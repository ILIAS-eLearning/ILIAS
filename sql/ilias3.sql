# phpMyAdmin SQL Dump
# version 2.5.7-pl1
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Oct 19, 2004 at 10:34 PM
# Server version: 4.0.21
# PHP Version: 5.0.1
# 
# Database : `ilias320_rel`
# 

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
  `slm_id` int(11) NOT NULL default '0',
  `system_id` varchar(50) NOT NULL default '',
  `title` text NOT NULL,
  `description` text,
  `developer_id` varchar(50) default NULL,
  `type` char(3) NOT NULL default '',
  PRIMARY KEY  (`obj_id`),
  KEY `alm_id` (`slm_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
  `last_conn_timestamp` int(14) default NULL,
  PRIMARY KEY  (`usr_id`,`chat_id`,`room_id`)
) TYPE=MyISAM;

#
# Dumping data for table `chat_user`
#


# --------------------------------------------------------

#
# Table structure for table `conditions`
#

CREATE TABLE `conditions` (
  `id` int(11) NOT NULL auto_increment,
  `target_ref_id` int(11) NOT NULL default '0',
  `target_obj_id` int(11) NOT NULL default '0',
  `target_type` varchar(8) NOT NULL default '',
  `trigger_ref_id` int(11) NOT NULL default '0',
  `trigger_obj_id` int(11) NOT NULL default '0',
  `trigger_type` varchar(8) NOT NULL default '',
  `operator` varchar(64) default NULL,
  `value` varchar(64) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `conditions`
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
  `print_view_active` enum('y','n') default 'y',
  `numbering` enum('y','n') default 'n',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `content_object`
#


# --------------------------------------------------------

#
# Table structure for table `crs_archives`
#

CREATE TABLE `crs_archives` (
  `archive_id` int(11) NOT NULL auto_increment,
  `course_id` int(11) NOT NULL default '0',
  `archive_name` varchar(255) NOT NULL default '',
  `archive_type` tinyint(2) NOT NULL default '0',
  `archive_date` int(11) default NULL,
  `archive_size` int(11) default NULL,
  `archive_lang` varchar(16) default NULL,
  PRIMARY KEY  (`archive_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `crs_archives`
#


# --------------------------------------------------------

#
# Table structure for table `crs_items`
#

CREATE TABLE `crs_items` (
  `parent_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `activation_unlimited` tinyint(2) default NULL,
  `activation_start` int(8) default NULL,
  `activation_end` int(8) default NULL,
  `position` int(11) default NULL,
  PRIMARY KEY  (`parent_id`,`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `crs_items`
#


# --------------------------------------------------------

#
# Table structure for table `crs_members`
#

CREATE TABLE `crs_members` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `status` tinyint(2) NOT NULL default '0',
  `role` tinyint(2) NOT NULL default '0',
  `passed` tinyint(1) default NULL,
  PRIMARY KEY  (`obj_id`,`usr_id`)
) TYPE=MyISAM;

#
# Dumping data for table `crs_members`
#


# --------------------------------------------------------

#
# Table structure for table `crs_settings`
#

CREATE TABLE `crs_settings` (
  `obj_id` int(11) NOT NULL default '0',
  `syllabus` text,
  `contact_name` varchar(255) default NULL,
  `contact_responsibility` varchar(255) default NULL,
  `contact_phone` varchar(255) default NULL,
  `contact_email` varchar(255) default NULL,
  `contact_consultation` text,
  `activation_unlimited` tinyint(2) default NULL,
  `activation_start` int(11) default NULL,
  `activation_end` int(11) default NULL,
  `activation_offline` int(1) default NULL,
  `subscription_unlimited` tinyint(2) default NULL,
  `subscription_start` int(11) default NULL,
  `subscription_end` int(11) default NULL,
  `subscription_type` int(2) default NULL,
  `subscription_password` varchar(32) default NULL,
  `subscription_max_members` int(4) default NULL,
  `subscription_notify` int(2) default NULL,
  `sortorder` int(2) default NULL,
  `archive_start` int(11) default NULL,
  `archive_end` int(11) default NULL,
  `archive_type` int(2) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `crs_settings`
#


# --------------------------------------------------------

#
# Table structure for table `crs_subscribers`
#

CREATE TABLE `crs_subscribers` (
  `usr_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `sub_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`obj_id`)
) TYPE=MyISAM;

#
# Dumping data for table `crs_subscribers`
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

INSERT INTO `ctrl_calls` VALUES ('ilobjquestionpoolgui', 'ilpageobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjquestionpoolgui', 'ass_multiplechoicegui');
INSERT INTO `ctrl_calls` VALUES ('ilobjquestionpoolgui', 'ass_clozetestgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjquestionpoolgui', 'ass_matchingquestiongui');
INSERT INTO `ctrl_calls` VALUES ('ilobjquestionpoolgui', 'ass_orderingquestiongui');
INSERT INTO `ctrl_calls` VALUES ('ilobjquestionpoolgui', 'ass_imagemapquestiongui');
INSERT INTO `ctrl_calls` VALUES ('ilobjquestionpoolgui', 'ass_javaappletgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjgroupgui', 'ilregistergui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjgroupgui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjfoldergui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjfilegui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjcoursegui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjsahslearningmodulegui');
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
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjcategorygui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjusergui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjrolegui');
INSERT INTO `ctrl_calls` VALUES ('ilrepositorygui', 'ilobjuserfoldergui');
INSERT INTO `ctrl_calls` VALUES ('ileditclipboardgui', 'ilobjmediaobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilglossarytermgui', 'iltermdefinitioneditorgui');
INSERT INTO `ctrl_calls` VALUES ('illmeditorgui', 'ilobjdlbookgui');
INSERT INTO `ctrl_calls` VALUES ('illmeditorgui', 'ilmetadatagui');
INSERT INTO `ctrl_calls` VALUES ('illmeditorgui', 'ilobjlearningmodulegui');
INSERT INTO `ctrl_calls` VALUES ('illmpageobjectgui', 'ilpageobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjaicclearningmodulegui', 'ilfilesystemgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjdlbookgui', 'illmpageobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjdlbookgui', 'ilstructureobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjdlbookgui', 'ilobjstylesheetgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjfilebasedlmgui', 'ilfilesystemgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjglossarygui', 'ilglossarytermgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjhacplearningmodulegui', 'ilfilesystemgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjlearningmodulegui', 'illmpageobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjlearningmodulegui', 'ilstructureobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjlearningmodulegui', 'ilobjstylesheetgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjmediapoolgui', 'ilobjmediaobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjmediapoolgui', 'ilobjfoldergui');
INSERT INTO `ctrl_calls` VALUES ('ilobjmediapoolgui', 'ileditclipboardgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjscormlearningmodulegui', 'ilfilesystemgui');
INSERT INTO `ctrl_calls` VALUES ('iltermdefinitioneditorgui', 'ilpageobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjmediaobjectgui', 'ilinternallinkgui');
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
INSERT INTO `ctrl_calls` VALUES ('ilpageeditorgui', 'ilpcquestiongui');
INSERT INTO `ctrl_calls` VALUES ('ilpageobjectgui', 'ilpageeditorgui');
INSERT INTO `ctrl_calls` VALUES ('ilpageobjectgui', 'ileditclipboardgui');
INSERT INTO `ctrl_calls` VALUES ('ilobjcoursegui', 'ilcourseregistergui');
INSERT INTO `ctrl_calls` VALUES ('ilobjcoursegui', 'ilpaymentpurchasegui');
INSERT INTO `ctrl_calls` VALUES ('ilpaymentgui', 'ilpaymentshoppincartgui');
INSERT INTO `ctrl_calls` VALUES ('ilpaymentgui', 'ilpaymentshoppingcartgui');
INSERT INTO `ctrl_calls` VALUES ('ilpaymentadmingui', 'ilpaymenttrusteegui');
INSERT INTO `ctrl_calls` VALUES ('ilpaymentadmingui', 'ilpaymentstatisticgui');
INSERT INTO `ctrl_calls` VALUES ('ilpaymentadmingui', 'ilpaymentobjectgui');
INSERT INTO `ctrl_calls` VALUES ('ilpaymentadmingui', 'ilpaymentbilladmingui');

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

INSERT INTO `ctrl_classfile` VALUES ('ilobjquestionpoolgui', 'assessment/classes/class.ilObjQuestionPoolGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjgroupgui', 'classes/class.ilObjGroupGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilrepositorygui', 'classes/class.ilRepositoryGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ileditclipboardgui', 'content/classes/class.ilEditClipboardGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilglossarytermgui', 'content/classes/class.ilGlossaryTermGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('illmeditorgui', 'content/classes/class.ilLMEditorGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('illmpageobjectgui', 'content/classes/class.ilLMPageObjectGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjaicclearningmodulegui', 'content/classes/class.ilObjAICCLearningModuleGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjdlbookgui', 'content/classes/class.ilObjDlBookGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjfilebasedlmgui', 'content/classes/class.ilObjFileBasedLMGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjglossarygui', 'content/classes/class.ilObjGlossaryGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjhacplearningmodulegui', 'content/classes/class.ilObjHACPLearningModuleGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjlearningmodulegui', 'content/classes/class.ilObjLearningModuleGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjmediapoolgui', 'content/classes/class.ilObjMediaPoolGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjscormlearningmodulegui', 'content/classes/class.ilObjSCORMLearningModuleGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('iltermdefinitioneditorgui', 'content/classes/class.ilTermDefinitionEditorGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjmediaobjectgui', 'content/classes/Media/class.ilObjMediaObjectGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilpageeditorgui', 'content/classes/Pages/class.ilPageEditorGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilpageobjectgui', 'content/classes/Pages/class.ilPageObjectGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilobjcoursegui', 'course/classes/class.ilObjCourseGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilpaymentgui', 'payment/classes/class.ilPaymentGUI.php');
INSERT INTO `ctrl_classfile` VALUES ('ilpaymentadmingui', 'payment/classes/class.ilPaymentAdminGUI.php');

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
) TYPE=MyISAM COMMENT='Tabelle für Anzeige von Geänderten Termindaten' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM COMMENT='Termin Tabelle' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM COMMENT='Tabelle für Schlagwörter' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM COMMENT='Tabelle für die Zuordnung der Schlagwörter' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM COMMENT='Tabelle für die negativen Termine' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM COMMENT='Tabelle für UserEinstellungen' AUTO_INCREMENT=1 ;

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
# Table structure for table `exc_returned`
#

CREATE TABLE `exc_returned` (
  `returned_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `filename` mediumtext NOT NULL,
  `filetitle` mediumtext NOT NULL,
  `mimetype` varchar(40) NOT NULL default '',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`returned_id`),
  KEY `obj_id` (`obj_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `exc_returned`
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=2 ;

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

INSERT INTO `lng_data` VALUES ('administration', 'analyze_data', 'en', 0x416e616c797a65206461746120496e74656772697479);
INSERT INTO `lng_data` VALUES ('administration', 'analyze_desc', 'en', 0x5363616e2073797374656d20666f7220636f727275707465642f696e76616c69642f6d697373696e672f756e626f756e64206f626a656374732e);
INSERT INTO `lng_data` VALUES ('administration', 'analyzing', 'en', 0x416e616c797a696e672e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'clean', 'en', 0x436c65616e207570);
INSERT INTO `lng_data` VALUES ('administration', 'clean_desc', 'en', 0x52656d6f766520696e76616c6964207265666572656e6365732026207472656520656e74726965732e20436c6f7365206761707320696e2074726565207374727563747572652e);
INSERT INTO `lng_data` VALUES ('administration', 'cleaning', 'en', 0x436c65616e696e672e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'cleaning_final', 'en', 0x46696e616c20636c65616e696e672e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'closing_gaps', 'en', 0x436c6f73696e67206761707320696e20747265652e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'disabled', 'en', 0x64697361626c6564);
INSERT INTO `lng_data` VALUES ('administration', 'done', 'en', 0x446f6e65);
INSERT INTO `lng_data` VALUES ('administration', 'found', 'en', 0x666f756e642e);
INSERT INTO `lng_data` VALUES ('administration', 'found_none', 'en', 0x6e6f6e6520666f756e642e);
INSERT INTO `lng_data` VALUES ('administration', 'log_scan', 'en', 0x4c6f67207363616e20726573756c7473);
INSERT INTO `lng_data` VALUES ('administration', 'log_scan_desc', 'en', 0x5772697465207363616e20726573756c747320746f20277363616e6c6f672e6c6f672720696e20636c69656e742064617461206469726563746f72792e);
INSERT INTO `lng_data` VALUES ('administration', 'nothing_to_purge', 'en', 0x4e6f7468696e6720746f2070757267652e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'nothing_to_remove', 'en', 0x4e6f7468696e6720746f2072656d6f76652e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'nothing_to_restore', 'en', 0x4e6f7468696e6720746f20726573746f72652e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'options', 'en', 0x4f7074696f6e73);
INSERT INTO `lng_data` VALUES ('administration', 'purge_missing', 'en', 0x5075726765206d697373696e67206f626a65637473);
INSERT INTO `lng_data` VALUES ('administration', 'purge_missing_desc', 'en', 0x52656d6f766520616c6c206d697373696e6720616e6420756e626f756e64206f626a6563747320666f756e642066726f6d2073797374656d2e);
INSERT INTO `lng_data` VALUES ('administration', 'purge_trash', 'en', 0x50757267652064656c65746564206f626a65637473);
INSERT INTO `lng_data` VALUES ('administration', 'purge_trash_desc', 'en', 0x52656d6f766520616c6c206f626a6563747320696e2074726173682062696e2066726f6d2073797374656d2e);
INSERT INTO `lng_data` VALUES ('administration', 'purging', 'en', 0x50757267696e672e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'purging_missing_objs', 'en', 0x50757267696e67206d697373696e67206f626a656374732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'purging_trash', 'en', 0x50757267696e672074726173682e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'purging_unbound_objs', 'en', 0x50757267696e6720756e626f756e64206f626a656374732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'removing_invalid_childs', 'en', 0x52656d6f76696e6720696e76616c6964207472656520656e74726965732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'removing_invalid_refs', 'en', 0x52656d6f76696e6720696e76616c6964207265666572656e6365732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'restore_missing', 'en', 0x526573746f7265206d697373696e67206f626a65637473);
INSERT INTO `lng_data` VALUES ('administration', 'restore_missing_desc', 'en', 0x526573746f7265206d697373696e6720616e6420756e626f756e64206f626a6563747320746f205265636f76657279466f6c6465722e);
INSERT INTO `lng_data` VALUES ('administration', 'restore_trash', 'en', 0x526573746f72652064656c65746564206f626a65637473);
INSERT INTO `lng_data` VALUES ('administration', 'restore_trash_desc', 'en', 0x526573746f726520616c6c206f626a6563747320696e2074726173682062696e20746f205265636f76657279466f6c6465722e);
INSERT INTO `lng_data` VALUES ('administration', 'restoring', 'en', 0x526573746f72696e672e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'restoring_missing_objs', 'en', 0x526573746f72696e67206d697373696e67206f626a656374732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'restoring_trash', 'en', 0x526573746f72696e672074726173682e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'restoring_unbound_objs', 'en', 0x526573746f72696e6720756e626f756e64206f626a656374732026207375626f626a656374732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'scan_completed', 'en', 0x5363616e20636f6d706c65746564);
INSERT INTO `lng_data` VALUES ('administration', 'scan_details', 'en', 0x5363616e2064657461696c73);
INSERT INTO `lng_data` VALUES ('administration', 'scan_modes', 'en', 0x5363616e206d6f6465732075736564);
INSERT INTO `lng_data` VALUES ('administration', 'scan_only', 'en', 0x5363616e206f6e6c79);
INSERT INTO `lng_data` VALUES ('administration', 'scanning_system', 'en', 0x5363616e6e696e672073797374656d2e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'searching_deleted_objs', 'en', 0x536561726368696e6720666f722064656c65746564206f626a656374732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'searching_invalid_childs', 'en', 0x536561726368696e6720666f7220696e76616c6964207472656520656e74726965732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'searching_invalid_refs', 'en', 0x536561726368696e6720666f7220696e76616c6964207265666572656e6365732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'searching_missing_objs', 'en', 0x536561726368696e6720666f72206d697373696e67206f626a656374732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'searching_unbound_objs', 'en', 0x536561726368696e6720666f7220756e626f756e64206f626a656374732e2e2e);
INSERT INTO `lng_data` VALUES ('administration', 'skipped', 'en', 0x736b6970706564);
INSERT INTO `lng_data` VALUES ('administration', 'start_scan', 'en', 0x537461727421);
INSERT INTO `lng_data` VALUES ('administration', 'stop_scan', 'en', 0x53746f7021);
INSERT INTO `lng_data` VALUES ('administration', 'systemcheck', 'en', 0x53797374656d20636865636b);
INSERT INTO `lng_data` VALUES ('administration', 'view_last_log', 'en', 0x56696577206c617374205363616e206c6f67);
INSERT INTO `lng_data` VALUES ('administration', 'view_log', 'en', 0x566965772064657461696c73);
INSERT INTO `lng_data` VALUES ('assessment', '0_unlimited', 'en', 0x28303d756e6c696d6974656429);
INSERT INTO `lng_data` VALUES ('assessment', 'add_answer', 'en', 0x41646420616e73776572);
INSERT INTO `lng_data` VALUES ('assessment', 'add_answer_tf', 'en', 0x41646420747275652f66616c736520616e7377657273);
INSERT INTO `lng_data` VALUES ('assessment', 'add_answer_yn', 'en', 0x416464207965732f6e6f20616e7377657273);
INSERT INTO `lng_data` VALUES ('assessment', 'add_applet_parameter', 'en', 0x416464206170706c657420706172616d65746572);
INSERT INTO `lng_data` VALUES ('assessment', 'add_area', 'en', 0x4164642061726561);
INSERT INTO `lng_data` VALUES ('assessment', 'add_gap', 'en', 0x416464204761702054657874);
INSERT INTO `lng_data` VALUES ('assessment', 'add_imagemap', 'en', 0x496d706f727420696d616765206d6170);
INSERT INTO `lng_data` VALUES ('assessment', 'add_matching_pair', 'en', 0x416464206d61746368696e672070616972);
INSERT INTO `lng_data` VALUES ('assessment', 'add_solution', 'en', 0x4164642073756767657374656420736f6c7574696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'all_available_question_pools', 'en', 0x416c6c20617661696c61626c65207175657374696f6e20706f6f6c73);
INSERT INTO `lng_data` VALUES ('assessment', 'answer', 'en', 0x416e73776572);
INSERT INTO `lng_data` VALUES ('assessment', 'answer_is_right', 'en', 0x596f757220736f6c7574696f6e206973207269676874);
INSERT INTO `lng_data` VALUES ('assessment', 'answer_is_wrong', 'en', 0x596f757220736f6c7574696f6e2069732077726f6e67);
INSERT INTO `lng_data` VALUES ('assessment', 'answer_picture', 'en', 0x416e737765722070696374757265);
INSERT INTO `lng_data` VALUES ('assessment', 'answer_text', 'en', 0x416e737765722074657874);
INSERT INTO `lng_data` VALUES ('assessment', 'applet_attributes', 'en', 0x4170706c65742061747472696275746573);
INSERT INTO `lng_data` VALUES ('assessment', 'applet_new_parameter', 'en', 0x4e657720706172616d65746572);
INSERT INTO `lng_data` VALUES ('assessment', 'applet_parameter', 'en', 0x506172616d65746572);
INSERT INTO `lng_data` VALUES ('assessment', 'applet_parameters', 'en', 0x4170706c657420706172616d6574657273);
INSERT INTO `lng_data` VALUES ('assessment', 'apply', 'en', 0x4170706c79);
INSERT INTO `lng_data` VALUES ('assessment', 'ass_create_export_file', 'en', 0x437265617465206578706f72742066696c65);
INSERT INTO `lng_data` VALUES ('assessment', 'ass_export_files', 'en', 0x4578706f72742066696c6573);
INSERT INTO `lng_data` VALUES ('assessment', 'ass_file', 'en', 0x46696c65);
INSERT INTO `lng_data` VALUES ('assessment', 'ass_questions', 'en', 0x5175657374696f6e73);
INSERT INTO `lng_data` VALUES ('assessment', 'ass_size', 'en', 0x53697a65);
INSERT INTO `lng_data` VALUES ('assessment', 'available_points', 'en', 0x417661696c61626c6520706f696e7473);
INSERT INTO `lng_data` VALUES ('assessment', 'by', 'en', 0x6279);
INSERT INTO `lng_data` VALUES ('assessment', 'cancel_test', 'en', 0x43616e63656c207468652074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'cannot_export_test', 'en', 0x596f7520646f206e6f7420706f73736573732073756666696369656e74207065726d697373696f6e7320746f206578706f727420746865207465737421);
INSERT INTO `lng_data` VALUES ('assessment', 'cannot_maintain_test', 'en', 0x596f7520646f206e6f7420706f73736573732073756666696369656e74207065726d697373696f6e7320746f206d61696e7461696e20746865207465737421);
INSERT INTO `lng_data` VALUES ('assessment', 'cannot_save_metaobject', 'en', 0x596f7520646f206e6f7420706f73736573732073756666696369656e74207065726d697373696f6e7320746f207361766520746865206d657461206461746121);
INSERT INTO `lng_data` VALUES ('assessment', 'change_solution', 'en', 0x4368616e67652073756767657374656420736f6c7574696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'checkbox_checked', 'en', 0x436865636b6564);
INSERT INTO `lng_data` VALUES ('assessment', 'checkbox_unchecked', 'en', 0x556e636865636b6564);
INSERT INTO `lng_data` VALUES ('assessment', 'circle', 'en', 0x436972636c65);
INSERT INTO `lng_data` VALUES ('assessment', 'circle_click_center', 'en', 0x506c6561736520636c69636b206f6e2063656e746572206f6620746865206465736972656420617265612e);
INSERT INTO `lng_data` VALUES ('assessment', 'circle_click_circle', 'en', 0x506c6561736520636c69636b206f6e206120636972636c6520706f696e74206f6620746865206465736972656420617265612e);
INSERT INTO `lng_data` VALUES ('assessment', 'cloze_text', 'en', 0x436c6f73652074657874);
INSERT INTO `lng_data` VALUES ('assessment', 'code', 'en', 0x436f6465);
INSERT INTO `lng_data` VALUES ('assessment', 'coordinates', 'en', 0x436f6f7264696e61746573);
INSERT INTO `lng_data` VALUES ('assessment', 'correct_solution_is', 'en', 0x54686520636f727265637420736f6c7574696f6e206973);
INSERT INTO `lng_data` VALUES ('assessment', 'counter', 'en', 0x436f756e746572);
INSERT INTO `lng_data` VALUES ('assessment', 'create_date', 'en', 0x43726561746564);
INSERT INTO `lng_data` VALUES ('assessment', 'create_gaps', 'en', 0x4372656174652067617073);
INSERT INTO `lng_data` VALUES ('assessment', 'create_imagemap', 'en', 0x43726561746520616e20496d616765206d6170);
INSERT INTO `lng_data` VALUES ('assessment', 'create_new', 'en', 0x437265617465206e6577);
INSERT INTO `lng_data` VALUES ('assessment', 'delete_area', 'en', 0x44656c6574652061726561);
INSERT INTO `lng_data` VALUES ('assessment', 'detail_ending_time_reached', 'en', 0x596f75206861766520726561636865642074686520656e64696e672074696d65206f662074686520746573742e2054686520746573742069736e277420617661696c61626c652073696e6365202573);
INSERT INTO `lng_data` VALUES ('assessment', 'detail_max_processing_time_reached', 'en', 0x596f752068617665207265616368656420746865206d6178696d756d20616c6c6f7765642070726f63657373696e672074696d65206f6620746865207465737421);
INSERT INTO `lng_data` VALUES ('assessment', 'detail_starting_time_not_reached', 'en', 0x596f752068617665206e6f74207265616368656420746865207374617274696e672074696d65206f662074686520746573742e2054686520746573742077696c6c20626520617661696c61626c65206f6e202573);
INSERT INTO `lng_data` VALUES ('assessment', 'direct_feedback', 'en', 0x56657269667920796f757220736f6c7574696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'duplicate', 'en', 0x4475706c6963617465);
INSERT INTO `lng_data` VALUES ('assessment', 'duplicate_matching_values_selected', 'en', 0x596f7520686176652073656c6563746564206475706c6963617465206d61746368696e672076616c75657321);
INSERT INTO `lng_data` VALUES ('assessment', 'duplicate_order_values_entered', 'en', 0x596f75206861766520656e7465726564206475706c6963617465206f726465722076616c75657321);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_allow_ects_grades', 'en', 0x53686f772045435453206772616465206164646974696f6e616c20746f207265636569766564206d61726b);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_fill_out_all_values', 'en', 0x506c6561736520656e74657220612076616c756520666f72206576657279204543545320677261646521);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade', 'en', 0x45435453206772616465);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_a', 'en', 0x6f75747374616e64696e6720706572666f726d616e63652077697468206f6e6c79206d696e6f72206572726f7273);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_a_short', 'en', 0x455843454c4c454e54);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_b', 'en', 0x61626f7665207468652061766572616765207374616e6461726420627574207769746820736f6d65206572726f7273);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_b_short', 'en', 0x5645525920474f4f44);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_c', 'en', 0x67656e6572616c6c7920736f756e6420776f726b20776974682061206e756d626572206f66206e6f7461626c65206572726f7273);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_c_short', 'en', 0x474f4f44);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_d', 'en', 0x66616972206275742077697468207369676e69666963616e742073686f7274636f6d696e6773);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_d_short', 'en', 0x5341544953464143544f5259);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_e', 'en', 0x706572666f726d616e6365206d6565747320746865206d696e696d756d206372697465726961);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_e_short', 'en', 0x53554646494349454e54);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_f', 'en', 0x636f6e736964657261626c65206675727468657220776f726b206973207265717569726564);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_f_short', 'en', 0x4641494c);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_fx', 'en', 0x736f6d65206d6f726520776f726b207265717569726564206265666f726520746865206372656469742063616e2062652061776172646564);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_grade_fx_short', 'en', 0x4641494c);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_output_of_ects_grades', 'en', 0x4f7574707574206f66204543545320677261646573);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_range_error_a', 'en', 0x506c6561736520656e74657220612076616c7565206265747765656e203020616e642031303020666f722045435453206772616465204121);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_range_error_b', 'en', 0x506c6561736520656e74657220612076616c7565206265747765656e203020616e642031303020666f722045435453206772616465204221);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_range_error_c', 'en', 0x506c6561736520656e74657220612076616c7565206265747765656e203020616e642031303020666f722045435453206772616465204321);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_range_error_d', 'en', 0x506c6561736520656e74657220612076616c7565206265747765656e203020616e642031303020666f722045435453206772616465204421);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_range_error_e', 'en', 0x506c6561736520656e74657220612076616c7565206265747765656e203020616e642031303020666f722045435453206772616465204521);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_use_fx_grade', 'en', 0x5573652074686520274658272067726164652c206966206661696c6564207061727469636970616e74732072656163686564206174206c65617374);
INSERT INTO `lng_data` VALUES ('assessment', 'ects_use_fx_grade_part2', 'en', 0x70657263656e74206f662074686520746f74616c20706f696e74732e);
INSERT INTO `lng_data` VALUES ('assessment', 'end_tag', 'en', 0x456e6420746167);
INSERT INTO `lng_data` VALUES ('assessment', 'ending_time_reached', 'en', 0x596f75206861766520726561636865642074686520656e64696e672074696d65);
INSERT INTO `lng_data` VALUES ('assessment', 'error_image_upload_copy_file', 'en', 0x54686572652077617320616e206572726f72206d6f76696e67207468652075706c6f6164656420696d6167652066696c6520746f206974732064657374696e6174696f6e20666f6c64657221);
INSERT INTO `lng_data` VALUES ('assessment', 'error_image_upload_wrong_format', 'en', 0x506c656173652075706c6f616420612076616c696420696d6167652066696c652120537570706f7274656420696d61676520666f726d61747320617265206769662c206a70656720616e6420706e672e);
INSERT INTO `lng_data` VALUES ('assessment', 'error_importing_question', 'en', 0x54686572652077617320616e206572726f7220696d706f7274696e6720746865207175657374696f6e2873292066726f6d207468652066696c6520796f7520686176652073656c656374656421);
INSERT INTO `lng_data` VALUES ('assessment', 'error_open_image_file', 'en', 0x4572726f72206f70656e696e6720616e20696d6167652066696c6521);
INSERT INTO `lng_data` VALUES ('assessment', 'error_open_java_file', 'en', 0x4572726f72206f70656e696e6720616e206a617661206170706c657421);
INSERT INTO `lng_data` VALUES ('assessment', 'error_save_image_file', 'en', 0x4572726f7220736176696e6720616e20696d6167652066696c6521);
INSERT INTO `lng_data` VALUES ('assessment', 'eval_choice_result', 'en', 0x5468652063686f6963652077697468207468652076616c75652025732069732025732e20596f7520726563656976656420257320706f696e74732e);
INSERT INTO `lng_data` VALUES ('assessment', 'eval_cloze_result', 'en', 0x476170206e756d6265722025732077697468207468652076616c75652025732069732025732e20596f7520726563656976656420257320706f696e74732e);
INSERT INTO `lng_data` VALUES ('assessment', 'eval_data', 'en', 0x4576616c756174696f6e2044617461);
INSERT INTO `lng_data` VALUES ('assessment', 'eval_imagemap_result', 'en', 0x54686520696d616765206d61702073656c656374696f6e2069732025732e20596f7520726563656976656420257320706f696e74732e);
INSERT INTO `lng_data` VALUES ('assessment', 'eval_java_result', 'en', 0x54686520616e7377657220666f72207468652076616c75652025732069732025732e20596f7520726563656976656420257320706f696e74732e);
INSERT INTO `lng_data` VALUES ('assessment', 'eval_legend_link', 'en', 0x506c6561736520726566657220746f20746865206c6567656e6420666f7220746865206d65616e696e67206f662074686520636f6c756d6e206865616465722073796d626f6c732e);
INSERT INTO `lng_data` VALUES ('assessment', 'eval_matching_result', 'en', 0x546865206d61746368696e672070616972202573202d2025732069732025732e20596f7520726563656976656420257320706f696e74732e);
INSERT INTO `lng_data` VALUES ('assessment', 'eval_order_result', 'en', 0x546865206f7264657220666f72207468652076616c75652025732069732025732e20596f7520726563656976656420257320706f696e74732e);
INSERT INTO `lng_data` VALUES ('assessment', 'exp_eval_data', 'en', 0x4578706f7274206576616c756174696f6e2064617461206173);
INSERT INTO `lng_data` VALUES ('assessment', 'exp_type_excel', 'en', 0x4d6963726f736f667420457863656c);
INSERT INTO `lng_data` VALUES ('assessment', 'exp_type_spss', 'en', 0x436f6d6d61207365706172617465642076616c7565202843535629);
INSERT INTO `lng_data` VALUES ('assessment', 'export', 'en', 0x4578706f7274);
INSERT INTO `lng_data` VALUES ('assessment', 'failed_official', 'en', 0x6661696c6564);
INSERT INTO `lng_data` VALUES ('assessment', 'failed_short', 'en', 0x6661696c6564);
INSERT INTO `lng_data` VALUES ('assessment', 'false', 'en', 0x46616c7365);
INSERT INTO `lng_data` VALUES ('assessment', 'fill_out_all_answer_fields', 'en', 0x506c656173652066696c6c206f757420616c6c20616e737765722074657874206669656c6473206265666f726520796f75206164642061206e6577206f6e6521);
INSERT INTO `lng_data` VALUES ('assessment', 'fill_out_all_matching_pairs', 'en', 0x506c656173652066696c6c206f757420616c6c206d61746368696e67207061697273206265666f726520796f75206164642061206e6577206f6e6521);
INSERT INTO `lng_data` VALUES ('assessment', 'fill_out_all_required_fields_add_answer', 'en', 0x506c656173652066696c6c206f757420616c6c207265717569726564206669656c6473206265666f726520796f752061646420616e737765727321);
INSERT INTO `lng_data` VALUES ('assessment', 'fill_out_all_required_fields_add_matching', 'en', 0x506c656173652066696c6c206f757420616c6c207265717569726564206669656c6473206265666f726520796f75206164642061206d61746368696e67207061697221);
INSERT INTO `lng_data` VALUES ('assessment', 'fill_out_all_required_fields_create_gaps', 'en', 0x506c656173652066696c6c206f757420616c6c207265717569726564206669656c6473206265666f726520796f7520637265617465206761707321);
INSERT INTO `lng_data` VALUES ('assessment', 'fill_out_all_required_fields_upload_image', 'en', 0x506c656173652066696c6c206f757420616c6c207265717569726564206669656c6473206265666f726520796f752075706c6f616420696d6167657321);
INSERT INTO `lng_data` VALUES ('assessment', 'fill_out_all_required_fields_upload_imagemap', 'en', 0x506c656173652066696c6c206f757420616c6c207265717569726564206669656c6473206265666f726520796f752075706c6f616420696d616765206d61707321);
INSERT INTO `lng_data` VALUES ('assessment', 'fill_out_all_required_fields_upload_material', 'en', 0x506c656173652066696c6c206f757420616c6c207265717569726564206669656c6473206265666f726520796f752075706c6f6164206d6174657269616c7321);
INSERT INTO `lng_data` VALUES ('assessment', 'filter', 'en', 0x46696c746572);
INSERT INTO `lng_data` VALUES ('assessment', 'filter_all_question_types', 'en', 0x416c6c207175657374696f6e207479706573);
INSERT INTO `lng_data` VALUES ('assessment', 'filter_all_questionpools', 'en', 0x416c6c207175657374696f6e20706f6f6c73);
INSERT INTO `lng_data` VALUES ('assessment', 'filter_show_question_types', 'en', 0x53686f77207175657374696f6e207479706573);
INSERT INTO `lng_data` VALUES ('assessment', 'filter_show_questionpools', 'en', 0x53686f77207175657374696f6e706f6f6c73);
INSERT INTO `lng_data` VALUES ('assessment', 'gap', 'en', 0x476170);
INSERT INTO `lng_data` VALUES ('assessment', 'gap_definition', 'en', 0x47617020646566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'gap_selection', 'en', 0x4761702073656c656374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'height', 'en', 0x486569676874);
INSERT INTO `lng_data` VALUES ('assessment', 'imagemap', 'en', 0x496d616765206d6170);
INSERT INTO `lng_data` VALUES ('assessment', 'imagemap_file', 'en', 0x496d616765206d61702066696c65);
INSERT INTO `lng_data` VALUES ('assessment', 'imagemap_source', 'en', 0x496d616765206d617020736f75726365);
INSERT INTO `lng_data` VALUES ('assessment', 'import_question', 'en', 0x496d706f7274207175657374696f6e287329);
INSERT INTO `lng_data` VALUES ('assessment', 'insert_after', 'en', 0x496e73657274206166746572);
INSERT INTO `lng_data` VALUES ('assessment', 'insert_before', 'en', 0x496e73657274206265666f7265);
INSERT INTO `lng_data` VALUES ('assessment', 'internal_links', 'en', 0x496e7465726e616c204c696e6b73);
INSERT INTO `lng_data` VALUES ('assessment', 'internal_links_update', 'en', 0x557064617465);
INSERT INTO `lng_data` VALUES ('assessment', 'javaapplet', 'en', 0x4a617661204170706c6574);
INSERT INTO `lng_data` VALUES ('assessment', 'javaapplet_successful_saved', 'en', 0x596f757220726573756c74732068617665206265656e207361766564207375636365737366756c21);
INSERT INTO `lng_data` VALUES ('assessment', 'javaapplet_unsuccessful_saved', 'en', 0x54686572652077617320616e206572726f7220736176696e6720746865207175657374696f6e20726573756c747321);
INSERT INTO `lng_data` VALUES ('assessment', 'last_update', 'en', 0x4c61737420757064617465);
INSERT INTO `lng_data` VALUES ('assessment', 'legend', 'en', 0x4c6567656e64);
INSERT INTO `lng_data` VALUES ('assessment', 'locked', 'en', 0x6c6f636b6564);
INSERT INTO `lng_data` VALUES ('assessment', 'maintenance', 'en', 0x4d61696e74656e616e6365);
INSERT INTO `lng_data` VALUES ('assessment', 'mark_schema', 'en', 0x4d61726b20736368656d61);
INSERT INTO `lng_data` VALUES ('assessment', 'match_terms_and_definitions', 'en', 0x4d61746368207465726d7320616e6420646566696e6974696f6e73);
INSERT INTO `lng_data` VALUES ('assessment', 'match_terms_and_pictures', 'en', 0x4d61746368207465726d7320616e64207069637475726573);
INSERT INTO `lng_data` VALUES ('assessment', 'matches', 'en', 0x6d617463686573);
INSERT INTO `lng_data` VALUES ('assessment', 'matching_pair', 'en', 0x4d61746368696e672070616972);
INSERT INTO `lng_data` VALUES ('assessment', 'material', 'en', 0x4d6174657269616c);
INSERT INTO `lng_data` VALUES ('assessment', 'material_download', 'en', 0x4d6174657269616c20746f20646f776e6c6f6164);
INSERT INTO `lng_data` VALUES ('assessment', 'material_file', 'en', 0x4d6174657269616c2066696c65);
INSERT INTO `lng_data` VALUES ('assessment', 'maximum_nr_of_tries_reached', 'en', 0x596f752068617665207265616368656420746865206d6178696d756d206e756d626572206f6620747269657320666f72207468697320746573742e2054686520746573742063616e6e6f7420626520656e746572656421);
INSERT INTO `lng_data` VALUES ('assessment', 'meaning', 'en', 0x4d65616e696e67);
INSERT INTO `lng_data` VALUES ('assessment', 'negative_points_not_allowed', 'en', 0x596f7520617265206e6f7420616c6c6f77656420746f20656e746572206e6567617469766520706f696e747321);
INSERT INTO `lng_data` VALUES ('assessment', 'next_question_rows', 'en', 0x5175657374696f6e73202564202d202564206f66202564203e3e);
INSERT INTO `lng_data` VALUES ('assessment', 'no_question_selected_for_move', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e20746f206d6f766520697421);
INSERT INTO `lng_data` VALUES ('assessment', 'no_questions_available', 'en', 0x546865726520617265206e6f207175657374696f6e7320617661696c61626c6521);
INSERT INTO `lng_data` VALUES ('assessment', 'no_target_selected_for_move', 'en', 0x596f75206d7573742073656c65637420612074617267657420706f736974696f6e21);
INSERT INTO `lng_data` VALUES ('assessment', 'or', 'en', 0x6f72);
INSERT INTO `lng_data` VALUES ('assessment', 'order_pictures', 'en', 0x4f72646572207069637475726573);
INSERT INTO `lng_data` VALUES ('assessment', 'order_terms', 'en', 0x4f72646572207465726d73);
INSERT INTO `lng_data` VALUES ('assessment', 'passed_official', 'en', 0x706173736564);
INSERT INTO `lng_data` VALUES ('assessment', 'passed_short', 'en', 0x706173736564);
INSERT INTO `lng_data` VALUES ('assessment', 'percentage_solved', 'en', 0x596f75206861766520736f6c76656420252e32662070657263656e74206f662074686973207175657374696f6e21);
INSERT INTO `lng_data` VALUES ('assessment', 'percentile', 'en', 0x50657263656e74696c65);
INSERT INTO `lng_data` VALUES ('assessment', 'please_select', 'en', 0x2d2d20706c656173652073656c656374202d2d);
INSERT INTO `lng_data` VALUES ('assessment', 'points', 'en', 0x506f696e7473);
INSERT INTO `lng_data` VALUES ('assessment', 'polygon', 'en', 0x506f6c79676f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'polygon_click_next_or_save', 'en', 0x506c6561736520636c69636b206f6e20746865206e65787420706f696e74206f662074686520706f6c79676f6e206f7220736176652074686520617265612e20284974206973206e6f74206e656365737361727920746f20636c69636b20616761696e206f6e20746865207374617274696e6720706f696e74206f66207468697320706f6c79676f6e202129);
INSERT INTO `lng_data` VALUES ('assessment', 'polygon_click_next_point', 'en', 0x506c6561736520636c69636b206f6e20746865206e65787420706f696e74206f662074686520706f6c79676f6e2e);
INSERT INTO `lng_data` VALUES ('assessment', 'polygon_click_starting_point', 'en', 0x506c6561736520636c69636b206f6e20746865207374617274696e6720706f696e74206f662074686520706f6c79676f6e2e);
INSERT INTO `lng_data` VALUES ('assessment', 'postpone', 'en', 0x506f7374706f6e65207175657374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'postponed', 'en', 0x706f7374706f6e6564);
INSERT INTO `lng_data` VALUES ('assessment', 'preview', 'en', 0x50726576696577);
INSERT INTO `lng_data` VALUES ('assessment', 'previous_question_rows', 'en', 0x3c3c205175657374696f6e73202564202d202564206f66202564);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_assessment_no_assessment_of_questions', 'en', 0x5468657265206973206e6f206173736573736d656e74206f66207175657374696f6e7320617661696c61626c6520666f72207468652073656c6563746564207175657374696f6e2e20546865207175657374696f6e20776173206e65766572207573656420696e206120746573742e);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_assessment_of_questions', 'en', 0x4173736573736d656e74206f66207175657374696f6e73);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_assessment_total_of_answers', 'en', 0x546f74616c206f6620616e7377657273);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_assessment_total_of_right_answers', 'en', 0x546f74616c2070657263656e74616765206f6620726967687420616e7377657273202870657263656e74616765206f66206d6178696d756d20706f696e747329);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_confirm_delete_questions', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e67207175657374696f6e2873293f20496620796f752064656c657465206c6f636b6564207175657374696f6e732074686520726573756c7473206f6620616c6c20746573747320636f6e7461696e696e672061206c6f636b6564207175657374696f6e2077696c6c2062652064656c6574656420746f6f2e);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_delete_rbac_error', 'en', 0x596f752068617665206e6f2072696768747320746f2064656c6574652074686973207175657374696f6e21);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_delete_select_none', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e20746f2064656c657465206974);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_display_fullsize_image', 'en', 0x436c69636b206865726520746f20646973706c617920746865206f726967696e616c20696d61676521);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_duplicate_select_none', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e20746f206475706c6963617465206974);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_edit_rbac_error', 'en', 0x596f752068617665206e6f2072696768747320746f20656469742074686973207175657374696f6e21);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_edit_select_multiple', 'en', 0x506c6561736520636865636b206f6e6c79206f6e65207175657374696f6e20666f722065646974696e67);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_edit_select_none', 'en', 0x506c6561736520636865636b206f6e65207175657374696f6e20666f722065646974696e67);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_export_select_none', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e20746f206578706f7274206974);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_question_is_in_use', 'en', 0x546865207175657374696f6e20796f75206172652061626f757420746f20656469742065786973747320696e20257320746573742873292e20496620796f75206368616e67652074686973207175657374696f6e2c20796f752077696c6c204e4f54206368616e676520746865207175657374696f6e28732920696e2074686520746573742873292c2062656361757365207468652073797374656d2063726561746573206120636f7079206f662061207175657374696f6e207768656e20697420697320696e73657274656420696e2061207465737421);
INSERT INTO `lng_data` VALUES ('assessment', 'qpl_questions_deleted', 'en', 0x5175657374696f6e2873292064656c657465642e);
INSERT INTO `lng_data` VALUES ('assessment', 'qt_cloze', 'en', 0x436c6f7365205175657374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'qt_imagemap', 'en', 0x496d616765204d6170205175657374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'qt_javaapplet', 'en', 0x4a617661204170706c6574205175657374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'qt_matching', 'en', 0x4d61746368696e67205175657374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'qt_multiple_choice_mr', 'en', 0x4d756c7469706c652043686f696365205175657374696f6e20286d756c7469706c6520726573706f6e736529);
INSERT INTO `lng_data` VALUES ('assessment', 'qt_multiple_choice_sr', 'en', 0x4d756c7469706c652043686f696365205175657374696f6e202873696e676c6520726573706f6e736529);
INSERT INTO `lng_data` VALUES ('assessment', 'qt_ordering', 'en', 0x4f72646572696e67205175657374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'question_saved_for_upload', 'en', 0x546865207175657374696f6e20776173207361766564206175746f6d61746963616c6c7920696e206f7264657220746f20726573657276652068617264206469736b20737061636520746f2073746f7265207468652075706c6f616465642066696c652e20496620796f752063616e63656c207468697320666f726d206e6f772c206265206177617265207468617420796f75206d7573742064656c65746520746865207175657374696f6e20696e20746865207175657374696f6e20706f6f6c20696620796f7520646f206e6f742077616e7420746f206b65657020697421);
INSERT INTO `lng_data` VALUES ('assessment', 'question_short', 'en', 0x51);
INSERT INTO `lng_data` VALUES ('assessment', 'question_type', 'en', 0x5175657374696f6e2054797065);
INSERT INTO `lng_data` VALUES ('assessment', 'questionpool_not_entered', 'en', 0x506c6561736520656e7465722061206e616d6520666f722061207175657374696f6e20706f6f6c21);
INSERT INTO `lng_data` VALUES ('assessment', 'radio_set', 'en', 0x536574);
INSERT INTO `lng_data` VALUES ('assessment', 'radio_unset', 'en', 0x556e736574);
INSERT INTO `lng_data` VALUES ('assessment', 'random_accept_sample', 'en', 0x4163636570742073616d706c65);
INSERT INTO `lng_data` VALUES ('assessment', 'random_another_sample', 'en', 0x47657420616e6f746865722073616d706c65);
INSERT INTO `lng_data` VALUES ('assessment', 'random_selection', 'en', 0x52616e646f6d2073656c656374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'rectangle', 'en', 0x52656374616e676c65);
INSERT INTO `lng_data` VALUES ('assessment', 'rectangle_click_br_corner', 'en', 0x506c6561736520636c69636b206f6e2074686520626f74746f6d20726967687420636f726e6572206f6620746865206465736972656420617265612e);
INSERT INTO `lng_data` VALUES ('assessment', 'rectangle_click_tl_corner', 'en', 0x506c6561736520636c69636b206f6e2074686520746f70206c65667420636f726e6572206f6620746865206465736972656420617265612e);
INSERT INTO `lng_data` VALUES ('assessment', 'region', 'en', 0x526567696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'remove_question', 'en', 0x52656d6f7665);
INSERT INTO `lng_data` VALUES ('assessment', 'remove_solution', 'en', 0x52656d6f76652073756767657374656420736f6c7574696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'report_date_not_reached', 'en', 0x596f752068617665206e6f74207265616368656420746865207265706f7274696e6720646174652e20596f752077696c6c2062652061626c6520746f207669657720746865207465737420726573756c7473206174202573);
INSERT INTO `lng_data` VALUES ('assessment', 'reset_filter', 'en', 0x52657365742066696c746572);
INSERT INTO `lng_data` VALUES ('assessment', 'result', 'en', 0x526573756c74);
INSERT INTO `lng_data` VALUES ('assessment', 'save_before_upload_imagemap', 'en', 0x596f75206d757374206170706c7920796f7572206368616e676573206265666f726520796f752063616e2075706c6f616420616e20696d616765206d617021);
INSERT INTO `lng_data` VALUES ('assessment', 'save_before_upload_javaapplet', 'en', 0x596f75206d757374206170706c7920796f7572206368616e676573206265666f726520796f752063616e2075706c6f61642061204a617661206170706c657421);
INSERT INTO `lng_data` VALUES ('assessment', 'save_edit', 'en', 0x5361766520616e64206564697420636f6e74656e74);
INSERT INTO `lng_data` VALUES ('assessment', 'save_finish', 'en', 0x5361766520616e642066696e697368207468652074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'save_introduction', 'en', 0x5361766520616e6420676f20746f20696e74726f64756374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'save_next', 'en', 0x5361766520616e6420676f20746f206e657874207175657374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'save_previous', 'en', 0x5361766520616e6420676f20746f2070726576696f7573207175657374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'select_gap', 'en', 0x53656c65637420676170);
INSERT INTO `lng_data` VALUES ('assessment', 'select_target_position_for_move_question', 'en', 0x506c656173652073656c65637420612074617267657420706f736974696f6e20746f206d6f766520746865207175657374696f6e28732920616e64207072657373206f6e65206f662074686520696e7365727420627574746f6e7321);
INSERT INTO `lng_data` VALUES ('assessment', 'selected_image', 'en', 0x53656c656374656420696d616765);
INSERT INTO `lng_data` VALUES ('assessment', 'set_filter', 'en', 0x5365742066696c746572);
INSERT INTO `lng_data` VALUES ('assessment', 'shape', 'en', 0x5368617065);
INSERT INTO `lng_data` VALUES ('assessment', 'shuffle_answers', 'en', 0x53687566666c6520616e7377657273);
INSERT INTO `lng_data` VALUES ('assessment', 'solution_hint', 'en', 0x53756767657374656420736f6c7574696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'solution_order', 'en', 0x536f6c7574696f6e206f72646572);
INSERT INTO `lng_data` VALUES ('assessment', 'start_tag', 'en', 0x537461727420746167);
INSERT INTO `lng_data` VALUES ('assessment', 'starting_time_not_reached', 'en', 0x596f752068617665206e6f74207265616368656420746865207374617274696e672074696d65);
INSERT INTO `lng_data` VALUES ('assessment', 'symbol', 'en', 0x53796d626f6c);
INSERT INTO `lng_data` VALUES ('assessment', 'test_cancelled', 'en', 0x5468652074657374207761732063616e63656c6c6564);
INSERT INTO `lng_data` VALUES ('assessment', 'text_gap', 'en', 0x5465787420676170);
INSERT INTO `lng_data` VALUES ('assessment', 'time_format', 'en', 0x4848204d4d205353);
INSERT INTO `lng_data` VALUES ('assessment', 'too_many_empty_parameters', 'en', 0x506c656173652066696c6c206f757420796f757220656d707479206170706c657420706172616d6574657273206265666f726520796f75206164642061206e657720706172616d6574657221);
INSERT INTO `lng_data` VALUES ('assessment', 'true', 'en', 0x54727565);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_all_user_data_deleted', 'en', 0x416c6c20757365722064617461206f662074686973207465737420686173206265656e2064656c6574656421);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_already_taken', 'en', 0x546573747320616c72656164792074616b656e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_anon_eval', 'en', 0x416e6f6e796d6f7573206167677265676174656420726573756c7473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_browse_for_questions', 'en', 0x42726f77736520666f72207175657374696f6e73);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_confirm_delete_results', 'en', 0x596f75206172652061626f757420746f2064656c65746520616c6c20796f75722070726576696f757320656e7465726564207175657374696f6e20726573756c7473206f66207468697320746573742e20546869732077696c6c20726573657420616c6c207175657374696f6e7320746f206974732064656661756c742076616c7565732e20506c65617365206e6f74652c207468617420796f752063616e6e6f7420726573657420746865206e756d626572206f662074726965732c20796f7520616c7265616479206e656564656420666f72207468697320746573742e20446f20796f75207265616c6c792077616e7420746f20726573657420616c6c20796f75722070726576696f757320656e746572656420726573756c74733f);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_confirm_delete_results_info', 'en', 0x596f75722070726576696f757320656e7465726564207175657374696f6e20726573756c747320776572652064656c657465642e20596f752068617665207375636365737366756c6c7920726573657420746865207465737421);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_delete_all_user_data', 'en', 0x44656c65746520616c6c20757365722064617461);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_delete_missing_mark', 'en', 0x506c656173652073656c656374206174206c65617374206f6e65206d61726b207374657020746f2064656c657465206974);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_delete_results', 'en', 0x52657365742074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_ending_time', 'en', 0x456e64696e672074696d65);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_enter_questionpool', 'en', 0x506c6561736520656e7465722061207175657374696f6e20706f6f6c206e616d6520776865726520746865206e6577207175657374696f6e2077696c6c2062652073746f726564);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_eval_no_anonymous_aggregation', 'en', 0x4e6f206f6e652068617320656e7465726564207468652074657374207965742e20546865726520617265206e6f20616e6f6e796d6f75732061676772656761746564207465737420726573756c747320617661696c61626c652e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_eval_total_finished', 'en', 0x546f74616c20636f6d706c65746564207465737473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_eval_total_finished_average_time', 'en', 0x417665726167652070726f63657373696e672074696d6520666f7220636f6d706c65746564207465737473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_eval_total_passed', 'en', 0x546f74616c20706173736564207465737473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_eval_total_passed_average_points', 'en', 0x4176657261676520706f696e7473206f6620706173736564207465737473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_eval_total_persons', 'en', 0x546f74616c206e756d626572206f6620706572736f6e7320656e7465726564207468652074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_general_properties', 'en', 0x47656e6572616c2070726f70657274696573);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_generate_xls', 'en', 0x47656e657261746520457863656c);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_insert_missing_question', 'en', 0x506c656173652073656c656374206174206c65617374206f6e65207175657374696f6e20746f20696e7365727420697420696e746f20746865207465737421);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_insert_questions', 'en', 0x41726520796f75207375726520796f752077616e7420746f20696e736572742074686520666f6c6c6f77696e67207175657374696f6e28732920746f2074686520746573743f);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_insert_questions_and_results', 'en', 0x5468697320746573742077617320616c726561647920657865637574656420627920257320757365722873292e20496e73657274696e67207175657374696f6e7320746f207468697320746573742077696c6c2072656d6f766520616c6c207465737420726573756c7473206f662074686573652075736572732e2041726520796f75207375726520796f752077616e7420746f20696e736572742074686520666f6c6c6f77696e67207175657374696f6e28732920746f2074686520746573743f);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_introduction', 'en', 0x496e74726f64756374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_mark', 'en', 0x4d61726b);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_mark_create_new_mark_step', 'en', 0x437265617465206e6577206d61726b2073746570);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_mark_create_simple_mark_schema', 'en', 0x4372656174652073696d706c65206d61726b20736368656d61);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_mark_minimum_level', 'en', 0x4d696e696d756d206c6576656c2028696e202529);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_mark_official_form', 'en', 0x4f6666696369616c20666f726d);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_mark_passed', 'en', 0x506173736564);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_mark_short_form', 'en', 0x53686f727420666f726d);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_maximum_points', 'en', 0x4d6178696d756d20706f696e7473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_missing_author', 'en', 0x596f752068617665206e6f7420656e74657265642074686520617574686f7273206e616d6520696e2074686520746573742070726f706572746965732120506c656173652061646420616e20617574686f7273206e616d652e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_missing_marks', 'en', 0x596f7520646f206e6f7420686176652061206d61726b20736368656d6120696e2074686520746573742120506c6561736520616464206174206c6561737420612073696d706c65206d61726b20736368656d6120746f2074686520746573742e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_missing_questions', 'en', 0x596f7520646f206e6f74206861766520616e79207175657374696f6e7320696e2074686520746573742120506c6561736520616464206174206c65617374206f6e65207175657374696f6e20696e746f2074686520746573742e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_missing_title', 'en', 0x596f752068617665206e6f7420656e746572656420612074657374207469746c652120506c6561736520676f20746f20746865206d657461646174612073656374696f6e20616e6420656e7465722061207469746c652e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_no_marks_defined', 'en', 0x546865726520617265206e6f206d61726b7320646566696e65642c20706c6561736520637265617465206174206c6561737420612073696d706c65206d61726b20736368656d6121);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_no_question_selected_for_removal', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e20746f2072656d6f766520697421);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_no_questions_available', 'en', 0x546865726520617265206e6f207175657374696f6e7320617661696c61626c652120596f75206e65656420746f20637265617465206e6577207175657374696f6e73206f722062726f77736520666f72207175657374696f6e732e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_no_taken_tests', 'en', 0x546865726520617265206e6f2074616b656e20746573747321);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_no_tries', 'en', 0x6e6f6e65);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_nr_of_tries', 'en', 0x4d61782e206e756d626572206f66207472696573);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_nr_of_tries_of_user', 'en', 0x547269657320616c726561647920636f6d706c65746564);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_percent_solved', 'en', 0x50657263656e7420736f6c766564);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_processing_time', 'en', 0x4d61782e2070726f63657373696e672074696d65);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_question_no', 'en', 0x4f72646572);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_question_offer', 'en', 0x446f20796f752061636365707420746869732073616d706c65206f7220646f20796f752077616e7420616e6f74686572206f6e653f);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_question_title', 'en', 0x5469746c65);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_question_type', 'en', 0x5175657374696f6e2074797065);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_questions_inserted', 'en', 0x5175657374696f6e28732920696e73657274656421);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_questions_removed', 'en', 0x5175657374696f6e2873292072656d6f76656421);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_random_nr_of_questions', 'en', 0x486f77206d616e79207175657374696f6e733f);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_random_select_questionpool', 'en', 0x53656c65637420746865207175657374696f6e20706f6f6c20746f2063686f6f736520746865207175657374696f6e732066726f6d);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_reached_points', 'en', 0x5265616368656420706f696e7473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_remove_questions', 'en', 0x41726520796f75207375726520796f752077616e7420746f2072656d6f76652074686520666f6c6c6f77696e67207175657374696f6e2873292066726f6d2074686520746573743f);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_remove_questions_and_results', 'en', 0x5468697320746573742077617320616c726561647920657865637574656420627920257320757365722873292e2052656d6f76696e67207175657374696f6e732066726f6d207468697320746573742077696c6c2072656d6f766520616c6c207465737420726573756c7473206f662074686573652075736572732e2041726520796f75207375726520796f752077616e7420746f2072656d6f76652074686520666f6c6c6f77696e67207175657374696f6e2873292066726f6d2074686520746573743f);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_report_after_question', 'en', 0x5265706f7274207468652073636f7265206166746572206576657279207175657374696f6e20697320616e737765726564);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_report_after_test', 'en', 0x5265706f7274207468652073636f726520616674657220636f6d706c6574696e67207468652077686f6c652074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_result_congratulations', 'en', 0x436f6e67726174756c6174696f6e732120596f75207061737365642074686520746573742e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_result_sorry', 'en', 0x536f7272792c20796f75206661696c65642074686520746573742e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_results', 'en', 0x5465737420726573756c7473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_resume_test', 'en', 0x526573756d65207468652074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_score_reporting', 'en', 0x53636f7265207265706f7274696e67);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_score_reporting_date', 'en', 0x53636f7265207265706f7274696e672064617465);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_score_type', 'en', 0x53636f7265207265706f7274696e672074797065);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_select_questionpool', 'en', 0x506c656173652073656c6563742061207175657374696f6e20706f6f6c20746f2073746f7265207468652063726561746564207175657374696f6e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_select_questionpools', 'en', 0x506c656173652073656c6563742061207175657374696f6e20706f6f6c20746f2073746f72652074686520696d706f72746564207175657374696f6e73);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_sequence', 'en', 0x53657175656e6365);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_sequence_fixed', 'en', 0x5468652073657175656e6365206f66207175657374696f6e73206973206669786564);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_sequence_postpone', 'en', 0x546865206c6561726e6572206d617920706f7374706f6e652061207175657374696f6e20746f2074686520656e64206f66207468652074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_sequence_properties', 'en', 0x53657175656e63652070726f70657274696573);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_session_settings', 'en', 0x53657373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_show_results', 'en', 0x53686f77207465737420726573756c7473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_start_test', 'en', 0x5374617274207468652074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_starting_time', 'en', 0x5374617274696e672074696d65);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_all_users', 'en', 0x416c6c207573657273);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_choose_users', 'en', 0x2873656c656374206f6e65206f72206d6f72652075736572732066726f6d207468652075736572206c69737429);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_atimeofwork', 'en', 0x417665726167652074696d65206f6620776f726b);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_distancemean', 'en', 0x44697374616e636520746f2061726974686d65746963206d65616e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_distancemedian', 'en', 0x44697374616e636520746f206d656469616e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_distancequintile', 'en', 0x44697374616e636520746f2061726974686d65746963207175696e74696c65);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_firstvisit', 'en', 0x4669727374207669736974);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_lastvisit', 'en', 0x4c617374207669736974);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_mark_median', 'en', 0x4d61726b206f66206d656469616e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_median', 'en', 0x4d656469616e206f66207465737420726573756c7420696e20706f696e7473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_pworkedthrough', 'en', 0x50657263656e74206f6620746f74616c20776f726b6c6f616420616c726561647920776f726b6564207468726f756768);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_qworkedthrough', 'en', 0x5175657374696f6e7320616c726561647920776f726b6564207468726f756768);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_rank_median', 'en', 0x52616e6b206f66206d656469616e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_rank_participant', 'en', 0x52616e6b206f66207061727469636970616e74);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_resultsmarks', 'en', 0x5465737420726573756c747320696e206d61726b73);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_resultspoints', 'en', 0x5465737420726573756c747320696e20706f696e7473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_specification', 'en', 0x53706563696669636174696f6e206f66206576616c756174696f6e2064617461);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_timeofwork', 'en', 0x54696d65206f6620776f726b);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_result_total_participants', 'en', 0x546f74616c206e756d626572206f66207061727469636970616e7473);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_selected_users', 'en', 0x53656c6563746564207573657273);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_stat_users_intro', 'en', 0x53686f7720737461746973746963616c206576616c756174696f6e20666f72);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_statistical_evaluation', 'en', 0x537461746973746963616c206576616c756174696f6e20746f6f6c);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_status_completed', 'en', 0x636f6d706c65746564);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_status_completed_more_tries_possible', 'en', 0x436f6d706c65746564);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_status_missing', 'en', 0x54686572652061726520726571756972656420656c656d656e7473206d697373696e6720696e2074686973207465737421);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_status_missing_elements', 'en', 0x54686520666f6c6c6f77696e6720656c656d656e747320617265206d697373696e673a);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_status_not_entered', 'en', 0x4e6f742073746172746564);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_status_ok', 'en', 0x54686520737461747573206f66207468652074657374206973204f4b2e20546865726520617265206e6f206d697373696e6720656c656d656e74732e);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_status_progress', 'en', 0x496e2070726f6772657373);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_time_already_spent', 'en', 0x54696d6520616c7265616479207370656e7420776f726b696e67);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_type', 'en', 0x546573742074797065);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_type_changed', 'en', 0x596f752068617665206368616e67656420746865207465737420747970652e20416c6c2070726576696f75732070726f6365737365642074657374732068617665206265656e2064656c6574656421);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_type_comment', 'en', 0x5761726e696e673a204368616e67696e672074686520746573742074797065206f6620612072756e6e696e6720746573742077696c6c2064656c65746520616c6c2070726576696f75732070726f63657373656420746573747321);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_types', 'en', 0x54657374207479706573);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_use_javascript', 'en', 0x557365204a61766173637269707420666f72206472616720616e642064726f7020616374696f6e73);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_your_ects_mark_is', 'en', 0x596f75722045435453206772616465206973);
INSERT INTO `lng_data` VALUES ('assessment', 'tst_your_mark_is', 'en', 0x596f7572206d61726b206973);
INSERT INTO `lng_data` VALUES ('assessment', 'tt_assessment', 'en', 0x4173736573736d656e742074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'tt_navigation_controlling', 'en', 0x436f6e74726f6c6c696e67206c6561726e657273206e617669676174696f6e2074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'tt_self_assessment', 'en', 0x53656c66206173736573736d656e742074657374);
INSERT INTO `lng_data` VALUES ('assessment', 'unlimited', 'en', 0x756e6c696d69746564);
INSERT INTO `lng_data` VALUES ('assessment', 'unlock', 'en', 0x556e6c6f636b);
INSERT INTO `lng_data` VALUES ('assessment', 'unlock_question', 'en', 0x54686973207175657374696f6e20697320696e20757365206f66206174206c65617374206f6e65207465737420616e6420746865726520617265202573207375626d697474656420726573756c74732e20496620796f752063686f6f736520746f20756e6c6f636b20746865207175657374696f6e2074686520636f6d706c657465207465737420726573756c7473206f66207468652061666665637465642075736572732077696c6c2062652064656c6574656420616674657220736176696e672074686973207175657374696f6e2e20496620796f752077616e7420746f207573652061206368616e6765642076657273696f6e206f662074686973207175657374696f6e2062757420796f7520646f206e6f742077616e7420746f2064656c65746520746865207573657273207465737420726573756c74732c20796f752063616e206475706c696361746520746865207175657374696f6e20696e20746865207175657374696f6e20706f6f6c20616e64206368616e676520746861742076657273696f6e2e);
INSERT INTO `lng_data` VALUES ('assessment', 'upload_imagemap', 'en', 0x55706c6f616420616e20496d616765206d6170);
INSERT INTO `lng_data` VALUES ('assessment', 'uploaded_material', 'en', 0x55706c6f61646564204d6174657269616c);
INSERT INTO `lng_data` VALUES ('assessment', 'warning_question_not_complete', 'en', 0x546865207175657374696f6e206973206e6f7420636f6d706c65746521);
INSERT INTO `lng_data` VALUES ('assessment', 'warning_test_not_complete', 'en', 0x5468652074657374206973206e6f7420636f6d706c65746521);
INSERT INTO `lng_data` VALUES ('assessment', 'when', 'en', 0x7768656e);
INSERT INTO `lng_data` VALUES ('assessment', 'width', 'en', 0x5769647468);
INSERT INTO `lng_data` VALUES ('assessment', 'with_order', 'en', 0x77697468206f72646572);
INSERT INTO `lng_data` VALUES ('assessment', 'working_time', 'en', 0x576f726b696e672054696d65);
INSERT INTO `lng_data` VALUES ('assessment', 'you_received_a_of_b_points', 'en', 0x596f75207265636569766564202564206f6620256420706f737369626c6520706f696e7473);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_add', 'en', 0x416464);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_author', 'en', 0x417574686f72);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_av', 'en', 0x4156);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_bibitem', 'en', 0x4269626c696f67726170686963616c20696e666f726d6174696f6e);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_book', 'en', 0x426f6f6b);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_booktitle', 'en', 0x426f6f6b207469746c65);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_catalog', 'en', 0x436174616c6f67);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_choose_index', 'en', 0x506c656173652073656c6563742061206269626c696f67726170686963616c20696e666f726d6174696f6e3a);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_cross_reference', 'en', 0x43726f7373207265666572656e6365);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_delete', 'en', 0x44656c657465);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_dissertation', 'en', 0x446973736572746174696f6e);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_edition', 'en', 0x45646974696f6e);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_editor', 'en', 0x456469746f72);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_entry', 'en', 0x456e747279);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_first_name', 'en', 0x4669727374206e616d65);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_grey_literature', 'en', 0x47726579206c697465726174757265);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_how_published', 'en', 0x486f77207075626c6973686564);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_identifier', 'en', 0x4964656e746966696572);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_inbook', 'en', 0x496e20626f6f6b);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_inproceedings', 'en', 0x496e2070726f63656564696e6773);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_institution', 'en', 0x496e737469747574696f6e);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_internet', 'en', 0x496e7465726e6574);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_isbn', 'en', 0x4953424e);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_issn', 'en', 0x4953534e);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_journal', 'en', 0x4a6f75726e616c);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_journal_article', 'en', 0x4a6f75726e616c2061727469636c65);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_keyword', 'en', 0x4b6579776f7264);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_label', 'en', 0x4c6162656c);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_language', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_last_name', 'en', 0x4c617374206e616d65);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_manual', 'en', 0x4d616e75616c);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_master_thesis', 'en', 0x4d617374657220746865736973);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_middle_name', 'en', 0x4d6964646c65206e616d65);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_month', 'en', 0x4d6f6e7468);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_new_element', 'en', 0x4e657720656c656d656e74);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_newspaper_article', 'en', 0x4e65777370617065722061727469636c65);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_not_found', 'en', 0x4e6f206269626c696f67726170686963616c20696e666f726d6174696f6e20617661696c61626c652e);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_note', 'en', 0x4e6f7465);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_number', 'en', 0x4e756d626572);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_organization', 'en', 0x4f7267616e697a6174696f6e);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_pages', 'en', 0x5061676573);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_phd_thesis', 'en', 0x50684420746865736973);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_please_select', 'en', 0x506c656173652073656c656374);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_print', 'en', 0x5072696e74);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_proceedings', 'en', 0x50726f63656564696e6773);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_publisher', 'en', 0x5075626c6973686572);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_save', 'en', 0x53617665);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_school', 'en', 0x5363686f6f6c);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_series', 'en', 0x536572696573);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_series_editor', 'en', 0x456469746f72);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_series_title', 'en', 0x536572696573207469746c65);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_series_volume', 'en', 0x566f6c756d65);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_technical_report', 'en', 0x546563686e6963616c207265706f7274);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_type', 'en', 0x54797065);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_unpublished', 'en', 0x556e7075626c6973686564);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_url', 'en', 0x55524c);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_where_published', 'en', 0x5768657265207075626c6973686564);
INSERT INTO `lng_data` VALUES ('bibitem', 'bibitem_year', 'en', 0x59656172);
INSERT INTO `lng_data` VALUES ('chat', 'chat_active', 'en', 0x416374697665);
INSERT INTO `lng_data` VALUES ('chat', 'chat_active_users', 'en', 0x55736572202861637469766529);
INSERT INTO `lng_data` VALUES ('chat', 'chat_add_allowed_hosts', 'en', 0x506c6561736520616464207468652061646472657373206f662074686520686f73742873292077686963682061726520616c6c6f77656420746f20616363657373207468652063686174207365727665722e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_add_ip', 'en', 0x506c656173652061646420746865207365727665722049502e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_add_moderator_password', 'en', 0x506c65617365206164642061206d6f64657261746f722070617373776f72642e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_add_port', 'en', 0x506c6561736520616464207468652073657276657220706f72742e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_aqua', 'en', 0x61717561);
INSERT INTO `lng_data` VALUES ('chat', 'chat_arial', 'en', 0x417269616c);
INSERT INTO `lng_data` VALUES ('chat', 'chat_black', 'en', 0x626c61636b);
INSERT INTO `lng_data` VALUES ('chat', 'chat_blue', 'en', 0x626c7565);
INSERT INTO `lng_data` VALUES ('chat', 'chat_bold', 'en', 0x426f6c64);
INSERT INTO `lng_data` VALUES ('chat', 'chat_chatroom_body', 'en', 0x4368617420726f6f6d3a);
INSERT INTO `lng_data` VALUES ('chat', 'chat_chatroom_rename', 'en', 0x45646974206368617420726f6f6d);
INSERT INTO `lng_data` VALUES ('chat', 'chat_chatrooms', 'en', 0x4368617420726f6f6d73);
INSERT INTO `lng_data` VALUES ('chat', 'chat_color', 'en', 0x466f6e7420636f6c6f72);
INSERT INTO `lng_data` VALUES ('chat', 'chat_delete_sure', 'en', 0x41726520796f7520737572652c20796f752077616e7420746f2064656c6574652074686973206368617420726f6f6d);
INSERT INTO `lng_data` VALUES ('chat', 'chat_drop_user', 'en', 0x55736572206b69636b6564);
INSERT INTO `lng_data` VALUES ('chat', 'chat_edit', 'en', 0x456469742063686174);
INSERT INTO `lng_data` VALUES ('chat', 'chat_face', 'en', 0x466f6e742074797065);
INSERT INTO `lng_data` VALUES ('chat', 'chat_fuchsia', 'en', 0x66756368736961);
INSERT INTO `lng_data` VALUES ('chat', 'chat_gray', 'en', 0x67726179);
INSERT INTO `lng_data` VALUES ('chat', 'chat_green', 'en', 0x677265656e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_hide_details', 'en', 0x486964652064657461696c73);
INSERT INTO `lng_data` VALUES ('chat', 'chat_html_export', 'en', 0x48544d4c206578706f7274);
INSERT INTO `lng_data` VALUES ('chat', 'chat_ilias', 'en', 0x494c4941532043686174206d6f64756c65);
INSERT INTO `lng_data` VALUES ('chat', 'chat_inactive', 'en', 0x496e616374697665);
INSERT INTO `lng_data` VALUES ('chat', 'chat_input', 'en', 0x496e707574);
INSERT INTO `lng_data` VALUES ('chat', 'chat_insert_message', 'en', 0x504c6561736520696e736572742061206d657373616765);
INSERT INTO `lng_data` VALUES ('chat', 'chat_invitation_body', 'en', 0x496e7669746174696f6e20746f20636861742066726f6d3a);
INSERT INTO `lng_data` VALUES ('chat', 'chat_invitation_subject', 'en', 0x4368617420696e7669746174696f6e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_invite_user', 'en', 0x496e766974652075736572);
INSERT INTO `lng_data` VALUES ('chat', 'chat_italic', 'en', 0x4974616c6963);
INSERT INTO `lng_data` VALUES ('chat', 'chat_level_all', 'en', 0x416c6c);
INSERT INTO `lng_data` VALUES ('chat', 'chat_level_debug', 'en', 0x4465627567);
INSERT INTO `lng_data` VALUES ('chat', 'chat_level_error', 'en', 0x4572726f72);
INSERT INTO `lng_data` VALUES ('chat', 'chat_level_fatal', 'en', 0x466174616c);
INSERT INTO `lng_data` VALUES ('chat', 'chat_level_info', 'en', 0x496e666f);
INSERT INTO `lng_data` VALUES ('chat', 'chat_lime', 'en', 0x6c696d65);
INSERT INTO `lng_data` VALUES ('chat', 'chat_maroon', 'en', 0x6d61726f6f6e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_messages_deleted', 'en', 0x4d657373616765732068617665206265656e2064656c657465642e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_moderator_password', 'en', 0x4d6f64657261746f722070617373776f7264);
INSERT INTO `lng_data` VALUES ('chat', 'chat_navy', 'en', 0x6e617679);
INSERT INTO `lng_data` VALUES ('chat', 'chat_no_active_users', 'en', 0x4e6f2075736572732070726573656e742e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_no_connection', 'en', 0x43616e6e6f7420636f6e746163742063686174207365727665722e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_no_delete_public', 'en', 0x546865207075626c6963206368617420726f6f6d2063616e6e6f742062652064656c65746564);
INSERT INTO `lng_data` VALUES ('chat', 'chat_no_refresh_public', 'en', 0x746865207075626c6963206368617420726f6f6d2063616e6e6f74206265207265667265736865642e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_no_rename_public', 'en', 0x546865207075626c696320726f6f6d2063616e6e6f742062652072656e616d6564);
INSERT INTO `lng_data` VALUES ('chat', 'chat_no_write_perm', 'en', 0x4e6f207065726d697373696f6e20746f207772697465);
INSERT INTO `lng_data` VALUES ('chat', 'chat_olive', 'en', 0x6f6c697665);
INSERT INTO `lng_data` VALUES ('chat', 'chat_online_users', 'en', 0x5573657220286f6e6c696e6529);
INSERT INTO `lng_data` VALUES ('chat', 'chat_private_message', 'en', 0x50726976617465204d657373616765);
INSERT INTO `lng_data` VALUES ('chat', 'chat_public_room', 'en', 0x5075626c6963206368617420726f6f6d);
INSERT INTO `lng_data` VALUES ('chat', 'chat_purple', 'en', 0x707572706c65);
INSERT INTO `lng_data` VALUES ('chat', 'chat_red', 'en', 0x726564);
INSERT INTO `lng_data` VALUES ('chat', 'chat_refresh', 'en', 0x52656672657368);
INSERT INTO `lng_data` VALUES ('chat', 'chat_room_added', 'en', 0x4164646564206e6577206368617420726f6f6d);
INSERT INTO `lng_data` VALUES ('chat', 'chat_room_name', 'en', 0x4e616d65206f66206368617420726f6f6d);
INSERT INTO `lng_data` VALUES ('chat', 'chat_room_renamed', 'en', 0x4368617420726f6f6d2072656e616d65642e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_room_select', 'en', 0x50726976617465206368617420726f6f6d);
INSERT INTO `lng_data` VALUES ('chat', 'chat_rooms', 'en', 0x4368617420726f6f6d73);
INSERT INTO `lng_data` VALUES ('chat', 'chat_rooms_deleted', 'en', 0x4368617420726f6f6d2873292064656c657465642e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_select_one_room', 'en', 0x506c656173652073656c656374206f6e65206368617420726f6f6d);
INSERT INTO `lng_data` VALUES ('chat', 'chat_server_allowed', 'en', 0x416c6c6f77656420686f737473);
INSERT INTO `lng_data` VALUES ('chat', 'chat_server_allowed_b', 'en', 0x28652e672e203139322e3136382e312e312c3139322e3136382e312e3229);
INSERT INTO `lng_data` VALUES ('chat', 'chat_server_ip', 'en', 0x536572766572204950);
INSERT INTO `lng_data` VALUES ('chat', 'chat_server_logfile', 'en', 0x4c6f672066696c652070617468);
INSERT INTO `lng_data` VALUES ('chat', 'chat_server_loglevel', 'en', 0x4c6f67206c6576656c);
INSERT INTO `lng_data` VALUES ('chat', 'chat_server_not_active', 'en', 0x546865206368617420736572766572206973206e6f7420616374697665);
INSERT INTO `lng_data` VALUES ('chat', 'chat_server_port', 'en', 0x53657276657220506f7274);
INSERT INTO `lng_data` VALUES ('chat', 'chat_server_settings', 'en', 0x43686174207365727665722073657474696e6773);
INSERT INTO `lng_data` VALUES ('chat', 'chat_settings_saved', 'en', 0x53657474696e6773207361766564);
INSERT INTO `lng_data` VALUES ('chat', 'chat_show_details', 'en', 0x53686f772064657461696c73);
INSERT INTO `lng_data` VALUES ('chat', 'chat_silver', 'en', 0x73696c766572);
INSERT INTO `lng_data` VALUES ('chat', 'chat_status', 'en', 0x537461747573);
INSERT INTO `lng_data` VALUES ('chat', 'chat_status_saved', 'en', 0x5468652073746174757320686173206265656e206368616e676564);
INSERT INTO `lng_data` VALUES ('chat', 'chat_tahoma', 'en', 0x5461686f6d61);
INSERT INTO `lng_data` VALUES ('chat', 'chat_teal', 'en', 0x7465616c);
INSERT INTO `lng_data` VALUES ('chat', 'chat_times', 'en', 0x54696d6573);
INSERT INTO `lng_data` VALUES ('chat', 'chat_title_missing', 'en', 0x506c6561736520696e736572742061206e616d652e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_to_chat_body', 'en', 0x546f207468652063686174);
INSERT INTO `lng_data` VALUES ('chat', 'chat_type', 'en', 0x466f6e742066616365);
INSERT INTO `lng_data` VALUES ('chat', 'chat_underlined', 'en', 0x556e6465726c696e6564);
INSERT INTO `lng_data` VALUES ('chat', 'chat_user_dropped', 'en', 0x55736572206b69636b6564);
INSERT INTO `lng_data` VALUES ('chat', 'chat_user_invited', 'en', 0x496e7669746564207573657220746f20636861742e);
INSERT INTO `lng_data` VALUES ('chat', 'chat_yellow', 'en', 0x79656c6c6f77);
INSERT INTO `lng_data` VALUES ('chat', 'chats', 'en', 0x4368617473);
INSERT INTO `lng_data` VALUES ('chat', 'my_chats', 'en', 0x4d79204368617473);
INSERT INTO `lng_data` VALUES ('common', '3rd_party_software', 'en', 0x33726420706172747920736f667477617265);
INSERT INTO `lng_data` VALUES ('common', 'DD.MM.YYYY', 'en', 0x44442e4d4d2e59595959);
INSERT INTO `lng_data` VALUES ('common', 'HH:MM', 'en', 0x48483a4d4d);
INSERT INTO `lng_data` VALUES ('common', 'absolute_path', 'en', 0x4162736f6c7574652050617468);
INSERT INTO `lng_data` VALUES ('common', 'accept_usr_agreement', 'en', 0x41636365707420757365722061677265656d656e743f);
INSERT INTO `lng_data` VALUES ('common', 'access', 'en', 0x416363657373);
INSERT INTO `lng_data` VALUES ('common', 'account_expires_body', 'en', 0x596f7572206163636f756e74206973206c696d697465642c2069742077696c6c206578706972652061743a);
INSERT INTO `lng_data` VALUES ('common', 'account_expires_subject', 'en', 0x5b494c49415320335d20596f7572206163636f756e742065787069726573);
INSERT INTO `lng_data` VALUES ('common', 'action_aborted', 'en', 0x416374696f6e2061626f72746564);
INSERT INTO `lng_data` VALUES ('common', 'actions', 'en', 0x416374696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'activate_https', 'en', 0x48545450532068616e646c696e6720627920494c4941533c62723e285061796d656e742c204c6f67696e29);
INSERT INTO `lng_data` VALUES ('common', 'activate_tracking', 'en', 0x416374697661746520547261636b696e67);
INSERT INTO `lng_data` VALUES ('common', 'activation', 'en', 0x41637469766174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'active', 'en', 0x416374697665);
INSERT INTO `lng_data` VALUES ('common', 'active_roles', 'en', 0x41637469766520526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'add', 'en', 0x416464);
INSERT INTO `lng_data` VALUES ('common', 'add_author', 'en', 0x41646420417574686f72);
INSERT INTO `lng_data` VALUES ('common', 'add_condition', 'en', 0x437265617465206e6577206173736f63696174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'add_member', 'en', 0x416464204d656d626572);
INSERT INTO `lng_data` VALUES ('common', 'add_members', 'en', 0x416464204d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'add_translation', 'en', 0x416464207472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'add_user', 'en', 0x416464206c6f63616c2075736572);
INSERT INTO `lng_data` VALUES ('common', 'added_new_condition', 'en', 0x43726561746564206e657720636f6e646974696f6e2e);
INSERT INTO `lng_data` VALUES ('common', 'adm_create_tax', 'en', 0x437265617465207461786f6e6f6d79);
INSERT INTO `lng_data` VALUES ('common', 'adm_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'adm_read', 'en', 0x526561642061636365737320746f2053797374656d2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'adm_visible', 'en', 0x53797374656d2073657474696e6773206172652076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'adm_write', 'en', 0x456469742042617369632073797374656d2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'administrate', 'en', 0x41646d696e69737472617465);
INSERT INTO `lng_data` VALUES ('common', 'administrate_users', 'en', 0x4c6f63616c20757365722061646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'administration', 'en', 0x41646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'administrator', 'en', 0x41646d696e6973747261746f72);
INSERT INTO `lng_data` VALUES ('common', 'adopt', 'en', 0x61646f7074);
INSERT INTO `lng_data` VALUES ('common', 'adopt_perm_from_template', 'en', 0x41646f7074207065726d697373696f6e2073657474696e67732066726f6d20526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'all_authors', 'en', 0x416c6c20617574686f7273);
INSERT INTO `lng_data` VALUES ('common', 'all_global_roles', 'en', 0x476c6f62616c20726f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'all_lms', 'en', 0x416c6c206c6561726e696e67206d6f64756c6573);
INSERT INTO `lng_data` VALUES ('common', 'all_local_roles', 'en', 0x4c6f63616c20726f6c65732028616c6c29);
INSERT INTO `lng_data` VALUES ('common', 'all_objects', 'en', 0x416c6c204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'all_roles', 'en', 0x416c6c20526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'all_tsts', 'en', 0x416c6c207465737473);
INSERT INTO `lng_data` VALUES ('common', 'all_users', 'en', 0x416c6c207573657273);
INSERT INTO `lng_data` VALUES ('common', 'allow_assign_users', 'en', 0x416c6c6f7720757365722061737369676e6d656e7420666f72206c6f63616c2061646d696e6973747261746f72732e);
INSERT INTO `lng_data` VALUES ('common', 'allow_register', 'en', 0x417661696c61626c6520696e20726567697374726174696f6e20666f726d20666f72206e6577207573657273);
INSERT INTO `lng_data` VALUES ('common', 'alm', 'en', 0x4c6561726e696e67204d6f64756c652041494343);
INSERT INTO `lng_data` VALUES ('common', 'alm_added', 'en', 0x41494343204c6561726e696e67204d6f64756c65206164646564);
INSERT INTO `lng_data` VALUES ('common', 'alm_create', 'en', 0x4372656174652041494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'alm_delete', 'en', 0x44656c6574652041494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'alm_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'alm_join', 'en', 0x53756273637269626520746f2041494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'alm_leave', 'en', 0x556e7375627363726962652066726f6d2041494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'alm_read', 'en', 0x526561642061636365737320746f2041494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'alm_visible', 'en', 0x41494343204c6561726e696e67204d6f64756c652069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'alm_write', 'en', 0x456469742041494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'and', 'en', 0x616e64);
INSERT INTO `lng_data` VALUES ('common', 'announce', 'en', 0x416e6e6f756e6365);
INSERT INTO `lng_data` VALUES ('common', 'announce_changes', 'en', 0x416e6e6f756e6365204368616e676573);
INSERT INTO `lng_data` VALUES ('common', 'answers', 'en', 0x416e7377657273);
INSERT INTO `lng_data` VALUES ('common', 'any_language', 'en', 0x416e79206c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'application_completed', 'en', 0x4170706c69636174696f6e20697320636f6d706c657465);
INSERT INTO `lng_data` VALUES ('common', 'application_date', 'en', 0x4170706c69636174696f6e2064617465);
INSERT INTO `lng_data` VALUES ('common', 'applied_users', 'en', 0x4170706c696564207573657273);
INSERT INTO `lng_data` VALUES ('common', 'apply', 'en', 0x4170706c79);
INSERT INTO `lng_data` VALUES ('common', 'apply_filter', 'en', 0x4170706c792066696c746572);
INSERT INTO `lng_data` VALUES ('common', 'appointment', 'en', 0x4170706f696e746d656e74);
INSERT INTO `lng_data` VALUES ('common', 'appointment_list', 'en', 0x4170706f696e746d656e74204c697374);
INSERT INTO `lng_data` VALUES ('common', 'approve_date', 'en', 0x417070726f766564204f6e);
INSERT INTO `lng_data` VALUES ('common', 'approve_recipient', 'en', 0x4c6f67696e204944206f6620617070726f766572);
INSERT INTO `lng_data` VALUES ('common', 'archive', 'en', 0x41726368697665);
INSERT INTO `lng_data` VALUES ('common', 'are_you_sure', 'en', 0x41726520796f7520737572653f);
INSERT INTO `lng_data` VALUES ('common', 'assign', 'en', 0x41737369676e);
INSERT INTO `lng_data` VALUES ('common', 'assign_global_role', 'en', 0x41737369676e20746f20676c6f62616c20726f6c65);
INSERT INTO `lng_data` VALUES ('common', 'assign_lo_forum', 'en', 0x41737369676e204c4f20466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'assign_local_role', 'en', 0x41737369676e20746f206c6f63616c20726f6c65);
INSERT INTO `lng_data` VALUES ('common', 'assign_user_to_role', 'en', 0x41737369676e205573657220746f20526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'assigned_roles', 'en', 0x41737369676e656420726f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'assigned_users', 'en', 0x41737369676e6564207573657273);
INSERT INTO `lng_data` VALUES ('common', 'at_location', 'en', 0x6174206c6f636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'attachment', 'en', 0x4174746163686d656e74);
INSERT INTO `lng_data` VALUES ('common', 'attachments', 'en', 0x4174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('common', 'attend', 'en', 0x417474656e64);
INSERT INTO `lng_data` VALUES ('common', 'auth_configure', 'en', 0x636f6e6669677572652e2e2e);
INSERT INTO `lng_data` VALUES ('common', 'auth_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'auth_ldap', 'en', 0x4c444150);
INSERT INTO `lng_data` VALUES ('common', 'auth_ldap_desc', 'en', 0x41757468656e74696361746520757365727320766961204c44415020736572766572);
INSERT INTO `lng_data` VALUES ('common', 'auth_local', 'en', 0x494c494153206461746162617365);
INSERT INTO `lng_data` VALUES ('common', 'auth_local_desc', 'en', 0x41757468656e74696361746520757365727320766961206c6f63616c20494c494153206461746162617365202864656661756c7429);
INSERT INTO `lng_data` VALUES ('common', 'auth_mode', 'en', 0x41757468656e7469636174696f6e206d6f6465);
INSERT INTO `lng_data` VALUES ('common', 'auth_mode_changed_to', 'en', 0x41757468656e7469636174696f6e206d6f6465206368616e67656420746f);
INSERT INTO `lng_data` VALUES ('common', 'auth_mode_not_changed', 'en', 0x284e6f7468696e67206368616e67656429);
INSERT INTO `lng_data` VALUES ('common', 'auth_radius', 'en', 0x524144495553);
INSERT INTO `lng_data` VALUES ('common', 'auth_radius_desc', 'en', 0x41757468656e746963617465207573657273207669612052414449555320736572766572);
INSERT INTO `lng_data` VALUES ('common', 'auth_read', 'en', 0x4163636573732061757468656e7469636174696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'auth_remark_non_local_auth', 'en', 0x5768656e2073656c656374696e6720616e6f746865722061757468656e7469636174696f6e206d6f6465207468616e2064656661756c74206d6f64652c2075736572206c6f67696e7320616e642070617373776f7264732063616e6e6f74206265206368616e67656420616e796d6f726520616e6420746865206f7074696f6e20746f207265676973746572206e65772075736572732069732064697361626c65642e);
INSERT INTO `lng_data` VALUES ('common', 'auth_script', 'en', 0x437573746f6d);
INSERT INTO `lng_data` VALUES ('common', 'auth_script_desc', 'en', 0x41757468656e746963617465207573657273207669612065787465726e616c20736372697074);
INSERT INTO `lng_data` VALUES ('common', 'auth_select', 'en', 0x53656c6563742061757468656e7469636174696f6e206d6f6465);
INSERT INTO `lng_data` VALUES ('common', 'auth_visible', 'en', 0x41757468656e7469636174696f6e2073657474696e6773206172652076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'auth_write', 'en', 0x456469742061757468656e7469636174696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'author', 'en', 0x417574686f72);
INSERT INTO `lng_data` VALUES ('common', 'authors', 'en', 0x417574686f7273);
INSERT INTO `lng_data` VALUES ('common', 'auto_registration', 'en', 0x4175746f6d61746963616c6c7920617070726f766520726567697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'available_languages', 'en', 0x417661696c61626c65204c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'average_time', 'en', 0x417665726167652074696d65);
INSERT INTO `lng_data` VALUES ('common', 'back', 'en', 0x4261636b);
INSERT INTO `lng_data` VALUES ('common', 'basedn', 'en', 0x42617365444e);
INSERT INTO `lng_data` VALUES ('common', 'basic_data', 'en', 0x42617369632044617461);
INSERT INTO `lng_data` VALUES ('common', 'benchmark', 'en', 0x42656e63686d61726b);
INSERT INTO `lng_data` VALUES ('common', 'benchmark_settings', 'en', 0x42656e63686d61726b2053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'benchmarks', 'en', 0x42656e63686d61726b73);
INSERT INTO `lng_data` VALUES ('common', 'bib_data', 'en', 0x4269626c696f67726170686963616c2044617461);
INSERT INTO `lng_data` VALUES ('common', 'bm', 'en', 0x426f6f6b6d61726b);
INSERT INTO `lng_data` VALUES ('common', 'bmf', 'en', 0x426f6f6b6d61726b20466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_edit', 'en', 0x4564697420426f6f6b6d61726b);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_folder_edit', 'en', 0x4564697420426f6f6b6d61726b20466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_folder_new', 'en', 0x4e657720426f6f6b6d61726b20466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_new', 'en', 0x4e657720426f6f6b6d61726b);
INSERT INTO `lng_data` VALUES ('common', 'bookmark_target', 'en', 0x546172676574);
INSERT INTO `lng_data` VALUES ('common', 'bookmarks', 'en', 0x426f6f6b6d61726b73);
INSERT INTO `lng_data` VALUES ('common', 'bookmarks_of', 'en', 0x426f6f6b6d61726b73206f66);
INSERT INTO `lng_data` VALUES ('common', 'btn_remove_system', 'en', 0x52656d6f76652066726f6d2053797374656d);
INSERT INTO `lng_data` VALUES ('common', 'btn_undelete', 'en', 0x556e64656c657465);
INSERT INTO `lng_data` VALUES ('common', 'by', 'en', 0x4279);
INSERT INTO `lng_data` VALUES ('common', 'calendar', 'en', 0x43616c656e646172);
INSERT INTO `lng_data` VALUES ('common', 'cancel', 'en', 0x43616e63656c);
INSERT INTO `lng_data` VALUES ('common', 'cannot_find_xml', 'en', 0x43616e6e6f742066696e6420616e7920584d4c2d66696c652e);
INSERT INTO `lng_data` VALUES ('common', 'cannot_uninstall_language_in_use', 'en', 0x596f752063616e6e6f7420756e696e7374616c6c20746865206c616e67756167652063757272656e746c7920696e2075736521);
INSERT INTO `lng_data` VALUES ('common', 'cannot_uninstall_systemlanguage', 'en', 0x596f752063616e6e6f7420756e696e7374616c6c207468652073797374656d206c616e677561676521);
INSERT INTO `lng_data` VALUES ('common', 'cannot_unzip_file', 'en', 0x43616e6e6f7420756e7061636b2066696c652e);
INSERT INTO `lng_data` VALUES ('common', 'cat', 'en', 0x43617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_a', 'en', 0x612043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_add', 'en', 0x4164642043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_added', 'en', 0x43617465676f7279206164646564);
INSERT INTO `lng_data` VALUES ('common', 'cat_cat_administrate_users', 'en', 0x41646d696e69737472617465206c6f63616c2075736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'cat_create', 'en', 0x4372656174652043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_alm', 'en', 0x4372656174652041494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_cat', 'en', 0x4372656174652043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_chat', 'en', 0x4372656174652043686174);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_crs', 'en', 0x43726561746520436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_dbk', 'en', 0x43726561746520446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_exc', 'en', 0x437265617465204578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_file', 'en', 0x55706c6f61642046696c65);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_frm', 'en', 0x43726561746520466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_glo', 'en', 0x43726561746520476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_grp', 'en', 0x4372656174652047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_hlm', 'en', 0x4372656174652048414350204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_htlm', 'en', 0x4372656174652048544d4c204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_lm', 'en', 0x43726561746520494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_mep', 'en', 0x437265617465204d6564696120506f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_qpl', 'en', 0x437265617465205175657374696f6e20506f6f6c20666f722054657374);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_sahs', 'en', 0x4372656174652053434f524d2f41494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_spl', 'en', 0x437265617465205175657374696f6e20506f6f6c20666f7220537572766579);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_svy', 'en', 0x43726561746520537572766579);
INSERT INTO `lng_data` VALUES ('common', 'cat_create_tst', 'en', 0x4372656174652054657374);
INSERT INTO `lng_data` VALUES ('common', 'cat_delete', 'en', 0x44656c6574652043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_edit', 'en', 0x456469742043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'cat_new', 'en', 0x4e65772043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_read', 'en', 0x526561642061636365737320746f2043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'cat_read_users', 'en', 0x526561642061636365737320746f206c6f63616c2075736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'cat_visible', 'en', 0x43617465676f72792069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'cat_write', 'en', 0x456469742043617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'categories', 'en', 0x43617465676f72696573);
INSERT INTO `lng_data` VALUES ('common', 'categories_imported', 'en', 0x43617465676f727920696d706f727420636f6d706c6574652e);
INSERT INTO `lng_data` VALUES ('common', 'cc', 'en', 0x4343);
INSERT INTO `lng_data` VALUES ('common', 'cen', 'en', 0x43656e747261204576656e74);
INSERT INTO `lng_data` VALUES ('common', 'cen_add', 'en', 0x4164642043656e747261204576656e74);
INSERT INTO `lng_data` VALUES ('common', 'cen_added', 'en', 0x43656e747261204576656e74206164646564);
INSERT INTO `lng_data` VALUES ('common', 'cen_delete', 'en', 0x44656c6574652043656e747261204576656e74);
INSERT INTO `lng_data` VALUES ('common', 'cen_edit', 'en', 0x456469742043656e747261204576656e74);
INSERT INTO `lng_data` VALUES ('common', 'cen_new', 'en', 0x4e65772043656e747261204576656e74);
INSERT INTO `lng_data` VALUES ('common', 'cen_save', 'en', 0x536176652043656e747261204576656e74);
INSERT INTO `lng_data` VALUES ('common', 'censorship', 'en', 0x43656e736f7273686970);
INSERT INTO `lng_data` VALUES ('common', 'centra_account', 'en', 0x43656e7472612041646d696e204163636f756e74);
INSERT INTO `lng_data` VALUES ('common', 'centra_rooms', 'en', 0x43656e74726120526f6f6d73);
INSERT INTO `lng_data` VALUES ('common', 'centra_server', 'en', 0x43656e74726120536572766572);
INSERT INTO `lng_data` VALUES ('common', 'chac_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'chac_read', 'en', 0x526561642061636365737320746f20636861742073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'chac_visible', 'en', 0x436861742073657474696e6773206172652076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'chac_write', 'en', 0x4564697420636861742073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'change', 'en', 0x4368616e6765);
INSERT INTO `lng_data` VALUES ('common', 'change_active_assignment', 'en', 0x4368616e6765206163746976652061737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'change_assignment', 'en', 0x4368616e67652061737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'change_existing_objects', 'en', 0x4368616e6765206578697374696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'change_header_title', 'en', 0x4564697420486561646572205469746c65);
INSERT INTO `lng_data` VALUES ('common', 'change_lo_info', 'en', 0x4368616e6765204c4f20496e666f);
INSERT INTO `lng_data` VALUES ('common', 'change_metadata', 'en', 0x4368616e6765204d65746164617461);
INSERT INTO `lng_data` VALUES ('common', 'change_sort_direction', 'en', 0x4368616e676520736f727420646972656374696f6e);
INSERT INTO `lng_data` VALUES ('common', 'changed_to', 'en', 0x6368616e67656420746f);
INSERT INTO `lng_data` VALUES ('common', 'chapter', 'en', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('common', 'chapter_number', 'en', 0x43686170746572204e756d626572);
INSERT INTO `lng_data` VALUES ('common', 'chapter_title', 'en', 0x43686170746572205469746c65);
INSERT INTO `lng_data` VALUES ('common', 'chat', 'en', 0x43686174);
INSERT INTO `lng_data` VALUES ('common', 'chat_add', 'en', 0x4164642043686174);
INSERT INTO `lng_data` VALUES ('common', 'chat_delete', 'en', 0x44656c6574652063686174);
INSERT INTO `lng_data` VALUES ('common', 'chat_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'chat_new', 'en', 0x4e65772043686174);
INSERT INTO `lng_data` VALUES ('common', 'chat_read', 'en', 0x526561642f57726974652061636365737320746f2063686174);
INSERT INTO `lng_data` VALUES ('common', 'chat_visible', 'en', 0x436861742069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'chat_write', 'en', 0x456469742063686174);
INSERT INTO `lng_data` VALUES ('common', 'check', 'en', 0x436865636b);
INSERT INTO `lng_data` VALUES ('common', 'check_all', 'en', 0x436865636b20616c6c);
INSERT INTO `lng_data` VALUES ('common', 'check_langfile', 'en', 0x506c6561736520636865636b20796f7572206c616e67756167652066696c65);
INSERT INTO `lng_data` VALUES ('common', 'check_languages', 'en', 0x436865636b204c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'check_max_allowed_packet_size', 'en', 0x546865207061676520636f6e74656e742073697a6520697320746f6f206c617267652e);
INSERT INTO `lng_data` VALUES ('common', 'checked_files', 'en', 0x496d706f727461626c652066696c6573);
INSERT INTO `lng_data` VALUES ('common', 'chg_language', 'en', 0x4368616e6765204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'chg_password', 'en', 0x4368616e67652050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'choose_language', 'en', 0x43686f6f736520596f7572204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'choose_location', 'en', 0x43686f6f7365204c6f636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'choose_only_one_language', 'en', 0x506c656173652063686f6f7365206f6e6c79206f6e65206c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'city', 'en', 0x436974792c205374617465);
INSERT INTO `lng_data` VALUES ('common', 'clear', 'en', 0x436c656172);
INSERT INTO `lng_data` VALUES ('common', 'client ip', 'en', 0x436c69656e74204950);
INSERT INTO `lng_data` VALUES ('common', 'client_id', 'en', 0x436c69656e74204944);
INSERT INTO `lng_data` VALUES ('common', 'client_ip', 'en', 0x436c69656e74204950);
INSERT INTO `lng_data` VALUES ('common', 'clipboard', 'en', 0x436c6970626f617264);
INSERT INTO `lng_data` VALUES ('common', 'close', 'en', 0x436c6f7365);
INSERT INTO `lng_data` VALUES ('common', 'comma_separated', 'en', 0x436f6d6d6120536570617261746564);
INSERT INTO `lng_data` VALUES ('common', 'comment', 'en', 0x636f6d6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'compose', 'en', 0x436f6d706f7365);
INSERT INTO `lng_data` VALUES ('common', 'condition', 'en', 0x436f6e646974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'condition_already_assigned', 'en', 0x4f626a65637420616c72656164792061737369676e65642e);
INSERT INTO `lng_data` VALUES ('common', 'condition_circle_created', 'en', 0x54686973206173736f63696174696f6e206973206e6f7420706f737369626c652c2073696e636520746865206f626a6563747320776f756c6420696e746572646570656e642e);
INSERT INTO `lng_data` VALUES ('common', 'condition_deleted', 'en', 0x436f6e646974696f6e2064656c657465642e);
INSERT INTO `lng_data` VALUES ('common', 'condition_passed', 'en', 0x506173736564);
INSERT INTO `lng_data` VALUES ('common', 'condition_precondition', 'en', 0x507265636f6e646974696f6e733a);
INSERT INTO `lng_data` VALUES ('common', 'condition_select_object', 'en', 0x506c656173652073656c656374206f6e65206f626a6563742e);
INSERT INTO `lng_data` VALUES ('common', 'condition_select_one', 'en', 0x2d506c656173652073656c656374206f6e6520636f6e646974696f6e2d);
INSERT INTO `lng_data` VALUES ('common', 'condition_update', 'en', 0x53617665);
INSERT INTO `lng_data` VALUES ('common', 'conditions_updated', 'en', 0x436f6e646974696f6e73207361766564);
INSERT INTO `lng_data` VALUES ('common', 'confirm', 'en', 0x436f6e6669726d);
INSERT INTO `lng_data` VALUES ('common', 'confirmation', 'en', 0x436f6e6669726d6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'cont_object', 'en', 0x4f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'contact_data', 'en', 0x436f6e7461637420496e666f726d6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'container', 'en', 0x436f6e7461696e6572);
INSERT INTO `lng_data` VALUES ('common', 'context', 'en', 0x436f6e74657874);
INSERT INTO `lng_data` VALUES ('common', 'continue_work', 'en', 0x436f6e74696e7565);
INSERT INTO `lng_data` VALUES ('common', 'copied_object', 'en', 0x436f70696564206f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'copy', 'en', 0x436f7079);
INSERT INTO `lng_data` VALUES ('common', 'copyPage', 'en', 0x436f7079);
INSERT INTO `lng_data` VALUES ('common', 'copy_of', 'en', 0x436f7079206f66);
INSERT INTO `lng_data` VALUES ('common', 'copy_to', 'en', 0x746f);
INSERT INTO `lng_data` VALUES ('common', 'count', 'en', 0x436f756e74);
INSERT INTO `lng_data` VALUES ('common', 'country', 'en', 0x436f756e747279);
INSERT INTO `lng_data` VALUES ('common', 'course', 'en', 0x436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'courses', 'en', 0x436f7572736573);
INSERT INTO `lng_data` VALUES ('common', 'create', 'en', 0x437265617465);
INSERT INTO `lng_data` VALUES ('common', 'create_date', 'en', 0x43726561746564204f6e);
INSERT INTO `lng_data` VALUES ('common', 'create_in', 'en', 0x43726561746520696e);
INSERT INTO `lng_data` VALUES ('common', 'create_stylesheet', 'en', 0x437265617465205374796c65);
INSERT INTO `lng_data` VALUES ('common', 'crs', 'en', 0x436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_a', 'en', 0x6120436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_add', 'en', 0x41646420436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_added', 'en', 0x436f75727365206164646564);
INSERT INTO `lng_data` VALUES ('common', 'crs_archives', 'en', 0x4172636869766573);
INSERT INTO `lng_data` VALUES ('common', 'crs_available', 'en', 0x417661696c61626c6520436f7572736573);
INSERT INTO `lng_data` VALUES ('common', 'crs_container_link_not_allowed', 'en', 0x4974206973206e6f7420706f737369626c6520746f206c696e6b20636f6e7461696e6572206f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'crs_create', 'en', 0x43726561746520436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_alm', 'en', 0x4372656174652041494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_chat', 'en', 0x4372656174652043686174);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_dbk', 'en', 0x43726561746520446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_exc', 'en', 0x437265617465204578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_file', 'en', 0x55706c6f61642046696c65);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_fold', 'en', 0x43726561746520466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_frm', 'en', 0x43726561746520466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_glo', 'en', 0x43726561746520476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_grp', 'en', 0x4372656174652047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_hlm', 'en', 0x4372656174652048414350204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_htlm', 'en', 0x4372656174652048544d4c204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_lm', 'en', 0x43726561746520494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_mep', 'en', 0x437265617465204d6564696120506f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_qpl', 'en', 0x437265617465205175657374696f6e20506f6f6c20666f722054657374);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_sahs', 'en', 0x4372656174652053434f524d2f41494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_spl', 'en', 0x437265617465205175657374696f6e20506f6f6c20666f7220537572766579);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_svy', 'en', 0x43726561746520537572766579);
INSERT INTO `lng_data` VALUES ('common', 'crs_create_tst', 'en', 0x4372656174652054657374);
INSERT INTO `lng_data` VALUES ('common', 'crs_delete', 'en', 0x44656c65746520436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_edit', 'en', 0x4564697420436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'crs_join', 'en', 0x53756273637269626520746f20436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_leave', 'en', 0x556e7375627363726962652066726f6d20436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_management_system', 'en', 0x436f75727365204d616e6167656d656e742053797374656d);
INSERT INTO `lng_data` VALUES ('common', 'crs_member_not_passed', 'en', 0x4e6f7420706173736564);
INSERT INTO `lng_data` VALUES ('common', 'crs_member_passed', 'en', 0x506173736564);
INSERT INTO `lng_data` VALUES ('common', 'crs_new', 'en', 0x4e657720436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_no_content', 'en', 0x576974686f757420636f6e74656e74);
INSERT INTO `lng_data` VALUES ('common', 'crs_read', 'en', 0x526561642061636365737320746f20436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'crs_status_blocked', 'en', 0x5b41636365737320726566757365645d);
INSERT INTO `lng_data` VALUES ('common', 'crs_status_pending', 'en', 0x5b57616974696e6720666f7220726567697374726174696f6e5d);
INSERT INTO `lng_data` VALUES ('common', 'crs_subscribers_assigned', 'en', 0x41737369676e6564206e65772075736572287329);
INSERT INTO `lng_data` VALUES ('common', 'crs_visible', 'en', 0x436f757273652069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'crs_write', 'en', 0x4564697420436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'cumulative_time', 'en', 0x43756d756c61746976652074696d65);
INSERT INTO `lng_data` VALUES ('common', 'cur_number_rec', 'en', 0x43757272656e74206e756d626572206f66207265636f726473);
INSERT INTO `lng_data` VALUES ('common', 'current_password', 'en', 0x43757272656e742050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'cut', 'en', 0x437574);
INSERT INTO `lng_data` VALUES ('common', 'cutPage', 'en', 0x437574);
INSERT INTO `lng_data` VALUES ('common', 'database', 'en', 0x4461746162617365);
INSERT INTO `lng_data` VALUES ('common', 'database_version', 'en', 0x43757272656e742044617461626173652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'dataset', 'en', 0x4974656d);
INSERT INTO `lng_data` VALUES ('common', 'date', 'en', 0x44617465);
INSERT INTO `lng_data` VALUES ('common', 'dateplaner', 'en', 0x43616c656e646172);
INSERT INTO `lng_data` VALUES ('common', 'day', 'en', 0x446179);
INSERT INTO `lng_data` VALUES ('common', 'days', 'en', 0x44617973);
INSERT INTO `lng_data` VALUES ('common', 'days_of_period', 'en', 0x44617973206f6620706572696f64);
INSERT INTO `lng_data` VALUES ('common', 'db_host', 'en', 0x446174616261736520486f7374);
INSERT INTO `lng_data` VALUES ('common', 'db_name', 'en', 0x4461746162617365204e616d65);
INSERT INTO `lng_data` VALUES ('common', 'db_need_update', 'en', 0x4461746162617365206e6565647320616e2075706461746521);
INSERT INTO `lng_data` VALUES ('common', 'db_pass', 'en', 0x44617461626173652050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'db_type', 'en', 0x44617461626173652054797065);
INSERT INTO `lng_data` VALUES ('common', 'db_user', 'en', 0x44617461626173652055736572);
INSERT INTO `lng_data` VALUES ('common', 'db_version', 'en', 0x44617461626173652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'dbk', 'en', 0x446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'dbk_a', 'en', 0x6120446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'dbk_add', 'en', 0x41646420446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'dbk_added', 'en', 0x446967696c696220426f6f6b206164646564);
INSERT INTO `lng_data` VALUES ('common', 'dbk_create', 'en', 0x43726561746520446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'dbk_delete', 'en', 0x44656c65746520446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'dbk_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'dbk_new', 'en', 0x4e657720446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'dbk_read', 'en', 0x526561642061636365737320746f20446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'dbk_visible', 'en', 0x446967696c696220426f6f6b2069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'dbk_write', 'en', 0x4564697420446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'default', 'en', 0x44656661756c74);
INSERT INTO `lng_data` VALUES ('common', 'default_language', 'en', 0x44656661756c74204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'default_perm_settings', 'en', 0x44656661756c74207065726d697373696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'default_role', 'en', 0x44656661756c7420526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'default_roles', 'en', 0x44656661756c7420526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'default_skin', 'en', 0x44656661756c7420536b696e);
INSERT INTO `lng_data` VALUES ('common', 'default_skin_style', 'en', 0x44656661756c7420536b696e202f205374796c65);
INSERT INTO `lng_data` VALUES ('common', 'default_style', 'en', 0x44656661756c74205374796c65);
INSERT INTO `lng_data` VALUES ('common', 'delete', 'en', 0x44656c657465);
INSERT INTO `lng_data` VALUES ('common', 'delete_all', 'en', 0x44656c65746520416c6c);
INSERT INTO `lng_data` VALUES ('common', 'delete_all_rec', 'en', 0x44656c65746520616c6c207265636f726473);
INSERT INTO `lng_data` VALUES ('common', 'delete_object', 'en', 0x44656c657465204f626a656374287329);
INSERT INTO `lng_data` VALUES ('common', 'delete_selected', 'en', 0x44656c6574652053656c6563746564);
INSERT INTO `lng_data` VALUES ('common', 'delete_tr_data', 'en', 0x44656c65746520747261636b696e672064617461);
INSERT INTO `lng_data` VALUES ('common', 'deleted', 'en', 0x44656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'deleted_user', 'en', 0x546865207573657220686173206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'department', 'en', 0x4465706172746d656e74);
INSERT INTO `lng_data` VALUES ('common', 'desc', 'en', 0x4465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'description', 'en', 0x4465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'desired_password', 'en', 0x446573697265642050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'disabled', 'en', 0x44697361626c6564);
INSERT INTO `lng_data` VALUES ('common', 'domain', 'en', 0x446f6d61696e);
INSERT INTO `lng_data` VALUES ('common', 'down', 'en', 0x446f776e);
INSERT INTO `lng_data` VALUES ('common', 'download', 'en', 0x446f776e6c6f6164);
INSERT INTO `lng_data` VALUES ('common', 'drafts', 'en', 0x447261667473);
INSERT INTO `lng_data` VALUES ('common', 'drop', 'en', 0x44726f70);
INSERT INTO `lng_data` VALUES ('common', 'edit', 'en', 0x45646974);
INSERT INTO `lng_data` VALUES ('common', 'edit_data', 'en', 0x656469742064617461);
INSERT INTO `lng_data` VALUES ('common', 'edit_operations', 'en', 0x45646974204f7065726174696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'edit_perm_ruleset', 'en', 0x456469742064656661756c74207065726d697373696f6e2072756c6573);
INSERT INTO `lng_data` VALUES ('common', 'edit_properties', 'en', 0x456469742050726f70657274696573);
INSERT INTO `lng_data` VALUES ('common', 'edit_roleassignment', 'en', 0x4564697420726f6c652061737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'edit_stylesheet', 'en', 0x45646974205374796c65);
INSERT INTO `lng_data` VALUES ('common', 'edited_at', 'en', 0x456469746564206174);
INSERT INTO `lng_data` VALUES ('common', 'editor', 'en', 0x456469746f72);
INSERT INTO `lng_data` VALUES ('common', 'email', 'en', 0x452d6d61696c);
INSERT INTO `lng_data` VALUES ('common', 'email_not_valid', 'en', 0x54686520656d61696c206164647265737320796f7520656e7465726564206973206e6f742076616c696421);
INSERT INTO `lng_data` VALUES ('common', 'enable', 'en', 0x456e61626c65);
INSERT INTO `lng_data` VALUES ('common', 'enable_registration', 'en', 0x456e61626c65206e6577207573657220726567697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'enabled', 'en', 0x456e61626c6564);
INSERT INTO `lng_data` VALUES ('common', 'enumerate', 'en', 0x456e756d6572617465);
INSERT INTO `lng_data` VALUES ('common', 'err_1_param', 'en', 0x4f6e6c79203120706172616d6574657221);
INSERT INTO `lng_data` VALUES ('common', 'err_2_param', 'en', 0x4f6e6c79203220706172616d6574657221);
INSERT INTO `lng_data` VALUES ('common', 'err_count_param', 'en', 0x526561736f6e3a2057726f6e6720706172616d6574657220636f756e74);
INSERT INTO `lng_data` VALUES ('common', 'err_enter_current_passwd', 'en', 0x506c6561736520656e74657220796f75722063757272656e742070617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'err_in_line', 'en', 0x4572726f7220696e206c696e65);
INSERT INTO `lng_data` VALUES ('common', 'err_inactive', 'en', 0x54686973206163636f756e7420686173206e6f74206265656e206163746976617465642e20506c6561736520636f6e74616374207468652073797374656d2061646d696e6973747261746f7220666f72206163636573732e);
INSERT INTO `lng_data` VALUES ('common', 'err_invalid_port', 'en', 0x496e76616c696420706f7274206e756d626572);
INSERT INTO `lng_data` VALUES ('common', 'err_ldap_auth_failed', 'en', 0x55736572206e6f742061757468656e74696361746564206f6e204c444150207365727665722120506c6561736520656e73757265207468617420796f752068617665207468652073616d652070617373776f726420696e20494c49415320616e64204c444150);
INSERT INTO `lng_data` VALUES ('common', 'err_ldap_connect_failed', 'en', 0x436f6e6e656374696f6e20746f204c44415020736572766572206661696c65642120506c6561736520636865636b20796f75722073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'err_ldap_search_failed', 'en', 0x436f6e6e656374696f6e20746f204c44415020736572766572206661696c65642120504c6561736520636865636b2042617365444e20616e64205365617263682062617365);
INSERT INTO `lng_data` VALUES ('common', 'err_ldap_user_not_found', 'en', 0x55736572206e6f7420666f756e64206f6e204c444150207365727665722120506c6561736520636865636b2061747472696275746520666f72206c6f67696e206e616d6520616e64204f626a656374636c617373);
INSERT INTO `lng_data` VALUES ('common', 'err_no_langfile_found', 'en', 0x4e6f206c616e67756167652066696c6520666f756e6421);
INSERT INTO `lng_data` VALUES ('common', 'err_no_param', 'en', 0x4e6f20706172616d6574657221);
INSERT INTO `lng_data` VALUES ('common', 'err_over_3_param', 'en', 0x4d6f7265207468616e203320706172616d657465727321);
INSERT INTO `lng_data` VALUES ('common', 'err_role_not_assignable', 'en', 0x596f752063616e6e6f742061737369676e20757365727320746f207468697320726f6c652061742074686973206c6f636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'err_session_expired', 'en', 0x596f75722073657373696f6e206973206578706972656421);
INSERT INTO `lng_data` VALUES ('common', 'err_unknown_error', 'en', 0x556e6b6e6f776e204572726f72);
INSERT INTO `lng_data` VALUES ('common', 'err_wrong_header', 'en', 0x526561736f6e3a2057726f6e67206865616465722e);
INSERT INTO `lng_data` VALUES ('common', 'err_wrong_login', 'en', 0x57726f6e67204c6f67696e);
INSERT INTO `lng_data` VALUES ('common', 'err_wrong_password', 'en', 0x57726f6e672050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'error_parser', 'en', 0x4572726f72207374617274696e6720746865207061727365722e);
INSERT INTO `lng_data` VALUES ('common', 'error_recipient', 'en', 0x4572726f7220526563697069656e74);
INSERT INTO `lng_data` VALUES ('common', 'exc', 'en', 0x4578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'exc_add', 'en', 0x416464204578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'exc_added', 'en', 0x4578657263697365206164646564);
INSERT INTO `lng_data` VALUES ('common', 'exc_ask_delete', 'en', 0x44656c6574652066696c653f);
INSERT INTO `lng_data` VALUES ('common', 'exc_assign_usr', 'en', 0x41737369676e2055736572);
INSERT INTO `lng_data` VALUES ('common', 'exc_create', 'en', 0x437265617465206578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'exc_date_not_valid', 'en', 0x5468652064617465206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('common', 'exc_deassign_members', 'en', 0x44656c657465206d656d626572287329);
INSERT INTO `lng_data` VALUES ('common', 'exc_delete', 'en', 0x44656c657465206578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'exc_edit', 'en', 0x4e65772065786572636973652063726561746564);
INSERT INTO `lng_data` VALUES ('common', 'exc_edit_exercise', 'en', 0x45646974206578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'exc_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'exc_edit_until', 'en', 0x4564697420756e74696c);
INSERT INTO `lng_data` VALUES ('common', 'exc_files', 'en', 0x46696c6573);
INSERT INTO `lng_data` VALUES ('common', 'exc_groups', 'en', 0x47726f757073);
INSERT INTO `lng_data` VALUES ('common', 'exc_header_members', 'en', 0x457865726369736520286d656d6265727329);
INSERT INTO `lng_data` VALUES ('common', 'exc_instruction', 'en', 0x576f726b20696e737472756374696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'exc_members_already_assigned', 'en', 0x546865736520757365722061726520616c72656164792061737369676e656420746f2074686973206578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'exc_members_assigned', 'en', 0x4d656d626572732061737369676e6564);
INSERT INTO `lng_data` VALUES ('common', 'exc_new', 'en', 0x4e6577206578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'exc_no_members_assigned', 'en', 0x4e6f206d656d626572732061737369676e6564);
INSERT INTO `lng_data` VALUES ('common', 'exc_notices', 'en', 0x4e6f7469636573);
INSERT INTO `lng_data` VALUES ('common', 'exc_obj', 'en', 0x4578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'exc_read', 'en', 0x416363657373206578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'exc_roles', 'en', 0x526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'exc_save_changes', 'en', 0x53617665);
INSERT INTO `lng_data` VALUES ('common', 'exc_search_for', 'en', 0x53656172636820666f72);
INSERT INTO `lng_data` VALUES ('common', 'exc_select_one_file', 'en', 0x506c656173652073656c6563742065786163746c79206f6e652066696c652e);
INSERT INTO `lng_data` VALUES ('common', 'exc_send_exercise', 'en', 0x53656e64206578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'exc_sent', 'en', 0x54686520657865726369736520686173206265656e2073656e7420746f207468652073656c6563746564207573657273);
INSERT INTO `lng_data` VALUES ('common', 'exc_status_returned', 'en', 0x52657475726e6564);
INSERT INTO `lng_data` VALUES ('common', 'exc_status_saved', 'en', 0x45786572636973652075706461746564);
INSERT INTO `lng_data` VALUES ('common', 'exc_status_solved', 'en', 0x536f6c766564);
INSERT INTO `lng_data` VALUES ('common', 'exc_upload_error', 'en', 0x4572726f722075706c6f6164696e672066696c65);
INSERT INTO `lng_data` VALUES ('common', 'exc_users', 'en', 0x5573657273);
INSERT INTO `lng_data` VALUES ('common', 'exc_visible', 'en', 0x45786572636973652069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'exc_write', 'en', 0x45646974206578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'excs', 'en', 0x457865726369736573);
INSERT INTO `lng_data` VALUES ('common', 'execute', 'en', 0x45786563757465);
INSERT INTO `lng_data` VALUES ('common', 'export', 'en', 0x4578706f7274);
INSERT INTO `lng_data` VALUES ('common', 'export_group_members', 'en', 0x4578706f72742067726f7570206d656d626572732028746f204d6963726f736f667420457863656c29);
INSERT INTO `lng_data` VALUES ('common', 'export_html', 'en', 0x6578706f72742061732048544d4c2066696c65);
INSERT INTO `lng_data` VALUES ('common', 'export_xml', 'en', 0x6578706f727420617320584d4c2066696c65);
INSERT INTO `lng_data` VALUES ('common', 'faq_exercise', 'en', 0x464151204578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'fax', 'en', 0x466178);
INSERT INTO `lng_data` VALUES ('common', 'feedback', 'en', 0x466565646261636b);
INSERT INTO `lng_data` VALUES ('common', 'feedback_recipient', 'en', 0x466565646261636b20526563697069656e74);
INSERT INTO `lng_data` VALUES ('common', 'file', 'en', 0x46696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_a', 'en', 0x612046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_add', 'en', 0x55706c6f61642046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_added', 'en', 0x46696c652075706c6f61646564);
INSERT INTO `lng_data` VALUES ('common', 'file_delete', 'en', 0x44656c6574652046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_edit', 'en', 0x456469742046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_edit_permission', 'en', 0x4368616e6765205065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'file_new', 'en', 0x4e65772046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_not_found', 'en', 0x46696c65204e6f7420466f756e64);
INSERT INTO `lng_data` VALUES ('common', 'file_not_valid', 'en', 0x46696c65206e6f742076616c696421);
INSERT INTO `lng_data` VALUES ('common', 'file_notice', 'en', 0x506c656173652074616b65206e6f7465206f6620746865206d6178696d756d2066696c652073697a65206f66);
INSERT INTO `lng_data` VALUES ('common', 'file_read', 'en', 0x446f776e6c6f61642046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_valid', 'en', 0x46696c652069732076616c696421);
INSERT INTO `lng_data` VALUES ('common', 'file_version', 'en', 0x56657273696f6e2050726f766964656420696e2046696c65);
INSERT INTO `lng_data` VALUES ('common', 'file_visible', 'en', 0x46696c652069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'file_write', 'en', 0x456469742046696c65);
INSERT INTO `lng_data` VALUES ('common', 'files', 'en', 0x46696c6573);
INSERT INTO `lng_data` VALUES ('common', 'files_location', 'en', 0x46696c6573204c6f636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'fill_out_all_required_fields', 'en', 0x506c656173652066696c6c206f757420616c6c207265717569726564206669656c6473);
INSERT INTO `lng_data` VALUES ('common', 'filter', 'en', 0x46696c7465723a);
INSERT INTO `lng_data` VALUES ('common', 'firstname', 'en', 0x4669727374206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'flatview', 'en', 0x466c61742056696577);
INSERT INTO `lng_data` VALUES ('common', 'fold', 'en', 0x466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold_a', 'en', 0x6120466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold_add', 'en', 0x41646420466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold_added', 'en', 0x466f6c646572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_alm', 'en', 0x4372656174652041494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_chat', 'en', 0x4372656174652043686174);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_dbk', 'en', 0x43726561746520446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_exc', 'en', 0x437265617465204578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_file', 'en', 0x55706c6f61642046696c65);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_fold', 'en', 0x43726561746520466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_frm', 'en', 0x43726561746520466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_glo', 'en', 0x43726561746520476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_hlm', 'en', 0x4372656174652048414350204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_htlm', 'en', 0x4372656174652048544d4c204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_lm', 'en', 0x43726561746520494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_mep', 'en', 0x437265617465204d6564696120506f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_qpl', 'en', 0x437265617465205175657374696f6e20506f6f6c20666f722054657374);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_sahs', 'en', 0x4372656174652053434f524d2f41494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_spl', 'en', 0x437265617465205175657374696f6e20506f6f6c20666f7220537572766579);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_svy', 'en', 0x43726561746520537572766579);
INSERT INTO `lng_data` VALUES ('common', 'fold_create_tst', 'en', 0x4372656174652054657374);
INSERT INTO `lng_data` VALUES ('common', 'fold_delete', 'en', 0x44656c65746520466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold_edit', 'en', 0x4564697420466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold_edit_permission', 'en', 0x4368616e6765205065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'fold_new', 'en', 0x4e657720466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold_read', 'en', 0x526561642061636365737320746f20466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'fold_visible', 'en', 0x466f6c6465722069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'fold_write', 'en', 0x4564697420466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'folder', 'en', 0x466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'folders', 'en', 0x466f6c64657273);
INSERT INTO `lng_data` VALUES ('common', 'force_accept_usr_agreement', 'en', 0x596f75206d757374206163636570742074686520757365722061677265656d656e7421);
INSERT INTO `lng_data` VALUES ('common', 'forename', 'en', 0x466f72656e616d65);
INSERT INTO `lng_data` VALUES ('common', 'forgot_password', 'en', 0x466f72676f742070617373776f72643f);
INSERT INTO `lng_data` VALUES ('common', 'form_empty_fields', 'en', 0x506c6561736520636f6d706c657465207468657365206669656c64733a);
INSERT INTO `lng_data` VALUES ('common', 'forum', 'en', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'forum_import', 'en', 0x466f72756d20696d706f7274);
INSERT INTO `lng_data` VALUES ('common', 'forum_import_file', 'en', 0x496d706f72742066696c65);
INSERT INTO `lng_data` VALUES ('common', 'forum_notification', 'en', 0x4e6f74696669636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'forum_notify_me', 'en', 0x4e6f74696679206d65207768656e2061207265706c7920697320706f73746564);
INSERT INTO `lng_data` VALUES ('common', 'forum_post_replied', 'en', 0x596f757220666f72756d20656e74727920686173206265656e207265706c6965642e);
INSERT INTO `lng_data` VALUES ('common', 'forums', 'en', 0x466f72756d73);
INSERT INTO `lng_data` VALUES ('common', 'forums_overview', 'en', 0x466f72756d73204f76657276696577);
INSERT INTO `lng_data` VALUES ('common', 'frm', 'en', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_a', 'en', 0x6120466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_add', 'en', 0x41646420466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_added', 'en', 0x466f72756d206164646564);
INSERT INTO `lng_data` VALUES ('common', 'frm_create', 'en', 0x43726561746520466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_delete', 'en', 0x44656c65746520466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_delete_post', 'en', 0x44656c657465206120706f73742c2063656e736f7273686970);
INSERT INTO `lng_data` VALUES ('common', 'frm_edit', 'en', 0x4564697420466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'frm_edit_post', 'en', 0x416464206e6577207468726561642c20706f7374696e67);
INSERT INTO `lng_data` VALUES ('common', 'frm_new', 'en', 0x4e657720466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_read', 'en', 0x526561642061636365737320746f20466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'frm_visible', 'en', 0x466f72756d2069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'frm_write', 'en', 0x4564697420666f72756d);
INSERT INTO `lng_data` VALUES ('common', 'from', 'en', 0x46726f6d);
INSERT INTO `lng_data` VALUES ('common', 'fullname', 'en', 0x46756c6c206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'functions', 'en', 0x46756e6374696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'gdf_add', 'en', 0x41646420446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'gdf_new', 'en', 0x4e657720446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'gender', 'en', 0x47656e646572);
INSERT INTO `lng_data` VALUES ('common', 'gender_f', 'en', 0x46656d616c65);
INSERT INTO `lng_data` VALUES ('common', 'gender_m', 'en', 0x4d616c65);
INSERT INTO `lng_data` VALUES ('common', 'generate', 'en', 0x47656e6572617465);
INSERT INTO `lng_data` VALUES ('common', 'glo', 'en', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_a', 'en', 0x6120476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_add', 'en', 0x41646420476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_added', 'en', 0x476c6f7373617279206164646564);
INSERT INTO `lng_data` VALUES ('common', 'glo_create', 'en', 0x43726561746520476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_delete', 'en', 0x44656c65746520476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'glo_join', 'en', 0x53756273637269626520746f20476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_leave', 'en', 0x556e7375627363726962652066726f6d20476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_new', 'en', 0x4e657720476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_read', 'en', 0x526561642061636365737320746f20476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glo_upload_file', 'en', 0x46696c65);
INSERT INTO `lng_data` VALUES ('common', 'glo_visible', 'en', 0x476c6f73736172792069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'glo_write', 'en', 0x4564697420476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'global', 'en', 0x476c6f62616c);
INSERT INTO `lng_data` VALUES ('common', 'global_user', 'en', 0x476c6f62616c207573657273);
INSERT INTO `lng_data` VALUES ('common', 'glossaries', 'en', 0x476c6f73736172696573);
INSERT INTO `lng_data` VALUES ('common', 'glossary', 'en', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'glossary_added', 'en', 0x476c6f7373617279206164646564);
INSERT INTO `lng_data` VALUES ('common', 'group_access_denied', 'en', 0x4163636573732044656e696564);
INSERT INTO `lng_data` VALUES ('common', 'group_any_objects', 'en', 0x4e6f207375626f626a6563747320617661696c61626c65);
INSERT INTO `lng_data` VALUES ('common', 'group_create_chat', 'en', 0x4372656174652063686174);
INSERT INTO `lng_data` VALUES ('common', 'group_desc', 'en', 0x47726f7570204465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'group_details', 'en', 0x47726f75702044657461696c73);
INSERT INTO `lng_data` VALUES ('common', 'group_filesharing', 'en', 0x47726f75702046696c652053686172696e67);
INSERT INTO `lng_data` VALUES ('common', 'group_import', 'en', 0x47726f757020696d706f7274);
INSERT INTO `lng_data` VALUES ('common', 'group_import_file', 'en', 0x496d706f72742066696c65);
INSERT INTO `lng_data` VALUES ('common', 'group_members', 'en', 0x47726f7570204d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'group_memstat', 'en', 0x4d656d62657220537461747573);
INSERT INTO `lng_data` VALUES ('common', 'group_memstat_admin', 'en', 0x41646d696e6973747261746f72);
INSERT INTO `lng_data` VALUES ('common', 'group_memstat_member', 'en', 0x4d656d626572);
INSERT INTO `lng_data` VALUES ('common', 'group_name', 'en', 0x47726f7570206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'group_new_registrations', 'en', 0x4e657720726567697374726174696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'group_no_registration', 'en', 0x6e6f20726567697374726174696f6e207265717569726564);
INSERT INTO `lng_data` VALUES ('common', 'group_no_registration_msg', 'en', 0x596f7520617265206e6f742061206d656d626572206f6620746869732067726f757020736f206661722e2044756520746f20726561736f6e73206f662061646d696e697374726174696f6e206974206973206e656365737361727920746f206a6f696e20746865207265717565737465642067726f75702e3c6272202f3e41732061206d656d62657220796f7520686176652074686520666f6c6c6f77696e6720616476616e74616765733a3c6272202f3e2d20596f752067657420696e666f726d65642061626f7574206e65777320616e6420757064617465733c6272202f3e2d20596f752063616e2061636365737320746865206163636f7264696e67206f626a65637473206c696b6520666f72756d732c206c6561726e696e67206d6f64756c65732c206574632e3c6272202f3e3c6272202f3e596f752063616e20616e6e756c20796f7572206d656d6265727368697020617420616e792074696d652e);
INSERT INTO `lng_data` VALUES ('common', 'group_not_available', 'en', 0x4e6f2067726f757020617661696c61626c65);
INSERT INTO `lng_data` VALUES ('common', 'group_objects', 'en', 0x47726f7570204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'group_password_registration_expired_msg', 'en', 0x54686520706572696f64206f6620726567697374726174696f6e206f6620746869732067726f757020697320657870697265642c20616e20616e6e6f756e63656d656e74206973206e6f206c6f6e67657220706f737369626c652e20506c6561736520636f6e7461637420746865206163636f7264696e672067726f7570206f776e6572206f722061646d696e6973747261746f722e);
INSERT INTO `lng_data` VALUES ('common', 'group_password_registration_msg', 'en', 0x546f206a6f696e20746869732067726f757020796f75206d75737420656e746572207468652070617373776f72642070726f7669646564206279207468652061646d696e6973747261746f72206f6620746869732067726f75702e3c6272202f3e596f7520617265206175746f6d61746963616c6c792061737369676e656420746f207468652067726f757020696620796f75722070617373776f726420697320636f72726563742e);
INSERT INTO `lng_data` VALUES ('common', 'group_registration_expiration_date', 'en', 0x45787069726174696f6e2044617465);
INSERT INTO `lng_data` VALUES ('common', 'group_registration_expiration_time', 'en', 0x45787069726174696f6e2054696d65);
INSERT INTO `lng_data` VALUES ('common', 'group_registration_expired', 'en', 0x506572696f64206f6620726567697374726174696f6e2065787069726564);
INSERT INTO `lng_data` VALUES ('common', 'group_registration_mode', 'en', 0x526567697374726174696f6e204d6f6465);
INSERT INTO `lng_data` VALUES ('common', 'group_req_password', 'en', 0x526567697374726174696f6e2070617373776f7264207265717569726564);
INSERT INTO `lng_data` VALUES ('common', 'group_req_registration', 'en', 0x526567697374726174696f6e207265717569726564);
INSERT INTO `lng_data` VALUES ('common', 'group_req_registration_msg', 'en', 0x546f206a6f696e20746869732067726f757020796f75206d7573742072656769737465722e20546865206163636f7264696e672067726f75702061646d696e6973747261746f722077696c6c2061737369676e20796f7520746f207468652067726f75702e20506c6561736520656e7465722061207375626a65637420746f206769766520726561736f6e7320746f20796f7572206170706c69636174696f6e2e3c6272202f3e596f752077696c6c20726563656976652061206d657373616765207768656e20796f75206765742061737369676e656420746f207468652067726f75702e);
INSERT INTO `lng_data` VALUES ('common', 'group_reset', 'en', 0x52657365742047726f7570205065726d697373696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'group_status', 'en', 0x47726f7570206973);
INSERT INTO `lng_data` VALUES ('common', 'group_status_closed', 'en', 0x436c6f736564);
INSERT INTO `lng_data` VALUES ('common', 'group_status_desc', 'en', 0x44657465726d696e65732074686520696e697469616c20737461747573206f66207468652067726f7570206279207573696e6720746865207065726d697373696f6e2073657474696e67732066726f6d2074686520726f6c652074656d706c617465732027696c5f6772705f7374617475735f636c6f73656427206f722027696c5f6772705f7374617475735f6f70656e273c6272202f3e5075626c69633a2047726f75702069732076697369626c653b2055736572206d6179207375627363726962652e3c6272202f3e436c6f7365643a2047726f7570206973206e6f742076697369626c6520666f72206e6f6e2d6d656d626572733b20557365722068617320746f20626520696e76697465642062792061206d656d62657220746f206a6f696e2e);
INSERT INTO `lng_data` VALUES ('common', 'group_status_public', 'en', 0x5075626c6963);
INSERT INTO `lng_data` VALUES ('common', 'groups', 'en', 0x47726f757073);
INSERT INTO `lng_data` VALUES ('common', 'groups_overview', 'en', 0x47726f757073204f76657276696577);
INSERT INTO `lng_data` VALUES ('common', 'grp', 'en', 0x47726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_a', 'en', 0x612047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_add', 'en', 0x4164642047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_add_member', 'en', 0x416464206d656d626572287329);
INSERT INTO `lng_data` VALUES ('common', 'grp_added', 'en', 0x47726f7570206164646564);
INSERT INTO `lng_data` VALUES ('common', 'grp_already_applied', 'en', 0x596f75206861766520616c7265616479206170706c69656420746f20746869732067726f75702120506c65617365207761697420666f7220636f6e6669726d6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'grp_app_send_mail', 'en', 0x53656e64206170706c6963616e742061206d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'grp_back', 'en', 0x4261636b);
INSERT INTO `lng_data` VALUES ('common', 'grp_count_members', 'en', 0x4e756d626572206f66206d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'grp_create', 'en', 0x4372656174652047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_alm', 'en', 0x4372656174652041494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_chat', 'en', 0x4372656174652043686174);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_dbk', 'en', 0x43726561746520446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_exc', 'en', 0x437265617465204578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_file', 'en', 0x55706c6f61642046696c65);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_fold', 'en', 0x43726561746520466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_frm', 'en', 0x43726561746520466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_glo', 'en', 0x43726561746520476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_hlm', 'en', 0x4372656174652048414350204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_htlm', 'en', 0x4372656174652048544d4c204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_lm', 'en', 0x43726561746520494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_mep', 'en', 0x437265617465204d6564696120506f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_qpl', 'en', 0x437265617465205175657374696f6e20506f6f6c20666f722054657374);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_sahs', 'en', 0x4372656174652053434f524d2f41494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_spl', 'en', 0x437265617465205175657374696f6e20506f6f6c20666f7220537572766579);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_svy', 'en', 0x43726561746520537572766579);
INSERT INTO `lng_data` VALUES ('common', 'grp_create_tst', 'en', 0x4372656174652054657374);
INSERT INTO `lng_data` VALUES ('common', 'grp_delete', 'en', 0x44656c6574652047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_dismiss_member', 'en', 0x41726520796f75207375726520796f752077616e7420746f206469736d6973732074686520666f6c6c6f77696e67206d656d6265722873292066726f6d207468652067726f75703f);
INSERT INTO `lng_data` VALUES ('common', 'grp_dismiss_myself', 'en', 0x41726520796f75207375726520796f752077616e7420746f206469736d69737320796f757273656c662066726f6d207468652067726f75703f);
INSERT INTO `lng_data` VALUES ('common', 'grp_edit', 'en', 0x456469742047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'grp_err_administrator_required', 'en', 0x4d656d62657220636f756c64206e6f742062652072656d6f7665642c206174206c65617374206f6e652061646d696e6973747261746f72207065722067726f75702069732072657175697265642021);
INSERT INTO `lng_data` VALUES ('common', 'grp_err_at_least_one_groupadministrator_is_needed', 'en', 0x456163682067726f7570206e65656473206174206c65617374206f6e652067726f75702061646d696e6973747261746f72);
INSERT INTO `lng_data` VALUES ('common', 'grp_err_error', 'en', 0x416e206572726f72206f63637572726564);
INSERT INTO `lng_data` VALUES ('common', 'grp_err_last_member', 'en', 0x4c617374206d656d62657220636f756c64206e6f742062652072656d6f7665642c20706c656173652064656c6574652067726f757020696e73746561642e);
INSERT INTO `lng_data` VALUES ('common', 'grp_err_member_could_not_be_removed', 'en', 0x4d656d62657220636f756c64206e6f742062652072656d6f7665642e20506c656173652076657269667920616c6c20646570656e64656e63696573206f66207468697320757365722e);
INSERT INTO `lng_data` VALUES ('common', 'grp_err_no_permission', 'en', 0x596f7520646f206e6f7420706f737365737320746865207065726d697373696f6e7320666f722074686973206f7065726174696f6e2e);
INSERT INTO `lng_data` VALUES ('common', 'grp_err_registration_data', 'en', 0x506c6561736520656e74657220612070617373776f72642c207468652065787069726174696f6e206461746520616e642074696d6520666f7220612076616c696420726567697374726174696f6e20706572696f642021);
INSERT INTO `lng_data` VALUES ('common', 'grp_header_edit_members', 'en', 0x45646974206d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'grp_join', 'en', 0x55736572206d6179206a6f696e2047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_leave', 'en', 0x55736572206d6179206c656176652047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_list_users', 'en', 0x4c697374207573657273);
INSERT INTO `lng_data` VALUES ('common', 'grp_mem_change_status', 'en', 0x4368616e6765206d656d62657220737461747573);
INSERT INTO `lng_data` VALUES ('common', 'grp_mem_leave', 'en', 0x4469736d697373206d656d6265722066726f6d2067726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_mem_send_mail', 'en', 0x53656e64206d656d6265722061206d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'grp_msg_applicants_assigned', 'en', 0x4170706c6963616e742873292061737369676e65642061732067726f7570206d656d626572287329);
INSERT INTO `lng_data` VALUES ('common', 'grp_msg_applicants_removed', 'en', 0x4170706c6963616e742873292072656d6f7665642066726f6d206c6973742e);
INSERT INTO `lng_data` VALUES ('common', 'grp_msg_member_assigned', 'en', 0x557365722873292061737369676e65642061732067726f7570206d656d626572287329);
INSERT INTO `lng_data` VALUES ('common', 'grp_msg_membership_annulled', 'en', 0x4d656d6265727368697020616e6e756c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'grp_name_exists', 'en', 0x546865726520697320616c726561647920612067726f757020776974682074686973206e616d652120506c656173652063686f6f736520616e6f74686572206e616d652e);
INSERT INTO `lng_data` VALUES ('common', 'grp_new', 'en', 0x4e65772047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_new_search', 'en', 0x4e657720736561726368);
INSERT INTO `lng_data` VALUES ('common', 'grp_no_groups_selected', 'en', 0x506c656173652073656c65637420612067726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_no_results_found', 'en', 0x4e6f20726573756c747320666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'grp_no_roles_selected', 'en', 0x506c656173652073656c656374206120726f6c65);
INSERT INTO `lng_data` VALUES ('common', 'grp_options', 'en', 0x4f7074696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'grp_read', 'en', 0x526561642061636365737320746f2047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'grp_register', 'en', 0x5265676973746572);
INSERT INTO `lng_data` VALUES ('common', 'grp_registration_completed', 'en', 0x526567697374726174696f6e20636f6d706c65746564);
INSERT INTO `lng_data` VALUES ('common', 'grp_search_enter_search_string', 'en', 0x506c6561736520656e74657220612073656172636820737472696e67);
INSERT INTO `lng_data` VALUES ('common', 'grp_search_members', 'en', 0x5573657220736561726368);
INSERT INTO `lng_data` VALUES ('common', 'grp_visible', 'en', 0x47726f75702069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'grp_write', 'en', 0x456469742047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'guest', 'en', 0x4775657374);
INSERT INTO `lng_data` VALUES ('common', 'guests', 'en', 0x477565737473);
INSERT INTO `lng_data` VALUES ('common', 'header_title', 'en', 0x486561646572205469746c65);
INSERT INTO `lng_data` VALUES ('common', 'help', 'en', 0x48656c70);
INSERT INTO `lng_data` VALUES ('common', 'hide_details', 'en', 0x686964652064657461696c73);
INSERT INTO `lng_data` VALUES ('common', 'hide_structure', 'en', 0x44697361626c6520537472756374757265642d56696577);
INSERT INTO `lng_data` VALUES ('common', 'hits_per_page', 'en', 0x486974732f50616765);
INSERT INTO `lng_data` VALUES ('common', 'hlm', 'en', 0x4c6561726e696e67204d6f64756c652048414350);
INSERT INTO `lng_data` VALUES ('common', 'hlm_added', 'en', 0x48414350204c6561726e696e67204d6f64756c65206164646564);
INSERT INTO `lng_data` VALUES ('common', 'hlm_create', 'en', 0x4372656174652048414350204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'hlm_delete', 'en', 0x44656c6574652048414350204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'hlm_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'hlm_join', 'en', 0x53756273637269626520746f2048414350204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'hlm_leave', 'en', 0x556e7375627363726962652066726f6d2048414350204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'hlm_read', 'en', 0x526561642061636365737320746f2048414350204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'hlm_visible', 'en', 0x48414350204c6561726e696e67204d6f64756c652069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'hlm_write', 'en', 0x456469742048414350204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'hobby', 'en', 0x496e746572657374732f486f6262696573);
INSERT INTO `lng_data` VALUES ('common', 'home', 'en', 0x486f6d65);
INSERT INTO `lng_data` VALUES ('common', 'host', 'en', 0x486f7374);
INSERT INTO `lng_data` VALUES ('common', 'hours_of_day', 'en', 0x486f757273206f6620646179);
INSERT INTO `lng_data` VALUES ('common', 'htlm', 'en', 0x4c6561726e696e67204d6f64756c652048544d4c);
INSERT INTO `lng_data` VALUES ('common', 'htlm_add', 'en', 0x4164642048544d4c204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'htlm_delete', 'en', 0x44656c6574652048544d4c204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'htlm_edit', 'en', 0x456469742048544d4c204c6561726e696e67204d6f64756c652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('common', 'htlm_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'htlm_new', 'en', 0x4e65772048544d4c204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'htlm_read', 'en', 0x526561642048544d4c204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'htlm_visible', 'en', 0x48544d4c204c6561726e696e67204d6f64756c652069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'htlm_write', 'en', 0x456469742048544d4c204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'http_not_possible', 'en', 0x5468697320736572766572206973206e6f7420737570706f7274696e6720687474702072657175657374732e);
INSERT INTO `lng_data` VALUES ('common', 'http_path', 'en', 0x487474702050617468);
INSERT INTO `lng_data` VALUES ('common', 'https_not_possible', 'en', 0x5468697320736572766572206973206e6f7420737570706f7274696e6720485454505320636f6e6e6e656374696f6e732e);
INSERT INTO `lng_data` VALUES ('common', 'id', 'en', 0x4944);
INSERT INTO `lng_data` VALUES ('common', 'identifier', 'en', 0x6964656e746966696572);
INSERT INTO `lng_data` VALUES ('common', 'if_no_title_then_filename', 'en', 0x4c6561766520626c616e6b20746f20646973706c6179207468652066696c65206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'ilias_version', 'en', 0x494c4941532076657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'image', 'en', 0x496d616765);
INSERT INTO `lng_data` VALUES ('common', 'image_gen_unsucc', 'en', 0x496d6167652067656e65726174696f6e20756e7375636365737366756c2e20436f6e7461637420796f75722073797374656d2061646d696e6973747261746f7220616e64207665726966792074686520636f6e7665727420706174682e);
INSERT INTO `lng_data` VALUES ('common', 'import', 'en', 0x496d706f7274);
INSERT INTO `lng_data` VALUES ('common', 'import_alm', 'en', 0x496d706f72742041494343205061636b616765);
INSERT INTO `lng_data` VALUES ('common', 'import_categories', 'en', 0x496d706f72742043617465676f72696573);
INSERT INTO `lng_data` VALUES ('common', 'import_file', 'en', 0x496d706f72742046696c65);
INSERT INTO `lng_data` VALUES ('common', 'import_file_not_valid', 'en', 0x54686520696d706f72742066696c65206973206e6f742076616c69642e);
INSERT INTO `lng_data` VALUES ('common', 'import_finished', 'en', 0x4e756d626572206f6620696d706f72746564206d657373616765732e);
INSERT INTO `lng_data` VALUES ('common', 'import_forum_finished', 'en', 0x54686520666f72756d20686173206265656e20696d706f727465642e);
INSERT INTO `lng_data` VALUES ('common', 'import_glossary', 'en', 0x496d706f727420476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'import_grp_finished', 'en', 0x496d706f727465642067726f757020776974686f757420616e79206572726f72);
INSERT INTO `lng_data` VALUES ('common', 'import_hlm', 'en', 0x496d706f72742048414350205061636b616765);
INSERT INTO `lng_data` VALUES ('common', 'import_lm', 'en', 0x496d706f727420494c494153204c6561726e696e67206d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'import_root_user', 'en', 0x496d706f727420526f6f742055736572);
INSERT INTO `lng_data` VALUES ('common', 'import_sahs', 'en', 0x496d706f72742053434f524d2f41494343205061636b616765);
INSERT INTO `lng_data` VALUES ('common', 'import_tst', 'en', 0x496d706f72742054657374);
INSERT INTO `lng_data` VALUES ('common', 'import_users', 'en', 0x496d706f7274205573657273);
INSERT INTO `lng_data` VALUES ('common', 'imported', 'en', 0x696d706f72746564);
INSERT INTO `lng_data` VALUES ('common', 'in', 'en', 0x696e);
INSERT INTO `lng_data` VALUES ('common', 'in_use', 'en', 0x55736572204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'inactive', 'en', 0x496e616374697665);
INSERT INTO `lng_data` VALUES ('common', 'inbox', 'en', 0x496e626f78);
INSERT INTO `lng_data` VALUES ('common', 'info_assign_sure', 'en', 0x41726520796f75207375726520796f752077616e7420746f2061737369676e2074686520666f6c6c6f77696e6720757365722873293f);
INSERT INTO `lng_data` VALUES ('common', 'info_delete_sure', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e67206f626a6563742873293f);
INSERT INTO `lng_data` VALUES ('common', 'info_deleted', 'en', 0x4f626a6563742873292044656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'info_trash', 'en', 0x44656c65746564204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'inform_user_mail', 'en', 0x53656e6420656d61696c20746f20696e666f726d20757365722061626f7574206368616e676573);
INSERT INTO `lng_data` VALUES ('common', 'information_abbr', 'en', 0x496e666f);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_all_offers', 'en', 0x416c6c204c45);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_back_to_le', 'en', 0x4261636b20746f204c45);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_afternoon', 'en', 0x476f6f642061667465726e6f6f6e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_evening', 'en', 0x476f6f64206576656e696e67);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_morning', 'en', 0x476f6f64206d6f726e696e67);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_good_night', 'en', 0x476f6f64206e69676874);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_hello', 'en', 0x48656c6c6f);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_info_about_work1', 'en', 0x4f6e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_info_about_work2', 'en', 0x2c20796f7520776f726b6564206865726520666f7220746865206c6173742074696d652e20596f752063616e20636f6e74696e756520746865726520627920636c69636b696e6720686572653a);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_level_zero', 'en', 0x4c6576656c207570);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_pers_dates', 'en', 0x506572736f6e616c2064617461);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_reg_fo', 'en', 0x5265676973746572656420666f72756d73);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_reg_le', 'en', 0x52656769737465726564204c45);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_info1', 'en', 0x4865726520796f752063616e206368616e676520796f757220706572736f6e616c20646174612e2045786365707420666f722074686520494e474d4544494120757365726e616d6520616e642070617373776f72642c20796f7520646f206e6f74206861766520746f2066696c6c206f7574206f74686572206669656c64732e2052656d656d6265722074686174206c6f73742070617373776f7264732063616e206f6e6c792062652072657475726e656420627920652d6d61696c202821292e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_info2', 'en', 0x4974206973206e6f7420616c6c6f77656420746f20757365207370656369616c206368617261637465727320286c696b65202c203b202e203d202d202a202b20232920696e20796f757220494e474d4544494120757365726e616d6520616e642070617373776f72642e20546f20626520616c6c6f77656420746f207573652074686520494e474d4544494120706c6174666f726d2c20796f75206d757374206163636570742074686520757365722061677265656d656e742e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_info3', 'en', 0x506c656173652073656c656374206f6e65206f6620746865206c696e6b7320696e207468652073696465206261722e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_use_title', 'en', 0x4368616e6765207573657220646174612e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_user_agree', 'en', 0x41677265656d656e74);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_vis_le', 'en', 0x56697369746564204c45);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_visited_le', 'en', 0x416c6c204c4520796f752068617665207669736974656420736f206661722e);
INSERT INTO `lng_data` VALUES ('common', 'ingmedia_welcome', 'en', 0x2c2077656c636f6d6520746f20796f757220706572736f6e616c206465736b746f702121);
INSERT INTO `lng_data` VALUES ('common', 'inifile', 'en', 0x496e692d46696c65);
INSERT INTO `lng_data` VALUES ('common', 'input_error', 'en', 0x496e707574206572726f72);
INSERT INTO `lng_data` VALUES ('common', 'insert', 'en', 0x496e73657274);
INSERT INTO `lng_data` VALUES ('common', 'inst_id', 'en', 0x496e7374616c6c6174696f6e204944);
INSERT INTO `lng_data` VALUES ('common', 'inst_info', 'en', 0x496e7374616c6c6174696f6e20496e666f);
INSERT INTO `lng_data` VALUES ('common', 'inst_name', 'en', 0x496e7374616c6c6174696f6e204e616d65);
INSERT INTO `lng_data` VALUES ('common', 'install', 'en', 0x496e7374616c6c);
INSERT INTO `lng_data` VALUES ('common', 'installed', 'en', 0x496e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'institution', 'en', 0x496e737469747574696f6e);
INSERT INTO `lng_data` VALUES ('common', 'internal_local_roles_only', 'en', 0x4c6f63616c20726f6c657320286f6e6c79206175746f67656e65726174656429);
INSERT INTO `lng_data` VALUES ('common', 'internal_system', 'en', 0x496e7465726e616c2073797374656d);
INSERT INTO `lng_data` VALUES ('common', 'ip_address', 'en', 0x49502041646472657373);
INSERT INTO `lng_data` VALUES ('common', 'is_already_your', 'en', 0x697320616c726561647920796f7572);
INSERT INTO `lng_data` VALUES ('common', 'item', 'en', 0x4974656d);
INSERT INTO `lng_data` VALUES ('common', 'kb', 'en', 0x4b42797465);
INSERT INTO `lng_data` VALUES ('common', 'keywords', 'en', 0x4b6579776f726473);
INSERT INTO `lng_data` VALUES ('common', 'lang_cs', 'en', 0x437a656368);
INSERT INTO `lng_data` VALUES ('common', 'lang_da', 'en', 0x44616e697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_dateformat', 'en', 0x592d6d2d64);
INSERT INTO `lng_data` VALUES ('common', 'lang_de', 'en', 0x4765726d616e);
INSERT INTO `lng_data` VALUES ('common', 'lang_el', 'en', 0x477265656b);
INSERT INTO `lng_data` VALUES ('common', 'lang_en', 'en', 0x456e676c697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_es', 'en', 0x5370616e697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_fi', 'en', 0x46696e6e697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_fr', 'en', 0x4672656e6368);
INSERT INTO `lng_data` VALUES ('common', 'lang_id', 'en', 0x496e646f6e657369616e);
INSERT INTO `lng_data` VALUES ('common', 'lang_it', 'en', 0x4974616c69616e);
INSERT INTO `lng_data` VALUES ('common', 'lang_ja', 'en', 0x4a6170616e657365);
INSERT INTO `lng_data` VALUES ('common', 'lang_lt', 'en', 0x4c69746875616e69616e);
INSERT INTO `lng_data` VALUES ('common', 'lang_nl', 'en', 0x4475746368);
INSERT INTO `lng_data` VALUES ('common', 'lang_no', 'en', 0x4e6f7277656769616e);
INSERT INTO `lng_data` VALUES ('common', 'lang_path', 'en', 0x4c616e67756167652050617468);
INSERT INTO `lng_data` VALUES ('common', 'lang_pl', 'en', 0x506f6c697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_pt', 'en', 0x506f7274756775657365);
INSERT INTO `lng_data` VALUES ('common', 'lang_sep_decimal', 'en', 0x2e);
INSERT INTO `lng_data` VALUES ('common', 'lang_sep_thousand', 'en', 0x2c);
INSERT INTO `lng_data` VALUES ('common', 'lang_sv', 'en', 0x53776564697368);
INSERT INTO `lng_data` VALUES ('common', 'lang_timeformat', 'en', 0x483a693a73);
INSERT INTO `lng_data` VALUES ('common', 'lang_ua', 'en', 0x556b7261696e69616e);
INSERT INTO `lng_data` VALUES ('common', 'lang_version', 'en', 0x31);
INSERT INTO `lng_data` VALUES ('common', 'lang_vi', 'en', 0x566965746e616d657365);
INSERT INTO `lng_data` VALUES ('common', 'lang_xx', 'en', 0x437573746f6d);
INSERT INTO `lng_data` VALUES ('common', 'lang_zh', 'en', 0x53696d706c6966696564204368696e657365);
INSERT INTO `lng_data` VALUES ('common', 'langfile_found', 'en', 0x4c616e67756167652066696c6520666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'language', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'language_not_installed', 'en', 0x6973206e6f7420696e7374616c6c65642e20506c6561736520696e7374616c6c2074686174206c616e6775616765206669727374);
INSERT INTO `lng_data` VALUES ('common', 'languages', 'en', 0x4c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'languages_already_installed', 'en', 0x53656c6563746564206c616e67756167652873292061726520616c726561647920696e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'languages_already_uninstalled', 'en', 0x53656c6563746564206c616e67756167652873292061726520616c726561647920756e696e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'languages_updated', 'en', 0x416c6c20696e7374616c6c6564206c616e6775616765732068617665206265656e2075706461746564);
INSERT INTO `lng_data` VALUES ('common', 'last_change', 'en', 0x4c617374204368616e6765);
INSERT INTO `lng_data` VALUES ('common', 'last_visit', 'en', 0x4c617374205669736974);
INSERT INTO `lng_data` VALUES ('common', 'lastname', 'en', 0x4c617374206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'launch', 'en', 0x4c61756e6368);
INSERT INTO `lng_data` VALUES ('common', 'ldap', 'en', 0x4c444150);
INSERT INTO `lng_data` VALUES ('common', 'ldap_basedn', 'en', 0x4c4441502042617365444e);
INSERT INTO `lng_data` VALUES ('common', 'ldap_configure', 'en', 0x436f6e666967757265204c4441502041757468656e7469636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'ldap_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'ldap_login_key', 'en', 0x41747472696275746520666f72206c6f67696e206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'ldap_objectclass', 'en', 0x4f626a656374436c617373206f662075736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'ldap_passwd', 'en', 0x596f75722063757272656e742070617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'ldap_port', 'en', 0x4c44415020506f7274);
INSERT INTO `lng_data` VALUES ('common', 'ldap_read', 'en', 0x526561642061636365737320746f204c4441502073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'ldap_search_base', 'en', 0x4c444150205365617263682062617365);
INSERT INTO `lng_data` VALUES ('common', 'ldap_server', 'en', 0x4c444150205365727665722055524c);
INSERT INTO `lng_data` VALUES ('common', 'ldap_tls', 'en', 0x557365204c44415020544c53);
INSERT INTO `lng_data` VALUES ('common', 'ldap_v2', 'en', 0x4c4441507632);
INSERT INTO `lng_data` VALUES ('common', 'ldap_v3', 'en', 0x4c4441507633);
INSERT INTO `lng_data` VALUES ('common', 'ldap_version', 'en', 0x4c4441502070726f746f6b6f6c6c2076657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'ldap_visible', 'en', 0x4c4441502073657474696e6773206172652076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'ldap_write', 'en', 0x45646974204c4441502073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'learning module', 'en', 0x4c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'learning_objects', 'en', 0x4c6561726e696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'learning_resources', 'en', 0x4c6561726e696e67205265736f7572636573);
INSERT INTO `lng_data` VALUES ('common', 'level', 'en', 0x4c6576656c);
INSERT INTO `lng_data` VALUES ('common', 'link', 'en', 0x4c696e6b);
INSERT INTO `lng_data` VALUES ('common', 'linked_object', 'en', 0x546865206f626a65637420686173206265656e206c696e6b6564);
INSERT INTO `lng_data` VALUES ('common', 'linked_pages', 'en', 0x4c696e6b6564205061676573);
INSERT INTO `lng_data` VALUES ('common', 'list_of_pages', 'en', 0x5061676573204c697374);
INSERT INTO `lng_data` VALUES ('common', 'list_of_questions', 'en', 0x5175657374696f6e204c697374);
INSERT INTO `lng_data` VALUES ('common', 'literature', 'en', 0x4c697465726174757265);
INSERT INTO `lng_data` VALUES ('common', 'literature_bookmarks', 'en', 0x4c69746572617475726520426f6f6b6d61726b73);
INSERT INTO `lng_data` VALUES ('common', 'lm', 'en', 0x4c6561726e696e67204d6f64756c6520494c494153);
INSERT INTO `lng_data` VALUES ('common', 'lm_a', 'en', 0x616e20494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_add', 'en', 0x41646420494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_added', 'en', 0x494c494153204c6561726e696e67204d6f64756c65206164646564);
INSERT INTO `lng_data` VALUES ('common', 'lm_create', 'en', 0x43726561746520494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_delete', 'en', 0x44656c65746520494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'lm_join', 'en', 0x53756273637269626520746f20494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_leave', 'en', 0x556e7375627363726962652066726f6d20494c4941534c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_new', 'en', 0x4e657720494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_read', 'en', 0x526561642061636365737320746f20494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_type_aicc', 'en', 0x41494343);
INSERT INTO `lng_data` VALUES ('common', 'lm_type_hacp', 'en', 0x48414350);
INSERT INTO `lng_data` VALUES ('common', 'lm_type_scorm', 'en', 0x53434f524d);
INSERT INTO `lng_data` VALUES ('common', 'lm_visible', 'en', 0x494c494153204c6561726e696e67204d6f64756c652069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'lm_write', 'en', 0x4564697420494c494153204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'lng', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'lngf', 'en', 0x4c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'lngf_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'lngf_read', 'en', 0x526561642061636365737320746f204c616e67756167652073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'lngf_visible', 'en', 0x4c616e67756167652073657474696e6773206172652076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'lo', 'en', 0x4c6561726e696e67204f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'lo_available', 'en', 0x4f76657276696577204c6561726e696e67204d6f64756c6573202620436f7572736573);
INSERT INTO `lng_data` VALUES ('common', 'lo_categories', 'en', 0x4c4f2043617465676f72696573);
INSERT INTO `lng_data` VALUES ('common', 'lo_edit', 'en', 0x45646974204c6561726e696e67204f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'lo_new', 'en', 0x4e6577204c6561726e696e67204f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'lo_no_content', 'en', 0x4e6f204c6561726e696e67205265736f757263657320417661696c61626c65);
INSERT INTO `lng_data` VALUES ('common', 'lo_other_langs', 'en', 0x4c4f7320696e204f74686572204c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'lo_overview', 'en', 0x4c4f204f76657276696577);
INSERT INTO `lng_data` VALUES ('common', 'local', 'en', 0x4c6f63616c);
INSERT INTO `lng_data` VALUES ('common', 'locator', 'en', 0x4c6f636174696f6e3a);
INSERT INTO `lng_data` VALUES ('common', 'logic_and', 'en', 0x616e64);
INSERT INTO `lng_data` VALUES ('common', 'logic_or', 'en', 0x6f72);
INSERT INTO `lng_data` VALUES ('common', 'login', 'en', 0x4c6f67696e);
INSERT INTO `lng_data` VALUES ('common', 'login_as', 'en', 0x4c6f6767656420696e206173);
INSERT INTO `lng_data` VALUES ('common', 'login_data', 'en', 0x4c6f67696e2064617461);
INSERT INTO `lng_data` VALUES ('common', 'login_exists', 'en', 0x546865726520697320616c72656164792061207573657220776974682074686973206c6f67696e206e616d652120506c656173652063686f6f736520616e6f74686572206f6e652e);
INSERT INTO `lng_data` VALUES ('common', 'login_invalid', 'en', 0x5468652063686f73656e206c6f67696e20697320696e76616c696421204f6e6c792074686520666f6c6c6f77696e6720636861726163746572732061726520616c6c6f77656420286d696e696d756d20342063686172616374657273293a20412d5a20612d7a20302d39205f2e2d2b2a402124257e);
INSERT INTO `lng_data` VALUES ('common', 'login_time', 'en', 0x54696d65206f6e6c696e65);
INSERT INTO `lng_data` VALUES ('common', 'login_to_ilias', 'en', 0x4c6f67696e20746f20494c494153);
INSERT INTO `lng_data` VALUES ('common', 'logout', 'en', 0x4c6f676f7574);
INSERT INTO `lng_data` VALUES ('common', 'logout_text', 'en', 0x596f75206c6f67676564206f66662066726f6d20494c4941532e20596f75722073657373696f6e20686173206265656e20636c6f7365642e);
INSERT INTO `lng_data` VALUES ('common', 'los', 'en', 0x4c6561726e696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'los_last_visited', 'en', 0x4c6173742056697369746564204c6561726e696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'mail', 'en', 0x4d61696c);
INSERT INTO `lng_data` VALUES ('common', 'mail_a_root', 'en', 0x4d61696c626f78);
INSERT INTO `lng_data` VALUES ('common', 'mail_addressbook', 'en', 0x4164647265737320626f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'mail_allow_smtp', 'en', 0x416c6c6f7720534d5450);
INSERT INTO `lng_data` VALUES ('common', 'mail_b_inbox', 'en', 0x496e626f78);
INSERT INTO `lng_data` VALUES ('common', 'mail_c_trash', 'en', 0x5472617368);
INSERT INTO `lng_data` VALUES ('common', 'mail_d_drafts', 'en', 0x447261667473);
INSERT INTO `lng_data` VALUES ('common', 'mail_delete_error', 'en', 0x4572726f72207768696c652064656c6574696e67);
INSERT INTO `lng_data` VALUES ('common', 'mail_delete_error_file', 'en', 0x4572726f722064656c6574696e672066696c65);
INSERT INTO `lng_data` VALUES ('common', 'mail_e_sent', 'en', 0x53656e74);
INSERT INTO `lng_data` VALUES ('common', 'mail_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'mail_folders', 'en', 0x4d61696c20466f6c64657273);
INSERT INTO `lng_data` VALUES ('common', 'mail_import', 'en', 0x496d706f7274);
INSERT INTO `lng_data` VALUES ('common', 'mail_import_file', 'en', 0x4578706f72742066696c65);
INSERT INTO `lng_data` VALUES ('common', 'mail_intern_enable', 'en', 0x456e61626c65);
INSERT INTO `lng_data` VALUES ('common', 'mail_mail_visible', 'en', 0x55736572206d617920757365206d61696c2073797374656d);
INSERT INTO `lng_data` VALUES ('common', 'mail_mails_of', 'en', 0x4d61696c);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_attach', 'en', 0x4d61782e206174746163686d656e742073697a65);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_box', 'en', 0x4d61782e206d61696c626f782073697a65);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxsize_mail', 'en', 0x4d61782e206d61696c2073697a65);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxtime_attach', 'en', 0x4d61782e206461797320746f206b656570206174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('common', 'mail_maxtime_mail', 'en', 0x4d61782e206461797320746f206b656570206d61696c);
INSERT INTO `lng_data` VALUES ('common', 'mail_not_sent', 'en', 0x4d61696c206e6f742073656e7421);
INSERT INTO `lng_data` VALUES ('common', 'mail_read', 'en', 0x526561642061636365737320746f204d61696c2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'mail_search_no', 'en', 0x4e6f20656e747269657320666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'mail_search_word', 'en', 0x53656172636820776f7264);
INSERT INTO `lng_data` VALUES ('common', 'mail_select_one', 'en', 0x596f75206d7573742073656c656374206f6e65206d61696c);
INSERT INTO `lng_data` VALUES ('common', 'mail_send_error', 'en', 0x4572726f722073656e64696e67206d61696c);
INSERT INTO `lng_data` VALUES ('common', 'mail_sent', 'en', 0x4d61696c2073656e7421);
INSERT INTO `lng_data` VALUES ('common', 'mail_smtp_mail', 'en', 0x55736572206d61792073656e64206d61696c2076696120534d5450);
INSERT INTO `lng_data` VALUES ('common', 'mail_system', 'en', 0x53797374656d204d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'mail_system_message', 'en', 0x55736572206d61792073656e6420696e7465726e616c2073797374656d206d65737361676573);
INSERT INTO `lng_data` VALUES ('common', 'mail_visible', 'en', 0x4d61696c2073657474696e6773206172652076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'mail_write', 'en', 0x45646974204d61696c2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'mail_z_local', 'en', 0x5573657220466f6c64657273);
INSERT INTO `lng_data` VALUES ('common', 'mails', 'en', 0x4d61696c);
INSERT INTO `lng_data` VALUES ('common', 'manage_tracking_data', 'en', 0x4d616e61676520547261636b696e672044617461);
INSERT INTO `lng_data` VALUES ('common', 'mark_all_read', 'en', 0x4d61726b20416c6c2061732052656164);
INSERT INTO `lng_data` VALUES ('common', 'mark_all_unread', 'en', 0x4d61726b20416c6c20617320556e72656164);
INSERT INTO `lng_data` VALUES ('common', 'max_number_rec', 'en', 0x4d6178696d756d206e756d626572206f66207265636f726473);
INSERT INTO `lng_data` VALUES ('common', 'max_time', 'en', 0x4d6178696d756d2074696d65);
INSERT INTO `lng_data` VALUES ('common', 'member', 'en', 0x4d656d626572);
INSERT INTO `lng_data` VALUES ('common', 'members', 'en', 0x4d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'membership_annulled', 'en', 0x596f7572206d656d6265727368697020686173206265656e2063616e63656c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'mep', 'en', 0x4d6564696120506f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'mep_add', 'en', 0x416464204d6564696120506f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'mep_added', 'en', 0x4164646564204d6564696120506f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'mep_delete', 'en', 0x44656c657465204d6564696120506f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'mep_edit', 'en', 0x45646974204d6564696120506f6f6c2050726f70657274696573);
INSERT INTO `lng_data` VALUES ('common', 'mep_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'mep_new', 'en', 0x4e6577204d6564696120506f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'mep_read', 'en', 0x52656164204d6564696120506f6f6c20436f6e74656e74);
INSERT INTO `lng_data` VALUES ('common', 'mep_visible', 'en', 0x4d6564696120506f6f6c2069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'mep_write', 'en', 0x45646974204d6564696120506f6f6c20436f6e74656e74);
INSERT INTO `lng_data` VALUES ('common', 'message', 'en', 0x4d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'message_content', 'en', 0x4d65737361676520436f6e74656e74);
INSERT INTO `lng_data` VALUES ('common', 'message_to', 'en', 0x4d65737361676520746f3a);
INSERT INTO `lng_data` VALUES ('common', 'meta_data', 'en', 0x4d65746164617461);
INSERT INTO `lng_data` VALUES ('common', 'migrate', 'en', 0x4d696772617465);
INSERT INTO `lng_data` VALUES ('common', 'min_time', 'en', 0x4d696e696d756d2074696d65);
INSERT INTO `lng_data` VALUES ('common', 'mob', 'en', 0x4d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'module', 'en', 0x6d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'modules', 'en', 0x4d6f64756c6573);
INSERT INTO `lng_data` VALUES ('common', 'month', 'en', 0x4d6f6e7468);
INSERT INTO `lng_data` VALUES ('common', 'month_01_long', 'en', 0x4a616e75617279);
INSERT INTO `lng_data` VALUES ('common', 'month_01_short', 'en', 0x4a616e);
INSERT INTO `lng_data` VALUES ('common', 'month_02_long', 'en', 0x4665627275617279);
INSERT INTO `lng_data` VALUES ('common', 'month_02_short', 'en', 0x466562);
INSERT INTO `lng_data` VALUES ('common', 'month_03_long', 'en', 0x4d61726368);
INSERT INTO `lng_data` VALUES ('common', 'month_03_short', 'en', 0x4d6172);
INSERT INTO `lng_data` VALUES ('common', 'month_04_long', 'en', 0x417072696c);
INSERT INTO `lng_data` VALUES ('common', 'month_04_short', 'en', 0x417072);
INSERT INTO `lng_data` VALUES ('common', 'month_05_long', 'en', 0x4d6179);
INSERT INTO `lng_data` VALUES ('common', 'month_05_short', 'en', 0x4d6179);
INSERT INTO `lng_data` VALUES ('common', 'month_06_long', 'en', 0x4a756e65);
INSERT INTO `lng_data` VALUES ('common', 'month_06_short', 'en', 0x4a756e);
INSERT INTO `lng_data` VALUES ('common', 'month_07_long', 'en', 0x4a756c79);
INSERT INTO `lng_data` VALUES ('common', 'month_07_short', 'en', 0x4a756c);
INSERT INTO `lng_data` VALUES ('common', 'month_08_long', 'en', 0x417567757374);
INSERT INTO `lng_data` VALUES ('common', 'month_08_short', 'en', 0x417567);
INSERT INTO `lng_data` VALUES ('common', 'month_09_long', 'en', 0x53657074656d626572);
INSERT INTO `lng_data` VALUES ('common', 'month_09_short', 'en', 0x536570);
INSERT INTO `lng_data` VALUES ('common', 'month_10_long', 'en', 0x4f63746f626572);
INSERT INTO `lng_data` VALUES ('common', 'month_10_short', 'en', 0x4f6374);
INSERT INTO `lng_data` VALUES ('common', 'month_11_long', 'en', 0x4e6f76656d626572);
INSERT INTO `lng_data` VALUES ('common', 'month_11_short', 'en', 0x4e6f76);
INSERT INTO `lng_data` VALUES ('common', 'month_12_long', 'en', 0x446563656d626572);
INSERT INTO `lng_data` VALUES ('common', 'month_12_short', 'en', 0x446563);
INSERT INTO `lng_data` VALUES ('common', 'move', 'en', 0x4d6f7665);
INSERT INTO `lng_data` VALUES ('common', 'moveChapter', 'en', 0x4d6f7665);
INSERT INTO `lng_data` VALUES ('common', 'movePage', 'en', 0x4d6f7665);
INSERT INTO `lng_data` VALUES ('common', 'move_to', 'en', 0x4d6f766520746f);
INSERT INTO `lng_data` VALUES ('common', 'msg_cancel', 'en', 0x416374696f6e2063616e63656c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'msg_changes_ok', 'en', 0x546865206368616e6765732077657265204f4b);
INSERT INTO `lng_data` VALUES ('common', 'msg_clear_clipboard', 'en', 0x436c6970626f61726420636c6561726564);
INSERT INTO `lng_data` VALUES ('common', 'msg_cloned', 'en', 0x53656c6563746564206f626a65637428732920636f70696564);
INSERT INTO `lng_data` VALUES ('common', 'msg_copy_clipboard', 'en', 0x53656c6563746564206f626a6563742873292073746f72656420696e20636c6970626f6172642028416374696f6e3a20636f707929);
INSERT INTO `lng_data` VALUES ('common', 'msg_cut_clipboard', 'en', 0x53656c6563746564206f626a6563742873292073746f72656420696e20636c6970626f6172642028416374696f6e3a2063757429);
INSERT INTO `lng_data` VALUES ('common', 'msg_cut_copied', 'en', 0x53656c6563746564206f626a656374287329206d6f7665642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_role', 'en', 0x526f6c652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_roles', 'en', 0x526f6c65732064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_roles_rolts', 'en', 0x526f6c6573202620526f6c652054656d706c617465732064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_rolt', 'en', 0x526f6c652054656d706c6174652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_deleted_rolts', 'en', 0x526f6c652054656d706c6174652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_error_copy', 'en', 0x436f7079204572726f72);
INSERT INTO `lng_data` VALUES ('common', 'msg_failed', 'en', 0x536f7272792c20616374696f6e206661696c6564);
INSERT INTO `lng_data` VALUES ('common', 'msg_is_last_role', 'en', 0x596f752072656d6f76656420746865206c61737420676c6f62616c20726f6c652066726f6d2074686520666f6c6c6f77696e67207573657273);
INSERT INTO `lng_data` VALUES ('common', 'msg_link_clipboard', 'en', 0x53656c6563746564206f626a6563742873292073746f72656420696e20636c6970626f6172642028416374696f6e3a206c696e6b29);
INSERT INTO `lng_data` VALUES ('common', 'msg_linked', 'en', 0x53656c6563746564206f626a656374287329206c696e6b65642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_may_not_contain', 'en', 0x54686973206f626a656374206d6179206e6f7420636f6e7461696e206f626a65637473206f6620747970653a);
INSERT INTO `lng_data` VALUES ('common', 'msg_min_one_active_role', 'en', 0x456163682075736572206d7573742068617665206174206c65617374206f6e652061637469766520676c6f62616c20726f6c6521);
INSERT INTO `lng_data` VALUES ('common', 'msg_min_one_role', 'en', 0x456163682075736572206d7573742068617665206174206c65617374206f6e6520676c6f62616c20726f6c6521);
INSERT INTO `lng_data` VALUES ('common', 'msg_multi_language_selected', 'en', 0x596f752073656c6563746564207468652073616d65206c616e677561676520666f7220646966666572656e74207472616e736c6174696f6e7321);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_default_language', 'en', 0x4e6f2064656661756c74206c616e6775616765207370656369666965642120596f75206d75737420646566696e65206f6e65207472616e736c6174696f6e2061732064656661756c74207472616e736c6174696f6e2e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_delete_yourself', 'en', 0x596f752063616e6e6f742064656c65746520796f7572206f776e2075736572206163636f756e742e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_file', 'en', 0x596f75206469646e27742063686f6f736520612066696c65);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_language_selected', 'en', 0x4e6f207472616e736c6174696f6e206c616e6775616765207370656369666965642120596f75206d75737420646566696e652061206c616e677561676520666f722065616368207472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_assign_role_to_user', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206368616e67652075736572277320726f6c652061737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_copy', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f20637265617465206120636f7079206f662074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206372656174652074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_object1', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f20637265617465);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_object2', 'en', 0x61742074686973206c6f636174696f6e21);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_role', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2061646420726f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_rolf', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f20637265617465206120526f6c6520466f6c6465722e205468657265666f726520796f75206d6179206e6f742073746f7020696e6865726974616e6365206f6620726f6c6573206f7220616464206c6f63616c20726f6c657320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_rolt', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2061646420726f6c652074656d706c61746573);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_create_user', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f20616464207573657273);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_cut', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206375742074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_delete', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2064656c6574652074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_delete_track', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2064656c65746520747261636b696e6720646174612e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_link', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206372656174652061206c696e6b2066726f6d2074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_role', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206d6f6469667920726f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_rolt', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206d6f6469667920726f6c652074656d706c61746573);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_modify_user', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f206d6f6469667920757365722064617461);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_paste', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2070617374652074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_perm', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2065646974207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_read_lm', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f20726561642074686973206c6561726e696e67206d6f64756c652e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_read_track', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2061636365737320746865207573657220747261636b696e672e);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_undelete', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f20756e64656c6574652074686520666f6c6c6f77696e67206f626a6563742873293a);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_perm_write', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f207772697465);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_rolf_allowed1', 'en', 0x546865206f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_rolf_allowed2', 'en', 0x6973206e6f7420616c6c6f77656420746f20636f6e7461696e206120526f6c6520466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_scan_log', 'en', 0x5363616e206c6f67206e6f7420696d706c656d656e74656420796574);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_search_result', 'en', 0x4e6f20656e747269657320666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_search_string', 'en', 0x506c6561736520656e74657220796f7572207175657279);
INSERT INTO `lng_data` VALUES ('common', 'msg_no_sysadmin_sysrole_not_assignable', 'en', 0x4f6e6c7920612053797374656d2041646d696e6973747261746f72206d61792061737369676e20757365727320746f207468652053797374656d2041646d696e6973747261746f7220526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'msg_not_available_for_anon', 'en', 0x546865207061676520796f7520686176652063686f73656e206973206f6e6c792061636365737369626c6520666f722072656769737465726564207573657273);
INSERT INTO `lng_data` VALUES ('common', 'msg_not_in_itself', 'en', 0x49742773206e6f7420706f737369626c6520746f20706173746520746865206f626a65637420696e20697473656c66);
INSERT INTO `lng_data` VALUES ('common', 'msg_nothing_found', 'en', 0x6d73675f6e6f7468696e675f666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_created', 'en', 0x4f626a65637420637265617465642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_exists', 'en', 0x54686973206f626a65637420616c72656164792065786973747320696e207468697320666f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_modified', 'en', 0x4d6f64696669636174696f6e732073617665642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_obj_no_link', 'en', 0x617265206e6f7420616c6c6f77656420746f206265206c696e6b6564);
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from1', 'en', 0x5065726d697373696f6e2073657474696e67732061646f707465642066726f6d);
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from2', 'en', 0x2853657474696e67732068617665206265656e2073617665642129);
INSERT INTO `lng_data` VALUES ('common', 'msg_perm_adopted_from_itself', 'en', 0x596f752063616e6e6f742061646f7074207065726d697373696f6e2073657474696e67732066726f6d207468652063757272656e7420726f6c652f726f6c652074656d706c61746520697473656c662e);
INSERT INTO `lng_data` VALUES ('common', 'msg_removed', 'en', 0x4f626a6563742873292072656d6f7665642066726f6d2073797374656d2e);
INSERT INTO `lng_data` VALUES ('common', 'msg_role_exists1', 'en', 0x4120726f6c652f726f6c652074656d706c617465207769746820746865206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'msg_role_exists2', 'en', 0x616c7265616479206578697374732120506c656173652063686f6f736520616e6f74686572206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'msg_role_reserved_prefix', 'en', 0x546865207072656669782027696c5f2720697320726573657276656420666f72206175746f6d61746963616c6c792067656e65726174656420726f6c65732e20506c656173652063686f6f736520616e6f74686572206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'msg_roleassignment_active_changed', 'en', 0x41637469766520726f6c652061737369676e6d656e74206368616e676564);
INSERT INTO `lng_data` VALUES ('common', 'msg_roleassignment_active_changed_comment', 'en', 0x546869732073657474696e67206973206e6f7420736176656420746f20746865207573657227732070726f66696c6521204966207468652075736572206c6f677320696e20616761696e2c20616c6c2061637469766520726f6c652061737369676e6d656e747320726573657420746f2074686569722073617665642076616c7565732e);
INSERT INTO `lng_data` VALUES ('common', 'msg_roleassignment_changed', 'en', 0x526f6c652061737369676e6d656e74206368616e676564);
INSERT INTO `lng_data` VALUES ('common', 'msg_sysrole_not_deletable', 'en', 0x5468652073797374656d20726f6c652063616e6e6f742062652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'msg_sysrole_not_editable', 'en', 0x546865207065726d697373696f6e2073657474696e6773206f66207468652073797374656d20726f6c65206d6179206e6f74206265206368616e6765642e205468652073797374656d20726f6c65206772616e747320616c6c2061737369676e656420757365727320756e6c696d697465642061636365737320746f20616c6c206f626a6563747320262066756e6374696f6e732e);
INSERT INTO `lng_data` VALUES ('common', 'msg_trash_empty', 'en', 0x546865726520617265206e6f2064656c65746564206f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'msg_undeleted', 'en', 0x4f626a65637428732920756e64656c657465642e);
INSERT INTO `lng_data` VALUES ('common', 'msg_user_last_role1', 'en', 0x54686520666f6c6c6f77696e67207573657273206172652061737369676e656420746f207468697320726f6c65206f6e6c793a);
INSERT INTO `lng_data` VALUES ('common', 'msg_user_last_role2', 'en', 0x506c656173652064656c65746520746865207573657273206f722061737369676e207468656d20746f20616e6f7468657220726f6c6520696e206f7264657220746f2064656c657465207468697320726f6c652e);
INSERT INTO `lng_data` VALUES ('common', 'msg_userassignment_changed', 'en', 0x557365722061737369676e6d656e74206368616e676564);
INSERT INTO `lng_data` VALUES ('common', 'multimedia', 'en', 0x4d756c74696d65646961);
INSERT INTO `lng_data` VALUES ('common', 'my_bms', 'en', 0x4d7920426f6f6b6d61726b73);
INSERT INTO `lng_data` VALUES ('common', 'my_frms', 'en', 0x4d7920466f72756d73);
INSERT INTO `lng_data` VALUES ('common', 'my_grps', 'en', 0x4d792047726f757073);
INSERT INTO `lng_data` VALUES ('common', 'my_los', 'en', 0x4d79204c6561726e696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'my_tsts', 'en', 0x4d79205465737473);
INSERT INTO `lng_data` VALUES ('common', 'name', 'en', 0x4e616d65);
INSERT INTO `lng_data` VALUES ('common', 'new', 'en', 0x4e6577);
INSERT INTO `lng_data` VALUES ('common', 'new_appointment', 'en', 0x4e6577204170706f696e746d656e74);
INSERT INTO `lng_data` VALUES ('common', 'new_folder', 'en', 0x4e657720466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'new_group', 'en', 0x4e65772047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'new_language', 'en', 0x4e6577204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'new_mail', 'en', 0x4e6577206d61696c21);
INSERT INTO `lng_data` VALUES ('common', 'news', 'en', 0x4e657773);
INSERT INTO `lng_data` VALUES ('common', 'next', 'en', 0x6e657874);
INSERT INTO `lng_data` VALUES ('common', 'nickname', 'en', 0x4e69636b6e616d65);
INSERT INTO `lng_data` VALUES ('common', 'no', 'en', 0x4e6f);
INSERT INTO `lng_data` VALUES ('common', 'no_access_link_object', 'en', 0x596f7520617265206e6f7420616c6c6f77656420746f206c696e6b2074686973206f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'no_bm_in_personal_list', 'en', 0x4e6f20626f6f6b6d61726b7320646566696e65642e);
INSERT INTO `lng_data` VALUES ('common', 'no_chat_in_personal_list', 'en', 0x4e6f20636861747320696e20706572736f6e616c206c697374);
INSERT INTO `lng_data` VALUES ('common', 'no_checkbox', 'en', 0x4e6f20636865636b626f7820636865636b656421);
INSERT INTO `lng_data` VALUES ('common', 'no_date', 'en', 0x4e6f2064617465);
INSERT INTO `lng_data` VALUES ('common', 'no_description', 'en', 0x4e6f206465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'no_frm_in_personal_list', 'en', 0x4e6f20666f72756d7320696e20706572736f6e616c206c6973742e);
INSERT INTO `lng_data` VALUES ('common', 'no_global_role_left', 'en', 0x457665727920757365722068617320746f2062652061737369676e656420746f206f6e6520726f6c652e);
INSERT INTO `lng_data` VALUES ('common', 'no_grp_in_personal_list', 'en', 0x4e6f2067726f75707320696e20706572736f6e616c206c6973742e);
INSERT INTO `lng_data` VALUES ('common', 'no_import_available', 'en', 0x496d706f7274206e6f7420617661696c61626c6520666f722074797065);
INSERT INTO `lng_data` VALUES ('common', 'no_limit', 'en', 0x4e6f206c696d6974);
INSERT INTO `lng_data` VALUES ('common', 'no_lo_in_personal_list', 'en', 0x4e6f206c6561726e696e67206f626a6563747320696e20706572736f6e616c206c6973742e);
INSERT INTO `lng_data` VALUES ('common', 'no_local_users', 'en', 0x546865726520617265206e6f206c6f63616c2075736572);
INSERT INTO `lng_data` VALUES ('common', 'no_objects', 'en', 0x4e6f206f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'no_permission_to_join', 'en', 0x596f7520617265206e6f7420616c6c6f77656420746f206a6f696e20746869732067726f757021);
INSERT INTO `lng_data` VALUES ('common', 'no_roles_user_can_be_assigned_to', 'en', 0x546865726520617265206e6f20676c6f62616c20726f6c65732074686520757365722063616e2062652061737369676e656420746f2e205468657265666f726520796f7520617265206e6f7420616c6c6f77656420746f206164642075736572732e);
INSERT INTO `lng_data` VALUES ('common', 'no_title', 'en', 0x4e6f205469746c65);
INSERT INTO `lng_data` VALUES ('common', 'no_tst_in_personal_list', 'en', 0x4e6f20746573747320696e20706572736f6e616c206c6973742e);
INSERT INTO `lng_data` VALUES ('common', 'no_users_applied', 'en', 0x506c656173652073656c656374206120757365722e);
INSERT INTO `lng_data` VALUES ('common', 'no_users_selected', 'en', 0x506c656173652073656c656374206f6e6520757365722e);
INSERT INTO `lng_data` VALUES ('common', 'no_xml_file_found_in_zip', 'en', 0x584d4c2066696c652077697468696e207a69702066696c65206e6f7420666f756e643a);
INSERT INTO `lng_data` VALUES ('common', 'no_zip_file', 'en', 0x4e6f207a69702066696c6520666f756e642e);
INSERT INTO `lng_data` VALUES ('common', 'non_internal_local_roles_only', 'en', 0x4c6f63616c20726f6c657320286f6e6c792075736572646566696e656429);
INSERT INTO `lng_data` VALUES ('common', 'none', 'en', 0x4e6f6e65);
INSERT INTO `lng_data` VALUES ('common', 'normal', 'en', 0x4e6f726d616c);
INSERT INTO `lng_data` VALUES ('common', 'not_implemented_yet', 'en', 0x4e6f7420696d706c656d656e74656420796574);
INSERT INTO `lng_data` VALUES ('common', 'not_installed', 'en', 0x4e6f7420496e7374616c6c6564);
INSERT INTO `lng_data` VALUES ('common', 'not_logged_in', 'en', 0x596f757220617265206e6f74206c6f6767656420696e);
INSERT INTO `lng_data` VALUES ('common', 'number_of_accesses', 'en', 0x4e756d626572206f66206163636573736573);
INSERT INTO `lng_data` VALUES ('common', 'number_of_records', 'en', 0x4e756d626572206f66207265636f726473);
INSERT INTO `lng_data` VALUES ('common', 'obj', 'en', 0x4f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'obj_adm', 'en', 0x41646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_adm_desc', 'en', 0x4d61696e2073797374656d2073657474696e677320666f6c64657220636f6e7461696e696e6720616c6c2070616e656c7320746f2061646d696e6973747261746520796f757220494c49415320696e7374616c6c6174696f6e2e);
INSERT INTO `lng_data` VALUES ('common', 'obj_alm', 'en', 0x4c6561726e696e67204d6f64756c652041494343);
INSERT INTO `lng_data` VALUES ('common', 'obj_auth', 'en', 0x41757468656e7469636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_auth_desc', 'en', 0x436f6e66696775726520796f75722061757468656e7469636174696f6e206d6f646520286c6f63616c2c204c4441502c202e2e2e29);
INSERT INTO `lng_data` VALUES ('common', 'obj_cat', 'en', 0x43617465676f7279);
INSERT INTO `lng_data` VALUES ('common', 'obj_cen', 'en', 0x43656e747261204576656e74);
INSERT INTO `lng_data` VALUES ('common', 'obj_chac', 'en', 0x436861742073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj_chac_desc', 'en', 0x436f6e66696775726520796f757220636861742073657276657220686572652e20456e61626c652f64697361626c652063686174732e);
INSERT INTO `lng_data` VALUES ('common', 'obj_chat', 'en', 0x43686174);
INSERT INTO `lng_data` VALUES ('common', 'obj_crs', 'en', 0x436f75727365);
INSERT INTO `lng_data` VALUES ('common', 'obj_dbk', 'en', 0x446967696c696220426f6f6b);
INSERT INTO `lng_data` VALUES ('common', 'obj_exc', 'en', 0x4578657263697365);
INSERT INTO `lng_data` VALUES ('common', 'obj_file', 'en', 0x46696c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_fold', 'en', 0x466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'obj_frm', 'en', 0x466f72756d);
INSERT INTO `lng_data` VALUES ('common', 'obj_glo', 'en', 0x476c6f7373617279);
INSERT INTO `lng_data` VALUES ('common', 'obj_grp', 'en', 0x47726f7570);
INSERT INTO `lng_data` VALUES ('common', 'obj_hlm', 'en', 0x4c6561726e696e67204d6f64756c652048414350);
INSERT INTO `lng_data` VALUES ('common', 'obj_htlm', 'en', 0x4c6561726e696e67204d6f64756c652048544d4c);
INSERT INTO `lng_data` VALUES ('common', 'obj_ldap', 'en', 0x4c4441502053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj_ldap_desc', 'en', 0x436f6e66696775726520676c6f62616c204c4441502053657474696e677320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'obj_lm', 'en', 0x4c6561726e696e67204d6f64756c6520494c494153);
INSERT INTO `lng_data` VALUES ('common', 'obj_lng', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'obj_lngf', 'en', 0x4c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'obj_lngf_desc', 'en', 0x4d616e61676520796f75722073797374656d206c616e67756167657320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'obj_lo', 'en', 0x4c6561726e696e674f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'obj_mail', 'en', 0x4d61696c2053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj_mail_desc', 'en', 0x436f6e66696775726520676c6f62616c206d61696c2073657474696e677320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'obj_mep', 'en', 0x4d6564696120506f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'obj_mob', 'en', 0x4d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'obj_not_found', 'en', 0x4f626a656374204e6f7420466f756e64);
INSERT INTO `lng_data` VALUES ('common', 'obj_note', 'en', 0x4e6f7465);
INSERT INTO `lng_data` VALUES ('common', 'obj_notf', 'en', 0x4e6f74652041646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_objf', 'en', 0x4f626a65637420646566696e6974696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'obj_objf_desc', 'en', 0x4d616e61676520494c494153206f626a65637420747970657320616e64206f626a656374207065726d697373696f6e732e20286f6e6c7920666f7220657870657274732129);
INSERT INTO `lng_data` VALUES ('common', 'obj_owner', 'en', 0x54686973204f626a656374206973206f776e6564206279);
INSERT INTO `lng_data` VALUES ('common', 'obj_pays', 'en', 0x5061796d656e742073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj_pays_desc', 'en', 0x436f6e666967757265207061796d656e742073657474696e677320616e642076656e646f7273);
INSERT INTO `lng_data` VALUES ('common', 'obj_pg', 'en', 0x50616765);
INSERT INTO `lng_data` VALUES ('common', 'obj_qpl', 'en', 0x5175657374696f6e20506f6f6c2054657374);
INSERT INTO `lng_data` VALUES ('common', 'obj_recf', 'en', 0x526573746f726564204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'obj_recf_desc', 'en', 0x436f6e7461696e7320726573746f726564204f626a656374732066726f6d2053797374656d20436865636b2e);
INSERT INTO `lng_data` VALUES ('common', 'obj_role', 'en', 0x526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf', 'en', 0x526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf_desc', 'en', 0x4d616e61676520796f757220726f6c657320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf_local', 'en', 0x4c6f63616c20526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolf_local_desc', 'en', 0x436f6e7461696e73206c6f63616c20726f6c6573206f66206f626a656374206e6f2e);
INSERT INTO `lng_data` VALUES ('common', 'obj_rolt', 'en', 0x526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'obj_root', 'en', 0x494c49415320726f6f74206e6f6465);
INSERT INTO `lng_data` VALUES ('common', 'obj_sahs', 'en', 0x4c6561726e696e67204d6f64756c652053434f524d2f41494343);
INSERT INTO `lng_data` VALUES ('common', 'obj_spl', 'en', 0x5175657374696f6e20506f6f6c20537572766579);
INSERT INTO `lng_data` VALUES ('common', 'obj_st', 'en', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('common', 'obj_svy', 'en', 0x537572766579);
INSERT INTO `lng_data` VALUES ('common', 'obj_tax', 'en', 0x5461786f6e6f6d79);
INSERT INTO `lng_data` VALUES ('common', 'obj_taxf', 'en', 0x5461786f6e6f6d696573);
INSERT INTO `lng_data` VALUES ('common', 'obj_taxf_desc', 'en', 0x466f6c64657220666f72207461786f6e6f6d696573);
INSERT INTO `lng_data` VALUES ('common', 'obj_trac', 'en', 0x5573657220547261636b696e67);
INSERT INTO `lng_data` VALUES ('common', 'obj_trac_desc', 'en', 0x557365722061636365737320747261636b696e672064617461);
INSERT INTO `lng_data` VALUES ('common', 'obj_tst', 'en', 0x54657374);
INSERT INTO `lng_data` VALUES ('common', 'obj_typ', 'en', 0x4f626a656374205479706520446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'obj_type', 'en', 0x4f626a6563742054797065);
INSERT INTO `lng_data` VALUES ('common', 'obj_uset', 'en', 0x557365722053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'obj_usr', 'en', 0x55736572);
INSERT INTO `lng_data` VALUES ('common', 'obj_usrf', 'en', 0x55736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'obj_usrf_desc', 'en', 0x4d616e6167652075736572206163636f756e747320686572652e);
INSERT INTO `lng_data` VALUES ('common', 'object', 'en', 0x4f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'object_added', 'en', 0x4f626a656374206164646564);
INSERT INTO `lng_data` VALUES ('common', 'objects', 'en', 0x4f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'objf', 'en', 0x4f626a65637420646566696e6974696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'objf_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'objf_read', 'en', 0x526561642061636365737320746f204f626a65637420646566696e6974696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'objf_visible', 'en', 0x4f626a65637420646566696e6974696f6e73206172652076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'objf_write', 'en', 0x45646974204f626a65637420646566696e6974696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'objs_alm', 'en', 0x41494343204c6561726e696e67204d6f64756c6573);
INSERT INTO `lng_data` VALUES ('common', 'objs_cat', 'en', 0x43617465676f72696573);
INSERT INTO `lng_data` VALUES ('common', 'objs_chat', 'en', 0x4368617473);
INSERT INTO `lng_data` VALUES ('common', 'objs_confirm', 'en', 0x436f6e6669726d20416374696f6e);
INSERT INTO `lng_data` VALUES ('common', 'objs_crs', 'en', 0x436f7572736573);
INSERT INTO `lng_data` VALUES ('common', 'objs_dbk', 'en', 0x446967696c696220426f6f6b73);
INSERT INTO `lng_data` VALUES ('common', 'objs_delete', 'en', 0x44656c657465206f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'objs_exc', 'en', 0x457865726369736573);
INSERT INTO `lng_data` VALUES ('common', 'objs_file', 'en', 0x46696c6573);
INSERT INTO `lng_data` VALUES ('common', 'objs_fold', 'en', 0x466f6c64657273);
INSERT INTO `lng_data` VALUES ('common', 'objs_frm', 'en', 0x466f72756d73);
INSERT INTO `lng_data` VALUES ('common', 'objs_glo', 'en', 0x476c6f73736172696573);
INSERT INTO `lng_data` VALUES ('common', 'objs_grp', 'en', 0x47726f757073);
INSERT INTO `lng_data` VALUES ('common', 'objs_hlm', 'en', 0x48414350204c6561726e696e67204d6f64756c6573);
INSERT INTO `lng_data` VALUES ('common', 'objs_htlm', 'en', 0x48544d4c204c6561726e696e67204d6f64756c6573);
INSERT INTO `lng_data` VALUES ('common', 'objs_lm', 'en', 0x494c494153204c6561726e696e67204d6f64756c6573);
INSERT INTO `lng_data` VALUES ('common', 'objs_lng', 'en', 0x4c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'objs_lo', 'en', 0x4c6561726e696e67204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'objs_mep', 'en', 0x4d6564696120506f6f6c73);
INSERT INTO `lng_data` VALUES ('common', 'objs_mob', 'en', 0x4d65646961204f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'objs_note', 'en', 0x4e6f746573);
INSERT INTO `lng_data` VALUES ('common', 'objs_pg', 'en', 0x5061676573);
INSERT INTO `lng_data` VALUES ('common', 'objs_qpl', 'en', 0x5175657374696f6e20506f6f6c732054657374);
INSERT INTO `lng_data` VALUES ('common', 'objs_role', 'en', 0x526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'objs_rolt', 'en', 0x526f6c652054656d706c61746573);
INSERT INTO `lng_data` VALUES ('common', 'objs_sahs', 'en', 0x53434f524d2f41494343204c6561726e696e67204d6f64756c6573);
INSERT INTO `lng_data` VALUES ('common', 'objs_spl', 'en', 0x5175657374696f6e20506f6f6c7320537572766579);
INSERT INTO `lng_data` VALUES ('common', 'objs_st', 'en', 0x4368617074657273);
INSERT INTO `lng_data` VALUES ('common', 'objs_svy', 'en', 0x53757276657973);
INSERT INTO `lng_data` VALUES ('common', 'objs_tst', 'en', 0x5465737473);
INSERT INTO `lng_data` VALUES ('common', 'objs_type', 'en', 0x4f626a656374205479706573);
INSERT INTO `lng_data` VALUES ('common', 'objs_usr', 'en', 0x5573657273);
INSERT INTO `lng_data` VALUES ('common', 'of', 'en', 0x4f66);
INSERT INTO `lng_data` VALUES ('common', 'offline_version', 'en', 0x4f66666c696e652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'ok', 'en', 0x4f4b);
INSERT INTO `lng_data` VALUES ('common', 'old', 'en', 0x4f6c64);
INSERT INTO `lng_data` VALUES ('common', 'online_chapter', 'en', 0x4f6e6c696e652043686170746572);
INSERT INTO `lng_data` VALUES ('common', 'online_version', 'en', 0x4f6e6c696e652056657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'open_views_inside_frameset', 'en', 0x4f70656e20766965777320696e73696465206672616d65736574);
INSERT INTO `lng_data` VALUES ('common', 'operation', 'en', 0x4f7065726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'optimize', 'en', 0x4f7074696d697a65);
INSERT INTO `lng_data` VALUES ('common', 'options', 'en', 0x4f7074696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'options_for_subobjects', 'en', 0x4f7074696f6e7320666f72207375626f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'order_by', 'en', 0x4f72646572206279);
INSERT INTO `lng_data` VALUES ('common', 'other', 'en', 0x4f74686572);
INSERT INTO `lng_data` VALUES ('common', 'overview', 'en', 0x4f76657276696577);
INSERT INTO `lng_data` VALUES ('common', 'owner', 'en', 0x4f776e6572);
INSERT INTO `lng_data` VALUES ('common', 'page', 'en', 0x50616765);
INSERT INTO `lng_data` VALUES ('common', 'page_edit', 'en', 0x456469742050616765);
INSERT INTO `lng_data` VALUES ('common', 'pages', 'en', 0x5061676573);
INSERT INTO `lng_data` VALUES ('common', 'parameter', 'en', 0x506172616d65746572);
INSERT INTO `lng_data` VALUES ('common', 'parse', 'en', 0x5061727365);
INSERT INTO `lng_data` VALUES ('common', 'passwd', 'en', 0x50617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'passwd_invalid', 'en', 0x546865206e65772070617373776f726420697320696e76616c696421204f6e6c792074686520666f6c6c6f77696e6720636861726163746572732061726520616c6c6f77656420286d696e696d756d20362063686172616374657273293a20412d5a20612d7a20302d39205f2e2d2b2a402124257e);
INSERT INTO `lng_data` VALUES ('common', 'passwd_not_match', 'en', 0x596f757220656e747269657320666f7220746865206e65772070617373776f726420646f6e2774206d617463682120506c656173652072652d656e74657220796f7572206e65772070617373776f72642e);
INSERT INTO `lng_data` VALUES ('common', 'passwd_wrong', 'en', 0x5468652070617373776f726420796f7520656e74657265642069732077726f6e6721);
INSERT INTO `lng_data` VALUES ('common', 'password', 'en', 0x50617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'paste', 'en', 0x5061737465);
INSERT INTO `lng_data` VALUES ('common', 'pasteChapter', 'en', 0x5061737465);
INSERT INTO `lng_data` VALUES ('common', 'pastePage', 'en', 0x5061737465);
INSERT INTO `lng_data` VALUES ('common', 'path', 'en', 0x50617468);
INSERT INTO `lng_data` VALUES ('common', 'path_not_set', 'en', 0x50617468206e6f7420736574);
INSERT INTO `lng_data` VALUES ('common', 'path_to_babylon', 'en', 0x5061746820746f20426162796c6f6e);
INSERT INTO `lng_data` VALUES ('common', 'path_to_convert', 'en', 0x5061746820746f20436f6e76657274);
INSERT INTO `lng_data` VALUES ('common', 'path_to_htmldoc', 'en', 0x5061746820746f2048544d4c646f63);
INSERT INTO `lng_data` VALUES ('common', 'path_to_java', 'en', 0x5061746820746f204a617661);
INSERT INTO `lng_data` VALUES ('common', 'path_to_unzip', 'en', 0x5061746820746f20556e7a6970);
INSERT INTO `lng_data` VALUES ('common', 'path_to_zip', 'en', 0x5061746820746f205a6970);
INSERT INTO `lng_data` VALUES ('common', 'pathes', 'en', 0x5061746873);
INSERT INTO `lng_data` VALUES ('common', 'pay_methods', 'en', 0x506179206d6574686f6473);
INSERT INTO `lng_data` VALUES ('common', 'payment_system', 'en', 0x5061796d656e742053797374656d);
INSERT INTO `lng_data` VALUES ('common', 'pays_edit', 'en', 0x45646974207061796d656e742073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'pays_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'pays_read', 'en', 0x416363657373207061796d656e742073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'pays_visible', 'en', 0x5061796d656e742073657474696e6773206172652076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'pays_write', 'en', 0x45646974207061796d656e742073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'per_object', 'en', 0x41636365737320706572206f626a656374);
INSERT INTO `lng_data` VALUES ('common', 'perm_settings', 'en', 0x5065726d697373696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'permission', 'en', 0x5065726d697373696f6e);
INSERT INTO `lng_data` VALUES ('common', 'permission_denied', 'en', 0x5065726d697373696f6e2044656e696564);
INSERT INTO `lng_data` VALUES ('common', 'permission_settings', 'en', 0x5065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'person_title', 'en', 0x5469746c65);
INSERT INTO `lng_data` VALUES ('common', 'personal_data', 'en', 0x506572736f6e616c20696e666f726d6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'personal_desktop', 'en', 0x506572736f6e616c204465736b746f70);
INSERT INTO `lng_data` VALUES ('common', 'personal_picture', 'en', 0x506572736f6e616c2050696374757265);
INSERT INTO `lng_data` VALUES ('common', 'personal_profile', 'en', 0x506572736f6e616c2050726f66696c65);
INSERT INTO `lng_data` VALUES ('common', 'persons', 'en', 0x506572736f6e73);
INSERT INTO `lng_data` VALUES ('common', 'pg_a', 'en', 0x612070616765);
INSERT INTO `lng_data` VALUES ('common', 'pg_add', 'en', 0x4164642070616765);
INSERT INTO `lng_data` VALUES ('common', 'pg_added', 'en', 0x50616765206164646564);
INSERT INTO `lng_data` VALUES ('common', 'pg_edit', 'en', 0x456469742070616765);
INSERT INTO `lng_data` VALUES ('common', 'pg_new', 'en', 0x4e65772070616765);
INSERT INTO `lng_data` VALUES ('common', 'phone', 'en', 0x50686f6e65);
INSERT INTO `lng_data` VALUES ('common', 'phone_home', 'en', 0x50686f6e652c20486f6d65);
INSERT INTO `lng_data` VALUES ('common', 'phone_mobile', 'en', 0x50686f6e652c204d6f62696c65);
INSERT INTO `lng_data` VALUES ('common', 'phone_office', 'en', 0x50686f6e652c204f6666696365);
INSERT INTO `lng_data` VALUES ('common', 'phrase', 'en', 0x506872617365);
INSERT INTO `lng_data` VALUES ('common', 'please_enter_target', 'en', 0x506c6561736520656e746572206120746172676574);
INSERT INTO `lng_data` VALUES ('common', 'please_enter_title', 'en', 0x506c6561736520656e7465722061207469746c65);
INSERT INTO `lng_data` VALUES ('common', 'port', 'en', 0x506f7274);
INSERT INTO `lng_data` VALUES ('common', 'position', 'en', 0x506f736974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'preconditions', 'en', 0x507265636f6e646974696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'presentation_options', 'en', 0x50726573656e746174696f6e204f7074696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'previous', 'en', 0x70726576696f7573);
INSERT INTO `lng_data` VALUES ('common', 'print', 'en', 0x5072696e74);
INSERT INTO `lng_data` VALUES ('common', 'profile_changed', 'en', 0x596f75722070726f66696c6520686173206368616e676564);
INSERT INTO `lng_data` VALUES ('common', 'profile_of', 'en', 0x50726f66696c65206f66);
INSERT INTO `lng_data` VALUES ('common', 'properties', 'en', 0x50726f70657274696573);
INSERT INTO `lng_data` VALUES ('common', 'pub_section', 'en', 0x5075626c69632053656374696f6e);
INSERT INTO `lng_data` VALUES ('common', 'public_profile', 'en', 0x5075626c69632050726f66696c65);
INSERT INTO `lng_data` VALUES ('common', 'publication', 'en', 0x5075626c69636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'publication_date', 'en', 0x5075626c69636174696f6e2044617465);
INSERT INTO `lng_data` VALUES ('common', 'published', 'en', 0x5075626c6973686564);
INSERT INTO `lng_data` VALUES ('common', 'publishing_organisation', 'en', 0x5075626c697368696e67204f7267616e697a6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'qpl', 'en', 0x5175657374696f6e20506f6f6c2054657374);
INSERT INTO `lng_data` VALUES ('common', 'qpl_add', 'en', 0x416464207175657374696f6e20706f6f6c20666f722074657374);
INSERT INTO `lng_data` VALUES ('common', 'qpl_delete', 'en', 0x44656c657465205175657374696f6e20506f6f6c20666f722054657374);
INSERT INTO `lng_data` VALUES ('common', 'qpl_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'qpl_new', 'en', 0x4e6577207175657374696f6e20706f6f6c20666f722074657374);
INSERT INTO `lng_data` VALUES ('common', 'qpl_read', 'en', 0x526561642061636365737320746f205175657374696f6e20506f6f6c20666f722054657374);
INSERT INTO `lng_data` VALUES ('common', 'qpl_visible', 'en', 0x5175657374696f6e20506f6f6c20666f7220546573742069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'qpl_write', 'en', 0x45646974205175657374696f6e20506f6f6c20666f722054657374);
INSERT INTO `lng_data` VALUES ('common', 'query_data', 'en', 0x51756572792064617461);
INSERT INTO `lng_data` VALUES ('common', 'question', 'en', 0x5175657374696f6e);
INSERT INTO `lng_data` VALUES ('common', 'question_pools', 'en', 0x5175657374696f6e20706f6f6c73);
INSERT INTO `lng_data` VALUES ('common', 'quit', 'en', 0x51756974);
INSERT INTO `lng_data` VALUES ('common', 'quote', 'en', 0x51756f7465);
INSERT INTO `lng_data` VALUES ('common', 'read', 'en', 0x52656164);
INSERT INTO `lng_data` VALUES ('common', 'recf_edit', 'en', 0x45646974205265636f76657279466f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'recipient', 'en', 0x526563697069656e74);
INSERT INTO `lng_data` VALUES ('common', 'referral_comment', 'en', 0x486f772064696420796f7520686561722061626f757420494c4941533f);
INSERT INTO `lng_data` VALUES ('common', 'refresh', 'en', 0x52656672657368);
INSERT INTO `lng_data` VALUES ('common', 'refresh_languages', 'en', 0x52656672657368204c616e677561676573);
INSERT INTO `lng_data` VALUES ('common', 'refresh_list', 'en', 0x52656672657368204c697374);
INSERT INTO `lng_data` VALUES ('common', 'refuse', 'en', 0x526566757365);
INSERT INTO `lng_data` VALUES ('common', 'register', 'en', 0x5265676973746572);
INSERT INTO `lng_data` VALUES ('common', 'register_info', 'en', 0x506c656173652066696c6c206f75742074686520666f726d20746f20726567697374657220284669656c6473206d61726b6564207769746820616e20617374657269736b2061726520726571756972656420696e666f726d6174696f6e292e);
INSERT INTO `lng_data` VALUES ('common', 'registered_since', 'en', 0x526567697374657265642073696e6365);
INSERT INTO `lng_data` VALUES ('common', 'registered_user', 'en', 0x726567697374657265642055736572);
INSERT INTO `lng_data` VALUES ('common', 'registered_users', 'en', 0x72656769737465726564205573657273);
INSERT INTO `lng_data` VALUES ('common', 'registration', 'en', 0x4e6577206163636f756e7420726567697374726174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'registration_disabled', 'en', 0x4f6e6c7920617661696c61626c65207768656e207573696e67206c6f63616c2061757468656e7469636174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'registration_expired', 'en', 0x526567697374726174696f6e20706572696f6420657870697265642e2e2e);
INSERT INTO `lng_data` VALUES ('common', 'remove', 'en', 0x52656d6f7665);
INSERT INTO `lng_data` VALUES ('common', 'remove_personal_picture', 'en', 0x52656d6f7665);
INSERT INTO `lng_data` VALUES ('common', 'remove_translation', 'en', 0x52656d6f7665207472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'rename', 'en', 0x52656e616d65);
INSERT INTO `lng_data` VALUES ('common', 'rename_file', 'en', 0x52656e616d652046696c65);
INSERT INTO `lng_data` VALUES ('common', 'reply', 'en', 0x5265706c79);
INSERT INTO `lng_data` VALUES ('common', 'repository', 'en', 0x5265706f7369746f7279);
INSERT INTO `lng_data` VALUES ('common', 'require_city', 'en', 0x526571756972652063697479);
INSERT INTO `lng_data` VALUES ('common', 'require_country', 'en', 0x5265717569726520636f756e747279);
INSERT INTO `lng_data` VALUES ('common', 'require_default_role', 'en', 0x5265717569726520726f6c65);
INSERT INTO `lng_data` VALUES ('common', 'require_department', 'en', 0x52657175697265206465706172746d656e74);
INSERT INTO `lng_data` VALUES ('common', 'require_email', 'en', 0x5265717569726520656d61696c);
INSERT INTO `lng_data` VALUES ('common', 'require_fax', 'en', 0x5265717569726520666178);
INSERT INTO `lng_data` VALUES ('common', 'require_firstname', 'en', 0x52657175697265206669727374206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'require_gender', 'en', 0x526571756972652067656e646572);
INSERT INTO `lng_data` VALUES ('common', 'require_hobby', 'en', 0x5265717569726520686f626279);
INSERT INTO `lng_data` VALUES ('common', 'require_institution', 'en', 0x5265717569726520696e737469747574696f6e);
INSERT INTO `lng_data` VALUES ('common', 'require_lastname', 'en', 0x52657175697265206c617374206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'require_login', 'en', 0x52657175697265206c6f67696e);
INSERT INTO `lng_data` VALUES ('common', 'require_mandatory', 'en', 0x6d616e6461746f7279);
INSERT INTO `lng_data` VALUES ('common', 'require_passwd', 'en', 0x526571756972652070617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'require_passwd2', 'en', 0x52657175697265207265747970652070617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'require_phone_home', 'en', 0x5265717569726520686f6d652070686f6e65);
INSERT INTO `lng_data` VALUES ('common', 'require_phone_mobile', 'en', 0x52657175697265206d6f62696c652070686f6e65);
INSERT INTO `lng_data` VALUES ('common', 'require_phone_office', 'en', 0x52657175697265206f66666963652070686f6e65);
INSERT INTO `lng_data` VALUES ('common', 'require_referral_comment', 'en', 0x5265717569726520726566657272616c20636f6d6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'require_street', 'en', 0x5265717569726520737472656574);
INSERT INTO `lng_data` VALUES ('common', 'require_zipcode', 'en', 0x52657175697265207a697020636f6465);
INSERT INTO `lng_data` VALUES ('common', 'required_field', 'en', 0x5265717569726564);
INSERT INTO `lng_data` VALUES ('common', 'reset', 'en', 0x5265736574);
INSERT INTO `lng_data` VALUES ('common', 'resources', 'en', 0x5265736f7572636573);
INSERT INTO `lng_data` VALUES ('common', 'retype_password', 'en', 0x5265747970652050617373776f7264);
INSERT INTO `lng_data` VALUES ('common', 'right', 'en', 0x5269676874);
INSERT INTO `lng_data` VALUES ('common', 'rights', 'en', 0x526967687473);
INSERT INTO `lng_data` VALUES ('common', 'role', 'en', 0x526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_a', 'en', 0x6120526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_add', 'en', 0x41646420526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_add_local', 'en', 0x416464206c6f63616c20526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_add_user', 'en', 0x416464205573657228732920746f20726f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_added', 'en', 0x526f6c65206164646564);
INSERT INTO `lng_data` VALUES ('common', 'role_assignment', 'en', 0x526f6c652041737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'role_assignment_updated', 'en', 0x526f6c652061737369676e6d656e7420686173206265656e20757064617465642e);
INSERT INTO `lng_data` VALUES ('common', 'role_count_users', 'en', 0x4e756d626572206f66207573657273);
INSERT INTO `lng_data` VALUES ('common', 'role_deleted', 'en', 0x526f6c652064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'role_edit', 'en', 0x4564697420526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_header_edit_users', 'en', 0x4368616e6765207573657261737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'role_list_users', 'en', 0x4c697374207573657273);
INSERT INTO `lng_data` VALUES ('common', 'role_new', 'en', 0x4e657720526f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_new_search', 'en', 0x4e657720736561726368);
INSERT INTO `lng_data` VALUES ('common', 'role_no_groups_selected', 'en', 0x506c656173652073656c65637420612067726f7570);
INSERT INTO `lng_data` VALUES ('common', 'role_no_results_found', 'en', 0x4e6f20726573756c747320666f756e64);
INSERT INTO `lng_data` VALUES ('common', 'role_no_roles_selected', 'en', 0x506c656173652073656c656374206120726f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_search_enter_search_string', 'en', 0x506c6561736520656e74657220612073656172636820737472696e67);
INSERT INTO `lng_data` VALUES ('common', 'role_search_users', 'en', 0x5573657220736561726368);
INSERT INTO `lng_data` VALUES ('common', 'role_templates_only', 'en', 0x526f6c652074656d706c61746573206f6e6c79);
INSERT INTO `lng_data` VALUES ('common', 'role_user_deassign', 'en', 0x446561737369676e20757365722066726f6d20726f6c65);
INSERT INTO `lng_data` VALUES ('common', 'role_user_edit', 'en', 0x45646974206163636f756e742064617461);
INSERT INTO `lng_data` VALUES ('common', 'role_user_send_mail', 'en', 0x53656e6420757365722061206d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'roles', 'en', 0x526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'roles_of_import_global', 'en', 0x476c6f62616c20726f6c6573206f6620696d706f72742066696c65);
INSERT INTO `lng_data` VALUES ('common', 'roles_of_import_local', 'en', 0x4c6f63616c20726f6c6573206f6620696d706f72742066696c65);
INSERT INTO `lng_data` VALUES ('common', 'rolf', 'en', 0x526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'rolf_added', 'en', 0x526f6c6520466f6c646572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'rolf_create_role', 'en', 0x437265617465206e657720526f6c6520646566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'rolf_create_rolt', 'en', 0x437265617465206e657720526f6c6520646566696e6974696f6e2074656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'rolf_delete', 'en', 0x44656c65746520526f6c65732f526f6c652074656d706c61746573);
INSERT INTO `lng_data` VALUES ('common', 'rolf_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'rolf_edit_userassignment', 'en', 0x4368616e676520757365722061737369676e6d656e74206f6620526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'rolf_read', 'en', 0x526561642061636365737320746f20526f6c65732f526f6c652074656d706c61746573);
INSERT INTO `lng_data` VALUES ('common', 'rolf_visible', 'en', 0x526f6c65732f526f6c652074656d706c61746573206172652076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'rolf_write', 'en', 0x456469742064656661756c74207065726d697373696f6e2073657474696e6773206f6620526f6c65732f526f6c652074656d706c61746573);
INSERT INTO `lng_data` VALUES ('common', 'rolt', 'en', 0x526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'rolt_a', 'en', 0x6120526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'rolt_add', 'en', 0x41646420526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'rolt_added', 'en', 0x526f6c652074656d706c617465206164646564);
INSERT INTO `lng_data` VALUES ('common', 'rolt_edit', 'en', 0x4564697420526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'rolt_new', 'en', 0x4e657720526f6c652054656d706c617465);
INSERT INTO `lng_data` VALUES ('common', 'root_create_cat', 'en', 0x55736572206d6179206372656174652043617465676f72696573);
INSERT INTO `lng_data` VALUES ('common', 'root_edit', 'en', 0x456469742073797374656d20726f6f74206e6f6465);
INSERT INTO `lng_data` VALUES ('common', 'root_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'root_read', 'en', 0x526561642061636365737320746f20494c4941532073797374656d);
INSERT INTO `lng_data` VALUES ('common', 'root_visible', 'en', 0x494c4941532073797374656d2069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'root_write', 'en', 0x45646974206d65746164617461206f662073797374656d20726f6f74206e6f6465);
INSERT INTO `lng_data` VALUES ('common', 'sahs', 'en', 0x4c6561726e696e67204d6f64756c652053434f524d2f41494343);
INSERT INTO `lng_data` VALUES ('common', 'sahs_added', 'en', 0x53434f524d2f41494343204c6561726e696e67204d6f64756c65206164646564);
INSERT INTO `lng_data` VALUES ('common', 'sahs_create', 'en', 0x4372656174652053434f524d2f41494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'sahs_delete', 'en', 0x44656c6574652053434f524d2f41494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'sahs_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'sahs_join', 'en', 0x53756273637269626520746f2053434f524d2f41494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'sahs_leave', 'en', 0x556e7375627363726962652066726f6d2053434f524d2f41494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'sahs_read', 'en', 0x526561642061636365737320746f2053434f524d2f41494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'sahs_visible', 'en', 0x53434f524d2f41494343204c6561726e696e67204d6f64756c652069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'sahs_write', 'en', 0x456469742053434f524d2f41494343204c6561726e696e67204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'salutation', 'en', 0x53616c75746174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'salutation_f', 'en', 0x4d732e2f4d72732e);
INSERT INTO `lng_data` VALUES ('common', 'salutation_m', 'en', 0x4d722e);
INSERT INTO `lng_data` VALUES ('common', 'save', 'en', 0x53617665);
INSERT INTO `lng_data` VALUES ('common', 'save_and_back', 'en', 0x5361766520416e64204261636b);
INSERT INTO `lng_data` VALUES ('common', 'save_message', 'en', 0x53617665204d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'save_refresh', 'en', 0x5361766520616e642052656672657368);
INSERT INTO `lng_data` VALUES ('common', 'save_return', 'en', 0x5361766520616e642052657475726e);
INSERT INTO `lng_data` VALUES ('common', 'save_settings', 'en', 0x536176652053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'save_user_related_data', 'en', 0x5361766520757365722072656c61746564206163636573732064617461);
INSERT INTO `lng_data` VALUES ('common', 'saved', 'en', 0x5361766564);
INSERT INTO `lng_data` VALUES ('common', 'saved_successfully', 'en', 0x5361766564205375636365737366756c6c79);
INSERT INTO `lng_data` VALUES ('common', 'search', 'en', 0x536561726368);
INSERT INTO `lng_data` VALUES ('common', 'search_active', 'en', 0x496e636c75646520616374697665207573657273);
INSERT INTO `lng_data` VALUES ('common', 'search_for', 'en', 0x53656172636820466f72);
INSERT INTO `lng_data` VALUES ('common', 'search_in', 'en', 0x53656172636820696e);
INSERT INTO `lng_data` VALUES ('common', 'search_inactive', 'en', 0x496e636c75646520696e616374697665207573657273);
INSERT INTO `lng_data` VALUES ('common', 'search_new', 'en', 0x4e657720536561726368);
INSERT INTO `lng_data` VALUES ('common', 'search_note', 'en', 0x4966202671756f743b73656172636820696e2671756f743b206973206c65667420626c616e6b2c20616c6c2075736572732077696c6c2062652073656172636865642e205468697320616c6c6f777320796f7520746f2066696e6420616c6c20616374697665206f7220616c6c20696e6163746976652075736572732e);
INSERT INTO `lng_data` VALUES ('common', 'search_recipient', 'en', 0x53656172636820526563697069656e74);
INSERT INTO `lng_data` VALUES ('common', 'search_result', 'en', 0x53656172636820726573756c74);
INSERT INTO `lng_data` VALUES ('common', 'search_user', 'en', 0x5365617263682055736572);
INSERT INTO `lng_data` VALUES ('common', 'sections', 'en', 0x53656374696f6e73);
INSERT INTO `lng_data` VALUES ('common', 'select', 'en', 0x53656c656374);
INSERT INTO `lng_data` VALUES ('common', 'select_all', 'en', 0x53656c65637420416c6c);
INSERT INTO `lng_data` VALUES ('common', 'select_file', 'en', 0x53656c6563742066696c65);
INSERT INTO `lng_data` VALUES ('common', 'select_mode', 'en', 0x53656c656374206d6f6465);
INSERT INTO `lng_data` VALUES ('common', 'select_object_to_copy', 'en', 0x506c656173652073656c65637420746865206f626a65637420796f752077616e7420746f20636f7079);
INSERT INTO `lng_data` VALUES ('common', 'select_object_to_link', 'en', 0x506c656173652073656c65637420746865206f626a65637420776869636820796f752077616e7420746f206c696e6b);
INSERT INTO `lng_data` VALUES ('common', 'select_questionpool', 'en', 0x5175657374696f6e20706f6f6c20666f722054657374);
INSERT INTO `lng_data` VALUES ('common', 'select_questionpool_option', 'en', 0x506c656173652073656c6563742061207175657374696f6e20706f6f6c);
INSERT INTO `lng_data` VALUES ('common', 'selected', 'en', 0x53656c6563746564);
INSERT INTO `lng_data` VALUES ('common', 'selected_items', 'en', 0x506572736f6e616c204974656d73);
INSERT INTO `lng_data` VALUES ('common', 'send', 'en', 0x53656e64);
INSERT INTO `lng_data` VALUES ('common', 'sender', 'en', 0x53656e646572);
INSERT INTO `lng_data` VALUES ('common', 'sent', 'en', 0x53656e74);
INSERT INTO `lng_data` VALUES ('common', 'sequence', 'en', 0x53657175656e6365);
INSERT INTO `lng_data` VALUES ('common', 'sequences', 'en', 0x53657175656e636573);
INSERT INTO `lng_data` VALUES ('common', 'server', 'en', 0x536572766572);
INSERT INTO `lng_data` VALUES ('common', 'server_data', 'en', 0x5365727665722064617461);
INSERT INTO `lng_data` VALUES ('common', 'server_software', 'en', 0x53657276657220536f667477617265);
INSERT INTO `lng_data` VALUES ('common', 'set', 'en', 0x536574);
INSERT INTO `lng_data` VALUES ('common', 'setSystemLanguage', 'en', 0x5365742053797374656d204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'setUserLanguage', 'en', 0x5365742055736572204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'set_offline', 'en', 0x536574204f66666c696e65);
INSERT INTO `lng_data` VALUES ('common', 'set_online', 'en', 0x536574204f6e6c696e65);
INSERT INTO `lng_data` VALUES ('common', 'settings', 'en', 0x53657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'show', 'en', 0x53686f77);
INSERT INTO `lng_data` VALUES ('common', 'show_details', 'en', 0x73686f772064657461696c73);
INSERT INTO `lng_data` VALUES ('common', 'show_list', 'en', 0x53686f77204c697374);
INSERT INTO `lng_data` VALUES ('common', 'show_members', 'en', 0x446973706c6179204d656d62657273);
INSERT INTO `lng_data` VALUES ('common', 'show_owner', 'en', 0x53686f77204f776e6572);
INSERT INTO `lng_data` VALUES ('common', 'show_structure', 'en', 0x456e61626c6520537472756374757265642d56696577);
INSERT INTO `lng_data` VALUES ('common', 'show_users_online', 'en', 0x53686f77207573657273206f6e6c696e65);
INSERT INTO `lng_data` VALUES ('common', 'signature', 'en', 0x5369676e6174757265);
INSERT INTO `lng_data` VALUES ('common', 'smtp', 'en', 0x534d5450);
INSERT INTO `lng_data` VALUES ('common', 'sort_by_this_column', 'en', 0x536f7274206279207468697320636f6c756d6e);
INSERT INTO `lng_data` VALUES ('common', 'spl', 'en', 0x5175657374696f6e20506f6f6c20537572766579);
INSERT INTO `lng_data` VALUES ('common', 'spl_add', 'en', 0x416464207175657374696f6e20706f6f6c20666f7220737572766579);
INSERT INTO `lng_data` VALUES ('common', 'spl_delete', 'en', 0x44656c657465205175657374696f6e20506f6f6c20666f7220537572766579);
INSERT INTO `lng_data` VALUES ('common', 'spl_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'spl_new', 'en', 0x4e6577207175657374696f6e20706f6f6c20666f7220737572766579);
INSERT INTO `lng_data` VALUES ('common', 'spl_read', 'en', 0x526561642061636365737320746f205175657374696f6e20506f6f6c20666f7220537572766579);
INSERT INTO `lng_data` VALUES ('common', 'spl_visible', 'en', 0x5175657374696f6e20506f6f6c20666f72205375727665792069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'spl_write', 'en', 0x45646974205175657374696f6e20506f6f6c20666f7220537572766579);
INSERT INTO `lng_data` VALUES ('common', 'st_a', 'en', 0x612063686170746572);
INSERT INTO `lng_data` VALUES ('common', 'st_add', 'en', 0x4164642063686170746572);
INSERT INTO `lng_data` VALUES ('common', 'st_added', 'en', 0x43686170746572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'st_edit', 'en', 0x456469742063686170746572);
INSERT INTO `lng_data` VALUES ('common', 'st_new', 'en', 0x4e65772063686170746572);
INSERT INTO `lng_data` VALUES ('common', 'startpage', 'en', 0x53746172742070616765);
INSERT INTO `lng_data` VALUES ('common', 'statistic', 'en', 0x537461746973746963);
INSERT INTO `lng_data` VALUES ('common', 'status', 'en', 0x537461747573);
INSERT INTO `lng_data` VALUES ('common', 'step', 'en', 0x53746570);
INSERT INTO `lng_data` VALUES ('common', 'stop_inheritance', 'en', 0x53746f7020696e6865726974616e6365);
INSERT INTO `lng_data` VALUES ('common', 'street', 'en', 0x537472656574);
INSERT INTO `lng_data` VALUES ('common', 'structure', 'en', 0x537472756374757265);
INSERT INTO `lng_data` VALUES ('common', 'subcat_name', 'en', 0x53756263617465676f7279204e616d65);
INSERT INTO `lng_data` VALUES ('common', 'subchapter_new', 'en', 0x4e65772053756263686170746572);
INSERT INTO `lng_data` VALUES ('common', 'subject', 'en', 0x5375626a656374);
INSERT INTO `lng_data` VALUES ('common', 'subject_module', 'en', 0x5375626a656374204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'submit', 'en', 0x5375626d6974);
INSERT INTO `lng_data` VALUES ('common', 'subobjects', 'en', 0x5375626f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'subscription', 'en', 0x537562736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'summary', 'en', 0x53756d6d617279);
INSERT INTO `lng_data` VALUES ('common', 'sure_delete_selected_users', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c657465207468652073656c65637465642075736572287329);
INSERT INTO `lng_data` VALUES ('common', 'svy', 'en', 0x537572766579);
INSERT INTO `lng_data` VALUES ('common', 'svy_add', 'en', 0x41646420737572766579);
INSERT INTO `lng_data` VALUES ('common', 'svy_delete', 'en', 0x44656c65746520737572766579);
INSERT INTO `lng_data` VALUES ('common', 'svy_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'svy_invite', 'en', 0x496e7669746520757365727320746f206120737572766579);
INSERT INTO `lng_data` VALUES ('common', 'svy_new', 'en', 0x4e657720737572766579);
INSERT INTO `lng_data` VALUES ('common', 'svy_participate', 'en', 0x506172746963697061746520696e206120737572766579);
INSERT INTO `lng_data` VALUES ('common', 'svy_read', 'en', 0x526561642061636365737320746f20737572766579);
INSERT INTO `lng_data` VALUES ('common', 'svy_visible', 'en', 0x5375727665792069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'svy_write', 'en', 0x4564697420737572766579);
INSERT INTO `lng_data` VALUES ('common', 'system', 'en', 0x53797374656d);
INSERT INTO `lng_data` VALUES ('common', 'system_check', 'en', 0x53797374656d20436865636b);
INSERT INTO `lng_data` VALUES ('common', 'system_choose_language', 'en', 0x53797374656d2043686f6f7365204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'system_groups', 'en', 0x53797374656d2047726f757073);
INSERT INTO `lng_data` VALUES ('common', 'system_grp', 'en', 0x53797374656d2047726f7570);
INSERT INTO `lng_data` VALUES ('common', 'system_information', 'en', 0x53797374656d20496e666f726d6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'system_language', 'en', 0x53797374656d204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'system_message', 'en', 0x53797374656d204d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'table_mail_import', 'en', 0x4d61696c20696d706f7274);
INSERT INTO `lng_data` VALUES ('common', 'tax', 'en', 0x5461786f6e6f6d79);
INSERT INTO `lng_data` VALUES ('common', 'tax_add', 'en', 0x416464205461786f6e6f6d79);
INSERT INTO `lng_data` VALUES ('common', 'tax_delete', 'en', 0x44656c657465207461786f6e6f6d696573);
INSERT INTO `lng_data` VALUES ('common', 'tax_edit', 'en', 0x45646974205461786f6e6f6d79);
INSERT INTO `lng_data` VALUES ('common', 'tax_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'tax_new', 'en', 0x4e6577205461786f6e6f6d79);
INSERT INTO `lng_data` VALUES ('common', 'tax_read', 'en', 0x526561642061636365737320746f207461786f6e6f6d696573);
INSERT INTO `lng_data` VALUES ('common', 'tax_visible', 'en', 0x5461786f6e6f6d792069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'tax_write', 'en', 0x57726974652061636365737320746f207461786f6e6f6d696573);
INSERT INTO `lng_data` VALUES ('common', 'taxf_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'taxf_read', 'en', 0x526561642061636365737320746f207461786f6e6f6d7920666f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'taxf_visible', 'en', 0x5461786f6e6f6d7920666f6c6465722069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'taxf_write', 'en', 0x57726974652061636365737320746f207461786f6e6f6d7920666f6c646572);
INSERT INTO `lng_data` VALUES ('common', 'term', 'en', 0x5465726d);
INSERT INTO `lng_data` VALUES ('common', 'test', 'en', 0x54657374);
INSERT INTO `lng_data` VALUES ('common', 'test_intern', 'en', 0x5465737420496e7465726e);
INSERT INTO `lng_data` VALUES ('common', 'test_module', 'en', 0x54657374204d6f64756c65);
INSERT INTO `lng_data` VALUES ('common', 'tests', 'en', 0x5465737473);
INSERT INTO `lng_data` VALUES ('common', 'time', 'en', 0x54696d65);
INSERT INTO `lng_data` VALUES ('common', 'time_d', 'en', 0x446179);
INSERT INTO `lng_data` VALUES ('common', 'time_h', 'en', 0x486f7572);
INSERT INTO `lng_data` VALUES ('common', 'time_limit', 'en', 0x416363657373);
INSERT INTO `lng_data` VALUES ('common', 'time_limit_add_time_limit_for_selected', 'en', 0x506c6561736520656e74657220612074696d6520706572696f6420666f72207468652073656c65637465642075736572732e);
INSERT INTO `lng_data` VALUES ('common', 'time_limit_applied_users', 'en', 0x4170706c696564207573657273);
INSERT INTO `lng_data` VALUES ('common', 'time_limit_no_users_selected', 'en', 0x506c656173652073656c656374206120757365722e);
INSERT INTO `lng_data` VALUES ('common', 'time_limit_not_valid', 'en', 0x54686520706572696f64206973206e6f742076616c69642e);
INSERT INTO `lng_data` VALUES ('common', 'time_limit_not_within_owners', 'en', 0x596f757220616363657373206973206c696d697465642e2054686520706572696f64206973206f757473696465206f6620796f7572206c696d69742e);
INSERT INTO `lng_data` VALUES ('common', 'time_limit_reached', 'en', 0x596f75722061636365737320706572696f6420697320657870697265642e);
INSERT INTO `lng_data` VALUES ('common', 'time_limit_users_updated', 'en', 0x5573657220646174612075706461746564);
INSERT INTO `lng_data` VALUES ('common', 'time_limits', 'en', 0x416363657373);
INSERT INTO `lng_data` VALUES ('common', 'time_segment', 'en', 0x506572696f64206f662074696d65);
INSERT INTO `lng_data` VALUES ('common', 'title', 'en', 0x5469746c65);
INSERT INTO `lng_data` VALUES ('common', 'to', 'en', 0x546f);
INSERT INTO `lng_data` VALUES ('common', 'to_client_list', 'en', 0x546f20436c69656e742073656c656374696f6e);
INSERT INTO `lng_data` VALUES ('common', 'to_desktop', 'en', 0x537562736372696265);
INSERT INTO `lng_data` VALUES ('common', 'today', 'en', 0x546f646179);
INSERT INTO `lng_data` VALUES ('common', 'total', 'en', 0x546f74616c);
INSERT INTO `lng_data` VALUES ('common', 'tpl_path', 'en', 0x54656d706c6174652050617468);
INSERT INTO `lng_data` VALUES ('common', 'trac_delete', 'en', 0x44656c65746520547261636b696e672044617461);
INSERT INTO `lng_data` VALUES ('common', 'trac_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'trac_read', 'en', 0x5265616420547261636b696e672044617461);
INSERT INTO `lng_data` VALUES ('common', 'trac_visible', 'en', 0x5573657220747261636b696e672069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'tracked_objects', 'en', 0x547261636b6564206f626a65637473);
INSERT INTO `lng_data` VALUES ('common', 'tracking_data', 'en', 0x547261636b696e672044617461);
INSERT INTO `lng_data` VALUES ('common', 'tracking_data_del_confirm', 'en', 0x446f20796f75207265616c6c792077616e7420746f2064656c65746520616c6c20747261636b696e672064617461206f662074686520737065636966696564206d6f6e746820616e64206265666f72653f);
INSERT INTO `lng_data` VALUES ('common', 'tracking_data_deleted', 'en', 0x547261636b696e6720646174612064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'tracking_settings', 'en', 0x547261636b696e672053657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'translation', 'en', 0x5472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('common', 'trash', 'en', 0x5472617368);
INSERT INTO `lng_data` VALUES ('common', 'treeview', 'en', 0x547265652056696577);
INSERT INTO `lng_data` VALUES ('common', 'tst', 'en', 0x54657374);
INSERT INTO `lng_data` VALUES ('common', 'tst_add', 'en', 0x4164642074657374);
INSERT INTO `lng_data` VALUES ('common', 'tst_delete', 'en', 0x44656c6574652054657374);
INSERT INTO `lng_data` VALUES ('common', 'tst_edit_permission', 'en', 0x4368616e6765207065726d697373696f6e2073657474696e6773);
INSERT INTO `lng_data` VALUES ('common', 'tst_new', 'en', 0x4e65772074657374);
INSERT INTO `lng_data` VALUES ('common', 'tst_read', 'en', 0x526561642061636365737320746f2054657374);
INSERT INTO `lng_data` VALUES ('common', 'tst_visible', 'en', 0x546573742069732076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'tst_write', 'en', 0x456469742054657374);
INSERT INTO `lng_data` VALUES ('common', 'txt_add_object_instruction1', 'en', 0x42726f77736520746f20746865206c6f636174696f6e20776865726520796f752077616e7420746f20616464);
INSERT INTO `lng_data` VALUES ('common', 'txt_add_object_instruction2', 'en', 0x2e);
INSERT INTO `lng_data` VALUES ('common', 'txt_registered', 'en', 0x596f75207375636365737366756c6c79207265676973746572656420746f20494c4941532e20506c6561736520636c69636b206f6e2074686520627574746f6e2062656c6f7720746f206c6f67696e20746f20494c494153207769746820796f75722075736572206163636f756e742e);
INSERT INTO `lng_data` VALUES ('common', 'txt_submitted', 'en', 0x596f75207375636365737366756c6c79207375626d697474656420616e206163636f756e74207265717565737420746f20494c4941532e20596f7572206163636f756e7420726571756573742077696c6c206265207265766965776564206279207468652073797374656d2061646d696e6973747261746f72732c20616e642073686f756c64206265206163746976617465642077697468696e20343820686f7572732e20596f752077696c6c206e6f742062652061626c6520746f206c6f6720696e20756e74696c20796f7572206163636f756e74206973206163746976617465642e);
INSERT INTO `lng_data` VALUES ('common', 'typ', 'en', 0x4f626a656374205479706520446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('common', 'type', 'en', 0x54797065);
INSERT INTO `lng_data` VALUES ('common', 'type_your_message_here', 'en', 0x5479706520596f7572204d6573736167652048657265);
INSERT INTO `lng_data` VALUES ('common', 'uid', 'en', 0x554944);
INSERT INTO `lng_data` VALUES ('common', 'uncheck_all', 'en', 0x556e636865636b20616c6c);
INSERT INTO `lng_data` VALUES ('common', 'uninstall', 'en', 0x556e696e7374616c6c);
INSERT INTO `lng_data` VALUES ('common', 'uninstalled', 'en', 0x756e696e7374616c6c65642e);
INSERT INTO `lng_data` VALUES ('common', 'unknown', 'en', 0x554e4b4e4f574e);
INSERT INTO `lng_data` VALUES ('common', 'unread', 'en', 0x556e72656164);
INSERT INTO `lng_data` VALUES ('common', 'unselected', 'en', 0x556e73656c6563746564);
INSERT INTO `lng_data` VALUES ('common', 'unsubscribe', 'en', 0x756e737562736372696265);
INSERT INTO `lng_data` VALUES ('common', 'unzip', 'en', 0x556e7a6970);
INSERT INTO `lng_data` VALUES ('common', 'up', 'en', 0x5570);
INSERT INTO `lng_data` VALUES ('common', 'update_applied', 'en', 0x557064617465204170706c696564);
INSERT INTO `lng_data` VALUES ('common', 'update_language', 'en', 0x557064617465204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'upload', 'en', 0x55706c6f6164);
INSERT INTO `lng_data` VALUES ('common', 'uploaded_and_checked', 'en', 0x5468652066696c6520686173206265656e2075706c6f6164656420616e6420636865636b65642c20796f752063616e206e6f7720737461727420746f20696d706f72742069742e);
INSERT INTO `lng_data` VALUES ('common', 'url', 'en', 0x55524c);
INSERT INTO `lng_data` VALUES ('common', 'url_description', 'en', 0x55524c204465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('common', 'user', 'en', 0x55736572);
INSERT INTO `lng_data` VALUES ('common', 'user_access', 'en', 0x416363657373207065722075736572);
INSERT INTO `lng_data` VALUES ('common', 'user_added', 'en', 0x55736572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'user_assignment', 'en', 0x557365722041737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('common', 'user_cant_receive_mail', 'en', 0x75736572206973206e6f7420616c6c6f77656420746f2075736520746865206d61696c2073797374656d);
INSERT INTO `lng_data` VALUES ('common', 'user_deleted', 'en', 0x557365722064656c65746564);
INSERT INTO `lng_data` VALUES ('common', 'user_detail', 'en', 0x44657461696c2044617461);
INSERT INTO `lng_data` VALUES ('common', 'user_imported', 'en', 0x5573657220696d706f727420636f6d706c6574652e);
INSERT INTO `lng_data` VALUES ('common', 'user_language', 'en', 0x55736572204c616e6775616765);
INSERT INTO `lng_data` VALUES ('common', 'user_not_chosen', 'en', 0x596f7520646964206e6f742063686f6f736520612067726f75702d6d656d62657221);
INSERT INTO `lng_data` VALUES ('common', 'user_statistics', 'en', 0x537461746973746963616c2044617461);
INSERT INTO `lng_data` VALUES ('common', 'userdata', 'en', 0x557365722064617461);
INSERT INTO `lng_data` VALUES ('common', 'username', 'en', 0x55736572206e616d65);
INSERT INTO `lng_data` VALUES ('common', 'users', 'en', 0x5573657273);
INSERT INTO `lng_data` VALUES ('common', 'users_not_imported', 'en', 0x54686520666f6c6c6f77696e6720757365727320646f206e6f742065786973742c207468656972206d657373616765732063616e6e6f74206265636f6d6520696d706f72746564);
INSERT INTO `lng_data` VALUES ('common', 'users_online', 'en', 0x5573657273206f6e6c696e65);
INSERT INTO `lng_data` VALUES ('common', 'usertracking', 'en', 0x5573657220547261636b696e67);
INSERT INTO `lng_data` VALUES ('common', 'usr', 'en', 0x55736572);
INSERT INTO `lng_data` VALUES ('common', 'usr_a', 'en', 0x612055736572);
INSERT INTO `lng_data` VALUES ('common', 'usr_add', 'en', 0x4164642055736572);
INSERT INTO `lng_data` VALUES ('common', 'usr_added', 'en', 0x55736572206164646564);
INSERT INTO `lng_data` VALUES ('common', 'usr_agreement', 'en', 0x557365722041677265656d656e74);
INSERT INTO `lng_data` VALUES ('common', 'usr_agreement_empty', 'en', 0x5468652061677265656d656e7420636f6e7461696e73206e6f2074657874);
INSERT INTO `lng_data` VALUES ('common', 'usr_edit', 'en', 0x456469742055736572);
INSERT INTO `lng_data` VALUES ('common', 'usr_hits_per_page', 'en', 0x486974732f7061676520696e207461626c6573);
INSERT INTO `lng_data` VALUES ('common', 'usr_new', 'en', 0x4e65772055736572);
INSERT INTO `lng_data` VALUES ('common', 'usr_skin_style', 'en', 0x536b696e202f205374796c65);
INSERT INTO `lng_data` VALUES ('common', 'usr_style', 'en', 0x55736572205374796c65);
INSERT INTO `lng_data` VALUES ('common', 'usrf', 'en', 0x55736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'usrf_create_user', 'en', 0x437265617465206e65772075736572206163636f756e74);
INSERT INTO `lng_data` VALUES ('common', 'usrf_delete', 'en', 0x44656c6574652075736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'usrf_edit_permission', 'en', 0x4368616e67652061636365737320746f2075736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'usrf_edit_roleassignment', 'en', 0x4368616e676520726f6c652061737369676e6d656e74206f662075736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'usrf_read', 'en', 0x526561642061636365737320746f2075736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'usrf_read_users', 'en', 0x526f6c652061737369676e6d656e7420666f72206c6f63616c2061646d696e6973747261746f7273);
INSERT INTO `lng_data` VALUES ('common', 'usrf_visible', 'en', 0x55736572206163636f756e7473206172652076697369626c65);
INSERT INTO `lng_data` VALUES ('common', 'usrf_write', 'en', 0x456469742075736572206163636f756e7473);
INSERT INTO `lng_data` VALUES ('common', 'validate', 'en', 0x56616c6964617465);
INSERT INTO `lng_data` VALUES ('common', 'value', 'en', 0x56616c7565);
INSERT INTO `lng_data` VALUES ('common', 'vendors', 'en', 0x56656e646f7273);
INSERT INTO `lng_data` VALUES ('common', 'version', 'en', 0x56657273696f6e);
INSERT INTO `lng_data` VALUES ('common', 'view', 'en', 0x56696577);
INSERT INTO `lng_data` VALUES ('common', 'view_content', 'en', 0x5669657720436f6e74656e74);
INSERT INTO `lng_data` VALUES ('common', 'visible_layers', 'en', 0x56697369626c65204c6179657273);
INSERT INTO `lng_data` VALUES ('common', 'visits', 'en', 0x566973697473);
INSERT INTO `lng_data` VALUES ('common', 'week', 'en', 0x5765656b);
INSERT INTO `lng_data` VALUES ('common', 'welcome', 'en', 0x57656c636f6d65);
INSERT INTO `lng_data` VALUES ('common', 'who_is_online', 'en', 0x57686f206973206f6e6c696e653f);
INSERT INTO `lng_data` VALUES ('common', 'with', 'en', 0x77697468);
INSERT INTO `lng_data` VALUES ('common', 'write', 'en', 0x5772697465);
INSERT INTO `lng_data` VALUES ('common', 'yes', 'en', 0x596573);
INSERT INTO `lng_data` VALUES ('common', 'you_may_add_local_roles', 'en', 0x596f75204d617920416464204c6f63616c20526f6c6573);
INSERT INTO `lng_data` VALUES ('common', 'your_message', 'en', 0x596f7572204d657373616765);
INSERT INTO `lng_data` VALUES ('common', 'zip', 'en', 0x5a697020436f6465);
INSERT INTO `lng_data` VALUES ('common', 'zipcode', 'en', 0x5a697020436f6465);
INSERT INTO `lng_data` VALUES ('content', 'HTML export', 'en', 0x48544d4c204578706f7274);
INSERT INTO `lng_data` VALUES ('content', 'PDF export', 'en', 0x504446204578706f7274);
INSERT INTO `lng_data` VALUES ('content', 'Pages', 'en', 0x5061676573);
INSERT INTO `lng_data` VALUES ('content', 'all', 'en', 0x416c6c);
INSERT INTO `lng_data` VALUES ('content', 'cont_Additional', 'en', 0x4164646974696f6e616c);
INSERT INTO `lng_data` VALUES ('content', 'cont_Alphabetic', 'en', 0x416c706861626574696320412c20422c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Circle', 'en', 0x436972636c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_Citation', 'en', 0x4369746174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Code', 'en', 0x436f6465);
INSERT INTO `lng_data` VALUES ('content', 'cont_Example', 'en', 0x4578616d706c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline1', 'en', 0x486561646c696e652031);
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline2', 'en', 0x486561646c696e652032);
INSERT INTO `lng_data` VALUES ('content', 'cont_Headline3', 'en', 0x486561646c696e652033);
INSERT INTO `lng_data` VALUES ('content', 'cont_List', 'en', 0x4c697374);
INSERT INTO `lng_data` VALUES ('content', 'cont_LocalFile', 'en', 0x4c6f63616c2046696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_Mnemonic', 'en', 0x4d6e656d6f6e6963);
INSERT INTO `lng_data` VALUES ('content', 'cont_Number', 'en', 0x4e756d626572);
INSERT INTO `lng_data` VALUES ('content', 'cont_Poly', 'en', 0x506f6c79676f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_Rect', 'en', 0x52656374616e676c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_Reference', 'en', 0x5265666572656e6365);
INSERT INTO `lng_data` VALUES ('content', 'cont_Remark', 'en', 0x52656d61726b);
INSERT INTO `lng_data` VALUES ('content', 'cont_Roman', 'en', 0x526f6d616e20492c2049492c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_TableContent', 'en', 0x5461626c6520436f6e74656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_Unordered', 'en', 0x556e6f726465726564);
INSERT INTO `lng_data` VALUES ('content', 'cont_act_number', 'en', 0x43686170746572204e756d65726174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_active', 'en', 0x456e61626c65204d656e75);
INSERT INTO `lng_data` VALUES ('content', 'cont_add_area', 'en', 0x4164642041726561);
INSERT INTO `lng_data` VALUES ('content', 'cont_add_definition', 'en', 0x41646420446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_add_fullscreen', 'en', 0x4164642046756c6c2053637265656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_added_term', 'en', 0x5465726d206164646564);
INSERT INTO `lng_data` VALUES ('content', 'cont_all_definitions', 'en', 0x416c6c20446566696e6974696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_all_pages', 'en', 0x416c6c205061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_alphabetic', 'en', 0x416c706861626574696320612c20622c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_annex', 'en', 0x416e6e6578);
INSERT INTO `lng_data` VALUES ('content', 'cont_api_adapter', 'en', 0x4150492041646170746572204e616d65);
INSERT INTO `lng_data` VALUES ('content', 'cont_api_func_prefix', 'en', 0x4150492046756e6374696f6e7320507265666978);
INSERT INTO `lng_data` VALUES ('content', 'cont_areas_deleted', 'en', 0x4d61702061726561732064656c657465642e);
INSERT INTO `lng_data` VALUES ('content', 'cont_assign_full', 'en', 0x41737369676e2046756c6c2053637265656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_assign_std', 'en', 0x41737369676e205374616e64617264);
INSERT INTO `lng_data` VALUES ('content', 'cont_assign_translation', 'en', 0x41737369676e207472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_assignments_deleted', 'en', 0x5468652061737369676e6d656e74732068617665206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('content', 'cont_autoindent', 'en', 0x4175746f20496e64656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_back', 'en', 0x4261636b);
INSERT INTO `lng_data` VALUES ('content', 'cont_booktitle', 'en', 0x426f6f6b207469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_bottom', 'en', 0x426f74746f6d);
INSERT INTO `lng_data` VALUES ('content', 'cont_cant_copy_folders', 'en', 0x466f6c646572732063616e6e6f7420626520636f7069656420746f20636c6970626f6172642e);
INSERT INTO `lng_data` VALUES ('content', 'cont_cant_del_full', 'en', 0x44656c6574696f6e206f662066756c6c2073637265656e2066696c65206e6f7420706f737369626c652e);
INSERT INTO `lng_data` VALUES ('content', 'cont_cant_del_std', 'en', 0x44656c6574696f6e206f66207374616e6461726420766965772066696c65206e6f7420706f737369626c652e);
INSERT INTO `lng_data` VALUES ('content', 'cont_caption', 'en', 0x43617074696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_change_type', 'en', 0x4368616e67652054797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_chap_and_pages', 'en', 0x436861707465727320616e64205061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_chap_select_target_now', 'en', 0x43686170746572206d61726b656420666f72206d6f76696e672e2053656c65637420746172676574206e6f772e);
INSERT INTO `lng_data` VALUES ('content', 'cont_chapters', 'en', 0x4368617074657273);
INSERT INTO `lng_data` VALUES ('content', 'cont_chapters_and_pages', 'en', 0x436861707465727320616e64205061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_chapters_only', 'en', 0x4368617074657273206f6e6c79);
INSERT INTO `lng_data` VALUES ('content', 'cont_characteristic', 'en', 0x4368617261637465726973746963);
INSERT INTO `lng_data` VALUES ('content', 'cont_choose_cont_obj', 'en', 0x43686f6f736520436f6e74656e74204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_choose_glossary', 'en', 0x43686f6f736520476c6f7373617279);
INSERT INTO `lng_data` VALUES ('content', 'cont_choose_media_source', 'en', 0x43686f6f7365204d6564696120536f75726365);
INSERT INTO `lng_data` VALUES ('content', 'cont_citation_err_one', 'en', 0x596f75206d7573742073656c6563742065786163746c79206f6e652065646974696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_citation_selection_not_valid', 'en', 0x596f752772652073656c656374696f6e206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('content', 'cont_citations', 'en', 0x4369746174696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_clean_frames', 'en', 0x436c65616e206164646974696f6e616c206672616d6573206f6e206e617669676174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_click_br_corner', 'en', 0x506c6561736520636c69636b206f6e2074686520626f74746f6d20726967687420636f726e6572206f6620746865206465736972656420617265612e);
INSERT INTO `lng_data` VALUES ('content', 'cont_click_center', 'en', 0x506c6561736520636c69636b206f6e2063656e746572206f6620746865206465736972656420617265612e);
INSERT INTO `lng_data` VALUES ('content', 'cont_click_circle', 'en', 0x506c6561736520636c69636b206f6e206120636972636c6520706f696e74206f6620746865206465736972656420617265612e);
INSERT INTO `lng_data` VALUES ('content', 'cont_click_next_or_save', 'en', 0x506c6561736520636c69636b206f6e20746865206e65787420706f696e74206f662074686520706f6c79676f6e206f7220736176652074686520617265612e20284974206973206e6f74206e656365737361727920746f20636c69636b20616761696e206f6e20746865207374617274696e6720706f696e74206f66207468697320706f6c79676f6e202129);
INSERT INTO `lng_data` VALUES ('content', 'cont_click_next_point', 'en', 0x506c6561736520636c69636b206f6e20746865206e65787420706f696e74206f662074686520706f6c79676f6e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_click_starting_point', 'en', 0x506c6561736520636c69636b206f6e20746865207374617274696e6720706f696e74206f662074686520706f6c79676f6e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_click_tl_corner', 'en', 0x506c6561736520636c69636b206f6e2074686520746f70206c65667420636f726e6572206f6620746865206465736972656420617265612e);
INSERT INTO `lng_data` VALUES ('content', 'cont_content', 'en', 0x436f6e74656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_content_obj', 'en', 0x436f6e74656e74204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_contents', 'en', 0x436f6e74656e7473);
INSERT INTO `lng_data` VALUES ('content', 'cont_coords', 'en', 0x436f6f7264696e61746573);
INSERT INTO `lng_data` VALUES ('content', 'cont_copy_to_clipboard', 'en', 0x436f707920746f20636c6970626f617264);
INSERT INTO `lng_data` VALUES ('content', 'cont_create_dir', 'en', 0x437265617465204469726563746f7279);
INSERT INTO `lng_data` VALUES ('content', 'cont_create_export_file', 'en', 0x437265617465204578706f72742046696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_create_folder', 'en', 0x43726561746520466f6c646572);
INSERT INTO `lng_data` VALUES ('content', 'cont_create_mob', 'en', 0x437265617465204d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_credit_mode', 'en', 0x437265646974206d6f64652028666f72206c6573736f6e206d6f646520276e6f726d616c2729);
INSERT INTO `lng_data` VALUES ('content', 'cont_credit_off', 'en', 0x4e6f20437265646974);
INSERT INTO `lng_data` VALUES ('content', 'cont_credit_on', 'en', 0x437265646974);
INSERT INTO `lng_data` VALUES ('content', 'cont_credits', 'en', 0x43726564697473);
INSERT INTO `lng_data` VALUES ('content', 'cont_cross_reference', 'en', 0x43726f7373207265666572656e6365);
INSERT INTO `lng_data` VALUES ('content', 'cont_data_from_lms', 'en', 0x61646c63703a6461746166726f6d6c6d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_def_layout', 'en', 0x44656661756c74204c61796f7574);
INSERT INTO `lng_data` VALUES ('content', 'cont_def_lesson_mode', 'en', 0x44656661756c74206c6573736f6e206d6f6465);
INSERT INTO `lng_data` VALUES ('content', 'cont_def_organization', 'en', 0x64656661756c74);
INSERT INTO `lng_data` VALUES ('content', 'cont_definition', 'en', 0x446566696e6974696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_definitions', 'en', 0x446566696e6974696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_del_assignment', 'en', 0x44656c6574652061737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_dependencies', 'en', 0x446570656e64656e63696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_derive_from_obj', 'en', 0x4465726976652066726f6d204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_details', 'en', 0x44657461696c73);
INSERT INTO `lng_data` VALUES ('content', 'cont_dir_file', 'en', 0x4469726563746f72792f46696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_disable_media', 'en', 0x44697361626c65204d65646961);
INSERT INTO `lng_data` VALUES ('content', 'cont_download_title', 'en', 0x446f776e6c6f6164205469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_align_center', 'en', 0x616c69676e3a2063656e746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_align_left', 'en', 0x616c69676e3a206c656674);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_align_left_float', 'en', 0x616c69676e3a206c65667420666c6f6174);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_align_right', 'en', 0x616c69676e3a207269676874);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_align_right_float', 'en', 0x616c69676e3a20726967687420666c6f6174);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_class', 'en', 0x5374796c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_copy_clip', 'en', 0x636f707920746f20636c6970626f617264);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_delete', 'en', 0x64656c657465);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_delete_col', 'en', 0x64656c65746520636f6c756d6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_delete_item', 'en', 0x64656c657465206974656d);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_delete_row', 'en', 0x64656c65746520726f77);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_edit', 'en', 0x65646974);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_edit_prop', 'en', 0x656469742070726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_go', 'en', 0x476f);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_insert_code', 'en', 0x696e7365727420436f6465);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_insert_filelist', 'en', 0x696e736572742046696c65204c697374);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_insert_list', 'en', 0x696e73657274204c697374);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_insert_media', 'en', 0x696e73657274204d65646961);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_insert_par', 'en', 0x696e73657274205061726167722e);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_insert_table', 'en', 0x696e73657274205461626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_moveafter', 'en', 0x6d6f7665206166746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_movebefore', 'en', 0x6d6f7665206265666f7265);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_new_col_after', 'en', 0x6e657720636f6c756d6e206166746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_new_col_before', 'en', 0x6e657720636f6c756d6e206265666f7265);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_new_item_after', 'en', 0x6e6577206974656d206166746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_new_item_before', 'en', 0x6e6577206974656d206265666f7265);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_new_row_after', 'en', 0x6e657720726f77206166746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_new_row_before', 'en', 0x6e657720726f77206265666f7265);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_paste_clip', 'en', 0x70617374652066726f6d20636c6970626f617264);
INSERT INTO `lng_data` VALUES ('content', 'cont_ed_width', 'en', 0x5769647468);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_area', 'en', 0x456469742041726561);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_file_list_properties', 'en', 0x456469742046696c65204c6973742050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob', 'en', 0x45646974204d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_alias_prop', 'en', 0x45646974204d65646961204f626a65637420496e7374616e63652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_files', 'en', 0x4f626a6563742046696c6573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_mob_properties', 'en', 0x45646974204d65646961204f626a6563742050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_par', 'en', 0x4564697420506172616772617068);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_src', 'en', 0x4564697420736f7572636520636f6465);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_tab_properties', 'en', 0x5461626c652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_edit_term', 'en', 0x45646974205465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_edition', 'en', 0x45646974696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_enable_media', 'en', 0x456e61626c65204d65646961);
INSERT INTO `lng_data` VALUES ('content', 'cont_export_files', 'en', 0x4578706f72742046696c6573);
INSERT INTO `lng_data` VALUES ('content', 'cont_external', 'en', 0x65787465726e616c);
INSERT INTO `lng_data` VALUES ('content', 'cont_file', 'en', 0x46696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_files', 'en', 0x46696c6573);
INSERT INTO `lng_data` VALUES ('content', 'cont_fix_tree', 'en', 0x46697820737472756374757265);
INSERT INTO `lng_data` VALUES ('content', 'cont_fix_tree_confirm', 'en', 0x506c656173652065786563757465207468697320636f6d6d616e64206f6e6c7920696620746865207472656520737472756374757265206f662074686973206c6561726e696e67206d6f64756c6520697320636f727275707465642c20652e672e20696620626c616e6b206974656d73206f6363757220696e20746865206578706c6f72657220766965772e);
INSERT INTO `lng_data` VALUES ('content', 'cont_format', 'en', 0x466f726d6174);
INSERT INTO `lng_data` VALUES ('content', 'cont_free_pages', 'en', 0x46726565205061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_full_is_in_dir', 'en', 0x44656c6574696f6e206e6f7420706f737369626c652e2046756c6c2073637265656e2066696c6520697320696e206469726563746f72792e);
INSERT INTO `lng_data` VALUES ('content', 'cont_fullscreen', 'en', 0x46756c6c2053637265656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_get_link', 'en', 0x676574206c696e6b);
INSERT INTO `lng_data` VALUES ('content', 'cont_get_orig_size', 'en', 0x536574206f726967696e616c2073697a65);
INSERT INTO `lng_data` VALUES ('content', 'cont_glo_properties', 'en', 0x476c6f73736172792050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_height', 'en', 0x486569676874);
INSERT INTO `lng_data` VALUES ('content', 'cont_how_published', 'en', 0x486f77207075626c6973686564);
INSERT INTO `lng_data` VALUES ('content', 'cont_href', 'en', 0x68726566);
INSERT INTO `lng_data` VALUES ('content', 'cont_id_ref', 'en', 0x6964656e746966696572726566);
INSERT INTO `lng_data` VALUES ('content', 'cont_imagemap', 'en', 0x496d616765204d6170);
INSERT INTO `lng_data` VALUES ('content', 'cont_import_id', 'en', 0x6964656e746966696572);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_file_item', 'en', 0x496e736572742046696c65204974656d);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_file_list', 'en', 0x496e736572742046696c65204c697374);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_list', 'en', 0x496e73657274204c697374);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_mob', 'en', 0x496e73657274204d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_par', 'en', 0x496e7365727420506172616772617068);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_search', 'en', 0x506c6561736520696e73657274206120736561726368207465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_src', 'en', 0x496e7365727420736f7572636520636f6465);
INSERT INTO `lng_data` VALUES ('content', 'cont_insert_table', 'en', 0x496e73657274205461626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_internal', 'en', 0x696e7465726e616c);
INSERT INTO `lng_data` VALUES ('content', 'cont_internal_link', 'en', 0x696e7465726e616c206c696e6b);
INSERT INTO `lng_data` VALUES ('content', 'cont_is_visible', 'en', 0x697376697369626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_isbn', 'en', 0x4953424e);
INSERT INTO `lng_data` VALUES ('content', 'cont_issn', 'en', 0x4953534e);
INSERT INTO `lng_data` VALUES ('content', 'cont_item', 'en', 0x4974656d);
INSERT INTO `lng_data` VALUES ('content', 'cont_journal', 'en', 0x4a6f75726e616c);
INSERT INTO `lng_data` VALUES ('content', 'cont_keyword', 'en', 0x4b6579776f7264);
INSERT INTO `lng_data` VALUES ('content', 'cont_link', 'en', 0x4c696e6b);
INSERT INTO `lng_data` VALUES ('content', 'cont_link_area', 'en', 0x4c696e6b2041726561);
INSERT INTO `lng_data` VALUES ('content', 'cont_link_ext', 'en', 0x4c696e6b202865787465726e616c29);
INSERT INTO `lng_data` VALUES ('content', 'cont_link_int', 'en', 0x4c696e6b2028696e7465726e616c29);
INSERT INTO `lng_data` VALUES ('content', 'cont_link_select', 'en', 0x496e7465726e616c204c696e6b);
INSERT INTO `lng_data` VALUES ('content', 'cont_link_type', 'en', 0x4c696e6b2054797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_linked_mobs', 'en', 0x4c696e6b6564206d65646961206f626a65637473);
INSERT INTO `lng_data` VALUES ('content', 'cont_list_files', 'en', 0x4c6973742046696c6573);
INSERT INTO `lng_data` VALUES ('content', 'cont_list_properties', 'en', 0x4c6973742050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_chapter', 'en', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_chapter_new', 'en', 0x4368617074657220284e6577204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_faq', 'en', 0x4d656469612028464151204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_inline', 'en', 0x4d656469612028496e6c696e6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_media', 'en', 0x4d6564696120284d65646961204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_media_new', 'en', 0x4d6564696120284e6577204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_page', 'en', 0x50616765);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_page_faq', 'en', 0x506167652028464151204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_page_new', 'en', 0x5061676520284e6577204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_survey', 'en', 0x537572766579);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_term', 'en', 0x476c6f7373617279205465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_lk_term_new', 'en', 0x476c6f7373617279205465726d20284e6577204672616d6529);
INSERT INTO `lng_data` VALUES ('content', 'cont_lm_menu', 'en', 0x4d656e75);
INSERT INTO `lng_data` VALUES ('content', 'cont_lm_properties', 'en', 0x4c6561726e696e67204d6f64756c652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_lvalue', 'en', 0x4461746120656c656d656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_manifest', 'en', 0x4d616e6966657374);
INSERT INTO `lng_data` VALUES ('content', 'cont_map_areas', 'en', 0x4c696e6b204172656173);
INSERT INTO `lng_data` VALUES ('content', 'cont_mastery_score', 'en', 0x61646c63703a6d61737465727973636f7265);
INSERT INTO `lng_data` VALUES ('content', 'cont_matches', 'en', 0x6d617463686573);
INSERT INTO `lng_data` VALUES ('content', 'cont_matching_question_javascript_hint', 'en', 0x506c656173652064726167206120646566696e6974696f6e2f70696374757265206f6e207468652072696768742073696465206f7665722061207465726d206f6e20746865206c656674207369646520616e642064726f702074686520646566696e6974696f6e2f7069637475726520746f206d6174636820746865207465726d20776974682074686520646566696e6974696f6e2f706963747572652e);
INSERT INTO `lng_data` VALUES ('content', 'cont_max_time_allowed', 'en', 0x61646c63703a6d617874696d65616c6c6f776564);
INSERT INTO `lng_data` VALUES ('content', 'cont_media_source', 'en', 0x4d6564696120536f75726365);
INSERT INTO `lng_data` VALUES ('content', 'cont_mep_structure', 'en', 0x4d6564696120506f6f6c20537472756374757265);
INSERT INTO `lng_data` VALUES ('content', 'cont_mob_files', 'en', 0x4f626a6563742046696c6573);
INSERT INTO `lng_data` VALUES ('content', 'cont_mob_inst_prop', 'en', 0x496e7374616e63652050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_mob_prop', 'en', 0x4f626a6563742050726f70657274696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_mob_usages', 'en', 0x5573616765);
INSERT INTO `lng_data` VALUES ('content', 'cont_month', 'en', 0x4d6f6e7468);
INSERT INTO `lng_data` VALUES ('content', 'cont_msg_multiple_editions', 'en', 0x49742773206e6f7420706f737369626c6520746f2073686f772064657461696c73206f66206d756c7469706c652065646974696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_name', 'en', 0x4e616d65);
INSERT INTO `lng_data` VALUES ('content', 'cont_new_area', 'en', 0x4e6577204c696e6b2041726561);
INSERT INTO `lng_data` VALUES ('content', 'cont_new_assignment', 'en', 0x4e65772061737369676e6d656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_new_dir', 'en', 0x4e6577204469726563746f7279);
INSERT INTO `lng_data` VALUES ('content', 'cont_new_file', 'en', 0x4e65772046696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_new_media_obj', 'en', 0x4e6577204d65646961204f626a656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_new_term', 'en', 0x4e6577205465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_no_assign_itself', 'en', 0x546865206f626a6563742063616e6e6f742062652061737369676e656420746f20697473656c66);
INSERT INTO `lng_data` VALUES ('content', 'cont_no_manifest', 'en', 0x4e6f20696d736d616e69666573742e786d6c2066696c6520666f756e6420696e206d61696e206469726563746f72792e);
INSERT INTO `lng_data` VALUES ('content', 'cont_no_object_found', 'en', 0x436f756c64206e6f742066696e6420616e79206f626a65637420776974682074686973207469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_no_page', 'en', 0x4e6f205061676520666f756e642e);
INSERT INTO `lng_data` VALUES ('content', 'cont_none', 'en', 0x4e6f6e65);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_cols', 'en', 0x4e756d626572206f6620436f6c756d6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_items', 'en', 0x4e756d626572206f66204974656d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_nr_rows', 'en', 0x4e756d626572206f6620526f7773);
INSERT INTO `lng_data` VALUES ('content', 'cont_obj_removed', 'en', 0x4f626a656374732072656d6f7665642e);
INSERT INTO `lng_data` VALUES ('content', 'cont_offline', 'en', 0x4f66666c696e652056657273696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_online', 'en', 0x4f6e6c696e65);
INSERT INTO `lng_data` VALUES ('content', 'cont_order', 'en', 0x4f726465722054797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_organization', 'en', 0x4f7267616e697a6174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_organizations', 'en', 0x4f7267616e697a6174696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_orig_size', 'en', 0x4f726967696e616c2053697a65);
INSERT INTO `lng_data` VALUES ('content', 'cont_page_header', 'en', 0x5061676520486561646572);
INSERT INTO `lng_data` VALUES ('content', 'cont_page_select_target_now', 'en', 0x50616765206d61726b656420666f72206d6f76696e672e2053656c65637420746172676574206e6f772e);
INSERT INTO `lng_data` VALUES ('content', 'cont_pages', 'en', 0x5061676573);
INSERT INTO `lng_data` VALUES ('content', 'cont_parameter', 'en', 0x506172616d65746572);
INSERT INTO `lng_data` VALUES ('content', 'cont_parameters', 'en', 0x706172616d6574657273);
INSERT INTO `lng_data` VALUES ('content', 'cont_personal_clipboard', 'en', 0x506572736f6e616c20436c6970626f617264);
INSERT INTO `lng_data` VALUES ('content', 'cont_pg_content', 'en', 0x5061676520436f6e74656e74);
INSERT INTO `lng_data` VALUES ('content', 'cont_pg_title', 'en', 0x50616765205469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_please_select', 'en', 0x706c656173652073656c656374);
INSERT INTO `lng_data` VALUES ('content', 'cont_prereq_type', 'en', 0x61646c63703a707265726571756973697465732e74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_prerequisites', 'en', 0x61646c63703a70726572657175697369746573);
INSERT INTO `lng_data` VALUES ('content', 'cont_preview', 'en', 0x50726576696577);
INSERT INTO `lng_data` VALUES ('content', 'cont_print_view', 'en', 0x5072696e742056696577);
INSERT INTO `lng_data` VALUES ('content', 'cont_publisher', 'en', 0x5075626c6973686572);
INSERT INTO `lng_data` VALUES ('content', 'cont_purpose', 'en', 0x507572706f7365);
INSERT INTO `lng_data` VALUES ('content', 'cont_ref_helptext', 'en', 0x28652e672e20687474703a2f2f7777772e7365727665722e6f72672f6d79696d6167652e6a706729);
INSERT INTO `lng_data` VALUES ('content', 'cont_reference', 'en', 0x5265666572656e6365);
INSERT INTO `lng_data` VALUES ('content', 'cont_remove_fullscreen', 'en', 0x52656d6f76652046756c6c2053637265656e);
INSERT INTO `lng_data` VALUES ('content', 'cont_reset_definitions', 'en', 0x526573657420646566696e6974696f6e20706f736974696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_reset_pictures', 'en', 0x5265736574207069637475726520706f736974696f6e73);
INSERT INTO `lng_data` VALUES ('content', 'cont_resource', 'en', 0x5265736f75726365);
INSERT INTO `lng_data` VALUES ('content', 'cont_resource_type', 'en', 0x74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_resources', 'en', 0x5265736f7572636573);
INSERT INTO `lng_data` VALUES ('content', 'cont_roman', 'en', 0x526f6d616e20692c2069692c202e2e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_rvalue', 'en', 0x56616c7565);
INSERT INTO `lng_data` VALUES ('content', 'cont_saved_map_area', 'en', 0x5361766564206d61702061726561);
INSERT INTO `lng_data` VALUES ('content', 'cont_saved_map_data', 'en', 0x5361766564206d61702064617461);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_auto_review', 'en', 0x536574206c6573736f6e206d6f64652027726576696577272069662073747564656e7420636f6d706c657465642053434f);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_less_mode_browse', 'en', 0x42726f777365);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_less_mode_normal', 'en', 0x4e6f726d616c);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_stat_browsed', 'en', 0x42726f77736564);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_stat_completed', 'en', 0x436f6d706c65746564);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_stat_failed', 'en', 0x4661696c6564);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_stat_incomplete', 'en', 0x496e636f6d706c657465);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_stat_not_attempted', 'en', 0x4e6f7420617474656d70746564);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_stat_passed', 'en', 0x506173736564);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_stat_running', 'en', 0x52756e6e696e67);
INSERT INTO `lng_data` VALUES ('content', 'cont_sc_title', 'en', 0x7469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_school', 'en', 0x5363686f6f6c);
INSERT INTO `lng_data` VALUES ('content', 'cont_score', 'en', 0x53636f7265);
INSERT INTO `lng_data` VALUES ('content', 'cont_scorm_type', 'en', 0x61646c63703a73636f726d74797065);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_item', 'en', 0x53656c656374206174206c65617374206f6e65206974656d2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_max_one_item', 'en', 0x506c656173652073656c656374206f6e65206974656d206f6e6c79);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_max_one_term', 'en', 0x53656c656374206f6e65207465726d206f6e6c79);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_one_edition', 'en', 0x506c656173652073656c656374206174206c65617374206f6e652065646974696f6e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_one_translation', 'en', 0x506c656173652073656c656374206f6e65207472616e736c6174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_one_translation_warning', 'en', 0x4974206973206e6f7420706f737369626c6520746f2073686f77206d6f7265207468616e206f6e65207472616e736c6174696f6e2e);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_term', 'en', 0x53656c6563742061207465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_select_translation', 'en', 0x506c656173652073656c656374207468652061737369676e6d656e742066726f6d20746865206c6973742061626f7665);
INSERT INTO `lng_data` VALUES ('content', 'cont_series', 'en', 0x536572696573);
INSERT INTO `lng_data` VALUES ('content', 'cont_series_editor', 'en', 0x53657269657320656469746f72);
INSERT INTO `lng_data` VALUES ('content', 'cont_series_title', 'en', 0x536572696573207469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_series_volume', 'en', 0x53657269657320766f6c756d65);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_class', 'en', 0x53657420436c617373);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_edit_mode', 'en', 0x5365742045646974204d6f6465);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_link', 'en', 0x45646974204c696e6b);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_shape', 'en', 0x45646974205368617065);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_start_file', 'en', 0x5365742053746172742046696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_set_width', 'en', 0x536574205769647468);
INSERT INTO `lng_data` VALUES ('content', 'cont_shape', 'en', 0x5368617065);
INSERT INTO `lng_data` VALUES ('content', 'cont_show', 'en', 0x53686f77);
INSERT INTO `lng_data` VALUES ('content', 'cont_show_citation', 'en', 0x53686f772077697468206369746174696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_show_line_numbers', 'en', 0x53686f77206c696e65206e756d62657273);
INSERT INTO `lng_data` VALUES ('content', 'cont_show_print_view', 'en', 0x53686f77205072696e742056696577);
INSERT INTO `lng_data` VALUES ('content', 'cont_size', 'en', 0x53697a652028427974657329);
INSERT INTO `lng_data` VALUES ('content', 'cont_source', 'en', 0x536f75726365);
INSERT INTO `lng_data` VALUES ('content', 'cont_src', 'en', 0x536f7572636520636f6465);
INSERT INTO `lng_data` VALUES ('content', 'cont_src_other', 'en', 0x6f74686572);
INSERT INTO `lng_data` VALUES ('content', 'cont_st_title', 'en', 0x43686170746572205469746c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_startfile', 'en', 0x53746172742046696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_status', 'en', 0x537461747573);
INSERT INTO `lng_data` VALUES ('content', 'cont_std_is_in_dir', 'en', 0x44656c6574696f6e206e6f7420706f737369626c652e205374616e6461726420766965772066696c6520697320696e206469726563746f72792e);
INSERT INTO `lng_data` VALUES ('content', 'cont_std_view', 'en', 0x5374616e646172642056696577);
INSERT INTO `lng_data` VALUES ('content', 'cont_structure', 'en', 0x737472756374757265);
INSERT INTO `lng_data` VALUES ('content', 'cont_subchapters', 'en', 0x5375626368617074657273);
INSERT INTO `lng_data` VALUES ('content', 'cont_table', 'en', 0x5461626c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_border', 'en', 0x5461626c6520426f72646572);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_cellpadding', 'en', 0x5461626c652043656c6c2050616464696e67);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_cellspacing', 'en', 0x5461626c652043656c6c2053706163696e67);
INSERT INTO `lng_data` VALUES ('content', 'cont_table_width', 'en', 0x5461626c65205769647468);
INSERT INTO `lng_data` VALUES ('content', 'cont_target_within_source', 'en', 0x546172676574206d757374206e6f742062652077697468696e20736f75726365206f626a6563742e);
INSERT INTO `lng_data` VALUES ('content', 'cont_term', 'en', 0x5465726d);
INSERT INTO `lng_data` VALUES ('content', 'cont_terms', 'en', 0x5465726d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_time', 'en', 0x54696d65);
INSERT INTO `lng_data` VALUES ('content', 'cont_time_limit_action', 'en', 0x61646c63703a74696d656c696d6974616374696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_toc', 'en', 0x5461626c65206f6620436f6e74656e7473);
INSERT INTO `lng_data` VALUES ('content', 'cont_toc_mode', 'en', 0x5461626c65206f6620436f6e74656e74732073686f77733a);
INSERT INTO `lng_data` VALUES ('content', 'cont_top', 'en', 0x546f70);
INSERT INTO `lng_data` VALUES ('content', 'cont_total_time', 'en', 0x546f74616c2054696d65);
INSERT INTO `lng_data` VALUES ('content', 'cont_tracking_data', 'en', 0x547261636b696e672044617461);
INSERT INTO `lng_data` VALUES ('content', 'cont_tracking_items', 'en', 0x547261636b696e67204974656d73);
INSERT INTO `lng_data` VALUES ('content', 'cont_translations', 'en', 0x5472616e736c6174696f6e287329);
INSERT INTO `lng_data` VALUES ('content', 'cont_translations_assigned', 'en', 0x546865207472616e736c6174696f6e2873292068617665206265656e2061737369676e6564);
INSERT INTO `lng_data` VALUES ('content', 'cont_tree_fixed', 'en', 0x547265652073747275637475726520686173206265656e2066697865642e);
INSERT INTO `lng_data` VALUES ('content', 'cont_update', 'en', 0x557064617465);
INSERT INTO `lng_data` VALUES ('content', 'cont_update_names', 'en', 0x557064617465204e616d6573);
INSERT INTO `lng_data` VALUES ('content', 'cont_upload_file', 'en', 0x55706c6f61642046696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_url', 'en', 0x55524c);
INSERT INTO `lng_data` VALUES ('content', 'cont_users_have_mob_in_clip1', 'en', 0x54686973206d65646961206f626a65637420697320696e2074686520636c6970626f617264206f66);
INSERT INTO `lng_data` VALUES ('content', 'cont_users_have_mob_in_clip2', 'en', 0x757365722873292e);
INSERT INTO `lng_data` VALUES ('content', 'cont_validate_file', 'en', 0x56616c69646174652046696c65);
INSERT INTO `lng_data` VALUES ('content', 'cont_version', 'en', 0x76657273696f6e);
INSERT INTO `lng_data` VALUES ('content', 'cont_view_last_export_log', 'en', 0x4c617374204578706f7274204c6f67);
INSERT INTO `lng_data` VALUES ('content', 'cont_where_published', 'en', 0x5768657265207075626c6973686564);
INSERT INTO `lng_data` VALUES ('content', 'cont_width', 'en', 0x5769647468);
INSERT INTO `lng_data` VALUES ('content', 'cont_wysiwyg', 'en', 0x436f6e74656e742057595349575947);
INSERT INTO `lng_data` VALUES ('content', 'cont_xml_base', 'en', 0x786d6c3a62617365);
INSERT INTO `lng_data` VALUES ('content', 'cont_year', 'en', 0x59656172);
INSERT INTO `lng_data` VALUES ('content', 'copied_to_clipboard', 'en', 0x436f70696564206f626a65637428732920746f20636c6970626f6172642e);
INSERT INTO `lng_data` VALUES ('content', 'pages from', 'en', 0x50616765732046726f6d);
INSERT INTO `lng_data` VALUES ('content', 'par', 'en', 0x506172616772617068);
INSERT INTO `lng_data` VALUES ('content', 'pg', 'en', 0x50616765);
INSERT INTO `lng_data` VALUES ('content', 'read offline', 'en', 0x52656164204f66666c696e65);
INSERT INTO `lng_data` VALUES ('content', 'select_a_file', 'en', 0x506c656173652073656c65637420612066696c652e);
INSERT INTO `lng_data` VALUES ('content', 'st', 'en', 0x43686170746572);
INSERT INTO `lng_data` VALUES ('content', 'start export', 'en', 0x5374617274204578706f7274);
INSERT INTO `lng_data` VALUES ('crs', 'crs_accept_subscriber', 'en', 0x436f7572736520737562736372697074696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_accept_subscriber_body', 'en', 0x596f752068617665206265656e2061737369676e656420746f207468697320636f757273652e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_access_password', 'en', 0x50617373776f7264);
INSERT INTO `lng_data` VALUES ('crs', 'crs_activation', 'en', 0x41637469766174696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_add_archive_html', 'en', 0x4164642048544d4c2d41726368697665);
INSERT INTO `lng_data` VALUES ('crs', 'crs_add_archive_xml', 'en', 0x41646420584d4c2d41726368697665);
INSERT INTO `lng_data` VALUES ('crs', 'crs_add_html_archive', 'en', 0x4164642048544d4c2061726368697665);
INSERT INTO `lng_data` VALUES ('crs', 'crs_add_member', 'en', 0x416464206d656d626572287329);
INSERT INTO `lng_data` VALUES ('crs', 'crs_add_subscribers', 'en', 0x4164642073756273637269626572);
INSERT INTO `lng_data` VALUES ('crs', 'crs_added', 'en', 0x4164646564206e657720636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_added_member', 'en', 0x436f75727365206d656d62657273686970);
INSERT INTO `lng_data` VALUES ('crs', 'crs_added_member_body', 'en', 0x596f752068617665206265656e20616363657074656420746f207468697320636f757273652e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_added_new_archive', 'en', 0x41206e6577206172636869766520686173206265656e206164646564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_admin', 'en', 0x41646d696e6973747261746f72);
INSERT INTO `lng_data` VALUES ('crs', 'crs_admin_no_notify', 'en', 0x41646d696e6973747261746f7220286e6f206e6f74696669636174696f6e29);
INSERT INTO `lng_data` VALUES ('crs', 'crs_admin_notify', 'en', 0x41646d696e6973747261746f7220286e6f74696669636174696f6e29);
INSERT INTO `lng_data` VALUES ('crs', 'crs_archive', 'en', 0x4172636869766573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_archive_disabled', 'en', 0x44697361626c6564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_archive_download', 'en', 0x446f776e6c6f6164);
INSERT INTO `lng_data` VALUES ('crs', 'crs_archive_lang', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('crs', 'crs_archive_read', 'en', 0x5265616420616363657373);
INSERT INTO `lng_data` VALUES ('crs', 'crs_archive_select_type', 'en', 0x41726368697665206163636573732074797065);
INSERT INTO `lng_data` VALUES ('crs', 'crs_archive_type', 'en', 0x417263686976652074797065);
INSERT INTO `lng_data` VALUES ('crs', 'crs_archive_type_disabled', 'en', 0x4e6f20616363657373);
INSERT INTO `lng_data` VALUES ('crs', 'crs_archives_deleted', 'en', 0x54686520617263686976652873292068617665206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_auto_fill', 'en', 0x4175746f2066696c6c);
INSERT INTO `lng_data` VALUES ('crs', 'crs_blocked', 'en', 0x4163636573732072656675736564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_blocked_member', 'en', 0x436f75727365206d656d62657273686970);
INSERT INTO `lng_data` VALUES ('crs', 'crs_blocked_member_body', 'en', 0x596f7572206d656d6265727368697020686173206265656e207465726d696e617465642e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_cancel_subscription', 'en', 0x436f75727365206465726567697374726174696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_cancel_subscription_body', 'en', 0x4120757365722068617320756e737562736372696265642066726f6d2074686520636f757273652e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_contact', 'en', 0x436f6e74616374);
INSERT INTO `lng_data` VALUES ('crs', 'crs_contact_consultation', 'en', 0x436f6e73756c746174696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_contact_email', 'en', 0x456d61696c);
INSERT INTO `lng_data` VALUES ('crs', 'crs_contact_name', 'en', 0x4e616d65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_contact_phone', 'en', 0x54656c6570686f6e65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_contact_responsibility', 'en', 0x526573706f6e736962696c697479);
INSERT INTO `lng_data` VALUES ('crs', 'crs_content', 'en', 0x436f7572736520636f6e74656e74);
INSERT INTO `lng_data` VALUES ('crs', 'crs_copy_cat_not_allowed', 'en', 0x4974206973206e6f7420706f737369626c6520746f20636f70792063617465676f72696573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_count_members', 'en', 0x4e756d626572206f66206d656d62657273);
INSERT INTO `lng_data` VALUES ('crs', 'crs_create_date', 'en', 0x4372656174652064617465);
INSERT INTO `lng_data` VALUES ('crs', 'crs_dates', 'en', 0x4461746573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_delete_member', 'en', 0x446561737369676e206d656d626572287329);
INSERT INTO `lng_data` VALUES ('crs', 'crs_delete_members_sure', 'en', 0x446f20796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e67206d656d626572732066726f6d207468697320636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_delete_subscribers', 'en', 0x44656c6574652073756273637269626572);
INSERT INTO `lng_data` VALUES ('crs', 'crs_delete_subscribers_sure', 'en', 0x446f20796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e67207573657273);
INSERT INTO `lng_data` VALUES ('crs', 'crs_details', 'en', 0x436f757273652064657461696c73);
INSERT INTO `lng_data` VALUES ('crs', 'crs_dismiss_member', 'en', 0x436f75727365206d656d62657273686970);
INSERT INTO `lng_data` VALUES ('crs', 'crs_dismiss_member_body', 'en', 0x596f75206d656d6265727368697020686173206265656e207465726d696e61746564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_edit_archive', 'en', 0x45646974204172636869766573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_end', 'en', 0x456e64);
INSERT INTO `lng_data` VALUES ('crs', 'crs_export', 'en', 0x436f75727365206578706f7274);
INSERT INTO `lng_data` VALUES ('crs', 'crs_file_name', 'en', 0x46696c65206e616d65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_from', 'en', 0x46726f6d3a);
INSERT INTO `lng_data` VALUES ('crs', 'crs_header_archives', 'en', 0x436f75727365206172636869766573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_header_delete_members', 'en', 0x44656c657465206d656d62657273);
INSERT INTO `lng_data` VALUES ('crs', 'crs_header_edit_members', 'en', 0x45646974206d656d62657273);
INSERT INTO `lng_data` VALUES ('crs', 'crs_header_members', 'en', 0x436f75727365206d656d62657273);
INSERT INTO `lng_data` VALUES ('crs', 'crs_html', 'en', 0x48544d4c);
INSERT INTO `lng_data` VALUES ('crs', 'crs_info_reg', 'en', 0x496e666f726d6174696f6e20666f7220726567697374726174696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_info_reg_confirmation', 'en', 0x5468697320636f75727365206e6565647320636f6e66696d6174696f6e206279206120636f757273652061646d696e6973747261746f722e20596f752077696c6c206765742061206d65737361676520696620796f752068617665206265656e2061737369676e656420746f2074686520636f757273652e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_info_reg_deactivated', 'en', 0x5468657265206973206e6f20726567697374726174696f6e20706f737369626c65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_info_reg_direct', 'en', 0x4974206973206e656365737361727920746f20726567697374657220746f207468697320636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_info_reg_password', 'en', 0x526567697374726174696f6e206e6565647320612076616c69642070617373776f72642e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_list_users', 'en', 0x4c697374207573657273);
INSERT INTO `lng_data` VALUES ('crs', 'crs_max_members_reached', 'en', 0x546865206d6178696d756d206e756d626572206f66206d656d6265727320686173206265656e2072656163686564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_mem_change_status', 'en', 0x4368616e676520737461747573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_mem_send_mail', 'en', 0x53656e64206d61696c);
INSERT INTO `lng_data` VALUES ('crs', 'crs_member', 'en', 0x4d656d626572);
INSERT INTO `lng_data` VALUES ('crs', 'crs_member_blocked', 'en', 0x4d6974676c6965642028756e626c6f636b656429);
INSERT INTO `lng_data` VALUES ('crs', 'crs_member_unblocked', 'en', 0x4d656d6265722028626c6f636b656429);
INSERT INTO `lng_data` VALUES ('crs', 'crs_member_updated', 'en', 0x546865206d656d62657220686173206265656e2075706461746564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_members_deleted', 'en', 0x44656c65746564206d656d62657273);
INSERT INTO `lng_data` VALUES ('crs', 'crs_members_print_title', 'en', 0x436f75727365206d656d62657273);
INSERT INTO `lng_data` VALUES ('crs', 'crs_members_title', 'en', 0x436f75727365206d656d62657273);
INSERT INTO `lng_data` VALUES ('crs', 'crs_move_down', 'en', 0x4d6f766520646f776e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_move_up', 'en', 0x4d6f7665207570);
INSERT INTO `lng_data` VALUES ('crs', 'crs_moved_item', 'en', 0x4d6f76656420636f75727365206974656d);
INSERT INTO `lng_data` VALUES ('crs', 'crs_new_search', 'en', 0x4e657720736561726368);
INSERT INTO `lng_data` VALUES ('crs', 'crs_new_subscription', 'en', 0x4e657720636f7572736520737562736372697074696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_new_subscription_body', 'en', 0x41206e6577207573657220686173206265656e2073756273637269626564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_archive_selected', 'en', 0x4e6f2061726368697665732073656c6563746564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_archives_available', 'en', 0x546865726520617265206e6f20617263686976657320617661696c61626c65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_archives_selected', 'en', 0x506c656173652073656c65637420616e2061726368697665);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_groups_selected', 'en', 0x506c656173652073656c65637420612067726f7570);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_items_found', 'en', 0x546865726520617265206e6f20636f75727365206974656d73);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_language', 'en', 0x4e6f206c616e6775616765);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_member_selected', 'en', 0x506c656173652073656c656374206f6e65206d656d626572);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_members_assigned', 'en', 0x546865726520617265206e6f206d656d626572732061737369676e656420746f207468697320636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_notify', 'en', 0x4e6f206e6f7469667920666f72206e657720726567697374726174696f6e73);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_results_found', 'en', 0x4e6f20726573756c747320666f756e64);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_roles_selected', 'en', 0x506c656173652073656c656374206120726f6c65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_subscribers_selected', 'en', 0x506c656173652073656c65637420612075736572);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_users_added', 'en', 0x4e6f206d656d62657273206164646564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_users_selected', 'en', 0x596f7520646964206e6f742073656c65637420616e792075736572);
INSERT INTO `lng_data` VALUES ('crs', 'crs_no_valid_member_id_given', 'en', 0x4e6f2076616c6964206d656d626572204964);
INSERT INTO `lng_data` VALUES ('crs', 'crs_not_available', 'en', 0x2d4e6f7420617661696c61626c652d);
INSERT INTO `lng_data` VALUES ('crs', 'crs_notify', 'en', 0x4e6f7469667920666f72206e657720726567697374726174696f6e73);
INSERT INTO `lng_data` VALUES ('crs', 'crs_number_users_added', 'en', 0x54686520666f6c6c6f77696e67206e756d626572206f6620757365727320686173206265656e2061737369676e656420746f2074686520636f757273653a);
INSERT INTO `lng_data` VALUES ('crs', 'crs_offline', 'en', 0x4f66666c696e65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_options', 'en', 0x4f7074696f6e73);
INSERT INTO `lng_data` VALUES ('crs', 'crs_passed', 'en', 0x506173736564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_password_not_valid', 'en', 0x596f75722070617373776f7264206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('crs', 'crs_pdf', 'en', 0x504446);
INSERT INTO `lng_data` VALUES ('crs', 'crs_print_list', 'en', 0x5072696e74206c697374);
INSERT INTO `lng_data` VALUES ('crs', 'crs_reg_subscription_deactivated', 'en', 0x526567697374726174696f6e2069732064697361626c6564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_reg_subscription_end_earlier', 'en', 0x54686520726567697374726174696f6e2074696d652069732065787069726564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_reg_subscription_max_members_reached', 'en', 0x546865206d6178696d756d206e756d626572206f662075736572732069732065786365656465642e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_reg_subscription_start_later', 'en', 0x54686520726567697374726174696f6e2074696d6520737461727473206c61746572);
INSERT INTO `lng_data` VALUES ('crs', 'crs_reg_until', 'en', 0x526567697374726174696f6e2064617465);
INSERT INTO `lng_data` VALUES ('crs', 'crs_reg_user_already_assigned', 'en', 0x596f752061726520616c72656164792061737369676e656420746f207468697320636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_reg_user_already_subscribed', 'en', 0x596f75206861766520616c7265616479207375627363726962656420746f207468697320636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_reg_user_blocked', 'en', 0x596f752061726520626c6f636b65642066726f6d207468697320636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_registration', 'en', 0x436f7572736520726567697374726174696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_reject_subscriber', 'en', 0x436f7572736520737562736372697074696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_reject_subscriber_body', 'en', 0x596f757220737562736372697074696f6e20686173206265656e206465636c696e65642e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_role', 'en', 0x526f6c65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_role_status', 'en', 0x526f6c65202f20537461747573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_search_enter_search_string', 'en', 0x506c6561736520656e74657220612073656172636820737472696e67);
INSERT INTO `lng_data` VALUES ('crs', 'crs_search_for', 'en', 0x53656172636820666f72);
INSERT INTO `lng_data` VALUES ('crs', 'crs_search_members', 'en', 0x5573657220736561726368);
INSERT INTO `lng_data` VALUES ('crs', 'crs_search_str', 'en', 0x536561726368207465726d);
INSERT INTO `lng_data` VALUES ('crs', 'crs_select_archive_language', 'en', 0x506c656173652073656c6563742061206c616e677561676520666f72207468652061726368697665);
INSERT INTO `lng_data` VALUES ('crs', 'crs_select_one_archive', 'en', 0x506c656173652073656c656374206f6e652061726368697665);
INSERT INTO `lng_data` VALUES ('crs', 'crs_settings', 'en', 0x436f757273652073657474696e6773);
INSERT INTO `lng_data` VALUES ('crs', 'crs_settings_saved', 'en', 0x53657474696e6773207361766564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_size', 'en', 0x46696c652073697a65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_sort_activation', 'en', 0x536f72742062792061637469766174696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_sort_manual', 'en', 0x536f7274206d616e75656c6c);
INSERT INTO `lng_data` VALUES ('crs', 'crs_sort_title', 'en', 0x536f7274206279207469746c65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_sortorder', 'en', 0x536f7274);
INSERT INTO `lng_data` VALUES ('crs', 'crs_start', 'en', 0x5374617274);
INSERT INTO `lng_data` VALUES ('crs', 'crs_status', 'en', 0x537461747573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_status_changed', 'en', 0x436f7572736520737461747573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_status_changed_body', 'en', 0x596f757220636f757273652073746174757320686173206265656e206368616e6765642e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_structure', 'en', 0x436f7572736520737472756374757265);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscribers', 'en', 0x537562736372697074696f6e73);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscribers_deleted', 'en', 0x44656c657465642073756273637269626572287329);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscription', 'en', 0x537562736372697074696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscription_max_members', 'en', 0x4d6178696d756d206f66206d656d62657273);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscription_notify', 'en', 0x4e6f7469667920666f7220737562736372697074696f6e73);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscription_options_confirmation', 'en', 0x537562736372697074696f6e207769746820636f6e6669726d6174696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscription_options_deactivated', 'en', 0x4465616374697661746564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscription_options_direct', 'en', 0x44697265637420737562736372697074696f6e);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscription_options_password', 'en', 0x537562736372697074696f6e20776974682070617373776f7264);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscription_successful', 'en', 0x596f752068617665206265656e207375627363726962656420746f207468697320636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_subscription_type', 'en', 0x537562736372697074696f6e2074797065);
INSERT INTO `lng_data` VALUES ('crs', 'crs_sure_delete_selected_archives', 'en', 0x41726520796f75207375726520746f2064656c657465207468652073656c6563746564206172636869766573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_syllabus', 'en', 0x53796c6c61627573);
INSERT INTO `lng_data` VALUES ('crs', 'crs_time', 'en', 0x537562736372697074696f6e2074696d65);
INSERT INTO `lng_data` VALUES ('crs', 'crs_to', 'en', 0x546f3a);
INSERT INTO `lng_data` VALUES ('crs', 'crs_tutor', 'en', 0x5475746f72);
INSERT INTO `lng_data` VALUES ('crs', 'crs_tutor_no_notify', 'en', 0x5475746f7220286e6f206e6f74696669636174696f6e29);
INSERT INTO `lng_data` VALUES ('crs', 'crs_tutor_notify', 'en', 0x5475746f7220286e6f74696669636174696f6e29);
INSERT INTO `lng_data` VALUES ('crs', 'crs_unblocked', 'en', 0x4672656520656e7472616e6365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_unblocked_member', 'en', 0x436f75727365206d656d62657273686970);
INSERT INTO `lng_data` VALUES ('crs', 'crs_unblocked_member_body', 'en', 0x596f7572206d656d6265727368697020686173206265656e20726573746f726564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_unlimited', 'en', 0x556e6c696d69746564);
INSERT INTO `lng_data` VALUES ('crs', 'crs_unsubscribe', 'en', 0x556e737562736372696265);
INSERT INTO `lng_data` VALUES ('crs', 'crs_unsubscribe_sure', 'en', 0x446f20796f752077616e7420746f20756e7375627363726962652066726f6d207468697320636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_unsubscribed_from_crs', 'en', 0x596f75207375627363726962656420746f207468697320636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_users_added', 'en', 0x4164646564207573657220746f2074686520636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_users_already_assigned', 'en', 0x546865207573657220697320616c72656164792061737369676e656420746f207468697320636f75727365);
INSERT INTO `lng_data` VALUES ('crs', 'crs_xml', 'en', 0x584d4c);
INSERT INTO `lng_data` VALUES ('crs_', 'crs_archives', 'en', 0x4172636869766573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'DateText', 'en', 0x4d657373616765);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Day_long', 'en', 0x4461792056696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_DATE_EXISTS', 'en', 0x54686973206461746520616c7265616479206578697374732e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_DB', 'en', 0x4461746162617365207772697465206572726f7221);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_DB_CONNECT', 'en', 0x54686520646174616261736520697320646973636f6e6e65637465642e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_DEL_MSG', 'en', 0x54686973206973206120726563757272696e6720646174652e20576f756c6420796f75206c696b6520746f2064656c657465206f6e652064617465206f722074686520736572696573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_ENDDATE', 'en', 0x57726f6e6720656e64206461746520696e7075742e20466f726d61743a2064642f6d6d2f79797979);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_ENDTIME', 'en', 0x57726f6e6720656e642074696d652e20466f726d61743a2068683a6d6d);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_END_START', 'en', 0x57726f6e6720656e64206461746520696e7075742e20456e642064617465206973206265666f726520737461727420646174652e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_FILE_CSV_MSG', 'en', 0x506c656173652074616b6520612076616c6964204353562046696c652e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_JAVASCRIPT', 'en', 0x3c423e3c43454e5445523e796f75722062726f7773657220646f65736e277420616363657074204a6176615363726970742e3c6272202f3e506c6561736520636c6f736520746869732057696e646f7720616674657220696e73657274206d616e75616c6c792e3c6272202f3e5468616e6b20796f753c2f43454e5445523e3c2f423e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_ROTATIONEND', 'en', 0x57726f6e6720726563757272696e6720656e64206461746520696e7075742e20466f726d61743a2064642f6d6d2f79797979);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_ROT_END_START', 'en', 0x57726f6e6720726563757272696e6720656e64206461746520696e7075742e20526563757272696e6720656e642064617465206973206265666f726520737461727420646174652e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_SHORTTEXT', 'en', 0x57726f6e672073686f7274207465787420696e7075742e2053686f727420746578742069732072657175697265642e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_STARTDATE', 'en', 0x57726f6e67207374617274206461746520696e7075742e20466f726d61743a2064642f6d6d2f79797979);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_STARTTIME', 'en', 0x57726f6e672073746172742074696d652e20466f726d61743a2068683a6d6d);
INSERT INTO `lng_data` VALUES ('dateplaner', 'ERROR_TS', 'en', 0x496e636f7272656374206461746520666f726d6174);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Fr_long', 'en', 0x467269646179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Fr_short', 'en', 0x4672);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Language_file', 'en', 0x456e676c697368);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Listbox_long', 'en', 0x4c697374206f66204461746573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Mo_long', 'en', 0x4d6f6e646179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Mo_short', 'en', 0x4d6f);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Month_long', 'en', 0x4d6f6e74682056696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Sa_long', 'en', 0x5361747572646179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Sa_short', 'en', 0x5361);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Su_long', 'en', 0x53756e646179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Su_short', 'en', 0x5375);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Text', 'en', 0x54657874);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Th_long', 'en', 0x5468757273646179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Th_short', 'en', 0x5468);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Tu_long', 'en', 0x54756573646179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Tu_short', 'en', 0x5475);
INSERT INTO `lng_data` VALUES ('dateplaner', 'View', 'en', 0x566965773a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'We_long', 'en', 0x5765646e6573646179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'We_short', 'en', 0x5765);
INSERT INTO `lng_data` VALUES ('dateplaner', 'Week_long', 'en', 0x5765656b2056696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'add_data', 'en', 0x4164646974696f6e616c2064617461);
INSERT INTO `lng_data` VALUES ('dateplaner', 'app_', 'en', 0x67726f757020646174657320696e626f78);
INSERT INTO `lng_data` VALUES ('dateplaner', 'app_date', 'en', 0x696e736572742064617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'app_day', 'en', 0x6461792076696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'app_freetime', 'en', 0x667265652074696d65206f66206f746865722067726f7570206d656d626572732028636174636820612074696d6529);
INSERT INTO `lng_data` VALUES ('dateplaner', 'app_inbox', 'en', 0x67726f757020646174657320696e626f78);
INSERT INTO `lng_data` VALUES ('dateplaner', 'app_list', 'en', 0x64617465206c697374);
INSERT INTO `lng_data` VALUES ('dateplaner', 'app_month', 'en', 0x6d6f6e74682076696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'app_properties', 'en', 0x70726f70657274696573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'app_week', 'en', 0x7765656b2076696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'application', 'en', 0x43616c656e646172);
INSERT INTO `lng_data` VALUES ('dateplaner', 'apply', 'en', 0x6170706c79);
INSERT INTO `lng_data` VALUES ('dateplaner', 'back', 'en', 0x6261636b);
INSERT INTO `lng_data` VALUES ('dateplaner', 'begin_date', 'en', 0x426567696e2044617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'busytime', 'en', 0x627573792074696d6520696e2067726f7570);
INSERT INTO `lng_data` VALUES ('dateplaner', 'c_date', 'en', 0x4372656174652044617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'changeTo', 'en', 0x4368616e676520746f3a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'changedDates', 'en', 0x4368616e676564206461746573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'checkforfreetime', 'en', 0x436865636b20666f7220667265652074696d65);
INSERT INTO `lng_data` VALUES ('dateplaner', 'created', 'en', 0x43726561746564);
INSERT INTO `lng_data` VALUES ('dateplaner', 'date', 'en', 0x44617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'date_format', 'en', 0x6d2f642f5920483a69);
INSERT INTO `lng_data` VALUES ('dateplaner', 'date_format_middle', 'en', 0x6d2f642f79);
INSERT INTO `lng_data` VALUES ('dateplaner', 'del_all', 'en', 0x64656c65746520616c6c206461746573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'del_one', 'en', 0x64656c657465206f6e652064617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'delete', 'en', 0x64656c657465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'deletedDates', 'en', 0x44656c65746564206461746573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'discard', 'en', 0x64697363617264);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_Grouptime', 'en', 0x667265652074696d65);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_button_cancel', 'en', 0x43616e63656c);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_button_delete', 'en', 0x44656c657465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_button_insert', 'en', 0x496e73657274);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_button_update', 'en', 0x557064617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_date_begin', 'en', 0x537461727420446174653a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_date_end', 'en', 0x456e6420446174653a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_date_hm', 'en', 0x486f75723a4d696e757465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_enddate', 'en', 0x456e6420646174653a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_groupselect', 'en', 0x47726f75702073656c6563743a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_groupview', 'en', 0x47726f757020646174653a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_groupview_text', 'en', 0x47726f75703a3c6272202f3e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_keywords', 'en', 0x4b6579776f7264733a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_keywordselect', 'en', 0x4b6579776f72642073656c6563743a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_message', 'en', 0x4d6573736167653a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_noGroup', 'en', 0x6e6f2047726f7570);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_noKey', 'en', 0x6e6f204b6579776f7264);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_preview', 'en', 0x3c623e21205072657669657720213c2f623e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_rotation', 'en', 0x526563757272656e63653a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_rotation_date', 'en', 0x526563757272656e636520646174653a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_rotation_end', 'en', 0x526563757272656e636520656e643a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_shorttext', 'en', 0x53686f727420746578743a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_startdate', 'en', 0x537461727420646174653a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_text', 'en', 0x546578743a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_whole_day', 'en', 0x57686f6c65206461793a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'dv_whole_rotation', 'en', 0x576f756c6420796f75206c696b6520746f2064656c6574652074686520656e7469726520726563757272696e67207365726965733f2028636c69636b2074686520636865636b626f7829);
INSERT INTO `lng_data` VALUES ('dateplaner', 'end_date', 'en', 0x456e642044617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'endtime_for_view', 'en', 0x456e642074696d6520666f7220766965773a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'error_minical_img', 'en', 0x43616e6e6f74206372656174652047442d706963747572652d73747265616d);
INSERT INTO `lng_data` VALUES ('dateplaner', 'execute', 'en', 0x657865637574652063686f69636573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'extra_dates', 'en', 0x4f6e6520446179204461746573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'free', 'en', 0x66726565);
INSERT INTO `lng_data` VALUES ('dateplaner', 'freetime', 'en', 0x667265652054696d6520696e2067726f7570);
INSERT INTO `lng_data` VALUES ('dateplaner', 'group', 'en', 0x67726f7570);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_day', 'en', 0x43616c656e646172206461792076696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_day_l', 'en', 0x496e2074686520646179207669657720616c6c206461746573206f6620612063686f73656e206461792061726520646973706c617965642e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_enddate', 'en', 0x456e642064617465202d2073796e7461785b64642f6d6d2f797979795d);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_enddate_l', 'en', 0x436f6e7461696e732074686520656e64706f696e74206f6620746865206170706f696e746d656e742e3c6272202f3e5468652073796d626f6c206f70656e73206120736d616c6c2063616c656e64617220666f722063686f6f73696e672074686520617070726f70726961746520646174652e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_endrotation', 'en', 0x456e642074696d652073796e7461785b64642f6d6d2f797979795d);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_endrotation_l', 'en', 0x4174207468697320646179207468652072657065746974696f6e2077696c6c20656e642e3c6272202f3e5468652073796d626f6c206f70656e73206120736d616c6c2063616c656e64617220666f722063686f6f73696e672074686520617070726f7072696174652064617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_endtime', 'en', 0x456e642074696d65202d2073796e7461785b68682f6d6d5d2032342d686f757220666f726d6174);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_endtime_l', 'en', 0x436f6e7461696e732074686520656e642074696d65206f6620746865206170706f696e746d656e74207573696e672032342d686f757220666f726d61742e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_filename', 'en', 0x64705f68656c705f656e2e68746d6c);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_fullday', 'en', 0x57686f6c65206461792064617465202d2073796e7461785b6f6e2f6f66665d);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_fullday_l', 'en', 0x496620746865206461746520737472657463686573206f76657220612077686f6c65206461792c20706c656173652063686f6f73652074686973206f7074696f6e2e3c6272202f3e546869732077696c6c2068696465207468652073746172742074696d652c20656e642074696d652c20616e6420656e6420646174652e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_further', 'en', 0x3c6272202f3e3c623e636c69636b206865726520666f72206675727468657220696e666f726d6174696f6e2e3c2f623e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_group', 'en', 0x47726f75702064617465732073656c656374696f6e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_group_l', 'en', 0x436f6e7461696e73207468652067726f75707320666f7220776869636820746865206c6f676765642d696e2075736572206861732061646d696e6973747261746f722072696768747320616e6420666f72207768696368206120646174652063616e20626520637265617465642e3c6272202f3e41667465722063686f6f73696e67207468697320697420697320706f737369626c6520746f20766965772074686520756e7363686564756c65642074696d65206f66207468652067726f75702e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_inbox', 'en', 0x43616c656e64617220496e626f78);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_inbox_l', 'en', 0x4865726520616c6c206e65772c206368616e6765642c20616e642064656c657465642067726f75702064617465732061726520646973706c617965642e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_keyword', 'en', 0x4b6579776f72642073656c656374696f6e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_keywordUse', 'en', 0x4b6579776f72642073656c656374696f6e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_keywordUse_l', 'en', 0x43686f6f7365206f6e65206f72206d6f7265206b6579776f7264732066726f6d20746865206469616c6f6720626f782e3c6272202f3e5449503a2075736520746865204354524c206b657920666f722073656c656374696e67207365766572616c20656e74726965732e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_keyword_l', 'en', 0x436f6e7461696e7320746865206b6579776f726473207768696368206120757365722063616e206173736f636961746520746f2064617465732e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_list', 'en', 0x43616c656e6461722064617465206c697374);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_list_l', 'en', 0x496e207468652064617465206c6973742c20616c6c206461746573206f766572206120676976656e20706572696f64206f662074696d652061726520646973706c617965642e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_month', 'en', 0x43616c656e646172206d6f6e74682076696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_month_l', 'en', 0x496e20746865206d6f6e7468207669657720616c6c206461746573206f66206120676976656e206d6f6e74682061726520646973706c617965642e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_properties_alt', 'en', 0x4368616e67657320746865206b6579776f7264);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_properties_alt_l', 'en', 0x43686f6f73652061206b6579776f72642066726f6d20746865206469616c6f6720626f782e3c6272202f3e4368616e676520746865206b6579776f726420696e207468652074657874206669656c642e20496d706f7274616e743a206d61782e20323020636861726163746572732e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_properties_del', 'en', 0x44656c6574657320746865206b6579776f7264);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_properties_del_l', 'en', 0x43686f6f73652061206b6579776f72642066726f6d20746865206469616c6f6720626f782e20436c69636b2074686520627574746f6e20746f2064656c65746520746865206b6579776f72642e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_properties_import', 'en', 0x496d706f7274206f66204f75746c6f6f6b2d4353562d46696c6573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_properties_import_l', 'en', 0x486572652061204353432d46696c652063616e2062652073656c65637465642c20776869636820636f6e7461696e73204f75746c6f6f6b2d44617465732e2054686573652064617465732077696c6c20626520696d706f7274656420696e746f207468652063616c656e6461722e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_properties_new', 'en', 0x4164642061206b6579776f72642c206d61782e2032302063686172616374657273);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_properties_new_l', 'en', 0x4164642061206e6577206b6579776f726420746f20746865206669656c64205c226e65775c222e20496d706f7274616e743a206d61782e20323020636861726163746572732e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_properties_time', 'en', 0x446973706c61792074696d6520666f722064617920766965772f7765656b2076696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_properties_time_l', 'en', 0x546865207374616e6461726420646973706c61792074696d6520666f722074686520646179207669657720616e64207765656b20766965772063616e206265206368616e6765642e20496620646174657320617265206f757473696465206f662074686520646973706c61792074696d6520666f72206120676976656e2064617973206f72207765656b2c2074686520646973706c61792074696d652077696c6c206265206368616e67656420666f7220746861742074696d65207370616e2e205374616e64617264206973203868202d3138682e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_rotation', 'en', 0x526563757272656e6365);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_rotation_l', 'en', 0x436f6e7461696e73207468652074797065206f662074686520726563757272656e6365202f2072657065746974696f6e2e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_shorttext', 'en', 0x53686f727420746578742c206d61782e2035302063686172616374657273);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_shorttext_l', 'en', 0x48657265206f6e652063616e20656e746572207468652073686f7274207465787420666f72206120676976656e20646174652e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_startdate', 'en', 0x53746172742064617465202d2073796e7461785b64642f6d6d2f797979795d);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_startdate_l', 'en', 0x436f6e7461696e732074686520737461727420706f696e74206f6620746865206170706f696e746d656e742e3c6272202f3e5468652073796d626f6c206f70656e73206120736d616c6c2063616c656e64617220666f722063686f6f73696e672074686520617070726f70726961746520646174652e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_starttime', 'en', 0x53746172742074696d65202d2073796e7461785b68682f6d6d5d2032342d686f757220666f726d6174);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_starttime_l', 'en', 0x436f6e7461696e732074686520737461727420706f696e74206f6620746865206170706f696e746d656e74207573696e672032342d686f757220666f726d61742e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_text', 'en', 0x54657874);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_text_l', 'en', 0x48657265206f6e652063616e20656e74657220746865207465787420666f72206120676976656e20646174652e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_week', 'en', 0x43616c656e646172207765656b2076696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'help_week_l', 'en', 0x496e20746865207765656b207669657720616c6c206461746573206f66206120676976656e207765656b2061726520646973706c617965642e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'hour', 'en', 0x686f7572);
INSERT INTO `lng_data` VALUES ('dateplaner', 'importDates', 'en', 0x496d706f7274206f66204f75746c6f6f6b204461746573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'inbox', 'en', 0x496e626f78);
INSERT INTO `lng_data` VALUES ('dateplaner', 'insertImportDates', 'en', 0x54686573652064617465732077657265207375636365737366756c6c7920726567697374657265643a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'k_alldates', 'en', 0x616c6c206461746573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'keep', 'en', 0x6b656570);
INSERT INTO `lng_data` VALUES ('dateplaner', 'keyword', 'en', 0x4b6579776f7264);
INSERT INTO `lng_data` VALUES ('dateplaner', 'keywords', 'en', 0x4b6579776f726473);
INSERT INTO `lng_data` VALUES ('dateplaner', 'last_change', 'en', 0x4c617374206368616e676564);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_01', 'en', 0x4a616e75617279);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_02', 'en', 0x4665627275617279);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_03', 'en', 0x4d61726368);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_04', 'en', 0x417072696c);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_05', 'en', 0x4d6179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_06', 'en', 0x4a756e65);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_07', 'en', 0x4a756c79);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_08', 'en', 0x417567757374);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_09', 'en', 0x53657074656d626572);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_10', 'en', 0x4f63746f626572);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_11', 'en', 0x4e6f76656d626572);
INSERT INTO `lng_data` VALUES ('dateplaner', 'long_12', 'en', 0x446563656d626572);
INSERT INTO `lng_data` VALUES ('dateplaner', 'main_dates', 'en', 0x4d61696e204461746573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'menue', 'en', 0x4d656e75);
INSERT INTO `lng_data` VALUES ('dateplaner', 'mmonth', 'en', 0x6c617374206d6f6e7468);
INSERT INTO `lng_data` VALUES ('dateplaner', 'month', 'en', 0x6d6f6e7468);
INSERT INTO `lng_data` VALUES ('dateplaner', 'more', 'en', 0x6d6f7265);
INSERT INTO `lng_data` VALUES ('dateplaner', 'mweek', 'en', 0x6c617374207765656b);
INSERT INTO `lng_data` VALUES ('dateplaner', 'myeahr', 'en', 0x6c6173742079656172);
INSERT INTO `lng_data` VALUES ('dateplaner', 'newDates', 'en', 0x4e6577206461746573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'newKeyword', 'en', 0x4e65773a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'new_doc', 'en', 0x637265617465206e65772064617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'no_entry', 'en', 0x6e6f20646174657320617661696c61626c65);
INSERT INTO `lng_data` VALUES ('dateplaner', 'o_day_date', 'en', 0x32342d686f75722064617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'of', 'en', 0x6f66);
INSERT INTO `lng_data` VALUES ('dateplaner', 'open_day', 'en', 0x6461792076696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'pmonth', 'en', 0x6e657874206d6f6e7468);
INSERT INTO `lng_data` VALUES ('dateplaner', 'printlist', 'en', 0x7072696e74);
INSERT INTO `lng_data` VALUES ('dateplaner', 'properties', 'en', 0x50726f70657274696573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'pweek', 'en', 0x6e657874207765656b);
INSERT INTO `lng_data` VALUES ('dateplaner', 'pyear', 'en', 0x6e6578742079656172);
INSERT INTO `lng_data` VALUES ('dateplaner', 'r_14', 'en', 0x65766572792031342064617973);
INSERT INTO `lng_data` VALUES ('dateplaner', 'r_4_weeks', 'en', 0x65766572792034207765656b73);
INSERT INTO `lng_data` VALUES ('dateplaner', 'r_day', 'en', 0x657665727920646179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'r_halfyear', 'en', 0x65766572792068616c662079656172);
INSERT INTO `lng_data` VALUES ('dateplaner', 'r_month', 'en', 0x6576657279206d6f6e7468);
INSERT INTO `lng_data` VALUES ('dateplaner', 'r_nonrecurring', 'en', 0x4f6e6365);
INSERT INTO `lng_data` VALUES ('dateplaner', 'r_week', 'en', 0x6576657279207765656b);
INSERT INTO `lng_data` VALUES ('dateplaner', 'r_year', 'en', 0x65766572792079656172);
INSERT INTO `lng_data` VALUES ('dateplaner', 'readimportfile', 'en', 0x696e73657274);
INSERT INTO `lng_data` VALUES ('dateplaner', 'rotation', 'en', 0x526563757272656e6365);
INSERT INTO `lng_data` VALUES ('dateplaner', 'rotation_dates', 'en', 0x526563757272656e6365206461746573);
INSERT INTO `lng_data` VALUES ('dateplaner', 'semester_name', 'en', 0x53656d6573746572204e616d65);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_01', 'en', 0x4a616e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_02', 'en', 0x466562);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_03', 'en', 0x4d6172);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_04', 'en', 0x417072);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_05', 'en', 0x4d6179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_06', 'en', 0x4a756e);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_07', 'en', 0x4a756c);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_08', 'en', 0x417567);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_09', 'en', 0x536570);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_10', 'en', 0x4f6374);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_11', 'en', 0x4e6f76);
INSERT INTO `lng_data` VALUES ('dateplaner', 'short_12', 'en', 0x446563);
INSERT INTO `lng_data` VALUES ('dateplaner', 'shorttext', 'en', 0x53686f72742054657874);
INSERT INTO `lng_data` VALUES ('dateplaner', 'singleDate', 'en', 0x73696e676c652064617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'starttime_for_view', 'en', 0x53746172742074696d6520666f7220766965773a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'timedate', 'en', 0x54696d65202f2044617465);
INSERT INTO `lng_data` VALUES ('dateplaner', 'timeslice', 'en', 0x54696d653a);
INSERT INTO `lng_data` VALUES ('dateplaner', 'title', 'en', 0x494c4941532063616c656e646172);
INSERT INTO `lng_data` VALUES ('dateplaner', 'to', 'en', 0x746f);
INSERT INTO `lng_data` VALUES ('dateplaner', 'today', 'en', 0x746f646179);
INSERT INTO `lng_data` VALUES ('dateplaner', 'viewlist', 'en', 0x76696577);
INSERT INTO `lng_data` VALUES ('dateplaner', 'week', 'en', 0x7765656b);
INSERT INTO `lng_data` VALUES ('dateplaner', 'year', 'en', 0x79656172);
INSERT INTO `lng_data` VALUES ('forum', 'forums', 'en', 0x466f72756d73);
INSERT INTO `lng_data` VALUES ('forum', 'forums_articles', 'en', 0x41727469636c6573);
INSERT INTO `lng_data` VALUES ('forum', 'forums_attachments', 'en', 0x4174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('forum', 'forums_attachments_add', 'en', 0x416464206174746163686d656e74);
INSERT INTO `lng_data` VALUES ('forum', 'forums_attachments_edit', 'en', 0x45646974206174746163686d656e74);
INSERT INTO `lng_data` VALUES ('forum', 'forums_available', 'en', 0x417661696c61626c6520466f72756d73);
INSERT INTO `lng_data` VALUES ('forum', 'forums_censor_comment', 'en', 0x436f6d6d656e74206f662043656e736f72);
INSERT INTO `lng_data` VALUES ('forum', 'forums_count', 'en', 0x4e756d626572206f6620466f72756d73);
INSERT INTO `lng_data` VALUES ('forum', 'forums_count_art', 'en', 0x4e756d626572206f662041727469636c6573);
INSERT INTO `lng_data` VALUES ('forum', 'forums_count_thr', 'en', 0x4e756d626572206f662054687265616473);
INSERT INTO `lng_data` VALUES ('forum', 'forums_delete_file', 'en', 0x44656c657465206174746163686d656e74);
INSERT INTO `lng_data` VALUES ('forum', 'forums_download_attachment', 'en', 0x446f776e6c6f61642066696c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_edit_post', 'en', 0x456469742041727469636c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_censor2_post', 'en', 0x5265766f6b652043656e736f72736869703f);
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_censor_post', 'en', 0x41726520796f75207375726520796f752077616e7420746f206869646520746869732061727469636c653f);
INSERT INTO `lng_data` VALUES ('forum', 'forums_info_delete_post', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c65746520746869732061727469636c6520696e636c7564696e6720616e7920726573706f6e7365733f);
INSERT INTO `lng_data` VALUES ('forum', 'forums_last_post', 'en', 0x4c6173742041727469636c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_moderators', 'en', 0x4d6f64657261746f7273);
INSERT INTO `lng_data` VALUES ('forum', 'forums_new_entries', 'en', 0x4e657720466f72756d7320456e7472696573);
INSERT INTO `lng_data` VALUES ('forum', 'forums_new_thread', 'en', 0x4e657720546f706963);
INSERT INTO `lng_data` VALUES ('forum', 'forums_not_available', 'en', 0x466f72756d73204e6f7420417661696c61626c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_overview', 'en', 0x466f72756d73204f76657276696577);
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_deleted', 'en', 0x41727469636c6520686173206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_modified', 'en', 0x41727469636c6520686173206265656e206d6f646966696564);
INSERT INTO `lng_data` VALUES ('forum', 'forums_post_new_entry', 'en', 0x4e65772061727469636c6520686173206265656e2063726561746564);
INSERT INTO `lng_data` VALUES ('forum', 'forums_posts', 'en', 0x416c6c2061727469636c6573);
INSERT INTO `lng_data` VALUES ('forum', 'forums_posts_not_available', 'en', 0x41727469636c6573204e6f7420417661696c61626c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_print_thread', 'en', 0x5072696e7420546872656164);
INSERT INTO `lng_data` VALUES ('forum', 'forums_quote', 'en', 0x51756f7465);
INSERT INTO `lng_data` VALUES ('forum', 'forums_respond', 'en', 0x506f7374205265706c79);
INSERT INTO `lng_data` VALUES ('forum', 'forums_subject', 'en', 0x5375626a656374);
INSERT INTO `lng_data` VALUES ('forum', 'forums_the_post', 'en', 0x41727469636c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread', 'en', 0x546f706963);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_articles', 'en', 0x41727469636c6573206f662074686520746f706963);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_create_date', 'en', 0x43726561746564206174);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_create_from', 'en', 0x437265617465642066726f6d);
INSERT INTO `lng_data` VALUES ('forum', 'forums_thread_new_entry', 'en', 0x4e657720746f70696320686173206265656e2063726561746564);
INSERT INTO `lng_data` VALUES ('forum', 'forums_threads', 'en', 0x54687265616473);
INSERT INTO `lng_data` VALUES ('forum', 'forums_threads_not_available', 'en', 0x546f70696373204e6f7420417661696c61626c65);
INSERT INTO `lng_data` VALUES ('forum', 'forums_topics_overview', 'en', 0x546f70696373204f76657276696577);
INSERT INTO `lng_data` VALUES ('forum', 'forums_your_reply', 'en', 0x596f7572205265706c79);
INSERT INTO `lng_data` VALUES ('mail', 'also_as_email', 'en', 0x416c736f20617320456d61696c);
INSERT INTO `lng_data` VALUES ('mail', 'bc', 'en', 0x424343);
INSERT INTO `lng_data` VALUES ('mail', 'forward', 'en', 0x466f7277617264);
INSERT INTO `lng_data` VALUES ('mail', 'linebreak', 'en', 0x4c696e6520627265616b);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_recipient', 'en', 0x506c6561736520656e746572206120726563697069656e74);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_subfolder', 'en', 0x41646420537562666f6c646572);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_subject', 'en', 0x506c6561736520656e7465722061207375626a656374);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_to_addressbook', 'en', 0x41646420746f206164647265737320626f6f6b);
INSERT INTO `lng_data` VALUES ('mail', 'mail_add_type', 'en', 0x506c6561736520616464207468652074797065206f66206d61696c20796f752077616e7420746f2073656e64);
INSERT INTO `lng_data` VALUES ('mail', 'mail_addr_entries', 'en', 0x456e7472696573);
INSERT INTO `lng_data` VALUES ('mail', 'mail_attachments', 'en', 0x4174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('mail', 'mail_bc_not_valid', 'en', 0x5468652042434320726563697069656e74206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_bc_search', 'en', 0x42434320736561726368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_byte', 'en', 0x42797465);
INSERT INTO `lng_data` VALUES ('mail', 'mail_cc_not_valid', 'en', 0x54686520434320726563697069656e74206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_cc_search', 'en', 0x434320736561726368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_change_to_folder', 'en', 0x53776974636820746f20666f6c6465723a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_check_your_email_addr', 'en', 0x596f757220656d61696c2061646472657373206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_compose', 'en', 0x436f6d706f7365204d657373616765);
INSERT INTO `lng_data` VALUES ('mail', 'mail_deleted', 'en', 0x546865206d61696c2069732064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_deleted_entry', 'en', 0x54686520656e7472696573206172652064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_email_forbidden', 'en', 0x596f7520617265206e6f7420616c6c6f77656420746f2073656e6420656d61696c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_entry_added', 'en', 0x4164646564206e657720456e747279);
INSERT INTO `lng_data` VALUES ('mail', 'mail_entry_changed', 'en', 0x54686520656e747279206973206368616e676564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_file_name', 'en', 0x46696c65206e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_file_size', 'en', 0x46696c652073697a65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_files_deleted', 'en', 0x5468652066696c65287329206172652064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_created', 'en', 0x41206e657720666f6c64657220686173206265656e2063726561746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_deleted', 'en', 0x54686520666f6c64657220686173206265656e2064656c65746564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_exists', 'en', 0x4120666f6c64657220616c72656164792065786973747320776974682074686973206e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_name', 'en', 0x466f6c646572206e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_name_changed', 'en', 0x54686520666f6c64657220686173206265656e2072656e616d6564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_folder_options', 'en', 0x466f6c646572204f7074696f6e73);
INSERT INTO `lng_data` VALUES ('mail', 'mail_following_rcp_not_valid', 'en', 0x54686520666f6c6c6f77696e6720726563697069656e747320617265206e6f742076616c69643a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_global_options', 'en', 0x476c6f62616c206f7074696f6e73);
INSERT INTO `lng_data` VALUES ('mail', 'mail_incoming', 'en', 0x496e636f6d696e67206d61696c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_incoming_both', 'en', 0x6c6f63616c20616e6420666f7277617264696e67);
INSERT INTO `lng_data` VALUES ('mail', 'mail_incoming_local', 'en', 0x6f6e6c79206c6f63616c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_incoming_smtp', 'en', 0x666f727761726420746f20656d61696c2061646472657373);
INSERT INTO `lng_data` VALUES ('mail', 'mail_insert_folder_name', 'en', 0x506c6561736520696e73657274206120666f6c646572206e616d65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_insert_query', 'en', 0x506c6561736520696e736572742061207175657279);
INSERT INTO `lng_data` VALUES ('mail', 'mail_intern', 'en', 0x496e7465726e616c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_mark_read', 'en', 0x4d61726b206d61696c2072656164);
INSERT INTO `lng_data` VALUES ('mail', 'mail_mark_unread', 'en', 0x4d61726b206d61696c20756e72656164);
INSERT INTO `lng_data` VALUES ('mail', 'mail_maxsize_attachment_error', 'en', 0x5468652075706c6f6164206c696d69742069733a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_message_send', 'en', 0x4d6573736167652073656e74);
INSERT INTO `lng_data` VALUES ('mail', 'mail_move_error', 'en', 0x4572726f72206d6f76696e67206d61696c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_move_to', 'en', 0x4d6f766520746f3a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_moved', 'en', 0x546865206d61696c20686173206265656e206d6f766564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_moved_to_trash', 'en', 0x4d61696c20686173206265656e206d6f76656420746f207472617368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_new_file', 'en', 0x416464206e65772066696c653a);
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_attach_allowed', 'en', 0x53797374656d206d6573736167657320617265206e6f7420616c6c6f77656420746f20636f6e7461696e206174746163686d656e7473);
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_attachments_found', 'en', 0x4e6f206174746163686d656e747320666f756e64);
INSERT INTO `lng_data` VALUES ('mail', 'mail_no_permissions_write_smtp', 'en', 0x596f752068617665206e6f207065726d697373696f6e20746f2077726974652065787465726e616c206d61696c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_options_of', 'en', 0x4f7074696f6e73);
INSERT INTO `lng_data` VALUES ('mail', 'mail_options_saved', 'en', 0x4f7074696f6e73207361766564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_recipient_not_valid', 'en', 0x54686520726563697069656e74206973206e6f742076616c6964);
INSERT INTO `lng_data` VALUES ('mail', 'mail_s', 'en', 0x4d61696c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_s_unread', 'en', 0x556e72656164204d61696c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_saved', 'en', 0x546865206d65737361676520686173206265656e207361766564);
INSERT INTO `lng_data` VALUES ('mail', 'mail_search_addressbook', 'en', 0x73656172636820696e206164647265737320626f6f6b);
INSERT INTO `lng_data` VALUES ('mail', 'mail_search_system', 'en', 0x73656172636820696e2073797374656d);
INSERT INTO `lng_data` VALUES ('mail', 'mail_select_one_entry', 'en', 0x596f75206d7573742073656c656374206f6e6520656e747279);
INSERT INTO `lng_data` VALUES ('mail', 'mail_select_one_file', 'en', 0x596f75206d7573742073656c656374206f6e652066696c65);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete', 'en', 0x417265207375726520796f752077616e7420746f2064656c65746520746865206d61726b6564206d61696c);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_entry', 'en', 0x417265207375726520796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e6720656e7472696573);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_file', 'en', 0x5468652066696c652077696c6c2062652072656d6f766564207065726d616e656e746c79);
INSERT INTO `lng_data` VALUES ('mail', 'mail_sure_delete_folder', 'en', 0x54686520666f6c64657220616e642069747320636f6e74656e74732077696c6c2062652072656d6f766564207065726d616e656e746c79);
INSERT INTO `lng_data` VALUES ('mail', 'mail_to_search', 'en', 0x546f20736561726368);
INSERT INTO `lng_data` VALUES ('mail', 'mail_user_addr_n_valid', 'en', 0x466f6c6c6f77696e672075736572732068617665206e6f2076616c696420656d61696c2061646472657373);
INSERT INTO `lng_data` VALUES ('mail', 'search_bc_recipient', 'en', 0x5365617263682042434320526563697069656e74);
INSERT INTO `lng_data` VALUES ('mail', 'search_cc_recipient', 'en', 0x53656172636820434320526563697069656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_accessibility_restrictions', 'en', 0x4163636573736962696c697479205265737472696374696f6e73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_active', 'en', 0x416374697665);
INSERT INTO `lng_data` VALUES ('meta', 'meta_add', 'en', 0x416464);
INSERT INTO `lng_data` VALUES ('meta', 'meta_annotation', 'en', 0x416e6e6f746174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_atomic', 'en', 0x41746f6d6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_author', 'en', 0x417574686f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_browser', 'en', 0x42726f77736572);
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
INSERT INTO `lng_data` VALUES ('meta', 'meta_catalog', 'en', 0x436174616c6f67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_choose_element', 'en', 0x506c656173652063686f6f736520616e20656c656d656e7420796f752077616e7420746f2061646421);
INSERT INTO `lng_data` VALUES ('meta', 'meta_choose_language', 'en', 0x506c656173652063686f6f73652061206c616e6775616765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_choose_section', 'en', 0x506c656173652063686f6f736520612073656374696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_classification', 'en', 0x436c617373696669636174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_collection', 'en', 0x436f6c6c656374696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_competency', 'en', 0x436f6d706574656e6379);
INSERT INTO `lng_data` VALUES ('meta', 'meta_context', 'en', 0x436f6e74657874);
INSERT INTO `lng_data` VALUES ('meta', 'meta_contribute', 'en', 0x436f6e74726962757465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_copyright_and_other_restrictions', 'en', 0x436f7079726967687420616e64204f74686572205265737472696374696f6e73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_cost', 'en', 0x436f7374);
INSERT INTO `lng_data` VALUES ('meta', 'meta_coverage', 'en', 0x436f766572616765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_date', 'en', 0x44617465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_delete', 'en', 0x44656c657465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_description', 'en', 0x4465736372697074696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_diagramm', 'en', 0x4469616772616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_difficulty', 'en', 0x446966666963756c7479);
INSERT INTO `lng_data` VALUES ('meta', 'meta_dificult', 'en', 0x446966666963756c74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_draft', 'en', 0x4472616674);
INSERT INTO `lng_data` VALUES ('meta', 'meta_duration', 'en', 0x4475726174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_easy', 'en', 0x45617379);
INSERT INTO `lng_data` VALUES ('meta', 'meta_education', 'en', 0x456475636174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_educational_level', 'en', 0x456475636174696f6e616c204c6576656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_educational_objective', 'en', 0x456475636174696f6e616c204f626a656374697665);
INSERT INTO `lng_data` VALUES ('meta', 'meta_entity', 'en', 0x456e74697479);
INSERT INTO `lng_data` VALUES ('meta', 'meta_entry', 'en', 0x456e747279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_exam', 'en', 0x4578616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_exercise', 'en', 0x4578657263697365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_experiment', 'en', 0x4578706572696d656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_expositive', 'en', 0x4578706f736974697665);
INSERT INTO `lng_data` VALUES ('meta', 'meta_figure', 'en', 0x466967757265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_final', 'en', 0x46696e616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_format', 'en', 0x466f726d6174);
INSERT INTO `lng_data` VALUES ('meta', 'meta_general', 'en', 0x47656e6572616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_graph', 'en', 0x4772617068);
INSERT INTO `lng_data` VALUES ('meta', 'meta_has_format', 'en', 0x48617320466f726d6174);
INSERT INTO `lng_data` VALUES ('meta', 'meta_has_part', 'en', 0x4861732050617274);
INSERT INTO `lng_data` VALUES ('meta', 'meta_has_version', 'en', 0x4861732056657273696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_hierarchical', 'en', 0x48696572617263686963616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_high', 'en', 0x48696768);
INSERT INTO `lng_data` VALUES ('meta', 'meta_higher_education', 'en', 0x48696768657220456475636174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_id', 'en', 0x4964);
INSERT INTO `lng_data` VALUES ('meta', 'meta_idea', 'en', 0x49646561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_identifier', 'en', 0x4964656e746966696572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_index', 'en', 0x496e646578);
INSERT INTO `lng_data` VALUES ('meta', 'meta_installation_remarks', 'en', 0x496e7374616c6c6174696f6e2052656d61726b73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_intended_end_user_role', 'en', 0x496e74656e64656420456e64205573657220526f6c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_interactivity_level', 'en', 0x496e7465726163746976697479204c6576656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_interactivity_type', 'en', 0x496e74657261637469766974792054797065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_based_on', 'en', 0x4973204261736564204f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_basis_for', 'en', 0x497320426173697320466f72);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_format_of', 'en', 0x497320466f726d6174204f66);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_part_of', 'en', 0x49732050617274204f66);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_referenced_by', 'en', 0x4973205265666572656e636564204279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_required_by', 'en', 0x4973205265717569726564204279);
INSERT INTO `lng_data` VALUES ('meta', 'meta_is_version_of', 'en', 0x49732056657273696f6e204f66);
INSERT INTO `lng_data` VALUES ('meta', 'meta_keyword', 'en', 0x4b6579776f7264);
INSERT INTO `lng_data` VALUES ('meta', 'meta_kind', 'en', 0x4b696e64);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_aa', 'en', 0x41666172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ab', 'en', 0x41626b68617a69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_af', 'en', 0x416672696b61616e73);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_am', 'en', 0x416d6861726963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ar', 'en', 0x417261626963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_as', 'en', 0x417373616d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ay', 'en', 0x41796d617261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_az', 'en', 0x417a65726261696a616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ba', 'en', 0x426173686b6972);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_be', 'en', 0x4279656c6f7275737369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bg', 'en', 0x42756c67617269616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bh', 'en', 0x426968617269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bi', 'en', 0x4269736c616d61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bn', 'en', 0x42656e67616c693b62616e676c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_bo', 'en', 0x5469626574616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_br', 'en', 0x427265746f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ca', 'en', 0x436174616c616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_co', 'en', 0x436f72736963616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_cs', 'en', 0x437a656368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_cy', 'en', 0x57656c7368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_da', 'en', 0x44616e697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_de', 'en', 0x4765726d616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_dz', 'en', 0x42687574616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_el', 'en', 0x477265656b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_en', 'en', 0x456e676c697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_eo', 'en', 0x4573706572616e746f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_es', 'en', 0x5370616e697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_et', 'en', 0x4573746f6e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_eu', 'en', 0x426173717565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fa', 'en', 0x5065727369616e2028666172736929);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fi', 'en', 0x46696e6e697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fj', 'en', 0x46696a69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fo', 'en', 0x4661726f657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fr', 'en', 0x4672656e6368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_fy', 'en', 0x4672697369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ga', 'en', 0x4972697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gd', 'en', 0x53636f7473206761656c6963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gl', 'en', 0x47616c696369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gn', 'en', 0x47756172616e69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_gu', 'en', 0x47756a6172617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ha', 'en', 0x4861757361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_he', 'en', 0x486562726577);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hi', 'en', 0x48696e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hr', 'en', 0x43726f617469616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hu', 'en', 0x48756e67617269616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_hy', 'en', 0x41726d656e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ia', 'en', 0x496e7465726c696e677561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_id', 'en', 0x496e646f6e657369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ie', 'en', 0x496e7465726c696e677565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ik', 'en', 0x496e757069616b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_is', 'en', 0x4963656c616e646963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_it', 'en', 0x4974616c69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_iu', 'en', 0x496e756b7469747574);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ja', 'en', 0x4a6170616e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_jv', 'en', 0x4a6176616e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ka', 'en', 0x47656f726769616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kk', 'en', 0x4b617a616b68);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kl', 'en', 0x477265656e6c616e646963);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_km', 'en', 0x43616d626f6469616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_kn', 'en', 0x4b616e6e616461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ko', 'en', 0x4b6f7265616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ks', 'en', 0x4b6173686d697269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ku', 'en', 0x4b757264697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ky', 'en', 0x4b69726768697a);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_la', 'en', 0x4c6174696e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ln', 'en', 0x4c696e67616c61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lo', 'en', 0x4c616f746869616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lt', 'en', 0x4c69746875616e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_lv', 'en', 0x4c61747669616e3b6c657474697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mg', 'en', 0x4d616c6167617379);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mi', 'en', 0x4d616f7269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mk', 'en', 0x4d616365646f6e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ml', 'en', 0x4d616c6179616c616d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mn', 'en', 0x4d6f6e676f6c69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mo', 'en', 0x4d6f6c64617669616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mr', 'en', 0x4d617261746869);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ms', 'en', 0x4d616c6179);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_mt', 'en', 0x4d616c74657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_my', 'en', 0x4275726d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_na', 'en', 0x4e61757275);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ne', 'en', 0x4e6570616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_nl', 'en', 0x4475746368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_no', 'en', 0x4e6f7277656769616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_oc', 'en', 0x4f63636974616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_om', 'en', 0x4166616e20286f726f6d6f29);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_or', 'en', 0x4f72697961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pa', 'en', 0x50756e6a616269);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pl', 'en', 0x506f6c697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ps', 'en', 0x50617368746f3b70757368746f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_pt', 'en', 0x506f7274756775657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_qu', 'en', 0x51756563687561);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rm', 'en', 0x52686165746f2d726f6d616e6365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rn', 'en', 0x4b7572756e6469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ro', 'en', 0x526f6d616e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ru', 'en', 0x5275737369616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_rw', 'en', 0x4b696e79617277616e6461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sa', 'en', 0x53616e736b726974);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sd', 'en', 0x53696e646869);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sg', 'en', 0x53616e67686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sh', 'en', 0x536572626f2d63726f617469616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_si', 'en', 0x53696e6768616c657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sk', 'en', 0x536c6f76616b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sl', 'en', 0x536c6f76656e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sm', 'en', 0x53616d6f616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sn', 'en', 0x53686f6e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_so', 'en', 0x536f6d616c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sq', 'en', 0x416c62616e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sr', 'en', 0x5365726269616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ss', 'en', 0x53697377617469);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_st', 'en', 0x5365736f74686f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_su', 'en', 0x53756e64616e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sv', 'en', 0x53776564697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_sw', 'en', 0x53776168696c69);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ta', 'en', 0x54616d696c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_te', 'en', 0x54656c756775);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tg', 'en', 0x54616a696b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_th', 'en', 0x54686169);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ti', 'en', 0x54696772696e7961);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tk', 'en', 0x5475726b6d656e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tl', 'en', 0x546167616c6f67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tn', 'en', 0x5365747377616e61);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_to', 'en', 0x546f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tr', 'en', 0x5475726b697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ts', 'en', 0x54736f6e6761);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tt', 'en', 0x5461746172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_tw', 'en', 0x547769);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ug', 'en', 0x5569677572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_uk', 'en', 0x556b7261696e69616e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_ur', 'en', 0x55726475);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_uz', 'en', 0x557a62656b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_vi', 'en', 0x566965746e616d657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_vo', 'en', 0x566f6c6170756b);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_wo', 'en', 0x576f6c6f66);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_xh', 'en', 0x58686f7361);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_yi', 'en', 0x59696464697368);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_yo', 'en', 0x596f72756261);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_za', 'en', 0x5a6875616e67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_zh', 'en', 0x4368696e657365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_l_zu', 'en', 0x5a756c75);
INSERT INTO `lng_data` VALUES ('meta', 'meta_language', 'en', 0x4c616e6775616765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_learner', 'en', 0x4c6561726e6572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_learning_resource_type', 'en', 0x4c6561726e696e67205265736f757263652054797065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_lecture', 'en', 0x4c656374757265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_lifecycle', 'en', 0x4c6966656379636c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_linear', 'en', 0x4c696e656172);
INSERT INTO `lng_data` VALUES ('meta', 'meta_local_file', 'en', 0x4c6f63616c2046696c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_location', 'en', 0x4c6f636174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_low', 'en', 0x4c6f77);
INSERT INTO `lng_data` VALUES ('meta', 'meta_mac_os', 'en', 0x4d61634f53);
INSERT INTO `lng_data` VALUES ('meta', 'meta_manager', 'en', 0x4d616e61676572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_maximum_version', 'en', 0x4d6178696d756d2056657273696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_medium', 'en', 0x4d656469756d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_meta_metadata', 'en', 0x4d6574612d4d65746164617461);
INSERT INTO `lng_data` VALUES ('meta', 'meta_metadatascheme', 'en', 0x4d6574616461746120536368656d65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_minimum_version', 'en', 0x4d696e696d756d2056657273696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_mixed', 'en', 0x4d69786564);
INSERT INTO `lng_data` VALUES ('meta', 'meta_ms_windows', 'en', 0x4d532d57696e646f7773);
INSERT INTO `lng_data` VALUES ('meta', 'meta_multi_os', 'en', 0x4d756c74692d4f53);
INSERT INTO `lng_data` VALUES ('meta', 'meta_name', 'en', 0x4e616d65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_narrative_text', 'en', 0x4e61727261746976652054657874);
INSERT INTO `lng_data` VALUES ('meta', 'meta_networked', 'en', 0x4e6574776f726b6564);
INSERT INTO `lng_data` VALUES ('meta', 'meta_new_element', 'en', 0x4e657720456c656d656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no', 'en', 0x4e6f);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_annotation', 'en', 0x4e6f206d6574616461746120617661696c61626c6520666f722074686520416e6e6f746174696f6e2073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_classification', 'en', 0x4e6f206d6574616461746120617661696c61626c6520666f722074686520436c617373696669636174696f6e2073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_educational', 'en', 0x4e6f206d6574616461746120617661696c61626c6520666f7220456475636174696f6e616c2073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_lifecycle', 'en', 0x4e6f206d6574616461746120617661696c61626c6520666f72204c6966656379636c652073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_meta_metadata', 'en', 0x4e6f206d6574616461746120617661696c61626c6520666f72204d6574612d4d657461646174612073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_relation', 'en', 0x4e6f206d6574616461746120617661696c61626c6520666f72207468652052656c6174696f6e2073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_rights', 'en', 0x4e6f206d6574616461746120617661696c61626c6520666f7220746865205269676874732073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_no_technical', 'en', 0x4e6f206d6574616461746120617661696c61626c6520666f7220546563686e6963616c2073656374696f6e2e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_none', 'en', 0x4e6f6e65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_operating_system', 'en', 0x4f7065726174696e672053797374656d);
INSERT INTO `lng_data` VALUES ('meta', 'meta_or_composite', 'en', 0x4f7220436f6d706f73697465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_other', 'en', 0x4f74686572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_other_plattform_requirements', 'en', 0x4f7468657220506c6174666f726d20526571756972656d656e7473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_pc_dos', 'en', 0x50432d444f53);
INSERT INTO `lng_data` VALUES ('meta', 'meta_please_select', 'en', 0x506c656173652073656c656374);
INSERT INTO `lng_data` VALUES ('meta', 'meta_prerequisite', 'en', 0x507265726571756973697465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_problem_statement', 'en', 0x50726f626c656d2053746174656d656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_publisher', 'en', 0x5075626c6973686572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_purpose', 'en', 0x507572706f7365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_questionnaire', 'en', 0x5175657374696f6e6e61697265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_reference', 'en', 0x5265666572656e6365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_references', 'en', 0x5265666572656e636573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_relation', 'en', 0x52656c6174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_requirement', 'en', 0x526571756972656d656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_requires', 'en', 0x5265717569726573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_resource', 'en', 0x5265736f75726365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_revised', 'en', 0x52657669736564);
INSERT INTO `lng_data` VALUES ('meta', 'meta_rights', 'en', 0x526967687473);
INSERT INTO `lng_data` VALUES ('meta', 'meta_role', 'en', 0x526f6c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_school', 'en', 0x5363686f6f6c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_security_level', 'en', 0x5365637572697479204c6576656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_self_assessment', 'en', 0x53656c66204173736573736d656e74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_semantic_density', 'en', 0x53656d616e7469632044656e73697479);
INSERT INTO `lng_data` VALUES ('meta', 'meta_simulation', 'en', 0x53696d756c6174696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_size', 'en', 0x53697a65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_skill_level', 'en', 0x536b696c6c204c6576656c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_slide', 'en', 0x536c696465);
INSERT INTO `lng_data` VALUES ('meta', 'meta_source', 'en', 0x536f75726365);
INSERT INTO `lng_data` VALUES ('meta', 'meta_status', 'en', 0x537461747573);
INSERT INTO `lng_data` VALUES ('meta', 'meta_structure', 'en', 0x537472756374757265);
INSERT INTO `lng_data` VALUES ('meta', 'meta_table', 'en', 0x5461626c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_taxon', 'en', 0x5461786f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_taxon_path', 'en', 0x5461786f6e2050617468);
INSERT INTO `lng_data` VALUES ('meta', 'meta_teacher', 'en', 0x54656163686572);
INSERT INTO `lng_data` VALUES ('meta', 'meta_technical', 'en', 0x546563686e6963616c);
INSERT INTO `lng_data` VALUES ('meta', 'meta_title', 'en', 0x5469746c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_training', 'en', 0x547261696e696e67);
INSERT INTO `lng_data` VALUES ('meta', 'meta_type', 'en', 0x54797065);
INSERT INTO `lng_data` VALUES ('meta', 'meta_typical_age_range', 'en', 0x5479706963616c204167652052616e6765);
INSERT INTO `lng_data` VALUES ('meta', 'meta_typical_learning_time', 'en', 0x5479706963616c204c6561726e696e672054696d65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_unavailable', 'en', 0x556e617661696c61626c65);
INSERT INTO `lng_data` VALUES ('meta', 'meta_unix', 'en', 0x556e6978);
INSERT INTO `lng_data` VALUES ('meta', 'meta_value', 'en', 0x56616c7565);
INSERT INTO `lng_data` VALUES ('meta', 'meta_version', 'en', 0x56657273696f6e);
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_difficult', 'en', 0x5665727920446966666963756c74);
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_easy', 'en', 0x566572792045617379);
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_high', 'en', 0x566572792048696768);
INSERT INTO `lng_data` VALUES ('meta', 'meta_very_low', 'en', 0x56657279204c6f77);
INSERT INTO `lng_data` VALUES ('meta', 'meta_yes', 'en', 0x596573);
INSERT INTO `lng_data` VALUES ('payment', 'currency_cent', 'en', 0x43656e74);
INSERT INTO `lng_data` VALUES ('payment', 'currency_euro', 'en', 0x4575726f);
INSERT INTO `lng_data` VALUES ('payment', 'duration', 'en', 0x4475726174696f6e);
INSERT INTO `lng_data` VALUES ('payment', 'pay_deleted_booking', 'en', 0x54686520656e74727920686173206265656e2064656c657465642e);
INSERT INTO `lng_data` VALUES ('payment', 'pay_header', 'en', 0x5061796d656e74);
INSERT INTO `lng_data` VALUES ('payment', 'pay_locator', 'en', 0x5061796d656e74);
INSERT INTO `lng_data` VALUES ('payment', 'pay_no_vendors_created', 'en', 0x4e6f2076656e646f72732063726561746564);
INSERT INTO `lng_data` VALUES ('payment', 'paya_access', 'en', 0x416363657373);
INSERT INTO `lng_data` VALUES ('payment', 'paya_add_price', 'en', 0x416464206e6577207072696365);
INSERT INTO `lng_data` VALUES ('payment', 'paya_add_price_title', 'en', 0x4e6577207072696365);
INSERT INTO `lng_data` VALUES ('payment', 'paya_added_new_object', 'en', 0x4164646564206e6577206f626a6563742e20506c6561736520656469742074686520706179206d6574686f6420616e6420746865207072696365732e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_added_new_price', 'en', 0x43726561746564206e6577207072696365);
INSERT INTO `lng_data` VALUES ('payment', 'paya_added_trustee', 'en', 0x43726561746564206e657720747275737465652e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_bookings_available', 'en', 0x54686973206f626a65637420686173206265656e20736f6c642e20506c656173652064656c65746520616c6c2073746174697374696320646174612066697273742e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_buyable', 'en', 0x42757961626c65);
INSERT INTO `lng_data` VALUES ('payment', 'paya_count_purchaser', 'en', 0x4e756d626572206f662070757263686173657273);
INSERT INTO `lng_data` VALUES ('payment', 'paya_customer', 'en', 0x437573746f6d6572);
INSERT INTO `lng_data` VALUES ('payment', 'paya_delete_price', 'en', 0x44656c657465207072696365);
INSERT INTO `lng_data` VALUES ('payment', 'paya_delete_trustee_msg', 'en', 0x44656c657465642073656c65637465642074727573746565732e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_deleted_last_price', 'en', 0x546865206c61737420707269636520686173206265656e2064656c657465642e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_deleted_object', 'en', 0x53746f706564207061796d656e7420666f72207468652073656c6563746564206f626a6563742e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_details_updated', 'en', 0x44657461696c7320757064617465642e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_disabled', 'en', 0x44697361626c6564);
INSERT INTO `lng_data` VALUES ('payment', 'paya_edit_details', 'en', 0x456469742064657461696c73);
INSERT INTO `lng_data` VALUES ('payment', 'paya_edit_pay_method', 'en', 0x4564697420706179206d6574686f64);
INSERT INTO `lng_data` VALUES ('payment', 'paya_edit_prices', 'en', 0x4564697420707269636573);
INSERT INTO `lng_data` VALUES ('payment', 'paya_edit_prices_first', 'en', 0x506c65617365206564697420746865207072696365732066697273742e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_enabled', 'en', 0x456e61626c6564);
INSERT INTO `lng_data` VALUES ('payment', 'paya_enter_login', 'en', 0x506c6561736520656e74657220616e2075736572206e616d652e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_err_adding_object', 'en', 0x4572726f7220616464696e67206f626a656374);
INSERT INTO `lng_data` VALUES ('payment', 'paya_error_update_booking', 'en', 0x4572726f7220736176696e672073746174697374696320646174612e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_expires', 'en', 0x457870697265207061796d656e74);
INSERT INTO `lng_data` VALUES ('payment', 'paya_header', 'en', 0x5061796d656e742061646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_insert_only_numbers', 'en', 0x506c6561736520696e73657274206f6e6c7920696e7465676572732e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_locator', 'en', 0x5061796d656e742061646d696e697374726174696f6e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_months', 'en', 0x4d6f6e7468287329);
INSERT INTO `lng_data` VALUES ('payment', 'paya_no_booking_id_given', 'en', 0x4572726f723a206e6f2027626f6f6b696e672069642720676976656e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_no_bookings', 'en', 0x4573206c696567656e206b65696e652053746174697374696b656e20766f722e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_no_object_selected', 'en', 0x506c656173652073656c656374206f6e65206f626a6563742e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_no_objects_assigned', 'en', 0x546865726520617265206e6f2073656c6c61626c65206f626a656374732e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_no_price_available', 'en', 0x4e6f2070726963657320617661696c61626c65);
INSERT INTO `lng_data` VALUES ('payment', 'paya_no_prices_selected', 'en', 0x506c656173652073656c656374206f6e65207072696365);
INSERT INTO `lng_data` VALUES ('payment', 'paya_no_settings_necessary', 'en', 0x546865726520617265206e6f2073657474696e6773206e656365737361727920666f72207468697320706179206d6574686f64);
INSERT INTO `lng_data` VALUES ('payment', 'paya_no_trustees', 'en', 0x4e6f20747275737465657320637265617465642e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_no_valid_login', 'en', 0x546869732075736572206e616d65206973206e6f742076616c69642e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_no_vendor_selected', 'en', 0x596f7520686176656e27742073656c656374656420612076656e646f722e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_not_assign_yourself', 'en', 0x4974206973206e6f7420706f737369626c6520746f2061737369676e20796f757273656c662e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_not_buyable', 'en', 0x4e6f742062757961626c65);
INSERT INTO `lng_data` VALUES ('payment', 'paya_object', 'en', 0x4f626a656b7473);
INSERT INTO `lng_data` VALUES ('payment', 'paya_object_not_purchasable', 'en', 0x4974206973206e6f7420706f737369626c6520746f2073656c6c2074686973206f626a6563742e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_order_date', 'en', 0x4f726465722064617465);
INSERT INTO `lng_data` VALUES ('payment', 'paya_pay_method', 'en', 0x506179206d6574686f64);
INSERT INTO `lng_data` VALUES ('payment', 'paya_pay_method_not_specified', 'en', 0x4e6f7420737065636966696564);
INSERT INTO `lng_data` VALUES ('payment', 'paya_payed', 'en', 0x5061796564);
INSERT INTO `lng_data` VALUES ('payment', 'paya_payed_access', 'en', 0x50617965642f416363657373);
INSERT INTO `lng_data` VALUES ('payment', 'paya_perm_obj', 'en', 0x45646974206f626a65637473);
INSERT INTO `lng_data` VALUES ('payment', 'paya_perm_stat', 'en', 0x456469742073746174697374696373);
INSERT INTO `lng_data` VALUES ('payment', 'paya_price_not_valid', 'en', 0x54686973207072696365206973206e6f742076616c69642e20506c6561736520696e73657274206f6e6c79206e756d62657273);
INSERT INTO `lng_data` VALUES ('payment', 'paya_select_object_to_sell', 'en', 0x506c656173652073656c65637420746865206f626a65637420776869636820796f752077616e7420746f2073656c6c2e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_select_pay_method_first', 'en', 0x506c656173652073656c656374206f6e6520706179206d6574686f642066697273742e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_sell_object', 'en', 0x53656c6c206f626a656374);
INSERT INTO `lng_data` VALUES ('payment', 'paya_shopping_cart', 'en', 0x53686f7070696e672063617274);
INSERT INTO `lng_data` VALUES ('payment', 'paya_statistic', 'en', 0x537461746973746963);
INSERT INTO `lng_data` VALUES ('payment', 'paya_sure_delete_object', 'en', 0x41726520796f7520737572652c20796f752077616e7420746f2073746f70207061796d656e7420666f722074686973206f626a6563743f20416c6c2061737369676e656420646174612077696c6c20676574206c6f73742e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_sure_delete_selected_prices', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c657465207468652073656c6563746564207072696365733f);
INSERT INTO `lng_data` VALUES ('payment', 'paya_sure_delete_selected_trustees', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c657465207468652073656c65637465642074727573746565733f);
INSERT INTO `lng_data` VALUES ('payment', 'paya_sure_delete_stat', 'en', 0x41726520796f7520737572652c20796f752077616e7420746f2064656c6574652074686973207374617469737469633f);
INSERT INTO `lng_data` VALUES ('payment', 'paya_transaction', 'en', 0x5472616e73616374696f6e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_trustee_table', 'en', 0x5472757374656573);
INSERT INTO `lng_data` VALUES ('payment', 'paya_trustees', 'en', 0x5472757374656573);
INSERT INTO `lng_data` VALUES ('payment', 'paya_update_price', 'en', 0x55706461746520707269636573);
INSERT INTO `lng_data` VALUES ('payment', 'paya_updated_booking', 'en', 0x55706461746564207374617469737469632e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_updated_prices', 'en', 0x5570646174656420707269636573);
INSERT INTO `lng_data` VALUES ('payment', 'paya_updated_trustees', 'en', 0x55706461746564207065726d697373696f6e732e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_user_already_assigned', 'en', 0x5573657220616c72656164792061737369676e65642e);
INSERT INTO `lng_data` VALUES ('payment', 'paya_vendor', 'en', 0x56656e646f72);
INSERT INTO `lng_data` VALUES ('payment', 'pays_active_bookings', 'en', 0x54686572652061637469766520626f6f6b696e677320666f72207468652073656c65637465642076656e646f722873292e20506c656173652064656c657465207468656d2066697273742e);
INSERT INTO `lng_data` VALUES ('payment', 'pays_add_vendor', 'en', 0x4164642076656e646f72);
INSERT INTO `lng_data` VALUES ('payment', 'pays_added_vendor', 'en', 0x4164646564206e65772076656e646f72);
INSERT INTO `lng_data` VALUES ('payment', 'pays_already_assigned_vendors', 'en', 0x54686520666f6c6c6f77696e67206e756d626572206f66207573657273207765726520616c72656164792061737369676e65643a);
INSERT INTO `lng_data` VALUES ('payment', 'pays_assigned_vendors', 'en', 0x54686520666f6c6c6f77696e67206e756d626572206f662075736572732068617665206265656e2061737369676e656420746f207468652076656e646f72206c6973743a);
INSERT INTO `lng_data` VALUES ('payment', 'pays_bill', 'en', 0x42696c6c);
INSERT INTO `lng_data` VALUES ('payment', 'pays_bmf', 'en', 0x424d46);
INSERT INTO `lng_data` VALUES ('payment', 'pays_cost_center', 'en', 0x436f73742063656e746572);
INSERT INTO `lng_data` VALUES ('payment', 'pays_delete_vendor', 'en', 0x446561737369676e2075736572);
INSERT INTO `lng_data` VALUES ('payment', 'pays_deleted_number_vendors', 'en', 0x54686520666f6c6c6f77696e67206e756d626572206f662076656e646f72732068617665206265656e2064656c657465643a);
INSERT INTO `lng_data` VALUES ('payment', 'pays_header_select_vendor', 'en', 0x56656e646f722073656c656374696f6e);
INSERT INTO `lng_data` VALUES ('payment', 'pays_no_username_given', 'en', 0x4e6f20757365726e616d6520676976656e);
INSERT INTO `lng_data` VALUES ('payment', 'pays_no_valid_username_given', 'en', 0x4e6f2076616c696420757365726e616d6520676976656e);
INSERT INTO `lng_data` VALUES ('payment', 'pays_no_vendor_selected', 'en', 0x4e6f2076656e646f722073656c6563746564);
INSERT INTO `lng_data` VALUES ('payment', 'pays_number_bookings', 'en', 0x41637469766520626f6f6b696e6773);
INSERT INTO `lng_data` VALUES ('payment', 'pays_objects_bill_exist', 'en', 0x506179206d6574686f64202762696c6c272069732061637469766174656420666f7220736f6d65206f626a656374732e205468657265666f72652069742063616e206e6f742062652064656163746976617465642e);
INSERT INTO `lng_data` VALUES ('payment', 'pays_objects_bmf_exist', 'en', 0x506179206d6574686f642027424d46272069732061637469766174656420666f7220736f6d65206f626a656374732e205468657265666f72652069742063616e206e6f742062652064656163746976617465642e);
INSERT INTO `lng_data` VALUES ('payment', 'pays_offline', 'en', 0x4f66666c696e65);
INSERT INTO `lng_data` VALUES ('payment', 'pays_online', 'en', 0x4f6e6c696e65);
INSERT INTO `lng_data` VALUES ('payment', 'pays_pay_methods', 'en', 0x506179206d6574686f6473);
INSERT INTO `lng_data` VALUES ('payment', 'pays_sure_delete_selected_vendors', 'en', 0x446f20796f75207265616c6c792077616e7420746f2064656c657465207468652073656c65637465642076656e646f72287329);
INSERT INTO `lng_data` VALUES ('payment', 'pays_updated_pay_method', 'en', 0x4368616e67656420706179206d6574686f642e);
INSERT INTO `lng_data` VALUES ('payment', 'pays_user_already_assigned', 'en', 0x54686973207573657220697320616c72656164792061737369676e656420746f207468652076656e646f72206c697374);
INSERT INTO `lng_data` VALUES ('payment', 'pays_vendor', 'en', 0x56656e646f72);
INSERT INTO `lng_data` VALUES ('payment', 'price_a', 'en', 0x5072696365);
INSERT INTO `lng_data` VALUES ('payment', 'update', 'en', 0x557064617465);
INSERT INTO `lng_data` VALUES ('search', 'search_active', 'en', 0x496e636c75646520616374697665207573657273);
INSERT INTO `lng_data` VALUES ('search', 'search_all_results', 'en', 0x416c6c20726573756c7473);
INSERT INTO `lng_data` VALUES ('search', 'search_and', 'en', 0x616e64);
INSERT INTO `lng_data` VALUES ('search', 'search_concatenation', 'en', 0x436f6e636174656e6174696f6e);
INSERT INTO `lng_data` VALUES ('search', 'search_content', 'en', 0x5061676520436f6e74656e74);
INSERT INTO `lng_data` VALUES ('search', 'search_dbk_content', 'en', 0x4469676974616c204c6962726172792028636f6e74656e7429);
INSERT INTO `lng_data` VALUES ('search', 'search_dbk_meta', 'en', 0x4469676974616c204c69627261727920286d6574616461746129);
INSERT INTO `lng_data` VALUES ('search', 'search_delete_sure', 'en', 0x546865206f626a65637420616e642069747320636f6e74656e74732077696c6c2062652064656c65746564207065726d616e656e746c79);
INSERT INTO `lng_data` VALUES ('search', 'search_group', 'en', 0x47726f757073);
INSERT INTO `lng_data` VALUES ('search', 'search_in_result', 'en', 0x5365617263682077697468696e20726573756c7473);
INSERT INTO `lng_data` VALUES ('search', 'search_inactive', 'en', 0x496e636c75646520696e616374697665207573657273);
INSERT INTO `lng_data` VALUES ('search', 'search_lm_content', 'en', 0x4c6561726e696e67206d6174657269616c732028636f6e74656e7429);
INSERT INTO `lng_data` VALUES ('search', 'search_lm_meta', 'en', 0x4c6561726e696e67206d6174657269616c7320286d6574616461746129);
INSERT INTO `lng_data` VALUES ('search', 'search_meta', 'en', 0x4d65746164617461);
INSERT INTO `lng_data` VALUES ('search', 'search_minimum_three', 'en', 0x596f757220736561726368206d757374206265206174206c656173742074687265652063686172616374657273206c6f6e67);
INSERT INTO `lng_data` VALUES ('search', 'search_move_folder_not_allowed', 'en', 0x466f6c6465722063616e6e6f74206265206d6f7665642e);
INSERT INTO `lng_data` VALUES ('search', 'search_move_to', 'en', 0x4d6f766520746f3a);
INSERT INTO `lng_data` VALUES ('search', 'search_my_search_results', 'en', 0x4d792073656172636820726573756c7473);
INSERT INTO `lng_data` VALUES ('search', 'search_new_folder', 'en', 0x4e657720666f6c646572);
INSERT INTO `lng_data` VALUES ('search', 'search_no_category', 'en', 0x596f752068617665206e6f742073656c656374656420616e79207365617263682063617465676f72696573);
INSERT INTO `lng_data` VALUES ('search', 'search_no_match', 'en', 0x596f75722073656172636820646964206e6f74206d6174636820616e7920726573756c7473);
INSERT INTO `lng_data` VALUES ('search', 'search_no_results_saved', 'en', 0x4e6f20726573756c74732073617665642e);
INSERT INTO `lng_data` VALUES ('search', 'search_no_search_term', 'en', 0x596f752068617665206e6f742073656c656374656420616e7920736561726368207465726d73);
INSERT INTO `lng_data` VALUES ('search', 'search_no_selection', 'en', 0x596f75206d616465206e6f2073656c656374696f6e2e);
INSERT INTO `lng_data` VALUES ('search', 'search_object_renamed', 'en', 0x4f626a6563742072656e616d65642e);
INSERT INTO `lng_data` VALUES ('search', 'search_objects_deleted', 'en', 0x4f626a6563742873292064656c657465642e);
INSERT INTO `lng_data` VALUES ('search', 'search_objects_moved', 'en', 0x4f626a65637473206d6f7665642e);
INSERT INTO `lng_data` VALUES ('search', 'search_one_action', 'en', 0x53656c656374206f6e6520616374696f6e2e);
INSERT INTO `lng_data` VALUES ('search', 'search_or', 'en', 0x6f72);
INSERT INTO `lng_data` VALUES ('search', 'search_rename_title', 'en', 0x52656e616d65207469746c65);
INSERT INTO `lng_data` VALUES ('search', 'search_results_saved', 'en', 0x53656172636820726573756c7473207361766564);
INSERT INTO `lng_data` VALUES ('search', 'search_save_as_select', 'en', 0x536176652061733a);
INSERT INTO `lng_data` VALUES ('search', 'search_search_for', 'en', 0x53656172636820666f72);
INSERT INTO `lng_data` VALUES ('search', 'search_search_no_results_saved', 'en', 0x546865726520617265206e6f2073656172636820726573756c7473207361766564);
INSERT INTO `lng_data` VALUES ('search', 'search_search_results', 'en', 0x53656172636820726573756c7473);
INSERT INTO `lng_data` VALUES ('search', 'search_search_term', 'en', 0x536561726368207465726d);
INSERT INTO `lng_data` VALUES ('search', 'search_select_exactly_one_object', 'en', 0x596f75206d7573742073656c6563742065786163746c79206f6e65206f626a6563742e);
INSERT INTO `lng_data` VALUES ('search', 'search_select_one', 'en', 0x53656c656374206f6e6520666f6c646572);
INSERT INTO `lng_data` VALUES ('search', 'search_select_one_action', 'en', 0x2d2d53656c656374206f6e6520616374696f6e2d2d);
INSERT INTO `lng_data` VALUES ('search', 'search_select_one_folder_select', 'en', 0x2d2d53656c656374206f6e6520666f6c6465722d2d);
INSERT INTO `lng_data` VALUES ('search', 'search_select_one_result', 'en', 0x53656c656374206174206c65617374206f6e652073656172636820726573756c74);
INSERT INTO `lng_data` VALUES ('search', 'search_select_one_select', 'en', 0x2d2d53656c656374206f6e6520666f6c6465722d2d);
INSERT INTO `lng_data` VALUES ('search', 'search_show_result', 'en', 0x53686f77);
INSERT INTO `lng_data` VALUES ('search', 'search_user', 'en', 0x5573657273);
INSERT INTO `lng_data` VALUES ('survey', 'add_category', 'en', 0x4164642063617465676f7279);
INSERT INTO `lng_data` VALUES ('survey', 'add_limits_for_standard_numbers', 'en', 0x506c6561736520656e7465722061206c6f77657220616e64207570706572206c696d697420666f7220746865207374616e64617264206e756d6265727320796f752077616e7420746f206164642061732063617465676f726965732e);
INSERT INTO `lng_data` VALUES ('survey', 'add_phrase', 'en', 0x41646420706872617365);
INSERT INTO `lng_data` VALUES ('survey', 'add_phrase_introduction', 'en', 0x506c656173652073656c6563742061207068726173653a);
INSERT INTO `lng_data` VALUES ('survey', 'already_completed_survey', 'en', 0x596f75206861766520616c726561647920636f6d706c6574656420746865207375727665792120596f7520617265206e6f742061626c6520746f20656e746572207468652073757276657920616761696e2e);
INSERT INTO `lng_data` VALUES ('survey', 'anonymize_survey', 'en', 0x416e6f6e796d697a65207375727665792064617461);
INSERT INTO `lng_data` VALUES ('survey', 'anonymize_survey_explanation', 'en', 0x5768656e206163746976617465642c20616c6c207573657220646174612077696c6c20626520616e6f6e796d697a656420616e642063616e6e6f7420626520747261636b65642e);
INSERT INTO `lng_data` VALUES ('survey', 'answer', 'en', 0x416e73776572);
INSERT INTO `lng_data` VALUES ('survey', 'apply', 'en', 0x4170706c79);
INSERT INTO `lng_data` VALUES ('survey', 'arithmetic_mean', 'en', 0x41726974686d65746963206d65616e);
INSERT INTO `lng_data` VALUES ('survey', 'ask_insert_questionblocks', 'en', 0x41726520796f75207375726520796f752077616e7420746f20696e736572742074686520666f6c6c6f77696e67207175657374696f6e20626c6f636b28732920746f20746865207375727665793f);
INSERT INTO `lng_data` VALUES ('survey', 'ask_insert_questions', 'en', 0x41726520796f75207375726520796f752077616e7420746f20696e736572742074686520666f6c6c6f77696e67207175657374696f6e28732920746f20746865207375727665793f);
INSERT INTO `lng_data` VALUES ('survey', 'back', 'en', 0x3c3c204261636b);
INSERT INTO `lng_data` VALUES ('survey', 'browse_for_questions', 'en', 0x42726f77736520666f72207175657374696f6e73);
INSERT INTO `lng_data` VALUES ('survey', 'cannot_maintain_survey', 'en', 0x596f7520646f206e6f7420706f73736573732073756666696369656e74207065726d697373696f6e7320746f206d61696e7461696e207468652073757276657921);
INSERT INTO `lng_data` VALUES ('survey', 'cannot_manage_phrases', 'en', 0x596f7520646f206e6f7420706f73736573732073756666696369656e74207065726d697373696f6e7320746f206d616e61676520746865207068726173657321);
INSERT INTO `lng_data` VALUES ('survey', 'cannot_participate_survey', 'en', 0x596f7520646f206e6f7420706f73736573732073756666696369656e74207065726d697373696f6e7320746f20706172746963697061746520696e207468652073757276657921);
INSERT INTO `lng_data` VALUES ('survey', 'cannot_save_metaobject', 'en', 0x596f7520646f206e6f7420706f73736573732073756666696369656e74207065726d697373696f6e7320746f207361766520746865206d657461206461746121);
INSERT INTO `lng_data` VALUES ('survey', 'cannot_switch_to_online_no_questions', 'en', 0x546865207374617475732063616e6e6f74206265206368616e67656420746f202671756f743b6f6e6c696e652671756f743b206265636175736520746865726520617265206e6f207175657374696f6e7320696e20746865207465737421);
INSERT INTO `lng_data` VALUES ('survey', 'category', 'en', 0x43617465676f7279);
INSERT INTO `lng_data` VALUES ('survey', 'category_delete_confirm', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e672063617465676f726965733f);
INSERT INTO `lng_data` VALUES ('survey', 'category_delete_select_none', 'en', 0x506c6561736520636865636b206174206c65617374206f6e652063617465676f727920746f2064656c65746520697421);
INSERT INTO `lng_data` VALUES ('survey', 'category_nr_selected', 'en', 0x4e756d626572206f6620757365727320746861742073656c656374656420746869732063617465676f7279);
INSERT INTO `lng_data` VALUES ('survey', 'concatenation', 'en', 0x436f6e636174656e6174696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'constraints', 'en', 0x436f6e73747261696e7473);
INSERT INTO `lng_data` VALUES ('survey', 'contains', 'en', 0x436f6e7461696e73);
INSERT INTO `lng_data` VALUES ('survey', 'continue', 'en', 0x436f6e74696e7565203e3e);
INSERT INTO `lng_data` VALUES ('survey', 'create_date', 'en', 0x43726561746564);
INSERT INTO `lng_data` VALUES ('survey', 'create_new', 'en', 0x437265617465206e6577);
INSERT INTO `lng_data` VALUES ('survey', 'create_questionpool_before_add_question', 'en', 0x596f75206d75737420637265617465206174206c65617374206f6e65207175657374696f6e20706f6f6c20746f2073746f726520796f7572207175657374696f6e7321);
INSERT INTO `lng_data` VALUES ('survey', 'csv', 'en', 0x436f6d6d61207365706172617465642076616c7565202843535629);
INSERT INTO `lng_data` VALUES ('survey', 'dc_agree', 'en', 0x6167726565);
INSERT INTO `lng_data` VALUES ('survey', 'dc_always', 'en', 0x616c77617973);
INSERT INTO `lng_data` VALUES ('survey', 'dc_definitelyfalse', 'en', 0x646566696e6974656c792066616c7365);
INSERT INTO `lng_data` VALUES ('survey', 'dc_definitelytrue', 'en', 0x646566696e6974656c792074727565);
INSERT INTO `lng_data` VALUES ('survey', 'dc_desired', 'en', 0x64657369726564);
INSERT INTO `lng_data` VALUES ('survey', 'dc_disagree', 'en', 0x6469736167726565);
INSERT INTO `lng_data` VALUES ('survey', 'dc_fair', 'en', 0x66616972);
INSERT INTO `lng_data` VALUES ('survey', 'dc_false', 'en', 0x66616c7365);
INSERT INTO `lng_data` VALUES ('survey', 'dc_good', 'en', 0x676f6f64);
INSERT INTO `lng_data` VALUES ('survey', 'dc_manytimes', 'en', 0x6d616e792074696d6573);
INSERT INTO `lng_data` VALUES ('survey', 'dc_morenegative', 'en', 0x6d6f7265206e65676174697665);
INSERT INTO `lng_data` VALUES ('survey', 'dc_morepositive', 'en', 0x6d6f726520706f736974697665);
INSERT INTO `lng_data` VALUES ('survey', 'dc_mostcertainly', 'en', 0x6d6f7374206365727461696e6c79);
INSERT INTO `lng_data` VALUES ('survey', 'dc_mostcertainlynot', 'en', 0x6d6f7374206365727461696e6c79206e6f74);
INSERT INTO `lng_data` VALUES ('survey', 'dc_must', 'en', 0x6d757374);
INSERT INTO `lng_data` VALUES ('survey', 'dc_mustnot', 'en', 0x6d757374206e6f74);
INSERT INTO `lng_data` VALUES ('survey', 'dc_neutral', 'en', 0x6e65757472616c);
INSERT INTO `lng_data` VALUES ('survey', 'dc_never', 'en', 0x6e65766572);
INSERT INTO `lng_data` VALUES ('survey', 'dc_no', 'en', 0x6e6f);
INSERT INTO `lng_data` VALUES ('survey', 'dc_notacceptable', 'en', 0x6e6f742061636365707461626c65);
INSERT INTO `lng_data` VALUES ('survey', 'dc_poor', 'en', 0x706f6f72);
INSERT INTO `lng_data` VALUES ('survey', 'dc_rarely', 'en', 0x726172656c79);
INSERT INTO `lng_data` VALUES ('survey', 'dc_should', 'en', 0x73686f756c64);
INSERT INTO `lng_data` VALUES ('survey', 'dc_shouldnot', 'en', 0x73686f756c64206e6f74);
INSERT INTO `lng_data` VALUES ('survey', 'dc_sometimes', 'en', 0x736f6d6574696d6573);
INSERT INTO `lng_data` VALUES ('survey', 'dc_stronglyagree', 'en', 0x7374726f6e676c79206167726565);
INSERT INTO `lng_data` VALUES ('survey', 'dc_stronglydesired', 'en', 0x7374726f6e676c792064657369726564);
INSERT INTO `lng_data` VALUES ('survey', 'dc_stronglydisagree', 'en', 0x7374726f6e676c79206469736167726565);
INSERT INTO `lng_data` VALUES ('survey', 'dc_stronglyundesired', 'en', 0x7374726f6e676c7920756e64657369726564);
INSERT INTO `lng_data` VALUES ('survey', 'dc_true', 'en', 0x74727565);
INSERT INTO `lng_data` VALUES ('survey', 'dc_undecided', 'en', 0x756e64656369646564);
INSERT INTO `lng_data` VALUES ('survey', 'dc_undesired', 'en', 0x756e64657369726564);
INSERT INTO `lng_data` VALUES ('survey', 'dc_varying', 'en', 0x76617279696e67);
INSERT INTO `lng_data` VALUES ('survey', 'dc_verygood', 'en', 0x7665727920676f6f64);
INSERT INTO `lng_data` VALUES ('survey', 'dc_yes', 'en', 0x796573);
INSERT INTO `lng_data` VALUES ('survey', 'define_questionblock', 'en', 0x446566696e65207175657374696f6e20626c6f636b);
INSERT INTO `lng_data` VALUES ('survey', 'disinvite', 'en', 0x556e696e76697465);
INSERT INTO `lng_data` VALUES ('survey', 'display_all_available', 'en', 0x446973706c617920616c6c20617661696c61626c65);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_agree5', 'en', 0x5374616e6461726420617474697475646520287374726f6e676c792061677265652d61677265652d756e646563696465642d64697361677265652d7374726f6e676c7920646973616772656529);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_agree_disagree', 'en', 0x5374616e64617264206174746974756465202861677265652d646973616772656529);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_agree_undecided_disagree', 'en', 0x5374616e64617264206174746974756465202861677265652d756e646563696465642d646973616772656529);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_desired5', 'en', 0x5374616e6461726420617474697475646520287374726f6e676c7920646573697265642d646573697265642d6e65757472616c2d756e646573697265642d7374726f6e676c7920756e6465736972656429);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_desired_neutral_undesired', 'en', 0x5374616e646172642061747469747564652028646573697265642d6e65757472616c2d756e6465736972656429);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_desired_undesired', 'en', 0x5374616e646172642061747469747564652028646573697265642d756e6465736972656429);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_good5', 'en', 0x5374616e6461726420617474697475646520287665727920676f6f642d676f6f642d666169722d706f6f722d6e6f742061636365707461626c6529);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_good_fair_notacceptable', 'en', 0x5374616e646172642061747469747564652028676f6f642d666169722d6e6f742061636365707461626c6529);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_good_notacceptable', 'en', 0x5374616e646172642061747469747564652028676f6f642d6e6f742061636365707461626c6529);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_must5', 'en', 0x5374616e6461726420617474697475646520286d7573742d73686f756c642d756e646563696465642d73686f756c64206e6f742d6d757374206e6f7429);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_shold_shouldnot', 'en', 0x5374616e64617264206174746974756465202873686f756c642d73686f756c64206e6f7429);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_attitude_should_undecided_shouldnot', 'en', 0x5374616e64617264206174746974756465202873686f756c642d756e646563696465642d73686f756c64206e6f7429);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_behaviour_certainly5', 'en', 0x5374616e64617264206265686176696f7220286d6f7374206365727461696e6c792d6d6f726520706f7369746976652d756e646563696465642d6d6f7265206e656761746976652d6d6f7374206365727461696e6c79206e6f7429);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_behaviour_yes_no', 'en', 0x5374616e64617264206265686176696f7220287965732d6e6f29);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_behaviour_yes_undecided_no', 'en', 0x5374616e64617264206265686176696f7220287965732d756e646563696465642d6e6f29);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_beliefs_always5', 'en', 0x5374616e646172642062656c696566732028616c776179732d6d616e792074696d65732d76617279696e672d726172656c792d6e6576657229);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_beliefs_always_never', 'en', 0x5374616e646172642062656c696566732028616c776179732d6e6576657229);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_beliefs_always_sometimes_never', 'en', 0x5374616e646172642062656c696566732028616c776179732d736f6d6574696d65732d6e6576657229);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_beliefs_true5', 'en', 0x5374616e646172642062656c696566732028646566696e6974656c7920747275652d747275652d756e646563696465642d66616c73652d646566696e6974656c792066616c736529);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_beliefs_true_false', 'en', 0x5374616e646172642062656c696566732028747275652d66616c736529);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_beliefs_true_undecided_false', 'en', 0x5374616e646172642062656c696566732028747275652d756e646563696465642d66616c736529);
INSERT INTO `lng_data` VALUES ('survey', 'dp_standard_numbers', 'en', 0x5374616e64617264206e756d62657273);
INSERT INTO `lng_data` VALUES ('survey', 'duplicate', 'en', 0x4475706c6963617465);
INSERT INTO `lng_data` VALUES ('survey', 'edit_ask_continue', 'en', 0x446f20796f752077616e7420746f20636f6e74696e756520616e6420656469742074686973207175657374696f6e3f);
INSERT INTO `lng_data` VALUES ('survey', 'edit_constraints_introduction', 'en', 0x596f7520686176652073656c65637465642074686520666f6c6c6f77696e67207175657374696f6e2873292f7175657374696f6e20626c6f636b28732920746f20656469742074686520636f6e73747261696e7473);
INSERT INTO `lng_data` VALUES ('survey', 'end_date', 'en', 0x456e642064617465);
INSERT INTO `lng_data` VALUES ('survey', 'end_date_reached', 'en', 0x596f752063616e6e6f7420737461727420746865207375727665792e2054686520656e642064617465206973207265616368656421);
INSERT INTO `lng_data` VALUES ('survey', 'enter_phrase_title', 'en', 0x506c6561736520656e746572206120706872617365207469746c65);
INSERT INTO `lng_data` VALUES ('survey', 'enter_value', 'en', 0x456e74657220612076616c7565);
INSERT INTO `lng_data` VALUES ('survey', 'error_importing_question', 'en', 0x54686572652077617320616e206572726f7220696d706f7274696e6720746865207175657374696f6e2873292066726f6d207468652066696c6520796f7520686176652073656c656374656421);
INSERT INTO `lng_data` VALUES ('survey', 'error_retrieving_anonymous_survey', 'en', 0x5468652073797374656d20636f756c64206e6f742066696e6420796f757220737572766579206461746120666f722073757276657920636f64652025732e20506c6561736520636865636b207468652073757276657920636f646520796f75206861766520656e746572656421);
INSERT INTO `lng_data` VALUES ('survey', 'evaluation', 'en', 0x4576616c756174696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'evaluation_access', 'en', 0x4576616c756174696f6e20616363657373);
INSERT INTO `lng_data` VALUES ('survey', 'excel', 'en', 0x4d6963726f736f667420457863656c);
INSERT INTO `lng_data` VALUES ('survey', 'exit', 'en', 0x45786974);
INSERT INTO `lng_data` VALUES ('survey', 'export_data_as', 'en', 0x4578706f72742064617461206173);
INSERT INTO `lng_data` VALUES ('survey', 'fill_out_all_category_fields', 'en', 0x506c656173652066696c6c206f757420616c6c2063617465676f7279206669656c6473206265666f726520796f75206164642061206e6577206f6e6521);
INSERT INTO `lng_data` VALUES ('survey', 'fill_out_all_required_fields_add_category', 'en', 0x506c656173652066696c6c206f757420616c6c207265717569726564206669656c6473206265666f726520796f75206164642063617465676f7269657321);
INSERT INTO `lng_data` VALUES ('survey', 'filter', 'en', 0x46696c746572);
INSERT INTO `lng_data` VALUES ('survey', 'filter_all_question_types', 'en', 0x416c6c207175657374696f6e207479706573);
INSERT INTO `lng_data` VALUES ('survey', 'filter_all_questionpools', 'en', 0x416c6c207175657374696f6e706f6f6c73);
INSERT INTO `lng_data` VALUES ('survey', 'filter_show_question_types', 'en', 0x53686f77207175657374696f6e207479706573);
INSERT INTO `lng_data` VALUES ('survey', 'filter_show_questionpools', 'en', 0x53686f77207175657374696f6e706f6f6c73);
INSERT INTO `lng_data` VALUES ('survey', 'finished', 'en', 0x636f6d706c65746564);
INSERT INTO `lng_data` VALUES ('survey', 'found_questions', 'en', 0x466f756e64207175657374696f6e73);
INSERT INTO `lng_data` VALUES ('survey', 'geometric_mean', 'en', 0x47656f6d6574726963206d65616e);
INSERT INTO `lng_data` VALUES ('survey', 'given_answers', 'en', 0x476976656e20616e7377657273);
INSERT INTO `lng_data` VALUES ('survey', 'harmonic_mean', 'en', 0x4861726d6f6e6963206d65616e);
INSERT INTO `lng_data` VALUES ('survey', 'import_question', 'en', 0x496d706f7274207175657374696f6e287329);
INSERT INTO `lng_data` VALUES ('survey', 'insert_after', 'en', 0x496e73657274206166746572);
INSERT INTO `lng_data` VALUES ('survey', 'insert_before', 'en', 0x496e73657274206265666f7265);
INSERT INTO `lng_data` VALUES ('survey', 'insert_missing_question', 'en', 0x506c656173652073656c656374206174206c65617374206f6e65207175657374696f6e20746f20696e7365727420697420696e746f207468652073757276657921);
INSERT INTO `lng_data` VALUES ('survey', 'insert_missing_questionblock', 'en', 0x506c656173652073656c656374206174206c65617374206f6e65207175657374696f6e20626c6f636b20746f20696e7365727420697420696e746f207468652073757276657921);
INSERT INTO `lng_data` VALUES ('survey', 'introduction', 'en', 0x496e74726f64756374696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'introduction_manage_phrases', 'en', 0x4d616e61676520796f7572206f776e20706872617365732077686963682061726520617661696c61626c6520666f7220616c6c20746865206f7264696e616c207175657374696f6e7320796f7520656469742f6372656174652e);
INSERT INTO `lng_data` VALUES ('survey', 'invitation', 'en', 0x496e7669746174696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'invitation_mode', 'en', 0x496e7669746174696f6e206d6f6465);
INSERT INTO `lng_data` VALUES ('survey', 'invite_participants', 'en', 0x496e76697465207061727469636970616e7473);
INSERT INTO `lng_data` VALUES ('survey', 'invited_groups', 'en', 0x496e76697465642067726f757073);
INSERT INTO `lng_data` VALUES ('survey', 'invited_users', 'en', 0x496e7669746564207573657273);
INSERT INTO `lng_data` VALUES ('survey', 'label_resume_survey', 'en', 0x456e74657220796f75722033322064696769742073757276657920636f6465);
INSERT INTO `lng_data` VALUES ('survey', 'last_update', 'en', 0x4c61737420757064617465);
INSERT INTO `lng_data` VALUES ('survey', 'lower_limit', 'en', 0x4c6f776572206c696d6974);
INSERT INTO `lng_data` VALUES ('survey', 'maintenance', 'en', 0x4d61696e74656e616e6365);
INSERT INTO `lng_data` VALUES ('survey', 'manage_phrases', 'en', 0x4d616e6167652070687261736573);
INSERT INTO `lng_data` VALUES ('survey', 'material_file', 'en', 0x4d6174657269616c2066696c65);
INSERT INTO `lng_data` VALUES ('survey', 'maximum', 'en', 0x4d6178696d756d2076616c7565);
INSERT INTO `lng_data` VALUES ('survey', 'median', 'en', 0x4d656469616e);
INSERT INTO `lng_data` VALUES ('survey', 'median_between', 'en', 0x6265747765656e);
INSERT INTO `lng_data` VALUES ('survey', 'message_mail_survey_id', 'en', 0x596f75206861766520737461727465642074686520616e6f6e796d697a656420737572766579202671756f743b25732671756f743b2e20546f20726573756d6520746869732073757276657920796f75206e6565642074686520666f6c6c6f77696e672033322064696769742073757276657920636f64653a3c62723e3c62723e3c7374726f6e673e25733c2f7374726f6e673e3c62723e3c62723e526573756d696e67206120737572766579206973206f6e6c7920706f737369626c652c20696620796f7520696e746572727570742074686520737572766579206265666f72652066696e697368696e672069742e);
INSERT INTO `lng_data` VALUES ('survey', 'metric_question_out_of_bounds', 'en', 0x5468652076616c756520796f7520656e7465726564206973206e6f74206265747765656e20746865206d696e696d756d20616e64206d6178696d756d2076616c756521);
INSERT INTO `lng_data` VALUES ('survey', 'minimum', 'en', 0x4d696e696d756d2076616c7565);
INSERT INTO `lng_data` VALUES ('survey', 'missing_upper_or_lower_limit', 'en', 0x506c6561736520656e7465722061206c6f77657220616e6420616e207570706572206c696d697421);
INSERT INTO `lng_data` VALUES ('survey', 'mode', 'en', 0x4d6f73742073656c65637465642076616c7565);
INSERT INTO `lng_data` VALUES ('survey', 'mode_nr_of_selections', 'en', 0x4e72206f662073656c656374696f6e73);
INSERT INTO `lng_data` VALUES ('survey', 'mode_text', 'en', 0x4d6f73742073656c65637465642076616c756520285465787429);
INSERT INTO `lng_data` VALUES ('survey', 'multiple_choice_multiple_response', 'en', 0x4d756c7469706c652063686f69636520286d756c7469706c6520726573706f6e736529);
INSERT INTO `lng_data` VALUES ('survey', 'multiple_choice_single_response', 'en', 0x4d756c7469706c652063686f696365202873696e676c6520726573706f6e736529);
INSERT INTO `lng_data` VALUES ('survey', 'next_question_rows', 'en', 0x5175657374696f6e73202564202d202564206f66202564203e3e);
INSERT INTO `lng_data` VALUES ('survey', 'no_available_constraints', 'en', 0x546865726520617265206e6f20636f6e73747261696e747320646566696e656421);
INSERT INTO `lng_data` VALUES ('survey', 'no_category_selected_for_deleting', 'en', 0x506c656173652073656c6563742061206c65617374206f6e652063617465676f727920696620796f752077616e7420746f2064656c6574652063617465676f7269657321);
INSERT INTO `lng_data` VALUES ('survey', 'no_category_selected_for_move', 'en', 0x506c6561736520636865636b206174206c65617374206f6e652063617465676f727920746f206d6f766520697421);
INSERT INTO `lng_data` VALUES ('survey', 'no_constraints_checked', 'en', 0x506c656173652073656c6563742061206c65617374206f6e65207175657374696f6e206f72207175657374696f6e20626c6f636b20746f20656469742074686520636f6e73747261696e747321);
INSERT INTO `lng_data` VALUES ('survey', 'no_question_selected_for_move', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e20746f206d6f766520697421);
INSERT INTO `lng_data` VALUES ('survey', 'no_question_selected_for_removal', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e206f72207175657374696f6e20626c6f636b20746f2072656d6f766520697421);
INSERT INTO `lng_data` VALUES ('survey', 'no_questionblocks_available', 'en', 0x546865726520617265206e6f207175657374696f6e20626c6f636b7320617661696c61626c65);
INSERT INTO `lng_data` VALUES ('survey', 'no_questions_available', 'en', 0x546865726520617265206e6f207175657374696f6e7320617661696c61626c6521);
INSERT INTO `lng_data` VALUES ('survey', 'no_search_results', 'en', 0x546865726520617265206e6f2073656172636820726573756c747321);
INSERT INTO `lng_data` VALUES ('survey', 'no_target_selected_for_move', 'en', 0x596f75206d7573742073656c65637420612074617267657420706f736974696f6e21);
INSERT INTO `lng_data` VALUES ('survey', 'no_user_or_group_selected', 'en', 0x506c6561736520636865636b20616e206f7074696f6e20776861742061726520796f7520736561726368696e6720666f722028757365727320616e642f6f722067726f7570732921);
INSERT INTO `lng_data` VALUES ('survey', 'no_user_phrases_defined', 'en', 0x546865726520617265206e6f207573657220646566696e6564207068726173657320617661696c61626c6521);
INSERT INTO `lng_data` VALUES ('survey', 'nominal_question_not_checked', 'en', 0x506c6561736520636865636b206f6e65206f6620746865206f66666572656420616e737765727321);
INSERT INTO `lng_data` VALUES ('survey', 'non_ratio', 'en', 0x4e6f6e2d526174696f);
INSERT INTO `lng_data` VALUES ('survey', 'not_finished', 'en', 0x6e6f7420636f6d706c65746564);
INSERT INTO `lng_data` VALUES ('survey', 'not_started', 'en', 0x6e6f742073746172746564);
INSERT INTO `lng_data` VALUES ('survey', 'obligatory', 'en', 0x6f626c696761746f7279);
INSERT INTO `lng_data` VALUES ('survey', 'off', 'en', 0x6f6666);
INSERT INTO `lng_data` VALUES ('survey', 'offline', 'en', 0x6f66666c696e65);
INSERT INTO `lng_data` VALUES ('survey', 'on', 'en', 0x6f6e);
INSERT INTO `lng_data` VALUES ('survey', 'online', 'en', 0x6f6e6c696e65);
INSERT INTO `lng_data` VALUES ('survey', 'or', 'en', 0x6f72);
INSERT INTO `lng_data` VALUES ('survey', 'ordinal_question_not_checked', 'en', 0x506c6561736520636865636b206f6e65206f6620746865206f66666572656420616e737765727321);
INSERT INTO `lng_data` VALUES ('survey', 'percentage_of_entered_values', 'en', 0x50657263656e74616765206f66207573657273207468617420656e746572656420746869732076616c7565);
INSERT INTO `lng_data` VALUES ('survey', 'percentage_of_selections', 'en', 0x50657263656e74616765206f6620757365727320746861742073656c656374656420746869732063617465676f7279);
INSERT INTO `lng_data` VALUES ('survey', 'predefined_users', 'en', 0x507265646566696e6564207573657220736574);
INSERT INTO `lng_data` VALUES ('survey', 'preview', 'en', 0x50726576696577);
INSERT INTO `lng_data` VALUES ('survey', 'previous_question_rows', 'en', 0x3c3c205175657374696f6e73202564202d202564206f66202564);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_confirm_delete_phrases', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e67207068726173652873293f);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_confirm_delete_questions', 'en', 0x41726520796f75207375726520796f752077616e7420746f2064656c6574652074686520666f6c6c6f77696e67207175657374696f6e2873293f);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_copy_select_none', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e20746f20636f707920697421);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_define_questionblock_select_missing', 'en', 0x506c656173652073656c6563742061206c656173742074776f207175657374696f6e7320696620796f752077616e7420746f20646566696e652061207175657374696f6e20626c6f636b21);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_delete_phrase_select_none', 'en', 0x506c6561736520636865636b206174206c65617374206f6e652070687261736520746f2064656c657465206974);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_delete_rbac_error', 'en', 0x596f752068617665206e6f2072696768747320746f2064656c65746520746865207175657374696f6e28732921);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_delete_select_none', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e20746f2064656c65746520697421);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_duplicate_select_none', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e20746f206475706c696361746520697421);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_export_select_none', 'en', 0x506c6561736520636865636b206174206c65617374206f6e65207175657374696f6e20746f206578706f7274206974);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_past_questions_confirmation', 'en', 0x41726520796f75207375726520796f752077616e7420746f2070617374652074686520666f6c6c6f77696e67207175657374696f6e28732920696e20746865207175657374696f6e20706f6f6c3f);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_phrases_deleted', 'en', 0x5068726173652873292064656c657465642e);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_questions_deleted', 'en', 0x5175657374696f6e2873292064656c657465642e);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_questions_pasted', 'en', 0x5175657374696f6e287329207061737465642e);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_savephrase_empty', 'en', 0x506c6561736520656e746572206120706872617365207469746c6521);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_savephrase_exists', 'en', 0x54686520706872617365207469746c6520616c7265616479206578697374732120506c6561736520656e74657220616e6f7468657220706872617365207469746c652e);
INSERT INTO `lng_data` VALUES ('survey', 'qpl_unfold_select_none', 'en', 0x506c656173652073656c656374206174206c65617374206f6e65207175657374696f6e20626c6f636b20696620796f752077616e7420746f20756e666f6c64207175657374696f6e20626c6f636b7321);
INSERT INTO `lng_data` VALUES ('survey', 'qt_metric', 'en', 0x4d6574726963207175657374696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'qt_nominal', 'en', 0x4e6f6d696e616c207175657374696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'qt_ordinal', 'en', 0x4f7264696e616c207175657374696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'qt_text', 'en', 0x54657874207175657374696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'question_has_constraints', 'en', 0x436f6e73747261696e7473);
INSERT INTO `lng_data` VALUES ('survey', 'question_obligatory', 'en', 0x546865207175657374696f6e206973206f626c696761746f727921);
INSERT INTO `lng_data` VALUES ('survey', 'question_saved_for_upload', 'en', 0x546865207175657374696f6e20776173207361766564206175746f6d61746963616c6c7920696e206f7264657220746f20726573657276652068617264206469736b20737061636520746f2073746f7265207468652075706c6f616465642066696c652e20496620796f752063616e63656c207468697320666f726d206e6f772c206265206177617265207468617420796f75206d7573742064656c65746520746865207175657374696f6e20696e20746865207175657374696f6e20706f6f6c20696620796f7520646f206e6f742077616e7420746f206b65657020697421);
INSERT INTO `lng_data` VALUES ('survey', 'question_type', 'en', 0x5175657374696f6e2074797065);
INSERT INTO `lng_data` VALUES ('survey', 'questionblock', 'en', 0x5175657374696f6e20626c6f636b);
INSERT INTO `lng_data` VALUES ('survey', 'questionblock_has_constraints', 'en', 0x436f6e73747261696e7473);
INSERT INTO `lng_data` VALUES ('survey', 'questionblocks', 'en', 0x5175657374696f6e20626c6f636b73);
INSERT INTO `lng_data` VALUES ('survey', 'questions', 'en', 0x5175657374696f6e73);
INSERT INTO `lng_data` VALUES ('survey', 'questions_inserted', 'en', 0x5175657374696f6e28732920696e73657274656421);
INSERT INTO `lng_data` VALUES ('survey', 'questions_removed', 'en', 0x5175657374696f6e28732920616e642f6f72207175657374696f6e20626c6f636b2873292072656d6f76656421);
INSERT INTO `lng_data` VALUES ('survey', 'questiontype', 'en', 0x5175657374696f6e2074797065);
INSERT INTO `lng_data` VALUES ('survey', 'ratio_absolute', 'en', 0x526174696f2d4162736f6c757465);
INSERT INTO `lng_data` VALUES ('survey', 'ratio_non_absolute', 'en', 0x526174696f2d4e6f6e2d4162736f6c757465);
INSERT INTO `lng_data` VALUES ('survey', 'remove_question', 'en', 0x52656d6f7665);
INSERT INTO `lng_data` VALUES ('survey', 'remove_questions', 'en', 0x41726520796f75207375726520796f752077616e7420746f2072656d6f76652074686520666f6c6c6f77696e67207175657374696f6e28732920616e642f6f72207175657374696f6e20626c6f636b2873292066726f6d2074686520746573743f);
INSERT INTO `lng_data` VALUES ('survey', 'reset_filter', 'en', 0x52657365742066696c746572);
INSERT INTO `lng_data` VALUES ('survey', 'resume_survey', 'en', 0x526573756d652074686520737572766579);
INSERT INTO `lng_data` VALUES ('survey', 'save_phrase', 'en', 0x5361766520617320706872617365);
INSERT INTO `lng_data` VALUES ('survey', 'save_phrase_categories_not_checked', 'en', 0x506c6561736520636865636b206174206c656173742074776f2063617465676f7269657320746f20736176652063617465676f7269657320696e2061206e65772070687261736521);
INSERT INTO `lng_data` VALUES ('survey', 'save_phrase_introduction', 'en', 0x496620796f752077616e7420746f2073617665207468652063617465676f726965732062656c6f772061732064656661756c74207068726173652c20706c6561736520656e746572206120706872617365207469746c652e20596f752063616e2061636365737320612064656661756c7420706872617365207768656e6576657220796f752077616e7420746f2061646420612070687261736520746f20616e206f7264696e616c207175657374696f6e2e);
INSERT INTO `lng_data` VALUES ('survey', 'search_field_all', 'en', 0x53656172636820696e20616c6c206669656c6473);
INSERT INTO `lng_data` VALUES ('survey', 'search_for', 'en', 0x53656172636820666f72);
INSERT INTO `lng_data` VALUES ('survey', 'search_groups', 'en', 0x47726f757073);
INSERT INTO `lng_data` VALUES ('survey', 'search_invitation', 'en', 0x53656172636820666f72207573657273206f722067726f75707320746f20696e76697465);
INSERT INTO `lng_data` VALUES ('survey', 'search_questions', 'en', 0x536561726368207175657374696f6e73);
INSERT INTO `lng_data` VALUES ('survey', 'search_term', 'en', 0x536561726368207465726d);
INSERT INTO `lng_data` VALUES ('survey', 'search_type_all', 'en', 0x53656172636820696e20616c6c207175657374696f6e207479706573);
INSERT INTO `lng_data` VALUES ('survey', 'search_users', 'en', 0x5573657273);
INSERT INTO `lng_data` VALUES ('survey', 'select_prior_question', 'en', 0x53656c6563742061207072696f72207175657374696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'select_questionpool', 'en', 0x506c656173652073656c6563742061207175657374696f6e20706f6f6c20746f2073746f7265207468652063726561746564207175657374696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'select_relation', 'en', 0x53656c65637420612072656c6174696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'select_target_position_for_move', 'en', 0x506c656173652073656c65637420612074617267657420706f736974696f6e20746f206d6f7665207468652063617465676f7269657320616e64207072657373206f6e65206f662074686520696e7365727420627574746f6e7321);
INSERT INTO `lng_data` VALUES ('survey', 'select_target_position_for_move_question', 'en', 0x506c656173652073656c65637420612074617267657420706f736974696f6e20746f206d6f766520746865207175657374696f6e28732920616e64207072657373206f6e65206f662074686520696e7365727420627574746f6e7321);
INSERT INTO `lng_data` VALUES ('survey', 'select_value', 'en', 0x53656c65637420612076616c7565);
INSERT INTO `lng_data` VALUES ('survey', 'set_filter', 'en', 0x5365742066696c746572);
INSERT INTO `lng_data` VALUES ('survey', 'start_date', 'en', 0x53746172742064617465);
INSERT INTO `lng_data` VALUES ('survey', 'start_date_not_reached', 'en', 0x596f752063616e6e6f74207374617274207468652073757276657920756e74696c207468652073746172742064617465206973207265616368656421);
INSERT INTO `lng_data` VALUES ('survey', 'start_survey', 'en', 0x537461727420737572766579);
INSERT INTO `lng_data` VALUES ('survey', 'subject_mail_survey_id', 'en', 0x596f75722073757276657920636f646520666f722022257322);
INSERT INTO `lng_data` VALUES ('survey', 'subtype', 'en', 0x53756274797065);
INSERT INTO `lng_data` VALUES ('survey', 'survey_code_message_sent', 'en', 0x412073757276657920636f646520776869636820616c6c6f777320796f7520746f20726573756d652074686520737572766579207761732073656e7420746f20796f757220494e424f5821);
INSERT INTO `lng_data` VALUES ('survey', 'survey_finish', 'en', 0x7361766520616e642066696e69736820737572766579);
INSERT INTO `lng_data` VALUES ('survey', 'survey_finished', 'en', 0x596f75206861766520636f6d706c6574656420746865207375727665792e205468616e6b20796f7520666f7220796f75722070617274696369706174696f6e21);
INSERT INTO `lng_data` VALUES ('survey', 'survey_is_offline', 'en', 0x596f752063616e6e6f742073746172742074686520737572766579212054686520737572766579206973206f66666c696e652e);
INSERT INTO `lng_data` VALUES ('survey', 'survey_next', 'en', 0x73617665203e3e3e);
INSERT INTO `lng_data` VALUES ('survey', 'survey_offline_message', 'en', 0x43616e277420696e766974652075736572732e2054686520737572766579206973206f66666c696e6521);
INSERT INTO `lng_data` VALUES ('survey', 'survey_online_warning', 'en', 0x54686520737572766579206973206f6e6c696e652e20596f752063616e6e6f7420656469742074686520737572766579207175657374696f6e7321);
INSERT INTO `lng_data` VALUES ('survey', 'survey_previous', 'en', 0x3c3c3c2073617665);
INSERT INTO `lng_data` VALUES ('survey', 'survey_questions', 'en', 0x5175657374696f6e73);
INSERT INTO `lng_data` VALUES ('survey', 'survey_skip_finish', 'en', 0x736b697020616e642066696e69736820737572766579);
INSERT INTO `lng_data` VALUES ('survey', 'survey_skip_next', 'en', 0x736b6970203e3e3e);
INSERT INTO `lng_data` VALUES ('survey', 'survey_skip_previous', 'en', 0x3c3c3c20736b6970);
INSERT INTO `lng_data` VALUES ('survey', 'survey_skip_start', 'en', 0x736b697020616e6420676f20746f2073746172742070616765);
INSERT INTO `lng_data` VALUES ('survey', 'survey_start', 'en', 0x7361766520616e6420676f20746f2073746172742070616765);
INSERT INTO `lng_data` VALUES ('survey', 'svy_all_user_data_deleted', 'en', 0x416c6c20757365722064617461206f6620746869732073757276657920686173206265656e2064656c6574656421);
INSERT INTO `lng_data` VALUES ('survey', 'svy_delete_all_user_data', 'en', 0x44656c65746520616c6c20757365722064617461);
INSERT INTO `lng_data` VALUES ('survey', 'svy_missing_author', 'en', 0x596f752068617665206e6f7420656e74657265642074686520617574686f722773206e616d6520696e20746865207375727665792070726f706572746965732120506c656173652061646420616e20617574686f7273206e616d652e);
INSERT INTO `lng_data` VALUES ('survey', 'svy_missing_questions', 'en', 0x596f7520646f206e6f74206861766520616e79207175657374696f6e7320696e20746865207375727665792120506c6561736520616464206174206c65617374206f6e65207175657374696f6e20746f20746865207375727665792e);
INSERT INTO `lng_data` VALUES ('survey', 'svy_missing_title', 'en', 0x596f752068617665206e6f7420656e7465726564206120737572766579207469746c652120506c6561736520676f20746f20746865206d657461646174612073656374696f6e20616e6420656e7465722061207469746c652e);
INSERT INTO `lng_data` VALUES ('survey', 'svy_select_questionpools', 'en', 0x506c656173652073656c6563742061207175657374696f6e20706f6f6c20746f2073746f72652074686520696d706f72746564207175657374696f6e73);
INSERT INTO `lng_data` VALUES ('survey', 'svy_statistical_evaluation', 'en', 0x537461746973746963616c206576616c756174696f6e);
INSERT INTO `lng_data` VALUES ('survey', 'svy_status_missing', 'en', 0x54686572652061726520726571756972656420656c656d656e7473206d697373696e6720696e20746869732073757276657921);
INSERT INTO `lng_data` VALUES ('survey', 'svy_status_missing_elements', 'en', 0x54686520666f6c6c6f77696e6720656c656d656e747320617265206d697373696e673a);
INSERT INTO `lng_data` VALUES ('survey', 'svy_status_ok', 'en', 0x54686520737461747573206f662074686520737572766579206973204f4b2e20546865726520617265206e6f206d697373696e6720656c656d656e74732e);
INSERT INTO `lng_data` VALUES ('survey', 'text_question_not_filled_out', 'en', 0x506c656173652066696c6c206f75742074686520616e73776572206669656c6421);
INSERT INTO `lng_data` VALUES ('survey', 'text_resume_survey', 'en', 0x596f752061726520747279696e6720746f20726573756d6520616e20616e6f6e796d697a6564207375727665792e20546f20636f6e74696e75652074686520737572766579207769746820796f75722070726576696f75736c7920656e74657265642076616c75657320706c6561736520656e746572207468652033322064696769742073757276657920636f646520776869636820796f75207265636569766564206166746572207374617274696e67207468652073757276657920286974207761732073656e7420776974682061206d65737361676520746f20796f757220494c494153206d61696c20666f6c646572292e20496e7374656164206f6620747970696e6720796f75722073757276657920636f646520796f7520616c736f2063616e20636f70792069742066726f6d20796f757220696e626f7820616e6420706173746520697420696e746f207468652074657874206669656c642062656c6f772e);
INSERT INTO `lng_data` VALUES ('survey', 'title_resume_survey', 'en', 0x526573756d652074686520737572766579202d20456e7465722073757276657920636f6465);
INSERT INTO `lng_data` VALUES ('survey', 'unfold', 'en', 0x556e666f6c64);
INSERT INTO `lng_data` VALUES ('survey', 'unlimited_users', 'en', 0x556e6c696d69746564);
INSERT INTO `lng_data` VALUES ('survey', 'uploaded_material', 'en', 0x55706c6f61646564204d6174657269616c);
INSERT INTO `lng_data` VALUES ('survey', 'upper_limit', 'en', 0x5570706572206c696d6974);
INSERT INTO `lng_data` VALUES ('survey', 'upper_limit_must_be_greater', 'en', 0x546865207570706572206c696d6974206d7573742062652067726561746572207468616e20746865206c6f776572206c696d697421);
INSERT INTO `lng_data` VALUES ('survey', 'users_answered', 'en', 0x557365727320616e737765726564);
INSERT INTO `lng_data` VALUES ('survey', 'users_skipped', 'en', 0x557365727320736b6970706564);
INSERT INTO `lng_data` VALUES ('survey', 'value_nr_entered', 'en', 0x4e756d626572206f66207573657273207468617420656e746572656420746861742076616c7565);
INSERT INTO `lng_data` VALUES ('survey', 'values', 'en', 0x56616c756573);
INSERT INTO `lng_data` VALUES ('survey', 'view_phrase', 'en', 0x5669657720706872617365);
INSERT INTO `lng_data` VALUES ('survey', 'warning_question_in_use', 'en', 0x5761726e696e672120546865207175657374696f6e20796f752077616e7420746f206564697420697320696e20757365206279207468652073757276657973206c69737465642062656c6f772e20496620796f752064656369646520746f20636f6e74696e756520616e6420736176652f6170706c7920746865207175657374696f6e2c20616c6c20616e7377657273206f66207468652073757276657973206c69737465642062656c6f772077696c6c2062652064656c657465642e20496620796f752077616e7420746f206368616e676520746865207175657374696f6e20616e642075736520697420696e20616e6f74686572207375727665792c20706c656173652063686f6f7365206475706c696361746520696e20746865207175657374696f6e2062726f7773657220746f206372656174652061206e657720696e7374616e6365206f662074686973207175657374696f6e2e);
INSERT INTO `lng_data` VALUES ('survey', 'warning_question_not_complete', 'en', 0x546865207175657374696f6e206973206e6f7420636f6d706c65746521);
INSERT INTO `lng_data` VALUES ('survey', 'warning_survey_not_complete', 'en', 0x54686520737572766579206973206e6f7420636f6d706c65746521);

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=8 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=115 ;

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
INSERT INTO `object_data` VALUES (20, 'typ', 'sahs', 'SCORM/AICC Learning Module', -1, '2003-08-15 10:07:28', '2003-08-15 12:23:10', '');
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
INSERT INTO `object_data` VALUES (85, 'typ', 'auth', 'Authentication settings', -1, '2003-11-30 21:22:49', '2003-11-30 21:22:49', '');
INSERT INTO `object_data` VALUES (86, 'auth', 'Authentication settings', 'Select and configure authentication mode for all user accounts', -1, '2003-11-30 21:22:49', '2003-11-30 21:22:49', '');
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
INSERT INTO `object_data` VALUES (110, 'rolt', 'il_crs_admin', 'Administrator template for course admins', -1, '2004-09-02 09:49:43', '2004-09-02 09:49:43', '');
INSERT INTO `object_data` VALUES (111, 'rolt', 'il_crs_tutor', 'Tutor template for course tutors', -1, '2004-09-02 09:49:43', '2004-09-02 09:49:43', '');
INSERT INTO `object_data` VALUES (112, 'rolt', 'il_crs_member', 'Member template for course members', -1, '2004-09-02 09:49:43', '2004-09-02 09:49:43', '');
INSERT INTO `object_data` VALUES (113, 'typ', 'pays', 'Payment settings', -1, '2004-09-02 09:49:45', '2004-09-02 09:49:45', '');
INSERT INTO `object_data` VALUES (114, 'pays', 'Payment settings', 'Payment settings', -1, '2004-09-02 09:49:45', '2004-09-02 09:49:45', '');

# --------------------------------------------------------

#
# Table structure for table `object_reference`
#

CREATE TABLE `object_reference` (
  `ref_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ref_id`),
  KEY `obj_id` (`obj_id`)
) TYPE=MyISAM AUTO_INCREMENT=20 ;

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
INSERT INTO `object_reference` VALUES (19, 114);
INSERT INTO `object_reference` VALUES (14, 98);
INSERT INTO `object_reference` VALUES (15, 100);
INSERT INTO `object_reference` VALUES (16, 107);
INSERT INTO `object_reference` VALUES (17, 109);
INSERT INTO `object_reference` VALUES (18, 86);

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
# Table structure for table `payment_bill_vendor`
#

CREATE TABLE `payment_bill_vendor` (
  `pobject_id` int(11) NOT NULL default '0',
  `gender` tinyint(2) default NULL,
  `firstname` char(64) default NULL,
  `lastname` char(64) default NULL,
  `title` char(64) default NULL,
  `institution` char(64) default NULL,
  `department` char(64) default NULL,
  `street` char(64) default NULL,
  `zipcode` char(16) default NULL,
  `city` char(64) default NULL,
  `country` char(64) default NULL,
  `phone` char(32) default NULL,
  `fax` char(32) default NULL,
  `email` char(64) default NULL,
  `account_number` char(32) default NULL,
  `bankcode` char(32) default NULL,
  `iban` char(64) default NULL,
  `bic` char(64) default NULL,
  `bankname` char(64) default NULL,
  PRIMARY KEY  (`pobject_id`)
) TYPE=MyISAM;

#
# Dumping data for table `payment_bill_vendor`
#


# --------------------------------------------------------

#
# Table structure for table `payment_currencies`
#

CREATE TABLE `payment_currencies` (
  `currency_id` int(3) NOT NULL default '0',
  `unit` char(32) NOT NULL default '',
  `subunit` char(32) NOT NULL default '',
  PRIMARY KEY  (`currency_id`)
) TYPE=MyISAM;

#
# Dumping data for table `payment_currencies`
#

INSERT INTO `payment_currencies` VALUES (1, 'euro', 'cent');

# --------------------------------------------------------

#
# Table structure for table `payment_objects`
#

CREATE TABLE `payment_objects` (
  `pobject_id` int(11) NOT NULL auto_increment,
  `ref_id` int(11) NOT NULL default '0',
  `status` int(2) NOT NULL default '0',
  `pay_method` int(2) NOT NULL default '0',
  `vendor_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`pobject_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `payment_objects`
#


# --------------------------------------------------------

#
# Table structure for table `payment_prices`
#

CREATE TABLE `payment_prices` (
  `price_id` int(11) NOT NULL auto_increment,
  `pobject_id` int(11) NOT NULL default '0',
  `duration` int(4) NOT NULL default '0',
  `currency` int(4) NOT NULL default '0',
  `unit_value` char(6) default '0',
  `sub_unit_value` char(3) default '00',
  PRIMARY KEY  (`price_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `payment_prices`
#


# --------------------------------------------------------

#
# Table structure for table `payment_shopping_cart`
#

CREATE TABLE `payment_shopping_cart` (
  `psc_id` int(11) NOT NULL auto_increment,
  `customer_id` int(11) NOT NULL default '0',
  `pobject_id` int(11) NOT NULL default '0',
  `price_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`psc_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `payment_shopping_cart`
#


# --------------------------------------------------------

#
# Table structure for table `payment_statistic`
#

CREATE TABLE `payment_statistic` (
  `booking_id` int(11) NOT NULL auto_increment,
  `transaction` char(64) default NULL,
  `pobject_id` int(11) NOT NULL default '0',
  `customer_id` int(11) NOT NULL default '0',
  `b_vendor_id` int(11) default NULL,
  `b_pay_method` int(2) default NULL,
  `order_date` int(9) default NULL,
  `duration` char(16) default NULL,
  `price` char(16) NOT NULL default '',
  `payed` int(2) NOT NULL default '0',
  `access` int(2) NOT NULL default '0',
  PRIMARY KEY  (`booking_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `payment_statistic`
#


# --------------------------------------------------------

#
# Table structure for table `payment_trustees`
#

CREATE TABLE `payment_trustees` (
  `vendor_id` int(11) NOT NULL default '0',
  `trustee_id` int(11) NOT NULL default '0',
  `perm_stat` int(1) default NULL,
  `perm_obj` int(1) NOT NULL default '0',
  PRIMARY KEY  (`vendor_id`,`trustee_id`)
) TYPE=MyISAM;

#
# Dumping data for table `payment_trustees`
#


# --------------------------------------------------------

#
# Table structure for table `payment_vendors`
#

CREATE TABLE `payment_vendors` (
  `vendor_id` int(11) NOT NULL default '0',
  `cost_center` varchar(16) NOT NULL default '',
  PRIMARY KEY  (`vendor_id`)
) TYPE=MyISAM;

#
# Dumping data for table `payment_vendors`
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
# Table structure for table `qpl_answer_enhanced`
#

CREATE TABLE `qpl_answer_enhanced` (
  `answer_enhanced_id` int(11) NOT NULL auto_increment,
  `answerblock_fi` int(11) NOT NULL default '0',
  `answer_fi` int(11) NOT NULL default '0',
  `answer_boolean_prefix` enum('0','1') NOT NULL default '0',
  `answer_boolean_connection` enum('0','1') NOT NULL default '1',
  `enhanced_order` tinyint(4) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`answer_enhanced_id`),
  KEY `answerblock_fi` (`answerblock_fi`,`answer_fi`)
) TYPE=MyISAM COMMENT='saves combinations of test question answers which are combin' AUTO_INCREMENT=1 ;

#
# Dumping data for table `qpl_answer_enhanced`
#


# --------------------------------------------------------

#
# Table structure for table `qpl_answerblock`
#

CREATE TABLE `qpl_answerblock` (
  `answerblock_id` int(11) NOT NULL auto_increment,
  `answerblock_index` tinyint(4) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `subquestion_index` tinyint(4) NOT NULL default '0',
  `points` double NOT NULL default '0',
  `feedback` varchar(30) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`answerblock_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM COMMENT='defines an answerblock, a combination of given answers of a ' AUTO_INCREMENT=1 ;

#
# Dumping data for table `qpl_answerblock`
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
  `answertext` text NOT NULL,
  `points` double NOT NULL default '0',
  `aorder` int(10) unsigned NOT NULL default '0',
  `correctness` enum('0','1') NOT NULL default '0',
  `solution_order` int(10) unsigned NOT NULL default '0',
  `matchingtext` text,
  `matching_order` int(10) unsigned default NULL,
  `gap_id` int(10) unsigned NOT NULL default '0',
  `cloze_type` enum('0','1') default NULL,
  `coords` text,
  `area` varchar(20) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`answer_id`),
  UNIQUE KEY `answer_id` (`answer_id`),
  KEY `answer_id_2` (`answer_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `qpl_answers`
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
) TYPE=MyISAM AUTO_INCREMENT=8 ;

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
  `obj_fi` int(10) unsigned NOT NULL default '0',
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
  `solution_hint` text,
  `image_file` varchar(100) default NULL,
  `params` text,
  `complete` enum('0','1') NOT NULL default '1',
  `created` varchar(14) NOT NULL default '',
  `original_id` int(11) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`question_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
INSERT INTO `rbac_fa` VALUES (110, 8, 'n');
INSERT INTO `rbac_fa` VALUES (111, 8, 'n');
INSERT INTO `rbac_fa` VALUES (112, 8, 'n');

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
) TYPE=MyISAM AUTO_INCREMENT=49 ;

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
INSERT INTO `rbac_operations` VALUES (21, 'create_sahs', 'create new SCORM/AICC learning module');
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
INSERT INTO `rbac_operations` VALUES (47, 'cat_administrate_users', 'Administrate local user');
INSERT INTO `rbac_operations` VALUES (48, 'read_users', 'read local users');

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
INSERT INTO `rbac_ta` VALUES (15, 27);
INSERT INTO `rbac_ta` VALUES (15, 28);
INSERT INTO `rbac_ta` VALUES (15, 29);
INSERT INTO `rbac_ta` VALUES (15, 31);
INSERT INTO `rbac_ta` VALUES (15, 32);
INSERT INTO `rbac_ta` VALUES (15, 42);
INSERT INTO `rbac_ta` VALUES (15, 43);
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
INSERT INTO `rbac_ta` VALUES (16, 25);
INSERT INTO `rbac_ta` VALUES (16, 27);
INSERT INTO `rbac_ta` VALUES (16, 28);
INSERT INTO `rbac_ta` VALUES (16, 29);
INSERT INTO `rbac_ta` VALUES (16, 31);
INSERT INTO `rbac_ta` VALUES (16, 32);
INSERT INTO `rbac_ta` VALUES (16, 42);
INSERT INTO `rbac_ta` VALUES (16, 43);
INSERT INTO `rbac_ta` VALUES (16, 47);
INSERT INTO `rbac_ta` VALUES (16, 48);
INSERT INTO `rbac_ta` VALUES (17, 1);
INSERT INTO `rbac_ta` VALUES (17, 2);
INSERT INTO `rbac_ta` VALUES (17, 3);
INSERT INTO `rbac_ta` VALUES (17, 4);
INSERT INTO `rbac_ta` VALUES (17, 6);
INSERT INTO `rbac_ta` VALUES (17, 7);
INSERT INTO `rbac_ta` VALUES (17, 8);
INSERT INTO `rbac_ta` VALUES (17, 17);
INSERT INTO `rbac_ta` VALUES (17, 18);
INSERT INTO `rbac_ta` VALUES (17, 20);
INSERT INTO `rbac_ta` VALUES (17, 21);
INSERT INTO `rbac_ta` VALUES (17, 22);
INSERT INTO `rbac_ta` VALUES (17, 23);
INSERT INTO `rbac_ta` VALUES (17, 24);
INSERT INTO `rbac_ta` VALUES (17, 25);
INSERT INTO `rbac_ta` VALUES (17, 26);
INSERT INTO `rbac_ta` VALUES (17, 27);
INSERT INTO `rbac_ta` VALUES (17, 28);
INSERT INTO `rbac_ta` VALUES (17, 29);
INSERT INTO `rbac_ta` VALUES (17, 31);
INSERT INTO `rbac_ta` VALUES (17, 32);
INSERT INTO `rbac_ta` VALUES (17, 42);
INSERT INTO `rbac_ta` VALUES (17, 43);
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
INSERT INTO `rbac_ta` VALUES (22, 48);
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
INSERT INTO `rbac_ta` VALUES (87, 23);
INSERT INTO `rbac_ta` VALUES (87, 24);
INSERT INTO `rbac_ta` VALUES (87, 25);
INSERT INTO `rbac_ta` VALUES (87, 26);
INSERT INTO `rbac_ta` VALUES (87, 27);
INSERT INTO `rbac_ta` VALUES (87, 28);
INSERT INTO `rbac_ta` VALUES (87, 29);
INSERT INTO `rbac_ta` VALUES (87, 31);
INSERT INTO `rbac_ta` VALUES (87, 32);
INSERT INTO `rbac_ta` VALUES (87, 42);
INSERT INTO `rbac_ta` VALUES (87, 43);
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
INSERT INTO `rbac_ta` VALUES (113, 1);
INSERT INTO `rbac_ta` VALUES (113, 2);
INSERT INTO `rbac_ta` VALUES (113, 3);
INSERT INTO `rbac_ta` VALUES (113, 4);

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

INSERT INTO `rbac_templates` VALUES (5, 'sahs', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'sahs', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'root', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'root', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'sahs', 3, 8);
INSERT INTO `rbac_templates` VALUES (14, 'sahs', 2, 8);
INSERT INTO `rbac_templates` VALUES (14, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'sahs', 7, 8);
INSERT INTO `rbac_templates` VALUES (3, 'sahs', 6, 8);
INSERT INTO `rbac_templates` VALUES (3, 'sahs', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'sahs', 8, 8);
INSERT INTO `rbac_templates` VALUES (4, 'sahs', 7, 8);
INSERT INTO `rbac_templates` VALUES (4, 'sahs', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (5, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (5, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'sahs', 4, 8);
INSERT INTO `rbac_templates` VALUES (3, 'sahs', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'sahs', 2, 8);
INSERT INTO `rbac_templates` VALUES (3, 'rolf', 3, 8);
INSERT INTO `rbac_templates` VALUES (3, 'rolf', 2, 8);
INSERT INTO `rbac_templates` VALUES (4, 'sahs', 2, 8);
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
INSERT INTO `rbac_templates` VALUES (81, 'sahs', 8, 8);
INSERT INTO `rbac_templates` VALUES (80, 'sahs', 8, 8);
INSERT INTO `rbac_templates` VALUES (80, 'sahs', 7, 8);
INSERT INTO `rbac_templates` VALUES (80, 'sahs', 6, 8);
INSERT INTO `rbac_templates` VALUES (80, 'sahs', 4, 8);
INSERT INTO `rbac_templates` VALUES (81, 'rolf', 3, 8);
INSERT INTO `rbac_templates` VALUES (80, 'sahs', 3, 8);
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
INSERT INTO `rbac_templates` VALUES (80, 'sahs', 2, 8);
INSERT INTO `rbac_templates` VALUES (80, 'sahs', 1, 8);
INSERT INTO `rbac_templates` VALUES (81, 'sahs', 7, 8);
INSERT INTO `rbac_templates` VALUES (81, 'sahs', 3, 8);
INSERT INTO `rbac_templates` VALUES (81, 'sahs', 2, 8);
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
INSERT INTO `rbac_templates` VALUES (110, 'lm', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'lm', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'lm', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'lm', 7, 8);
INSERT INTO `rbac_templates` VALUES (110, 'lm', 8, 8);
INSERT INTO `rbac_templates` VALUES (110, 'sahs', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'sahs', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'sahs', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'sahs', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'sahs', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'sahs', 7, 8);
INSERT INTO `rbac_templates` VALUES (110, 'sahs', 8, 8);
INSERT INTO `rbac_templates` VALUES (110, 'dbk', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'dbk', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'dbk', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'dbk', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'glo', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'glo', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'glo', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'glo', 7, 8);
INSERT INTO `rbac_templates` VALUES (110, 'glo', 8, 8);
INSERT INTO `rbac_templates` VALUES (110, 'frm', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'frm', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'frm', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'frm', 9, 8);
INSERT INTO `rbac_templates` VALUES (110, 'frm', 10, 8);
INSERT INTO `rbac_templates` VALUES (110, 'chat', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'chat', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'chat', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'chat', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'chat', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'file', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'file', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'file', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'file', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'file', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'tst', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'tst', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'tst', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'tst', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'tst', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 18, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 20, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 21, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 22, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 23, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 24, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 25, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 26, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 27, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 29, 8);
INSERT INTO `rbac_templates` VALUES (110, 'grp', 31, 8);
INSERT INTO `rbac_templates` VALUES (110, 'exc', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'exc', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'exc', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'exc', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'exc', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 18, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 20, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 21, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 22, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 25, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 26, 8);
INSERT INTO `rbac_templates` VALUES (110, 'fold', 29, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 1, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 4, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 6, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 7, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 8, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 17, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 18, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 20, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 21, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 22, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 23, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 24, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 25, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 26, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 27, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 29, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 31, 8);
INSERT INTO `rbac_templates` VALUES (110, 'crs', 32, 8);
INSERT INTO `rbac_templates` VALUES (111, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'lm', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'sahs', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'sahs', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'sahs', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'sahs', 7, 8);
INSERT INTO `rbac_templates` VALUES (111, 'sahs', 8, 8);
INSERT INTO `rbac_templates` VALUES (111, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'dbk', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'dbk', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'glo', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'glo', 7, 8);
INSERT INTO `rbac_templates` VALUES (111, 'glo', 8, 8);
INSERT INTO `rbac_templates` VALUES (111, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'frm', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'frm', 9, 8);
INSERT INTO `rbac_templates` VALUES (111, 'chat', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'chat', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'chat', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'file', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'file', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'file', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'tst', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'tst', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'tst', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'grp', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (111, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (111, 'exc', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'exc', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'exc', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'fold', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'fold', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'fold', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (111, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (111, 'crs', 4, 8);
INSERT INTO `rbac_templates` VALUES (111, 'crs', 7, 8);
INSERT INTO `rbac_templates` VALUES (111, 'crs', 8, 8);
INSERT INTO `rbac_templates` VALUES (112, 'lm', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'lm', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'sahs', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'sahs', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'sahs', 7, 8);
INSERT INTO `rbac_templates` VALUES (112, 'sahs', 8, 8);
INSERT INTO `rbac_templates` VALUES (112, 'dbk', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'dbk', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'glo', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'glo', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'glo', 7, 8);
INSERT INTO `rbac_templates` VALUES (112, 'glo', 8, 8);
INSERT INTO `rbac_templates` VALUES (112, 'frm', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'frm', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'chat', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'chat', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'file', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'file', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'tst', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'tst', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'grp', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'grp', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'grp', 7, 8);
INSERT INTO `rbac_templates` VALUES (112, 'grp', 8, 8);
INSERT INTO `rbac_templates` VALUES (112, 'exc', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'exc', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'fold', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'fold', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'crs', 2, 8);
INSERT INTO `rbac_templates` VALUES (112, 'crs', 3, 8);
INSERT INTO `rbac_templates` VALUES (112, 'crs', 7, 8);
INSERT INTO `rbac_templates` VALUES (112, 'crs', 8, 8);
INSERT INTO `rbac_templates` VALUES (3, 'cat', 25, 8);

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
  `assign_users` char(2) default '0',
  PRIMARY KEY  (`role_id`)
) TYPE=MyISAM;

#
# Dumping data for table `role_data`
#

INSERT INTO `role_data` VALUES (2, 0, '0');
INSERT INTO `role_data` VALUES (3, 0, '0');
INSERT INTO `role_data` VALUES (4, 0, '0');
INSERT INTO `role_data` VALUES (5, 1, '0');
INSERT INTO `role_data` VALUES (14, 0, '0');

# --------------------------------------------------------

#
# Table structure for table `sahs_lm`
#

CREATE TABLE `sahs_lm` (
  `id` int(11) NOT NULL default '0',
  `online` enum('y','n') default 'n',
  `api_adapter` varchar(80) default 'API',
  `api_func_prefix` varchar(20) default 'LMS',
  `credit` enum('credit','no_credit') NOT NULL default 'credit',
  `default_lesson_mode` enum('normal','browse') NOT NULL default 'normal',
  `auto_review` enum('y','n') NOT NULL default 'n',
  `type` enum('scorm','aicc','hacp') default 'scorm',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

#
# Dumping data for table `sahs_lm`
#


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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
# Table structure for table `scorm_object`
#

CREATE TABLE `scorm_object` (
  `obj_id` int(11) NOT NULL auto_increment,
  `title` varchar(200) default NULL,
  `type` char(3) default NULL,
  `slm_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
INSERT INTO `settings` VALUES ('db_version', '321');
INSERT INTO `settings` VALUES ('ilias_version', '3.2.0 2004-10-20');
INSERT INTO `settings` VALUES ('inst_info', '');
INSERT INTO `settings` VALUES ('inst_name', '');
INSERT INTO `settings` VALUES ('java_path', '');
INSERT INTO `settings` VALUES ('language', 'en');
INSERT INTO `settings` VALUES ('ldap_basedn', '');
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
INSERT INTO `settings` VALUES ('auth_mode', '1');
INSERT INTO `settings` VALUES ('auto_registration', '1');
INSERT INTO `settings` VALUES ('approve_recipient', '');
INSERT INTO `settings` VALUES ('require_login', '1');
INSERT INTO `settings` VALUES ('require_passwd', '1');
INSERT INTO `settings` VALUES ('require_passwd2', '1');
INSERT INTO `settings` VALUES ('require_firstname', '1');
INSERT INTO `settings` VALUES ('require_gender', '1');
INSERT INTO `settings` VALUES ('require_lastname', '1');
INSERT INTO `settings` VALUES ('require_institution', '');
INSERT INTO `settings` VALUES ('require_department', '');
INSERT INTO `settings` VALUES ('require_street', '');
INSERT INTO `settings` VALUES ('require_city', '');
INSERT INTO `settings` VALUES ('require_zipcode', '');
INSERT INTO `settings` VALUES ('require_country', '');
INSERT INTO `settings` VALUES ('require_phone_office', '');
INSERT INTO `settings` VALUES ('require_phone_home', '');
INSERT INTO `settings` VALUES ('require_phone_mobile', '');
INSERT INTO `settings` VALUES ('require_fax', '');
INSERT INTO `settings` VALUES ('require_email', '1');
INSERT INTO `settings` VALUES ('require_hobby', '');
INSERT INTO `settings` VALUES ('require_default_role', '1');
INSERT INTO `settings` VALUES ('require_referral_comment', '');

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
  `anonymous_id` varchar(32) NOT NULL default '',
  `value` double default NULL,
  `textanswer` text,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`answer_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=36 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=23 ;

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
) TYPE=MyISAM AUTO_INCREMENT=71 ;

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
  `obj_fi` int(11) unsigned NOT NULL default '0',
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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=5 ;

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
  `longname` varchar(20) NOT NULL default '',
  `shortname` char(2) NOT NULL default '',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`relation_id`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

#
# Dumping data for table `survey_relation`
#

INSERT INTO `survey_relation` VALUES (1, 'less', '<', 20040518195753);
INSERT INTO `survey_relation` VALUES (2, 'less_or_equal', '<=', 20040518195808);
INSERT INTO `survey_relation` VALUES (3, 'equal', '=', 20040518195816);
INSERT INTO `survey_relation` VALUES (4, 'not_equal', '<>', 20040518195839);
INSERT INTO `survey_relation` VALUES (5, 'more_or_equal', '>=', 20040518195852);
INSERT INTO `survey_relation` VALUES (6, 'more', '>', 20040518195903);

# --------------------------------------------------------

#
# Table structure for table `survey_survey`
#

CREATE TABLE `survey_survey` (
  `survey_id` int(11) NOT NULL auto_increment,
  `obj_fi` int(11) NOT NULL default '0',
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
  `anonymize` enum('0','1') NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`survey_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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

INSERT INTO `tree` VALUES (1, 1, 0, 1, 26, 1);
INSERT INTO `tree` VALUES (1, 7, 9, 5, 6, 3);
INSERT INTO `tree` VALUES (1, 8, 9, 7, 8, 3);
INSERT INTO `tree` VALUES (1, 9, 1, 2, 25, 2);
INSERT INTO `tree` VALUES (1, 10, 9, 9, 10, 3);
INSERT INTO `tree` VALUES (1, 11, 9, 11, 12, 3);
INSERT INTO `tree` VALUES (1, 12, 9, 3, 4, 3);
INSERT INTO `tree` VALUES (1, 14, 9, 13, 14, 3);
INSERT INTO `tree` VALUES (1, 15, 9, 15, 16, 3);
INSERT INTO `tree` VALUES (1, 16, 9, 17, 18, 3);
INSERT INTO `tree` VALUES (1, 17, 9, 19, 20, 3);
INSERT INTO `tree` VALUES (1, 18, 9, 21, 22, 3);
INSERT INTO `tree` VALUES (1, 19, 9, 23, 24, 3);

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
  KEY `active_id_2` (`active_id`),
  KEY `user_fi` (`user_fi`),
  KEY `test_fi` (`test_fi`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
  `distancemedian` enum('0','1') NOT NULL default '1',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`eval_settings_id`)
) TYPE=MyISAM COMMENT='User settings for statistical evaluation tool' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM COMMENT='Mark steps of mark schemas' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM COMMENT='Test and Assessment solutions' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM COMMENT='Relation table for questions in tests' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM COMMENT='ILIAS 3 Assessment Test types' AUTO_INCREMENT=4 ;

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
  `obj_fi` int(11) NOT NULL default '0',
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
  `ects_output` enum('0','1') NOT NULL default '0',
  `ects_fx` float default NULL,
  `complete` enum('0','1') NOT NULL default '1',
  `created` varchar(14) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  `ects_a` double NOT NULL default '90',
  `ects_b` double NOT NULL default '65',
  `ects_c` double NOT NULL default '35',
  `ects_d` double NOT NULL default '10',
  `ects_e` double NOT NULL default '0',
  PRIMARY KEY  (`test_id`),
  UNIQUE KEY `test_id` (`test_id`),
  KEY `test_id_2` (`test_id`)
) TYPE=MyISAM COMMENT='Tests in ILIAS Assessment' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM COMMENT='Editing times of an assessment test' AUTO_INCREMENT=1 ;

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
  `time_limit_owner` int(10) default '0',
  `time_limit_unlimited` int(2) default '0',
  `time_limit_from` int(10) default '0',
  `time_limit_until` int(10) default '0',
  `time_limit_message` int(2) default '0',
  `referral_comment` varchar(250) default '',
  `matriculation` varchar(40) default NULL,
  `active` int(4) unsigned NOT NULL default '0',
  `approve_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`usr_id`),
  KEY `login` (`login`,`passwd`)
) TYPE=MyISAM;

#
# Dumping data for table `usr_data`
#

INSERT INTO `usr_data` VALUES (6, 'root', 'dfa8327f5bfa4c672a04f9b38e348a70', 'root', 'user', '', 'm', 'ilias@yourserver.com', '', '', '', '', '', '', '2004-01-20 12:11:07', '2003-09-30 19:50:01', '0000-00-00 00:00:00', '', '', '', '', '', '', 7, 1, 0, 0, 0, '', NULL, 1, '0000-00-00 00:00:00');
INSERT INTO `usr_data` VALUES (13, 'anonymous', '294de3557d9d00b3d2d8a1e6aab028cf', 'anonymous', 'anonymous', '', 'm', 'nomail', NULL, NULL, NULL, NULL, NULL, '', '2003-08-15 11:03:36', '2003-08-15 10:07:30', '2003-08-15 10:07:30', '', '', '', '', '', '', 7, 1, 0, 0, 0, '', NULL, 1, '0000-00-00 00:00:00');

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
INSERT INTO `usr_session` VALUES ('dd90ca21fa965ec700b2f339bec83d5d', 1094112251, 'locator_data|a:0:{}locator_level|i:-1;', 1094110811, 0);

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=11 ;

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
) TYPE=MyISAM COMMENT='Master Table for XML objects' AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

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
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `xmlvalue`
#

