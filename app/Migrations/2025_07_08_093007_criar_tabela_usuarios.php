<?php
// migrations/2025_07_08_093007_criar_tabela_usuarios.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para criar a tabela 'usuarios'.
 */
class CriarTabelaUsuarios
{
    /**
     * Executa a migração para cima (cria a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nome VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            usuario VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
            senha VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            territorio_id INT(11) DEFAULT NULL,
            ativo TINYINT(1) NOT NULL DEFAULT '1',
            permissoes VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000',
            primeiro_acesso INT(11) NOT NULL DEFAULT '1',
            id_usuario_criacao INT(11) DEFAULT NULL,
            data_hora_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            id_usuario_atualizacao INT(11) DEFAULT NULL,
            data_hora_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'usuarios' criada ou já existente.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar a tabela 'usuarios': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executa a migração para baixo (remove a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS usuarios;";
        try {
            Database::execute($sql);
            echo "  - Tabela 'usuarios' removida.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover a tabela 'usuarios': " . $e->getMessage(), 0, $e);
        }
    }
}
