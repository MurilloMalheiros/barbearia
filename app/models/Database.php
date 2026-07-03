<?php

declare(strict_types=1);

namespace App\models;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Singleton de conexão PDO com PostgreSQL
 * Todas as queries DEVEM usar prepared statements
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::connect();
        }
        return self::$instance;
    }

    private static function connect(): PDO
    {
        $cfg = require CONFIG_PATH . '/database.php';

        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['dbname']
        );

        try {
            $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], $cfg['options']);
            $pdo->exec("SET client_encoding TO 'UTF8'");
            $pdo->exec("SET NAMES 'UTF8'");
            // Define o search_path para o schema da barbearia
            $schema = preg_replace('/[^a-z0-9_]/i', '', $cfg['schema'] ?? 'barbearia');
            $pdo->exec("SET search_path TO {$schema}, public");
            return $pdo;
        } catch (PDOException $e) {
            Logger::error('[DB] Falha na conexão', ['message' => $e->getMessage()]);
            throw new RuntimeException('Serviço temporariamente indisponível. Tente novamente.', 503);
        }
    }

    /**
     * Executa query com prepared statement
     * Converte placeholders $1, $2 → ? para PDO
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $pdo          = self::getInstance();
        $sqlConverted = preg_replace('/\\$\\d+/', '?', $sql);
        $stmt         = $pdo->prepare($sqlConverted);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): array|false
    {
        return self::query($sql, $params)->fetch();
    }

    public static function fetchColumn(string $sql, array $params = [], int $col = 0): mixed
    {
        return self::query($sql, $params)->fetchColumn($col);
    }

    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    public static function rollback(): void
    {
        if (self::getInstance()->inTransaction()) {
            self::getInstance()->rollBack();
        }
    }

    public static function lastInsertId(string $sequence = ''): string
    {
        return self::getInstance()->lastInsertId($sequence ?: null);
    }
}
