<?php
// migrations/2025_07_08_093009_criar_tabela_auditorias.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $conn = Database::getInstance();
    $sql = "
    CREATE TABLE IF NOT EXISTS auditorias (
        id int(11) NOT NULL AUTO_INCREMENT,
        nome_tabela varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        acao enum('INSERT','UPDATE','DELETE') COLLATE utf8mb4_unicode_ci NOT NULL,
        dados_antigos json DEFAULT NULL,
        dados_novos json DEFAULT NULL,
        id_usuario_acao int(11) DEFAULT NULL,
        data_hora_acao datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    // Executa a query usando o método query() do MySQLi
    $conn->query($sql);

    // Verifica se houve erro na execução da query
    if ($conn->errno) {
        throw new DatabaseException("Erro ao criar a tabela 'auditorias': " . $conn->error, $conn->errno);
    }
    echo "  - Tabela 'auditorias' criada ou já existente.\n";
} catch (\mysqli_sql_exception $e) {
    throw new DatabaseException("Erro ao criar a tabela 'auditorias': " . $e->getMessage(), 0, $e);
}