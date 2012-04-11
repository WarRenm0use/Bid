--
-- Estructura de tabla para la tabla `webpay`
--

CREATE TABLE IF NOT EXISTS `webpay` (
 `webpay_id` int(10) NOT NULL auto_increment,
 `Tbk_Orden_Compra` varchar(30) NOT NULL default '',
 `Tbk_Codigo_Comercio` varchar(12) default NULL,
 `Tbk_Codigo_Comercio_Enc` varchar(255) default NULL,
 `Tbk_Tipo_Transaccion` varchar(50) NOT NULL default '',
 `Tbk_Respuesta` int(2) NOT NULL default '0',
 `Tbk_Monto` int(10) NOT NULL default '0',
 `Tbk_Codigo_Autorizacion` varchar(6) NOT NULL default '0',
 `Tbk_Numero_Tarjeta` varchar(19) NOT NULL default '0',
 `Tbk_Final_numero_Tarjeta` varchar(4) NOT NULL default '0',
 `Tbk_Fecha_Expiracion` varchar(6) NOT NULL default '',
 `Tbk_Fecha_Contable` varchar(4) NOT NULL default '',
 `Tbk_Fecha_Transaccion` varchar(8) NOT NULL default '',
 `Tbk_Hora_Transaccion` varchar(6) NOT NULL default '',
 `Tbk_Id_Sesion` varchar(40) NOT NULL default '',
 `Tbk_Id_Transaccion` int(20) NOT NULL default '0',
 `Tbk_Tipo_Pago` char(2) NOT NULL default '',
 `Tbk_Numero_Cuotas` char(2) NOT NULL default '0',
 `Tbk_Tasa_Interes_Max` int(4) NOT NULL default '0',
 `Tbk_Mac` varchar(32) NOT NULL default '',
 `TBK_VCI` varchar(20) default NULL,
 `FechaCompleta` datetime default NULL,
 PRIMARY KEY  (`webpay_id`),
 KEY `webpay_id` (`webpay_id`),
 KEY `Tbk_Id_Sesion` (`Tbk_Id_Sesion`),
 KEY `Tbk_Id_Transaccion` (`Tbk_Id_Transaccion`),
 KEY `Tbk_Orden_Compra` (`Tbk_Orden_Compra`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Volcar la base de datos para la tabla `webpay`
--