<?php
require_once 'Bd.php';

/**
 * ESTADO_SUBASTA:
 *  0: PENDIENTE
 *  1: ACTIVADA
 *  2: ANULADA
 *  3: EN CURSO
 *  4: FINALIZADA
 */

class SubastaVipMP {
    protected $_dbTable = "SVIP";
    protected $_id = "ID_SVIP";
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
    
    function fetchHoy() {
        $hoy = date("Y-m-d");
        $sql = "SELECT *, from_unixtime(INICIO_SUBASTA, '%Y-%m-%d %H:%i') INI_SUBASTA 
                FROM $this->_dbTable AS S 
                    INNER JOIN PRODUCTO AS P 
                    INNER JOIN IMAGEN AS I 
                ON S.ID_PRODUCTO = P.ID_PRODUCTO
                    AND from_unixtime(INICIO_SUBASTA, '%Y-%m-%d') = '$hoy'
                    AND P.ID_MAIN_IMAGEN = I.ID_IMAGEN";
        
//        echo $sql."<br>";
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
    
    function nextSubasta() {
        $now = date("U");
        $sql = "SELECT *, from_unixtime(INICIO_SUBASTA, '%d-%m-%Y %H:%i') INI_SUBASTA
                FROM $this->_dbTable AS S 
                    INNER JOIN PRODUCTO AS P 
                    INNER JOIN IMAGEN AS I 
                ON S.ID_PRODUCTO = P.ID_PRODUCTO
                    AND P.ID_MAIN_IMAGEN = I.ID_IMAGEN
                    AND S.ESTADO_SUBASTA IN (0,1,3)
                ORDER BY INICIO_SUBASTA ASC
                LIMIT 0,1";
        
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $arr = array();
        $row = mysql_fetch_object($res);
        $fecha = explode(" ", $row->INI_SUBASTA);
        $tiempo = explode(":", $fecha[1]);
        $row->FECHA_SUBASTA = $fecha[0];
        $row->HRS_SUBASTA = $tiempo[0];
        $row->MIN_SUBASTA = $tiempo[1];
        return $row;
    }
    
    function fetchAll() {
        $sql = "SELECT *, from_unixtime(INICIO_SUBASTA, '%Y-%m-%d %H:%i') INI_SUBASTA, from_unixtime(TERMINO_SUBASTA, '%d-%m-%Y %h:%i:%s') FIN_SUBASTA 
                FROM $this->_dbTable AS S 
                    LEFT JOIN USUARIO AS U
                ON S.ID_USUARIO = U.ID_USUARIO
                    INNER JOIN PRODUCTO AS P 
                ON S.ID_PRODUCTO = P.ID_PRODUCTO";
//        echo $sql."<br>";
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
        
        $sql = "SELECT S.MONTO_SUBASTA, U.NICK_USUARIO, S.INICIO_SUBASTA, S.ESTADO_SUBASTA, 
            ((S.DURACION_SUBASTA + S.RETRASO_SUBASTA) - ($now - S.INICIO_SUBASTA)) AS RESTO_TIEMPO_SEC,
            TIMEDIFF(
                from_unixtime(S.INICIO_SUBASTA + S.DURACION_SUBASTA + S.RETRASO_SUBASTA), 
                from_unixtime($now)
            ) AS RESTO_TIEMPO
            FROM SVIP AS S 
                INNER JOIN USUARIO AS U 
            ON 
                S.ID_USUARIO = U.ID_USUARIO 
                AND S.ESTADO_SUBASTA = 1
            ORDER BY RESTO_TIEMPO_SEC ".$ords[$ord];
        $res = $this->_bd->sql($sql);
        $arr = array();
//        echo $sql."<br>";
        while($row = mysql_fetch_object($res)) {
            $row->NOW = $now;
            if($row->RESTO_TIEMPO_SEC <0) {
                $sql = "UPDATE SUBASTA SET ESTADO_SUBASTA = 4, TERMINO_SUBASTA = $now WHERE ID_SUBASTA = $row->ID_SUBASTA";
                $this->_bd->sql($sql);
            } else {
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
    
    function setReserva($data) {
        $sql = "SELECT ID_SVIP_USUARIO FROM SVIP_USUARIO WHERE ID_SVIP = $data->ID_SVIP AND ID_USUARIO = $data->ID_USUARIO";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        $row = mysql_fetch_object($res);
        if(isset($row->ID_SVIP_USUARIO)) {
            $data->ID_SVIP_USUARIO = $row->ID_SVIP_USUARIO;
            $this->updateReserva($data);
        } else {
            $data->ID_SVIP_USUARIO = $this->saveReserva($data);
        }
        return $data->ID_SVIP_USUARIO;
    }
    
    function saveReserva($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        foreach($keys as $k) {
            if($k!="ID_SVIP_USUARIO") {
                if($vals != "") {
                    $vars .= ", ".$k;
                    $vals .= ", '".mysql_real_escape_string($data->$k)."'";
                } else {
                    $vars = $k;
                    $vals = "'".mysql_real_escape_string($data->$k)."'";
                }
            }
            $i++;
        }
        
        $sql = "INSERT INTO SVIP_USUARIO ($vars) VALUES ($vals)";
//        echo $sql."<br>";
        $this->_bd->sql($sql);
        return mysql_insert_id();
    }

    function updateReserva($obj) {
        $variables = get_object_vars($obj);
        $keys = array_keys($variables);
        
        $i = 0;
        $val = "";
        foreach($keys as $k) {
            if($k != "ID_SVIP_USUARIO") {
                if($val != "") {
                    $val .= ", ".$k." = '".mysql_real_escape_string(stripslashes($obj->$k))."'";
                } else {
                    $val = $k." = '".mysql_real_escape_string(stripslashes($obj->$k))."'";
                }
            }
            $i++;
        }
        
        $sql = "UPDATE SVIP_USUARIO SET 
                $val
                WHERE ID_SVIP_USUARIO = '$obj->ID_SVIP_USUARIO'";
//        echo $sql."<br>";
        return $this->_bd->sql($sql);
    }
    
    function delReserva($idUs, $idSu) {
        $idUs = $this->_bd->limpia($idUs);
        $idSu = $this->_bd->limpia($idSu);
        $sql = "UPDATE SVIP_USUARIO SET ESTADO_RESERVA = 0 WHERE ID_USUARIO = $idUs AND ID_SVIP = $idSu";
        $res = $this->_bd->sql($sql);
        
        return mysql_affected_rows();
    }
    
    function inSubasta($idUs, $idSu=null, $cod=null) {
        $idUs = $this->_bd->limpia($idUs);
        $idSu = $this->_bd->limpia($idSu);
        $cod = $this->_bd->limpia($cod);
        
        if($idSu!=null)
            $sql = "SELECT ID_SVIP_USUARIO, S.ESTADO_SUBASTA, S.ID_SVIP FROM SVIP_USUARIO SU INNER JOIN SVIP AS S ON SU.ID_SVIP = $idSu AND SU.ID_USUARIO = $idUs AND SU.ID_SVIP = S.ID_SVIP AND SU.ESTADO_RESERVA = 1";
        else if($cod!=null)
            $sql = "SELECT ID_SVIP_USUARIO FROM SVIP_USUARIO AS SU INNER JOIN SVIP AS S ON S.COD_SUBASTA = '$cod' AND SU.ID_USUARIO = $idUs AND S.ID_SVIP = SU.ID_SVIP AND SU.ESTADO_RESERVA = 1";
        
        $res = $this->_bd->sql($sql);
        if(mysql_num_rows($res)>0) return mysql_fetch_object($res);
        else return false;
    }
    
    function inSubastaFB($idUsFB, $idSu) {
        $idUsFB = $this->_bd->limpia($idUsFB);
        $idSu = $this->_bd->limpia($idSu);
        $cod = $this->_bd->limpia($cod);
        
        $sql = "SELECT SU.ID_SVIP_USUARIO, S.ESTADO_SUBASTA, S.ID_SVIP FROM SVIP_USUARIO AS SU INNER JOIN SVIP AS S INNER JOIN USUARIO AS U ON SU.ID_SVIP = $idSu AND SU.ID_USUARIO = U.ID_USUARIO AND U.FB_UID = $idUsFB AND SU.ID_SVIP = S.ID_SVIP AND SU.ESTADO_RESERVA = 1";
        
        $res = $this->_bd->sql($sql);
        if(mysql_num_rows($res)>0) return mysql_fetch_object($res);
        else return false;
    }
    
    function nUsSubasta($idSu) {
        $idSu = $this->_bd->limpia($idSu);
        $sql = "SELECT COUNT(ID_USUARIO) AS N_USUARIO FROM SVIP_USUARIO WHERE ID_SVIP = $idSu AND ESTADO_RESERVA = 1";
        $res = $this->_bd->sql($sql);
        return mysql_fetch_object($res)->N_USUARIO;
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
    
    function refreshByCod($cod, $attr = null) {
        $cod = $this->_bd->limpia($cod);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $now = date("U");
        
        $sql = "SELECT S.MONTO_SUBASTA, U.NICK_USUARIO,
            ((S.DURACION_SUBASTA + S.RETRASO_SUBASTA) - ($now - S.INICIO_SUBASTA)) AS RESTO_TIEMPO_SEC,
            TIMEDIFF(
                from_unixtime(S.INICIO_SUBASTA + S.DURACION_SUBASTA + S.RETRASO_SUBASTA), 
                from_unixtime($now)
            ) AS RESTO_TIEMPO
            FROM SVIP AS S 
                INNER JOIN USUARIO AS U 
            ON 
                S.ID_USUARIO = U.ID_USUARIO 
                AND S.COD_SUBASTA = '$cod'
                AND S.ESTADO_SUBASTA <> 2";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = mysql_fetch_object($res);
            $row->NOW = $now;
            if($row->RESTO_TIEMPO_SEC < 0 ) {
                $sql = "UPDATE SUBASTA SET ESTADO_SUBASTA = 4, TERMINO_SUBASTA = $now WHERE ID_SVIP = $row->ID_SVIP";
                $this->_bd->sql($sql);
            }
            return $row;
        } else return false;
    }
    
    function doBid($idSu, $idUs) {
        $idSu = $this->_bd->limpia($idSu);
        $idUs = $this->_bd->limpia($idUs);
        
        $sql = "UPDATE SVIP SET MONTO_SUBASTA = MONTO_SUBASTA + COSTO_BID_PESOS, ID_USUARIO = $idUs WHERE ID_SVIP = $idSu";
        $res1 = $this->_bd->sql($sql);
        $sql = "UPDATE SVIP_USUARIO SET BID_USADO = BID_USADO + 1 WHERE ID_SVIP = $idSu AND ID_USUARIO = $idUs";
        $res2 = $this->_bd->sql($sql);
        
        return ($res1 && $res2);
    }
    
    function retrasa($idSu) {
        $idSu = $this->_bd->limpia($idSu);
        $sql = "UPDATE SVIP SET RETRASO_SUBASTA = RETRASO_SUBASTA + TIEMPO_RETRASO WHERE ID_SVIP = $idSu";
        $res = $this->_bd->sql($sql);
        return $res;
    }
    
    function recargaBid($nBid, $idSu, $idUs) {
        $nBid = $this->_bd->limpia($nBid);
        $idSu = $this->_bd->limpia($idSu);
        $idUs = $this->_bd->limpia($idUs);
        
        $sql = "UPDATE SVIP_USUARIO SET BID_TOTAL = BID_TOTAL + $nBid, RECARGA_USADA = RECARGA_USADA + 1 WHERE ID_SVIP = $idSu AND ID_USUARIO = $idUs";
        $this->_bd->sql($sql);
        
        return $this->fetchUsuario($idSu, $idUs);
    }
    
    function fetchUsuario($idSu, $idUs) {
        $idUs = $this->_bd->limpia($idUs);
        $idSu = $this->_bd->limpia($idSu);
        
        $sql = "SELECT *, (BID_TOTAL - BID_USADO) AS BID_RESTO FROM SVIP_USUARIO WHERE ID_SVIP = $idSu AND ID_USUARIO = $idUs";
        
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = mysql_fetch_object($res);
            return $row;
        } else return false;
    }
    
    function fetchByUsuario($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }
        
        $sql = "SELECT S.ID_SVIP, S.MONTO_SUBASTA, S.ID_USUARIO, S.INICIO_SUBASTA, S.COD_SUBASTA, S.ESTADO_SUBASTA, P.NOM_PRODUCTO, I.URL_IMAGEN
            FROM SVIP_USUARIO AS SU
                INNER JOIN SVIP AS S 
                INNER JOIN PRODUCTO AS P 
                INNER JOIN IMAGEN AS I
            ON 
                SU.ID_USUARIO = $id
                AND ESTADO_RESERVA = 1
                AND S.ID_SVIP = SU.ID_SVIP
                AND P.ID_PRODUCTO = S.ID_PRODUCTO
                AND P.ID_MAIN_IMAGEN = I.ID_IMAGEN
                ORDER BY S.INICIO_SUBASTA DESC";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        if($res) {
            while($row = mysql_fetch_object($res)) {
                if($row->ID_USUARIO == $id) $row->IS_GANADOR = true;
                else $row->IS_GANADOR = false;
                $img = explode(".", $row->URL_IMAGEN);
                $imgMini = $img[0]."_mini.".$img[1];
                $row->URL_IMAGEN_MINI = $imgMini;
                $r[] = $row;
            }
            return $r;
        } else return false;
    }
    
    function getGanador($id) {
        $id = $this->_bd->limpia($id);
        $now = date("U");
        
        $sql = "SELECT S.ID_SVIP, S.COD_SUBASTA, S.MONTO_SUBASTA, U.ID_USUARIO, U.NICK_USUARIO, S.INICIO_SUBASTA, S.ESTADO_SUBASTA, S.ID_PRODUCTO,  
            ((S.DURACION_SUBASTA + S.RETRASO_SUBASTA) - ($now - S.INICIO_SUBASTA)) AS RESTO_TIEMPO_SEC,
            TIMEDIFF(
                from_unixtime(S.INICIO_SUBASTA + S.DURACION_SUBASTA + S.RETRASO_SUBASTA), 
                from_unixtime($now)
            ) AS RESTO_TIEMPO
            FROM SVIP AS S 
                INNER JOIN USUARIO AS U 
            ON 
                S.ID_USUARIO = U.ID_USUARIO 
                AND S.ID_SVIP = $id
                AND S.ESTADO_SUBASTA = 4
                ";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = mysql_fetch_object($res);
            return $row;
        } else return false;
    }
    
