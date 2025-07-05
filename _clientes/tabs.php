<?php

$tab = 0;
if (isset($_GET["ta"])) {
    $tab = $_GET["ta"];
}
$t_lista = "";
$t_vista1 = "";

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
    <a class="nav-link <?php echo $t_lista; ?>" aria-current="page" href="clientes.php?op=2&ta=0">Lista de Clientes</a>
  </li>
  <li class="tab-pane  <?php echo $t_vista1; ?>">
    <a class="nav-link" <?php echo $t_vista1; ?> href="clientesClinica.php?op=2&ta=1">Vista de Clientes por Clinica</a>
  </li>
</ul>
<br>