<?php

namespace App\Models;

/**
 * Representa a entidade 'usuarios' no banco de dados.
 */
class Usuario
{
    public ?int $id;
    public string $nome;
    public string $usuario;
    public string $senha;
    public ?int $territorio_id;
    public int $ativo;
    public string $permissoes;
    public int $primeiro_acesso;
    public ?int $id_usuario_criacao;
    public ?string $data_hora_criacao;
    public ?int $id_usuario_atualizacao;
    public ?string $data_hora_atualizacao;

    /**
     * Construtor da classe Usuario.
     *
     * @param array $data Um array associativo com os dados do usuário.
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->usuario = $data['usuario'] ?? '';
        $this->senha = $data['senha'] ?? '';
        $this->territorio_id = $data['territorio_id'] ?? null;
        $this->ativo = $data['ativo'] ?? 1;
        $this->permissoes = $data['permissoes'] ?? '000000';
        $this->primeiro_acesso = $data['primeiro_acesso'] ?? 1;
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
