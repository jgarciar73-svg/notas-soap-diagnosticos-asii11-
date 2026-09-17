<?php

declare(strict_types=1);

namespace MicroHis\Application;

use MicroHis\Domain\SoapNote;

interface SoapNoteRepository
{
    /**
     * Guarda la nota y devuelve una copia con el id ya asignado por la base de datos.
     */
    public function guardar(SoapNote $nota): SoapNote;
}
