# phpMyAdmin MySQL-Dump
# version 2.4.0
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Jul 11, 2004 at 01:05 AM
# Server version: 3.23.56
# PHP Version: 4.3.6
# Database : `ilias310beta1`
# --------------------------------------------------------

#
# Table structure for table `addressbook`
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
# Dumping data for table `addressbook`
#

# --------------------------------------------------------

#
# Table structure for table `aicc_course`
#

CREATE TABLE `aicc_course` (
  `obj_id` int(11) NOT NULL default '0',
  `course_creator` varchar(255) default NULL,
  `course_id` varchar(50) default NULL,
  `course_system` varchar(50) default NULL,
  `course_title` varchar(255) default NULL,
  `level` varchar(5) default NULL,
  `max_fields_cst` smallint(6) default NULL,
  `max_fields_ort` smallint(6) default NULL,
  `total_aus` smallint(6) default NULL,
  `total_blocks` smallint(6) default NULL,
  `total_complex_obj` smallint(6) default NULL,
  `total_objectives` smallint(6) default NULL,
  `version` varchar(10) default NULL,
  `max_normal` tinyint(4) default NULL,
  `description` text,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `aicc_course`
#

# --------------------------------------------------------

#
# Table structure for table `aicc_object`
#

CREATE TABLE `aicc_object` (
  `obj_id` int(11) NOT NULL auto_increment,
  `alm_id` int(11) NOT NULL default '0',
  `system_id` varchar(50) NOT NULL default '',
  `title` text NOT NULL,
  `description` text,
  `developer_id` varchar(50) default NULL,
  `type` char(3) NOT NULL default '',
  PRIMARY KEY  (`obj_id`),
  KEY `alm_id` (`alm_id`)
) TYPE=MyISAM;

#
# Dumping data for table `aicc_object`
#

# --------------------------------------------------------

#
# Table structure for table `aicc_units`
#

CREATE TABLE `aicc_units` (
  `obj_id` int(11) NOT NULL auto_increment,
  `type` varchar(50) default NULL,
  `command_line` varchar(255) default NULL,
  `max_time_allowed` time default NULL,
  `time_limit_action` varchar(50) default NULL,
  `max_score` decimal(10,0) default NULL,
  `core_vendor` text,
  `system_vendor` text,
  `file_name` varchar(255) default NULL,
  `mastery_score` smallint(6) default NULL,
  `web_launch` varchar(255) default NULL,
  `au_password` varchar(50) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `aicc_units`
#

# --------------------------------------------------------

#
# Table structure for table `benchmark`
#

CREATE TABLE `benchmark` (
  `cdate` datetime default NULL,
  `module` varchar(200) default NULL,
  `benchmark` varchar(200) default NULL,
  `duration` double(14,5) default NULL,
  KEY `module` (`module`,`benchmark`)
) TYPE=MyISAM;

#
# Dumping data for table `benchmark`
#

# --------------------------------------------------------

#
# Table structure for table `bookmark_data`
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
# Dumping data for table `bookmark_data`
#

# --------------------------------------------------------

#
# Table structure for table `bookmark_tree`
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
# Dumping data for table `bookmark_tree`
#

INSERT INTO `bookmark_tree` VALUES (6, 1, 0, 1, 2, 1);
# --------------------------------------------------------

#
# Table structure for table `chat_invitations`
#

CREATE TABLE `chat_invitations` (
  `room_id` int(11) NOT NULL default '0',
  `guest_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`room_id`,`guest_id`)
) TYPE=MyISAM;

#
# Dumping data for table `chat_invitations`
#

# --------------------------------------------------------

#
# Table structure for table `chat_room_messages`
#

CREATE TABLE `chat_room_messages` (
  `entry_id` int(11) NOT NULL auto_increment,
  `chat_id` int(11) NOT NULL default '0',
  `room_id` int(11) NOT NULL default '0',
  `message` text,
  `commit_timestamp` timestamp(14) NOT NULL,
  PRIMARY KEY  (`entry_id`)
) TYPE=MyISAM;

#
# Dumping data for table `chat_room_messages`
#

# --------------------------------------------------------

#
# Table structure for table `chat_rooms`
#

CREATE TABLE `chat_rooms` (
  `room_id` int(11) NOT NULL auto_increment,
  `chat_id` int(11) NOT NULL default '0',
  `title` varchar(64) default NULL,
  `owner` int(11) NOT NULL default '0',
  PRIMARY KEY  (`room_id`)
) TYPE=MyISAM;

#
# Dumping data for table `chat_rooms`
#

# --------------------------------------------------------

#
# Table structure for table `chat_user`
#

CREATE TABLE `chat_user` (
  `usr_id` int(11) NOT NULL default '0',
  `chat_id` int(11) NOT NULL default '0',
  `room_id` int(11) NOT NULL default '0',
  `last_conn_timestamp` timestamp(14) NOT NULL,
  PRIMARY KEY  (`usr_id`,`chat_id`,`room_id`)
) TYPE=MyISAM;

#
# Dumping data for table `chat_user`
#

# --------------------------------------------------------

#
# Table structure for table `content_object`
#

CREATE TABLE `content_object` (
  `id` int(11) NOT NULL default '0',
  `default_layout` varchar(100) NOT NULL default 'toc2win',
  `stylesheet` int(11) NOT NULL default '0',
  `page_header` enum('st_title','pg_title','none') default 'st_title',
  `online` enum('y','n') default 'n',
  `toc_active` enum('y','n') default 'y',
  `lm_menu_active` enum('y','n') default 'y',
  `toc_mode` enum('chapters','pages') default 'chapters',
  `clean_frames` enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `content_object`
#

# --------------------------------------------------------

#
# Table structure for table `ctrl_calls`
#

CREATE TABLE `ctrl_calls` (
  `parent` varchar(100) NOT NULL default '',
  `child` varchar(100) default NULL
) TYPE=MyISAM;

#
# Dumping data for table `ctrl_calls`
#

INSERT INTO `ctrl_calls` VALUES ('ilobjmediapoolgui', 'ilobjmediaobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjmediapoolgui', 'ilobjfoldergui');
INSERT INTO `ctrl_calls` VALUES ('ilobjmediapoolgui', 'ileditclipboardgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjmediaobjectgui', 'ilinternallinkgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageobjectgui', 'ilpageeditorgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageobjectgui', 'ileditclipboardgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilpcparagraphgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilpctablegui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilpctabledatagui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilpcmediaobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilpclistgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilpclistitemgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilpcfilelistgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilpcfileitemgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilobjmediaobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilpcsourcecodegui');
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilinternallinkgui');
INSERT INTO `ctrl_calls` VALUES ('illmeditorgui', 'ilobjdlbookgui');
INSERT INTO `ctrl_calls` VALUES ('illmeditorgui', 'ilmetadatagui');
INSERT INTO `ctrl_calls` VALUES ('illmeditorgui', 'ilobjlearningmodulegui');
INSERT INTO `ctrl_calls` VALUES ('ilobjglossarygui', 'ilglossarytermgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjdlbookgui', 'illmpageobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjdlbookgui', 'ilstructureobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjdlbookgui', 'ilobjstylesheetgui');
INSERT INTO `ctrl_calls` VALUES ('illmpageobjectgui', 'ilpageobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjfilebasedlmgui', 'ilfilesystemgui');
INSERT INTO `ctrl_calls` VALUES ('iltermdefinitioneditorgui', 'ilpageobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjlearningmodulegui', 'illmpageobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjlearningmodulegui', 'ilstructureobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjlearningmodulegui', 'ilobjstylesheetgui');
INSERT INTO `ctrl_calls` VALUES ('ilglossarytermgui', 'iltermdefinitioneditorgui');
INSERT INTO `ctrl_calls` VALUES ('ileditclipboardgui', 'ilobjmediaobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjhacplearningmodulegui', 'ilfilesystemgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjgroupgui', 'ilregistergui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjgroupgui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjfoldergui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjfilegui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjcoursegui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjscormlearningmodulegui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjchatgui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjforumgui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjlearningmodulegui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjdlbookgui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjglossarygui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjquestionpoolgui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjsurveyquestionpoolgui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjtestgui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjsurveygui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjexercisegui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjmediapoolgui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjfilebasedlmgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjaicclearningmodulegui', 'ilfilesystemgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjscormlearningmodulegui', 'ilfilesystemgui');
# --------------------------------------------------------

#
# Table structure for table `ctrl_classfile`
#

CREATE TABLE `ctrl_classfile` (
  `class` varchar(100) NOT NULL default '',
  `file` varchar(250) default NULL,
  PRIMARY KEY  (`class`)
) TYPE=MyISAM;

#
# Dumping data for table `ctrl_classfile`
#

INSERT INTO `ctrl_classfile` VALUES ('ilobjmediapoolgui', 'content/classes/class.ilObjMediaPoolGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjmediaobjectgui', 'content/classes/Media/class.ilObjMediaObjectGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilpageobjectgui', 'content/classes/Pages/class.ilPageObjectGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilpageeditorgui', 'content/classes/Pages/class.ilPageEditorGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('illmeditorgui', 'content/classes/class.ilLMEditorGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjglossarygui', 'content/classes/class.ilObjGlossaryGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjdlbookgui', 'content/classes/class.ilObjDlBookGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('illmpageobjectgui', 'content/classes/class.ilLMPageObjectGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjfilebasedlmgui', 'content/classes/class.ilObjFileBasedLMGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('iltermdefinitioneditorgui', 'content/classes/class.ilTermDefinitionEditorGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjlearningmodulegui', 'content/classes/class.ilObjLearningModuleGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilglossarytermgui', 'content/classes/class.ilGlossaryTermGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ileditclipboardgui', 'content/classes/class.ilEditClipboardGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjhacplearningmodulegui', 'classes/class.ilObjHACPLearningModuleGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjgroupgui', 'classes/class.ilObjGroupGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilrepositorygui', 'classes/class.ilRepositoryGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjaicclearningmodulegui', 'classes/class.ilObjAICCLearningModuleGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjscormlearningmodulegui', 'classes/class.ilObjSCORMLearningModuleGUI.php');
# --------------------------------------------------------

#
# Table structure for table `dbk_translations`
#

CREATE TABLE `dbk_translations` (
  `id` int(11) NOT NULL default '0',
  `tr_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`,`tr_id`)
) TYPE=MyISAM;

#
# Dumping data for table `dbk_translations`
#

# --------------------------------------------------------

#
# Table structure for table `desktop_item`
#

CREATE TABLE `desktop_item` (
  `user_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  `type` varchar(4) NOT NULL default '',
  `parameters` varchar(200) default NULL,
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

#
# Dumping data for table `desktop_item`
#

# --------------------------------------------------------

#
# Table structure for table `dp_changed_dates`
#

CREATE TABLE `dp_changed_dates` (
  `ID` int(15) NOT NULL auto_increment,
  `user_ID` int(15) NOT NULL default '0',
  `date_ID` int(15) NOT NULL default '0',
  `status` int(15) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Tabelle für Anzeige von Geänderten Termindaten';

#
# Dumping data for table `dp_changed_dates`
#

# --------------------------------------------------------

#
# Table structure for table `dp_dates`
#

CREATE TABLE `dp_dates` (
  `ID` int(15) NOT NULL auto_increment,
  `begin` int(10) NOT NULL default '0',
  `end` int(10) NOT NULL default '0',
  `group_ID` int(15) NOT NULL default '0',
  `user_ID` int(15) NOT NULL default '0',
  `created` int(10) NOT NULL default '0',
  `changed` int(10) NOT NULL default '0',
  `rotation` int(15) NOT NULL default '0',
  `shorttext` varchar(50) NOT NULL default '',
  `text` text,
  `end_rotation` int(10) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Termin Tabelle';

#
# Dumping data for table `dp_dates`
#

# --------------------------------------------------------

#
# Table structure for table `dp_keyword`
#

CREATE TABLE `dp_keyword` (
  `ID` int(15) NOT NULL auto_increment,
  `user_ID` int(15) NOT NULL default '0',
  `keyword` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Tabelle für Schlagwörter';

#
# Dumping data for table `dp_keyword`
#

# --------------------------------------------------------

#
# Table structure for table `dp_keywords`
#

CREATE TABLE `dp_keywords` (
  `ID` int(15) NOT NULL auto_increment,
  `date_ID` int(15) NOT NULL default '0',
  `keyword_ID` int(15) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Tabelle für die Zuordnung der Schlagwörter';

#
# Dumping data for table `dp_keywords`
#

# --------------------------------------------------------

#
# Table structure for table `dp_neg_dates`
#

CREATE TABLE `dp_neg_dates` (
  `ID` int(15) NOT NULL auto_increment,
  `date_ID` int(15) NOT NULL default '0',
  `user_ID` int(15) NOT NULL default '0',
  `timestamp` int(14) default NULL,
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Tabelle für die negativen Termine';

#
# Dumping data for table `dp_neg_dates`
#

# --------------------------------------------------------

#
# Table structure for table `dp_properties`
#

CREATE TABLE `dp_properties` (
  `ID` int(15) NOT NULL auto_increment,
  `user_ID` int(15) NOT NULL default '0',
  `dv_starttime` time NOT NULL default '00:00:00',
  `dv_endtime` time NOT NULL default '00:00:00',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Tabelle für UserEinstellungen';

#
# Dumping data for table `dp_properties`
#

# --------------------------------------------------------

#
# Table structure for table `exc_data`
#

CREATE TABLE `exc_data` (
  `obj_id` int(11) NOT NULL default '0',
  `instruction` text,
  `time_stamp` int(10) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `exc_data`
#

# --------------------------------------------------------

#
# Table structure for table `exc_members`
#

CREATE TABLE `exc_members` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `notice` text,
  `returned` tinyint(1) default NULL,
  `solved` tinyint(1) default NULL,
  `sent` tinyint(1) default NULL,
  PRIMARY KEY  (`obj_id`,`usr_id`)
) TYPE=MyISAM;

#
# Dumping data for table `exc_members`
#

# --------------------------------------------------------

#
# Table structure for table `file_based_lm`
#

CREATE TABLE `file_based_lm` (
  `id` int(11) NOT NULL default '0',
  `online` enum('y','n') default 'n',
  `startfile` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `file_based_lm`
#

# --------------------------------------------------------

#
# Table structure for table `file_data`
#

CREATE TABLE `file_data` (
  `file_id` int(11) NOT NULL default '0',
  `file_name` char(128) NOT NULL default '',
  `file_type` char(64) NOT NULL default '',
  PRIMARY KEY  (`file_id`)
) TYPE=MyISAM;

#
# Dumping data for table `file_data`
#

# --------------------------------------------------------

#
# Table structure for table `file_usage`
#

CREATE TABLE `file_usage` (
  `id` int(11) NOT NULL default '0',
  `usage_type` varchar(10) NOT NULL default '',
  `usage_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`,`usage_type`,`usage_id`)
) TYPE=MyISAM;

#
# Dumping data for table `file_usage`
#

# --------------------------------------------------------

#
# Table structure for table `frm_data`
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
# Dumping data for table `frm_data`
#

# --------------------------------------------------------

#
# Table structure for table `frm_posts`
#

CREATE TABLE `frm_posts` (
  `pos_pk` bigint(20) NOT NULL auto_increment,
  `pos_top_fk` bigint(20) NOT NULL default '0',
  `pos_thr_fk` bigint(20) NOT NULL default '0',
  `pos_usr_id` bigint(20) NOT NULL default '0',
  `pos_message` text NOT NULL,
  `pos_subject` text NOT NULL,
  `pos_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `pos_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `update_user` int(11) NOT NULL default '0',
  `pos_cens` tinyint(4) NOT NULL default '0',
  `pos_cens_com` text NOT NULL,
  `notify` tinyint(1) NOT NULL default '0',
  `import_name` text,
  PRIMARY KEY  (`pos_pk`)
) TYPE=MyISAM;

#
# Dumping data for table `frm_posts`
#

# --------------------------------------------------------

#
# Table structure for table `frm_posts_tree`
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
# Dumping data for table `frm_posts_tree`
#

# --------------------------------------------------------

#
# Table structure for table `frm_threads`
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
  `import_name` text,
  PRIMARY KEY  (`thr_pk`)
) TYPE=MyISAM;

#
# Dumping data for table `frm_threads`
#

# --------------------------------------------------------

#
# Table structure for table `glossary`
#

CREATE TABLE `glossary` (
  `id` int(11) NOT NULL default '0',
  `online` enum('y','n') default 'n',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `glossary`
#

# --------------------------------------------------------

#
# Table structure for table `glossary_definition`
#

CREATE TABLE `glossary_definition` (
  `id` int(11) NOT NULL auto_increment,
  `term_id` int(11) NOT NULL default '0',
  `short_text` varchar(200) NOT NULL default '',
  `nr` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `glossary_definition`
#

# --------------------------------------------------------

#
# Table structure for table `glossary_term`
#

CREATE TABLE `glossary_term` (
  `id` int(11) NOT NULL auto_increment,
  `glo_id` int(11) NOT NULL default '0',
  `term` varchar(200) default NULL,
  `language` char(2) default NULL,
  `import_id` varchar(50) NOT NULL default '',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `glo_id` (`glo_id`)
) TYPE=MyISAM;

#
# Dumping data for table `glossary_term`
#

# --------------------------------------------------------

#
# Table structure for table `grp_data`
#

CREATE TABLE `grp_data` (
  `grp_id` int(11) NOT NULL default '0',
  `register` int(11) default '1',
  `password` varchar(255) default NULL,
  `expiration` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`grp_id`)
) TYPE=MyISAM;

#
# Dumping data for table `grp_data`
#

# --------------------------------------------------------

#
# Table structure for table `grp_registration`
#

CREATE TABLE `grp_registration` (
  `grp_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `application_date` datetime NOT NULL default '0000-00-00 00:00:00'
) TYPE=MyISAM;

#
# Dumping data for table `grp_registration`
#

# --------------------------------------------------------

#
# Table structure for table `int_link`
#

CREATE TABLE `int_link` (
  `source_type` varchar(10) NOT NULL default '',
  `source_id` int(11) NOT NULL default '0',
  `target_type` varchar(4) NOT NULL default '',
  `target_id` int(11) NOT NULL default '0',
  `target_inst` int(11) NOT NULL default '0',
  PRIMARY KEY  (`source_type`,`source_id`,`target_type`,`target_id`,`target_inst`)
) TYPE=MyISAM;

#
# Dumping data for table `int_link`
#

# --------------------------------------------------------

#
# Table structure for table `lm_data`
#

CREATE TABLE `lm_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `type` char(2) NOT NULL default '',
  `lm_id` int(11) NOT NULL default '0',
  `import_id` varchar(50) NOT NULL default '',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `lm_data`
#

INSERT INTO `lm_data` VALUES (1, 'dummy', 'du', 0, '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
# --------------------------------------------------------

#
# Table structure for table `lm_tree`
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
# Dumping data for table `lm_tree`
#

# --------------------------------------------------------

#
# Table structure for table `lng_data`
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
# Dumping data for table `lng_data`
#

INSERT INTO `lng_data` VALUES ('assessment', 'add_answer', 'en', 'Add answer');
INSERT INTO `lng_data` VALUES ('assessment', 'add_matching_pair', 'en', 'Add matching pair');
INSERT INTO `lng_data` VALUES ('assessment', 'answer', 'en', 'Answer');
INSERT INTO `lng_data` VALUES ('assessment', 'answer_text', 'en', 'Answer text');
INSERT INTO `lng_data` VALUES ('assessment', 'apply', 'en', 'Apply');
INSERT INTO `lng_data` VALUES ('assessment', 'ass_questions', 'en', 'Questions');
INSERT INTO `lng_data` VALUES ('assessment', 'by', 'en', 'by');
INSERT INTO `lng_data` VALUES ('assessment', 'cloze_text', 'en', 'Cloze text');
INSERT INTO `lng_data` VALUES ('assessment', 'create_date', 'en', 'Created');
INSERT INTO `lng_data` VALUES ('assessment', 'create_gaps', 'en', 'Create gaps');
INSERT INTO `lng_data` VALUES ('assessment', 'create_new', 'en', 'Create new');
INSERT INTO `lng_data` VALUES ('assessment', 'duplicate', 'en', 'Duplicate');
INSERT INTO `lng_data` VALUES ('assessment', 'export', 'en', 'Export');
INSERT INTO `lng_data` VALUES ('assessment', 'false', 'en', 'False');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_file', 'en', 'You didn\'t choose a file');
INSERT INTO `lng_data` VALUES ('assessment', 'filter', 'en', 'Filter');
INSERT INTO `lng_data` VALUES ('assessment', 'gap_selection', 'en', 'Gap selection');
INSERT INTO `lng_data` VALUES ('assessment', 'last_update', 'en', 'Last update');
INSERT INTO `lng_data` VALUES ('assessment', 'matches', 'en', 'matches');
INSERT INTO `lng_data` VALUES ('assessment', 'matching_pair', 'en', 'Matching pair');
INSERT INTO `lng_data` VALUES ('assessment', 'no_questions_available', 'en', 'There are no questions available!');
INSERT INTO `lng_data` VALUES ('assessment', 'points', 'en', 'Points');
INSERT INTO `lng_data` VALUES ('assessment', 'possible_gap_text', 'en', 'Possible gap text');
INSERT INTO `lng_data` VALUES ('assessment', 'preview', 'en', 'Preview');
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_delete_rbac_error', 'en', 'You have no rights to delete this question!');
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_delete_select_none', 'en', 'Please check at least one question to delete it');
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_duplicate_select_none', 'en', 'Please check at least one question to duplicate it');
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_edit_rbac_error', 'en', 'You have no rights to edit this question!');
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_edit_select_multiple', 'en', 'Please check only one question for editing');
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_edit_select_none', 'en', 'Please check one question for editing');
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_export_select_none', 'en', 'Please check at least one question to export it');
INSERT INTO `lng_data` VALUES ('assessment', 'qt_cloze', 'en', 'Cloze Question');
INSERT INTO `lng_data` VALUES ('assessment', 'qt_matching', 'en', 'Matching Question');
INSERT INTO `lng_data` VALUES ('assessment', 'qt_multiple_choice_mr', 'en', 'Multiple Choice Question (multiple response)');
INSERT INTO `lng_data` VALUES ('assessment', 'qt_multiple_choice_sr', 'en', 'Multiple Choice Question (single response)');
INSERT INTO `lng_data` VALUES ('assessment', 'qt_ordering', 'en', 'Ordering Question');
INSERT INTO `lng_data` VALUES ('assessment', 'question_type', 'en', 'Question Type');
INSERT INTO `lng_data` VALUES ('assessment', 'reset_filter', 'en', 'Reset filter');
INSERT INTO `lng_data` VALUES ('assessment', 'select_gap', 'en', 'Select gap');
INSERT INTO `lng_data` VALUES ('assessment', 'set_filter', 'en', 'Set filter');
INSERT INTO `lng_data` VALUES ('assessment', 'solution_order', 'en', 'Solution order');
INSERT INTO `lng_data` VALUES ('assessment', 'text_gap', 'en', 'Text gap');
INSERT INTO `lng_data` VALUES ('assessment', 'true', 'en', 'True');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_general_properties', 'en', 'General properties');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_introduction', 'en', 'Introduction');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_nr_of_tries', 'en', 'Max. number of tries');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_processing_time', 'en', 'Max. processing time');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_report_after_question', 'en', 'Report the score after every question is answered');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_report_after_test', 'en', 'Report the score after completing the whole test');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_score_reporting', 'en', 'Score reporting');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_score_reporting_date', 'en', 'Score reporting date');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_score_type', 'en', 'Score reporting type');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_sequence', 'en', 'Sequence');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_sequence_fixed', 'en', 'The sequence of questions is fixed');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_sequence_postpone', 'en', 'The learner may postpone a question to the end of the test');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_sequence_properties', 'en', 'Sequence properties');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_session_settings', 'en', 'Session settings');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_starting_time', 'en', 'Starting time');
INSERT INTO `lng_data` VALUES ('assessment', 'tst_types', 'en', 'Test types');
INSERT INTO `lng_data` VALUES ('common', '3rd_party_software', 'en', '3rd party software');
INSERT INTO `lng_data` VALUES ('common', 'DD.MM.YYYY', 'en', 'DD.MM.YYYY');
INSERT INTO `lng_data` VALUES ('common', 'HH:MM', 'en', 'HH:MM');
INSERT INTO `lng_data` VALUES ('common', 'absolute_path', 'en', 'Absolute Path');
INSERT INTO `lng_data` VALUES ('common', 'accept_usr_agreement', 'en', 'Accept user agreement?');
INSERT INTO `lng_data` VALUES ('common', 'access', 'en', 'Access');
INSERT INTO `lng_data` VALUES ('common', 'action_aborted', 'en', 'Action aborted');
INSERT INTO `lng_data` VALUES ('common', 'actions', 'en', 'Actions');
INSERT INTO `lng_data` VALUES ('common', 'active_roles', 'en', 'Active Roles');
INSERT INTO `lng_data` VALUES ('common', 'add', 'en', 'Add');
INSERT INTO `lng_data` VALUES ('common', 'add_author', 'en', 'Add Author');
INSERT INTO `lng_data` VALUES ('common', 'add_member', 'en', 'Add Member');
INSERT INTO `lng_data` VALUES ('common', 'add_translation', 'en', 'Add translation');
INSERT INTO `lng_data` VALUES ('common', 'adm_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'adm_read', 'en', 'Read access to System settings');
INSERT INTO `lng_data` VALUES ('common', 'adm_visible', 'en', 'Systemsettings are visible');
INSERT INTO `lng_data` VALUES ('common', 'adm_write', 'en', 'Edit Basic system settings');
INSERT INTO `lng_data` VALUES ('common', 'administrate', 'en', 'Administrate');
INSERT INTO `lng_data` VALUES ('common', 'administration', 'en', 'Administration');
INSERT INTO `lng_data` VALUES ('common', 'administrator', 'en', 'Administrator');
INSERT INTO `lng_data` VALUES ('common', 'adopt', 'en', 'adopt');
INSERT INTO `lng_data` VALUES ('common', 'adopt_perm_from_template', 'en', 'Adopt permission settings from Role Template');
INSERT INTO `lng_data` VALUES ('common', 'all_objects', 'en', 'All Objects');
INSERT INTO `lng_data` VALUES ('common', 'allow_register', 'en', 'Available in registration form for new users');
INSERT INTO `lng_data` VALUES ('common', 'and', 'en', 'and');
INSERT INTO `lng_data` VALUES ('common', 'announce', 'en', 'Announce');
INSERT INTO `lng_data` VALUES ('common', 'announce_changes', 'en', 'Announce Changes');
INSERT INTO `lng_data` VALUES ('common', 'answers', 'en', 'Answers');
INSERT INTO `lng_data` VALUES ('common', 'application_completed', 'en', 'Application is complete');
INSERT INTO `lng_data` VALUES ('common', 'application_date', 'en', 'Application date');
INSERT INTO `lng_data` VALUES ('common', 'appointment', 'en', 'Appointment');
INSERT INTO `lng_data` VALUES ('common', 'appointment_list', 'en', 'Appointment List');
INSERT INTO `lng_data` VALUES ('common', 'archive', 'en', 'Archive');
INSERT INTO `lng_data` VALUES ('common', 'are_you_sure', 'en', 'Are you sure?');
INSERT INTO `lng_data` VALUES ('common', 'assign', 'en', 'Assign');
INSERT INTO `lng_data` VALUES ('common', 'assign_lo_forum', 'en', 'Assign LO Forum');
INSERT INTO `lng_data` VALUES ('common', 'assign_user_to_role', 'en', 'Assign User to Role');
INSERT INTO `lng_data` VALUES ('common', 'at_location', 'en', 'at location');
INSERT INTO `lng_data` VALUES ('common', 'attachment', 'en', 'Attachment');
INSERT INTO `lng_data` VALUES ('common', 'attachments', 'en', 'Attachments');
INSERT INTO `lng_data` VALUES ('common', 'author', 'en', 'Author');
INSERT INTO `lng_data` VALUES ('common', 'authors', 'en', 'Authors');
INSERT INTO `lng_data` VALUES ('common', 'available_languages', 'en', 'Available Languages');
INSERT INTO `lng_data` VALUES ('common', 'back', 'en', 'Back');
INSERT INTO `lng_data` VALUES ('common', 'basedn', 'en', 'BaseDN');
INSERT INTO `lng_data` VALUES ('common', 'basic_data', 'en', 'Basic Data');
INSERT INTO `lng_data` VALUES ('common', 'bm', 'en', 'Bookmark');
INSERT INTO `lng_data` VALUES ('common', 'bmf', 'en', 'Bookmark Folder');
INSERT INTO `lng_data` VALUES ('common', 'bookmark_edit', 'en', 'Edit Bookmark');
INSERT INTO `lng_data` VALUES ('common', 'bookmark_folder_edit', 'en', 'Edit Bookmark Folder');
INSERT INTO `lng_data` VALUES ('common', 'bookmark_folder_new', 'en', 'New Bookmark Folder');
INSERT INTO `lng_data` VALUES ('common', 'bookmark_new', 'en', 'New Bookmark');
INSERT INTO `lng_data` VALUES ('common', 'bookmark_target', 'en', 'Target');
INSERT INTO `lng_data` VALUES ('common', 'bookmarks', 'en', 'Bookmarks');
INSERT INTO `lng_data` VALUES ('common', 'bookmarks_of', 'en', 'Bookmarks of');
INSERT INTO `lng_data` VALUES ('common', 'btn_remove_system', 'en', 'Remove from System');
INSERT INTO `lng_data` VALUES ('common', 'btn_undelete', 'en', 'Undelete');
INSERT INTO `lng_data` VALUES ('common', 'by', 'en', 'By');
INSERT INTO `lng_data` VALUES ('common', 'calendar', 'en', 'Calendar');
INSERT INTO `lng_data` VALUES ('common', 'cancel', 'en', 'Cancel');
INSERT INTO `lng_data` VALUES ('common', 'cannot_uninstall_language_in_use', 'en', 'You cannot uninstall the language currently in use!');
INSERT INTO `lng_data` VALUES ('common', 'cannot_uninstall_systemlanguage', 'en', 'You cannot uninstall the system language!');
INSERT INTO `lng_data` VALUES ('common', 'cat', 'en', 'Category');
INSERT INTO `lng_data` VALUES ('common', 'cat_a', 'en', 'a Category');
INSERT INTO `lng_data` VALUES ('common', 'cat_add', 'en', 'Add Category');
INSERT INTO `lng_data` VALUES ('common', 'cat_added', 'en', 'Category added');
INSERT INTO `lng_data` VALUES ('common', 'cat_create', 'en', 'Create Category');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_cat', 'en', 'Create Category');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_chat', 'en', 'Create Chat');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_crs', 'en', 'Create Course');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_dbk', 'en', 'Create Digibook');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_exc', 'en', 'Create Exercise');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_frm', 'en', 'Create Forum');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_glo', 'en', 'Create Glossary');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_grp', 'en', 'Create Group');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_lm', 'en', 'Create Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_qpl', 'en', 'Create Question Pool');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_slm', 'en', 'Create SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'cat_create_tst', 'en', 'Create Test');
INSERT INTO `lng_data` VALUES ('common', 'cat_delete', 'en', 'Delete Category');
INSERT INTO `lng_data` VALUES ('common', 'cat_edit', 'en', 'Edit Category');
INSERT INTO `lng_data` VALUES ('common', 'cat_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'cat_new', 'en', 'New Category');
INSERT INTO `lng_data` VALUES ('common', 'cat_read', 'en', 'Read access to Category');
INSERT INTO `lng_data` VALUES ('common', 'cat_visible', 'en', 'Category is visible');
INSERT INTO `lng_data` VALUES ('common', 'cat_write', 'en', 'Edit Category');
INSERT INTO `lng_data` VALUES ('common', 'categories', 'en', 'Categories');
INSERT INTO `lng_data` VALUES ('common', 'cc', 'en', 'CC');
INSERT INTO `lng_data` VALUES ('common', 'censorship', 'en', 'Censorship');
INSERT INTO `lng_data` VALUES ('common', 'chac_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'chac_read', 'en', 'Read access to chat settings');
INSERT INTO `lng_data` VALUES ('common', 'chac_visible', 'en', 'Chat settings are visible');
INSERT INTO `lng_data` VALUES ('common', 'chac_write', 'en', 'Edit chat settings');
INSERT INTO `lng_data` VALUES ('common', 'change', 'en', 'Change');
INSERT INTO `lng_data` VALUES ('common', 'change_active_assignment', 'en', 'Change active assignment');
INSERT INTO `lng_data` VALUES ('common', 'change_assignment', 'en', 'Change assignment');
INSERT INTO `lng_data` VALUES ('common', 'change_existing_objects', 'en', 'Change existing Objects');
INSERT INTO `lng_data` VALUES ('common', 'change_lo_info', 'en', 'Change LO Info');
INSERT INTO `lng_data` VALUES ('common', 'change_metadata', 'en', 'Change Metadata');
INSERT INTO `lng_data` VALUES ('common', 'change_sort_direction', 'en', 'Change sort direction');
INSERT INTO `lng_data` VALUES ('common', 'changed_to', 'en', 'changed to');
INSERT INTO `lng_data` VALUES ('common', 'chapter', 'en', 'Chapter');
INSERT INTO `lng_data` VALUES ('common', 'chapter_number', 'en', 'Chapter Number');
INSERT INTO `lng_data` VALUES ('common', 'chapter_title', 'en', 'Chapter Title');
INSERT INTO `lng_data` VALUES ('common', 'chat_delete', 'en', 'Delete chat');
INSERT INTO `lng_data` VALUES ('common', 'chat_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'chat_read', 'en', 'Read/Write access to chat');
INSERT INTO `lng_data` VALUES ('common', 'chat_visible', 'en', 'Chat is visible');
INSERT INTO `lng_data` VALUES ('common', 'chat_write', 'en', 'Edit chat');
INSERT INTO `lng_data` VALUES ('common', 'check', 'en', 'Check');
INSERT INTO `lng_data` VALUES ('common', 'check_langfile', 'en', 'Please check your language file');
INSERT INTO `lng_data` VALUES ('common', 'check_languages', 'en', 'Check Languages');
INSERT INTO `lng_data` VALUES ('common', 'check_max_allowed_packet_size', 'en', 'The page content size is too large.');
INSERT INTO `lng_data` VALUES ('common', 'chg_language', 'en', 'Change Language');
INSERT INTO `lng_data` VALUES ('common', 'chg_password', 'en', 'Change Password');
INSERT INTO `lng_data` VALUES ('common', 'choose_language', 'en', 'Choose Your Language');
INSERT INTO `lng_data` VALUES ('common', 'choose_location', 'en', 'Choose Location');
INSERT INTO `lng_data` VALUES ('common', 'choose_only_one_language', 'en', 'Please choose only one language');
INSERT INTO `lng_data` VALUES ('common', 'city', 'en', 'City');
INSERT INTO `lng_data` VALUES ('common', 'clear', 'en', 'Clear');
INSERT INTO `lng_data` VALUES ('common', 'client_id', 'en', 'Client ID');
INSERT INTO `lng_data` VALUES ('common', 'clipboard', 'en', 'Clipboard');
INSERT INTO `lng_data` VALUES ('common', 'close', 'en', 'Close');
INSERT INTO `lng_data` VALUES ('common', 'comma_separated', 'en', 'Comma Separated');
INSERT INTO `lng_data` VALUES ('common', 'comment', 'en', 'comment');
INSERT INTO `lng_data` VALUES ('common', 'compose', 'en', 'Compose');
INSERT INTO `lng_data` VALUES ('common', 'confirm', 'en', 'Confirm');
INSERT INTO `lng_data` VALUES ('common', 'cont_object', 'en', 'Object');
INSERT INTO `lng_data` VALUES ('common', 'contact_data', 'en', 'Contact Information');
INSERT INTO `lng_data` VALUES ('common', 'context', 'en', 'Context');
INSERT INTO `lng_data` VALUES ('common', 'copy', 'en', 'Copy');
INSERT INTO `lng_data` VALUES ('common', 'copy_of', 'en', 'Copy of');
INSERT INTO `lng_data` VALUES ('common', 'country', 'en', 'Country');
INSERT INTO `lng_data` VALUES ('common', 'course', 'en', 'Course');
INSERT INTO `lng_data` VALUES ('common', 'courses', 'en', 'Courses');
INSERT INTO `lng_data` VALUES ('common', 'create', 'en', 'Create');
INSERT INTO `lng_data` VALUES ('common', 'create_in', 'en', 'Create in');
INSERT INTO `lng_data` VALUES ('common', 'create_stylesheet', 'en', 'Create Style');
INSERT INTO `lng_data` VALUES ('common', 'crs', 'en', 'Course');
INSERT INTO `lng_data` VALUES ('common', 'crs_a', 'en', 'a Course');
INSERT INTO `lng_data` VALUES ('common', 'crs_add', 'en', 'Add Course');
INSERT INTO `lng_data` VALUES ('common', 'crs_added', 'en', 'Course added');
INSERT INTO `lng_data` VALUES ('common', 'crs_available', 'en', 'Available Courses');
INSERT INTO `lng_data` VALUES ('common', 'crs_create', 'en', 'Create Course');
INSERT INTO `lng_data` VALUES ('common', 'crs_create_frm', 'en', 'Create Forum');
INSERT INTO `lng_data` VALUES ('common', 'crs_create_glo', 'en', 'Create Glossary');
INSERT INTO `lng_data` VALUES ('common', 'crs_create_lm', 'en', 'Create Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'crs_create_slm', 'en', 'Create SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'crs_delete', 'en', 'Delete Course');
INSERT INTO `lng_data` VALUES ('common', 'crs_edit', 'en', 'Edit Course');
INSERT INTO `lng_data` VALUES ('common', 'crs_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'crs_join', 'en', 'Subscribe to Course');
INSERT INTO `lng_data` VALUES ('common', 'crs_leave', 'en', 'Unsubscribe from Course');
INSERT INTO `lng_data` VALUES ('common', 'crs_management_system', 'en', 'Course Management System');
INSERT INTO `lng_data` VALUES ('common', 'crs_new', 'en', 'New Course');
INSERT INTO `lng_data` VALUES ('common', 'crs_read', 'en', 'Read access to Course');
INSERT INTO `lng_data` VALUES ('common', 'crs_visible', 'en', 'Course is visible');
INSERT INTO `lng_data` VALUES ('common', 'crs_write', 'en', 'Edit Course');
INSERT INTO `lng_data` VALUES ('common', 'current_password', 'en', 'Current Password');
INSERT INTO `lng_data` VALUES ('common', 'cut', 'en', 'Cut');
INSERT INTO `lng_data` VALUES ('common', 'cutPage', 'en', 'Cut');
INSERT INTO `lng_data` VALUES ('common', 'database', 'en', 'Database');
INSERT INTO `lng_data` VALUES ('common', 'database_version', 'en', 'Current Database Version');
INSERT INTO `lng_data` VALUES ('common', 'dataset', 'en', 'Item');
INSERT INTO `lng_data` VALUES ('common', 'date', 'en', 'Date');
INSERT INTO `lng_data` VALUES ('common', 'dateplaner', 'en', 'Dateplaner');
INSERT INTO `lng_data` VALUES ('common', 'day', 'en', 'Day');
INSERT INTO `lng_data` VALUES ('common', 'days', 'en', 'Days');
INSERT INTO `lng_data` VALUES ('common', 'db_host', 'en', 'Database Host');
INSERT INTO `lng_data` VALUES ('common', 'db_name', 'en', 'Database Name');
INSERT INTO `lng_data` VALUES ('common', 'db_need_update', 'en', 'Database needs an update!');
INSERT INTO `lng_data` VALUES ('common', 'db_pass', 'en', 'Database Password');
INSERT INTO `lng_data` VALUES ('common', 'db_type', 'en', 'Database Type');
INSERT INTO `lng_data` VALUES ('common', 'db_user', 'en', 'Database User');
INSERT INTO `lng_data` VALUES ('common', 'db_version', 'en', 'Database Version');
INSERT INTO `lng_data` VALUES ('common', 'dbk', 'en', 'Digilib Book');
INSERT INTO `lng_data` VALUES ('common', 'dbk_a', 'en', 'a Digilib Book');
INSERT INTO `lng_data` VALUES ('common', 'dbk_add', 'en', 'Add Digilib Book');
INSERT INTO `lng_data` VALUES ('common', 'dbk_added', 'en', 'Digilib Book added');
INSERT INTO `lng_data` VALUES ('common', 'dbk_create', 'en', 'Create Digibook');
INSERT INTO `lng_data` VALUES ('common', 'dbk_delete', 'en', 'Delete Digibook');
INSERT INTO `lng_data` VALUES ('common', 'dbk_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'dbk_new', 'en', 'New Digilib Book');
INSERT INTO `lng_data` VALUES ('common', 'dbk_read', 'en', 'Read access to Digibook');
INSERT INTO `lng_data` VALUES ('common', 'dbk_visible', 'en', 'Digibook is visible');
INSERT INTO `lng_data` VALUES ('common', 'dbk_write', 'en', 'Edit Digibook');
INSERT INTO `lng_data` VALUES ('common', 'default', 'en', 'Default');
INSERT INTO `lng_data` VALUES ('common', 'default_language', 'en', 'Default Language');
INSERT INTO `lng_data` VALUES ('common', 'default_perm_settings', 'en', 'Default permissions');
INSERT INTO `lng_data` VALUES ('common', 'default_role', 'en', 'Default Role');
INSERT INTO `lng_data` VALUES ('common', 'default_roles', 'en', 'Default Roles');
INSERT INTO `lng_data` VALUES ('common', 'default_skin', 'en', 'Default Skin');
INSERT INTO `lng_data` VALUES ('common', 'default_skin_style', 'en', 'Default Skin / Style');
INSERT INTO `lng_data` VALUES ('common', 'default_style', 'en', 'Default Style');
INSERT INTO `lng_data` VALUES ('common', 'delete', 'en', 'Delete');
INSERT INTO `lng_data` VALUES ('common', 'delete_all', 'en', 'Delete All');
INSERT INTO `lng_data` VALUES ('common', 'delete_object', 'en', 'Delete Object(s)');
INSERT INTO `lng_data` VALUES ('common', 'delete_selected', 'en', 'Delete Selected');
INSERT INTO `lng_data` VALUES ('common', 'deleted', 'en', 'Deleted');
INSERT INTO `lng_data` VALUES ('common', 'department', 'en', 'Department');
INSERT INTO `lng_data` VALUES ('common', 'desc', 'en', 'Description');
INSERT INTO `lng_data` VALUES ('common', 'description', 'en', 'Description');
INSERT INTO `lng_data` VALUES ('common', 'desired_password', 'en', 'Desired Password');
INSERT INTO `lng_data` VALUES ('common', 'disabled', 'en', 'Disabled');
INSERT INTO `lng_data` VALUES ('common', 'down', 'en', 'Down');
INSERT INTO `lng_data` VALUES ('common', 'download', 'en', 'Download');
INSERT INTO `lng_data` VALUES ('common', 'drafts', 'en', 'Drafts');
INSERT INTO `lng_data` VALUES ('common', 'drop', 'en', 'Drop');
INSERT INTO `lng_data` VALUES ('common', 'edit', 'en', 'Edit');
INSERT INTO `lng_data` VALUES ('common', 'edit_data', 'en', 'edit data');
INSERT INTO `lng_data` VALUES ('common', 'edit_operations', 'en', 'Edit Operations');
INSERT INTO `lng_data` VALUES ('common', 'edit_perm_ruleset', 'en', 'Edit default permission rules');
INSERT INTO `lng_data` VALUES ('common', 'edit_properties', 'en', 'Edit Properties');
INSERT INTO `lng_data` VALUES ('common', 'edit_stylesheet', 'en', 'Edit Style');
INSERT INTO `lng_data` VALUES ('common', 'edited_at', 'en', 'Edited at');
INSERT INTO `lng_data` VALUES ('common', 'editor', 'en', 'Editor');
INSERT INTO `lng_data` VALUES ('common', 'email', 'en', 'E-mail');
INSERT INTO `lng_data` VALUES ('common', 'email_not_valid', 'en', 'The email addess you entered is not valid!');
INSERT INTO `lng_data` VALUES ('common', 'enable', 'en', 'Enable');
INSERT INTO `lng_data` VALUES ('common', 'enable_registration', 'en', 'Enable new user registration');
INSERT INTO `lng_data` VALUES ('common', 'enabled', 'en', 'Enabled');
INSERT INTO `lng_data` VALUES ('common', 'enumerate', 'en', 'Enumerate');
INSERT INTO `lng_data` VALUES ('common', 'err_1_param', 'en', 'Only 1 parameter!');
INSERT INTO `lng_data` VALUES ('common', 'err_2_param', 'en', 'Only 2 parameter!');
INSERT INTO `lng_data` VALUES ('common', 'err_count_param', 'en', 'Reason: Wrong parameter count');
INSERT INTO `lng_data` VALUES ('common', 'err_in_line', 'en', 'Error in line');
INSERT INTO `lng_data` VALUES ('common', 'err_no_langfile_found', 'en', 'No language file found!');
INSERT INTO `lng_data` VALUES ('common', 'err_no_param', 'en', 'No parameter!');
INSERT INTO `lng_data` VALUES ('common', 'err_over_3_param', 'en', 'More than 3 parameters!');
INSERT INTO `lng_data` VALUES ('common', 'err_role_not_assignable', 'en', 'You cannot assign users to this role at this location');
INSERT INTO `lng_data` VALUES ('common', 'err_session_expired', 'en', 'Your session is expired!');
INSERT INTO `lng_data` VALUES ('common', 'err_wrong_header', 'en', 'Reason: Wrong header.');
INSERT INTO `lng_data` VALUES ('common', 'err_wrong_login', 'en', 'Wrong Login');
INSERT INTO `lng_data` VALUES ('common', 'error_recipient', 'en', 'Error Recipient');
INSERT INTO `lng_data` VALUES ('common', 'exc', 'en', 'Exercise');
INSERT INTO `lng_data` VALUES ('common', 'exc_add', 'en', 'Add Exercise');
INSERT INTO `lng_data` VALUES ('common', 'exc_added', 'en', 'Exercise added');
INSERT INTO `lng_data` VALUES ('common', 'exc_ask_delete', 'en', 'Delete file?');
INSERT INTO `lng_data` VALUES ('common', 'exc_assign_usr', 'en', 'Assign User');
INSERT INTO `lng_data` VALUES ('common', 'exc_create', 'en', 'Create exercise');
INSERT INTO `lng_data` VALUES ('common', 'exc_date_not_valid', 'en', 'The date is not valid');
INSERT INTO `lng_data` VALUES ('common', 'exc_deassign_members', 'en', 'Delete member(s)');
INSERT INTO `lng_data` VALUES ('common', 'exc_delete', 'en', 'Delete exercise');
INSERT INTO `lng_data` VALUES ('common', 'exc_edit', 'en', 'New exercise created');
INSERT INTO `lng_data` VALUES ('common', 'exc_edit_exercise', 'en', 'Edit exercise');
INSERT INTO `lng_data` VALUES ('common', 'exc_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'exc_edit_until', 'en', 'Edit until');
INSERT INTO `lng_data` VALUES ('common', 'exc_files', 'en', 'Files');
INSERT INTO `lng_data` VALUES ('common', 'exc_groups', 'en', 'Groups');
INSERT INTO `lng_data` VALUES ('common', 'exc_header_members', 'en', 'Exercise(members)');
INSERT INTO `lng_data` VALUES ('common', 'exc_instruction', 'en', 'Work instructions');
INSERT INTO `lng_data` VALUES ('common', 'exc_members_already_assigned', 'en', 'These user are already assigned to this exercise');
INSERT INTO `lng_data` VALUES ('common', 'exc_members_assigned', 'en', 'Members assigned');
INSERT INTO `lng_data` VALUES ('common', 'exc_new', 'en', 'New exercise');
INSERT INTO `lng_data` VALUES ('common', 'exc_no_members_assigned', 'en', 'No members assigned');
INSERT INTO `lng_data` VALUES ('common', 'exc_notices', 'en', 'Notices');
INSERT INTO `lng_data` VALUES ('common', 'exc_obj', 'en', 'Exercise');
INSERT INTO `lng_data` VALUES ('common', 'exc_read', 'en', 'Access exercise');
INSERT INTO `lng_data` VALUES ('common', 'exc_save_changes', 'en', 'Save');
INSERT INTO `lng_data` VALUES ('common', 'exc_search_for', 'en', 'Search for');
INSERT INTO `lng_data` VALUES ('common', 'exc_select_one_file', 'en', 'Please select exactly one file.');
INSERT INTO `lng_data` VALUES ('common', 'exc_send_exercise', 'en', 'Send exercise');
INSERT INTO `lng_data` VALUES ('common', 'exc_sent', 'en', 'The exercise has been sent to the selected users');
INSERT INTO `lng_data` VALUES ('common', 'exc_status_returned', 'en', 'Returned');
INSERT INTO `lng_data` VALUES ('common', 'exc_status_saved', 'en', 'Exercise updated');
INSERT INTO `lng_data` VALUES ('common', 'exc_status_solved', 'en', 'Solved');
INSERT INTO `lng_data` VALUES ('common', 'exc_upload_error', 'en', 'Error uploading file');
INSERT INTO `lng_data` VALUES ('common', 'exc_users', 'en', 'Users');
INSERT INTO `lng_data` VALUES ('common', 'exc_visible', 'en', 'Exercise is visible');
INSERT INTO `lng_data` VALUES ('common', 'exc_write', 'en', 'Edit exercise');
INSERT INTO `lng_data` VALUES ('common', 'excs', 'en', 'Exercises');
INSERT INTO `lng_data` VALUES ('common', 'execute', 'en', 'Execute');
INSERT INTO `lng_data` VALUES ('common', 'export_html', 'en', 'export as html file');
INSERT INTO `lng_data` VALUES ('common', 'export_xml', 'en', 'export as xml file');
INSERT INTO `lng_data` VALUES ('common', 'faq_exercise', 'en', 'FAQ Exercise');
INSERT INTO `lng_data` VALUES ('common', 'fax', 'en', 'Fax');
INSERT INTO `lng_data` VALUES ('common', 'feedback', 'en', 'Feedback');
INSERT INTO `lng_data` VALUES ('common', 'feedback_recipient', 'en', 'Feedback Recipient');
INSERT INTO `lng_data` VALUES ('common', 'file', 'en', 'File');
INSERT INTO `lng_data` VALUES ('common', 'file_a', 'en', 'a File');
INSERT INTO `lng_data` VALUES ('common', 'file_add', 'en', 'Upload File');
INSERT INTO `lng_data` VALUES ('common', 'file_added', 'en', 'File uploaded');
INSERT INTO `lng_data` VALUES ('common', 'file_new', 'en', 'New File');
INSERT INTO `lng_data` VALUES ('common', 'file_not_found', 'en', 'File Not Found');
INSERT INTO `lng_data` VALUES ('common', 'file_not_valid', 'en', 'File not valid!');
INSERT INTO `lng_data` VALUES ('common', 'file_valid', 'en', 'File is valid!');
INSERT INTO `lng_data` VALUES ('common', 'file_version', 'en', 'Version Provided in File');
INSERT INTO `lng_data` VALUES ('common', 'files_location', 'en', 'Files Location');
INSERT INTO `lng_data` VALUES ('common', 'fill_out_all_required_fields', 'en', 'Please fill out all required fields');
INSERT INTO `lng_data` VALUES ('common', 'firstname', 'en', 'First name');
INSERT INTO `lng_data` VALUES ('common', 'flatview', 'en', 'Flat View');
INSERT INTO `lng_data` VALUES ('common', 'fold', 'en', 'Folder');
INSERT INTO `lng_data` VALUES ('common', 'fold_a', 'en', 'a Folder');
INSERT INTO `lng_data` VALUES ('common', 'fold_add', 'en', 'Add Folder');
INSERT INTO `lng_data` VALUES ('common', 'fold_added', 'en', 'Folder added');
INSERT INTO `lng_data` VALUES ('common', 'fold_new', 'en', 'New Folder');
INSERT INTO `lng_data` VALUES ('common', 'folder', 'en', 'Folder');
INSERT INTO `lng_data` VALUES ('common', 'folders', 'en', 'Folders');
INSERT INTO `lng_data` VALUES ('common', 'force_accept_usr_agreement', 'en', 'You have to accept user agreement!');
INSERT INTO `lng_data` VALUES ('common', 'forename', 'en', 'Forename');
INSERT INTO `lng_data` VALUES ('common', 'form_empty_fields', 'en', 'Please complete these fields:');
INSERT INTO `lng_data` VALUES ('common', 'forum', 'en', 'Forum');
INSERT INTO `lng_data` VALUES ('common', 'forum_notification', 'en', 'Notification');
INSERT INTO `lng_data` VALUES ('common', 'forum_notify_me', 'en', 'Notify me when a reply is posted');
INSERT INTO `lng_data` VALUES ('common', 'forum_post_replied', 'en', 'Your forum entry has been replied.');
INSERT INTO `lng_data` VALUES ('common', 'forums', 'en', 'Forums');
INSERT INTO `lng_data` VALUES ('common', 'forums_overview', 'en', 'Forums Overview');
INSERT INTO `lng_data` VALUES ('common', 'frm', 'en', 'Forum');
INSERT INTO `lng_data` VALUES ('common', 'frm_a', 'en', 'a Forum');
INSERT INTO `lng_data` VALUES ('common', 'frm_add', 'en', 'Add Forum');
INSERT INTO `lng_data` VALUES ('common', 'frm_added', 'en', 'Forum added');
INSERT INTO `lng_data` VALUES ('common', 'frm_create', 'en', 'Create Forum');
INSERT INTO `lng_data` VALUES ('common', 'frm_delete', 'en', 'Delete Forum');
INSERT INTO `lng_data` VALUES ('common', 'frm_delete_post', 'en', 'Delete a post');
INSERT INTO `lng_data` VALUES ('common', 'frm_edit', 'en', 'Edit Forum');
INSERT INTO `lng_data` VALUES ('common', 'frm_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'frm_edit_post', 'en', 'Edit a post');
INSERT INTO `lng_data` VALUES ('common', 'frm_new', 'en', 'New Forum');
INSERT INTO `lng_data` VALUES ('common', 'frm_read', 'en', 'Read access to Forum');
INSERT INTO `lng_data` VALUES ('common', 'frm_visible', 'en', 'Forum is visible');
INSERT INTO `lng_data` VALUES ('common', 'frm_write', 'en', 'Edit forum/write post');
INSERT INTO `lng_data` VALUES ('common', 'from', 'en', 'From');
INSERT INTO `lng_data` VALUES ('common', 'fullname', 'en', 'Full name');
INSERT INTO `lng_data` VALUES ('common', 'functions', 'en', 'Functions');
INSERT INTO `lng_data` VALUES ('common', 'gdf_add', 'en', 'Add Definition');
INSERT INTO `lng_data` VALUES ('common', 'gdf_new', 'en', 'New Definition');
INSERT INTO `lng_data` VALUES ('common', 'gender', 'en', 'Gender');
INSERT INTO `lng_data` VALUES ('common', 'gender_f', 'en', 'Female');
INSERT INTO `lng_data` VALUES ('common', 'gender_m', 'en', 'Male');
INSERT INTO `lng_data` VALUES ('common', 'generate', 'en', 'Generate');
INSERT INTO `lng_data` VALUES ('common', 'glo', 'en', 'Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glo_a', 'en', 'a Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glo_add', 'en', 'Add Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glo_added', 'en', 'Glossary added');
INSERT INTO `lng_data` VALUES ('common', 'glo_create', 'en', 'Create Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glo_delete', 'en', 'Delete Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glo_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'glo_join', 'en', 'Subscribe to Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glo_leave', 'en', 'Unsubscribe from Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glo_new', 'en', 'New Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glo_read', 'en', 'Read access to Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glo_visible', 'en', 'Glossary is visible');
INSERT INTO `lng_data` VALUES ('common', 'glo_write', 'en', 'Edit Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glossaries', 'en', 'Glossaries');
INSERT INTO `lng_data` VALUES ('common', 'glossary', 'en', 'Glossary');
INSERT INTO `lng_data` VALUES ('common', 'glossary_added', 'en', 'Glossary added');
INSERT INTO `lng_data` VALUES ('common', 'group_access_denied', 'en', 'Access Denied');
INSERT INTO `lng_data` VALUES ('common', 'group_any_objects', 'en', 'No Subobjects available');
INSERT INTO `lng_data` VALUES ('common', 'group_create_chat', 'en', 'Create chat');
INSERT INTO `lng_data` VALUES ('common', 'group_desc', 'en', 'Group Description');
INSERT INTO `lng_data` VALUES ('common', 'group_details', 'en', 'Group Details');
INSERT INTO `lng_data` VALUES ('common', 'group_filesharing', 'en', 'Group File Sharing');
INSERT INTO `lng_data` VALUES ('common', 'group_members', 'en', 'Group Members');
INSERT INTO `lng_data` VALUES ('common', 'group_memstat', 'en', 'Member Status');
INSERT INTO `lng_data` VALUES ('common', 'group_memstat_admin', 'en', 'Administrator');
INSERT INTO `lng_data` VALUES ('common', 'group_memstat_member', 'en', 'Member');
INSERT INTO `lng_data` VALUES ('common', 'group_name', 'en', 'Groupname');
INSERT INTO `lng_data` VALUES ('common', 'group_new_registrations', 'en', 'New Registrations');
INSERT INTO `lng_data` VALUES ('common', 'group_no_registration', 'en', 'no registration required');
INSERT INTO `lng_data` VALUES ('common', 'group_no_registration_msg', 'en', 'You are not a member of this group so far. Due to reasons of administration it is necessary to join the requested group.<br> As a member you have the following advantages:<br>- You get informed about news and updates<br>- You can access the according objects like forums, learning modules, etc.<br><br>You can annul your membership at any time.');
INSERT INTO `lng_data` VALUES ('common', 'group_not_available', 'en', 'No group available');
INSERT INTO `lng_data` VALUES ('common', 'group_objects', 'en', 'Group Objects');
INSERT INTO `lng_data` VALUES ('common', 'group_password_registration_expired_msg', 'en', 'The period of registration of this group is expired, an announcement is no longer possible. Please contact the according group owner or administrator.');
INSERT INTO `lng_data` VALUES ('common', 'group_password_registration_msg', 'en', 'To join this group you have to enter the password provided by the adminstrator of this group.<br>You are automatically assigned to the group if your password is correct.');
INSERT INTO `lng_data` VALUES ('common', 'group_registration_expiration_date', 'en', 'Expiration Date');
INSERT INTO `lng_data` VALUES ('common', 'group_registration_expiration_time', 'en', 'Expiration Time');
INSERT INTO `lng_data` VALUES ('common', 'group_registration_expired', 'en', 'Period of registraton expired');
INSERT INTO `lng_data` VALUES ('common', 'group_registration_mode', 'en', 'Registration Mode');
INSERT INTO `lng_data` VALUES ('common', 'group_req_password', 'en', 'Registration password required');
INSERT INTO `lng_data` VALUES ('common', 'group_req_registration', 'en', 'Registration required');
INSERT INTO `lng_data` VALUES ('common', 'group_req_registration_msg', 'en', 'To join this group you have to register. The according group administrator will assign you to the group. Please enter a subject to give reasons to your application.<br>You will receive a message when you get assigned to the group.');
INSERT INTO `lng_data` VALUES ('common', 'group_reset', 'en', 'Reset Group Permissions');
INSERT INTO `lng_data` VALUES ('common', 'group_status', 'en', 'Group Status');
INSERT INTO `lng_data` VALUES ('common', 'group_status_closed', 'en', 'Closed');
INSERT INTO `lng_data` VALUES ('common', 'group_status_public', 'en', 'Public');
INSERT INTO `lng_data` VALUES ('common', 'groups', 'en', 'Groups');
INSERT INTO `lng_data` VALUES ('common', 'groups_overview', 'en', 'Groups Overview');
INSERT INTO `lng_data` VALUES ('common', 'grp', 'en', 'Group');
INSERT INTO `lng_data` VALUES ('common', 'grp_a', 'en', 'a Group');
INSERT INTO `lng_data` VALUES ('common', 'grp_add', 'en', 'Add Group');
INSERT INTO `lng_data` VALUES ('common', 'grp_added', 'en', 'Group added');
INSERT INTO `lng_data` VALUES ('common', 'grp_create', 'en', 'Create Group');
INSERT INTO `lng_data` VALUES ('common', 'grp_create_chat', 'en', 'Create Chat');
INSERT INTO `lng_data` VALUES ('common', 'grp_create_file', 'en', 'User may upload Files');
INSERT INTO `lng_data` VALUES ('common', 'grp_create_fold', 'en', 'User may create Folders');
INSERT INTO `lng_data` VALUES ('common', 'grp_create_frm', 'en', 'Create Forum');
INSERT INTO `lng_data` VALUES ('common', 'grp_create_lm', 'en', 'Create Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'grp_create_slm', 'en', 'Create SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'grp_delete', 'en', 'Delete Group');
INSERT INTO `lng_data` VALUES ('common', 'grp_dismiss_member', 'en', 'Are you sure you want to dismiss the following member(s) from the group.');
INSERT INTO `lng_data` VALUES ('common', 'grp_dismiss_myself', 'en', 'Are you sure you want to yourself from the group.');
INSERT INTO `lng_data` VALUES ('common', 'grp_edit', 'en', 'Edit Group');
INSERT INTO `lng_data` VALUES ('common', 'grp_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'grp_err_administrator_required', 'en', 'Member could not be removed, at least one administrator per group is required !');
INSERT INTO `lng_data` VALUES ('common', 'grp_err_at_least_one_groupadministrator_is_needed', 'en', 'Each group needs at least one group administrator');
INSERT INTO `lng_data` VALUES ('common', 'grp_err_error', 'en', 'An error occured');
INSERT INTO `lng_data` VALUES ('common', 'grp_err_last_member', 'en', 'Last member could not be removed, please delete group instead.');
INSERT INTO `lng_data` VALUES ('common', 'grp_err_member_could_not_be_removed', 'en', 'Member could not be removed. Please verify all dependencies of this user.');
INSERT INTO `lng_data` VALUES ('common', 'grp_err_no_permission', 'en', 'You do not possess the permissions for this operation.');
INSERT INTO `lng_data` VALUES ('common', 'grp_err_registration_data', 'en', 'Please enter a password, the expiration date and time for a valid registration period !');
INSERT INTO `lng_data` VALUES ('common', 'grp_join', 'en', 'User may join Group');
INSERT INTO `lng_data` VALUES ('common', 'grp_leave', 'en', 'User may leave Group');
INSERT INTO `lng_data` VALUES ('common', 'grp_mem_change_status', 'en', 'Change member status');
INSERT INTO `lng_data` VALUES ('common', 'grp_mem_leave', 'en', 'Dismiss member from group');
INSERT INTO `lng_data` VALUES ('common', 'grp_mem_send_mail', 'en', 'Send member a message');
INSERT INTO `lng_data` VALUES ('common', 'grp_msg_membership_annulled', 'en', 'Membership annulled');
INSERT INTO `lng_data` VALUES ('common', 'grp_name_exists', 'en', 'There is already a group with this name! Please choose another name.');
INSERT INTO `lng_data` VALUES ('common', 'grp_new', 'en', 'New Group');
INSERT INTO `lng_data` VALUES ('common', 'grp_read', 'en', 'Read access to Group');
INSERT INTO `lng_data` VALUES ('common', 'grp_register', 'en', 'Register');
INSERT INTO `lng_data` VALUES ('common', 'grp_registration_completed', 'en', 'Registration completed');
INSERT INTO `lng_data` VALUES ('common', 'grp_visible', 'en', 'Group is visible');
INSERT INTO `lng_data` VALUES ('common', 'grp_write', 'en', 'Edit Group');
INSERT INTO `lng_data` VALUES ('common', 'guest', 'en', 'Guest');
INSERT INTO `lng_data` VALUES ('common', 'guests', 'en', 'Guests');
INSERT INTO `lng_data` VALUES ('common', 'help', 'en', 'Help');
INSERT INTO `lng_data` VALUES ('common', 'hide_details', 'en', 'hide details');
INSERT INTO `lng_data` VALUES ('common', 'hide_structure', 'en', 'Disable Structured-View');
INSERT INTO `lng_data` VALUES ('common', 'hobby', 'en', 'Interests/Hobbies');
INSERT INTO `lng_data` VALUES ('common', 'home', 'en', 'Home');
INSERT INTO `lng_data` VALUES ('common', 'host', 'en', 'Host');
INSERT INTO `lng_data` VALUES ('common', 'http_path', 'en', 'Http Path');
INSERT INTO `lng_data` VALUES ('common', 'id', 'en', 'ID');
INSERT INTO `lng_data` VALUES ('common', 'identifier', 'en', 'identifier');
INSERT INTO `lng_data` VALUES ('common', 'ilias_version', 'en', 'ILIAS version');
INSERT INTO `lng_data` VALUES ('common', 'image', 'en', 'Image');
INSERT INTO `lng_data` VALUES ('common', 'image_gen_unsucc', 'en', 'Image generation unsuccessfull. Contact your system administration and check convert path.');
INSERT INTO `lng_data` VALUES ('common', 'import', 'en', 'Import');
INSERT INTO `lng_data` VALUES ('common', 'import_lm', 'en', 'Import Learningmodule');
INSERT INTO `lng_data` VALUES ('common', 'import_slm', 'en', 'Import SCORM Package');
INSERT INTO `lng_data` VALUES ('common', 'in', 'en', 'in');
INSERT INTO `lng_data` VALUES ('common', 'in_use', 'en', 'User Language');
INSERT INTO `lng_data` VALUES ('common', 'inbox', 'en', 'Inbox');
INSERT INTO `lng_data` VALUES ('common', 'info_assign_sure', 'en', 'Are you sure you want to assign the following user(s)?');
INSERT INTO `lng_data` VALUES ('common', 'info_delete_sure', 'en', 'Are you sure you want to delete the following object(s)?');
INSERT INTO `lng_data` VALUES ('common', 'info_deleted', 'en', 'Object(s) Deleted');
INSERT INTO `lng_data` VALUES ('common', 'info_trash', 'en', 'Deleted Objects');
INSERT INTO `lng_data` VALUES ('common', 'inform_user_mail', 'en', 'Send email to inform user about changes');
INSERT INTO `lng_data` VALUES ('common', 'information_abbr', 'en', 'Info');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_all_offers', 'en', 'All LE');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_back_to_le', 'en', 'Back to LE');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_afternoon', 'en', 'Good afternoon');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_evening', 'en', 'Good evening');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_morning', 'en', 'Good morning');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_night', 'en', 'Good night');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_hello', 'en', 'Hello');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_info_about_work1', 'en', 'On');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_info_about_work2', 'en', ', you worked here for the last time. You can continue there by clicking here:');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_level_zero', 'en', 'Level up');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_pers_dates', 'en', 'Personal data');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_reg_fo', 'en', 'Registered forums');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_reg_le', 'en', 'Registered LE');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_info1', 'en', 'Here you can change your personal data. Except for the INGMEDIA username and password, you do not have to fill out other fields. Remember that lost passwords can only be returned by e-mail (!).');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_info2', 'en', 'It is not allowed to use special characters (like , ; . = - * + #) in your INGMEDIA username and password. To be allowed to use the INGMEDIA platform, you have to accept the user agreement.');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_info3', 'en', 'Please select one of the links in the side bar.');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_title', 'en', 'Change user data.');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_user_agree', 'en', 'Agreement');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_vis_le', 'en', 'Visited LE');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_visited_le', 'en', 'All LE you have visited so far.');
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_welcome', 'en', ', welcome to your personal desktop!!');
INSERT INTO `lng_data` VALUES ('common', 'inifile', 'en', 'Ini-File');
INSERT INTO `lng_data` VALUES ('common', 'input_error', 'en', 'Input error');
INSERT INTO `lng_data` VALUES ('common', 'insert', 'en', 'Insert');
INSERT INTO `lng_data` VALUES ('common', 'inst_id', 'en', 'Installation ID');
INSERT INTO `lng_data` VALUES ('common', 'inst_info', 'en', 'Installation Info');
INSERT INTO `lng_data` VALUES ('common', 'inst_name', 'en', 'Installation Name');
INSERT INTO `lng_data` VALUES ('common', 'install', 'en', 'Install');
INSERT INTO `lng_data` VALUES ('common', 'installed', 'en', 'Installed');
INSERT INTO `lng_data` VALUES ('common', 'institution', 'en', 'Institution');
INSERT INTO `lng_data` VALUES ('common', 'internal_system', 'en', 'Internal system');
INSERT INTO `lng_data` VALUES ('common', 'ip_address', 'en', 'IP Address');
INSERT INTO `lng_data` VALUES ('common', 'is_already_your', 'en', 'is already your');
INSERT INTO `lng_data` VALUES ('common', 'item', 'en', 'Item');
INSERT INTO `lng_data` VALUES ('common', 'kb', 'en', 'KByte');
INSERT INTO `lng_data` VALUES ('common', 'keywords', 'en', 'Keywords');
INSERT INTO `lng_data` VALUES ('common', 'lang_cz', 'en', 'Czech');
INSERT INTO `lng_data` VALUES ('common', 'lang_dateformat', 'en', 'Y-m-d');
INSERT INTO `lng_data` VALUES ('common', 'lang_de', 'en', 'German');
INSERT INTO `lng_data` VALUES ('common', 'lang_dk', 'en', 'Danish');
INSERT INTO `lng_data` VALUES ('common', 'lang_en', 'en', 'English');
INSERT INTO `lng_data` VALUES ('common', 'lang_es', 'en', 'Spanish');
INSERT INTO `lng_data` VALUES ('common', 'lang_fi', 'en', 'Finnish');
INSERT INTO `lng_data` VALUES ('common', 'lang_fr', 'en', 'French');
INSERT INTO `lng_data` VALUES ('common', 'lang_gr', 'en', 'Greek');
INSERT INTO `lng_data` VALUES ('common', 'lang_id', 'en', 'Indonesian');
INSERT INTO `lng_data` VALUES ('common', 'lang_it', 'en', 'Italian');
INSERT INTO `lng_data` VALUES ('common', 'lang_ja', 'en', 'Japanese');
INSERT INTO `lng_data` VALUES ('common', 'lang_lt', 'en', 'Lithuanian');
INSERT INTO `lng_data` VALUES ('common', 'lang_nl', 'en', 'Dutch');
INSERT INTO `lng_data` VALUES ('common', 'lang_no', 'en', 'Norwegian');
INSERT INTO `lng_data` VALUES ('common', 'lang_path', 'en', 'Language Path');
INSERT INTO `lng_data` VALUES ('common', 'lang_pl', 'en', 'Polish');
INSERT INTO `lng_data` VALUES ('common', 'lang_pt', 'en', 'Portuguese');
INSERT INTO `lng_data` VALUES ('common', 'lang_se', 'en', 'Swedish');
INSERT INTO `lng_data` VALUES ('common', 'lang_sep_decimal', 'en', '.');
INSERT INTO `lng_data` VALUES ('common', 'lang_sep_thousand', 'en', ',');
INSERT INTO `lng_data` VALUES ('common', 'lang_timeformat', 'en', 'H:i:s');
INSERT INTO `lng_data` VALUES ('common', 'lang_ua', 'en', 'Ukrainian');
INSERT INTO `lng_data` VALUES ('common', 'lang_version', 'en', '1');
INSERT INTO `lng_data` VALUES ('common', 'lang_xx', 'en', 'Custom');
INSERT INTO `lng_data` VALUES ('common', 'lang_zh', 'en', 'Simplified Chinese');
INSERT INTO `lng_data` VALUES ('common', 'langfile_found', 'en', 'Language file found');
INSERT INTO `lng_data` VALUES ('common', 'language', 'en', 'Language');
INSERT INTO `lng_data` VALUES ('common', 'language_not_installed', 'en', 'is not installed. Please install that language first');
INSERT INTO `lng_data` VALUES ('common', 'languages', 'en', 'Languages');
INSERT INTO `lng_data` VALUES ('common', 'languages_already_installed', 'en', 'Chosen language(s) are already installed');
INSERT INTO `lng_data` VALUES ('common', 'languages_already_uninstalled', 'en', 'Chosen language(s) are already uninstalled');
INSERT INTO `lng_data` VALUES ('common', 'languages_updated', 'en', 'All installed languages have been updated');
INSERT INTO `lng_data` VALUES ('common', 'last_change', 'en', 'Last Change');
INSERT INTO `lng_data` VALUES ('common', 'last_visit', 'en', 'Last Visit');
INSERT INTO `lng_data` VALUES ('common', 'lastname', 'en', 'Last name');
INSERT INTO `lng_data` VALUES ('common', 'launch', 'en', 'Launch');
INSERT INTO `lng_data` VALUES ('common', 'ldap', 'en', 'LDAP');
INSERT INTO `lng_data` VALUES ('common', 'ldap_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'ldap_read', 'en', 'Read access to LDAP settings');
INSERT INTO `lng_data` VALUES ('common', 'ldap_visible', 'en', 'LDAP settings are visible');
INSERT INTO `lng_data` VALUES ('common', 'ldap_write', 'en', 'Edit LDAP settings');
INSERT INTO `lng_data` VALUES ('common', 'learning_objects', 'en', 'Learning Objects');
INSERT INTO `lng_data` VALUES ('common', 'learning_resources', 'en', 'Learning Resources');
INSERT INTO `lng_data` VALUES ('common', 'level', 'en', 'Level');
INSERT INTO `lng_data` VALUES ('common', 'link', 'en', 'Link');
INSERT INTO `lng_data` VALUES ('common', 'linked_pages', 'en', 'Linked Pages');
INSERT INTO `lng_data` VALUES ('common', 'list_of_pages', 'en', 'Pages List');
INSERT INTO `lng_data` VALUES ('common', 'list_of_questions', 'en', 'Question List');
INSERT INTO `lng_data` VALUES ('common', 'literature', 'en', 'Literature');
INSERT INTO `lng_data` VALUES ('common', 'literature_bookmarks', 'en', 'Literature Bookmarks');
INSERT INTO `lng_data` VALUES ('common', 'lm', 'en', 'Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'lm_a', 'en', 'a Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'lm_add', 'en', 'Add Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'lm_added', 'en', 'Learning Module added');
INSERT INTO `lng_data` VALUES ('common', 'lm_create', 'en', 'Create Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'lm_delete', 'en', 'Delete Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'lm_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'lm_join', 'en', 'Subscribe to Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'lm_leave', 'en', 'Unsubscribe from Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'lm_new', 'en', 'New Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'lm_read', 'en', 'Read access to Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'lm_visible', 'en', 'Learning Module is visible');
INSERT INTO `lng_data` VALUES ('common', 'lm_write', 'en', 'Edit Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'lng', 'en', 'Language');
INSERT INTO `lng_data` VALUES ('common', 'lngf', 'en', 'Languages');
INSERT INTO `lng_data` VALUES ('common', 'lngf_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'lngf_read', 'en', 'Read access to Language settings');
INSERT INTO `lng_data` VALUES ('common', 'lngf_visible', 'en', 'Language settings are visible');
INSERT INTO `lng_data` VALUES ('common', 'lo', 'en', 'Learning Object');
INSERT INTO `lng_data` VALUES ('common', 'lo_available', 'en', 'Overview Learning Modules & Courses');
INSERT INTO `lng_data` VALUES ('common', 'lo_categories', 'en', 'LO Categories');
INSERT INTO `lng_data` VALUES ('common', 'lo_edit', 'en', 'Edit Learning Object');
INSERT INTO `lng_data` VALUES ('common', 'lo_new', 'en', 'New Learning Object');
INSERT INTO `lng_data` VALUES ('common', 'lo_no_content', 'en', 'No Learning Resources Available');
INSERT INTO `lng_data` VALUES ('common', 'lo_other_langs', 'en', 'LO\'s in Other Langauges');
INSERT INTO `lng_data` VALUES ('common', 'lo_overview', 'en', 'LO Overview');
INSERT INTO `lng_data` VALUES ('common', 'locator', 'en', 'Location:');
INSERT INTO `lng_data` VALUES ('common', 'login', 'en', 'Login');
INSERT INTO `lng_data` VALUES ('common', 'login_as', 'en', 'Logged in as');
INSERT INTO `lng_data` VALUES ('common', 'login_data', 'en', 'Login data');
INSERT INTO `lng_data` VALUES ('common', 'login_exists', 'en', 'There is already a user with this loginname! Please choose another one.');
INSERT INTO `lng_data` VALUES ('common', 'login_time', 'en', 'Time online');
INSERT INTO `lng_data` VALUES ('common', 'login_to_ilias', 'en', 'Login to ILIAS');
INSERT INTO `lng_data` VALUES ('common', 'logout', 'en', 'Logout');
INSERT INTO `lng_data` VALUES ('common', 'logout_text', 'en', 'You logged off from ILIAS. Your session has been closed.');
INSERT INTO `lng_data` VALUES ('common', 'los', 'en', 'Learning Objects');
INSERT INTO `lng_data` VALUES ('common', 'los_last_visited', 'en', 'Last Visited Learning Objects');
INSERT INTO `lng_data` VALUES ('common', 'mail', 'en', 'Mail');
INSERT INTO `lng_data` VALUES ('common', 'mail_a_root', 'en', 'Mailbox');
INSERT INTO `lng_data` VALUES ('common', 'mail_addressbook', 'en', 'Addressbook');
INSERT INTO `lng_data` VALUES ('common', 'mail_allow_smtp', 'en', 'Allow SMTP');
INSERT INTO `lng_data` VALUES ('common', 'mail_b_inbox', 'en', 'Inbox');
INSERT INTO `lng_data` VALUES ('common', 'mail_c_trash', 'en', 'Trash');
INSERT INTO `lng_data` VALUES ('common', 'mail_d_drafts', 'en', 'Drafts');
INSERT INTO `lng_data` VALUES ('common', 'mail_delete_error', 'en', 'Error while deleting');
INSERT INTO `lng_data` VALUES ('common', 'mail_delete_error_file', 'en', 'Error deleting file');
INSERT INTO `lng_data` VALUES ('common', 'mail_e_sent', 'en', 'Sent');
INSERT INTO `lng_data` VALUES ('common', 'mail_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'mail_folders', 'en', 'Mail Folders');
INSERT INTO `lng_data` VALUES ('common', 'mail_intern_enable', 'en', 'Enable');
INSERT INTO `lng_data` VALUES ('common', 'mail_mails_of', 'en', 'Mails');
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_attach', 'en', 'Max. attachment size');
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_box', 'en', 'Max. mailbox size');
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_mail', 'en', 'Max. mail size');
INSERT INTO `lng_data` VALUES ('common', 'mail_maxtime_attach', 'en', 'Max. days to keep attachments');
INSERT INTO `lng_data` VALUES ('common', 'mail_maxtime_mail', 'en', 'Max. days to keep mails');
INSERT INTO `lng_data` VALUES ('common', 'mail_not_sent', 'en', 'Mail not sent!');
INSERT INTO `lng_data` VALUES ('common', 'mail_read', 'en', 'Read access to Mail settings');
INSERT INTO `lng_data` VALUES ('common', 'mail_search_no', 'en', 'No entries found');
INSERT INTO `lng_data` VALUES ('common', 'mail_search_word', 'en', 'Search word');
INSERT INTO `lng_data` VALUES ('common', 'mail_select_one', 'en', 'You have to select one mail');
INSERT INTO `lng_data` VALUES ('common', 'mail_send_error', 'en', 'Error sending mail');
INSERT INTO `lng_data` VALUES ('common', 'mail_sent', 'en', 'Mail sent!');
INSERT INTO `lng_data` VALUES ('common', 'mail_smtp_mail', 'en', 'User may send mail via SMTP');
INSERT INTO `lng_data` VALUES ('common', 'mail_system', 'en', 'System Message');
INSERT INTO `lng_data` VALUES ('common', 'mail_system_message', 'en', 'User may send internal system messages');
INSERT INTO `lng_data` VALUES ('common', 'mail_visible', 'en', 'Mail settings are visible');
INSERT INTO `lng_data` VALUES ('common', 'mail_write', 'en', 'Edit Mail settings');
INSERT INTO `lng_data` VALUES ('common', 'mail_z_local', 'en', 'User Folders');
INSERT INTO `lng_data` VALUES ('common', 'mails', 'en', 'Mails');
INSERT INTO `lng_data` VALUES ('common', 'mark_all_read', 'en', 'Mark All as Read');
INSERT INTO `lng_data` VALUES ('common', 'mark_all_unread', 'en', 'Mark All as Unread');
INSERT INTO `lng_data` VALUES ('common', 'member', 'en', 'Member');
INSERT INTO `lng_data` VALUES ('common', 'members', 'en', 'Members');
INSERT INTO `lng_data` VALUES ('common', 'membership_annulled', 'en', 'Your membership has been cancelled');
INSERT INTO `lng_data` VALUES ('common', 'message', 'en', 'Message');
INSERT INTO `lng_data` VALUES ('common', 'message_content', 'en', 'Message Content');
INSERT INTO `lng_data` VALUES ('common', 'message_to', 'en', 'Message to:');
INSERT INTO `lng_data` VALUES ('common', 'meta_data', 'en', 'Meta Data');
INSERT INTO `lng_data` VALUES ('common', 'migrate', 'en', 'Migrate');
INSERT INTO `lng_data` VALUES ('common', 'mob', 'en', 'Media Object');
INSERT INTO `lng_data` VALUES ('common', 'module', 'en', 'module');
INSERT INTO `lng_data` VALUES ('common', 'modules', 'en', 'Modules');
INSERT INTO `lng_data` VALUES ('common', 'month', 'en', 'Month');
INSERT INTO `lng_data` VALUES ('common', 'move', 'en', 'Move');
INSERT INTO `lng_data` VALUES ('common', 'moveChapter', 'en', 'Move');
INSERT INTO `lng_data` VALUES ('common', 'movePage', 'en', 'Move');
INSERT INTO `lng_data` VALUES ('common', 'move_to', 'en', 'Move to');
INSERT INTO `lng_data` VALUES ('common', 'msg_cancel', 'en', 'Action cancelled');
INSERT INTO `lng_data` VALUES ('common', 'msg_changes_ok', 'en', 'The changes were OK');
INSERT INTO `lng_data` VALUES ('common', 'msg_clear_clipboard', 'en', 'Clipboard cleared');
INSERT INTO `lng_data` VALUES ('common', 'msg_cloned', 'en', 'Selected object(s) copied');
INSERT INTO `lng_data` VALUES ('common', 'msg_copy_clipboard', 'en', 'Selected object(s) stored in clipboard (Action: copy)');
INSERT INTO `lng_data` VALUES ('common', 'msg_cut_clipboard', 'en', 'Selected object(s) stored in clipboard (Action: cut)');
INSERT INTO `lng_data` VALUES ('common', 'msg_cut_copied', 'en', 'Selected object(s) moved.');
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_role', 'en', 'Role deleted');
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_roles', 'en', 'Roles deleted');
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_roles_rolts', 'en', 'Roles & Role Templates deleted');
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_rolt', 'en', 'Role Template deleted');
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_rolts', 'en', 'Role Template deleted');
INSERT INTO `lng_data` VALUES ('common', 'msg_error_copy', 'en', 'Copy Error');
INSERT INTO `lng_data` VALUES ('common', 'msg_failed', 'en', 'Sorry, action failed');
INSERT INTO `lng_data` VALUES ('common', 'msg_is_last_role', 'en', 'You deassigned the following users from their last global role');
INSERT INTO `lng_data` VALUES ('common', 'msg_link_clipboard', 'en', 'Selected object(s) stored in clipboard (Action: link)');
INSERT INTO `lng_data` VALUES ('common', 'msg_linked', 'en', 'Selected object(s) linked.');
INSERT INTO `lng_data` VALUES ('common', 'msg_may_not_contain', 'en', 'This object may not contain objects of type:');
INSERT INTO `lng_data` VALUES ('common', 'msg_min_one_active_role', 'en', 'Each user must have at least one active global role!');
INSERT INTO `lng_data` VALUES ('common', 'msg_min_one_role', 'en', 'Each user must have at least one global role!');
INSERT INTO `lng_data` VALUES ('common', 'msg_multi_language_selected', 'en', 'You selected the same language for different translations!');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_default_language', 'en', 'No default language specified! You must define one translation as default translation.');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_delete_yourself', 'en', 'You cannot delete your own user account.');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_language_selected', 'en', 'No translation language specified! You must define a language for each translation');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_assign_role_to_user', 'en', 'You have no permission to change user\'s roleassignment');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_copy', 'en', 'You have no permission to create a copy of the following object(s):');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create', 'en', 'You have no permission to create the following object(s):');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_object1', 'en', 'You have no permission to create');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_object2', 'en', 'at this location!');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_role', 'en', 'You have no permission to add roles');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_rolf', 'en', 'You have no permission to create a Role Folder. Therefore you may not stop inheritance of roles or add local roles here.');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_rolt', 'en', 'You have no permission to add role templates');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_user', 'en', 'You have no permission to add users');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_cut', 'en', 'You have no permission to cut the following object(s):');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_delete', 'en', 'You have no permission to delete the following object(s):');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_link', 'en', 'You have no permission to create a link from the following object(s):');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_role', 'en', 'You have no permission to modify roles');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_rolt', 'en', 'You have no permission to modify role templates');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_user', 'en', 'You have no permission to modify user datas');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_paste', 'en', 'You have no permission to paste the following object(s):');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_perm', 'en', 'You have no permission to edit permission settings');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_undelete', 'en', 'You have no permission to undelete the following object(s):');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_write', 'en', 'You have no permission to write');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_rolf_allowed1', 'en', 'The object');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_rolf_allowed2', 'en', 'is not allowed to contain a Role Folder');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_search_result', 'en', 'No entries found');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_search_string', 'en', 'Please enter your query');
INSERT INTO `lng_data` VALUES ('common', 'msg_no_sysadmin_sysrole_not_assignable', 'en', 'You may not assign users to the Systemadministrator Role if you are not assigned yourself to that Role');
INSERT INTO `lng_data` VALUES ('common', 'msg_not_available_for_anon', 'en', 'The page you have chosen is only accessible for registered users');
INSERT INTO `lng_data` VALUES ('common', 'msg_not_in_itself', 'en', 'It\'s not possible to paste the object in itself');
INSERT INTO `lng_data` VALUES ('common', 'msg_nothing_found', 'en', 'msg_nothing_found');
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_created', 'en', 'Object created.');
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_exists', 'en', 'This object already exists in this folder');
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_modified', 'en', 'Modifications saved.');
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_no_link', 'en', 'are not allowed to be linked');
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from1', 'en', 'Permission settings adopted from');
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from2', 'en', '(Settings have been saved!)');
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from_itself', 'en', 'You cannot adopt permission settings from the current role/role template itself.');
INSERT INTO `lng_data` VALUES ('common', 'msg_removed', 'en', 'Object(s) removed from system.');
INSERT INTO `lng_data` VALUES ('common', 'msg_role_exists1', 'en', 'A role/role template with the name');
INSERT INTO `lng_data` VALUES ('common', 'msg_role_exists2', 'en', 'already exists! Please choose another name');
INSERT INTO `lng_data` VALUES ('common', 'msg_role_reserved_prefix', 'en', 'The prefix \'il_\' is reserved for autogenerated roles. Please choose another name');
INSERT INTO `lng_data` VALUES ('common', 'msg_roleassignment_active_changed', 'en', 'Active Roleassigment changed');
INSERT INTO `lng_data` VALUES ('common', 'msg_roleassignment_active_changed_comment', 'en', 'This settings is not saved to the user\'s profile! If the user re-login all active role assignments reset to saved values again.');
INSERT INTO `lng_data` VALUES ('common', 'msg_roleassignment_changed', 'en', 'Roleassigment changed');
INSERT INTO `lng_data` VALUES ('common', 'msg_sysrole_not_deletable', 'en', 'The systemrole cannot be deleted');
INSERT INTO `lng_data` VALUES ('common', 'msg_sysrole_not_editable', 'en', 'The permission settings of the system role may not be changed. The system role grants all assigned users unlimited access to all objects & functions.');
INSERT INTO `lng_data` VALUES ('common', 'msg_trash_empty', 'en', 'There are no deleted objects');
INSERT INTO `lng_data` VALUES ('common', 'msg_undeleted', 'en', 'Object(s) undeleted.');
INSERT INTO `lng_data` VALUES ('common', 'msg_user_last_role1', 'en', 'The following users are assigned to this role only:');
INSERT INTO `lng_data` VALUES ('common', 'msg_user_last_role2', 'en', 'Please delete the users or assign them to another role in order to delete this role.');
INSERT INTO `lng_data` VALUES ('common', 'msg_userassignment_changed', 'en', 'Userassigment changed');
INSERT INTO `lng_data` VALUES ('common', 'multimedia', 'en', 'Multimedia');
INSERT INTO `lng_data` VALUES ('common', 'my_bms', 'en', 'My Bookmarks');
INSERT INTO `lng_data` VALUES ('common', 'my_frms', 'en', 'My Forums');
INSERT INTO `lng_data` VALUES ('common', 'my_grps', 'en', 'My Groups');
INSERT INTO `lng_data` VALUES ('common', 'my_los', 'en', 'My Learning Objects');
INSERT INTO `lng_data` VALUES ('common', 'name', 'en', 'Name');
INSERT INTO `lng_data` VALUES ('common', 'new', 'en', 'New');
INSERT INTO `lng_data` VALUES ('common', 'new_appointment', 'en', 'New Appointment');
INSERT INTO `lng_data` VALUES ('common', 'new_folder', 'en', 'New Folder');
INSERT INTO `lng_data` VALUES ('common', 'new_group', 'en', 'New Group');
INSERT INTO `lng_data` VALUES ('common', 'new_language', 'en', 'New Language');
INSERT INTO `lng_data` VALUES ('common', 'new_mail', 'en', 'New mail!');
INSERT INTO `lng_data` VALUES ('common', 'news', 'en', 'News');
INSERT INTO `lng_data` VALUES ('common', 'next', 'en', 'next');
INSERT INTO `lng_data` VALUES ('common', 'nickname', 'en', 'Nickname');
INSERT INTO `lng_data` VALUES ('common', 'no', 'en', 'No');
INSERT INTO `lng_data` VALUES ('common', 'no_bm_in_personal_list', 'en', 'No bookmarks defined.');
INSERT INTO `lng_data` VALUES ('common', 'no_checkbox', 'en', 'No checkbox checked!');
INSERT INTO `lng_data` VALUES ('common', 'no_date', 'en', 'No date');
INSERT INTO `lng_data` VALUES ('common', 'no_frm_in_personal_list', 'en', 'No forums in personal list.');
INSERT INTO `lng_data` VALUES ('common', 'no_grp_in_personal_list', 'en', 'No groups in personal list.');
INSERT INTO `lng_data` VALUES ('common', 'no_import_available', 'en', 'Import not available for type');
INSERT INTO `lng_data` VALUES ('common', 'no_lo_in_personal_list', 'en', 'No learning objects in personal list.');
INSERT INTO `lng_data` VALUES ('common', 'no_objects', 'en', 'No objects');
INSERT INTO `lng_data` VALUES ('common', 'no_permission_to_join', 'en', 'You are not allowed to join this group!');
INSERT INTO `lng_data` VALUES ('common', 'no_title', 'en', 'No Title');
INSERT INTO `lng_data` VALUES ('common', 'none', 'en', 'None');
INSERT INTO `lng_data` VALUES ('common', 'normal', 'en', 'Normal');
INSERT INTO `lng_data` VALUES ('common', 'not_installed', 'en', 'Not Installed');
INSERT INTO `lng_data` VALUES ('common', 'not_logged_in', 'en', 'Your are not logged in');
INSERT INTO `lng_data` VALUES ('common', 'obj', 'en', 'Object');
INSERT INTO `lng_data` VALUES ('common', 'obj_adm', 'en', 'System Settings');
INSERT INTO `lng_data` VALUES ('common', 'obj_adm_desc', 'en', 'Main system settings folder containing all panels to administrate your ILIAS installation.');
INSERT INTO `lng_data` VALUES ('common', 'obj_cat', 'en', 'Category');
INSERT INTO `lng_data` VALUES ('common', 'obj_chac', 'en', 'Chat settings');
INSERT INTO `lng_data` VALUES ('common', 'obj_chat', 'en', 'Chat');
INSERT INTO `lng_data` VALUES ('common', 'obj_crs', 'en', 'Course');
INSERT INTO `lng_data` VALUES ('common', 'obj_dbk', 'en', 'Digilib Book');
INSERT INTO `lng_data` VALUES ('common', 'obj_exc', 'en', 'Exercise');
INSERT INTO `lng_data` VALUES ('common', 'obj_file', 'en', 'File');
INSERT INTO `lng_data` VALUES ('common', 'obj_fold', 'en', 'Folder');
INSERT INTO `lng_data` VALUES ('common', 'obj_frm', 'en', 'Forum');
INSERT INTO `lng_data` VALUES ('common', 'obj_glo', 'en', 'Glossary');
INSERT INTO `lng_data` VALUES ('common', 'obj_grp', 'en', 'Group');
INSERT INTO `lng_data` VALUES ('common', 'obj_ldap', 'en', 'LDAP Settings');
INSERT INTO `lng_data` VALUES ('common', 'obj_ldap_desc', 'en', 'Configure global LDAP Settings here.');
INSERT INTO `lng_data` VALUES ('common', 'obj_lm', 'en', 'Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'obj_lng', 'en', 'Language');
INSERT INTO `lng_data` VALUES ('common', 'obj_lngf', 'en', 'Languages');
INSERT INTO `lng_data` VALUES ('common', 'obj_lngf_desc', 'en', 'Manage your system languages here.');
INSERT INTO `lng_data` VALUES ('common', 'obj_lo', 'en', 'LearningObject');
INSERT INTO `lng_data` VALUES ('common', 'obj_mail', 'en', 'Mail Settings');
INSERT INTO `lng_data` VALUES ('common', 'obj_mail_desc', 'en', 'Configure global mail settings here.');
INSERT INTO `lng_data` VALUES ('common', 'obj_mob', 'en', 'Multimedia Object');
INSERT INTO `lng_data` VALUES ('common', 'obj_not_found', 'en', 'Object Not Found');
INSERT INTO `lng_data` VALUES ('common', 'obj_note', 'en', 'Note');
INSERT INTO `lng_data` VALUES ('common', 'obj_notf', 'en', 'Note Administration');
INSERT INTO `lng_data` VALUES ('common', 'obj_objf', 'en', 'Objectdefinitions');
INSERT INTO `lng_data` VALUES ('common', 'obj_objf_desc', 'en', 'Manage ILIAS object types and object permissions. (only for experts!)');
INSERT INTO `lng_data` VALUES ('common', 'obj_owner', 'en', 'This Object is owned by');
INSERT INTO `lng_data` VALUES ('common', 'obj_pg', 'en', 'Page');
INSERT INTO `lng_data` VALUES ('common', 'obj_qpl', 'en', 'Question Pool');
INSERT INTO `lng_data` VALUES ('common', 'obj_role', 'en', 'Role');
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf', 'en', 'Roles');
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf_desc', 'en', 'Manage your roles here.');
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf_local', 'en', 'Local Roles');
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf_local_desc', 'en', 'Contains local roles of object no.');
INSERT INTO `lng_data` VALUES ('common', 'obj_rolt', 'en', 'Role Template');
INSERT INTO `lng_data` VALUES ('common', 'obj_root', 'en', 'ILIAS root node');
INSERT INTO `lng_data` VALUES ('common', 'obj_slm', 'en', 'SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'obj_st', 'en', 'Chapter');
INSERT INTO `lng_data` VALUES ('common', 'obj_tst', 'en', 'Test');
INSERT INTO `lng_data` VALUES ('common', 'obj_typ', 'en', 'Objecttype Definition');
INSERT INTO `lng_data` VALUES ('common', 'obj_type', 'en', 'Object Type');
INSERT INTO `lng_data` VALUES ('common', 'obj_uset', 'en', 'User Settings');
INSERT INTO `lng_data` VALUES ('common', 'obj_usr', 'en', 'User');
INSERT INTO `lng_data` VALUES ('common', 'obj_usrf', 'en', 'User accounts');
INSERT INTO `lng_data` VALUES ('common', 'obj_usrf_desc', 'en', 'Manage user accounts here.');
INSERT INTO `lng_data` VALUES ('common', 'objects', 'en', 'Objects');
INSERT INTO `lng_data` VALUES ('common', 'objf', 'en', 'Objectdefinitions');
INSERT INTO `lng_data` VALUES ('common', 'objf_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'objf_read', 'en', 'Read access to Objectdefinitions');
INSERT INTO `lng_data` VALUES ('common', 'objf_visible', 'en', 'Objectdefinitions are visible');
INSERT INTO `lng_data` VALUES ('common', 'objf_write', 'en', 'Edit Objectdefinitions');
INSERT INTO `lng_data` VALUES ('common', 'objs_cat', 'en', 'Categories');
INSERT INTO `lng_data` VALUES ('common', 'objs_confirm', 'en', 'Confirm Action');
INSERT INTO `lng_data` VALUES ('common', 'objs_crs', 'en', 'Courses');
INSERT INTO `lng_data` VALUES ('common', 'objs_dbk', 'en', 'Digilib Books');
INSERT INTO `lng_data` VALUES ('common', 'objs_delete', 'en', 'Delete objects');
INSERT INTO `lng_data` VALUES ('common', 'objs_exc', 'en', 'Exersices');
INSERT INTO `lng_data` VALUES ('common', 'objs_file', 'en', 'Files');
INSERT INTO `lng_data` VALUES ('common', 'objs_fold', 'en', 'Folders');
INSERT INTO `lng_data` VALUES ('common', 'objs_frm', 'en', 'Forums');
INSERT INTO `lng_data` VALUES ('common', 'objs_glo', 'en', 'Glossaries');
INSERT INTO `lng_data` VALUES ('common', 'objs_grp', 'en', 'Groups');
INSERT INTO `lng_data` VALUES ('common', 'objs_lm', 'en', 'Learning Modules');
INSERT INTO `lng_data` VALUES ('common', 'objs_lng', 'en', 'Languages');
INSERT INTO `lng_data` VALUES ('common', 'objs_lo', 'en', 'Learning Objects');
INSERT INTO `lng_data` VALUES ('common', 'objs_mob', 'en', 'Multimedia Objects');
INSERT INTO `lng_data` VALUES ('common', 'objs_note', 'en', 'Notes');
INSERT INTO `lng_data` VALUES ('common', 'objs_pg', 'en', 'Pages');
INSERT INTO `lng_data` VALUES ('common', 'objs_qpl', 'en', 'Question Pools');
INSERT INTO `lng_data` VALUES ('common', 'objs_role', 'en', 'Roles');
INSERT INTO `lng_data` VALUES ('common', 'objs_rolt', 'en', 'Role Templates');
INSERT INTO `lng_data` VALUES ('common', 'objs_slm', 'en', 'SCORM Learning Modules');
INSERT INTO `lng_data` VALUES ('common', 'objs_st', 'en', 'Chapters');
INSERT INTO `lng_data` VALUES ('common', 'objs_tst', 'en', 'Tests');
INSERT INTO `lng_data` VALUES ('common', 'objs_type', 'en', 'Objecttypes');
INSERT INTO `lng_data` VALUES ('common', 'objs_usr', 'en', 'Users');
INSERT INTO `lng_data` VALUES ('common', 'of', 'en', 'Of');
INSERT INTO `lng_data` VALUES ('common', 'offline_version', 'en', 'Offline Version');
INSERT INTO `lng_data` VALUES ('common', 'ok', 'en', 'OK');
INSERT INTO `lng_data` VALUES ('common', 'old', 'en', 'Old');
INSERT INTO `lng_data` VALUES ('common', 'online_chapter', 'en', 'Online Chapter');
INSERT INTO `lng_data` VALUES ('common', 'online_version', 'en', 'Online Version');
INSERT INTO `lng_data` VALUES ('common', 'operation', 'en', 'Operation');
INSERT INTO `lng_data` VALUES ('common', 'optimize', 'en', 'Optimize');
INSERT INTO `lng_data` VALUES ('common', 'options', 'en', 'Options');
INSERT INTO `lng_data` VALUES ('common', 'order_by', 'en', 'Order by');
INSERT INTO `lng_data` VALUES ('common', 'other', 'en', 'Other');
INSERT INTO `lng_data` VALUES ('common', 'overview', 'en', 'Overview');
INSERT INTO `lng_data` VALUES ('common', 'owner', 'en', 'Owner');
INSERT INTO `lng_data` VALUES ('common', 'page', 'en', 'Page');
INSERT INTO `lng_data` VALUES ('common', 'page_edit', 'en', 'Edit Page');
INSERT INTO `lng_data` VALUES ('common', 'pages', 'en', 'Pages');
INSERT INTO `lng_data` VALUES ('common', 'parameter', 'en', 'Parameter');
INSERT INTO `lng_data` VALUES ('common', 'parse', 'en', 'Parse');
INSERT INTO `lng_data` VALUES ('common', 'passwd', 'en', 'Password');
INSERT INTO `lng_data` VALUES ('common', 'passwd_invalid', 'en', 'The new password is invalid! Only the following characters are allowed (minimum 6 characters): A-Z a-z 0-9 _.-+*@!$%~');
INSERT INTO `lng_data` VALUES ('common', 'passwd_not_match', 'en', 'Your entries for the new password don\'t match! Please re-enter your new password.');
INSERT INTO `lng_data` VALUES ('common', 'passwd_wrong', 'en', 'The password you entered is wrong!');
INSERT INTO `lng_data` VALUES ('common', 'password', 'en', 'Password');
INSERT INTO `lng_data` VALUES ('common', 'paste', 'en', 'Paste');
INSERT INTO `lng_data` VALUES ('common', 'pasteChapter', 'en', 'Paste');
INSERT INTO `lng_data` VALUES ('common', 'pastePage', 'en', 'Paste');
INSERT INTO `lng_data` VALUES ('common', 'path', 'en', 'Path');
INSERT INTO `lng_data` VALUES ('common', 'path_not_set', 'en', 'Path not set');
INSERT INTO `lng_data` VALUES ('common', 'path_to_babylon', 'en', 'Path to Babylon');
INSERT INTO `lng_data` VALUES ('common', 'path_to_convert', 'en', 'Path to Convert');
INSERT INTO `lng_data` VALUES ('common', 'path_to_htmldoc', 'en', 'Path to HTMLdoc');
INSERT INTO `lng_data` VALUES ('common', 'path_to_java', 'en', 'Path to Java');
INSERT INTO `lng_data` VALUES ('common', 'path_to_unzip', 'en', 'Path to Unzip');
INSERT INTO `lng_data` VALUES ('common', 'path_to_zip', 'en', 'Path to Zip');
INSERT INTO `lng_data` VALUES ('common', 'pathes', 'en', 'Pathes');
INSERT INTO `lng_data` VALUES ('common', 'payment_system', 'en', 'Payment System');
INSERT INTO `lng_data` VALUES ('common', 'perm_settings', 'en', 'Permissions');
INSERT INTO `lng_data` VALUES ('common', 'permission', 'en', 'Permission');
INSERT INTO `lng_data` VALUES ('common', 'permission_denied', 'en', 'Permission Denied');
INSERT INTO `lng_data` VALUES ('common', 'permission_settings', 'en', 'Permission settings');
INSERT INTO `lng_data` VALUES ('common', 'personal_data', 'en', 'Personal information');
INSERT INTO `lng_data` VALUES ('common', 'personal_desktop', 'en', 'Personal Desktop');
INSERT INTO `lng_data` VALUES ('common', 'personal_picture', 'en', 'Personal Picture');
INSERT INTO `lng_data` VALUES ('common', 'personal_profile', 'en', 'Personal Profile');
INSERT INTO `lng_data` VALUES ('common', 'persons', 'en', 'Personen');
INSERT INTO `lng_data` VALUES ('common', 'pg_a', 'en', 'a page');
INSERT INTO `lng_data` VALUES ('common', 'pg_add', 'en', 'Add page');
INSERT INTO `lng_data` VALUES ('common', 'pg_added', 'en', 'Page added');
INSERT INTO `lng_data` VALUES ('common', 'pg_edit', 'en', 'Edit page');
INSERT INTO `lng_data` VALUES ('common', 'pg_new', 'en', 'New page');
INSERT INTO `lng_data` VALUES ('common', 'phone', 'en', 'Phone');
INSERT INTO `lng_data` VALUES ('common', 'phone_home', 'en', 'Phone, Home');
INSERT INTO `lng_data` VALUES ('common', 'phone_mobile', 'en', 'Phone, Mobile');
INSERT INTO `lng_data` VALUES ('common', 'phone_office', 'en', 'Phone, Office');
INSERT INTO `lng_data` VALUES ('common', 'phrase', 'en', 'Phrase');
INSERT INTO `lng_data` VALUES ('common', 'please_enter_target', 'en', 'Please enter a target');
INSERT INTO `lng_data` VALUES ('common', 'please_enter_title', 'en', 'Please enter a title');
INSERT INTO `lng_data` VALUES ('common', 'port', 'en', 'Port');
INSERT INTO `lng_data` VALUES ('common', 'position', 'en', 'Position');
INSERT INTO `lng_data` VALUES ('common', 'presentation_options', 'en', 'Presentation Options');
INSERT INTO `lng_data` VALUES ('common', 'previous', 'en', 'previous');
INSERT INTO `lng_data` VALUES ('common', 'print', 'en', 'Print');
INSERT INTO `lng_data` VALUES ('common', 'profile_changed', 'en', 'Your profile has changed');
INSERT INTO `lng_data` VALUES ('common', 'profile_of', 'en', 'Profile of');
INSERT INTO `lng_data` VALUES ('common', 'properties', 'en', 'Properties');
INSERT INTO `lng_data` VALUES ('common', 'pub_section', 'en', 'Public Section');
INSERT INTO `lng_data` VALUES ('common', 'public_profile', 'en', 'Public Profile');
INSERT INTO `lng_data` VALUES ('common', 'publication', 'en', 'Publication');
INSERT INTO `lng_data` VALUES ('common', 'publication_date', 'en', 'Publication Date');
INSERT INTO `lng_data` VALUES ('common', 'published', 'en', 'Published');
INSERT INTO `lng_data` VALUES ('common', 'publishing_organisation', 'en', 'Publishing Organisation');
INSERT INTO `lng_data` VALUES ('common', 'qpl', 'en', 'Question pool');
INSERT INTO `lng_data` VALUES ('common', 'qpl_add', 'en', 'Add question pool');
INSERT INTO `lng_data` VALUES ('common', 'qpl_delete', 'en', 'Delete Question Pool');
INSERT INTO `lng_data` VALUES ('common', 'qpl_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'qpl_new', 'en', 'New question pool');
INSERT INTO `lng_data` VALUES ('common', 'qpl_read', 'en', 'Read access to Question Pool');
INSERT INTO `lng_data` VALUES ('common', 'qpl_visible', 'en', 'Question Pool is visible');
INSERT INTO `lng_data` VALUES ('common', 'qpl_write', 'en', 'Edit Question Pool');
INSERT INTO `lng_data` VALUES ('common', 'question', 'en', 'Question');
INSERT INTO `lng_data` VALUES ('common', 'question_pools', 'en', 'Question pools');
INSERT INTO `lng_data` VALUES ('common', 'quit', 'en', 'Quit');
INSERT INTO `lng_data` VALUES ('common', 'quote', 'en', 'Quote');
INSERT INTO `lng_data` VALUES ('common', 'read', 'en', 'Read');
INSERT INTO `lng_data` VALUES ('common', 'recipient', 'en', 'Recipient');
INSERT INTO `lng_data` VALUES ('common', 'refresh', 'en', 'Refresh');
INSERT INTO `lng_data` VALUES ('common', 'refresh_languages', 'en', 'Refresh Languages');
INSERT INTO `lng_data` VALUES ('common', 'refresh_list', 'en', 'Refresh List');
INSERT INTO `lng_data` VALUES ('common', 'register', 'en', 'Register');
INSERT INTO `lng_data` VALUES ('common', 'register_info', 'en', 'Please fill out the form to register (Fields marked with an asterisk are required information).');
INSERT INTO `lng_data` VALUES ('common', 'registered_since', 'en', 'Registered since');
INSERT INTO `lng_data` VALUES ('common', 'registered_user', 'en', 'registered User');
INSERT INTO `lng_data` VALUES ('common', 'registered_users', 'en', 'registered Users');
INSERT INTO `lng_data` VALUES ('common', 'registration', 'en', 'New account registration');
INSERT INTO `lng_data` VALUES ('common', 'registration_expired', 'en', 'Registration period expired...');
INSERT INTO `lng_data` VALUES ('common', 'remove', 'en', 'Dismiss');
INSERT INTO `lng_data` VALUES ('common', 'remove_translation', 'en', 'Remove translation');
INSERT INTO `lng_data` VALUES ('common', 'rename', 'en', 'Rename');
INSERT INTO `lng_data` VALUES ('common', 'reply', 'en', 'Reply');
INSERT INTO `lng_data` VALUES ('common', 'repository', 'en', 'Repository');
INSERT INTO `lng_data` VALUES ('common', 'required_field', 'en', 'Required Field');
INSERT INTO `lng_data` VALUES ('common', 'reset', 'en', 'Reset');
INSERT INTO `lng_data` VALUES ('common', 'resources', 'en', 'Resources');
INSERT INTO `lng_data` VALUES ('common', 'retype_password', 'en', 'Retype Password');
INSERT INTO `lng_data` VALUES ('common', 'right', 'en', 'Right');
INSERT INTO `lng_data` VALUES ('common', 'rights', 'en', 'Rights');
INSERT INTO `lng_data` VALUES ('common', 'role', 'en', 'Role');
INSERT INTO `lng_data` VALUES ('common', 'role_a', 'en', 'a Role');
INSERT INTO `lng_data` VALUES ('common', 'role_add', 'en', 'Add Role');
INSERT INTO `lng_data` VALUES ('common', 'role_add_local', 'en', 'Add local Role');
INSERT INTO `lng_data` VALUES ('common', 'role_added', 'en', 'Role added');
INSERT INTO `lng_data` VALUES ('common', 'role_assignment', 'en', 'Role Assignment');
INSERT INTO `lng_data` VALUES ('common', 'role_deleted', 'en', 'Role deleted');
INSERT INTO `lng_data` VALUES ('common', 'role_edit', 'en', 'Edit Role');
INSERT INTO `lng_data` VALUES ('common', 'role_new', 'en', 'New Role');
INSERT INTO `lng_data` VALUES ('common', 'roles', 'en', 'Roles');
INSERT INTO `lng_data` VALUES ('common', 'rolf', 'en', 'Roles');
INSERT INTO `lng_data` VALUES ('common', 'rolf_added', 'en', 'Role Folder added');
INSERT INTO `lng_data` VALUES ('common', 'rolf_create_role', 'en', 'Create new Role definition');
INSERT INTO `lng_data` VALUES ('common', 'rolf_create_rolt', 'en', 'Create new Role definition template');
INSERT INTO `lng_data` VALUES ('common', 'rolf_delete', 'en', 'Delete Roles/Role templates');
INSERT INTO `lng_data` VALUES ('common', 'rolf_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'rolf_edit_userassignment', 'en', 'Change userassignment of Roles');
INSERT INTO `lng_data` VALUES ('common', 'rolf_read', 'en', 'Read access to Roles/Role templates');
INSERT INTO `lng_data` VALUES ('common', 'rolf_visible', 'en', 'Roles/Role templates are visible');
INSERT INTO `lng_data` VALUES ('common', 'rolf_write', 'en', 'Edit default permission settings of Roles/Role templates');
INSERT INTO `lng_data` VALUES ('common', 'rolt', 'en', 'Role Template');
INSERT INTO `lng_data` VALUES ('common', 'rolt_a', 'en', 'a Role Template');
INSERT INTO `lng_data` VALUES ('common', 'rolt_add', 'en', 'Add Role Template');
INSERT INTO `lng_data` VALUES ('common', 'rolt_added', 'en', 'Role template added');
INSERT INTO `lng_data` VALUES ('common', 'rolt_edit', 'en', 'Edit Role Template');
INSERT INTO `lng_data` VALUES ('common', 'rolt_new', 'en', 'New Role Template');
INSERT INTO `lng_data` VALUES ('common', 'root_create_cat', 'en', 'User may create Categories');
INSERT INTO `lng_data` VALUES ('common', 'root_edit', 'en', 'Edit system root node');
INSERT INTO `lng_data` VALUES ('common', 'root_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'root_read', 'en', 'Read access to ILIAS system');
INSERT INTO `lng_data` VALUES ('common', 'root_visible', 'en', 'ILIAS system is visible');
INSERT INTO `lng_data` VALUES ('common', 'root_write', 'en', 'Edit metadata of system root node');
INSERT INTO `lng_data` VALUES ('common', 'salutation', 'en', 'Salutation');
INSERT INTO `lng_data` VALUES ('common', 'salutation_f', 'en', 'Ms./Mrs.');
INSERT INTO `lng_data` VALUES ('common', 'salutation_m', 'en', 'Mr.');
INSERT INTO `lng_data` VALUES ('common', 'save', 'en', 'Save');
INSERT INTO `lng_data` VALUES ('common', 'save_and_back', 'en', 'Save And Back');
INSERT INTO `lng_data` VALUES ('common', 'save_message', 'en', 'Save Message');
INSERT INTO `lng_data` VALUES ('common', 'save_refresh', 'en', 'Save and Refresh');
INSERT INTO `lng_data` VALUES ('common', 'save_return', 'en', 'Save and Return');
INSERT INTO `lng_data` VALUES ('common', 'saved', 'en', 'Saved');
INSERT INTO `lng_data` VALUES ('common', 'saved_successfully', 'en', 'Saved Successfully');
INSERT INTO `lng_data` VALUES ('common', 'search', 'en', 'Search');
INSERT INTO `lng_data` VALUES ('common', 'search_in', 'en', 'Search in');
INSERT INTO `lng_data` VALUES ('common', 'search_new', 'en', 'New Search');
INSERT INTO `lng_data` VALUES ('common', 'search_recipient', 'en', 'Search Recipient');
INSERT INTO `lng_data` VALUES ('common', 'search_result', 'en', 'Search result');
INSERT INTO `lng_data` VALUES ('common', 'search_user', 'en', 'Search User');
INSERT INTO `lng_data` VALUES ('common', 'sections', 'en', 'Sections');
INSERT INTO `lng_data` VALUES ('common', 'select', 'en', 'Select');
INSERT INTO `lng_data` VALUES ('common', 'select_all', 'en', 'Select All');
INSERT INTO `lng_data` VALUES ('common', 'select_file', 'en', 'Select file');
INSERT INTO `lng_data` VALUES ('common', 'select_mode', 'en', 'Select mode');
INSERT INTO `lng_data` VALUES ('common', 'selected', 'en', 'Selected');
INSERT INTO `lng_data` VALUES ('common', 'send', 'en', 'Send');
INSERT INTO `lng_data` VALUES ('common', 'sender', 'en', 'Sender');
INSERT INTO `lng_data` VALUES ('common', 'sent', 'en', 'Sent');
INSERT INTO `lng_data` VALUES ('common', 'sequence', 'en', 'Sequence');
INSERT INTO `lng_data` VALUES ('common', 'sequences', 'en', 'Sequences');
INSERT INTO `lng_data` VALUES ('common', 'server', 'en', 'Server');
INSERT INTO `lng_data` VALUES ('common', 'server_data', 'en', 'Server data');
INSERT INTO `lng_data` VALUES ('common', 'server_software', 'en', 'Server Software');
INSERT INTO `lng_data` VALUES ('common', 'set', 'en', 'Set');
INSERT INTO `lng_data` VALUES ('common', 'setSystemLanguage', 'en', 'Set System Language');
INSERT INTO `lng_data` VALUES ('common', 'setUserLanguage', 'en', 'Set User Language');
INSERT INTO `lng_data` VALUES ('common', 'set_offline', 'en', 'Set Offline');
INSERT INTO `lng_data` VALUES ('common', 'set_online', 'en', 'Set Online');
INSERT INTO `lng_data` VALUES ('common', 'settings', 'en', 'Settings');
INSERT INTO `lng_data` VALUES ('common', 'show_details', 'en', 'show details');
INSERT INTO `lng_data` VALUES ('common', 'show_list', 'en', 'Show List');
INSERT INTO `lng_data` VALUES ('common', 'show_members', 'en', 'Display Members');
INSERT INTO `lng_data` VALUES ('common', 'show_owner', 'en', 'Show Owner');
INSERT INTO `lng_data` VALUES ('common', 'show_structure', 'en', 'Enable Structured-View');
INSERT INTO `lng_data` VALUES ('common', 'signature', 'en', 'Signature');
INSERT INTO `lng_data` VALUES ('common', 'slm', 'en', 'SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'slm_added', 'en', 'SCORM Learning Module added');
INSERT INTO `lng_data` VALUES ('common', 'slm_create', 'en', 'Create SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'slm_delete', 'en', 'Delete SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'slm_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'slm_join', 'en', 'Subscribe to SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'slm_leave', 'en', 'Unsubscribe from SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'slm_read', 'en', 'Read access to SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'slm_visible', 'en', 'SCORM Learning Module is visible');
INSERT INTO `lng_data` VALUES ('common', 'slm_write', 'en', 'Edit SCORM Learning Module');
INSERT INTO `lng_data` VALUES ('common', 'smtp', 'en', 'SMTP');
INSERT INTO `lng_data` VALUES ('common', 'sort_by_this_column', 'en', 'Sort by this column');
INSERT INTO `lng_data` VALUES ('common', 'st_a', 'en', 'a chapter');
INSERT INTO `lng_data` VALUES ('common', 'st_add', 'en', 'Add chapter');
INSERT INTO `lng_data` VALUES ('common', 'st_added', 'en', 'Chapter added');
INSERT INTO `lng_data` VALUES ('common', 'st_edit', 'en', 'Edit chapter');
INSERT INTO `lng_data` VALUES ('common', 'st_new', 'en', 'New chapter');
INSERT INTO `lng_data` VALUES ('common', 'startpage', 'en', 'Startpage');
INSERT INTO `lng_data` VALUES ('common', 'status', 'en', 'Status');
INSERT INTO `lng_data` VALUES ('common', 'step', 'en', 'Step');
INSERT INTO `lng_data` VALUES ('common', 'stop_inheritance', 'en', 'Stop inheritance');
INSERT INTO `lng_data` VALUES ('common', 'street', 'en', 'Street');
INSERT INTO `lng_data` VALUES ('common', 'structure', 'en', 'Structure');
INSERT INTO `lng_data` VALUES ('common', 'subcat_name', 'en', 'Subcategory Name');
INSERT INTO `lng_data` VALUES ('common', 'subchapter_new', 'en', 'New Subchapter');
INSERT INTO `lng_data` VALUES ('common', 'subject', 'en', 'Subject');
INSERT INTO `lng_data` VALUES ('common', 'submit', 'en', 'Submit');
INSERT INTO `lng_data` VALUES ('common', 'subobjects', 'en', 'Subobjects');
INSERT INTO `lng_data` VALUES ('common', 'subscription', 'en', 'Subscription');
INSERT INTO `lng_data` VALUES ('common', 'summary', 'en', 'Summary');
INSERT INTO `lng_data` VALUES ('common', 'system', 'en', 'System');
INSERT INTO `lng_data` VALUES ('common', 'system_choose_language', 'en', 'System Choose Language');
INSERT INTO `lng_data` VALUES ('common', 'system_groups', 'en', 'System Groups');
INSERT INTO `lng_data` VALUES ('common', 'system_grp', 'en', 'System Group');
INSERT INTO `lng_data` VALUES ('common', 'system_language', 'en', 'System Language');
INSERT INTO `lng_data` VALUES ('common', 'system_message', 'en', 'System Message');
INSERT INTO `lng_data` VALUES ('common', 'term', 'en', 'Term');
INSERT INTO `lng_data` VALUES ('common', 'test_intern', 'en', 'Test Intern');
INSERT INTO `lng_data` VALUES ('common', 'tests', 'en', 'Tests');
INSERT INTO `lng_data` VALUES ('common', 'time', 'en', 'Time');
INSERT INTO `lng_data` VALUES ('common', 'title', 'en', 'Title');
INSERT INTO `lng_data` VALUES ('common', 'to', 'en', 'To');
INSERT INTO `lng_data` VALUES ('common', 'to_client_list', 'en', 'To Client selection');
INSERT INTO `lng_data` VALUES ('common', 'to_desktop', 'en', 'Subscribe');
INSERT INTO `lng_data` VALUES ('common', 'today', 'en', 'Today');
INSERT INTO `lng_data` VALUES ('common', 'total', 'en', 'Total');
INSERT INTO `lng_data` VALUES ('common', 'tpl_path', 'en', 'Template Path');
INSERT INTO `lng_data` VALUES ('common', 'translation', 'en', 'Translation');
INSERT INTO `lng_data` VALUES ('common', 'trash', 'en', 'Trash');
INSERT INTO `lng_data` VALUES ('common', 'treeview', 'en', 'Tree View');
INSERT INTO `lng_data` VALUES ('common', 'tst', 'en', 'Test');
INSERT INTO `lng_data` VALUES ('common', 'tst_add', 'en', 'Add test');
INSERT INTO `lng_data` VALUES ('common', 'tst_delete', 'en', 'Delete Test');
INSERT INTO `lng_data` VALUES ('common', 'tst_edit_permission', 'en', 'Change permission settings');
INSERT INTO `lng_data` VALUES ('common', 'tst_new', 'en', 'New test');
INSERT INTO `lng_data` VALUES ('common', 'tst_read', 'en', 'Read access to Test');
INSERT INTO `lng_data` VALUES ('common', 'tst_visible', 'en', 'Test is visible');
INSERT INTO `lng_data` VALUES ('common', 'tst_write', 'en', 'Edit Test');
INSERT INTO `lng_data` VALUES ('common', 'txt_add_object_instruction1', 'en', 'Browse to the location where you want to add');
INSERT INTO `lng_data` VALUES ('common', 'txt_add_object_instruction2', 'en', '.');
INSERT INTO `lng_data` VALUES ('common', 'txt_registered', 'en', 'You successfully registered to ILIAS. Please click on the button below to login to ILIAs with your useraccount.');
INSERT INTO `lng_data` VALUES ('common', 'typ', 'en', 'Object Type Definition');
INSERT INTO `lng_data` VALUES ('common', 'type', 'en', 'Type');
INSERT INTO `lng_data` VALUES ('common', 'type_your_message_here', 'en', 'Type Your Message Here');
INSERT INTO `lng_data` VALUES ('common', 'uid', 'en', 'UID');
INSERT INTO `lng_data` VALUES ('common', 'uninstall', 'en', 'Uninstall');
INSERT INTO `lng_data` VALUES ('common', 'uninstalled', 'en', 'uninstalled.');
INSERT INTO `lng_data` VALUES ('common', 'unknown', 'en', 'UNKNOWN');
INSERT INTO `lng_data` VALUES ('common', 'unread', 'en', 'Unread');
INSERT INTO `lng_data` VALUES ('common', 'unsubscribe', 'en', 'unsubscribe');
INSERT INTO `lng_data` VALUES ('common', 'up', 'en', 'Up');
INSERT INTO `lng_data` VALUES ('common', 'update_applied', 'en', 'Update Applied');
INSERT INTO `lng_data` VALUES ('common', 'update_language', 'en', 'Update Language');
INSERT INTO `lng_data` VALUES ('common', 'upload', 'en', 'Upload');
INSERT INTO `lng_data` VALUES ('common', 'url', 'en', 'URL');
INSERT INTO `lng_data` VALUES ('common', 'url_description', 'en', 'URL Description');
INSERT INTO `lng_data` VALUES ('common', 'user', 'en', 'User');
INSERT INTO `lng_data` VALUES ('common', 'user_added', 'en', 'User added');
INSERT INTO `lng_data` VALUES ('common', 'user_assignment', 'en', 'User Assignment');
INSERT INTO `lng_data` VALUES ('common', 'user_deleted', 'en', 'User deleted');
INSERT INTO `lng_data` VALUES ('common', 'user_language', 'en', 'User Language');
INSERT INTO `lng_data` VALUES ('common', 'user_not_chosen', 'en', 'You did not choose a group-member!');
INSERT INTO `lng_data` VALUES ('common', 'userdata', 'en', 'User data');
INSERT INTO `lng_data` VALUES ('common', 'username', 'en', 'User name');
INSERT INTO `lng_data` VALUES ('common', 'users', 'en', 'Users');
INSERT INTO `lng_data` VALUES ('common', 'users_online', 'en', 'Users online');
INSERT INTO `lng_data` VALUES ('common', 'usr', 'en', 'User');
INSERT INTO `lng_data` VALUES ('common', 'usr_a', 'en', 'a User');
INSERT INTO `lng_data` VALUES ('common', 'usr_add', 'en', 'Add User');
INSERT INTO `lng_data` VALUES ('common', 'usr_added', 'en', 'User added');
INSERT INTO `lng_data` VALUES ('common', 'usr_agreement', 'en', 'User Agreement');
INSERT INTO `lng_data` VALUES ('common', 'usr_agreement_empty', 'en', 'The agreement contains no text');
INSERT INTO `lng_data` VALUES ('common', 'usr_edit', 'en', 'Edit User');
INSERT INTO `lng_data` VALUES ('common', 'usr_new', 'en', 'New User');
INSERT INTO `lng_data` VALUES ('common', 'usr_skin_style', 'en', 'Skin / Style');
INSERT INTO `lng_data` VALUES ('common', 'usr_style', 'en', 'User Style');
INSERT INTO `lng_data` VALUES ('common', 'usrf', 'en', 'User accounts');
INSERT INTO `lng_data` VALUES ('common', 'usrf_create_user', 'en', 'Create new user account');
INSERT INTO `lng_data` VALUES ('common', 'usrf_delete', 'en', 'Delete user accounts');
INSERT INTO `lng_data` VALUES ('common', 'usrf_edit_permission', 'en', 'Change access to user accounts');
INSERT INTO `lng_data` VALUES ('common', 'usrf_edit_roleassignment', 'en', 'Change roleassignment of user accounts');
INSERT INTO `lng_data` VALUES ('common', 'usrf_read', 'en', 'Read access to user accounts');
INSERT INTO `lng_data` VALUES ('common', 'usrf_visible', 'en', 'User accounts are visible');
INSERT INTO `lng_data` VALUES ('common', 'usrf_write', 'en', 'Edit user accounts');
INSERT INTO `lng_data` VALUES ('common', 'validate', 'en', 'Validate');
INSERT INTO `lng_data` VALUES ('common', 'value', 'en', 'Value');
INSERT INTO `lng_data` VALUES ('common', 'version', 'en', 'Version');
INSERT INTO `lng_data` VALUES ('common', 'view', 'en', 'View');
INSERT INTO `lng_data` VALUES ('common', 'view_content', 'en', 'View Content');
INSERT INTO `lng_data` VALUES ('common', 'visible_layers', 'en', 'Visible Layers');
INSERT INTO `lng_data` VALUES ('common', 'visits', 'en', 'Visits');
INSERT INTO `lng_data` VALUES ('common', 'week', 'en', 'Week');
INSERT INTO `lng_data` VALUES ('common', 'welcome', 'en', 'Welcome');
INSERT INTO `lng_data` VALUES ('common', 'who_is_online', 'en', 'Who is online?');
INSERT INTO `lng_data` VALUES ('common', 'with', 'en', 'with');
INSERT INTO `lng_data` VALUES ('common', 'write', 'en', 'Write');
INSERT INTO `lng_data` VALUES ('common', 'yes', 'en', 'Yes');
INSERT INTO `lng_data` VALUES ('common', 'you_may_add_local_roles', 'en', 'You May Add Local Roles');
INSERT INTO `lng_data` VALUES ('common', 'your_message', 'en', 'Your Message');
INSERT INTO `lng_data` VALUES ('common', 'zip', 'en', 'Postal Code');
INSERT INTO `lng_data` VALUES ('common', 'zipcode', 'en', 'Postal Code');
INSERT INTO `lng_data` VALUES ('content', 'HTML export', 'en', 'HTML Export');
INSERT INTO `lng_data` VALUES ('content', 'PDF export', 'en', 'PDF Export');
INSERT INTO `lng_data` VALUES ('content', 'Pages', 'en', 'Pages');
INSERT INTO `lng_data` VALUES ('content', 'all', 'en', 'All');
INSERT INTO `lng_data` VALUES ('content', 'cont_Alphabetic', 'en', 'Alphabetic A, B, ...');
INSERT INTO `lng_data` VALUES ('content', 'cont_Circle', 'en', 'Circle');
INSERT INTO `lng_data` VALUES ('content', 'cont_Citation', 'en', 'Citation');
INSERT INTO `lng_data` VALUES ('content', 'cont_Example', 'en', 'Example');
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline1', 'en', 'Headline 1');
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline2', 'en', 'Headline 2');
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline3', 'en', 'Headline 3');
INSERT INTO `lng_data` VALUES ('content', 'cont_List', 'en', 'List');
INSERT INTO `lng_data` VALUES ('content', 'cont_LocalFile', 'en', 'Local File');
INSERT INTO `lng_data` VALUES ('content', 'cont_Mnemonic', 'en', 'Mnemonic');
INSERT INTO `lng_data` VALUES ('content', 'cont_Number', 'en', 'Number');
INSERT INTO `lng_data` VALUES ('content', 'cont_Poly', 'en', 'Polygon');
INSERT INTO `lng_data` VALUES ('content', 'cont_Rect', 'en', 'Rectangle');
INSERT INTO `lng_data` VALUES ('content', 'cont_Reference', 'en', 'Reference');
INSERT INTO `lng_data` VALUES ('content', 'cont_Remark', 'en', 'Remark');
INSERT INTO `lng_data` VALUES ('content', 'cont_Roman', 'en', 'Roman I, II, ...');
INSERT INTO `lng_data` VALUES ('content', 'cont_Unordered', 'en', 'Unordered');
INSERT INTO `lng_data` VALUES ('content', 'cont_add_area', 'en', 'Add Area');
INSERT INTO `lng_data` VALUES ('content', 'cont_add_definition', 'en', 'Add Definition');
INSERT INTO `lng_data` VALUES ('content', 'cont_added_term', 'en', 'Term added');
INSERT INTO `lng_data` VALUES ('content', 'cont_all_definitions', 'en', 'All Definitions');
INSERT INTO `lng_data` VALUES ('content', 'cont_all_pages', 'en', 'All Pages');
INSERT INTO `lng_data` VALUES ('content', 'cont_alphabetic', 'en', 'Alphabetic a, b, ...');
INSERT INTO `lng_data` VALUES ('content', 'cont_areas_deleted', 'en', 'Map areas deleted.');
INSERT INTO `lng_data` VALUES ('content', 'cont_assign_full', 'en', 'Assign Fullscreen');
INSERT INTO `lng_data` VALUES ('content', 'cont_assign_std', 'en', 'Assign Standard');
INSERT INTO `lng_data` VALUES ('content', 'cont_assign_translation', 'en', 'Assign translation');
INSERT INTO `lng_data` VALUES ('content', 'cont_assignments_deleted', 'en', 'The assignments have been deleted');
INSERT INTO `lng_data` VALUES ('content', 'cont_back', 'en', 'Back');
INSERT INTO `lng_data` VALUES ('content', 'cont_booktitle', 'en', 'Booktitle');
INSERT INTO `lng_data` VALUES ('content', 'cont_bottom', 'en', 'Bottom');
INSERT INTO `lng_data` VALUES ('content', 'cont_cant_del_full', 'en', 'Deletion of fullscreen file not possible.');
INSERT INTO `lng_data` VALUES ('content', 'cont_cant_del_std', 'en', 'Deletion of standard view file not possible.');
INSERT INTO `lng_data` VALUES ('content', 'cont_caption', 'en', 'Caption');
INSERT INTO `lng_data` VALUES ('content', 'cont_change_type', 'en', 'Change Type');
INSERT INTO `lng_data` VALUES ('content', 'cont_chap_and_pages', 'en', 'Chapters and Pages');
INSERT INTO `lng_data` VALUES ('content', 'cont_chap_select_target_now', 'en', 'Chapter marked for moving. Select target now.');
INSERT INTO `lng_data` VALUES ('content', 'cont_chapters', 'en', 'Chapters');
INSERT INTO `lng_data` VALUES ('content', 'cont_characteristic', 'en', 'Characteristic');
INSERT INTO `lng_data` VALUES ('content', 'cont_choose_cont_obj', 'en', 'Choose Content Object');
INSERT INTO `lng_data` VALUES ('content', 'cont_choose_glossary', 'en', 'Choose Glossary');
INSERT INTO `lng_data` VALUES ('content', 'cont_citation_err_one', 'en', 'You have to select exactly one edition');
INSERT INTO `lng_data` VALUES ('content', 'cont_citation_selection_not_valid', 'en', 'You\'re selection is not valid');
INSERT INTO `lng_data` VALUES ('content', 'cont_citations', 'en', 'Citations');
INSERT INTO `lng_data` VALUES ('content', 'cont_click_br_corner', 'en', 'Please click on the bottom right corner of the desired area.');
INSERT INTO `lng_data` VALUES ('content', 'cont_click_center', 'en', 'Please click on center of the desired area.');
INSERT INTO `lng_data` VALUES ('content', 'cont_click_circle', 'en', 'Please click on a circle point of the desired area.');
INSERT INTO `lng_data` VALUES ('content', 'cont_click_next_or_save', 'en', 'Please click on the next point of the polygon or save the area. (It is not necessary to click again on the starting point of this polygon !)');
INSERT INTO `lng_data` VALUES ('content', 'cont_click_next_point', 'en', 'Please click on the next point of the polygon.');
INSERT INTO `lng_data` VALUES ('content', 'cont_click_starting_point', 'en', 'Please click on the starting point of the polygon.');
INSERT INTO `lng_data` VALUES ('content', 'cont_click_tl_corner', 'en', 'Please click on the top left corner of the desired area.');
INSERT INTO `lng_data` VALUES ('content', 'cont_content', 'en', 'Content');
INSERT INTO `lng_data` VALUES ('content', 'cont_content_obj', 'en', 'Content Object');
INSERT INTO `lng_data` VALUES ('content', 'cont_coords', 'en', 'Coordinates');
INSERT INTO `lng_data` VALUES ('content', 'cont_create_dir', 'en', 'Create Directory');
INSERT INTO `lng_data` VALUES ('content', 'cont_cross_reference', 'en', 'Cross reference');
INSERT INTO `lng_data` VALUES ('content', 'cont_data_from_lms', 'en', 'adlcp:datafromlms');
INSERT INTO `lng_data` VALUES ('content', 'cont_def_layout', 'en', 'Default Layout');
INSERT INTO `lng_data` VALUES ('content', 'cont_def_organization', 'en', 'default');
INSERT INTO `lng_data` VALUES ('content', 'cont_definition', 'en', 'Definition');
INSERT INTO `lng_data` VALUES ('content', 'cont_definitions', 'en', 'Definitions');
INSERT INTO `lng_data` VALUES ('content', 'cont_del_assignment', 'en', 'Delete assignment');
INSERT INTO `lng_data` VALUES ('content', 'cont_dependencies', 'en', 'Dependencies');
INSERT INTO `lng_data` VALUES ('content', 'cont_details', 'en', 'Details');
INSERT INTO `lng_data` VALUES ('content', 'cont_dir_file', 'en', 'Directory/File');
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_file_list_properties', 'en', 'Edit File List Properties');
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob', 'en', 'Edit Media Object');
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_alias_prop', 'en', 'Edit Media Object Instance Properties');
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_files', 'en', 'Object Files');
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_properties', 'en', 'Edit Media Object Properties');
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_par', 'en', 'Edit Paragraph');
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_tab_properties', 'en', 'Table Properties');
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_term', 'en', 'Edit Term');
INSERT INTO `lng_data` VALUES ('content', 'cont_edition', 'en', 'Edition');
INSERT INTO `lng_data` VALUES ('content', 'cont_external', 'en', 'external');
INSERT INTO `lng_data` VALUES ('content', 'cont_file', 'en', 'File');
INSERT INTO `lng_data` VALUES ('content', 'cont_files', 'en', 'Files');
INSERT INTO `lng_data` VALUES ('content', 'cont_full_is_in_dir', 'en', 'Deletion not possible. Fullscreen file is in directory.');
INSERT INTO `lng_data` VALUES ('content', 'cont_fullscreen', 'en', 'Fullscreen');
INSERT INTO `lng_data` VALUES ('content', 'cont_get_link', 'en', 'get link');
INSERT INTO `lng_data` VALUES ('content', 'cont_height', 'en', 'Height');
INSERT INTO `lng_data` VALUES ('content', 'cont_how_published', 'en', 'How published');
INSERT INTO `lng_data` VALUES ('content', 'cont_href', 'en', 'href');
INSERT INTO `lng_data` VALUES ('content', 'cont_id_ref', 'en', 'identifierref');
INSERT INTO `lng_data` VALUES ('content', 'cont_imagemap', 'en', 'Image Map');
INSERT INTO `lng_data` VALUES ('content', 'cont_import_id', 'en', 'identifier');
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_file_item', 'en', 'Insert File Item');
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_file_list', 'en', 'Insert File List');
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_list', 'en', 'Insert List');
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_mob', 'en', 'Insert Media Object');
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_par', 'en', 'Insert Paragraph');
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_search', 'en', 'Please insert a search term');
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_table', 'en', 'Insert Table');
INSERT INTO `lng_data` VALUES ('content', 'cont_internal', 'en', 'internal');
INSERT INTO `lng_data` VALUES ('content', 'cont_internal_link', 'en', 'internal link');
INSERT INTO `lng_data` VALUES ('content', 'cont_is_visible', 'en', 'isvisible');
INSERT INTO `lng_data` VALUES ('content', 'cont_isbn', 'en', 'ISBN');
INSERT INTO `lng_data` VALUES ('content', 'cont_issn', 'en', 'ISSN');
INSERT INTO `lng_data` VALUES ('content', 'cont_item', 'en', 'Item');
INSERT INTO `lng_data` VALUES ('content', 'cont_journal', 'en', 'Journal');
INSERT INTO `lng_data` VALUES ('content', 'cont_keyword', 'en', 'Keyword');
INSERT INTO `lng_data` VALUES ('content', 'cont_link', 'en', 'Link');
INSERT INTO `lng_data` VALUES ('content', 'cont_link_ext', 'en', 'Link (external)');
INSERT INTO `lng_data` VALUES ('content', 'cont_link_int', 'en', 'Link (internal)');
INSERT INTO `lng_data` VALUES ('content', 'cont_link_select', 'en', 'Internal Link');
INSERT INTO `lng_data` VALUES ('content', 'cont_link_type', 'en', 'Link Type');
INSERT INTO `lng_data` VALUES ('content', 'cont_list_properties', 'en', 'List Properties');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_chapter', 'en', 'Chapter');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_chapter_new', 'en', 'Chapter (New Frame)');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_faq', 'en', 'Media (FAQ Frame)');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_inline', 'en', 'Media (Inline)');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_media', 'en', 'Media (Media Frame)');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_new', 'en', 'Media (New Frame)');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_page', 'en', 'Page');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_page_faq', 'en', 'Page (FAQ Frame)');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_page_new', 'en', 'Page (New Frame)');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_term', 'en', 'Glossary Term');
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_term_new', 'en', 'Glossary Term (New Frame)');
INSERT INTO `lng_data` VALUES ('content', 'cont_lm_properties', 'en', 'Learning Module Properties');
INSERT INTO `lng_data` VALUES ('content', 'cont_manifest', 'en', 'Manifest');
INSERT INTO `lng_data` VALUES ('content', 'cont_map_areas', 'en', 'Link Areas');
INSERT INTO `lng_data` VALUES ('content', 'cont_mastery_score', 'en', 'adlcp:masteryscore');
INSERT INTO `lng_data` VALUES ('content', 'cont_max_time_allowed', 'en', 'adlcp:maxtimeallowed');
INSERT INTO `lng_data` VALUES ('content', 'cont_mob_files', 'en', 'Object Files');
INSERT INTO `lng_data` VALUES ('content', 'cont_mob_inst_prop', 'en', 'Instance Properties');
INSERT INTO `lng_data` VALUES ('content', 'cont_mob_prop', 'en', 'Object Properties');
INSERT INTO `lng_data` VALUES ('content', 'cont_mob_usages', 'en', 'Usage');
INSERT INTO `lng_data` VALUES ('content', 'cont_month', 'en', 'Month');
INSERT INTO `lng_data` VALUES ('content', 'cont_msg_multiple_editions', 'en', 'It\'s not possible to show details of multiple editions');
INSERT INTO `lng_data` VALUES ('content', 'cont_name', 'en', 'Name');
INSERT INTO `lng_data` VALUES ('content', 'cont_new_area', 'en', 'New Link Area');
INSERT INTO `lng_data` VALUES ('content', 'cont_new_assignment', 'en', 'New assignment');
INSERT INTO `lng_data` VALUES ('content', 'cont_new_dir', 'en', 'New Directory');
INSERT INTO `lng_data` VALUES ('content', 'cont_new_file', 'en', 'New File');
INSERT INTO `lng_data` VALUES ('content', 'cont_new_media_obj', 'en', 'New Media Object');
INSERT INTO `lng_data` VALUES ('content', 'cont_new_term', 'en', 'New Term');
INSERT INTO `lng_data` VALUES ('content', 'cont_no_assign_itself', 'en', 'The object cannot be assigned to itself');
INSERT INTO `lng_data` VALUES ('content', 'cont_no_object_found', 'en', 'Could not find any object with this title');
INSERT INTO `lng_data` VALUES ('content', 'cont_no_page', 'en', 'No Page found.');
INSERT INTO `lng_data` VALUES ('content', 'cont_none', 'en', 'None');
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_cols', 'en', 'Number of Columns');
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_items', 'en', 'Number of Items');
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_rows', 'en', 'Number of Rows');
INSERT INTO `lng_data` VALUES ('content', 'cont_online', 'en', 'Online');
INSERT INTO `lng_data` VALUES ('content', 'cont_order', 'en', 'Order Type');
INSERT INTO `lng_data` VALUES ('content', 'cont_organization', 'en', 'Organization');
INSERT INTO `lng_data` VALUES ('content', 'cont_organizations', 'en', 'Organizations');
INSERT INTO `lng_data` VALUES ('content', 'cont_orig_size', 'en', 'Original Size');
INSERT INTO `lng_data` VALUES ('content', 'cont_page_header', 'en', 'Page Header');
INSERT INTO `lng_data` VALUES ('content', 'cont_page_select_target_now', 'en', 'Page marked for moving. Select target now.');
INSERT INTO `lng_data` VALUES ('content', 'cont_pages', 'en', 'Pages');
INSERT INTO `lng_data` VALUES ('content', 'cont_parameter', 'en', 'Parameter');
INSERT INTO `lng_data` VALUES ('content', 'cont_parameters', 'en', 'parameters');
INSERT INTO `lng_data` VALUES ('content', 'cont_personal_clipboard', 'en', 'Personal Clipboard');
INSERT INTO `lng_data` VALUES ('content', 'cont_pg_content', 'en', 'Page Content');
INSERT INTO `lng_data` VALUES ('content', 'cont_pg_title', 'en', 'Page Title');
INSERT INTO `lng_data` VALUES ('content', 'cont_prereq_type', 'en', 'adlcp:prerequisites.type');
INSERT INTO `lng_data` VALUES ('content', 'cont_prerequisites', 'en', 'adlcp:prerequisites');
INSERT INTO `lng_data` VALUES ('content', 'cont_preview', 'en', 'Preview');
INSERT INTO `lng_data` VALUES ('content', 'cont_publisher', 'en', 'Publisher');
INSERT INTO `lng_data` VALUES ('content', 'cont_purpose', 'en', 'Purpose');
INSERT INTO `lng_data` VALUES ('content', 'cont_ref_helptext', 'en', '(e.g. http://www.server.org/myimage.jpg)');
INSERT INTO `lng_data` VALUES ('content', 'cont_reference', 'en', 'Reference');
INSERT INTO `lng_data` VALUES ('content', 'cont_resource', 'en', 'Resource');
INSERT INTO `lng_data` VALUES ('content', 'cont_resource_type', 'en', 'type');
INSERT INTO `lng_data` VALUES ('content', 'cont_resources', 'en', 'Resources');
INSERT INTO `lng_data` VALUES ('content', 'cont_roman', 'en', 'Roman i, ii, ...');
INSERT INTO `lng_data` VALUES ('content', 'cont_saved_map_area', 'en', 'Saved map area');
INSERT INTO `lng_data` VALUES ('content', 'cont_saved_map_data', 'en', 'Saved map data');
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_title', 'en', 'title');
INSERT INTO `lng_data` VALUES ('content', 'cont_school', 'en', 'School');
INSERT INTO `lng_data` VALUES ('content', 'cont_scorm_type', 'en', 'adlcp:scormtype');
INSERT INTO `lng_data` VALUES ('content', 'cont_select_max_one_item', 'en', 'Please select one item only');
INSERT INTO `lng_data` VALUES ('content', 'cont_select_max_one_term', 'en', 'Select one term only');
INSERT INTO `lng_data` VALUES ('content', 'cont_select_one_edition', 'en', 'Please select at least one edition.');
INSERT INTO `lng_data` VALUES ('content', 'cont_select_one_translation', 'en', 'Please select one translation');
INSERT INTO `lng_data` VALUES ('content', 'cont_select_one_translation_warning', 'en', 'It is not possible to show more than one translation.');
INSERT INTO `lng_data` VALUES ('content', 'cont_select_term', 'en', 'Select a term');
INSERT INTO `lng_data` VALUES ('content', 'cont_select_translation', 'en', 'Please select the assignment from the list above');
INSERT INTO `lng_data` VALUES ('content', 'cont_series', 'en', 'Series');
INSERT INTO `lng_data` VALUES ('content', 'cont_series_editor', 'en', 'Series editor');
INSERT INTO `lng_data` VALUES ('content', 'cont_series_title', 'en', 'Series title');
INSERT INTO `lng_data` VALUES ('content', 'cont_series_volume', 'en', 'Series volume');
INSERT INTO `lng_data` VALUES ('content', 'cont_set_class', 'en', 'Set Class');
INSERT INTO `lng_data` VALUES ('content', 'cont_set_width', 'en', 'Set Width');
INSERT INTO `lng_data` VALUES ('content', 'cont_shape', 'en', 'Shape');
INSERT INTO `lng_data` VALUES ('content', 'cont_show', 'en', 'Show');
INSERT INTO `lng_data` VALUES ('content', 'cont_show_citation', 'en', 'Show with citation');
INSERT INTO `lng_data` VALUES ('content', 'cont_size', 'en', 'Size (Bytes)');
INSERT INTO `lng_data` VALUES ('content', 'cont_source', 'en', 'Quelle');
INSERT INTO `lng_data` VALUES ('content', 'cont_st_title', 'en', 'Chapter Title');
INSERT INTO `lng_data` VALUES ('content', 'cont_std_is_in_dir', 'en', 'Deletion not possible. Standard view file is in directory.');
INSERT INTO `lng_data` VALUES ('content', 'cont_std_view', 'en', 'Standard View');
INSERT INTO `lng_data` VALUES ('content', 'cont_structure', 'en', 'structure');
INSERT INTO `lng_data` VALUES ('content', 'cont_subchapters', 'en', 'Subchapters');
INSERT INTO `lng_data` VALUES ('content', 'cont_table', 'en', 'Table');
INSERT INTO `lng_data` VALUES ('content', 'cont_table_border', 'en', 'Table Border');
INSERT INTO `lng_data` VALUES ('content', 'cont_table_cellpadding', 'en', 'Table Cell Padding');
INSERT INTO `lng_data` VALUES ('content', 'cont_table_cellspacing', 'en', 'Table Cell Spacing');
INSERT INTO `lng_data` VALUES ('content', 'cont_table_width', 'en', 'Table Width');
INSERT INTO `lng_data` VALUES ('content', 'cont_target_within_source', 'en', 'Target must not be within source object.');
INSERT INTO `lng_data` VALUES ('content', 'cont_term', 'en', 'Term');
INSERT INTO `lng_data` VALUES ('content', 'cont_terms', 'en', 'Terms');
INSERT INTO `lng_data` VALUES ('content', 'cont_time_limit_action', 'en', 'adlcp:timelimitaction');
INSERT INTO `lng_data` VALUES ('content', 'cont_toc', 'en', 'Table of Contents');
INSERT INTO `lng_data` VALUES ('content', 'cont_top', 'en', 'Top');
INSERT INTO `lng_data` VALUES ('content', 'cont_translations', 'en', 'Translation(s)');
INSERT INTO `lng_data` VALUES ('content', 'cont_translations_assigned', 'en', 'The translation(s) have been assigned');
INSERT INTO `lng_data` VALUES ('content', 'cont_update', 'en', 'Update');
INSERT INTO `lng_data` VALUES ('content', 'cont_upload_file', 'en', 'Upload File');
INSERT INTO `lng_data` VALUES ('content', 'cont_url', 'en', 'URL');
INSERT INTO `lng_data` VALUES ('content', 'cont_version', 'en', 'version');
INSERT INTO `lng_data` VALUES ('content', 'cont_where_published', 'en', 'Where published');
INSERT INTO `lng_data` VALUES ('content', 'cont_width', 'en', 'Width');
INSERT INTO `lng_data` VALUES ('content', 'cont_wysiwyg', 'en', 'Content Wysiwyg');
INSERT INTO `lng_data` VALUES ('content', 'cont_xml_base', 'en', 'xml:base');
INSERT INTO `lng_data` VALUES ('content', 'cont_year', 'en', 'Year');
INSERT INTO `lng_data` VALUES ('content', 'pages from', 'en', 'Pages From');
INSERT INTO `lng_data` VALUES ('content', 'par', 'en', 'Paragraph');
INSERT INTO `lng_data` VALUES ('content', 'pg', 'en', 'Page');
INSERT INTO `lng_data` VALUES ('content', 'read offline', 'en', 'Read Offline');
INSERT INTO `lng_data` VALUES ('content', 'st', 'en', 'Chapter');
INSERT INTO `lng_data` VALUES ('content', 'start export', 'en', 'Start Export');
INSERT INTO `lng_data` VALUES ('forum', 'forums', 'en', 'Forums');
INSERT INTO `lng_data` VALUES ('forum', 'forums_articles', 'en', 'Articles');
INSERT INTO `lng_data` VALUES ('forum', 'forums_attachments', 'en', 'Attachments');
INSERT INTO `lng_data` VALUES ('forum', 'forums_attachments_add', 'en', 'Add attachment');
INSERT INTO `lng_data` VALUES ('forum', 'forums_attachments_edit', 'en', 'Edit attachment');
INSERT INTO `lng_data` VALUES ('forum', 'forums_available', 'en', 'Available Forums');
INSERT INTO `lng_data` VALUES ('forum', 'forums_censor_comment', 'en', 'Comment of Censor');
INSERT INTO `lng_data` VALUES ('forum', 'forums_count', 'en', 'Number of Forums');
INSERT INTO `lng_data` VALUES ('forum', 'forums_count_art', 'en', 'Number of Articles');
INSERT INTO `lng_data` VALUES ('forum', 'forums_count_thr', 'en', 'Number of Threads');
INSERT INTO `lng_data` VALUES ('forum', 'forums_delete_file', 'en', 'Delete attachment');
INSERT INTO `lng_data` VALUES ('forum', 'forums_download_attachment', 'en', 'Download file');
INSERT INTO `lng_data` VALUES ('forum', 'forums_edit_post', 'en', 'Edit Article');
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_censor2_post', 'en', 'Revoke Censorship?');
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_censor_post', 'en', 'Are you sure you want to hide this article?');
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_delete_post', 'en', 'Are you sure you want to delete this article including any responses?');
INSERT INTO `lng_data` VALUES ('forum', 'forums_last_post', 'en', 'Last Article');
INSERT INTO `lng_data` VALUES ('forum', 'forums_moderators', 'en', 'Moderators');
INSERT INTO `lng_data` VALUES ('forum', 'forums_new_entries', 'en', 'New Forums Entries');
INSERT INTO `lng_data` VALUES ('forum', 'forums_new_thread', 'en', 'New Topic');
INSERT INTO `lng_data` VALUES ('forum', 'forums_not_available', 'en', 'Forums Not Available');
INSERT INTO `lng_data` VALUES ('forum', 'forums_overview', 'en', 'Forums Overview');
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_deleted', 'en', 'Article has been deleted');
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_modified', 'en', 'Article has been modified');
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_new_entry', 'en', 'New article has been inscribed');
INSERT INTO `lng_data` VALUES ('forum', 'forums_posts', 'en', 'All articles');
INSERT INTO `lng_data` VALUES ('forum', 'forums_posts_not_available', 'en', 'Articles Not Available');
INSERT INTO `lng_data` VALUES ('forum', 'forums_print_thread', 'en', 'Print Thread');
INSERT INTO `lng_data` VALUES ('forum', 'forums_quote', 'en', 'Quote');
INSERT INTO `lng_data` VALUES ('forum', 'forums_respond', 'en', 'Post Reply');
INSERT INTO `lng_data` VALUES ('forum', 'forums_the_post', 'en', 'Article');
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread', 'en', 'Topic');
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_articles', 'en', 'Articles to the topic');
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_create_date', 'en', 'Created at');
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_create_from', 'en', 'Created from');
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_new_entry', 'en', 'New topic has been inscribed');
INSERT INTO `lng_data` VALUES ('forum', 'forums_threads', 'en', 'Threads');
INSERT INTO `lng_data` VALUES ('forum', 'forums_threads_not_available', 'en', 'Topics Not Available');
INSERT INTO `lng_data` VALUES ('forum', 'forums_topics_overview', 'en', 'Topics Overview');
INSERT INTO `lng_data` VALUES ('forum', 'forums_your_reply', 'en', 'Your Reply');
INSERT INTO `lng_data` VALUES ('mail', 'also_as_email', 'en', 'Also as Email');
INSERT INTO `lng_data` VALUES ('mail', 'bc', 'en', 'BC');
INSERT INTO `lng_data` VALUES ('mail', 'forward', 'en', 'Forward');
INSERT INTO `lng_data` VALUES ('mail', 'linebreak', 'en', 'Linebreak');
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_recipient', 'en', 'Please enter a recipient');
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_subfolder', 'en', 'Add Subfolder');
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_subject', 'en', 'Please enter a subject');
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_to_addressbook', 'en', 'Add to addressbook');
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_type', 'en', 'Please add the type of mail you want to send');
INSERT INTO `lng_data` VALUES ('mail', 'mail_addr_entries', 'en', 'Entries');
INSERT INTO `lng_data` VALUES ('mail', 'mail_attachments', 'en', 'Attachments');
INSERT INTO `lng_data` VALUES ('mail', 'mail_bc_not_valid', 'en', 'The Bc recipient is not valid');
INSERT INTO `lng_data` VALUES ('mail', 'mail_bc_search', 'en', 'BC search');
INSERT INTO `lng_data` VALUES ('mail', 'mail_byte', 'en', 'Byte');
INSERT INTO `lng_data` VALUES ('mail', 'mail_cc_not_valid', 'en', 'The Cc recipient is not valid');
INSERT INTO `lng_data` VALUES ('mail', 'mail_cc_search', 'en', 'CC search');
INSERT INTO `lng_data` VALUES ('mail', 'mail_change_to_folder', 'en', 'Switch to folder:');
INSERT INTO `lng_data` VALUES ('mail', 'mail_check_your_email_addr', 'en', 'Your email address is not valid');
INSERT INTO `lng_data` VALUES ('mail', 'mail_compose', 'en', 'Compose Message');
INSERT INTO `lng_data` VALUES ('mail', 'mail_deleted', 'en', 'The mail(s) are deleted');
INSERT INTO `lng_data` VALUES ('mail', 'mail_deleted_entry', 'en', 'The entries are deleted');
INSERT INTO `lng_data` VALUES ('mail', 'mail_email_forbidden', 'en', 'You are not allowed to send email');
INSERT INTO `lng_data` VALUES ('mail', 'mail_entry_added', 'en', 'Added new Entry');
INSERT INTO `lng_data` VALUES ('mail', 'mail_entry_changed', 'en', 'The entry is changed');
INSERT INTO `lng_data` VALUES ('mail', 'mail_file_name', 'en', 'Filename');
INSERT INTO `lng_data` VALUES ('mail', 'mail_file_size', 'en', 'Filesize');
INSERT INTO `lng_data` VALUES ('mail', 'mail_files_deleted', 'en', 'The file(s) are deleted');
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_created', 'en', 'A new folder has been created');
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_deleted', 'en', 'The folder has been deleted');
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_exists', 'en', 'A folder already exists with this name');
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_name', 'en', 'Folder name');
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_name_changed', 'en', 'The folder has been renamed');
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_options', 'en', 'Folder Options');
INSERT INTO `lng_data` VALUES ('mail', 'mail_following_rcp_not_valid', 'en', 'The following recipients are not valid:');
INSERT INTO `lng_data` VALUES ('mail', 'mail_global_options', 'en', 'Global options');
INSERT INTO `lng_data` VALUES ('mail', 'mail_incoming', 'en', 'Incoming mails');
INSERT INTO `lng_data` VALUES ('mail', 'mail_incoming_both', 'en', 'local and forwarding');
INSERT INTO `lng_data` VALUES ('mail', 'mail_incoming_local', 'en', 'only local');
INSERT INTO `lng_data` VALUES ('mail', 'mail_incoming_smtp', 'en', 'forward to email address');
INSERT INTO `lng_data` VALUES ('mail', 'mail_insert_folder_name', 'en', 'Please insert a folder name');
INSERT INTO `lng_data` VALUES ('mail', 'mail_insert_query', 'en', 'Please insert a query');
INSERT INTO `lng_data` VALUES ('mail', 'mail_intern', 'en', 'Internal');
INSERT INTO `lng_data` VALUES ('mail', 'mail_mark_read', 'en', 'Mark mails read');
INSERT INTO `lng_data` VALUES ('mail', 'mail_mark_unread', 'en', 'Mark mails unread');
INSERT INTO `lng_data` VALUES ('mail', 'mail_maxsize_attachment_error', 'en', 'The upload limit is:');
INSERT INTO `lng_data` VALUES ('mail', 'mail_message_send', 'en', 'Message sent');
INSERT INTO `lng_data` VALUES ('mail', 'mail_move_error', 'en', 'Error moving mail(s)');
INSERT INTO `lng_data` VALUES ('mail', 'mail_move_to', 'en', 'Move to:');
INSERT INTO `lng_data` VALUES ('mail', 'mail_moved', 'en', 'The mail(s) have been moved');
INSERT INTO `lng_data` VALUES ('mail', 'mail_moved_to_trash', 'en', 'Mail(s) have been moved to trash');
INSERT INTO `lng_data` VALUES ('mail', 'mail_new_file', 'en', 'Add new file:');
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_attach_allowed', 'en', 'System messages are not allowed to contain attachments');
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_attachments_found', 'en', 'No attachments found');
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_permissions_write_smtp', 'en', 'You have permission to write extern emails');
INSERT INTO `lng_data` VALUES ('mail', 'mail_options_of', 'en', 'Options');
INSERT INTO `lng_data` VALUES ('mail', 'mail_options_saved', 'en', 'Options saved');
INSERT INTO `lng_data` VALUES ('mail', 'mail_recipient_not_valid', 'en', 'The recipient is not valid');
INSERT INTO `lng_data` VALUES ('mail', 'mail_s', 'en', 'Mail(s)');
INSERT INTO `lng_data` VALUES ('mail', 'mail_s_unread', 'en', 'Unread Mail(s)');
INSERT INTO `lng_data` VALUES ('mail', 'mail_saved', 'en', 'The message has been saved');
INSERT INTO `lng_data` VALUES ('mail', 'mail_search_addressbook', 'en', 'search in addressbook');
INSERT INTO `lng_data` VALUES ('mail', 'mail_search_system', 'en', 'search in system');
INSERT INTO `lng_data` VALUES ('mail', 'mail_select_one_entry', 'en', 'You have to select one entry');
INSERT INTO `lng_data` VALUES ('mail', 'mail_select_one_file', 'en', 'You have to select one file');
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete', 'en', 'Are sure you want to delete the marked mail(s)');
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_entry', 'en', 'Are sure you want to delete the following entries');
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_file', 'en', 'The file will be removed permanently');
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_folder', 'en', 'The folder and its content will be removed permanently');
INSERT INTO `lng_data` VALUES ('mail', 'mail_to_search', 'en', 'To search');
INSERT INTO `lng_data` VALUES ('mail', 'mail_user_addr_n_valid', 'en', 'Follwing users have no valid email address');
INSERT INTO `lng_data` VALUES ('mail', 'search_bc_recipient', 'en', 'Search BC Recipient');
INSERT INTO `lng_data` VALUES ('mail', 'search_cc_recipient', 'en', 'Search CC Recipient');
INSERT INTO `lng_data` VALUES ('meta', 'meta_accessibility_restrictions', 'en', 'Accessibility Restrictions');
INSERT INTO `lng_data` VALUES ('meta', 'meta_active', 'en', 'Active');
INSERT INTO `lng_data` VALUES ('meta', 'meta_add', 'en', 'Add');
INSERT INTO `lng_data` VALUES ('meta', 'meta_annotation', 'en', 'Annotation');
INSERT INTO `lng_data` VALUES ('meta', 'meta_atomic', 'en', 'Atomic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_author', 'en', 'Author');
INSERT INTO `lng_data` VALUES ('meta', 'meta_browser', 'en', 'Browser');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AD', 'en', 'Andorra');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AE', 'en', 'United Arab Emirates');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AF', 'en', 'Afghanistan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AG', 'en', 'Antigua And Barbuda');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AI', 'en', 'Anguilla');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AL', 'en', 'Albania');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AM', 'en', 'Armenia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AN', 'en', 'Netherlands Antilles');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AO', 'en', 'Angola');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AQ', 'en', 'Antarctica');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AR', 'en', 'Argentina');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AS', 'en', 'American Samoa');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AT', 'en', 'Austria');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AU', 'en', 'Australia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AW', 'en', 'Aruba');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_AZ', 'en', 'Azerbaijan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BA', 'en', 'Bosnia And Herzegowina');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BB', 'en', 'Barbados');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BD', 'en', 'Bangladesh');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BE', 'en', 'Belgium');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BF', 'en', 'Burkina Faso');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BG', 'en', 'Bulgaria');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BH', 'en', 'Bahrain');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BI', 'en', 'Burundi');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BJ', 'en', 'Benin');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BM', 'en', 'Bermuda');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BN', 'en', 'Brunei Darussalam');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BO', 'en', 'Bolivia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BR', 'en', 'Brazil');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BS', 'en', 'Bahamas');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BT', 'en', 'Bhutan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BV', 'en', 'Bouvet Island');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BW', 'en', 'Botswana');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BY', 'en', 'Belarus');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_BZ', 'en', 'Belize');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CA', 'en', 'Canada');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CC', 'en', 'Cocos (Keeling) Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CF', 'en', 'Central African Republic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CG', 'en', 'Congo');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CH', 'en', 'Switzerland');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CI', 'en', 'Cote D\' Ivoire');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CK', 'en', 'Cook Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CL', 'en', 'Chile');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CM', 'en', 'Cameroon');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CN', 'en', 'China');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CO', 'en', 'Colombia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CR', 'en', 'Costa Rica');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CU', 'en', 'Cuba');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CV', 'en', 'Cape Verde');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CX', 'en', 'Christmas Island');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CY', 'en', 'Cyprus');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_CZ', 'en', 'Czech Republic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DE', 'en', 'Germany');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DJ', 'en', 'Djibouti');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DK', 'en', 'Denmark');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DM', 'en', 'Dominica');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DO', 'en', 'Dominican Republic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_DZ', 'en', 'Algeria');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EC', 'en', 'Ecuador');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EE', 'en', 'Estonia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EG', 'en', 'Egypt');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_EH', 'en', 'Western Sahara');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ER', 'en', 'Eritrea');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ES', 'en', 'Spain');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ET', 'en', 'Ethiopia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FI', 'en', 'Finland');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FJ', 'en', 'Fiji');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FK', 'en', 'Falkland Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FM', 'en', 'Micronesia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FO', 'en', 'Faroe Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FR', 'en', 'France');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_FX', 'en', 'France, Metropolitan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GA', 'en', 'Gabon');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GB', 'en', 'United Kingdom');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GD', 'en', 'Grenada');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GE', 'en', 'Giorgia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GF', 'en', 'French Guiana');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GH', 'en', 'Ghana');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GI', 'en', 'Gibraltar');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GL', 'en', 'Greenland');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GM', 'en', 'Gambia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GN', 'en', 'Guinea');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GP', 'en', 'Guadeloupe');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GQ', 'en', 'Equatorial Guinea');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GR', 'en', 'Greece');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GS', 'en', 'South Georgia And The South Sandwich Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GT', 'en', 'Guatemala');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GU', 'en', 'Guam');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GW', 'en', 'Guinea-Bissau');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_GY', 'en', 'Guyana');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HM', 'en', 'Heard And Nc Donald Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HN', 'en', 'Honduras');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HR', 'en', 'Croatia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HT', 'en', 'Haiti');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_HU', 'en', 'Hungary');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ID', 'en', 'Indonesia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IE', 'en', 'Ireland');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IL', 'en', 'Israel');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IN', 'en', 'India');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IO', 'en', 'British Indian Ocean Territory');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IQ', 'en', 'Iraq');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IR', 'en', 'Iran');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IS', 'en', 'Iceland');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_IT', 'en', 'Italy');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JM', 'en', 'Jamaica');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JO', 'en', 'Jordan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_JP', 'en', 'Japan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KE', 'en', 'Kenya');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KG', 'en', 'Kyrgyzstan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KH', 'en', 'Cambodia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KI', 'en', 'Kiribati');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KM', 'en', 'Comoros');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KN', 'en', 'Saint Kitts And Nevis');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KP', 'en', 'North Korea (People\'s Republic Of Korea)');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KR', 'en', 'South Korea (Republic Of Korea)');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KW', 'en', 'Kuwait');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KY', 'en', 'Cayman Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_KZ', 'en', 'Kazakhstan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LA', 'en', 'Lao People\'s Republic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LB', 'en', 'Lebanon');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LC', 'en', 'Saint Lucia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LI', 'en', 'Liechtenstein');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LK', 'en', 'Sri Lanka');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LR', 'en', 'Liberia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LS', 'en', 'Lesotho');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LT', 'en', 'Lithunia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LU', 'en', 'Luxembourg');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LV', 'en', 'Latvia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_LY', 'en', 'Libyan Arab Jamahiriya');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MA', 'en', 'Morocco');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MC', 'en', 'Monaco');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MD', 'en', 'Moldova');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MG', 'en', 'Madagascar');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MH', 'en', 'Marshall Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MK', 'en', 'Macedonia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ML', 'en', 'Mali');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MM', 'en', 'Myanmar');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MN', 'en', 'Mongolia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MO', 'en', 'Macau');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MP', 'en', 'Northern Mariana Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MQ', 'en', 'Martinique');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MR', 'en', 'Mauritania');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MS', 'en', 'Montserrat');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MT', 'en', 'Malta');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MU', 'en', 'Mauritius');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MV', 'en', 'Maldives');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MW', 'en', 'Malawi');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MX', 'en', 'Mexico');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MY', 'en', 'Malaysia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_MZ', 'en', 'Mozambique');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NA', 'en', 'Namibia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NC', 'en', 'New Caledonia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NE', 'en', 'Niger');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NF', 'en', 'Norfolk Island');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NG', 'en', 'Nigeria');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NI', 'en', 'Nicaragua');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NL', 'en', 'Netherlands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NO', 'en', 'Norway');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NP', 'en', 'Nepal');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NR', 'en', 'Nauru');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NU', 'en', 'Niue');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_NZ', 'en', 'New Zealand');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_OM', 'en', 'Oman');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PA', 'en', 'Panama');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PE', 'en', 'Peru');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PF', 'en', 'French Polynesia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PG', 'en', 'Papua New Guinea');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PH', 'en', 'Philippines');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PK', 'en', 'Pakistan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PL', 'en', 'Poland');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PM', 'en', 'St. Pierre And Miquelon');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PN', 'en', 'Pitcairn');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PR', 'en', 'Puerto Rico');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PT', 'en', 'Portugal');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PW', 'en', 'Palau');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_PY', 'en', 'Paraguay');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_QA', 'en', 'Qatar');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RE', 'en', 'Reunion');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RO', 'en', 'Romania');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RU', 'en', 'Ran Federation');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_RW', 'en', 'Rwanda');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SA', 'en', 'Saudi Arabia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SB', 'en', 'Solomon Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SC', 'en', 'Seychelles');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SD', 'en', 'Sudan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SE', 'en', 'Sweden');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SG', 'en', 'Singapore');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SH', 'en', 'St. Helena');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SI', 'en', 'Slovenia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SJ', 'en', 'Svalbard And Jan Mayen Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SK', 'en', 'Slovakia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SL', 'en', 'Siearra Leone');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SM', 'en', 'San Marino');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SN', 'en', 'Senegal');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SO', 'en', 'Somalia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SR', 'en', 'Suriname');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ST', 'en', 'Sao Tome And Principe');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SV', 'en', 'El Salvador');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SY', 'en', 'Syrian Arab Republic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_SZ', 'en', 'Swaziland');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TC', 'en', 'Turks And Caicos Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TD', 'en', 'Chad');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TF', 'en', 'French Southern Territories');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TG', 'en', 'Togo');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TH', 'en', 'Thailand');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TJ', 'en', 'Tajikistan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TK', 'en', 'Tokelau');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TM', 'en', 'Turkmenistan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TN', 'en', 'Tunisia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TO', 'en', 'Tonga');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TP', 'en', 'East Timor');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TR', 'en', 'Turkey');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TT', 'en', 'Trinidad And Tobago');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TV', 'en', 'Tuvalu');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TW', 'en', 'Taiwan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_TZ', 'en', 'Tanzania');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UA', 'en', 'Ukraine');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UG', 'en', 'Uganda');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UM', 'en', 'U.S. Minor Outlying Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_US', 'en', 'U.S.A');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UY', 'en', 'Uruguay');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_UZ', 'en', 'Uzbekistan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VA', 'en', 'Vatican City State');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VC', 'en', 'Saint Vincent And The Grenadines');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VE', 'en', 'Venezuela');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VG', 'en', 'Virgin Islands (British)');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VI', 'en', 'Virgin Islands (US)');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VN', 'en', 'Viet Nam');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_VU', 'en', 'Vanuatu');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_WF', 'en', 'Wallis And Futuna Islands');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_WS', 'en', 'Samoa');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_YE', 'en', 'Yemen');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_YT', 'en', 'Mayotte');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZA', 'en', 'South Africa');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZM', 'en', 'Zambia');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZR', 'en', 'Zaire');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZW', 'en', 'Zimbabwe');
INSERT INTO `lng_data` VALUES ('meta', 'meta_c_ZZ', 'en', 'Other Country');
INSERT INTO `lng_data` VALUES ('meta', 'meta_catalog', 'en', 'Catalog');
INSERT INTO `lng_data` VALUES ('meta', 'meta_choose_element', 'en', 'Please choose an element you want to add!');
INSERT INTO `lng_data` VALUES ('meta', 'meta_choose_language', 'en', 'Please choose a language');
INSERT INTO `lng_data` VALUES ('meta', 'meta_choose_section', 'en', 'Please choose a section');
INSERT INTO `lng_data` VALUES ('meta', 'meta_classification', 'en', 'Classification');
INSERT INTO `lng_data` VALUES ('meta', 'meta_collection', 'en', 'Collection');
INSERT INTO `lng_data` VALUES ('meta', 'meta_competency', 'en', 'Competency');
INSERT INTO `lng_data` VALUES ('meta', 'meta_context', 'en', 'Context');
INSERT INTO `lng_data` VALUES ('meta', 'meta_contribute', 'en', 'Contribute');
INSERT INTO `lng_data` VALUES ('meta', 'meta_copyright_and_other_restrictions', 'en', 'Copyright and Other Restrictions');
INSERT INTO `lng_data` VALUES ('meta', 'meta_cost', 'en', 'Cost');
INSERT INTO `lng_data` VALUES ('meta', 'meta_coverage', 'en', 'Coverage');
INSERT INTO `lng_data` VALUES ('meta', 'meta_date', 'en', 'Date');
INSERT INTO `lng_data` VALUES ('meta', 'meta_delete', 'en', 'Delete');
INSERT INTO `lng_data` VALUES ('meta', 'meta_description', 'en', 'Description');
INSERT INTO `lng_data` VALUES ('meta', 'meta_diagramm', 'en', 'Diagramm');
INSERT INTO `lng_data` VALUES ('meta', 'meta_difficulty', 'en', 'Difficulty');
INSERT INTO `lng_data` VALUES ('meta', 'meta_dificult', 'en', 'Difficult');
INSERT INTO `lng_data` VALUES ('meta', 'meta_draft', 'en', 'Draft');
INSERT INTO `lng_data` VALUES ('meta', 'meta_duration', 'en', 'Duration');
INSERT INTO `lng_data` VALUES ('meta', 'meta_easy', 'en', 'Easy');
INSERT INTO `lng_data` VALUES ('meta', 'meta_education', 'en', 'Education');
INSERT INTO `lng_data` VALUES ('meta', 'meta_educational_level', 'en', 'Educational Level');
INSERT INTO `lng_data` VALUES ('meta', 'meta_educational_objective', 'en', 'Educational Objective');
INSERT INTO `lng_data` VALUES ('meta', 'meta_entity', 'en', 'Entity');
INSERT INTO `lng_data` VALUES ('meta', 'meta_entry', 'en', 'Entry');
INSERT INTO `lng_data` VALUES ('meta', 'meta_exam', 'en', 'Exam');
INSERT INTO `lng_data` VALUES ('meta', 'meta_exercise', 'en', 'Exercise');
INSERT INTO `lng_data` VALUES ('meta', 'meta_experiment', 'en', 'Experiment');
INSERT INTO `lng_data` VALUES ('meta', 'meta_expositive', 'en', 'Expositive');
INSERT INTO `lng_data` VALUES ('meta', 'meta_figure', 'en', 'Figure');
INSERT INTO `lng_data` VALUES ('meta', 'meta_final', 'en', 'Final');
INSERT INTO `lng_data` VALUES ('meta', 'meta_format', 'en', 'Format');
INSERT INTO `lng_data` VALUES ('meta', 'meta_general', 'en', 'General');
INSERT INTO `lng_data` VALUES ('meta', 'meta_graph', 'en', 'Graph');
INSERT INTO `lng_data` VALUES ('meta', 'meta_has_format', 'en', 'Has Format');
INSERT INTO `lng_data` VALUES ('meta', 'meta_has_part', 'en', 'Has Part');
INSERT INTO `lng_data` VALUES ('meta', 'meta_has_version', 'en', 'Has Version');
INSERT INTO `lng_data` VALUES ('meta', 'meta_hierarchical', 'en', 'Hierarchical');
INSERT INTO `lng_data` VALUES ('meta', 'meta_high', 'en', 'High');
INSERT INTO `lng_data` VALUES ('meta', 'meta_higher_education', 'en', 'Higher Education');
INSERT INTO `lng_data` VALUES ('meta', 'meta_id', 'en', 'Id');
INSERT INTO `lng_data` VALUES ('meta', 'meta_idea', 'en', 'Idea');
INSERT INTO `lng_data` VALUES ('meta', 'meta_identifier', 'en', 'Identifier');
INSERT INTO `lng_data` VALUES ('meta', 'meta_index', 'en', 'Index');
INSERT INTO `lng_data` VALUES ('meta', 'meta_installation_remarks', 'en', 'Installation Remarks');
INSERT INTO `lng_data` VALUES ('meta', 'meta_intended_end_user_role', 'en', 'Intended End User Role');
INSERT INTO `lng_data` VALUES ('meta', 'meta_interactivity_level', 'en', 'Interactivity Level');
INSERT INTO `lng_data` VALUES ('meta', 'meta_interactivity_type', 'en', 'Interactivity Type');
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_based_on', 'en', 'Is Based On');
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_basis_for', 'en', 'Is Basis For');
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_format_of', 'en', 'Is Format Of');
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_part_of', 'en', 'Is Part Of');
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_referenced_by', 'en', 'Is Referenced By');
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_required_by', 'en', 'Is Required By');
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_version_of', 'en', 'Is Version Of');
INSERT INTO `lng_data` VALUES ('meta', 'meta_keyword', 'en', 'Keyword');
INSERT INTO `lng_data` VALUES ('meta', 'meta_kind', 'en', 'Kind');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_aa', 'en', 'afar');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ab', 'en', 'abkhazian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_af', 'en', 'afrikaans');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_am', 'en', 'amharic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ar', 'en', 'arabic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_as', 'en', 'assamese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ay', 'en', 'aymara');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_az', 'en', 'azerbaijani');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ba', 'en', 'bashkir');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_be', 'en', 'byelorussian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bg', 'en', 'bulgarian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bh', 'en', 'bihari');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bi', 'en', 'bislama');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bn', 'en', 'bengali;bangla');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bo', 'en', 'tibetan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_br', 'en', 'breton');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ca', 'en', 'catalan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_co', 'en', 'corsican');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_cs', 'en', 'czech');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_cy', 'en', 'welsh');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_da', 'en', 'danish');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_de', 'en', 'german');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_dz', 'en', 'bhutani');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_el', 'en', 'greek');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_en', 'en', 'english');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_eo', 'en', 'esperanto');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_es', 'en', 'spanish');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_et', 'en', 'estonian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_eu', 'en', 'basque');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fa', 'en', 'persian (farsi)');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fi', 'en', 'finnish');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fj', 'en', 'fiji');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fo', 'en', 'faroese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fr', 'en', 'french');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fy', 'en', 'frisian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ga', 'en', 'irish');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gd', 'en', 'scots gaelic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gl', 'en', 'galician');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gn', 'en', 'guarani');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gu', 'en', 'gujarati');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ha', 'en', 'hausa');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_he', 'en', 'hebrew');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hi', 'en', 'hindi');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hr', 'en', 'croatian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hu', 'en', 'hungarian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hy', 'en', 'armenian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ia', 'en', 'interlingua');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_id', 'en', 'indonesian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ie', 'en', 'interlingue');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ik', 'en', 'inupiak');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_is', 'en', 'icelandic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_it', 'en', 'italian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_iu', 'en', 'inuktitut');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ja', 'en', 'japanese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_jv', 'en', 'javanese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ka', 'en', 'georgian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kk', 'en', 'kazakh');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kl', 'en', 'greenlandic');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_km', 'en', 'cambodian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kn', 'en', 'kannada');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ko', 'en', 'korean');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ks', 'en', 'kashmiri');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ku', 'en', 'kurdish');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ky', 'en', 'kirghiz');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_la', 'en', 'latin');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ln', 'en', 'lingala');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lo', 'en', 'laothian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lt', 'en', 'lithuanian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lv', 'en', 'latvian;lettish');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mg', 'en', 'malagasy');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mi', 'en', 'maori');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mk', 'en', 'macedonian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ml', 'en', 'malayalam');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mn', 'en', 'mongolian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mo', 'en', 'moldavian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mr', 'en', 'marathi');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ms', 'en', 'malay');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mt', 'en', 'maltese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_my', 'en', 'burmese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_na', 'en', 'nauru');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ne', 'en', 'nepali');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_nl', 'en', 'dutch');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_no', 'en', 'norwegian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_oc', 'en', 'occitan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_om', 'en', 'afan (oromo)');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_or', 'en', 'oriya');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pa', 'en', 'punjabi');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pl', 'en', 'polish');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ps', 'en', 'pashto;pushto');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pt', 'en', 'portuguese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_qu', 'en', 'quechua');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rm', 'en', 'rhaeto-romance');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rn', 'en', 'kurundi');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ro', 'en', 'romanian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ru', 'en', 'russian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rw', 'en', 'kinyarwanda');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sa', 'en', 'sanskrit');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sd', 'en', 'sindhi');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sg', 'en', 'sangho');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sh', 'en', 'serbo-croatian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_si', 'en', 'singhalese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sk', 'en', 'slovak');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sl', 'en', 'slovenian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sm', 'en', 'samoan');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sn', 'en', 'shona');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_so', 'en', 'somali');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sq', 'en', 'albanian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sr', 'en', 'serbian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ss', 'en', 'siswati');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_st', 'en', 'sesotho');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_su', 'en', 'sundanese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sv', 'en', 'swedish');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sw', 'en', 'swahili');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ta', 'en', 'tamil');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_te', 'en', 'telugu');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tg', 'en', 'tajik');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_th', 'en', 'thai');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ti', 'en', 'tigrinya');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tk', 'en', 'turkmen');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tl', 'en', 'tagalog');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tn', 'en', 'setswana');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_to', 'en', 'tonga');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tr', 'en', 'turkish');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ts', 'en', 'tsonga');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tt', 'en', 'tatar');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tw', 'en', 'twi');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ug', 'en', 'uigur');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_uk', 'en', 'ukrainian');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ur', 'en', 'urdu');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_uz', 'en', 'uzbek');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_vi', 'en', 'vietnamese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_vo', 'en', 'volapuk');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_wo', 'en', 'wolof');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_xh', 'en', 'xhosa');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_yi', 'en', 'yiddish');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_yo', 'en', 'yoruba');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_za', 'en', 'zhuang');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_zh', 'en', 'chinese');
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_zu', 'en', 'zulu');
INSERT INTO `lng_data` VALUES ('meta', 'meta_language', 'en', 'Language');
INSERT INTO `lng_data` VALUES ('meta', 'meta_learner', 'en', 'Learner');
INSERT INTO `lng_data` VALUES ('meta', 'meta_learning_resource_type', 'en', 'Learning Resource Type');
INSERT INTO `lng_data` VALUES ('meta', 'meta_lecture', 'en', 'Lecture');
INSERT INTO `lng_data` VALUES ('meta', 'meta_lifecycle', 'en', 'Lifecycle');
INSERT INTO `lng_data` VALUES ('meta', 'meta_linear', 'en', 'Linear');
INSERT INTO `lng_data` VALUES ('meta', 'meta_local_file', 'en', 'Local File');
INSERT INTO `lng_data` VALUES ('meta', 'meta_location', 'en', 'Location');
INSERT INTO `lng_data` VALUES ('meta', 'meta_low', 'en', 'Low');
INSERT INTO `lng_data` VALUES ('meta', 'meta_mac_os', 'en', 'MacOS');
INSERT INTO `lng_data` VALUES ('meta', 'meta_manager', 'en', 'Manager');
INSERT INTO `lng_data` VALUES ('meta', 'meta_maximum_version', 'en', 'Maximum Version');
INSERT INTO `lng_data` VALUES ('meta', 'meta_medium', 'en', 'Medium');
INSERT INTO `lng_data` VALUES ('meta', 'meta_meta_metadata', 'en', 'Meta-Metadata');
INSERT INTO `lng_data` VALUES ('meta', 'meta_metadatascheme', 'en', 'Metadata Scheme');
INSERT INTO `lng_data` VALUES ('meta', 'meta_minimum_version', 'en', 'Minimum Version');
INSERT INTO `lng_data` VALUES ('meta', 'meta_mixed', 'en', 'Mixed');
INSERT INTO `lng_data` VALUES ('meta', 'meta_ms_windows', 'en', 'MS-Windows');
INSERT INTO `lng_data` VALUES ('meta', 'meta_multi_os', 'en', 'Multi-OS');
INSERT INTO `lng_data` VALUES ('meta', 'meta_name', 'en', 'Name');
INSERT INTO `lng_data` VALUES ('meta', 'meta_narrative_text', 'en', 'Narrative Text');
INSERT INTO `lng_data` VALUES ('meta', 'meta_networked', 'en', 'Networked');
INSERT INTO `lng_data` VALUES ('meta', 'meta_new_element', 'en', 'New Element');
INSERT INTO `lng_data` VALUES ('meta', 'meta_no', 'en', 'No');
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_annotation', 'en', 'No meta data available for the Annotation section.');
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_classification', 'en', 'No meta data available for the Classification section.');
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_educational', 'en', 'No meta data available for Educational section.');
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_lifecycle', 'en', 'No meta data available for Lifecycle section.');
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_meta_metadata', 'en', 'No meta data available for Meta-Metadata section.');
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_relation', 'en', 'No meta data available for the Relation section.');
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_rights', 'en', 'No meta data available for the Rights section.');
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_technical', 'en', 'No meta data available for Technical section.');
INSERT INTO `lng_data` VALUES ('meta', 'meta_none', 'en', 'None');
INSERT INTO `lng_data` VALUES ('meta', 'meta_operating_system', 'en', 'Operating System');
INSERT INTO `lng_data` VALUES ('meta', 'meta_or_composite', 'en', 'Or Composite');
INSERT INTO `lng_data` VALUES ('meta', 'meta_other', 'en', 'Other');
INSERT INTO `lng_data` VALUES ('meta', 'meta_other_plattform_requirements', 'en', 'Other Plattform Requirements');
INSERT INTO `lng_data` VALUES ('meta', 'meta_pc_dos', 'en', 'PC-DOS');
INSERT INTO `lng_data` VALUES ('meta', 'meta_please_select', 'en', 'Please select');
INSERT INTO `lng_data` VALUES ('meta', 'meta_prerequisite', 'en', 'Prerequisite');
INSERT INTO `lng_data` VALUES ('meta', 'meta_problem_statement', 'en', 'Problem Statement');
INSERT INTO `lng_data` VALUES ('meta', 'meta_publisher', 'en', 'Publisher');
INSERT INTO `lng_data` VALUES ('meta', 'meta_purpose', 'en', 'Purpose');
INSERT INTO `lng_data` VALUES ('meta', 'meta_questionnaire', 'en', 'Questionnaire');
INSERT INTO `lng_data` VALUES ('meta', 'meta_reference', 'en', 'Reference');
INSERT INTO `lng_data` VALUES ('meta', 'meta_references', 'en', 'References');
INSERT INTO `lng_data` VALUES ('meta', 'meta_relation', 'en', 'Relation');
INSERT INTO `lng_data` VALUES ('meta', 'meta_requirement', 'en', 'Requirement');
INSERT INTO `lng_data` VALUES ('meta', 'meta_requires', 'en', 'Requires');
INSERT INTO `lng_data` VALUES ('meta', 'meta_resource', 'en', 'Resource');
INSERT INTO `lng_data` VALUES ('meta', 'meta_revised', 'en', 'Revised');
INSERT INTO `lng_data` VALUES ('meta', 'meta_rights', 'en', 'Rights');
INSERT INTO `lng_data` VALUES ('meta', 'meta_role', 'en', 'Role');
INSERT INTO `lng_data` VALUES ('meta', 'meta_school', 'en', 'School');
INSERT INTO `lng_data` VALUES ('meta', 'meta_security_level', 'en', 'Security Level');
INSERT INTO `lng_data` VALUES ('meta', 'meta_self_assessment', 'en', 'Self Assessment');
INSERT INTO `lng_data` VALUES ('meta', 'meta_semantic_density', 'en', 'Semantic Density');
INSERT INTO `lng_data` VALUES ('meta', 'meta_simulation', 'en', 'Simulation');
INSERT INTO `lng_data` VALUES ('meta', 'meta_size', 'en', 'Size');
INSERT INTO `lng_data` VALUES ('meta', 'meta_skill_level', 'en', 'Skill Level');
INSERT INTO `lng_data` VALUES ('meta', 'meta_slide', 'en', 'Slide');
INSERT INTO `lng_data` VALUES ('meta', 'meta_source', 'en', 'Source');
INSERT INTO `lng_data` VALUES ('meta', 'meta_status', 'en', 'Status');
INSERT INTO `lng_data` VALUES ('meta', 'meta_structure', 'en', 'Structure');
INSERT INTO `lng_data` VALUES ('meta', 'meta_table', 'en', 'Table');
INSERT INTO `lng_data` VALUES ('meta', 'meta_taxon', 'en', 'Taxon');
INSERT INTO `lng_data` VALUES ('meta', 'meta_taxon_path', 'en', 'Taxon Path');
INSERT INTO `lng_data` VALUES ('meta', 'meta_teacher', 'en', 'Teacher');
INSERT INTO `lng_data` VALUES ('meta', 'meta_technical', 'en', 'Technical');
INSERT INTO `lng_data` VALUES ('meta', 'meta_title', 'en', 'Title');
INSERT INTO `lng_data` VALUES ('meta', 'meta_training', 'en', 'Training');
INSERT INTO `lng_data` VALUES ('meta', 'meta_type', 'en', 'Type');
INSERT INTO `lng_data` VALUES ('meta', 'meta_typical_age_range', 'en', 'Typical Age Range');
INSERT INTO `lng_data` VALUES ('meta', 'meta_typical_learning_time', 'en', 'Typical Learning Time');
INSERT INTO `lng_data` VALUES ('meta', 'meta_unavailable', 'en', 'Unavailable');
INSERT INTO `lng_data` VALUES ('meta', 'meta_unix', 'en', 'Unix');
INSERT INTO `lng_data` VALUES ('meta', 'meta_value', 'en', 'Value');
INSERT INTO `lng_data` VALUES ('meta', 'meta_version', 'en', 'Version');
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_difficult', 'en', 'Very Difficult');
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_easy', 'en', 'Very Easy');
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_high', 'en', 'Very High');
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_low', 'en', 'Very Low');
INSERT INTO `lng_data` VALUES ('meta', 'meta_yes', 'en', 'Yes');
INSERT INTO `lng_data` VALUES ('search', 'search_all_results', 'en', 'All results');
INSERT INTO `lng_data` VALUES ('search', 'search_concatenation', 'en', 'Concatenation');
INSERT INTO `lng_data` VALUES ('search', 'search_content', 'en', 'Page Content');
INSERT INTO `lng_data` VALUES ('search', 'search_dbk_content', 'en', 'Digital Library (content)');
INSERT INTO `lng_data` VALUES ('search', 'search_dbk_meta', 'en', 'Digital Library (meta data)');
INSERT INTO `lng_data` VALUES ('search', 'search_delete_sure', 'en', 'The object and its content will be deleted permanantly');
INSERT INTO `lng_data` VALUES ('search', 'search_group', 'en', 'Groups');
INSERT INTO `lng_data` VALUES ('search', 'search_in_result', 'en', 'Search within results');
INSERT INTO `lng_data` VALUES ('search', 'search_lm_content', 'en', 'Learning materials (content)');
INSERT INTO `lng_data` VALUES ('search', 'search_lm_meta', 'en', 'Learning materials (meta data)');
INSERT INTO `lng_data` VALUES ('search', 'search_meta', 'en', 'Meta data');
INSERT INTO `lng_data` VALUES ('search', 'search_minimum_three', 'en', 'Your search has to be at least three characters long');
INSERT INTO `lng_data` VALUES ('search', 'search_move_folder_not_allowed', 'en', 'Folder cannot be moved.');
INSERT INTO `lng_data` VALUES ('search', 'search_move_to', 'en', 'Move to:');
INSERT INTO `lng_data` VALUES ('search', 'search_my_search_results', 'en', 'My search results');
INSERT INTO `lng_data` VALUES ('search', 'search_new_folder', 'en', 'New folder');
INSERT INTO `lng_data` VALUES ('search', 'search_no_category', 'en', 'You haven\'t selected any search category');
INSERT INTO `lng_data` VALUES ('search', 'search_no_match', 'en', 'Your search didn\'t match any results');
INSERT INTO `lng_data` VALUES ('search', 'search_no_results_saved', 'en', 'No results saved.');
INSERT INTO `lng_data` VALUES ('search', 'search_no_search_term', 'en', 'You haven\'t selected any search term');
INSERT INTO `lng_data` VALUES ('search', 'search_no_selection', 'en', 'You did no selection.');
INSERT INTO `lng_data` VALUES ('search', 'search_object_renamed', 'en', 'Object renamed.');
INSERT INTO `lng_data` VALUES ('search', 'search_objects_deleted', 'en', 'Object(s) deleted.');
INSERT INTO `lng_data` VALUES ('search', 'search_objects_moved', 'en', 'Objects moved.');
INSERT INTO `lng_data` VALUES ('search', 'search_one_action', 'en', 'Select one action.');
INSERT INTO `lng_data` VALUES ('search', 'search_rename_title', 'en', 'Rename title');
INSERT INTO `lng_data` VALUES ('search', 'search_results_saved', 'en', 'Search results saved');
INSERT INTO `lng_data` VALUES ('search', 'search_save_as_select', 'en', 'Save as:');
INSERT INTO `lng_data` VALUES ('search', 'search_search_for', 'en', 'Search for');
INSERT INTO `lng_data` VALUES ('search', 'search_search_no_results_saved', 'en', 'There are no search results saved');
INSERT INTO `lng_data` VALUES ('search', 'search_search_results', 'en', 'Search results');
INSERT INTO `lng_data` VALUES ('search', 'search_search_term', 'en', 'Search term');
INSERT INTO `lng_data` VALUES ('search', 'search_select_exactly_one_object', 'en', 'You have to select exactly one object.');
INSERT INTO `lng_data` VALUES ('search', 'search_select_one', 'en', 'Select one folder');
INSERT INTO `lng_data` VALUES ('search', 'search_select_one_action', 'en', '--Select one action--');
INSERT INTO `lng_data` VALUES ('search', 'search_select_one_folder_select', 'en', '--Select one folder--');
INSERT INTO `lng_data` VALUES ('search', 'search_select_one_result', 'en', 'Select at least one search result');
INSERT INTO `lng_data` VALUES ('search', 'search_select_one_select', 'en', '--Select one folder--');
INSERT INTO `lng_data` VALUES ('search', 'search_show_result', 'en', 'Show');
INSERT INTO `lng_data` VALUES ('search', 'search_user', 'en', 'Users');
# --------------------------------------------------------

