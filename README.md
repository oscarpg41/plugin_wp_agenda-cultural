# Agenda Cultural

Plugin de WordPress que muestra la agenda de eventos culturales. Los datos se gestionan desde el back-office que debes de generar por otro lado y el plugin los lee directamente de su base de datos.
**IMPORTANTE:** Este plugin no realiza la gestión de los eventos, únicamente muestra la información.

- **Versión:** 1.1.0
- **Shortcode:** `[agenda_cultural]`

## Características

- **Listado automático de próximos eventos**: solo se muestran eventos con fecha igual o posterior a hoy, ordenados por fecha y hora. Los eventos pasados desaparecen solos.
- **Agrupación por día**: cada jornada lleva su cabecera con la fecha en español (p. ej. «Sábado, 26 de septiembre de 2026»).
- **Ficha de cada evento**:
  - Hora, título y categoría (etiqueta).
  - Lugar con icono 📍. Si el campo incluye una URL (p. ej. de Google Maps), el texto del lugar se convierte en enlace; si solo hay URL, el enlace se llama «Ver mapa».
  - Descripción con párrafos. Cualquier URL que aparezca en ella se transforma en un enlace «Más información» que abre en pestaña nueva.
- **Imagen del evento con lightbox**: al hacer clic en la imagen se abre ampliada sobre fondo oscuro, con el título debajo. Se cierra con clic fuera, con la ✕ o con Esc; bloquea el scroll de la página mientras está abierta y devuelve el foco a la miniatura. Sin JavaScript, el clic abre la imagen a tamaño completo. No usa librerías externas.
- **Estados y destacados**:
  - *Suspendido*: título tachado y etiqueta roja «Suspendido».
  - *Infantil*: etiqueta azul «Infantil».
  - *Popular*: la tarjeta se resalta con un borde dorado.
- **Diseño responsive**: en móvil, la imagen pasa a ocupar el ancho de la tarjeta encima del texto.
- **Caché de la consulta**: el resultado se guarda en un transient de WordPress (`agenda_cultural_eventos`) para no consultar la BD externa en cada visita. La caché se borra al desactivar el plugin.
- **Carga selectiva de assets**: el CSS y el JS solo se cargan en las páginas donde aparece el shortcode.
- **Seguridad**: toda la salida va escapada (`esc_html`, `esc_url`, `esc_attr`), los enlaces externos llevan `rel="noopener"` y los errores de BD se registran en el log sin mostrarse al visitante.
- **Aviso de configuración**: si faltan las constantes de conexión, la tabla o la URL de imágenes, muestra un aviso en el escritorio de WordPress y no intenta conectar.

## Requisitos

En `wp-config.php` deben estar definidas las constantes de conexión a la BD donde se guarda la información de los eventos:

```php
define('BD_SERVIDOR', '...');
define('BD_NOMBRE', '...');
define('BD_USUARIO', '...');
define('BD_PASSWORD', '...');
define('AGENDA_CULTURAL_TABLA', '....');
define('AGENDA_CULTURAL_IMG_URL', 'https://.../files/agenda-cultural/eventos/'); // URL base (con barra final) donde están las imágenes de los eventos
```

Esto nos permite que los eventos puedan estar en otra base de datos externa a la instancia de WordPress. Si la información de los eventos estan en la base de datos de la intancia de WordPress, debeis configurar los defines de la siguiente forma:

```php
define ('BD_SERVIDOR', DB_HOST);
define ('BD_NOMBRE', DB_NAME);
define ('BD_USUARIO', DB_USER);
define ('BD_PASSWORD', DB_PASSWORD);
```
asignando los valores de la base de datos de la instancia de WordPress.

La tabla leída tiene que tener las columnas siguiente: `idEvent`, `idEventoGrupo`, `ff_event`, `hour`, `title`, `description`, `lugar`, `categoria`, `image`, `popular`, `suspendido` e `infantil`.

## Uso

Añade el shortcode en cualquier entrada o página, en el punto donde quieras que aparezca la agenda:

```
[agenda_cultural]
```

Si no hay eventos próximos, se muestra el mensaje «No hay eventos culturales programados por el momento.»

## Configuración opcional

| Constante | Dónde | Por defecto | Descripción |
|---|---|---|---|
| `AGENDA_CULTURAL_CACHE_MINUTOS` | `agenda-cultural.php` | `10` | Minutos de caché de la consulta. `0` desactiva la caché. |

## Estructura

```
agenda-cultural/
├── agenda-cultural.php                          # Cabecera del plugin, constantes y arranque
├── includes/
│   ├── class-agenda-cultural-db.php             # Conexión a la BD de GeMal y consulta con caché
│   └── class-agenda-cultural-shortcode.php      # Shortcode, HTML de los eventos y registro de assets
├── assets/
│   ├── css/agenda-cultural.css                  # Estilos de la agenda y del lightbox
│   └── js/agenda-cultural.js                    # Lightbox de imágenes
└── README.md
```

## Despliegue

Copiar la carpeta completa `agenda-cultural` a `wp-content/plugins/` y activar el plugin. Al actualizar, subir también `assets/js/` si es la primera vez que se despliega la versión con lightbox. Si se cambian el CSS o el JS, subir la versión (`1.1.0`) en `registrar_assets()` para evitar que se sirva la caché del navegador.
