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
            $row->BID_DISPONIBLE = $row->BID_TOTAL + $data->BID_GANADO - $row->BID_USADO;
            return $row;
        } else return false;
    }
    
    function existeNick($nick) {
        $nick = $this->_bd->limpia($nick);

//        if($attr == null) {
//            $sAttr = "*";
//        } else {
//            $sAttr = implode(",", $attr);
//        }

        $sql = "SELECT COUNT(ID_USUARIO) AS N FROM $this->_dbTable WHERE NICK_USUARIO = '$nick'";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $row = mysql_fetch_object($res);
        return !($row->N == 0);
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
        $usAux = $this->findByFb($data->FB_UID, array("ID_USUARIO", "BID_TOTAL", "BID_USADO", "BID_GANADO", "NICK_USUARIO", "EMA_USUARIO"));
        if($usAux!= null && $usAux->ID_USUARIO>0) { //UPDATE
            $data->LAST_SIGN = $now;
            $data->ID_USUARIO = $usAux->ID_USUARIO;
            unset($data->NICK_USUARIO);
            $this->update($data);
            $data->BID_TOTAL = $usAux->BID_TOTAL;
            $data->BID_USADO = $usAux->BID_USADO;
            $data->BID_DISPONIBLE = $data->BID_TOTAL + $data->BID_GANADO - $data->BID_USADO;
            $data->IS_NEW = 0;
            $data->NICK_USUARIO = $usAux->NICK_USUARIO;
        } else { //INSERT
            $data->FECHA_SIGN = $now;
            $data->LAST_SIGN = $now;
            $data->BID_TOTAL = 0;
            $data->BID_USADO = 0;
            $data->NICK_USUARIO = "";
            $data->ID_USUARIO = $this->insert($data);
            $data->BID_DISPONIBLE = $data->BID_TOTAL + $data->BID_GANADO - $data->BID_USADO;
            $data->IS_NEW = 1;
        }
        return $data;
    }
    
    function restaBid($nBid, $idUs) {
        $nBid = $this->_bd->limpia($nBid);
        $idUs = $this->_bd->limpia($idUs);
        
        $sql = "UPDATE $this->_dbTable SET BID_USADO = BID_USADO + $nBid WHERE ID_USUARIO = $idUs";
        $this->_bd->sql($sql);
        return $this->find($idUs, array("BID_TOTAL", "BID_GANADO", "BID_USADO"))->BID_DISPONIBLE;
    }
    
    function sumaBid($nBid, $idUs) {
        $nBid = $this->_bd->limpia($nBid);
        $idUs = $this->_bd->limpia($idUs);
        
        $sql = "UPDATE $this->_dbTable SET BID_TOTAL = BID_TOTAL + $nBid WHERE ID_USUARIO = $idUs";
        return $this->_bd->sql($sql);
    }
    
    function eliminaBid($nBid, $idUs) {
        $nBid = $this->_bd->limpia($nBid);
        $idUs = $this->_bd->limpia($idUs);
        
        $sql = "UPDATE $this->_dbTable SET BID_TOTAL = BID_TOTAL - $nBid WHERE ID_USUARIO = $idUs";
        return $this->_bd->sql($sql);
    }
    
    function devuelveBid($nBid, $idUs) {
        $nBid = $this->_bd->limpia($nBid);
        $idUs = $this->_bd->limpia($idUs);
        
        $sql = "UPDATE $this->_dbTable SET BID_USADO = BID_USADO - $nBid WHERE ID_USUARIO = $idUs";
        $this->_bd->sql($sql);
        return $this->find($idUs, array("BID_TOTAL", "BID_GANADO", "BID_USADO"))->BID_DISPONIBLE;
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
        
        $sql = "INSERT INTO $this->_dbTable ($vars) VALUES ($vals)";
//        echo $sql."<br>";
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
//        echo $sql."<br>";
        return $this->_bd->sql($sql);
    }

    function desactiva($idUs) {
        $idUs = $this->_bd->limpia($idUs);
        $sql = "UPDATE $this->_dbTable SET ESTADO_USUARIO = 0 WHERE $this->_id = $idUs";
        return $this->_bd->sql($sql);
    }
}
?>