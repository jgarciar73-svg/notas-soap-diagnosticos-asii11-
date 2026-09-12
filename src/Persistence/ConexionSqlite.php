<?php

declare(strict_types=1);

namespace MicroHis\Persistence;

use PDO;

final class ConexionSqlite
{
    public static function crear(string $rutaArchivo): PDO
    {
        $pdo = new PDO('sqlite:' . $rutaArchivo);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');

        self::migrar($pdo);

        return $pdo;
    }

    private static function migrar(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS soap_notes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                medical_record_id TEXT NOT NULL,
                doctor_id TEXT NOT NULL,
                subjective TEXT NOT NULL,
                objective TEXT NOT NULL,
                assessment TEXT NOT NULL,
                plan TEXT NOT NULL,
                recorded_at TEXT NOT NULL
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS diagnoses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                soap_note_id INTEGER NOT NULL,
                cie10_code TEXT NOT NULL,
                description TEXT NOT NULL,
                type TEXT NOT NULL,
                FOREIGN KEY (soap_note_id) REFERENCES soap_notes(id)
            )'
        );
    }
}
