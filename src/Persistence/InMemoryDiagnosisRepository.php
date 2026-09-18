<?php

declare(strict_types=1);

namespace MicroHis\Persistence;

use MicroHis\Application\DiagnosisRepository;
use MicroHis\Domain\Diagnosis;

final class InMemoryDiagnosisRepository implements DiagnosisRepository
{
    /** @var Diagnosis[] */
    private array $diagnosticos = [];

    private int $siguienteId = 1;

    public function guardar(Diagnosis $diagnostico): Diagnosis
    {
        $diagnosticoGuardado = Diagnosis::reconstruir(
            $this->siguienteId,
            $diagnostico->soapNoteId(),
            $diagnostico->cie10Code(),
            $diagnostico->description(),
            $diagnostico->type(),
        );

        $this->diagnosticos[$this->siguienteId] = $diagnosticoGuardado;
        $this->siguienteId++;

        return $diagnosticoGuardado;
    }

    /**
     * @return Diagnosis[]
     */
    public function todas(): array
    {
        return array_values($this->diagnosticos);
    }
}
