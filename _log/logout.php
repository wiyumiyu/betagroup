
<?php

# Script 18.9 - logout.php
// This is the logout page for the site.
//require ('../includes/config.inc.php');
$page_title = 'Logout';
//


// If no first_name session variable exists, redirect the user:


$url = '../index.php'; // Define the URL.
echo "<script>
  document.write('Hola Mundo');
</script>";
if (!isset($_SESSION['nombre'])) {
    ob_end_clean(); // Delete the buffer.
    header("Location: $url");
    exit(); // Quit the script.
    
} else { // Log out the user.
    //echo 2;
    $_SESSION = array(); // Destroy the variables.
    
    session_destroy(); // Destroy the session itself.
    setcookie(session_name(), '', time() - 3600); // Destroy the cookie.
    header("Location: $url");
}

include ('../includes/header.html');
// Print a customized message:
//echo '<h3>No has iniciado sesi�n</h3>';

//include ('../includes/footer.html');
?>