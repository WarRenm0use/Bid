<?php
require_once 'Bd.php';

/**
 * Description of WebPayMP
 *
 * @author Alvaro
 */
class WebPayMP {
    protected $_dbTable = "webpay";
    protected $_id = "Tbk_Orden_Compra";
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
    
    function deleteByIdCarro($id) {
        $id = $this->_bd->limpia($id);
        
        $sql = "DELETE FROM $this->_dbTable WHERE Tbk_Orden_Compra = $id";
        return $this->_bd->sql($sql);
    }

}

?>
