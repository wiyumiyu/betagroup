<?php
include('../includes/header.html');
include('../includes/barralateral.php');
include('../includes/funciones.php');

$del = "";
$edt = "";
$ta = "";
$op = "";
$linkAceptar = "#";

if (isset($_GET['ta'])) $ta = $_GET['ta'];
if (isset($_GET['op'])) $op = $_GET['op'];
if (isset($_GET['edt'])) $edt = $_GET['edt'];
if (isset($_GET['del'])) $del = $_GET['del'];
$edtVer = ($edt != "") ? "edt=$edt" : "";

// Eliminar cliente
if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];
    $sql = "BEGIN eliminar_cliente(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $del2);
    if (oci_execute($stmt)) {
        echo "<script>window.location.href = 'clientes.php?op=$op&ta=$ta';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    }
    oci_free_statement($stmt);
}

// Insertar o actualizar cliente
if (isset($_POST['submitted'])) {
    $nombre = trim($_POST["nombre_cliente"]);
    $correo = trim($_POST["correo"]);
    $tipo = trim($_POST["id_tipo_clinica"]);

    if ($edt != "") {
        $id = $edt;
        $sql = "BEGIN actualizar_cliente(:id, :nombre, :correo, :tipo); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $id);
    } else {
        $sql = "BEGIN insertar_cliente(:nombre, :correo, :tipo); END;";
        $stmt = oci_parse($conn, $sql);
        // Necesitamos obtener el ID del último cliente insertado para los teléfonos
        $get_id_sql = "SELECT MAX(ID_CLIENTE) AS ID_CLIENTE FROM CLIENTE";
        $stmt_get = oci_parse($conn, $get_id_sql);
        oci_execute($stmt_get);
        $row = oci_fetch_assoc($stmt_get);
        $id = $row['ID_CLIENTE'] + 1; // Asumimos autoincremento
        oci_free_statement($stmt_get);
    }

    oci_bind_by_name($stmt, ":nombre", $nombre);
    oci_bind_by_name($stmt, ":correo", $correo);
    oci_bind_by_name($stmt, ":tipo", $tipo);
    oci_execute($stmt);
    oci_free_statement($stmt);

    // Insertar / actualizar teléfonos
    $telefonos_actuales = [];
    $ids_recibidos = [];

    if ($edt != "") {
        $stmt_get = oci_parse($conn, "BEGIN OBTENER_ID_TELEFONOS_CLIENTE(:id, :cursor); END;");
        $cursor = oci_new_cursor($conn);
        oci_bind_by_name($stmt_get, ":id", $id);
        oci_bind_by_name($stmt_get, ":cursor", $cursor, -1, OCI_B_CURSOR);
        oci_execute($stmt_get);
        oci_execute($cursor);

        while ($r = oci_fetch_assoc($cursor)) {
            $telefonos_actuales[] = $r['ID_TELEFONO'];
        }

        oci_free_statement($stmt_get);
        oci_free_statement($cursor);
    }

    for ($i = 0; $i < count($_POST['telefonos']); $i++) {
        $telefono = trim($_POST['telefonos'][$i]);
        $id_tel = $_POST['id_telefonos'][$i];

        if ($telefono == "") continue;

        if ($id_tel == "") {
            $sql_tel = "BEGIN insertar_telefono_cliente(:id, :tel); END;";
            $stmt_tel = oci_parse($conn, $sql_tel);
            oci_bind_by_name($stmt_tel, ":id", $id);
            oci_bind_by_name($stmt_tel, ":tel", $telefono);
            oci_execute($stmt_tel);
            oci_free_statement($stmt_tel);
        } else {
            $sql_upd = "BEGIN actualizar_telefono_cliente(:id_tel, :tel); END;";
            $stmt_upd = oci_parse($conn, $sql_upd);
            oci_bind_by_name($stmt_upd, ":id_tel", $id_tel);
            oci_bind_by_name($stmt_upd, ":tel", $telefono);
            oci_execute($stmt_upd);
            oci_free_statement($stmt_upd);
            $ids_recibidos[] = $id_tel;
        }
    }

    if ($edt != "") {
        $a_eliminar = array_diff($telefonos_actuales, $ids_recibidos);
        foreach ($a_eliminar as $id_eliminar) {
            $sql_del = "BEGIN eliminar_telefono_cliente(:id_tel); END;";
            $stmt_del = oci_parse($conn, $sql_del);
            oci_bind_by_name($stmt_del, ":id_tel", $id_eliminar);
            oci_execute($stmt_del);
            oci_free_statement($stmt_del);
        }
    }

    echo "<script>window.location.href = 'clientes.php?op=$op&ta=$ta';</script>";
}
?>