#
# Table structure for table `lo_access`
#

CREATE TABLE `lo_access` (
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `usr_id` int(11) NOT NULL default '0',
  `lm_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `lm_title` varchar(200) NOT NULL default ''
) TYPE=MyISAM;

#
# Dumping data for table `lo_access`
#

# --------------------------------------------------------

#
# Table structure for table `mail`
#

CREATE TABLE `mail` (
  `mail_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `folder_id` int(11) NOT NULL default '0',
  `sender_id` int(11) default NULL,
  `attachments` varchar(255) default NULL,
  `send_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `rcp_to` varchar(255) default NULL,
  `rcp_cc` varchar(255) default NULL,
  `rcp_bcc` varchar(255) default NULL,
  `m_status` varchar(16) default NULL,
  `m_type` varchar(255) default NULL,
  `m_email` tinyint(1) default NULL,
  `m_subject` varchar(255) default NULL,
  `m_message` text,
  `import_name` text,
  PRIMARY KEY  (`mail_id`)
) TYPE=MyISAM;

#
# Dumping data for table `mail`
#

# --------------------------------------------------------

#
# Table structure for table `mail_attachment`
#

CREATE TABLE `mail_attachment` (
  `mail_id` int(11) NOT NULL default '0',
  `path` text NOT NULL,
  PRIMARY KEY  (`mail_id`)
) TYPE=MyISAM;

#
# Dumping data for table `mail_attachment`
#

# --------------------------------------------------------

#
# Table structure for table `mail_obj_data`
#

CREATE TABLE `mail_obj_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` char(70) NOT NULL default '',
  `type` char(16) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`user_id`)
) TYPE=MyISAM;

#
# Dumping data for table `mail_obj_data`
#

INSERT INTO `mail_obj_data` VALUES (2, 6, 'a_root', 'root');
INSERT INTO `mail_obj_data` VALUES (3, 6, 'b_inbox', 'inbox');
INSERT INTO `mail_obj_data` VALUES (4, 6, 'c_trash', 'trash');
INSERT INTO `mail_obj_data` VALUES (5, 6, 'd_drafts', 'drafts');
INSERT INTO `mail_obj_data` VALUES (6, 6, 'e_sent', 'sent');
INSERT INTO `mail_obj_data` VALUES (7, 6, 'z_local', 'local');
# --------------------------------------------------------

#
# Table structure for table `mail_options`
#

CREATE TABLE `mail_options` (
  `user_id` int(11) NOT NULL default '0',
  `linebreak` tinyint(4) NOT NULL default '0',
  `signature` text NOT NULL,
  `incoming_type` tinyint(3) default NULL,
  KEY `user_id` (`user_id`,`linebreak`)
) TYPE=MyISAM;

#
# Dumping data for table `mail_options`
#

INSERT INTO `mail_options` VALUES (6, 60, '', NULL);
# --------------------------------------------------------

#
# Table structure for table `mail_saved`
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
# Dumping data for table `mail_saved`
#

# --------------------------------------------------------

#
# Table structure for table `mail_tree`
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
# Dumping data for table `mail_tree`
#

INSERT INTO `mail_tree` VALUES (6, 2, 0, 1, 12, 1);
INSERT INTO `mail_tree` VALUES (6, 3, 2, 2, 3, 2);
INSERT INTO `mail_tree` VALUES (6, 4, 2, 4, 5, 2);
INSERT INTO `mail_tree` VALUES (6, 5, 2, 6, 7, 2);
INSERT INTO `mail_tree` VALUES (6, 6, 2, 8, 9, 2);
INSERT INTO `mail_tree` VALUES (6, 7, 2, 10, 11, 2);
# --------------------------------------------------------

#
# Table structure for table `map_area`
#

CREATE TABLE `map_area` (
  `item_id` int(11) NOT NULL default '0',
  `nr` int(11) NOT NULL default '0',
  `shape` varchar(20) default NULL,
  `coords` varchar(200) default NULL,
  `link_type` char(3) default NULL,
  `title` varchar(200) default NULL,
  `href` varchar(200) default NULL,
  `target` varchar(50) default NULL,
  `type` varchar(20) default NULL,
  `target_frame` varchar(50) default NULL,
  PRIMARY KEY  (`item_id`,`nr`)
) TYPE=MyISAM;

#
# Dumping data for table `map_area`
#

# --------------------------------------------------------

#
# Table structure for table `media_item`
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
  `param` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `media_item`
#

# --------------------------------------------------------

#
# Table structure for table `mep_tree`
#

CREATE TABLE `mep_tree` (
  `mep_id` int(11) NOT NULL default '0',
  `child` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `depth` smallint(6) NOT NULL default '0'
) TYPE=MyISAM;

#
# Dumping data for table `mep_tree`
#

# --------------------------------------------------------

#
# Table structure for table `meta_data`
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
# Dumping data for table `meta_data`
#

# --------------------------------------------------------

#
# Table structure for table `meta_keyword`
#

CREATE TABLE `meta_keyword` (
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` char(3) NOT NULL default '',
  `language` char(2) NOT NULL default '',
  `keyword` varchar(200) NOT NULL default ''
) TYPE=MyISAM;

