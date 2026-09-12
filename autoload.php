<?php

declare(strict_types=1);

spl_autoload_register(function (string $clase): void {
    $prefijo = 'MicroHis\\';
    $carpetaBase = __DIR__ . '/src/';

    if (!str_starts_with($clase, $prefijo)) {
        return;
    }

    $rutaRelativa = substr($clase, strlen($prefijo));
    $rutaArchivo = $carpetaBase . str_replace('\\', '/', $rutaRelativa) . '.php';

    if (is_file($rutaArchivo)) {
        require $rutaArchivo;
    }
});
