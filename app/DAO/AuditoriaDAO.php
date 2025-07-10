<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\Auditoria;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade Auditoria.
 * Nota: A tabela de auditoria geralmente não tem operações de 'criar' ou 'atualizar' diretas
 * via DAO, pois é populada por triggers. No entanto, as interfaces exigem esses métodos.
 * Implementaremos de forma a refletir isso ou lançar exceções se a operação não for suportada.
 */
class AuditoriaDAO implements BaseDAOInterface
{
    private $tableName = 'auditorias';
    private $modelClass = Auditoria::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return Auditoria O objeto Auditoria.
     */
    private function hydrate(array $data): Auditoria
    {
        return new Auditoria($data);
    }

    /**
     * Busca todos os registros de auditoria.
     *
     * @return array Um array de objetos Auditoria.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os registros de auditoria: " . $mysqli->error, $mysqli->errno);
        }

        $auditorias = [];
        while ($row = $result->fetch_assoc()) {
            $auditorias[] = $this->hydrate($row);
        }
        $result->free();
        return $auditorias;
    }

    /**
     * Não aplicável para a tabela 'auditorias' (não possui campo 'ativo').
     * Retorna um array vazio ou lança uma exceção.
     *
     * @return array Um array de objetos Auditoria.
     */
    public function buscarTodosAtivos(): array
    {
        // A tabela de auditoria geralmente não tem um campo 'ativo'.
        // Retorna todos os registros, ou pode-se lançar uma exceção se a semântica for estritamente "ativos".
        return $this->buscarTodos();
    }

    /**
     * Busca um registro de auditoria pelo seu ID.
     *
     * @param int $id O ID do registro de auditoria a ser buscado.
     * @return Auditoria|null O objeto Auditoria ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?Auditoria
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar registro de auditoria por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca registros de auditoria pelo nome da tabela ou ação, utilizando a palavra-chave LIKE.
     *
     * @param string $termo O termo de busca para nome da tabela ou ação.
     * @return array Um array de objetos Auditoria.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorNome(string $termo): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nome_tabela LIKE ? OR acao LIKE ?";
        $stmt = Database::prepare($sql);
        $termoLike = "%{$termo}%";
        $stmt->bind_param("ss", $termoLike, $termoLike);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar registros de auditoria por termo: " . $stmt->error, $stmt->errno);
        }

        $auditorias = [];
        while ($row = $result->fetch_assoc()) {
            $auditorias[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $auditorias;
    }

    /**
     * Cria um novo registro de auditoria no banco de dados.
     *
     * Esta função não deve ser chamada diretamente, pois os registros de auditoria
     * são criados por triggers no banco de dados.
     *
     * @param Auditoria $auditoria O objeto Auditoria a ser criado.
     * @return int|false Sempre retorna false ou lança uma exceção, pois a criação é via trigger.
     * @throws \BadMethodCallException Sempre lança uma exceção.
     */
    public function criar(object $auditoria)
    {
        throw new \BadMethodCallException("A criação de registros de auditoria é gerenciada por triggers do banco de dados e não deve ser chamada diretamente via DAO.");
    }

    /**
     * Atualiza um registro de auditoria existente no banco de dados.
     *
     * Esta função não deve ser chamada diretamente, pois os registros de auditoria
     * não são projetados para serem atualizados após a criação.
     *
     * @param Auditoria $auditoria O objeto Auditoria a ser atualizado.
     * @return bool Sempre retorna false ou lança uma exceção, pois a atualização não é suportada.
     * @throws \BadMethodCallException Sempre lança uma exceção.
     */
    public function atualizar(object $auditoria): bool
    {
        throw new \BadMethodCallException("A atualização de registros de auditoria não é suportada.");
    }
}
