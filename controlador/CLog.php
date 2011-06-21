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
        $this->cp->getSession()->salto("index.php");
    }

    function getLayout() {
        return $this->layout;
    }

    function checkLogin() {
        $this->login = $this->usuMP->validaCuenta($_POST["emp"], $_POST["user"], $_POST["pass"]);
        if($this->login != null) {
            $this->cp->getSession()->set("account", $this->login->accountName);
            $this->cp->getSession()->set("accountID", $this->login->accountID);
            $this->cp->getSession()->set("user", $this->login->contactName);
            $this->cp->getSession()->set("userName", $this->login->userName);
            $this->cp->getSession()->set("userID", $this->login->userID);
            $this->cp->getSession()->set("roleID", $this->login->roleID);
            $this->cp->getSession()->salto("?sec=monitoreo");
        } else {
            $this->cp->getSession()->salto("?&e=1");
        }
    }

    function setDo() {
        if(isset($_GET["do"])) {
            $this->cp->showLayout = false;
            $do = $_GET["do"];
            switch($do) {
                case 'in':
                    $this->checkLogin();
                    break;
                case 'out':
                    $this->logout();
                    break;
                case 'reg':
                    $this->usuMP->save($this->cp->user_profile);
                    $this->cp->getSession()->salto("/");
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
            default:
                break;
        }
    }
}
?>
