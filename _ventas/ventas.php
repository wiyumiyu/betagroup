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

$op = "";
$ta = "";
$del = "";
$edt = "";
$edtVer = "";

// Si en la URL hay un valor ?edt=, lo guardamos en la variable $edt para editar ese usuario
if (isset($_GET['edt'])) {
    $edt = $_GET['edt'];
}
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}
if (isset($_GET['ta'])) {
    $ta = $_GET['ta'];
}


// Si en la URL hay un valor ?del=, lo guardamos en $del para confirmar eliminación
if (isset($_GET['del'])) {
    $del = $_GET['del'];
}

// Si se confirma la eliminación con ?del2=, eliminamos la venta de la base de datos
if (isset($_GET['del2'])) {
    $del2 = $_GET['del2'];

    echo $_SESSION['id_usuario'];
    $stmt_contexto = llenarBitacora($_SESSION['id_usuario'], "BEGIN pkg_contexto_venta.set_venta(:id); END;", $conn);
//
//    $stmt_contexto2 = "BEGIN pkg_contexto_venta_detalle.set_venta_detalle(:id); END;";
//    llenarBitacora($_SESSION['id_usuario'], $stmt_contexto2, $conn);

    $sql = "BEGIN eliminar_venta_detalle(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $del2);

    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        echo "Error al eliminar la venta: " . $e['message'];
    }

    $sql = "BEGIN eliminar_venta(:id); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $del2);

    if (!oci_execute($stmt)) {
            $e = oci_error($stmt);
        echo "Error al eliminar la venta: " . $e['message']; 
    } 
    
    if (isset($stmt_contexto))
        oci_free_statement($stmt_contexto);
//    if (isset($stmt_contexto2))
//        oci_free_statement($stmt_contexto2);
    oci_free_statement($stmt);

    // Si se eliminó correctamente, recargamos la página
   // echo "<script>window.location.href = 'ventas.php?op=$op&ta=$ta';</script>";
}

