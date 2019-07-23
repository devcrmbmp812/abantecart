
DROP TABLE IF EXISTS `ac_blog_entry`;
CREATE TABLE `ac_blog_entry` (
  `blog_entry_id` int(16) NOT NULL AUTO_INCREMENT,
  `blog_author_id` int(16) NOT NULL,
  `allow_comment` int(1) NOT NULL,
  `status` int(1) NOT NULL,
  `use_intro` int(1) NOT NULL,
  `use_image` int(1) DEFAULT NULL,
  `release_date` date NOT NULL DEFAULT '0000-00-00',
  `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`blog_entry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `ac_blog_entry_description`;
CREATE TABLE `ac_blog_entry_description` (
  `blog_entry_description_id` int(16) NOT NULL AUTO_INCREMENT,
  `blog_entry_id` int(16) NOT NULL,
  `language_id` int(16) NOT NULL,
  `entry_title` varchar(256) NOT NULL COMMENT 'translatable',
  `entry_intro` text NOT NULL COMMENT 'translatable',
  `content` text NOT NULL COMMENT 'translatable',
  `reference` text NOT NULL COMMENT 'translatable',
  `copyright` text NOT NULL COMMENT 'translatable',
  `entries_lead` varchar(100) NOT NULL COMMENT 'translatable',
  `category_lead` varchar(100) NOT NULL COMMENT 'translatable',
  `product_lead` varchar(100) NOT NULL COMMENT 'translatable',
  `meta_description` varchar(256) NOT NULL COMMENT 'translatable',
  `meta_keywords` varchar(256) NOT NULL COMMENT 'translatable',
  PRIMARY KEY (`blog_entry_description_id`, `language_id`),
  FULLTEXT (`entry_title`, `entry_intro`, `content`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `ac_blog_entry_category_related`;
CREATE TABLE `ac_blog_entry_category_related` (
  `blog_entry_id` int(16) NOT NULL,
  `category_id` int(16) NOT NULL,
  PRIMARY KEY (`blog_entry_id`, `category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

DROP TABLE IF EXISTS `ac_blog_entry_product_related`;
CREATE TABLE `ac_blog_entry_product_related` (
  `blog_entry_id` int(16) NOT NULL,
  `product_id` int(16) NOT NULL,
  PRIMARY KEY (`blog_entry_id`, `product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ac_blog_related_entry`;
CREATE TABLE `ac_blog_related_entry` (
  `blog_entry_id` int(16) NOT NULL,
  `blog_entry_related_id` int(16) NOT NULL,
  PRIMARY KEY (`blog_entry_id`, `blog_entry_related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ac_blog_entry_to_category`;
CREATE TABLE `ac_blog_entry_to_category` (
  `blog_entry_id` int(16) NOT NULL,
  `blog_category_id` int(16) NOT NULL,
  PRIMARY KEY (`blog_entry_id`, `blog_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ac_blog_notifications`;
CREATE TABLE `ac_blog_notifications` (
  `notification_id` int(16) NOT NULL AUTO_INCREMENT,
  `blog_entry_id` int(16) NOT NULL,
  `blog_comment_id` int(16) NOT NULL,
  `primary_comment_id` int(16) NOT NULL,
  `parent_id` int(16) NOT NULL,
  `blog_user_id` int(16) NOT NULL,
  `user_name` varchar(64) NOT NULL,
  `email` varchar(50) NOT NULL,
  `all_comments` int(1) NOT NULL,
  `on_reply` int(1) NOT NULL,
  `status` int(1) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'ON UPDATE CURRENT_TIMESTAMP',
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `ac_blog_category`;
CREATE TABLE `ac_blog_category` (
  `blog_category_id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16) NOT NULL,
  `sort_order` int(8) NOT NULL,
  `status` int(1) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`blog_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `ac_blog_category_description`;
CREATE TABLE `ac_blog_category_description` (
  `blog_category_description_id` int(16) NOT NULL AUTO_INCREMENT,
  `blog_category_id` int(16) NOT NULL,
  `language_id` int(16) NOT NULL,
  `name` varchar(256) NOT NULL,
  `page_title` varchar(256) NOT NULL COMMENT 'translatable',
  `description` text NOT NULL COMMENT 'translatable',
  `meta_description` varchar(256) NOT NULL COMMENT 'translatable',
  `meta_keyword` varchar(256) NOT NULL COMMENT 'translatable',
  PRIMARY KEY (`blog_category_description_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;


DROP TABLE IF EXISTS `ac_blog_author`;
CREATE TABLE `ac_blog_author` (
  `blog_author_id` int(16) NOT NULL AUTO_INCREMENT,
  `blog_user_id` int(16) NOT NULL,
  `status` int(1) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `role_id` int(1) NOT NULL,
  `contact_info` varchar(256) NOT NULL,
  `email` varchar(60) NOT NULL,
  `site_url` varchar(256) NOT NULL,
  `show_author_page` int(1) NOT NULL DEFAULT '0',
  `show_details` int(1) NOT NULL DEFAULT '1',
  `show_details_ap` int(1) NOT NULL DEFAULT '1',
  `show_author_link` int(1) NOT NULL DEFAULT '1',
  `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`blog_author_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `ac_blog_author_description`;
CREATE TABLE `ac_blog_author_description` (
  `blog_author_description_id` int(16) NOT NULL AUTO_INCREMENT,
  `blog_author_id` int(16) NOT NULL,
  `language_id` int(16) NOT NULL,
  `author_description` text NOT NULL COMMENT 'translatable',
  `author_title` varchar(256) NOT NULL COMMENT 'translatable',
  `meta_description` varchar(256) NOT NULL COMMENT 'translatable',
  `meta_keywords` varchar(256) NOT NULL COMMENT 'translatable',
  PRIMARY KEY (`blog_author_description_id`, `language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `ac_blog_comment`;
CREATE TABLE `ac_blog_comment` (
  `blog_comment_id` int(16) NOT NULL AUTO_INCREMENT,
  `primary_comment_id` int(16) NOT NULL,
  `blog_entry_id` int(16) NOT NULL,
  `parent_id` int(16) NOT NULL,
  `blog_user_id` int(16) NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(60) NOT NULL,
  `site_url` varchar(256) NOT NULL,
  `comment` text NOT NULL COMMENT 'translatable',
  `approved` int(1) NOT NULL,
  `status` int(1) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`blog_comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `ac_blog_view`;
CREATE TABLE `ac_blog_view` (
  `blog_view_id` int(16) NOT NULL AUTO_INCREMENT,
  `blog_entry_id` int(16) NOT NULL,
  `view` int(16) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`blog_view_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `ac_blog_user`;
CREATE TABLE `ac_blog_user` (
  `blog_user_id` int(16) NOT NULL AUTO_INCREMENT,
  `customer_id` int(16) NOT NULL,
  `source` varchar(12) NOT NULL,
  `status` int(1) NOT NULL,
  `role_id` int(3) NOT NULL,
  `firstname` varchar(60) NOT NULL,
  `lastname` varchar(60) NOT NULL,
  `username` varchar(40) NOT NULL,
  `name_option` int(1) NOT NULL DEFAULT '0',
  `password` varchar(40) NOT NULL,
  `email` varchar(60) NOT NULL,
  `users_tz` varchar(40) NOT NULL DEFAULT '0',
  `site_url` varchar(60) NOT NULL,
  `admin_comment` text NOT NULL COMMENT 'translatable',
  `approve` int(1) NOT NULL,
  `user_approve_comments` int(1) NOT NULL,
  `user_require_approval` int(1) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`blog_user_id`,`customer_id`) USING BTREE,
  UNIQUE KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;  

DROP TABLE IF EXISTS `ac_blog_user_role`;
CREATE TABLE `ac_blog_user_role` (
  `role_id` int(11) NOT NULL,
  `role_description` varchar(30) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `ac_blog_user_role` (`role_id`, `role_description`) VALUES
(1, 'Admin'),
(2, 'Author'),
(3, 'Contributor'),
(4, 'Customer'),
(5, 'Blog User');

DROP TABLE IF EXISTS `ac_blog_settings`;
CREATE TABLE `ac_blog_settings` (
	`blog_setting_id` int(16) NOT NULL AUTO_INCREMENT,
	`key` varchar(64) NOT NULL,
	`value` text NOT NULL,
	`date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  	PRIMARY KEY (`blog_setting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;   
  
INSERT INTO `ac_blog_settings` (`blog_setting_id`, `key`, `value`, `date_added`, `date_modified`) VALUES
(1, 'blog_store_id', '0', now(), now()),
(2, 'use_store_url', '0', now(), now()),
(3, 'blog_ssl', '0', now(), now()),
(4, 'blog_ssl_url', 'blog', now(), now()),
(5, 'show_dt', 'dt_system', now(), now()),
(6, 'show_entry_view_count', '1', now(), now()),
(7, 'default_blog_category', '1', now(), now()),
(8, 'omit_uncatergorized', '1', now(), now()),
(9, 'show_related_prices', '1', now(), now()),
(10, 'show_month', '0', now(), now()),
(11, 'entry_display_order', 'DESC', now(), now()),
(12, 'comment_display_order', 'DESC', now(), now()),
(13, 'entries_per_main_page', '10', now(), now()),
(14, 'word_count_main', '300', now(), now()),
(15, 'feed_type', 'rss2', now(), now()),
(16, 'entries_per_rss_feed', '10', now(), now()),
(17, 'word_count_feed', '300', now(), now()),
(18, 'feed_show_thumb', '0', now(), now()),
(19, 'blog_entry_image_width', '225', now(), now()),
(20, 'blog_entry_image_height', '100', now(), now()),
(21, 'blog_product_image_width', '100', now(), now()),
(22, 'blog_product_image_height', '100', now(), now()),
(23, 'blog_category_image_width', '57', now(), now()),
(24, 'blog_category_image_height', '57', now(), now()),
(25, 'blog_feed_image_width', '100', now(), now()),
(26, 'blog_feed_image_height', '100', now(), now()),
(27, 'blog_access', 'all', now(), now()),
(28, 'approve_user', '0', now(), now()),
(29, 'autofill_form', '0', now(), now()),
(30, 'login_data', 'customer', now(), now()),
(31, 'blog_access', 'all', now(), now()),
(32, 'customer_groups', '1', now(), now());
