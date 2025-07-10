<?php

namespace App\Services;

use App\DAO\TerritorioDAO;
use App\Models\Territorio;
use App\Exceptions\DatabaseException;

class TerritorioService
{
    private $territorioDAO;

    public function __construct()
    {
        $this->territorioDAO = new TerritorioDAO();
    }

    public function obterTodosTerritorios(): array
    {
        return $this->territorioDAO->buscarTodos();
    }

    public function obterTerritorioPorId(int $id): ?Territorio
    {
        return $this->territorioDAO->buscarPorID($id);
    }

    public function salvarTerritorio(array $dados)
    {
        if (empty($dados['nome'])) {
            throw new \InvalidArgumentException("Nome do território é obrigatório.");
        }
        $territorio = new Territorio($dados);
        return $this->territorioDAO->criar($territorio);
    }

    public function atualizarTerritorio(int $id, array $dados): bool
    {
        $territorioExistente = $this->territorioDAO->buscarPorID($id);
        if (!$territorioExistente) {
            throw new \InvalidArgumentException("Território com ID {$id} não encontrado para atualização.");
        }
        foreach ($dados as $key => $value) {
            if (property_exists($territorioExistente, $key)) {
                $territorioExistente->$key = $value;
            }
        }
        return $this->territorioDAO->atualizar($territorioExistente);
    }
}
