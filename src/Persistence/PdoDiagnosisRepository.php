<?php

declare(strict_types=1);

namespace MicroHis\Persistence;

use MicroHis\Application\DiagnosisRepository;
use MicroHis\Domain\Diagnosis;
use PDO;

final class PdoDiagnosisRepository implements DiagnosisRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function guardar(Diagnosis $diagnostico): Diagnosis
    {
        $sentencia = $this->pdo->prepare(
            'INSERT INTO diagnoses (soap_note_id, cie10_code, description, type)
             VALUES (:soap_note_id, :cie10_code, :description, :type)'
        );

        $sentencia->execute([
            ':soap_note_id' => $diagnostico->soapNoteId(),
            ':cie10_code' => $diagnostico->cie10Code(),
            ':description' => $diagnostico->description(),
            ':type' => $diagnostico->type()->value,
        ]);

        return Diagnosis::reconstruir(
            (int) $this->pdo->lastInsertId(),
            $diagnostico->soapNoteId(),
            $diagnostico->cie10Code(),
            $diagnostico->description(),
            $diagnostico->type(),
        );
    }
}
