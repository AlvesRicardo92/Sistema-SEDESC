<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de [Nome da Entidade]</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Link para o CSS externo -->
</head>
<body>
    <div class="container container-list card card-custom p-4">
        <h1 class="text-center mb-4 text-primary">Lista de [Nome da Entidade]</h1>

        <?php if (empty($dados)): // $dados seria a variável passada pelo Controller (ex: $bairros, $pessoas) ?>
            <div class="alert alert-info text-center alert-custom" role="alert">
                Nenhum [Nome da Entidade] encontrado.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle table-custom">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nome</th>
                            <th scope="col">Ativo</th>
                            <th scope="col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados as $item): // Loop através dos dados ?>
                            <tr>
                                <td><?= htmlspecialchars($item->id) ?></td>
                                <td><?= htmlspecialchars($item->nome ?? $item->descricao ?? $item->usuario) ?></td>
                                <td><?= ($item->ativo ?? 1) ? 'Sim' : 'Não' ?></td>
                                <td>
                                    <a href="/[entidade]/mostrar?id=<?= htmlspecialchars($item->id) ?>" class="btn btn-info btn-sm btn-custom">Ver Detalhes</a>
                                    <!-- Exemplo de link para editar: -->
                                    <!-- <a href="/[entidade]/atualizar?id=<?= htmlspecialchars($item->id) ?>" class="btn btn-warning btn-sm btn-custom">Editar</a> -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="/dashboard" class="btn btn-secondary btn-custom">Voltar ao Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
