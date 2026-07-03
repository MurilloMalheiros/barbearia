<?php

declare(strict_types=1);

namespace App\models;

/**
 * Utilitários de segurança: XSS, Rate Limiting, Headers, Upload
 */
final class Security
{
    /** Escapa output HTML (previne XSS) */
    public static function escape(mixed $value): string
    {
        $string = (string)$value;
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        }
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    public static function e(mixed $value): string
    {
        return self::escape($value);
    }

    /** Anonimiza IP para LGPD */
    public static function anonymizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', inet_ntop(inet_pton($ip)));
            $parts = array_slice($parts, 0, 3);
            return implode(':', $parts) . ':0000:0000:0000:0000:0000';
        }
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            $parts[3] = '0';
            return implode('.', $parts);
        }
        return '0.0.0.0';
    }

    /** Verifica bloqueio por brute force */
    public static function isIpBlocked(string $ip): bool
    {
        $result = Database::fetchOne(
            'SELECT bloqueado_ate FROM tentativas_login WHERE ip = $1',
            [$ip]
        );
        if (!$result || empty($result['bloqueado_ate'])) {
            return false;
        }
        return strtotime($result['bloqueado_ate']) > time();
    }

    /** Registra tentativa de login */
    public static function registerLoginAttempt(string $ip): void
    {
        $existing = Database::fetchOne(
            'SELECT id, tentativas FROM tentativas_login WHERE ip = $1',
            [$ip]
        );

        if (!$existing) {
            Database::query(
                'INSERT INTO tentativas_login (ip, tentativas) VALUES ($1, 1)',
                [$ip]
            );
            return;
        }

        $tentativas  = (int)$existing['tentativas'] + 1;
        $bloqueadoAte = null;

        if ($tentativas >= LOGIN_MAX_ATTEMPTS) {
            $bloqueadoAte = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_MINUTES * 60);
        }

        Database::query(
            'UPDATE tentativas_login SET tentativas = $1, bloqueado_ate = $2, ultima_tentativa = NOW() WHERE ip = $3',
            [$tentativas, $bloqueadoAte, $ip]
        );
    }

    public static function clearLoginAttempts(string $ip): void
    {
        Database::query('DELETE FROM tentativas_login WHERE ip = $1', [$ip]);
    }

    public static function remainingAttempts(string $ip): int
    {
        $result = Database::fetchOne(
            'SELECT tentativas FROM tentativas_login WHERE ip = $1',
            [$ip]
        );
        return $result ? max(0, LOGIN_MAX_ATTEMPTS - (int)$result['tentativas']) : LOGIN_MAX_ATTEMPTS;
    }

    /** Headers de segurança HTTP */
    public static function setSecurityHeaders(): void
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: blob:",
            "media-src 'self'",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
        ]);
        header("Content-Security-Policy: {$csp}");

        if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    /** Gera slug seguro */
    public static function slug(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = str_replace(
            ['á','à','â','ã','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','ô','õ','ö','ú','ù','û','ü','ç','ñ'],
            ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n'],
            $text
        );
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return substr($text, 0, 120);
    }

    /** Valida arquivo de upload */
    public static function validateUpload(array $file, string $type = 'image'): array
    {
        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = self::uploadErrorMessage($file['error']);
            return $errors;
        }

        $maxSize = MAX_IMAGE_SIZE;
        if ($file['size'] > $maxSize) {
            $errors[] = 'Arquivo muito grande. Máximo: ' . self::formatBytes($maxSize);
        }

        // Verifica MIME via finfo (mais seguro que extensão)
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($file['tmp_name']);

        $allowedMimes = ALLOWED_IMAGE_MIMES;
        $allowedExts  = ALLOWED_IMAGE_EXTS;

        if (!in_array($mimeReal, $allowedMimes, true)) {
            $errors[] = 'Tipo de arquivo não permitido. Use: ' . implode(', ', $allowedExts);
        }

        // Valida extensão
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            $errors[] = 'Extensão não permitida.';
        }

        return $errors;
    }

    /** Move upload para o destino com nome aleatório seguro */
    public static function moveUpload(array $file, string $subdir = ''): string|false
    {
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir      = PUBLIC_PATH . '/uploads' . ($subdir ? '/' . trim($subdir, '/') : '');

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            // Impede listagem e execução de PHP no diretório de upload
            file_put_contents($dir . '/.htaccess', "Options -Indexes\n<FilesMatch \\.php$>\n    Require all denied\n</FilesMatch>\n");
        }

        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Logger::error('[Upload] Falha ao mover arquivo', ['dest' => $dest]);
            return false;
        }

        return ($subdir ? trim($subdir, '/') . '/' : '') . $filename;
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho permitido.',
            UPLOAD_ERR_PARTIAL  => 'Upload incompleto.',
            UPLOAD_ERR_NO_FILE  => 'Nenhum arquivo enviado.',
            default             => 'Erro no upload.',
        };
    }
}
