<?php

namespace App\Controllers;

use App\Services\SexoService;
use App\Exceptions\DatabaseException;

class SexoController extends BaseController
{
    private $sexoService;

    public function __construct()
    {
        parent::__construct(); // Chama o construtor do BaseController
        $this->sexoService = new SexoService();
    }

    /**
     * Retorna todos os sexos em formato JSON.
     */
    public function listarJson(): void
    {
        header('Content-Type: application/json');
        try {
            $sexos = $this->sexoService->obterTodosSexos();
            $formattedSexos = [];
            foreach ($sexos as $sexo) {
                $formattedSexos[] = [
                    'id' => $sexo->id,
                    'nome' => $sexo->nome,
                    'sigla' => $sexo->sigla
                ];
            }
            $this->renderJson($formattedSexos);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar sexos: ' . $e->getMessage()], 500);
        }
    }

    // Métodos para CRUD de Sexo (se necessário, podem ser adicionados aqui)
    // Ex: listar, mostrar, criar, editar, atualizar
    public function listar(): void
    {
        try {
            $sexos = $this->sexoService->obterTodosSexos();
            $this->render('sexos/listar', ['sexos' => $sexos]);
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar sexos: ' . $e->getMessage()], 500);
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $sexo = $this->sexoService->obterSexoPorId($id);
            if ($sexo) {
                $this->render('sexos/detalhe', ['sexo' => $sexo]);
            } else {
                $this->render('404', [], 404);
            }
        } catch (DatabaseException $e) {
            $this->renderJson(['success' => false, 'message' => 'Erro ao carregar detalhes do sexo: ' . $e->getMessage()], 500);
        }
    }
    // ... outros métodos CRUD se a UI para Sexo for separada
}
