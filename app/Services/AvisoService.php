<?php

namespace App\Services;

use App\DAO\AvisoDAO;
use App\Models\Aviso;
use App\Exceptions\DatabaseException;

/**
 * Camada de Serviço para a entidade Aviso.
 * Contém a lógica de negócio e orquestra as operações de dados através do DAO.
 */
class AvisoService
{
    private $avisoDAO;

    /**
     * Construtor do AvisoService.
     * Injeta a dependência do AvisoDAO.
     */
    public function __construct()
    {
        $this->avisoDAO = new AvisoDAO();
    }

    /**
     * Obtém todos os avisos.
     *
     * @return array Um array de objetos Aviso.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterTodosAvisos(): array
    {
        return $this->avisoDAO->buscarTodos();
    }

    /**
     * Obtém avisos ativos para um território específico.
     *
     * @param int|null $territorioId O ID do território do usuário logado, ou null para avisos gerais.
     * @return array Um array de objetos Aviso.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterAvisosAtivosPorTerritorio(?int $territorioId): array
    {
        $avisosAtivos = $this->avisoDAO->buscarTodosAtivos();
        $filteredAvisos = [];

        foreach ($avisosAtivos as $aviso) {
            // Se o aviso for para um território específico, verifica se corresponde ao território do usuário
            // Se id_territorio_exibicao for NULL, o aviso é para todos os territórios
            if ($aviso->id_territorio_exibicao === null || $aviso->id_territorio_exibicao === $territorioId) {
                $filteredAvisos[] = $aviso;
            }
        }
        return $filteredAvisos;
    }

    /**
     * Obtém um aviso pelo seu ID.
     *
     * @param int $id O ID do aviso.
     * @return Aviso|null O objeto Aviso ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterAvisoPorId(int $id): ?Aviso
    {
        return $this->avisoDAO->buscarPorID($id);
    }

    /**
     * Salva um novo aviso no banco de dados.
     *
     * @param array $dados Os dados do novo aviso.
     * @return int|false O ID do novo aviso ou false em caso de falha.
     * @throws \InvalidArgumentException Se os dados forem inválidos.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function salvarAviso(array $dados)
    {
        if (empty($dados['descricao']) || empty($dados['data_inicio_exibicao']) || empty($dados['data_fim_exibicao'])) {
            throw new \InvalidArgumentException("Dados essenciais para o aviso estão faltando.");
        }

        $aviso = new Aviso($dados);

        $newId = $this->avisoDAO->criar($aviso);

        return $newId;
    }

    /**
     * Atualiza um aviso existente.
     *
     * @param int $id O ID do aviso a ser atualizado.
     * @param array $dados Os novos dados do aviso.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws \InvalidArgumentException Se os dados forem inválidos ou o ID não for encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizarAviso(int $id, array $dados): bool
    {
        $avisoExistente = $this->avisoDAO->buscarPorID($id);
        if (!$avisoExistente) {
            throw new \InvalidArgumentException("Aviso com ID {$id} não encontrado para atualização.");
        }

        foreach ($dados as $key => $value) {
            if (property_exists($avisoExistente, $key)) {
                $avisoExistente->$key = $value;
            }
        }

        $success = $this->avisoDAO->atualizar($avisoExistente);

        return $success;
    }
}
