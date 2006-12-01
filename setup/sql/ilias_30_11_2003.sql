# phpMyAdmin SQL Dump
# version 2.5.3
# http://www.phpmyadmin.net
#
# Host: localhost
# Erstellungszeit: 30. September 2003 um 20:03
# Server Version: 4.0.14
# PHP-Version: 4.3.2
# 
# Datenbank: `ilias3`
# 



#
# Tabellenstruktur für Tabelle `addressbook`
#

CREATE TABLE `addressbook` (
  `addr_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `login` varchar(40) default NULL,
  `firstname` varchar(40) default NULL,
  `lastname` varchar(40) default NULL,
  `email` varchar(40) default NULL,
  PRIMARY KEY  (`addr_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `addressbook`
#




#
# Tabellenstruktur für Tabelle `bookmark_data`
#

CREATE TABLE `bookmark_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `target` varchar(200) NOT NULL default '',
  `type` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`user_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `bookmark_data`
#




#
# Tabellenstruktur für Tabelle `bookmark_tree`
#

CREATE TABLE `bookmark_tree` (
  `tree` int(11) NOT NULL default '0',
  `child` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned default NULL,
  `lft` int(11) unsigned NOT NULL default '0',
  `rgt` int(11) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  KEY `child` (`child`),
  KEY `parent` (`parent`)
) TYPE=MyISAM;

#
# Daten für Tabelle `bookmark_tree`
#

INSERT INTO `bookmark_tree` VALUES (6, 1, 0, 1, 2, 1);



#
# Tabellenstruktur für Tabelle `cal_appointment`
#

CREATE TABLE `cal_appointment` (
  `appointmentId` int(11) NOT NULL auto_increment,
  `appointmentUnionId` int(11) NOT NULL default '0',
  `categoryId` int(11) NOT NULL default '0',
  `priorityId` int(11) NOT NULL default '0',
  `access` varchar(15) NOT NULL default '',
  `description` text,
  `duration` int(11) NOT NULL default '0',
  `startTimestamp` bigint(14) NOT NULL default '0',
  `term` varchar(128) NOT NULL default '',
  `location` varchar(80) default NULL,
  `serial` tinyint(1) unsigned NOT NULL default '0',
  `ownerId` int(11) unsigned NOT NULL default '0',
  `userId` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`appointmentId`)
) TYPE=MyISAM;

#
# Daten für Tabelle `cal_appointment`
#




#
# Tabellenstruktur für Tabelle `cal_appointmentrepeats`
#

CREATE TABLE `cal_appointmentrepeats` (
  `appointmentRepeatsId` int(11) NOT NULL auto_increment,
  `appointmentId` int(11) NOT NULL default '0',
  `endTimestamp` int(14) default NULL,
  `type` varchar(15) NOT NULL default '',
  `weekdays` varchar(7) NOT NULL default 'nnnnnnn',
  PRIMARY KEY  (`appointmentRepeatsId`)
) TYPE=MyISAM;

#
# Daten für Tabelle `cal_appointmentrepeats`
#




#
# Tabellenstruktur für Tabelle `cal_appointmentrepeatsnot`
#

CREATE TABLE `cal_appointmentrepeatsnot` (
  `appointmentRepeatsNotId` int(11) NOT NULL auto_increment,
  `appointmentRepeatsId` int(11) NOT NULL default '0',
  `leaveOutTimestamp` int(14) default NULL,
  PRIMARY KEY  (`appointmentRepeatsNotId`)
) TYPE=MyISAM;

#
# Daten für Tabelle `cal_appointmentrepeatsnot`
#




#
# Tabellenstruktur für Tabelle `cal_category`
#

CREATE TABLE `cal_category` (
  `categoryId` int(11) NOT NULL auto_increment,
  `description` text,
  `term` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`categoryId`)
) TYPE=MyISAM;

#
# Daten für Tabelle `cal_category`
#

INSERT INTO `cal_category` VALUES (1, 'test', 'test');



#
# Tabellenstruktur für Tabelle `cal_priority`
#

CREATE TABLE `cal_priority` (
  `priorityId` int(11) NOT NULL auto_increment,
  `description` text,
  `term` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`priorityId`)
) TYPE=MyISAM;

#
# Daten für Tabelle `cal_priority`
#

INSERT INTO `cal_priority` VALUES (1, 'high', 'high');
INSERT INTO `cal_priority` VALUES (2, 'middle', 'middle');
INSERT INTO `cal_priority` VALUES (3, 'low', 'low');



#
# Tabellenstruktur für Tabelle `cal_user_group`
#

CREATE TABLE `cal_user_group` (
  `groupId` int(11) NOT NULL default '0',
  `userId` int(11) NOT NULL default '0',
  `description` text,
  PRIMARY KEY  (`groupId`,`userId`)
) TYPE=MyISAM;

#
# Daten für Tabelle `cal_user_group`
#




#
# Tabellenstruktur für Tabelle `dbk_translations`
#

CREATE TABLE `dbk_translations` (
  `id` int(11) NOT NULL default '0',
  `tr_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`,`tr_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `dbk_translations`
#




#
# Tabellenstruktur für Tabelle `desktop_item`
#

CREATE TABLE `desktop_item` (
  `user_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  `type` varchar(4) NOT NULL default '',
  `parameters` varchar(200) default NULL,
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `desktop_item`
#




#
# Tabellenstruktur für Tabelle `dummy_groups`
#

CREATE TABLE `dummy_groups` (
  `groupId` int(11) NOT NULL auto_increment,
  `description` text,
  `owner` varchar(20) NOT NULL default '',
  `term` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`groupId`)
) TYPE=MyISAM;

#
# Daten für Tabelle `dummy_groups`
#




#
# Tabellenstruktur für Tabelle `file_data`
#

CREATE TABLE `file_data` (
  `file_id` int(11) NOT NULL default '0',
  `file_name` char(128) NOT NULL default '',
  `file_type` char(64) NOT NULL default '',
  PRIMARY KEY  (`file_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `file_data`
#




#
# Tabellenstruktur für Tabelle `frm_data`
#

CREATE TABLE `frm_data` (
  `top_pk` bigint(20) NOT NULL auto_increment,
  `top_frm_fk` bigint(20) NOT NULL default '0',
  `top_name` varchar(255) NOT NULL default '',
  `top_description` varchar(255) NOT NULL default '',
  `top_num_posts` int(11) NOT NULL default '0',
  `top_num_threads` int(11) NOT NULL default '0',
  `top_last_post` varchar(50) NOT NULL default '',
  `top_mods` int(11) NOT NULL default '0',
  `top_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `visits` int(11) NOT NULL default '0',
  `top_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `update_user` int(11) NOT NULL default '0',
  `top_usr_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`top_pk`)
) TYPE=MyISAM;

#
# Daten für Tabelle `frm_data`
#




#
# Tabellenstruktur für Tabelle `frm_posts`
#

CREATE TABLE `frm_posts` (
  `pos_pk` bigint(20) NOT NULL auto_increment,
  `pos_top_fk` bigint(20) NOT NULL default '0',
  `pos_thr_fk` bigint(20) NOT NULL default '0',
  `pos_usr_id` bigint(20) NOT NULL default '0',
  `pos_message` text NOT NULL,
  `pos_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `pos_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `update_user` int(11) NOT NULL default '0',
  `pos_cens` tinyint(4) NOT NULL default '0',
  `pos_cens_com` text NOT NULL,
  PRIMARY KEY  (`pos_pk`)
) TYPE=MyISAM;

#
# Daten für Tabelle `frm_posts`
#




#
# Tabellenstruktur für Tabelle `frm_posts_tree`
#

CREATE TABLE `frm_posts_tree` (
  `fpt_pk` bigint(20) NOT NULL auto_increment,
  `thr_fk` bigint(20) NOT NULL default '0',
  `pos_fk` bigint(20) NOT NULL default '0',
  `parent_pos` bigint(20) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `depth` int(11) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`fpt_pk`)
) TYPE=MyISAM;

#
# Daten für Tabelle `frm_posts_tree`
#




#
# Tabellenstruktur für Tabelle `frm_threads`
#

CREATE TABLE `frm_threads` (
  `thr_pk` bigint(20) NOT NULL auto_increment,
  `thr_top_fk` bigint(20) NOT NULL default '0',
  `thr_subject` varchar(255) NOT NULL default '',
  `thr_usr_id` bigint(20) NOT NULL default '0',
  `thr_num_posts` int(11) NOT NULL default '0',
  `thr_last_post` varchar(50) NOT NULL default '',
  `thr_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `thr_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `visits` int(11) NOT NULL default '0',
  PRIMARY KEY  (`thr_pk`)
) TYPE=MyISAM;

#
# Daten für Tabelle `frm_threads`
#




#
# Tabellenstruktur für Tabelle `glossary_definition`
#

