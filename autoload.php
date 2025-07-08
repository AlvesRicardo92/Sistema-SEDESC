<?php
// autoload.php

spl_autoload_register(function ($nome_classe) {
    // Definir o prefixo do seu namespace e o diretório base correspondente.
    $namespace_prefix = 'App\\';
    $pasta_app = __DIR__ . '/app/'; // Caminho absoluto para a pasta 'app'

    // Verificar se o namespace da classe começa com o prefixo
    $comprimento = strlen($namespace_prefix);
    if (strncmp($namespace_prefix, $nome_classe, $comprimento) !== 0) {
        return; // Não é uma classe do nosso namespace App, ignore.
    }

    // Obter o nome relativo da classe (sem o prefixo do namespace).
    $classe_relativa = substr($nome_classe, $comprimento);

    // Converter o nome relativo da classe para o caminho do arquivo.
    $arquivo = $pasta_app . str_replace('\\', '/', $classe_relativa) . '.php';

    // Verificar se o arquivo existe e incluí-lo.
    if (file_exists($arquivo)) {
        require_once $arquivo;
    }
});