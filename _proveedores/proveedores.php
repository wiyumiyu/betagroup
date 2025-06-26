<?php
include("../includes/conexion.php");

$del = $edt = "";

if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];
    $sql = "BEGIN eliminar_proveedor(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $del2);
    oci_execute($stmt);
    echo "<script>window.location='proveedores.php';</script>";
}

if (isset($_POST['submitted'])) {
    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);
    $correo = trim($_POST["correo"]);
    $direccion = trim($_POST["direccion"]);

    if (isset($_GET['edt'])) {
        $id = $_GET['edt'];
        $sql = "BEGIN actualizar_proveedor(:id, :nombre, :telefono, :correo, :direccion); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $id);
    } else {
        $sql = "BEGIN insertar_proveedor(:nombre, :telefono, :correo, :direccion); END;";
        $stmt = oci_parse($conn, $sql);
    }

    oci_bind_by_name($stmt, ":nombre", $nombre);
    oci_bind_by_name($stmt, ":telefono", $telefono);
    oci_bind_by_name($stmt, ":correo", $correo);
    oci_bind_by_name($stmt, ":direccion", $direccion);

    if (oci_execute($stmt)) {
        echo "<script>window.location='proveedores.php';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    }
}

if (isset($_GET['edt'])) {
    $edt = $_GET['edt'];
    $sql = "SELECT * FROM PROVEEDOR WHERE ID_PROVEEDOR = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $edt);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);

    $nombre = $row['NOMBRE'];
    $telefono = $row['TELEFONO'];
    $correo = $row['CORREO'];
    $direccion = $row['DIRECCION'];
} else {
    $nombre = $telefono = $correo = $direccion = "";
}
?>

<?php include("../includes/header.php"); ?>

<div class="container">
    <h2>Gestión de Proveedores</h2>
    <form method="POST" action="proveedores.php<?php if ($edt) echo '?edt=' . $edt; ?>">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo $nombre; ?>" required>
        </div>
        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control" value="<?php echo $telefono; ?>">
        </div>
        <div class="form-group">
            <label>Correo</label>
            <input type="email" name="correo" class="form-control" value="<?php echo $correo; ?>">
        </div>
        <div class="form-group">
            <label>Dirección</label>
            <textarea name="direccion" class="form-control"><?php echo $direccion; ?></textarea>
        </div>
        <input type="hidden" name="submitted" value="TRUE">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="proveedores.php" class="btn btn-secondary">Cancelar</a>
    </form>

    <hr>

    <h3>Lista de Proveedores</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "BEGIN listar_proveedores(:cursor); END;";
            $stid = oci_parse($conn, $sql);
            $cursor = oci_new_cursor($conn);
            oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
            oci_execute($stid);
            oci_execute($cursor);
            while ($row = oci_fetch_assoc($cursor)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['NOMBRE']) . "</td>";
                echo "<td>" . htmlspecialchars($row['TELEFONO']) . "</td>";
                echo "<td>" . htmlspecialchars($row['CORREO']) . "</td>";
                echo "<td>" . htmlspecialchars($row['DIRECCION']) . "</td>";
                echo "<td>
                    <a href='proveedores.php?edt=" . $row['ID_PROVEEDOR'] . "' class='btn btn-sm btn-warning'>Editar</a>
                    <a href='proveedores.php?del2=" . $row['ID_PROVEEDOR'] . "' class='btn btn-sm btn-danger'>Eliminar</a>
                </td>";
                echo "</tr>";
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            ?>
        </tbody>
    </table>
</div>

<?php include("../includes/footer.php"); ?>

