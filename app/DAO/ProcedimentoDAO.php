<?php
// ProcedimentoDAO.php
namespace App\dao;

use App\dao\Database;
use App\Models\Procedimento;
use App\Models\Pessoa;
use App\Models\Bairro;
use App\Models\Demandante;
use App\Models\Usuario;
use App\Models\Sexo;
use App\Models\Territorio;
use App\Models\Migracao; // Adicionado

require_once __DIR__ . '/autoload.php';

// Carregar as configurações do ambiente e definir no Database
$dbConfig = require_once __DIR__ . '/config/database.php';
Database::setConfig($dbConfig);

class ProcedimentoDAO
{
    private $db;
    private $pessoaDAO;
    private $bairroDAO;
    private $demandanteDAO;
    private $usuarioDAO;
    private $sexoDAO;
    private $territorioDAO;
    private $migracaoDAO;

    public function __construct()
    {
        try {
            $instancia_db = Database::getInstance();
            $this->db = $instancia_db->getConnection();
            $this->pessoaDAO = new PessoaDAO();
            $this->bairroDAO = new BairroDAO();
            $this->demandanteDAO = new DemandanteDAO();
            $this->usuarioDAO = new UsuarioDAO();
            $this->sexoDAO = new SexoDAO();
            $this->territorioDAO = new TerritorioDAO();
            $this->migracaoDAO = new MigracaoDAO();
        } catch (\Exception $e) {
            error_log("Erro ao obter conexão com o banco de dados em ProcedimentoDAO: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine() . "\nStack Trace:\n" . $e->getTraceAsString());
            throw new \Exception("Um erro ocorreu ao conectar ao sistema (ProcedimentoDAO). Tente novamente mais tarde.");
        }
    }

    /**
     * Define a variável de sessão MySQL @user_id.
     * Isso é crucial para que os triggers de auditoria possam registrar o usuário que realizou a operação.
     * @param int $userId O ID do usuário logado.
     * @return bool True se a variável foi definida com sucesso, false caso contrário.
     */
    private function setMysqlUserId(int $userId): bool
    {
        $stmt = $this->db->prepare("SET @user_id = ?");
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando SET @user_id: " . $this->db->error);
            return false;
        }
        $stmt->bind_param("i", $userId);
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando SET @user_id: " . $stmt->error);
            $stmt->close();
            return false;
        }
        $stmt->close();
        return true;
    }

    /**
     * Insere um novo Procedimento no banco de dados.
     * @param Procedimento $procedimento O objeto Procedimento a ser inserido.
     * @return int|null O ID do Procedimento inserido se for bem-sucedido, ou null caso contrário.
     */
    public function create(Procedimento $procedimento)
    {
        // Define o user_id para o MySQL antes da operação
        if (!$this->setMysqlUserId($procedimento->getIdUsuarioCriacao())) {
            return null; // Falha ao definir o user_id
        }

        // Adicionado 'id_territorio' ao INSERT
        $sql = "INSERT INTO procedimentos (
                    numero_procedimento, ano_procedimento, id_bairro, id_territorio,
                    id_pessoa, id_genitora_pessoa, id_demandante,
                    ativo, data_criacao, hora_criacao,
                    id_usuario_criacao, id_usuario_atualizacao, data_hora_atualizacao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando statement para create Procedimento: " . $this->db->error);
            return null;
        }

        // Obter IDs dos objetos relacionados
        $bairroId = $procedimento->getBairro() ? $procedimento->getBairro()->getId() : null;
        $idTerritorio = $procedimento->getIdTerritorio(); // Obter o ID do território
        $pessoaPrincipalId = $procedimento->getPessoaPrincipal() ? $procedimento->getPessoaPrincipal()->getId() : null;
        $genitoraId = $procedimento->getGenitora() ? $procedimento->getGenitora()->getId() : null;
        $demandanteId = $procedimento->getDemandante() ? $procedimento->getDemandante()->getId() : null;

        $ativo = $procedimento->isAtivo() ? 1 : 0;
        $idUsuarioCriacao = $procedimento->getIdUsuarioCriacao();
        $idUsuarioAtualizacao = $procedimento->getIdUsuarioAtualizacao();

        // Bind parameters - Adicionado 'i' para id_territorio
        $bindTypes = "iiiiiiiissii"; // numero(s), ano(i), id_bairro(i), id_territorio(i), pessoa_principal(i), genitora(i), demandante(i), ativo(i), data_criacao(s), hora_criacao(s), id_usuario_criacao(i), id_usuario_atualizacao(i)
        
        $bindParams = [
            $procedimento->getNumeroProcedimento(),
            $procedimento->getAnoProcedimento(),
            $bairroId,
            $idTerritorio, // Adicionado ao bindParams
            $pessoaPrincipalId,
            $genitoraId,
            $demandanteId,
            $ativo,
            $procedimento->getDataCriacaoProcedimento(),
            $procedimento->getHoraCriacaoProcedimento(),
            $idUsuarioCriacao,
            $idUsuarioAtualizacao
        ];

        $refs = [];
        foreach ($bindParams as $key => $value) {
            $refs[$key] = &$bindParams[$key];
        }

        array_unshift($refs, $bindTypes);

        if (!call_user_func_array([$stmt, 'bind_param'], $refs)) {
            error_log("DEBUG DAO: Erro no bind_param para create Procedimento: " . $stmt->error);
            $stmt->close();
            return null;
        }

        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando create Procedimento: " . $stmt->error);
            error_log("DEBUG DAO: Código de erro MySQL: " . $stmt->errno);
            error_log("DEBUG DAO: Mensagem de erro MySQL: " . $stmt->error);
            $stmt->close();
            return null;
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza um Procedimento existente no banco de dados.
     * @param Procedimento $procedimento O objeto Procedimento a ser atualizado.
     * @return bool True se a atualização for bem-sucedida, false caso contrário.
     */
    public function update(Procedimento $procedimento): bool
    {
        // Define o user_id para o MySQL antes da operação
        if (!$this->setMysqlUserId($procedimento->getIdUsuarioAtualizacao())) {
            return false; // Falha ao definir o user_id
        }

        // Adicionado 'id_territorio' ao UPDATE
        $sql = "UPDATE procedimentos SET
                    id_bairro = ?,
                    id_pessoa = ?, id_genitora_pessoa = ?, id_demandante = ?,
                    ativo = ?, data_criacao = ?, hora_criacao = ?,
                    id_usuario_atualizacao = ?, data_hora_atualizacao = NOW()
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando statement para update Procedimento: " . $this->db->error);
            return false;
        }

        $bairroId = $procedimento->getBairro() ? $procedimento->getBairro()->getId() : null;
        $idTerritorio = $procedimento->getIdTerritorio(); // Obter o ID do território
        $pessoaPrincipalId = $procedimento->getPessoaPrincipal() ? $procedimento->getPessoaPrincipal()->getId() : null;
        $genitoraId = $procedimento->getGenitora() ? $procedimento->getGenitora()->getId() : null;
        $demandanteId = $procedimento->getDemandante() ? $procedimento->getDemandante()->getId() : null;

        $ativo = $procedimento->isAtivo() ? 1 : 0;
        $idUsuarioAtualizacao = $procedimento->getIdUsuarioAtualizacao();

        // Os tipos de bind foram ajustados para refletir a adição de id_territorio
        $bindTypes = "iiiiissii"; // id_bairro(i), pessoa_principal(i), genitora(i), demandante(i), ativo(i), data_criacao(s), hora_criacao(s), id_usuario_atualizacao(i), id(i)
        $bindParams = [
            $bairroId,
            $pessoaPrincipalId,
            $genitoraId,
            $demandanteId,
            $ativo,
            $procedimento->getDataCriacaoProcedimento(),
            $procedimento->getHoraCriacaoProcedimento(),
            $idUsuarioAtualizacao,
            $procedimento->getId() // ID para a cláusula WHERE
        ];

        $refs = [];
        foreach ($bindParams as $key => $value) {
            $refs[$key] = &$bindParams[$key];
        }
        array_unshift($refs, $bindTypes);

        if (!call_user_func_array([$stmt, 'bind_param'], $refs)) {
            error_log("DEBUG DAO: Erro no bind_param para update Procedimento: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando update Procedimento: " . $stmt->error);
            error_log("DEBUG DAO: Código de erro MySQL (update): " . $stmt->errno);
            error_log("DEBUG DAO: Mensagem de erro MySQL (update): " . $stmt->error);
        }
        $stmt->close();
        return $executed;
    }

    /**
     * Desativa um Procedimento (define ativo como 0).
     * @param int $id O ID do Procedimento a ser desativado.
     * @param int $idUsuarioAtualizacao O ID do usuário que está realizando a atualização.
     * @return bool True se a desativação for bem-sucedida, false caso contrário.
     */
    public function deactivate(int $id, int $idUsuarioAtualizacao): bool
    {
        // Define o user_id para o MySQL antes da operação
        if (!$this->setMysqlUserId($idUsuarioAtualizacao)) {
            return false; // Falha ao definir o user_id
        }

        $sql = "UPDATE procedimentos SET ativo = 0, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando desativar Procedimento: " . $this->db->error);
            return false;
        }
        $stmt->bind_param("ii", $idUsuarioAtualizacao, $id);
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando desativar Procedimento: " . $stmt->error);
            error_log("DEBUG DAO: Código de erro MySQL (deactivate): " . $stmt->errno);
            error_log("DEBUG DAO: Mensagem de erro MySQL (deactivate): " . $stmt->error);
        }
        $stmt->close();
        return $executed;
    }

    /**
     * Ativa um Procedimento (define ativo como 1).
     * @param int $id O ID do Procedimento a ser ativado.
     * @param int $idUsuarioAtualizacao O ID do usuário que está realizando a atualização.
     * @return bool True se a ativação for bem-sucedida, false caso contrário.
     */
    public function activate(int $id, int $idUsuarioAtualizacao): bool
    {
        // Define o user_id para o MySQL antes da operação
        if (!$this->setMysqlUserId($idUsuarioAtualizacao)) {
            return false; // Falha ao definir o user_id
        }

        $sql = "UPDATE procedimentos SET ativo = 1, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando ativar Procedimento: " . $this->db->error);
            return false;
        }
        $stmt->bind_param("ii", $idUsuarioAtualizacao, $id);
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando ativar Procedimento: " . $stmt->error);
            error_log("DEBUG DAO: Código de erro MySQL (activate): " . $stmt->errno);
            error_log("DEBUG DAO: Mensagem de erro MySQL (activate): " . $stmt->error);
        }
        $stmt->close();
        return $executed;
    }

    /**
     * Busca um Procedimento pelo ID.
     * @param int $id O ID do Procedimento.
     * @param int|null $idTerritorioUsuario O ID do território do usuário logado para filtragem.
     * @return Procedimento|null O objeto Procedimento se encontrado, ou null caso contrário.
     */
    public function findById(int $id, int $idTerritorioUsuario = null)
    {
        error_log("DEBUG DAO: findById recebido idTerritorioUsuario: " . var_export($idTerritorioUsuario, true));
        $sql = $this->buildSelectJoinClause() . " WHERE pr.id = ?";
        $bindTypes = "i";
        $bindParams = [$id];

        // Apenas filtra se o idTerritorioUsuario for 1, 2 ou 3
        if ($idTerritorioUsuario !== null && in_array($idTerritorioUsuario, [1, 2, 3])) {
            $sql .= " AND pr.id_territorio = ?";
            $bindTypes .= "i";
            $bindParams[] = $idTerritorioUsuario;
        }
        error_log("DEBUG DAO: findById SQL final: " . $sql . " com bindParams: " . var_export($bindParams, true));

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando findById Procedimento: " . $this->db->error);
            return null;
        }
        $stmt->bind_param($bindTypes, ...$bindParams);
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando findById Procedimento: " . $stmt->error);
            $stmt->close();
            return null;
        }
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        if ($data) {
            return $this->hydrateProcedimento($data);
        }
        return null;
    }

    /**
     * Busca Procedimentos pelo número.
     * @param string $numero O número do Procedimento.
     * @param int|null $idTerritorioUsuario O ID do território do usuário logado para filtragem.
     * @return array<Procedimento> Um array de objetos Procedimento.
     */
    public function findByNumero(string $numero, int $idTerritorioUsuario = null): array
    {
        error_log("DEBUG DAO: findByNumero recebido idTerritorioUsuario: " . var_export($idTerritorioUsuario, true));
        $sql = $this->buildSelectJoinClause() . " WHERE pr.numero_procedimento = ? AND pr.ativo = 1";
        $bindTypes = "i";
        $bindParams = [$numero];

        // Apenas filtra se o idTerritorioUsuario for 1, 2 ou 3
        if ($idTerritorioUsuario !== null && in_array($idTerritorioUsuario, [1, 2, 3])) {
            $sql .= " AND pr.id_territorio = ?";
            $bindTypes .= "i";
            $bindParams[] = $idTerritorioUsuario;
        }
        $sql .= " ORDER BY pr.ano_procedimento DESC";
        error_log("DEBUG DAO: findByNumero SQL final: " . $sql . " com bindParams: " . var_export($bindParams, true));

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando findByNumero Procedimento: " . $this->db->error);
            return [];
        }
        error_log("DEBUG DAO: Executando findByNumero. SQL: " . $sql . ", Parâmetro: " . $numero);
        $stmt->bind_param($bindTypes, ...$bindParams);
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando findByNumero Procedimento: " . $stmt->error);
            error_log("DEBUG DAO: Código de erro MySQL (findByNumero): " . $stmt->errno);
            error_log("DEBUG DAO: Mensagem de erro MySQL (findByNumero): " . $stmt->error);
            $stmt->close();
            return [];
        }
        $result = $stmt->get_result();
        $procedimentos = [];
        while ($data = $result->fetch_assoc()) {
            $procedimentos[] = $this->hydrateProcedimento($data);
        }
        $stmt->close();
        $result->free();
        error_log("DEBUG DAO: findByNumero encontrou " . count($procedimentos) . " resultados.");
        return $procedimentos;
    }

    /**
     * Busca Procedimentos pelo nome da pessoa principal (LIKE).
     * @param string $nomePessoa O nome da pessoa principal.
     * @param int|null $idTerritorioUsuario O ID do território do usuário logado para filtragem.
     * @return array<Procedimento> Um array de objetos Procedimento.
     */
    public function findByPessoaNome(string $nomePessoa, int $idTerritorioUsuario = null): array
    {
        error_log("DEBUG DAO: findByPessoaNome recebido idTerritorioUsuario: " . var_export($idTerritorioUsuario, true));
        $sql = $this->buildSelectJoinClause() . " WHERE pp.nome LIKE ? AND pr.ativo = 1";
        $bindTypes = "s";
        $searchTerm = '%' . $nomePessoa . '%';
        $bindParams = [$searchTerm];

        // Apenas filtra se o idTerritorioUsuario for 1, 2 ou 3
        if ($idTerritorioUsuario !== null && in_array($idTerritorioUsuario, [1, 2, 3])) {
            $sql .= " AND pr.id_territorio = ?";
            $bindTypes .= "i";
            $bindParams[] = $idTerritorioUsuario;
        }
        $sql .= " ORDER BY pr.ano_procedimento DESC, pr.numero_procedimento DESC";
        error_log("DEBUG DAO: findByPessoaNome SQL final: " . $sql . " com bindParams: " . var_export($bindParams, true));

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando findByPessoaNome Procedimento: " . $this->db->error);
            return [];
        }
        error_log("DEBUG DAO: Executando findByPessoaNome. SQL: " . $sql . ", Parâmetro de busca: " . $searchTerm);
        $stmt->bind_param($bindTypes, ...$bindParams);
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando findByPessoaNome Procedimento: " . $stmt->error);
            error_log("DEBUG DAO: Código de erro MySQL (findByPessoaNome): " . $stmt->errno);
            error_log("DEBUG DAO: Mensagem de erro MySQL (findByPessoaNome): " . $stmt->error);
            $stmt->close();
            return [];
        }
        $result = $stmt->get_result();
        $procedimentos = [];
        while ($data = $result->fetch_assoc()) {
            $procedimentos[] = $this->hydrateProcedimento($data);
        }
        $stmt->close();
        $result->free();
        error_log("DEBUG DAO: findByPessoaNome encontrou " . count($procedimentos) . " resultados.");
        return $procedimentos;
    }

    /**
     * Busca Procedimentos pela data de nascimento da pessoa principal.
     * @param string $dataNascimento A data de nascimento da pessoa principal no formato BCE-MM-DD.
     * @param int|null $idTerritorioUsuario O ID do território do usuário logado para filtragem.
     * @return array<Procedimento> Um array de objetos Procedimento.
     */
    public function findByPessoaDataNascimento(string $dataNascimento, int $idTerritorioUsuario = null): array
    {
        error_log("DEBUG DAO: findByPessoaDataNascimento recebido idTerritorioUsuario: " . var_export($idTerritorioUsuario, true));
        $sql = $this->buildSelectJoinClause() . " WHERE pp.data_nascimento = ? AND pr.ativo = 1";
        $bindTypes = "s";
        $bindParams = [$dataNascimento];

        // Apenas filtra se o idTerritorioUsuario for 1, 2 ou 3
        if ($idTerritorioUsuario !== null && in_array($idTerritorioUsuario, [1, 2, 3])) {
            $sql .= " AND pr.id_territorio = ?";
            $bindTypes .= "i";
            $bindParams[] = $idTerritorioUsuario;
        }
        $sql .= " ORDER BY pr.ano_procedimento DESC, pr.numero_procedimento DESC";
        error_log("DEBUG DAO: findByPessoaDataNascimento SQL final: " . $sql . " com bindParams: " . var_export($bindParams, true));

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando findByPessoaDataNascimento Procedimento: " . $this->db->error);
            return [];
        }
        error_log("DEBUG DAO: Executando findByPessoaDataNascimento. SQL: " . $sql . ", Parâmetro: " . $dataNascimento);
        $stmt->bind_param($bindTypes, ...$bindParams);
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando findByPessoaDataNascimento Procedimento: " . $stmt->error);
            error_log("DEBUG DAO: Código de erro MySQL (findByPessoaDataNascimento): " . $stmt->errno);
            error_log("DEBUG DAO: Mensagem de erro MySQL (findByPessoaDataNascimento): " . $stmt->error);
            $stmt->close();
            return [];
        }
        $result = $stmt->get_result();
        $procedimentos = [];
        while ($data = $result->fetch_assoc()) {
            $procedimentos[] = $this->hydrateProcedimento($data);
        }
        $stmt->close();
        $result->free();
        error_log("DEBUG DAO: findByPessoaDataNascimento encontrou " . count($procedimentos) . " resultados.");
        return $procedimentos;
    }

    /**
     * Busca Procedimentos pelo nome da genitora (LIKE).
     * @param string $nomeGenitora O nome da genitora.
     * @param int|null $idTerritorioUsuario O ID do território do usuário logado para filtragem.
     * @return array<Procedimento> Um array de objetos Procedimento.
     */
    public function findByGenitoraNome(string $nomeGenitora, int $idTerritorioUsuario = null): array
    {
        error_log("DEBUG DAO: findByGenitoraNome recebido idTerritorioUsuario: " . var_export($idTerritorioUsuario, true));
        $sql = $this->buildSelectJoinClause() . " WHERE gen.nome LIKE ? AND pr.ativo = 1";
        $bindTypes = "s";
        $searchTerm = '%' . $nomeGenitora . '%';
        $bindParams = [$searchTerm];

        // Apenas filtra se o idTerritorioUsuario for 1, 2 ou 3
        if ($idTerritorioUsuario !== null && in_array($idTerritorioUsuario, [1, 2, 3])) {
            $sql .= " AND pr.id_territorio = ?";
            $bindTypes .= "i";
            $bindParams[] = $idTerritorioUsuario;
        }
        $sql .= " ORDER BY pr.ano_procedimento DESC, pr.numero_procedimento DESC";
        error_log("DEBUG DAO: findByGenitoraNome SQL final: " . $sql . " com bindParams: " . var_export($bindParams, true));

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando findByGenitoraNome Procedimento: " . $this->db->error);
            return [];
        }
        error_log("DEBUG DAO: Executando findByGenitoraNome. SQL: " . $sql . ", Parâmetro: " . $searchTerm);
        $stmt->bind_param($bindTypes, ...$bindParams);
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando findByGenitoraNome Procedimento: " . $stmt->error);
            error_log("DEBUG DAO: Código de erro MySQL (findByGenitoraNome): " . $stmt->errno);
            error_log("DEBUG DAO: Mensagem de erro MySQL (findByGenitoraNome): " . $stmt->error);
            $stmt->close();
            return [];
        }
        $result = $stmt->get_result();
        $procedimentos = [];
        while ($data = $result->fetch_assoc()) {
            $procedimentos[] = $this->hydrateProcedimento($data);
        }
        $stmt->close();
        $result->free();
        error_log("DEBUG DAO: findByGenitoraNome encontrou " . count($procedimentos) . " resultados.");
        return $procedimentos;
    }

    /**
     * Retorna todos os procedimentos ativos.
     * @param int|null $idTerritorioUsuario O ID do território do usuário logado para filtragem.
     * @return array<Procedimento> Um array de objetos Procedimento.
     */
    public function getAllActive(int $idTerritorioUsuario = null): array
    {
        error_log("DEBUG DAO: getAllActive recebido idTerritorioUsuario: " . var_export($idTerritorioUsuario, true));
        $sql = $this->buildSelectJoinClause() . " WHERE pr.ativo = 1";
        $bindTypes = "";
        $bindParams = [];

        // Apenas filtra se o idTerritorioUsuario for 1, 2 ou 3
        if ($idTerritorioUsuario !== null && in_array($idTerritorioUsuario, [1, 2, 3])) {
            $sql .= " AND pr.id_territorio = ?";
            $bindTypes .= "i";
            $bindParams[] = $idTerritorioUsuario;
        }
        $sql .= " ORDER BY pr.ano_procedimento DESC, pr.numero_procedimento DESC";
        error_log("DEBUG DAO: getAllActive SQL final: " . $sql . " com bindParams: " . var_export($bindParams, true));

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando getAllActive Procedimento: " . $this->db->error);
            return [];
        }
        error_log("DEBUG DAO: Executando getAllActive. SQL: " . $sql);
        if (!empty($bindTypes)) {
            $stmt->bind_param($bindTypes, ...$bindParams);
        }
        
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando getAllActive Procedimento: " . $stmt->error);
            error_log("DEBUG DAO: Código de erro MySQL (getAllActive): " . $stmt->errno);
            error_log("DEBUG DAO: Mensagem de erro MySQL (getAllActive): " . $stmt->error);
            $stmt->close();
            return [];
        }
        $result = $stmt->get_result();
        $procedimentos = [];
        while ($data = $result->fetch_assoc()) {
            $procedimentos[] = $this->hydrateProcedimento($data);
        }
        $stmt->close();
        $result->free();
        error_log("DEBUG DAO: getAllActive encontrou " . count($procedimentos) . " resultados.");
        return $procedimentos;
    }

    /**
     * Busca o último número de procedimento para um dado ano e território.
     * @param int $ano O ano para buscar o último procedimento.
     * @param int $idTerritorio O ID do território para buscar o último procedimento.
     * @return array|null Um array associativo com 'numero_procedimento', 'ano_procedimento' e 'id_territorio', ou null se nenhum for encontrado.
     */
    public function findLastProcedimentoNumberByYearAndTerritory(int $ano, int $idTerritorio)
    {
        $sql = "SELECT numero_procedimento, ano_procedimento, id_territorio FROM procedimentos WHERE ano_procedimento = ? AND id_territorio = ? ORDER BY numero_procedimento DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            error_log("DEBUG DAO: Erro preparando findLastProcedimentoNumberByYearAndTerritory: " . $this->db->error);
            return null;
        }
        error_log("DEBUG DAO: Executando findLastProcedimentoNumberByYearAndTerritory. SQL: " . $sql . ", Parâmetros: Ano=" . $ano . ", Território=" . $idTerritorio);
        $stmt->bind_param("ii", $ano, $idTerritorio);
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("DEBUG DAO: Erro executando findLastProcedimentoNumberByYearAndTerritory: " . $stmt->error);
            error_log("DEBUG DAO: Código de erro MySQL (findLastProcedimentoNumberByYearAndTerritory): " . $stmt->errno);
            error_log("DEBUG DAO: Mensagem de erro MySQL (findLastProcedimentoNumberByYearAndTerritory): " . $stmt->error);
            $stmt->close();
            return null;
        }
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        error_log("DEBUG DAO: findLastProcedimentoNumberByYearAndTerritory encontrou: " . var_export($data, true));
        return $data;
    }

    /**
     * Constrói a cláusula SELECT com JOINs para buscar procedimentos e suas entidades relacionadas.
     * @return string A cláusula SQL completa.
     */
    private function buildSelectJoinClause(): string
    {
        // Adicionado pr.id_territorio à seleção
        return "SELECT
                    pr.id, pr.numero_procedimento, pr.ano_procedimento, pr.ativo,
                    pr.data_criacao, pr.hora_criacao, pr.id_territorio,
                    pr.id_usuario_criacao,
                    pr.id_usuario_atualizacao, pr.data_hora_atualizacao,
                    b.id AS bairro_id, b.nome AS bairro_nome, b.ativo AS bairro_ativo,
                    t.id AS territorio_id, t.nome AS territorio_nome, t.ativo AS territorio_ativo,
                    pp.id AS pessoa_principal_id, pp.nome AS pessoa_principal_nome, pp.data_nascimento AS pessoa_principal_data_nascimento, pp.ativo AS pessoa_principal_ativo,
                    spp.id AS sexo_pessoa_principal_id, spp.nome AS sexo_pessoa_principal_nome, spp.sigla AS sexo_pessoa_principal_sigla,
                    gen.id AS genitora_id, gen.nome AS genitora_nome, gen.data_nascimento AS genitora_data_nascimento, gen.ativo AS genitora_ativo,
                    sgen.id AS sexo_genitora_id, sgen.nome AS sexo_genitora_nome, sgen.sigla AS sexo_genitora_sigla,
                    dem.id AS demandante_id, dem.nome AS demandante_nome, dem.ativo AS demandante_ativo,
                    uc.id AS usuario_criacao_id, uc.nome AS usuario_criacao_nome,
                    ua.id AS usuario_atualizacao_id, ua.nome AS usuario_atualizacao_nome,
                    mig.numero_novo AS migracao_numero_novo, mig.ano_novo AS migracao_ano_novo, mig.territorio_novo AS migracao_territorio_novo,
                    t_novo.nome AS migracao_territorio_nome_novo
                FROM
                    procedimentos pr
                LEFT JOIN
                    bairros b ON pr.id_bairro = b.id
                LEFT JOIN
                    territorios t ON pr.id_territorio = t.id -- Alterado para usar pr.id_territorio diretamente
                LEFT JOIN
                    pessoas pp ON pr.id_pessoa = pp.id
                LEFT JOIN
                    sexos spp ON pp.id_sexo = spp.id
                LEFT JOIN
                    pessoas gen ON pr.id_genitora_pessoa = gen.id
                LEFT JOIN
                    sexos sgen ON gen.id_sexo = sgen.id
                LEFT JOIN
                    demandantes dem ON pr.id_demandante = dem.id
                LEFT JOIN
                    usuarios uc ON pr.id_usuario_criacao = uc.id
                LEFT JOIN
                    usuarios ua ON pr.id_usuario_atualizacao = ua.id
                LEFT JOIN
                    migracoes mig ON pr.id_migracao = mig.id
                LEFT JOIN
                    territorios t_novo ON mig.territorio_novo = t_novo.id";
    }

    /**
     * Hidrata um objeto Procedimento a partir de um array de dados do banco de dados.
     * @param array $data O array de dados do banco de dados.
     * @return Procedimento O objeto Procedimento hidratado.
     */
    private function hydrateProcedimento(array $data): Procedimento
    {
        // Hidratar Bairro e Território
        $territorio = null;
        if (isset($data['territorio_id']) && $data['territorio_id'] !== null) {
            $territorio = new Territorio(
                $data['territorio_id'],
                $data['territorio_nome'],
                (bool)$data['territorio_ativo']
            );
        }

        $bairro = null;
        if (isset($data['bairro_id']) && $data['bairro_id'] !== null) { 
            $bairro = new Bairro(
                $data['bairro_id'],
                $data['bairro_nome'],
                (bool)$data['bairro_ativo'],
                null, null, null, null, // Campos de criação/atualização do Bairro não são necessários aqui
                $territorio
            );
        }

        // Hidratar Sexo para Pessoa Principal
        $sexoPessoaPrincipal = null;
        if (isset($data['sexo_pessoa_principal_id']) && $data['sexo_pessoa_principal_id'] !== null) {
            $sexoPessoaPrincipal = new Sexo(
                $data['sexo_pessoa_principal_id'],
                $data['sexo_pessoa_principal_nome'],
                $data['sexo_pessoa_principal_sigla']
            );
        }

        // Hidratar Pessoa Principal
        $pessoaPrincipal = null;
        if (isset($data['pessoa_principal_id']) && $data['pessoa_principal_id'] !== null) {
            $pessoaPrincipal = new Pessoa(
                $data['pessoa_principal_id'],
                $data['pessoa_principal_nome'],
                $data['pessoa_principal_data_nascimento'],
                $sexoPessoaPrincipal,
                (bool)$data['pessoa_principal_ativo']
            );
        }

        // Hidratar Sexo para Genitora
        $sexoGenitora = null;
        if (isset($data['sexo_genitora_id']) && $data['sexo_genitora_id'] !== null) {
            $sexoGenitora = new Sexo(
                $data['sexo_genitora_id'],
                $data['sexo_genitora_nome'],
                $data['sexo_genitora_sigla']
            );
        }

        // Hidratar Genitora
        $genitora = null;
        if (isset($data['genitora_id']) && $data['genitora_id'] !== null) {
            $genitora = new Pessoa(
                $data['genitora_id'],
                $data['genitora_nome'],
                $data['genitora_data_nascimento'],
                $sexoGenitora,
                (bool)$data['genitora_ativo']
            );
        }

        // Hidratar Demandante
        $demandante = null;
        if (isset($data['demandante_id']) && $data['demandante_id'] !== null) {
            $demandante = new Demandante(
                $data['demandante_id'],
                $data['demandante_nome'],
                (bool)$data['demandante_ativo']
            );
        }

        // Hidratar Usuário de Criação
        $usuarioCriacao = null;
        if (isset($data['usuario_criacao_id']) && $data['usuario_criacao_id'] !== null) {
            $usuarioCriacao = new Usuario(
                $data['usuario_criacao_id'],
                null, // usuario
                null, // senha
                null, // permissoes
                null, // ativo
                $data['usuario_criacao_nome']
            );
        }

        // Hidratar Usuário de Atualização
        $usuarioAtualizacao = null;
        if (isset($data['usuario_atualizacao_id']) && $data['usuario_atualizacao_id'] !== null) {
            $usuarioAtualizacao = new Usuario(
                $data['usuario_atualizacao_id'],
                null, // usuario
                null, // senha
                null, // permissoes
                null, // ativo
                $data['usuario_atualizacao_nome']
            );
        }

        return new Procedimento(
            $data['id'],
            $data['numero_procedimento'],
            $data['ano_procedimento'],
            $bairro,
            $pessoaPrincipal,
            $genitora,
            $demandante,
            (bool)$data['ativo'],
            $data['data_criacao'],
            $data['hora_criacao'],
            $data['id_usuario_criacao'],
            null, // dataHoraCriacaoRegistro agora é null, pois não vem do DB
            $data['id_usuario_atualizacao'],
            $data['data_hora_atualizacao'],
            $data['id_territorio'] // Adicionado ao construtor
        );
    }

    /**
     * Retorna todos os bairros ativos com seus territórios associados.
     * Opcionalmente filtra por um ID de território.
     * @param int|null $territorioId Opcional. ID do território para filtrar os bairros.
     * @return array<Bairro> Um array de objetos Bairro.
     */
    public function findAllActiveWithTerritories(int $territorioId = null): array
    {
        error_log("DEBUG DAO: ProcedimentoDAO::findAllActiveWithTerritories chamado com territorioId: " . var_export($territorioId, true));
        if ($territorioId !== null) {
            // Se um territorioId for fornecido, chamar um novo método no BairroDAO para filtrar.
            return $this->bairroDAO->findAllActiveByTerritoryId($territorioId);
        }
        // Caso contrário, retorna todos os ativos (comportamento atual)
        return $this->bairroDAO->findAllActiveWithTerritories();
    }
    
    /**
     * Retorna todos os territórios ativos.
     * @return array<Territorio> Um array de objetos Territorio.
     */
    public function findAllActiveTerritories(): array
    {
        return $this->territorioDAO->getAllActive();
    }

    /**
     * Retorna todas as pessoas ativas.
     * @return array<Pessoa> Um array de objetos Pessoa.
     */
    public function findAllActivePessoas(): array
    {
        return $this->pessoaDAO->getAllActive();
    }

    /**
     * Retorna todos os demandantes ativos.
     * @return array<Demandante> Um array de objetos Demandante.
     */
    public function findAllActiveDemandantes(): array
    {
        return $this->demandanteDAO->getAllActive();
    }

    /**
     * Retorna todos os sexos.
     * @return array<Sexo> Um array de objetos Sexo.
     */
    public function findAllSexos(): array
    {
        return $this->sexoDAO->getAllActive();
    }
}
