<?php
include_once 'modelo/SubastaVipMP.php';
include_once 'modelo/ProductoMP.php';
include_once 'modelo/BidMP.php';
include_once 'modelo/UsuarioMP.php';
include_once 'modelo/InvitacionMP.php';

class CSVip {
    protected $cp;
    protected $suMP;
    protected $login;
    protected $layout;

    function __construct($cp) {
        $this->cp = $cp;
        $this->layout = "vista/svip.phtml";
        $this->suMP = new SubastaVipMP();
        $this->usMP = new UsuarioMP();
        $this->biMP = new BidMP();
        $this->getJSON();
        $this->setDo();
        $this->setOp();
    }

    function getLayout() {
        return $this->layout;
    }
    
    function setDo() {
        if(isset($_GET["do"])) {
            $this->cp->showLayout = false;
            $do = $_GET["do"];
            switch($do) {
                case 'delReserva':
                    $idUs = $this->cp->getSession()->get("ID_USUARIO");
                    $data = $this->suMP->inSubasta($idUs, $_POST["ID_SVIP"]);
                    $res = new stdClass();
                    $res->ID_SVIP = $data->ID_SVIP;
                    if($data) {
                        if($data->ESTADO_SUBASTA == 0) {
                            $res->ERROR = !$this->suMP->delReserva($idUs, $data->ID_SVIP);
                            if(!$res->ERROR) {
                                $sub = $this->suMP->find($data->ID_SVIP, array("BID_ENTRADA", "MIN_USUARIO"));
                                $res->BID_DISPONIBLE = $this->usMP->devuelveBid($sub->BID_ENTRADA, $idUs);
                                $nUs = $this->suMP->nUsSubasta($data->ID_SVIP);
                                $res->RESTO_USUARIOS = $sub->MIN_USUARIO - $nUs;
                                $res->MENSAJE = "La Reserva fue anulada correctamente";
                            } else {
                                $res->MENSAJE = "La Reserva no pudo ser anulada, intentalo nuevamente";
                            }
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "Ya no puedes anular tu reserva";
                        }
                    } else {
                        $res->ERROR = 1;
                        $res->MENSAJE = "No eres parte de la subasta";
                    }
                    echo json_encode($res);
                    break;
                case 'checkReserva':
                    $data = new stdClass();
                    $data = $this->suMP->inSubastaFB($_GET["fb_id"], $_GET["id"]);
                    if($data) {
                        $data->IN = 1;
                    } else {
                        $data = new stdClass();
                        $data->IN = 0;
                    }
                    echo json_encode($data);
                    break;
                case 'recarga':
                    $idUs = $this->cp->getSession()->get("ID_USUARIO");
                    $ses = $this->cp->getSession()->get($_POST["COD_SUBASTA"]);
                    if(isset($_POST["ID_SVIP"]) && $this->cp->isLoged && $idUs>0 && $ses) {
                        $idSu = $_POST["ID_SVIP"];
                        $monto = $_POST["MONTO_RECARGA"];
                        $suAux = $this->suMP->find($idSu, array("N_RECARGA_BID", "MAX_RECARGA_BID", "ESTADO_SUBASTA"));
                        $usAux = $this->suMP->fetchUsuario($idSu, $idUs);
                        $usu = $this->usMP->find($idUs, array("BID_TOTAL", "BID_USADO"));
                        $usAux->RECARGA_RESTO = $suAux->N_RECARGA_BID - $usAux->RECARGA_USADA;
//                        echo json_encode($usAux);
                        if($suAux->ESTADO_SUBASTA == 3) {
                            if($monto <= $suAux->MAX_RECARGA_BID) {
                                if($usAux->RECARGA_RESTO > 0) {
                                    if($usu->BID_DISPONIBLE >= $monto) {
                                        $data = $this->suMP->recargaBid($monto, $idSu, $idUs);
                                        $data->BID_RESTO_US = $this->usMP->restaBid($monto, $idUs);
                                        $data->RECARGA_RESTO = $suAux->N_RECARGA_BID - $data->RECARGA_USADA;
                                        $data->ERROR = 0;
                                        $data->MENSAJE = "Se recargaron $monto bids";
                                    } else {
                                        $data->ERROR = 1;
                                        $data->MENSAJE = "No tienes bids suficientes para recargar";
                                    }
                                } else {
                                    $data->ERROR = 1;
                                    $data->MENSAJE = "Alcanzaste el limite de recargas";
    //                                $data = $usAux;
    //                                $data->BID_RESTO_US = $usu->BID_RESTO;
                                    $data->RECARGA_RESTO = $usAux->RECARGA_RESTO;
                                }
                            } else {
                                $data->ERROR = 1;
                                $data->MENSAJE = "Recarga invalida";
                            }
                        } else {
                            $data->ERROR = 1;
                            $data->MENSAJE = "Subasta invalida";
                        }
                        echo json_encode($data);
                    }
                    break;
                case 'reservar':
                    $sub = $this->suMP->find($_POST["ID_SVIP"]);
                    $data = new stdClass();
                    $data->ID_SVIP = $sub->ID_SVIP;
                    if($this->cp->getSession()->existe("ID_USUARIO")) {
                        $idUs = $this->cp->getSession()->get("ID_USUARIO");
                        $usu = $this->usMP->find($idUs, array("NOM_USUARIO", "APE_USUARIO", "EMA_USUARIO", "NICK_USUARIO", "BID_TOTAL", "BID_USADO"));
                        $now = date("U");
                        $nUs = $this->suMP->nUsSubasta($sub->ID_SVIP);
                        if(!$this->suMP->inSubasta($idUs, $sub->ID_SVIP)) {
                            if($sub->ESTADO_SUBASTA == 0) {
                                if(($sub->MAX_USUARIO != 0 && $sub->MAX_USUARIO > $nUs) || $sub->MAX_USUARIO == 0) {
                                    if($usu->BID_DISPONIBLE >= $sub->BID_ENTRADA) {
                                        $data->ID_SVIP = $sub->ID_SVIP;
                                        $data->ID_USUARIO = $idUs;
                                        $data->BID_TOTAL = $sub->BID_BASE_USUARIO;
                                        $data->BID_USADO = 0;
                                        $data->FECHA_RESERVA = date("Y-m-d H:i:s", $now);
                                        $data->ESTADO_RESERVA = 1;
                                        $data->ID_SVIP_USUARIO = $this->suMP->setReserva($data);
                                        if($data->ID_SVIP_USUARIO > 0) {
                                            $data->RESTO_USUARIOS = $sub->MIN_USUARIO - $nUs - 1;
                                            $data->ERROR = 0;
                                            $data->BID_DISPONIBLE = $this->usMP->restaBid($sub->BID_ENTRADA, $idUs);
                                            $data->MENSAJE = "Listo, ahora solo espera a que comience!";
//                                            notificacion
                                            $this->proMP = new ProductoMP();
                                            $prod = $this->proMP->find($sub->ID_PRODUCTO, null, true);
                                            $email = new stdClass();
                                            $destino = new stdClass();
                                            $destino->email = $usu->EMA_USUARIO;
                                            $destino->nombre = $usu->NOM_USUARIO." ".$usu->APE_USUARIO;
                                            $email->destino[] = $destino;
                                            $email->titulo = "Reserva lista! - Lo Kiero!";
                                            $email->cuerpo = "<table border=0 cellspacing=0 cellpadding=0 style='color:#666;'><tr><td><img src='http://dev.lokiero.cl/producto/".$prod->URL_IMAGEN."' width='300'></td><td><h1>".$destino->nombre." (".$usu->NICK_USUARIO.")</h1><p>La reserva para la subasta de un ".$prod->NOM_PRODUCTO." fue realizada correctamente, para ingresar visita esta pagina <a href='http://dev.lokiero.cl/svip/".$sub->COD_SUBASTA."'>Subasta ".$prod->NOM_PRODUCTO."</a></p><p>15 minutos antes de que comience la subasta se verificara que se haya logrado el minimo de usuarios requeridos, si se alcanza el minimo se activara la subasta, si no, sera anulada y reembolsaremos los bids que gastaste en la reserva.</p></td></tr></table>";
                                            $this->cp->sendEmail($email);
                                        } else {
                                            $data->ERROR = 1;
                                            $data->MENSAJE = "La reserva no pudo ser realizada, intentalo nuevamente";
                                        }
                                    } else { //faltan bids
                                        $data->ERROR = 1;
                                        $data->MENSAJE = "No tienes Bids suficientes :(";
                                    }
                                } else { //se alcanzo el maximo
                                    $data->ERROR = 1;
                                    $data->MENSAJE = "Ya no quedan cupos :(";
                                }
                            } else {
                                $data->ERROR = 1;
                                $data->MENSAJE = "Ya es demasiado tarde para reservar tu cupo :(";
                            }
                        } else { //ya esta en la subasta
                            $data->ERROR = 0;
                            $data->RESTO_USUARIOS = $sub->MIN_USUARIO - $nUs;
                            $data->MENSAJE = "Ya eres parte de la subasta ;)";
                        }
                    } else {
                        $data->ERROR = 1;
                        $data->MENSAJE = "Debes iniciar sesi&oacuten";
                    }
                    echo json_encode($data);
                    break;
                case 'bid':
                    $idUs = $this->cp->getSession()->get("ID_USUARIO");
                    $nomUs = $this->cp->getSession()->get("NICK_USUARIO");
                    $ses = $this->cp->getSession()->get($_POST["COD_SUBASTA"]);
//                    $res = new stdClass();
//                    $res->post = $_POST;
//                    $res->ses = $_SESSION;
//                    $res->idUs = $idUs;
//                    $res->nomUs = $nomUs;
//                    echo json_encode($res);
                    if(isset($_POST["COD_SUBASTA"]) && isset($_POST["ID_SVIP"]) && $this->cp->isLoged && $idUs>0 && $ses) {
                        $data = new stdClass();
                        $now = microtime(true);
                        $data->ID_SVIP = $_POST["ID_SVIP"];
                        $data->ID_USUARIO = $idUs;
                        $data->HORA_BID = $now;
//                        print_r($data);
                        $subAux = $this->suMP->refreshById($data->ID_SVIP);
                        $usAux = $this->suMP->fetchUsuario($data->ID_SVIP, $data->ID_USUARIO);
//                        print_r($usAux);
//                        print_r($subAux);
                        $sub = new stdClass();
                        if($subAux->ESTADO_SUBASTA == 3 && $usAux->BID_RESTO > 0) {
                            $this->biMP->save($data);
                            $this->suMP->doBid($data->ID_SVIP, $data->ID_USUARIO);
                            $usAux->BID_RESTO--;
                            if($subAux->RESTO_TIEMPO_SEC<=10) {
                                $this->suMP->retrasa($data->ID_SVIP);
                            }
                        }
                        $sub = $this->suMP->refreshById($_POST["ID_SVIP"]);
                        $sub->BID_RESTO = $usAux->BID_RESTO;
                        echo json_encode($sub);
                    }
                    break;
            }
            die();
        }
    }
    
