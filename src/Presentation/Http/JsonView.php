<?php

declare(strict_types=1);

namespace MicroHis\Presentation\Http;

use MicroHis\Domain\Diagnosis;
use MicroHis\Domain\SoapNote;

final class JsonView
{
    public function exito(SoapNote $nota, Diagnosis $diagnostico): void
    {
        http_response_code(201);
        header('Content-Type: application/json');

        echo json_encode([
            'soap_note' => [
                'id' => $nota->id(),
                'medical_record_id' => $nota->medicalRecordId(),
                'doctor_id' => $nota->doctorId(),
                'subjective' => $nota->subjective(),
                'objective' => $nota->objective(),
                'assessment' => $nota->assessment(),
                'plan' => $nota->plan(),
                'recorded_at' => $nota->recordedAt()->format(DATE_ATOM),
            ],
            'diagnosis' => [
                'id' => $diagnostico->id(),
                'soap_note_id' => $diagnostico->soapNoteId(),
                'cie10_code' => $diagnostico->cie10Code(),
                'description' => $diagnostico->description(),
                'type' => $diagnostico->type()->value,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function error(int $codigoHttp, string $codigo, string $mensaje): void
    {
        http_response_code($codigoHttp);
        header('Content-Type: application/json');

        echo json_encode([
            'error' => [
                'code' => $codigo,
                'message' => $mensaje,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
