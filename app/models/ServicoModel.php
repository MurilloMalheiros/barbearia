<?php

declare(strict_types=1);

namespace App\models;

final class ServicoModel extends BaseModel
{
    protected static string $table = 'servicos';

    public static function allAtivos(): array
    {
        return Database::fetchAll(
            'SELECT * FROM servicos WHERE ativo = TRUE ORDER BY ordem ASC, nome ASC'
        );
    }

    public static function allAdmin(): array
    {
        return Database::fetchAll(
            'SELECT * FROM servicos ORDER BY ordem ASC, nome ASC'
        );
    }

    public static function insert(array $data): int
    {
        Database::query(
            'INSERT INTO servicos (nome, descricao, icone, preco, duracao_minutos, ordem)
             VALUES ($1, $2, $3, $4, $5, $6)',
            [
                $data['nome'],
                $data['descricao']       ?? null,
                $data['icone']           ?? null,
                $data['preco']           ?? null,
                $data['duracao_minutos'] ?? 60,
                $data['ordem']           ?? 0,
            ]
        );
        return (int)Database::lastInsertId('servicos_id_seq');
    }

    public static function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $i      = 1;

        $allowed = ['nome', 'descricao', 'icone', 'preco', 'duracao_minutos', 'ativo', 'ordem'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = \${$i}";
                $params[] = $data[$field];
                $i++;
            }
        }

        if (empty($sets)) return false;

        $sets[]   = "atualizado_em = NOW()";
        $params[] = $id;
        $sql      = 'UPDATE servicos SET ' . implode(', ', $sets) . " WHERE id = \${$i}";
        return Database::query($sql, $params)->rowCount() > 0;
    }
}
