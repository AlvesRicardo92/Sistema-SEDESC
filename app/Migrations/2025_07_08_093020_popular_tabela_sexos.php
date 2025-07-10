<?php
// migrations/2025_07_08_093020_popular_tabela_sexos.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para popular a tabela 'sexos'.
 */
class PopularTabelaSexos
{
    /**
     * Executa a migração para cima (insere dados).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        INSERT INTO `sexos` (`id`, `nome`, `sigla`, `ativo`, `id_usuario_criacao`, `data_hora_criacao`, `id_usuario_atualizacao`, `data_hora_atualizacao`) VALUES
            (1, 'Masculino', 'M', 1, 1, '2025-06-28 11:27:30', 1, '2025-06-28 11:32:30'),
            (2, 'Feminino', 'F', 1, 1, '2025-06-28 11:27:30', 1, '2025-06-28 11:32:37'),
            (3, 'Não declarado', 'ND', 1, 1, '2025-06-28 11:27:30', 1, '2025-06-28 11:32:40');
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'sexos' populada com dados iniciais.\n";
        } catch (DatabaseException $e) {
            // Se os dados já existirem (UNIQUE constraint), apenas loga e continua
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "  - Dados da tabela 'sexos' já existentes. Pulando inserção.\n";
            } else {
                throw new DatabaseException("Erro ao popular a tabela 'sexos': " . $e->getMessage(), 0, $e);
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
        $sql = "TRUNCATE TABLE `sexos`;";
        try {
            Database::execute($sql);
            echo "  - Dados da tabela 'sexos' removidos (TRUNCATE).\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao truncar a tabela 'sexos': " . $e->getMessage(), 0, $e);
        }
    }
}
