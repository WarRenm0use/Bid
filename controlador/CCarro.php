<?php
include_once 'modelo/UsuarioMP.php';
include_once 'modelo/ProductoMP.php';
include_once 'modelo/CarroMP.php';

class CCarro {
    protected $cp;
    protected $prMP;
    protected $usMP;
    protected $caMP;
    protected $login;
    protected $layout;

    function __construct($cp) {
        $this->cp = $cp;
        $this->layout = "vista/carro.phtml";
        $this->usMP = new UsuarioMP();
        $this->prMP = new ProductoMP();
        $this->caMP = new CarroMP();
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
                case 'delete':
                    $res = new stdClass();
                    if($this->cp->getSession()->existe("ID_USUARIO")) {
                        if($this->cp->getSession()->existe("ID_CARRO") && $this->cp->getSession()->get("ID_CARRO")*1 != 0) {
                            if(isset($_POST["ID_CARRO_PROD"])) {
                                $carro = $this->caMP->find($this->cp->getSession()->get("ID_CARRO"));
                                if($carro->ESTADO_CARRO == 0) {
                                    $prod = new stdClass();
                                    $prod->ID_CARRO_PROD = $_POST["ID_CARRO_PROD"];
                                    $prod->ESTADO_CARRO_PROD = 0;
                                    if($this->caMP->updateProducto($prod)) {
                                        $this->caMP->updateCarro($this->cp->getSession()->get("ID_CARRO"));
                                        $res = $this->caMP->find($carro->ID_CARRO);
                                        $res->N_PRODUCTOS = $this->caMP->cuentaProductos($carro->ID_CARRO);
                                        $res->ERROR = 0;
                                        $res->MENSAJE = "El producto fue eliminado correctamente";
                                    } else {
                                        $res->ERROR = 1;
                                        $res->MENSAJE = "El producto NO pudo ser eliminado, intentalo nuevamente";
                                    }
                                } else {
                                    $res->CARRO = $carro;
                                    $res->ERROR = 1;
                                    $res->MENSAJE = "Ya no puedes modificar este carro";
                                }
                            } else {
                                $res->ERROR = 1;
                                $res->MENSAJE = "Debes seleccionar el producto a eliminar";
                            }
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "El carro no existe";
                        }
                    } else {
                        $res->ERROR = 1;
                        $res->MENSAJE = "Debes iniciar sesi&oacute;n";
                    }
                    echo json_encode($res);
                    break;
                case 'add':
                    if($this->cp->getSession()->existe("ID_USUARIO")) {
                        if(isset($_POST["id_producto"]) && isset($_POST["cantidad"])) {
                            $prod = $this->prMP->find($_POST["id_producto"], array("ID_PRODUCTO", "PRECIO_VENTA"));
                            $prod->CANTIDAD = $_POST["cantidad"];
                            $carro = new stdClass();
                            if(!$this->cp->getSession()->existe("ID_CARRO") || $this->cp->getSession()->get("ID_CARRO")*1 == 0) {
                                $carro->ID_USUARIO = $this->cp->getSession()->get("ID_USUARIO");
                                $carro->MONTO_CARRO = 0;
                                $carro->FECHA_INICIO = date("U");
                                $carro->ESTADO_CARRO = 0;
                                $carro->ID_CARRO = $this->caMP->save($carro);
                                if($carro->ID_CARRO > 0) $this->cp->getSession()->set("ID_CARRO", $carro->ID_CARRO);
                            } else {
                                $carro->ID_CARRO = $this->cp->getSession()->get("ID_CARRO");
                            }
                            
                            if($carro->ID_CARRO > 0) {
                                $res = $this->caMP->find($carro->ID_CARRO);
                                if($res->ESTADO_CARRO == 0) {
                                    $r1 = $this->caMP->addProducto($carro->ID_CARRO, $prod);
                                    $r2 = $this->caMP->updateCarro($carro->ID_CARRO);
                                    if($r1 && $r2) {
                                        $res = $this->caMP->find($carro->ID_CARRO);
                                        $res->PRODUCTOS = $this->caMP->fetchProductos($carro->ID_CARRO);
                                        $res->ERROR = 0;
                                        $res->MENSAJE = "Los productos fueron agregados correctamente al carro";
                                    } else {
                                        $res->ERROR = 1;
                                        $res->MENSAJE = "Los productos no pudieron ser agregados, intentalo nuevamente";
                                    }
                                } else {
                                    $res->ERROR = 1;
                                    $res->MENSAJE = "No puedes agregar productos a este carro";
                                }
                            } else {
                                $res->ERROR = 1;
                                $res->MENSAJE = "Los productos no pudieron ser agregados, intentalo nuevamente";
                            }
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "Debes seleccionar productos";
                        }
                    } else {
                        $res->ERROR = 1;
                        $res->MENSAJE = "Debes iniciar sesi&oacute;n";
                    }
                    echo json_encode($res);
                    break;
                case 'addProd':
                    if($this->cp->getSession()->existe("ID_USUARIO")) {
                        $keys = array_keys($_POST);
                        $nVars = count($keys);
                        $prod = array();
                        for($i = 0; $i<$nVars; $i++) {
                            $var = explode("_",$keys[$i]);
                            if($var[0] == "cant") {
                                $p = $this->prMP->find($var[1], array("ID_PRODUCTO", "PRECIO_VENTA"));
                                $p->CANTIDAD = $_POST[$keys[$i]];
                                if($p->CANTIDAD > 0) $prod[] = $p;
                            }
                        }
//                        echo json_encode($prod);
                        $nProd = count($prod);
                        if($nProd > 0) {
                            $carro = new stdClass();
                            if(!$this->cp->getSession()->existe("ID_CARRO") || $this->cp->getSession()->get("ID_CARRO")*1 == 0) {
                                $carro->ID_USUARIO = $this->cp->getSession()->get("ID_USUARIO");
                                $carro->MONTO_CARRO = 0;
                                $carro->FECHA_INICIO = date("U");
                                $carro->ESTADO_CARRO = 0;
                                $carro->ID_CARRO = $this->caMP->save($carro);
                                if($carro->ID_CARRO > 0)
                                    $this->cp->getSession()->set("ID_CARRO", $carro->ID_CARRO);
                            } else {
                                $carro->ID_CARRO = $this->cp->getSession()->get("ID_CARRO");
                            }
                            
                            if($carro->ID_CARRO > 0) {
                                $res = $this->caMP->find($carro->ID_CARRO);
                                if($res->ESTADO_CARRO == 0) {
                                    for($i=0; $i<$nProd; $i++) {
                                        $this->caMP->addProducto($carro->ID_CARRO, $prod[$i]);
                                    }
                                    $this->caMP->updateCarro($carro->ID_CARRO);
                                    $res = $this->caMP->find($carro->ID_CARRO);
                                    $res->PRODUCTOS = $this->caMP->fetchProductos($carro->ID_CARRO);
                                    $res->ERROR = 0;
                                    $res->MENSAJE = "Los productos fueron agregados correctamente al carro";
                                } else {
                                    $res->ERROR = 1;
                                    $res->MENSAJE = "No puedes agregar productos a este carro";
                                }
                            } else {
                                $res->ERROR = 1;
                                $res->MENSAJE = "Los productos no pudieron ser agregados, intentalo nuevamente";
                            }
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "Debes seleccionar productos";
                        }
                    } else {
                        $res->ERROR = 1;
                        $res->MENSAJE = "Debes iniciar sesi&oacute;n";
                    }
                    echo json_encode($res);
                    break;
                case 'toggle':
                    $ca = new stdClass();
                    $ca->ID_CARRO = $_POST["ID_CARRO"];
                    $ca->ESTADO_CARRO = $_POST["ESTADO_CARRO"];
                    $this->caMP->update($ca);
                    $this->caMP->updateCarro($_POST["ID_CARRO"]);
                    $carro = $this->caMP->find($_POST["ID_CARRO"]);
                    $prod = $this->caMP->fetchProductos($_POST["ID_CARRO"]);
                    $carro->N_PRODUCTOS = count($prod);
                    $res->MODELO = $carro;
                    $res->PRODUCTOS = $prod;
                    echo json_encode($res);
                    break;
                case 'bloqdes':
                    $res = new stdClass();
                    if($this->cp->getSession()->existe("ID_CARRO")) {
                        $idCarro = $this->cp->getSession()->get("ID_CARRO");
                        $carro = $this->caMP->find($idCarro);
                        if($carro) {
                            $ca->ID_CARRO = $carro->ID_CARRO;
                            if($carro->ESTADO_CARRO == 0 || $carro->ESTADO_CARRO == 1) {
                                $ca->ESTADO_CARRO = ($carro->ESTADO_CARRO == 0)?1:0;
                                $res->ERROR = 0;
                                $this->caMP->update($ca);
                                $this->caMP->updateCarro($carro->ID_CARRO);
                            } else {
                                $res->ERROR = 1;
                                $res->MENSAJE = "Debes terminar el proceso de pago para desbloquear el carro";
                            }
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "El carro es invalido";
                        }
                    } else {
                        $res->ERROR = 1;
                        $res->MENSAJE = "Debes agregar productos al carro";
                    }
                    echo json_encode($res);
                    break;
            }
            die();
        }
    }
    
    function getJSON() {
        if(isset($_GET["get"])) {
            $this->cp->showLayout = false;
            $get = $_GET["get"];
            switch($get) {
                case 'all':
                    if($this->cp->getSession()->existe("ID_USUARIO")) {
                        $this->caMP->updateCarro($this->cp->getSession()->get("ID_CARRO"));
                        $carro = $this->caMP->find($this->cp->getSession()->get("ID_CARRO"));
                        $prod = $this->caMP->fetchProductos($this->cp->getSession()->get("ID_CARRO"));
                        $carro->N_PRODUCTOS = count($prod);
                        $res->MODELO = $carro;
                        $res->PRODUCTOS = $prod;
                        $res->LOGIN = 1;
                    } else {
                        $res->LOGIN = 0;
                    }
                    break;
                case 'pago':
                    if($this->cp->getSession()->existe("ID_USUARIO")) {
                        $ca = new stdClass();
                        $ca->ID_CARRO = $this->cp->getSession()->get("ID_CARRO");
                        $ca->ESTADO_CARRO = 1;
                        $this->caMP->update($ca);
                        $this->caMP->updateCarro($this->cp->getSession()->get("ID_CARRO"));
                        $carro = $this->caMP->find($this->cp->getSession()->get("ID_CARRO"));
                        $prod = $this->caMP->fetchProductos($this->cp->getSession()->get("ID_CARRO"));
                        $carro->N_PRODUCTOS = count($prod);
                        $res->MODELO = $carro;
                        $res->PRODUCTOS = $prod;
                        $res->LOGIN = 1;
                    } else {
                        $res->LOGIN = 0;
                    }
                    break;
            }
            echo json_encode($res);
            die();
        }
    }

    function setOp() {
        $op = $_GET["op"];
        switch($op) {
            default:
                if($this->cp->getSession()->existe("ID_USUARIO")) {
                    $this->caMP->updateCarro($this->cp->getSession()->get("ID_CARRO"));
                    $carro = $this->caMP->find($this->cp->getSession()->get("ID_CARRO"));
                    $prod = $this->caMP->fetchProductos($this->cp->getSession()->get("ID_CARRO"));
                    $carro->N_PRODUCTOS = count($prod);
                    $res = $carro;
                    $res->PRODUCTOS = $prod;
                    $res->LOGIN = 1;
                } else {
                    $this->cp->getSession()->salto("/");
                }
                $this->carro = $res;
                $this->titulo = "Carro de compra";
                break;
        }
    }
}
?>
