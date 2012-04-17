<?php
//include_once 'modelo/PaginaMP.php';

class CCuenta {
    protected $cp;
    protected $login;
    protected $layout;

    function __construct($cp) {
        $this->cp = $cp;
        $this->layout = "vista/cuenta.phtml";
//        $this->paMP = new PaginaMP();
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
                case 'contacto':
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
    
    function setOp() {
        $op = $_GET["op"];
        switch($op) {
            default:
                
                break;
        }
    }

}
?>
