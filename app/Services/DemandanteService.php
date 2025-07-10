<?php

namespace App\Services;

use App\DAO\DemandanteDAO;
use App\Models\Demandante;
use App\Exceptions\DatabaseException;
use InvalidArgumentException;

/**
 * Serviço para gerenciar operações relacionadas a Demandantes.
 */
class DemandanteService
{
    private $demandanteDAO;

    public function __construct()
    {
        $this->demandanteDAO = new DemandanteDAO();
    }

    /**
     * Obtém todos os demandantes ativos.
     *
     * @return array Um array de objetos Demandante.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterTodosDemandantesAtivos(): array
    {
        return $this->demandanteDAO->buscarTodosAtivos();
    }

    /**
     * Obtém um demandante pelo ID.
     *
     * @param int $id O ID do demandante.
     * @return Demandante|null O objeto Demandante ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterDemandantePorId(int $id): ?Demandante
    {
        return $this->demandanteDAO->buscarPorID($id);
    }

    /**
     * Busca demandantes por nome (LIKE).
     *
     * @param string $nome O nome a ser buscado.
     * @return array Um array de objetos Demandante.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarDemandantesPorNome(string $nome): array
    {
        if (empty($nome)) {
            throw new InvalidArgumentException("O nome para busca não pode ser vazio.");
        }
        return $this->demandanteDAO->buscarPorNome($nome);
    }

    /**
     * Salva um demandante. Se o ID for fornecido e o demandante existir, atualiza. Caso contrário, cria.
     *
     * @param array $dados Array associativo com os dados do demandante.
     * @return int O ID do demandante salvo/criado.
     * @throws InvalidArgumentException Se os dados forem inválidos.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function salvarDemandante(array $dados): int
    {
        // Validação básica
        if (empty($dados['nome'])) {
            throw new InvalidArgumentException("O nome do demandante é obrigatório.");
        }

        $demandante = new Demandante($dados);

        if ($demandante->id) {
            // Tenta atualizar
            $success = $this->demandanteDAO->atualizar($demandante);
            if (!$success) {
                throw new DatabaseException("Falha ao atualizar o demandante.");
            }
            return $demandante->id;
        } else {
            // Tenta criar
            $newId = $this->demandanteDAO->criar($demandante);
            if (!$newId) {
                throw new DatabaseException("Falha ao criar o demandante.");
            }
            return $newId;
        }
    }
}
