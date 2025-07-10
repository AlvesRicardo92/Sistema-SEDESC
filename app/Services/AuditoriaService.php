<?php

namespace App\Services;

use App\DAO\AuditoriaDAO;
use App\Models\Auditoria;
use App\Exceptions\DatabaseException;

class AuditoriaService
{
    private $auditoriaDAO;

    public function __construct()
    {
        $this->auditoriaDAO = new AuditoriaDAO();
    }

    public function obterTodosAuditorias(): array
    {
        return $this->auditoriaDAO->buscarTodos();
    }

    public function obterAuditoriaPorId(int $id): ?Auditoria
    {
        return $this->auditoriaDAO->buscarPorID($id);
    }

    // Métodos 'criar' e 'atualizar' não são implementados, pois a auditoria é via trigger.
    // A interface exige, mas a lógica de negócio dita que não são chamados diretamente.
    public function criar(array $dados)
    {
        throw new \BadMethodCallException("A criação de registros de auditoria é gerenciada por triggers do banco de dados e não deve ser chamada diretamente via Service.");
    }

    public function atualizar(int $id, array $dados): bool
    {
        throw new \BadMethodCallException("A atualização de registros de auditoria não é suportada via Service.");
    }
}
