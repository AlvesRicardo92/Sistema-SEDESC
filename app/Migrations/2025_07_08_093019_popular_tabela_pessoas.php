<?php
// migrations/2025_07_08_093019_popular_tabela_pessoas.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para popular a tabela 'pessoas'.
 */
class PopularTabelaPessoas
{
    /**
     * Executa a migração para cima (insere dados).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        $sql = "
        INSERT INTO `pessoas` (`id`, `nome`, `data_nascimento`, `id_sexo`, `ativo`, `id_usuario_criacao`, `data_hora_criacao`, `id_usuario_atualizacao`, `data_hora_atualizacao`) VALUES
            (6, 'João Silva', '1990-05-15', 1, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
            (7, 'Maria Oliveira', '1988-11-22', 2, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
            (8, 'Carlos Souza', '1995-03-01', 1, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
            (9, 'Ana Pereira', '2000-07-30', 2, 1, 1, '2025-06-28 11:32:53', 1, '2025-07-02 15:52:53'),
            (10, 'Pedro Santos', '1985-01-20', 1, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
            (11, 'Carla Lima', '1992-09-10', 2, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
            (12, 'Lucas Fernandes', '1978-04-05', 1, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
            (13, 'Juliana Costa', '1998-12-25', 2, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
            (14, 'Fernando Rodrigues', '1970-06-18', 1, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53'),
            (15, 'Patrícia Almeida', '1993-02-14', 2, 1, 1, '2025-06-28 11:32:53', 1, '2025-06-28 11:32:53');
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'pessoas' populada com dados iniciais.\n";
        } catch (DatabaseException $e) {
            // Se os dados já existirem (UNIQUE constraint), apenas loga e continua
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "  - Dados da tabela 'pessoas' já existentes. Pulando inserção.\n";
            } else {
                throw new DatabaseException("Erro ao popular a tabela 'pessoas': " . $e->getMessage(), 0, $e);
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
        $sql = "TRUNCATE TABLE `pessoas`;";
        try {
            Database::execute($sql);
            echo "  - Dados da tabela 'pessoas' removidos (TRUNCATE).\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao truncar a tabela 'pessoas': " . $e->getMessage(), 0, $e);
        }
    }
}
