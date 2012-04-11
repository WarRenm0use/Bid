<?php
include_once 'modelo/UsuarioMP.php';
include_once 'modelo/ProductoMP.php';
include_once 'modelo/CategoriaMP.php';

class CProducto {
    protected $cp;
    protected $prMP;
    protected $usMP;
    protected $catMP;
    protected $login;
    protected $layout;

    function __construct($cp) {
        $this->cp = $cp;
        $this->layout = "vista/producto_categoria.phtml";
        $this->usMP = new UsuarioMP();
        $this->prMP = new ProductoMP();
        $this->catMP = new CategoriaMP();
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
            }
        }
    }
    
    function getJSON() {
        if(isset($_GET["get"])) {
            $this->cp->showLayout = false;
            $get = $_GET["get"];
            switch($get) {
                case 'byCatHash':
                    if(isset($_GET["hash"])) {
                        $res = $this->catMP->findByHash($_GET["hash"]);
                        $prod = $this->prMP->fetchByCategoria($res->ID_CATEGORIA);
                        $res->PRODUCTOS = $prod;
                        $res->ERROR = 0;
                    }
                    break;
                case 'det':
                    if(isset($_GET["id"])) {
                        $id = $_GET["id"];
                        $res = $this->prMP->find($id);
                    }
                    break;
            }
            echo json_encode($res);
        }
    }

    function setOp() {
        $op = $_GET["op"];
        switch($op) {
            case 'categoria':
                if(isset($_GET["hash"])) {
                    $res = $this->catMP->findByHash($_GET["hash"]);
                    $prod = $this->prMP->fetchByCategoria($res->ID_CATEGORIA);
                    $res->PRODUCTOS = $prod;
                    $res->ERROR = 0;
                    $this->cat = $res;
                    $this->titulo = $this->cat->NOM_CATEGORIA;
                }
                break;
        }
    }
}
?>
