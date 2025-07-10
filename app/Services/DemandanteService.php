<?php

namespace App\Services;

use App\DAO\DemandanteDAO;
use App\Models\Demandante;
use App\Exceptions\DatabaseException;

class DemandanteService
{
    private $demandanteDAO;

    public function __construct()
    {
        $this->demandanteDAO = new DemandanteDAO();
    }

    public function obterTodosDemandantes(): array
    {
        return $this->demandanteDAO->buscarTodos();
    }

    public function obterDemandantePorId(int $id): ?Demandante
    {
        return $this->demandanteDAO->buscarPorID($id);
    }

    public function salvarDemandante(array $dados)
    {
        if (empty($dados['nome'])) {
            throw new \InvalidArgumentException("Nome do demandante é obrigatório.");
        }
        $demandante = new Demandante($dados);
        return $this->demandanteDAO->criar($demandante);
    }

    public function atualizarDemandante(int $id, array $dados): bool
    {
        $demandanteExistente = $this->demandanteDAO->buscarPorID($id);
        if (!$demandanteExistente) {
            throw new \InvalidArgumentException("Demandante com ID {$id} não encontrado para atualização.");
        }
        foreach ($dados as $key => $value) {
            if (property_exists($demandanteExistente, $key)) {
                $demandanteExistente->$key = $value;
            }
        }
        return $this->demandanteDAO->atualizar($demandanteExistente);
    }
}
