<?php

$tab = 0;
if (isset($_GET["ta"])) {
    $tab = $_GET["ta"];
}
$t_lista = "";
$t_vista1 = "";
$t_vista2 = "";

switch ($tab) {
    case 0:
        $t_lista = "active";
        break;
    case 1:
        $t_vista1 = "active";
        break;
}
?>


<br>
<ul class="nav nav-tabs bordered">
  <li class="tab-pane <?php echo $t_lista; ?>">
    <a class="nav-link <?php echo $t_lista; ?>" aria-current="page" href="productos.php?op=2&ta=0">Lista de Productos</a>
  </li>
  <li class="tab-pane  <?php echo $t_vista1; ?>">
    <a class="nav-link" <?php echo $t_vista1; ?> href="vista1.php?op=2&ta=1">Productos menos vendidos</a>
  </li>

</ul>
<br>