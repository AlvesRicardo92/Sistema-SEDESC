<?php
// migrations/2025_07_08_093010_alterar_tabela_bairros_criar_chaves_estrangeiras.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $conn = Database::getInstance();
    $sql = "
    ALTER TABLE `bairros`
        ADD CONSTRAINT `fk_usuario_criacao` FOREIGN KEY (`id_usuario_criacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_usuario_atualizacao` FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_bairro_territorio` FOREIGN KEY (`territorio_id`) REFERENCES `territorios` (`id`) ON UPDATE CASCADE;
    ";
    // Executa a query usando o método query() do MySQLi
    $conn->query($sql);

    // Verifica se houve erro na execução da query
    if ($conn->errno) {
        throw new DatabaseException("Erro ao criar chaves estrangeiras na tabela 'bairros': " . $conn->error, $conn->errno);
    }
    echo "  - Tabela 'bairros' alterada - Chaves estrangeiras criadas.\n";
} catch (\mysqli_sql_exception $e) {
    throw new DatabaseException("Erro ao criar chaves estrangeiras na tabela 'bairros': " . $e->getMessage(), 0, $e);
}