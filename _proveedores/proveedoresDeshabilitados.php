<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');
?>

<hr>
<ol class="breadcrumb bc-3">
    <li><a href="../_dashboard/escritorio.php"><i class="entypo-home"></i>Home</a></li>
    <li class="active"><strong>Lista de Proveedores Deshabilitados</strong></li>
</ol>

<?php include("tabs.php"); ?>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Lista de Proveedores Deshabilitados</h2>
</div>
<br>

<table class="table table-bordered table-striped datatable" id="tabla-deshabilitados">
    <thead>
        <tr>
            <th><strong style="color: #999999;">Nombre</strong></th>
            <th><strong style="color: #999999;">Correo</strong></th>
            <th><strong style="color: #999999;">Dirección</strong></th>
            <th><strong style="color: #999999;">Teléfono</strong></th>
        </tr>
    </thead>
    <tbody>
<?php
$sql = "SELECT ID_PROVEEDOR, NOMBRE_PROVEEDOR, TELEFONO, CORREO, DIRECCION_PROVEEDOR FROM V_PROVEEDORES_DESHABILITADOS";
$stid = oci_parse($conn, $sql);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
    echo "<tr>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['NOMBRE_PROVEEDOR']) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['CORREO']) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['DIRECCION_PROVEEDOR']) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['TELEFONO'] ?? '') . "</td>";
    echo "</tr>";
}

oci_free_statement($stid);
?>
    </tbody>
</table>

<?php include("../includes/footer.php"); ?>
