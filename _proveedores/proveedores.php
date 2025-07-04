<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');

$ii = "123";
$rr1 = "A";
$linkAceptar = "#";

$del = "";
$edt = "";
$edtVer = "";

if (isset($_GET['edt'])) {
    $edt = $_GET['edt'];
}

if (isset($_GET['del'])) {
    $del = $_GET['del'];
}

if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];

    $sql = "BEGIN eliminar_proveedor(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $del2);

    if (oci_execute($stmt)) {
        echo "<script>window.location.href = 'proveedores.php';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error al eliminar el proveedor: " . $e['message'];
    }

    oci_free_statement($stmt);
}

if (isset($_POST['submitted'])) {
    $nombre_proveedor = trim($_POST["nombre_proveedor"]);
    $correo = trim($_POST["correo"]);
    $direccion = trim($_POST["direccion"]);
    $estado = $_POST["estado"];

    if (isset($_GET['edt'])) {
        $id = $_GET['edt'];
        $sql = "BEGIN actualizar_proveedor(:id, :nombre, :correo, :direccion, :estado); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $id);
    } else {
        $sql = "BEGIN insertar_proveedor(:nombre, :correo, :direccion, :estado); END;";
        $stmt = oci_parse($conn, $sql);
    }

    oci_bind_by_name($stmt, ":nombre", $nombre_proveedor);
    oci_bind_by_name($stmt, ":correo", $correo);
    oci_bind_by_name($stmt, ":direccion", $direccion);
    oci_bind_by_name($stmt, ":estado", $estado);

    if (oci_execute($stmt)) {
        echo "<script>window.location.href='proveedores.php?op=3&pc=1';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    }

    oci_free_statement($stmt);
    oci_close($conn);
}

function cargarSelect($conn, $proc, $idCampo, $nomCampo, $name) {
    $stid = oci_parse($conn, "BEGIN $proc(:cursor); END;");
    $cur = oci_new_cursor($conn);
    oci_bind_by_name($stid, ":cursor", $cur, -1, OCI_B_CURSOR);
    oci_execute($stid);
    oci_execute($cur);
    echo "<select class='form-control' name='$name' required>";
    while ($r = oci_fetch_assoc($cur)) {
        echo "<option value='{$r[$idCampo]}'>{$r[$nomCampo]}</option>";
    }
    echo "</select>";
    oci_free_statement($stid);
    oci_free_statement($cur);
}
?>

<!-- ------------------ INTERFAZ HTML ---------------------- -->
<hr>
<ol class="breadcrumb bc-3">
  <li><a href="../_dashboard/escritorio.php"><i class="entypo-home"></i>Home</a></li>
  <li class="active"><strong>Lista de Proveedores</strong></li>
</ol>

<?php include("tabs.php"); ?>
<div style="display:flex;justify-content:space-between;align-items:center;">
  <h2>Lista de Proveedores</h2>
  <button onclick="abrirModal()" class="btn btn-success"> Nuevo Proveedor</button>
</div>
<br>
<table class="table table-bordered table-striped datatable" id="table-2">
<thead>
    <tr>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Dirección</th>
        <th>Fecha registro</th>
        <th>Acciones</th>
    </tr>
</thead>
<tbody>
<?php
$sql = "BEGIN LISTAR_PROVEEDORES(:cursor); END;";
$stid = oci_parse($conn, $sql);
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
oci_execute($stid);
oci_execute($cursor);

while ($row = oci_fetch_assoc($cursor)) {
    $id = $row['ID_PROVEEDOR'];
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['NOMBRE_PROVEEDOR']) . "</td>";
    echo "<td>" . htmlspecialchars($row['CORREO']) . "</td>";
    echo "<td>" . htmlspecialchars($row['DIRECCION_PROVEEDOR']) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . date("d-m-Y", strtotime($row['FECHA_REGISTRO'])) . "</td>";
    echo "<td>
            <a href='proveedores.php?edt=$id' class='btn btn-default'><i class='entypo-pencil'></i></a>
            <a href='proveedores.php?del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
          </td>";
    echo "</tr>";
}
oci_free_statement($stid);
oci_free_statement($cursor);
?>
</tbody>
</table>