<?php include("tabs.php"); ?>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2>Lista de Clientes</h2>
    <button onclick="abrirModal()" class="btn btn-success">Nuevo Cliente</button>
</div>
<br>

<table class="table table-bordered table-striped datatable" id="table-2">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Tipo de Clínica</th>
            <th>Teléfonos</th>
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
    $id = $row['ID_CLIENTE'];

    // Obtener teléfonos
    $telefonos = [];
    $stmt_tel = oci_parse($conn, "BEGIN OBTENER_TELEFONOS_CLIENTE(:id, :cur); END;");
    $cur = oci_new_cursor($conn);
    oci_bind_by_name($stmt_tel, ":id", $id);
    oci_bind_by_name($stmt_tel, ":cur", $cur, -1, OCI_B_CURSOR);
    oci_execute($stmt_tel);
    oci_execute($cur);
    while ($tel = oci_fetch_assoc($cur)) {
        $telefonos[] = $tel['TELEFONO'];
    }
    oci_free_statement($stmt_tel);
    oci_free_statement($cur);

    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['NOMBRE_CLIENTE']) . "</td>";
    echo "<td>" . htmlspecialchars($row['CORREO']) . "</td>";
    echo "<td>" . htmlspecialchars($row['TIPO_CLINICA']) . "</td>";
    echo "<td>" . implode("<br>", $telefonos) . "</td>";
    echo "<td>
            <a href='clientes.php?op=$op&ta=$ta&edt=$id' class='btn btn-default'><i class='entypo-pencil'></i></a>
            <a href='clientes.php?op=$op&ta=$ta&del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
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
$telefonos_editar = [];

