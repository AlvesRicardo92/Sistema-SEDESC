<?php
// migrations/2025_07_08_093004_criar_tabela_demandantes.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para criar a tabela 'demandantes'.
 */
class CriarTabelaDemandantes
{
    /**
     * Executa a migração para cima (cria a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS demandantes (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nome VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            ativo TINYINT(1) NOT NULL DEFAULT '1',
            id_usuario_criacao INT(11) DEFAULT NULL,
            data_hora_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            id_usuario_atualizacao INT(11) DEFAULT NULL,
            data_hora_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'demandantes' criada ou já existente.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar a tabela 'demandantes': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executa a migração para baixo (remove a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS demandantes;";
        try {
            Database::execute($sql);
            echo "  - Tabela 'demandantes' removida.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover a tabela 'demandantes': " . $e->getMessage(), 0, $e);
        }
    }
}
