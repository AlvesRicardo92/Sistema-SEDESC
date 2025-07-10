<?php
// index.php - Ponto de entrada da aplicação com sistema de login e roteamento completo

// Inicia a sessão PHP para gerenciar o estado do usuário
session_start();

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/config/database.php';

use App\Controllers\ProcedimentoController;
use App\Controllers\UsuarioController;
use App\Controllers\TerritorioController;
use App\Controllers\BairroController;
use App\Controllers\PessoaController;
use App\Controllers\DemandanteController;
use App\Controllers\MigracaoController;
use App\Controllers\MotivoMigracaoController;
use App\Controllers\SexoController;
use App\Controllers\AuditoriaController;
use App\Controllers\AvisoController; // Adicionado para incluir o AvisoController
use App\Utils\Database;

// Carregar as configurações do ambiente e definir no Database
// Certifique-se de que este arquivo 'config/database.php' existe e retorna um array de configuração
$dbConfig = require_once __DIR__ . '/config/database.php';
Database::setConfig($dbConfig);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$query = $_GET;

// A lógica de autenticação e redirecionamento agora está no BaseController
// Removemos a duplicação daqui, pois o BaseController será instanciado em cada Controller.

// Roteamento
switch ($requestUri) {
    case '/':
        header("Location: /dashboard");
        exit();
        break;

    case '/login':
        $controller = new UsuarioController();
        $controller->showLoginForm();
        break;

    case '/authenticate':
        $controller = new UsuarioController();
        $controller->authenticate();
        break;

    case '/logout':
        $controller = new UsuarioController();
        $controller->logout();
        break;

    case '/first-access':
        $controller = new UsuarioController();
        $controller->showFirstAccessForm();
        break;

    case '/authenticate-first-access': // Rota para processar o formulário de primeiro acesso
        $controller = new UsuarioController();
        $controller->processFirstAccessChange();
        break;

    case '/dashboard':
        $controller = new UsuarioController();
        $controller->showDashboard();
        break;

    // --- Rotas para Procedimentos ---
    case '/procedimentos': // Rota principal para a tela unificada
        $controller = new ProcedimentoController();
        $controller->index();
        break;
    case '/procedimentos/search-json': // Nova rota para pesquisa AJAX
        $controller = new ProcedimentoController();
        $controller->searchJson();
        break;
    case '/procedimentos/mostrar-json': // Nova rota para mostrar detalhes via AJAX
        $controller = new ProcedimentoController();
        $controller->mostrarJson();
        break;
    case '/procedimentos/salvar-json': // Nova rota para salvar via AJAX
        $controller = new ProcedimentoController();
        $controller->salvarJson();
        break;
    case '/procedimentos/atualizar-json': // Nova rota para atualizar via AJAX
        $controller = new ProcedimentoController();
        $controller->atualizarJson();
        break;
    case '/procedimentos/deletar-json': // Nova rota para deletar via AJAX
        $controller = new ProcedimentoController();
        $controller->deletarJson();
        break;

    // --- Rotas para Pessoas (JSON para Autocomplete) ---
    case '/pessoas/search-by-name-json':
        $controller = new PessoaController();
        $controller->searchByNameJson();
        break;

    // --- Rotas para Demandantes (JSON para Autocomplete) ---
    case '/demandantes/search-by-name-json':
        $controller = new DemandanteController();
        $controller->searchByNameJson();
        break;

    // --- Rotas para Bairros (JSON para Selects) ---
    case '/bairros/listar-ativos-json':
        $controller = new BairroController();
        $controller->listarAtivosJson();
        break;
    case '/bairros/listar-por-territorio-json':
        $controller = new BairroController();
        $controller->listarPorTerritorioJson();
        break;

    // --- Rotas para Sexos (JSON para Selects) ---
    case '/sexos/listar-json':
        $controller = new SexoController();
        $controller->listarJson();
        break;

    // --- Rotas para Territórios (JSON para Selects) ---
    case '/territorios/listar-json':
        $controller = new TerritorioController();
        $controller->listarJson();
        break;

    // --- Rotas para outras entidades (mantidas se houver UIs separadas) ---
    case '/procedimentos/listar': // Mantido, mas a UI principal usa /procedimentos
        $controller = new ProcedimentoController();
        $controller->listar();
        break;
    case '/procedimentos/mostrar':
        $controller = new ProcedimentoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->mostrar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404'); // Já tratado pelo BaseController
        }
        break;
    case '/procedimentos/criar':
        $controller = new ProcedimentoController();
        $controller->criar();
        break;
    case '/procedimentos/salvar':
        $controller = new ProcedimentoController();
        $controller->salvar();
        break;
    case '/procedimentos/editar':
        $controller = new ProcedimentoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->editar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/procedimentos/atualizar':
        $controller = new ProcedimentoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->atualizar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;

    // --- Rotas para Territórios ---
    case '/territorios/listar':
        $controller = new TerritorioController();
        $controller->listar();
        break;
    case '/territorios/mostrar':
        $controller = new TerritorioController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->mostrar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/territorios/criar':
        $controller = new TerritorioController();
        $controller->criar();
        break;
    case '/territorios/salvar': // Nova rota para salvar POST
        $controller = new TerritorioController();
        $controller->salvar();
        break;
    case '/territorios/editar': // Nova rota para exibir formulário de edição
        $controller = new TerritorioController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->editar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/territorios/atualizar':
        $controller = new TerritorioController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->atualizar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;

    // --- Rotas para Bairros ---
    case '/bairros/listar':
        $controller = new BairroController();
        $controller->listar();
        break;
    case '/bairros/mostrar':
        $controller = new BairroController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->mostrar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/bairros/criar':
        $controller = new BairroController();
        $controller->criar();
        break;
    case '/bairros/salvar': // Nova rota para salvar POST
        $controller = new BairroController();
        $controller->salvar();
        break;
    case '/bairros/editar': // Nova rota para exibir formulário de edição
        $controller = new BairroController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->editar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/bairros/atualizar':
        $controller = new BairroController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->atualizar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;

    // --- Rotas para Pessoas ---
    case '/pessoas/listar':
        $controller = new PessoaController();
        $controller->listar();
        break;
    case '/pessoas/mostrar':
        $controller = new PessoaController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->mostrar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/pessoas/criar':
        $controller = new PessoaController();
        $controller->criar();
        break;
    case '/pessoas/salvar': // Nova rota para salvar POST
        $controller = new PessoaController();
        $controller->salvar();
        break;
    case '/pessoas/editar': // Nova rota para exibir formulário de edição
        $controller = new PessoaController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->editar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/pessoas/atualizar':
        $controller = new PessoaController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->atualizar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;

    // --- Rotas para Demandantes ---
    case '/demandantes/listar':
        $controller = new DemandanteController();
        $controller->listar();
        break;
    case '/demandantes/mostrar':
        $controller = new DemandanteController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->mostrar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/demandantes/criar':
        $controller = new DemandanteController();
        $controller->criar();
        break;
    case '/demandantes/salvar': // Nova rota para salvar POST
        $controller = new DemandanteController();
        $controller->salvar();
        break;
    case '/demandantes/editar': // Nova rota para exibir formulário de edição
        $controller = new DemandanteController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->editar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/demandantes/atualizar':
        $controller = new DemandanteController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->atualizar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;

    // --- Rotas para Migrações ---
    case '/migracoes/listar':
        $controller = new MigracaoController();
        $controller->listar();
        break;
    case '/migracoes/mostrar':
        $controller = new MigracaoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->mostrar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/migracoes/criar':
        $controller = new MigracaoController();
        $controller->criar();
        break;
    case '/migracoes/salvar': // Nova rota para salvar POST
        $controller = new MigracaoController();
        $controller->salvar();
        break;
    case '/migracoes/editar': // Nova rota para exibir formulário de edição
        $controller = new MigracaoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->editar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/migracoes/atualizar':
        $controller = new MigracaoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->atualizar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;

    // --- Rotas para Motivos de Migração ---
    case '/motivos_migracao/listar':
        $controller = new MotivoMigracaoController();
        $controller->listar();
        break;
    case '/motivos_migracao/mostrar':
        $controller = new MotivoMigracaoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->mostrar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/motivos_migracao/criar':
        $controller = new MotivoMigracaoController();
        $controller->criar();
        break;
    case '/motivos_migracao/salvar': // Nova rota para salvar POST
        $controller = new MotivoMigracaoController();
        $controller->salvar();
        break;
    case '/motivos_migracao/editar': // Nova rota para exibir formulário de edição
        $controller = new MotivoMigracaoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->editar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/motivos_migracao/atualizar':
        $controller = new MotivoMigracaoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->atualizar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;

    // --- Rotas para Sexos ---
    case '/sexos/listar':
        $controller = new SexoController();
        $controller->listar();
        break;
    case '/sexos/mostrar':
        $controller = new SexoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->mostrar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/sexos/criar':
        $controller = new SexoController();
        $controller->criar();
        break;
    case '/sexos/salvar': // Nova rota para salvar POST
        $controller = new SexoController();
        $controller->salvar();
        break;
    case '/sexos/editar': // Nova rota para exibir formulário de edição
        $controller = new SexoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->editar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/sexos/atualizar':
        $controller = new SexoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->atualizar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;

    // --- Rotas para Auditorias ---
    case '/auditorias/listar':
        $controller = new AuditoriaController();
        $controller->listar();
        break;
    case '/auditorias/mostrar':
        $controller = new AuditoriaController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->mostrar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    // Auditoria não tem criar/atualizar direto via Controller/Service

    // --- Rotas para Avisos ---
    case '/avisos/listar':
        $controller = new AvisoController();
        $controller->listar();
        break;
    case '/avisos/mostrar':
        $controller = new AvisoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->mostrar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/avisos/criar':
        $controller = new AvisoController();
        $controller->criar();
        break;
    case '/avisos/salvar':
        $controller = new AvisoController();
        $controller->salvar();
        break;
    case '/avisos/editar':
        $controller = new AvisoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->editar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;
    case '/avisos/atualizar':
        $controller = new AvisoController();
        $id = $query['id'] ?? null;
        if ($id !== null && is_numeric($id)) {
            $controller->atualizar((int)$id);
        } else {
            http_response_code(404);
            //renderView('404');
        }
        break;

    default:
        // Define o código de status HTTP para 404 (Não Encontrado)
        http_response_code(404);
        // Renderiza a view 404 (agora via BaseController)
        $controller = new UsuarioController(); // Qualquer controller que estenda BaseController serve
        $controller->render('404', [], 404);
        break;
}

// Fecha a conexão com o banco de dados ao final da requisição
Database::closeConnection();