// Si el formulario fue enviado (para agregar o actualizar un usuario)
if (isset($_POST['submitted'])) {
    // 1. Datos principales de la venta
    $numero = trim($_POST['numero']);
    $impuestos = trim($_POST['impuestos']);
    $id_cliente = trim($_POST['cliente']);
    $id_usuario = $_SESSION['id_usuario'];  // El usuario actual autenticado
    // 2. Insertar venta usando el procedimiento almacenado
    $sqlVenta = "BEGIN insertar_venta(:numero, :impuestos, :id_cliente, :id_usuario); END;";
    $stmtVenta = oci_parse($conn, $sqlVenta);

    oci_bind_by_name($stmtVenta, ":numero", $numero);
    oci_bind_by_name($stmtVenta, ":impuestos", $impuestos);
    oci_bind_by_name($stmtVenta, ":id_cliente", $id_cliente);
    oci_bind_by_name($stmtVenta, ":id_usuario", $id_usuario);

    if (!oci_execute($stmtVenta)) {
        $e = oci_error($stmtVenta);
        echo "Error al insertar la venta: " . $e['message'];
        exit;
    }
    oci_free_statement($stmtVenta);
// Paso 1: Preparamos llamada al procedimiento
    $sqlGetId = "BEGIN OBTENER_ULTIMO_ID_VENTA(:id); END;";
    $stmtId = oci_parse($conn, $sqlGetId);
    // Paso 2: Variable para capturar el ID
    $idVenta = null;
    oci_bind_by_name($stmtId, ":id", $idVenta, 10);
    // Paso 3: Ejecutamos
    if (!oci_execute($stmtId)) {
        $e = oci_error($stmtId);
        echo "Error al recuperar ID de venta: " . $e['message'];
        //exit;
    }
    oci_free_statement($stmtId);

    $stmt_contexto = llenarBitacora($_SESSION['id_usuario'], "BEGIN pkg_contexto_venta.set_venta(:id); END;", $conn);
    $stmt_contexto2 = llenarBitacora($_SESSION['id_usuario'], "BEGIN pkg_contexto_venta_detalle.set_venta_detalle(:id); END;", $conn);
    
    // 3. Recuperar el ID_VENTA generado automáticamente (vía CURRVAL)
//    $sqlGetId = "SELECT SEQ_ID_VENTA.CURRVAL AS ID FROM DUAL";
//    $stmtId = oci_parse($conn, $sqlGetId);
//    oci_execute($stmtId);
//    $row = oci_fetch_assoc($stmtId);
//    oci_free_statement($stmtId);
//    
//    echo $row['ID'];
//
//    if (!$row || !isset($row['ID'])) {
//        echo "No se pudo recuperar el ID de la venta.";
//        exit;
//    }
//
//    $idVenta = $row['ID'];
    // 4. Insertar los detalles de la venta
    $productos = $_POST['producto'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $precios = $_POST['precio_unitario'] ?? [];
    $descuentos = $_POST['descuento'] ?? [];

    $filas = min(count($productos), count($cantidades), count($precios)); // Por seguridad

    for ($i = 0; $i < $filas; $i++) {
        if ($productos[$i] === '' || $cantidades[$i] === '' || $precios[$i] === '') {
            continue;
        }

        $sqlDet = "BEGIN insertar_venta_detalle(:cantidad, :precio, :descuento, :producto, :id_venta); END;";
        $stmtDet = oci_parse($conn, $sqlDet);

        oci_bind_by_name($stmtDet, ":cantidad", $cantidades[$i]);
        oci_bind_by_name($stmtDet, ":precio", $precios[$i]);
        oci_bind_by_name($stmtDet, ":descuento", $descuentos[$i]);
        oci_bind_by_name($stmtDet, ":producto", $productos[$i]);
        oci_bind_by_name($stmtDet, ":id_venta", $idVenta);

        if (!oci_execute($stmtDet)) {
            $e = oci_error($stmtDet);
            echo "Error al insertar detalle #" . ($i + 1) . ": " . $e['message'];
            exit;
        }

        oci_free_statement($stmtDet);
    }

    if (isset($stmt_contexto))
        oci_free_statement($stmt_contexto);
   if (isset($stmt_contexto2))
        oci_free_statement($stmt_contexto2);
    //5. Confirmación
    echo "<script>//alert('Venta registrada correctamente'); 
            window.location='ventas.php?op=$op&ta=$ta';</script>";
}
?>

<!-- ------------------ INTERFAZ HTML ---------------------- -->



<?php
include("tabs.php");
?>

<!-- Título y botón para nuevo usuario -->
<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Lista de Ventas</h2>
    <button onclick="abrirModal()" class="btn btn-success">Nueva Venta</button>
</div>
<br>

<!-- Tabla donde se muestran todos los usuarios registrados -->
<table class="table table-bordered table-striped datatable" id="table-2">
    <thead>
        <tr>
            <th><strong style="color: #999999;">Número</strong></th>
            <th><strong style="color: #999999;">Fecha</strong></th>
            <th><strong style="color: #999999;">Impuestos</strong></th>
            <th><strong style="color: #999999;">Cliente</strong></th>
            <th><strong style="color: #999999;">Usuario</strong></th>
            <th><strong style="color: #999999;">Acciones</strong></th>
        </tr>
    </thead>
    <tbody>
<?php
// Se llama al procedimiento almacenado para listar LAS VENTAS
$sql = "BEGIN LISTAR_VENTAS(:cursor); END;";
$stid = oci_parse($conn, $sql);
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
oci_execute($stid);
oci_execute($cursor);

// Recorremos cada venta y la mostramos en una fila de la tabla
while ($row = oci_fetch_assoc($cursor)) {
    $id = $row['ID_VENTA'];
    echo "<tr>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['NUMERO']) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . date("d-m-Y", strtotime($row['FECHA'])) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['IMPUESTOS']) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['NOMBRE_CLIENTE']) . "</td>";
    echo "<td style='color: #4B4B4B;'>" . htmlspecialchars($row['NOMBRE_USUARIO']) . "</td>";

    // Botones de editar y eliminar
    echo "<td>
                    <a href='ventas.php?op=$op&ta=$ta&edt=$id' class='btn btn-default'><i class='entypo-eye'></i></a>
                    <a href='ventas.php?op=$op&ta=$ta&del=$id' class='btn btn-danger'><i class='entypo-cancel'></i></a>
                  </td>";
    echo "</tr>";
}

oci_free_statement($stid);
oci_free_statement($cursor);
?>
    </tbody>
</table>

<!-- ------------------ SCRIPT PARA MODALES ---------------------- -->
<script>
    // Funciones para mostrar u ocultar el modal de agregar/editar
    function abrirModal() {
        document.getElementById('modal-confirmar').style.display = 'block';
    }

    function cerrarModal() {
        document.getElementById('modal-confirmar').style.display = 'none';
    }

    // Si se hace clic fuera del modal, se cierra
    window.onclick = function (event) {
        const modal = document.getElementById('modal-confirmar');
        if (event.target == modal)
            cerrarModal();
    };

    // Si hay un valor 'edt' en PHP, se abre el modal de edición automáticamente
    $(window).on('load', function () {

        var edt = '<?php echo $edt; ?>';
        if (edt != "") {
            document.getElementById('modal-confirmar').style.display = 'block';
        }
    });


    // Si hay un valor 'del' en PHP, se abre el modal de confirmación para eliminar
    $(window).on('load', function () {
        var del = '<?php echo $del; ?>';
        if (del != "") {
            document.getElementById('modal-eliminar').style.display = 'block';
        }
    });

    // Esta función se activa al presionar el botón "Agregar"
    function agregarFila() {
        const productoSelect = document.querySelector('#tablaDetalle select');
        const cantidad = document.getElementById('cantidad').value;
        const precio = document.getElementById('precio_unitario').value;
        const descuento = document.getElementById('descuento').value;
        const idProducto = productoSelect.value;
        const nombreProducto = productoSelect.options[productoSelect.selectedIndex].text;

        if (!idProducto || !cantidad || !precio) {
            alert("Por favor complete producto, cantidad y precio.");
            return;
        }

        // Crear fila visual con inputs ocultos para envío por POST
        const tr = document.createElement('tr');
        tr.innerHTML = `
    <td>
      ${nombreProducto}
      <input type="hidden" name="producto[]" value="${idProducto}">
    </td>
    <td>
      ${cantidad}
      <input type="hidden" name="cantidad[]" value="${cantidad}">
    </td>
    <td>
      ${descuento || '0'}
      <input type="hidden" name="descuento[]" value="${descuento || 0}">
    </td>          
    <td>
      ${precio}
      <input type="hidden" name="precio_unitario[]" value="${precio}">
    </td>

    <td>
      <button type="button" class="btn btn-danger" onclick="eliminarFila(this)">X</button>
    </td>
  `;

        document.getElementById('detalleFactura').appendChild(tr);

        // Limpiar campos
        productoSelect.selectedIndex = 0;
        document.getElementById('cantidad').value = '';
        document.getElementById('precio_unitario').value = '';
        document.getElementById('descuento').value = '';
        recalcularTotales();
    }
// Elimina la fila correspondiente
    function eliminarFila(boton) {
        boton.closest("tr").remove();
        recalcularTotales();
    }

// Calcula y actualiza los totales
    function recalcularTotales() {
        let subtotal = 0;
        let descuentoTotal = 0;

        const filas = document.querySelectorAll("#detalleFactura tr");

        filas.forEach(fila => {
            const cantidad = parseFloat(fila.children[1].textContent) || 0;         // Cantidad
            const descuentoStr = fila.children[2].textContent || '0';               // Descuento
            const precio = parseFloat(fila.children[3].textContent) || 0;           // Precio unitario

            const descuento = parseFloat(descuentoStr.replace('%', '').replace(',', '.')) || 0;

            const totalProducto = cantidad * precio;
            subtotal += totalProducto;
            descuentoTotal += totalProducto * (descuento / 100);
        });

        const impuestos = parseFloat(document.getElementById("impuestos").value) || 0;
        const impuestosTotal = (subtotal - descuentoTotal) * (impuestos / 100);
        const totalFinal = subtotal - descuentoTotal + impuestosTotal;

        document.getElementById("subtotal").textContent = subtotal.toFixed(2);
        document.getElementById("descuento_total").textContent = descuentoTotal.toFixed(2);
        document.getElementById("impuestos_total").textContent = impuestosTotal.toFixed(2);
        document.getElementById("total").textContent = totalFinal.toFixed(2);
    }

    // esto funciona para el select de productos en ventas
    document.addEventListener('DOMContentLoaded', function () {
        const productoSelect = document.getElementById("producto");
        const precioInput = document.getElementById("precio_unitario");

        // Cada vez que se selecciona un producto
        productoSelect.addEventListener("change", function () {
            const precio = this.options[this.selectedIndex].getAttribute("data-precio");
            if (precio) {
                precioInput.value = precio;
            } else {
                precioInput.value = "";
            }
        });
    });


//evitar que se cierre el script

//document.addEventListener('DOMContentLoaded', function () {
//  const modal = document.getElementById('modal-confirmar');
//
//  // Desactivar cerrar al hacer clic fuera del modal
//  modal.addEventListener('click', function (e) {
//    if (e.target === modal) {
//      // No hacer nada: bloquear salida
//      e.stopPropagation();
//    }
//  });
//
//  // Desactivar cerrar con tecla Escape
//  window.addEventListener('keydown', function (e) {
//    if (e.key === "Escape") {
//      e.preventDefault(); // evita cierre
//    }
//  });
//});


</script>



<!-- ------------------ MODAL PARA FORMULARIO DE USUARIO ---------------------- -->
<div id="modal-confirmar" class="modalx modalx_venta">
    <div class="modalx-content">
<?php
// Variables para rellenar el formulario si se está editando
$nombre = $telefono = $correo = $contrasena = $rol = "";
$seleccionadoA = $seleccionadoV = "";
$edt = "";
$tipoEdit = "Nueva Venta";
$cliente_seleccionado = 0;
// Abrimos cursor desde el procedimiento LISTAR_PRODUCTOS
$sql = "BEGIN LISTAR_PRODUCTOS(:cursor); END;";
$stmt = oci_parse($conn, $sql);
$cursor = oci_new_cursor($conn);
oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);
oci_execute($stmt);
oci_execute($cursor);
$selectProductos = '<select class="form-select" name="producto" id="producto" required>';
$selectProductos .= "<option value=-1>-- Seleccione --</option>";
// Recorremos los productos
while ($row = oci_fetch_assoc($cursor)) {
    $id = $row['ID_PRODUCTO'];
    $nombre = htmlspecialchars($row['NOMBRE_PRODUCTO']);
    $precio = $row['PRECIO'];

    $selectProductos .= "<option value=\"$id\" data-precio=\"$precio\">$nombre</option>";
}

