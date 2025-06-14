<?php


/* Normaliza una fecha de
 * dd/mm/aaaa a aaaa-mm-dd o de aaaa-mm-dd a dd/mm/aaaa */
function convertirfecha($date) {

    $resp = "";
    if (!empty($date)) {
        if (substr($date, 2, 1) == "/") { //Si viene de php y va para mysql
            $var = explode('/', str_replace('-', '/', $date));
            $resp = "$var[2]-$var[1]-$var[0]";
        } else { //si viene de mysql a mostrar en php
            $var = explode('-', str_replace('/', '-', $date));
            $resp = "$var[2]/$var[1]/$var[0]";
        }
    }
    if ($resp == "00/00/0000" || $resp == "00-00-0000") {
        $resp = "";
    }
    return $resp;
}
/* Normaliza una fecha de
 * dd/mm/aaaa a aaaa-mm-dd o de aaaa-mm-dd a dd/mm/aaaa  INCLUYE LA HORA*/ 
function convertirfecha2($date) {
    $resp = "";
    if (!empty($date)) {
        if (substr($date, 2, 1) == "/") { //Si viene de php y va para mysql
            $var = explode('/', str_replace('-', '/', $date));
            $resp = substr($var[2], 0, 4) . "-$var[1]-$var[0] " . substr($var[2], 5, 9);
        } else { //si viene de mysql a mostrar en php
            $var = explode('-', str_replace('/', '-', $date));
            $resp = substr($var[2], 0, 2) . "/$var[1]/$var[0] " . substr($var[2], 3, 8);
        }
    }
    if ($resp == "00/00/0000" || $resp == "00-00-0000") {
        $resp = "";
    }
    return $resp;
}


function vercadena2($cad1, $cad2, $caseSensitive) {
    $tam1 = strlen($cad1);
    $tam2 = strlen($cad2);
    $c1 = 0;
    $c2 = 0;
    $temp = "";
    $resp = false;

    if (!$caseSensitive) {
        $cad1 = strtoupper($cad1);
        $cad2 = strtoupper($cad2);
    }
    $i = 0;
    while ($i < $tam2) {
        $c1 = 0;
        $c2 = $i;
        $seguir = true;

        while ($c1 < $tam1 && $c2 < $tam2 && $cad1[$c1] != $cad2[$c2]) {
            $c1 += 1;
        }
        $temp = "";
        while ($c1 < $tam1 && $c2 < $tam2 && $cad1[$c1] == $cad2[$c2]) {
            $temp = $temp . $cad2[$c2];
            $c1 += 1;
            $c2 += 1;
        }
        if ($temp == $cad1) {
            $i = $tam2;
            $resp = true;
        }

        $i += 1;
    }
    return $resp;
}

function segString($s, $i, $j) {
    $resp = "";
    while ($i < strlen($s) && $i <= $j) {
        $resp .= $s[$i];
        $i += 1;
    }
    return $resp;
}
?>