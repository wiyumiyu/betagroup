<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');
?>

<hr>
<ol class="breadcrumb bc-3">
    <li><a href="../_dashboard/escritorio.php"><i class="entypo-home"></i>Home</a></li>
    <li class="active"><strong>Lista de Usuarios Deshabilitados</strong></li>
</ol>

<?php
include("tabs.php");
?>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Lista de Usuarios Deshabilitados</h2>
</div>
<br>

<!-- Tabla de usuarios deshabilitados -->
<table class="table table-bordered table-striped datatable" id="tabla-deshabilitados">
    <thead>
        <tr>
            <th><strong style="color: #999999;">Usuario</strong></th>
            <th><strong style="color: #999999;">Teléfono</strong></th>
            <th><strong style="color: #999999;">Correo</strong></th>
            <th><strong style="color: #999999;">Rol</strong></th>
            <th><strong style="color: #999999;">Fecha de Registro</strong></th>
        </tr>
    </thead>
    <tbody>
<?php

$sql = "SELECT ID_USUARIO, NOMBRE_USUARIO, TELEFONO, CORREO, ROL, FECHA_REGISTRO FROM V_USUARIOS_DESHABILITADOS";
$stid = oci_parse($conn, $sql);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
    echo "<tr>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['NOMBRE_USUARIO']) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['TELEFONO'] ?? '') . "</td>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['CORREO']) . "</td>";
    $rolTexto = ($row['ROL'] == 1) ? "Administrador" : "Vendedor";
    echo "<td style='color: #4B4B4B;'>$rolTexto</td>";
    echo "<td style='color: #4B4B4B;'>" . date("d-m-Y", strtotime($row['FECHA_REGISTRO'])) . "</td>";
    echo "</tr>";
}

oci_free_statement($stid);
?>
    </tbody>
</table>

<?php include("../includes/footer.php"); ?>