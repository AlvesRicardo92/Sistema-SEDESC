<?php
// migrations/2025_07_08_093021_popular_tabela_territorios.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para popular a tabela 'territorios_ct'.
 */
class PopularTabelaTerritorios
{
    /**
     * Executa a migração para cima (insere dados).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        INSERT INTO `territorios_ct` (`id`, `nome`, `ativo`, `data_hora_criacao`, `data_hora_atualizacao`, `id_usuario_criacao`, `id_usuario_atualizacao`) VALUES
            (1, 'Território I', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1),
            (2, 'Território II', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1),
            (3, 'Território III', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1),
            (4, 'Administrativo', 1, '2025-06-28 11:21:12', '2025-06-28 11:22:55', 1, 1);
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'territorios' populada com dados iniciais.\n";
        } catch (DatabaseException $e) {
            // Se os dados já existirem (UNIQUE constraint), apenas loga e continua
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "  - Dados da tabela 'territorios' já existentes. Pulando inserção.\n";
            } else {
                throw new DatabaseException("Erro ao popular a tabela 'territorios': " . $e->getMessage(), 0, $e);
            }
        }
    }

    /**
     * Executa a migração para baixo (limpa os dados da tabela).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function down(): void
    {
        $sql = "TRUNCATE TABLE `territorios_ct`;";
        try {
            Database::execute($sql);
            echo "  - Dados da tabela 'territorios' removidos (TRUNCATE).\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao truncar a tabela 'territorios': " . $e->getMessage(), 0, $e);
        }
    }
}