#
# Dumping data for table `meta_keyword`
#

# --------------------------------------------------------

#
# Table structure for table `meta_techn_format`
#

CREATE TABLE `meta_techn_format` (
  `tech_id` int(11) NOT NULL default '0',
  `format` varchar(150) default NULL,
  `nr` int(11) default NULL,
  KEY `tech_id` (`tech_id`)
) TYPE=MyISAM;

#
# Dumping data for table `meta_techn_format`
#

# --------------------------------------------------------

#
# Table structure for table `meta_techn_loc`
#

CREATE TABLE `meta_techn_loc` (
  `tech_id` int(11) NOT NULL default '0',
  `location` varchar(150) default NULL,
  `nr` int(11) default NULL,
  `type` enum('LocalFile','Reference') NOT NULL default 'LocalFile',
  KEY `tech_id` (`tech_id`)
) TYPE=MyISAM;

#
# Dumping data for table `meta_techn_loc`
#

# --------------------------------------------------------

#
# Table structure for table `meta_technical`
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
# Dumping data for table `meta_technical`
#

# --------------------------------------------------------

#
# Table structure for table `mob_parameter`
#

CREATE TABLE `mob_parameter` (
  `med_item_id` int(11) NOT NULL default '0',
  `name` varchar(50) default NULL,
  `value` text,
  KEY `mob_id` (`med_item_id`)
) TYPE=MyISAM;

