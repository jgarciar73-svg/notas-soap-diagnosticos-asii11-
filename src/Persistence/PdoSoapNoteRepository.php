<?php

declare(strict_types=1);

namespace MicroHis\Persistence;

use MicroHis\Application\SoapNoteRepository;
use MicroHis\Domain\SoapNote;
use PDO;

final class PdoSoapNoteRepository implements SoapNoteRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function guardar(SoapNote $nota): SoapNote
    {
        $sentencia = $this->pdo->prepare(
            'INSERT INTO soap_notes
                (previous_version_id, medical_record_id, doctor_id, subjective, objective, assessment, plan, recorded_at)
             VALUES
                (:previous_version_id, :medical_record_id, :doctor_id, :subjective, :objective, :assessment, :plan, :recorded_at)'
        );

        $sentencia->execute([
            ':previous_version_id' => $nota->previousVersionId(),
            ':medical_record_id' => $nota->medicalRecordId(),
            ':doctor_id' => $nota->doctorId(),
            ':subjective' => $nota->subjective(),
            ':objective' => $nota->objective(),
            ':assessment' => $nota->assessment(),
            ':plan' => $nota->plan(),
            ':recorded_at' => $nota->recordedAt()->format(DATE_ATOM),
        ]);

        return $nota->conId((int) $this->pdo->lastInsertId());
    }
}
