<?php

declare(strict_types=1);

namespace MicroHis\Domain\Exceptions;

use DomainException;

final class NotaSoapSinIdException extends DomainException
{
    public static function porFaltaDeId(): self
    {
        return new self('No se puede corregir una nota que todavía no tiene un id asignado (todavía no se guardó).');
    }
}
