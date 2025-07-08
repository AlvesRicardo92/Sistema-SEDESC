<?php
// migrations/2025_07_08_093021_popular_tabela_territorios.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $pdo = Database::getInstance();
    $sql = "
    INSERT INTO `territorios_ct` (`id`, `nome`, `ativo`, `data_hora_criacao`, `data_hora_atualizacao`, `id_usuario_criacao`, `id_usuario_atualizacao`) VALUES
        (1, 'Território I', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1),
        (2, 'Território II', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1),
        (3, 'Território III', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1),
        (4, 'Administrativo', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1);
    ";
    $pdo->exec($sql);
    echo "  - Tabela 'territorios' alterada - Dados inseridos.\n";
} catch (PDOException $e) {
    throw new DatabaseException("Erro ao popular a tabela 'territorios': " . $e->getMessage(), 0, $e);
}