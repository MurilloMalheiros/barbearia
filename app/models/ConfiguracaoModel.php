<?php

declare(strict_types=1);

namespace App\models;

final class ConfiguracaoModel
{
    private static array $cache = [];

    public static function get(string $chave, string $default = ''): string
    {
        if (isset(self::$cache[$chave])) {
            return self::$cache[$chave];
        }

        $result = Database::fetchOne(
            'SELECT valor FROM configuracoes WHERE chave = $1',
            [$chave]
        );

        $value = $result ? ($result['valor'] ?? $default) : $default;

        if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
            $converted = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
            $value     = mb_check_encoding($converted, 'UTF-8') ? $converted : $default;
        }

        self::$cache[$chave] = $value;
        return $value;
    }

    public static function set(string $chave, string $valor): void
    {
        Database::query(
            'INSERT INTO configuracoes (chave, valor)
             VALUES ($1, $2)
             ON CONFLICT (chave) DO UPDATE SET valor = EXCLUDED.valor, atualizado_em = NOW()',
            [$chave, $valor]
        );
        self::$cache[$chave] = $valor;
    }

    public static function saveMany(array $configs): void
    {
        Database::beginTransaction();
        try {
            foreach ($configs as $chave => $valor) {
                self::set($chave, (string)$valor);
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            Logger::error('[Config] Erro ao salvar configurações', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public static function all(): array
    {
        $rows   = Database::fetchAll('SELECT chave, valor FROM configuracoes');
        $result = [];
        foreach ($rows as $row) {
            $result[$row['chave']] = $row['valor'] ?? '';
        }
        return $result;
    }
}
