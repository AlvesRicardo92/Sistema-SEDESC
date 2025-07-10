<?php
// migrations/2025_07_08_093020_popular_tabela_sexos.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $conn = Database::getInstance();
    $sql = "
    INSERT INTO `sexos` (`id`, `nome`, `sigla`, `ativo`, `id_usuario_criacao`, `data_hora_criacao`, `id_usuario_atualizacao`, `data_hora_atualizacao`) VALUES
        (1, 'Masculino', 'M', 1, 1, '2025-06-28 11:27:30', 1, '2025-06-28 11:32:30'),
        (2, 'Feminino', 'F', 1, 1, '2025-06-28 11:27:30', 1, '2025-06-28 11:32:37'),
        (3, 'Não declarado', 'ND', 1, 1, '2025-06-28 11:27:30', 1, '2025-06-28 11:32:40');
    ";
    // Executa a query usando o método query() do MySQLi
    $conn->query($sql);

    // Verifica se houve erro na execução da query
    if ($conn->errno) {
        throw new DatabaseException("Erro ao popular a tabela 'sexos': " . $conn->error, $conn->errno);
    }
    echo "  - Tabela 'sexos' alterada - Dados inseridos.\n";
} catch (\mysqli_sql_exception $e) {
    throw new DatabaseException("Erro ao popular a tabela 'sexos': " . $e->getMessage(), 0, $e);
}