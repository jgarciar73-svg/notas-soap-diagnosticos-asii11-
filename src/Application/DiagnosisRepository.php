<?php

declare(strict_types=1);

namespace MicroHis\Application;

use MicroHis\Domain\Diagnosis;

interface DiagnosisRepository
{
    /**
     * Guarda el diagnóstico y devuelve una copia con el id ya asignado por la base de datos.
     */
    public function guardar(Diagnosis $diagnostico): Diagnosis;
}
