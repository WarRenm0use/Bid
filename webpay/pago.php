<? 
// GENERA ORDEN DE COMPRA A PARTIR DE FECHA

$fechaactual=strftime("%m/%d/%Y %H:%M:%S %p");
$ano=strftime("%Y",($fechaactual));
$mes=strftime("%m",($fechaactual));
$dia=$day[$fechaactual];
$minuto=strftime("%M",($fechaactual));
$segundo=strftime("%S",($fechaactual));
$TBK_ORDEN_COMPRA="ORDEN DE COMPRA:".$ano.$mes.$dia.$minuto.$segundo;



 
// GENERA ORDEN DE TBK_ID_SESION

$fechaactual=strftime("%m/%d/%Y %H:%M:%S %p");
$ano=strftime("%Y",($fechaactual));
$mes=strftime("%m",($fechaactual));
$dia=$day[$fechaactual];
$minuto=strftime("%M",($fechaactual));
$segundo=strftime("%S",($fechaactual));
$TBK_ID_SESION="CODIGO DE AUTORIZACION:".$dia.$segundo.$ano.$mes.$dia.$minuto;

?>



<html><head>




<title>Store Muebles - Modulo webpay KCC</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<script language="JavaScript"> 
<!-- 
   function MostrarTotal() 
   { 
     var suma = 0;

     if (document.formulario.cd1.checked==1) suma = suma + parseInt(document.formulario.cd1.value);
     if (document.formulario.cd2.checked==1) suma = suma + parseInt(document.formulario.cd2.value);
     if (document.formulario.cd3.checked==1) suma = suma + parseInt(document.formulario.cd3.value);
     if (document.formulario.cd4.checked==1) suma = suma + parseInt(document.formulario.cd4.value);
     
      document.formulario.total.value = suma;
      document.formulario.TBK_MONTO.value = suma*100;
   } 

 function ValidaCompra() 
   { 
     if (document.formulario.total.value>0) return true
     else {
           alert ("Debe seleccionar un producto antes de comprar");
            return false;
          }
   } 
//--> 
</script> 

