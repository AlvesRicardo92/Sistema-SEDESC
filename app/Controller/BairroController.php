<?php

namespace App\Controllers;

use App\Services\BairroService;
use App\Models\Bairro; // Assumindo que você tem um modelo Bairro
use App\Exceptions\DatabaseException; // Assumindo que você tem uma exceção de banco de dados

class BairroController
{
    private $bairroService;

    public function __construct()
    {
        $this->bairroService = new BairroService();
    }

    public function listar(): void
    {
        try {
            $bairros = $this->bairroService->obterTodosBairros();
            $this->render('bairros/listar', ['bairros' => $bairros]);
        } catch (DatabaseException $e) {
            echo "Erro ao carregar bairros: " . $e->getMessage();
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $bairro = $this->bairroService->obterBairroPorId($id);
            if ($bairro) {
                $this->render('bairros/detalhe', ['bairro' => $bairro]);
            } else {
                echo "Bairro não encontrado.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar detalhes do bairro: " . $e->getMessage();
        }
    }

    public function criar(): void
    {
        $this->render('bairros/criar');
    }

    public function salvar(): void
    {
        // Aqui você processaria os dados do POST
        $dados = $_POST; // Exemplo simples, em um projeto real faria validação e sanitização
        try {
            $this->bairroService->salvarBairro($dados);
            header('Location: /bairros'); // Redireciona para a lista após salvar
            exit();
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao salvar bairro: " . $e->getMessage();
        }
    }

    public function editar(int $id): void
    {
        try {
            $bairro = $this->bairroService->obterBairroPorId($id);
            if ($bairro) {
                $this->render('bairros/editar', ['bairro' => $bairro]);
            } else {
                echo "Bairro não encontrado para edição.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar bairro para edição: " . $e->getMessage();
        }
    }

    public function atualizar(int $id): void
    {
        // Aqui você processaria os dados do POST
        $dados = $_POST; // Exemplo simples
        try {
            $this->bairroService->atualizarBairro($id, $dados);
            header('Location: /bairros'); // Redireciona para a lista após atualizar
            exit();
        } catch (\InvalidArgumentException $e) {
            echo "Erro de validação: " . $e->getMessage();
        } catch (DatabaseException $e) {
            echo "Erro ao atualizar bairro: " . $e->getMessage();
        }
    }

    public function deletar(int $id): void
    {
        try {
            if ($this->bairroService->deletarBairro($id)) {
                header('Location: /bairros'); // Redireciona para a lista após deletar
                exit();
            } else {
                echo "Erro ao deletar bairro ou bairro não encontrado.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao deletar bairro: " . $e->getMessage();
        }
    }

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
    