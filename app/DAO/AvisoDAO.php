<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\Aviso;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade Aviso.
 */
class AvisoDAO implements BaseDAOInterface
{
    private $tableName = 'avisos';
    private $modelClass = Aviso::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return Aviso O objeto Aviso.
     */
    private function hydrate(array $data): Aviso
    {
        return new Aviso($data);
    }

    /**
     * Busca todos os avisos.
     *
     * @return array Um array de objetos Aviso.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os avisos: " . $mysqli->error, $mysqli->errno);
        }

        $avisos = [];
        while ($row = $result->fetch_assoc()) {
            $avisos[] = $this->hydrate($row);
        }
        $result->free();
        return $avisos;
    }

    /**
     * Busca todos os avisos ativos (dentro do período de exibição).
     *
     * @return array Um array de objetos Aviso.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodosAtivos(): array
    {
        $currentDate = date('Y-m-d');
        $sql = "SELECT * FROM {$this->tableName} WHERE data_inicio_exibicao <= ? AND data_fim_exibicao >= ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("ss", $currentDate, $currentDate);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar avisos ativos: " . $stmt->error, $stmt->errno);
        }

        $avisos = [];
        while ($row = $result->fetch_assoc()) {
            $avisos[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $avisos;
    }

    /**
     * Busca um aviso pelo seu ID.
     *
     * @param int $id O ID do aviso a ser buscado.
     * @return Aviso|null O objeto Aviso ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?Aviso
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar aviso por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca avisos pela descrição, utilizando a palavra-chave LIKE.
     *
     * @param string $termo O termo de busca para a descrição.
     * @return array Um array de objetos Aviso.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorNome(string $termo): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE descricao LIKE ?";
        $stmt = Database::prepare($sql);
        $termoLike = "%{$termo}%";
        $stmt->bind_param("s", $termoLike);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar avisos por descrição: " . $stmt->error, $stmt->errno);
        }

        $avisos = [];
        while ($row = $result->fetch_assoc()) {
            $avisos[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $avisos;
    }

    /**
     * Cria um novo aviso no banco de dados.
     *
     * @param Aviso $aviso O objeto Aviso a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criar(object $aviso)
    {
        if (!$aviso instanceof Aviso) {
            throw new \InvalidArgumentException("O objeto fornecido não é uma instância de Aviso.");
        }

        $sql = "INSERT INTO {$this->tableName} (descricao, id_territorio_exibicao, data_inicio_exibicao, data_fim_exibicao, nome_imagem, id_usuario_criacao, id_usuario_atualizacao) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = Database::prepare($sql);

        $idTerritorioExibicao = $aviso->id_territorio_exibicao ?? null;
        $nomeImagem = $aviso->nome_imagem ?? null;
        $idUsuarioCriacao = $aviso->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $aviso->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "sisssii",
            $aviso->descricao,
            $idTerritorioExibicao,
            $aviso->data_inicio_exibicao,
            $aviso->data_fim_exibicao,
            $nomeImagem,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao criar aviso: " . $stmt->error, $stmt->errno);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza um aviso existente no banco de dados.
     *
     * @param Aviso $aviso O objeto Aviso a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizar(object $aviso): bool
    {
        if (!$aviso instanceof Aviso || $aviso->id === null) {
            throw new \InvalidArgumentException("Objeto Aviso inválido para atualização: ID é nulo ou não é uma instância de Aviso.");
        }

        $sql = "UPDATE {$this->tableName} SET descricao = ?, id_territorio_exibicao = ?, data_inicio_exibicao = ?, data_fim_exibicao = ?, nome_imagem = ?, id_usuario_criacao = ?, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = Database::prepare($sql);

        $idTerritorioExibicao = $aviso->id_territorio_exibicao ?? null;
        $nomeImagem = $aviso->nome_imagem ?? null;
        $idUsuarioCriacao = $aviso->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $aviso->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "sisssiii",
            $aviso->descricao,
            $idTerritorioExibicao,
            $aviso->data_inicio_exibicao,
            $aviso->data_fim_exibicao,
            $nomeImagem,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao,
            $aviso->id
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao atualizar aviso: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
