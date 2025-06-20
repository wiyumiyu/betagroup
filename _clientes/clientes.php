<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');

$del = "";
$edt = "";
$edtVer = "";
$linkAceptar = "#";

if (isset($_GET['edt'])) {
    $edt = $_GET['edt'];
}
if (isset($_GET['del'])) {
    $del = $_GET['del'];
}

// Eliminar cliente si viene ?del2
if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];
    $sql = "BEGIN eliminar_cliente(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $del2);

    if (oci_execute($stmt)) {
        echo "<script>window.location.href = 'clientes.php';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error al eliminar el cliente: " . $e['message'];
    }

    oci_free_statement($stmt);
}

// Insertar o actualizar cliente
if (isset($_POST['submitted'])) {
    $nombre = trim($_POST["nombre_cliente"]);
    $correo = trim($_POST["correo"]);
    $tipo = trim($_POST["id_tipo_clinica"]);

    if (isset($_GET['edt'])) {
        $id = $_GET['edt'];
        $sql = "BEGIN actualizar_cliente(:id, :nombre, :correo, :tipo); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $id);
    } else {
        $sql = "BEGIN insertar_cliente(:id, :nombre, :correo, :tipo); END;";
        $stmt = oci_parse($conn, $sql);
        $nuevo_id = rand(1000, 9999);
        oci_bind_by_name($stmt, ":id", $nuevo_id);
    }

    oci_bind_by_name($stmt, ":nombre", $nombre);
    oci_bind_by_name($stmt, ":correo", $correo);
    oci_bind_by_name($stmt, ":tipo", $tipo);

    if (oci_execute($stmt)) {
        echo "<script>window.location='clientes.php';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    }

    oci_free_statement($stmt);
    oci_close($conn);
}
?>

<!-- ------------------ INTERFAZ HTML ---------------------- -->
<hr>
<ol class="breadcrumb bc-3">
    <li><a href="..\_dashboard\escritorio.php"><i class="entypo-home"></i>Home</a></li>
    <li class="active"><strong>Lista de Clientes</strong></li>
</ol>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Lista de Clientes</h2>
    <button onclick="abrirModal()" class="btn btn-success">Nuevo Cliente</button>
</div>
<br>

<table class="table table-bordered table-striped datatable" id="table-2">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Tipo de Clínica</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
<?php
$sql = "BEGIN listar_clientes(:cursor); END;";
$stid = oci_parse($conn, $sql);
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
oci_execute($stid);
oci_execute($cursor);

while ($row = oci_fetch_assoc($cursor)) {
    $id = $row['ID_USUARIO'];
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['NOMBRE_CLIENTE']) . "</td>";
    echo "<td>" . htmlspecialchars($row['CORREO']) . "</td>";
    echo "<td>" . htmlspecialchars($row['TIPO_CLINICA']) . "</td>";
    echo "<td>
            <a href='clientes.php?edt=$id' class='btn btn-default'><i class='entypo-pencil'></i></a>
            <a href='clientes.php?del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
          </td>";
    echo "</tr>";
}
oci_free_statement($stid);
oci_free_statement($cursor);
?>
    </tbody>
</table>

<!-- MODAL CLIENTE -->
<div id="modal-confirmar" class="modalx">
    <div class="modalx-content">
<?php
$nombre = $correo = $tipo = "";
$tipoEdit = "Agregar nuevo";
$edtVer = "";

if (isset($_GET["edt"])) {
    $id = $_GET["edt"];
    $sql = "SELECT NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA FROM CLIENTE WHERE ID_USUARIO = :id";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":id", $id);
    oci_execute($stid);
    if ($row = oci_fetch_array($stid, OCI_ASSOC)) {
        $nombre = htmlspecialchars($row["NOMBRE_CLIENTE"]);
        $correo = htmlspecialchars($row["CORREO"]);
        $tipo = $row["ID_TIPO_CLINICA"];
    }
    oci_free_statement($stid);
    $tipoEdit = "Editar";
    $edtVer = "?edt=$id";
}
?>

        <h3 class='modalx-titulo'><?php echo $tipoEdit; ?> Cliente</h3>
        <form action="clientes.php<?php echo $edtVer; ?>" method="POST">
            <label for="nombre_cliente">Nombre:</label>
            <input type="text" id="nombre_cliente" class="form-control" name="nombre_cliente" value="<?php echo $nombre; ?>" required>
            <br>
            <label for="correo">Correo:</label>
            <input type="email" id="correo" class="form-control" name="correo" value="<?php echo $correo; ?>" required>
            <br>
            <label for="id_tipo_clinica">Tipo de Clínica:</label>
            <select id="id_tipo_clinica" name="id_tipo_clinica" class="form-control">
<?php
// Cargar todas las clínicas desde la base de datos para el select
$sql = "SELECT ID_TIPO_CLINICA, DESCRIPCION FROM TIPO_CLINICA";
$stid = oci_parse($conn, $sql);
oci_execute($stid);
while ($row = oci_fetch_array($stid, OCI_ASSOC)) {
    $selected = ($row['ID_TIPO_CLINICA'] == $tipo) ? "selected" : "";
    echo "<option value='{$row['ID_TIPO_CLINICA']}' $selected>" . htmlspecialchars($row['DESCRIPCION']) . "</option>";
}
oci_free_statement($stid);
?>
            </select>
            <br>
            <input type='hidden' name='submitted' value='TRUE' />
            <div class="modalx-footer">
                <a href='clientes.php' class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN PARA ELIMINAR -->
<div id="modal-eliminar" class="modalx">
    <div class="modalx-content">
        <h3 class="modalx-titulo">Confirmar eliminación</h3>
        <p class="modalx-texto">¿Estás segura de que deseas eliminar este cliente?</p>
        <div class="modalx-footer">
            <a href='clientes.php' class="btn-cancelar">Cancelar</a>
            <a href='clientes.php?del2=<?php echo $del; ?>' class="btn-confirmar">Eliminar</a>
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
