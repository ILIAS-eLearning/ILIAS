# phpMyAdmin MySQL-Dump
# version 2.5.0
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Erstellungszeit: 15. August 2003 um 13:53
# Server Version: 4.0.5
# PHP-Version: 4.3.2
# Datenbank: `ilias3`
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `addressbook`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `addressbook` (
  `addr_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `login` varchar(40) default NULL,
  `firstname` varchar(40) default NULL,
  `lastname` varchar(40) default NULL,
  `email` varchar(40) default NULL,
  PRIMARY KEY  (`addr_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `addressbook`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `bookmark_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `bookmark_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `target` varchar(200) NOT NULL default '',
  `type` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`user_id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Daten für Tabelle `bookmark_data`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `bookmark_tree`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `frm_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `frm_data`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `frm_posts`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `frm_posts`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `frm_posts_tree`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `frm_posts_tree`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `frm_threads`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `frm_threads`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `grp_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `grp_data` (
  `grp_id` int(11) NOT NULL default '0',
  `status` int(11) default NULL,
  PRIMARY KEY  (`grp_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `grp_data`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `grp_tree`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `grp_tree` (
  `tree` int(11) NOT NULL default '0',
  `child` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned NOT NULL default '0',
  `lft` int(11) unsigned NOT NULL default '0',
  `rgt` int(11) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  `perm` tinyint(1) unsigned NOT NULL default '0',
  `ref_id` int(11) default NULL,
  PRIMARY KEY  (`tree`,`child`,`parent`)
) TYPE=MyISAM;

#
# Daten für Tabelle `grp_tree`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `learning_module`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lm_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lm_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `type` char(2) NOT NULL default '',
  `lm_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Daten für Tabelle `lm_data`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lm_page_object`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lm_page_object` (
  `page_id` int(11) NOT NULL default '0',
  `parent_id` int(11) default NULL,
  `content` longtext NOT NULL,
  `parent_type` varchar(4) default 'lm',
  PRIMARY KEY  (`page_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lm_page_object`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lm_tree`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lng_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
INSERT INTO `lng_data` VALUES ('forum', 'forums_respond', 'en', 0x526573706f6e6420746f20616e2041727469636c65);
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
INSERT INTO `lng_data` VALUES ('content', 'st', 'en', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('content', 'par', 'en', 0x506172616772617068);
INSERT INTO `lng_data` VALUES ('content', 'pg', 'en', 0x50616765);
INSERT INTO `lng_data` VALUES ('content', 'cont_xml_base', 'en', 0x786d6c3a62617365);
INSERT INTO `lng_data` VALUES ('content', 'cont_wysiwyg', 'en', 0x436f6e74656e742057797369777967);
INSERT INTO `lng_data` VALUES ('content', 'cont_width', 'en', 0x5769647468);
INSERT INTO `lng_data` VALUES ('content', 'cont_version', 'en', 0x76657273696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_top', 'en', 0x546f70);
INSERT INTO `lng_data` VALUES ('content', 'cont_time_limit_action', 'en', 0x61646c63703a74696d656c696d6974616374696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_terms', 'en', 0x5465726d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_term', 'en', 0x5465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_width', 'en', 0x5461626c65205769647468);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_cellspacing', 'en', 0x5461626c652043656c6c2053706163696e67);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_cellpadding', 'en', 0x5461626c652043656c6c2050616464696e67);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_border', 'en', 0x5461626c6520426f72646572);
INSERT INTO `lng_data` VALUES ('content', 'cont_table', 'en', 0x5461626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_subchapters', 'en', 0x5375626368617074657273);
INSERT INTO `lng_data` VALUES ('content', 'cont_structure', 'en', 0x737472756374757265);
INSERT INTO `lng_data` VALUES ('content', 'cont_std_view', 'en', 0x5374616e646172642056696577);
INSERT INTO `lng_data` VALUES ('content', 'cont_st_title', 'en', 0x43686170746572205469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_width', 'en', 0x536574205769647468);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_class', 'en', 0x53657420436c617373);
INSERT INTO `lng_data` VALUES ('content', 'cont_scorm_type', 'en', 0x61646c63703a73636f726d74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_title', 'en', 0x7469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_roman', 'en', 0x526f6d616e20692c2069692c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_resources', 'en', 0x5265736f7572636573);
INSERT INTO `lng_data` VALUES ('content', 'cont_resource_type', 'en', 0x74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_resource', 'en', 0x5265736f75726365);
INSERT INTO `lng_data` VALUES ('content', 'cont_reference', 'en', 0x5265666572656e6365);
INSERT INTO `lng_data` VALUES ('content', 'cont_ref_helptext', 'en', 0x28652e672e20687474703a2f2f7777772e7365727665722e6f72672f6d79696d6167652e6a706729);
INSERT INTO `lng_data` VALUES ('content', 'cont_preview', 'en', 0x50726576696577);
INSERT INTO `lng_data` VALUES ('content', 'cont_prerequisites', 'en', 0x61646c63703a70726572657175697369746573);
INSERT INTO `lng_data` VALUES ('content', 'cont_prereq_type', 'en', 0x61646c63703a707265726571756973697465732e74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_pg_title', 'en', 0x50616765205469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_pg_content', 'en', 0x5061676520436f6e74656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_parameters', 'en', 0x706172616d6574657273);
INSERT INTO `lng_data` VALUES ('content', 'cont_parameter', 'en', 0x506172616d65746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_pages', 'en', 0x5061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_page_header', 'en', 0x5061676520486561646572);
INSERT INTO `lng_data` VALUES ('content', 'cont_orig_size', 'en', 0x4f726967696e616c2053697a65);
INSERT INTO `lng_data` VALUES ('content', 'cont_organizations', 'en', 0x4f7267616e697a6174696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_organization', 'en', 0x4f7267616e697a6174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_order', 'en', 0x4f726465722054797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_rows', 'en', 0x4e756d626572206f6620526f7773);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_items', 'en', 0x4e756d626572206f66204974656d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_cols', 'en', 0x4e756d626572206f6620436f6c756d6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_none', 'en', 0x4e6f6e65);
INSERT INTO `lng_data` VALUES ('content', 'cont_max_time_allowed', 'en', 0x61646c63703a6d617874696d65616c6c6f776564);
INSERT INTO `lng_data` VALUES ('content', 'cont_mastery_score', 'en', 0x61646c63703a6d61737465727973636f7265);
INSERT INTO `lng_data` VALUES ('content', 'cont_manifest', 'en', 0x4d616e6966657374);
INSERT INTO `lng_data` VALUES ('content', 'cont_lm_properties', 'en', 0x4c6561726e696e67204d6f64756c652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_list_properties', 'en', 0x4c6973742050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_item', 'en', 0x4974656d);
INSERT INTO `lng_data` VALUES ('content', 'cont_is_visible', 'en', 0x697376697369626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_table', 'en', 0x496e73657274205461626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_par', 'en', 0x496e7365727420506172616772617068);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_mob', 'en', 0x496e73657274204d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_list', 'en', 0x496e73657274204c697374);
INSERT INTO `lng_data` VALUES ('content', 'cont_import_id', 'en', 0x6964656e746966696572);
INSERT INTO `lng_data` VALUES ('content', 'cont_id_ref', 'en', 0x6964656e746966696572726566);
INSERT INTO `lng_data` VALUES ('content', 'cont_href', 'en', 0x68726566);
INSERT INTO `lng_data` VALUES ('content', 'cont_height', 'en', 0x486569676874);
INSERT INTO `lng_data` VALUES ('content', 'cont_fullscreen', 'en', 0x46756c6c73637265656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_files', 'en', 0x46696c6573);
INSERT INTO `lng_data` VALUES ('content', 'cont_file', 'en', 0x46696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_tab_properties', 'en', 0x5461626c652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_par', 'en', 0x4564697420506172616772617068);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_properties', 'en', 0x45646974204d65646961204f626a6563742050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_alias_prop', 'en', 0x45646974204d65646961204f626a65637420496e7374616e63652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob', 'en', 0x45646974204d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_dependencies', 'en', 0x446570656e64656e63696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_def_organization', 'en', 0x64656661756c74);
INSERT INTO `lng_data` VALUES ('content', 'cont_def_layout', 'en', 0x44656661756c74204c61796f7574);
INSERT INTO `lng_data` VALUES ('content', 'cont_content', 'en', 0x436f6e74656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_data_from_lms', 'en', 0x61646c63703a6461746166726f6d6c6d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_characteristic', 'en', 0x4368617261637465726973746963);
INSERT INTO `lng_data` VALUES ('content', 'cont_chapters', 'en', 0x4368617074657273);
INSERT INTO `lng_data` VALUES ('content', 'cont_chap_and_pages', 'en', 0x436861707465727320616e64205061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_caption', 'en', 0x43617074696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_bottom', 'en', 0x426f74746f6d);
INSERT INTO `lng_data` VALUES ('content', 'cont_alphabetic', 'en', 0x416c706861626574696320612c20622c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_all_pages', 'en', 0x416c6c205061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_Unordered', 'en', 0x556e6f726465726564);
INSERT INTO `lng_data` VALUES ('content', 'cont_Roman', 'en', 0x526f6d616e20492c2049492c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Reference', 'en', 0x5265666572656e6365);
INSERT INTO `lng_data` VALUES ('content', 'cont_Number', 'en', 0x4e756d626572);
INSERT INTO `lng_data` VALUES ('content', 'cont_Mnemonic', 'en', 0x4d6e656d6f6e6963);
INSERT INTO `lng_data` VALUES ('content', 'cont_LocalFile', 'en', 0x4c6f63616c2046696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_List', 'en', 0x4c697374);
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline', 'en', 0x486561646c696e65);
INSERT INTO `lng_data` VALUES ('content', 'cont_Example', 'en', 0x4578616d706c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_Citation', 'en', 0x4369746174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Alphabetic', 'en', 0x416c706861626574696320412c20422c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Additional', 'en', 0x4164646974696f6e616c);
INSERT INTO `lng_data` VALUES ('common', 'your_message', 'en', 0x596f7572204d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'zipcode', 'en', 0x5a697020436f6465);
INSERT INTO `lng_data` VALUES ('common', 'you_may_add_local_roles', 'en', 0x596f75204d617920416464204c6f63616c20526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'write', 'en', 0x5772697465);
INSERT INTO `lng_data` VALUES ('common', 'yes', 'en', 0x596573);
INSERT INTO `lng_data` VALUES ('common', 'with', 'en', 0x77697468);
INSERT INTO `lng_data` VALUES ('common', 'who_is_online', 'en', 0x57686f206973206f6e6c696e653f);
INSERT INTO `lng_data` VALUES ('common', 'welcome', 'en', 0x57656c636f6d65);
INSERT INTO `lng_data` VALUES ('common', 'visits', 'en', 0x566973697473);
INSERT INTO `lng_data` VALUES ('common', 'visible_layers', 'en', 0x56697369626c65204c6179657273);
INSERT INTO `lng_data` VALUES ('common', 'view_content', 'en', 0x5669657720436f6e74656e74);
INSERT INTO `lng_data` VALUES ('common', 'view', 'en', 0x56696577);
INSERT INTO `lng_data` VALUES ('common', 'version', 'en', 0x56657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'value', 'en', 0x56616c7565);
INSERT INTO `lng_data` VALUES ('common', 'validate', 'en', 0x56616c6964617465);
INSERT INTO `lng_data` VALUES ('common', 'usrf', 'en', 0x5573657220466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'usr_style', 'en', 0x55736572205374796c65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_attachments', 'de', 0x4174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('mail', 'mail_addr_entries', 'de', 0x45696e7472c3a46765);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_type', 'de', 0x426974746520676562656e205369652064656e2054797020646572204e616368726963687420616e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_to_addressbook', 'de', 0x5a756d20416472657373627563682068696e7a7566c3bc67656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_subject', 'de', 0x426974746520676562656e205369652065696e656e20426574726566662065696e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_subfolder', 'de', 0x556e7465726f72646e65722068696e7a7566c3bc67656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_recipient', 'de', 0x426974746520676562656e205369652065696e656e20456d7066c3a46e6765722065696e);
INSERT INTO `lng_data` VALUES ('mail', 'linebreak', 'de', 0x5a65696c656e756d6272756368);
INSERT INTO `lng_data` VALUES ('mail', 'forward', 'de', 0x5765697465726c656974656e);
INSERT INTO `lng_data` VALUES ('mail', 'bc', 'de', 0x4243);
INSERT INTO `lng_data` VALUES ('mail', 'also_as_email', 'de', 0x4175636820616c7320452d4d61696c);
INSERT INTO `lng_data` VALUES ('forum', 'forums_your_reply', 'de', 0x4968726520416e74776f7274);
INSERT INTO `lng_data` VALUES ('forum', 'forums_topics_overview', 'de', 0x5468656d656ec3bc6265727369636874);
INSERT INTO `lng_data` VALUES ('forum', 'forums_threads_not_available', 'de', 0x4b65696e65205468656d656e2076657266c3bc67626172);
INSERT INTO `lng_data` VALUES ('forum', 'forums_threads', 'de', 0x5468656d656e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_new_entry', 'de', 0x4e65756573205468656d612077757264652065696e676574726167656e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_create_date', 'de', 0x45727374656c6c7420616d);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_create_from', 'de', 0x45727374656c6c7420766f6e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread', 'de', 0x5468656d61);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_articles', 'de', 0x4265697472c3a46765207a756d205468656d61);
INSERT INTO `lng_data` VALUES ('forum', 'forums_the_post', 'de', 0x42656974726167);
INSERT INTO `lng_data` VALUES ('forum', 'forums_respond', 'de', 0x42656974726167206265616e74776f7274656e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_posts_not_available', 'de', 0x4b65696e6520417274696b656c2076657266c3bc67626172);
INSERT INTO `lng_data` VALUES ('forum', 'forums_posts', 'de', 0x416c6c65204265697472c3a46765);
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_new_entry', 'de', 0x4e6575657220426569747261672077757264652065696e676574726167656e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_modified', 'de', 0x426569747261672077757264652062656172626569746574);
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_deleted', 'de', 0x426569747261672077757264652067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('forum', 'forums_overview', 'de', 0xc39c626572736963687420466f72656e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_not_available', 'de', 0x4b65696e6520466f72656e2076657266c3bc67626172);
INSERT INTO `lng_data` VALUES ('forum', 'forums_new_thread', 'de', 0x4e65756573205468656d61);
INSERT INTO `lng_data` VALUES ('forum', 'forums_new_entries', 'de', 0x4e65756520466f72656e2d45696e7472c3a46765);
INSERT INTO `lng_data` VALUES ('forum', 'forums_moderators', 'de', 0x4d6f64657261746f72656e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_last_post', 'de', 0x4c65747a7465722042656974726167);
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_delete_post', 'de', 0x53696e6420536965207369636865722c206461c39f20646965736572204265697472616720696e6b6c757369766520616c6c657220416e74776f7274656e2067656cc3b6736368742077657264656e20736f6c6c3f);
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_censor_post', 'de', 0x53696e6420536965207369636865722c206461c39f206469657365722042656974726167206e69636874206d65687220616e67657a656967742077657264656e20736f6c6c3f);
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_censor2_post', 'de', 0x53696e6420536965207369636865722c206461c39f20646965736572204265697472616720617563682077656974657268696e206e6963687420616e67657a656967742077657264656e20736f6c6c3f);
INSERT INTO `lng_data` VALUES ('forum', 'forums_edit_post', 'de', 0x42656974726167206265617262656974656e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_count_thr', 'de', 0x416e7a61686c205468656d656e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_count_art', 'de', 0x416e7a61686c204265697472c3a46765);
INSERT INTO `lng_data` VALUES ('forum', 'forums_count', 'de', 0x416e7a61686c20466f72656e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_available', 'de', 0x56657266c3bc676261726520466f72656e);
INSERT INTO `lng_data` VALUES ('forum', 'forums_articles', 'de', 0x4265697472c3a46765);
INSERT INTO `lng_data` VALUES ('forum', 'forums', 'de', 0x466f72656e);
INSERT INTO `lng_data` VALUES ('content', 'st', 'de', 0x4b61706974656c);
INSERT INTO `lng_data` VALUES ('content', 'pg', 'de', 0x5365697465);
INSERT INTO `lng_data` VALUES ('content', 'par', 'de', 0x41627361747a);
INSERT INTO `lng_data` VALUES ('content', 'cont_xml_base', 'de', 0x786d6c3a62617365);
INSERT INTO `lng_data` VALUES ('content', 'cont_wysiwyg', 'de', 0x496e68616c742057797369777967);
INSERT INTO `lng_data` VALUES ('content', 'cont_width', 'de', 0x427265697465);
INSERT INTO `lng_data` VALUES ('content', 'cont_version', 'de', 0x76657273696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_top', 'de', 0x4f62656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_time_limit_action', 'de', 0x61646c63703a74696d656c696d6974616374696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_terms', 'de', 0x5465726d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_term', 'de', 0x5465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_width', 'de', 0x546162656c6c656e627265697465);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_cellspacing', 'de', 0x41627374616e6420546162656c6c656e7a656c6c656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_cellpadding', 'de', 0x496e6e656e61627374616e6420546162656c6c656e7a656c6c656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_border', 'de', 0x546162656c6c656e72616e64);
INSERT INTO `lng_data` VALUES ('content', 'cont_table', 'de', 0x546162656c6c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_subchapters', 'de', 0x556e7465726b61706974656c);
INSERT INTO `lng_data` VALUES ('content', 'cont_structure', 'de', 0x537472756b747572);
INSERT INTO `lng_data` VALUES ('content', 'cont_std_view', 'de', 0x5374616e646172647369636874);
INSERT INTO `lng_data` VALUES ('content', 'cont_st_title', 'de', 0x4b61706974656c2d546974656c);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_width', 'de', 0x42726569746520666573746c6567656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_class', 'de', 0x4b6c6173736520666573746c6567656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_scorm_type', 'de', 0x61646c63703a73636f726d74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_title', 'de', 0x546974656c);
INSERT INTO `lng_data` VALUES ('content', 'cont_roman', 'de', 0x52c3b66d6973636820692c2069692c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_resource_type', 'de', 0x547970);
INSERT INTO `lng_data` VALUES ('content', 'cont_resources', 'de', 0x526573736f757263656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_resource', 'de', 0x526573736f75726365);
INSERT INTO `lng_data` VALUES ('content', 'cont_reference', 'de', 0x5265666572656e7a);
INSERT INTO `lng_data` VALUES ('content', 'cont_ref_helptext', 'de', 0x28652e672e20687474703a2f2f7777772e7365727665722e6f72672f6d79696d6167652e6a706729);
INSERT INTO `lng_data` VALUES ('content', 'cont_preview', 'de', 0x566f727363686175);
INSERT INTO `lng_data` VALUES ('content', 'cont_prerequisites', 'de', 0x61646c63703a70726572657175697369746573);
INSERT INTO `lng_data` VALUES ('content', 'cont_prereq_type', 'de', 0x61646c63703a707265726571756973697465732e74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_pg_title', 'de', 0x53656974656e746974656c);
INSERT INTO `lng_data` VALUES ('content', 'cont_pg_content', 'de', 0x53656974656e696e68616c74);
INSERT INTO `lng_data` VALUES ('content', 'cont_parameters', 'de', 0x506172616d65746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_parameter', 'de', 0x506172616d65746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_pages', 'de', 0x506167657353656974656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_page_header', 'de', 0x53656974656e746974656c);
INSERT INTO `lng_data` VALUES ('content', 'cont_orig_size', 'de', 0x4f726967696e616c6772c3b6c39f65);
INSERT INTO `lng_data` VALUES ('content', 'cont_organizations', 'de', 0x4f7267616e69736174696f6e656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_organization', 'de', 0x4f7267616e69736174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_order', 'de', 0x4175667ac3a4686c756e6773747970);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_rows', 'de', 0x5a61686c20646572205a65696c656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_items', 'de', 0x416e7a61686c206465722045696e7472c3a46765);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_cols', 'de', 0x5a61686c20646572205370616c74656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_none', 'de', 0x4b65696e65);
INSERT INTO `lng_data` VALUES ('content', 'cont_max_time_allowed', 'de', 0x61646c63703a6d617874696d65616c6c6f776564);
INSERT INTO `lng_data` VALUES ('content', 'cont_mastery_score', 'de', 0x61646c63703a6d61737465727973636f7265);
INSERT INTO `lng_data` VALUES ('content', 'cont_manifest', 'de', 0x4d616e6966657374);
INSERT INTO `lng_data` VALUES ('content', 'cont_lm_properties', 'de', 0x4c65726e6d6f64756c2d456967656e736368616674656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_list_properties', 'de', 0x4c697374656e2d456967656e736368616674656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_item', 'de', 0x4974656d);
INSERT INTO `lng_data` VALUES ('content', 'cont_is_visible', 'de', 0x697376697369626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_table', 'de', 0x546162656c6c652065696e66c3bc67656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_par', 'de', 0x41627361747a2065696e66c3bc67656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_mob', 'de', 0x4d656469616f626a656b74);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_list', 'de', 0x4c697374652065696e66c3bc67656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_import_id', 'de', 0x6964656e746966696572);
INSERT INTO `lng_data` VALUES ('content', 'cont_id_ref', 'de', 0x6964656e746966696572726566);
INSERT INTO `lng_data` VALUES ('content', 'cont_href', 'de', 0x68726566);
INSERT INTO `lng_data` VALUES ('content', 'cont_height', 'de', 0x48c3b66865);
INSERT INTO `lng_data` VALUES ('content', 'cont_fullscreen', 'de', 0x566f6c6c62696c64);
INSERT INTO `lng_data` VALUES ('content', 'cont_files', 'de', 0x4461746569656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_file', 'de', 0x4461746569);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_tab_properties', 'de', 0x546162656c6c656e2d456967656e736368616674656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_par', 'de', 0x41627361747a206265617262656974656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_properties', 'de', 0x456967656e736368616674656e20646573204d656469616f626a656b7473206265617262656974656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_alias_prop', 'de', 0x456967656e736368616674656e20646572204d656469616f626a656b742d496e7374616e7a206265617262656974656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob', 'de', 0x45646974204d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_dependencies', 'de', 0x446570656e64656e63696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_def_organization', 'de', 0x5374616e64617264);
INSERT INTO `lng_data` VALUES ('content', 'cont_def_layout', 'de', 0x5374616e646172642d4c61796f7574);
INSERT INTO `lng_data` VALUES ('content', 'cont_data_from_lms', 'de', 0x61646c63703a6461746166726f6d6c6d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_content', 'de', 0x496e68616c74);
INSERT INTO `lng_data` VALUES ('content', 'cont_characteristic', 'de', 0x417274);
INSERT INTO `lng_data` VALUES ('content', 'cont_chapters', 'de', 0x4b61706974656c);
INSERT INTO `lng_data` VALUES ('content', 'cont_chap_and_pages', 'de', 0x4b61706974656c20756e642053656974656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_caption', 'de', 0x42696c64756e74657273636872696674);
INSERT INTO `lng_data` VALUES ('content', 'cont_bottom', 'de', 0x556e74656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_alphabetic', 'de', 0x416c7068616265746973636820612c20622c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_all_pages', 'de', 0x416c6c652053656974656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Unordered', 'de', 0x556e67656f72646e6574);
INSERT INTO `lng_data` VALUES ('content', 'cont_Roman', 'de', 0x52c3b66d6973636820492c2049492c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Reference', 'de', 0x5265666572656e6365);
INSERT INTO `lng_data` VALUES ('content', 'cont_Number', 'de', 0x4e756d6d6572);
INSERT INTO `lng_data` VALUES ('content', 'cont_Mnemonic', 'de', 0x4d65726b7361747a);
INSERT INTO `lng_data` VALUES ('content', 'cont_LocalFile', 'de', 0x4c6f63616c2046696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_List', 'de', 0x4c6973744c69737465);
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline', 'de', 0xc39c62657273636872696674);
INSERT INTO `lng_data` VALUES ('content', 'cont_Example', 'de', 0x426569737069656c);
INSERT INTO `lng_data` VALUES ('content', 'cont_Citation', 'de', 0x5a69746174);
INSERT INTO `lng_data` VALUES ('content', 'cont_Alphabetic', 'de', 0x416c7068616265746973636820412c20422c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Additional', 'de', 0x5a7573c3a4747a6c696368);
INSERT INTO `lng_data` VALUES ('common', 'zipcode', 'de', 0x506f73746c6569747a61686c);
INSERT INTO `lng_data` VALUES ('common', 'your_message', 'de', 0x49687265204e6163687269636874);
INSERT INTO `lng_data` VALUES ('common', 'you_may_add_local_roles', 'de', 0x536965206bc3b66e6e656e206c6f6b616c6520526f6c6c656e2068696e7a7566c3bc67656e);
INSERT INTO `lng_data` VALUES ('common', 'yes', 'de', 0x4a61);
INSERT INTO `lng_data` VALUES ('common', 'write', 'de', 0x53636872656962656e);
INSERT INTO `lng_data` VALUES ('common', 'with', 'de', 0x6d6974);
INSERT INTO `lng_data` VALUES ('common', 'who_is_online', 'de', 0x57657220697374204f6e6c696e653f);
INSERT INTO `lng_data` VALUES ('common', 'welcome', 'de', 0x57696c6c6b6f6d6d656e);
INSERT INTO `lng_data` VALUES ('common', 'visits', 'de', 0x42657375636865);
INSERT INTO `lng_data` VALUES ('common', 'visible_layers', 'de', 0x536963687462617265204562656e656e);
INSERT INTO `lng_data` VALUES ('common', 'view_content', 'de', 0x496e68616c74);
INSERT INTO `lng_data` VALUES ('common', 'view', 'de', 0x5a656967656e);
INSERT INTO `lng_data` VALUES ('common', 'version', 'de', 0x56657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'value', 'de', 0x57657274);
INSERT INTO `lng_data` VALUES ('common', 'validate', 'de', 0x56616c6964696572656e);
INSERT INTO `lng_data` VALUES ('common', 'usrf', 'de', 0x42656e75747a65726f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'usr_style', 'de', 0x5374696c);
INSERT INTO `lng_data` VALUES ('common', 'usr_skin', 'de', 0x42656e75747a65726f626572666cc3a4636865);
INSERT INTO `lng_data` VALUES ('common', 'usr_agreement_empty', 'de', 0x4469652056657265696e626172756e6720656e7468c3a46c74206b65696e656e2054657874);
INSERT INTO `lng_data` VALUES ('common', 'usr_agreement', 'de', 0x56657265696e626172756e67);
INSERT INTO `lng_data` VALUES ('common', 'usr', 'de', 0x42656e75747a6572);
INSERT INTO `lng_data` VALUES ('common', 'users_online', 'de', 0x416b746976652042656e75747a6572);
INSERT INTO `lng_data` VALUES ('common', 'users', 'de', 0x42656e75747a6572);
INSERT INTO `lng_data` VALUES ('common', 'username', 'de', 0x42656e75747a65726e616d65);
INSERT INTO `lng_data` VALUES ('common', 'userdata', 'de', 0x42656e75747a6572646174656e);
INSERT INTO `lng_data` VALUES ('common', 'user_language', 'de', 0x42656e75747a657273707261636865);
INSERT INTO `lng_data` VALUES ('common', 'user_deleted', 'de', 0x42656e75747a65722067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('common', 'user_assignment', 'de', 0x42656e75747a65727a7577656973756e67);
INSERT INTO `lng_data` VALUES ('common', 'user_added', 'de', 0x42656e75747a657220616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'user', 'de', 0x42656e75747a6572);
INSERT INTO `lng_data` VALUES ('common', 'url_description', 'de', 0x55524c20426573636872656962756e67);
INSERT INTO `lng_data` VALUES ('common', 'url', 'de', 0x55524c);
INSERT INTO `lng_data` VALUES ('common', 'upload', 'de', 0x486f63686c6164656e);
INSERT INTO `lng_data` VALUES ('common', 'update_language', 'de', 0x53707261636865207570646174656e);
INSERT INTO `lng_data` VALUES ('common', 'update_applied', 'de', 0x446174656e62616e6b757064617465206572666f6c677265696368);
INSERT INTO `lng_data` VALUES ('common', 'unread', 'de', 0x556e67656c6573656e);
INSERT INTO `lng_data` VALUES ('common', 'unknown', 'de', 0x554e42454b414e4e54);
INSERT INTO `lng_data` VALUES ('common', 'uninstalled', 'de', 0x6465696e7374616c6c696572742e);
INSERT INTO `lng_data` VALUES ('common', 'uid', 'de', 0x554944);
INSERT INTO `lng_data` VALUES ('common', 'uninstall', 'de', 0x4465696e7374616c6c696572656e);
INSERT INTO `lng_data` VALUES ('common', 'type_your_message_here', 'de', 0x53636872656962656e205369652049687265204e61636872696368742068696572);
INSERT INTO `lng_data` VALUES ('common', 'type', 'de', 0x547970);
INSERT INTO `lng_data` VALUES ('common', 'typ', 'de', 0x4f626a656b747479702d446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'txt_add_object_instruction2', 'de', 0x65696e662675756d6c3b67656e206d266f756d6c3b636874656e2e);
INSERT INTO `lng_data` VALUES ('common', 'txt_add_object_instruction1', 'de', 0x572661756d6c3b686c656e20536965206c696e6b732064696520506f736974696f6e2c20616e2064657220536965);
INSERT INTO `lng_data` VALUES ('common', 'treeview', 'de', 0x4261756d616e7369636874);
INSERT INTO `lng_data` VALUES ('common', 'trash', 'de', 0x5061706965726b6f7262);
INSERT INTO `lng_data` VALUES ('common', 'tpl_path', 'de', 0x54656d706c6174652d50666164);
INSERT INTO `lng_data` VALUES ('common', 'total', 'de', 0x476573616d74);
INSERT INTO `lng_data` VALUES ('common', 'today', 'de', 0x4865757465);
INSERT INTO `lng_data` VALUES ('common', 'to', 'de', 0x416e);
INSERT INTO `lng_data` VALUES ('common', 'title', 'de', 0x546974656c);
INSERT INTO `lng_data` VALUES ('common', 'time', 'de', 0x5a656974);
INSERT INTO `lng_data` VALUES ('common', 'test_intern', 'de', 0x496e7465726e65722054657374);
INSERT INTO `lng_data` VALUES ('common', 'system_message', 'de', 0x53797374656d6e6163687269636874);
INSERT INTO `lng_data` VALUES ('common', 'system_language', 'de', 0x53797374656d73707261636865);
INSERT INTO `lng_data` VALUES ('common', 'system_grp', 'de', 0x53797374656d677275707065);
INSERT INTO `lng_data` VALUES ('common', 'system_groups', 'de', 0x53797374656d6772757070656e);
INSERT INTO `lng_data` VALUES ('common', 'system_choose_language', 'de', 0x53797374656d737072616368652077c3a4686c656e);
INSERT INTO `lng_data` VALUES ('common', 'system', 'de', 0x53797374656d);
INSERT INTO `lng_data` VALUES ('common', 'summary', 'de', 0x5a7573616d6d656e66617373756e67);
INSERT INTO `lng_data` VALUES ('common', 'subscription', 'de', 0x41626f);
INSERT INTO `lng_data` VALUES ('common', 'subobjects', 'de', 0x556e7465722d4f626a656b7465);
INSERT INTO `lng_data` VALUES ('common', 'submit', 'de', 0x416273636869636b656e);
INSERT INTO `lng_data` VALUES ('common', 'subject', 'de', 0x42657472656666);
INSERT INTO `lng_data` VALUES ('common', 'subchapter_new', 'de', 0x4e6575657320556e7465726b61706974656c);
INSERT INTO `lng_data` VALUES ('common', 'structure', 'de', 0x476c6965646572756e67);
INSERT INTO `lng_data` VALUES ('common', 'subcat_name', 'de', 0x556e7465726b617465676f726965204e616d65);
INSERT INTO `lng_data` VALUES ('common', 'street', 'de', 0x53747261efac8265);
INSERT INTO `lng_data` VALUES ('common', 'stop_inheritance', 'de', 0x566572657262756e6720756e7465726272656368656e);
INSERT INTO `lng_data` VALUES ('common', 'step', 'de', 0x53636872697474);
INSERT INTO `lng_data` VALUES ('common', 'status', 'de', 0x537461747573);
INSERT INTO `lng_data` VALUES ('common', 'startpage', 'de', 0x53746172747365697465);
INSERT INTO `lng_data` VALUES ('common', 'sort_by_this_column', 'de', 0x4e61636820646965736572205370616c746520736f7274696572656e);
INSERT INTO `lng_data` VALUES ('common', 'smtp', 'de', 0x534d5450);
INSERT INTO `lng_data` VALUES ('common', 'slm_added', 'de', 0x53434f524d204c65726e6d6f64756c20616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'slm', 'de', 0x53434f524d204c65726e6d6f64756c);
INSERT INTO `lng_data` VALUES ('common', 'signature', 'de', 0x5369676e61747572);
INSERT INTO `lng_data` VALUES ('common', 'show_owner', 'de', 0x42657369747a6572696e666f);
INSERT INTO `lng_data` VALUES ('common', 'show_members', 'de', 0x4d6974676c696564657220616e7a656967656e);
INSERT INTO `lng_data` VALUES ('common', 'show_list', 'de', 0x4175666c697374756e6720616e7a656967656e);
INSERT INTO `lng_data` VALUES ('common', 'settings', 'de', 0x45696e7374656c6c756e67656e);
INSERT INTO `lng_data` VALUES ('common', 'set_online', 'de', 0x4f6e6c696e65207374656c6c656e);
INSERT INTO `lng_data` VALUES ('common', 'set_offline', 'de', 0x4f66666c696e65207365747a656e);
INSERT INTO `lng_data` VALUES ('common', 'setUserLanguage', 'de', 0x42656e75747a65727370726163686520c3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'setSystemLanguage', 'de', 0x53797374656d7370726163686520c3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'set', 'de', 0x5365747a656e);
INSERT INTO `lng_data` VALUES ('common', 'server_software', 'de', 0x53657276657220536f667477617265);
INSERT INTO `lng_data` VALUES ('common', 'server', 'de', 0x536572766572);
INSERT INTO `lng_data` VALUES ('common', 'sequences', 'de', 0x53657175656e7a656e);
INSERT INTO `lng_data` VALUES ('common', 'sequence', 'de', 0x53657175656e7a);
INSERT INTO `lng_data` VALUES ('common', 'sent', 'de', 0x476573656e646574);
INSERT INTO `lng_data` VALUES ('common', 'sender', 'de', 0x416273656e646572);
INSERT INTO `lng_data` VALUES ('common', 'send', 'de', 0x416273636869636b656e);
INSERT INTO `lng_data` VALUES ('common', 'selected', 'de', 0x417573676577c3a4686c7465);
INSERT INTO `lng_data` VALUES ('common', 'select_mode', 'de', 0x4d6f6475732077c3a4686c656e);
INSERT INTO `lng_data` VALUES ('common', 'select_file', 'de', 0x44617465692077c3a4686c656e);
INSERT INTO `lng_data` VALUES ('common', 'select_all', 'de', 0x416c6c652061757377c3a4686c656e);
INSERT INTO `lng_data` VALUES ('common', 'select', 'de', 0x41757377c3a4686c656e);
INSERT INTO `lng_data` VALUES ('common', 'sections', 'de', 0x4265726569636865);
INSERT INTO `lng_data` VALUES ('common', 'search_user', 'de', 0x42656e75747a65722073756368656e);
INSERT INTO `lng_data` VALUES ('common', 'search_result', 'de', 0x5375636865726765626e6973);
INSERT INTO `lng_data` VALUES ('common', 'search_recipient', 'de', 0x456d7066c3a46e6765722073756368656e);
INSERT INTO `lng_data` VALUES ('common', 'search_new', 'de', 0x4e657565205375636865);
INSERT INTO `lng_data` VALUES ('common', 'search_in', 'de', 0x53756368656e20696e);
INSERT INTO `lng_data` VALUES ('common', 'search', 'de', 0x53756368656e);
INSERT INTO `lng_data` VALUES ('common', 'saved_successfully', 'de', 0x4968726520c3846e646572756e67656e2077757264656e206765737065696368657274);
INSERT INTO `lng_data` VALUES ('common', 'saved', 'de', 0x4765737065696368657274);
INSERT INTO `lng_data` VALUES ('common', 'save_return', 'de', 0x53706569636865726e20756e64207a7572c3bc636b6b656872656e);
INSERT INTO `lng_data` VALUES ('common', 'save_refresh', 'de', 0x53706569636865726e20756e6420616b7475616c6973696572656e);
INSERT INTO `lng_data` VALUES ('common', 'save_message', 'de', 0x4e61636872696368742073706569636865726e);
INSERT INTO `lng_data` VALUES ('common', 'save_and_back', 'de', 0x53706569636865726e20756e64207a7572c3bc636b);
INSERT INTO `lng_data` VALUES ('common', 'save', 'de', 0x53706569636865726e);
INSERT INTO `lng_data` VALUES ('common', 'salutation_m', 'de', 0x48657272);
INSERT INTO `lng_data` VALUES ('common', 'salutation_f', 'de', 0x46726175);
INSERT INTO `lng_data` VALUES ('common', 'salutation', 'de', 0x416e72656465);
INSERT INTO `lng_data` VALUES ('common', 'rolt', 'de', 0x526f6c6c656e766f726c616765);
INSERT INTO `lng_data` VALUES ('common', 'rolt_added', 'de', 0x526f6c6c656e766f726c61676520616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'rolf', 'de', 0x526f6c6c656e6f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'rolf_added', 'de', 0x526f6c6c656e6f72646e657220616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'roles', 'de', 0x526f6c6c656e);
INSERT INTO `lng_data` VALUES ('common', 'role_deleted', 'de', 0x526f6c6c652067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('common', 'role_assignment', 'de', 0x526f6c6c656e7a7577656973756e67);
INSERT INTO `lng_data` VALUES ('common', 'role', 'de', 0x526f6c6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_added', 'de', 0x526f6c6c6520616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'rights', 'de', 0x526563687465);
INSERT INTO `lng_data` VALUES ('common', 'right', 'de', 0x5265636874);
INSERT INTO `lng_data` VALUES ('common', 'reset', 'de', 0x5a7572c3bc636b7365747a656e);
INSERT INTO `lng_data` VALUES ('common', 'retype_password', 'de', 0x50617373776f727420776965646572686f6c656e);
INSERT INTO `lng_data` VALUES ('common', 'reply', 'de', 0x416e74776f7274656e);
INSERT INTO `lng_data` VALUES ('common', 'required_field', 'de', 0x6572666f726465726c69636865732046656c64);
INSERT INTO `lng_data` VALUES ('common', 'rename', 'de', 0x556d62656e656e6e656e);
INSERT INTO `lng_data` VALUES ('common', 'register', 'de', 0x416e6d656c64656e);
INSERT INTO `lng_data` VALUES ('common', 'registered_since', 'de', 0x45696e676574726167656e2073656974);
INSERT INTO `lng_data` VALUES ('common', 'refresh_list', 'de', 0x4c6973746520616b7475616c6973696572656e);
INSERT INTO `lng_data` VALUES ('common', 'refresh', 'de', 0x416b7475616c6973696572656e);
INSERT INTO `lng_data` VALUES ('common', 'refresh_languages', 'de', 0x537072616368656e20616b7475616c6973696572656e);
INSERT INTO `lng_data` VALUES ('common', 'read', 'de', 0x4c6573656e);
INSERT INTO `lng_data` VALUES ('common', 'recipient', 'de', 0x456d7066c3a46e676572);
INSERT INTO `lng_data` VALUES ('common', 'quit', 'de', 0x4c6f676f7574);
INSERT INTO `lng_data` VALUES ('common', 'quote', 'de', 0x5a69746174);
INSERT INTO `lng_data` VALUES ('common', 'question', 'de', 0x4672616765);
INSERT INTO `lng_data` VALUES ('common', 'publishing_organisation', 'de', 0x566572c3b66666656e746c696368656e6465204f7267616e69736174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'published', 'de', 0x566572c3b66666656e746c69636874);
INSERT INTO `lng_data` VALUES ('common', 'publication', 'de', 0x566572c3b66666656e746c696368756e67);
INSERT INTO `lng_data` VALUES ('common', 'publication_date', 'de', 0x566572c3b66666656e746c696368756e6773646174756d);
INSERT INTO `lng_data` VALUES ('common', 'pub_section', 'de', 0xc3b66666656e746c69636865722042657265696368);
INSERT INTO `lng_data` VALUES ('common', 'public_profile', 'de', 0xc3966666656e746c69636865732050726f66696c);
INSERT INTO `lng_data` VALUES ('common', 'profile_changed', 'de', 0x4968722050726f66696c207775726465206765c3a46e64657274);
INSERT INTO `lng_data` VALUES ('common', 'properties', 'de', 0x456967656e736368616674656e);
INSERT INTO `lng_data` VALUES ('common', 'previous', 'de', 0x7a7572c3bc636b);
INSERT INTO `lng_data` VALUES ('common', 'print', 'de', 0x447275636b656e);
INSERT INTO `lng_data` VALUES ('common', 'position', 'de', 0x506f736974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'presentation_options', 'de', 0x5072c3a473656e746174696f6e736f7074696f6e656e);
INSERT INTO `lng_data` VALUES ('common', 'please_enter_title', 'de', 0x426974746520676562656e205369652065696e656e20546974656c2065696e21);
INSERT INTO `lng_data` VALUES ('common', 'port', 'de', 0x506f7274);
INSERT INTO `lng_data` VALUES ('common', 'phrase', 'de', 0x506872617365);
INSERT INTO `lng_data` VALUES ('common', 'please_enter_target', 'de', 0x426974746520676562656e205369652065696e205a69656c2065696e21);
INSERT INTO `lng_data` VALUES ('common', 'persons', 'de', 0x506572736f6e656e);
INSERT INTO `lng_data` VALUES ('common', 'phone', 'de', 0x54656c65666f6e);
INSERT INTO `lng_data` VALUES ('common', 'personal_picture', 'de', 0x50657273c3b66e6c69636865732042696c64);
INSERT INTO `lng_data` VALUES ('common', 'personal_profile', 'de', 0x50657273c3b66e6c69636865732050726f66696c);
INSERT INTO `lng_data` VALUES ('common', 'personal_data', 'de', 0x446174656e207a757220506572736f6e);
INSERT INTO `lng_data` VALUES ('common', 'personal_desktop', 'de', 0x50657273c3b66e6c696368657220536368726569627469736368);
INSERT INTO `lng_data` VALUES ('common', 'payment_system', 'de', 0x42657a61686c756e677373797374656d);
INSERT INTO `lng_data` VALUES ('common', 'perm_settings', 'de', 0x526563687465);
INSERT INTO `lng_data` VALUES ('common', 'permission', 'de', 0x5265636874);
INSERT INTO `lng_data` VALUES ('common', 'permission_denied', 'de', 0x4b65696e205a756772696666737265636874);
INSERT INTO `lng_data` VALUES ('common', 'path_to_zip', 'de', 0x5a4950204265666568737a65696c65);
INSERT INTO `lng_data` VALUES ('common', 'pathes', 'de', 0x5066616465);
INSERT INTO `lng_data` VALUES ('common', 'path_to_unzip', 'de', 0x554e5a49502042656665686c737a65696c65);
INSERT INTO `lng_data` VALUES ('common', 'path_to_java', 'de', 0x4a4156412042656665686c737a65696c65);
INSERT INTO `lng_data` VALUES ('common', 'path_to_babylon', 'de', 0x424142594c4f4e2042656665686c737a65696c65);
INSERT INTO `lng_data` VALUES ('common', 'path_to_convert', 'de', 0x434f4e564552542042656665686c737a65696c65);
INSERT INTO `lng_data` VALUES ('common', 'paste', 'de', 0x45696e66c3bc67656e);
INSERT INTO `lng_data` VALUES ('common', 'path', 'de', 0x50666164);
INSERT INTO `lng_data` VALUES ('common', 'password', 'de', 0x50617373776f7274);
INSERT INTO `lng_data` VALUES ('common', 'passwd_wrong', 'de', 0x4461732065696e6765676562656e652050617373776f7274206973742066616c73636821);
INSERT INTO `lng_data` VALUES ('common', 'passwd_not_match', 'de', 0x496872652045696e676162656e2066c3bc7220646173206e6575652050617373776f7274207374696d6d656e206e6963687420c3bc62657265696e2120426974746520646173206e6575652050617373776f72742065726e6575742065696e676562656e2e);
INSERT INTO `lng_data` VALUES ('common', 'passwd', 'de', 0x50617373776f7274);
INSERT INTO `lng_data` VALUES ('common', 'passwd_invalid', 'de', 0x446173206e6575652050617373776f72742069737420756e67c3bc6c746967212042697474652077c3a4686c656e205369652065696e20616e64657265732050617373776f72742e);
INSERT INTO `lng_data` VALUES ('common', 'parameter', 'de', 0x506172616d65746572);
INSERT INTO `lng_data` VALUES ('common', 'parse', 'de', 0x50617273656e);
INSERT INTO `lng_data` VALUES ('common', 'page_edit', 'de', 0x5365697465206265617262656974656e);
INSERT INTO `lng_data` VALUES ('common', 'page', 'de', 0x5365697465);
INSERT INTO `lng_data` VALUES ('common', 'owner', 'de', 0x42657369747a6572);
INSERT INTO `lng_data` VALUES ('common', 'overview', 'de', 0xc3bc6265727369636874);
INSERT INTO `lng_data` VALUES ('common', 'other', 'de', 0x416e64657265);
INSERT INTO `lng_data` VALUES ('common', 'order_by', 'de', 0x536f7274696572656e206e616368);
INSERT INTO `lng_data` VALUES ('common', 'options', 'de', 0x45696e7374656c6c756e67656e);
INSERT INTO `lng_data` VALUES ('common', 'optimize', 'de', 0x4f7074696d696572656e);
INSERT INTO `lng_data` VALUES ('common', 'online_version', 'de', 0x4f6e6c696e652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'operation', 'de', 0x4f7065726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'old', 'de', 0x416c74);
INSERT INTO `lng_data` VALUES ('common', 'online_chapter', 'de', 0x4f6e6c696e65204b61706974656c);
INSERT INTO `lng_data` VALUES ('common', 'ok', 'de', 0x4f4b);
INSERT INTO `lng_data` VALUES ('common', 'offline_version', 'de', 0x4f66666c696e652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'of', 'de', 0x766f6e);
INSERT INTO `lng_data` VALUES ('common', 'objf', 'de', 0x4f626a656b746f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'objects', 'de', 0x4f626a656b7465);
INSERT INTO `lng_data` VALUES ('common', 'obj_usrf', 'de', 0x42656e75747a657276657277616c74756e67);
INSERT INTO `lng_data` VALUES ('common', 'obj_usr', 'de', 0x42656e75747a6572);
INSERT INTO `lng_data` VALUES ('common', 'obj_uset', 'de', 0x42656e75747a657265696e7374656c6c756e67656e);
INSERT INTO `lng_data` VALUES ('common', 'obj_type', 'de', 0x4f626a656b74747970);
INSERT INTO `lng_data` VALUES ('common', 'obj_typ', 'de', 0x4f626a656b747479702d446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_st', 'de', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('common', 'obj_slm', 'de', 0x53434f524d204c65726e6d6f64756c);
INSERT INTO `lng_data` VALUES ('common', 'obj_root', 'de', 0x726f6f74);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolt', 'de', 0x526f6c6c656e766f726c616765);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf', 'de', 0x526f6c6c656e76657277616c74756e67);
INSERT INTO `lng_data` VALUES ('common', 'obj_role', 'de', 0x526f6c6c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_pg', 'de', 0x50616765);
INSERT INTO `lng_data` VALUES ('common', 'obj_owner', 'de', 0x42657369747a657220646573204f626a656b747320697374);
INSERT INTO `lng_data` VALUES ('common', 'obj_objf', 'de', 0x4f626a656b7476657277616c74756e67);
INSERT INTO `lng_data` VALUES ('common', 'obj_note', 'de', 0x4e6f74697a);
INSERT INTO `lng_data` VALUES ('common', 'obj_notf', 'de', 0x4e6f74697a656e76657277616c74756e67);
INSERT INTO `lng_data` VALUES ('common', 'obj_not_found', 'de', 0x4b65696e204f626a656b7420676566756e64656e);
INSERT INTO `lng_data` VALUES ('common', 'obj_mail', 'de', 0x4d61696c65696e7374656c6c756e67656e);
INSERT INTO `lng_data` VALUES ('common', 'obj_lo', 'de', 0x4c65726e6f626a656b74);
INSERT INTO `lng_data` VALUES ('common', 'obj_lngf', 'de', 0x537072616368656e76657277616c74756e67);
INSERT INTO `lng_data` VALUES ('common', 'obj_lng', 'de', 0x53707261636865);
INSERT INTO `lng_data` VALUES ('common', 'obj_lm', 'de', 0x4c65726e6d6f64756c);
INSERT INTO `lng_data` VALUES ('common', 'obj_le', 'de', 0x4c65726e6d6f64756c);
INSERT INTO `lng_data` VALUES ('common', 'obj_grp', 'de', 0x477275707065);
INSERT INTO `lng_data` VALUES ('common', 'obj_glo', 'de', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'obj_frm', 'de', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'obj_file', 'de', 0x4461746569);
INSERT INTO `lng_data` VALUES ('common', 'obj_crs', 'de', 0x4b757273);
INSERT INTO `lng_data` VALUES ('common', 'obj_cat', 'de', 0x4b617465676f726965);
INSERT INTO `lng_data` VALUES ('common', 'obj_adm', 'de', 0x53797374656d76657277616c74756e67);
INSERT INTO `lng_data` VALUES ('common', 'obj', 'de', 0x4f626a656b74);
INSERT INTO `lng_data` VALUES ('common', 'not_logged_in', 'de', 0x5369652073696e64206e696368742065696e67656c6f676774);
INSERT INTO `lng_data` VALUES ('common', 'not_installed', 'de', 0x4e6963687420696e7374616c6c69657274);
INSERT INTO `lng_data` VALUES ('common', 'normal', 'de', 0x4e6f726d616c);
INSERT INTO `lng_data` VALUES ('common', 'none', 'de', 0x4b65696e65);
INSERT INTO `lng_data` VALUES ('common', 'no_title', 'de', 0x4b65696e20546974656c);
INSERT INTO `lng_data` VALUES ('common', 'no_objects', 'de', 0x4b65696e65204f626a656b7465);
INSERT INTO `lng_data` VALUES ('common', 'no_import_available', 'de', 0x496d706f7274206e696368742076657266c3bc676261722066c3bc7220547970);
INSERT INTO `lng_data` VALUES ('common', 'no_date', 'de', 0x4b65696e20446174756d);
INSERT INTO `lng_data` VALUES ('common', 'no_checkbox', 'de', 0x53696520686162656e206b65696e65204175737761686c20676574726f6666656e);
INSERT INTO `lng_data` VALUES ('common', 'no', 'de', 0x4e65696e);
INSERT INTO `lng_data` VALUES ('common', 'nickname', 'de', 0x4c6f67696e);
INSERT INTO `lng_data` VALUES ('common', 'next', 'de', 0x776569746572);
INSERT INTO `lng_data` VALUES ('common', 'news', 'de', 0x4e657773);
INSERT INTO `lng_data` VALUES ('common', 'new_mail', 'de', 0x4e657565204d61696c21);
INSERT INTO `lng_data` VALUES ('common', 'new_language', 'de', 0x4e6575652053707261636865);
INSERT INTO `lng_data` VALUES ('common', 'new_group', 'de', 0x4e65756520477275707065);
INSERT INTO `lng_data` VALUES ('common', 'new_folder', 'de', 0x4e65756572204f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'new', 'de', 0x4e6575);
INSERT INTO `lng_data` VALUES ('common', 'name', 'de', 0x4e616d65);
INSERT INTO `lng_data` VALUES ('common', 'multimedia', 'de', 0x4d756c74696d65646961);
INSERT INTO `lng_data` VALUES ('common', 'msg_userassignment_changed', 'de', 0x42656e75747a65727a7577656973756e67206765c3a46e64657274);
INSERT INTO `lng_data` VALUES ('common', 'msg_user_last_role2', 'de', 0x4269747465206cc3b6736368656e20536965206469652042656e75747a6572206f6465722077656973656e205369652069686e656e2065696e657220616e646572656e20526f6c6c65207a752e);
INSERT INTO `lng_data` VALUES ('common', 'msg_user_last_role1', 'de', 0x44696520666f6c67656e64656e2042656e75747a65722073696e64206e7572206469657365722065696e656e20526f6c6c65207a75676577696573656e3a);
INSERT INTO `lng_data` VALUES ('common', 'msg_undeleted', 'de', 0x4f626a656b742865292077696564657268657267657374656c6c7421);
INSERT INTO `lng_data` VALUES ('common', 'msg_trash_empty', 'de', 0x496e2064696573656d204f72646e6572206578697374696572656e206b65696e652067656cc3b673636874656e204f626a656b746521);
INSERT INTO `lng_data` VALUES ('common', 'msg_roleassignment_changed', 'de', 0x526f6c6c656e7a7577656973756e67206765c3a46e64657274);
INSERT INTO `lng_data` VALUES ('common', 'msg_role_exists2', 'de', 0x6578697374696572742062657265697473212042697474652077c3a4686c656e205369652065696e656e20616e646572656e204e616d656e);
INSERT INTO `lng_data` VALUES ('common', 'msg_role_exists1', 'de', 0x45696e6520526f6c6c652f526f6c6c656e766f726c616765206d69742064656d204e616d656e);
INSERT INTO `lng_data` VALUES ('common', 'msg_removed', 'de', 0x4f626a656b74286529206175732064656d2053797374656d20656e746665726e74);
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from_itself', 'de', 0x457320697374206e69636874206dc3b6676c6963682c206469652052656368746565696e7374656c6c7567656e20766f6e2064657220616b7475656c6c656e20526f6c6c652f526f6c6c656e766f726c6167652073656c627374207a7520c3bc6265726e65686d656e2e);
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from2', 'de', 0x284469652045696e7374656c6c756e67656e2077757264656e2067657370656963686572742129);
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from1', 'de', 0x52656368746565696e7374656c6c756e67656e20c3bc6265726e6f6d6d656e20766f6e);
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_modified', 'de', 0xc3846e646572756e67656e20676573706569636865727421);
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_exists', 'de', 0x446965736573204f626a656b7420657869737469657274206265726569747320696e2064696573656d204f72646e657221);
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_created', 'de', 0x4f626a656b7420616e67656c65677421);
INSERT INTO `lng_data` VALUES ('common', 'msg_nothing_found', 'de', 0x4e696368747320676566756e64656e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_not_possible_link', 'de', 0x457320697374206e69636874206dc3b6676c6963682064696520666f6c67656e64656e204f626a656b7465207a75207665726b6ec3bc7066656e3a20547970);
INSERT INTO `lng_data` VALUES ('common', 'msg_not_in_itself', 'de', 0x457320697374206e69636874206dc3b6676c6963682064696520666f6c67656e64656e204f626a656b746520696e20736963682073656c6273742065696e7a7566c3bc67656e3a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_search_string', 'de', 0x426974746520676562656e205369652065696e656e2053756368626567726966662065696e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_search_result', 'de', 0x4b65696e652045696e7472c3a4676520676566756e64656e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_rolf_allowed2', 'de', 0x6b616e6e206b65696e656e20526f6c6c656e6f72646e6572206265696e68616c74656e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_rolf_allowed1', 'de', 0x446173204f626a656b74);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_write', 'de', 0x5369652062657369747a656e206b65696e652053636872656962626572656368746967756e6721);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_perm', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c20756d20617566206469652052656368746565696e7374656c6c756e67656e207a757a756772656966656e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_paste', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c20756d2064696520666f6c67656e64656e204f626a656b74652065696e7a7566c3bc67656e3a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_user', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c20756d2042656e75747a6572646174656e207a7520c3a46e6465726e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_rolt', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c20756d20526f6c6c656e766f726c6167656e207a7520c3a46e6465726e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_role', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c20756d20526f6c6c656e207a7520c3a46e6465726e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_link', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672065696e65205665726b6ec3bc7066756e672066c3bc722064696520666f6c67656e64656e204f626a656b7465207a752065727374656c6c656e3a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_delete', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672064696520666f6c67656e64656e204f626a656b7465207a75206cc3b6736368656e3a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_cut', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672064696520666f6c67656e64656e204f626a656b7465206175737a757363686e656964656e3a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_user', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c20756d2042656e75747a657220616e7a756c6567656e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_rolt', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c20756d206e65756520526f6c6c656e766f726c6167656e20616e7a756c6567656e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_rolf', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c20756d2065696e656e20526f6c6c656e6f72646e657220616e7a756c6567656e2e2044657368616c62206bc3b66e6e656e205369652068696572206e696368742064696520566572657262756e6720766f6e20526f6c6c656e20756e7465726272656368656e206f646572206c6f6b616c6520526f6c6c656e20616e6c6567656e2e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_role', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c20756d206e65756520526f6c6c656e20616e7a756c6567656e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_object2', 'de', 0x616e2064696573657220506f736974696f6e20616e7a756c6567656e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_object1', 'de', 0x5369652062657369747a656e206b65696e6520426572656368746967756e672c20756d);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c2064696520666f6c67656e64656e204f626a656b7465207a752065727a657567656e3a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_copy', 'de', 0x5369652062657369747a656e206b65696e65206175737265696368656e646520426572656368746967756e672c2064696520666f6c67656e64656e204f626a656b7465207a75206b6f70696572656e3a);
INSERT INTO `lng_data` VALUES ('common', 'msg_min_one_role', 'de', 0x4a656465722042656e75747a6572206d757373206d696e64657374656e732065696e657220526f6c6c65207a75676577696573656e207365696e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_min_one_active_role', 'de', 0x4a656465722042656e75747a6572206d757373206d696e64657374656e732065696e657220616b746976656e20526f6c6c656e207a75676577696573656e207365696e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_may_not_contain', 'de', 0x446965736573204f626a656b742064617266206b65696e65204f626a656b74652064657320666f6c67656e64656e2054797073206265696e68616c74656e3a);
INSERT INTO `lng_data` VALUES ('common', 'msg_linked', 'de', 0x4f626a656b74286529207665726b6ec3bc706674);
INSERT INTO `lng_data` VALUES ('common', 'msg_link_clipboard', 'de', 0x417573676577c3a4686c7465287329204f626a656b7428652920696e20646572205a7769736368656e61626c616765207665726d65726b742028416b74696f6e3a207665726b6ec3bc7066656e29);
INSERT INTO `lng_data` VALUES ('common', 'msg_is_last_role', 'de', 0x53696520686162656e2064656e20666f6c67656e64656e2042656e75747a65726e2069687265206c65747a746520526f6c6c656e7a7577656973756e672067656e6f6d6d656e);
INSERT INTO `lng_data` VALUES ('common', 'msg_failed', 'de', 0x416b74696f6e206665686c67657363686c6167656e);
INSERT INTO `lng_data` VALUES ('common', 'msg_error_copy', 'de', 0x4665686c6572206265696d204b6f70696572656e);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_rolts', 'de', 0x526f6c6c656e766f726c6167656e2067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_rolt', 'de', 0x526f6c6c656e766f726c6167652067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_roles_rolts', 'de', 0x526f6c6c656e202620526f6c6c656e766f726c6167656e2067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_roles', 'de', 0x526f6c6c656e2067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_role', 'de', 0x526f6c6c652067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('common', 'msg_cut_copied', 'de', 0x4f626a656b74286529207665727363686f62656e2e);
INSERT INTO `lng_data` VALUES ('common', 'msg_cut_clipboard', 'de', 0x417573676577c3a4686c7465287329204f626a656b7428652920696e20646572205a7769736368656e61626c616765207665726d65726b742028416b74696f6e3a20766572736368696562656e29);
INSERT INTO `lng_data` VALUES ('common', 'msg_copy_clipboard', 'de', 0x417573676577c3a4686c7465287329204f626a656b7428652920696e20646572205a7769736368656e61626c616765207665726d65726b742028416b74696f6e3a206b6f70696572656e29);
INSERT INTO `lng_data` VALUES ('common', 'msg_cloned', 'de', 0x4f626a656b74286529206b6f70696572742e);
INSERT INTO `lng_data` VALUES ('common', 'msg_clear_clipboard', 'de', 0x5a7769736368656e61626c6167652067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('common', 'msg_changes_ok', 'de', 0x44696520c3846e646572756e67656e2077757264656e20c3bc6265726e6f6d6d656e);
INSERT INTO `lng_data` VALUES ('common', 'msg_cancel_delete', 'de', 0x416b74696f6e206162676562726f6368656e);
INSERT INTO `lng_data` VALUES ('common', 'move_to', 'de', 0x41626c6567656e20696e);
INSERT INTO `lng_data` VALUES ('common', 'modules', 'de', 0x4d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'module', 'de', 0x4d6f64756c);
INSERT INTO `lng_data` VALUES ('common', 'migrate', 'de', 0x4d696772696572656e);
INSERT INTO `lng_data` VALUES ('common', 'meta_data', 'de', 0x4d657461646174656e);
INSERT INTO `lng_data` VALUES ('common', 'message_to', 'de', 0x4e616368726963687420616e3a);
INSERT INTO `lng_data` VALUES ('common', 'message_content', 'de', 0x4e6163687269636874656e696e68616c74);
INSERT INTO `lng_data` VALUES ('common', 'message', 'de', 0x4e6163687269636874);
INSERT INTO `lng_data` VALUES ('common', 'members', 'de', 0x4d6974676c6965646572);
INSERT INTO `lng_data` VALUES ('common', 'mark_all_unread', 'de', 0x416c7320756e67656c6573656e206d61726b696572656e);
INSERT INTO `lng_data` VALUES ('common', 'mark_all_read', 'de', 0x416c732067656c6573656e206d61726b696572656e);
INSERT INTO `lng_data` VALUES ('common', 'mails', 'de', 0x4d61696c73);
INSERT INTO `lng_data` VALUES ('common', 'mail_z_local', 'de', 0x456967656e65204f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'mail_system', 'de', 0x53797374656d6e6163687269636874);
INSERT INTO `lng_data` VALUES ('common', 'mail_sent', 'de', 0x49687265204e61636872696368742077757264652076657273616e6474);
INSERT INTO `lng_data` VALUES ('common', 'mail_send_error', 'de', 0x4665686c6572206265696d2056657273636869636b656e20646572204e6163687269636874);
INSERT INTO `lng_data` VALUES ('common', 'mail_select_one', 'de', 0x536965206dc3bc7373656e206d696e64657374656e732065696e65204e61636872696368742061757377c3a4686c656e);
INSERT INTO `lng_data` VALUES ('common', 'mail_search_word', 'de', 0x5375636865);
INSERT INTO `lng_data` VALUES ('common', 'mail_search_no', 'de', 0x45732077757264656e206b65696e652070617373656e64656e2045726765626e6973736520676566756e64656e);
INSERT INTO `lng_data` VALUES ('common', 'mail_not_sent', 'de', 0x49687265204e6163687269636874206b6f6e6e7465206e696368742076657273636869636b742077657264656e);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxtime_mail', 'de', 0x4d61782e20417566626577616872756e67737a6569742066c3bc72204d61696c73);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxtime_attach', 'de', 0x4d61782e20417566626577616872756e67737a6569742066c3bc72204174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_mail', 'de', 0x4d61782e204d61696c2d4772c3b6737365);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_box', 'de', 0x4d61782e204d61696c626f782d4772c3b6737365);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_attach', 'de', 0x4d61782e204174746163686d656e742d4772c3b6737365);
INSERT INTO `lng_data` VALUES ('common', 'mail_mails_of', 'de', 0x4e6163687269636874656e);
INSERT INTO `lng_data` VALUES ('common', 'mail_intern_enable', 'de', 0x416b746976696572656e);
INSERT INTO `lng_data` VALUES ('common', 'mail_folders', 'de', 0x4d61696c2d4f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'mail_e_sent', 'de', 0x476573656e64657465);
INSERT INTO `lng_data` VALUES ('common', 'mail_delete_error', 'de', 0x4665686c6572206265696d204cc3b6736368656e);
INSERT INTO `lng_data` VALUES ('common', 'mail_delete_error_file', 'de', 0x4665686c6572206265696d204cc3b6736368656e20646572204461746569);
INSERT INTO `lng_data` VALUES ('common', 'mail_d_drafts', 'de', 0x456e7477c3bc726665);
INSERT INTO `lng_data` VALUES ('common', 'mail_c_trash', 'de', 0x5061706965726b6f7262);
INSERT INTO `lng_data` VALUES ('common', 'mail_b_inbox', 'de', 0x506f737465696e67616e67);
INSERT INTO `lng_data` VALUES ('common', 'mail_allow_smtp', 'de', 0x534d5450207a756c617373656e);
INSERT INTO `lng_data` VALUES ('common', 'mail_addressbook', 'de', 0x41647265737362756368);
INSERT INTO `lng_data` VALUES ('common', 'mail_a_root', 'de', 0x4e6163687269636874656e);
INSERT INTO `lng_data` VALUES ('common', 'mail', 'de', 0x4d61696c);
INSERT INTO `lng_data` VALUES ('common', 'los_last_visited', 'de', 0x5a756c65747a74206265737563687465204c65726e6d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'los', 'de', 0x4c65726e6d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'logout', 'de', 0x4c6f676f7574);
INSERT INTO `lng_data` VALUES ('common', 'login_to_ilias', 'de', 0x42656920494c49415320616e6d656c64656e);
INSERT INTO `lng_data` VALUES ('common', 'login_time', 'de', 0x4f6e6c696e657a656974);
INSERT INTO `lng_data` VALUES ('common', 'login_exists', 'de', 0x45696e2042656e75747a6572206d69742064696573656d204e616d656e206578697374696572742062657265697473212042697474652077c3a4686c656e205369652065696e656e20616e646572656e2042656e75747a65726e616d656e);
INSERT INTO `lng_data` VALUES ('common', 'login_data', 'de', 0x4c6f67696e646174656e);
INSERT INTO `lng_data` VALUES ('common', 'login', 'de', 0x4c6f67696e);
INSERT INTO `lng_data` VALUES ('common', 'locator', 'de', 0x4c6f6361746f723a);
INSERT INTO `lng_data` VALUES ('common', 'lo_overview', 'de', 0xc39c6265727369636874204c65726e6d6174657269616c69656e);
INSERT INTO `lng_data` VALUES ('common', 'lo_other_langs', 'de', 0x4c65726e6d6f64756c6520696e20616e646572656e20537072616368656e);
INSERT INTO `lng_data` VALUES ('common', 'lo_no_content', 'de', 0x4b65696e65204c65726e6d6174657269616c69656e20766f7268616e64656e);
INSERT INTO `lng_data` VALUES ('common', 'lo_new', 'de', 0x6e65756573204c65726e6d6f64756c);
INSERT INTO `lng_data` VALUES ('common', 'lo_edit', 'de', 0x4c65726e6d6f64756c652065646974696572656e);
INSERT INTO `lng_data` VALUES ('common', 'lo_available', 'de', 0x4c65726e6d6174657269616c69656e);
INSERT INTO `lng_data` VALUES ('common', 'lo_categories', 'de', 0x4b617465676f7269656e);
INSERT INTO `lng_data` VALUES ('common', 'lo', 'de', 0x4c65726e65696e68656974);
INSERT INTO `lng_data` VALUES ('common', 'lngf', 'de', 0x5370726163686f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'lng', 'de', 0x53707261636865);
INSERT INTO `lng_data` VALUES ('common', 'lm_new', 'de', 0x4e65756573204c65726e6d6f64756c);
INSERT INTO `lng_data` VALUES ('common', 'lm_added', 'de', 0x4c65726e6d6f64756c20616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'lm_add', 'de', 0x4c65726e6d6f64756c20616e6c6567656e);
INSERT INTO `lng_data` VALUES ('common', 'lm_a', 'de', 0x65696e204c65726e6d6f64756c);
INSERT INTO `lng_data` VALUES ('common', 'lm', 'de', 0x4c65726e6d6f64756c);
INSERT INTO `lng_data` VALUES ('common', 'literature_bookmarks', 'de', 0x4c69746572617475722d426f6f6b6d61726b73);
INSERT INTO `lng_data` VALUES ('common', 'literature', 'de', 0x4c6974657261747572);
INSERT INTO `lng_data` VALUES ('common', 'list_of_questions', 'de', 0x46726167656e6c69737465);
INSERT INTO `lng_data` VALUES ('common', 'list_of_pages', 'de', 0x53656974656e6c69737465);
INSERT INTO `lng_data` VALUES ('common', 'linked_pages', 'de', 0x5665726b6ec3bc706674206d69742064656e2053656974656e);
INSERT INTO `lng_data` VALUES ('common', 'link', 'de', 0x7665726b6ec3bc7066656e);
INSERT INTO `lng_data` VALUES ('common', 'level', 'de', 0x5374756665);
INSERT INTO `lng_data` VALUES ('common', 'learning_objects', 'de', 0x4c65726e6f626a656b7465);
INSERT INTO `lng_data` VALUES ('common', 'ldap', 'de', 0x4c444150);
INSERT INTO `lng_data` VALUES ('common', 'launch', 'de', 0x5374617274);
INSERT INTO `lng_data` VALUES ('common', 'lastname', 'de', 0x4e6163686e616d65);
INSERT INTO `lng_data` VALUES ('common', 'last_visit', 'de', 0x4c65747a74657220426573756368);
INSERT INTO `lng_data` VALUES ('common', 'last_change', 'de', 0x4c65747a746520c3846e646572756e67);
INSERT INTO `lng_data` VALUES ('common', 'languages_updated', 'de', 0x416c6c6520696e7374616c6c69657274656e20537072616368656e2077757264656e20616b7475616c697369657274);
INSERT INTO `lng_data` VALUES ('common', 'languages_already_uninstalled', 'de', 0x416e676577c3a4686c746520537072616368652f6e206973742f73696e642062657265697473206465696e7374616c6c69657274);
INSERT INTO `lng_data` VALUES ('common', 'languages_already_installed', 'de', 0x476577c3a4686c746520537072616368652f6e206973742f73696e64206265726569747320696e7374616c6c69657274);
INSERT INTO `lng_data` VALUES ('common', 'languages', 'de', 0x537072616368656e);
INSERT INTO `lng_data` VALUES ('common', 'language_not_installed', 'de', 0x697374206e6963687420696e7374616c6c696572742e20426974746520696e7374616c6c696572656e20536965206469652053707261636865207a7565727374);
INSERT INTO `lng_data` VALUES ('common', 'language', 'de', 0x53707261636865);
INSERT INTO `lng_data` VALUES ('common', 'langfile_found', 'de', 0x537072616368646174656920676566756e64656e);
INSERT INTO `lng_data` VALUES ('common', 'lang_xx', 'de', 0x62656e75747a6572646566696e69657274);
INSERT INTO `lng_data` VALUES ('common', 'lang_version', 'de', 0x31);
INSERT INTO `lng_data` VALUES ('common', 'lang_timeformat', 'de', 0x483a693a73);
INSERT INTO `lng_data` VALUES ('common', 'lang_sep_thousand', 'de', 0x2e);
INSERT INTO `lng_data` VALUES ('common', 'lang_se', 'de', 0x53636877656469736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_sep_decimal', 'de', 0x2c);
INSERT INTO `lng_data` VALUES ('common', 'lang_pl', 'de', 0x506f6c6e69736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_path', 'de', 0x5370726163686461746569656e2d50666164);
INSERT INTO `lng_data` VALUES ('common', 'lang_no', 'de', 0x4e6f7277656769736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_id', 'de', 0x496e646f6e657369736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_it', 'de', 0x4974616c69656e69736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_fr', 'de', 0x4672616e7ac3b67369736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_es', 'de', 0x5370616e69736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_en', 'de', 0x456e676c69736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_dk', 'de', 0x44c3a46e69736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_de', 'de', 0x44657574736368);
INSERT INTO `lng_data` VALUES ('common', 'lang_dateformat', 'de', 0x642e6d2e59);
INSERT INTO `lng_data` VALUES ('common', 'keywords', 'de', 0x5363686cc3bc7373656c77c3b672746572);
INSERT INTO `lng_data` VALUES ('common', 'kb', 'de', 0x4b42797465);
INSERT INTO `lng_data` VALUES ('common', 'item', 'de', 0x42656772696666);
INSERT INTO `lng_data` VALUES ('common', 'ip_address', 'de', 0x49502041647265737365);
INSERT INTO `lng_data` VALUES ('common', 'is_already_your', 'de', 0x69737420626572656974732049687265);
INSERT INTO `lng_data` VALUES ('common', 'institution', 'de', 0x496e737469747574696f6e);
INSERT INTO `lng_data` VALUES ('common', 'internal_system', 'de', 0x496e7465726e65732053797374656d);
INSERT INTO `lng_data` VALUES ('common', 'installed', 'de', 0x496e7374616c6c69657274);
INSERT INTO `lng_data` VALUES ('common', 'install', 'de', 0x496e7374616c6c696572656e);
INSERT INTO `lng_data` VALUES ('common', 'inst_name', 'de', 0x496e7374616c6c6174696f6e736e616d65);
INSERT INTO `lng_data` VALUES ('common', 'inst_info', 'de', 0x496e7374616c6c6174696f6e732d496e666f);
INSERT INTO `lng_data` VALUES ('common', 'inst_id', 'de', 0x496e7374616c6c6174696f6e732d4944);
INSERT INTO `lng_data` VALUES ('common', 'insert', 'de', 0x45696e66c3bc67656e);
INSERT INTO `lng_data` VALUES ('common', 'inifile', 'de', 0x494e492d4461746569);
INSERT INTO `lng_data` VALUES ('common', 'input_error', 'de', 0x45696e676162656665686c6572);
INSERT INTO `lng_data` VALUES ('common', 'information_abbr', 'de', 0x496e666f73);
INSERT INTO `lng_data` VALUES ('common', 'inform_user_mail', 'de', 0x42656e75747a65722070657220452d4d61696c20c3bc62657220c3846e646572756e67656e20696e666f726d696572656e);
INSERT INTO `lng_data` VALUES ('common', 'info_deleted', 'de', 0x4f626a656b742865292067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('common', 'info_trash', 'de', 0x47656cc3b67363687465204f626a656b7465);
INSERT INTO `lng_data` VALUES ('common', 'info_delete_sure', 'de', 0x53696e6420536965207369636865722c206461c39f20666f6c67656e6465204f626a656b74652067656cc3b6736368742077657264656e20736f6c6c656e3a);
INSERT INTO `lng_data` VALUES ('common', 'in_use', 'de', 0x42656e75747a657273707261636865);
INSERT INTO `lng_data` VALUES ('common', 'inbox', 'de', 0x506f737465696e67616e67);
INSERT INTO `lng_data` VALUES ('common', 'import_slm', 'de', 0x496d706f72742053434f524d2d4461746569);
INSERT INTO `lng_data` VALUES ('common', 'import', 'de', 0x496d706f7274);
INSERT INTO `lng_data` VALUES ('common', 'import_lm', 'de', 0x496d706f7274204c65726e6d6f64756c);
INSERT INTO `lng_data` VALUES ('common', 'ilias_version', 'de', 0x494c4941532056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'identifier', 'de', 0x4964656e746966696572);
INSERT INTO `lng_data` VALUES ('common', 'id', 'de', 0x4944);
INSERT INTO `lng_data` VALUES ('common', 'http_path', 'de', 0x485454502050666164);
INSERT INTO `lng_data` VALUES ('common', 'host', 'de', 0x486f7374);
INSERT INTO `lng_data` VALUES ('common', 'hobby', 'de', 0x496e746572657373656e2f486f6262696573);
INSERT INTO `lng_data` VALUES ('common', 'help', 'de', 0x48696c6665);
INSERT INTO `lng_data` VALUES ('common', 'grp_new', 'de', 0x4e65756520477275707065);
INSERT INTO `lng_data` VALUES ('common', 'grp_added', 'de', 0x47727570706520616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'grp_add', 'de', 0x47727570706520616e6c6567656e);
INSERT INTO `lng_data` VALUES ('common', 'grp_a', 'de', 0x65696e6520477275707065);
INSERT INTO `lng_data` VALUES ('common', 'grp', 'de', 0x477275707065);
INSERT INTO `lng_data` VALUES ('common', 'groups_overview', 'de', 0x4772757070656e20c3bc6265727369636874);
INSERT INTO `lng_data` VALUES ('common', 'groups', 'de', 0x4772757070656e);
INSERT INTO `lng_data` VALUES ('common', 'group_status_public', 'de', 0xc3966666656e746c696368);
INSERT INTO `lng_data` VALUES ('common', 'group_status_private', 'de', 0x507269766174);
INSERT INTO `lng_data` VALUES ('common', 'group_status_closed', 'de', 0x47657363686c6f7373656e);
INSERT INTO `lng_data` VALUES ('common', 'group_status', 'de', 0x4772757070656e737461747573);
INSERT INTO `lng_data` VALUES ('common', 'group_not_available', 'de', 0x6b65696e65204772757070652076657266c3bc67626172);
INSERT INTO `lng_data` VALUES ('common', 'group_name', 'de', 0x4772757070656e6e616d65);
INSERT INTO `lng_data` VALUES ('common', 'group_members', 'de', 0x4772757070656e2d4d6974676c6965646572);
INSERT INTO `lng_data` VALUES ('common', 'group_filesharing', 'de', 0x4772757070656e2d46696c6573686172696e67);
INSERT INTO `lng_data` VALUES ('common', 'group_details', 'de', 0x4772757070656e2d44657461696c73);
INSERT INTO `lng_data` VALUES ('common', 'group_any_objects', 'de', 0x4b65696e6520556e7465726f626a656b746520766f7268616e64656e);
INSERT INTO `lng_data` VALUES ('common', 'glossary_added', 'de', 0x476c6f7373617279206164646564);
INSERT INTO `lng_data` VALUES ('common', 'glossary', 'de', 0x476c6f73736172);
INSERT INTO `lng_data` VALUES ('common', 'glo_added', 'de', 0x416464656420676c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo', 'de', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'generate', 'de', 0x67656e6572696572656e);
INSERT INTO `lng_data` VALUES ('common', 'gender', 'de', 0x47657363686c65636874);
INSERT INTO `lng_data` VALUES ('common', 'functions', 'de', 0x46756e6b74696f6e656e);
INSERT INTO `lng_data` VALUES ('common', 'from', 'de', 0x566f6e);
INSERT INTO `lng_data` VALUES ('common', 'frm_new', 'de', 0x4e6575657320466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_added', 'de', 0x466f72756d20616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'frm_add', 'de', 0x466f72756d20616e6c6567656e);
INSERT INTO `lng_data` VALUES ('common', 'frm_a', 'de', 0x65696e20466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm', 'de', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'forums_overview', 'de', 0xc39c626572736963687420466f72656e);
INSERT INTO `lng_data` VALUES ('common', 'forums', 'de', 0x466f72656e);
INSERT INTO `lng_data` VALUES ('common', 'forum', 'de', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'form_empty_fields', 'de', 0x44696573652046656c646572206269747465206e6f63682061757366c3bc6c6c656e3a);
INSERT INTO `lng_data` VALUES ('common', 'forename', 'de', 0x566f726e616d65);
INSERT INTO `lng_data` VALUES ('common', 'folders', 'de', 0x4f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'folder_added', 'de', 0x4f72646e657220616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'folder', 'de', 0x4f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'fold', 'de', 0x4f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'flatview', 'de', 0x466c6163686520416e7369636874);
INSERT INTO `lng_data` VALUES ('common', 'firstname', 'de', 0x566f726e616d65);
INSERT INTO `lng_data` VALUES ('common', 'fill_out_all_required_fields', 'de', 0x42697474652066c3bc6c6c656e2053696520616c6c652062656ec3b674696774656e2046656c64657220617573);
INSERT INTO `lng_data` VALUES ('common', 'files_location', 'de', 0x44617465696f7274);
INSERT INTO `lng_data` VALUES ('common', 'file_version', 'de', 0x56657266c3bc67626172652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'file_valid', 'de', 0x4461746569206973742067c3bc6c74696721);
INSERT INTO `lng_data` VALUES ('common', 'file_not_valid', 'de', 0x44617465692069737420756e67c3bc6c74696721);
INSERT INTO `lng_data` VALUES ('common', 'file_not_found', 'de', 0x4461746569206e6963687420676566756e64656e);
INSERT INTO `lng_data` VALUES ('common', 'file', 'de', 0x4461746569);
INSERT INTO `lng_data` VALUES ('common', 'feedback_recipient', 'de', 0x456d7066c3a46e67657220766f6e20466565646261636b6d656c64756e67656e);
INSERT INTO `lng_data` VALUES ('common', 'feedback', 'de', 0x466565646261636b);
INSERT INTO `lng_data` VALUES ('common', 'faq_exercise', 'de', 0x4641512fc3bc62756e67736b61706974656c);
INSERT INTO `lng_data` VALUES ('common', 'export_xml', 'de', 0x4578706f7274696572656e20616c7320584d4c2d4461746569);
INSERT INTO `lng_data` VALUES ('common', 'export_html', 'de', 0x4578706f7274696572656e20616c732048544d4c2d4461746569);
INSERT INTO `lng_data` VALUES ('common', 'execute', 'de', 0x41757366c3bc6872656e);
INSERT INTO `lng_data` VALUES ('common', 'error_recipient', 'de', 0x456d7066c3a46e67657220766f6e204665686c65726d656c64756e67656e);
INSERT INTO `lng_data` VALUES ('common', 'err_wrong_login', 'de', 0x4c6f67696e20756e67c3bc6c746967);
INSERT INTO `lng_data` VALUES ('common', 'err_wrong_header', 'de', 0x4772756e643a2046616c736368657220486561646572);
INSERT INTO `lng_data` VALUES ('common', 'err_session_expired', 'de', 0x496872652053657373696f6e2069737420616267656c617566656e21);
INSERT INTO `lng_data` VALUES ('common', 'err_over_3_param', 'de', 0x4d65687220616c73203320506172616d6574657221);
INSERT INTO `lng_data` VALUES ('common', 'err_no_param', 'de', 0x4b65696e6520506172616d6574657221);
INSERT INTO `lng_data` VALUES ('common', 'err_no_langfile_found', 'de', 0x4b65696e65205370726163686461746569656e20676566756e64656e21);
INSERT INTO `lng_data` VALUES ('common', 'err_in_line', 'de', 0x4665686c657220696e205a65696c65);
INSERT INTO `lng_data` VALUES ('common', 'err_count_param', 'de', 0x4772756e643a2046616c7363686520506172616d65746572616e7a61686c);
INSERT INTO `lng_data` VALUES ('common', 'err_2_param', 'de', 0x4e7572203220506172616d6574657221);
INSERT INTO `lng_data` VALUES ('common', 'err_1_param', 'de', 0x4e7572203120506172616d6574657221);
INSERT INTO `lng_data` VALUES ('common', 'enumerate', 'de', 0x4e756d6d6572696572656e);
INSERT INTO `lng_data` VALUES ('common', 'enabled', 'de', 0x45696e6765736368616c746574);
INSERT INTO `lng_data` VALUES ('common', 'enable', 'de', 0x416b746976696572656e);
INSERT INTO `lng_data` VALUES ('common', 'email_not_valid', 'de', 0x4469652065696e6765676562656e6520452d4d61696c2d4164726573736520697374206e696368742067c3bc6c74696721);
INSERT INTO `lng_data` VALUES ('common', 'email', 'de', 0x452d4d61696c);
INSERT INTO `lng_data` VALUES ('common', 'editor', 'de', 0x456469746f72);
INSERT INTO `lng_data` VALUES ('common', 'edited_at', 'de', 0x4265617262656974657420616d);
INSERT INTO `lng_data` VALUES ('common', 'edit_stylesheet', 'de', 0x5374796c65206265617262656974656e);
INSERT INTO `lng_data` VALUES ('common', 'edit_properties', 'de', 0x456967656e736368616674656e);
INSERT INTO `lng_data` VALUES ('common', 'edit_operations', 'de', 0x4f7065726174696f6e656e206265617262656974656e);
INSERT INTO `lng_data` VALUES ('common', 'edit_data', 'de', 0x446174656e206265617262656974656e);
INSERT INTO `lng_data` VALUES ('common', 'edit', 'de', 0x4265617262656974656e);
INSERT INTO `lng_data` VALUES ('common', 'drafts', 'de', 0x456e7477c3bc726665);
INSERT INTO `lng_data` VALUES ('common', 'download', 'de', 0x486572756e7465726c6164656e);
INSERT INTO `lng_data` VALUES ('common', 'disabled', 'de', 0x4175736765736368616c746574);
INSERT INTO `lng_data` VALUES ('common', 'desired_password', 'de', 0x4e657565732050617373776f7274);
INSERT INTO `lng_data` VALUES ('common', 'description', 'de', 0x426573636872656962756e67);
INSERT INTO `lng_data` VALUES ('common', 'desc', 'de', 0x426573636872656962756e67);
INSERT INTO `lng_data` VALUES ('common', 'deleted', 'de', 0x47656cc3b673636874);
INSERT INTO `lng_data` VALUES ('common', 'delete_selected', 'de', 0x4175737761686c206cc3b6736368656e);
INSERT INTO `lng_data` VALUES ('common', 'delete_object', 'de', 0x4cc3b673636865204f626a656b7465);
INSERT INTO `lng_data` VALUES ('common', 'delete_all', 'de', 0x416c6c65206cc3b6736368656e);
INSERT INTO `lng_data` VALUES ('common', 'delete', 'de', 0x4cc3b6736368656e);
INSERT INTO `lng_data` VALUES ('common', 'default_style', 'de', 0x5374616e646172642d5374796c657368656574);
INSERT INTO `lng_data` VALUES ('common', 'default_skin', 'de', 0x5374616e646172642d42656e75747a65726f626572666cc3a4636865);
INSERT INTO `lng_data` VALUES ('common', 'default_role', 'de', 0x5374616e64617264726f6c6c65);
INSERT INTO `lng_data` VALUES ('common', 'default_language', 'de', 0x5374616e646172642d53707261636865);
INSERT INTO `lng_data` VALUES ('common', 'default', 'de', 0x5374616e64617264);
INSERT INTO `lng_data` VALUES ('common', 'db_version', 'de', 0x446174656e62616e6b76657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'db_user', 'de', 0x446174656e62616e6b62656e75747a6572);
INSERT INTO `lng_data` VALUES ('common', 'db_type', 'de', 0x446174656e62616e6b20547970);
INSERT INTO `lng_data` VALUES ('common', 'db_pass', 'de', 0x446174656e62616e6b2d50617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'db_name', 'de', 0x446174656e62616e6b6e616d65);
INSERT INTO `lng_data` VALUES ('common', 'db_host', 'de', 0x446174656e62616e6b20486f7374);
INSERT INTO `lng_data` VALUES ('common', 'days', 'de', 0x54616765);
INSERT INTO `lng_data` VALUES ('common', 'date', 'de', 0x446174756d);
INSERT INTO `lng_data` VALUES ('common', 'dataset', 'de', 0x446174656e7361747a);
INSERT INTO `lng_data` VALUES ('common', 'database_version', 'de', 0x4968726520446174656e62616e6b76657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'database', 'de', 0x446174656e62616e6b);
INSERT INTO `lng_data` VALUES ('common', 'cut', 'de', 0x4175737363686e656964656e);
INSERT INTO `lng_data` VALUES ('common', 'current_password', 'de', 0x4465727a656974696765732050617373776f7274);
INSERT INTO `lng_data` VALUES ('common', 'crs_new', 'de', 0x4e65756572204b757273);
INSERT INTO `lng_data` VALUES ('common', 'crs_management_system', 'de', 0x4b7572736d616e6167656d656e742d53797374656d);
INSERT INTO `lng_data` VALUES ('common', 'crs_available', 'de', 0x56657266c3bc6762617265204b75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_added', 'de', 0x4b75727320616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'crs_add', 'de', 0x4b75727320616e6c6567656e);
INSERT INTO `lng_data` VALUES ('common', 'crs_a', 'de', 0x65696e656e204b757273);
INSERT INTO `lng_data` VALUES ('common', 'crs', 'de', 0x4b757273);
INSERT INTO `lng_data` VALUES ('common', 'create_stylesheet', 'de', 0x5374796c652065727374656c6c656e);
INSERT INTO `lng_data` VALUES ('common', 'create_in', 'de', 0x45727374656c6c656e20696e);
INSERT INTO `lng_data` VALUES ('common', 'create', 'de', 0x45727374656c6c656e);
INSERT INTO `lng_data` VALUES ('common', 'courses', 'de', 0x4b75727365);
INSERT INTO `lng_data` VALUES ('common', 'course', 'de', 0x4b757273);
INSERT INTO `lng_data` VALUES ('common', 'country', 'de', 0x4c616e64);
INSERT INTO `lng_data` VALUES ('common', 'copy', 'de', 0x4b6f70696572656e);
INSERT INTO `lng_data` VALUES ('common', 'context', 'de', 0x4b6f6e74657874);
INSERT INTO `lng_data` VALUES ('common', 'contact_data', 'de', 0x4b6f6e74616b74696e666f726d6174696f6e656e);
INSERT INTO `lng_data` VALUES ('common', 'confirm', 'de', 0x42657374c3a4746967656e);
INSERT INTO `lng_data` VALUES ('common', 'compose', 'de', 0x45727374656c6c656e);
INSERT INTO `lng_data` VALUES ('common', 'comment', 'de', 0x4b6f6d6d656e746172);
INSERT INTO `lng_data` VALUES ('common', 'comma_separated', 'de', 0x4b6f6d6d6167657472656e6e74);
INSERT INTO `lng_data` VALUES ('common', 'clear', 'de', 0x41626272656368656e);
INSERT INTO `lng_data` VALUES ('common', 'city', 'de', 0x5374616474);
INSERT INTO `lng_data` VALUES ('common', 'choose_only_one_language', 'de', 0x4269747465206e75722065696e6520537072616368652061757377c3a4686c656e21);
INSERT INTO `lng_data` VALUES ('common', 'choose_location', 'de', 0x506f736974696f6e20772661756d6c3b686c656e);
INSERT INTO `lng_data` VALUES ('common', 'choose_language', 'de', 0x42697474652077c3a4686c656e205369652049687265205370726163686521);
INSERT INTO `lng_data` VALUES ('common', 'chg_password', 'de', 0x50617373776f727420c3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'chg_language', 'de', 0x5370726163686520c3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'check_languages', 'de', 0x537072616368656e20636865636b656e);
INSERT INTO `lng_data` VALUES ('common', 'check_langfile', 'de', 0x426974746520c3bc6265727072c3bc66656e205369652064696520537072616368646174656921);
INSERT INTO `lng_data` VALUES ('common', 'check', 'de', 0x5072c3bc66656e);
INSERT INTO `lng_data` VALUES ('common', 'chapter_title', 'de', 0x4b61706974656c746974656c);
INSERT INTO `lng_data` VALUES ('common', 'chapter_number', 'de', 0x4b61706974656c6e756d6d6572);
INSERT INTO `lng_data` VALUES ('common', 'chapter', 'de', 0x4b61706974656c);
INSERT INTO `lng_data` VALUES ('common', 'changed_to', 'de', 0x67657765636873656c74207a75);
INSERT INTO `lng_data` VALUES ('common', 'change_sort_direction', 'de', 0x536f72746965727269636874756e6720c3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'change_metadata', 'de', 0x4d657461646174656e20c3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'change_lo_info', 'de', 0x4c65726e65696e68656974656e2d496e666f7320c3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'change_existing_objects', 'de', 0x566f7268616e64656e65204f626a656b746520c3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'change_assignment', 'de', 0x5a7577656973756e6720c3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'change_active_assignment', 'de', 0x416b74697665205a7577656973756e6720c3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'change', 'de', 0xc3a46e6465726e);
INSERT INTO `lng_data` VALUES ('common', 'censorship', 'de', 0x5a656e737572);
INSERT INTO `lng_data` VALUES ('common', 'cc', 'de', 0x4343);
INSERT INTO `lng_data` VALUES ('common', 'categories', 'de', 0x4b617465676f7269656e);
INSERT INTO `lng_data` VALUES ('common', 'cat_new', 'de', 0x4e657565204b617465676f726965);
INSERT INTO `lng_data` VALUES ('common', 'cat_added', 'de', 0x4b617465676f72696520616e67656c656774);
INSERT INTO `lng_data` VALUES ('common', 'cat_add', 'de', 0x4b617465676f72696520616e6c6567656e);
INSERT INTO `lng_data` VALUES ('common', 'cat_a', 'de', 0x65696e65204b617465676f726965);
INSERT INTO `lng_data` VALUES ('common', 'cat', 'de', 0x4b617465676f726965);
INSERT INTO `lng_data` VALUES ('common', 'cannot_uninstall_systemlanguage', 'de', 0x536965206bc3b66e6e656e206469652053797374656d73707261636865206e69636874206465696e7374616c6c696572656e2e);
INSERT INTO `lng_data` VALUES ('common', 'cannot_uninstall_language_in_use', 'de', 0x536965206bc3b66e6e656e206b65696e6520616b7475656c6c2062656e75747a74652053707261636865206465696e7374616c6c696572656e21);
INSERT INTO `lng_data` VALUES ('common', 'cancel', 'de', 0x41626272656368656e);
INSERT INTO `lng_data` VALUES ('common', 'by', 'de', 0x6475726368);
INSERT INTO `lng_data` VALUES ('common', 'btn_undelete', 'de', 0x5769656465726865727374656c6c656e);
INSERT INTO `lng_data` VALUES ('common', 'btn_remove_system', 'de', 0x4175732053797374656d20656e746665726e656e);
INSERT INTO `lng_data` VALUES ('common', 'bookmarks_of', 'de', 0x426f6f6b6d61726b7320766f6e);
INSERT INTO `lng_data` VALUES ('common', 'bookmarks', 'de', 0x426f6f6b6d61726b73);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_target', 'de', 0x5a69656c);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_new', 'de', 0x426f6f6b6d61726b2068696e7a7566c3bc67656e);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_folder_new', 'de', 0x4e6575657220426f6f6b6d61726b204f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_folder_edit', 'de', 0x426f6f6b6d61726b204f72646e6572206265617262656974656e);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_edit', 'de', 0x426f6f6b6d61726b206265617262656974656e);
INSERT INTO `lng_data` VALUES ('common', 'bmf', 'de', 0x426f6f6b6d61726b204f72646e6572);
INSERT INTO `lng_data` VALUES ('common', 'bm', 'de', 0x426f6f6b6d61726b);
INSERT INTO `lng_data` VALUES ('common', 'basic_data', 'de', 0x4772756e64646174656e);
INSERT INTO `lng_data` VALUES ('common', 'basedn', 'de', 0x42617365444e);
INSERT INTO `lng_data` VALUES ('common', 'back', 'de', 0x5a7572c3bc636b);
INSERT INTO `lng_data` VALUES ('common', 'available_languages', 'de', 0x56657266c3bc676261726520537072616368656e);
INSERT INTO `lng_data` VALUES ('common', 'authors', 'de', 0x4175746f72656e);
INSERT INTO `lng_data` VALUES ('common', 'author', 'de', 0x4175746f72);
INSERT INTO `lng_data` VALUES ('common', 'attachments', 'de', 0x4174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('common', 'attachment', 'de', 0x4174746163686d656e74);
INSERT INTO `lng_data` VALUES ('common', 'at_location', 'de', 0x616e20506f736974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'assign_user_to_role', 'de', 0x42656e75747a657220526f6c6c65207a7577656973656e);
INSERT INTO `lng_data` VALUES ('common', 'assign_lo_forum', 'de', 0x4c65726e6d6f64756c2d466f72756d207a756f72646e656e);
INSERT INTO `lng_data` VALUES ('common', 'are_you_sure', 'de', 0x53696e6420536965205369636865723f);
INSERT INTO `lng_data` VALUES ('common', 'archive', 'de', 0x417263686976);
INSERT INTO `lng_data` VALUES ('common', 'answers', 'de', 0x416e74776f7274656e);
INSERT INTO `lng_data` VALUES ('common', 'announce_changes', 'de', 0xc3846e646572756e67656e2062656b616e6e74676562656e);
INSERT INTO `lng_data` VALUES ('common', 'announce', 'de', 0x416e6bc3bc6e646967656e);
INSERT INTO `lng_data` VALUES ('common', 'allow_register', 'de', 0x56657266c3bc6762617220696d20416e6d656c6465666f726d756c61722066c3bc72206e6575652042656e75747a6572);
INSERT INTO `lng_data` VALUES ('common', 'all_objects', 'de', 0x416c6c65204f626a656b7465);
INSERT INTO `lng_data` VALUES ('common', 'adopt_perm_from_template', 'de', 0xc39c6265726e65686d652052656368746565696e7374656c6c756e67656e20766f6e20526f6c6c656e766f726c616765);
INSERT INTO `lng_data` VALUES ('common', 'adopt', 'de', 0xc39c6265726e65686d656e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_delete_yourself', 'de', 0x4465707265737369763f204c6562656e736dc3bc64653f20536965206bc3b66e6e656e206e696368742049687220656967656e65732042656e75747a65726b6f6e746f206cc3b6736368656e2e);
INSERT INTO `lng_data` VALUES ('common', 'administrator', 'de', 0x41646d696e6973747261746f72);
INSERT INTO `lng_data` VALUES ('common', 'administration', 'de', 0x41646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'administrate', 'de', 0x41646d696e69737472696572656e);
INSERT INTO `lng_data` VALUES ('common', 'add_member', 'de', 0x4d6974676c6965642068696e7a7566c3bc67656e);
INSERT INTO `lng_data` VALUES ('common', 'add_author', 'de', 0x4175746f722068696e7a7566c3bc67656e);
INSERT INTO `lng_data` VALUES ('common', 'add', 'de', 0x68696e7a7566c3bc67656e);
INSERT INTO `lng_data` VALUES ('common', 'active_roles', 'de', 0x416b7469766520526f6c6c656e);
INSERT INTO `lng_data` VALUES ('common', 'actions', 'de', 0x416b74696f6e656e);
INSERT INTO `lng_data` VALUES ('common', 'action_aborted', 'de', 0x416b74696f6e206162676562726f6368656e);
INSERT INTO `lng_data` VALUES ('common', 'access', 'de', 0x5a7567616e67);
INSERT INTO `lng_data` VALUES ('common', 'accept_usr_agreement', 'de', 0x42656e75747a65722d4c697a656e7a20616b7a657074696572656e3f);
INSERT INTO `lng_data` VALUES ('common', 'absolute_path', 'de', 0x4162736f6c757465722050666164);
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
INSERT INTO `lng_data` VALUES ('common', 'url_description', 'en', 0x55524c204465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'user', 'en', 0x55736572);
INSERT INTO `lng_data` VALUES ('common', 'url', 'en', 0x55524c);
INSERT INTO `lng_data` VALUES ('common', 'upload', 'en', 0x55706c6f6164);
INSERT INTO `lng_data` VALUES ('common', 'update_applied', 'en', 0x557064617465204170706c696564);
INSERT INTO `lng_data` VALUES ('common', 'update_language', 'en', 0x557064617465204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'unknown', 'en', 0x554e4b4e4f574e);
INSERT INTO `lng_data` VALUES ('common', 'unread', 'en', 0x556e72656164);
INSERT INTO `lng_data` VALUES ('common', 'uninstalled', 'en', 0x756e696e7374616c6c65642e);
INSERT INTO `lng_data` VALUES ('common', 'uninstall', 'en', 0x556e696e7374616c6c);
INSERT INTO `lng_data` VALUES ('common', 'uid', 'en', 0x554944);
INSERT INTO `lng_data` VALUES ('common', 'type_your_message_here', 'en', 0x5479706520596f7572204d6573736167652048657265);
INSERT INTO `lng_data` VALUES ('common', 'type', 'en', 0x54797065);
INSERT INTO `lng_data` VALUES ('common', 'typ', 'en', 0x4f626a656374205479706520446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'txt_add_object_instruction2', 'en', 0x2e);
INSERT INTO `lng_data` VALUES ('common', 'txt_add_object_instruction1', 'en', 0x42726f77736520746f20746865206c6f636174696f6e20776865726520796f752077616e7420746f20616464);
INSERT INTO `lng_data` VALUES ('common', 'treeview', 'en', 0x547265652056696577);
INSERT INTO `lng_data` VALUES ('common', 'trash', 'en', 0x5472617368);
INSERT INTO `lng_data` VALUES ('common', 'tpl_path', 'en', 0x54656d706c6174652050617468);
INSERT INTO `lng_data` VALUES ('common', 'total', 'en', 0x546f74616c);
INSERT INTO `lng_data` VALUES ('common', 'today', 'en', 0x546f646179);
INSERT INTO `lng_data` VALUES ('common', 'to', 'en', 0x546f);
INSERT INTO `lng_data` VALUES ('common', 'title', 'en', 0x5469746c65);
INSERT INTO `lng_data` VALUES ('common', 'time', 'en', 0x54696d65);
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
INSERT INTO `lng_data` VALUES ('common', 'sort_by_this_column', 'en', 0x536f7274206279207468697320636f6c756d6e);
INSERT INTO `lng_data` VALUES ('common', 'smtp', 'en', 0x534d5450);
INSERT INTO `lng_data` VALUES ('common', 'slm_added', 'en', 0x53434f524d204c6561726e696e674d6f64756c65206164646564);
INSERT INTO `lng_data` VALUES ('common', 'slm', 'en', 0x53434f524d204c4d);
INSERT INTO `lng_data` VALUES ('common', 'signature', 'en', 0x5369676e6174757265);
INSERT INTO `lng_data` VALUES ('common', 'show_owner', 'en', 0x53686f77204f776e6572);
INSERT INTO `lng_data` VALUES ('common', 'show_members', 'en', 0x446973706c6179204d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'show_list', 'en', 0x53686f77204c697374);
INSERT INTO `lng_data` VALUES ('common', 'settings', 'en', 0x53657474696e6773);
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
INSERT INTO `lng_data` VALUES ('common', 'select_file', 'en', 0x53656c6563742066696c65);
INSERT INTO `lng_data` VALUES ('common', 'select_mode', 'en', 0x53656c656374206d6f6465);
INSERT INTO `lng_data` VALUES ('common', 'select_all', 'en', 0x53656c65637420416c6c);
INSERT INTO `lng_data` VALUES ('common', 'select', 'en', 0x53656c656374);
INSERT INTO `lng_data` VALUES ('common', 'sections', 'en', 0x53656374696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'search_user', 'en', 0x5365617263682055736572);
INSERT INTO `lng_data` VALUES ('common', 'search_result', 'en', 0x53656172636820726573756c74);
INSERT INTO `lng_data` VALUES ('common', 'search_recipient', 'en', 0x53656172636820526563697069656e74);
INSERT INTO `lng_data` VALUES ('common', 'search_new', 'en', 0x4e657720536561726368);
INSERT INTO `lng_data` VALUES ('common', 'search_in', 'en', 0x53656172636820696e);
INSERT INTO `lng_data` VALUES ('common', 'search', 'en', 0x536561726368);
INSERT INTO `lng_data` VALUES ('common', 'saved', 'en', 0x5361766564);
INSERT INTO `lng_data` VALUES ('common', 'saved_successfully', 'en', 0x5361766564205375636365737366756c6c79);
INSERT INTO `lng_data` VALUES ('common', 'save_return', 'en', 0x5361766520616e642052657475726e);
INSERT INTO `lng_data` VALUES ('common', 'save_refresh', 'en', 0x5361766520616e642052656672657368);
INSERT INTO `lng_data` VALUES ('common', 'save_message', 'en', 0x53617665204d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'save_and_back', 'en', 0x5361766520416e64204261636b);
INSERT INTO `lng_data` VALUES ('common', 'salutation_m', 'en', 0x4d722e);
INSERT INTO `lng_data` VALUES ('common', 'save', 'en', 0x53617665);
INSERT INTO `lng_data` VALUES ('common', 'salutation', 'en', 0x53616c75746174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'salutation_f', 'en', 0x4d732e2f4d72732e);
INSERT INTO `lng_data` VALUES ('common', 'rolt_added', 'en', 0x526f6c652074656d706c617465206164646564);
INSERT INTO `lng_data` VALUES ('common', 'rolt', 'en', 0x526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'rolf_added', 'en', 0x526f6c6520466f6c646572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'role_deleted', 'en', 0x526f6c652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'roles', 'en', 0x526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'rolf', 'en', 0x526f6c6520466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'role_assignment', 'en', 0x526f6c652041737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'role', 'en', 0x526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_added', 'en', 0x526f6c65206164646564);
INSERT INTO `lng_data` VALUES ('common', 'right', 'en', 0x5269676874);
INSERT INTO `lng_data` VALUES ('common', 'rights', 'en', 0x526967687473);
INSERT INTO `lng_data` VALUES ('common', 'reset', 'en', 0x5265736574);
INSERT INTO `lng_data` VALUES ('common', 'retype_password', 'en', 0x5265747970652050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'reply', 'en', 0x5265706c79);
INSERT INTO `lng_data` VALUES ('common', 'required_field', 'en', 0x5265717569726564204669656c64);
INSERT INTO `lng_data` VALUES ('common', 'rename', 'en', 0x52656e616d65);
INSERT INTO `lng_data` VALUES ('common', 'register', 'en', 0x5265676973746572);
INSERT INTO `lng_data` VALUES ('common', 'registered_since', 'en', 0x526567697374657265642073696e6365);
INSERT INTO `lng_data` VALUES ('common', 'refresh_list', 'en', 0x52656672657368204c697374);
INSERT INTO `lng_data` VALUES ('common', 'refresh', 'en', 0x52656672657368);
INSERT INTO `lng_data` VALUES ('common', 'refresh_languages', 'en', 0x52656672657368204c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'read', 'en', 0x52656164);
INSERT INTO `lng_data` VALUES ('common', 'recipient', 'en', 0x526563697069656e74);
INSERT INTO `lng_data` VALUES ('common', 'quote', 'en', 0x51756f7465);
INSERT INTO `lng_data` VALUES ('common', 'quit', 'en', 0x51756974);
INSERT INTO `lng_data` VALUES ('common', 'question', 'en', 0x5175657374696f6e);
INSERT INTO `lng_data` VALUES ('common', 'publishing_organisation', 'en', 0x5075626c697368696e67204f7267616e69736174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'published', 'en', 0x5075626c6973686564);
INSERT INTO `lng_data` VALUES ('common', 'publication', 'en', 0x5075626c69636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'publication_date', 'en', 0x5075626c69636174696f6e2044617465);
INSERT INTO `lng_data` VALUES ('common', 'public_profile', 'en', 0x5075626c69632050726f66696c65);
INSERT INTO `lng_data` VALUES ('common', 'pub_section', 'en', 0x5075626c69632053656374696f6e);
INSERT INTO `lng_data` VALUES ('common', 'properties', 'en', 0x50726f70657274696573);
INSERT INTO `lng_data` VALUES ('common', 'print', 'en', 0x5072696e74);
INSERT INTO `lng_data` VALUES ('common', 'profile_changed', 'en', 0x596f75722070726f66696c6520686173206368616e676564);
INSERT INTO `lng_data` VALUES ('common', 'previous', 'en', 0x70726576696f7573);
INSERT INTO `lng_data` VALUES ('common', 'position', 'en', 0x506f736974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'presentation_options', 'en', 0x50726573656e746174696f6e204f7074696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'port', 'en', 0x506f7274);
INSERT INTO `lng_data` VALUES ('common', 'please_enter_title', 'en', 0x506c6561736520656e7465722061207469746c65);
INSERT INTO `lng_data` VALUES ('common', 'please_enter_target', 'en', 0x506c6561736520656e746572206120746172676574);
INSERT INTO `lng_data` VALUES ('common', 'phrase', 'en', 0x506872617365);
INSERT INTO `lng_data` VALUES ('common', 'phone', 'en', 0x50686f6e65);
INSERT INTO `lng_data` VALUES ('common', 'persons', 'en', 0x506572736f6e656e);
INSERT INTO `lng_data` VALUES ('common', 'personal_profile', 'en', 0x506572736f6e616c2050726f66696c65);
INSERT INTO `lng_data` VALUES ('common', 'personal_desktop', 'en', 0x506572736f6e616c204465736b746f70);
INSERT INTO `lng_data` VALUES ('common', 'personal_picture', 'en', 0x506572736f6e616c2050696374757265);
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
INSERT INTO `lng_data` VALUES ('common', 'paste', 'en', 0x5061737465);
INSERT INTO `lng_data` VALUES ('common', 'password', 'en', 0x50617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'passwd_wrong', 'en', 0x5468652070617373776f726420796f7520656e74657265642069732077726f6e6721);
INSERT INTO `lng_data` VALUES ('common', 'passwd_not_match', 'en', 0x596f757220656e747269657320666f7220746865206e65772070617373776f726420646f6e2774206d617463682120506c656173652072652d656e74657220796f7572206e65772070617373776f72642e);
INSERT INTO `lng_data` VALUES ('common', 'passwd_invalid', 'en', 0x546865206e65772070617373776f726420697320696e76616c69642120506c656173652063686f6f736520616e6f746865722070617373776f72642e);
INSERT INTO `lng_data` VALUES ('common', 'passwd', 'en', 0x50617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'parse', 'en', 0x5061727365);
INSERT INTO `lng_data` VALUES ('common', 'parameter', 'en', 0x506172616d65746572);
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
INSERT INTO `lng_data` VALUES ('common', 'obj_usrf', 'en', 0x557365722041646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_usr', 'en', 0x55736572);
INSERT INTO `lng_data` VALUES ('common', 'obj_uset', 'en', 0x557365722053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj_type', 'en', 0x4f626a6563742054797065);
INSERT INTO `lng_data` VALUES ('common', 'obj_typ', 'en', 0x4f626a6563747479706520446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_slm', 'en', 0x53434f524d204c4d);
INSERT INTO `lng_data` VALUES ('common', 'obj_st', 'en', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolt', 'en', 0x526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'obj_root', 'en', 0x726f6f74);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf', 'en', 0x526f6c652041646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_role', 'en', 0x526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_pg', 'en', 0x50616765);
INSERT INTO `lng_data` VALUES ('common', 'obj_owner', 'en', 0x54686973204f626a656374206973206f776e6564206279);
INSERT INTO `lng_data` VALUES ('common', 'obj_objf', 'en', 0x4f626a6563742041646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_notf', 'en', 0x4e6f74652041646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_note', 'en', 0x4e6f7465);
INSERT INTO `lng_data` VALUES ('common', 'obj_not_found', 'en', 0x4f626a656374204e6f7420466f756e64);
INSERT INTO `lng_data` VALUES ('common', 'obj_mail', 'en', 0x4d61696c2053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj_lo', 'en', 0x4c6561726e696e674f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'obj_lngf', 'en', 0x4c616e67756167652041646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_lng', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'obj_lm', 'en', 0x4c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_le', 'en', 0x4c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_grp', 'en', 0x47726f7570);
INSERT INTO `lng_data` VALUES ('common', 'obj_glo', 'en', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'obj_frm', 'en', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'obj_file', 'en', 0x46696c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_crs', 'en', 0x436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'obj_cat', 'en', 0x43617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'obj_adm', 'en', 0x53797374656d2053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj', 'en', 0x4f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'not_logged_in', 'en', 0x596f757220617265206e6f74206c6f6767656420696e);
INSERT INTO `lng_data` VALUES ('common', 'not_installed', 'en', 0x4e6f7420496e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'normal', 'en', 0x4e6f726d616c);
INSERT INTO `lng_data` VALUES ('common', 'none', 'en', 0x4e6f6e65);
INSERT INTO `lng_data` VALUES ('common', 'no_title', 'en', 0x4e6f205469746c65);
INSERT INTO `lng_data` VALUES ('common', 'no_objects', 'en', 0x4e6f206f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'no_import_available', 'en', 0x496d706f7274206e6f7420617661696c61626c6520666f722074797065);
INSERT INTO `lng_data` VALUES ('common', 'no_date', 'en', 0x4e6f2064617465);
INSERT INTO `lng_data` VALUES ('common', 'no_checkbox', 'en', 0x4e6f20636865636b626f7820636865636b656421);
INSERT INTO `lng_data` VALUES ('common', 'no', 'en', 0x4e6f);
INSERT INTO `lng_data` VALUES ('common', 'nickname', 'en', 0x4e69636b6e616d65);
INSERT INTO `lng_data` VALUES ('common', 'next', 'en', 0x6e657874);
INSERT INTO `lng_data` VALUES ('common', 'news', 'en', 0x4e657773);
INSERT INTO `lng_data` VALUES ('common', 'new_mail', 'en', 0x4e6577206d61696c21);
INSERT INTO `lng_data` VALUES ('common', 'new_language', 'en', 0x4e6577204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'new_group', 'en', 0x4e65772047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'new_folder', 'en', 0x4e657720466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'new', 'en', 0x4e6577);
INSERT INTO `lng_data` VALUES ('common', 'name', 'en', 0x4e616d65);
INSERT INTO `lng_data` VALUES ('common', 'multimedia', 'en', 0x4d756c74696d65646961);
INSERT INTO `lng_data` VALUES ('common', 'msg_userassignment_changed', 'en', 0x5573657261737369676d656e74206368616e676564);
INSERT INTO `lng_data` VALUES ('common', 'msg_user_last_role2', 'en', 0x506c656173652064656c65746520746865207573657273206f722061737369676e207468656d20746f20616e6f7468657220726f6c6520696e206f7264657220746f2064656c657465207468697320726f6c652e);
INSERT INTO `lng_data` VALUES ('common', 'msg_user_last_role1', 'en', 0x54686520666f6c6c6f77696e67207573657273206172652061737369676e656420746f207468697320726f6c65206f6e6c793a);
INSERT INTO `lng_data` VALUES ('common', 'msg_undeleted', 'en', 0x4f626a65637428732920756e64656c657465642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_trash_empty', 'en', 0x546865726520617265206e6f2064656c65746564206f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'msg_roleassignment_changed', 'en', 0x526f6c6561737369676d656e74206368616e676564);
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
INSERT INTO `lng_data` VALUES ('common', 'msg_cancel_delete', 'en', 0x416374696f6e2063616e63656c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'move_to', 'en', 0x4d6f766520746f);
INSERT INTO `lng_data` VALUES ('common', 'modules', 'en', 0x4d6f64756c6573);
INSERT INTO `lng_data` VALUES ('common', 'module', 'en', 0x6d6f64756c65);
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
INSERT INTO `lng_data` VALUES ('common', 'mail_folders', 'en', 0x4d61696c20466f6c64657273);
INSERT INTO `lng_data` VALUES ('common', 'mail_e_sent', 'en', 0x53656e74);
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
INSERT INTO `lng_data` VALUES ('common', 'logout', 'en', 0x4c6f676f7574);
INSERT INTO `lng_data` VALUES ('common', 'los', 'en', 0x4c6561726e696e67204f626a65637473);
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
INSERT INTO `lng_data` VALUES ('common', 'level', 'en', 0x4c6576656c);
INSERT INTO `lng_data` VALUES ('common', 'link', 'en', 0x4c696e6b);
INSERT INTO `lng_data` VALUES ('common', 'learning_objects', 'en', 0x4c6561726e696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'ldap', 'en', 0x4c444150);
INSERT INTO `lng_data` VALUES ('common', 'launch', 'en', 0x4c61756e6368);
INSERT INTO `lng_data` VALUES ('common', 'lastname', 'en', 0x4c6173746e616d65);
INSERT INTO `lng_data` VALUES ('common', 'last_visit', 'en', 0x4c617374205669736974);
INSERT INTO `lng_data` VALUES ('common', 'last_change', 'en', 0x4c617374204368616e6765);
INSERT INTO `lng_data` VALUES ('common', 'languages_updated', 'en', 0x416c6c20696e7374616c6c6564206c616e6775616765732068617665206265656e2075706461746564);
INSERT INTO `lng_data` VALUES ('common', 'languages_already_uninstalled', 'en', 0x46756e6e792e2043686f73656e206c616e67756167652873292061726520616c726561647920756e696e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'languages_already_installed', 'en', 0x46756e6e792e2043686f73656e206c616e67756167652873292061726520616c726561647920696e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'languages', 'en', 0x4c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'language_not_installed', 'en', 0x6973206e6f7420696e7374616c6c65642e20506c6561736520696e7374616c6c2074686174206c616e6775616765206669727374);
INSERT INTO `lng_data` VALUES ('common', 'language', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'langfile_found', 'en', 0x4c616e67756167652066696c6520666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'lang_xx', 'en', 0x437573746f6d);
INSERT INTO `lng_data` VALUES ('common', 'lang_version', 'en', 0x31);
INSERT INTO `lng_data` VALUES ('common', 'lang_timeformat', 'en', 0x483a693a73);
INSERT INTO `lng_data` VALUES ('common', 'lang_sep_thousand', 'en', 0x2c);
INSERT INTO `lng_data` VALUES ('common', 'lang_se', 'en', 0x53776564697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_sep_decimal', 'en', 0x2e);
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
INSERT INTO `lng_data` VALUES ('common', 'is_already_your', 'en', 0x697320616c726561647920796f7572);
INSERT INTO `lng_data` VALUES ('common', 'ip_address', 'en', 0x49502041646472657373);
INSERT INTO `lng_data` VALUES ('common', 'internal_system', 'en', 0x496e7465726e616c2073797374656d);
INSERT INTO `lng_data` VALUES ('common', 'institution', 'en', 0x496e737469747574696f6e);
INSERT INTO `lng_data` VALUES ('common', 'installed', 'en', 0x496e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'install', 'en', 0x496e7374616c6c);
INSERT INTO `lng_data` VALUES ('common', 'inst_name', 'en', 0x496e7374616c6c6174696f6e204e616d65);
INSERT INTO `lng_data` VALUES ('common', 'inst_id', 'en', 0x496e7374616c6c6174696f6e204944);
INSERT INTO `lng_data` VALUES ('common', 'inst_info', 'en', 0x496e7374616c6c6174696f6e20496e666f);
INSERT INTO `lng_data` VALUES ('common', 'insert', 'en', 0x496e73657274);
INSERT INTO `lng_data` VALUES ('common', 'inifile', 'en', 0x496e692d46696c65);
INSERT INTO `lng_data` VALUES ('common', 'input_error', 'en', 0x496e707574206572726f72);
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
INSERT INTO `lng_data` VALUES ('common', 'help', 'en', 0x48656c70);
INSERT INTO `lng_data` VALUES ('common', 'grp_new', 'en', 0x4e65772047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_added', 'en', 0x47726f7570206164646564);
INSERT INTO `lng_data` VALUES ('common', 'grp_add', 'en', 0x4164642047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_a', 'en', 0x612047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp', 'en', 0x47726f7570);
INSERT INTO `lng_data` VALUES ('common', 'groups_overview', 'en', 0x47726f757073204f76657276696577);
INSERT INTO `lng_data` VALUES ('common', 'groups', 'en', 0x47726f757073);
INSERT INTO `lng_data` VALUES ('common', 'group_status_public', 'en', 0x5075626c6963);
INSERT INTO `lng_data` VALUES ('common', 'group_status_private', 'en', 0x50726976617465);
INSERT INTO `lng_data` VALUES ('common', 'group_status_closed', 'en', 0x436c6f736564);
INSERT INTO `lng_data` VALUES ('common', 'group_status', 'en', 0x47726f757020537461747573);
INSERT INTO `lng_data` VALUES ('common', 'group_not_available', 'en', 0x616e792067726f757020617661696c61626c65);
INSERT INTO `lng_data` VALUES ('common', 'group_name', 'en', 0x47726f75706e616d65);
INSERT INTO `lng_data` VALUES ('common', 'group_members', 'en', 0x47726f7570204d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'group_filesharing', 'en', 0x47726f75702046696c652053686172696e67);
INSERT INTO `lng_data` VALUES ('common', 'group_details', 'en', 0x47726f75702044657461696c73);
INSERT INTO `lng_data` VALUES ('common', 'group_any_objects', 'en', 0x4e6f205375626f626a65637473206176696c61626c65);
INSERT INTO `lng_data` VALUES ('common', 'glossary_added', 'en', 0x476c6f7373617279206164646564);
INSERT INTO `lng_data` VALUES ('common', 'glossary', 'en', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_added', 'en', 0x416464656420676c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo', 'en', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'generate', 'en', 0x47656e6572617465);
INSERT INTO `lng_data` VALUES ('common', 'gender', 'en', 0x47656e646572);
INSERT INTO `lng_data` VALUES ('common', 'functions', 'en', 0x46756e6374696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'from', 'en', 0x46726f6d);
INSERT INTO `lng_data` VALUES ('common', 'frm_new', 'en', 0x4e657720466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_added', 'en', 0x466f72756d206164646564);
INSERT INTO `lng_data` VALUES ('common', 'frm_add', 'en', 0x41646420466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_a', 'en', 0x6120466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm', 'en', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'forums_overview', 'en', 0x466f72756d73204f76657276696577);
INSERT INTO `lng_data` VALUES ('common', 'forums', 'en', 0x466f72756d73);
INSERT INTO `lng_data` VALUES ('common', 'forum', 'en', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'form_empty_fields', 'en', 0x506c6561736520636f6d706c657465207468657365206669656c64733a);
INSERT INTO `lng_data` VALUES ('common', 'forename', 'en', 0x466f72656e616d65);
INSERT INTO `lng_data` VALUES ('common', 'folders', 'en', 0x466f6c64657273);
INSERT INTO `lng_data` VALUES ('common', 'folder_added', 'en', 0x466f6c646572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'folder', 'en', 0x466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold', 'en', 0x466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'flatview', 'en', 0x466c61742056696577);
INSERT INTO `lng_data` VALUES ('common', 'firstname', 'en', 0x46697273746e616d65);
INSERT INTO `lng_data` VALUES ('common', 'fill_out_all_required_fields', 'en', 0x506c656173652066696c6c206f757420616c6c207265717569726564206669656c6473);
INSERT INTO `lng_data` VALUES ('common', 'files_location', 'en', 0x46696c6573204c6f636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'file_version', 'en', 0x56657273696f6e2050726f766964656420696e2046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_valid', 'en', 0x46696c652069732076616c696421);
INSERT INTO `lng_data` VALUES ('common', 'file_not_valid', 'en', 0x46696c65206e6f742076616c696421);
INSERT INTO `lng_data` VALUES ('common', 'file_not_found', 'en', 0x46696c65204e6f7420466f756e64);
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
INSERT INTO `lng_data` VALUES ('common', 'drafts', 'en', 0x447261667473);
INSERT INTO `lng_data` VALUES ('common', 'download', 'en', 0x446f776e6c6f6164);
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
INSERT INTO `lng_data` VALUES ('common', 'date', 'en', 0x44617465);
INSERT INTO `lng_data` VALUES ('common', 'dataset', 'en', 0x44617461736574);
INSERT INTO `lng_data` VALUES ('common', 'database_version', 'en', 0x43757272656e742044617461626173652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'database', 'en', 0x4461746162617365);
INSERT INTO `lng_data` VALUES ('common', 'cut', 'en', 0x437574);
INSERT INTO `lng_data` VALUES ('common', 'current_password', 'en', 0x43757272656e742050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'crs_new', 'en', 0x4e657720436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_management_system', 'en', 0x436f75727365204d616e6167656d656e742053797374656d);
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
INSERT INTO `lng_data` VALUES ('common', 'confirm', 'en', 0x436f6e6669726d);
INSERT INTO `lng_data` VALUES ('common', 'compose', 'en', 0x436f6d706f7365);
INSERT INTO `lng_data` VALUES ('common', 'comment', 'en', 0x636f6d6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'comma_separated', 'en', 0x436f6d6d6120536570617261746564);
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
INSERT INTO `lng_data` VALUES ('common', 'cat_new', 'en', 0x4e65772043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_added', 'en', 0x43617465676f7279206164646564);
INSERT INTO `lng_data` VALUES ('common', 'cat_add', 'en', 0x4164642043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_a', 'en', 0x612063617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat', 'en', 0x43617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cannot_uninstall_systemlanguage', 'en', 0x596f752063616e6e6f7420756e696e7374616c6c207468652073797374656d206c616e677561676521);
INSERT INTO `lng_data` VALUES ('common', 'cannot_uninstall_language_in_use', 'en', 0x596f752063616e6e6f7420756e696e7374616c6c20746865206c616e67756167652063757272656e746c7920696e2075736521);
INSERT INTO `lng_data` VALUES ('common', 'cancel', 'en', 0x43616e63656c);
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
INSERT INTO `lng_data` VALUES ('common', 'are_you_sure', 'en', 0x41726520796f7520737572653f);
INSERT INTO `lng_data` VALUES ('common', 'archive', 'en', 0x41726368697665);
INSERT INTO `lng_data` VALUES ('common', 'answers', 'en', 0x416e7377657273);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_delete_yourself', 'en', 0x446f20796f75206665656c206465707265737365643f207765617279206f66206c6966653f20596f752063616e6e6f742064656c65746520796f7572206f776e2075736572206163636f756e742e);
INSERT INTO `lng_data` VALUES ('common', 'announce_changes', 'en', 0x416e6e6f756e6365204368616e676573);
INSERT INTO `lng_data` VALUES ('common', 'announce', 'en', 0x416e6e6f756e6365);
INSERT INTO `lng_data` VALUES ('common', 'allow_register', 'en', 0x417661696c61626c6520696e20726567697374726174696f6e20666f726d20666f72206e6577207573657273);
INSERT INTO `lng_data` VALUES ('common', 'all_objects', 'en', 0x416c6c204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'adopt_perm_from_template', 'en', 0x41646f7074207065726d697373696f6e2073657474696e67732066726f6d20526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'adopt', 'en', 0x61646f7074);
INSERT INTO `lng_data` VALUES ('common', 'administrator', 'en', 0x41646d696e6973747261746f72);
INSERT INTO `lng_data` VALUES ('common', 'administration', 'en', 0x41646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'administrate', 'en', 0x41646d696e69737472617465);
INSERT INTO `lng_data` VALUES ('common', 'add_member', 'en', 0x416464204d656d626572);
INSERT INTO `lng_data` VALUES ('common', 'add_author', 'en', 0x41646420417574686f72);
INSERT INTO `lng_data` VALUES ('common', 'add', 'en', 0x416464);
INSERT INTO `lng_data` VALUES ('common', 'active_roles', 'en', 0x41637469766520526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'actions', 'en', 0x416374696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'action_aborted', 'en', 0x416374696f6e2061626f72746564);
INSERT INTO `lng_data` VALUES ('common', 'access', 'en', 0x416363657373);
INSERT INTO `lng_data` VALUES ('common', 'accept_usr_agreement', 'en', 0x41636365707420757365722061677265656d656e743f);
INSERT INTO `lng_data` VALUES ('common', 'absolute_path', 'en', 0x4162736f6c7574652050617468);
INSERT INTO `lng_data` VALUES ('mail', 'mail_bc_not_valid', 'de', 0x4465722042432d456d7066c3a46e67657220697374206e696368742067c3bc6c746967);
INSERT INTO `lng_data` VALUES ('mail', 'mail_bc_search', 'de', 0x42432d456d7066c3a46e6765722073756368656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_cc_not_valid', 'de', 0x4465722043432d456d7066c3a46e67657220697374206e696368742067c3bc6c746967);
INSERT INTO `lng_data` VALUES ('mail', 'mail_cc_search', 'de', 0x43432d456d7066c3a46e6765722073756368656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_change_to_folder', 'de', 0x5765636873656c6e207a75204f72646e65723a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_check_your_email_addr', 'de', 0x426974746520c3bc6265727072c3bc66656e20536965204968726520452d4d61696c2041647265737365);
INSERT INTO `lng_data` VALUES ('mail', 'mail_compose', 'de', 0x4e61636872696368742065727374656c6c656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_deleted', 'de', 0x446965204e616368726963687428656e292077757264656e2067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('mail', 'mail_deleted_entry', 'de', 0x4469652045696e7472c3a467652077757264656e2067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('mail', 'mail_email_forbidden', 'de', 0x5369652073696e64206e69636874206265726563687469677420452d4d61696c73207a752076657273636869636b656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_entry_added', 'de', 0x45696e206e657565722045696e747261672077757264652068696e7a75676566c3bc6774);
INSERT INTO `lng_data` VALUES ('mail', 'mail_entry_changed', 'de', 0x4465722045696e74726167207775726465206765c3a46e64657274);
INSERT INTO `lng_data` VALUES ('mail', 'mail_file_name', 'de', 0x44617465696e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_file_size', 'de', 0x44617465696772c3b6737365);
INSERT INTO `lng_data` VALUES ('mail', 'mail_files_deleted', 'de', 0x44696520446174656928656e29207775726465286e292067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_created', 'de', 0x45696e206e65756572204f72646e657220777572646520616e67656c656774);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_deleted', 'de', 0x446572204f72646e65722077757264652067656cc3b673636874);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_exists', 'de', 0x45696e204f72646e6572206d69742064696573656d204e616d656e206578697374696572742062657265697473);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_name', 'de', 0x4f72646e65726e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_name_changed', 'de', 0x446572204f72646e657220777572646520756d62656e616e6e74);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_options', 'de', 0x4f72646e65722045696e7374656c6c756e67656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_following_rcp_not_valid', 'de', 0x44696520666f6c67656e64656e20456d7066c3a46e6765722073696e64206e696368742067c3bc6c7469673a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_global_options', 'de', 0x416c6c67656d65696e652045696e7374656c6c756e67656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_insert_folder_name', 'de', 0x426974746520676562656e205369652065696e656e204f72646e65726e616d656e2065696e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_insert_query', 'de', 0x426974746520676562656e205369652065696e656e2053756368626567726966662065696e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_intern', 'de', 0x496e7465726e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_mark_read', 'de', 0x416c732067656c6573656e206d61726b696572656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_mark_unread', 'de', 0x416c7320756e67656c6573656e206d61726b696572656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_message_send', 'de', 0x49687265204e61636872696368742077757264652076657273616e6474);
INSERT INTO `lng_data` VALUES ('mail', 'mail_move_error', 'de', 0x4665686c6572206265696d2053656e64656e20646572204e6163687269636874);
INSERT INTO `lng_data` VALUES ('mail', 'mail_move_to', 'de', 0x566572736368696562656e206e6163683a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_moved', 'de', 0x49687265204e6163687269636874656e2077757264656e207665727363686f62656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_moved_to_trash', 'de', 0x446965204e616368726963687428656e29207775726465286e2920696e2064656e205061706965726b6f7262207665727363686f62656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_new_file', 'de', 0x4e6575652044617465692068696e7a7566c3bc67656e3a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_attach_allowed', 'de', 0x53797374656d6e6163687269636874656e206bc3b66e6e656e206b65696e65204174746163686d656e7473206265696e68616c74656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_attachments_found', 'de', 0x45732077757264656e206b65696e65204174746163686d656e747320676566756e64656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_options_of', 'de', 0x45696e7374656c6c756e67656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_recipient_not_valid', 'de', 0x44657220456d7066c3a46e67657220697374206e696368742067c3bc6c746967);
INSERT INTO `lng_data` VALUES ('mail', 'mail_s', 'de', 0x4e6163687269636874656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_s_unread', 'de', 0x556e67656c6573656e65204e6163687269636874656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_saved', 'de', 0x49687265204e6163687269636874207775726465206765737065696368657274);
INSERT INTO `lng_data` VALUES ('mail', 'mail_search_addressbook', 'de', 0x696d2041646472657373627563682073756368656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_search_system', 'de', 0x696d2053797374656d2073756368656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_select_one_entry', 'de', 0x536965206dc3bc7373656e206d696e64657374656e732065696e656e2045696e747261672061757377c3a4686c656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_select_one_file', 'de', 0x536965206dc3bc7373656e206d696e64657374656e732065696e652044617465692061757377c3a4686c656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete', 'de', 0x53696e6420536965207369636865722c206461c39f20646965206d61726b69657274656e204e6163687269636874656e2067656cc3b6736368742077657264656e20736f6c6c656e3f);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_entry', 'de', 0x53696e6420536965207369636865722c206461c39f20646965206d61726b69657274656e2045696e7472c3a467652067656cc3b6736368742077657264656e20736f6c6c656e3f);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_file', 'de', 0x53696e6420536965207369636865722c206461c39f20646965206d61726b69657274656e204461746569656e2067656cc3b6736368742077657264656e20736f6c6c656e3f);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_folder', 'de', 0x446572204f72646e657220756e64207365696e20496e68616c742077657264656e20756e77696465727275666c6963682067656cc3b67363687421);
INSERT INTO `lng_data` VALUES ('mail', 'mail_to_search', 'de', 0x456d7066c3a46e6765722073756368656e);
INSERT INTO `lng_data` VALUES ('mail', 'mail_user_addr_n_valid', 'de', 0x44696520666f6c67656e64656e204164726573736174656e20686162656e206b65696e652067c3bc6c7469676520452d4d61696c2041647265737365);
INSERT INTO `lng_data` VALUES ('mail', 'search_bc_recipient', 'de', 0x42432d456d7066c3a46e6765722073756368656e);
INSERT INTO `lng_data` VALUES ('mail', 'search_cc_recipient', 'de', 0x43432d456d7066c3a46e6765722073756368656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AD', 'de', 0x416e646f727261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AE', 'de', 0x56657265696e696774652041726162697363686520456d6972617465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AF', 'de', 0x41666768616e697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AG', 'de', 0x416e746967756120756e642042617262756461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AI', 'de', 0x416e6775696c6c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AL', 'de', 0x416c62616e69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AM', 'de', 0x41726d656e69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AN', 'de', 0x4e69656465726cc3a46e64697363686520416e74696c6c656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AO', 'de', 0x416e676f6c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AQ', 'de', 0x416e7461726b746973);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AR', 'de', 0x417267656e74696e69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AS', 'de', 0x416d6572696b616e697363682053616d6f61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AT', 'de', 0xc396737465727265696368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AU', 'de', 0x4175737472616c69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AW', 'de', 0x4172756261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AZ', 'de', 0x417a657262616964736368616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BA', 'de', 0x426f736e69656e2d4865727a65676f77696e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BB', 'de', 0x4261726261646f73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BD', 'de', 0x42616e676c6164657368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BE', 'de', 0x42656c6769656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BF', 'de', 0x4275726b696e61204661736f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BG', 'de', 0x42756c67617269656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BH', 'de', 0x4261687261696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BI', 'de', 0x427572756e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BJ', 'de', 0x42656e696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BM', 'de', 0x4265726d75646173);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BN', 'de', 0x4272756e6569);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BO', 'de', 0x426f6c697669656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BR', 'de', 0x42726173696c69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BS', 'de', 0x426168616d6173);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BT', 'de', 0x42687574616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BV', 'de', 0x426f757665742049736c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BW', 'de', 0x426f747377616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BY', 'de', 0x576569c39f7275c39f6c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BZ', 'de', 0x42656c697a65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CA', 'de', 0x4b616e616461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CC', 'de', 0x4b6f6b6f7320284b65656c696e672920496e73656c6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CF', 'de', 0x5a656e7472616c616672696b616e69736368652052657075626c696b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CG', 'de', 0x4b6f6e676f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CH', 'de', 0x5363687765697a);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CI', 'de', 0x456c66656e6265696e6bc3bc737465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CK', 'de', 0x436f6f6b20496e73656c6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CL', 'de', 0x4368696c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CM', 'de', 0x4b616d6572756e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CN', 'de', 0x4368696e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CO', 'de', 0x4b6f6c756d6269656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CR', 'de', 0x436f7374612052696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CU', 'de', 0x43756261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CV', 'de', 0x4b617076657264697363686520496e73656c6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CX', 'de', 0x576569686e6163687473696e73656c6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CY', 'de', 0x5a797065726e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CZ', 'de', 0x5473636865636869736368652052657075626c696b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DE', 'de', 0x446575747363686c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DJ', 'de', 0x446a69626f757469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DK', 'de', 0x44c3a46e656d61726b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DM', 'de', 0x446f6d696e696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DO', 'de', 0x446f6d696e696b616e69736368652052657075626c696b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DZ', 'de', 0x416c67657269656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EC', 'de', 0x456b7561646f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EE', 'de', 0x4573746c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EG', 'de', 0xc38467797074656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EH', 'de', 0x576573742d536168617261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ER', 'de', 0x45726974726561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ES', 'de', 0x5370616e69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ET', 'de', 0xc3847468696f7069656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FI', 'de', 0x46696e6e6c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FJ', 'de', 0x46696a69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FK', 'de', 0x46616c6b6c616e6420496e73656c6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FM', 'de', 0x4d696b726f6e657369656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FO', 'de', 0x4661726f6572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FR', 'de', 0x4672616e6b7265696368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FX', 'de', 0x4672616e6b7265696368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GA', 'de', 0x4761626f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GB', 'de', 0x56657265696e6967746573204bc3b66e69677265696368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GD', 'de', 0x4772656e616461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GE', 'de', 0x47656f726769656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GF', 'de', 0x4672616e7ac3b67369636820477579616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GH', 'de', 0x4768616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GI', 'de', 0x47696272616c746172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GL', 'de', 0x4772c3b66e6c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GM', 'de', 0x47616d626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GN', 'de', 0x4775696e6561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GP', 'de', 0x47756164656c6f757065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GQ', 'de', 0xc384717561746f7269616c2d4775696e6561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GR', 'de', 0x477269656368656e6c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GS', 'de', 0x536f7574682047656f7267696120416e642054686520536f7574682053616e64776963682049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GT', 'de', 0x47756174656d616c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GU', 'de', 0x4775616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GW', 'de', 0x4775696e65612d426973736175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GY', 'de', 0x477579616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HM', 'de', 0x486561726420416e64204e6320446f6e616c642049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HN', 'de', 0x486f6e6475726173);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HR', 'de', 0x4b726f617469656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HT', 'de', 0x4861697469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HU', 'de', 0x556e6761726e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ID', 'de', 0x496e646f6e657369656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IE', 'de', 0x49726c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IL', 'de', 0x49737261656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IN', 'de', 0x496e6469656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IO', 'de', 0x4272697469736820496e6469616e204f6365616e205465727269746f7279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IQ', 'de', 0x4972616b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IR', 'de', 0x4972616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IS', 'de', 0x4963656c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IT', 'de', 0x4974616c69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JM', 'de', 0x4a616d61696b61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JO', 'de', 0x4a6f7264616e69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JP', 'de', 0x4a6170616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KE', 'de', 0x4b656e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KG', 'de', 0x4b697267697369656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KH', 'de', 0x4b616d626f6473636861);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KI', 'de', 0x4b69726962617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KM', 'de', 0x4b6f6d6f72656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KN', 'de', 0x5361696e74204b6974747320416e64204e65766973);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KP', 'de', 0x4e6f72642d4b6f7265612028566f6c6b7372657075626c696b204b6f72656129);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KR', 'de', 0x53c3bc642d4b6f726561202852657075626c696b29);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KW', 'de', 0x4b7577616974);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KY', 'de', 0x4b61696d616e20496e73656c6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KZ', 'de', 0x4b61736163687374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LA', 'de', 0x4c616f73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LB', 'de', 0x4c6962616e6f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LC', 'de', 0x5361696e74204c75636961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LI', 'de', 0x4c6965636874656e737465696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LK', 'de', 0x537269204c616e6b61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LR', 'de', 0x4c696265726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LS', 'de', 0x4c65736f74686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LT', 'de', 0x4c69746175656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LU', 'de', 0x4c7578656d62757267);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LV', 'de', 0x4c6574746c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LY', 'de', 0x4c696279656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MA', 'de', 0x4d6f726f6b6b6f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MC', 'de', 0x4d6f6e61636f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MD', 'de', 0x4d6f6c64617769656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MG', 'de', 0x4d6164616761736b6172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MH', 'de', 0x4d61727368616c6c20496e73656c6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MK', 'de', 0x4d617a65646f6e69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ML', 'de', 0x4d616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MM', 'de', 0x4d79616e6d6172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MN', 'de', 0x4d6f6e676f6c6569);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MO', 'de', 0x4d61636175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MP', 'de', 0x4e6f72746865726e204d617269616e612049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MQ', 'de', 0x4d617274696e69717565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MR', 'de', 0x4d6175726574616e69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MS', 'de', 0x4d6f6e74736572726174);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MT', 'de', 0x4d616c7461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MU', 'de', 0x4d6175726974697573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MV', 'de', 0x4d616c65646976656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MW', 'de', 0x4d616c617769);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MX', 'de', 0x4d6578696b6f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MY', 'de', 0x4d616c6179736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MZ', 'de', 0x4d6f7a616d62696b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NA', 'de', 0x4e616d69626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NC', 'de', 0x4e6575204b616c65646f6e69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NE', 'de', 0x4e69676572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NF', 'de', 0x4e6f72666f6c6b2049736c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NG', 'de', 0x4e696765726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NI', 'de', 0x4e6963617261677561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NL', 'de', 0x4e69656465726c616e6465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NO', 'de', 0x4e6f72776567656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NP', 'de', 0x4e6570616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NR', 'de', 0x4e61757275);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NU', 'de', 0x4e697565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NZ', 'de', 0x4e65757365656c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_OM', 'de', 0x4f6d616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PA', 'de', 0x50616e616d61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PE', 'de', 0x50657275);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PF', 'de', 0x4672616e7ac3b67369636820506f6c796e657369656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PG', 'de', 0x50617075612d4e65756775696e6561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PH', 'de', 0x5068696c697070696e656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PK', 'de', 0x50616b697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PL', 'de', 0x506f6c656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PM', 'de', 0x53742e2050696572726520416e64204d697175656c6f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PN', 'de', 0x506974636169726e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PR', 'de', 0x50756572746f205269636f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PT', 'de', 0x506f72747567616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PW', 'de', 0x50616c6175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PY', 'de', 0x5061726167756179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_QA', 'de', 0x4b61746172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RE', 'de', 0x5265756e696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RO', 'de', 0x52756dc3a46e69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RU', 'de', 0x52616e2046656465726174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RW', 'de', 0x5275616e6461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SA', 'de', 0x53617564692d4172616269656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SB', 'de', 0x536f6c6f6d6f6e20496e73656c6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SC', 'de', 0x5365796368656c6c656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SD', 'de', 0x537564616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SE', 'de', 0x536368776564656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SG', 'de', 0x53696e6761707572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SH', 'de', 0x53742e2048656c656e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SI', 'de', 0x536c6f77656e69656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SJ', 'de', 0x5376616c6261726420756e64204a616e204d6179656e20496e73656c6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SK', 'de', 0x536c6f77616b6569);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SL', 'de', 0x5369657261204c656f6e65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SM', 'de', 0x53616e204d6172696e6f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SN', 'de', 0x53656e6567616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SO', 'de', 0x536f6d616c6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SR', 'de', 0x537572696e616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ST', 'de', 0x53616f20546f6d6520756e64205072696e63697065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SV', 'de', 0x456c2053616c7661646f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SY', 'de', 0x53797269656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SZ', 'de', 0x5377617a696c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TC', 'de', 0x5475726b7320756e6420436169636f7320496e73656c6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TD', 'de', 0x547363686164);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TF', 'de', 0x4672616e7ac3b673696368652053c3bc646c69636865205465727269746f7269656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TG', 'de', 0x546f676f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TH', 'de', 0x546861696c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TJ', 'de', 0x546164736368696b697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TK', 'de', 0x546f6b656c6175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TM', 'de', 0x5475726b6d656e697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TN', 'de', 0x54756e657369656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TO', 'de', 0x546f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TP', 'de', 0x4f73742d54696d6f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TR', 'de', 0x5475726b6569);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TT', 'de', 0x5472696e6964616420756e6420546f6261676f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TV', 'de', 0x547576616c75);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TW', 'de', 0x54616977616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TZ', 'de', 0x54616e73616e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UA', 'de', 0x556b7261696e65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UG', 'de', 0x5567616e6461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UM', 'de', 0x552e532e204d696e6f72204f75746c79696e672049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_US', 'de', 0x56657265696e69677465205374616174656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UY', 'de', 0x55727567756179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UZ', 'de', 0x557362656b697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VA', 'de', 0x566174696b616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VC', 'de', 0x5361696e742056696e63656e7420756e64204772656e6164696e6573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VE', 'de', 0x56656e657a75656c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VG', 'de', 0x56697267696e2049736c616e64732028427269746973636829);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VI', 'de', 0x56697267696e2049736c616e64732028555329);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VN', 'de', 0x566965746e616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VU', 'de', 0x56616e75617475);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_WF', 'de', 0x57616c6c697320756e6420467574756e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_WS', 'de', 0x53616d6f61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_YE', 'de', 0x4a656d656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_YT', 'de', 0x4d61796f747465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZA', 'de', 0x53c3bc64616672696b61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZM', 'de', 0x53616d626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZR', 'de', 0x5a61697265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZW', 'de', 0x53696d6261627765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZZ', 'de', 0x416e64657265204cc3a46e646572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_general', 'de', 0x47656e6572616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_identifier', 'de', 0x4964656e746966696572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_aa', 'de', 0x61666172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ab', 'de', 0x41626368617369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_af', 'de', 0x416672696b61616e73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_am', 'de', 0x416d6861726963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ar', 'de', 0x4172616269736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_as', 'de', 0x617373616d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ay', 'de', 0x61796d617261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_az', 'de', 0x4173657262616964736368616e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ba', 'de', 0x626173686b6972);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_be', 'de', 0x576569c39f7275737369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bg', 'de', 0x42756c67617269736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bh', 'de', 0x626968617269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bi', 'de', 0x6269736c616d61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bn', 'de', 0x42656e67616c69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bo', 'de', 0x5469626574616e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_br', 'de', 0x427265746f6e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ca', 'de', 0x4b6174616c616e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_co', 'de', 0x4b6f727369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_cs', 'de', 0x5473636865636869736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_cy', 'de', 0x57616c697369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_da', 'de', 0x44c3a46e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_de', 'de', 0x44657574736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_dz', 'de', 0x62687574616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_el', 'de', 0x47726965636869736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_en', 'de', 0x456e676c69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_eo', 'de', 0x4573706572616e746f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_es', 'de', 0x5370616e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_et', 'de', 0x45737469736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_eu', 'de', 0x4261736b69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fa', 'de', 0x5065727369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fi', 'de', 0x46696e6e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fj', 'de', 0x66696a69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fo', 'de', 0x6661726f657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fr', 'de', 0x4672616e7ac3b67369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fy', 'de', 0x467269657369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ga', 'de', 0x497269736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gd', 'de', 0x47c3a46c69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gl', 'de', 0x47616c697a69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gn', 'de', 0x67756172616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gu', 'de', 0x67756a6172617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ha', 'de', 0x4861757361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_he', 'de', 0x49737261656c69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hi', 'de', 0x48696e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hr', 'de', 0x4b726f617469736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hu', 'de', 0x556e67617269736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hy', 'de', 0x41726d656e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ia', 'de', 0x696e7465726c696e677561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_id', 'de', 0x496e646f6e657369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ie', 'de', 0x696e7465726c696e677565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ik', 'de', 0x696e757069616b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_is', 'de', 0x6963656c616e646963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_it', 'de', 0x4974616c69656e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_iu', 'de', 0x696e756b7469747574);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ja', 'de', 0x4a6170616e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_jv', 'de', 0x4a6176616e657369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ka', 'de', 0x47656f726769736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kk', 'de', 0x6b617a616b68);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kl', 'de', 0x677265656e6c616e646963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_km', 'de', 0x63616d626f6469616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kn', 'de', 0x6b616e6e616461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ko', 'de', 0x4b6f7265616e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ks', 'de', 0x6b6173686d697269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ku', 'de', 0x4b75726469736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ky', 'de', 0x4b697267697369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_la', 'de', 0x4c617465696e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ln', 'de', 0x6c696e67616c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lo', 'de', 0x6c616f746869616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lt', 'de', 0x4c6974617569736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lv', 'de', 0x4c65747469736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mg', 'de', 0x6d616c6167617379);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mi', 'de', 0x6d616f7269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mk', 'de', 0x6d616365646f6e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ml', 'de', 0x6d616c6179616c616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mn', 'de', 0x6d6f6e676f6c69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mo', 'de', 0x6d6f6c64617669616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mr', 'de', 0x6d617261746869);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ms', 'de', 0x4d616c617969736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mt', 'de', 0x6d616c74657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_my', 'de', 0x6275726d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_na', 'de', 0x6e61757275);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ne', 'de', 0x6e6570616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_nl', 'de', 0x4e69656465726cc3a46e6469736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_no', 'de', 0x4e6f7277656769736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_oc', 'de', 0x6f63636974616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_om', 'de', 0x6166616e20286f726f6d6f29);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_or', 'de', 0x6f72697961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pa', 'de', 0x70756e6a616269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pl', 'de', 0x506f6c6e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ps', 'de', 0x70617368746f3b70757368746f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pt', 'de', 0x506f72747567697369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_qu', 'de', 0x71756563687561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rm', 'de', 0x52c3a4746f726f6d616e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rn', 'de', 0x6b7572756e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ro', 'de', 0x52756dc3a46e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ru', 'de', 0x5275737369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rw', 'de', 0x6b696e79617277616e6461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sa', 'de', 0x53616e736b726974);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sd', 'de', 0x73696e646869);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sg', 'de', 0x73616e67686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sh', 'de', 0x536572626f2d4b726f617469736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_si', 'de', 0x73696e6768616c657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sk', 'de', 0x536c6f77616b69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sl', 'de', 0x536c6f77656e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sm', 'de', 0x73616d6f616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sn', 'de', 0x73686f6e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_so', 'de', 0x736f6d616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sq', 'de', 0x416c62616e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sr', 'de', 0x5365726269736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ss', 'de', 0x73697377617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_st', 'de', 0x7365736f74686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_su', 'de', 0x73756e64616e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sv', 'de', 0x53636877656469736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sw', 'de', 0x53776168696c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ta', 'de', 0x54616d696c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_te', 'de', 0x74656c756775);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tg', 'de', 0x74616a696b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_th', 'de', 0x54686169);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ti', 'de', 0x74696772696e7961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tk', 'de', 0x5475726b6d656e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tl', 'de', 0x746167616c6f67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tn', 'de', 0x7365747377616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_to', 'de', 0x746f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tr', 'de', 0x54c3bc726b69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ts', 'de', 0x74736f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tt', 'de', 0x7461746172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tw', 'de', 0x747769);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ug', 'de', 0x7569677572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_uk', 'de', 0x556b7261696e69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ur', 'de', 0x75726475);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_uz', 'de', 0x557362656b69736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_vi', 'de', 0x566965746e616d657369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_vo', 'de', 0x766f6c6170756b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_wo', 'de', 0x776f6c6f66);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_xh', 'de', 0x58686f7361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_yi', 'de', 0x4a696464697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_yo', 'de', 0x796f72756261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_za', 'de', 0x7a6875616e67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_zh', 'de', 0x4368696e657369736368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_zu', 'de', 0x5a756c75);
INSERT INTO `lng_data` VALUES ('meta', 'meta_language', 'de', 0x53707261636865);
INSERT INTO `lng_data` VALUES ('meta', 'meta_title', 'de', 0x546974656c);
INSERT INTO `lng_data` VALUES ('mail', 'forward', 'en', 0x466f7277617264);
INSERT INTO `lng_data` VALUES ('mail', 'linebreak', 'en', 0x4c696e65627265616b);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_recipient', 'en', 0x506c6561736520656e746572206120726563697069656e74);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_subfolder', 'en', 0x41646420537562666f6c646572);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_subject', 'en', 0x506c6561736520656e7465722061207375626a656374);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_to_addressbook', 'en', 0x41646420746f2061646472657373626f6f6b);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_type', 'en', 0x506c6561736520616464207468652074797065206f66206d61696c20796f752077616e7420746f2073656e64);
INSERT INTO `lng_data` VALUES ('mail', 'mail_addr_entries', 'en', 0x456e7472696573);
INSERT INTO `lng_data` VALUES ('mail', 'mail_attachments', 'en', 0x4174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('mail', 'mail_bc_not_valid', 'en', 0x54686520426320726563697069656e74206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_bc_search', 'en', 0x424320736561726368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_cc_not_valid', 'en', 0x54686520436320726563697069656e74206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_cc_search', 'en', 0x434320736561726368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_change_to_folder', 'en', 0x53776974636820746f20666f6c6465723a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_check_your_email_addr', 'en', 0x596f757220656d61696c2061646472657373206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_compose', 'en', 0x436f6d706f7365204d657373616765);
INSERT INTO `lng_data` VALUES ('mail', 'mail_deleted', 'en', 0x546865206d61696c2873292068617665206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_deleted_entry', 'en', 0x54686520656e7472696573206172652064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_email_forbidden', 'en', 0x596f7520617265206e6f7420616c6c6f77656420746f2073656e6420656d61696c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_entry_added', 'en', 0x4164646564206e657720456e747279);
INSERT INTO `lng_data` VALUES ('mail', 'mail_entry_changed', 'en', 0x54686520656e747279206973206368616e676564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_file_name', 'en', 0x46696c656e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_file_size', 'en', 0x46696c6573697a65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_files_deleted', 'en', 0x5468652066696c65287329206172652064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_created', 'en', 0x41206e657720666f6c6465722069732063726561746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_deleted', 'en', 0x54686520666f6c64657220686173206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_exists', 'en', 0x4120666f6c64657220616c72656164792065786973747320776974682074686973206e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_name', 'en', 0x466f6c646572206e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_name_changed', 'en', 0x54686520666f6c6465722069732072656e616d6564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_options', 'en', 0x466f6c646572204f7074696f6e73);
INSERT INTO `lng_data` VALUES ('mail', 'mail_following_rcp_not_valid', 'en', 0x54686520666f6c6c6f77696e6720726563697069656e747320617265206e6f742076616c69643a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_global_options', 'en', 0x476c6f62616c206f7074696f6e73);
INSERT INTO `lng_data` VALUES ('mail', 'mail_insert_folder_name', 'en', 0x506c6561736520696e73657274206120666f6c646572206e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_insert_query', 'en', 0x506c6561736520696e736572742061207175657279);
INSERT INTO `lng_data` VALUES ('mail', 'mail_intern', 'en', 0x496e7465726e616c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_mark_read', 'en', 0x4d61726b206d61696c732072656164);
INSERT INTO `lng_data` VALUES ('mail', 'mail_mark_unread', 'en', 0x4d61726b206d61696c7320756e72656164);
INSERT INTO `lng_data` VALUES ('mail', 'mail_message_send', 'en', 0x546865206d6573736167652069732073656e64);
INSERT INTO `lng_data` VALUES ('mail', 'mail_move_error', 'en', 0x4572726f72206d6f76696e67206d61696c287329);
INSERT INTO `lng_data` VALUES ('mail', 'mail_move_to', 'en', 0x4d6f766520746f3a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_moved', 'en', 0x546865206d61696c2873292068617665206265656e206d6f766564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_moved_to_trash', 'en', 0x4d61696c2873292068617665206265656e206d6f76656420746f207472617368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_new_file', 'en', 0x416464206e65772066696c653a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_attach_allowed', 'en', 0x53797374656d206d6573736167657320617265206e6f7420616c6c6f77656420746f20636f6e7461696e206174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_attachments_found', 'en', 0x4e6f206174746163686d656e747320666f756e64);
INSERT INTO `lng_data` VALUES ('mail', 'mail_options_of', 'en', 0x4f7074696f6e73);
INSERT INTO `lng_data` VALUES ('mail', 'mail_recipient_not_valid', 'en', 0x54686520726563697069656e74206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_s', 'en', 0x4d61696c287329);
INSERT INTO `lng_data` VALUES ('mail', 'mail_s_unread', 'en', 0x556e72656164204d61696c287329);
INSERT INTO `lng_data` VALUES ('mail', 'mail_saved', 'en', 0x546865206d657373616765206973207361766564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_search_addressbook', 'en', 0x73656172636820696e2061646472657373626f6f6b);
INSERT INTO `lng_data` VALUES ('mail', 'mail_search_system', 'en', 0x73656172636820696e2073797374656d);
INSERT INTO `lng_data` VALUES ('mail', 'mail_select_one_entry', 'en', 0x596f75206861766520746f2073656c656374206f6e6520656e747279);
INSERT INTO `lng_data` VALUES ('mail', 'mail_select_one_file', 'en', 0x596f75206861766520746f2073656c656374206f6e652066696c65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete', 'en', 0x41726520796f7520737572652c20746f2064656c65746520746865206d61726b6564206d61696c287329);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_entry', 'en', 0x417265207375726520796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e6720656e7472696573);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_file', 'en', 0x5468652066696c652077696c6c2062652072656d6f766564207065726d616e656e746c79);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_folder', 'en', 0x54686520666f6c64657220616e642069747320636f6e74656e742077696c6c2062652072656d6f766564207065726d616e656e746c79);
INSERT INTO `lng_data` VALUES ('mail', 'mail_to_search', 'en', 0x546f20736561726368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_user_addr_n_valid', 'en', 0x466f6c6c77696e672075736572732068617665206e6f2076616c696420656d61696c2061646472657373);
INSERT INTO `lng_data` VALUES ('mail', 'search_bc_recipient', 'en', 0x53656172636820424320526563697069656e74);
INSERT INTO `lng_data` VALUES ('mail', 'search_cc_recipient', 'en', 0x53656172636820434320526563697069656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AD', 'en', 0x416e646f727261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AE', 'en', 0x556e69746564204172616220456d697261746573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AF', 'en', 0x41666768616e697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AG', 'en', 0x416e746967756120416e642042617262756461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AI', 'en', 0x416e6775696c6c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AL', 'en', 0x416c62616e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AM', 'en', 0x41726d656e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AN', 'en', 0x4e65746865726c616e647320416e74696c6c6573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AO', 'en', 0x416e676f6c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AQ', 'en', 0x416e7461726374696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AR', 'en', 0x417267656e74696e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AS', 'en', 0x416d65726963616e2053616d6f61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AT', 'en', 0x41757374726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AU', 'en', 0x4175737472616c6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AW', 'en', 0x4172756261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AZ', 'en', 0x417a65726261696a616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BA', 'en', 0x426f736e696120416e64204865727a65676f77696e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BB', 'en', 0x4261726261646f73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BD', 'en', 0x42616e676c6164657368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BE', 'en', 0x42656c6769756d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BF', 'en', 0x4275726b696e61204661736f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BG', 'en', 0x42756c6761726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BH', 'en', 0x4261687261696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BI', 'en', 0x427572756e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BJ', 'en', 0x42656e696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BM', 'en', 0x4265726d756461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BN', 'en', 0x4272756e656920446172757373616c616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BO', 'en', 0x426f6c69766961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BR', 'en', 0x4272617a696c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BS', 'en', 0x426168616d6173);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BT', 'en', 0x42687574616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BV', 'en', 0x426f757665742049736c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BW', 'en', 0x426f747377616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BY', 'en', 0x42656c61727573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BZ', 'en', 0x42656c697a65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CA', 'en', 0x43616e616461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CC', 'en', 0x436f636f7320284b65656c696e67292049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CF', 'en', 0x43656e7472616c204166726963616e2052657075626c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CG', 'en', 0x436f6e676f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CH', 'en', 0x537769747a65726c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CI', 'en', 0x436f74652044272049766f697265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CK', 'en', 0x436f6f6b2049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CL', 'en', 0x4368696c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CM', 'en', 0x43616d65726f6f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CN', 'en', 0x4368696e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CO', 'en', 0x436f6c6f6d626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CR', 'en', 0x436f7374612052696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CU', 'en', 0x43756261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CV', 'en', 0x43617065205665726465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CX', 'en', 0x4368726973746d61732049736c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CY', 'en', 0x437970727573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CZ', 'en', 0x437a6563682052657075626c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DE', 'en', 0x4765726d616e79);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DJ', 'en', 0x446a69626f757469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DK', 'en', 0x44656e6d61726b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DM', 'en', 0x446f6d696e696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DO', 'en', 0x446f6d696e6963616e2052657075626c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DZ', 'en', 0x416c6765726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EC', 'en', 0x45637561646f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EE', 'en', 0x4573746f6e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EG', 'en', 0x4567797074);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EH', 'en', 0x5765737465726e20536168617261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ER', 'en', 0x45726974726561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ES', 'en', 0x537061696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ET', 'en', 0x457468696f706961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FI', 'en', 0x46696e6c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FJ', 'en', 0x46696a69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FK', 'en', 0x46616c6b6c616e642049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FM', 'en', 0x4d6963726f6e65736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FO', 'en', 0x4661726f652049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FR', 'en', 0x4672616e6365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FX', 'en', 0x4672616e63652c204d6574726f706f6c6974616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GA', 'en', 0x4761626f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GB', 'en', 0x556e69746564204b696e67646f6d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GD', 'en', 0x4772656e616461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GE', 'en', 0x47696f72676961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GF', 'en', 0x4672656e636820477569616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GH', 'en', 0x4768616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GI', 'en', 0x47696272616c746172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GL', 'en', 0x477265656e6c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GM', 'en', 0x47616d626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GN', 'en', 0x4775696e6561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GP', 'en', 0x47756164656c6f757065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GQ', 'en', 0x45717561746f7269616c204775696e6561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GR', 'en', 0x477265656365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GS', 'en', 0x536f7574682047656f7267696120416e642054686520536f7574682053616e64776963682049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GT', 'en', 0x47756174656d616c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GU', 'en', 0x4775616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GW', 'en', 0x4775696e65612d426973736175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GY', 'en', 0x477579616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HM', 'en', 0x486561726420416e64204e6320446f6e616c642049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HN', 'en', 0x486f6e6475726173);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HR', 'en', 0x43726f61746961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HT', 'en', 0x4861697469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HU', 'en', 0x48756e67617279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ID', 'en', 0x496e646f6e65736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IE', 'en', 0x4972656c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IL', 'en', 0x49737261656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IN', 'en', 0x496e646961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IO', 'en', 0x4272697469736820496e6469616e204f6365616e205465727269746f7279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IQ', 'en', 0x49726171);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IR', 'en', 0x4972616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IS', 'en', 0x4963656c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IT', 'en', 0x4974616c79);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JM', 'en', 0x4a616d61696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JO', 'en', 0x4a6f7264616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JP', 'en', 0x4a6170616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KE', 'en', 0x4b656e7961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KG', 'en', 0x4b797267797a7374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KH', 'en', 0x43616d626f646961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KI', 'en', 0x4b69726962617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KM', 'en', 0x436f6d6f726f73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KN', 'en', 0x5361696e74204b6974747320416e64204e65766973);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KP', 'en', 0x4e6f727468204b6f726561202850656f706c6527732052657075626c6963204f66204b6f72656129);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KR', 'en', 0x536f757468204b6f726561202852657075626c6963204f66204b6f72656129);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KW', 'en', 0x4b7577616974);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KY', 'en', 0x4361796d616e2049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KZ', 'en', 0x4b617a616b687374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LA', 'en', 0x4c616f2050656f706c6527732052657075626c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LB', 'en', 0x4c6562616e6f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LC', 'en', 0x5361696e74204c75636961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LI', 'en', 0x4c6965636874656e737465696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LK', 'en', 0x537269204c616e6b61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LR', 'en', 0x4c696265726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LS', 'en', 0x4c65736f74686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LT', 'en', 0x4c697468756e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LU', 'en', 0x4c7578656d626f757267);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LV', 'en', 0x4c6174766961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LY', 'en', 0x4c696279616e2041726162204a616d61686972697961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MA', 'en', 0x4d6f726f63636f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MC', 'en', 0x4d6f6e61636f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MD', 'en', 0x4d6f6c646f7661);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MG', 'en', 0x4d616461676173636172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MH', 'en', 0x4d61727368616c6c2049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MK', 'en', 0x4d616365646f6e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ML', 'en', 0x4d616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MM', 'en', 0x4d79616e6d6172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MN', 'en', 0x4d6f6e676f6c6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MO', 'en', 0x4d61636175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MP', 'en', 0x4e6f72746865726e204d617269616e612049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MQ', 'en', 0x4d617274696e69717565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MR', 'en', 0x4d6175726974616e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MS', 'en', 0x4d6f6e74736572726174);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MT', 'en', 0x4d616c7461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MU', 'en', 0x4d6175726974697573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MV', 'en', 0x4d616c6469766573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MW', 'en', 0x4d616c617769);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MX', 'en', 0x4d657869636f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MY', 'en', 0x4d616c6179736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MZ', 'en', 0x4d6f7a616d6269717565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NA', 'en', 0x4e616d69626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NC', 'en', 0x4e65772043616c65646f6e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NE', 'en', 0x4e69676572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NF', 'en', 0x4e6f72666f6c6b2049736c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NG', 'en', 0x4e696765726961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NI', 'en', 0x4e6963617261677561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NL', 'en', 0x4e65746865726c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NO', 'en', 0x4e6f72776179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NP', 'en', 0x4e6570616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NR', 'en', 0x4e61757275);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NU', 'en', 0x4e697565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NZ', 'en', 0x4e6577205a65616c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_OM', 'en', 0x4f6d616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PA', 'en', 0x50616e616d61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PE', 'en', 0x50657275);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PF', 'en', 0x4672656e636820506f6c796e65736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PG', 'en', 0x5061707561204e6577204775696e6561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PH', 'en', 0x5068696c697070696e6573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PK', 'en', 0x50616b697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PL', 'en', 0x506f6c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PM', 'en', 0x53742e2050696572726520416e64204d697175656c6f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PN', 'en', 0x506974636169726e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PR', 'en', 0x50756572746f205269636f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PT', 'en', 0x506f72747567616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PW', 'en', 0x50616c6175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PY', 'en', 0x5061726167756179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_QA', 'en', 0x5161746172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RE', 'en', 0x5265756e696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RO', 'en', 0x526f6d616e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RU', 'en', 0x52616e2046656465726174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RW', 'en', 0x5277616e6461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SA', 'en', 0x536175646920417261626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SB', 'en', 0x536f6c6f6d6f6e2049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SC', 'en', 0x5365796368656c6c6573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SD', 'en', 0x537564616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SE', 'en', 0x53776564656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SG', 'en', 0x53696e6761706f7265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SH', 'en', 0x53742e2048656c656e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SI', 'en', 0x536c6f76656e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SJ', 'en', 0x5376616c6261726420416e64204a616e204d6179656e2049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SK', 'en', 0x536c6f76616b6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SL', 'en', 0x53696561727261204c656f6e65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SM', 'en', 0x53616e204d6172696e6f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SN', 'en', 0x53656e6567616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SO', 'en', 0x536f6d616c6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SR', 'en', 0x537572696e616d65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ST', 'en', 0x53616f20546f6d6520416e64205072696e63697065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SV', 'en', 0x456c2053616c7661646f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SY', 'en', 0x53797269616e20417261622052657075626c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SZ', 'en', 0x5377617a696c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TC', 'en', 0x5475726b7320416e6420436169636f732049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TD', 'en', 0x43686164);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TF', 'en', 0x4672656e636820536f75746865726e205465727269746f72696573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TG', 'en', 0x546f676f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TH', 'en', 0x546861696c616e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TJ', 'en', 0x54616a696b697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TK', 'en', 0x546f6b656c6175);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TM', 'en', 0x5475726b6d656e697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TN', 'en', 0x54756e69736961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TO', 'en', 0x546f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TP', 'en', 0x456173742054696d6f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TR', 'en', 0x5475726b6579);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TT', 'en', 0x5472696e6964616420416e6420546f6261676f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TV', 'en', 0x547576616c75);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TW', 'en', 0x54616977616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TZ', 'en', 0x54616e7a616e6961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UA', 'en', 0x556b7261696e65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UG', 'en', 0x5567616e6461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UM', 'en', 0x552e532e204d696e6f72204f75746c79696e672049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_US', 'en', 0x552e532e41);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UY', 'en', 0x55727567756179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UZ', 'en', 0x557a62656b697374616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VA', 'en', 0x5661746963616e2043697479205374617465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VC', 'en', 0x5361696e742056696e63656e7420416e6420546865204772656e6164696e6573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VE', 'en', 0x56656e657a75656c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VG', 'en', 0x56697267696e2049736c616e647320284272697469736829);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VI', 'en', 0x56697267696e2049736c616e64732028555329);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VN', 'en', 0x56696574204e616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VU', 'en', 0x56616e75617475);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_WF', 'en', 0x57616c6c697320416e6420467574756e612049736c616e6473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_WS', 'en', 0x53616d6f61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_YE', 'en', 0x59656d656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_YT', 'en', 0x4d61796f747465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZA', 'en', 0x536f75746820416672696361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZM', 'en', 0x5a616d626961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZR', 'en', 0x5a61697265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZW', 'en', 0x5a696d6261627765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZZ', 'en', 0x4f7468657220436f756e747279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_general', 'en', 0x47656e6572616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_identifier', 'en', 0x4964656e746966696572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_aa', 'en', 0x61666172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ab', 'en', 0x61626b68617a69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_af', 'en', 0x616672696b61616e73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_am', 'en', 0x616d6861726963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ar', 'en', 0x617261626963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_as', 'en', 0x617373616d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ay', 'en', 0x61796d617261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_az', 'en', 0x617a65726261696a616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ba', 'en', 0x626173686b6972);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_be', 'en', 0x6279656c6f7275737369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bg', 'en', 0x62756c67617269616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bh', 'en', 0x626968617269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bi', 'en', 0x6269736c616d61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bn', 'en', 0x62656e67616c693b62616e676c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bo', 'en', 0x7469626574616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_br', 'en', 0x627265746f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ca', 'en', 0x636174616c616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_co', 'en', 0x636f72736963616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_cs', 'en', 0x637a656368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_cy', 'en', 0x77656c7368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_da', 'en', 0x64616e697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_de', 'en', 0x6765726d616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_dz', 'en', 0x62687574616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_el', 'en', 0x677265656b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_en', 'en', 0x656e676c697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_eo', 'en', 0x6573706572616e746f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_es', 'en', 0x7370616e697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_et', 'en', 0x6573746f6e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_eu', 'en', 0x626173717565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fa', 'en', 0x7065727369616e2028666172736929);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fi', 'en', 0x66696e6e697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fj', 'en', 0x66696a69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fo', 'en', 0x6661726f657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fr', 'en', 0x6672656e6368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fy', 'en', 0x6672697369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ga', 'en', 0x6972697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gd', 'en', 0x73636f7473206761656c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gl', 'en', 0x67616c696369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gn', 'en', 0x67756172616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gu', 'en', 0x67756a6172617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ha', 'en', 0x6861757361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_he', 'en', 0x686562726577);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hi', 'en', 0x68696e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hr', 'en', 0x63726f617469616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hu', 'en', 0x68756e67617269616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hy', 'en', 0x61726d656e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ia', 'en', 0x696e7465726c696e677561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_id', 'en', 0x696e646f6e657369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ie', 'en', 0x696e7465726c696e677565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ik', 'en', 0x696e757069616b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_is', 'en', 0x6963656c616e646963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_it', 'en', 0x6974616c69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_iu', 'en', 0x696e756b7469747574);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ja', 'en', 0x6a6170616e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_jv', 'en', 0x6a6176616e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ka', 'en', 0x67656f726769616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kk', 'en', 0x6b617a616b68);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kl', 'en', 0x677265656e6c616e646963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_km', 'en', 0x63616d626f6469616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kn', 'en', 0x6b616e6e616461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ko', 'en', 0x6b6f7265616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ks', 'en', 0x6b6173686d697269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ku', 'en', 0x6b757264697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ky', 'en', 0x6b69726768697a);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_la', 'en', 0x6c6174696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ln', 'en', 0x6c696e67616c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lo', 'en', 0x6c616f746869616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lt', 'en', 0x6c69746875616e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lv', 'en', 0x6c61747669616e3b6c657474697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mg', 'en', 0x6d616c6167617379);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mi', 'en', 0x6d616f7269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mk', 'en', 0x6d616365646f6e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ml', 'en', 0x6d616c6179616c616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mn', 'en', 0x6d6f6e676f6c69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mo', 'en', 0x6d6f6c64617669616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mr', 'en', 0x6d617261746869);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ms', 'en', 0x6d616c6179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mt', 'en', 0x6d616c74657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_my', 'en', 0x6275726d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_na', 'en', 0x6e61757275);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ne', 'en', 0x6e6570616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_nl', 'en', 0x6475746368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_no', 'en', 0x6e6f7277656769616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_oc', 'en', 0x6f63636974616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_om', 'en', 0x6166616e20286f726f6d6f29);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_or', 'en', 0x6f72697961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pa', 'en', 0x70756e6a616269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pl', 'en', 0x706f6c697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ps', 'en', 0x70617368746f3b70757368746f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pt', 'en', 0x706f7274756775657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_qu', 'en', 0x71756563687561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rm', 'en', 0x72686165746f2d726f6d616e6365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rn', 'en', 0x6b7572756e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ro', 'en', 0x726f6d616e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ru', 'en', 0x7275737369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rw', 'en', 0x6b696e79617277616e6461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sa', 'en', 0x73616e736b726974);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sd', 'en', 0x73696e646869);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sg', 'en', 0x73616e67686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sh', 'en', 0x736572626f2d63726f617469616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_si', 'en', 0x73696e6768616c657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sk', 'en', 0x736c6f76616b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sl', 'en', 0x736c6f76656e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sm', 'en', 0x73616d6f616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sn', 'en', 0x73686f6e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_so', 'en', 0x736f6d616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sq', 'en', 0x616c62616e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sr', 'en', 0x7365726269616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ss', 'en', 0x73697377617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_st', 'en', 0x7365736f74686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_su', 'en', 0x73756e64616e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sv', 'en', 0x73776564697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sw', 'en', 0x73776168696c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ta', 'en', 0x74616d696c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_te', 'en', 0x74656c756775);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tg', 'en', 0x74616a696b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_th', 'en', 0x74686169);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ti', 'en', 0x74696772696e7961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tk', 'en', 0x7475726b6d656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tl', 'en', 0x746167616c6f67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tn', 'en', 0x7365747377616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_to', 'en', 0x746f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tr', 'en', 0x7475726b697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ts', 'en', 0x74736f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tt', 'en', 0x7461746172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tw', 'en', 0x747769);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ug', 'en', 0x7569677572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_uk', 'en', 0x756b7261696e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ur', 'en', 0x75726475);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_uz', 'en', 0x757a62656b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_vi', 'en', 0x766965746e616d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_vo', 'en', 0x766f6c6170756b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_wo', 'en', 0x776f6c6f66);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_xh', 'en', 0x78686f7361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_yi', 'en', 0x79696464697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_yo', 'en', 0x796f72756261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_za', 'en', 0x7a6875616e67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_zh', 'en', 0x6368696e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_zu', 'en', 0x7a756c75);
INSERT INTO `lng_data` VALUES ('meta', 'meta_language', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_title', 'en', 0x5469746c65);
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_attribute_idx`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_attribute_name`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_attribute_name` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `attribute` char(32) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`),
  UNIQUE KEY `attribute` (`attribute`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_attribute_name`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_attribute_namespace`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_attribute_namespace` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `namespace` char(64) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_attribute_namespace`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_attribute_value`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_attribute_value` (
  `value_id` smallint(5) unsigned NOT NULL auto_increment,
  `value` char(32) NOT NULL default '0',
  PRIMARY KEY  (`value_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_attribute_value`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_cdata`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_cdata` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `cdata` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_cdata`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_comment`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_comment` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `comment` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_comment`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_element_idx`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_element_idx` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `element_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `lo_element_idx`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_element_name`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_element_name` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `element` char(32) NOT NULL default '',
  PRIMARY KEY  (`element_id`),
  UNIQUE KEY `element` (`element`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_element_name`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_element_namespace`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_element_namespace` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `namespace` char(64) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_element_namespace`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_entity_reference`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_entity_reference` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `entity_reference` char(128) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_entity_reference`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_node_type`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_node_type` (
  `node_type_id` int(11) NOT NULL auto_increment,
  `description` varchar(50) default NULL,
  `lft_delimiter` varchar(10) default NULL,
  `rgt_delimiter` varchar(10) default NULL,
  PRIMARY KEY  (`node_type_id`)
) TYPE=MyISAM AUTO_INCREMENT=11 ;

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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_pi_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_pi_data` (
  `leaf_id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `leaf_text` text NOT NULL,
  PRIMARY KEY  (`leaf_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_pi_data`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_pi_target`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `lo_pi_target` (
  `leaf_id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `leaf_text` text NOT NULL,
  PRIMARY KEY  (`leaf_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_pi_target`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_text`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lo_tree`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `lo_tree`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `mail`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `mail`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `mail_attachment`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `mail_attachment` (
  `mail_id` int(11) NOT NULL default '0',
  `path` text NOT NULL,
  PRIMARY KEY  (`mail_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `mail_attachment`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `mail_obj_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `mail_obj_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` char(70) NOT NULL default '',
  `type` char(16) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`user_id`)
) TYPE=MyISAM AUTO_INCREMENT=38 ;

#
# Daten für Tabelle `mail_obj_data`
#

INSERT INTO `mail_obj_data` VALUES (2, 6, 'a_root', 'root');
INSERT INTO `mail_obj_data` VALUES (3, 6, 'b_inbox', 'inbox');
INSERT INTO `mail_obj_data` VALUES (4, 6, 'c_trash', 'trash');
INSERT INTO `mail_obj_data` VALUES (5, 6, 'd_drafts', 'drafts');
INSERT INTO `mail_obj_data` VALUES (6, 6, 'e_sent', 'sent');
INSERT INTO `mail_obj_data` VALUES (7, 6, 'z_local', 'local');
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `mail_options`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `mail_saved`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `mail_tree`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `media_item`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `media_item`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `meta_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `meta_keyword`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `meta_techn_format`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `meta_techn_loc`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `meta_technical`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `meta_technical`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `mob_parameter`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `note_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `object_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `object_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `type` char(4) NOT NULL default 'none',
  `title` char(70) NOT NULL default '',
  `description` char(128) default NULL,
  `owner` int(11) NOT NULL default '0',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`obj_id`),
  KEY `type` (`type`)
) TYPE=MyISAM AUTO_INCREMENT=197 ;

#
# Daten für Tabelle `object_data`
#

INSERT INTO `object_data` VALUES (2, 'role', 'Administrator', 'Role for systemadministrators. This role grants access to everything!', -1, '2002-01-16 15:31:45', '2003-08-15 13:18:57');
INSERT INTO `object_data` VALUES (3, 'role', 'Author', 'Role for teachers with many write & some create permissions.', -1, '2002-01-16 15:32:50', '2003-08-15 13:19:22');
INSERT INTO `object_data` VALUES (4, 'role', 'Learner', 'Typical role for students. Grants write access to some objects.', -1, '2002-01-16 15:34:00', '2003-08-15 13:19:48');
INSERT INTO `object_data` VALUES (5, 'role', 'Guest', 'Role grants only a few visible & read permissions.', -1, '2002-01-16 15:34:46', '2003-08-15 13:19:34');
INSERT INTO `object_data` VALUES (6, 'usr', 'root user', 'ilias@yourserver.com', -1, '2002-01-16 16:09:22', '2003-08-15 10:44:05');
INSERT INTO `object_data` VALUES (7, 'usrf', 'Users', 'Folder contains all users', -1, '2002-06-27 09:24:06', '2003-08-15 10:13:26');
INSERT INTO `object_data` VALUES (8, 'rolf', 'Roles', 'Folder contains all roles', -1, '2002-06-27 09:24:06', '2002-06-27 09:24:06');
INSERT INTO `object_data` VALUES (1, 'root', 'ILIAS open source', 'This is the root node of the system!!!', -1, '2002-06-24 15:15:03', '2002-06-24 15:15:03');
INSERT INTO `object_data` VALUES (9, 'adm', 'System Settings', 'Folder contains the systems settings', -1, '2002-07-15 12:37:33', '2002-07-15 12:37:33');
INSERT INTO `object_data` VALUES (10, 'objf', 'Objects', 'Folder contains list of known object types', -1, '2002-07-15 12:36:56', '2003-08-15 11:52:05');
INSERT INTO `object_data` VALUES (11, 'lngf', 'Languages', 'Folder contains all available languages', -1, '2002-07-15 15:52:51', '2002-07-15 15:52:51');
INSERT INTO `object_data` VALUES (25, 'typ', 'usr', 'User object', -1, '2002-07-15 15:53:37', '2003-08-15 12:30:56');
INSERT INTO `object_data` VALUES (34, 'typ', 'lm', 'Learning module Object', -1, '2002-07-15 15:54:04', '2003-08-15 12:33:04');
INSERT INTO `object_data` VALUES (37, 'typ', 'frm', 'Forum object', -1, '2002-07-15 15:54:22', '2003-08-15 12:36:40');
INSERT INTO `object_data` VALUES (15, 'typ', 'grp', 'Group object', -1, '2002-07-15 15:54:37', '2002-07-15 15:54:37');
INSERT INTO `object_data` VALUES (16, 'typ', 'cat', 'Category object', -1, '2002-07-15 15:54:54', '2002-07-15 15:54:54');
INSERT INTO `object_data` VALUES (17, 'typ', 'crs', 'Course object', -1, '2002-07-15 15:55:08', '2002-07-15 15:55:08');
INSERT INTO `object_data` VALUES (19, 'typ', 'mail', 'Mailmodule object', -1, '2002-07-15 15:55:49', '2002-07-15 15:55:49');
INSERT INTO `object_data` VALUES (21, 'typ', 'adm', 'Administration Panel object', -1, '2002-07-15 15:56:38', '2002-07-15 15:56:38');
INSERT INTO `object_data` VALUES (22, 'typ', 'usrf', 'User Folder object', -1, '2002-07-15 15:56:52', '2002-07-15 15:56:52');
INSERT INTO `object_data` VALUES (23, 'typ', 'rolf', 'Role Folder object', -1, '2002-07-15 15:57:06', '2002-07-15 15:57:06');
INSERT INTO `object_data` VALUES (24, 'typ', 'objf', 'Object-Type Folder object', -1, '2002-07-15 15:57:17', '2002-07-15 15:57:17');
INSERT INTO `object_data` VALUES (26, 'typ', 'typ', 'Object Type Definition object', -1, '2002-07-15 15:58:16', '2002-07-15 15:58:16');
INSERT INTO `object_data` VALUES (27, 'typ', 'rolt', 'Role template object', -1, '2002-07-15 15:58:16', '2002-07-15 15:58:16');
INSERT INTO `object_data` VALUES (28, 'typ', 'lngf', 'Language Folder object', -1, '2002-08-28 14:22:01', '2002-08-28 14:22:01');
INSERT INTO `object_data` VALUES (29, 'typ', 'lng', 'Language object', -1, '2002-08-30 10:18:29', '2002-08-30 10:18:29');
INSERT INTO `object_data` VALUES (30, 'typ', 'role', 'Role Object', -1, '2002-08-30 10:21:37', '2002-08-30 10:21:37');
INSERT INTO `object_data` VALUES (31, 'typ', 'dbk', 'Digilib Book', -1, '2003-08-15 10:07:29', '2003-08-15 12:30:19');
INSERT INTO `object_data` VALUES (33, 'typ', 'root', 'Root Folder Object', -1, '2002-12-21 00:04:00', '2003-08-15 12:04:20');
INSERT INTO `object_data` VALUES (70, 'lng', 'en', 'installed', -1, '0000-00-00 00:00:00', '2003-08-15 13:16:36');
INSERT INTO `object_data` VALUES (14, 'role', 'Anonymous', 'Default role for anonymous users (with no account)', -1, '2003-08-15 12:06:19', '2003-08-15 13:19:11');
INSERT INTO `object_data` VALUES (18, 'typ', 'mob', 'Multimedia object', -1, '0000-00-00 00:00:00', '2003-08-15 12:03:20');
INSERT INTO `object_data` VALUES (35, 'typ', 'notf', 'Note Folder Object', -1, '2002-12-21 00:04:00', '2002-12-21 00:04:00');
INSERT INTO `object_data` VALUES (36, 'typ', 'note', 'Note Object', -1, '2002-12-21 00:04:00', '2002-12-21 00:04:00');
INSERT INTO `object_data` VALUES (12, 'mail', 'Mail Settings', 'Mail settings object', -1, '2003-08-15 10:07:28', '2003-08-15 10:07:28');
INSERT INTO `object_data` VALUES (20, 'typ', 'slm', 'SCORM Learning Module', -1, '2003-08-15 10:07:28', '2003-08-15 12:23:10');
INSERT INTO `object_data` VALUES (80, 'rolt', 'il_grp_admin', 'Administrator role template of groups', -1, '2003-08-15 10:07:28', '2003-08-15 12:11:40');
INSERT INTO `object_data` VALUES (81, 'rolt', 'il_grp_member', 'Member role template of groups', -1, '2003-08-15 10:07:28', '2003-08-15 12:12:24');
INSERT INTO `object_data` VALUES (82, 'rolt', 'il_grp_status_closed', 'Group role template', -1, '2003-08-15 10:07:29', '2003-08-15 13:21:38');
INSERT INTO `object_data` VALUES (83, 'rolt', 'il_grp_status_open', 'Group role template', -1, '2003-08-15 10:07:29', '2003-08-15 13:21:25');
INSERT INTO `object_data` VALUES (32, 'typ', 'glo', 'Glossary', -1, '2003-08-15 10:07:30', '2003-08-15 12:29:54');
INSERT INTO `object_data` VALUES (13, 'usr', 'Anonymous', 'Anonymous user account. DO NOT DELETE!', -1, '2003-08-15 10:07:30', '2003-08-15 10:07:30');
INSERT INTO `object_data` VALUES (71, 'lng', 'de', 'installed', 6, '2003-08-15 10:25:19', '2003-08-15 13:16:34');
INSERT INTO `object_data` VALUES (72, 'lng', 'es', 'not_installed', 6, '2003-08-15 10:25:19', '2003-08-15 10:25:19');
INSERT INTO `object_data` VALUES (73, 'lng', 'it', 'not_installed', 6, '2003-08-15 10:25:19', '2003-08-15 10:25:19');
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `object_reference`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `object_reference` (
  `ref_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ref_id`)
) TYPE=MyISAM AUTO_INCREMENT=184 ;

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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `rbac_fa`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `rbac_operations`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `rbac_operations` (
  `ops_id` int(11) NOT NULL auto_increment,
  `operation` char(32) NOT NULL default '',
  `description` char(255) default NULL,
  PRIMARY KEY  (`ops_id`),
  UNIQUE KEY `operation` (`operation`)
) TYPE=MyISAM AUTO_INCREMENT=13 ;

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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `rbac_pa`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
INSERT INTO `rbac_pa` VALUES (2, 'a:4:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 1);
INSERT INTO `rbac_pa` VALUES (3, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 7);
INSERT INTO `rbac_pa` VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 8);
INSERT INTO `rbac_pa` VALUES (2, 'a:4:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 11);
INSERT INTO `rbac_pa` VALUES (3, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 8);
INSERT INTO `rbac_pa` VALUES (2, 'a:5:{i:0;s:1:"5";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"2";i:4;s:1:"4";}', 9);
INSERT INTO `rbac_pa` VALUES (2, 'a:6:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"3";i:4;s:1:"2";i:5;s:1:"4";}', 8);
INSERT INTO `rbac_pa` VALUES (2, 'a:4:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 10);
INSERT INTO `rbac_pa` VALUES (2, 'a:5:{i:0;s:1:"6";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"2";i:4;s:1:"4";}', 7);
INSERT INTO `rbac_pa` VALUES (14, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1);
INSERT INTO `rbac_pa` VALUES (3, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 12);
INSERT INTO `rbac_pa` VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1);
INSERT INTO `rbac_pa` VALUES (3, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1);
INSERT INTO `rbac_pa` VALUES (5, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 12);
INSERT INTO `rbac_pa` VALUES (4, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 12);
INSERT INTO `rbac_pa` VALUES (2, 'a:6:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:2:"11";i:3;s:2:"12";i:4;s:1:"2";i:5;s:1:"4";}', 12);
INSERT INTO `rbac_pa` VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 7);
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `rbac_ta`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `rbac_templates`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
INSERT INTO `rbac_templates` VALUES (2, 'root', 4, 8);
INSERT INTO `rbac_templates` VALUES (4, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'root', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'objf', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'objf', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'objf', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'objf', 1, 8);
INSERT INTO `rbac_templates` VALUES (5, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'mail', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'mail', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'mail', 2, 8);
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
INSERT INTO `rbac_templates` VALUES (2, 'mail', 12, 8);
INSERT INTO `rbac_templates` VALUES (2, 'mail', 11, 8);
INSERT INTO `rbac_templates` VALUES (2, 'mail', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'mail', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lngf', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lngf', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lngf', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lngf', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'usrf', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'usrf', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'usrf', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'usrf', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'usrf', 6, 8);
INSERT INTO `rbac_templates` VALUES (2, 'usr', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'slm', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'slm', 8, 8);
INSERT INTO `rbac_templates` VALUES (2, 'slm', 7, 8);
INSERT INTO `rbac_templates` VALUES (2, 'slm', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'slm', 6, 8);
INSERT INTO `rbac_templates` VALUES (2, 'slm', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'rolt', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'rolf', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'rolf', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'rolf', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'rolf', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'rolf', 6, 8);
INSERT INTO `rbac_templates` VALUES (2, 'rolf', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'role', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'mob', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lm', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lm', 8, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lm', 7, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lm', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lm', 6, 8);
INSERT INTO `rbac_templates` VALUES (2, 'lm', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'grp', 4, 8);
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
INSERT INTO `rbac_templates` VALUES (2, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (2, 'grp', 7, 8);
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
INSERT INTO `rbac_templates` VALUES (2, 'grp', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'grp', 6, 8);
INSERT INTO `rbac_templates` VALUES (2, 'grp', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'glo', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'glo', 8, 8);
INSERT INTO `rbac_templates` VALUES (2, 'glo', 7, 8);
INSERT INTO `rbac_templates` VALUES (2, 'glo', 1, 8);
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
INSERT INTO `rbac_templates` VALUES (2, 'glo', 6, 8);
INSERT INTO `rbac_templates` VALUES (2, 'glo', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'frm', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'frm', 9, 8);
INSERT INTO `rbac_templates` VALUES (2, 'frm', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'frm', 10, 8);
INSERT INTO `rbac_templates` VALUES (2, 'frm', 6, 8);
INSERT INTO `rbac_templates` VALUES (2, 'frm', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'dbk', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'dbk', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'dbk', 1, 8);
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
INSERT INTO `rbac_templates` VALUES (2, 'dbk', 6, 8);
INSERT INTO `rbac_templates` VALUES (2, 'dbk', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'crs', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (2, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'crs', 8, 8);
INSERT INTO `rbac_templates` VALUES (2, 'crs', 7, 8);
INSERT INTO `rbac_templates` VALUES (2, 'crs', 1, 8);
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
INSERT INTO `rbac_templates` VALUES (2, 'crs', 6, 8);
INSERT INTO `rbac_templates` VALUES (2, 'crs', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'cat', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'cat', 2, 8);
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
INSERT INTO `rbac_templates` VALUES (2, 'cat', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'cat', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'cat', 6, 8);
INSERT INTO `rbac_templates` VALUES (2, 'cat', 5, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 5, 8);
INSERT INTO `rbac_templates` VALUES (14, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'adm', 4, 8);
INSERT INTO `rbac_templates` VALUES (2, 'adm', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'frm', 5, 8);
INSERT INTO `rbac_templates` VALUES (2, 'adm', 3, 8);
INSERT INTO `rbac_templates` VALUES (2, 'adm', 1, 8);
INSERT INTO `rbac_templates` VALUES (2, 'adm', 5, 8);
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `rbac_ua`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `role_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
INSERT INTO `role_data` VALUES (5, 0);
INSERT INTO `role_data` VALUES (14, 0);
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `sc_item`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `sc_manifest`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `sc_organization`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `sc_organizations`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `sc_organizations` (
  `obj_id` int(11) NOT NULL default '0',
  `default_organization` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `sc_organizations`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `sc_resource`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `sc_resource_dependency`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `sc_resource_dependency` (
  `id` int(11) NOT NULL auto_increment,
  `res_id` int(11) default NULL,
  `identifierref` varchar(200) default NULL,
  `nr` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `sc_resource_dependency`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `sc_resource_file`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `sc_resource_file` (
  `id` int(11) NOT NULL auto_increment,
  `res_id` int(11) default NULL,
  `href` text,
  `nr` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `sc_resource_file`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `sc_resources`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `sc_resources` (
  `obj_id` int(11) NOT NULL default '0',
  `xml_base` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Daten für Tabelle `sc_resources`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `scorm_object`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `scorm_object` (
  `obj_id` int(11) NOT NULL auto_increment,
  `title` varchar(200) default NULL,
  `type` char(3) default NULL,
  `slm_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `scorm_object`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `scorm_tracking`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `scorm_tree`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `settings`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `settings` (
  `keyword` char(50) NOT NULL default '',
  `value` char(50) NOT NULL default '',
  PRIMARY KEY  (`keyword`)
) TYPE=MyISAM;

#
# Daten für Tabelle `settings`
#

INSERT INTO `settings` VALUES ('admin_firstname', 'system');
INSERT INTO `settings` VALUES ('admin_lastname', 'root');
INSERT INTO `settings` VALUES ('admin_position', '');
INSERT INTO `settings` VALUES ('admin_title', '');
INSERT INTO `settings` VALUES ('babylon_path', '');
INSERT INTO `settings` VALUES ('admin_country', 'your country');
INSERT INTO `settings` VALUES ('convert_path', '');
INSERT INTO `settings` VALUES ('admin_city', 'your city');
INSERT INTO `settings` VALUES ('crs_enable', '');
INSERT INTO `settings` VALUES ('db_version', '0');
INSERT INTO `settings` VALUES ('admin_zipcode', '00000');
INSERT INTO `settings` VALUES ('admin_street', 'your street');
INSERT INTO `settings` VALUES ('admin_institution', '');
INSERT INTO `settings` VALUES ('group_file_sharing', '');
INSERT INTO `settings` VALUES ('ilias_version', '3.0a');
INSERT INTO `settings` VALUES ('admin_phone', 'your phone');
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
INSERT INTO `settings` VALUES ('admin_email', 'your@email.info');
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `style_parameter`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `style_parameter` (
  `id` int(11) NOT NULL auto_increment,
  `style_id` int(11) NOT NULL default '0',
  `tag` varchar(100) default NULL,
  `class` varchar(100) default NULL,
  `parameter` varchar(100) default NULL,
  `value` varchar(100) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `style_parameter`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `tree`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `usr_data`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

INSERT INTO `usr_data` VALUES (6, 'root', 'dfa8327f5bfa4c672a04f9b38e348a70', 'root', 'user', '', 'm', 'ilias@yourserver.com', '', '', '', '', '', '', '2003-08-15 11:03:46', '2003-08-15 10:44:05', '0000-00-00 00:00:00', '');
INSERT INTO `usr_data` VALUES (13, 'anonymous', '294de3557d9d00b3d2d8a1e6aab028cf', 'anonymous', 'anonymous', '', 'm', 'nomail', NULL, NULL, NULL, NULL, NULL, NULL, '2003-08-15 11:03:36', '2003-08-15 10:07:30', '2003-08-15 10:07:30', '');
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `usr_pref`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

INSERT INTO `usr_pref` VALUES (6, 'public_hobby', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_email', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_phone', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_country', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_city', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_zip', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_street', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_upload', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_institution', 'n');
INSERT INTO `usr_pref` VALUES (6, 'public_profile', 'n');
INSERT INTO `usr_pref` VALUES (6, 'language', 'de');
INSERT INTO `usr_pref` VALUES (6, 'skin', 'default');
INSERT INTO `usr_pref` VALUES (6, 'style', 'blueshadow');
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `usr_session`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `xmlnestedset`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `xmlparam`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
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

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `xmltags`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `xmltags` (
  `tag_pk` int(11) NOT NULL auto_increment,
  `tag_depth` int(11) NOT NULL default '0',
  `tag_name` char(50) NOT NULL default '',
  PRIMARY KEY  (`tag_pk`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `xmltags`
#

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `xmlvalue`
#
# Erzeugt am: 15. August 2003 um 13:53
# Aktualisiert am: 15. August 2003 um 13:53
#

CREATE TABLE `xmlvalue` (
  `tag_value_pk` int(11) NOT NULL auto_increment,
  `tag_fk` int(11) NOT NULL default '0',
  `tag_value` text NOT NULL,
  PRIMARY KEY  (`tag_value_pk`),
  KEY `tag_fk` (`tag_fk`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten für Tabelle `xmlvalue`
#


