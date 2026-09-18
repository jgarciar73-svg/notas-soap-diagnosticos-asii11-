<?php

declare(strict_types=1);

namespace MicroHis\Presentation\Controllers;

use MicroHis\Application\RegistrarNotaConDiagnostico;
use MicroHis\Application\SolicitudDiagnostico;
use MicroHis\Application\SolicitudNotaSoap;
use MicroHis\Domain\Diagnosis;
use MicroHis\Domain\DiagnosisType;
use MicroHis\Domain\SoapNote;

final class RegistrarNotaSoapController
{
    public function __construct(
        private readonly RegistrarNotaConDiagnostico $casoUso,
    ) {
    }

    /**
     * No valida reglas de negocio (eso es de Domain) ni arma SQL (eso es de
     * Persistence). Solo traduce el arreglo de entrada en una llamada al
     * caso de uso de Application, y deja que cualquier excepción suba tal
     * cual para que la capa de arriba decida cómo mostrarla.
     *
     * @param array{
     *     medical_record_id: string,
     *     doctor_id: string,
     *     subjective: string,
     *     objective: string,
     *     assessment: string,
     *     plan: string,
     *     cie10_code: string,
     *     diagnosis_description: string,
     *     diagnosis_type: string,
     * } $entrada
     * @return array{nota: SoapNote, diagnostico: Diagnosis}
     */
    public function manejar(array $entrada): array
    {
        $solicitudNota = new SolicitudNotaSoap(
            $entrada['medical_record_id'],
            $entrada['doctor_id'],
            $entrada['subjective'],
            $entrada['objective'],
            $entrada['assessment'],
            $entrada['plan'],
        );

        $solicitudDiagnostico = new SolicitudDiagnostico(
            $entrada['cie10_code'],
            $entrada['diagnosis_description'],
            DiagnosisType::from($entrada['diagnosis_type']),
        );

        return $this->casoUso->ejecutar($solicitudNota, $solicitudDiagnostico);
    }
}
