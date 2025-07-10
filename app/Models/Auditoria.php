<?php

namespace App\Models;

/**
 * Representa a entidade 'auditorias' no banco de dados.
 */
class Auditoria
{
    public ?int $id;
    public string $nome_tabela;
    public string $acao; // ENUM('INSERT','UPDATE','DELETE')
    public ?string $dados_antigos; // JSON
    public ?string $dados_novos;   // JSON
    public ?int $id_usuario_acao;
    public ?string $data_hora_acao;

    /**
     * Construtor da classe Auditoria.
     *
     * @param array $data Um array associativo com os dados da auditoria.
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->nome_tabela = $data['nome_tabela'] ?? '';
        $this->acao = $data['acao'] ?? '';
        $this->dados_antigos = $data['dados_antigos'] ?? null;
        $this->dados_novos = $data['dados_novos'] ?? null;
        $this->id_usuario_acao = $data['id_usuario_acao'] ?? null;
        $this->data_hora_acao = $data['data_hora_acao'] ?? null;
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
