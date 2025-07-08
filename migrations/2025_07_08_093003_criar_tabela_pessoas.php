<?php
// migrations/2025_07_08_093003_criar_tabela_pessoas.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $pdo = Database::getInstance();
    $sql = "
    CREATE TABLE IF NOT EXISTS pessoas (
        id int(11) NOT NULL AUTO_INCREMENT,
        nome varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        data_nascimento date DEFAULT NULL,
        id_sexo int(11) DEFAULT NULL,
        ativo tinyint(1) NOT NULL DEFAULT '1',
        id_usuario_criacao int(11) DEFAULT NULL,
        data_hora_criacao datetime DEFAULT CURRENT_TIMESTAMP,
        id_usuario_atualizacao int(11) DEFAULT NULL,
        data_hora_atualizacao datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
    echo "  - Tabela 'pessoas' criada ou já existente.\n";
} catch (PDOException $e) {
    throw new DatabaseException("Erro ao criar a tabela 'pessoas': " . $e->getMessage(), 0, $e);
}