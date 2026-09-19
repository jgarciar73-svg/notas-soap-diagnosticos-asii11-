# Semana 10 — Diseño para movilidad

Módulo: Notas SOAP y diagnósticos (ASII-11). Estudiante: Joshua Eduardo García Reyes, carné 22-5831.

## 1. Jerarquía de datos y acciones

En desktop (semana 8) los cuatro campos SOAP y el diagnóstico caben juntos en una sola vista. En 320–430px eso no entra sin generar scroll excesivo, así que se prioriza así:

1. **Contexto mínimo** (expediente + doctor), como chip compacto arriba, nunca como formulario expandido.
2. **Las 4 secciones SOAP**, en el orden clínico natural, una debajo de otra, cada una con su propio espacio para no competir por atención.
3. **Diagnóstico**, colapsado por defecto (`+ Agregar diagnóstico`). Es un paso secundario dentro del mismo flujo, no hace falta verlo hasta que se llega ahí.
4. **Acción principal**, fija abajo (`Registrar nota`), siempre visible sin importar cuánto se haya scrolleado.

Autoría clínica y versionado no compiten por espacio en la pantalla de registro: la autoría se resuelve sola (según la sesión, sin campo editable) y el versionado solo aparece cuando corresponde (pantalla de corrección).

## 2. Reglas de breakpoints

| Ancho | Comportamiento |
|---|---|
| 320–374px | Una sola columna, textareas al 100% del ancho disponible menos 32px de margen. Botones con alto mínimo de 44px (objetivo táctil). |
| 375–430px | Igual que arriba, con un poco más de aire entre campos (se ve en los wireframes, pensados para ~390px). |
| ≥431px | Deja de aplicar este diseño; a partir de tablet se usa el layout de escritorio de la semana 8, no una versión intermedia nueva. |

No hay una versión "tablet" propia: por debajo de 430px es el diseño de esta semana, por encima es directamente el de escritorio.

## 3. Navegación

Un `←` en la barra superior vuelve a la pantalla anterior (típicamente el historial del expediente). No hay menú lateral ni tabs: el flujo es lineal (registrar → confirmar), como ya lo era en desktop, así que no hace falta inventar una navegación nueva, solo adaptar el ancho.

## 4. Tablas y formularios

No hay tablas en este flujo (el historial de notas, que sí sería una tabla, queda fuera de esta entrega). Los formularios usan controles nativos grandes: sin selects personalizados chicos, sin campos de una sola línea para texto largo.

## 5. Confirmaciones

Igual que en desktop: antes de guardar una corrección, un aviso explícito ("Se creará una versión nueva"). En mobile ese aviso se mantiene sin recortar, porque es información crítica, no decorativa.

## 6. Recuperación ante conexión limitada

Pantalla nueva, sin equivalente en desktop, porque en mobile la conexión intermitente es la norma, no la excepción:

- El estado de conexión se muestra en la barra de estado del sistema (no se duplica con un ícono propio) y además con un aviso explícito arriba del formulario.
- Cada campo se guarda localmente apenas se escribe (ícono de guardado 💾 junto al campo), no solo al final.
- El botón principal cambia a "Sin señal, esperando", deshabilitado: no se le pide al usuario que reintente a mano, se le explica que el envío es automático apenas vuelva la señal.

## Wireframes (5, anotados)

En `docs/wireframes-movil/`, mismo formato SVG que la semana 8:

1. `01-registro-movil.svg` — formulario, jerarquizado, diagnóstico colapsado.
2. `02-error-movil.svg` — error recuperable, aviso pegado arriba.
3. `03-exito-movil.svg` — confirmación, resumen mínimo, dos acciones grandes.
4. `04-correccion-movil.svg` — corrección, versión original colapsada para priorizar la tarea.
5. `05-conexion-limitada-movil.svg` — recuperación ante conexión limitada, guardado local explícito.

## Siguiente paso

La semana 11 convierte estas pantallas en un prototipo navegable real, desktop y móvil, con capturas del camino feliz y de un error crítico.
