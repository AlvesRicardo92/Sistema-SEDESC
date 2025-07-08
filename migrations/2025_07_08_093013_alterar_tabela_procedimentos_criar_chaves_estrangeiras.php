<?php
// migrations/2025_07_08_093013_alterar_tabela_procedimentos_criar_chaves_estrangeiras.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

try {
    $pdo = Database::getInstance();
    $sql = "
    ALTER TABLE `procedimentos`
        ADD CONSTRAINT `fk_procedimento_territorio` FOREIGN KEY (`id_territorio`) REFERENCES `territorios` (`id`),
        ADD CONSTRAINT `fk_procedimento_bairro` FOREIGN KEY (`id_bairro`) REFERENCES `bairros` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_procedimento_pessoa` FOREIGN KEY (`id_pessoa`) REFERENCES `pessoas` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_procedimento_genitora_pessoa` FOREIGN KEY (`id_genitora_pessoa`) REFERENCES `pessoas` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_procedimento_demandante` FOREIGN KEY (`id_demandante`) REFERENCES `demandantes` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_usuario_criacao` FOREIGN KEY (`id_usuario_criacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
        ADD CONSTRAINT `fk_usuario_atualizacao` FOREIGN KEY (`id_usuario_atualizacao`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
    ";
    $pdo->exec($sql);
    echo "  - Tabela 'procedimentos' alterada - Chaves estrangeiras criadas.\n";
} catch (PDOException $e) {
    throw new DatabaseException("Erro ao criar chaves estrangeiras na tabela 'procedimentos': " . $e->getMessage(), 0, $e);
}