<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');

$del = $_GET['del'] ?? "";
$edt = $_GET['edt'] ?? "";
$edtVer = $edt ? "?edt=$edt" : "";

if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];
    $stmt = oci_parse($conn, "BEGIN eliminar_producto(:id); END;");
    oci_bind_by_name($stmt, ":id", $del2);
    if (oci_execute($stmt)) {
        echo "<script>window.location.href='productos.php?op=3&pc=1';</script>";
    } else {
        echo "Error: " . oci_error($stmt)['message'];
    }
    oci_free_statement($stmt);
    exit;
}

if (isset($_POST['submitted'])) {
    $nombre = trim($_POST["nombre_producto"]);
    $precio = trim($_POST["precio"]);
    $id_proveedor = $_POST["id_proveedor"];
    $id_categoria = $_POST["id_categoria"];

    if ($edt) {
        $stmt = oci_parse($conn, "BEGIN actualizar_producto(:id, :n, :p, :prov, :cat); END;");
        oci_bind_by_name($stmt,":id",$edt);
    } else {
        $stmt = oci_parse($conn, "BEGIN insertar_producto(:n, :p, :prov, :cat); END;");
    }
    oci_bind_by_name($stmt, ":n", $nombre);
    oci_bind_by_name($stmt, ":p", $precio);
    oci_bind_by_name($stmt, ":prov", $id_proveedor);
    oci_bind_by_name($stmt, ":cat", $id_categoria);

    if (oci_execute($stmt)) {
        echo "<script>window.location.href='productos.php?op=3&pc=1';</script>";
    } else {
        echo "Error: " . oci_error($stmt)['message'];
    }
    oci_free_statement($stmt);
    exit;
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
<!-- INTERFAZ -->
<hr>
<ol class="breadcrumb bc-3">
  <li><a href="../_dashboard/escritorio.php"><i class="entypo-home"></i>Home</a></li>
  <li class="active"><strong>Lista de Productos</strong></li>
</ol>
<div style="display:flex;justify-content:space-between;align-items:center;">
  <h2>Lista de Productos</h2>
  <button onclick="document.getElementById('modal-edit').style.display='block'" class="btn btn-success">Nuevo Producto</button>
</div>
<br>
<table class="table ..." id="table-2">
  <!-- encabezado omitido por brevedad -->
  <tbody>
    <?php
    $stid = oci_parse($conn, "BEGIN LISTAR_PRODUCTOS(:cursor); END;");
    $cur = oci_new_cursor($conn);
    oci_bind_by_name($stid,":cursor",$cur,-1,OCI_B_CURSOR);
    oci_execute($stid); oci_execute($cur);
    while($r = oci_fetch_assoc($cur)):
      echo "<tr>";
      echo "<td>{$r['ID_PRODUCTO']}</td>";
      echo "<td>{$r['NOMBRE_PRODUCTO']}</td>";
      echo "<td>₡".number_format($r['PRECIO'],2)."</td>";
      echo "<td>{$r['NOMBRE_PROVEEDOR']}</td>";
      echo "<td>{$r['NOMBRE_CATEGORIA']}</td>";
      echo "<td>".date('d-m-Y',strtotime($r['FECHA_REGISTRO']))."</td>";
      echo "<td>
              <a href='productos.php?edt={$r['ID_PRODUCTO']}&op=3&pc=1' class='btn btn-default'>✏️</a>
              <a href='productos.php?del={$r['ID_PRODUCTO']}&op=3&pc=1' class='btn btn-danger'>🗑️</a>
            </td>";
      echo "</tr>";
    endwhile;
    oci_free_statement($stid);
    oci_free_statement($cur);
    ?>
  </tbody>
</table>

<!-- MODAL: Agregar / Editar -->
<div id="modal-edit" class="modalx" style="display:none;">
  <div class="modalx-content">
    <h3><?php echo $edt ? "Editar Producto" : "Nuevo Producto"; ?></h3>
    <form method="POST">
      <input type="hidden" name="submitted" value="1">
      <label>Nombre:</label>
      <input type="text" name="nombre_producto" class="form-control" value="<?php echo $r['NOMBRE_PRODUCTO'] ?? ''; ?>" required>
      <label>Precio:</label>
      <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $r['PRECIO'] ?? ''; ?>" required>
      <label>Proveedor:</label>
      <?php cargarSelect($conn, 'LISTAR_PROVEEDORES', 'ID_PROVEEDOR','NOMBRE_PROVEEDOR','id_proveedor'); ?>
      <label>Categoría:</label>
      <?php cargarSelect($conn, 'LISTAR_CATEGORIAS', 'ID_CATEGORIA','NOMBRE_CATEGORIA','id_categoria'); ?>
      <div class="modalx-footer">
        <button type="button" onclick="window.location='productos.php?op=3&pc=1';" class="btn-cancelar">Cancelar</button>
        <button type="submit" class="btn btn-success">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Confirmar Eliminación -->
<div id="modal-delete" class="modalx" style="display:none;">
  <div class="modalx-content">
    <h3>Confirmar Eliminación</h3>
    <p>¿Eliminar el producto?</p>
    <div class="modalx-footer">
      <button onclick="window.location='productos.php?op=3&pc=1';" class="btn-cancelar">Cancelar</button>
      <a href="productos.php?del2=<?php echo $del;?>&op=3&pc=1" class="btn-confirmar">Eliminar</a>
    </div>
  </div>
</div>

<script>
  window.onload = function() {
    if ('<?php echo $edt;?>') document.getElementById('modal-edit').style.display = 'block';
    if ('<?php echo $del;?>') document.getElementById('modal-delete').style.display = 'block';
  }
</script>

<?php include("../includes/footer.php"); ?>
