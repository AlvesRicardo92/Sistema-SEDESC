<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\Demandante;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade Demandante.
 */
class DemandanteDAO implements BaseDAOInterface
{
    private $tableName = 'demandantes';
    private $modelClass = Demandante::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return Demandante O objeto Demandante.
     */
    private function hydrate(array $data): Demandante
    {
        return new Demandante($data);
    }

    /**
     * Busca todos os demandantes.
     *
     * @return array Um array de objetos Demandante.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os demandantes: " . $mysqli->error, $mysqli->errno);
        }

        $demandantes = [];
        while ($row = $result->fetch_assoc()) {
            $demandantes[] = $this->hydrate($row);
        }
        $result->free();
        return $demandantes;
    }

    /**
     * Busca todos os demandantes ativos.
     *
     * @return array Um array de objetos Demandante.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodosAtivos(): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ativo = 1";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os demandantes ativos: " . $mysqli->error, $mysqli->errno);
        }

        $demandantes = [];
        while ($row = $result->fetch_assoc()) {
            $demandantes[] = $this->hydrate($row);
        }
        $result->free();
        return $demandantes;
    }

    /**
     * Busca um demandante pelo seu ID.
     *
     * @param int $id O ID do demandante a ser buscado.
     * @return Demandante|null O objeto Demandante ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?Demandante
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar demandante por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca demandantes pelo nome, utilizando a palavra-chave LIKE.
     *
     * @param string $nome O nome ou parte do nome a ser buscado.
     * @return array Um array de objetos Demandante.
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
            throw new DatabaseException("Erro ao buscar demandantes por nome: " . $stmt->error, $stmt->errno);
        }

        $demandantes = [];
        while ($row = $result->fetch_assoc()) {
            $demandantes[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $demandantes;
    }

    /**
     * Cria um novo demandante no banco de dados.
     *
     * @param Demandante $demandante O objeto Demandante a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criar(object $demandante)
    {
        if (!$demandante instanceof Demandante) {
            throw new \InvalidArgumentException("O objeto fornecido não é uma instância de Demandante.");
        }

        $sql = "INSERT INTO {$this->tableName} (nome, ativo, id_usuario_criacao, id_usuario_atualizacao) VALUES (?, ?, ?, ?)";
        $stmt = Database::prepare($sql);

        $idUsuarioCriacao = $demandante->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $demandante->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "siii",
            $demandante->nome,
            $demandante->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao criar demandante: " . $stmt->error, $stmt->errno);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza um demandante existente no banco de dados.
     *
     * @param Demandante $demandante O objeto Demandante a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizar(object $demandante): bool
    {
        if (!$demandante instanceof Demandante || $demandante->id === null) {
            throw new \InvalidArgumentException("Objeto Demandante inválido para atualização: ID é nulo ou não é uma instância de Demandante.");
        }

        $sql = "UPDATE {$this->tableName} SET nome = ?, ativo = ?, id_usuario_criacao = ?, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = Database::prepare($sql);

        $idUsuarioCriacao = $demandante->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $demandante->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "siiii",
            $demandante->nome,
            $demandante->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao,
            $demandante->id
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao atualizar demandante: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
