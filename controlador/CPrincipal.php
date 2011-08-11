<?php
//require_once "modelo/base_facebook.php";
include_once 'modelo/facebook.php';
include_once 'modelo/UsuarioMP.php';
include_once 'util/session.php';

class CPrincipal {
    protected $_CSec;
    protected $usuarioMP;
    public $layout = "vista/layout.phtml";
    public $showLayout = true;
    public $thisLayout = true;
    public $isLoged = false;
    public $ss;

    function __construct() {
        $this->usuMP = new UsuarioMP();
        $this->ss = new session();
        $this->isLoged = true;
        $this->getSession()->set("ID_USUARIO", 1);
//        print_r($_SESSION);
//        $this->isLoged = $this->checkLogin();
//        $this->checkLogin();
        $this->setSec();
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

    function checkLogin() {
        $this->facebook = new Facebook(array(
            'appId'  => '111254775631703',
            'secret' => '6628193d182dcea7faf260c9d1296a1c',
            'cookie' => true
        ));
        $this->user = $this->facebook->getUser();
        echo "<pre>facebook: ";
        print_r($this->facebook);
        echo "</pre>";
        echo "<pre>user: ";
        print_r($this->user);
        echo "</pre>";
        
        if ($this->user) {
            try {
                echo "loged<br>";
                // Proceed knowing you have a logged in user who's authenticated.
                $this->user_profile = $this->facebook->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                echo "not loged: $e<br>";
                $this->user = null;
            }
        }

        if ($this->user) {
            echo "2: loged<br>";
            $this->logoutUrl = $this->facebook->getLogoutUrl();
        } else {
            echo "2: not loged<br>";
            $this->loginUrl = $this->facebook->getLoginUrl();
        }
//        $this->getSession()->set("ID_USUARIO", 1);
        return true;
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
        $this->sec = $_GET["sec"];
        $this->showLayout = true;
        $this->thisLayout = true;
        switch($this->sec) {
            case "log":
                include_once 'CLog.php';
                $this->_CSec = new CLog($this);
                break;
            default:
                include_once 'CMain.php';
                $this->_CSec = new CMain($this);
                break;
        }
    }
}
?>