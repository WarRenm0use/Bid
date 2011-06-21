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
        $now = time();
        $usAux = $this->findByFb($data["id"], array("ID_USUARIO"));
        if($usAux!= null && $usAux->ID_USUARIO) {
            $sql = "UPDATE $this->_dbTable SET 
                        NOM_USUARIO = '".$data["first_name"]."'
                        APE_USUARIO = '".$data["last_name"]."'
                        EMA_USUARIO = '".$data["email"]."'
                        NICK_USUARIO = '".$data["username"]."' 
                    WHERE ID_USUARIO = $usAux->ID_USUARIO";
        } else {
            $sql = "INSERT INTO $this->_dbTable (NOM_USUARIO, APE_USUARIO, EMA_USUARIO, NICK_USUARIO, FB_UID) VALUES 
                    ('".$data["first_name"]."', '".$data["last_name"]."', '".$data["email"]."', '".$data["username"]."', ".$data["id"].")";
        }
        $this->_bd->sql($sql);
    }

    function update($obj) {
        $variables = get_object_vars($obj);
        $keys = array_keys($variables);
        
        $i = 0;
        $data = "";
        foreach($keys as $k) {
            if($k != $this->_id) {
                if($data != "") {
                    $data .= ", ".$k." = '".$obj->$k."'";
                } else {
                    $data = $k." = '".$obj->$k."'";
                }
            }
            $i++;
        }
        $sql = "UPDATE $this->_dbTable SET 
                $data
                WHERE $this->_id = '$obj->ID_USUARIO'";
        return $this->_bd->sql($sql);
    }

    function desactiva($idUs) {
        $idUs = $this->_bd->limpia($idUs);
        $sql = "UPDATE $this->_dbTable SET ESTADO_USUARIO = 0 WHERE $this->_id = $idUs";
        return $this->_bd->sql($sql);
    }
}
?>
