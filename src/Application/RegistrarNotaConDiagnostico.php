<?php

declare(strict_types=1);

namespace MicroHis\Application;

use MicroHis\Domain\Diagnosis;
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
    public function ejecutar(SolicitudNotaSoap $nota, SolicitudDiagnostico $diagnostico): array
    {
        // 1. Domain valida la nota. Si falta algo, lanza NotaSoapIncompletaException
        //    antes de que se intente guardar nada.
        $notaDominio = SoapNote::registrar(
            $nota->medicalRecordId,
            $nota->doctorId,
            $nota->subjective,
            $nota->objective,
            $nota->assessment,
            $nota->plan,
        );

        // 2. Recién acá se intenta guardar. Si la base de datos falla, la excepción
        //    de Persistence sube tal cual, sin que Application la oculte.
        $notaGuardada = $this->notas->guardar($notaDominio);

        // 3. Domain valida el diagnóstico, ya asociado al id real de la nota guardada.
        $diagnosticoDominio = Diagnosis::registrar(
            $notaGuardada->id(),
            $diagnostico->cie10Code,
            $diagnostico->description,
            $diagnostico->type,
        );

        $diagnosticoGuardado = $this->diagnosticos->guardar($diagnosticoDominio);

        return [
            'nota' => $notaGuardada,
            'diagnostico' => $diagnosticoGuardado,
        ];
    }
}
