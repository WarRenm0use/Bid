<?php
require_once '../modelo/Bd.php';

$bd = new Bd();

$trs_orden_compra = $_POST['TBK_ORDEN_COMPRA'];

$sql_pagos = "SELECT * FROM pagos order by TBK_ORDEN_COMPRA DESC Limit 1";
$sql_webpay = "SELECT * FROM webpay order by TBK_ORDEN_COMPRA DESC Limit 1 ";

//$result_port = mysql_query($sql_port, $conn);
$fechapedido = date("Y-m-d");
$result_pagos = $bd->sql($sql_pagos);
$result_webpay = $bd->sql($sql_webpay);

$i = 0;
while ($myrow_not = mysql_fetch_array($result_webpay)) {
    $i++;

    $t_compra = $myrow_not[Tbk_Orden_Compra];
    $t_monto = $myrow_not[Tbk_Monto];
    $tar_final = $myrow_not[Tbk_Final_numero_Tarjeta];
    $cuotas = $myrow_not[Tbk_Numero_Cuotas];
    $autorizacion = $myrow_not[Tbk_Codigo_Autorizacion];
    $pagos = $myrow_not[Tbk_Tipo_Pago];
} //Fin While
$e = 0;
while ($myrow_p = mysql_fetch_array($result_pagos)) {
    $e++;

    $t_producto = $myrow_p[PRODUCTO];
    $t_nombre = $myrow_p[usr_nombre];
    $t_apellido = $myrow_p[usr_apellido];
    $t_email = $myrow_p[usr_email];
} //Fin While


switch ($pagos) {
    case VN:
        $vn = ("Sin Cuotas");
        break;
    case SI:
        $vn = ("Sin Intereses");
        break;
    case VC:
        $vn = ("Cuotas Comercio");
        break;
}
?> 


