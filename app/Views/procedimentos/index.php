<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Procedimentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Link para o CSS externo -->
</head>
<body>
    <!-- Navbar Superior (mantida do dashboard) -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">Meu App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php
                    require_once __DIR__ . '/../Utils/MenuBuilder.php';
                    use App\Utils\MenuBuilder;

                    $userPermissions = $_SESSION['user_permissions'] ?? '';
                    echo MenuBuilder::buildMenu($userPermissions);
                    ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link">Olá, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuário') ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container container-list card card-custom p-4 mt-4">
        <h1 class="text-center mb-4 text-primary">Gerenciar Procedimentos</h1>

        <!-- Campos de Pesquisa -->
        <div class="row mb-3">
            <div class="col-md-3 search-field-group">
                <label for="search_numero" class="form-label">Número:</label>
                <input type="number" class="form-control search-input" id="search_numero" name="numero_procedimento">
            </div>
            <div class="col-md-3 search-field-group">
                <label for="search_nome_pessoa" class="form-label">Nome Pessoa:</label>
                <input type="text" class="form-control search-input" id="search_nome_pessoa" name="nome_pessoa">
            </div>
            <div class="col-md-3 search-field-group">
                <label for="search_nome_genitora" class="form-label">Nome Genitora:</label>
                <input type="text" class="form-control search-input" id="search_nome_genitora" name="nome_genitora">
            </div>
            <div class="col-md-3 search-field-group">
                <label for="search_data_nascimento" class="form-label">Data Nascimento:</label>
                <input type="date" class="form-control search-input" id="search_data_nascimento" name="data_nascimento">
            </div>
        </div>

        <div class="d-flex justify-content-between mb-3">
            <button type="button" class="btn btn-primary btn-custom" id="searchBtn">Pesquisar</button>
            <button type="button" class="btn btn-success btn-custom" id="newProcedimentoBtn">
                Novo Procedimento
            </button>
        </div>

        <!-- Tabela de Resultados -->
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle table-custom" id="procedimentosTable">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Número/Ano</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Genitora</th>
                        <th scope="col">Data Nascimento</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody id="procedimentosTableBody">
                    <tr>
                        <td colspan="5" class="text-center">Preencha um dos campos acima para pesquisar.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="/dashboard" class="btn btn-secondary btn-custom">Voltar ao Dashboard</a>
        </div>
    </div>

    <!-- Modais -->

    <!-- Modal de Criação/Edição de Procedimento -->
    <div class="modal fade" id="procedimentoFormModal" tabindex="-1" aria-labelledby="procedimentoFormModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="procedimentoFormModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="procedimentoForm">
                        <input type="hidden" id="form_id_procedimento" name="id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="form_numero_procedimento" class="form-label">Número do Procedimento:</label>
                                <input type="number" class="form-control" id="form_numero_procedimento" name="numero_procedimento" required disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="form_ano_procedimento" class="form-label">Ano do Procedimento:</label>
                                <input type="number" class="form-control" id="form_ano_procedimento" name="ano_procedimento" required disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="form_id_territorio" class="form-label">Território:</label>
                                <select class="form-select" id="form_id_territorio" name="id_territorio" required>
                                    <!-- Opções preenchidas via JS -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="form_id_bairro" class="form-label">Bairro:</label>
                                <select class="form-select" id="form_id_bairro" name="id_bairro">
                                    <!-- Opções preenchidas via JS -->
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 search-field-group">
                                <label for="form_nome_pessoa" class="form-label">Pessoa:</label>
                                <input type="text" class="form-control autocomplete-input" id="form_nome_pessoa" name="nome_pessoa" data-target-id="form_id_pessoa" data-target-data-nascimento="form_data_nascimento_pessoa" data-target-sexo="form_id_sexo_pessoa" data-field-type="pessoa">
                                <input type="hidden" id="form_id_pessoa" name="id_pessoa">
                                <div class="autocomplete-results" id="autocomplete_results_pessoa"></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="form_data_nascimento_pessoa" class="form-label">Data Nasc. Pessoa:</label>
                                <input type="date" class="form-control" id="form_data_nascimento_pessoa" name="data_nascimento_pessoa">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="form_id_sexo_pessoa" class="form-label">Sexo Pessoa:</label>
                                <select class="form-select" id="form_id_sexo_pessoa" name="id_sexo_pessoa">
                                    <!-- Opções preenchidas via JS -->
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 search-field-group">
                                <label for="form_nome_genitora" class="form-label">Genitora:</label>
                                <input type="text" class="form-control autocomplete-input" id="form_nome_genitora" name="nome_genitora" data-target-id="form_id_genitora_pessoa" data-target-data-nascimento="form_data_nascimento_genitora" data-target-sexo="form_id_sexo_genitora" data-field-type="genitora">
                                <input type="hidden" id="form_id_genitora_pessoa" name="id_genitora_pessoa">
                                <div class="autocomplete-results" id="autocomplete_results_genitora"></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="form_data_nascimento_genitora" class="form-label">Data Nasc. Genitora:</label>
                                <input type="date" class="form-control" id="form_data_nascimento_genitora" name="data_nascimento_genitora">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="form_id_sexo_genitora" class="form-label">Sexo Genitora:</label>
                                <select class="form-select" id="form_id_sexo_genitora" name="id_sexo_genitora">
                                    <!-- Opções preenchidas via JS -->
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 search-field-group">
                                <label for="form_nome_demandante" class="form-label">Demandante:</label>
                                <input type="text" class="form-control autocomplete-input" id="form_nome_demandante" name="nome_demandante" data-target-id="form_id_demandante" data-field-type="demandante">
                                <input type="hidden" id="form_id_demandante" name="id_demandante">
                                <div class="autocomplete-results" id="autocomplete_results_demandante"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="form_id_migracao" class="form-label">ID Migração:</label>
                                <input type="number" class="form-control" id="form_id_migracao" name="id_migracao">
                            </div>
                        </div>

                        <div class="mb-3 form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="form_ativo" name="ativo" value="1">
                            <label class="form-check-label" for="form_ativo">Ativo</label>
                        </div>
                        <div class="mb-3 form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="form_migrado" name="migrado" value="1">
                            <label class="form-check-label" for="form_migrado">Migrado</label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-custom" id="saveProcedimentoBtn"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Visualização de Procedimento (apenas para exibir dados) -->
    <div class="modal fade" id="viewProcedimentoModal" tabindex="-1" aria-labelledby="viewProcedimentoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewProcedimentoModalLabel">Detalhes do Procedimento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>ID:</strong> <span id="view_id"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Número:</strong> <span id="view_numero_procedimento"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Ano:</strong> <span id="view_ano_procedimento"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Território Procedimento:</strong> <span id="view_id_territorio"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Bairro:</strong> <span id="view_id_bairro"></span>
                            <strong class="ms-3">Território Bairro:</strong> <span id="view_territorio_bairro_nome"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Pessoa:</strong> <span id="view_id_pessoa"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Genitora:</strong> <span id="view_id_genitora_pessoa"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Demandante:</strong> <span id="view_id_demandante"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Ativo:</strong> <span id="view_ativo"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Migrado:</strong> <span id="view_migrado"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>ID Migração:</strong> <span id="view_id_migracao"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Data Criação:</strong> <span id="view_data_criacao"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Hora Criação:</strong> <span id="view_hora_criacao"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Usuário Criação:</strong> <span id="view_id_usuario_criacao"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Data/Hora Atualização:</strong> <span id="view_data_hora_atualizacao"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Usuário Atualização:</strong> <span id="view_id_usuario_atualizacao"></span></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-custom" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="deleteProcedimentoModal" tabindex="-1" aria-labelledby="deleteProcedimentoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteProcedimentoModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza de que deseja excluir o procedimento <strong id="delete_procedimento_info"></strong>?</p>
                    <input type="hidden" id="delete_procedimento_token">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-custom" id="confirmDeleteBtn">Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Variáveis PHP passadas para o JavaScript -->
    <script>
        window.userPermissions = "<?= $_SESSION['user_permissions'] ?? '0000000000' ?>";
        window.userTerritoryId = "<?= $_SESSION['user_territory_id'] ?? '' ?>";
    </script>
    
    <!-- Script principal da página -->
    <script src="/assets/js/procedimentos.js"></script>
</body>
</html>
