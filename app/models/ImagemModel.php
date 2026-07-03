<?php

declare(strict_types=1);

namespace App\models;

final class ImagemModel extends BaseModel
{
    protected static string $table = 'imagens';

    public static function allAtivos(int $limit = 100, int $offset = 0): array
    {
        return Database::fetchAll(
            'SELECT * FROM imagens WHERE ativo = TRUE
             ORDER BY ordem ASC, criado_em DESC
             LIMIT $1 OFFSET $2',
            [$limit, $offset]
        );
    }

    public static function allAdmin(int $limit = 60, int $offset = 0): array
    {
        return Database::fetchAll(
            'SELECT * FROM imagens ORDER BY criado_em DESC LIMIT $1 OFFSET $2',
            [$limit, $offset]
        );
    }

    public static function insert(array $data): int
    {
        Database::query(
            'INSERT INTO imagens (titulo, descricao, arquivo, mime_type, tamanho_bytes, admin_id)
             VALUES ($1, $2, $3, $4, $5, $6)',
            [
                $data['titulo'],
                $data['descricao']    ?? null,
                $data['arquivo'],
                $data['mime_type']    ?? null,
                $data['tamanho_bytes'] ?? null,
                $data['admin_id']    ?? null,
            ]
        );
        return (int)Database::lastInsertId('imagens_id_seq');
    }

    public static function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $i      = 1;

        $allowed = ['titulo', 'descricao', 'arquivo', 'ativo', 'ordem'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = \${$i}";
                $params[] = $data[$field];
                $i++;
            }
        }

        if (empty($sets)) return false;

        $sets[]   = 'atualizado_em = NOW()';
        $params[] = $id;
        $sql      = 'UPDATE imagens SET ' . implode(', ', $sets) . " WHERE id = \${$i}";
        return Database::query($sql, $params)->rowCount() > 0;
    }

    public static function totalAtivos(): int
    {
        return (int)Database::fetchColumn('SELECT COUNT(*) FROM imagens WHERE ativo = TRUE');
    }

    public static function ultimos(int $limit = 6): array
    {
        return Database::fetchAll(
            'SELECT * FROM imagens WHERE ativo = TRUE ORDER BY criado_em DESC LIMIT $1',
            [$limit]
        );
    }
}
