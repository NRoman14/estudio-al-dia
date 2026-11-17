# Estudio al Día

Estudio al Día es un proyecto de aplicación web en Laravel para organizar tareas universitarias. Cada estudiante podrá crear, editar y eliminar sus tareas, además de marcarlas como completadas.

## Fecha de entrega

Cada tarea puede tener una fecha de entrega opcional. La lista ordena las pendientes por fecha (las que no tienen fecha van al final) y muestra un aviso: **Atrasada**, **Vence hoy**, **Vence mañana** o **Vence en N días**. Si hay tareas atrasadas, el panel "Un paso a la vez" lo indica. La fecha también se envía a Groq para que los pasos sugeridos consideren el plazo.

Los avisos usan la zona horaria de `APP_TIMEZONE` en el `.env` (por ejemplo `America/Santiago`). Si no se define, se usa UTC.

## Sugerencias con Groq

Desde el detalle de una tarea, el botón **Sugerir pasos** usa Groq para generar tres acciones concretas a partir del título y la descripción de la tarea. Por ejemplo, para preparar una exposición de Base de Datos, puede sugerir definir el tema, ordenar los contenidos y preparar la primera diapositiva.

## Prototipo en Figma

<https://www.figma.com/make/dv04b5vf4szuqGSLgjXj4I/Organizador-de-tareas-universitarias?t=6MUgGHpl6Mln77P7-1>

## Ejecutar en local

El proyecto usa PHP 8.3 o superior, Composer y la extensión PDO SQLite. Las vistas Blade y los estilos están incluidos en el proyecto, así que puedes iniciar la aplicación directamente con PHP.

En la primera instalación, desde la carpeta del proyecto:

```sh
composer setup
php artisan serve
```

Abre <http://127.0.0.1:8000> y selecciona **Crear cuenta**. Las tareas se guardan en `database/database.sqlite`. Si el proyecto ya está instalado, basta con ejecutar `php artisan serve`. Para aplicar nuevas migraciones, ejecuta `php artisan migrate`.

## Activar Groq

Crea una clave en [GroqCloud](https://console.groq.com/keys) y guárdala en `GROQ_API_KEY` en el archivo local `.env`. El modelo predeterminado es `openai/gpt-oss-20b`. Si necesitas otro modelo compatible con salidas estructuradas, configúralo en `GROQ_MODEL`. Ejecuta `php artisan config:clear` para cargar los cambios. La app envía el título y la descripción al presionar **Sugerir pasos**. Si falta la clave o el servicio no responde, la ficha muestra un aviso y las tareas siguen disponibles.

## Pruebas

Ejecuta `php artisan test`. Las pruebas usan SQLite en memoria y una respuesta simulada de Groq. Cubren el flujo de cuentas y tareas, los permisos entre dos usuarios, la respuesta ante una cuota agotada, y la fecha de entrega (orden, avisos y validación).
