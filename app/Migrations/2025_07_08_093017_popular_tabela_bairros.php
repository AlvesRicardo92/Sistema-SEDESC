<?php
// migrations/2025_07_08_093017_popular_tabela_bairros.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para popular a tabela 'bairros'.
 */
class PopularTabelaBairros
{
    /**
     * Executa a migração para cima (insere dados).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        INSERT INTO `bairros` (`id`, `nome`, `territorio_id`, `ativo`, `id_usuario_criacao`, `data_hora_criacao`, `id_usuario_atualizacao`, `data_hora_atualizacao`) VALUES
            (1, 'Centro', 1, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:15'),
            (2, 'Nova Esperança', 2, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:20'),
            (3, 'Jardim Primavera', 3, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:23'),
            (4, 'Vila Mariana', 4, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:26'),
            (5, 'São Francisco', 1, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:29'),
            (6, 'Santa Cruz', 2, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:32'),
            (7, 'Boa Vista', 3, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:35'),
            (8, 'Industrial', 4, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:38'),
            (9, 'Morada do Sol', 1, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:41'),
            (10, 'Planalto', 2, 1, 1, '2025-06-28 11:31:08', 1, '2025-06-30 12:55:44');
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'bairros' populada com dados iniciais.\n";
        } catch (DatabaseException $e) {
            // Se os dados já existirem (UNIQUE constraint), apenas loga e continua
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "  - Dados da tabela 'bairros' já existentes. Pulando inserção.\n";
            } else {
                throw new DatabaseException("Erro ao popular a tabela 'bairros': " . $e->getMessage(), 0, $e);
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
        $sql = "TRUNCATE TABLE `bairros`;";
        try {
            Database::execute($sql);
            echo "  - Dados da tabela 'bairros' removidos (TRUNCATE).\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao truncar a tabela 'bairros': " . $e->getMessage(), 0, $e);
        }
    }
}
