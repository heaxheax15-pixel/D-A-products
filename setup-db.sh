#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

echo "==> Creating database user (sudo password required)..."
sudo mysql < setup-mysql-local.sql

echo "==> Importing schema..."
mysql -h 127.0.0.1 -u da_shop -pda_honey_local da_honey_shop < database.sql

echo "==> Testing PHP connection..."
php -r "require 'config.php'; echo 'DB OK: ' . db()->query('SELECT 1')->fetchColumn() . PHP_EOL;"

echo "Done. Restart: php -S 0.0.0.0:8000"
