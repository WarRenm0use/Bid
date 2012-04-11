<?php
include_once 'modelo/SubastaMP.php';
include_once 'modelo/BidMP.php';
include_once 'modelo/UsuarioMP.php';
include_once 'modelo/InvitacionMP.php';

class CMain {
    protected $cp;
    protected $suMP;
    protected $login;
    protected $layout;

    function __construct($cp) {
        $this->cp = $cp;
        $this->layout = "vista/main.phtml";
        $this->suMP = new SubastaMP();
        $this->usMP = new UsuarioMP();
        $this->biMP = new BidMP();
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
            $this->cp->getSession()->salto("/#!/invitacion/".$_GET["request_ids"]);
        }
    }

    function setDo() {
        if(isset($_GET["do"])) {
            $this->cp->showLayout = false;
            $do = $_GET["do"];
            switch($do) {
                case 'invitar':
//                    print_r($_POST);
                    $this->cp->iniFacebook();
                    if($this->cp->user) {
                        $req = $_POST["request"];
                        $to = $_POST["to"];
                        $nTo = count($to);
                        for($i=0; $i<$nTo; $i++) {
                            $aux = new stdClass();
                            $inv = $this->cp->facebook->api("/".$req."_".$to[$i],'GET');
                            $aux->ID_REQUEST = $req;
                            $aux->ID_FROM = $this->cp->getSession()->get("ID_FB");
                            $aux->FECHA_REQUEST = date("Y-m-d H:i:s");
                            $aux->NOMBRE_TO = $inv["to"]["name"];
                            $aux->ID_TO = $to[$i];
                            $this->invMP->insert($aux);
                        }
                        $usAux = $this->usMP->find($this->cp->getSession()->get("ID_USUARIO"), array("INVITACION_TOTAL", "INVITACION_USADA"));
                        $usAux->ID_USUARIO = $this->cp->getSession()->get("ID_USUARIO");
                        $usAux->INVITACION_USADA = $usAux->INVITACION_USADA+$nTo;
                        unset($usAux->BID_RESTO);
                        $this->usMP->update($usAux);
                        $usAux->INVITACION_DISP = $usAux->INVITACION_TOTAL - $usAux->INVITACION_USADA;
                        echo json_encode($usAux);
                    }
                    break;
                case 'login':
                    $user = new stdClass();
                    $user->NOM_USUARIO = $_POST["first_name"];
                    $user->APE_USUARIO = $_POST["last_name"];
                    $user->EMA_USUARIO = $_POST["email"];
                    $user->NICK_USUARIO = $_POST["username"];
                    $user->SEXO_USUARIO = ($_POST["gender"]=="male")?1:2;
                    $user->FB_UID = $_POST["session"]["userID"];
                    $user->FB_ACCESS_TOKEN = $_POST["session"]["access_token"];
//                    $user->FB_SECRET = $_POST["session"]["secret"];
//                    $user->FB_SESSION_KEY = $_POST["session"]["session_key"];
//                    echo "lala";
                    $res = $this->usMP->save($user);
                    if($res->ID_USUARIO > 0) {
                        $this->cp->getSession()->set("ID_USUARIO", $res->ID_USUARIO);
                        $this->cp->getSession()->set("NICK_USUARIO", $user->NICK_USUARIO);
                        $this->cp->getSession()->set("ID_FB", $_POST["session"]["userID"]);
                        if($res->IS_NEW == 1) {
                            if($_POST["id_request"]!=0) {
                                $req = $this->invMP->acepta($_POST["id_request"], $_POST["session"]["userID"]);
                            }
                        }
                        echo json_encode($res);
                    }
                    break;
                case 'bid':
                    $idUs = $this->cp->getSession()->get("ID_USUARIO");
                    $nomUs = $this->cp->getSession()->get("NICK_USUARIO");
//                    echo "idUs: ".$idUs;
                    if(isset($_POST["ID_SUBASTA"]) && $this->cp->isLoged && $idUs>0) {
                        $data = new stdClass();
                        $now = date("U");
                        $data->ID_SUBASTA = $_POST["ID_SUBASTA"];
                        $data->ID_USUARIO = $idUs;
                        $data->HORA_BID = $now;
//                        print_r($data);
                        $subAux = $this->suMP->find($data->ID_SUBASTA);
                        $usAux = $this->usMP->find($data->ID_USUARIO);
//                        print_r($usAux);
                        $sub = new stdClass();
                        if($subAux->RESTO_TIEMPO_SEC>=0 && $usAux->BID_RESTO > 0) {
                            $this->biMP->save($data);
                            $subAux->MONTO_SUBASTA += $subAux->COSTO_BID_PESOS;
                            if($subAux->RESTO_TIEMPO_SEC<=10) {
                                $subAux->RETRASO_SUBASTA += $subAux->TIEMPO_RETRASO;
                            }
                            $usAux2 = new stdClass();
                            $usAux2->BID_USADO = $usAux->BID_USADO + $subAux->COSTO_BID_BID;
                            $usAux2->ID_USUARIO = $usAux->ID_USUARIO;
                            $subAux2 = new stdClass();
                            $subAux2->MONTO_SUBASTA = $subAux->MONTO_SUBASTA;
                            $subAux2->RETRASO_SUBASTA = $subAux->RETRASO_SUBASTA;
                            $subAux2->ID_SUBASTA = $subAux->ID_SUBASTA;
                            $subAux2->ID_USUARIO = $idUs;
                            $this->usMP->update($usAux2);
                            $sub = $this->suMP->update($subAux2);
                            $sub->BID_RESTO = $usAux->BID_TOTAL - $usAux2->BID_USADO;
                        } else {
                            $sub = $subAux;
                            $sub->BID_RESTO = $usAux->BID_TOTAL - $usAux->BID_USADO;
                        }
                        
                        echo json_encode($sub);
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
                case 'invitacion':
                    $invAux = $this->invMP->fetchByUser($this->cp->getSession()->get("ID_FB"), array("ID_TO"));
                    $usAux = $this->usMP->find($this->cp->getSession()->get("ID_USUARIO"), array("INVITACION_TOTAL", "INVITACION_USADA"));
                    //limitar el max de invitados por semana
                    $res = new stdClass();
                    $nInv = count($invAux);
                    $res->INVITADOS = array();
                    for($i=0; $i<$nInv; $i++) {
                        $res->INVITADOS[] = $invAux[$i]->ID_TO;
                    }
                    $res->INVITACION_DISP = $usAux->INVITACION_TOTAL-$usAux->INVITACION_USADA;
                    break;
                case 'subastas':
                    $res = $this->suMP->fetchActive($_GET["ord"]);
                    break;
                case 'subasta':
                    if(isset($_GET["id"])) {
                        $res = $this->suMP->find($_GET["id"]);
                    } else $res = null;
                    break;
                case 'inv_request':
                    $res = $this->invMP->findByReq($_GET["id_request"]);
//                    echo "<pre>";
//                    print_r($inv);
//                    echo "</pre>";                    
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
