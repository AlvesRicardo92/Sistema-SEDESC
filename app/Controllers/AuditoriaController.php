<?php

namespace App\Controllers;

use App\Services\AuditoriaService;
use App\Exceptions\DatabaseException;

class AuditoriaController
{
    private $auditoriaService;

    public function __construct()
    {
        $this->auditoriaService = new AuditoriaService();
    }

    public function listar(): void
    {
        try {
            $auditorias = $this->auditoriaService->obterTodosAuditorias();
            $this->render('auditorias/listar', ['auditorias' => $auditorias]);
        } catch (DatabaseException $e) {
            echo "Erro ao carregar auditorias: " . $e->getMessage();
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $auditoria = $this->auditoriaService->obterAuditoriaPorId($id);
            if ($auditoria) {
                $this->render('auditorias/detalhe', ['auditoria' => $auditoria]);
            } else {
                echo "Registro de auditoria não encontrado.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar detalhes da auditoria: " . $e->getMessage();
        }
    }

    // Métodos 'criar' e 'atualizar' não são implementados, pois a auditoria é via trigger.
    public function criar(): void
    {
        echo "A criação de registros de auditoria é gerenciada por triggers do banco de dados.";
    }

    public function atualizar(int $id): void
    {
        echo "A atualização de registros de auditoria não é suportada.";
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
