<!DOCTYPE html>
<html  xmlns:fb="http://www.facebook.com/2008/fbml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="css/reset.css" />
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
                    <h1>Transacci&oacute;n fracasada :(</h1>
                    <div id="cuerpo"><p>Lamentablemente la orden de compra N° <?=$_POST["TBK_ORDEN_COMPRA"]?> no pudo ser procesada, las posibles causas de este rechazo son:</p>
                        <ul>
                            <li>Error en el ingreso de los datos de su tarjeta de cr&eacute;dito (fecha y/o c&oacute;digo de seguridad).</li>
                            <li>Su tarjeta de cr&eacute;dito no cuenta con el cupo necesario para cancelar la compra.</li>
                            <li>Tarjeta a&uacute;n no habilitada en el sistema financiero.</li>
                            <li>Si el problema persiste favor comunicarse con su Banco emisor.</li>
                        </ul>
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