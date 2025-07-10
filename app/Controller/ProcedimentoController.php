<?php

namespace App\Controllers;

use App\Services\ProcedimentoService;
use App\Models\Procedimento; // Assumindo que você tem um modelo Procedimento
use App\Exceptions\DatabaseException; // Assumindo que você tem uma exceção de banco de dados

/**
 * Controller para a entidade Procedimento.
 * Lida com as requisições HTTP relacionadas a procedimentos.
 */
class ProcedimentoController
{
    private $procedimentoService;

    /**
     * Construtor do ProcedimentoController.
     * Injeta a dependência do ProcedimentoService.
     */
    public function __construct()
    {
        $this->procedimentoService = new ProcedimentoService();
    }

    /**
     * Exibe a lista de todos os procedimentos.
     */
    public function listar(): void
    {
        try {
            $procedimentos = $this->procedimentoService->obterTodosProcedimentos();
            // Renderiza a view de listagem, passando os dados dos procedimentos
            $this->render('procedimentos/listar', ['dados' => $procedimentos]); // Alterado para 'dados' para compatibilidade com template
        } catch (DatabaseException $e) {
            // Em um ambiente real, você logaria o erro e mostraria uma página de erro amigável.
            echo "Erro ao carregar procedimentos: " . $e->getMessage();
        }
    }

    /**
     * Exibe os detalhes de um procedimento específico.
     *
     * @param int $id O ID do procedimento a ser exibido.
     */
    public function mostrar(int $id): void
    {
        try {
            $procedimento = $this->procedimentoService->obterProcedimentoPorId($id);

            if ($procedimento) {
                // Renderiza a view de detalhes, passando os dados do procedimento
                $this->render('procedimentos/detalhe', ['item' => $procedimento]); // Alterado para 'item' para compatibilidade com template
            } else {
                // Procedimento não encontrado, pode redirecionar para uma página 404 ou exibir uma mensagem
                echo "Procedimento não encontrado.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar detalhes do procedimento: " . $e->getMessage();
        }
    }

    /**
     * Exibe o formulário para criar um novo procedimento.
     */
    public function criar(): void
    {
        $this->render('procedimentos/criar');
    }

    /**
     * Salva um novo procedimento no banco de dados.
     * Este método seria chamado via POST.
     */
    public function salvar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Aqui você processaria os dados do POST
            $dados = $_POST; // Exemplo simples, em um projeto real faria validação e sanitização
            try {
                // Adiciona o ID do usuário logado e a data/hora atual para criação
                $dados['id_usuario_criacao'] = $_SESSION['user_id'] ?? null;
                $dados['data_criacao'] = date('Y-m-d');
                $dados['hora_criacao'] = date('H:i:s');

                $newId = $this->procedimentoService->salvarProcedimento($dados);
                if ($newId) {
                    header('Location: /procedimentos/mostrar?id=' . $newId); // Redireciona para os detalhes do novo procedimento
                    exit();
                } else {
                    echo "Falha ao criar procedimento.";
                }
            } catch (\InvalidArgumentException $e) {
                echo "Erro de validação: " . $e->getMessage();
            } catch (DatabaseException $e) {
                echo "Erro ao salvar procedimento: " . $e->getMessage();
            }
        } else {
            // Se a requisição não for POST, redireciona para o formulário de criação
            header("Location: /procedimentos/criar");
            exit();
        }
    }

    /**
     * Exibe o formulário para editar um procedimento existente.
     *
     * @param int $id O ID do procedimento a ser editado.
     */
    public function editar(int $id): void
    {
        try {
            $procedimento = $this->procedimentoService->obterProcedimentoPorId($id);
            if ($procedimento) {
                $this->render('procedimentos/editar', ['item' => $procedimento]); // Alterado para 'item'
            } else {
                echo "Procedimento não encontrado para edição.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar procedimento para edição: " . $e->getMessage();
        }
    }

    /**
     * Atualiza um procedimento existente no banco de dados.
     * Este método seria chamado via POST.
     *
     * @param int $id O ID do procedimento a ser atualizado.
     */
    public function atualizar(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Aqui você processaria os dados do POST
            $dados = $_POST; // Exemplo simples
            try {
                // Adiciona o ID do usuário logado e a data/hora atual para atualização
                $dados['id_usuario_atualizacao'] = $_SESSION['user_id'] ?? null;
                $dados['data_hora_atualizacao'] = date('Y-m-d H:i:s');

                $success = $this->procedimentoService->atualizarProcedimento($id, $dados);
                if ($success) {
                    header('Location: /procedimentos/mostrar?id=' . $id); // Redireciona para os detalhes do procedimento atualizado
                    exit();
                } else {
                    echo "Falha ao atualizar procedimento ID {$id}.";
                }
            } catch (\InvalidArgumentException $e) {
                echo "Erro de validação: " . $e->getMessage();
            } catch (DatabaseException $e) {
                echo "Erro ao atualizar procedimento: " . $e->getMessage();
            }
        } else {
            // Se a requisição não for POST, redireciona para o formulário de edição
            header("Location: /procedimentos/editar?id=" . $id);
            exit();
        }
    }

    /**
     * Função auxiliar para renderizar as views.
     *
     * @param string $viewName O nome da view (ex: 'procedimentos/listar').
     * @param array $data Um array associativo de dados a serem passados para a view.
     */
    private function render(string $viewName, array $data = []): void
    {
        // Extrai os dados para que as variáveis fiquem disponíveis diretamente na view
        extract($data);

        // Inclui o arquivo da view
        $viewPath = __DIR__ . '/../Views/' . $viewName . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "Erro: View '{$viewName}' não encontrada.";
        }
    }
}