if ($edt != "") {
    $sql = "BEGIN OBTENER_CLIENTE(:id, :nom, :cor, :tip); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $edt);
    oci_bind_by_name($stmt, ":nom", $nombre, 100);
    oci_bind_by_name($stmt, ":cor", $correo, 100);
    oci_bind_by_name($stmt, ":tip", $tipo);
    oci_execute($stmt);
    oci_free_statement($stmt);

    $stmt_tel = oci_parse($conn, "BEGIN OBTENER_TELEFONOS_CLIENTE(:id, :cur); END;");
    $cur = oci_new_cursor($conn);
    oci_bind_by_name($stmt_tel, ":id", $edt);
    oci_bind_by_name($stmt_tel, ":cur", $cur, -1, OCI_B_CURSOR);
    oci_execute($stmt_tel);
    oci_execute($cur);
    while ($row = oci_fetch_assoc($cur)) {
        $telefonos_editar[] = $row;
    }
    oci_free_statement($stmt_tel);
    oci_free_statement($cur);
}
?>
    <h3 class="modalx-titulo"><?php echo ($edt != "") ? "Editar" : "Agregar nuevo"; ?> Cliente</h3>
    <form method="POST" action="clientes.php?op=<?php echo $op; ?>&ta=<?php echo $ta; ?>&<?php echo $edtVer; ?>">
        <label>Nombre:</label>
        <input type="text" class="form-control" name="nombre_cliente" value="<?php echo $nombre; ?>" required><br>

        <label>Correo:</label>
        <input type="email" class="form-control" name="correo" value="<?php echo $correo; ?>" required><br>

        <label>Tipo de Clínica:</label>
        <select class="form-control" name="id_tipo_clinica" required>
            <?php
            $sql = "SELECT ID_TIPO_CLINICA, DESCRIPCION FROM TIPO_CLINICA";
            $stmt = oci_parse($conn, $sql);
            oci_execute($stmt);
            while ($r = oci_fetch_assoc($stmt)) {
                $sel = ($tipo == $r['ID_TIPO_CLINICA']) ? "selected" : "";
                echo "<option value='{$r['ID_TIPO_CLINICA']}' $sel>" . htmlspecialchars($r['DESCRIPCION']) . "</option>";
            }
            oci_free_statement($stmt);
            ?>
        </select><br>

        <label>Teléfonos:</label>
        <div id="telefonos-container">
            <?php foreach ($telefonos_editar as $tel) { ?>
                <div class="input-group mb-2">
                    <input type="hidden" name="id_telefonos[]" value="<?php echo $tel['ID_TELEFONO']; ?>">
                    <input type="text" name="telefonos[]" class="form-control" value="<?php echo htmlspecialchars($tel['TELEFONO']); ?>">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-danger" onclick="eliminarTelefono(this)">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </span>
                </div>
            <?php } ?>
            <div class="input-group mb-2">
                <input type="hidden" name="id_telefonos[]" value="">
                <input type="text" name="telefonos[]" class="form-control" placeholder="Teléfono adicional">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-success" onclick="agregarTelefono()">
                        <i class="bi bi-plus-circle"></i>
                    </button>
                </span>
            </div>
        </div>

        <input type="hidden" name="submitted" value="TRUE" />
        <div class="modalx-footer">
            <a href="clientes.php?op=<?php echo $op; ?>&ta=<?php echo $ta; ?>" class="btn-cancelar">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar</button>
        </div>
    </form>
</div>
</div>

<!-- MODAL ELIMINAR -->
<div id="modal-eliminar" class="modalx">
    <div class="modalx-content">
        <h3 class="modalx-titulo">¿Eliminar cliente?</h3>
        <p class="modalx-texto">Esta acción es permanente.</p>
        <div class="modalx-footer">
            <a href="clientes.php?op=<?php echo $op; ?>&ta=<?php echo $ta; ?>" class="btn-cancelar">Cancelar</a>
            <a href="clientes.php?op=<?php echo $op; ?>&ta=<?php echo $ta; ?>&del2=<?php echo $del; ?>" class="btn-confirmar">Eliminar</a>
        </div>
    </div>
</div>

<script>
    function abrirModal() {
        document.getElementById('modal-confirmar').style.display = 'block';
    }

    function agregarTelefono() {
        const container = document.getElementById('telefonos-container');
        const grupos = container.querySelectorAll('.input-group');
        grupos.forEach(grupo => {
            const btn = grupo.querySelector('button');
            btn.className = 'btn btn-danger';
            btn.innerHTML = '<i class="bi bi-x-circle"></i>';
            btn.setAttribute('onclick', 'eliminarTelefono(this)');
        });

        const nuevo = document.createElement('div');
        nuevo.className = 'input-group mb-2';
        nuevo.innerHTML = `
            <input type="hidden" name="id_telefonos[]" value="">
            <input type="text" name="telefonos[]" class="form-control" placeholder="Teléfono adicional">
            <span class="input-group-btn">
                <button type="button" class="btn btn-success" onclick="agregarTelefono()">
                    <i class="bi bi-plus-circle"></i>
                </button>
            </span>
        `;
        container.appendChild(nuevo);
    }

    function eliminarTelefono(btn) {
        btn.closest('.input-group').remove();
    }

    window.onload = function () {
        if ('<?php echo $edt; ?>' !== "") document.getElementById('modal-confirmar').style.display = 'block';
        if ('<?php echo $del; ?>' !== "") document.getElementById('modal-eliminar').style.display = 'block';
    };
</script>

<?php include("../includes/footer.php"); ?>
<?php oci_close($conn); ?>
