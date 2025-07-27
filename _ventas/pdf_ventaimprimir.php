<?php

//include ('../includes/header.html');
//require_once ('../includes/config.inc.php');
//require_once (MYSQL);
include("../includes/funciones.php");
include("pdf_plantilla.php");

//ob_end_clean();
//ob_start();
//require("../lib/fpdf181/fpdf.php");
//$ban = "";

//$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//mysqli_set_charset($dbc, "utf8");

$sql = "";
//mysqli_set_charset($dbc, "utf8");
//$resultado = mysqli_query($dbc, $sql);
//$solicitud = mysqli_fetch_row($resultado);


$correosR = "correo.ejemp@gmail.com";
$telefonos = "";
$ubicacion = "";
$impuestos = "";

$Xsangria = 16;
$ponerEncabezado = true;
$ponerPie = false;
//$ultimamuestra = 226;
//$lineasetiqueta = 0;
//$lineasanalisis = 0;
//$chretiquetamaximo = 40;
//$chranalisismaximo = 45;
//$ultimalineamuestras = 241;
//$cantidadmaximamuestras = 241;
$ultimalinea = 277;
$cantidadmaximalineas = 277;
//$primeralinea = 0;
$primeralineadetalle = 70;
$pagina = 1;
$version = 1;

// traer info de la factura

$id_venta = $_GET['id']; // o cualquier otro ID que estés usando
$conn = oci_connect("BETAGROUP", "beta123", "localhost/orcl", "AL32UTF8");
$sql_info = "BEGIN :cursor := FUNC_INFO_VENTA(:id_venta); END;";
$stid_info = oci_parse($conn, $sql_info);

$cursor_info = oci_new_cursor($conn);
oci_bind_by_name($stid_info, ":cursor", $cursor_info, -1, OCI_B_CURSOR);
oci_bind_by_name($stid_info, ":id_venta", $id_venta);

oci_execute($stid_info);
oci_execute($cursor_info);

$info = [];
if ($row = oci_fetch_assoc($cursor_info)) {
    $info = $row;
}

oci_free_statement($stid_info);
oci_free_statement($cursor_info);



$pdf = new PDF(); //Creamos un objeto de la librería
$pdf->AliasNbPages();
$pdf->Xsangria = $Xsangria;
$pdf->primeralineamuestras = $primeralineadetalle;
$pdf->version = $version;
$pdf->s_numero0 = $info['NUMERO'];
$pdf->s_usuario3 = $info['NOMBRE_CLIENTE'];
$pdf->s_usuario25 = "";
$pdf->correosR = $info['CORREO_CLIENTE'];
$pdf->impuestos = $info['IMPUESTOS'];
//$pdf->s_subcliente16 = $solicitud[14];
$pdf->s_fecha1 = $info['FECHA'];
//$pdf->responsable = $responsable;
//$pdf->s_material12 = $solicitud[12];
$pdf->telefonos = $telefonos;
//$pdf->s_cultivo5 = $cultivo;
$pdf->ubicacion = $ubicacion;
//$pdf->otropais = $otropais;
//$pdf->facturas = $facturas;
//$pdf->recibos = $recibos;
//$pdf->dias = $dias[0];
//$pdf->observaciones = $solicitud[15];
//$pdf->color = $solicitud[31];

//$pdf->rdetalle = $rdetalle;
$pdf->sesion_nombre = "nombre";
$pdf->sesion_apellido1 = "apellido1";
$pdf->sesion_apellido2 = "apellido2";


$pdf->AddPage(); //Agregamos una Pagina
#Establecemos los márgenes izquierda, arriba y derecha: 
//$pdf->SetMargins(30, 25 , 30); 
#Establecemos el margen inferior: 
$pdf->SetAutoPageBreak(true, 60);


//Agregamos texto en una celda de 40px ancho y 10px de alto, Con Borde, Sin salto de linea y Alineada a la derecha
//CELL
//1: largo de celda en pixeles
//2: alto de la celda
//3: texto
//4: borde
//5: salto de linea
//6: alineado
//MULTICELL
//1: largo de celda en pixeles
//2: alto de la celda
//3: texto
//4: borde
//5: ALINEACION
//6: FONDO



//
//
//$dbc2 = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//mysqli_set_charset($dbc2, "utf8");
//$rmuestras = mysqli_query($dbc2, $sqlmuestras);
//$idanterior = "";
//$cultivoanterior = "";
//$cultivoactual = "";
//$idactual = "";
//$analisis = "";
//$etiquetaanterior = "";
//$etiquetaactual = "";
//$cont = 0;
//$col1 = 5;
//$col2 = 100;
//$col3 = 60;
//$col4 = 20;

$y = 0;
$yactual = 0;
$yanterior = 0;
//$muestras_arr = array();
//$agno = "";
//$material = "";

$i = 0;

$tamarr = 0;






// FUNC_DETALLE_VENTA
$sql = "BEGIN :cursor := FUNC_DETALLE_VENTA(:id_venta); END;";
$stid = oci_parse($conn, $sql);

