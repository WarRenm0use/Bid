<?php 
include("conexion.php");
?>
<div class="padder"><div class="page-head button-level">
       <div align="justify">
        <p><span class="Estilo2"><strong><?php echo " $t_nombre" ?><?php echo " $t_apellido" ?></strong>,         </span></p>
         <p>Gracias por comprar en Wake up� Store.,
           nos pondremos en contacto con Ud. para gestionar su orden.
           Usted puede comprobar el estado de su orden registrandose en su cuenta.
           
           Si usted tiene alguna consulta respecto a su orden, por favor, pongase en contacto con  contacto@wakeup.cl o llamenos al (56 2) 5709813 De Lunes a Viernes, de 9:00 a 18:00 hrs. en horario continuado.
           
           Tiendas wakeup, est� ubicado en Eduardo Llanos 31, �u�oa, Santiago.
           
           
           Gracias otra vez por su compra.</p>
       </div>
       <br><br>
    <h3>
    Pedido - <?php echo "$t_compra" ?>  </h3>
</div>

<div class="col2-set"><p>Fecha del Pedido: <?php echo $fechapedido ?> </p>  <div class="col2-set">
    <div class="col-1">
        <div class="head-alt3">
          
          <h5 class="title">M�todo de Pago por Venta de Producto</h5>
        </div>
        <p>Tarjeta de Credito / Webpay<br />
        <strong>Codigo de Autorizaci�n </strong><?php echo "$autorizacion" ?> 
        <br />
        <strong>Numero de Tarjeta </strong>XXXXXXXXXXXX-<?php echo "$tar_final" ?> 
        <br />
        <strong>Cantidad de Cuotas</strong> 0<?php echo "$cuotas" ?> 
        <br />
        <strong>Tipo de Cuotas</strong> <?php echo "$vn" ?>
    </div>
    <div class="col-2">

        <div class="head-alt3">
            <h5 class="title">Art�culos pedidos</h5>
        </div>

        <strong>Nombre del Producto</strong> :<?php echo $t_producto?>
 <br /> 
 <strong>Monto Total en Pesos $</strong>:<?php echo "$t_monto" ?>
        <br />      
    </div>
</div>
  <strong>La entrega de su pedido se realizara dependiendo de su regi�n    
   </strong><br>
   <strong>No hay Devoluciones de producto, para reclamos de productos contactarnos a.</strong></strong><br />
<div class="col2-set">
    <div class="col-1">

        <div class="head-alt3">
           
        </div>
        <address>
</address>
    </div>
    <br />
Fono: (56) 2-5709813<br />
<a href="mailto:contacto@wakeup.cl">contacto@wakeup.cl</a><br />
Direcci�n: Eduardo Llanos 31<br />
Comuna: �u�oa<br />
Ciudad: Santiago - Chile<br />
Horario: Lunes a Viernes de 10:00 a 19:00 Hrs
</div>
</div>
<div class="padder"><script type="text/javascript">decorateTable('my-orders-table', {'tbody' : ['odd', 'even'], 'tbody tr' : ['first', 'last']})</script>
<div class="button-set">
      <a href="http://www.modulowebpay.co.cc/webpay/pago.php" class="f-left">� Volver a Mis Pedidos</a>
  </div>
</div>
       
     