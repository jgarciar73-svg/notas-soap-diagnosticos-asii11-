<?php

declare(strict_types=1);

namespace MicroHis\Presentation\Views;

use MicroHis\Domain\Diagnosis;
use MicroHis\Domain\SoapNote;

final class ConsolaView
{
    public function mostrarExito(SoapNote $nota, Diagnosis $diagnostico): void
    {
        echo sprintf(
            "\nListo. Nota guardada con id %d, diagnóstico guardado con id %d.\n",
            $nota->id(),
            $diagnostico->id(),
        );
    }

    public function mostrarError(string $mensaje): void
    {
        echo "\nNo se pudo registrar: {$mensaje}\n";
    }
}
