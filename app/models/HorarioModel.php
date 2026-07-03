<?php

declare(strict_types=1);

namespace App\models;

/**
 * Configuração de horários disponíveis por dia da semana
 */
final class HorarioModel
{
    private static array $diasNomes = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];

    /** Retorna configuração de todos os 7 dias */
    public static function todos(): array
    {
        $rows   = Database::fetchAll('SELECT * FROM horarios_disponiveis ORDER BY dia_semana');
        $result = [];
        foreach ($rows as $row) {
            $result[$row['dia_semana']] = $row;
        }

        // Garante todos os 7 dias mesmo que não existam no BD
        for ($i = 0; $i <= 6; $i++) {
            if (!isset($result[$i])) {
                $result[$i] = [
                    'id'              => null,
                    'dia_semana'      => $i,
                    'hora_inicio'     => '08:00',
                    'hora_fim'        => '18:00',
                    'duracao_minutos' => 60,
                    'ativo'           => false,
                ];
            }
            $result[$i]['nome_dia'] = self::$diasNomes[$i];
        }

        ksort($result);
        return $result;
    }

    /** Retorna config de um dia específico */
    public static function porDia(int $diaSemana): array|false
    {
        return Database::fetchOne(
            'SELECT * FROM horarios_disponiveis WHERE dia_semana = $1 AND ativo = TRUE',
            [$diaSemana]
        );
    }

    /**
     * Gera lista de slots de horário para um dia da semana configurado
     * Retorna array de strings "HH:MM"
     */
    public static function gerarSlots(int $diaSemana): array
    {
        $config = self::porDia($diaSemana);
        if (!$config) return [];

        $slots    = [];
        $inicio   = strtotime($config['hora_inicio']);
        $fim      = strtotime($config['hora_fim']);
        $duracao  = (int)$config['duracao_minutos'] * 60;

        for ($t = $inicio; $t < $fim; $t += $duracao) {
            $slots[] = date('H:i', $t);
        }

        return $slots;
    }

    /** Upsert de um dia */
    public static function salvar(int $diaSemana, array $data): void
    {
        $existing = Database::fetchOne(
            'SELECT id FROM horarios_disponiveis WHERE dia_semana = $1',
            [$diaSemana]
        );

        if ($existing) {
            Database::query(
                'UPDATE horarios_disponiveis
                 SET hora_inicio = $1, hora_fim = $2, duracao_minutos = $3, ativo = $4, atualizado_em = NOW()
                 WHERE dia_semana = $5',
                [
                    $data['hora_inicio'],
                    $data['hora_fim'],
                    (int)$data['duracao_minutos'],
                    (bool)$data['ativo'],
                    $diaSemana,
                ]
            );
        } else {
            Database::query(
                'INSERT INTO horarios_disponiveis (dia_semana, hora_inicio, hora_fim, duracao_minutos, ativo)
                 VALUES ($1, $2, $3, $4, $5)',
                [
                    $diaSemana,
                    $data['hora_inicio'],
                    $data['hora_fim'],
                    (int)$data['duracao_minutos'],
                    (bool)$data['ativo'],
                ]
            );
        }
    }

    public static function nomeDia(int $diaSemana): string
    {
        return self::$diasNomes[$diaSemana] ?? 'Desconhecido';
    }

    /** Retorna quais dias da semana estão ativos */
    public static function diasAtivos(): array
    {
        return Database::fetchAll(
            'SELECT dia_semana FROM horarios_disponiveis WHERE ativo = TRUE ORDER BY dia_semana'
        );
    }
}
