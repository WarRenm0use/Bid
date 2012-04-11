<?php
require_once 'Bd.php';

class SubastaMP {
    protected $_dbTable = "SUBASTA";
    protected $_id = "ID_SUBASTA";
    protected $_bd;

    function __construct() {
        $this->_bd = new Bd();
    }
    
    function resetAll() {
        $now = date("U");
        $sql = "UPDATE $this->_dbTable SET INICIO_SUBASTA = $now, ESTADO_SUBASTA = 1, RETRASO_SUBASTA = 0";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
    }
    
    function fetchAll() {
        $sql = "SELECT *, from_unixtime(INICIO_SUBASTA, '%Y-%m-%d %h:%i:%s') INI_SUBASTA, from_unixtime(TERMINO_SUBASTA, '%d-%m-%Y %h:%i:%s') FIN_SUBASTA 
                FROM $this->_dbTable AS S 
                    LEFT JOIN USUARIO AS U
                ON S.ID_USUARIO = U.ID_USUARIO
                    INNER JOIN PRODUCTO AS P 
                ON S.ID_PRODUCTO = P.ID_PRODUCTO";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $fecha = explode(" ", $row->INI_SUBASTA);
            $tiempo = explode(":", $fecha[1]);
            $row->FECHA_SUBASTA = $fecha[0];
            $row->HRS_SUBASTA = $tiempo[0];
            $row->MIN_SUBASTA = $tiempo[1];
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchActive($ord = 1) {
        $ord = $this->_bd->limpia($ord);
        $ords = array("DESC", "ASC");
        $now = date("U");
        
        $sql = "SELECT S.*, U.NICK_USUARIO, I.URL_IMAGEN, 
            ((S.DURACION_SUBASTA + S.RETRASO_SUBASTA) - ($now - S.INICIO_SUBASTA)) AS RESTO_TIEMPO_SEC,
            TIMEDIFF(
                from_unixtime(S.INICIO_SUBASTA + S.DURACION_SUBASTA + S.RETRASO_SUBASTA), 
                from_unixtime($now)
            ) AS RESTO_TIEMPO
            FROM SUBASTA AS S 
                LEFT JOIN USUARIO AS U 
            ON 
                S.ID_USUARIO = U.ID_USUARIO 
            INNER JOIN PRODUCTO AS P 
            INNER JOIN IMAGEN AS I
                ON S.INICIO_SUBASTA < $now
                AND S.ESTADO_SUBASTA <> 2
                AND S.ID_PRODUCTO = P.ID_PRODUCTO 
                AND P.ID_MAIN_IMAGEN = I.ID_IMAGEN
            ORDER BY RESTO_TIEMPO_SEC ".$ords[$ord];
        
//        $sql = "SELECT *, ((S.DURACION_SUBASTA + S.RETRASO_SUBASTA) - ($now - S.INICIO_SUBASTA)) AS RESTO_TIEMPO_SEC
//                FROM $this->_dbTable AS S LEFT JOIN USUARIO AS U 
//                    ON S.ID_USUARIO = U.ID_USUARIO
//                INNER JOIN PRODUCTO AS P 
//                    ON 
//                    S.ID_PRODUCTO = P.ID_PRODUCTO 
//                    AND S.ESTADO_SUBASTA = 1
//                ORDER BY RESTO_TIEMPO_SEC ".$ords[$ord];
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
//            $row->RESTO_TIEMPO = ($row->DURACION_SUBASTA + $row->RETRASO_SUBASTA) - ($now - $row->INICIO_SUBASTA);
            $row->NOW = $now;
            if($row->RESTO_TIEMPO_SEC <0) {
                $sql = "UPDATE SUBASTA SET ESTADO_SUBASTA = 2, TERMINO_SUBASTA = $now WHERE ID_SUBASTA = $row->ID_SUBASTA";
                $this->_bd->sql($sql);
            } else {
//                $row->RESTO_TIEMPO = $this->getTiempo($row->RESTO_TIEMPO_SEC, ":");
                $arr[] = $row;
            }
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

        $now = date("U");
        
        $sql = "SELECT S.*, U.NICK_USUARIO, I.URL_IMAGEN, 
            ((S.DURACION_SUBASTA + S.RETRASO_SUBASTA) - ($now - S.INICIO_SUBASTA)) AS RESTO_TIEMPO_SEC,
            TIMEDIFF(
                from_unixtime(S.INICIO_SUBASTA + S.DURACION_SUBASTA + S.RETRASO_SUBASTA), 
                from_unixtime($now)
            ) AS RESTO_TIEMPO
            FROM SUBASTA AS S 
                LEFT JOIN USUARIO AS U 
            ON 
                S.ID_USUARIO = U.ID_USUARIO 
            INNER JOIN PRODUCTO AS P 
            INNER JOIN IMAGEN AS I
                ON S.ID_SUBASTA = $id
                AND S.INICIO_SUBASTA < $now
                AND S.ESTADO_SUBASTA <> 2
                AND S.ID_PRODUCTO = P.ID_PRODUCTO 
                AND P.ID_MAIN_IMAGEN = I.ID_IMAGEN";
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = mysql_fetch_object($res);
//            $row->RESTO_TIEMPO_SEC = ($row->DURACION_SUBASTA + $row->RETRASO_SUBASTA) - ($now - $row->INICIO_SUBASTA);
            $row->NOW = $now;
//            $row->RESTO_TIEMPO = $this->getTiempo($row->RESTO_TIEMPO_SEC, ":");
//            $row->RESTO_TIEMPO = $row->RESTO_TIEMPO_SEC;
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
        return mysql_insert_id();
    }

    function update($obj) {
        $variables = get_object_vars($obj);
        $keys = array_keys($variables);
        
        $i = 0;
        $val = "";
        foreach($keys as $k) {
            if($k != $this->_id) {
                if($val != "") {
                    $val .= ", ".$k." = '".mysql_real_escape_string(stripslashes($obj->$k))."'";
                } else {
                    $val = $k." = '".mysql_real_escape_string(stripslashes($obj->$k))."'";
                }
            }
            $i++;
        }
        
        $sql = "UPDATE $this->_dbTable SET 
                $val
                WHERE $this->_id = '$obj->ID_SUBASTA'";
//        echo $sql."<br>";
        $this->_bd->sql($sql);
        return $this->find($obj->ID_SUBASTA);
//        return $sql;
    }
}
?>