
<?php
$op = 0;
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}
$pc = 0;
if (isset($_GET['pc'])) {
    $pc = $_GET['pc'];
}



$op_menu01 = "";
$op_menu02 = "";
$op_menu03 = "";
$op_menu04 = "";
$op_menu05 = "";
$op_menu06 = "";

$idmenu = 0;


$idusuario = $_SESSION['id_usuario'];
$rol = $_SESSION['rol'];

switch ($op) {
    case 0:
        $op_menu01 = "opened";
        $idmenu = 1;
        break;
    case 1:
        $op_menu02 = "opened";
        break;
    case 2:
        $op_menu03 = "opened";
        break;
    case 3:
        $op_menu04 = "opened";
        break;
    case 4:
        $op_menu05 = "opened";
        break;
    case 5:
        $op_menu06 = "opened";
        break;
    default:
        echo "error";
}

if ($pc == 0) {
    echo "<div class='page-container'>";
} else {
    echo "<div class='page-container sidebar-collapsed'>";
}
//echo $rol . "aaaaaaaa";
//$rol = 0;
//admin = 1
//vendedor = 0
?>

<!--<div class="page-container"> add class "sidebar-collapsed" to close sidebar by default, "chat-visible" to make chat appear always -->	
<div class="sidebar-menu">


    <header class="logo-env">
        <!-- logo collapse icon -->

        <div class="sidebar-collapse">
            <a href="#" class="sidebar-collapse-icon"><!-- add class "with-animation" if you want sidebar to have animation during expanding/collapsing transition -->
                <i class="entypo-menu"></i>
            </a>
        </div>


        <!-- logo -->
        <div class="logo">
            <a href="../_dashboard/escritorio.php">
                <img src="../assets/images/logos/betagroup.png" width="120" alt="" />
            </a>
        </div>





        <!-- open/close menu icon (do not remove if you want to enable menu on mobile devices) -->
        <div class="sidebar-mobile-menu visible-xs">
            <a href="#" class="with-animation"><!-- add class "with-animation" to support animation -->
                <i class="entypo-menu"></i>
            </a>
        </div>

    </header>






    <ul id="main-menu" class="">
        <!-- add class "multiple-expanded" to allow multiple submenus to open -->
        <!-- class "auto-inherit-active-class" will automatically add "active" class for parent elements who are marked already with class "active" -->
        <!-- Search Bar -->

        <!--               <li id="search">
                                   <form method="get" action="">
                                     <input type="text" name="q" class="search-input" placeholder="Search something..."/>
                                        <button type="submit">
                                                <i class="entypo-search"></i>
                                        </button>
                                </form>
                        </li>-->
        <?php
        if ($rol == 1) {
            echo "           <li class='active root-level has-sub $op_menu01 active'>
                <a href='index.html'>
                    <i class='entypo-users'></i>
                    <span><font size='3'><strong>Usuarios</strong></font></span>
                </a>
                <ul>
                                     <li class='active'>
                                            <a href='..\_usuarios\usuarios.php?op=0&ta=0'>
                                                <span>Lista de Usuarios</span>
                                            </a>                            
                                        </li>          
                                        <li class='active'>
                                            <a href='..\_usuarios\bitacora.php?op=0&ta=0'>
                                                <span>Bitácora</span>
                                            </a>                            
                                        </li>                                         
                </ul>
            </li>";
        }
        ?>            



        <li class="active <?php echo $op_menu02; ?> active">
            <a href="">
                <i class="entypo-suitcase"></i>
                <span><font size="3"><strong>Proveedores</strong></font></span>
            </a> 
            <ul>

                <li class="active">
                    <?php echo "<a href='../_proveedores/proveedores.php?op=1&ta=0'>"; ?>
                        <span>Lista de Proveedores</span>
                    </a>                            
                </li>          

            </ul>
        </li>

        <li class="active <?php echo $op_menu03; ?> active">
            <a href="">
                <i class="entypo-bag"></i>
                <span><font size="3"><strong>Productos</strong></font></span>
            </a> 
            <ul>

                <li class="active">
                    
                        <?php echo "<a href='../_productos/productos.php?op=2&ta=0'>"; ?>
                        <span>Lista de Productos</span>
                    </a>                            
                </li>     

                <li class="active">
                    
                    <?php echo "<a href='../_productos/categorias.php?op=2&ta=0'>"; ?>    
                        <span>Categorías</span>
                    </a>                            
                </li>   

            </ul>
        </li>

        <li class="active <?php echo $op_menu04; ?> active">
            <a href="#">
                <i class="entypo-user-add"></i>
                <span><font size="3"><strong>Clientes</strong></font></span>
            </a> 
            <ul>

                <li class="active">
                    
                        <?php echo "<a href='../_clientes/clientes.php?op=3'> "; ?>    
                        <span>Lista de Clientes</span>                        
                    </a>                            
                </li>     

                <li class="active">
                    
                    <?php echo "<a href='../_tipoClinica/tipoClinica.php?op=3'>"; ?>     
                        <span>Tipos de Clínica</span>
                    </a>                            
                </li>   

            </ul>
        </li>

        <li class="active <?php echo $op_menu05; ?> active">
            <a href="">
                <i class="entypo-doc-text"></i>
                <span><font size="3"><strong>Ventas</strong></font></span>
            </a> 
            <ul>
                <li class="active">
                    
                    <?php echo "<a href='../_ventas/ventas.php?op=4&ta=0'>"; ?>       
                        <span>Lista de Ventas</span>
                    </a>                            
                </li>  
                <li class="active">
                    <?php echo "<a href='../_ventas/reporte.php?op=4'>"; ?>  
                        <span>Reporte de Ventas  </span>
                    </a>                            
                </li>     

            </ul>
        </li>
        
<!--        <li class="active php echo $op_menu06; ?> active">
            <a href="#">
                <i class="entypo-user-add"></i>
                <span><font size="3"><strong>Vistas</strong></font></span>
            </a> 
            <ul>

                <li class="active">
                    
                        php echo "<a href=''> "; ?>    
                        <span>Vista 1</span>                        
                    </a>                            
                </li>     

                <li class="active">
                    
                    php echo "<a href=''>"; ?>     
                        <span>Vista2</span>
                    </a>                            
                </li>   

            </ul>
        </li>        -->

    </ul>
</div>	

<div class="main-content">


    <div class="row">

        <!-- Profile Info and Notifications -->
        <?php
        include("../includes/perfil.php");
        ?>

        <!-- Raw Links -->
        <div class="col-md-6 col-sm-4 clearfix hidden-xs">

            <ul class="list-inline links-list pull-right">




                <li class="sep"></li>

                <li>
                    <a href="../_log/logout.php">
                        Salir <i class="entypo-logout right"></i>
                    </a>
                </li>
            </ul>

        </div>

    </div>