CREATE TABLE `glossary_definition` (
  `id` int(11) NOT NULL auto_increment,
  `term_id` int(11) NOT NULL default '0',
  `short_text` varchar(200) NOT NULL default '',
  `nr` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `glossary_definition`
#




#
# Tabellenstruktur für Tabelle `glossary_term`
#

CREATE TABLE `glossary_term` (
  `id` int(11) NOT NULL auto_increment,
  `glo_id` int(11) NOT NULL default '0',
  `term` varchar(200) default NULL,
  `language` char(2) default NULL,
  `import_id` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `glo_id` (`glo_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `glossary_term`
#




#
# Tabellenstruktur für Tabelle `grp_data`
#

CREATE TABLE `grp_data` (
  `grp_id` int(11) NOT NULL default '0',
  `status` int(11) default NULL,
  PRIMARY KEY  (`grp_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `grp_data`
#




#
# Tabellenstruktur für Tabelle `grp_tree`
#

CREATE TABLE `grp_tree` (
  `tree` int(11) NOT NULL default '0',
  `child` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned NOT NULL default '0',
  `lft` int(11) unsigned NOT NULL default '0',
  `rgt` int(11) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  `perm` tinyint(1) unsigned NOT NULL default '0',
  `obj_id` int(11) default NULL,
  PRIMARY KEY  (`tree`,`child`,`parent`)
) TYPE=MyISAM;

#
# Daten für Tabelle `grp_tree`
#




#
# Tabellenstruktur für Tabelle `learning_module`
#

CREATE TABLE `learning_module` (
  `id` int(11) NOT NULL default '0',
  `default_layout` varchar(100) NOT NULL default 'toc2win',
  `stylesheet` int(11) NOT NULL default '0',
  `page_header` enum('st_title','pg_title','none') default 'st_title',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `learning_module`
#




#
# Tabellenstruktur für Tabelle `lm_data`
#

CREATE TABLE `lm_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `type` char(2) NOT NULL default '',
  `lm_id` int(11) NOT NULL default '0',
  `import_id` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lm_data`
#

INSERT INTO `lm_data` VALUES (1, 'dummy', 'du', 0, '');



#
# Tabellenstruktur für Tabelle `lm_tree`
#

CREATE TABLE `lm_tree` (
  `lm_id` int(11) NOT NULL default '0',
  `child` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned default NULL,
  `lft` int(11) unsigned NOT NULL default '0',
  `rgt` int(11) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  KEY `child` (`child`),
  KEY `parent` (`parent`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lm_tree`
#




#
# Tabellenstruktur für Tabelle `lng_data`
#

CREATE TABLE `lng_data` (
  `module` varchar(30) NOT NULL default '',
  `identifier` varchar(50) binary NOT NULL default '',
  `lang_key` char(2) NOT NULL default '',
  `value` blob NOT NULL,
  PRIMARY KEY  (`module`,`identifier`,`lang_key`),
  KEY `module` (`module`),
  KEY `lang_key` (`lang_key`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lng_data`
#

INSERT INTO `lng_data` VALUES ('content', 'cont_bottom', 'en', 0x426f74746f6d);
INSERT INTO `lng_data` VALUES ('content', 'cont_booktitle', 'en', 0x426f6f6b7469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_assignments_deleted', 'en', 0x5468652061737369676e6d656e74732068617665206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('content', 'cont_assign_translation', 'en', 0x41737369676e207472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_alphabetic', 'en', 0x416c706861626574696320612c20622c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_all_pages', 'en', 0x416c6c205061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_all_definitions', 'en', 0x416c6c20446566696e6974696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_added_term', 'en', 0x5465726d206164646564);
INSERT INTO `lng_data` VALUES ('content', 'cont_add_definition', 'en', 0x41646420446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Unordered', 'en', 0x556e6f726465726564);
INSERT INTO `lng_data` VALUES ('content', 'cont_Roman', 'en', 0x526f6d616e20492c2049492c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Remark', 'en', 0x52656d61726b);
INSERT INTO `lng_data` VALUES ('content', 'cont_Reference', 'en', 0x5265666572656e6365);
INSERT INTO `lng_data` VALUES ('content', 'cont_Number', 'en', 0x4e756d626572);
INSERT INTO `lng_data` VALUES ('content', 'cont_Mnemonic', 'en', 0x4d6e656d6f6e6963);
INSERT INTO `lng_data` VALUES ('content', 'cont_LocalFile', 'en', 0x4c6f63616c2046696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_List', 'en', 0x4c697374);
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline3', 'en', 0x486561646c696e652033);
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline2', 'en', 0x486561646c696e652032);
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline1', 'en', 0x486561646c696e652031);
INSERT INTO `lng_data` VALUES ('content', 'cont_Example', 'en', 0x4578616d706c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_Citation', 'en', 0x4369746174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'all', 'en', 0x416c6c);
INSERT INTO `lng_data` VALUES ('content', 'cont_Alphabetic', 'en', 0x416c706861626574696320412c20422c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'PDF export', 'en', 0x504446204578706f7274);
INSERT INTO `lng_data` VALUES ('content', 'Pages', 'en', 0x5061676573);
INSERT INTO `lng_data` VALUES ('content', 'HTML export', 'en', 0x48544d4c204578706f7274);
INSERT INTO `lng_data` VALUES ('common', 'zipcode', 'en', 0x5a697020436f6465);
INSERT INTO `lng_data` VALUES ('common', 'your_message', 'en', 0x596f7572204d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'you_may_add_local_roles', 'en', 0x596f75204d617920416464204c6f63616c20526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'yes', 'en', 0x596573);
INSERT INTO `lng_data` VALUES ('common', 'write', 'en', 0x5772697465);
INSERT INTO `lng_data` VALUES ('common', 'with', 'en', 0x77697468);
INSERT INTO `lng_data` VALUES ('common', 'who_is_online', 'en', 0x57686f206973206f6e6c696e653f);
INSERT INTO `lng_data` VALUES ('common', 'welcome', 'en', 0x57656c636f6d65);
INSERT INTO `lng_data` VALUES ('common', 'week', 'en', 0x5765656b);
INSERT INTO `lng_data` VALUES ('common', 'visits', 'en', 0x566973697473);
INSERT INTO `lng_data` VALUES ('common', 'visible_layers', 'en', 0x56697369626c65204c6179657273);
INSERT INTO `lng_data` VALUES ('common', 'view_content', 'en', 0x5669657720436f6e74656e74);
INSERT INTO `lng_data` VALUES ('common', 'view', 'en', 0x56696577);
INSERT INTO `lng_data` VALUES ('common', 'version', 'en', 0x56657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'value', 'en', 0x56616c7565);
INSERT INTO `lng_data` VALUES ('common', 'validate', 'en', 0x56616c6964617465);
INSERT INTO `lng_data` VALUES ('common', 'usrf', 'en', 0x5573657220466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'usr_style', 'en', 0x55736572205374796c65);
INSERT INTO `lng_data` VALUES ('common', 'usr_skin', 'en', 0x5573657220536b696e);
INSERT INTO `lng_data` VALUES ('common', 'usr_agreement_empty', 'en', 0x5468652061677265656d656e7420636f6e7461696e73206e6f2074657874);
INSERT INTO `lng_data` VALUES ('common', 'usr_agreement', 'en', 0x557365722041677265656d656e74);
INSERT INTO `lng_data` VALUES ('common', 'usr', 'en', 0x55736572);
INSERT INTO `lng_data` VALUES ('common', 'users_online', 'en', 0x5573657273206f6e6c696e65);
INSERT INTO `lng_data` VALUES ('common', 'users', 'en', 0x5573657273);
INSERT INTO `lng_data` VALUES ('common', 'username', 'en', 0x557365726e616d65);
INSERT INTO `lng_data` VALUES ('common', 'userdata', 'en', 0x5573657264617461);
INSERT INTO `lng_data` VALUES ('common', 'user_language', 'en', 0x55736572204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'user_deleted', 'en', 0x557365722064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'user_assignment', 'en', 0x557365722041737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'user_added', 'en', 0x55736572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'user', 'en', 0x55736572);
INSERT INTO `lng_data` VALUES ('common', 'url_description', 'en', 0x55524c204465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'url', 'en', 0x55524c);
INSERT INTO `lng_data` VALUES ('common', 'upload', 'en', 0x55706c6f6164);
INSERT INTO `lng_data` VALUES ('common', 'update_language', 'en', 0x557064617465204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'update_applied', 'en', 0x557064617465204170706c696564);
INSERT INTO `lng_data` VALUES ('common', 'up', 'en', 0x5570);
INSERT INTO `lng_data` VALUES ('common', 'unread', 'en', 0x556e72656164);
INSERT INTO `lng_data` VALUES ('common', 'unknown', 'en', 0x554e4b4e4f574e);
INSERT INTO `lng_data` VALUES ('common', 'uninstalled', 'en', 0x756e696e7374616c6c65642e);
INSERT INTO `lng_data` VALUES ('common', 'uninstall', 'en', 0x556e696e7374616c6c);
INSERT INTO `lng_data` VALUES ('common', 'uid', 'en', 0x554944);
INSERT INTO `lng_data` VALUES ('common', 'type_your_message_here', 'en', 0x5479706520596f7572204d6573736167652048657265);
INSERT INTO `lng_data` VALUES ('common', 'type', 'en', 0x54797065);
INSERT INTO `lng_data` VALUES ('common', 'typ', 'en', 0x4f626a656374205479706520446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'txt_registered', 'en', 0x596f75207375636365737366756c6c79207265676973746572656420746f20494c4941532e20506c6561736520636c69636b206f6e2074686520627574746f6e2062656c6f7720746f206c6f67696e20746f20494c494173207769746820796f757220757365726163636f756e742e);
INSERT INTO `lng_data` VALUES ('common', 'txt_add_object_instruction2', 'en', 0x2e);
INSERT INTO `lng_data` VALUES ('common', 'txt_add_object_instruction1', 'en', 0x42726f77736520746f20746865206c6f636174696f6e20776865726520796f752077616e7420746f20616464);
INSERT INTO `lng_data` VALUES ('common', 'treeview', 'en', 0x547265652056696577);
INSERT INTO `lng_data` VALUES ('common', 'trash', 'en', 0x5472617368);
INSERT INTO `lng_data` VALUES ('common', 'translation', 'en', 0x5472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'tpl_path', 'en', 0x54656d706c6174652050617468);
INSERT INTO `lng_data` VALUES ('common', 'total', 'en', 0x546f74616c);
INSERT INTO `lng_data` VALUES ('common', 'today', 'en', 0x546f646179);
INSERT INTO `lng_data` VALUES ('common', 'to_desktop', 'en', 0x537562736372696265);
INSERT INTO `lng_data` VALUES ('common', 'to', 'en', 0x546f);
INSERT INTO `lng_data` VALUES ('common', 'time', 'en', 0x54696d65);
INSERT INTO `lng_data` VALUES ('common', 'title', 'en', 0x5469746c65);
INSERT INTO `lng_data` VALUES ('common', 'term', 'en', 0x5465726d);
INSERT INTO `lng_data` VALUES ('common', 'test_intern', 'en', 0x5465737420496e7465726e);
INSERT INTO `lng_data` VALUES ('common', 'system_message', 'en', 0x53797374656d204d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'system_language', 'en', 0x53797374656d204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'system_grp', 'en', 0x53797374656d2047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'system_groups', 'en', 0x53797374656d2047726f757073);
INSERT INTO `lng_data` VALUES ('common', 'system_choose_language', 'en', 0x53797374656d2043686f6f7365204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'system', 'en', 0x53797374656d);
INSERT INTO `lng_data` VALUES ('common', 'summary', 'en', 0x53756d6d617279);
INSERT INTO `lng_data` VALUES ('common', 'subscription', 'en', 0x537562736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'subobjects', 'en', 0x5375626f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'submit', 'en', 0x5375626d6974);
INSERT INTO `lng_data` VALUES ('common', 'subject', 'en', 0x5375626a656374);
INSERT INTO `lng_data` VALUES ('common', 'subchapter_new', 'en', 0x4e65772053756263686170746572);
INSERT INTO `lng_data` VALUES ('common', 'subcat_name', 'en', 0x53756263617465676f7279204e616d65);
INSERT INTO `lng_data` VALUES ('common', 'structure', 'en', 0x537472756374757265);
INSERT INTO `lng_data` VALUES ('common', 'street', 'en', 0x537472656574);
INSERT INTO `lng_data` VALUES ('common', 'stop_inheritance', 'en', 0x53746f7020696e6865726974616e6365);
INSERT INTO `lng_data` VALUES ('common', 'step', 'en', 0x53746570);
INSERT INTO `lng_data` VALUES ('common', 'status', 'en', 0x537461747573);
INSERT INTO `lng_data` VALUES ('common', 'startpage', 'en', 0x537461727470616765);
INSERT INTO `lng_data` VALUES ('common', 'st_new', 'en', 0x4e65772063686170746572);
INSERT INTO `lng_data` VALUES ('common', 'st_edit', 'en', 0x456469742063686170746572);
INSERT INTO `lng_data` VALUES ('common', 'st_added', 'en', 0x43686170746572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'st_add', 'en', 0x4164642063686170746572);
INSERT INTO `lng_data` VALUES ('common', 'st_a', 'en', 0x612063686170746572);
INSERT INTO `lng_data` VALUES ('common', 'sort_by_this_column', 'en', 0x536f7274206279207468697320636f6c756d6e);
INSERT INTO `lng_data` VALUES ('common', 'smtp', 'en', 0x534d5450);
INSERT INTO `lng_data` VALUES ('common', 'slm_added', 'en', 0x53434f524d204c6561726e696e674d6f64756c65206164646564);
INSERT INTO `lng_data` VALUES ('common', 'slm', 'en', 0x53434f524d204c4d);
INSERT INTO `lng_data` VALUES ('common', 'signature', 'en', 0x5369676e6174757265);
INSERT INTO `lng_data` VALUES ('common', 'show_owner', 'en', 0x53686f77204f776e6572);
INSERT INTO `lng_data` VALUES ('common', 'show_members', 'en', 0x446973706c6179204d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'show_list', 'en', 0x53686f77204c697374);
INSERT INTO `lng_data` VALUES ('common', 'settings', 'en', 0x53657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'show_details', 'en', 0x73686f772064657461696c73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no', 'en', 0x4e6f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_yes', 'en', 0x596573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_taxon', 'en', 0x5461786f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_id', 'en', 0x4964);
INSERT INTO `lng_data` VALUES ('meta', 'meta_source', 'en', 0x536f75726365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_taxon_path', 'en', 0x5461786f6e2050617468);
INSERT INTO `lng_data` VALUES ('meta', 'meta_competency', 'en', 0x436f6d706574656e6379);
INSERT INTO `lng_data` VALUES ('meta', 'meta_security_level', 'en', 0x5365637572697479204c6576656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_skill_level', 'en', 0x536b696c6c204c6576656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_educational_level', 'en', 0x456475636174696f6e616c204c6576656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_accessibility_restrictions', 'en', 0x4163636573736962696c697479205265737472696374696f6e73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_educational_objective', 'en', 0x456475636174696f6e616c204f626a656374697665);
INSERT INTO `lng_data` VALUES ('meta', 'meta_prerequisite', 'en', 0x507265726571756973697465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_idea', 'en', 0x49646561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_purpose', 'en', 0x507572706f7365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_classification', 'en', 0x436c617373696669636174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_classification', 'en', 0x4e6f206d657461206461746120617661696c61626c6520666f722074686520436c617373696669636174696f6e2073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_annotation', 'en', 0x416e6e6f746174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_annotation', 'en', 0x4e6f206d657461206461746120617661696c61626c6520666f722074686520416e6e6f746174696f6e2073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_resource', 'en', 0x5265736f75726365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_required_by', 'en', 0x4973205265717569726564204279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_requires', 'en', 0x5265717569726573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_basis_for', 'en', 0x497320426173697320466f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_based_on', 'en', 0x4973204261736564204f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_referenced_by', 'en', 0x4973205265666572656e636564204279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_references', 'en', 0x5265666572656e636573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_has_format', 'en', 0x48617320466f726d6174);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_format_of', 'en', 0x497320466f726d6174204f66);
INSERT INTO `lng_data` VALUES ('meta', 'meta_has_version', 'en', 0x4861732056657273696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_version_of', 'en', 0x49732056657273696f6e204f66);
INSERT INTO `lng_data` VALUES ('meta', 'meta_has_part', 'en', 0x4861732050617274);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_part_of', 'en', 0x49732050617274204f66);
INSERT INTO `lng_data` VALUES ('meta', 'meta_kind', 'en', 0x4b696e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_relation', 'en', 0x52656c6174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_relation', 'en', 0x4e6f206d657461206461746120617661696c61626c6520666f72207468652052656c6174696f6e2073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_copyright_and_other_restrictions', 'en', 0x436f7079726967687420616e64204f74686572205265737472696374696f6e73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_cost', 'en', 0x436f7374);
INSERT INTO `lng_data` VALUES ('meta', 'meta_rights', 'en', 0x526967687473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_rights', 'en', 0x4e6f206d657461206461746120617661696c61626c6520666f7220746865205269676874732073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_difficult', 'en', 0x5665727920446966666963756c74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_dificult', 'en', 0x446966666963756c74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_easy', 'en', 0x45617379);
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_easy', 'en', 0x566572792045617379);
INSERT INTO `lng_data` VALUES ('meta', 'meta_other', 'en', 0x4f74686572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_training', 'en', 0x547261696e696e67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_higher_education', 'en', 0x48696768657220456475636174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_school', 'en', 0x5363686f6f6c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_manager', 'en', 0x4d616e61676572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_learner', 'en', 0x4c6561726e6572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_author', 'en', 0x417574686f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_teacher', 'en', 0x54656163686572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_high', 'en', 0x566572792048696768);
INSERT INTO `lng_data` VALUES ('meta', 'meta_high', 'en', 0x48696768);
INSERT INTO `lng_data` VALUES ('meta', 'meta_medium', 'en', 0x4d656469756d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_low', 'en', 0x4c6f77);
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_low', 'en', 0x56657279204c6f77);
INSERT INTO `lng_data` VALUES ('meta', 'meta_lecture', 'en', 0x4c656374757265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_self_assessment', 'en', 0x53656c66204173736573736d656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_problem_statement', 'en', 0x50726f626c656d2053746174656d656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_experiment', 'en', 0x4578706572696d656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_exam', 'en', 0x4578616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_narrative_text', 'en', 0x4e61727261746976652054657874);
INSERT INTO `lng_data` VALUES ('meta', 'meta_table', 'en', 0x5461626c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_slide', 'en', 0x536c696465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_index', 'en', 0x496e646578);
INSERT INTO `lng_data` VALUES ('meta', 'meta_graph', 'en', 0x4772617068);
INSERT INTO `lng_data` VALUES ('meta', 'meta_figure', 'en', 0x466967757265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_diagramm', 'en', 0x4469616772616d6d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_questionnaire', 'en', 0x5175657374696f6e6e61697265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_simulation', 'en', 0x53696d756c6174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_exercise', 'en', 0x4578657263697365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_mixed', 'en', 0x4d69786564);
INSERT INTO `lng_data` VALUES ('meta', 'meta_expositive', 'en', 0x4578706f736974697665);
INSERT INTO `lng_data` VALUES ('meta', 'meta_active', 'en', 0x416374697665);
INSERT INTO `lng_data` VALUES ('meta', 'meta_typical_learning_time', 'en', 0x5479706963616c204c6561726e696e672054696d65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_typical_age_range', 'en', 0x5479706963616c204167652052616e6765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_difficulty', 'en', 0x446966666963756c7479);
INSERT INTO `lng_data` VALUES ('meta', 'meta_context', 'en', 0x436f6e74657874);
INSERT INTO `lng_data` VALUES ('meta', 'meta_intended_end_user_role', 'en', 0x496e74656e64656420456e64205573657220526f6c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_semantic_density', 'en', 0x53656d616e7469632044656e73697479);
INSERT INTO `lng_data` VALUES ('meta', 'meta_interactivity_level', 'en', 0x496e7465726163746976697479204c6576656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_learning_resource_type', 'en', 0x4c6561726e696e67205265736f757263652054797065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_interactivity_type', 'en', 0x496e74657261637469766974792054797065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_education', 'en', 0x456475636174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_educational', 'en', 0x4e6f206d657461206461746120617661696c61626c6520666f7220456475636174696f6e616c2073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_none', 'en', 0x4e6f6e65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_multi_os', 'en', 0x4d756c74692d4f53);
INSERT INTO `lng_data` VALUES ('meta', 'meta_unix', 'en', 0x556e6978);
INSERT INTO `lng_data` VALUES ('meta', 'meta_mac_os', 'en', 0x4d61634f53);
INSERT INTO `lng_data` VALUES ('meta', 'meta_ms_windows', 'en', 0x4d532d57696e646f7773);
INSERT INTO `lng_data` VALUES ('meta', 'meta_pc_dos', 'en', 0x50432d444f53);
INSERT INTO `lng_data` VALUES ('meta', 'meta_browser', 'en', 0x42726f77736572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_maximum_version', 'en', 0x4d6178696d756d2056657273696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_minimum_version', 'en', 0x4d696e696d756d2056657273696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_name', 'en', 0x4e616d65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_operating_system', 'en', 0x4f7065726174696e672053797374656d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_reference', 'en', 0x5265666572656e6365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_local_file', 'en', 0x4c6f63616c2046696c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_type', 'en', 0x54797065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_duration', 'en', 0x4475726174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_other_plattform_requirements', 'en', 0x4f7468657220506c617474666f726d20526571756972656d656e7473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_installation_remarks', 'en', 0x496e7374616c6c6174696f6e2052656d61726b73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_or_composite', 'en', 0x4f7220436f6d706f73697465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_requirement', 'en', 0x526571756972656d656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_location', 'en', 0x4c6f636174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_size', 'en', 0x53697a65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_format', 'en', 0x466f726d6174);
INSERT INTO `lng_data` VALUES ('meta', 'meta_technical', 'en', 0x546563686e6963616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_technical', 'en', 0x4e6f206d657461206461746120617661696c61626c6520666f7220546563686e6963616c2073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_metadatascheme', 'en', 0x4d6574616461746120536368656d65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_meta_metadata', 'en', 0x4d6574612d4d65746164617461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_meta_metadata', 'en', 0x4e6f206d657461206461746120617661696c61626c6520666f72204d6574612d4d657461646174612073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_date', 'en', 0x44617465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_entity', 'en', 0x456e74697479);
INSERT INTO `lng_data` VALUES ('meta', 'meta_publisher', 'en', 0x5075626c6973686572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_role', 'en', 0x526f6c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_version', 'en', 0x56657273696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_unavailable', 'en', 0x556e617661696c61626c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_revised', 'en', 0x52657669736564);
INSERT INTO `lng_data` VALUES ('meta', 'meta_final', 'en', 0x46696e616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_draft', 'en', 0x4472616674);
INSERT INTO `lng_data` VALUES ('meta', 'meta_status', 'en', 0x537461747573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_contribute', 'en', 0x436f6e74726962757465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_lifecycle', 'en', 0x4c6966656379636c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_lifecycle', 'en', 0x4e6f206d657461206461746120617661696c61626c6520666f72204c6966656379636c652073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_coverage', 'en', 0x436f766572616765);
INSERT INTO `lng_data` VALUES ('search', 'search_user', 'en', 0x5573657273);
INSERT INTO `lng_data` VALUES ('search', 'search_show_result', 'en', 0x53686f77);
INSERT INTO `lng_data` VALUES ('search', 'search_search_term', 'en', 0x536561726368207465726d);
INSERT INTO `lng_data` VALUES ('search', 'search_search_for', 'en', 0x53656172636820666f72);
INSERT INTO `lng_data` VALUES ('search', 'search_no_search_term', 'en', 0x596f7520686176656e27742073656c656374656420616e7920736561726368207465726d);
INSERT INTO `lng_data` VALUES ('search', 'search_no_match', 'en', 0x596f757220736561726368206469646e2774206d6174636820616e7920726573756c7473);
INSERT INTO `lng_data` VALUES ('search', 'search_no_category', 'en', 0x596f7520686176656e27742073656c656374656420616e79207365617263682063617465676f7279);
INSERT INTO `lng_data` VALUES ('search', 'search_minimum_three', 'en', 0x596f7572207365617263682068617320746f206265206174206c656173742074687265652063686172616374657273206c6f6e67);
INSERT INTO `lng_data` VALUES ('search', 'search_meta', 'en', 0x4d6574612064617461);
INSERT INTO `lng_data` VALUES ('search', 'search_lm_meta', 'en', 0x4c6561726e696e67206d6174657269616c7320286d657461206461746129);
INSERT INTO `lng_data` VALUES ('search', 'search_lm_content', 'en', 0x4c6561726e696e67206d6174657269616c732028636f6e74656e7429);
INSERT INTO `lng_data` VALUES ('search', 'search_in_result', 'en', 0x5365617263682077697468696e20726573756c7473);
INSERT INTO `lng_data` VALUES ('search', 'search_dbk_meta', 'en', 0x4469676974616c204c69627261727920286d657461206461746129);
INSERT INTO `lng_data` VALUES ('search', 'search_dbk_content', 'en', 0x4469676974616c204c6962726172792028636f6e74656e7429);
INSERT INTO `lng_data` VALUES ('search', 'search_content', 'en', 0x5061676520436f6e74656e74);
INSERT INTO `lng_data` VALUES ('search', 'search_concatenation', 'en', 0x436f6e636174656e6174696f6e);
INSERT INTO `lng_data` VALUES ('search', 'search_all_results', 'en', 0x416c6c20726573756c7473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_value', 'en', 0x56616c7565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_title', 'en', 0x5469746c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_structure', 'en', 0x537472756374757265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_please_select', 'en', 0x506c656173652073656c656374);
INSERT INTO `lng_data` VALUES ('meta', 'meta_new_element', 'en', 0x4e657720456c656d656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_networked', 'en', 0x4e6574776f726b6564);
INSERT INTO `lng_data` VALUES ('meta', 'meta_linear', 'en', 0x4c696e656172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_language', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_zu', 'en', 0x7a756c75);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_zh', 'en', 0x6368696e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_za', 'en', 0x7a6875616e67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_yo', 'en', 0x796f72756261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_yi', 'en', 0x79696464697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_xh', 'en', 0x78686f7361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_wo', 'en', 0x776f6c6f66);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_vo', 'en', 0x766f6c6170756b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_vi', 'en', 0x766965746e616d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_uz', 'en', 0x757a62656b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ur', 'en', 0x75726475);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_uk', 'en', 0x756b7261696e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ug', 'en', 0x7569677572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tw', 'en', 0x747769);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tt', 'en', 0x7461746172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ts', 'en', 0x74736f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tr', 'en', 0x7475726b697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_to', 'en', 0x746f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tn', 'en', 0x7365747377616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tl', 'en', 0x746167616c6f67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tk', 'en', 0x7475726b6d656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ti', 'en', 0x74696772696e7961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_th', 'en', 0x74686169);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tg', 'en', 0x74616a696b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_te', 'en', 0x74656c756775);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ta', 'en', 0x74616d696c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sw', 'en', 0x73776168696c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sv', 'en', 0x73776564697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_su', 'en', 0x73756e64616e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_st', 'en', 0x7365736f74686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ss', 'en', 0x73697377617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sr', 'en', 0x7365726269616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sq', 'en', 0x616c62616e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_so', 'en', 0x736f6d616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sn', 'en', 0x73686f6e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sm', 'en', 0x73616d6f616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sl', 'en', 0x736c6f76656e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sk', 'en', 0x736c6f76616b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_si', 'en', 0x73696e6768616c657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sg', 'en', 0x73616e67686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sh', 'en', 0x736572626f2d63726f617469616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sa', 'en', 0x73616e736b726974);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sd', 'en', 0x73696e646869);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rw', 'en', 0x6b696e79617277616e6461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ro', 'en', 0x726f6d616e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ru', 'en', 0x7275737369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rn', 'en', 0x6b7572756e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_qu', 'en', 0x71756563687561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rm', 'en', 0x72686165746f2d726f6d616e6365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pt', 'en', 0x706f7274756775657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ps', 'en', 0x70617368746f3b70757368746f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_or', 'en', 0x6f72697961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pa', 'en', 0x70756e6a616269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pl', 'en', 0x706f6c697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_oc', 'en', 0x6f63636974616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_om', 'en', 0x6166616e20286f726f6d6f29);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_no', 'en', 0x6e6f7277656769616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_nl', 'en', 0x6475746368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_na', 'en', 0x6e61757275);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ne', 'en', 0x6e6570616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_my', 'en', 0x6275726d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mt', 'en', 0x6d616c74657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mr', 'en', 0x6d617261746869);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ms', 'en', 0x6d616c6179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mn', 'en', 0x6d6f6e676f6c69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mo', 'en', 0x6d6f6c64617669616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ml', 'en', 0x6d616c6179616c616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mk', 'en', 0x6d616365646f6e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mi', 'en', 0x6d616f7269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mg', 'en', 0x6d616c6167617379);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lv', 'en', 0x6c61747669616e3b6c657474697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lo', 'en', 0x6c616f746869616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lt', 'en', 0x6c69746875616e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_la', 'en', 0x6c6174696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ln', 'en', 0x6c696e67616c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ku', 'en', 0x6b757264697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ky', 'en', 0x6b69726768697a);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ko', 'en', 0x6b6f7265616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ks', 'en', 0x6b6173686d697269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_km', 'en', 0x63616d626f6469616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kn', 'en', 0x6b616e6e616461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ka', 'en', 0x67656f726769616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kk', 'en', 0x6b617a616b68);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kl', 'en', 0x677265656e6c616e646963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ja', 'en', 0x6a6170616e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_jv', 'en', 0x6a6176616e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_it', 'en', 0x6974616c69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_iu', 'en', 0x696e756b7469747574);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ik', 'en', 0x696e757069616b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_is', 'en', 0x6963656c616e646963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_id', 'en', 0x696e646f6e657369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ie', 'en', 0x696e7465726c696e677565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hr', 'en', 0x63726f617469616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hu', 'en', 0x68756e67617269616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hy', 'en', 0x61726d656e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ia', 'en', 0x696e7465726c696e677561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_he', 'en', 0x686562726577);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hi', 'en', 0x68696e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ha', 'en', 0x6861757361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gn', 'en', 0x67756172616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gu', 'en', 0x67756a6172617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gd', 'en', 0x73636f7473206761656c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gl', 'en', 0x67616c696369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fy', 'en', 0x6672697369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ga', 'en', 0x6972697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fo', 'en', 0x6661726f657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fr', 'en', 0x6672656e6368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fi', 'en', 0x66696e6e697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fj', 'en', 0x66696a69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_eu', 'en', 0x626173717565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fa', 'en', 0x7065727369616e2028666172736929);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_et', 'en', 0x6573746f6e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_eo', 'en', 0x6573706572616e746f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_es', 'en', 0x7370616e697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_el', 'en', 0x677265656b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_en', 'en', 0x656e676c697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_dz', 'en', 0x62687574616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_de', 'en', 0x6765726d616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_da', 'en', 0x64616e697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_cy', 'en', 0x77656c7368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_cs', 'en', 0x637a656368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_co', 'en', 0x636f72736963616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ca', 'en', 0x636174616c616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_br', 'en', 0x627265746f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bn', 'en', 0x62656e67616c693b62616e676c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bo', 'en', 0x7469626574616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bh', 'en', 0x626968617269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bi', 'en', 0x6269736c616d61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bg', 'en', 0x62756c67617269616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_be', 'en', 0x6279656c6f7275737369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ba', 'en', 0x626173686b6972);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_az', 'en', 0x617a65726261696a616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ay', 'en', 0x61796d617261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_as', 'en', 0x617373616d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ar', 'en', 0x617261626963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_am', 'en', 0x616d6861726963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_af', 'en', 0x616672696b61616e73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ab', 'en', 0x61626b68617a69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_aa', 'en', 0x61666172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_keyword', 'en', 0x4b6579776f7264);
INSERT INTO `lng_data` VALUES ('meta', 'meta_identifier', 'en', 0x4964656e746966696572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_hierarchical', 'en', 0x48696572617263686963616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_general', 'en', 0x47656e6572616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_entry', 'en', 0x456e747279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_description', 'en', 0x4465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_delete', 'en', 0x44656c657465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_collection', 'en', 0x436f6c6c656374696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_choose_section', 'en', 0x506c656173652063686f6f736520612073656374696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_choose_language', 'en', 0x506c656173652063686f6f73652061206c616e6775616765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_choose_element', 'en', 0x506c656173652063686f6f736520616e20656c656d656e7420796f752077616e7420746f2061646421);
INSERT INTO `lng_data` VALUES ('meta', 'meta_catalog', 'en', 0x436174616c6f67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZZ', 'en', 0x4f7468657220436f756e747279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZW', 'en', 0x5a696d6261627765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZR', 'en', 0x5a61697265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZM', 'en', 0x5a616d626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZA', 'en', 0x536f75746820416672696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_YT', 'en', 0x4d61796f747465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_YE', 'en', 0x59656d656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_WS', 'en', 0x53616d6f61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_WF', 'en', 0x57616c6c697320416e6420467574756e612049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VU', 'en', 0x56616e75617475);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VN', 'en', 0x56696574204e616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VI', 'en', 0x56697267696e2049736c616e64732028555329);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VG', 'en', 0x56697267696e2049736c616e647320284272697469736829);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VE', 'en', 0x56656e657a75656c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VC', 'en', 0x5361696e742056696e63656e7420416e6420546865204772656e6164696e6573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VA', 'en', 0x5661746963616e2043697479205374617465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_US', 'en', 0x552e532e41);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UY', 'en', 0x55727567756179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UZ', 'en', 0x557a62656b697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UM', 'en', 0x552e532e204d696e6f72204f75746c79696e672049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UG', 'en', 0x5567616e6461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UA', 'en', 0x556b7261696e65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TZ', 'en', 0x54616e7a616e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TW', 'en', 0x54616977616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TV', 'en', 0x547576616c75);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TT', 'en', 0x5472696e6964616420416e6420546f6261676f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TR', 'en', 0x5475726b6579);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TP', 'en', 0x456173742054696d6f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TO', 'en', 0x546f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TN', 'en', 0x54756e69736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TM', 'en', 0x5475726b6d656e697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TK', 'en', 0x546f6b656c6175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TJ', 'en', 0x54616a696b697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TH', 'en', 0x546861696c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TG', 'en', 0x546f676f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TF', 'en', 0x4672656e636820536f75746865726e205465727269746f72696573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TD', 'en', 0x43686164);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TC', 'en', 0x5475726b7320416e6420436169636f732049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SZ', 'en', 0x5377617a696c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SY', 'en', 0x53797269616e20417261622052657075626c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SV', 'en', 0x456c2053616c7661646f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ST', 'en', 0x53616f20546f6d6520416e64205072696e63697065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SR', 'en', 0x537572696e616d65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SO', 'en', 0x536f6d616c6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SM', 'en', 0x53616e204d6172696e6f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SN', 'en', 0x53656e6567616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SK', 'en', 0x536c6f76616b6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SL', 'en', 0x53696561727261204c656f6e65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SJ', 'en', 0x5376616c6261726420416e64204a616e204d6179656e2049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SI', 'en', 0x536c6f76656e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SG', 'en', 0x53696e6761706f7265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SH', 'en', 0x53742e2048656c656e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SE', 'en', 0x53776564656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SD', 'en', 0x537564616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SC', 'en', 0x5365796368656c6c6573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SB', 'en', 0x536f6c6f6d6f6e2049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SA', 'en', 0x536175646920417261626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RW', 'en', 0x5277616e6461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RU', 'en', 0x52616e2046656465726174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RO', 'en', 0x526f6d616e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RE', 'en', 0x5265756e696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_QA', 'en', 0x5161746172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PY', 'en', 0x5061726167756179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PW', 'en', 0x50616c6175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PT', 'en', 0x506f72747567616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PR', 'en', 0x50756572746f205269636f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PN', 'en', 0x506974636169726e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PM', 'en', 0x53742e2050696572726520416e64204d697175656c6f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PL', 'en', 0x506f6c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PK', 'en', 0x50616b697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PH', 'en', 0x5068696c697070696e6573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PG', 'en', 0x5061707561204e6577204775696e6561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PF', 'en', 0x4672656e636820506f6c796e65736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PE', 'en', 0x50657275);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PA', 'en', 0x50616e616d61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_OM', 'en', 0x4f6d616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NZ', 'en', 0x4e6577205a65616c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NU', 'en', 0x4e697565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NR', 'en', 0x4e61757275);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NP', 'en', 0x4e6570616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NL', 'en', 0x4e65746865726c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NO', 'en', 0x4e6f72776179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NI', 'en', 0x4e6963617261677561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NG', 'en', 0x4e696765726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NE', 'en', 0x4e69676572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NF', 'en', 0x4e6f72666f6c6b2049736c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NC', 'en', 0x4e65772043616c65646f6e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NA', 'en', 0x4e616d69626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MZ', 'en', 0x4d6f7a616d6269717565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MY', 'en', 0x4d616c6179736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MX', 'en', 0x4d657869636f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MW', 'en', 0x4d616c617769);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MV', 'en', 0x4d616c6469766573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MU', 'en', 0x4d6175726974697573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MT', 'en', 0x4d616c7461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MS', 'en', 0x4d6f6e74736572726174);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MR', 'en', 0x4d6175726974616e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MQ', 'en', 0x4d617274696e69717565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MP', 'en', 0x4e6f72746865726e204d617269616e612049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MO', 'en', 0x4d61636175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MN', 'en', 0x4d6f6e676f6c6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MM', 'en', 0x4d79616e6d6172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ML', 'en', 0x4d616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MK', 'en', 0x4d616365646f6e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MH', 'en', 0x4d61727368616c6c2049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MG', 'en', 0x4d616461676173636172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MD', 'en', 0x4d6f6c646f7661);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MC', 'en', 0x4d6f6e61636f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MA', 'en', 0x4d6f726f63636f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LY', 'en', 0x4c696279616e2041726162204a616d61686972697961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LV', 'en', 0x4c6174766961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LU', 'en', 0x4c7578656d626f757267);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LT', 'en', 0x4c697468756e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LS', 'en', 0x4c65736f74686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LR', 'en', 0x4c696265726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LK', 'en', 0x537269204c616e6b61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LI', 'en', 0x4c6965636874656e737465696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LC', 'en', 0x5361696e74204c75636961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LB', 'en', 0x4c6562616e6f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LA', 'en', 0x4c616f2050656f706c6527732052657075626c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KZ', 'en', 0x4b617a616b687374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KY', 'en', 0x4361796d616e2049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KW', 'en', 0x4b7577616974);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KR', 'en', 0x536f757468204b6f726561202852657075626c6963204f66204b6f72656129);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KP', 'en', 0x4e6f727468204b6f726561202850656f706c6527732052657075626c6963204f66204b6f72656129);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KN', 'en', 0x5361696e74204b6974747320416e64204e65766973);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KM', 'en', 0x436f6d6f726f73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KI', 'en', 0x4b69726962617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KH', 'en', 0x43616d626f646961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KG', 'en', 0x4b797267797a7374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KE', 'en', 0x4b656e7961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JP', 'en', 0x4a6170616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JO', 'en', 0x4a6f7264616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JM', 'en', 0x4a616d61696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IT', 'en', 0x4974616c79);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IS', 'en', 0x4963656c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IR', 'en', 0x4972616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IQ', 'en', 0x49726171);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IO', 'en', 0x4272697469736820496e6469616e204f6365616e205465727269746f7279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IN', 'en', 0x496e646961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IL', 'en', 0x49737261656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IE', 'en', 0x4972656c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ID', 'en', 0x496e646f6e65736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HU', 'en', 0x48756e67617279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HT', 'en', 0x4861697469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HR', 'en', 0x43726f61746961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HN', 'en', 0x486f6e6475726173);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HM', 'en', 0x486561726420416e64204e6320446f6e616c642049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GY', 'en', 0x477579616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GU', 'en', 0x4775616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GW', 'en', 0x4775696e65612d426973736175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GT', 'en', 0x47756174656d616c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GS', 'en', 0x536f7574682047656f7267696120416e642054686520536f7574682053616e64776963682049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GR', 'en', 0x477265656365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GQ', 'en', 0x45717561746f7269616c204775696e6561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GP', 'en', 0x47756164656c6f757065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GN', 'en', 0x4775696e6561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GM', 'en', 0x47616d626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GL', 'en', 0x477265656e6c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GI', 'en', 0x47696272616c746172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GH', 'en', 0x4768616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GF', 'en', 0x4672656e636820477569616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GE', 'en', 0x47696f72676961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GD', 'en', 0x4772656e616461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GB', 'en', 0x556e69746564204b696e67646f6d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GA', 'en', 0x4761626f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FX', 'en', 0x4672616e63652c204d6574726f706f6c6974616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FR', 'en', 0x4672616e6365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FO', 'en', 0x4661726f652049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FM', 'en', 0x4d6963726f6e65736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FK', 'en', 0x46616c6b6c616e642049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FJ', 'en', 0x46696a69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FI', 'en', 0x46696e6c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ET', 'en', 0x457468696f706961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ES', 'en', 0x537061696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ER', 'en', 0x45726974726561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EH', 'en', 0x5765737465726e20536168617261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EG', 'en', 0x4567797074);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EE', 'en', 0x4573746f6e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EC', 'en', 0x45637561646f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DZ', 'en', 0x416c6765726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DO', 'en', 0x446f6d696e6963616e2052657075626c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DM', 'en', 0x446f6d696e696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DK', 'en', 0x44656e6d61726b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DJ', 'en', 0x446a69626f757469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DE', 'en', 0x4765726d616e79);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CZ', 'en', 0x437a6563682052657075626c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CY', 'en', 0x437970727573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CX', 'en', 0x4368726973746d61732049736c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CV', 'en', 0x43617065205665726465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CU', 'en', 0x43756261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CR', 'en', 0x436f7374612052696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CO', 'en', 0x436f6c6f6d626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CN', 'en', 0x4368696e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CM', 'en', 0x43616d65726f6f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CL', 'en', 0x4368696c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CK', 'en', 0x436f6f6b2049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CI', 'en', 0x436f74652044272049766f697265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CH', 'en', 0x537769747a65726c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CG', 'en', 0x436f6e676f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CF', 'en', 0x43656e7472616c204166726963616e2052657075626c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CC', 'en', 0x436f636f7320284b65656c696e67292049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CA', 'en', 0x43616e616461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BZ', 'en', 0x42656c697a65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BY', 'en', 0x42656c61727573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BW', 'en', 0x426f747377616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BV', 'en', 0x426f757665742049736c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BT', 'en', 0x42687574616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BS', 'en', 0x426168616d6173);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BR', 'en', 0x4272617a696c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BO', 'en', 0x426f6c69766961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BN', 'en', 0x4272756e656920446172757373616c616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BM', 'en', 0x4265726d756461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BJ', 'en', 0x42656e696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BI', 'en', 0x427572756e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BH', 'en', 0x4261687261696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BG', 'en', 0x42756c6761726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BF', 'en', 0x4275726b696e61204661736f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BE', 'en', 0x42656c6769756d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BD', 'en', 0x42616e676c6164657368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BB', 'en', 0x4261726261646f73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BA', 'en', 0x426f736e696120416e64204865727a65676f77696e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AW', 'en', 0x4172756261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AZ', 'en', 0x417a65726261696a616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AT', 'en', 0x41757374726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AU', 'en', 0x4175737472616c6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AS', 'en', 0x416d65726963616e2053616d6f61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AR', 'en', 0x417267656e74696e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AQ', 'en', 0x416e7461726374696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AO', 'en', 0x416e676f6c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AN', 'en', 0x4e65746865726c616e647320416e74696c6c6573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AM', 'en', 0x41726d656e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AL', 'en', 0x416c62616e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AI', 'en', 0x416e6775696c6c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AG', 'en', 0x416e746967756120416e642042617262756461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AF', 'en', 0x41666768616e697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AE', 'en', 0x556e69746564204172616220456d697261746573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AD', 'en', 0x416e646f727261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_atomic', 'en', 0x41746f6d6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_add', 'en', 0x416464);
INSERT INTO `lng_data` VALUES ('mail', 'search_cc_recipient', 'en', 0x53656172636820434320526563697069656e74);
INSERT INTO `lng_data` VALUES ('mail', 'search_bc_recipient', 'en', 0x53656172636820424320526563697069656e74);
INSERT INTO `lng_data` VALUES ('mail', 'mail_user_addr_n_valid', 'en', 0x466f6c6c77696e672075736572732068617665206e6f2076616c696420656d61696c2061646472657373);
INSERT INTO `lng_data` VALUES ('mail', 'mail_to_search', 'en', 0x546f20736561726368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_folder', 'en', 0x54686520666f6c64657220616e642069747320636f6e74656e742077696c6c2062652072656d6f766564207065726d616e656e746c79);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_file', 'en', 0x5468652066696c652077696c6c2062652072656d6f766564207065726d616e656e746c79);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_entry', 'en', 0x417265207375726520796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e6720656e7472696573);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete', 'en', 0x41726520796f7520737572652c20746f2064656c65746520746865206d61726b6564206d61696c287329);
INSERT INTO `lng_data` VALUES ('mail', 'mail_select_one_file', 'en', 0x596f75206861766520746f2073656c656374206f6e652066696c65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_select_one_entry', 'en', 0x596f75206861766520746f2073656c656374206f6e6520656e747279);
INSERT INTO `lng_data` VALUES ('mail', 'mail_search_system', 'en', 0x73656172636820696e2073797374656d);
INSERT INTO `lng_data` VALUES ('mail', 'mail_search_addressbook', 'en', 0x73656172636820696e2061646472657373626f6f6b);
INSERT INTO `lng_data` VALUES ('mail', 'mail_saved', 'en', 0x546865206d657373616765206973207361766564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_s_unread', 'en', 0x556e72656164204d61696c287329);
INSERT INTO `lng_data` VALUES ('mail', 'mail_s', 'en', 0x4d61696c287329);
INSERT INTO `lng_data` VALUES ('mail', 'mail_recipient_not_valid', 'en', 0x54686520726563697069656e74206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('common', 'set_online', 'en', 0x536574204f6e6c696e65);
INSERT INTO `lng_data` VALUES ('common', 'set_offline', 'en', 0x536574204f66666c696e65);
INSERT INTO `lng_data` VALUES ('common', 'setUserLanguage', 'en', 0x5365742055736572204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'setSystemLanguage', 'en', 0x5365742053797374656d204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'set', 'en', 0x536574);
INSERT INTO `lng_data` VALUES ('common', 'server_software', 'en', 0x53657276657220536f667477617265);
INSERT INTO `lng_data` VALUES ('common', 'server', 'en', 0x536572766572);
INSERT INTO `lng_data` VALUES ('common', 'sequences', 'en', 0x53657175656e636573);
INSERT INTO `lng_data` VALUES ('common', 'sequence', 'en', 0x53657175656e6365);
INSERT INTO `lng_data` VALUES ('common', 'sent', 'en', 0x53656e74);
INSERT INTO `lng_data` VALUES ('common', 'sender', 'en', 0x53656e646572);
INSERT INTO `lng_data` VALUES ('common', 'send', 'en', 0x53656e64);
INSERT INTO `lng_data` VALUES ('common', 'selected', 'en', 0x53656c6563746564);
INSERT INTO `lng_data` VALUES ('common', 'select_mode', 'en', 0x53656c656374206d6f6465);
INSERT INTO `lng_data` VALUES ('common', 'select_file', 'en', 0x53656c6563742066696c65);
INSERT INTO `lng_data` VALUES ('common', 'select_all', 'en', 0x53656c65637420416c6c);
INSERT INTO `lng_data` VALUES ('common', 'select', 'en', 0x53656c656374);
INSERT INTO `lng_data` VALUES ('common', 'sections', 'en', 0x53656374696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'search_user', 'en', 0x5365617263682055736572);
INSERT INTO `lng_data` VALUES ('common', 'search_result', 'en', 0x53656172636820726573756c74);
INSERT INTO `lng_data` VALUES ('common', 'search_recipient', 'en', 0x53656172636820526563697069656e74);
INSERT INTO `lng_data` VALUES ('common', 'search_new', 'en', 0x4e657720536561726368);
INSERT INTO `lng_data` VALUES ('common', 'search_in', 'en', 0x53656172636820696e);
INSERT INTO `lng_data` VALUES ('common', 'search', 'en', 0x536561726368);
INSERT INTO `lng_data` VALUES ('common', 'saved_successfully', 'en', 0x5361766564205375636365737366756c6c79);
INSERT INTO `lng_data` VALUES ('common', 'saved', 'en', 0x5361766564);
INSERT INTO `lng_data` VALUES ('common', 'save_return', 'en', 0x5361766520616e642052657475726e);
INSERT INTO `lng_data` VALUES ('common', 'save_refresh', 'en', 0x5361766520616e642052656672657368);
INSERT INTO `lng_data` VALUES ('common', 'save_message', 'en', 0x53617665204d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'save_and_back', 'en', 0x5361766520416e64204261636b);
INSERT INTO `lng_data` VALUES ('common', 'save', 'en', 0x53617665);
INSERT INTO `lng_data` VALUES ('common', 'salutation_m', 'en', 0x4d722e);
INSERT INTO `lng_data` VALUES ('common', 'salutation_f', 'en', 0x4d732e2f4d72732e);
INSERT INTO `lng_data` VALUES ('common', 'salutation', 'en', 0x53616c75746174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'rolt_added', 'en', 0x526f6c652074656d706c617465206164646564);
INSERT INTO `lng_data` VALUES ('common', 'rolt', 'en', 0x526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'rolf_added', 'en', 0x526f6c6520466f6c646572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'rolf', 'en', 0x526f6c6520466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'roles', 'en', 0x526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'role_deleted', 'en', 0x526f6c652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'role_assignment', 'en', 0x526f6c652041737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'role_added', 'en', 0x526f6c65206164646564);
INSERT INTO `lng_data` VALUES ('common', 'role_add_local', 'en', 0x416464206c6f63616c20526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role', 'en', 0x526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'rights', 'en', 0x526967687473);
INSERT INTO `lng_data` VALUES ('common', 'right', 'en', 0x5269676874);
INSERT INTO `lng_data` VALUES ('common', 'retype_password', 'en', 0x5265747970652050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'reset', 'en', 0x5265736574);
INSERT INTO `lng_data` VALUES ('common', 'required_field', 'en', 0x5265717569726564204669656c64);
INSERT INTO `lng_data` VALUES ('common', 'reply', 'en', 0x5265706c79);
INSERT INTO `lng_data` VALUES ('common', 'rename', 'en', 0x52656e616d65);
INSERT INTO `lng_data` VALUES ('common', 'remove_translation', 'en', 0x52656d6f7665207472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'registration', 'en', 0x4e6577206163636f756e7420726567697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'registered_since', 'en', 0x526567697374657265642073696e6365);
INSERT INTO `lng_data` VALUES ('common', 'register_info', 'en', 0x506c656173652066696c6c206f75742074686520666f726d20746f20726567697374657220284669656c6473206d61726b6564207769746820616e206173746572696b732061726520726571756972656420696e666f726d6174696f6e292e);
INSERT INTO `lng_data` VALUES ('common', 'register', 'en', 0x5265676973746572);
INSERT INTO `lng_data` VALUES ('common', 'refresh_list', 'en', 0x52656672657368204c697374);
INSERT INTO `lng_data` VALUES ('common', 'refresh_languages', 'en', 0x52656672657368204c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'refresh', 'en', 0x52656672657368);
INSERT INTO `lng_data` VALUES ('common', 'recipient', 'en', 0x526563697069656e74);
INSERT INTO `lng_data` VALUES ('common', 'read', 'en', 0x52656164);
INSERT INTO `lng_data` VALUES ('common', 'quote', 'en', 0x51756f7465);
INSERT INTO `lng_data` VALUES ('common', 'quit', 'en', 0x51756974);
INSERT INTO `lng_data` VALUES ('common', 'question', 'en', 0x5175657374696f6e);
INSERT INTO `lng_data` VALUES ('common', 'publishing_organisation', 'en', 0x5075626c697368696e67204f7267616e69736174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'published', 'en', 0x5075626c6973686564);
INSERT INTO `lng_data` VALUES ('common', 'publication_date', 'en', 0x5075626c69636174696f6e2044617465);
INSERT INTO `lng_data` VALUES ('common', 'publication', 'en', 0x5075626c69636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'public_profile', 'en', 0x5075626c69632050726f66696c65);
INSERT INTO `lng_data` VALUES ('common', 'pub_section', 'en', 0x5075626c69632053656374696f6e);
INSERT INTO `lng_data` VALUES ('common', 'properties', 'en', 0x50726f70657274696573);
INSERT INTO `lng_data` VALUES ('common', 'profile_changed', 'en', 0x596f75722070726f66696c6520686173206368616e676564);
INSERT INTO `lng_data` VALUES ('common', 'print', 'en', 0x5072696e74);
INSERT INTO `lng_data` VALUES ('common', 'previous', 'en', 0x70726576696f7573);
INSERT INTO `lng_data` VALUES ('common', 'presentation_options', 'en', 0x50726573656e746174696f6e204f7074696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'position', 'en', 0x506f736974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'port', 'en', 0x506f7274);
INSERT INTO `lng_data` VALUES ('common', 'please_enter_title', 'en', 0x506c6561736520656e7465722061207469746c65);
INSERT INTO `lng_data` VALUES ('common', 'please_enter_target', 'en', 0x506c6561736520656e746572206120746172676574);
INSERT INTO `lng_data` VALUES ('common', 'phrase', 'en', 0x506872617365);
INSERT INTO `lng_data` VALUES ('common', 'phone', 'en', 0x50686f6e65);
INSERT INTO `lng_data` VALUES ('common', 'pg_new', 'en', 0x4e65772070616765);
INSERT INTO `lng_data` VALUES ('common', 'pg_edit', 'en', 0x456469742070616765);
INSERT INTO `lng_data` VALUES ('common', 'pg_added', 'en', 0x50616765206164646564);
INSERT INTO `lng_data` VALUES ('common', 'pg_add', 'en', 0x4164642070616765);
INSERT INTO `lng_data` VALUES ('common', 'pg_a', 'en', 0x612070616765);
INSERT INTO `lng_data` VALUES ('common', 'persons', 'en', 0x506572736f6e656e);
INSERT INTO `lng_data` VALUES ('common', 'personal_profile', 'en', 0x506572736f6e616c2050726f66696c65);
INSERT INTO `lng_data` VALUES ('common', 'personal_picture', 'en', 0x506572736f6e616c2050696374757265);
INSERT INTO `lng_data` VALUES ('common', 'personal_desktop', 'en', 0x506572736f6e616c204465736b746f70);
INSERT INTO `lng_data` VALUES ('common', 'personal_data', 'en', 0x506572736f6e616c20696e666f726d6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'permission_denied', 'en', 0x5065726d697373696f6e2044656e696564);
INSERT INTO `lng_data` VALUES ('common', 'permission', 'en', 0x5065726d697373696f6e);
INSERT INTO `lng_data` VALUES ('common', 'perm_settings', 'en', 0x5065726d697373696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'payment_system', 'en', 0x5061796d656e742053797374656d);
INSERT INTO `lng_data` VALUES ('common', 'pathes', 'en', 0x506174686573);
INSERT INTO `lng_data` VALUES ('common', 'path_to_zip', 'en', 0x5061746820746f205a6970);
INSERT INTO `lng_data` VALUES ('common', 'path_to_unzip', 'en', 0x5061746820746f20556e7a6970);
INSERT INTO `lng_data` VALUES ('common', 'path_to_java', 'en', 0x5061746820746f204a617661);
INSERT INTO `lng_data` VALUES ('common', 'path_to_convert', 'en', 0x5061746820746f20436f6e76657274);
INSERT INTO `lng_data` VALUES ('common', 'path_to_babylon', 'en', 0x5061746820746f20426162796c6f6e);
INSERT INTO `lng_data` VALUES ('common', 'path', 'en', 0x50617468);
INSERT INTO `lng_data` VALUES ('common', 'pastePage', 'en', 0x5061737465);
INSERT INTO `lng_data` VALUES ('common', 'pasteChapter', 'en', 0x5061737465);
INSERT INTO `lng_data` VALUES ('common', 'paste', 'en', 0x5061737465);
INSERT INTO `lng_data` VALUES ('common', 'password', 'en', 0x50617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'passwd_wrong', 'en', 0x5468652070617373776f726420796f7520656e74657265642069732077726f6e6721);
INSERT INTO `lng_data` VALUES ('common', 'passwd_not_match', 'en', 0x596f757220656e747269657320666f7220746865206e65772070617373776f726420646f6e2774206d617463682120506c656173652072652d656e74657220796f7572206e65772070617373776f72642e);
INSERT INTO `lng_data` VALUES ('common', 'passwd_invalid', 'en', 0x546865206e65772070617373776f726420697320696e76616c69642120506c656173652063686f6f736520616e6f746865722070617373776f72642e);
INSERT INTO `lng_data` VALUES ('common', 'passwd', 'en', 0x50617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'parse', 'en', 0x5061727365);
INSERT INTO `lng_data` VALUES ('common', 'parameter', 'en', 0x506172616d65746572);
INSERT INTO `lng_data` VALUES ('common', 'pages', 'en', 0x5061676573);
INSERT INTO `lng_data` VALUES ('common', 'page_edit', 'en', 0x456469742050616765);
INSERT INTO `lng_data` VALUES ('common', 'page', 'en', 0x50616765);
INSERT INTO `lng_data` VALUES ('common', 'owner', 'en', 0x4f776e6572);
INSERT INTO `lng_data` VALUES ('common', 'overview', 'en', 0x4f76657276696577);
INSERT INTO `lng_data` VALUES ('common', 'other', 'en', 0x4f74686572);
INSERT INTO `lng_data` VALUES ('common', 'order_by', 'en', 0x4f72646572206279);
INSERT INTO `lng_data` VALUES ('common', 'options', 'en', 0x4f7074696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'optimize', 'en', 0x4f7074696d697a65);
INSERT INTO `lng_data` VALUES ('common', 'operation', 'en', 0x4f7065726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'online_version', 'en', 0x4f6e6c696e652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'online_chapter', 'en', 0x4f6e6c696e652043686170746572);
INSERT INTO `lng_data` VALUES ('common', 'old', 'en', 0x4f6c64);
INSERT INTO `lng_data` VALUES ('common', 'ok', 'en', 0x4f4b);
INSERT INTO `lng_data` VALUES ('common', 'offline_version', 'en', 0x4f66666c696e652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'of', 'en', 0x4f66);
INSERT INTO `lng_data` VALUES ('common', 'objf', 'en', 0x4f626a65637420466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'objects', 'en', 0x4f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'obj_usrf', 'en', 0x55736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'obj_usr', 'en', 0x55736572);
INSERT INTO `lng_data` VALUES ('common', 'obj_uset', 'en', 0x557365722053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj_type', 'en', 0x4f626a6563742054797065);
INSERT INTO `lng_data` VALUES ('common', 'obj_typ', 'en', 0x4f626a6563747479706520446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_st', 'en', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('common', 'obj_slm', 'en', 0x53434f524d204c4d);
INSERT INTO `lng_data` VALUES ('common', 'obj_root', 'en', 0x726f6f74);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolt', 'en', 0x526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf_local_desc', 'en', 0x436f6e7461696e73206c6f63616c20726f6c6573206f66206f626a656374206e6f2e);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf_local', 'en', 0x4c6f63616c20526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf', 'en', 0x526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'obj_role', 'en', 0x526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_pg', 'en', 0x50616765);
INSERT INTO `lng_data` VALUES ('common', 'obj_owner', 'en', 0x54686973204f626a656374206973206f776e6564206279);
INSERT INTO `lng_data` VALUES ('common', 'obj_objf', 'en', 0x4f626a656374646566696e6974696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'obj_notf', 'en', 0x4e6f74652041646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_note', 'en', 0x4e6f7465);
INSERT INTO `lng_data` VALUES ('common', 'obj_not_found', 'en', 0x4f626a656374204e6f7420466f756e64);
INSERT INTO `lng_data` VALUES ('common', 'obj_mail', 'en', 0x4d61696c2053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj_lo', 'en', 0x4c6561726e696e674f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'obj_lngf', 'en', 0x4c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'obj_lng', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'obj_lm', 'en', 0x4c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_le', 'en', 0x4c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_grp', 'en', 0x47726f7570);
INSERT INTO `lng_data` VALUES ('common', 'obj_file', 'en', 0x46696c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_frm', 'en', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'obj_glo', 'en', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'obj_cat', 'en', 0x43617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'obj_crs', 'en', 0x436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'obj_adm', 'en', 0x53797374656d2053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj', 'en', 0x4f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'not_logged_in', 'en', 0x596f757220617265206e6f74206c6f6767656420696e);
INSERT INTO `lng_data` VALUES ('common', 'not_installed', 'en', 0x4e6f7420496e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'normal', 'en', 0x4e6f726d616c);
INSERT INTO `lng_data` VALUES ('common', 'none', 'en', 0x4e6f6e65);
INSERT INTO `lng_data` VALUES ('common', 'no_objects', 'en', 0x4e6f206f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'no_title', 'en', 0x4e6f205469746c65);
INSERT INTO `lng_data` VALUES ('common', 'no_lo_in_personal_list', 'en', 0x4e6f206c6561726e696e67206f626a6563747320696e20706572736f6e616c206c6973742e);
INSERT INTO `lng_data` VALUES ('common', 'no_import_available', 'en', 0x496d706f7274206e6f7420617661696c61626c6520666f722074797065);
INSERT INTO `lng_data` VALUES ('common', 'no_frm_in_personal_list', 'en', 0x4e6f20666f72756d7320696e20706572736f6e616c206c6973742e);
INSERT INTO `lng_data` VALUES ('common', 'no_date', 'en', 0x4e6f2064617465);
INSERT INTO `lng_data` VALUES ('common', 'no', 'en', 0x4e6f);
INSERT INTO `lng_data` VALUES ('common', 'no_checkbox', 'en', 0x4e6f20636865636b626f7820636865636b656421);
INSERT INTO `lng_data` VALUES ('common', 'next', 'en', 0x6e657874);
INSERT INTO `lng_data` VALUES ('common', 'nickname', 'en', 0x4e69636b6e616d65);
INSERT INTO `lng_data` VALUES ('common', 'news', 'en', 0x4e657773);
INSERT INTO `lng_data` VALUES ('common', 'new_mail', 'en', 0x4e6577206d61696c21);
INSERT INTO `lng_data` VALUES ('common', 'new_language', 'en', 0x4e6577204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'new_group', 'en', 0x4e65772047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'new_folder', 'en', 0x4e657720466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'new', 'en', 0x4e6577);
INSERT INTO `lng_data` VALUES ('common', 'new_appointment', 'en', 0x4e6577204170706f696e746d656e74);
INSERT INTO `lng_data` VALUES ('common', 'my_los', 'en', 0x4d79204c6561726e696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'name', 'en', 0x4e616d65);
INSERT INTO `lng_data` VALUES ('common', 'my_frms', 'en', 0x4d7920466f72756d73);
INSERT INTO `lng_data` VALUES ('common', 'multimedia', 'en', 0x4d756c74696d65646961);
INSERT INTO `lng_data` VALUES ('common', 'msg_userassignment_changed', 'en', 0x5573657261737369676d656e74206368616e676564);
INSERT INTO `lng_data` VALUES ('common', 'msg_user_last_role2', 'en', 0x506c656173652064656c65746520746865207573657273206f722061737369676e207468656d20746f20616e6f7468657220726f6c6520696e206f7264657220746f2064656c657465207468697320726f6c652e);
INSERT INTO `lng_data` VALUES ('common', 'msg_user_last_role1', 'en', 0x54686520666f6c6c6f77696e67207573657273206172652061737369676e656420746f207468697320726f6c65206f6e6c793a);
INSERT INTO `lng_data` VALUES ('common', 'msg_undeleted', 'en', 0x4f626a65637428732920756e64656c657465642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_trash_empty', 'en', 0x546865726520617265206e6f2064656c65746564206f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'msg_sysrole_not_editable', 'en', 0x546865207065726d697373696f6e2073657474696e6773206f66207468652073797374656d20726f6c65206d6179206e6f74206265206368616e6765642e205468652073797374656d20726f6c65206772616e747320616c6c2061737369676e656420757365727320756e6c696d697465642061636365737320746f20616c6c206f626a6563747320262066756e6374696f6e732e);
INSERT INTO `lng_data` VALUES ('common', 'msg_sysrole_not_deletable', 'en', 0x5468652073797374656d726f6c652063616e6e6f742062652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_roleassignment_changed', 'en', 0x526f6c6561737369676d656e74206368616e676564);
INSERT INTO `lng_data` VALUES ('common', 'msg_role_reserved_prefix', 'en', 0x546865207072656669782027696c5f2720697320726573657276656420666f72206175746f67656e65726174656420726f6c65732e20506c656173652063686f73736520616e6f74686572206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'msg_role_exists2', 'en', 0x616c7265616479206578697374732120506c656173652063686f6f736520616e6f74686572206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'msg_role_exists1', 'en', 0x4120726f6c652f726f6c652074656d706c617465207769746820746865206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'msg_removed', 'en', 0x4f626a6563742873292072656d6f7665642066726f6d2073797374656d2e);
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from_itself', 'en', 0x596f752063616e6e6f742061646f7074207065726d697373696f6e2073657474696e67732066726f6d207468652063757272656e7420726f6c652f726f6c652074656d706c61746520697473656c662e);
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from2', 'en', 0x2853657474696e67732068617665206265656e2073617665642129);
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from1', 'en', 0x5065726d697373696f6e2073657474696e67732061646f707465642066726f6d);
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_modified', 'en', 0x4d6f64696669636174696f6e732073617665642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_exists', 'en', 0x54686973206f626a65637420616c72656164792065786973747320696e207468697320666f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_created', 'en', 0x4f626a65637420637265617465642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_nothing_found', 'en', 0x6d73675f6e6f7468696e675f666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'msg_not_possible_link', 'en', 0x49742773206e6f7420706f737369626c6520746f206372656174652061206c696e6b206f6620746865206f626a6563743a2054797065);
INSERT INTO `lng_data` VALUES ('common', 'msg_not_in_itself', 'en', 0x49742773206e6f7420706f737369626c6520746f20706173746520746865206f626a65637420696e20697473656c66);
INSERT INTO `lng_data` VALUES ('common', 'msg_not_available_for_anon', 'en', 0x546865207061676520796f7520686176652063686f73656e206973206f6e6c792061636365737369626c6520666f722072656769737465726564207573657273);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_search_string', 'en', 0x506c6561736520656e74657220796f7572207175657279);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_search_result', 'en', 0x4e6f20656e747269657320666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_rolf_allowed2', 'en', 0x6973206e6f7420616c6c6f77656420746f20636f6e7461696e206120526f6c6520466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_rolf_allowed1', 'en', 0x546865206f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_write', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f207772697465);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_perm', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2065646974207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_paste', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2070617374652074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_user', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206d6f646966792075736572206461746173);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_rolt', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206d6f6469667920726f6c652074656d706c61746573);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_role', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206d6f6469667920726f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_link', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206372656174652061206c696e6b2066726f6d2074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_delete', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2064656c6574652074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_cut', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206375742074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_user', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f20616464207573657273);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_rolt', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2061646420726f6c652074656d706c61746573);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_rolf', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f20637265617465206120526f6c6520466f6c6465722e205468657265666f726520796f75206d6179206e6f742073746f7020696e6865726974616e6365206f6620726f6c6573206f7220616464206c6f63616c20726f6c657320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_role', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2061646420726f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_object2', 'en', 0x61742074686973206c6f636174696f6e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_object1', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f20637265617465);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206372656174652074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_copy', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f20637265617465206120636f7079206f662074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_language_selected', 'en', 0x4e6f207472616e736c6174696f6e206c616e6775616765207370656369666965642120596f75206d75737420646566696e652061206c616e677561676520666f722065616368207472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_delete_yourself', 'en', 0x596f752063616e6e6f742064656c65746520796f7572206f776e2075736572206163636f756e742e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_default_language', 'en', 0x4e6f2064656661756c74206c616e6775616765207370656369666965642120596f75206d75737420646566696e65206f6e65207472616e736c6174696f6e2061732064656661756c74207472616e736c6174696f6e2e);
INSERT INTO `lng_data` VALUES ('common', 'msg_multi_language_selected', 'en', 0x596f752073656c6563746564207468652073616d65206c616e677561676520666f7220646966666572656e74207472616e736c6174696f6e7321);
INSERT INTO `lng_data` VALUES ('common', 'msg_min_one_role', 'en', 0x456163682075736572206d7573742068617665206174206c65617374206f6e6520726f6c6521);
INSERT INTO `lng_data` VALUES ('common', 'msg_min_one_active_role', 'en', 0x456163682075736572206d7573742068617665206174206c65617374206f6e652061637469766520726f6c6521);
INSERT INTO `lng_data` VALUES ('common', 'msg_may_not_contain', 'en', 0x54686973206f626a656374206d6179206e6f7420636f6e7461696e206f626a65637473206f6620747970653a);
INSERT INTO `lng_data` VALUES ('common', 'msg_linked', 'en', 0x53656c6563746564206f626a656374287329206c696e6b65642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_link_clipboard', 'en', 0x53656c6563746564206f626a6563742873292073746f72656420696e20636c6970626f6172642028416374696f6e3a206c696e6b29);
INSERT INTO `lng_data` VALUES ('common', 'msg_is_last_role', 'en', 0x596f7520646561737369676e65642074686520666f6c6c6f77696e672075736572732066726f6d207468656972206c61737420726f6c65);
INSERT INTO `lng_data` VALUES ('common', 'msg_failed', 'en', 0x536f7272792c20616374696f6e206661696c6564);
INSERT INTO `lng_data` VALUES ('common', 'msg_error_copy', 'en', 0x436f7079204572726f72);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_rolts', 'en', 0x526f6c652054656d706c6174652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_rolt', 'en', 0x526f6c652054656d706c6174652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_roles_rolts', 'en', 0x526f6c6573202620526f6c652054656d706c617465732064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_roles', 'en', 0x526f6c65732064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_role', 'en', 0x526f6c652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_cut_copied', 'en', 0x53656c6563746564206f626a656374287329206d6f7665642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_cut_clipboard', 'en', 0x53656c6563746564206f626a6563742873292073746f72656420696e20636c6970626f6172642028416374696f6e3a2063757429);
INSERT INTO `lng_data` VALUES ('common', 'msg_copy_clipboard', 'en', 0x53656c6563746564206f626a6563742873292073746f72656420696e20636c6970626f6172642028416374696f6e3a20636f707929);
INSERT INTO `lng_data` VALUES ('common', 'msg_cloned', 'en', 0x53656c6563746564206f626a65637428732920636f70696564);
INSERT INTO `lng_data` VALUES ('common', 'msg_clear_clipboard', 'en', 0x436c6970626f61726420636c6561726564);
INSERT INTO `lng_data` VALUES ('common', 'msg_changes_ok', 'en', 0x546865206368616e6765732077657265204f4b);
INSERT INTO `lng_data` VALUES ('common', 'move_to', 'en', 0x4d6f766520746f);
INSERT INTO `lng_data` VALUES ('common', 'msg_cancel', 'en', 0x416374696f6e2063616e63656c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'movePage', 'en', 0x4d6f7665);
INSERT INTO `lng_data` VALUES ('common', 'moveChapter', 'en', 0x4d6f7665);
INSERT INTO `lng_data` VALUES ('common', 'month', 'en', 0x4d6f6e7468);
INSERT INTO `lng_data` VALUES ('common', 'modules', 'en', 0x4d6f64756c6573);
INSERT INTO `lng_data` VALUES ('common', 'module', 'en', 0x6d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'mob', 'en', 0x4d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'migrate', 'en', 0x4d696772617465);
INSERT INTO `lng_data` VALUES ('common', 'meta_data', 'en', 0x4d6574612044617461);
INSERT INTO `lng_data` VALUES ('common', 'message_to', 'en', 0x4d65737361676520746f3a);
INSERT INTO `lng_data` VALUES ('common', 'message_content', 'en', 0x4d65737361676520436f6e74656e74);
INSERT INTO `lng_data` VALUES ('common', 'message', 'en', 0x4d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'members', 'en', 0x4d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'mark_all_unread', 'en', 0x4d61726b20416c6c20617320556e72656164);
INSERT INTO `lng_data` VALUES ('common', 'mark_all_read', 'en', 0x4d61726b20416c6c2061732052656164);
INSERT INTO `lng_data` VALUES ('common', 'mails', 'en', 0x4d61696c73);
INSERT INTO `lng_data` VALUES ('common', 'mail_z_local', 'en', 0x5573657220466f6c64657273);
INSERT INTO `lng_data` VALUES ('common', 'mail_system', 'en', 0x53797374656d204d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'mail_sent', 'en', 0x4d61696c207375636365737366756c6c792073656e7421);
INSERT INTO `lng_data` VALUES ('common', 'mail_send_error', 'en', 0x4572726f722073656e64696e67206d61696c);
INSERT INTO `lng_data` VALUES ('common', 'mail_select_one', 'en', 0x596f75206861766520746f2073656c656374206f6e65206d61696c);
INSERT INTO `lng_data` VALUES ('common', 'mail_search_word', 'en', 0x53656172636820776f7264);
INSERT INTO `lng_data` VALUES ('common', 'mail_search_no', 'en', 0x4e6f20656e747269657320666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'mail_not_sent', 'en', 0x4d61696c206e6f742073656e7421);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxtime_mail', 'en', 0x4d61782e206461797320746f206b656570206d61696c73);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxtime_attach', 'en', 0x4d61782e206461797320746f206b656570206174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_mail', 'en', 0x4d61782e206d61696c2073697a65);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_box', 'en', 0x4d61782e206d61696c626f782073697a65);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_attach', 'en', 0x4d61782e206174746163686d656e742073697a65);
INSERT INTO `lng_data` VALUES ('common', 'mail_mails_of', 'en', 0x4d61696c73);
INSERT INTO `lng_data` VALUES ('common', 'mail_intern_enable', 'en', 0x456e61626c65);
INSERT INTO `lng_data` VALUES ('common', 'mail_e_sent', 'en', 0x53656e74);
INSERT INTO `lng_data` VALUES ('common', 'mail_folders', 'en', 0x4d61696c20466f6c64657273);
INSERT INTO `lng_data` VALUES ('common', 'mail_delete_error_file', 'en', 0x4572726f722064656c6574696e672066696c65);
INSERT INTO `lng_data` VALUES ('common', 'mail_delete_error', 'en', 0x4572726f72207768696c652064656c6574696e67);
INSERT INTO `lng_data` VALUES ('common', 'mail_d_drafts', 'en', 0x447261667473);
INSERT INTO `lng_data` VALUES ('common', 'mail_c_trash', 'en', 0x5472617368);
INSERT INTO `lng_data` VALUES ('common', 'mail_b_inbox', 'en', 0x496e626f78);
INSERT INTO `lng_data` VALUES ('common', 'mail_allow_smtp', 'en', 0x416c6c6f7720534d5450);
INSERT INTO `lng_data` VALUES ('common', 'mail_addressbook', 'en', 0x41646472657373626f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'mail_a_root', 'en', 0x4d61696c626f78);
INSERT INTO `lng_data` VALUES ('common', 'mail', 'en', 0x4d61696c);
INSERT INTO `lng_data` VALUES ('common', 'los_last_visited', 'en', 0x4c6173742056697369746564204c6561726e696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'los', 'en', 0x4c6561726e696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'logout_text', 'en', 0x596f75206c6f67676564206f66662066726f6d20494c4941532e20596f75722073657373696f6e20686173206265656e20636c6f7365642e);
INSERT INTO `lng_data` VALUES ('common', 'logout', 'en', 0x4c6f676f7574);
INSERT INTO `lng_data` VALUES ('common', 'login_to_ilias', 'en', 0x4c6f67696e20746f20494c494153);
INSERT INTO `lng_data` VALUES ('common', 'login_time', 'en', 0x43757272656e74206f6e6c696e652074696d65);
INSERT INTO `lng_data` VALUES ('common', 'login_exists', 'en', 0x546865726520697320616c72656164792061207573657220776974682074686973206c6f67696e6e616d652120506c656173652063686f6f736520616e6f74686572206f6e65);
INSERT INTO `lng_data` VALUES ('common', 'login_data', 'en', 0x4c6f67696e2064617461);
INSERT INTO `lng_data` VALUES ('common', 'login', 'en', 0x4c6f67696e);
INSERT INTO `lng_data` VALUES ('common', 'locator', 'en', 0x4c6f6361746f723a);
INSERT INTO `lng_data` VALUES ('common', 'lo_overview', 'en', 0x4c4f204f76657276696577);
INSERT INTO `lng_data` VALUES ('common', 'lo_other_langs', 'en', 0x4c4f277320696e204f74686572204c616e676175676573);
INSERT INTO `lng_data` VALUES ('common', 'lo_no_content', 'en', 0x4e6f204c6561726e696e6720526573736f757263657320417661696c61626c65);
INSERT INTO `lng_data` VALUES ('common', 'lo_new', 'en', 0x4e6577204c6561726e696e67204f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'lo_edit', 'en', 0x45646974204c6561726e696e67204f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'lo_categories', 'en', 0x4c4f2043617465676f72696573);
INSERT INTO `lng_data` VALUES ('common', 'lo_available', 'en', 0x4f76657276696577204c6561726e696e67204d6f64756c6573202620436f7572736573);
INSERT INTO `lng_data` VALUES ('common', 'lo', 'en', 0x4c6561726e696e67204f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'lngf', 'en', 0x4c616e677561676520466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'lng', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'lm_new', 'en', 0x4e6577204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_added', 'en', 0x4c6561726e696e674d6f64756c65206164646564);
INSERT INTO `lng_data` VALUES ('common', 'lm_add', 'en', 0x416464204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_a', 'en', 0x61204c6561726e696e67206d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm', 'en', 0x4c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'literature_bookmarks', 'en', 0x4c69746572617475726520426f6f6b6d61726b73);
INSERT INTO `lng_data` VALUES ('common', 'literature', 'en', 0x4c697465726174757265);
INSERT INTO `lng_data` VALUES ('common', 'list_of_questions', 'en', 0x5175657374696f6e204c697374);
INSERT INTO `lng_data` VALUES ('common', 'list_of_pages', 'en', 0x5061676573204c697374);
INSERT INTO `lng_data` VALUES ('common', 'linked_pages', 'en', 0x4c696e6b6564205061676573);
INSERT INTO `lng_data` VALUES ('common', 'link', 'en', 0x4c696e6b);
INSERT INTO `lng_data` VALUES ('common', 'level', 'en', 0x4c6576656c);
INSERT INTO `lng_data` VALUES ('common', 'learning_objects', 'en', 0x4c6561726e696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'ldap', 'en', 0x4c444150);
INSERT INTO `lng_data` VALUES ('common', 'launch', 'en', 0x4c61756e6368);
INSERT INTO `lng_data` VALUES ('common', 'lastname', 'en', 0x4c6173746e616d65);
INSERT INTO `lng_data` VALUES ('common', 'last_visit', 'en', 0x4c617374205669736974);
INSERT INTO `lng_data` VALUES ('common', 'last_change', 'en', 0x4c617374204368616e6765);
INSERT INTO `lng_data` VALUES ('common', 'languages_updated', 'en', 0x416c6c20696e7374616c6c6564206c616e6775616765732068617665206265656e2075706461746564);
INSERT INTO `lng_data` VALUES ('common', 'languages_already_uninstalled', 'en', 0x43686f73656e206c616e67756167652873292061726520616c726561647920756e696e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'languages_already_installed', 'en', 0x43686f73656e206c616e67756167652873292061726520616c726561647920696e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'languages', 'en', 0x4c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'language_not_installed', 'en', 0x6973206e6f7420696e7374616c6c65642e20506c6561736520696e7374616c6c2074686174206c616e6775616765206669727374);
INSERT INTO `lng_data` VALUES ('common', 'language', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'langfile_found', 'en', 0x4c616e67756167652066696c6520666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'lang_xx', 'en', 0x437573746f6d);
INSERT INTO `lng_data` VALUES ('common', 'lang_version', 'en', 0x31);
INSERT INTO `lng_data` VALUES ('common', 'lang_timeformat', 'en', 0x483a693a73);
INSERT INTO `lng_data` VALUES ('common', 'lang_sep_thousand', 'en', 0x2c);
INSERT INTO `lng_data` VALUES ('common', 'lang_sep_decimal', 'en', 0x2e);
INSERT INTO `lng_data` VALUES ('common', 'lang_se', 'en', 0x53776564697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_pl', 'en', 0x506f6c697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_path', 'en', 0x4c616e67756167652050617468);
INSERT INTO `lng_data` VALUES ('common', 'lang_no', 'en', 0x4e6f7277656769736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_it', 'en', 0x4974616c69616e);
INSERT INTO `lng_data` VALUES ('common', 'lang_id', 'en', 0x496e646f6e657369616e);
INSERT INTO `lng_data` VALUES ('common', 'lang_fr', 'en', 0x4672656e6368);
INSERT INTO `lng_data` VALUES ('common', 'lang_es', 'en', 0x5370616e697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_en', 'en', 0x456e676c697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_dk', 'en', 0x44616e697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_de', 'en', 0x4765726d616e);
INSERT INTO `lng_data` VALUES ('common', 'lang_dateformat', 'en', 0x592d6d2d64);
INSERT INTO `lng_data` VALUES ('common', 'keywords', 'en', 0x4b6579776f726473);
INSERT INTO `lng_data` VALUES ('common', 'item', 'en', 0x4974656d);
INSERT INTO `lng_data` VALUES ('common', 'kb', 'en', 0x4b42797465);
INSERT INTO `lng_data` VALUES ('common', 'ip_address', 'en', 0x49502041646472657373);
INSERT INTO `lng_data` VALUES ('common', 'is_already_your', 'en', 0x697320616c726561647920796f7572);
INSERT INTO `lng_data` VALUES ('common', 'internal_system', 'en', 0x496e7465726e616c2073797374656d);
INSERT INTO `lng_data` VALUES ('common', 'institution', 'en', 0x496e737469747574696f6e);
INSERT INTO `lng_data` VALUES ('common', 'installed', 'en', 0x496e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'install', 'en', 0x496e7374616c6c);
INSERT INTO `lng_data` VALUES ('common', 'inst_name', 'en', 0x496e7374616c6c6174696f6e204e616d65);
INSERT INTO `lng_data` VALUES ('common', 'inst_info', 'en', 0x496e7374616c6c6174696f6e20496e666f);
INSERT INTO `lng_data` VALUES ('common', 'inst_id', 'en', 0x496e7374616c6c6174696f6e204944);
INSERT INTO `lng_data` VALUES ('common', 'insert', 'en', 0x496e73657274);
INSERT INTO `lng_data` VALUES ('common', 'input_error', 'en', 0x496e707574206572726f72);
INSERT INTO `lng_data` VALUES ('common', 'inifile', 'en', 0x496e692d46696c65);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_welcome', 'en', 0x2c2077656c636f6d6520746f20796f757220706572736f6e616c206465736b746f702121);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_visited_le', 'en', 0x416c6c204c4520796f752068617665207669736974656420736f206661722e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_vis_le', 'en', 0x56697369746564204c45);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_title', 'en', 0x4368616e6765207573657220646174612e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_info3', 'en', 0x506c656173652073656c656374206f6e65206f6620746865206c696e6b7320696e207468652073696465206261722e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_info2', 'en', 0x4974206973206e6f7420616c6c6f77656420746f20757365207370656369616c206368617261637465727320286c696b65202c203b202e203d202d202a202b20232920696e20796f757220494e474d4544494120757365726e616d6520616e642070617373776f72642e20546f20626520616c6c6f77656420746f207573652074686520494e474d4544494120706c6174666f726d2c20796f75206861766520746f206163636570742074686520757365722061677265656d656e742e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_info1', 'en', 0x4865726520796f752063616e206368616e676520796f757220706572736f6e616c20646174612e2045786365707420666f722074686520494e474d4544494120757365726e616d6520616e642070617373776f72642c20796f7520646f206e6f74206861766520746f2066696c6c206f7574206f74686572206669656c64732e2052656d656d6265722074686174206c6f73742070617373776f7264732063616e206f6e6c792062652072657475726e656420627920652d6d61696c202821292e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_user_agree', 'en', 0x41677265656d656e74);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_reg_le', 'en', 0x52656769737465726564204c45);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_reg_fo', 'en', 0x5265676973746572656420666f72756d73);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_pers_dates', 'en', 0x506572736f6e616c2064617461);
INSERT INTO `lng_data` VALUES ('mail', 'mail_options_of', 'en', 0x4f7074696f6e73);
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_attachments_found', 'en', 0x4e6f206174746163686d656e747320666f756e64);
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_attach_allowed', 'en', 0x53797374656d206d6573736167657320617265206e6f7420616c6c6f77656420746f20636f6e7461696e206174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('mail', 'mail_new_file', 'en', 0x416464206e65772066696c653a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_moved_to_trash', 'en', 0x4d61696c2873292068617665206265656e206d6f76656420746f207472617368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_moved', 'en', 0x546865206d61696c2873292068617665206265656e206d6f766564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_move_to', 'en', 0x4d6f766520746f3a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_move_error', 'en', 0x4572726f72206d6f76696e67206d61696c287329);
INSERT INTO `lng_data` VALUES ('mail', 'mail_message_send', 'en', 0x546865206d6573736167652069732073656e64);
INSERT INTO `lng_data` VALUES ('mail', 'mail_mark_unread', 'en', 0x4d61726b206d61696c7320756e72656164);
INSERT INTO `lng_data` VALUES ('mail', 'mail_mark_read', 'en', 0x4d61726b206d61696c732072656164);
INSERT INTO `lng_data` VALUES ('mail', 'mail_intern', 'en', 0x496e7465726e616c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_insert_query', 'en', 0x506c6561736520696e736572742061207175657279);
INSERT INTO `lng_data` VALUES ('mail', 'mail_insert_folder_name', 'en', 0x506c6561736520696e73657274206120666f6c646572206e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_global_options', 'en', 0x476c6f62616c206f7074696f6e73);
INSERT INTO `lng_data` VALUES ('mail', 'mail_following_rcp_not_valid', 'en', 0x54686520666f6c6c6f77696e6720726563697069656e747320617265206e6f742076616c69643a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_options', 'en', 0x466f6c646572204f7074696f6e73);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_name_changed', 'en', 0x54686520666f6c6465722069732072656e616d6564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_name', 'en', 0x466f6c646572206e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_exists', 'en', 0x4120666f6c64657220616c72656164792065786973747320776974682074686973206e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_deleted', 'en', 0x54686520666f6c64657220686173206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_created', 'en', 0x41206e657720666f6c6465722069732063726561746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_files_deleted', 'en', 0x5468652066696c65287329206172652064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_file_size', 'en', 0x46696c6573697a65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_file_name', 'en', 0x46696c656e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_entry_changed', 'en', 0x54686520656e747279206973206368616e676564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_entry_added', 'en', 0x4164646564206e657720456e747279);
INSERT INTO `lng_data` VALUES ('mail', 'mail_email_forbidden', 'en', 0x596f7520617265206e6f7420616c6c6f77656420746f2073656e6420656d61696c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_deleted_entry', 'en', 0x54686520656e7472696573206172652064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_deleted', 'en', 0x546865206d61696c2873292068617665206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_compose', 'en', 0x436f6d706f7365204d657373616765);
INSERT INTO `lng_data` VALUES ('mail', 'mail_check_your_email_addr', 'en', 0x596f757220656d61696c2061646472657373206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_cc_search', 'en', 0x434320736561726368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_change_to_folder', 'en', 0x53776974636820746f20666f6c6465723a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_cc_not_valid', 'en', 0x54686520436320726563697069656e74206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_bc_search', 'en', 0x424320736561726368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_attachments', 'en', 0x4174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('mail', 'mail_bc_not_valid', 'en', 0x54686520426320726563697069656e74206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_addr_entries', 'en', 0x456e7472696573);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_type', 'en', 0x506c6561736520616464207468652074797065206f66206d61696c20796f752077616e7420746f2073656e64);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_to_addressbook', 'en', 0x41646420746f2061646472657373626f6f6b);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_subject', 'en', 0x506c6561736520656e7465722061207375626a656374);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_subfolder', 'en', 0x41646420537562666f6c646572);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_recipient', 'en', 0x506c6561736520656e746572206120726563697069656e74);
INSERT INTO `lng_data` VALUES ('mail', 'linebreak', 'en', 0x4c696e65627265616b);
INSERT INTO `lng_data` VALUES ('mail', 'forward', 'en', 0x466f7277617264);
INSERT INTO `lng_data` VALUES ('mail', 'bc', 'en', 0x4243);
INSERT INTO `lng_data` VALUES ('mail', 'also_as_email', 'en', 0x416c736f20617320456d61696c);
INSERT INTO `lng_data` VALUES ('forum', 'forums_your_reply', 'en', 0x596f7572205265706c79);
INSERT INTO `lng_data` VALUES ('forum', 'forums_topics_overview', 'en', 0x546f70696373204f76657276696577);
INSERT INTO `lng_data` VALUES ('forum', 'forums_threads_not_available', 'en', 0x546f70696373204e6f7420417661696c61626c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_threads', 'en', 0x54687265616473);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_new_entry', 'en', 0x4e657720746f70696320686173206265656e20696e73637269626564);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_create_from', 'en', 0x437265617465642066726f6d);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_create_date', 'en', 0x43726561746564206174);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_articles', 'en', 0x41727469636c657320746f2074686520746f706963);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread', 'en', 0x546f706963);
INSERT INTO `lng_data` VALUES ('forum', 'forums_the_post', 'en', 0x41727469636c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_respond', 'en', 0x506f7374205265706c79);
INSERT INTO `lng_data` VALUES ('forum', 'forums_quote', 'en', 0x51756f7465);
INSERT INTO `lng_data` VALUES ('forum', 'forums_posts_not_available', 'en', 0x41727469636c6573204e6f7420417661696c61626c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_posts', 'en', 0x416c6c2061727469636c6573);
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_new_entry', 'en', 0x4e65772061727469636c6520686173206265656e20696e73637269626564);
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_modified', 'en', 0x41727469636c6520686173206265656e206d6f646966696564);
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_deleted', 'en', 0x41727469636c6520686173206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('forum', 'forums_overview', 'en', 0x466f72756d73204f76657276696577);
INSERT INTO `lng_data` VALUES ('forum', 'forums_not_available', 'en', 0x466f72756d73204e6f7420417661696c61626c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_new_thread', 'en', 0x4e657720546f706963);
INSERT INTO `lng_data` VALUES ('forum', 'forums_new_entries', 'en', 0x4e657720466f72756d7320456e7472696573);
INSERT INTO `lng_data` VALUES ('forum', 'forums_moderators', 'en', 0x4d6f64657261746f7273);
INSERT INTO `lng_data` VALUES ('forum', 'forums_last_post', 'en', 0x4c6173742041727469636c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_delete_post', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c65746520746869732061727469636c6520696e636c7564696e6720616e7920726573706f6e7365733f);
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_censor_post', 'en', 0x41726520796f75207375726520796f752077616e7420746f206869646520746869732061727469636c653f);
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_censor2_post', 'en', 0x41726520796f75207375726520796f752077616e7420746f20636f6e74696e756520746f206869646520746869732061727469636c653f);
INSERT INTO `lng_data` VALUES ('forum', 'forums_edit_post', 'en', 0x456469742041727469636c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_count_thr', 'en', 0x4e756d626572206f662054687265616473);
INSERT INTO `lng_data` VALUES ('forum', 'forums_count_art', 'en', 0x4e756d626572206f662041727469636c6573);
INSERT INTO `lng_data` VALUES ('forum', 'forums_count', 'en', 0x4e756d626572206f6620466f72756d73);
INSERT INTO `lng_data` VALUES ('forum', 'forums_available', 'en', 0x417661696c61626c6520466f72756d73);
INSERT INTO `lng_data` VALUES ('forum', 'forums_articles', 'en', 0x41727469636c6573);
INSERT INTO `lng_data` VALUES ('forum', 'forums', 'en', 0x466f72756d73);
INSERT INTO `lng_data` VALUES ('content', 'start export', 'en', 0x5374617274204578706f7274);
INSERT INTO `lng_data` VALUES ('content', 'st', 'en', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('content', 'read offline', 'en', 0x52656164204f66666c696e65);
INSERT INTO `lng_data` VALUES ('content', 'pg', 'en', 0x50616765);
INSERT INTO `lng_data` VALUES ('content', 'par', 'en', 0x506172616772617068);
INSERT INTO `lng_data` VALUES ('content', 'pages from', 'en', 0x50616765732046726f6d);
INSERT INTO `lng_data` VALUES ('content', 'cont_year', 'en', 0x59656172);
INSERT INTO `lng_data` VALUES ('content', 'cont_xml_base', 'en', 0x786d6c3a62617365);
INSERT INTO `lng_data` VALUES ('content', 'cont_wysiwyg', 'en', 0x436f6e74656e742057797369777967);
INSERT INTO `lng_data` VALUES ('content', 'cont_width', 'en', 0x5769647468);
INSERT INTO `lng_data` VALUES ('content', 'cont_where_published', 'en', 0x5768657265207075626c6973686564);
INSERT INTO `lng_data` VALUES ('content', 'cont_version', 'en', 0x76657273696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_url', 'en', 0x55524c);
INSERT INTO `lng_data` VALUES ('content', 'cont_translations_assigned', 'en', 0x546865207472616e736c6174696f6e2873292068617665206265656e2061737369676e6564);
INSERT INTO `lng_data` VALUES ('content', 'cont_translations', 'en', 0x5472616e736c6174696f6e287329);
INSERT INTO `lng_data` VALUES ('content', 'cont_top', 'en', 0x546f70);
INSERT INTO `lng_data` VALUES ('content', 'cont_toc', 'en', 0x5461626c65206f6620436f6e74656e7473);
INSERT INTO `lng_data` VALUES ('content', 'cont_time_limit_action', 'en', 0x61646c63703a74696d656c696d6974616374696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_terms', 'en', 0x5465726d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_term', 'en', 0x5465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_target_within_source', 'en', 0x546172676574206d757374206e6f742062652077697468696e20736f75726365206f626a6563742e);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_width', 'en', 0x5461626c65205769647468);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_cellspacing', 'en', 0x5461626c652043656c6c2053706163696e67);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_cellpadding', 'en', 0x5461626c652043656c6c2050616464696e67);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_border', 'en', 0x5461626c6520426f72646572);
INSERT INTO `lng_data` VALUES ('content', 'cont_table', 'en', 0x5461626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_subchapters', 'en', 0x5375626368617074657273);
INSERT INTO `lng_data` VALUES ('content', 'cont_structure', 'en', 0x737472756374757265);
INSERT INTO `lng_data` VALUES ('content', 'cont_std_view', 'en', 0x5374616e646172642056696577);
INSERT INTO `lng_data` VALUES ('content', 'cont_st_title', 'en', 0x43686170746572205469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_source', 'en', 0x5175656c6c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_show', 'en', 0x53686f77);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_width', 'en', 0x536574205769647468);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_class', 'en', 0x53657420436c617373);
INSERT INTO `lng_data` VALUES ('content', 'cont_series_volume', 'en', 0x53657269657320766f6c756d65);
INSERT INTO `lng_data` VALUES ('content', 'cont_series_title', 'en', 0x536572696573207469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_series_editor', 'en', 0x53657269657320656469746f72);
INSERT INTO `lng_data` VALUES ('content', 'cont_series', 'en', 0x536572696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_translation', 'en', 0x506c656173652073656c656374207468652061737369676e6d656e742066726f6d20746865206c6973742061626f7665);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_term', 'en', 0x53656c6563742061207465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_one_translation', 'en', 0x506c656173652073656c656374206f6e65207472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_max_one_term', 'en', 0x53656c656374206f6e65207465726d206f6e6c79);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_max_one_item', 'en', 0x506c656173652073656c656374206f6e65206974656d206f6e6c79);
INSERT INTO `lng_data` VALUES ('content', 'cont_scorm_type', 'en', 0x61646c63703a73636f726d74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_school', 'en', 0x5363686f6f6c);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_title', 'en', 0x7469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_roman', 'en', 0x526f6d616e20692c2069692c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_resources', 'en', 0x5265736f7572636573);
INSERT INTO `lng_data` VALUES ('content', 'cont_resource_type', 'en', 0x74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_resource', 'en', 0x5265736f75726365);
INSERT INTO `lng_data` VALUES ('content', 'cont_reference', 'en', 0x5265666572656e6365);
INSERT INTO `lng_data` VALUES ('content', 'cont_ref_helptext', 'en', 0x28652e672e20687474703a2f2f7777772e7365727665722e6f72672f6d79696d6167652e6a706729);
INSERT INTO `lng_data` VALUES ('content', 'cont_publisher', 'en', 0x5075626c6973686572);
INSERT INTO `lng_data` VALUES ('content', 'cont_preview', 'en', 0x50726576696577);
INSERT INTO `lng_data` VALUES ('content', 'cont_prerequisites', 'en', 0x61646c63703a70726572657175697369746573);
INSERT INTO `lng_data` VALUES ('content', 'cont_prereq_type', 'en', 0x61646c63703a707265726571756973697465732e74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_pg_title', 'en', 0x50616765205469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_pg_content', 'en', 0x5061676520436f6e74656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_personal_clipboard', 'en', 0x506572736f6e616c20436c6970626f617264);
INSERT INTO `lng_data` VALUES ('content', 'cont_parameters', 'en', 0x706172616d6574657273);
INSERT INTO `lng_data` VALUES ('content', 'cont_parameter', 'en', 0x506172616d65746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_pages', 'en', 0x5061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_page_select_target_now', 'en', 0x50616765206d61726b656420666f72206d6f76696e672e2053656c65637420746172676574206e6f772e);
INSERT INTO `lng_data` VALUES ('content', 'cont_page_header', 'en', 0x5061676520486561646572);
INSERT INTO `lng_data` VALUES ('content', 'cont_orig_size', 'en', 0x4f726967696e616c2053697a65);
INSERT INTO `lng_data` VALUES ('content', 'cont_organizations', 'en', 0x4f7267616e697a6174696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_organization', 'en', 0x4f7267616e697a6174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_order', 'en', 0x4f726465722054797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_rows', 'en', 0x4e756d626572206f6620526f7773);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_items', 'en', 0x4e756d626572206f66204974656d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_cols', 'en', 0x4e756d626572206f6620436f6c756d6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_none', 'en', 0x4e6f6e65);
INSERT INTO `lng_data` VALUES ('content', 'cont_no_page', 'en', 0x4e6f205061676520666f756e642e);
INSERT INTO `lng_data` VALUES ('content', 'cont_no_object_found', 'en', 0x436f756c64206e6f742066696e6420616e79206f626a65637420776974682074686973207469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_no_assign_itself', 'en', 0x546865206f626a6563742063616e6e6f742062652061737369676e656420746f20697473656c66);
INSERT INTO `lng_data` VALUES ('content', 'cont_new_term', 'en', 0x4e6577205465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_new_media_obj', 'en', 0x4e6577204d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_new_assignment', 'en', 0x4e65772061737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_msg_multiple_editions', 'en', 0x49742773206e6f7420706f737369626c6520746f2073686f772064657461696c73206f66206d756c7469706c652065646974696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_month', 'en', 0x4d6f6e7468);
INSERT INTO `lng_data` VALUES ('content', 'cont_max_time_allowed', 'en', 0x61646c63703a6d617874696d65616c6c6f776564);
INSERT INTO `lng_data` VALUES ('content', 'cont_mastery_score', 'en', 0x61646c63703a6d61737465727973636f7265);
INSERT INTO `lng_data` VALUES ('content', 'cont_manifest', 'en', 0x4d616e6966657374);
INSERT INTO `lng_data` VALUES ('content', 'cont_lm_properties', 'en', 0x4c6561726e696e67204d6f64756c652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_term_new', 'en', 0x476c6f7373617279205465726d20284e6577204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_term', 'en', 0x476c6f7373617279205465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_page_new', 'en', 0x5061676520284e6577204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_page_faq', 'en', 0x506167652028464151204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_page', 'en', 0x50616765);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_new', 'en', 0x4d6564696120284e6577204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_media', 'en', 0x4d6564696120284d65646961204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_inline', 'en', 0x4d656469612028496e6c696e6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_faq', 'en', 0x4d656469612028464151204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_chapter_new', 'en', 0x4368617074657220284e6577204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_chapter', 'en', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_list_properties', 'en', 0x4c6973742050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_link_type', 'en', 0x4c696e6b2054797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_link_select', 'en', 0x496e7465726e616c204c696e6b);
INSERT INTO `lng_data` VALUES ('content', 'cont_keyword', 'en', 0x4b6579776f7264);
INSERT INTO `lng_data` VALUES ('content', 'cont_journal', 'en', 0x4a6f75726e616c);
INSERT INTO `lng_data` VALUES ('content', 'cont_item', 'en', 0x4974656d);
INSERT INTO `lng_data` VALUES ('content', 'cont_issn', 'en', 0x4953534e);
INSERT INTO `lng_data` VALUES ('content', 'cont_isbn', 'en', 0x4953424e);
INSERT INTO `lng_data` VALUES ('content', 'cont_is_visible', 'en', 0x697376697369626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_internal_link', 'en', 0x696e7465726e616c206c696e6b);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_table', 'en', 0x496e73657274205461626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_search', 'en', 0x506c6561736520696e73657274206120736561726368207465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_par', 'en', 0x496e7365727420506172616772617068);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_mob', 'en', 0x496e73657274204d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_list', 'en', 0x496e73657274204c697374);
INSERT INTO `lng_data` VALUES ('content', 'cont_import_id', 'en', 0x6964656e746966696572);
INSERT INTO `lng_data` VALUES ('content', 'cont_id_ref', 'en', 0x6964656e746966696572726566);
INSERT INTO `lng_data` VALUES ('content', 'cont_href', 'en', 0x68726566);
INSERT INTO `lng_data` VALUES ('content', 'cont_how_published', 'en', 0x486f77207075626c6973686564);
INSERT INTO `lng_data` VALUES ('content', 'cont_height', 'en', 0x486569676874);
INSERT INTO `lng_data` VALUES ('content', 'cont_fullscreen', 'en', 0x46756c6c73637265656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_files', 'en', 0x46696c6573);
INSERT INTO `lng_data` VALUES ('content', 'cont_file', 'en', 0x46696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_edition', 'en', 0x45646974696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_term', 'en', 0x45646974205465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_tab_properties', 'en', 0x5461626c652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_par', 'en', 0x4564697420506172616772617068);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_properties', 'en', 0x45646974204d65646961204f626a6563742050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_alias_prop', 'en', 0x45646974204d65646961204f626a65637420496e7374616e63652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob', 'en', 0x45646974204d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_details', 'en', 0x44657461696c73);
INSERT INTO `lng_data` VALUES ('content', 'cont_dependencies', 'en', 0x446570656e64656e63696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_del_assignment', 'en', 0x44656c6574652061737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_definitions', 'en', 0x446566696e6974696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_definition', 'en', 0x446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_def_organization', 'en', 0x64656661756c74);
INSERT INTO `lng_data` VALUES ('content', 'cont_def_layout', 'en', 0x44656661756c74204c61796f7574);
INSERT INTO `lng_data` VALUES ('content', 'cont_data_from_lms', 'en', 0x61646c63703a6461746166726f6d6c6d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_cross_reference', 'en', 0x43726f7373207265666572656e6365);
INSERT INTO `lng_data` VALUES ('content', 'cont_content_obj', 'en', 0x436f6e74656e74204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_content', 'en', 0x436f6e74656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_choose_glossary', 'en', 0x43686f6f736520476c6f7373617279);
INSERT INTO `lng_data` VALUES ('content', 'cont_choose_cont_obj', 'en', 0x43686f6f736520436f6e74656e74204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_characteristic', 'en', 0x4368617261637465726973746963);
INSERT INTO `lng_data` VALUES ('content', 'cont_chapters', 'en', 0x4368617074657273);
INSERT INTO `lng_data` VALUES ('content', 'cont_chap_select_target_now', 'en', 0x43686170746572206d61726b656420666f72206d6f76696e672e2053656c65637420746172676574206e6f772e);
INSERT INTO `lng_data` VALUES ('content', 'cont_chap_and_pages', 'en', 0x436861707465727320616e64205061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_change_type', 'en', 0x4368616e67652054797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_caption', 'en', 0x43617074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_info_about_work2', 'en', 0x2c20796f7520776f726b6564206865726520666f7220746865206c6173742074696d652e20596f752063616e20636f6e74696e756520746865726520627920636c69636b696e6720686572653a);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_info_about_work1', 'en', 0x4f6e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_hello', 'en', 0x48656c6c6f);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_night', 'en', 0x476f6f64206e69676874);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_evening', 'en', 0x476f6f64206576656e696e67);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_afternoon', 'en', 0x476f6f642061667465726e6f6f6e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_morning', 'en', 0x476f6f64206d6f726e696e67);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_level_zero', 'en', 0x4c6576656c207570);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_back_to_le', 'en', 0x4261636b20746f204c45);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_all_offers', 'en', 0x416c6c204c45);
INSERT INTO `lng_data` VALUES ('common', 'information_abbr', 'en', 0x496e666f);
INSERT INTO `lng_data` VALUES ('common', 'inform_user_mail', 'en', 0x53656e6420656d61696c20746f20696e666f726d20757365722061626f7574206368616e676573);
INSERT INTO `lng_data` VALUES ('common', 'info_trash', 'en', 0x44656c65746564204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'info_deleted', 'en', 0x4f626a6563742873292044656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'info_delete_sure', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e67206f626a6563742873293f);
INSERT INTO `lng_data` VALUES ('common', 'inbox', 'en', 0x496e626f78);
INSERT INTO `lng_data` VALUES ('common', 'in_use', 'en', 0x55736572204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'import_slm', 'en', 0x496d706f72742053434f524d205061636b616765);
INSERT INTO `lng_data` VALUES ('common', 'import_lm', 'en', 0x496d706f7274204c6561726e696e676d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'import', 'en', 0x496d706f7274);
INSERT INTO `lng_data` VALUES ('common', 'ilias_version', 'en', 0x494c4941532076657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'identifier', 'en', 0x6964656e746966696572);
INSERT INTO `lng_data` VALUES ('common', 'id', 'en', 0x4944);
INSERT INTO `lng_data` VALUES ('common', 'http_path', 'en', 0x487474702050617468);
INSERT INTO `lng_data` VALUES ('common', 'host', 'en', 0x486f7374);
INSERT INTO `lng_data` VALUES ('common', 'hobby', 'en', 0x496e746572657374732f486f6262696573);
INSERT INTO `lng_data` VALUES ('common', 'home', 'en', 0x486f6d65);
INSERT INTO `lng_data` VALUES ('common', 'hide_details', 'en', 0x686964652064657461696c73);
INSERT INTO `lng_data` VALUES ('common', 'help', 'en', 0x48656c70);
INSERT INTO `lng_data` VALUES ('common', 'guests', 'en', 0x477565737473);
INSERT INTO `lng_data` VALUES ('common', 'guest', 'en', 0x4775657374);
INSERT INTO `lng_data` VALUES ('common', 'grp_new', 'en', 0x4e65772047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_edit', 'en', 0x456469742047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_added', 'en', 0x47726f7570206164646564);
INSERT INTO `lng_data` VALUES ('common', 'grp_add', 'en', 0x4164642047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_a', 'en', 0x612047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp', 'en', 0x47726f7570);
INSERT INTO `lng_data` VALUES ('common', 'groups_overview', 'en', 0x47726f757073204f76657276696577);
INSERT INTO `lng_data` VALUES ('common', 'groups', 'en', 0x47726f757073);
INSERT INTO `lng_data` VALUES ('common', 'group_memstat_admin', 'en', 0x41646d696e6973747261746f72);
INSERT INTO `lng_data` VALUES ('common', 'group_memstat_member', 'en', 0x4d656d626572);
INSERT INTO `lng_data` VALUES ('common', 'group_memstat', 'en', 0x4d656d62657220537461747573);
INSERT INTO `lng_data` VALUES ('common', 'group_status_public', 'en', 0x5075626c6963);
INSERT INTO `lng_data` VALUES ('common', 'group_status_private', 'en', 0x50726976617465);
INSERT INTO `lng_data` VALUES ('common', 'group_status_closed', 'en', 0x436c6f736564);
INSERT INTO `lng_data` VALUES ('common', 'group_status', 'en', 0x47726f757020537461747573);
INSERT INTO `lng_data` VALUES ('common', 'group_objects', 'en', 0x47726f7570204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'group_not_available', 'en', 0x616e792067726f757020617661696c61626c65);
INSERT INTO `lng_data` VALUES ('common', 'group_members', 'en', 0x47726f7570204d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'group_name', 'en', 0x47726f75706e616d65);
INSERT INTO `lng_data` VALUES ('common', 'group_filesharing', 'en', 0x47726f75702046696c652053686172696e67);
INSERT INTO `lng_data` VALUES ('common', 'group_details', 'en', 0x47726f75702044657461696c73);
INSERT INTO `lng_data` VALUES ('common', 'group_any_objects', 'en', 0x4e6f205375626f626a65637473206176696c61626c65);
INSERT INTO `lng_data` VALUES ('common', 'glossary_added', 'en', 0x476c6f7373617279206164646564);
INSERT INTO `lng_data` VALUES ('common', 'glossary', 'en', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_new', 'en', 0x4e657720476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_added', 'en', 0x416464656420676c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_add', 'en', 0x41646420476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_a', 'en', 0x6120476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo', 'en', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'generate', 'en', 0x47656e6572617465);
INSERT INTO `lng_data` VALUES ('common', 'gender', 'en', 0x47656e646572);
INSERT INTO `lng_data` VALUES ('common', 'gdf_new', 'en', 0x4e657720446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'gdf_add', 'en', 0x41646420446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'functions', 'en', 0x46756e6374696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'fullname', 'en', 0x46756c6c6e616d65);
INSERT INTO `lng_data` VALUES ('common', 'from', 'en', 0x46726f6d);
INSERT INTO `lng_data` VALUES ('common', 'frm_new', 'en', 0x4e657720466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_edit', 'en', 0x4564697420466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_added', 'en', 0x466f72756d206164646564);
INSERT INTO `lng_data` VALUES ('common', 'frm_add', 'en', 0x41646420466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_a', 'en', 0x6120466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm', 'en', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'forums_overview', 'en', 0x466f72756d73204f76657276696577);
INSERT INTO `lng_data` VALUES ('common', 'forums', 'en', 0x466f72756d73);
INSERT INTO `lng_data` VALUES ('common', 'forum', 'en', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'form_empty_fields', 'en', 0x506c6561736520636f6d706c657465207468657365206669656c64733a);
INSERT INTO `lng_data` VALUES ('common', 'forename', 'en', 0x466f72656e616d65);
INSERT INTO `lng_data` VALUES ('common', 'obj_adm_desc', 'en', 0x4d61696e2073797374656d2073657474696e677320666f6c64657220636f6e7461696e696e6720616c6c2070616e656c7320746f2061646d696e6973747261746520796f757220494c49415320696e7374616c6c6174696f6e2e);
INSERT INTO `lng_data` VALUES ('common', 'obj_mail_desc', 'en', 0x436f6e66696775726520676c6f62616c206d61696c2073657474696e677320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf_desc', 'en', 0x4d616e61676520796f757220726f6c657320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'obj_usrf_desc', 'en', 0x4d616e6167652075736572206163636f756e747320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'obj_objf_desc', 'en', 0x4d616e61676520494c494153206f626a65637420747970657320616e64206f626a656374207065726d697373696f6e732e20286f6e6c7920666f7220657870657274732129);
INSERT INTO `lng_data` VALUES ('common', 'obj_lngf_desc', 'en', 0x4d616e61676520796f75722073797374656d206c616e67756167657320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'folders', 'en', 0x466f6c64657273);
INSERT INTO `lng_data` VALUES ('common', 'fold_new', 'en', 0x4e657720466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold_add', 'en', 0x41646420466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold_added', 'en', 0x466f6c646572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'fold_a', 'en', 0x6120466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold', 'en', 0x466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'flatview', 'en', 0x466c61742056696577);
INSERT INTO `lng_data` VALUES ('common', 'firstname', 'en', 0x46697273746e616d65);
INSERT INTO `lng_data` VALUES ('common', 'fill_out_all_required_fields', 'en', 0x506c656173652066696c6c206f757420616c6c207265717569726564206669656c6473);
INSERT INTO `lng_data` VALUES ('common', 'files_location', 'en', 0x46696c6573204c6f636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'file_version', 'en', 0x56657273696f6e2050726f766964656420696e2046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_valid', 'en', 0x46696c652069732076616c696421);
INSERT INTO `lng_data` VALUES ('common', 'file_not_valid', 'en', 0x46696c65206e6f742076616c696421);
INSERT INTO `lng_data` VALUES ('common', 'file_not_found', 'en', 0x46696c65204e6f7420466f756e64);
INSERT INTO `lng_data` VALUES ('common', 'file_new', 'en', 0x4e65772046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_add', 'en', 0x55706c6f61642046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_added', 'en', 0x46696c652075706c6f61646564);
INSERT INTO `lng_data` VALUES ('common', 'file_a', 'en', 0x612046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file', 'en', 0x46696c65);
INSERT INTO `lng_data` VALUES ('common', 'feedback_recipient', 'en', 0x466565646261636b20526563697069656e74);
INSERT INTO `lng_data` VALUES ('common', 'feedback', 'en', 0x466565646261636b);
INSERT INTO `lng_data` VALUES ('common', 'faq_exercise', 'en', 0x464151204578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'export_xml', 'en', 0x6578706f727420617320786d6c2066696c65);
INSERT INTO `lng_data` VALUES ('common', 'export_html', 'en', 0x6578706f72742061732068746d6c2066696c65);
INSERT INTO `lng_data` VALUES ('common', 'execute', 'en', 0x45786563757465);
INSERT INTO `lng_data` VALUES ('common', 'error_recipient', 'en', 0x4572726f7220526563697069656e74);
INSERT INTO `lng_data` VALUES ('common', 'err_wrong_login', 'en', 0x57726f6e67204c6f67696e);
INSERT INTO `lng_data` VALUES ('common', 'err_wrong_header', 'en', 0x526561736f6e3a2057726f6e67206865616465722e);
INSERT INTO `lng_data` VALUES ('common', 'err_session_expired', 'en', 0x596f75722073657373696f6e206973206578706972656421);
INSERT INTO `lng_data` VALUES ('common', 'err_over_3_param', 'en', 0x4d6f7265207468616e203320706172616d657465727321);
INSERT INTO `lng_data` VALUES ('common', 'err_no_param', 'en', 0x4e6f20706172616d6574657221);
INSERT INTO `lng_data` VALUES ('common', 'err_no_langfile_found', 'en', 0x4e6f206c616e67756167652066696c6520666f756e6421);
INSERT INTO `lng_data` VALUES ('common', 'err_in_line', 'en', 0x4572726f7220696e206c696e65);
INSERT INTO `lng_data` VALUES ('common', 'err_count_param', 'en', 0x526561736f6e3a2057726f6e6720706172616d6574657220636f756e74);
INSERT INTO `lng_data` VALUES ('common', 'err_2_param', 'en', 0x4f6e6c79203220706172616d6574657221);
INSERT INTO `lng_data` VALUES ('common', 'err_1_param', 'en', 0x4f6e6c79203120706172616d6574657221);
INSERT INTO `lng_data` VALUES ('common', 'enumerate', 'en', 0x456e756d6572617465);
INSERT INTO `lng_data` VALUES ('common', 'enabled', 'en', 0x456e61626c6564);
INSERT INTO `lng_data` VALUES ('common', 'enable_registration', 'en', 0x456e61626c65206e6577207573657220726567697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'enable', 'en', 0x456e61626c65);
INSERT INTO `lng_data` VALUES ('common', 'email_not_valid', 'en', 0x54686520656d61696c2061646465737320796f7520656e7465726564206973206e6f742076616c696421);
INSERT INTO `lng_data` VALUES ('common', 'email', 'en', 0x456d61696c);
INSERT INTO `lng_data` VALUES ('common', 'editor', 'en', 0x456469746f72);
INSERT INTO `lng_data` VALUES ('common', 'edited_at', 'en', 0x456469746564206174);
INSERT INTO `lng_data` VALUES ('common', 'edit_stylesheet', 'en', 0x45646974205374796c65);
INSERT INTO `lng_data` VALUES ('common', 'edit_properties', 'en', 0x456469742050726f70657274696573);
INSERT INTO `lng_data` VALUES ('common', 'edit_operations', 'en', 0x45646974204f7065726174696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'edit_data', 'en', 0x656469742064617461);
INSERT INTO `lng_data` VALUES ('common', 'edit', 'en', 0x45646974);
INSERT INTO `lng_data` VALUES ('common', 'drop', 'en', 0x44726f70);
INSERT INTO `lng_data` VALUES ('common', 'drafts', 'en', 0x447261667473);
INSERT INTO `lng_data` VALUES ('common', 'download', 'en', 0x446f776e6c6f6164);
INSERT INTO `lng_data` VALUES ('common', 'down', 'en', 0x446f776e);
INSERT INTO `lng_data` VALUES ('common', 'disabled', 'en', 0x44697361626c6564);
INSERT INTO `lng_data` VALUES ('common', 'desired_password', 'en', 0x446573697265642050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'description', 'en', 0x4465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'desc', 'en', 0x4465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'deleted', 'en', 0x44656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'delete_selected', 'en', 0x44656c6574652053656c6563746564);
INSERT INTO `lng_data` VALUES ('common', 'delete_object', 'en', 0x44656c657465204f626a656374287329);
INSERT INTO `lng_data` VALUES ('common', 'delete_all', 'en', 0x44656c65746520416c6c);
INSERT INTO `lng_data` VALUES ('common', 'delete', 'en', 0x44656c657465);
INSERT INTO `lng_data` VALUES ('common', 'default_style', 'en', 0x44656661756c74205374796c65);
INSERT INTO `lng_data` VALUES ('common', 'default_skin', 'en', 0x44656661756c7420536b696e);
INSERT INTO `lng_data` VALUES ('common', 'default_role', 'en', 0x44656661756c7420526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'default_language', 'en', 0x44656661756c74204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'default', 'en', 0x44656661756c74);
INSERT INTO `lng_data` VALUES ('common', 'db_version', 'en', 0x44617461626173652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'db_user', 'en', 0x44617461626173652055736572);
INSERT INTO `lng_data` VALUES ('common', 'db_type', 'en', 0x44617461626173652054797065);
INSERT INTO `lng_data` VALUES ('common', 'db_pass', 'en', 0x44617461626173652050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'db_name', 'en', 0x4461746162617365204e616d65);
INSERT INTO `lng_data` VALUES ('common', 'db_host', 'en', 0x446174616261736520486f7374);
INSERT INTO `lng_data` VALUES ('common', 'days', 'en', 0x44617973);
INSERT INTO `lng_data` VALUES ('common', 'day', 'en', 0x446179);
INSERT INTO `lng_data` VALUES ('common', 'date', 'en', 0x44617465);
INSERT INTO `lng_data` VALUES ('common', 'dataset', 'en', 0x44617461736574);
INSERT INTO `lng_data` VALUES ('common', 'database_version', 'en', 0x43757272656e742044617461626173652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'database', 'en', 0x4461746162617365);
INSERT INTO `lng_data` VALUES ('common', 'cutPage', 'en', 0x437574);
INSERT INTO `lng_data` VALUES ('common', 'cut', 'en', 0x437574);
INSERT INTO `lng_data` VALUES ('common', 'current_password', 'en', 0x43757272656e742050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'crs_new', 'en', 0x4e657720436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_management_system', 'en', 0x436f75727365204d616e6167656d656e742053797374656d);
INSERT INTO `lng_data` VALUES ('common', 'crs_edit', 'en', 0x4564697420436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_available', 'en', 0x417661696c61626c6520436f7572736573);
INSERT INTO `lng_data` VALUES ('common', 'crs_added', 'en', 0x436f75727365206164646564);
INSERT INTO `lng_data` VALUES ('common', 'crs_add', 'en', 0x41646420436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_a', 'en', 0x6120436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs', 'en', 0x436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'create_stylesheet', 'en', 0x437265617465205374796c65);
INSERT INTO `lng_data` VALUES ('common', 'create_in', 'en', 0x43726561746520696e);
INSERT INTO `lng_data` VALUES ('common', 'create', 'en', 0x437265617465);
INSERT INTO `lng_data` VALUES ('common', 'courses', 'en', 0x436f7572736573);
INSERT INTO `lng_data` VALUES ('common', 'course', 'en', 0x436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'country', 'en', 0x436f756e747279);
INSERT INTO `lng_data` VALUES ('common', 'copy', 'en', 0x436f7079);
INSERT INTO `lng_data` VALUES ('common', 'context', 'en', 0x436f6e74657874);
INSERT INTO `lng_data` VALUES ('common', 'contact_data', 'en', 0x436f6e7461637420496e666f726d6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'cont_object', 'en', 0x4f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'confirm', 'en', 0x436f6e6669726d);
INSERT INTO `lng_data` VALUES ('common', 'compose', 'en', 0x436f6d706f7365);
INSERT INTO `lng_data` VALUES ('common', 'comment', 'en', 0x636f6d6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'comma_separated', 'en', 0x436f6d6d6120536570617261746564);
INSERT INTO `lng_data` VALUES ('common', 'close', 'en', 0x436c6f7365);
INSERT INTO `lng_data` VALUES ('common', 'clipboard', 'en', 0x436c6970626f617264);
INSERT INTO `lng_data` VALUES ('common', 'clear', 'en', 0x436c656172);
INSERT INTO `lng_data` VALUES ('common', 'city', 'en', 0x43697479);
INSERT INTO `lng_data` VALUES ('common', 'choose_only_one_language', 'en', 0x506c656173652063686f6f7365206f6e6c79206f6e65206c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'choose_location', 'en', 0x43686f6f7365204c6f636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'choose_language', 'en', 0x43686f6f736520596f7572204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'chg_password', 'en', 0x4368616e67652050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'chg_language', 'en', 0x4368616e6765204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'check_languages', 'en', 0x436865636b204c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'check_langfile', 'en', 0x506c6561736520636865636b20796f7572206c616e67756167652066696c65);
INSERT INTO `lng_data` VALUES ('common', 'check', 'en', 0x436865636b);
INSERT INTO `lng_data` VALUES ('common', 'chapter_title', 'en', 0x43686170746572205469746c65);
INSERT INTO `lng_data` VALUES ('common', 'chapter_number', 'en', 0x43686170746572204e756d626572);
INSERT INTO `lng_data` VALUES ('common', 'chapter', 'en', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('common', 'changed_to', 'en', 0x6368616e67656420746f);
INSERT INTO `lng_data` VALUES ('common', 'change_sort_direction', 'en', 0x4368616e676520736f727420646972656374696f6e);
INSERT INTO `lng_data` VALUES ('common', 'change_metadata', 'en', 0x4368616e6765204d65746164617461);
INSERT INTO `lng_data` VALUES ('common', 'change_lo_info', 'en', 0x4368616e6765204c4f20496e666f);
INSERT INTO `lng_data` VALUES ('common', 'change_existing_objects', 'en', 0x4368616e6765206578697374696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'change_assignment', 'en', 0x4368616e67652061737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'change_active_assignment', 'en', 0x4368616e6765206163746976652061737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'change', 'en', 0x4368616e6765);
INSERT INTO `lng_data` VALUES ('common', 'censorship', 'en', 0x63656e736f7273686970);
INSERT INTO `lng_data` VALUES ('common', 'cc', 'en', 0x4343);
INSERT INTO `lng_data` VALUES ('common', 'categories', 'en', 0x43617465676f72696573);
INSERT INTO `lng_data` VALUES ('common', 'cat_new', 'en', 0x4e65772063617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_edit', 'en', 0x456469742043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_added', 'en', 0x43617465676f7279206164646564);
INSERT INTO `lng_data` VALUES ('common', 'cat_add', 'en', 0x4164642043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_a', 'en', 0x612063617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat', 'en', 0x43617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cannot_uninstall_systemlanguage', 'en', 0x596f752063616e6e6f7420756e696e7374616c6c207468652073797374656d206c616e677561676521);
INSERT INTO `lng_data` VALUES ('common', 'cannot_uninstall_language_in_use', 'en', 0x596f752063616e6e6f7420756e696e7374616c6c20746865206c616e67756167652063757272656e746c7920696e2075736521);
INSERT INTO `lng_data` VALUES ('common', 'cancel', 'en', 0x43616e63656c);
INSERT INTO `lng_data` VALUES ('common', 'calendar', 'en', 0x43616c656e646172);
INSERT INTO `lng_data` VALUES ('common', 'by', 'en', 0x4279);
INSERT INTO `lng_data` VALUES ('common', 'btn_undelete', 'en', 0x556e64656c657465);
INSERT INTO `lng_data` VALUES ('common', 'btn_remove_system', 'en', 0x52656d6f76652066726f6d2053797374656d);
INSERT INTO `lng_data` VALUES ('common', 'bookmarks_of', 'en', 0x426f6f6b6d61726b73206f66);
INSERT INTO `lng_data` VALUES ('common', 'bookmarks', 'en', 0x426f6f6b6d61726b73);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_target', 'en', 0x546172676574);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_new', 'en', 0x4e657720426f6f6b6d61726b);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_folder_new', 'en', 0x4e657720426f6f6b6d61726b20466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_folder_edit', 'en', 0x4564697420426f6f6b6d61726b20466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_edit', 'en', 0x4564697420426f6f6b6d61726b);
INSERT INTO `lng_data` VALUES ('common', 'bmf', 'en', 0x426f6f6b6d61726b20466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'bm', 'en', 0x426f6f6b6d61726b);
INSERT INTO `lng_data` VALUES ('common', 'basic_data', 'en', 0x42617369632044617461);
INSERT INTO `lng_data` VALUES ('common', 'basedn', 'en', 0x42617365444e);
INSERT INTO `lng_data` VALUES ('common', 'back', 'en', 0x4261636b);
INSERT INTO `lng_data` VALUES ('common', 'available_languages', 'en', 0x417661696c61626c65204c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'authors', 'en', 0x417574686f7273);
INSERT INTO `lng_data` VALUES ('common', 'author', 'en', 0x417574686f72);
INSERT INTO `lng_data` VALUES ('common', 'attachments', 'en', 0x4174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('common', 'attachment', 'en', 0x4174746163686d656e74);
INSERT INTO `lng_data` VALUES ('common', 'at_location', 'en', 0x6174206c6f636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'assign_user_to_role', 'en', 0x41737369676e205573657220746f20526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'assign_lo_forum', 'en', 0x41737369676e204c4f20466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'assign', 'en', 0x41737369676e);
INSERT INTO `lng_data` VALUES ('common', 'are_you_sure', 'en', 0x41726520796f7520737572653f);
INSERT INTO `lng_data` VALUES ('common', 'archive', 'en', 0x41726368697665);
INSERT INTO `lng_data` VALUES ('common', 'appointment_list', 'en', 0x4170706f696e746d656e74204c697374);
INSERT INTO `lng_data` VALUES ('common', 'appointment', 'en', 0x4170706f696e746d656e74);
INSERT INTO `lng_data` VALUES ('common', 'answers', 'en', 0x416e7377657273);
INSERT INTO `lng_data` VALUES ('common', 'announce_changes', 'en', 0x416e6e6f756e6365204368616e676573);
INSERT INTO `lng_data` VALUES ('common', 'announce', 'en', 0x416e6e6f756e6365);
INSERT INTO `lng_data` VALUES ('common', 'and', 'en', 0x616e64);
INSERT INTO `lng_data` VALUES ('common', 'allow_register', 'en', 0x417661696c61626c6520696e20726567697374726174696f6e20666f726d20666f72206e6577207573657273);
INSERT INTO `lng_data` VALUES ('common', 'all_objects', 'en', 0x416c6c204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'adopt_perm_from_template', 'en', 0x41646f7074207065726d697373696f6e2073657474696e67732066726f6d20526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'adopt', 'en', 0x61646f7074);
INSERT INTO `lng_data` VALUES ('common', 'administrator', 'en', 0x41646d696e6973747261746f72);
INSERT INTO `lng_data` VALUES ('common', 'administration', 'en', 0x41646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'administrate', 'en', 0x41646d696e69737472617465);
INSERT INTO `lng_data` VALUES ('common', 'add_translation', 'en', 0x416464207472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'add_member', 'en', 0x416464204d656d626572);
INSERT INTO `lng_data` VALUES ('common', 'add_author', 'en', 0x41646420417574686f72);
INSERT INTO `lng_data` VALUES ('common', 'add', 'en', 0x416464);
INSERT INTO `lng_data` VALUES ('common', 'active_roles', 'en', 0x41637469766520526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'actions', 'en', 0x416374696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'action_aborted', 'en', 0x416374696f6e2061626f72746564);
INSERT INTO `lng_data` VALUES ('common', 'access', 'en', 0x416363657373);
INSERT INTO `lng_data` VALUES ('common', 'accept_usr_agreement', 'en', 0x41636365707420757365722061677265656d656e743f);
INSERT INTO `lng_data` VALUES ('common', 'absolute_path', 'en', 0x4162736f6c7574652050617468);



#
# Tabellenstruktur für Tabelle `lo_access`
#

CREATE TABLE `lo_access` (
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `usr_id` int(11) NOT NULL default '0',
  `lm_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `lm_title` varchar(200) NOT NULL default ''
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_access`
#




#
# Tabellenstruktur für Tabelle `lo_attribute_idx`
#

CREATE TABLE `lo_attribute_idx` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `value_id` smallint(5) unsigned NOT NULL default '0',
  KEY `node_id` (`node_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_attribute_idx`
#




#
# Tabellenstruktur für Tabelle `lo_attribute_name`
#

CREATE TABLE `lo_attribute_name` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `attribute` char(32) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`),
  UNIQUE KEY `attribute` (`attribute`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_attribute_name`
#




#
# Tabellenstruktur für Tabelle `lo_attribute_namespace`
#

CREATE TABLE `lo_attribute_namespace` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `namespace` char(64) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_attribute_namespace`
#




#
# Tabellenstruktur für Tabelle `lo_attribute_value`
#

CREATE TABLE `lo_attribute_value` (
  `value_id` smallint(5) unsigned NOT NULL auto_increment,
  `value` char(32) NOT NULL default '0',
  PRIMARY KEY  (`value_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_attribute_value`
#




#
# Tabellenstruktur für Tabelle `lo_cdata`
#

CREATE TABLE `lo_cdata` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `cdata` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_cdata`
#




#
# Tabellenstruktur für Tabelle `lo_comment`
#

CREATE TABLE `lo_comment` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `comment` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_comment`
#




#
# Tabellenstruktur für Tabelle `lo_element_idx`
#

CREATE TABLE `lo_element_idx` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `element_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_element_idx`
#




#
# Tabellenstruktur für Tabelle `lo_element_name`
#

CREATE TABLE `lo_element_name` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `element` char(32) NOT NULL default '',
  PRIMARY KEY  (`element_id`),
  UNIQUE KEY `element` (`element`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_element_name`
#




#
# Tabellenstruktur für Tabelle `lo_element_namespace`
#

CREATE TABLE `lo_element_namespace` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `namespace` char(64) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_element_namespace`
#




#
# Tabellenstruktur für Tabelle `lo_entity_reference`
#

CREATE TABLE `lo_entity_reference` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `entity_reference` char(128) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_entity_reference`
#




#
# Tabellenstruktur für Tabelle `lo_node_type`
#

CREATE TABLE `lo_node_type` (
  `node_type_id` int(11) NOT NULL auto_increment,
  `description` varchar(50) default NULL,
  `lft_delimiter` varchar(10) default NULL,
  `rgt_delimiter` varchar(10) default NULL,
  PRIMARY KEY  (`node_type_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_node_type`
#

INSERT INTO `lo_node_type` VALUES (1, 'ELEMENT_NODE', '<', '>');
INSERT INTO `lo_node_type` VALUES (2, 'ATTRIBUTE_NODE(not used)', '"', '"');
INSERT INTO `lo_node_type` VALUES (3, 'TEXT_NODE', NULL, NULL);
INSERT INTO `lo_node_type` VALUES (5, 'ENTITY_REF_NODE', '&', ';');
INSERT INTO `lo_node_type` VALUES (4, 'CDATA_SECTION_NODE', '<![CDATA[', ']]>');
INSERT INTO `lo_node_type` VALUES (8, 'COMMENT_NODE', '<!--', '-->');
INSERT INTO `lo_node_type` VALUES (9, 'DOCUMENT_NODE', NULL, NULL);
INSERT INTO `lo_node_type` VALUES (10, 'DOCUMENT_TYPE_NODE', NULL, NULL);
INSERT INTO `lo_node_type` VALUES (6, 'ENTITY_NODE', '&', ';');



#
# Tabellenstruktur für Tabelle `lo_pi_data`
#

CREATE TABLE `lo_pi_data` (
  `leaf_id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `leaf_text` text NOT NULL,
  PRIMARY KEY  (`leaf_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_pi_data`
#




#
# Tabellenstruktur für Tabelle `lo_pi_target`
#

CREATE TABLE `lo_pi_target` (
  `leaf_id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `leaf_text` text NOT NULL,
  PRIMARY KEY  (`leaf_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_pi_target`
#




#
# Tabellenstruktur für Tabelle `lo_text`
#

CREATE TABLE `lo_text` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `textnode` text NOT NULL,
  PRIMARY KEY  (`node_id`),
  FULLTEXT KEY `textnode` (`textnode`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_text`
#




#
# Tabellenstruktur für Tabelle `lo_tree`
#

CREATE TABLE `lo_tree` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `lo_id` mediumint(8) unsigned NOT NULL default '0',
  `parent_node_id` int(10) unsigned NOT NULL default '0',
  `lft` smallint(5) unsigned NOT NULL default '0',
  `rgt` smallint(5) unsigned NOT NULL default '0',
  `node_type_id` tinyint(3) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  `prev_sibling_node_id` int(10) unsigned NOT NULL default '0',
  `next_sibling_node_id` int(10) unsigned NOT NULL default '0',
  `first_child_node_id` int(10) unsigned NOT NULL default '0',
  `struct` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`node_id`),
  KEY `lo_id` (`lo_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_tree`
#




#
# Tabellenstruktur für Tabelle `mail`
#

CREATE TABLE `mail` (
  `mail_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `folder_id` int(11) NOT NULL default '0',
  `sender_id` int(11) default NULL,
  `attachments` varchar(255) default NULL,
  `send_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `timest` timestamp(14) NOT NULL,
  `rcp_to` varchar(255) default NULL,
  `rcp_cc` varchar(255) default NULL,
  `rcp_bcc` varchar(255) default NULL,
  `m_status` varchar(16) default NULL,
  `m_type` varchar(16) default NULL,
  `m_email` tinyint(1) default NULL,
  `m_subject` varchar(255) default NULL,
  `m_message` text,
  PRIMARY KEY  (`mail_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `mail`
#




#
# Tabellenstruktur für Tabelle `mail_attachment`
#

CREATE TABLE `mail_attachment` (
  `mail_id` int(11) NOT NULL default '0',
  `path` text NOT NULL,
  PRIMARY KEY  (`mail_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `mail_attachment`
#




#
# Tabellenstruktur für Tabelle `mail_obj_data`
#

CREATE TABLE `mail_obj_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` char(70) NOT NULL default '',
  `type` char(16) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`user_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `mail_obj_data`
#

INSERT INTO `mail_obj_data` VALUES (2, 6, 'a_root', 'root');
INSERT INTO `mail_obj_data` VALUES (3, 6, 'b_inbox', 'inbox');
INSERT INTO `mail_obj_data` VALUES (4, 6, 'c_trash', 'trash');
INSERT INTO `mail_obj_data` VALUES (5, 6, 'd_drafts', 'drafts');
INSERT INTO `mail_obj_data` VALUES (6, 6, 'e_sent', 'sent');
INSERT INTO `mail_obj_data` VALUES (7, 6, 'z_local', 'local');



#
# Tabellenstruktur für Tabelle `mail_options`
#

CREATE TABLE `mail_options` (
  `user_id` int(11) NOT NULL default '0',
  `linebreak` tinyint(4) NOT NULL default '0',
  `signature` text NOT NULL,
  KEY `user_id` (`user_id`,`linebreak`)
) TYPE=MyISAM;

#
# Daten für Tabelle `mail_options`
#

INSERT INTO `mail_options` VALUES (6, 60, '');



#
# Tabellenstruktur für Tabelle `mail_saved`
#

CREATE TABLE `mail_saved` (
  `user_id` int(11) NOT NULL default '0',
  `attachments` varchar(255) default NULL,
  `rcp_to` varchar(255) default NULL,
  `rcp_cc` varchar(255) default NULL,
  `rcp_bcc` varchar(255) default NULL,
  `m_type` varchar(16) default NULL,
  `m_email` tinyint(1) default NULL,
  `m_subject` varchar(255) default NULL,
  `m_message` text
) TYPE=MyISAM;

#
# Daten für Tabelle `mail_saved`
#




#
# Tabellenstruktur für Tabelle `mail_tree`
#

CREATE TABLE `mail_tree` (
  `tree` int(11) NOT NULL default '0',
  `child` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned default NULL,
  `lft` int(11) unsigned NOT NULL default '0',
  `rgt` int(11) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  KEY `child` (`child`),
  KEY `parent` (`parent`)
) TYPE=MyISAM;

#
# Daten für Tabelle `mail_tree`
#

INSERT INTO `mail_tree` VALUES (6, 2, 0, 1, 12, 1);
INSERT INTO `mail_tree` VALUES (6, 3, 2, 2, 3, 2);
INSERT INTO `mail_tree` VALUES (6, 4, 2, 4, 5, 2);
INSERT INTO `mail_tree` VALUES (6, 5, 2, 6, 7, 2);
INSERT INTO `mail_tree` VALUES (6, 6, 2, 8, 9, 2);
INSERT INTO `mail_tree` VALUES (6, 7, 2, 10, 11, 2);



#
# Tabellenstruktur für Tabelle `media_item`
#

CREATE TABLE `media_item` (
  `id` int(11) NOT NULL auto_increment,
  `width` varchar(10) default NULL,
  `height` varchar(10) default NULL,
  `halign` enum('Left','Center','Right','LeftFloat','RightFloat') default NULL,
  `caption` text,
  `nr` int(11) NOT NULL default '0',
  `purpose` enum('Standard','Fullscreen','Additional') default NULL,
  `mob_id` int(11) NOT NULL default '0',
  `location` varchar(200) default NULL,
  `location_type` enum('LocalFile','Reference') NOT NULL default 'LocalFile',
  `format` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `media_item`
#




#
# Tabellenstruktur für Tabelle `meta_data`
#

CREATE TABLE `meta_data` (
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` char(3) NOT NULL default '',
  `title` varchar(200) NOT NULL default '',
  `language` varchar(200) NOT NULL default '',
  `description` blob NOT NULL,
  PRIMARY KEY  (`obj_id`,`obj_type`)
) TYPE=MyISAM;

#
# Daten für Tabelle `meta_data`
#




#
# Tabellenstruktur für Tabelle `meta_keyword`
#

CREATE TABLE `meta_keyword` (
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` char(3) NOT NULL default '',
  `language` char(2) NOT NULL default '',
  `keyword` varchar(200) NOT NULL default ''
) TYPE=MyISAM;

#
# Daten für Tabelle `meta_keyword`
#




#
# Tabellenstruktur für Tabelle `meta_techn_format`
#

CREATE TABLE `meta_techn_format` (
  `tech_id` int(11) NOT NULL default '0',
  `format` varchar(150) default NULL,
  `nr` int(11) default NULL,
  KEY `tech_id` (`tech_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `meta_techn_format`
#




#
# Tabellenstruktur für Tabelle `meta_techn_loc`
#

CREATE TABLE `meta_techn_loc` (
  `tech_id` int(11) NOT NULL default '0',
  `location` varchar(150) default NULL,
  `nr` int(11) default NULL,
  `type` enum('LocalFile','Reference') NOT NULL default 'LocalFile',
  KEY `tech_id` (`tech_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `meta_techn_loc`
#




#
# Tabellenstruktur für Tabelle `meta_technical`
#

CREATE TABLE `meta_technical` (
  `tech_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` char(3) NOT NULL default '',
  `size` varchar(50) NOT NULL default '',
  `install_remarks` text NOT NULL,
  `install_remarks_lang` char(2) NOT NULL default '',
  `other_requirements` text NOT NULL,
  `other_requirements_lang` char(2) NOT NULL default '',
  `duration` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`tech_id`),
  KEY `obj_id` (`obj_id`,`obj_type`)
) TYPE=MyISAM;

#
# Daten für Tabelle `meta_technical`
#




#
# Tabellenstruktur für Tabelle `mob_parameter`
#

CREATE TABLE `mob_parameter` (
  `med_item_id` int(11) NOT NULL default '0',
  `name` varchar(50) default NULL,
  `value` text,
  KEY `mob_id` (`med_item_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `mob_parameter`
#




#
# Tabellenstruktur für Tabelle `note_data`
#

CREATE TABLE `note_data` (
  `note_id` int(11) NOT NULL default '0',
  `lo_id` int(11) NOT NULL default '0',
  `text` text,
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `important` enum('y','n') NOT NULL default 'n',
  `good` enum('y','n') NOT NULL default 'n',
  `question` enum('y','n') NOT NULL default 'n',
  `bad` enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`note_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `note_data`
#




#
# Tabellenstruktur für Tabelle `object_data`
#

CREATE TABLE `object_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `type` char(4) NOT NULL default 'none',
  `title` char(70) NOT NULL default '',
  `description` char(128) default NULL,
  `owner` int(11) NOT NULL default '0',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `import_id` char(50) NOT NULL default '',
  PRIMARY KEY  (`obj_id`),
  KEY `type` (`type`)
) TYPE=MyISAM;

#
# Daten für Tabelle `object_data`
#

INSERT INTO `object_data` VALUES (2, 'role', 'Administrator', 'Role for systemadministrators. This role grants access to everything!', -1, '2002-01-16 15:31:45', '2003-08-15 13:18:57', '');
INSERT INTO `object_data` VALUES (3, 'role', 'Author', 'Role for teachers with many write & some create permissions.', -1, '2002-01-16 15:32:50', '2003-08-15 13:19:22', '');
INSERT INTO `object_data` VALUES (4, 'role', 'Learner', 'Typical role for students. Grants write access to some objects.', -1, '2002-01-16 15:34:00', '2003-08-15 13:19:48', '');
INSERT INTO `object_data` VALUES (5, 'role', 'Guest', 'Role grants only a few visible & read permissions.', -1, '2002-01-16 15:34:46', '2003-08-15 13:19:34', '');
INSERT INTO `object_data` VALUES (6, 'usr', 'root user', 'ilias@yourserver.com', -1, '2002-01-16 16:09:22', '2003-09-30 19:50:01', '');
INSERT INTO `object_data` VALUES (7, 'usrf', 'Users', 'Folder contains all users', -1, '2002-06-27 09:24:06', '2003-08-15 10:13:26', '');
INSERT INTO `object_data` VALUES (8, 'rolf', 'Roles', 'Folder contains all roles', -1, '2002-06-27 09:24:06', '2002-06-27 09:24:06', '');
INSERT INTO `object_data` VALUES (1, 'root', 'ILIAS', 'This is the root node of the system!!!', -1, '2002-06-24 15:15:03', '2002-06-24 15:15:03', '');
INSERT INTO `object_data` VALUES (9, 'adm', 'System Settings', 'Folder contains the systems settings', -1, '2002-07-15 12:37:33', '2002-07-15 12:37:33', '');
INSERT INTO `object_data` VALUES (10, 'objf', 'Objects', 'Folder contains list of known object types', -1, '2002-07-15 12:36:56', '2003-08-15 11:52:05', '');
INSERT INTO `object_data` VALUES (11, 'lngf', 'Languages', 'Folder contains all available languages', -1, '2002-07-15 15:52:51', '2002-07-15 15:52:51', '');
INSERT INTO `object_data` VALUES (25, 'typ', 'usr', 'User object', -1, '2002-07-15 15:53:37', '2003-08-15 12:30:56', '');
INSERT INTO `object_data` VALUES (34, 'typ', 'lm', 'Learning module Object', -1, '2002-07-15 15:54:04', '2003-08-15 12:33:04', '');
INSERT INTO `object_data` VALUES (37, 'typ', 'frm', 'Forum object', -1, '2002-07-15 15:54:22', '2003-08-15 12:36:40', '');
INSERT INTO `object_data` VALUES (15, 'typ', 'grp', 'Group object', -1, '2002-07-15 15:54:37', '2002-07-15 15:54:37', '');
INSERT INTO `object_data` VALUES (16, 'typ', 'cat', 'Category object', -1, '2002-07-15 15:54:54', '2002-07-15 15:54:54', '');
INSERT INTO `object_data` VALUES (17, 'typ', 'crs', 'Course object', -1, '2002-07-15 15:55:08', '2002-07-15 15:55:08', '');
INSERT INTO `object_data` VALUES (19, 'typ', 'mail', 'Mailmodule object', -1, '2002-07-15 15:55:49', '2002-07-15 15:55:49', '');
INSERT INTO `object_data` VALUES (21, 'typ', 'adm', 'Administration Panel object', -1, '2002-07-15 15:56:38', '2002-07-15 15:56:38', '');
INSERT INTO `object_data` VALUES (22, 'typ', 'usrf', 'User Folder object', -1, '2002-07-15 15:56:52', '2002-07-15 15:56:52', '');
INSERT INTO `object_data` VALUES (23, 'typ', 'rolf', 'Role Folder object', -1, '2002-07-15 15:57:06', '2002-07-15 15:57:06', '');
INSERT INTO `object_data` VALUES (24, 'typ', 'objf', 'Object-Type Folder object', -1, '2002-07-15 15:57:17', '2002-07-15 15:57:17', '');
INSERT INTO `object_data` VALUES (26, 'typ', 'typ', 'Object Type Definition object', -1, '2002-07-15 15:58:16', '2002-07-15 15:58:16', '');
INSERT INTO `object_data` VALUES (27, 'typ', 'rolt', 'Role template object', -1, '2002-07-15 15:58:16', '2002-07-15 15:58:16', '');
INSERT INTO `object_data` VALUES (28, 'typ', 'lngf', 'Language Folder object', -1, '2002-08-28 14:22:01', '2002-08-28 14:22:01', '');
INSERT INTO `object_data` VALUES (29, 'typ', 'lng', 'Language object', -1, '2002-08-30 10:18:29', '2002-08-30 10:18:29', '');
INSERT INTO `object_data` VALUES (30, 'typ', 'role', 'Role Object', -1, '2002-08-30 10:21:37', '2002-08-30 10:21:37', '');
INSERT INTO `object_data` VALUES (31, 'typ', 'dbk', 'Digilib Book', -1, '2003-08-15 10:07:29', '2003-08-15 12:30:19', '');
INSERT INTO `object_data` VALUES (33, 'typ', 'root', 'Root Folder Object', -1, '2002-12-21 00:04:00', '2003-08-15 12:04:20', '');
INSERT INTO `object_data` VALUES (70, 'lng', 'en', 'installed', -1, '0000-00-00 00:00:00', '2003-09-30 19:50:12', '');
INSERT INTO `object_data` VALUES (14, 'role', 'Anonymous', 'Default role for anonymous users (with no account)', -1, '2003-08-15 12:06:19', '2003-08-15 13:19:11', '');
INSERT INTO `object_data` VALUES (18, 'typ', 'mob', 'Multimedia object', -1, '0000-00-00 00:00:00', '2003-08-15 12:03:20', '');
INSERT INTO `object_data` VALUES (35, 'typ', 'notf', 'Note Folder Object', -1, '2002-12-21 00:04:00', '2002-12-21 00:04:00', '');
INSERT INTO `object_data` VALUES (36, 'typ', 'note', 'Note Object', -1, '2002-12-21 00:04:00', '2002-12-21 00:04:00', '');
INSERT INTO `object_data` VALUES (12, 'mail', 'Mail Settings', 'Mail settings object', -1, '2003-08-15 10:07:28', '2003-08-15 10:07:28', '');
INSERT INTO `object_data` VALUES (20, 'typ', 'slm', 'SCORM Learning Module', -1, '2003-08-15 10:07:28', '2003-08-15 12:23:10', '');
INSERT INTO `object_data` VALUES (80, 'rolt', 'il_grp_admin', 'Administrator role template of groups', -1, '2003-08-15 10:07:28', '2003-08-15 12:11:40', '');
INSERT INTO `object_data` VALUES (81, 'rolt', 'il_grp_member', 'Member role template of groups', -1, '2003-08-15 10:07:28', '2003-08-15 12:12:24', '');
INSERT INTO `object_data` VALUES (82, 'rolt', 'il_grp_status_closed', 'Group role template', -1, '2003-08-15 10:07:29', '2003-08-15 13:21:38', '');
INSERT INTO `object_data` VALUES (83, 'rolt', 'il_grp_status_open', 'Group role template', -1, '2003-08-15 10:07:29', '2003-08-15 13:21:25', '');
INSERT INTO `object_data` VALUES (32, 'typ', 'glo', 'Glossary', -1, '2003-08-15 10:07:30', '2003-08-15 12:29:54', '');
INSERT INTO `object_data` VALUES (13, 'usr', 'Anonymous', 'Anonymous user account. DO NOT DELETE!', -1, '2003-08-15 10:07:30', '2003-08-15 10:07:30', '');
INSERT INTO `object_data` VALUES (71, 'lng', 'de', 'not_installed', 6, '2003-08-15 10:25:19', '2003-09-30 19:50:06', '');
INSERT INTO `object_data` VALUES (72, 'lng', 'es', 'not_installed', 6, '2003-08-15 10:25:19', '2003-08-15 10:25:19', '');
INSERT INTO `object_data` VALUES (73, 'lng', 'it', 'not_installed', 6, '2003-08-15 10:25:19', '2003-08-15 10:25:19', '');



#
# Tabellenstruktur für Tabelle `object_reference`
#

CREATE TABLE `object_reference` (
  `ref_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ref_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `object_reference`
#

INSERT INTO `object_reference` VALUES (1, 1);
INSERT INTO `object_reference` VALUES (7, 7);
INSERT INTO `object_reference` VALUES (8, 8);
INSERT INTO `object_reference` VALUES (9, 9);
INSERT INTO `object_reference` VALUES (10, 10);
INSERT INTO `object_reference` VALUES (11, 11);
INSERT INTO `object_reference` VALUES (12, 12);



#
# Tabellenstruktur für Tabelle `object_translation`
#

CREATE TABLE `object_translation` (
  `obj_id` int(11) NOT NULL default '0',
  `title` char(70) NOT NULL default '',
  `description` char(128) default NULL,
  `lang_code` char(2) NOT NULL default '',
  `lang_default` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`,`lang_code`)
) TYPE=MyISAM;

#
# Daten für Tabelle `object_translation`
#




#
# Tabellenstruktur für Tabelle `page_object`
#

CREATE TABLE `page_object` (
  `page_id` int(11) NOT NULL default '0',
  `parent_id` int(11) default NULL,
  `content` text NOT NULL,
  `parent_type` varchar(4) NOT NULL default 'lm',
  PRIMARY KEY  (`page_id`,`parent_type`),
  FULLTEXT KEY `content` (`content`)
) TYPE=MyISAM;

#
# Daten für Tabelle `page_object`
#




#
# Tabellenstruktur für Tabelle `personal_clipboard`
#

CREATE TABLE `personal_clipboard` (
  `user_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  `type` char(4) NOT NULL default '',
  `title` char(70) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`item_id`,`type`)
) TYPE=MyISAM;

#
# Daten für Tabelle `personal_clipboard`
#




#
# Tabellenstruktur für Tabelle `rbac_fa`
#

CREATE TABLE `rbac_fa` (
  `rol_id` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `assign` enum('y','n') default NULL,
  PRIMARY KEY  (`rol_id`,`parent`)
) TYPE=MyISAM;

#
# Daten für Tabelle `rbac_fa`
#

INSERT INTO `rbac_fa` VALUES (2, 8, 'y');
INSERT INTO `rbac_fa` VALUES (3, 8, 'y');
INSERT INTO `rbac_fa` VALUES (4, 8, 'y');
INSERT INTO `rbac_fa` VALUES (5, 8, 'y');
INSERT INTO `rbac_fa` VALUES (83, 8, 'n');
INSERT INTO `rbac_fa` VALUES (82, 8, 'n');
INSERT INTO `rbac_fa` VALUES (80, 8, 'n');
INSERT INTO `rbac_fa` VALUES (81, 8, 'n');
INSERT INTO `rbac_fa` VALUES (14, 8, 'y');



#
# Tabellenstruktur für Tabelle `rbac_operations`
#

CREATE TABLE `rbac_operations` (
  `ops_id` int(11) NOT NULL auto_increment,
  `operation` char(32) NOT NULL default '',
  `description` char(255) default NULL,
  PRIMARY KEY  (`ops_id`),
  UNIQUE KEY `operation` (`operation`)
) TYPE=MyISAM;

#
# Daten für Tabelle `rbac_operations`
#

INSERT INTO `rbac_operations` VALUES (1, 'edit permission', 'edit permissions');
INSERT INTO `rbac_operations` VALUES (2, 'visible', 'view object');
INSERT INTO `rbac_operations` VALUES (3, 'read', 'access object');
INSERT INTO `rbac_operations` VALUES (4, 'write', 'modify object');
INSERT INTO `rbac_operations` VALUES (5, 'create', 'add object');
INSERT INTO `rbac_operations` VALUES (6, 'delete', 'remove object');
INSERT INTO `rbac_operations` VALUES (7, 'join', 'join/subscribe');
INSERT INTO `rbac_operations` VALUES (8, 'leave', 'leave/unsubscribe');
INSERT INTO `rbac_operations` VALUES (9, 'edit post', 'edit forum articles');
INSERT INTO `rbac_operations` VALUES (10, 'delete post', 'delete forum articles');
INSERT INTO `rbac_operations` VALUES (11, 'smtp mail', 'send external mail');
INSERT INTO `rbac_operations` VALUES (12, 'system message', 'allow to send system messages');



#
# Tabellenstruktur für Tabelle `rbac_pa`
#

CREATE TABLE `rbac_pa` (
  `rol_id` int(11) NOT NULL default '0',
  `ops_id` text NOT NULL,
  `obj_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rol_id`,`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `rbac_pa`
#

INSERT INTO `rbac_pa` VALUES (5, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1);
INSERT INTO `rbac_pa` VALUES (3, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 7);
INSERT INTO `rbac_pa` VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 8);
INSERT INTO `rbac_pa` VALUES (3, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 8);
INSERT INTO `rbac_pa` VALUES (14, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1);
INSERT INTO `rbac_pa` VALUES (3, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 12);
INSERT INTO `rbac_pa` VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1);
INSERT INTO `rbac_pa` VALUES (3, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1);
INSERT INTO `rbac_pa` VALUES (5, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 12);
INSERT INTO `rbac_pa` VALUES (4, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 12);
INSERT INTO `rbac_pa` VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 7);



#
# Tabellenstruktur für Tabelle `rbac_ta`
#

CREATE TABLE `rbac_ta` (
  `typ_id` smallint(6) NOT NULL default '0',
  `ops_id` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`typ_id`,`ops_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `rbac_ta`
#

INSERT INTO `rbac_ta` VALUES (15, 1);
INSERT INTO `rbac_ta` VALUES (15, 2);
INSERT INTO `rbac_ta` VALUES (15, 3);
INSERT INTO `rbac_ta` VALUES (15, 4);
INSERT INTO `rbac_ta` VALUES (15, 5);
INSERT INTO `rbac_ta` VALUES (15, 6);
INSERT INTO `rbac_ta` VALUES (15, 7);
INSERT INTO `rbac_ta` VALUES (15, 8);
INSERT INTO `rbac_ta` VALUES (16, 1);
INSERT INTO `rbac_ta` VALUES (16, 2);
INSERT INTO `rbac_ta` VALUES (16, 3);
INSERT INTO `rbac_ta` VALUES (16, 4);
INSERT INTO `rbac_ta` VALUES (16, 5);
INSERT INTO `rbac_ta` VALUES (16, 6);
INSERT INTO `rbac_ta` VALUES (17, 1);
INSERT INTO `rbac_ta` VALUES (17, 2);
INSERT INTO `rbac_ta` VALUES (17, 3);
INSERT INTO `rbac_ta` VALUES (17, 4);
INSERT INTO `rbac_ta` VALUES (17, 5);
INSERT INTO `rbac_ta` VALUES (17, 6);
INSERT INTO `rbac_ta` VALUES (17, 7);
INSERT INTO `rbac_ta` VALUES (17, 8);
INSERT INTO `rbac_ta` VALUES (18, 5);
INSERT INTO `rbac_ta` VALUES (19, 1);
INSERT INTO `rbac_ta` VALUES (19, 2);
INSERT INTO `rbac_ta` VALUES (19, 3);
INSERT INTO `rbac_ta` VALUES (19, 4);
INSERT INTO `rbac_ta` VALUES (19, 11);
INSERT INTO `rbac_ta` VALUES (19, 12);
INSERT INTO `rbac_ta` VALUES (20, 1);
INSERT INTO `rbac_ta` VALUES (20, 2);
INSERT INTO `rbac_ta` VALUES (20, 3);
INSERT INTO `rbac_ta` VALUES (20, 4);
INSERT INTO `rbac_ta` VALUES (20, 5);
INSERT INTO `rbac_ta` VALUES (20, 6);
INSERT INTO `rbac_ta` VALUES (20, 7);
INSERT INTO `rbac_ta` VALUES (20, 8);
INSERT INTO `rbac_ta` VALUES (21, 1);
INSERT INTO `rbac_ta` VALUES (21, 2);
INSERT INTO `rbac_ta` VALUES (21, 3);
INSERT INTO `rbac_ta` VALUES (21, 4);
INSERT INTO `rbac_ta` VALUES (21, 5);
INSERT INTO `rbac_ta` VALUES (22, 1);
INSERT INTO `rbac_ta` VALUES (22, 2);
INSERT INTO `rbac_ta` VALUES (22, 3);
INSERT INTO `rbac_ta` VALUES (22, 4);
INSERT INTO `rbac_ta` VALUES (22, 6);
INSERT INTO `rbac_ta` VALUES (23, 1);
INSERT INTO `rbac_ta` VALUES (23, 2);
INSERT INTO `rbac_ta` VALUES (23, 3);
INSERT INTO `rbac_ta` VALUES (23, 4);
INSERT INTO `rbac_ta` VALUES (23, 5);
INSERT INTO `rbac_ta` VALUES (23, 6);
INSERT INTO `rbac_ta` VALUES (24, 1);
INSERT INTO `rbac_ta` VALUES (24, 2);
INSERT INTO `rbac_ta` VALUES (24, 3);
INSERT INTO `rbac_ta` VALUES (24, 4);
INSERT INTO `rbac_ta` VALUES (25, 5);
INSERT INTO `rbac_ta` VALUES (27, 5);
INSERT INTO `rbac_ta` VALUES (28, 1);
INSERT INTO `rbac_ta` VALUES (28, 2);
INSERT INTO `rbac_ta` VALUES (28, 3);
INSERT INTO `rbac_ta` VALUES (28, 4);
INSERT INTO `rbac_ta` VALUES (30, 5);
INSERT INTO `rbac_ta` VALUES (31, 1);
INSERT INTO `rbac_ta` VALUES (31, 2);
INSERT INTO `rbac_ta` VALUES (31, 3);
INSERT INTO `rbac_ta` VALUES (31, 4);
INSERT INTO `rbac_ta` VALUES (31, 5);
INSERT INTO `rbac_ta` VALUES (31, 6);
INSERT INTO `rbac_ta` VALUES (32, 1);
INSERT INTO `rbac_ta` VALUES (32, 2);
INSERT INTO `rbac_ta` VALUES (32, 3);
INSERT INTO `rbac_ta` VALUES (32, 4);
INSERT INTO `rbac_ta` VALUES (32, 5);
INSERT INTO `rbac_ta` VALUES (32, 6);
INSERT INTO `rbac_ta` VALUES (32, 7);
INSERT INTO `rbac_ta` VALUES (32, 8);
INSERT INTO `rbac_ta` VALUES (33, 1);
INSERT INTO `rbac_ta` VALUES (33, 2);
INSERT INTO `rbac_ta` VALUES (33, 3);
INSERT INTO `rbac_ta` VALUES (33, 4);
INSERT INTO `rbac_ta` VALUES (34, 1);
INSERT INTO `rbac_ta` VALUES (34, 2);
INSERT INTO `rbac_ta` VALUES (34, 3);
INSERT INTO `rbac_ta` VALUES (34, 4);
INSERT INTO `rbac_ta` VALUES (34, 5);
INSERT INTO `rbac_ta` VALUES (34, 6);
INSERT INTO `rbac_ta` VALUES (34, 7);
INSERT INTO `rbac_ta` VALUES (34, 8);
INSERT INTO `rbac_ta` VALUES (37, 1);
INSERT INTO `rbac_ta` VALUES (37, 2);
INSERT INTO `rbac_ta` VALUES (37, 3);
INSERT INTO `rbac_ta` VALUES (37, 4);
INSERT INTO `rbac_ta` VALUES (37, 5);
INSERT INTO `rbac_ta` VALUES (37, 6);
INSERT INTO `rbac_ta` VALUES (37, 9);
INSERT INTO `rbac_ta` VALUES (37, 10);



#
# Tabellenstruktur für Tabelle `rbac_templates`
#

CREATE TABLE `rbac_templates` (
  `rol_id` int(11) NOT NULL default '0',
  `type` char(5) NOT NULL default '',
  `ops_id` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  KEY `rol_id` (`rol_id`),
  KEY `type` (`type`),
  KEY `ops_id` (`ops_id`),
  KEY `parent` (`parent`)
) TYPE=MyISAM;

#
# Daten für Tabelle `rbac_templates`
#

INSERT INTO `rbac_templates` VALUES (5, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'mail', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'rolf', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'rolf', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'mail', 4, 8);
INSERT INTO `rbac_templates` VALUES (5, 'mail', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'rolf', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'rolf', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'mail', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'mail', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'mail', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 8, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 6, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 5, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 8, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 6, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 5, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 4, 8);
INSERT INTO `rbac_templates` VALUES (4, 'mail', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'mail', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'cat', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'cat', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'slm', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'slm', 7, 8);
INSERT INTO `rbac_templates` VALUES (4, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'lm', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'lm', 7, 8);
INSERT INTO `rbac_templates` VALUES (4, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 6, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 5, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 4, 8);
INSERT INTO `rbac_templates` VALUES (5, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 8, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 6, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 5, 8);
INSERT INTO `rbac_templates` VALUES (3, 'frm', 4, 8);
INSERT INTO `rbac_templates` VALUES (4, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'glo', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'glo', 7, 8);
INSERT INTO `rbac_templates` VALUES (4, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (83, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'frm', 5, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'crs', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'crs', 7, 8);
INSERT INTO `rbac_templates` VALUES (4, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'cat', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'crs', 5, 8);
INSERT INTO `rbac_templates` VALUES (80, 'crs', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 5, 8);
INSERT INTO `rbac_templates` VALUES (5, 'cat', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'grp', 5, 8);
INSERT INTO `rbac_templates` VALUES (4, 'frm', 4, 8);
INSERT INTO `rbac_templates` VALUES (4, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 5, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 5, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (14, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 5, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'rolf', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'rolf', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'rolf', 4, 8);
INSERT INTO `rbac_templates` VALUES (4, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 6, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 5, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 4, 8);
INSERT INTO `rbac_templates` VALUES (81, 'crs', 5, 8);
INSERT INTO `rbac_templates` VALUES (81, 'crs', 6, 8);
INSERT INTO `rbac_templates` VALUES (81, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 2, 8);
INSERT INTO `rbac_templates` VALUES (81, 'frm', 5, 8);
INSERT INTO `rbac_templates` VALUES (81, 'frm', 6, 8);
INSERT INTO `rbac_templates` VALUES (81, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 4, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (81, 'slm', 5, 8);
INSERT INTO `rbac_templates` VALUES (81, 'slm', 6, 8);
INSERT INTO `rbac_templates` VALUES (81, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (14, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (14, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (82, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (83, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (83, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 5, 8);
INSERT INTO `rbac_templates` VALUES (14, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'frm', 5, 8);



#
# Tabellenstruktur für Tabelle `rbac_ua`
#

CREATE TABLE `rbac_ua` (
  `usr_id` int(11) NOT NULL default '0',
  `rol_id` int(11) NOT NULL default '0',
  `default_role` varchar(100) default NULL,
  PRIMARY KEY  (`usr_id`,`rol_id`),
  KEY `usr_id` (`usr_id`),
  KEY `rol_id` (`rol_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `rbac_ua`
#

INSERT INTO `rbac_ua` VALUES (6, 2, 'y');
INSERT INTO `rbac_ua` VALUES (13, 14, 'y');



#
# Tabellenstruktur für Tabelle `role_data`
#

CREATE TABLE `role_data` (
  `role_id` int(11) NOT NULL default '0',
  `allow_register` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`role_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `role_data`
#

INSERT INTO `role_data` VALUES (2, 0);
INSERT INTO `role_data` VALUES (3, 0);
INSERT INTO `role_data` VALUES (4, 0);
INSERT INTO `role_data` VALUES (5, 1);
INSERT INTO `role_data` VALUES (14, 0);



#
# Tabellenstruktur für Tabelle `sc_item`
#

CREATE TABLE `sc_item` (
  `obj_id` int(11) NOT NULL default '0',
  `import_id` varchar(200) default NULL,
  `identifierref` varchar(200) default NULL,
  `isvisible` enum('','true','false') default '',
  `parameters` text,
  `prereq_type` varchar(200) default NULL,
  `prerequisites` text,
  `maxtimeallowed` varchar(30) default NULL,
  `timelimitaction` varchar(30) default NULL,
  `datafromlms` text,
  `masteryscore` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `sc_item`
#




#
# Tabellenstruktur für Tabelle `sc_manifest`
#

CREATE TABLE `sc_manifest` (
  `obj_id` int(11) NOT NULL default '0',
  `import_id` varchar(200) default NULL,
  `version` varchar(200) default NULL,
  `xml_base` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `sc_manifest`
#




#
# Tabellenstruktur für Tabelle `sc_organization`
#

CREATE TABLE `sc_organization` (
  `obj_id` int(11) NOT NULL default '0',
  `import_id` varchar(200) default NULL,
  `structure` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `sc_organization`
#




#
# Tabellenstruktur für Tabelle `sc_organizations`
#

CREATE TABLE `sc_organizations` (
  `obj_id` int(11) NOT NULL default '0',
  `default_organization` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `sc_organizations`
#




#
# Tabellenstruktur für Tabelle `sc_resource`
#

CREATE TABLE `sc_resource` (
  `obj_id` int(11) NOT NULL default '0',
  `import_id` varchar(200) default NULL,
  `resourcetype` varchar(30) default NULL,
  `scormtype` enum('sco','asset') default NULL,
  `href` varchar(250) default NULL,
  `xml_base` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `sc_resource`
#




#
# Tabellenstruktur für Tabelle `sc_resource_dependency`
#

CREATE TABLE `sc_resource_dependency` (
  `id` int(11) NOT NULL auto_increment,
  `res_id` int(11) default NULL,
  `identifierref` varchar(200) default NULL,
  `nr` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `sc_resource_dependency`
#




#
# Tabellenstruktur für Tabelle `sc_resource_file`
#

CREATE TABLE `sc_resource_file` (
  `id` int(11) NOT NULL auto_increment,
  `res_id` int(11) default NULL,
  `href` text,
  `nr` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `sc_resource_file`
#




#
# Tabellenstruktur für Tabelle `sc_resources`
#

CREATE TABLE `sc_resources` (
  `obj_id` int(11) NOT NULL default '0',
  `xml_base` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `sc_resources`
#




#
# Tabellenstruktur für Tabelle `scorm_object`
#

CREATE TABLE `scorm_object` (
  `obj_id` int(11) NOT NULL auto_increment,
  `title` varchar(200) default NULL,
  `type` char(3) default NULL,
  `slm_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `scorm_object`
#




#
# Tabellenstruktur für Tabelle `scorm_tracking`
#

CREATE TABLE `scorm_tracking` (
  `sc_item_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `entry` enum('ab-initio','resume','') NOT NULL default 'ab-initio',
  `exit` enum('time-out','suspend','logout','') NOT NULL default 'time-out',
  `lesson_location` varchar(255) NOT NULL default '',
  `credit` enum('credit','no-credit') NOT NULL default 'credit',
  `raw` decimal(10,0) NOT NULL default '0',
  `session_time` time NOT NULL default '00:00:00',
  `total_time` time NOT NULL default '00:00:00',
  `comments` text NOT NULL,
  `lesson_status` enum('passed','completed','failed','incomplete','browsed','not attempted') NOT NULL default 'passed',
  `launch_data` text NOT NULL,
  `suspend_data` text NOT NULL,
  `mastery_score` decimal(10,0) NOT NULL default '0',
  PRIMARY KEY  (`sc_item_id`,`sc_item_id`,`usr_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `scorm_tracking`
#




#
# Tabellenstruktur für Tabelle `scorm_tree`
#

CREATE TABLE `scorm_tree` (
  `slm_id` int(11) NOT NULL default '0',
  `child` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned default NULL,
  `lft` int(11) unsigned NOT NULL default '0',
  `rgt` int(11) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  KEY `child` (`child`),
  KEY `parent` (`parent`)
) TYPE=MyISAM;

#
# Daten für Tabelle `scorm_tree`
#




#
# Tabellenstruktur für Tabelle `settings`
#

CREATE TABLE `settings` (
  `keyword` char(50) NOT NULL default '',
  `value` char(50) NOT NULL default '',
  PRIMARY KEY  (`keyword`)
) TYPE=MyISAM;

#
# Daten für Tabelle `settings`
#

INSERT INTO `settings` VALUES ('admin_position', '');
INSERT INTO `settings` VALUES ('admin_title', '');
INSERT INTO `settings` VALUES ('babylon_path', '');
INSERT INTO `settings` VALUES ('convert_path', '');
INSERT INTO `settings` VALUES ('crs_enable', '');
INSERT INTO `settings` VALUES ('db_version', '35');
INSERT INTO `settings` VALUES ('admin_institution', '');
INSERT INTO `settings` VALUES ('group_file_sharing', '');
INSERT INTO `settings` VALUES ('ilias_version', '3.0.0_alpha5');
INSERT INTO `settings` VALUES ('inst_info', '');
INSERT INTO `settings` VALUES ('inst_name', '');
INSERT INTO `settings` VALUES ('java_path', '');
INSERT INTO `settings` VALUES ('language', 'en');
INSERT INTO `settings` VALUES ('ldap_basedn', '');
INSERT INTO `settings` VALUES ('ldap_enable', '');
INSERT INTO `settings` VALUES ('ldap_port', '');
INSERT INTO `settings` VALUES ('ldap_server', '');
INSERT INTO `settings` VALUES ('news', '');
INSERT INTO `settings` VALUES ('payment_system', '');
INSERT INTO `settings` VALUES ('error_recipient', '');
INSERT INTO `settings` VALUES ('pub_section', '');
INSERT INTO `settings` VALUES ('feedback_recipient', '');
INSERT INTO `settings` VALUES ('unzip_path', '');
INSERT INTO `settings` VALUES ('anonymous_user_id', '13');
INSERT INTO `settings` VALUES ('zip_path', '');
INSERT INTO `settings` VALUES ('enable_registration', '1');
INSERT INTO `settings` VALUES ('system_role_id', '2');



#
# Tabellenstruktur für Tabelle `style_parameter`
#

CREATE TABLE `style_parameter` (
  `id` int(11) NOT NULL auto_increment,
  `style_id` int(11) NOT NULL default '0',
  `tag` varchar(100) default NULL,
  `class` varchar(100) default NULL,
  `parameter` varchar(100) default NULL,
  `value` varchar(100) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `style_parameter`
#




#
# Tabellenstruktur für Tabelle `tree`
#

CREATE TABLE `tree` (
  `tree` int(10) NOT NULL default '0',
  `child` int(10) unsigned NOT NULL default '0',
  `parent` int(10) unsigned default NULL,
  `lft` int(10) unsigned NOT NULL default '0',
  `rgt` int(10) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  KEY `child` (`child`),
  KEY `parent` (`parent`)
) TYPE=MyISAM;

#
# Daten für Tabelle `tree`
#

INSERT INTO `tree` VALUES (1, 1, 0, 1, 14, 1);
INSERT INTO `tree` VALUES (1, 7, 9, 5, 6, 3);
INSERT INTO `tree` VALUES (1, 8, 9, 7, 8, 3);
INSERT INTO `tree` VALUES (1, 9, 1, 2, 13, 2);
INSERT INTO `tree` VALUES (1, 10, 9, 9, 10, 3);
INSERT INTO `tree` VALUES (1, 11, 9, 11, 12, 3);
INSERT INTO `tree` VALUES (1, 12, 9, 3, 4, 3);



#
# Tabellenstruktur für Tabelle `usr_data`
#

CREATE TABLE `usr_data` (
  `usr_id` int(10) unsigned NOT NULL default '0',
  `login` varchar(32) NOT NULL default '',
  `passwd` varchar(32) NOT NULL default '',
  `firstname` varchar(32) NOT NULL default '',
  `lastname` varchar(32) NOT NULL default '',
  `title` varchar(32) NOT NULL default '',
  `gender` enum('m','f') NOT NULL default 'm',
  `email` varchar(40) NOT NULL default 'here your email',
  `institution` varchar(80) default NULL,
  `street` varchar(40) default NULL,
  `city` varchar(40) default NULL,
  `zipcode` varchar(10) default NULL,
  `country` varchar(40) default NULL,
  `phone` varchar(40) default NULL,
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `hobby` text NOT NULL,
  PRIMARY KEY  (`usr_id`),
  KEY `login` (`login`,`passwd`)
) TYPE=MyISAM;

#
# Daten für Tabelle `usr_data`
#

INSERT INTO `usr_data` VALUES (6, 'root', 'dfa8327f5bfa4c672a04f9b38e348a70', 'root', 'user', '', 'm', 'ilias@yourserver.com', '', '', '', '', '', '', '2003-09-30 19:49:44', '2003-09-30 19:50:01', '0000-00-00 00:00:00', '');
INSERT INTO `usr_data` VALUES (13, 'anonymous', '294de3557d9d00b3d2d8a1e6aab028cf', 'anonymous', 'anonymous', '', 'm', 'nomail', NULL, NULL, NULL, NULL, NULL, NULL, '2003-08-15 11:03:36', '2003-08-15 10:07:30', '2003-08-15 10:07:30', '');



#
# Tabellenstruktur für Tabelle `usr_pref`
#

CREATE TABLE `usr_pref` (
  `usr_id` int(10) unsigned NOT NULL default '0',
  `keyword` char(40) NOT NULL default '',
  `value` char(40) default NULL,
  PRIMARY KEY  (`usr_id`,`keyword`)
) TYPE=MyISAM;

#
# Daten für Tabelle `usr_pref`
#

INSERT INTO `usr_pref` VALUES (6, 'style', 'blueshadow');
INSERT INTO `usr_pref` VALUES (6, 'skin', 'default');
INSERT INTO `usr_pref` VALUES (6, 'public_zip', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_upload', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_street', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_profile', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_phone', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_institution', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_hobby', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_email', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_country', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_city', 'n');
INSERT INTO `usr_pref` VALUES (6, 'language', 'en');



#
# Tabellenstruktur für Tabelle `usr_search`
#

CREATE TABLE `usr_search` (
  `usr_id` int(11) NOT NULL default '0',
  `search_result` text,
  PRIMARY KEY  (`usr_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `usr_search`
#




#
# Tabellenstruktur für Tabelle `usr_session`
#

CREATE TABLE `usr_session` (
  `session_id` varchar(32) NOT NULL default '',
  `expires` int(11) NOT NULL default '0',
  `data` text NOT NULL,
  `ctime` int(11) NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`session_id`),
  KEY `expires` (`expires`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `usr_session`
#

INSERT INTO `usr_session` VALUES ('9fd182081849dd88829a477f43d187e2', 1064946013, 'post_vars|a:0:{}referer|s:32:"/ilias3/adm_object.php?ref_id=11";auth|a:4:{s:10:"registered";b:1;s:8:"username";s:4:"root";s:9:"timestamp";i:1064944184;s:4:"idle";i:1064944212;}AccountId|s:1:"6";RoleId|a:1:{i:0;s:1:"2";}expand|a:2:{i:0;s:1:"1";i:1;s:1:"9";}', 1064944213, 6);



#
# Tabellenstruktur für Tabelle `xmlnestedset`
#

CREATE TABLE `xmlnestedset` (
  `ns_book_fk` int(11) NOT NULL default '0',
  `ns_type` char(50) NOT NULL default '',
  `ns_tag_fk` int(11) default NULL,
  `ns_l` int(11) default NULL,
  `ns_r` int(11) default NULL,
  KEY `ns_tag_fk` (`ns_tag_fk`),
  KEY `ns_l` (`ns_l`),
  KEY `ns_r` (`ns_r`),
  KEY `ns_book_fk` (`ns_book_fk`)
) TYPE=MyISAM;

#
# Daten für Tabelle `xmlnestedset`
#




#
# Tabellenstruktur für Tabelle `xmlparam`
#

CREATE TABLE `xmlparam` (
  `tag_fk` int(11) NOT NULL default '0',
  `param_name` char(50) NOT NULL default '',
  `param_value` char(255) NOT NULL default '',
  KEY `tag_fk` (`tag_fk`)
) TYPE=MyISAM;

#
# Daten für Tabelle `xmlparam`
#




#
# Tabellenstruktur für Tabelle `xmltags`
#

CREATE TABLE `xmltags` (
  `tag_pk` int(11) NOT NULL auto_increment,
  `tag_depth` int(11) NOT NULL default '0',
  `tag_name` char(50) NOT NULL default '',
  PRIMARY KEY  (`tag_pk`)
) TYPE=MyISAM;

#
# Daten für Tabelle `xmltags`
#




#
# Tabellenstruktur für Tabelle `xmlvalue`
#

CREATE TABLE `xmlvalue` (
  `tag_value_pk` int(11) NOT NULL auto_increment,
  `tag_fk` int(11) NOT NULL default '0',
  `tag_value` text NOT NULL,
  PRIMARY KEY  (`tag_value_pk`),
  KEY `tag_fk` (`tag_fk`)
) TYPE=MyISAM;

#
# Daten für Tabelle `xmlvalue`
#

