<?php
include_once 'modelo/SubastaMP.php';
include_once 'modelo/BidMP.php';
include_once 'modelo/UsuarioMP.php';

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
                case 'login':
                    $user = new stdClass();
                    $user->NOM_USUARIO = $_POST["first_name"];
                    $user->APE_USUARIO = $_POST["last_name"];
                    $user->EMA_USUARIO = $_POST["email"];
                    $user->NICK_USUARIO = $_POST["username"];
                    $user->SEXO_USUARIO = ($_POST["gender"]=="male")?1:2;
                    $user->FB_UID = $_POST["session"]["uid"];
                    $user->FB_ACCESS_TOKEN = $_POST["session"]["access_token"];
                    $user->FB_SECRET = $_POST["session"]["secret"];
                    $user->FB_SESSION_KEY = $_POST["session"]["session_key"];
                    
                    $res = $this->usMP->save($user);
                    if($res->ID_USUARIO > 0) {
                        $this->cp->getSession()->set("ID_USUARIO", $res->ID_USUARIO);
                        echo json_encode($res);
                    } else {
                        
                    }
                    break;
                case 'bid':
                    $idUs = $this->cp->getSession()->get("ID_USUARIO");
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
                            $this->suMP->update($subAux2);
                        }
                        echo $usAux->BID_TOTAL - $usAux->BID_USADO;
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
                case 'subastas':
                    $res = $this->suMP->fetchAll($_GET["ord"]);
                    break;
                case 'subasta':
                    if(isset($_GET["id"])) {
                        $res = $this->suMP->find($_GET["id"]);
                    } else $res = null;
                    break;
            }
            echo json_encode($res);
        }
    }

    function setOp() {
        $op = $_GET["op"];
        switch($op) {
            default:
                
                break;
        }
    }
}
?>
