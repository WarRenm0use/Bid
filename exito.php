<?
session_start();
require_once 'modelo/WebPayMP.php';
require_once 'modelo/CarroMP.php';

$wpMP = new WebPayMP();
$caMP = new CarroMP();
$wp = $wpMP->find($_POST["TBK_ORDEN_COMPRA"]);
$ca = $caMP->find($_POST["TBK_ORDEN_COMPRA"]);
$prod = $caMP->fetchProductos($_POST["TBK_ORDEN_COMPRA"]);
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

switch ($wp->Tbk_Tipo_Pago) {
    case VN:
        $vn = ("Sin Cuotas");
        break;
    case SI:
        $vn = ("Sin Intereses");
        break;
    case VC:
        $vn = ("Cuotas Normales");
        break;
    case CI:
        $vn = ("Cuotas Comercio");
        break;
}

//$vn = $cp->getCSec()->vn;
//$resumen = $cp->getCSec()->resumen;
//$ca = $cp->getCSec()->ca;
//$wp = $cp->getCSec()->wp;
//$prod = $cp->getCSec()->prod;
//$nProd = $cp->getCSec()->nProd;
//$bidTotal = 0;
?>
<!DOCTYPE html>
<html  xmlns:fb="http://www.facebook.com/2008/fbml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="js/fancybox/jquery.fancybox-1.3.4.css" />
        <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
        <link rel="stylesheet" type="text/css" href="css/estilos.css" />
        <title>Lo Kiero!.cl - Subastas VIP</title>
        <script type="text/javascript">

        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-29417526-1']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();

        </script>
    </head>
    <body>
        <div id="notificacion" style="display:none;"></div>
        <div id="contenedor">
            <div class="menu">
                <ul>
                    <li><a href="/#/quienes-somos">QUIÉNES SOMOS</a></li>
                    <li><a href="/#/bids">BIDS</a></li>
                    <li><a href="/#/terminos">TÉRMINOS Y CONDICIONES</a></li>
                    <li><a href="/#/faq">PREGUNTAS FRECUENTES</a></li>
                    <li><a href="/#/contacto">CONTACTO</a></li>
                </ul>
            </div><!--fin menu -->
            <div id="encabezado">
                <div id="logo" class="tp" data-placement="right" title="Volver al inicio"><a href="/#/"><img src="img/logo.png" width="366" height="131" border="0" /></a></div><!--fin logo -->
                <ul id="pestanas">
                    <li id="registro" class="tp" title="Registrate usando tu cuenta de Facebook">Registrate! <img src="img/ki2_12.png" width="36" height="38" /></li>
                    <li id="invitar" class="tp" title="Invita a tus amigos y gana Bids por cada uno que se registre" style="display:none;"><a href="/#/invitaciones">Invita y Gana <img src="img/ki2_10.png" width="34" height="38" border="0"/></a></li>
                    <li id="compraBid" class="tp" title="Compra Bids para participar de las subastas" style="display:none;"><a href="/#/bids">Compra Bids <img src="img/BID.png" width="36" height="38" border="0"/></a></li>
                    <li id="carroCompra" class="tp" title="No tienes productos en el carro de compra" style="display:none;"><a href="/#/carro">Carro <img src="img/BID.png" width="36" height="38" /></a></li>
                </ul>
            </div><!--fin encabezado -->
            <div id="flechas" style="display:none;">
                <div id="ahora">AHORA</div>
