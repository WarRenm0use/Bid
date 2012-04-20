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
        if($this->cp->isLoged) {
            $this->getJSON();
            $this->setDo();
            $this->setOp();
        } else {
            $this->cp->getSession()->salto("/");
        }
    }

    function getLayout() {
        return $this->layout;
    }
    
    function getLayoutOp() {
        return $this->layoutOp;
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
        $op = (isset($_GET["op"]))?$_GET["op"]:"subastas";
        $this->op = $op;
        switch($op) {
            case "subastas":
                $this->titulo = "Mi Cuenta :: Subastas";
                $this->layoutOp = "vista/cuenta_subastas.phtml";
                include_once 'modelo/SubastaVipMP.php';
                include_once 'modelo/CarroMP.php';
                $suMP = new SubastaVipMP();
                $caMP = new CarroMP();
                $this->res = $suMP->fetchByUsuario($this->cp->getSession()->get("ID_USUARIO"));
//                $this->res = $suMP->fetchByUsuario(1);
                $nSub = count($this->res);
                for($i=0; $i<$nSub; $i++) {
                    $this->res[$i]->IN_CARRO = $caMP->existeSubasta($this->res[$i]);
                }
                break;
            case "misdatos":
                $this->titulo = "Mi Cuenta :: Mis datos";
                $this->layoutOp = "vista/cuenta_datos.phtml";
                break;
        }
    }

}
?>
