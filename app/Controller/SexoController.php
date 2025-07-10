<?php

namespace App\Controllers;

use App\Services\SexoService;
use App\Models\Sexo; // Assumindo que você tem um modelo Sexo
use App\Exceptions\DatabaseException; // Assumindo que você tem uma exceção de banco de dados

class SexoController
{
    private $sexoService;

    public function __construct()
    {
        $this->sexoService = new SexoService();
    }

    /**
     * Exibe a lista de todos os sexos.
     */
    public function listar(): void
    {
        try {
            $sexos = $this->sexoService->obterTodosSexos();
            $this->render('sexos/listar', ['sexos' => $sexos]);
        } catch (DatabaseException $e) {
            echo "Erro ao carregar sexos: " . $e->getMessage();
        }
    }

    /**
     * Exibe os detalhes de um sexo específico.
     *
     * @param int $id O ID do sexo a ser exibido.
     */
    public function mostrar(int $id): void
    {
        try {
            $sexo = $this->sexoService->obterSexoPorId($id);
            if ($sexo) {
                $this->render('sexos/detalhe', ['sexo' => $sexo]);
            } else {
                echo "Sexo não encontrado.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar detalhes do sexo: " . $e->getMessage();
        }
    }

    /**
     * Exibe o formulário para criar um novo sexo.
     */
    public function criar(): void
    {
        $this->render('sexos/criar');
    }

    /**
     * Salva um novo sexo no banco de dados.
     * Este método seria chamado via POST.
     */
    public function salvar(): void
    {
        // Aqui você processaria os dados do POST
        $dados = $_POST; // Exemplo simples, em um projeto real faria validação e sanitização
        try {
            $newId = $this->sexoService->salvarSexo($dados);
            if ($newId) {
                header('Location: /sexos/mostrar?id=' . $newId); // Redireciona para os detalhes do novo sexo
                exit();
            } else {
                echo "Falha ao criar sexo.";
            }
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao salvar sexo: " . $e->getMessage();
        }
    }

    /**
     * Exibe o formulário para editar um sexo existente.
     *
     * @param int $id O ID do sexo a ser editado.
     */
    public function editar(int $id): void
    {
        try {
            $sexo = $this->sexoService->obterSexoPorId($id);
            if ($sexo) {
                $this->render('sexos/editar', ['sexo' => $sexo]);
            } else {
                echo "Sexo não encontrado para edição.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar sexo para edição: " . $e->getMessage();
        }
    }

    /**
     * Atualiza um sexo existente no banco de dados.
     * Este método seria chamado via POST.
     *
     * @param int $id O ID do sexo a ser atualizado.
     */
    public function atualizar(int $id): void
    {
        // Aqui você processaria os dados do POST
        $dados = $_POST; // Exemplo simples
        try {
            $success = $this->sexoService->atualizarSexo($id, $dados);
            if ($success) {
                header('Location: /sexos/mostrar?id=' . $id); // Redireciona para os detalhes do sexo atualizado
                exit();
            } else {
                echo "Falha ao atualizar sexo ID {$id}.";
            }
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao atualizar sexo: " . $e->getMessage();
        }
    }

    // O método 'deletar' não foi solicitado na interface BaseDAOInterface,
    // mas pode ser adicionado se necessário no Service e DAO.
    // public function deletar(int $id): void
    // {
    //     try {
    //         if ($this->sexoService->deletarSexo($id)) {
    //             header('Location: /sexos/listar');
    //             exit();
    //         } else {
    //             echo "Erro ao deletar sexo ou sexo não encontrado.";
    //         }
    //     } catch (DatabaseException $e) {
    //         echo "Erro ao deletar sexo: " . $e->getMessage();
    //     }
    // }

    /**
     * Função auxiliar para renderizar as views.
     *
     * @param string $viewName O nome da view (ex: 'sexos/listar').
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
