<?php
require_once 'Bd.php';

class BidMP {
    protected $_dbTable = "BID";
    protected $_id = "ID_BID";
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
    
    function fetchLast($id, $tipo=2,$n=10) {
        $id = $this->_bd->limpia($id);
        if($tipo==1) {
            $sql = "SELECT * FROM $this->_dbTable WHERE ID_SUBASTA = $id ORDER BY HORA_BID DESC LIMIT 0,$n";
        } else if($tipo==2) {
            $sql = "SELECT S.HORA_BID, from_unixtime(S.HORA_BID, '%T') AS HORA_BID_H, U.NICK_USUARIO FROM $this->_dbTable AS S INNER JOIN USUARIO AS U ON S.ID_SVIP = $id AND S.ID_USUARIO = U.ID_USUARIO ORDER BY HORA_BID DESC LIMIT 0,$n";
        }
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $sec = explode(".",$row->HORA_BID);
            $row->HORA_BID_H = $row->HORA_BID_H.".".$sec[1];
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
    }

    function update($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        $data = "";
        foreach($keys as $k) {
            if($k != $this->_id) {
                if($data != "") {
                    $data .= ", ".$k." = '".$data->$k."'";
                } else {
                    $data = $k." = '".$data->$k."'";
                }
            }
            $i++;
        }
        
        $sql = "UPDATE $this->_dbTable SET 
                $data
                WHERE $this->_id = '$data->$this->_id'";
        
        return $this->_bd->sql($sql);
    }
}
?>
