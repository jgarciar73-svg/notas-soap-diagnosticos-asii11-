<?php

declare(strict_types=1);

namespace MicroHis\Application;

use MicroHis\Domain\DiagnosisType;

final class SolicitudDiagnostico
{
    public function __construct(
        public readonly string $cie10Code,
        public readonly string $description,
        public readonly DiagnosisType $type,
    ) {
    }
}
