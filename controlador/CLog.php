<?php
include_once 'modelo/UsuarioMP.php';

class CLog {
    protected $cp;
    protected $usuMP;
    protected $login;
    protected $layout;

    function __construct($cp) {
        $this->cp = $cp;
        $this->usuMP = new UsuarioMP();
        $this->layout = "vista/login.phtml";
        $this->cp->thisLayout = true;
        $this->setDo();
        $this->setOp();
    }

    function logout() {
        $this->cp->getSession()->kill();
//        $this->cp->getSession()->salto("index.php");
//        echo "<pre>";
//        print_r($_SERVER);
//        echo "</pre>";
        $this->cp->getSession()->salto($_SERVER["HTTP_REFERER"]);
    }

    function getLayout() {
        return $this->layout;
    }

    function checkLogin() {
        $user = new stdClass();
        $user->NOM_USUARIO = $_POST["first_name"];
        $user->APE_USUARIO = $_POST["last_name"];
        $user->EMA_USUARIO = $_POST["email"];
        $user->SEXO_USUARIO = ($_POST["gender"]=="male")?1:2;
        $user->FB_UID = $_POST["session"]["userID"];

        if($user->FB_UID > 0) {
            $res = $this->usuMP->save($user);
            if($res->ID_USUARIO > 0) {
                if(!$this->cp->isLoged || $user->FB_UID != $this->cp->getSession()->get("ID_FB")) {
                    $this->cp->getSession()->set("ID_USUARIO", $res->ID_USUARIO);
                    $this->cp->getSession()->set("NICK_USUARIO", $res->NICK_USUARIO);
                    $this->cp->getSession()->set("EMA_USUARIO", $res->EMA_USUARIO);
                    $this->cp->getSession()->set("NOM_USUARIO", $res->NOM_USUARIO." ".$res->APE_USUARIO);
                    $this->cp->getSession()->set("ID_FB", $_POST["session"]["userID"]);
                    $res->RELOAD = 1;
                } else $res->RELOAD = 0;
            }
            return $res;
        }
    }
    
    function parse_signed_request($signed_request, $secret) {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

        // decode the data
        $sig = $this->base64_url_decode($encoded_sig);
        $data = json_decode($this->base64_url_decode($payload), true);

        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            error_log('Unknown algorithm. Expected HMAC-SHA256');
            return null;
        }

        // check sig
        $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
        if ($sig !== $expected_sig) {
            error_log('Bad Signed JSON signature!');
            return null;
        }

        return $data;
    }

    function base64_url_decode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    function setDo() {
        if(isset($_GET["do"])) {
            $this->cp->showLayout = false;
            $do = $_GET["do"];
            switch($do) {
                case 'in':
                    $res = $this->checkLogin();
                    echo json_encode($res);
                    break;
                case 'out':
                    $this->logout();
                    break;
                case 'reg':
//                    $this->usuMP->save($this->cp->user_profile);
//                    $this->cp->getSession()->salto("/");
                    $response = $this->parse_signed_request($_REQUEST['signed_request'], $this->cp->getSecret());
                    echo "<pre>";
                    print_r($response);
                    echo "</pre>";
                    break;
                case 'setNick':
                    $user = new stdClass();
                    if(!$this->usuMP->existeNick($_POST["username"])) {
                        $user->NICK_USUARIO = $_POST["username"];
                        $user->ID_USUARIO = $this->cp->getSession()->get("ID_USUARIO");
                        $usAux = $this->usuMP->find($user->ID_USUARIO, array("BID_TOTAL", "BID_USADO"));
                        if($this->usuMP->update($user)) {
                            $this->cp->getSession()->set("NICK_USUARIO", $user->NICK_USUARIO);
                            $user->ID_FB = $this->cp->getSession()->get("ID_FB");
                            $user->BID_DISPONIBLE = $usAux->BID_DISPONIBLE;
                            $user->ERROR = 0;
                            $user->MENSAJE = "El nombre de usuario fue guardado correctamente";
                        } else {
                            $user->ERROR = 1;
                            $user->MENSAJE = "El nombre de usuario no pudo ser guardado, por favor intentalo nuevamente";
                        }
                    } else {
                        $user->ERROR = 1;
                        $user->MENSAJE = "El nombre de usuario no esta disponible";
                    }
                    echo json_encode($user);
                    break;
            }
        }
    }

    function setOp() {
        $op = $_GET["op"];
        switch($op) {
            case 'rec':
                $this->layout = "vista/login_recuperar.phtml";
                break;
            case 'reg':
                $this->cp->showLayout = true;
                $this->cp->thisLayout = false;
                $this->layout = "vista/facebook.phtml";
                break;
            default:
                break;
        }
    }
}
?>
