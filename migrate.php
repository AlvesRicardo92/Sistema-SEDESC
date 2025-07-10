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
    /** @var mysqli $conn */ 
    // Adiciona um hint de tipo para a IDE
    $conn = Database::getInstance(); // Agora retorna uma instância de mysqli

    // 1. Garantir que a tabela 'migrations' exista
    // Usar query() para comandos DDL (CREATE TABLE)
    $conn->query("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome_migration VARCHAR(255) NOT NULL UNIQUE,
            data_hora_execucao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    // Verificar se houve erro na query
    if ($conn->errno) {
        throw new DatabaseException("Erro ao criar/verificar tabela 'migrations': " . $conn->error, $conn->errno);
    }
    echo "Tabela 'migrations' verificada/criada.\n";

    // 2. Obter as migrations já executadas
    $stmt = $conn->query("SELECT nome_migration FROM migrations");
    if ($conn->errno) {
        throw new DatabaseException("Erro ao consultar migrations existentes: " . $conn->error, $conn->errno);
    }
    $migrations_executadas = [];
    if ($stmt->num_rows > 0) {
        while ($row = $stmt->fetch_assoc()) { // fetch_assoc para resultados associativos
            $migrations_executadas[] = $row['nome_migration'];
        }
    }
    $stmt->free(); // Libera o resultado

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
            $stmt = $conn->prepare("INSERT INTO migrations (nome_migration) VALUES (?)");
            if ($stmt === false) {
                throw new DatabaseException("Erro ao preparar statement para inserir migration: " . $conn->error, $conn->errno);
            }
            $stmt->bind_param("s", $nome_migration); // "s" para string, vincula o parâmetro
            if (!$stmt->execute()) { // Executa o statement
                throw new DatabaseException("Erro ao registrar migration " . $nome_migration . ": " . $stmt->error, $stmt->errno);
            }
            $stmt->close(); // Fecha o statement
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
        error_log("Exceção original no migrador: " . $e->getPrevious()->getMessage());
        echo "Detalhes do erro original (logado): " . $e->getPrevious()->getMessage() . "\n";
    }
    exit(1);
} catch (\mysqli_sql_exception $e) { // Altera para a exceção específica do MySQLi
    echo "\nERRO CRÍTICO (mysqli_sql_exception): " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\nERRO CRÍTICO INESPERADO: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    // É uma boa prática fechar a conexão, embora o PHP o faça automaticamente ao final do script
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}


echo "\n--- Migrações Concluídas --- \n";