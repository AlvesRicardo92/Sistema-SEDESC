<?php

namespace App\Controllers;

use App\Services\UsuarioService;
use App\Services\AvisoService; // Para o dashboard
use App\Exceptions\DatabaseException;

/**
 * Controller para a entidade Usuário.
 * Lida com as requisições HTTP relacionadas a autenticação de usuários (login, logout, primeiro acesso).
 */
class UsuarioController
{
    private $usuarioService;
    private $avisoService; // Para o dashboard

    /**
     * Construtor do UsuarioController.
     * Injeta as dependências dos Services.
     */
    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
        $this->avisoService = new AvisoService(); // Instancia o AvisoService
    }

    /**
     * Exibe o formulário de login.
     */
    public function showLoginForm(): void
    {
        if (isset($_SESSION['user_id'])) {
            header("Location: /dashboard");
            exit();
        }
        $this->render('auth/login');
    }

    /**
     * Processa a tentativa de autenticação do usuário.
     * Recebe dados via POST.
     */
    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_POST['usuario'] ?? '';
            $senha = $_POST['senha'] ?? '';

            try {
                $authenticatedUser = $this->usuarioService->authenticateUser($usuario, $senha);

                if ($authenticatedUser) {
                    // Autenticação bem-sucedida: armazena dados na sessão
                    $_SESSION['user_id'] = $authenticatedUser->id;
                    $_SESSION['user_name'] = $authenticatedUser->nome;
                    $_SESSION['user_permissions'] = $authenticatedUser->permissoes;
                    $_SESSION['user_territory_id'] = $authenticatedUser->territorio_id;
                    $_SESSION['primeiro_acesso'] = $authenticatedUser->primeiro_acesso; // Armazena o status de primeiro acesso

                    // Verifica se é o primeiro acesso
                    if ($authenticatedUser->primeiro_acesso === 1) {
                        header("Location: /first-access");
                        exit();
                    } else {
                        $redirectUrl = $_GET['redirect'] ?? '/dashboard';
                        header("Location: " . $redirectUrl);
                        exit();
                    }
                } else {
                    $this->render('auth/login', ['error' => 'Usuário ou senha inválidos.']);
                }
            } catch (DatabaseException $e) {
                $this->render('auth/login', ['error' => 'Erro de banco de dados: ' . $e->getMessage()]);
            } catch (\Exception $e) {
                $this->render('auth/login', ['error' => 'Ocorreu um erro inesperado: ' . $e->getMessage()]);
            }
        } else {
            header("Location: /login");
            exit();
        }
    }

    /**
     * Realiza o logout do usuário.
     */
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        header("Location: /login");
        exit();
    }

    /**
     * Exibe o dashboard do usuário logado com avisos.
     */
    public function showDashboard(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        // Verifica se é primeiro acesso e redireciona se necessário
        if (isset($_SESSION['primeiro_acesso']) && $_SESSION['primeiro_acesso'] === 1) {
            header("Location: /first-access");
            exit();
        }

        try {
            $territorioId = $_SESSION['user_territory_id'] ?? null;
            $avisos = $this->avisoService->obterAvisosAtivosPorTerritorio($territorioId);
            $this->render('dashboard', ['user_name' => $_SESSION['user_name'], 'avisos' => $avisos]);
        } catch (DatabaseException $e) {
            $this->render('dashboard', ['user_name' => $_SESSION['user_name'], 'error' => 'Erro ao carregar avisos: ' . $e->getMessage()]);
        }
    }

    /**
     * Exibe o formulário de primeiro acesso para troca de senha.
     */
    public function showFirstAccessForm(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        try {
            $user = $this->usuarioService->obterUsuarioPorId($_SESSION['user_id']);
            if (!$user || $user->primeiro_acesso === 0) {
                // Se não for primeiro acesso, redireciona para o dashboard
                header("Location: /dashboard");
                exit();
            }
            $this->render('auth/first_access');
        } catch (DatabaseException $e) {
            echo "Erro ao verificar status de primeiro acesso: " . $e->getMessage();
        }
    }

    /**
     * Processa a troca de senha do primeiro acesso.
     */
    public function processFirstAccessChange(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($newPassword) || strlen($newPassword) < 6) {
                $this->render('auth/first_access', ['error' => 'A nova senha deve ter pelo menos 6 caracteres.']);
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $this->render('auth/first_access', ['error' => 'As senhas não coincidem.']);
                return;
            }

            try {
                $success = $this->usuarioService->atualizarSenhaPrimeiroAcesso($_SESSION['user_id'], $newPassword);

                if ($success) {
                    // Senha atualizada com sucesso, marca primeiro_acesso como 0 e redireciona
                    $_SESSION['primeiro_acesso'] = 0; // Atualiza a sessão também
                    header("Location: /dashboard");
                    exit();
                } else {
                    $this->render('auth/first_access', ['error' => 'Falha ao atualizar a senha.']);
                }
            } catch (\InvalidArgumentException $e) {
                $this->render('auth/first_access', ['error' => $e->getMessage()]);
            } catch (DatabaseException $e) {
                $this->render('auth/first_access', ['error' => 'Erro de banco de dados ao atualizar senha: ' . $e->getMessage()]);
            }
        } else {
            header("Location: /first-access");
            exit();
        }
    }

    /**
     * Função auxiliar para renderizar as views.
     *
     * @param string $viewName O nome da view (ex: 'auth/login').
     * @param array $data Um array associativo de dados a serem passados para a view.
     */
    private function render(string $viewName, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . $viewName . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "Erro: View '{$viewName}' não encontrada.";
        }
    }
}
