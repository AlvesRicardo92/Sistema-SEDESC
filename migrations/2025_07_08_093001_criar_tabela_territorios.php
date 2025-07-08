<?php
// migrations/2025_07_08_093001_criar_tabela_territorios.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $pdo = Database::getInstance();
    $sql = "
    CREATE TABLE IF NOT EXISTS territorios_ct (
        id int(11) NOT NULL AUTO_INCREMENT,
        nome varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
        ativo tinyint(1) NOT NULL DEFAULT '1',
        data_hora_criacao datetime DEFAULT CURRENT_TIMESTAMP,
        data_hora_atualizacao datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        id_usuario_criacao int(11) DEFAULT NULL,
        id_usuario_atualizacao int(11) DEFAULT NULL,
        PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
    echo "  - Tabela 'territórios' criada ou já existente.\n";
} catch (PDOException $e) {
    throw new DatabaseException("Erro ao criar a tabela 'territórios': " . $e->getMessage(), 0, $e);
}