<?php

namespace App\Services;

use App\DAO\ProcedimentoDAO;
use App\Models\Procedimento;
use App\Exceptions\DatabaseException;

/**
 * Camada de Serviço para a entidade Procedimento.
 * Contém a lógica de negócio e orquestra as operações de dados através do DAO.
 */
class ProcedimentoService
{
    private $procedimentoDAO;

    /**
     * Construtor do ProcedimentoService.
     * Injeta a dependência do ProcedimentoDAO.
     */
    public function __construct()
    {
        $this->procedimentoDAO = new ProcedimentoDAO();
    }

    /**
     * Obtém todos os procedimentos.
     *
     * @return array Um array de objetos Procedimento.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterTodosProcedimentos(): array
    {
        // Aqui poderia haver lógica de negócio adicional, como filtragem complexa,
        // validação de permissões antes de buscar todos, etc.
        return $this->procedimentoDAO->buscarTodos();
    }

    /**
     * Obtém um procedimento pelo seu ID.
     *
     * @param int $id O ID do procedimento.
     * @return Procedimento|null O objeto Procedimento ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterProcedimentoPorId(int $id): ?Procedimento
    {
        // Lógica de negócio: talvez verificar se o usuário tem permissão para ver este procedimento.
        return $this->procedimentoDAO->buscarPorID($id);
    }

    /**
     * Salva um novo procedimento no banco de dados.
     *
     * @param array $dados Os dados do novo procedimento.
     * @return int|false O ID do novo procedimento ou false em caso de falha.
     * @throws \InvalidArgumentException Se os dados forem inválidos.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function salvarProcedimento(array $dados)
    {
        // Exemplo de lógica de validação de negócio simples
        if (empty($dados['numero_procedimento']) || empty($dados['ano_procedimento']) || empty($dados['id_territorio'])) {
            throw new \InvalidArgumentException("Dados essenciais para o procedimento estão faltando.");
        }

        // Você pode criar uma instância do Modelo aqui e passá-la para o DAO
        $procedimento = new Procedimento($dados);
        // Definir valores padrão ou lógicas de negócio antes de salvar
        $procedimento->ativo = $dados['ativo'] ?? 1;
        $procedimento->migrado = $dados['migrado'] ?? 0;
        $procedimento->data_criacao = $dados['data_criacao'] ?? date('Y-m-d');
        $procedimento->hora_criacao = $dados['hora_criacao'] ?? date('H:i:s');
        $procedimento->id_usuario_criacao = $dados['id_usuario_criacao'] ?? null;
        $procedimento->id_usuario_atualizacao = $dados['id_usuario_atualizacao'] ?? null;
        $procedimento->data_hora_atualizacao = $dados['data_hora_atualizacao'] ?? null;


        $newId = $this->procedimentoDAO->criar($procedimento);

        // Exemplo: Se houvesse um AuditoriaDAO, você poderia registrar a ação aqui
        // $auditoriaDAO = new AuditoriaDAO();
        // $auditoriaDAO->criarRegistro('procedimentos', 'INSERT', null, json_encode($procedimento->toArray()));

        return $newId;
    }

    /**
     * Atualiza um procedimento existente.
     *
     * @param int $id O ID do procedimento a ser atualizado.
     * @param array $dados Os novos dados do procedimento.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws \InvalidArgumentException Se os dados forem inválidos ou o ID não for encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizarProcedimento(int $id, array $dados): bool
    {
        $procedimentoExistente = $this->procedimentoDAO->buscarPorID($id);
        if (!$procedimentoExistente) {
            throw new \InvalidArgumentException("Procedimento com ID {$id} não encontrado para atualização.");
        }

        // Atualiza as propriedades do objeto existente com os novos dados
        foreach ($dados as $key => $value) {
            // Garante que só propriedades válidas do modelo sejam atualizadas
            if (property_exists($procedimentoExistente, $key)) {
                $procedimentoExistente->$key = $value;
            }
        }
        // Exemplo de lógica de negócio: garantir que um procedimento não pode ser desativado
        // se tiver dependências ativas, etc. (não implementado aqui, apenas um exemplo)
        $procedimentoExistente->data_hora_atualizacao = $dados['data_hora_atualizacao'] ?? date('Y-m-d H:i:s');
        $procedimentoExistente->id_usuario_atualizacao = $dados['id_usuario_atualizacao'] ?? null;


        $success = $this->procedimentoDAO->atualizar($procedimentoExistente);

        // Exemplo: Se houvesse um AuditoriaDAO, você poderia registrar a ação aqui
        // $auditoriaDAO = new AuditoriaDAO();
        // $auditoriaDAO->criarRegistro('procedimentos', 'UPDATE', json_encode($oldData), json_encode($procedimentoExistente->toArray()));

        return $success;
    }
}
