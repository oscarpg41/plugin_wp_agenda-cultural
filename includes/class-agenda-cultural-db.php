<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Conexión a la base de datos externa (GeMal) donde vive la tabla de eventos.
 * Es una instalación de BD independiente de la propia de WordPress.
 */
class Agenda_Cultural_DB {

    private static $conexion = null;

    private static function conectar() {
        if (self::$conexion === null) {
            self::$conexion = new wpdb(BD_USUARIO, BD_PASSWORD, BD_NOMBRE, BD_SERVIDOR);
        }

        return self::$conexion;
    }

    /**
     * Devuelve los próximos eventos de la agenda cultural (ff_event >= hoy),
     * ordenados por fecha y hora. Usa un transient de WordPress como caché
     * para no golpear la BD externa en cada carga de página.
     * Con AGENDA_CULTURAL_CACHE_MINUTOS = 0 la caché queda desactivada.
     */
    public static function obtener_eventos() {
        $cache_activa = AGENDA_CULTURAL_CACHE_MINUTOS > 0;

        if ($cache_activa) {
            $cache = get_transient('agenda_cultural_eventos');
            if (false !== $cache) {
                return $cache;
            }
        }

        $db = self::conectar();
        $tabla = AGENDA_CULTURAL_TABLA;

        $eventos = $db->get_results(
            "SELECT idEvent, idEventoGrupo, ff_event, hour, title, description, lugar, categoria, image, popular, suspendido, infantil
             FROM {$tabla}
             WHERE ff_event >= CURDATE()
             ORDER BY ff_event ASC, hour ASC",
            ARRAY_A
        );

        if (!empty($db->last_error)) {
            error_log('Agenda Cultural - error de base de datos: ' . $db->last_error);
            return array();
        }

        $eventos = $eventos ? $eventos : array();

        if ($cache_activa) {
            set_transient('agenda_cultural_eventos', $eventos, AGENDA_CULTURAL_CACHE_MINUTOS * MINUTE_IN_SECONDS);
        }

        return $eventos;
    }
}
