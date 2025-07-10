<?php

namespace App\DAO;

use App\Interfaces\BaseDAOInterface;
use App\Models\Usuario;
use App\Utils\Database;
use App\Exceptions\DatabaseException;
use mysqli_result;

/**
 * Data Access Object para a entidade Usuario.
 */
class UsuarioDAO implements BaseDAOInterface
{
    private $tableName = 'usuarios';
    private $modelClass = Usuario::class;

    /**
     * Converte um array de dados do banco de dados para um objeto Modelo.
     *
     * @param array $data O array de dados.
     * @return Usuario O objeto Usuario.
     */
    private function hydrate(array $data): Usuario
    {
        return new Usuario($data);
    }

    /**
     * Busca todos os usuários.
     *
     * @return array Um array de objetos Usuario.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os usuários: " . $mysqli->error, $mysqli->errno);
        }

        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $this->hydrate($row);
        }
        $result->free();
        return $usuarios;
    }

    /**
     * Busca todos os usuários ativos.
     *
     * @return array Um array de objetos Usuario.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarTodosAtivos(): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ativo = 1";
        $mysqli = Database::getInstance();
        $result = $mysqli->query($sql);

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar todos os usuários ativos: " . $mysqli->error, $mysqli->errno);
        }

        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $this->hydrate($row);
        }
        $result->free();
        return $usuarios;
    }

    /**
     * Busca um usuário pelo seu ID.
     *
     * @param int $id O ID do usuário a ser buscado.
     * @return Usuario|null O objeto Usuario ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorID(int $id): ?Usuario
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar usuário por ID: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Busca usuários pelo nome ou nome de usuário, utilizando a palavra-chave LIKE.
     *
     * @param string $nome O nome ou parte do nome/usuário a ser buscado.
     * @return array Um array de objetos Usuario.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function buscarPorNome(string $nome): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE nome LIKE ? OR usuario LIKE ?";
        $stmt = Database::prepare($sql);
        $nomeLike = "%{$nome}%";
        $stmt->bind_param("ss", $nomeLike, $nomeLike);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar usuários por nome: " . $stmt->error, $stmt->errno);
        }

        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $this->hydrate($row);
        }
        $stmt->close();
        $result->free();
        return $usuarios;
    }

    /**
     * Cria um novo usuário no banco de dados.
     *
     * @param Usuario $usuario O objeto Usuario a ser criado.
     * @return int|false O ID do novo registro inserido ou false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criar(object $usuario)
    {
        if (!$usuario instanceof Usuario) {
            throw new \InvalidArgumentException("O objeto fornecido não é uma instância de Usuario.");
        }

        $sql = "INSERT INTO {$this->tableName} (nome, usuario, senha, territorio_id, ativo, permissoes, primeiro_acesso, id_usuario_criacao, id_usuario_atualizacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = Database::prepare($sql);

        $territorioId = $usuario->territorio_id ?? null;
        $idUsuarioCriacao = $usuario->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $usuario->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "sssisiisi",
            $usuario->nome,
            $usuario->usuario,
            $usuario->senha,
            $territorioId,
            $usuario->ativo,
            $usuario->permissoes,
            $usuario->primeiro_acesso,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao criar usuário: " . $stmt->error, $stmt->errno);
        }

        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /**
     * Atualiza um usuário existente no banco de dados.
     *
     * @param Usuario $usuario O objeto Usuario a ser atualizado.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizar(object $usuario): bool
    {
        if (!$usuario instanceof Usuario || $usuario->id === null) {
            throw new \InvalidArgumentException("Objeto Usuario inválido para atualização: ID é nulo ou não é uma instância de Usuario.");
        }

        $sql = "UPDATE {$this->tableName} SET nome = ?, usuario = ?, senha = ?, territorio_id = ?, ativo = ?, permissoes = ?, primeiro_acesso = ?, id_usuario_criacao = ?, id_usuario_atualizacao = ?, data_hora_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = Database::prepare($sql);

        $territorioId = $usuario->territorio_id ?? null;
        $idUsuarioCriacao = $usuario->id_usuario_criacao ?? null;
        $idUsuarioAtualizacao = $usuario->id_usuario_atualizacao ?? null;

        $stmt->bind_param(
            "sssisiisii",
            $usuario->nome,
            $usuario->usuario,
            $usuario->senha,
            $territorioId,
            $usuario->ativo,
            $usuario->permissoes,
            $usuario->primeiro_acesso,
            $idUsuarioCriacao,
            $idUsuarioAtualizacao,
            $usuario->id
        );

        if (!$stmt->execute()) {
            throw new DatabaseException("Erro ao atualizar usuário: " . $stmt->error, $stmt->errno);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows > 0;
    }
}
