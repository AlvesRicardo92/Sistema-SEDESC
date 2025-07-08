<?php
// migrations/2025_07_08_093015_alterar_tabela_territorios.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $pdo = Database::getInstance();
    $sql = "
    ALTER TABLE `usuarios`
        ADD CONSTRAINT `fk_usuario_atualizacao` FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_usuario_criacao` FOREIGN KEY (`id_usuario_criacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_usuario_territorio` FOREIGN KEY (`territorio_id`) REFERENCES `territorios` (`id`) ON UPDATE CASCADE;
    ";
    $pdo->exec($sql);
    echo "  - Tabela 'usuarios' alterada - Chaves estrangeiras criadas.\n";
} catch (PDOException $e) {
    throw new DatabaseException("Erro ao criar chaves estrangeiras na tabela 'usuarios': " . $e->getMessage(), 0, $e);
}