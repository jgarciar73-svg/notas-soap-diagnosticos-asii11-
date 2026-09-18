<?php

declare(strict_types=1);

// Se corre así, desde la raíz del proyecto: php tests/PersistenceTest.php

require __DIR__ . '/../autoload.php';

use MicroHis\Application\RegistrarNotaConDiagnostico;
use MicroHis\Application\SolicitudDiagnostico;
use MicroHis\Application\SolicitudNotaSoap;
use MicroHis\Domain\Diagnosis;
use MicroHis\Domain\DiagnosisType;
use MicroHis\Persistence\ConexionSqlite;
use MicroHis\Persistence\PdoDiagnosisRepository;
use MicroHis\Persistence\PdoSoapNoteRepository;

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

echo "=== PersistenceTest (contra una base SQLite real en memoria) ===\n\n";

// Base de prueba en memoria: se crea y se destruye sola al terminar el script,
// nunca toca storage/database.sqlite.
$pdo = ConexionSqlite::crear(':memory:');

$repoNotas = new PdoSoapNoteRepository($pdo);
$repoDiagnosticos = new PdoDiagnosisRepository($pdo);
$casoUso = new RegistrarNotaConDiagnostico($repoNotas, $repoDiagnosticos);

// Camino feliz contra la base de datos real
$resultado = $casoUso->ejecutar(
    new SolicitudNotaSoap('EXP-100', 'DOC-05', 'Fiebre de dos días', 'T 38.6, FC 98', 'Probable dengue', 'Hidratación y control en 24h'),
    new SolicitudDiagnostico('A90', 'Dengue clásico', DiagnosisType::Presuntivo),
);
verificar('la nota quedó guardada con id 1 en la base real', $resultado['nota']->id() === 1, $pasaron, $fallaron);
verificar('el diagnóstico quedó guardado con id 1 en la base real', $resultado['diagnostico']->id() === 1, $pasaron, $fallaron);

$filaNota = $pdo->query('SELECT medical_record_id FROM soap_notes WHERE id = 1')->fetch();
verificar('la fila de la nota existe de verdad en la tabla soap_notes', $filaNota['medical_record_id'] === 'EXP-100', $pasaron, $fallaron);

$filaDiagnostico = $pdo->query('SELECT cie10_code FROM diagnoses WHERE id = 1')->fetch();
verificar('la fila del diagnóstico existe de verdad en la tabla diagnoses', $filaDiagnostico['cie10_code'] === 'A90', $pasaron, $fallaron);

// Error de persistencia real: diagnóstico apuntando a una nota que no existe.
// La restricción de llave foránea rechaza el INSERT.
$fallo = false;
try {
    $diagnosticoHuerfano = Diagnosis::registrar(999, 'J20.9', 'Bronquitis aguda', DiagnosisType::Secundario);
    $repoDiagnosticos->guardar($diagnosticoHuerfano);
} catch (PDOException) {
    $fallo = true;
}
verificar('guardar un diagnóstico con soap_note_id inexistente lanza un error real de la base de datos', $fallo, $pasaron, $fallaron);

echo "\nTotal PersistenceTest: $pasaron pasaron, $fallaron fallaron\n";
