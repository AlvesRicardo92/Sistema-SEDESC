<?php

namespace App\Services;

use App\DAO\BairroDAO;
use App\Models\Bairro;
use App\Exceptions\DatabaseException;

class BairroService
{
    private $bairroDAO;

    public function __construct()
    {
        $this->bairroDAO = new BairroDAO();
    }

    public function obterTodosBairros(): array
    {
        return $this->bairroDAO->buscarTodos();
    }

    public function obterBairroPorId(int $id): ?Bairro
    {
        return $this->bairroDAO->buscarPorID($id);
    }

    public function salvarBairro(array $dados)
    {
        if (empty($dados['nome'])) {
            throw new \InvalidArgumentException("Nome do bairro é obrigatório.");
        }
        $bairro = new Bairro($dados);
        return $this->bairroDAO->criar($bairro);
    }

    public function atualizarBairro(int $id, array $dados): bool
    {
        $bairroExistente = $this->bairroDAO->buscarPorID($id);
        if (!$bairroExistente) {
            throw new \InvalidArgumentException("Bairro com ID {$id} não encontrado para atualização.");
        }
        foreach ($dados as $key => $value) {
            if (property_exists($bairroExistente, $key)) {
                $bairroExistente->$key = $value;
            }
        }
        return $this->bairroDAO->atualizar($bairroExistente);
    }
}
