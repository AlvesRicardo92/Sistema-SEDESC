<?php

namespace App\Models;

/**
 * Representa a entidade 'avisos' no banco de dados.
 */
class Aviso
{
    public ?int $id;
    public string $descricao;
    public ?int $id_territorio_exibicao;
    public string $data_inicio_exibicao;
    public string $data_fim_exibicao;
    public ?string $nome_imagem;
    public ?int $id_usuario_criacao;
    public ?string $data_hora_criacao;
    public ?int $id_usuario_atualizacao;
    public ?string $data_hora_atualizacao;

    /**
     * Construtor da classe Aviso.
     *
     * @param array $data Um array associativo com os dados do aviso.
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->descricao = $data['descricao'] ?? '';
        $this->id_territorio_exibicao = $data['id_territorio_exibicao'] ?? null;
        $this->data_inicio_exibicao = $data['data_inicio_exibicao'] ?? date('Y-m-d');
        $this->data_fim_exibicao = $data['data_fim_exibicao'] ?? date('Y-m-d');
        $this->nome_imagem = $data['nome_imagem'] ?? null;
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
