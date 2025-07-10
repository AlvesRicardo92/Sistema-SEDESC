<?php

namespace App\Controllers;

use App\Services\MotivoMigracaoService;
use App\Exceptions\DatabaseException;

class MotivoMigracaoController
{
    private $motivoMigracaoService;

    public function __construct()
    {
        $this->motivoMigracaoService = new MotivoMigracaoService();
    }

    public function listar(): void
    {
        try {
            $motivos = $this->motivoMigracaoService->obterTodosMotivosMigracao();
            $this->render('motivos_migracao/listar', ['motivos' => $motivos]);
        } catch (DatabaseException $e) {
            echo "Erro ao carregar motivos de migração: " . $e->getMessage();
        }
    }

    public function mostrar(int $id): void
    {
        try {
            $motivo = $this->motivoMigracaoService->obterMotivoMigracaoPorId($id);
            if ($motivo) {
                $this->render('motivos_migracao/detalhe', ['motivo' => $motivo]);
            } else {
                echo "Motivo de migração não encontrado.";
            }
        } catch (DatabaseException $e) {
            echo "Erro ao carregar detalhes do motivo de migração: " . $e->getMessage();
        }
    }

    public function criar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $dados = $_POST;
                $newId = $this->motivoMigracaoService->salvarMotivoMigracao($dados);
                if ($newId) {
                    echo "Motivo de migração criado com sucesso! ID: " . $newId;
                } else {
                    echo "Falha ao criar motivo de migração.";
                }
            } catch (\InvalidArgumentException $e) {
                echo "Erro de validação: " . $e->getMessage();
            } catch (DatabaseException $e) {
                echo "Erro ao criar motivo de migração no banco de dados: " . $e->getMessage();
            }
        } else {
            echo "Formulário para criar novo motivo de migração (método GET).";
        }
    }

    public function atualizar(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $dados = $_POST;
                $success = $this->motivoMigracaoService->atualizarMotivoMigracao($id, $dados);
                if ($success) {
                    echo "Motivo de migração ID {$id} atualizado com sucesso!";
                } else {
                    echo "Falha ao atualizar motivo de migração ID {$id}.";
                }
            } catch (\InvalidArgumentException $e) {
                echo "Erro de validação: " . $e->getMessage();
            } catch (DatabaseException $e) {
                echo "Erro ao atualizar motivo de migração no banco de dados: " . $e->getMessage();
            }
        } else {
            try {
                $motivo = $this->motivoMigracaoService->obterMotivoMigracaoPorId($id);
                if ($motivo) {
                    echo "Formulário para editar motivo de migração ID {$id} (método GET).";
                } else {
                    echo "Motivo de migração não encontrado para edição.";
                }
            } catch (DatabaseException $e) {
                echo "Erro ao carregar motivo de migração para edição: " . $e->getMessage();
            }
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
