<?php

declare(strict_types=1);

namespace MicroHis\Domain\Exceptions;

use DomainException;

final class DiagnosticoInvalidoException extends DomainException
{
    public static function porCampoFaltante(string $campo): self
    {
        return new self(sprintf('El diagnóstico necesita el campo "%s".', $campo));
    }
}
