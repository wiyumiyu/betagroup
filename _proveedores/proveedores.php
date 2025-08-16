<?php
include('../includes/header.html');
include('../includes/barralateral.php');
include('../includes/funciones.php');

$ii = "123";
$rr1 = "A";
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

if (isset($_GET['edt'])) {
    $edt = $_GET['edt'];
}

if (isset($_GET['del'])) {
    $del = $_GET['del'];
}

if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];

    $sqlCheck = "BEGIN :result := FUNC_proveedor_tiene_ventas(:id_proveedor); END;";
    $checkStmt = oci_parse($conn, $sqlCheck);
    oci_bind_by_name($checkStmt, ":id_proveedor", $del2);
    oci_bind_by_name($checkStmt, ":result", $tieneVentas, 10); // NUMBER
    oci_execute($checkStmt);
    oci_free_statement($checkStmt);

    if ($tieneVentas == 0) {


        $stmt_contexto = llenarBitacora($_SESSION['id_usuario'], "BEGIN pkg_contexto_usuario.set_usuario(:id); END;", $conn);
        $sql = "BEGIN PROC_eliminar_proveedor(:id); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $del2);

        if (oci_execute($stmt)) {
            echo "<script>window.location.href = 'proveedores.php?op=$op&ta=$ta';</script>";
        } else {
            $e = oci_error($stmt);
            echo "Error al eliminar el proveedor: " . $e['message'];
        }

        oci_free_statement($stmt);
    } else {
        echo "<script>alert('No se puede eliminar el proveedor porque tiene productos vendidos en registros de ventas.');</script>";
    }
}

