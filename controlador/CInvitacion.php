<?php
include_once 'modelo/UsuarioMP.php';
include_once 'modelo/InvitacionMP.php';

class CInvitacion {
    protected $cp;
    protected $invMP;
    protected $usMP;
    protected $login;
    protected $layout;

    function __construct($cp) {
        $this->cp = $cp;
        $this->layout = "vista/main.phtml";
        $this->usMP = new UsuarioMP();
        $this->invMP = new InvitacionMP();
        $this->catchRequest();
        $this->getJSON();
        $this->setDo();
        $this->setOp();
    }

    function getLayout() {
        return $this->layout;
    }
    
    function catchRequest() {
        if(isset($_GET["request_ids"])) {
            $this->cp->showLayout = false;
            $res = $this->invMP->findByReq($_GET["request_ids"], 0);
//            echo "<pre>";
//            print_r($res);
//            echo "</pre>";
            $nInv = count($res);
            $this->cp->iniFacebook();
            if($nInv > 0) {
                if($nInv == 1) { //1 usuario lo invito
                    $this->invMP->acepta($res[0]->ID_REQUEST, $this->cp->user);
                    $this->cp->getSession()->salto("http://dev.lokiero.cl/#!/");
                } else { //mas de 1 usuario lo invito
                    $this->cp->getSession()->salto("http://dev.lokiero.cl/#!/invitacion/".$_GET["request_ids"]);
                }
            } else {
                $this->cp->getSession()->salto("http://dev.lokiero.cl/#!/");
            }
        }
    }

    function setDo() {
        if(isset($_GET["do"])) {
            $this->cp->showLayout = false;
            $do = $_GET["do"];
            switch($do) {
                case 'invitar':
                    if(count($_POST)>0) {
                        $this->cp->iniFacebook();
                        if($this->cp->user) {
                            $req = $_POST["request"];
                            $to = $_POST["to"];
                            $nTo = count($to);
                            $newInv = array();
                            for($i=0; $i<$nTo; $i++) {
                                $aux = new stdClass();
                                $inv = $this->cp->facebook->api("/".$req."_".$to[$i],'GET');
                                $aux->ID_REQUEST = $req;
                                $aux->ID_USUARIO = $this->cp->getSession()->get("ID_USUARIO");
                                $aux->ID_FROM = $this->cp->getSession()->get("ID_FB");
                                $aux->FECHA_REQUEST = date("Y-m-d H:i:s");
                                $aux->NOM_TO = $inv["to"]["name"];
                                $aux->ID_TO = $to[$i];
                                $aux->ESTADO_INVITACION = 0;
                                $aux->ID_INVITACION = $this->invMP->insert($aux);
                                if($aux->ID_INVITACION > 0) $newInv[] = $aux;
                            }
                            $usAux = $this->usMP->find($this->cp->getSession()->get("ID_USUARIO"), array("INVITACION_TOTAL", "INVITACION_USADA", "INVITACION_RECHAZADA"));
                            $usAux->ID_USUARIO = $this->cp->getSession()->get("ID_USUARIO");
                            $usAux->INVITACION_USADA = $usAux->INVITACION_USADA+count($newInv);
                            unset($usAux->BID_RESTO);
                            unset($usAux->BID_DISPONIBLE);
                            $this->usMP->update($usAux);
                            $res = new stdClass();
                            $res->MODELO->INVITACION_DISP = $usAux->INVITACION_TOTAL - $usAux->INVITACION_USADA + $usAux->INVITACION_RECHAZADA;
                            $res->MODELO->INVITACION_TOTAL = $usAux->INVITACION_TOTAL;
                            $res->MODELO->INVITACION_USADA = $usAux->INVITACION_USADA;
                            $res->INVITACIONES = $newInv;
                            echo json_encode($res);
                        }
                    }
                    break;
                case 'delete':
                    if(count($_POST)>0) {
                        $this->cp->iniFacebook();
                        if($this->cp->user) {
                            $res = new stdClass();
                            if($this->invMP->remove($_POST["id_request"], $_POST["id_to"], $this->cp->getSession()->get("ID_FB"))) {
                                $res->ERROR = 0;
                                $res->MENSAJE = "La invitacion fue eliminada correctamente";
                            } else {
                                $res->ERROR = 1;
                                $res->MENSAJE = "La invitacion no pudo ser eliminada, intentalo nuevamente!";
                            }
                            echo json_encode($res);
                        }    
                    }
                    break;
                case 'aceptar':
                    $this->cp->iniFacebook();
                    if($this->cp->user) {
                        $res = new stdClass();
                        $res->ERROR = 0;
                        if($this->invMP->acepta($_POST["id_request"], $this->cp->user)) {
                            $res->ERROR = 0;
                            $res->MENSAJE = "Listo!, ahora seras redireccionado al inicio";
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "Algo salio mal, por favor intentalo denuevo";
                        }
                    } else {
                        $res->ERROR = 1;
                        $res->MENSAJE = "Debes iniciar tu sesion";
                    }
                    echo json_encode($res);
                    break;
            }
        }
    }
    
