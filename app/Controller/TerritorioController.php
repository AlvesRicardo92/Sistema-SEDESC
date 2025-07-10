<?php

namespace App\Controllers;

use App\Services\TerritorioService;
use App\Models\Territorio; // Assumindo que você tem um modelo Territorio
use App\Exceptions\DatabaseException; // Assumindo que você tem uma exceção de banco de dados

class TerritorioController
{
    private $territorioService;

    public function __construct()
    {
        $this->territorioService = new TerritorioService();
    }

    /**
     * Exibe a lista de todos os territórios.
     */
    public function listar(): void
    {
        try {
            $territorios = $this->territorioService->obterTodosTerritorios();
            $this->render('territorios/listar', ['territorios' => $territorios]);
        } catch (DatabaseException $e) {
            echo "Erro ao carregar territórios: " . $e->getMessage();
        }
    }

    /**
     * Exibe os detalhes de um território específico.
     *
     * @param int $id O ID do território a ser exibido.
     */
    public function mostrar(int $id): void
    {
        try {
            $territorio = $this->territorioService->obterTerritorioPorId($id);
            if ($territorio) {
                $this->render('territorios/detalhe', ['territorio' => $territorio]);
            } else {
                echo "Território não encontrado.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar detalhes do território: " . $e->getMessage();
        }
    }

    /**
     * Exibe o formulário para criar um novo território.
     */
    public function criar(): void
    {
        $this->render('territorios/criar');
    }

    /**
     * Salva um novo território no banco de dados.
     * Este método seria chamado via POST.
     */
    public function salvar(): void
    {
        // Aqui você processaria os dados do POST
        $dados = $_POST; // Exemplo simples, em um projeto real faria validação e sanitização
        try {
            $newId = $this->territorioService->salvarTerritorio($dados);
            if ($newId) {
                header('Location: /territorios/mostrar?id=' . $newId); // Redireciona para os detalhes do novo território
                exit();
            } else {
                echo "Falha ao criar território.";
            }
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao salvar território: " . $e->getMessage();
        }
    }

    /**
     * Exibe o formulário para editar um território existente.
     *
     * @param int $id O ID do território a ser editado.
     */
    public function editar(int $id): void
    {
        try {
            $territorio = $this->territorioService->obterTerritorioPorId($id);
            if ($territorio) {
                $this->render('territorios/editar', ['territorio' => $territorio]);
            } else {
                echo "Território não encontrado para edição.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar território para edição: " . $e->getMessage();
        }
    }

    /**
     * Atualiza um território existente no banco de dados.
     * Este método seria chamado via POST.
     *
     * @param int $id O ID do território a ser atualizado.
     */
    public function atualizar(int $id): void
    {
        // Aqui você processaria os dados do POST
        $dados = $_POST; // Exemplo simples
        try {
            $success = $this->territorioService->atualizarTerritorio($id, $dados);
            if ($success) {
                header('Location: /territorios/mostrar?id=' . $id); // Redireciona para os detalhes do território atualizado
                exit();
            } else {
                echo "Falha ao atualizar território ID {$id}.";
            }
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao atualizar território: " . $e->getMessage();
        }
    }

    // O método 'deletar' não foi solicitado na interface BaseDAOInterface,
    // mas pode ser adicionado se necessário no Service e DAO.
    // public function deletar(int $id): void
    // {
    //     try {
    //         if ($this->territorioService->deletarTerritorio($id)) {
    //             header('Location: /territorios/listar');
    //             exit();
    //         } else {
    //             echo "Erro ao deletar território ou território não encontrado.";
    //         }
    //     } catch (DatabaseException $e) {
    //         echo "Erro ao deletar território: " . $e->getMessage();
    //     }
    // }

    /**
     * Função auxiliar para renderizar as views.
     *
     * @param string $viewName O nome da view (ex: 'territorios/listar').
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
