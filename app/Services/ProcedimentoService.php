<?php

namespace App\Services;

use App\DAO\ProcedimentoDAO;
use App\DAO\PessoaDAO; // Para buscar nomes de pessoa/genitora
use App\DAO\DemandanteDAO; // Para buscar nomes de demandante
use App\DAO\TerritorioDAO; // Para buscar nomes de território
use App\DAO\BairroDAO; // Para buscar nomes de bairro
use App\DAO\UsuarioDAO; // Para buscar nomes de usuário
use App\Models\Procedimento;
use App\Models\Pessoa;
use App\Models\Demandante;
use App\Utils\Database;
use App\Utils\TokenManager;
use App\Exceptions\DatabaseException;
use InvalidArgumentException;

/**
 * Serviço para gerenciar operações relacionadas a Procedimentos.
 */
class ProcedimentoService
{
    private $procedimentoDAO;
    private $pessoaService;
    private $demandanteService;
    private $territorioService;
    private $bairroService;
    private $usuarioDAO; // Para obter nomes de usuário

    public function __construct()
    {
        $this->procedimentoDAO = new ProcedimentoDAO();
        $this->pessoaService = new PessoaService();
        $this->demandanteService = new DemandanteService();
        $this->territorioService = new TerritorioService();
        $this->bairroService = new BairroService();
        $this->usuarioDAO = new UsuarioDAO();
    }

