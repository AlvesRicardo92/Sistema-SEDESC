<?php
// migrations/2025_07_08_093006_criar_tabela_motivo_migracao.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $conn = Database::getInstance();
    $sql = "
    CREATE TABLE IF NOT EXISTS motivos_migracao (
        id int(11) NOT NULL AUTO_INCREMENT,
        nome varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        ativo int(11) NOT NULL,
        id_usuario_criacao int(11) DEFAULT NULL,
        data_hora_criacao datetime DEFAULT CURRENT_TIMESTAMP,
        id_usuario_atualizacao int(11) DEFAULT NULL,
        data_hora_atualizacao datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    // Executa a query usando o método query() do MySQLi
    $conn->query($sql);

    // Verifica se houve erro na execução da query
    if ($conn->errno) {
        throw new DatabaseException("Erro ao criar a tabela 'motivos_migracao': " . $conn->error, $conn->errno);
    }
    echo "  - Tabela 'motivos_migracao' criada ou já existente.\n";
} catch (\mysqli_sql_exception $e) {
    throw new DatabaseException("Erro ao criar a tabela 'motivos_migracao': " . $e->getMessage(), 0, $e);
}