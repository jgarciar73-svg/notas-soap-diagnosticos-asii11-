<?php

declare(strict_types=1);

namespace MicroHis\Application;

final class SolicitudNotaSoap
{
    public function __construct(
        public readonly string $medicalRecordId,
        public readonly string $doctorId,
        public readonly string $subjective,
        public readonly string $objective,
        public readonly string $assessment,
        public readonly string $plan,
    ) {
    }
}
