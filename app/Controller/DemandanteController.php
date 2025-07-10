<?php

namespace App\Controllers;

use App\Services\DemandanteService;
use App\Models\Demandante; // Assumindo que você tem um modelo Demandante
use App\Exceptions\DatabaseException; // Assumindo que você tem uma exceção de banco de dados

class DemandanteController
{
    private $demandanteService;

    public function __construct()
    {
        $this->demandanteService = new DemandanteService();
    }

    public function listar(): void
    {
        try {
            $demandantes = $this->demandanteService->obterTodosDemandantes();
            $this->render('demandantes/listar', ['demandantes' => $demandantes]);
        } catch (DatabaseException $e) {
            echo "Erro ao carregar demandantes: " . $e->getMessage();
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $demandante = $this->demandanteService->obterDemandantePorId($id);
            if ($demandante) {
                $this->render('demandantes/detalhe', ['demandante' => $demandante]);
            } else {
                echo "Demandante não encontrado.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar detalhes do demandante: " . $e->getMessage();
        }
    }

    public function criar(): void
    {
        // Exibe o formulário de criação
        $this->render('demandantes/criar');
    }

    public function salvar(): void
    {
        // Processa os dados do POST
        $dados = $_POST; // Exemplo simples, em um projeto real faria validação e sanitização
        try {
            $this->demandanteService->salvarDemandante($dados);
            header('Location: /demandantes/listar'); // Redireciona para a lista após salvar
            exit();
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao salvar demandante: " . $e->getMessage();
        }
    }

    public function editar(int $id): void
    {
        try {
            $demandante = $this->demandanteService->obterDemandantePorId($id);
            if ($demandante) {
                $this->render('demandantes/editar', ['demandante' => $demandante]);
            } else {
                echo "Demandante não encontrado para edição.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar demandante para edição: " . $e->getMessage();
        }
    }

    public function atualizar(int $id): void
    {
        // Processa os dados do POST
        $dados = $_POST; // Exemplo simples
        try {
            $this->demandanteService->atualizarDemandante($id, $dados);
            header('Location: /demandantes/listar'); // Redireciona para a lista após atualizar
            exit();
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao atualizar demandante: " . $e->getMessage();
        }
    }

    // O método 'deletar' não foi solicitado na interface BaseDAOInterface,
    // mas pode ser adicionado se necessário no Service e DAO.
    // public function deletar(int $id): void
    // {
    //     try {
    //         if ($this->demandanteService->deletarDemandante($id)) {
    //             header('Location: /demandantes/listar');
    //             exit();
    //         } else {
    //             echo "Erro ao deletar demandante ou demandante não encontrado.";
    //         }
    //     } catch (DatabaseException $e) {
    //         echo "Erro ao deletar demandante: " . $e->getMessage();
    //     }
    // }

    /**
     * Função auxiliar para renderizar as views.
     *
     * @param string $viewName O nome da view (ex: 'demandantes/listar').
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