$cursor = oci_new_cursor($conn);
oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
oci_bind_by_name($stid, ":id_venta", $id_venta);

oci_execute($stid);
oci_execute($cursor);

$arr = [];
while ($row = oci_fetch_assoc($cursor)) {
    // Asumimos que las columnas son: NOMBRE_PRODUCTO, CANTIDAD, PRECIO_UNITARIO, DESCUENTO, TOTAL_PRODUCTO
    $arr[] = array(
        $row['NOMBRE_PRODUCTO'],
        $row['CANTIDAD'],
        $row['PRECIO_UNITARIO'],
        $row['DESCUENTO'],
        $row['TOTAL_PRODUCTO']
    );
}

oci_free_statement($stid);
oci_free_statement($cursor);





$tamarr = count($arr);

$col0 = 10;
$col1 = 95;
$col2 = 26;
$col3 = 20;
$col4 = 35;

$Xsangria  = 15;

$pdf->SetFont('Arial', '', 7);
$impresa = false;

foreach ($arr as $i => $item) {
    $producto = $item[0];
    $cantidad = $item[1];
    $precio   = $item[2];
    $descuento = $item[3];
    $total = $item[4];

    
    $precio = number_format($precio, 2, ',', '.');
    $total = number_format($total, 2, ',', '.');

    $y = $pdf->GetY();


    $pdf->SetY($y);

    $x = $Xsangria;
    $pdf->SetX($x);
    $pdf->MultiCell($col0, 4, $cantidad  ,0, 'L', 0);

    $pdf->SetY($y);
    $x+= $col0;
    $pdf->SetX($x);
    $pdf->MultiCell($col1, 4, $producto , 0, 'L', 0);

    $pdf->SetY($y);
    $x += $col1;
    $pdf->SetX($x );
    $pdf->MultiCell($col2, 4,$descuento . "%", 0, 'L', 0);

    $pdf->SetY($y);
    $x += $col2;
    $pdf->SetX($x);
    $pdf->MultiCell($col3, 4, mb_convert_encoding("¢" . $precio, 'ISO-8859-1', 'UTF-8')  , 0, 'R', 0);

    $pdf->SetY($y);
    $x += $col3;
    $pdf->SetX($x );
    $pdf->MultiCell($col4, 4,  mb_convert_encoding("¢" . $total, 'ISO-8859-1', 'UTF-8'), 0, 'R', 0);
    
    
    
    
    $impresa = true;
    //$i++;
}

// FUNC_TOTALES_VENTA
$sql_total = "BEGIN :cursor := FUNC_TOTALES_VENTA(:id_venta); END;";
$stid_total = oci_parse($conn, $sql_total);

$cursor_total = oci_new_cursor($conn);
oci_bind_by_name($stid_total, ":cursor", $cursor_total, -1, OCI_B_CURSOR);
oci_bind_by_name($stid_total, ":id_venta", $id_venta);

oci_execute($stid_total);
oci_execute($cursor_total);

$subtotal = $descuento = $total = 0;
$impuestosFinal = 0;
if ($row = oci_fetch_assoc($cursor_total)) {
    $subtotal  = number_format($row['SUBTOTAL'], 2, ',', '.');
    $descuento = number_format($row['DESCUENTO_TOTAL'], 2, ',', '.');
    
    $totalCrudo = $row['TOTAL'];
    $impuestosFinal = $totalCrudo * ($info['IMPUESTOS']/100);
    $total = $row['TOTAL'] + $impuestosFinal;
    $total = number_format($total, 2, ',', '.');

}

oci_free_statement($stid_total);
oci_free_statement($cursor_total);
oci_close($conn);





$pdf->SetY(210); // O ajustá según el espacio disponible



// SUBTOTAL
$pdf->SetX(130);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 6, "SUBTOTAL:", 0, 0, 'R'); // 0 = no salto
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(30, 6, mb_convert_encoding("¢" . $subtotal, 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
//
// DESCUENTO
$pdf->SetX(130);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 6, "DESCUENTO:", 0, 0, 'R'); // 0 = no salto
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(30, 6, mb_convert_encoding("¢" . $descuento, 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
//
// IMPUESTOS
$pdf->SetX(130);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 6, "IMPUESTOS:", 0, 0, 'R'); // 0 = no salto
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(30, 6, mb_convert_encoding("¢" . $impuestosFinal, 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');

// Línea horizontal encima de los totales
$pdf->SetDrawColor(0); // negro
$pdf->Line(130, 228, 200, 228); // de X=130 a X=200 en Y=218

//
// TOTAL
$pdf->SetX(130);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 6, "TOTAL:", 0, 0, 'R'); // 0 = no salto
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(30, 6, mb_convert_encoding("¢" . $total, 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');




$pdf->Output(); //Mostramos el PDF creado
ob_end_flush();

//function ponerEspacios($s) {
//    $resp = "";
//    $i = strlen($s);
//    while ($i > 0) {
//        $resp .= " ";
//        $i--;
//    }
//    return $resp;
//}
?>

