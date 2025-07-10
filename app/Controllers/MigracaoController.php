<?php

namespace App\Controllers;

use App\Services\MigracaoService;
use App\Models\Migracao; // Assumindo que você tem um modelo Migracao
use App\Exceptions\DatabaseException; // Assumindo que você tem uma exceção de banco de dados

class MigracaoController
{
    private $migracaoService;

    public function __construct()
    {
        $this->migracaoService = new MigracaoService();
    }

    public function listar(): void
    {
        try {
            $migracoes = $this->migracaoService->obterTodasMigracoes();
            $this->render('migracoes/listar', ['migracoes' => $migracoes]);
        } catch (DatabaseException $e) {
            echo "Erro ao carregar migrações: " . $e->getMessage();
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $migracao = $this->migracaoService->obterMigracaoPorId($id);
            if ($migracao) {
                $this->render('migracoes/detalhe', ['migracao' => $migracao]);
            } else {
                echo "Migração não encontrada.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar detalhes da migração: " . $e->getMessage();
        }
    }

    public function criar(): void
    {
        // Exibe o formulário de criação
        $this->render('migracoes/criar');
    }

    public function salvar(): void
    {
        // Processa os dados do POST
        $dados = $_POST; // Exemplo simples, em um projeto real faria validação e sanitização
        try {
            $this->migracaoService->salvarMigracao($dados);
            header('Location: /migracoes/listar'); // Redireciona para a lista após salvar
            exit();
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao salvar migração: " . $e->getMessage();
        }
    }

    public function editar(int $id): void
    {
        try {
            $migracao = $this->migracaoService->obterMigracaoPorId($id);
            if ($migracao) {
                $this->render('migracoes/editar', ['migracao' => $migracao]);
            } else {
                echo "Migração não encontrada para edição.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar migração para edição: " . $e->getMessage();
        }
    }

    public function atualizar(int $id): void
    {
        // Processa os dados do POST
        $dados = $_POST; // Exemplo simples
        try {
            $this->migracaoService->atualizarMigracao($id, $dados);
            header('Location: /migracoes/listar'); // Redireciona para a lista após atualizar
            exit();
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao atualizar migração: " . $e->getMessage();
        }
    }

    // O método 'deletar' não foi solicitado na interface BaseDAOInterface,
    // mas pode ser adicionado se necessário no Service e DAO.
    // public function deletar(int $id): void
    // {
    //     try {
    //         if ($this->migracaoService->deletarMigracao($id)) {
    //             header('Location: /migracoes/listar');
    //             exit();
    //         } else {
    //             echo "Erro ao deletar migração ou migração não encontrada.";
    //         }
    //     } catch (DatabaseException $e) {
    //         echo "Erro ao deletar migração: " . $e->getMessage();
    //     }
    // }

    /**
     * Função auxiliar para renderizar as views.
     *
     * @param string $viewName O nome da view (ex: 'migracoes/listar').
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
