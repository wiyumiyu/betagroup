<?php
include ('../includes/header.html');
include("../includes/barralateral.php");
include ('../includes/funciones.php');
?>
<hr><!-- comment -->

<ol class="breadcrumb bc-3">
    <li>
        <a href="..\_dashboard\escritorio.php"><i class="entypo-home"></i>Home</a>
    </li>

    <li class="active">

        <strong>Lista de Usuarios</strong>
    </li>
</ol>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h2 style="margin: 0;">Lista de Usuarios</h2>
    <a href='' type='button' class='btn btn-success'><i class='entypo-plus'></i>Nuevo Usuario </a>
</div>

<br>

<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"><table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
        <thead>
            <tr role="row">
                <th class="sorting" tabindex="0" style="width: 200px;">
                    <strong style="color: #999999;">Usuario</strong>
                </th>
                <th class="sorting" tabindex="0" style="width: 150px;">
                    <strong style="color: #999999;">Teléfono</strong>
                </th>
                <th class="sorting" tabindex="0" style="width: 250px;">
                    <strong style="color: #999999;">Correo</strong>
                </th>
                <th class="sorting" tabindex="0" style="width: 100px;">
                    <strong style="color: #999999;">Rol</strong>
                </th>
                <th class="sorting" tabindex="0" style="width: 150px;">
                    <strong style="color: #999999;">Registro</strong>
                </th>
                <th class="sorting" tabindex="0" style="width: 200px;">
                    <strong style="color: #999999;">Acciones</strong>
                </th>
            </tr>

        </thead>



        <tbody>
            <?php
// Preparar y ejecutar el procedimiento LISTAR_USUARIOS
            $sql = "BEGIN LISTAR_USUARIOS(:cursor); END;";
            $stid = oci_parse($conn, $sql);
            $cursor = oci_new_cursor($conn);
            oci_bind_by_name($stid, ":cursor", $cursor, -1, OCI_B_CURSOR);

            oci_execute($stid);
            oci_execute($cursor);

// Iterar sobre los usuarios
            $claseFila = "odd";
            while ($row = oci_fetch_assoc($cursor)) {
                echo "<tr class=\"$claseFila\">";

                // Nombre de usuario
                echo "<td style='color: #4B4B4B; font-size: 14px;'>" . htmlspecialchars($row['NOMBRE_USUARIO']) . "</td>";

// Teléfono
                echo "<td style='color: #4B4B4B; font-size: 14px;'>" . htmlspecialchars($row['TELEFONO'] ?? '') . "</td>";

// Correo
                echo "<td style='color: #4B4B4B;font-size: 14px;'>" . htmlspecialchars($row['CORREO']) . "</td>";

// Rol
                $rolTexto = ($row['ROL'] == 1) ? "Administrador" : "Vendedor";
                echo "<td style='color: #4B4B4B;font-size: 14px;'>$rolTexto</td>";

// Fecha de registro
                echo "<td style='color: #4B4B4B;font-size: 14px;'>" . date("d-m-Y", strtotime($row['FECHA_REGISTRO'])) . "</td>";

                // Acciones
                echo "<td>
            <a href='#' class='btn btn-default '>
                <i class='entypo-pencil'></i> 
            </a>
            <a type='button' class='btn btn-danger'><i class='entypo-cancel'></i></a>
          </td>";

                echo "</tr>";

                // Alternar clase de fila entre odd/even
                $claseFila = ($claseFila === "odd") ? "even" : "odd";
            }


// Liberar recursos
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);
            ?>
        </tbody>
    </table><div class="row"><div class="col-xs-6 col-left"><div class="dataTables_info" id="table-2_info">Showing 1 to 8 of 12 entries</div></div><div class="col-xs-6 col-right"><div class="dataTables_paginate paging_bootstrap"><ul class="pagination pagination-sm"><li class="prev disabled"><a href="#"><i class="entypo-left-open"></i></a></li><li class="active"><a href="#">1</a></li><li><a href="#">2</a></li><li class="next"><a href="#"><i class="entypo-right-open"></i></a></li></ul></div></div></div></div>






<?php
include("../includes/footer.php");
?>