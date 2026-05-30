-- Local MariaDB/MySQL fix for PHP PDO (error 1698 / auth_socket)
-- Run from project folder:
--   sudo mysql < setup-mysql-local.sql
--   mysql -h 127.0.0.1 -u da_shop -pda_honey_local da_honey_shop < database.sql

CREATE DATABASE IF NOT EXISTS da_honey_shop
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP USER IF EXISTS 'da_shop'@'localhost';
DROP USER IF EXISTS 'da_shop'@'127.0.0.1';

CREATE USER 'da_shop'@'127.0.0.1' IDENTIFIED BY 'da_honey_local';
CREATE USER 'da_shop'@'localhost' IDENTIFIED BY 'da_honey_local';

ALTER USER 'da_shop'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING PASSWORD('da_honey_local');
ALTER USER 'da_shop'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('da_honey_local');

GRANT ALL PRIVILEGES ON da_honey_shop.* TO 'da_shop'@'127.0.0.1';
GRANT ALL PRIVILEGES ON da_honey_shop.* TO 'da_shop'@'localhost';

FLUSH PRIVILEGES;
