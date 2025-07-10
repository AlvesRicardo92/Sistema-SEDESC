<?php

namespace App\Controllers;

use App\Services\ProcedimentoService;
use App\Services\PessoaService;
use App\Services\DemandanteService;
use App\Services\BairroService;
use App\Services\SexoService;
use App\Services\TerritorioService;
use App\Exceptions\DatabaseException;
use App\Utils\TokenManager;
use InvalidArgumentException;

class ProcedimentoController extends BaseController
{
    private $procedimentoService;
    private $pessoaService;
    private $demandanteService;
    private $bairroService;
    private $sexoService;
    private $territorioService;

    public function __construct()
    {
        parent::__construct(); // Chama o construtor do BaseController
        $this->procedimentoService = new ProcedimentoService();
        $this->pessoaService = new PessoaService();
        $this->demandanteService = new DemandanteService();
        $this->bairroService = new BairroService();
        $this->sexoService = new SexoService();
        $this->territorioService = new TerritorioService();
    }

    /**
     * Exibe a página de gerenciamento de procedimentos.
     */
    public function index(): void
    {
        // A página de índice não carrega dados por padrão, eles são carregados via AJAX.
        // Apenas renderiza a view principal.
        $this->render('procedimentos/index');
    }

    /**
     * Retorna procedimentos com base em filtros de pesquisa em formato JSON.
     */
    public function searchJson(): void
    {
        header('Content-Type: application/json');
        $filtros = $_GET;

        // Validação do território do usuário logado
        $userTerritoryId = $_SESSION['user_territory_id'] ?? null;
        if (!$userTerritoryId) {
            $this->renderJson(['success' => false, 'message' => 'Território do usuário não definido.'], 401);
            return;
        }

        try {
            $procedimentos = $this->procedimentoService->buscarProcedimentosComFiltros($filtros, (int)$userTerritoryId);
            $this->renderJson($procedimentos);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao buscar procedimentos: ' . $e->getMessage()], 500);
        } catch (InvalidArgumentException $e) {
            $this->renderJson(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Retorna os detalhes de um procedimento específico em formato JSON, validando o token.
     */
    public function mostrarJson(): void
    {
        header('Content-Type: application/json');
        $token = $_GET['token'] ?? null;

        if (!$token) {
            $this->renderJson(['success' => false, 'message' => 'Token não fornecido.'], 400);
            return;
        }

        $id = TokenManager::validateToken($token, false); // Não remove o token, pois pode ser usado para edição logo em seguida

        if (!$id) {
            $this->renderJson(['success' => false, 'message' => 'Token inválido ou expirado.'], 401);
            return;
        }

        try {
            $procedimento = $this->procedimentoService->obterProcedimentoPorIdComNomes($id);
            if ($procedimento) {
                // Verifica se o procedimento pertence ao território do usuário logado
                $userTerritoryId = $_SESSION['user_territory_id'] ?? null;
                if ($userTerritoryId && $procedimento['id_territorio'] != $userTerritoryId) {
                    $this->renderJson(['success' => false, 'message' => 'Acesso negado. Procedimento não pertence ao seu território.'], 403);
                    return;
                }
                $this->renderJson($procedimento);
            } else {
                $this->renderJson(['success' => false, 'message' => 'Procedimento não encontrado.'], 404);
            }
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar detalhes do procedimento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Salva um novo procedimento no banco de dados via requisição AJAX.
     * Retorna uma resposta JSON.
     */
    public function salvarJson(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);

        // Adiciona o ID do usuário logado para auditoria
        $input['id_usuario_criacao'] = $_SESSION['user_id'] ?? null;
        $input['id_usuario_atualizacao'] = $_SESSION['user_id'] ?? null; // Para consistência, se não houver update posterior

        // Validação do território: novo procedimento deve pertencer ao território do usuário logado
        $userTerritoryId = $_SESSION['user_territory_id'] ?? null;
        if (!$userTerritoryId) {
            $this->renderJson(['success' => false, 'message' => 'Território do usuário não definido.'], 401);
            return;
        }
        $input['id_territorio'] = (int)$userTerritoryId; // Força o território do usuário logado

        try {
            $newId = $this->procedimentoService->salvarProcedimento($input);
            $this->renderJson(['success' => true, 'message' => 'Procedimento criado com sucesso!', 'id' => $newId]);
        } catch (\InvalidArgumentException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro de validação: ' . $e->getMessage()], 400);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao salvar procedimento: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro inesperado ao salvar procedimento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza um procedimento existente no banco de dados via requisição AJAX, validando o token.
     * Retorna uma resposta JSON.
     */
    public function atualizarJson(): void
    {
        header('Content-Type: application/json');
        $token = $_GET['token'] ?? null; // Pega o token da URL
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$token) {
            $this->renderJson(['success' => false, 'message' => 'Token não fornecido.'], 400);
            return;
        }

        $id = TokenManager::validateToken($token, true); // Remove o token após o uso

        if (!$id) {
            $this->renderJson(['success' => false, 'message' => 'Token inválido ou expirado.'], 401);
            return;
        }

        // Validação do território: procedimento deve pertencer ao território do usuário logado
        $userTerritoryId = $_SESSION['user_territory_id'] ?? null;
        if (!$userTerritoryId) {
            $this->renderJson(['success' => false, 'message' => 'Território do usuário não definido.'], 401);
            return;
        }
        // Buscar o procedimento para verificar se pertence ao território do usuário
        $procedimentoExistente = $this->procedimentoService->obterProcedimentoPorIdComNomes($id);
        if (!$procedimentoExistente || $procedimentoExistente['id_territorio'] != $userTerritoryId) {
            $this->renderJson(['success' => false, 'message' => 'Acesso negado. Procedimento não pertence ao seu território ou não encontrado.'], 403);
            return;
        }

        // Adiciona o ID do usuário logado para auditoria
        $input['id_usuario_atualizacao'] = $_SESSION['user_id'] ?? null;

        try {
            $success = $this->procedimentoService->atualizarProcedimento($id, $input);
            if ($success) {
                $this->renderJson(['success' => true, 'message' => 'Procedimento atualizado com sucesso!']);
            } else {
                $this->renderJson(['success' => false, 'message' => 'Falha ao atualizar procedimento ou nenhum dado alterado.'], 500);
            }
        } catch (\InvalidArgumentException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro de validação: ' . $e->getMessage()], 400);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao atualizar procedimento: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro inesperado ao atualizar procedimento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Deleta um procedimento existente no banco de dados via requisição AJAX, validando o token.
     * Retorna uma resposta JSON.
     */
    public function deletarJson(): void
    {
        header('Content-Type: application/json');
        $token = $_GET['token'] ?? null; // Pega o token da URL

        if (!$token) {
            $this->renderJson(['success' => false, 'message' => 'Token não fornecido.'], 400);
            return;
        }

        $id = TokenManager::validateToken($token, true); // Remove o token após o uso

        if (!$id) {
            $this->renderJson(['success' => false, 'message' => 'Token inválido ou expirado.'], 401);
            return;
        }

        // Validação do território: procedimento deve pertencer ao território do usuário logado
        $userTerritoryId = $_SESSION['user_territory_id'] ?? null;
        if (!$userTerritoryId) {
            $this->renderJson(['success' => false, 'message' => 'Território do usuário não definido.'], 401);
            return;
        }
        $procedimentoExistente = $this->procedimentoService->obterProcedimentoPorIdComNomes($id);
        if (!$procedimentoExistente || $procedimentoExistente['id_territorio'] != $userTerritoryId) {
            $this->renderJson(['success' => false, 'message' => 'Acesso negado. Procedimento não pertence ao seu território ou não encontrado.'], 403);
            return;
        }

        try {
            $success = $this->procedimentoService->deletarProcedimento($id);
            if ($success) {
                $this->renderJson(['success' => true, 'message' => 'Procedimento excluído com sucesso!']);
            } else {
                $this->renderJson(['success' => false, 'message' => 'Falha ao excluir procedimento ou procedimento não encontrado.'], 500);
            }
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao excluir procedimento: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro inesperado ao excluir procedimento: ' . $e->getMessage()], 500);
        }
    }

    // Métodos CRUD básicos (mantidos para compatibilidade, mas a UI usará os JSON)
    public function listar(): void
    {
        try {
            $procedimentos = $this->procedimentoService->obterTodosProcedimentos();
            $this->render('procedimentos/listar', ['procedimentos' => $procedimentos]);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar procedimentos: ' . $e->getMessage()], 500);
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $procedimento = $this->procedimentoService->obterProcedimentoPorIdComNomes($id);
            if ($procedimento) {
                $this->render('procedimentos/detalhe', ['procedimento' => $procedimento]);
            } else {
                $this->render('404', [], 404);
            }
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar detalhes do procedimento: ' . $e->getMessage()], 500);
        }
    }

    public function criar(): void
    {
        // Esta função não será usada diretamente pela UI de procedimentos, mas pode ser mantida para outros fins
        $this->render('procedimentos/criar');
    }

    public function salvar(): void
    {
        // Esta função não será usada diretamente pela UI de procedimentos, mas pode ser mantida para outros fins
        // A lógica de salvamento agora está em salvarJson()
        $this->renderJson(['success' => false, 'message' => 'Método não suportado. Use salvarJson.'], 405);
    }

    public function editar(int $id): void
    {
        // Esta função não será usada diretamente pela UI de procedimentos, mas pode ser mantida para outros fins
        try {
            $procedimento = $this->procedimentoService->obterProcedimentoPorIdComNomes($id);
            if ($procedimento) {
                $this->render('procedimentos/editar', ['procedimento' => $procedimento]);
            } else {
                $this->render('404', [], 404);
            }
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar procedimento para edição: ' . $e->getMessage()], 500);
        }
    }

    public function atualizar(int $id): void
    {
        // Esta função não será usada diretamente pela UI de procedimentos, mas pode ser mantida para outros fins
        // A lógica de atualização agora está em atualizarJson()
        $this->renderJson(['success' => false, 'message' => 'Método não suportado. Use atualizarJson.'], 405);
    }
}
