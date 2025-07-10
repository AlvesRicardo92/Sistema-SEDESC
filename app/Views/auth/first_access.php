<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Primeiro Acesso - Trocar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Link para o CSS externo -->
</head>
<body class="auth-page">
    <div class="container card card-custom p-4">
        <h1 class="text-center mb-4 text-primary">Primeiro Acesso</h1>
        <p class="text-center text-muted mb-4">Por favor, defina uma nova senha para sua conta.</p>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center alert-custom" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success text-center alert-custom" role="alert">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="/authenticate-first-access" method="POST">
            <div class="mb-3">
                <label for="new_password" class="form-label">Nova Senha:</label>
                <input type="password" class="form-control form-control-custom" id="new_password" name="new_password" required minlength="6">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmar Nova Senha:</label>
                <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-custom">Alterar Senha</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
