# Semana 9 — Evaluación de usabilidad y accesibilidad

Módulo: Notas SOAP y diagnósticos. Estudiante: Joshua Eduardo García Reyes, carné 22-5831.

Evalúa los 6 wireframes y los dos user flow de la semana 8 (no un sistema aparte). Los números de anotación de los propios wireframes (①②③) quedan fuera de esta evaluación: son notas mías para explicarte el diseño, no parte de la interfaz real.

## 1. Checklist completado

| # | Criterio | Resultado | Evidencia |
|---|---|---|---|
| 1 | Teclado: cada campo y botón es alcanzable con Tab, en orden lógico | ✅ Pasa (orden visual = orden de tabulación en los 6 wireframes) | Todos |
| 2 | Teclado: el selector de "Tipo" de diagnóstico funciona sin mouse | ❌ Falla (no especifica soporte de teclado para el desplegable) | `01-registro-vacio.svg` |
| 3 | Foco: hay un indicador visible de qué elemento tiene el foco | ❌ Falla (ningún wireframe dibuja un estado de foco) | Todos |
| 4 | Contraste: texto de ejemplo (placeholder) ≥ 4.5:1 | ❌ Falla (`#aaaaaa` sobre blanco ≈ 2.1:1) | `01-registro-vacio.svg` |
| 5 | Contraste: texto de ayuda contextual ≥ 4.5:1 | ❌ Falla (`#888888` sobre blanco ≈ 3.1:1) | `01-registro-vacio.svg` |
| 6 | Contraste: mensaje de error y de éxito ≥ 4.5:1 | ✅ Pasa (rojo oscuro sobre rosa claro, verde oscuro sobre verde claro) | `02-error-recuperable.svg`, `04-estado-exito.svg` |
| 7 | Etiquetas: cada campo tiene una etiqueta visible asociada | ✅ Pasa visualmente | Todos |
| 8 | Etiquetas: los campos de solo lectura (expediente, autor) se identifican como tales para un lector de pantalla, no solo con color de fondo | ❌ Falla (solo hay una diferencia visual, ningún `aria-readonly`/`disabled` especificado) | `01-registro-vacio.svg`, `05-correccion-revisor.svg` |
| 9 | Mensajes: el error se anuncia automáticamente a un lector de pantalla, no solo se muestra en pantalla | ❌ Falla (no se especifica región `aria-live`) | `02-error-recuperable.svg` |
| 10 | Mensajes: el mensaje de error identifica el campo exacto, no un error genérico | ✅ Pasa (dice literalmente qué sección falta) | `02-error-recuperable.svg` |
| 11 | Prevención de errores: una acción que crea un dato clínico nuevo pide confirmación explícita antes de guardar | ✅ Pasa (el aviso "Se creará una versión nueva" antes de guardar la corrección) | `05-correccion-revisor.svg` |
| 12 | Prevención de errores: no hay ninguna acción de borrado irreversible sobre datos clínicos | ✅ Pasa (ninguna versión tiene botón de eliminar) | `06-historial-versiones.svg` |

**Resultado**: 6 de 12 criterios pasan, 6 fallan. Los 6 que fallan son los 6 hallazgos de la sección 2.

## 2. Hallazgos (6)

1. **Sin indicador de foco visible** (criterio 3). Afecta el flujo completo, de los dos roles, no un campo en particular.
2. **Selector de "Tipo" de diagnóstico sin soporte de teclado especificado** (criterio 2). Afecta específicamente el registro del diagnóstico.
3. **Contraste insuficiente en el texto de ayuda contextual** (criterio 5). Afecta la ayuda contextual de las 4 secciones de la nota SOAP.
4. **Campos de solo lectura no identificados para lectores de pantalla** (criterio 8). Afecta expediente y autoría clínica: un usuario de lector de pantalla no tiene forma de saber que esos campos no se pueden tocar.
5. **Mensaje de error no anunciado automáticamente** (criterio 9). Afecta el registro y la corrección de notas SOAP por igual: un usuario de lector de pantalla podría no enterarse de que el guardado falló.
6. **Contraste insuficiente en el texto de ejemplo (placeholder)** (criterio 4). Afecta la legibilidad general del formulario, prioridad menor porque es solo un ejemplo, no un dato real.

## 3. Backlog priorizado

Orden por impacto en notas SOAP, diagnósticos, autoría clínica, versionado y expediente (de mayor a menor).

| Prioridad | Hallazgo | Corrección propuesta | Criterio verificable |
|---|---|---|---|
| 1 | Sin foco visible | Agregar un contorno visible (ej. `outline: 2px solid` con buen contraste) a todo elemento interactivo al recibir foco | Al navegar con Tab por cualquiera de los 6 wireframes implementados, cada campo y botón muestra un contorno distinguible a simple vista |
| 2 | Campos de solo lectura sin marcar para lectores de pantalla | Marcar expediente y autor con `aria-readonly="true"` o `disabled`, según corresponda | Con un lector de pantalla activo (ej. NVDA), al enfocar el campo de expediente se anuncia "solo lectura" o equivalente |
| 3 | Error no anunciado automáticamente | Envolver el mensaje de error en una región `role="alert"` o `aria-live="assertive"` | Con un lector de pantalla activo, al fallar el envío del formulario, el mensaje de error se lee en voz alta sin que el usuario tenga que buscarlo |
| 4 | Selector de "Tipo" sin soporte de teclado confirmado | Si se implementa como `<select>` nativo, ya cumple; si es un componente a medida, agregar navegación con flechas, `Enter`/`Escape`, y rol ARIA de combobox | Se puede elegir un tipo de diagnóstico completo (abrir, moverse entre opciones, confirmar) usando solo el teclado, sin tocar el mouse |
| 5 | Ayuda contextual con contraste insuficiente | Cambiar el color del texto de ayuda de `#888888` a uno con razón de contraste ≥ 4.5:1 sobre fondo blanco (ej. `#5a5a5a`) | Medido con una herramienta de contraste (ej. WebAIM Contrast Checker), el resultado es ≥ 4.5:1 |
| 6 | Placeholder con contraste insuficiente | Cambiar `#aaaaaa` a un tono más oscuro para el texto de ejemplo, o duplicarlo como ayuda visible en vez de depender solo del placeholder | Medido con la misma herramienta, ≥ 4.5:1; alternativamente, el texto de ejemplo ya no desaparece al enfocar el campo |
