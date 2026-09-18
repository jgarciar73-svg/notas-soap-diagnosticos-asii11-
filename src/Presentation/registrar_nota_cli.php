<?php

declare(strict_types=1);

require __DIR__ . '/../../autoload.php';

use MicroHis\Application\RegistrarNotaConDiagnostico;
use MicroHis\Domain\Exceptions\DiagnosticoInvalidoException;
use MicroHis\Domain\Exceptions\NotaSoapIncompletaException;
use MicroHis\Persistence\ConexionSqlite;
use MicroHis\Persistence\PdoDiagnosisRepository;
use MicroHis\Persistence\PdoSoapNoteRepository;
use MicroHis\Presentation\Controllers\RegistrarNotaSoapController;
use MicroHis\Presentation\Views\ConsolaView;

function preguntar(string $etiqueta): string
{
    echo $etiqueta . ': ';
    $valor = fgets(STDIN);

    return $valor === false ? '' : trim($valor);
}

// Este archivo solo arma las piezas (el "bootstrap") y recolecta la entrada
// cruda de la consola. No valida nada ni arma SQL: eso ya no es su trabajo.

$rutaBaseDatos = __DIR__ . '/../../storage/database.sqlite';
$pdo = ConexionSqlite::crear($rutaBaseDatos);

$controlador = new RegistrarNotaSoapController(
    new RegistrarNotaConDiagnostico(
        new PdoSoapNoteRepository($pdo),
        new PdoDiagnosisRepository($pdo),
    ),
);
$vista = new ConsolaView();

echo "=== Registro de nota SOAP y diagnóstico ===\n\n";

$entrada = [
    'medical_record_id' => preguntar('Número de expediente'),
    'doctor_id' => preguntar('Código del doctor'),
    'subjective' => preguntar('Subjetivo'),
    'objective' => preguntar('Objetivo'),
    'assessment' => preguntar('Análisis'),
    'plan' => preguntar('Plan'),
    'cie10_code' => preguntar('Código CIE-10 del diagnóstico'),
    'diagnosis_description' => preguntar('Descripción del diagnóstico'),
];

echo "\nTipos de diagnóstico disponibles: principal, secundario, presuntivo, definitivo\n";
$entrada['diagnosis_type'] = preguntar('Tipo de diagnóstico');

try {
    $resultado = $controlador->manejar($entrada);
    $vista->mostrarExito($resultado['nota'], $resultado['diagnostico']);
} catch (NotaSoapIncompletaException | DiagnosticoInvalidoException $e) {
    $vista->mostrarError($e->getMessage());
} catch (ValueError) {
    $vista->mostrarError('Tipo de diagnóstico inválido. Usá: principal, secundario, presuntivo o definitivo.');
} catch (PDOException $e) {
    $vista->mostrarError('Error al guardar en la base de datos: ' . $e->getMessage());
}