<!-- MODAL: Agregar / Editar -->

<div id="modal-confirmar" class="modalx">
  <div class="modalx-content">
<?php
$nombre_proveedor = $correo = $direccion = "";
$estado = 1;
$tipoEdit = "Agregar nuevo";
$edtVer = "";

if (isset($_GET["edt"])) {
    $id = $_GET["edt"];
    $sql = "SELECT NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO FROM PROVEEDOR WHERE ID_PROVEEDOR = :id";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":id", $id);
    oci_execute($stid);
    if ($row = oci_fetch_array($stid, OCI_ASSOC)) {
        $nombre_proveedor = htmlspecialchars($row["NOMBRE_PROVEEDOR"]);
        $correo = htmlspecialchars($row["CORREO"]);
        $direccion = htmlspecialchars($row["DIRECCION_PROVEEDOR"]);
        $estado = $row["ESTADO"];
    }
    oci_free_statement($stid);
    $tipoEdit = "Editar";
    $edtVer = "?edt=$id";
}

echo "<h3 class='modalx-titulo'>$tipoEdit proveedor</h3>";
?>
    <form action="proveedores.php<?php echo $edtVer; ?>" method="POST">
        <label for="nombre_proveedor">Nombre:</label>
        <input type="text" id="nombre_proveedor" class="form-control" name="nombre_proveedor" value="<?php echo $nombre_proveedor; ?>" required>
        <br>
        <label for="correo">Correo:</label>
        <input type="email" id="correo" class="form-control" name="correo" value="<?php echo $correo; ?>" required>
        <br>
        <label for="direccion">Dirección:</label>
        <input type="text" id="direccion" class="form-control" name="direccion" value="<?php echo $direccion; ?>" required>
        <br>
        <label for="estado">Estado:</label>
        <select name="estado" class="form-control" required>
            <option value="1" <?php if ($estado == 1) echo 'selected'; ?>>Habilitado</option>
            <option value="0" <?php if ($estado == 0) echo 'selected'; ?>>Deshabilitado</option>
        </select>
        <br>
        <input type="hidden" name="submitted" value="TRUE" />
        <div class="modalx-footer">
            <a href='proveedores.php' class="btn-cancelar">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar</button>
        </div>
    </form>
  </div>
</div>

<!-- MODAL DE CONFIRMACIÓN PARA ELIMINAR -->
<div id="modal-eliminar" class="modalx">
    <div class="modalx-content">
        <h3 class="modalx-titulo">Confirmar eliminación</h3>
        <p class="modalx-texto">¿Estás seguro de que deseas eliminar este proveedor?</p>
        <div class="modalx-footer">
            <a href='proveedores.php' class="btn-cancelar">Cancelar</a>
            <a href='proveedores.php?del2=<?php echo $del; ?>' class="btn-confirmar">Eliminar</a>
        </div>
    </div>
</div>

<!-- SCRIPTS PARA MODALES -->
<script>
    function abrirModal() {
        document.getElementById('modal-confirmar').style.display = 'block';
    }

    function cerrarModal() {
        document.getElementById('modal-confirmar').style.display = 'none';
    }

    window.onclick = function (event) {
        const modal = document.getElementById('modal-confirmar');
        if (event.target == modal)
            cerrarModal();
    };

    $(window).on('load', function () {
        var edt = '<?php echo $edt; ?>';
        if (edt != "") {
            document.getElementById('modal-confirmar').style.display = 'block';
        }

        var del = '<?php echo $del; ?>';
        if (del != "") {
            document.getElementById('modal-eliminar').style.display = 'block';
        }
    });
</script>

<?php include("../includes/footer.php"); ?>
