<?php
declare(strict_types=1);

require __DIR__ . '/src/Response.php';
require __DIR__ . '/src/HttpException.php';
require __DIR__ . '/src/Request.php';
require __DIR__ . '/src/Database.php';
require __DIR__ . '/src/Auth.php';
require __DIR__ . '/src/Schemas.php';
require __DIR__ . '/src/PageService.php';
require __DIR__ . '/src/MediaService.php';
require __DIR__ . '/src/PublicService.php';
require __DIR__ . '/src/Crypto.php';
require __DIR__ . '/src/Mailer.php';
require __DIR__ . '/src/SettingsService.php';
require __DIR__ . '/src/EnquiryService.php';
require __DIR__ . '/src/ProjectService.php';
require __DIR__ . '/src/MediaLibrary.php';
require __DIR__ . '/src/PasswordReset.php';
require __DIR__ . '/src/Xlsx.php';
require __DIR__ . '/src/AdminUsers.php';
require __DIR__ . '/src/DashboardService.php';

// Everything is stored in UTC. Screens convert to the time zone chosen in Settings.
date_default_timezone_set('UTC');

$config = require __DIR__ . '/config/load.php';
Crypto::init($config['app_key'] ?? '');

// Development CORS: allow the Vite dev server. Same-origin in production.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $config['allowed_origins'], true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
}
if (Request::method() === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = '/' . trim(preg_replace('#^/api#', '', $path), '/');
$route = Request::method() . ' ' . $path;

try {
    if ($route === 'GET /health') {
        Response::json(['status' => 'ok', 'database' => Database::status($config['db'])]);
        exit;
    }

    $isUpload = $route === 'POST /admin/media' || (bool) preg_match('#^POST /admin/media/\d+/replace$#', $route);
    Request::requireSafeWrite($isUpload);
    $pdo = Database::connect($config['db']);
    $m = [];

    // ---- Public website (no sign-in, no session) ------------------------
    if ($route === 'GET /site') {
        Response::json(PublicService::site($pdo));
        exit;
    }
    if (preg_match('#^GET /pages/([a-z0-9-]+)$#', $route, $m)) {
        $data = PublicService::page($pdo, $m[1]);
        if (!$data) {
            throw new HttpException('Page not found', 404);
        }
        Response::json($data);
        exit;
    }

    // ---- Enquiry form (public, no sign-in, no session) -------------------
    if ($route === 'POST /enquiries') {
        EnquiryService::submit($pdo, Request::json(), (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        Response::json(['ok' => true], 201);
        exit;
    }

    Auth::start();

    // ---- Sign in and out -------------------------------------------------
    if ($route === 'POST /admin/login') {
        $body = Request::json();
        $identifier = trim((string) ($body['identifier'] ?? ''));
        $password = (string) ($body['password'] ?? '');
        if ($identifier === '' || $password === '') {
            throw new HttpException('Enter your username or email and password');
        }
        $admin = Auth::login($pdo, $identifier, $password);
        if (!$admin) {
            throw new HttpException('Incorrect username, email or password', 401);
        }
        Response::json(['user' => Auth::publicFields($admin)]);
        exit;
    }
    // ---- Forgot password (no sign-in needed) -----------------------------
    if ($route === 'POST /admin/forgot-password') {
        $identifier = trim((string) (Request::json()['identifier'] ?? ''));
        if ($identifier === '') {
            throw new HttpException('Enter your username or email');
        }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $base = ($config['public_url'] ?? '') ?: (($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        PasswordReset::request($pdo, mb_substr($identifier, 0, 190), $base);
        usleep(random_int(200000, 500000));
        Response::json(['ok' => true]);
        exit;
    }
    if ($route === 'POST /admin/reset-password/check') {
        Response::json(['valid' => PasswordReset::valid($pdo, (string) (Request::json()['token'] ?? ''))]);
        exit;
    }
    if ($route === 'POST /admin/reset-password') {
        $b = Request::json();
        PasswordReset::reset($pdo, (string) ($b['token'] ?? ''), (string) ($b['password'] ?? ''));
        Response::json(['ok' => true]);
        exit;
    }
    if ($route === 'POST /admin/logout') {
        Auth::logout();
        Response::json(['ok' => true]);
        exit;
    }
    if ($route === 'GET /admin/me') {
        $user = Auth::user($pdo);
        if (!$user) {
            throw new HttpException('Please sign in', 401);
        }
        Response::json(['user' => $user]);
        exit;
    }

    // ---- Everything below needs a signed-in admin ------------------------
    $user = Auth::require($pdo);

    if ($route === 'GET /admin/pages') {
        $rows = $pdo->query('SELECT id, title, menu_label, slug, is_visible, is_locked FROM pages ORDER BY sort_order, id')->fetchAll();
        foreach ($rows as &$r) {
            $r['id'] = (int) $r['id'];
            $r['is_visible'] = (bool) $r['is_visible'];
            $r['is_locked'] = (bool) $r['is_locked'];
        }
        Response::json(['pages' => $rows]);
    } elseif ($route === 'GET /admin/dashboard') {
        Response::json(DashboardService::overview($pdo));
    } elseif ($route === 'POST /admin/profile/password') {
        $b = Request::json();
        Auth::changePassword($pdo, (int) $user['id'], (string) ($b['current_password'] ?? ''), (string) ($b['password'] ?? ''));
        Response::json(['ok' => true]);
    } elseif ($route === 'GET /admin/overview') {
        $count = fn (string $sql): int => (int) $pdo->query($sql)->fetchColumn();
        $zone = $pdo->query("SELECT value_text FROM settings WHERE group_name = 'general' AND setting_key = 'timezone'")->fetchColumn();
        Response::json([
            'timezone' => $zone ?: 'Europe/London',
            'pages' => $count('SELECT COUNT(*) FROM pages'),
            'new_enquiries' => $count("SELECT COUNT(*) FROM enquiries WHERE status = 'new'"),
            'media' => $count('SELECT COUNT(*) FROM media'),
            'admins' => $count('SELECT COUNT(*) FROM admins WHERE is_active = 1'),
        ]);
    } elseif (preg_match('#^GET /admin/pages/([a-z0-9-]+)$#', $route, $m)) {
        $data = PageService::loadPage($pdo, $m[1]);
        if (!$data) {
            throw new HttpException('Page not found', 404);
        }
        Response::json($data);
    } elseif (preg_match('#^PUT /admin/pages/([a-z0-9-]+)$#', $route, $m)) {
        Response::json(PageService::savePage($pdo, $m[1], Request::json()));
    } elseif (preg_match('#^PUT /admin/pages/([a-z0-9-]+)/order$#', $route, $m)) {
        $ids = Request::json()['ids'] ?? null;
        if (!is_array($ids)) {
            throw new HttpException('Send the section order');
        }
        PageService::reorderSections($pdo, $m[1], $ids);
        Response::json(['ok' => true]);
    } elseif (preg_match('#^PUT /admin/sections/(\d+)$#', $route, $m)) {
        Response::json(['section' => PageService::saveSection($pdo, (int) $m[1], Request::json())]);
    } elseif (preg_match('#^PUT /admin/sections/(\d+)/visibility$#', $route, $m)) {
        PageService::setSectionVisibility($pdo, (int) $m[1], !empty(Request::json()['is_visible']));
        Response::json(['ok' => true]);
    } elseif (preg_match('#^GET /admin/settings/([a-z]+)$#', $route, $m)) {
        Response::json(SettingsService::load($pdo, $m[1]));
    } elseif (preg_match('#^PUT /admin/settings/([a-z]+)$#', $route, $m)) {
        $values = Request::json()['values'] ?? null;
        if (!is_array($values)) {
            throw new HttpException('Send the settings to save');
        }
        Response::json(SettingsService::save($pdo, $m[1], $values));
    } elseif ($route === 'POST /admin/settings/email/test') {
        $to = trim((string) (Request::json()['to'] ?? ''));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new HttpException('Enter a valid email address to send the test to');
        }
        try {
            Mailer::send(
                SettingsService::smtp($pdo),
                $to,
                'Test email from your website',
                "This is a test email from the E for Events admin panel.\r\n\r\nIf you can read this, your email settings work."
            );
        } catch (RuntimeException $e) {
            throw new HttpException($e->getMessage());
        }
        Response::json(['ok' => true]);
    } elseif ($route === 'GET /admin/enquiries') {
        Response::json(EnquiryService::list(
            $pdo,
            (string) ($_GET['status'] ?? 'all'),
            (string) ($_GET['q'] ?? ''),
            (int) ($_GET['page'] ?? 1),
            (int) ($_GET['per_page'] ?? 20)
        ));
    } elseif ($route === 'GET /admin/enquiries/export') {
        $file = EnquiryService::exportXlsx($pdo, (string) ($_GET['status'] ?? 'all'), (string) ($_GET['q'] ?? ''));
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="enquiries-' . gmdate('Y-m-d') . '.xlsx"');
        header('Content-Length: ' . strlen($file));
        echo $file;
        exit;
    } elseif ($route === 'POST /admin/enquiries/delete') {
        $ids = Request::json()['ids'] ?? null;
        if (!is_array($ids)) {
            throw new HttpException('Choose the enquiries to delete');
        }
        Response::json(['deleted' => EnquiryService::deleteMany($pdo, $ids)]);
    } elseif ($route === 'GET /admin/users') {
        Auth::requireSuper($user);
        Response::json(['users' => AdminUsers::list($pdo)]);
    } elseif ($route === 'POST /admin/users') {
        Auth::requireSuper($user);
        Response::json(['user' => AdminUsers::create($pdo, Request::json())], 201);
    } elseif (preg_match('#^PUT /admin/users/(\d+)$#', $route, $m)) {
        Auth::requireSuper($user);
        Response::json(['user' => AdminUsers::update($pdo, (int) $m[1], Request::json(), (int) $user['id'])]);
    } elseif (preg_match('#^DELETE /admin/users/(\d+)$#', $route, $m)) {
        Auth::requireSuper($user);
        AdminUsers::delete($pdo, (int) $m[1], (int) $user['id']);
        Response::json(['ok' => true]);
    } elseif ($route === 'POST /admin/enquiries/mark-all-read') {
        Response::json(['updated' => EnquiryService::markAllRead($pdo)]);
    } elseif (preg_match('#^GET /admin/enquiries/(\d+)$#', $route, $m)) {
        Response::json(['enquiry' => EnquiryService::get($pdo, (int) $m[1])]);
    } elseif (preg_match('#^PUT /admin/enquiries/(\d+)$#', $route, $m)) {
        EnquiryService::setStatus($pdo, (int) $m[1], (string) (Request::json()['status'] ?? ''));
        Response::json(['ok' => true]);
    } elseif (preg_match('#^DELETE /admin/enquiries/(\d+)$#', $route, $m)) {
        EnquiryService::delete($pdo, (int) $m[1]);
        Response::json(['ok' => true]);
    } elseif ($route === 'GET /admin/projects') {
        Response::json(['projects' => ProjectService::list($pdo), 'categories' => ProjectService::categories($pdo)]);
    } elseif ($route === 'POST /admin/projects') {
        Response::json(['project' => ProjectService::create($pdo, Request::json())], 201);
    } elseif ($route === 'PUT /admin/projects/order') {
        $ids = Request::json()['ids'] ?? null;
        if (!is_array($ids)) {
            throw new HttpException('Send the project order');
        }
        ProjectService::reorder($pdo, $ids);
        Response::json(['ok' => true]);
    } elseif (preg_match('#^GET /admin/projects/(\d+)$#', $route, $m)) {
        Response::json(['project' => ProjectService::get($pdo, (int) $m[1])]);
    } elseif (preg_match('#^PUT /admin/projects/(\d+)$#', $route, $m)) {
        Response::json(['project' => ProjectService::update($pdo, (int) $m[1], Request::json())]);
    } elseif (preg_match('#^PUT /admin/projects/(\d+)/flags$#', $route, $m)) {
        ProjectService::setFlags($pdo, (int) $m[1], Request::json());
        Response::json(['ok' => true]);
    } elseif (preg_match('#^DELETE /admin/projects/(\d+)$#', $route, $m)) {
        ProjectService::delete($pdo, (int) $m[1]);
        Response::json(['ok' => true]);
    } elseif ($route === 'POST /admin/project-categories') {
        ProjectService::createCategory($pdo, Request::json());
        Response::json(['categories' => ProjectService::categories($pdo)], 201);
    } elseif (preg_match('#^PUT /admin/project-categories/(\d+)$#', $route, $m)) {
        ProjectService::updateCategory($pdo, (int) $m[1], Request::json());
        Response::json(['categories' => ProjectService::categories($pdo)]);
    } elseif (preg_match('#^DELETE /admin/project-categories/(\d+)$#', $route, $m)) {
        ProjectService::deleteCategory($pdo, (int) $m[1]);
        Response::json(['categories' => ProjectService::categories($pdo)]);
    } elseif ($route === 'GET /admin/media-library') {
        Response::json(MediaLibrary::library(
            $pdo,
            (string) ($_GET['filter'] ?? 'all'),
            (string) ($_GET['q'] ?? ''),
            (int) ($_GET['page'] ?? 1)
        ));
    } elseif ($route === 'POST /admin/media/delete') {
        $ids = Request::json()['ids'] ?? null;
        if (!is_array($ids)) {
            throw new HttpException('Choose the images to delete');
        }
        Response::json(MediaLibrary::deleteMany($pdo, $ids));
    } elseif (preg_match('#^GET /admin/media/(\d+)$#', $route, $m)) {
        Response::json(['media' => MediaLibrary::detail($pdo, (int) $m[1])]);
    } elseif (preg_match('#^PUT /admin/media/(\d+)$#', $route, $m)) {
        Response::json(['media' => MediaLibrary::update($pdo, (int) $m[1], Request::json())]);
    } elseif (preg_match('#^POST /admin/media/(\d+)/replace$#', $route, $m)) {
        Response::json(['media' => MediaLibrary::replace($pdo, (int) $m[1], $_FILES['file'] ?? null)]);
    } elseif (preg_match('#^DELETE /admin/media/(\d+)$#', $route, $m)) {
        MediaLibrary::delete($pdo, (int) $m[1]);
        Response::json(['ok' => true]);
    } elseif ($route === 'GET /admin/media') {
        Response::json(['media' => MediaService::list($pdo)]);
    } elseif ($route === 'POST /admin/media') {
        Response::json(['media' => MediaService::upload($pdo, $_FILES['file'] ?? null, $user['id'])], 201);
    } else {
        throw new HttpException('Not found', 404);
    }
} catch (HttpException $e) {
    Response::json(['error' => $e->getMessage()], $e->status());
} catch (Throwable $e) {
    error_log($e->getMessage());
    Response::json(['error' => 'Something went wrong. Try again.'], 500);
}
