<?php
// autoload.php

spl_autoload_register(function ($nomeClasse) {
    // Definir o prefixo do seu namespace e o diretório base correspondente.
    $namespacePrefix = 'App\\';
    $appDir = __DIR__ . '/app/'; // Caminho absoluto para a pasta 'app'

    // Verificar se o namespace da classe começa com o prefixo
    $comprimento = strlen($namespacePrefix);
    if (strncmp($namespacePrefix, $nomeClasse, $comprimento) !== 0) {
        return; // Não é uma classe do nosso namespace App, ignore.
    }

    // Obter o nome relativo da classe (sem o prefixo do namespace).
    $relativeClass = substr($nomeClasse, $comprimento);

    // Converter o nome relativo da classe para o caminho do arquivo.
    $arquivo = $appDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Verificar se o arquivo existe e incluí-lo.
    if (file_exists($arquivo)) {
        require_once $arquivo;
    }
});