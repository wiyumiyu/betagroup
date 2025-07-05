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

// Eliminar tipo de clínica si viene ?del2
if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];
    $sql = "BEGIN eliminar_tipo_clinica(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $del2);

    if (oci_execute($stmt)) {
        echo "<script>window.location.href = 'tipoClinica.php';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error al eliminar: " . $e['message'];
    }

    oci_free_statement($stmt);
}

// Insertar o actualizar tipo de clínica
if (isset($_POST['submitted'])) {
    $descripcion = trim($_POST["descripcion"]);

    if (isset($_GET['edt'])) {
        $id = $_GET['edt'];
        $sql = "BEGIN actualizar_tipo_clinica(:id, :desc); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $id);
    } else {
        $sql = "BEGIN insertar_tipo_clinica(:descripcion); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":descripcion", $descripcion);


    }

    oci_bind_by_name($stmt, ":desc", $descripcion);

    if (oci_execute($stmt)) {
        echo "<script>window.location='tipoClinica.php';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    }

    oci_free_statement($stmt);
    oci_close($conn);
}
?>

<hr>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Tipos de Clínica</h2>
    <button onclick="abrirModal()" class="btn btn-success">Nuevo Tipo</button>
</div>
<br>

<table class="table table-bordered table-striped datatable" id="table-2">
    <thead>
        <tr>
            <th>ID</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
<?php
$sql = "BEGIN listar_tipos_clinica(:cursor); END;";
$stid = oci_parse($conn, $sql);
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
oci_execute($stid);
oci_execute($cursor);

while ($row = oci_fetch_assoc($cursor)) {
    $id = $row['ID_TIPO_CLINICA'];
    echo "<tr>";
    echo "<td>" . htmlspecialchars($id) . "</td>";
    echo "<td>" . htmlspecialchars($row['DESCRIPCION']) . "</td>";
    echo "<td>
            <a href='tipoClinica.php?edt=$id' class='btn btn-default'><i class='entypo-pencil'></i></a>
            <a href='tipoClinica.php?del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
          </td>";
    echo "</tr>";
}
oci_free_statement($stid);
oci_free_statement($cursor);
?>
    </tbody>
</table>

<!-- MODAL TIPO DE CLÍNICA -->
<div id="modal-confirmar" class="modalx">
    <div class="modalx-content">
<?php
$descripcion = "";
$tipoEdit = "Agregar un nuevo ";
$edtVer = "";

if (isset($_GET["edt"])) {
    $id = $_GET["edt"];
    $sql = "SELECT DESCRIPCION FROM TIPO_CLINICA WHERE ID_TIPO_CLINICA = :id";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":id", $id);
    oci_execute($stid);
    if ($row = oci_fetch_array($stid, OCI_ASSOC)) {
        $descripcion = htmlspecialchars($row["DESCRIPCION"]);
    }
    oci_free_statement($stid);
    $tipoEdit = "Editar";
    $edtVer = "?edt=$id";
}
?>

        <h3 class='modalx-titulo'><?php echo $tipoEdit; ?> Tipo de Clínica</h3>
        <form action="tipoClinica.php<?php echo $edtVer; ?>" method="POST">
            <label for="descripcion">Descripción:</label>
            <input type="text" id="descripcion" class="form-control" name="descripcion" value="<?php echo $descripcion; ?>" required>
            <br>
            <input type='hidden' name='submitted' value='TRUE' />
            <div class="modalx-footer">
                <a href='tipoClinica.php' class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN PARA ELIMINAR -->
<div id="modal-eliminar" class="modalx">
    <div class="modalx-content">
        <h3 class="modalx-titulo">Confirmar eliminación</h3>
        <p class="modalx-texto">¿Estás segura de que deseas eliminar este tipo de clínica?</p>
        <div class="modalx-footer">
            <a href='tipoClinica.php' class="btn-cancelar">Cancelar</a>
            <a href='tipoClinica.php?del2=<?php echo $del; ?>' class="btn-confirmar">Eliminar</a>
        </div>
    </div>
</div>

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

<?php include("../includes/footer.php"); ?>