<?php
// migrations/2025_07_08_093012_alterar_tabela_pessoas.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $pdo = Database::getInstance();
    $sql = "
    ALTER TABLE `pessoas`
        ADD CONSTRAINT `fk_pessoa_sexo` FOREIGN KEY (`id_sexo`) REFERENCES `sexos` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_usuario_criacao` FOREIGN KEY (`id_usuario_criacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_usuario_atualizacao` FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
    ";
    $pdo->exec($sql);
    echo "  - Tabela 'pessoas' alterada - Chaves estrangeiras criadas.\n";
} catch (PDOException $e) {
    throw new DatabaseException("Erro ao criar chaves estrangeiras na 'pessoas': " . $e->getMessage(), 0, $e);
}