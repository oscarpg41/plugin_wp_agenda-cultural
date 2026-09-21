<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode [agenda_cultural]: se coloca en el contenido de cualquier
 * entrada o página para pintar ahí la agenda de eventos culturales.
 */
class Agenda_Cultural_Shortcode {

    public static function init() {
        add_shortcode('agenda_cultural', array(__CLASS__, 'render'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'registrar_assets'));
    }

    public static function registrar_assets() {
        wp_register_style(
            'agenda-cultural',
            plugins_url('assets/css/agenda-cultural.css', dirname(__FILE__)),
            array(),
            '1.1.0'
        );
        wp_register_script(
            'agenda-cultural',
            plugins_url('assets/js/agenda-cultural.js', dirname(__FILE__)),
            array(),
            '1.1.0',
            true
        );
    }

    public static function render($atts) {
        wp_enqueue_style('agenda-cultural');
        wp_enqueue_script('agenda-cultural');

        $eventos = Agenda_Cultural_DB::obtener_eventos();

        if (empty($eventos)) {
            return '<p class="agenda-cultural-vacio">No hay eventos culturales programados por el momento.</p>';
        }

        $grupos = array();
        foreach ($eventos as $evento) {
            $grupos[$evento['ff_event']][] = $evento;
        }

        ob_start();
        echo '<div class="agenda-cultural">';

        foreach ($grupos as $fecha => $eventos_dia) {
            $titulo_dia = ucfirst(strtolower(date_i18n('l, j \d\e F \d\e Y', strtotime($fecha))));
            echo '<h3 class="agenda-cultural-dia">' . esc_html($titulo_dia) . '</h3>';
            echo '<div class="agenda-cultural-eventos">';

            foreach ($eventos_dia as $evento) {
                self::render_evento($evento);
            }

            echo '</div>';
        }

        echo '</div>';

        return ob_get_clean();
    }

    private static function render_evento($evento) {
        $clases = array('agenda-cultural-evento');
        if (!empty($evento['suspendido'])) {
            $clases[] = 'agenda-cultural-suspendido';
        }
        if (!empty($evento['infantil'])) {
            $clases[] = 'agenda-cultural-infantil';
        }
        if (!empty($evento['popular'])) {
            $clases[] = 'agenda-cultural-destacado';
        }

        echo '<div class="' . esc_attr(implode(' ', $clases)) . '">';

        if (!empty($evento['image'])) {
            $url_imagen = AGENDA_CULTURAL_IMG_URL . rawurlencode($evento['image']);
            echo '<a class="agenda-cultural-imagen-enlace" href="' . esc_url($url_imagen) . '" aria-label="Ampliar imagen: ' . esc_attr($evento['title']) . '">';
            echo '<img class="agenda-cultural-imagen" src="' . esc_url($url_imagen) . '" alt="' . esc_attr($evento['title']) . '" loading="lazy">';
            echo '</a>';
        }

        echo '<div class="agenda-cultural-info">';

        echo '<div class="agenda-cultural-titulo">';
        echo '<span class="agenda-cultural-hora">' . esc_html(substr($evento['hour'], 0, 5)) . 'h</span> ';
        echo esc_html($evento['title']);
        if (!empty($evento['suspendido'])) {
            echo ' <span class="agenda-cultural-badge agenda-cultural-badge-suspendido">Suspendido</span>';
        }
        if (!empty($evento['infantil'])) {
            echo ' <span class="agenda-cultural-badge agenda-cultural-badge-infantil">Infantil</span>';
        }
        echo '</div>';

        if (!empty($evento['categoria'])) {
            echo '<span class="agenda-cultural-categoria">' . esc_html($evento['categoria']) . '</span>';
        }

        if (!empty($evento['lugar'])) {
            self::render_lugar($evento['lugar']);
        }

        if (!empty($evento['description'])) {
            echo '<div class="agenda-cultural-descripcion">' . self::formatear_descripcion($evento['description']) . '</div>';
        }

        echo '</div>'; // agenda-cultural-info
        echo '</div>'; // agenda-cultural-evento
    }

    /**
     * El campo "lugar" puede traer solo texto, solo una URL, o texto + URL
     * (p.ej. "Puerta de Cosíos (San Ildefonso) https://maps.app.goo.gl/xxx").
     * Si hay URL, el texto del lugar se convierte en el propio enlace.
     */
    private static function render_lugar($lugar) {
        $url = null;

        if (preg_match('/(https?:\/\/\S+)/i', $lugar, $coincidencia)) {
            $url = rtrim($coincidencia[1], '.,;:)]}');
        }

        if (null === $url) {
            echo '<p class="agenda-cultural-lugar">📍 ' . esc_html($lugar) . '</p>';
            return;
        }

        $texto = trim(str_replace($coincidencia[1], '', $lugar));
        $texto = trim($texto, " \t\n\r\0\x0B-–—:,");
        $etiqueta = ('' !== $texto) ? $texto : 'Ver mapa';

        echo '<p class="agenda-cultural-lugar">📍 <a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($etiqueta) . '</a></p>';
    }

    /**
     * Convierte cualquier URL que aparezca en la descripción en un enlace
     * "Más información" que abre en una pestaña nueva, dejando el resto
     * del texto escapado tal cual.
     */
    private static function formatear_descripcion($description) {
        $patron = '/(https?:\/\/[^\s<>"]+)/i';
        $offset = 0;
        $html = '';

        if (preg_match_all($patron, $description, $coincidencias, PREG_OFFSET_CAPTURE)) {
            foreach ($coincidencias[0] as $coincidencia) {
                list($url_bruta, $pos) = $coincidencia;

                $html .= esc_html(substr($description, $offset, $pos - $offset));

                $url = rtrim($url_bruta, '.,;:)]}');
                $sobrante = substr($url_bruta, strlen($url));

                $html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">Más información</a>' . esc_html($sobrante);

                $offset = $pos + strlen($url_bruta);
            }
        }

        $html .= esc_html(substr($description, $offset));

        return wpautop($html);
    }
}
