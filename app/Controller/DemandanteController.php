<?php

namespace App\Controllers;

use App\Services\DemandanteService;
use App\Exceptions\DatabaseException;
use InvalidArgumentException;

class DemandanteController extends BaseController
{
    private $demandanteService;

    public function __construct()
    {
        parent::__construct(); // Chama o construtor do BaseController
        $this->demandanteService = new DemandanteService();
    }

    /**
     * Retorna demandantes por nome (LIKE) em formato JSON para autocomplete.
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
            $demandantes = $this->demandanteService->buscarDemandantesPorNome($nome);
            $formattedDemandantes = [];
            foreach ($demandantes as $demandante) {
                $formattedDemandantes[] = [
                    'id' => $demandante->id,
                    'nome' => $demandante->nome
                ];
            }
            $this->renderJson($formattedDemandantes);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao buscar demandantes: ' . $e->getMessage()], 500);
        } catch (InvalidArgumentException $e) {
            $this->renderJson(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // Métodos para CRUD de Demandante (se necessário, podem ser adicionados aqui)
    // Ex: listar, mostrar, criar, editar, atualizar
    public function listar(): void
    {
        try {
            $demandantes = $this->demandanteService->obterTodosDemandantesAtivos();
            $this->render('demandantes/listar', ['demandantes' => $demandantes]);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar demandantes: ' . $e->getMessage()], 500);
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $demandante = $this->demandanteService->obterDemandantePorId($id);
            if ($demandante) {
                $this->render('demandantes/detalhe', ['demandante' => $demandante]);
            } else {
                $this->render('404', [], 404);
            }
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar detalhes do demandante: ' . $e->getMessage()], 500);
        }
    }
    // ... outros métodos CRUD se a UI para Demandante for separada
}