$selectProductos .= '</select>';

oci_free_statement($stmt);
oci_free_statement($cursor);

// Si se está editando, cargamos los datos del usuario
if (isset($_GET["edt"])) {
    $tipoEdit = "Editar Venta";
    $venta_id = $_GET["edt"];
    // Obtener datos de la venta
    $sql = "BEGIN OBTENER_VENTA(:id_venta, :numero, :impuestos, :id_cliente); END;";
    $stmt = oci_parse($conn, $sql);

    // Parámetros IN y OUT
    oci_bind_by_name($stmt, ":id_venta", $venta_id); // IN
    oci_bind_by_name($stmt, ":numero", $numero, 32); // OUT
    oci_bind_by_name($stmt, ":impuestos", $impuestos, 32); // OUT
    oci_bind_by_name($stmt, ":id_cliente", $id_cliente, 32); // OUT

    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        echo "Error al ejecutar el procedimiento: " . $e['message'];
    }
    $cliente_seleccionado = $id_cliente;

    // Conexión y preparación
    $sqlDetalle = "BEGIN LISTAR_DETALLES_VENTA(:id_venta, :cursor); END;";
    $stmtDetalle = oci_parse($conn, $sqlDetalle);

    // Crear cursor de salida
    $cursorDetalle = oci_new_cursor($conn);

    // Bind de parámetros
    oci_bind_by_name($stmtDetalle, ":id_venta", $venta_id);
    oci_bind_by_name($stmtDetalle, ":cursor", $cursorDetalle, -1, OCI_B_CURSOR);

    // Ejecutar procedimiento y cursor
    oci_execute($stmtDetalle);
    oci_execute($cursorDetalle);

    // Leer resultados
    $detalles = [];
    while ($row = oci_fetch_assoc($cursorDetalle)) {
        $detalles[] = $row;
    }

    // Liberar recursos
    oci_free_statement($stmtDetalle);
    oci_free_statement($cursorDetalle);
} else {
    $sql = "BEGIN OBTENER_MAX_NUMERO_VENTA(:max_numero); END;";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":max_numero", $maxNumero, 10);
    oci_execute($stmt);
    $maxNumero += 1;
}

