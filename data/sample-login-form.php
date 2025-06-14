<?php
/*
	Sample Processing of Forgot password form via ajax
	Page: extra-register.html
*/

# Response Data Array
$resp = array();


require ('../includes/config.inc.php');
require (ORACLE);

//mysqli_set_charset($dbc, "utf8");
include ('../includes/funciones.php');

// Fields Submitted
$username = $_POST["username"];
$password = $_POST["password"];

// This array of data is returned for demo purpose, see assets/js/neon-forgotpassword.js
$resp['submitted_data'] = $_POST;

// Login success or invalid login data [success|invalid]
// Your code will decide if username and password are correct
$login_status = 'invalid';

//if  (!(vercadena2("@", $username, false) )){
//    $username = $username . "@xxxxxx";
//}

$q = "SELECT 
USUARIO.id_usuario,
USUARIO.nombre_usuario,
USUARIO.contrasena,
USUARIO.telefono,
USUARIO.correo,
USUARIO.rol,
USUARIO.fecha_registro
FROM
USUARIO
WHERE
(USUARIO.correo='$username' AND USUARIO.contrasena='$password')";

$stid = oci_parse($conn, $q);



if (!$stid) {
    $e = oci_error($conn);
    trigger_error("Query: $q<br />Oracle Parse Error: " . $e['message']);
}

$r = oci_execute($stid);

if (!$r) {
    $e = oci_error($stid);
    trigger_error("Query: $q<br />Oracle Execution Error: " . $e['message']);
}



//oci_fetch_assoc($stid) recupera una fila como un arreglo asociativo.
//Si hay una fila, eso equivale a que num_rows == 1.
$row = oci_fetch_assoc($stid);

if ($row) {
    $login_status = 'success';

    session_start();
    $_SESSION = $row;

    $url = '_dashboard/escritorio.php';
    //ob_end_clean(); // Limpia el buffer de salida
}

//*******************************************************************************************
oci_free_statement($stid);
oci_close($conn);



$resp['login_status'] = $login_status;


// Login Success URL
if($login_status == 'success')
{
	// If you validate the user you may set the user cookies/sessions here
		#setcookie("logged_in", "user_id");
		#$_SESSION["logged_user"] = "user_id";
	
	// Set the redirect url after successful login
	$resp['redirect_url'] = $url;
}






//$login_status = 'success';
$resp['login_status'] = $login_status;

echo json_encode($resp);