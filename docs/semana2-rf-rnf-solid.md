# ASII-11: Notas SOAP y diagnósticos

**Estudiante:** Joshua Eduardo García Reyes (`jgarciar73-svg`)
**Módulo:** Notas SOAP y diagnósticos
**Rama:** `feature/asii-11-notas-soap-y-diagnosticos-jgarciar73-svg`
**Semana 2:** RF/RNF, criterios de aceptación y ejemplo SOLID

## Requisitos funcionales

Saqué estos cuatro de los casos de uso que ya había definido la semana pasada:

| ID | Requisito | Caso de uso | Prioridad |
|---|---|---|---|
| RF-007 | Registrar nota médica en formato SOAP (subjetivo, objetivo, análisis, plan) | UC-01 | Alta |
| RF-007-DX * | Registrar uno o más diagnósticos CIE-10 dentro de una nota SOAP | UC-02 | Alta |
| RF-012 | Firmar electrónicamente la nota, con marca de tiempo | UC-03 | Alta |
| RF-006 ** | Consultar el historial cronológico de notas SOAP del expediente | UC-04 | Media |

\* Este no está en la tabla general de requisitos del proyecto como línea propia, se lo agregué yo porque mi issue lo pide por nombre y ya existe la tabla `diagnoses` para soportarlo. Falta confirmarlo con el equipo para que quede oficial.

\** RF-006 es el requisito grande de "historia clínica completa con historial histórico". Lo que cubro en UC-04 es solo la parte de notas SOAP, no todo el expediente.

### Criterios de aceptación

Para RF-007: si el médico tiene sesión activa, el tenant es el correcto, y el paciente ya tiene expediente, al llenar los cuatro campos y guardar debería quedar el registro en `soap_notes` sin firmar todavía.

Para RF-007-DX: con una nota SOAP ya guardada, al agregar un diagnóstico con su código CIE-10 y tipo (principal, secundario, presuntivo o definitivo), debería crearse el registro en `diagnoses` apuntando a esa nota.

Para RF-012: si los cuatro campos de la nota están completos y el médico confirma la firma, el sistema tiene que guardar `electronic_sign` y `signed_at`, y de ahí en adelante la nota queda bloqueada, no se puede editar más.

Para RF-006 en la parte que me toca (UC-04): si el expediente ya tiene alguna nota, al abrirlo debería listarse el historial completo con sus diagnósticos, ordenado de la más reciente a la más vieja.

## Requisitos no funcionales

De la lista general del proyecto, estos cuatro son los que le pegan a mi módulo:

| ID | Categoría | Cómo aplica en mi módulo |
|---|---|---|
| RNF-002 | Rendimiento | El historial de notas (UC-04) tiene que cargar en menos de 3 segundos |
| RNF-007 | Seguridad | Los endpoints van detrás de HTTPS + JWT, como todo el resto del sistema |
| RNF-009 | Seguridad | Cada nota creada, editada o firmada queda en `audit_logs` |
| RNF-010 | Seguridad | Solo Médico puede escribir o firmar, Enfermera nomás consulta |

## Ejemplo SOLID

El profe pide ejemplificar al menos dos principios de los que vienen en el artículo de MVP Cluster. Elegí responsabilidad única y abierto/cerrado porque son los que más se notan en mi propio módulo, no los agarré al azar. Esto todavía es diseño, no el código final del módulo.

### Responsabilidad única (SRP)

Al principio pensé en meter todo en un solo método: crear la nota, validar y guardar los diagnósticos, firmar, y avisarle a enfermería. Algo así:

```php
class SoapNoteService
{
    public function crearYFirmarNota(array $data): SoapNote
    {
        $note = SoapNote::create([
            'medical_record_id' => $data['medical_record_id'],
            'doctor_id' => $data['doctor_id'],
            'subjective' => $data['subjective'],
            'objective' => $data['objective'],
            'assessment' => $data['assessment'],
            'plan' => $data['plan'],
        ]);

        foreach ($data['diagnoses'] as $dx) {
            if (!preg_match('/^[A-Z][0-9]{2}(\.[0-9]{1,2})?$/', $dx['cie10_code'])) {
                throw new \InvalidArgumentException('Código CIE-10 inválido');
            }
            $note->diagnoses()->create($dx);
        }

        $note->update([
            'electronic_sign' => hash('sha256', $data['doctor_id'] . now()),
            'signed_at' => now(),
        ]);

        Notification::send($data['enfermeras'], new NuevaNotaFirmada($note));

        return $note;
    }
}
```

