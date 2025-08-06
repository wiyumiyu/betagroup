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

    // Verificar si el producto está en alguna venta
    $sqlCheck = "BEGIN :result := FUNC_producto_tiene_ventas(:id_prod); END;";
    $stmtCheck = oci_parse($conn, $sqlCheck);
    oci_bind_by_name($stmtCheck, ":id_prod", $del2);
    oci_bind_by_name($stmtCheck, ":result", $tieneVentas, 10); // NUMBER result
    oci_execute($stmtCheck);
    oci_free_statement($stmtCheck);

    if ($tieneVentas == 0) {



        $stmt_contexto = llenarBitacora($_SESSION['id_usuario'], "BEGIN pkg_contexto_usuario.set_usuario(:id); END;", $conn);

        $sql = "BEGIN PROC_eliminar_producto(:id); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $del2);

        if (oci_execute($stmt)) {
            // Si se eliminó correctamente, recargamos la página
            echo "<script>window.location.href = 'productos.php?op=$op&ta=$ta';</script>";
        } else {
            $e = oci_error($stmt);
            echo "Error al eliminar el producto: " . $e['message'];
        }
        if (isset($stmt_contexto))
            oci_free_statement($stmt_contexto);
        oci_free_statement($stmt);
    } else {
        // El producto está en alguna venta, mostrar aviso y no eliminar
        echo "<script>alert('No se puede eliminar el producto porque está asociado a ventas.');</script>";
    }
}


if (isset($_POST['submitted'])) {
    $nombre_producto = trim($_POST["nombre_producto"]);
    $precio = trim($_POST["precio"]);
    $id_proveedor = trim($_POST["id_proveedor"]);
    $id_categoria = trim($_POST["id_categoria"]);

    $stmt_contexto = llenarBitacora($_SESSION['id_usuario'], "BEGIN pkg_contexto_usuario.set_usuario(:id); END;", $conn);

    if (isset($_GET['edt'])) {
        $id = $_GET['edt'];
        $sql = "BEGIN PROC_actualizar_producto(:id, :nombre_producto, :precio, :id_proveedor, :id_categoria); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $id);
    } else {
        $sql = "BEGIN PROC_insertar_producto(:nombre_producto, :precio, :id_proveedor, :id_categoria); END;";
        $stmt = oci_parse($conn, $sql);
    }

    oci_bind_by_name($stmt, ":nombre_producto", $nombre_producto);
    oci_bind_by_name($stmt, ":precio", $precio);
    oci_bind_by_name($stmt, ":id_proveedor", $id_proveedor);
    oci_bind_by_name($stmt, ":id_categoria", $id_categoria);

    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    }

    oci_free_statement($stmt);
    if (isset($stmt_contexto))
        oci_free_statement($stmt_contexto);
    oci_close($conn);

    echo "<script>window.location.href='productos.php?op=$op&ta=$ta';</script>";
}

// --------------------------- Funcion Cargar Select ----------------------------------

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

<?php include("tabs.php"); ?>


<div style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Lista de Productos</h2>
    <button onclick="abrirModal()" class="btn btn-success"> Nuevo Producto</button>
</div>
<br>
<table class="table table-bordered table-striped datatable" id="table-2">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Proveedor</th>
            <th>Categoria</th>
            <th>Fecha registro</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
<?php
$sql = "BEGIN PROC_LISTAR_PRODUCTOS(:cursor); END;";
$stid = oci_parse($conn, $sql);
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
oci_execute($stid);
oci_execute($cursor);

while ($row = oci_fetch_assoc($cursor)) {
    $id = $row['ID_PRODUCTO'];
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['NOMBRE_PRODUCTO']) . "</td>";
    echo "<td>₡" . number_format($row['PRECIO'], 2) . "</td>";
    echo "<td>" . htmlspecialchars($row['NOMBRE_PROVEEDOR']) . "</td>";
    echo "<td>" . htmlspecialchars($row['NOMBRE_CATEGORIA']) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . date("d-m-Y", strtotime($row['FECHA_REGISTRO'])) . "</td>";
    echo "<td>
            <a href='productos.php?op=$op&ta=$ta&edt=$id' class='btn btn-default'><i class='entypo-pencil'></i></a>
            <a href='productos.php?op=$op&ta=$ta&del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
            </td>";
    echo "</tr>";
}
oci_free_statement($stid);
oci_free_statement($cursor);
?>
    </tbody>
