# ASII-11: Notas SOAP y diagnósticos

**Estudiante:** Joshua Eduardo García Reyes (`jgarciar73-svg`)
**Módulo:** Notas SOAP y diagnósticos
**Rama:** `feature/asii-11-notas-soap-y-diagnosticos-jgarciar73-svg`
**Semana 1:** actores, alcance y casos de uso

## Actores

El actor principal es el **médico**. Necesita rol Médico (vía Spatie, guard `api`) y además tener su perfil en la tabla `doctors`. Es el único que puede escribir algo clínico en este módulo: redacta la nota, agrega los diagnósticos y la firma.

El **paciente** entra como sujeto de la nota, no como alguien que usa el sistema directamente, no hay portal de paciente en este MVP. Toda nota cuelga de su expediente por `medical_record_id`.

Le puse también a la **enfermera** como actor, pero solo de lectura: puede ver el historial de notas para dar seguimiento, no puede crear ni firmar ninguna. Ojo que esto lo supongo yo, no está escrito en ningún lado del proyecto ni en la documentación del curso, así que antes de armar los permisos hay que confirmarlo con el equipo.

Y por último el **tenant middleware**, que no es una persona sino el mecanismo que revisa el header `X-Tenant-ID` en cada petición para que un médico de un hospital no termine viendo notas de otro. Aplica a los cuatro casos de uso de abajo aunque no aparezca en ninguno de forma explícita.

## Alcance

Lo que sí entra en este módulo:

- Crear la nota SOAP (subjective, objective, assessment, plan) ligada a expediente, médico y, si aplica, admisión.
- Agregar diagnósticos CIE-10 a la nota, con su tipo: principal, secundario, presuntivo o definitivo.
- Firmar la nota electrónicamente (`electronic_sign`, `signed_at`).
- Ver el historial de notas de un expediente en orden cronológico. Ya hay un índice armado para esto en la migración, `idx_soap_record_date`, así que la consulta no debería ser lenta.

Lo que no entra, aunque toque tablas relacionadas a las mías:

- Alergias y el bloqueo de prescripción por alergia.
- El CRUD de prescripciones. Aunque `prescriptions.soap_note_id` apunta a mi tabla, la prescripción la construye otro compañero.
- Signos vitales, tampoco es mío.
- Admisión y camas. Para mi módulo, `medical_record_id` y `admission_id` son cosas que ya existen cuando el médico se sienta a escribir la nota, no las manejo yo.
- Laboratorio.

## Casos de uso

**UC-01: Registrar nota SOAP.** El médico documenta una consulta o evolución. Llena los cuatro campos: qué dice el paciente, qué encuentra el médico, el análisis, y el plan. Para esto necesita sesión activa, tenant correcto, y que el paciente ya tenga expediente. El flujo es simple: abre el expediente, elige nueva nota, llena los campos, guarda. Con eso queda un registro en `soap_notes` sin firmar, `signed_at` en null.

**UC-02: Agregar diagnóstico CIE-10.** Dentro de una nota ya creada, el médico agrega uno o varios diagnósticos con código CIE-10, descripción, y el tipo que le corresponda. Necesita que ya exista la nota. Busca o escribe el código, indica el tipo, guarda, y queda el registro en `diagnoses` ligado a esa nota.

**UC-03: Firmar electrónicamente la nota.** El médico cierra la nota formalmente. Para poder firmarla los cuatro campos SOAP tienen que estar completos. Confirma la firma y el sistema graba `electronic_sign` más `signed_at`. Acá propongo una regla que tampoco está confirmada en ningún lado: una vez firmada, la nota ya no se edita. Si hace falta corregir algo, se agrega una nota nueva en vez de tocar la vieja. Me parece lo más razonable clínicamente, pero hay que validarlo con el equipo.

**UC-04: Consultar historial de notas.** Antes de atender al paciente, el médico (o la enfermera, solo viendo) revisa las notas anteriores en orden cronológico. Necesita que el expediente tenga al menos una nota. Se abre el expediente, se lista el timeline con los diagnósticos de cada nota, de la más reciente a la más vieja. No cambia nada en la base de datos, es puramente de lectura.
