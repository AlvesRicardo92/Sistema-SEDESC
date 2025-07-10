<?php
// migrate.php

require_once __DIR__ . '/autoload.php';

use App\Exceptions\DatabaseException;
use App\Utils\Database;

// Carregar as configurações do ambiente e definir no Database
// Certifique-se de que este arquivo 'config/database.php' existe e retorna um array de configuração
$dbConfig = require_once __DIR__ . '/config/database.php';
Database::setConfig($dbConfig);

echo "--- Gerenciador de Migrações --- \n\n";

try {
    $mysqli = Database::getInstance();

    // 1. Garantir que a tabela 'migrations' exista
    $createMigrationsTableSql = "
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome_migration VARCHAR(255) NOT NULL UNIQUE,
            data_hora_execucao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    Database::execute($createMigrationsTableSql);
    echo "Tabela 'migrations' verificada/criada.\n";

    // 2. Obter as migrations já executadas
    $stmt = $mysqli->query("SELECT nome_migration FROM migrations");
    if ($stmt === false) {
        throw new DatabaseException("Erro ao buscar migrations executadas: " . $mysqli->error, $mysqli->errno);
    }
    $migrations_executadas = [];
    while ($row = $stmt->fetch_assoc()) {
        $migrations_executadas[] = $row['nome_migration'];
    }
    $stmt->free_result();

    // 3. Obter todos os arquivos de migração
    $arquivos_migration = glob(__DIR__ . '/migrations/*.php');
    sort($arquivos_migration); // Garante que as migrations sejam executadas em ordem alfabética/cronológica

    $contador_novas_migrations = 0;

    // 4. Executar as novas migrations
    foreach ($arquivos_migration as $arquivo) {
        $nome_arquivo = basename($arquivo); // Ex: 2025_07_08_093000_criar_tabela_procedimentos.php
        $nome_classe = str_replace('.php', '', $nome_arquivo); // Ex: 2025_07_08_093000_criar_tabela_procedimentos
        
        // Converte o nome do arquivo para o nome da classe esperada
        // Ex: 2025_07_08_093000_criar_tabela_procedimentos -> CriarTabelaProcedimentos
        $partes_nome = explode('_', $nome_classe);
        // Remove as partes de data/hora e 'criar_tabela' ou 'alterar_tabela'
        $partes_significativas = array_slice($partes_nome, 4); 
        $nome_classe_formatado = '';
        foreach ($partes_significativas as $parte) {
            $nome_classe_formatado .= ucfirst($parte);
        }
        // Adiciona "Criar" ou "Alterar" no início se o nome original do arquivo contiver
        if (strpos($nome_classe, 'criar_tabela') !== false) {
            $nome_classe_final = 'Criar' . $nome_classe_formatado;
        } elseif (strpos($nome_classe, 'alterar_tabela') !== false) {
            $nome_classe_final = 'Alterar' . $nome_classe_formatado;
        } elseif (strpos($nome_classe, 'popular_tabela') !== false) {
            $nome_classe_final = 'Popular' . $nome_classe_formatado;
        } else {
            $nome_classe_final = $nome_classe_formatado; // Fallback
        }

        $namespace_classe = "App\\Migrations\\" . $nome_classe_final; // Assumindo namespace App\Migrations

        if (!in_array($nome_classe, $migrations_executadas)) {
            echo "Executando migration: " . $nome_classe . "...\n";
            
            // Inclui o arquivo para que a classe seja definida
            require_once $arquivo; 

            if (class_exists($namespace_classe)) {
                $migration_instance = new $namespace_classe();
                if (method_exists($migration_instance, 'up')) {
                    $migration_instance->up(); // Executa o método up() da migração

                    // Após a execução bem-sucedida, registra no banco de dados
                    $stmt_insert = Database::prepare("INSERT INTO migrations (nome_migration) VALUES (?)");
                    $stmt_insert->bind_param("s", $nome_classe);
                    $stmt_insert->execute();
                    $stmt_insert->close();

                    echo "Migration " . $nome_classe . " executada e registrada com sucesso.\n";
                    $contador_novas_migrations++;
                } else {
                    echo "Erro: Método 'up' não encontrado na classe de migração " . $namespace_classe . ".\n";
                }
            } else {
                echo "Erro: Classe de migração " . $namespace_classe . " não encontrada no arquivo " . $arquivo . ".\n";
            }
        } else {
            echo "Migration " . $nome_classe . " já executada. Pulando.\n";
        }
    }

    if ($contador_novas_migrations === 0) {
        echo "\nNenhuma nova migration para executar. Banco de dados atualizado.\n";
    } else {
        echo "\n" . $contador_novas_migrations . " nova(s) migration(s) executada(s) com sucesso.\n";
    }

} catch (DatabaseException $e) {
    echo "\nERRO CRÍTICO (DatabaseException): " . $e->getMessage() . "\n";
    error_log("Detalhes do erro original (logado): " . $e->getMessage());
    exit(1);
} catch (Exception $e) {
    echo "\nERRO CRÍTICO INESPERADO: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    Database::closeConnection();
}

echo "\n--- Migrações Concluídas --- \n";
