<?php
require_once 'Bd.php';

class SubastaMP {
    protected $_dbTable = "SUBASTA";
    protected $_id = "ID_SUBASTA";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }

    function fetchAll() {
        $sql = "SELECT *
                FROM $this->_dbTable AS S 
                    INNER JOIN PRODUCTO AS P 
                ON 
                    S.ID_PRODUCTO = P.ID_PRODUCTO ";
        $res = $this->_bd->sql($sql);
        $arr = array();
        $now = date("U");
        while($row = mysql_fetch_object($res)) {
            $row->RESTO_TIEMPO = ($row->DURACION_SUBASTA + $row->RETRASO_SUBASTA) - ($now - $row->INICIO_SUBASTA);
            $row->NOW = $now;
            if($row->RESTO_TIEMPO <0) {
                $sql = "UPDATE ";
            }
            $row->RESTO_TIEMPO = $this->getTiempo($row->RESTO_TIEMPO, ":");
            $arr[] = $row;
        }
        return $arr;
    }
    
    function getTiempo($seg, $sep) {
        if($seg >= 0) {
            $hrsA = $seg/3600;
            $hrs = floor($hrsA);
            if($hrs == 0) $hrs = "00";
            else if($hrs<10) $hrs = "0".$hrs;

            $minA = ($hrsA-$hrs)*60;
            $min = floor($minA);
            if($min == 0) $min = "00";
            else if($min<10) $min = "0".$min;

            $sec = floor(($minA-$min)*60);
            if($sec == 0) $sec = "00";
            else if($sec<10) $sec = "0".$sec;

            return $hrs.$sep.$min.$sep.$sec;
        } else { 
            return "TERMINADA!";
        }
    }

    function find($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable AS S INNER JOIN PRODUCTO AS P ON S.$this->_id = $id AND S.ID_PRODUCTO = P.ID_PRODUCTO";
        $now = date("U");
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = mysql_fetch_object($res);
            $row->RESTO_TIEMPO = ($row->DURACION_SUBASTA + $row->RETRASO_SUBASTA) - ($now - $row->INICIO_SUBASTA);
            $row->NOW = $now;
            $row->RESTO_TIEMPO = $this->getTiempo($row->RESTO_TIEMPO, ":");
            return $row;
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
                    $data .= ", '".$data->$k."'";
                } else {
                    $vars = $k;
                    $data = "'".$data->$k."'";
                }
            }
            $i++;
        }
        
        $sql = "INSERT INTO $this->_dbTable ($vars) VALUES
                ($data)";
        
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
                WHERE $this->_id = '$obj->ID_SUBASTA'";
//        echo $sql."<br>";
        return $this->_bd->sql($sql);
    }
}
?>