<?php
// migrations/2025_07_08_093019_popular_tabela_pessoas.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $conn = Database::getInstance();
    $sql = "
    INSERT INTO `pessoas` (`id`, `nome`, `data_nascimento`, `id_sexo`, `ativo`, `id_usuario_criacao`, `data_hora_criacao`, `id_usuario_atualizacao`, `data_hora_atualizacao`) VALUES
        (6, 'João Silva', '1990-05-15', 1, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
        (7, 'Maria Oliveira', '1988-11-22', 2, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
        (8, 'Carlos Souza', '1995-03-01', 1, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
        (9, 'Ana Pereira', '2000-07-30', 2, 1, 1, '2025-06-28 11:32:53', 1, '2025-07-02 15:52:54'),
        (10, 'Pedro Santos', '1975-01-01', 3, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
        (11, 'NOME CRIADO1', '2025-07-29', 1, 1, 1, '2025-07-01 16:24:16', 1, '2025-07-01 16:24:16'),
        (12, 'NOME CRIADO2', '2025-07-08', 1, 1, 1, '2025-07-01 16:24:16', 1, '2025-07-01 16:24:16'),
        (13, 'nova genitora1', '2025-07-01', 3, 1, 1, '2025-07-02 09:06:35', 1, '2025-07-02 09:06:35'),
        (14, 'novoNome terrotório 3', '2025-07-17', 3, 1, 3, '2025-07-07 16:02:50', 3, '2025-07-07 16:02:50'),
        (15, 'novaGenitora terrotório 3', '2025-07-09', 1, 1, 3, '2025-07-07 16:02:50', 3, '2025-07-07 16:02:50');
    ";
    // Executa a query usando o método query() do MySQLi
    $conn->query($sql);

    // Verifica se houve erro na execução da query
    if ($conn->errno) {
        throw new DatabaseException("Erro ao popular a tabela 'pessoas': " . $conn->error, $conn->errno);
    }
    echo "  - Tabela 'pessoas' alterada - Dados inseridos.\n";
} catch (\mysqli_sql_exception $e) {
    throw new DatabaseException("Erro ao popular a tabela 'pessoas': " . $e->getMessage(), 0, $e);
}