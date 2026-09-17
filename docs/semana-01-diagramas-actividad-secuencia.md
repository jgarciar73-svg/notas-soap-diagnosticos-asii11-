# Semana 1 — Diagramas de actividad y secuencia

Completa el entregable de la semana 1 (el texto de actores, alcance y casos de uso ya existía; faltaban estos dos diagramas). Módulo: Notas SOAP y diagnósticos. Estudiante: Joshua Eduardo García Reyes, carné 22-5831.

## Diagrama de actividad — registro de nota SOAP y asociación de diagnóstico clínico

Incluye las decisiones, las excepciones, y el resultado final, tal como pide la consigna.

```mermaid
flowchart TD
    Start(["Doctor inicia el registro"]) --> Ingresar["Ingresa expediente, doctor y las 4 secciones (S/O/A/P)"]
    Ingresar --> ValidaNota{"¿Expediente, doctor y las 4 secciones completos?"}
    ValidaNota -- No --> ErrorNota["Excepción: NotaSoapIncompletaException"]
    ErrorNota --> FinError(["Fin: registro rechazado"])
    ValidaNota -- Sí --> GuardaNota["Se guarda la nota SOAP, recibe un id"]
    GuardaNota --> IngresaDx["Ingresa código CIE-10, descripción y tipo de diagnóstico"]
    IngresaDx --> ValidaDx{"¿Diagnóstico completo y nota con id real?"}
    ValidaDx -- No --> ErrorDx["Excepción: DiagnosticoInvalidoException"]
    ErrorDx --> FinError
    ValidaDx -- Sí --> GuardaDx["Se guarda el diagnóstico, asociado a la nota"]
    GuardaDx --> Fin(["Fin: nota y diagnóstico registrados"])
```

## Diagrama de secuencia — mismo flujo

Participantes, mensajes, validaciones, y respuesta. Corresponde exactamente al recorrido real del código (`RegistrarNotaSoapController` → `RegistrarNotaConDiagnostico` → `SoapNote`/`Diagnosis` → repositorio), no es un diagrama aparte inventado.

```mermaid
sequenceDiagram
    actor Doctor
    participant Presentation as "Presentation (Controller)"
    participant Application as "RegistrarNotaConDiagnostico"
    participant Domain as "SoapNote / Diagnosis"
    participant Persistence as "Repository"

    Doctor->>Presentation: Ingresa datos de la nota y el diagnóstico
    Presentation->>Application: ejecutar(datos)
    Application->>Domain: SoapNote::registrar(...)
    alt datos incompletos
        Domain-->>Application: lanza NotaSoapIncompletaException
        Application-->>Presentation: excepción
        Presentation-->>Doctor: muestra error, pide corregir
    else datos completos
        Domain-->>Application: nota válida (sin id)
        Application->>Persistence: guardar(nota)
        Persistence-->>Application: nota con id asignado
        Application->>Domain: Diagnosis::registrar(id, ...)
        alt diagnóstico inválido
            Domain-->>Application: lanza DiagnosticoInvalidoException
            Application-->>Presentation: excepción
            Presentation-->>Doctor: muestra error, pide corregir
        else diagnóstico válido
            Domain-->>Application: diagnóstico válido (sin id)
            Application->>Persistence: guardar(diagnóstico)
            Persistence-->>Application: diagnóstico con id asignado
            Application-->>Presentation: nota + diagnóstico
            Presentation-->>Doctor: confirmación de registro exitoso
        end
    end
```

## Trazabilidad con los casos de uso

Los dos diagramas cubren el mismo y único caso de uso ya descrito en el texto de actores y alcance ("registrar nota SOAP y asociar diagnóstico clínico"), con el mismo actor (Doctor), las mismas dos excepciones de negocio ya implementadas en el código (`NotaSoapIncompletaException`, `DiagnosticoInvalidoException`), y el mismo resultado esperado. No se inventó ningún camino nuevo: ambos diagramas describen exactamente lo que el código de `RegistrarNotaConDiagnostico` ya hace.
