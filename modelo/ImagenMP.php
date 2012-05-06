<?php
require_once 'Bd.php';

class ImagenMP {
    protected $_dbTable = "IMAGEN";
    protected $_id = "ID_IMAGEN";
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
    
    function fetchByProducto($idElm) {
        $idElm = $this->_bd->limpia($idElm);
        
        $sql = "SELECT * FROM $this->_dbTable WHERE ID_PRODUCTO = $idElm AND ESTADO_IMAGEN = 1 ORDER BY ID_IMAGEN ASC";
        
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $nom = explode(".", $row->URL_IMAGEN);
            $row->URL_MINI = $nom[0]."_mini.".$nom[1];
            $arr[] = $row;
        }
        return $arr;
    }
    
    function find($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            for($i=0; $i<count($attr); $i++) {
                if($i==0) {
                    $sAttr = $attr[$i];
                } else {
                    $sAttr .= ", ".$attr[$i];
                }
            }
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE $this->_id = $id";
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
                    $vals .= ", '".$data->$k."'";
                } else {
                    $vars = $k;
                    $vals = "'".$data->$k."'";
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
                    $val .= ", ".$k." = '".$data->$k."'";
                } else {
                    $val = $k." = '".$data->$k."'";
                }
            }
            $i++;
        }
        
        $sql = "UPDATE $this->_dbTable SET $val WHERE $this->_id = $data->ID_ELEMENTO";
//        return $sql;
        return $this->_bd->sql($sql);
    }
}
?>