    function getJSON() {
        if(isset($_GET["get"])) {
            $this->cp->showLayout = false;
            $get = $_GET["get"];
            switch($get) {
                case 'nextSubasta':
                    $res = $this->suMP->nextSubasta();
                    $nUs = $this->suMP->nUsSubasta($res->ID_SVIP);
                    $res->RESTO_USUARIOS = $res->MIN_USUARIO - $nUs;
                    if($this->cp->getSession()->existe("ID_USUARIO")) {
                        if($this->suMP->inSubasta($this->cp->getSession()->get("ID_USUARIO"), $res->ID_SVIP)) {
                            $res->IN_SUBASTA = 1;
                        } else {
                            $res->IN_SUBASTA = 0;
                        }
                    }
                    break;
                case 'subasta':
                    $res = new stdClass();
                    $res->ERROR = 1;
                    $res->ALLOW = 0;
                    if(isset($_GET["cod"])) {
                        $_GET["cod"] = mysql_escape_string($_GET["cod"]);
                        $res = $this->suMP->findByCod($_GET["cod"]);
                        if($this->cp->getSession()->existe("ID_USUARIO")) {
                            $idUs = $this->cp->getSession()->get("ID_USUARIO");
                            if($this->suMP->inSubasta($idUs, $res->ID_SVIP)) {
                                $usAux = $this->suMP->fetchUsuario($res->ID_SVIP, $idUs);
                                $res->BID_RESTO = $usAux->BID_RESTO;
                                $res->RECARGA_RESTO = $res->N_RECARGA_BID - $usAux->RECARGA_USADA;
                                $this->cp->getSession()->set($_GET["cod"], 1);
                                $this->cp->getSession()->set($_GET["cod"]."_id", $res->ID_SVIP);
                                $res->ALLOW = 1;
                            } else {
                                $this->cp->getSession()->set($_GET["cod"], 0);
                                $this->cp->getSession()->set($_GET["cod"]."_id", $res->ID_SVIP);
                            }
                        }
                    }
                    break;
                case 'refresh':
//                    $ses = $this->cp->getSession()->get($_GET["cod"]);
                    if(isset($_GET["cod"])) {
//                        $res = $this->suMP->refreshById($this->cp->getSession()->get($_GET["cod"]."_id"));
                        include $_GET["cod"].'.json';
                    } else $res = null;
                    break;
                case 'generate':
                    $res = $this->suMP->refreshById($_GET["id"]);
                    break;
                case 'lala':
                    include $_GET["cod"].'.json';
                    break;
                case 'fetchAll':
                    $res = $this->suMP->fetchActive();
                    break;
            }
            if($res!=null)
            echo json_encode($res);
            die();
        }
    }

