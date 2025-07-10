<?php

namespace App\Services;

use App\DAO\SexoDAO;
use App\Models\Sexo;
use App\Exceptions\DatabaseException;
use InvalidArgumentException;

/**
 * Serviço para gerenciar operações relacionadas a Sexos.
 */
class SexoService
{
    private $sexoDAO;

    public function __construct()
    {
        $this->sexoDAO = new SexoDAO();
    }

    /**
     * Obtém todos os sexos.
     *
     * @return array Um array de objetos Sexo.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterTodosSexos(): array
    {
        return $this->sexoDAO->buscarTodos();
    }

    /**
     * Obtém um sexo pelo ID.
     *
     * @param int $id O ID do sexo.
     * @return Sexo|null O objeto Sexo ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterSexoPorId(int $id): ?Sexo
    {
        return $this->sexoDAO->buscarPorID($id);
    }
}
