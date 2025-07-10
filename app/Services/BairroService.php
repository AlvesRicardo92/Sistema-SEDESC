<?php

namespace App\Services;

use App\DAO\BairroDAO;
use App\Models\Bairro;
use App\Exceptions\DatabaseException;
use InvalidArgumentException;

/**
 * Serviço para gerenciar operações relacionadas a Bairros.
 */
class BairroService
{
    private $bairroDAO;

    public function __construct()
    {
        $this->bairroDAO = new BairroDAO();
    }

    /**
     * Obtém todos os bairros ativos.
     *
     * @return array Um array de objetos Bairro.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterTodosBairrosAtivos(): array
    {
        return $this->bairroDAO->buscarTodosAtivos();
    }

    /**
     * Obtém um bairro pelo ID.
     *
     * @param int $id O ID do bairro.
     * @return Bairro|null O objeto Bairro ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterBairroPorId(int $id): ?Bairro
    {
        return $this->bairroDAO->buscarPorID($id);
    }

    /**
     * Busca bairros ativos por ID do território.
     *
     * @param int $territorioId O ID do território.
     * @return array Um array de objetos Bairro.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterBairrosAtivosPorTerritorioId(int $territorioId): array
    {
        return $this->bairroDAO->buscarAtivosPorTerritorioId($territorioId);
    }

    /**
     * Salva um bairro. Se o ID for fornecido e o bairro existir, atualiza. Caso contrário, cria.
     *
     * @param array $dados Array associativo com os dados do bairro.
     * @return int O ID do bairro salvo/criado.
     * @throws InvalidArgumentException Se os dados forem inválidos.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function salvarBairro(array $dados): int
    {
        // Validação básica
        if (empty($dados['nome'])) {
            throw new InvalidArgumentException("O nome do bairro é obrigatório.");
        }

        $bairro = new Bairro($dados);

        if ($bairro->id) {
            // Tenta atualizar
            $success = $this->bairroDAO->atualizar($bairro);
            if (!$success) {
                throw new DatabaseException("Falha ao atualizar o bairro.");
            }
            return $bairro->id;
        } else {
            // Tenta criar
            $newId = $this->bairroDAO->criar($bairro);
            if (!$newId) {
                throw new DatabaseException("Falha ao criar o bairro.");
            }
            return $newId;
        }
    }
}