    function getJSON() {
        if(isset($_GET["get"])) {
            $this->cp->showLayout = false;
            $get = $_GET["get"];
            switch($get) {
                case 'invitacion':
                    $invAux = $this->invMP->fetchByUser($this->cp->getSession()->get("ID_USUARIO"), array("ID_TO"));
                    $usAux = $this->usMP->find($this->cp->getSession()->get("ID_USUARIO"), array("INVITACION_TOTAL", "INVITACION_USADA", "INVITACION_RECHAZADA"));
                    //limitar el max de invitados por semana
                    $res = new stdClass();
                    $nInv = count($invAux);
                    $res->INVITADOS = array();
                    for($i=0; $i<$nInv; $i++) {
                        $res->INVITADOS[] = $invAux[$i]->ID_TO;
                    }
                    $res->INVITACION_DISP = $usAux->INVITACION_TOTAL-$usAux->INVITACION_USADA+$usAux->INVITACION_RECHAZADA;
                    break;
                case 'inv_request':
                    $res = $this->invMP->findByReq($_GET["id_request"], 0);
//                    echo "<pre>";
//                    print_r($inv);
//                    echo "</pre>";                   
                    break;
                case 'req_fb':
                    $this->cp->iniFacebook();
                    echo $this->cp->facebook->getAccessToken()."<br>";
                    if(isset($_GET["to"])) {
                        $res = $this->cp->facebook->api("/".$_GET["req"]."_".$_GET["to"],'GET');
                    } else {
                        $res = $this->cp->facebook->api("/".$_GET["req"],'GET');
                    }
                    echo "<pre>";
                    print_r($res);
                    echo "</pre>";
                    break;
                case 'del_req':
                    $this->cp->iniFacebook();
                    try {
                        $res = $this->cp->facebook->api("/".$_GET["req"]."_".$_GET["to"]."?access_token=".$this->cp->facebook->getAccessToken(),'DELETE');
                    } catch(FacebookApiException $e) {
                        echo "<pre>";
                        print_r($this->cp->user);
                        echo "</pre>";
                        echo "Error: ".$e."<br>";
                    }
                    break;
                case 'enviadas':
                    $res = $this->invMP->fetchByFb($this->cp->getSession()->get("ID_FB"));
                    break;
                case 'all':
                    $res = new stdClass();
                    if($this->cp->getSession()->existe("ID_USUARIO")) {
                        $usAux = $this->usMP->find($this->cp->getSession()->get("ID_USUARIO"), array("INVITACION_TOTAL", "INVITACION_USADA", "INVITACION_RECHAZADA"));
                        $res->MODELO->INVITACION_DISP = $usAux->INVITACION_TOTAL - $usAux->INVITACION_USADA + $usAux->INVITACION_RECHAZADA;
                        $res->MODELO->INVITACION_TOTAL = $usAux->INVITACION_TOTAL;
                        $res->MODELO->INVITACION_USADA = $usAux->INVITACION_USADA;
                        $res->INVITACIONES = $this->invMP->fetchByFb($this->cp->getSession()->get("ID_FB"));
                    $res->LOGIN = 1;
                    } else {
                        $res->LOGIN = 0;
                    }
                    break;
            }
            echo json_encode($res);
        }
    }

    function setOp() {
        if(isset($_GET["op"])) {
            $op = $_GET["op"];
            switch($op) {
                default:

                    break;
            }
        }
    }
}
?>
