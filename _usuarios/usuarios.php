<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');

$del = "";
$edt = "";
$edtVer = "";
$op = "";
$ta = "";

if (isset($_GET['op'])) {
    $op = $_GET['op'];
}
if (isset($_GET['ta'])) {
    $ta = $_GET['ta'];
}

if (isset($_GET['edt'])) {
    $edt = $_GET['edt'];
}
if (isset($_GET['del'])) {
    $del = $_GET['del'];
}
if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];
    $sql = "BEGIN eliminar_usuario(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $del2);
    if (oci_execute($stmt)) {
        echo "<script>window.location.href = 'usuarios.php?op=$op&ta=$ta';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error al eliminar el usuario: " . $e['message'];
    }
    oci_free_statement($stmt);
}

if (isset($_POST['submitted'])) {
    $nombre = trim($_POST["nombre_usuario"]);
    $contrasena = trim($_POST["contrasena"]);
    $telefono = trim($_POST["telefono"]);
    $correo = trim($_POST["correo"]);
    $rol = trim($_POST["rol"]);
    $estado = isset($_POST["estado"]) ? intval($_POST["estado"]) : 1;

    if (isset($_GET['edt'])) {
        $id = $_GET['edt'];

        if ($contrasena != "") {
            $sql = "BEGIN actualizar_usuario(:id, :nombre, :contrasena, :telefono, :correo, :rol, :estado); END;";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":contrasena", $contrasena);
        } else {
            $sql = "BEGIN actualizar_usuario_sc(:id, :nombre, :telefono, :correo, :rol, :estado); END;";
            $stmt = oci_parse($conn, $sql);
        }

        oci_bind_by_name($stmt, ":id", $id);
        oci_bind_by_name($stmt, ":estado", $estado);
    } else {
        $sql = "BEGIN insertar_usuario(:nombre, :contrasena, :telefono, :correo, :rol); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":contrasena", $contrasena);
    }

    oci_bind_by_name($stmt, ":nombre", $nombre);
    oci_bind_by_name($stmt, ":telefono", $telefono);
    oci_bind_by_name($stmt, ":correo", $correo);
    oci_bind_by_name($stmt, ":rol", $rol);

    if (oci_execute($stmt)) {
        echo "<script>window.location='usuarios.php?op=$op&ta=$ta';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    }

    oci_free_statement($stmt);
}
?>

<?php include("tabs.php"); ?>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Lista de Usuarios</h2>
    <button onclick="abrirModal()" class="btn btn-success">Nuevo Usuario</button>
</div>
<br>

<table class="table table-bordered table-striped datatable" id="table-2">
    <thead>
        <tr>
            <th><strong style="color: #999999;">Usuario</strong></th>
            <th><strong style="color: #999999;">Teléfono</strong></th>
            <th><strong style="color: #999999;">Correo</strong></th>
            <th><strong style="color: #999999;">Rol</strong></th>
            <th><strong style="color: #999999;">Registro</strong></th>
            <th><strong style="color: #999999;">Acciones</strong></th>
        </tr>
    </thead>
    <tbody>
<?php
$sql = "BEGIN LISTAR_USUARIOS(:cursor); END;";
$stid = oci_parse($conn, $sql);
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
oci_execute($stid);
oci_execute($cursor);

while ($row = oci_fetch_assoc($cursor)) {
    $id = $row['ID_USUARIO'];
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['NOMBRE_USUARIO']) . "</td>";
    echo "<td>" . htmlspecialchars($row['TELEFONO'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['CORREO']) . "</td>";
    $rolTexto = ($row['ROL'] == 1) ? "Administrador" : "Vendedor";
    echo "<td>$rolTexto</td>";
    echo "<td>" . date("d-m-Y", strtotime($row['FECHA_REGISTRO'])) . "</td>";
    echo "<td>
            <a href='usuarios.php?op=$op&ta=$ta&edt=$id' class='btn btn-default'><i class='entypo-pencil'></i></a>
            <a href='usuarios.php?op=$op&ta=$ta&del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
          </td>";
    echo "</tr>";
}

oci_free_statement($stid);
oci_free_statement($cursor);
?>
    </tbody>
</table>

