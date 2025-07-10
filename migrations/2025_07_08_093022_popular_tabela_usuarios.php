<?php
// migrations/2025_07_08_093022_popular_tabela_usuarios.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $conn = Database::getInstance();
    $sql = "
    INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `territorio_id`, `ativo`, `data_hora_criacao`, `data_hora_atualizacao`, `permissoes`, `primeiro_acesso`, `id_usuario_criacao`, `id_usuario_atualizacao`) VALUES
        (1, 'Admin Geral', 'admin.geral', '$2y$10$HJKiHas5Gm3z3uVD//KYBeoiliR6CHvyuPnQWX9pAh2/5K8ZCyDfy', 4, 1, '2025-06-28 11:22:04', '2025-07-04 08:49:30', '4111111111', 0, 1, 1),
        (2, 'Usuario Territorio I', 'user.territorio1', '$2y$10$B66VOIBg8RhxeuHSjkNCceGBtf6iyZGgCHUBCH4N7t4ytXv8GrGGy', 1, 1, '2025-06-28 11:22:04', '2025-07-02 16:52:21', '1000000000', 0, 1, 2),
        (3, 'Usuario Territorio II', 'user.territorio2', '$2y$10$1Ai0USu2PfRVdPMvbtkOKOkkk/LNt3pgiBbmCFrPE/Fz7D9STGDZi', 2, 1, '2025-06-28 11:22:04', '2025-07-03 16:03:45', '4100000000', 0, 1, 1),
        (4, 'Usuario Territorio III-1', 'user.territorio3', '$2y$10$XjbGywdZvyViUUnmMmuuQu7wfuZxCcZa2UulW5ZKmYOusr1nD8XC6', 3, 0, '2025-06-28 11:22:04', '2025-07-03 16:02:38', '211120000', 1, 1, 1),
        (5, 'Usuario Administrativo', 'user.adm', '$2y$10$FUR1hawNgfDqP/g5GBpFFuldg4cnNO9c8bk7PMPgvdNWgbzPh1bNW', 4, 1, '2025-06-28 11:22:04', '2025-07-02 16:52:28', '0001000000', 1, 1, 1),
        (6, 'nome', 'usuario.teste', '$2y$10$RTlkaq77XJ95EWt7NVw5mOr46LThsxoAlJq23NGZFcXlZZiVA8JM2', 1, 1, '2025-07-02 16:27:28', '2025-07-04 08:49:34', '4321011110', 1, 1, 1),
        (7, 'nome 2 do usuário teste', 'usuario.teste2', '$2y$10$4esztB7iWf1rc7ZDFCbdSedulJ9KFjDZhrRmb0vEbcgapUx/aQxv6', 1, 1, '2025-07-02 16:34:45', '2025-07-02 16:52:32', '4321011110', 1, 1, 1);
    ";
    // Executa a query usando o método query() do MySQLi
    $conn->query($sql);

    // Verifica se houve erro na execução da query
    if ($conn->errno) {
        throw new DatabaseException("Erro ao popular a tabela 'usuarios': " . $conn->error, $conn->errno);
    }
    echo "  - Tabela 'usuarios' alterada - Dados inseridos.\n";
} catch (\mysqli_sql_exception $e) {
    throw new DatabaseException("Erro ao popular a tabela 'usuarios': " . $e->getMessage(), 0, $e);
}