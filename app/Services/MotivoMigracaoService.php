<?php

namespace App\Services;

use App\DAO\MotivoMigracaoDAO;
use App\Models\MotivoMigracao;
use App\Exceptions\DatabaseException;

class MotivoMigracaoService
{
    private $motivoMigracaoDAO;

    public function __construct()
    {
        $this->motivoMigracaoDAO = new MotivoMigracaoDAO();
    }

    public function obterTodosMotivosMigracao(): array
    {
        return $this->motivoMigracaoDAO->buscarTodos();
    }

    public function obterMotivoMigracaoPorId(int $id): ?MotivoMigracao
    {
        return $this->motivoMigracaoDAO->buscarPorID($id);
    }

    public function salvarMotivoMigracao(array $dados)
    {
        if (empty($dados['nome'])) {
            throw new \InvalidArgumentException("Nome do motivo de migração é obrigatório.");
        }
        $motivo = new MotivoMigracao($dados);
        return $this->motivoMigracaoDAO->criar($motivo);
    }

    public function atualizarMotivoMigracao(int $id, array $dados): bool
    {
        $motivoExistente = $this->motivoMigracaoDAO->buscarPorID($id);
        if (!$motivoExistente) {
            throw new \InvalidArgumentException("Motivo de migração com ID {$id} não encontrado para atualização.");
        }
        foreach ($dados as $key => $value) {
            if (property_exists($motivoExistente, $key)) {
                $motivoExistente->$key = $value;
            }
        }
        return $this->motivoMigracaoDAO->atualizar($motivoExistente);
    }
}
