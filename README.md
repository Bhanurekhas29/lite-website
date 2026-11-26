# E for Events website

A multi-page website with a custom admin panel.

- `frontend/` React + Vite: the public website and the admin panel (`/admin`)
- `backend/` core PHP API, MySQL-compatible database, uploaded images in `backend/media`
- `Dockerfile`, `docker/`, `render.yaml` run everything as one container (used on Render)

## Run on your own computer

1. Start XAMPP (Apache is not needed, only MySQL) and create the database with `php backend/database/install.php`.
2. Copy `backend/config/config.sample.php` to `backend/config/config.php` and fill it in.
3. `php -S localhost:8000 -t backend backend/router.php` (from the project folder)
4. `npm install` and `npm run dev` inside `frontend/`, then open http://localhost:5173

## Hosting settings (environment variables)

| Name | What it is |
| --- | --- |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` | The MySQL-compatible database (for example TiDB Cloud) |
| `DB_SSL` | `true` for databases that need an encrypted connection |
| `APP_KEY` | A long random value that encrypts stored secrets such as the email password |

No secret is stored in this repository. `backend/config/config.php` and database exports (`*.sql`) are ignored by git.

## Note about images

Images uploaded in the admin are saved on the server's disk. Free hosting plans reset that disk when the service restarts or is redeployed, so uploaded images need permanent storage (a paid disk or an image service) before real launch.