    function refreshById($id, $attr = null) {
        $id = $this->_bd->limpia($id);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $now = date("U");
        
        $sql = "SELECT S.ID_SVIP, S.COD_SUBASTA, S.MONTO_SUBASTA, U.ID_USUARIO, U.NICK_USUARIO, S.INICIO_SUBASTA, S.ESTADO_SUBASTA, S.ID_PRODUCTO,  
            ((S.DURACION_SUBASTA + S.RETRASO_SUBASTA) - ($now - S.INICIO_SUBASTA)) AS RESTO_TIEMPO_SEC,
            TIMEDIFF(
                from_unixtime(S.INICIO_SUBASTA + S.DURACION_SUBASTA + S.RETRASO_SUBASTA), 
                from_unixtime($now)
            ) AS RESTO_TIEMPO
            FROM SVIP AS S 
                INNER JOIN USUARIO AS U 
            ON 
                S.ID_USUARIO = U.ID_USUARIO 
                AND S.ID_SVIP = $id
                AND (S.ESTADO_SUBASTA = 0 OR S.ESTADO_SUBASTA = 1 OR S.ESTADO_SUBASTA = 3)
                ";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = mysql_fetch_object($res);
            $row->NOW = $now;
            $row->MONTO_SUBASTA_H = number_format($row->MONTO_SUBASTA, 0, ",", ".");
            if($row->RESTO_TIEMPO_SEC < 0 ) {
                $sql = "UPDATE SVIP SET ESTADO_SUBASTA = 4, TERMINO_SUBASTA = $now WHERE ID_SVIP = $id";
                $this->_bd->sql($sql);
                $row->RESTO_TIEMPO_SEC = 0;
                $row->RESTO_TIEMPO = "00:00:00";
                $row->ESTADO_SUBASTA = 4;
            }
            if($row->INICIO_SUBASTA > $now) {
                $row->ERROR = 1;
                $row->RESTO_TIEMPO = "Todavia no comienza";
            } else $row->ERROR = 0;
            return $row;
        } else return false;
    }
    
    function getId($cod) {
        $cod = $this->_bd->limpia($cod);
        $sql = "SELECT ID_SVIP FROM SVIP WHERE COD_SUBASTA = '$cod'";
        $res = $this->_bd->sql($sql);
        if($res)
            return mysql_fetch_object($res);
        else return false;
    }
    
    function findbyCod($cod, $attr = null) {
        $cod = $this->_bd->limpia($cod);

        if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $now = date("U");
        
        $sql = "SELECT S.ID_SVIP, S.MONTO_SUBASTA, S.INICIO_SUBASTA, S.N_RECARGA_BID, S.ESTADO_SUBASTA, S.COD_SUBASTA, U.ID_USUARIO, U.NICK_USUARIO, I.URL_IMAGEN, P.NOM_PRODUCTO,
            ((S.DURACION_SUBASTA + S.RETRASO_SUBASTA) - ($now - S.INICIO_SUBASTA)) AS RESTO_TIEMPO_SEC,
            TIMEDIFF(
                from_unixtime(S.INICIO_SUBASTA + S.DURACION_SUBASTA + S.RETRASO_SUBASTA), 
                from_unixtime($now)
            ) AS RESTO_TIEMPO
            FROM SVIP AS S 
                LEFT JOIN USUARIO AS U 
            ON 
                S.ID_USUARIO = U.ID_USUARIO 
            INNER JOIN PRODUCTO AS P 
            INNER JOIN IMAGEN AS I
                ON S.COD_SUBASTA = '$cod'
                
                AND S.ID_PRODUCTO = P.ID_PRODUCTO 
                AND P.ID_MAIN_IMAGEN = I.ID_IMAGEN";
//        echo $sql."<br>";
        $res = $this->_bd->sql($sql);
        if($res) {
            $row = mysql_fetch_object($res);
            $row->NOW = $now;
            if($row->RESTO_TIEMPO_SEC < 0 ) {
                $sql = "UPDATE SUBASTA SET ESTADO_SUBASTA = 4, TERMINO_SUBASTA = $now WHERE ID_SVIP = $row->ID_SVIP";
                $this->_bd->sql($sql);
            }
            if($row->ESTADO_SUBASTA == 1) {
                $row->ERROR = 1;
                $row->RESTO_TIEMPO = "Ya esta por comenzar!";
            } else if($row->ESTADO_SUBASTA == 0) {
                $row->ERROR = 1;
                $row->RESTO_TIEMPO = "Todavia no comienza";
            } else if($row->ESTADO_SUBASTA == 2){
                $row->ERROR = 1;
                $row->RESTO_TIEMPO = "Anulada!";
            } else if($row->ESTADO_SUBASTA == 3){
                $row->ERROR = 0;
            } else if($row->ESTADO_SUBASTA == 4) {
                $row->ERROR = 0;
                $row->RESTO_TIEMPO = "Terminada!";
            }
            return $row;
        } else {
            $res = new stdClass();
            $res->ERROR = 1;
            return $res;
        }
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
        
        $sql = "INSERT INTO $this->_dbTable ($vars) VALUES ($vals)";
        
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
        return $this->_bd->sql($sql);
    }
}
?>