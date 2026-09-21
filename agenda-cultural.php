<?php
/**
 * Plugin Name: Agenda Cultural
 * Description: Muestra la agenda de eventos culturales del municipio (datos gestionados desde GeMal) mediante el shortcode [agenda_cultural].
 * Version: 1.0.0
 * Author: Ayuntamiento del Real Sitio de San Ildefonso
 * Text Domain: agenda-cultural
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cuánto tiempo se cachea la consulta a la BD externa (minutos). 0 = sin caché.
// TODO: subir a 15 (o el valor que se decida) al pasar a producción.
define('AGENDA_CULTURAL_CACHE_MINUTOS', 10);

require_once plugin_dir_path(__FILE__) . 'includes/class-agenda-cultural-db.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-agenda-cultural-shortcode.php';

add_action('plugins_loaded', function () {
    if (!defined('BD_SERVIDOR') || !defined('BD_NOMBRE') || !defined('BD_USUARIO') || !defined('BD_PASSWORD')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>Agenda Cultural:</strong> faltan las constantes BD_SERVIDOR, BD_NOMBRE, BD_USUARIO y/o BD_PASSWORD en wp-config.php. El plugin no puede conectar con la base de datos de eventos.</p></div>';
        });
        return;
    }

    Agenda_Cultural_Shortcode::init();
});

register_deactivation_hook(__FILE__, function () {
    delete_transient('agenda_cultural_eventos');
});
