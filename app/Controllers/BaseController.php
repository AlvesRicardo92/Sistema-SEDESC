<?php

namespace App\Controllers;

class BaseController
{
    public function __construct()
    {
        // Inicia a sessão se ainda não estiver iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se o usuário está logado, exceto para as rotas de login/autenticação
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $publicRoutes = ['/login', '/authenticate', '/first-access', '/authenticate-first-access'];

        if (!isset($_SESSION['user_id']) && !in_array($requestUri, $publicRoutes)) {
            header("Location: /login?redirect=" . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }

        // Se logado e for primeiro acesso, força a troca de senha
        if (isset($_SESSION['user_id']) && ($_SESSION['primeiro_acesso'] ?? null) === 1 && !in_array($requestUri, ['/first-access', '/authenticate-first-access'])) {
            header("Location: /first-access");
            exit();
        }
    }

    /**
     * Renderiza uma view PHP.
     *
     * @param string $viewName O nome da view (ex: 'dashboard', 'usuarios/listar').
     * @param array $data Dados a serem passados para a view.
     * @param int $statusCode Código de status HTTP a ser enviado.
     */
    protected function render(string $viewName, array $data = [], int $statusCode = 200): void
    {
        http_response_code($statusCode);
        extract($data); // Extrai o array $data para variáveis individuais

        $viewPath = __DIR__ . '/../Views/' . $viewName . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            // Se a view não for encontrada, tenta renderizar uma página de erro 500
            http_response_code(500);
            $errorViewPath = __DIR__ . '/../Views/500.php';
            if (file_exists($errorViewPath)) {
                require $errorViewPath;
            } else {
                echo "Erro interno do servidor. View '{$viewName}' não encontrada.";
            }
        }
    }

    /**
     * Renderiza uma resposta JSON.
     *
     * @param array $data Dados a serem retornados como JSON.
     * @param int $statusCode Código de status HTTP a ser enviado.
     */
    protected function renderJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Verifica se o usuário logado possui uma permissão específica.
     *
     * @param int $index O índice da permissão na string de permissões (0-based).
     * @return bool True se o usuário tiver a permissão, false caso contrário.
     */
    protected function hasPermission(int $index): bool
    {
        $permissions = $_SESSION['user_permissions'] ?? '';
        return isset($permissions[$index]) && $permissions[$index] === '1';
    }
}
