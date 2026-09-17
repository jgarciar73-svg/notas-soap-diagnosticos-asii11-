<?php

declare(strict_types=1);

require __DIR__ . '/../autoload.php';

use MicroHis\Application\RegistrarNotaConDiagnostico;
use MicroHis\Domain\Exceptions\DiagnosticoInvalidoException;
use MicroHis\Domain\Exceptions\NotaSoapIncompletaException;
use MicroHis\Persistence\ConexionSqlite;
use MicroHis\Persistence\PdoDiagnosisRepository;
use MicroHis\Persistence\PdoSoapNoteRepository;
use MicroHis\Presentation\Controllers\RegistrarNotaSoapController;
use MicroHis\Presentation\Http\JsonView;

// Este archivo es el único punto nuevo para exponer HTTP. No repite ninguna
// regla de negocio ni arma SQL: solo lee la petición, la traduce al mismo
// formato que ya entendía el controlador de la CLI, y lo llama.

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

$vista = new JsonView();

if ($metodo !== 'POST' || $ruta !== '/api/v1/soap-notes') {
    $vista->error(404, 'ruta_no_encontrada', 'Esta API solo expone POST /api/v1/soap-notes.');
    exit;
}

$cuerpo = json_decode(file_get_contents('php://input') ?: '', true);

if (!is_array($cuerpo) || !isset($cuerpo['diagnosis']) || !is_array($cuerpo['diagnosis'])) {
    $vista->error(400, 'cuerpo_invalido', 'El cuerpo debe ser JSON con los campos de la nota y un objeto "diagnosis".');
    exit;
}

$rutaBaseDatos = __DIR__ . '/../storage/database.sqlite';
$pdo = ConexionSqlite::crear($rutaBaseDatos);

$controlador = new RegistrarNotaSoapController(
    new RegistrarNotaConDiagnostico(
        new PdoSoapNoteRepository($pdo),
        new PdoDiagnosisRepository($pdo),
    ),
);

try {
    $resultado = $controlador->manejar([
        'medical_record_id' => (string) ($cuerpo['medical_record_id'] ?? ''),
        'doctor_id' => (string) ($cuerpo['doctor_id'] ?? ''),
        'subjective' => (string) ($cuerpo['subjective'] ?? ''),
        'objective' => (string) ($cuerpo['objective'] ?? ''),
        'assessment' => (string) ($cuerpo['assessment'] ?? ''),
        'plan' => (string) ($cuerpo['plan'] ?? ''),
        'cie10_code' => (string) ($cuerpo['diagnosis']['cie10_code'] ?? ''),
        'diagnosis_description' => (string) ($cuerpo['diagnosis']['description'] ?? ''),
        'diagnosis_type' => (string) ($cuerpo['diagnosis']['type'] ?? ''),
    ]);

    $vista->exito($resultado['nota'], $resultado['diagnostico']);
} catch (NotaSoapIncompletaException $e) {
    $vista->error(422, 'nota_incompleta', $e->getMessage());
} catch (DiagnosticoInvalidoException $e) {
    $vista->error(422, 'diagnostico_invalido', $e->getMessage());
} catch (ValueError) {
    $vista->error(400, 'tipo_invalido', 'Tipo de diagnóstico inválido. Usá: principal, secundario, presuntivo o definitivo.');
} catch (PDOException $e) {
    $vista->error(500, 'error_persistencia', 'No se pudo guardar en la base de datos.');
}
