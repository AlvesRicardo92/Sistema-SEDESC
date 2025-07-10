<?php

namespace App\Controllers;

use App\Services\BairroService;
use App\Exceptions\DatabaseException;
use InvalidArgumentException;

class BairroController extends BaseController
{
    private $bairroService;

    public function __construct()
    {
        parent::__construct(); // Chama o construtor do BaseController
        $this->bairroService = new BairroService();
    }

    /**
     * Retorna todos os bairros ativos em formato JSON.
     */
    public function listarAtivosJson(): void
    {
        header('Content-Type: application/json');
        try {
            $bairros = $this->bairroService->obterTodosBairrosAtivos();
            $formattedBairros = [];
            foreach ($bairros as $bairro) {
                $formattedBairros[] = [
                    'id' => $bairro->id,
                    'nome' => $bairro->nome,
                    'territorio_id' => $bairro->territorio_id
                ];
            }
            $this->renderJson($formattedBairros);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar bairros: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Retorna bairros ativos de um território específico em formato JSON.
     */
    public function listarPorTerritorioJson(): void
    {
        header('Content-Type: application/json');
        $territorioId = $_GET['id_territorio'] ?? null;

        if (!is_numeric($territorioId) || $territorioId <= 0) {
            $this->renderJson(['success' => false, 'message' => 'ID do território inválido.'], 400);
            return;
        }

        try {
            $bairros = $this->bairroService->obterBairrosAtivosPorTerritorioId((int)$territorioId);
            $formattedBairros = [];
            foreach ($bairros as $bairro) {
                $formattedBairros[] = [
                    'id' => $bairro->id,
                    'nome' => $bairro->nome,
                    'territorio_id' => $bairro->territorio_id
                ];
            }
            $this->renderJson($formattedBairros);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar bairros por território: ' . $e->getMessage()], 500);
        }
    }

    // Métodos para CRUD de Bairro (se necessário, podem ser adicionados aqui)
    // Ex: listar, mostrar, criar, editar, atualizar
    public function listar(): void
    {
        try {
            $bairros = $this->bairroService->obterTodosBairrosAtivos();
            $this->render('bairros/listar', ['bairros' => $bairros]);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar bairros: ' . $e->getMessage()], 500);
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $bairro = $this->bairroService->obterBairroPorId($id);
            if ($bairro) {
                $this->render('bairros/detalhe', ['bairro' => $bairro]);
            } else {
                $this->render('404', [], 404);
            }
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar detalhes do bairro: ' . $e->getMessage()], 500);
        }
    }
    // ... outros métodos CRUD se a UI para Bairro for separada
}
