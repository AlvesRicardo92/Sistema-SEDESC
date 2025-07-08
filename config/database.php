<?php
// config/database.php

// Função simples para carregar variáveis de ambiente de um arquivo .env
function carregarEnv($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("Arquivo .env não encontrado em: " . $filePath);
    }
    $linhas = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if (str_starts_with($linha, '#') || empty($linha)) {
            continue;
        }
        list($key, $value) = explode('=', $linha, 2);
        $_ENV[trim($key)] = trim($value);
        //$_SERVER[trim($key)] = trim($value); // Opcional, mas útil para web servers
    }
}

// Carrega o arquivo .env a partir da raiz do projeto
loadEnv(__DIR__ . '/../.env');

return [
    'host' => $_ENV['DB_HOST'] ?? '',
    'dbname' => $_ENV['DB_NAME'] ?? '',
    'user' => $_ENV['DB_USER'] ?? '',
    'password' => $_ENV['DB_PASS'] ?? ''
];