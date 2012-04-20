<?php
require_once 'Bd.php';

class ComunaMP {
    protected $_dbTable = "COMUNA";
    protected $_id = "ID_COMUNA";
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
}
?>