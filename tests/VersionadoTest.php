<?php

declare(strict_types=1);

// Se corre así, desde la raíz del proyecto: php tests/VersionadoTest.php
//
// Esta prueba es la evidencia del "cambio práctico" que pide la semana 6:
// ¿qué pasa si un doctor necesita corregir una nota SOAP ya registrada?

require __DIR__ . '/../autoload.php';

use MicroHis\Domain\Exceptions\NotaSoapSinIdException;
use MicroHis\Domain\SoapNote;
use MicroHis\Persistence\ConexionSqlite;
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

echo "=== VersionadoTest ===\n\n";

// Regla de dominio: no se puede corregir una nota que nunca se guardó
$notaSinGuardar = SoapNote::registrar('EXP-700', 'DOC-15', 'S', 'O', 'A', 'P');
$fallo = false;
try {
    $notaSinGuardar->corregir('S corregido', 'O', 'A', 'P');
} catch (NotaSoapSinIdException) {
    $fallo = true;
}
verificar('corregir() sobre una nota sin id lanza NotaSoapSinIdException', $fallo, $pasaron, $fallaron);

// Camino feliz del versionado, contra una base real
$pdo = ConexionSqlite::crear(':memory:');
$repo = new PdoSoapNoteRepository($pdo);

$original = SoapNote::registrar('EXP-701', 'DOC-15', 'Tos leve', 'Afebril', 'Bronquitis', 'Reposo', );
$original = $repo->guardar($original);
verificar('la versión original queda guardada con id 1', $original->id() === 1, $pasaron, $fallaron);
verificar('la versión original no es una corrección', !$original->esCorreccion(), $pasaron, $fallaron);

$corregida = $original->corregir('Tos con flema', 'Afebril, sibilancias', 'Bronquitis con broncoespasmo', 'Salbutamol y reposo');
verificar('corregir() todavía no le asigna id a la nueva versión', $corregida->id() === null, $pasaron, $fallaron);
verificar('corregir() liga la nueva versión a la id de la original', $corregida->previousVersionId() === 1, $pasaron, $fallaron);

$corregida = $repo->guardar($corregida);
verificar('la versión corregida se guarda con un id nuevo, distinto al original', $corregida->id() === 2, $pasaron, $fallaron);

$filas = $pdo->query('SELECT id, previous_version_id, assessment FROM soap_notes ORDER BY id')->fetchAll();
verificar('quedan DOS filas en la base, la original no se borró ni se sobrescribió', count($filas) === 2, $pasaron, $fallaron);
verificar('la fila original conserva su texto sin cambios', $filas[0]['assessment'] === 'Bronquitis', $pasaron, $fallaron);
verificar('la fila nueva apunta a la original por previous_version_id', (int) $filas[1]['previous_version_id'] === 1, $pasaron, $fallaron);

echo "\nTotal VersionadoTest: $pasaron pasaron, $fallaron fallaron\n";
