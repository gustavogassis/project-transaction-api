<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

abstract class DomainError extends DomainException
{
    protected string $type;

    public function __construct(string $type, string $message, int $code)
    {
        $this->type = $type;
        parent::__construct($message, $code);
    }

    public function getType(): string
    {
        return $this->type;
    }
}
