#!/bin/sh
set -e
cd /var/www/html

if [ -n "$RENDER_EXTERNAL_URL" ] && [ -z "$APP_URL" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ -z "$APP_KEY" ]; then
  export APP_KEY="base64:$(openssl rand -base64 32 | tr -d '\n')"
fi

php -r '
$path = ".env";
$env = file_exists($path) ? file_get_contents($path) : "";
$set = function (string $env, string $key, ?string $value): string {
    if ($value === null || $value === "") {
        return $env;
    }
    if (str_contains($value, " ") || str_contains($value, "#") || str_contains($value, "=")) {
        $value = "\"".addcslashes($value, "\"\\")."\"";
    }
    $line = $key."=".$value;
    if (preg_match("/^".preg_quote($key, "/")."=.*/m", $env)) {
        return preg_replace("/^".preg_quote($key, "/")."=.*/m", $line, $env);
    }
    return rtrim($env)."\n".$line."\n";
};
$env = $set($env, "APP_KEY", getenv("APP_KEY") ?: null);
$env = $set($env, "APP_URL", getenv("APP_URL") ?: null);
$env = $set($env, "APP_ENV", getenv("APP_ENV") ?: "production");
$env = $set($env, "APP_DEBUG", getenv("APP_DEBUG") !== false && getenv("APP_DEBUG") !== "" ? getenv("APP_DEBUG") : "false");
$env = $set($env, "APP_NAME", getenv("APP_NAME") ?: "NextVerseSkill");
$env = $set($env, "DB_CONNECTION", getenv("DB_CONNECTION") ?: "sqlite");
$env = $set($env, "SESSION_DRIVER", getenv("SESSION_DRIVER") ?: "database");
$env = $set($env, "SESSION_SECURE_COOKIE", getenv("SESSION_SECURE_COOKIE") ?: "true");
$env = $set($env, "CACHE_STORE", getenv("CACHE_STORE") ?: "database");
$env = $set($env, "QUEUE_CONNECTION", getenv("QUEUE_CONNECTION") ?: "sync");
$env = $set($env, "FILESYSTEM_DISK", getenv("FILESYSTEM_DISK") ?: "public");
$env = $set($env, "LOG_CHANNEL", getenv("LOG_CHANNEL") ?: "stderr");
$env = $set($env, "ADMIN_PASSWORD", getenv("ADMIN_PASSWORD") ?: "nextverse");
file_put_contents($path, $env);
'

mkdir -p database storage/app/public
if [ ! -f database/database.sqlite ]; then
  touch database/database.sqlite
fi

php artisan storage:link --force || true
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan view:cache
php artisan route:cache || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