</head><body javascript:mostrartotal()="" bgcolor="#ffffff">
<table align="center" border="0" cellpadding="0" cellspacing="0" width="75%">
  <tbody><tr> 
    <td colspan="3"><img src="Tienda_demo_integracion_emisores_files/feriadiscotop.jpg" height="153" width="977"></td>
  </tr>
  <tr> 
    <td valign="top" width="32%"><img src="Tienda_demo_integracion_emisores_files/menuFeria.jpg" height="512" width="213"></td>
    <td width="68%"> 
      
      <form name="formulario" method="post" action="/cgi-bin/tbk_bp_pago.cgi">
        <table align="left" border="0" width="500">
          <tbody><tr> 
            <td bgcolor="#cccccc" width="5%"> </td>
            <td bgcolor="#cccccc" width="19%"> 
              <div align="center"></div>
            </td>
            <td bgcolor="#cccccc" width="49%"><font color="#666666"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
              MUEBLES</font></b></font></td>
            <td bgcolor="#cccccc" width="27%"><font color="#666666"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">VALOR</font></b></font></td>
          </tr>
          <tr> 
            <td width="5%"> 
              <input name="cd1" value="47090" onclick="return MostrarTotal();" type="checkbox">
            </td>
            <td width="19%"> 
              <div align="center"><img src="Tienda_demo_integracion_emisores_files/215934.jpg" height="80" width="80"></div>
            </td>
            <td bgcolor="#efefef" width="49%"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">EXTREME<br>
              <b>BERGERE TFX T101 by Rosen</b></font></td>
            <td bgcolor="#e0e0e0" width="27%"> 
              <div align="right"><font face="Georgia, Times New Roman, Times, serif" size="3">$ 
                47.090</font></div>
            </td>
          </tr>
          <tr> 
            <td width="5%"> 
              <input name="cd2" value="70990" onclick="return MostrarTotal();" type="checkbox">
            </td>
            <td width="19%"> 
              <div align="center"><img src="Tienda_demo_integracion_emisores_files/342856.jpg" height="80" width="80"></div>
            </td>
            <td bgcolor="#efefef" width="49%"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">NEO<br>
              <b>SOFA NICOLETTE Tapiz Tela Roja</b></font><font face="Verdana, Arial, Helvetica, sans-serif"></font></td>
            <td bgcolor="#e0e0e0" width="27%"> 
              <div align="right"><font face="Georgia, Times New Roman, Times, serif" size="3">$ 
                70.990</font></div>
            </td>
          </tr>
          <tr> 
            <td width="5%"> 
              <input name="cd3" value="78890" onclick="return MostrarTotal();" type="checkbox">
            </td>
            <td width="19%"> 
              <div align="center"><img src="Tienda_demo_integracion_emisores_files/232042.jpg" height="80" width="80"></div>
            </td>
            <td bgcolor="#efefef" width="49%"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">CLASICO<br>
              <b>COMEDOR</b></font><b><font face="Verdana, Arial, Helvetica, sans-serif" size="1"> 
              GOLLA, 6 SILLAS</font></b></td>
            <td bgcolor="#e0e0e0" width="27%"> 
              <div align="right"><font face="Georgia, Times New Roman, Times, serif" size="3">$ 
                78.890</font></div>
            </td>
          </tr>
          <tr> 
            <td width="5%"> 
              <input name="cd4" value="15590" onclick="return MostrarTotal();" type="checkbox">
            </td>
            <td width="19%"> 
              <div align="center"><img src="Tienda_demo_integracion_emisores_files/405220.jpg" height="80" width="80"></div>
            </td>
            <td bgcolor="#efefef" width="49%"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">MODERNO<br>
              <b>MESA WIDE COFEE</b></font><font face="Verdana, Arial, Helvetica, sans-serif"></font></td>
            <td bgcolor="#e0e0e0" width="27%"> 
              <div align="right"><font face="Georgia, Times New Roman, Times, serif" size="3">$ 
                15.590</font></div>
            </td>
          </tr>
          <tr> 
            <td colspan="4"> 
              <hr size="1">
            </td>
          </tr>
          <tr> 
            <td width="5%"> </td>
            <td width="19%"> </td>
            <td width="49%"><font face="Verdana, Arial, Helvetica, sans-serif" size="1"><? echo $TBK_ORDEN_COMPRA; ?></font></td>
            <td width="27%"> 
              <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">Total</font>: 
                <input name="total" size="6" value="0" align="right" type="text">
              </div>
            </td>
          </tr>
          <tr> 
            <td colspan="4"> 
              <hr size="1">
            </td>
          </tr>
          <tr> 
            <td width="5%"> </td>
            <td width="19%"> </td>
            <td width="49%"><font face="Verdana, Arial, Helvetica, sans-serif"> 
              <input name="TBK_TIPO_TRANSACCION" value="TR_NORMAL" type="HIDDEN">
              <input name="TBK_ID_SESION" value="<? echo $TBK_ID_SESION; ?>" type="HIDDEN">
              <input name="TBK_URL_EXITO" size="40" value="http://www.modulowebpay.co.cc/webpay/exito.php" type="HIDDEN">
              <input name="TBK_URL_FRACASO" size="40" value="http://www.modulowebpay.co.cc/webpay/fracaso.php" type="HIDDEN">
              <input name="TBK_ORDEN_COMPRA" size="40" value="<? echo $TBK_ORDEN_COMPRA; ?>" type="HIDDEN">
              <input name="TBK_MONTO" size="40" value="0" type="HIDDEN">
              </font></td>
            <td width="27%"> 
              <div align="center">
             <input name="Submit" src="Tienda_demo_integracion_emisores_files/b_comprar.jpg" value="Submit" onclick="return ValidaCompra();" type="image">
</div>
            </td>
          </tr>
        </tbody></table>
      </form>
      <p> </p>
      </td>
    <td width="0%"> </td>
  </tr>
  <tr> 
    <td colspan="3"><img src="Tienda_demo_integracion_emisores_files/pieFeria.jpg" height="104" width="978"></td>
  </tr>
</tbody></table>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-10869333-1");
pageTracker._trackPageview();
} catch(err) {}</script>
</body></html>