    function setOp() {
        $op = $_GET["op"];
        switch($op) {
            default:
                $res = new stdClass();
                $res->ERROR = 1;
                $res->ALLOW = 0;
                if(isset($_GET["id"])) {
                    $_GET["id"] = mysql_escape_string($_GET["id"]);
                    $res = $this->suMP->findByCod($_GET["id"]);
                    $idUs = $this->cp->getSession()->get("ID_USUARIO");
                    if($this->suMP->inSubasta($idUs, $res->ID_SVIP)) {
                        $usAux = $this->suMP->fetchUsuario($res->ID_SVIP, $idUs);
                        $res->BID_RESTO = $usAux->BID_RESTO;
                        $res->RECARGA_RESTO = $res->N_RECARGA_BID - $usAux->RECARGA_USADA;
                        $this->cp->getSession()->set($_GET["id"], 1);
                        $this->cp->getSession()->set($_GET["id"]."_id", $res->ID_SVIP);
                        $res->ALLOW = 1;
                    } else {
                        $this->cp->getSession()->set($_GET["id"], 0);
                        $this->cp->getSession()->set($_GET["id"]."_id", $res->ID_SVIP);
                        $res->ALLOW = 0;
                    }
                    $this->svip = $res;
                    $this->titulo = $res->NOM_PRODUCTO;
                }
                break;
        }
    }
}
?>