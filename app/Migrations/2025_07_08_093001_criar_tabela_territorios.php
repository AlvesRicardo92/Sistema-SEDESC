<?php
// migrations/2025_07_08_093001_criar_tabela_territorios.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para criar a tabela 'territorios_ct'.
 */
class CriarTabelaTerritorios
{
    /**
     * Executa a migração para cima (cria a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS territorios_ct (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nome VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            ativo TINYINT(1) NOT NULL DEFAULT '1',
            data_hora_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_hora_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            id_usuario_criacao INT(11) DEFAULT NULL,
            id_usuario_atualizacao INT(11) DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'territórios' criada ou já existente.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar a tabela 'territórios': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executa a migração para baixo (remove a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS territorios_ct;";
        try {
            Database::execute($sql);
            echo "  - Tabela 'territórios' removida.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover a tabela 'territórios': " . $e->getMessage(), 0, $e);
        }
    }
}
