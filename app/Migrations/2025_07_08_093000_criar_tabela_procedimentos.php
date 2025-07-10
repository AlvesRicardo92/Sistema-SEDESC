<?php
// migrations/2025_07_08_093000_criar_tabela_procedimentos.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para criar a tabela 'procedimentos'.
 */
class CriarTabelaProcedimentos
{
    /**
     * Executa a migração para cima (cria a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS procedimentos (
            id INT(11) NOT NULL AUTO_INCREMENT,
            numero_procedimento INT(11) NOT NULL,
            ano_procedimento INT(11) NOT NULL,
            id_territorio INT(11) NOT NULL,
            id_bairro INT(11) DEFAULT NULL,
            id_pessoa INT(11) DEFAULT NULL,
            id_genitora_pessoa INT(11) DEFAULT NULL,
            id_demandante INT(11) DEFAULT NULL,
            ativo TINYINT(1) NOT NULL DEFAULT '1',
            migrado INT(11) NOT NULL DEFAULT '0',
            id_migracao INT(11) DEFAULT NULL,
            data_criacao DATE NOT NULL,
            hora_criacao TIME DEFAULT NULL,
            id_usuario_criacao INT(11) DEFAULT NULL,
            id_usuario_atualizacao INT(11) DEFAULT NULL,
            data_hora_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'procedimentos' criada ou já existente.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar a tabela 'procedimentos': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executa a migração para baixo (remove a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS procedimentos;";
        try {
            Database::execute($sql);
            echo "  - Tabela 'procedimentos' removida.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover a tabela 'procedimentos': " . $e->getMessage(), 0, $e);
        }
    }
}
