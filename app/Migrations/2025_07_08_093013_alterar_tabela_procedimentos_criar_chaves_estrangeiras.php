<?php
// migrations/2025_07_08_093013_alterar_tabela_procedimentos_criar_chaves_estrangeiras.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para adicionar chaves estrangeiras à tabela 'procedimentos'.
 */
class AlterarTabelaProcedimentosCriarChavesEstrangeiras
{
    /**
     * Executa a migração para cima (adiciona chaves estrangeiras).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        ALTER TABLE `procedimentos`
            ADD CONSTRAINT `fk_procedimento_territorio` FOREIGN KEY (`id_territorio`) REFERENCES `territorios_ct` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_procedimento_bairro` FOREIGN KEY (`id_bairro`) REFERENCES `bairros` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_procedimento_pessoa` FOREIGN KEY (`id_pessoa`) REFERENCES `pessoas` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_procedimento_genitora_pessoa` FOREIGN KEY (`id_genitora_pessoa`) REFERENCES `pessoas` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_procedimento_demandante` FOREIGN KEY (`id_demandante`) REFERENCES `demandantes` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_procedimento_usuario_criacao` FOREIGN KEY (`id_usuario_criacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_procedimento_usuario_atualizacao` FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
        "; // Note: changed `territorios` to `territorios_ct` based on earlier migration

        try {
            Database::execute($sql);
            echo "  - Tabela 'procedimentos' alterada - Chaves estrangeiras criadas.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao criar chaves estrangeiras na 'procedimentos': " . $e->getMessage(), 0, $e);
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
        ALTER TABLE `procedimentos`
            DROP CONSTRAINT IF EXISTS `fk_procedimento_territorio`,
            DROP CONSTRAINT IF EXISTS `fk_procedimento_bairro`,
            DROP CONSTRAINT IF EXISTS `fk_procedimento_pessoa`,
            DROP CONSTRAINT IF EXISTS `fk_procedimento_genitora_pessoa`,
            DROP CONSTRAINT IF EXISTS `fk_procedimento_demandante`,
            DROP CONSTRAINT IF EXISTS `fk_procedimento_usuario_criacao`,
            DROP CONSTRAINT IF EXISTS `fk_procedimento_usuario_atualizacao`;
        ";
        try {
            Database::execute($sql);
            echo "  - Tabela 'procedimentos' alterada - Chaves estrangeiras removidas.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover chaves estrangeiras na 'procedimentos': " . $e->getMessage(), 0, $e);
        }
    }
}
