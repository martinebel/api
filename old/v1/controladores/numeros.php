<?php
$caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$caracteres .= "1234567890";
$final = array();
$longitud = 6;
$carac_desordenada = str_shuffle($caracteres);
for($i=0;$i<=$longitud;$i++) {
$final[$i] = $carac_desordenada[$i]; }
//recorremos la array e imprimimos
foreach($final as $datos) {
echo $datos; }