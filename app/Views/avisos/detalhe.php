<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do [Nome da Entidade]</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Link para o CSS externo -->
</head>
<body>
    <div class="container card card-custom p-4">
        <h1 class="text-center mb-4 text-primary">Detalhes do [Nome da Entidade]</h1>

        <?php if ($item): // $item seria a variável passada pelo Controller (ex: $bairro, $pessoa) ?>
            <ul class="list-group list-group-flush">
                <li class="list-group-item list-group-item-custom d-flex justify-content-between align-items-center">
                    <strong>ID:</strong> <span><?= htmlspecialchars($item->id) ?></span>
                </li>
                <li class="list-group-item list-group-item-custom d-flex justify-content-between align-items-center">
                    <strong>Nome:</strong> <span><?= htmlspecialchars($item->nome ?? $item->descricao ?? $item->usuario) ?></span>
                </li>
                <li class="list-group-item list-group-item-custom d-flex justify-content-between align-items-center">
                    <strong>Ativo:</strong> <span><?= ($item->ativo ?? 1) ? 'Sim' : 'Não' ?></span>
                </li>
                <?php
                // Exemplo de como adicionar mais campos dinamicamente
                // Adapte conforme as propriedades de cada modelo
                $specificFields = [
                    'territorio_id' => 'ID Território',
                    'data_nascimento' => 'Data de Nascimento',
                    'id_sexo' => 'ID Sexo',
                    'sigla' => 'Sigla',
                    'numero_procedimento' => 'Número Procedimento',
                    'ano_procedimento' => 'Ano Procedimento',
                    'id_territorio' => 'ID Território',
                    'id_bairro' => 'ID Bairro',
                    'id_pessoa' => 'ID Pessoa',
                    'id_genitora_pessoa' => 'ID Genitora',
                    'id_demandante' => 'ID Demandante',
                    'migrado' => 'Migrado',
                    'id_migracao' => 'ID Migração',
                    'data_criacao' => 'Data Criação',
                    'hora_criacao' => 'Hora Criação',
                    'permissoes' => 'Permissões',
                    'primeiro_acesso' => 'Primeiro Acesso',
                    'id_territorio_exibicao' => 'Território Exibição',
                    'data_inicio_exibicao' => 'Início Exibição',
                    'data_fim_exibicao' => 'Fim Exibição',
                    'nome_imagem' => 'Nome Imagem',
                    'nome_tabela' => 'Nome Tabela',
                    'acao' => 'Ação',
                    'dados_antigos' => 'Dados Antigos',
                    'dados_novos' => 'Dados Novos',
                    'id_usuario_acao' => 'ID Usuário Ação',
                    'numero_antigo' => 'Número Antigo',
                    'ano_antigo' => 'Ano Antigo',
                    'territorio_antigo' => 'Território Antigo',
                    'numero_novo' => 'Número Novo',
                    'ano_novo' => 'Ano Novo',
                    'territorio_novo' => 'Território Novo',
                    'id_motivo_migracao' => 'ID Motivo Migração',
                    'usuario' => 'Usuário',
                    'data_hora_criacao' => 'Data/Hora Criação',
                    'id_usuario_criacao' => 'ID Usuário Criação',
                    'data_hora_atualizacao' => 'Data/Hora Atualização',
                    'id_usuario_atualizacao' => 'ID Usuário Atualização',
                ];

                foreach ($specificFields as $prop => $label) {
                    if (property_exists($item, $prop) && $item->$prop !== null && $item->$prop !== '') {
                        $value = $item->$prop;
                        // Formatação especial para JSON
                        if ($prop === 'dados_antigos' || $prop === 'dados_novos') {
                            $value = json_decode($value, true);
                            $value = '<pre class="bg-light p-2 rounded">' . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
                        } elseif ($prop === 'primeiro_acesso' || $prop === 'migrado') {
                            $value = $value ? 'Sim' : 'Não';
                        }
                        echo "<li class='list-group-item list-group-item-custom d-flex justify-content-between align-items-center'>";
                        echo "<strong>{$label}:</strong> <span>{$value}</span>";
                        echo "</li>";
                    }
                }
                ?>
            </ul>

            <div class="text-center mt-4">
                <a href="/[entidade]/listar" class="btn btn-secondary btn-custom me-2">Voltar à Lista</a>
                <!-- Exemplo de link para editar: -->
                <!-- <a href="/[entidade]/atualizar?id=<?= htmlspecialchars($item->id) ?>" class="btn btn-primary btn-custom">Editar</a> -->
            </div>
        <?php else: ?>
            <div class="alert alert-danger text-center alert-custom" role="alert">
                [Nome da Entidade] não encontrado.
            </div>
            <div class="text-center mt-4">
                <a href="/[entidade]/listar" class="btn btn-secondary btn-custom">Voltar à Lista</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

