<?php

namespace App\Exceptions;

use App\Exceptions\DomainError;

class UserException extends DomainError
{
    public static function forUserNotFound($id): self
    {
        return new static("usuario_nao_encontrado", sprintf(
            "O usuário com o id %d não foi encontrado.",
            $id
        ), 400);
    }
}