#
# Dumping data for table `mob_parameter`
#

# --------------------------------------------------------

#
# Table structure for table `mob_usage`
#

CREATE TABLE `mob_usage` (
  `id` int(11) NOT NULL default '0',
  `usage_type` varchar(10) NOT NULL default '',
  `usage_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`,`usage_type`,`usage_id`)
) TYPE=MyISAM;

#
# Dumping data for table `mob_usage`
#

# --------------------------------------------------------

#
# Table structure for table `note_data`
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
# Dumping data for table `note_data`
#

# --------------------------------------------------------

#
# Table structure for table `object_data`
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
# Dumping data for table `object_data`
#

INSERT INTO `object_data` VALUES (2, 'role', 'Administrator', 'Role for systemadministrators. This role grants access to everything!', -1, '2002-01-16 15:31:45', '2003-08-15 13:18:57', '');
INSERT INTO `object_data` VALUES (3, 'rolt', 'Author', 'Role template for authors with write & create permissions.', -1, '2002-01-16 15:32:50', '2004-01-20 12:21:17', '');
INSERT INTO `object_data` VALUES (4, 'role', 'User', 'Standard role for registered users. Grants read access to most objects.', -1, '2002-01-16 15:34:00', '2004-01-20 12:19:02', '');
INSERT INTO `object_data` VALUES (5, 'role', 'Guest', 'Role grants only a few visible & read permissions.', -1, '2002-01-16 15:34:46', '2004-01-20 12:16:42', '');
INSERT INTO `object_data` VALUES (6, 'usr', 'root user', 'ilias@yourserver.com', -1, '2002-01-16 16:09:22', '2003-09-30 19:50:01', '');
INSERT INTO `object_data` VALUES (7, 'usrf', 'User accounts', 'Manage user accounts here.', -1, '2002-06-27 09:24:06', '2004-01-20 12:23:47', '');
INSERT INTO `object_data` VALUES (8, 'rolf', 'Roles', 'Manage your roles here.', -1, '2002-06-27 09:24:06', '2004-01-20 12:23:40', '');
INSERT INTO `object_data` VALUES (1, 'root', 'ILIAS', 'This is the root node of the system!!!', -1, '2002-06-24 15:15:03', '2004-01-20 12:24:12', '');
INSERT INTO `object_data` VALUES (9, 'adm', 'System Settings', 'Folder contains the systems settings', -1, '2002-07-15 12:37:33', '2002-07-15 12:37:33', '');
INSERT INTO `object_data` VALUES (10, 'objf', 'Objectdefinitions', 'Manage ILIAS object types and object permissions. (only for experts!)', -1, '2002-07-15 12:36:56', '2004-01-20 12:23:53', '');
INSERT INTO `object_data` VALUES (11, 'lngf', 'Languages', 'Manage your system languages here.', -1, '2002-07-15 15:52:51', '2004-01-20 12:24:06', '');
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
INSERT INTO `object_data` VALUES (70, 'lng', 'en', 'installed', -1, '0000-00-00 00:00:00', '2004-01-20 12:22:27', '');
INSERT INTO `object_data` VALUES (14, 'role', 'Anonymous', 'Default role for anonymous users (with no account)', -1, '2003-08-15 12:06:19', '2004-01-20 12:15:45', '');
INSERT INTO `object_data` VALUES (18, 'typ', 'mob', 'Multimedia object', -1, '0000-00-00 00:00:00', '2003-08-15 12:03:20', '');
INSERT INTO `object_data` VALUES (35, 'typ', 'notf', 'Note Folder Object', -1, '2002-12-21 00:04:00', '2002-12-21 00:04:00', '');
INSERT INTO `object_data` VALUES (36, 'typ', 'note', 'Note Object', -1, '2002-12-21 00:04:00', '2002-12-21 00:04:00', '');
INSERT INTO `object_data` VALUES (12, 'mail', 'Mail Settings', 'Configure global mail settings here.', -1, '2003-08-15 10:07:28', '2004-01-20 12:24:00', '');
INSERT INTO `object_data` VALUES (20, 'typ', 'slm', 'SCORM Learning Module', -1, '2003-08-15 10:07:28', '2003-08-15 12:23:10', '');
INSERT INTO `object_data` VALUES (80, 'rolt', 'il_grp_admin', 'Administrator role template of groups', -1, '2003-08-15 10:07:28', '2004-01-20 12:12:50', '');
INSERT INTO `object_data` VALUES (81, 'rolt', 'il_grp_member', 'Member role template of groups', -1, '2003-08-15 10:07:28', '2004-01-20 12:14:45', '');
INSERT INTO `object_data` VALUES (82, 'rolt', 'il_grp_status_closed', 'Group role template', -1, '2003-08-15 10:07:29', '2003-08-15 13:21:38', '');
INSERT INTO `object_data` VALUES (83, 'rolt', 'il_grp_status_open', 'Group role template', -1, '2003-08-15 10:07:29', '2003-08-15 13:21:25', '');
INSERT INTO `object_data` VALUES (32, 'typ', 'glo', 'Glossary', -1, '2003-08-15 10:07:30', '2003-08-15 12:29:54', '');
INSERT INTO `object_data` VALUES (13, 'usr', 'Anonymous', 'Anonymous user account. DO NOT DELETE!', -1, '2003-08-15 10:07:30', '2003-08-15 10:07:30', '');
INSERT INTO `object_data` VALUES (71, 'lng', 'de', 'not_installed', 6, '2003-08-15 10:25:19', '2003-09-30 19:50:06', '');
INSERT INTO `object_data` VALUES (72, 'lng', 'es', 'not_installed', 6, '2003-08-15 10:25:19', '2003-08-15 10:25:19', '');
INSERT INTO `object_data` VALUES (73, 'lng', 'it', 'not_installed', 6, '2003-08-15 10:25:19', '2003-08-15 10:25:19', '');
INSERT INTO `object_data` VALUES (84, 'typ', 'exc', 'Exercise object', -1, '2003-11-30 21:22:49', '2003-11-30 21:22:49', '');
INSERT INTO `object_data` VALUES (85, 'typ', 'ldap', 'LDAP settings object', -1, '2003-11-30 21:22:49', '2003-11-30 21:22:49', '');
INSERT INTO `object_data` VALUES (86, 'ldap', 'LDAP settings', 'Folder contains all LDAP settings', -1, '2003-11-30 21:22:49', '2003-11-30 21:22:49', '');
INSERT INTO `object_data` VALUES (87, 'typ', 'fold', 'Folder object', -1, '2003-11-30 21:22:50', '2003-11-30 21:22:50', '');
INSERT INTO `object_data` VALUES (88, 'typ', 'file', 'File object', -1, '2003-11-30 21:22:50', '2003-11-30 21:22:50', '');
INSERT INTO `object_data` VALUES (89, 'lng', 'fr', 'not_installed', 6, '2004-01-20 12:22:17', '2004-01-20 12:22:17', '');
INSERT INTO `object_data` VALUES (90, 'lng', 'nl', 'not_installed', 6, '2004-01-20 12:22:17', '2004-01-20 12:22:17', '');
INSERT INTO `object_data` VALUES (91, 'lng', 'pl', 'not_installed', 6, '2004-01-20 12:22:17', '2004-01-20 12:22:17', '');
INSERT INTO `object_data` VALUES (92, 'lng', 'ua', 'not_installed', 6, '2004-01-20 12:22:17', '2004-01-20 12:22:17', '');
INSERT INTO `object_data` VALUES (93, 'lng', 'zh', 'not_installed', 6, '2004-01-20 12:22:17', '2004-01-20 12:22:17', '');
INSERT INTO `object_data` VALUES (94, 'typ', 'tst', 'Test object', -1, '2004-02-18 21:17:40', '2004-02-18 21:17:40', '');
INSERT INTO `object_data` VALUES (95, 'typ', 'qpl', 'Question pool object', -1, '2004-02-18 21:17:40', '2004-02-18 21:17:40', '');
INSERT INTO `object_data` VALUES (96, 'typ', 'chat', 'Chat object', -1, '2004-02-18 21:17:40', '2004-02-18 21:17:40', '');
INSERT INTO `object_data` VALUES (97, 'typ', 'chac', 'Chat server config object', -1, '2004-02-18 21:17:40', '2004-02-18 21:17:40', '');
INSERT INTO `object_data` VALUES (98, 'chac', 'Chat server settings', 'Configure chat server settings here', -1, '2004-02-18 21:17:40', '2004-02-18 21:17:40', '');
INSERT INTO `object_data` VALUES (99, 'typ', 'recf', 'RecoveryFolder object', -1, '2004-03-09 18:13:16', '2004-03-09 18:13:16', '');
INSERT INTO `object_data` VALUES (100, 'recf', '__Restored Objects', 'Contains objects restored by recovery tool', -1, '2004-03-09 18:13:16', '2004-03-09 18:13:16', '');
INSERT INTO `object_data` VALUES (101, 'typ', 'mep', 'Media pool object', -1, '2004-04-19 00:09:14', '2004-04-19 00:09:14', '');
INSERT INTO `object_data` VALUES (102, 'typ', 'htlm', 'HTML LM object', -1, '2004-04-19 00:09:15', '2004-04-19 00:09:15', '');
INSERT INTO `object_data` VALUES (103, 'typ', 'svy', 'Survey object', -1, '2004-05-15 01:18:59', '2004-05-15 01:18:59', '');
INSERT INTO `object_data` VALUES (104, 'typ', 'spl', 'Question pool object (Survey)', -1, '2004-05-15 01:18:59', '2004-05-15 01:18:59', '');
INSERT INTO `object_data` VALUES (105, 'typ', 'tax', 'Taxonomy object', -1, '2004-06-21 01:27:18', '2004-06-21 01:27:18', '');
INSERT INTO `object_data` VALUES (106, 'typ', 'taxf', 'Taxonomy folder object', -1, '2004-06-21 01:27:18', '2004-06-21 01:27:18', '');
INSERT INTO `object_data` VALUES (107, 'taxf', 'Taxonomy folder', 'Configure taxonomy settings here', -1, '2004-06-21 01:27:18', '2004-06-21 01:27:18', '');
INSERT INTO `object_data` VALUES (108, 'typ', 'trac', 'UserTracking object', -1, '2004-07-11 01:03:12', '2004-07-11 01:03:12', '');
INSERT INTO `object_data` VALUES (109, 'trac', '__User Tracking', 'System user tracking', -1, '2004-07-11 01:03:12', '2004-07-11 01:03:12', '');
# --------------------------------------------------------

