<?php
// migrations/2025_07_08_093025_criar_tabela_avisos.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para criar a tabela 'avisos'.
 */
class CriarTabelaAvisos
{
    /**
     * Executa a migração para cima (cria a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS avisos (
            id INT(11) NOT NULL AUTO_INCREMENT,
            descricao TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
            id_territorio_exibicao INT(11) DEFAULT NULL,
            data_inicio_exibicao DATE NOT NULL,
            data_fim_exibicao DATE NOT NULL,
            nome_imagem VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            id_usuario_criacao INT(11) DEFAULT NULL,
            data_hora_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            id_usuario_atualizacao INT(11) DEFAULT NULL,
            data_hora_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY fk_aviso_territorio (id_territorio_exibicao),
            KEY fk_aviso_usuario_criacao (id_usuario_criacao),
            KEY fk_aviso_usuario_atualizacao (id_usuario_atualizacao),
            CONSTRAINT fk_aviso_territorio FOREIGN KEY (id_territorio_exibicao) REFERENCES territorios_ct (id) ON UPDATE CASCADE,
            CONSTRAINT fk_aviso_usuario_criacao FOREIGN KEY (id_usuario_criacao) REFERENCES usuarios (id) ON UPDATE CASCADE,
            CONSTRAINT fk_aviso_usuario_atualizacao FOREIGN KEY (id_usuario_atualizacao) REFERENCES usuarios (id) ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'avisos' criada ou já existente.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar a tabela 'avisos': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executa a migração para baixo (remove a tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS avisos;";
        try {
            Database::execute($sql);
            echo "  - Tabela 'avisos' removida.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover a tabela 'avisos': " . $e->getMessage(), 0, $e);
        }
    }
}
