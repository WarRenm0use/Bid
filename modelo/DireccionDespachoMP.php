<?php
require_once 'Bd.php';

class DireccionDespachoMP {
    protected $_dbTable = "DIRECCION_DESPACHO";
    protected $_id = "ID_DIRECCION";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function fetchAll() {
        $sql = "SELECT * FROM $this->_dbTable WHERE ESTADO_DIRECCION = 1";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }
    
    function fetchByUsuario($id) {
        $id = $this->_bd->limpia($id);
        $sql = "SELECT * FROM $this->_dbTable WHERE ID_USUARIO = $id AND ESTADO_DIRECCION = 1";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        if(count($arr)>0) return $arr;
        else return false;
    }

    function find($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }
        
        $sql = "SELECT $sAttr FROM $this->_dbTable AS DD INNER JOIN COMUNA AS C ON DD.$this->_id = $id AND DD.ID_COMUNA = C.ID_COMUNA";
        
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
//        echo $sql;
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
                    $val .= ", ".$k." = '".mysql_real_escape_string(stripslashes($data->$k))."'";
                } else {
                    $val = $k." = '".mysql_real_escape_string(stripslashes($data->$k))."'";
                }
            }
            $i++;
        }
        
        $sql = "UPDATE $this->_dbTable SET 
                $val
                WHERE $this->_id = '$data->ID_DIRECCION'";
        
        return $this->_bd->sql($sql);
    }
    
}
?>