#
# Table structure for table `object_reference`
#

CREATE TABLE `object_reference` (
  `ref_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ref_id`)
) TYPE=MyISAM;

#
# Dumping data for table `object_reference`
#

INSERT INTO `object_reference` VALUES (1, 1);
INSERT INTO `object_reference` VALUES (7, 7);
INSERT INTO `object_reference` VALUES (8, 8);
INSERT INTO `object_reference` VALUES (9, 9);
INSERT INTO `object_reference` VALUES (10, 10);
INSERT INTO `object_reference` VALUES (11, 11);
INSERT INTO `object_reference` VALUES (12, 12);
INSERT INTO `object_reference` VALUES (13, 86);
INSERT INTO `object_reference` VALUES (14, 98);
INSERT INTO `object_reference` VALUES (15, 100);
INSERT INTO `object_reference` VALUES (16, 107);
INSERT INTO `object_reference` VALUES (17, 109);
# --------------------------------------------------------

#
# Table structure for table `object_translation`
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
# Dumping data for table `object_translation`
#

INSERT INTO `object_translation` VALUES (9, 'Open Source eLearning', '', 'en', 1);
# --------------------------------------------------------

#
# Table structure for table `page_object`
#

CREATE TABLE `page_object` (
  `page_id` int(11) NOT NULL default '0',
  `parent_id` int(11) default NULL,
  `content` mediumtext,
  `parent_type` varchar(4) NOT NULL default 'lm',
  PRIMARY KEY  (`page_id`,`parent_type`),
  FULLTEXT KEY `content` (`content`)
) TYPE=MyISAM;

