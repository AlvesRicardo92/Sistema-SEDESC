<?php
// migrations/2025_07_08_093009_criar_tabela_auditorias.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para criar a tabela 'auditorias'.
 */
class CriarTabelaAuditorias
{
    /**
     * Executa a migração para cima (cria a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS auditorias (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nome_tabela VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            acao ENUM('INSERT','UPDATE','DELETE') COLLATE utf8mb4_unicode_ci NOT NULL,
            dados_antigos JSON DEFAULT NULL,
            dados_novos JSON DEFAULT NULL,
            id_usuario_acao INT(11) DEFAULT NULL,
            data_hora_acao DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'auditorias' criada ou já existente.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar a tabela 'auditorias': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executa a migração para baixo (remove a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS auditorias;";
        try {
            Database::execute($sql);
            echo "  - Tabela 'auditorias' removida.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover a tabela 'auditorias': " . $e->getMessage(), 0, $e);
        }
    }
}
