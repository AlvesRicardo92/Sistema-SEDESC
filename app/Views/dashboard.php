<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Link para o CSS externo -->
</head>
<body>
    <!-- Navbar Superior -->
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

    <!-- Conteúdo Principal - Carrossel de Avisos -->
    <div class="carousel-container">
        <h1 class="text-3xl font-bold mb-4 text-primary">Avisos Importantes</h1>

        <?php if (empty($avisos)): ?>
            <div class="alert alert-info text-center alert-custom" role="alert">
                Nenhum aviso disponível no momento.
            </div>
        <?php else: ?>
            <div id="avisosCarousel" class="carousel slide w-75" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach ($avisos as $index => $aviso): ?>
                        <button type="button" data-bs-target="#avisosCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner rounded-3 shadow-lg">
                    <?php foreach ($avisos as $index => $aviso): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <?php if (!empty($aviso->nome_imagem)): ?>
                                <img src="/assets/images/<?= htmlspecialchars($aviso->nome_imagem) ?>" class="d-block w-100" alt="Imagem do Aviso">
                            <?php else: ?>
                                <div class="d-flex justify-content-center align-items-center bg-secondary text-white" style="height: 400px;">
                                    <span></span>
                                </div>
                            <?php endif; ?>
                            <div class="carousel-caption d-none d-md-block">
                                <h5>Aviso</h5>
                                <p><?= htmlspecialchars($aviso->descricao) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#avisosCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#avisosCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Próximo</span>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
