#!/bin/sh
# Writes config/config.php from environment variables (so secrets never live in
# the image or in git). Caddy reads $PORT itself via the Caddyfile, so unlike
# docker-entrypoint.sh (Apache/Railway) there's no web-server config to patch here.
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
    "upload_max_mb" => 10,
];
file_put_contents("/app/config/config.php", "<?php\nreturn " . var_export($cfg, true) . ";\n");
'

exec "$@"
