<?php
// migrations/2025_07_08_093018_popular_tabela_demandantes.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para popular a tabela 'demandantes'.
 */
class PopularTabelaDemandantes
{
    /**
     * Executa a migração para cima (insere dados).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        INSERT INTO `demandantes` (`id`, `nome`, `ativo`, `id_usuario_criacao`, `data_hora_criacao`, `id_usuario_atualizacao`, `data_hora_atualizacao`) VALUES
            (1, 'Escola Municipal Prof. João', 1, 1, '2025-06-28 11:39:40', 1, '2025-06-28 11:39:40'),
            (2, 'Unidade Básica de Saúde Central', 1, 1, '2025-06-28 11:39:40', 1, '2025-06-28 11:39:40'),
            (3, 'Secretaria de Assistência Social', 1, 1, '2025-06-28 11:39:40', 1, '2025-06-28 11:39:40'),
            (4, 'Maria Joaquina da Silva', 1, 1, '2025-06-28 11:39:40', 1, '2025-06-28 11:39:40'),
            (5, 'Conselho Tutelar Leste', 1, 1, '2025-06-28 11:39:40', 1, '2025-06-28 11:39:40');
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'demandantes' populada com dados iniciais.\n";
        } catch (DatabaseException $e) {
            // Se os dados já existirem (UNIQUE constraint), apenas loga e continua
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "  - Dados da tabela 'demandantes' já existentes. Pulando inserção.\n";
            } else {
                throw new DatabaseException("Erro ao popular a tabela 'demandantes': " . $e->getMessage(), 0, $e);
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
        $sql = "TRUNCATE TABLE `demandantes`;";
        try {
            Database::execute($sql);
            echo "  - Dados da tabela 'demandantes' removidos (TRUNCATE).\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao truncar a tabela 'demandantes': " . $e->getMessage(), 0, $e);
        }
    }
}
