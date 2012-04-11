<?php
require_once 'Bd.php';

/**
 * ESTADO_CARRO:
 *  0: ABIERTO
 *  1: CERRADO
 *  2: PAGADO
 */

class CarroMP {
    protected $_dbTable = "CARRO";
    protected $_id = "ID_CARRO";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function fetchAll() {
        $sql = "SELECT * FROM $this->_dbTable";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function find($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE $this->_id = $id";
        $res = $this->_bd->sql($sql);
        
        if($res) {
            $row = mysql_fetch_object($res);
            $row->MONTO_CARRO_H = number_format($row->MONTO_CARRO, 0, ",", ".");
            return $row;
        } else return false;
    }
    
    function lastByUser($id, $attr = null) {
        $id = $this->_bd->limpia($id);
        
        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }
        
        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE ESTADO_CARRO IN (0,1) AND ID_USUARIO = $id ORDER BY FECHA_INICIO DESC LIMIT 0,1";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = mysql_fetch_object($res);
            $row->SQL = $sql;
            return $row;
        } else return false;
    }
    
    function cuentaProductos($id) {
        $id = $this->_bd->limpia($id);
        $sql = "SELECT COUNT(ID_CARRO_PROD) AS TOTAL FROM CARRO_PRODUCTO WHERE ID_CARRO = $id AND ESTADO_CARRO_PROD = 1";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res)->TOTAL;
    }
    
    function updateProducto($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        $this->_bd->limpia($data->ID_CARRO_PROD);
        
        $i = 0;
        $val = "";
        foreach($keys as $k) {
            if($k != "ID_CARRO_PROD") {
                if($val != "") {
                    $val .= ", ".$k." = '".$this->_bd->limpia($data->$k)."'";
                } else {
                    $val = $k." = '".$this->_bd->limpia($data->$k)."'";
                }
            }
            $i++;
        }
        
        $sql = "UPDATE CARRO_PRODUCTO SET 
                $val
                WHERE ID_CARRO_PROD = $data->ID_CARRO_PROD";
        
        return $this->_bd->sql($sql);
    }
    
    function fetchProductos($idCarro) {
        $idCarro = $this->_bd->limpia($idCarro);
        $sql = "SELECT CP.*, P.NOM_PRODUCTO, P.VALOR_BID, I.URL_IMAGEN, P.ESTADO_PRODUCTO FROM CARRO_PRODUCTO AS CP INNER JOIN PRODUCTO AS P INNER JOIN IMAGEN AS I ON CP.ID_CARRO = $idCarro AND CP.ESTADO_CARRO_PROD = 1 AND CP.ID_PRODUCTO = P.ID_PRODUCTO AND P.ID_MAIN_IMAGEN = I.ID_IMAGEN";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $row->PRECIO_CARRO_PROD_H = number_format($row->PRECIO_CARRO_PROD, 0, ",", ".");
            $row->PRECIO_TOTAL_CARRO_PROD = $row->PRECIO_CARRO_PROD * $row->CANTIDAD_CARRO_PROD;
            $row->PRECIO_TOTAL_CARRO_PROD_H = number_format($row->PRECIO_TOTAL_CARRO_PROD, 0, ",", ".");
            $arr[] = $row;
        }
        return $arr;
    }
    
    function addProducto($idCarro, $producto) {
        $idCarro = $this->_bd->limpia($idCarro);
        $now = date("U");
        $sql = "INSERT INTO CARRO_PRODUCTO 
                (ID_PRODUCTO, ID_CARRO, FECHA_CARRO_PROD, PRECIO_CARRO_PROD, CANTIDAD_CARRO_PROD, ESTADO_CARRO_PROD) VALUES 
                ($producto->ID_PRODUCTO, $idCarro, $now, $producto->PRECIO_VENTA, $producto->CANTIDAD, 1)";
        
        if($this->_bd->sql($sql)) {
            $sql = "UPDATE $this->_dbTable SET MONTO_CARRO = MONTO_CARRO + ".($producto->PRECIO_VENTA*$producto->CANTIDAD)." WHERE ID_CARRO = $idCarro";
            return $this->_bd->sql($sql);
        } else return false;
    }
    
    function updateCarro($idCarro) {
        $idCarro = $this->_bd->limpia($idCarro);
        $sql = "SELECT P.ID_PRODUCTO, P.PRECIO_VENTA FROM CARRO_PRODUCTO AS CP INNER JOIN PRODUCTO AS P ON CP.ID_CARRO = $idCarro AND CP.ESTADO_CARRO_PROD = 1 AND CP.ID_PRODUCTO = P.ID_PRODUCTO AND P.ESTADO_PRODUCTO = 1 GROUP BY P.ID_PRODUCTO";
        $res = $this->_bd->sql($sql);
        while($row = mysql_fetch_object($res)) {
            $sql = "UPDATE CARRO_PRODUCTO SET PRECIO_CARRO_PROD = $row->PRECIO_VENTA WHERE ID_CARRO = $idCarro AND ID_PRODUCTO = $row->ID_PRODUCTO";
            $this->_bd->sql($sql);
        }
        $sql = "SELECT SUM(PRECIO_CARRO_PROD * CANTIDAD_CARRO_PROD) AS TOTAL FROM CARRO_PRODUCTO WHERE ID_CARRO = $idCarro AND ESTADO_CARRO_PROD = 1";
        $res = $this->_bd->sql($sql);
        $row = mysql_fetch_object($res);
        if($row->TOTAL) {
            $sql = "UPDATE $this->_dbTable SET MONTO_CARRO = $row->TOTAL WHERE ID_CARRO = $idCarro";
        } else {
            $sql = "UPDATE $this->_dbTable SET MONTO_CARRO = 0 WHERE ID_CARRO = $idCarro";
        }
        $res = $this->_bd->sql($sql);
        
        if($res) return true;
        else return false;
    }
    
    function save($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        foreach($keys as $k) {
            if($k!=$this->_id) {
                if($i) {
                    $vars .= ", ".$k;
                    $vals .= ", '".$this->_bd->limpia($data->$k)."'";
                } else {
                    $vars = $k;
                    $vals = "'".$this->_bd->limpia($data->$k)."'";
                }
            }
            $i++;
        }
        
        $sql = "INSERT INTO $this->_dbTable ($vars) VALUES
                ($vals)";
        
        $this->_bd->sql($sql);
        return mysql_insert_id();
    }

    function update($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        $val = "";
        foreach($keys as $k) {
            if($k != $this->_id) {
                if($val != "") {
                    $val .= ", ".$k." = '".$this->_bd->limpia($data->$k)."'";
                } else {
                    $val = $k." = '".$this->_bd->limpia($data->$k)."'";
                }
            }
            $i++;
        }
        
        $sql = "UPDATE $this->_dbTable SET 
                $val
                WHERE $this->_id = $data->ID_CARRO";
        
        return $this->_bd->sql($sql);
    }
}
?>