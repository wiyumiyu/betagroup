<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');

if (isset($_GET['hab'])) {
    $hab = $_GET['hab'];
    $sql = "BEGIN habilitar_usuario(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $hab);

    if (oci_execute($stmt)) {
        // Redirigir a usuarios habilitados
        echo "<script>window.location.href = 'usuarios.php?op=admin&ta=activos';</script>";
        exit;
    } else {
        $e = oci_error($stmt);
        echo "Error al habilitar el usuario: " . $e['message'];
    }

    oci_free_statement($stmt);
}
?>

<?php include("tabs.php"); ?>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Lista de Usuarios Deshabilitados</h2>
</div>
<br>

<!-- Tabla de usuarios deshabilitados -->
<table class="table table-bordered table-striped datatable" id="tabla-deshabilitados">
    <thead>
        <tr>
            <th><strong style="color: #999999;">Usuario</strong></th>
            <th><strong style="color: #999999;">Teléfono</strong></th>
            <th><strong style="color: #999999;">Correo</strong></th>
            <th><strong style="color: #999999;">Rol</strong></th>
            <th><strong style="color: #999999;">Fecha de Registro</strong></th>
            <th><strong style="color: #999999;">Acción</strong></th>
        </tr>
    </thead>
    <tbody>
<?php
$sql = "BEGIN listar_usuarios_deshabilitados(:cursor); END;";
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
    echo "<td><a href='usuariosDeshabilitados.php?hab=$id' class='btn btn-success'><i class='bi bi-check-circle'></i></a></td>";
    echo "</tr>";
}

oci_free_statement($stid);
oci_free_statement($cursor);
?>
    </tbody>
</table>

<?php include("../includes/footer.php"); ?>
