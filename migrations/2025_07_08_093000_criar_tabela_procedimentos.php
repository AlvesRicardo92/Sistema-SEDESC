<?php
// migrations/2025_07_08_093000_criar_tabela_procedimentos.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $conn = Database::getInstance();
    $sql = "
    CREATE TABLE IF NOT EXISTS procedimentos (
        id int(11) NOT NULL AUTO_INCREMENT,
        numero_procedimento int(11) NOT NULL,
        ano_procedimento int(11) NOT NULL,
        id_territorio int(11) NOT NULL,
        id_bairro int(11) DEFAULT NULL,
        id_pessoa int(11) DEFAULT NULL,
        id_genitora_pessoa int(11) DEFAULT NULL,
        id_demandante int(11) DEFAULT NULL,
        ativo tinyint(1) NOT NULL DEFAULT '1',
        migrado int(11) NOT NULL DEFAULT '0',
        id_migracao int(11) DEFAULT NULL,
        data_criacao date NOT NULL,
        hora_criacao time DEFAULT NULL,
        id_usuario_criacao int(11) DEFAULT NULL,
        id_usuario_atualizacao int(11) DEFAULT NULL,
        data_hora_atualizacao datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    // Executa a query usando o método query() do MySQLi
    $conn->query($sql);

    // Verifica se houve erro na execução da query
    if ($conn->errno) {
        throw new DatabaseException("Erro ao criar a tabela 'procedimentos': " . $conn->error, $conn->errno);
    }
    echo "  - Tabela 'procedimentos' criada ou já existente.\n";
} catch (\mysqli_sql_exception $e) {
    throw new DatabaseException("Erro ao criar a tabela 'procedimentos': " . $e->getMessage(), 0, $e);
}