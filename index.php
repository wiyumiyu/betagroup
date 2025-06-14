
<?php
# Script 18.8 - login.php
$page_title = 'BETAGROUP';
//include ('includes/ora_connect.php');
//require ('includes/config.inc.php');

$op = "";
$url = '_dashboard/escritorio.php'; // Define the URL.

//****************************************

//
//require ('includes/config.inc.php');
//require (ORACLE);
//
//include ('includes/funciones.php');
//
//// Fields Submitted
//$username = "admin";
//$password = "a";
//
//// This array of data is returned for demo purpose, see assets/js/neon-forgotpassword.js
//$resp['submitted_data'] = $_POST;
//
//
//// Login success or invalid login data [success|invalid]
//// Your code will decide if username and password are correct
//$login_status = 'invalid';
//
//$q = "SELECT 
//USUARIO.id_usuario,
//USUARIO.nombre_usuario,
//USUARIO.contrasena,
//USUARIO.telefono,
//USUARIO.correo,
//USUARIO.rol,
//USUARIO.fecha_registro
//FROM
//USUARIO
//WHERE
//(USUARIO.correo='$username' AND USUARIO.contrasena='$password')";
//echo $q;
//$stid = oci_parse($conn, $q);
//
//if (!$stid) {
//    $e = oci_error($conn);
//    trigger_error("Query: $q<br />Oracle Parse Error: " . $e['message']);
//}
//
//$r = oci_execute($stid);
//
//if (!$r) {
//    $e = oci_error($stid);
//    trigger_error("Query: $q<br />Oracle Execution Error: " . $e['message']);
//}
//
//$row = oci_fetch_assoc($stid);
//if ($row) {
//    $login_status = 'success';
//
//    session_start();
//    $_SESSION = $row;
//
//    $url = '_dashboard/escritorio.php';
//    ob_end_clean(); // Limpia el buffer de salida
//}
//
//echo $login_status;
//
//
//
//





//if (!$conn) {
//    $e = oci_error();
//    echo "Error de conexión: " . $e['message'];
//} else {
//    echo "Conexión exitosa.";
//}
//
//
//?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="Neon Admin Panel" />
        <meta name="author" content="" />

        <title>Neon | Login</title>

        <link rel="stylesheet" href="assets/js/jquery-ui/css/no-theme/jquery-ui-1.10.3.custom.min.css">
        <link rel="stylesheet" href="assets/css/font-icons/entypo/css/entypo.css">
        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic">
        <link rel="stylesheet" href="assets/css/bootstrap.css">
        <link rel="stylesheet" href="assets/css/neon-core.css">
        <link rel="stylesheet" href="assets/css/neon-theme.css">
        <link rel="stylesheet" href="assets/css/neon-forms.css">
        <link rel="stylesheet" href="assets/css/custom.css">

        <script src="assets/js/jquery-1.11.0.min.js"></script>

        <!--[if lt IE 9]><script src="assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
                <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

    </head>
    <body class="page-body login-page login-form-fall" >


        <!-- This is needed when you send requests via Ajax --><script type="text/javascript">
            var baseurl = '';
        </script>

        <div class="login-container">

            <div class="login-header login-caret">

                <div class="login-content">

                    <a href="index.php" class="logo">
<!--                            <img src="assets/images/logos/betagroup.png" width="120" alt="" />-->
                    </a>

                    <p class="description"><font color=lightgrey size='5'><strong>Sistema Administrativo BetaGroup S.A.</strong></font></p>

                    <!-- progress bar indicator -->
                    <div class="login-progressbar-indicator">
                        <h3>43%</h3>
                        <span>logging in...</span>
                    </div>
                </div>

            </div>

            <div class="login-progressbar">
                <div></div>
            </div>

            <div class="login-form">

                <div class="login-content">

                    <div class="form-login-error">
                        <h3>Inicio de Sesión Inválido</h3>
                        <p>El <strong>Usuario</strong> o la <strong>contraseña</strong> es invalido.</p>
                    </div>

                    <form method="post" role="form" id="form_login" >

                        <div class="form-group">

                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="entypo-user"></i>
                                </div>

                                <input type="text" class="form-control" name="username" id="username" placeholder="Correo Electrónico"  />
                            </div>

                        </div>

                        <div class="form-group">

                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="entypo-key"></i>
                                </div>

                                <input type="password" class="form-control" name="password" id="password" placeholder="Contraseña" autocomplete="off" />
                            </div>

                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-login">
                                Iniciar Sesión
                                <i class="entypo-login"></i>
                            </button>
                        </div>
                     
                        
                        

                        <!-- Implemented in v1.1.4 -->				
<!--                        <div class="form-group">
                            <em>- or -</em>
                        </div>

                        <div class="form-group">

                            <button type="button" class="btn btn-default btn-lg btn-block btn-icon icon-left facebook-button">
                                Login with Facebook
                                <i class="entypo-facebook"></i>
                            </button>

                        </div>-->

                        <!-- 
                        
                        You can also use other social network buttons
                        <div class="form-group">
                        
                                <button type="button" class="btn btn-default btn-lg btn-block btn-icon icon-left twitter-button">
                                        Login with Twitter
                                        <i class="entypo-twitter"></i>
                                </button>
                                
                        </div>
                        
                        <div class="form-group">
                        
                                <button type="button" class="btn btn-default btn-lg btn-block btn-icon icon-left google-button">
                                        Login with Google+
                                        <i class="entypo-gplus"></i>
                                </button>
                                
                        </div> -->				
                    </form>


                    <div class="login-bottom-links">

                        <a href="extra-forgot-password.html" class="link">¿Olvidaste la Contraseña?</a>
<br><br><img src="assets/images/logos/betagroup.png" width="100" alt="" />
                        <br />

<!--                        <a href="#">ToS</a>  - <a href="#">Privacy Policy</a>-->

                    </div>

                    <div class="login-bottom-links">
                         
                    </div>                    
                </div>

            </div>

        </div>


        <!-- Bottom Scripts -->
        <script src="assets/js/gsap/main-gsap.js"></script>
        <script src="assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
        <script src="assets/js/bootstrap.js"></script>
        <script src="assets/js/joinable.js"></script>
        <script src="assets/js/resizeable.js"></script>
        <script src="assets/js/neon-api.js"></script>
        <script src="assets/js/jquery.validate.min.js"></script>
        <script src="assets/js/neon-login.js"></script>
        <script src="assets/js/neon-custom.js"></script>
        <script src="assets/js/neon-demo.js"></script>

    </body>
</html>