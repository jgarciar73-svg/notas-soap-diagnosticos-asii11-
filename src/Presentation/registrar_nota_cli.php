<?php

declare(strict_types=1);

require __DIR__ . '/../../autoload.php';

use MicroHis\Application\RegistrarNotaConDiagnostico;
use MicroHis\Domain\DiagnosisType;
use MicroHis\Domain\Exceptions\DiagnosticoInvalidoException;
use MicroHis\Domain\Exceptions\NotaSoapIncompletaException;
use MicroHis\Persistence\ConexionSqlite;
use MicroHis\Persistence\PdoDiagnosisRepository;
use MicroHis\Persistence\PdoSoapNoteRepository;

function preguntar(string $etiqueta): string
{
    echo $etiqueta . ': ';
    $valor = fgets(STDIN);

    return $valor === false ? '' : trim($valor);
}

$rutaBaseDatos = __DIR__ . '/../../storage/database.sqlite';
$pdo = ConexionSqlite::crear($rutaBaseDatos);

$casoUso = new RegistrarNotaConDiagnostico(
    new PdoSoapNoteRepository($pdo),
    new PdoDiagnosisRepository($pdo),
);

echo "=== Registro de nota SOAP y diagnóstico ===\n\n";

$medicalRecordId = preguntar('Número de expediente');
$doctorId = preguntar('Código del doctor');
$subjective = preguntar('Subjetivo');
$objective = preguntar('Objetivo');
$assessment = preguntar('Análisis');
$plan = preguntar('Plan');
$cie10Code = preguntar('Código CIE-10 del diagnóstico');
$diagnosisDescription = preguntar('Descripción del diagnóstico');

echo "\nTipos de diagnóstico disponibles: principal, secundario, presuntivo, definitivo\n";
$tipoTexto = preguntar('Tipo de diagnóstico');

try {
    $tipo = DiagnosisType::from($tipoTexto);

    $resultado = $casoUso->ejecutar(
        $medicalRecordId,
        $doctorId,
        $subjective,
        $objective,
        $assessment,
        $plan,
        $cie10Code,
        $diagnosisDescription,
        $tipo,
    );

    $notaId = $resultado['nota']->id();
    $diagnosticoId = $resultado['diagnostico']->id();
    echo "\nListo. Nota guardada con id {$notaId}, diagnóstico guardado con id {$diagnosticoId}.\n";
} catch (NotaSoapIncompletaException | DiagnosticoInvalidoException $e) {
    echo "\nNo se pudo registrar: {$e->getMessage()}\n";
} catch (ValueError) {
    echo "\nTipo de diagnóstico inválido. Usá: principal, secundario, presuntivo o definitivo.\n";
} catch (PDOException $e) {
    echo "\nError al guardar en la base de datos: {$e->getMessage()}\n";
}
