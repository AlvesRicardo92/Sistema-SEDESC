<?php
// migrations/2025_07_08_093017_popular_tabela_bairros.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $pdo = Database::getInstance();
    $sql = "
    INSERT INTO `bairros` (`id`, `nome`, `territorio_id`, `ativo`, `id_usuario_criacao`, `data_hora_criacao`, `id_usuario_atualizacao`, `data_hora_atualizacao`) VALUES
        (1, 'Centro', 1, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:15'),
        (2, 'Nova Esperança', 2, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:20'),
        (3, 'Jardim Primavera', 3, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:23'),
        (4, 'Vila Mariana', 4, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:26'),
        (5, 'novo Bairro II', 2, 1, 1, '2025-07-07 14:55:11', 1, '2025-07-07 14:55:11');
    ";
    $pdo->exec($sql);
    echo "  - Tabela 'bairros' alterada - Dados inseridos.\n";
} catch (PDOException $e) {
    throw new DatabaseException("Erro ao popular a tabela 'bairros': " . $e->getMessage(), 0, $e);
}