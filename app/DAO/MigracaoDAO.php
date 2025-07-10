<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\Migracao;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade Migracao.
 */
class MigracaoDAO implements BaseDAOInterface
{
    private $tableName = 'migracoes';
    private $modelClass = Migracao::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return Migracao O objeto Migracao.
     */
    private function hydrate(array $data): Migracao
    {
        return new Migracao($data);
    }

    /**
     * Busca todas as migrações.
     *
     * @return array Um array de objetos Migracao.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todas as migrações: " . $mysqli->error, $mysqli->errno);
        }

        $migracoes = [];
        while ($row = $result->fetch_assoc()) {
            $migracoes[] = $this->hydrate($row);
        }
        $result->free();
        return $migracoes;
    }

    /**
     * Não aplicável para a tabela 'migracoes' (não possui campo 'ativo').
     * Retorna um array vazio ou lança uma exceção.
     *
     * @return array Um array de objetos Migracao.
     */
    public function buscarTodosAtivos(): array
    {
        // Esta tabela não possui o campo 'ativo', então esta função não é diretamente aplicável.
        // Você pode optar por retornar todos os registros ou lançar uma exceção.
        return $this->buscarTodos();
    }

    /**
     * Busca uma migração pelo seu ID.
     *
     * @param int $id O ID da migração a ser buscada.
     * @return Migracao|null O objeto Migracao ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?Migracao
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar migração por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca migrações por termos relacionados a números de procedimento ou ano.
     *
     * @param string $termo O termo de busca.
     * @return array Um array de objetos Migracao.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorNome(string $termo): array
    {
        // Adaptação para Migracao: buscar por numero_antigo, ano_antigo, numero_novo, ano_novo
        $sql = "SELECT * FROM {$this->tableName} WHERE CAST(numero_antigo AS CHAR) LIKE ? OR CAST(ano_antigo AS CHAR) LIKE ? OR CAST(numero_novo AS CHAR) LIKE ? OR CAST(ano_novo AS CHAR) LIKE ?";
        $stmt = Database::prepare($sql);
        $termoLike = "%{$termo}%";
        $stmt->bind_param("ssss", $termoLike, $termoLike, $termoLike, $termoLike);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar migrações por termo: " . $stmt->error, $stmt->errno);
        }

        $migracoes = [];
        while ($row = $result->fetch_assoc()) {
            $migracoes[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $migracoes;
    }

    /**
     * Cria uma nova migração no banco de dados.
     *
     * @param Migracao $migracao O objeto Migracao a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criar(object $migracao)
    {
        if (!$migracao instanceof Migracao) {
            throw new \InvalidArgumentException("O objeto fornecido não é uma instância de Migracao.");
        }

        $sql = "INSERT INTO {$this->tableName} (numero_antigo, ano_antigo, territorio_antigo, numero_novo, ano_novo, territorio_novo, id_motivo_migracao, id_usuario_criacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = Database::prepare($sql);

        $idUsuarioCriacao = $migracao->id_usuario_criacao ?? null;

        $stmt->bind_param(
            "iiiiiiii",
            $migracao->numero_antigo,
            $migracao->ano_antigo,
            $migracao->territorio_antigo,
            $migracao->numero_novo,
            $migracao->ano_novo,
            $migracao->territorio_novo,
            $migracao->id_motivo_migracao,
            $idUsuarioCriacao
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao criar migração: " . $stmt->error, $stmt->errno);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza uma migração existente no banco de dados.
     *
     * @param Migracao $migracao O objeto Migracao a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizar(object $migracao): bool
    {
        if (!$migracao instanceof Migracao || $migracao->id === null) {
            throw new \InvalidArgumentException("Objeto Migracao inválido para atualização: ID é nulo ou não é uma instância de Migracao.");
        }

        $sql = "UPDATE {$this->tableName} SET numero_antigo = ?, ano_antigo = ?, territorio_antigo = ?, numero_novo = ?, ano_novo = ?, territorio_novo = ?, id_motivo_migracao = ?, id_usuario_criacao = ? WHERE id = ?";
        $stmt = Database::prepare($sql);

        $idUsuarioCriacao = $migracao->id_usuario_criacao ?? null;

        $stmt->bind_param(
            "iiiiiiiii",
            $migracao->numero_antigo,
            $migracao->ano_antigo,
            $migracao->territorio_antigo,
            $migracao->numero_novo,
            $migracao->ano_novo,
            $migracao->territorio_novo,
            $migracao->id_motivo_migracao,
            $idUsuarioCriacao,
            $migracao->id
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao atualizar migração: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
