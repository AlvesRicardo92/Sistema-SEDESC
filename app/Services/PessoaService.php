<?php

namespace App\Services;

use App\DAO\PessoaDAO;
use App\Models\Pessoa;
use App\Exceptions\DatabaseException;

class PessoaService
{
    private $pessoaDAO;

    public function __construct()
    {
        $this->pessoaDAO = new PessoaDAO();
    }

    public function obterTodasPessoas(): array
    {
        return $this->pessoaDAO->buscarTodos();
    }

    public function obterPessoaPorId(int $id): ?Pessoa
    {
        return $this->pessoaDAO->buscarPorID($id);
    }

    public function salvarPessoa(array $dados)
    {
        if (empty($dados['nome'])) {
            throw new \InvalidArgumentException("Nome da pessoa é obrigatório.");
        }
        $pessoa = new Pessoa($dados);
        return $this->pessoaDAO->criar($pessoa);
    }

    public function atualizarPessoa(int $id, array $dados): bool
    {
        $pessoaExistente = $this->pessoaDAO->buscarPorID($id);
        if (!$pessoaExistente) {
            throw new \InvalidArgumentException("Pessoa com ID {$id} não encontrada para atualização.");
        }
        foreach ($dados as $key => $value) {
            if (property_exists($pessoaExistente, $key)) {
                $pessoaExistente->$key = $value;
            }
        }
        return $this->pessoaDAO->atualizar($pessoaExistente);
    }
}
