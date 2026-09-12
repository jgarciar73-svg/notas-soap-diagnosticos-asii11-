<?php

declare(strict_types=1);

namespace MicroHis\Domain;

use DateTimeImmutable;
use MicroHis\Domain\Exceptions\NotaSoapIncompletaException;

final class SoapNote
{
    private ?int $id;
    private string $medicalRecordId;
    private string $doctorId;
    private string $subjective;
    private string $objective;
    private string $assessment;
    private string $plan;
    private DateTimeImmutable $recordedAt;

    private function __construct(
        ?int $id,
        string $medicalRecordId,
        string $doctorId,
        string $subjective,
        string $objective,
        string $assessment,
        string $plan,
        DateTimeImmutable $recordedAt
    ) {
        $this->id = $id;
        $this->medicalRecordId = $medicalRecordId;
        $this->doctorId = $doctorId;
        $this->subjective = $subjective;
        $this->objective = $objective;
        $this->assessment = $assessment;
        $this->plan = $plan;
        $this->recordedAt = $recordedAt;
    }

    public static function registrar(
        string $medicalRecordId,
        string $doctorId,
        string $subjective,
        string $objective,
        string $assessment,
        string $plan
    ): self {
        self::validarCampos($medicalRecordId, $doctorId, $subjective, $objective, $assessment, $plan);

        return new self(
            null,
            $medicalRecordId,
            $doctorId,
            $subjective,
            $objective,
            $assessment,
            $plan,
            new DateTimeImmutable()
        );
    }

    public static function reconstruir(
        int $id,
        string $medicalRecordId,
        string $doctorId,
        string $subjective,
        string $objective,
        string $assessment,
        string $plan,
        DateTimeImmutable $recordedAt
    ): self {
        self::validarCampos($medicalRecordId, $doctorId, $subjective, $objective, $assessment, $plan);

        return new self($id, $medicalRecordId, $doctorId, $subjective, $objective, $assessment, $plan, $recordedAt);
    }

    public function conId(int $id): self
    {
        return new self(
            $id,
            $this->medicalRecordId,
            $this->doctorId,
            $this->subjective,
            $this->objective,
            $this->assessment,
            $this->plan,
            $this->recordedAt
        );
    }

    private static function validarCampos(
        string $medicalRecordId,
        string $doctorId,
        string $subjective,
        string $objective,
        string $assessment,
        string $plan
    ): void {
        if (trim($medicalRecordId) === '') {
            throw new NotaSoapIncompletaException('La nota debe estar asociada a un expediente.');
        }

        if (trim($doctorId) === '') {
            throw new NotaSoapIncompletaException('La nota debe tener un doctor autor.');
        }

        $secciones = [
            'subjetivo' => $subjective,
            'objetivo' => $objective,
            'analisis' => $assessment,
            'plan' => $plan,
        ];

        foreach ($secciones as $nombre => $valor) {
            if (trim($valor) === '') {
                throw new NotaSoapIncompletaException(sprintf('La sección "%s" no puede estar vacía.', $nombre));
            }
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function medicalRecordId(): string
    {
        return $this->medicalRecordId;
    }

    public function doctorId(): string
    {
        return $this->doctorId;
    }

    public function subjective(): string
    {
        return $this->subjective;
    }

    public function objective(): string
    {
        return $this->objective;
    }

    public function assessment(): string
    {
        return $this->assessment;
    }

    public function plan(): string
    {
        return $this->plan;
    }

    public function recordedAt(): DateTimeImmutable
    {
        return $this->recordedAt;
    }
}