<script>
function abrirModal() {
    document.getElementById('modal-confirmar').style.display = 'block';
}
function cerrarModal() {
    document.getElementById('modal-confirmar').style.display = 'none';
}
window.onclick = function (event) {
    const modal = document.getElementById('modal-confirmar');
    if (event.target == modal) cerrarModal();
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

<!-- Modal Agregar/Editar -->
<div id="modal-confirmar" class="modalx">
    <div class="modalx-content">
<?php
$nombre = $telefono = $correo = $contrasena = $rol = "";
$estado = 1;
$seleccionadoA = $seleccionadoV = "";
$seleccionadoActivo = "selected";
$seleccionadoInactivo = "";
$tipoEdit = "Agregar nuevo";
$edtVer = "";

if (isset($_GET["edt"])) {
    $edt = $_GET["edt"];

    $sql = "BEGIN obtener_usuario_por_id(:id_usuario, :cursor); END;";
    $stid = oci_parse($conn, $sql);
    $cursor = oci_new_cursor($conn);

    oci_bind_by_name($stid, ":id_usuario", $edt);
    oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);

    oci_execute($stid);
    oci_execute($cursor);

    if ($row = oci_fetch_array($cursor, OCI_ASSOC)) {
        $nombre = htmlspecialchars($row["NOMBRE_USUARIO"]);
        $telefono = isset($row["TELEFONO"]) ? $row["TELEFONO"] : "";
        $correo = htmlspecialchars($row["CORREO"]);
        $rol = $row["ROL"];
        $estado = $row["ESTADO"];
    }

    oci_free_statement($stid);
    oci_free_statement($cursor);

    $seleccionadoV = ($rol == 0) ? "selected" : "";
    $seleccionadoA = ($rol == 1) ? "selected" : "";
    $seleccionadoActivo = ($estado == 1) ? "selected" : "";
    $seleccionadoInactivo = ($estado == 0) ? "selected" : "";
    $tipoEdit = "Editar";
    $edtVer = "edt=$edt";
}
?>

        <h3 class="modalx-titulo"><?php echo $tipoEdit; ?> usuario</h3>
        <form action="usuarios.php<?php echo "?op=$op&ta=$ta&" . $edtVer; ?>" method="POST">
            <label for="nombre_usuario">Nombre de Usuario:</label>
            <input type="text" id="nombre_usuario" class="form-control" name="nombre_usuario" value="<?php echo $nombre; ?>"><br>

            <label for="contrasena">Contraseña:</label>
            <input type="text" id="contrasena" class="form-control" name="contrasena" value=""><br>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" class="form-control" name="telefono" value="<?php echo $telefono; ?>"><br>

            <label for="correo">Correo electrónico:</label>
            <input type="email" id="correo" class="form-control" name="correo" value="<?php echo $correo; ?>"><br>

            <label for="rol">Rol:</label>
            <select id="rol" name="rol" class="form-control">
                <option <?php echo $seleccionadoV; ?> value="0">Vendedor</option>
                <option <?php echo $seleccionadoA; ?> value="1">Administrador</option>
            </select><br>

<?php if (!empty($edt)) { ?>
            <label for="estado">Estado:</label>
            <select id="estado" name="estado" class="form-control">
                <option value="1" <?php echo $seleccionadoActivo; ?>>Habilitado</option>
                <option value="0" <?php echo $seleccionadoInactivo; ?>>Deshabilitado</option>
            </select><br>
<?php } ?>

            <input type='hidden' name='submitted' value='TRUE' />
            <div class="modalx-footer">
                <a href='usuarios.php<?php echo "?op=$op&ta=$ta";?>' class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-success">Registrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal eliminar -->
<div id="modal-eliminar" class="modalx">
    <div class="modalx-content">
        <h3 class="modalx-titulo">Confirmar eliminación</h3>
        <p class="modalx-texto">¿Estás seguro de que deseas eliminar este usuario?</p>
        <div class="modalx-footer">
            <a href='usuarios.php<?php echo "?op=$op&ta=$ta";?>' class="btn-cancelar">Cancelar</a>
            <a href='usuarios.php<?php echo "?op=$op&ta=$ta&del2=$del" ; ?>' class="btn-confirmar">Eliminar</a>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
