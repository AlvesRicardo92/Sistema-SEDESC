<?php
// app/Utils/Database.php
namespace App\Utils;

use App\Exceptions\DatabaseException;

class Database
{
    private static $instancia = null;
    private static array $config;

    // Construtor privado para impedir a criação direta de novas instâncias (padrão Singleton)
    private function __construct() {}

    // Método privado para impedir a clonagem da instância (padrão Singleton)
    private function __clone() {}

    /**
     * Define as configurações de conexão com o banco de dados.
     * @param array $config Um array associativo com 'host', 'dbname', 'user' e 'password'.
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Retorna a única instância da conexão PDO.
     * Se a instância ainda não existir, ela é criada.
     * @return \PDO A instância da conexão PDO.
     * @throws DatabaseException Se a configuração do banco de dados não estiver definida ou se houver um erro de conexão.
     */
    public static function getInstance(): \PDO
    {
        if (self::$instancia === null) {
            // Verifica se as configurações foram definidas
            if (empty(self::$config)) {
                throw new DatabaseException("Configuração do banco de dados não definida. Use Database::setConfig() primeiro.");
            }

            // Monta a string DSN (Data Source Name) para a conexão PDO
            $dsn = "mysql:host=" . self::$config['host'] . ";dbname=" . self::$config['dbname'] . ";charset=utf8mb4";

            try {
                // Tenta criar uma nova conexão PDO
                self::$instancia = new \PDO($dsn, self::$config['user'], self::$config['password']);

                // Define atributos para o PDO:
                // ATTR_ERRMODE: Define como o PDO lida com erros (neste caso, lança exceções)
                self::$instancia->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                // ATTR_DEFAULT_FETCH_MODE: Define o modo de busca padrão para resultados (array associativo)
                self::$instancia->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            } catch (\PDOException $e) {
                // Captura a exceção PDO original e a encapsula em nossa exceção personalizada
                // Isso permite um tratamento mais específico em outras partes da aplicação,
                // enquanto ainda mantém o erro original para depuração.
                throw new DatabaseException("Erro de conexão com o banco de dados: " . $e->getMessage(), (int)$e->getCode(), $e);
            }
        }
        // Retorna a instância única da conexão
        return self::$instancia;
    }
}
