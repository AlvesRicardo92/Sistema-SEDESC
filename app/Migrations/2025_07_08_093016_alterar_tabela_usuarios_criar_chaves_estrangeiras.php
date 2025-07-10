<?php
// migrations/2025_07_08_093016_alterar_tabela_usuarios_criar_chaves_estrangeiras.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para adicionar chaves estrangeiras à tabela 'usuarios'.
 */
class AlterarTabelaUsuariosCriarChavesEstrangeiras
{
    /**
     * Executa a migração para cima (adiciona chaves estrangeiras).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        ALTER TABLE `usuarios`
            ADD CONSTRAINT `fk_usuario_atualizacao` FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_usuario_criacao` FOREIGN KEY (`id_usuario_criacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_usuario_territorio` FOREIGN KEY (`territorio_id`) REFERENCES `territorios_ct` (`id`) ON UPDATE CASCADE;
        "; // Note: changed `territorios` to `territorios_ct` based on earlier migration

        try {
            Database::execute($sql);
            echo "  - Tabela 'usuarios' alterada - Chaves estrangeiras criadas.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar chaves estrangeiras na tabela 'usuarios': " . $e->getMessage(), 0, $e);
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
        ALTER TABLE `usuarios`
            DROP CONSTRAINT IF EXISTS `fk_usuario_atualizacao`,
            DROP CONSTRAINT IF EXISTS `fk_usuario_criacao`,
            DROP CONSTRAINT IF EXISTS `fk_usuario_territorio`;
        ";
        try {
            Database::execute($sql);
            echo "  - Tabela 'usuarios' alterada - Chaves estrangeiras removidas.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover chaves estrangeiras na tabela 'usuarios': " . $e->getMessage(), 0, $e);
        }
    }
}
