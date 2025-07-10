<?php
include('../includes/header.html');
include('../includes/barralateral.php');
include('../includes/funciones.php');
include("tabs.php");

$habilitar = ""; // Inicializar
$ta = "";
$op = "";

if (isset($_GET['ta'])) {
    $ta = $_GET['ta'];
}
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}


// Si se confirmó desde el modal
if (isset($_GET['confirmar_habilitacion'])) {
    $id_habilitar = $_GET['confirmar_habilitacion'];

    $sql = "BEGIN PROC_habilitar_proveedor(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $id_habilitar);

    if (oci_execute($stmt)) {
       echo "<script>window.location.href='proveedoresDeshabilitados.php?op=$op&ta=$ta';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Error al habilitar: " . $e['message'];
    }
    oci_free_statement($stmt);
}

// Si solo se quiere mostrar el modal
if (isset($_GET['habilitar'])) {
    $habilitar = $_GET['habilitar'];
}
?>

<!-- TÍTULO -->
<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 3;">Lista de Proveedores Deshabilitados</h2>
</div>
<br>

<!-- TABLA -->
<table class="table table-bordered table-striped datatable" id="tabla-deshabilitados">
    <thead>
        <tr>
            <th><strong style="color: #999999;">Nombre</strong></th>
            <th><strong style="color: #999999;">Correo</strong></th>
            <th><strong style="color: #999999;">Dirección</strong></th>
            <th><strong style="color: #999999;">Teléfonos</strong></th>
            <th><strong style="color: #999999;">Acciones</strong></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "BEGIN PROC_LISTAR_PROVEEDORES_DESHABILITADOS(:cursor); END;";
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
            echo "<td>
                    <a href='proveedoresDeshabilitados.php?op=$op&ta=$ta&habilitar=$id' class='btn btn-success'>
                        <i class='bi bi-check-circle'></i>
                    </a>
                  </td>";
            echo "</tr>";
        }

        oci_free_statement($stid);
        oci_free_statement($cursor);
        ?>
    </tbody>
</table>

<!-- MODAL DE CONFIRMACIÓN PARA HABILITAR -->
<div id="modal-habilitar" class="modalx">
    <div class="modalx-content">
        <h3 class="modalx-titulo">Confirmar habilitación</h3>
        <p class="modalx-texto">¿Estás seguro de que deseas habilitar este proveedor?</p>
        <div class="modalx-footer">
            <a href='proveedoresDeshabilitados.php<?php echo "?op=$op&ta=$ta";?>' class="btn-cancelar">Cancelar</a>
            <a href='proveedoresDeshabilitados.php<?php echo "?op=$op&ta=$ta&confirmar_habilitacion=$habilitar" ; ?>' class="btn btn-success">Habilitar</a>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script>
    // Mostrar modal si se viene con ?habilitar=
    window.onload = function () {
        const habilitar = "<?php echo $habilitar; ?>";
        if (habilitar !== "") {
            document.getElementById("modal-habilitar").style.display = "block";
        }
    };

    // Cerrar modal si se hace clic fuera de él
    window.onclick = function(event) {
        const modal = document.getElementById("modal-habilitar");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
</script>

<?php include("../includes/footer.php"); ?>
<?php oci_close($conn); ?>
