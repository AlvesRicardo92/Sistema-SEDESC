<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\Sexo;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade Sexo.
 */
class SexoDAO implements BaseDAOInterface
{
    private $tableName = 'sexos';
    private $modelClass = Sexo::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return Sexo O objeto Sexo.
     */
    private function hydrate(array $data): Sexo
    {
        return new Sexo($data);
    }

    /**
     * Busca todos os sexos.
     *
     * @return array Um array de objetos Sexo.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os sexos: " . $mysqli->error, $mysqli->errno);
        }

        $sexos = [];
        while ($row = $result->fetch_assoc()) {
            $sexos[] = $this->hydrate($row);
        }
        $result->free();
        return $sexos;
    }

    /**
     * Busca todos os sexos ativos.
     *
     * @return array Um array de objetos Sexo.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodosAtivos(): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ativo = 1";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os sexos ativos: " . $mysqli->error, $mysqli->errno);
        }

        $sexos = [];
        while ($row = $result->fetch_assoc()) {
            $sexos[] = $this->hydrate($row);
        }
        $result->free();
        return $sexos;
    }

    /**
     * Busca um sexo pelo seu ID.
     *
     * @param int $id O ID do sexo a ser buscado.
     * @return Sexo|null O objeto Sexo ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?Sexo
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar sexo por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca sexos pelo nome, utilizando a palavra-chave LIKE.
     *
     * @param string $nome O nome ou parte do nome a ser buscado.
     * @return array Um array de objetos Sexo.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorNome(string $nome): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nome LIKE ?";
        $stmt = Database::prepare($sql);
        $nomeLike = "%{$nome}%";
        $stmt->bind_param("s", $nomeLike);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar sexos por nome: " . $stmt->error, $stmt->errno);
        }

        $sexos = [];
        while ($row = $result->fetch_assoc()) {
            $sexos[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $sexos;
    }

    /**
     * Cria um novo sexo no banco de dados.
     *
     * @param Sexo $sexo O objeto Sexo a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criar(object $sexo)
    {
        if (!$sexo instanceof Sexo) {
            throw new \InvalidArgumentException("O objeto fornecido não é uma instância de Sexo.");
        }

        $sql = "INSERT INTO {$this->tableName} (nome, sigla, ativo, id_usuario_criacao, id_usuario_atualizacao) VALUES (?, ?, ?, ?, ?)";
        $stmt = Database::prepare($sql);

        $idUsuarioCriacao = $sexo->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $sexo->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "ssiii",
            $sexo->nome,
            $sexo->sigla,
            $sexo->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao criar sexo: " . $stmt->error, $stmt->errno);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza um sexo existente no banco de dados.
     *
     * @param Sexo $sexo O objeto Sexo a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizar(object $sexo): bool
    {
        if (!$sexo instanceof Sexo || $sexo->id === null) {
            throw new \InvalidArgumentException("Objeto Sexo inválido para atualização: ID é nulo ou não é uma instância de Sexo.");
        }

        $sql = "UPDATE {$this->tableName} SET nome = ?, sigla = ?, ativo = ?, id_usuario_criacao = ?, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = Database::prepare($sql);

        $idUsuarioCriacao = $sexo->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $sexo->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "ssiiii",
            $sexo->nome,
            $sexo->sigla,
            $sexo->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao,
            $sexo->id
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao atualizar sexo: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
