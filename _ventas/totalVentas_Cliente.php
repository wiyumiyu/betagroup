<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');
?>

<hr>
<ol class="breadcrumb bc-3">
    <li><a href="../_dashboard/escritorio.php"><i class="entypo-home"></i>Home</a></li>
    <li class="active"><strong>Lista de Compras efectuadas por Cliente</strong></li>
</ol>

<?php
include("tabs.php");
?>








<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Lista de Compras efectuadas por Cliente</h2>
</div>
<br>

<!-- Tabla de clientes con compras -->
<table class="table table-bordered table-striped datatable" id="tabla-clientes-compras">
    <thead>
        <tr>
            <th><strong style="color: #999999;">Cliente</strong></th>
            <th><strong style="color: #999999;">Cantidad de Compras</strong></th>
        </tr>
    </thead>
    <tbody>
<?php
$sql = "SELECT ID_CLIENTE, NOMBRE_CLIENTE, CANTIDAD_VENTAS FROM VW_CANTIDAD_VENTAS_POR_CLIENTE ORDER BY CANTIDAD_VENTAS DESC";
$stid = oci_parse($conn, $sql);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
    echo "<tr>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['NOMBRE_CLIENTE']) . "</td>";
    echo "<td style='color: #4B4B4B; text-align: center;'>" . htmlspecialchars($row['CANTIDAD_VENTAS']) . "</td>";
    echo "</tr>";
}

oci_free_statement($stid);
?>
    </tbody>
</table>









<?php include("../includes/footer.php"); ?>