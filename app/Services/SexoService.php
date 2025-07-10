<?php

namespace App\Services;

use App\DAO\SexoDAO;
use App\Models\Sexo;
use App\Exceptions\DatabaseException;

class SexoService
{
    private $sexoDAO;

    public function __construct()
    {
        $this->sexoDAO = new SexoDAO();
    }

    public function obterTodosSexos(): array
    {
        return $this->sexoDAO->buscarTodos();
    }

    public function obterSexoPorId(int $id): ?Sexo
    {
        return $this->sexoDAO->buscarPorID($id);
    }

    public function salvarSexo(array $dados)
    {
        if (empty($dados['nome']) || empty($dados['sigla'])) {
            throw new \InvalidArgumentException("Nome e sigla do sexo são obrigatórios.");
        }
        $sexo = new Sexo($dados);
        return $this->sexoDAO->criar($sexo);
    }

    public function atualizarSexo(int $id, array $dados): bool
    {
        $sexoExistente = $this->sexoDAO->buscarPorID($id);
        if (!$sexoExistente) {
            throw new \InvalidArgumentException("Sexo com ID {$id} não encontrado para atualização.");
        }
        foreach ($dados as $key => $value) {
            if (property_exists($sexoExistente, $key)) {
                $sexoExistente->$key = $value;
            }
        }
        return $this->sexoDAO->atualizar($sexoExistente);
    }
}