echo "<h3 class='modalx-titulo'>$tipoEdit</h3>";

// llenar select de clientes


$selectClientes = llenarSelect("cliente", "ID_CLIENTE", "NOMBRE_CLIENTE", $cliente_seleccionado, "BEGIN listar_clientes(:cursor); END;", $conn);
?>

        <form action="ventas.php<?php echo "?op=$op&ta=$ta"; ?>" method="POST" id="formFactura">
            <!-- Encabezado de factura -->
            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label for="numero">Número:</label>
                    <input type="number" id="numero" name="numero" class="form-control" value="<?php
        if (isset($_GET["edt"])) {
            echo $numero;
        } else
            echo $maxNumero;
?>" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="impuestos">Impuestos (%):</label>
                    <input type="number" id="impuestos" name="impuestos" class="form-control" value="<?php
                    if (isset($_GET["edt"])) {
                        echo $impuestos;
                    } else
                        echo 13;
                    ?>" required>
                </div>
            </div>

            <div class="form-group mt-3">
                <label for="cliente">Cliente:</label>
                           <?php echo $selectClientes; ?>
            </div>

            <!-- Tabla de detalles -->
            <h4 class="mt-4">Agregar Detalle</h4>
            <table class="table" id="tablaDetalle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Descuento</th>
                        <th>Precio Unitario</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="detalleFactura">
                    <!-- Las filas se agregarán dinámicamente -->