El problema es que esta clase termina teniendo cuatro razones distintas para cambiar. Si mañana cambia la validación del CIE-10, toco esta clase. Si cambia cómo se genera la firma, también. Nada de eso debería afectar a lo mismo.

Lo separé así:

```php
class SoapNoteService
{
    public function crear(array $data): SoapNote
    {
        return SoapNote::create($data);
    }
}

class DiagnosisService
{
    public function agregar(SoapNote $note, array $dx): Diagnosis
    {
        if (!preg_match('/^[A-Z][0-9]{2}(\.[0-9]{1,2})?$/', $dx['cie10_code'])) {
            throw new \InvalidArgumentException('Código CIE-10 inválido');
        }
        return $note->diagnoses()->create($dx);
    }
}

class ElectronicSignatureService
{
    public function firmar(SoapNote $note, Doctor $doctor): SoapNote
    {
        $note->update([
            'electronic_sign' => hash('sha256', $doctor->id . now()),
            'signed_at' => now(),
        ]);
        return $note;
    }
}
```

Ahora cada clase tiene una sola razón para cambiar. La notificación la dejé fuera del ejemplo a propósito, esa sería otra responsabilidad más, le tocaría a un listener aparte.

### Abierto/cerrado (OCP)

Esto lo pensé directo desde el enum `type` que ya tengo en la migración de `diagnoses`: principal, secundario, presuntivo, definitivo. La forma obvia, y la mala, de validar cada tipo es con un if/elseif:

```php
class DiagnosisValidator
{
    public function validar(string $type, array $data): void
    {
        if ($type === 'principal') {
            if (empty($data['cie10_code'])) {
                throw new \InvalidArgumentException('El diagnóstico principal necesita código CIE-10');
            }
        } elseif ($type === 'presuntivo') {
            if (empty($data['description'])) {
                throw new \InvalidArgumentException('El diagnóstico presuntivo necesita descripción');
            }
        } elseif ($type === 'definitivo') {
            if (empty($data['cie10_code']) || empty($data['description'])) {
                throw new \InvalidArgumentException('El diagnóstico definitivo necesita código y descripción');
            }
        }
        // y si agregan "secundario" con su propia regla, toca editar esto otra vez
    }
}
```

Cada tipo nuevo obliga a volver a esta misma clase, con el riesgo de romper algo que ya funcionaba. Mejor una interfaz con una clase por tipo:

```php
interface DiagnosisTypeRule
{
    public function validar(array $data): void;
}

class PrincipalRule implements DiagnosisTypeRule
{
    public function validar(array $data): void
    {
        if (empty($data['cie10_code'])) {
            throw new \InvalidArgumentException('El diagnóstico principal necesita código CIE-10');
        }
    }
}

class PresuntivoRule implements DiagnosisTypeRule
{
    public function validar(array $data): void
    {
        if (empty($data['description'])) {
            throw new \InvalidArgumentException('El diagnóstico presuntivo necesita descripción');
        }
    }
}

class DiagnosisValidator
{
    /** @var array<string, DiagnosisTypeRule> */
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules; // ej: ['principal' => new PrincipalRule(), 'presuntivo' => new PresuntivoRule()]
    }

    public function validar(string $type, array $data): void
    {
        $this->rules[$type]->validar($data);
    }
}
```

Con esto, agregar "secundario" es nomás crear `SecundarioRule` e implementar `DiagnosisTypeRule`, sin tocar `DiagnosisValidator`. Queda abierta para agregar tipos nuevos, pero cerrada para que alguien tenga que meterle mano al código que ya funciona.
