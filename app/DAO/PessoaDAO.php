<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\Pessoa;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade Pessoa.
 */
class PessoaDAO implements BaseDAOInterface
{
    private $tableName = 'pessoas';
    private $modelClass = Pessoa::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return Pessoa O objeto Pessoa.
     */
    private function hydrate(array $data): Pessoa
    {
        return new Pessoa($data);
    }

    /**
     * Busca todos as pessoas.
     *
     * @return array Um array de objetos Pessoa.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todas as pessoas: " . $mysqli->error, $mysqli->errno);
        }

        $pessoas = [];
        while ($row = $result->fetch_assoc()) {
            $pessoas[] = $this->hydrate($row);
        }
        $result->free();
        return $pessoas;
    }

    /**
     * Busca todas as pessoas ativas.
     *
     * @return array Um array de objetos Pessoa.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodosAtivos(): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ativo = 1";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todas as pessoas ativas: " . $mysqli->error, $mysqli->errno);
        }

        $pessoas = [];
        while ($row = $result->fetch_assoc()) {
            $pessoas[] = $this->hydrate($row);
        }
        $result->free();
        return $pessoas;
    }

    /**
     * Busca uma pessoa pelo seu ID.
     *
     * @param int $id O ID da pessoa a ser buscada.
     * @return Pessoa|null O objeto Pessoa ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?Pessoa
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar pessoa por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca pessoas pelo nome, utilizando a palavra-chave LIKE.
     *
     * @param string $nome O nome ou parte do nome a ser buscado.
     * @return array Um array de objetos Pessoa.
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
            throw new DatabaseException("Erro ao buscar pessoas por nome: " . $stmt->error, $stmt->errno);
        }

        $pessoas = [];
        while ($row = $result->fetch_assoc()) {
            $pessoas[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $pessoas;
    }

    /**
     * Busca pessoas pelo nome (LIKE) e data de nascimento.
     * Usado para verificar se uma pessoa já existe com nome e data de nascimento específicos.
     *
     * @param string $nome O nome da pessoa.
     * @param string $dataNascimento A data de nascimento da pessoa (formato YYYY-MM-DD).
     * @return Pessoa|null O objeto Pessoa encontrado ou null.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorNomeEDataNascimento(string $nome, string $dataNascimento): ?Pessoa
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nome = ? AND data_nascimento = ? LIMIT 1";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("ss", $nome, $dataNascimento);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar pessoa por nome e data de nascimento: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Cria uma nova pessoa no banco de dados.
     *
     * @param Pessoa $pessoa O objeto Pessoa a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criar(object $pessoa)
    {
        if (!$pessoa instanceof Pessoa) {
            throw new \InvalidArgumentException("O objeto fornecido não é uma instância de Pessoa.");
        }

        $sql = "INSERT INTO {$this->tableName} (nome, data_nascimento, id_sexo, ativo, id_usuario_criacao, id_usuario_atualizacao) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = Database::prepare($sql);

        $dataNascimento = $pessoa->data_nascimento ?? null;
        $idSexo = $pessoa->id_sexo ?? null;
        $idUsuarioCriacao = $pessoa->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $pessoa->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "ssiiii",
            $pessoa->nome,
            $dataNascimento,
            $idSexo,
            $pessoa->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao criar pessoa: " . $stmt->error, $stmt->errno);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza uma pessoa existente no banco de dados.
     *
     * @param Pessoa $pessoa O objeto Pessoa a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizar(object $pessoa): bool
    {
        if (!$pessoa instanceof Pessoa || $pessoa->id === null) {
            throw new \InvalidArgumentException("Objeto Pessoa inválido para atualização: ID é nulo ou não é uma instância de Pessoa.");
        }

        $sql = "UPDATE {$this->tableName} SET nome = ?, data_nascimento = ?, id_sexo = ?, ativo = ?, id_usuario_criacao = ?, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = Database::prepare($sql);

        $dataNascimento = $pessoa->data_nascimento ?? null;
        $idSexo = $pessoa->id_sexo ?? null;
        $idUsuarioCriacao = $pessoa->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $pessoa->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "ssiiiii",
            $pessoa->nome,
            $dataNascimento,
            $idSexo,
            $pessoa->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao,
            $pessoa->id
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao atualizar pessoa: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
