<?php

declare(strict_types=1);

namespace App\models;

final class VisitaModel
{
    private static array $botSignatures = [
        'bot', 'crawler', 'spider', 'slurp', 'googlebot', 'bingbot', 'yandex',
        'baidu', 'duckduck', 'facebot', 'ia_archiver', 'curl', 'wget', 'python',
        'java/', 'libwww', 'lwp-', 'go-http', 'okhttp', 'apache-http',
        'scrapy', 'nutch', 'phantomjs', 'headless', 'selenium',
    ];

    public static function registrar(string $ip, string $pagina = '/'): void
    {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

        // Ignora bots
        foreach (self::$botSignatures as $bot) {
            if (str_contains($ua, $bot)) return;
        }

        $ipHash    = hash('sha256', Security::anonymizeIp($ip));
        $navegador = self::detectarNavegador($ua);
        $so        = self::detectarSO($ua);
        $referrer  = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 1000);

        Database::query(
            'INSERT INTO visitas (ip_hash, user_agent, navegador, sistema_operacional, pagina, referrer)
             VALUES ($1, $2, $3, $4, $5, $6)',
            [
                $ipHash,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                $navegador,
                $so,
                substr($pagina, 0, 500),
                $referrer ?: null,
            ]
        );
    }

    public static function total(): int
    {
        return (int)Database::fetchColumn('SELECT COUNT(*) FROM visitas');
    }

    public static function hoje(): int
    {
        return (int)Database::fetchColumn(
            "SELECT COUNT(*) FROM visitas WHERE DATE(criado_em) = CURRENT_DATE"
        );
    }

    public static function porDia(int $dias = 30): array
    {
        return Database::fetchAll(
            'SELECT * FROM vw_visitas_diarias ORDER BY dia ASC'
        );
    }

    public static function porNavegador(): array
    {
        return Database::fetchAll('SELECT * FROM vw_visitas_navegador LIMIT 10');
    }

    public static function porSO(): array
    {
        return Database::fetchAll('SELECT * FROM vw_visitas_so LIMIT 10');
    }

    public static function ultimas(int $limit = 20): array
    {
        return Database::fetchAll(
            'SELECT * FROM visitas ORDER BY criado_em DESC LIMIT $1',
            [$limit]
        );
    }

    private static function detectarNavegador(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'edg/')      => 'Edge',
            str_contains($ua, 'opr/')      => 'Opera',
            str_contains($ua, 'chrome/')   => 'Chrome',
            str_contains($ua, 'firefox/')  => 'Firefox',
            str_contains($ua, 'safari/')   => 'Safari',
            default                        => 'Outro',
        };
    }

    private static function detectarSO(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'windows')  => 'Windows',
            str_contains($ua, 'android')  => 'Android',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') => 'iOS',
            str_contains($ua, 'mac os')   => 'macOS',
            str_contains($ua, 'linux')    => 'Linux',
            default                       => 'Outro',
        };
    }
}
