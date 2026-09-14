<?php

declare(strict_types=1);

namespace MicroHis\Domain;

use MicroHis\Domain\Exceptions\DiagnosticoInvalidoException;

final class Diagnosis
{
    private function __construct(
        private readonly ?int $id,
        private readonly int $soapNoteId,
        private readonly string $cie10Code,
        private readonly string $description,
        private readonly DiagnosisType $type,
    ) {
    }

    public static function registrar(
        int $soapNoteId,
        string $cie10Code,
        string $description,
        DiagnosisType $type,
    ): self {
        self::validar($soapNoteId, $cie10Code, $description);

        return new self(
            id: null,
            soapNoteId: $soapNoteId,
            cie10Code: $cie10Code,
            description: $description,
            type: $type,
        );
    }

    public static function reconstruir(
        int $id,
        int $soapNoteId,
        string $cie10Code,
        string $description,
        DiagnosisType $type,
    ): self {
        return new self(
            id: $id,
            soapNoteId: $soapNoteId,
            cie10Code: $cie10Code,
            description: $description,
            type: $type,
        );
    }

    private static function validar(int $soapNoteId, string $cie10Code, string $description): void
    {
        if ($soapNoteId <= 0) {
            throw DiagnosticoInvalidoException::porCampoFaltante('soap_note_id');
        }

        if (trim($cie10Code) === '') {
            throw DiagnosticoInvalidoException::porCampoFaltante('cie10_code');
        }

        if (trim($description) === '') {
            throw DiagnosticoInvalidoException::porCampoFaltante('description');
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function soapNoteId(): int
    {
        return $this->soapNoteId;
    }

    public function cie10Code(): string
    {
        return $this->cie10Code;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function type(): DiagnosisType
    {
        return $this->type;
    }
}
