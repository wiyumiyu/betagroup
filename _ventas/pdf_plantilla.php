<?php

require("../lib/fpdf181/fpdf.php");

class PDF extends FPDF {

    public $Xsangria;
    public $primeralineamuestras;
    public $version;
    public $s_numero0;
    public $s_usuario3;
    public $s_usuario25;
    public $correosR;
    //public $s_subcliente16;
    public $s_fecha1;
    public $impuestos;
    //public $s_material12;
    public $telefonos;
    //public $s_cultivo5;
    public $ubicacion;
    //public $dias;
    //public $rdetalle;
    public $sesion_nombre;
    public $sesion_apellido1;
    public $sesion_apellido2;
    //public $facturas;
    //public $recibos;
    //public $observaciones;
    //public $otropais;
    //public $color;

    public function Header() {
        $tam1 = 22;
        $tam2 = 163;
        $tam3 = 116;
        $tam4 = 15;
        $tam5 = 32;
        $tam6 = 27;


        $nombre_usuario = $this->s_usuario3;
        if ($nombre_usuario == "") {
            $nombre_usuario = $this->s_usuario25;
        }
        

        
        //$this->Image('..\images\logoucr.png',18,8,35);
        
        $this->SetX($this->Xsangria);
        $this->SetFont('Arial', 'B', 16); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell(185, 6, mb_convert_encoding("Factura", 'ISO-8859-1', 'UTF-8') , 0, 1, 'C');
        $this->SetX($this->Xsangria);
        $this->Cell(185, 6,mb_convert_encoding($this->s_numero0, 'ISO-8859-1', 'UTF-8') , 0, 0, 'C');
        $this->Image('..\assets\images\logos\betagroup2.png',10,8,30);
        
      
        
        
//        $this->SetY(24);
//        $this->SetX($this->Xsangria);
//        $this->SetFont('Arial', '', 10); //Establecemos tipo de fuente, negrita y tamaño 16
//        $this->Cell(36, 8, utf8_decode("Versión " . $this->version), 1, 0, 'C');
//        $this->SetFont('Arial', 'B', 16); //Establecemos tipo de fuente, negrita y tamaño 16
//
//        $this->Cell(117, 8, utf8_decode("No. " . $this->s_numero0 . " - ". $this->s_material12), 1, 0, 'C');
//
//        $this->SetFont('Arial', '', 10); //Establecemos tipo de fuente, negrita y tamaño 16
//        $this->Cell(32, 8, utf8_decode('Página ' . $this->PageNo() . ' de {nb}'), 1, 1, 'C');
//
        $this->SetY(32);
        $this->SetX($this->Xsangria );
        $this->SetFont('Arial', '', 10); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell($tam1, 5, "Cliente: ", 'LT', 0, 'L');
        $this->SetFont('Arial', 'B', 10); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell($tam2, 5, mb_convert_encoding($nombre_usuario, 'ISO-8859-1', 'UTF-8') ,'TR', 1, 'L');

        $this->SetX($this->Xsangria );
        $this->SetFont('Arial', '', 10); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell($tam1, 5, "Correo: ", 'L', 0, 'L');
        $this->SetFont('Arial', '', 8); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell($tam2, 5, utf8_decode($this->correosR), 'R', 1, 'L');


//        $this->SetX($this->Xsangria );
//        $this->SetFont('Arial', '', 10); //Establecemos tipo de fuente, negrita y tamaño 16
//        $this->Cell($tam1, 5, "", 'L', 0, 'L');
//        $this->SetFont('Arial', '', 8); //Establecemos tipo de fuente, negrita y tamaño 16
//        $this->Cell($tam3, 5, "", '0', 0, 'L');
//
//        $this->SetFont('Arial', '', 10);
//        $this->Cell($tam4, 5, "", 0, 0, 'L');
//        $this->SetFont('Arial', '', 7);
//        $this->Cell($tam5, 5,"" , 'R', 1, 'L');
        


//        $this->SetX($this->Xsangria );
//        $this->SetFont('Arial', '', 10);
//        $this->Cell($tam1, 5, "", 'L', 0, 'L');
//        $this->SetFont('Arial', '', 8);
//        $this->Cell($tam3, 5, "", 0, 0, 'L');
        
        $this->SetX($this->Xsangria );
        $this->SetFont('Arial', '', 10);
        $this->Cell($tam1, 5, mb_convert_encoding("Teléfono: " , 'ISO-8859-1', 'UTF-8')  , 'L', 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell($tam3, 5, $this->telefonos, 0, 0, 'L');


        $this->SetFont('Arial', '', 10);
        $this->Cell($tam4, 5, "Fecha: ", 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell($tam5, 5, mb_convert_encoding($this->s_fecha1, 'ISO-8859-1', 'UTF-8'), 'R', 1, 'L');
        
        $this->SetX($this->Xsangria );
        $this->SetFont('Arial', '', 10);
        $this->Cell($tam1, 5, mb_convert_encoding("Ubicación: " , 'ISO-8859-1', 'UTF-8'), 'LB', 0, 'L');
        $this->SetFont('Arial', '', 8);
        $fillpais = 0;
        $this->Cell($tam3, 5, mb_convert_encoding($this->ubicacion, 'ISO-8859-1', 'UTF-8') , 'B', 0, 'L', $fillpais);

        

        
        $this->SetFont('Arial', '', 10); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell($tam4, 5, "Impuestos: ", 'B', 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell($tam5, 5,  mb_convert_encoding($this->impuestos, 'ISO-8859-1', 'UTF-8') , 'BR', 1, 'L');
//        
//
//        
        $this->SetY(63);
        $this->SetX($this->Xsangria );
        $this->SetFont('Arial', 'B', 10); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell(8, 6,  utf8_decode("#"), 'B', 0, 'L');
        $this->SetFont('Arial', 'B', 10); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell(86, 6, "Producto",'B', 0, 'L');        
        $this->SetFont('Arial', 'B', 10); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell(30, 6, "Descuento",'B', 0, 'C');
        
        $this->SetFont('Arial', 'B', 10); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell(30, 6, "P/ Unitario",'B', 0, 'R');
        
        $this->SetFont('Arial', 'B', 10); //Establecemos tipo de fuente, negrita y tamaño 16
        $this->Cell(30, 6, "Total",'B', 1, 'R');
        
        
        $this->SetY($this->primeralineamuestras);

    }



    function Footer() {
//        $tam5 = 27;
//
//
//        $observaciones = "";
//        $obs = trim(utf8_decode($this->observaciones));
//        if ($obs != "") {
//            $observaciones = $obs;
//        }
//
//        if ($observaciones != "") {
//            $observaciones .= chr(10).chr(13);
//        }
//
//        while ($fila = mysqli_fetch_row($this->rdetalle)) {
//            $this->SetX($this->Xsangria );
//            $item = trim(utf8_decode($fila[1]));
//            if ($item != "") {
//                $observaciones .= chr(149) . " " . $item;
//            }
//                     
//        }
//         
//        $this->SetY(-78);
//        $this->SetX($this->Xsangria );
//        $this->SetFont('Arial', '', 7.5);
//        $this->MultiCell(185, 3, utf8_decode("OBSERVACIONES: El usuario acepta los métodos de ensayo utilizados, los cuales están disponibles en cada laboratorio. El muestreo es responsabilidad del usuario. El usuario acepta que el Reporte de Ensayo con validez legal es el original firmado y sellado, y que es imprime ante su solicitud expresa. Cuando el usuario solicita el envío del reporte por correo electrónico, libera al Laboratorio de resguardar la integridad y confidencialidad de los resultados."), 1, 'J', 0);        
//        
//        $this->SetY(-67);
//        $this->SetX($this->Xsangria );
//        $this->SetFont('Arial', '', 9);
//        $this->MultiCell(133, 4, $observaciones, 0, 'L', 0);
// 
//
//
//
//        $this->SetY(-67);
//        $this->SetX(154);
//
//        $facturas = $this->facturas;
//        $recibos = $this->recibos;
//        $fyr = "";
//        if(strlen($facturas) > 0){
//            $fyr = utf8_decode("Factura: ");
//            $fyr .= utf8_decode($this->facturas);
//            if (strlen($recibos) > 0) {
//                $fyr .= chr(13).chr(10).utf8_decode("Recibo: ");
//                $fyr .= $recibos;
//            }
//
//            $this->MultiCell(44, 5, $fyr,  0, 'L', 0);
//        }
//        
//     
//
//        $this->SetFont('Arial', '', 10);
//        $this->SetY(-38);
//
//
//        $this->SetX($this->Xsangria );
//        $this->Cell(100, 5, "Recibido por: " . utf8_decode($this->sesion_nombre . " " . $this->sesion_apellido1 . " " . $this->sesion_apellido2) , 0, 0, 'L');
//        $this->Cell(85, 5, "Entregado por:" , 0, 1, 'L');
//
//        $this->SetX($this->Xsangria );
//        $this->SetFont('Arial', '', 8);
//        $this->Cell(185, 4,  utf8_decode("Recepción de muestras: Teléfono (506) 2511-2054 * Fax: (506) 2234-1627 * Correo-e: muestras.cia@ucr.ac.cr") , 'T', 1, 'C');
//        $this->SetX($this->Xsangria );
//        $this->SetFont('Arial', '', 8);
//        $this->Cell(185, 4,  utf8_decode("Lab. Suelos y Foliares (506) 2511-2079 * Lab. Recursos Naturales: (506) 2511-3167 * Lab. Microbiología Agrícola: (506) 2511-3121") , 0, 1, 'C');        
//        
//     
    }

}

//fin class PDF
?>

