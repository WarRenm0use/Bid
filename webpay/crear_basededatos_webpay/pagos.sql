CREATE TABLE IF NOT EXISTS `pagos` (
  `pagosID` int(7) NOT NULL auto_increment,
  `tienda` int(2) NOT NULL default '0',
  `TBK_MONTO` varchar(10) NOT NULL default '',
  `TBK_ORDEN_COMPRA` varchar(40) NOT NULL default '',
  `TBK_ID_SESION` varchar(40) NOT NULL default '',
  `PRODUCTO` varchar(60) NOT NULL,
  `usr_nombre` text NOT NULL,
  `usr_apellido` text NOT NULL,
  `usr_email` text NOT NULL,
  PRIMARY KEY  (`pagosID`),
  KEY `tienda` (`tienda`),
  KEY `pagosID` (`pagosID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;