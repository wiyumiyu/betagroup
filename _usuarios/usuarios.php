<?php
// ------------------ INICIO DEL CÓDIGO ----------------------
// Estos archivos son como piezas de LEGO que se repiten en todas las páginas
// Incluyen el encabezado del sitio, el menú lateral y funciones útiles
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');

// Variables vacías que usamos para saber si se quiere editar o eliminar algo
$ii = "123"; // de ejemplo, no se usa
$rr1 = "A";  // de ejemplo, no se usa
$linkAceptar = "#";

$del = "";
$edt = "";
$edtVer = "";

// Si en la URL hay un valor ?edt=, lo guardamos en la variable $edt para editar ese usuario
if (isset($_GET['edt'])) {
    $edt = $_GET['edt'];
}

// Si en la URL hay un valor ?del=, lo guardamos en $del para confirmar eliminación
if (isset($_GET['del'])) {
    $del = $_GET['del'];
}

// Si se confirma la eliminación con ?del2=, eliminamos el usuario de la base de datos
if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];

    $sql = "BEGIN eliminar_usuario(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $del2);

    if (oci_execute($stmt)) {
        // Si se eliminó correctamente, recargamos la página
        echo "<script>window.location.href = 'usuarios.php';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error al eliminar el usuario: " . $e['message'];
    }

    oci_free_statement($stmt);
}

// Si el formulario fue enviado (para agregar o actualizar un usuario)
if (isset($_POST['submitted'])) {
    // Capturamos los datos del formulario
    $nombre = trim($_POST["nombre_usuario"]);
    $contrasena = trim($_POST["contrasena"]);
    $telefono = trim($_POST["telefono"]);
    $correo = trim($_POST["correo"]);
    $rol = trim($_POST["rol"]);

    // Si se escribió una contraseña nueva, la agregamos al SQL con una función HASH
    if ($contrasena != "") {
        $sql_part = ", CONTRASENA = HASH_PASSWORD(:contrasena)";
    } else {
        $sql_part = "";
    }

    // Si vino un 'id' oculto, significa que estamos actualizando un usuario existente
    if (isset($_GET['edt'])) {
        $id = $_GET['edt'];

        if ($contrasena != "") {
            $sql = "BEGIN actualizar_usuario(:id, :nombre, :contrasena, :telefono, :correo, :rol); END;";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":contrasena", $contrasena);
        }else{
            $sql = "BEGIN actualizar_usuario_sc(:id, :nombre,  :telefono, :correo, :rol); END;";
            $stmt = oci_parse($conn, $sql);
            

        }
        oci_bind_by_name($stmt, ":id", $id);
    } else {
        // Si no vino ID, estamos insertando un nuevo usuario
        $sql = "BEGIN insertar_usuario(:nombre, :contrasena, :telefono, :correo, :rol); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":contrasena", $contrasena);
    }


    // Asignamos los valores a los campos del SQL
    oci_bind_by_name($stmt, ":nombre", $nombre);
    oci_bind_by_name($stmt, ":telefono", $telefono);
    oci_bind_by_name($stmt, ":correo", $correo);
    oci_bind_by_name($stmt, ":rol", $rol);

    // Ejecutamos la acción y redirigimos
    if (oci_execute($stmt)) {
        echo "<script>window.location='usuarios.php';</script>";
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
<!-- Navegación superior -->
<ol class="breadcrumb bc-3">
    <li><a href="..\_dashboard\escritorio.php"><i class="entypo-home"></i>Home</a></li>
    <li class="active"><strong>Lista de Usuarios</strong></li>
</ol>

<!-- Título y botón para nuevo usuario -->
<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Lista de Usuarios</h2>
    <button onclick="abrirModal()" class="btn btn-success">Nuevo Usuario</button>
</div>
<br>

<!-- Tabla donde se muestran todos los usuarios registrados -->
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
// Se llama al procedimiento almacenado para listar los usuarios
$sql = "BEGIN LISTAR_USUARIOS(:cursor); END;";
$stid = oci_parse($conn, $sql);
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
oci_execute($stid);
oci_execute($cursor);

// Recorremos cada usuario y lo mostramos en una fila de la tabla
while ($row = oci_fetch_assoc($cursor)) {
    $id = $row['ID_USUARIO'];
    echo "<tr>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['NOMBRE_USUARIO']) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['TELEFONO'] ?? '') . "</td>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['CORREO']) . "</td>";
    $rolTexto = ($row['ROL'] == 1) ? "Administrador" : "Vendedor";
    echo "<td style='color: #4B4B4B;'>$rolTexto</td>";
    echo "<td style='color: #4B4B4B;'>" . date("d-m-Y", strtotime($row['FECHA_REGISTRO'])) . "</td>";

    // Botones de editar y eliminar
    echo "<td>
                    <a href='usuarios.php?edt=$id' class='btn btn-default'><i class='entypo-pencil'></i></a>
                    <a href='usuarios.php?del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
                  </td>";
    echo "</tr>";
}

