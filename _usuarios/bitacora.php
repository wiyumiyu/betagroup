<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');

$del = "";

$op = "";
$ta = "";

if (isset($_GET['op'])) {
    $op = $_GET['op'];
}
if (isset($_GET['ta'])) {
    $ta = $_GET['ta'];
}

if (isset($_GET['del'])) {
    $del = $_GET['del'];
}
if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];
    $sql = "BEGIN PROC_eliminar_usuario(:id); END;";
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
    
}
?>


<?php include("tabs_bitacora.php"); ?>


<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Bitácora</h2>
    <!--    <button onclick="abrirModal()" class="btn btn-success">Nuevo</button>-->
</div>
<br>

<table class="table table-bordered table-striped datatable" id="table-2">
    <thead>
        <tr>
            <th style="width:15%"><strong style="color: #999999;">Usuario</strong></th>
            <th style="width:8%"><strong style="color: #999999;">Correo</strong></th>
            <th style="width:20%"><strong style="color: #999999;">Fecha</strong></th>
            <th style="width:57%"><strong style="color: #999999;">Descripción</strong></th>

        </tr>
    </thead>
    <tbody>
<?php
    // 1. Preparamos la llamada al procedimiento almacenado que devuelve la bitácora
$sql = "BEGIN PROC_LISTAR_BITACORA(:cursor); END;";
$stid = oci_parse($conn, $sql); // Preparamos el statement

// 2. Creamos un cursor que será llenado por el procedimiento
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR); // Enlazamos el cursor al parámetro de salida

// 3. Ejecutamos el statement que llama al procedimiento
oci_execute($stid);

// 4. Ejecutamos el cursor para obtener los resultados
oci_execute($cursor);

// 5. Iteramos sobre cada fila devuelta por el cursor
while ($row = oci_fetch_assoc($cursor)) {
    // a) Obtenemos el nombre del usuario. Si está vacío, se usa "Sin usuario"
    $usuario = htmlspecialchars($row['NOMBRE_USUARIO'] ?? 'Sin usuario');

    // b) Obtenemos el correo del usuario o "N/A" si no hay
    $correo = htmlspecialchars($row['CORREO'] ?? 'N/A');

    // c) Formateamos la fecha de la operación
    $fecha = date("d/m/Y H:i:s", strtotime($row['FECHA_OPERACION']));

    // d) Procesamos la descripción (que es un CLOB), usando load() para convertirlo en string
    $descripcion = $row['DESCRIPCION'];
    if ($descripcion instanceof OCILob) {
        $descripcion = $descripcion->load(); // Se convierte a string
    }
    // e) Escapamos HTML y convertimos saltos de línea a <br> para que se vea bien en la tabla
    $descripcion = nl2br(htmlspecialchars($descripcion));

    // f) Guardamos el ID del usuario por si se quiere hacer alguna acción (como eliminar)
    $id = $row['ID_USUARIO'];

    
    
    // g) Imprimimos la fila en la tabla HTML
    echo "<tr>";
    echo "<td>$usuario</td>";
    echo "<td>$correo</td>";
    echo "<td>$fecha</td>";
    echo "<td>$descripcion</td>";
    echo "</tr>";
}

// 6. Cerramos y liberamos los recursos
oci_free_statement($stid);
oci_free_statement($cursor);
?>
    </tbody>
</table>    