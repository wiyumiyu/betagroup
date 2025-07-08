<?php

$tab = 0;
if (isset($_GET["ta"])) {
    $tab = $_GET["ta"];
}
$t_lista = "";

switch ($tab) {
    case 0:
        $t_lista = "active";
        break;

}
?>


<br>
<ul class="nav nav-tabs bordered">
  <li class="tab-pane <?php echo $t_lista; ?>">
    <a class="nav-link <?php echo $t_lista; ?>" aria-current="page" href="bitacora.php?op=0&ta=0">Bitácora</a>
  </li>


</ul>
<br>