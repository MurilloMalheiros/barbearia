<?php

declare(strict_types=1);

namespace App\models;

final class AdminModel
{
    public static function findByEmail(string $email): array|false
    {
        return Database::fetchOne(
            'SELECT * FROM administradores WHERE email = $1 AND ativo = TRUE',
            [strtolower(trim($email))]
        );
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function registrarLogin(int $id, string $ip): void
    {
        Database::query(
            'UPDATE administradores SET ultimo_login = NOW(), ip_ultimo_login = $1 WHERE id = $2',
            [$ip, $id]
        );
    }

    public static function log(int $adminId, string $acao, string $detalhes = '', string $ip = ''): void
    {
        Database::query(
            'INSERT INTO logs_acesso_admin (admin_id, ip, acao, detalhes) VALUES ($1, $2, $3, $4)',
            [$adminId, $ip, $acao, $detalhes]
        );
    }

    public static function ultimasAcoes(int $limit = 20): array
    {
        return Database::fetchAll(
            'SELECT l.*, a.nome AS admin_nome
             FROM logs_acesso_admin l
             LEFT JOIN administradores a ON a.id = l.admin_id
             ORDER BY l.criado_em DESC
             LIMIT $1',
            [$limit]
        );
    }

    public static function updateSenha(int $id, string $novaSenha): void
    {
        Database::query(
            'UPDATE administradores SET senha_hash = $1, atualizado_em = NOW() WHERE id = $2',
            [self::hashPassword($novaSenha), $id]
        );
    }
}
