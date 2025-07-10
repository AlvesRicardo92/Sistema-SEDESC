<?php

namespace App\Models;

/**
 * Representa a entidade 'procedimentos' no banco de dados.
 */
class Procedimento
{
    public ?int $id;
    public int $numero_procedimento;
    public int $ano_procedimento;
    public int $id_territorio;
    public ?int $id_bairro;
    public ?int $id_pessoa;
    public ?int $id_genitora_pessoa;
    public ?int $id_demandante;
    public int $ativo;
    public int $migrado;
    public ?int $id_migracao;
    public string $data_criacao;
    public ?string $hora_criacao;
    public ?int $id_usuario_criacao;
    public ?int $id_usuario_atualizacao;
    public ?string $data_hora_atualizacao;

    /**
     * Construtor da classe Procedimento.
     *
     * @param array $data Um array associativo com os dados do procedimento.
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->numero_procedimento = $data['numero_procedimento'] ?? 0;
        $this->ano_procedimento = $data['ano_procedimento'] ?? 0;
        $this->id_territorio = $data['id_territorio'] ?? 0;
        $this->id_bairro = $data['id_bairro'] ?? null;
        $this->id_pessoa = $data['id_pessoa'] ?? null;
        $this->id_genitora_pessoa = $data['id_genitora_pessoa'] ?? null;
        $this->id_demandante = $data['id_demandante'] ?? null;
        $this->ativo = $data['ativo'] ?? 1;
        $this->migrado = $data['migrado'] ?? 0;
        $this->id_migracao = $data['id_migracao'] ?? null;
        $this->data_criacao = $data['data_criacao'] ?? date('Y-m-d');
        $this->hora_criacao = $data['hora_criacao'] ?? date('H:i:s');
        $this->id_usuario_criacao = $data['id_usuario_criacao'] ?? null;
        $this->id_usuario_atualizacao = $data['id_usuario_atualizacao'] ?? null;
        $this->data_hora_atualizacao = $data['data_hora_atualizacao'] ?? null;
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
