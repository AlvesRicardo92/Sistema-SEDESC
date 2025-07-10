<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\Bairro;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade Bairro.
 */
class BairroDAO implements BaseDAOInterface
{
    private $tableName = 'bairros';
    private $modelClass = Bairro::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return Bairro O objeto Bairro.
     */
    private function hydrate(array $data): Bairro
    {
        return new Bairro($data);
    }

    /**
     * Busca todos os bairros.
     *
     * @return array Um array de objetos Bairro.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os bairros: " . $mysqli->error, $mysqli->errno);
        }

        $bairros = [];
        while ($row = $result->fetch_assoc()) {
            $bairros[] = $this->hydrate($row);
        }
        $result->free();
        return $bairros;
    }

    /**
     * Busca todos os bairros ativos.
     *
     * @return array Um array de objetos Bairro.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodosAtivos(): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ativo = 1";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os bairros ativos: " . $mysqli->error, $mysqli->errno);
        }

        $bairros = [];
        while ($row = $result->fetch_assoc()) {
            $bairros[] = $this->hydrate($row);
        }
        $result->free();
        return $bairros;
    }

    /**
     * Busca um bairro pelo seu ID.
     *
     * @param int $id O ID do bairro a ser buscado.
     * @return Bairro|null O objeto Bairro ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?Bairro
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar bairro por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca bairros pelo nome, utilizando a palavra-chave LIKE.
     *
     * @param string $nome O nome ou parte do nome a ser buscado.
     * @return array Um array de objetos Bairro.
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
            throw new DatabaseException("Erro ao buscar bairros por nome: " . $stmt->error, $stmt->errno);
        }

        $bairros = [];
        while ($row = $result->fetch_assoc()) {
            $bairros[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $bairros;
    }

    /**
     * Busca bairros ativos por ID do território.
     *
     * @param int $territorioId O ID do território.
     * @return array Um array de objetos Bairro.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarAtivosPorTerritorioId(int $territorioId): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ativo = 1 AND territorio_id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $territorioId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar bairros ativos por território: " . $stmt->error, $stmt->errno);
        }

        $bairros = [];
        while ($row = $result->fetch_assoc()) {
            $bairros[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $bairros;
    }

    /**
     * Cria um novo bairro no banco de dados.
     *
     * @param Bairro $bairro O objeto Bairro a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criar(object $bairro)
    {
        if (!$bairro instanceof Bairro) {
            throw new \InvalidArgumentException("O objeto fornecido não é uma instância de Bairro.");
        }

        $sql = "INSERT INTO {$this->tableName} (nome, territorio_id, ativo, id_usuario_criacao, id_usuario_atualizacao) VALUES (?, ?, ?, ?, ?)";
        $stmt = Database::prepare($sql);

        $territorioId = $bairro->territorio_id ?? null;
        $idUsuarioCriacao = $bairro->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $bairro->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "siiii",
            $bairro->nome,
            $territorioId,
            $bairro->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao criar bairro: " . $stmt->error, $stmt->errno);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza um bairro existente no banco de dados.
     *
     * @param Bairro $bairro O objeto Bairro a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizar(object $bairro): bool
    {
        if (!$bairro instanceof Bairro || $bairro->id === null) {
            throw new \InvalidArgumentException("Objeto Bairro inválido para atualização: ID é nulo ou não é uma instância de Bairro.");
        }

        $sql = "UPDATE {$this->tableName} SET nome = ?, territorio_id = ?, ativo = ?, id_usuario_criacao = ?, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = Database::prepare($sql);

        $territorioId = $bairro->territorio_id ?? null;
        $idUsuarioCriacao = $bairro->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $bairro->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "siiii",
            $bairro->nome,
            $territorioId,
            $bairro->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao,
            $bairro->id
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao atualizar bairro: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
