<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');
?>

<hr>
<ol class="breadcrumb bc-3">
    <li><a href="../_dashboard/escritorio.php"><i class="entypo-home"></i>Home</a></li>
    <li class="active"><strong>Lista de Clientes por Clínica</strong></li>
</ol>

<?php
include("tabs.php");

// ---------- LÓGICA PHP ----------
$tipoSeleccionado = "";
$resultado = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipoSeleccionado = $_POST["id_tipo_clinica"];

    $sql = "SELECT * FROM V_CLIENTES_CON_TIPO_CLINICA WHERE ID_TIPO_CLINICA = :tipo_id";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":tipo_id", $tipoSeleccionado);
    oci_execute($stmt);

    while ($row = oci_fetch_assoc($stmt)) {
        $resultado[] = $row;
    }

    oci_free_statement($stmt);
}
?>

<h2>Filtrar Clientes por Tipo de Clínica</h2>

<form method="POST" action="clientesClinica.php" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 10px;">
        <select name="id_tipo_clinica" class="form-control" required>
            <option value="">-- Seleccione Tipo de Clínica --</option>
<?php
$sqlTipos = "SELECT ID_TIPO_CLINICA, DESCRIPCION FROM TIPO_CLINICA ORDER BY DESCRIPCION";
$stidTipos = oci_parse($conn, $sqlTipos);
oci_execute($stidTipos);
while ($row = oci_fetch_array($stidTipos, OCI_ASSOC)) {
    $selected = ($tipoSeleccionado == $row["ID_TIPO_CLINICA"]) ? "selected" : "";
    echo "<option value='{$row["ID_TIPO_CLINICA"]}' $selected>" . htmlspecialchars($row["DESCRIPCION"]) . "</option>";
}
oci_free_statement($stidTipos);
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
