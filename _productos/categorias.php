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

$ta = "";
$op = "";

if (isset($_GET['ta'])) {
    $ta = $_GET['ta'];
}
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}

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

    $sqlCheck = "BEGIN :result := FUNC_categoria_tiene_productos_num(:id_categoria); END;";
    $checkStmt = oci_parse($conn, $sqlCheck);
    oci_bind_by_name($checkStmt, ":id_categoria", $del2);
    oci_bind_by_name($checkStmt, ":result", $tieneProductos, 10); // ahora es NUMBER, no BOOLEAN
    oci_execute($checkStmt);
    oci_free_statement($checkStmt);

// Si el usuario NO tiene ventas, se puede continuar con la eliminación
    if (!$tieneProductos) {
        
        $stmt_contexto = llenarBitacora($_SESSION['id_usuario'], "BEGIN pkg_contexto_usuario.set_usuario(:id); END;", $conn);
        
        $sql = "BEGIN PROC_eliminar_categoria(:id); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $del2);

        if (oci_execute($stmt)) {
            // Si se eliminó correctamente, recargamos la página
            echo "<script>window.location.href = 'categorias.php?op=$op&ta=$ta';</script>";
        } else {
            $e = oci_error($stmt);
            echo "Error al eliminar el categoria: " . $e['message'];
        }
        if (isset($stmt_contexto))
            oci_free_statement($stmt_contexto);
        oci_free_statement($stmt);
    } else {
        echo "<script>alert('No se puede eliminar el usuario porque tiene ventas registradas.');</script>";
        
    }
}


if (isset($_POST['submitted'])) {

    
   $stmt_contexto = llenarBitacora($_SESSION['id_usuario'], "BEGIN pkg_contexto_usuario.set_usuario(:id); END;", $conn);

    $nombre_categoria = trim($_POST["nombre_categoria"]);

    $sql = "BEGIN PROC_insertar_categoria(:nombre_categoria); END;";
    $stmt = oci_parse($conn, $sql);

    oci_bind_by_name($stmt, ":nombre_categoria", $nombre_categoria);

    if (oci_execute($stmt)) {
        echo "<script>window.location.href='categorias.php?op=$op&ta=$ta';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    }
    
    if (isset($stmt_contexto))
        oci_free_statement($stmt_contexto);
    oci_free_statement($stmt);
    oci_close($conn);
}
?>

<!-- ------------------ INTERFAZ HTML ---------------------- -->
<hr>
<div style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Lista de Categorías</h2>
    <button onclick="abrirModal()" class="btn btn-success"> Nueva Categoría</button>
</div>
<br>
<table class="table table-bordered table-striped datatable" id="table-2">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Acciones</th>

        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "BEGIN PROC_LISTAR_CATEGORIAS(:cursor); END;";
        $stid = oci_parse($conn, $sql);
        $cursor = oci_new_cursor($conn);
        oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
        oci_execute($stid);
        oci_execute($cursor);

        while ($row = oci_fetch_assoc($cursor)) {
            $id = $row['ID_CATEGORIA'];
            echo "<tr>";
            echo "<td>" . htmlspecialchars($id) . "</td>";
            echo "<td>" . htmlspecialchars($row['NOMBRE_CATEGORIA']) . "</td>";
            echo "<td>
            <a href='categorias.php?op=$op&ta=$ta&del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
            </td>";
            echo "</tr>";
        }
        oci_free_statement($stid);
        oci_free_statement($cursor);
        ?>
    </tbody>
</table>

<!-- MODAL: Agregar-->

<div id="modal-confirmar" class="modalx">
    <div class="modalx-content">
        <?php
        $nombre_categoria = "";
        $tipoEdit = "Agregar nuevo";
        $edtVer = "";

        echo "<h3 class='modalx-titulo'>$tipoEdit categoria</h3>";
        ?>
        <form action="categorias.php<?php echo "?op=$op&ta=$ta"; ?>" method="POST">
            <label for="nombre_categoria">Nombre:</label>
            <input type="text" id="nombre_categoria" class="form-control" name="nombre_categoria" value="<?php echo $nombre_categoria; ?>" required>
            <br>
            <input type='hidden' name='submitted' value='TRUE' />
            <div class="modalx-footer">
                <a href='categorias.php<?php echo "?op=$op&ta=$ta"; ?>' class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL DE CONFIRMACIÓN PARA ELIMINAR -->
<div id="modal-eliminar" class="modalx">
    <div class="modalx-content">
        <h3 class="modalx-titulo">Confirmar eliminación</h3>
        <p class="modalx-texto">¿Estás seguro de que deseas eliminar este categoria?</p>
        <div class="modalx-footer">
            <a href='categorias.php<?php echo "?op=$op&ta=$ta"; ?>' class="btn-cancelar">Cancelar</a>
            <a href='categorias.php<?php echo "?op=$op&ta=$ta&del2=$del"; ?>' class="btn-confirmar">Eliminar</a>
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

