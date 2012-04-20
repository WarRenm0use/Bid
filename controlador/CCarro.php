<?php
include_once 'modelo/UsuarioMP.php';
include_once 'modelo/ProductoMP.php';
include_once 'modelo/CarroMP.php';
include_once 'modelo/SubastaVipMP.php';
include_once 'modelo/ComunaMP.php';
include_once 'modelo/DireccionDespachoMP.php';

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
        $this->coMP = new ComunaMP();
        $this->diMP = new DireccionDespachoMP();
        $this->getJSON();
        $this->setDo();
        $this->setOp();
    }

    function getLayout() {
        return $this->layout;
    }
    
    function iniCarro() {
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
        return $carro;
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
                                    $prod->TIPO_CARRO_PROD = 1;
                                    $prod->ID_SUBASTA = null;
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
                case 'addSub':
                    if($this->cp->getSession()->existe("ID_USUARIO")) {
                        $this->suMP = new SubastaVipMP();
                        if(isset($_POST["id_svip"])) {
                            $sub = $this->suMP->find($_POST["id_svip"], array("ID_SVIP", "ID_USUARIO", "ID_PRODUCTO", "MONTO_SUBASTA", "ESTADO_SUBASTA"));
                            if(isset($sub->ID_USUARIO) && $sub->ID_USUARIO == $this->cp->getSession()->get("ID_USUARIO")) {
//                                $prod = $this->prMP->find($sub->ID_PRODUCTO, array("ID_PRODUCTO", "PRECIO_VENTA"));
                                $prod = new stdClass();
                                $prod->ID_PRODUCTO = $sub->ID_PRODUCTO;
                                $prod->PRECIO_VENTA = $sub->MONTO_SUBASTA;
                                $prod->CANTIDAD = 1;
                                $carro = $this->iniCarro();
                                if(!$this->caMP->existeSubasta($sub)) {
                                    if($carro->ID_CARRO > 0) {
                                        $res = $this->caMP->find($carro->ID_CARRO);
                                        if($res->ESTADO_CARRO == 0) {
                                            $prod->TIPO_CARRO_PROD = 2;
                                            $prod->ID_SUBASTA = $_POST["id_svip"];
                                            $r1 = $this->caMP->addProducto($carro->ID_CARRO, $prod);
                                            $r2 = $this->caMP->updateCarro($carro->ID_CARRO);
                                            if($r1 && $r2) {
                                                $res = $this->caMP->find($carro->ID_CARRO);
                                                $res->PRODUCTOS = $this->caMP->fetchProductos($carro->ID_CARRO);
                                                $res->ERROR = 0;
                                                $res->MENSAJE = "El producto fue agregado correctamente al carro";
                                            } else {
                                                $res->ERROR = 1;
                                                $res->MENSAJE = "El producto no pudo ser agregado, intentalo nuevamente";
                                            }
                                        } else {
                                            $res->ERROR = 1;
                                            $res->MENSAJE = "No puedes agregar productos a este carro";
                                        }
                                    } else {
                                        $res->ERROR = 1;
                                        $res->MENSAJE = "El producto no pudo ser agregado, intentalo nuevamente";
                                    }
                                } else {
                                    $res->ERROR = 1;
                                    $res->MENSAJE = "Este producto ya fue agregado a un carro";
                                }
                            } else {
                                $res->ERROR = 1;
                                $res->MENSAJE = "No puedes agregar esta subasta";
                            }
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "Debes seleccionar una subasta";
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
                case 'setDes':
                    if($this->cp->isLoged) {
                        $res = new stdClass();
                        $dir = new stdClass();
                        $dir->ID_COMUNA = $_POST["com"];
                        $dir->DIRECCION = $_POST["dir"];
                        $dir->ID_USUARIO = $this->cp->getSession()->get("ID_USUARIO");
                        $dir->TEL_RECEPTOR = $_POST["tel"];
                        $dir->EMA_RECEPTOR = $_POST["ema"];
                        $dir->NOM_RECEPTOR = $_POST["nom"];
                        $dir->ESTADO_DIRECCION = 1;
                        $dir->ID_DIRECCION = (isset($_POST["id_dir"]))?$_POST["id_dir"]:0;
                        if($dir->ID_DIRECCION == 0) {
                            $idDir = $this->diMP->save($dir);
                        } else {
                            $idDir = $this->diMP->update($dir);
                        }
                        
                        $dir->ID_DIRECCION = ($dir->ID_DIRECCION == 0)?$idDir:$dir->ID_DIRECCION;
                        if($dir->ID_DIRECCION) {
                            $com = $this->coMP->find($dir->ID_COMUNA);
                            $car = $this->caMP->find($this->cp->getSession()->get("ID_CARRO"));
                            
                            $prod = $this->caMP->fetchProductos($this->cp->getSession()->get("ID_CARRO"));
    //                        print_r($com);
    //                        print_r($dir);
    //                        print_r($prod);

                            $nProd = count($prod);
                            $maxDes = 0;
                            if($com->ID_REGION == 13) { //RM
                                for($i=0; $i<$nProd; $i++) {
                                    $maxDes = ($prod[$i]->TIENE_DESPACHO == 1 && $prod[$i]->COSTO_STGO > $maxDes)?$prod[$i]->COSTO_STGO:$maxDes;
                                }
                            } else { //REGIONES
                                for($i=0; $i<$nProd; $i++) {
                                    $maxDes = ($prod[$i]->TIENE_DESPACHO == 1 && $prod[$i]->COSTO_REGIONES > $maxDes)?$prod[$i]->COSTO_REGIONES:$maxDes;
                                }
                            }
                            $ca = new stdClass();
                            $ca->ID_CARRO = $car->ID_CARRO;
                            $ca->ID_COMUNA = $dir->ID_COMUNA;
                            $ca->MONTO_DESPACHO = $maxDes;
                            $ca->ID_DIRECCION = $dir->ID_DIRECCION;
                            $ca->TEL_DESPACHO = $dir->TEL_RECEPTOR;
                            $ca->NOM_DESPACHO = $dir->NOM_RECEPTOR;
                            $ca->EMA_DESPACHO = $dir->EMA_RECEPTOR;
                            $ca->DIR_DESPACHO = $dir->DIRECCION;
                            $updCar = $this->caMP->update($ca);
                            if($updCar) {
                                $res->ERROR = 0;
                                $res->MENSAJE = "";
                            } else {
                                $res->ERROR = 1;
                                $res->MENSAJE = "La dirección no pudo ser guardada, por favor intentalo nuevamente";
                            }
                        } else {
                            $res->ERROR = 1;
                            $res->MENSAJE = "La dirección no pudo ser guardada, por favor intentalo nuevamente";
                        }
                        echo json_encode($res);
                    }
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
                case 'direccion':
                    if($this->cp->isLoged) {
                        $res = $this->diMP->find($_POST["id_dir"]);
                        $res->LOGIN = 1;
                    } else $res->LOGIN = 0;
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
                    if($carro->ESTADO_CARRO == 0) {
                        $this->com = $this->coMP->fetchAll();
                        $this->dir = $this->diMP->fetchByUsuario($this->cp->getSession()->get("ID_USUARIO"));
                        if($this->dir) {
                            $nueva = new stdClass();
                            $nueva->ID_DIRECCION = 0;
                            $nueva->DIRECCION = "Nueva direcci&oacute;n";
                            $this->dir[] = $nueva;
                        }
                    } else {
                        $this->dirDes = $this->diMP->find($carro->ID_DIRECCION);
                    }
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
