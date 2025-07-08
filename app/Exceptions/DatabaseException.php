<?php
// DatabaseException.php
namespace App\Exceptions;

use RuntimeException;
use Throwable;

class DatabaseException extends RuntimeException
{
    public function __construct($mensagem = "", $codigo = 0, Throwable $anterior = null)
    {
        parent::__construct($mensagem, $codigo, $anterior);
    }
}