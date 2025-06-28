<?php

$resp = array(); 

require ('../includes/config.inc.php'); // conexión a la base de datos de Oracle (parte global)
require (ORACLE); // (conexión directa con Oracle)
include ('../includes/funciones.php'); // colección de funciones en php

// Variables del formulario
$username = $_POST["username"];
$password = $_POST["password"];
$login_status = 'invalid'; // esto sirva para AJAX (el 100%, la animacion del login)

$sql = "BEGIN VALIDAR_LOGIN(:correo, :pass, :resultado, :id_usuario, :nombre, :rol); END;";
$stid = oci_parse($conn, $sql); // aqui hace la consulta, se convierte en idioma oracle la info

// definir bien quien es quien... los campos que vienen de la BD
oci_bind_by_name($stid, ':correo', $username);
oci_bind_by_name($stid, ':pass', $password);
oci_bind_by_name($stid, ':resultado', $resultado, 10);
oci_bind_by_name($stid, ':id_usuario', $id_usuario, 10);
oci_bind_by_name($stid, ':nombre', $nombre, 100);
oci_bind_by_name($stid, ':rol', $rol, 50);

// ejecuta todo lo que definimos 
$r = oci_execute($stid);

if ($r && $resultado == 1) {
    session_start();
    $_SESSION['id_usuario'] = $id_usuario;
    $_SESSION['nombre'] = $nombre;
    $_SESSION['rol'] = $rol;

    $login_status = 'success';
    $resp['redirect_url'] = '_dashboard/escritorio.php';
}

oci_free_statement($stid);
oci_close($conn);



// Resultado final
$resp['login_status'] = $login_status;
$resp['submitted_data'] = $_POST; // Solo para depurar

echo json_encode($resp);
