<?php

namespace App\Services;

use App\DAO\MigracaoDAO;
use App\Models\Migracao;
use App\Exceptions\DatabaseException;

class MigracaoService
{
    private $migracaoDAO;

    public function __construct()
    {
        $this->migracaoDAO = new MigracaoDAO();
    }

    public function obterTodasMigracoes(): array
    {
        return $this->migracaoDAO->buscarTodos();
    }

    public function obterMigracaoPorId(int $id): ?Migracao
    {
        return $this->migracaoDAO->buscarPorID($id);
    }

    public function salvarMigracao(array $dados)
    {
        if (empty($dados['numero_antigo']) || empty($dados['ano_antigo']) || empty($dados['numero_novo']) || empty($dados['ano_novo'])) {
            throw new \InvalidArgumentException("Dados essenciais para a migração estão faltando.");
        }
        $migracao = new Migracao($dados);
        return $this->migracaoDAO->criar($migracao);
    }

    public function atualizarMigracao(int $id, array $dados): bool
    {
        $migracaoExistente = $this->migracaoDAO->buscarPorID($id);
        if (!$migracaoExistente) {
            throw new \InvalidArgumentException("Migração com ID {$id} não encontrada para atualização.");
        }
        foreach ($dados as $key => $value) {
            if (property_exists($migracaoExistente, $key)) {
                $migracaoExistente->$key = $value;
            }
        }
        return $this->migracaoDAO->atualizar($migracaoExistente);
    }
}
