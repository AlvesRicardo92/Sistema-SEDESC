<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\Procedimento;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade Procedimento.
 */
class ProcedimentoDAO implements BaseDAOInterface
{
    private $tableName = 'procedimentos';
    private $modelClass = Procedimento::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return Procedimento O objeto Procedimento.
     */
    private function hydrate(array $data): Procedimento
    {
        return new Procedimento($data);
    }

    /**
     * Busca todos os procedimentos.
     *
     * @return array Um array de objetos Procedimento.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os procedimentos: " . $mysqli->error, $mysqli->errno);
        }

        $procedimentos = [];
        while ($row = $result->fetch_assoc()) {
            $procedimentos[] = $this->hydrate($row);
        }
        $result->free();
        return $procedimentos;
    }

    /**
     * Busca todos os procedimentos ativos.
     *
     * @return array Um array de objetos Procedimento.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodosAtivos(): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ativo = 1";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os procedimentos ativos: " . $mysqli->error, $mysqli->errno);
        }

        $procedimentos = [];
        while ($row = $result->fetch_assoc()) {
            $procedimentos[] = $this->hydrate($row);
        }
        $result->free();
        return $procedimentos;
    }

    /**
     * Busca um procedimento pelo seu ID.
     *
     * @param int $id O ID do procedimento a ser buscado.
     * @return Procedimento|null O objeto Procedimento ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?Procedimento
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar procedimento por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca procedimentos pelo número do procedimento ou ano do procedimento, utilizando a palavra-chave LIKE.
     *
     * @param string $termo O termo de busca para o número do procedimento ou ano.
     * @return array Um array de objetos Procedimento.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorNome(string $termo): array
    {
        // Adaptação para Procedimento: buscar por numero_procedimento e ano_procedimento
        $sql = "SELECT * FROM {$this->tableName} WHERE CAST(numero_procedimento AS CHAR) LIKE ? OR CAST(ano_procedimento AS CHAR) LIKE ?";
        $stmt = Database::prepare($sql);
        $termoLike = "%{$termo}%";
        $stmt->bind_param("ss", $termoLike, $termoLike);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar procedimentos por termo: " . $stmt->error, $stmt->errno);
        }

        $procedimentos = [];
        while ($row = $result->fetch_assoc()) {
            $procedimentos[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $procedimentos;
    }

    /**
     * Cria um novo procedimento no banco de dados.
     *
     * @param Procedimento $procedimento O objeto Procedimento a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criar(object $procedimento)
    {
        if (!$procedimento instanceof Procedimento) {
            throw new \InvalidArgumentException("O objeto fornecido não é uma instância de Procedimento.");
        }

        $sql = "INSERT INTO {$this->tableName} (numero_procedimento, ano_procedimento, id_territorio, id_bairro, id_pessoa, id_genitora_pessoa, id_demandante, ativo, migrado, id_migracao, data_criacao, hora_criacao, id_usuario_criacao, id_usuario_atualizacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = Database::prepare($sql);

        // Define valores padrão para campos que podem ser nulos
        $idBairro = $procedimento->id_bairro ?? null;
        $idPessoa = $procedimento->id_pessoa ?? null;
        $idGenitoraPessoa = $procedimento->id_genitora_pessoa ?? null;
        $idDemandante = $procedimento->id_demandante ?? null;
        $idMigracao = $procedimento->id_migracao ?? null;
        $horaCriacao = $procedimento->hora_criacao ?? null;
        $idUsuarioCriacao = $procedimento->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $procedimento->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "iiiisiiisisiii",
            $procedimento->numero_procedimento,
            $procedimento->ano_procedimento,
            $procedimento->id_territorio,
            $idBairro,
            $idPessoa,
            $idGenitoraPessoa,
            $idDemandante,
            $procedimento->ativo,
            $procedimento->migrado,
            $idMigracao,
            $procedimento->data_criacao,
            $horaCriacao,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao criar procedimento: " . $stmt->error, $stmt->errno);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza um procedimento existente no banco de dados.
     *
     * @param Procedimento $procedimento O objeto Procedimento a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizar(object $procedimento): bool
    {
        if (!$procedimento instanceof Procedimento || $procedimento->id === null) {
            throw new \InvalidArgumentException("Objeto Procedimento inválido para atualização: ID é nulo ou não é uma instância de Procedimento.");
        }

        $sql = "UPDATE {$this->tableName} SET numero_procedimento = ?, ano_procedimento = ?, id_territorio = ?, id_bairro = ?, id_pessoa = ?, id_genitora_pessoa = ?, id_demandante = ?, ativo = ?, migrado = ?, id_migracao = ?, data_criacao = ?, hora_criacao = ?, id_usuario_criacao = ?, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = Database::prepare($sql);

        // Define valores padrão para campos que podem ser nulos
        $idBairro = $procedimento->id_bairro ?? null;
        $idPessoa = $procedimento->id_pessoa ?? null;
        $idGenitoraPessoa = $procedimento->id_genitora_pessoa ?? null;
        $idDemandante = $procedimento->id_demandante ?? null;
        $idMigracao = $procedimento->id_migracao ?? null;
        $horaCriacao = $procedimento->hora_criacao ?? null;
        $idUsuarioCriacao = $procedimento->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $procedimento->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "iiiisiiisisiiii",
            $procedimento->numero_procedimento,
            $procedimento->ano_procedimento,
            $procedimento->id_territorio,
            $idBairro,
            $idPessoa,
            $idGenitoraPessoa,
            $idDemandante,
            $procedimento->ativo,
            $procedimento->migrado,
            $idMigracao,
            $procedimento->data_criacao,
            $horaCriacao,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao,
            $procedimento->id
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao atualizar procedimento: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
