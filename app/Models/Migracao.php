<?php

namespace App\Models;

/**
 * Representa a entidade 'migracoes' no banco de dados.
 */
class Migracao
{
    public ?int $id;
    public int $numero_antigo;
    public int $ano_antigo;
    public int $territorio_antigo;
    public int $numero_novo;
    public int $ano_novo;
    public int $territorio_novo;
    public int $id_motivo_migracao;
    public ?int $id_usuario_criacao;
    public ?string $data_hora_criacao;

    /**
     * Construtor da classe Migracao.
     *
     * @param array $data Um array associativo com os dados da migração.
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->numero_antigo = $data['numero_antigo'] ?? 0;
        $this->ano_antigo = $data['ano_antigo'] ?? 0;
        $this->territorio_antigo = $data['territorio_antigo'] ?? 0;
        $this->numero_novo = $data['numero_novo'] ?? 0;
        $this->ano_novo = $data['ano_novo'] ?? 0;
        $this->territorio_novo = $data['territorio_novo'] ?? 0;
        $this->id_motivo_migracao = $data['id_motivo_migracao'] ?? 0;
        $this->id_usuario_criacao = $data['id_usuario_criacao'] ?? null;
        $this->data_hora_criacao = $data['data_hora_criacao'] ?? null;
    }

    /**
     * Método mágico para obter propriedades.
     *
     * @param string $name O nome da propriedade.
     * @return mixed O valor da propriedade.
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        trigger_error("Propriedade indefinida: " . $name, E_USER_NOTICE);
        return null;
    }

    /**
     * Método mágico para definir propriedades.
     *
     * @param string $name O nome da propriedade.
     * @param mixed $value O valor a ser definido.
     */
    public function __set(string $name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            trigger_error("Propriedade indefinida: " . $name, E_USER_NOTICE);
        }
    }
}
