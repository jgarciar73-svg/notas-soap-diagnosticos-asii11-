<?php

declare(strict_types=1);

// Se corre así, desde la raíz del proyecto: php tests/ApplicationTest.php

require __DIR__ . '/../autoload.php';

use MicroHis\Application\DiagnosisRepository;
use MicroHis\Application\RegistrarNotaConDiagnostico;
use MicroHis\Application\SoapNoteRepository;
use MicroHis\Domain\Diagnosis;
use MicroHis\Domain\DiagnosisType;
use MicroHis\Domain\Exceptions\NotaSoapIncompletaException;
use MicroHis\Domain\SoapNote;

$pasaron = 0;
$fallaron = 0;

function verificar(string $descripcion, bool $condicion, int &$pasaron, int &$fallaron): void
{
    if ($condicion) {
        echo "PASS: $descripcion\n";
        $pasaron++;
        return;
    }

    echo "FAIL: $descripcion\n";
    $fallaron++;
}

echo "=== ApplicationTest (con dobles, sin tocar una base de datos real) ===\n\n";

// Doble que simula guardar con éxito.
$notasQueFuncionan = new class implements SoapNoteRepository {
    public function guardar(SoapNote $nota): SoapNote
    {
        return $nota->conId(1);
    }
};

$diagnosticosQueFuncionan = new class implements DiagnosisRepository {
    public function guardar(Diagnosis $diagnostico): Diagnosis
    {
        return Diagnosis::reconstruir(
            1,
            $diagnostico->soapNoteId(),
            $diagnostico->cie10Code(),
            $diagnostico->description(),
            $diagnostico->type(),
        );
    }
};

// Doble que simula una falla de base de datos.
$notasQueFallan = new class implements SoapNoteRepository {
    public function guardar(SoapNote $nota): SoapNote
    {
        throw new RuntimeException('Fallo simulado de base de datos');
    }
};

// Camino feliz de punta a punta
$casoUso = new RegistrarNotaConDiagnostico($notasQueFuncionan, $diagnosticosQueFuncionan);
$resultado = $casoUso->ejecutar(
    'EXP-010',
    'DOC-02',
    'Tos seca',
    'Afebril',
    'Bronquitis leve',
    'Reposo y líquidos',
    'J20.9',
    'Bronquitis aguda',
    DiagnosisType::Principal,
);
verificar(
    'ejecutar() con todo válido devuelve nota y diagnóstico con id',
    $resultado['nota']->id() === 1 && $resultado['diagnostico']->id() === 1,
    $pasaron,
    $fallaron,
);

// Regla de dominio: no debería llegar a Persistence si la nota es inválida
$fallo = false;
try {
    $casoUso->ejecutar('EXP-011', 'DOC-02', '', 'Afebril', 'Bronquitis leve', 'Reposo y líquidos', 'J20.9', 'Bronquitis aguda', DiagnosisType::Principal);
} catch (NotaSoapIncompletaException) {
    $fallo = true;
}
verificar('ejecutar() con nota incompleta lanza NotaSoapIncompletaException sin llegar a Persistence', $fallo, $pasaron, $fallaron);

// Error de persistencia simulado con un doble
$casoUsoConFalla = new RegistrarNotaConDiagnostico($notasQueFallan, $diagnosticosQueFuncionan);
$fallo = false;
try {
    $casoUsoConFalla->ejecutar('EXP-012', 'DOC-02', 'Tos seca', 'Afebril', 'Bronquitis leve', 'Reposo y líquidos', 'J20.9', 'Bronquitis aguda', DiagnosisType::Principal);
} catch (RuntimeException $e) {
    $fallo = $e->getMessage() === 'Fallo simulado de base de datos';
}
verificar('ejecutar() propaga el error cuando Persistence falla al guardar la nota', $fallo, $pasaron, $fallaron);

echo "\nTotal ApplicationTest: $pasaron pasaron, $fallaron fallaron\n";
