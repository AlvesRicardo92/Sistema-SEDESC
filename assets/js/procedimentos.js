// assets/js/procedimentos.js

$(document).ready(function() {
    // Variáveis globais para os modais
    const procedimentoFormModal = new bootstrap.Modal($('#procedimentoFormModal')[0]);
    const viewProcedimentoModal = new bootstrap.Modal($('#viewProcedimentoModal')[0]);
    const deleteProcedimentoModal = new bootstrap.Modal($('#deleteProcedimentoModal')[0]);

    // Elementos do formulário de criação/edição
    const procedimentoForm = $('#procedimentoForm');
    const formIdProcedimento = $('#form_id_procedimento');
    const formNumeroProcedimento = $('#form_numero_procedimento');
    const formAnoProcedimento = $('#form_ano_procedimento');
    const formIdTerritorio = $('#form_id_territorio');
    const formIdBairro = $('#form_id_bairro');
    const formNomePessoa = $('#form_nome_pessoa');
    const formIdPessoa = $('#form_id_pessoa');
    const formDataNascimentoPessoa = $('#form_data_nascimento_pessoa');
    const formIdSexoPessoa = $('#form_id_sexo_pessoa');
    const formNomeGenitora = $('#form_nome_genitora');
    const formIdGenitoraPessoa = $('#form_id_genitora_pessoa');
    const formDataNascimentoGenitora = $('#form_data_nascimento_genitora');
    const formIdSexoGenitora = $('#form_id_sexo_genitora');
    const formNomeDemandante = $('#form_nome_demandante');
    const formIdDemandante = $('#form_id_demandante');
    const formIdMigracao = $('#form_id_migracao');
    const formAtivo = $('#form_ativo');
    const formMigrado = $('#form_migrado');
    const saveProcedimentoBtn = $('#saveProcedimentoBtn');
    const procedimentoFormModalLabel = $('#procedimentoFormModalLabel');

    // Elementos da tabela e botões de ação
    const searchInputs = $('.search-input');
    const searchBtn = $('#searchBtn');
    const newProcedimentoBtn = $('#newProcedimentoBtn');
    const procedimentosTableBody = $('#procedimentosTableBody');
    const deleteProcedimentoInfo = $('#delete_procedimento_info');
    const deleteProcedimentoToken = $('#delete_procedimento_token');
    const confirmDeleteBtn = $('#confirmDeleteBtn');

    // Variáveis de estado (agora globais no escopo do arquivo JS)
    let currentSearchCriteria = {};
    // userPermissions e userTerritoryId são passadas via variável global no HTML
    // const userPermissions = window.userPermissions;
    // const userTerritoryId = window.userTerritoryId;


    // --- Funções de Utilitário ---

    /**
     * Exibe uma mensagem de feedback para o usuário.
     * @param {string} message A mensagem a ser exibida.
     * @param {string} type O tipo de alerta (success, danger, info, warning).
     */
    function showFeedback(message, type = 'info') {
        const alertContainer = $('<div>')
            .addClass('alert alert-custom text-center mt-3')
            .addClass(`alert-${type}`)
            .attr('role', 'alert')
            .text(message);
        $('.container.container-list').prepend(alertContainer);

        setTimeout(() => {
            alertContainer.remove();
        }, 5000); // Remove a mensagem após 5 segundos
    }

    /**
     * Busca dados de uma URL e retorna JSON.
     * @param {string} url A URL para buscar.
     * @returns {Promise<object>} O objeto JSON da resposta.
     */
    async function fetchData(url) {
        try {
            const response = await $.ajax({ url: url, method: 'GET' });
            return response;
        } catch (jqXHR) {
            const errorData = jqXHR.responseJSON || { message: 'Erro desconhecido.' };
            throw new Error(errorData.message || `HTTP error! status: ${jqXHR.status}`);
        }
    }

    /**
     * Envia dados via POST (JSON) para uma URL e retorna JSON.
     * @param {string} url A URL para enviar.
     * @param {object} data Os dados a serem enviados.
     * @returns {Promise<object>} O objeto JSON da resposta.
     */
    async function postData(url, data) {
        try {
            const response = await $.ajax({
                url: url,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data)
            });
            return response;
        } catch (jqXHR) {
            const errorData = jqXHR.responseJSON || { message: 'Erro desconhecido.' };
            throw new Error(errorData.message || `HTTP error! status: ${jqXHR.status}`);
        }
    }

    /**
     * Preenche um select com opções.
     * @param {jQuery} selectElement O elemento <select> jQuery.
     * @param {Array<object>} options Os dados das opções (com id e nome).
     * @param {any} selectedValue O valor a ser pré-selecionado.
     * @param {string} defaultOptionText Texto da opção padrão (ex: "Selecione...").
     */
    function populateSelect(selectElement, options, selectedValue = null, defaultOptionText = 'Selecione...') {
        selectElement.empty();
        $('<option>').val('').text(defaultOptionText).appendTo(selectElement);

        $.each(options, (index, option) => {
            const opt = $('<option>')
                .val(option.id)
                .text(option.nome || option.descricao || option.usuario); // Adapta para diferentes modelos
            if (selectedValue !== null && option.id == selectedValue) {
                opt.prop('selected', true);
            }
            selectElement.append(opt);
        });
    }

    // --- Lógica de Permissões ---

    /**
     * Verifica se o usuário tem uma permissão específica.
     * @param {number} index O índice da permissão na string (0-based).
     * @returns {boolean} True se tiver a permissão, false caso contrário.
     */
    function hasPermission(index) {
        // userPermissions é uma variável global definida no HTML antes de carregar este script
        return window.userPermissions.length > index && window.userPermissions[index + 1] === '1';
    }

    /**
     * Atualiza a visibilidade dos botões com base nas permissões.
     */
    function updateButtonVisibility() {
        // Permissão para "Novo Procedimento" (A4)
        if (hasPermission(3)) { // Índice 3 para a 4ª permissão (A4)
            $('#newProcedimentoBtn').show();
        } else {
            $('#newProcedimentoBtn').hide();
        }

        // Permissões para botões de ação na tabela
        const viewAllowed = hasPermission(0); // A1
        const editAllowed = hasPermission(1); // A2
        const deleteAllowed = hasPermission(2); // A3

        $('.view-btn').css('display', viewAllowed ? 'inline-block' : 'none');
        $('.edit-btn').css('display', editAllowed ? 'inline-block' : 'none');
        $('.delete-btn').css('display', deleteAllowed ? 'inline-block' : 'none');
    }

    // --- Lógica de Pesquisa ---

    /**
     * Limpa os outros campos de busca quando um é preenchido.
     */
    searchInputs.on('input', function() {
        const currentInput = $(this);
        searchInputs.not(currentInput).val('');
    });

    /**
     * Realiza a pesquisa de procedimentos e atualiza a tabela.
     * @param {object} criteria Critérios de pesquisa.
     */
    async function performSearch(criteria) {
        currentSearchCriteria = criteria; // Armazena os critérios da pesquisa atual
        procedimentosTableBody.html(`<tr><td colspan="5" class="text-center">Carregando...</td></tr>`);

        // Constrói a query string
        const params = new URLSearchParams(criteria);

        try {
            const procedimentos = await fetchData(`/procedimentos/search-json?${params.toString()}`);
            
            procedimentosTableBody.empty(); // Limpa a tabela

            if (procedimentos.length === 0) {
                procedimentosTableBody.html(`<tr><td colspan="5" class="text-center">Não foi encontrado nenhum resultado para a pesquisa.</td></tr>`);
            } else {
                $.each(procedimentos, (index, proc) => {
                    const row = $('<tr>').attr('data-id', proc.id); // Guardamos o ID real aqui, mas usaremos o token para ações
                    row.html(`
                        <td>${proc.numero_procedimento}/${proc.ano_procedimento}</td>
                        <td>${proc.nome_pessoa || 'N/A'}</td>
                        <td>${proc.nome_genitora_pessoa || 'N/A'}</td>
                        <td>${proc.data_nascimento_pessoa || 'N/A'}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm btn-custom view-btn" data-token="${proc.token}">Visualizar</button>
                            <button type="button" class="btn btn-warning btn-sm btn-custom edit-btn" data-token="${proc.token}">Editar</button>
                            <button type="button" class="btn btn-danger btn-sm btn-custom delete-btn" data-token="${proc.token}">Excluir</button>
                        </td>
                    `);
                    procedimentosTableBody.append(row);
                });
            }
            updateButtonVisibility(); // Atualiza visibilidade dos botões após carregar a tabela
        } catch (error) {
            console.error("Erro ao realizar pesquisa:", error);
            procedimentosTableBody.html(`<tr><td colspan="5" class="text-center text-danger">Erro ao carregar resultados: ${error.message}</td></tr>`);
        }
    }

    // Evento de clique no botão Pesquisar
    searchBtn.on('click', function() {
        let criteria = {};
        let foundInput = false;
        searchInputs.each(function() {
            if ($(this).val()) {
                criteria[$(this).attr('name')] = $(this).val();
                foundInput = true;
            }
        });

        if (!foundInput) {
            procedimentosTableBody.html(`<tr><td colspan="5" class="text-center">Preencha um dos campos acima para pesquisar.</td></tr>`);
            return;
        }
        performSearch(criteria);
    });

    // --- Lógica de Modais (Visualizar, Editar, Criar, Excluir) ---

    // Event listener para botões de ação na tabela (delegation)
    procedimentosTableBody.on('click', '.view-btn', async function() {
        const token = $(this).data('token');
        await openViewModal(token);
    });

    procedimentosTableBody.on('click', '.edit-btn', async function() {
        const token = $(this).data('token');
        await openEditModal(token);
    });

    procedimentosTableBody.on('click', '.delete-btn', function() {
        const token = $(this).data('token');
        const row = $(this).closest('tr');
        const numeroAno = row.find('td:eq(0)').text(); // Pega o texto da primeira célula (Número/Ano)
        openDeleteModal(token, numeroAno);
    });

    // Abrir Modal de Novo Procedimento
    $('#newProcedimentoBtn').on('click', async function() {
        procedimentoFormModalLabel.text('Novo Procedimento');
        saveProcedimentoBtn.text('Salvar');
        procedimentoForm[0].reset(); // Limpa o formulário
        formIdProcedimento.val(''); // Garante que o ID esteja vazio para criação
        formNumeroProcedimento.prop('disabled', false); // Habilita para criação
        formAnoProcedimento.prop('disabled', false); // Habilita para criação

        // Garante que os campos de pessoa/genitora/demandante estejam vazios e não pré-selecionados
        formIdPessoa.val('');
        formIdGenitoraPessoa.val('');
        formIdDemandante.val('');
        formDataNascimentoPessoa.val('');
        formIdSexoPessoa.val('');
        formDataNascimentoGenitora.val('');
        formIdSexoGenitora.val('');

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
    async function openViewModal(token) {
        try {
            const procedimento = await fetchData(`/procedimentos/mostrar-json?token=${token}`);
            if (procedimento) {
                $('#view_id').text(procedimento.id);
                $('#view_numero_procedimento').text(procedimento.numero_procedimento);
                $('#view_ano_procedimento').text(procedimento.ano_procedimento);
                $('#view_id_territorio').text(procedimento.nome_territorio || procedimento.id_territorio); // Exibe o nome
                $('#view_id_bairro').text(procedimento.nome_bairro || procedimento.id_bairro || 'N/A');
                $('#view_territorio_bairro_nome').text(procedimento.nome_territorio_bairro || 'N/A'); // Nome do território do bairro
                $('#view_id_pessoa').text(procedimento.nome_pessoa || procedimento.id_pessoa || 'N/A');
                $('#view_id_genitora_pessoa').text(procedimento.nome_genitora_pessoa || procedimento.id_genitora_pessoa || 'N/A');
                $('#view_id_demandante').text(procedimento.nome_demandante || procedimento.id_demandante || 'N/A');
                $('#view_ativo').text((procedimento.ativo == 1) ? 'Sim' : 'Não');
                $('#view_migrado').text((procedimento.migrado == 1) ? 'Sim' : 'Não');
                $('#view_id_migracao').text(procedimento.id_migracao || 'N/A');
                $('#view_data_criacao').text(procedimento.data_criacao || 'N/A');
                $('#view_hora_criacao').text(procedimento.hora_criacao || 'N/A');
                $('#view_id_usuario_criacao').text(procedimento.nome_usuario_criacao || procedimento.id_usuario_criacao || 'N/A');
                $('#view_data_hora_atualizacao').text(procedimento.data_hora_atualizacao || 'N/A');
                $('#view_id_usuario_atualizacao').text(procedimento.nome_usuario_atualizacao || procedimento.id_usuario_atualizacao || 'N/A');

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
    async function openEditModal(token) {
        procedimentoFormModalLabel.text('Editar Procedimento');
        saveProcedimentoBtn.text('Atualizar');
        formNumeroProcedimento.prop('disabled', true); // Desabilita edição
        formAnoProcedimento.prop('disabled', true); // Desabilita edição

        try {
            const procedimento = await fetchData(`/procedimentos/mostrar-json?token=${token}`);
            if (procedimento) {
                formIdProcedimento.val(procedimento.id); // O ID real é armazenado aqui para o POST
                formNumeroProcedimento.val(procedimento.numero_procedimento);
                formAnoProcedimento.val(procedimento.ano_procedimento);
                formIdTerritorio.val(procedimento.id_territorio);

                // Carregar bairros APENAS do território do procedimento
                const bairrosDoTerritorio = await fetchData(`/bairros/listar-por-territorio-json?id_territorio=${procedimento.id_territorio}`);
                populateSelect(formIdBairro, bairrosDoTerritorio, procedimento.id_bairro, 'Selecione o Bairro...');

                formNomePessoa.val(procedimento.nome_pessoa || '');
                formIdPessoa.val(procedimento.id_pessoa || '');
                formDataNascimentoPessoa.val(procedimento.data_nascimento_pessoa || '');
                formIdSexoPessoa.val(procedimento.id_sexo_pessoa || '');

                formNomeGenitora.val(procedimento.nome_genitora_pessoa || '');
                formIdGenitoraPessoa.val(procedimento.id_genitora_pessoa || '');
                formDataNascimentoGenitora.val(procedimento.data_nascimento_genitora || '');
                formIdSexoGenitora.val(procedimento.id_sexo_genitora || '');

                formNomeDemandante.val(procedimento.nome_demandante || '');
                formIdDemandante.val(procedimento.id_demandante || '');

                formIdMigracao.val(procedimento.id_migracao || '');
                formAtivo.prop('checked', (procedimento.ativo == 1));
                formMigrado.prop('checked', (procedimento.migrado == 1));

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
    function openDeleteModal(token, info) {
        deleteProcedimentoToken.val(token); // Armazena o token
        deleteProcedimentoInfo.text(info);
        deleteProcedimentoModal.show();
    }

    // --- Lógica de Submissão de Formulário (Criação/Edição) ---

    procedimentoForm.on('submit', async function(event) {
        event.preventDefault();

        const isEdit = formIdProcedimento.val() !== '';
        // Para atualização, enviamos o ID real do procedimento
        const id = formIdProcedimento.val();
        const url = isEdit ? `/procedimentos/atualizar-json?id=${id}` : '/procedimentos/salvar-json';

        const data = {};
        $(this).serializeArray().forEach(item => {
            data[item.name] = item.value;
        });

        // Converte checkboxes para 0 ou 1
        data.ativo = formAtivo.is(':checked') ? 1 : 0;
        data.migrado = formMigrado.is(':checked') ? 1 : 0;

        try {
            const result = await postData(url, data);
            showFeedback(result.message, 'success');
            procedimentoFormModal.hide();
            // Refaz a pesquisa com os últimos critérios para atualizar a tabela
            if (Object.keys(currentSearchCriteria).length > 0) {
                performSearch(currentSearchCriteria);
            } else {
                // Se não havia pesquisa ativa, limpa a tabela para o estado inicial
                procedimentosTableBody.html(`<tr><td colspan="5" class="text-center">Preencha um dos campos acima para pesquisar.</td></tr>`);
            }
        } catch (error) {
            console.error("Erro ao salvar/atualizar procedimento:", error);
            showFeedback("Erro: " + error.message, 'danger');
        }
    });

    // Confirmação de exclusão
    confirmDeleteBtn.on('click', async function() {
        const token = deleteProcedimentoToken.val(); // Pega o token
        try {
            const result = await postData(`/procedimentos/deletar-json?token=${token}`, {}); // Envia o token
            showFeedback(result.message, 'success');
            deleteProcedimentoModal.hide();
            // Refaz a pesquisa com os últimos critérios para atualizar a tabela
            if (Object.keys(currentSearchCriteria).length > 0) {
                performSearch(currentSearchCriteria);
            } else {
                procedimentosTableBody.html(`<tr><td colspan="5" class="text-center">Preencha um dos campos acima para pesquisar.</td></tr>`);
            }
        } catch (error) {
            console.error("Erro ao excluir procedimento:", error);
            showFeedback("Erro: " + error.message, 'danger');
        }
    });

    // --- Lógica de Autocomplete para Pessoa, Genitora, Demandante ---

    const autocompleteInputs = $('.autocomplete-input');

    autocompleteInputs.each(function() {
        const input = $(this);
        const resultsContainer = $(`#${input.data('target-id').replace('form_id_', 'autocomplete_results_')}`);
        const hiddenIdField = $(`#${input.data('target-id')}`);
        const dataNascimentoField = input.data('target-data-nascimento') ? $(`#${input.data('target-data-nascimento')}`) : null;
        const sexoField = input.data('target-sexo') ? $(`#${input.data('target-sexo')}`) : null;
        const fieldType = input.data('field-type'); // 'pessoa', 'genitora', 'demandante'

        let timeout = null;

        input.on('input', function() {
            clearTimeout(timeout);
            const query = input.val();
            resultsContainer.empty();
            hiddenIdField.val(''); // Limpa o ID quando o nome é alterado
            if (dataNascimentoField) dataNascimentoField.val(''); // Limpa campos relacionados
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
                        resultsContainer.empty();
                        if (results.length > 0) {
                            $.each(results, (index, item) => {
                                const div = $('<div>')
                                    .text(item.nome || item.usuario) // Adapta para nome/usuario
                                    .data('id', item.id);
                                if (item.data_nascimento) div.data('dataNascimento', item.data_nascimento);
                                if (item.id_sexo) div.data('idSexo', item.id_sexo);
                                
                                div.on('click', function() {
                                    input.val($(this).text());
                                    hiddenIdField.val($(this).data('id'));
                                    if (dataNascimentoField) dataNascimentoField.val($(this).data('dataNascimento') || '');
                                    if (sexoField) populateSelect(sexoField, sexosOptions, $(this).data('idSexo'), 'Selecione o Sexo...');
                                    resultsContainer.empty(); // Limpa os resultados
                                });
                                resultsContainer.append(div);
                            });
                        } else {
                            resultsContainer.html('<div class="text-muted">Nenhum resultado encontrado.</div>');
                        }
                    } catch (error) {
                        console.error("Erro no autocomplete:", error);
                        resultsContainer.html('<div class="text-danger">Erro ao buscar.</div>');
                    }
                }, 300); // Atraso de 300ms
            }
        });

        // Esconder resultados quando o input perde o foco
        input.on('blur', function() {
            setTimeout(() => {
                resultsContainer.empty();
            }, 200); // Pequeno atraso para permitir clique nos resultados
        });
    });

    // Lógica para carregar bairros ao mudar o território no modal de edição
    formIdTerritorio.on('change', async function() {
        const territorioId = $(this).val();
        if (territorioId) {
            try {
                const bairros = await fetchData(`/bairros/listar-por-territorio-json?id_territorio=${territorioId}`);
                populateSelect(formIdBairro, bairros, null, 'Selecione o Bairro...');
            } catch (error) {
                console.error("Erro ao carregar bairros por território:", error);
                showFeedback("Erro ao carregar bairros para o território selecionado. " + error.message, 'danger');
            }
        } else {
            formIdBairro.html('<option value="">Selecione o Bairro...</option>');
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

    // Limpar formulários ao fechar os modais
    $('#procedimentoFormModal').on('hidden.bs.modal', function () {
        procedimentoForm[0].reset();
    });
});
