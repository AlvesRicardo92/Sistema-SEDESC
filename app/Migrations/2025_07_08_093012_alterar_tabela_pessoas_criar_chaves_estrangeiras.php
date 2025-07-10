<?php
// migrations/2025_07_08_093012_alterar_tabela_pessoas_criar_chaves_estrangeiras.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para adicionar chaves estrangeiras à tabela 'pessoas'.
 */
class AlterarTabelaPessoasCriarChavesEstrangeiras
{
    /**
     * Executa a migração para cima (adiciona chaves estrangeiras).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        ALTER TABLE `pessoas`
            ADD CONSTRAINT `fk_pessoa_sexo` FOREIGN KEY (`id_sexo`) REFERENCES `sexos` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_pessoa_usuario_criacao` FOREIGN KEY (`id_usuario_criacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_pessoa_usuario_atualizacao` FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'pessoas' alterada - Chaves estrangeiras criadas.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar chaves estrangeiras na 'pessoas': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executa a migração para baixo (remove chaves estrangeiras).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function down(): void
    {
        $sql = "
        ALTER TABLE `pessoas`
            DROP CONSTRAINT IF EXISTS `fk_pessoa_sexo`,
            DROP CONSTRAINT IF EXISTS `fk_pessoa_usuario_criacao`,
            DROP CONSTRAINT IF EXISTS `fk_pessoa_usuario_atualizacao`;
        ";
        try {
            Database::execute($sql);
            echo "  - Tabela 'pessoas' alterada - Chaves estrangeiras removidas.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover chaves estrangeiras na 'pessoas': " . $e->getMessage(), 0, $e);
        }
    }
}
