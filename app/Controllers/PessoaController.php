<?php

namespace App\Controllers;

use App\Services\PessoaService;
use App\Exceptions\DatabaseException;
use InvalidArgumentException;

class PessoaController extends BaseController
{
    private $pessoaService;

    public function __construct()
    {
        parent::__construct(); // Chama o construtor do BaseController
        $this->pessoaService = new PessoaService();
    }

    /**
     * Retorna pessoas por nome (LIKE) em formato JSON para autocomplete.
     */
    public function searchByNameJson(): void
    {
        header('Content-Type: application/json');
        $nome = $_GET['nome'] ?? '';

        if (strlen($nome) < 3) {
            $this->renderJson(['success' => false, 'message' => 'Digite pelo menos 3 caracteres para buscar.'], 400);
            return;
        }

        try {
            $pessoas = $this->pessoaService->buscarPessoasPorNome($nome);
            $formattedPessoas = [];
            foreach ($pessoas as $pessoa) {
                $formattedPessoas[] = [
                    'id' => $pessoa->id,
                    'nome' => $pessoa->nome,
                    'data_nascimento' => $pessoa->data_nascimento,
                    'id_sexo' => $pessoa->id_sexo
                ];
            }
            $this->renderJson($formattedPessoas);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao buscar pessoas: ' . $e->getMessage()], 500);
        } catch (InvalidArgumentException $e) {
            $this->renderJson(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // Métodos para CRUD de Pessoa (se necessário, podem ser adicionados aqui)
    // Ex: listar, mostrar, criar, editar, atualizar
    public function listar(): void
    {
        try {
            $pessoas = $this->pessoaService->obterTodasPessoasAtivas();
            $this->render('pessoas/listar', ['pessoas' => $pessoas]);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar pessoas: ' . $e->getMessage()], 500);
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $pessoa = $this->pessoaService->obterPessoaPorId($id);
            if ($pessoa) {
                $this->render('pessoas/detalhe', ['pessoa' => $pessoa]);
            } else {
                $this->render('404', [], 404);
            }
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar detalhes da pessoa: ' . $e->getMessage()], 500);
        }
    }
    // ... outros métodos CRUD se a UI para Pessoa for separada
}
