<?php

declare(strict_types=1);

namespace App\models;

/**
 * Gerenciamento de sessões seguras
 * Proteção contra Session Fixation, Hijacking e CSRF
 */
final class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443;

        ini_set('session.use_strict_mode',  '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid',    '0');
        ini_set('session.cookie_httponly',  '1');
        ini_set('session.cookie_secure',    $isHttps ? '1' : '0');
        ini_set('session.cookie_samesite',  $isHttps ? 'Strict' : 'Lax');
        ini_set('session.gc_maxlifetime',   (string)SESSION_LIFETIME);

        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => $isHttps ? 'Strict' : 'Lax',
        ]);

        session_start();
        self::$started = true;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function validate(): bool
    {
        $ip = self::getClientIp();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (!isset($_SESSION['_fingerprint'])) {
            $_SESSION['_fingerprint'] = hash_hmac('sha256', $ip . $ua, APP_SECRET);
            return true;
        }

        $expected = hash_hmac('sha256', $ip . $ua, APP_SECRET);
        if (!hash_equals($_SESSION['_fingerprint'], $expected)) {
            self::destroy();
            return false;
        }

        if (isset($_SESSION['_last_activity'])) {
            if ((time() - $_SESSION['_last_activity']) > SESSION_LIFETIME) {
                self::destroy();
                return false;
            }
        }

        $_SESSION['_last_activity'] = time();
        return true;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
        self::$started = false;

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    }

    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;
        return $token;
    }

    public static function validateCsrf(string $token): bool
    {
        $stored = $_SESSION['_csrf_token'] ?? '';
        return hash_equals($stored, $token);
    }

    private static function getClientIp(): string
    {
        // Proxies confiáveis — adicione IPs se usar proxy reverso
        $trustedProxies = [];

        if (!empty($trustedProxies) && in_array($_SERVER['REMOTE_ADDR'] ?? '', $trustedProxies)) {
            foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'] as $header) {
                if (!empty($_SERVER[$header])) {
                    $ip = trim(explode(',', $_SERVER[$header])[0]);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
