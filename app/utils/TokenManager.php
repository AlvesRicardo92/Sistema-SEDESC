<?php

namespace App\Utils;

/**
 * Gerencia a criação e validação de tokens temporários para IDs.
 * Os tokens são armazenados na sessão do usuário para segurança.
 */
class TokenManager
{
    private const SESSION_KEY = 'tokens';

    /**
     * Gera um token hexadecimal único para um dado ID e o armazena na sessão.
     *
     * @param int $id O ID para o qual o token será gerado.
     * @return string O token gerado.
     */
    public static function generateToken(int $id): string
    {
        // Garante que a sessão está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Inicializa o array de tokens se não existir
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        // Gera um token único (ex: 32 caracteres hexadecimais)
        $token = bin2hex(random_bytes(16)); // 16 bytes = 32 caracteres hexadecimais

        // Armazena o mapeamento token => id na sessão
        $_SESSION[self::SESSION_KEY][$token] = $id;

        return $token;
    }

    /**
     * Valida um token e retorna o ID associado a ele.
     * Remove o token da sessão após a validação para uso único (opcional, dependendo da necessidade).
     *
     * @param string $token O token a ser validado.
     * @param bool $removeAfterUse Se true, remove o token da sessão após a validação.
     * @return int|null O ID associado ao token, ou null se o token for inválido ou não encontrado.
     */
    public static function validateToken(string $token, bool $removeAfterUse = true): ?int
    {
        // Garante que a sessão está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            return null;
        }

        if (isset($_SESSION[self::SESSION_KEY][$token])) {
            $id = $_SESSION[self::SESSION_KEY][$token];
            if ($removeAfterUse) {
                unset($_SESSION[self::SESSION_KEY][$token]); // Remove o token após o uso
            }
            return $id;
        }

        return null;
    }

    /**
     * Limpa todos os tokens da sessão.
     */
    public static function clearTokens(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION[self::SESSION_KEY]);
    }
}
