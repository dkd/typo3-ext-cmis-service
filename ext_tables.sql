CREATE TABLE `tx_cmisservice_queue` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_class` tinytext NOT NULL,
  `resource_identifier` tinytext,
  `parameters` text,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
