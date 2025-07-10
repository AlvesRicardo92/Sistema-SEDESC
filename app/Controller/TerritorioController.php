<?php

namespace App\Controllers;

use App\Services\TerritorioService;
use App\Exceptions\DatabaseException;

class TerritorioController extends BaseController
{
    private $territorioService;

    public function __construct()
    {
        parent::__construct(); // Chama o construtor do BaseController
        $this->territorioService = new TerritorioService();
    }

    /**
     * Retorna todos os territórios em formato JSON.
     */
    public function listarJson(): void
    {
        header('Content-Type: application/json');
        try {
            $territorios = $this->territorioService->obterTodosTerritorios();
            $formattedTerritorios = [];
            foreach ($territorios as $territorio) {
                $formattedTerritorios[] = [
                    'id' => $territorio->id,
                    'nome' => $territorio->nome
                ];
            }
            $this->renderJson($formattedTerritorios);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar territórios: ' . $e->getMessage()], 500);
        }
    }

    // Métodos para CRUD de Território (se necessário, podem ser adicionados aqui)
    // Ex: listar, mostrar, criar, editar, atualizar
    public function listar(): void
    {
        try {
            $territorios = $this->territorioService->obterTodosTerritorios();
            $this->render('territorios/listar', ['territorios' => $territorios]);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar territórios: ' . $e->getMessage()], 500);
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $territorio = $this->territorioService->obterTerritorioPorId($id);
            if ($territorio) {
                $this->render('territorios/detalhe', ['territorio' => $territorio]);
            } else {
                $this->render('404', [], 404);
            }
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar detalhes do território: ' . $e->getMessage()], 500);
        }
    }
    // ... outros métodos CRUD se a UI para Território for separada
}
