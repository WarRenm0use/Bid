<?php
include_once 'modelo/UsuarioMP.php';
include_once 'modelo/CarroMP.php';
include_once 'modelo/InvitacionMP.php';
include_once 'modelo/SubastaVipMP.php';

class CMain {
    protected $cp;
    protected $caMP;
    protected $login;
    protected $layout;

    function __construct($cp) {
        $this->cp = $cp;
        $this->layout = "vista/main.phtml";
        $this->usMP = new UsuarioMP();
        $this->caMP = new CarroMP();
        $this->invMP = new InvitacionMP();
        $this->suMP = new SubastaVipMP();
//        $this->catchRequest();
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
                case 'sign':
                    $user = new stdClass();
                    $user->NOM_USUARIO = $_POST["first_name"];
                    $user->APE_USUARIO = $_POST["last_name"];
                    $user->EMA_USUARIO = $_POST["email"];
                    $user->NICK_USUARIO = "";
                    $user->SEXO_USUARIO = ($_POST["gender"]=="male")?1:2;
                    $user->FB_UID = $_POST["session"]["userID"];
                    $user->FB_ACCESS_TOKEN = $_POST["session"]["access_token"];
                    if($user->FB_UID > 0) {
                        $res = $this->usMP->save($user);
                        if($res->ID_USUARIO > 0) {
                            $this->cp->getSession()->set("ID_USUARIO", $res->ID_USUARIO);
                            $this->cp->getSession()->set("NICK_USUARIO", $res->NICK_USUARIO);
                            $this->cp->getSession()->set("EMA_USUARIO", $res->EMA_USUARIO);
                            $this->cp->getSession()->set("NOM_USUARIO", $res->NOM_USUARIO." ".$res->APE_USUARIO);
                            $this->cp->getSession()->set("ID_FB", $_POST["session"]["userID"]);
                            $carro = $this->caMP->lastByUser($res->ID_USUARIO, array("ID_CARRO"));
                            $res->SQL = $carro->SQL;
                            if(isset($carro->ID_CARRO)) {
                                $this->caMP->updateCarro($carro->ID_CARRO);
                                $carro = $this->caMP->find($carro->ID_CARRO, array("ID_CARRO","MONTO_CARRO"));
                                $this->cp->getSession()->set("ID_CARRO", $carro->ID_CARRO);
                                $res->ID_CARRO = $carro->ID_CARRO;
                                $res->MONTO_CARRO = $carro->MONTO_CARRO;
                                $res->MONTO_CARRO_H = $carro->MONTO_CARRO_H;
                                $res->N_PRODUCTOS = $this->caMP->cuentaProductos($carro->ID_CARRO);
                            } else {
                                $caAux = new stdClass();
                                $caAux->ID_USUARIO = $this->cp->getSession()->get("ID_USUARIO");
                                $caAux->MONTO_CARRO = 0;
                                $caAux->FECHA_INICIO = date("U");
                                $caAux->ESTADO_CARRO = 0;
                                $caAux->ID_CARRO = $this->caMP->save($caAux);
                                $this->cp->getSession()->set("ID_CARRO", $caAux->ID_CARRO);
                                $res->ID_CARRO = $caAux->ID_CARRO;
                                $res->MONTO_CARRO = 0;
                                $res->MONTO_CARRO_H = 0;
                                $res->N_PRODUCTOS = 0;
                            }
                            if($res->IS_NEW == 1) {
                                if($_POST["id_request"]!=0) {
                                    $req = $this->invMP->acepta($_POST["id_request"], $_POST["session"]["userID"]);
                                }
                                $this->cp->iniFacebook();
                                try {
                                    $this->cp->facebook->api('/me/feed', 'POST', array(
                                        'link' => 'www.lokiero.cl',
                                        'message' => 'Estoy usando Lo Kiero!, la nueva forma de comprar los mejores productos con descuentos increibles, tu tambien puedes registrarte, es gratis!',
                                        'icon' => 'http://www.lokiero.cl/img/icono.png',
                                        'picture' => 'http://www.lokiero.cl/img/logoFB.png'
                                    ));
                                } catch(FacebookApiException $e) {}
                            }
                        }
                        $this->cp->getSession()->salto("/");
                    }
                    break;
                case 'login':
                    $user = new stdClass();
                    $user->NOM_USUARIO = $_POST["first_name"];
                    $user->APE_USUARIO = $_POST["last_name"];
                    $user->EMA_USUARIO = $_POST["email"];
//                    $user->NICK_USUARIO = $_POST["username"];
                    $user->SEXO_USUARIO = ($_POST["gender"]=="male")?1:2;
                    $user->FB_UID = $_POST["session"]["userID"];
//                    $user->FB_ACCESS_TOKEN = $_POST["session"]["access_token"];
                    if($user->FB_UID > 0) {
                    $res = $this->usMP->save($user);
                    if($res->ID_USUARIO > 0) {
                        $this->cp->getSession()->set("ID_USUARIO", $res->ID_USUARIO);
                        $this->cp->getSession()->set("NICK_USUARIO", $res->NICK_USUARIO);
                        $this->cp->getSession()->set("EMA_USUARIO", $res->EMA_USUARIO);
                        $this->cp->getSession()->set("NOM_USUARIO", $res->NOM_USUARIO." ".$res->APE_USUARIO);
                        $this->cp->getSession()->set("ID_FB", $_POST["session"]["userID"]);
                        $carro = $this->caMP->lastByUser($res->ID_USUARIO, array("ID_CARRO"));
                        $res->SQL = $carro->SQL;
                        if(isset($carro->ID_CARRO)) {
                            $this->caMP->updateCarro($carro->ID_CARRO);
                            $carro = $this->caMP->find($carro->ID_CARRO, array("ID_CARRO","MONTO_CARRO"));
                            $this->cp->getSession()->set("ID_CARRO", $carro->ID_CARRO);
                            $res->ID_CARRO = $carro->ID_CARRO;
                            $res->MONTO_CARRO = $carro->MONTO_CARRO;
                            $res->MONTO_CARRO_H = $carro->MONTO_CARRO_H;
                            $res->N_PRODUCTOS = $this->caMP->cuentaProductos($carro->ID_CARRO);
                        } else {
                            $caAux = new stdClass();
                            $caAux->ID_USUARIO = $this->cp->getSession()->get("ID_USUARIO");
                            $caAux->MONTO_CARRO = 0;
                            $caAux->FECHA_INICIO = date("U");
                            $caAux->ESTADO_CARRO = 0;
                            $caAux->ID_CARRO = $this->caMP->save($caAux);
                            $this->cp->getSession()->set("ID_CARRO", $caAux->ID_CARRO);
                            $res->ID_CARRO = $caAux->ID_CARRO;
                            $res->MONTO_CARRO = 0;
                            $res->MONTO_CARRO_H = 0;
                            $res->N_PRODUCTOS = 0;
                        }
                        if($res->IS_NEW == 1) {
                            if($_POST["id_request"]!=0) {
                                $req = $this->invMP->acepta($_POST["id_request"], $_POST["session"]["userID"]);
                            }
                            $this->cp->iniFacebook();
                            try {
                                $this->cp->facebook->api('/me/feed', 'POST', array(
                                    'link' => 'www.lokiero.cl',
                                    'message' => 'Estoy usando Lo Kiero!, la nueva forma de comprar los mejores productos con descuentos increibles, tu tambien puedes registrarte, es gratis!',
                                    'icon' => 'http://www.lokiero.cl/img/icono.png',
                                    'picture' => 'http://www.lokiero.cl/img/logoFB.png'
                                ));
                            } catch(FacebookApiException $e) {}
                        }
                        echo json_encode($res);
                    }
                    }
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
            echo json_encode($res);
        }
    }

    function setOp() {
        $op = $_GET["op"];
        switch($op) {
            default:
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
                $this->smain = $res;
                $this->titulo = "Inicio";
                break;
        }
    }
}
?>