<?php
$subtotal = 0;
$descuento = 0;
$total = 0;
$impuestos_total = 0;
if (!empty($detalles)) {
    foreach ($detalles as $det) {
        $descuento = ($det['CANTIDAD'] * $det['PRECIO_UNITARIO']) * ($det['DESCUENTO'] / 100);
        $subtotal += ($det['CANTIDAD'] * $det['PRECIO_UNITARIO']);
        $impuestos_total += ($det['CANTIDAD'] * $det['PRECIO_UNITARIO']) * ($impuestos / 100);

        echo "<tr>
                                <td>{$det['NOMBRE_PRODUCTO']}<input type='hidden' name='producto[]' value='{$det['ID_PRODUCTO']}'></td>
                                <td>{$det['CANTIDAD']}<input type='hidden' name='cantidad[]' value='{$det['CANTIDAD']}'></td>
                                <td>{$det['DESCUENTO']}<input type='hidden' name='descuento[]' value='{$det['DESCUENTO']}'></td>
                                <td>{$det['PRECIO_UNITARIO']}<input type='hidden' name='precio_unitario[]' value='{$det['PRECIO_UNITARIO']}'></td>
                                <td><button type='button' class='btn btn-danger' onclick='this.closest(\"tr\").remove()'>X</button></td>
                                </tr>";
    }
    $total = ($subtotal - $descuento) + $impuestos_total;
}
?>

                </tbody>
                <tfoot>
                    <tr>
                        <td><?php echo str_replace('name="producto"', 'id="producto"', $selectProductos); ?></td>
                        <td><input type="number" id="cantidad" class="form-control" placeholder="Cantidad"></td>
                        <td><input type="number" id="descuento" class="form-control" placeholder="Descuento"></td>
                        <td><input type="number" readonly id="precio_unitario" class="form-control" placeholder="Precio Unitario"></td>                        
                        <td><button type="button" class="btn btn-primary" onclick="agregarFila()">Agregar</button></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Totales visuales (puedes mejorarlos con JS si quieres) -->
            <div style="text-align: right; margin-top: 20px;">
                <p>Subtotal: <span id="subtotal"><?php
                    if (isset($_GET['edt'])) {
                        echo $subtotal;
                    } else {
                        echo '0.00';
                    }
?></span></p>
                <p>Descuento Total: <span id="descuento_total"><?php
                        if (isset($_GET['edt'])) {
                            echo $descuento;
                        } else {
                            echo '0.00';
                        }
                        ?></span></p>
                <p>Impuestos Totales: <span id="impuestos_total"><?php
                        if (isset($_GET['edt'])) {
                            echo $impuestos_total;
                        } else {
                            echo '0.00';
                        }
                        ?></span></p>
                <p><strong>Total: <span id="total"><?php
                            if (isset($_GET['edt'])) {
                                echo $total;
                            } else {
                                echo '0.00';
                            }
                            ?></span></strong></p>
            </div>

            <input type="hidden" name="submitted" value="TRUE">

            <div class="modal-footer">
                <a href="ventas.php<?php echo "?op=$op&ta=$ta"; ?>" class="btn btn-danger">Salir</a>
                <button type="submit" class="btn btn-success">Agregar Factura</button>
            </div>
        </form>
    </div>
</div>

<!-- ------------------ MODAL DE CONFIRMACIÓN PARA ELIMINAR ---------------------- -->
<div id="modal-eliminar" class="modalx">
    <div class="modalx-content">
        <h3 class="modalx-titulo">Confirmar eliminación</h3>
        <p class="modalx-texto">¿Estás seguro de que deseas eliminar esta venta?</p>
        <div class="modalx-footer">
            <a href='ventas.php<?php echo "?op=$op&ta=$ta"; ?>' class="btn btn-cancelar">Cancelar</a>
            <a href='ventas.php<?php echo "?op=$op&ta=$ta&del2=" . $del; ?>' class="btn-confirmar">Eliminar</a>
        </div>
    </div>
</div>

<!-- Pie de página -->
<?php include("../includes/footer.php"); ?>





