<?php

namespace App\Controllers;

use App\Services\PessoaService;
use App\Models\Pessoa; // Assumindo que você tem um modelo Pessoa
use App\Exceptions\DatabaseException; // Assumindo que você tem uma exceção de banco de dados

class PessoaController
{
    private $pessoaService;

    public function __construct()
    {
        $this->pessoaService = new PessoaService();
    }

    /**
     * Exibe a lista de todas as pessoas.
     */
    public function listar(): void
    {
        try {
            $pessoas = $this->pessoaService->obterTodasPessoas();
            $this->render('pessoas/listar', ['pessoas' => $pessoas]);
        } catch (DatabaseException $e) {
            echo "Erro ao carregar pessoas: " . $e->getMessage();
        }
    }

    /**
     * Exibe os detalhes de uma pessoa específica.
     *
     * @param int $id O ID da pessoa a ser exibida.
     */
    public function mostrar(int $id): void
    {
        try {
            $pessoa = $this->pessoaService->obterPessoaPorId($id);
            if ($pessoa) {
                $this->render('pessoas/detalhe', ['pessoa' => $pessoa]);
            } else {
                echo "Pessoa não encontrada.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar detalhes da pessoa: " . $e->getMessage();
        }
    }

    /**
     * Exibe o formulário para criar uma nova pessoa.
     */
    public function criar(): void
    {
        $this->render('pessoas/criar');
    }

    /**
     * Salva uma nova pessoa no banco de dados.
     * Este método seria chamado via POST.
     */
    public function salvar(): void
    {
        // Aqui você processaria os dados do POST
        $dados = $_POST; // Exemplo simples, em um projeto real faria validação e sanitização
        try {
            $newId = $this->pessoaService->salvarPessoa($dados);
            if ($newId) {
                header('Location: /pessoas/mostrar?id=' . $newId); // Redireciona para os detalhes da nova pessoa
                exit();
            } else {
                echo "Falha ao criar pessoa.";
            }
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao salvar pessoa: " . $e->getMessage();
        }
    }

    /**
     * Exibe o formulário para editar uma pessoa existente.
     *
     * @param int $id O ID da pessoa a ser editada.
     */
    public function editar(int $id): void
    {
        try {
            $pessoa = $this->pessoaService->obterPessoaPorId($id);
            if ($pessoa) {
                $this->render('pessoas/editar', ['pessoa' => $pessoa]);
            } else {
                echo "Pessoa não encontrada para edição.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar pessoa para edição: " . $e->getMessage();
        }
    }

    /**
     * Atualiza uma pessoa existente no banco de dados.
     * Este método seria chamado via POST.
     *
     * @param int $id O ID da pessoa a ser atualizada.
     */
    public function atualizar(int $id): void
    {
        // Aqui você processaria os dados do POST
        $dados = $_POST; // Exemplo simples
        try {
            $success = $this->pessoaService->atualizarPessoa($id, $dados);
            if ($success) {
                header('Location: /pessoas/mostrar?id=' . $id); // Redireciona para os detalhes da pessoa atualizada
                exit();
            } else {
                echo "Falha ao atualizar pessoa ID {$id}.";
            }
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao atualizar pessoa: " . $e->getMessage();
        }
    }

    // O método 'deletar' não foi solicitado na interface BaseDAOInterface,
    // mas pode ser adicionado se necessário no Service e DAO.
    // public function deletar(int $id): void
    // {
    //     try {
    //         if ($this->pessoaService->deletarPessoa($id)) {
    //             header('Location: /pessoas/listar');
    //             exit();
    //         } else {
    //             echo "Erro ao deletar pessoa ou pessoa não encontrada.";
    //         }
    //     } catch (DatabaseException $e) {
    //         echo "Erro ao deletar pessoa: " . $e->getMessage();
    //     }
    // }

    /**
     * Função auxiliar para renderizar as views.
     *
     * @param string $viewName O nome da view (ex: 'pessoas/listar').
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
