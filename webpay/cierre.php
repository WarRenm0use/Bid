<?php
require_once '../modelo/WebPayMP.php';
require_once '../modelo/CarroMP.php';
require_once '../modelo/UsuarioMP.php';

$wpMP = new WebPayMP();
$caMP = new CarroMP();
$usMP = new UsuarioMP();

$wp = new stdClass();
$wp->Tbk_tipo_transaccion = $_POST['TBK_TIPO_TRANSACCION'];
$wp->Tbk_respuesta = $_POST['TBK_RESPUESTA'];
$wp->Tbk_orden_compra = $_POST['TBK_ORDEN_COMPRA'];
$wp->Tbk_id_sesion = $_POST['TBK_ID_SESION'];
$wp->Tbk_codigo_autorizacion = $_POST['TBK_CODIGO_AUTORIZACION'];
$wp->Tbk_monto = substr($_POST['TBK_MONTO'], 0, -2) . ".00";
$wp->Tbk_Final_numero_Tarjeta = $_POST['TBK_FINAL_NUMERO_TARJETA'];
$wp->Tbk_fecha_expiracion = $_POST['TBK_FECHA_EXPIRACION'];
$wp->Tbk_fecha_contable = $_POST['TBK_FECHA_CONTABLE'];
$wp->Tbk_fecha_transaccion = $_POST['TBK_FECHA_TRANSACCION'];
$wp->Tbk_hora_transaccion = $_POST['TBK_HORA_TRANSACCION'];
$wp->Tbk_id_transaccion = $_POST['TBK_ID_TRANSACCION'];
$wp->Tbk_tipo_pago = $_POST['TBK_TIPO_PAGO'];
$wp->Tbk_numero_cuotas = $_POST['TBK_NUMERO_CUOTAS'];
$wp->Tbk_mac = $_POST['TBK_MAC'];
$wp->Tbk_tasa_interes_max = $_POST['TBK_TASA_INTERES_MAX'];

if ($wp->Tbk_respuesta == 0) {
    $temporal = "/home/dev/www/cgi-bin/log/temporal.txt";
    $fp = fopen($temporal, "w");
    if ($fp) {
        fwrite($fp, $wp->Tbk_codigo_autorizacion);
        fclose($fp);
    }
    
    $filename = "/home/dev/www/cgi-bin/log/log" . $wp->Tbk_id_transaccion . ".txt";
    $fp2 = fopen($filename, "w");
    if($fp2) {
        reset($_POST);
        while (list($key, $val) = each($_POST)) {
            fwrite($fp2, "$key=$val&");
        }
        fclose($fp2);
    }
    
    $cmdline = "/home/dev/www/cgi-bin/tbk_check_mac.cgi $filename";
    exec($cmdline, $result, $retint);
    
    if ($result[0] == "CORRECTO") {
        $ca = $caMP->find($wp->Tbk_orden_compra);
        if($ca->ESTADO_CARRO == 1) {
            $prod = $caMP->fetchProductos($wp->Tbk_orden_compra);
            $nProd = count($prod);
            $resumen = new stdClass();
            $resumen->totalProd = 0;
            $resumen->totalMonto = 0;
            $resumen->totalBid = 0;
            for($i=0; $i<$nProd; $i++) {
                $resumen->totalProd += $prod[$i]->CANTIDAD_CARRO_PROD;
                $resumen->totalMonto += $prod[$i]->PRECIO_CARRO_PROD*$prod[$i]->CANTIDAD_CARRO_PROD;
                $resumen->totalBid += $prod[$i]->VALOR_BID*$prod[$i]->CANTIDAD_CARRO_PROD;
            }
            if ($ca) {
                if ($ca->MONTO_CARRO == $wp->Tbk_monto && $ca->MONTO_CARRO == $resumen->totalMonto) {
                    $caAux = new stdClass();
                    $caAux->ID_CARRO = $ca->ID_CARRO;
                    $caAux->ESTADO_CARRO = 2;
                    $suma = $usMP->sumaBid($resumen->totalBid, $ca->ID_USUARIO);
                    $upd = $caMP->update($caAux);
                    $log = $wpMP->save($wp);
                    if($suma && $upd && $log) {
                        echo "ACEPTADO";
                    } else {
                        if($suma) $usMP->eliminaBid($nBid, $idUs);
                        if($upd) {
                            $caAux->ESTADO_CARRO = 0;
                            $upd = $caMP->update($caAux);
                        }
                        if($log) $wpMP->deleteByIdCarro($ca->ID_CARRO);
                        echo "RECHAZADO";
                    }
                } else {
                    echo "RECHAZADO";
                }
            } else {
                echo "RECHAZADO";
            }
        } else {
            echo "RECHAZADO";
        }
    } else {
        echo "RECHAZADO";
    }
} else {
    echo "ACEPTADO";
}
?>