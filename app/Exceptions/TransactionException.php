<?php

namespace App\Exceptions;

use App\Exceptions\DomainError;

class TransactionException extends DomainError
{
    public static function forInsufficientBalance($id, $value): self
    {
        return new static("saldo_insuficiente", sprintf(
            "O usuário com id %d não possui saldo suficiente para transferir o valor de %s reais.",
            $id,
            number_format($value, 2, ',', '.')
        ), 400);
    }

    public static function forUnauthorizedTransaction($payerId, $payeeId, $value): self
    {
        return new static("transacao_nao_autorizada", sprintf(
            "A transação entre os usuários %d e %d no valor de %s reais não foi autorizada",
            $payerId,
            $payeeId,
            number_format($value, 2, ',', '.')
        ), 400);
    }

    public static function forUserTypeNotAllowedToMakeTransaction($id): self
    {
        return new static("usuario_nao_autorizado", sprintf(
            "O tipo do usuário %d não pode realizar uma transação.",
            $id
        ), 400);
    }

    public static function forTransactionToSameUser($id): self
    {
        return new static("transacao_para_o_mesmo_usuario", sprintf(
            "O usuário %d não pode transferir para ele mesmo.",
            $id
        ), 400);
    }
}
