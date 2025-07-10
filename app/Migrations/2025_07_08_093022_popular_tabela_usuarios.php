<?php
// migrations/2025_07_08_093022_popular_tabela_usuarios.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para popular a tabela 'usuarios'.
 */
class PopularTabelaUsuarios
{
    /**
     * Executa a migração para cima (insere dados).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        // Senhas já estão hasheadas no SQL original
        $sql = "
        INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `territorio_id`, `ativo`, `data_hora_criacao`, `data_hora_atualizacao`, `permissoes`, `primeiro_acesso`, `id_usuario_criacao`, `id_usuario_atualizacao`) VALUES
            (1, 'Admin Geral', 'admin.geral', '$2y$10$HJKiHas5Gm3z3uVD//KYBeoiliR6CHvyuPnQWX9pAh2/5K8ZCyDfy', 4, 1, '2025-06-28 11:22:04', '2025-07-04 08:49:30', '4111111111', 0, 1, 1),
            (2, 'Usuario Territorio I', 'user.territorio1', '$2y$10$B66VOIBg8RhxeuHSjkNCceGBtf6iyZGgCHUBCH4N7t4ytXv8GrGGy', 1, 1, '2025-06-28 11:22:04', '2025-07-02 16:52:21', '1000000000', 0, 1, 2),
            (3, 'Usuario Territorio II', 'user.territorio2', '$2y$10$1Ai0USu2PfRVdPMvbtkOKOkkk/LNt3pgiBbmCFrPE/Fz7D9STGDZi', 2, 1, '2025-06-28 11:22:04', '2025-07-03 16:03:45', '4100000000', 0, 1, 1),
            (4, 'Usuario Territorio III-1', 'user.territorio3.1', '$2y$10$O0F/t.1uV5Y9eR1d.2z.5uV6Y9eR1d.2z.5uV6Y9eR1d.2z.5uV6Y9eR1d', 3, 1, '2025-06-28 11:22:04', '2025-07-03 16:03:45', '4100000000', 0, 1, 1),
            (5, 'Usuario Territorio III-2', 'user.territorio3.2', '$2y$10$O0F/t.1uV5Y9eR1d.2z.5uV6Y9eR1d.2z.5uV6Y9eR1d.2z.5uV6Y9eR1d', 3, 1, '2025-06-28 11:22:04', '2025-07-03 16:03:45', '4100000000', 0, 1, 1);
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'usuarios' populada com dados iniciais.\n";
        } catch (DatabaseException $e) {
            // Se os dados já existirem (UNIQUE constraint), apenas loga e continua
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "  - Dados da tabela 'usuarios' já existentes. Pulando inserção.\n";
            } else {
                throw new DatabaseException("Erro ao popular a tabela 'usuarios': " . $e->getMessage(), 0, $e);
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
        $sql = "TRUNCATE TABLE `usuarios`;";
        try {
            Database::execute($sql);
            echo "  - Dados da tabela 'usuarios' removidos (TRUNCATE).\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao truncar a tabela 'usuarios': " . $e->getMessage(), 0, $e);
        }
    }
}
