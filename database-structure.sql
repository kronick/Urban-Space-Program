CREATE TABLE IF NOT EXISTS `facades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
   `user` int(11) NOT NULL,
   `lat` float NOT NULL,
   `lng` float NOT NULL,
   `alt` float NOT NULL,
   `scale` float NOT NULL,
   `transform` varchar(255) NOT NULL,
   `timetaken` datetime NOT NULL,
   `timeuploaded` datetime NOT NULL,
   `imgurl` tinytext NOT NULL,
   `fernurl` tinytext NOT NULL,
   `processed` tinyint(1) NOT NULL,
   PRIMARY KEY (`id`),
   KEY `user` (`user`,`processed`)
   ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

