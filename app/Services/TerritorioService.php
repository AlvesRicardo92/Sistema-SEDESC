<?php

namespace App\Services;

use App\DAO\TerritorioDAO;
use App\Models\Territorio;
use App\Exceptions\DatabaseException;
use InvalidArgumentException;

/**
 * Serviço para gerenciar operações relacionadas a Territórios.
 */
class TerritorioService
{
    private $territorioDAO;

    public function __construct()
    {
        $this->territorioDAO = new TerritorioDAO();
    }

    /**
     * Obtém todos os territórios.
     *
     * @return array Um array de objetos Territorio.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterTodosTerritorios(): array
    {
        return $this->territorioDAO->buscarTodos();
    }

    /**
     * Obtém um território pelo ID.
     *
     * @param int $id O ID do território.
     * @return Territorio|null O objeto Territorio ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterTerritorioPorId(int $id): ?Territorio
    {
        return $this->territorioDAO->buscarPorID($id);
    }
}
