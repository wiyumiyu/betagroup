<?php
include("../includes/header.html");
include("../includes/barralateral.php");
include("../includes/funciones.php");

$total_global = 0;
?>

<div class="container">
    <h2>Reporte General de Ventas</h2>

    <form method="GET" class="form-inline mb-4">
        <label>Desde:</label>
        <input type="date" name="inicio" required value="<?php echo $_GET['inicio'] ?? ''; ?>">
        <label style="margin-left:10px;">Hasta:</label>
        <input type="date" name="fin" required value="<?php echo $_GET['fin'] ?? ''; ?>">
        <button type="submit" class="btn btn-primary" style="margin-left:10px;">Consultar</button>
    </form>

<?php
if (isset($_GET['inicio']) && isset($_GET['fin'])) {
    $inicio = $_GET['inicio'];
    $fin = $_GET['fin'];

    $sql = "BEGIN :cursor := FUNC_REPORTE_VENTAS_RANGO(TO_DATE(:inicio, 'YYYY-MM-DD'), TO_DATE(:fin, 'YYYY-MM-DD')); END;";
    $stmt = oci_parse($conn, $sql);
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
    oci_bind_by_name($stmt, ":inicio", $inicio);
    oci_bind_by_name($stmt, ":fin", $fin);

    oci_execute($stmt); // <- Aquí fallaba antes
    oci_execute($cursor);

    while ($venta = oci_fetch_assoc($cursor)) {
        $id_venta = $venta['ID_VENTA'];
        $numero = $venta['NUMERO'];
        $fecha = date("d-m-Y", strtotime($venta['FECHA']));
        $impuestos = $venta['IMPUESTOS'];

        echo "<div class='card mb-3'><div class='card-body'>";
        echo "<h5>Factura #$numero - Fecha: $fecha - Impuesto: {$impuestos}%</h5>";

        // Obtener detalles
        $sql_det = "BEGIN :cursor := FUNC_DETALLE_VENTA(:id_venta); END;";
        $stmt_det = oci_parse($conn, $sql_det);
        $cursor_det = oci_new_cursor($conn);
        oci_bind_by_name($stmt_det, ":cursor", $cursor_det, -1, OCI_B_CURSOR);
        oci_bind_by_name($stmt_det, ":id_venta", $id_venta);
        oci_execute($stmt_det);
        oci_execute($cursor_det);

        $subtotal = 0;

        echo "<table class='table table-sm'><thead><tr>
              <th>Producto</th><th>Cantidad</th><th>Precio</th><th>Desc%</th><th>Total</th>
              </tr></thead><tbody>";

        while ($detalle = oci_fetch_assoc($cursor_det)) {
            $producto = $detalle['NOMBRE_PRODUCTO'];
            $cantidad = $detalle['CANTIDAD'];
            $precio = $detalle['PRECIO_UNITARIO'];
            $descuento = $detalle['DESCUENTO'];
            $total_producto = $detalle['TOTAL_PRODUCTO'];

            $subtotal += $total_producto;

            echo "<tr>
                    <td>$producto</td>
                    <td>$cantidad</td>
                    <td>₡" . number_format($precio, 2) . "</td>
                    <td>$descuento%</td>
                    <td>₡" . number_format($total_producto, 2) . "</td>
                  </tr>";
        }

        $total_venta = $subtotal * (1 + $impuestos / 100);
        $total_global += $total_venta;

        echo "</tbody></table>";
        echo "<p><strong>Total con impuestos: ₡" . number_format($total_venta, 2) . "</strong></p>";
        echo "</div></div>";

        oci_free_statement($stmt_det);
        oci_free_statement($cursor_det);
    }

    echo "<h4 class='text-right'>TOTAL GLOBAL: ₡" . number_format($total_global, 2) . "</h4>";

    oci_free_statement($stmt);
    oci_free_statement($cursor);
}
?>

</div>

<?php include("../includes/footer.php"); ?>
