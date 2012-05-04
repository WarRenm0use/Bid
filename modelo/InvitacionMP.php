<?php
require_once 'Bd.php';

/**
 * ESTADO_INVITACION:
 *  0: enviada
 *  1: aceptada
 *  2: rechazada
 *  3: eliminada
 */

class InvitacionMP {
    protected $_dbTable = "INVITACION";
    protected $_id = "ID_INVITACION";
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
    
    function fetchByFb($id, $attr) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr, from_unixtime(FECHA_REQUEST, '%d-%m-%Y %T') AS FECHA_REQUEST_H FROM $this->_dbTable WHERE ID_FROM = '$id' AND ESTADO_INVITACION <> 3";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        while($row = mysql_fetch_object($res)) {
            switch($row->ESTADO_INVITACION) {
                case 0:
                    $row->ESTADO_INVITACION_H = "Enviada";
                    break;
                case 1:
                    $row->ESTADO_INVITACION_H = "Aceptada";
                    break;
                case 2:
                    $row->ESTADO_INVITACION_H = "Rechazada";
                    break;
                default:
                    $row->ESTADO_INVITACION_H = "Rechazada";
                    break;
            }
            $arr[] = $row;
        }
        return $arr;
    }
    
    function fetchByUser($id, $attr=null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE ID_USUARIO = $id AND ESTADO_INVITACION <> 3";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }
    
    function fetchByTo($id, $attr=null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT I.$sAttr, U.NOM_USUARIO, U.APE_USUARIO FROM $this->_dbTable AS I INNER JOIN USUARIO AS U ON I.ID_TO = $id AND U.FB_UID = I.ID_FROM";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function find($idR=null, $idTo=null, $attr = null) {
        $idR = $this->_bd->limpia($idR);
        $idTo = $this->_bd->limpia($idTo);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }
        $sql = "SELECT $sAttr FROM $this->_dbTable WHERE ID_REQUEST = $idR AND ID_TO = $idTo";
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = mysql_fetch_object($res);
            return $row;
        } else return false;
    }
    
    function remove($idR, $idTo, $idFrom) {
        $idR = $this->_bd->limpia($idR);
        $idTo = $this->_bd->limpia($idTo);
        $idFrom = $this->_bd->limpia($idFrom);
        $req = $this->find($idR, $idTo, array("ID_FROM"));
        if($idFrom == $req->ID_FROM) {
            $sql = "UPDATE $this->_dbTable SET ESTADO_INVITACION = 3 WHERE ID_REQUEST = $idR AND ID_TO = $idTo AND ESTADO_INVITACION = 0";
            $res1 = $this->_bd->sql($sql);
            if($res1) {
                $sql = "UPDATE USUARIO 
                            SET INVITACION_USADA = INVITACION_USADA-1
                        WHERE FB_UID = '".$req->ID_FROM."'";
                $res2 = $this->_bd->sql($sql);
            } else $res2 = false;
            return ($res1 && $res2);
        } else return false;
    }
    
    function acepta($inv, $bids=2) {
//        $idR = $this->_bd->limpia($idR);
//        $idTo = $this->_bd->limpia($idTo);
        $bids = $this->_bd->limpia($bids);
        $sql = "UPDATE $this->_dbTable SET ESTADO_INVITACION = 1 WHERE ID_REQUEST = $inv->ID_REQUEST AND ID_TO = $inv->ID_TO";
        $res1 = $this->_bd->sql($sql);
        $sql = "UPDATE $this->_dbTable SET ESTADO_INVITACION = 2 WHERE ID_REQUEST <> $inv->ID_REQUEST AND ID_TO = $inv->ID_TO AND ESTADO_INVITACION = 0";
        $res2 = $this->_bd->sql($sql);
//        $req = $this->find($idR, $idTo, array("ID_FROM"));
        $sql = "UPDATE USUARIO 
                    SET INVITACION_ACEPTADA = INVITACION_ACEPTADA+1,
                    BID_GANADO = BID_GANADO+$bids
                WHERE FB_UID = '".$inv->ID_FROM."'";
        
        $res3 = $this->_bd->sql($sql);
        
        $rec = $this->fetchByTo($idTo, array("ID_FROM"));
        $nRec = count($rec);
        for($i=0; $i<$nRec; $i++) {
            if($rec[$i]->ID_FROM != $inv->ID_FROM) {
                if($i==0) $idRec = $rec[$i]->ID_FROM;
                else $idRec .= ",".$rec[$i]->ID_FROM;
            }
        }
        $sql = "UPDATE USUARIO SET INVITACION_RECHAZADA = INVITACION_RECHAZADA+1 WHERE FB_UID IN ($idRec)";
        $res4 = $this->_bd->sql($sql);
        
        return ($res1 && $res2 && $res3);
    }
    
    function findByReq($id, $estado, $attr = null) {
        $id = $this->_bd->limpia($id);
        $estado = $this->_bd->limpia($estado);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }
        
        

        $sql = "SELECT I.$sAttr, U.NOM_USUARIO, U.APE_USUARIO FROM $this->_dbTable AS I INNER JOIN USUARIO AS U ON I.ID_REQUEST IN ( $id ) AND U.FB_UID = I.ID_FROM AND I.ESTADO_INVITACION = $estado GROUP BY U.FB_UID";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        if($res) {
            while($row = mysql_fetch_object($res)) {
                $arr[] = $row;
            }
            return $arr;
        } else return false;
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
        
        $sql = "UPDATE $this->_dbTable SET $val WHERE $this->_id = $data->ID_REQUEST";
//        echo $sql."<br>";
        return $this->_bd->sql($sql);
    }
}
?>