    /**
     * Obtém todos os procedimentos.
     *
     * @return array Um array de objetos Procedimento.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterTodosProcedimentos(): array
    {
        return $this->procedimentoDAO->buscarTodos();
    }

    /**
     * Obtém um procedimento pelo ID, incluindo nomes relacionados e gera um token.
     *
     * @param int $id O ID do procedimento.
     * @return array|null Um array associativo com os dados do procedimento e nomes relacionados, ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterProcedimentoPorIdComNomes(int $id): ?array
    {
        $procedimento = $this->procedimentoDAO->buscarPorID($id);

        if ($procedimento) {
            $data = (array) $procedimento; // Converte o objeto Procedimento para array

            // Adiciona nomes relacionados
            if ($procedimento->id_territorio) {
                $territorio = $this->territorioService->obterTerritorioPorId($procedimento->id_territorio);
                $data['nome_territorio'] = $territorio ? $territorio->nome : null;
            }
            if ($procedimento->id_bairro) {
                $bairro = $this->bairroService->obterBairroPorId($procedimento->id_bairro);
                $data['nome_bairro'] = $bairro ? $bairro->nome : null;
                if ($bairro && $bairro->territorio_id) {
                    $territorioBairro = $this->territorioService->obterTerritorioPorId($bairro->territorio_id);
                    $data['nome_territorio_bairro'] = $territorioBairro ? $territorioBairro->nome : null;
                }
            }
            if ($procedimento->id_pessoa) {
                $pessoa = $this->pessoaService->obterPessoaPorId($procedimento->id_pessoa);
                $data['nome_pessoa'] = $pessoa ? $pessoa->nome : null;
                $data['data_nascimento_pessoa'] = $pessoa ? $pessoa->data_nascimento : null;
                $data['id_sexo_pessoa'] = $pessoa ? $pessoa->id_sexo : null;
            }
            if ($procedimento->id_genitora_pessoa) {
                $genitora = $this->pessoaService->obterPessoaPorId($procedimento->id_genitora_pessoa);
                $data['nome_genitora_pessoa'] = $genitora ? $genitora->nome : null;
                $data['data_nascimento_genitora'] = $genitora ? $genitora->data_nascimento : null;
                $data['id_sexo_genitora'] = $genitora ? $genitora->id_sexo : null;
            }
            if ($procedimento->id_demandante) {
                $demandante = $this->demandanteService->obterDemandantePorId($procedimento->id_demandante);
                $data['nome_demandante'] = $demandante ? $demandante->nome : null;
            }
            if ($procedimento->id_usuario_criacao) {
                $usuarioCriacao = $this->usuarioDAO->buscarPorID($procedimento->id_usuario_criacao);
                $data['nome_usuario_criacao'] = $usuarioCriacao ? $usuarioCriacao->nome : null;
            }
            if ($procedimento->id_usuario_atualizacao) {
                $usuarioAtualizacao = $this->usuarioDAO->buscarPorID($procedimento->id_usuario_atualizacao);
                $data['nome_usuario_atualizacao'] = $usuarioAtualizacao ? $usuarioAtualizacao->nome : null;
            }

            // Gera e adiciona o token
            $data['token'] = TokenManager::generateToken($procedimento->id);

            return $data;
        }
        return null;
    }

    /**
     * Busca procedimentos com base em filtros, incluindo nomes relacionados e gera tokens.
     *
     * @param array $filtros Array associativo com os filtros (ex: 'numero_procedimento', 'nome_pessoa', 'nome_genitora', 'data_nascimento').
     * @param int $territorioId O ID do território do usuário logado para filtrar.
     * @return array Um array de objetos Procedimento com tokens.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarProcedimentosComFiltros(array $filtros, int $territorioId): array
    {
        $procedimentos = $this->procedimentoDAO->buscarComFiltros($filtros, $territorioId);
        $result = [];

        foreach ($procedimentos as $procedimento) {
            $data = (array) $procedimento;
            // Os nomes de pessoa e genitora já vêm do DAO na busca com filtros
            // Adiciona o token
            $data['token'] = TokenManager::generateToken($procedimento->id);
            $result[] = $data;
        }
        return $result;
    }

    /**
     * Salva um novo procedimento no banco de dados.
     *
     * @param array $dados Array associativo com os dados do procedimento.
     * @return int O ID do novo procedimento inserido.
     * @throws InvalidArgumentException Se os dados forem inválidos.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function salvarProcedimento(array $dados): int
    {
        // Validação básica
        if (empty($dados['numero_procedimento']) || empty($dados['ano_procedimento']) || empty($dados['id_territorio'])) {
            throw new InvalidArgumentException("Número, ano e território do procedimento são obrigatórios.");
        }

        // Lidar com Pessoa
        $idPessoa = null;
        if (!empty($dados['nome_pessoa'])) {
            $pessoaData = [
                'nome' => $dados['nome_pessoa'],
                'data_nascimento' => $dados['data_nascimento_pessoa'] ?? null,
                'id_sexo' => $dados['id_sexo_pessoa'] ?? null,
                'ativo' => 1,
                'id_usuario_criacao' => $_SESSION['user_id'] ?? null
            ];
            $idPessoa = $this->pessoaService->salvarPessoa($pessoaData);
        }
        $dados['id_pessoa'] = $idPessoa;

        // Lidar com Genitora
        $idGenitoraPessoa = null;
        if (!empty($dados['nome_genitora'])) {
            $genitoraData = [
                'nome' => $dados['nome_genitora'],
                'data_nascimento' => $dados['data_nascimento_genitora'] ?? null,
                'id_sexo' => $dados['id_sexo_genitora'] ?? null,
                'ativo' => 1,
                'id_usuario_criacao' => $_SESSION['user_id'] ?? null
            ];
            $idGenitoraPessoa = $this->pessoaService->salvarPessoa($genitoraData);
        }
        $dados['id_genitora_pessoa'] = $idGenitoraPessoa;

        // Lidar com Demandante
        $idDemandante = null;
        if (!empty($dados['nome_demandante'])) {
            $demandanteData = [
                'nome' => $dados['nome_demandante'],
                'ativo' => 1,
                'id_usuario_criacao' => $_SESSION['user_id'] ?? null
            ];
            $idDemandante = $this->demandanteService->salvarDemandante($demandanteData);
        }
        $dados['id_demandante'] = $idDemandante;

        // Adicionar informações de criação
        $dados['id_usuario_criacao'] = $_SESSION['user_id'] ?? null;
        $dados['data_criacao'] = date('Y-m-d');
        $dados['hora_criacao'] = date('H:i:s');

        $procedimento = new Procedimento($dados);
        $newId = $this->procedimentoDAO->criar($procedimento);

        if (!$newId) {
            throw new DatabaseException("Falha ao criar o procedimento.");
        }

        return $newId;
    }

    /**
     * Atualiza um procedimento existente no banco de dados.
     *
     * @param int $id O ID do procedimento a ser atualizado.
     * @param array $dados Array associativo com os dados do procedimento.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws InvalidArgumentException Se os dados forem inválidos.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizarProcedimento(int $id, array $dados): bool
    {
        $procedimentoExistente = $this->procedimentoDAO->buscarPorID($id);
        if (!$procedimentoExistente) {
            throw new InvalidArgumentException("Procedimento com ID {$id} não encontrado.");
        }

        // Lidar com Pessoa
        $idPessoa = $procedimentoExistente->id_pessoa; // Mantém o ID existente por padrão
        if (!empty($dados['nome_pessoa'])) {
            $pessoaData = [
                'id' => $idPessoa, // Se já tem ID, tenta atualizar
                'nome' => $dados['nome_pessoa'],
                'data_nascimento' => $dados['data_nascimento_pessoa'] ?? null,
                'id_sexo' => $dados['id_sexo_pessoa'] ?? null,
                'ativo' => 1,
                'id_usuario_criacao' => $_SESSION['user_id'] ?? null, // Usado se for nova criação
                'id_usuario_atualizacao' => $_SESSION['user_id'] ?? null
            ];
            $idPessoa = $this->pessoaService->salvarPessoa($pessoaData); // Este método lida com criação/atualização
        } else {
            $idPessoa = null; // Se o nome da pessoa for limpo, desvincula
        }
        $dados['id_pessoa'] = $idPessoa;


        // Lidar com Genitora
        $idGenitoraPessoa = $procedimentoExistente->id_genitora_pessoa; // Mantém o ID existente por padrão
        if (!empty($dados['nome_genitora'])) {
            $genitoraData = [
                'id' => $idGenitoraPessoa, // Se já tem ID, tenta atualizar
                'nome' => $dados['nome_genitora'],
                'data_nascimento' => $dados['data_nascimento_genitora'] ?? null,
                'id_sexo' => $dados['id_sexo_genitora'] ?? null,
                'ativo' => 1,
                'id_usuario_criacao' => $_SESSION['user_id'] ?? null,
                'id_usuario_atualizacao' => $_SESSION['user_id'] ?? null
            ];
            $idGenitoraPessoa = $this->pessoaService->salvarPessoa($genitoraData);
        } else {
            $idGenitoraPessoa = null;
        }
        $dados['id_genitora_pessoa'] = $idGenitoraPessoa;

        // Lidar com Demandante
        $idDemandante = $procedimentoExistente->id_demandante; // Mantém o ID existente por padrão
        if (!empty($dados['nome_demandante'])) {
            $demandanteData = [
                'id' => $idDemandante, // Se já tem ID, tenta atualizar
                'nome' => $dados['nome_demandante'],
                'ativo' => 1,
                'id_usuario_criacao' => $_SESSION['user_id'] ?? null,
                'id_usuario_atualizacao' => $_SESSION['user_id'] ?? null
            ];
            $idDemandante = $this->demandanteService->salvarDemandante($demandanteData);
        } else {
            $idDemandante = null;
        }
        $dados['id_demandante'] = $idDemandante;

        // Atualizar os dados do procedimento existente com os novos valores
        foreach ($dados as $key => $value) {
            // Ignora numero_procedimento e ano_procedimento conforme regra
            if ($key !== 'numero_procedimento' && $key !== 'ano_procedimento') {
                if (property_exists($procedimentoExistente, $key)) {
                    $procedimentoExistente->$key = $value;
                }
            }
        }

        // Adicionar informações de atualização
        $procedimentoExistente->id_usuario_atualizacao = $_SESSION['user_id'] ?? null;
        $procedimentoExistente->data_hora_atualizacao = date('Y-m-d H:i:s');

        return $this->procedimentoDAO->atualizar($procedimentoExistente);
    }

    /**
     * Deleta um procedimento do banco de dados.
     *
     * @param int $id O ID do procedimento a ser deletado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function deletarProcedimento(int $id): bool
    {
        return $this->procedimentoDAO->deletar($id);
    }
}
