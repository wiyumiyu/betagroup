<?php

function llenarSelect($name, $id_tabla, $col_tabla, $seleccionado, $sqlproc, $conn) {

    $sel = "";
    $stid = oci_parse($conn, $sqlproc);
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);
    oci_execute($stid);
    oci_execute($cursor);
        $selected = "";
    $sel = "<option value=-1>-- Seleccione --</option>";
    while ($row = oci_fetch_assoc($cursor)) {
        $id = $row[$id_tabla];

        $selected = "";
        if($seleccionado  == $id){
            $selected = "selected";
        }        
        
        $sel .= "<option $selected value=$id>" . htmlspecialchars($row[$col_tabla]) . "</option>";
    }

    $sel = "<select id='$name' name='$name' class='form-control'>$sel</select>";
    oci_free_statement($stid);
    oci_free_statement($cursor);
    return $sel;
}

function llenarBitacora($id, $sql_contexto, $conn){
// ==========================================================================
// Establecer el ID del usuario actual en el contexto de sesión de Oracle.
// Esto es necesario para que los triggers de auditoría puedan saber quién
// está realizando cada operación (INSERT, UPDATE, DELETE) desde la aplicación.
// El ID se obtiene de la sesión PHP y se pasa al paquete PL/SQL:
//    pkg_contexto_usuario.set_usuario(:id)
// ==========================================================================
      $stmt_contexto = oci_parse($conn, $sql_contexto);
    oci_bind_by_name($stmt_contexto, ':id', $id);
    oci_execute($stmt_contexto);
// ==========================================================================
    return $stmt_contexto;
    
}
//
//function formatearTextoBitacora($texto) {
//    // 1. Reemplazar ":" o "," por salto de línea
//    $texto = preg_replace('/[:|,]/', "<br>", $texto);
//
//    // 2. Poner en negrita las palabras que están antes de un "="
//    $texto = preg_replace('/(\b\w+\b)\s*=/','<strong>$1</strong>=', $texto);
//
//    return $texto;
//}

?>