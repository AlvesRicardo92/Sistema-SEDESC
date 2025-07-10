<?php

namespace App\Services;

use App\DAO\PessoaDAO;
use App\Models\Pessoa;
use App\Exceptions\DatabaseException;
use InvalidArgumentException;

/**
 * Serviço para gerenciar operações relacionadas a Pessoas.
 */
class PessoaService
{
    private $pessoaDAO;

    public function __construct()
    {
        $this->pessoaDAO = new PessoaDAO();
    }

    /**
     * Obtém todas as pessoas ativas.
     *
     * @return array Um array de objetos Pessoa.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterTodasPessoasAtivas(): array
    {
        return $this->pessoaDAO->buscarTodosAtivos();
    }

    /**
     * Obtém uma pessoa pelo ID.
     *
     * @param int $id O ID da pessoa.
     * @return Pessoa|null O objeto Pessoa ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterPessoaPorId(int $id): ?Pessoa
    {
        return $this->pessoaDAO->buscarPorID($id);
    }

    /**
     * Busca pessoas por nome (LIKE).
     *
     * @param string $nome O nome a ser buscado.
     * @return array Um array de objetos Pessoa.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPessoasPorNome(string $nome): array
    {
        if (empty($nome)) {
            throw new InvalidArgumentException("O nome para busca não pode ser vazio.");
        }
        return $this->pessoaDAO->buscarPorNome($nome);
    }

    /**
     * Salva uma pessoa. Se o ID for fornecido e a pessoa existir, atualiza. Caso contrário, cria.
     *
     * @param array $dados Array associativo com os dados da pessoa.
     * @return int O ID da pessoa salva/criada.
     * @throws InvalidArgumentException Se os dados forem inválidos.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function salvarPessoa(array $dados): int
    {
        // Validação básica
        if (empty($dados['nome'])) {
            throw new InvalidArgumentException("O nome da pessoa é obrigatório.");
        }

        $pessoa = new Pessoa($dados);

        if ($pessoa->id) {
            // Tenta atualizar
            $success = $this->pessoaDAO->atualizar($pessoa);
            if (!$success) {
                throw new DatabaseException("Falha ao atualizar a pessoa.");
            }
            return $pessoa->id;
        } else {
            // Tenta criar
            // Verifica se já existe uma pessoa com o mesmo nome e data de nascimento
            if (!empty($pessoa->nome) && !empty($pessoa->data_nascimento)) {
                $existingPessoa = $this->pessoaDAO->buscarPorNomeEDataNascimento($pessoa->nome, $pessoa->data_nascimento);
                if ($existingPessoa) {
                    // Se a pessoa já existe, retorna o ID da pessoa existente
                    return $existingPessoa->id;
                }
            }

            $newId = $this->pessoaDAO->criar($pessoa);
            if (!$newId) {
                throw new DatabaseException("Falha ao criar a pessoa.");
            }
            return $newId;
        }
    }
}
