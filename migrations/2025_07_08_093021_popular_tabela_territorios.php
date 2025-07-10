<?php
// migrations/2025_07_08_093021_popular_tabela_territorios.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $conn = Database::getInstance();
    $sql = "
    INSERT INTO `territorios_ct` (`id`, `nome`, `ativo`, `data_hora_criacao`, `data_hora_atualizacao`, `id_usuario_criacao`, `id_usuario_atualizacao`) VALUES
        (1, 'Território I', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1),
        (2, 'Território II', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1),
        (3, 'Território III', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1),
        (4, 'Administrativo', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1);
    ";
    // Executa a query usando o método query() do MySQLi
    $conn->query($sql);

    // Verifica se houve erro na execução da query
    if ($conn->errno) {
        throw new DatabaseException("Erro ao popular a tabela 'territorios': " . $conn->error, $conn->errno);
    }
    echo "  - Tabela 'territorios' alterada - Dados inseridos.\n";
} catch (\mysqli_sql_exception $e) {
    throw new DatabaseException("Erro ao popular a tabela 'territorios': " . $e->getMessage(), 0, $e);
}