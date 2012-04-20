<?php
include_once 'modelo/facebook.php';
include_once 'modelo/UsuarioMP.php';
include_once 'modelo/CarroMP.php';
include_once 'modelo/InvitacionMP.php';
include_once 'util/session.php';

class CPrincipal {
    protected $_CSec;
    protected $usuarioMP;
    protected $secret;
    public $layout = "vista/layout.phtml";
    public $showLayout = true;
    public $thisLayout = true;
    public $isLoged = false;
    public $facebook;
    public $ss;

    function __construct() {
        $this->usMP = new UsuarioMP();
        $this->caMP = new CarroMP();
        $this->invMP = new InvitacionMP();
        $this->ss = new session();
        $this->secret = "6dfc87d94a03dbe7d4512d31f3fc16d2";
        $this->iniFacebook();
        $this->isLoged = $this->checkLogin();
        if(!$this->isLoged) {
            $params = array(
                scope => 'email,publish_stream,user_birthday,publish_actions',
                redirect_uri => 'http://www.lokiero.cl/?do=sign'
            );
            $this->loginUrl = $this->facebook->getLoginUrl($params);
        }
//        echo "<pre>";
//        print_r($this->usuario);
//        echo "</pre>";
//        echo "<pre>";
//        print_r($_SERVER);
//        echo "</pre>";
        $host = explode(".",$_SERVER["HTTP_HOST"]);
//        echo $host[0]."<br>";
//        if($_GET["sec"]!="svip" || $_GET["get"]!="refresh")
//        if($host[0] == "dev" && (!isset($this->usuario) || $this->usuario->ID_USUARIO!=43)) $this->ss->salto("http://www.lokiero.cl".$_SERVER["REQUEST_URI"]);
//        echo "<pre>";
//        print_r($_SESSION);
//        echo "</pre>";
//        $this->getSession()->set("ID_USUARIO", 1);
//        unset($_SESSION["ID_CARRO"]);
//        print_r($_SESSION);
//        $this->isLoged = $this->checkLogin();
//        $this->checkLogin();
        $this->setSec();
    }
    
    function getSecret() {
        return $this->secret;
    }
    
    public function getTitulo() {
        return $this->_CSec->titulo." :: Lo Kiero!.cl - Subastas VIP";
    }

    public function getLayout() {
        if($this->thisLayout) return $this->layout;
        else return $this->_CSec->getLayout();
    }

    function getCSec() {
        return $this->_CSec;
    }

    function getSession() {
        return $this->ss;
    }
    
    function setCarro() {
        if($this->isLoged) {
            $idUser = $this->usuario->ID_USUARIO;
            
            $carro = $this->caMP->lastByUser($idUser, array("ID_CARRO"));
            $res->SQL = $carro->SQL;
            if(isset($carro->ID_CARRO)) {
                $this->caMP->updateCarro($carro->ID_CARRO);
                $carro = $this->caMP->find($carro->ID_CARRO, array("ID_CARRO","MONTO_CARRO", "MONTO_PRODUCTOS"));
                $this->getSession()->set("ID_CARRO", $carro->ID_CARRO);
                $res->ID_CARRO = $carro->ID_CARRO;
                $res->MONTO_CARRO = $carro->MONTO_CARRO;
                $res->MONTO_CARRO_H = $carro->MONTO_CARRO_H;
                $res->MONTO_PRODUCTOS_H = $carro->MONTO_PRODUCTOS_H;
                $res->N_PRODUCTOS = $this->caMP->cuentaProductos($carro->ID_CARRO);
            } else {
                $caAux = new stdClass();
                $caAux->ID_USUARIO = $idUser;
                $caAux->MONTO_CARRO = 0;
                $caAux->FECHA_INICIO = date("U");
                $caAux->ESTADO_CARRO = 0;
                $caAux->ID_CARRO = $this->caMP->save($caAux);
                $this->getSession()->set("ID_CARRO", $caAux->ID_CARRO);
                $res->ID_CARRO = $caAux->ID_CARRO;
                $res->MONTO_CARRO = 0;
                $res->MONTO_CARRO_H = 0;
                $res->MONTO_PRODUCTOS_H = 0;
                $res->N_PRODUCTOS = 0;
            }
            $this->carro = $res;
        }
    }
    
    function iniFacebook() {
        if(!$this->facebook) {
            $this->facebook = new Facebook(array(
              'appId'  => '264213770284841',
              'secret' => '6dfc87d94a03dbe7d4512d31f3fc16d2',
            ));
            $this->user = $this->facebook->getUser();
        }
    }
    
