<?php

namespace App\Interfaces;

/**
 * Interface para operações básicas de Data Access Object (DAO).
 */
interface BaseDAOInterface
{
    /**
     * Busca todos os registros.
     *
     * @return array Um array de objetos do modelo correspondente.
     */
    public function buscarTodos(): array;

    /**
     * Busca todos os registros que estão ativos.
     *
     * @return array Um array de objetos do modelo correspondente.
     */
    public function buscarTodosAtivos(): array;

    /**
     * Busca um registro pelo seu ID.
     *
     * @param int $id O ID do registro a ser buscado.
     * @return object|null O objeto do modelo correspondente ou null se não encontrado.
     */
    public function buscarPorID(int $id): ?object;

    /**
     * Busca registros pelo nome, utilizando a palavra-chave LIKE.
     *
     * @param string $nome O nome ou parte do nome a ser buscado.
     * @return array Um array de objetos do modelo correspondente.
     */
    public function buscarPorNome(string $nome): array;

    /**
     * Cria um novo registro no banco de dados.
     *
     * @param object $model O objeto do modelo a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     */
    public function criar(object $model);

    /**
     * Atualiza um registro existente no banco de dados.
     *
     * @param object $model O objeto do modelo a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     */
    public function atualizar(object $model): bool;
}
