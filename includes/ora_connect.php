<?php
# oracle_connect.php
// Este archivo contiene la información de conexión a Oracle 
// y crea una conexión utilizando OCI8.

// Constantes para la conexión:
define('ORA_USER', 'betagroup');
define('ORA_PASSWORD', 'beta123');
define('ORA_HOST', 'localhost/ORCL'); // Cambiar si usás otro servicio

// Conexión con Oracle:
$conn = @oci_connect(ORA_USER, ORA_PASSWORD, ORA_HOST);

if (!$conn) {
    $e = oci_error();
    trigger_error('No se pudo conectar a Oracle: ' . $e['message']);
}
?>
