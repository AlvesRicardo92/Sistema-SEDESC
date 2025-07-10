<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\MotivoMigracao;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade MotivoMigracao.
 */
class MotivoMigracaoDAO implements BaseDAOInterface
{
    private $tableName = 'motivos_migracao';
    private $modelClass = MotivoMigracao::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return MotivoMigracao O objeto MotivoMigracao.
     */
    private function hydrate(array $data): MotivoMigracao
    {
        return new MotivoMigracao($data);
    }

    /**
     * Busca todos os motivos de migração.
     *
     * @return array Um array de objetos MotivoMigracao.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os motivos de migração: " . $mysqli->error, $mysqli->errno);
        }

        $motivos = [];
        while ($row = $result->fetch_assoc()) {
            $motivos[] = $this->hydrate($row);
        }
        $result->free();
        return $motivos;
    }

    /**
     * Busca todos os motivos de migração ativos.
     *
     * @return array Um array de objetos MotivoMigracao.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodosAtivos(): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ativo = 1";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os motivos de migração ativos: " . $mysqli->error, $mysqli->errno);
        }

        $motivos = [];
        while ($row = $result->fetch_assoc()) {
            $motivos[] = $this->hydrate($row);
        }
        $result->free();
        return $motivos;
    }

    /**
     * Busca um motivo de migração pelo seu ID.
     *
     * @param int $id O ID do motivo de migração a ser buscado.
     * @return MotivoMigracao|null O objeto MotivoMigracao ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?MotivoMigracao
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar motivo de migração por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca motivos de migração pelo nome, utilizando a palavra-chave LIKE.
     *
     * @param string $nome O nome ou parte do nome a ser buscado.
     * @return array Um array de objetos MotivoMigracao.
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
            throw new DatabaseException("Erro ao buscar motivos de migração por nome: " . $stmt->error, $stmt->errno);
        }

        $motivos = [];
        while ($row = $result->fetch_assoc()) {
            $motivos[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $motivos;
    }

    /**
     * Cria um novo motivo de migração no banco de dados.
     *
     * @param MotivoMigracao $motivo O objeto MotivoMigracao a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criar(object $motivo)
    {
        if (!$motivo instanceof MotivoMigracao) {
            throw new \InvalidArgumentException("O objeto fornecido não é uma instância de MotivoMigracao.");
        }

        $sql = "INSERT INTO {$this->tableName} (nome, ativo, id_usuario_criacao, id_usuario_atualizacao) VALUES (?, ?, ?, ?)";
        $stmt = Database::prepare($sql);

        $idUsuarioCriacao = $motivo->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $motivo->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "siii",
            $motivo->nome,
            $motivo->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao criar motivo de migração: " . $stmt->error, $stmt->errno);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza um motivo de migração existente no banco de dados.
     *
     * @param MotivoMigracao $motivo O objeto MotivoMigracao a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizar(object $motivo): bool
    {
        if (!$motivo instanceof MotivoMigracao || $motivo->id === null) {
            throw new \InvalidArgumentException("Objeto MotivoMigracao inválido para atualização: ID é nulo ou não é uma instância de MotivoMigracao.");
        }

        $sql = "UPDATE {$this->tableName} SET nome = ?, ativo = ?, id_usuario_criacao = ?, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = Database::prepare($sql);

        $idUsuarioCriacao = $motivo->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $motivo->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "siiii",
            $motivo->nome,
            $motivo->ativo,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao,
            $motivo->id
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao atualizar motivo de migração: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
