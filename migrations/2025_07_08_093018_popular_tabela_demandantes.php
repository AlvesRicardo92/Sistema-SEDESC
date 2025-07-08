<?php
// migrations/2025_07_08_093018_popular_tabela_demandantes.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $pdo = Database::getInstance();
    $sql = "
    INSERT INTO `demandantes` (`id`, `nome`, `ativo`, `id_usuario_criacao`, `data_hora_criacao`, `id_usuario_atualizacao`, `data_hora_atualizacao`) VALUES
        (1, 'Escola Municipal Prof. João', 1, 1, '2025-06-28 11:39:40', 1, '2025-06-28 11:39:40'),
        (2, 'Unidade Básica de Saúde Central', 1, 1, '2025-06-28 11:39:40', 1, '2025-06-28 11:39:40'),
        (3, 'Secretaria de Assistência Social', 1, 1, '2025-06-28 11:39:40', 1, '2025-06-28 11:39:40'),
        (4, 'Maria Joaquina da Silva', 1, 1, '2025-06-28 11:39:40', 1, '2025-06-28 11:39:40'),
        (5, 'Conselho Tutelar Setor Norte', 1, 1, '2025-06-28 11:39:40', 1, '2025-06-28 11:39:40'),
        (6, 'NOME CRIADO3', 1, 1, '2025-07-01 16:24:16', 1, '2025-07-01 16:24:16'),
        (7, 'EMEB Jacob Zampieri', 1, 1, '2025-07-02 09:06:35', 1, '2025-07-02 09:06:35'),
        (8, 'novoDemandante', 1, 3, '2025-07-07 16:02:50', 3, '2025-07-07 16:02:50');
    ";
    $pdo->exec($sql);
    echo "  - Tabela 'demandantes' alterada - Dados inseridos.\n";
} catch (PDOException $e) {
    throw new DatabaseException("Erro ao popular a tabela 'demandantes': " . $e->getMessage(), 0, $e);
}