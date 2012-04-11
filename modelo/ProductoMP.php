<?php
require_once 'Bd.php';

class ProductoMP {
    protected $_dbTable = "PRODUCTO";
    protected $_id = "ID_PRODUCTO";
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

    function find($id, $attr = null, $ima = false) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        if($ima) {
            $sql = "SELECT P.*, I.URL_IMAGEN FROM $this->_dbTable AS P INNER JOIN IMAGEN AS I ON P.ID_PRODUCTO = $id AND P.ID_MAIN_IMAGEN = I.ID_IMAGEN";
        } else {
            $sql = "SELECT $sAttr FROM $this->_dbTable WHERE $this->_id = $id";
        }
        $res = $this->_bd->sql($sql);
        if($res) {
            return mysql_fetch_object($res);
        } else return false;
    }
    
    function save($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        foreach($keys as $k) {
            if($k!=$this->_id) {
                if($i) {
                    $vars .= ", ".$k;
                    $vals .= ", '".mysql_real_escape_string($data->$k)."'";
                } else {
                    $vars = $k;
                    $vals = "'".mysql_real_escape_string($data->$k)."'";
                }
            }
            $i++;
        }
        
        $sql = "INSERT INTO $this->_dbTable ($vars) VALUES
                ($vals)";
        
        $this->_bd->sql($sql);
        $r = new stdClass();
        $r->sql = $sql;
        $r->id = mysql_insert_id();
        return $r;
    }

    function update($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        $val = "";
        foreach($keys as $k) {
            if($k != $this->_id) {
                if($val != "") {
                    $val .= ", ".$k." = '".mysql_real_escape_string(stripslashes($data->$k))."'";
                } else {
                    $val = $k." = '".mysql_real_escape_string(stripslashes($data->$k))."'";
                }
            }
            $i++;
        }
        
        $sql = "UPDATE $this->_dbTable SET 
                $val
                WHERE $this->_id = '$data->ID_PRODUCTO'";
        
        return $this->_bd->sql($sql);
    }
    
    function fetchByCategoria($idCat, $attr = null) {
        $idCat = $this->_bd->limpia($idCat);
        
        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }
        
        $sql = "SELECT P.$sAttr, I.URL_IMAGEN FROM $this->_dbTable AS P INNER JOIN CATEGORIA_PRODUCTO AS CP INNER JOIN IMAGEN AS I
                ON CP.ID_CATEGORIA = $idCat
                AND CP.ID_PRODUCTO = P.ID_PRODUCTO
                AND P.ID_MAIN_IMAGEN = I.ID_IMAGEN
                AND P.ESTADO_PRODUCTO = 1";
        
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $row->PRECIO_VENTA_H = number_format($row->PRECIO_VENTA, 0, ",", ".");
            $arr[] = $row;
        }
        return $arr;
    }
    
    function fetchByCategoriaHash($hashCat, $attr = null) {
        $hashCat = $this->_bd->limpia($hashCat);
        
        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }
        
        $sql = "SELECT P.$sAttr FROM $this->_dbTable AS P INNER JOIN CATEGORIA_PRODUCTO AS CP INNER JOIN CATEGORIA AS C
                ON C.HASH_CATEGORIA = '$hashCat'
                AND C.ID_CATEGORIA = CP.ID_CATEGORIA
                AND CP.ID_PRODUCTO = P.ID_PRODUCTO
                AND P.ESTADO_PRODUCTO = 1";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }
}
?>