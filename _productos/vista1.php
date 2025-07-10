<?php
include('../includes/header.html');
include('../includes/barralateral.php');
include('../includes/funciones.php');
include("tabs.php");

// Llamar al procedimiento que usa la vista
$sql = "BEGIN PROC_LISTAR_PRODUCTOS_MENOS_VENDIDOS(:cursor); END;";
$stid = oci_parse($conn, $sql);
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);

oci_execute($stid);
oci_execute($cursor);
?>

<h2>Top 5 Productos Menos Vendidos</h2>

<table class="table table-bordered table-striped datatable">
    <thead>
        <tr>
            <th>ID Producto</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Total Vendido</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = oci_fetch_assoc($cursor)) : ?>
            <tr>
                <td><?= htmlspecialchars($row['ID_PRODUCTO']) ?></td>
                <td><?= htmlspecialchars($row['NOMBRE_PRODUCTO']) ?></td>
                <td><?= htmlspecialchars($row['NOMBRE_CATEGORIA']) ?></td>
                <td><?= htmlspecialchars($row['TOTAL_VENDIDO']) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
oci_free_statement($stid);
oci_free_statement($cursor);
include("../includes/footer.php");
oci_close($conn);
?>
