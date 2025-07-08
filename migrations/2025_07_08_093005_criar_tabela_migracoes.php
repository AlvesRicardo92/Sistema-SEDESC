<?php
// migrations/2025_07_08_093005_criar_tabela_migracoes.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $pdo = Database::getInstance();
    $sql = "
    CREATE TABLE IF NOT EXISTS migracoes (
        id int(11) NOT NULL AUTO_INCREMENT,
        numero_antigo int(11) NOT NULL,
        ano_antigo int(11) NOT NULL,
        territorio_antigo int(11) NOT NULL,
        numero_novo int(11) NOT NULL,
        ano_novo int(11) NOT NULL,
        territorio_novo int(11) NOT NULL,
        id_motivo_migracao int(11) NOT NULL,
        id_usuario_criacao int(11) DEFAULT NULL,
        data_hora_criacao datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
    echo "  - Tabela 'migracoes' criada ou já existente.\n";
} catch (PDOException $e) {
    throw new DatabaseException("Erro ao criar a tabela 'migracoes': " . $e->getMessage(), 0, $e);
}