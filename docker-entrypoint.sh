#!/bin/sh
# Writes config/config.php from environment variables (so secrets never live in
# the image or in git), then points Apache at Railway's $PORT and starts it.
set -e

php -r '
$cfg = [
    "db" => [
        "host" => getenv("DB_HOST") ?: "127.0.0.1",
        "port" => (int)(getenv("DB_PORT") ?: 5432),
        "name" => getenv("DB_NAME") ?: "postgres",
        "user" => getenv("DB_USER") ?: "postgres",
        "pass" => getenv("DB_PASS") ?: "",
    ],
    "base_url"      => getenv("BASE_URL") ?: "",
    "debug"         => getenv("APP_DEBUG") === "true",
    "timezone"      => "Asia/Bangkok",
    "upload_max_mb" => 20,
];
file_put_contents("/var/www/html/config/config.php", "<?php\nreturn " . var_export($cfg, true) . ";\n");
'

PORT="${PORT:-8080}"
sed -ri "s/Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:[0-9]+>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