#
# Dumping data for table `page_object`
#

# --------------------------------------------------------

#
# Table structure for table `personal_clipboard`
#

CREATE TABLE `personal_clipboard` (
  `user_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  `type` char(4) NOT NULL default '',
  `title` char(70) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`item_id`,`type`)
) TYPE=MyISAM;

#
# Dumping data for table `personal_clipboard`
#

# --------------------------------------------------------

#
# Table structure for table `qpl_answers`
#

CREATE TABLE `qpl_answers` (
  `answer_id` int(10) unsigned NOT NULL auto_increment,
  `question_fi` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `shuffle` enum('0','1') NOT NULL default '1',
  `answertext` varchar(100) NOT NULL default '',
  `points` double NOT NULL default '0',
  `aorder` int(10) unsigned NOT NULL default '0',
  `correctness` enum('0','1') NOT NULL default '0',
  `solution_order` int(10) unsigned NOT NULL default '0',
  `matchingtext` varchar(100) default NULL,
  `matching_order` int(10) unsigned default NULL,
  `gap_id` int(10) unsigned NOT NULL default '0',
  `cloze_type` enum('0','1') default NULL,
  `coords` text,
  `area` varchar(20) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`answer_id`),
  UNIQUE KEY `answer_id` (`answer_id`),
  KEY `answer_id_2` (`answer_id`)
) TYPE=MyISAM;

#
# Dumping data for table `qpl_answers`
#

# --------------------------------------------------------

#
# Table structure for table `qpl_question_material`
#

CREATE TABLE `qpl_question_material` (
  `material_id` int(11) NOT NULL auto_increment,
  `question_id` int(11) NOT NULL default '0',
  `materials` text,
  `materials_file` text,
  `TIMESTAMP` timestamp(14) NOT NULL,
  UNIQUE KEY `material_id` (`material_id`)
) TYPE=MyISAM;

#
# Dumping data for table `qpl_question_material`
#

# --------------------------------------------------------

#
# Table structure for table `qpl_question_type`
#

CREATE TABLE `qpl_question_type` (
  `question_type_id` int(3) unsigned NOT NULL auto_increment,
  `type_tag` char(25) NOT NULL default '',
  PRIMARY KEY  (`question_type_id`),
  UNIQUE KEY `question_type_id` (`question_type_id`),
  KEY `question_type_id_2` (`question_type_id`)
) TYPE=MyISAM;

#
# Dumping data for table `qpl_question_type`
#

INSERT INTO `qpl_question_type` VALUES (1, 'qt_multiple_choice_sr');
INSERT INTO `qpl_question_type` VALUES (2, 'qt_multiple_choice_mr');
INSERT INTO `qpl_question_type` VALUES (3, 'qt_cloze');
INSERT INTO `qpl_question_type` VALUES (4, 'qt_matching');
INSERT INTO `qpl_question_type` VALUES (5, 'qt_ordering');
INSERT INTO `qpl_question_type` VALUES (6, 'qt_imagemap');
INSERT INTO `qpl_question_type` VALUES (7, 'qt_javaapplet');
# --------------------------------------------------------

#
# Table structure for table `qpl_questions`
#

CREATE TABLE `qpl_questions` (
  `question_id` int(11) NOT NULL auto_increment,
  `question_type_fi` int(10) unsigned NOT NULL default '0',
  `ref_fi` int(10) unsigned NOT NULL default '0',
  `title` varchar(100) NOT NULL default '',
  `comment` text,
  `author` varchar(50) NOT NULL default '',
  `owner` int(11) NOT NULL default '0',
  `question_text` text NOT NULL,
  `working_time` time NOT NULL default '00:00:00',
  `shuffle` enum('0','1') NOT NULL default '1',
  `points` double default NULL,
  `start_tag` varchar(5) default NULL,
  `end_tag` varchar(5) default NULL,
  `matching_type` enum('0','1') default NULL,
  `ordering_type` enum('0','1') default NULL,
  `cloze_type` enum('0','1') default NULL,
  `choice_response` enum('0','1') default NULL,
  `materials` text,
  `image_file` varchar(100) default NULL,
  `params` text,
  `complete` enum('0','1') NOT NULL default '1',
  `created` varchar(14) NOT NULL default '',
  `original_id` int(11) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`question_id`)
) TYPE=MyISAM;

#
# Dumping data for table `qpl_questions`
#

# --------------------------------------------------------

#
# Table structure for table `rbac_fa`
#

CREATE TABLE `rbac_fa` (
  `rol_id` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `assign` enum('y','n') default NULL,
  PRIMARY KEY  (`rol_id`,`parent`)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_fa`
#

INSERT INTO `rbac_fa` VALUES (2, 8, 'y');
INSERT INTO `rbac_fa` VALUES (3, 8, 'n');
INSERT INTO `rbac_fa` VALUES (4, 8, 'y');
INSERT INTO `rbac_fa` VALUES (5, 8, 'y');
INSERT INTO `rbac_fa` VALUES (83, 8, 'n');
INSERT INTO `rbac_fa` VALUES (82, 8, 'n');
INSERT INTO `rbac_fa` VALUES (80, 8, 'n');
INSERT INTO `rbac_fa` VALUES (81, 8, 'n');
INSERT INTO `rbac_fa` VALUES (14, 8, 'y');
# --------------------------------------------------------

#
# Table structure for table `rbac_operations`
#

CREATE TABLE `rbac_operations` (
  `ops_id` int(11) NOT NULL auto_increment,
  `operation` char(32) NOT NULL default '',
  `description` char(255) default NULL,
  PRIMARY KEY  (`ops_id`),
  UNIQUE KEY `operation` (`operation`)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_operations`
#

INSERT INTO `rbac_operations` VALUES (1, 'edit_permission', 'edit permissions');
INSERT INTO `rbac_operations` VALUES (2, 'visible', 'view object');
INSERT INTO `rbac_operations` VALUES (3, 'read', 'access object');
INSERT INTO `rbac_operations` VALUES (4, 'write', 'modify object');
INSERT INTO `rbac_operations` VALUES (6, 'delete', 'remove object');
INSERT INTO `rbac_operations` VALUES (7, 'join', 'join/subscribe');
INSERT INTO `rbac_operations` VALUES (8, 'leave', 'leave/unsubscribe');
INSERT INTO `rbac_operations` VALUES (9, 'edit_post', 'edit forum articles');
INSERT INTO `rbac_operations` VALUES (10, 'delete_post', 'delete forum articles');
INSERT INTO `rbac_operations` VALUES (11, 'smtp_mail', 'send external mail');
INSERT INTO `rbac_operations` VALUES (12, 'system_message', 'allow to send system messages');
INSERT INTO `rbac_operations` VALUES (13, 'create_user', 'create new user account');
INSERT INTO `rbac_operations` VALUES (14, 'create_role', 'create new role definition');
INSERT INTO `rbac_operations` VALUES (15, 'create_rolt', 'create new role definition template');
INSERT INTO `rbac_operations` VALUES (16, 'create_cat', 'create new category');
INSERT INTO `rbac_operations` VALUES (17, 'create_grp', 'create new group');
INSERT INTO `rbac_operations` VALUES (18, 'create_frm', 'create new forum');
INSERT INTO `rbac_operations` VALUES (19, 'create_crs', 'create new course');
INSERT INTO `rbac_operations` VALUES (20, 'create_lm', 'create new learning module');
INSERT INTO `rbac_operations` VALUES (21, 'create_slm', 'create new SCORM learning module');
INSERT INTO `rbac_operations` VALUES (22, 'create_glo', 'create new glossary');
INSERT INTO `rbac_operations` VALUES (23, 'create_dbk', 'create new digibook');
INSERT INTO `rbac_operations` VALUES (24, 'create_exc', 'create new exercise');
INSERT INTO `rbac_operations` VALUES (25, 'create_file', 'upload new file');
INSERT INTO `rbac_operations` VALUES (26, 'create_fold', 'create new folder');
INSERT INTO `rbac_operations` VALUES (40, 'edit_userassignment', 'change userassignment of roles');
INSERT INTO `rbac_operations` VALUES (41, 'edit_roleassignment', 'change roleassignments of user accounts');
INSERT INTO `rbac_operations` VALUES (27, 'create_tst', 'create new test');
INSERT INTO `rbac_operations` VALUES (28, 'create_qpl', 'create new question pool');
INSERT INTO `rbac_operations` VALUES (29, 'create_chat', 'create chat object');
INSERT INTO `rbac_operations` VALUES (30, 'mail_visible', 'users can use mail system');
INSERT INTO `rbac_operations` VALUES (31, 'create_mep', 'create new media pool');
INSERT INTO `rbac_operations` VALUES (32, 'create_htlm', 'create new html learning module');
INSERT INTO `rbac_operations` VALUES (42, 'create_svy', 'create new survey');
INSERT INTO `rbac_operations` VALUES (43, 'create_spl', 'create new question pool (Survey)');
INSERT INTO `rbac_operations` VALUES (44, 'create_tax', 'create taxonomy object');
INSERT INTO `rbac_operations` VALUES (45, 'invite', 'invite');
INSERT INTO `rbac_operations` VALUES (46, 'participate', 'participate');
# --------------------------------------------------------

#
# Table structure for table `rbac_pa`
#

CREATE TABLE `rbac_pa` (
  `rol_id` int(11) NOT NULL default '0',
  `ops_id` text NOT NULL,
  `ref_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rol_id`,`ref_id`)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_pa`
#

INSERT INTO `rbac_pa` VALUES (5, 'a:2:{i:0;i:3;i:1;i:2;}', 1);
INSERT INTO `rbac_pa` VALUES (14, 'a:2:{i:0;i:3;i:1;i:2;}', 1);
INSERT INTO `rbac_pa` VALUES (4, 'a:2:{i:0;i:3;i:1;i:2;}', 1);
# --------------------------------------------------------

#
# Table structure for table `rbac_ta`
#

CREATE TABLE `rbac_ta` (
  `typ_id` smallint(6) NOT NULL default '0',
  `ops_id` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`typ_id`,`ops_id`)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_ta`
#

INSERT INTO `rbac_ta` VALUES (15, 1);
INSERT INTO `rbac_ta` VALUES (15, 2);
INSERT INTO `rbac_ta` VALUES (15, 3);
INSERT INTO `rbac_ta` VALUES (15, 4);
INSERT INTO `rbac_ta` VALUES (15, 6);
INSERT INTO `rbac_ta` VALUES (15, 7);
INSERT INTO `rbac_ta` VALUES (15, 8);
INSERT INTO `rbac_ta` VALUES (15, 18);
INSERT INTO `rbac_ta` VALUES (15, 20);
INSERT INTO `rbac_ta` VALUES (15, 21);
INSERT INTO `rbac_ta` VALUES (15, 22);
INSERT INTO `rbac_ta` VALUES (15, 23);
INSERT INTO `rbac_ta` VALUES (15, 24);
INSERT INTO `rbac_ta` VALUES (15, 25);
INSERT INTO `rbac_ta` VALUES (15, 26);
INSERT INTO `rbac_ta` VALUES (15, 29);
INSERT INTO `rbac_ta` VALUES (15, 31);
INSERT INTO `rbac_ta` VALUES (15, 32);
INSERT INTO `rbac_ta` VALUES (16, 1);
INSERT INTO `rbac_ta` VALUES (16, 2);
INSERT INTO `rbac_ta` VALUES (16, 3);
INSERT INTO `rbac_ta` VALUES (16, 4);
INSERT INTO `rbac_ta` VALUES (16, 6);
INSERT INTO `rbac_ta` VALUES (16, 16);
INSERT INTO `rbac_ta` VALUES (16, 17);
INSERT INTO `rbac_ta` VALUES (16, 18);
INSERT INTO `rbac_ta` VALUES (16, 19);
INSERT INTO `rbac_ta` VALUES (16, 20);
INSERT INTO `rbac_ta` VALUES (16, 21);
INSERT INTO `rbac_ta` VALUES (16, 22);
INSERT INTO `rbac_ta` VALUES (16, 23);
INSERT INTO `rbac_ta` VALUES (16, 24);
INSERT INTO `rbac_ta` VALUES (16, 27);
INSERT INTO `rbac_ta` VALUES (16, 28);
INSERT INTO `rbac_ta` VALUES (16, 29);
INSERT INTO `rbac_ta` VALUES (16, 31);
INSERT INTO `rbac_ta` VALUES (16, 32);
INSERT INTO `rbac_ta` VALUES (16, 42);
INSERT INTO `rbac_ta` VALUES (16, 43);
INSERT INTO `rbac_ta` VALUES (17, 1);
INSERT INTO `rbac_ta` VALUES (17, 2);
INSERT INTO `rbac_ta` VALUES (17, 3);
INSERT INTO `rbac_ta` VALUES (17, 4);
INSERT INTO `rbac_ta` VALUES (17, 6);
INSERT INTO `rbac_ta` VALUES (17, 7);
INSERT INTO `rbac_ta` VALUES (17, 8);
INSERT INTO `rbac_ta` VALUES (17, 18);
INSERT INTO `rbac_ta` VALUES (17, 20);
INSERT INTO `rbac_ta` VALUES (17, 21);
INSERT INTO `rbac_ta` VALUES (17, 22);
INSERT INTO `rbac_ta` VALUES (19, 1);
INSERT INTO `rbac_ta` VALUES (19, 2);
INSERT INTO `rbac_ta` VALUES (19, 3);
INSERT INTO `rbac_ta` VALUES (19, 4);
INSERT INTO `rbac_ta` VALUES (19, 11);
INSERT INTO `rbac_ta` VALUES (19, 12);
INSERT INTO `rbac_ta` VALUES (19, 30);
INSERT INTO `rbac_ta` VALUES (20, 1);
INSERT INTO `rbac_ta` VALUES (20, 2);
INSERT INTO `rbac_ta` VALUES (20, 3);
INSERT INTO `rbac_ta` VALUES (20, 4);
INSERT INTO `rbac_ta` VALUES (20, 6);
INSERT INTO `rbac_ta` VALUES (20, 7);
INSERT INTO `rbac_ta` VALUES (20, 8);
INSERT INTO `rbac_ta` VALUES (21, 1);
INSERT INTO `rbac_ta` VALUES (21, 2);
INSERT INTO `rbac_ta` VALUES (21, 3);
INSERT INTO `rbac_ta` VALUES (21, 4);
INSERT INTO `rbac_ta` VALUES (21, 44);
INSERT INTO `rbac_ta` VALUES (22, 1);
INSERT INTO `rbac_ta` VALUES (22, 2);
INSERT INTO `rbac_ta` VALUES (22, 3);
INSERT INTO `rbac_ta` VALUES (22, 4);
INSERT INTO `rbac_ta` VALUES (22, 6);
INSERT INTO `rbac_ta` VALUES (22, 13);
INSERT INTO `rbac_ta` VALUES (22, 41);
INSERT INTO `rbac_ta` VALUES (23, 1);
INSERT INTO `rbac_ta` VALUES (23, 2);
INSERT INTO `rbac_ta` VALUES (23, 3);
INSERT INTO `rbac_ta` VALUES (23, 4);
INSERT INTO `rbac_ta` VALUES (23, 6);
INSERT INTO `rbac_ta` VALUES (23, 14);
INSERT INTO `rbac_ta` VALUES (23, 15);
INSERT INTO `rbac_ta` VALUES (23, 40);
INSERT INTO `rbac_ta` VALUES (24, 1);
INSERT INTO `rbac_ta` VALUES (24, 2);
INSERT INTO `rbac_ta` VALUES (24, 3);
INSERT INTO `rbac_ta` VALUES (24, 4);
INSERT INTO `rbac_ta` VALUES (28, 1);
INSERT INTO `rbac_ta` VALUES (28, 2);
INSERT INTO `rbac_ta` VALUES (28, 3);
INSERT INTO `rbac_ta` VALUES (31, 1);
INSERT INTO `rbac_ta` VALUES (31, 2);
INSERT INTO `rbac_ta` VALUES (31, 3);
INSERT INTO `rbac_ta` VALUES (31, 4);
INSERT INTO `rbac_ta` VALUES (31, 6);
INSERT INTO `rbac_ta` VALUES (32, 1);
INSERT INTO `rbac_ta` VALUES (32, 2);
INSERT INTO `rbac_ta` VALUES (32, 3);
INSERT INTO `rbac_ta` VALUES (32, 4);
INSERT INTO `rbac_ta` VALUES (32, 6);
INSERT INTO `rbac_ta` VALUES (32, 7);
INSERT INTO `rbac_ta` VALUES (32, 8);
INSERT INTO `rbac_ta` VALUES (33, 1);
INSERT INTO `rbac_ta` VALUES (33, 2);
INSERT INTO `rbac_ta` VALUES (33, 3);
INSERT INTO `rbac_ta` VALUES (33, 4);
INSERT INTO `rbac_ta` VALUES (33, 16);
INSERT INTO `rbac_ta` VALUES (34, 1);
INSERT INTO `rbac_ta` VALUES (34, 2);
INSERT INTO `rbac_ta` VALUES (34, 3);
INSERT INTO `rbac_ta` VALUES (34, 4);
INSERT INTO `rbac_ta` VALUES (34, 6);
INSERT INTO `rbac_ta` VALUES (34, 7);
INSERT INTO `rbac_ta` VALUES (34, 8);
INSERT INTO `rbac_ta` VALUES (37, 1);
INSERT INTO `rbac_ta` VALUES (37, 2);
INSERT INTO `rbac_ta` VALUES (37, 3);
INSERT INTO `rbac_ta` VALUES (37, 4);
INSERT INTO `rbac_ta` VALUES (37, 6);
INSERT INTO `rbac_ta` VALUES (37, 9);
INSERT INTO `rbac_ta` VALUES (37, 10);
INSERT INTO `rbac_ta` VALUES (84, 1);
INSERT INTO `rbac_ta` VALUES (84, 2);
INSERT INTO `rbac_ta` VALUES (84, 3);
INSERT INTO `rbac_ta` VALUES (84, 4);
INSERT INTO `rbac_ta` VALUES (84, 6);
INSERT INTO `rbac_ta` VALUES (85, 1);
INSERT INTO `rbac_ta` VALUES (85, 2);
INSERT INTO `rbac_ta` VALUES (85, 3);
INSERT INTO `rbac_ta` VALUES (85, 4);
INSERT INTO `rbac_ta` VALUES (87, 1);
INSERT INTO `rbac_ta` VALUES (87, 2);
INSERT INTO `rbac_ta` VALUES (87, 3);
INSERT INTO `rbac_ta` VALUES (87, 4);
INSERT INTO `rbac_ta` VALUES (87, 6);
INSERT INTO `rbac_ta` VALUES (87, 18);
INSERT INTO `rbac_ta` VALUES (87, 20);
INSERT INTO `rbac_ta` VALUES (87, 21);
INSERT INTO `rbac_ta` VALUES (87, 22);
INSERT INTO `rbac_ta` VALUES (87, 25);
INSERT INTO `rbac_ta` VALUES (87, 26);
INSERT INTO `rbac_ta` VALUES (88, 1);
INSERT INTO `rbac_ta` VALUES (88, 2);
INSERT INTO `rbac_ta` VALUES (88, 3);
INSERT INTO `rbac_ta` VALUES (88, 4);
INSERT INTO `rbac_ta` VALUES (88, 6);
INSERT INTO `rbac_ta` VALUES (94, 1);
INSERT INTO `rbac_ta` VALUES (94, 2);
INSERT INTO `rbac_ta` VALUES (94, 3);
INSERT INTO `rbac_ta` VALUES (94, 4);
INSERT INTO `rbac_ta` VALUES (94, 6);
INSERT INTO `rbac_ta` VALUES (95, 1);
INSERT INTO `rbac_ta` VALUES (95, 2);
INSERT INTO `rbac_ta` VALUES (95, 3);
INSERT INTO `rbac_ta` VALUES (95, 4);
INSERT INTO `rbac_ta` VALUES (95, 6);
INSERT INTO `rbac_ta` VALUES (96, 1);
INSERT INTO `rbac_ta` VALUES (96, 2);
INSERT INTO `rbac_ta` VALUES (96, 3);
INSERT INTO `rbac_ta` VALUES (96, 4);
INSERT INTO `rbac_ta` VALUES (96, 6);
INSERT INTO `rbac_ta` VALUES (97, 1);
INSERT INTO `rbac_ta` VALUES (97, 2);
INSERT INTO `rbac_ta` VALUES (97, 3);
INSERT INTO `rbac_ta` VALUES (97, 4);
INSERT INTO `rbac_ta` VALUES (101, 1);
INSERT INTO `rbac_ta` VALUES (101, 2);
INSERT INTO `rbac_ta` VALUES (101, 3);
INSERT INTO `rbac_ta` VALUES (101, 4);
INSERT INTO `rbac_ta` VALUES (101, 6);
INSERT INTO `rbac_ta` VALUES (102, 1);
INSERT INTO `rbac_ta` VALUES (102, 2);
INSERT INTO `rbac_ta` VALUES (102, 3);
INSERT INTO `rbac_ta` VALUES (102, 4);
INSERT INTO `rbac_ta` VALUES (102, 6);
INSERT INTO `rbac_ta` VALUES (103, 1);
INSERT INTO `rbac_ta` VALUES (103, 2);
INSERT INTO `rbac_ta` VALUES (103, 3);
INSERT INTO `rbac_ta` VALUES (103, 4);
INSERT INTO `rbac_ta` VALUES (103, 6);
INSERT INTO `rbac_ta` VALUES (103, 45);
INSERT INTO `rbac_ta` VALUES (103, 46);
INSERT INTO `rbac_ta` VALUES (104, 1);
INSERT INTO `rbac_ta` VALUES (104, 2);
INSERT INTO `rbac_ta` VALUES (104, 3);
INSERT INTO `rbac_ta` VALUES (104, 4);
INSERT INTO `rbac_ta` VALUES (104, 6);
INSERT INTO `rbac_ta` VALUES (105, 1);
INSERT INTO `rbac_ta` VALUES (105, 2);
INSERT INTO `rbac_ta` VALUES (105, 3);
INSERT INTO `rbac_ta` VALUES (105, 4);
INSERT INTO `rbac_ta` VALUES (105, 6);
INSERT INTO `rbac_ta` VALUES (106, 1);
INSERT INTO `rbac_ta` VALUES (106, 2);
INSERT INTO `rbac_ta` VALUES (106, 3);
INSERT INTO `rbac_ta` VALUES (106, 4);
INSERT INTO `rbac_ta` VALUES (108, 1);
INSERT INTO `rbac_ta` VALUES (108, 2);
INSERT INTO `rbac_ta` VALUES (108, 3);
INSERT INTO `rbac_ta` VALUES (108, 6);
# --------------------------------------------------------

#
# Table structure for table `rbac_templates`
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
# Dumping data for table `rbac_templates`
#

INSERT INTO `rbac_templates` VALUES (5, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (14, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 6, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'slm', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'slm', 7, 8);
INSERT INTO `rbac_templates` VALUES (4, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'rolf', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'rolf', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'mail', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'mail', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'mail', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 8, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 6, 8);
INSERT INTO `rbac_templates` VALUES (4, 'rolf', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (4, 'rolf', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'lm', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'lm', 7, 8);
INSERT INTO `rbac_templates` VALUES (4, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (4, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'glo', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'glo', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 8, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'exc', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'glo', 1, 8);
INSERT INTO `rbac_templates` VALUES (3, 'frm', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'frm', 1, 8);
INSERT INTO `rbac_templates` VALUES (4, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'frm', 4, 8);
INSERT INTO `rbac_templates` VALUES (4, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'exc', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'exc', 2, 8);
INSERT INTO `rbac_templates` VALUES (83, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'exc', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'exc', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'exc', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'exc', 1, 8);
INSERT INTO `rbac_templates` VALUES (3, 'dbk', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'dbk', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'dbk', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'crs', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'crs', 7, 8);
INSERT INTO `rbac_templates` VALUES (5, 'cat', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (4, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (81, 'slm', 8, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 8, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 7, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 4, 8);
INSERT INTO `rbac_templates` VALUES (81, 'rolf', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'rolf', 14, 8);
INSERT INTO `rbac_templates` VALUES (5, 'cat', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'cat', 22, 8);
INSERT INTO `rbac_templates` VALUES (80, 'rolf', 6, 8);
INSERT INTO `rbac_templates` VALUES (4, 'cat', 18, 8);
INSERT INTO `rbac_templates` VALUES (80, 'rolf', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'rolf', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'rolf', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'rolf', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 8, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 7, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'lm', 1, 8);
INSERT INTO `rbac_templates` VALUES (81, 'rolf', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 25, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 22, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 21, 8);
INSERT INTO `rbac_templates` VALUES (14, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (14, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 20, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 18, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (81, 'lm', 8, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 6, 8);
INSERT INTO `rbac_templates` VALUES (4, 'cat', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'dbk', 1, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 22, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 21, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 20, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 4, 8);
INSERT INTO `rbac_templates` VALUES (81, 'lm', 7, 8);
INSERT INTO `rbac_templates` VALUES (81, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 18, 8);
INSERT INTO `rbac_templates` VALUES (81, 'fold', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'fold', 2, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 26, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 25, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 18, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (81, 'frm', 4, 8);
INSERT INTO `rbac_templates` VALUES (81, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (14, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (14, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 1, 8);
INSERT INTO `rbac_templates` VALUES (81, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (83, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 8, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 7, 8);
INSERT INTO `rbac_templates` VALUES (14, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'cat', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 10, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 9, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'crs', 1, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 24, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 23, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 22, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 21, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 20, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 19, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 18, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 17, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 16, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'frm', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'slm', 1, 8);
INSERT INTO `rbac_templates` VALUES (81, 'slm', 7, 8);
INSERT INTO `rbac_templates` VALUES (81, 'slm', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'slm', 2, 8);
INSERT INTO `rbac_templates` VALUES (81, 'chat', 2, 8);
INSERT INTO `rbac_templates` VALUES (81, 'chat', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'file', 2, 8);
INSERT INTO `rbac_templates` VALUES (81, 'file', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'frm', 9, 8);
INSERT INTO `rbac_templates` VALUES (81, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (81, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'glo', 7, 8);
INSERT INTO `rbac_templates` VALUES (81, 'glo', 8, 8);
INSERT INTO `rbac_templates` VALUES (81, 'fold', 18, 8);
INSERT INTO `rbac_templates` VALUES (81, 'fold', 25, 8);
INSERT INTO `rbac_templates` VALUES (81, 'fold', 26, 8);
INSERT INTO `rbac_templates` VALUES (80, 'chat', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'chat', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'chat', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'chat', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'chat', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'file', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'file', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'file', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'file', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'file', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 7, 8);
INSERT INTO `rbac_templates` VALUES (80, 'glo', 8, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 26, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 29, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 18, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 20, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 21, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 22, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 25, 8);
INSERT INTO `rbac_templates` VALUES (80, 'fold', 26, 8);
INSERT INTO `rbac_templates` VALUES (80, 'dbk', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'dbk', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'dbk', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'dbk', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'exc', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'exc', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'exc', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'exc', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'exc', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 23, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 24, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 31, 8);
INSERT INTO `rbac_templates` VALUES (80, 'grp', 32, 8);
INSERT INTO `rbac_templates` VALUES (80, 'htlm', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'htlm', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'htlm', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'htlm', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'htlm', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'mep', 1, 8);
INSERT INTO `rbac_templates` VALUES (80, 'mep', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'mep', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'mep', 4, 8);
INSERT INTO `rbac_templates` VALUES (80, 'mep', 6, 8);
INSERT INTO `rbac_templates` VALUES (4, 'tst', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'tst', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'svy', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'svy', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'svy', 46, 8);
INSERT INTO `rbac_templates` VALUES (5, 'svy', 46, 8);
# --------------------------------------------------------

#
# Table structure for table `rbac_ua`
#

CREATE TABLE `rbac_ua` (
  `usr_id` int(11) NOT NULL default '0',
  `rol_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`rol_id`),
  KEY `usr_id` (`usr_id`),
  KEY `rol_id` (`rol_id`)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_ua`
#

INSERT INTO `rbac_ua` VALUES (6, 2);
INSERT INTO `rbac_ua` VALUES (13, 14);
# --------------------------------------------------------

#
# Table structure for table `role_data`
#

CREATE TABLE `role_data` (
  `role_id` int(11) NOT NULL default '0',
  `allow_register` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`role_id`)
) TYPE=MyISAM;

#
# Dumping data for table `role_data`
#

INSERT INTO `role_data` VALUES (2, 0);
INSERT INTO `role_data` VALUES (3, 0);
INSERT INTO `role_data` VALUES (4, 0);
INSERT INTO `role_data` VALUES (5, 1);
INSERT INTO `role_data` VALUES (14, 0);
# --------------------------------------------------------

#
# Table structure for table `sc_item`
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
# Dumping data for table `sc_item`
#

# --------------------------------------------------------

#
# Table structure for table `sc_manifest`
#

CREATE TABLE `sc_manifest` (
  `obj_id` int(11) NOT NULL default '0',
  `import_id` varchar(200) default NULL,
  `version` varchar(200) default NULL,
  `xml_base` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `sc_manifest`
#

# --------------------------------------------------------

#
# Table structure for table `sc_organization`
#

CREATE TABLE `sc_organization` (
  `obj_id` int(11) NOT NULL default '0',
  `import_id` varchar(200) default NULL,
  `structure` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `sc_organization`
#

# --------------------------------------------------------

#
# Table structure for table `sc_organizations`
#

CREATE TABLE `sc_organizations` (
  `obj_id` int(11) NOT NULL default '0',
  `default_organization` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `sc_organizations`
#

# --------------------------------------------------------

#
# Table structure for table `sc_resource`
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
# Dumping data for table `sc_resource`
#

# --------------------------------------------------------

#
# Table structure for table `sc_resource_dependency`
#

CREATE TABLE `sc_resource_dependency` (
  `id` int(11) NOT NULL auto_increment,
  `res_id` int(11) default NULL,
  `identifierref` varchar(200) default NULL,
  `nr` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `sc_resource_dependency`
#

# --------------------------------------------------------

#
# Table structure for table `sc_resource_file`
#

CREATE TABLE `sc_resource_file` (
  `id` int(11) NOT NULL auto_increment,
  `res_id` int(11) default NULL,
  `href` text,
  `nr` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `sc_resource_file`
#

# --------------------------------------------------------

#
# Table structure for table `sc_resources`
#

CREATE TABLE `sc_resources` (
  `obj_id` int(11) NOT NULL default '0',
  `xml_base` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `sc_resources`
#

# --------------------------------------------------------

#
# Table structure for table `scorm_lm`
#

CREATE TABLE `scorm_lm` (
  `id` int(11) NOT NULL default '0',
  `online` enum('y','n') default 'n',
  `api_adapter` varchar(80) default 'API',
  `api_func_prefix` varchar(20) default 'LMS',
  `credit` enum('credit','no_credit') NOT NULL default 'credit',
  `default_lesson_mode` enum('normal','browse') NOT NULL default 'normal',
  `auto_review` enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `scorm_lm`
#

# --------------------------------------------------------

#
# Table structure for table `scorm_object`
#

CREATE TABLE `scorm_object` (
  `obj_id` int(11) NOT NULL auto_increment,
  `title` varchar(200) default NULL,
  `type` char(3) default NULL,
  `slm_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `scorm_object`
#

# --------------------------------------------------------

#
# Table structure for table `scorm_tracking`
#

CREATE TABLE `scorm_tracking` (
  `user_id` int(11) NOT NULL default '0',
  `sco_id` int(11) NOT NULL default '0',
  `lvalue` varchar(64) NOT NULL default '',
  `rvalue` text,
  `ref_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`sco_id`,`lvalue`)
) TYPE=MyISAM;

#
# Dumping data for table `scorm_tracking`
#

# --------------------------------------------------------

#
# Table structure for table `scorm_tree`
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
# Dumping data for table `scorm_tree`
#

# --------------------------------------------------------

#
# Table structure for table `search_data`
#

CREATE TABLE `search_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `target` text NOT NULL,
  `type` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`user_id`)
) TYPE=MyISAM;

