<?php

declare(strict_types=1);

namespace MicroHis\Domain;

use DateTimeImmutable;
use MicroHis\Domain\Exceptions\NotaSoapIncompletaException;
use MicroHis\Domain\Exceptions\NotaSoapSinIdException;

final class SoapNote
{
    private ?int $id;
    private ?int $previousVersionId;
    private string $medicalRecordId;
    private string $doctorId;
    private string $subjective;
    private string $objective;
    private string $assessment;
    private string $plan;
    private DateTimeImmutable $recordedAt;

    private function __construct(
        ?int $id,
        ?int $previousVersionId,
        string $medicalRecordId,
        string $doctorId,
        string $subjective,
        string $objective,
        string $assessment,
        string $plan,
        DateTimeImmutable $recordedAt
    ) {
        $this->id = $id;
        $this->previousVersionId = $previousVersionId;
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
        ?int $previousVersionId,
        string $medicalRecordId,
        string $doctorId,
        string $subjective,
        string $objective,
        string $assessment,
        string $plan,
        DateTimeImmutable $recordedAt
    ): self {
        self::validarCampos($medicalRecordId, $doctorId, $subjective, $objective, $assessment, $plan);

        return new self(
            $id,
            $previousVersionId,
            $medicalRecordId,
            $doctorId,
            $subjective,
            $objective,
            $assessment,
            $plan,
            $recordedAt
        );
    }

    /**
     * Crea una NUEVA versión de esta nota, ligada a esta por id. No borra ni
     * modifica la versión actual: eso es justamente la regla de negocio
     * central del módulo (versionado sin pérdida de historial).
     */
    /**
     * $doctorIdQueCorrige es quien AUTORIZA esta versión, no necesariamente
     * el mismo doctor que escribió la original (autoría clínica: cada
     * versión conserva quién la redactó a ella).
     */
    public function corregir(
        string $doctorIdQueCorrige,
        string $subjective,
        string $objective,
        string $assessment,
        string $plan
    ): self {
        if ($this->id === null) {
            throw NotaSoapSinIdException::porFaltaDeId();
        }

        self::validarCampos($this->medicalRecordId, $doctorIdQueCorrige, $subjective, $objective, $assessment, $plan);

        return new self(
            null,
            $this->id,
            $this->medicalRecordId,
            $doctorIdQueCorrige,
            $subjective,
            $objective,
            $assessment,
            $plan,
            new DateTimeImmutable()
        );
    }

    public function conId(int $id): self
    {
        return new self(
            $id,
            $this->previousVersionId,
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

    public function previousVersionId(): ?int
    {
        return $this->previousVersionId;
    }

    public function esCorreccion(): bool
    {
        return $this->previousVersionId !== null;
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
