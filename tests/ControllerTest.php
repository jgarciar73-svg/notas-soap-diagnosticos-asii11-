<?php

declare(strict_types=1);

// Se corre así, desde la raíz del proyecto: php tests/ControllerTest.php

require __DIR__ . '/../autoload.php';

use MicroHis\Application\RegistrarNotaConDiagnostico;
use MicroHis\Domain\Exceptions\NotaSoapIncompletaException;
use MicroHis\Persistence\InMemoryDiagnosisRepository;
use MicroHis\Persistence\InMemorySoapNoteRepository;
use MicroHis\Presentation\Controllers\RegistrarNotaSoapController;

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

echo "=== ControllerTest ===\n\n";

$controlador = new RegistrarNotaSoapController(
    new RegistrarNotaConDiagnostico(
        new InMemorySoapNoteRepository(),
        new InMemoryDiagnosisRepository(),
    ),
);

// El controlador reutiliza el mismo caso de uso con repos en memoria,
// sin ninguna configuración especial: es un objeto como cualquier otro.
$resultado = $controlador->manejar([
    'medical_record_id' => 'EXP-400',
    'doctor_id' => 'DOC-11',
    'subjective' => 'Dolor lumbar',
    'objective' => 'Sin fiebre, movilidad reducida',
    'assessment' => 'Lumbalgia mecánica',
    'plan' => 'Reposo relativo y antiinflamatorio',
    'cie10_code' => 'M54.5',
    'diagnosis_description' => 'Lumbalgia',
    'diagnosis_type' => 'principal',
]);
verificar('manejar() devuelve la nota y el diagnóstico guardados', $resultado['nota']->id() !== null && $resultado['diagnostico']->id() !== null, $pasaron, $fallaron);

// El controlador NO valida nada por su cuenta: si la entrada es inválida,
// la excepción viene de Domain (a través de Application) y sube sin que
// el controlador la esconda ni la transforme.
$fallo = false;
try {
    $controlador->manejar([
        'medical_record_id' => 'EXP-401',
        'doctor_id' => 'DOC-11',
        'subjective' => '',
        'objective' => 'Sin fiebre',
        'assessment' => 'Lumbalgia',
        'plan' => 'Reposo',
        'cie10_code' => 'M54.5',
        'diagnosis_description' => 'Lumbalgia',
        'diagnosis_type' => 'principal',
    ]);
} catch (NotaSoapIncompletaException) {
    $fallo = true;
}
verificar('manejar() con entrada inválida deja pasar la excepción de Domain sin envolverla', $fallo, $pasaron, $fallaron);

echo "\nTotal ControllerTest: $pasaron pasaron, $fallaron fallaron\n";
