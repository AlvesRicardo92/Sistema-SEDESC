<?php

namespace App\Utils;

use mysqli;
use App\Exceptions\DatabaseException;

/**
 * Classe utilitária para gerenciar a conexão com o banco de dados MySQLi.
 */
class Database
{
    private static $instance = null;
    private static array $config = [];
    private $connection; // Propriedade para armazenar a instância da conexão mysqli

    /**
     * Construtor privado para implementar o padrão Singleton.
     * Impede a criação direta de novas instâncias da classe.
     *
     * @throws DatabaseException Se as configurações do banco de dados não estiverem definidas
     * ou se houver um erro na conexão com o banco de dados.
     */
    private function __construct()
    {
        // Verifica se as configurações do banco de dados foram definidas
        if (empty(self::$config)) {
            throw new DatabaseException("As configurações do banco de dados não foram definidas. Use Database::setConfig() primeiro.");
        }

        // Obtém as configurações do array estático
        $host = self::$config['host'] ?? 'localhost';
        $user = self::$config['user'] ?? 'root';
        $pass = self::$config['password'] ?? ''; // Usar 'password' conforme retornado por config/database.php
        $name = self::$config['dbname'] ?? '';   // Usar 'dbname' conforme retornado por config/database.php
        $port = self::$config['port'] ?? 3306;   // Porta padrão do MySQL

        // Tenta estabelecer a conexão com o banco de dados MySQLi
        // O '@' suprime os avisos de conexão para que possamos lidar com o erro de forma personalizada
        $this->connection = @new mysqli($host, $user, $pass, $name, $port);

        // Verifica se houve erro na conexão
        if ($this->connection->connect_error) {
            throw new DatabaseException("Erro de conexão com o banco de dados: " . $this->connection->connect_error, $this->connection->connect_errno);
        }

        // Define o charset da conexão para evitar problemas de codificação
        if (!$this->connection->set_charset("utf8mb4")) {
            error_log("Erro ao definir o charset da conexão MySQLi: " . $this->connection->error);
        }
    }

    /**
     * Método privado para impedir a clonagem da instância (padrão Singleton).
     */
    private function __clone() {}

    /**
     * Define as configurações de conexão com o banco de dados.
     * Este método deve ser chamado antes de tentar obter uma instância da conexão.
     *
     * @param array $config Um array associativo com 'host', 'dbname', 'user', 'password' e opcionalmente 'port'.
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Retorna a única instância da conexão MySQLi.
     * Implementa o padrão Singleton para garantir que haja apenas uma conexão ativa.
     *
     * @return mysqli A instância da conexão MySQLi.
     * @throws DatabaseException Se a configuração do banco de dados não estiver definida
     * ou se houver um erro de conexão.
     */
    public static function getInstance(): mysqli
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }

    /**
     * Prepara uma declaração SQL para execução segura usando prepared statements.
     *
     * @param string $sql A consulta SQL a ser preparada.
     * @return \mysqli_stmt A declaração preparada.
     * @throws DatabaseException Se a preparação da declaração falhar.
     */
    public static function prepare(string $sql): \mysqli_stmt
    {
        $mysqli = self::getInstance();
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            throw new DatabaseException("Erro ao preparar a declaração SQL: " . $mysqli->error, $mysqli->errno);
        }
        return $stmt;
    }

    /**
     * Executa uma consulta SQL que não retorna resultados (INSERT, UPDATE, DELETE, CREATE TABLE, DROP TABLE, etc.).
     *
     * @param string $sql A consulta SQL a ser executada.
     * @return bool True em caso de sucesso.
     * @throws DatabaseException Se a execução da consulta falhar.
     */
    public static function execute(string $sql): bool
    {
        $mysqli = self::getInstance();
        if (!$mysqli->query($sql)) {
            throw new DatabaseException("Erro ao executar a consulta SQL: " . $mysqli->error, $mysqli->errno);
        }
        return true;
    }

    /**
     * Fecha a conexão com o banco de dados.
     * Utilizado para liberar os recursos do banco de dados quando a aplicação não precisa mais da conexão.
     */
    public static function closeConnection(): void
    {
        if (self::$instance !== null && self::$instance->connection) {
            self::$instance->connection->close();
            self::$instance = null; // Reseta a instância para permitir uma nova conexão se necessário
        }
    }
}
