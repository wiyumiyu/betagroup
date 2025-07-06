<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_GET["ta"] = 1;
}

include("tabs.php");

$tipoSeleccionado = "";
$resultado = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipoSeleccionado = $_POST["id_tipo_clinica"];

    $stmt = oci_parse($conn, "BEGIN obtener_clientes_por_clinica(:tipo_id, :cursor); END;");
    $cursor = oci_new_cursor($conn);

    oci_bind_by_name($stmt, ":tipo_id", $tipoSeleccionado);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);

    oci_execute($stmt);
    oci_execute($cursor);

    while ($row = oci_fetch_assoc($cursor)) {
        $resultado[] = $row;
    }

    oci_free_statement($stmt);
    oci_free_statement($cursor);
}
?>

<h2>Filtrar Clientes por Tipo de Clínica</h2>

<form method="POST" action="clientesClinica.php?op=3&ta=1" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 10px;">
        <select name="id_tipo_clinica" class="form-control" required>
            <option value="">-- Seleccione Tipo de Clínica --</option>
<?php
$stmt = oci_parse($conn, "BEGIN listar_tipos_clinica(:cursor); END;");
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
oci_execute($stmt);
oci_execute($cursor);

while ($row = oci_fetch_array($cursor, OCI_ASSOC)) {
    $selected = ($tipoSeleccionado == $row["ID_TIPO_CLINICA"]) ? "selected" : "";
    echo "<option value='{$row["ID_TIPO_CLINICA"]}' $selected>" . htmlspecialchars($row["DESCRIPCION"]) . "</option>";
}

oci_free_statement($stmt);
oci_free_statement($cursor);
?>
        </select>
        <button type="submit" class="btn btn-info">Consultar</button>
    </div>
</form>

<?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
<table class="table table-bordered table-striped datatable">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Tipo de Clínica</th>
        </tr>
    </thead>
    <tbody>
<?php
    foreach ($resultado as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['NOMBRE_CLIENTE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['CORREO']) . "</td>";
        echo "<td>" . htmlspecialchars($row['TIPO_CLINICA']) . "</td>";
        echo "</tr>";
    }
?>
    </tbody>
</table>
<?php endif; ?>

<?php include("../includes/footer.php"); ?>
