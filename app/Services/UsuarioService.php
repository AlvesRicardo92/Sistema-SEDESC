<?php

namespace App\Services;

use App\DAO\UsuarioDAO;
use App\Models\Usuario;
use App\Exceptions\DatabaseException;

/**
 * Camada de Serviço para a entidade Usuário.
 * Contém a lógica de negócio relacionada a usuários, como autenticação e gerenciamento de senha.
 */
class UsuarioService
{
    private $usuarioDAO;

    /**
     * Construtor do UsuarioService.
     * Injeta a dependência do UsuarioDAO.
     */
    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
    }

    /**
     * Tenta autenticar um usuário com base no nome de usuário e senha.
     *
     * @param string $username O nome de usuário.
     * @param string $password A senha em texto puro.
     * @return Usuario|null O objeto Usuário autenticado se as credenciais forem válidas, caso contrário, null.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function authenticateUser(string $username, string $password): ?Usuario
    {
        // Buscar o usuário pelo campo 'usuario' (que deve ser único)
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        $stmt = Database::prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new DatabaseException("Erro ao buscar usuário para autenticação: " . $stmt->error, $stmt->errno);
        }

        $data = $result->fetch_assoc();
        $stmt->close();
        $result->free();

        if (!$data) {
            return null; // Usuário não encontrado
        }

        $user = new Usuario($data);

        // Verifica a senha usando password_verify para senhas hasheadas
        if (password_verify($password, $user->senha)) {
            return $user; // Autenticação bem-sucedida
        }

        return null; // Senha incorreta
    }

    /**
     * Cria um novo usuário no banco de dados.
     * A senha é automaticamente hasheada antes de ser salva.
     *
     * @param array $dados Os dados do novo usuário.
     * @return int|false O ID do novo usuário ou false em caso de falha.
     * @throws \InvalidArgumentException Se os dados forem inválidos.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function criarUsuario(array $dados)
    {
        if (empty($dados['nome']) || empty($dados['usuario']) || empty($dados['senha'])) {
            throw new \InvalidArgumentException("Nome de usuário, nome e senha são obrigatórios para criar um usuário.");
        }

        // Hash da senha antes de criar o objeto Modelo
        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);

        $usuario = new Usuario($dados);
        // Definir valores padrão para novos usuários, se não vierem dos dados
        $usuario->ativo = $dados['ativo'] ?? 1;
        $usuario->permissoes = $dados['permissoes'] ?? '0000000000'; // Permissões padrão
        $usuario->primeiro_acesso = $dados['primeiro_acesso'] ?? 1; // Novo usuário sempre com primeiro acesso

        $newId = $this->usuarioDAO->criar($usuario);

        return $newId;
    }

    /**
     * Atualiza a senha de um usuário e define primeiro_acesso como 0.
     *
     * @param int $userId O ID do usuário.
     * @param string $newPassword A nova senha em texto puro.
     * @return bool True se a senha foi atualizada com sucesso, false caso contrário.
     * @throws \InvalidArgumentException Se o usuário não for encontrado ou a senha for inválida.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function atualizarSenhaPrimeiroAcesso(int $userId, string $newPassword): bool
    {
        $usuario = $this->usuarioDAO->buscarPorID($userId);

        if (!$usuario) {
            throw new \InvalidArgumentException("Usuário com ID {$userId} não encontrado.");
        }

        if (empty($newPassword) || strlen($newPassword) < 6) { // Exemplo de validação de senha
            throw new \InvalidArgumentException("A nova senha deve ter pelo menos 6 caracteres.");
        }

        // Hash da nova senha
        $usuario->senha = password_hash($newPassword, PASSWORD_DEFAULT);
        $usuario->primeiro_acesso = 0; // Marca como primeiro acesso concluído
        $usuario->data_hora_atualizacao = date('Y-m-d H:i:s'); // Atualiza timestamp

        return $this->usuarioDAO->atualizar($usuario);
    }

    /**
     * Obtém um usuário pelo seu ID.
     *
     * @param int $id O ID do usuário.
     * @return Usuario|null O objeto Usuário ou null se não encontrado.
     * @throws DatabaseException Se houver um erro no banco de dados.
     */
    public function obterUsuarioPorId(int $id): ?Usuario
    {
        return $this->usuarioDAO->buscarPorID($id);
    }
}
