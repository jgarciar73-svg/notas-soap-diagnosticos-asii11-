<?php

declare(strict_types=1);

namespace MicroHis\Domain;

enum DiagnosisType: string
{
    case Principal = 'principal';
    case Secundario = 'secundario';
    case Presuntivo = 'presuntivo';
    case Definitivo = 'definitivo';
}
