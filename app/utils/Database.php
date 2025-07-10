<?php
// app/Utils/Database.php
namespace App\Utils;

use App\Exceptions\DatabaseException;

class Database
{
    private static $instancia = null;
    private static $config;

    // Construtor privado para impedir a criação direta de novas instâncias (padrão Singleton)
    private function __construct() {}

    // Método privado para impedir a clonagem da instância (padrão Singleton)
    private function __clone() {}

    /**
     * Define as configurações de conexão com o banco de dados.
     * @param array $config Um array associativo com 'host', 'dbname', 'user' e 'password'.
     */
    public static function setConfig(array $config)
    {
        self::$config = $config;
    }

    /**
     * Retorna a única instância da conexão MySQLi.
     * Se a instância ainda não existir, ela é criada.
     * @return \mysqli A instância da conexão MySQLi.
     * @throws DatabaseException Se a configuração do banco de dados não estiver definida ou se houver um erro de conexão.
     */
    public static function getInstance(): \mysqli
    {
        if (self::$instancia === null) {
            // Verifica se as configurações foram definidas
            if (empty(self::$config)) {
                throw new DatabaseException("Configuração do banco de dados não definida. Use Database::setConfig() primeiro.");
            }

            try {
                // Tenta criar uma nova conexão MySQLi
                // Use new mysqli(...) para instanciar a conexão
                self::$instancia = new \mysqli(
                    self::$config['host'],
                    self::$config['user'],
                    self::$config['password'],
                    self::$config['dbname']
                );

                // Verifica se houve erros de conexão
                if (self::$instancia->connect_error) {
                    throw new DatabaseException("Erro de conexão com o banco de dados: " . self::$instancia->connect_error, self::$instancia->connect_errno);
                }

                // Define o conjunto de caracteres para a conexão
                if (!self::$instancia->set_charset("utf8mb4")) {
                    throw new DatabaseException("Erro ao definir o conjunto de caracteres: " . self::$instancia->error, self::$instancia->errno);
                }

            } catch (\mysqli_sql_exception $e) { // Captura exceções específicas do MySQLi
                // Captura a exceção MySQLi original e a encapsula em nossa exceção personalizada
                throw new DatabaseException("Erro de conexão com o banco de dados: " . $e->getMessage(), (int)$e->getCode(), $e);
            } catch (\Exception $e) { // Captura outras exceções gerais
                throw new DatabaseException("Ocorreu um erro inesperado: " . $e->getMessage(), (int)$e->getCode(), $e);
            }
        }
        // Retorna a instância única da conexão
        return self::$instancia;
    }
}
