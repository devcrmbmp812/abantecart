DROP TABLE IF EXISTS `abc_search_log`;
CREATE TABLE `abc_search_log` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`search_keyword` varchar(255) NOT NULL DEFAULT '',
	`date_added` timestamp NOT NULL DEFAULT,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
