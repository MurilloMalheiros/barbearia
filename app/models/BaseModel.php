<?php

declare(strict_types=1);

namespace App\models;

abstract class BaseModel
{
    protected static string $table = '';

    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM ' . static::$table . ' ORDER BY id DESC');
    }

    public static function find(int $id): array|false
    {
        return Database::fetchOne(
            'SELECT * FROM ' . static::$table . ' WHERE id = $1',
            [$id]
        );
    }

    public static function count(array $where = []): int
    {
        if (empty($where)) {
            return (int)Database::fetchColumn('SELECT COUNT(*) FROM ' . static::$table);
        }

        $conditions = [];
        $params     = [];
        $i          = 1;
        foreach ($where as $col => $val) {
            $conditions[] = "{$col} = \${$i}";
            $params[]     = $val;
            $i++;
        }

        $sql = 'SELECT COUNT(*) FROM ' . static::$table . ' WHERE ' . implode(' AND ', $conditions);
        return (int)Database::fetchColumn($sql, $params);
    }

    public static function delete(int $id): bool
    {
        $stmt = Database::query(
            'DELETE FROM ' . static::$table . ' WHERE id = $1',
            [$id]
        );
        return $stmt->rowCount() > 0;
    }
}
