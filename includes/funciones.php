<?php

function llenarSelect($name, $id_tabla, $col_tabla, $sqlproc, $conn) {

    $sel = "";
    $stid = oci_parse($conn, $sqlproc);
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
    oci_execute($stid);
    oci_execute($cursor);
    $sel = "<option value=-1>-- Seleccione --</option>";
    while ($row = oci_fetch_assoc($cursor)) {
        $id = $row[$id_tabla];

        $sel .= "<option value=$id>" . htmlspecialchars($row[$col_tabla]) . "</option>";
    }

    $sel = "<select id='$name' name='$name' class='form-control'>$sel</select>";
    oci_free_statement($stid);
    oci_free_statement($cursor);
    return $sel;
}

//
//$icosize = 28;
//
//function llenaConteo(&$lista, &$totalpaginas, &$regporpagina, &$totalregistros, $sql) {
//
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($Fdbc, "utf8");
//
//    $lista = mysqli_query($Fdbc, $sql);
//    $totalregistros = mysqli_num_rows($lista);
//
//    if ($totalregistros < $regporpagina) {
//        $regporpagina = $totalregistros;
//    }
//
//
//    if ($regporpagina > 0)
//        $totalpaginasTemp = $totalregistros / $regporpagina;
//    else
//        $totalpaginasTemp = $totalregistros / 1;
//    $totalpaginas = round($totalpaginasTemp, 0, PHP_ROUND_HALF_UP);
//
//    if ($totalpaginasTemp > $totalpaginas) {
//        $totalpaginas = $totalpaginas + 1;
//    }
//}
//
//function llenaConteoFiltrado(&$lista, &$totalpaginas, $regporpagina, &$totalregistros, $sql, $filtro) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    if ($filtro != "") {
//        $sql = $sql . " AND (" . $filtro . ")";
//    }
//
//    $lista = mysqli_query($Fdbc, $sql);
//
//    $totalregistros = mysqli_num_rows($lista);
//    $totalpaginas = $totalregistros / $regporpagina;
//    $totalpaginas = round($totalpaginas, 0, PHP_ROUND_HALF_UP);
//}
//
//function llenaPaginacion(&$lista, $inicio, $regporpagina, $ordenarpor, $sql) {
//
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($Fdbc, "utf8");
//    $inicio = (($inicio - 1) * $regporpagina);
////$sql = "Select * From tbl_personal WHERE (tbl_personal.nombre <> 'sistema') ORDER BY  tbl_personal.estado, tbl_personal.nombre LIMIT $inicio,$regporpagina";
//
//    $sql = $sql . " ORDER BY $ordenarpor " . " LIMIT $inicio,$regporpagina";
//
//    $lista = mysqli_query($Fdbc, $sql);
//}
//
//function traearea($codarea) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($Fdbc, "utf8");
//    $sql = "Select nombre From tbl_area WHERE (tbl_area.id_area = $codarea)";
//    $resultado = mysqli_query($Fdbc, $sql);
//    $fila = mysqli_fetch_row($resultado);
//    return $fila[0];
//}
//
////llenapersonalconteo($personal, $totalpaginas, $regporpagina, $filtro, $totalregistros, $sql);
//
//
//
///* Normaliza una fecha de
// * dd/mm/aaaa a aaaa-mm-dd o de aaaa-mm-dd a dd/mm/aaaa */
//function convertirfecha($date) {
//
//    $resp = "";
//    if (!empty($date)) {
//        if (substr($date, 2, 1) == "/") { //Si viene de php y va para mysql
//            $var = explode('/', str_replace('-', '/', $date));
//            $resp = "$var[2]-$var[1]-$var[0]";
//        } else { //si viene de mysql a mostrar en php
//            $var = explode('-', str_replace('/', '-', $date));
//            $resp = "$var[2]/$var[1]/$var[0]";
//        }
//    }
//    if ($resp == "00/00/0000" || $resp == "00-00-0000") {
//        $resp = "";
//    }
//    return $resp;
//}
//
//function convertirfecha2($date) {
//    $resp = "";
//    if (!empty($date)) {
//        if (substr($date, 2, 1) == "/") { //Si viene de php y va para mysql
//            $var = explode('/', str_replace('-', '/', $date));
//            $resp = substr($var[2], 0, 4) . "-$var[1]-$var[0] " . substr($var[2], 5, 9);
//        } else { //si viene de mysql a mostrar en php
//            $var = explode('-', str_replace('/', '-', $date));
//            $resp = substr($var[2], 0, 2) . "/$var[1]/$var[0] " . substr($var[2], 3, 8);
//        }
//    }
//    if ($resp == "00/00/0000" || $resp == "00-00-0000") {
//        $resp = "";
//    }
//    return $resp;
//}
//
////function llenacombo2($ssql, $valorselecionado, $nombredelcombo, $indexvalue, $indexdisplay, $ancho) {
////    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
////
////    //echo "<select name=\"$nombredelcombo\"> ";
////    echo "<select name='agno' id='agno' style='opacity: 0; position: relative; z-index: 100; width: ".$ancho."px' >";
////    mysqli_set_charset($dbc, "utf8");
////    $resultado = mysqli_query($dbc, $ssql);
////
////    while ($fila = mysqli_fetch_row($resultado)) {
////        if (strlen($fila[$indexdisplay]) < 6) {
////            $fila[$indexdisplay] = $fila[$indexdisplay] . "......";
////        }
////        echo $fila[$indexdisplay];
////        if ($fila[0] == $valorselecionado) {
////            echo "<option value='$fila[$indexvalue]' selected>$fila[$indexdisplay]</option>";
////        } else {
////            echo "<option value='$fila[$indexvalue]'>$fila[$indexdisplay]</option>";
////        }
////    }
////
////    echo "</select>";
////    mysqli_close($dbc);
////}
//function llenacombo($ssql, $valorselecionado, $nombredelcombo, $indexvalue, $indexdisplay, $ancho = 220) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    //echo "<select name=\"$nombredelcombo\"> ";
//    echo "<select name='$nombredelcombo' id='$nombredelcombo' style='opacity: 0; position: relative; z-index: 100; width: " . $ancho . "px' >";
//    mysqli_set_charset($dbc, "utf8");
//    $resultado = mysqli_query($dbc, $ssql);
//
//    while ($fila = mysqli_fetch_row($resultado)) {
//
//        //echo $fila[$indexdisplay];
//        //echo "holla";
//        if ($fila[0] == $valorselecionado) {
//            echo "<option value='$fila[$indexvalue]' selected>$fila[$indexdisplay]</option>";
//        } else {
//            echo "<option value='$fila[$indexvalue]'>$fila[$indexdisplay]</option>";
//        }
//    }
//
//    echo "</select>";
//    mysqli_close($dbc);
//}
//
//function llenacomboConOpcionCero($ssql, $valorselecionado, $nombredelcombo, $indexvalue, $indexdisplay, $ancho = 220) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    //echo "<select name=\"$nombredelcombo\"> ";
//    echo "<select name='$nombredelcombo' id='$nombredelcombo' style='opacity: 0; position: relative; z-index: 100; width: " . $ancho . "px' >";
//    mysqli_set_charset($dbc, "utf8");
//    $resultado = mysqli_query($dbc, $ssql);
//
//    $i = 0;
//    if ($i == 0 && $indexvalue == 0) {
//        echo "<option value='0' selected>Seleccione $nombredelcombo...</option>";
//        $i += 1;
//    }
//    while ($fila = mysqli_fetch_row($resultado)) {
//
//
//
//        if ($fila[0] == $valorselecionado) {
//            echo "<option value='$fila[$indexvalue]' selected>$fila[$indexdisplay]</option>";
//        } else {
//            echo "<option value='$fila[$indexvalue]'>$fila[$indexdisplay]</option>";
//        }
//    }
//
//    echo "</select>";
//    mysqli_close($dbc);
//}
//
//function llenacomboproyectos($ssql, $valorselecionado, $nombredelcombo, $indexvalue, $indexdisplay, $ancho = 420) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    //echo "<select name=\"$nombredelcombo\"> ";
//    echo "<select name='$nombredelcombo' id='$nombredelcombo' style='opacity: 0; position: relative; z-index: 100; width: " . $ancho . "px' >";
//    mysqli_set_charset($dbc, "utf8");
//    $resultado = mysqli_query($dbc, $ssql);
//    if ($indexdisplay == -1) {
//        echo "<option value='-1' selected>Seleccione un proyecto...</option>";
//    } else {
//        echo "<option value='-1' >Seleccione un proyecto...</option>";
//    }
//
//
//    while ($fila = mysqli_fetch_row($resultado)) {
////        if (strlen($fila[$indexdisplay]) < 6) {
////            $fila[$indexdisplay] = $fila[$indexdisplay] . "......";
////        }
//        echo $fila[$indexdisplay];
//        if ($fila[0] == $valorselecionado) {
//            echo "<option value='$fila[$indexvalue]' selected>$fila[$indexdisplay]</option>";
//        } else {
//            echo "<option value='$fila[$indexvalue]'>$fila[$indexdisplay]</option>";
//        }
//    }
//
//    echo "</select>";
//    mysqli_close($dbc);
//}
//
//function llenacombodescuento($ssql, $valorselecionado, $nombredelcombo, $indexvalue, $indexdisplay, $ancho = 220) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    //echo "<select name=\"$nombredelcombo\" onchange=\"cambiarDescuento(event)\"> ";
//    echo "<select name='$nombredelcombo' id='$nombredelcombo' onchange=\"cambiarDescuento(event)\" style='opacity: 0; position: relative; z-index: 100; width: " . $ancho . "px' >";
//    mysqli_set_charset($dbc, "utf8");
//    $resultado = mysqli_query($dbc, $ssql);
//    $i = 0;
//    while ($fila = mysqli_fetch_row($resultado)) {
////        if (strlen($fila[$indexdisplay]) < 6) {
////            $fila[$indexdisplay] = $fila[$indexdisplay] . "......";
////        }
//
//        if ($i == 0 && $indexvalue == 0) {
//            echo "<option value='0' selected>Seleccione un Material...</option>";
//            $i += 1;
//        } else if ($i == 0) {
//            echo "<option value='0'>Seleccione un Material...</option>";
//            $i += 1;
//        }
//        if ($fila[0] == $valorselecionado) {
//            echo "<option value='$fila[$indexvalue]' selected>$fila[$indexdisplay]</option>";
//        } else {
//            echo "<option value='$fila[$indexvalue]'>$fila[$indexdisplay]</option>";
//        }
//    }
//
//    echo "</select>";
//    mysqli_close($dbc);
//}
//
//function llenacombosinpuntos($ssql, $valorselecionado, $nombredelcombo, $indexvalue, $indexdisplay) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    mysqli_set_charset($dbc, "utf8");
//    $resultado = mysqli_query($dbc, $ssql);
//
//    while ($fila = mysqli_fetch_row($resultado)) {
//        if (strlen($fila[$indexdisplay]) < 6) {
//            $fila[$indexdisplay] = $fila[$indexdisplay];
//        }
//        echo $fila[$indexdisplay];
//        if ($fila[0] == $valorselecionado) {
//            echo "<option value='$fila[$indexvalue]' selected>$fila[$indexdisplay]</option>";
//        } else {
//            echo "<option value='$fila[$indexvalue]'>$fila[$indexdisplay]</option>";
//        }
//    }
//
//
//    mysqli_close($dbc);
//}
//
//function llenacombosinpuntosmas1($ssql, $valorselecionado, $nombredelcombo, $indexvalue, $indexdisplay) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    mysqli_set_charset($dbc, "utf8");
//    $resultado = mysqli_query($dbc, $ssql);
//    echo "<option value='-1' selected>Seleccione un análisis...</option>";
//    while ($fila = mysqli_fetch_row($resultado)) {
////        if (strlen($fila[$indexdisplay]) < 6) {
////            $fila[$indexdisplay] = $fila[$indexdisplay];
////        }
//        //echo $fila[$indexdisplay];
//        if ($fila[0] == $valorselecionado) {
//            echo "<option value='$fila[$indexvalue]' selected>$fila[3]. $fila[$indexdisplay]</option>";
//        } else {
//            echo "<option value='$fila[$indexvalue]'>$fila[3]. $fila[$indexdisplay]</option>";
//        }
//    }
//
//
//    mysqli_close($dbc);
//}
//
//function llenacomboagnos($agnoactual) {
//    $i = $agnoactual - 1;
//    $j = $agnoactual;
//    while ($i <= $j) {
//        if ($i == $agnoactual) {
//            echo "<option value='$i' selected>$i</option>";
//        } else {
//            echo "<option value='$i'>$i</option>";
//        }
//        $i += 1;
//    }
//}
//
//function llenacomboLocalidades($valorselecionado, $nombredelcombo) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $lugaresarray = array();
//
//    $szql = "Select id_lugar,nombre, tipo, padre from tbl_lugares order by  id_lugar ";
//    $resultadoz = mysqli_query($dbc, $szql);
//    while ($fila = mysqli_fetch_row($resultadoz)) {
//        $lugaresarray[] = $fila;
//    }
//
//
//
//
//    $ssql = "Select id_lugar,nombre, tipo, padre from tbl_lugares where tipo = 1 or  tipo = 2 or  tipo = 3 
//            order by padre ";
//    echo "<select name=\"$nombredelcombo\" onchange='asignarMontos(this.value,\"total\", \"desayuno\", \"almuerzo\", \"cena\", \"transporte\", \"hospedaje\" )' > ";
//    $resultado = mysqli_query($dbc, $ssql);
//
//    while ($fila = mysqli_fetch_row($resultado)) {
//
//        $lugar = localidaddetallada($lugaresarray, $fila[0], $fila[1], $fila[2], $fila[3]);
//
//        if ($fila[0] == $valorselecionado) {
//            echo "<option value='$fila[0]' selected>$fila[0] . '--'. $lugar</option>";
//        } else {
//            echo "<option value='$fila[0]'>$fila[0] . '--'. $lugar</option>";
//        }
//    }
//
//    echo "</select>";
//    mysqli_close($dbc);
//}
//
//function llenacomboConFraseInicio($ssql, $valorselecionado, $nombredelcombo, $indexvalue, $indexdisplay, $script, $ancho = 220) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    //echo "<select name=\"$nombredelcombo\"> ";
//    echo "<select name='$nombredelcombo' $script id='$nombredelcombo' style='opacity: 0; position: relative; z-index: 100; width: " . $ancho . "px' >";
//    mysqli_set_charset($dbc, "utf8");
//    $resultado = mysqli_query($dbc, $ssql);
//    $i = 0;
//    while ($fila = mysqli_fetch_row($resultado)) {
//
//        //echo $fila[$indexdisplay];
//        if ($i == 0 && $valorselecionado == -1) {
//            echo "<option value='-1' selected>Seleccione $nombredelcombo...</option>";
//            $i = 1;
//        }
//        if ($fila[0] == $valorselecionado) {
//            echo "<option value='$fila[$indexvalue]' selected>$fila[$indexdisplay]</option>";
//        } else {
//            echo "<option value='$fila[$indexvalue]'>$fila[$indexdisplay]</option>";
//        }
//    }
//
//    echo "</select>";
//    mysqli_close($dbc);
//}
//
//function localidaddetallada($lugaresarray, $id, $nombre, $tipo, $padre) {
//    $nlugares = count($lugaresarray);
//    $lugar = $nombre;
////$lugar =  $lugaresarray[1][1];
////    if ($padre > 593) {
////         if ($tipo == 2 || $tipo == 3) {
////            $lugar = $lugaresarray[592][1] . "; " . $lugar;
////            $id = $padre;
////            $tipo = $lugaresarray[592][2];
////            $padre = $lugaresarray[592][3];
////        }
////    }
//    //if (padre < 593) {
//    if ($tipo == 2 || $tipo == 3) {
//
//        $lugar = $lugaresarray[$padre - 1][1] . "; " . $lugar;
//        $id = $padre;
//        $tipo = $lugaresarray[$padre - 1][2];
//        $padre = $lugaresarray[$padre - 1][3];
//    }
//
//
//    if ($padre == 593) {
//        if ($tipo == 2 || $tipo == 3) {
//            //$lugar = $lugaresarray[592][1] . "; " . $lugar;
//            $id = $padre;
//            $tipo = 1;
//            $padre = 2;
//        }
//    }
//
//    if ($tipo == 1) {
//
//        $lugar = $lugaresarray[$padre - 1][1] . "; " . $lugar;
//        $id = $padre;
//        $tipo = $lugaresarray[$padre - 1][2];
//        $padre = $lugaresarray[$padre - 1][3];
//    }
//    // }
////    if ($tipo == 0   ){
////      
////        $lugar = $lugaresarray[$padre-1][1] . "; ". $lugar;
////
////    }
//    return $lugar;
//}
//
//function llenacomboLugar($valorselecionado, $nombredelcombo) {
//    $dbcLugar = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    echo "<select name='$nombredelcombo'> ";
//    $resultadoLugar = mysqli_query($dbcLugar, 'Select * from tbl_activo_lugar order by piso, oficina, lugar ');
//    while ($fila = mysqli_fetch_row($resultadoLugar)) {
//
//        if (substr($fila[3], 0, 10) == "Poscosecha") {
//
//            $fila[1] = $fila[1] / 10;
//            $temp = $fila[3];
//            $fila[3] = $fila[2];
//            $fila[2] = $fila[1];
//            $fila[1] = $temp;
//        }
//
//        if ($fila[0] == '0')
//            $fila[1] = '';
//        elseif ($fila[1] == '0')
//            $fila[1] = 'Afuera';
//        elseif (substr($fila[1], 0, 10) == "Poscosecha")
//            $fila[2] = ', Piso ' . $fila[2];
//        else
//            $fila[1] = 'Piso ' . $fila[1];
//
//
//
//
//        if (substr($fila[1], 0, 10) == "Poscosecha") {
//            if (is_numeric($fila[3]))
//                $fila[3] = ', Oficina ' . $fila[3];
//        } else {
//            if (is_numeric($fila[2]))
//                $fila[2] = 'Oficina ' . $fila[2];
//            if ($fila[2] != '' && $fila[0] != 0)
//                $fila[2] = ', ' . $fila[2];
//            if ($fila[3] != '' && $fila[0] != 0)
//                $fila[3] = ', ' . $fila[3];
//        }
//        if ($fila[0] == $valorselecionado) {
//            echo utf8_encode("<option value='$fila[0]' selected>$fila[1]$fila[2]$fila[3]</option>");
//        } else {
//            echo utf8_encode("<option value='$fila[0]'>$fila[1]$fila[2]$fila[3]</option>");
//        }
//    }
//
//    echo "</select>";
//    mysqli_close($dbcLugar);
//}
//
//function llenacombopersonal($valorselecionado, $nombredelcombo) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    $sql = "                   SELECT tbl_persona.id_persona, CONVERT( CONCAT_WS( _utf8 ' ', tbl_persona.nombre, tbl_persona.apellido1, tbl_persona.apellido2, tbl_persona_grado_academico.abreviatura ) 
//USING utf8 ) AS nombrea1a2
//FROM tbl_persona
//LEFT JOIN tbl_persona_grado_academico ON tbl_persona.id_persona_grado_academico = tbl_persona_grado_academico.id_persona_grado_academico
//WHERE id_estado =1
//ORDER BY nombre, apellido1, apellido2";
//
//    echo "<select class='form-control' name=\"$nombredelcombo\" id=\"$nombredelcombo\">";
//    $resultado = mysqli_query($dbc, $sql);
//
//    while ($fila = mysqli_fetch_row($resultado)) {
//
//        $nombre = utf8_encode($fila[1] . " " . $fila[2] . " " . $fila[3]);
//        if ($fila[4] != "") {
//            $nombre .= ", " . $fila[4];
//        }
//        if ($fila[0] == $valorselecionado) {
//            echo "<option selected value='$fila[0]'>$nombre</option>";
//        } else {
//            echo "<option value='$fila[0]'>$nombre</option>";
//        }
//    }
//    echo "</select>";
//    mysqli_close($dbc);
//}
//
//function llenacombopersonal3($valorselecionado, $nombredelcombo) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    $sql = "                   SELECT tbl_persona.id_persona, CONVERT( CONCAT_WS( _utf8 ' ', tbl_persona.nombre, tbl_persona.apellido1, tbl_persona.apellido2, tbl_persona_grado_academico.abreviatura ) 
//USING utf8 ) AS nombrea1a2
//FROM tbl_persona
//LEFT JOIN tbl_persona_grado_academico ON tbl_persona.id_persona_grado_academico = tbl_persona_grado_academico.id_persona_grado_academico
//WHERE id_estado =1
//ORDER BY nombre, apellido1, apellido2";
//    $m = "";
//    $m .= "<select class='form-control' name=\"$nombredelcombo\" id=\"$nombredelcombo\">";
//    $resultado = mysqli_query($dbc, $sql);
//
//    while ($fila = mysqli_fetch_row($resultado)) {
//
//        $nombre = utf8_encode($fila[1]);
//
//        if ($fila[0] == $valorselecionado) {
//            $m .= "<option selected value='$fila[0]'>$nombre</option>";
//        } else {
//            $m .= "<option value='$fila[0]'>$nombre</option>";
//        }
//    }
//    $m .= "</select>";
//    mysqli_close($dbc);
//    return $m;
//}
//
//function cbmateriales($i) {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $m = "";
//    $s = "";
//
//    $m = "<select class='form-control' name='cbmateriales' id='cbmateriales'>";
//    $sql = "SELECT * FROM tlb_material order by id_material ";
//    $r = mysqli_query($dbc, $sql);
//    while ($fila = mysqli_fetch_row($r)) {
//        $f = $fila[0];
//        $material = $fila[1];
//        $s = "";
//        if ($f == $i) {
//            $s = "selected";
//        }
//        $m .= "<option value='$f' $s>$material</option>";
//    }
//    $m .= "</select>";
//    return $m;
//}
//
//function llenacombopersonal2($valorselecionado, $nombredelcombo, $categorias) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    if ($categorias != "") {
//        $categoria = explode(',', $categorias);
//        $where = "where tbl_persona.id_estado <> '2' and ";
//
//        for ($i = 0; $i < count($categoria); $i++) {
//            if ($i == 0) {
//                $where .= " (tbl_persona_categorias.id_persona_categoria = '" . $categoria[$i] . "'";
//            } else {
//                $where .= " or tbl_persona_categorias.id_persona_categoria = '" . $categoria[$i] . "'";
//            }
//        }
//        $where = $where . " )";
//    }
//
//    $sql = "SELECT DISTINCT tbl_persona.id_persona, nombre, apellido1, apellido2, tbl_persona_grado_academico.abreviatura
//            FROM tbl_persona
//            INNER JOIN tbl_persona_grado_academico ON tbl_persona.id_persona_grado_academico = tbl_persona_grado_academico.id_persona_grado_academico
//            INNER JOIN tbl_persona_categorias ON tbl_persona.id_persona = tbl_persona_categorias.id_persona
//            $where
//            ORDER BY nombre";
//    echo "<select class='form-control' name=\"$nombredelcombo\" id=\"$nombredelcombo\">";
//    $resultado = mysqli_query($dbc, $sql);
//
//    while ($fila = mysqli_fetch_row($resultado)) {
//
//        $nombre = utf8_encode($fila[1] . " " . $fila[2] . " " . $fila[3]);
//        if ($fila[4] != "") {
//            $nombre .= ", " . $fila[4];
//        }
//        if ($fila[0] == $valorselecionado) {
//            echo "<option selected value='$fila[0]'>$nombre</option>";
//        } else {
//            echo "<option value='$fila[0]'>$nombre</option>";
//        }
//    }
//    echo "</select>";
//    mysqli_close($dbc);
//}
//
//function llenacombohoras($valorselecionado, $nombredelcombo) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $valor = "";
//    $valor2 = "";
////$sql = "Select id_personal, nombre, apellido1, apellido2, grado_academico from tbl_personal where nombre!='sistema' && estado!='i'  order by nombre";
//
//    echo "<select name=\"$nombredelcombo\">";
////$resultado = mysqli_query($dbc, $sql);
//    $apm = "a.m.";
//    for ($i = 0; $i < 13; $i++) {
//
//
//        if ($i == 0) {
//            $valor = "";
//        } else {
//            if ($i < 10)
//                $valor = "0";
//            else
//                $valor = "";
//
//            $valor2 = $valor;
//            $valor = $valor . "$i" . ":00:00 " . $apm;
//            $valor2 = $valor2 . "$i" . ":30:00 " . $apm;
//        }
//        if ($valor == $valorselecionado) {
//            echo "<option selected value='$valor'>$valor</option>";
//        } else {
//            echo "<option value='$valor'>$valor</option>";
//        }
//
//        if ($i != 0) {
//
//            if ($valor2 == $valorselecionado) {
//                echo "<option selected value='$valor2'>$valor2</option>";
//            } else {
//                echo "<option value='$valor2'>$valor2</option>";
//            }
//        }
//        if ($i == 12 && $apm == "a.m.") {
//            $i = 0;
//            $apm = "p.m.";
//        }
//    }
//
//
//    while ($fila = mysqli_fetch_row($resultado)) {
//
//        $nombre = utf8_encode($fila[1] . " " . $fila[2] . " " . $fila[3]);
//        if ($fila[4] != "") {
//            $nombre .= ", " . $fila[4];
//        }
//        if ($fila[0] == $valorselecionado) {
//            echo "<option selected value='$fila[0]'>$nombre</option>";
//        } else {
//            echo "<option value='$fila[0]'>$nombre</option>";
//        }
//    }
//    echo "</select>";
//    mysqli_close($dbc);
//}
//
//function traepersona($codpersona) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($Fdbc, "utf8");
//    $sql = "Select 
//                       tbl_persona.nombre,
//            tbl_persona.apellido1,
//            tbl_persona.apellido2,
//            tbl_persona_grado_academico.abreviatura
//            From tbl_persona LEFT JOIN tbl_persona_grado_academico ON tbl_persona.id_persona_grado_academico = tbl_persona_grado_academico.id_persona_grado_academico
//            WHERE (tbl_persona.id_persona = $codpersona)";
//    $resultado = mysqli_query($Fdbc, $sql);
//    $fila = mysqli_fetch_row($resultado);
//    $nombrecompleto = $fila[0] . " " . $fila[1] . " " . $fila[2];
//    if ($fila[3] != "")
//        $nombrecompleto .= ", " . $fila[3];
//    return $nombrecompleto;
//}
//
//function traeinfoproyecto($idproyecto) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sql = "Select * From tbl_proyecto WHERE (id_proyecto = $idproyecto)";
//    $resultado = mysqli_query($Fdbc, $sql);
//    $proyecto = mysqli_fetch_row($resultado);
//
//    return $proyecto;
//}
//
//function traeinfopersona2($idpersona) {
//    $persona = "";
//    $email = 0;
//    $telefono = 0;
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($Fdbc, "utf8");
//    $sql = "Select 
//            tbl_persona_correo.correo
//            From tbl_persona_correo 
//            WHERE (tbl_persona_correo.id_persona = $idpersona) and tbl_persona_correo.descripcion = 'Principal'";
//    $resultado = mysqli_query($Fdbc, $sql);
//    $email = mysqli_fetch_row($resultado);
//
//    $sql = "Select 
//            tbl_persona_telefono.telefono,
//            tbl_telefono_tipo.descripcion
//            From tbl_persona_telefono LEFT JOIN tbl_telefono_tipo ON
//                    tbl_persona_telefono.id_telefono_tipo = tbl_telefono_tipo.id_telefono_tipo
//            WHERE (tbl_persona_telefono.id_persona = $idpersona) ";
//    $resultado = mysqli_query($Fdbc, $sql);
////$telefonos = mysqli_fetch_row($resultado);
//
//
//
//    if ($email != 0) {
//        $persona = "<small2>Email: " . $email[0] . "</small2>";
//    }
//    while ($telefono = mysqli_fetch_row($resultado)) {
//        if ($telefono[1] != "Casa") {
//            $persona = $persona . "<small2>" . $telefono[1] . ": " . $telefono[0] . "</small2>";
//        }
//    }
//
//    return $persona;
//}
//
////borrar
//function traeinfopersona($idpersona) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sql = "Select * From tbl_personal WHERE (id_personal = $idpersona)";
//    $resultado = mysqli_query($Fdbc, $sql);
//    $persona = mysqli_fetch_row($resultado);
//
//    return $persona;
//}
//
//function traeultimo($tabla, $campo) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sql = "Select $campo FROM $tabla ORDER BY $campo DESC Limit 1";
//    $resultado = mysqli_query($Fdbc, $sql);
//    $fila = mysqli_fetch_row($resultado);
//    $ultimo = $fila[0];
//    //echo $ultimo;
//    return $ultimo;
//}
//function traeultimo_where($tabla, $campo, $where) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sql = "Select $campo FROM $tabla WHERE $where ORDER BY $campo DESC Limit 1";
//    //echo $sql;
//    $resultado = mysqli_query($Fdbc, $sql);
//    $fila = mysqli_fetch_row($resultado);
//    $ultimo = $fila[0];
//    //echo $ultimo;
//    return $ultimo;
//}
//function traedato($tabla, $campo, $campoid, $id) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($Fdbc, "utf8");
//    $sql = "Select $campo FROM $tabla where $campoid = $id ";
//    $resultado = mysqli_query($Fdbc, $sql);
//    $dato = "";
//    if ($resultado->num_rows != 0) {
//        $fila = mysqli_fetch_row($resultado);
//        $dato = $fila[0];
//    }
//
//    return $dato;
//}
//
//function esImpar($numero) {
//    return $numero & 1; // 0 = es par, 1 = es impar
//}
//
//function validateDate($date, $format = 'Y-m-d H:i:s') {
//    $d = DateTime::createFromFormat($format, $date);
//    return $d && $d->format($format) == $date;
//}
//
//function quitar_tildes($cadena) {
//    $no_permitidas = array("?", "?", "?", "?", "?", "?", "?", "?", "?", "?", "?", "?", "?", "?", "?", "?", "Ù", "? ", "è", "ì", "ò", "ù", "?", "?", "â", "?", "î", "ô", "û", "Â", "Ê", "Î", "Ô", "Û", "?", "ö", "Ö", "ï", "ä", "?", "?", "??", "Ä", "Ë");
//    $permitidas = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "n", "N", "A", "E", "I", "O", "U", "a", "e", "i", "o", "u", "c", "C", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "u", "o", "O", "i", "a", "e", "U", "I", "A", "E");
//    $texto = str_replace($no_permitidas, $permitidas, $cadena);
//    return $texto;
//}
//
//function quitar_tildes2($cadena) {
//    $no_permitidas = array("?", "?", "?", "?", "?", "?", "?", "?", "?", "?", "?", "?");
//    $permitidas = array("&aacute;", "&eacute;", "&iacute;", "&oacute;", "&uacute;", "&Aacute;", "&Eacute;", "&Iacute;", "&Oacute;", "&acute;", "&ntilde;", "&Ntilde;");
//    $texto = str_replace($no_permitidas, $permitidas, $cadena);
//    return $texto;
//}
//
//function migaproyecto($idp) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sql = "Select nombre_proyecto, numero_vicerrectoria from tbl_proyecto where id_proyecto = $idp";
//    $resultado = mysqli_query($Fdbc, $sql);
//    $fila = mysqli_fetch_row($resultado);
//    $miga = utf8_encode($fila[1] . ", " . $fila[0]);
//    return $miga;
//}
//
//function migapersonal($idp) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sql = "Select tbl_persona.nombre, tbl_persona.apellido1, tbl_persona.apellido2,tbl_persona_grado_academico.abreviatura
//            FROM tbl_persona LEFT JOIN tbl_persona_grado_academico ON
//            tbl_persona_grado_academico.id_persona_grado_academico = tbl_persona.id_persona_grado_academico
//            WHERE id_persona = $idp";
//    $resultado = mysqli_query($Fdbc, $sql);
//    $fila = mysqli_fetch_row($resultado);
////    if ($fila[3] != "")
////        $fila[3] = ", ".$fila[3];
//    $miga = utf8_encode($fila[3] . " " . $fila[0] . " " . $fila[1] . " " . $fila[2]);
//    return $miga;
//}
//
//function ampliacionesvencidas($idproyecto, $tblvigencia) {
//    $vigenciaactual = "";
//
//    $fila = "";
//
//    while ($fila = mysqli_fetch_row($tblvigencia)) {
////echo $idproyecto . "   " . $fila[0] . "  " . $fila[1];
////echo "<br>";
//        if ($fila[0] == $idproyecto) {
//
//            $vigenciaactual = $fila[1];
//            break;
//        }
//    }
//    mysqli_data_seek($tblvigencia, 0);
//
//    if ($vigenciaactual != "" && $vigenciaactual < date("Y-m-d")) {
//        return true;
//    } else {
//        return false;
//    }
//}
//
//function cambiaestadoproyecto($idproyecto, $idestado) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//
//    $sql = "Update tbl_proyecto set estado='$idestado' where id_proyecto='$idproyecto'";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    if (mysqli_affected_rows($dbc) == 1) {
//        return true;
//    } else {
//        return false;
//    }
//
//    mysqli_close($dbc);
//}
//
//function compararFechas($primera, $segunda) {
//    $valoresPrimera = explode("/", $primera);
//    $valoresSegunda = explode("/", $segunda);
//
//    $diaPrimera = $valoresPrimera[0];
//    $mesPrimera = $valoresPrimera[1];
//    $anyoPrimera = $valoresPrimera[2];
//
//    $diaSegunda = $valoresSegunda[0];
//    $mesSegunda = $valoresSegunda[1];
//    $anyoSegunda = $valoresSegunda[2];
//
//    $diasPrimeraJuliano = gregoriantojd($mesPrimera, $diaPrimera, $anyoPrimera);
//    $diasSegundaJuliano = gregoriantojd($mesSegunda, $diaSegunda, $anyoSegunda);
//
//    if (!checkdate($mesPrimera, $diaPrimera, $anyoPrimera)) {
//// "La fecha ".$primera." no es válida";
//        return 0;
//    } elseif (!checkdate($mesSegunda, $diaSegunda, $anyoSegunda)) {
//// "La fecha ".$segunda." no es válida";
//        return 0;
//    } else {
//        return $diasPrimeraJuliano - $diasSegundaJuliano;
//    }
//}
//
////FUNCIONES ADICIONALES
//
//function traevigencia($idproyecto, &$vigenciade, &$vigenciahasta) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sql = "Select * FROM tbl_proyecto_vigencia WHERE (tbl_proyecto_vigencia.id_proyecto = $idproyecto) "
//            . "ORDER BY tbl_proyecto_vigencia.fechahasta DESC LIMIT 1 ";
//    $resultado = mysqli_query($Fdbc, $sql);
//    $fila = mysqli_fetch_row($resultado);
//
//    $vigenciade = convertirfecha($fila[5]);
//    $vigenciahasta = convertirfecha($fila[6]);
//}
//
//function correoInformes($mail) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($Fdbc, "utf8");
//    $mail->IsHTML(true);
//    $mail->CharSet = 'UTF-8';
//    $anno = date("Y");
//    $de = $anno . "-01-01";
//    $hasta = $anno . "-12-31";
//    $sql = "Select * FROM tbl_proyecto_informes LEFT JOIN
//            tbl_proyecto ON tbl_proyecto.id_proyecto = tbl_proyecto_informes.id_proyecto
//            where fecha_limite >= '$de' and fecha_limite <= '$hasta'  and
//            tbl_proyecto_informes.estado = 1
//            ORDER BY tbl_proyecto_informes.fecha_limite  ";
////echo $sql;
//    $resultado = mysqli_query($Fdbc, $sql);
//    $mail->PluginDir = "includes/";
//    $dir_correo = "";
//    $asunto = "";
//    $correo = "";
//    $id_informes0 = "";
//    $id_informes1 = "";
//    $cant = 1;
//    //para el envío en formato HTML 
//    $headers = "MIME-Version: 1.0\r\n";
//    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
//    while ($fila = mysqli_fetch_row($resultado)) {
//
//        if ($fila[2] != "" && $fila[2] < date("Y-m-d") && $fila[9] == 1) {
//            $dt1 = new DateTime(date("Y-m-d"));
//            $dt2 = new DateTime($fila[2]);
//            $diferencia = date_diff($dt2, $dt1);
//            $diferencia = 0 + $diferencia->format('%R%a');
//            //echo $diferencia;
//            if ($diferencia >= 5 && $fila[12] == 0) {
//
//                $correo .= $fila[17] . " <b>" . $fila[16] . "</b><br><br>" . $fila[3] . ": <u><b>Vencimiento " . convertirfecha($fila[2]) . "</b></u><br><br>";
////                if ($id_informes0 != "") {
////                    $id_informes0 .= ", ";
////                }
////                $id_informes0 .= $fila[0];
//            }
//        }//if
////        if ($fila[2] != "" && (date("Y-m-d") - $fila[2]) < 31 && $fila[9] == 1 && $fila[12] == 1) {
////            $correo .= "$fila[3] Vencimiento" . convertirfecha($fila[2]);
////            if ($id_informes1 != "") {
////                $id_informes1 .= ", ";
////            }
////            $id_informes1 .= $fila[0];
////        }
//
//        if ($correo != "" && $cant == 1) {
//            $cant += 1;
//            $correo = "Estimado(a) Investigador(a):<br><br>
//                       Se le recuerda que el proyecto a su cargo tiene próximo a vencer el siguiente compromiso:<br><br>" .
//                    $correo .
//                    "Si tiene alguna consulta o requiere información adicional, por favor no dude en contactarnos.<br><br>
//
//                        Atentamente,<br>
//                        
//<br />
//<br />
//<table style='border:0; padding:2px; vertical-align:middle; width:700px; height:80px; border:1px black solid;'>
//<tr>
//<td style='width:33%; border:0; padding:10px; vertical-align:middle;'>
//<a href='http://www.ucr.ac.cr/'>
//<img src='http://www.cia.ucr.ac.cr/wp-content/gallery/galeria_cia/firma-ucr.png' width='180' height='68' alt='Universidad de Costa Rica' /></a>
//</td>
//<td style='width:33%; border:0; padding:10px; vertical-align:middle;'>
//<a href='http://www.cia.ucr.ac.cr/'><img src='http://www.cia.ucr.ac.cr/wp-content/gallery/galeria_cia/firma-cia.png' alt='Unidad de la UCR' /></a>
//</td>
//<td style='width:33%; border:0; padding:10px; vertical-align:middle; color:white; background:#00c0f3; font-family:arial;font-size:14px;'>
//Karol Espinoza J.<br />
//Secretaría<br />
//(506) 2511-2050<br />
//<a href='karol.espinozajuarez@ucr.ac.cr' style='color:white;'>karol.espinozajuarez@ucr.ac.cr</a><br />
//</td>
//</tr>
//</table>
//<br />
//<br />
//";
//
//            $sql = "Select tbl_persona_correo.correo , tbl_persona_grado_academico.abreviatura, tbl_persona.nombre,tbl_persona.apellido1, tbl_persona.apellido2
//            FROM tbl_persona_correo LEFT JOIN
//            tbl_proyecto_personal ON tbl_persona_correo.id_persona = tbl_proyecto_personal.id_personal and tbl_proyecto_personal.cargo = 1 LEFT JOIN
//            tbl_persona ON tbl_persona.id_persona = tbl_proyecto_personal.id_personal and tbl_proyecto_personal.cargo = 1 LEFT JOIN
//            tbl_persona_grado_academico ON tbl_persona_grado_academico.id_persona_grado_academico = tbl_persona.id_persona_grado_academico
//            
//            WHERE 
//            tbl_proyecto_personal.id_proyecto = $fila[1];
//            ";
//            $resultado2 = mysqli_query($Fdbc, $sql);
//            $nombre = "";
//            while ($fila2 = mysqli_fetch_row($resultado2)) {
//                if ($dir_correo != "") {
//                    $dir_correo .= ", ";
//                }
//                $dir_correo .= $fila2[0];
//                $nombre = $fila2[1] . " " . $fila2[2] . " " . $fila2[3] . " " . $fila2[4];
//            }
//
//            $asunto = "Aviso de Informe: " . $fila[17] . " " . $fila[3] . " - " . $nombre;
//
//            $from = "cia@ucr.ac.cr";
//            $dir_correo = "vjuliorc@gmail.com";
//
//            $mail->IsSMTP();
//            $mail->Mailer = "smtp";
//            $mail->Host = "smtp.ucr.ac.cr";
//            $mail->Port = "25"; // 8025, 587 and 25 can also be used. Use Port 465 for SSL.
//            $mail->SMTPAuth = true;
//            //$mail->SMTPSecure = 'tls';
//            $mail->Username = "cia";
//            $mail->Password = "criterio.2014.UPE";
//
//            $mail->setFrom('cia@ucr.ac.cr', 'CIA');
//
//            /* Add a recipient. */
//            $mail->addAddress('vjuliorc@gmail.com', 'Julio');
//            $mail->addAddress('karol.espinozajuarez@ucr.ac.cr', 'Karol Espinoza');
//
//            /* Set the subject. */
//            $mail->Subject = $asunto;
//
//            /* Set the mail message body. */
//            $mail->Body = $correo;
//
//            /* Finally send the mail. */
//            //$mail->send();
//            if (!$mail->Send()) {
//                echo 'Message was not sent.';
//                echo 'Mailer error: ' . $mail->ErrorInfo;
//                exit;
//            } else {
//                echo 'Mensaje Enviado.';
//            }
//        }
//    }//while  
//}
//
////fin 
//
//function traeinformes($idproyecto, &$informes) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($Fdbc, "utf8");
//    $sql = "Select * FROM tbl_proyecto_informes 
//            LEFT JOIN tbl_proyecto_informe_estado ON tbl_proyecto_informes.estado = tbl_proyecto_informe_estado.id_estado
//            WHERE (tbl_proyecto_informes.id_proyecto = $idproyecto) 
//            ORDER BY tbl_proyecto_informes.fecha_limite  ";
//    $resultado = mysqli_query($Fdbc, $sql);
//
////
////    $mail->PluginDir = "includes/";
////
////    $dir_correo = "";
////    $asunto = "";
////    $correo = "";
//    $id_informes0 = "";
//    $id_informes1 = "";
//    //para el envío en formato HTML 
////    $headers = "MIME-Version: 1.0\r\n";
////    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
//    $estado = "";
//    while ($fila = mysqli_fetch_row($resultado)) {
//        $estado .= $estado . $fila[3] . "<br>" . $fila[16] . "<br><br>";
//        if ($fila[2] != "" && $fila[2] < date("Y-m-d") && $fila[9] == 1) {
//            $informes = $informes . $fila[3] . "<br>";
//
//            $dt1 = new DateTime(date("Y-m-d"));
//            $dt2 = new DateTime($fila[2]);
//            $diferencia = date_diff($dt2, $dt1);
//            $diferencia = 0 + $diferencia->format('%R%a');
//
//            if ($diferencia >= 15 && $fila[12] == 0) {
////                $correo .= "Está vencido " . $fila[3] . " Fecha límite: " . convertirfecha($fila[2]);
//                if ($id_informes0 != "") {
//                    $id_informes0 .= ", ";
//                }
//                $id_informes0 .= $fila[0];
//            }
//        }
//
//
//
//        $datetime1 = new DateTime(date("Y-m-d"));
//        $datetime2 = new DateTime($fila[2]);
//        $intervalo = $datetime1->diff($datetime2);
//
//        if ($fila[2] != "" && $intervalo->days < 31 && $fila[9] == 1 && $fila[12] == 1) {
//            //$correo .= "Vence en 30 días: " . $fila[3] . " Fecha límite: " . convertirfecha($fila[2]);
//            if ($id_informes1 != "") {
//                $id_informes1 .= ", ";
//            }
//            $id_informes1 .= $fila[0];
//        }
//    }
//    if (strlen($informes) > 0) {
//        $informes = $informes . "Sin Cumplir";
//    } else {
//        $informes = "Al día <br><br>" . $estado;
//    }
//}
//
//function formatearLugar($fila1, $fila2, $fila3) {
//    if (substr($fila3, 0, 10) == "Poscosecha") {
//        if (is_numeric($fila1))
//            $fila1 = $fila1 / 10;
//        $temp = $fila3;
//        $fila3 = $fila2;
//        $fila2 = $fila1;
//        $fila1 = $temp;
//
//        $fila2 = ', Piso ' . $fila2;
//
//        if (is_numeric($fila3))
//            $fila3 = ', Oficina ' . $fila3;
//    } else {
//        if ($fila1 == '0')
//            $fila1 = 'Afuera';
//        else
//            $fila1 = 'Piso ' . $fila1;
//        if (is_numeric($fila2))
//            $fila2 = 'Oficina ' . $fila2;
//        if ($fila2 != '')
//            $fila2 = ', ' . $fila2;
//        if ($fila3 != '')
//            $fila3 = ', ' . $fila3;
//    }
//    $lugar = $fila1 . $fila2 . $fila3;
//
//    return $lugar;
//}
//
//function tabla($nombredetabla, $datos) {
//    $dibujatabla = ' <div class="box">
//                    <div class="box-header">
//                        <h1>' . $nombredetabla . '</h1>
//                    </div>
//                    <div class="dataTables_wrapper">
//                        <table class="datatable">
//                            <thead>
//                                <tr>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Fecha</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 7%;">Salida</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 7%;">Regreso</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Destino</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Desayuno</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Almuerzo</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Cena</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Hopedaje</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Transporte</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Total</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 6%;">Eliminar</th>
//
//                                </tr>
//                            </thead>
//
//                            <tbody>';
//
//    $cantidad = 0;
//    $destinoL = "";
//    while ($fila = mysqli_fetch_row($datos)) {
//        $cantidad = $cantidad + 1;
//
////destino
//        $xdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//        mysqli_set_charset($xdbc, "utf8");
//        $sqlL = " 
//        Select
//            nombre, padre
//        from tbl_lugares 
//        where id_lugar = $fila[5];
//        ";
//        $resultadoL = mysqli_query($xdbc, $sqlL);
//        $lugarL = mysqli_fetch_row($resultadoL);
//        $destino = $lugarL[0];
//
//        if ($lugarL[1] != 0) {
//            $sqlL = " 
//                    Select
//                        nombre, padre
//                        from tbl_lugares 
//                        where id_lugar =  $lugarL[1];
//                        ";
//            $resultadoL = mysqli_query($xdbc, $sqlL);
//            $lugarL = mysqli_fetch_row($resultadoL);
//            $destino = $lugarL[0] . ", " . $destino;
//        }
//        if ($lugarL[1] != 0) {
//            $sqlL = " 
//                    Select
//                        nombre, padre
//                        from tbl_lugares 
//                        where id_lugar =  $lugarL[1];
//                        ";
//            $resultadoL = mysqli_query($xdbc, $sqlL);
//            $lugarL = mysqli_fetch_row($resultadoL);
//            $destino = $lugarL[0] . ", " . $destino;
//        }
//
//
//
//
//        if ($parImpar = false) {
//            $parImpar = true;
//            $dibujatabla .= "<tr class=\'odd\'>";
//        } else {
//            $parImpar = false;
//            $dibujatabla .= "<tr class=\'even\'>";
//        }
//        $total = $fila[11] + $fila[10] + $fila[9] + $fila[8] + $fila[7];
//        $fila[2] = convertirfecha($fila[2]);
//        $dibujatabla .= "<td>  $fila[2]  </td> 
//                         <td>  $fila[3]  </td>
//                         <td>  $fila[4]  </td> 
//                         <td>   $destino  </td> 
//                         <td>  $fila[7]  </td>
//                         <td>  $fila[8]  </td>
//                         <td>  $fila[9]  </td>    
//                         <td>  $fila[10]  </td>    
//                         <td>  $fila[11]  </td>    
//                         <td>  $total  </td>    
//                         <td>  <a onClick=\"borrar($fila[1],$fila[0])\" class='button plain'>Borrar</a> </td>                             
//                         </tr> ";
//    } //while
//
//    $dibujatabla .= '</tbody>
//                        </table> 
//                    </div> <!-- class=dataTables_wrapper -->
//                </div> <!-- class=box -->';
//
//    echo $dibujatabla;
//}
//
//function traecorrespondencia($tipo) {
//    $fechade = date('Y') . "-1-1";
//    $fechahasta = date('Y') . "-12-31";
//    $oficio = "";
//
//    $sql = "Select * from tbl_consecutivo_correspondencia where (fecha > '" . $fechade . "') AND (fecha < '" . $fechahasta . "') and tbl_consecutivo_correspondencia.tipo = $tipo ORDER BY oficio DESC";
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $oficios = mysqli_query($Fdbc, $sql);
//
//    if (mysqli_num_rows($oficios) == 0)
//        $oficio = "0";
//    else {
//        $oficios = mysqli_fetch_array($oficios);
//        $oficio = $oficios[4];
//    }
//
//    if ($oficio < 9)
//        $ceros = "00";
//    else if ($oficio > 8 && $oficio < 99)
//        $ceros = "0";
//    else
//        $ceros = "";
//
//    if ($tipo == '1' || $tipo == '4' || $tipo == '5')
//        $oficio = "CIA-" . $ceros . ($oficio + 1) . "-" . date('Y');
//    else if ($tipo == '2')
//        $oficio = "MEMORANDUM-" . ($oficio + 1) . "-" . date('Y');
//    else if ($tipo == '3')
//        $oficio = "CIA-CC-" . $ceros . ($oficio + 1) . "-" . date('Y');
//
//    return $oficio;
//}
//
//function utf8ize($d) {
//    if (is_array($d)) {
//        foreach ($d as $k => $v) {
//            $d[$k] = utf8ize($v);
//        }
//    } else if (is_string($d)) {
//        return utf8_encode($d);
//    }
//    return $d;
//}
//
//function quitarCIA00($codigo) {
//    $CIA = "CIA ";
//    if (strlen($codigo) > 4)
//        $pos = strpos($codigo, $CIA);
//    else
//        $pos = false;
//    $resp = "";
//
//    if ($pos == 1) {
//
//        $i = 5;
//
//        $j = strlen($codigo);
//
//        $continuar = true;
//
//        while ($i < $j && $continuar) {
//
//            if (substr($codigo, $i, 1) != "0") {
//                $continuar = false;
//            } else
//                $i = $i + 1;
//        }
//
//        while ($i < $j) {
//            $resp = $resp . substr($codigo, $i, 1);
//
//            $i = $i + 1;
//        }
//
//        $codigo = "'" . $resp;
//    }
//    return $codigo;
//}
//
//function sinCerosAlInicio($codigo) {
//    $ceros = str_split($codigo);
//    $sinceros = "";
////if ($ceros[0] == "0") {
//
//    $i = 0;
//    $j = count($ceros);
//
//    while ($i < $j && $ceros[$i] == "0") {
//        $i = $i + 1;
//    }
//
//    while ($i < $j) {
//
//        $sinceros = "$sinceros" . "$ceros[$i]";
//        $i = $i + 1;
//    }
//
//    return $sinceros;
////}
//}
//
//function paisselects(&$paises_arr, &$provincias_arr, &$cantones_arr, &$distritos_arr, &$barrios_arr) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sqlpa = "SELECT 
//                                                *
//                                            FROM tbm_dirpais where id_pais > 0
//                                            ORDER BY id_pais "
//    ;
//    $sqlp = "SELECT 
//                                                tbm_dirprovincia.id_provincia, tbm_dirprovincia.provincia, tbm_dirpais.pais
//                                            FROM tbm_dirprovincia LEFT JOIN tbm_dirpais on tbm_dirprovincia.id_pais = tbm_dirpais.id_pais
//                                            ORDER BY id_provincia "
//    ;
//    $sqlc = "SELECT 
//                                                tbm_dircanton.id_canton, tbm_dircanton.canton, tbm_dirprovincia.provincia, tbm_dirpais.pais
//                                            FROM tbm_dircanton LEFT JOIN 
//                                            tbm_dirprovincia on tbm_dircanton.id_provincia = tbm_dirprovincia.id_provincia LEFT JOIN
//                                            tbm_dirpais on tbm_dirpais.id_pais = tbm_dirprovincia.id_pais
//                                            
//                                            ORDER BY id_canton "
//    ;
//    $sqld = "SELECT 
//                                                tbm_dirdistrito.id_distrito, tbm_dirdistrito.distrito, tbm_dircanton.canton, tbm_dirprovincia.provincia, tbm_dirpais.pais
//                                            FROM tbm_dirdistrito LEFT JOIN 
//                                            tbm_dircanton on tbm_dirdistrito.id_canton = tbm_dircanton.id_canton LEFT JOIN 
//                                            tbm_dirprovincia on tbm_dircanton.id_provincia = tbm_dirprovincia.id_provincia LEFT JOIN
//                                            tbm_dirpais on tbm_dirpais.id_pais = tbm_dirprovincia.id_pais                                            
//                                            ORDER BY id_distrito "
//    ;
//    $sqlb = "SELECT 
//                                                tbm_dirbarrio.id_barrio, tbm_dirbarrio.barrio, tbm_dirdistrito.distrito, tbm_dircanton.canton, tbm_dirprovincia.provincia, tbm_dirpais.pais
//                                            FROM tbm_dirbarrio LEFT JOIN 
//                                            tbm_dirdistrito on tbm_dirbarrio.id_distrito = tbm_dirdistrito.id_distrito LEFT JOIN
//                                            tbm_dircanton on tbm_dirdistrito.id_canton = tbm_dircanton.id_canton LEFT JOIN
//                                            tbm_dirprovincia on tbm_dircanton.id_provincia = tbm_dirprovincia.id_provincia LEFT JOIN
//                                            tbm_dirpais on tbm_dirpais.id_pais = tbm_dirprovincia.id_pais                                                                                        
//                                            ORDER BY id_barrio "
//    ;
//    mysqli_set_charset($Fdbc, "utf8");
//    $paises = mysqli_query($Fdbc, $sqlpa);
//    $paises_arr = mysqli_fetch_all($paises);
//    $provincias = mysqli_query($Fdbc, $sqlp);
////$provincias_arr = mysqli_fetch_all($provincias );
//    $cantones = mysqli_query($Fdbc, $sqlc);
//    $distritos = mysqli_query($Fdbc, $sqld);
//    $barrios = mysqli_query($Fdbc, $sqlb);
//    $provincias_arr = array();
//    $cantones_arr = array();
//    $distritos_arr = array();
//    $barrios_arr = array();
//    $cadena = "";
//    $i = 0;
//    $j = 0;
//    while ($fila = mysqli_fetch_row($provincias)) {
//        $provincias_arr[$i][$j] = $fila[1];
//        $j = $j + 1;
//        $provincias_arr[$i][$j] = $fila[2];
//        $i = $i + 1;
//        $j = 0;
//    }
//    $i = 0;
//    $j = 0;
//    while ($fila = mysqli_fetch_row($cantones)) {
//        $cantones_arr[$i][$j] = $fila[1];
//        $j = $j + 1;
//        $cantones_arr[$i][$j] = $fila[2];
//        $j = $j + 1;
//        $cantones_arr[$i][$j] = $fila[3];
//        $i = $i + 1;
//        $j = 0;
//    }
//    $i = 0;
//    $j = 0;
//    while ($fila = mysqli_fetch_row($distritos)) {
//        $distritos_arr[$i][$j] = $fila[1];
//        $j = $j + 1;
//        $distritos_arr[$i][$j] = $fila[2];
//        $j = $j + 1;
//        $distritos_arr[$i][$j] = $fila[3];
//        $j = $j + 1;
//        $distritos_arr[$i][$j] = $fila[4];
//
//        $i = $i + 1;
//        $j = 0;
//    }
//    $i = 0;
//    $j = 0;
//    while ($fila = mysqli_fetch_row($barrios)) {
//        $barrios_arr[$i][$j] = $fila[1];
//        $j = $j + 1;
//        $barrios_arr[$i][$j] = $fila[2];
//        $j = $j + 1;
//        $barrios_arr[$i][$j] = $fila[3];
//        $j = $j + 1;
//        $barrios_arr[$i][$j] = $fila[4];
//        $j = $j + 1;
//        $barrios_arr[$i][$j] = $fila[5];
//
//        $i = $i + 1;
//        $j = 0;
//    }
//}
//
//function importarEtiquetas($solOrigen, $idsolNueva, $oficial, $idlaboratorio, $agno) {
//
//
//
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $t = false;
//
//    $idlabmax = 0;
//    //$tipodesol1 =  strpos($solOrigen , "T");
//    //$tipodesol2 = strpos($solOrigen , "T");    
////    $x = strpos($solOrigen , "t");
////    echo "--".$x."/";
//    if (strpos($solOrigen, "T") !== false || strpos($solOrigen, "t") !== false) {
////        $solOrigen = substr($solOrigen, 1);
////        $t = true;
////        $sqle = "SELECT * FROM tbm_solicitud_muestras where id_solicitud =  (select id_solicitud from tbm_solicitud where numero = '$solOrigen') order by id_muestras";
////        $etiqs = mysqli_query($dbc, $sqle);
//    } else {
//        if ($oficial == "o") {
//            $sqlL = "SELECT MAX( idlab ) 
//                    FROM tbm_solicitud_muestras
//                    LEFT JOIN tbm_solicitud ON tbm_solicitud.id_solicitud = tbm_solicitud_muestras.id_solicitud
//                    WHERE tbm_solicitud.id_laboratorio = $idlaboratorio and tbm_solicitud_muestras.agno = $agno";
//            $resultadoidlabmax = mysqli_query($dbc, $sqlL);
//            $fila = mysqli_fetch_row($resultadoidlabmax);
//            $idlabmax = $fila[0];
//        }
//
//        $sqle = "SELECT * FROM tbm_solicitud_muestras where id_solicitud =  (select id_solicitud from tbm_solicitud where numero = '$solOrigen') order by id_muestras";
//        $etiqs = mysqli_query($dbc, $sqle);
//    }
//
//
//    $idlab = "";
//    $etiqueta = "";
//    //$analisis = "";
//    //$id_solicitud = "";
//    //$precio = "";
////    $idtemporal = 0;
//    $estado = 1;
//    echo $sqle;
//    //$i = 0;
//    while ($row = mysqli_fetch_array($etiqs)) {
//        $idlab = $row[2];
//        $etiqueta = $row[3];
//        //$analisis = $row[3];
//        //$id_solicitud = $row[4];
//        //$precio = $row[5];
//        if (!$t) {
//            // $idtemporal = $row[6];
//            $estado = $row[8];
//        }
//
//        if ($oficial == "o") {
//            $idlabmax += 1;
//            $q = "INSERT INTO `tbm_solicitud_muestras`(`agno`, `idlab`, `etiqueta`,`analisis`, `id_solicitud`, `id_temporal`,`estado`) 
//                  VALUES ( $agno, $idlabmax, '$etiqueta', '',  $idsolNueva, 0,$estado)";
//        } else {
//            $q = "INSERT INTO `tbm_solicitud_muestrastemp`(`agno`, `idlab`, `etiqueta`,`analisis`, `id_solicitud`, id_cultivo) 
//                  VALUES ($agno, 0, '$etiqueta', '',  $idsolNueva,0)";
//        }
//        $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//
//        //$i = $i + 1;
//    }
//}
//
//function solicitudAnalisis($oficial, $idsolicitud, $idu, $cadenainicial, &$preciototal, $br) {
//    $dbcma = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sqlma = "";
//
//    if ($oficial == "o") {
//
//        $sqlma = "Select
//            tbm_solicitud_muestras_analisis.id_muestras,
//            tbm_producto_precio.id_producto_precio,
//            tbm_producto.id_producto,
//            tbm_producto.siglas,
//            tbm_solicitud_muestras_analisis.precio,
//            tbm_solicitud_muestras_analisis.id_solicitud_muestras_analisis,
//            tbm_solicitud_muestras.descuento,
//            tbm_producto.dias_entrega,
//            tbm_solicitud_muestras.analisis,
//            tbm_solicitud_muestras.id_temporal
//        from tbm_solicitud_muestras_analisis LEFT JOIN
//        tbm_producto_precio ON tbm_producto_precio.id_producto_precio = tbm_solicitud_muestras_analisis.id_producto_precio LEFT JOIN
//        tbm_producto ON tbm_producto_precio.id_producto = tbm_producto.id_producto LEFT JOIN
//        tbm_solicitud_muestras ON tbm_solicitud_muestras.id_muestras = tbm_solicitud_muestras_analisis.id_muestras
//        where tbm_solicitud_muestras.id_solicitud = $idsolicitud
//         ORDER BY tbm_solicitud_muestras_analisis.id_muestras , tbm_producto.posicion       ";
//    } else {
//
//
//        $sqlma = "Select
//            tbm_solicitud_muestrastemp_analisis.id_muestras,
//            tbm_producto_precio.id_producto_precio,
//            tbm_producto.id_producto,
//            tbm_producto.siglas,
//            tbm_solicitud_muestrastemp_analisis.precio,
//            tbm_solicitud_muestrastemp_analisis.id_solicitud_muestras_analisis,
//            tbm_solicitud_muestrastemp.descuento,
//            tbm_producto.dias_entrega
//        from tbm_solicitud_muestrastemp_analisis LEFT JOIN
//        tbm_producto_precio ON tbm_producto_precio.id_producto_precio = tbm_solicitud_muestrastemp_analisis.id_producto_precio LEFT JOIN
//        tbm_producto ON tbm_producto_precio.id_producto = tbm_producto.id_producto LEFT JOIN
//        tbm_solicitud_muestrastemp ON tbm_solicitud_muestrastemp.id_muestras = tbm_solicitud_muestrastemp_analisis.id_muestras
//        where tbm_solicitud_muestrastemp.id_solicitud = $idsolicitud
//        ORDER BY tbm_solicitud_muestrastemp_analisis.id_muestras, tbm_producto.posicion        ";
//    }
//    mysqli_set_charset($dbcma, "utf8");
//    $rr = mysqli_query($dbcma, $sqlma) or trigger_error("Query: $sqlma\n<br />MySQL Error: " . mysqli_error($dbcma));
//    $muestraactual = -1;
//    $muestrasarr = array();
//    $i = 0;
//    $cantidad = 0;
//    while ($filama = mysqli_fetch_row($rr)) {
//        //solicitudpaso2.php?d=1&op=1&t=3
//        $cantidad += 1;
//        if ($muestraactual != -1 && $filama[0] != $muestraactual) {
//
//            $i = $i + 1;
//        }
//        $muestrasarr[$i][0] = $filama[0];
//        $preciototal += $filama[4];
//        if ($filama[6] > 0) {
//            $filama[4] = $filama[4] - ($filama[4] * $filama[6] / 100);
//        }
//        $filama[4] = number_format($filama[4], 0, ',', '.');
//        //$filama[6] = number_format($filama[6], 0, ',', '.')." %";
//        if ($br) {
//            if (isset($muestrasarr[$i][1])) {
//                //$muestrasarr[$i][1] = $muestrasarr[$i][1] . "<br>" . $filama[3] . " (" . $filama[4] . ");"."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][1] = $muestrasarr[$i][1] . "<a  onClick=\"quitaranalisis('$cadenainicial&id=$idsolicitud&idu=$idu&ta=$oficial&qa=$filama[5]')\" >" . $filama[3] . "</a><br>" . " "; //."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][2] = $muestrasarr[$i][2] . "<br>" . $filama[4]; // + $filama[4];
//            } else {
//                //$muestrasarr[$i][1] = $filama[3] . " (" . $filama[4] . ");"."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][1] = "<a  onClick=\"quitaranalisis('$cadenainicial&id=$idsolicitud&idu=$idu&ta=$oficial&qa=$filama[5]')\"  >" . $filama[3] . "</a><br>" . " "; // ."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][2] = $filama[4];
//            }
//        } else {
//            if (isset($muestrasarr[$i][1])) {
//                //$muestrasarr[$i][1] = $muestrasarr[$i][1] . "<br>" . $filama[3] . " (" . $filama[4] . ");"."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][1] = $muestrasarr[$i][1] . $filama[3] . ","; //."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][2] = $muestrasarr[$i][2] . "," . $filama[4]; // + $filama[4];
//            } else {
//                //$muestrasarr[$i][1] = $filama[3] . " (" . $filama[4] . ");"."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][1] = $filama[3] . ", "; // ."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][2] = $filama[4];
//            }
//        }
//        $muestrasarr[$i][3] = $filama[7];
//        $muestraactual = $filama[0];
//    }
//
//
//    return $muestrasarr;
//}
//
//function solicitudAnalisisN($oficial, $idsolicitud, $idu, &$preciototal, $br, $lab, $moneda) {
//    $dbcma = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sqlma = "";
//    $iCantidadAnalisis = 0;
//
//    $hayfactura = "";
//
//    $iconteo = 11;
//    if ($oficial == "o") {
//
//        $hayfactura = buscarFacturadeSolicitud($idsolicitud);
//
//        $sqlma = "Select
//            tbm_solicitud_muestras_analisis.id_muestras,
//            tbm_producto_precio.id_producto_precio,
//            tbm_producto.id_producto,
//            tbm_producto.siglas,
//            tbm_solicitud_muestras_analisis.precio,
//            tbm_solicitud_muestras_analisis.id_solicitud_muestras_analisis,
//            tbm_solicitud_muestras.descuento,
//            tbm_producto.dias_entrega,
//            tbm_solicitud_muestras.analisis,
//            tbm_solicitud_muestras.id_temporal,
//            tbm_solicitud_muestras_analisis.cantidad,
//            tbm_producto.conteo,
//            tbm_producto.posicion
//        from tbm_solicitud_muestras_analisis LEFT JOIN
//        tbm_producto_precio ON tbm_producto_precio.id_producto_precio = tbm_solicitud_muestras_analisis.id_producto_precio LEFT JOIN
//        tbm_producto ON tbm_producto_precio.id_producto = tbm_producto.id_producto LEFT JOIN
//        tbm_solicitud_muestras ON tbm_solicitud_muestras.id_muestras = tbm_solicitud_muestras_analisis.id_muestras
//        where tbm_solicitud_muestras.id_solicitud = $idsolicitud
//         ORDER BY tbm_solicitud_muestras_analisis.id_muestras, tbm_producto.posicion, tbm_producto.siglas       ";
//
//        $iCantidadAnalisis = 10;
//    } else {
//        $iconteo = 9;
//
//        $sqlma = "Select
//            tbm_solicitud_muestrastemp_analisis.id_muestras,
//            tbm_producto_precio.id_producto_precio,
//            tbm_producto.id_producto,
//            tbm_producto.siglas,
//            tbm_solicitud_muestrastemp_analisis.precio,
//            tbm_solicitud_muestrastemp_analisis.id_solicitud_muestras_analisis,
//            tbm_solicitud_muestrastemp.descuento,
//            tbm_producto.dias_entrega,
//            tbm_solicitud_muestrastemp_analisis.cantidad,
//            tbm_producto.conteo
//        from tbm_solicitud_muestrastemp_analisis LEFT JOIN
//        tbm_producto_precio ON tbm_producto_precio.id_producto_precio = tbm_solicitud_muestrastemp_analisis.id_producto_precio LEFT JOIN
//        tbm_producto ON tbm_producto_precio.id_producto = tbm_producto.id_producto LEFT JOIN
//        tbm_solicitud_muestrastemp ON tbm_solicitud_muestrastemp.id_muestras = tbm_solicitud_muestrastemp_analisis.id_muestras
//        where tbm_solicitud_muestrastemp.id_solicitud = $idsolicitud
//        ORDER BY tbm_solicitud_muestrastemp_analisis.id_muestras,  tbm_producto.posicion, tbm_producto.siglas       ";
//        $iCantidadAnalisis = 8;
//    }
//    mysqli_set_charset($dbcma, "utf8");
//    $rr = mysqli_query($dbcma, $sqlma) or trigger_error("Query: $sqlma\n<br />MySQL Error: " . mysqli_error($dbcma));
//    $muestraactual = -1;
//    $muestrasarr = array();
//    $i = 0;
//    $cantidad = 0;
//    $CantAnalisis = 0;
//    $textCantAnalisis = "";
//    while ($filama = mysqli_fetch_row($rr)) {
//        //solicitudpaso2.php?d=1&op=1&t=3
//        $cantidad += 1;
//        if ($muestraactual != -1 && $filama[0] != $muestraactual) {
//
//            $i = $i + 1;
//        }
//
//
//        //Cantidad de analisis aplica solo para los analisis de microbiología abajo indicados, sino siempre valdrá 1
//        $CantAnalisis = $filama[$iCantidadAnalisis];
//
//        $muestrasarr[$i][0] = $filama[0];
//        $preciototal += $filama[4] * $CantAnalisis;
//
//        if ($filama[6] > 0) {
//            $filama[4] = $filama[4] - ($filama[4] * $filama[6] / 100);
//        }
//        $filama[4] = number_format($filama[4], 0, ',', '.');
//        $textCantAnalisis = "";
//        //if ($filama[11] == 1 || $filama[2] == 162 || $filama[2] == 166 || $filama[2] == 170 ) {
//        if ($filama[$iconteo] == 1) {
//            if ($hayfactura == "") {
//
//                $plus = "<img src='../images/icons/png/24x24/Add.png'  height='10' width='10'>";
//                $subtract = "<img src='../images/icons/png/24x24/Remove.png' height='10' width='10'>";
//                $plus = "<a onClick=\"sumarAnalisis($idsolicitud,'$oficial',$idu,$lab,$moneda,'$filama[5]','sumar',cultivo.value,prefijo.value,etiqueta.value,sufijo.value)\"  class='button button-plain-link'   title='Incrementar...'>$plus</a>";
//                $subtract = "<a onClick=\"sumarAnalisis($idsolicitud,'$oficial',$idu,$lab,$moneda,'$filama[5]','restar',cultivo.value,prefijo.value,etiqueta.value,sufijo.value)\"  class='button button-plain-link'   title='Reducir...'>$subtract</a>";
//
//                $textCantAnalisis = $subtract . $CantAnalisis . $plus;
//            } else {
//                $textCantAnalisis = " [$CantAnalisis]";
//            }
//        }
//
//
//        if ($br) {
//            if (isset($muestrasarr[$i][1])) {
//
//                $muestrasarr[$i][1] = $muestrasarr[$i][1] . "<a onClick=\"editarAnalisis($idsolicitud,'$oficial',$idu,$lab,$moneda,'$filama[0]',cultivo.value,prefijo.value,etiqueta.value,sufijo.value)\"  class='button button-plain-link'   title='Modificar Análisisa...'>" . $filama[3] . $textCantAnalisis . "</a> <br>";
//                $muestrasarr[$i][2] = $muestrasarr[$i][2] . "<a class='button button-plain-link fastbutton' >$filama[4]</a><br>";
//            } else {
//                $muestrasarr[$i][1] = "<a  onClick=\"editarAnalisis($idsolicitud,'$oficial',$idu,$lab,$moneda,'$filama[0]',cultivo.value,prefijo.value,etiqueta.value,sufijo.value)\" class='button button-plain-link fastbutton' title='Modificar Análisis...'  >" . $filama[3] . $textCantAnalisis . "</a><br>";
//                $muestrasarr[$i][2] = "<a class='button button-plain-link fastbutton' >$filama[4]</a><br>";
//            }
//        } else {
//            if (isset($muestrasarr[$i][1])) {
//                $muestrasarr[$i][1] = $muestrasarr[$i][1] . $filama[3] . $textCantAnalisis . ",";
//                $muestrasarr[$i][2] = $muestrasarr[$i][2] . "," . $filama[4];
//            } else {
//                $muestrasarr[$i][1] = $filama[3] . $textCantAnalisis . ", ";
//                $muestrasarr[$i][2] = $filama[4];
//            }
//        }
//        $muestrasarr[$i][3] = $filama[7];
//        $muestraactual = $filama[0];
//    }
//
//
//    return $muestrasarr;
//}
//
//function solicitudAnalisisACC($oficial, $idsolicitud, $idu, $cadenainicial, &$preciototal, $br) {
//    $dbcma = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    $sqlma = "";
//
//    if ($oficial == "o") {
//
//        $sqlma = "Select
//            tbm_solicitud_muestras_analisis.id_muestras,
//            tbm_producto_precio.id_producto_precio,
//            tbm_producto.id_producto,
//            tbm_producto.siglas,
//            tbm_solicitud_muestras_analisis.precio,
//            tbm_solicitud_muestras_analisis.id_solicitud_muestras_analisis,
//            tbm_solicitud_muestras.descuento,
//            tbm_producto.dias_entrega,
//            tbm_solicitud_muestras.analisis,
//            tbm_solicitud_muestras.id_temporal
//        from tbm_solicitud_muestras_analisis LEFT JOIN
//        tbm_producto_precio ON tbm_producto_precio.id_producto_precio = tbm_solicitud_muestras_analisis.id_producto_precio LEFT JOIN
//        tbm_producto ON tbm_producto_precio.id_producto = tbm_producto.id_producto LEFT JOIN
//        tbm_solicitud_muestras ON tbm_solicitud_muestras.id_muestras = tbm_solicitud_muestras_analisis.id_muestras
//        where tbm_solicitud_muestras.id_solicitud = $idsolicitud
//         ORDER BY tbm_solicitud_muestras_analisis.id_muestras       ";
//    }
//    mysqli_set_charset($dbcma, "utf8");
//    $rr = mysqli_query($dbcma, $sqlma) or trigger_error("Query: $sqlma\n<br />MySQL Error: " . mysqli_error($dbcma));
//    $muestraactual = -1;
//    $muestrasarr = "";
//    $i = 0;
//    $cantidad = 0;
//    while ($filama = mysqli_fetch_row($rr)) {
//        //solicitudpaso2.php?d=1&op=1&t=3
//        $cantidad += 1;
//        if ($muestraactual != -1 && $filama[0] != $muestraactual) {
//
//            $i = $i + 1;
//        }
//        $muestrasarr[$i][0] = $filama[0];
//        $preciototal += $filama[4];
//        if ($filama[6] > 0) {
//            $filama[4] = $filama[4] - ($filama[4] * $filama[6] / 100);
//        }
//        //$filama[4] = number_format($filama[4], 0, ',', '.');
//        //$filama[6] = number_format($filama[6], 0, ',', '.')." %";
//        $tt = $filama[4];
//        $filama[4] = cambiarpunto($filama[4], ",");
//        //echo "//" . $tt . "---" . $filama[4] . "<br>";
//        if ($br) {
//            if (isset($muestrasarr[$i][1])) {
//                //$muestrasarr[$i][1] = $muestrasarr[$i][1] . "<br>" . $filama[3] . " (" . $filama[4] . ");"."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][1] = $muestrasarr[$i][1] . "<a  onClick=\"quitaranalisis('$cadenainicial&id=$idsolicitud&idu=$idu&ta=$oficial&qa=$filama[5]')\" >" . $filama[3] . "</a><br>" . " "; //."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][2] += $filama[4]; // + $filama[4];
//            } else {
//                //$muestrasarr[$i][1] = $filama[3] . " (" . $filama[4] . ");"."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][1] = "<a  onClick=\"quitaranalisis('$cadenainicial&id=$idsolicitud&idu=$idu&ta=$oficial&qa=$filama[5]')\"  >" . $filama[3] . "</a><br>" . " "; // ."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][2] = $filama[4];
//            }
//        } else {
//            if (isset($muestrasarr[$i][1])) {
//                //$muestrasarr[$i][1] = $muestrasarr[$i][1] . "<br>" . $filama[3] . " (" . $filama[4] . ");"."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][1] = $muestrasarr[$i][1] . $filama[3] . ","; //."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][2] += $filama[4]; // + $filama[4];
//            } else {
//                //$muestrasarr[$i][1] = $filama[3] . " (" . $filama[4] . ");"."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][1] = $filama[3] . ", "; // ."<a onClick=\"\" >  <img src='../images/icons/png/24x24/Erase.png' alt='Editar' height='12' width='12'> </a>";
//                $muestrasarr[$i][2] = $filama[4];
//            }
//        }
//        $muestrasarr[$i][3] = $filama[7];
//        $muestraactual = $filama[0];
//    }
//
//
//    return $muestrasarr;
//}
//
//function buscaindexLugar($tabla, $columna1, $columnaPadre, $lugar) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $sqlLugar = "Select $columnaPadre from  $tabla 
//             WHERE '$lugar' = $columna1  ";
//    $sqlLugar = "Select * from $tabla where $columnaPadre in ( $sqlLugar) ";
//
//    $r = mysqli_query($dbc, $sqlLugar) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//    $resp = "";
//    $i = 0;
//    while ($fila = mysqli_fetch_row($r)) {
//
//        if ($fila[0] == $lugar) {
//            $resp = $i;
//        }
//        $i = $i + 1;
//    }
//
//    $resp = $resp + 1;
//
//    return $resp;
//}
//
//function clientecontactoinfo(&$telefonos, &$correos, $idcliente, $contacto, $equis, $idsol, $res, $oficial) {
//
//    $dbc2 = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc2, "utf8");
//    //TELEFONOS
//    $sqlclientecontactotelefonos = "SELECT 
//                                    tbm_cliente_contacto_telefono.id_contacto_telefono, 
//                                    tbl_telefono_tipo.descripcion, 
//                                    tbm_cliente_contacto_telefono.telefono, 
//                                    tbm_cliente_contacto_telefono.extension
//                                        FROM tbm_cliente_contacto_telefono Left JOIN tbl_telefono_tipo ON
//                                        tbm_cliente_contacto_telefono.id_telefono_tipo = tbl_telefono_tipo.id_telefono_tipo
//                                        WHERE tbm_cliente_contacto_telefono.id_contacto = $contacto";
//    $clientecontactotelefonos = mysqli_query($dbc2, $sqlclientecontactotelefonos);
//    while ($contactotel = mysqli_fetch_row($clientecontactotelefonos)) {
//        //$telefonos .= "$contactotel[1]" . ": " . "$contactotel[2] ";
//        if ($telefonos != "") {
//            $telefonos .= "<br>";
//        }
//
//        $telefonos .= $contactotel[2];
//        if ($contactotel[3] != "") {
//            $telefonos .= " [ " . $contactotel[3] . " ]";
//        }
//    }
//
//    //************************************************************************
//    //CORREOS
//    $sqlclientecontactocorreos = "SELECT * FROM tbm_cliente_contacto_correo WHERE tbm_cliente_contacto_correo.id_contacto = $contacto order by favorito desc";
//    $clientecontactocorreos = mysqli_query($dbc2, $sqlclientecontactocorreos);
//    while ($correocontacto = mysqli_fetch_row($clientecontactocorreos)) {
//        if ($correos != "") {
//            $correos .= "<br>";
//        }
//        if ($correocontacto[3] != "")
//            $correocontacto[3] = " (" . $correocontacto[3] . ") ";
//        $correos .= "<a href='mailto:$correocontacto[2]'>$correocontacto[2]" . "$correocontacto[3]" . "</a>";
//    }
//}
//
//function clientecontactoinfo2(&$telefonos, &$ext, &$correos, $contacto, $n, $idsol, $oficial, $idcliente, $res, $idsolicitudcontacto = 0) {
//
//    $dbc2 = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc2, "utf8");
//    $link = "";
//    if ($idsol != "") {
//        $idsolicitud = $idsol;
//        $idsol = "&idsol=$idsol";
//        $oficial = "&ta=$oficial";
//    } else {
//        $idsol = "";
//        $oficial = "";
//    }
//    if ($idsolicitudcontacto == 0) {
//        $idsolicitudcontacto = "";
//    } else {
//        $idsolicitudcontacto = "&idsolc=" . $idsolicitudcontacto;
//    }
//
//
//    if ($n == "nS") {
//        $link = "solicitudpaso1_contactos.php?d=1&op=1&t=2&ta=$oficial&id=$idsolicitud&idu=$idcliente&res=$res";
//    } else {
//        $link = "usuariocontactos.php?d=1&op=3&t=3&id=$idcliente$oficial$idsol";
//    }
//
//
//    //TELEFONOS
//    $sqlclientecontactotelefonos = "SELECT 
//                                    tbm_cliente_contacto_telefono.id_contacto_telefono, 
//                                    tbl_telefono_tipo.descripcion, 
//                                    tbm_cliente_contacto_telefono.telefono,
//                                    tbm_cliente_contacto_telefono.favorito,
//                                    tbm_cliente_contacto_telefono.extension
//                                        FROM tbm_cliente_contacto_telefono Left JOIN tbl_telefono_tipo ON
//                                        tbm_cliente_contacto_telefono.id_telefono_tipo = tbl_telefono_tipo.id_telefono_tipo
//                                        WHERE tbm_cliente_contacto_telefono.id_contacto = $contacto  and tbm_cliente_contacto_telefono.estado > 0
//                                    ORDER BY tbm_cliente_contacto_telefono.telefono";
//    $clientecontactotelefonos = mysqli_query($dbc2, $sqlclientecontactotelefonos);
//    if (mysqli_num_rows($clientecontactotelefonos) > 0) {
//        while ($contactotel = mysqli_fetch_row($clientecontactotelefonos)) {
//            $idtel = $contactotel[0];
//            if ($n == "n" || $n == "nS") {
//                if ($telefonos != "") {
//                    $telefonos .= "<br><br>";
//                }
//                if ($contactotel[4] != "") {
//                    $contactotel[2] = $contactotel[2] . " [ $contactotel[4] ]";
//                }
//
//                if ($contactotel[3] == 1) {
//                    //$telefonos .= "*";
//                    $contactotel[2] = "<a class='button button-plain-link fastbutton' href='$link&idtel=$contactotel[0]&acc=10&idc=$contacto$idsolicitudcontacto' title='Click para omitir en el reporte'><font size='3' color=blue >$contactotel[2]</font></a>";
//                } else {
//                    $contactotel[2] = "<a class='button button-plain-link fastbutton' href='$link&idtel=$contactotel[0]&acc=11&idc=$contacto$idsolicitudcontacto' title='Click para omitir en el reporte'><font size='3' color='#333333' >$contactotel[2]</font></a>";
//                }
//                $telefonos .= $contactotel[2];
//            } else if ($n == 1) {
//                if ($telefonos != "") {
//                    $telefonos .= "<br><br><br>";
//                }
//
//
//
//                $telefonos .= "<input  type='text' name='ctelefono$idtel' maxlength='20'  placeholder='Teléfono' value='$contactotel[2]'/><br>
//                    <input type='text' name='cextension$idtel' maxlength='10' placeholder='Extensión'  value='$contactotel[4]'/><br>";
//            }
//        }
//    }
//    //************************************************************************
//    //CORREOS
//    $sqlclientecontactocorreos = "SELECT * FROM tbm_cliente_contacto_correo WHERE tbm_cliente_contacto_correo.id_contacto = $contacto and tbm_cliente_contacto_correo.estado > 0 order by correo";
//    $clientecontactocorreos = mysqli_query($dbc2, $sqlclientecontactocorreos);
//    if (mysqli_num_rows($clientecontactocorreos) > 0) {
//        while ($correocontacto = mysqli_fetch_row($clientecontactocorreos)) {
//            $idemail = $correocontacto[0];
//            if ($n == "n" || $n == "nS") {
//                if ($correos != "") {
//                    $correos .= "<br><br>";
//                }
//                //si el acc es 10, es tipo contacto, y hay que desactivar
//                //si el acc es 11, es tipo contacto, y hay que activar
//                if ($correocontacto[4] == 1) {
//                    //$telefonos .= "*";
//                    $correocontacto[2] = "<a class='button button-plain-link fastbutton' href='$link&idcorreo=$correocontacto[0]&acc=10&idc=$contacto$idsolicitudcontacto' title='Click para omitir en el reporte'><font size='3' color=blue >$correocontacto[2]</font></a>";
//                } else {
//                    $correocontacto[2] = "<a class='button button-plain-link fastbutton' href='$link&idcorreo=$correocontacto[0]&acc=11&idc=$contacto$idsolicitudcontacto' title='Click para omitir en el reporte'><font size='3' color='#333333' >$correocontacto[2]</font></a>";
//                }
//                $correos .= $correocontacto[2];
//            } else if ($n == 1) {
//                if ($correos != "") {
//                    $correos .= "<br><br><br>";
//                }
//                $correos .= "<input  type='text' name='ccorreo$idemail' maxlength='50'  placeholder='Correo Electrónico' value='$correocontacto[2]'/>";
//            }
//        }
//    }
//    if ($n == 1) {
//        if ($telefonos != "") {
//            $telefonos .= "<br><br><br>";
//        }
//
//        $telefonos .= "<input  type='text' name='ctelefononuevo' maxlength='20' placeholder='Nuevo Teléfono' value=''/>
//                <input type='text' name='cextensionnueva' maxlength='10' placeholder='Extensión'  value=''/>";
//        if ($correos != "") {
//            $correos .= "<br><br><br>";
//        }
//
//        $correos .= "<input  type='text' name='ccorreonuevo' maxlength='50' placeholder='Nuevo Correo' value=''/>";
//    }
//}
//
//function clientecontactoinfo2PRINCIPAL(&$telefonos, &$ext, &$correos, $idu, $n, $idsol, $oficial, $res) {
//
//    $dbc2 = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc2, "utf8");
//    $chk = "";
//    $link = "";
//    if ($idsol != "") {
//        $idsolicitud = $idsol;
//        $idsol = "&idsol=$idsol";
//        $oficial = "&ta=$oficial";
//    } else {
//        $idsol = "";
//        $oficial = "";
//    }
//    if ($n == "nS") {
//        $link = "solicitudpaso1_contactos.php?d=1&op=1&t=2&ta=$oficial&id=$idsolicitud&idu=$idu&res=$res";
//    } else {
//        $link = "usuariocontactos.php?d=1&op=3&t=3&id=$idu$oficial$idsol";
//    }
//
//    //TELEFONOS
//    $sqlclientecontactotelefonos = "SELECT 
//                                    tbm_cliente_telefono.id_cliente_telefono, 
//                                    tbl_telefono_tipo.descripcion, 
//                                    tbm_cliente_telefono.telefono,
//                                    tbm_cliente_telefono.favorito,
//                                    tbm_cliente_telefono.extension
//                                        FROM tbm_cliente_telefono Left JOIN tbl_telefono_tipo ON
//                                        tbm_cliente_telefono.id_telefono_tipo = tbl_telefono_tipo.id_telefono_tipo
//                                        WHERE tbm_cliente_telefono.id_cliente = $idu";
//    $clientecontactotelefonos = mysqli_query($dbc2, $sqlclientecontactotelefonos);
//    while ($contactotel = mysqli_fetch_row($clientecontactotelefonos)) {
//        $idtel = $contactotel[0];
//        if ($n == "n" || $n == "nS") {
//            if ($telefonos != "") {
//                $telefonos .= "<br><br>";
//            }
//            if ($contactotel[4] != "") {
//                $contactotel[2] = $contactotel[2] . " [ $contactotel[4] ]";
//            }
//            if ($contactotel[3] == 1) {
//                //$telefonos .= "*";
//                $contactotel[2] = "<a class='button button-plain-link fastbutton' href='$link&idtel=$contactotel[0]&acc=20' title='Click para omitir en el reporte'><font size='3' color=blue >$contactotel[2]</font></a>";
//            } else {
//                $contactotel[2] = "<a class='button button-plain-link fastbutton' href='$link&idtel=$contactotel[0]&acc=21' title='Click para omitir en el reporte'><font size='3' color='#333333' >$contactotel[2]</font></a>";
//            }
//            $telefonos .= $contactotel[2];
//        } else if ($n == 1) {
//            if ($telefonos != "") {
//                $telefonos .= "<br><br><br>";
//            }
//
//            $telefonos .= "<input type='text' name='ctelefono$idtel' maxlength='20'  placeholder='Teléfono' value='$contactotel[2]'/>
//                    <input type='text'  name='cextension$idtel' maxlength='10' placeholder='Extensión'  value='$contactotel[4]'/> ";
//        }
//    }
//    //************************************************************************
//    //CORREOS
//    $sqlclientecontactocorreos = "SELECT * FROM tbm_cliente_correo WHERE tbm_cliente_correo.id_cliente = $idu order by favorito desc";
//    $clientecontactocorreos = mysqli_query($dbc2, $sqlclientecontactocorreos);
//    while ($correocontacto = mysqli_fetch_row($clientecontactocorreos)) {
//        $idemail = $correocontacto[0];
//        if ($n == "n" || $n == "nS") {
//            if ($correos != "") {
//                $correos .= "<br><br>";
//            }
//            if ($correocontacto[4] == 1) {
//                //$telefonos .= "*";
//                $correocontacto[2] = "<a class='button button-plain-link fastbutton' href='$link&idcorreo=$correocontacto[0]&acc=20' title='Click para omitir en el reporte'><font size='3' color=blue >$correocontacto[2]</font></a>";
//            } else {
//                $correocontacto[2] = "<a class='button button-plain-link fastbutton' href='$link&idcorreo=$correocontacto[0]&acc=21' title='Click para omitir en el reporte'><font size='3' color='#333333' >$correocontacto[2]</font></a>";
//            }
//            $correos .= $correocontacto[2];
//        } else if ($n == 1) {
//            if ($correos != "") {
//                $correos .= "<br><br><br>";
//            }
//            $correos .= "<input  type='text' name='ccorreo$idemail' maxlength='50'  placeholder='Correo Electrónico' value='$correocontacto[2]'/>";
//        }
//    }
//
//    if ($n == 1) {
//        if ($telefonos != "") {
//            $telefonos .= "<br><br><br>";
//        }
//        $telefonos .= "<input  type='text' name='ctelefononuevo' maxlength='20' placeholder='Nuevo Teléfono' value=''/>
//                <input type='text' name='cextensionnueva' maxlength='10' placeholder='Extensión'  value='$contactotel[4]'/>";
//        if ($correos != "") {
//            $correos .= "<br><br><br>";
//        }
//        $correos .= "<input  type='text' name='ccorreonuevo' maxlength='50' placeholder='Nuevo Correo' value=''/>";
//    }
//}
//
//function agregarItemFactura($cantidad, $detalle, $idproducto, $precio, $id, $idu, $descuento, $impuesto) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    if (is_numeric($cantidad) && $detalle != "" && is_numeric($precio)) {
//        $q = "
//             INSERT INTO `tbm_cobro_factura_detalle`
//             (`id_cobro_factura`, `detalle`, `monto`, `id_producto_precio`, `cantidad`, `descuento`, `impuesto`) VALUES 
//             ($id,'$detalle',$precio,$idproducto,$cantidad,$descuento, $impuesto)";
//        $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//    }
//}
//
//function buscarFacturadeSolicitud($sol) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $resp = "";
//    $q = "
//            Select id_factura 
//            from tbm_cobro_factura_solicitud LEFT JOIN
//            tbm_cobro_factura ON tbm_cobro_factura_solicitud.id_factura = tbm_cobro_factura.id_cobro_factura
//            where tbm_cobro_factura_solicitud.id_solicitud = $sol and tbm_cobro_factura.estado >= 1
//            ";
//    $r = mysqli_query($dbc, $q);
//    if (mysqli_num_rows($r) > 0) {
//        $idf = mysqli_fetch_row($r);
//        $resp = $idf[0];
//    }
//    return $resp;
//}
//
//function eliminar_tildes($cadena) {
//
//    //Codificamos la cadena en formato utf8 en caso de que nos de errores
//    //$cadena = utf8_encode($cadena);
//    //Ahora reemplazamos las letras
//    $cadena = str_replace(
//            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
//            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
//            $cadena
//    );
//
//    $cadena = str_replace(
//            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
//            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
//            $cadena);
//
//    $cadena = str_replace(
//            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
//            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
//            $cadena);
//
//    $cadena = str_replace(
//            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
//            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
//            $cadena);
//
//    $cadena = str_replace(
//            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
//            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
//            $cadena);
//
////    $cadena = str_replace(
////        array('ñ', 'Ñ', 'ç', 'Ç'),
////        array('n', 'N', 'c', 'C'),
////        $cadena
////    );
//
//    return $cadena;
//}
//
//function sacarclientefacturadetalle($nota) {
//    $i = 0;
//    $j = strlen($nota);
//    $nombre = "";
//    while ($i < $j) {
//        if (substr($nota, $i, 9) == "Cliente: ") {
//            $nombre = "";
//            $i += 9;
//            while ($i < $j && $nota[$i] != ";") {
//                $nombre .= $nota[$i];
//                $i += 1;
//            }//while2
//        }
//        $i += 1;
//    }
//    return $nombre;
//}
//
//function quitarpuntos($s) {
//    $s = str_replace('"', '', $s);
//    $s = str_replace(':', '', $s);
//    $s = str_replace('.', '', $s);
//    $s = str_replace(',', '', $s);
//    $s = str_replace(';', '', $s);
//    return $s;
//}
//
//function validar_fecha($fecha) {
//    $valores = explode('-', $fecha);
//    if (count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])) {
//        return true;
//    }
//    return false;
//}
//
//function validar_fecha_espanol($fecha) {
//    $valores = explode('/', $fecha);
//    if (count($valores) == 3 && checkdate($valores[1], $valores[0], $valores[2])) {
//        return true;
//    }
//    return false;
//}
//
////Sacar un pedazo de un string, EJ: ('ABCD',1,3) = 'ABC'
//function segString($s, $i, $j) {
//    $resp = "";
//    while ($i < strlen($s) && $i <= $j) {
//        $resp .= $s[$i];
//        $i += 1;
//    }
//    return $resp;
//}
//
////Recibe un numero y lo devuelve escrito en letras
//function montoEnLetras($monto, $tipo) {
//    $entero = "";
//    $decimales = "";
//    $cen = "";
//
//    str_replace(".", ",", $monto);
//    if ($tipo == "C") {
//        $cen = "céntimos";
//        $tipo = "Colones";
//    } else {
//        $cen = "centavos";
//        $tipo = "Dólares";
//    }
//    $i = 0;
//    $j = strlen($monto);
//    while ($i < $j && $monto[$i] != ",") {
//        $entero .= + $monto[$i];
//        $i += 1;
//    }
//    $i += 1;
//    while ($i < $j) {
//        $decimales .= $monto[$i];
//        $i += 1;
//    }
//    $entero = MELetras($entero);
//
//    if (strlen($entero) > 0) {
//        if ($entero[strlen($entero) - 2] . $entero[strlen($entero) - 1] == "un") {
//            $entero .= "o";
//        }
//        if (!is_null($decimales) && $decimales != "") {
//            $decimales .= "/100";
//            $entero .= " " . $tipo . " con " . $decimales;
//        } else {
//            $entero .= " " . $tipo . " exactos";
//        }
//    }
//    return $entero;
//}
//
////Auxiliar de MontoenLetras
//function MELetras($monto) {
//    $temp = "";
//    $monto = MELCeros($monto);
//    $i = 0;
//    $j = strlen($monto);
//    $resp = "";
//
//    if ($j == 1) {
//        $resp = MELAux($monto);
//    } else if ($j >= 7 && $monto != 0) {
//        $temp = $monto[$j - 7];
//        $resp = MELetras(segString($monto, $j - 6, $j));
//        if ($temp == "1" && $j == 7) {
//            $resp = "un millon " . $resp;
//        } else {
//            $resp = MELetras(segString($monto, 0, $j - 7)) . " millones " . $resp;
//        }
//    } else if ($j >= 4 && $monto != 0) {
//        $temp = $monto[$j - 4];
//        $resp = MELetras(segString($monto, $j - 3, $j));
//        if ($temp == "1" && $j == 4) {
//            $resp = "un " . MELAux("1000") . " " . $resp;
//        } else {
//            $resp = MELetras(segString($monto, 0, $j - 4)) . " mil " . $resp;
//        }
//    } else if ($j >= 2 && $monto != 0) {
//        $temp = $monto[$j - 2] . $monto[$j - 1];
//
//        if ($temp != "00") {
//            $resp = MELdos($temp, false);
//        }
//        if ($j >= 3) {
//            $temp = $monto[$j - 3] . $monto[$j - 2] . $monto[$j - 1];
//
//            if ($temp != "000") {
//                $temp = $monto[$j - 3];
//                $resp = MELtres($temp, $resp);
//            } else {
//                $resp = "";
//            }
//        }
//    }
//
//    return $resp;
//}
//
////Auxiliar de MontoenLetras
//function MELdos($temp, $mil) {
//    $resp = "";
//    $a = 0;
//    $a = $temp;
//
//    if ($a >= 10 && $a <= 19) {
//        $resp = MELAux($temp);
//    } else if ($temp[0] == "0") {
//        $resp = MELAux($temp[1]);
//    } else {
//
//        if ($temp[1] == "0") {
//            $resp = MELAux($temp[0] . "0");
//        } else if ($temp[0] == "2") {
//
//            if ($mil) {
//                $resp = MELAux($temp[0] . "1") + MELAux("00" . $temp[1]);
//            } else {
//
//                $resp = MELAux($temp[0] . "1") . MELAux($temp[1]);
//            }
//        } else {
//            $resp = MELAux($temp[0] . "0") . " y " . MELAux($temp[1]);
//        }
//    }
//    return $resp;
//}
//
////Auxiliar de MontoenLetras
//function MELtres($temp, $resp) {
//    if ($temp == "0") {
//        
//    } else if ($temp == "1") {
//        if (is_null($resp) || $resp == "") {
//            $resp = MELAux($temp . "00");
//        } else {
//            $resp = MELAux($temp . "00") . "to " . $resp;
//        }
//    } else {
//        $resp = MELAux($temp . "00") . " " . $resp;
//    }
//    return $resp;
//}
//
////Auxiliar de MontoenLetras
//function MELCeros($monto) {
//
//    $resp = "";
//    $i = 0;
//    $j = strlen($monto);
//    while ($i < $j && $monto[$i] == "0") {
//        $i += 1;
//    }
//    $resp = segString($monto, $i, $j);
//    return $resp;
//}
//
////Auxiliar de MontoenLetras
//function MELAux($num) {
//
//    switch ($num) {
//        case "001":
//            $num = "ún";
//            break;
//        case "0":
//            $num = "cero";
//            break;
//        case "1":
//            $num = "un";
//            break;
//        case "2":
//            $num = "dos";
//            break;
//        case "3":
//            $num = "tres";
//            break;
//        case "4":
//            $num = "cuatro";
//            break;
//        case "5":
//            $num = "cinco";
//            break;
//        case "6":
//            $num = "seis";
//            break;
//        case "7":
//            $num = "siete";
//            break;
//        case "8":
//            $num = "ocho";
//            break;
//        case "9":
//            $num = "nueve";
//            break;
//        case "10":
//            $num = "diez";
//            break;
//        case "11":
//            $num = "once";
//            break;
//        case "12":
//            $num = "doce";
//            break;
//        case "13":
//            $num = "trece";
//            break;
//        case "14":
//            $num = "catorce";
//            break;
//        case "15":
//            $num = "quince";
//            break;
//        case "16":
//            $num = "dieciséis";
//            break;
//        case "17":
//            $num = "diecisiete";
//            break;
//        case "18":
//            $num = "dieciocho";
//            break;
//        case "19":
//            $num = "diecinueve";
//            break;
//        case "20":
//            $num = "veinte";
//            break;
//        case "21":
//            $num = "veinti";
//            break;
//        case "30":
//            $num = "treinta";
//            break;
//        case "40":
//            $num = "cuarenta";
//            break;
//        case "50":
//            $num = "cincuenta";
//            break;
//        case "60":
//            $num = "sesenta";
//            break;
//        case "70":
//            $num = "setenta";
//            break;
//        case "80":
//            $num = "ochenta";
//            break;
//        case "90":
//            $num = "noventa";
//            break;
//        case "100":
//            $num = "cien";
//            break;
//        case "200":
//            $num = "doscientos";
//            break;
//        case "300":
//            $num = "trescientos";
//            break;
//        case "400":
//            $num = "cuatrocientos";
//            break;
//        case "500":
//            $num = "quinientos";
//            break;
//        case "600":
//            $num = "seiscientos";
//            break;
//        case "700":
//            $num = "setecientos";
//            break;
//        case "800":
//            $num = "ochocientos";
//            break;
//        case "900":
//            $num = "novecientos";
//            break;
//        case "1000":
//            $num = "mil";
//            break;
//        case "1000000":
//            $num = "millon";
//
//            break;
//    }
//    return $num;
//}
//
//function tablaDetalle($nombredetabla, $facturadetalle, $idfactura, $idusuario, $estado, $no_exento, $hacerecho, $exentodeimpuestos, &$totalfactura) {
//
//    $descuentoglobal = 0;
//    $impuestoglobal = 0;
//    $desc = 0;
//
//    $dbc2 = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc2, "utf8");
//
//    $dibujatabla = ' <div class="box">
//                    <div class="box-header">
//                        <h1><font size="3" color="#4c74bc" >' . $nombredetabla . '</font></h1>
//                    
//                    </div>
//                    <div class="dataTables_wrapper">
//                        <table class="datatable">
//                            <thead>
//                                <tr>';
//    if ($estado == 2) {
//        $dibujatabla .= '  
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 40%;"><font color="green">Detalle</font></th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;"><font color="green">Cantidad</font></th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 15%;"><font color="green">P/Unitario</font></th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;"><font color="green">Descuento</font></th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;"><font color="green">Impuesto</font></th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;"><font color="green">Total</font></th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 5%;"></th>';
//    } else {
//        $dibujatabla .= '  
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 40%;">Detalle</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Cantidad</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 15%;">P/Unitario</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Descuento</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Impuesto</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 10%;">Total</th>
//                                    <th class="sorting" rowspan="1" colspan="1" style="width: 5%;"></th>';
//    }
//
//
//
//    $dibujatabla .= '
//                                </tr>
//                            </thead>
//
//                            <tbody>';
//
//    if ($estado == 1) {
//        $dibujatabla .= "<tr> 
//                             <td><input  type='text' name='acDetalle' id='acDetalle' maxlength='400'  placeholder='' value='' ></td>
//                             <td><input class='medium' name='acCantidad' id='acCantidad' type='text' name='extension' maxlength='20'  placeholder='' value='' ></td>
//                             <td><input class='medium' type='text' name='acPrecio' id='acPrecio' maxlength='20'  placeholder='' value='' ></td>    
//                             <td><input class='medium' type='text' name='acDescuento' id='acDescuento' maxlength='20'  placeholder='' value='0' ></td>    
//                             <td><input class='medium' type='text' name='acImpuesto' id='acImpuesto' maxlength='20'  placeholder='' value='2' ></td>    
//                             <td></td>    
//                             <td> </td> </tr>                            
//                          ";
//    }
//    $subtotal = 0;
//    $subtotalglobal = 0;
//    while ($detalle = mysqli_fetch_row($facturadetalle)) {
//
//        //************************************************************************
//
//        if ($detalle[2] == "") {
//
//            $sqldetalleproducto = "SELECT nombre_producto
//                                        FROM tbm_producto 
//                                        WHERE id_producto = $detalle[4]";
//            $detalleproducto = mysqli_query($dbc2, $sqldetalleproducto);
//            while ($producto = mysqli_fetch_row($detalleproducto)) {
//                $detalle[2] = $producto[0];
//            }
//        }
//
//        $desc = $detalle[6];
//        $impuesto = 0;
//        $imp = $detalle[7];
//        $descuento = 0;
//
//        $pu = "";
//        if (is_numeric($detalle[5]) && is_numeric($detalle[3])) {
//            $pu = $detalle[5] * $detalle[3];
//            $subtotal = $pu;
//            $descuento = ($subtotal * ($desc / 100));
//            $subtotal = $subtotal - $descuento;
//            $impuesto = ($subtotal * ($imp / 100));
//            $subtotal = $subtotal + $impuesto;
//            $subtotalglobal += $pu;
//            $descuentoglobal += $descuento;
//            $impuestoglobal += $impuesto;
//        }
//
//
//
//
//        $detalle[3] = number_format($detalle[3], 2, ',', '.');
//        $subtotal = number_format($subtotal, 2, ',', '.');
//
//        $desc = number_format($desc, 2, ',', '.');
//        $imp = number_format($imp, 2, ',', '.');
//        $btnborrar = "";
//        $buscar = array(chr(13) . chr(10), "\r\n", "\n", "\r");
//        $reemplazar = array("", "", "", "");
//        $detallesinenter = str_ireplace($buscar, $reemplazar, $detalle[2]);
//        if ($estado == 1) {
//            $btnborrar = "<a onClick=\"borrar($idfactura,$idusuario, $detalle[0], '$detallesinenter')\" class='button plain'>Borrar</a>";
//        }
//
//        $dibujatabla .= "
//                             <td>  $detalle[2] </td>
//                             <td> $detalle[5] </td>    
//                             <td>  $detalle[3]  </td>    
//                             <td>  $desc % </td>           
//                             <td>  $imp % </td>           
//                             <td>  $subtotal  </td>    
//                             <td> $btnborrar  </td>
//                            </tr>                             
//                          ";
//    }
//
//    //$desc = $descuento / 100 * $subtotal;
//    //$desc = 0;
//    $total = $subtotalglobal - $descuentoglobal + $impuestoglobal;
//
//    $subtotalglobal = number_format($subtotalglobal, 2, ',', '.');
//
//    $descuentoglobal = number_format($descuentoglobal, 2, ',', '.');
//    $exoneracion = 0;
//    if ($exentodeimpuestos == 1) {
//        $exoneracion = $impuestoglobal;
//        echo $exoneracion;
//        $total = $total - $exoneracion;
//        $exoneracion = number_format($exoneracion, 2, ',', '.');
//    }
//    $impuestoglobal = number_format($impuestoglobal, 2, ',', '.');
//    $totalfactura = $total;
//    $total = number_format($total, 2, ',', '.');
//
//    $totalfactura = number_format($totalfactura, 2, ',', '');
//
//    $dibujatabla .= "<tr>
//                            <td></td><td></td><td></td><td></td><td>SubTotal:</td><td>$subtotalglobal</td><td></td> 
//                        </tr>
//                        <tr>
//                            <td></td><td></td><td></td><td></td><td>Descuento Global:</td><td>$descuentoglobal</td><td></td> 
//                        </tr>
//                        <tr>
//                            <td></td><td></td><td></td><td></td><td>Impuesto:</td><td>$impuestoglobal</td><td></td>
//                        </tr>
//                        ";
//    if ($exentodeimpuestos == 1) {
//        $dibujatabla .= "<tr>
//                            <td></td><td></td><td></td><td></td><td>Exoneración:</td><td>$exoneracion</td><td></td> 
//                        </tr>";
//    }
//    $dibujatabla .= "<tr> 
//                            <td></td><td></td><td></td><td></td><td>TOTAL:</td><td><font size=4>$total</font></td><td></td> 
//                        </tr>                            
//                          ";
//
//    $dibujatabla .= ' </tbody>
//                        </table> 
//                    </div> <!-- class=dataTables_wrapper -->
//                </div> <!-- class=box -->';
//    if ($hacerecho) {
//        echo $dibujatabla;
//    }
//}
//
//function generarListaSolicitudes($lsols) {
//    $i = 4;
//    $j = strlen($lsols);
//    $resp = "SOL.";
//    $hayguion = false;
//    $solactual = "";
//    $solanterior = "";
//    while ($i < $j) {
//        $solanterior = $solactual;
//        $solactual = "";
//        while ($i < $j && $lsols[$i] != "," && $lsols[$i] != "-") {
//            if ($lsols[$i] != " ")
//                $solactual .= $lsols[$i];
//            $i += 1;
//        }
//        $solactual = trim($solactual);
//        //echo $solactual ."<br>";
//        if ($hayguion) {
//
//            if (is_numeric($solactual) && is_numeric($solanterior)) {
//                $solanterior += 1;
//                while ($solanterior <= $solactual) {
//                    if (strlen($resp) > 4) {
//                        $resp .= ",";
//                    }
//                    $resp .= $solanterior;
//                    $solanterior += 1;
//                }
//            }
//            $hayguion = false;
//        } else {
//            if (strlen($resp) > 4) {
//                $resp .= ",";
//            }
//            $resp .= $solactual;
//        }
//
//        if ($i < $j && $lsols[$i] == "-") {
//            $hayguion = true;
//        }
//
//
//        $i += 1;
//    }
//
//    return $resp;
//}
//
//function traeidSolicitud($sol) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $resp = "";
//    $q = "SELECT
//                id_solicitud
//              FROM tbm_solicitud 
//              WHERE numero = '$sol'
//               ";
//    $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    if (mysqli_num_rows($r) > 0) {
//
//        $sol = mysqli_fetch_row($r);
//        $resp = $sol[0];
//    }
//
//    return $resp;
//}
//
//function vercadena($cad1, $cad2, $caseSensitive) {
//    $tam1 = strlen($cad1);
//    $tam2 = strlen($cad2);
//    $c1 = 0;
//    $c2 = 0;
//    $resp = false;
//    $temp = "";
//    $resp = false;
//
//    if (!$caseSensitive) {
//        $cad1 = strtoupper($cad1);
//        $cad2 = strtoupper($cad2);
//    }
//    // mientras el tamaño restante de cad2 sea mayor al de cad1
//    while (((($tam2 - $c2) + 1) > $tam1) && ($c1 <> -1)) {
//        While (($c1 < $tam1) && ($c2 < $tam2) && ($cad1[$c1] <> $cad2[$c2])) {
//            $c2 = $c2 + 1;
//        }
//        While (($c1 < $tam1) && ($c2 < $tam2) && ($cad1[$c1] == $cad2[$c2] )) {
//            $temp = $temp + $cad2[$c2];
//            $c1 = $c1 + 1;
//            $c2 = $c2 + 1;
//        }
//        If ($temp == $cad1) {
//            $c1 = -1;
//            $resp = True;
//        }
//        $temp = "";
//        If ($c1 <> -1) {
//            $c1 = 0;
//        }
//    }//while 1
//
//    return $resp;
//}
//
//function vercadena2($cad1, $cad2, $caseSensitive) {
//    $tam1 = strlen($cad1);
//    $tam2 = strlen($cad2);
//    $c1 = 0;
//    $c2 = 0;
//    $temp = "";
//    $resp = false;
//
//    if (!$caseSensitive) {
//        $cad1 = strtoupper($cad1);
//        $cad2 = strtoupper($cad2);
//    }
//    $i = 0;
//    while ($i < $tam2) {
//        $c1 = 0;
//        $c2 = $i;
//        $seguir = true;
//
//        while ($c1 < $tam1 && $c2 < $tam2 && $cad1[$c1] != $cad2[$c2]) {
//            $c1 += 1;
//        }
//        $temp = "";
//        while ($c1 < $tam1 && $c2 < $tam2 && $cad1[$c1] == $cad2[$c2]) {
//            $temp = $temp . $cad2[$c2];
//            $c1 += 1;
//            $c2 += 1;
//        }
//        if ($temp == $cad1) {
//            $i = $tam2;
//            $resp = true;
//        }
//
//        $i += 1;
//    }
//    return $resp;
//}
//
//function cambiarpunto($val, $c) {
//    if ($val != "") {
//        if ($c == ",") {
//            $val = cambiarpunto2($val, ",");
//        } else if ($c == ".") {
////        if (vercadena2("e-", $val, false)){
////           $val = cambiarpunto2($val, ",");     
////        }
//            $val = cambiarpunto2($val, ".");
//        }
//    }
//    return $val;
//}
//
//function cambiarpunto2($val, $c) {
//    $i = 0;
//    $j = 0;
//    $resp = "";
//    $p = "";
//    //echo ";;;" . $val;
//
//    $val = str_split($val);
//    //echo "***" . $val[0] . "<br>";
//    $j = count($val);
//    //echo $j;
//    if ($c == ".") {
//        $p = ",";
//    } else {
//        $p = ".";
//    }
//    //echo "hola ".$val[$i].   "/";
//    while ($i < $j) {
//        if ($val[$i] != $p) {
//            $resp .= $val[$i];
//        } else {
//            $resp .= $c;
//        }
//        $i += 1;
//    }
////        if (is_numeric($resp[0]) && vercadena2 ("e-", $resp, false)){
////            
////        }
//
//    return $resp;
//}
//
//function borrarCliente($iduu) {
//
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $sql = "DELETE FROM `tbm_cliente_telefono` WHERE id_cliente =  $iduu";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    $sql = "DELETE FROM `tbm_cliente_descuentofijo` WHERE id_cliente =  $iduu";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    $sql = "DELETE FROM `tbm_cliente_descuento` WHERE id_cliente =  $iduu";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    $sql = "DELETE FROM `tbm_cliente_correo` WHERE id_cliente =  $iduu";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    $sql = "DELETE FROM `tbm_cliente_contacto_correo` WHERE id_contacto in 
//                (SELECT id_contacto FROM tbm_cliente_contacto WHERE id_cliente = $iduu )";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    $sql = "DELETE FROM `tbm_cliente_contacto_telefono` WHERE id_contacto in 
//                (SELECT id_contacto FROM tbm_cliente_contacto WHERE id_cliente = $iduu )";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    $sql = "DELETE FROM `tbm_cliente_contacto` WHERE id_cliente =  $iduu";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    $sql = "DELETE FROM `tbm_cliente_subcliente` WHERE id_cliente =  $iduu";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    $sql = "DELETE FROM `tbm_cliente` WHERE id_cliente =  $iduu";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    echo "<script>
//                               
//                              window.location=\"usuarioslistado.php?d=1&op=3&t=1&e=1\";
//                      </script>";
//}
//
//function solicitudTelefonos2($idsolicitud, $oficial, $responsable2, $link = "x") {
//
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $tsolicitud_contacto = "";
//    if ($oficial == "o") {
//        $tsolicitud_contacto = "tbm_solicitud_contacto";
//    } else {
//        $tsolicitud_contacto = "tbm_solicitud_contactotemp";
//    }
//
//    $telefonos2 = "";
//    $sqlcontactostel = " SELECT DISTINCT * FROM (
//                    select 
//                    $tsolicitud_contacto.id_contacto, 
//                    tbm_cliente_contacto_telefono.telefono,
//                    tbm_cliente_contacto_telefono.id_contacto_telefono
//                    from $tsolicitud_contacto LEFT JOIN
//                    tbm_cliente_contacto ON tbm_cliente_contacto.id_contacto = $tsolicitud_contacto.id_contacto LEFT JOIN
//                    tbm_cliente_contacto_telefono ON $tsolicitud_contacto.id_contacto = tbm_cliente_contacto_telefono.id_contacto 
//                    where $tsolicitud_contacto.id_solicitud = $idsolicitud and tbm_cliente_contacto_telefono.favorito = 1 and tbm_cliente_contacto.estado > 0
//                    UNION ALL
//                    select 
//                    tbm_cliente_contacto.id_contacto, 
//                    tbm_cliente_contacto_telefono.telefono,
//                    tbm_cliente_contacto_telefono.id_contacto_telefono
//                    from tbm_cliente_contacto LEFT JOIN
//                    tbm_cliente_contacto_telefono ON tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_telefono.id_contacto 
//                    where tbm_cliente_contacto.id_contacto in ($responsable2) and tbm_cliente_contacto_telefono.favorito = 1 and tbm_cliente_contacto.estado > 0) 
//                    TTELEFONOS
//                    ORDER BY telefono
//                        
//
//            ";
//    $rcontactostel = mysqli_query($dbc, $sqlcontactostel);
//    while ($fila = mysqli_fetch_row($rcontactostel)) {
//
//        if (trim($telefonos2) != "") {
//            $telefonos2 .= ", ";
//        }
//        if ($link == "x") {
//            $telefonos2 .= $fila[1];
//        } else {
//            $idcontactotelefono = $fila[2];
//            $telefonos2 .= "<font size=3><a href='$link&idtel=$idcontactotelefono' data-toggle='tooltip' title='Ocultar Teléfono de Contacto' color=blue >$fila[1]</a></font>";
//        }
//    }
//
//    return $telefonos2;
//}
//
//function solicitudTelefonos1($idcliente, $telefonos2, $link = "x") {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $telefonos = "";
////    $sqltelefonos = "select 
////                    telefono, id_cliente_telefono  
////                    from tbm_cliente_telefono
////                    where id_cliente = $idcliente and favorito = 1
////                    ORDER BY id_telefono_tipo";
//    $sqltelefonos = "select 
//                    tbm_cliente_contacto_telefono.telefono, tbm_cliente_contacto_telefono.id_contacto_telefono  
//                    from tbm_cliente_contacto_telefono LEFT JOIN 
//                    tbm_cliente_contacto ON tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_telefono.id_contacto
//                    where tbm_cliente_contacto.id_cliente = $idcliente and tbm_cliente_contacto_telefono.favorito = 1 and tbm_cliente_contacto.general = 1
//                    ";
//    $rtelefonos = mysqli_query($dbc, $sqltelefonos);
//    $contTelefonos = 0;
//    while ($fila = mysqli_fetch_row($rtelefonos)) {
//        if ($contTelefonos < 6) {
//            if (trim($telefonos) != "") {
//                $telefonos .= ", ";
//            }
//            $contTelefonos += 1;
//            $fila[0] = trim($fila[0]);
//            if ($link == "x") {
//                $telefonos .= $fila[0];
//            } else {
//                $idclientetelefono = $fila[1];
//                $telefonos .= "<font size=3><a href='$link&idtel=$idclientetelefono' data-toggle='tooltip' title='Ocultar Teléfono Principal' color=blue >$fila[0]</a></font>";
//            }
//        }
//    }
//    if (trim($telefonos2) != "" && trim($telefonos) != "") {
//        $telefonos .= ", ";
//        $telefonos .= $telefonos2;
//    } else if (trim($telefonos2) != "") {
//        $telefonos .= $telefonos2;
//    }
//    return $telefonos;
//}
//
//function solicitudCorreosN2($idsolicitud, $oficial) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $tsolicitud_contacto = "";
//    if ($oficial == "o") {
//        $tsolicitud_contacto = "tbm_solicitud_contacto";
//    } else {
//        $tsolicitud_contacto = "tbm_solicitud_contactotemp";
//    }
//
//
//    $correosN = "";
//
//    $sqlcontactos = "SELECT DISTINCT * FROM  (
//                    select 
//                    $tsolicitud_contacto.id_contacto, 
//                    tbm_cliente_contacto_correo.correo, 
//                    tbm_cliente_contacto.nombre ,
//                    tbm_cliente_contacto_correo.id_contacto_correo
//                    from $tsolicitud_contacto LEFT JOIN
//                    tbm_cliente_contacto ON tbm_cliente_contacto.id_contacto = $tsolicitud_contacto.id_contacto LEFT JOIN
//                    tbm_cliente_contacto_correo ON $tsolicitud_contacto.id_contacto = tbm_cliente_contacto_correo.id_contacto 
//                    where $tsolicitud_contacto.id_solicitud = $idsolicitud and tbm_cliente_contacto_correo.favorito = 1 and tbm_cliente_contacto.estado > 0
//                    UNION 
//                    select 
//                    tbm_cliente_contacto.id_contacto, 
//                    tbm_cliente_contacto_correo.correo, 
//                    tbm_cliente_contacto.nombre ,
//                    tbm_cliente_contacto_correo.id_contacto_correo
//                    from tbm_cliente_contacto LEFT JOIN
//                    tbm_cliente_contacto_correo ON tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_correo.id_contacto 
//                    where tbm_cliente_contacto.id_contacto in ($responsable2) and tbm_cliente_contacto_correo.favorito = 1 and tbm_cliente_contacto.estado > 0)
//                    UNION ALL
//                    select 
//                    tbm_cliente_contacto.id_contacto, 
//                    tbm_cliente_contacto_correo.correo, 
//                    tbm_cliente_contacto.nombre,
//                    tbm_cliente_contacto_correo.id_contacto_correo
//                    from tbm_cliente_contacto LEFT JOIN 
//                    tbm_cliente_contacto_correo ON tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_correo.id_contacto LEFT JOIN 
//                    tbm_solicitud ON tbm_solicitud.id_cliente = tbm_cliente_contacto.id_cliente 
//                    where ( 
//                        tbm_solicitud.id_solicitud = $idsolicitud and tbm_cliente_contacto.nombre = 'CONTACTO GENERAL' and 
//                            tbm_cliente_contacto_correo.favorito = 1 and tbm_cliente_contacto.estado > 0)                     
//                    TCORREOS
//                    ORDER BY   nombre, correo
//                     ";
//    $rcontactos = mysqli_query($dbc, $sqlcontactos);
//    while ($fila = mysqli_fetch_row($rcontactos)) {
//
//
//        $fila[1] = trim($fila[1]);
//        if ($fila[1] != "") {
//            if (strlen($correosN) > 0) {
//                $correosN .= ", ";
//            }
//            if ($responsable2 == "") {
//                $responsable2 = $fila[2];
//            }
//
//
//            if ($link == "x") {
//                $correosN .= $fila[1];
//            } else {
//                $idcontactocorreo = $fila[3];
//                $correosN .= "<font size=3><a href='$link&idcorreo=$idcontactocorreo' data-toggle='tooltip' title='Ocultar Correo de Contacto'  color=blue >$fila[1]</a></font>";
//            }
//        }
////        }
//    }
//    return $correosN;
//}
//
//function solicitudCorreosN($idsolicitud, &$responsable2, $oficial, $link = "x") {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $tsolicitud_contacto = "";
//    if ($oficial == "o") {
//        $tsolicitud_contacto = "tbm_solicitud_contacto";
//    } else {
//        $tsolicitud_contacto = "tbm_solicitud_contactotemp";
//    }
//
//
//    $correosN = "";
//
//    $sqlcontactos = "SELECT DISTINCT * FROM  (
//                    select 
//                    $tsolicitud_contacto.id_contacto, 
//                    tbm_cliente_contacto_correo.correo, 
//                    tbm_cliente_contacto.nombre ,
//                    tbm_cliente_contacto_correo.id_contacto_correo
//                    from $tsolicitud_contacto LEFT JOIN
//                    tbm_cliente_contacto ON tbm_cliente_contacto.id_contacto = $tsolicitud_contacto.id_contacto LEFT JOIN
//                    tbm_cliente_contacto_correo ON $tsolicitud_contacto.id_contacto = tbm_cliente_contacto_correo.id_contacto 
//                    where $tsolicitud_contacto.id_solicitud = $idsolicitud and tbm_cliente_contacto_correo.favorito = 1 and tbm_cliente_contacto.estado > 0
//                    UNION ALL
//                    select 
//                    tbm_cliente_contacto.id_contacto, 
//                    tbm_cliente_contacto_correo.correo, 
//                    tbm_cliente_contacto.nombre ,
//                    tbm_cliente_contacto_correo.id_contacto_correo
//                    from tbm_cliente_contacto LEFT JOIN
//                    tbm_cliente_contacto_correo ON tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_correo.id_contacto 
//                    where tbm_cliente_contacto.id_contacto in ($responsable2) and tbm_cliente_contacto_correo.favorito = 1 and tbm_cliente_contacto.estado > 0)
//                        TCORREOS
//                        ORDER BY   nombre, correo
//                     ";
//    $rcontactos = mysqli_query($dbc, $sqlcontactos);
//    while ($fila = mysqli_fetch_row($rcontactos)) {
//
//
//        $fila[1] = trim($fila[1]);
//        if ($fila[1] != "") {
//            if (strlen($correosN) > 0) {
//                $correosN .= ", ";
//            }
//            if ($responsable2 == "") {
//                $responsable2 = $fila[2];
//            }
//
//
//            if ($link == "x") {
//                $correosN .= $fila[1];
//            } else {
//                $idcontactocorreo = $fila[3];
//                $correosN .= "<font size=3><a href='$link&idcorreo=$idcontactocorreo' data-toggle='tooltip' title='Ocultar Correo de Contacto'  color=blue >$fila[1]</a></font>";
//            }
//        }
//    }
//    return $correosN;
//}
//
//function solicitudCorreosR($idcliente, $link = "x") {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $correosR = "";
//
////    $sqlcontactos = "select 
////                    tbm_cliente_correo.id_cliente_correo,
////                    tbm_cliente_correo.correo, 
////                    tbm_cliente_correo.favorito
////                    from tbm_cliente_correo
////                    where tbm_cliente_correo.id_cliente = $idcliente and tbm_cliente_correo.favorito = 1
////                    ORDER BY tbm_cliente_correo.favorito, tbm_cliente_correo.correo ";
//
//    $sqlcontactos = "select 
//                    tbm_cliente_contacto_correo.id_contacto_correo,
//                    tbm_cliente_contacto_correo.correo, 
//                    tbm_cliente_contacto_correo.favorito
//                    from tbm_cliente_contacto_correo LEFT JOIN 
//                    tbm_cliente_contacto ON tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_correo.id_contacto
//                    where tbm_cliente_contacto.id_cliente = $idcliente and tbm_cliente_contacto_correo.favorito = 1 and tbm_cliente_contacto.general = 1
//                    ORDER BY tbm_cliente_contacto_correo.correo ";
//
//    $rcontactos = mysqli_query($dbc, $sqlcontactos);
//    while ($fila = mysqli_fetch_row($rcontactos)) {
//
//        //if ($fila[2] == 1) {
//        if (strlen($correosR) > 0) {
//            $correosR .= "; ";
//        }
//        if ($link == "x") {
//            $correosR .= $fila[1];
//        } else {
//            $idclientecorreo = $fila[0];
//            $correosR .= "<font size=3><a href='$link&idcorreo=$idclientecorreo' data-toggle='tooltip' title='Ocultar Correo Principal'  color=blue >$fila[1]</a></font>";
//        }
////        } else {
////            if (strlen($correosN) > 0) {
////                $correosN .= ", ";
////            }
////            $correosN .= $fila[1];
////        }
//    }
//    return $correosR;
//}
//
//function solicitudCorreosN1($idsolicitud, &$responsable2, $oficial, $idcliente, $link = "x") {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $correosN = "";
//    if ($oficial == "o") {
//        $tsolicitud_contactocorreo = "tbm_solicitud_contacto_correo";
//    } else {
//        $tsolicitud_contactocorreo = "tbm_solicitud_contactotemp_correo";
//    }
//
//    if ($responsable2 == "") {
//        $responsable2 = 0;
//    }
////    $sqlcontactos = "
////                    SELECT DISTINCT
////                        id_contacto_correo,
////                        correo
////                    from 
////
////                        tbm_cliente_contacto_correo 
////                    where id_contacto_correo in 
////                    (
////                    Select id_contacto_correo
////                    from $tsolicitud_contactocorreo
////                    where id_solicitud = $idsolicitud
////                    )
////
////                    or 
////                     id_contacto = $responsable2
////                        
////            ";
////    $sqlcontactos = "
////        SELECT DISTINCT *
////FROM (
////
////SELECT tbm_cliente_contacto_correo.id_contacto_correo, tbm_cliente_contacto_correo.correo
////FROM tbm_cliente_contacto_correo LEFT JOIN
////tbm_cliente_contacto on tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_correo.id_contacto
////LEFT JOIN $tsolicitud_contactocorreo ON $tsolicitud_contactocorreo.id_contacto_correo = tbm_cliente_contacto_correo.id_contacto_correo
////WHERE 
////        $tsolicitud_contactocorreo.id_solicitud = $idsolicitud and 
////        tbm_cliente_contacto.estado > 0 and 
////        tbm_cliente_contacto_correo.favorito > 0 and
////        tbm_cliente_contacto_correo.estado > 0 
////UNION
////SELECT tbm_cliente_contacto_correo.id_contacto_correo, tbm_cliente_contacto_correo.correo
////FROM tbm_cliente_contacto_correo LEFT JOIN
////tbm_cliente_contacto on tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_correo.id_contacto
////WHERE 
////    tbm_cliente_contacto_correo.id_contacto = $responsable2 and 
////    tbm_cliente_contacto.id_cliente = $idcliente and
////    tbm_cliente_contacto.estado > 0 and 
////    tbm_cliente_contacto_correo.favorito > 0 and
////    tbm_cliente_contacto_correo.estado > 0
////)TCORREOS GROUP BY correo order by correo 
////        ";
//
//    $sqlcontactos = "
//SELECT distinct tbm_cliente_contacto_correo.id_contacto_correo, tbm_cliente_contacto_correo.correo
//FROM tbm_cliente_contacto_correo LEFT JOIN
//tbm_cliente_contacto on tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_correo.id_contacto
//LEFT JOIN $tsolicitud_contactocorreo ON $tsolicitud_contactocorreo.id_contacto_correo = tbm_cliente_contacto_correo.id_contacto_correo
//WHERE 
//        $tsolicitud_contactocorreo.id_solicitud = $idsolicitud 
//GROUP BY tbm_cliente_contacto_correo.correo order by tbm_cliente_contacto_correo.correo 
//        ";
//    //echo  $sqlcontactos. "<br>"; 
//
//    $rcontactos = mysqli_query($dbc, $sqlcontactos);
//    while ($fila = mysqli_fetch_row($rcontactos)) {
//
//
//        $fila[1] = trim($fila[1]);
//        if ($fila[1] != "") {
//            if (strlen($correosN) > 0) {
//                $correosN .= "; ";
//            }
////            if ($responsable2 == "") {
////                $responsable2 = $fila[2];
////            }
//
//
//            if ($link == "x") {
//                $correosN .= $fila[1];
//            } else {
//                $idcontactocorreo = $fila[3];
//                $correosN .= "<font size=3><a href='$link&idcorreo=$idcontactocorreo' data-toggle='tooltip' title='Ocultar Correo de Contacto'  color=blue >$fila[1]</a></font>";
//            }
//        }
//    }
//    //echo $correosN;
//    return $correosN;
//}
//
//function solicitudTelefonosN1($idsolicitud, $oficial, $responsable2, $idcliente, $link = "x") {
//
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $tsolicitud_contacto = "";
//    if ($oficial == "o") {
//        $tsolicitud_contactotel = "tbm_solicitud_contacto_telefono";
//    } else {
//        $tsolicitud_contactotel = "tbm_solicitud_contactotemp_telefono";
//    }
//
//    $telefonos2 = "";
////    $sqlcontactostel = "
////                    SELECT DISTINCT
////                        id_contacto_telefono,
////                        telefono
////                    from 
////                        tbm_cliente_contacto_telefono
////                    where  id_contacto_telefono in 
////                    (
////                    Select id_contacto_telefono
////                    from $tsolicitud_contactotel
////                    where id_solicitud = $idsolicitud
////                    )
////
////                    or 
////                     id_contacto = $responsable2
////            ";
////    $sqlcontactostel  = "
////        SELECT DISTINCT *
////FROM (
////
////SELECT tbm_cliente_contacto_telefono.id_contacto_telefono, tbm_cliente_contacto_telefono.telefono
////FROM tbm_cliente_contacto_telefono left join
////tbm_cliente_contacto on tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_telefono.id_contacto
////LEFT JOIN $tsolicitud_contactotel ON $tsolicitud_contactotel.id_contacto_telefono = tbm_cliente_contacto_telefono.id_contacto_telefono
////WHERE 
////        $tsolicitud_contactotel.id_solicitud = $idsolicitud and
////        tbm_cliente_contacto.estado > 0 and 
////        tbm_cliente_contacto_telefono.favorito > 0 and
////        tbm_cliente_contacto_telefono.estado > 0 
////UNION
////SELECT tbm_cliente_contacto_telefono.id_contacto_telefono, tbm_cliente_contacto_telefono.telefono
////FROM tbm_cliente_contacto_telefono left join
////tbm_cliente_contacto on tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_telefono.id_contacto
////WHERE 
////        tbm_cliente_contacto_telefono.id_contacto = $responsable2 and 
////        tbm_cliente_contacto.id_cliente = $idcliente and
////       tbm_cliente_contacto.estado > 0 and 
////       tbm_cliente_contacto_telefono.favorito > 0 and
////       tbm_cliente_contacto_telefono.estado > 0
////)TCORREOS GROUP BY telefono order by telefono
////        ";
//    $sqlcontactostel = "
//
//SELECT distinct tbm_cliente_contacto_telefono.id_contacto_telefono, tbm_cliente_contacto_telefono.telefono
//FROM tbm_cliente_contacto_telefono left join
//tbm_cliente_contacto on tbm_cliente_contacto.id_contacto = tbm_cliente_contacto_telefono.id_contacto
//LEFT JOIN $tsolicitud_contactotel ON $tsolicitud_contactotel.id_contacto_telefono = tbm_cliente_contacto_telefono.id_contacto_telefono
//WHERE 
//        $tsolicitud_contactotel.id_solicitud = $idsolicitud
//GROUP BY tbm_cliente_contacto_telefono.telefono order by tbm_cliente_contacto_telefono.telefono
//
//        ";
//
//    //echo $sqlcontactostel;
//    $rcontactostel = mysqli_query($dbc, $sqlcontactostel);
//    while ($fila = mysqli_fetch_row($rcontactostel)) {
//
//        if (trim($telefonos2) != "") {
//            $telefonos2 .= ", ";
//        }
//        if ($link == "x") {
//            $telefonos2 .= $fila[1];
//        } else {
//            $idcontactotelefono = $fila[0];
//            $telefonos2 .= "<font size=3><a href='$link&idtel=$idcontactotelefono' data-toggle='tooltip' title='Ocultar Teléfono de Contacto' color=blue >$fila[1]</a></font>";
//        }
//    }
//
//    return $telefonos2;
//}
//
//function solicitudResponsable($idresponsable) {
//    $responsable = "";
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $sql = "SELECT nombre FROM tbm_cliente_contacto where id_contacto = $idresponsable";
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//    if (mysqli_num_rows($r) > 0) {
//        $fila = mysqli_fetch_row($r);
//        $responsable = $fila[0];
//    }
//
//    return $responsable;
//}
//
//function preciosArr($idmoneda = "*") {
//    //$preciosarr = "";
//    $preciosarr = array();
//    $precioarrTAM = 0;
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $ssql = "select tbm_producto_precio.id_producto_precio, tbm_producto_precio.id_producto, tbm_producto_precio.precio, tbm_moneda.simbolo, tbm_producto_precio.id_moneda
//        from tbm_producto_precio LEFT JOIN
//        tbm_moneda ON tbm_producto_precio.id_moneda = tbm_moneda.id_moneda";
//
//    if ($idmoneda != "*" && is_numeric($idmoneda)) {
//        $ssql .= " where tbm_producto_precio.id_moneda = $idmoneda";
//    }
//    $resultado = mysqli_query($dbc, $ssql);
//
//    while ($fila = mysqli_fetch_row($resultado)) {
//        $preciosarr[$precioarrTAM][0] = $fila[0];
//        $preciosarr[$precioarrTAM][1] = $fila[1];
//        $preciosarr[$precioarrTAM][2] = $fila[2];
//        $preciosarr[$precioarrTAM][3] = $fila[3];
//        $preciosarr[$precioarrTAM][4] = $fila[4];
//        $precioarrTAM += 1;
//    }
//    return $preciosarr;
//}
//
//function precioProducto($preciosarr, $idproducto) {
//    $precios = "";
//    $p = "";
//    $i = 0;
//    $precioarrTAM = count($preciosarr);
//    while ($i < $precioarrTAM) {
//        if ($preciosarr[$i][1] == $idproducto) {
//            if ($precios != "") {
//                $precios .= "<br>";
//            }
//            if ($preciosarr[$i][4] == 2) {
//                $preciosarr[$i][2] = number_format($preciosarr[$i][2], 0, '.', ',');
//            } else {
//                $preciosarr[$i][2] = number_format($preciosarr[$i][2], 0, ',', '.');
//            }
//
//
//            $precios .= $preciosarr[$i][3] . " " . $preciosarr[$i][2];
//        }
//        $i += 1;
//    }
//    return $precios;
//}
//
//function escribirAgregar($dir, $item) {
//    $fp = fopen($dir, "a");
//    fputs($fp, $item . ";");
//    fclose($fp);
//}
//
//function escribirQuitar($dir, $item) {
//    $fp = fopen($dir, "r");
//    $linea = fgets($fp);
//    fclose($fp);
//    $linea = explode(";", $linea);
//    $fp = fopen($dir, "w");
//    $i = 0;
//    $j = count($linea);
//
//    while ($i < $j) {
//
//
//        if ($item != $linea[$i] && $linea[$i] != "") {
//
//            fputs($fp, $linea[$i] . ";");
//        }
//        $i += 1;
//    }
//    fclose($fp);
//}
//
//function leerArreglo($dir) {
//    $arreglo = "";
//    $fp = fopen($dir, "r");
//    $linea = fgets($fp);
//    $arreglo = explode(";", $linea);
//    fclose($fp);
//    return $arreglo;
//}
//
//function escribirLimpiar($dir) {
//    $fp = fopen($dir, "w");
//    fclose($fp);
//}
//
//function agregarAnalisisGeneral($idmuestra, $moneda, $idproducto, $tablamuestrasanalisis) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $sql = "SELECT id_producto_precio, precio
//        from tbm_producto_precio
//        where id_moneda = $moneda and id_producto = $idproducto
//            ";
//    $resultado = mysqli_query($dbc, $sql);
//    if ($resultado->num_rows > 0) {
//        $r = mysqli_fetch_row($resultado);
//        $precio = $r[1];
//        $idp = $r[0];
//
//        $q = "INSERT INTO $tablamuestrasanalisis (id_muestras, id_producto_precio, precio) 
//                            VALUES ($idmuestra, $idp , $precio )";
//        $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//    }
//}
//
//function quitarAnalisisGeneral($idmuestra, $moneda, $idproducto, $tablamuestrasanalisis) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $sql = "SELECT id_producto_precio
//        from tbm_producto_precio
//        where id_moneda = $moneda and id_producto = $idproducto
//            ";
//
//    $resultado = mysqli_query($dbc, $sql);
//    if ($resultado->num_rows > 0) {
//
//        $r = mysqli_fetch_row($resultado);
//        $idp = $r[0];
//
//        $q = "DELETE FROM $tablamuestrasanalisis WHERE id_muestras = $idmuestra and id_producto_precio = $idp";
//        $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//    }
//}
//
//function actualizarEtiquetaCultivo2n($idmuestra, $etiqueta, $cultivo, $tablamuestras, $lab) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    //$etiqueta = "hoa";
//    //echo $lab;
//    if ($lab == 3 || $lab == 5) {
//        if (!vercadena2("SOL:", $etiqueta, false)) {
//            $etiqueta = "SOL: $etiqueta";
//        }
//    } else if ($lab == 4 || $lab == 6) {
//        if (!vercadena2("LIQ:", $etiqueta, false)) {
//            $etiqueta = "LIQ: $etiqueta";
//        }
//    }
//
//
//    $q = "UPDATE $tablamuestras SET `etiqueta` = '$etiqueta', `id_cultivo`= $cultivo WHERE id_muestras = $idmuestra ";
//    $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//}
//
//function mostrarOcultarInformacionContactos($acc, $idtel, $idcorreo, $idsolicitud, $oficial, $link) {
//    $dbc2 = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc2, "utf8");
//    $tsolcontactocorrreo = "";
//    $tsolcontactotelefono = "";
//    if ($oficial == "o") {
//        $tsolcontactocorrreo = "tbm_solicitud_contacto_correo";
//        $tsolcontactotelefono = "tbm_solicitud_contacto_telefono";
//    } else {
//        $tsolcontactocorrreo = "tbm_solicitud_contactotemp_correo";
//        $tsolcontactotelefono = "tbm_solicitud_contactotemp_telefono";
//    }
//
//
//    if ($acc == 10 || $acc == 11) {
//        $acc = $acc % 10;
//
//        if ($idtel != "x") {
//            $sqlup = "UPDATE `tbm_cliente_contacto_telefono` SET `favorito`= $acc WHERE `id_contacto_telefono` =  $idtel";
//            $r = mysqli_query($dbc2, $sqlup);
//            if ($idsolicitud != 0 && $acc == 10) {
//                $sqlup = "DELETE FROM `$tsolcontactotelefono`
//                        WHERE `id_contacto_telefono`= $idtel and `id_solicitud` = $idsolicitud ";
//                $r = mysqli_query($dbc2, $sqlup);
//            }
//        } else if ($idcorreo != "x") {
//            $sqlup = "UPDATE `tbm_cliente_contacto_correo` SET `favorito`=$acc WHERE `id_contacto_correo`= $idcorreo";
//            $r = mysqli_query($dbc2, $sqlup);
//            if ($idsolicitud != 0 && $acc == 10) {
//                $sqlup = "DELETE FROM `$tsolcontactocorrreo`
//                        WHERE `id_contacto_correo`= $idcorreo and `id_solicitud` = $idsolicitud ";
//                $r = mysqli_query($dbc2, $sqlup);
//            }
//        }
//    } else if ($acc == 20 || $acc == 21) {
//
//        $acc = $acc % 20;
//
//        if ($idtel != "x") {
//            $sqlup = "UPDATE `tbm_cliente_telefono` SET `favorito`= $acc WHERE `id_cliente_telefono` =  $idtel";
//            $r = mysqli_query($dbc2, $sqlup);
//        } else if ($idcorreo != "x") {
//            $sqlup = "UPDATE `tbm_cliente_correo` SET `favorito`=$acc WHERE `id_cliente_correo`= $idcorreo";
//            $r = mysqli_query($dbc2, $sqlup);
//        }
//    }
//    echo "<script>
//                    //window.location=\"$link\";
//                      </script>";
//}
//
//function AnularEliminarMuestra($agno, $idmuestra, $lab) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $ultimamuestrasol = "0";
//    $ultimamuestrageneral = "1";
//
//    //CONSEGUIMOS EL IDLAB DE LA MUESTRA QUE DESEAMOS ANULAR/ELIMINAR
//    $sqlmuestra = "SELECT idlab FROM tbm_solicitud_muestras WHERE id_muestras =  $idmuestra";
//    $rsolmuestra = mysqli_query($dbc, $sqlmuestra);
//    $muestrasol = mysqli_fetch_row($rsolmuestra);
//    $muestrasol = $muestrasol[0];
//
//    //Conseguimor el idlab máximo del laboratorio el el año actual
//    $sqlmuestra = "SELECT max(idlab) FROM tbm_solicitud_muestras WHERE agno = $agno and 
//                            id_solicitud in (SELECT id_solicitud FROM tbm_solicitud WHERE id_laboratorio = $lab and fecha > '$agno-01-01 00:00:01'  )";
//    $rsolmuestra = mysqli_query($dbc, $sqlmuestra);
//    $ultimamuestrageneral = mysqli_fetch_row($rsolmuestra);
//    $ultimamuestrageneral = $ultimamuestrageneral[0];
//
//    if ($muestrasol == $ultimamuestrageneral) {
//        $q = "DELETE FROM `tbm_solicitud_muestras` WHERE `id_muestras`= $idmuestra";
//        $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//        $q = "DELETE FROM `tbm_solicitud_muestras_analisis` WHERE `id_muestras`= $idmuestra";
//        $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//    } else {
//
//        $q = "UPDATE `tbm_solicitud_muestras` SET `estado`= 0 WHERE id_muestras= $idmuestra";
//        $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//    }
//}
//
//function agregarSolicitudContacto_correo($idcontacto, $idsolicitudcontacto, $idsolicitud, $tablasolicitudcontactocorreo, $tablasolicitudcontacto) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $dbc2 = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc2, "utf8");
//
////
////    
//    $sqlfs = "SELECT id_contacto_correo
//                  FROM tbm_cliente_contacto_correo
//                    WHERE id_contacto = $idcontacto and estado = 1 and favorito = 1";
//    $rx = mysqli_query($dbc, $sqlfs);
//
//    //if (!$r or $r->num_rows == 0) {
//    while ($fila = mysqli_fetch_row($rx)) {
//        $buscar = "Select * from  $tablasolicitudcontactocorreo 
//                        where $tablasolicitudcontactocorreo.id_solicitud = $idsolicitud and $tablasolicitudcontactocorreo.id_contacto_correo =  $fila[0]";
//
////            $buscar = "Select * from  " . $tablasolicitudcontactocorreo . 
////                    "  LEFT JOIN $tablasolicitudcontacto ON $tablasolicitudcontacto. where id_solicitud_contacto = $idsolicitudcontacto and id_contacto_correo =  $fila[0]";
//        $r2 = mysqli_query($dbc2, $buscar) or trigger_error("Query: $buscar\n<br />MySQL Error: " . mysqli_error($dbc2));
//        if (mysqli_num_rows($r2) == 0) {
//
//            $q = "INSERT INTO $tablasolicitudcontactocorreo (id_solicitud_contacto, id_contacto_correo, id_solicitud) 
//                            VALUES ($idsolicitudcontacto, $fila[0], $idsolicitud )";
//            $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//        }
//    }
//    //}
//    //
//}
//
//function agregarSolicitudContacto_telefono($idcontacto, $idsolicitudcontacto, $idsolicitud, $tablasolicitudcontactotelefono, $tablasolicitudcontacto) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $dbc2 = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc2, "utf8");
//
//    $sqlfs = "SELECT id_contacto_telefono
//                  FROM tbm_cliente_contacto_telefono
//                    WHERE id_contacto = $idcontacto and estado = 1 and favorito = 1";
//    $rx = mysqli_query($dbc, $sqlfs);
//    //if (!$r or $r->num_rows == 0) {
//    while ($fila = mysqli_fetch_row($rx)) {
//        $buscar = "Select * from $tablasolicitudcontactotelefono 
//                        where $tablasolicitudcontactotelefono.id_solicitud = $idsolicitud and $tablasolicitudcontactotelefono.id_contacto_telefono =  $fila[0]";
////            $buscar = "Select * from  " . $tablasolicitudcontactotelefono .
////                    " where id_solicitud_contacto = $idsolicitudcontacto and id_contacto_telefono =  $fila[0] ";
//        $r2 = mysqli_query($dbc2, $buscar) or trigger_error("Query: $buscar\n<br />MySQL Error: " . mysqli_error($dbc2));
//        if (mysqli_num_rows($r2) == 0) {
//            $q = "INSERT INTO $tablasolicitudcontactotelefono (id_solicitud_contacto, id_contacto_telefono, id_solicitud) 
//                            VALUES ($idsolicitudcontacto, $fila[0], $idsolicitud  )";
//            $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//        }
//    }
//    //}
//}
//
//function traerultimoidlab($idlaboratorio) {
//    $Fdbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($Fdbc, "utf8");
//
//    $condicionparaLaboratorios = "";
//    $idlab = 1;
//
//    switch ($idlaboratorio) {
//        case 3:
//        case 4:
//            $condicionparaLaboratorios = "(tbm_solicitud.id_laboratorio = 3 or tbm_solicitud.id_laboratorio = 4)";
//            break;
//        case 5:
//        case 6:
//        case 7:
//            $condicionparaLaboratorios = "(tbm_solicitud.id_laboratorio = 5 or tbm_solicitud.id_laboratorio = 6 or tbm_solicitud.id_laboratorio = 7)";
//            break;
//        default:
//            $condicionparaLaboratorios = "tbm_solicitud.id_laboratorio = $idlaboratorio";
//            break;
//    }
//    $sql = "SELECT tbm_solicitud_muestras.idlab 
//                FROM tbm_solicitud_muestras LEFT JOIN
//                     tbm_solicitud ON tbm_solicitud.id_solicitud = tbm_solicitud_muestras.id_solicitud
//                WHERE YEAR(tbm_solicitud.fecha) = YEAR(NOW()) and $condicionparaLaboratorios
//                    ORDER BY  tbm_solicitud_muestras.idlab  DESC LIMIT 1
//                
//            ";
//    $ridlabultimo = mysqli_query($Fdbc, $sql) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($Fdbc));
//    if (!$ridlabultimo or $ridlabultimo->num_rows == 0) {
//        $idlab = 1;
//    } else {
//        $fila = mysqli_fetch_row($ridlabultimo);
//        $idlab = $fila[0];
//        $idlab = $idlab + 1;
//    }
//
//    return $idlab;
//}
//
//function traerultimasolicitudmaterial($llaboratorio) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $ultimasolmaterial = 0;
//    if ($llaboratorio > 0) {
//        $sqlusm = "SELECT MAX( id_solicitud )
//                                            FROM tbm_solicitud";
//        $rusm = mysqli_query($dbc, $sqlusm);
//        $ultimasolmaterial = mysqli_fetch_row($rusm);
//        $ultimasolmaterial = $ultimasolmaterial[0];
//    }
//
//    return $ultimasolmaterial;
//}
//
////de 01/09 -> 01/SEP
//function formatearFecha1($ff) {
//    $mes = segString($ff, 3, 4);
//    $dia = segString($ff, 0, 1);
//    switch ($mes) {
//        case "01":
//        case "1":
//            $mes = "$dia-ENE";
//            break;
//        case "02":
//        case "2":
//            $mes = "$dia-FEB";
//            break;
//        case "03":
//        case "3":
//            $mes = "$dia-MAR";
//            break;
//        case "04":
//        case "4":
//            $mes = "$dia-ABR";
//            break;
//        case "05":
//        case "5":
//            $mes = "$dia-MAY";
//            break;
//        case "06":
//        case "6":
//            $mes = "$dia-JUN";
//            break;
//        case "07":
//        case "7":
//            $mes = "$dia-JUL";
//            break;
//        case "08":
//        case "8":
//            $mes = "$dia-AGO";
//            break;
//        case "09":
//        case "9":
//            $mes = "$dia-SEP";
//            break;
//        case "10":
//            $mes = "$dia-OCT";
//            break;
//        case "11":
//            $mes = "$dia-NOV";
//            break;
//        case "12":
//            $mes = "$dia-DIC";
//            break;
//    }
//    return $mes;
//}
//
////FUNCIONES PARA CIALAB
//function traemuestraidresultado($mat, $periodo, $idlab, $rep, $material, $idelemento) {
//    $idresultado = "";
//    $i = 0;
//    $j = count($mat);
//    while ($i < $j) {
//        if ($mat[$i][0] == $idlab &&
//                $mat[$i][3] == $periodo &&
//                $mat[$i][1] == $rep &&
//                $mat[$i][2] == $material &&
//                $mat[$i][9] == $idelemento
//        ) {
//            $idresultado = $mat[$i][6];
//            $i = j;
//        }
//
//        $i += 1;
//    }
//    return $idresultado;
//}
//
//function ingresararchivo($tabla1, $campoid, $periodo, $analista, $fecha, $archivo) {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    //$tabla = $tabla1 . "_muestra";
//    //$campoidarchivo = $campoid . "_muestra";
//
//    $q = "SELECT $campoid FROM $tabla1 WHERE periodo = $periodo and archivo = '$archivo' ";
//    $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//
//    $idarchivo = "";
//
//    if (mysqli_num_rows($r) == 0) { // Available.
//        //insert
//        $q = "INSERT INTO $tabla1
//                (periodo, analista,fecha,archivo) 
//              VALUES 
//                ($periodo, $analista, '$fecha', '$archivo')";
//        $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//
//        $idarchivo = traeultimo($tabla1, $campoid);
//
//        //echo $q . " $tabla1 $campoid    $idarchivo";
//    } else {
//        $idarchivo = mysqli_fetch_row($r);
//        $idarchivo = $idarchivo[0];
//    }
//    return $idarchivo;
//}
//
//function ingresarmuestra($tabla1, $campoid, $idarchivo, $idlab, $rep, $material, $tipo, $posicion, $estado = 1, $ri = 0) {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $tabla = $tabla1 . "_muestra";
//    $idmuestra = "";
//    //$campoidarchivo = $campoid . "_muestra";
//    //insert
//    //echo ", $idlab, $rep, $material,$tipo,$posicion,$estado<br>";
//
//    $q = "INSERT INTO $tabla 
//                ($campoid, idlab, rep,material,tipo,posicion,estado,ri) 
//              VALUES 
//                ($idarchivo, '$idlab', $rep, $material,$tipo,$posicion,$estado,$ri)";
//    $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//    $campoid .= "_muestra";
//    $idmuestra = traeultimo($tabla, $campoid);
//    return $idmuestra;
//}
//
//function gestionarresultadoICPMASAS($i, $mat, $matsql, $tabla, $campoid, $idmuestra, $factor = 1) {
//    //Litio (Li) = 57 col=4
//    //Vanadio(V) = 113 col=5
//    //Cromo(Cr) = 25 col=6
//    //Cobalto(Co) = 23 col=7
//    //Niquel(Ni) = 69 col=8
//    //Arsenico(As) = 6 col=9
//    //Selenio(Se) = 95 col=10
//    //Rubidio(Rb) = 89 col=11
//    //Molibdeno(Mo) = 64 col=12
//    //Cadmio(Cd) = 16 col=13
//    //Antimonio(Sb) = 4 col=14
//    //Bario(Ba) = 9 col=15
//    //Mercurio(Hg) = 63 col=16
//    //Plomo(Pb) = 78 col=17
//    $x = 4;
//    $y = 17;
//    $e = 0;
//    while ($x <= $y) {
//        switch ($x) {
//            case 4:
//                $e = 57;
//                break;
//            case 5:
//                $e = 113;
//                break;
//            case 6:
//                $e = 25;
//                break;
//            case 7:
//                $e = 23;
//                break;
//            case 8:
//                $e = 69;
//                break;
//            case 9:
//                $e = 6;
//                break;
//            case 10:
//                $e = 95;
//                break;
//            case 11:
//                $e = 89;
//                break;
//            case 12:
//                $e = 64;
//                break;
//            case 13:
//                $e = 16;
//                break;
//            case 14:
//                $e = 4;
//                break;
//            case 15:
//                $e = 9;
//                break;
//            case 16:
//                $e = 63;
//                break;
//            case 17:
//                $e = 78;
//                break;
//        }
//        $id = buscaridresultado($matsql, $idmuestra, $e);
//
//        $resultado = $mat[$i][$x];
//
//        //$idresultado = $mat[$id][6];
//
//        $resultado = cambiarpunto($resultado, ",");
//
//        if ($id != 0) {
//            actualizarresultado($tabla, $campoid, $id, $resultado, $factor);
//        } else {
//            ingresarresultado($tabla, $campoid, $idmuestra, $e, $resultado, $factor);
//        }
//        $x += 1;
//    }
//}
//
//function ingresarresultado($tabla1, $campoid, $idmuestra, $idelemento, $resultado, $factor = 1) {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $tabla = $tabla1 . "_resultado";
//
//    $campoidmuestra = $campoid . "_muestra";
//
//    //insert
//    $q = "INSERT INTO $tabla 
//                ($campoidmuestra, `id_elemento`, `resultado`, `factor`) 
//              VALUES 
//                ($idmuestra, $idelemento, '$resultado', $factor)";
//    $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//}
//
//function actualizarresultado($tabla1, $campoid, $idresultado, $resultado, $factor = 1) {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $tabla = $tabla1 . "_resultado";
//
//    $campoidresultado = $campoid . "_resultado";
//
//    $q = "UPDATE $tabla SET 
//                resultado = '$resultado',
//                factor = $factor
//                WHERE $campoidresultado = $idresultado ";
//    $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//}
//
//function buscaridmuestrai($mat, $id, $rep, $material) {
//
//    $i = 0;
//    $j = count($mat);
//    $encontrado = false;
//    $resp = "";
////echo "$id $rep $material<br>";
//    while ($i < $j && !$encontrado) {
//
//        if ($mat[$i][0] == $id &&
//                $mat[$i][1] == $rep &&
//                $mat[$i][2] == $material) {
//
//            $encontrado = true;
//            $resp = $i;
//            $i = $j;
//        }
//        $i += 1;
//    }
//    return $resp;
//}
//
//function buscaridmuestra($mat, $id, $rep, $material) {
//
//    $i = 0;
//    $j = count($mat);
//    $encontrado = false;
//    $resp = "";
//
//    while ($i < $j && !$encontrado) {
//
//        if ($mat[$i][0] == $id &&
//                $mat[$i][1] == $rep &&
//                $mat[$i][2] == $material) {
//
//            $encontrado = true;
//
//            $resp = $mat[$i][5];
//            $i = $j;
//        }
//        $i += 1;
//    }
//
//    return $resp;
//}
//
//function buscaridcontrol($mat, $id, $pos, $archivo) {
//
//    $i = 0;
//    $j = count($mat);
//    $encontrado = false;
//    $resp = "";
//
//    while ($i < $j && !$encontrado) {
//        //echo $mat[$i][0] . "- $id / "  . $mat[$i][10] . "- $pos / " . $mat[$i][11]  . "- $archivo--<br>";
//        if ($mat[$i][0] == $id &&
//                $mat[$i][10] == $pos &&
//                $mat[$i][11] == $archivo) {
//            $encontrado = true;
//            $resp = $mat[$i][5];
//            $i = $j;
//        }
//        $i += 1;
//    }
//    return $resp;
//}
//
//function buscaridresultado($matsql, $idmuestra, $ele) {
//
//    $i = 0;
//    $j = count($matsql);
//    $encontrado = false;
//    $resp = 0;
//
//    while ($i < $j && !$encontrado) {
//        //echo $matsql[$i][5] . " - $idmuestra /// " . $matsql[$i][7] . "-" .  $ele . "<br>";
//        if ($matsql[$i][5] == $idmuestra &&
//                $matsql[$i][7] == $ele) {
//            $encontrado = true;
//            $resp = $matsql[$i][6];
//            //$i = $j;
//        }
//        $i += 1;
//    }
//    // echo $resp . "xx<br>";
//    return $resp;
//}
//
//function convertirmaterial($id, $idlab, $agno, $tipomuestra, $materialanterior) {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    if ($tipomuestra == 2) {
//        if (vercadena2("AO", $id, false)) {
//            $id = "AOS";
//        } else if (vercadena2("CC", $id, false)) {
//            $id = "P";
//        } else if (vercadena2("IPE", $id, false)) {
//            $id = "P";
//        } else if (vercadena2("MARSEP", $id, false)) {
//            $id = "AOS";
//        } else if (vercadena2("PALMA", $id, false)) {
//            $id = "P";
//        } else if (vercadena2("CP", $id, false)) {
//            $id = "P";
//        } else if (vercadena2("BL", $id, false)) {
//            switch ($materialanterior) {
//                case 2:
//                    $id = "P";
//                    break;
//                case 3:
//                    $id = "AOS";
//                    break;
//            }
//        }
//    } else if ($tipomuestra == 1) {
//        switch ($id) {
//            case "AO":
//                $id = "AO";
//                break;
//            case "P":
//                $id = "P";
//                break;
//            case "A":
//                $id = "A";
//                break;
//            case "M":
//                $id = "M";
//                break;
//            case "F":
//                $id = "F";
//                break;
//            case "S":
//                $id = "S";
//                break;
//            default:
//                $id = "";
//                break;
//        }
//
//
//        $sql = "SELECT  tbm_solicitud.id_laboratorio,tbm_solicitud_muestras.idlab
//            FROM tbm_solicitud LEFT JOIN
//            tbm_solicitud_muestras on tbm_solicitud.id_solicitud = tbm_solicitud_muestras.id_solicitud
//            WHERE tbm_solicitud_muestras.idlab = $idlab and tbm_solicitud_muestras.agno = $agno and tbm_solicitud.id_laboratorio in (3,4,5,6,7)";
////echo $sql;
//
//        $i = 0;
//        $rr = mysqli_query($dbc, $sql);
//        while ($fila = mysqli_fetch_row($rr)) {
//            if($id == "AO" && $fila[0] == 3  ){
//                $id = "AOS";
//            }else if($id == "AO" && $fila[0] == 4  ){
//                $id = "AOL";
//            }else if($id == "F" && $fila[0] == 5  ){
//                $id = "FS";
//            }else if($id == "F" && $fila[0] == 6  ){
//                $id = "FL";
//            }else if($id == "F" && $fila[0] == 7  ){
//                $id = "FC";
//            }
//        }
//    }
//
//    switch ($id) {
//        case "S":
//            $material = 1;
//            break;        
//        case "P":
//            $material = 2;
//            break;
//        case "AOS":
//            $material = 3;
//            break;
//        case "AOL":
//            $material = 4;
//            break;       
//        case "FS":
//            $material = 5;
//            break;
//        case "FL":
//            $material = 6;
//            break;
//        case "FC":
//            $material = 7;
//            break;        
//        default:
//            $material = 0;
//            break;
//    }
//    return $material;
//}
//
//function convertirmaterialcontrol($id, $tipomuestra, $materialanterior) {
//
//    if ($tipomuestra == 2) {
//        if (vercadena2("AO", $id, false)) {
//            $id = "AOS";
//        } else if (vercadena2("CC", $id, false)) {
//            $id = "P";
//        } else if (vercadena2("IPE", $id, false)) {
//            $id = "P";
//        } else if (vercadena2("MARSEP", $id, false)) {
//            $id = "AOS";
//        } else if (vercadena2("PALMA", $id, false)) {
//            $id = "P";
//        } else if (vercadena2("CP", $id, false)) {
//            $id = "P";
//        } else if (vercadena2("BL", $id, false)) {
//            switch ($materialanterior) {
//                case 2:
//                    $id = "P";
//                    break;
//                case 3:
//                    $id = "AOS";
//                    break;
//            }
//        }
//    } else if ($tipomuestra == 1) {
//        switch ($id) {
//            case "AO":
//            case "AOS":
//                $id = "AOS";
//                break;
//            case "P":
//                $id = "P";
//                break;
//            default:
//                $id = "";
//                break;
//        }
//    }
//
//    switch ($id) {
//        case "P":
//            $material = 2;
//            break;
//        case "AOS":
//            $material = 3;
//            break;
//        default:
//            $material = 0;
//            break;
//    }
//    return $material;
//}
//
//function convertirmaterialatexto($material) {
//    switch ($material) {
//        case 2:
//            $material = "Foliares";
//            break;
//        case 1:
//            $material = "Suelos";
//            break;
//        case 3:
//            $material = "Abonos Orgánico Sólido";
//            break;
//        case 4:
//            $material = "Abonos Orgánico Líquido";
//            break;
//        case 5:
//            $material = "Fertilizante Sólido";
//            break;
//        case 6:
//            $material = "Fertilizante Líquido ";
//            break;
//        default:
//            $material = "";
//            break;
//    }
//    return $material;
//}
//
//function convertirmaterialatexto_muestra($material) {
//    switch ($material) {
//        case 2:
//            $material = "Foliares";
//            break;
//        case 1:
//            $material = "Suelos";
//            break;
//        case 3:
//            $material = "AO Sólido";
//            break;
//        case 4:
//            $material = "AO Líquido";
//            break;
//        case 5:
//            $material = "Fert Sólido";
//
//            break;
//        case 6:
//            $material = "Fert Líquido";
//            break;
//        case 7:
//            $material = "Fert Cal";
//            break;
//        case 8:
//            $material = "Aguas";
//
//            break;
//        default:
//            $material = "";
//            break;
//    }
//    return $material;
//}
//
//function sacarrep($s) {
//    $i = 0;
//    $j = 0;
//    $resp = "";
//    $i = 0;
//    $s = trim($s);
//    $j = strlen($s);
//    $s = strtoupper($s);
//
//    While ($i < $j) {
//
//        if ($s[$i] <> 'R' && (substr($s, -3) <> 'REP' )) {
//            $resp .= $s[$i];
//        } Else {
//            $j = $i;
//        }
//
//        $i += 1;
//    }
//
//
//    Return trim($resp);
//}
//
//function redondear($valor, $elemento) {
//
//    $valor = cambiarpunto($valor, ".");
//    $n = 3;
//    switch ($elemento) {
//
//        case "Li":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            //echo $valor . "<br>";
//            break;
//        case "V":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Cr":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Co":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Ni":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "As":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Se":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Rb":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Mo":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Cd":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Sb":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Ba":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Hg":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//        case "Pb":
//            $valor = number_format(round($valor, $n), $n, ',', '');
//            break;
//    }
//    $valor = cambiarpunto($valor, ",");
//
////    if ($valor == "0" || $valor == "-0"){
////        $valor = "0,00";
////    }
//
//    return $valor;
//}
//
//function cbelementos($i, $le, $te, &$ce) {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $m = "";
//    //$s = "";
//
//    $m = "<select class='form-control' name='cbelementos' id='cbelementos' width='4' style='font-weight: bold;font-size:18px;color:black'>";
//    $sql = "SELECT * FROM tlb_elemento order by nombre ";
//    $r = mysqli_query($dbc, $sql);
//    while ($fila = mysqli_fetch_row($r)) {
//        $f = $fila[0];
//        $ele = $fila[1];
//        $valido = true;
//        //$s = "";
//        $eleactual = $fila[2] . ",";
//        if (vercadena2($eleactual, $te, true)) {
//            if (vercadena2($eleactual, $le, false)) {
//                $valido = false;
//            }
//        } else {
//            $valido = false;
//        }
//
//        if ($valido) {
//            $ce += 1;
//            $m .= "<option value='$f'>$ele</option>";
//        }
//    }
//    $m .= "</select>";
//    return $m;
//}
//
//function esnumero($s) {
//
//    $i = 0;
//    $resp = true;
//    $tam = strlen($s);
//    //$cantidadcomas = 0;
//    if ($s != "" && $tam > 0) {
//        $tam -= 1;
//        if (substr($s, 0, 1) == "-") {
//            $i = 1;
//        }
////      while ($i <= $tam){
////          if(substr($s, $i, 1) < "0" || substr($s, $i, 1) > "9" ){
////              if($i < $tam && (substr($s, $i, 1) == "." || substr($s, $i, 1) == ",") ){
////    
////    
////                $i += 1;
////                while($i <= $tam){
////                    if((substr($s, $i, 1 < "0")) || (substr($s, $i, 1) > "9")){
////                        $resp = false;
////                    }
////                    if($i < $tam && (substr($s, $i, 1) == "." || substr($s, $i, 1) == ",") ){
////                       $resp = false;  
////                     }
////                    $i += 1;
////                }  
////              }else{
////                  $resp = false;
////              }
////          }
////          $i +=1;
////      }
//        while ($i <= $tam) {
//            if (!is_numeric(substr($s, $i, 1))) {
//                if ($i < $tam && (substr($s, $i, 1) == "." || substr($s, $i, 1) == ",")) {
//
//
//                    $i += 1;
//                    while ($i <= $tam) {
//                        if (!is_numeric(substr($s, $i, 1))) {
//                            $resp = false;
//                        }
//                        $i += 1;
//                    }
//                } else {
//                    $resp = false;
//                }
//            }
//            $i += 1;
//        }
//    } else {
//        $resp = false;
//    }
//
//
////echo $s . " /" . $resp . "/ "  ;
//
//
//
//
//
//
//    Return $resp;
//}
//
//function traerdatossolicitud($mat, $periodo) {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $foliares = "";
//    $suelos = "";
//    $aol = "";
//    $aos = "";
//    $fers = "";
//    $ferl = "";
//    $fercal = "";
//    $aguas = "";
//    $matsol = [];
//
//    //12 tipo
//    $i = 0;
//    $j = count($mat);
//    while ($i < $j) {
//        $tipo = $mat[$i][12];
//        $idlab = $mat[$i][0];
//        $material = $mat[$i][2];
//        
//        if ($tipo == 1) {
//            switch ($material) {
//                case 1:
//                    $suelos = metervalorcoma($suelos, $idlab);
//                    break;
//                case 2:
//                    $foliares = metervalorcoma($foliares, $idlab);
//                    break;
//                case 3:
//                case 4:    
//                    $aos = metervalorcoma($aos, $idlab);
//                    $aol = metervalorcoma($aol, $idlab);
//                    
//                    break;
//                case 5:
//                case 6:
//                case 7:
//                    $fers = metervalorcoma($fers, $idlab);
//                    $ferl = metervalorcoma($ferl, $idlab);
//                    $fercal = metervalorcoma($fercal, $idlab);
//                    break;
//                case 5:
//                    $aguas = metervalorcoma($aguas, $idlab);
//                    break;
//            }
//        }
//        $i += 1;
//    }
//
//    $foliares .= "0";
//
//    $suelos .= "0";
//
//    $aol .= "0";
//
//    $aos .= "0";
//
//    $fers .= "0";
//
//    $ferl .= "0";
//
//    $fercal .= "0";
//
//    $aguas .= "0";
//
//    //echo     $foliares . "f--" . $suelos  .  "s---"  .  $aol  .  "al--"  .  $aos  .  "as---" ;
//    $q = "SELECT 
//            tbm_solicitud.numero, 
//            tbm_solicitud.fecha, 
//            tbm_cliente.nombre,
//            tbm_cliente.nombre_comercial,
//            tbm_solicitud.id_dirpais,
//            tbm_dirpais.pais,
//            tbm_dirprovincia.provincia,
//            tbm_dircanton.canton,
//            tbm_solicitud_muestras.idlab,
//            tbm_solicitud_muestras.etiqueta,
//            tbm_solicitud.id_laboratorio, 
//            tbm_solicitud.id_solicitud
//            FROM tbm_solicitud LEFT JOIN
//            tbm_cliente ON tbm_cliente.id_cliente = tbm_solicitud.id_cliente LEFT JOIN
//            tbm_dirpais ON tbm_dirpais.id_pais = tbm_solicitud.id_dirpais LEFT JOIN
//            tbm_dirprovincia ON tbm_dirprovincia.id_provincia = tbm_solicitud.id_dirprovincia LEFT JOIN
//            tbm_dircanton ON tbm_dircanton.id_canton = tbm_solicitud.id_dircanton LEFT JOIN
//            tbm_solicitud_muestras ON tbm_solicitud_muestras.id_solicitud = tbm_solicitud.id_solicitud 
//            WHERE
//            tbm_solicitud_muestras.agno = $periodo and (
//            (tbm_solicitud.id_laboratorio = 1 and tbm_solicitud_muestras.idlab in ($suelos)) or
//            (tbm_solicitud.id_laboratorio = 2 and tbm_solicitud_muestras.idlab in ($foliares)) or
//            (tbm_solicitud.id_laboratorio = 3 and tbm_solicitud_muestras.idlab in ($aos)) or               
//            (tbm_solicitud.id_laboratorio = 4 and tbm_solicitud_muestras.idlab in ($aol))  or                              
//            (tbm_solicitud.id_laboratorio = 5 and tbm_solicitud_muestras.idlab in ($fers))  or              
//            (tbm_solicitud.id_laboratorio = 6 and tbm_solicitud_muestras.idlab in ($ferl))   or             
//            (tbm_solicitud.id_laboratorio = 7 and tbm_solicitud_muestras.idlab in ($fercal))  or                              
//            (tbm_solicitud.id_laboratorio = 8 and tbm_solicitud_muestras.idlab in ($aguas))                                
//
//            )
//            ";
//    $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//    $i = 0;
//  // echo $q;
//    $matsql = [];
//    while ($fila = mysqli_fetch_row($r)) {
//        $matsol[$i][0] = $fila[0];
//        $matsol[$i][1] = $fila[1];
//        $matsol[$i][2] = $fila[2];
//        $matsol[$i][3] = $fila[3];
//        $matsol[$i][4] = $fila[4];
//        $matsol[$i][5] = $fila[5];
//        $matsol[$i][6] = $fila[6];
//        $matsol[$i][7] = $fila[7];
//        $matsol[$i][8] = $fila[8];
//        $matsol[$i][9] = $fila[9];
//        $matsol[$i][10] = $fila[10];
//        $matsol[$i][11] = $fila[11];
//        $i += 1;
//    }
//
//    return $matsol;
//}
//
//function metervalorcoma($lista, $valor) {
//
//    $valor .= ",";
//    if (!vercadena2($valor, $lista, false)) {
//        $lista .= $valor;
//    }
//    return $lista;
//}
//
//function traerdatos($tabla, $campoid, $idarchivo, $filtro = "*") {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $tablaresultado = $tabla . "_resultado";
//    $tablamuestra = $tabla . "_muestra";
//    $campoidmuestra = $campoid . "_muestra";
//    $campoidresultado = $campoid . "_resultado";
//
//    if ($filtro == "m") {
//        $filtro = "and $tablamuestra.tipo = 1";
//    } else {
//        $filtro = "";
//    }
//
//    $sql = "SELECT 
//
//                $tablamuestra.idlab, 
//                $tablamuestra.rep, 
//                $tablamuestra.material, 
//                $tabla.periodo,                
//                $tabla.$campoid, 
//                $tablamuestra.$campoidmuestra,
//                $tablaresultado.$campoidresultado,
//                $tablaresultado.id_elemento,
//                $tablaresultado.resultado,
//                tlb_elemento.simbolo,
//                $tablamuestra.posicion,
//                $tabla.archivo,
//                $tablamuestra.tipo,
//                $tablamuestra.estado,
//                $tabla.observaciones,
//                $tablamuestra.ri
//                FROM 
//                $tabla LEFT JOIN 
//                $tablamuestra ON $tabla.$campoid = $tablamuestra.$campoid LEFT JOIN
//                $tablaresultado ON 
//                $tablaresultado.$campoidmuestra = $tablamuestra.$campoidmuestra LEFT JOIN
//                tlb_elemento ON tlb_elemento.id_elemento = $tablaresultado.id_elemento LEFT JOIN
//                tbl_persona ON tbl_persona.id_persona = $tabla.analista
//                WHERE
//                $tabla.$campoid = '$idarchivo' $filtro
//                order by $tablamuestra.posicion, $tablamuestra.idlab, $tablamuestra.rep
//                ";
//
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//    $i = 0;
//    //echo $sql;
//    $matsql = [];
//    while ($fila = mysqli_fetch_row($r)) {
//
//        $matsql[$i][0] = $fila[0];
//        $matsql[$i][1] = $fila[1];
//        $matsql[$i][2] = $fila[2];
//        $matsql[$i][3] = $fila[3];
//        $matsql[$i][4] = $fila[4];
//        $matsql[$i][5] = $fila[5];
//        $matsql[$i][6] = $fila[6];
//        $matsql[$i][7] = $fila[7];
//        $matsql[$i][8] = $fila[8];
//        $matsql[$i][9] = $fila[9];
//        $matsql[$i][10] = $fila[10];
//        $matsql[$i][11] = $fila[11];
//        $matsql[$i][12] = $fila[12];
//        $matsql[$i][13] = $fila[13];
//        $matsql[$i][14] = $fila[14];
//        $matsql[$i][15] = $fila[15];
//
//        $i += 1;
//    }
//    return $matsql;
//}
//
//function traerdatos_lista_conanulados($tabla, $campoid, $listaarchivos, $filtro = "*") {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $tablaresultado = $tabla . "_resultado";
//    $tablamuestra = $tabla . "_muestra";
//    $campoidmuestra = $campoid . "_muestra";
//    $campoidresultado = $campoid . "_resultado";
//
//    if ($filtro == "m") {
//        $filtro = "and $tablamuestra.tipo = 1";
//    } else {
//        $filtro = "";
//    }
//
//    $sql = "SELECT 
//
//                $tablamuestra.idlab, 
//                $tablamuestra.rep, 
//                $tablamuestra.material, 
//                $tabla.periodo,                
//                $tabla.$campoid, 
//                $tablamuestra.$campoidmuestra,
//                $tablaresultado.$campoidresultado,
//                $tablaresultado.id_elemento,
//                $tablaresultado.resultado,
//                tlb_elemento.simbolo,
//                $tablamuestra.posicion,
//                $tabla.archivo,
//                $tablamuestra.tipo,
//                $tablamuestra.estado,
//                $tabla.observaciones,
//                $tablamuestra.ri                    
//                FROM 
//                $tabla LEFT JOIN 
//                $tablamuestra ON $tabla.$campoid = $tablamuestra.$campoid LEFT JOIN
//                $tablaresultado ON 
//                $tablaresultado.$campoidmuestra = $tablamuestra.$campoidmuestra LEFT JOIN
//                tlb_elemento ON tlb_elemento.id_elemento = $tablaresultado.id_elemento LEFT JOIN
//                tbl_persona ON tbl_persona.id_persona = $tabla.analista
//                WHERE
//                $tabla.$campoid in ($listaarchivos) $filtro
//                order by $tabla.$campoid, $tablamuestra.posicion, $tablamuestra.idlab, $tablamuestra.rep
//                ";
//
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//    $i = 0;
//
//    $matsql = [];
//    while ($fila = mysqli_fetch_row($r)) {
//
//        $matsql[$i][0] = $fila[0];
//        $matsql[$i][1] = $fila[1];
//        $matsql[$i][2] = $fila[2];
//        $matsql[$i][3] = $fila[3];
//        $matsql[$i][4] = $fila[4];
//        $matsql[$i][5] = $fila[5];
//        $matsql[$i][6] = $fila[6];
//        $matsql[$i][7] = $fila[7];
//        $matsql[$i][8] = $fila[8];
//        $matsql[$i][9] = $fila[9];
//        $matsql[$i][10] = $fila[10];
//        $matsql[$i][11] = $fila[11];
//        $matsql[$i][12] = $fila[12];
//        $matsql[$i][13] = $fila[13];
//        $matsql[$i][14] = $fila[14];
//        $matsql[$i][15] = $fila[15];
//
//        $i += 1;
//    }
//    return $matsql;
//}
//
//function traerdatos_lista($tabla, $campoid, $listaarchivos, $filtro = "*") {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $tablaresultado = $tabla . "_resultado";
//    $tablamuestra = $tabla . "_muestra";
//    $campoidmuestra = $campoid . "_muestra";
//    $campoidresultado = $campoid . "_resultado";
//
//    if ($filtro == "m") {
//        $filtro = "and $tablamuestra.tipo = 1";
//    } else {
//        $filtro = "";
//    }
//
//    $sql = "SELECT 
//
//                $tablamuestra.idlab, 
//                $tablamuestra.rep, 
//                $tablamuestra.material, 
//                $tabla.periodo,                
//                $tabla.$campoid, 
//                $tablamuestra.$campoidmuestra,
//                $tablaresultado.$campoidresultado,
//                $tablaresultado.id_elemento,
//                $tablaresultado.resultado,
//                tlb_elemento.simbolo,
//                $tablamuestra.posicion,
//                $tabla.archivo,
//                $tablamuestra.tipo,
//                $tablamuestra.estado,
//                $tabla.observaciones
//                FROM 
//                $tabla LEFT JOIN 
//                $tablamuestra ON $tabla.$campoid = $tablamuestra.$campoid LEFT JOIN
//                $tablaresultado ON 
//                $tablaresultado.$campoidmuestra = $tablamuestra.$campoidmuestra LEFT JOIN
//                tlb_elemento ON tlb_elemento.id_elemento = $tablaresultado.id_elemento LEFT JOIN
//                tbl_persona ON tbl_persona.id_persona = $tabla.analista
//                WHERE
//                $tablamuestra.estado > 0 and $tabla.$campoid in ($listaarchivos) $filtro
//                order by $tabla.$campoid, $tablamuestra.posicion, $tablamuestra.idlab, $tablamuestra.rep
//                ";
//
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//    $i = 0;
//
//    $matsql = [];
//    while ($fila = mysqli_fetch_row($r)) {
//
//        $matsql[$i][0] = $fila[0];
//        $matsql[$i][1] = $fila[1];
//        $matsql[$i][2] = $fila[2];
//        $matsql[$i][3] = $fila[3];
//        $matsql[$i][4] = $fila[4];
//        $matsql[$i][5] = $fila[5];
//        $matsql[$i][6] = $fila[6];
//        $matsql[$i][7] = $fila[7];
//        $matsql[$i][8] = $fila[8];
//        $matsql[$i][9] = $fila[9];
//        $matsql[$i][10] = $fila[10];
//        $matsql[$i][11] = $fila[11];
//        $matsql[$i][12] = $fila[12];
//        $matsql[$i][13] = $fila[13];
//        $matsql[$i][14] = $fila[14];
//
//        $i += 1;
//    }
//    return $matsql;
//}
//
//function consulta_traerdaatos_matsolicitudes($tabla, $campoid, $lista, $lab, $periodo) {
//
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $tablaresultado = $tabla . "_resultado";
//    $tablamuestra = $tabla . "_muestra";
//    $campoidmuestra = $campoid . "_muestra";
//    $campoidresultado = $campoid . "_resultado";
//
//    $resp = "";
//    $listaidlabs = "";
//    if ($lista != "") {
//
//        $sql = "SELECT 
//            tbm_solicitud_muestras.idlab ,
//            tbm_solicitud_muestras.etiqueta
//            FROM tbm_solicitud_muestras 
//            WHERE tbm_solicitud_muestras.id_solicitud in ($lista)";
//
//        $rr = mysqli_query($dbc, $sql);
//        while ($fila = mysqli_fetch_row($rr)) {
//            if ($listaidlabs != "") {
//                $listaidlabs .= ",";
//            }
//            $listaidlabs .= $fila[0];
//        }
//        if ($listaidlabs != "") {
//            $resp = "SELECT 
//
//                $tablamuestra.idlab, 
//                $tablamuestra.rep, 
//                $tablamuestra.material, 
//                $tabla.periodo,                
//                $tabla.$campoid, 
//                $tablamuestra.$campoidmuestra,
//                $tablaresultado.$campoidresultado,
//                $tablaresultado.id_elemento,
//                $tablaresultado.resultado,
//                tlb_elemento.simbolo,
//                $tablamuestra.posicion,
//                $tabla.archivo,
//                $tablamuestra.tipo,
//                $tablamuestra.estado,
//                $tabla.observaciones,
//                tbm_solicitud_muestras.etiqueta,
//                tbm_solicitud_muestras.id_solicitud
//                FROM 
//                $tabla LEFT JOIN 
//                $tablamuestra ON $tabla.$campoid = $tablamuestra.$campoid LEFT JOIN
//                $tablaresultado ON 
//                $tablaresultado.$campoidmuestra = $tablamuestra.$campoidmuestra LEFT JOIN
//                tlb_elemento ON tlb_elemento.id_elemento = $tablaresultado.id_elemento LEFT JOIN
//                tbl_persona ON tbl_persona.id_persona = $tabla.analista LEFT JOIN
//                tbm_solicitud_muestras ON tbm_solicitud_muestras.idlab =  $tablamuestra.idlab   
//                WHERE
//                tbm_solicitud_muestras.id_solicitud in ($lista) and  $tablamuestra.material in ($lab ) and 
//                    $tabla.periodo = $periodo
//                    
//               
//                ";
//        }
//    }
//
//    return $resp;
//}
//
//function traerdatos_matsolicitudes($tabla, $campoid, $matsolicitudes, $material, $periodo, $filtro = "*") {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//
//    $tablaresultado = $tabla . "_resultado";
//    $tablamuestra = $tabla . "_muestra";
//    $campoidmuestra = $campoid . "_muestra";
//    $campoidresultado = $campoid . "_resultado";
//
//    if ($filtro == "m") {
//        $filtro = "and $tablamuestra.tipo = 1";
//    } else {
//        $filtro = "";
//    }
//
//
//    $listasolicitudesP = $matsolicitudes[1];
//    $listasolicitudesAOS = $matsolicitudes[2];
//    $listasolicitudesAOL = $matsolicitudes[3];
//    $listasolicitudesFS = $matsolicitudes[4];
//    $listasolicitudesFL = $matsolicitudes[5];
//    $listasolicitudesA = $matsolicitudes[6];
//
//    switch ($material) {
//        case "P":
//            $sqlP = consulta_traerdaatos_matsolicitudes($tabla, $campoid, $listasolicitudesP, 2, $periodo);
//            $sql = "$sqlP  order by CONVERT(tsld_icpmasas_muestra.idlab, UNSIGNED INTEGER), tsld_icpmasas_muestra.rep";
//            //echo $sql;
//            break;
//        case "AO":
//            $sqlAOS = consulta_traerdaatos_matsolicitudes($tabla, $campoid, $listasolicitudesAOS, 3, $periodo);
//            $sqlAOL = consulta_traerdaatos_matsolicitudes($tabla, $campoid, $listasolicitudesAOL, 4, $periodo);
//            $sql = "$sqlAOS UNION $sqlAOL order by CONVERT(idlab, UNSIGNED INTEGER), rep";
//            break;
//        case "AOS":
//            $sqlAOS = consulta_traerdaatos_matsolicitudes($tabla, $campoid, $listasolicitudesAOS, 3, $periodo);
//            $sql = "$sqlAOS order by CONVERT(tsld_icpmasas_muestra.idlab, UNSIGNED INTEGER), tsld_icpmasas_muestra.rep";
//            break;
//        case "AOL":
//            $sqlAOL = consulta_traerdaatos_matsolicitudes($tabla, $campoid, $listasolicitudesAOL, 4, $periodo);
//            break;
//        case "FS":
//            $sqlFS = consulta_traerdaatos_matsolicitudes($tabla, $campoid, $listasolicitudesFS, 5, $periodo);
//            break;
//        case "FL":
//            $sqlFL = consulta_traerdaatos_matsolicitudes($tabla, $campoid, $listasolicitudesFL, 6, $periodo);
//            break;
//        case "FC":
//            break;
//        case "A":
//            $sqlA = consulta_traerdaatos_matsolicitudes($tabla, $campoid, $listasolicitudesA, 8, $periodo);
//            break;
//    }
//
//
//
//
//
//
//
//
//
//
//    //echo $sql;
//    $r = mysqli_query($dbc, $sql) or trigger_error("Query: $sql\n<br />MySQL Error: " . mysqli_error($dbc));
//    $i = 0;
//
//    $matsql = [];
//    while ($fila = mysqli_fetch_row($r)) {
//
//        $matsql[$i][0] = $fila[0];
//        $matsql[$i][1] = $fila[1];
//        $matsql[$i][2] = $fila[2];
//        $matsql[$i][3] = $fila[3];
//        $matsql[$i][4] = $fila[4];
//        $matsql[$i][5] = $fila[5];
//        $matsql[$i][6] = $fila[6];
//        $matsql[$i][7] = $fila[7];
//        $matsql[$i][8] = $fila[8];
//        $matsql[$i][9] = $fila[9];
//        $matsql[$i][10] = $fila[10];
//        $matsql[$i][11] = $fila[11];
//        $matsql[$i][12] = $fila[12];
//        $matsql[$i][13] = $fila[13];
//        $matsql[$i][14] = $fila[14];
//        $matsql[$i][15] = $fila[15];
//        $matsql[$i][16] = $fila[16];
//
//        $i += 1;
//    }
//    //echo $sql . "//" .count($matsql);
//    return $matsql;
//}
//
//function imprimirmatriz($datos) {
//    $x = 0;
//    $y = count($datos);
//    $a = 0;
//    $b = 0;
//    // echo "cantidad $y <br>";
//    while ($x < $y) {
//        $primeraFila = $datos[$x]; // Obtenemos la primera fila de la matriz
//        $a = 0;
//        $b = count($primeraFila);
//        //echo $b;
//        while ($a < $b) {
//            if ($a < 12) {
//                echo " | " . $datos[$x][$a] ;
//            } else {
//                echo "---";
//            }
//            $a += 1;
//        }
//        echo "<br>";
//        $x += 1;
//    }
//}
//
//function select_agno($agno, $id = "") {
//
//    $yy = date("Y");
//    if ($id == "") {
//        $sel = "<div class='col-sm-2'>
//            <select class='form-control' name='agno' id='agno' onChange=\"cambiaragno(this.value)\"  style='font-family : \"Helvetica Neue\"; font-size : 20pt; width:110px; height:40px; '  >";
//    } else {
//        if ($id == "n") {
//            $id = "";
//        }
//        $sel = "<div class='col-sm-2'>
//            <select class='form-control' name='agno' id='agno' onChange=\"cambiaragno2(this.value,'$id')\"  style='font-family : \"Helvetica Neue\"; font-size : 20pt; width:110px; height:40px; '  >";
//    }
//
//    $i = 2010;
//    $j = 0;
//    while ($yy >= $i) {
//        if ($yy == $agno) {
//            $sel .= "<option value='$yy' selected >$yy</option>";
//        } else {
//            $sel .= "<option value='$yy'>$yy</option>";
//        }
//
//        $yy -= 1;
//        $j += 1;
//    }
//
//    $sel .= "</select></div>";
//
//    return $sel;
//}
//function select_agnoenvio($agno, $id = "") {
//
//    $yy = date("Y");
//    if ($id == "") {
//        $sel = "<div class='col-sm-2'>
//            <select class='form-control' name='cbagno' id='cbagno' onChange=\"cambiaragno(this.value)\"  style='font-family : \"Helvetica Neue\"; font-size : 20pt; width:110px; height:40px; '  >";
//    } else {
//        if ($id == "n") {
//            $id = "";
//        }
//        $sel = "<div class='col-sm-2'>
//            <select class='form-control' name='cbagno' id='cbagno' onChange=\"cambiaragno2(this.value,'$id')\"  style='font-family : \"Helvetica Neue\"; font-size : 20pt; width:110px; height:40px; '  >";
//    }
//
//    $i = 2010;
//    $j = 0;
//    while ($yy >= $i) {
//        if ($yy == $agno) {
//            $sel .= "<option value='$yy' selected >$yy</option>";
//        } else {
//            $sel .= "<option value='$yy'>$yy</option>";
//        }
//
//        $yy -= 1;
//        $j += 1;
//    }
//
//    $sel .= "</select></div>";
//
//    return $sel;
//}
//
//function campofecha($fecha, $calendario, $nombre = "calendario1") {
//
//    $resp = "";
//
//    $fecha = convertirfecha($fecha);
//
//    $resp = "
//                <input  type='text' class='form-control daterange' data-format='DD/MM/YYYY' data-start-date='$fecha' data-end-date='$fecha' data-separator=' - '  name='$nombre' id='$nombre' style='font-family : \"Helvetica Neue\"; font-size : 20pt; width:400px; height:40px; ' maxlength='0'>                
//            ";
//
//    return $resp;
//}
//function campofecha_simple($fecha, $calendario, $nombre = "calendario1") {
//
////    $resp = "";
////
////    $fecha = convertirfecha($fecha);
////
////    $resp = "
////                <input  type='text' class='form-control daterange' data-format='DD/MM/YYYY' data-start-date='$fecha' data-end-date='$fecha' data-separator=' - '  name='$nombre' id='$nombre' style='font-family : \"Helvetica Neue\"; font-size : 20pt; width:400px; height:40px; ' maxlength='0'>                
////            ";
////
////    return $resp;
//}
//function resumirconteo($lista, $tipo = 0) {
//
//    $l = "";
//    if ($tipo == "sols") {
//        $l = explode(",", $lista);
//    } else {
//        $l = explode(",", substr($lista, 0, -1));
//    }
//    $i = 0;
//    $j = count($l);
//    $ant = -1;
//    $act = "";
//    $resp = "";
//    $ultimodelalista = 0;
//    $pendiente = "";
//    $prependiente = "";
//    $ri = "";
//    $riant = "";
//    while ($i < $j) {
//        $ri = "";
//        $riant = "";
//        if (vercadena2("R", $l[$i], false)) {
//            $ri = "R";
//            $l[$i] = sacarrep($l[$i]);
//        }
//        if (vercadena2("R", $ant, false)) {
//            $riant = "R";
//            $ant = sacarrep($ant);
//        }
//        // echo  $l[$i] . "<br>";
//        $act = preg_replace('/[^0-9]/', '', $l[$i]);
//        $pre = preg_replace("/[^a-zA-Z]+/", "", $l[$i]);
//
//        //echo $l[$i]."<br>";
//        if (esnumero($act) && ($act - 1) > $ant) {
//            if ($pendiente != "") {
//                if (($ultimodelalista + 1) == $pendiente) {
//                    $resp .= ", " . $prependiente . $pendiente;
//                } else {
//                    $resp .= " - " . $prependiente . $pendiente;
//                }
//
//
//                $pendiente = "";
//            }
//            if ($resp != "") {
//                $resp .= ", ";
//            }
//            $resp .= $pre . $act . $ri;
//        } else if (esnumero($act) && ($act - 1) == $ant && ($i + 1) < $j) {
//
//            if ($pendiente == "") {
//                $ultimodelalista = $ant . $riant;
//            }
//            $pendiente = $act . $ri;
//            $prependiente = $pre;
//        } else {
//            //echo $act . "--" .  $i. "--" . $j. "  $pendiente  $ultimodelalista<br>" ;
//            if (esnumero($act)) {
//                if ($pendiente != "") {
//                    if (($ultimodelalista + 1) == $pendiente) {
//                        if (($pendiente + 1) != $act) {
//                            $resp .= ", " . $prependiente . $pendiente;
//                            if ($resp != "") {
//                                $resp .= ", ";
//                            }
//                        } else {
//                            $resp .= " - ";
//                        }
//                    } else {
//                        $resp .= " - " . $prependiente . $pendiente;
//                        if ($resp != "") {
//                            $resp .= ", ";
//                        }
//                    }
//                    $pendiente = "";
//                }
//
//
//
//                $resp .= $pre . $act . $ri;
//            }
//        }
//
//        if (($i + 1) == $j && $pendiente != "") {
//
//            $resp .= " - " . $pendiente;
//        }
//
//        $ant = $act . $ri;
//
//        $i += 1;
//    }
//
//    //echo $resp ;
//
//    return $resp;
//}
//
//function convertirlistamuestras($lista) {
//    $resp = "";
//    $n = 1;
//    $i = 0;
//    $j = count($lista);
//    while ($i < $j) {
//        $resp .= $n . ". " . $lista[$i] . "<br>";
//        $n += 1;
//        $i += 1;
//    }
//
//    return $resp;
//}
//
//function desvest($datos) {
//    // Calcular la media
//    $media = array_sum($datos) / count($datos);
//
//    // Calcular la suma de los cuadrados de las diferencias
//    $diferenciasCuadradas = array_map(function ($dato) use ($media) {
//        return pow($dato - $media, 2);
//    }, $datos);
//
//    $sumaDiferenciasCuadradas = array_sum($diferenciasCuadradas);
//
//    // Calcular la desviación estándar muestral
//    $desviacionEstandarMuestral = sqrt($sumaDiferenciasCuadradas / (count($datos) - 1));
//
//    return $desviacionEstandarMuestral;
//}
//
//function anularmuestra($tablamuestra, $campoidmuestra, $idmue, $est) {
//    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $q = "UPDATE $tablamuestra SET 
//                estado =  $est
//                WHERE $campoidmuestra = $idmue ";
//    $r = mysqli_query($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
//}
//
//function calcular_resultados(&$elep, &$ele_desv, &$ele_cv, $ele, $ele_desv_arr, $eleacu) {
//
//
//    if ($elep != 0) {
//
//        $elep = $eleacu / $elep;
//    } else {
//        $elep = "";
//    }
//
//    $ele_desv = desvest($ele_desv_arr);
//
//    if ($elep != "" && $elep != 0) {
//
//
//        $ele_cv = redondear(($ele_desv / $elep) * 100, $ele);
//    } else {
//        $ele_cv = "NA";
//    }
//
//
//    $ele_desv = redondear($ele_desv, $ele);
//    if ($elep != "") {
//
//
//        $elep = redondear($elep, $ele);
//    } else {
//        $elep = "0";
//    }
//}
//
//function colores($ele, $me, $ma) {
//    $ele2 = cambiarpunto($ele, ".");
//
//    if ($ele2 > $ma) {
//        $ele = "<button type='button' class='btn btn-gold'><font size='3' color='black'><strong>$ele</strong></font></button>";
//    } else if ($ele2 < $me) {
//        $ele = "<button type='button' class='btn btn-info'><font size='3' color='black'><strong>$ele</strong></font></button>";
//    }
//    return $ele;
//}
//
//function colores2($dato, $ele, $me, $ma) {
//    $dato2 = cambiarpunto($dato, ".");
//
//    if ($dato2 > $ma) {
//        $dato2 = redondear($dato2, $ele);
//        $dato = cambiarpunto($dato2, ",");
//        $dato = "<button type='button' class='btn btn-gold'><font size='3' color='black'><strong>$dato</strong></font></button>";
//    } else if ($dato2 < $me) {
//        $dato2 = redondear($dato2, $ele);
//        $dato = cambiarpunto($dato2, ",");
//        $dato = "<button type='button' class='btn btn-info'><font size='3' color='black'><strong>$dato</strong></font></button>";
//    }
//    //echo $dato . "<br><br>";
//    return $dato;
//}
//
//function colorescriticos($ele, $me, $ma) {
//    $ele2 = cambiarpunto($ele, ".");
//
//    if ($ele2 < $me) {
//        $ele = "<button type='button' class='btn btn-default disabled'><font size='3' color='red'><strong>$ele</strong></font></button>";
//    } else if ($ele2 < $ma) {
//        $ele = "<font size='3' color='red'><strong>$ele</strong></font>";
//    } else {
//        $ele = "<font size='3' color='black'><strong>$ele</strong></font>";
//        //<font color='black'><strong>$lip</strong></font>
//    }
//    return $ele;
//}
//
//function colorescv($ele, $me, $ma) {
//
//    //$ele = $ele * 100;
//    $ele2 = cambiarpunto($ele, ".");
//    //$ele2 = $ele2 *100;
//
//
//    if ($ele2 > $me) {
//        //$ele = "<button type='button' class='btn btn-default disabled'><font size='3' color='red'><strong>$ele</strong></font></button>";
//        //$ele = $ele * 100;
//        $ele2 = number_format(round($ele2, 1), 1, ',', '');
//
//        $ele2 = cambiarpunto($ele2, ",");
//        $ele = "<font size='3' color='red'><strong>$ele2%</strong></font>";
//    } else if ($ele2 > $ma) {
//        //$ele = "<button type='button' class='btn btn-default disabled'><font size='3' color='red'><strong>$ele</strong></font></button>";
//        $ele2 = number_format(round($ele2, 1), 1, ',', '');
//        $ele2 = cambiarpunto($ele2, ",");
//        $ele = "<font size='3' color='red'><strong>$ele2%</strong></font>";
//    } else {
//        $ele2 = number_format(round($ele2, 1), 1, ',', '');
//        $ele2 = cambiarpunto($ele2, ",");
//        $ele = "<font size='3' color='black'><strong>$ele2%</strong></font>";
//        //<font color='black'><strong>$lip</strong></font>
//    }
//    return $ele;
//}
//
//function validarnumeroICPMASAS($d, $factordilusion) {
//
//    if (is_numeric($d) && $factordilusion != 0) {
//        $d = $d / $factordilusion;
//    }
//
//    return $d;
//}
//
//function buscarinfo($matsol, $idlab, &$material, $mat_app, $link) {
//    $i = 0;
//    $j = count($matsol);
//    $info = "";
//    $fecha = "";
//    $nosol = "";
//    $lugar = "";
//    $cliente = "";
//    $material2 = 0;
//    $a = 0;
//    $b = count($mat_app);
//
//    while ($i < $j) {
//        $idsol = $matsol[$i][11];
//        //echo $idsol . "<br>";
////        if ($matsol[$i][10] == 4) {
////            $material2 = 3;
////        } else if ($matsol[$i][10] == 5 || $matsol[$i][10] == 6 || $matsol[$i][10] == 7) {
////            $material2 = 4;
////        } else if ($matsol[$i][10] == 8) {
////            $material2 = 5;
////        } else {
////            $material2 = $material;
////        }
////
////        if ($matsol[$i][8] == $idlab &&
////                $material2 == $material) {
//        if ($matsol[$i][8] == $idlab ) {            
//            $fecha = segString(convertirfecha2($matsol[$i][1]), 0, 10);
//            $nosol = $matsol[$i][0];
//            if ($matsol[$i][3] == "") {
//                $cliente = $matsol[$i][2];
//            } else {
//                $cliente = $matsol[$i][3];
//            }
//            if ($matsol[$i][4] == 1) {
//                $lugar = $matsol[$i][7];
//            } else {
//                $lugar = $matsol[$i][5];
//            }
//            $material = $matsol[$i][10];
//
//            $i = $j;
//        }
//        $i += 1;
//    }
//
//    if ($nosol != "") {
//
//        $botones = "<a href='$link&idsol=$idsol&app=1' class='btn btn-green btn-sm btn-icon icon-left'><p class='entypo-check'></p></a>
//            <a href='$link&idsol=$idsol&app=2' class='btn btn-orange btn-sm btn-icon icon-left'><p class='entypo-cancel'></p></a>";
//        $encontrado = false;
//        $a = 0;
//        while ($a < $b && !$encontrado) {
//
//            if ($idsol == $mat_app[$a][2]) {
//                $encontrado = true;
//            }
//            $a += 1;
//        }
//        if ($encontrado == true) {
//            $botones = "";
//            $a -= 1;
//            if ($mat_app[$a][3] == 1) {
//                $nosol = "<font size='4' color='green'>SOL." . $nosol . "</font>";
//            } else {
//                $nosol = "<font size='4' color='orange'>SOL." . $nosol . "</font>";
//            }
//        } else {
//            $nosol = "<font size='4' color='black'>SOL." . $nosol . "</font>";
//        }
//
//
//        $info = "<strong>$nosol</strong><br>
//            
//            $botones
//            
//            <br>" . $lugar . "<br><strong>" . $cliente . "</strong><br>" . $fecha;
//    }
//
//
//    return $info;
//}
//function aprobar($idpersona, $idarchivo, $link,$tipo, $pagina) {
//   $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//  
//
//    mysqli_set_charset($dbc, "utf8");
////echo "holaaaaa<br>" ;
//    $texto = "";
//    $BOTONAPROBAR = "";
//    $aprobaciones = "<br><br>";
//    $aprobaciones2 = "";
//    //$mostrarboton = true; 
//    $sql = "SELECT  
//            tbl_persona_siglas.siglas
//            FROM tbl_persona_siglas
//            WHERE tbl_persona_siglas.id_persona = $idpersona";
//
//    $rr = mysqli_query($dbc, $sql);
//
//    $sql2 = "SELECT  
//            tsld_icpmasas_revision.fecha,
//            tsld_icpmasas_revision.observaciones,
//            tbl_persona_siglas.siglas,
//            tbl_persona_siglas.id_persona
//            FROM tsld_icpmasas_revision left join
//            tbl_persona_siglas ON tbl_persona_siglas.id_persona = tsld_icpmasas_revision.id_persona
//            WHERE tsld_icpmasas_revision.id_archivo = $idarchivo and tipo = $tipo and tsld_icpmasas_revision.pagina = $pagina";
//
//    $rr2 = mysqli_query($dbc, $sql2);
//
//    while ($fila = mysqli_fetch_row($rr2)) {
//
//
//        if($fila[3] == $idpersona){
//            //$mostrarboton = false;
//            $texto = $fila[1];
//        }//else{
//            $siglas = $fila[2];
//            $aprobaciones2 .= "<li>" . $fila[1] . " [ " . $fila[2]  . " - " . convertirfecha2($fila[0]) . " ] " . "</li>";
//        //}
//    }
//    if ($aprobaciones2 != "") {
//        $aprobaciones .= "<ul>";
//        $aprobaciones .= $aprobaciones2;
//        $aprobaciones .= "</ul>";
//        $aprobaciones = "<font size=3 color=darkgreen > $aprobaciones</font>";
//    }
//    
//
//    $aprobaciones .= "<br>";
//
//
//
//
////        $BOTONAPROBAR .= "
////		<div class='col-sm-5'>
////                    <div class='input-group'>
////			<input type='text' class='form-control' maxlength='500' id='obb' name='obb' value=''>
////                            <span class='input-group-btn'>
////                                <a onClick='aprobarjs($idarchivo, $idpersona, obb.value, $pagina);'>
////				<button class='btn btn-success' type='button'>Aprobar</button>
////                                </a>
////                            </span>
////                    </div>
////		</div>
////                 <br><br>
////                        "
////        ;
//    return $aprobaciones;
//}
//
//
//function enviarcorreo($informe, $correo, $nombre ,$dir, $mail, $archivos) {
//    $mail->IsHTML(true);
//    $mail->CharSet = 'UTF-8';
//
//    $mail->PluginDir = "includes/";
//
//    //para el envío en formato HTML 
//    $headers = "MIME-Version: 1.0\r\n";
//    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
//
//    $correo .= "                       
//
//<table style='border:0; padding:2px; vertical-align:middle; width:700px; height:80px; border:1px black solid;'>
//<tr>
//<td style='width:33%; border:0; padding:10px; vertical-align:middle;'>
//<a href='http://www.ucr.ac.cr/'>
//<img src='http://www.cia.ucr.ac.cr/wp-content/gallery/galeria_cia/firma-ucr.png' width='180' height='68' alt='Universidad de Costa Rica' /></a>
//</td>
//<td style='width:33%; border:0; padding:10px; vertical-align:middle;'>
//<a href='http://www.cia.ucr.ac.cr/'><img src='http://www.cia.ucr.ac.cr/wp-content/gallery/galeria_cia/firma-cia.png' alt='Unidad de la UCR' /></a>
//</td>
//<td style='width:33%; border:0; padding:10px; vertical-align:middle; color:white; background:#00c0f3; font-family:arial;font-size:14px;'>
//$nombre<br />
//Recepción de Muestras<br />
//(506) 2511-2054<br />
//<a href='muestras.cia@ucr.ac.cr' style='color:white;'>muestras.cia@ucr.ac.cr</a><br />
//</td>
//</tr>
//</table>
//<br />
//<br />
//";
////$correo = $correo;
//    //$asunto = "Aviso: " . $informe . " - " . $nombre;
//    $asunto = $informe;
//
//    $from = "muestras.cia@ucr.ac.cr";
//    $dir_correo = $dir;
//
//    $mail->IsSMTP();
//    $mail->Mailer = "smtp";
//    $mail->Host = "smtp.ucr.ac.cr";
//    $mail->Port = "25"; // 8025, 587 and 25 can also be used. Use Port 465 for SSL.
//    $mail->SMTPAuth = true;
//    //$mail->SMTPSecure = 'tls';
//    $mail->Username = "muestras.cia";
//    $mail->Password = "patri55011965.";
//
//    $mail->setFrom('muestras.cia@ucr.ac.cr', 'Recepción de Muestras CIA-UCR');
//
//    /* Add a recipient. */
//
//
//    $dir = explode(";", $dir);
//    $j = count($dir);
//    $i = 0;
//    while ($i < $j) {
//        $mail->addAddress($dir[$i]);
//        //$mail->addAddress("victor.rodriguezcerdas@ucr.ac.cr");
//        //$mail->addAddress("cp.cia@ucr.ac.cr");
//        //echo $dir[$i];
//        $i += 1;
//    }
//    $archivos = explode(";", $archivos);
//
//    $j = count($archivos);
//    $i = 0;
//    while ($i < $j) {
//        //echo $archivos[$i] . "<br>";
//        $mail->AddAttachment($archivos[$i]);
//        $i += 1;
//    }
//
//
//
//
//
//    //$mail->addAddress('karol.espinozajuarez@ucr.ac.cr', 'Karol Espinoza');
//
//    /* Set the subject. */
//    $mail->Subject = $asunto;
//
//    /* Set the mail message body. */
//    $mail->Body = $correo;
//
//    /* Finally send the mail. */
//    //$mail->send();
//    if (!$mail->Send()) {
//        echo 'Message was not sent.';
//        echo 'Mailer error: ' . $mail->ErrorInfo;
//        exit;
//    } else {
//        echo 'Mensaje Enviado.';
//    }
//}
//
//
//
//function verificarformatoid($input) {
//    //P-3705-3 => F3
//        //P-3883 => F2
//        //Blanco => F1
//    if (preg_match('/^[A-Z]-\d+-\d+$/', $input)) {
//        return 'F3';
//    } elseif (preg_match('/^[A-Z]-\d+$/', $input)) {
//        return 'F2';
//    } elseif (preg_match('/^[A-Z]$/', $input)) {
//        return 'F1';
//    } else {
//        return 'F0';
//    }
//}
//
////funcion de registros
//function Buscarcampo( $idc, $fila , $mat) {
//    $i = 0;
//    $j = count($mat);
//    $resp = "";
//    while ($i < $j) {
//        //echo "$fila == $i and $idc == "  .$mat[$i][0] . "<br>";
//        
//        if ( $idc == $mat[$i][0] && $fila == $mat[$i][1]) {
//
//                $resp = $mat[$i][2];
//                $i = $j;
//        }
//        $i += 1;
//    }
//    return $resp;
//}
////funcion de registros
//function Buscarcampoyid( $idc, $fila , $mat) {
//    $i = 0;
//    $j = count($mat);
//    $resp = [];
//    $encontrado = false;
//    while ($i < $j) {
//        if ( $idc == $mat[$i][0] && $fila == $mat[$i][1]) {
//                $encontrado = true;
//                $resp[0] = $mat[$i][3];
//                $resp[1] = $mat[$i][2];
//                $i = $j;
//        }
//        $i += 1;
//    }
//    
//    if (!$encontrado){
//                       $resp[0] = "";
//                $resp[1] = "";
//    }
//    return $resp;
//}
//
//
//
////funcion de registros
//function procesarCampo($tabla, $listaids) {
//    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//    mysqli_set_charset($dbc, "utf8");
//    $mat = [];
//    $sql1 = "
//            SELECT id_registros_campo, id_registros_fila, campo,id
//            FROM 
//            $tabla
//            WHERE 
//            id_registros_campo in ($listaids)  
//            order by id_registros_fila";
//    //echo $sql1;
//    $result = mysqli_query($dbc, $sql1);
//    //echo $sql1. "<br><br>";
//    $i = 0;
//    while ($fila = mysqli_fetch_row($result)) {
//        $mat[$i][0] = $fila[0];
//        $mat[$i][1] = $fila[1];
//        $mat[$i][2] = $fila[2];
//        $mat[$i][3] = $fila[3];
//        $i +=1;
//    }
//    return $mat;
//}
//function validarFecha($input) {
//    // Patrón para validar el formato "dd/mm/yyyy"
//    $patron = '/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/(19|20)\d{2}$/';
//
//    // Verificar si el formato coincide con el patrón
//    if (preg_match($patron, $input)) {
//        // Dividir el string en día, mes y año
//        list($dia, $mes, $anio) = explode('/', $input);
//
//        // Verificar si es una fecha válida usando checkdate
//        if (checkdate((int)$mes, (int)$dia, (int)$anio)) {
//            return true; // Es una fecha válida
//        }
//    }
//
//    return false; // No es una fecha válida
//}
//function resumirTexto($texto, $maxCaracteres = 100) {
//    // Si la longitud del texto es menor o igual al máximo, devolverlo tal cual
//    if (strlen($texto) <= $maxCaracteres) {
//        return $texto;
//    }
//
//    // Cortar el texto hasta el máximo permitido
//    $textoCortado = substr($texto, 0, $maxCaracteres);
//
//    // Asegurar que no se corte una palabra por la mitad
//    $ultimoEspacio = strrpos($textoCortado, ' ');
//    if ($ultimoEspacio !== false) {
//        $textoCortado = substr($textoCortado, 0, $ultimoEspacio);
//    }
//
//    // Agregar puntos suspensivos
//    return $textoCortado . '...';
//}
?>