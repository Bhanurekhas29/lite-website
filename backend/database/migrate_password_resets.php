<?php
declare(strict_types=1);
// One-time: adds the table used by "Forgot password". Run: php backend/database/migrate_password_resets.php
if (PHP_SAPI !== 'cli') { exit('Run from the command line only.'); }
require __DIR__ . '/../src/Database.php';
$config = require __DIR__ . '/../config/load.php';
$pdo = Database::connect($config['db']);
$pdo->exec('CREATE TABLE IF NOT EXISTS password_resets (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id   INT UNSIGNED NOT NULL,
  token_hash CHAR(64)     NOT NULL,
  expires_at TIMESTAMP    NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_reset_token (token_hash),
  KEY idx_reset_admin (admin_id, created_at),
  CONSTRAINT fk_reset_admin FOREIGN KEY (admin_id) REFERENCES admins (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
echo "password_resets is ready.\n";
