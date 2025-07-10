<?php
// migrations/2025_07_08_093005_criar_tabela_migracoes.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para criar a tabela 'migracoes'.
 */
class CriarTabelaMigracoes
{
    /**
     * Executa a migração para cima (cria a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS migracoes (
            id INT(11) NOT NULL AUTO_INCREMENT,
            numero_antigo INT(11) NOT NULL,
            ano_antigo INT(11) NOT NULL,
            territorio_antigo INT(11) NOT NULL,
            numero_novo INT(11) NOT NULL,
            ano_novo INT(11) NOT NULL,
            territorio_novo INT(11) NOT NULL,
            id_motivo_migracao INT(11) NOT NULL,
            id_usuario_criacao INT(11) DEFAULT NULL,
            data_hora_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'migracoes' criada ou já existente.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar a tabela 'migracoes': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executa a migração para baixo (remove a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS migracoes;";
        try {
            Database::execute($sql);
            echo "  - Tabela 'migracoes' removida.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover a tabela 'migracoes': " . $e->getMessage(), 0, $e);
        }
    }
}
