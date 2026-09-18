<?php

declare(strict_types=1);

// Se corre así, desde la raíz del proyecto: php tests/RepositoryPatternTest.php

require __DIR__ . '/../autoload.php';

use MicroHis\Application\RegistrarNotaConDiagnostico;
use MicroHis\Application\SolicitudDiagnostico;
use MicroHis\Application\SolicitudNotaSoap;
use MicroHis\Domain\DiagnosisType;
use MicroHis\Persistence\InMemoryDiagnosisRepository;
use MicroHis\Persistence\InMemorySoapNoteRepository;

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

echo "=== RepositoryPatternTest ===\n\n";
echo "Idea: RegistrarNotaConDiagnostico no cambia ni una línea al cambiar\n";
echo "de PdoSoapNoteRepository/PdoDiagnosisRepository a los adaptadores en\n";
echo "memoria. Eso es lo que da el patrón Repository: Application solo\n";
echo "conoce la interfaz, nunca la implementación concreta.\n\n";

$repoNotas = new InMemorySoapNoteRepository();
$repoDiagnosticos = new InMemoryDiagnosisRepository();
$casoUso = new RegistrarNotaConDiagnostico($repoNotas, $repoDiagnosticos);

$resultado = $casoUso->ejecutar(
    new SolicitudNotaSoap('EXP-300', 'DOC-07', 'Dolor abdominal', 'Abdomen blando, doloroso a la palpación', 'Probable gastritis', 'Dieta blanda y omeprazol'),
    new SolicitudDiagnostico('K29.7', 'Gastritis no especificada', DiagnosisType::Presuntivo),
);

verificar('la nota quedó guardada en memoria con id 1', $resultado['nota']->id() === 1, $pasaron, $fallaron);
verificar('el diagnóstico quedó guardado en memoria con id 1', $resultado['diagnostico']->id() === 1, $pasaron, $fallaron);
verificar('el repositorio en memoria puede listar lo que guardó', count($repoNotas->todas()) === 1, $pasaron, $fallaron);

// Segunda nota, para confirmar que el id sigue subiendo solo
$casoUso->ejecutar(
    new SolicitudNotaSoap('EXP-301', 'DOC-07', 'Tos con flema', 'Afebril, sibilancias leves', 'Bronquitis', 'Salbutamol'),
    new SolicitudDiagnostico('J40', 'Bronquitis no especificada', DiagnosisType::Principal),
);
verificar('la segunda nota guardada en memoria recibe el id 2', count($repoNotas->todas()) === 2, $pasaron, $fallaron);

echo "\nTotal RepositoryPatternTest: $pasaron pasaron, $fallaron fallaron\n";
