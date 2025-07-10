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
        $sql = "
            SELECT
                p.*,
                t.nome AS nome_territorio,
                b.nome AS nome_bairro,
                tb.nome AS nome_territorio_bairro,
                pes.nome AS nome_pessoa,
                gen.nome AS nome_genitora_pessoa,
                dem.nome AS nome_demandante,
                uc.nome AS nome_usuario_criacao,
                ua.nome AS nome_usuario_atualizacao
            FROM {$this->tableName} p
            LEFT JOIN territorios_ct t ON p.id_territorio = t.id
            LEFT JOIN bairros b ON p.id_bairro = b.id
            LEFT JOIN territorios_ct tb ON b.territorio_id = tb.id -- Território do Bairro
            LEFT JOIN pessoas pes ON p.id_pessoa = pes.id
            LEFT JOIN pessoas gen ON p.id_genitora_pessoa = gen.id
            LEFT JOIN demandantes dem ON p.id_demandante = dem.id
            LEFT JOIN usuarios uc ON p.id_usuario_criacao = uc.id
            LEFT JOIN usuarios ua ON p.id_usuario_atualizacao = ua.id
            WHERE p.id = ?
        ";
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

        // Se encontrou dados, adiciona os nomes relacionados ao objeto Procedimento
        if ($data) {
            $procedimento = $this->hydrate($data);
            // Adiciona propriedades dinâmicas para os nomes relacionados
            $procedimento->nome_territorio = $data['nome_territorio'] ?? null;
            $procedimento->nome_bairro = $data['nome_bairro'] ?? null;
            $procedimento->nome_territorio_bairro = $data['nome_territorio_bairro'] ?? null;
            $procedimento->nome_pessoa = $data['nome_pessoa'] ?? null;
            $procedimento->nome_genitora_pessoa = $data['nome_genitora_pessoa'] ?? null;
            $procedimento->nome_demandante = $data['nome_demandante'] ?? null;
            $procedimento->nome_usuario_criacao = $data['nome_usuario_criacao'] ?? null;
            $procedimento->nome_usuario_atualizacao = $data['nome_usuario_atualizacao'] ?? null;
            return $procedimento;
        }

        return null;
    }

    /**
     * Busca procedimentos pelo nome, utilizando a palavra-chave LIKE.
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
     * Busca procedimentos com base em filtros de pesquisa.
     *
     * @param array $filtros Array associativo com os filtros (ex: 'numero_procedimento', 'nome_pessoa', 'nome_genitora', 'data_nascimento').
     * @param int $territorioId O ID do território do usuário logado para filtrar.
     * @return array Um array de objetos Procedimento.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarComFiltros(array $filtros, int $territorioId): array
    {
        $sql = "
            SELECT
                p.id,
                p.numero_procedimento,
                p.ano_procedimento,
                p.id_territorio,
                p.id_bairro,
                p.id_pessoa,
                p.id_genitora_pessoa,
                p.id_demandante,
                p.ativo,
                p.migrado,
                p.id_migracao,
                p.data_criacao,
                p.hora_criacao,
                p.id_usuario_criacao,
                p.id_usuario_atualizacao,
                p.data_hora_atualizacao,
                pes.nome AS nome_pessoa,
                pes.data_nascimento AS data_nascimento_pessoa,
                gen.nome AS nome_genitora_pessoa
            FROM {$this->tableName} p
            LEFT JOIN pessoas pes ON p.id_pessoa = pes.id
            LEFT JOIN pessoas gen ON p.id_genitora_pessoa = gen.id
            WHERE p.ativo = 1 AND p.id_territorio = ?
        ";

        $params = [$territorioId];
        $types = "i";

        if (isset($filtros['numero_procedimento']) && $filtros['numero_procedimento'] !== '') {
            $sql .= " AND p.numero_procedimento = ?";
            $params[] = $filtros['numero_procedimento'];
            $types .= "i";
        }
        if (isset($filtros['nome_pessoa']) && $filtros['nome_pessoa'] !== '') {
            $sql .= " AND pes.nome LIKE ?";
            $params[] = "%{$filtros['nome_pessoa']}%";
            $types .= "s";
        }
        if (isset($filtros['nome_genitora']) && $filtros['nome_genitora'] !== '') {
            $sql .= " AND gen.nome LIKE ?";
            $params[] = "%{$filtros['nome_genitora']}%";
            $types .= "s";
        }
        if (isset($filtros['data_nascimento']) && $filtros['data_nascimento'] !== '') {
            // Assume que data_nascimento é para a pessoa principal
            $sql .= " AND pes.data_nascimento = ?";
            $params[] = $filtros['data_nascimento'];
            $types .= "s";
        }

        $sql .= " ORDER BY p.ano_procedimento DESC, p.numero_procedimento DESC";

        $stmt = Database::prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar procedimentos com filtros: " . $stmt->error, $stmt->errno);
        }

        $procedimentos = [];
        while ($row = $result->fetch_assoc()) {
            $procedimento = $this->hydrate($row);
            // Adiciona campos extras para a exibição na tabela
            $procedimento->nome_pessoa = $row['nome_pessoa'] ?? null;
            $procedimento->data_nascimento_pessoa = $row['data_nascimento_pessoa'] ?? null;
            $procedimento->nome_genitora_pessoa = $row['nome_genitora_pessoa'] ?? null;
            $procedimentos[] = $procedimento;
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

        // Ajuste o tipo de binding para 's' para campos que podem ser nulos (quando passados como null)
        // ou 'i' para inteiros. Para MySQLi, null para INT deve ser 'i' e o valor null.
        $stmt->bind_param(
            "iiiisiiisisiii", // i=int, s=string. Ajuste conforme seus campos
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

        // Não atualizamos numero_procedimento e ano_procedimento aqui, pois são desabilitados na UI
        $sql = "UPDATE {$this->tableName} SET id_territorio = ?, id_bairro = ?, id_pessoa = ?, id_genitora_pessoa = ?, id_demandante = ?, ativo = ?, migrado = ?, id_migracao = ?, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = Database::prepare($sql);

        // Define valores padrão para campos que podem ser nulos
        $idBairro = $procedimento->id_bairro ?? null;
        $idPessoa = $procedimento->id_pessoa ?? null;
        $idGenitoraPessoa = $procedimento->id_genitora_pessoa ?? null;
        $idDemandante = $procedimento->id_demandante ?? null;
        $idMigracao = $procedimento->id_migracao ?? null;
        $idUsuarioAtualizacao = $procedimento->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "iiiiiiiii", // Ajuste os tipos conforme os campos que você está atualizando
            $procedimento->id_territorio,
            $idBairro,
            $idPessoa,
            $idGenitoraPessoa,
            $idDemandante,
            $procedimento->ativo,
            $procedimento->migrado,
            $idMigracao,
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

    /**
     * Deleta um procedimento do banco de dados.
     *
     * @param int $id O ID do procedimento a ser deletado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function deletar(int $id): bool
    {
        $sql = "DELETE FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao deletar procedimento: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
