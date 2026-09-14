<?php

declare(strict_types=1);

// Se corre así, desde la raíz del proyecto: php tests/DomainTest.php

require __DIR__ . '/../autoload.php';

use MicroHis\Domain\Diagnosis;
use MicroHis\Domain\DiagnosisType;
use MicroHis\Domain\Exceptions\DiagnosticoInvalidoException;
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

echo "=== DomainTest ===\n\n";

// Camino feliz: SoapNote con todos los datos completos
$nota = SoapNote::registrar('EXP-001', 'DOC-01', 'Dolor de cabeza', 'Presión 120/80', 'Migraña probable', 'Ibuprofeno 400mg');
verificar('SoapNote::registrar() con todo completo no lanza error', $nota->medicalRecordId() === 'EXP-001', $pasaron, $fallaron);
verificar('la nota nueva todavía no tiene id', $nota->id() === null, $pasaron, $fallaron);

// Regla de dominio: falta una sección de la nota
$fallo = false;
try {
    SoapNote::registrar('EXP-002', 'DOC-01', 'Dolor de cabeza', 'Presión 120/80', '', 'Ibuprofeno 400mg');
} catch (NotaSoapIncompletaException) {
    $fallo = true;
}
verificar('SoapNote::registrar() sin la sección de análisis lanza NotaSoapIncompletaException', $fallo, $pasaron, $fallaron);

// Regla de dominio: falta el expediente
$fallo = false;
try {
    SoapNote::registrar('', 'DOC-01', 'Dolor de cabeza', 'Presión 120/80', 'Migraña probable', 'Ibuprofeno 400mg');
} catch (NotaSoapIncompletaException) {
    $fallo = true;
}
verificar('SoapNote::registrar() sin expediente lanza NotaSoapIncompletaException', $fallo, $pasaron, $fallaron);

// conId() no muta la nota original
$notaConId = $nota->conId(7);
verificar('conId() asigna el id nuevo', $notaConId->id() === 7, $pasaron, $fallaron);
verificar('conId() no modifica la nota original', $nota->id() === null, $pasaron, $fallaron);

// Camino feliz: Diagnosis asociado a una nota ya persistida
$diagnostico = Diagnosis::registrar(7, 'G43.9', 'Migraña, no especificada', DiagnosisType::Principal);
verificar('Diagnosis::registrar() asocia el diagnóstico al id de la nota', $diagnostico->soapNoteId() === 7, $pasaron, $fallaron);
verificar('el diagnóstico nuevo todavía no tiene id propio', $diagnostico->id() === null, $pasaron, $fallaron);

// Regla de dominio: código CIE-10 vacío
$fallo = false;
try {
    Diagnosis::registrar(7, '', 'Migraña, no especificada', DiagnosisType::Principal);
} catch (DiagnosticoInvalidoException) {
    $fallo = true;
}
verificar('Diagnosis::registrar() sin código CIE-10 lanza DiagnosticoInvalidoException', $fallo, $pasaron, $fallaron);

// Regla de dominio: no se puede asociar a una nota sin id real
$fallo = false;
try {
    Diagnosis::registrar(0, 'G43.9', 'Migraña, no especificada', DiagnosisType::Principal);
} catch (DiagnosticoInvalidoException) {
    $fallo = true;
}
verificar('Diagnosis::registrar() con soapNoteId 0 lanza DiagnosticoInvalidoException', $fallo, $pasaron, $fallaron);

echo "\nTotal DomainTest: $pasaron pasaron, $fallaron fallaron\n";