<!--                <div id="proxima">PRÓXIMA</div>-->
            </div><!--fin contenido-->
            <div id="contenido">
                <div id="inner">
                    <h1>Compra exitosa!</h1>
                    <div id="cuerpo">
                        <p>Estimado <b><?=$_SESSION["NOM_USUARIO"]?></b>, la venta se realizo correctamente y se cargaron <?=$resumen->totalBid?> Bids a tu cuenta.</p>
                        <h2>Detalle de la compra</h2>
                        <div class="row">
                            <div class="span5">
                                <table class='table table-striped table-bordered'>
                                    <tr>
                                        <td>Fecha de compra:</td>
                                        <td><?=date("d-m-Y")?></td>
                                    </tr>
                                    <tr>
                                        <td>Orden de compra:</td>
                                        <td><?=$ca->ID_CARRO?></td>
                                    </tr>
                                    <tr>
                                        <td>Codigo de autorizaci&oacute;n:</td>
                                        <td><?=$wp->Tbk_Codigo_Autorizacion?></td>
                                    </tr>
                                    <tr>
                                        <td>N&uacute;mero de tarjeta:</td>
                                        <td>xxxxxxxxxxxxx-<?=$wp->Tbk_Final_numero_Tarjeta?></td>
                                    </tr>
                                    <tr>
                                        <td>Cantidad de Cuotas:</td>
                                        <td>0<?=$wp->Tbk_Numero_Cuotas?></td>
                                    </tr>
                                    <tr>
                                        <td>Tipo de Cuotas:</td>
                                        <td><?=$vn?></td>
                                    </tr>
                                    <tr>
                                        <td>Comercio:</td>
                                        <td>Lo Kiero! (<a href="/">www.lokiero.cl</a>)</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="span6">
                                <table class='table table-striped table-bordered' id='carro'>
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Precio</th>
                                            <th>Cantidad</th>
                                            <th class="blue">Total</th>
                                            <th class="blue">Total Bids</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <? 
                                        for($i=0; $i<$nProd; $i++) { 
                                            $pro = $prod[$i];
                                            $bidFila = $pro->CANTIDAD_CARRO_PROD * $pro->VALOR_BID;
                                            $bidTotal += $bidFila;
                                        ?>
                                        <tr>
                                            <td><img src="/producto/<?=$pro->URL_IMAGEN?>" border="0" height="15" style="margin-right:5px;"/><?=$pro->NOM_PRODUCTO?></td>
                                            <td>$<?=number_format($pro->PRECIO_CARRO_PROD, 0, ",", ".")?></td>
                                            <td><?=$pro->CANTIDAD_CARRO_PROD?></td>
                                            <td>$<?=number_format($pro->CANTIDAD_CARRO_PROD*$pro->PRECIO_CARRO_PROD, 0, ",", ".")?></td>
                                            <td><?=$bidFila?></td>
                                        </tr>
                                        <? } ?>
                                        <tr>
                                            <td colspan="2" style="text-align: right;">Total</td>
                                            <td><?=$resumen->totalProd?></td>
                                            <td>$<?=number_format($resumen->totalMonto, 0, ",", ".")?></td>
                                            <td><?=$resumen->totalBid?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p><b>Nota</b>: Todos los montos expresados corresponde a pesos chilenos, IVA incluido. Para devoluciones y/o reclamos comunicate con el departamento de <a mailto="ventas@lokiero.cl">Ventas</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--fin contenido-->
            <div id="sombra"></div>
            <div id="contenido2"></div>
        </div><!--fin contenedor -->
        <div id="pie">
            <div id="tarjetas"><img src="img/ki3_40.png" width="743" height="100"></div>
            <p class="infoEmpresa"><b>Lo Kiero!</b>&#8482 (<a href="http://www.lokiero.cl">www.lokiero.cl</a>) - Av. 11 de septiembre 1881, of 1620, Providencia, Santiago, Chile</p>
        </div>
        <div id="fb-root"></div>
        <script src="js/LAB.min.js" type="text/javascript"></script>
        <script type="text/javascript">
            $LAB
//            .script("js/modernizr.custom.43749.js").wait()
            .script("js/jquery-1.7.min.js").wait()
            .script("http://connect.facebook.net/es_ES/all.js").wait()
//            .script("js/jquery.easing.1.3.js")
            .script("js/jquery.tmpl.min.js")
//            .script("js/simple-modal.js")
            .script("js/underscore-min.js")
            .script("js/backbone-min.js")
            .script("js/cacheprovider.js").wait()
            .script("js/subasta_vip.js")
            .script("js/producto.js")
            .script("js/jquery.validate.min.js")
            .script("js/pagina.js")
            .script("js/invitacion.js")
            .script("js/carro.js")
            .script("js/facebook.js")
            .script("js/fancybox/jquery.fancybox-1.3.4.pack.js")
            .script("js/fancybox/jquery.mousewheel-3.0.4.pack.js")
            .script("js/fancybox/jquery.easing-1.3.pack.js")
            .script("js/jquery.bar.js")
            .script("js/bootstrap.min.js")
            .script("js/webpay.js").wait();
        </script>
    </body>
</html>