    function sendEmail($data) {
        include_once 'modelo/class.phpmailer.php';
        include_once 'modelo/class.smtp.php';
        
        $mail = new PHPMailer ();
        $mail -> From = "contacto@lokiero.cl";
        $mail -> FromName = "Lo Kiero!";
        $nDes = count($data->destino);
        for($i=0; $i<$nDes; $i++) {
            $mail -> AddAddress ($data->destino[$i]->email, $data->destino[$i]->nombre);
        }
        $mail -> Subject = $data->titulo;
        $mail -> Body = "<table width='100%'  border=0 cellspacing=0 cellpadding=0>
                            <tr>
                                <td><a href='http://www.lokiero.cl/' title='Ir a Lo Kiero!'><img src='http://www.lokiero.cl/img/logo_ema.png' border=0/></a></td>
                            </tr>
                            <tr>
                                <td>".$data->cuerpo."</td>
                            </tr>
                        </table>";
        $mail -> IsHTML (true);
        
        $mail->IsSMTP();
        $mail->Host = 'ssl://smtp.gmail.com';
        $mail->Port = 465;
        $mail->SMTPAuth = true;
        $mail->Username = 'contacto@lokiero.cl';
        $mail->Password = '8Tt7GRivUObe';
        
        return $mail->Send();
    }

    function checkLogin() {
        if($this->ss->existe("ID_FB")) {
            $this->usuario = $this->usMP->find($this->getSession()->get("ID_USUARIO"));
            return true;
        } else return false;
//        if(!$this->ss->existe("ID_FB")) {
//
//            if ($this->user) {
//                try {
//                    $this->user_profile = $this->facebook->api('/me');
//                    $user = new stdClass();
//                    $user->NOM_USUARIO = $this->user_profile["first_name"];
//                    $user->APE_USUARIO = $this->user_profile["last_name"];
//                    $user->EMA_USUARIO = $this->user_profile["email"];
////                    $user->NICK_USUARIO = (isset($this->user_profile["username"]))?$this->user_profile["username"]:"";
//                    $user->NICK_USUARIO = "";
//                    $user->SEXO_USUARIO = ($this->user_profile["gender"]=="male")?1:2;
//                    $user->FB_UID = $this->user;
//                    $user->FB_ACCESS_TOKEN = $this->facebook->getAccessToken();
//                    $res = $this->usMP->save($user);
//                    
//                    $this->getSession()->set("ID_USUARIO", $res->ID_USUARIO);
//                    $this->getSession()->set("NICK_USUARIO", $res->NICK_USUARIO);
//                    $this->getSession()->set("NOM_USUARIO", $res->NOM_USUARIO." ".$res->APE_USUARIO);
//                    $this->getSession()->set("ID_FB", $this->user);
//                    
//                    if($res->IS_NEW == 1) {
//                        if($_POST["id_request"]!=0) {
//                            $req = $this->invMP->acepta($_POST["id_request"], $_POST["session"]["userID"]);
//                        }
//                    }
//                    $this->usuario = $res;
//                    return true;
//                } catch (FacebookApiException $e) {
//                    error_log($e);
//                    $this->user = null;
//                    return false;
//                }
//            }
//        } else {
//            $this->usuario = $this->usMP->find($this->getSession()->get("ID_USUARIO"));
//            return true;
//        }
    }

    function error($e) {
        switch($e) {
            case '404':
                $this->showLayout = false;
                echo "error 404<br>";
                break;
        }
    }

    function setSec() {
        $this->sec = (isset($_GET["sec"]))?$_GET["sec"]:"main";
        $this->showLayout = true;
        $this->thisLayout = true;
        switch($this->sec) {
            case "log":
                include_once 'CLog.php';
                $this->_CSec = new CLog($this);
                break;
            case "svip":
                include_once 'CSVip.php';
                $this->_CSec = new CSVip($this);
                break;
            case "producto":
                include_once 'CProducto.php';
                $this->_CSec = new CProducto($this);
                break;
            case "invitacion":
                include_once 'CInvitacion.php';
                $this->_CSec = new CInvitacion($this);
                break;
            case "carro":
                include_once 'CCarro.php';
                $this->_CSec = new CCarro($this);
                break;
            case "pagina":
                include_once 'CPagina.php';
                $this->_CSec = new CPagina($this);
                break;
            case "cuenta":
                include_once 'CCuenta.php';
                $this->_CSec = new CCuenta($this);
                break;
            case "main":
                include_once 'CMain.php';
                $this->_CSec = new CMain($this);
                break;
        }
        $this->setCarro();
//        echo "<pre>";
//        print_r($this->carro);
//        echo "</pre>";
    }
}
?>