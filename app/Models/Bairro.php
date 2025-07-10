<?php

namespace App\Models;

/**
 * Representa a entidade 'bairros' no banco de dados.
 */
class Bairro
{
    public ?int $id;
    public string $nome;
    public ?int $territorio_id;
    public int $ativo;
    public ?int $id_usuario_criacao;
    public ?string $data_hora_criacao;
    public ?int $id_usuario_atualizacao;
    public ?string $data_hora_atualizacao;

    /**
     * Construtor da classe Bairro.
     *
     * @param array $data Um array associativo com os dados do bairro.
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->territorio_id = $data['territorio_id'] ?? null;
        $this->ativo = $data['ativo'] ?? 1;
        $this->id_usuario_criacao = $data['id_usuario_criacao'] ?? null;
        $this->data_hora_criacao = $data['data_hora_criacao'] ?? null;
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
