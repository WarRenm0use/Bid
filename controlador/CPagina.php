<?php
include_once 'modelo/PaginaMP.php';

class CPagina {
    protected $cp;
    protected $login;
    protected $layout;

    function __construct($cp) {
        $this->cp = $cp;
        $this->layout = "vista/pagina.phtml";
        $this->paMP = new PaginaMP();
        $this->getJSON();
        $this->setDo();
        $this->setId();
    }

    function getLayout() {
        return $this->layout;
    }
    
    function setDo() {
        if(isset($_GET["do"])) {
            $this->cp->showLayout = false;
            $do = $_GET["do"];
            switch($do) {
                case 'contacto':
                    $msg = new stdClass();
                    $msg->titulo = "Nuevo Mensaje - LoKiero.cl";
                    $msg->cuerpo = "Nombre: ".$_POST["nom"]."<br/>Email: ".$_POST["ema"]."<br/>Usuario: ".$_POST["id_us"]."<br/>Mensaje: <br>".$_POST["msg"];
                    $msg->destino[0] = new stdClass();
                    $msg->destino[0]->email = "alvaro@lokiero.cl";
                    $msg->destino[0]->nombre = "Lo Kiero!";
                    $res = new stdClass();
                    $res->titulo = "Nuevo Mensaje - LoKiero.cl";
                    $res->cuerpo = "Estimado ".$_POST["nom"]." (".$_POST["ema"]."), ya recibimos tu mensaje, pronto nos comunicaremos contigo!.<br/><br/>Gracias por escribirnos";
                    $res->destino[0] = new stdClass();
                    $res->destino[0]->email = $_POST["ema"];
                    $res->destino[0]->nombre = $_POST["nom"];
                    
                    $m1 = $this->cp->sendEmail($msg);
                    $m2 = $this->cp->sendEmail($res);
                    $out = new stdClass();
                    if($m1 && $m2) {
                        $out->ERROR = 0;
                        $out->MENSAJE = "Hemos recibido tu mensaje, gracias por escribirnos!";
                    } else {
                        $out->ERROR = 1;
                        $out->MENSAJE = "Ups!, algo salio mal, por favor intentalo nuevamente";
                    }
                    
                    echo json_encode($out);
                    break;
            }
        }
    }
    
    function getJSON() {
        if(isset($_GET["get"])) {
            $this->cp->showLayout = false;
            $get = $_GET["get"];
            switch($get) {   
            }
        }
    }
    
    function setId() {
        $op = $_GET["id"];
        switch($op) {
            case 'fracaso':
                if(isset($_POST["TBK_ORDEN_COMPRA"])) {
                    $this->layout = "vista/fracaso.phtml";
                } else $this->cp->getSession()->salto("/");
                break;
            case 'exito':
                if(isset($_POST["TBK_ORDEN_COMPRA"])) {
                    require_once 'modelo/WebPayMP.php';
                    $this->layout = "vista/exito.phtml";
                    
                    $this->wpMP = new WebPayMP();
                    $this->caMP = new CarroMP();
                    $this->wp = $this->wpMP->find($_POST["TBK_ORDEN_COMPRA"]);
                    $this->ca = $this->caMP->find($_POST["TBK_ORDEN_COMPRA"]);
                    $this->prod = $this->caMP->fetchProductos($_POST["TBK_ORDEN_COMPRA"]);
                    $this->nProd = count($this->prod);
                    $this->resumen = new stdClass();
                    $this->resumen->totalProd = 0;
                    $this->resumen->totalMonto = 0;
                    $this->resumen->totalBid = 0;
                    for($i=0; $i<$this->nProd; $i++) {
                        $this->resumen->totalProd += $this->prod[$i]->CANTIDAD_CARRO_PROD;
                        $this->resumen->totalMonto += $this->prod[$i]->PRECIO_CARRO_PROD*$this->prod[$i]->CANTIDAD_CARRO_PROD;
                        $this->resumen->totalBid += $this->prod[$i]->VALOR_BID*$this->prod[$i]->CANTIDAD_CARRO_PROD;
                    }

                    switch ($this->wp->Tbk_Tipo_Pago) {
                        case VN:
                            $this->vn = ("Sin Cuotas");
                            break;
                        case SI:
                            $this->vn = ("Sin Intereses");
                            break;
                        case VC:
                            $this->vn = ("Cuotas Normales");
                            break;
                        case CI:
                            $this->vn = ("Cuotas Comercio");
                            break;
                    }
                    
                } else $this->cp->getSession()->salto("/");
                break;
            default:
                $this->pagina = $this->paMP->findByHash($op);
                if($this->pagina) {
                    $this->titulo = $this->pagina->PAGINA_TITULO;
                    if($this->pagina->PAGINA_TIPO == 2) {
                        $this->layout = "vista/".$this->pagina->PAGINA_HTML;
                    }
                } else {
                    $this->titulo = "404";
                    $this->layout = "vista/404.phtml";
                }
                break;
        }
    }

}
?>
