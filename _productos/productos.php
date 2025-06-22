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

    $sql = "BEGIN eliminar_producto(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $del2);

    if (oci_execute($stmt)) {
        // Si se eliminó correctamente, recargamos la página
        echo "<script>window.location.href = 'productos.php';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error al eliminar el producto: " . $e['message'];
    }

    oci_free_statement($stmt);
}


if (isset($_POST['submitted'])) {
    $nombre_producto = trim($_POST["nombre_producto"]);
    $precio = trim($_POST["precio"]);
    $id_proveedor = trim($_POST["id_proveedor"]);
    $id_categoria = trim($_POST["id_categoria"]);
    
    if (isset($_GET['edt'])) {
        $id = $_GET['edt'];
        $sql = "BEGIN actualizar_producto(:id, :nombre_producto, :precio, :id_proveedor, :id_categoria); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $id);
    } else {
        $sql = "BEGIN insertar_producto(:nombre_producto, :precio, :id_proveedor, :id_categoria); END;";
        $stmt = oci_parse($conn, $sql);
    }
    
    oci_bind_by_name($stmt, ":nombre_producto", $nombre_producto);
    oci_bind_by_name($stmt, ":precio", $precio);
    oci_bind_by_name($stmt, ":id_proveedor", $id_proveedor);
    oci_bind_by_name($stmt, ":id_categoria", $id_categoria);

    if (oci_execute($stmt)) {
        echo "<script>window.location.href='productos.php?op=3&pc=1';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    }

    oci_free_statement($stmt);
    oci_close($conn);
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
<hr>
<ol class="breadcrumb bc-3">
  <li><a href="../_dashboard/escritorio.php"><i class="entypo-home"></i>Home</a></li>
  <li class="active"><strong>Lista de Productos</strong></li>
</ol>
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
$sql = "BEGIN LISTAR_PRODUCTOS(:cursor); END;";
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
            <a href='productos.php?edt=$id' class='btn btn-default'><i class='entypo-pencil'></i></a>
            <a href='productos.php?del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
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
$nombre_producto = $precio = $id_proveedor = $id_categoria = "";
$tipoEdit = "Agregar nuevo";
$edtVer = "";

if (isset($_GET["edt"])) {
    $id = $_GET["edt"];
    $sql = "SELECT NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA FROM PRODUCTO WHERE ID_PRODUCTO = :id";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":id", $id);
    oci_execute($stid);
    if ($row = oci_fetch_array($stid, OCI_ASSOC)) {
        $nombre_producto = htmlspecialchars($row["NOMBRE_PRODUCTO"]);
        $precio = $row["PRECIO"];
        $id_proveedor = htmlspecialchars($row["ID_PROVEEDOR"]);
        $id_categoria = htmlspecialchars($row["ID_CATEGORIA"]);
    }
    oci_free_statement($stid);
    $tipoEdit = "Editar";
    $edtVer = "?edt=$id";
}
echo "<h3 class='modalx-titulo'>$tipoEdit producto</h3>";
?>
            <form action="productos.php<?php echo $edtVer; ?>" method="POST">
            <label for="nombre_producto">Nombre:</label>
            <input type="text" id="nombre_producto" class="form-control" name="nombre_producto" value="<?php echo $nombre_producto; ?>" required>
            <br>
            <label for="precio">Precio:</label>
            <input type="number" id="precio" class="form-control" name="precio" value="<?php echo $precio; ?>" required>
            <br>
            <label for="$id_proveedor"> Proveedor:</label>
            <?php cargarSelect($conn, 'LISTAR_PROVEEDORES', 'ID_PROVEEDOR', 'NOMBRE_PROVEEDOR', 'id_proveedor', $id_proveedor); ?>
            <label for="$id_categoria"> Categoría:</label>
            <?php cargarSelect($conn, 'LISTAR_CATEGORIAS', 'ID_CATEGORIA', 'NOMBRE_CATEGORIA', 'id_categoria', $id_categoria); ?>
            </select>
            <br>
            <input type='hidden' name='submitted' value='TRUE' />
            <div class="modalx-footer">
                <a href='productos.php' class="btn-cancelar">Cancelar</a>
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
            <a href='productos.php' class="btn-cancelar">Cancelar</a>
            <a href='productos.php?del2=<?php echo $del; ?>' class="btn-confirmar">Eliminar</a>
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
