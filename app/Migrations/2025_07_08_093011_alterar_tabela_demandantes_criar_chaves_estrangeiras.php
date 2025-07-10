<?php
// migrations/2025_07_08_093011_alterar_tabela_demandantes_criar_chaves_estrangeiras.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para adicionar chaves estrangeiras à tabela 'demandantes'.
 */
class AlterarTabelaDemandantesCriarChavesEstrangeiras
{
    /**
     * Executa a migração para cima (adiciona chaves estrangeiras).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        ALTER TABLE `demandantes`
            ADD CONSTRAINT `fk_demandante_usuario_criacao` FOREIGN KEY (`id_usuario_criacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_demandante_usuario_atualizacao` FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'demandantes' alterada - Chaves estrangeiras criadas.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar chaves estrangeiras na tabela 'demandantes': " . $e->getMessage(), 0, $e);
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
        ALTER TABLE `demandantes`
            DROP CONSTRAINT IF EXISTS `fk_demandante_usuario_criacao`,
            DROP CONSTRAINT IF EXISTS `fk_demandante_usuario_atualizacao`;
        ";
        try {
            Database::execute($sql);
            echo "  - Tabela 'demandantes' alterada - Chaves estrangeiras removidas.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover chaves estrangeiras na tabela 'demandantes': " . $e->getMessage(), 0, $e);
        }
    }
}
