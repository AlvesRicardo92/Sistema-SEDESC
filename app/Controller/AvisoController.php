<?php

namespace App\Controllers;

use App\Services\AvisoService;
use App\Models\Aviso; // Assumindo que você tem um modelo Aviso
use App\Exceptions\DatabaseException; // Assumindo que você tem uma exceção de banco de dados

class AvisoController
{
    private $avisoService;

    public function __construct()
    {
        $this->avisoService = new AvisoService();
    }

    /**
     * Exibe a lista de todos os avisos.
     */
    public function listar(): void
    {
        try {
            $avisos = $this->avisoService->obterTodosAvisos();
            $this->render('avisos/listar', ['avisos' => $avisos]);
        } catch (DatabaseException $e) {
            echo "Erro ao carregar avisos: " . $e->getMessage();
        }
    }

    /**
     * Exibe os detalhes de um aviso específico.
     *
     * @param int $id O ID do aviso a ser exibido.
     */
    public function mostrar(int $id): void
    {
        try {
            $aviso = $this->avisoService->obterAvisoPorId($id);
            if ($aviso) {
                $this->render('avisos/detalhe', ['aviso' => $aviso]);
            } else {
                echo "Aviso não encontrado.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar detalhes do aviso: " . $e->getMessage();
        }
    }

    /**
     * Exibe o formulário para criar um novo aviso.
     */
    public function criar(): void
    {
        $this->render('avisos/criar');
    }

    /**
     * Salva um novo aviso no banco de dados.
     * Este método seria chamado via POST.
     */
    public function salvar(): void
    {
        // Aqui você processaria os dados do POST
        $dados = $_POST; // Exemplo simples, em um projeto real faria validação e sanitização
        try {
            // Adiciona o ID do usuário logado e a data/hora atual para criação
            $dados['id_usuario_criacao'] = $_SESSION['user_id'] ?? null;
            $dados['data_hora_criacao'] = date('Y-m-d H:i:s');

            $newId = $this->avisoService->salvarAviso($dados);
            if ($newId) {
                header('Location: /avisos/mostrar?id=' . $newId); // Redireciona para os detalhes do novo aviso
                exit();
            } else {
                echo "Falha ao criar aviso.";
            }
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao salvar aviso: " . $e->getMessage();
        }
    }

    /**
     * Exibe o formulário para editar um aviso existente.
     *
     * @param int $id O ID do aviso a ser editado.
     */
    public function editar(int $id): void
    {
        try {
            $aviso = $this->avisoService->obterAvisoPorId($id);
            if ($aviso) {
                $this->render('avisos/editar', ['aviso' => $aviso]);
            } else {
                echo "Aviso não encontrado para edição.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar aviso para edição: " . $e->getMessage();
        }
    }

    /**
     * Atualiza um aviso existente no banco de dados.
     * Este método seria chamado via POST.
     *
     * @param int $id O ID do aviso a ser atualizado.
     */
    public function atualizar(int $id): void
    {
        // Aqui você processaria os dados do POST
        $dados = $_POST; // Exemplo simples
        try {
            // Adiciona o ID do usuário logado e a data/hora atual para atualização
            $dados['id_usuario_atualizacao'] = $_SESSION['user_id'] ?? null;
            $dados['data_hora_atualizacao'] = date('Y-m-d H:i:s');

            $success = $this->avisoService->atualizarAviso($id, $dados);
            if ($success) {
                header('Location: /avisos/mostrar?id=' . $id); // Redireciona para os detalhes do aviso atualizado
                exit();
            } else {
                echo "Falha ao atualizar aviso ID {$id}.";
            }
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao atualizar aviso: " . $e->getMessage();
        }
    }

    /**
     * Função auxiliar para renderizar as views.
     *
     * @param string $viewName O nome da view (ex: 'avisos/listar').
     * @param array $data Um array associativo de dados a serem passados para a view.
     */
    private function render(string $viewName, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "Erro: View '{$viewName}' não encontrada.";
        }
    }
}