#
# Dumping data for table `search_data`
#

# --------------------------------------------------------

#
# Table structure for table `search_tree`
#

CREATE TABLE `search_tree` (
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
# Dumping data for table `search_tree`
#

# --------------------------------------------------------

#
# Table structure for table `settings`
#

CREATE TABLE `settings` (
  `keyword` char(50) NOT NULL default '',
  `value` char(50) NOT NULL default '',
  PRIMARY KEY  (`keyword`)
) TYPE=MyISAM;

#
# Dumping data for table `settings`
#

INSERT INTO `settings` VALUES ('convert_path', '');
INSERT INTO `settings` VALUES ('db_version', '234');
INSERT INTO `settings` VALUES ('ilias_version', '3.1.0_beta1 2004/07/11');
INSERT INTO `settings` VALUES ('inst_info', '');
INSERT INTO `settings` VALUES ('inst_name', '');
INSERT INTO `settings` VALUES ('java_path', '');
INSERT INTO `settings` VALUES ('language', 'en');
INSERT INTO `settings` VALUES ('ldap_basedn', '');
INSERT INTO `settings` VALUES ('ldap_enable', '');
INSERT INTO `settings` VALUES ('ldap_port', '');
INSERT INTO `settings` VALUES ('ldap_server', '');
INSERT INTO `settings` VALUES ('system_user_id', '6');
INSERT INTO `settings` VALUES ('anonymous_role_id', '14');
INSERT INTO `settings` VALUES ('error_recipient', '');
INSERT INTO `settings` VALUES ('pub_section', '');
INSERT INTO `settings` VALUES ('feedback_recipient', '');
INSERT INTO `settings` VALUES ('unzip_path', '');
INSERT INTO `settings` VALUES ('anonymous_user_id', '13');
INSERT INTO `settings` VALUES ('zip_path', '');
INSERT INTO `settings` VALUES ('enable_registration', '1');
INSERT INTO `settings` VALUES ('system_role_id', '2');
INSERT INTO `settings` VALUES ('recovery_folder_id', '15');
INSERT INTO `settings` VALUES ('bench_max_records', '10000');
INSERT INTO `settings` VALUES ('enable_bench', '0');
INSERT INTO `settings` VALUES ('sys_user_tracking_id', '17');
INSERT INTO `settings` VALUES ('enable_tracking', '0');
INSERT INTO `settings` VALUES ('save_user_related_data', '0');
# --------------------------------------------------------

#
# Table structure for table `style_parameter`
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
# Dumping data for table `style_parameter`
#

# --------------------------------------------------------

#
# Table structure for table `survey_answer`
#

CREATE TABLE `survey_answer` (
  `answer_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `value` double default NULL,
  `textanswer` text,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`answer_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_answer`
#

# --------------------------------------------------------

#
# Table structure for table `survey_category`
#

CREATE TABLE `survey_category` (
  `category_id` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `defaultvalue` enum('0','1') NOT NULL default '0',
  `owner_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`category_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_category`
#

INSERT INTO `survey_category` VALUES (1, 'dc_desired', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (2, 'dc_undesired', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (3, 'dc_agree', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (4, 'dc_disagree', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (5, 'dc_good', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (6, 'dc_notacceptable', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (7, 'dc_should', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (8, 'dc_shouldnot', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (9, 'dc_true', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (10, 'dc_false', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (11, 'dc_always', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (12, 'dc_never', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (13, 'dc_yes', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (14, 'dc_no', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (15, 'dc_neutral', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (16, 'dc_undecided', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (17, 'dc_fair', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (18, 'dc_sometimes', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (19, 'dc_stronglydesired', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (20, 'dc_stronglyundesired', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (21, 'dc_stronglyagree', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (22, 'dc_stronglydisagree', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (23, 'dc_verygood', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (24, 'dc_poor', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (25, 'dc_must', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (26, 'dc_mustnot', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (27, 'dc_definitelytrue', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (28, 'dc_definitelyfalse', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (29, 'dc_manytimes', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (30, 'dc_varying', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (31, 'dc_rarely', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (32, 'dc_mostcertainly', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (33, 'dc_morepositive', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (34, 'dc_morenegative', '1', 0, 20040522134301);
INSERT INTO `survey_category` VALUES (35, 'dc_mostcertainlynot', '1', 0, 20040522134301);
# --------------------------------------------------------

#
# Table structure for table `survey_constraint`
#

CREATE TABLE `survey_constraint` (
  `constraint_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL default '0',
  `relation_fi` int(11) NOT NULL default '0',
  `value` double NOT NULL default '0',
  PRIMARY KEY  (`constraint_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_constraint`
#

# --------------------------------------------------------

#
# Table structure for table `survey_finished`
#

CREATE TABLE `survey_finished` (
  `finished_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `state` enum('0','1') NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`finished_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_finished`
#

# --------------------------------------------------------

#
# Table structure for table `survey_invited_group`
#

CREATE TABLE `survey_invited_group` (
  `invited_group_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `group_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`invited_group_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_invited_group`
#

# --------------------------------------------------------

#
# Table structure for table `survey_invited_user`
#

CREATE TABLE `survey_invited_user` (
  `invited_user_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`invited_user_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_invited_user`
#

# --------------------------------------------------------

#
# Table structure for table `survey_phrase`
#

CREATE TABLE `survey_phrase` (
  `phrase_id` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `defaultvalue` enum('0','1','2') NOT NULL default '0',
  `owner_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`phrase_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_phrase`
#

INSERT INTO `survey_phrase` VALUES (1, 'dp_standard_attitude_desired_undesired', '1', 0, 20040522135431);
INSERT INTO `survey_phrase` VALUES (2, 'dp_standard_attitude_agree_disagree', '1', 0, 20040522135458);
INSERT INTO `survey_phrase` VALUES (3, 'dp_standard_attitude_good_notacceptable', '1', 0, 20040522135518);
INSERT INTO `survey_phrase` VALUES (4, 'dp_standard_attitude_shold_shouldnot', '1', 0, 20040522135546);
INSERT INTO `survey_phrase` VALUES (5, 'dp_standard_beliefs_true_false', '1', 0, 20040522135613);
INSERT INTO `survey_phrase` VALUES (6, 'dp_standard_beliefs_always_never', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` VALUES (7, 'dp_standard_behaviour_yes_no', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` VALUES (8, 'dp_standard_attitude_desired_neutral_undesired', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` VALUES (9, 'dp_standard_attitude_agree_undecided_disagree', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` VALUES (10, 'dp_standard_attitude_good_fair_notacceptable', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` VALUES (11, 'dp_standard_attitude_should_undecided_shouldnot', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` VALUES (12, 'dp_standard_beliefs_true_undecided_false', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` VALUES (13, 'dp_standard_beliefs_always_sometimes_never', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` VALUES (14, 'dp_standard_behaviour_yes_undecided_no', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` VALUES (15, 'dp_standard_attitude_desired5', '1', 0, 20040522150702);
INSERT INTO `survey_phrase` VALUES (16, 'dp_standard_attitude_agree5', '1', 0, 20040522150717);
INSERT INTO `survey_phrase` VALUES (17, 'dp_standard_attitude_good5', '1', 0, 20040522150729);
INSERT INTO `survey_phrase` VALUES (18, 'dp_standard_attitude_must5', '1', 0, 20040522150744);
INSERT INTO `survey_phrase` VALUES (19, 'dp_standard_beliefs_true5', '1', 0, 20040522150754);
INSERT INTO `survey_phrase` VALUES (20, 'dp_standard_beliefs_always5', '1', 0, 20040522150812);
INSERT INTO `survey_phrase` VALUES (21, 'dp_standard_behaviour_certainly5', '1', 0, 20040522150828);
INSERT INTO `survey_phrase` VALUES (22, 'dp_standard_numbers', '1', 0, 20040621012719);
# --------------------------------------------------------

#
# Table structure for table `survey_phrase_category`
#

CREATE TABLE `survey_phrase_category` (
  `phrase_category_id` int(11) NOT NULL auto_increment,
  `phrase_fi` int(11) NOT NULL default '0',
  `category_fi` int(11) NOT NULL default '0',
  `sequence` int(11) NOT NULL default '0',
  PRIMARY KEY  (`phrase_category_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_phrase_category`
#

INSERT INTO `survey_phrase_category` VALUES (1, 1, 1, 1);
INSERT INTO `survey_phrase_category` VALUES (2, 1, 2, 2);
INSERT INTO `survey_phrase_category` VALUES (3, 2, 3, 1);
INSERT INTO `survey_phrase_category` VALUES (4, 2, 4, 2);
INSERT INTO `survey_phrase_category` VALUES (5, 3, 5, 1);
INSERT INTO `survey_phrase_category` VALUES (6, 3, 6, 2);
INSERT INTO `survey_phrase_category` VALUES (7, 4, 7, 1);
INSERT INTO `survey_phrase_category` VALUES (8, 4, 8, 2);
INSERT INTO `survey_phrase_category` VALUES (9, 5, 9, 1);
INSERT INTO `survey_phrase_category` VALUES (10, 5, 10, 2);
INSERT INTO `survey_phrase_category` VALUES (11, 6, 11, 1);
INSERT INTO `survey_phrase_category` VALUES (12, 6, 12, 2);
INSERT INTO `survey_phrase_category` VALUES (13, 7, 13, 1);
INSERT INTO `survey_phrase_category` VALUES (14, 7, 14, 2);
INSERT INTO `survey_phrase_category` VALUES (15, 8, 1, 1);
INSERT INTO `survey_phrase_category` VALUES (16, 8, 15, 2);
INSERT INTO `survey_phrase_category` VALUES (17, 8, 2, 3);
INSERT INTO `survey_phrase_category` VALUES (18, 9, 3, 1);
INSERT INTO `survey_phrase_category` VALUES (19, 9, 16, 2);
INSERT INTO `survey_phrase_category` VALUES (20, 9, 4, 3);
INSERT INTO `survey_phrase_category` VALUES (21, 10, 5, 1);
INSERT INTO `survey_phrase_category` VALUES (22, 10, 17, 2);
INSERT INTO `survey_phrase_category` VALUES (23, 10, 6, 3);
INSERT INTO `survey_phrase_category` VALUES (24, 11, 7, 1);
INSERT INTO `survey_phrase_category` VALUES (25, 11, 16, 2);
INSERT INTO `survey_phrase_category` VALUES (26, 11, 8, 3);
INSERT INTO `survey_phrase_category` VALUES (27, 12, 9, 1);
INSERT INTO `survey_phrase_category` VALUES (28, 12, 16, 2);
INSERT INTO `survey_phrase_category` VALUES (29, 12, 10, 3);
INSERT INTO `survey_phrase_category` VALUES (30, 13, 11, 1);
INSERT INTO `survey_phrase_category` VALUES (31, 13, 18, 2);
INSERT INTO `survey_phrase_category` VALUES (32, 13, 12, 3);
INSERT INTO `survey_phrase_category` VALUES (33, 14, 13, 1);
INSERT INTO `survey_phrase_category` VALUES (34, 14, 16, 2);
INSERT INTO `survey_phrase_category` VALUES (35, 14, 14, 3);
INSERT INTO `survey_phrase_category` VALUES (36, 15, 19, 1);
INSERT INTO `survey_phrase_category` VALUES (37, 15, 1, 2);
INSERT INTO `survey_phrase_category` VALUES (38, 15, 15, 3);
INSERT INTO `survey_phrase_category` VALUES (39, 15, 2, 4);
INSERT INTO `survey_phrase_category` VALUES (40, 15, 20, 5);
INSERT INTO `survey_phrase_category` VALUES (41, 16, 21, 1);
INSERT INTO `survey_phrase_category` VALUES (42, 16, 3, 2);
INSERT INTO `survey_phrase_category` VALUES (43, 16, 16, 3);
INSERT INTO `survey_phrase_category` VALUES (44, 16, 4, 4);
INSERT INTO `survey_phrase_category` VALUES (45, 16, 22, 5);
INSERT INTO `survey_phrase_category` VALUES (46, 17, 23, 1);
INSERT INTO `survey_phrase_category` VALUES (47, 17, 5, 2);
INSERT INTO `survey_phrase_category` VALUES (48, 17, 17, 3);
INSERT INTO `survey_phrase_category` VALUES (49, 17, 24, 4);
INSERT INTO `survey_phrase_category` VALUES (50, 17, 6, 5);
INSERT INTO `survey_phrase_category` VALUES (51, 18, 25, 1);
INSERT INTO `survey_phrase_category` VALUES (52, 18, 7, 2);
INSERT INTO `survey_phrase_category` VALUES (53, 18, 16, 3);
INSERT INTO `survey_phrase_category` VALUES (54, 18, 8, 4);
INSERT INTO `survey_phrase_category` VALUES (55, 18, 26, 5);
INSERT INTO `survey_phrase_category` VALUES (56, 19, 27, 1);
INSERT INTO `survey_phrase_category` VALUES (57, 19, 9, 2);
INSERT INTO `survey_phrase_category` VALUES (58, 19, 16, 3);
INSERT INTO `survey_phrase_category` VALUES (59, 19, 10, 4);
INSERT INTO `survey_phrase_category` VALUES (60, 19, 28, 5);
INSERT INTO `survey_phrase_category` VALUES (61, 20, 11, 1);
INSERT INTO `survey_phrase_category` VALUES (62, 20, 29, 2);
INSERT INTO `survey_phrase_category` VALUES (63, 20, 30, 3);
INSERT INTO `survey_phrase_category` VALUES (64, 20, 31, 4);
INSERT INTO `survey_phrase_category` VALUES (65, 20, 12, 5);
INSERT INTO `survey_phrase_category` VALUES (66, 21, 32, 1);
INSERT INTO `survey_phrase_category` VALUES (67, 21, 33, 2);
INSERT INTO `survey_phrase_category` VALUES (68, 21, 16, 3);
INSERT INTO `survey_phrase_category` VALUES (69, 21, 34, 4);
INSERT INTO `survey_phrase_category` VALUES (70, 21, 35, 5);
# --------------------------------------------------------

#
# Table structure for table `survey_question`
#

CREATE TABLE `survey_question` (
  `question_id` int(11) NOT NULL auto_increment,
  `subtype` enum('0','1','2','3','4','5') NOT NULL default '0',
  `questiontype_fi` int(11) NOT NULL default '0',
  `ref_fi` int(11) NOT NULL default '0',
  `owner_fi` int(11) NOT NULL default '0',
  `title` varchar(100) NOT NULL default '',
  `description` varchar(200) NOT NULL default '',
  `author` varchar(100) NOT NULL default '',
  `questiontext` text NOT NULL,
  `obligatory` enum('0','1') NOT NULL default '1',
  `complete` enum('0','1') NOT NULL default '0',
  `created` varchar(14) NOT NULL default '',
  `original_id` int(11) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`question_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_question`
#

# --------------------------------------------------------

#
# Table structure for table `survey_question_constraint`
#

CREATE TABLE `survey_question_constraint` (
  `question_constraint_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `constraint_fi` int(11) NOT NULL default '0',
  PRIMARY KEY  (`question_constraint_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_question_constraint`
#

# --------------------------------------------------------

#
# Table structure for table `survey_question_material`
#

CREATE TABLE `survey_question_material` (
  `material_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL default '0',
  `materials` text,
  `materials_file` varchar(200) NOT NULL default '',
  UNIQUE KEY `material_id` (`material_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_question_material`
#

# --------------------------------------------------------

#
# Table structure for table `survey_questionblock`
#

CREATE TABLE `survey_questionblock` (
  `questionblock_id` int(11) NOT NULL auto_increment,
  `title` text,
  `obligatory` enum('0','1') NOT NULL default '0',
  `owner_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`questionblock_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_questionblock`
#

# --------------------------------------------------------

#
# Table structure for table `survey_questionblock_question`
#

CREATE TABLE `survey_questionblock_question` (
  `questionblock_question_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `questionblock_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  PRIMARY KEY  (`questionblock_question_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_questionblock_question`
#

# --------------------------------------------------------

#
# Table structure for table `survey_questiontype`
#

CREATE TABLE `survey_questiontype` (
  `questiontype_id` int(11) NOT NULL auto_increment,
  `type_tag` varchar(30) NOT NULL default '',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`questiontype_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_questiontype`
#

INSERT INTO `survey_questiontype` VALUES (1, 'qt_nominal', 20040518222841);
INSERT INTO `survey_questiontype` VALUES (2, 'qt_ordinal', 20040518222848);
INSERT INTO `survey_questiontype` VALUES (3, 'qt_metric', 20040518222859);
INSERT INTO `survey_questiontype` VALUES (4, 'qt_text', 20040518222904);
# --------------------------------------------------------

#
# Table structure for table `survey_relation`
#

CREATE TABLE `survey_relation` (
  `relation_id` int(11) NOT NULL auto_increment,
  `long` varchar(20) NOT NULL default '',
  `short` char(2) NOT NULL default '',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`relation_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_relation`
#

INSERT INTO `survey_relation` VALUES (1, 'less', '<', 20040518195753);
INSERT INTO `survey_relation` VALUES (2, 'less or equal', '<=', 20040518195808);
INSERT INTO `survey_relation` VALUES (3, 'equal', '=', 20040518195816);
INSERT INTO `survey_relation` VALUES (4, 'not equal', '<>', 20040518195839);
INSERT INTO `survey_relation` VALUES (5, 'more or equal', '>=', 20040518195852);
INSERT INTO `survey_relation` VALUES (6, 'more', '>', 20040518195903);
# --------------------------------------------------------

#
# Table structure for table `survey_survey`
#

CREATE TABLE `survey_survey` (
  `survey_id` int(11) NOT NULL auto_increment,
  `ref_fi` int(11) NOT NULL default '0',
  `author` varchar(50) NOT NULL default '',
  `introduction` text,
  `status` enum('0','1') NOT NULL default '1',
  `startdate` date default NULL,
  `enddate` date default NULL,
  `evaluation_access` enum('0','1') NOT NULL default '0',
  `invitation` enum('0','1') NOT NULL default '0',
  `invitation_mode` enum('0','1') NOT NULL default '1',
  `complete` enum('0','1') NOT NULL default '0',
  `created` varchar(14) NOT NULL default '',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`survey_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_survey`
#

# --------------------------------------------------------

#
# Table structure for table `survey_survey_question`
#

CREATE TABLE `survey_survey_question` (
  `survey_question_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `sequence` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`survey_question_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_survey_question`
#

# --------------------------------------------------------

#
# Table structure for table `survey_variable`
#

CREATE TABLE `survey_variable` (
  `variable_id` int(11) NOT NULL auto_increment,
  `category_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `value1` double default NULL,
  `value2` double default NULL,
  `sequence` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`variable_id`)
) TYPE=MyISAM;

#
# Dumping data for table `survey_variable`
#

# --------------------------------------------------------

#
# Table structure for table `tree`
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
# Dumping data for table `tree`
#

INSERT INTO `tree` VALUES (1, 1, 0, 1, 22, 1);
INSERT INTO `tree` VALUES (1, 7, 9, 5, 6, 3);
INSERT INTO `tree` VALUES (1, 8, 9, 7, 8, 3);
INSERT INTO `tree` VALUES (1, 9, 1, 2, 21, 2);
INSERT INTO `tree` VALUES (1, 10, 9, 9, 10, 3);
INSERT INTO `tree` VALUES (1, 11, 9, 11, 12, 3);
INSERT INTO `tree` VALUES (1, 12, 9, 3, 4, 3);
INSERT INTO `tree` VALUES (1, 14, 9, 13, 14, 3);
INSERT INTO `tree` VALUES (1, 15, 9, 15, 16, 3);
INSERT INTO `tree` VALUES (1, 16, 9, 17, 18, 3);
INSERT INTO `tree` VALUES (1, 17, 9, 19, 20, 3);
# --------------------------------------------------------

#
# Table structure for table `tst_active`
#

CREATE TABLE `tst_active` (
  `active_id` int(10) unsigned NOT NULL auto_increment,
  `user_fi` int(10) unsigned NOT NULL default '0',
  `test_fi` int(10) unsigned NOT NULL default '0',
  `sequence` text NOT NULL,
  `postponed` text,
  `lastindex` tinyint(4) NOT NULL default '1',
  `tries` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`active_id`),
  UNIQUE KEY `active_id` (`active_id`),
  KEY `active_id_2` (`active_id`)
) TYPE=MyISAM;

#
# Dumping data for table `tst_active`
#

# --------------------------------------------------------

#
# Table structure for table `tst_eval_settings`
#

CREATE TABLE `tst_eval_settings` (
  `eval_settings_id` int(11) NOT NULL auto_increment,
  `user_fi` int(11) NOT NULL default '0',
  `qworkedthrough` enum('0','1') NOT NULL default '1',
  `pworkedthrough` enum('0','1') NOT NULL default '1',
  `timeofwork` enum('0','1') NOT NULL default '1',
  `atimeofwork` enum('0','1') NOT NULL default '1',
  `firstvisit` enum('0','1') NOT NULL default '1',
  `lastvisit` enum('0','1') NOT NULL default '1',
  `resultspoints` enum('0','1') NOT NULL default '1',
  `resultsmarks` enum('0','1') NOT NULL default '1',
  `distancemean` enum('0','1') NOT NULL default '1',
  `distancequintile` enum('0','1') NOT NULL default '1',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`eval_settings_id`)
) TYPE=MyISAM COMMENT='User settings for statistical evaluation tool';

#
# Dumping data for table `tst_eval_settings`
#

# --------------------------------------------------------

#
# Table structure for table `tst_mark`
#

CREATE TABLE `tst_mark` (
  `mark_id` int(10) unsigned NOT NULL auto_increment,
  `test_fi` int(10) unsigned NOT NULL default '0',
  `short_name` varchar(15) NOT NULL default '',
  `official_name` varchar(50) NOT NULL default '',
  `minimum_level` double NOT NULL default '0',
  `passed` enum('0','1') NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`mark_id`),
  UNIQUE KEY `mark_id` (`mark_id`),
  KEY `mark_id_2` (`mark_id`)
) TYPE=MyISAM COMMENT='Mark steps of mark schemas';

#
# Dumping data for table `tst_mark`
#

# --------------------------------------------------------

#
# Table structure for table `tst_solutions`
#

CREATE TABLE `tst_solutions` (
  `solution_id` int(10) unsigned NOT NULL auto_increment,
  `user_fi` int(10) unsigned NOT NULL default '0',
  `test_fi` int(10) unsigned NOT NULL default '0',
  `question_fi` int(10) unsigned NOT NULL default '0',
  `value1` varchar(50) default NULL,
  `value2` varchar(50) default NULL,
  `points` double default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`solution_id`),
  UNIQUE KEY `solution_id` (`solution_id`),
  KEY `solution_id_2` (`solution_id`)
) TYPE=MyISAM COMMENT='Test and Assessment solutions';

#
# Dumping data for table `tst_solutions`
#

# --------------------------------------------------------

#
# Table structure for table `tst_test_question`
#

CREATE TABLE `tst_test_question` (
  `test_question_id` int(11) NOT NULL auto_increment,
  `test_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `sequence` int(10) unsigned NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`test_question_id`)
) TYPE=MyISAM COMMENT='Relation table for questions in tests';

#
# Dumping data for table `tst_test_question`
#

# --------------------------------------------------------

#
# Table structure for table `tst_test_type`
#

CREATE TABLE `tst_test_type` (
  `test_type_id` int(10) unsigned NOT NULL auto_increment,
  `type_tag` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`test_type_id`),
  UNIQUE KEY `test_type_id` (`test_type_id`),
  KEY `test_type_id_2` (`test_type_id`)
) TYPE=MyISAM COMMENT='ILIAS 3 Assessment Test types';

#
# Dumping data for table `tst_test_type`
#

INSERT INTO `tst_test_type` VALUES (1, 'tt_assessment');
INSERT INTO `tst_test_type` VALUES (2, 'tt_self_assessment');
INSERT INTO `tst_test_type` VALUES (3, 'tt_navigation_controlling');
# --------------------------------------------------------

#
# Table structure for table `tst_tests`
#

CREATE TABLE `tst_tests` (
  `test_id` int(10) unsigned NOT NULL auto_increment,
  `ref_fi` int(11) NOT NULL default '0',
  `author` varchar(50) NOT NULL default '',
  `test_type_fi` int(10) unsigned NOT NULL default '0',
  `introduction` text,
  `sequence_settings` tinyint(3) unsigned NOT NULL default '0',
  `score_reporting` tinyint(3) unsigned NOT NULL default '0',
  `nr_of_tries` tinyint(3) unsigned NOT NULL default '0',
  `processing_time` time default NULL,
  `enable_processing_time` enum('0','1') NOT NULL default '0',
  `reporting_date` varchar(14) default NULL,
  `starting_time` varchar(14) default NULL,
  `ending_time` varchar(14) default NULL,
  `complete` enum('0','1') NOT NULL default '1',
  `created` varchar(14) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`test_id`),
  UNIQUE KEY `test_id` (`test_id`),
  KEY `test_id_2` (`test_id`)
) TYPE=MyISAM COMMENT='Tests in ILIAS Assessment';

#
# Dumping data for table `tst_tests`
#

# --------------------------------------------------------

#
# Table structure for table `tst_times`
#

CREATE TABLE `tst_times` (
  `times_id` int(11) NOT NULL auto_increment,
  `active_fi` int(11) NOT NULL default '0',
  `started` datetime NOT NULL default '0000-00-00 00:00:00',
  `finished` datetime NOT NULL default '0000-00-00 00:00:00',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`times_id`)
) TYPE=MyISAM COMMENT='Editing times of an assessment test';

#
# Dumping data for table `tst_times`
#

# --------------------------------------------------------

#
# Table structure for table `usr_data`
#

CREATE TABLE `usr_data` (
  `usr_id` int(10) unsigned NOT NULL default '0',
  `login` varchar(80) NOT NULL default '',
  `passwd` varchar(32) NOT NULL default '',
  `firstname` varchar(32) NOT NULL default '',
  `lastname` varchar(32) NOT NULL default '',
  `title` varchar(32) NOT NULL default '',
  `gender` enum('m','f') NOT NULL default 'm',
  `email` varchar(80) NOT NULL default '',
  `institution` varchar(80) default NULL,
  `street` varchar(40) default NULL,
  `city` varchar(40) default NULL,
  `zipcode` varchar(10) default NULL,
  `country` varchar(40) default NULL,
  `phone_office` varchar(40) NOT NULL default '',
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `hobby` text NOT NULL,
  `department` varchar(80) NOT NULL default '',
  `phone_home` varchar(40) NOT NULL default '',
  `phone_mobile` varchar(40) NOT NULL default '',
  `fax` varchar(40) NOT NULL default '',
  `i2passwd` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`usr_id`),
  KEY `login` (`login`,`passwd`)
) TYPE=MyISAM;

#
# Dumping data for table `usr_data`
#

INSERT INTO `usr_data` VALUES (6, 'root', 'dfa8327f5bfa4c672a04f9b38e348a70', 'root', 'user', '', 'm', 'ilias@yourserver.com', '', '', '', '', '', '', '2004-01-20 12:11:07', '2003-09-30 19:50:01', '0000-00-00 00:00:00', '', '', '', '', '', '');
INSERT INTO `usr_data` VALUES (13, 'anonymous', '294de3557d9d00b3d2d8a1e6aab028cf', 'anonymous', 'anonymous', '', 'm', 'nomail', NULL, NULL, NULL, NULL, NULL, '', '2003-08-15 11:03:36', '2003-08-15 10:07:30', '2003-08-15 10:07:30', '', '', '', '', '', '');
# --------------------------------------------------------

#
# Table structure for table `usr_pref`
#

CREATE TABLE `usr_pref` (
  `usr_id` int(10) unsigned NOT NULL default '0',
  `keyword` char(40) NOT NULL default '',
  `value` char(40) default NULL,
  PRIMARY KEY  (`usr_id`,`keyword`)
) TYPE=MyISAM;

#
# Dumping data for table `usr_pref`
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
INSERT INTO `usr_pref` VALUES (6, 'show_users_online', 'y');
INSERT INTO `usr_pref` VALUES (13, 'show_users_online', 'y');
# --------------------------------------------------------

#
# Table structure for table `usr_search`
#

CREATE TABLE `usr_search` (
  `usr_id` int(11) NOT NULL default '0',
  `search_result` text,
  PRIMARY KEY  (`usr_id`)
) TYPE=MyISAM;

#
# Dumping data for table `usr_search`
#

# --------------------------------------------------------

#
# Table structure for table `usr_session`
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
# Dumping data for table `usr_session`
#

INSERT INTO `usr_session` VALUES ('6e41d11d4bab4fed4d1f16be263140f2', 1082327754, 'locator_data|a:0:{}locator_level|i:-1;', 1082326314, 0);
# --------------------------------------------------------

#
# Table structure for table `ut_access`
#

CREATE TABLE `ut_access` (
  `id` int(10) NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `action_type` char(10) default NULL,
  `php_script` char(100) NOT NULL default '',
  `client_ip` char(15) default NULL,
  `acc_obj_type` char(10) NOT NULL default '',
  `acc_obj_id` int(10) NOT NULL default '0',
  `acc_sub_type` char(10) NOT NULL default '',
  `acc_sub_id` int(10) NOT NULL default '0',
  `language` char(15) default NULL,
  `browser` char(60) NOT NULL default '',
  `session_id` char(40) default NULL,
  `acc_time` datetime default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `ut_access`
#

# --------------------------------------------------------

#
# Table structure for table `xml_attribute_idx`
#

CREATE TABLE `xml_attribute_idx` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `value_id` smallint(5) unsigned NOT NULL default '0',
  KEY `node_id` (`node_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_attribute_idx`
#

# --------------------------------------------------------

#
# Table structure for table `xml_attribute_name`
#

CREATE TABLE `xml_attribute_name` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `attribute` char(32) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`),
  UNIQUE KEY `attribute` (`attribute`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_attribute_name`
#

# --------------------------------------------------------

#
# Table structure for table `xml_attribute_namespace`
#

CREATE TABLE `xml_attribute_namespace` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `namespace` char(64) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_attribute_namespace`
#

# --------------------------------------------------------

#
# Table structure for table `xml_attribute_value`
#

CREATE TABLE `xml_attribute_value` (
  `value_id` smallint(5) unsigned NOT NULL auto_increment,
  `value` char(32) NOT NULL default '0',
  PRIMARY KEY  (`value_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_attribute_value`
#

# --------------------------------------------------------

#
# Table structure for table `xml_cdata`
#

CREATE TABLE `xml_cdata` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `cdata` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_cdata`
#

# --------------------------------------------------------

#
# Table structure for table `xml_comment`
#

CREATE TABLE `xml_comment` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `comment` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_comment`
#

# --------------------------------------------------------

#
# Table structure for table `xml_element_idx`
#

CREATE TABLE `xml_element_idx` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `element_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_element_idx`
#

# --------------------------------------------------------

#
# Table structure for table `xml_element_name`
#

CREATE TABLE `xml_element_name` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `element` char(32) NOT NULL default '',
  PRIMARY KEY  (`element_id`),
  UNIQUE KEY `element` (`element`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_element_name`
#

# --------------------------------------------------------

#
# Table structure for table `xml_element_namespace`
#

CREATE TABLE `xml_element_namespace` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `namespace` char(64) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_element_namespace`
#

# --------------------------------------------------------

#
# Table structure for table `xml_entity_reference`
#

CREATE TABLE `xml_entity_reference` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `entity_reference` char(128) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_entity_reference`
#

# --------------------------------------------------------

#
# Table structure for table `xml_node_type`
#

CREATE TABLE `xml_node_type` (
  `node_type_id` int(11) NOT NULL auto_increment,
  `description` varchar(50) default NULL,
  `lft_delimiter` varchar(10) default NULL,
  `rgt_delimiter` varchar(10) default NULL,
  PRIMARY KEY  (`node_type_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_node_type`
#

INSERT INTO `xml_node_type` VALUES (1, 'ELEMENT_NODE', '<', '>');
INSERT INTO `xml_node_type` VALUES (2, 'ATTRIBUTE_NODE(not used)', '"', '"');
INSERT INTO `xml_node_type` VALUES (3, 'TEXT_NODE', NULL, NULL);
INSERT INTO `xml_node_type` VALUES (5, 'ENTITY_REF_NODE', '&', ';');
INSERT INTO `xml_node_type` VALUES (4, 'CDATA_SECTION_NODE', '<![CDATA[', ']]>');
INSERT INTO `xml_node_type` VALUES (8, 'COMMENT_NODE', '<!--', '-->');
INSERT INTO `xml_node_type` VALUES (9, 'DOCUMENT_NODE', NULL, NULL);
INSERT INTO `xml_node_type` VALUES (10, 'DOCUMENT_TYPE_NODE', NULL, NULL);
INSERT INTO `xml_node_type` VALUES (6, 'ENTITY_NODE', '&', ';');
# --------------------------------------------------------

#
# Table structure for table `xml_object`
#

CREATE TABLE `xml_object` (
  `ID` int(11) NOT NULL auto_increment,
  `version` varchar(5) NOT NULL default '',
  `encoding` varchar(40) default NULL,
  `charset` varchar(40) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Master Table for XML objects';

#
# Dumping data for table `xml_object`
#

# --------------------------------------------------------

#
# Table structure for table `xml_pi_data`
#

CREATE TABLE `xml_pi_data` (
  `leaf_id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `leaf_text` text NOT NULL,
  PRIMARY KEY  (`leaf_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_pi_data`
#

# --------------------------------------------------------

#
# Table structure for table `xml_pi_target`
#

CREATE TABLE `xml_pi_target` (
  `leaf_id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `leaf_text` text NOT NULL,
  PRIMARY KEY  (`leaf_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_pi_target`
#

# --------------------------------------------------------

#
# Table structure for table `xml_text`
#

CREATE TABLE `xml_text` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `textnode` text NOT NULL,
  PRIMARY KEY  (`node_id`),
  FULLTEXT KEY `textnode` (`textnode`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_text`
#

# --------------------------------------------------------

#
# Table structure for table `xml_tree`
#

CREATE TABLE `xml_tree` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `xml_id` mediumint(8) unsigned NOT NULL default '0',
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
  KEY `xml_id` (`xml_id`)
) TYPE=MyISAM;

#
# Dumping data for table `xml_tree`
#

# --------------------------------------------------------

#
# Table structure for table `xmlnestedset`
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
# Dumping data for table `xmlnestedset`
#

# --------------------------------------------------------

#
# Table structure for table `xmlparam`
#

CREATE TABLE `xmlparam` (
  `tag_fk` int(11) NOT NULL default '0',
  `param_name` char(50) NOT NULL default '',
  `param_value` char(255) NOT NULL default '',
  KEY `tag_fk` (`tag_fk`)
) TYPE=MyISAM;

#
# Dumping data for table `xmlparam`
#

# --------------------------------------------------------

#
# Table structure for table `xmltags`
#

CREATE TABLE `xmltags` (
  `tag_pk` int(11) NOT NULL auto_increment,
  `tag_depth` int(11) NOT NULL default '0',
  `tag_name` char(50) NOT NULL default '',
  PRIMARY KEY  (`tag_pk`)
) TYPE=MyISAM;

#
# Dumping data for table `xmltags`
#

# --------------------------------------------------------

#
# Table structure for table `xmlvalue`
#

CREATE TABLE `xmlvalue` (
  `tag_value_pk` int(11) NOT NULL auto_increment,
  `tag_fk` int(11) NOT NULL default '0',
  `tag_value` text NOT NULL,
  PRIMARY KEY  (`tag_value_pk`),
  KEY `tag_fk` (`tag_fk`),
  FULLTEXT KEY `tag_value` (`tag_value`)
) TYPE=MyISAM;

#
# Dumping data for table `xmlvalue`
#


