<?php
// migrations/2025_07_08_093023_alterar_tabela_procedimentos_criar_triggers_after_insert.php

use App\Utils\Database;
use App\Exceptions\DatabaseException;

/**
 * Classe de migração para criar o trigger AFTER INSERT na tabela 'procedimentos'.
 */
class AlterarTabelaProcedimentosCriarTriggersAfterInsert
{
    /**
     * Executa a migração para cima (cria o trigger).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function up(): void
    {
        // SQL para criar o trigger
        // BEFORE/AFTER: Define quando o trigger é executado (antes ou depois da operação)
        // FOR EACH ROW: O trigger é executado para cada linha afetada pela operação
        // NEW: Refere-se aos valores da linha após a operação (para INSERT e UPDATE)
        // OLD: Refere-se aos valores da linha antes da operação (para UPDATE e DELETE)
        $sql = "
            CREATE TRIGGER trigger_procedimentos_after_insert
            AFTER INSERT ON procedimentos
            FOR EACH ROW
            BEGIN
                DECLARE id_usuario_acao INT;
                SET id_usuario_acao = @user_id; -- Assume que @user_id é definido na sessão MySQL

                INSERT INTO auditorias (
                    nome_tabela,
                    acao,
                    dados_antigos,
                    dados_novos,
                    id_usuario_acao,
                    data_hora_acao
                ) VALUES (
                    'PROCEDIMENTOS',
                    'INSERT',
                    NULL,
                    JSON_OBJECT(
                        'id', NEW.id,
                        'numero_procedimento', NEW.numero_procedimento,
                        'ano_procedimento', NEW.ano_procedimento,
                        'id_territorio', NEW.id_territorio,
                        'id_bairro', NEW.id_bairro,
                        'id_pessoa', NEW.id_pessoa,
                        'id_genitora_pessoa', NEW.id_genitora_pessoa,
                        'id_demandante', NEW.id_demandante,
                        'ativo', NEW.ativo,
                        'migrado', NEW.migrado,
                        'id_migracao', NEW.id_migracao,
                        'data_criacao', NEW.data_criacao,
                        'hora_criacao', NEW.hora_criacao,
                        'id_usuario_criacao', NEW.id_usuario_criacao,
                        'id_usuario_atualizacao', NEW.id_usuario_atualizacao,
                        'data_hora_atualizacao', NEW.data_hora_atualizacao
                    ),
                    id_usuario_acao,
                    NOW()
                );
            END;
        ";

        try {
            Database::execute($sql);
            echo "  - Tabela 'procedimentos' alterada - Trigger After Insert criado.\n";
        } catch (DatabaseException $e) {
            // Se o trigger já existir, apenas loga e continua
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "  - Trigger 'trigger_procedimentos_after_insert' já existente. Pulando criação.\n";
            } else {
                throw new DatabaseException("Erro ao criar o trigger after insert na tabela 'procedimentos': " . $e->getMessage(), 0, $e);
            }
        }
    }

    /**
     * Executa a migração para baixo (remove o trigger).
     *
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function down(): void
    {
        $sql = "DROP TRIGGER IF EXISTS trigger_procedimentos_after_insert;";
        try {
            Database::execute($sql);
            echo "  - Trigger 'trigger_procedimentos_after_insert' removido.\n";
        } catch (DatabaseException $e) {
            throw new DatabaseException("Erro ao remover o trigger after insert na tabela 'procedimentos': " . $e->getMessage(), 0, $e);
        }
    }
}