</table>

<?php ?>


<!-- MODAL: Agregar / Editar -->

<div id="modal-confirmar" class="modalx">
    <div class="modalx-content">
<?php
$nombre_producto = $precio = $id_proveedor = $id_categoria = "";
$tipoEdit = "Agregar nuevo";
$edtVer = "";

if (isset($_GET["edt"])) {
    $id = $_GET["edt"];
    $sql = "BEGIN PROC_OBTENER_PRODUCTO(:id, :cursor); END;";
    $stmt = oci_parse($conn, $sql);
    $cursor = oci_new_cursor($conn);

// Enlazar el ID y el cursor
    oci_bind_by_name($stmt, ":id", $id);
    oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);

// Ejecutar ambos
    oci_execute($stmt);
    oci_execute($cursor);

// Leer los datos del cursor
    if ($row = oci_fetch_assoc($cursor)) {
        $nombre_producto = htmlspecialchars($row["NOMBRE_PRODUCTO"]);
        $precio = $row["PRECIO"];
        $id_proveedor = $row["ID_PROVEEDOR"];
        $id_categoria = $row["ID_CATEGORIA"];
    } else {
        echo "Producto no encontrado.";
    }

// Cerrar
    oci_free_statement($stmt);
    oci_free_statement($cursor);
    //**************************************

    $tipoEdit = "Editar";
    $edtVer = "edt=$id";
}
echo "<h3 class='modalx-titulo'>$tipoEdit producto</h3>";
?>
        <form action="productos.php<?php echo "?op=$op&ta=$ta&" . $edtVer; ?>" method="POST">
            <label for="nombre_producto">Nombre:</label>
            <input type="text" id="nombre_producto" class="form-control" name="nombre_producto" value="<?php echo $nombre_producto; ?>" required>
            <br>
            <label for="precio">Precio:</label>
            <input type="number" id="precio" class="form-control" name="precio" value="<?php echo $precio; ?>" required>
            <br>
            <label for="$id_proveedor"> Proveedor:</label>
<?php cargarSelect($conn, 'PROC_LISTAR_PROVEEDORES', 'ID_PROVEEDOR', 'NOMBRE_PROVEEDOR', 'id_proveedor', $id_proveedor); ?>
            <label for="$id_categoria"> Categoría:</label>
<?php cargarSelect($conn, 'PROC_LISTAR_CATEGORIAS', 'ID_CATEGORIA', 'NOMBRE_CATEGORIA', 'id_categoria', $id_categoria); ?>
            </select>
            <br>
            <input type='hidden' name='submitted' value='TRUE' />
            <div class="modalx-footer">
                <a href='productos.php<?php echo "?op=$op&ta=$ta"; ?>' class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL DE CONFIRMACIÓN PARA ELIMINAR -->
<div id="modal-eliminar" class="modalx">
    <div class="modalx-content">
        <h3 class="modalx-titulo">Confirmar eliminación</h3>
        <p class="modalx-texto">¿Estás seguro de que deseas eliminar este producto?</p>
        <div class="modalx-footer">
            <a href='productos.php<?php echo "?op=$op&ta=$ta"; ?>' class="btn-cancelar">Cancelar</a>
            <a href='productos.php<?php echo "?op=$op&ta=$ta&del2=$del"; ?>' class="btn-confirmar">Eliminar</a>
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






//    var triggerTabList = [].slice.call(document.querySelectorAll('#myTab a'))
//    triggerTabList.forEach(function (triggerEl) {
//        var tabTrigger = new bootstrap.Tab(triggerEl)
//
//        triggerEl.addEventListener('click', function (event) {
//            event.preventDefault()
//            tabTrigger.show()
//        })
//    })

</script>

<?php include("../includes/footer.php"); ?>