if (isset($_POST['submitted'])) {
    $stmt_contexto = llenarBitacora($_SESSION['id_usuario'], "BEGIN pkg_contexto_usuario.set_usuario(:id); END;", $conn);
    $nombre_proveedor = trim($_POST["nombre_proveedor"]);
    $correo = trim($_POST["correo"]);
    $direccion = trim($_POST["direccion"]);
    $estado = $_POST["estado"];

    if (isset($_GET['edt'])) {
        // Modo edición
        $id = $_GET['edt'];
        $sql = "BEGIN PROC_actualizar_proveedor(:id, :nombre, :correo, :direccion, :estado); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $id);
    } else {
        // Modo inserción con retorno del ID generado
        $sql = "BEGIN PROC_insertar_proveedor(:nombre, :correo, :direccion, :estado, :id_out); END;";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id_out", $id, 20); // ← Captura el ID generado
    }

    oci_bind_by_name($stmt, ":nombre", $nombre_proveedor);
    oci_bind_by_name($stmt, ":correo", $correo);
    oci_bind_by_name($stmt, ":direccion", $direccion);
    oci_bind_by_name($stmt, ":estado", $estado);

    if (oci_execute($stmt)) {
        // Insertar o actualizar teléfonos
        $telefonos_actuales = [];
        $ids_recibidos = [];

        if (isset($_GET['edt'])) {
            // Si es edición, obtener los teléfonos actuales
            $stmt_get = oci_parse($conn, "BEGIN PROC_OBTENER_ID_TELEFONOS(:id, :cursor); END;");
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

        // Procesar los teléfonos del formulario
        for ($i = 0; $i < count($_POST['telefonos']); $i++) {
            $telefono = trim($_POST['telefonos'][$i]);
            $id_tel = $_POST['id_telefonos'][$i];

            if ($telefono == "")
                continue;

            if ($id_tel == "") {
                // Insertar nuevo teléfono
                $sql_tel = "BEGIN PROC_insertar_telefono_proveedor(:id, :tel); END;";
                $stmt_tel = oci_parse($conn, $sql_tel);
                oci_bind_by_name($stmt_tel, ":id", $id); // ← Funciona en inserción y edición
                oci_bind_by_name($stmt_tel, ":tel", $telefono);
                oci_execute($stmt_tel);
                oci_free_statement($stmt_tel);
            } else {
                // Actualizar teléfono existente
                $sql_upd = "BEGIN PROC_actualizar_telefono_proveedor(:id_tel, :tel); END;";
                $stmt_upd = oci_parse($conn, $sql_upd);
                oci_bind_by_name($stmt_upd, ":id_tel", $id_tel);
                oci_bind_by_name($stmt_upd, ":tel", $telefono);
                oci_execute($stmt_upd);
                oci_free_statement($stmt_upd);

                $ids_recibidos[] = $id_tel;
            }
        }

        // Eliminar los teléfonos que ya no están (solo en edición)
        if (isset($_GET['edt'])) {
            $a_eliminar = array_diff($telefonos_actuales, $ids_recibidos);
            foreach ($a_eliminar as $id_eliminar) {
                $sql_del = "BEGIN PROC_eliminar_telefono(:id_tel); END;";
                $stmt_del = oci_parse($conn, $sql_del);
                oci_bind_by_name($stmt_del, ":id_tel", $id_eliminar);
                oci_execute($stmt_del);
                oci_free_statement($stmt_del);
            }
        }

        echo "<script>window.location.href='proveedores.php?op=$op&ta=$ta';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'];
    }
    oci_free_statement($stmt);
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
<?php include("tabs.php"); ?>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2>Lista de Proveedores</h2>
    <button onclick="abrirModal()" class="btn btn-success">Nuevo Proveedor</button>
</div>

<br>

<table class="table table-bordered table-striped datatable" id="table-2">
    <thead>
        <tr>
            <th><strong style="color: #999999;">Nombre</strong></th>
            <th><strong style="color: #999999;">Correo</strong></th>
            <th><strong style="color: #999999;">Dirección</strong></th>
            <th><strong style="color: #999999;">Teléfonos</strong></th>
            <th><strong style="color: #999999;">Fecha registro</strong></th>
            <th><strong style="color: #999999;">Acciones</strong></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "BEGIN PROC_LISTAR_PROVEEDORES(:cursor); END;";
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
            echo "<td>" . nl2br(htmlspecialchars($row['TELEFONOS'] ?? '')) . "</td>";
            echo "<td>" . date("d-m-Y", strtotime($row['FECHA_REGISTRO'])) . "</td>";
            echo "<td>
                    <a href='proveedores.php?op=$op&ta=$ta&edt=$id' class='btn btn-default'><i class='entypo-pencil'></i></a>
                    
                    <a href='proveedores.php?op=$op&ta=$ta&del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
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

            // --- Llamar procedimiento para proveedor
            $stmt = oci_parse($conn, "BEGIN PROC_OBTENER_PROVEEDOR(:id, :cursor); END;");
            $cursor = oci_new_cursor($conn);
            oci_bind_by_name($stmt, ":id", $id);
            oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
            oci_execute($stmt);
            oci_execute($cursor);

            if ($row = oci_fetch_array($cursor, OCI_ASSOC)) {
                $nombre_proveedor = htmlspecialchars($row["NOMBRE_PROVEEDOR"]);
                $correo = htmlspecialchars($row["CORREO"]);
                $direccion = htmlspecialchars($row["DIRECCION_PROVEEDOR"]);
                $estado = $row["ESTADO"];
            }

            oci_free_statement($stmt);
            oci_free_statement($cursor);

            // --- Llamar procedimiento para teléfonos
            $telefonos_editar = [];
            $stmt_tels = oci_parse($conn, "BEGIN PROC_OBTENER_TELEFONOS_PROVEEDOR(:id, :cursor); END;");
            $cursor_tels = oci_new_cursor($conn);
            oci_bind_by_name($stmt_tels, ":id", $id);
            oci_bind_by_name($stmt_tels, ":cursor", $cursor_tels, -1, OCI_B_CURSOR);
            oci_execute($stmt_tels);
            oci_execute($cursor_tels);

            while ($row_tel = oci_fetch_assoc($cursor_tels)) {
                $telefonos_editar[] = $row_tel;
            }

            oci_free_statement($stmt_tels);
            oci_free_statement($cursor_tels);

            $tipoEdit = "Editar";
            $edtVer = "edt=$id";
        }

        echo "<h3 class='modalx-titulo'>$tipoEdit proveedor</h3>";
        ?>

        <form action="proveedores.php<?php echo "?op=$op&ta=$ta&" . $edtVer; ?>" method="POST">
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

            <label>Teléfonos:</label>
            <div id="telefonos-container">
                <?php
                if (!empty($telefonos_editar)) {
                    foreach ($telefonos_editar as $tel) {
                        echo "<div class='input-group mb-2'>
                            <input type='hidden' name='id_telefonos[]' value='" . $tel['ID_TELEFONO'] . "'>
                            <input type='text' name='telefonos[]' class='form-control' value='" . htmlspecialchars($tel['TELEFONO']) . "'>
                            <span class='input-group-btn'>
                                <button type='button' class='btn btn-danger' onclick='eliminarTelefono(this)'>
                                    <i class='bi bi-x-circle'></i>
                                </button>
                            </span>
                          </div>";
                    }
                }

                echo "<div class='input-group mb-2'>
                    <input type='hidden' name='id_telefonos[]' value=''>
                    <input type='text' name='telefonos[]' class='form-control' placeholder='Nuevo teléfono'>
                    <span class='input-group-btn'>
                        <button type='button' class='btn btn-success' onclick='agregarTelefono()'>
                            <i class='bi bi-plus-circle'></i>
                        </button>
                    </span>
                  </div>";
                ?>
            </div>
            <br>

            <input type="hidden" name="submitted" value="TRUE" />
            <div class="modalx-footer">
                <a href='proveedores.php<?php echo "?op=$op&ta=$ta"; ?>' class="btn-cancelar">Cancelar</a>
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
            <a href='proveedores.php<?php echo "?op=$op&ta=$ta"; ?>' class="btn-cancelar">Cancelar</a>
            <a href='proveedores.php<?php echo "?op=$op&ta=$ta&del2=" . $del; ?>' class="btn-confirmar">Eliminar</a>
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
        if (event.target == modal) {
            cerrarModal();
        }
    };

    $(window).on('load', function () {
        var edt = '<?php echo $edt; ?>';
        if (edt !== "") {
            document.getElementById('modal-confirmar').style.display = 'block';
        }

        var del = '<?php echo $del; ?>';
        if (del !== "") {
            document.getElementById('modal-eliminar').style.display = 'block';
        }
    });

    function agregarTelefono() {
        const container = document.getElementById('telefonos-container');

        // Cambiar el botón + del anterior a un botón x
        const grupos = container.querySelectorAll('.input-group');
        grupos.forEach(grupo => {
            const btn = grupo.querySelector('button');
            btn.className = 'btn btn-danger';
            btn.innerHTML = '<i class="bi bi-x-circle"></i>';
            btn.setAttribute('onclick', 'eliminarTelefono(this)');
        });

        // Crear nuevo input con botón +
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

</script>

<?php include("../includes/footer.php"); ?>
<?php oci_close($conn); ?>