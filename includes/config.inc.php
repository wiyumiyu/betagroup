<?php # config.inc.php
/* Este script:
 * - Define constantes y configuración global
 * - Controla cómo se manejan los errores
 * - Define funciones útiles
 */

// ********************************** //
// ************ SETTINGS ************ //

// Modo del sitio: FALSE = desarrollo, TRUE = producción
define('LIVE', FALSE);

// Correo del administrador:
define('EMAIL', 'victor.rodriguezcerdas@ucr.ac.cr');

// URL base para redirecciones:
define('BASE_URL', 'http://localhost/betagroup/');

// Ruta al archivo de conexión Oracle:
define('ORACLE', '../includes/ora_connect.php');

// Zona horaria para PHP:
date_default_timezone_set('America/Costa_Rica');

// ************ SETTINGS ************ //
// ********************************** //


// ****************************************** //
// ************ ERROR MANAGEMENT ************ //

function my_error_handler($e_number, $e_message, $e_file, $e_line)
{
    $message = "<p>Error en el script '$e_file' en la línea $e_line: $e_message<br />";
    $message .= "Fecha/Hora: " . date('j-n-Y  H:i:s') . "<br />";
    
    // Eliminar uso de $e_vars o reemplazarlo por error_get_last() si necesitás más contexto

    if (!LIVE) {
        echo '<div class="error">' . $message . '</div><br />';
    } else {
        mail(EMAIL, 'Error en el sistema', $message, 'From: sistema@betagroup.local');
        if ($e_number != E_NOTICE) {
            echo '<div class="error">Error en el sistema. Disculpe las molestias.</div><br />';
        }
    }
}

set_error_handler('my_error_handler');

// Límites de subida y ejecución:
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);

// ************ ERROR MANAGEMENT ************ //
// ****************************************** //
?>
