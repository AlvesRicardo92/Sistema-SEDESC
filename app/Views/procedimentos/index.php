<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Procedimentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Link para o CSS externo -->
    <style>
        /* Estilos específicos para esta tela, se necessário */
        .autocomplete-results {
            position: absolute;
            background-color: white;
            border: 1px solid #dee2e6;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            width: calc(100% - 1.5rem); /* Ajusta à largura do input pai com padding */
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.25rem;
        }
        .autocomplete-results div {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
        }
        .autocomplete-results div:hover {
            background-color: #e9ecef;
        }
        .form-check-inline {
            margin-right: 1rem;
        }
        .search-field-group {
            position: relative;
        }
    </style>
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
                    <input type="hidden" id="delete_procedimento_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-custom" id="confirmDeleteBtn">Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variáveis globais para os modais
            const procedimentoFormModal = new bootstrap.Modal(document.getElementById('procedimentoFormModal'));
            const viewProcedimentoModal = new bootstrap.Modal(document.getElementById('viewProcedimentoModal'));
            const deleteProcedimentoModal = new bootstrap.Modal(document.getElementById('deleteProcedimentoModal'));

            // Elementos do formulário de criação/edição
            const procedimentoForm = document.getElementById('procedimentoForm');
            const formIdProcedimento = document.getElementById('form_id_procedimento');
            const formNumeroProcedimento = document.getElementById('form_numero_procedimento');
            const formAnoProcedimento = document.getElementById('form_ano_procedimento');
            const formIdTerritorio = document.getElementById('form_id_territorio');
            const formIdBairro = document.getElementById('form_id_bairro');
            const formNomePessoa = document.getElementById('form_nome_pessoa');
            const formIdPessoa = document.getElementById('form_id_pessoa');
            const formDataNascimentoPessoa = document.getElementById('form_data_nascimento_pessoa');
            const formIdSexoPessoa = document.getElementById('form_id_sexo_pessoa');
            const formNomeGenitora = document.getElementById('form_nome_genitora');
            const formIdGenitoraPessoa = document.getElementById('form_id_genitora_pessoa');
            const formDataNascimentoGenitora = document.getElementById('form_data_nascimento_genitora');
            const formIdSexoGenitora = document.getElementById('form_id_sexo_genitora');
            const formNomeDemandante = document.getElementById('form_nome_demandante');
            const formIdDemandante = document.getElementById('form_id_demandante');
            const formIdMigracao = document.getElementById('form_id_migracao');
            const formAtivo = document.getElementById('form_ativo');
            const formMigrado = document.getElementById('form_migrado');
            const saveProcedimentoBtn = document.getElementById('saveProcedimentoBtn');
            const procedimentoFormModalLabel = document.getElementById('procedimentoFormModalLabel');

            // Elementos da tabela e botões de ação
            const searchInputs = document.querySelectorAll('.search-input');
            const searchBtn = document.getElementById('searchBtn');
            const newProcedimentoBtn = document.getElementById('newProcedimentoBtn');
            const procedimentosTableBody = document.getElementById('procedimentosTableBody');
            const deleteProcedimentoInfo = document.getElementById('delete_procedimento_info');
            const deleteProcedimentoId = document.getElementById('delete_procedimento_id');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

            // Variáveis de estado
            let currentSearchCriteria = {};
            const userPermissions = "<?= $_SESSION['user_permissions'] ?? '0000000000' ?>"; // Permissões do usuário logado
            const userTerritoryId = "<?= $_SESSION['user_territory_id'] ?? '' ?>"; // Território do usuário logado

            // --- Funções de Utilitário ---

            /**
             * Exibe uma mensagem de feedback para o usuário.
             * @param {string} message A mensagem a ser exibida.
             * @param {string} type O tipo de alerta (success, danger, info, warning).
             */
            function showFeedback(message, type = 'info') {
                const alertContainer = document.createElement('div');
                alertContainer.classList.add('alert', `alert-${type}`, 'alert-custom', 'text-center', 'mt-3');
                alertContainer.setAttribute('role', 'alert');
                alertContainer.textContent = message;
                document.querySelector('.container.container-list').prepend(alertContainer);

                setTimeout(() => {
                    alertContainer.remove();
                }, 5000); // Remove a mensagem após 5 segundos
            }

            /**
             * Converte um objeto simples para um objeto FormData.
             * @param {object} obj O objeto a ser convertido.
             * @returns {FormData} O objeto FormData.
             */
            function objectToFormData(obj) {
                const formData = new FormData();
                for (const key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        formData.append(key, obj[key]);
                    }
                }
                return formData;
            }

            /**
             * Busca dados de uma URL e retorna JSON.
             * @param {string} url A URL para buscar.
             * @returns {Promise<object>} O objeto JSON da resposta.
             */
            async function fetchData(url) {
                const response = await fetch(url);
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                }
                return await response.json();
            }

            /**
             * Envia dados via POST (JSON) para uma URL e retorna JSON.
             * @param {string} url A URL para enviar.
             * @param {object} data Os dados a serem enviados.
             * @returns {Promise<object>} O objeto JSON da resposta.
             */
            async function postData(url, data) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                }
                return await response.json();
            }

            /**
             * Preenche um select com opções.
             * @param {HTMLElement} selectElement O elemento <select>.
             * @param {Array<object>} options Os dados das opções (com id e nome).
             * @param {any} selectedValue O valor a ser pré-selecionado.
             * @param {string} defaultOptionText Texto da opção padrão (ex: "Selecione...").
             */
            function populateSelect(selectElement, options, selectedValue = null, defaultOptionText = 'Selecione...') {
                selectElement.innerHTML = '';
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = defaultOptionText;
                selectElement.appendChild(defaultOption);

                options.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.id;
                    opt.textContent = option.nome || option.descricao || option.usuario; // Adapta para diferentes modelos
                    if (selectedValue !== null && option.id == selectedValue) {
                        opt.selected = true;
                    }
                    selectElement.appendChild(opt);
                });
            }

            // --- Lógica de Permissões ---

            /**
             * Verifica se o usuário tem uma permissão específica.
             * @param {number} index O índice da permissão na string (0-based).
             * @returns {boolean} True se tiver a permissão, false caso contrário.
             */
            function hasPermission(index) {
                return userPermissions.length > index && userPermissions[index + 1] === '1';
            }

            /**
             * Atualiza a visibilidade dos botões com base nas permissões.
             */
            function updateButtonVisibility() {
                // Permissão para "Novo Procedimento" (A4)
                if (hasPermission(3)) { // Índice 3 para a 4ª permissão (A4)
                    newProcedimentoBtn.style.display = 'block';
                } else {
                    newProcedimentoBtn.style.display = 'none';
                }

                // Permissões para botões de ação na tabela
                const viewAllowed = hasPermission(0); // A1
                const editAllowed = hasPermission(1); // A2
                const deleteAllowed = hasPermission(2); // A3

                document.querySelectorAll('.view-btn').forEach(btn => {
                    btn.style.display = viewAllowed ? 'inline-block' : 'none';
                });
                document.querySelectorAll('.edit-btn').forEach(btn => {
                    btn.style.display = editAllowed ? 'inline-block' : 'none';
                });
                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.style.display = deleteAllowed ? 'inline-block' : 'none';
                });
            }

            // --- Lógica de Pesquisa ---

            /**
             * Limpa os outros campos de busca quando um é preenchido.
             */
            searchInputs.forEach(input => {
                input.addEventListener('input', function() {
                    searchInputs.forEach(otherInput => {
                        if (otherInput !== this) {
                            otherInput.value = '';
                        }
                    });
                });
            });

            /**
             * Realiza a pesquisa de procedimentos e atualiza a tabela.
             * @param {object} criteria Critérios de pesquisa.
             */
            async function performSearch(criteria) {
                currentSearchCriteria = criteria; // Armazena os critérios da pesquisa atual
                procedimentosTableBody.innerHTML = `<tr><td colspan="5" class="text-center">Carregando...</td></tr>`;

                // Constrói a query string
                const params = new URLSearchParams();
                for (const key in criteria) {
                    if (criteria[key]) {
                        params.append(key, criteria[key]);
                    }
                }

                try {
                    const procedimentos = await fetchData(`/procedimentos/search-json?${params.toString()}`);
                    
                    procedimentosTableBody.innerHTML = ''; // Limpa a tabela

                    if (procedimentos.length === 0) {
                        procedimentosTableBody.innerHTML = `<tr><td colspan="5" class="text-center">Não foi encontrado nenhum resultado para a pesquisa.</td></tr>`;
                    } else {
                        procedimentos.forEach(proc => {
                            const row = procedimentosTableBody.insertRow();
                            row.dataset.id = proc.id;
                            row.innerHTML = `
                                <td>${proc.numero_procedimento}/${proc.ano_procedimento}</td>
                                <td>${proc.nome_pessoa || 'N/A'}</td>
                                <td>${proc.nome_genitora_pessoa || 'N/A'}</td>
                                <td>${proc.data_nascimento_pessoa || 'N/A'}</td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm btn-custom view-btn" data-id="${proc.id}">Visualizar</button>
                                    <button type="button" class="btn btn-warning btn-sm btn-custom edit-btn" data-id="${proc.id}">Editar</button>
                                    <button type="button" class="btn btn-danger btn-sm btn-custom delete-btn" data-id="${proc.id}">Excluir</button>
                                </td>
                            `;
                        });
                    }
                    updateButtonVisibility(); // Atualiza visibilidade dos botões após carregar a tabela
                } catch (error) {
                    console.error("Erro ao realizar pesquisa:", error);
                    procedimentosTableBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Erro ao carregar resultados: ${error.message}</td></tr>`;
                }
            }

            // Evento de clique no botão Pesquisar
            searchBtn.addEventListener('click', function() {
                let criteria = {};
                let foundInput = false;
                searchInputs.forEach(input => {
                    if (input.value) {
                        criteria[input.name] = input.value;
                        foundInput = true;
                    }
                });

                if (!foundInput) {
                    procedimentosTableBody.innerHTML = `<tr><td colspan="5" class="text-center">Preencha um dos campos acima para pesquisar.</td></tr>`;
                    return;
                }
                performSearch(criteria);
            });

            // --- Lógica de Modais (Visualizar, Editar, Criar, Excluir) ---

            // Event listener para botões de ação na tabela (delegation)
            procedimentosTableBody.addEventListener('click', async function(event) {
                const target = event.target;
                if (target.classList.contains('view-btn')) {
                    const id = target.dataset.id;
                    await openViewModal(id);
                } else if (target.classList.contains('edit-btn')) {
                    const id = target.dataset.id;
                    await openEditModal(id);
                } else if (target.classList.contains('delete-btn')) {
                    const id = target.dataset.id;
                    const row = target.closest('tr');
                    const numeroAno = row.cells[0].textContent; // Pega o texto da primeira célula (Número/Ano)
                    openDeleteModal(id, numeroAno);
                }
            });

            // Abrir Modal de Novo Procedimento
            newProcedimentoBtn.addEventListener('click', async function() {
                procedimentoFormModalLabel.textContent = 'Novo Procedimento';
                saveProcedimentoBtn.textContent = 'Salvar';
                procedimentoForm.reset(); // Limpa o formulário
                formIdProcedimento.value = ''; // Garante que o ID esteja vazio para criação
                formNumeroProcedimento.disabled = false; // Habilita para criação
                formAnoProcedimento.disabled = false; // Habilita para criação

                // Garante que os campos de pessoa/genitora/demandante estejam vazios e não pré-selecionados
                formIdPessoa.value = '';
                formIdGenitoraPessoa.value = '';
                formIdDemandante.value = '';
                formDataNascimentoPessoa.value = '';
                formIdSexoPessoa.value = '';
                formDataNascimentoGenitora.value = '';
                formIdSexoGenitora.value = '';

                // Carregar todos os bairros ativos para o modal de criação
                try {
                    const allBairros = await fetchData('/bairros/listar-ativos-json');
                    populateSelect(formIdBairro, allBairros, null, 'Selecione o Bairro...');
                } catch (error) {
                    console.error("Erro ao carregar bairros para criação:", error);
                    showFeedback("Erro ao carregar bairros. " + error.message, 'danger');
                }

                // Carregar sexos para os selects
                try {
                    const sexos = await fetchData('/sexos/listar-json');
                    populateSelect(formIdSexoPessoa, sexos, null, 'Selecione o Sexo...');
                    populateSelect(formIdSexoGenitora, sexos, null, 'Selecione o Sexo...');
                } catch (error) {
                    console.error("Erro ao carregar sexos:", error);
                    showFeedback("Erro ao carregar opções de sexo. " + error.message, 'danger');
                }

                // Carregar todos os territórios para o modal de criação
                try {
                    const allTerritorios = await fetchData('/territorios/listar-json');
                    populateSelect(formIdTerritorio, allTerritorios, null, 'Selecione o Território...');
                } catch (error) {
                    console.error("Erro ao carregar territórios para criação:", error);
                    showFeedback("Erro ao carregar territórios. " + error.message, 'danger');
                }


                procedimentoFormModal.show();
            });

            // Abrir Modal de Visualização
            async function openViewModal(id) {
                try {
                    const procedimento = await fetchData(`/procedimentos/mostrar-json?id=${id}`);
                    if (procedimento) {
                        document.getElementById('view_id').textContent = procedimento.id;
                        document.getElementById('view_numero_procedimento').textContent = procedimento.numero_procedimento;
                        document.getElementById('view_ano_procedimento').textContent = procedimento.ano_procedimento;
                        document.getElementById('view_id_territorio').textContent = procedimento.nome_territorio || procedimento.id_territorio; // Exibe o nome
                        document.getElementById('view_id_bairro').textContent = procedimento.nome_bairro || procedimento.id_bairro || 'N/A';
                        document.getElementById('view_territorio_bairro_nome').textContent = procedimento.nome_territorio_bairro || 'N/A'; // Nome do território do bairro
                        document.getElementById('view_id_pessoa').textContent = procedimento.nome_pessoa || procedimento.id_pessoa || 'N/A';
                        document.getElementById('view_id_genitora_pessoa').textContent = procedimento.nome_genitora_pessoa || procedimento.id_genitora_pessoa || 'N/A';
                        document.getElementById('view_id_demandante').textContent = procedimento.nome_demandante || procedimento.id_demandante || 'N/A';
                        document.getElementById('view_ativo').textContent = (procedimento.ativo == 1) ? 'Sim' : 'Não';
                        document.getElementById('view_migrado').textContent = (procedimento.migrado == 1) ? 'Sim' : 'Não';
                        document.getElementById('view_id_migracao').textContent = procedimento.id_migracao || 'N/A';
                        document.getElementById('view_data_criacao').textContent = procedimento.data_criacao || 'N/A';
                        document.getElementById('view_hora_criacao').textContent = procedimento.hora_criacao || 'N/A';
                        document.getElementById('view_id_usuario_criacao').textContent = procedimento.nome_usuario_criacao || procedimento.id_usuario_criacao || 'N/A';
                        document.getElementById('view_data_hora_atualizacao').textContent = procedimento.data_hora_atualizacao || 'N/A';
                        document.getElementById('view_id_usuario_atualizacao').textContent = procedimento.nome_usuario_atualizacao || procedimento.id_usuario_atualizacao || 'N/A';

                        viewProcedimentoModal.show();
                    } else {
                        showFeedback('Procedimento não encontrado.', 'danger');
                    }
                } catch (error) {
                    console.error("Erro ao buscar detalhes do procedimento:", error);
                    showFeedback("Erro ao carregar detalhes do procedimento. " + error.message, 'danger');
                }
            }

            // Abrir Modal de Edição
            async function openEditModal(id) {
                procedimentoFormModalLabel.textContent = 'Editar Procedimento';
                saveProcedimentoBtn.textContent = 'Atualizar';
                formNumeroProcedimento.disabled = true; // Desabilita edição
                formAnoProcedimento.disabled = true; // Desabilita edição

                try {
                    const procedimento = await fetchData(`/procedimentos/mostrar-json?id=${id}`);
                    if (procedimento) {
                        formIdProcedimento.value = procedimento.id;
                        formNumeroProcedimento.value = procedimento.numero_procedimento;
                        formAnoProcedimento.value = procedimento.ano_procedimento;
                        formIdTerritorio.value = procedimento.id_territorio;

                        // Carregar bairros APENAS do território do procedimento
                        const bairrosDoTerritorio = await fetchData(`/bairros/listar-por-territorio-json?id_territorio=${procedimento.id_territorio}`);
                        populateSelect(formIdBairro, bairrosDoTerritorio, procedimento.id_bairro, 'Selecione o Bairro...');

                        formNomePessoa.value = procedimento.nome_pessoa || '';
                        formIdPessoa.value = procedimento.id_pessoa || '';
                        formDataNascimentoPessoa.value = procedimento.data_nascimento_pessoa || '';
                        formIdSexoPessoa.value = procedimento.id_sexo_pessoa || '';

                        formNomeGenitora.value = procedimento.nome_genitora_pessoa || '';
                        formIdGenitoraPessoa.value = procedimento.id_genitora_pessoa || '';
                        formDataNascimentoGenitora.value = procedimento.data_nascimento_genitora || '';
                        formIdSexoGenitora.value = procedimento.id_sexo_genitora || '';

                        formNomeDemandante.value = procedimento.nome_demandante || '';
                        formIdDemandante.value = procedimento.id_demandante || '';

                        formIdMigracao.value = procedimento.id_migracao || '';
                        formAtivo.checked = (procedimento.ativo == 1);
                        formMigrado.checked = (procedimento.migrado == 1);

                        // Carregar sexos para os selects
                        const sexos = await fetchData('/sexos/listar-json');
                        populateSelect(formIdSexoPessoa, sexos, procedimento.id_sexo_pessoa, 'Selecione o Sexo...');
                        populateSelect(formIdSexoGenitora, sexos, procedimento.id_sexo_genitora, 'Selecione o Sexo...');

                        // Carregar todos os territórios para o modal de edição (para que o select funcione)
                        const allTerritorios = await fetchData('/territorios/listar-json');
                        populateSelect(formIdTerritorio, allTerritorios, procedimento.id_territorio, 'Selecione o Território...');

                        procedimentoFormModal.show();
                    } else {
                        showFeedback('Procedimento não encontrado para edição.', 'danger');
                    }
                } catch (error) {
                    console.error("Erro ao buscar procedimento para edição:", error);
                    showFeedback("Erro ao carregar procedimento para edição. " + error.message, 'danger');
                }
            }

            // Abrir Modal de Exclusão
            function openDeleteModal(id, info) {
                deleteProcedimentoId.value = id;
                deleteProcedimentoInfo.textContent = info;
                deleteProcedimentoModal.show();
            }

            // --- Lógica de Submissão de Formulário (Criação/Edição) ---

            procedimentoForm.addEventListener('submit', async function(event) {
                event.preventDefault();

                const isEdit = formIdProcedimento.value !== '';
                const url = isEdit ? `/procedimentos/atualizar-json?id=${formIdProcedimento.value}` : '/procedimentos/salvar-json';

                const formData = new FormData(procedimentoForm);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });

                // Campos desabilitados não são incluídos no FormData, então adicione-os manualmente se forem necessários
                // Para edição, numero_procedimento e ano_procedimento não são alteráveis, então não precisam ser enviados.
                // Para criação, eles são habilitados e já estariam no formData.

                // Converte checkboxes para 0 ou 1
                data.ativo = formAtivo.checked ? 1 : 0;
                data.migrado = formMigrado.checked ? 1 : 0;

                try {
                    const result = await postData(url, data);
                    showFeedback(result.message, 'success');
                    procedimentoFormModal.hide();
                    // Refaz a pesquisa com os últimos critérios para atualizar a tabela
                    if (Object.keys(currentSearchCriteria).length > 0) {
                        performSearch(currentSearchCriteria);
                    } else {
                        // Se não havia pesquisa ativa, limpa a tabela para o estado inicial
                        procedimentosTableBody.innerHTML = `<tr><td colspan="5" class="text-center">Preencha um dos campos acima para pesquisar.</td></tr>`;
                    }
                } catch (error) {
                    console.error("Erro ao salvar/atualizar procedimento:", error);
                    showFeedback("Erro: " + error.message, 'danger');
                }
            });

            // Confirmação de exclusão
            confirmDeleteBtn.addEventListener('click', async function() {
                const id = deleteProcedimentoId.value;
                try {
                    const result = await postData(`/procedimentos/deletar-json?id=${id}`, {});
                    showFeedback(result.message, 'success');
                    deleteProcedimentoModal.hide();
                    // Refaz a pesquisa com os últimos critérios para atualizar a tabela
                    if (Object.keys(currentSearchCriteria).length > 0) {
                        performSearch(currentSearchCriteria);
                    } else {
                        procedimentosTableBody.innerHTML = `<tr><td colspan="5" class="text-center">Preencha um dos campos acima para pesquisar.</td></tr>`;
                    }
                } catch (error) {
                    console.error("Erro ao excluir procedimento:", error);
                    showFeedback("Erro: " + error.message, 'danger');
                }
            });

            // --- Lógica de Autocomplete para Pessoa, Genitora, Demandante ---

            const autocompleteInputs = document.querySelectorAll('.autocomplete-input');

            autocompleteInputs.forEach(input => {
                const resultsContainer = document.getElementById(input.dataset.targetId.replace('form_id_', 'autocomplete_results_'));
                const hiddenIdField = document.getElementById(input.dataset.targetId);
                const dataNascimentoField = input.dataset.targetDataNascimento ? document.getElementById(input.dataset.targetDataNascimento) : null;
                const sexoField = input.dataset.targetSexo ? document.getElementById(input.dataset.targetSexo) : null;
                const fieldType = input.dataset.fieldType; // 'pessoa', 'genitora', 'demandante'

                let timeout = null;

                input.addEventListener('input', function() {
                    clearTimeout(timeout);
                    const query = this.value;
                    resultsContainer.innerHTML = '';
                    hiddenIdField.value = ''; // Limpa o ID quando o nome é alterado
                    if (dataNascimentoField) dataNascimentoField.value = ''; // Limpa campos relacionados
                    if (sexoField) populateSelect(sexoField, [], null, 'Selecione o Sexo...'); // Limpa e reseta select de sexo

                    if (query.length >= 3) {
                        timeout = setTimeout(async () => {
                            try {
                                let url = '';
                                if (fieldType === 'pessoa' || fieldType === 'genitora') {
                                    url = `/pessoas/search-by-name-json?nome=${encodeURIComponent(query)}`;
                                } else if (fieldType === 'demandante') {
                                    url = `/demandantes/search-by-name-json?nome=${encodeURIComponent(query)}`;
                                }

                                const results = await fetchData(url);
                                resultsContainer.innerHTML = '';
                                if (results.length > 0) {
                                    results.forEach(item => {
                                        const div = document.createElement('div');
                                        div.textContent = item.nome || item.usuario; // Adapta para nome/usuario
                                        div.dataset.id = item.id;
                                        if (item.data_nascimento) div.dataset.dataNascimento = item.data_nascimento;
                                        if (item.id_sexo) div.dataset.idSexo = item.id_sexo;
                                        div.addEventListener('click', function() {
                                            input.value = this.textContent;
                                            hiddenIdField.value = this.dataset.id;
                                            if (dataNascimentoField) dataNascimentoField.value = this.dataset.dataNascimento || '';
                                            if (sexoField) populateSelect(sexoField, sexosOptions, this.dataset.idSexo, 'Selecione o Sexo...');
                                            resultsContainer.innerHTML = ''; // Limpa os resultados
                                        });
                                        resultsContainer.appendChild(div);
                                    });
                                } else {
                                    resultsContainer.innerHTML = '<div class="text-muted">Nenhum resultado encontrado.</div>';
                                }
                            } catch (error) {
                                console.error("Erro no autocomplete:", error);
                                resultsContainer.innerHTML = '<div class="text-danger">Erro ao buscar.</div>';
                            }
                        }, 300); // Atraso de 300ms
                    }
                });

                // Esconder resultados quando o input perde o foco
                input.addEventListener('blur', function() {
                    setTimeout(() => {
                        resultsContainer.innerHTML = '';
                    }, 200); // Pequeno atraso para permitir clique nos resultados
                });
            });

            // Lógica para carregar bairros ao mudar o território no modal de edição
            formIdTerritorio.addEventListener('change', async function() {
                const territorioId = this.value;
                if (territorioId) {
                    try {
                        const bairros = await fetchData(`/bairros/listar-por-territorio-json?id_territorio=${territorioId}`);
                        populateSelect(formIdBairro, bairros, null, 'Selecione o Bairro...');
                    } catch (error) {
                        console.error("Erro ao carregar bairros por território:", error);
                        showFeedback("Erro ao carregar bairros para o território selecionado. " + error.message, 'danger');
                    }
                } else {
                    formIdBairro.innerHTML = '<option value="">Selecione o Bairro...</option>';
                }
            });

            // Variável global para armazenar opções de sexo (carregadas uma vez)
            let sexosOptions = [];
            async function loadSexosOptions() {
                try {
                    sexosOptions = await fetchData('/sexos/listar-json');
                } catch (error) {
                    console.error("Erro ao carregar opções de sexo:", error);
                    showFeedback("Erro ao carregar opções de sexo. " + error.message, 'danger');
                }
            }

            // --- Inicialização ---
            updateButtonVisibility(); // Define a visibilidade inicial dos botões
            loadSexosOptions(); // Carrega as opções de sexo uma vez

            // Se a página for carregada com dados (primeira vez sem pesquisa), exibe a mensagem padrão
            <?php if (empty($dados)): ?>
                procedimentosTableBody.innerHTML = `<tr><td colspan="5" class="text-center">Preencha um dos campos acima para pesquisar.</td></tr>`;
            <?php else: ?>
                // Se houver dados iniciais (ex: vindo do controller sem filtro), exiba-os
                // No seu caso, a lógica de pesquisa inicial é vazia, então o else não seria ativado por padrão
                // Mas se você quisesse carregar todos os ativos do território do usuário na carga inicial:
                // performSearch({ id_territorio: userTerritoryId }); // Descomente e ajuste se quiser carregar na inicialização
            <?php endif; ?>
        });
    </script>
</body>
</html>
