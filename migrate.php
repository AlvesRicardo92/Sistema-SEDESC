<?php
// migrate.php

require_once __DIR__ . '/autoload.php';

use App\Exceptions\DatabaseException;
use App\Utils\Database;

// Carregar as configurações do ambiente e definir no Database
$dbConfig = require_once __DIR__ . '/config/database.php';
Database::setConfig($dbConfig);

echo "--- Gerenciador de Migrações --- \n\n";

try {
    $pdo = Database::getInstance();

    // 1. Garantir que a tabela 'migrations' exista
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome_migration VARCHAR(255) NOT NULL UNIQUE,
            data_hora_execucao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabela 'migrations' verificada/criada.\n";

    // 2. Obter as migrations já executadas
    $stmt = $pdo->query("SELECT nome_migracao FROM migrations");
    $migrations_executadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Obter todos os arquivos de migração
    $arquivos_migration = glob(__DIR__ . '/migrations/*.php');
    sort($arquivos_migration); // Garante que as migrations sejam executadas em ordem alfabética/cronológica

    $contador_novas_migrations = 0;

    // 4. Executar as novas migrations
    foreach ($arquivos_migration as $arquivo) {
        $nome_migration = basename($arquivo, '.php');

        if (!in_array($nome_migration, $migrations_executadas)) {
            echo "Executando migration: " . $nome_migration . "...\n";
            require $arquivo; // Inclui e executa o conteúdo do arquivo de migração

            // Após a execução bem-sucedida, registra no banco de dados
            $stmt = $pdo->prepare("INSERT INTO migrations (nome_migration) VALUES (:nome)");
            $stmt->execute([':nome' => $nome_migration]);
            echo "Migration " . $nome_migration . " executada e registrada com sucesso.\n";
            $contador_novas_migrations++;
        } else {
            echo "Migration " . $nome_migration . " já executada. Pulando.\n";
        }
    }

    if ($contador_novas_migrations === 0) {
        echo "\nNenhuma nova migration para executar. Banco de dados atualizado.\n";
    } else {
        echo "\n" . $contador_novas_migrations . " nova(s) migration(s) executada(s) com sucesso.\n";
    }

} catch (DatabaseException $e) {
    echo "\nERRO CRÍTICO (DatabaseException): " . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        error_log("PDOException original no migrador: " . $e->getPrevious()->getMessage());
        echo "Detalhes do erro original (logado): " . $e->getPrevious()->getMessage() . "\n";
    }
    exit(1);
} catch (PDOException $e) {
    echo "\nERRO CRÍTICO (PDOException): " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\nERRO CRÍTICO INESPERADO: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n--- Migrações Concluídas --- \n";