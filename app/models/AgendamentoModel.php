<?php

declare(strict_types=1);

namespace App\models;

/**
 * Model de agendamentos
 */
final class AgendamentoModel extends BaseModel
{
    protected static string $table = 'agendamentos';

    /**
     * Retorna todos os slots já ocupados em uma data específica
     * (apenas agendamentos não cancelados)
     */
    public static function slotsOcupados(string $data): array
    {
        $rows = Database::fetchAll(
            "SELECT horario FROM agendamentos
             WHERE data_agendamento = $1 AND status != 'cancelado'",
            [$data]
        );

        return array_column($rows, 'horario');
    }

    /**
     * Verifica se um slot está disponível
     */
    public static function slotDisponivel(string $data, string $horario): bool
    {
        $count = (int)Database::fetchColumn(
            "SELECT COUNT(*) FROM agendamentos
             WHERE data_agendamento = $1 AND horario = $2 AND status != 'cancelado'",
            [$data, $horario]
        );
        return $count === 0;
    }

    /**
     * Cria novo agendamento
     */
    public static function inserir(array $data): int
    {
        Database::query(
            'INSERT INTO agendamentos
             (cliente_nome, cliente_telefone, servico_id, data_agendamento, horario, observacoes)
             VALUES ($1, $2, $3, $4, $5, $6)',
            [
                $data['cliente_nome'],
                $data['cliente_telefone'],
                $data['servico_id'] ?: null,
                $data['data_agendamento'],
                $data['horario'],
                $data['observacoes'] ?? null,
            ]
        );
        return (int)Database::lastInsertId('agendamentos_id_seq');
    }

    /**
     * Lista agendamentos com filtros (admin)
     */
    public static function listar(array $filtros = [], int $limit = 50, int $offset = 0): array
    {
        $where  = ['1=1'];
        $params = [];
        $i      = 1;

        if (!empty($filtros['data'])) {
            $where[]  = "a.data_agendamento = \${$i}";
            $params[] = $filtros['data'];
            $i++;
        }

        if (!empty($filtros['status'])) {
            $where[]  = "a.status = \${$i}";
            $params[] = $filtros['status'];
            $i++;
        }

        if (!empty($filtros['periodo'])) {
            if ($filtros['periodo'] === 'hoje') {
                $where[] = "a.data_agendamento = CURRENT_DATE";
            } elseif ($filtros['periodo'] === 'semana') {
                $where[] = "a.data_agendamento BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '7 days'";
            } elseif ($filtros['periodo'] === 'mes') {
                $where[] = "DATE_TRUNC('month', a.data_agendamento) = DATE_TRUNC('month', CURRENT_DATE)";
            }
        }

        $params[] = $limit;
        $params[] = $offset;
        $sql      = "SELECT a.*, s.nome AS servico_nome
                     FROM agendamentos a
                     LEFT JOIN servicos s ON s.id = a.servico_id
                     WHERE " . implode(' AND ', $where) . "
                     ORDER BY a.data_agendamento ASC, a.horario ASC
                     LIMIT \${$i} OFFSET \$" . ($i + 1);

        return Database::fetchAll($sql, $params);
    }

    /**
     * Atualiza status de um agendamento
     */
    public static function atualizarStatus(int $id, string $status): bool
    {
        $allowed = ['pendente', 'confirmado', 'cancelado', 'concluido'];
        if (!in_array($status, $allowed, true)) return false;

        return Database::query(
            'UPDATE agendamentos SET status = $1, atualizado_em = NOW() WHERE id = $2',
            [$status, $id]
        )->rowCount() > 0;
    }

    /** Totais por status */
    public static function totaisPorStatus(): array
    {
        $rows   = Database::fetchAll('SELECT status, COUNT(*) AS total FROM agendamentos GROUP BY status');
        $result = ['pendente' => 0, 'confirmado' => 0, 'cancelado' => 0, 'concluido' => 0];
        foreach ($rows as $row) {
            $result[$row['status']] = (int)$row['total'];
        }
        return $result;
    }

    /** Agendamentos de hoje */
    public static function hoje(): array
    {
        return Database::fetchAll(
            "SELECT a.*, s.nome AS servico_nome
             FROM agendamentos a
             LEFT JOIN servicos s ON s.id = a.servico_id
             WHERE a.data_agendamento = CURRENT_DATE AND a.status != 'cancelado'
             ORDER BY a.horario"
        );
    }

    /** Total de agendamentos do mês atual */
    public static function totalMes(): int
    {
        return (int)Database::fetchColumn(
            "SELECT COUNT(*) FROM agendamentos
             WHERE DATE_TRUNC('month', data_agendamento) = DATE_TRUNC('month', CURRENT_DATE)"
        );
    }
}
