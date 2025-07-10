<?php
// rollback.php

require_once __DIR__ . '/autoload.php';

use App\Exceptions\DatabaseException;
use App\Utils\Database;

// Carregar as configurações do ambiente e definir no Database
$dbConfig = require_once __DIR__ . '/config/database.php';
Database::setConfig($dbConfig);

echo "--- Gerenciador de Rollbacks (Completo) --- \n\n";

try {
    $mysqli = Database::getInstance();

    // 1. Obter TODAS as migrations executadas em ordem inversa de execução
    $stmt = $mysqli->query("SELECT nome_migration FROM migrations ORDER BY id DESC");
    if ($stmt === false) {
        throw new DatabaseException("Erro ao buscar migrations executadas para rollback: " . $mysqli->error, $mysqli->errno);
    }

    $migrations_executadas = [];
    while ($row = $stmt->fetch_assoc()) {
        $migrations_executadas[] = $row['nome_migration'];
    }
    $stmt->free_result();

    if (empty($migrations_executadas)) {
        echo "Nenhuma migration encontrada para rollback.\n";
        exit(0);
    }

    $contador_rollbacks = 0;

    // 2. Executar o rollback para cada migration, da mais recente para a mais antiga
    foreach ($migrations_executadas as $nome_migration_executada) {
        $arquivo_migration = __DIR__ . '/migrations/' . $nome_migration_executada . '.php';

        if (!file_exists($arquivo_migration)) {
            echo "Aviso: Arquivo de migration não encontrado para rollback: " . $arquivo_migration . ". Pulando.\n";
            continue; // Pula para a próxima migration se o arquivo não for encontrado
        }

        // Converte o nome do arquivo para o nome da classe esperada
        $partes_nome = explode('_', $nome_migration_executada);
        $partes_significativas = array_slice($partes_nome, 4);
        $nome_classe_formatado = '';
        foreach ($partes_significativas as $parte) {
            $nome_classe_formatado .= ucfirst($parte);
        }
        if (strpos($nome_migration_executada, 'criar_tabela') !== false) {
            $nome_classe_final = 'Criar' . $nome_classe_formatado;
        } elseif (strpos($nome_migration_executada, 'alterar_tabela') !== false) {
            $nome_classe_final = 'Alterar' . $nome_classe_formatado;
        } elseif (strpos($nome_migration_executada, 'popular_tabela') !== false) {
            $nome_classe_final = 'Popular' . $nome_classe_formatado;
        } else {
            $nome_classe_final = $nome_classe_formatado; // Fallback
        }
        $namespace_classe = "App\\Migrations\\" . $nome_classe_final;

        echo "Executando rollback para: " . $nome_migration_executada . "...\n";

        require_once $arquivo_migration; // Inclui o arquivo para que a classe seja definida

        if (class_exists($namespace_classe)) {
            $migration_instance = new $namespace_classe();
            if (method_exists($migration_instance, 'down')) {
                $migration_instance->down(); // Executa o método down() da migração

                // Remove o registro da migration da tabela 'migrations'
                $stmt_delete = Database::prepare("DELETE FROM migrations WHERE nome_migration = ?");
                $stmt_delete->bind_param("s", $nome_migration_executada);
                $stmt_delete->execute();
                $stmt_delete->close();

                echo "Rollback para " . $nome_migration_executada . " executado e registro removido com sucesso.\n";
                $contador_rollbacks++;
            } else {
                echo "Erro: Método 'down' não encontrado na classe de migration " . $namespace_classe . ". Pulando.\n";
            }
        } else {
            echo "Erro: Classe de migration " . $namespace_classe . " não encontrada no arquivo " . $arquivo_migration . ". Pulando.\n";
        }
    }

    if ($contador_rollbacks === 0) {
        echo "\nNenhum rollback executado.\n";
    } else {
        echo "\n" . $contador_rollbacks . " migration(s) revertida(s) com sucesso.\n";
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

echo "\n--- Rollbacks Concluídos --- \n";
