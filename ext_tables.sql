CREATE TABLE `tx_cmisservice_queue` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_class` tinytext NOT NULL,
  `resource_identifier` tinytext,
  `parameters` text,
  PRIMARY KEY (`uid`)
);

CREATE TABLE `tx_cmisservice_identity` (
  `cmis_uuid` tinytext NOT NULL,
  `cmis_version` varchar(8) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL auto_increment,
  `foreign_tablename` varchar(255) NOT NULL DEFAULT '',
  `foreign_uid` int(11) DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid),
  KEY foreign_relation (foreign_tablename, foreign_uid),
  KEY cmis_uuid (cmis_uuid(48))
);