oci_free_statement($stid);
oci_free_statement($cursor);
?>
    </tbody>
</table>

<!-- ------------------ SCRIPT PARA MODALES ---------------------- -->
<script>
    // Funciones para mostrar u ocultar el modal de agregar/editar
    function abrirModal() {
        document.getElementById('modal-confirmar').style.display = 'block';
    }

    function cerrarModal() {
        document.getElementById('modal-confirmar').style.display = 'none';
    }

    // Si se hace clic fuera del modal, se cierra
    window.onclick = function (event) {
        const modal = document.getElementById('modal-confirmar');
        if (event.target == modal)
            cerrarModal();
    };

    // Si hay un valor 'edt' en PHP, se abre el modal de edición automáticamente
    $(window).on('load', function () {
        var edt = '<?php echo $edt; ?>';
        if (edt != "") {
            document.getElementById('modal-confirmar').style.display = 'block';
        }
    });

    // Si hay un valor 'del' en PHP, se abre el modal de confirmación para eliminar
    $(window).on('load', function () {
        var del = '<?php echo $del; ?>';
        if (del != "") {
            document.getElementById('modal-eliminar').style.display = 'block';
        }
    });
</script>

<!-- ------------------ MODAL PARA FORMULARIO DE USUARIO ---------------------- -->
<div id="modal-confirmar" class="modalx">
    <div class="modalx-content">
<?php
// Variables para rellenar el formulario si se está editando
$nombre = $telefono = $correo = $contrasena = $rol = "";
$seleccionadoA = $seleccionadoV = "";
$edt = "";
$tipoEdit = "Agregar nuevo";

// Si se está editando, cargamos los datos del usuario
if (isset($_GET["edt"])) {
    $edt = $_GET["edt"];
    $sql = "SELECT NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL FROM USUARIO WHERE ID_USUARIO = :id";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":id", $edt);
    oci_execute($stid);
    if ($row = oci_fetch_array($stid, OCI_ASSOC)) {
        $nombre = htmlspecialchars($row["NOMBRE_USUARIO"]);
        $telefono = disset($row["TELEFONO"]) ? htmlspecialchars($row["TELEFONO"]) : "";
        $correo = htmlspecialchars($row["CORREO"]);
        $rol = $row["ROL"];
    }
    oci_free_statement($stid);
    $seleccionadoV = ($rol == 0) ? "selected" : "";
    $seleccionadoA = ($rol == 1) ? "selected" : "";
    $tipoEdit = "Editar";
    $edtVer = "?edt=$edt";
}

echo "<h3 class='modalx-titulo'>$tipoEdit usuario</h3>";
?>

        <!-- Formulario para agregar o editar -->
        <form action="usuarios.php<?php echo $edtVer; ?>" method="POST">
            <label for="nombre_usuario">Nombre de Usuario:</label>
            <input type="text" id="nombre_usuario" class="form-control" name="nombre_usuario" value="<?php echo $nombre; ?>">
            <br>
            <label for="contrasena">Contraseña:</label>
            <input type="text" id="contrasena" class="form-control" name="contrasena" value="<?php echo $contrasena; ?>">
            <br>
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" class="form-control" name="telefono" value="<?php echo $telefono; ?>">
            <br>
            <label for="correo">Correo electrónico:</label>
            <input type="email" id="correo" class="form-control" name="correo" value="<?php echo $correo; ?>">
            <br>
            <label for="rol">Rol:</label>
            <select id="rol" name="rol" class="form-control">
                <option <?php echo $seleccionadoV; ?> value="0">Vendedor</option>
                <option <?php echo $seleccionadoA; ?> value="1">Administrador</option>
            </select>
            <br>
            <!-- Campo oculto con el ID del usuario si estamos editando -->
<?php if (!empty($id)) echo "<input type='hidden' name='id' value='$id'>"; ?>
            <input type='hidden' name='submitted' value='TRUE' />
            <div class="modalx-footer">
                <a href='usuarios.php' class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-success">Registrar</button>
            </div>
        </form>
    </div>
</div>

<!-- ------------------ MODAL DE CONFIRMACIÓN PARA ELIMINAR ---------------------- -->
<div id="modal-eliminar" class="modalx">
    <div class="modalx-content">
        <h3 class="modalx-titulo">Confirmar eliminación</h3>
        <p class="modalx-texto">¿Estás seguro de que deseas eliminar este usuario?</p>
        <div class="modalx-footer">
            <a href='usuarios.php' class="btn-cancelar">Cancelar</a>
            <a href='usuarios.php?del2=<?php echo $del; ?>' class="btn-confirmar">Eliminar</a>
        </div>
    </div>
</div>

<!-- Pie de página -->
<?php include("../includes/footer.php"); ?>
