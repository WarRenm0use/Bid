<?php

?>
<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
        <form action="/cgi-bin/tbk_bp_pago.cgi" method="post">
            <input name="TBK_MONTO" type="hidden" id="TBK_MONTO" value="0" />
            <input name="TBK_ORDEN_COMPRA" type="hidden" id="TBK_ORDEN_COMPRA" value="1" />
            <input name="TBK_TIPO_TRANSACCION" type="hidden" id="TBK_TIPO_TRANSACCION" value="TR_NORMAL" />
            <input name="TBK_URL_EXITO" type="hidden" id="TBK_URL_EXITO" value="http://dev.lokiero.cl/KSs1Dp/exito.php" />
            <input name="TBK_URL_FRACASO" type="hidden" id="TBK_URL_FRACASO" value="http://dev.lokiero.cl/KSs1Dp/fracaso.php" />
            <input type="submit" value="Pagar" />
        </form>
    </body>
</html>