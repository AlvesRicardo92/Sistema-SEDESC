<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\Territorio;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade Territorio.
 */
class TerritorioDAO implements BaseDAOInterface
{
    private $tableName = 'territorios_ct'; // Nome da tabela conforme a migração
    private $modelClass = Territorio::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return Territorio O objeto Territorio.
     */
    private function hydrate(array $data): Territorio
    {
        return new Territorio($data);
    }

    /**
     * Busca todos os territórios.
     *
     * @return array Um array de objetos Territorio.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os territórios: " . $mysqli->error, $mysqli->errno);
        }

        $territorios = [];
        while ($row = $result->fetch_assoc()) {
            $territorios[] = $this->hydrate($row);
        }
        $result->free();
        return $territorios;
    }

    /**
     * Busca todos os territórios ativos.
     *
     * @return array Um array de objetos Territorio.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodosAtivos(): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ativo = 1";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os territórios ativos: " . $mysqli->error, $mysqli->errno);
        }

        $territorios = [];
        while ($row = $result->fetch_assoc()) {
            $territorios[] = $this->hydrate($row);
        }
        $result->free();
        return $territorios;
    }

    /**
     * Busca um território pelo seu ID.
     *
     * @param int $id O ID do território a ser buscado.
     * @return Territorio|null O objeto Territorio ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?Territorio
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar território por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca territórios pelo nome, utilizando a palavra-chave LIKE.
     *
     * @param string $nome O nome ou parte do nome a ser buscado.
     * @return array Um array de objetos Territorio.
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
            throw new DatabaseException("Erro ao buscar territórios por nome: " . $stmt->error, $stmt->errno);
        }

        $territorios = [];
        while ($row = $result->fetch_assoc()) {
            $territorios[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $territorios;
    }

    /**
     * Cria um novo território no banco de dados.
     *
     * @param Territorio $territorio O objeto Territorio a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criar(object $territorio)
    {
        if (!$territorio instanceof Territorio) {
            throw new \InvalidArgumentException("O objeto fornecido não é uma instância de Territorio.");
        }

        $sql = "INSERT INTO {$this->tableName} (nome, ativo, id_usuario_criacao, id_usuario_atualizacao) VALUES (?, ?, ?, ?)";
        $stmt = Database::prepare($sql);

        $idUsuarioCriacao = $territorio->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $territorio->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "siii",
            $territorio->nome,
            $territorio->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao criar território: " . $stmt->error, $stmt->errno);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza um território existente no banco de dados.
     *
     * @param Territorio $territorio O objeto Territorio a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizar(object $territorio): bool
    {
        if (!$territorio instanceof Territorio || $territorio->id === null) {
            throw new \InvalidArgumentException("Objeto Territorio inválido para atualização: ID é nulo ou não é uma instância de Territorio.");
        }

        $sql = "UPDATE {$this->tableName} SET nome = ?, ativo = ?, id_usuario_criacao = ?, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = Database::prepare($sql);

        $idUsuarioCriacao = $territorio->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $territorio->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "siiii",
            $territorio->nome,
            $territorio->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao,
            $territorio->id
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao atualizar território: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
