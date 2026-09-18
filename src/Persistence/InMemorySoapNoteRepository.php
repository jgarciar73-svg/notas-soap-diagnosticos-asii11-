<?php

declare(strict_types=1);

namespace MicroHis\Persistence;

use MicroHis\Application\SoapNoteRepository;
use MicroHis\Domain\SoapNote;

final class InMemorySoapNoteRepository implements SoapNoteRepository
{
    /** @var SoapNote[] */
    private array $notas = [];

    private int $siguienteId = 1;

    public function guardar(SoapNote $nota): SoapNote
    {
        $notaGuardada = $nota->conId($this->siguienteId);
        $this->notas[$this->siguienteId] = $notaGuardada;
        $this->siguienteId++;

        return $notaGuardada;
    }

    /**
     * @return SoapNote[]
     */
    public function todas(): array
    {
        return array_values($this->notas);
    }
}
