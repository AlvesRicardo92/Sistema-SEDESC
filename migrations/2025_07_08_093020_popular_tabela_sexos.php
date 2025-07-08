<?php
// migrations/2025_07_08_093020_popular_tabela_sexos.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $pdo = Database::getInstance();
    $sql = "
    INSERT INTO `sexos` (`id`, `nome`, `sigla`, `ativo`, `id_usuario_criacao`, `data_hora_criacao`, `id_usuario_atualizacao`, `data_hora_atualizacao`) VALUES
        (1, 'Masculino', 'M', 1, 1, '2025-06-28 11:27:30', 1, '2025-06-28 11:32:30'),
        (2, 'Feminino', 'F', 1, 1, '2025-06-28 11:27:30', 1, '2025-06-28 11:32:37'),
        (3, 'Não declarado', 'ND', 1, 1, '2025-06-28 11:27:30', 1, '2025-06-28 11:32:40');
    ";
    $pdo->exec($sql);
    echo "  - Tabela 'sexos' alterada - Dados inseridos.\n";
} catch (PDOException $e) {
    throw new DatabaseException("Erro ao popular a tabela 'sexos': " . $e->getMessage(), 0, $e);
}