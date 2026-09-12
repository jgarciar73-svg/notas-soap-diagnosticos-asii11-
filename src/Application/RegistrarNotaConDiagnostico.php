<?php

declare(strict_types=1);

namespace MicroHis\Application;

use MicroHis\Domain\Diagnosis;
use MicroHis\Domain\DiagnosisType;
use MicroHis\Domain\SoapNote;

final class RegistrarNotaConDiagnostico
{
    public function __construct(
        private readonly SoapNoteRepository $notas,
        private readonly DiagnosisRepository $diagnosticos,
    ) {
    }

    /**
     * @return array{nota: SoapNote, diagnostico: Diagnosis}
     */
    public function ejecutar(
        string $medicalRecordId,
        string $doctorId,
        string $subjective,
        string $objective,
        string $assessment,
        string $plan,
        string $cie10Code,
        string $diagnosisDescription,
        DiagnosisType $diagnosisType,
    ): array {
        // 1. Domain valida la nota. Si falta algo, lanza NotaSoapIncompletaException
        //    antes de que se intente guardar nada.
        $nota = SoapNote::registrar($medicalRecordId, $doctorId, $subjective, $objective, $assessment, $plan);

        // 2. Recién acá se intenta guardar. Si la base de datos falla, la excepción
        //    de Persistence sube tal cual, sin que Application la oculte.
        $notaGuardada = $this->notas->guardar($nota);

        // 3. Domain valida el diagnóstico, ya asociado al id real de la nota guardada.
        $diagnostico = Diagnosis::registrar(
            $notaGuardada->id(),
            $cie10Code,
            $diagnosisDescription,
            $diagnosisType,
        );

        $diagnosticoGuardado = $this->diagnosticos->guardar($diagnostico);

        return [
            'nota' => $notaGuardada,
            'diagnostico' => $diagnosticoGuardado,
        ];
    }
}
