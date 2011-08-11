<?php
require_once 'Bd.php';

class UsuarioMP {
    protected $_dbTable = "USUARIO";
    protected $_id = "ID_USUARIO";
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
            $row->BID_RESTO = $row->BID_TOTAL - $row->BID_USADO;
            return $row;
        } else return false;
    }
    
    function findByFb($id, $attr = null) {
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

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE FB_UID = '$id'";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        if($res) {
            return mysql_fetch_object($res);
        } else return false;
    }

    function save($data) {
        $now = date("Y-m-d H:i:s");
        $usAux = $this->findByFb($data->FB_UID, array("ID_USUARIO"));
        if($usAux!= null && $usAux->ID_USUARIO>0) { //UPDATE
            $data->LAST_SIGN = $now;
            $data->ID_USUARIO = $usAux->ID_USUARIO;
            $this->update($data);
        } else { //INSERT
            $data->FECHA_SIGN = $now;
            $data->LAST_SIGN = $now;
            $data->ID_USUARIO = $this->insert($data);
        }
        
        return $data;
    }
    
    function insert($data) {
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
        
        $sql = "UPDATE $this->_dbTable SET $val WHERE $this->_id = $data->ID_USUARIO";
        
        return $this->_bd->sql($sql);
    }

    function desactiva($idUs) {
        $idUs = $this->_bd->limpia($idUs);
        $sql = "UPDATE $this->_dbTable SET ESTADO_USUARIO = 0 WHERE $this->_id = $idUs";
        return $this->_bd->sql($sql);
    }
}
?>
