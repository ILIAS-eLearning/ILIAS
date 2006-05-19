-- MySQL dump 10.9
--
-- Host: localhost    Database: ilias360rel
-- ------------------------------------------------------
-- Server version	4.1.10a-standard

--
-- Table structure for table `addressbook`
--

DROP TABLE IF EXISTS `addressbook`;
CREATE TABLE `addressbook` (
  `addr_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `login` varchar(40) default NULL,
  `firstname` varchar(40) default NULL,
  `lastname` varchar(40) default NULL,
  `email` varchar(40) default NULL,
  PRIMARY KEY  (`addr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `addressbook`
--


/*!40000 ALTER TABLE `addressbook` DISABLE KEYS */;
LOCK TABLES `addressbook` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `addressbook` ENABLE KEYS */;

--
-- Table structure for table `aicc_course`
--

DROP TABLE IF EXISTS `aicc_course`;
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

--
-- Dumping data for table `aicc_course`
--


/*!40000 ALTER TABLE `aicc_course` DISABLE KEYS */;
LOCK TABLES `aicc_course` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `aicc_course` ENABLE KEYS */;

--
-- Table structure for table `aicc_object`
--

DROP TABLE IF EXISTS `aicc_object`;
CREATE TABLE `aicc_object` (
  `obj_id` int(11) NOT NULL auto_increment,
  `slm_id` int(11) NOT NULL default '0',
  `system_id` varchar(50) NOT NULL default '',
  `title` text NOT NULL,
  `description` text,
  `developer_id` varchar(50) default NULL,
  `type` varchar(3) NOT NULL default '',
  PRIMARY KEY  (`obj_id`),
  KEY `alm_id` (`slm_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `aicc_object`
--


/*!40000 ALTER TABLE `aicc_object` DISABLE KEYS */;
LOCK TABLES `aicc_object` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `aicc_object` ENABLE KEYS */;

--
-- Table structure for table `aicc_units`
--

DROP TABLE IF EXISTS `aicc_units`;
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

--
-- Dumping data for table `aicc_units`
--


/*!40000 ALTER TABLE `aicc_units` DISABLE KEYS */;
LOCK TABLES `aicc_units` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `aicc_units` ENABLE KEYS */;

--
-- Table structure for table `ass_log`
--

DROP TABLE IF EXISTS `ass_log`;
CREATE TABLE `ass_log` (
  `ass_log_id` int(11) NOT NULL auto_increment,
  `user_fi` int(11) NOT NULL default '0',
  `obj_fi` int(11) NOT NULL default '0',
  `logtext` text NOT NULL,
  `question_fi` int(11) default NULL,
  `original_fi` int(11) default NULL,
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`ass_log_id`),
  KEY `user_fi` (`user_fi`,`obj_fi`)
) TYPE=MyISAM COMMENT='Logging of Test&Assessment object changes';

--
-- Dumping data for table `ass_log`
--


/*!40000 ALTER TABLE `ass_log` DISABLE KEYS */;
LOCK TABLES `ass_log` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ass_log` ENABLE KEYS */;

--
-- Table structure for table `benchmark`
--

DROP TABLE IF EXISTS `benchmark`;
CREATE TABLE `benchmark` (
  `cdate` datetime default NULL,
  `module` varchar(150) default NULL,
  `benchmark` varchar(150) default NULL,
  `duration` double(14,5) default NULL,
  KEY `module` (`module`,`benchmark`)
) TYPE=MyISAM;

--
-- Dumping data for table `benchmark`
--


/*!40000 ALTER TABLE `benchmark` DISABLE KEYS */;
LOCK TABLES `benchmark` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `benchmark` ENABLE KEYS */;

--
-- Table structure for table `bookmark_data`
--

DROP TABLE IF EXISTS `bookmark_data`;
CREATE TABLE `bookmark_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `target` varchar(200) NOT NULL default '',
  `type` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`user_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `bookmark_data`
--


/*!40000 ALTER TABLE `bookmark_data` DISABLE KEYS */;
LOCK TABLES `bookmark_data` WRITE;
INSERT INTO `bookmark_data` VALUES (1,0,'dummy_folder','','','bmf');
UNLOCK TABLES;
/*!40000 ALTER TABLE `bookmark_data` ENABLE KEYS */;

--
-- Table structure for table `bookmark_tree`
--

DROP TABLE IF EXISTS `bookmark_tree`;
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

--
-- Dumping data for table `bookmark_tree`
--


/*!40000 ALTER TABLE `bookmark_tree` DISABLE KEYS */;
LOCK TABLES `bookmark_tree` WRITE;
INSERT INTO `bookmark_tree` VALUES (6,1,0,1,2,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `bookmark_tree` ENABLE KEYS */;

--
-- Table structure for table `chat_blocked`
--

DROP TABLE IF EXISTS `chat_blocked`;
CREATE TABLE `chat_blocked` (
  `chat_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table `chat_blocked`
--


/*!40000 ALTER TABLE `chat_blocked` DISABLE KEYS */;
LOCK TABLES `chat_blocked` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `chat_blocked` ENABLE KEYS */;

--
-- Table structure for table `chat_invitations`
--

DROP TABLE IF EXISTS `chat_invitations`;
CREATE TABLE `chat_invitations` (
  `room_id` int(11) NOT NULL default '0',
  `guest_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`room_id`,`guest_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `chat_invitations`
--


/*!40000 ALTER TABLE `chat_invitations` DISABLE KEYS */;
LOCK TABLES `chat_invitations` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `chat_invitations` ENABLE KEYS */;

--
-- Table structure for table `chat_record_data`
--

DROP TABLE IF EXISTS `chat_record_data`;
CREATE TABLE `chat_record_data` (
  `record_data_id` int(11) NOT NULL auto_increment,
  `record_id` int(11) NOT NULL default '0',
  `message` mediumtext NOT NULL,
  `msg_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`record_data_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `chat_record_data`
--


/*!40000 ALTER TABLE `chat_record_data` DISABLE KEYS */;
LOCK TABLES `chat_record_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `chat_record_data` ENABLE KEYS */;

--
-- Table structure for table `chat_records`
--

DROP TABLE IF EXISTS `chat_records`;
CREATE TABLE `chat_records` (
  `record_id` int(11) NOT NULL auto_increment,
  `moderator_id` int(11) NOT NULL default '0',
  `chat_id` int(11) NOT NULL default '0',
  `room_id` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `start_time` int(11) NOT NULL default '0',
  `end_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`record_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `chat_records`
--


/*!40000 ALTER TABLE `chat_records` DISABLE KEYS */;
LOCK TABLES `chat_records` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `chat_records` ENABLE KEYS */;

--
-- Table structure for table `chat_room_messages`
--

DROP TABLE IF EXISTS `chat_room_messages`;
CREATE TABLE `chat_room_messages` (
  `entry_id` int(11) NOT NULL auto_increment,
  `chat_id` int(11) NOT NULL default '0',
  `room_id` int(11) NOT NULL default '0',
  `message` text,
  `commit_timestamp` timestamp NOT NULL,
  PRIMARY KEY  (`entry_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `chat_room_messages`
--


/*!40000 ALTER TABLE `chat_room_messages` DISABLE KEYS */;
LOCK TABLES `chat_room_messages` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `chat_room_messages` ENABLE KEYS */;

--
-- Table structure for table `chat_rooms`
--

DROP TABLE IF EXISTS `chat_rooms`;
CREATE TABLE `chat_rooms` (
  `room_id` int(11) NOT NULL auto_increment,
  `chat_id` int(11) NOT NULL default '0',
  `title` varchar(64) default NULL,
  `owner` int(11) NOT NULL default '0',
  PRIMARY KEY  (`room_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `chat_rooms`
--


/*!40000 ALTER TABLE `chat_rooms` DISABLE KEYS */;
LOCK TABLES `chat_rooms` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `chat_rooms` ENABLE KEYS */;

--
-- Table structure for table `chat_user`
--

DROP TABLE IF EXISTS `chat_user`;
CREATE TABLE `chat_user` (
  `usr_id` int(11) NOT NULL default '0',
  `chat_id` int(11) NOT NULL default '0',
  `room_id` int(11) NOT NULL default '0',
  `last_conn_timestamp` int(14) default NULL,
  `kicked` tinyint(4) default '0',
  PRIMARY KEY  (`usr_id`,`chat_id`,`room_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `chat_user`
--


/*!40000 ALTER TABLE `chat_user` DISABLE KEYS */;
LOCK TABLES `chat_user` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `chat_user` ENABLE KEYS */;

--
-- Table structure for table `conditions`
--

DROP TABLE IF EXISTS `conditions`;
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
) TYPE=MyISAM;

--
-- Dumping data for table `conditions`
--


/*!40000 ALTER TABLE `conditions` DISABLE KEYS */;
LOCK TABLES `conditions` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `conditions` ENABLE KEYS */;

--
-- Table structure for table `container_settings`
--

DROP TABLE IF EXISTS `container_settings`;
CREATE TABLE `container_settings` (
  `id` int(11) NOT NULL default '0',
  `keyword` char(40) NOT NULL default '',
  `value` char(50) default NULL,
  PRIMARY KEY  (`id`,`keyword`)
) TYPE=MyISAM;

--
-- Dumping data for table `container_settings`
--


/*!40000 ALTER TABLE `container_settings` DISABLE KEYS */;
LOCK TABLES `container_settings` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `container_settings` ENABLE KEYS */;

--
-- Table structure for table `content_object`
--

DROP TABLE IF EXISTS `content_object`;
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
  `hist_user_comments` enum('y','n') default 'n',
  `public_access_mode` enum('complete','selected') NOT NULL default 'complete',
  `public_html_file` varchar(50) NOT NULL default '',
  `public_xml_file` varchar(50) NOT NULL default '',
  `downloads_active` enum('y','n') default 'n',
  `downloads_public_active` enum('y','n') NOT NULL default 'y',
  `pub_notes` enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `content_object`
--


/*!40000 ALTER TABLE `content_object` DISABLE KEYS */;
LOCK TABLES `content_object` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `content_object` ENABLE KEYS */;

--
-- Table structure for table `crs_archives`
--

DROP TABLE IF EXISTS `crs_archives`;
CREATE TABLE `crs_archives` (
  `archive_id` int(11) NOT NULL auto_increment,
  `course_id` int(11) NOT NULL default '0',
  `archive_name` varchar(255) NOT NULL default '',
  `archive_type` tinyint(2) NOT NULL default '0',
  `archive_date` int(11) default NULL,
  `archive_size` int(11) default NULL,
  `archive_lang` varchar(16) default NULL,
  PRIMARY KEY  (`archive_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_archives`
--


/*!40000 ALTER TABLE `crs_archives` DISABLE KEYS */;
LOCK TABLES `crs_archives` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_archives` ENABLE KEYS */;

--
-- Table structure for table `crs_groupings`
--

DROP TABLE IF EXISTS `crs_groupings`;
CREATE TABLE `crs_groupings` (
  `crs_grp_id` int(11) NOT NULL default '0',
  `crs_ref_id` int(11) NOT NULL default '0',
  `crs_id` int(11) NOT NULL default '0',
  `unique_field` char(32) NOT NULL default '',
  PRIMARY KEY  (`crs_grp_id`),
  KEY `crs_id` (`crs_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_groupings`
--


/*!40000 ALTER TABLE `crs_groupings` DISABLE KEYS */;
LOCK TABLES `crs_groupings` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_groupings` ENABLE KEYS */;

--
-- Table structure for table `crs_items`
--

DROP TABLE IF EXISTS `crs_items`;
CREATE TABLE `crs_items` (
  `parent_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `activation_unlimited` tinyint(2) default NULL,
  `activation_start` int(8) default NULL,
  `activation_end` int(8) default NULL,
  `position` int(11) default NULL,
  PRIMARY KEY  (`parent_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_items`
--


/*!40000 ALTER TABLE `crs_items` DISABLE KEYS */;
LOCK TABLES `crs_items` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_items` ENABLE KEYS */;

--
-- Table structure for table `crs_lm_history`
--

DROP TABLE IF EXISTS `crs_lm_history`;
CREATE TABLE `crs_lm_history` (
  `usr_id` int(11) NOT NULL default '0',
  `crs_ref_id` int(11) NOT NULL default '0',
  `lm_ref_id` int(11) NOT NULL default '0',
  `lm_page_id` int(11) NOT NULL default '0',
  `last_access` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`crs_ref_id`,`lm_ref_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_lm_history`
--


/*!40000 ALTER TABLE `crs_lm_history` DISABLE KEYS */;
LOCK TABLES `crs_lm_history` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_lm_history` ENABLE KEYS */;

--
-- Table structure for table `crs_members`
--

DROP TABLE IF EXISTS `crs_members`;
CREATE TABLE `crs_members` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `status` tinyint(2) NOT NULL default '0',
  `role` tinyint(2) NOT NULL default '0',
  `passed` tinyint(1) default NULL,
  PRIMARY KEY  (`obj_id`,`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_members`
--


/*!40000 ALTER TABLE `crs_members` DISABLE KEYS */;
LOCK TABLES `crs_members` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_members` ENABLE KEYS */;

--
-- Table structure for table `crs_objective_lm`
--

DROP TABLE IF EXISTS `crs_objective_lm`;
CREATE TABLE `crs_objective_lm` (
  `lm_ass_id` int(11) NOT NULL auto_increment,
  `objective_id` int(11) NOT NULL default '0',
  `ref_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `type` char(6) NOT NULL default '',
  PRIMARY KEY  (`lm_ass_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_objective_lm`
--


/*!40000 ALTER TABLE `crs_objective_lm` DISABLE KEYS */;
LOCK TABLES `crs_objective_lm` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_objective_lm` ENABLE KEYS */;

--
-- Table structure for table `crs_objective_qst`
--

DROP TABLE IF EXISTS `crs_objective_qst`;
CREATE TABLE `crs_objective_qst` (
  `qst_ass_id` int(11) NOT NULL auto_increment,
  `objective_id` int(11) NOT NULL default '0',
  `ref_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `question_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`qst_ass_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_objective_qst`
--


/*!40000 ALTER TABLE `crs_objective_qst` DISABLE KEYS */;
LOCK TABLES `crs_objective_qst` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_objective_qst` ENABLE KEYS */;

--
-- Table structure for table `crs_objective_results`
--

DROP TABLE IF EXISTS `crs_objective_results`;
CREATE TABLE `crs_objective_results` (
  `res_id` int(11) NOT NULL auto_increment,
  `usr_id` int(11) NOT NULL default '0',
  `question_id` int(11) NOT NULL default '0',
  `points` int(11) NOT NULL default '0',
  PRIMARY KEY  (`res_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_objective_results`
--


/*!40000 ALTER TABLE `crs_objective_results` DISABLE KEYS */;
LOCK TABLES `crs_objective_results` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_objective_results` ENABLE KEYS */;

--
-- Table structure for table `crs_objective_tst`
--

DROP TABLE IF EXISTS `crs_objective_tst`;
CREATE TABLE `crs_objective_tst` (
  `test_objective_id` int(11) NOT NULL auto_increment,
  `objective_id` int(11) NOT NULL default '0',
  `ref_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `tst_status` tinyint(2) default NULL,
  `tst_limit` tinyint(3) default NULL,
  PRIMARY KEY  (`test_objective_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_objective_tst`
--


/*!40000 ALTER TABLE `crs_objective_tst` DISABLE KEYS */;
LOCK TABLES `crs_objective_tst` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_objective_tst` ENABLE KEYS */;

--
-- Table structure for table `crs_objectives`
--

DROP TABLE IF EXISTS `crs_objectives`;
CREATE TABLE `crs_objectives` (
  `crs_id` int(11) NOT NULL default '0',
  `objective_id` int(11) NOT NULL auto_increment,
  `title` varchar(70) NOT NULL default '',
  `description` varchar(128) NOT NULL default '',
  `position` int(3) NOT NULL default '0',
  `created` int(11) NOT NULL default '0',
  PRIMARY KEY  (`objective_id`),
  KEY `crs_id` (`crs_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_objectives`
--


/*!40000 ALTER TABLE `crs_objectives` DISABLE KEYS */;
LOCK TABLES `crs_objectives` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_objectives` ENABLE KEYS */;

--
-- Table structure for table `crs_settings`
--

DROP TABLE IF EXISTS `crs_settings`;
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
  `abo` tinyint(2) default '1',
  `objective_view` tinyint(2) default '0',
  `waiting_list` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_settings`
--


/*!40000 ALTER TABLE `crs_settings` DISABLE KEYS */;
LOCK TABLES `crs_settings` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_settings` ENABLE KEYS */;

--
-- Table structure for table `crs_start`
--

DROP TABLE IF EXISTS `crs_start`;
CREATE TABLE `crs_start` (
  `crs_start_id` int(11) NOT NULL auto_increment,
  `crs_id` int(11) NOT NULL default '0',
  `item_ref_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`crs_start_id`),
  KEY `crs_id` (`crs_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_start`
--


/*!40000 ALTER TABLE `crs_start` DISABLE KEYS */;
LOCK TABLES `crs_start` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_start` ENABLE KEYS */;

--
-- Table structure for table `crs_subscribers`
--

DROP TABLE IF EXISTS `crs_subscribers`;
CREATE TABLE `crs_subscribers` (
  `usr_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `sub_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_subscribers`
--


/*!40000 ALTER TABLE `crs_subscribers` DISABLE KEYS */;
LOCK TABLES `crs_subscribers` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_subscribers` ENABLE KEYS */;

--
-- Table structure for table `crs_waiting_list`
--

DROP TABLE IF EXISTS `crs_waiting_list`;
CREATE TABLE `crs_waiting_list` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `sub_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`,`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `crs_waiting_list`
--


/*!40000 ALTER TABLE `crs_waiting_list` DISABLE KEYS */;
LOCK TABLES `crs_waiting_list` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `crs_waiting_list` ENABLE KEYS */;

--
-- Table structure for table `ctrl_calls`
--

DROP TABLE IF EXISTS `ctrl_calls`;
CREATE TABLE `ctrl_calls` (
  `parent` varchar(100) NOT NULL default '',
  `child` varchar(100) default NULL,
  KEY `jmp_parent` (`parent`)
) TYPE=MyISAM;

--
-- Dumping data for table `ctrl_calls`
--


/*!40000 ALTER TABLE `ctrl_calls` DISABLE KEYS */;
LOCK TABLES `ctrl_calls` WRITE;
INSERT INTO `ctrl_calls` VALUES ('ilobjquestionpoolgui','ilpageobjectgui'),('ilobjquestionpoolgui','ass_multiplechoicegui'),('ilobjquestionpoolgui','ass_clozetestgui'),('ilobjquestionpoolgui','ass_matchingquestiongui'),('ilobjquestionpoolgui','ass_orderingquestiongui'),('ilobjquestionpoolgui','ass_imagemapquestiongui'),('ilobjquestionpoolgui','ass_javaappletgui'),('ilobjquestionpoolgui','ass_textquestiongui'),('ilobjquestionpoolgui','ilmdeditorgui'),('ilobjquestionpoolgui','ilpermissiongui'),('ilobjtestgui','ilobjcoursegui'),('ilobjtestgui','ilmdeditorgui'),('ilobjtestgui','iltestoutputgui'),('ilobjtestgui','iltestevaluationgui'),('ilobjtestgui','ilpermissiongui'),('ilobjtestgui','ilinfoscreengui'),('ilobjtestgui','illearningprogressgui'),('ilobjchatgui','ilpermissiongui'),('ilobjchatservergui','ilpermissiongui'),('iladministrationgui','ilobjgroupgui'),('iladministrationgui','ilobjfoldergui'),('iladministrationgui','ilobjfilegui'),('iladministrationgui','ilobjcoursegui'),('iladministrationgui','ilcourseobjectivesgui'),('iladministrationgui','ilobjsahslearningmodulegui'),('iladministrationgui','ilobjchatgui'),('iladministrationgui','ilobjforumgui'),('iladministrationgui','ilobjlearningmodulegui'),('iladministrationgui','ilobjdlbookgui'),('iladministrationgui','ilobjglossarygui'),('iladministrationgui','ilobjquestionpoolgui'),('iladministrationgui','ilobjsurveyquestionpoolgui'),('iladministrationgui','ilobjtestgui'),('iladministrationgui','ilobjsurveygui'),('iladministrationgui','ilobjexercisegui'),('iladministrationgui','ilobjmediapoolgui'),('iladministrationgui','ilobjfilebasedlmgui'),('iladministrationgui','ilobjcategorygui'),('iladministrationgui','ilobjusergui'),('iladministrationgui','ilobjrolegui'),('iladministrationgui','ilobjuserfoldergui'),('iladministrationgui','ilobjilinccoursegui'),('iladministrationgui','ilobjilincclassroomgui'),('iladministrationgui','ilobjlinkresourcegui'),('iladministrationgui','ilobjroletemplategui'),('iladministrationgui','ilobjstylesheetgui'),('iladministrationgui','ilobjrootfoldergui'),('iladministrationgui','ilobjsystemfoldergui'),('iladministrationgui','ilobjrolefoldergui'),('iladministrationgui','ilobjauthsettingsgui'),('iladministrationgui','ilobjchatservergui'),('iladministrationgui','ilobjlanguagefoldergui'),('iladministrationgui','ilobjmailgui'),('iladministrationgui','ilobjobjectfoldergui'),('iladministrationgui','ilobjpaymentsettingsgui'),('iladministrationgui','ilobjrecoveryfoldergui'),('iladministrationgui','ilobjsearchsettingsgui'),('iladministrationgui','ilobjstylesettingsgui'),('iladministrationgui','ilobjtaxonomyfoldergui'),('iladministrationgui','ilobjassessmentfoldergui'),('iladministrationgui','ilobjexternaltoolssettingsgui'),('iladministrationgui','ilobjusertrackinggui'),('ilinfoscreengui','ilnotegui'),('ilinfoscreengui','ilfeedbackgui'),('ilfeedbackgui','\"'),('ilobjassessmentfoldergui','ilpermissiongui'),('ilobjauthsettingsgui','ilpermissiongui'),('ilobjcategorygui','ilpermissiongui'),('ilobjexercisegui','ilpermissiongui'),('ilobjexternaltoolssettingsgui','ilpermissiongui'),('ilobjfilegui','ilmdeditorgui'),('ilobjfilegui','ilinfoscreengui'),('ilobjfilegui','ilpermissiongui'),('ilobjfoldergui','ilconditionhandlerinterface'),('ilobjfoldergui','ilpermissiongui'),('ilobjforumgui','ilpermissiongui'),('ilobjgroupgui','ilregistergui'),('ilobjgroupgui','ilconditionhandlerinterface'),('ilobjgroupgui','ilpermissiongui'),('ilobjlanguagefoldergui','ilpermissiongui'),('ilobjmailgui','ilpermissiongui'),('ilobjobjectfoldergui','ilpermissiongui'),('ilobjrecoveryfoldergui','ilpermissiongui'),('ilobjrolefoldergui','ilpermissiongui'),('ilobjrolegui',''),('ilobjroletemplategui',''),('ilobjrootfoldergui','ilpermissiongui'),('ilobjstylesettingsgui','ilpermissiongui'),('ilobjstylesheetgui',''),('ilobjsystemfoldergui','ilpermissiongui'),('ilobjsysusertrackinggui','ilpermissiongui'),('ilobjuserfoldergui','ilpermissiongui'),('ilobjusergui','illearningprogressgui'),('ilpdnotesgui','ilnotegui'),('ilpermissiongui','ilobjrolegui'),('ilpersonaldesktopgui','ilpersonalprofilegui'),('ilpersonaldesktopgui','ilbookmarkadministrationgui'),('ilpersonaldesktopgui','ilobjusergui'),('ilpersonaldesktopgui','ilpdnotesgui'),('ilpersonaldesktopgui','illearningprogressgui'),('ilpersonalprofilegui',''),('ilrepositorygui','ilobjgroupgui'),('ilrepositorygui','ilobjfoldergui'),('ilrepositorygui','ilobjfilegui'),('ilrepositorygui','ilobjcoursegui'),('ilrepositorygui','ilcourseobjectivesgui'),('ilrepositorygui','ilobjsahslearningmodulegui'),('ilrepositorygui','ilobjchatgui'),('ilrepositorygui','ilobjforumgui'),('ilrepositorygui','ilobjlearningmodulegui'),('ilrepositorygui','ilobjdlbookgui'),('ilrepositorygui','ilobjglossarygui'),('ilrepositorygui','ilobjquestionpoolgui'),('ilrepositorygui','ilobjsurveyquestionpoolgui'),('ilrepositorygui','ilobjtestgui'),('ilrepositorygui','ilobjsurveygui'),('ilrepositorygui','ilobjexercisegui'),('ilrepositorygui','ilobjmediapoolgui'),('ilrepositorygui','ilobjfilebasedlmgui'),('ilrepositorygui','ilobjcategorygui'),('ilrepositorygui','ilobjusergui'),('ilrepositorygui','ilobjrolegui'),('ilrepositorygui','ilobjuserfoldergui'),('ilrepositorygui','ilobjilinccoursegui'),('ilrepositorygui','ilobjilincclassroomgui'),('ilrepositorygui','ilobjlinkresourcegui'),('ilrepositorygui','ilobjrootfoldergui'),('ileditclipboardgui','ilobjmediaobjectgui'),('ilglossaryeditorgui','ilobjglossarygui'),('ilglossarytermgui','iltermdefinitioneditorgui'),('illmeditorgui','ilobjdlbookgui'),('illmeditorgui','ilmetadatagui'),('illmeditorgui','ilobjlearningmodulegui'),('illmpageobjectgui','ilpageobjectgui'),('illmpageobjectgui','ilmdeditorgui'),('illmpresentationgui','ilnotegui'),('illmpresentationgui','ilinfoscreengui'),('ilobjaicclearningmodulegui','ilfilesystemgui'),('ilobjaicclearningmodulegui','ilmdeditorgui'),('ilobjaicclearningmodulegui','ilpermissiongui'),('ilobjaicclearningmodulegui','illearningprogressgui'),('ilobjdlbookgui','illmpageobjectgui'),('ilobjdlbookgui','ilstructureobjectgui'),('ilobjdlbookgui','ilobjstylesheetgui'),('ilobjdlbookgui','ilmdeditorgui'),('ilobjdlbookgui','illearningprogressgui'),('ilobjdlbookgui','ilpermissiongui'),('ilobjfilebasedlmgui','ilfilesystemgui'),('ilobjfilebasedlmgui','ilmdeditorgui'),('ilobjfilebasedlmgui','ilpermissiongui'),('ilobjglossarygui','ilglossarytermgui'),('ilobjglossarygui','ilmdeditorgui'),('ilobjglossarygui','ilpermissiongui'),('ilobjhacplearningmodulegui','ilfilesystemgui'),('ilobjhacplearningmodulegui','ilmdeditorgui'),('ilobjhacplearningmodulegui','ilpermissiongui'),('ilobjlearningmodulegui','illmpageobjectgui'),('ilobjlearningmodulegui','ilstructureobjectgui'),('ilobjlearningmodulegui','ilobjstylesheetgui'),('ilobjlearningmodulegui','ilmdeditorgui'),('ilobjlearningmodulegui','illearningprogressgui'),('ilobjlearningmodulegui','ilpermissiongui'),('ilobjmediapoolgui','ilobjmediaobjectgui'),('ilobjmediapoolgui','ilobjfoldergui'),('ilobjmediapoolgui','ileditclipboardgui'),('ilobjmediapoolgui','ilpermissiongui'),('ilobjsahslearningmodulegui','ilfilesystemgui'),('ilobjsahslearningmodulegui','ilmdeditorgui'),('ilobjsahslearningmodulegui','ilpermissiongui'),('ilobjscormlearningmodulegui','ilfilesystemgui'),('ilobjscormlearningmodulegui','ilmdeditorgui'),('ilobjscormlearningmodulegui','ilpermissiongui'),('ilobjscormlearningmodulegui','illearningprogressgui'),('ilstructureobjectgui','ilconditionhandlerinterface'),('ilstructureobjectgui','ilmdeditorgui'),('iltermdefinitioneditorgui','ilpageobjectgui'),('iltermdefinitioneditorgui','ilmdeditorgui'),('ilobjmediaobjectgui','ilinternallinkgui'),('ilobjmediaobjectgui','ilmdeditorgui'),('ilpageeditorgui','ilpcparagraphgui'),('ilpageeditorgui','ilpctablegui'),('ilpageeditorgui','ilpctabledatagui'),('ilpageeditorgui','ilpcmediaobjectgui'),('ilpageeditorgui','ilpclistgui'),('ilpageeditorgui','ilpclistitemgui'),('ilpageeditorgui','ilpcfilelistgui'),('ilpageeditorgui','ilpcfileitemgui'),('ilpageeditorgui','ilobjmediaobjectgui'),('ilpageeditorgui','ilpcsourcecodegui'),('ilpageeditorgui','ilinternallinkgui'),('ilpageeditorgui','ilpcquestiongui'),('ilpageobjectgui','ilpageeditorgui'),('ilpageobjectgui','ileditclipboardgui'),('ilpageobjectgui','ilmediapooltargetselector'),('ilcoursecontentinterface','ilconditionhandlerinterface'),('ilobjcoursegui','ilcourseregistergui'),('ilobjcoursegui','ilpaymentpurchasegui'),('ilobjcoursegui','ilcourseobjectivesgui'),('ilobjcoursegui','ilconditionhandlerinterface'),('ilobjcoursegui','ilobjcoursegroupinggui'),('ilobjcoursegui','ilmdeditorgui'),('ilobjcoursegui','ilinfoscreengui'),('ilobjcoursegui','illearningprogressgui'),('ilobjcoursegui','ilpermissiongui'),('ilobjilincclassroomgui',''),('ilobjilinccoursegui','ilobjilincclassroomgui'),('ilobjilinccoursegui','ilpermissiongui'),('ilobjlinkresourcegui','ilmdeditorgui'),('ilobjlinkresourcegui','ilpermissiongui'),('ilobjlinkresourcegui','ilinfoscreengui'),('ilobjpaymentsettingsgui','ilpermissiongui'),('ilpaymentgui','ilpaymentshoppincartgui'),('ilpaymentgui','ilpaymentshoppingcartgui'),('ilpaymentgui','ilpaymentbuyedobjectsgui'),('ilpaymentadmingui','ilpaymenttrusteegui'),('ilpaymentadmingui','ilpaymentstatisticgui'),('ilpaymentadmingui','ilpaymentobjectgui'),('ilpaymentadmingui','ilpaymentbilladmingui'),('ilobjsearchsettingsgui','ilpermissiongui'),('ilsearchcontroller','ilsearchgui'),('ilsearchcontroller','iladvancedsearchgui'),('ilsearchcontroller','ilsearchresultgui'),('illearningprogressgui','illplistofobjectsgui'),('illearningprogressgui','illplistofsettingsgui'),('illearningprogressgui','illplistofprogressgui'),('illpfiltergui',''),('illpgui',''),('illplistofobjectsgui','illpfiltergui'),('illplistofprogressgui',''),('illplistofprogressgui','illpfiltergui'),('illplistofprogressgui','ilpdfpresentation'),('illplistofsettingsgui',''),('ilobjusertrackinggui','illearningprogressgui'),('ilobjusertrackinggui','ilpermissiongui'),('ilpdfpresentation',''),('ilobjsurveygui','ilsurveyevaluationgui'),('ilobjsurveygui','ilsurveyexecutiongui'),('ilobjsurveygui','ilmdeditorgui'),('ilobjsurveygui','ilpermissiongui'),('ilobjsurveyquestionpoolgui','surveynominalquestiongui'),('ilobjsurveyquestionpoolgui','surveymetricquestiongui'),('ilobjsurveyquestionpoolgui','surveyordinalquestiongui'),('ilobjsurveyquestionpoolgui','surveytextquestiongui'),('ilobjsurveyquestionpoolgui','ilmdeditorgui'),('ilobjsurveyquestionpoolgui','ilpermissiongui');
UNLOCK TABLES;
/*!40000 ALTER TABLE `ctrl_calls` ENABLE KEYS */;

--
-- Table structure for table `ctrl_classfile`
--

DROP TABLE IF EXISTS `ctrl_classfile`;
CREATE TABLE `ctrl_classfile` (
  `class` varchar(100) NOT NULL default '',
  `file` varchar(250) default NULL,
  PRIMARY KEY  (`class`)
) TYPE=MyISAM;

--
-- Dumping data for table `ctrl_classfile`
--


/*!40000 ALTER TABLE `ctrl_classfile` DISABLE KEYS */;
LOCK TABLES `ctrl_classfile` WRITE;
INSERT INTO `ctrl_classfile` VALUES ('ilobjquestionpoolgui','assessment/classes/class.ilObjQuestionPoolGUI.php'),('ilobjtestgui','assessment/classes/class.ilObjTestGUI.php'),('ilobjchatgui','chat/classes/class.ilObjChatGUI.php'),('ilobjchatservergui','chat/classes/class.ilObjChatServerGUI.php'),('iladministrationgui','classes/class.ilAdministrationGUI.php'),('ilinfoscreengui','classes/class.ilInfoScreenGUI.php'),('ilfeedbackgui','classes/class.ilInfoScreenGUI.php'),('ilobjassessmentfoldergui','classes/class.ilObjAssessmentFolderGUI.php'),('ilobjauthsettingsgui','classes/class.ilObjAuthSettingsGUI.php'),('ilobjcategorygui','classes/class.ilObjCategoryGUI.php'),('ilobjexercisegui','classes/class.ilObjExerciseGUI.php'),('ilobjexternaltoolssettingsgui','classes/class.ilObjExternalToolsSettingsGUI.php'),('ilobjfilegui','classes/class.ilObjFileGUI.php'),('ilobjfoldergui','classes/class.ilObjFolderGUI.php'),('ilobjforumgui','classes/class.ilObjForumGUI.php'),('ilobjgroupgui','classes/class.ilObjGroupGUI.php'),('ilobjlanguagefoldergui','classes/class.ilObjLanguageFolderGUI.php'),('ilobjmailgui','classes/class.ilObjMailGUI.php'),('ilobjobjectfoldergui','classes/class.ilObjObjectFolderGUI.php'),('ilobjrecoveryfoldergui','classes/class.ilObjRecoveryFolderGUI.php'),('ilobjrolefoldergui','classes/class.ilObjRoleFolderGUI.php'),('ilobjrolegui','classes/class.ilObjRoleGUI.php'),('ilobjroletemplategui','classes/class.ilObjRoleTemplateGUI.php'),('ilobjrootfoldergui','classes/class.ilObjRootFolderGUI.php'),('ilobjstylesettingsgui','classes/class.ilObjStyleSettingsGUI.php'),('ilobjstylesheetgui','classes/class.ilObjStyleSheetGUI.php'),('ilobjsystemfoldergui','classes/class.ilObjSystemFolderGUI.php'),('ilobjsysusertrackinggui','classes/class.ilObjSysUserTrackingGUI.php'),('ilobjuserfoldergui','classes/class.ilObjUserFolderGUI.php'),('ilobjusergui','classes/class.ilObjUserGUI.php'),('ilpdnotesgui','classes/class.ilPDNotesGUI.php'),('ilpermissiongui','classes/class.ilPermissionGUI.php'),('ilpersonaldesktopgui','classes/class.ilPersonalDesktopGUI.php'),('ilpersonalprofilegui','classes/class.ilPersonalProfileGUI.php'),('ilrepositorygui','classes/class.ilRepositoryGUI.php'),('ileditclipboardgui','content/classes/class.ilEditClipboardGUI.php'),('ilglossaryeditorgui','content/classes/class.ilGlossaryEditorGUI.php'),('ilglossarytermgui','content/classes/class.ilGlossaryTermGUI.php'),('illmeditorgui','content/classes/class.ilLMEditorGUI.php'),('illmpageobjectgui','content/classes/class.ilLMPageObjectGUI.php'),('illmpresentationgui','content/classes/class.ilLMPresentationGUI.php'),('ilobjaicclearningmodulegui','content/classes/class.ilObjAICCLearningModuleGUI.php'),('ilobjdlbookgui','content/classes/class.ilObjDlBookGUI.php'),('ilobjfilebasedlmgui','content/classes/class.ilObjFileBasedLMGUI.php'),('ilobjglossarygui','content/classes/class.ilObjGlossaryGUI.php'),('ilobjhacplearningmodulegui','content/classes/class.ilObjHACPLearningModuleGUI.php'),('ilobjlearningmodulegui','content/classes/class.ilObjLearningModuleGUI.php'),('ilobjmediapoolgui','content/classes/class.ilObjMediaPoolGUI.php'),('ilobjsahslearningmodulegui','content/classes/class.ilObjSAHSLearningModuleGUI.php'),('ilobjscormlearningmodulegui','content/classes/class.ilObjSCORMLearningModuleGUI.php'),('ilstructureobjectgui','content/classes/class.ilStructureObjectGUI.php'),('iltermdefinitioneditorgui','content/classes/class.ilTermDefinitionEditorGUI.php'),('ilobjmediaobjectgui','content/classes/Media/class.ilObjMediaObjectGUI.php'),('ilpageeditorgui','content/classes/Pages/class.ilPageEditorGUI.php'),('ilpageobjectgui','content/classes/Pages/class.ilPageObjectGUI.php'),('ilcoursecontentinterface','course/classes/class.ilCourseContentInterface.php'),('ilobjcoursegui','course/classes/class.ilObjCourseGUI.php'),('ilobjilincclassroomgui','ilinc/classes/class.ilObjiLincClassroomGUI.php'),('ilobjilinccoursegui','ilinc/classes/class.ilObjiLincCourseGUI.php'),('ilobjlinkresourcegui','link/classes/class.ilObjLinkResourceGUI.php'),('ilobjpaymentsettingsgui','payment/classes/class.ilObjPaymentSettingsGUI.php'),('ilpaymentgui','payment/classes/class.ilPaymentGUI.php'),('ilpaymentadmingui','payment/classes/class.ilPaymentAdminGUI.php'),('ilobjsearchsettingsgui','Services/Search/classes/class.ilObjSearchSettingsGUI.php'),('ilsearchcontroller','Services/Search/classes/class.ilSearchController.php'),('illearningprogressgui','Services/Tracking/classes/class.ilLearningProgressGUI.php'),('illpfiltergui','Services/Tracking/classes/class.ilLPFilterGUI.php'),('illpgui','Services/Tracking/classes/class.ilLPGUI.php'),('illplistofobjectsgui','Services/Tracking/classes/class.ilLPListOfObjectsGUI.php'),('illplistofprogressgui','Services/Tracking/classes/class.ilLPListOfProgressGUI.php'),('illplistofsettingsgui','Services/Tracking/classes/class.ilLPListOfSettingsGUI.php'),('ilobjusertrackinggui','Services/Tracking/classes/class.ilObjUserTrackingGUI.php'),('ilpdfpresentation','Services/Tracking/classes/class.ilPDFPresentation.php'),('ilobjsurveygui','survey/classes/class.ilObjSurveyGUI.php'),('ilobjsurveyquestionpoolgui','survey/classes/class.ilObjSurveyQuestionPoolGUI.php');
UNLOCK TABLES;
/*!40000 ALTER TABLE `ctrl_classfile` ENABLE KEYS */;

--
-- Table structure for table `ctrl_structure`
--

DROP TABLE IF EXISTS `ctrl_structure`;
CREATE TABLE `ctrl_structure` (
  `root_class` varchar(40) NOT NULL default '',
  `call_node` mediumtext,
  `forward` mediumtext,
  `parent` mediumtext,
  PRIMARY KEY  (`root_class`)
) TYPE=MyISAM;

--
-- Dumping data for table `ctrl_structure`
--


/*!40000 ALTER TABLE `ctrl_structure` DISABLE KEYS */;
LOCK TABLES `ctrl_structure` WRITE;
INSERT INTO `ctrl_structure` VALUES ('ilrepositorygui','a:349:{i:1;a:2:{s:5:\"class\";s:15:\"ilrepositorygui\";s:6:\"parent\";i:0;}i:2;a:2:{s:5:\"class\";s:21:\"ilcourseobjectivesgui\";s:6:\"parent\";i:1;}i:3;a:2:{s:5:\"class\";s:16:\"ilobjcategorygui\";s:6:\"parent\";i:1;}i:4;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:3;}i:5;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:4;}i:6;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:5;}i:7;a:2:{s:5:\"class\";s:12:\"ilobjchatgui\";s:6:\"parent\";i:1;}i:8;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:7;}i:9;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:8;}i:10;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:9;}i:11;a:2:{s:5:\"class\";s:14:\"ilobjcoursegui\";s:6:\"parent\";i:1;}i:12;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:11;}i:13;a:2:{s:5:\"class\";s:21:\"ilcourseobjectivesgui\";s:6:\"parent\";i:11;}i:14;a:2:{s:5:\"class\";s:19:\"ilcourseregistergui\";s:6:\"parent\";i:11;}i:15;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:11;}i:16;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:15;}i:17;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:16;}i:18;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:15;}i:19;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:11;}i:20;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:19;}i:21;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:20;}i:22;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:21;}i:23;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:19;}i:24;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:23;}i:25;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:23;}i:26;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:25;}i:27;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:23;}i:28;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:27;}i:29;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:19;}i:30;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:29;}i:31;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:11;}i:32;a:2:{s:5:\"class\";s:22:\"ilobjcoursegroupinggui\";s:6:\"parent\";i:11;}i:33;a:2:{s:5:\"class\";s:20:\"ilpaymentpurchasegui\";s:6:\"parent\";i:11;}i:34;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:11;}i:35;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:34;}i:36;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:35;}i:37;a:2:{s:5:\"class\";s:14:\"ilobjdlbookgui\";s:6:\"parent\";i:1;}i:38;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:37;}i:39;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:38;}i:40;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:39;}i:41;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:40;}i:42;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:38;}i:43;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:42;}i:44;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:42;}i:45;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:44;}i:46;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:42;}i:47;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:46;}i:48;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:38;}i:49;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:48;}i:50;a:2:{s:5:\"class\";s:17:\"illmpageobjectgui\";s:6:\"parent\";i:37;}i:51;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:50;}i:52;a:2:{s:5:\"class\";s:15:\"ilpageobjectgui\";s:6:\"parent\";i:50;}i:53;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:52;}i:54;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:53;}i:55;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:54;}i:56;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:54;}i:57;a:2:{s:5:\"class\";s:25:\"ilmediapooltargetselector\";s:6:\"parent\";i:52;}i:58;a:2:{s:5:\"class\";s:15:\"ilpageeditorgui\";s:6:\"parent\";i:52;}i:59;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:58;}i:60;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:58;}i:61;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:60;}i:62;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:60;}i:63;a:2:{s:5:\"class\";s:15:\"ilpcfileitemgui\";s:6:\"parent\";i:58;}i:64;a:2:{s:5:\"class\";s:15:\"ilpcfilelistgui\";s:6:\"parent\";i:58;}i:65;a:2:{s:5:\"class\";s:11:\"ilpclistgui\";s:6:\"parent\";i:58;}i:66;a:2:{s:5:\"class\";s:15:\"ilpclistitemgui\";s:6:\"parent\";i:58;}i:67;a:2:{s:5:\"class\";s:18:\"ilpcmediaobjectgui\";s:6:\"parent\";i:58;}i:68;a:2:{s:5:\"class\";s:16:\"ilpcparagraphgui\";s:6:\"parent\";i:58;}i:69;a:2:{s:5:\"class\";s:15:\"ilpcquestiongui\";s:6:\"parent\";i:58;}i:70;a:2:{s:5:\"class\";s:17:\"ilpcsourcecodegui\";s:6:\"parent\";i:58;}i:71;a:2:{s:5:\"class\";s:16:\"ilpctabledatagui\";s:6:\"parent\";i:58;}i:72;a:2:{s:5:\"class\";s:12:\"ilpctablegui\";s:6:\"parent\";i:58;}i:73;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:37;}i:74;a:2:{s:5:\"class\";s:18:\"ilobjstylesheetgui\";s:6:\"parent\";i:37;}i:75;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:74;}i:76;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:37;}i:77;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:76;}i:78;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:77;}i:79;a:2:{s:5:\"class\";s:20:\"ilstructureobjectgui\";s:6:\"parent\";i:37;}i:80;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:79;}i:81;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:79;}i:82;a:2:{s:5:\"class\";s:16:\"ilobjexercisegui\";s:6:\"parent\";i:1;}i:83;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:82;}i:84;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:83;}i:85;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:84;}i:86;a:2:{s:5:\"class\";s:19:\"ilobjfilebasedlmgui\";s:6:\"parent\";i:1;}i:87;a:2:{s:5:\"class\";s:15:\"ilfilesystemgui\";s:6:\"parent\";i:86;}i:88;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:86;}i:89;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:86;}i:90;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:89;}i:91;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:90;}i:92;a:2:{s:5:\"class\";s:12:\"ilobjfilegui\";s:6:\"parent\";i:1;}i:93;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:92;}i:94;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:93;}i:95;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:94;}i:96;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:93;}i:97;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:92;}i:98;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:92;}i:99;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:98;}i:100;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:99;}i:101;a:2:{s:5:\"class\";s:14:\"ilobjfoldergui\";s:6:\"parent\";i:1;}i:102;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:101;}i:103;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:101;}i:104;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:103;}i:105;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:104;}i:106;a:2:{s:5:\"class\";s:13:\"ilobjforumgui\";s:6:\"parent\";i:1;}i:107;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:106;}i:108;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:107;}i:109;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:108;}i:110;a:2:{s:5:\"class\";s:16:\"ilobjglossarygui\";s:6:\"parent\";i:1;}i:111;a:2:{s:5:\"class\";s:17:\"ilglossarytermgui\";s:6:\"parent\";i:110;}i:112;a:2:{s:5:\"class\";s:25:\"iltermdefinitioneditorgui\";s:6:\"parent\";i:111;}i:113;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:112;}i:114;a:2:{s:5:\"class\";s:15:\"ilpageobjectgui\";s:6:\"parent\";i:112;}i:115;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:114;}i:116;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:115;}i:117;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:116;}i:118;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:116;}i:119;a:2:{s:5:\"class\";s:25:\"ilmediapooltargetselector\";s:6:\"parent\";i:114;}i:120;a:2:{s:5:\"class\";s:15:\"ilpageeditorgui\";s:6:\"parent\";i:114;}i:121;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:120;}i:122;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:120;}i:123;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:122;}i:124;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:122;}i:125;a:2:{s:5:\"class\";s:15:\"ilpcfileitemgui\";s:6:\"parent\";i:120;}i:126;a:2:{s:5:\"class\";s:15:\"ilpcfilelistgui\";s:6:\"parent\";i:120;}i:127;a:2:{s:5:\"class\";s:11:\"ilpclistgui\";s:6:\"parent\";i:120;}i:128;a:2:{s:5:\"class\";s:15:\"ilpclistitemgui\";s:6:\"parent\";i:120;}i:129;a:2:{s:5:\"class\";s:18:\"ilpcmediaobjectgui\";s:6:\"parent\";i:120;}i:130;a:2:{s:5:\"class\";s:16:\"ilpcparagraphgui\";s:6:\"parent\";i:120;}i:131;a:2:{s:5:\"class\";s:15:\"ilpcquestiongui\";s:6:\"parent\";i:120;}i:132;a:2:{s:5:\"class\";s:17:\"ilpcsourcecodegui\";s:6:\"parent\";i:120;}i:133;a:2:{s:5:\"class\";s:16:\"ilpctabledatagui\";s:6:\"parent\";i:120;}i:134;a:2:{s:5:\"class\";s:12:\"ilpctablegui\";s:6:\"parent\";i:120;}i:135;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:110;}i:136;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:110;}i:137;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:136;}i:138;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:137;}i:139;a:2:{s:5:\"class\";s:13:\"ilobjgroupgui\";s:6:\"parent\";i:1;}i:140;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:139;}i:141;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:139;}i:142;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:141;}i:143;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:142;}i:144;a:2:{s:5:\"class\";s:13:\"ilregistergui\";s:6:\"parent\";i:139;}i:145;a:2:{s:5:\"class\";s:22:\"ilobjilincclassroomgui\";s:6:\"parent\";i:1;}i:146;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:145;}i:147;a:2:{s:5:\"class\";s:19:\"ilobjilinccoursegui\";s:6:\"parent\";i:1;}i:148;a:2:{s:5:\"class\";s:22:\"ilobjilincclassroomgui\";s:6:\"parent\";i:147;}i:149;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:148;}i:150;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:147;}i:151;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:150;}i:152;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:151;}i:153;a:2:{s:5:\"class\";s:22:\"ilobjlearningmodulegui\";s:6:\"parent\";i:1;}i:154;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:153;}i:155;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:154;}i:156;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:155;}i:157;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:156;}i:158;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:154;}i:159;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:158;}i:160;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:158;}i:161;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:160;}i:162;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:158;}i:163;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:162;}i:164;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:154;}i:165;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:164;}i:166;a:2:{s:5:\"class\";s:17:\"illmpageobjectgui\";s:6:\"parent\";i:153;}i:167;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:166;}i:168;a:2:{s:5:\"class\";s:15:\"ilpageobjectgui\";s:6:\"parent\";i:166;}i:169;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:168;}i:170;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:169;}i:171;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:170;}i:172;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:170;}i:173;a:2:{s:5:\"class\";s:25:\"ilmediapooltargetselector\";s:6:\"parent\";i:168;}i:174;a:2:{s:5:\"class\";s:15:\"ilpageeditorgui\";s:6:\"parent\";i:168;}i:175;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:174;}i:176;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:174;}i:177;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:176;}i:178;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:176;}i:179;a:2:{s:5:\"class\";s:15:\"ilpcfileitemgui\";s:6:\"parent\";i:174;}i:180;a:2:{s:5:\"class\";s:15:\"ilpcfilelistgui\";s:6:\"parent\";i:174;}i:181;a:2:{s:5:\"class\";s:11:\"ilpclistgui\";s:6:\"parent\";i:174;}i:182;a:2:{s:5:\"class\";s:15:\"ilpclistitemgui\";s:6:\"parent\";i:174;}i:183;a:2:{s:5:\"class\";s:18:\"ilpcmediaobjectgui\";s:6:\"parent\";i:174;}i:184;a:2:{s:5:\"class\";s:16:\"ilpcparagraphgui\";s:6:\"parent\";i:174;}i:185;a:2:{s:5:\"class\";s:15:\"ilpcquestiongui\";s:6:\"parent\";i:174;}i:186;a:2:{s:5:\"class\";s:17:\"ilpcsourcecodegui\";s:6:\"parent\";i:174;}i:187;a:2:{s:5:\"class\";s:16:\"ilpctabledatagui\";s:6:\"parent\";i:174;}i:188;a:2:{s:5:\"class\";s:12:\"ilpctablegui\";s:6:\"parent\";i:174;}i:189;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:153;}i:190;a:2:{s:5:\"class\";s:18:\"ilobjstylesheetgui\";s:6:\"parent\";i:153;}i:191;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:190;}i:192;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:153;}i:193;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:192;}i:194;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:193;}i:195;a:2:{s:5:\"class\";s:20:\"ilstructureobjectgui\";s:6:\"parent\";i:153;}i:196;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:195;}i:197;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:195;}i:198;a:2:{s:5:\"class\";s:20:\"ilobjlinkresourcegui\";s:6:\"parent\";i:1;}i:199;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:198;}i:200;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:199;}i:201;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:200;}i:202;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:199;}i:203;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:198;}i:204;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:198;}i:205;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:204;}i:206;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:205;}i:207;a:2:{s:5:\"class\";s:17:\"ilobjmediapoolgui\";s:6:\"parent\";i:1;}i:208;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:207;}i:209;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:208;}i:210;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:209;}i:211;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:209;}i:212;a:2:{s:5:\"class\";s:14:\"ilobjfoldergui\";s:6:\"parent\";i:207;}i:213;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:212;}i:214;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:212;}i:215;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:214;}i:216;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:215;}i:217;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:207;}i:218;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:217;}i:219;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:217;}i:220;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:207;}i:221;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:220;}i:222;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:221;}i:223;a:2:{s:5:\"class\";s:20:\"ilobjquestionpoolgui\";s:6:\"parent\";i:1;}i:224;a:2:{s:5:\"class\";s:16:\"ass_clozetestgui\";s:6:\"parent\";i:223;}i:225;a:2:{s:5:\"class\";s:23:\"ass_imagemapquestiongui\";s:6:\"parent\";i:223;}i:226;a:2:{s:5:\"class\";s:17:\"ass_javaappletgui\";s:6:\"parent\";i:223;}i:227;a:2:{s:5:\"class\";s:23:\"ass_matchingquestiongui\";s:6:\"parent\";i:223;}i:228;a:2:{s:5:\"class\";s:21:\"ass_multiplechoicegui\";s:6:\"parent\";i:223;}i:229;a:2:{s:5:\"class\";s:23:\"ass_orderingquestiongui\";s:6:\"parent\";i:223;}i:230;a:2:{s:5:\"class\";s:19:\"ass_textquestiongui\";s:6:\"parent\";i:223;}i:231;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:223;}i:232;a:2:{s:5:\"class\";s:15:\"ilpageobjectgui\";s:6:\"parent\";i:223;}i:233;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:232;}i:234;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:233;}i:235;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:234;}i:236;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:234;}i:237;a:2:{s:5:\"class\";s:25:\"ilmediapooltargetselector\";s:6:\"parent\";i:232;}i:238;a:2:{s:5:\"class\";s:15:\"ilpageeditorgui\";s:6:\"parent\";i:232;}i:239;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:238;}i:240;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:238;}i:241;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:240;}i:242;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:240;}i:243;a:2:{s:5:\"class\";s:15:\"ilpcfileitemgui\";s:6:\"parent\";i:238;}i:244;a:2:{s:5:\"class\";s:15:\"ilpcfilelistgui\";s:6:\"parent\";i:238;}i:245;a:2:{s:5:\"class\";s:11:\"ilpclistgui\";s:6:\"parent\";i:238;}i:246;a:2:{s:5:\"class\";s:15:\"ilpclistitemgui\";s:6:\"parent\";i:238;}i:247;a:2:{s:5:\"class\";s:18:\"ilpcmediaobjectgui\";s:6:\"parent\";i:238;}i:248;a:2:{s:5:\"class\";s:16:\"ilpcparagraphgui\";s:6:\"parent\";i:238;}i:249;a:2:{s:5:\"class\";s:15:\"ilpcquestiongui\";s:6:\"parent\";i:238;}i:250;a:2:{s:5:\"class\";s:17:\"ilpcsourcecodegui\";s:6:\"parent\";i:238;}i:251;a:2:{s:5:\"class\";s:16:\"ilpctabledatagui\";s:6:\"parent\";i:238;}i:252;a:2:{s:5:\"class\";s:12:\"ilpctablegui\";s:6:\"parent\";i:238;}i:253;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:223;}i:254;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:253;}i:255;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:254;}i:256;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:1;}i:257;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:256;}i:258;a:2:{s:5:\"class\";s:18:\"ilobjrootfoldergui\";s:6:\"parent\";i:1;}i:259;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:258;}i:260;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:259;}i:261;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:260;}i:262;a:2:{s:5:\"class\";s:26:\"ilobjsahslearningmodulegui\";s:6:\"parent\";i:1;}i:263;a:2:{s:5:\"class\";s:15:\"ilfilesystemgui\";s:6:\"parent\";i:262;}i:264;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:262;}i:265;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:262;}i:266;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:265;}i:267;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:266;}i:268;a:2:{s:5:\"class\";s:14:\"ilobjsurveygui\";s:6:\"parent\";i:1;}i:269;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:268;}i:270;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:268;}i:271;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:270;}i:272;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:271;}i:273;a:2:{s:5:\"class\";s:21:\"ilsurveyevaluationgui\";s:6:\"parent\";i:268;}i:274;a:2:{s:5:\"class\";s:20:\"ilsurveyexecutiongui\";s:6:\"parent\";i:268;}i:275;a:2:{s:5:\"class\";s:26:\"ilobjsurveyquestionpoolgui\";s:6:\"parent\";i:1;}i:276;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:275;}i:277;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:275;}i:278;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:277;}i:279;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:278;}i:280;a:2:{s:5:\"class\";s:23:\"surveymetricquestiongui\";s:6:\"parent\";i:275;}i:281;a:2:{s:5:\"class\";s:24:\"surveynominalquestiongui\";s:6:\"parent\";i:275;}i:282;a:2:{s:5:\"class\";s:24:\"surveyordinalquestiongui\";s:6:\"parent\";i:275;}i:283;a:2:{s:5:\"class\";s:21:\"surveytextquestiongui\";s:6:\"parent\";i:275;}i:284;a:2:{s:5:\"class\";s:12:\"ilobjtestgui\";s:6:\"parent\";i:1;}i:285;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:284;}i:286;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:285;}i:287;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:286;}i:288;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:285;}i:289;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:284;}i:290;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:289;}i:291;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:290;}i:292;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:291;}i:293;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:289;}i:294;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:293;}i:295;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:293;}i:296;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:295;}i:297;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:293;}i:298;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:297;}i:299;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:289;}i:300;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:299;}i:301;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:284;}i:302;a:2:{s:5:\"class\";s:14:\"ilobjcoursegui\";s:6:\"parent\";i:284;}i:303;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:302;}i:304;a:2:{s:5:\"class\";s:21:\"ilcourseobjectivesgui\";s:6:\"parent\";i:302;}i:305;a:2:{s:5:\"class\";s:19:\"ilcourseregistergui\";s:6:\"parent\";i:302;}i:306;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:302;}i:307;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:306;}i:308;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:307;}i:309;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:306;}i:310;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:302;}i:311;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:310;}i:312;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:311;}i:313;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:312;}i:314;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:310;}i:315;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:314;}i:316;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:314;}i:317;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:316;}i:318;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:314;}i:319;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:318;}i:320;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:310;}i:321;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:320;}i:322;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:302;}i:323;a:2:{s:5:\"class\";s:22:\"ilobjcoursegroupinggui\";s:6:\"parent\";i:302;}i:324;a:2:{s:5:\"class\";s:20:\"ilpaymentpurchasegui\";s:6:\"parent\";i:302;}i:325;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:302;}i:326;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:325;}i:327;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:326;}i:328;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:284;}i:329;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:328;}i:330;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:329;}i:331;a:2:{s:5:\"class\";s:19:\"iltestevaluationgui\";s:6:\"parent\";i:284;}i:332;a:2:{s:5:\"class\";s:15:\"iltestoutputgui\";s:6:\"parent\";i:284;}i:333;a:2:{s:5:\"class\";s:18:\"ilobjuserfoldergui\";s:6:\"parent\";i:1;}i:334;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:333;}i:335;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:334;}i:336;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:335;}i:337;a:2:{s:5:\"class\";s:12:\"ilobjusergui\";s:6:\"parent\";i:1;}i:338;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:337;}i:339;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:338;}i:340;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:339;}i:341;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:340;}i:342;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:338;}i:343;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:342;}i:344;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:342;}i:345;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:344;}i:346;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:342;}i:347;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:346;}i:348;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:338;}i:349;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:348;}}','a:45:{s:0:\"\";a:189:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";i:10;s:0:\"\";i:11;s:0:\"\";i:12;s:0:\"\";i:13;s:0:\"\";i:14;s:0:\"\";i:15;s:0:\"\";i:16;s:0:\"\";i:17;s:0:\"\";i:18;s:0:\"\";i:19;s:0:\"\";i:20;s:0:\"\";i:21;s:0:\"\";i:22;s:0:\"\";i:23;s:0:\"\";i:24;s:0:\"\";i:25;s:0:\"\";i:26;s:0:\"\";i:27;s:0:\"\";i:28;s:0:\"\";i:29;s:0:\"\";i:30;s:0:\"\";i:31;s:0:\"\";i:32;s:0:\"\";i:33;s:0:\"\";i:34;s:0:\"\";i:35;s:0:\"\";i:36;s:0:\"\";i:37;s:0:\"\";i:38;s:0:\"\";i:39;s:0:\"\";i:40;s:0:\"\";i:41;s:0:\"\";i:42;s:0:\"\";i:43;s:0:\"\";i:44;s:0:\"\";i:45;s:0:\"\";i:46;s:0:\"\";i:47;s:0:\"\";i:48;s:0:\"\";i:49;s:0:\"\";i:50;s:0:\"\";i:51;s:0:\"\";i:52;s:0:\"\";i:53;s:0:\"\";i:54;s:0:\"\";i:55;s:0:\"\";i:56;s:0:\"\";i:57;s:0:\"\";i:58;s:0:\"\";i:59;s:0:\"\";i:60;s:0:\"\";i:61;s:0:\"\";i:62;s:0:\"\";i:63;s:0:\"\";i:64;s:0:\"\";i:65;s:0:\"\";i:66;s:0:\"\";i:67;s:0:\"\";i:68;s:0:\"\";i:69;s:0:\"\";i:70;s:0:\"\";i:71;s:0:\"\";i:72;s:0:\"\";i:73;s:0:\"\";i:74;s:0:\"\";i:75;s:0:\"\";i:76;s:0:\"\";i:77;s:0:\"\";i:78;s:0:\"\";i:79;s:0:\"\";i:80;s:0:\"\";i:81;s:0:\"\";i:82;s:0:\"\";i:83;s:0:\"\";i:84;s:0:\"\";i:85;s:0:\"\";i:86;s:0:\"\";i:87;s:0:\"\";i:88;s:0:\"\";i:89;s:0:\"\";i:90;s:0:\"\";i:91;s:0:\"\";i:92;s:0:\"\";i:93;s:0:\"\";i:94;s:0:\"\";i:95;s:0:\"\";i:96;s:0:\"\";i:97;s:0:\"\";i:98;s:0:\"\";i:99;s:0:\"\";i:100;s:0:\"\";i:101;s:0:\"\";i:102;s:0:\"\";i:103;s:0:\"\";i:104;s:0:\"\";i:105;s:0:\"\";i:106;s:0:\"\";i:107;s:0:\"\";i:108;s:0:\"\";i:109;s:0:\"\";i:110;s:0:\"\";i:111;s:0:\"\";i:112;s:0:\"\";i:113;s:0:\"\";i:114;s:0:\"\";i:115;s:0:\"\";i:116;s:0:\"\";i:117;s:0:\"\";i:118;s:0:\"\";i:119;s:0:\"\";i:120;s:0:\"\";i:121;s:0:\"\";i:122;s:0:\"\";i:123;s:0:\"\";i:124;s:0:\"\";i:125;s:0:\"\";i:126;s:0:\"\";i:127;s:0:\"\";i:128;s:0:\"\";i:129;s:0:\"\";i:130;s:0:\"\";i:131;s:0:\"\";i:132;s:0:\"\";i:133;s:0:\"\";i:134;s:0:\"\";i:135;s:0:\"\";i:136;s:0:\"\";i:137;s:0:\"\";i:138;s:0:\"\";i:139;s:0:\"\";i:140;s:0:\"\";i:141;s:0:\"\";i:142;s:0:\"\";i:143;s:0:\"\";i:144;s:0:\"\";i:145;s:0:\"\";i:146;s:0:\"\";i:147;s:0:\"\";i:148;s:0:\"\";i:149;s:0:\"\";i:150;s:0:\"\";i:151;s:0:\"\";i:152;s:0:\"\";i:153;s:0:\"\";i:154;s:0:\"\";i:155;s:0:\"\";i:156;s:0:\"\";i:157;s:0:\"\";i:158;s:0:\"\";i:159;s:0:\"\";i:160;s:0:\"\";i:161;s:0:\"\";i:162;s:0:\"\";i:163;s:0:\"\";i:164;s:0:\"\";i:165;s:0:\"\";i:166;s:0:\"\";i:167;s:0:\"\";i:168;s:0:\"\";i:169;s:0:\"\";i:170;s:0:\"\";i:171;s:0:\"\";i:172;s:0:\"\";i:173;s:0:\"\";i:174;s:0:\"\";i:175;s:0:\"\";i:176;s:0:\"\";i:177;s:0:\"\";i:178;s:0:\"\";i:179;s:0:\"\";i:180;s:0:\"\";i:181;s:0:\"\";i:182;s:0:\"\";i:183;s:0:\"\";i:184;s:0:\"\";i:185;s:0:\"\";i:186;s:0:\"\";i:187;s:0:\"\";i:188;s:0:\"\";}s:12:\"ilobjrolegui\";a:25:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";i:10;s:0:\"\";i:11;s:0:\"\";i:12;s:0:\"\";i:13;s:0:\"\";i:14;s:0:\"\";i:15;s:0:\"\";i:16;s:0:\"\";i:17;s:0:\"\";i:18;s:0:\"\";i:19;s:0:\"\";i:20;s:0:\"\";i:21;s:0:\"\";i:22;s:0:\"\";i:23;s:0:\"\";i:24;s:0:\"\";}s:15:\"ilpermissiongui\";a:24:{i:0;s:12:\"ilobjrolegui\";i:1;s:12:\"ilobjrolegui\";i:2;s:12:\"ilobjrolegui\";i:3;s:12:\"ilobjrolegui\";i:4;s:12:\"ilobjrolegui\";i:5;s:12:\"ilobjrolegui\";i:6;s:12:\"ilobjrolegui\";i:7;s:12:\"ilobjrolegui\";i:8;s:12:\"ilobjrolegui\";i:9;s:12:\"ilobjrolegui\";i:10;s:12:\"ilobjrolegui\";i:11;s:12:\"ilobjrolegui\";i:12;s:12:\"ilobjrolegui\";i:13;s:12:\"ilobjrolegui\";i:14;s:12:\"ilobjrolegui\";i:15;s:12:\"ilobjrolegui\";i:16;s:12:\"ilobjrolegui\";i:17;s:12:\"ilobjrolegui\";i:18;s:12:\"ilobjrolegui\";i:19;s:12:\"ilobjrolegui\";i:20;s:12:\"ilobjrolegui\";i:21;s:12:\"ilobjrolegui\";i:22;s:12:\"ilobjrolegui\";i:23;s:12:\"ilobjrolegui\";}s:16:\"ilobjcategorygui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:12:\"ilobjchatgui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:13:\"ilfeedbackgui\";a:5:{i:0;s:1:\"\"\";i:1;s:1:\"\"\";i:2;s:1:\"\"\";i:3;s:1:\"\"\";i:4;s:1:\"\"\";}s:15:\"ilinfoscreengui\";a:10:{i:0;s:13:\"ilfeedbackgui\";i:1;s:9:\"ilnotegui\";i:2;s:13:\"ilfeedbackgui\";i:3;s:9:\"ilnotegui\";i:4;s:13:\"ilfeedbackgui\";i:5;s:9:\"ilnotegui\";i:6;s:13:\"ilfeedbackgui\";i:7;s:9:\"ilnotegui\";i:8;s:13:\"ilfeedbackgui\";i:9;s:9:\"ilnotegui\";}s:13:\"illpfiltergui\";a:12:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";i:10;s:0:\"\";i:11;s:0:\"\";}s:20:\"illplistofobjectsgui\";a:6:{i:0;s:13:\"illpfiltergui\";i:1;s:13:\"illpfiltergui\";i:2;s:13:\"illpfiltergui\";i:3;s:13:\"illpfiltergui\";i:4;s:13:\"illpfiltergui\";i:5;s:13:\"illpfiltergui\";}s:17:\"ilpdfpresentation\";a:6:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";}s:21:\"illplistofprogressgui\";a:18:{i:0;s:0:\"\";i:1;s:13:\"illpfiltergui\";i:2;s:17:\"ilpdfpresentation\";i:3;s:0:\"\";i:4;s:13:\"illpfiltergui\";i:5;s:17:\"ilpdfpresentation\";i:6;s:0:\"\";i:7;s:13:\"illpfiltergui\";i:8;s:17:\"ilpdfpresentation\";i:9;s:0:\"\";i:10;s:13:\"illpfiltergui\";i:11;s:17:\"ilpdfpresentation\";i:12;s:0:\"\";i:13;s:13:\"illpfiltergui\";i:14;s:17:\"ilpdfpresentation\";i:15;s:0:\"\";i:16;s:13:\"illpfiltergui\";i:17;s:17:\"ilpdfpresentation\";}s:21:\"illplistofsettingsgui\";a:6:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";}s:21:\"illearningprogressgui\";a:18:{i:0;s:20:\"illplistofobjectsgui\";i:1;s:21:\"illplistofprogressgui\";i:2;s:21:\"illplistofsettingsgui\";i:3;s:20:\"illplistofobjectsgui\";i:4;s:21:\"illplistofprogressgui\";i:5;s:21:\"illplistofsettingsgui\";i:6;s:20:\"illplistofobjectsgui\";i:7;s:21:\"illplistofprogressgui\";i:8;s:21:\"illplistofsettingsgui\";i:9;s:20:\"illplistofobjectsgui\";i:10;s:21:\"illplistofprogressgui\";i:11;s:21:\"illplistofsettingsgui\";i:12;s:20:\"illplistofobjectsgui\";i:13;s:21:\"illplistofprogressgui\";i:14;s:21:\"illplistofsettingsgui\";i:15;s:20:\"illplistofobjectsgui\";i:16;s:21:\"illplistofprogressgui\";i:17;s:21:\"illplistofsettingsgui\";}s:14:\"ilobjcoursegui\";a:18:{i:0;s:27:\"ilconditionhandlerinterface\";i:1;s:21:\"ilcourseobjectivesgui\";i:2;s:19:\"ilcourseregistergui\";i:3;s:15:\"ilinfoscreengui\";i:4;s:21:\"illearningprogressgui\";i:5;s:13:\"ilmdeditorgui\";i:6;s:22:\"ilobjcoursegroupinggui\";i:7;s:20:\"ilpaymentpurchasegui\";i:8;s:15:\"ilpermissiongui\";i:9;s:27:\"ilconditionhandlerinterface\";i:10;s:21:\"ilcourseobjectivesgui\";i:11;s:19:\"ilcourseregistergui\";i:12;s:15:\"ilinfoscreengui\";i:13;s:21:\"illearningprogressgui\";i:14;s:13:\"ilmdeditorgui\";i:15;s:22:\"ilobjcoursegroupinggui\";i:16;s:20:\"ilpaymentpurchasegui\";i:17;s:15:\"ilpermissiongui\";}s:19:\"ilobjmediaobjectgui\";a:20:{i:0;s:17:\"ilinternallinkgui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:17:\"ilinternallinkgui\";i:3;s:13:\"ilmdeditorgui\";i:4;s:17:\"ilinternallinkgui\";i:5;s:13:\"ilmdeditorgui\";i:6;s:17:\"ilinternallinkgui\";i:7;s:13:\"ilmdeditorgui\";i:8;s:17:\"ilinternallinkgui\";i:9;s:13:\"ilmdeditorgui\";i:10;s:17:\"ilinternallinkgui\";i:11;s:13:\"ilmdeditorgui\";i:12;s:17:\"ilinternallinkgui\";i:13;s:13:\"ilmdeditorgui\";i:14;s:17:\"ilinternallinkgui\";i:15;s:13:\"ilmdeditorgui\";i:16;s:17:\"ilinternallinkgui\";i:17;s:13:\"ilmdeditorgui\";i:18;s:17:\"ilinternallinkgui\";i:19;s:13:\"ilmdeditorgui\";}s:18:\"ileditclipboardgui\";a:5:{i:0;s:19:\"ilobjmediaobjectgui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:19:\"ilobjmediaobjectgui\";i:3;s:19:\"ilobjmediaobjectgui\";i:4;s:19:\"ilobjmediaobjectgui\";}s:15:\"ilpageeditorgui\";a:48:{i:0;s:17:\"ilinternallinkgui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:15:\"ilpcfileitemgui\";i:3;s:15:\"ilpcfilelistgui\";i:4;s:11:\"ilpclistgui\";i:5;s:15:\"ilpclistitemgui\";i:6;s:18:\"ilpcmediaobjectgui\";i:7;s:16:\"ilpcparagraphgui\";i:8;s:15:\"ilpcquestiongui\";i:9;s:17:\"ilpcsourcecodegui\";i:10;s:16:\"ilpctabledatagui\";i:11;s:12:\"ilpctablegui\";i:12;s:17:\"ilinternallinkgui\";i:13;s:19:\"ilobjmediaobjectgui\";i:14;s:15:\"ilpcfileitemgui\";i:15;s:15:\"ilpcfilelistgui\";i:16;s:11:\"ilpclistgui\";i:17;s:15:\"ilpclistitemgui\";i:18;s:18:\"ilpcmediaobjectgui\";i:19;s:16:\"ilpcparagraphgui\";i:20;s:15:\"ilpcquestiongui\";i:21;s:17:\"ilpcsourcecodegui\";i:22;s:16:\"ilpctabledatagui\";i:23;s:12:\"ilpctablegui\";i:24;s:17:\"ilinternallinkgui\";i:25;s:19:\"ilobjmediaobjectgui\";i:26;s:15:\"ilpcfileitemgui\";i:27;s:15:\"ilpcfilelistgui\";i:28;s:11:\"ilpclistgui\";i:29;s:15:\"ilpclistitemgui\";i:30;s:18:\"ilpcmediaobjectgui\";i:31;s:16:\"ilpcparagraphgui\";i:32;s:15:\"ilpcquestiongui\";i:33;s:17:\"ilpcsourcecodegui\";i:34;s:16:\"ilpctabledatagui\";i:35;s:12:\"ilpctablegui\";i:36;s:17:\"ilinternallinkgui\";i:37;s:19:\"ilobjmediaobjectgui\";i:38;s:15:\"ilpcfileitemgui\";i:39;s:15:\"ilpcfilelistgui\";i:40;s:11:\"ilpclistgui\";i:41;s:15:\"ilpclistitemgui\";i:42;s:18:\"ilpcmediaobjectgui\";i:43;s:16:\"ilpcparagraphgui\";i:44;s:15:\"ilpcquestiongui\";i:45;s:17:\"ilpcsourcecodegui\";i:46;s:16:\"ilpctabledatagui\";i:47;s:12:\"ilpctablegui\";}s:15:\"ilpageobjectgui\";a:12:{i:0;s:18:\"ileditclipboardgui\";i:1;s:25:\"ilmediapooltargetselector\";i:2;s:15:\"ilpageeditorgui\";i:3;s:18:\"ileditclipboardgui\";i:4;s:25:\"ilmediapooltargetselector\";i:5;s:15:\"ilpageeditorgui\";i:6;s:18:\"ileditclipboardgui\";i:7;s:25:\"ilmediapooltargetselector\";i:8;s:15:\"ilpageeditorgui\";i:9;s:18:\"ileditclipboardgui\";i:10;s:25:\"ilmediapooltargetselector\";i:11;s:15:\"ilpageeditorgui\";}s:17:\"illmpageobjectgui\";a:4:{i:0;s:13:\"ilmdeditorgui\";i:1;s:15:\"ilpageobjectgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:15:\"ilpageobjectgui\";}s:18:\"ilobjstylesheetgui\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:20:\"ilstructureobjectgui\";a:4:{i:0;s:27:\"ilconditionhandlerinterface\";i:1;s:13:\"ilmdeditorgui\";i:2;s:27:\"ilconditionhandlerinterface\";i:3;s:13:\"ilmdeditorgui\";}s:14:\"ilobjdlbookgui\";a:6:{i:0;s:21:\"illearningprogressgui\";i:1;s:17:\"illmpageobjectgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:18:\"ilobjstylesheetgui\";i:4;s:15:\"ilpermissiongui\";i:5;s:20:\"ilstructureobjectgui\";}s:16:\"ilobjexercisegui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:19:\"ilobjfilebasedlmgui\";a:3:{i:0;s:15:\"ilfilesystemgui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:15:\"ilpermissiongui\";}s:12:\"ilobjfilegui\";a:3:{i:0;s:15:\"ilinfoscreengui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:15:\"ilpermissiongui\";}s:14:\"ilobjfoldergui\";a:4:{i:0;s:27:\"ilconditionhandlerinterface\";i:1;s:15:\"ilpermissiongui\";i:2;s:27:\"ilconditionhandlerinterface\";i:3;s:15:\"ilpermissiongui\";}s:13:\"ilobjforumgui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:25:\"iltermdefinitioneditorgui\";a:2:{i:0;s:13:\"ilmdeditorgui\";i:1;s:15:\"ilpageobjectgui\";}s:17:\"ilglossarytermgui\";a:1:{i:0;s:25:\"iltermdefinitioneditorgui\";}s:16:\"ilobjglossarygui\";a:3:{i:0;s:17:\"ilglossarytermgui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:15:\"ilpermissiongui\";}s:13:\"ilobjgroupgui\";a:3:{i:0;s:27:\"ilconditionhandlerinterface\";i:1;s:15:\"ilpermissiongui\";i:2;s:13:\"ilregistergui\";}s:22:\"ilobjilincclassroomgui\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:19:\"ilobjilinccoursegui\";a:2:{i:0;s:22:\"ilobjilincclassroomgui\";i:1;s:15:\"ilpermissiongui\";}s:22:\"ilobjlearningmodulegui\";a:6:{i:0;s:21:\"illearningprogressgui\";i:1;s:17:\"illmpageobjectgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:18:\"ilobjstylesheetgui\";i:4;s:15:\"ilpermissiongui\";i:5;s:20:\"ilstructureobjectgui\";}s:20:\"ilobjlinkresourcegui\";a:3:{i:0;s:15:\"ilinfoscreengui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:15:\"ilpermissiongui\";}s:17:\"ilobjmediapoolgui\";a:4:{i:0;s:18:\"ileditclipboardgui\";i:1;s:14:\"ilobjfoldergui\";i:2;s:19:\"ilobjmediaobjectgui\";i:3;s:15:\"ilpermissiongui\";}s:20:\"ilobjquestionpoolgui\";a:10:{i:0;s:16:\"ass_clozetestgui\";i:1;s:23:\"ass_imagemapquestiongui\";i:2;s:17:\"ass_javaappletgui\";i:3;s:23:\"ass_matchingquestiongui\";i:4;s:21:\"ass_multiplechoicegui\";i:5;s:23:\"ass_orderingquestiongui\";i:6;s:19:\"ass_textquestiongui\";i:7;s:13:\"ilmdeditorgui\";i:8;s:15:\"ilpageobjectgui\";i:9;s:15:\"ilpermissiongui\";}s:18:\"ilobjrootfoldergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:26:\"ilobjsahslearningmodulegui\";a:3:{i:0;s:15:\"ilfilesystemgui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:15:\"ilpermissiongui\";}s:14:\"ilobjsurveygui\";a:4:{i:0;s:13:\"ilmdeditorgui\";i:1;s:15:\"ilpermissiongui\";i:2;s:21:\"ilsurveyevaluationgui\";i:3;s:20:\"ilsurveyexecutiongui\";}s:26:\"ilobjsurveyquestionpoolgui\";a:6:{i:0;s:13:\"ilmdeditorgui\";i:1;s:15:\"ilpermissiongui\";i:2;s:23:\"surveymetricquestiongui\";i:3;s:24:\"surveynominalquestiongui\";i:4;s:24:\"surveyordinalquestiongui\";i:5;s:21:\"surveytextquestiongui\";}s:12:\"ilobjtestgui\";a:7:{i:0;s:15:\"ilinfoscreengui\";i:1;s:21:\"illearningprogressgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:14:\"ilobjcoursegui\";i:4;s:15:\"ilpermissiongui\";i:5;s:19:\"iltestevaluationgui\";i:6;s:15:\"iltestoutputgui\";}s:18:\"ilobjuserfoldergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:12:\"ilobjusergui\";a:1:{i:0;s:21:\"illearningprogressgui\";}s:15:\"ilrepositorygui\";a:26:{i:0;s:21:\"ilcourseobjectivesgui\";i:1;s:16:\"ilobjcategorygui\";i:2;s:12:\"ilobjchatgui\";i:3;s:14:\"ilobjcoursegui\";i:4;s:14:\"ilobjdlbookgui\";i:5;s:16:\"ilobjexercisegui\";i:6;s:19:\"ilobjfilebasedlmgui\";i:7;s:12:\"ilobjfilegui\";i:8;s:14:\"ilobjfoldergui\";i:9;s:13:\"ilobjforumgui\";i:10;s:16:\"ilobjglossarygui\";i:11;s:13:\"ilobjgroupgui\";i:12;s:22:\"ilobjilincclassroomgui\";i:13;s:19:\"ilobjilinccoursegui\";i:14;s:22:\"ilobjlearningmodulegui\";i:15;s:20:\"ilobjlinkresourcegui\";i:16;s:17:\"ilobjmediapoolgui\";i:17;s:20:\"ilobjquestionpoolgui\";i:18;s:12:\"ilobjrolegui\";i:19;s:18:\"ilobjrootfoldergui\";i:20;s:26:\"ilobjsahslearningmodulegui\";i:21;s:14:\"ilobjsurveygui\";i:22;s:26:\"ilobjsurveyquestionpoolgui\";i:23;s:12:\"ilobjtestgui\";i:24;s:18:\"ilobjuserfoldergui\";i:25;s:12:\"ilobjusergui\";}}','a:81:{s:0:\"\";a:248:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:12:\"ilobjrolegui\";i:3;s:0:\"\";i:4;s:12:\"ilobjrolegui\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";i:10;s:0:\"\";i:11;s:13:\"illpfiltergui\";i:12;s:0:\"\";i:13;s:0:\"\";i:14;s:13:\"illpfiltergui\";i:15;s:0:\"\";i:16;s:17:\"ilpdfpresentation\";i:17;s:21:\"illplistofprogressgui\";i:18;s:0:\"\";i:19;s:21:\"illplistofsettingsgui\";i:20;s:0:\"\";i:21;s:0:\"\";i:22;s:0:\"\";i:23;s:0:\"\";i:24;s:12:\"ilobjrolegui\";i:25;s:0:\"\";i:26;s:13:\"illpfiltergui\";i:27;s:0:\"\";i:28;s:0:\"\";i:29;s:13:\"illpfiltergui\";i:30;s:0:\"\";i:31;s:17:\"ilpdfpresentation\";i:32;s:21:\"illplistofprogressgui\";i:33;s:0:\"\";i:34;s:21:\"illplistofsettingsgui\";i:35;s:0:\"\";i:36;s:0:\"\";i:37;s:0:\"\";i:38;s:0:\"\";i:39;s:0:\"\";i:40;s:0:\"\";i:41;s:0:\"\";i:42;s:0:\"\";i:43;s:0:\"\";i:44;s:0:\"\";i:45;s:0:\"\";i:46;s:0:\"\";i:47;s:0:\"\";i:48;s:0:\"\";i:49;s:0:\"\";i:50;s:0:\"\";i:51;s:0:\"\";i:52;s:0:\"\";i:53;s:0:\"\";i:54;s:18:\"ilobjstylesheetgui\";i:55;s:0:\"\";i:56;s:12:\"ilobjrolegui\";i:57;s:0:\"\";i:58;s:0:\"\";i:59;s:0:\"\";i:60;s:12:\"ilobjrolegui\";i:61;s:0:\"\";i:62;s:0:\"\";i:63;s:0:\"\";i:64;s:12:\"ilobjrolegui\";i:65;s:0:\"\";i:66;s:0:\"\";i:67;s:0:\"\";i:68;s:0:\"\";i:69;s:12:\"ilobjrolegui\";i:70;s:0:\"\";i:71;s:0:\"\";i:72;s:12:\"ilobjrolegui\";i:73;s:0:\"\";i:74;s:12:\"ilobjrolegui\";i:75;s:0:\"\";i:76;s:0:\"\";i:77;s:0:\"\";i:78;s:0:\"\";i:79;s:0:\"\";i:80;s:0:\"\";i:81;s:0:\"\";i:82;s:0:\"\";i:83;s:0:\"\";i:84;s:0:\"\";i:85;s:0:\"\";i:86;s:0:\"\";i:87;s:0:\"\";i:88;s:0:\"\";i:89;s:0:\"\";i:90;s:0:\"\";i:91;s:0:\"\";i:92;s:0:\"\";i:93;s:0:\"\";i:94;s:12:\"ilobjrolegui\";i:95;s:0:\"\";i:96;s:0:\"\";i:97;s:12:\"ilobjrolegui\";i:98;s:0:\"\";i:99;s:0:\"\";i:100;s:22:\"ilobjilincclassroomgui\";i:101;s:0:\"\";i:102;s:22:\"ilobjilincclassroomgui\";i:103;s:0:\"\";i:104;s:12:\"ilobjrolegui\";i:105;s:0:\"\";i:106;s:13:\"illpfiltergui\";i:107;s:0:\"\";i:108;s:0:\"\";i:109;s:13:\"illpfiltergui\";i:110;s:0:\"\";i:111;s:17:\"ilpdfpresentation\";i:112;s:21:\"illplistofprogressgui\";i:113;s:0:\"\";i:114;s:21:\"illplistofsettingsgui\";i:115;s:0:\"\";i:116;s:0:\"\";i:117;s:0:\"\";i:118;s:0:\"\";i:119;s:0:\"\";i:120;s:0:\"\";i:121;s:0:\"\";i:122;s:0:\"\";i:123;s:0:\"\";i:124;s:0:\"\";i:125;s:0:\"\";i:126;s:0:\"\";i:127;s:0:\"\";i:128;s:0:\"\";i:129;s:0:\"\";i:130;s:0:\"\";i:131;s:0:\"\";i:132;s:0:\"\";i:133;s:0:\"\";i:134;s:18:\"ilobjstylesheetgui\";i:135;s:0:\"\";i:136;s:12:\"ilobjrolegui\";i:137;s:0:\"\";i:138;s:0:\"\";i:139;s:0:\"\";i:140;s:0:\"\";i:141;s:0:\"\";i:142;s:0:\"\";i:143;s:12:\"ilobjrolegui\";i:144;s:0:\"\";i:145;s:0:\"\";i:146;s:0:\"\";i:147;s:0:\"\";i:148;s:12:\"ilobjrolegui\";i:149;s:0:\"\";i:150;s:0:\"\";i:151;s:0:\"\";i:152;s:12:\"ilobjrolegui\";i:153;s:0:\"\";i:154;s:0:\"\";i:155;s:0:\"\";i:156;s:0:\"\";i:157;s:0:\"\";i:158;s:0:\"\";i:159;s:0:\"\";i:160;s:0:\"\";i:161;s:0:\"\";i:162;s:0:\"\";i:163;s:0:\"\";i:164;s:0:\"\";i:165;s:0:\"\";i:166;s:0:\"\";i:167;s:0:\"\";i:168;s:0:\"\";i:169;s:0:\"\";i:170;s:0:\"\";i:171;s:0:\"\";i:172;s:0:\"\";i:173;s:0:\"\";i:174;s:0:\"\";i:175;s:0:\"\";i:176;s:0:\"\";i:177;s:0:\"\";i:178;s:12:\"ilobjrolegui\";i:179;s:0:\"\";i:180;s:12:\"ilobjrolegui\";i:181;s:0:\"\";i:182;s:12:\"ilobjrolegui\";i:183;s:0:\"\";i:184;s:0:\"\";i:185;s:0:\"\";i:186;s:12:\"ilobjrolegui\";i:187;s:0:\"\";i:188;s:0:\"\";i:189;s:12:\"ilobjrolegui\";i:190;s:0:\"\";i:191;s:0:\"\";i:192;s:0:\"\";i:193;s:0:\"\";i:194;s:12:\"ilobjrolegui\";i:195;s:0:\"\";i:196;s:0:\"\";i:197;s:0:\"\";i:198;s:0:\"\";i:199;s:0:\"\";i:200;s:0:\"\";i:201;s:0:\"\";i:202;s:13:\"illpfiltergui\";i:203;s:0:\"\";i:204;s:0:\"\";i:205;s:13:\"illpfiltergui\";i:206;s:0:\"\";i:207;s:17:\"ilpdfpresentation\";i:208;s:21:\"illplistofprogressgui\";i:209;s:0:\"\";i:210;s:21:\"illplistofsettingsgui\";i:211;s:0:\"\";i:212;s:0:\"\";i:213;s:0:\"\";i:214;s:0:\"\";i:215;s:0:\"\";i:216;s:0:\"\";i:217;s:0:\"\";i:218;s:13:\"illpfiltergui\";i:219;s:0:\"\";i:220;s:0:\"\";i:221;s:13:\"illpfiltergui\";i:222;s:0:\"\";i:223;s:17:\"ilpdfpresentation\";i:224;s:21:\"illplistofprogressgui\";i:225;s:0:\"\";i:226;s:21:\"illplistofsettingsgui\";i:227;s:0:\"\";i:228;s:0:\"\";i:229;s:0:\"\";i:230;s:0:\"\";i:231;s:12:\"ilobjrolegui\";i:232;s:0:\"\";i:233;s:12:\"ilobjrolegui\";i:234;s:0:\"\";i:235;s:0:\"\";i:236;s:0:\"\";i:237;s:12:\"ilobjrolegui\";i:238;s:0:\"\";i:239;s:13:\"illpfiltergui\";i:240;s:0:\"\";i:241;s:0:\"\";i:242;s:13:\"illpfiltergui\";i:243;s:0:\"\";i:244;s:17:\"ilpdfpresentation\";i:245;s:21:\"illplistofprogressgui\";i:246;s:0:\"\";i:247;s:21:\"illplistofsettingsgui\";}s:12:\"ilobjrolegui\";a:25:{i:0;s:15:\"ilpermissiongui\";i:1;s:15:\"ilpermissiongui\";i:2;s:15:\"ilpermissiongui\";i:3;s:15:\"ilpermissiongui\";i:4;s:15:\"ilpermissiongui\";i:5;s:15:\"ilpermissiongui\";i:6;s:15:\"ilpermissiongui\";i:7;s:15:\"ilpermissiongui\";i:8;s:15:\"ilpermissiongui\";i:9;s:15:\"ilpermissiongui\";i:10;s:15:\"ilpermissiongui\";i:11;s:15:\"ilpermissiongui\";i:12;s:15:\"ilpermissiongui\";i:13;s:15:\"ilpermissiongui\";i:14;s:15:\"ilpermissiongui\";i:15;s:15:\"ilpermissiongui\";i:16;s:15:\"ilpermissiongui\";i:17;s:15:\"ilpermissiongui\";i:18;s:15:\"ilpermissiongui\";i:19;s:15:\"ilpermissiongui\";i:20;s:15:\"ilpermissiongui\";i:21;s:15:\"ilpermissiongui\";i:22;s:15:\"ilpermissiongui\";i:23;s:15:\"ilpermissiongui\";i:24;s:15:\"ilrepositorygui\";}s:15:\"ilpermissiongui\";a:24:{i:0;s:16:\"ilobjcategorygui\";i:1;s:12:\"ilobjchatgui\";i:2;s:14:\"ilobjcoursegui\";i:3;s:14:\"ilobjdlbookgui\";i:4;s:16:\"ilobjexercisegui\";i:5;s:19:\"ilobjfilebasedlmgui\";i:6;s:12:\"ilobjfilegui\";i:7;s:14:\"ilobjfoldergui\";i:8;s:13:\"ilobjforumgui\";i:9;s:16:\"ilobjglossarygui\";i:10;s:13:\"ilobjgroupgui\";i:11;s:19:\"ilobjilinccoursegui\";i:12;s:22:\"ilobjlearningmodulegui\";i:13;s:20:\"ilobjlinkresourcegui\";i:14;s:14:\"ilobjfoldergui\";i:15;s:17:\"ilobjmediapoolgui\";i:16;s:20:\"ilobjquestionpoolgui\";i:17;s:18:\"ilobjrootfoldergui\";i:18;s:26:\"ilobjsahslearningmodulegui\";i:19;s:14:\"ilobjsurveygui\";i:20;s:26:\"ilobjsurveyquestionpoolgui\";i:21;s:14:\"ilobjcoursegui\";i:22;s:12:\"ilobjtestgui\";i:23;s:18:\"ilobjuserfoldergui\";}s:1:\"\"\";a:5:{i:0;s:13:\"ilfeedbackgui\";i:1;s:13:\"ilfeedbackgui\";i:2;s:13:\"ilfeedbackgui\";i:3;s:13:\"ilfeedbackgui\";i:4;s:13:\"ilfeedbackgui\";}s:13:\"ilfeedbackgui\";a:5:{i:0;s:15:\"ilinfoscreengui\";i:1;s:15:\"ilinfoscreengui\";i:2;s:15:\"ilinfoscreengui\";i:3;s:15:\"ilinfoscreengui\";i:4;s:15:\"ilinfoscreengui\";}s:9:\"ilnotegui\";a:5:{i:0;s:15:\"ilinfoscreengui\";i:1;s:15:\"ilinfoscreengui\";i:2;s:15:\"ilinfoscreengui\";i:3;s:15:\"ilinfoscreengui\";i:4;s:15:\"ilinfoscreengui\";}s:13:\"illpfiltergui\";a:12:{i:0;s:20:\"illplistofobjectsgui\";i:1;s:21:\"illplistofprogressgui\";i:2;s:20:\"illplistofobjectsgui\";i:3;s:21:\"illplistofprogressgui\";i:4;s:20:\"illplistofobjectsgui\";i:5;s:21:\"illplistofprogressgui\";i:6;s:20:\"illplistofobjectsgui\";i:7;s:21:\"illplistofprogressgui\";i:8;s:20:\"illplistofobjectsgui\";i:9;s:21:\"illplistofprogressgui\";i:10;s:20:\"illplistofobjectsgui\";i:11;s:21:\"illplistofprogressgui\";}s:17:\"ilpdfpresentation\";a:6:{i:0;s:21:\"illplistofprogressgui\";i:1;s:21:\"illplistofprogressgui\";i:2;s:21:\"illplistofprogressgui\";i:3;s:21:\"illplistofprogressgui\";i:4;s:21:\"illplistofprogressgui\";i:5;s:21:\"illplistofprogressgui\";}s:20:\"illplistofobjectsgui\";a:6:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";i:2;s:21:\"illearningprogressgui\";i:3;s:21:\"illearningprogressgui\";i:4;s:21:\"illearningprogressgui\";i:5;s:21:\"illearningprogressgui\";}s:21:\"illplistofprogressgui\";a:6:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";i:2;s:21:\"illearningprogressgui\";i:3;s:21:\"illearningprogressgui\";i:4;s:21:\"illearningprogressgui\";i:5;s:21:\"illearningprogressgui\";}s:21:\"illplistofsettingsgui\";a:6:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";i:2;s:21:\"illearningprogressgui\";i:3;s:21:\"illearningprogressgui\";i:4;s:21:\"illearningprogressgui\";i:5;s:21:\"illearningprogressgui\";}s:27:\"ilconditionhandlerinterface\";a:7:{i:0;s:14:\"ilobjcoursegui\";i:1;s:20:\"ilstructureobjectgui\";i:2;s:14:\"ilobjfoldergui\";i:3;s:13:\"ilobjgroupgui\";i:4;s:20:\"ilstructureobjectgui\";i:5;s:14:\"ilobjfoldergui\";i:6;s:14:\"ilobjcoursegui\";}s:21:\"ilcourseobjectivesgui\";a:3:{i:0;s:14:\"ilobjcoursegui\";i:1;s:14:\"ilobjcoursegui\";i:2;s:15:\"ilrepositorygui\";}s:19:\"ilcourseregistergui\";a:2:{i:0;s:14:\"ilobjcoursegui\";i:1;s:14:\"ilobjcoursegui\";}s:15:\"ilinfoscreengui\";a:5:{i:0;s:14:\"ilobjcoursegui\";i:1;s:12:\"ilobjfilegui\";i:2;s:20:\"ilobjlinkresourcegui\";i:3;s:14:\"ilobjcoursegui\";i:4;s:12:\"ilobjtestgui\";}s:21:\"illearningprogressgui\";a:6:{i:0;s:14:\"ilobjcoursegui\";i:1;s:14:\"ilobjdlbookgui\";i:2;s:22:\"ilobjlearningmodulegui\";i:3;s:14:\"ilobjcoursegui\";i:4;s:12:\"ilobjtestgui\";i:5;s:12:\"ilobjusergui\";}s:13:\"ilmdeditorgui\";a:28:{i:0;s:14:\"ilobjcoursegui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:19:\"ilobjmediaobjectgui\";i:3;s:17:\"illmpageobjectgui\";i:4;s:20:\"ilstructureobjectgui\";i:5;s:14:\"ilobjdlbookgui\";i:6;s:19:\"ilobjfilebasedlmgui\";i:7;s:12:\"ilobjfilegui\";i:8;s:19:\"ilobjmediaobjectgui\";i:9;s:19:\"ilobjmediaobjectgui\";i:10;s:25:\"iltermdefinitioneditorgui\";i:11;s:16:\"ilobjglossarygui\";i:12;s:19:\"ilobjmediaobjectgui\";i:13;s:19:\"ilobjmediaobjectgui\";i:14;s:17:\"illmpageobjectgui\";i:15;s:20:\"ilstructureobjectgui\";i:16;s:22:\"ilobjlearningmodulegui\";i:17;s:20:\"ilobjlinkresourcegui\";i:18;s:19:\"ilobjmediaobjectgui\";i:19;s:19:\"ilobjmediaobjectgui\";i:20;s:19:\"ilobjmediaobjectgui\";i:21;s:19:\"ilobjmediaobjectgui\";i:22;s:20:\"ilobjquestionpoolgui\";i:23;s:26:\"ilobjsahslearningmodulegui\";i:24;s:14:\"ilobjsurveygui\";i:25;s:26:\"ilobjsurveyquestionpoolgui\";i:26;s:14:\"ilobjcoursegui\";i:27;s:12:\"ilobjtestgui\";}s:22:\"ilobjcoursegroupinggui\";a:2:{i:0;s:14:\"ilobjcoursegui\";i:1;s:14:\"ilobjcoursegui\";}s:20:\"ilpaymentpurchasegui\";a:2:{i:0;s:14:\"ilobjcoursegui\";i:1;s:14:\"ilobjcoursegui\";}s:17:\"ilinternallinkgui\";a:14:{i:0;s:19:\"ilobjmediaobjectgui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:19:\"ilobjmediaobjectgui\";i:4;s:19:\"ilobjmediaobjectgui\";i:5;s:15:\"ilpageeditorgui\";i:6;s:19:\"ilobjmediaobjectgui\";i:7;s:19:\"ilobjmediaobjectgui\";i:8;s:15:\"ilpageeditorgui\";i:9;s:19:\"ilobjmediaobjectgui\";i:10;s:19:\"ilobjmediaobjectgui\";i:11;s:19:\"ilobjmediaobjectgui\";i:12;s:19:\"ilobjmediaobjectgui\";i:13;s:15:\"ilpageeditorgui\";}s:19:\"ilobjmediaobjectgui\";a:10:{i:0;s:18:\"ileditclipboardgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:18:\"ileditclipboardgui\";i:3;s:15:\"ilpageeditorgui\";i:4;s:18:\"ileditclipboardgui\";i:5;s:15:\"ilpageeditorgui\";i:6;s:18:\"ileditclipboardgui\";i:7;s:17:\"ilobjmediapoolgui\";i:8;s:18:\"ileditclipboardgui\";i:9;s:15:\"ilpageeditorgui\";}s:15:\"ilpcfileitemgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:15:\"ilpcfilelistgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:11:\"ilpclistgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:15:\"ilpclistitemgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:18:\"ilpcmediaobjectgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:16:\"ilpcparagraphgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:15:\"ilpcquestiongui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:17:\"ilpcsourcecodegui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:16:\"ilpctabledatagui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:12:\"ilpctablegui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:18:\"ileditclipboardgui\";a:5:{i:0;s:15:\"ilpageobjectgui\";i:1;s:15:\"ilpageobjectgui\";i:2;s:15:\"ilpageobjectgui\";i:3;s:17:\"ilobjmediapoolgui\";i:4;s:15:\"ilpageobjectgui\";}s:25:\"ilmediapooltargetselector\";a:4:{i:0;s:15:\"ilpageobjectgui\";i:1;s:15:\"ilpageobjectgui\";i:2;s:15:\"ilpageobjectgui\";i:3;s:15:\"ilpageobjectgui\";}s:15:\"ilpageeditorgui\";a:4:{i:0;s:15:\"ilpageobjectgui\";i:1;s:15:\"ilpageobjectgui\";i:2;s:15:\"ilpageobjectgui\";i:3;s:15:\"ilpageobjectgui\";}s:15:\"ilpageobjectgui\";a:4:{i:0;s:17:\"illmpageobjectgui\";i:1;s:25:\"iltermdefinitioneditorgui\";i:2;s:17:\"illmpageobjectgui\";i:3;s:20:\"ilobjquestionpoolgui\";}s:17:\"illmpageobjectgui\";a:2:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";}s:18:\"ilobjstylesheetgui\";a:2:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";}s:20:\"ilstructureobjectgui\";a:2:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";}s:15:\"ilfilesystemgui\";a:2:{i:0;s:19:\"ilobjfilebasedlmgui\";i:1;s:26:\"ilobjsahslearningmodulegui\";}s:25:\"iltermdefinitioneditorgui\";a:1:{i:0;s:17:\"ilglossarytermgui\";}s:17:\"ilglossarytermgui\";a:1:{i:0;s:16:\"ilobjglossarygui\";}s:13:\"ilregistergui\";a:1:{i:0;s:13:\"ilobjgroupgui\";}s:22:\"ilobjilincclassroomgui\";a:2:{i:0;s:19:\"ilobjilinccoursegui\";i:1;s:15:\"ilrepositorygui\";}s:14:\"ilobjfoldergui\";a:2:{i:0;s:17:\"ilobjmediapoolgui\";i:1;s:15:\"ilrepositorygui\";}s:16:\"ass_clozetestgui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:23:\"ass_imagemapquestiongui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:17:\"ass_javaappletgui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:23:\"ass_matchingquestiongui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:21:\"ass_multiplechoicegui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:23:\"ass_orderingquestiongui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:19:\"ass_textquestiongui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:21:\"ilsurveyevaluationgui\";a:1:{i:0;s:14:\"ilobjsurveygui\";}s:20:\"ilsurveyexecutiongui\";a:1:{i:0;s:14:\"ilobjsurveygui\";}s:23:\"surveymetricquestiongui\";a:1:{i:0;s:26:\"ilobjsurveyquestionpoolgui\";}s:24:\"surveynominalquestiongui\";a:1:{i:0;s:26:\"ilobjsurveyquestionpoolgui\";}s:24:\"surveyordinalquestiongui\";a:1:{i:0;s:26:\"ilobjsurveyquestionpoolgui\";}s:21:\"surveytextquestiongui\";a:1:{i:0;s:26:\"ilobjsurveyquestionpoolgui\";}s:14:\"ilobjcoursegui\";a:2:{i:0;s:12:\"ilobjtestgui\";i:1;s:15:\"ilrepositorygui\";}s:19:\"iltestevaluationgui\";a:1:{i:0;s:12:\"ilobjtestgui\";}s:15:\"iltestoutputgui\";a:1:{i:0;s:12:\"ilobjtestgui\";}s:16:\"ilobjcategorygui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:12:\"ilobjchatgui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:14:\"ilobjdlbookgui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:16:\"ilobjexercisegui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:19:\"ilobjfilebasedlmgui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:12:\"ilobjfilegui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:13:\"ilobjforumgui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:16:\"ilobjglossarygui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:13:\"ilobjgroupgui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:19:\"ilobjilinccoursegui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:22:\"ilobjlearningmodulegui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:20:\"ilobjlinkresourcegui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:17:\"ilobjmediapoolgui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:20:\"ilobjquestionpoolgui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:18:\"ilobjrootfoldergui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:26:\"ilobjsahslearningmodulegui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:14:\"ilobjsurveygui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:26:\"ilobjsurveyquestionpoolgui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:12:\"ilobjtestgui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:18:\"ilobjuserfoldergui\";a:1:{i:0;s:15:\"ilrepositorygui\";}s:12:\"ilobjusergui\";a:1:{i:0;s:15:\"ilrepositorygui\";}}'),('ilpersonaldesktopgui','a:31:{i:1;a:2:{s:5:\"class\";s:20:\"ilpersonaldesktopgui\";s:6:\"parent\";i:0;}i:2;a:2:{s:5:\"class\";s:27:\"ilbookmarkadministrationgui\";s:6:\"parent\";i:1;}i:3;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:1;}i:4;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:3;}i:5;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:4;}i:6;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:5;}i:7;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:3;}i:8;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:7;}i:9;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:7;}i:10;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:9;}i:11;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:7;}i:12;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:11;}i:13;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:3;}i:14;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:13;}i:15;a:2:{s:5:\"class\";s:12:\"ilobjusergui\";s:6:\"parent\";i:1;}i:16;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:15;}i:17;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:16;}i:18;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:17;}i:19;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:18;}i:20;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:16;}i:21;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:20;}i:22;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:20;}i:23;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:22;}i:24;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:20;}i:25;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:24;}i:26;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:16;}i:27;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:26;}i:28;a:2:{s:5:\"class\";s:12:\"ilpdnotesgui\";s:6:\"parent\";i:1;}i:29;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:28;}i:30;a:2:{s:5:\"class\";s:20:\"ilpersonalprofilegui\";s:6:\"parent\";i:1;}i:31;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:30;}}','a:11:{s:0:\"\";a:13:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";i:10;s:0:\"\";i:11;s:0:\"\";i:12;s:0:\"\";}s:13:\"illpfiltergui\";a:4:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";}s:20:\"illplistofobjectsgui\";a:2:{i:0;s:13:\"illpfiltergui\";i:1;s:13:\"illpfiltergui\";}s:17:\"ilpdfpresentation\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:21:\"illplistofprogressgui\";a:6:{i:0;s:0:\"\";i:1;s:13:\"illpfiltergui\";i:2;s:17:\"ilpdfpresentation\";i:3;s:0:\"\";i:4;s:13:\"illpfiltergui\";i:5;s:17:\"ilpdfpresentation\";}s:21:\"illplistofsettingsgui\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:21:\"illearningprogressgui\";a:6:{i:0;s:20:\"illplistofobjectsgui\";i:1;s:21:\"illplistofprogressgui\";i:2;s:21:\"illplistofsettingsgui\";i:3;s:20:\"illplistofobjectsgui\";i:4;s:21:\"illplistofprogressgui\";i:5;s:21:\"illplistofsettingsgui\";}s:12:\"ilobjusergui\";a:1:{i:0;s:21:\"illearningprogressgui\";}s:12:\"ilpdnotesgui\";a:1:{i:0;s:9:\"ilnotegui\";}s:20:\"ilpersonalprofilegui\";a:1:{i:0;s:0:\"\";}s:20:\"ilpersonaldesktopgui\";a:5:{i:0;s:27:\"ilbookmarkadministrationgui\";i:1;s:21:\"illearningprogressgui\";i:2;s:12:\"ilobjusergui\";i:3;s:12:\"ilpdnotesgui\";i:4;s:20:\"ilpersonalprofilegui\";}}','a:12:{s:0:\"\";a:24:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:13:\"illpfiltergui\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:13:\"illpfiltergui\";i:6;s:0:\"\";i:7;s:17:\"ilpdfpresentation\";i:8;s:21:\"illplistofprogressgui\";i:9;s:0:\"\";i:10;s:21:\"illplistofsettingsgui\";i:11;s:0:\"\";i:12;s:13:\"illpfiltergui\";i:13;s:0:\"\";i:14;s:0:\"\";i:15;s:13:\"illpfiltergui\";i:16;s:0:\"\";i:17;s:17:\"ilpdfpresentation\";i:18;s:21:\"illplistofprogressgui\";i:19;s:0:\"\";i:20;s:21:\"illplistofsettingsgui\";i:21;s:0:\"\";i:22;s:0:\"\";i:23;s:20:\"ilpersonalprofilegui\";}s:13:\"illpfiltergui\";a:4:{i:0;s:20:\"illplistofobjectsgui\";i:1;s:21:\"illplistofprogressgui\";i:2;s:20:\"illplistofobjectsgui\";i:3;s:21:\"illplistofprogressgui\";}s:17:\"ilpdfpresentation\";a:2:{i:0;s:21:\"illplistofprogressgui\";i:1;s:21:\"illplistofprogressgui\";}s:20:\"illplistofobjectsgui\";a:2:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";}s:21:\"illplistofprogressgui\";a:2:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";}s:21:\"illplistofsettingsgui\";a:2:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";}s:21:\"illearningprogressgui\";a:2:{i:0;s:12:\"ilobjusergui\";i:1;s:20:\"ilpersonaldesktopgui\";}s:9:\"ilnotegui\";a:1:{i:0;s:12:\"ilpdnotesgui\";}s:27:\"ilbookmarkadministrationgui\";a:1:{i:0;s:20:\"ilpersonaldesktopgui\";}s:12:\"ilobjusergui\";a:1:{i:0;s:20:\"ilpersonaldesktopgui\";}s:12:\"ilpdnotesgui\";a:1:{i:0;s:20:\"ilpersonaldesktopgui\";}s:20:\"ilpersonalprofilegui\";a:1:{i:0;s:20:\"ilpersonaldesktopgui\";}}'),('illmpresentationgui','a:6:{i:1;a:2:{s:5:\"class\";s:19:\"illmpresentationgui\";s:6:\"parent\";i:0;}i:2;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:1;}i:3;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:2;}i:4;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:3;}i:5;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:2;}i:6;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:1;}}','a:4:{s:0:\"\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:13:\"ilfeedbackgui\";a:1:{i:0;s:1:\"\"\";}s:15:\"ilinfoscreengui\";a:2:{i:0;s:13:\"ilfeedbackgui\";i:1;s:9:\"ilnotegui\";}s:19:\"illmpresentationgui\";a:2:{i:0;s:15:\"ilinfoscreengui\";i:1;s:9:\"ilnotegui\";}}','a:5:{s:0:\"\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:1:\"\"\";a:1:{i:0;s:13:\"ilfeedbackgui\";}s:13:\"ilfeedbackgui\";a:1:{i:0;s:15:\"ilinfoscreengui\";}s:9:\"ilnotegui\";a:2:{i:0;s:15:\"ilinfoscreengui\";i:1;s:19:\"illmpresentationgui\";}s:15:\"ilinfoscreengui\";a:1:{i:0;s:19:\"illmpresentationgui\";}}'),('illmeditorgui','a:92:{i:1;a:2:{s:5:\"class\";s:13:\"illmeditorgui\";s:6:\"parent\";i:0;}i:2;a:2:{s:5:\"class\";s:13:\"ilmetadatagui\";s:6:\"parent\";i:1;}i:3;a:2:{s:5:\"class\";s:14:\"ilobjdlbookgui\";s:6:\"parent\";i:1;}i:4;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:3;}i:5;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:4;}i:6;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:5;}i:7;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:6;}i:8;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:4;}i:9;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:8;}i:10;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:8;}i:11;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:10;}i:12;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:8;}i:13;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:12;}i:14;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:4;}i:15;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:14;}i:16;a:2:{s:5:\"class\";s:17:\"illmpageobjectgui\";s:6:\"parent\";i:3;}i:17;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:16;}i:18;a:2:{s:5:\"class\";s:15:\"ilpageobjectgui\";s:6:\"parent\";i:16;}i:19;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:18;}i:20;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:19;}i:21;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:20;}i:22;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:20;}i:23;a:2:{s:5:\"class\";s:25:\"ilmediapooltargetselector\";s:6:\"parent\";i:18;}i:24;a:2:{s:5:\"class\";s:15:\"ilpageeditorgui\";s:6:\"parent\";i:18;}i:25;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:24;}i:26;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:24;}i:27;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:26;}i:28;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:26;}i:29;a:2:{s:5:\"class\";s:15:\"ilpcfileitemgui\";s:6:\"parent\";i:24;}i:30;a:2:{s:5:\"class\";s:15:\"ilpcfilelistgui\";s:6:\"parent\";i:24;}i:31;a:2:{s:5:\"class\";s:11:\"ilpclistgui\";s:6:\"parent\";i:24;}i:32;a:2:{s:5:\"class\";s:15:\"ilpclistitemgui\";s:6:\"parent\";i:24;}i:33;a:2:{s:5:\"class\";s:18:\"ilpcmediaobjectgui\";s:6:\"parent\";i:24;}i:34;a:2:{s:5:\"class\";s:16:\"ilpcparagraphgui\";s:6:\"parent\";i:24;}i:35;a:2:{s:5:\"class\";s:15:\"ilpcquestiongui\";s:6:\"parent\";i:24;}i:36;a:2:{s:5:\"class\";s:17:\"ilpcsourcecodegui\";s:6:\"parent\";i:24;}i:37;a:2:{s:5:\"class\";s:16:\"ilpctabledatagui\";s:6:\"parent\";i:24;}i:38;a:2:{s:5:\"class\";s:12:\"ilpctablegui\";s:6:\"parent\";i:24;}i:39;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:3;}i:40;a:2:{s:5:\"class\";s:18:\"ilobjstylesheetgui\";s:6:\"parent\";i:3;}i:41;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:40;}i:42;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:3;}i:43;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:42;}i:44;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:43;}i:45;a:2:{s:5:\"class\";s:20:\"ilstructureobjectgui\";s:6:\"parent\";i:3;}i:46;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:45;}i:47;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:45;}i:48;a:2:{s:5:\"class\";s:22:\"ilobjlearningmodulegui\";s:6:\"parent\";i:1;}i:49;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:48;}i:50;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:49;}i:51;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:50;}i:52;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:51;}i:53;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:49;}i:54;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:53;}i:55;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:53;}i:56;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:55;}i:57;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:53;}i:58;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:57;}i:59;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:49;}i:60;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:59;}i:61;a:2:{s:5:\"class\";s:17:\"illmpageobjectgui\";s:6:\"parent\";i:48;}i:62;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:61;}i:63;a:2:{s:5:\"class\";s:15:\"ilpageobjectgui\";s:6:\"parent\";i:61;}i:64;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:63;}i:65;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:64;}i:66;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:65;}i:67;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:65;}i:68;a:2:{s:5:\"class\";s:25:\"ilmediapooltargetselector\";s:6:\"parent\";i:63;}i:69;a:2:{s:5:\"class\";s:15:\"ilpageeditorgui\";s:6:\"parent\";i:63;}i:70;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:69;}i:71;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:69;}i:72;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:71;}i:73;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:71;}i:74;a:2:{s:5:\"class\";s:15:\"ilpcfileitemgui\";s:6:\"parent\";i:69;}i:75;a:2:{s:5:\"class\";s:15:\"ilpcfilelistgui\";s:6:\"parent\";i:69;}i:76;a:2:{s:5:\"class\";s:11:\"ilpclistgui\";s:6:\"parent\";i:69;}i:77;a:2:{s:5:\"class\";s:15:\"ilpclistitemgui\";s:6:\"parent\";i:69;}i:78;a:2:{s:5:\"class\";s:18:\"ilpcmediaobjectgui\";s:6:\"parent\";i:69;}i:79;a:2:{s:5:\"class\";s:16:\"ilpcparagraphgui\";s:6:\"parent\";i:69;}i:80;a:2:{s:5:\"class\";s:15:\"ilpcquestiongui\";s:6:\"parent\";i:69;}i:81;a:2:{s:5:\"class\";s:17:\"ilpcsourcecodegui\";s:6:\"parent\";i:69;}i:82;a:2:{s:5:\"class\";s:16:\"ilpctabledatagui\";s:6:\"parent\";i:69;}i:83;a:2:{s:5:\"class\";s:12:\"ilpctablegui\";s:6:\"parent\";i:69;}i:84;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:48;}i:85;a:2:{s:5:\"class\";s:18:\"ilobjstylesheetgui\";s:6:\"parent\";i:48;}i:86;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:85;}i:87;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:48;}i:88;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:87;}i:89;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:88;}i:90;a:2:{s:5:\"class\";s:20:\"ilstructureobjectgui\";s:6:\"parent\";i:48;}i:91;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:90;}i:92;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:90;}}','a:19:{s:0:\"\";a:55:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";i:10;s:0:\"\";i:11;s:0:\"\";i:12;s:0:\"\";i:13;s:0:\"\";i:14;s:0:\"\";i:15;s:0:\"\";i:16;s:0:\"\";i:17;s:0:\"\";i:18;s:0:\"\";i:19;s:0:\"\";i:20;s:0:\"\";i:21;s:0:\"\";i:22;s:0:\"\";i:23;s:0:\"\";i:24;s:0:\"\";i:25;s:0:\"\";i:26;s:0:\"\";i:27;s:0:\"\";i:28;s:0:\"\";i:29;s:0:\"\";i:30;s:0:\"\";i:31;s:0:\"\";i:32;s:0:\"\";i:33;s:0:\"\";i:34;s:0:\"\";i:35;s:0:\"\";i:36;s:0:\"\";i:37;s:0:\"\";i:38;s:0:\"\";i:39;s:0:\"\";i:40;s:0:\"\";i:41;s:0:\"\";i:42;s:0:\"\";i:43;s:0:\"\";i:44;s:0:\"\";i:45;s:0:\"\";i:46;s:0:\"\";i:47;s:0:\"\";i:48;s:0:\"\";i:49;s:0:\"\";i:50;s:0:\"\";i:51;s:0:\"\";i:52;s:0:\"\";i:53;s:0:\"\";i:54;s:0:\"\";}s:13:\"illpfiltergui\";a:4:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";}s:20:\"illplistofobjectsgui\";a:2:{i:0;s:13:\"illpfiltergui\";i:1;s:13:\"illpfiltergui\";}s:17:\"ilpdfpresentation\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:21:\"illplistofprogressgui\";a:6:{i:0;s:0:\"\";i:1;s:13:\"illpfiltergui\";i:2;s:17:\"ilpdfpresentation\";i:3;s:0:\"\";i:4;s:13:\"illpfiltergui\";i:5;s:17:\"ilpdfpresentation\";}s:21:\"illplistofsettingsgui\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:21:\"illearningprogressgui\";a:6:{i:0;s:20:\"illplistofobjectsgui\";i:1;s:21:\"illplistofprogressgui\";i:2;s:21:\"illplistofsettingsgui\";i:3;s:20:\"illplistofobjectsgui\";i:4;s:21:\"illplistofprogressgui\";i:5;s:21:\"illplistofsettingsgui\";}s:19:\"ilobjmediaobjectgui\";a:8:{i:0;s:17:\"ilinternallinkgui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:17:\"ilinternallinkgui\";i:3;s:13:\"ilmdeditorgui\";i:4;s:17:\"ilinternallinkgui\";i:5;s:13:\"ilmdeditorgui\";i:6;s:17:\"ilinternallinkgui\";i:7;s:13:\"ilmdeditorgui\";}s:18:\"ileditclipboardgui\";a:2:{i:0;s:19:\"ilobjmediaobjectgui\";i:1;s:19:\"ilobjmediaobjectgui\";}s:15:\"ilpageeditorgui\";a:24:{i:0;s:17:\"ilinternallinkgui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:15:\"ilpcfileitemgui\";i:3;s:15:\"ilpcfilelistgui\";i:4;s:11:\"ilpclistgui\";i:5;s:15:\"ilpclistitemgui\";i:6;s:18:\"ilpcmediaobjectgui\";i:7;s:16:\"ilpcparagraphgui\";i:8;s:15:\"ilpcquestiongui\";i:9;s:17:\"ilpcsourcecodegui\";i:10;s:16:\"ilpctabledatagui\";i:11;s:12:\"ilpctablegui\";i:12;s:17:\"ilinternallinkgui\";i:13;s:19:\"ilobjmediaobjectgui\";i:14;s:15:\"ilpcfileitemgui\";i:15;s:15:\"ilpcfilelistgui\";i:16;s:11:\"ilpclistgui\";i:17;s:15:\"ilpclistitemgui\";i:18;s:18:\"ilpcmediaobjectgui\";i:19;s:16:\"ilpcparagraphgui\";i:20;s:15:\"ilpcquestiongui\";i:21;s:17:\"ilpcsourcecodegui\";i:22;s:16:\"ilpctabledatagui\";i:23;s:12:\"ilpctablegui\";}s:15:\"ilpageobjectgui\";a:6:{i:0;s:18:\"ileditclipboardgui\";i:1;s:25:\"ilmediapooltargetselector\";i:2;s:15:\"ilpageeditorgui\";i:3;s:18:\"ileditclipboardgui\";i:4;s:25:\"ilmediapooltargetselector\";i:5;s:15:\"ilpageeditorgui\";}s:17:\"illmpageobjectgui\";a:4:{i:0;s:13:\"ilmdeditorgui\";i:1;s:15:\"ilpageobjectgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:15:\"ilpageobjectgui\";}s:18:\"ilobjstylesheetgui\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:12:\"ilobjrolegui\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:15:\"ilpermissiongui\";a:2:{i:0;s:12:\"ilobjrolegui\";i:1;s:12:\"ilobjrolegui\";}s:20:\"ilstructureobjectgui\";a:4:{i:0;s:27:\"ilconditionhandlerinterface\";i:1;s:13:\"ilmdeditorgui\";i:2;s:27:\"ilconditionhandlerinterface\";i:3;s:13:\"ilmdeditorgui\";}s:14:\"ilobjdlbookgui\";a:6:{i:0;s:21:\"illearningprogressgui\";i:1;s:17:\"illmpageobjectgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:18:\"ilobjstylesheetgui\";i:4;s:15:\"ilpermissiongui\";i:5;s:20:\"ilstructureobjectgui\";}s:22:\"ilobjlearningmodulegui\";a:6:{i:0;s:21:\"illearningprogressgui\";i:1;s:17:\"illmpageobjectgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:18:\"ilobjstylesheetgui\";i:4;s:15:\"ilpermissiongui\";i:5;s:20:\"ilstructureobjectgui\";}s:13:\"illmeditorgui\";a:3:{i:0;s:13:\"ilmetadatagui\";i:1;s:14:\"ilobjdlbookgui\";i:2;s:22:\"ilobjlearningmodulegui\";}}','a:33:{s:0:\"\";a:69:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:13:\"illpfiltergui\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:13:\"illpfiltergui\";i:6;s:0:\"\";i:7;s:17:\"ilpdfpresentation\";i:8;s:21:\"illplistofprogressgui\";i:9;s:0:\"\";i:10;s:21:\"illplistofsettingsgui\";i:11;s:0:\"\";i:12;s:0:\"\";i:13;s:0:\"\";i:14;s:0:\"\";i:15;s:0:\"\";i:16;s:0:\"\";i:17;s:0:\"\";i:18;s:0:\"\";i:19;s:0:\"\";i:20;s:0:\"\";i:21;s:0:\"\";i:22;s:0:\"\";i:23;s:0:\"\";i:24;s:0:\"\";i:25;s:0:\"\";i:26;s:0:\"\";i:27;s:0:\"\";i:28;s:0:\"\";i:29;s:0:\"\";i:30;s:18:\"ilobjstylesheetgui\";i:31;s:0:\"\";i:32;s:12:\"ilobjrolegui\";i:33;s:0:\"\";i:34;s:0:\"\";i:35;s:0:\"\";i:36;s:13:\"illpfiltergui\";i:37;s:0:\"\";i:38;s:0:\"\";i:39;s:13:\"illpfiltergui\";i:40;s:0:\"\";i:41;s:17:\"ilpdfpresentation\";i:42;s:21:\"illplistofprogressgui\";i:43;s:0:\"\";i:44;s:21:\"illplistofsettingsgui\";i:45;s:0:\"\";i:46;s:0:\"\";i:47;s:0:\"\";i:48;s:0:\"\";i:49;s:0:\"\";i:50;s:0:\"\";i:51;s:0:\"\";i:52;s:0:\"\";i:53;s:0:\"\";i:54;s:0:\"\";i:55;s:0:\"\";i:56;s:0:\"\";i:57;s:0:\"\";i:58;s:0:\"\";i:59;s:0:\"\";i:60;s:0:\"\";i:61;s:0:\"\";i:62;s:0:\"\";i:63;s:0:\"\";i:64;s:18:\"ilobjstylesheetgui\";i:65;s:0:\"\";i:66;s:12:\"ilobjrolegui\";i:67;s:0:\"\";i:68;s:0:\"\";}s:13:\"illpfiltergui\";a:4:{i:0;s:20:\"illplistofobjectsgui\";i:1;s:21:\"illplistofprogressgui\";i:2;s:20:\"illplistofobjectsgui\";i:3;s:21:\"illplistofprogressgui\";}s:17:\"ilpdfpresentation\";a:2:{i:0;s:21:\"illplistofprogressgui\";i:1;s:21:\"illplistofprogressgui\";}s:20:\"illplistofobjectsgui\";a:2:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";}s:21:\"illplistofprogressgui\";a:2:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";}s:21:\"illplistofsettingsgui\";a:2:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";}s:17:\"ilinternallinkgui\";a:6:{i:0;s:19:\"ilobjmediaobjectgui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:19:\"ilobjmediaobjectgui\";i:4;s:19:\"ilobjmediaobjectgui\";i:5;s:15:\"ilpageeditorgui\";}s:13:\"ilmdeditorgui\";a:10:{i:0;s:19:\"ilobjmediaobjectgui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:17:\"illmpageobjectgui\";i:3;s:20:\"ilstructureobjectgui\";i:4;s:14:\"ilobjdlbookgui\";i:5;s:19:\"ilobjmediaobjectgui\";i:6;s:19:\"ilobjmediaobjectgui\";i:7;s:17:\"illmpageobjectgui\";i:8;s:20:\"ilstructureobjectgui\";i:9;s:22:\"ilobjlearningmodulegui\";}s:19:\"ilobjmediaobjectgui\";a:4:{i:0;s:18:\"ileditclipboardgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:18:\"ileditclipboardgui\";i:3;s:15:\"ilpageeditorgui\";}s:15:\"ilpcfileitemgui\";a:2:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";}s:15:\"ilpcfilelistgui\";a:2:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";}s:11:\"ilpclistgui\";a:2:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";}s:15:\"ilpclistitemgui\";a:2:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";}s:18:\"ilpcmediaobjectgui\";a:2:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";}s:16:\"ilpcparagraphgui\";a:2:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";}s:15:\"ilpcquestiongui\";a:2:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";}s:17:\"ilpcsourcecodegui\";a:2:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";}s:16:\"ilpctabledatagui\";a:2:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";}s:12:\"ilpctablegui\";a:2:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";}s:18:\"ileditclipboardgui\";a:2:{i:0;s:15:\"ilpageobjectgui\";i:1;s:15:\"ilpageobjectgui\";}s:25:\"ilmediapooltargetselector\";a:2:{i:0;s:15:\"ilpageobjectgui\";i:1;s:15:\"ilpageobjectgui\";}s:15:\"ilpageeditorgui\";a:2:{i:0;s:15:\"ilpageobjectgui\";i:1;s:15:\"ilpageobjectgui\";}s:15:\"ilpageobjectgui\";a:2:{i:0;s:17:\"illmpageobjectgui\";i:1;s:17:\"illmpageobjectgui\";}s:12:\"ilobjrolegui\";a:2:{i:0;s:15:\"ilpermissiongui\";i:1;s:15:\"ilpermissiongui\";}s:27:\"ilconditionhandlerinterface\";a:2:{i:0;s:20:\"ilstructureobjectgui\";i:1;s:20:\"ilstructureobjectgui\";}s:21:\"illearningprogressgui\";a:2:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";}s:17:\"illmpageobjectgui\";a:2:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";}s:18:\"ilobjstylesheetgui\";a:2:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";}s:15:\"ilpermissiongui\";a:2:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";}s:20:\"ilstructureobjectgui\";a:2:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";}s:13:\"ilmetadatagui\";a:1:{i:0;s:13:\"illmeditorgui\";}s:14:\"ilobjdlbookgui\";a:1:{i:0;s:13:\"illmeditorgui\";}s:22:\"ilobjlearningmodulegui\";a:1:{i:0;s:13:\"illmeditorgui\";}}'),('iladministrationgui','a:422:{i:1;a:2:{s:5:\"class\";s:19:\"iladministrationgui\";s:6:\"parent\";i:0;}i:2;a:2:{s:5:\"class\";s:21:\"ilcourseobjectivesgui\";s:6:\"parent\";i:1;}i:3;a:2:{s:5:\"class\";s:24:\"ilobjassessmentfoldergui\";s:6:\"parent\";i:1;}i:4;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:3;}i:5;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:4;}i:6;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:5;}i:7;a:2:{s:5:\"class\";s:20:\"ilobjauthsettingsgui\";s:6:\"parent\";i:1;}i:8;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:7;}i:9;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:8;}i:10;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:9;}i:11;a:2:{s:5:\"class\";s:16:\"ilobjcategorygui\";s:6:\"parent\";i:1;}i:12;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:11;}i:13;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:12;}i:14;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:13;}i:15;a:2:{s:5:\"class\";s:12:\"ilobjchatgui\";s:6:\"parent\";i:1;}i:16;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:15;}i:17;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:16;}i:18;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:17;}i:19;a:2:{s:5:\"class\";s:18:\"ilobjchatservergui\";s:6:\"parent\";i:1;}i:20;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:19;}i:21;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:20;}i:22;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:21;}i:23;a:2:{s:5:\"class\";s:14:\"ilobjcoursegui\";s:6:\"parent\";i:1;}i:24;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:23;}i:25;a:2:{s:5:\"class\";s:21:\"ilcourseobjectivesgui\";s:6:\"parent\";i:23;}i:26;a:2:{s:5:\"class\";s:19:\"ilcourseregistergui\";s:6:\"parent\";i:23;}i:27;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:23;}i:28;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:27;}i:29;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:28;}i:30;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:27;}i:31;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:23;}i:32;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:31;}i:33;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:32;}i:34;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:33;}i:35;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:31;}i:36;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:35;}i:37;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:35;}i:38;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:37;}i:39;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:35;}i:40;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:39;}i:41;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:31;}i:42;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:41;}i:43;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:23;}i:44;a:2:{s:5:\"class\";s:22:\"ilobjcoursegroupinggui\";s:6:\"parent\";i:23;}i:45;a:2:{s:5:\"class\";s:20:\"ilpaymentpurchasegui\";s:6:\"parent\";i:23;}i:46;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:23;}i:47;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:46;}i:48;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:47;}i:49;a:2:{s:5:\"class\";s:14:\"ilobjdlbookgui\";s:6:\"parent\";i:1;}i:50;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:49;}i:51;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:50;}i:52;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:51;}i:53;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:52;}i:54;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:50;}i:55;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:54;}i:56;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:54;}i:57;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:56;}i:58;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:54;}i:59;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:58;}i:60;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:50;}i:61;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:60;}i:62;a:2:{s:5:\"class\";s:17:\"illmpageobjectgui\";s:6:\"parent\";i:49;}i:63;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:62;}i:64;a:2:{s:5:\"class\";s:15:\"ilpageobjectgui\";s:6:\"parent\";i:62;}i:65;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:64;}i:66;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:65;}i:67;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:66;}i:68;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:66;}i:69;a:2:{s:5:\"class\";s:25:\"ilmediapooltargetselector\";s:6:\"parent\";i:64;}i:70;a:2:{s:5:\"class\";s:15:\"ilpageeditorgui\";s:6:\"parent\";i:64;}i:71;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:70;}i:72;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:70;}i:73;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:72;}i:74;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:72;}i:75;a:2:{s:5:\"class\";s:15:\"ilpcfileitemgui\";s:6:\"parent\";i:70;}i:76;a:2:{s:5:\"class\";s:15:\"ilpcfilelistgui\";s:6:\"parent\";i:70;}i:77;a:2:{s:5:\"class\";s:11:\"ilpclistgui\";s:6:\"parent\";i:70;}i:78;a:2:{s:5:\"class\";s:15:\"ilpclistitemgui\";s:6:\"parent\";i:70;}i:79;a:2:{s:5:\"class\";s:18:\"ilpcmediaobjectgui\";s:6:\"parent\";i:70;}i:80;a:2:{s:5:\"class\";s:16:\"ilpcparagraphgui\";s:6:\"parent\";i:70;}i:81;a:2:{s:5:\"class\";s:15:\"ilpcquestiongui\";s:6:\"parent\";i:70;}i:82;a:2:{s:5:\"class\";s:17:\"ilpcsourcecodegui\";s:6:\"parent\";i:70;}i:83;a:2:{s:5:\"class\";s:16:\"ilpctabledatagui\";s:6:\"parent\";i:70;}i:84;a:2:{s:5:\"class\";s:12:\"ilpctablegui\";s:6:\"parent\";i:70;}i:85;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:49;}i:86;a:2:{s:5:\"class\";s:18:\"ilobjstylesheetgui\";s:6:\"parent\";i:49;}i:87;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:86;}i:88;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:49;}i:89;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:88;}i:90;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:89;}i:91;a:2:{s:5:\"class\";s:20:\"ilstructureobjectgui\";s:6:\"parent\";i:49;}i:92;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:91;}i:93;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:91;}i:94;a:2:{s:5:\"class\";s:16:\"ilobjexercisegui\";s:6:\"parent\";i:1;}i:95;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:94;}i:96;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:95;}i:97;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:96;}i:98;a:2:{s:5:\"class\";s:29:\"ilobjexternaltoolssettingsgui\";s:6:\"parent\";i:1;}i:99;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:98;}i:100;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:99;}i:101;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:100;}i:102;a:2:{s:5:\"class\";s:19:\"ilobjfilebasedlmgui\";s:6:\"parent\";i:1;}i:103;a:2:{s:5:\"class\";s:15:\"ilfilesystemgui\";s:6:\"parent\";i:102;}i:104;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:102;}i:105;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:102;}i:106;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:105;}i:107;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:106;}i:108;a:2:{s:5:\"class\";s:12:\"ilobjfilegui\";s:6:\"parent\";i:1;}i:109;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:108;}i:110;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:109;}i:111;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:110;}i:112;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:109;}i:113;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:108;}i:114;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:108;}i:115;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:114;}i:116;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:115;}i:117;a:2:{s:5:\"class\";s:14:\"ilobjfoldergui\";s:6:\"parent\";i:1;}i:118;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:117;}i:119;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:117;}i:120;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:119;}i:121;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:120;}i:122;a:2:{s:5:\"class\";s:13:\"ilobjforumgui\";s:6:\"parent\";i:1;}i:123;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:122;}i:124;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:123;}i:125;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:124;}i:126;a:2:{s:5:\"class\";s:16:\"ilobjglossarygui\";s:6:\"parent\";i:1;}i:127;a:2:{s:5:\"class\";s:17:\"ilglossarytermgui\";s:6:\"parent\";i:126;}i:128;a:2:{s:5:\"class\";s:25:\"iltermdefinitioneditorgui\";s:6:\"parent\";i:127;}i:129;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:128;}i:130;a:2:{s:5:\"class\";s:15:\"ilpageobjectgui\";s:6:\"parent\";i:128;}i:131;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:130;}i:132;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:131;}i:133;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:132;}i:134;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:132;}i:135;a:2:{s:5:\"class\";s:25:\"ilmediapooltargetselector\";s:6:\"parent\";i:130;}i:136;a:2:{s:5:\"class\";s:15:\"ilpageeditorgui\";s:6:\"parent\";i:130;}i:137;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:136;}i:138;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:136;}i:139;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:138;}i:140;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:138;}i:141;a:2:{s:5:\"class\";s:15:\"ilpcfileitemgui\";s:6:\"parent\";i:136;}i:142;a:2:{s:5:\"class\";s:15:\"ilpcfilelistgui\";s:6:\"parent\";i:136;}i:143;a:2:{s:5:\"class\";s:11:\"ilpclistgui\";s:6:\"parent\";i:136;}i:144;a:2:{s:5:\"class\";s:15:\"ilpclistitemgui\";s:6:\"parent\";i:136;}i:145;a:2:{s:5:\"class\";s:18:\"ilpcmediaobjectgui\";s:6:\"parent\";i:136;}i:146;a:2:{s:5:\"class\";s:16:\"ilpcparagraphgui\";s:6:\"parent\";i:136;}i:147;a:2:{s:5:\"class\";s:15:\"ilpcquestiongui\";s:6:\"parent\";i:136;}i:148;a:2:{s:5:\"class\";s:17:\"ilpcsourcecodegui\";s:6:\"parent\";i:136;}i:149;a:2:{s:5:\"class\";s:16:\"ilpctabledatagui\";s:6:\"parent\";i:136;}i:150;a:2:{s:5:\"class\";s:12:\"ilpctablegui\";s:6:\"parent\";i:136;}i:151;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:126;}i:152;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:126;}i:153;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:152;}i:154;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:153;}i:155;a:2:{s:5:\"class\";s:13:\"ilobjgroupgui\";s:6:\"parent\";i:1;}i:156;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:155;}i:157;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:155;}i:158;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:157;}i:159;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:158;}i:160;a:2:{s:5:\"class\";s:13:\"ilregistergui\";s:6:\"parent\";i:155;}i:161;a:2:{s:5:\"class\";s:22:\"ilobjilincclassroomgui\";s:6:\"parent\";i:1;}i:162;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:161;}i:163;a:2:{s:5:\"class\";s:19:\"ilobjilinccoursegui\";s:6:\"parent\";i:1;}i:164;a:2:{s:5:\"class\";s:22:\"ilobjilincclassroomgui\";s:6:\"parent\";i:163;}i:165;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:164;}i:166;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:163;}i:167;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:166;}i:168;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:167;}i:169;a:2:{s:5:\"class\";s:22:\"ilobjlanguagefoldergui\";s:6:\"parent\";i:1;}i:170;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:169;}i:171;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:170;}i:172;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:171;}i:173;a:2:{s:5:\"class\";s:22:\"ilobjlearningmodulegui\";s:6:\"parent\";i:1;}i:174;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:173;}i:175;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:174;}i:176;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:175;}i:177;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:176;}i:178;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:174;}i:179;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:178;}i:180;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:178;}i:181;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:180;}i:182;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:178;}i:183;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:182;}i:184;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:174;}i:185;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:184;}i:186;a:2:{s:5:\"class\";s:17:\"illmpageobjectgui\";s:6:\"parent\";i:173;}i:187;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:186;}i:188;a:2:{s:5:\"class\";s:15:\"ilpageobjectgui\";s:6:\"parent\";i:186;}i:189;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:188;}i:190;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:189;}i:191;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:190;}i:192;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:190;}i:193;a:2:{s:5:\"class\";s:25:\"ilmediapooltargetselector\";s:6:\"parent\";i:188;}i:194;a:2:{s:5:\"class\";s:15:\"ilpageeditorgui\";s:6:\"parent\";i:188;}i:195;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:194;}i:196;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:194;}i:197;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:196;}i:198;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:196;}i:199;a:2:{s:5:\"class\";s:15:\"ilpcfileitemgui\";s:6:\"parent\";i:194;}i:200;a:2:{s:5:\"class\";s:15:\"ilpcfilelistgui\";s:6:\"parent\";i:194;}i:201;a:2:{s:5:\"class\";s:11:\"ilpclistgui\";s:6:\"parent\";i:194;}i:202;a:2:{s:5:\"class\";s:15:\"ilpclistitemgui\";s:6:\"parent\";i:194;}i:203;a:2:{s:5:\"class\";s:18:\"ilpcmediaobjectgui\";s:6:\"parent\";i:194;}i:204;a:2:{s:5:\"class\";s:16:\"ilpcparagraphgui\";s:6:\"parent\";i:194;}i:205;a:2:{s:5:\"class\";s:15:\"ilpcquestiongui\";s:6:\"parent\";i:194;}i:206;a:2:{s:5:\"class\";s:17:\"ilpcsourcecodegui\";s:6:\"parent\";i:194;}i:207;a:2:{s:5:\"class\";s:16:\"ilpctabledatagui\";s:6:\"parent\";i:194;}i:208;a:2:{s:5:\"class\";s:12:\"ilpctablegui\";s:6:\"parent\";i:194;}i:209;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:173;}i:210;a:2:{s:5:\"class\";s:18:\"ilobjstylesheetgui\";s:6:\"parent\";i:173;}i:211;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:210;}i:212;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:173;}i:213;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:212;}i:214;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:213;}i:215;a:2:{s:5:\"class\";s:20:\"ilstructureobjectgui\";s:6:\"parent\";i:173;}i:216;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:215;}i:217;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:215;}i:218;a:2:{s:5:\"class\";s:20:\"ilobjlinkresourcegui\";s:6:\"parent\";i:1;}i:219;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:218;}i:220;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:219;}i:221;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:220;}i:222;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:219;}i:223;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:218;}i:224;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:218;}i:225;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:224;}i:226;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:225;}i:227;a:2:{s:5:\"class\";s:12:\"ilobjmailgui\";s:6:\"parent\";i:1;}i:228;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:227;}i:229;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:228;}i:230;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:229;}i:231;a:2:{s:5:\"class\";s:17:\"ilobjmediapoolgui\";s:6:\"parent\";i:1;}i:232;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:231;}i:233;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:232;}i:234;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:233;}i:235;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:233;}i:236;a:2:{s:5:\"class\";s:14:\"ilobjfoldergui\";s:6:\"parent\";i:231;}i:237;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:236;}i:238;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:236;}i:239;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:238;}i:240;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:239;}i:241;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:231;}i:242;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:241;}i:243;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:241;}i:244;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:231;}i:245;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:244;}i:246;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:245;}i:247;a:2:{s:5:\"class\";s:20:\"ilobjobjectfoldergui\";s:6:\"parent\";i:1;}i:248;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:247;}i:249;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:248;}i:250;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:249;}i:251;a:2:{s:5:\"class\";s:23:\"ilobjpaymentsettingsgui\";s:6:\"parent\";i:1;}i:252;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:251;}i:253;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:252;}i:254;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:253;}i:255;a:2:{s:5:\"class\";s:20:\"ilobjquestionpoolgui\";s:6:\"parent\";i:1;}i:256;a:2:{s:5:\"class\";s:16:\"ass_clozetestgui\";s:6:\"parent\";i:255;}i:257;a:2:{s:5:\"class\";s:23:\"ass_imagemapquestiongui\";s:6:\"parent\";i:255;}i:258;a:2:{s:5:\"class\";s:17:\"ass_javaappletgui\";s:6:\"parent\";i:255;}i:259;a:2:{s:5:\"class\";s:23:\"ass_matchingquestiongui\";s:6:\"parent\";i:255;}i:260;a:2:{s:5:\"class\";s:21:\"ass_multiplechoicegui\";s:6:\"parent\";i:255;}i:261;a:2:{s:5:\"class\";s:23:\"ass_orderingquestiongui\";s:6:\"parent\";i:255;}i:262;a:2:{s:5:\"class\";s:19:\"ass_textquestiongui\";s:6:\"parent\";i:255;}i:263;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:255;}i:264;a:2:{s:5:\"class\";s:15:\"ilpageobjectgui\";s:6:\"parent\";i:255;}i:265;a:2:{s:5:\"class\";s:18:\"ileditclipboardgui\";s:6:\"parent\";i:264;}i:266;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:265;}i:267;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:266;}i:268;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:266;}i:269;a:2:{s:5:\"class\";s:25:\"ilmediapooltargetselector\";s:6:\"parent\";i:264;}i:270;a:2:{s:5:\"class\";s:15:\"ilpageeditorgui\";s:6:\"parent\";i:264;}i:271;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:270;}i:272;a:2:{s:5:\"class\";s:19:\"ilobjmediaobjectgui\";s:6:\"parent\";i:270;}i:273;a:2:{s:5:\"class\";s:17:\"ilinternallinkgui\";s:6:\"parent\";i:272;}i:274;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:272;}i:275;a:2:{s:5:\"class\";s:15:\"ilpcfileitemgui\";s:6:\"parent\";i:270;}i:276;a:2:{s:5:\"class\";s:15:\"ilpcfilelistgui\";s:6:\"parent\";i:270;}i:277;a:2:{s:5:\"class\";s:11:\"ilpclistgui\";s:6:\"parent\";i:270;}i:278;a:2:{s:5:\"class\";s:15:\"ilpclistitemgui\";s:6:\"parent\";i:270;}i:279;a:2:{s:5:\"class\";s:18:\"ilpcmediaobjectgui\";s:6:\"parent\";i:270;}i:280;a:2:{s:5:\"class\";s:16:\"ilpcparagraphgui\";s:6:\"parent\";i:270;}i:281;a:2:{s:5:\"class\";s:15:\"ilpcquestiongui\";s:6:\"parent\";i:270;}i:282;a:2:{s:5:\"class\";s:17:\"ilpcsourcecodegui\";s:6:\"parent\";i:270;}i:283;a:2:{s:5:\"class\";s:16:\"ilpctabledatagui\";s:6:\"parent\";i:270;}i:284;a:2:{s:5:\"class\";s:12:\"ilpctablegui\";s:6:\"parent\";i:270;}i:285;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:255;}i:286;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:285;}i:287;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:286;}i:288;a:2:{s:5:\"class\";s:22:\"ilobjrecoveryfoldergui\";s:6:\"parent\";i:1;}i:289;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:288;}i:290;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:289;}i:291;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:290;}i:292;a:2:{s:5:\"class\";s:18:\"ilobjrolefoldergui\";s:6:\"parent\";i:1;}i:293;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:292;}i:294;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:293;}i:295;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:294;}i:296;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:1;}i:297;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:296;}i:298;a:2:{s:5:\"class\";s:20:\"ilobjroletemplategui\";s:6:\"parent\";i:1;}i:299;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:298;}i:300;a:2:{s:5:\"class\";s:18:\"ilobjrootfoldergui\";s:6:\"parent\";i:1;}i:301;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:300;}i:302;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:301;}i:303;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:302;}i:304;a:2:{s:5:\"class\";s:26:\"ilobjsahslearningmodulegui\";s:6:\"parent\";i:1;}i:305;a:2:{s:5:\"class\";s:15:\"ilfilesystemgui\";s:6:\"parent\";i:304;}i:306;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:304;}i:307;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:304;}i:308;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:307;}i:309;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:308;}i:310;a:2:{s:5:\"class\";s:22:\"ilobjsearchsettingsgui\";s:6:\"parent\";i:1;}i:311;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:310;}i:312;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:311;}i:313;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:312;}i:314;a:2:{s:5:\"class\";s:21:\"ilobjstylesettingsgui\";s:6:\"parent\";i:1;}i:315;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:314;}i:316;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:315;}i:317;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:316;}i:318;a:2:{s:5:\"class\";s:18:\"ilobjstylesheetgui\";s:6:\"parent\";i:1;}i:319;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:318;}i:320;a:2:{s:5:\"class\";s:14:\"ilobjsurveygui\";s:6:\"parent\";i:1;}i:321;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:320;}i:322;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:320;}i:323;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:322;}i:324;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:323;}i:325;a:2:{s:5:\"class\";s:21:\"ilsurveyevaluationgui\";s:6:\"parent\";i:320;}i:326;a:2:{s:5:\"class\";s:20:\"ilsurveyexecutiongui\";s:6:\"parent\";i:320;}i:327;a:2:{s:5:\"class\";s:26:\"ilobjsurveyquestionpoolgui\";s:6:\"parent\";i:1;}i:328;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:327;}i:329;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:327;}i:330;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:329;}i:331;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:330;}i:332;a:2:{s:5:\"class\";s:23:\"surveymetricquestiongui\";s:6:\"parent\";i:327;}i:333;a:2:{s:5:\"class\";s:24:\"surveynominalquestiongui\";s:6:\"parent\";i:327;}i:334;a:2:{s:5:\"class\";s:24:\"surveyordinalquestiongui\";s:6:\"parent\";i:327;}i:335;a:2:{s:5:\"class\";s:21:\"surveytextquestiongui\";s:6:\"parent\";i:327;}i:336;a:2:{s:5:\"class\";s:20:\"ilobjsystemfoldergui\";s:6:\"parent\";i:1;}i:337;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:336;}i:338;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:337;}i:339;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:338;}i:340;a:2:{s:5:\"class\";s:22:\"ilobjtaxonomyfoldergui\";s:6:\"parent\";i:1;}i:341;a:2:{s:5:\"class\";s:12:\"ilobjtestgui\";s:6:\"parent\";i:1;}i:342;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:341;}i:343;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:342;}i:344;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:343;}i:345;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:342;}i:346;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:341;}i:347;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:346;}i:348;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:347;}i:349;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:348;}i:350;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:346;}i:351;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:350;}i:352;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:350;}i:353;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:352;}i:354;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:350;}i:355;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:354;}i:356;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:346;}i:357;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:356;}i:358;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:341;}i:359;a:2:{s:5:\"class\";s:14:\"ilobjcoursegui\";s:6:\"parent\";i:341;}i:360;a:2:{s:5:\"class\";s:27:\"ilconditionhandlerinterface\";s:6:\"parent\";i:359;}i:361;a:2:{s:5:\"class\";s:21:\"ilcourseobjectivesgui\";s:6:\"parent\";i:359;}i:362;a:2:{s:5:\"class\";s:19:\"ilcourseregistergui\";s:6:\"parent\";i:359;}i:363;a:2:{s:5:\"class\";s:15:\"ilinfoscreengui\";s:6:\"parent\";i:359;}i:364;a:2:{s:5:\"class\";s:13:\"ilfeedbackgui\";s:6:\"parent\";i:363;}i:365;a:2:{s:5:\"class\";s:1:\"\"\";s:6:\"parent\";i:364;}i:366;a:2:{s:5:\"class\";s:9:\"ilnotegui\";s:6:\"parent\";i:363;}i:367;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:359;}i:368;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:367;}i:369;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:368;}i:370;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:369;}i:371;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:367;}i:372;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:371;}i:373;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:371;}i:374;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:373;}i:375;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:371;}i:376;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:375;}i:377;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:367;}i:378;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:377;}i:379;a:2:{s:5:\"class\";s:13:\"ilmdeditorgui\";s:6:\"parent\";i:359;}i:380;a:2:{s:5:\"class\";s:22:\"ilobjcoursegroupinggui\";s:6:\"parent\";i:359;}i:381;a:2:{s:5:\"class\";s:20:\"ilpaymentpurchasegui\";s:6:\"parent\";i:359;}i:382;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:359;}i:383;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:382;}i:384;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:383;}i:385;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:341;}i:386;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:385;}i:387;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:386;}i:388;a:2:{s:5:\"class\";s:19:\"iltestevaluationgui\";s:6:\"parent\";i:341;}i:389;a:2:{s:5:\"class\";s:15:\"iltestoutputgui\";s:6:\"parent\";i:341;}i:390;a:2:{s:5:\"class\";s:18:\"ilobjuserfoldergui\";s:6:\"parent\";i:1;}i:391;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:390;}i:392;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:391;}i:393;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:392;}i:394;a:2:{s:5:\"class\";s:12:\"ilobjusergui\";s:6:\"parent\";i:1;}i:395;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:394;}i:396;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:395;}i:397;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:396;}i:398;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:397;}i:399;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:395;}i:400;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:399;}i:401;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:399;}i:402;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:401;}i:403;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:399;}i:404;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:403;}i:405;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:395;}i:406;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:405;}i:407;a:2:{s:5:\"class\";s:20:\"ilobjusertrackinggui\";s:6:\"parent\";i:1;}i:408;a:2:{s:5:\"class\";s:21:\"illearningprogressgui\";s:6:\"parent\";i:407;}i:409;a:2:{s:5:\"class\";s:20:\"illplistofobjectsgui\";s:6:\"parent\";i:408;}i:410;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:409;}i:411;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:410;}i:412;a:2:{s:5:\"class\";s:21:\"illplistofprogressgui\";s:6:\"parent\";i:408;}i:413;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:412;}i:414;a:2:{s:5:\"class\";s:13:\"illpfiltergui\";s:6:\"parent\";i:412;}i:415;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:414;}i:416;a:2:{s:5:\"class\";s:17:\"ilpdfpresentation\";s:6:\"parent\";i:412;}i:417;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:416;}i:418;a:2:{s:5:\"class\";s:21:\"illplistofsettingsgui\";s:6:\"parent\";i:408;}i:419;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:418;}i:420;a:2:{s:5:\"class\";s:15:\"ilpermissiongui\";s:6:\"parent\";i:407;}i:421;a:2:{s:5:\"class\";s:12:\"ilobjrolegui\";s:6:\"parent\";i:420;}i:422;a:2:{s:5:\"class\";s:0:\"\";s:6:\"parent\";i:421;}}','a:60:{s:0:\"\";a:211:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";i:10;s:0:\"\";i:11;s:0:\"\";i:12;s:0:\"\";i:13;s:0:\"\";i:14;s:0:\"\";i:15;s:0:\"\";i:16;s:0:\"\";i:17;s:0:\"\";i:18;s:0:\"\";i:19;s:0:\"\";i:20;s:0:\"\";i:21;s:0:\"\";i:22;s:0:\"\";i:23;s:0:\"\";i:24;s:0:\"\";i:25;s:0:\"\";i:26;s:0:\"\";i:27;s:0:\"\";i:28;s:0:\"\";i:29;s:0:\"\";i:30;s:0:\"\";i:31;s:0:\"\";i:32;s:0:\"\";i:33;s:0:\"\";i:34;s:0:\"\";i:35;s:0:\"\";i:36;s:0:\"\";i:37;s:0:\"\";i:38;s:0:\"\";i:39;s:0:\"\";i:40;s:0:\"\";i:41;s:0:\"\";i:42;s:0:\"\";i:43;s:0:\"\";i:44;s:0:\"\";i:45;s:0:\"\";i:46;s:0:\"\";i:47;s:0:\"\";i:48;s:0:\"\";i:49;s:0:\"\";i:50;s:0:\"\";i:51;s:0:\"\";i:52;s:0:\"\";i:53;s:0:\"\";i:54;s:0:\"\";i:55;s:0:\"\";i:56;s:0:\"\";i:57;s:0:\"\";i:58;s:0:\"\";i:59;s:0:\"\";i:60;s:0:\"\";i:61;s:0:\"\";i:62;s:0:\"\";i:63;s:0:\"\";i:64;s:0:\"\";i:65;s:0:\"\";i:66;s:0:\"\";i:67;s:0:\"\";i:68;s:0:\"\";i:69;s:0:\"\";i:70;s:0:\"\";i:71;s:0:\"\";i:72;s:0:\"\";i:73;s:0:\"\";i:74;s:0:\"\";i:75;s:0:\"\";i:76;s:0:\"\";i:77;s:0:\"\";i:78;s:0:\"\";i:79;s:0:\"\";i:80;s:0:\"\";i:81;s:0:\"\";i:82;s:0:\"\";i:83;s:0:\"\";i:84;s:0:\"\";i:85;s:0:\"\";i:86;s:0:\"\";i:87;s:0:\"\";i:88;s:0:\"\";i:89;s:0:\"\";i:90;s:0:\"\";i:91;s:0:\"\";i:92;s:0:\"\";i:93;s:0:\"\";i:94;s:0:\"\";i:95;s:0:\"\";i:96;s:0:\"\";i:97;s:0:\"\";i:98;s:0:\"\";i:99;s:0:\"\";i:100;s:0:\"\";i:101;s:0:\"\";i:102;s:0:\"\";i:103;s:0:\"\";i:104;s:0:\"\";i:105;s:0:\"\";i:106;s:0:\"\";i:107;s:0:\"\";i:108;s:0:\"\";i:109;s:0:\"\";i:110;s:0:\"\";i:111;s:0:\"\";i:112;s:0:\"\";i:113;s:0:\"\";i:114;s:0:\"\";i:115;s:0:\"\";i:116;s:0:\"\";i:117;s:0:\"\";i:118;s:0:\"\";i:119;s:0:\"\";i:120;s:0:\"\";i:121;s:0:\"\";i:122;s:0:\"\";i:123;s:0:\"\";i:124;s:0:\"\";i:125;s:0:\"\";i:126;s:0:\"\";i:127;s:0:\"\";i:128;s:0:\"\";i:129;s:0:\"\";i:130;s:0:\"\";i:131;s:0:\"\";i:132;s:0:\"\";i:133;s:0:\"\";i:134;s:0:\"\";i:135;s:0:\"\";i:136;s:0:\"\";i:137;s:0:\"\";i:138;s:0:\"\";i:139;s:0:\"\";i:140;s:0:\"\";i:141;s:0:\"\";i:142;s:0:\"\";i:143;s:0:\"\";i:144;s:0:\"\";i:145;s:0:\"\";i:146;s:0:\"\";i:147;s:0:\"\";i:148;s:0:\"\";i:149;s:0:\"\";i:150;s:0:\"\";i:151;s:0:\"\";i:152;s:0:\"\";i:153;s:0:\"\";i:154;s:0:\"\";i:155;s:0:\"\";i:156;s:0:\"\";i:157;s:0:\"\";i:158;s:0:\"\";i:159;s:0:\"\";i:160;s:0:\"\";i:161;s:0:\"\";i:162;s:0:\"\";i:163;s:0:\"\";i:164;s:0:\"\";i:165;s:0:\"\";i:166;s:0:\"\";i:167;s:0:\"\";i:168;s:0:\"\";i:169;s:0:\"\";i:170;s:0:\"\";i:171;s:0:\"\";i:172;s:0:\"\";i:173;s:0:\"\";i:174;s:0:\"\";i:175;s:0:\"\";i:176;s:0:\"\";i:177;s:0:\"\";i:178;s:0:\"\";i:179;s:0:\"\";i:180;s:0:\"\";i:181;s:0:\"\";i:182;s:0:\"\";i:183;s:0:\"\";i:184;s:0:\"\";i:185;s:0:\"\";i:186;s:0:\"\";i:187;s:0:\"\";i:188;s:0:\"\";i:189;s:0:\"\";i:190;s:0:\"\";i:191;s:0:\"\";i:192;s:0:\"\";i:193;s:0:\"\";i:194;s:0:\"\";i:195;s:0:\"\";i:196;s:0:\"\";i:197;s:0:\"\";i:198;s:0:\"\";i:199;s:0:\"\";i:200;s:0:\"\";i:201;s:0:\"\";i:202;s:0:\"\";i:203;s:0:\"\";i:204;s:0:\"\";i:205;s:0:\"\";i:206;s:0:\"\";i:207;s:0:\"\";i:208;s:0:\"\";i:209;s:0:\"\";i:210;s:0:\"\";}s:12:\"ilobjrolegui\";a:39:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";i:10;s:0:\"\";i:11;s:0:\"\";i:12;s:0:\"\";i:13;s:0:\"\";i:14;s:0:\"\";i:15;s:0:\"\";i:16;s:0:\"\";i:17;s:0:\"\";i:18;s:0:\"\";i:19;s:0:\"\";i:20;s:0:\"\";i:21;s:0:\"\";i:22;s:0:\"\";i:23;s:0:\"\";i:24;s:0:\"\";i:25;s:0:\"\";i:26;s:0:\"\";i:27;s:0:\"\";i:28;s:0:\"\";i:29;s:0:\"\";i:30;s:0:\"\";i:31;s:0:\"\";i:32;s:0:\"\";i:33;s:0:\"\";i:34;s:0:\"\";i:35;s:0:\"\";i:36;s:0:\"\";i:37;s:0:\"\";i:38;s:0:\"\";}s:15:\"ilpermissiongui\";a:38:{i:0;s:12:\"ilobjrolegui\";i:1;s:12:\"ilobjrolegui\";i:2;s:12:\"ilobjrolegui\";i:3;s:12:\"ilobjrolegui\";i:4;s:12:\"ilobjrolegui\";i:5;s:12:\"ilobjrolegui\";i:6;s:12:\"ilobjrolegui\";i:7;s:12:\"ilobjrolegui\";i:8;s:12:\"ilobjrolegui\";i:9;s:12:\"ilobjrolegui\";i:10;s:12:\"ilobjrolegui\";i:11;s:12:\"ilobjrolegui\";i:12;s:12:\"ilobjrolegui\";i:13;s:12:\"ilobjrolegui\";i:14;s:12:\"ilobjrolegui\";i:15;s:12:\"ilobjrolegui\";i:16;s:12:\"ilobjrolegui\";i:17;s:12:\"ilobjrolegui\";i:18;s:12:\"ilobjrolegui\";i:19;s:12:\"ilobjrolegui\";i:20;s:12:\"ilobjrolegui\";i:21;s:12:\"ilobjrolegui\";i:22;s:12:\"ilobjrolegui\";i:23;s:12:\"ilobjrolegui\";i:24;s:12:\"ilobjrolegui\";i:25;s:12:\"ilobjrolegui\";i:26;s:12:\"ilobjrolegui\";i:27;s:12:\"ilobjrolegui\";i:28;s:12:\"ilobjrolegui\";i:29;s:12:\"ilobjrolegui\";i:30;s:12:\"ilobjrolegui\";i:31;s:12:\"ilobjrolegui\";i:32;s:12:\"ilobjrolegui\";i:33;s:12:\"ilobjrolegui\";i:34;s:12:\"ilobjrolegui\";i:35;s:12:\"ilobjrolegui\";i:36;s:12:\"ilobjrolegui\";i:37;s:12:\"ilobjrolegui\";}s:24:\"ilobjassessmentfoldergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:20:\"ilobjauthsettingsgui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:16:\"ilobjcategorygui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:12:\"ilobjchatgui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:18:\"ilobjchatservergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:13:\"ilfeedbackgui\";a:5:{i:0;s:1:\"\"\";i:1;s:1:\"\"\";i:2;s:1:\"\"\";i:3;s:1:\"\"\";i:4;s:1:\"\"\";}s:15:\"ilinfoscreengui\";a:10:{i:0;s:13:\"ilfeedbackgui\";i:1;s:9:\"ilnotegui\";i:2;s:13:\"ilfeedbackgui\";i:3;s:9:\"ilnotegui\";i:4;s:13:\"ilfeedbackgui\";i:5;s:9:\"ilnotegui\";i:6;s:13:\"ilfeedbackgui\";i:7;s:9:\"ilnotegui\";i:8;s:13:\"ilfeedbackgui\";i:9;s:9:\"ilnotegui\";}s:13:\"illpfiltergui\";a:14:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";i:10;s:0:\"\";i:11;s:0:\"\";i:12;s:0:\"\";i:13;s:0:\"\";}s:20:\"illplistofobjectsgui\";a:7:{i:0;s:13:\"illpfiltergui\";i:1;s:13:\"illpfiltergui\";i:2;s:13:\"illpfiltergui\";i:3;s:13:\"illpfiltergui\";i:4;s:13:\"illpfiltergui\";i:5;s:13:\"illpfiltergui\";i:6;s:13:\"illpfiltergui\";}s:17:\"ilpdfpresentation\";a:7:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";}s:21:\"illplistofprogressgui\";a:21:{i:0;s:0:\"\";i:1;s:13:\"illpfiltergui\";i:2;s:17:\"ilpdfpresentation\";i:3;s:0:\"\";i:4;s:13:\"illpfiltergui\";i:5;s:17:\"ilpdfpresentation\";i:6;s:0:\"\";i:7;s:13:\"illpfiltergui\";i:8;s:17:\"ilpdfpresentation\";i:9;s:0:\"\";i:10;s:13:\"illpfiltergui\";i:11;s:17:\"ilpdfpresentation\";i:12;s:0:\"\";i:13;s:13:\"illpfiltergui\";i:14;s:17:\"ilpdfpresentation\";i:15;s:0:\"\";i:16;s:13:\"illpfiltergui\";i:17;s:17:\"ilpdfpresentation\";i:18;s:0:\"\";i:19;s:13:\"illpfiltergui\";i:20;s:17:\"ilpdfpresentation\";}s:21:\"illplistofsettingsgui\";a:7:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";}s:21:\"illearningprogressgui\";a:21:{i:0;s:20:\"illplistofobjectsgui\";i:1;s:21:\"illplistofprogressgui\";i:2;s:21:\"illplistofsettingsgui\";i:3;s:20:\"illplistofobjectsgui\";i:4;s:21:\"illplistofprogressgui\";i:5;s:21:\"illplistofsettingsgui\";i:6;s:20:\"illplistofobjectsgui\";i:7;s:21:\"illplistofprogressgui\";i:8;s:21:\"illplistofsettingsgui\";i:9;s:20:\"illplistofobjectsgui\";i:10;s:21:\"illplistofprogressgui\";i:11;s:21:\"illplistofsettingsgui\";i:12;s:20:\"illplistofobjectsgui\";i:13;s:21:\"illplistofprogressgui\";i:14;s:21:\"illplistofsettingsgui\";i:15;s:20:\"illplistofobjectsgui\";i:16;s:21:\"illplistofprogressgui\";i:17;s:21:\"illplistofsettingsgui\";i:18;s:20:\"illplistofobjectsgui\";i:19;s:21:\"illplistofprogressgui\";i:20;s:21:\"illplistofsettingsgui\";}s:14:\"ilobjcoursegui\";a:18:{i:0;s:27:\"ilconditionhandlerinterface\";i:1;s:21:\"ilcourseobjectivesgui\";i:2;s:19:\"ilcourseregistergui\";i:3;s:15:\"ilinfoscreengui\";i:4;s:21:\"illearningprogressgui\";i:5;s:13:\"ilmdeditorgui\";i:6;s:22:\"ilobjcoursegroupinggui\";i:7;s:20:\"ilpaymentpurchasegui\";i:8;s:15:\"ilpermissiongui\";i:9;s:27:\"ilconditionhandlerinterface\";i:10;s:21:\"ilcourseobjectivesgui\";i:11;s:19:\"ilcourseregistergui\";i:12;s:15:\"ilinfoscreengui\";i:13;s:21:\"illearningprogressgui\";i:14;s:13:\"ilmdeditorgui\";i:15;s:22:\"ilobjcoursegroupinggui\";i:16;s:20:\"ilpaymentpurchasegui\";i:17;s:15:\"ilpermissiongui\";}s:19:\"ilobjmediaobjectgui\";a:20:{i:0;s:17:\"ilinternallinkgui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:17:\"ilinternallinkgui\";i:3;s:13:\"ilmdeditorgui\";i:4;s:17:\"ilinternallinkgui\";i:5;s:13:\"ilmdeditorgui\";i:6;s:17:\"ilinternallinkgui\";i:7;s:13:\"ilmdeditorgui\";i:8;s:17:\"ilinternallinkgui\";i:9;s:13:\"ilmdeditorgui\";i:10;s:17:\"ilinternallinkgui\";i:11;s:13:\"ilmdeditorgui\";i:12;s:17:\"ilinternallinkgui\";i:13;s:13:\"ilmdeditorgui\";i:14;s:17:\"ilinternallinkgui\";i:15;s:13:\"ilmdeditorgui\";i:16;s:17:\"ilinternallinkgui\";i:17;s:13:\"ilmdeditorgui\";i:18;s:17:\"ilinternallinkgui\";i:19;s:13:\"ilmdeditorgui\";}s:18:\"ileditclipboardgui\";a:5:{i:0;s:19:\"ilobjmediaobjectgui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:19:\"ilobjmediaobjectgui\";i:3;s:19:\"ilobjmediaobjectgui\";i:4;s:19:\"ilobjmediaobjectgui\";}s:15:\"ilpageeditorgui\";a:48:{i:0;s:17:\"ilinternallinkgui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:15:\"ilpcfileitemgui\";i:3;s:15:\"ilpcfilelistgui\";i:4;s:11:\"ilpclistgui\";i:5;s:15:\"ilpclistitemgui\";i:6;s:18:\"ilpcmediaobjectgui\";i:7;s:16:\"ilpcparagraphgui\";i:8;s:15:\"ilpcquestiongui\";i:9;s:17:\"ilpcsourcecodegui\";i:10;s:16:\"ilpctabledatagui\";i:11;s:12:\"ilpctablegui\";i:12;s:17:\"ilinternallinkgui\";i:13;s:19:\"ilobjmediaobjectgui\";i:14;s:15:\"ilpcfileitemgui\";i:15;s:15:\"ilpcfilelistgui\";i:16;s:11:\"ilpclistgui\";i:17;s:15:\"ilpclistitemgui\";i:18;s:18:\"ilpcmediaobjectgui\";i:19;s:16:\"ilpcparagraphgui\";i:20;s:15:\"ilpcquestiongui\";i:21;s:17:\"ilpcsourcecodegui\";i:22;s:16:\"ilpctabledatagui\";i:23;s:12:\"ilpctablegui\";i:24;s:17:\"ilinternallinkgui\";i:25;s:19:\"ilobjmediaobjectgui\";i:26;s:15:\"ilpcfileitemgui\";i:27;s:15:\"ilpcfilelistgui\";i:28;s:11:\"ilpclistgui\";i:29;s:15:\"ilpclistitemgui\";i:30;s:18:\"ilpcmediaobjectgui\";i:31;s:16:\"ilpcparagraphgui\";i:32;s:15:\"ilpcquestiongui\";i:33;s:17:\"ilpcsourcecodegui\";i:34;s:16:\"ilpctabledatagui\";i:35;s:12:\"ilpctablegui\";i:36;s:17:\"ilinternallinkgui\";i:37;s:19:\"ilobjmediaobjectgui\";i:38;s:15:\"ilpcfileitemgui\";i:39;s:15:\"ilpcfilelistgui\";i:40;s:11:\"ilpclistgui\";i:41;s:15:\"ilpclistitemgui\";i:42;s:18:\"ilpcmediaobjectgui\";i:43;s:16:\"ilpcparagraphgui\";i:44;s:15:\"ilpcquestiongui\";i:45;s:17:\"ilpcsourcecodegui\";i:46;s:16:\"ilpctabledatagui\";i:47;s:12:\"ilpctablegui\";}s:15:\"ilpageobjectgui\";a:12:{i:0;s:18:\"ileditclipboardgui\";i:1;s:25:\"ilmediapooltargetselector\";i:2;s:15:\"ilpageeditorgui\";i:3;s:18:\"ileditclipboardgui\";i:4;s:25:\"ilmediapooltargetselector\";i:5;s:15:\"ilpageeditorgui\";i:6;s:18:\"ileditclipboardgui\";i:7;s:25:\"ilmediapooltargetselector\";i:8;s:15:\"ilpageeditorgui\";i:9;s:18:\"ileditclipboardgui\";i:10;s:25:\"ilmediapooltargetselector\";i:11;s:15:\"ilpageeditorgui\";}s:17:\"illmpageobjectgui\";a:4:{i:0;s:13:\"ilmdeditorgui\";i:1;s:15:\"ilpageobjectgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:15:\"ilpageobjectgui\";}s:18:\"ilobjstylesheetgui\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:20:\"ilstructureobjectgui\";a:4:{i:0;s:27:\"ilconditionhandlerinterface\";i:1;s:13:\"ilmdeditorgui\";i:2;s:27:\"ilconditionhandlerinterface\";i:3;s:13:\"ilmdeditorgui\";}s:14:\"ilobjdlbookgui\";a:6:{i:0;s:21:\"illearningprogressgui\";i:1;s:17:\"illmpageobjectgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:18:\"ilobjstylesheetgui\";i:4;s:15:\"ilpermissiongui\";i:5;s:20:\"ilstructureobjectgui\";}s:16:\"ilobjexercisegui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:29:\"ilobjexternaltoolssettingsgui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:19:\"ilobjfilebasedlmgui\";a:3:{i:0;s:15:\"ilfilesystemgui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:15:\"ilpermissiongui\";}s:12:\"ilobjfilegui\";a:3:{i:0;s:15:\"ilinfoscreengui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:15:\"ilpermissiongui\";}s:14:\"ilobjfoldergui\";a:4:{i:0;s:27:\"ilconditionhandlerinterface\";i:1;s:15:\"ilpermissiongui\";i:2;s:27:\"ilconditionhandlerinterface\";i:3;s:15:\"ilpermissiongui\";}s:13:\"ilobjforumgui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:25:\"iltermdefinitioneditorgui\";a:2:{i:0;s:13:\"ilmdeditorgui\";i:1;s:15:\"ilpageobjectgui\";}s:17:\"ilglossarytermgui\";a:1:{i:0;s:25:\"iltermdefinitioneditorgui\";}s:16:\"ilobjglossarygui\";a:3:{i:0;s:17:\"ilglossarytermgui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:15:\"ilpermissiongui\";}s:13:\"ilobjgroupgui\";a:3:{i:0;s:27:\"ilconditionhandlerinterface\";i:1;s:15:\"ilpermissiongui\";i:2;s:13:\"ilregistergui\";}s:22:\"ilobjilincclassroomgui\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:19:\"ilobjilinccoursegui\";a:2:{i:0;s:22:\"ilobjilincclassroomgui\";i:1;s:15:\"ilpermissiongui\";}s:22:\"ilobjlanguagefoldergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:22:\"ilobjlearningmodulegui\";a:6:{i:0;s:21:\"illearningprogressgui\";i:1;s:17:\"illmpageobjectgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:18:\"ilobjstylesheetgui\";i:4;s:15:\"ilpermissiongui\";i:5;s:20:\"ilstructureobjectgui\";}s:20:\"ilobjlinkresourcegui\";a:3:{i:0;s:15:\"ilinfoscreengui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:15:\"ilpermissiongui\";}s:12:\"ilobjmailgui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:17:\"ilobjmediapoolgui\";a:4:{i:0;s:18:\"ileditclipboardgui\";i:1;s:14:\"ilobjfoldergui\";i:2;s:19:\"ilobjmediaobjectgui\";i:3;s:15:\"ilpermissiongui\";}s:20:\"ilobjobjectfoldergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:23:\"ilobjpaymentsettingsgui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:20:\"ilobjquestionpoolgui\";a:10:{i:0;s:16:\"ass_clozetestgui\";i:1;s:23:\"ass_imagemapquestiongui\";i:2;s:17:\"ass_javaappletgui\";i:3;s:23:\"ass_matchingquestiongui\";i:4;s:21:\"ass_multiplechoicegui\";i:5;s:23:\"ass_orderingquestiongui\";i:6;s:19:\"ass_textquestiongui\";i:7;s:13:\"ilmdeditorgui\";i:8;s:15:\"ilpageobjectgui\";i:9;s:15:\"ilpermissiongui\";}s:22:\"ilobjrecoveryfoldergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:18:\"ilobjrolefoldergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:20:\"ilobjroletemplategui\";a:1:{i:0;s:0:\"\";}s:18:\"ilobjrootfoldergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:26:\"ilobjsahslearningmodulegui\";a:3:{i:0;s:15:\"ilfilesystemgui\";i:1;s:13:\"ilmdeditorgui\";i:2;s:15:\"ilpermissiongui\";}s:22:\"ilobjsearchsettingsgui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:21:\"ilobjstylesettingsgui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:14:\"ilobjsurveygui\";a:4:{i:0;s:13:\"ilmdeditorgui\";i:1;s:15:\"ilpermissiongui\";i:2;s:21:\"ilsurveyevaluationgui\";i:3;s:20:\"ilsurveyexecutiongui\";}s:26:\"ilobjsurveyquestionpoolgui\";a:6:{i:0;s:13:\"ilmdeditorgui\";i:1;s:15:\"ilpermissiongui\";i:2;s:23:\"surveymetricquestiongui\";i:3;s:24:\"surveynominalquestiongui\";i:4;s:24:\"surveyordinalquestiongui\";i:5;s:21:\"surveytextquestiongui\";}s:20:\"ilobjsystemfoldergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:12:\"ilobjtestgui\";a:7:{i:0;s:15:\"ilinfoscreengui\";i:1;s:21:\"illearningprogressgui\";i:2;s:13:\"ilmdeditorgui\";i:3;s:14:\"ilobjcoursegui\";i:4;s:15:\"ilpermissiongui\";i:5;s:19:\"iltestevaluationgui\";i:6;s:15:\"iltestoutputgui\";}s:18:\"ilobjuserfoldergui\";a:1:{i:0;s:15:\"ilpermissiongui\";}s:12:\"ilobjusergui\";a:1:{i:0;s:21:\"illearningprogressgui\";}s:20:\"ilobjusertrackinggui\";a:2:{i:0;s:21:\"illearningprogressgui\";i:1;s:15:\"ilpermissiongui\";}s:19:\"iladministrationgui\";a:43:{i:0;s:21:\"ilcourseobjectivesgui\";i:1;s:24:\"ilobjassessmentfoldergui\";i:2;s:20:\"ilobjauthsettingsgui\";i:3;s:16:\"ilobjcategorygui\";i:4;s:12:\"ilobjchatgui\";i:5;s:18:\"ilobjchatservergui\";i:6;s:14:\"ilobjcoursegui\";i:7;s:14:\"ilobjdlbookgui\";i:8;s:16:\"ilobjexercisegui\";i:9;s:29:\"ilobjexternaltoolssettingsgui\";i:10;s:19:\"ilobjfilebasedlmgui\";i:11;s:12:\"ilobjfilegui\";i:12;s:14:\"ilobjfoldergui\";i:13;s:13:\"ilobjforumgui\";i:14;s:16:\"ilobjglossarygui\";i:15;s:13:\"ilobjgroupgui\";i:16;s:22:\"ilobjilincclassroomgui\";i:17;s:19:\"ilobjilinccoursegui\";i:18;s:22:\"ilobjlanguagefoldergui\";i:19;s:22:\"ilobjlearningmodulegui\";i:20;s:20:\"ilobjlinkresourcegui\";i:21;s:12:\"ilobjmailgui\";i:22;s:17:\"ilobjmediapoolgui\";i:23;s:20:\"ilobjobjectfoldergui\";i:24;s:23:\"ilobjpaymentsettingsgui\";i:25;s:20:\"ilobjquestionpoolgui\";i:26;s:22:\"ilobjrecoveryfoldergui\";i:27;s:18:\"ilobjrolefoldergui\";i:28;s:12:\"ilobjrolegui\";i:29;s:20:\"ilobjroletemplategui\";i:30;s:18:\"ilobjrootfoldergui\";i:31;s:26:\"ilobjsahslearningmodulegui\";i:32;s:22:\"ilobjsearchsettingsgui\";i:33;s:21:\"ilobjstylesettingsgui\";i:34;s:18:\"ilobjstylesheetgui\";i:35;s:14:\"ilobjsurveygui\";i:36;s:26:\"ilobjsurveyquestionpoolgui\";i:37;s:20:\"ilobjsystemfoldergui\";i:38;s:22:\"ilobjtaxonomyfoldergui\";i:39;s:12:\"ilobjtestgui\";i:40;s:18:\"ilobjuserfoldergui\";i:41;s:12:\"ilobjusergui\";i:42;s:20:\"ilobjusertrackinggui\";}}','a:97:{s:0:\"\";a:291:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:12:\"ilobjrolegui\";i:3;s:0:\"\";i:4;s:12:\"ilobjrolegui\";i:5;s:0:\"\";i:6;s:12:\"ilobjrolegui\";i:7;s:0:\"\";i:8;s:12:\"ilobjrolegui\";i:9;s:0:\"\";i:10;s:12:\"ilobjrolegui\";i:11;s:0:\"\";i:12;s:0:\"\";i:13;s:0:\"\";i:14;s:0:\"\";i:15;s:0:\"\";i:16;s:0:\"\";i:17;s:13:\"illpfiltergui\";i:18;s:0:\"\";i:19;s:0:\"\";i:20;s:13:\"illpfiltergui\";i:21;s:0:\"\";i:22;s:17:\"ilpdfpresentation\";i:23;s:21:\"illplistofprogressgui\";i:24;s:0:\"\";i:25;s:21:\"illplistofsettingsgui\";i:26;s:0:\"\";i:27;s:0:\"\";i:28;s:0:\"\";i:29;s:0:\"\";i:30;s:12:\"ilobjrolegui\";i:31;s:0:\"\";i:32;s:13:\"illpfiltergui\";i:33;s:0:\"\";i:34;s:0:\"\";i:35;s:13:\"illpfiltergui\";i:36;s:0:\"\";i:37;s:17:\"ilpdfpresentation\";i:38;s:21:\"illplistofprogressgui\";i:39;s:0:\"\";i:40;s:21:\"illplistofsettingsgui\";i:41;s:0:\"\";i:42;s:0:\"\";i:43;s:0:\"\";i:44;s:0:\"\";i:45;s:0:\"\";i:46;s:0:\"\";i:47;s:0:\"\";i:48;s:0:\"\";i:49;s:0:\"\";i:50;s:0:\"\";i:51;s:0:\"\";i:52;s:0:\"\";i:53;s:0:\"\";i:54;s:0:\"\";i:55;s:0:\"\";i:56;s:0:\"\";i:57;s:0:\"\";i:58;s:0:\"\";i:59;s:0:\"\";i:60;s:18:\"ilobjstylesheetgui\";i:61;s:0:\"\";i:62;s:12:\"ilobjrolegui\";i:63;s:0:\"\";i:64;s:0:\"\";i:65;s:0:\"\";i:66;s:12:\"ilobjrolegui\";i:67;s:0:\"\";i:68;s:12:\"ilobjrolegui\";i:69;s:0:\"\";i:70;s:0:\"\";i:71;s:0:\"\";i:72;s:12:\"ilobjrolegui\";i:73;s:0:\"\";i:74;s:0:\"\";i:75;s:0:\"\";i:76;s:0:\"\";i:77;s:12:\"ilobjrolegui\";i:78;s:0:\"\";i:79;s:0:\"\";i:80;s:12:\"ilobjrolegui\";i:81;s:0:\"\";i:82;s:12:\"ilobjrolegui\";i:83;s:0:\"\";i:84;s:0:\"\";i:85;s:0:\"\";i:86;s:0:\"\";i:87;s:0:\"\";i:88;s:0:\"\";i:89;s:0:\"\";i:90;s:0:\"\";i:91;s:0:\"\";i:92;s:0:\"\";i:93;s:0:\"\";i:94;s:0:\"\";i:95;s:0:\"\";i:96;s:0:\"\";i:97;s:0:\"\";i:98;s:0:\"\";i:99;s:0:\"\";i:100;s:0:\"\";i:101;s:0:\"\";i:102;s:12:\"ilobjrolegui\";i:103;s:0:\"\";i:104;s:0:\"\";i:105;s:12:\"ilobjrolegui\";i:106;s:0:\"\";i:107;s:0:\"\";i:108;s:22:\"ilobjilincclassroomgui\";i:109;s:0:\"\";i:110;s:22:\"ilobjilincclassroomgui\";i:111;s:0:\"\";i:112;s:12:\"ilobjrolegui\";i:113;s:0:\"\";i:114;s:12:\"ilobjrolegui\";i:115;s:0:\"\";i:116;s:13:\"illpfiltergui\";i:117;s:0:\"\";i:118;s:0:\"\";i:119;s:13:\"illpfiltergui\";i:120;s:0:\"\";i:121;s:17:\"ilpdfpresentation\";i:122;s:21:\"illplistofprogressgui\";i:123;s:0:\"\";i:124;s:21:\"illplistofsettingsgui\";i:125;s:0:\"\";i:126;s:0:\"\";i:127;s:0:\"\";i:128;s:0:\"\";i:129;s:0:\"\";i:130;s:0:\"\";i:131;s:0:\"\";i:132;s:0:\"\";i:133;s:0:\"\";i:134;s:0:\"\";i:135;s:0:\"\";i:136;s:0:\"\";i:137;s:0:\"\";i:138;s:0:\"\";i:139;s:0:\"\";i:140;s:0:\"\";i:141;s:0:\"\";i:142;s:0:\"\";i:143;s:0:\"\";i:144;s:18:\"ilobjstylesheetgui\";i:145;s:0:\"\";i:146;s:12:\"ilobjrolegui\";i:147;s:0:\"\";i:148;s:0:\"\";i:149;s:0:\"\";i:150;s:0:\"\";i:151;s:0:\"\";i:152;s:0:\"\";i:153;s:12:\"ilobjrolegui\";i:154;s:0:\"\";i:155;s:12:\"ilobjrolegui\";i:156;s:0:\"\";i:157;s:0:\"\";i:158;s:0:\"\";i:159;s:0:\"\";i:160;s:12:\"ilobjrolegui\";i:161;s:0:\"\";i:162;s:0:\"\";i:163;s:0:\"\";i:164;s:12:\"ilobjrolegui\";i:165;s:0:\"\";i:166;s:12:\"ilobjrolegui\";i:167;s:0:\"\";i:168;s:12:\"ilobjrolegui\";i:169;s:0:\"\";i:170;s:0:\"\";i:171;s:0:\"\";i:172;s:0:\"\";i:173;s:0:\"\";i:174;s:0:\"\";i:175;s:0:\"\";i:176;s:0:\"\";i:177;s:0:\"\";i:178;s:0:\"\";i:179;s:0:\"\";i:180;s:0:\"\";i:181;s:0:\"\";i:182;s:0:\"\";i:183;s:0:\"\";i:184;s:0:\"\";i:185;s:0:\"\";i:186;s:0:\"\";i:187;s:0:\"\";i:188;s:0:\"\";i:189;s:0:\"\";i:190;s:0:\"\";i:191;s:0:\"\";i:192;s:0:\"\";i:193;s:0:\"\";i:194;s:12:\"ilobjrolegui\";i:195;s:0:\"\";i:196;s:12:\"ilobjrolegui\";i:197;s:0:\"\";i:198;s:12:\"ilobjrolegui\";i:199;s:0:\"\";i:200;s:12:\"ilobjrolegui\";i:201;s:0:\"\";i:202;s:20:\"ilobjroletemplategui\";i:203;s:0:\"\";i:204;s:12:\"ilobjrolegui\";i:205;s:0:\"\";i:206;s:0:\"\";i:207;s:0:\"\";i:208;s:12:\"ilobjrolegui\";i:209;s:0:\"\";i:210;s:12:\"ilobjrolegui\";i:211;s:0:\"\";i:212;s:12:\"ilobjrolegui\";i:213;s:0:\"\";i:214;s:18:\"ilobjstylesheetgui\";i:215;s:0:\"\";i:216;s:0:\"\";i:217;s:12:\"ilobjrolegui\";i:218;s:0:\"\";i:219;s:0:\"\";i:220;s:0:\"\";i:221;s:0:\"\";i:222;s:12:\"ilobjrolegui\";i:223;s:0:\"\";i:224;s:0:\"\";i:225;s:0:\"\";i:226;s:0:\"\";i:227;s:0:\"\";i:228;s:12:\"ilobjrolegui\";i:229;s:0:\"\";i:230;s:0:\"\";i:231;s:0:\"\";i:232;s:0:\"\";i:233;s:13:\"illpfiltergui\";i:234;s:0:\"\";i:235;s:0:\"\";i:236;s:13:\"illpfiltergui\";i:237;s:0:\"\";i:238;s:17:\"ilpdfpresentation\";i:239;s:21:\"illplistofprogressgui\";i:240;s:0:\"\";i:241;s:21:\"illplistofsettingsgui\";i:242;s:0:\"\";i:243;s:0:\"\";i:244;s:0:\"\";i:245;s:0:\"\";i:246;s:0:\"\";i:247;s:0:\"\";i:248;s:0:\"\";i:249;s:13:\"illpfiltergui\";i:250;s:0:\"\";i:251;s:0:\"\";i:252;s:13:\"illpfiltergui\";i:253;s:0:\"\";i:254;s:17:\"ilpdfpresentation\";i:255;s:21:\"illplistofprogressgui\";i:256;s:0:\"\";i:257;s:21:\"illplistofsettingsgui\";i:258;s:0:\"\";i:259;s:0:\"\";i:260;s:0:\"\";i:261;s:0:\"\";i:262;s:12:\"ilobjrolegui\";i:263;s:0:\"\";i:264;s:12:\"ilobjrolegui\";i:265;s:0:\"\";i:266;s:0:\"\";i:267;s:0:\"\";i:268;s:12:\"ilobjrolegui\";i:269;s:0:\"\";i:270;s:13:\"illpfiltergui\";i:271;s:0:\"\";i:272;s:0:\"\";i:273;s:13:\"illpfiltergui\";i:274;s:0:\"\";i:275;s:17:\"ilpdfpresentation\";i:276;s:21:\"illplistofprogressgui\";i:277;s:0:\"\";i:278;s:21:\"illplistofsettingsgui\";i:279;s:0:\"\";i:280;s:13:\"illpfiltergui\";i:281;s:0:\"\";i:282;s:0:\"\";i:283;s:13:\"illpfiltergui\";i:284;s:0:\"\";i:285;s:17:\"ilpdfpresentation\";i:286;s:21:\"illplistofprogressgui\";i:287;s:0:\"\";i:288;s:21:\"illplistofsettingsgui\";i:289;s:0:\"\";i:290;s:12:\"ilobjrolegui\";}s:12:\"ilobjrolegui\";a:39:{i:0;s:15:\"ilpermissiongui\";i:1;s:15:\"ilpermissiongui\";i:2;s:15:\"ilpermissiongui\";i:3;s:15:\"ilpermissiongui\";i:4;s:15:\"ilpermissiongui\";i:5;s:15:\"ilpermissiongui\";i:6;s:15:\"ilpermissiongui\";i:7;s:15:\"ilpermissiongui\";i:8;s:15:\"ilpermissiongui\";i:9;s:15:\"ilpermissiongui\";i:10;s:15:\"ilpermissiongui\";i:11;s:15:\"ilpermissiongui\";i:12;s:15:\"ilpermissiongui\";i:13;s:15:\"ilpermissiongui\";i:14;s:15:\"ilpermissiongui\";i:15;s:15:\"ilpermissiongui\";i:16;s:15:\"ilpermissiongui\";i:17;s:15:\"ilpermissiongui\";i:18;s:15:\"ilpermissiongui\";i:19;s:15:\"ilpermissiongui\";i:20;s:15:\"ilpermissiongui\";i:21;s:15:\"ilpermissiongui\";i:22;s:15:\"ilpermissiongui\";i:23;s:15:\"ilpermissiongui\";i:24;s:15:\"ilpermissiongui\";i:25;s:15:\"ilpermissiongui\";i:26;s:15:\"ilpermissiongui\";i:27;s:15:\"ilpermissiongui\";i:28;s:15:\"ilpermissiongui\";i:29;s:15:\"ilpermissiongui\";i:30;s:15:\"ilpermissiongui\";i:31;s:15:\"ilpermissiongui\";i:32;s:15:\"ilpermissiongui\";i:33;s:15:\"ilpermissiongui\";i:34;s:15:\"ilpermissiongui\";i:35;s:15:\"ilpermissiongui\";i:36;s:15:\"ilpermissiongui\";i:37;s:15:\"ilpermissiongui\";i:38;s:19:\"iladministrationgui\";}s:15:\"ilpermissiongui\";a:38:{i:0;s:24:\"ilobjassessmentfoldergui\";i:1;s:20:\"ilobjauthsettingsgui\";i:2;s:16:\"ilobjcategorygui\";i:3;s:12:\"ilobjchatgui\";i:4;s:18:\"ilobjchatservergui\";i:5;s:14:\"ilobjcoursegui\";i:6;s:14:\"ilobjdlbookgui\";i:7;s:16:\"ilobjexercisegui\";i:8;s:29:\"ilobjexternaltoolssettingsgui\";i:9;s:19:\"ilobjfilebasedlmgui\";i:10;s:12:\"ilobjfilegui\";i:11;s:14:\"ilobjfoldergui\";i:12;s:13:\"ilobjforumgui\";i:13;s:16:\"ilobjglossarygui\";i:14;s:13:\"ilobjgroupgui\";i:15;s:19:\"ilobjilinccoursegui\";i:16;s:22:\"ilobjlanguagefoldergui\";i:17;s:22:\"ilobjlearningmodulegui\";i:18;s:20:\"ilobjlinkresourcegui\";i:19;s:12:\"ilobjmailgui\";i:20;s:14:\"ilobjfoldergui\";i:21;s:17:\"ilobjmediapoolgui\";i:22;s:20:\"ilobjobjectfoldergui\";i:23;s:23:\"ilobjpaymentsettingsgui\";i:24;s:20:\"ilobjquestionpoolgui\";i:25;s:22:\"ilobjrecoveryfoldergui\";i:26;s:18:\"ilobjrolefoldergui\";i:27;s:18:\"ilobjrootfoldergui\";i:28;s:26:\"ilobjsahslearningmodulegui\";i:29;s:22:\"ilobjsearchsettingsgui\";i:30;s:21:\"ilobjstylesettingsgui\";i:31;s:14:\"ilobjsurveygui\";i:32;s:26:\"ilobjsurveyquestionpoolgui\";i:33;s:20:\"ilobjsystemfoldergui\";i:34;s:14:\"ilobjcoursegui\";i:35;s:12:\"ilobjtestgui\";i:36;s:18:\"ilobjuserfoldergui\";i:37;s:20:\"ilobjusertrackinggui\";}s:1:\"\"\";a:5:{i:0;s:13:\"ilfeedbackgui\";i:1;s:13:\"ilfeedbackgui\";i:2;s:13:\"ilfeedbackgui\";i:3;s:13:\"ilfeedbackgui\";i:4;s:13:\"ilfeedbackgui\";}s:13:\"ilfeedbackgui\";a:5:{i:0;s:15:\"ilinfoscreengui\";i:1;s:15:\"ilinfoscreengui\";i:2;s:15:\"ilinfoscreengui\";i:3;s:15:\"ilinfoscreengui\";i:4;s:15:\"ilinfoscreengui\";}s:9:\"ilnotegui\";a:5:{i:0;s:15:\"ilinfoscreengui\";i:1;s:15:\"ilinfoscreengui\";i:2;s:15:\"ilinfoscreengui\";i:3;s:15:\"ilinfoscreengui\";i:4;s:15:\"ilinfoscreengui\";}s:13:\"illpfiltergui\";a:14:{i:0;s:20:\"illplistofobjectsgui\";i:1;s:21:\"illplistofprogressgui\";i:2;s:20:\"illplistofobjectsgui\";i:3;s:21:\"illplistofprogressgui\";i:4;s:20:\"illplistofobjectsgui\";i:5;s:21:\"illplistofprogressgui\";i:6;s:20:\"illplistofobjectsgui\";i:7;s:21:\"illplistofprogressgui\";i:8;s:20:\"illplistofobjectsgui\";i:9;s:21:\"illplistofprogressgui\";i:10;s:20:\"illplistofobjectsgui\";i:11;s:21:\"illplistofprogressgui\";i:12;s:20:\"illplistofobjectsgui\";i:13;s:21:\"illplistofprogressgui\";}s:17:\"ilpdfpresentation\";a:7:{i:0;s:21:\"illplistofprogressgui\";i:1;s:21:\"illplistofprogressgui\";i:2;s:21:\"illplistofprogressgui\";i:3;s:21:\"illplistofprogressgui\";i:4;s:21:\"illplistofprogressgui\";i:5;s:21:\"illplistofprogressgui\";i:6;s:21:\"illplistofprogressgui\";}s:20:\"illplistofobjectsgui\";a:7:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";i:2;s:21:\"illearningprogressgui\";i:3;s:21:\"illearningprogressgui\";i:4;s:21:\"illearningprogressgui\";i:5;s:21:\"illearningprogressgui\";i:6;s:21:\"illearningprogressgui\";}s:21:\"illplistofprogressgui\";a:7:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";i:2;s:21:\"illearningprogressgui\";i:3;s:21:\"illearningprogressgui\";i:4;s:21:\"illearningprogressgui\";i:5;s:21:\"illearningprogressgui\";i:6;s:21:\"illearningprogressgui\";}s:21:\"illplistofsettingsgui\";a:7:{i:0;s:21:\"illearningprogressgui\";i:1;s:21:\"illearningprogressgui\";i:2;s:21:\"illearningprogressgui\";i:3;s:21:\"illearningprogressgui\";i:4;s:21:\"illearningprogressgui\";i:5;s:21:\"illearningprogressgui\";i:6;s:21:\"illearningprogressgui\";}s:27:\"ilconditionhandlerinterface\";a:7:{i:0;s:14:\"ilobjcoursegui\";i:1;s:20:\"ilstructureobjectgui\";i:2;s:14:\"ilobjfoldergui\";i:3;s:13:\"ilobjgroupgui\";i:4;s:20:\"ilstructureobjectgui\";i:5;s:14:\"ilobjfoldergui\";i:6;s:14:\"ilobjcoursegui\";}s:21:\"ilcourseobjectivesgui\";a:3:{i:0;s:14:\"ilobjcoursegui\";i:1;s:14:\"ilobjcoursegui\";i:2;s:19:\"iladministrationgui\";}s:19:\"ilcourseregistergui\";a:2:{i:0;s:14:\"ilobjcoursegui\";i:1;s:14:\"ilobjcoursegui\";}s:15:\"ilinfoscreengui\";a:5:{i:0;s:14:\"ilobjcoursegui\";i:1;s:12:\"ilobjfilegui\";i:2;s:20:\"ilobjlinkresourcegui\";i:3;s:14:\"ilobjcoursegui\";i:4;s:12:\"ilobjtestgui\";}s:21:\"illearningprogressgui\";a:7:{i:0;s:14:\"ilobjcoursegui\";i:1;s:14:\"ilobjdlbookgui\";i:2;s:22:\"ilobjlearningmodulegui\";i:3;s:14:\"ilobjcoursegui\";i:4;s:12:\"ilobjtestgui\";i:5;s:12:\"ilobjusergui\";i:6;s:20:\"ilobjusertrackinggui\";}s:13:\"ilmdeditorgui\";a:28:{i:0;s:14:\"ilobjcoursegui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:19:\"ilobjmediaobjectgui\";i:3;s:17:\"illmpageobjectgui\";i:4;s:20:\"ilstructureobjectgui\";i:5;s:14:\"ilobjdlbookgui\";i:6;s:19:\"ilobjfilebasedlmgui\";i:7;s:12:\"ilobjfilegui\";i:8;s:19:\"ilobjmediaobjectgui\";i:9;s:19:\"ilobjmediaobjectgui\";i:10;s:25:\"iltermdefinitioneditorgui\";i:11;s:16:\"ilobjglossarygui\";i:12;s:19:\"ilobjmediaobjectgui\";i:13;s:19:\"ilobjmediaobjectgui\";i:14;s:17:\"illmpageobjectgui\";i:15;s:20:\"ilstructureobjectgui\";i:16;s:22:\"ilobjlearningmodulegui\";i:17;s:20:\"ilobjlinkresourcegui\";i:18;s:19:\"ilobjmediaobjectgui\";i:19;s:19:\"ilobjmediaobjectgui\";i:20;s:19:\"ilobjmediaobjectgui\";i:21;s:19:\"ilobjmediaobjectgui\";i:22;s:20:\"ilobjquestionpoolgui\";i:23;s:26:\"ilobjsahslearningmodulegui\";i:24;s:14:\"ilobjsurveygui\";i:25;s:26:\"ilobjsurveyquestionpoolgui\";i:26;s:14:\"ilobjcoursegui\";i:27;s:12:\"ilobjtestgui\";}s:22:\"ilobjcoursegroupinggui\";a:2:{i:0;s:14:\"ilobjcoursegui\";i:1;s:14:\"ilobjcoursegui\";}s:20:\"ilpaymentpurchasegui\";a:2:{i:0;s:14:\"ilobjcoursegui\";i:1;s:14:\"ilobjcoursegui\";}s:17:\"ilinternallinkgui\";a:14:{i:0;s:19:\"ilobjmediaobjectgui\";i:1;s:19:\"ilobjmediaobjectgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:19:\"ilobjmediaobjectgui\";i:4;s:19:\"ilobjmediaobjectgui\";i:5;s:15:\"ilpageeditorgui\";i:6;s:19:\"ilobjmediaobjectgui\";i:7;s:19:\"ilobjmediaobjectgui\";i:8;s:15:\"ilpageeditorgui\";i:9;s:19:\"ilobjmediaobjectgui\";i:10;s:19:\"ilobjmediaobjectgui\";i:11;s:19:\"ilobjmediaobjectgui\";i:12;s:19:\"ilobjmediaobjectgui\";i:13;s:15:\"ilpageeditorgui\";}s:19:\"ilobjmediaobjectgui\";a:10:{i:0;s:18:\"ileditclipboardgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:18:\"ileditclipboardgui\";i:3;s:15:\"ilpageeditorgui\";i:4;s:18:\"ileditclipboardgui\";i:5;s:15:\"ilpageeditorgui\";i:6;s:18:\"ileditclipboardgui\";i:7;s:17:\"ilobjmediapoolgui\";i:8;s:18:\"ileditclipboardgui\";i:9;s:15:\"ilpageeditorgui\";}s:15:\"ilpcfileitemgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:15:\"ilpcfilelistgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:11:\"ilpclistgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:15:\"ilpclistitemgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:18:\"ilpcmediaobjectgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:16:\"ilpcparagraphgui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:15:\"ilpcquestiongui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:17:\"ilpcsourcecodegui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:16:\"ilpctabledatagui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:12:\"ilpctablegui\";a:4:{i:0;s:15:\"ilpageeditorgui\";i:1;s:15:\"ilpageeditorgui\";i:2;s:15:\"ilpageeditorgui\";i:3;s:15:\"ilpageeditorgui\";}s:18:\"ileditclipboardgui\";a:5:{i:0;s:15:\"ilpageobjectgui\";i:1;s:15:\"ilpageobjectgui\";i:2;s:15:\"ilpageobjectgui\";i:3;s:17:\"ilobjmediapoolgui\";i:4;s:15:\"ilpageobjectgui\";}s:25:\"ilmediapooltargetselector\";a:4:{i:0;s:15:\"ilpageobjectgui\";i:1;s:15:\"ilpageobjectgui\";i:2;s:15:\"ilpageobjectgui\";i:3;s:15:\"ilpageobjectgui\";}s:15:\"ilpageeditorgui\";a:4:{i:0;s:15:\"ilpageobjectgui\";i:1;s:15:\"ilpageobjectgui\";i:2;s:15:\"ilpageobjectgui\";i:3;s:15:\"ilpageobjectgui\";}s:15:\"ilpageobjectgui\";a:4:{i:0;s:17:\"illmpageobjectgui\";i:1;s:25:\"iltermdefinitioneditorgui\";i:2;s:17:\"illmpageobjectgui\";i:3;s:20:\"ilobjquestionpoolgui\";}s:17:\"illmpageobjectgui\";a:2:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";}s:18:\"ilobjstylesheetgui\";a:3:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";i:2;s:19:\"iladministrationgui\";}s:20:\"ilstructureobjectgui\";a:2:{i:0;s:14:\"ilobjdlbookgui\";i:1;s:22:\"ilobjlearningmodulegui\";}s:15:\"ilfilesystemgui\";a:2:{i:0;s:19:\"ilobjfilebasedlmgui\";i:1;s:26:\"ilobjsahslearningmodulegui\";}s:25:\"iltermdefinitioneditorgui\";a:1:{i:0;s:17:\"ilglossarytermgui\";}s:17:\"ilglossarytermgui\";a:1:{i:0;s:16:\"ilobjglossarygui\";}s:13:\"ilregistergui\";a:1:{i:0;s:13:\"ilobjgroupgui\";}s:22:\"ilobjilincclassroomgui\";a:2:{i:0;s:19:\"ilobjilinccoursegui\";i:1;s:19:\"iladministrationgui\";}s:14:\"ilobjfoldergui\";a:2:{i:0;s:17:\"ilobjmediapoolgui\";i:1;s:19:\"iladministrationgui\";}s:16:\"ass_clozetestgui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:23:\"ass_imagemapquestiongui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:17:\"ass_javaappletgui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:23:\"ass_matchingquestiongui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:21:\"ass_multiplechoicegui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:23:\"ass_orderingquestiongui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:19:\"ass_textquestiongui\";a:1:{i:0;s:20:\"ilobjquestionpoolgui\";}s:21:\"ilsurveyevaluationgui\";a:1:{i:0;s:14:\"ilobjsurveygui\";}s:20:\"ilsurveyexecutiongui\";a:1:{i:0;s:14:\"ilobjsurveygui\";}s:23:\"surveymetricquestiongui\";a:1:{i:0;s:26:\"ilobjsurveyquestionpoolgui\";}s:24:\"surveynominalquestiongui\";a:1:{i:0;s:26:\"ilobjsurveyquestionpoolgui\";}s:24:\"surveyordinalquestiongui\";a:1:{i:0;s:26:\"ilobjsurveyquestionpoolgui\";}s:21:\"surveytextquestiongui\";a:1:{i:0;s:26:\"ilobjsurveyquestionpoolgui\";}s:14:\"ilobjcoursegui\";a:2:{i:0;s:12:\"ilobjtestgui\";i:1;s:19:\"iladministrationgui\";}s:19:\"iltestevaluationgui\";a:1:{i:0;s:12:\"ilobjtestgui\";}s:15:\"iltestoutputgui\";a:1:{i:0;s:12:\"ilobjtestgui\";}s:24:\"ilobjassessmentfoldergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:20:\"ilobjauthsettingsgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:16:\"ilobjcategorygui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:12:\"ilobjchatgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:18:\"ilobjchatservergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:14:\"ilobjdlbookgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:16:\"ilobjexercisegui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:29:\"ilobjexternaltoolssettingsgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:19:\"ilobjfilebasedlmgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:12:\"ilobjfilegui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:13:\"ilobjforumgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:16:\"ilobjglossarygui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:13:\"ilobjgroupgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:19:\"ilobjilinccoursegui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:22:\"ilobjlanguagefoldergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:22:\"ilobjlearningmodulegui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:20:\"ilobjlinkresourcegui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:12:\"ilobjmailgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:17:\"ilobjmediapoolgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:20:\"ilobjobjectfoldergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:23:\"ilobjpaymentsettingsgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:20:\"ilobjquestionpoolgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:22:\"ilobjrecoveryfoldergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:18:\"ilobjrolefoldergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:20:\"ilobjroletemplategui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:18:\"ilobjrootfoldergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:26:\"ilobjsahslearningmodulegui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:22:\"ilobjsearchsettingsgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:21:\"ilobjstylesettingsgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:14:\"ilobjsurveygui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:26:\"ilobjsurveyquestionpoolgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:20:\"ilobjsystemfoldergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:22:\"ilobjtaxonomyfoldergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:12:\"ilobjtestgui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:18:\"ilobjuserfoldergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:12:\"ilobjusergui\";a:1:{i:0;s:19:\"iladministrationgui\";}s:20:\"ilobjusertrackinggui\";a:1:{i:0;s:19:\"iladministrationgui\";}}');
UNLOCK TABLES;
/*!40000 ALTER TABLE `ctrl_structure` ENABLE KEYS */;

--
-- Table structure for table `dbk_translations`
--

DROP TABLE IF EXISTS `dbk_translations`;
CREATE TABLE `dbk_translations` (
  `id` int(11) NOT NULL default '0',
  `tr_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`,`tr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `dbk_translations`
--


/*!40000 ALTER TABLE `dbk_translations` DISABLE KEYS */;
LOCK TABLES `dbk_translations` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `dbk_translations` ENABLE KEYS */;

--
-- Table structure for table `desktop_item`
--

DROP TABLE IF EXISTS `desktop_item`;
CREATE TABLE `desktop_item` (
  `user_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  `type` varchar(4) NOT NULL default '',
  `parameters` varchar(200) default NULL,
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `desktop_item`
--


/*!40000 ALTER TABLE `desktop_item` DISABLE KEYS */;
LOCK TABLES `desktop_item` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `desktop_item` ENABLE KEYS */;

--
-- Table structure for table `dp_changed_dates`
--

DROP TABLE IF EXISTS `dp_changed_dates`;
CREATE TABLE `dp_changed_dates` (
  `ID` int(15) NOT NULL auto_increment,
  `user_ID` int(15) NOT NULL default '0',
  `date_ID` int(15) NOT NULL default '0',
  `status` int(15) NOT NULL default '0',
  `timestamp` int(10) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Tabelle f?r Anzeige von Ge?nderten Termindaten';

--
-- Dumping data for table `dp_changed_dates`
--


/*!40000 ALTER TABLE `dp_changed_dates` DISABLE KEYS */;
LOCK TABLES `dp_changed_dates` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `dp_changed_dates` ENABLE KEYS */;

--
-- Table structure for table `dp_dates`
--

DROP TABLE IF EXISTS `dp_dates`;
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

--
-- Dumping data for table `dp_dates`
--


/*!40000 ALTER TABLE `dp_dates` DISABLE KEYS */;
LOCK TABLES `dp_dates` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `dp_dates` ENABLE KEYS */;

--
-- Table structure for table `dp_keyword`
--

DROP TABLE IF EXISTS `dp_keyword`;
CREATE TABLE `dp_keyword` (
  `ID` int(15) NOT NULL auto_increment,
  `user_ID` int(15) NOT NULL default '0',
  `keyword` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Tabelle f?r Schlagw?rter';

--
-- Dumping data for table `dp_keyword`
--


/*!40000 ALTER TABLE `dp_keyword` DISABLE KEYS */;
LOCK TABLES `dp_keyword` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `dp_keyword` ENABLE KEYS */;

--
-- Table structure for table `dp_keywords`
--

DROP TABLE IF EXISTS `dp_keywords`;
CREATE TABLE `dp_keywords` (
  `ID` int(15) NOT NULL auto_increment,
  `date_ID` int(15) NOT NULL default '0',
  `keyword_ID` int(15) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Tabelle f?r die Zuordnung der Schlagw?rter';

--
-- Dumping data for table `dp_keywords`
--


/*!40000 ALTER TABLE `dp_keywords` DISABLE KEYS */;
LOCK TABLES `dp_keywords` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `dp_keywords` ENABLE KEYS */;

--
-- Table structure for table `dp_neg_dates`
--

DROP TABLE IF EXISTS `dp_neg_dates`;
CREATE TABLE `dp_neg_dates` (
  `ID` int(15) NOT NULL auto_increment,
  `date_ID` int(15) NOT NULL default '0',
  `user_ID` int(15) NOT NULL default '0',
  `timestamp` int(14) default NULL,
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Tabelle f?r die negativen Termine';

--
-- Dumping data for table `dp_neg_dates`
--


/*!40000 ALTER TABLE `dp_neg_dates` DISABLE KEYS */;
LOCK TABLES `dp_neg_dates` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `dp_neg_dates` ENABLE KEYS */;

--
-- Table structure for table `dp_properties`
--

DROP TABLE IF EXISTS `dp_properties`;
CREATE TABLE `dp_properties` (
  `ID` int(15) NOT NULL auto_increment,
  `user_ID` int(15) NOT NULL default '0',
  `dv_starttime` time NOT NULL default '00:00:00',
  `dv_endtime` time NOT NULL default '00:00:00',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Tabelle f?r UserEinstellungen';

--
-- Dumping data for table `dp_properties`
--


/*!40000 ALTER TABLE `dp_properties` DISABLE KEYS */;
LOCK TABLES `dp_properties` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `dp_properties` ENABLE KEYS */;

--
-- Table structure for table `exc_data`
--

DROP TABLE IF EXISTS `exc_data`;
CREATE TABLE `exc_data` (
  `obj_id` int(11) NOT NULL default '0',
  `instruction` text,
  `time_stamp` int(10) default NULL,
  PRIMARY KEY  (`obj_id`),
  FULLTEXT KEY `instruction` (`instruction`)
) TYPE=MyISAM;

--
-- Dumping data for table `exc_data`
--


/*!40000 ALTER TABLE `exc_data` DISABLE KEYS */;
LOCK TABLES `exc_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `exc_data` ENABLE KEYS */;

--
-- Table structure for table `exc_members`
--

DROP TABLE IF EXISTS `exc_members`;
CREATE TABLE `exc_members` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `notice` text,
  `returned` tinyint(1) default NULL,
  `solved` tinyint(1) default NULL,
  `sent` tinyint(1) default NULL,
  PRIMARY KEY  (`obj_id`,`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `exc_members`
--


/*!40000 ALTER TABLE `exc_members` DISABLE KEYS */;
LOCK TABLES `exc_members` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `exc_members` ENABLE KEYS */;

--
-- Table structure for table `exc_returned`
--

DROP TABLE IF EXISTS `exc_returned`;
CREATE TABLE `exc_returned` (
  `returned_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `filename` mediumtext NOT NULL,
  `filetitle` mediumtext NOT NULL,
  `mimetype` varchar(40) NOT NULL default '',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`returned_id`),
  KEY `obj_id` (`obj_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `exc_returned`
--


/*!40000 ALTER TABLE `exc_returned` DISABLE KEYS */;
LOCK TABLES `exc_returned` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `exc_returned` ENABLE KEYS */;

--
-- Table structure for table `feedback_items`
--

DROP TABLE IF EXISTS `feedback_items`;
CREATE TABLE `feedback_items` (
  `fb_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `description` text,
  `anonymous` tinyint(1) NOT NULL default '1',
  `required` tinyint(1) default '0',
  `show_on` varchar(6) default NULL,
  `text_answer` tinyint(1) default '0',
  `votes` text,
  `starttime` int(11) default '0',
  `endtime` int(11) default '0',
  `repeat_interval` int(11) default NULL,
  `interval_unit` tinyint(4) default NULL,
  `first_vote_best` tinyint(1) default '0',
  `obj_id` int(11) default '0',
  `ref_id` int(11) default '0',
  PRIMARY KEY  (`fb_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `feedback_items`
--


/*!40000 ALTER TABLE `feedback_items` DISABLE KEYS */;
LOCK TABLES `feedback_items` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `feedback_items` ENABLE KEYS */;

--
-- Table structure for table `feedback_results`
--

DROP TABLE IF EXISTS `feedback_results`;
CREATE TABLE `feedback_results` (
  `fb_id` int(11) NOT NULL default '0',
  `user_id` int(11) default NULL,
  `vote` int(11) NOT NULL default '0',
  `note` text NOT NULL,
  `votetime` int(11) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table `feedback_results`
--


/*!40000 ALTER TABLE `feedback_results` DISABLE KEYS */;
LOCK TABLES `feedback_results` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `feedback_results` ENABLE KEYS */;

--
-- Table structure for table `file_based_lm`
--

DROP TABLE IF EXISTS `file_based_lm`;
CREATE TABLE `file_based_lm` (
  `id` int(11) NOT NULL default '0',
  `online` enum('y','n') default 'n',
  `startfile` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `file_based_lm`
--


/*!40000 ALTER TABLE `file_based_lm` DISABLE KEYS */;
LOCK TABLES `file_based_lm` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `file_based_lm` ENABLE KEYS */;

--
-- Table structure for table `file_data`
--

DROP TABLE IF EXISTS `file_data`;
CREATE TABLE `file_data` (
  `file_id` int(11) NOT NULL default '0',
  `file_name` char(128) NOT NULL default '',
  `file_type` char(64) NOT NULL default '',
  `version` int(11) default NULL,
  `mode` char(8) default 'object',
  PRIMARY KEY  (`file_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `file_data`
--


/*!40000 ALTER TABLE `file_data` DISABLE KEYS */;
LOCK TABLES `file_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `file_data` ENABLE KEYS */;

--
-- Table structure for table `file_usage`
--

DROP TABLE IF EXISTS `file_usage`;
CREATE TABLE `file_usage` (
  `id` int(11) NOT NULL default '0',
  `usage_type` varchar(10) NOT NULL default '',
  `usage_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`,`usage_type`,`usage_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `file_usage`
--


/*!40000 ALTER TABLE `file_usage` DISABLE KEYS */;
LOCK TABLES `file_usage` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `file_usage` ENABLE KEYS */;

--
-- Table structure for table `frm_data`
--

DROP TABLE IF EXISTS `frm_data`;
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

--
-- Dumping data for table `frm_data`
--


/*!40000 ALTER TABLE `frm_data` DISABLE KEYS */;
LOCK TABLES `frm_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `frm_data` ENABLE KEYS */;

--
-- Table structure for table `frm_notification`
--

DROP TABLE IF EXISTS `frm_notification`;
CREATE TABLE `frm_notification` (
  `notification_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `frm_id` int(11) NOT NULL default '0',
  `thread_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`notification_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `frm_notification`
--


/*!40000 ALTER TABLE `frm_notification` DISABLE KEYS */;
LOCK TABLES `frm_notification` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `frm_notification` ENABLE KEYS */;

--
-- Table structure for table `frm_posts`
--

DROP TABLE IF EXISTS `frm_posts`;
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
  PRIMARY KEY  (`pos_pk`),
  KEY `pos_thr_fk` (`pos_thr_fk`),
  KEY `pos_top_fk` (`pos_top_fk`),
  FULLTEXT KEY `message_subject` (`pos_message`,`pos_subject`)
) TYPE=MyISAM;

--
-- Dumping data for table `frm_posts`
--


/*!40000 ALTER TABLE `frm_posts` DISABLE KEYS */;
LOCK TABLES `frm_posts` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `frm_posts` ENABLE KEYS */;

--
-- Table structure for table `frm_posts_tree`
--

DROP TABLE IF EXISTS `frm_posts_tree`;
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

--
-- Dumping data for table `frm_posts_tree`
--


/*!40000 ALTER TABLE `frm_posts_tree` DISABLE KEYS */;
LOCK TABLES `frm_posts_tree` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `frm_posts_tree` ENABLE KEYS */;

--
-- Table structure for table `frm_settings`
--

DROP TABLE IF EXISTS `frm_settings`;
CREATE TABLE `frm_settings` (
  `obj_id` int(11) NOT NULL default '0',
  `default_view` int(2) NOT NULL default '0',
  `anonymized` tinyint(1) NOT NULL default '0',
  `statistics_enabled` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `frm_settings`
--


/*!40000 ALTER TABLE `frm_settings` DISABLE KEYS */;
LOCK TABLES `frm_settings` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `frm_settings` ENABLE KEYS */;

--
-- Table structure for table `frm_thread_access`
--

DROP TABLE IF EXISTS `frm_thread_access`;
CREATE TABLE `frm_thread_access` (
  `usr_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `thread_id` int(11) NOT NULL default '0',
  `access_old` int(11) NOT NULL default '0',
  `access_last` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`obj_id`,`thread_id`),
  KEY `usr_thread` (`thread_id`,`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `frm_thread_access`
--


/*!40000 ALTER TABLE `frm_thread_access` DISABLE KEYS */;
LOCK TABLES `frm_thread_access` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `frm_thread_access` ENABLE KEYS */;

--
-- Table structure for table `frm_threads`
--

DROP TABLE IF EXISTS `frm_threads`;
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
  PRIMARY KEY  (`thr_pk`),
  FULLTEXT KEY `thr_subject` (`thr_subject`)
) TYPE=MyISAM;

--
-- Dumping data for table `frm_threads`
--


/*!40000 ALTER TABLE `frm_threads` DISABLE KEYS */;
LOCK TABLES `frm_threads` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `frm_threads` ENABLE KEYS */;

--
-- Table structure for table `frm_user_read`
--

DROP TABLE IF EXISTS `frm_user_read`;
CREATE TABLE `frm_user_read` (
  `usr_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `thread_id` int(11) NOT NULL default '0',
  `post_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`obj_id`,`thread_id`,`post_id`),
  KEY `obj_usr` (`obj_id`,`usr_id`),
  KEY `post_usr` (`post_id`,`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `frm_user_read`
--


/*!40000 ALTER TABLE `frm_user_read` DISABLE KEYS */;
LOCK TABLES `frm_user_read` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `frm_user_read` ENABLE KEYS */;

--
-- Table structure for table `glossary`
--

DROP TABLE IF EXISTS `glossary`;
CREATE TABLE `glossary` (
  `id` int(11) NOT NULL default '0',
  `online` enum('y','n') default 'n',
  `virtual` enum('none','fixed','level','subtree') NOT NULL default 'none',
  `public_html_file` varchar(50) NOT NULL default '',
  `public_xml_file` varchar(50) NOT NULL default '',
  `glo_menu_active` enum('y','n') default 'y',
  `downloads_active` enum('y','n') default 'n',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `glossary`
--


/*!40000 ALTER TABLE `glossary` DISABLE KEYS */;
LOCK TABLES `glossary` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `glossary` ENABLE KEYS */;

--
-- Table structure for table `glossary_definition`
--

DROP TABLE IF EXISTS `glossary_definition`;
CREATE TABLE `glossary_definition` (
  `id` int(11) NOT NULL auto_increment,
  `term_id` int(11) NOT NULL default '0',
  `short_text` varchar(200) NOT NULL default '',
  `nr` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `glossary_definition`
--


/*!40000 ALTER TABLE `glossary_definition` DISABLE KEYS */;
LOCK TABLES `glossary_definition` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `glossary_definition` ENABLE KEYS */;

--
-- Table structure for table `glossary_term`
--

DROP TABLE IF EXISTS `glossary_term`;
CREATE TABLE `glossary_term` (
  `id` int(11) NOT NULL auto_increment,
  `glo_id` int(11) NOT NULL default '0',
  `term` varchar(200) default NULL,
  `language` varchar(2) default NULL,
  `import_id` varchar(50) NOT NULL default '',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `glo_id` (`glo_id`),
  FULLTEXT KEY `term` (`term`)
) TYPE=MyISAM;

--
-- Dumping data for table `glossary_term`
--


/*!40000 ALTER TABLE `glossary_term` DISABLE KEYS */;
LOCK TABLES `glossary_term` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `glossary_term` ENABLE KEYS */;

--
-- Table structure for table `grp_data`
--

DROP TABLE IF EXISTS `grp_data`;
CREATE TABLE `grp_data` (
  `grp_id` int(11) NOT NULL default '0',
  `register` int(11) default '1',
  `password` varchar(255) default NULL,
  `expiration` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`grp_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `grp_data`
--


/*!40000 ALTER TABLE `grp_data` DISABLE KEYS */;
LOCK TABLES `grp_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `grp_data` ENABLE KEYS */;

--
-- Table structure for table `grp_registration`
--

DROP TABLE IF EXISTS `grp_registration`;
CREATE TABLE `grp_registration` (
  `grp_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `application_date` datetime NOT NULL default '0000-00-00 00:00:00'
) TYPE=MyISAM;

--
-- Dumping data for table `grp_registration`
--


/*!40000 ALTER TABLE `grp_registration` DISABLE KEYS */;
LOCK TABLES `grp_registration` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `grp_registration` ENABLE KEYS */;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` varchar(8) NOT NULL default '',
  `action` varchar(20) NOT NULL default '',
  `hdate` datetime default NULL,
  `usr_id` int(11) NOT NULL default '0',
  `info_params` text NOT NULL,
  `user_comment` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_type` (`obj_id`,`obj_type`)
) TYPE=MyISAM;

--
-- Dumping data for table `history`
--


/*!40000 ALTER TABLE `history` DISABLE KEYS */;
LOCK TABLES `history` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `history` ENABLE KEYS */;

--
-- Table structure for table `il_meta_annotation`
--

DROP TABLE IF EXISTS `il_meta_annotation`;
CREATE TABLE `il_meta_annotation` (
  `meta_annotation_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `entity` text,
  `date` text,
  `description` text,
  `description_language` varchar(2) default NULL,
  PRIMARY KEY  (`meta_annotation_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_annotation`
--


/*!40000 ALTER TABLE `il_meta_annotation` DISABLE KEYS */;
LOCK TABLES `il_meta_annotation` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_annotation` ENABLE KEYS */;

--
-- Table structure for table `il_meta_classification`
--

DROP TABLE IF EXISTS `il_meta_classification`;
CREATE TABLE `il_meta_classification` (
  `meta_classification_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `purpose` varchar(32) default NULL,
  `description` text,
  `description_language` varchar(2) default NULL,
  PRIMARY KEY  (`meta_classification_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_classification`
--


/*!40000 ALTER TABLE `il_meta_classification` DISABLE KEYS */;
LOCK TABLES `il_meta_classification` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_classification` ENABLE KEYS */;

--
-- Table structure for table `il_meta_contribute`
--

DROP TABLE IF EXISTS `il_meta_contribute`;
CREATE TABLE `il_meta_contribute` (
  `meta_contribute_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(32) default NULL,
  `parent_id` int(11) default NULL,
  `role` varchar(32) default NULL,
  `date` text,
  PRIMARY KEY  (`meta_contribute_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_contribute`
--


/*!40000 ALTER TABLE `il_meta_contribute` DISABLE KEYS */;
LOCK TABLES `il_meta_contribute` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_contribute` ENABLE KEYS */;

--
-- Table structure for table `il_meta_description`
--

DROP TABLE IF EXISTS `il_meta_description`;
CREATE TABLE `il_meta_description` (
  `meta_description_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(16) default NULL,
  `parent_id` int(11) default NULL,
  `description` text,
  `description_language` varchar(2) default NULL,
  PRIMARY KEY  (`meta_description_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`),
  FULLTEXT KEY `description` (`description`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_description`
--


/*!40000 ALTER TABLE `il_meta_description` DISABLE KEYS */;
LOCK TABLES `il_meta_description` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_description` ENABLE KEYS */;

--
-- Table structure for table `il_meta_educational`
--

DROP TABLE IF EXISTS `il_meta_educational`;
CREATE TABLE `il_meta_educational` (
  `meta_educational_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `interactivity_type` varchar(16) default NULL,
  `learning_resource_type` varchar(32) default NULL,
  `interactivity_level` varchar(16) default NULL,
  `semantic_density` varchar(16) default NULL,
  `intended_end_user_role` varchar(16) default NULL,
  `context` varchar(16) default NULL,
  `difficulty` varchar(16) default NULL,
  `typical_learning_time` text,
  PRIMARY KEY  (`meta_educational_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_educational`
--


/*!40000 ALTER TABLE `il_meta_educational` DISABLE KEYS */;
LOCK TABLES `il_meta_educational` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_educational` ENABLE KEYS */;

--
-- Table structure for table `il_meta_entity`
--

DROP TABLE IF EXISTS `il_meta_entity`;
CREATE TABLE `il_meta_entity` (
  `meta_entity_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(16) default NULL,
  `parent_id` int(11) default NULL,
  `entity` text,
  PRIMARY KEY  (`meta_entity_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`),
  FULLTEXT KEY `entity` (`entity`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_entity`
--


/*!40000 ALTER TABLE `il_meta_entity` DISABLE KEYS */;
LOCK TABLES `il_meta_entity` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_entity` ENABLE KEYS */;

--
-- Table structure for table `il_meta_format`
--

DROP TABLE IF EXISTS `il_meta_format`;
CREATE TABLE `il_meta_format` (
  `meta_format_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `format` text,
  PRIMARY KEY  (`meta_format_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_format`
--


/*!40000 ALTER TABLE `il_meta_format` DISABLE KEYS */;
LOCK TABLES `il_meta_format` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_format` ENABLE KEYS */;

--
-- Table structure for table `il_meta_general`
--

DROP TABLE IF EXISTS `il_meta_general`;
CREATE TABLE `il_meta_general` (
  `meta_general_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `general_structure` varchar(16) default NULL,
  `title` text NOT NULL,
  `title_language` varchar(2) default NULL,
  `coverage` text,
  `coverage_language` varchar(2) default NULL,
  PRIMARY KEY  (`meta_general_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`),
  FULLTEXT KEY `title_coverage` (`title`,`coverage`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_general`
--


/*!40000 ALTER TABLE `il_meta_general` DISABLE KEYS */;
LOCK TABLES `il_meta_general` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_general` ENABLE KEYS */;

--
-- Table structure for table `il_meta_identifier`
--

DROP TABLE IF EXISTS `il_meta_identifier`;
CREATE TABLE `il_meta_identifier` (
  `meta_identifier_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(16) default NULL,
  `parent_id` int(11) default NULL,
  `catalog` text,
  `entry` text,
  PRIMARY KEY  (`meta_identifier_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_identifier`
--


/*!40000 ALTER TABLE `il_meta_identifier` DISABLE KEYS */;
LOCK TABLES `il_meta_identifier` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_identifier` ENABLE KEYS */;

--
-- Table structure for table `il_meta_identifier_`
--

DROP TABLE IF EXISTS `il_meta_identifier_`;
CREATE TABLE `il_meta_identifier_` (
  `meta_identifier__id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(16) default NULL,
  `parent_id` int(11) default NULL,
  `catalog` text,
  `entry` text,
  PRIMARY KEY  (`meta_identifier__id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_identifier_`
--


/*!40000 ALTER TABLE `il_meta_identifier_` DISABLE KEYS */;
LOCK TABLES `il_meta_identifier_` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_identifier_` ENABLE KEYS */;

--
-- Table structure for table `il_meta_keyword`
--

DROP TABLE IF EXISTS `il_meta_keyword`;
CREATE TABLE `il_meta_keyword` (
  `meta_keyword_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(32) default NULL,
  `parent_id` int(11) default NULL,
  `keyword` text,
  `keyword_language` varchar(2) default NULL,
  PRIMARY KEY  (`meta_keyword_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`),
  FULLTEXT KEY `keyword` (`keyword`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_keyword`
--


/*!40000 ALTER TABLE `il_meta_keyword` DISABLE KEYS */;
LOCK TABLES `il_meta_keyword` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_keyword` ENABLE KEYS */;

--
-- Table structure for table `il_meta_language`
--

DROP TABLE IF EXISTS `il_meta_language`;
CREATE TABLE `il_meta_language` (
  `meta_language_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` char(6) default NULL,
  `parent_type` char(16) default NULL,
  `parent_id` int(11) default NULL,
  `language` char(2) default NULL,
  PRIMARY KEY  (`meta_language_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_language`
--


/*!40000 ALTER TABLE `il_meta_language` DISABLE KEYS */;
LOCK TABLES `il_meta_language` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_language` ENABLE KEYS */;

--
-- Table structure for table `il_meta_lifecycle`
--

DROP TABLE IF EXISTS `il_meta_lifecycle`;
CREATE TABLE `il_meta_lifecycle` (
  `meta_lifecycle_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `lifecycle_status` varchar(16) default NULL,
  `meta_version` text,
  `version_language` varchar(2) binary default NULL,
  PRIMARY KEY  (`meta_lifecycle_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_lifecycle`
--


/*!40000 ALTER TABLE `il_meta_lifecycle` DISABLE KEYS */;
LOCK TABLES `il_meta_lifecycle` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_lifecycle` ENABLE KEYS */;

--
-- Table structure for table `il_meta_location`
--

DROP TABLE IF EXISTS `il_meta_location`;
CREATE TABLE `il_meta_location` (
  `meta_location_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(16) NOT NULL default '',
  `parent_id` int(11) default NULL,
  `location` text,
  `location_type` varchar(16) default NULL,
  PRIMARY KEY  (`meta_location_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_location`
--


/*!40000 ALTER TABLE `il_meta_location` DISABLE KEYS */;
LOCK TABLES `il_meta_location` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_location` ENABLE KEYS */;

--
-- Table structure for table `il_meta_meta_data`
--

DROP TABLE IF EXISTS `il_meta_meta_data`;
CREATE TABLE `il_meta_meta_data` (
  `meta_meta_data_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` char(6) default NULL,
  `meta_data_scheme` char(16) default NULL,
  `language` char(2) default NULL,
  PRIMARY KEY  (`meta_meta_data_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_meta_data`
--


/*!40000 ALTER TABLE `il_meta_meta_data` DISABLE KEYS */;
LOCK TABLES `il_meta_meta_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_meta_data` ENABLE KEYS */;

--
-- Table structure for table `il_meta_relation`
--

DROP TABLE IF EXISTS `il_meta_relation`;
CREATE TABLE `il_meta_relation` (
  `meta_relation_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` char(6) default NULL,
  `kind` char(16) default NULL,
  PRIMARY KEY  (`meta_relation_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_relation`
--


/*!40000 ALTER TABLE `il_meta_relation` DISABLE KEYS */;
LOCK TABLES `il_meta_relation` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_relation` ENABLE KEYS */;

--
-- Table structure for table `il_meta_requirement`
--

DROP TABLE IF EXISTS `il_meta_requirement`;
CREATE TABLE `il_meta_requirement` (
  `meta_requirement_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(16) default NULL,
  `parent_id` int(11) default NULL,
  `operating_system_name` varchar(16) default NULL,
  `operating_system_minimum_version` text,
  `operating_system_maximum_version` text,
  `browser_name` varchar(32) default NULL,
  `browser_minimum_version` text,
  `browser_maximum_version` text,
  `or_composite_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`meta_requirement_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_requirement`
--


/*!40000 ALTER TABLE `il_meta_requirement` DISABLE KEYS */;
LOCK TABLES `il_meta_requirement` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_requirement` ENABLE KEYS */;

--
-- Table structure for table `il_meta_rights`
--

DROP TABLE IF EXISTS `il_meta_rights`;
CREATE TABLE `il_meta_rights` (
  `meta_rights_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `costs` varchar(3) default NULL,
  `copyright_and_other_restrictions` varchar(3) default NULL,
  `description` text,
  `description_language` varchar(2) default NULL,
  PRIMARY KEY  (`meta_rights_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_rights`
--


/*!40000 ALTER TABLE `il_meta_rights` DISABLE KEYS */;
LOCK TABLES `il_meta_rights` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_rights` ENABLE KEYS */;

--
-- Table structure for table `il_meta_taxon`
--

DROP TABLE IF EXISTS `il_meta_taxon`;
CREATE TABLE `il_meta_taxon` (
  `meta_taxon_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(32) default NULL,
  `parent_id` int(11) default NULL,
  `taxon` text,
  `taxon_language` varchar(2) default NULL,
  `taxon_id` text,
  PRIMARY KEY  (`meta_taxon_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_taxon`
--


/*!40000 ALTER TABLE `il_meta_taxon` DISABLE KEYS */;
LOCK TABLES `il_meta_taxon` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_taxon` ENABLE KEYS */;

--
-- Table structure for table `il_meta_taxon_path`
--

DROP TABLE IF EXISTS `il_meta_taxon_path`;
CREATE TABLE `il_meta_taxon_path` (
  `meta_taxon_path_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(32) default NULL,
  `parent_id` int(11) default NULL,
  `source` text,
  `source_language` varchar(2) default NULL,
  PRIMARY KEY  (`meta_taxon_path_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_taxon_path`
--


/*!40000 ALTER TABLE `il_meta_taxon_path` DISABLE KEYS */;
LOCK TABLES `il_meta_taxon_path` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_taxon_path` ENABLE KEYS */;

--
-- Table structure for table `il_meta_technical`
--

DROP TABLE IF EXISTS `il_meta_technical`;
CREATE TABLE `il_meta_technical` (
  `meta_technical_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `size` text,
  `installation_remarks` text,
  `installation_remarks_language` varchar(2) default NULL,
  `other_platform_requirements` text,
  `other_platform_requirements_language` varchar(2) default NULL,
  `duration` text,
  PRIMARY KEY  (`meta_technical_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_technical`
--


/*!40000 ALTER TABLE `il_meta_technical` DISABLE KEYS */;
LOCK TABLES `il_meta_technical` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_technical` ENABLE KEYS */;

--
-- Table structure for table `il_meta_typical_age_range`
--

DROP TABLE IF EXISTS `il_meta_typical_age_range`;
CREATE TABLE `il_meta_typical_age_range` (
  `meta_typical_age_range_id` int(11) NOT NULL auto_increment,
  `rbac_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  `obj_type` varchar(6) default NULL,
  `parent_type` varchar(16) default NULL,
  `parent_id` int(11) default NULL,
  `typical_age_range` text,
  `typical_age_range_language` varchar(2) default NULL,
  `typical_age_range_min` tinyint(3) NOT NULL default '-1',
  `typical_age_range_max` tinyint(3) NOT NULL default '-1',
  PRIMARY KEY  (`meta_typical_age_range_id`),
  KEY `rbac_obj` (`rbac_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `il_meta_typical_age_range`
--


/*!40000 ALTER TABLE `il_meta_typical_age_range` DISABLE KEYS */;
LOCK TABLES `il_meta_typical_age_range` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `il_meta_typical_age_range` ENABLE KEYS */;

--
-- Table structure for table `ilinc_data`
--

DROP TABLE IF EXISTS `ilinc_data`;
CREATE TABLE `ilinc_data` (
  `obj_id` int(11) unsigned NOT NULL default '0',
  `type` varchar(5) NOT NULL default '',
  `course_id` int(11) unsigned NOT NULL default '0',
  `contact_name` varchar(255) default NULL,
  `contact_responsibility` varchar(255) default NULL,
  `contact_phone` varchar(255) default NULL,
  `contact_email` varchar(255) default NULL,
  `activation_unlimited` tinyint(2) default NULL,
  `activation_start` int(11) default NULL,
  `activation_end` int(11) default NULL,
  `activation_offline` enum('y','n') default NULL,
  `subscription_unlimited` tinyint(2) default NULL,
  `subscription_start` int(11) default NULL,
  `subscription_end` int(11) default NULL,
  `subscription_type` tinyint(2) default NULL,
  `subscription_password` varchar(32) default NULL,
  KEY `obj_id` (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `ilinc_data`
--


/*!40000 ALTER TABLE `ilinc_data` DISABLE KEYS */;
LOCK TABLES `ilinc_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ilinc_data` ENABLE KEYS */;

--
-- Table structure for table `ilinc_registration`
--

DROP TABLE IF EXISTS `ilinc_registration`;
CREATE TABLE `ilinc_registration` (
  `obj_id` int(10) unsigned NOT NULL default '0',
  `usr_id` int(10) unsigned NOT NULL default '0',
  `usr_text` varchar(255) default NULL,
  `application_date` datetime default NULL,
  PRIMARY KEY  (`obj_id`),
  KEY `usr_id` (`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `ilinc_registration`
--


/*!40000 ALTER TABLE `ilinc_registration` DISABLE KEYS */;
LOCK TABLES `ilinc_registration` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ilinc_registration` ENABLE KEYS */;

--
-- Table structure for table `int_link`
--

DROP TABLE IF EXISTS `int_link`;
CREATE TABLE `int_link` (
  `source_type` varchar(10) NOT NULL default '',
  `source_id` int(11) NOT NULL default '0',
  `target_type` varchar(4) NOT NULL default '',
  `target_id` int(11) NOT NULL default '0',
  `target_inst` int(11) NOT NULL default '0',
  PRIMARY KEY  (`source_type`,`source_id`,`target_type`,`target_id`,`target_inst`)
) TYPE=MyISAM;

--
-- Dumping data for table `int_link`
--


/*!40000 ALTER TABLE `int_link` DISABLE KEYS */;
LOCK TABLES `int_link` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `int_link` ENABLE KEYS */;

--
-- Table structure for table `link_check`
--

DROP TABLE IF EXISTS `link_check`;
CREATE TABLE `link_check` (
  `obj_id` int(11) NOT NULL default '0',
  `page_id` int(11) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `parent_type` varchar(8) NOT NULL default '',
  `http_status_code` int(4) NOT NULL default '0',
  `last_check` int(11) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table `link_check`
--


/*!40000 ALTER TABLE `link_check` DISABLE KEYS */;
LOCK TABLES `link_check` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `link_check` ENABLE KEYS */;

--
-- Table structure for table `link_check_report`
--

DROP TABLE IF EXISTS `link_check_report`;
CREATE TABLE `link_check_report` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`,`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `link_check_report`
--


/*!40000 ALTER TABLE `link_check_report` DISABLE KEYS */;
LOCK TABLES `link_check_report` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `link_check_report` ENABLE KEYS */;

--
-- Table structure for table `lm_data`
--

DROP TABLE IF EXISTS `lm_data`;
CREATE TABLE `lm_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `type` varchar(2) NOT NULL default '',
  `lm_id` int(11) NOT NULL default '0',
  `import_id` varchar(50) NOT NULL default '',
  `public_access` enum('y','n') NOT NULL default 'n',
  `create_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`obj_id`),
  KEY `lm_id` (`lm_id`),
  KEY `type` (`type`)
) TYPE=MyISAM;

--
-- Dumping data for table `lm_data`
--


/*!40000 ALTER TABLE `lm_data` DISABLE KEYS */;
LOCK TABLES `lm_data` WRITE;
INSERT INTO `lm_data` VALUES (1,'dummy','du',0,'','n','0000-00-00 00:00:00','0000-00-00 00:00:00');
UNLOCK TABLES;
/*!40000 ALTER TABLE `lm_data` ENABLE KEYS */;

--
-- Table structure for table `lm_menu`
--

DROP TABLE IF EXISTS `lm_menu`;
CREATE TABLE `lm_menu` (
  `id` int(11) NOT NULL auto_increment,
  `lm_id` int(11) NOT NULL default '0',
  `link_type` enum('extern','intern') default NULL,
  `title` varchar(200) default NULL,
  `target` varchar(200) default NULL,
  `link_ref_id` int(11) default NULL,
  `active` enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`id`),
  KEY `link_type` (`link_type`),
  KEY `lm_id` (`lm_id`),
  KEY `active` (`active`)
) TYPE=MyISAM;

--
-- Dumping data for table `lm_menu`
--


/*!40000 ALTER TABLE `lm_menu` DISABLE KEYS */;
LOCK TABLES `lm_menu` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `lm_menu` ENABLE KEYS */;

--
-- Table structure for table `lm_tree`
--

DROP TABLE IF EXISTS `lm_tree`;
CREATE TABLE `lm_tree` (
  `lm_id` int(11) NOT NULL default '0',
  `child` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned default NULL,
  `lft` int(11) unsigned NOT NULL default '0',
  `rgt` int(11) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  KEY `child` (`child`),
  KEY `parent` (`parent`),
  KEY `jmp_lm` (`lm_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `lm_tree`
--


/*!40000 ALTER TABLE `lm_tree` DISABLE KEYS */;
LOCK TABLES `lm_tree` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `lm_tree` ENABLE KEYS */;

--
-- Table structure for table `lng_data`
--

DROP TABLE IF EXISTS `lng_data`;
CREATE TABLE `lng_data` (
  `module` varchar(30) NOT NULL default '',
  `identifier` varchar(50) binary NOT NULL default '',
  `lang_key` varchar(2) NOT NULL default '',
  `value` blob NOT NULL,
  PRIMARY KEY  (`module`,`identifier`,`lang_key`),
  KEY `module` (`module`),
  KEY `lang_key` (`lang_key`)
) TYPE=MyISAM;

--
-- Dumping data for table `lng_data`
--


/*!40000 ALTER TABLE `lng_data` DISABLE KEYS */;
LOCK TABLES `lng_data` WRITE;
INSERT INTO `lng_data` VALUES ('administration','analysis_options','en','Analysis options'),('administration','analyze_data','en','Analyze and repair data integrity'),('administration','analyzing','en','Analyzing...'),('administration','analyzing_tree_structure','en','Analyzing tree structure...'),('administration','check_tree','en','Check tree structure'),('administration','check_tree_desc','en','Check consistence of entire tree structure. Depending on tree size this may take a while!'),('administration','clean','en','Clean up'),('administration','clean_desc','en','Remove invalid references & tree entries. Initialize gaps in tree structure.'),('administration','cleaning','en','Cleaning...'),('administration','cleaning_final','en','Final cleaning...'),('administration','disabled','en','disabled'),('administration','done','en','Done'),('administration','dump_tree','en','Dump tree'),('administration','dump_tree_desc','en','Perform an analysis of the tree and print all tree nodes along with analysis data.'),('administration','dumping_tree','en','Dumping tree...'),('administration','found','en','found.'),('administration','found_none','en','none found.'),('administration','initializing_gaps','en','Initializing gaps in tree...'),('administration','log_scan','en','Log scan results'),('administration','log_scan_desc','en','Write scan results to \'scanlog.log\' in client data directory.'),('administration','nothing_to_purge','en','Nothing to purge...'),('administration','nothing_to_remove','en','Nothing to remove...'),('administration','nothing_to_restore','en','Nothing to restore...'),('administration','options','en','Options'),('administration','output_options','en','Output options'),('administration','passwd_auto_generate','en','Password Forwarding/Generation'),('administration','purge_missing','en','Purge missing objects'),('administration','purge_missing_desc','en','Remove all missing and unbound objects found from system.'),('administration','purge_trash','en','Purge deleted objects'),('administration','purge_trash_desc','en','Remove all objects in trash bin from system.'),('administration','purging','en','Purging...'),('administration','purging_missing_objs','en','Purging missing objects...'),('administration','purging_trash','en','Purging trash...'),('administration','purging_unbound_objs','en','Purging unbound objects...'),('administration','removing_invalid_childs','en','Removing invalid tree entries...'),('administration','removing_invalid_refs','en','Removing invalid references...'),('administration','removing_invalid_rolfs','en','Removing invalid rolefolders...'),('administration','repair_options','en','Repair options'),('administration','restore_missing','en','Restore missing objects'),('administration','restore_missing_desc','en','Restore missing and unbound objects to RecoveryFolder.'),('administration','restore_trash','en','Restore deleted objects'),('administration','restore_trash_desc','en','Restore all objects in trash bin to RecoveryFolder.'),('administration','restoring','en','Restoring...'),('administration','restoring_missing_objs','en','Restoring missing objects...'),('administration','restoring_trash','en','Restoring trash...'),('administration','restoring_unbound_objs','en','Restoring unbound objects & subobjects...'),('administration','scan','en','Scan'),('administration','scan_completed','en','Scan completed'),('administration','scan_desc','en','Scan system for corrupted/invalid/missing/unbound objects.'),('administration','scan_details','en','Scan details'),('administration','scan_modes','en','Scan modes used'),('administration','scanning_system','en','Scanning system...'),('administration','searching_deleted_objs','en','Searching for deleted objects...'),('administration','searching_invalid_childs','en','Searching for invalid tree entries...'),('administration','searching_invalid_refs','en','Searching for invalid references...'),('administration','searching_invalid_rolfs','en','Searching for invalid rolefolders...'),('administration','searching_missing_objs','en','Searching for missing objects...'),('administration','searching_unbound_objs','en','Searching for unbound objects...'),('administration','skipped','en','skipped'),('administration','start_scan','en','Start!'),('administration','stop_scan','en','Stop!'),('administration','systemcheck','en','System check'),('administration','tree_corrupt','en','Tree is corrupted! See scan log for details.'),('administration','view_last_log','en','View last Scan log'),('administration','view_log','en','View details'),('assessment','0_unlimited','en','(0=unlimited)'),('assessment','action','en','Action'),('assessment','add_answer','en','Add answer'),('assessment','add_answer_tf','en','Add true/false answers'),('assessment','add_answer_yn','en','Add yes/no answers'),('assessment','add_applet_parameter','en','Add applet parameter'),('assessment','add_area','en','Add area'),('assessment','add_gap','en','Add Gap Text'),('assessment','add_imagemap','en','Import image map'),('assessment','add_matching_pair','en','Add matching pair'),('assessment','add_questionpool','en','Add question pool'),('assessment','add_solution','en','Add suggested solution'),('assessment','all_available_question_pools','en','All available question pools'),('assessment','answer','en','Answer'),('assessment','answer_is_right','en','Your solution is right'),('assessment','answer_is_wrong','en','Your solution is wrong'),('assessment','answer_picture','en','Answer picture'),('assessment','answer_text','en','Answer text'),('assessment','applet_attributes','en','Applet attributes'),('assessment','applet_new_parameter','en','New parameter'),('assessment','applet_parameter','en','Parameter'),('assessment','applet_parameters','en','Applet parameters'),('assessment','apply','en','Apply'),('assessment','ass_create_export_file','en','Create export file'),('assessment','ass_create_export_test_results','en','Export test results'),('assessment','ass_export_files','en','Export files'),('assessment','ass_file','en','File'),('assessment','ass_question','en','Question'),('assessment','ass_questions','en','Questions'),('assessment','ass_size','en','Size'),('assessment','available_points','en','Available points'),('assessment','average_reached_points','en','Average reached points'),('assessment','backtocallingtest','en','Back to the calling test'),('assessment','by','en','by'),('assessment','cancel_test','en','Suspend the test'),('assessment','cannot_edit_marks','en','There are already learners who ran the test. You can only change the marks if a score reporting date is enabled and the reporting date is not yet reached.'),('assessment','cannot_edit_test','en','You do not possess sufficient permissions to edit the test!'),('assessment','cannot_execute_test','en','You do not possess sufficient permissions to run the test!'),('assessment','cannot_export_qpl','en','You do not possess sufficient permissions to export the question pool!'),('assessment','cannot_export_test','en','You do not possess sufficient permissions to export the test!'),('assessment','cannot_maintain_test','en','You do not possess sufficient permissions to maintain the test!'),('assessment','cannot_read_questionpool','en','You do not possess sufficient permissions to read the question pool data!'),('assessment','cannot_save_metaobject','en','You do not possess sufficient permissions to save the meta data!'),('assessment','change_solution','en','Change suggested solution'),('assessment','checkbox_checked','en','Checked'),('assessment','checkbox_unchecked','en','Unchecked'),('assessment','circle','en','Circle'),('assessment','circle_click_center','en','Please click on center of the desired area.'),('assessment','circle_click_circle','en','Please click on a circle point of the desired area.'),('assessment','clientip','en','Client IP'),('assessment','close_text_hint','en','You can define a gap by entering <gap></gap> at the text position of your choice. Press the \'Create gaps\' button to add an editable form for every gap.'),('assessment','cloze_text','en','Cloze text'),('assessment','cloze_textgap_case_insensitive','en','Case insensitive'),('assessment','cloze_textgap_case_sensitive','en','Case sensitive'),('assessment','cloze_textgap_levenshtein_of','en','Levenshtein distance of %s'),('assessment','cloze_textgap_rating','en','Text gap rating'),('assessment','code','en','Code'),('assessment','concatenation','en','Concatenation'),('assessment','confirm_delete_all_user_data','en','Are you sure you want to delete all user data of the test?'),('assessment','confirm_delete_single_user_data','en','Are you sure you want to delete the test data of the selected users?'),('assessment','confirm_sync_questions','en','The question you changed is a copy which has been created for use with the active test. Do you want to change the original of the question too?'),('assessment','coordinates','en','Coordinates'),('assessment','correct_solution_is','en','The correct solution is'),('assessment','counter','en','Counter'),('assessment','create_date','en','Created'),('assessment','create_gaps','en','Create gaps'),('assessment','create_imagemap','en','Create an Image map'),('assessment','create_new','en','Create new'),('assessment','definition','en','Definition'),('assessment','delete_area','en','Delete area'),('assessment','delete_user_data','en','Delete test data of the selected users'),('assessment','description_maxchars','en','If nothing entered the maximum number of characters for this text answer is unlimited.'),('assessment','detail_ending_time_reached','en','You have reached the ending time of the test. The test isn\'t available since %s'),('assessment','detail_max_processing_time_reached','en','You have reached the maximum allowed processing time of the test!'),('assessment','detail_starting_time_not_reached','en','You have not reached the starting time of the test. The test will be available on %s'),('assessment','direct_feedback','en','Verify your solution'),('assessment','duplicate','en','Duplicate'),('assessment','duplicate_matching_values_selected','en','You have selected duplicate matching values!'),('assessment','duplicate_order_values_entered','en','You have entered duplicate order values!'),('assessment','duplicate_tst','en','Duplicate test'),('assessment','ects_allow_ects_grades','en','Show ECTS grade additional to received mark'),('assessment','ects_fill_out_all_values','en','Please enter a value for every ECTS grade!'),('assessment','ects_grade','en','ECTS grade'),('assessment','ects_grade_a','en','outstanding performance with only minor errors'),('assessment','ects_grade_a_short','en','EXCELLENT'),('assessment','ects_grade_b','en','above the average standard but with some errors'),('assessment','ects_grade_b_short','en','VERY GOOD'),('assessment','ects_grade_c','en','generally sound work with a number of notable errors'),('assessment','ects_grade_c_short','en','GOOD'),('assessment','ects_grade_d','en','fair but with significant shortcomings'),('assessment','ects_grade_d_short','en','SATISFACTORY'),('assessment','ects_grade_e','en','performance meets the minimum criteria'),('assessment','ects_grade_e_short','en','SUFFICIENT'),('assessment','ects_grade_f','en','considerable further work is required'),('assessment','ects_grade_f_short','en','FAIL'),('assessment','ects_grade_fx','en','some more work required before the credit can be awarded'),('assessment','ects_grade_fx_short','en','FAIL'),('assessment','ects_output_of_ects_grades','en','Output of ECTS grades'),('assessment','ects_range_error_a','en','Please enter a value between 0 and 100 for ECTS grade A!'),('assessment','ects_range_error_b','en','Please enter a value between 0 and 100 for ECTS grade B!'),('assessment','ects_range_error_c','en','Please enter a value between 0 and 100 for ECTS grade C!'),('assessment','ects_range_error_d','en','Please enter a value between 0 and 100 for ECTS grade D!'),('assessment','ects_range_error_e','en','Please enter a value between 0 and 100 for ECTS grade E!'),('assessment','ects_use_fx_grade','en','Use the \'FX\' grade, if failed participants reached at least'),('assessment','ects_use_fx_grade_part2','en','percent of the total points.'),('assessment','edit_content','en','Edit content'),('assessment','edit_properties','en','Edit properties'),('assessment','end_tag','en','End tag'),('assessment','ending_time_reached','en','You have reached the ending time'),('assessment','error_image_upload_copy_file','en','There was an error moving the uploaded image file to its destination folder!'),('assessment','error_image_upload_wrong_format','en','Please upload a valid image file! Supported image formats are gif, jpeg and png.'),('assessment','error_importing_question','en','There was an error importing the question(s) from the file you have selected!'),('assessment','error_open_image_file','en','Error opening an image file!'),('assessment','error_open_java_file','en','Error opening an java applet!'),('assessment','error_save_image_file','en','Error saving an image file!'),('assessment','eval_all_users','en','Evaluation for all users'),('assessment','eval_choice_result','en','The choice with the value %s is %s. You received %s points.'),('assessment','eval_cloze_result','en','Gap number %s with the value %s is %s. You received %s points.'),('assessment','eval_concatenation','en','Concatenation'),('assessment','eval_data','en','Evaluation Data'),('assessment','eval_found_selected_groups','en','Selected groups'),('assessment','eval_found_selected_users','en','Selected users'),('assessment','eval_imagemap_result','en','The image map selection is %s. You received %s points.'),('assessment','eval_java_result','en','The answer for the value %s is %s. You received %s points.'),('assessment','eval_legend_link','en','Please refer to the legend for the meaning of the column header symbols.'),('assessment','eval_matching_result','en','The matching pair %s - %s is %s. You received %s points.'),('assessment','eval_order_result','en','The order for the value %s is %s. You received %s points.'),('assessment','eval_search_groups','en','Groups'),('assessment','eval_search_roles','en','Roles'),('assessment','eval_search_term','en','Search term'),('assessment','eval_search_users','en','Users'),('assessment','eval_search_userselection','en','Search the users you want to add to the statistical evaluation'),('assessment','eval_selected_users','en','Evaluation for selected users'),('assessment','exp_eval_data','en','Export evaluation data as'),('assessment','exp_type_excel','en','Microsoft Excel (Intel x86)'),('assessment','exp_type_excel_mac','en','Microsoft Excel (IBM PPC)'),('assessment','exp_type_spss','en','Comma separated value (CSV)'),('assessment','export','en','Export'),('assessment','failed_official','en','failed'),('assessment','failed_short','en','failed'),('assessment','false','en','False'),('assessment','fill_out_all_answer_fields','en','Please fill out all answer text fields before you add a new one!'),('assessment','fill_out_all_matching_pairs','en','Please fill out all matching pairs before you add a new one!'),('assessment','fill_out_all_required_fields_add_answer','en','Please fill out all required fields before you add answers!'),('assessment','fill_out_all_required_fields_add_matching','en','Please fill out all required fields before you add a matching pair!'),('assessment','fill_out_all_required_fields_create_gaps','en','Please fill out all required fields before you create gaps!'),('assessment','fill_out_all_required_fields_upload_image','en','Please fill out all required fields before you upload images!'),('assessment','fill_out_all_required_fields_upload_imagemap','en','Please fill out all required fields before you upload image maps!'),('assessment','fill_out_all_required_fields_upload_material','en','Please fill out all required fields before you upload materials!'),('assessment','filter','en','Filter'),('assessment','filter_all_question_types','en','All question types'),('assessment','filter_all_questionpools','en','All question pools'),('assessment','filter_show_question_types','en','Show question types'),('assessment','filter_show_questionpools','en','Show question pools'),('assessment','gap','en','Gap'),('assessment','gap_definition','en','Gap definition'),('assessment','gap_selection','en','Gap selection'),('assessment','glossary_term','en','Glossary term'),('assessment','height','en','Height'),('assessment','imagemap','en','Image map'),('assessment','imagemap_file','en','Image map file'),('assessment','imagemap_source','en','Image map source'),('assessment','import_errors_qti','en','The were errors parsing the QTI file. The import was cancelled.'),('assessment','import_question','en','Import question(s)'),('assessment','insert_after','en','Insert after'),('assessment','insert_before','en','Insert before'),('assessment','internal_links','en','Internal Links'),('assessment','internal_links_update','en','Update'),('assessment','javaapplet','en','Java Applet'),('assessment','javaapplet_successful_saved','en','Your results have been saved successful!'),('assessment','javaapplet_unsuccessful_saved','en','There was an error saving the question results!'),('assessment','last_update','en','Last update'),('assessment','legend','en','Legend'),('assessment','locked','en','locked'),('assessment','log_create_new_test','en','Created new test'),('assessment','log_mark_added','en','Mark added'),('assessment','log_mark_changed','en','Mark changed'),('assessment','log_mark_removed','en','Mark removed'),('assessment','log_modified_test','en','The test was modified'),('assessment','log_no_test_fields_changed','en','Saved, but nothing changed'),('assessment','log_question_added','en','Question added at position'),('assessment','log_question_position_changed','en','Question position changed'),('assessment','log_question_removed','en','Question removed'),('assessment','log_selected_user_data_removed','en','Removed test data of user %s'),('assessment','log_user_answered_question','en','User answered a question and received %s points'),('assessment','log_user_data_removed','en','Removed all user data'),('assessment','maintenance','en','Maintenance'),('assessment','mark_schema','en','Mark schema'),('assessment','match_terms_and_definitions','en','Match terms and definitions'),('assessment','match_terms_and_pictures','en','Match terms and pictures'),('assessment','matches','en','matches'),('assessment','matching_pair','en','Matching pair'),('assessment','material','en','Material'),('assessment','material_download','en','Material to download'),('assessment','material_file','en','Material file'),('assessment','maxchars','en','Maximum number of characters'),('assessment','maximum_nr_of_tries_reached','en','You have reached the maximum number of passes for this test. The test cannot be entered!'),('assessment','meaning','en','Meaning'),('assessment','min_percentage_ne_0','en','You must define a minimum percentage of 0 percent! The mark schema wasn\'t saved.'),('assessment','negative_points_not_allowed','en','You are not allowed to enter negative points!'),('assessment','next_question_rows','en','Questions %d - %d of %d >>'),('assessment','no_passed_mark','en','You must set at least one mark to PASSED! The mark schema wasn\'t saved.'),('assessment','no_question_selected_for_move','en','Please check at least one question to move it!'),('assessment','no_questions_available','en','There are no questions available!'),('assessment','no_target_selected_for_move','en','You must select a target position!'),('assessment','no_user_or_group_selected','en','Please check an option what are you searching for (users, groups, roles)!'),('assessment','online_exam_show_answer_print_sheet','en','You have finished your online exam. To call the answers printview to print out your test results please press the &quot;Answers printview&quot; button.'),('assessment','online_exam_show_finish_test','en','You still have to call the final submission of your online exam. Please press the &quot;Finish the test&quot; button to call the final submission.'),('assessment','or','en','or'),('assessment','order_pictures','en','Order pictures'),('assessment','order_terms','en','Order terms'),('assessment','participants','en','Participants'),('assessment','pass','en','Pass'),('assessment','passed_official','en','passed'),('assessment','passed_short','en','passed'),('assessment','percentage_solved','en','You have solved %.2f percent of this question!'),('assessment','percentile','en','Percentile'),('assessment','picture','en','Picture'),('assessment','please_select','en','-- please select --'),('assessment','points','en','Points'),('assessment','points_short','en','Pt.'),('assessment','polygon','en','Polygon'),('assessment','polygon_click_next_or_save','en','Please click on the next point of the polygon or save the area. (It is not necessary to click again on the starting point of this polygon !)'),('assessment','polygon_click_next_point','en','Please click on the next point of the polygon.'),('assessment','polygon_click_starting_point','en','Please click on the starting point of the polygon.'),('assessment','postpone','en','Postpone question'),('assessment','postponed','en','postponed'),('assessment','preview','en','Preview'),('assessment','previous_question_rows','en','<< Questions %d - %d of %d'),('assessment','print_answers','en','Print Answers'),('assessment','qpl_assessment_no_assessment_of_questions','en','There is no assessment of questions available for the selected question. The question was never used in a test.'),('assessment','qpl_assessment_of_questions','en','Assessment of questions'),('assessment','qpl_assessment_total_of_answers','en','Total of answers'),('assessment','qpl_assessment_total_of_right_answers','en','Total percentage of right answers (percentage of maximum points)'),('assessment','qpl_confirm_delete_questions','en','Are you sure you want to delete the following question(s)? If you delete locked questions the results of all tests containing a locked question will be deleted too.'),('assessment','qpl_copy_insert_clipboard','en','The selected question(s) are copied to the clipboard'),('assessment','qpl_copy_select_none','en','Please check at least one question to copy it to the clipboard'),('assessment','qpl_delete_rbac_error','en','You have no rights to delete this question!'),('assessment','qpl_delete_select_none','en','Please check at least one question to delete it'),('assessment','qpl_display_fullsize_image','en','Click here to display the original image!'),('assessment','qpl_duplicate_select_none','en','Please check at least one question to duplicate it'),('assessment','qpl_edit_rbac_error','en','You have no rights to edit this question!'),('assessment','qpl_edit_select_multiple','en','Please check only one question for editing'),('assessment','qpl_edit_select_none','en','Please check one question for editing'),('assessment','qpl_export_select_none','en','Please check at least one question to export it'),('assessment','qpl_general_properties','en','General properties'),('assessment','qpl_imagemap_preview_missing','en','ILIAS could not create the temporary preview file containing the imagemap areas. The original image is shown instead. This means that either the webserver\'s ImageMagick tool isn\'t configured correctly or there are no write permissions in the temporary directory! Please contact your system administrator.'),('assessment','qpl_import_create_new_qpl','en','Import the questions in a new question pool'),('assessment','qpl_import_no_items','en','Error: The import file contains no questions!'),('assessment','qpl_import_non_ilias_files','en','Error: The import file contains QTI files which are not created by an ILIAS system. Please contact the ILIAS team to get in import filter for your QTI file format.'),('assessment','qpl_import_verify_found_questions','en','ILIAS found the following questions in the import file. Please select the questions you want to import.'),('assessment','qpl_move_insert_clipboard','en','The selected question(s) are marked for moving'),('assessment','qpl_move_select_none','en','Please check at least one question to select it for moving'),('assessment','qpl_online_property','en','Online'),('assessment','qpl_online_property_description','en','If the questionpool is not online, it cannot be used in tests.'),('assessment','qpl_paste_no_objects','en','There are no questions in the clipboard. Please copy or move a question into the clipboard.'),('assessment','qpl_paste_success','en','The question(s) have been pasted into the question pool'),('assessment','qpl_question_is_in_use','en','The question you are about to edit exists in %s test(s). If you change this question, you will NOT change the question(s) in the test(s), because the system creates a copy of a question when it is inserted in a test!'),('assessment','qpl_questions_deleted','en','Question(s) deleted.'),('assessment','qpl_select_file_for_import','en','You must select a file for import!'),('assessment','qt_cloze','en','Cloze Question'),('assessment','qt_imagemap','en','Image Map Question'),('assessment','qt_javaapplet','en','Java Applet Question'),('assessment','qt_matching','en','Matching Question'),('assessment','qt_multiple_choice','en','Multiple Choice Question'),('assessment','qt_multiple_choice_mr','en','Multiple Choice Question (multiple response)'),('assessment','qt_multiple_choice_sr','en','Multiple Choice Question (single response)'),('assessment','qt_ordering','en','Ordering Question'),('assessment','qt_text','en','Essay Question'),('assessment','question_saved_for_upload','en','The question was saved automatically in order to reserve hard disk space to store the uploaded file. If you cancel this form now, be aware that you must delete the question in the question pool if you do not want to keep it!'),('assessment','question_short','en','Q'),('assessment','question_title','en','Question title'),('assessment','question_type','en','Question Type'),('assessment','questionpool_not_entered','en','Please enter a name for a question pool!'),('assessment','questions_from','en','questions from'),('assessment','radio_set','en','Set'),('assessment','radio_unset','en','Unset'),('assessment','random_accept_sample','en','Accept sample'),('assessment','random_another_sample','en','Get another sample'),('assessment','random_selection','en','Random selection'),('assessment','rectangle','en','Rectangle'),('assessment','rectangle_click_br_corner','en','Please click on the bottom right corner of the desired area.'),('assessment','rectangle_click_tl_corner','en','Please click on the top left corner of the desired area.'),('assessment','region','en','Region'),('assessment','remove_question','en','Remove'),('assessment','remove_solution','en','Remove suggested solution'),('assessment','report_date_not_reached','en','You have not reached the reporting date. You will be able to view the test results at %s'),('assessment','reset_filter','en','Reset filter'),('assessment','result','en','Result'),('assessment','save_before_upload_imagemap','en','You must apply your changes before you can upload an image map!'),('assessment','save_before_upload_javaapplet','en','You must apply your changes before you can upload a Java applet!'),('assessment','save_edit','en','Save and edit content'),('assessment','save_finish','en','Finish the test'),('assessment','save_introduction','en','Go to introduction'),('assessment','save_next','en','Next'),('assessment','save_previous','en','Previous'),('assessment','save_text_answer_points','en','Save essay points'),('assessment','search_found_groups','en','Found groups'),('assessment','search_found_users','en','Found users'),('assessment','search_groups','en','Search Groups'),('assessment','search_roles','en','Search Roles'),('assessment','search_term','en','Search Term'),('assessment','search_users','en','Search Users'),('assessment','see_details_for_further_information','en','See details for suggested solutions'),('assessment','select_an_answer','en','--- Please select an answer ---'),('assessment','select_gap','en','Select gap'),('assessment','select_max_one_item','en','Please select one item only'),('assessment','select_one_submitted_test','en','Please select at least one user who finished the test'),('assessment','select_one_user','en','Please select at least one user'),('assessment','select_target_position_for_move_question','en','Please select a target position to move the question(s) and press one of the insert buttons!'),('assessment','select_tst_option','en','--- Please select a test ---'),('assessment','selected_image','en','Selected image'),('assessment','set_filter','en','Set filter'),('assessment','shape','en','Shape'),('assessment','shuffle_answers','en','Shuffle answers'),('assessment','solution_comment_count_system','en','Questions which are answered partially correct will be scored with 0 points'),('assessment','solution_comment_mc_scoring','en','Multiple choice questions with no checked answer will be scored with 0 points'),('assessment','solution_hint','en','Suggested solution'),('assessment','solution_order','en','Solution order'),('assessment','start_tag','en','Start tag'),('assessment','starting_time_not_reached','en','You have not reached the starting time'),('assessment','statistical_data','en','Statistical data'),('assessment','statistics','en','Statistics'),('assessment','suggested_solution_added_successfully','en','You successfully set a suggested solution!'),('assessment','symbol','en','Symbol'),('assessment','term','en','Term'),('assessment','test_cancelled','en','The test was cancelled'),('assessment','text_answers_saved','en','Essay points modified!'),('assessment','text_gap','en','Text gap'),('assessment','text_maximum_chars_allowed','en','Please do not enter more than a maximum of %s characters. Any characters above will be cut.'),('assessment','time_format','en','HH MM SS'),('assessment','too_many_empty_parameters','en','Please fill out your empty applet parameters before you add a new parameter!'),('assessment','true','en','True'),('assessment','tst_all_user_data_deleted','en','All user data of this test has been deleted!'),('assessment','tst_already_submitted','en','Test has already been finish and submitted.'),('assessment','tst_already_taken','en','Tests already taken'),('assessment','tst_answer_sheet','en','Test Answer Sheet'),('assessment','tst_answered_questions','en','Answered questions'),('assessment','tst_browse_for_questions','en','Browse for questions'),('assessment','tst_confirm_delete_results','en','You are about to delete all your previous entered question results of this test. This will reset all questions to its default values. Please note, that you cannot reset the number of passes, you already needed for this test. Do you really want to reset all your previous entered results?'),('assessment','tst_confirm_delete_results_info','en','Your previous entered question results were deleted. You have successfully reset the test!'),('assessment','tst_confirm_print','en','Press button to print all questions with points and solution. The question always appears at the end of a page.'),('assessment','tst_confirm_submit_answers','en','Please confirm your submission of your solution. You won\'t be able to revert your answers after pressing the submit button.'),('assessment','tst_count_correct_solutions','en','Count only complete solutions'),('assessment','tst_count_partial_solutions','en','Count partial solutions'),('assessment','tst_delete_all_user_data','en','Delete all user data'),('assessment','tst_delete_missing_mark','en','Please select at least one mark step to delete it'),('assessment','tst_delete_results','en','Reset test'),('assessment','tst_ending_time','en','Ending time'),('assessment','tst_enter_questionpool','en','Please enter a question pool name where the new question will be stored'),('assessment','tst_eval_no_anonymous_aggregation','en','No one has entered the test yet. There are no anonymous aggregated test results available.'),('assessment','tst_eval_results_by_pass','en','Detailed question results'),('assessment','tst_eval_show_answer','en','Show answer'),('assessment','tst_eval_total_finished','en','Total completed tests'),('assessment','tst_eval_total_finished_average_time','en','Average processing time for completed tests'),('assessment','tst_eval_total_passed','en','Total passed tests'),('assessment','tst_eval_total_passed_average_points','en','Average points of passed tests'),('assessment','tst_eval_total_persons','en','Total number of persons entered the test'),('assessment','tst_eval_user_answer','en','User answer'),('assessment','tst_finish_confirm_button','en','Yes, I want to finish the test'),('assessment','tst_finish_confirm_cancel_button','en','No, go back to the previous answer'),('assessment','tst_finish_confirmation_question','en','You are going to finish this test and reach the maximum number of allowed test passes. You won\'t be able to enter this test again to change your answers. Do you really want to finish the test?'),('assessment','tst_finished','en','Finished'),('assessment','tst_general_properties','en','General properties'),('assessment','tst_generate_xls','en','Generate Excel'),('assessment','tst_heading_scoring','en','Scoring'),('assessment','tst_hide_previous_results','en','Hide previous results'),('assessment','tst_hide_previous_results_description','en','If checked, the previous results of a learner will not be used as default values in future test passes'),('assessment','tst_hide_previous_results_hide','en','If you check the checkbox, your results from the previous test pass will be hidden'),('assessment','tst_hide_previous_results_introduction','en','If you\'re processing more than one pass of this test, your previous entered results will not be used as default values'),('assessment','tst_hide_title_points','en','Question title points'),('assessment','tst_hide_title_points_description','en','Hide the output of the maximum points in the question title'),('assessment','tst_import_no_items','en','Error: The import file contains no questions!'),('assessment','tst_import_verify_found_questions','en','ILIAS found the following questions in the test import file. Please select the questions you want to import with this test.'),('assessment','tst_in_use_edit_questions_disabled','en','The test is in use by %d user(s). You are not allowed to edit or move the questions in this test!'),('assessment','tst_insert_missing_question','en','Please select at least one question to insert it into the test!'),('assessment','tst_insert_questions','en','Are you sure you want to insert the following question(s) to the test?'),('assessment','tst_insert_questions_and_results','en','This test was already executed by %s user(s). Inserting questions to this test will remove all test results of these users. Are you sure you want to insert the following question(s) to the test?'),('assessment','tst_introduction','en','Introduction'),('assessment','tst_maintenance_information_no_results','en','There are no existing user datasets for the test. You need at least one user dataset to maintain the data..'),('assessment','tst_mark','en','Mark'),('assessment','tst_mark_create_new_mark_step','en','Create new mark step'),('assessment','tst_mark_create_simple_mark_schema','en','Create simple mark schema'),('assessment','tst_mark_minimum_level','en','Minimum level (in %)'),('assessment','tst_mark_official_form','en','Official form'),('assessment','tst_mark_passed','en','Passed'),('assessment','tst_mark_short_form','en','Short form'),('assessment','tst_maximum_points','en','Maximum points'),('assessment','tst_missing_author','en','You have not entered the authors name in the test properties! Please add an authors name.'),('assessment','tst_missing_marks','en','You do not have a mark schema in the test! Please add at least a simple mark schema to the test.'),('assessment','tst_missing_questions','en','You do not have any questions in the test! Please add at least one question into the test.'),('assessment','tst_missing_title','en','You have not entered a test title! Please go to the metadata section and enter a title.'),('assessment','tst_must_be_online_exam','en','This option is only available for online exams!'),('assessment','tst_no_marks_defined','en','There are no marks defined, please create at least a simple mark schema!'),('assessment','tst_no_more_available_questionpools','en','There are no more question pools available'),('assessment','tst_no_question_selected_for_removal','en','Please check at least one question to remove it!'),('assessment','tst_no_questionpools_for_random_test','en','You have not added a question pool for this random test! Please select at least one question pool.'),('assessment','tst_no_questions_available','en','There are no questions available! You need to create new questions or browse for questions.'),('assessment','tst_no_questions_for_random_test','en','You have taken no questions from the questionspools for this random test! Please take at least one question by setting the amount of questions for an added question pool.'),('assessment','tst_no_solution_available','en','Short answer question don\'t have a solution.'),('assessment','tst_no_taken_tests','en','There are no taken tests!'),('assessment','tst_no_tries','en','none'),('assessment','tst_nr_of_tries','en','Max. number of passes'),('assessment','tst_nr_of_tries_of_user','en','Passes already completed'),('assessment','tst_participating_users','en','Participating Users'),('assessment','tst_pass_best_pass','en','Score the best pass of a user'),('assessment','tst_pass_details','en','Show details'),('assessment','tst_pass_last_pass','en','Score the last pass of a user'),('assessment','tst_pass_scoring','en','Multiple pass scoring'),('assessment','tst_percent_solved','en','Percent solved'),('assessment','tst_print','en','Test & Assessment Print View'),('assessment','tst_print_date','en','Date of Print'),('assessment','tst_processing_time','en','Max. processing time'),('assessment','tst_properties','en','Test properties'),('assessment','tst_qst_order','en','Order'),('assessment','tst_qst_resetsolved','en','Set unsolved'),('assessment','tst_qst_result_sheet','en','Show result sheet'),('assessment','tst_qst_setsolved','en','Set solved'),('assessment','tst_qst_summary_text','en','Here you can see all questions. Use the bulbs to mark a question as solved.'),('assessment','tst_question_no','en','Order'),('assessment','tst_question_offer','en','Do you accept this sample or do you want another one?'),('assessment','tst_question_selection','en','Question selection'),('assessment','tst_question_selection_description','en','If you only use question pools which contain questions with an equal maximum number of available points, the test will have an equal number of maximum points for all participating users and thus the test results will be better comparable. It is recommended to choose this selection mode.'),('assessment','tst_question_selection_equal','en','Use question pools with equal maximum points'),('assessment','tst_question_solved_state','en','Solved'),('assessment','tst_question_title','en','Title'),('assessment','tst_question_type','en','Question type'),('assessment','tst_questions_inserted','en','Question(s) inserted!'),('assessment','tst_questions_removed','en','Question(s) removed!'),('assessment','tst_random_nr_of_questions','en','How many questions?'),('assessment','tst_random_qpl_unselected','en','There is at least one unselected question pool! You don\'t need to add a new question pool.'),('assessment','tst_random_questionpools','en','Source question pools'),('assessment','tst_random_select_questionpool','en','Select the question pool to choose the questions from'),('assessment','tst_random_test','en','Random test'),('assessment','tst_random_test_description','en','If checked, the questions of the test will be generated by a random generator'),('assessment','tst_reached_points','en','Reached points'),('assessment','tst_remove_questions','en','Are you sure you want to remove the following question(s) from the test?'),('assessment','tst_remove_questions_and_results','en','This test was already executed by %s user(s). Removing questions from this test will remove all test results of these users. Are you sure you want to remove the following question(s) from the test?'),('assessment','tst_report_after_question','en','Report the score after every question is answered'),('assessment','tst_report_after_test','en','Report the score after completing the whole test'),('assessment','tst_result_congratulations','en','Congratulations! You passed the test.'),('assessment','tst_result_sorry','en','Sorry, you failed the test.'),('assessment','tst_result_user_name','en','Test results for %s'),('assessment','tst_results','en','Test results'),('assessment','tst_results_aggregated','en','Aggregated test results'),('assessment','tst_results_back_evaluation','en','Back to evaluation overview'),('assessment','tst_results_back_introduction','en','Back to introduction'),('assessment','tst_results_back_overview','en','Back to results overview'),('assessment','tst_resume_test','en','Resume the test'),('assessment','tst_score_mcmr_questions','en','Multiple choice questions'),('assessment','tst_score_mcmr_use_scoring_system','en','Set the defined score, even when no answer was checked'),('assessment','tst_score_mcmr_zero_points_when_unanswered','en','Score 0 points when no answer was checked'),('assessment','tst_score_reporting','en','Score reporting'),('assessment','tst_score_reporting_date','en','Score reporting date'),('assessment','tst_score_type','en','Score reporting type'),('assessment','tst_select_file_for_import','en','You must select a file for import!'),('assessment','tst_select_questionpool','en','Please select a question pool to store the created question'),('assessment','tst_select_questionpools','en','Please select a question pool to store the imported questions'),('assessment','tst_select_random_questions','en','Select the question pool(s) and the amount of questions for the random test'),('assessment','tst_select_tsts','en','Please select a test for duplication'),('assessment','tst_selected_user_data_deleted','en','The test data of the selected user(s) was deleted successfully'),('assessment','tst_sequence','en','Sequence'),('assessment','tst_sequence_fixed','en','The sequence of questions is fixed'),('assessment','tst_sequence_postpone','en','The learner may postpone a question to the end of the test'),('assessment','tst_sequence_properties','en','Sequence properties'),('assessment','tst_session_settings','en','Session settings'),('assessment','tst_show_answer_print_sheet','en','Answers Printview'),('assessment','tst_show_answer_sheet','en','Show answers'),('assessment','tst_show_results','en','Show test results'),('assessment','tst_signature','en','Signature'),('assessment','tst_start_test','en','Start the test'),('assessment','tst_started','en','Test Started'),('assessment','tst_starting_time','en','Starting time'),('assessment','tst_stat_all_users','en','All users'),('assessment','tst_stat_choose_users','en','(select one or more users from the user list)'),('assessment','tst_stat_result_atimeofwork','en','Average time of work'),('assessment','tst_stat_result_distancemean','en','Distance to arithmetic mean'),('assessment','tst_stat_result_distancemedian','en','Distance to median'),('assessment','tst_stat_result_distancequintile','en','Distance to arithmetic quintile'),('assessment','tst_stat_result_firstvisit','en','First visit'),('assessment','tst_stat_result_lastvisit','en','Last visit'),('assessment','tst_stat_result_mark_median','en','Mark of median'),('assessment','tst_stat_result_median','en','Median of test result in points'),('assessment','tst_stat_result_pworkedthrough','en','Percent of total workload already worked through'),('assessment','tst_stat_result_qworkedthrough','en','Questions already worked through'),('assessment','tst_stat_result_rank_median','en','Rank of median'),('assessment','tst_stat_result_rank_participant','en','Rank of participant'),('assessment','tst_stat_result_resultsmarks','en','Test results in marks'),('assessment','tst_stat_result_resultspoints','en','Test results in points'),('assessment','tst_stat_result_specification','en','Specification of evaluation data'),('assessment','tst_stat_result_timeofwork','en','Time of work'),('assessment','tst_stat_result_total_participants','en','Total number of participants'),('assessment','tst_stat_selected_users','en','Selected participants'),('assessment','tst_stat_users_intro','en','Show statistical evaluation for'),('assessment','tst_status_completed','en','completed'),('assessment','tst_status_completed_more_tries_possible','en','Completed'),('assessment','tst_status_missing','en','There are required elements missing in this test!'),('assessment','tst_status_missing_elements','en','The following elements are missing:'),('assessment','tst_status_not_entered','en','Not started'),('assessment','tst_status_ok','en','The status of the test is OK. There are no missing elements.'),('assessment','tst_status_progress','en','In progress'),('assessment','tst_submit_answers','en','Yes, I want to submit my answers'),('assessment','tst_submit_answers_txt','en','You now may finish the test by submitting your answers.'),('assessment','tst_submit_results','en','Yes, I do confirm the submission.'),('assessment','tst_test_output','en','Test output'),('assessment','tst_text_count_system','en','Scoring system'),('assessment','tst_time_already_spent','en','Time already spent working'),('assessment','tst_total_questions','en','Total amount of questions'),('assessment','tst_total_questions_description','en','If you set this value, the amount of questions entered with the question pools are ignored. If you don\'t set this value, you need to enter an amount with every selected question pool!'),('assessment','tst_tst_date','en','Date of Test'),('assessment','tst_type','en','Test type'),('assessment','tst_type_changed','en','You have changed the test type. All previous processed tests have been deleted!'),('assessment','tst_type_comment','en','Warning: Changing the test type of a running test will delete all previous processed tests!'),('assessment','tst_types','en','Test types'),('assessment','tst_use_javascript','en','Use Javascript for drag and drop actions'),('assessment','tst_your_answer_was','en','Your answer was'),('assessment','tst_your_answers','en','These are your answers given to the following questions.'),('assessment','tst_your_ects_mark_is','en','Your ECTS grade is'),('assessment','tst_your_mark_is','en','Your mark is'),('assessment','tt_assessment','en','Assessment test'),('assessment','tt_online_exam','en','Online Exam'),('assessment','tt_self_assessment','en','Self assessment test'),('assessment','tt_varying_randomtest','en','Varying random test'),('assessment','unlimited','en','unlimited'),('assessment','unlock','en','Unlock'),('assessment','unlock_question','en','This question is in use of at least one test and there are %s submitted results. If you choose to unlock the question the complete test results of the affected users will be deleted after saving this question. If you want to use a changed version of this question but you do not want to delete the users test results, you can duplicate the question in the question pool and change that version.'),('assessment','upload_imagemap','en','Upload an Image map'),('assessment','uploaded_material','en','Uploaded Material'),('assessment','user_not_invited','en','You are not supposed to take this exam.'),('assessment','user_wrong_clientip','en','You don\'t have the right ip using this test.'),('assessment','warning_question_not_complete','en','The question is not complete!'),('assessment','warning_test_not_complete','en','The test is not complete!'),('assessment','when','en','when'),('assessment','width','en','Width'),('assessment','with_order','en','with order'),('assessment','working_time','en','Working Time'),('assessment','you_received_a_of_b_points','en','You received %d of %d possible points'),('bibitem','bibitem_add','en','Add'),('bibitem','bibitem_author','en','Author'),('bibitem','bibitem_av','en','AV'),('bibitem','bibitem_bibitem','en','Bibliographical information'),('bibitem','bibitem_book','en','Book'),('bibitem','bibitem_booktitle','en','Book title'),('bibitem','bibitem_catalog','en','Catalog'),('bibitem','bibitem_choose_index','en','Please select a bibliographical information:'),('bibitem','bibitem_cross_reference','en','Cross reference'),('bibitem','bibitem_delete','en','Delete'),('bibitem','bibitem_dissertation','en','Dissertation'),('bibitem','bibitem_edition','en','Edition'),('bibitem','bibitem_editor','en','Editor'),('bibitem','bibitem_entry','en','Entry'),('bibitem','bibitem_first_name','en','First name'),('bibitem','bibitem_grey_literature','en','Grey literature'),('bibitem','bibitem_how_published','en','How published'),('bibitem','bibitem_identifier','en','Identifier'),('bibitem','bibitem_inbook','en','In book'),('bibitem','bibitem_inproceedings','en','In proceedings'),('bibitem','bibitem_institution','en','Institution'),('bibitem','bibitem_internet','en','Internet'),('bibitem','bibitem_isbn','en','ISBN'),('bibitem','bibitem_issn','en','ISSN'),('bibitem','bibitem_journal','en','Journal'),('bibitem','bibitem_journal_article','en','Journal article'),('bibitem','bibitem_keyword','en','Keyword'),('bibitem','bibitem_label','en','Label'),('bibitem','bibitem_language','en','Language'),('bibitem','bibitem_last_name','en','Last name'),('bibitem','bibitem_manual','en','Manual'),('bibitem','bibitem_master_thesis','en','Master thesis'),('bibitem','bibitem_middle_name','en','Middle name'),('bibitem','bibitem_month','en','Month'),('bibitem','bibitem_new_element','en','New element'),('bibitem','bibitem_newspaper_article','en','Newspaper article'),('bibitem','bibitem_not_found','en','No bibliographical information available.'),('bibitem','bibitem_note','en','Note'),('bibitem','bibitem_number','en','Number'),('bibitem','bibitem_organization','en','Organization'),('bibitem','bibitem_pages','en','Pages'),('bibitem','bibitem_phd_thesis','en','PhD thesis'),('bibitem','bibitem_please_select','en','Please select'),('bibitem','bibitem_print','en','Print'),('bibitem','bibitem_proceedings','en','Proceedings'),('bibitem','bibitem_publisher','en','Publisher'),('bibitem','bibitem_save','en','Save'),('bibitem','bibitem_school','en','School'),('bibitem','bibitem_series','en','Series'),('bibitem','bibitem_series_editor','en','Editor'),('bibitem','bibitem_series_title','en','Series title'),('bibitem','bibitem_series_volume','en','Volume'),('bibitem','bibitem_technical_report','en','Technical report'),('bibitem','bibitem_type','en','Type'),('bibitem','bibitem_unpublished','en','Unpublished'),('bibitem','bibitem_url','en','URL'),('bibitem','bibitem_where_published','en','Where published'),('bibitem','bibitem_year','en','Year'),('chat','cancel_talk','en','Cancel address'),('chat','cancel_whisper','en','Cancel whisper'),('chat','chat_access_blocked','en','You are excluded from these chatrooms'),('chat','chat_active','en','Active'),('chat','chat_active_users','en','Further users (present)'),('chat','chat_add_allowed_hosts','en','Please add the address of the host(s) which are allowed to access the chat server.'),('chat','chat_add_external_ip','en','Please add the server\'s external IP.'),('chat','chat_add_internal_ip','en','Please add the server\'s internal IP.'),('chat','chat_add_moderator_password','en','Please add a moderator password.'),('chat','chat_add_port','en','Please add the server port.'),('chat','chat_add_private_chatroom','en','Add private chatroom'),('chat','chat_address','en','Address'),('chat','chat_address_user','en','Address'),('chat','chat_aqua','en','aqua'),('chat','chat_arial','en','Arial'),('chat','chat_black','en','black'),('chat','chat_block_user','en','Block'),('chat','chat_blocked','en','Restricted access'),('chat','chat_blocked_unlocked','en','Unlock'),('chat','chat_blocked_users','en','Blocked users'),('chat','chat_blue','en','blue'),('chat','chat_bold','en','Bold'),('chat','chat_chatroom_body','en','Chat room:'),('chat','chat_chatroom_rename','en','Edit chat room'),('chat','chat_chatrooms','en','Chat Rooms'),('chat','chat_color','en','Font color'),('chat','chat_delete_sure','en','Are you sure, you want to delete this chat room'),('chat','chat_drop_user','en','User kicked'),('chat','chat_edit','en','Edit chat'),('chat','chat_empty','en','Empty'),('chat','chat_enter_valid_username','en','Please enter a valid username.'),('chat','chat_face','en','Font type'),('chat','chat_fuchsia','en','fuchsia'),('chat','chat_gray','en','gray'),('chat','chat_green','en','green'),('chat','chat_hide_details','en','Hide details'),('chat','chat_html_export','en','HTML export'),('chat','chat_ilias','en','ILIAS Chat module'),('chat','chat_inactive','en','Inactive'),('chat','chat_input','en','Input'),('chat','chat_insert_message','en','Please insert a message'),('chat','chat_invitation_body','en','Invitation to chat from:'),('chat','chat_invitation_subject','en','Chat invitation'),('chat','chat_invite_user','en','Invite user'),('chat','chat_invited_by','en','Invited by'),('chat','chat_italic','en','Italic'),('chat','chat_kick_user_session','en','Kick user'),('chat','chat_kicked_from_room','en','You have been kicked from this chatroom, you cannot write messages.'),('chat','chat_level_all','en','All'),('chat','chat_level_debug','en','Debug'),('chat','chat_level_error','en','Error'),('chat','chat_level_fatal','en','Fatal'),('chat','chat_level_info','en','Info'),('chat','chat_lime','en','lime'),('chat','chat_maroon','en','maroon'),('chat','chat_messages_deleted','en','Messages have been deleted.'),('chat','chat_moderator_password','en','Moderator password'),('chat','chat_navy','en','navy'),('chat','chat_no_active_users','en','No further users present.'),('chat','chat_no_blocked','en','No blocked users available'),('chat','chat_no_connection','en','Cannot contact chat server.'),('chat','chat_no_delete_public','en','The public chat room cannot be deleted'),('chat','chat_no_recordings_available','en','No recordings are available at the moment.'),('chat','chat_no_refresh_public','en','The public chat room cannot be refreshed.'),('chat','chat_no_rename_public','en','The public room cannot be renamed'),('chat','chat_no_users_selected','en','You have to select one user.'),('chat','chat_no_write_perm','en','No permission to write'),('chat','chat_olive','en','olive'),('chat','chat_online_users','en','Further users (online)'),('chat','chat_private_message','en','Private Message'),('chat','chat_profile','en','Profile'),('chat','chat_public_room','en','Public chat room'),('chat','chat_purple','en','purple'),('chat','chat_recording_action','en','Action'),('chat','chat_recording_description','en','Description'),('chat','chat_recording_moderator','en','Moderator'),('chat','chat_recording_not_found','en','Could not find recording.'),('chat','chat_recording_running','en','Recording running'),('chat','chat_recording_started','en','Recording started'),('chat','chat_recording_stopped','en','Recording stopped'),('chat','chat_recording_time_frame','en','Time frame'),('chat','chat_recording_title','en','Title of recording'),('chat','chat_recordings','en','Recordings'),('chat','chat_recordings_delete_sure','en','Deleting records - are you sure?'),('chat','chat_recordings_deleted','en','Recordings were deleted.'),('chat','chat_recordings_select_one','en','Please choose recordings you would like to delete.'),('chat','chat_red','en','red'),('chat','chat_refresh','en','Refresh'),('chat','chat_refreshed','en','Deleted all messages of the selected chatrooms.'),('chat','chat_room_added','en','Added new chat room'),('chat','chat_room_name','en','Name of chat room'),('chat','chat_room_renamed','en','Chat room renamed.'),('chat','chat_room_select','en','Private chat room'),('chat','chat_rooms','en','Chat Rooms'),('chat','chat_rooms_deleted','en','Chat room(s) deleted.'),('chat','chat_select_one_room','en','Please select one chat room'),('chat','chat_server_allowed','en','Allowed hosts'),('chat','chat_server_allowed_b','en','(e.g. 192.168.1.1,192.168.1.2)'),('chat','chat_server_external_ip','en','Server external IP'),('chat','chat_server_internal_ip','en','Server internal IP'),('chat','chat_server_logfile','en','Log file path'),('chat','chat_server_loglevel','en','Log level'),('chat','chat_server_not_active','en','The chat server is not active'),('chat','chat_server_port','en','Server Port'),('chat','chat_server_settings','en','Chat server settings'),('chat','chat_settings_saved','en','Settings saved'),('chat','chat_show_details','en','Show details'),('chat','chat_silver','en','silver'),('chat','chat_start_recording','en','Start recording'),('chat','chat_status','en','Status'),('chat','chat_status_saved','en','The status has been changed'),('chat','chat_stop_recording','en','Stop recording'),('chat','chat_tahoma','en','Tahoma'),('chat','chat_teal','en','teal'),('chat','chat_times','en','Times'),('chat','chat_title_missing','en','Please insert a name.'),('chat','chat_to_chat_body','en','To the chat'),('chat','chat_type','en','Font face'),('chat','chat_unblocked_user','en','The selected user have been unlocked.'),('chat','chat_underlined','en','Underlined'),('chat','chat_unkick_user_session','en','Unkick user'),('chat','chat_user_already_blocked','en','The access for the selected user is already blocked.'),('chat','chat_user_blocked','en','Blocked access for the selected user.'),('chat','chat_user_dropped','en','User kicked'),('chat','chat_user_invited','en','Invited user to chat.'),('chat','chat_user_name','en','Username'),('chat','chat_whisper','en','Whisper'),('chat','chat_yellow','en','yellow'),('chat','chats','en','Chats'),('chat','my_chats','en','My Chats'),('common','3rd_party_software','en','3rd party software'),('common','DD.MM.YYYY','en','DD.MM.YYYY'),('common','HH:MM','en','HH:MM'),('common','absolute_path','en','Absolute Path'),('common','accept_usr_agreement','en','Accept user agreement?'),('common','access','en','Access'),('common','account_expires_body','en','Your account is limited, it will expire at:'),('common','account_expires_subject','en','[ILIAS 3] Your account expires'),('common','action','en','Action'),('common','action_aborted','en','Action aborted'),('common','actions','en','Actions'),('common','activate_assessment_logging','en','Activate Test&Assessment logging'),('common','activate_https','en','HTTPS handling by ILIAS<br>(Payment, Login)'),('common','activate_tracking','en','Activate Tracking'),('common','activation','en','Activation'),('common','active','en','Active'),('common','active_roles','en','Active Roles'),('common','add','en','Add'),('common','add_author','en','Add Author'),('common','add_condition','en','Create new association'),('common','add_member','en','Add Member'),('common','add_members','en','Add Members'),('common','add_note','en','Add Note'),('common','add_translation','en','Add translation'),('common','add_user','en','Add local user'),('common','added_new_condition','en','Created new condition.'),('common','adm_create_tax','en','Create taxonomy'),('common','adm_edit_permission','en','Change permission settings'),('common','adm_read','en','Read access to System settings'),('common','adm_visible','en','System settings are visible'),('common','adm_write','en','Edit Basic system settings'),('common','admin_panel','en','Administration Panel'),('common','admin_panel_disable','en','Switch Administration Commands Off'),('common','admin_panel_enable','en','Switch Administration Commands On'),('common','administrate','en','Administrate'),('common','administrate_users','en','Local user administration'),('common','administration','en','Administration'),('common','administrator','en','Administrator'),('common','adopt','en','Adopt'),('common','adopt_perm_from_template','en','Adopt permission settings from Role Template'),('common','agree_date','en','Agreed on'),('common','all_authors','en','All authors'),('common','all_global_roles','en','Global roles'),('common','all_lms','en','All learning modules'),('common','all_local_roles','en','Local roles (all)'),('common','all_objects','en','All Objects'),('common','all_roles','en','All Roles'),('common','all_tsts','en','All tests'),('common','all_users','en','All users'),('common','allow_assign_users','en','Allow user assignment for local administrators.'),('common','allow_register','en','Available in registration form for new users'),('common','alm','en','Learning Module AICC'),('common','alm_added','en','AICC Learning Module added'),('common','alm_create','en','Create AICC Learning Module'),('common','alm_delete','en','Delete AICC Learning Module'),('common','alm_edit_permission','en','Change permission settings'),('common','alm_join','en','Subscribe to AICC Learning Module'),('common','alm_leave','en','Unsubscribe from AICC Learning Module'),('common','alm_read','en','Read access to AICC Learning Module'),('common','alm_visible','en','AICC Learning Module is visible'),('common','alm_write','en','Edit AICC Learning Module'),('common','already_delivered_files','en','Already delivered files'),('common','and','en','and'),('common','announce','en','Announce'),('common','announce_changes','en','Announce Changes'),('common','answers','en','Answers'),('common','any_language','en','Any language'),('common','append_search','en','Append search results'),('common','application_completed','en','Application is complete'),('common','application_date','en','Application date'),('common','applied_users','en','Applied users'),('common','apply','en','Apply'),('common','apply_filter','en','Apply filter'),('common','appointment','en','Appointment'),('common','appointment_list','en','Appointment List'),('common','approve_date','en','Approved on'),('common','approve_recipient','en','Login ID of approver'),('common','archive','en','Archive'),('common','are_you_sure','en','Are you sure?'),('common','arrow_downright','en','Arrow from top to down right'),('common','articels_unread','en','Unread articels'),('common','as_of','en','as of'),('common','ascending_order','en','Ascending order'),('common','assessment_log','en','Create an Test&Assessment log for a specific time period'),('common','assessment_log_datetime','en','Date/Time'),('common','assessment_log_for_test','en','For test'),('common','assessment_log_log_entries','en','log entries'),('common','assessment_log_no_log','en','It is no log data available for the selected test and the selected time period!'),('common','assessment_log_open_calendar','en','Click here to open a calendar for date selection (JavaScript required!)'),('common','assessment_log_question','en','Question'),('common','assessment_log_select_test','en','--- Please select a test ---'),('common','assessment_log_text','en','Log message'),('common','assessment_log_user_answer','en','User answered a question'),('common','assessment_log_user_answers','en','Show user actions'),('common','assessment_settings','en','Test&Assessment Administration Settings'),('common','assessment_settings_reporting_language','en','Reporting language'),('common','assf_edit_permission','en','Change permission settings'),('common','assf_read','en','Access Test&Assessment Administration'),('common','assf_visible','en','Test&Assessment administration is visible'),('common','assf_write','en','Edit Test&Assessment administration'),('common','assign','en','Assign'),('common','assign_global_role','en','Assign to Global Role'),('common','assign_lo_forum','en','Assign LO Forum'),('common','assign_local_role','en','Assign to Local Role'),('common','assign_user_to_role','en','Assign User to Role'),('common','assigned_roles','en','Assigned Roles'),('common','assigned_users','en','Assigned Users'),('common','associated_user','en','associated User'),('common','associated_users','en','associated Users'),('common','at_least_one_style','en','At least one style must remain activated.'),('common','at_location','en','at location'),('common','attachment','en','Attachment'),('common','attachments','en','Attachments'),('common','attend','en','Attend'),('common','auth_active_roles','en','Global roles available on registration form'),('common','auth_configure','en','configure...'),('common','auth_default','en','Default setting'),('common','auth_default_mode_changed_to','en','Default authentication mode changed to'),('common','auth_edit_permission','en','Change permission settings'),('common','auth_ldap','en','LDAP'),('common','auth_ldap_desc','en','Authenticate users via LDAP server'),('common','auth_ldap_enable','en','Enable LDAP support'),('common','auth_ldap_not_configured','en','LDAP is not configured yet'),('common','auth_ldap_settings_saved','en','LDAP settings saved'),('common','auth_local','en','ILIAS database'),('common','auth_local_desc','en','Authenticate users via local ILIAS database (default)'),('common','auth_mode','en','Authentication mode'),('common','auth_mode_not_changed','en','(Nothing changed)'),('common','auth_radius','en','RADIUS'),('common','auth_radius_configure','en','Configure RADIUS-Authentication'),('common','auth_radius_desc','en','Authenticate users via RADIUS server'),('common','auth_radius_enable','en','Enable RADIUS support'),('common','auth_radius_not_configured','en','RADIUS is not configured yet'),('common','auth_radius_port','en','Port'),('common','auth_radius_server','en','Servers'),('common','auth_radius_server_desc','en','You may add multiple servers with commas separated. Servers are rotated in Round robin fashion when queried.'),('common','auth_radius_settings_saved','en','RADIUS settings saved'),('common','auth_radius_shared_secret','en','Shared secret'),('common','auth_read','en','Access authentication settings'),('common','auth_remark_non_local_auth','en','When selecting another authentication mode than ILIAS database, you may not change user\'s login name and password anymore.'),('common','auth_role_auth_mode','en','Authentication mode'),('common','auth_script','en','Custom'),('common','auth_script_desc','en','Authenticate users via external script'),('common','auth_select','en','Select authentication mode'),('common','auth_shib','en','Shibboleth'),('common','auth_shib_desc','en','Authenticate users via Shibboleth, can be used as second authentication method'),('common','auth_shib_not_configured','en','Shibboleth is not configured yet'),('common','auth_shibboleth','en','Shibboleth'),('common','auth_visible','en','Authentication settings are visible'),('common','auth_write','en','Edit authentication settings'),('common','author','en','Author'),('common','authors','en','Authors'),('common','auto_registration','en','Automatically approve registration'),('common','available','en','Available'),('common','available_languages','en','Available Languages'),('common','average_time','en','Average time'),('common','back','en','Back'),('common','basedn','en','BaseDN'),('common','basic_data','en','Basic Data'),('common','basic_settings','en','Basic Settings'),('common','benchmark','en','Benchmark'),('common','benchmark_settings','en','Benchmark Settings'),('common','benchmarks','en','Benchmarks'),('common','bib_data','en','Bibliographical Data'),('common','big_icon','en','Big Icon'),('common','bkm_import','en','Import bookmarks'),('common','bkm_import_ok','en','Successfully imported %d bookmarks and %d bookmark folders.'),('common','bkm_sendmail','en','Send as attachment'),('common','bm','en','Bookmark'),('common','bmf','en','Bookmark Folder'),('common','bookmark_edit','en','Edit Bookmark'),('common','bookmark_folder_edit','en','Edit Bookmark Folder'),('common','bookmark_folder_new','en','New Bookmark Folder'),('common','bookmark_new','en','New Bookmark'),('common','bookmark_target','en','Target'),('common','bookmarks','en','Bookmarks'),('common','bookmarks_of','en','Bookmarks of'),('common','bottom_frame','en','Main Content'),('common','btn_remove_system','en','Remove from System'),('common','btn_undelete','en','Undelete'),('common','by','en','By'),('common','by_location','en','Order by location'),('common','by_type','en','Order by type'),('common','bytes','en','Bytes'),('common','calendar','en','Calendar'),('common','cancel','en','Cancel'),('common','cannot_find_xml','en','Cannot find any XML-file.'),('common','cannot_uninstall_language_in_use','en','You cannot uninstall the language currently in use!'),('common','cannot_uninstall_systemlanguage','en','You cannot uninstall the system language!'),('common','cannot_unzip_file','en','Cannot unpack file.'),('common','cant_deactivate_if_users_assigned','en','You cannot deactivate a style if there are still users assigned to it.'),('common','cat','en','Category'),('common','cat_a','en','a Category'),('common','cat_add','en','Add Category'),('common','cat_added','en','Category added'),('common','cat_cat_administrate_users','en','Administrate local user accounts'),('common','cat_create','en','Create Category'),('common','cat_create_alm','en','Create AICC Learning Module'),('common','cat_create_cat','en','Create Category'),('common','cat_create_chat','en','Create Chat'),('common','cat_create_crs','en','Create Course'),('common','cat_create_dbk','en','Create Digilib Book'),('common','cat_create_exc','en','Create Exercise'),('common','cat_create_file','en','Upload File'),('common','cat_create_frm','en','Create Forum'),('common','cat_create_glo','en','Create Glossary'),('common','cat_create_grp','en','Create Group'),('common','cat_create_hlm','en','Create HACP Learning Module'),('common','cat_create_htlm','en','Create HTML Learning Module'),('common','cat_create_icrs','en','Create LearnLinc Seminar'),('common','cat_create_lm','en','Create ILIAS Learning Module'),('common','cat_create_mep','en','Create Media Pool'),('common','cat_create_qpl','en','Create Question Pool for Test'),('common','cat_create_sahs','en','Create SCORM/AICC Learning Module'),('common','cat_create_spl','en','Create Question Pool for Survey'),('common','cat_create_svy','en','Create Survey'),('common','cat_create_tst','en','Create Test'),('common','cat_create_url','en','Create URL'),('common','cat_create_webr','en','Create Web Resource'),('common','cat_delete','en','Delete Category'),('common','cat_edit','en','Edit Category'),('common','cat_edit_permission','en','Change permission settings'),('common','cat_new','en','New Category'),('common','cat_read','en','Read access to Category'),('common','cat_read_users','en','Read access to local user accounts'),('common','cat_visible','en','Category is visible'),('common','cat_write','en','Edit Category'),('common','categories','en','Categories'),('common','categories_imported','en','Category import complete.'),('common','cc','en','CC'),('common','cen','en','Centra Event'),('common','cen_add','en','Add Centra Event'),('common','cen_added','en','Centra Event added'),('common','cen_delete','en','Delete Centra Event'),('common','cen_edit','en','Edit Centra Event'),('common','cen_new','en','New Centra Event'),('common','cen_save','en','Save Centra Event'),('common','censorship','en','Censorship'),('common','centra_account','en','Centra Admin Account'),('common','centra_rooms','en','Centra Rooms'),('common','centra_server','en','Centra Server'),('common','chac_edit_permission','en','Change permission settings'),('common','chac_read','en','Read access to chat settings'),('common','chac_visible','en','Chat settings are visible'),('common','chac_write','en','Edit chat settings'),('common','change','en','Change'),('common','change_active_assignment','en','Change active assignment'),('common','change_assignment','en','Change assignment'),('common','change_existing_objects','en','Change existing Objects'),('common','change_header_title','en','Edit Header Title'),('common','change_lo_info','en','Change LO Info'),('common','change_metadata','en','Change Metadata'),('common','change_owner','en','Change owner'),('common','change_sort_direction','en','Change sort direction'),('common','changeable','en','Changeable'),('common','changed_to','en','changed to'),('common','chapter','en','Chapter'),('common','chapter_number','en','Chapter Number'),('common','chapter_title','en','Chapter Title'),('common','chat','en','Chat'),('common','chat_active_in','en','Active in chat:'),('common','chat_add','en','Add Chat'),('common','chat_delete','en','Delete chat'),('common','chat_edit_permission','en','Change permission settings'),('common','chat_invite','en','Chat'),('common','chat_moderate','en','Moderate chat'),('common','chat_new','en','New Chat'),('common','chat_read','en','Read/Write access to chat'),('common','chat_users_active','en','Active users'),('common','chat_visible','en','Chat is visible'),('common','chat_write','en','Edit chat'),('common','check','en','Check'),('common','check_all','en','Check all'),('common','check_langfile','en','Please check your language file'),('common','check_languages','en','Check Languages'),('common','check_link','en','Link check'),('common','check_link_desc','en','If enabled all external links in ILIAS learning modules will be checked if they are active.'),('common','check_max_allowed_packet_size','en','The page content size is too large.'),('common','check_user_accounts','en','Check user accounts'),('common','check_user_accounts_desc','en','If cron jobs are activated and this option is enabled, all users whose account expires will be informed by email.'),('common','check_web_resources','en','Check Web Resources'),('common','check_web_resources_desc','en','If enabled all Web Resources will be checked if they are active.'),('common','checked_files','en','Importable files'),('common','chg_language','en','Change Language'),('common','chg_password','en','Change Password'),('common','choose_language','en','Choose Your Language'),('common','choose_location','en','Choose Location'),('common','choose_only_one_language','en','Please choose only one language'),('common','chown_warning','en','Attention, you may loose access permissions on this object after changing the owner.'),('common','city','en','City, State'),('common','cleaned_file','en','File has been cleaned.'),('common','cleaning_failed','en','Cleaning failed.'),('common','clear','en','Clear'),('common','clear_clipboard','en','Empty Clipboard'),('common','client ip','en','Client IP'),('common','client','en','Client'),('common','client_id','en','Client ID'),('common','client_ip','en','Client IP'),('common','clipboard','en','Clipboard'),('common','close','en','Close'),('common','close_window','en','Close window'),('common','collapse','en','Collapse'),('common','comma_separated','en','Comma Separated'),('common','comment','en','comment'),('common','compose','en','Compose'),('common','condition','en','Condition'),('common','condition_already_assigned','en','Object already assigned.'),('common','condition_circle_created','en','This association is not possible, since the objects would interdepend.'),('common','condition_deleted','en','Condition deleted.'),('common','condition_finished','en','Finished'),('common','condition_not_finished','en','Not finished'),('common','condition_passed','en','Passed'),('common','condition_precondition','en','Preconditions:'),('common','condition_select_object','en','Please select one object.'),('common','condition_select_one','en','-Please select one condition-'),('common','condition_update','en','Save'),('common','conditions_updated','en','Conditions saved'),('common','confirm','en','Confirm'),('common','confirmation','en','Confirmation'),('common','conflict_handling','en','Conflict handling'),('common','cont_object','en','Object'),('common','contact_data','en','Contact Information'),('common','container','en','Container'),('common','content','en','Content'),('common','content_styles','en','Content Styles'),('common','context','en','Context'),('common','continue_work','en','Continue'),('common','contra','en','Contra'),('common','cookies_howto','en','How to enable cookies'),('common','copied_object','en','Copied object'),('common','copy','en','Copy'),('common','copyChapter','en','Copy'),('common','copyPage','en','Copy'),('common','copy_of','en','Copy of'),('common','copy_to','en','to'),('common','could_not_verify_account','en','Sorry, we could not verify your account. Please contact the system administrator.'),('common','count','en','Count'),('common','country','en','Country'),('common','course','en','Course'),('common','courses','en','Courses'),('common','create','en','Create'),('common','create_date','en','Created on'),('common','create_export_file','en','Create export file'),('common','create_in','en','Create in'),('common','create_stylesheet','en','Create Style'),('common','created','en','Create date'),('common','cron_forum_notification','en','Send forum notifications'),('common','cron_forum_notification_cron','en','regularly per cron job'),('common','cron_forum_notification_desc','en','If enabled, all users, who want to be informed about new posts in specified forum topics, will get notifications by e-mail.'),('common','cron_forum_notification_directly','en','directly on new entries'),('common','cron_forum_notification_never','en','never'),('common','cron_jobs','en','Cron jobs'),('common','cron_jobs_desc','en','A cron job is an automated process that operates at predefined time intervals. You will find informations about the setup in the \'INSTALL Documentation.'),('common','cron_lucene_index','en','Update Lucene search index'),('common','cron_lucene_index_info','en','If enabled, the lucene search index will be updated. Please configure the lucene server at \'Administration -> Search\'.'),('common','crs','en','Course'),('common','crs_a','en','a Course'),('common','crs_add','en','Add Course'),('common','crs_added','en','Course added'),('common','crs_all_questions_answered_successfully','en','You have successfully answered every question.'),('common','crs_archives','en','Archives'),('common','crs_at_least_one_admin','en','There has to be at least one course administrator.'),('common','crs_available','en','Available Courses'),('common','crs_container_link_not_allowed','en','It is not possible to link container objects'),('common','crs_create','en','Create Course'),('common','crs_create_alm','en','Create AICC Learning Module'),('common','crs_create_chat','en','Create Chat'),('common','crs_create_dbk','en','Create Digilib Book'),('common','crs_create_exc','en','Create Exercise'),('common','crs_create_file','en','Upload File'),('common','crs_create_fold','en','Create Folder'),('common','crs_create_frm','en','Create Forum'),('common','crs_create_glo','en','Create Glossary'),('common','crs_create_grp','en','Create Group'),('common','crs_create_hlm','en','Create HACP Learning Module'),('common','crs_create_htlm','en','Create HTML Learning Module'),('common','crs_create_icrs','en','Create LearnLinc Seminar'),('common','crs_create_lm','en','Create ILIAS Learning Module'),('common','crs_create_mep','en','Create Media Pool'),('common','crs_create_qpl','en','Create Question Pool for Test'),('common','crs_create_sahs','en','Create SCORM/AICC Learning Module'),('common','crs_create_spl','en','Create Question Pool for Survey'),('common','crs_create_svy','en','Create Survey'),('common','crs_create_tst','en','Create Test'),('common','crs_create_webr','en','Create Web Resource'),('common','crs_delete','en','Delete Course'),('common','crs_edit','en','Edit Course'),('common','crs_edit_learning_progress','en','Edit learning progress'),('common','crs_edit_permission','en','Change permission settings'),('common','crs_info','en','Info'),('common','crs_join','en','Join Course'),('common','crs_leave','en','Leave Course'),('common','crs_management_system','en','Course Management System'),('common','crs_member_not_passed','en','Not passed'),('common','crs_member_passed','en','Passed'),('common','crs_new','en','New Course'),('common','crs_no_content','en','Without content'),('common','crs_read','en','Read access to Course'),('common','crs_status_blocked','en','[Access refused]'),('common','crs_status_pending','en','[Waiting for registration]'),('common','crs_subscribers_assigned','en','Assigned new user(s)'),('common','crs_visible','en','Course is visible'),('common','crs_write','en','Edit Course'),('common','cumulative_time','en','Cumulative time'),('common','cur_number_rec','en','Current number of records'),('common','current_ip','en','Current IP:'),('common','current_ip_alert','en','Notice: if you enter a wrong ip you won\'t be able to access the system with this profile anymore.'),('common','current_password','en','Current Password'),('common','custom_icon_size_big','en','Custom icon size (big)'),('common','custom_icon_size_small','en','Custom icon size (small)'),('common','cut','en','Cut'),('common','cutPage','en','Cut'),('common','daily','en','daily'),('common','database','en','Database'),('common','database_version','en','Current Database Version'),('common','dataset','en','Item'),('common','date','en','Date'),('common','dateplaner','en','Calendar'),('common','day','en','Day'),('common','days','en','Days'),('common','days_of_period','en','Days of period'),('common','db_host','en','Database Host'),('common','db_name','en','Database Name'),('common','db_need_update','en','Database needs an update!'),('common','db_pass','en','Database Password'),('common','db_type','en','Database Type'),('common','db_user','en','Database User'),('common','db_version','en','Database Version'),('common','dbk','en','Digilib Book'),('common','dbk_a','en','a Digilib Book'),('common','dbk_add','en','Add Digilib Book'),('common','dbk_added','en','Digilib Book added'),('common','dbk_create','en','Create Digilib Book'),('common','dbk_delete','en','Delete Digilib Book'),('common','dbk_edit_learning_progress','en','Edit learning progress'),('common','dbk_edit_permission','en','Change permission settings'),('common','dbk_new','en','New Digilib Book'),('common','dbk_read','en','Read access to Digilib Book'),('common','dbk_visible','en','Digilib Book is visible'),('common','dbk_write','en','Edit Digilib Book'),('common','def_repository_view','en','Default repository view'),('common','default','en','Default'),('common','default_language','en','Default Language'),('common','default_perm_settings','en','Default permissions'),('common','default_role','en','Default Role'),('common','default_roles','en','Default Roles'),('common','default_skin','en','Default Skin'),('common','default_skin_style','en','Default Skin / Style'),('common','default_style','en','Default Style'),('common','default_value','en','Default Value'),('common','delete','en','Delete'),('common','delete_all','en','Delete All'),('common','delete_all_rec','en','Delete all records'),('common','delete_object','en','Delete Object(s)'),('common','delete_selected','en','Delete Selected'),('common','delete_selected_items','en','Delete selected items'),('common','delete_tr_data','en','Delete tracking data'),('common','deleted','en','Deleted'),('common','deleted_user','en','The user has been deleted'),('common','deliver','en','Deliver exercise'),('common','department','en','Department'),('common','desc','en','Description'),('common','descending_order','en','Descending order'),('common','description','en','Description'),('common','desired_password','en','Desired Password'),('common','desktop_items','en','Personal desktop items'),('common','details','en','Details'),('common','disable','en','disable'),('common','disable_check','en','Disable check'),('common','disabled','en','Disabled'),('common','domain','en','Domain'),('common','down','en','Down'),('common','download','en','Download'),('common','download_all_returned_files','en','Download all returned files'),('common','drafts','en','Drafts'),('common','drop','en','Drop'),('common','edit','en','Edit'),('common','edit_content','en','Edit content'),('common','edit_data','en','edit data'),('common','edit_operations','en','Edit Operations'),('common','edit_perm_ruleset','en','Edit default permission rules'),('common','edit_properties','en','Edit Properties'),('common','edit_roleassignment','en','Edit role assignment'),('common','edit_stylesheet','en','Edit Style'),('common','edited_at','en','Edited at'),('common','editor','en','Editor'),('common','email','en','Mail'),('common','email_footer','en','<br />powered by ILIAS'),('common','email_not_valid','en','The email address you entered is not valid!'),('common','enable','en','Enable'),('common','enable_calendar','en','Enable Calendar'),('common','enable_custom_icons','en','Enable custom icons'),('common','enable_custom_icons_info','en','This allows to define custom icons for single categories, courses and groups. The properties section of categories, courses and groups will provide an image upload dialog.'),('common','enable_hist_user_comments','en','Enable user comments in history'),('common','enable_hist_user_comments_desc','en','Give authors the opportunity to add comments to the history log of pages.'),('common','enable_js_edit','en','Enable JavaScript Editing'),('common','enable_js_edit_info','en','This option enables JavaScript editing for all content objects (ILIAS Learning Modules, Glossaries, Assessment Questions, Digilib Books)'),('common','enable_password_assistance','en','Enable password assistance'),('common','enable_registration','en','Enable new user registration'),('common','enabled','en','Enabled'),('common','enter_filename_deliver','en','Enter exercise to deliver'),('common','enumerate','en','Enumerate'),('common','err_1_param','en','Only 1 parameter!'),('common','err_2_param','en','Only 2 parameter!'),('common','err_count_param','en','Reason: Wrong parameter count'),('common','err_enter_current_passwd','en','Please enter your current password'),('common','err_in_line','en','Error in line'),('common','err_inactive','en','This account has not been activated. Please contact the system administrator for access.'),('common','err_invalid_port','en','Invalid port number'),('common','err_ldap_auth_failed','en','User not authenticated on LDAP server! Please ensure that you have the same password in ILIAS and LDAP'),('common','err_ldap_connect_failed','en','Connection to LDAP server failed! Please check your settings'),('common','err_ldap_search_failed','en','Connection to LDAP server failed! PLease check BaseDN and Search base'),('common','err_ldap_user_not_found','en','User not found on LDAP server! Please check attribute for login name and Objectclass'),('common','err_no_cookies','en','Please enable session cookies in your browser!'),('common','err_no_langfile_found','en','No language file found!'),('common','err_no_param','en','No parameter!'),('common','err_over_3_param','en','More than 3 parameters!'),('common','err_role_not_assignable','en','You cannot assign users to this role at this location'),('common','err_session_expired','en','Your session is expired!'),('common','err_unknown_error','en','Unknown Error'),('common','err_wrong_header','en','Reason: Wrong header.'),('common','err_wrong_login','en','Wrong Login'),('common','err_wrong_password','en','Wrong Password'),('common','error','en','Error'),('common','error_parser','en','Error starting the parser.'),('common','error_recipient','en','Error Recipient'),('common','exc','en','Exercise'),('common','exc_add','en','Add Exercise'),('common','exc_added','en','Exercise added'),('common','exc_ask_delete','en','Delete file?'),('common','exc_assign_usr','en','Assign User'),('common','exc_create','en','Create exercise'),('common','exc_date_not_valid','en','The date is not valid'),('common','exc_deassign_members','en','Delete member(s)'),('common','exc_delete','en','Delete exercise'),('common','exc_edit','en','New exercise created'),('common','exc_edit_exercise','en','Edit exercise'),('common','exc_edit_permission','en','Change permission settings'),('common','exc_edit_until','en','Edit until'),('common','exc_files','en','Files'),('common','exc_files_returned','en','Returned files'),('common','exc_groups','en','Groups'),('common','exc_header_members','en','Exercise (members)'),('common','exc_instruction','en','Work instructions'),('common','exc_members_already_assigned','en','These user are already assigned to this exercise'),('common','exc_members_assigned','en','Members assigned'),('common','exc_new','en','New exercise'),('common','exc_no_members_assigned','en','No members assigned'),('common','exc_notices','en','Notices'),('common','exc_obj','en','Exercise'),('common','exc_read','en','Access exercise'),('common','exc_roles','en','Roles'),('common','exc_save_changes','en','Save'),('common','exc_search_for','en','Search for'),('common','exc_select_one_file','en','Please select exactly one file.'),('common','exc_send_exercise','en','Send exercise'),('common','exc_sent','en','The exercise has been sent to the selected users'),('common','exc_status_returned','en','Returned'),('common','exc_status_saved','en','Exercise updated'),('common','exc_status_solved','en','Solved'),('common','exc_upload_error','en','Error uploading file'),('common','exc_users','en','Users'),('common','exc_visible','en','Exercise is visible'),('common','exc_write','en','Edit exercise'),('common','excs','en','Exercises'),('common','execute','en','Execute'),('common','exercise_time_over','en','You have reached the end time of the exercise. You cannot longer deliver files!'),('common','exp_html','en','Export HTML'),('common','expand','en','Expand'),('common','explorer_frame','en','Explorer Tree'),('common','export','en','Export'),('common','export_group_members','en','Export group members (to Microsoft Excel)'),('common','export_html','en','export as HTML file'),('common','export_xml','en','export as XML file'),('common','extt_configure','en','configure...'),('common','extt_edit_permission','en','Change permission settings'),('common','extt_ilinc','en','netucate LearnLinc'),('common','extt_ilinc_configure','en','Configure netucate LearnLinc'),('common','extt_ilinc_customer_id','en','Customer ID'),('common','extt_ilinc_desc','en','Provides virtual classrooms. Commercial service. For an account and further information please contact netucate at www.netucate.com'),('common','extt_ilinc_enable','en','Enable LearnLinc support'),('common','extt_ilinc_protocol_port','en','Protocol & Port'),('common','extt_ilinc_registrar_login','en','Login ID'),('common','extt_ilinc_registrar_passwd','en','Password'),('common','extt_ilinc_server','en','Server'),('common','extt_ilinc_settings_saved','en','LearnLinc settings saved'),('common','extt_ilinc_timeout','en','Max time waiting for server response'),('common','extt_name','en','Software/Service'),('common','extt_read','en','Access third party software settings'),('common','extt_remark','en','All software and services listed above are not part of ILIAS and do not fall under the ILIAS open source licence agreement.'),('common','extt_title_configure','en','Configure external software'),('common','extt_visible','en','Third party software settings are visible'),('common','extt_write','en','Configure third party software'),('common','faq_exercise','en','FAQ Exercise'),('common','fax','en','Fax'),('common','feedback','en','Feedback'),('common','feedback_recipient','en','Feedback Recipient'),('common','file','en','File'),('common','file_a','en','a File'),('common','file_add','en','Upload File'),('common','file_added','en','File uploaded'),('common','file_delete','en','Delete File'),('common','file_edit','en','Edit File'),('common','file_edit_permission','en','Change Permission settings'),('common','file_icon','en','File icon'),('common','file_info','en','File Information'),('common','file_is_infected','en','The file is infected by a virus.'),('common','file_new','en','New File'),('common','file_not_found','en','File Not Found'),('common','file_not_valid','en','File not valid!'),('common','file_notice','en','Please take note of the maximum file size of'),('common','file_read','en','Download File'),('common','file_valid','en','File is valid!'),('common','file_version','en','Version Provided in File'),('common','file_visible','en','File is visible'),('common','file_write','en','Edit File'),('common','filename','en','Filename'),('common','files','en','Files'),('common','files_location','en','Files Location'),('common','fill_out_all_required_fields','en','Please fill out all required fields'),('common','filter','en','Filter:'),('common','firstname','en','First name'),('common','flatview','en','Flat View'),('common','fold','en','Folder'),('common','fold_a','en','a Folder'),('common','fold_add','en','Add Folder'),('common','fold_added','en','Folder added'),('common','fold_create_alm','en','Create AICC Learning Module'),('common','fold_create_chat','en','Create Chat'),('common','fold_create_dbk','en','Create Digilib Book'),('common','fold_create_exc','en','Create Exercise'),('common','fold_create_file','en','Upload File'),('common','fold_create_fold','en','Create Folder'),('common','fold_create_frm','en','Create Forum'),('common','fold_create_glo','en','Create Glossary'),('common','fold_create_hlm','en','Create HACP Learning Module'),('common','fold_create_htlm','en','Create HTML Learning Module'),('common','fold_create_icrs','en','Create LearnLinc Seminar'),('common','fold_create_lm','en','Create ILIAS Learning Module'),('common','fold_create_mep','en','Create Media Pool'),('common','fold_create_qpl','en','Create Question Pool for Test'),('common','fold_create_sahs','en','Create SCORM/AICC Learning Module'),('common','fold_create_spl','en','Create Question Pool for Survey'),('common','fold_create_svy','en','Create Survey'),('common','fold_create_tst','en','Create Test'),('common','fold_create_webr','en','Create Web Resource'),('common','fold_delete','en','Delete Folder'),('common','fold_edit','en','Edit Folder'),('common','fold_edit_permission','en','Change Permission settings'),('common','fold_new','en','New Folder'),('common','fold_read','en','Read access to Folder'),('common','fold_visible','en','Folder is visible'),('common','fold_write','en','Edit Folder'),('common','folder','en','Folder'),('common','folders','en','Folders'),('common','force_accept_usr_agreement','en','You must accept the user agreement!'),('common','forename','en','Forename'),('common','forgot_password','en','Forgot password?'),('common','forgot_username','en','Forgot username?'),('common','form_empty_fields','en','Please complete these fields:'),('common','forum','en','Forum'),('common','forum_import','en','Forum import'),('common','forum_import_file','en','Import file'),('common','forum_notification','en','Notification'),('common','forum_notify_me','en','Notify me when a reply is posted'),('common','forum_post_replied','en','Your forum entry has been replied.'),('common','forums','en','Forums'),('common','forums_articles','en','Articles'),('common','forums_last_post','en','Last Article'),('common','forums_moderators','en','Moderators'),('common','forums_new_articles','en','New articles'),('common','forums_overview','en','Forums Overview'),('common','forums_threads','en','Topics'),('common','frm','en','Forum'),('common','frm_a','en','a Forum'),('common','frm_add','en','Add Forum'),('common','frm_added','en','Forum added'),('common','frm_article','en','article'),('common','frm_create','en','Create Forum'),('common','frm_delete','en','Delete Forum'),('common','frm_delete_post','en','Delete a post, censorship'),('common','frm_edit','en','Edit Forum'),('common','frm_edit_permission','en','Change permission settings'),('common','frm_edit_post','en','Add new thread, posting'),('common','frm_new','en','New Forum'),('common','frm_read','en','Read access to Forum'),('common','frm_status_new','en','Status \'New\''),('common','frm_status_new_desc','en','Please select a duration how long the status \'New\' is kept for forum entries.'),('common','frm_visible','en','Forum is visible'),('common','frm_write','en','Edit forum'),('common','from','en','From'),('common','fullname','en','Full name'),('common','functions','en','Functions'),('common','gdf_add','en','Add Definition'),('common','gdf_new','en','New Definition'),('common','gender','en','Gender'),('common','gender_f','en','Female'),('common','gender_m','en','Male'),('common','general_settings','en','General settings'),('common','generate','en','Generate'),('common','glo','en','Glossary'),('common','glo_a','en','a Glossary'),('common','glo_add','en','Add Glossary'),('common','glo_added','en','Glossary added'),('common','glo_create','en','Create Glossary'),('common','glo_delete','en','Delete Glossary'),('common','glo_edit_permission','en','Change permission settings'),('common','glo_join','en','Subscribe to Glossary'),('common','glo_leave','en','Unsubscribe from Glossary'),('common','glo_mode','en','Mode'),('common','glo_mode_desc','en','A virtual glossary works like a normal glossary. Additionally it contains the terms from all glossaries that are located on the same level in the repository like the virtual glossary (level) respectively from all glossaries that are located downwards in the repository from the position of the virtual glossary (subtree).'),('common','glo_mode_level','en','virtual (this level only)'),('common','glo_mode_normal','en','normal'),('common','glo_mode_subtree','en','virtual (entire subtree)'),('common','glo_new','en','New Glossary'),('common','glo_read','en','Read access to Glossary'),('common','glo_upload_file','en','File'),('common','glo_visible','en','Glossary is visible'),('common','glo_write','en','Edit Glossary'),('common','global','en','Global'),('common','global_default','en','Global Default'),('common','global_fixed','en','Global Fixed'),('common','global_settings','en','Global settings'),('common','global_user','en','Global users'),('common','glossaries','en','Glossaries'),('common','glossary','en','Glossary'),('common','glossary_added','en','Glossary added'),('common','group_access_denied','en','Access Denied'),('common','group_any_objects','en','No subobjects available'),('common','group_create_chat','en','Create chat'),('common','group_desc','en','Group Description'),('common','group_details','en','Group Details'),('common','group_filesharing','en','Group File Sharing'),('common','group_import','en','Group import'),('common','group_import_file','en','Import file'),('common','group_info_reg','en','Admission procedure'),('common','group_members','en','Group Members'),('common','group_memstat','en','Member Status'),('common','group_memstat_admin','en','Administrator'),('common','group_memstat_member','en','Member'),('common','group_name','en','Group name'),('common','group_new_registrations','en','Join requests'),('common','group_no_registration','en','Join group'),('common','group_no_registration_msg','en','You can join this group directly.'),('common','group_not_available','en','No group available'),('common','group_objects','en','Group Objects'),('common','group_password_registration_msg','en','If you know the group password, you can join this group. Please contact a group administrator, if you don\'t know the password.'),('common','group_registration','en','Registration'),('common','group_registration_expiration_date','en','Expiration Date'),('common','group_registration_expiration_time','en','Expiration Time'),('common','group_registration_expired','en','The time period for joining this group has expired'),('common','group_registration_expired_msg','en','The time period for joining this group has expired. Please contact a group administrator, if you want to join this group.'),('common','group_registration_mode','en','Registration procedure'),('common','group_registration_time','en','Registration period'),('common','group_req_direct','en','Join directly'),('common','group_req_password','en','Join with group password'),('common','group_req_registration','en','Request membership'),('common','group_req_registration_msg','en','You can request membership for this group. You will receive a message when a group administrator has accepted or declined your request.'),('common','group_reset','en','Reset Group Permissions'),('common','group_status','en','Group is'),('common','group_status_closed','en','Closed'),('common','group_status_desc','en','Determines the initial status of the group by using the permission settings from the role templates \'il_grp_status_closed\' or \'il_grp_status_open\'<br />Public: Group is visible; User may subscribe.<br />Closed: Group is not visible for non-members; User has to be invited by a member to join.'),('common','group_status_public','en','Public'),('common','groups','en','Groups'),('common','groups_overview','en','Groups Overview'),('common','grp','en','Group'),('common','grp_a','en','a Group'),('common','grp_add','en','Add Group'),('common','grp_add_member','en','Add Member(s)'),('common','grp_added','en','Group added'),('common','grp_already_applied','en','You have already applied to this group! Please wait for confirmation'),('common','grp_app_send_mail','en','Send applicant a message'),('common','grp_back','en','Back'),('common','grp_count_members','en','Number of members'),('common','grp_create','en','Create Group'),('common','grp_create_alm','en','Create AICC Learning Module'),('common','grp_create_chat','en','Create Chat'),('common','grp_create_dbk','en','Create Digilib Book'),('common','grp_create_exc','en','Create Exercise'),('common','grp_create_file','en','Upload File'),('common','grp_create_fold','en','Create Folder'),('common','grp_create_frm','en','Create Forum'),('common','grp_create_glo','en','Create Glossary'),('common','grp_create_hlm','en','Create HACP Learning Module'),('common','grp_create_htlm','en','Create HTML Learning Module'),('common','grp_create_icrs','en','Create LearnLinc Seminar'),('common','grp_create_lm','en','Create ILIAS Learning Module'),('common','grp_create_mep','en','Create Media Pool'),('common','grp_create_qpl','en','Create Question Pool for Test'),('common','grp_create_sahs','en','Create SCORM/AICC Learning Module'),('common','grp_create_spl','en','Create Question Pool for Survey'),('common','grp_create_svy','en','Create Survey'),('common','grp_create_tst','en','Create Test'),('common','grp_create_webr','en','Create Web Resource'),('common','grp_delete','en','Delete Group'),('common','grp_deleted_export_files','en','Deleted selected files.'),('common','grp_dismiss_member','en','Are you sure you want to dismiss the following member(s) from the group?'),('common','grp_dismiss_myself','en','Are you sure you want to leave the group?'),('common','grp_edit','en','Edit Group'),('common','grp_edit_permission','en','Change permission settings'),('common','grp_err_administrator_required','en','Member could not be removed, at least one administrator per group is required !'),('common','grp_err_at_least_one_groupadministrator_is_needed','en','Each group needs at least one group administrator'),('common','grp_err_error','en','An error occurred'),('common','grp_err_last_member','en','Last member could not be removed, please delete group instead.'),('common','grp_err_member_could_not_be_removed','en','Member could not be removed. Please verify all dependencies of this user.'),('common','grp_err_no_permission','en','You do not possess the permissions for this operation.'),('common','grp_err_registration_data','en','Please enter a password, the expiration date and time for a valid joining period !'),('common','grp_header_edit_members','en','Edit members'),('common','grp_join','en','User may join Group'),('common','grp_leave','en','User may leave Group'),('common','grp_list_users','en','List users'),('common','grp_mail_body_new_subscription','en','You have been subscribed to this group.'),('common','grp_mail_body_subscription_cancelled','en','Your membership has been cancelled.'),('common','grp_mail_subj_new_subscription','en','New subscription'),('common','grp_mail_subj_subscription_cancelled','en','Subscription cancelled'),('common','grp_mem_change_status','en','Change member status'),('common','grp_mem_leave','en','Dismiss member from group'),('common','grp_mem_send_mail','en','Send member a message'),('common','grp_msg_applicants_assigned','en','Applicant(s) assigned as group member(s)'),('common','grp_msg_applicants_removed','en','Applicant(s) removed from list.'),('common','grp_msg_member_assigned','en','User(s) assigned as group member(s)'),('common','grp_msg_membership_annulled','en','You left the group'),('common','grp_name_exists','en','There is already a group with this name! Please choose another name.'),('common','grp_new','en','New Group'),('common','grp_new_search','en','New search'),('common','grp_no_groups_selected','en','Please select a group'),('common','grp_no_results_found','en','No results found'),('common','grp_no_roles_selected','en','Please select a role'),('common','grp_options','en','Options'),('common','grp_read','en','Read access to Group'),('common','grp_register','en','Join'),('common','grp_registration','en','Join group'),('common','grp_registration_completed','en','You have joined the group'),('common','grp_search_enter_search_string','en','Please enter a search string'),('common','grp_search_members','en','User search'),('common','grp_select_one_file','en','Please select one file.'),('common','grp_select_one_file_only','en','Please select exactly one file.'),('common','grp_visible','en','Group is visible'),('common','grp_write','en','Edit Group'),('common','guest','en','Guest'),('common','guests','en','Guests'),('common','header_title','en','Header Title'),('common','help','en','Help'),('common','hide','en','hide'),('common','hide_details','en','Hide Details'),('common','hide_private_notes','en','Hide private notes'),('common','hide_public_notes','en','Hide public notes'),('common','hide_structure','en','Disable Structured-View'),('common','hist_dbk:pg_create','en','Page created'),('common','hist_dbk:pg_cut','en','Page cut out of chapter \"%1\" [%2]'),('common','hist_dbk:pg_paste','en','Page pasted into chapter \"%1\" [%2]'),('common','hist_dbk:pg_update','en','Page changed'),('common','hist_file_create','en','File created, file name is \"%1\".'),('common','hist_file_replace','en','File replaced, new file name is \"%1\", new version is %2.'),('common','hist_lm:pg_create','en','Page created'),('common','hist_lm:pg_cut','en','Page cut out of chapter \"%1\" [%2]'),('common','hist_lm:pg_paste','en','Page pasted into chapter \"%1\" [%2]'),('common','hist_lm:pg_update','en','Page changed'),('common','hist_no_entries','en','No entries in history.'),('common','hist_webr_add','en','Added new Web Resource with title: \"%1\"'),('common','hist_webr_delete','en','Deleted Web Resource. Title: \"%1\"'),('common','hist_webr_update','en','Update Web Resource. Title: \"%1\"'),('common','history','en','History'),('common','hits_per_page','en','Hits/Page'),('common','hlm','en','Learning Module HACP'),('common','hlm_added','en','HACP Learning Module added'),('common','hlm_create','en','Create HACP Learning Module'),('common','hlm_delete','en','Delete HACP Learning Module'),('common','hlm_edit_permission','en','Change permission settings'),('common','hlm_join','en','Subscribe to HACP Learning Module'),('common','hlm_leave','en','Unsubscribe from HACP Learning Module'),('common','hlm_read','en','Read access to HACP Learning Module'),('common','hlm_visible','en','HACP Learning Module is visible'),('common','hlm_write','en','Edit HACP Learning Module'),('common','hobby','en','Interests/Hobbies'),('common','home','en','Public Area'),('common','host','en','Host'),('common','hours','en','hour(s)'),('common','hours_of_day','en','Hours of day'),('common','htlm','en','Learning Module HTML'),('common','htlm_add','en','Add HTML Learning Module'),('common','htlm_delete','en','Delete HTML Learning Module'),('common','htlm_edit','en','Edit HTML Learning Module Properties'),('common','htlm_edit_learning_progress','en','Edit learning progress'),('common','htlm_edit_permission','en','Change permission settings'),('common','htlm_new','en','New HTML Learning Module'),('common','htlm_read','en','Read HTML Learning Module'),('common','htlm_visible','en','HTML Learning Module is visible'),('common','htlm_write','en','Edit HTML Learning Module'),('common','http','en','HTTP'),('common','http_not_possible','en','This server is not supporting http requests.'),('common','http_path','en','Http Path'),('common','https_not_possible','en','This server is not supporting HTTPS connnections.'),('common','i2passwd','en','ILIAS 2 password'),('common','icla','en','LearnLinc Classroom'),('common','icla_add','en','Add LearnLinc Classroom'),('common','icla_deleted','en','LearnLinc classroom(s) deleted'),('common','icla_edit','en','Edit LearnLinc Classroom'),('common','icla_new','en','New LearnLinc Classroom'),('common','icon_settings','en','Icon Settings'),('common','icons_in_header','en','Header row'),('common','icons_in_item_rows','en','Item rows'),('common','icons_in_typed_lists','en','Icon position in item lists'),('common','icons_in_typed_lists_info','en','Determines where to display icons in item lists that are ordered by type within the repository and on the personal desktop.'),('common','icrs','en','LearnLinc Seminar'),('common','icrs_add','en','Add LearnLinc Seminar'),('common','icrs_added','en','LearnLinc Seminar added'),('common','icrs_create_icla','en','Create LearnLinc Classrooms'),('common','icrs_delete','en','Delete LearnLinc Seminar'),('common','icrs_edit','en','Edit LearnLinc Seminar'),('common','icrs_edit_permission','en','Edit permission settings'),('common','icrs_join','en','User may join LearnLinc Seminar'),('common','icrs_leave','en','User may leave LearnLinc Seminar'),('common','icrs_new','en','New LearnLinc Seminar'),('common','icrs_read','en','Read access to LearnLinc Seminar'),('common','icrs_visible','en','LearnLinc Seminar is visible'),('common','icrs_write','en','Edit LearnLinc Seminar'),('common','id','en','ID'),('common','identifier','en','identifier'),('common','if_no_title_then_filename','en','Leave blank to display the file name'),('common','if_no_title_then_url','en','Leave blank to display the URL'),('common','ignore_on_conflict','en','Ignore on conflict'),('common','il_chat_moderator','en','Chat moderator'),('common','il_crs_admin','en','Course administrator'),('common','il_crs_member','en','Course member'),('common','il_crs_tutor','en','Course tutor'),('common','il_frm_moderator','en','Forum moderator'),('common','il_grp_admin','en','Group administrator'),('common','il_grp_member','en','Group member'),('common','il_icrs_admin','en','LearnLinc Seminar administrator'),('common','il_icrs_member','en','LearnLinc Seminar member'),('common','ilias_version','en','ILIAS version'),('common','ilinc_add_user','en','Add user(s)'),('common','ilinc_classroom_always_open','en','Classroom is always open'),('common','ilinc_classroom_closed','en','Classroom is closed'),('common','ilinc_classroom_open','en','Classroom is open'),('common','ilinc_classrooms','en','LearnLinc Classrooms'),('common','ilinc_coursemember_status','en','Attending as'),('common','ilinc_courses','en','LearnLinc Seminars'),('common','ilinc_dismiss_member','en','Are you sure you want to dismiss the following user(s) from the LearnLinc Seminar?'),('common','ilinc_dismiss_myself','en','Are you sure you want to dismiss yourself from the LearnLinc Seminar?'),('common','ilinc_docent','en','Instructor'),('common','ilinc_err_administrator_required','en','Member could not be removed, at least one administrator per LearnLinc Seminar is required !'),('common','ilinc_err_at_least_one_groupadministrator_is_neede','en','Each LearnLinc Seminar needs at least one administrator'),('common','ilinc_err_last_member','en','Last member could not be removed, please delete LearnLinc Seminar instead.'),('common','ilinc_err_member_could_not_be_removed','en','User could not be removed. Please verify all dependencies of that user.'),('common','ilinc_err_no_permission','en','You do not possess the permissions for this operation.'),('common','ilinc_err_registration_data','en','Please enter a password, the expiration date and time for a valid registration period !'),('common','ilinc_header_edit_users','en','Edit list of involved users'),('common','ilinc_id','en','iLinc ID'),('common','ilinc_involved_users','en','Involved Users'),('common','ilinc_mail_body_new_subscription','en','You have been subscribed to this LearnLinc Seminar.'),('common','ilinc_mail_body_subscription_cancelled','en','Your membership has been cancelled.'),('common','ilinc_mail_subj_new_subscription','en','New subscription'),('common','ilinc_mail_subj_subscription_cancelled','en','Subscription cancelled'),('common','ilinc_manage_course_documents','en','Materials'),('common','ilinc_mem_change_status','en','Change user\'s status'),('common','ilinc_mem_leave','en','Dismiss user from LearnLinc Seminar'),('common','ilinc_mem_send_mail','en','Send user a message'),('common','ilinc_msg_joined','en','You joined the LearnLinc Seminar'),('common','ilinc_msg_member_assigned','en','User(s) assigned'),('common','ilinc_msg_membership_annulled','en','Subscription cancelled'),('common','ilinc_no_docent_assigned','en','Not assigned'),('common','ilinc_notset','en','Not set'),('common','ilinc_remark','en','Remark'),('common','ilinc_server_not_active','en','iLinc server not activated'),('common','ilinc_student','en','Participant'),('common','ilinc_upload_pic_linktext','en','Upload'),('common','ilinc_upload_pic_text','en','To display your picture in LearnLinc Seminars please use this link:'),('common','image','en','Image'),('common','image_gen_unsucc','en','Image generation unsuccessful. Contact your system administrator and verify the convert path.'),('common','import','en','Import'),('common','import_alm','en','Import AICC Package'),('common','import_cat_localrol','en','Create local role for every new category'),('common','import_cat_table','en','The following table is only meaningful if the checkbox is set'),('common','import_categories','en','Import Categories'),('common','import_dbk','en','Import Digilib Book'),('common','import_failure_log','en','Import failure log'),('common','import_file','en','Import File'),('common','import_file_not_valid','en','The import file is not valid.'),('common','import_finished','en','Number of imported messages.'),('common','import_forum_finished','en','The forum has been imported.'),('common','import_glossary','en','Import Glossary'),('common','import_grp','en','Import group'),('common','import_grp_finished','en','Imported group without any error'),('common','import_hlm','en','Import HACP Package'),('common','import_lm','en','Import ILIAS Learning module'),('common','import_qpl','en','Import Question pool Test'),('common','import_questions_into_qpl','en','Import question(s) into question pool'),('common','import_root_user','en','Import Root User'),('common','import_sahs','en','Import SCORM/AICC Package'),('common','import_spl','en','Import Question pool Survey'),('common','import_svy','en','Import Survey'),('common','import_tst','en','Import Test'),('common','import_users','en','Import Users'),('common','import_warning_log','en','Import warning log'),('common','important','en','Important'),('common','imported','en','imported'),('common','in','en','in'),('common','in_use','en','User Language'),('common','inactive','en','Inactive'),('common','inbox','en','Inbox'),('common','include_local','en','include local'),('common','info','en','Information'),('common','info_access_and_status_info','en','Access- and Statusinformation'),('common','info_access_permissions','en','Access permissions'),('common','info_assign_sure','en','Are you sure you want to assign the following user(s)?'),('common','info_assigned','en','assigned'),('common','info_available_roles','en','Available roles'),('common','info_change_user_view','en','Change User'),('common','info_delete_sure','en','Are you sure that you want to delete the following item(s)?'),('common','info_deleted','en','Object(s) Deleted'),('common','info_enter_login_or_id','en','<i>(Enter login name or user ID)</i>'),('common','info_err_user_not_exist','en','User with that login name or user_id does not exists'),('common','info_from_role','en','Granted by role/ownership'),('common','info_is_member','en','User is member'),('common','info_is_not_member','en','User is not a member'),('common','info_not_assigned','en','not assigned'),('common','info_owner_of_object','en','Owner of object'),('common','info_permission_origin','en','Original position'),('common','info_permission_source','en','effective from*'),('common','info_remark_interrupted','en','Role is interrupted at this position. The role\'s default permission settings in effect are located in that position.'),('common','info_short','en','Info'),('common','info_status_info','en','Permissions of User'),('common','info_trash','en','Deleted Objects'),('common','info_view_of_user','en','User'),('common','inform_user_mail','en','Send email to inform user about changes'),('common','information_abbr','en','Info'),('common','ingmedia_all_offers','en','All LE'),('common','ingmedia_back_to_le','en','Back to LE'),('common','ingmedia_good_afternoon','en','Good afternoon'),('common','ingmedia_good_evening','en','Good evening'),('common','ingmedia_good_morning','en','Good morning'),('common','ingmedia_good_night','en','Good night'),('common','ingmedia_hello','en','Hello'),('common','ingmedia_info_about_work1','en','On'),('common','ingmedia_info_about_work2','en',', you worked here for the last time. You can continue there by clicking here:'),('common','ingmedia_level_zero','en','Level up'),('common','ingmedia_pers_dates','en','Personal data'),('common','ingmedia_reg_fo','en','Registered forums'),('common','ingmedia_reg_le','en','Registered LE'),('common','ingmedia_use_info1','en','Here you can change your personal data. Except for the INGMEDIA username and password, you do not have to fill out other fields. Remember that lost passwords can only be returned by email (!).'),('common','ingmedia_use_info2','en','It is not allowed to use special characters (like , ; . = - * + #) in your INGMEDIA username and password. To be allowed to use the INGMEDIA platform, you must accept the user agreement.'),('common','ingmedia_use_info3','en','Please select one of the links in the side bar.'),('common','ingmedia_use_title','en','Change user data.'),('common','ingmedia_user_agree','en','Agreement'),('common','ingmedia_vis_le','en','Visited LE'),('common','ingmedia_visited_le','en','All LE you have visited so far.'),('common','ingmedia_welcome','en',', welcome to your personal desktop!!'),('common','inifile','en','Ini-File'),('common','input_error','en','Input error'),('common','insert','en','Insert'),('common','insert_object_here','en','Insert object here'),('common','inst_id','en','Installation ID'),('common','inst_info','en','Installation Info'),('common','inst_name','en','Installation Name'),('common','install','en','Install'),('common','install_local','en','Install With Local'),('common','installed','en','Installed'),('common','installed_local','en','Installed With Local'),('common','institution','en','Institution'),('common','internal_local_roles_only','en','Local roles (only autogenerated)'),('common','internal_system','en','Internal system'),('common','ip_address','en','IP Address'),('common','is_already_your','en','is already your'),('common','item','en','Item'),('common','join','en','Join'),('common','kb','en','KByte'),('common','keywords','en','Keywords'),('common','lang_cs','en','Czech'),('common','lang_da','en','Danish'),('common','lang_dateformat','en','Y-m-d'),('common','lang_de','en','German'),('common','lang_el','en','Greek'),('common','lang_en','en','English'),('common','lang_es','en','Spanish'),('common','lang_fi','en','Finnish'),('common','lang_fr','en','French'),('common','lang_ge','en','Georgian'),('common','lang_hu','en','Hungarian'),('common','lang_id','en','Indonesian'),('common','lang_it','en','Italian'),('common','lang_ja','en','Japanese'),('common','lang_lt','en','Lithuanian'),('common','lang_nl','en','Dutch'),('common','lang_no','en','Norwegian'),('common','lang_path','en','Language Path'),('common','lang_pl','en','Polish'),('common','lang_pt','en','Portuguese'),('common','lang_ro','en','Romanian'),('common','lang_sep_decimal','en','.'),('common','lang_sep_thousand','en',','),('common','lang_sq','en','Albanian'),('common','lang_sr','en','Serbian'),('common','lang_sv','en','Swedish'),('common','lang_timeformat','en','H:i:s'),('common','lang_uk','en','Ukrainian'),('common','lang_version','en','1'),('common','lang_vi','en','Vietnamese'),('common','lang_xx','en','Custom'),('common','lang_zh','en','Simplified Chinese'),('common','langfile_found','en','Language file found'),('common','language','en','Language'),('common','language_not_installed','en','is not installed. Please install that language first'),('common','languages','en','Languages'),('common','languages_already_installed','en','Selected language(s) are already installed'),('common','languages_already_uninstalled','en','Selected language(s) are already uninstalled'),('common','languages_updated','en','All installed languages have been updated'),('common','last_access','en','Last access:'),('common','last_change','en','Last Change'),('common','last_edited_on','en','Last edited on'),('common','last_login','en','Last login'),('common','last_update','en','Last Update'),('common','last_visit','en','Last Visit'),('common','lastname','en','Last name'),('common','launch','en','Launch'),('common','ldap','en','LDAP'),('common','ldap_basedn','en','LDAP BaseDN'),('common','ldap_configure','en','Configure LDAP Authentication'),('common','ldap_edit_permission','en','Change permission settings'),('common','ldap_login_key','en','Attribute for login name'),('common','ldap_objectclass','en','ObjectClass of user accounts'),('common','ldap_passwd','en','Your current password'),('common','ldap_port','en','LDAP Port'),('common','ldap_read','en','Read access to LDAP settings'),('common','ldap_search_base','en','LDAP Search base'),('common','ldap_server','en','LDAP Server URL'),('common','ldap_tls','en','Use LDAP TLS'),('common','ldap_v2','en','LDAPv2'),('common','ldap_v3','en','LDAPv3'),('common','ldap_version','en','LDAP protokoll version'),('common','ldap_visible','en','LDAP settings are visible'),('common','ldap_write','en','Edit LDAP settings'),('common','learning module','en','Learning Module'),('common','learning_objects','en','Learning Objects'),('common','learning_progress','en','Learning progress'),('common','learning_resource','en','Learning Resource'),('common','learning_resources','en','Learning Resources'),('common','level','en','Level'),('common','link','en','Link'),('common','link_check','en','Link check'),('common','link_check_body_top','en','You receive this mail because you want to be informed about invalid links of the following learning modules:'),('common','link_check_message_b','en','If enabled, you will be informed about invalid links by email.'),('common','link_check_message_disabled','en','Messages diabled'),('common','link_check_message_enabled','en','Messages enabled'),('common','link_check_subject','en','[ILIAS 3] Link check'),('common','link_checker_refreshed','en','Refreshed view'),('common','link_dynamic_info','en','You can add dynamic parameters like the ILIAS user id.'),('common','link_selected_items','en','Link selected items'),('common','linked_object','en','The object has been linked'),('common','linked_pages','en','Linked Pages'),('common','links_add_param','en','Add parameter:'),('common','links_dyn_parameter','en','Dynamic parameters'),('common','links_dynamic','en','Dynamic Web Links parameters'),('common','links_dynamic_info','en','If enabled, it is possible to append dynamic parameters to Web Links. E.g. the ILIAS user id or the username.'),('common','links_existing_params','en','Existing parameters:'),('common','links_name','en','Parameter name'),('common','links_no_name_given','en','Please choose a parameter name.'),('common','links_no_value_given','en','Please choose a parameter value.'),('common','links_not_available','en','Not available'),('common','links_parameter_deleted','en','Parameter deleted.'),('common','links_select_one','en','- Select one -'),('common','links_session_id','en','ILIAS session id'),('common','links_user_id','en','ILIAS user id'),('common','links_user_name','en','ILIAS username'),('common','links_value','en','Parameter value'),('common','list_of_pages','en','Pages List'),('common','list_of_questions','en','Question List'),('common','literature','en','Literature'),('common','literature_bookmarks','en','Literature Bookmarks'),('common','lm','en','Learning Module ILIAS'),('common','lm_a','en','an ILIAS Learning Module'),('common','lm_add','en','Add ILIAS Learning Module'),('common','lm_added','en','ILIAS Learning Module added'),('common','lm_create','en','Create ILIAS Learning Module'),('common','lm_delete','en','Delete ILIAS Learning Module'),('common','lm_edit_learning_progress','en','Edit learning progress'),('common','lm_edit_permission','en','Change permission settings'),('common','lm_join','en','Subscribe to ILIAS Learning Module'),('common','lm_leave','en','Unsubscribe from ILIASLearning Module'),('common','lm_new','en','New ILIAS Learning Module'),('common','lm_read','en','Read access to ILIAS Learning Module'),('common','lm_type_aicc','en','AICC'),('common','lm_type_hacp','en','HACP'),('common','lm_type_scorm','en','SCORM'),('common','lm_visible','en','ILIAS Learning Module is visible'),('common','lm_write','en','Edit ILIAS Learning Module'),('common','lng','en','Language'),('common','lngf','en','Languages'),('common','lngf_edit_permission','en','Change permission settings'),('common','lngf_read','en','Read access to Language settings'),('common','lngf_visible','en','Language settings are visible'),('common','lo','en','Learning Object'),('common','lo_available','en','Overview Learning Modules & Courses'),('common','lo_categories','en','LO Categories'),('common','lo_edit','en','Edit Learning Object'),('common','lo_new','en','New Learning Object'),('common','lo_no_content','en','No Learning Resources Available'),('common','lo_other_langs','en','LOs in Other Languages'),('common','lo_overview','en','LO Overview'),('common','local','en','Local'),('common','local_language_file','en','local language file'),('common','local_language_files','en','local language files'),('common','local_languages_already_installed','en','Selected local language file(s) are already installed'),('common','locator','en','Location:'),('common','log_in','en','Login'),('common','logic_and','en','and'),('common','logic_or','en','or'),('common','login','en','Login'),('common','login_as','en','Logged in as'),('common','login_data','en','Login data'),('common','login_exists','en','There is already a user with this login name! Please choose another one.'),('common','login_invalid','en','The chosen login is invalid! Only the following characters are allowed (minimum 4 characters): A-Z a-z 0-9 _.-+*@!$%~'),('common','login_time','en','Time online'),('common','login_to_ilias','en','Login to ILIAS'),('common','login_to_ilias_via_shibboleth','en','Login to ILIAS via Shibboleth'),('common','logout','en','Logout'),('common','logout_text','en','You logged off from ILIAS. Your session has been closed.'),('common','logs','en','Log data'),('common','los','en','Learning Objects'),('common','los_last_visited','en','Last Visited Learning Objects'),('common','lres','en','Learning Resources'),('common','mail','en','Mail'),('common','mail_a_root','en','Mailbox'),('common','mail_addressbook','en','Address book'),('common','mail_allow_smtp','en','Allow SMTP'),('common','mail_b_inbox','en','Inbox'),('common','mail_c_trash','en','Trash'),('common','mail_d_drafts','en','Drafts'),('common','mail_delete_error','en','Error while deleting'),('common','mail_delete_error_file','en','Error deleting file'),('common','mail_e_sent','en','Sent'),('common','mail_edit_permission','en','Change permission settings'),('common','mail_folders','en','Mail Folders'),('common','mail_import','en','Import'),('common','mail_import_file','en','Export file'),('common','mail_intern_enable','en','Enable'),('common','mail_mail_visible','en','User may use mail system'),('common','mail_mails_of','en','Mail'),('common','mail_maxsize_attach','en','Max. attachment size'),('common','mail_maxsize_box','en','Max. mailbox size'),('common','mail_maxsize_mail','en','Max. mail size'),('common','mail_maxtime_attach','en','Max. days to keep attachments'),('common','mail_maxtime_mail','en','Max. days to keep mail'),('common','mail_members','en','Mail to members'),('common','mail_not_sent','en','Mail not sent!'),('common','mail_read','en','Read access to Mail settings'),('common','mail_search_no','en','No entries found'),('common','mail_search_word','en','Search word'),('common','mail_select_one','en','You must select one mail'),('common','mail_send_error','en','Error sending mail'),('common','mail_sent','en','Mail sent!'),('common','mail_smtp_mail','en','User may send mail via SMTP'),('common','mail_system','en','System Message'),('common','mail_system_message','en','User may send internal system messages'),('common','mail_visible','en','Mail settings are visible'),('common','mail_write','en','Edit Mail settings'),('common','mail_z_local','en','User Folders'),('common','mails','en','Mail'),('common','main_menu_frame','en','Top Level Menu'),('common','manage_tracking_data','en','Manage Tracking Data'),('common','mark_all_read','en','Mark All as Read'),('common','mark_all_unread','en','Mark All as Unread'),('common','matriculation','en','Matriculation number'),('common','max_number_rec','en','Maximum number of records'),('common','max_time','en','Maximum time'),('common','member','en','Member'),('common','member_status','en','Member Status'),('common','members','en','Members'),('common','membership_annulled','en','Your membership has been cancelled'),('common','mep','en','Media Pool'),('common','mep_add','en','Add Media Pool'),('common','mep_added','en','Added Media Pool'),('common','mep_delete','en','Delete Media Pool'),('common','mep_edit','en','Edit Media Pool Properties'),('common','mep_edit_permission','en','Change permission settings'),('common','mep_new','en','New Media Pool'),('common','mep_not_insert_already_exist','en','The following items have not been inserted because they are already in the media pool:'),('common','mep_read','en','Read Media Pool Content'),('common','mep_visible','en','Media Pool is visible'),('common','mep_write','en','Edit Media Pool Content'),('common','message','en','Message'),('common','message_content','en','Message Content'),('common','message_no_delivered_files','en','You don\'t have delivered a file!'),('common','message_to','en','Message to:'),('common','meta_data','en','Metadata'),('common','migrate','en','Migrate'),('common','min_time','en','Minimum time'),('common','minutes','en','Minutes'),('common','missing','en','Missing'),('common','mob','en','Media Object'),('common','module','en','module'),('common','modules','en','Modules'),('common','month','en','Month'),('common','month_01_long','en','January'),('common','month_01_short','en','Jan'),('common','month_02_long','en','February'),('common','month_02_short','en','Feb'),('common','month_03_long','en','March'),('common','month_03_short','en','Mar'),('common','month_04_long','en','April'),('common','month_04_short','en','Apr'),('common','month_05_long','en','May'),('common','month_05_short','en','May'),('common','month_06_long','en','June'),('common','month_06_short','en','Jun'),('common','month_07_long','en','July'),('common','month_07_short','en','Jul'),('common','month_08_long','en','August'),('common','month_08_short','en','Aug'),('common','month_09_long','en','September'),('common','month_09_short','en','Sep'),('common','month_10_long','en','October'),('common','month_10_short','en','Oct'),('common','month_11_long','en','November'),('common','month_11_short','en','Nov'),('common','month_12_long','en','December'),('common','month_12_short','en','Dec'),('common','monthly','en','monthly'),('common','move','en','Move'),('common','moveChapter','en','Move'),('common','movePage','en','Move'),('common','move_selected_items','en','Move selected items'),('common','move_to','en','Move to'),('common','move_users_to_style','en','Move users to style'),('common','msg_cancel','en','Action cancelled'),('common','msg_changes_ok','en','The changes were OK'),('common','msg_clear_clipboard','en','Clipboard cleared'),('common','msg_cloned','en','Selected object(s) copied'),('common','msg_copy_clipboard','en','Selected object(s) stored in clipboard (Action: copy)'),('common','msg_cut_clipboard','en','Selected object(s) stored in clipboard (Action: cut)'),('common','msg_cut_copied','en','Selected object(s) moved.'),('common','msg_deleted_role','en','Role deleted'),('common','msg_deleted_roles','en','Roles deleted'),('common','msg_deleted_roles_rolts','en','Roles & Role Templates deleted'),('common','msg_deleted_rolt','en','Role Template deleted'),('common','msg_deleted_rolts','en','Role Template deleted'),('common','msg_error_copy','en','Copy Error'),('common','msg_failed','en','Sorry, action failed'),('common','msg_invalid_filetype','en','Invalid file type'),('common','msg_is_last_role','en','You removed the last global role from the following users'),('common','msg_last_role_for_registration','en','At least one role must be available in the registration form for new users. This role is currently the only one available.'),('common','msg_link_clipboard','en','Selected object(s) stored in clipboard (Action: link)'),('common','msg_linked','en','Selected object(s) linked.'),('common','msg_may_not_contain','en','This object may not contain objects of type:'),('common','msg_min_one_active_role','en','Each user must have at least one active global role!'),('common','msg_min_one_role','en','Each user must have at least one global role!'),('common','msg_multi_language_selected','en','You selected the same language for different translations!'),('common','msg_no_default_language','en','No default language specified! You must define one translation as default translation.'),('common','msg_no_delete_yourself','en','You cannot delete your own user account.'),('common','msg_no_file','en','You didn\'t choose a file'),('common','msg_no_language_selected','en','No translation language specified! You must define a language for each translation'),('common','msg_no_perm_assign_role_to_user','en','You have no permission to change user\'s role assignment'),('common','msg_no_perm_assign_user_to_role','en','You have no permission to change the user assignment'),('common','msg_no_perm_copy','en','You have no permission to create a copy of the following object(s):'),('common','msg_no_perm_create','en','You have no permission to create the following object(s):'),('common','msg_no_perm_create_object1','en','You have no permission to create'),('common','msg_no_perm_create_object2','en','at this location!'),('common','msg_no_perm_create_role','en','You have no permission to add roles'),('common','msg_no_perm_create_rolf','en','You have no permission to create a Role Folder. Therefore you may not stop inheritance of roles or add local roles here.'),('common','msg_no_perm_create_rolt','en','You have no permission to add role templates'),('common','msg_no_perm_create_user','en','You have no permission to add users'),('common','msg_no_perm_cut','en','You have no permission to cut the following object(s):'),('common','msg_no_perm_delete','en','You have no permission to delete the following object(s):'),('common','msg_no_perm_delete_track','en','You have no permission to delete tracking data.'),('common','msg_no_perm_link','en','You have no permission to create a link from the following object(s):'),('common','msg_no_perm_modify_role','en','You have no permission to modify roles'),('common','msg_no_perm_modify_rolt','en','You have no permission to modify role templates'),('common','msg_no_perm_modify_user','en','You have no permission to modify user data'),('common','msg_no_perm_paste','en','You have no permission to paste the following object(s):'),('common','msg_no_perm_perm','en','You have no permission to edit permission settings'),('common','msg_no_perm_read','en','You have no permission to access this item.'),('common','msg_no_perm_read_lm','en','You have no permission to read this learning module.'),('common','msg_no_perm_read_track','en','You have no permission to access the user tracking.'),('common','msg_no_perm_undelete','en','You have no permission to undelete the following object(s):'),('common','msg_no_perm_write','en','You have no permission to write'),('common','msg_no_rolf_allowed1','en','The object'),('common','msg_no_rolf_allowed2','en','is not allowed to contain a Role Folder'),('common','msg_no_scan_log','en','Scan log not implemented yet'),('common','msg_no_search_result','en','No entries found'),('common','msg_no_search_string','en','Please enter your query'),('common','msg_no_sysadmin_sysrole_not_assignable','en','Only a System Administrator may assign users to the System Administrator Role'),('common','msg_no_title','en','Please enter a title.'),('common','msg_no_url','en','You didn\'t enter a URL'),('common','msg_not_available_for_anon','en','The page you have chosen is only accessible for registered users'),('common','msg_not_in_itself','en','It\'s not possible to paste the object in itself'),('common','msg_nothing_found','en','msg_nothing_found'),('common','msg_obj_created','en','Object created.'),('common','msg_obj_exists','en','This object already exists in this folder'),('common','msg_obj_modified','en','Modifications saved.'),('common','msg_obj_no_link','en','are not allowed to be linked'),('common','msg_obj_not_deletable_sold','en','Payment is activated for the following objects. They can no be deleted.'),('common','msg_perm_adopted_from1','en','Permission settings adopted from'),('common','msg_perm_adopted_from2','en','(Settings have been saved!)'),('common','msg_perm_adopted_from_itself','en','You cannot adopt permission settings from the current role/role template itself.'),('common','msg_removed','en','Object(s) removed from system.'),('common','msg_role_exists1','en','A role/role template with the name'),('common','msg_role_exists2','en','already exists! Please choose another name'),('common','msg_role_reserved_prefix','en','The prefix \'il_\' is reserved for automatically generated roles. Please choose another name'),('common','msg_roleassignment_active_changed','en','Active role assignment changed'),('common','msg_roleassignment_active_changed_comment','en','This setting is not saved to the user\'s profile! If the user logs in again, all active role assignments reset to their saved values.'),('common','msg_roleassignment_changed','en','Role assignment changed'),('common','msg_sysrole_not_deletable','en','The system role cannot be deleted'),('common','msg_sysrole_not_editable','en','The permission settings of the system role may not be changed. The system role grants all assigned users unlimited access to all objects & functions.'),('common','msg_trash_empty','en','There are no deleted objects'),('common','msg_undeleted','en','Object(s) undeleted.'),('common','msg_user_last_role1','en','The following users are assigned to this role only:'),('common','msg_user_last_role2','en','Please delete the users or assign them to another role in order to delete this role.'),('common','msg_userassignment_changed','en','User assignment changed'),('common','multimedia','en','Multimedia'),('common','my_bms','en','My Bookmarks'),('common','my_frms','en','My Forums'),('common','my_grps','en','My Groups'),('common','my_los','en','My Learning Objects'),('common','my_tsts','en','My Tests'),('common','name','en','Name'),('common','never','en','never'),('common','new','en','New'),('common','new_appointment','en','New Appointment'),('common','new_folder','en','New Folder'),('common','new_group','en','New Group'),('common','new_language','en','New Language'),('common','new_list_password','en','New suggestions'),('common','new_lowercase','en','new'),('common','new_mail','en','New mail!'),('common','news','en','News'),('common','next','en','next'),('common','nickname','en','Nickname'),('common','no','en','No'),('common','no_access_item','en','You have no permission to access this item.'),('common','no_access_item_public','en','This item cannot be accessed in the public area.'),('common','no_access_link_object','en','You are not allowed to link this object'),('common','no_bm_in_personal_list','en','No bookmarks defined.'),('common','no_chat_in_personal_list','en','No chats in personal list'),('common','no_checkbox','en','No checkbox checked!'),('common','no_condition_selected','en','Please select one precondition.'),('common','no_conditions_found','en','No preconditions created.'),('common','no_datasets','en','The table is empty'),('common','no_date','en','No date'),('common','no_description','en','No description'),('common','no_frm_in_personal_list','en','No forums in personal list.'),('common','no_global_role_left','en','Every user has to be assigned to one role.'),('common','no_grp_in_personal_list','en','No groups in personal list.'),('common','no_import_available','en','Import not available for type'),('common','no_import_file_found','en','No import file found'),('common','no_invalid_links','en','No invalid links found.'),('common','no_limit','en','No limit'),('common','no_lo_in_personal_list','en','No learning objects in personal list.'),('common','no_local_users','en','There are no local user'),('common','no_objects','en','No objects'),('common','no_parent_access','en','No access to a superordinated object!'),('common','no_permission','en','You do not have the necessary permission.'),('common','no_permission_to_join','en','You are not allowed to join this group!'),('common','no_roles_user_can_be_assigned_to','en','There are no global roles the user can be assigned to. Therefore you are not allowed to add users.'),('common','no_title','en','No Title'),('common','no_tst_in_personal_list','en','No tests in personal list.'),('common','no_users_applied','en','Please select a user.'),('common','no_users_selected','en','Please select one user.'),('common','no_xml_file_found_in_zip','en','XML file within zip file not found:'),('common','no_zip_file','en','No zip file found.'),('common','non_internal_local_roles_only','en','Local roles (only userdefined)'),('common','none','en','None'),('common','normal','en','Normal'),('common','not_accessed','en','Not accessed'),('common','not_accessed_yet','en','Not accessed yet'),('common','not_implemented_yet','en','Not implemented yet'),('common','not_installed','en','Not Installed'),('common','not_logged_in','en','You are not logged in'),('common','note','en','Note'),('common','note_all_pub_notes','en','All notes'),('common','note_my_pub_notes','en','My notes only'),('common','notes','en','Notes'),('common','num_users','en','Number of users'),('common','number_of_accesses','en','Number of accesses'),('common','number_of_records','en','Number of records'),('common','obj','en','Object'),('common','obj_adm','en','Administration'),('common','obj_adm_desc','en','Main system settings folder containing all panels to administrate your ILIAS installation.'),('common','obj_alm','en','Learning Module AICC'),('common','obj_assf','en','Test&Assessment'),('common','obj_assf_desc','en','Test&Assessment log settings.'),('common','obj_auth','en','Authentication'),('common','obj_auth_desc','en','Configure your authentication mode (local, LDAP, ...)'),('common','obj_cat','en','Category'),('common','obj_cen','en','Centra Event'),('common','obj_chac','en','Chat settings'),('common','obj_chac_desc','en','Configure your chat server here. Enable/disable chats.'),('common','obj_chat','en','Chat'),('common','obj_crs','en','Course'),('common','obj_dbk','en','Digilib Book'),('common','obj_exc','en','Exercise'),('common','obj_extt','en','Third party software'),('common','obj_extt_desc','en','Configure external software or services that are supported by ILIAS'),('common','obj_file','en','File'),('common','obj_fold','en','Folder'),('common','obj_frm','en','Forum'),('common','obj_glo','en','Glossary'),('common','obj_grp','en','Group'),('common','obj_hlm','en','Learning Module HACP'),('common','obj_htlm','en','Learning Module HTML'),('common','obj_icla','en','LearnLinc Classroom'),('common','obj_icrs','en','LearnLinc Seminar'),('common','obj_ldap','en','LDAP Settings'),('common','obj_ldap_desc','en','Configure global LDAP Settings here.'),('common','obj_lm','en','Learning Module ILIAS'),('common','obj_lng','en','Language'),('common','obj_lngf','en','Languages'),('common','obj_lngf_desc','en','Manage your system languages here.'),('common','obj_lo','en','LearningObject'),('common','obj_mail','en','Mail Settings'),('common','obj_mail_desc','en','Configure global mail settings here.'),('common','obj_mep','en','Media Pool'),('common','obj_mob','en','Media Object'),('common','obj_not_found','en','Object Not Found'),('common','obj_note','en','Note'),('common','obj_notf','en','Note Administration'),('common','obj_objf','en','Object definitions'),('common','obj_objf_desc','en','Manage ILIAS object types and object permissions. (only for experts!)'),('common','obj_owner','en','This Object is owned by'),('common','obj_pays','en','Payment settings'),('common','obj_pays_desc','en','Configure payment settings and vendors'),('common','obj_pg','en','Page'),('common','obj_qpl','en','Question Pool Test'),('common','obj_recf','en','Restored Objects'),('common','obj_recf_desc','en','Contains restored Objects from System Check.'),('common','obj_role','en','Role'),('common','obj_rolf','en','Roles'),('common','obj_rolf_desc','en','Manage your roles here.'),('common','obj_rolf_local','en','Local Roles'),('common','obj_rolf_local_desc','en','Contains local roles of object no.'),('common','obj_rolt','en','Role Template'),('common','obj_root','en','ILIAS root node'),('common','obj_sahs','en','Learning Module SCORM/AICC'),('common','obj_seas','en','Search'),('common','obj_seas_desc','en','Manage the search settings here.'),('common','obj_spl','en','Question Pool Survey'),('common','obj_st','en','Chapter'),('common','obj_stys','en','Style Settings'),('common','obj_stys_desc','en','Manage system skin and style settings here'),('common','obj_svy','en','Survey'),('common','obj_tax','en','Taxonomy'),('common','obj_taxf','en','Taxonomies'),('common','obj_taxf_desc','en','Folder for taxonomies'),('common','obj_trac','en','User Tracking'),('common','obj_trac_desc','en','User access tracking data'),('common','obj_tst','en','Test'),('common','obj_typ','en','Object Type Definition'),('common','obj_type','en','Object Type'),('common','obj_url','en','URL'),('common','obj_uset','en','User Settings'),('common','obj_usr','en','User'),('common','obj_usrf','en','User accounts'),('common','obj_usrf_desc','en','Manage user accounts here.'),('common','obj_webr','en','Web Resource'),('common','object','en','Object'),('common','object_added','en','Object added'),('common','object_duplicated','en','Object duplicated'),('common','object_imported','en','Object imported'),('common','objects','en','Objects'),('common','objf','en','Object definitions'),('common','objf_edit_permission','en','Change permission settings'),('common','objf_read','en','Read access to Object definitions'),('common','objf_visible','en','Object definitions are visible'),('common','objf_write','en','Edit Object definitions'),('common','objs_alm','en','AICC Learning Modules'),('common','objs_cat','en','Categories'),('common','objs_chat','en','Chats'),('common','objs_confirm','en','Confirm Action'),('common','objs_crs','en','Courses'),('common','objs_dbk','en','Digilib Books'),('common','objs_delete','en','Delete objects'),('common','objs_exc','en','Exercises'),('common','objs_file','en','Files'),('common','objs_fold','en','Folders'),('common','objs_frm','en','Forums'),('common','objs_glo','en','Glossaries'),('common','objs_grp','en','Groups'),('common','objs_hlm','en','HACP Learning Modules'),('common','objs_htlm','en','HTML Learning Modules'),('common','objs_icla','en','LearnLinc Classrooms'),('common','objs_icrs','en','LearnLinc Seminars'),('common','objs_lm','en','ILIAS Learning Modules'),('common','objs_lng','en','Languages'),('common','objs_lo','en','Learning Objects'),('common','objs_mep','en','Media Pools'),('common','objs_mob','en','Media Objects'),('common','objs_note','en','Notes'),('common','objs_pg','en','Pages'),('common','objs_qpl','en','Question Pools Test'),('common','objs_role','en','Roles'),('common','objs_rolt','en','Role Templates'),('common','objs_sahs','en','SCORM/AICC Learning Modules'),('common','objs_spl','en','Question Pools Survey'),('common','objs_st','en','Chapters'),('common','objs_svy','en','Surveys'),('common','objs_tst','en','Tests'),('common','objs_type','en','Object Types'),('common','objs_url','en','URLs'),('common','objs_usr','en','Users'),('common','objs_webr','en','Web Resources'),('common','of','en','Of'),('common','offline','en','offline'),('common','offline_version','en','Offline Version'),('common','ok','en','OK'),('common','old','en','Old'),('common','online','en','Online'),('common','online_chapter','en','Online Chapter'),('common','online_version','en','Online Version'),('common','open_views_inside_frameset','en','Open views inside frameset'),('common','operation','en','Operation'),('common','optimize','en','Optimize'),('common','options','en','Options'),('common','options_for_subobjects','en','Options for subobjects'),('common','order_by','en','Order by'),('common','other','en','Other'),('common','overview','en','Overview'),('common','overwrite','en','Overwrite'),('common','owner','en','Owner'),('common','owner_updated','en','Owner updated.'),('common','page','en','Page'),('common','page_edit','en','Edit Page'),('common','pages','en','Pages'),('common','parameter','en','Parameter'),('common','parse','en','Parse'),('common','passed','en','Passed'),('common','passwd','en','Password'),('common','passwd_invalid','en','The new password is invalid! Only the following characters are allowed (minimum 6 characters): A-Z a-z 0-9 _.-+?#*@!$%~'),('common','passwd_not_match','en','Your entries for the new password don\'t match! Please re-enter your new password.'),('common','passwd_wrong','en','The password you entered is wrong!'),('common','password','en','Password'),('common','password_assistance_disabled','en','Password assistance is only then available, when user authentication is done via local ILIAS database.'),('common','password_assistance_info','en','If password assistance is enabled, a link with the text \"Forgot Password?\" is shown on the login page of ILIAS. Users can use this link to assign a new password to their user account, without needing assistance from a system administrator.'),('common','paste','en','Paste'),('common','pasteChapter','en','Paste'),('common','pastePage','en','Paste'),('common','paste_clipboard_items','en','Paste items from clipboard'),('common','path','en','Path'),('common','path_not_set','en','Path not set'),('common','path_to_babylon','en','Path to Babylon'),('common','path_to_convert','en','Path to Convert'),('common','path_to_htmldoc','en','Path to HTMLdoc'),('common','path_to_java','en','Path to Java'),('common','path_to_unzip','en','Path to Unzip'),('common','path_to_zip','en','Path to Zip'),('common','pathes','en','Paths'),('common','pay_methods','en','Pay methods'),('common','payment_system','en','Payment System'),('common','pays_edit','en','Edit payment settings'),('common','pays_edit_permission','en','Change permission settings'),('common','pays_read','en','Access payment settings'),('common','pays_visible','en','Payment settings are visible'),('common','pays_write','en','Edit payment settings'),('common','per_object','en','Access per object'),('common','perm_settings','en','Permissions'),('common','permission','en','Permission'),('common','permission_denied','en','Permission Denied'),('common','permission_settings','en','Permission settings'),('common','person_title','en','Title'),('common','personal_data','en','Personal information'),('common','personal_desktop','en','Personal Desktop'),('common','personal_picture','en','Personal Picture'),('common','personal_profile','en','Personal Profile'),('common','persons','en','Persons'),('common','pg_a','en','a page'),('common','pg_add','en','Add page'),('common','pg_added','en','Page added'),('common','pg_edit','en','Edit page'),('common','pg_new','en','New page'),('common','phone','en','Phone'),('common','phone_home','en','Phone, Home'),('common','phone_mobile','en','Phone, Mobile'),('common','phone_office','en','Phone, Office'),('common','phrase','en','Phrase'),('common','please_choose','en','-- Select --'),('common','please_enter_target','en','Please enter a target'),('common','please_enter_title','en','Please enter a title'),('common','please_select_a_delivered_file_to_delete','en','You must select at least one delivered file to delete it!'),('common','please_select_a_delivered_file_to_download','en','You must select at least one delivered file to download it!'),('common','port','en','Port'),('common','position','en','Position'),('common','preconditions','en','Preconditions'),('common','predefined_template','en','Predefined role template'),('common','presentation_options','en','Presentation Options'),('common','previous','en','previous'),('common','print','en','Print'),('common','private_notes','en','Private Notes'),('common','pro','en','Pro'),('common','profile','en','Profile'),('common','profile_changed','en','Your profile has changed'),('common','profile_of','en','Profile of'),('common','properties','en','Properties'),('common','pub_section','en','Public Area'),('common','public','en','public'),('common','public_notes','en','Public Notes'),('common','public_profile','en','Public Profile'),('common','publication','en','Publication'),('common','publication_date','en','Publication Date'),('common','published','en','Published'),('common','publishing_organisation','en','Publishing Organization'),('common','purpose','en','Purpose'),('common','qpl','en','Question Pool Test'),('common','qpl_add','en','Add question pool for test'),('common','qpl_delete','en','Delete Question Pool for Test'),('common','qpl_edit_permission','en','Change permission settings'),('common','qpl_new','en','New question pool for test'),('common','qpl_read','en','Read access to Question Pool for Test'),('common','qpl_upload_file','en','Upload file'),('common','qpl_visible','en','Question Pool for Test is visible'),('common','qpl_write','en','Edit Question Pool for Test'),('common','quarterly','en','quarterly'),('common','query_data','en','Query data'),('common','question','en','Question'),('common','question_pools','en','Question pools'),('common','quit','en','Quit'),('common','quote','en','Quote'),('common','read','en','Read'),('common','recf_edit','en','Edit RecoveryFolder'),('common','recipient','en','Recipient'),('common','referral_comment','en','How did you hear about ILIAS?'),('common','refresh','en','Refresh'),('common','refresh_languages','en','Refresh Languages'),('common','refresh_list','en','Refresh List'),('common','refuse','en','Refuse'),('common','reg_mail_body_salutation','en','Hello'),('common','reg_mail_body_text1','en','Welcome to ILIAS eLearning!'),('common','reg_mail_body_text2','en','To acces ILIAs use the following data:'),('common','reg_mail_body_text3','en','Further personal information:'),('common','reg_mail_subject','en','ILIAS eLearning - Your access data'),('common','reg_passwd_via_mail','en','Your password will be sent to your email address given below.'),('common','register','en','Register'),('common','register_info','en','Please fill out the form to register (Fields marked with an asterisk are required information).'),('common','registered_since','en','Registered since'),('common','registered_user','en','registered User'),('common','registered_users','en','registered Users'),('common','registration','en','New account registration'),('common','registration_disabled','en','Only available when using local authentication'),('common','registration_expired','en','Registration period expired...'),('common','related_to','en','Related to'),('common','remove','en','Remove'),('common','remove_personal_picture','en','Remove'),('common','remove_translation','en','Remove translation'),('common','rename','en','Rename'),('common','rename_file','en','Rename File'),('common','repeat_scan','en','Repeating virus scan...'),('common','repeat_scan_failed','en','Repeat scan failed.'),('common','repeat_scan_succeded','en','Repeat scan succeded.'),('common','replace_file','en','Replace File'),('common','reply','en','Reply'),('common','repository','en','Repository'),('common','repository_admin','en','Repository, Trash and Permissions'),('common','repository_admin_desc','en','Set permissions for repository items, restore or remove objects from system trash'),('common','repository_frame','en','Repository Content'),('common','request_membership','en','Request membership'),('common','require_city','en','Require city'),('common','require_country','en','Require country'),('common','require_default_role','en','Require role'),('common','require_department','en','Require department'),('common','require_email','en','Require email'),('common','require_fax','en','Require fax'),('common','require_firstname','en','Require first name'),('common','require_gender','en','Require gender'),('common','require_hobby','en','Require hobby'),('common','require_institution','en','Require institution'),('common','require_lastname','en','Require last name'),('common','require_login','en','Require login'),('common','require_mandatory','en','mandatory'),('common','require_matriculation','en','Require matriculation number'),('common','require_passwd','en','Require password'),('common','require_passwd2','en','Require retype password'),('common','require_phone_home','en','Require home phone'),('common','require_phone_mobile','en','Require mobile phone'),('common','require_phone_office','en','Require office phone'),('common','require_referral_comment','en','Require referral comment'),('common','require_street','en','Require street'),('common','require_zipcode','en','Require zip code'),('common','required_field','en','Required'),('common','reset','en','Reset'),('common','resources','en','Resources'),('common','retype_password','en','Retype Password'),('common','right','en','Right'),('common','rights','en','Rights'),('common','role','en','Role'),('common','role_a','en','a Role'),('common','role_add','en','Add Role'),('common','role_add_local','en','Add local Role'),('common','role_add_user','en','Add User(s) to role'),('common','role_added','en','Role added'),('common','role_assigned_desk_items','en','Assigned desktop items'),('common','role_assigned_desktop_item','en','Created new assignment.'),('common','role_assignment','en','Role Assignment'),('common','role_assignment_updated','en','Role assignment has been updated.'),('common','role_count_users','en','Number of users'),('common','role_deleted','en','Role deleted'),('common','role_deleted_desktop_items','en','Deleted assignment.'),('common','role_desk_add','en','Assign desktop item'),('common','role_desk_none_created','en','No desktop items assigned to this role.'),('common','role_edit','en','Edit Role'),('common','role_header_edit_users','en','Change User Assignment'),('common','role_list_users','en','List users'),('common','role_new','en','New Role'),('common','role_new_search','en','New search'),('common','role_no_groups_selected','en','Please select a group'),('common','role_no_results_found','en','No results found'),('common','role_no_roles_selected','en','Please select a role'),('common','role_no_users_no_desk_items','en','It is not possible to assign personal desktop items, since you cannot assign users to this role.'),('common','role_protect_permissions','en','Protect permissions'),('common','role_search_enter_search_string','en','Please enter a search string'),('common','role_search_users','en','User search'),('common','role_select_desktop_item','en','Please select one object that will be assigned to this role.'),('common','role_select_one_item','en','Please select one object.'),('common','role_sure_delete_desk_items','en','Are you sure you want to delete the following assignments?'),('common','role_templates_only','en','Role templates only'),('common','role_user_deassign','en','Deassign user from role'),('common','role_user_edit','en','Edit account data'),('common','role_user_send_mail','en','Send user a message'),('common','roles','en','Roles'),('common','roles_of_import_global','en','Global roles of import file'),('common','roles_of_import_local','en','Local roles of import file'),('common','rolf','en','Roles'),('common','rolf_added','en','Role Folder added'),('common','rolf_create_role','en','Create new Role definition'),('common','rolf_create_rolt','en','Create new Role definition template'),('common','rolf_delete','en','Delete Roles/Role templates'),('common','rolf_edit_permission','en','Change permission settings'),('common','rolf_edit_userassignment','en','Change user assignment of Roles'),('common','rolf_read','en','Read access to Roles/Role templates'),('common','rolf_visible','en','Roles/Role templates are visible'),('common','rolf_write','en','Edit default permission settings of Roles/Role templates'),('common','rolt','en','Role Template'),('common','rolt_a','en','a Role Template'),('common','rolt_add','en','Add Role Template'),('common','rolt_added','en','Role template added'),('common','rolt_edit','en','Edit Role Template'),('common','rolt_new','en','New Role Template'),('common','root_create_cat','en','User may create Categories'),('common','root_edit','en','Edit system root node'),('common','root_edit_permission','en','Change permission settings'),('common','root_read','en','Read access to ILIAS system'),('common','root_visible','en','ILIAS system is visible'),('common','root_write','en','Edit metadata of system root node'),('common','sahs','en','Learning Module SCORM/AICC'),('common','sahs_added','en','SCORM/AICC Learning Module added'),('common','sahs_create','en','Create SCORM/AICC Learning Module'),('common','sahs_delete','en','Delete SCORM/AICC Learning Module'),('common','sahs_edit_learning_progress','en','Edit learning progress'),('common','sahs_edit_permission','en','Change permission settings'),('common','sahs_join','en','Subscribe to SCORM/AICC Learning Module'),('common','sahs_leave','en','Unsubscribe from SCORM/AICC Learning Module'),('common','sahs_read','en','Read access to SCORM/AICC Learning Module'),('common','sahs_visible','en','SCORM/AICC Learning Module is visible'),('common','sahs_write','en','Edit SCORM/AICC Learning Module'),('common','salutation','en','Salutation'),('common','salutation_f','en','Ms./Mrs.'),('common','salutation_m','en','Mr.'),('common','save','en','Save'),('common','save_and_back','en','Save And Back'),('common','save_message','en','Save Message'),('common','save_refresh','en','Save and Refresh'),('common','save_return','en','Save and Return'),('common','save_settings','en','Save Settings'),('common','save_user_related_data','en','Save user related access data'),('common','saved','en','Saved'),('common','saved_successfully','en','Saved Successfully'),('common','search','en','Search'),('common','search_active','en','Include active users'),('common','search_for','en','Search For'),('common','search_in','en','Search in'),('common','search_inactive','en','Include inactive users'),('common','search_minimum_characters','en','The minimum word length is:'),('common','search_new','en','New Search'),('common','search_note','en','If &quot;search in&quot; is left blank, all users will be searched. This allows you to find all active or all inactive users.'),('common','search_recipient','en','Search Recipient'),('common','search_result','en','Search result'),('common','search_to_short','en','Your query is to short.'),('common','search_user','en','Search User'),('common','seas_edit_permission','en','Change permission settings'),('common','seas_max_hits','en','Max hits'),('common','seas_max_hits_info','en','Please enter a number for the maximum number of search results.'),('common','seas_read','en','Read search settings'),('common','seas_search','en','Allow to use the search'),('common','seas_settings','en','Search settings'),('common','seas_visible','en','Search settings are visible'),('common','seas_write','en','Edit search settings'),('common','seconds','en','Seconds'),('common','sections','en','Sections'),('common','select','en','Select'),('common','select_all','en','Select All'),('common','select_file','en','Select file'),('common','select_max_one_item','en','Please select one item only'),('common','select_mode','en','Select mode'),('common','select_object_to_copy','en','Please select the object you want to copy'),('common','select_object_to_link','en','Please select the object which you want to link'),('common','select_password','en','Select a new password'),('common','select_questionpool','en','Question pool for Test'),('common','select_questionpool_option','en','--- Please select a question pool ---'),('common','selected','en','Selected'),('common','selected_items','en','Personal Items'),('common','send','en','Send'),('common','send_mail_admins','en','Mail to administrators'),('common','send_mail_members','en','Mail to members'),('common','send_mail_tutors','en','Mail to tutors'),('common','sender','en','Sender'),('common','sent','en','Sent'),('common','sequence','en','Sequence'),('common','sequences','en','Sequences'),('common','server','en','Server'),('common','server_data','en','Server data'),('common','server_software','en','Server Software'),('common','set','en','Set'),('common','setSystemLanguage','en','Set System Language'),('common','setUserLanguage','en','Set User Language'),('common','set_offline','en','Set Offline'),('common','set_online','en','Set Online'),('common','settings','en','Settings'),('common','settings_saved','en','Saved settings'),('common','shib','en','Shibboleth'),('common','shib_active','en','Enable Shibboleth support'),('common','shib_city','en','Attribute for city'),('common','shib_configure','en','Configure Shibboleh Authentication'),('common','shib_country','en','Attribute for country'),('common','shib_data_conv','en','Absolute path to data manipulation API'),('common','shib_data_conv_warning','en','The data manipulation API file you specified cannot be read'),('common','shib_department','en','Attribute for department'),('common','shib_edit_permission','en','Change permission settings'),('common','shib_email','en','Attribute for email address'),('common','shib_firstname','en','Attribute for firstname'),('common','shib_gender','en','Attribute for gender (must be \'m\' or \'f\')'),('common','shib_institution','en','Attribute for institution'),('common','shib_instructions','en','Be sure to read the <a href=\"README.SHIBBOLETH.txt\" target=\"_blank\">README</a> for instructions on how to configure Shibboleth support for ILIAS.'),('common','shib_language','en','Attribute for language'),('common','shib_lastname','en','Attribute for lastname'),('common','shib_login','en','Unique Shibboleth attribute'),('common','shib_login_button','en','Path to Shibboleth login button'),('common','shib_login_instructions','en','Instructions shown in login field'),('common','shib_phone_home','en','Attribute for home phone number'),('common','shib_phone_mobile','en','Attribute for mobile phone number'),('common','shib_phone_office','en','Attribute for office phone number'),('common','shib_read','en','Read access to Shibboleth settings'),('common','shib_settings_saved','en','The Shibboleth settings were saved'),('common','shib_street','en','Attribute for street'),('common','shib_title','en','Attribute for title'),('common','shib_update','en','Update this field on login'),('common','shib_user_default_role','en','Default role assigned to Shibboleth users'),('common','shib_visible','en','Shibboleth settings are visible'),('common','shib_write','en','Edit Shibboleth settings'),('common','shib_zipcode','en','Attribute for zipcode'),('common','show','en','Show'),('common','show_details','en','Show Details'),('common','show_list','en','Show List'),('common','show_members','en','Display Members'),('common','show_notes_on_pd','en','Show notes on personal desktop'),('common','show_owner','en','Show Owner'),('common','show_private_notes','en','Show private notes'),('common','show_public_notes','en','Show public notes'),('common','show_structure','en','Enable Structured-View'),('common','show_users_online','en','Show active users'),('common','signature','en','Signature'),('common','size','en','Size'),('common','skin_style','en','Default Skin / Style'),('common','small_icon','en','Small Icon'),('common','smtp','en','SMTP'),('common','soap_user_administration','en','External user administration (SOAP)'),('common','soap_user_administration_desc','en','If enabled, all user accounts can be administrated by an external SOAP-Client.'),('common','sort_by_this_column','en','Sort by this column'),('common','spacer','en','Spacer'),('common','spl','en','Question Pool Survey'),('common','spl_add','en','Add question pool for survey'),('common','spl_delete','en','Delete Question Pool for Survey'),('common','spl_edit_permission','en','Change permission settings'),('common','spl_new','en','New question pool for survey'),('common','spl_read','en','Read access to Question Pool for Survey'),('common','spl_upload_file','en','Upload file'),('common','spl_visible','en','Question Pool for Survey is visible'),('common','spl_write','en','Edit Question Pool for Survey'),('common','ssl','en','SSL (HTTPS)'),('common','st_a','en','a chapter'),('common','st_add','en','Add chapter'),('common','st_added','en','Chapter added'),('common','st_edit','en','Edit chapter'),('common','st_new','en','New chapter'),('common','startpage','en','Start page'),('common','statistic','en','Statistic'),('common','status','en','Status'),('common','step','en','Step'),('common','stop_inheritance','en','Stop inheritance'),('common','street','en','Street'),('common','structure','en','Structure'),('common','sty','en','Style'),('common','style_activation','en','Style Activation'),('common','stys_edit_permission','en','Change permission settings'),('common','stys_read','en','Read access to style settings'),('common','stys_visible','en','Style settings are visible'),('common','stys_write','en','Edit style settings'),('common','subcat_name','en','Subcategory Name'),('common','subchapter_new','en','New Subchapter'),('common','subject','en','Subject'),('common','subject_module','en','Subject Module'),('common','submit','en','Submit'),('common','subobjects','en','Subobjects'),('common','subscription','en','Subscription'),('common','summary','en','Summary'),('common','sure_delete_selected_users','en','Are you sure you want to delete the selected user(s)'),('common','svy','en','Survey'),('common','svy_add','en','Add survey'),('common','svy_delete','en','Delete survey'),('common','svy_edit_permission','en','Change permission settings'),('common','svy_evaluation','en','Evaluation'),('common','svy_finished','en','finished'),('common','svy_invite','en','Invite users to a survey'),('common','svy_new','en','New survey'),('common','svy_no_content','en','No surveys available'),('common','svy_not_finished','en','not finished'),('common','svy_not_started','en','not started'),('common','svy_participate','en','Participate in a survey'),('common','svy_read','en','Read access to survey'),('common','svy_run','en','Run'),('common','svy_upload_file','en','Upload file'),('common','svy_visible','en','Survey is visible'),('common','svy_warning_survey_not_complete','en','The survey is not finished!'),('common','svy_write','en','Edit survey'),('common','system','en','System'),('common','system_check','en','System Check'),('common','system_choose_language','en','System Choose Language'),('common','system_groups','en','System Groups'),('common','system_grp','en','System Group'),('common','system_information','en','System Information'),('common','system_language','en','System Language'),('common','system_message','en','System Message'),('common','system_style_settings','en','System Style Settings'),('common','system_styles','en','System Styles'),('common','table_mail_import','en','Mail import'),('common','target','en','Target'),('common','tax','en','Taxonomy'),('common','tax_add','en','Add Taxonomy'),('common','tax_delete','en','Delete taxonomies'),('common','tax_edit','en','Edit Taxonomy'),('common','tax_edit_permission','en','Change permission settings'),('common','tax_new','en','New Taxonomy'),('common','tax_read','en','Read access to taxonomies'),('common','tax_visible','en','Taxonomy is visible'),('common','tax_write','en','Write access to taxonomies'),('common','taxf_edit_permission','en','Change permission settings'),('common','taxf_read','en','Read access to taxonomy folder'),('common','taxf_visible','en','Taxonomy folder is visible'),('common','taxf_write','en','Write access to taxonomy folder'),('common','term','en','Term'),('common','test','en','Test'),('common','test_intern','en','Test Intern'),('common','test_module','en','Test Module'),('common','tests','en','Tests'),('common','thumbnail','en','Thumbnail'),('common','time','en','Time'),('common','time_d','en','Day'),('common','time_h','en','Hour'),('common','time_limit','en','Access'),('common','time_limit_add_time_limit_for_selected','en','Please enter a time period for the selected users.'),('common','time_limit_applied_users','en','Applied users'),('common','time_limit_from','en','From (time limit)'),('common','time_limit_message','en','Message (time limit)'),('common','time_limit_no_users_selected','en','Please select a user.'),('common','time_limit_not_valid','en','The period is not valid.'),('common','time_limit_not_within_owners','en','Your access is limited. The period is outside of your limit.'),('common','time_limit_owner','en','Owner (time limit)'),('common','time_limit_reached','en','Your access period is expired.'),('common','time_limit_unlimited','en','Unlimited (time limit)'),('common','time_limit_until','en','Until (time limit)'),('common','time_limit_users_updated','en','User data updated'),('common','time_limits','en','Access'),('common','time_segment','en','Period of time'),('common','title','en','Title'),('common','to','en','To'),('common','to_client_list','en','To Client selection'),('common','to_desktop','en','Add to desktop'),('common','today','en','Today'),('common','toggleGlobalDefault','en','Toggle Global Default'),('common','toggleGlobalFixed','en','Toggle Global Fixed'),('common','total','en','Total'),('common','total_online','en','Time online (DD:HH:MM:SS)'),('common','tpl_path','en','Template Path'),('common','trac_delete','en','Delete Tracking Data'),('common','trac_edit_permission','en','Change permission settings'),('common','trac_read','en','Read Tracking Data'),('common','trac_visible','en','User tracking is visible'),('common','tracked_objects','en','Tracked objects'),('common','tracking_data','en','Tracking Data'),('common','tracking_data_del_confirm','en','Do you really want to delete all tracking data of the specified month and before?'),('common','tracking_data_deleted','en','Tracking data deleted'),('common','tracking_settings','en','Tracking Settings'),('common','translation','en','Translation'),('common','trash','en','Trash'),('common','treeview','en','Tree View'),('common','tst','en','Test'),('common','tst_add','en','Add test'),('common','tst_anon_eval','en','Results'),('common','tst_delete','en','Delete Test'),('common','tst_edit_learning_progress','en','Edit learning progress'),('common','tst_edit_permission','en','Change permission settings'),('common','tst_new','en','New test'),('common','tst_no_content','en','No tests available'),('common','tst_read','en','Read access to Test'),('common','tst_run','en','Run'),('common','tst_statistical_evaluation','en','Statistics'),('common','tst_upload_file','en','Upload file'),('common','tst_user_not_invited','en','You are not supposed to take this exam.'),('common','tst_user_wrong_clientip','en','You don\'t have the right ip using this test'),('common','tst_visible','en','Test is visible'),('common','tst_warning_test_not_complete','en','The test is not complete!'),('common','tst_write','en','Edit Test'),('common','txt_add_object_instruction1','en','Browse to the location where you want to add'),('common','txt_add_object_instruction2','en','.'),('common','txt_registered','en','You successfully registered to ILIAS. Please click on the button below to login to ILIAS with your user account.'),('common','txt_submitted','en','You successfully submitted an account request to ILIAS. Your account request will be reviewed by the system administrators, and should be activated within 48 hours. You will not be able to log in until your account is activated.'),('common','typ','en','Object Type Definition'),('common','type','en','Type'),('common','type_your_message_here','en','Type Your Message Here'),('common','uid','en','UID'),('common','unambiguousness','en','Unambiguousness'),('common','uncheck_all','en','Uncheck all'),('common','uninstall','en','Uninstall'),('common','uninstalled','en','uninstalled.'),('common','unknown','en','UNKNOWN'),('common','unlabeled','en','Unlabeled'),('common','unread','en','Unread'),('common','unread_lowercase','en','unread'),('common','unselected','en','Unselected'),('common','unsubscribe','en','Remove from desktop'),('common','unzip','en','Unzip'),('common','up','en','Up'),('common','update','en','Edit'),('common','update_applied','en','Update Applied'),('common','update_language','en','Update Language'),('common','update_on_conflict','en','Update on conflict'),('common','upload','en','Upload'),('common','upload_error_file_not_found','en','Upload error: File not found.'),('common','uploaded_and_checked','en','The file has been uploaded and checked, you can now start to import it.'),('common','url','en','URL'),('common','url_a','en','a URL'),('common','url_add','en','Add URL'),('common','url_added','en','URL added'),('common','url_create_url','en','Create URL'),('common','url_delete','en','Delete URL'),('common','url_description','en','URL Description'),('common','url_edit','en','Edit URL'),('common','url_edit_permission','en','Change Permission settings'),('common','url_new','en','New URL'),('common','url_not_found','en','File Not Found'),('common','url_read','en','Open URL'),('common','url_visible','en','URL is visible'),('common','url_write','en','Edit URL'),('common','user','en','User'),('common','user_access','en','Access per user'),('common','user_added','en','User added'),('common','user_assignment','en','User Assignment'),('common','user_cant_receive_mail','en','user is not allowed to use the mail system'),('common','user_comment','en','User comment'),('common','user_deleted','en','User deleted'),('common','user_detail','en','Detail Data'),('common','user_import_failed','en','User import failed.'),('common','user_imported','en','User import complete.'),('common','user_imported_with_warnings','en','User import complete with warnings.'),('common','user_language','en','User Language'),('common','user_not_chosen','en','You did not choose a group-member!'),('common','user_not_known','en','Please insert a valid username.'),('common','user_profile_other','en','Other Information'),('common','user_statistics','en','Statistical Data'),('common','userdata','en','User data'),('common','userfolder_export_csv','en','Comma Separated Values'),('common','userfolder_export_excel_ppc','en','Microsoft Excel (IBM PPC)'),('common','userfolder_export_excel_x86','en','Microsoft Excel (Intel x86)'),('common','userfolder_export_file','en','File'),('common','userfolder_export_file_size','en','File size'),('common','userfolder_export_files','en','Files'),('common','userfolder_export_xml','en','XML'),('common','username','en','User name'),('common','users','en','Users'),('common','users_not_imported','en','The following users do not exist, their messages cannot become imported'),('common','users_online','en','Active users'),('common','users_online_show_associated','en','Show members of my courses and groups only'),('common','users_online_show_n','en','Don\'t show active users'),('common','users_online_show_y','en','Show all active users'),('common','usertracking','en','User Tracking'),('common','usr','en','User'),('common','usr_a','en','a User'),('common','usr_active_only','en','Active Users only'),('common','usr_add','en','Add User'),('common','usr_added','en','User added'),('common','usr_agreement','en','User Agreement'),('common','usr_agreement_empty','en','The agreement contains no text'),('common','usr_edit','en','Edit User'),('common','usr_hits_per_page','en','Hits/page in tables'),('common','usr_inactive_only','en','Inactive Users only'),('common','usr_new','en','New User'),('common','usr_settings_explanation_profile','en','Check \'Visible\' to show fields in the registration form and personal user profile on the personal desktop. Check \'Changeable\' to allow the user to change the value in the personal profile. Please note that visible fields can always be entered in the registration form. Required fields are required in the registration form and the personal user profile. All data can be changed in the user administration independent on these settings.'),('common','usr_settings_header_profile','en','User profile settings'),('common','usr_settings_header_profile_profile','en','Personal profile setting'),('common','usr_settings_saved','en','Global user settings saved successfully!'),('common','usr_skin_style','en','Skin / Style'),('common','usr_style','en','User Style'),('common','usrf','en','User accounts'),('common','usrf_create_user','en','Create new user account'),('common','usrf_delete','en','Delete user accounts'),('common','usrf_edit_permission','en','Change access to user accounts'),('common','usrf_edit_roleassignment','en','Change role assignment of user accounts'),('common','usrf_push_desktop_items','en','Allow to push items on the personal desktop.'),('common','usrf_read','en','Read access to user accounts'),('common','usrf_read_users','en','Role assignment for local administrators'),('common','usrf_visible','en','User accounts are visible'),('common','usrf_write','en','Edit user accounts'),('common','usrimport_action_failed','en','Action %1$s failed.'),('common','usrimport_action_ignored','en','Ignored action %1$s.'),('common','usrimport_action_replaced','en','Replaced action %1$s by %2$s.'),('common','usrimport_cant_delete','en','Can\'t perform \"Delete\" action. No such user in database.'),('common','usrimport_cant_insert','en','Can\'t perform \"Insert\" action. User is already in database.'),('common','usrimport_cant_update','en','Can\'t perform \"Update\" action. No such user in database.'),('common','usrimport_conflict_handling_info','en','When \"Ignore on conflict\" is selected, ILIAS ignores an action, if it can not be performed (e. g. an \"Insert\" action is not done, if there is already a user with the same login in the database.)\\nWhen \"Update on conflict\" is selected, ILIAS updates the database if an action can not be performed. (e. g. an \"Insert\" action is replaced by an \"Update\" action, if a user with the same login exists in the database).'),('common','usrimport_global_role_for_action_required','en','At least one global role must be specified for \"%1$s\" action.'),('common','usrimport_ignore_role','en','Ignore role'),('common','usrimport_login_is_not_unique','en','Login is not unique.'),('common','usrimport_with_specified_role_not_permitted','en','Import with specified role not permitted.'),('common','usrimport_xml_attribute_missing','en','Attribute \"%2$s\" in element \"%1$s\" is missing.'),('common','usrimport_xml_attribute_value_illegal','en','Value \"%3$s\" of attribute \"%2$s\" in element \"%1$s\" is illegal.'),('common','usrimport_xml_attribute_value_inapplicable','en','Value \"%3$s\" of attribute \"%2$s\" in element \"%1$s\" is inapplicable for \"%4$s\" action.'),('common','usrimport_xml_element_content_illegal','en','Content \"%2$s\" of element \"%1$s\" is illegal.'),('common','usrimport_xml_element_for_action_required','en','Element \"%1$s\" must be specified for \"%2$s\" action.'),('common','usrimport_xml_element_inapplicable','en','Element \"%1$s\" is inapplicable for \"%2$s\" action.'),('common','valid','en','Valid'),('common','validate','en','Validate'),('common','value','en','Value'),('common','vcard','en','Visiting card'),('common','vcard_download','en','Download visiting card'),('common','vendors','en','Vendors'),('common','verification_failed','en','Verification failed'),('common','verification_failure_log','en','Verification failure log'),('common','verification_log','en','Verification log'),('common','verification_warning_log','en','Verification warning log'),('common','version','en','Version'),('common','versions','en','Versions'),('common','view','en','View'),('common','view_content','en','View Content'),('common','visible','en','Visible'),('common','visible_layers','en','Visible Layers'),('common','visitor','en','Visitor'),('common','visitors','en','Visitors'),('common','visits','en','Visits'),('common','web_resources','en','Web Resources'),('common','webr','en','Web Resource'),('common','webr_active','en','Active'),('common','webr_add','en','Add Web Resource'),('common','webr_add_item','en','Add Web Resource'),('common','webr_delete','en','Delete Web Resource'),('common','webr_delete_items','en','Delete Web Resource(s)'),('common','webr_deleted_items','en','Deleted Web Resource(s).'),('common','webr_disable_check','en','Disable check'),('common','webr_edit','en','Edit Web Resource'),('common','webr_edit_item','en','Edit Web Resource'),('common','webr_edit_permission','en','Change permission settings'),('common','webr_fillout_all','en','Please fill out all required fields.'),('common','webr_item_updated','en','Saved modifications'),('common','webr_join','en','Subscribe Web Resource'),('common','webr_last_check','en','Last check'),('common','webr_last_check_table','en','Last check:'),('common','webr_leave','en','Unsubscribe Web Resource'),('common','webr_modified_items','en','Saved modifications.'),('common','webr_never_checked','en','Never checked'),('common','webr_new','en','Add new Web Resource'),('common','webr_no_items_created','en','No Web Resources created'),('common','webr_read','en','Read access to Web Resource'),('common','webr_sure_delete_items','en','Do you really want to delete the following Web Resource(s)?'),('common','webr_visible','en','Web Resource is visible'),('common','webr_write','en','Edit Web Resource'),('common','webservices','en','Webservices'),('common','week','en','Week'),('common','weekly','en','weekly'),('common','weeks','en','Weeks'),('common','welcome','en','Welcome'),('common','who_is_online','en','Who is online?'),('common','width_x_height','en','Width x Height'),('common','with','en','with'),('common','write','en','Write'),('common','wrong_ip_detected','en','Your access is denied because of a wrong client ip.<br>Please contact the system administrator.'),('common','yes','en','Yes'),('common','you_may_add_local_roles','en','You May Add Local Roles'),('common','your_message','en','Your Message'),('common','zip','en','Zip Code'),('common','zipcode','en','Zip Code'),('content','HTML export','en','HTML Export'),('content','PDF export','en','PDF Export'),('content','Pages','en','Pages'),('content','add_menu_entry','en','Add menu entry >>'),('content','all','en','All'),('content','all_pages','en','the entire Learning module'),('content','choose_public_mode','en','Unregistered users may access'),('content','choose_public_pages','en','Choose public accessible pages'),('content','cont_Additional','en','Additional'),('content','cont_Alphabetic','en','Alphabetic A, B, ...'),('content','cont_Circle','en','Circle'),('content','cont_Citation','en','Citation'),('content','cont_Code','en','Code'),('content','cont_Example','en','Example'),('content','cont_Headline1','en','Headline 1'),('content','cont_Headline2','en','Headline 2'),('content','cont_Headline3','en','Headline 3'),('content','cont_List','en','List'),('content','cont_LocalFile','en','Local File'),('content','cont_Mnemonic','en','Mnemonic'),('content','cont_Number','en','Number'),('content','cont_Poly','en','Polygon'),('content','cont_Rect','en','Rectangle'),('content','cont_Reference','en','Reference'),('content','cont_Remark','en','Remark'),('content','cont_Roman','en','Roman I, II, ...'),('content','cont_TableContent','en','Table Content'),('content','cont_Unordered','en','Unordered'),('content','cont_act_number','en','Chapter Numeration'),('content','cont_active','en','Enable Menu'),('content','cont_add_area','en','Add Area'),('content','cont_add_change_comment','en','Add change comment'),('content','cont_add_definition','en','Add Definition'),('content','cont_add_fullscreen','en','Add Full Screen'),('content','cont_added_comment','en','Comment has been added to history.'),('content','cont_added_term','en','Term added'),('content','cont_all_definitions','en','All Definitions'),('content','cont_all_pages','en','All Pages'),('content','cont_alphabetic','en','Alphabetic a, b, ...'),('content','cont_annex','en','Annex'),('content','cont_api_adapter','en','API Adapter Name'),('content','cont_api_func_prefix','en','API Functions Prefix'),('content','cont_areas_deleted','en','Map areas deleted.'),('content','cont_assign_full','en','Assign Full Screen'),('content','cont_assign_std','en','Assign Standard'),('content','cont_assign_translation','en','Assign translation'),('content','cont_assignments_deleted','en','The assignments have been deleted'),('content','cont_autoindent','en','Auto Indent'),('content','cont_back','en','Back'),('content','cont_booktitle','en','Book title'),('content','cont_bottom','en','Bottom'),('content','cont_browser_not_js_capable','en','JavaScript enabled editing is not supported for your browser.'),('content','cont_cant_copy_folders','en','Folders cannot be copied to clipboard.'),('content','cont_cant_del_full','en','Deletion of full screen file not possible.'),('content','cont_cant_del_std','en','Deletion of standard view file not possible.'),('content','cont_caption','en','Caption'),('content','cont_change_type','en','Change Type'),('content','cont_chap_and_pages','en','Chapters and Pages'),('content','cont_chap_copy_select_target_now','en','Chapter marked for copying. Select target now.'),('content','cont_chap_select_target_now','en','Chapter marked for moving. Select target now.'),('content','cont_chapters','en','Chapters'),('content','cont_chapters_and_pages','en','Chapters and Pages'),('content','cont_chapters_only','en','Chapters only'),('content','cont_characteristic','en','Characteristic'),('content','cont_choose_cont_obj','en','Choose Content Object'),('content','cont_choose_glossary','en','Choose Glossary'),('content','cont_choose_media_source','en','Choose Media Source'),('content','cont_citation_err_one','en','You must select exactly one edition'),('content','cont_citation_selection_not_valid','en','You\'re selection is not valid'),('content','cont_citations','en','Citations'),('content','cont_click_br_corner','en','Please click on the bottom right corner of the desired area.'),('content','cont_click_center','en','Please click on center of the desired area.'),('content','cont_click_circle','en','Please click on a circle point of the desired area.'),('content','cont_click_next_or_save','en','Please click on the next point of the polygon or save the area. (It is not necessary to click again on the starting point of this polygon !)'),('content','cont_click_next_point','en','Please click on the next point of the polygon.'),('content','cont_click_starting_point','en','Please click on the starting point of the polygon.'),('content','cont_click_tl_corner','en','Please click on the top left corner of the desired area.'),('content','cont_confirm_delete','en','really delete ?'),('content','cont_content','en','Content'),('content','cont_content_obj','en','Content Object'),('content','cont_contents','en','Contents'),('content','cont_coords','en','Coordinates'),('content','cont_copy_object','en','copy object'),('content','cont_copy_to_clipboard','en','Copy to clipboard'),('content','cont_copy_to_media_pool','en','Copy to media pool'),('content','cont_create_dir','en','Create Directory'),('content','cont_create_export_file','en','Create Export File'),('content','cont_create_export_file_html','en','Create Export File (HTML)'),('content','cont_create_export_file_scorm','en','Create Export File (SCORM)'),('content','cont_create_export_file_xml','en','Create Export File (XML)'),('content','cont_create_folder','en','Create Folder'),('content','cont_create_html_version','en','Create HTML Package'),('content','cont_create_mob','en','Create Media Object'),('content','cont_credit_mode','en','Credit mode (for lesson mode \'normal\')'),('content','cont_credit_off','en','No Credit'),('content','cont_credit_on','en','Credit'),('content','cont_credits','en','Credits'),('content','cont_cross_reference','en','Cross reference'),('content','cont_data_from_lms','en','adlcp:datafromlms'),('content','cont_def_layout','en','Default Layout'),('content','cont_def_lesson_mode','en','Default lesson mode'),('content','cont_def_organization','en','default'),('content','cont_definition','en','Definition'),('content','cont_definitions','en','Definitions'),('content','cont_del_assignment','en','Delete assignment'),('content','cont_dependencies','en','Dependencies'),('content','cont_derive_from_obj','en','Derive from Object'),('content','cont_details','en','Details'),('content','cont_dir_file','en','Directory/File'),('content','cont_disable_js','en','JavaScript inactive'),('content','cont_disable_media','en','Media inactive'),('content','cont_download_title','en','Download Title'),('content','cont_downloads','en','Downloads'),('content','cont_downloads_desc','en','Enable download of all public export files.'),('content','cont_downloads_public_desc','en','Allow download even for anonymous users (in public area)'),('content','cont_ed_align_center','en','align: center'),('content','cont_ed_align_left','en','align: left'),('content','cont_ed_align_left_float','en','align: left float'),('content','cont_ed_align_right','en','align: right'),('content','cont_ed_align_right_float','en','align: right float'),('content','cont_ed_class','en','Style'),('content','cont_ed_col_left','en','move column left'),('content','cont_ed_col_right','en','move column right'),('content','cont_ed_copy_clip','en','copy to clipboard'),('content','cont_ed_delete','en','delete'),('content','cont_ed_delete_col','en','delete column'),('content','cont_ed_delete_item','en','delete item'),('content','cont_ed_delete_row','en','delete row'),('content','cont_ed_edit','en','edit'),('content','cont_ed_edit_prop','en','edit properties'),('content','cont_ed_go','en','Go'),('content','cont_ed_insert_code','en','insert Code'),('content','cont_ed_insert_filelist','en','insert File List'),('content','cont_ed_insert_list','en','insert List'),('content','cont_ed_insert_media','en','insert Media'),('content','cont_ed_insert_par','en','insert Paragr.'),('content','cont_ed_insert_table','en','insert Table'),('content','cont_ed_item_down','en','move item down'),('content','cont_ed_item_up','en','move item up'),('content','cont_ed_moveafter','en','move after'),('content','cont_ed_movebefore','en','move before'),('content','cont_ed_new_col_after','en','new column after'),('content','cont_ed_new_col_before','en','new column before'),('content','cont_ed_new_item_after','en','new item after'),('content','cont_ed_new_item_before','en','new item before'),('content','cont_ed_new_row_after','en','new row after'),('content','cont_ed_new_row_before','en','new row before'),('content','cont_ed_paste_clip','en','paste from clipboard'),('content','cont_ed_row_down','en','move row down'),('content','cont_ed_row_up','en','move row up'),('content','cont_ed_split_page','en','split to new page'),('content','cont_ed_split_page_next','en','split to next page'),('content','cont_ed_width','en','Width'),('content','cont_edit_area','en','Edit Area'),('content','cont_edit_file_list_properties','en','Edit File List Properties'),('content','cont_edit_mob','en','Edit Media Object'),('content','cont_edit_mob_alias_prop','en','Edit Media Object Instance Properties'),('content','cont_edit_mob_files','en','Object Files'),('content','cont_edit_mob_properties','en','Edit Media Object Properties'),('content','cont_edit_par','en','Edit Paragraph'),('content','cont_edit_src','en','Edit source code'),('content','cont_edit_tab_properties','en','Table Properties'),('content','cont_edit_term','en','Edit Term'),('content','cont_edition','en','Edition'),('content','cont_enable_js','en','JavaScript active'),('content','cont_enable_media','en','Media active'),('content','cont_example','en','e.g.'),('content','cont_export_files','en','Export Files'),('content','cont_external','en','external'),('content','cont_external_url','en','external url'),('content','cont_file','en','File'),('content','cont_files','en','Files'),('content','cont_fix_tree','en','Fix structure'),('content','cont_fix_tree_confirm','en','Please execute this command only if the tree structure of this learning module is corrupted, e.g. if blank items occur in the explorer view.'),('content','cont_format','en','Format'),('content','cont_format_error','en','This operation is not allowed here!'),('content','cont_frame_botright','en','Content Details (Glossary)'),('content','cont_frame_maincontent','en','Learning Resource Main Content'),('content','cont_frame_right','en','Content Details'),('content','cont_frame_toc','en','Table of Contents'),('content','cont_frame_topright','en','Content Details (FAQ and Media)'),('content','cont_free_pages','en','Free Pages'),('content','cont_full_is_in_dir','en','Deletion not possible. Full screen file is in directory.'),('content','cont_fullscreen','en','Full Screen'),('content','cont_get_link','en','get link'),('content','cont_get_orig_size','en','Set original size'),('content','cont_glo_menu','en','Menu'),('content','cont_glo_properties','en','Glossary Properties'),('content','cont_height','en','Height'),('content','cont_how_published','en','How published'),('content','cont_href','en','href'),('content','cont_id_ref','en','identifierref'),('content','cont_imagemap','en','Image Map'),('content','cont_import_id','en','identifier'),('content','cont_insert_file_item','en','Insert File Item'),('content','cont_insert_file_list','en','Insert File List'),('content','cont_insert_list','en','Insert List'),('content','cont_insert_mob','en','Insert Media Object'),('content','cont_insert_new_footnote','en','insert new footnote'),('content','cont_insert_par','en','Insert Paragraph'),('content','cont_insert_search','en','Please insert a search term'),('content','cont_insert_src','en','Insert source code'),('content','cont_insert_table','en','Insert Table'),('content','cont_internal','en','internal'),('content','cont_internal_link','en','internal link'),('content','cont_is_visible','en','isvisible'),('content','cont_isbn','en','ISBN'),('content','cont_issn','en','ISSN'),('content','cont_item','en','Item'),('content','cont_journal','en','Journal'),('content','cont_keyword','en','Keyword'),('content','cont_link','en','Link'),('content','cont_link_area','en','Link Area'),('content','cont_link_ext','en','Link (external)'),('content','cont_link_int','en','Link (internal)'),('content','cont_link_select','en','Internal Link'),('content','cont_link_type','en','Link Type'),('content','cont_linked_mobs','en','Linked media objects'),('content','cont_list_files','en','List Files'),('content','cont_list_properties','en','List Properties'),('content','cont_lk_chapter','en','Chapter'),('content','cont_lk_chapter_new','en','Chapter (New Frame)'),('content','cont_lk_media_faq','en','Media (FAQ Frame)'),('content','cont_lk_media_inline','en','Media (Inline)'),('content','cont_lk_media_media','en','Media (Media Frame)'),('content','cont_lk_media_new','en','Media (New Frame)'),('content','cont_lk_page','en','Page'),('content','cont_lk_page_faq','en','Page (FAQ Frame)'),('content','cont_lk_page_new','en','Page (New Frame)'),('content','cont_lk_survey','en','Survey'),('content','cont_lk_term','en','Glossary Term'),('content','cont_lk_term_new','en','Glossary Term (New Frame)'),('content','cont_lm_menu','en','Menu'),('content','cont_lm_properties','en','Learning Module Properties'),('content','cont_lvalue','en','Data element'),('content','cont_manifest','en','Manifest'),('content','cont_map_areas','en','Link Areas'),('content','cont_mastery_score','en','adlcp:masteryscore'),('content','cont_matches','en','matches'),('content','cont_matching_question_javascript_hint','en','Please drag a definition/picture on the right side over a term on the left side and drop the definition/picture to match the term with the definition/picture.'),('content','cont_max_time_allowed','en','adlcp:maxtimeallowed'),('content','cont_media_source','en','Media Source'),('content','cont_mep_structure','en','Media Pool Structure'),('content','cont_missing_preconditions','en','You need to fulfill the following preconditions to access the chapter \"%s\".'),('content','cont_mob_files','en','Object Files'),('content','cont_mob_inst_prop','en','Instance Properties'),('content','cont_mob_prop','en','Object Properties'),('content','cont_mob_usages','en','Usage'),('content','cont_month','en','Month'),('content','cont_move_object','en','move object'),('content','cont_msg_multiple_editions','en','It\'s not possible to show details of multiple editions'),('content','cont_name','en','Name'),('content','cont_new_area','en','New Link Area'),('content','cont_new_assignment','en','New assignment'),('content','cont_new_dir','en','New Directory'),('content','cont_new_file','en','New File'),('content','cont_new_media_obj','en','New Media Object'),('content','cont_new_term','en','New Term'),('content','cont_no_access','en','No Access'),('content','cont_no_assign_itself','en','The object cannot be assigned to itself'),('content','cont_no_manifest','en','No imsmanifest.xml file found in main directory.'),('content','cont_no_object_found','en','Could not find any object with this title'),('content','cont_no_page','en','No Page found.'),('content','cont_no_subdir_in_zip','en','Zip command failed or import file invalid.<br>It does not contain a subfolder \'%s\'.'),('content','cont_no_zip_file','en','Import file is not a zip file.'),('content','cont_none','en','None'),('content','cont_nr_cols','en','Number of Columns'),('content','cont_nr_items','en','Number of Items'),('content','cont_nr_rows','en','Number of Rows'),('content','cont_obj_removed','en','Objects removed.'),('content','cont_offline','en','Offline Versions'),('content','cont_offline_files','en','Offline versions'),('content','cont_offline_versions','en','Offline Versions'),('content','cont_online','en','Online'),('content','cont_or','en','or'),('content','cont_order','en','Order Type'),('content','cont_ordering_question_javascript_hint','en','Please drag a definition/picture over the definition/picture where you want to put it and drop it. The underlying definition/picture and all following definitions/pictures will move downwards.'),('content','cont_organization','en','Organization'),('content','cont_organizations','en','Organizations'),('content','cont_orig_size','en','Original Size'),('content','cont_page_header','en','Page Header'),('content','cont_page_link','en','Page Link'),('content','cont_page_select_target_now','en','Page marked for moving. Select target now.'),('content','cont_pages','en','Pages'),('content','cont_parameter','en','Parameter'),('content','cont_parameters','en','parameters'),('content','cont_paste_from_clipboard','en','Paste from clipboard'),('content','cont_personal_clipboard','en','Personal Clipboard'),('content','cont_pg_content','en','Page Content'),('content','cont_pg_title','en','Page Title'),('content','cont_please_select','en','please select'),('content','cont_prereq_type','en','adlcp:prerequisites.type'),('content','cont_prerequisites','en','adlcp:prerequisites'),('content','cont_preview','en','Preview'),('content','cont_print_view','en','Print View'),('content','cont_public_access','en','Public access'),('content','cont_public_notes','en','Public Notes'),('content','cont_public_notes_desc','en','Allow users to share notes on every page of the learning module.'),('content','cont_publisher','en','Publisher'),('content','cont_purpose','en','Purpose'),('content','cont_ref_helptext','en','(e.g. http://www.server.org/myimage.jpg)'),('content','cont_ref_images','en','Referenced Images'),('content','cont_reference','en','Reference'),('content','cont_remove_fullscreen','en','Remove Full Screen'),('content','cont_removeiln','en','really remove internal link ?'),('content','cont_repository_item','en','Repository Item'),('content','cont_repository_item_links','en','Repository Item Links'),('content','cont_reset_definitions','en','Reset definition positions'),('content','cont_reset_pictures','en','Reset picture positions'),('content','cont_resize_explanation','en','If this option is activated uploaded image files are automatically resized to the specified width and height.'),('content','cont_resize_explanation2','en','This function resizes local image files to the specified width and height.'),('content','cont_resize_image','en','Resize images'),('content','cont_resource','en','Resource'),('content','cont_resource_type','en','type'),('content','cont_resources','en','Resources'),('content','cont_roman','en','Roman i, ii, ...'),('content','cont_rvalue','en','Value'),('content','cont_saved_map_area','en','Saved map area'),('content','cont_saved_map_data','en','Saved map data'),('content','cont_sc_auto_review','en','Set lesson mode \'review\' if student completed SCO'),('content','cont_sc_less_mode_browse','en','Browse'),('content','cont_sc_less_mode_normal','en','Normal'),('content','cont_sc_stat_browsed','en','Browsed'),('content','cont_sc_stat_completed','en','Completed'),('content','cont_sc_stat_failed','en','Failed'),('content','cont_sc_stat_incomplete','en','Incomplete'),('content','cont_sc_stat_not_attempted','en','Not attempted'),('content','cont_sc_stat_passed','en','Passed'),('content','cont_sc_stat_running','en','Running'),('content','cont_sc_title','en','title'),('content','cont_school','en','School'),('content','cont_score','en','Score'),('content','cont_scorm_type','en','adlcp:scormtype'),('content','cont_select_item','en','Select at least one item.'),('content','cont_select_max_one_item','en','Please select one item only'),('content','cont_select_max_one_term','en','Select one term only'),('content','cont_select_one_edition','en','Please select at least one edition.'),('content','cont_select_one_translation','en','Please select one translation'),('content','cont_select_one_translation_warning','en','It is not possible to show more than one translation.'),('content','cont_select_term','en','Select a term'),('content','cont_select_translation','en','Please select the assignment from the list above'),('content','cont_series','en','Series'),('content','cont_series_editor','en','Series editor'),('content','cont_series_title','en','Series title'),('content','cont_series_volume','en','Series volume'),('content','cont_set_after','en','insert after'),('content','cont_set_before','en','insert before'),('content','cont_set_cancel','en','cancel'),('content','cont_set_class','en','Set Class'),('content','cont_set_edit_mode','en','Set Edit Mode'),('content','cont_set_into','en','insert into'),('content','cont_set_link','en','Edit Link'),('content','cont_set_shape','en','Edit Shape'),('content','cont_set_start_file','en','Set Start File'),('content','cont_set_width','en','Set Width'),('content','cont_shape','en','Shape'),('content','cont_show','en','Show'),('content','cont_show_citation','en','Show with citation'),('content','cont_show_line_numbers','en','Show line numbers'),('content','cont_show_print_view','en','Show Print View'),('content','cont_size','en','Size (Bytes)'),('content','cont_skip_chapter','en','Skip this chapter'),('content','cont_source','en','Source'),('content','cont_src','en','Source code'),('content','cont_src_other','en','other'),('content','cont_st_on_pg','en','this function is not allowed.'),('content','cont_st_title','en','Chapter Title'),('content','cont_startfile','en','Start File'),('content','cont_status','en','Status'),('content','cont_std_is_in_dir','en','Deletion not possible. Standard view file is in directory.'),('content','cont_std_view','en','Standard View'),('content','cont_structure','en','structure'),('content','cont_style','en','Style'),('content','cont_subchapters','en','Subchapters'),('content','cont_synchronize_frames','en','Synchronise frames'),('content','cont_synchronize_frames_desc','en','This option synchronises the table of contents frame with the content frame and clears additional frames in 3 window view. But it also decreases performance.'),('content','cont_table','en','Table'),('content','cont_table_border','en','Table Border'),('content','cont_table_cellpadding','en','Table Cell Padding'),('content','cont_table_cellspacing','en','Table Cell Spacing'),('content','cont_table_html_import','en','Import HTML Table'),('content','cont_table_html_import_info','en','Import only works with XHTML conform tables. Table must be root tag!'),('content','cont_table_spreadsheet_import','en','Spreadsheet Import'),('content','cont_table_spreadsheet_import_info','en','Paste table from spreadsheet application via clipboard into the textarea.'),('content','cont_table_width','en','Table Width'),('content','cont_target_within_source','en','Target must not be within source object.'),('content','cont_term','en','Term'),('content','cont_terms','en','Terms'),('content','cont_text_code','en','Code:'),('content','cont_text_com','en','Comment:'),('content','cont_text_emp','en','Emphatic Text:'),('content','cont_text_fn','en','Footnote:'),('content','cont_text_iln','en','Internal Link, e.g.:'),('content','cont_text_quot','en','Quotation:'),('content','cont_text_str','en','Strong Text:'),('content','cont_text_xln','en','External Link:'),('content','cont_time','en','Time'),('content','cont_time_limit_action','en','adlcp:timelimitaction'),('content','cont_title','en','title'),('content','cont_title_footnotes','en','Footnotes'),('content','cont_toc','en','Table of Contents'),('content','cont_toc_mode','en','Table of Contents shows'),('content','cont_top','en','Top'),('content','cont_total_time','en','Total Time'),('content','cont_tracking_data','en','Tracking Data'),('content','cont_tracking_items','en','Tracking Items'),('content','cont_translations','en','Translation(s)'),('content','cont_translations_assigned','en','The translation(s) have been assigned'),('content','cont_tree_fixed','en','Tree structure has been fixed.'),('content','cont_update','en','Update'),('content','cont_update_names','en','Update Names'),('content','cont_upload_file','en','Upload File'),('content','cont_url','en','URL'),('content','cont_users_have_mob_in_clip1','en','This media object is in the clipboard of'),('content','cont_users_have_mob_in_clip2','en','user(s).'),('content','cont_validate_file','en','Validate File'),('content','cont_version','en','version'),('content','cont_view_last_export_log','en','Last Export Log'),('content','cont_where_published','en','Where published'),('content','cont_width','en','Width'),('content','cont_wysiwyg','en','Content WYSIWYG'),('content','cont_xml_base','en','xml:base'),('content','cont_year','en','Year'),('content','cont_zip_file_invalid','en','File is not a valid import file.<br>It does not contain a file \'%s\'.'),('content','copied_to_clipboard','en','Copied object(s) to clipboard.'),('content','glo_definition_abbr','en','Def.'),('content','glo_term_used_in','en','The following resources linking to that term'),('content','lm_menu_edit_entry','en','Edit menu entry'),('content','lm_menu_entry_target','en','Target'),('content','lm_menu_entry_title','en','Title'),('content','lm_menu_new_entry','en','Create a new menu entry'),('content','lm_menu_select_internal_object','en','Select internal object >>'),('content','lm_menu_select_object_to_add','en','Please select the object you want to add to the menu'),('content','msg_entry_added','en','Menu entry added'),('content','msg_entry_removed','en','Menu entry removed'),('content','msg_entry_updated','en','Menu entry updated'),('content','msg_page_no_public_access','en','The page you called is not available in the public area. Only registered users may view this page. Please login first to access this page.'),('content','msg_page_not_public','en','Page is not public'),('content','pages from','en','Pages From'),('content','par','en','Paragraph'),('content','pg','en','Page'),('content','public_section','en','Public Area'),('content','read offline','en','Read Offline'),('content','select_a_file','en','Please select a file.'),('content','selected_pages_only','en','only that pages selected below'),('content','set_public_mode','en','Set public access mode'),('content','st','en','Chapter'),('content','start export','en','Start Export'),('crs','activation_times_not_valid','en','The availability period is not valid.'),('crs','assigned','en','Assigned'),('crs','contact_email_not_valid','en','the contact email is not valid.'),('crs','crs_accept_subscriber','en','Admission to course \"%s\"'),('crs','crs_accept_subscriber_body','en','You have been admitted to course \"%s\".'),('crs','crs_access_password','en','Password'),('crs','crs_activation','en','Activation'),('crs','crs_add_archive_html','en','Create a HTML Archive'),('crs','crs_add_archive_xml','en','Create a XML Archive'),('crs','crs_add_grouping','en','Add course grouping'),('crs','crs_add_grp_assignment','en','Assign course(s)'),('crs','crs_add_html_archive','en','Add HTML archive'),('crs','crs_add_member','en','Add Member(s)'),('crs','crs_add_objective','en','Add objective'),('crs','crs_add_starter','en','Add start object'),('crs','crs_add_subscribers','en','Add subscriber'),('crs','crs_added','en','Added new course'),('crs','crs_added_member','en','Addmission to course \"%s\"'),('crs','crs_added_member_body','en','You have been admitted to course \"%s\".'),('crs','crs_added_new_archive','en','A new archive has been added'),('crs','crs_added_objective','en','A new learning objective has been created.'),('crs','crs_added_starters','en','Assigned start object(s).'),('crs','crs_added_to_list','en','You have been assigned to the waiting list. You are assigned to position %s on the waiting list.'),('crs','crs_admin','en','Administrator'),('crs','crs_admin_no_notify','en','Administrator (no notification)'),('crs','crs_admin_notify','en','Administrator (notification)'),('crs','crs_allow_abo','en','Allow subscription of course items'),('crs','crs_already_assigned_to_list','en','You are already assigned to the waiting list.'),('crs','crs_archive','en','Archives'),('crs','crs_archive_disabled','en','Deactivated'),('crs','crs_archive_download','en','Download'),('crs','crs_archive_info','en','If enabled, all course content will we suppressed. Only HTML archives of this course will be available to course members.'),('crs','crs_archive_lang','en','Language'),('crs','crs_archive_read','en','Read access'),('crs','crs_archive_select_type','en','Archive view'),('crs','crs_archive_type','en','Archive type'),('crs','crs_archive_type_disabled','en','Not available'),('crs','crs_archives_deleted','en','The archive(s) have been deleted'),('crs','crs_auto_fill','en','Auto fill'),('crs','crs_availability_limitless_info','en','Choose this mode for making the course available and accessible to users (depending on the registration settings below).'),('crs','crs_availability_until_info','en','Choose this mode for making the course available and accessible to users for a fixed period of time (depending on the registration settings below).'),('crs','crs_availability_unvisible_info','en','Choose this mode for preparing the course. Only course administators and tutors can see and edit the course.'),('crs','crs_blocked','en','Access refused'),('crs','crs_blocked_member','en','Membership status in course \"%s\"'),('crs','crs_blocked_member_body','en','Your membership for course \"%s\" has been blocked.'),('crs','crs_cancel_subscription','en','Resigned from course \"%s\"'),('crs','crs_cancel_subscription_body','en','A member has resigned from course \"%s\".'),('crs','crs_change_status','en','Changes status of objective:'),('crs','crs_chapter_already_assigned','en','These chapters are already assigned.'),('crs','crs_contact','en','Contact'),('crs','crs_contact_consultation','en','Consultation'),('crs','crs_contact_email','en','Mail'),('crs','crs_contact_name','en','Name'),('crs','crs_contact_phone','en','Telephone'),('crs','crs_contact_responsibility','en','Responsibility'),('crs','crs_content','en','Course content'),('crs','crs_copy_cat_not_allowed','en','It is not possible to copy categories'),('crs','crs_count_members','en','Number of members'),('crs','crs_count_questions','en','Number of questions'),('crs','crs_create_date','en','Create date'),('crs','crs_crs_structure','en','Course structure'),('crs','crs_dates','en','Dates'),('crs','crs_deassign_lm_sure','en','Are you sure you want to delete this assignment?'),('crs','crs_delete_from_list_sure','en','Are you sure, you want to deassign the following users from the list?'),('crs','crs_delete_from_waiting_list','en','Remove from waiting list'),('crs','crs_delete_member','en','Deassign member(s)'),('crs','crs_delete_members_sure','en','Do you want to delete the following members from this course'),('crs','crs_delete_objectve_sure','en','Are you sure, you want to delete the selected objectives?'),('crs','crs_delete_subscribers','en','Delete subscriber'),('crs','crs_delete_subscribers_sure','en','Do you want to delete the following users'),('crs','crs_details','en','Course Details'),('crs','crs_dismiss_member','en','Terminated membership for course \"%s\"'),('crs','crs_dismiss_member_body','en','You membership for course \"%s\" has been terminated'),('crs','crs_edit_archive','en','Edit Archives'),('crs','crs_edit_content','en','Edit items'),('crs','crs_end','en','End'),('crs','crs_export','en','Course export'),('crs','crs_file_name','en','File name'),('crs','crs_free_places','en','Available places'),('crs','crs_from','en','From'),('crs','crs_grouping','en','Course grouping'),('crs','crs_grouping_assign','en','Change assignment'),('crs','crs_grouping_delete_sure','en','Do you really want to delete the following course groupings?'),('crs','crs_grouping_deleted','en','Deleted course grouping.'),('crs','crs_grouping_modified_assignment','en','Changed assignment.'),('crs','crs_grouping_select_one','en','Please select a course grouping.'),('crs','crs_groupings','en','Course groupings'),('crs','crs_groupings_ask_delete','en','Delete course grouping'),('crs','crs_grp_already_assigned','en','You are already member of this course grouping.'),('crs','crs_grp_assign_crs','en','Assign course'),('crs','crs_grp_assigned_courses','en','Assigned courses.'),('crs','crs_grp_assigned_courses_info','en','Assigned courses:'),('crs','crs_grp_courses_already_assigned','en','The selected courses were already assigned.'),('crs','crs_grp_crs_assignment','en','Course assignment'),('crs','crs_grp_deassigned_courses','en','Delete assignment'),('crs','crs_grp_enter_title','en','Please enter a title.'),('crs','crs_grp_info_reg','en','You can only register to one of this courses:'),('crs','crs_grp_matriculation_required','en','This course grouping requires an unique matriculation number. <br />Please insert this value in your settings on the personal desktop.'),('crs','crs_grp_modified_grouping','en','Saved modifications.'),('crs','crs_grp_no_course_selected','en','Please select one course.'),('crs','crs_grp_no_courses_assigned','en','No assigned courses.'),('crs','crs_grp_table_assigned_courses','en','Assigned courses'),('crs','crs_header_archives','en','Course archives'),('crs','crs_header_delete_members','en','Delete members'),('crs','crs_header_delete_subscribers','en','Delete subscribers'),('crs','crs_header_edit_members','en','Edit members'),('crs','crs_header_members','en','Course members'),('crs','crs_header_remove_from_waiting_list','en','Waiting list: deassign users'),('crs','crs_hide_link_lms','en','Hide learning materials'),('crs','crs_hide_link_objectives','en','Hide objectives'),('crs','crs_hide_link_or','en','Hide other resources'),('crs','crs_hide_link_tst','en','Hide tests'),('crs','crs_html','en','HTML'),('crs','crs_info_reg','en','Admittance'),('crs','crs_info_reg_confirmation','en','You can request membership for this course. You will get a message from a course administrator when your request has been admitted or declined.'),('crs','crs_info_reg_deactivated','en','Course admittance is closed.'),('crs','crs_info_reg_direct','en','You can join this course directly.'),('crs','crs_info_reg_password','en','If a course administrator has given you the course password, you can join this course.'),('crs','crs_info_start','en','Please work through all of the course items stated below.<br />After you have processed all red marked objects new course items will be activated.'),('crs','crs_learning_materials','en','Learning materials'),('crs','crs_list_users','en','List users'),('crs','crs_lm_assignment','en','Learning material assignment'),('crs','crs_lm_assignment_deleted','en','Deleted assignment.'),('crs','crs_lm_deassign','en','Delete assignment.'),('crs','crs_lm_no_assignments_selected','en','Please select one assignment.'),('crs','crs_lms_already_assigned','en','These learning materials were already assigned.'),('crs','crs_max_members_reached','en','The maximum number of members has been reached'),('crs','crs_mem_change_status','en','Change status'),('crs','crs_mem_send_mail','en','Send mail'),('crs','crs_member','en','Member'),('crs','crs_member_blocked','en','Member (blocked)'),('crs','crs_member_of','en','Member of course:'),('crs','crs_member_unblocked','en','Member (unblocked)'),('crs','crs_member_updated','en','The member has been updated'),('crs','crs_members_deleted','en','Deleted members'),('crs','crs_members_footer','en','course member(s)'),('crs','crs_members_footer_passed','en','passed'),('crs','crs_members_print_title','en','Course members'),('crs','crs_members_title','en','Course members'),('crs','crs_move_down','en','Move down'),('crs','crs_move_up','en','Move up'),('crs','crs_moved_item','en','Moved course item'),('crs','crs_moved_objective','en','Moved learning objective.'),('crs','crs_new_search','en','New search'),('crs','crs_new_subscription','en','User joined course \"%s\"'),('crs','crs_new_subscription_body','en','A user has joined course \"%s\".'),('crs','crs_new_subscription_request','en','Request to join course \"%s\"'),('crs','crs_new_subscription_request_body','en','A user has requested to join course \"%s\".'),('crs','crs_no_archive_selected','en','No archives selected'),('crs','crs_no_archives_available','en','There are no archives available'),('crs','crs_no_archives_selected','en','Please select an archive'),('crs','crs_no_chapter_selected','en','Please select a chapter.'),('crs','crs_no_groupings_assigned','en','No groupings assigned to this course.'),('crs','crs_no_groups_selected','en','Please select a group'),('crs','crs_no_items_found','en','No items'),('crs','crs_no_language','en','No language'),('crs','crs_no_lm_selected','en','Please select one learning material.'),('crs','crs_no_lms_assigned','en','There are no learning materials assigned.'),('crs','crs_no_lms_inside_course','en','No learning materials found inside this course.'),('crs','crs_no_lms_inside_crs','en','No learning materials inside this course, which can be assigned to this learning objective.'),('crs','crs_no_member_selected','en','Please select one member'),('crs','crs_no_members_assigned','en','There are no members assigned to this course'),('crs','crs_no_notify','en','No notify for new registrations'),('crs','crs_no_objective_selected','en','Please select one objective.'),('crs','crs_no_objectives_created','en','There are no objectives assigned.'),('crs','crs_no_question_selected','en','Please select one question.'),('crs','crs_no_questions_assigned','en','There are no questions assigned.'),('crs','crs_no_results_found','en','No results found'),('crs','crs_no_roles_selected','en','Please select a role'),('crs','crs_no_starter_created','en','No start objects assigned to this course.'),('crs','crs_no_subscribers_selected','en','Please select a user'),('crs','crs_no_tests_inside_crs','en','No tests inside this course, which can be assigned to this learning objective.'),('crs','crs_no_users_added','en','No members added'),('crs','crs_no_users_selected','en','You did not select any user'),('crs','crs_no_valid_member_id_given','en','No valid member Id'),('crs','crs_not_available','en','-Not available-'),('crs','crs_notification','en','Notification'),('crs','crs_notify','en','Notify for new registrations'),('crs','crs_nr','en','Nr.'),('crs','crs_number_users_added','en','The following number of users has been assigned to the course:'),('crs','crs_objective_accomplished','en','Accomplished'),('crs','crs_objective_assign_chapter','en','Assign chapters'),('crs','crs_objective_assign_lm','en','Assign learning material'),('crs','crs_objective_assign_question','en','Assign question'),('crs','crs_objective_deassign_question','en','Delete assignment'),('crs','crs_objective_delete_lm_assignment','en','Delete assignment'),('crs','crs_objective_insert_percent','en','Please enter only numbers between 0 and 100.'),('crs','crs_objective_modified','en','Updated learning objective.'),('crs','crs_objective_no_title_given','en','Please enter a title.'),('crs','crs_objective_not_accomplished','en','Not accomplished'),('crs','crs_objective_overview_objectives','en','Overview objectives'),('crs','crs_objective_overview_question_assignment','en','Overview question assignment'),('crs','crs_objective_pretest','en','Self assessment'),('crs','crs_objective_question_assignment','en','Question assignment'),('crs','crs_objective_result','en','After test'),('crs','crs_objective_updated_test','en','Updated modifications.'),('crs','crs_objective_view','en','Objective oriented view'),('crs','crs_objectives','en','Learning objectives'),('crs','crs_objectives_assign_chapter','en','Assign chapter'),('crs','crs_objectives_assigned_lm','en','The selected learning materials have been assigned.'),('crs','crs_objectives_assigned_lms','en','Assigned learning materials'),('crs','crs_objectives_assigned_new_questions','en','Added new assignment.'),('crs','crs_objectives_assigned_questions','en','Assigned questions'),('crs','crs_objectives_chapter_assignment','en','Chapter assignment'),('crs','crs_objectives_deassign_question_sure','en','Are you sure, you want to delete the following questions?'),('crs','crs_objectives_deleted','en','Delete learning objectives'),('crs','crs_objectives_edit_question_assignments','en','Edit question assignment'),('crs','crs_objectives_info_final','en','You did not accomplish all course objectives in the previous exam. <br />Please select all red marked objectives one after another and review the related lerning materials. <br />After you have reviewed all red marked objectives, you can take the exam again.'),('crs','crs_objectives_info_finished','en','Congratulations, you have accomplished all objectives of this course!'),('crs','crs_objectives_info_none','en','The following is a list of the course objectives.<br /> Please select the objectives one after another and work through the related learning materials.'),('crs','crs_objectives_info_pretest','en','The following are the results of your self assessment. <br />Please select the red marked objectives one after another and work through their related learning material. <br />After you have processed all red marked objectives, you can take the final exam of this course, which is located in the test section of this page.'),('crs','crs_objectives_info_pretest_non_suggest','en','The following are the results of your self assessment. <br />According to this you have fullfilled any learning objective. You can now take the final exam of this course, which is located in the test section of this page.'),('crs','crs_objectives_lm_assignment','en','Learning material assignment'),('crs','crs_objectives_max_points','en','Maximum points'),('crs','crs_objectives_no_question_selected','en','Please select one question.'),('crs','crs_objectives_nr_questions','en','Number of questions'),('crs','crs_objectives_qst_deassigned','en','The question assignment has been deleted.'),('crs','crs_objectives_reset_sure','en','You want to delete all results of this course. <br />All test results will be deleted.'),('crs','crs_objectives_reseted','en','Reseted results.'),('crs','crs_offline','en','Offline'),('crs','crs_options','en','Options'),('crs','crs_other_groupings','en','Groupings of other courses'),('crs','crs_other_resources','en','Other resources'),('crs','crs_passed','en','Passed'),('crs','crs_password_not_valid','en','Your password is not valid'),('crs','crs_pdf','en','PDF'),('crs','crs_persons_on_waiting_list','en','Persons on the waiting list'),('crs','crs_print_list','en','Print list'),('crs','crs_question_assignment','en','Question assignment'),('crs','crs_reg','en','Registration'),('crs','crs_reg_deactivated','en','Disabled'),('crs','crs_reg_lim_info','en','Registrations are only possible during a fixed period of time.'),('crs','crs_reg_max_info','en','Define the maximum number of users that can be assigned to this course. \'0\' means unlimited.'),('crs','crs_reg_notify_info','en','If enabled all course administrators/tutors with status \'Notify\' will be informed by email about new registrations.'),('crs','crs_reg_subscription_deactivated','en','Registration is disabled'),('crs','crs_reg_subscription_end_earlier','en','The registration time is expired'),('crs','crs_reg_subscription_max_members_reached','en','The maximum number of users is exceeded.'),('crs','crs_reg_subscription_start_later','en','The registration time starts later'),('crs','crs_reg_unlim_info','en','If enabled, there is no time limit for registrations. Please choose one of the registration procedures below.'),('crs','crs_reg_until','en','Registration period'),('crs','crs_reg_user_already_assigned','en','You are already assigned to this course'),('crs','crs_reg_user_already_subscribed','en','You have already requested membership for this course'),('crs','crs_reg_user_blocked','en','You are blocked from this course'),('crs','crs_registration','en','Course registration'),('crs','crs_registration_deactivated','en','Choose this option for disabling course registration. No user can join this course.'),('crs','crs_registration_limited','en','Limited registration period'),('crs','crs_registration_type','en','Registration procedure'),('crs','crs_registration_unlimited','en','Unlimited registration period'),('crs','crs_reject_subscriber','en','Declined for course \"%s\"'),('crs','crs_reject_subscriber_body','en','Your request to join course \"%s\" has been declined.'),('crs','crs_reset_results','en','Reset results'),('crs','crs_role','en','Role'),('crs','crs_role_status','en','Role / Status'),('crs','crs_search_enter_search_string','en','Please enter a search string'),('crs','crs_search_for','en','Search for'),('crs','crs_search_members','en','User search'),('crs','crs_search_str','en','Search term'),('crs','crs_select_archive_language','en','Please select a language for the archive'),('crs','crs_select_exactly_one_lm','en','Please select exactly one learning module.'),('crs','crs_select_exactly_one_tst','en','Please select exactly one test.'),('crs','crs_select_native_lm','en','Single chapters can only be assigned to native ILIAS learning modules.'),('crs','crs_select_one_archive','en','Please select one archive'),('crs','crs_select_one_object','en','Please select one course item.'),('crs','crs_select_registration_type','en','Please select an admittance procedure.'),('crs','crs_select_starter','en','Select start object'),('crs','crs_set_on_waiting_list','en','You can add your join request to the waiting list. You will receive a message from a course administrator when your request has been approved or rejected.'),('crs','crs_settings','en','Course settings'),('crs','crs_settings_saved','en','Settings saved'),('crs','crs_show_link_lms','en','Show learning materials'),('crs','crs_show_link_objectives','en','Show objectives'),('crs','crs_show_link_or','en','Show other resources'),('crs','crs_show_link_tst','en','Show tests'),('crs','crs_show_objectives_view','en','Objective view'),('crs','crs_size','en','File size'),('crs','crs_sort_activation','en','Sort by activation'),('crs','crs_sort_manual','en','Sort manually'),('crs','crs_sort_title','en','Sort by title'),('crs','crs_sortorder','en','Sort'),('crs','crs_sortorder_abo','en','Sortorder/Subscription'),('crs','crs_start','en','Start'),('crs','crs_start_objects','en','Start objects'),('crs','crs_starter_deleted','en','Removed assignment.'),('crs','crs_starters_already_assigned','en','This object was already assigned.'),('crs','crs_status','en','Status'),('crs','crs_status_changed','en','Status in course \"%s\"'),('crs','crs_status_changed_body','en','Your status in course \"%s\" has been changed.'),('crs','crs_structure','en','Course structure'),('crs','crs_subscribers','en','Subscribers'),('crs','crs_subscribers_deleted','en','Deleted subscriber(s)'),('crs','crs_subscription','en','Subscription'),('crs','crs_subscription_max_members','en','Maximum of users'),('crs','crs_subscription_notify','en','Notify for subscriptions'),('crs','crs_subscription_options_confirmation','en','Request membership'),('crs','crs_subscription_options_deactivated','en','Deactivated'),('crs','crs_subscription_options_direct','en','Join directly'),('crs','crs_subscription_options_password','en','Join with course password'),('crs','crs_subscription_requested','en','You have requested membership for the course.'),('crs','crs_subscription_successful','en','You have joined the course'),('crs','crs_subscription_type','en','Subscription type'),('crs','crs_suggest_lm','en','Suggest learning materials if:'),('crs','crs_sure_delete_selected_archives','en','Are you sure to delete the selected archives'),('crs','crs_syllabus','en','Syllabus'),('crs','crs_table_start_objects','en','Start objects'),('crs','crs_test_status_complete','en','Complete'),('crs','crs_test_status_not_complete','en','Not complete'),('crs','crs_test_status_random','en','Random test'),('crs','crs_time','en','Subscription time'),('crs','crs_to','en','To'),('crs','crs_tutor','en','Tutor'),('crs','crs_tutor_no_notify','en','Tutor (no notification)'),('crs','crs_tutor_notify','en','Tutor (notification)'),('crs','crs_unblocked','en','Free entrance'),('crs','crs_unblocked_member','en','Status in course \"%s\"'),('crs','crs_unblocked_member_body','en','Your membership in course \"%s\" has been restored'),('crs','crs_unlimited','en','Unlimited'),('crs','crs_unsubscribe','en','Unsubscribe'),('crs','crs_unsubscribe_sure','en','Do you want to unsubscribe from this course'),('crs','crs_unsubscribed_from_crs','en','You have been unsubscribed from this course'),('crs','crs_update_objective','en','Edit learning objective'),('crs','crs_users_added','en','Added user to the course'),('crs','crs_users_already_assigned','en','The user is already assigned to this course'),('crs','crs_users_removed_from_list','en','The selected users have been removed from the waiting list'),('crs','crs_visibility','en','Availability'),('crs','crs_visibility_limitless','en','Unlimited'),('crs','crs_visibility_until','en','Temporarily available'),('crs','crs_visibility_unvisible','en','Not available'),('crs','crs_wait_info','en','If enabled and the maximum number of users is exceeded, new registrations will be placed on a waiting list.'),('crs','crs_waiting_list','en','Waiting list'),('crs','crs_xml','en','XML'),('crs','crs_youre_position','en','Your position on the waiting list'),('crs','edit_content','en','Edit content'),('crs','import_crs','en','Import course'),('crs','learners_view','en','Learners view'),('crs','subscription_times_not_valid','en','The registration period is not valid.'),('crs_','crs_archives','en','Archives'),('dateplaner','DateText','en','Message'),('dateplaner','Day_long','en','Day View'),('dateplaner','ERROR_DATE_EXISTS','en','This event already exists.'),('dateplaner','ERROR_DB','en','Database write error!'),('dateplaner','ERROR_DB_CONNECT','en','The database is disconnected.'),('dateplaner','ERROR_DEL_MSG','en','This is a recurring event. Would you like to delete one event or the series'),('dateplaner','ERROR_ENDDATE','en','Wrong end date input. Format: dd/mm/yyyy'),('dateplaner','ERROR_ENDTIME','en','Wrong end time. Format: hh:mm'),('dateplaner','ERROR_END_START','en','Wrong end date input. End date is before start date.'),('dateplaner','ERROR_FILE_CSV_MSG','en','Please take a valid CSV File.'),('dateplaner','ERROR_JAVASCRIPT','en','<B><CENTER>your browser doesn\'t accept JavaScript.<br />Please close this Window after insert manually.<br />Thank you</CENTER></B>'),('dateplaner','ERROR_ROTATIONEND','en','Wrong recurring end date input. Format: dd/mm/yyyy'),('dateplaner','ERROR_ROT_END_START','en','Wrong recurring end date input. Recurring end date is before start date.'),('dateplaner','ERROR_SHORTTEXT','en','Wrong short text input. Short text is required.'),('dateplaner','ERROR_STARTDATE','en','Wrong start date input. Format: dd/mm/yyyy'),('dateplaner','ERROR_STARTTIME','en','Wrong start time. Format: hh:mm'),('dateplaner','ERROR_TS','en','Incorrect date format'),('dateplaner','Fr_long','en','Friday'),('dateplaner','Fr_short','en','Fr'),('dateplaner','Language_file','en','English'),('dateplaner','Listbox_long','en','List of Events'),('dateplaner','Mo_long','en','Monday'),('dateplaner','Mo_short','en','Mo'),('dateplaner','Month_long','en','Month View'),('dateplaner','Sa_long','en','Saturday'),('dateplaner','Sa_short','en','Sa'),('dateplaner','Su_long','en','Sunday'),('dateplaner','Su_short','en','Su'),('dateplaner','Text','en','Text'),('dateplaner','Th_long','en','Thursday'),('dateplaner','Th_short','en','Th'),('dateplaner','Tu_long','en','Tuesday'),('dateplaner','Tu_short','en','Tu'),('dateplaner','View','en','View:'),('dateplaner','We_long','en','Wednesday'),('dateplaner','We_short','en','We'),('dateplaner','Week_long','en','Week View'),('dateplaner','add_data','en','Additional Data'),('dateplaner','app_','en','Group Events Inbox'),('dateplaner','app_date','en','Create Event'),('dateplaner','app_day','en','Day View'),('dateplaner','app_freetime','en','Free time of other group members (catch a time)'),('dateplaner','app_inbox','en','Group Events Inbox'),('dateplaner','app_list','en','List of Events'),('dateplaner','app_month','en','Month View'),('dateplaner','app_properties','en','Properties'),('dateplaner','app_week','en','Week View'),('dateplaner','application','en','Calendar'),('dateplaner','apply','en','Apply'),('dateplaner','back','en','Back'),('dateplaner','begin_date','en','Begin Date'),('dateplaner','busytime','en','Busy time in group'),('dateplaner','c_date','en','Create Event'),('dateplaner','changeTo','en','Change to:'),('dateplaner','changedDates','en','Changed Events'),('dateplaner','checkforfreetime','en','Check for free time'),('dateplaner','created','en','Created'),('dateplaner','date','en','Date'),('dateplaner','date_format','en','m/d/Y H:i'),('dateplaner','date_format_middle','en','m/d/y'),('dateplaner','del_all','en','Delete all events'),('dateplaner','del_one','en','Delete one event'),('dateplaner','delete','en','Delete'),('dateplaner','deletedDates','en','Deleted Events'),('dateplaner','discard','en','Discard'),('dateplaner','dv_Grouptime','en','Free time'),('dateplaner','dv_button_cancel','en','Cancel'),('dateplaner','dv_button_delete','en','Delete'),('dateplaner','dv_button_insert','en','Insert'),('dateplaner','dv_button_update','en','Update'),('dateplaner','dv_date_begin','en','Start Date:'),('dateplaner','dv_date_end','en','End Date:'),('dateplaner','dv_date_hm','en','Hour:Minute'),('dateplaner','dv_enddate','en','End date:'),('dateplaner','dv_groupselect','en','Group select:'),('dateplaner','dv_groupview','en','Group event:'),('dateplaner','dv_groupview_text','en','Group:<br />'),('dateplaner','dv_keywords','en','Keywords:'),('dateplaner','dv_keywordselect','en','Keyword select:'),('dateplaner','dv_message','en','Message:'),('dateplaner','dv_noGroup','en','no Group'),('dateplaner','dv_noKey','en','no Keyword'),('dateplaner','dv_preview','en','<b>! Preview !</b>'),('dateplaner','dv_rotation','en','Recurrence:'),('dateplaner','dv_rotation_date','en','Recurrence date:'),('dateplaner','dv_rotation_end','en','Recurrence end:'),('dateplaner','dv_shorttext','en','Short text:'),('dateplaner','dv_startdate','en','Start date:'),('dateplaner','dv_text','en','Text:'),('dateplaner','dv_whole_day','en','Whole day:'),('dateplaner','dv_whole_rotation','en','Would you like to delete the entire recurring series? (click the checkbox)'),('dateplaner','end_date','en','End Date'),('dateplaner','endtime_for_view','en','End time for view:'),('dateplaner','error_minical_img','en','Cannot create GD-picture-stream'),('dateplaner','execute','en','Execute choices'),('dateplaner','extra_dates','en','One Day Events'),('dateplaner','free','en','Free'),('dateplaner','freetime','en','Free Time in group'),('dateplaner','group','en','Group'),('dateplaner','help_day','en','Calendar Day View'),('dateplaner','help_day_l','en','In the day view all events of a chosen day are displayed.'),('dateplaner','help_enddate','en','End date - syntax[dd/mm/yyyy]'),('dateplaner','help_enddate_l','en','Contains the end time of the event.<br />The symbol opens a small calendar for choosing the appropriate date.'),('dateplaner','help_endrotation','en','End time syntax[dd/mm/yyyy]'),('dateplaner','help_endrotation_l','en','At this day the repetition will end.<br />The symbol opens a small calendar for choosing the appropriate date'),('dateplaner','help_endtime','en','End time - syntax[hh/mm] 24-hour format'),('dateplaner','help_endtime_l','en','Contains the end time of the appointment using 24-hour format.'),('dateplaner','help_filename','en','dp_help_en.html'),('dateplaner','help_fullday','en','Whole day event - syntax[on/off]'),('dateplaner','help_fullday_l','en','If the event stretches over a whole day, please choose this option.<br />This will hide the start time, end time, and end date.'),('dateplaner','help_further','en','<br /><b>click here for further information.</b>'),('dateplaner','help_group','en','Group events selection'),('dateplaner','help_group_l','en','Contains the groups for which the logged-in user has administrator rights and for which a date can be created.<br />After choosing this it is possible to view the unscheduled time of the group.'),('dateplaner','help_inbox','en','Calendar Inbox'),('dateplaner','help_inbox_l','en','Here all new, changed, and deleted group events are displayed.'),('dateplaner','help_keyword','en','Keyword selection'),('dateplaner','help_keywordUse','en','Keyword selection'),('dateplaner','help_keywordUse_l','en','Choose one or more keywords from the dialog box.<br />TIP: use the CTRL key for selecting several entries.'),('dateplaner','help_keyword_l','en','Contains the keywords which a user can associate to dates.'),('dateplaner','help_list','en','Calendar event list'),('dateplaner','help_list_l','en','In the event list, all events of a given period of time are displayed.'),('dateplaner','help_month','en','Calendar month view'),('dateplaner','help_month_l','en','In the month view all events of a given month are displayed.'),('dateplaner','help_properties_alt','en','Changes the keyword'),('dateplaner','help_properties_alt_l','en','Choose a keyword from the dialog box.<br />Change the keyword in the text field. Important: max. 20 characters.'),('dateplaner','help_properties_del','en','Deletes the keyword'),('dateplaner','help_properties_del_l','en','Choose a keyword from the dialog box. Click the button to delete the keyword.'),('dateplaner','help_properties_import','en','Import of Outlook-CSV-Files'),('dateplaner','help_properties_import_l','en','Here a CSC-File can be selected, which contains Outlook-Events. These events will be imported into the calendar.'),('dateplaner','help_properties_new','en','Add a keyword, max. 20 characters'),('dateplaner','help_properties_new_l','en','Add a new keyword to the field \\\"new\\\". Important: max. 20 characters.'),('dateplaner','help_properties_time','en','Display time for day view/week view'),('dateplaner','help_properties_time_l','en','The standard display time for the day view and week view can be changed. If dates are outside of the display time for a given days or week, the display time will be changed for that time span. Standard is 8h -18h.'),('dateplaner','help_rotation','en','Recurrence'),('dateplaner','help_rotation_l','en','Contains the type of the recurrence / repetition.'),('dateplaner','help_shorttext','en','Short text, max. 50 characters'),('dateplaner','help_shorttext_l','en','Here one can enter the short text for a given date.'),('dateplaner','help_startdate','en','Start date - syntax[dd/mm/yyyy]'),('dateplaner','help_startdate_l','en','Contains the starting time of the event.<br />The symbol opens a small calendar for choosing the appropriate date.'),('dateplaner','help_starttime','en','Start time - syntax[hh/mm] 24-hour format'),('dateplaner','help_starttime_l','en','Contains the starting time of the event using 24-hour format.'),('dateplaner','help_text','en','Text'),('dateplaner','help_text_l','en','Here one can enter the text for a given event.'),('dateplaner','help_week','en','Calendar week view'),('dateplaner','help_week_l','en','In the week view all events of a given week are displayed.'),('dateplaner','hour','en','hour'),('dateplaner','importDates','en','Import of Outlook Events'),('dateplaner','inbox','en','Inbox'),('dateplaner','insertImportDates','en','These events were successfully registered:'),('dateplaner','k_alldates','en','All Events'),('dateplaner','keep','en','Keep'),('dateplaner','keyword','en','Keyword'),('dateplaner','keywords','en','Keywords'),('dateplaner','last_change','en','Last changed'),('dateplaner','long_01','en','January'),('dateplaner','long_02','en','February'),('dateplaner','long_03','en','March'),('dateplaner','long_04','en','April'),('dateplaner','long_05','en','May'),('dateplaner','long_06','en','June'),('dateplaner','long_07','en','July'),('dateplaner','long_08','en','August'),('dateplaner','long_09','en','September'),('dateplaner','long_10','en','October'),('dateplaner','long_11','en','November'),('dateplaner','long_12','en','December'),('dateplaner','main_dates','en','Main Events'),('dateplaner','menue','en','Menu'),('dateplaner','mmonth','en','Last Month'),('dateplaner','month','en','Month'),('dateplaner','more','en','More'),('dateplaner','mweek','en','Last Week'),('dateplaner','myeahr','en','Last Year'),('dateplaner','newDates','en','New Events'),('dateplaner','newKeyword','en','New:'),('dateplaner','new_doc','en','Create new event'),('dateplaner','no_entry','en','No events available'),('dateplaner','o_day_date','en','24-hour event'),('dateplaner','of','en','of'),('dateplaner','open_day','en','Day View'),('dateplaner','pmonth','en','Next Month'),('dateplaner','printlist','en','Print'),('dateplaner','properties','en','Properties'),('dateplaner','pweek','en','Next Week'),('dateplaner','pyear','en','Next Year'),('dateplaner','r_14','en','Every 14 days'),('dateplaner','r_4_weeks','en','Every 4 weeks'),('dateplaner','r_day','en','Every day'),('dateplaner','r_halfyear','en','Every half year'),('dateplaner','r_month','en','Every month'),('dateplaner','r_nonrecurring','en','Once'),('dateplaner','r_week','en','Every week'),('dateplaner','r_year','en','Every year'),('dateplaner','readimportfile','en','Insert'),('dateplaner','rotation','en','Recurrence'),('dateplaner','rotation_dates','en','Recurrence events'),('dateplaner','semester_name','en','Semester Name'),('dateplaner','short_01','en','Jan'),('dateplaner','short_02','en','Feb'),('dateplaner','short_03','en','Mar'),('dateplaner','short_04','en','Apr'),('dateplaner','short_05','en','May'),('dateplaner','short_06','en','Jun'),('dateplaner','short_07','en','Jul'),('dateplaner','short_08','en','Aug'),('dateplaner','short_09','en','Sep'),('dateplaner','short_10','en','Oct'),('dateplaner','short_11','en','Nov'),('dateplaner','short_12','en','Dec'),('dateplaner','shorttext','en','Short Text'),('dateplaner','singleDate','en','Single Event'),('dateplaner','starttime_for_view','en','Start time for view:'),('dateplaner','timedate','en','Time / Date'),('dateplaner','timeslice','en','Time:'),('dateplaner','title','en','ILIAS calendar'),('dateplaner','to','en','to'),('dateplaner','today','en','Today'),('dateplaner','viewlist','en','View'),('dateplaner','week','en','Week'),('dateplaner','wk_short','en','WK'),('dateplaner','year','en','Year'),('forum','forum_anonymize','en','Write anonymously'),('forum','forum_anonymize_desc','en','Write this message anonymously (your ILIAS user name won\'t be shown anywhere).'),('forum','forums','en','Forums'),('forum','forums_all_threads_marked_read','en','All threads have been marked as read'),('forum','forums_attachments','en','Attachments'),('forum','forums_attachments_add','en','Add attachment'),('forum','forums_attachments_edit','en','Edit attachment'),('forum','forums_available','en','Available Forums'),('forum','forums_censor_comment','en','Comment of Censor'),('forum','forums_count','en','Number of Forums'),('forum','forums_count_art','en','Number of Articles'),('forum','forums_count_thr','en','Number of Threads'),('forum','forums_created_by','en','Created by'),('forum','forums_delete_file','en','Delete attachment'),('forum','forums_disable_notification','en','Disable notification'),('forum','forums_download_attachment','en','Download file'),('forum','forums_edit_post','en','Edit Article'),('forum','forums_enable_notification','en','Enable notification'),('forum','forums_info_censor2_post','en','Revoke Censorship?'),('forum','forums_info_censor_post','en','Are you sure you want to hide this article?'),('forum','forums_info_delete_post','en','Are you sure you want to delete this article including any responses?'),('forum','forums_mark_read','en','Mark all read'),('forum','forums_new_entries','en','New Forums Entries'),('forum','forums_new_thread','en','New Topic'),('forum','forums_not_available','en','Forums Not Available'),('forum','forums_notification','en','Notification'),('forum','forums_notification_enabled','en','Notification enabled'),('forum','forums_notification_intro','en','This e-mail was automatically sent to you by the ILIAS installation %s, %s'),('forum','forums_notification_is_disabled','en','Your notification about new posts in this thread is not (yet) enabled.'),('forum','forums_notification_is_enabled','en','At the moment your notification about new posts in this thread is already enabled.'),('forum','forums_notification_show_post','en','Show post: %s'),('forum','forums_notification_subject','en','New post in a forum topic'),('forum','forums_overview','en','Forums Overview'),('forum','forums_post_deleted','en','Article has been deleted'),('forum','forums_post_modified','en','Article has been modified'),('forum','forums_post_new_entry','en','New article has been created'),('forum','forums_posts','en','All articles'),('forum','forums_posts_not_available','en','Articles Not Available'),('forum','forums_print_thread','en','Print Thread'),('forum','forums_print_view','en','Print view'),('forum','forums_quote','en','Quote'),('forum','forums_respond','en','Post Reply'),('forum','forums_subject','en','Subject'),('forum','forums_the_post','en','Article'),('forum','forums_thread','en','Topic'),('forum','forums_thread_articles','en','Articles of the topic'),('forum','forums_thread_create_date','en','Created at'),('forum','forums_thread_create_from','en','Created from'),('forum','forums_thread_marked','en','The Thread has been marked as read.'),('forum','forums_thread_new_entry','en','New topic has been created'),('forum','forums_threads_not_available','en','Topics Not Available'),('forum','forums_topics_overview','en','Topics Overview'),('forum','forums_your_reply','en','Your Reply'),('forum','frm_anonymous_posting','en','Anonymous posting'),('forum','frm_anonymous_posting_desc','en','If enabled, anonymous posting is allowed within this forum.'),('forum','frm_default_view','en','Default view'),('forum','frm_title_required','en','Please insert a title.'),('forum','is_read','en','mark read'),('forum','new_post','en','New post'),('forum','thread','en','Topic'),('jscalendar','about_calendar','en','About the calendar'),('jscalendar','about_calendar_long','en','DHTML Date/Time Selector\\n(c) dynarch.com 2002-2003\\nFor latest version visit: http://dynarch.com/mishoo/calendar.epl\\nDistributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details.\\n\\nDate selection:\\n- Use the «, » buttons to select year\\n- Use the <, > buttons to select month\\n- Hold mouse button on any of the above buttons for faster selection.'),('jscalendar','about_time','en','\\n\\nTime selection:\\n- Click on any of the time parts to increase it\\n- or Shift-click to decrease it\\n- or click and drag for faster selection.'),('jscalendar','close','en','Close'),('jscalendar','day_first','en','Display %s first'),('jscalendar','def_date_format','en','%Y-%m-%d'),('jscalendar','drag_to_move','en','Drag to move'),('jscalendar','go_today','en','Go Today'),('jscalendar','l_01','en','January'),('jscalendar','l_02','en','Feburary'),('jscalendar','l_03','en','March'),('jscalendar','l_04','en','April'),('jscalendar','l_05','en','May'),('jscalendar','l_06','en','June'),('jscalendar','l_07','en','July'),('jscalendar','l_08','en','August'),('jscalendar','l_09','en','September'),('jscalendar','l_10','en','October'),('jscalendar','l_11','en','November'),('jscalendar','l_12','en','December'),('jscalendar','l_fr','en','Friday'),('jscalendar','l_mo','en','Monday'),('jscalendar','l_sa','en','Saturday'),('jscalendar','l_su','en','Sunday'),('jscalendar','l_th','en','Thursday'),('jscalendar','l_tu','en','Tuesday'),('jscalendar','l_we','en','Wednesday'),('jscalendar','next_month','en','Next month (hold for menu)'),('jscalendar','next_year','en','Next year (hold for menu)'),('jscalendar','open_calendar','en','Click here to open a calendar for date selection (JavaScript required!)'),('jscalendar','part_today','en',' (today)'),('jscalendar','prev_month','en','Prev. month (hold for menu)'),('jscalendar','prev_year','en','Prev. year (hold for menu)'),('jscalendar','s_01','en','Jan'),('jscalendar','s_02','en','Feb'),('jscalendar','s_03','en','Mar'),('jscalendar','s_04','en','Apr'),('jscalendar','s_05','en','May'),('jscalendar','s_06','en','Jun'),('jscalendar','s_07','en','Jul'),('jscalendar','s_08','en','Aug'),('jscalendar','s_09','en','Sep'),('jscalendar','s_10','en','Oct'),('jscalendar','s_11','en','Nov'),('jscalendar','s_12','en','Dec'),('jscalendar','s_fr','en','Fr'),('jscalendar','s_mo','en','Mo'),('jscalendar','s_sa','en','Sa'),('jscalendar','s_su','en','Su'),('jscalendar','s_th','en','Th'),('jscalendar','s_tu','en','Tu'),('jscalendar','s_we','en','We'),('jscalendar','select_date','en','Select date'),('jscalendar','time','en','Time'),('jscalendar','time_part','en','(Shift-)Click or drag to change value'),('jscalendar','today','en','Today'),('jscalendar','tt_date_format','en','%a, %b %e'),('jscalendar','wk','en','wk'),('mail','also_as_email','en','Also as Email'),('mail','bc','en','BCC'),('mail','forward','en','Forward'),('mail','linebreak','en','Line break'),('mail','mail_add_recipient','en','Please enter a recipient'),('mail','mail_add_subfolder','en','Add Subfolder'),('mail','mail_add_subject','en','Please enter a subject'),('mail','mail_add_to_addressbook','en','Add to address book'),('mail','mail_add_type','en','Please add the type of mail you want to send'),('mail','mail_addr_entries','en','Entries'),('mail','mail_attachments','en','Attachments'),('mail','mail_bc_not_valid','en','The BCC recipient is not valid'),('mail','mail_bc_search','en','BCC search'),('mail','mail_byte','en','Byte'),('mail','mail_cc_not_valid','en','The CC recipient is not valid'),('mail','mail_cc_search','en','CC search'),('mail','mail_change_to_folder','en','Switch to folder:'),('mail','mail_check_your_email_addr','en','Your email address is not valid'),('mail','mail_compose','en','Compose Message'),('mail','mail_deleted','en','The mail is deleted'),('mail','mail_deleted_entry','en','The entries are deleted'),('mail','mail_email_forbidden','en','You are not allowed to send email'),('mail','mail_enter_login_or_email_addr','en','Please enter a valid login name or e-mail address.'),('mail','mail_enter_valid_email_addr','en','Please enter a valid e-mail address.'),('mail','mail_enter_valid_login','en','Please enter a valid login name.'),('mail','mail_entry_added','en','Added new Entry'),('mail','mail_entry_changed','en','The entry is changed'),('mail','mail_entry_exists','en','An entry with the given login name or e-mail address already exists.'),('mail','mail_file_name','en','File name'),('mail','mail_file_size','en','File size'),('mail','mail_files_deleted','en','The file(s) are deleted'),('mail','mail_folder_created','en','A new folder has been created'),('mail','mail_folder_deleted','en','The folder has been deleted'),('mail','mail_folder_exists','en','A folder already exists with this name'),('mail','mail_folder_name','en','Folder name'),('mail','mail_folder_name_changed','en','The folder has been renamed'),('mail','mail_folder_options','en','Folder Options'),('mail','mail_following_rcp_not_valid','en','The following recipients are not valid:'),('mail','mail_global_options','en','Global options'),('mail','mail_incoming','en','Incoming mail'),('mail','mail_incoming_both','en','local and forwarding'),('mail','mail_incoming_local','en','only local'),('mail','mail_incoming_smtp','en','forward to email address'),('mail','mail_insert_folder_name','en','Please insert a folder name'),('mail','mail_insert_query','en','Please insert a query'),('mail','mail_intern','en','Internal'),('mail','mail_mark_read','en','Mark mail read'),('mail','mail_mark_unread','en','Mark mail unread'),('mail','mail_maxsize_attachment_error','en','The upload limit is:'),('mail','mail_message_send','en','Message sent'),('mail','mail_move_error','en','Error moving mail'),('mail','mail_move_to','en','Move to:'),('mail','mail_moved','en','The mail has been moved'),('mail','mail_moved_to_trash','en','Mail has been moved to trash'),('mail','mail_new_file','en','Add new file:'),('mail','mail_no_attach_allowed','en','System messages are not allowed to contain attachments'),('mail','mail_no_attachments_found','en','No attachments found'),('mail','mail_no_permissions_write_smtp','en','You have no permission to write external mail'),('mail','mail_options_of','en','Options'),('mail','mail_options_saved','en','Options saved'),('mail','mail_recipient_not_valid','en','The recipient is not valid'),('mail','mail_s','en','Mail'),('mail','mail_s_unread','en','Unread Mail'),('mail','mail_saved','en','The message has been saved'),('mail','mail_search_addressbook','en','search in address book'),('mail','mail_search_system','en','search in system'),('mail','mail_select_one_entry','en','You must select one entry'),('mail','mail_select_one_file','en','You must select one file'),('mail','mail_sure_delete','en','Are sure you want to delete the marked mail'),('mail','mail_sure_delete_entry','en','Are sure you want to delete the following entries'),('mail','mail_sure_delete_file','en','The file will be removed permanently'),('mail','mail_sure_delete_folder','en','The folder and its contents will be removed permanently'),('mail','mail_to','en','To'),('mail','mail_to_search','en','To search'),('mail','mail_user_addr_n_valid','en','Following users have no valid email address'),('mail','search_bc_recipient','en','Search BCC Recipient'),('mail','search_cc_recipient','en','Search CC Recipient'),('meta','meta_accessibility_restrictions','en','Accessibility Restrictions'),('meta','meta_active','en','Active'),('meta','meta_add','en','Add'),('meta','meta_annotation','en','Annotation'),('meta','meta_atomic','en','Atomic'),('meta','meta_author','en','Author'),('meta','meta_browser','en','Browser'),('meta','meta_c_AD','en','Andorra'),('meta','meta_c_AE','en','United Arab Emirates'),('meta','meta_c_AF','en','Afghanistan'),('meta','meta_c_AG','en','Antigua And Barbuda'),('meta','meta_c_AI','en','Anguilla'),('meta','meta_c_AL','en','Albania'),('meta','meta_c_AM','en','Armenia'),('meta','meta_c_AN','en','Netherlands Antilles'),('meta','meta_c_AO','en','Angola'),('meta','meta_c_AQ','en','Antarctica'),('meta','meta_c_AR','en','Argentina'),('meta','meta_c_AS','en','American Samoa'),('meta','meta_c_AT','en','Austria'),('meta','meta_c_AU','en','Australia'),('meta','meta_c_AW','en','Aruba'),('meta','meta_c_AZ','en','Azerbaijan'),('meta','meta_c_BA','en','Bosnia And Herzegowina'),('meta','meta_c_BB','en','Barbados'),('meta','meta_c_BD','en','Bangladesh'),('meta','meta_c_BE','en','Belgium'),('meta','meta_c_BF','en','Burkina Faso'),('meta','meta_c_BG','en','Bulgaria'),('meta','meta_c_BH','en','Bahrain'),('meta','meta_c_BI','en','Burundi'),('meta','meta_c_BJ','en','Benin'),('meta','meta_c_BM','en','Bermuda'),('meta','meta_c_BN','en','Brunei Darussalam'),('meta','meta_c_BO','en','Bolivia'),('meta','meta_c_BR','en','Brazil'),('meta','meta_c_BS','en','Bahamas'),('meta','meta_c_BT','en','Bhutan'),('meta','meta_c_BV','en','Bouvet Island'),('meta','meta_c_BW','en','Botswana'),('meta','meta_c_BY','en','Belarus'),('meta','meta_c_BZ','en','Belize'),('meta','meta_c_CA','en','Canada'),('meta','meta_c_CC','en','Cocos (Keeling) Islands'),('meta','meta_c_CF','en','Central African Republic'),('meta','meta_c_CG','en','Congo'),('meta','meta_c_CH','en','Switzerland'),('meta','meta_c_CI','en','Cote D\' Ivoire'),('meta','meta_c_CK','en','Cook Islands'),('meta','meta_c_CL','en','Chile'),('meta','meta_c_CM','en','Cameroon'),('meta','meta_c_CN','en','China'),('meta','meta_c_CO','en','Colombia'),('meta','meta_c_CR','en','Costa Rica'),('meta','meta_c_CU','en','Cuba'),('meta','meta_c_CV','en','Cape Verde'),('meta','meta_c_CX','en','Christmas Island'),('meta','meta_c_CY','en','Cyprus'),('meta','meta_c_CZ','en','Czech Republic'),('meta','meta_c_DE','en','Germany'),('meta','meta_c_DJ','en','Djibouti'),('meta','meta_c_DK','en','Denmark'),('meta','meta_c_DM','en','Dominica'),('meta','meta_c_DO','en','Dominican Republic'),('meta','meta_c_DZ','en','Algeria'),('meta','meta_c_EC','en','Ecuador'),('meta','meta_c_EE','en','Estonia'),('meta','meta_c_EG','en','Egypt'),('meta','meta_c_EH','en','Western Sahara'),('meta','meta_c_ER','en','Eritrea'),('meta','meta_c_ES','en','Spain'),('meta','meta_c_ET','en','Ethiopia'),('meta','meta_c_FI','en','Finland'),('meta','meta_c_FJ','en','Fiji'),('meta','meta_c_FK','en','Falkland Islands'),('meta','meta_c_FM','en','Micronesia'),('meta','meta_c_FO','en','Faroe Islands'),('meta','meta_c_FR','en','France'),('meta','meta_c_FX','en','France, Metropolitan'),('meta','meta_c_GA','en','Gabon'),('meta','meta_c_GB','en','United Kingdom'),('meta','meta_c_GD','en','Grenada'),('meta','meta_c_GE','en','Giorgia'),('meta','meta_c_GF','en','French Guiana'),('meta','meta_c_GH','en','Ghana'),('meta','meta_c_GI','en','Gibraltar'),('meta','meta_c_GL','en','Greenland'),('meta','meta_c_GM','en','Gambia'),('meta','meta_c_GN','en','Guinea'),('meta','meta_c_GP','en','Guadeloupe'),('meta','meta_c_GQ','en','Equatorial Guinea'),('meta','meta_c_GR','en','Greece'),('meta','meta_c_GS','en','South Georgia And The South Sandwich Islands'),('meta','meta_c_GT','en','Guatemala'),('meta','meta_c_GU','en','Guam'),('meta','meta_c_GW','en','Guinea-Bissau'),('meta','meta_c_GY','en','Guyana'),('meta','meta_c_HM','en','Heard And Nc Donald Islands'),('meta','meta_c_HN','en','Honduras'),('meta','meta_c_HR','en','Croatia'),('meta','meta_c_HT','en','Haiti'),('meta','meta_c_HU','en','Hungary'),('meta','meta_c_ID','en','Indonesia'),('meta','meta_c_IE','en','Ireland'),('meta','meta_c_IL','en','Israel'),('meta','meta_c_IN','en','India'),('meta','meta_c_IO','en','British Indian Ocean Territory'),('meta','meta_c_IQ','en','Iraq'),('meta','meta_c_IR','en','Iran'),('meta','meta_c_IS','en','Iceland'),('meta','meta_c_IT','en','Italy'),('meta','meta_c_JM','en','Jamaica'),('meta','meta_c_JO','en','Jordan'),('meta','meta_c_JP','en','Japan'),('meta','meta_c_KE','en','Kenya'),('meta','meta_c_KG','en','Kyrgyzstan'),('meta','meta_c_KH','en','Cambodia'),('meta','meta_c_KI','en','Kiribati'),('meta','meta_c_KM','en','Comoros'),('meta','meta_c_KN','en','Saint Kitts And Nevis'),('meta','meta_c_KP','en','North Korea (People\'s Republic Of Korea)'),('meta','meta_c_KR','en','South Korea (Republic Of Korea)'),('meta','meta_c_KW','en','Kuwait'),('meta','meta_c_KY','en','Cayman Islands'),('meta','meta_c_KZ','en','Kazakhstan'),('meta','meta_c_LA','en','Lao People\'s Republic'),('meta','meta_c_LB','en','Lebanon'),('meta','meta_c_LC','en','Saint Lucia'),('meta','meta_c_LI','en','Liechtenstein'),('meta','meta_c_LK','en','Sri Lanka'),('meta','meta_c_LR','en','Liberia'),('meta','meta_c_LS','en','Lesotho'),('meta','meta_c_LT','en','Lithunia'),('meta','meta_c_LU','en','Luxembourg'),('meta','meta_c_LV','en','Latvia'),('meta','meta_c_LY','en','Libyan Arab Jamahiriya'),('meta','meta_c_MA','en','Morocco'),('meta','meta_c_MC','en','Monaco'),('meta','meta_c_MD','en','Moldova'),('meta','meta_c_MG','en','Madagascar'),('meta','meta_c_MH','en','Marshall Islands'),('meta','meta_c_MK','en','Macedonia'),('meta','meta_c_ML','en','Mali'),('meta','meta_c_MM','en','Myanmar'),('meta','meta_c_MN','en','Mongolia'),('meta','meta_c_MO','en','Macau'),('meta','meta_c_MP','en','Northern Mariana Islands'),('meta','meta_c_MQ','en','Martinique'),('meta','meta_c_MR','en','Mauritania'),('meta','meta_c_MS','en','Montserrat'),('meta','meta_c_MT','en','Malta'),('meta','meta_c_MU','en','Mauritius'),('meta','meta_c_MV','en','Maldives'),('meta','meta_c_MW','en','Malawi'),('meta','meta_c_MX','en','Mexico'),('meta','meta_c_MY','en','Malaysia'),('meta','meta_c_MZ','en','Mozambique'),('meta','meta_c_NA','en','Namibia'),('meta','meta_c_NC','en','New Caledonia'),('meta','meta_c_NE','en','Niger'),('meta','meta_c_NF','en','Norfolk Island'),('meta','meta_c_NG','en','Nigeria'),('meta','meta_c_NI','en','Nicaragua'),('meta','meta_c_NL','en','Netherlands'),('meta','meta_c_NO','en','Norway'),('meta','meta_c_NP','en','Nepal'),('meta','meta_c_NR','en','Nauru'),('meta','meta_c_NU','en','Niue'),('meta','meta_c_NZ','en','New Zealand'),('meta','meta_c_OM','en','Oman'),('meta','meta_c_PA','en','Panama'),('meta','meta_c_PE','en','Peru'),('meta','meta_c_PF','en','French Polynesia'),('meta','meta_c_PG','en','Papua New Guinea'),('meta','meta_c_PH','en','Philippines'),('meta','meta_c_PK','en','Pakistan'),('meta','meta_c_PL','en','Poland'),('meta','meta_c_PM','en','St. Pierre And Miquelon'),('meta','meta_c_PN','en','Pitcairn'),('meta','meta_c_PR','en','Puerto Rico'),('meta','meta_c_PT','en','Portugal'),('meta','meta_c_PW','en','Palau'),('meta','meta_c_PY','en','Paraguay'),('meta','meta_c_QA','en','Qatar'),('meta','meta_c_RE','en','Reunion'),('meta','meta_c_RO','en','Romania'),('meta','meta_c_RU','en','Ran Federation'),('meta','meta_c_RW','en','Rwanda'),('meta','meta_c_SA','en','Saudi Arabia'),('meta','meta_c_SB','en','Solomon Islands'),('meta','meta_c_SC','en','Seychelles'),('meta','meta_c_SD','en','Sudan'),('meta','meta_c_SE','en','Sweden'),('meta','meta_c_SG','en','Singapore'),('meta','meta_c_SH','en','St. Helena'),('meta','meta_c_SI','en','Slovenia'),('meta','meta_c_SJ','en','Svalbard And Jan Mayen Islands'),('meta','meta_c_SK','en','Slovakia'),('meta','meta_c_SL','en','Siearra Leone'),('meta','meta_c_SM','en','San Marino'),('meta','meta_c_SN','en','Senegal'),('meta','meta_c_SO','en','Somalia'),('meta','meta_c_SR','en','Suriname'),('meta','meta_c_ST','en','Sao Tome And Principe'),('meta','meta_c_SV','en','El Salvador'),('meta','meta_c_SY','en','Syrian Arab Republic'),('meta','meta_c_SZ','en','Swaziland'),('meta','meta_c_TC','en','Turks And Caicos Islands'),('meta','meta_c_TD','en','Chad'),('meta','meta_c_TF','en','French Southern Territories'),('meta','meta_c_TG','en','Togo'),('meta','meta_c_TH','en','Thailand'),('meta','meta_c_TJ','en','Tajikistan'),('meta','meta_c_TK','en','Tokelau'),('meta','meta_c_TM','en','Turkmenistan'),('meta','meta_c_TN','en','Tunisia'),('meta','meta_c_TO','en','Tonga'),('meta','meta_c_TP','en','East Timor'),('meta','meta_c_TR','en','Turkey'),('meta','meta_c_TT','en','Trinidad And Tobago'),('meta','meta_c_TV','en','Tuvalu'),('meta','meta_c_TW','en','Taiwan'),('meta','meta_c_TZ','en','Tanzania'),('meta','meta_c_UA','en','Ukraine'),('meta','meta_c_UG','en','Uganda'),('meta','meta_c_UM','en','U.S. Minor Outlying Islands'),('meta','meta_c_US','en','U.S.A'),('meta','meta_c_UY','en','Uruguay'),('meta','meta_c_UZ','en','Uzbekistan'),('meta','meta_c_VA','en','Vatican City State'),('meta','meta_c_VC','en','Saint Vincent And The Grenadines'),('meta','meta_c_VE','en','Venezuela'),('meta','meta_c_VG','en','Virgin Islands (British)'),('meta','meta_c_VI','en','Virgin Islands (US)'),('meta','meta_c_VN','en','Viet Nam'),('meta','meta_c_VU','en','Vanuatu'),('meta','meta_c_WF','en','Wallis And Futuna Islands'),('meta','meta_c_WS','en','Samoa'),('meta','meta_c_YE','en','Yemen'),('meta','meta_c_YT','en','Mayotte'),('meta','meta_c_ZA','en','South Africa'),('meta','meta_c_ZM','en','Zambia'),('meta','meta_c_ZR','en','Zaire'),('meta','meta_c_ZW','en','Zimbabwe'),('meta','meta_c_ZZ','en','Other Country'),('meta','meta_catalog','en','Catalog'),('meta','meta_choose_element','en','Please choose an element you want to add!'),('meta','meta_choose_language','en','Please choose a language'),('meta','meta_choose_section','en','Please choose a section'),('meta','meta_classification','en','Classification'),('meta','meta_collection','en','Collection'),('meta','meta_competency','en','Competency'),('meta','meta_contentprovider','en','Content provider'),('meta','meta_context','en','Context'),('meta','meta_contribute','en','Contribute'),('meta','meta_copyright','en','Copyright'),('meta','meta_copyright_and_other_restrictions','en','Copyright and Other Restrictions'),('meta','meta_cost','en','Cost'),('meta','meta_coverage','en','Coverage'),('meta','meta_creator','en','Creator'),('meta','meta_current_value','en','Current value'),('meta','meta_date','en','Date'),('meta','meta_delete','en','Delete'),('meta','meta_description','en','Description'),('meta','meta_diagramm','en','Diagram'),('meta','meta_difficult','en','Difficult'),('meta','meta_difficulty','en','Difficulty'),('meta','meta_draft','en','Draft'),('meta','meta_duration','en','Duration'),('meta','meta_easy','en','Easy'),('meta','meta_editor','en','Editor'),('meta','meta_education','en','Education'),('meta','meta_educational','en','Educational'),('meta','meta_educational_level','en','Educational Level'),('meta','meta_educational_objective','en','Educational Objective'),('meta','meta_educationalvalidator','en','Educational validator'),('meta','meta_entity','en','Entity'),('meta','meta_entry','en','Entry'),('meta','meta_exam','en','Exam'),('meta','meta_exercise','en','Exercise'),('meta','meta_experiment','en','Experiment'),('meta','meta_expositive','en','Expositive'),('meta','meta_figure','en','Figure'),('meta','meta_final','en','Final'),('meta','meta_format','en','Format'),('meta','meta_general','en','General'),('meta','meta_graph','en','Graph'),('meta','meta_graphicaldesigner','en','Graphical designer'),('meta','meta_has_format','en','Has Format'),('meta','meta_has_part','en','Has Part'),('meta','meta_has_version','en','Has Version'),('meta','meta_hierarchical','en','Hierarchical'),('meta','meta_high','en','High'),('meta','meta_higher_education','en','Higher Education'),('meta','meta_id','en','Id'),('meta','meta_idea','en','Idea'),('meta','meta_identifier','en','Identifier'),('meta','meta_index','en','Index'),('meta','meta_info_tlt_not_valid','en','The current value is not valid. Therefore it can not be used other sections, e.g for learning progress statistics. Please insert atypical learning time in hours and/or minutes.'),('meta','meta_initiator','en','Initiator'),('meta','meta_installation_remarks','en','Installation Remarks'),('meta','meta_instructional_designer','en','Instructional designer'),('meta','meta_instructionaldesigner','en','Instructional designer'),('meta','meta_intended_end_user_role','en','Intended End User Role'),('meta','meta_interactivity_level','en','Interactivity Level'),('meta','meta_interactivity_type','en','Interactivity Type'),('meta','meta_is_based_on','en','Is Based On'),('meta','meta_is_basis_for','en','Is Basis For'),('meta','meta_is_format_of','en','Is Format Of'),('meta','meta_is_part_of','en','Is Part Of'),('meta','meta_is_referenced_by','en','Is Referenced By'),('meta','meta_is_required_by','en','Is Required By'),('meta','meta_is_version_of','en','Is Version Of'),('meta','meta_keyword','en','Keyword'),('meta','meta_kind','en','Kind'),('meta','meta_l_aa','en','Afar'),('meta','meta_l_ab','en','Abkhazian'),('meta','meta_l_af','en','Afrikaans'),('meta','meta_l_am','en','Amharic'),('meta','meta_l_ar','en','Arabic'),('meta','meta_l_as','en','Assamese'),('meta','meta_l_ay','en','Aymara'),('meta','meta_l_az','en','Azerbaijani'),('meta','meta_l_ba','en','Bashkir'),('meta','meta_l_be','en','Byelorussian'),('meta','meta_l_bg','en','Bulgarian'),('meta','meta_l_bh','en','Bihari'),('meta','meta_l_bi','en','Bislama'),('meta','meta_l_bn','en','Bengali;bangla'),('meta','meta_l_bo','en','Tibetan'),('meta','meta_l_br','en','Breton'),('meta','meta_l_ca','en','Catalan'),('meta','meta_l_co','en','Corsican'),('meta','meta_l_cs','en','Czech'),('meta','meta_l_cy','en','Welsh'),('meta','meta_l_da','en','Danish'),('meta','meta_l_de','en','German'),('meta','meta_l_dz','en','Bhutani'),('meta','meta_l_el','en','Greek'),('meta','meta_l_en','en','English'),('meta','meta_l_eo','en','Esperanto'),('meta','meta_l_es','en','Spanish'),('meta','meta_l_et','en','Estonian'),('meta','meta_l_eu','en','Basque'),('meta','meta_l_fa','en','Persian (farsi)'),('meta','meta_l_fi','en','Finnish'),('meta','meta_l_fj','en','Fiji'),('meta','meta_l_fo','en','Faroese'),('meta','meta_l_fr','en','French'),('meta','meta_l_fy','en','Frisian'),('meta','meta_l_ga','en','Irish'),('meta','meta_l_gd','en','Scots gaelic'),('meta','meta_l_gl','en','Galician'),('meta','meta_l_gn','en','Guarani'),('meta','meta_l_gu','en','Gujarati'),('meta','meta_l_ha','en','Hausa'),('meta','meta_l_he','en','Hebrew'),('meta','meta_l_hi','en','Hindi'),('meta','meta_l_hr','en','Croatian'),('meta','meta_l_hu','en','Hungarian'),('meta','meta_l_hy','en','Armenian'),('meta','meta_l_ia','en','Interlingua'),('meta','meta_l_id','en','Indonesian'),('meta','meta_l_ie','en','Interlingue'),('meta','meta_l_ik','en','Inupiak'),('meta','meta_l_is','en','Icelandic'),('meta','meta_l_it','en','Italian'),('meta','meta_l_iu','en','Inuktitut'),('meta','meta_l_ja','en','Japanese'),('meta','meta_l_jv','en','Javanese'),('meta','meta_l_ka','en','Georgian'),('meta','meta_l_kk','en','Kazakh'),('meta','meta_l_kl','en','Greenlandic'),('meta','meta_l_km','en','Cambodian'),('meta','meta_l_kn','en','Kannada'),('meta','meta_l_ko','en','Korean'),('meta','meta_l_ks','en','Kashmiri'),('meta','meta_l_ku','en','Kurdish'),('meta','meta_l_ky','en','Kirghiz'),('meta','meta_l_la','en','Latin'),('meta','meta_l_ln','en','Lingala'),('meta','meta_l_lo','en','Laothian'),('meta','meta_l_lt','en','Lithuanian'),('meta','meta_l_lv','en','Latvian;lettish'),('meta','meta_l_mg','en','Malagasy'),('meta','meta_l_mi','en','Maori'),('meta','meta_l_mk','en','Macedonian'),('meta','meta_l_ml','en','Malayalam'),('meta','meta_l_mn','en','Mongolian'),('meta','meta_l_mo','en','Moldavian'),('meta','meta_l_mr','en','Marathi'),('meta','meta_l_ms','en','Malay'),('meta','meta_l_mt','en','Maltese'),('meta','meta_l_my','en','Burmese'),('meta','meta_l_na','en','Nauru'),('meta','meta_l_ne','en','Nepali'),('meta','meta_l_nl','en','Dutch'),('meta','meta_l_no','en','Norwegian'),('meta','meta_l_oc','en','Occitan'),('meta','meta_l_om','en','Afan (oromo)'),('meta','meta_l_or','en','Oriya'),('meta','meta_l_pa','en','Punjabi'),('meta','meta_l_pl','en','Polish'),('meta','meta_l_ps','en','Pashto;pushto'),('meta','meta_l_pt','en','Portuguese'),('meta','meta_l_qu','en','Quechua'),('meta','meta_l_rm','en','Rhaeto-romance'),('meta','meta_l_rn','en','Kurundi'),('meta','meta_l_ro','en','Romanian'),('meta','meta_l_ru','en','Russian'),('meta','meta_l_rw','en','Kinyarwanda'),('meta','meta_l_sa','en','Sanskrit'),('meta','meta_l_sd','en','Sindhi'),('meta','meta_l_sg','en','Sangho'),('meta','meta_l_sh','en','Serbo-croatian'),('meta','meta_l_si','en','Singhalese'),('meta','meta_l_sk','en','Slovak'),('meta','meta_l_sl','en','Slovenian'),('meta','meta_l_sm','en','Samoan'),('meta','meta_l_sn','en','Shona'),('meta','meta_l_so','en','Somali'),('meta','meta_l_sq','en','Albanian'),('meta','meta_l_sr','en','Serbian'),('meta','meta_l_ss','en','Siswati'),('meta','meta_l_st','en','Sesotho'),('meta','meta_l_su','en','Sundanese'),('meta','meta_l_sv','en','Swedish'),('meta','meta_l_sw','en','Swahili'),('meta','meta_l_ta','en','Tamil'),('meta','meta_l_te','en','Telugu'),('meta','meta_l_tg','en','Tajik'),('meta','meta_l_th','en','Thai'),('meta','meta_l_ti','en','Tigrinya'),('meta','meta_l_tk','en','Turkmen'),('meta','meta_l_tl','en','Tagalog'),('meta','meta_l_tn','en','Setswana'),('meta','meta_l_to','en','Tonga'),('meta','meta_l_tr','en','Turkish'),('meta','meta_l_ts','en','Tsonga'),('meta','meta_l_tt','en','Tatar'),('meta','meta_l_tw','en','Twi'),('meta','meta_l_ug','en','Uigur'),('meta','meta_l_uk','en','Ukrainian'),('meta','meta_l_ur','en','Urdu'),('meta','meta_l_uz','en','Uzbek'),('meta','meta_l_vi','en','Vietnamese'),('meta','meta_l_vo','en','Volapuk'),('meta','meta_l_wo','en','Wolof'),('meta','meta_l_xh','en','Xhosa'),('meta','meta_l_yi','en','Yiddish'),('meta','meta_l_yo','en','Yoruba'),('meta','meta_l_za','en','Zhuang'),('meta','meta_l_zh','en','Chinese'),('meta','meta_l_zu','en','Zulu'),('meta','meta_language','en','Language'),('meta','meta_learner','en','Learner'),('meta','meta_learning_resource_type','en','Learning Resource Type'),('meta','meta_lecture','en','Lecture'),('meta','meta_lifecycle','en','Lifecycle'),('meta','meta_linear','en','Linear'),('meta','meta_local_file','en','Local File'),('meta','meta_location','en','Location'),('meta','meta_low','en','Low'),('meta','meta_mac_os','en','MacOS'),('meta','meta_manager','en','Manager'),('meta','meta_maximum_version','en','Maximum Version'),('meta','meta_medium','en','Medium'),('meta','meta_meta_metadata','en','Meta-Metadata'),('meta','meta_metadatascheme','en','Metadata Scheme'),('meta','meta_minimum_version','en','Minimum Version'),('meta','meta_mixed','en','Mixed'),('meta','meta_ms_windows','en','MS-Windows'),('meta','meta_multi_os','en','Multi-OS'),('meta','meta_name','en','Name'),('meta','meta_narrative_text','en','Narrative Text'),('meta','meta_networked','en','Networked'),('meta','meta_new_element','en','New Element'),('meta','meta_no','en','No'),('meta','meta_no_annotation','en','No metadata available for the Annotation section.'),('meta','meta_no_classification','en','No metadata available for the Classification section.'),('meta','meta_no_educational','en','No metadata available for Educational section.'),('meta','meta_no_lifecycle','en','No metadata available for Lifecycle section.'),('meta','meta_no_meta_metadata','en','No metadata available for Meta-Metadata section.'),('meta','meta_no_relation','en','No metadata available for the Relation section.'),('meta','meta_no_rights','en','No metadata available for the Rights section.'),('meta','meta_no_technical','en','No metadata available for Technical section.'),('meta','meta_none','en','None'),('meta','meta_operating_system','en','Operating System'),('meta','meta_or_composite','en','Or Composite'),('meta','meta_other','en','Other'),('meta','meta_other_plattform_requirements','en','Other Platform Requirements'),('meta','meta_pc_dos','en','PC-DOS'),('meta','meta_please_select','en','Please select'),('meta','meta_prerequisite','en','Prerequisite'),('meta','meta_problem_statement','en','Problem Statement'),('meta','meta_publisher','en','Publisher'),('meta','meta_purpose','en','Purpose'),('meta','meta_questionnaire','en','Questionnaire'),('meta','meta_quickedit','en','Quick Edit'),('meta','meta_reference','en','Reference'),('meta','meta_references','en','References'),('meta','meta_relation','en','Relation'),('meta','meta_requirement','en','Requirement'),('meta','meta_requires','en','Requires'),('meta','meta_resource','en','Resource'),('meta','meta_revised','en','Revised'),('meta','meta_rights','en','Rights'),('meta','meta_role','en','Role'),('meta','meta_school','en','School'),('meta','meta_scriptwriter','en','Scriptwriter'),('meta','meta_security_level','en','Security Level'),('meta','meta_self_assessment','en','Self Assessment'),('meta','meta_semantic_density','en','Semantic Density'),('meta','meta_simulation','en','Simulation'),('meta','meta_size','en','Size'),('meta','meta_skill_level','en','Skill Level'),('meta','meta_slide','en','Slide'),('meta','meta_source','en','Source'),('meta','meta_status','en','Status'),('meta','meta_structure','en','Structure'),('meta','meta_subjectmatterexpert','en','Subject matter expert'),('meta','meta_table','en','Table'),('meta','meta_taxon','en','Taxon'),('meta','meta_taxon_path','en','Taxon Path'),('meta','meta_teacher','en','Teacher'),('meta','meta_technical','en','Technical'),('meta','meta_technicalimplementer','en','Technical implementer'),('meta','meta_technicalvalidator','en','Technical validator'),('meta','meta_terminator','en','Terminator'),('meta','meta_title','en','Title'),('meta','meta_training','en','Training'),('meta','meta_type','en','Type'),('meta','meta_typical_age_range','en','Typical Age Range'),('meta','meta_typical_learning_time','en','Typical Learning Time'),('meta','meta_unavailable','en','Unavailable'),('meta','meta_unix','en','Unix'),('meta','meta_unknown','en','Unknown'),('meta','meta_validator','en','Validator'),('meta','meta_value','en','Value'),('meta','meta_version','en','Version'),('meta','meta_very_difficult','en','Very Difficult'),('meta','meta_very_easy','en','Very Easy'),('meta','meta_very_high','en','Very High'),('meta','meta_very_low','en','Very Low'),('meta','meta_yes','en','Yes'),('payment','currency_cent','en','Cent'),('payment','currency_euro','en','Euro'),('payment','duration','en','Duration'),('payment','excel_export','en','Excel export'),('payment','info','en','Informations about the pay method'),('payment','pay_add_to_shopping_cart','en','Add to shopping cart'),('payment','pay_added_to_shopping_cart','en','Added item to shopping cart.'),('payment','pay_all','en','All'),('payment','pay_article','en','Article'),('payment','pay_bank_data','en','Bank data'),('payment','pay_bill_no','en','Incoice no.'),('payment','pay_bmf_account_holder','en','Account holder'),('payment','pay_bmf_account_number','en','Account number'),('payment','pay_bmf_back','en','back'),('payment','pay_bmf_bank_code','en','Bank code'),('payment','pay_bmf_card_holder','en','Card holder'),('payment','pay_bmf_card_number','en','Card number'),('payment','pay_bmf_check_number','en','Check number'),('payment','pay_bmf_check_terms_conditions','en','Please confirm that you\'ve read the terms and conditions and accept it.'),('payment','pay_bmf_credit_card','en','Credit card'),('payment','pay_bmf_credit_card_data','en','Credit card'),('payment','pay_bmf_credit_card_not_valid','en','The credit card data you\'ve entered isn\'t valid. Please check out whether all fields are filled out correctly.'),('payment','pay_bmf_debit_entry','en','Debit entry (german bank accounts only)'),('payment','pay_bmf_debit_entry_data','en','Debit entry'),('payment','pay_bmf_debit_entry_not_valid','en','The debit entry data you\'ve entered isn\'t valid. Please check out whether all fields are filled out correctly.'),('payment','pay_bmf_description_credit_card','en','Please check out whether all articles you would like to order are in the shopping cart. By filling in your credit card data and clicking the \"Save\"-button you confirm your wish to order, that starts immediately. The data check may take some seconds.'),('payment','pay_bmf_description_debit_entry','en','Please check out whether all articles you would like to order are in the shopping cart. By filling in your data for the debit entry and clicking the \"Save\"-button you confirm your wish to order, that starts immediately. The data check may take some seconds.'),('payment','pay_bmf_description_payment_type','en','Please select how to pay.'),('payment','pay_bmf_description_personal_data','en','Please fill out the following form completely.'),('payment','pay_bmf_house_number','en','House number'),('payment','pay_bmf_info','en','Access to the following material is not for free. If you want to gain access to a certain learning object please select a price. You can purchase one or several learning objects by using the button \"shopping cart\" on your personal desktop.  You are going to gain immediate access to a chosen object and period.'),('payment','pay_bmf_optional','en','optional'),('payment','pay_bmf_or','en','or'),('payment','pay_bmf_password_not_valid','en','Your password wasn\'t valid.'),('payment','pay_bmf_payment_type','en','Kind of paying'),('payment','pay_bmf_payment_type_not_valid','en','The kind of paying you\'ve choosen isn\'t valid. Please select another option.'),('payment','pay_bmf_personal_data','en','Your personal data'),('payment','pay_bmf_personal_data_not_valid','en','Your personal data isn\'t valid. Please check out whether all fields are filled out correctly.'),('payment','pay_bmf_please_select','en','Please select'),('payment','pay_bmf_po_box','en','PO Box'),('payment','pay_bmf_server_error_code','en','Payment server returns error code'),('payment','pay_bmf_server_error_communication','en','Error while communictaion with payment server.'),('payment','pay_bmf_server_error_sysadmin','en','Please contact system administrator for further information.'),('payment','pay_bmf_shopping_cart_empty','en','Your shopping card is empty.'),('payment','pay_bmf_street_or_pobox','en','Please enter street and house number or po box.'),('payment','pay_bmf_terms_conditions','en','General terms and conditions'),('payment','pay_bmf_terms_conditions_read','en','I\'ve read the general terms and conditions and accept it.'),('payment','pay_bmf_terms_conditions_show','en','show'),('payment','pay_bmf_thanks','en','Thank you. Your order was submitted.'),('payment','pay_bmf_total_amount','en','Total amount'),('payment','pay_bmf_validity','en','valid until'),('payment','pay_bmf_vat_included','en','VAT included'),('payment','pay_bmf_your_order','en','Your order'),('payment','pay_click_to_buy','en','Buy now'),('payment','pay_confirm_order','en','Please confirm your order by entering your password.'),('payment','pay_customer_no','en','Customer no.'),('payment','pay_deleted_booking','en','The entry has been deleted.'),('payment','pay_deleted_items','en','The selected items have been deleted.'),('payment','pay_ending','en','ends with'),('payment','pay_entitled_retrieve','en','Entitled to retrieve data for'),('payment','pay_expires_info','en','This object is disposable. The payment has been expired, you can not buy it.'),('payment','pay_filter','en','Filter'),('payment','pay_goto_buyed_objects','en','Goto your buyed objects'),('payment','pay_goto_shopping_cart','en','Goto the shopping cart'),('payment','pay_header','en','Payment'),('payment','pay_item_already_in_sc','en','This object already exists in your shopping cart.'),('payment','pay_item_already_in_sc_choose_another','en','This object was already taken up in your shopping cart. Select if necessary a new price/duration combination.'),('payment','pay_locator','en','Payment'),('payment','pay_message_attachment','en','As a confirmation for your order we send you a bill (PDF format) as attachment.'),('payment','pay_message_hello','en','Dear'),('payment','pay_message_regards','en','Yours sincerely'),('payment','pay_message_subject','en','Your ILIAS order'),('payment','pay_message_thanks','en','Thank you for your ILIAS 3 order.'),('payment','pay_no_vendors_created','en','No vendors created'),('payment','pay_not_buyed_any_object','en','You have not bought any object.'),('payment','pay_order_date_from','en','Order date from'),('payment','pay_order_date_til','en','Order date til'),('payment','pay_payed_credit_card','en','The order was paid by credit card.'),('payment','pay_payed_debit_entry','en','The order was paid by debit entry.'),('payment','pay_reset_filter','en','Reset filter'),('payment','pay_select_one_item','en','Please select one item.'),('payment','pay_select_price','en','Please select a new price.'),('payment','pay_send_order','en','Send order'),('payment','pay_shopping_cart_empty','en','Your shopping cart is empty.'),('payment','pay_starting','en','starts with'),('payment','pay_step1','en','Your order - Schritt 1/3: Enter your personal data'),('payment','pay_step2','en','Your order - Schritt 2/3: Choose kind of payment'),('payment','pay_step3_credit_card','en','Your order - Schritt 3/3: Enter your credit card data and send your order'),('payment','pay_step3_debit_entry','en','Your order - Schritt 3/3: Enter your bank data and send your order'),('payment','pay_update_view','en','Update view'),('payment','paya_access','en','Access'),('payment','paya_add_price','en','Add new price'),('payment','paya_add_price_title','en','New price'),('payment','paya_added_new_object','en','Added new object. Please edit the pay method and the prices.'),('payment','paya_added_new_price','en','Created new price'),('payment','paya_added_trustee','en','Created new trustee.'),('payment','paya_bookings_available','en','This object has been sold. Please delete all statistic data first.'),('payment','paya_buyable','en','Buyable'),('payment','paya_buyed_objects','en','Buyed objects'),('payment','paya_count_purchaser','en','Number of purchasers'),('payment','paya_customer','en','Customer'),('payment','paya_delete_price','en','Delete price'),('payment','paya_delete_trustee_msg','en','Deleted selected trustees.'),('payment','paya_deleted_last_price','en','The last price has been deleted.'),('payment','paya_deleted_object','en','Stoped payment for the selected object.'),('payment','paya_details_updated','en','Details updated.'),('payment','paya_disabled','en','Disabled'),('payment','paya_edit_details','en','Edit details'),('payment','paya_edit_pay_method','en','Edit pay method'),('payment','paya_edit_prices','en','Edit prices'),('payment','paya_edit_prices_first','en','Please edit the prices first.'),('payment','paya_enabled','en','Enabled'),('payment','paya_enter_login','en','Please enter an user name.'),('payment','paya_err_adding_object','en','Error adding object'),('payment','paya_error_update_booking','en','Error saving statistic data.'),('payment','paya_expires','en','Expire payment'),('payment','paya_filter_reseted','en','Filter reseted.'),('payment','paya_header','en','Payment administration'),('payment','paya_insert_only_numbers','en','Please insert only integers.'),('payment','paya_locator','en','Payment administration'),('payment','paya_months','en','Month(s)'),('payment','paya_no_booking_id_given','en','Error: no \'booking id\' given'),('payment','paya_no_bookings','en','No statistic available.'),('payment','paya_no_object_selected','en','Please select one object.'),('payment','paya_no_objects_assigned','en','There are no sellable objects.'),('payment','paya_no_price_available','en','No prices available'),('payment','paya_no_prices_selected','en','Please select one price'),('payment','paya_no_settings_necessary','en','There are no settings necessary for this pay method'),('payment','paya_no_trustees','en','No trustees created.'),('payment','paya_no_valid_login','en','This user name is not valid.'),('payment','paya_no_vendor_selected','en','You haven\'t selected a vendor.'),('payment','paya_not_assign_yourself','en','It is not possible to assign yourself.'),('payment','paya_not_buyable','en','Not buyable'),('payment','paya_object','en','Objekts'),('payment','paya_object_not_purchasable','en','It is not possible to sell this object.'),('payment','paya_order_date','en','Order date'),('payment','paya_pay_method','en','Pay method'),('payment','paya_pay_method_not_specified','en','Not specified'),('payment','paya_payed','en','Payed'),('payment','paya_payed_access','en','Payed/Access'),('payment','paya_perm_obj','en','Edit objects'),('payment','paya_perm_stat','en','Edit statistics'),('payment','paya_price_not_valid','en','This price is not valid. Please insert only numbers'),('payment','paya_select_object_to_sell','en','Please select the object which you want to sell.'),('payment','paya_select_pay_method_first','en','Please select one pay method first.'),('payment','paya_sell_object','en','Sell object'),('payment','paya_shopping_cart','en','Shopping cart'),('payment','paya_statistic','en','Statistic'),('payment','paya_sure_delete_object','en','Are you sure, you want to stop payment for this object? All assigned data will get lost.'),('payment','paya_sure_delete_selected_prices','en','Are you sure you want to delete the selected prices?'),('payment','paya_sure_delete_selected_trustees','en','Are you sure you want to delete the selected trustees?'),('payment','paya_sure_delete_stat','en','Are you sure, you want to delete this statistic?'),('payment','paya_transaction','en','Transaction'),('payment','paya_trustee_table','en','Trustees'),('payment','paya_trustees','en','Trustees'),('payment','paya_update_price','en','Update prices'),('payment','paya_updated_booking','en','Updated statistic.'),('payment','paya_updated_prices','en','Updated prices'),('payment','paya_updated_trustees','en','Updated permissions.'),('payment','paya_user_already_assigned','en','User already assigned.'),('payment','paya_vendor','en','Vendor'),('payment','pays_active_bookings','en','There active bookings for the selected vendor(s). Please delete them first.'),('payment','pays_add_info','en','Additional information for bill (optional)'),('payment','pays_add_vendor','en','Add vendor'),('payment','pays_added_vendor','en','Added new vendor'),('payment','pays_address','en','Address for bill'),('payment','pays_already_assigned_vendors','en','The following number of users were already assigned:'),('payment','pays_assigned_vendors','en','The following number of users have been assigned to the vendor list:'),('payment','pays_bank_data','en','Bank data for bill'),('payment','pays_bill','en','Bill'),('payment','pays_bmf','en','BMF'),('payment','pays_cost_center','en','Cost center'),('payment','pays_cost_center_not_valid','en','Please enter a valid cost center.'),('payment','pays_currency_subunit','en','Currency (1/100) (e.g. Cent)'),('payment','pays_currency_unit','en','Currency (e.g. EUR)'),('payment','pays_delete_vendor','en','Deassign user'),('payment','pays_deleted_number_vendors','en','The following number of vendors have been deleted:'),('payment','pays_edit_vendor','en','Edit user'),('payment','pays_general_settings','en','General settings'),('payment','pays_general_settings_not_valid','en','Your general settings aren\'t valid. Please check out whether all fields are filled out correctly.'),('payment','pays_header_select_vendor','en','Vendor selection'),('payment','pays_no_username_given','en','No username given'),('payment','pays_no_valid_username_given','en','No valid username given'),('payment','pays_no_vendor_selected','en','No vendor selected'),('payment','pays_number_bookings','en','Active bookings'),('payment','pays_objects_bill_exist','en','Pay method \'bill\' is activated for some objects. Therefore it can not be deactivated.'),('payment','pays_objects_bmf_exist','en','Pay method \'BMF\' is activated for some objects. Therefore it can not be deactivated.'),('payment','pays_offline','en','Offline'),('payment','pays_online','en','Online'),('payment','pays_pay_methods','en','Pay methods'),('payment','pays_pdf_path','en','Path for generating bill as PDF file'),('payment','pays_sure_delete_selected_vendors','en','Do you really want to delete the selected vendor(s)'),('payment','pays_too_many_vendors_selected','en','Please select only one customer to perform this action.'),('payment','pays_updated_general_settings','en','Your general settings were updated.'),('payment','pays_updated_pay_method','en','Changed pay method.'),('payment','pays_user_already_assigned','en','This user is already assigned to the vendor list'),('payment','pays_vat_rate','en','VAT rate in % (optional)'),('payment','pays_vendor','en','Vendor'),('payment','price_a','en','Price'),('payment','prices','en','Prices'),('payment','update','en','Update'),('pwassist','password_assistance','en','Password Assistance'),('pwassist','pwassist_enter_email','en','Please enter an email address and submit the form.\\nILIAS will send an email to that address. The email contains all usernames which have registered this email address.\\nChoose a suitable username and use the password service to retrieve a new password. If you do not retrieve any email by this service please contact your course admin or %1$s.'),('pwassist','pwassist_enter_username_and_email','en','Enter a user name and the associated email address in the fields shown below.\\nILIAS will send a message to that email address. The message contains an address for a web page, where you can enter a new password for the user account.\\nIn case your unable to assign a password to your user account using this form, contact your course administration or send an email to %1$s.'),('pwassist','pwassist_enter_username_and_new_password','en','Enter the user name and the new password in the fields below.'),('pwassist','pwassist_invalid_email','en','Please correct your email address.\\nThe given email address could not be found in the database.'),('pwassist','pwassist_invalid_username_or_email','en','Please correct the text in the entry fields.\\nThe user name and email address you have entered do not match an entry in the database.'),('pwassist','pwassist_login_not_match','en','Please enter another user name.\\nThe user name you have entered does not match the user name for which you had asked for password assistance.'),('pwassist','pwassist_mail_body','en','Register a new password for your user account:\\n\\t%1$s\\n\\nThis message has been generated automatically by the ILIAS server\\n\\t%2$s\\n\\nYou (or someone at %3$s) has asked for password assistance for the user account \"%4$s\".\\n\\nPlease check carefully the conditions listed below, and proceed accordingly:\\n\\n- If you have used the password assistance form on the ILIAS server by accident:\\nDelete this mail.\\n\\n- If you are certain, that you never asked for password assistance at this ILIAS server:\\nPlease contact %5$s.\\n\\n- If you asked for password assistance, please proceed as follows:\\n\\n1. Open your browser.\\n\\n2. Enter the following address in your browser:\\n\\t%1$s\\n\\nImportant: The address is a single line. If you see this address split into multiple lines, then your email program has inserted these line breaks.\\n\\n3. On the web page shown by your browser, enter a new password for your user account.\\n\\nPlease note, that for security reasons, you can perform you can perform the three steps abeove only exactly once and for a limited time only. Afterwards the address becomes invalid, and you have to use the password assistance page on the ILIAS server again.'),('pwassist','pwassist_mail_sent','en','A message has been sent to %1$s.\\nPlease check your mail box.'),('pwassist','pwassist_mail_subject','en','ILIAS Password Assistance'),('pwassist','pwassist_not_permitted','en','Please enter another user name.\\nPassword assistance is not permitted for the user name you have entered.'),('pwassist','pwassist_password_assigned','en','The password has been successfully assigned to user \"%1$s\".'),('pwassist','pwassist_session_expired','en','Please fill in this form again.\\nYour password assistance session has expired. This may have happened, because you tried to use the link that has been emailed to you more than once, or because too much time has passed since the link has been sent to you.'),('pwassist','pwassist_update_error','en','Please contact your system administrator.\\nThe password could not be assigned to the user account due to an error whe updating the data base.'),('pwassist','pwassist_username_mail_body','en','These are the active username found for the given email address:\\n%s\\n\\nThis message has been created automatically by the following ILIAS Server:\\n\\t%s\\n\\nYou (or somebody with IP  %s) have requested support for forgotten usernames for the email address \'%s\'.\\n\\nPlease check the following and act as suggested::\\n\\n-You have requested this mail by accident:\\nDelete this email.\\n\\n-You are sure, that you never requested this email:\\nPlease contact %s.\\n\\n- If you requested this email, please proceed as follows::\\n\\n1. Start your internet browser.\\n\\n2. Enter the following url:\\n\\t%s\\n\\nImportant: The address is a single line. If you see this address split into multiple lines, then your email program has inserted these line breaks.\\n\\n3. Your Browser now shows the Password-Service. Use this page together with one of the usernames and the according email address to retrieve a new password.'),('rbac','edit_learning_progress','en','Edit learning progress'),('rbac','filter_all_roles','en','Show all roles of current context'),('rbac','filter_global_roles','en','Show only global roles'),('rbac','filter_local_roles','en','Show only local roles of current context'),('rbac','filter_local_roles_object','en','Show only local roles defined at this position'),('rbac','filter_roles_local_policy','en','Show only roles using a local policy at this position'),('rbac','leave','en','Leave'),('rbac','perm_administrate','en','Administrate'),('rbac','perm_class_create','en','Create new objects'),('rbac','perm_class_create_desc','en','Determine which object types may be created under the current object.'),('rbac','perm_class_general','en','General operations'),('rbac','perm_class_general_desc','en','Common operations available for all objects. Hover the mousepointer over operation to get more information.'),('rbac','perm_class_object','en','Special operations'),('rbac','perm_class_object_desc','en','Object-specific operations, only available to certain object types.'),('rbac','perm_class_rbac','en','Permission settings'),('rbac','perm_class_rbac_desc','en','Access control to Permission settings (this menu) and local policy settings for automatic permission settings for new created objects.'),('rbac','perm_local_role','en','Locale role'),('rbac','perm_local_role_desc','en','This role is locally defined at the current object and correspond to a local policy'),('rbac','perm_use_local_policy','en','Use local policy'),('rbac','perm_use_local_policy_desc','en','If local policy is activated, you may define different default permission settings for the current object.'),('search','search_active','en','Include active users'),('search','search_added_new_folder','en','Added new folder.'),('search','search_advanced','en','Advanced search'),('search','search_all_results','en','All results'),('search','search_all_words','en','All words'),('search','search_and','en','and'),('search','search_any','en','-- Any --'),('search','search_any_word','en','Any word'),('search','search_area','en','Search area'),('search','search_area_info','en','Please select an area where the search should start.'),('search','search_below','en','Beneath:'),('search','search_change','en','Change'),('search','search_choose_object_type','en','Please choose one object type.'),('search','search_concatenation','en','Concatenation'),('search','search_content','en','Page Content'),('search','search_dbk_content','en','Digital Library (content)'),('search','search_dbk_meta','en','Digital Library (metadata)'),('search','search_delete_sure','en','The object and its contents will be deleted permanently'),('search','search_details_info','en','Detail search. Please select one or more objects above.'),('search','search_direct','en','Direct search'),('search','search_fast_info','en','Search for titles, descriptions and keywords in all object types'),('search','search_full_info','en','Choose this option to search in a large amount of data.'),('search','search_group','en','Groups'),('search','search_hits','en','Found in:'),('search','search_in_magazin','en','In the repository'),('search','search_in_result','en','Search within results'),('search','search_inactive','en','Include inactive users'),('search','search_index','en','Indexed search'),('search','search_like_info','en','Choose this option to get best results.'),('search','search_limit_reached','en','Your search produced more than %s hits. You can restrict the search terms to receive more detailed results.'),('search','search_lm_content','en','Learning materials (content)'),('search','search_lm_meta','en','Learning materials (metadata)'),('search','search_lucene','en','Lucene search'),('search','search_lucene_host','en','Lucene-Server'),('search','search_lucene_info','en','If activated, it is possible to search in PDF, HTML files and HTML-Learning modules'),('search','search_lucene_port','en','Lucene-Port'),('search','search_lucene_readme','en','Setup informations'),('search','search_meta','en','Metadata'),('search','search_minimum_three','en','Your search must be at least three characters long'),('search','search_move_folder_not_allowed','en','Folder cannot be moved.'),('search','search_move_to','en','Move to:'),('search','search_my_search_results','en','My search results'),('search','search_new_folder','en','New folder'),('search','search_no_category','en','You have not selected any search categories'),('search','search_no_connection_lucene','en','Cannot connect to Lucene server.'),('search','search_no_match','en','Your search did not match any results'),('search','search_no_results_saved','en','No results saved.'),('search','search_no_search_term','en','You have not selected any search terms'),('search','search_no_selection','en','You made no selection.'),('search','search_object_renamed','en','Object renamed.'),('search','search_objects_deleted','en','Object(s) deleted.'),('search','search_objects_moved','en','Objects moved.'),('search','search_one_action','en','Select one action.'),('search','search_or','en','or'),('search','search_rename_title','en','Rename title'),('search','search_results_saved','en','Search results saved'),('search','search_save_as_select','en','Save as:'),('search','search_search_for','en','Search for'),('search','search_search_no_results_saved','en','There are no search results saved'),('search','search_search_results','en','Search results'),('search','search_search_term','en','Search term'),('search','search_select_exactly_one_object','en','You must select exactly one object.'),('search','search_select_folder','en','Please select exactly one folder. It is not possible to rename search results.'),('search','search_select_one','en','Select one folder'),('search','search_select_one_action','en','--Select one action--'),('search','search_select_one_folder_select','en','--Select one folder--'),('search','search_select_one_result','en','Select at least one search result'),('search','search_select_one_select','en','--Select one folder--'),('search','search_show_result','en','Show'),('search','search_tst_svy','en','Tests/Surveys'),('search','search_type','en','Search type'),('search','search_user','en','Users'),('search','until','en','up to'),('survey','add_category','en','Add answer'),('survey','add_heading','en','Add heading'),('survey','add_limits_for_standard_numbers','en','Please enter a lower and upper limit for the standard numbers you want to add as answers.'),('survey','add_phrase','en','Add phrase'),('survey','add_phrase_introduction','en','Please select a phrase:'),('survey','add_standard_numbers','en','Add standard numbers'),('survey','already_completed_survey','en','You have already finished the survey! You are not able to enter the survey again.'),('survey','anonymize_anonymous_introduction','en','This survey anonymizes all user data. To grant you access to the survey, you must use a 32-character survey code which you can receive from the creator/maintainer of this survey<br />Please enter it in the text field below.'),('survey','anonymize_key_introduction','en','This survey anonymizes all user data. To grant you access to the survey, a 32-character code was created by the system and will be sent to your ILIAS mail inbox when you start the survey (after answering the first question). Your survey access code is<br /><span class=\"bold\">%s</span><br />Please enter it in the text field below.'),('survey','anonymize_resume_introduction','en','Please enter your survey access code to resume the survey. It was sent to your ILIAS mail inbox when you started the survey.'),('survey','anonymize_survey','en','Anonymize survey data'),('survey','anonymize_survey_explanation','en','When activated, all user data will be anonymized and cannot be tracked.'),('survey','anonymous_with_personalized_survey','en','You must be a registered user to run a personalized survey!'),('survey','answer','en','Answer'),('survey','apply','en','Apply'),('survey','arithmetic_mean','en','Arithmetic mean'),('survey','ask_insert_questionblocks','en','Are you sure you want to insert the following question block(s) to the survey?'),('survey','ask_insert_questions','en','Are you sure you want to insert the following question(s) to the survey?'),('survey','back','en','<< Back'),('survey','before','en','before'),('survey','browse_for_questions','en','Browse for questions'),('survey','cannot_create_survey_codes','en','You do not possess sufficient permissions to create survey access codes!'),('survey','cannot_edit_survey','en','You do not possess sufficient permissions to edit the survey!'),('survey','cannot_export_questionpool','en','You do not possess sufficient permissions to export the survey question pool!'),('survey','cannot_export_survey','en','You do not possess sufficient permissions to export the survey!'),('survey','cannot_maintain_survey','en','You do not possess sufficient permissions to maintain the survey!'),('survey','cannot_manage_phrases','en','You do not possess sufficient permissions to manage the phrases!'),('survey','cannot_participate_survey','en','You do not possess sufficient permissions to participate in the survey!'),('survey','cannot_read_questionpool','en','You do not possess sufficient permissions to read the question pool data!'),('survey','cannot_read_survey','en','You do not possess sufficient permissions to read the survey data!'),('survey','cannot_save_metaobject','en','You do not possess sufficient permissions to save the meta data!'),('survey','cannot_switch_to_online_no_questions','en','The status cannot be changed to &quot;online&quot; because there are no questions in the survey!'),('survey','categories','en','Answers'),('survey','category','en','Answer'),('survey','category_delete_confirm','en','Are you sure you want to delete the following answers?'),('survey','category_delete_select_none','en','Please check at least one answer to delete it!'),('survey','category_nr_selected','en','Number of users that selected this answer'),('survey','chart','en','Chart'),('survey','check_category_to_save_phrase','en','You must select a least one answer to save a phrase!'),('survey','codes','en','Access codes'),('survey','combobox','en','Combobox'),('survey','concatenation','en','Concatenation'),('survey','confirm_delete_all_user_data','en','Are you sure you want to delete all user data of the survey?'),('survey','confirm_remove_heading','en','Are you sure you want to remove the heading?'),('survey','confirm_sync_questions','en','The question you changed is a copy which has been created for use with the active survey. Do you want to change the original of the question too?'),('survey','constraint_add','en','Add precondition'),('survey','constraints','en','Preconditions'),('survey','constraints_first_question_description','en','The first entity could not have any preconditions because there are no previous questions.'),('survey','constraints_introduction','en','For every entity (a single question or a question block with multiple questions) one or more preconditions could be defined. These preconditions will be derived from the answers of the survey participants of questions which occur earlier in the survey. Since the first entity has no previous questions or questionblocks there is no possibility to define preconditions for it.'),('survey','constraints_list_of_entities','en','Available survey entities for preconditions'),('survey','constraints_no_nonessay_available','en','There are no previous questions or only essay questions available to calculate preconditions (essay questions are not valid to calculate preconditions)'),('survey','constraints_no_questions_or_questionblocks_selecte','en','Please select at least one question or questionblock!'),('survey','contains','en','Contains'),('survey','continue','en','Continue >>'),('survey','create_date','en','Created'),('survey','create_new','en','Create new'),('survey','create_questionpool_before_add_question','en','You must create at least one question pool to store your questions!'),('survey','dc_agree','en','agree'),('survey','dc_always','en','always'),('survey','dc_definitelyfalse','en','definitely false'),('survey','dc_definitelytrue','en','definitely true'),('survey','dc_desired','en','desired'),('survey','dc_disagree','en','disagree'),('survey','dc_fair','en','fair'),('survey','dc_false','en','false'),('survey','dc_good','en','good'),('survey','dc_manytimes','en','many times'),('survey','dc_morenegative','en','more negative'),('survey','dc_morepositive','en','more positive'),('survey','dc_mostcertainly','en','most certainly'),('survey','dc_mostcertainlynot','en','most certainly not'),('survey','dc_must','en','must'),('survey','dc_mustnot','en','must not'),('survey','dc_neutral','en','neutral'),('survey','dc_never','en','never'),('survey','dc_no','en','no'),('survey','dc_notacceptable','en','not acceptable'),('survey','dc_poor','en','poor'),('survey','dc_rarely','en','rarely'),('survey','dc_should','en','should'),('survey','dc_shouldnot','en','should not'),('survey','dc_sometimes','en','sometimes'),('survey','dc_stronglyagree','en','strongly agree'),('survey','dc_stronglydesired','en','strongly desired'),('survey','dc_stronglydisagree','en','strongly disagree'),('survey','dc_stronglyundesired','en','strongly undesired'),('survey','dc_true','en','true'),('survey','dc_undecided','en','undecided'),('survey','dc_undesired','en','undesired'),('survey','dc_varying','en','varying'),('survey','dc_verygood','en','very good'),('survey','dc_yes','en','yes'),('survey','define_questionblock','en','Define question block'),('survey','description_maxchars','en','If nothing entered the maximum number of characters for this text answer is unlimited.'),('survey','disinvite','en','Disinvite'),('survey','display_all_available','en','Display all available'),('survey','dp_standard_attitude_agree5','en','Standard attitude (strongly agree-agree-undecided-disagree-strongly disagree)'),('survey','dp_standard_attitude_agree_disagree','en','Standard attitude (agree-disagree)'),('survey','dp_standard_attitude_agree_undecided_disagree','en','Standard attitude (agree-undecided-disagree)'),('survey','dp_standard_attitude_desired5','en','Standard attitude (strongly desired-desired-neutral-undesired-strongly undesired)'),('survey','dp_standard_attitude_desired_neutral_undesired','en','Standard attitude (desired-neutral-undesired)'),('survey','dp_standard_attitude_desired_undesired','en','Standard attitude (desired-undesired)'),('survey','dp_standard_attitude_good5','en','Standard attitude (very good-good-fair-poor-not acceptable)'),('survey','dp_standard_attitude_good_fair_notacceptable','en','Standard attitude (good-fair-not acceptable)'),('survey','dp_standard_attitude_good_notacceptable','en','Standard attitude (good-not acceptable)'),('survey','dp_standard_attitude_must5','en','Standard attitude (must-should-undecided-should not-must not)'),('survey','dp_standard_attitude_shold_shouldnot','en','Standard attitude (should-should not)'),('survey','dp_standard_attitude_should_undecided_shouldnot','en','Standard attitude (should-undecided-should not)'),('survey','dp_standard_behaviour_certainly5','en','Standard behavior (most certainly-more positive-undecided-more negative-most certainly not)'),('survey','dp_standard_behaviour_yes_no','en','Standard behavior (yes-no)'),('survey','dp_standard_behaviour_yes_undecided_no','en','Standard behavior (yes-undecided-no)'),('survey','dp_standard_beliefs_always5','en','Standard beliefs (always-many times-varying-rarely-never)'),('survey','dp_standard_beliefs_always_never','en','Standard beliefs (always-never)'),('survey','dp_standard_beliefs_always_sometimes_never','en','Standard beliefs (always-sometimes-never)'),('survey','dp_standard_beliefs_true5','en','Standard beliefs (definitely true-true-undecided-false-definitely false)'),('survey','dp_standard_beliefs_true_false','en','Standard beliefs (true-false)'),('survey','dp_standard_beliefs_true_undecided_false','en','Standard beliefs (true-undecided-false)'),('survey','dp_standard_numbers','en','Standard numbers'),('survey','duplicate','en','Duplicate'),('survey','duplicate_svy','en','Duplicate survey'),('survey','edit_ask_continue','en','Do you want to continue and edit this question?'),('survey','edit_constraints_introduction','en','You have selected the following question(s)/question block(s) to edit the preconditions'),('survey','edit_heading','en','Edit heading'),('survey','end_date','en','End date'),('survey','end_date_reached','en','You cannot start the survey. The end date is reached!'),('survey','enter_anonymous_id','en','Survey access code'),('survey','enter_phrase_title','en','Please enter a phrase title'),('survey','enter_questionblock_title','en','Please enter a questionblock title!'),('survey','enter_valid_number_of_codes','en','Please enter a valid number to generate survey access codes!'),('survey','enter_value','en','Enter a value'),('survey','error_add_heading','en','Please add a heading!'),('survey','error_importing_question','en','There was an error importing the question(s) from the file you have selected!'),('survey','error_retrieving_anonymous_survey','en','The system could not find your survey data for survey code &quot;%s&quot;. Please check the survey code you have entered!'),('survey','eval_legend_link','en','Please refer to the legend for the meaning of the column header symbols.'),('survey','evaluation','en','Evaluation'),('survey','evaluation_access','en','Evaluation access'),('survey','evaluation_access_all','en','Evaluation access for all users'),('survey','evaluation_access_participants','en','Evaluation access for survey participants'),('survey','existing_constraints','en','Existing preconditions'),('survey','exit','en','Exit'),('survey','exp_type_csv','en','Comma separated value (CSV)'),('survey','exp_type_excel','en','Microsoft Excel (Intel x86)'),('survey','exp_type_excel_mac','en','Microsoft Excel (IBM PPC)'),('survey','export_data_as','en','Export data as'),('survey','fill_out_all_category_fields','en','Please fill out all answer fields before you add a new one!'),('survey','fill_out_all_required_fields_add_category','en','Please fill out all required fields before you add answers!'),('survey','fill_out_all_required_fields_delete_category','en','Please fill out all required fields before you delete answers!'),('survey','fill_out_all_required_fields_move_category','en','Please fill out all required fields before you move answers!'),('survey','fill_out_all_required_fields_save_phrase','en','Please fill out all required fields before you save a phrase!'),('survey','filter','en','Filter'),('survey','filter_all_question_types','en','All question types'),('survey','filter_all_questionpools','en','All question pools'),('survey','filter_show_question_types','en','Show question types'),('survey','filter_show_questionpools','en','Show question pools'),('survey','form_data_modified_press_save','en','The form data has been modified. Press the save button to keep the changes.'),('survey','found_questions','en','Found questions'),('survey','geometric_mean','en','Geometric mean'),('survey','given_answers','en','Given answers'),('survey','glossary_term','en','Glossary term'),('survey','harmonic_mean','en','Harmonic mean'),('survey','heading','en','Heading'),('survey','horizontal','en','horizontal'),('survey','import_error_closing_file','en','Error closing the import file!'),('survey','import_error_opening_file','en','Error opening the import file!'),('survey','import_error_survey_no_proper_values','en','The survey properties do not contain proper values!'),('survey','import_error_survey_no_properties','en','No survey properties found. Cannot import the survey!'),('survey','import_no_file_selected','en','No file selected!'),('survey','import_question','en','Import question(s)'),('survey','import_wrong_file_type','en','Wrong file type!'),('survey','insert_after','en','Insert after'),('survey','insert_before','en','Insert before'),('survey','insert_missing_question','en','Please select at least one question to insert it into the survey!'),('survey','insert_missing_questionblock','en','Please select at least one question block to insert it into the survey!'),('survey','introduction','en','Introduction'),('survey','introduction_manage_phrases','en','Manage your own phrases which are available for all the ordinal questions you edit/create.'),('survey','invitation','en','Invitation'),('survey','invitation_mode','en','Invitation mode'),('survey','invite_participants','en','Invite participants'),('survey','invited_groups','en','Invited groups'),('survey','invited_users','en','Invited users'),('survey','label_resume_survey','en','Enter your 32 digit survey code'),('survey','last_update','en','Last update'),('survey','legend','en','Legend'),('survey','lower_limit','en','Lower limit'),('survey','maintenance','en','Maintenance'),('survey','manage_phrases','en','Manage phrases'),('survey','material','en','Material'),('survey','material_added_successfully','en','You successfully set a material link!'),('survey','material_file','en','Material file'),('survey','maxchars','en','Maximum number of characters'),('survey','maximum','en','Maximum value'),('survey','median','en','Median'),('survey','median_between','en','between'),('survey','menuback','en','Back'),('survey','menubacktosurvey','en','Back to the survey'),('survey','message_mail_survey_id','en','You have started the anonymized survey &quot;%s&quot;. To resume this survey you need the following 32 digit survey code:<br><br><strong>%s</strong><br><br>Resuming a survey is only possible, if you interrupt the survey before finishing it.'),('survey','metric_question_floating_point','en','The value you entered is a floating point value. Floating point values are not allowed for this question type!'),('survey','metric_question_not_a_value','en','The value you entered is not a numeric value!'),('survey','metric_question_out_of_bounds','en','The value you entered is not between the minimum and maximum value!'),('survey','minimum','en','Minimum value'),('survey','missing_upper_or_lower_limit','en','Please enter a lower and an upper limit!'),('survey','mode','en','Most selected value'),('survey','mode_nr_of_selections','en','Nr of selections'),('survey','mode_text','en','Most selected value (Text)'),('survey','multiple_choice_multiple_response','en','Multiple choice (multiple response)'),('survey','multiple_choice_single_response','en','Multiple choice (single response)'),('survey','new_survey_codes','en','new survey access code(s)'),('survey','next_question_rows','en','Questions %d - %d of %d >>'),('survey','no_available_constraints','en','There are no preconditions defined!'),('survey','no_category_selected_for_deleting','en','Please select a least one answer if you want to delete answers!'),('survey','no_category_selected_for_move','en','Please check at least one answer to move it!'),('survey','no_constraints_checked','en','Please select a least one question or question block to edit the preconditions!'),('survey','no_question_selected_for_move','en','Please check at least one question to move it!'),('survey','no_question_selected_for_removal','en','Please check at least one question or question block to remove it!'),('survey','no_questionblocks_available','en','There are no question blocks available'),('survey','no_questions_available','en','There are no questions available!'),('survey','no_search_results','en','There are no search results!'),('survey','no_target_selected_for_move','en','You must select a target position!'),('survey','no_user_or_group_selected','en','Please check an option what are you searching for (users and/or groups)!'),('survey','no_user_phrases_defined','en','There are no user defined phrases available!'),('survey','nominal_question_not_checked','en','Please check one of the offered answers!'),('survey','non_ratio','en','Non-Ratio'),('survey','not_used','en','not used'),('survey','obligatory','en','obligatory'),('survey','off','en','off'),('survey','offline','en','offline'),('survey','on','en','on'),('survey','online','en','online'),('survey','or','en','or'),('survey','ordinal_question_not_checked','en','Please check one of the offered answers!'),('survey','orientation','en','Orientation'),('survey','percentage_of_entered_values','en','Percentage of users that entered this value'),('survey','percentage_of_offered_answers','en','Percentage of offered answers'),('survey','percentage_of_selections','en','Percentage of users that selected this answer'),('survey','phrase_saved','en','The phrase was saved successfully!'),('survey','predefined_users','en','Predefined user set'),('survey','preview','en','Preview'),('survey','previous_question_rows','en','<< Questions %d - %d of %d'),('survey','qpl_confirm_delete_phrases','en','Are you sure you want to delete the following phrase(s)?'),('survey','qpl_confirm_delete_questions','en','Are you sure you want to delete the following question(s)?'),('survey','qpl_copy_select_none','en','Please check at least one question to copy it!'),('survey','qpl_define_questionblock_select_missing','en','Please select a least two questions if you want to define a question block!'),('survey','qpl_delete_phrase_select_none','en','Please check at least one phrase to delete it'),('survey','qpl_delete_rbac_error','en','You have no rights to delete the question(s)!'),('survey','qpl_delete_select_none','en','Please check at least one question to delete it!'),('survey','qpl_duplicate_select_none','en','Please check at least one question to duplicate it!'),('survey','qpl_export_select_none','en','Please check at least one question to export it'),('survey','qpl_past_questions_confirmation','en','Are you sure you want to paste the following question(s) in the question pool?'),('survey','qpl_phrases_deleted','en','Phrase(s) deleted.'),('survey','qpl_questions_deleted','en','Question(s) deleted.'),('survey','qpl_questions_pasted','en','Question(s) pasted.'),('survey','qpl_savephrase_empty','en','Please enter a phrase title!'),('survey','qpl_savephrase_exists','en','The phrase title already exists! Please enter another phrase title.'),('survey','qpl_unfold_select_none','en','Please select at least one question block if you want to unfold question blocks!'),('survey','qt_metric','en','Metric question'),('survey','qt_nominal','en','Nominal question'),('survey','qt_ordinal','en','Ordinal question'),('survey','qt_text','en','Essay'),('survey','question_contains_no_categories','en','There are no answers defined for this question'),('survey','question_has_constraints','en','Preconditions'),('survey','question_icon','en','Question icon'),('survey','question_obligatory','en','The question is obligatory!'),('survey','question_saved_for_upload','en','The question was saved automatically in order to reserve hard disk space to store the uploaded file. If you cancel this form now, be aware that you must delete the question in the question pool if you do not want to keep it!'),('survey','question_type','en','Question type'),('survey','questionblock','en','Question block'),('survey','questionblock_has_constraints','en','Preconditions'),('survey','questionblock_icon','en','Questionblock icon'),('survey','questionblocks','en','Question blocks'),('survey','questions','en','Questions'),('survey','questions_inserted','en','Question(s) inserted!'),('survey','questions_removed','en','Question(s) and/or question block(s) removed!'),('survey','questiontype','en','Question type'),('survey','ratio_absolute','en','Ratio-Absolute'),('survey','ratio_non_absolute','en','Ratio-Non-Absolute'),('survey','remove_question','en','Remove'),('survey','remove_questions','en','Are you sure you want to remove the following question(s) and/or question block(s) from the survey?'),('survey','reset_filter','en','Reset filter'),('survey','resume_survey','en','Resume the survey'),('survey','save_back_to_survey','en','Save and go back to the survey'),('survey','save_obligatory_state','en','Save obligatory states'),('survey','save_phrase','en','Save as phrase'),('survey','save_phrase_categories_not_checked','en','Please check at least two answers to save answers in a new phrase!'),('survey','save_phrase_introduction','en','If you want to save the answers below as default phrase, please enter a phrase title. You can access a default phrase whenever you want to add a phrase to an ordinal question.'),('survey','search_field_all','en','Search in all fields'),('survey','search_for','en','Search for'),('survey','search_groups','en','Found groups'),('survey','search_invitation','en','Search for users or groups to invite'),('survey','search_questions','en','Search questions'),('survey','search_roles','en','Found roles'),('survey','search_term','en','Search term'),('survey','search_type_all','en','Search in all question types'),('survey','search_users','en','Found users'),('survey','select_option','en','--- Please select an option ---'),('survey','select_phrase_to_add','en','Please select a phrase to add it to the question!'),('survey','select_prior_question','en','Select a prior question'),('survey','select_questionpool','en','Please select a question pool to store the created question'),('survey','select_questionpool_short','en','Question pool for Survey'),('survey','select_relation','en','Select a relation'),('survey','select_svy_option','en','--- Please select a survey ---'),('survey','select_target_position_for_move','en','Please select a target position to move the answers and press one of the insert buttons!'),('survey','select_target_position_for_move_question','en','Please select a target position to move the question(s) and press one of the insert buttons!'),('survey','select_value','en','Select a value'),('survey','selection','en','Selection'),('survey','set_filter','en','Set filter'),('survey','skipped','en','skipped'),('survey','spl_general_properties','en','General properties'),('survey','spl_online_property','en','Online'),('survey','spl_online_property_description','en','If the questionpool is not online, it cannot be used in surveys.'),('survey','spl_select_file_for_import','en','You must select a file for import!'),('survey','start_date','en','Start date'),('survey','start_date_not_reached','en','You cannot start the survey until the start date is reached!'),('survey','start_survey','en','Start survey'),('survey','subject_mail_survey_id','en','Your survey code for \"%s\"'),('survey','subtype','en','Subtype'),('survey','survey_code','en','Survey access code'),('survey','survey_code_message_sent','en','A survey code which allows you to resume the survey was sent to your INBOX!'),('survey','survey_code_no_codes','en','You have not created any survey access codes yet.'),('survey','survey_code_url','en','URL for direct access'),('survey','survey_code_url_name','en','URL (use right mouse button to copy URL)'),('survey','survey_code_used','en','Code was used'),('survey','survey_finish','en','finish survey'),('survey','survey_finished','en','You have finished the survey. Thank you for your participation!'),('survey','survey_has_datasets_warning','en','The survey already contains datasets. You cannot edit the survey questions until you remove these datasets in the maintenance section.'),('survey','survey_is_offline','en','You cannot start the survey! The survey is offline.'),('survey','survey_next','en','next >>>'),('survey','survey_offline_message','en','Can\'t invite users. The survey is offline!'),('survey','survey_online_warning','en','The survey is online. You cannot edit the survey questions!'),('survey','survey_previous','en','<<< previous'),('survey','survey_question_obligatory','en','(This question is obligatory. You must answer the question)'),('survey','survey_question_optional','en','(Optional)'),('survey','survey_questions','en','Questions'),('survey','survey_skip_finish','en','skip and finish survey'),('survey','survey_skip_next','en','skip >>>'),('survey','survey_skip_previous','en','<<< skip'),('survey','survey_skip_start','en','skip and go to start page'),('survey','survey_start','en','go to start page'),('survey','svy_all_user_data_deleted','en','All user data of this survey has been deleted!'),('survey','svy_check_evaluation_access_introduction','en','Since this survey evaluation is only accessable by survey participants you need to enter your survey access code to open the evaluation.'),('survey','svy_check_evaluation_authentication_needed','en','Authentication needed'),('survey','svy_check_evaluation_wrong_key','en','You entered a wrong survey access code or you have not participated the survey. Your access to the survey evaluation is denied.'),('survey','svy_create_export_file','en','Create export file'),('survey','svy_delete_all_user_data','en','Delete all user data'),('survey','svy_eval_cumulated','en','Cumulated results'),('survey','svy_eval_detail','en','Cumulated results (details)'),('survey','svy_eval_user','en','User specific results'),('survey','svy_export_files','en','Export files'),('survey','svy_file','en','File'),('survey','svy_missing_author','en','You have not entered the author\'s name in the survey properties! Please add an authors name.'),('survey','svy_missing_questions','en','You do not have any questions in the survey! Please add at least one question to the survey.'),('survey','svy_missing_title','en','You have not entered a survey title! Please go to the metadata section and enter a title.'),('survey','svy_page_error','en','There was an error answering a survey question. Please refer to the question to get more information on the error!'),('survey','svy_page_errors','en','There were errors answering the survey questions. Please refer to the questions to get more information on the errors!'),('survey','svy_select_file_for_import','en','You must select a file for import!'),('survey','svy_select_questionpools','en','Please select a question pool to store the imported questions'),('survey','svy_select_surveys','en','Please select a survey for duplication'),('survey','svy_show_questiontitles','en','Show question titles in survey'),('survey','svy_size','en','Size'),('survey','svy_statistical_evaluation','en','Statistical evaluation'),('survey','svy_status_missing','en','There are required elements missing in this survey!'),('survey','svy_status_missing_elements','en','The following elements are missing:'),('survey','svy_status_ok','en','The status of the survey is OK. There are no missing elements.'),('survey','text_maximum_chars_allowed','en','Please do not enter more than a maximum of %s characters. Any characters above will be cut.'),('survey','text_question_not_filled_out','en','Please fill out the answer field!'),('survey','text_resume_survey','en','You are trying to resume an anonymized survey. To continue the survey with your previously entered values please enter the 32 digit survey code which you received after starting the survey (it was sent with a message to your ILIAS mail folder). Instead of typing your survey code you also can copy it from your inbox and paste it into the text field below.'),('survey','title_resume_survey','en','Resume the survey - Enter survey code'),('survey','unfold','en','Unfold'),('survey','unlimited_users','en','Unlimited'),('survey','uploaded_material','en','Uploaded Material'),('survey','upper_limit','en','Upper limit'),('survey','upper_limit_must_be_greater','en','The upper limit must be greater than the lower limit!'),('survey','used','en','used'),('survey','users_answered','en','Users answered'),('survey','users_skipped','en','Users skipped'),('survey','value_nr_entered','en','Number of users that entered that value'),('survey','values','en','Values'),('survey','vertical','en','vertical'),('survey','view_constraints_introduction','en','You are viewing the preconditions of the following question/question block:'),('survey','view_phrase','en','View phrase'),('survey','warning_question_in_use','en','Warning! The question you want to edit is in use by the surveys listed below. If you decide to continue and save/apply the question, all answers of the surveys listed below will be deleted. If you want to change the question and use it in another survey, please choose duplicate in the question browser to create a new instance of this question.'),('survey','warning_question_not_complete','en','The question is not complete!'),('trac','info_valid_request','en','The maximum valid time between two requests of an user.'),('trac','last_login','en','Last login'),('trac','meta_typical_learning_time','en','Typical learning time'),('trac','obj_types','en','Object types:'),('trac','search_area_info','en','Please choose one object.'),('trac','select_one','en','Please select one object'),('trac','trac_activated','en','Tracking activated'),('trac','trac_added_no_shown_list','en','The selected objects will be hidden.'),('trac','trac_anonymized','en','Anonymized'),('trac','trac_anonymized_info','en','If enabled, all statistics will be anonymized.'),('trac','trac_assigned','en','Assigned'),('trac','trac_assignments','en','Assignments'),('trac','trac_below','en','Below:'),('trac','trac_collection_assign','en','Assign'),('trac','trac_collection_deassign','en','Deassign'),('trac','trac_comment','en','Comment'),('trac','trac_comments','en','Comments'),('trac','trac_completed','en','Completed'),('trac','trac_crs_assignments','en','Assignment of course items'),('trac','trac_crs_items','en','Assignable course items'),('trac','trac_crs_members','en','Course members:'),('trac','trac_crs_objects','en','Learning progress of course members'),('trac','trac_crs_releavant_items','en','Course items'),('trac','trac_edit_collection','en','Please assign course items. If the status of all assigned items is \'Completed\' the whole course is completed by the user.'),('trac','trac_edit_progress','en','Edit learning progress'),('trac','trac_edit_time','en','Processing time'),('trac','trac_edit_visits','en','Please define the number of visits that is neccessary to complete this object.'),('trac','trac_edited_scos','en','Processed content'),('trac','trac_filter_area','en','Area:'),('trac','trac_filter_hidden','en','Hidden:'),('trac','trac_filter_limit_reached','en','The number of hits is limited to %s. Please use the filter to reduce the hits.'),('trac','trac_filter_no_access','en','No objects found'),('trac','trac_filter_none','en','None'),('trac','trac_filter_repository','en','In the repository'),('trac','trac_hide','en','Hide'),('trac','trac_in_progress','en','In progress'),('trac','trac_info_edited','en','Set the status to \'Completed\' if you think you have processed all content.'),('trac','trac_last_access','en','Last access'),('trac','trac_learning_progress','en','Learning progress'),('trac','trac_learning_progress_tbl_header','en','Learning progress of:'),('trac','trac_lp_filter','en','Filter'),('trac','trac_mark','en','Mark'),('trac','trac_marks','en','Marks'),('trac','trac_mode','en','Mode'),('trac','trac_mode_collection','en','Collection of objects'),('trac','trac_mode_deactivated','en','Deactivated'),('trac','trac_mode_manual','en','Manual'),('trac','trac_mode_objectives','en','Learning objectives'),('trac','trac_mode_scorm','en','SCORM/AICC'),('trac','trac_mode_scorm_aicc','en','SCORM/AICC'),('trac','trac_mode_test_finished','en','Test finished'),('trac','trac_mode_test_passed','en','Test passed'),('trac','trac_mode_tlt','en','Typical learning time'),('trac','trac_mode_visits','en','Number of visits'),('trac','trac_modifications_saved','en','Saved settings'),('trac','trac_modus','en','Mode'),('trac','trac_no_attempted','en','Not attempted'),('trac','trac_no_content','en','No informations about the learning progress available.'),('trac','trac_no_sahs_items_found','en','No objects found.'),('trac','trac_not_accessed','en','Not accessed'),('trac','trac_not_assigned','en','Not assigned'),('trac','trac_not_attempted','en','Not attempted'),('trac','trac_not_completed','en','Not Completed'),('trac','trac_num_visits','en','Number of visits'),('trac','trac_objects','en','Learning progress of users'),('trac','trac_occurrences','en','Path:'),('trac','trac_processing_time','en','Processing time'),('trac','trac_progress','en','Personal learning progress'),('trac','trac_query','en','Title/description:'),('trac','trac_reached_points','en','Achieved score'),('trac','trac_reached_visits','en','Required visits'),('trac','trac_refresh','en','Refresh'),('trac','trac_required_visits','en','Number of required visits'),('trac','trac_sahs_relevant_items','en','SCO\'s'),('trac','trac_select_one','en','You made no selection'),('trac','trac_settings','en','Settings'),('trac','trac_settings_saved','en','Saved settings.'),('trac','trac_show','en','Show learning progress'),('trac','trac_show_hidden','en','Show'),('trac','trac_spent_time','en','Spent time'),('trac','trac_status','en','Status'),('trac','trac_total_online','en','Total time online'),('trac','trac_update_edit_user','en','Saved settings'),('trac','trac_updated_status','en','Saved learning progress status.'),('trac','trac_user_data','en','User data'),('trac','trac_usr_list','en','Users'),('trac','trac_valid_request','en','Max. time between requests'),('trac','trac_view_crs','en','Back to course'),('trac','trac_view_list','en','Back to list'),('trac','trac_visits','en','Number of visits'),('trac','trac_visits_info','en','Every page visit counts as one visit.'),('trac','tracking_time_span_not_valid','en','Please enter a valid number of seconds.');
UNLOCK TABLES;
/*!40000 ALTER TABLE `lng_data` ENABLE KEYS */;

--
-- Table structure for table `lo_access`
--

DROP TABLE IF EXISTS `lo_access`;
CREATE TABLE `lo_access` (
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `usr_id` int(11) NOT NULL default '0',
  `lm_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `lm_title` varchar(200) NOT NULL default ''
) TYPE=MyISAM;

--
-- Dumping data for table `lo_access`
--


/*!40000 ALTER TABLE `lo_access` DISABLE KEYS */;
LOCK TABLES `lo_access` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `lo_access` ENABLE KEYS */;

--
-- Table structure for table `mail`
--

DROP TABLE IF EXISTS `mail`;
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
  PRIMARY KEY  (`mail_id`),
  KEY `jmp_uid` (`user_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `mail`
--


/*!40000 ALTER TABLE `mail` DISABLE KEYS */;
LOCK TABLES `mail` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `mail` ENABLE KEYS */;

--
-- Table structure for table `mail_attachment`
--

DROP TABLE IF EXISTS `mail_attachment`;
CREATE TABLE `mail_attachment` (
  `mail_id` int(11) NOT NULL default '0',
  `path` text NOT NULL,
  PRIMARY KEY  (`mail_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `mail_attachment`
--


/*!40000 ALTER TABLE `mail_attachment` DISABLE KEYS */;
LOCK TABLES `mail_attachment` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `mail_attachment` ENABLE KEYS */;

--
-- Table structure for table `mail_obj_data`
--

DROP TABLE IF EXISTS `mail_obj_data`;
CREATE TABLE `mail_obj_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` char(70) NOT NULL default '',
  `type` char(16) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`user_id`),
  KEY `jmp_uid` (`user_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `mail_obj_data`
--


/*!40000 ALTER TABLE `mail_obj_data` DISABLE KEYS */;
LOCK TABLES `mail_obj_data` WRITE;
INSERT INTO `mail_obj_data` VALUES (2,6,'a_root','root'),(3,6,'b_inbox','inbox'),(4,6,'c_trash','trash'),(5,6,'d_drafts','drafts'),(6,6,'e_sent','sent'),(7,6,'z_local','local');
UNLOCK TABLES;
/*!40000 ALTER TABLE `mail_obj_data` ENABLE KEYS */;

--
-- Table structure for table `mail_options`
--

DROP TABLE IF EXISTS `mail_options`;
CREATE TABLE `mail_options` (
  `user_id` int(11) NOT NULL default '0',
  `linebreak` tinyint(4) NOT NULL default '0',
  `signature` text NOT NULL,
  `incoming_type` tinyint(3) default NULL,
  KEY `user_id` (`user_id`,`linebreak`)
) TYPE=MyISAM;

--
-- Dumping data for table `mail_options`
--


/*!40000 ALTER TABLE `mail_options` DISABLE KEYS */;
LOCK TABLES `mail_options` WRITE;
INSERT INTO `mail_options` VALUES (6,60,'',NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `mail_options` ENABLE KEYS */;

--
-- Table structure for table `mail_saved`
--

DROP TABLE IF EXISTS `mail_saved`;
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

--
-- Dumping data for table `mail_saved`
--


/*!40000 ALTER TABLE `mail_saved` DISABLE KEYS */;
LOCK TABLES `mail_saved` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `mail_saved` ENABLE KEYS */;

--
-- Table structure for table `mail_tree`
--

DROP TABLE IF EXISTS `mail_tree`;
CREATE TABLE `mail_tree` (
  `tree` int(11) NOT NULL default '0',
  `child` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned default NULL,
  `lft` int(11) unsigned NOT NULL default '0',
  `rgt` int(11) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  KEY `child` (`child`),
  KEY `parent` (`parent`),
  KEY `jmp_tree` (`tree`)
) TYPE=MyISAM;

--
-- Dumping data for table `mail_tree`
--


/*!40000 ALTER TABLE `mail_tree` DISABLE KEYS */;
LOCK TABLES `mail_tree` WRITE;
INSERT INTO `mail_tree` VALUES (6,2,0,1,12,1),(6,3,2,2,3,2),(6,4,2,4,5,2),(6,5,2,6,7,2),(6,6,2,8,9,2),(6,7,2,10,11,2);
UNLOCK TABLES;
/*!40000 ALTER TABLE `mail_tree` ENABLE KEYS */;

--
-- Table structure for table `map_area`
--

DROP TABLE IF EXISTS `map_area`;
CREATE TABLE `map_area` (
  `item_id` int(11) NOT NULL default '0',
  `nr` int(11) NOT NULL default '0',
  `shape` varchar(20) default NULL,
  `coords` varchar(200) default NULL,
  `link_type` varchar(3) default NULL,
  `title` varchar(200) default NULL,
  `href` varchar(200) default NULL,
  `target` varchar(50) default NULL,
  `type` varchar(20) default NULL,
  `target_frame` varchar(50) default NULL,
  PRIMARY KEY  (`item_id`,`nr`)
) TYPE=MyISAM;

--
-- Dumping data for table `map_area`
--


/*!40000 ALTER TABLE `map_area` DISABLE KEYS */;
LOCK TABLES `map_area` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `map_area` ENABLE KEYS */;

--
-- Table structure for table `media_item`
--

DROP TABLE IF EXISTS `media_item`;
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
  `tried_thumb` enum('y','n') default 'n',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `media_item`
--


/*!40000 ALTER TABLE `media_item` DISABLE KEYS */;
LOCK TABLES `media_item` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `media_item` ENABLE KEYS */;

--
-- Table structure for table `mep_tree`
--

DROP TABLE IF EXISTS `mep_tree`;
CREATE TABLE `mep_tree` (
  `mep_id` int(11) NOT NULL default '0',
  `child` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `depth` smallint(6) NOT NULL default '0'
) TYPE=MyISAM;

--
-- Dumping data for table `mep_tree`
--


/*!40000 ALTER TABLE `mep_tree` DISABLE KEYS */;
LOCK TABLES `mep_tree` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `mep_tree` ENABLE KEYS */;

--
-- Table structure for table `meta_data`
--

DROP TABLE IF EXISTS `meta_data`;
CREATE TABLE `meta_data` (
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` varchar(3) NOT NULL default '',
  `title` varchar(200) NOT NULL default '',
  `language` varchar(200) NOT NULL default '',
  `description` blob NOT NULL,
  PRIMARY KEY  (`obj_id`,`obj_type`)
) TYPE=MyISAM;

--
-- Dumping data for table `meta_data`
--


/*!40000 ALTER TABLE `meta_data` DISABLE KEYS */;
LOCK TABLES `meta_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `meta_data` ENABLE KEYS */;

--
-- Table structure for table `meta_keyword`
--

DROP TABLE IF EXISTS `meta_keyword`;
CREATE TABLE `meta_keyword` (
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` varchar(3) NOT NULL default '',
  `language` varchar(2) NOT NULL default '',
  `keyword` varchar(200) NOT NULL default ''
) TYPE=MyISAM;

--
-- Dumping data for table `meta_keyword`
--


/*!40000 ALTER TABLE `meta_keyword` DISABLE KEYS */;
LOCK TABLES `meta_keyword` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `meta_keyword` ENABLE KEYS */;

--
-- Table structure for table `meta_techn_format`
--

DROP TABLE IF EXISTS `meta_techn_format`;
CREATE TABLE `meta_techn_format` (
  `tech_id` int(11) NOT NULL default '0',
  `format` varchar(150) default NULL,
  `nr` int(11) default NULL,
  KEY `tech_id` (`tech_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `meta_techn_format`
--


/*!40000 ALTER TABLE `meta_techn_format` DISABLE KEYS */;
LOCK TABLES `meta_techn_format` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `meta_techn_format` ENABLE KEYS */;

--
-- Table structure for table `meta_techn_loc`
--

DROP TABLE IF EXISTS `meta_techn_loc`;
CREATE TABLE `meta_techn_loc` (
  `tech_id` int(11) NOT NULL default '0',
  `location` varchar(150) default NULL,
  `nr` int(11) default NULL,
  `type` enum('LocalFile','Reference') NOT NULL default 'LocalFile',
  KEY `tech_id` (`tech_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `meta_techn_loc`
--


/*!40000 ALTER TABLE `meta_techn_loc` DISABLE KEYS */;
LOCK TABLES `meta_techn_loc` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `meta_techn_loc` ENABLE KEYS */;

--
-- Table structure for table `meta_technical`
--

DROP TABLE IF EXISTS `meta_technical`;
CREATE TABLE `meta_technical` (
  `tech_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` varchar(3) NOT NULL default '',
  `size` varchar(50) NOT NULL default '',
  `install_remarks` text NOT NULL,
  `install_remarks_lang` varchar(2) NOT NULL default '',
  `other_requirements` text NOT NULL,
  `other_requirements_lang` varchar(2) NOT NULL default '',
  `duration` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`tech_id`),
  KEY `obj_id` (`obj_id`,`obj_type`)
) TYPE=MyISAM;

--
-- Dumping data for table `meta_technical`
--


/*!40000 ALTER TABLE `meta_technical` DISABLE KEYS */;
LOCK TABLES `meta_technical` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `meta_technical` ENABLE KEYS */;

--
-- Table structure for table `mob_parameter`
--

DROP TABLE IF EXISTS `mob_parameter`;
CREATE TABLE `mob_parameter` (
  `med_item_id` int(11) NOT NULL default '0',
  `name` varchar(50) default NULL,
  `value` text,
  KEY `mob_id` (`med_item_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `mob_parameter`
--


/*!40000 ALTER TABLE `mob_parameter` DISABLE KEYS */;
LOCK TABLES `mob_parameter` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `mob_parameter` ENABLE KEYS */;

--
-- Table structure for table `mob_usage`
--

DROP TABLE IF EXISTS `mob_usage`;
CREATE TABLE `mob_usage` (
  `id` int(11) NOT NULL default '0',
  `usage_type` varchar(10) NOT NULL default '',
  `usage_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`,`usage_type`,`usage_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `mob_usage`
--


/*!40000 ALTER TABLE `mob_usage` DISABLE KEYS */;
LOCK TABLES `mob_usage` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `mob_usage` ENABLE KEYS */;

--
-- Table structure for table `module`
--

DROP TABLE IF EXISTS `module`;
CREATE TABLE `module` (
  `name` varchar(100) NOT NULL default '',
  `dir` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`name`)
) TYPE=MyISAM COMMENT='ILIAS Modules';

--
-- Dumping data for table `module`
--


/*!40000 ALTER TABLE `module` DISABLE KEYS */;
LOCK TABLES `module` WRITE;
INSERT INTO `module` VALUES ('ILIASLearningModule','content'),('Glossary','content'),('Assessment','assessment'),('Survey','survey'),('Course','course');
UNLOCK TABLES;
/*!40000 ALTER TABLE `module` ENABLE KEYS */;

--
-- Table structure for table `module_class`
--

DROP TABLE IF EXISTS `module_class`;
CREATE TABLE `module_class` (
  `class` varchar(100) NOT NULL default '',
  `module` varchar(100) NOT NULL default '',
  `dir` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`class`)
) TYPE=MyISAM COMMENT='Class information of ILIAS Modules';

--
-- Dumping data for table `module_class`
--


/*!40000 ALTER TABLE `module_class` DISABLE KEYS */;
LOCK TABLES `module_class` WRITE;
INSERT INTO `module_class` VALUES ('ilLMEditorGUI','ILIASLearningModule','classes'),('ilLMPresentationGUI','ILIASLearningModule','classes'),('ilGlossaryEditorGUI','Glossary','classes'),('ilObjQuestionPoolGUI','Assessment','classes'),('ilObjTestGUI','Assessment','classes'),('ilObjSurveyQuestionPoolGUI','Survey','classes'),('ilObjSurveyGUI','Survey','classes');
UNLOCK TABLES;
/*!40000 ALTER TABLE `module_class` ENABLE KEYS */;

--
-- Table structure for table `note`
--

DROP TABLE IF EXISTS `note`;
CREATE TABLE `note` (
  `id` int(11) NOT NULL auto_increment,
  `rep_obj_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` varchar(10) default NULL,
  `type` int(11) NOT NULL default '0',
  `author` int(11) NOT NULL default '0',
  `text` mediumtext,
  `label` int(11) NOT NULL default '0',
  `creation_date` datetime default NULL,
  `update_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `subject` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `i_author` (`author`),
  KEY `i_obj` (`rep_obj_id`,`obj_id`,`obj_type`)
) TYPE=MyISAM;

--
-- Dumping data for table `note`
--


/*!40000 ALTER TABLE `note` DISABLE KEYS */;
LOCK TABLES `note` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `note` ENABLE KEYS */;

--
-- Table structure for table `note_data`
--

DROP TABLE IF EXISTS `note_data`;
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

--
-- Dumping data for table `note_data`
--


/*!40000 ALTER TABLE `note_data` DISABLE KEYS */;
LOCK TABLES `note_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `note_data` ENABLE KEYS */;

--
-- Table structure for table `object_data`
--

DROP TABLE IF EXISTS `object_data`;
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
  KEY `type` (`type`),
  FULLTEXT KEY `title_desc` (`title`,`description`)
) TYPE=MyISAM;

--
-- Dumping data for table `object_data`
--


/*!40000 ALTER TABLE `object_data` DISABLE KEYS */;
LOCK TABLES `object_data` WRITE;
INSERT INTO `object_data` VALUES (2,'role','Administrator','Role for systemadministrators. This role grants access to everything!',-1,'2002-01-16 15:31:45','2003-08-15 13:18:57',''),(3,'rolt','Author','Role template for authors with write & create permissions.',-1,'2002-01-16 15:32:50','2005-07-20 16:13:42',''),(4,'role','User','Standard role for registered users. Grants read access to most objects.',-1,'2002-01-16 15:34:00','2005-07-20 16:01:03',''),(5,'role','Guest','Role grants only a few visible & read permissions.',-1,'2002-01-16 15:34:46','2005-07-20 16:01:35',''),(6,'usr','root user','ilias@yourserver.com',-1,'2002-01-16 16:09:22','2003-09-30 19:50:01',''),(7,'usrf','User accounts','Manage user accounts here.',-1,'2002-06-27 09:24:06','2004-01-20 12:23:47',''),(8,'rolf','Roles','Manage your roles here.',-1,'2002-06-27 09:24:06','2004-01-20 12:23:40',''),(1,'root','ILIAS','This is the root node of the system!!!',-1,'2002-06-24 15:15:03','2004-01-20 12:24:12',''),(9,'adm','System Settings','Folder contains the systems settings',-1,'2002-07-15 12:37:33','2002-07-15 12:37:33',''),(10,'objf','Objectdefinitions','Manage ILIAS object types and object permissions. (only for experts!)',-1,'2002-07-15 12:36:56','2004-01-20 12:23:53',''),(11,'lngf','Languages','Manage your system languages here.',-1,'2002-07-15 15:52:51','2004-01-20 12:24:06',''),(25,'typ','usr','User object',-1,'2002-07-15 15:53:37','2003-08-15 12:30:56',''),(34,'typ','lm','Learning module Object',-1,'2002-07-15 15:54:04','2003-08-15 12:33:04',''),(37,'typ','frm','Forum object',-1,'2002-07-15 15:54:22','2003-08-15 12:36:40',''),(15,'typ','grp','Group object',-1,'2002-07-15 15:54:37','2002-07-15 15:54:37',''),(16,'typ','cat','Category object',-1,'2002-07-15 15:54:54','2002-07-15 15:54:54',''),(17,'typ','crs','Course object',-1,'2002-07-15 15:55:08','2002-07-15 15:55:08',''),(19,'typ','mail','Mailmodule object',-1,'2002-07-15 15:55:49','2002-07-15 15:55:49',''),(21,'typ','adm','Administration Panel object',-1,'2002-07-15 15:56:38','2002-07-15 15:56:38',''),(22,'typ','usrf','User Folder object',-1,'2002-07-15 15:56:52','2002-07-15 15:56:52',''),(23,'typ','rolf','Role Folder object',-1,'2002-07-15 15:57:06','2002-07-15 15:57:06',''),(24,'typ','objf','Object-Type Folder object',-1,'2002-07-15 15:57:17','2002-07-15 15:57:17',''),(26,'typ','typ','Object Type Definition object',-1,'2002-07-15 15:58:16','2002-07-15 15:58:16',''),(27,'typ','rolt','Role template object',-1,'2002-07-15 15:58:16','2002-07-15 15:58:16',''),(28,'typ','lngf','Language Folder object',-1,'2002-08-28 14:22:01','2002-08-28 14:22:01',''),(29,'typ','lng','Language object',-1,'2002-08-30 10:18:29','2002-08-30 10:18:29',''),(30,'typ','role','Role Object',-1,'2002-08-30 10:21:37','2002-08-30 10:21:37',''),(31,'typ','dbk','Digilib Book',-1,'2003-08-15 10:07:29','2003-08-15 12:30:19',''),(33,'typ','root','Root Folder Object',-1,'2002-12-21 00:04:00','2003-08-15 12:04:20',''),(70,'lng','en','installed',-1,'0000-00-00 00:00:00','2006-03-01 17:43:04',''),(14,'role','Anonymous','Default role for anonymous users (with no account)',-1,'2003-08-15 12:06:19','2005-07-20 15:15:06',''),(18,'typ','mob','Multimedia object',-1,'0000-00-00 00:00:00','2003-08-15 12:03:20',''),(35,'typ','notf','Note Folder Object',-1,'2002-12-21 00:04:00','2002-12-21 00:04:00',''),(36,'typ','note','Note Object',-1,'2002-12-21 00:04:00','2002-12-21 00:04:00',''),(12,'mail','Mail Settings','Configure global mail settings here.',-1,'2003-08-15 10:07:28','2004-01-20 12:24:00',''),(20,'typ','sahs','SCORM/AICC Learning Module',-1,'2003-08-15 10:07:28','2003-08-15 12:23:10',''),(80,'rolt','il_grp_admin','Administrator role template of groups',-1,'2003-08-15 10:07:28','2005-07-20 17:04:15',''),(81,'rolt','il_grp_member','Member role template of groups',-1,'2003-08-15 10:07:28','2005-07-20 17:07:55',''),(82,'rolt','il_grp_status_closed','Group role template',-1,'2003-08-15 10:07:29','2003-08-15 13:21:38',''),(83,'rolt','il_grp_status_open','Group role template',-1,'2003-08-15 10:07:29','2003-08-15 13:21:25',''),(32,'typ','glo','Glossary',-1,'2003-08-15 10:07:30','2003-08-15 12:29:54',''),(13,'usr','Anonymous','Anonymous user account. DO NOT DELETE!',-1,'2003-08-15 10:07:30','2003-08-15 10:07:30',''),(71,'lng','de','not_installed',6,'2003-08-15 10:25:19','2003-09-30 19:50:06',''),(72,'lng','es','not_installed',6,'2003-08-15 10:25:19','2003-08-15 10:25:19',''),(73,'lng','it','not_installed',6,'2003-08-15 10:25:19','2003-08-15 10:25:19',''),(84,'typ','exc','Exercise object',-1,'2003-11-30 21:22:49','2003-11-30 21:22:49',''),(85,'typ','auth','Authentication settings',-1,'2003-11-30 21:22:49','2003-11-30 21:22:49',''),(86,'auth','Authentication settings','Select and configure authentication mode for all user accounts',-1,'2003-11-30 21:22:49','2003-11-30 21:22:49',''),(87,'typ','fold','Folder object',-1,'2003-11-30 21:22:50','2003-11-30 21:22:50',''),(88,'typ','file','File object',-1,'2003-11-30 21:22:50','2003-11-30 21:22:50',''),(89,'lng','fr','not_installed',6,'2004-01-20 12:22:17','2004-01-20 12:22:17',''),(90,'lng','nl','not_installed',6,'2004-01-20 12:22:17','2004-01-20 12:22:17',''),(91,'lng','pl','not_installed',6,'2004-01-20 12:22:17','2004-01-20 12:22:17',''),(92,'lng','ua','not_installed',6,'2004-01-20 12:22:17','2004-01-20 12:22:17',''),(93,'lng','zh','not_installed',6,'2004-01-20 12:22:17','2004-01-20 12:22:17',''),(94,'typ','tst','Test object',-1,'2004-02-18 21:17:40','2004-02-18 21:17:40',''),(95,'typ','qpl','Question pool object',-1,'2004-02-18 21:17:40','2004-02-18 21:17:40',''),(96,'typ','chat','Chat object',-1,'2004-02-18 21:17:40','2004-02-18 21:17:40',''),(97,'typ','chac','Chat server config object',-1,'2004-02-18 21:17:40','2004-02-18 21:17:40',''),(98,'chac','Chat server settings','Configure chat server settings here',-1,'2004-02-18 21:17:40','2004-02-18 21:17:40',''),(99,'typ','recf','RecoveryFolder object',-1,'2004-03-09 18:13:16','2004-03-09 18:13:16',''),(100,'recf','__Restored Objects','Contains objects restored by recovery tool',-1,'2004-03-09 18:13:16','2004-03-09 18:13:16',''),(101,'typ','mep','Media pool object',-1,'2004-04-19 00:09:14','2004-04-19 00:09:14',''),(102,'typ','htlm','HTML LM object',-1,'2004-04-19 00:09:15','2004-04-19 00:09:15',''),(103,'typ','svy','Survey object',-1,'2004-05-15 01:18:59','2004-05-15 01:18:59',''),(104,'typ','spl','Question pool object (Survey)',-1,'2004-05-15 01:18:59','2004-05-15 01:18:59',''),(105,'typ','tax','Taxonomy object',-1,'2004-06-21 01:27:18','2004-06-21 01:27:18',''),(106,'typ','taxf','Taxonomy folder object',-1,'2004-06-21 01:27:18','2004-06-21 01:27:18',''),(107,'taxf','Taxonomy folder','Configure taxonomy settings here',-1,'2004-06-21 01:27:18','2004-06-21 01:27:18',''),(108,'typ','trac','UserTracking object',-1,'2004-07-11 01:03:12','2004-07-11 01:03:12',''),(109,'trac','__User Tracking','System user tracking',-1,'2004-07-11 01:03:12','2004-07-11 01:03:12',''),(110,'rolt','il_crs_admin','Administrator template for course admins',-1,'2004-09-02 09:49:43','2005-07-20 16:47:58',''),(111,'rolt','il_crs_tutor','Tutor template for course tutors',-1,'2004-09-02 09:49:43','2005-07-20 16:55:04',''),(112,'rolt','il_crs_member','Member template for course members',-1,'2004-09-02 09:49:43','2005-07-20 16:59:41',''),(113,'typ','pays','Payment settings',-1,'2004-09-02 09:49:45','2004-09-02 09:49:45',''),(114,'pays','Payment settings','Payment settings',-1,'2004-09-02 09:49:45','2004-09-02 09:49:45',''),(115,'typ','assf','AssessmentFolder object',-1,'2005-01-07 17:21:15','2005-01-07 17:21:15',''),(116,'assf','__Test&Assessment','Test&Assessment Administration',-1,'2005-01-07 17:21:15','2005-01-07 17:21:15',''),(117,'typ','stys','Style Settings',-1,'2005-03-02 08:59:01','2005-03-02 08:59:01',''),(118,'stys','System Style Settings','Manage system skin and style settings here',-1,'2005-03-02 08:59:01','2005-03-02 08:59:01',''),(119,'typ','icrs','iLinc course object',-1,'2005-03-02 08:59:01','2005-03-02 08:59:01',''),(120,'typ','icla','iLinc class room object',-1,'2005-03-02 08:59:01','2005-03-02 08:59:01',''),(121,'typ','crsg','Course grouping object',-1,'2005-03-02 08:59:02','2005-03-02 08:59:02',''),(122,'typ','webr','Link resource object',-1,'2005-03-13 22:41:38','2005-03-13 22:41:38',''),(123,'typ','seas','Search settings',-1,'2005-06-20 09:50:00','2005-06-20 09:50:00',''),(124,'seas','Search settings','Search settings',-1,'2005-06-20 09:50:00','2005-06-20 09:50:00',''),(125,'rolt','Local Administrator','Role template for local administrators.',6,'2005-07-20 15:33:13','2005-07-20 16:00:19',''),(126,'rolt','Co-Author','Role template for authors with limited permissions.',6,'2005-07-20 16:36:46','2005-07-20 16:42:50',''),(127,'typ','extt','external tools settings',-1,'2005-07-20 18:10:04','2005-07-20 18:10:04',''),(128,'extt','External tools settings','Configuring external tools',-1,'2005-07-20 18:10:04','2005-07-20 18:10:04',''),(129,'rolt','il_icrs_admin','Administrator template for LearnLink Seminars',-1,'2005-07-20 18:10:05','2005-07-20 18:10:05',''),(130,'rolt','il_icrs_member','Member template for LearnLink Seminars',-1,'2005-07-20 18:10:05','2005-07-20 18:10:05',''),(131,'rolt','il_crs_non_member','Non-member template for course object',-1,'2005-11-07 12:41:21','2005-11-07 12:41:21',''),(132,'chat','Public chat','Public chat',6,'2005-11-07 12:41:22','2005-11-07 12:41:22',''),(133,'rolf','132','(ref_id 24)',6,'2005-11-07 12:41:22','2005-11-07 12:41:22',''),(134,'role','il_chat_moderator_24','Moderator of chat obj_no.132',6,'2005-11-07 12:41:22','2005-11-07 12:41:22','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `object_data` ENABLE KEYS */;

--
-- Table structure for table `object_description`
--

DROP TABLE IF EXISTS `object_description`;
CREATE TABLE `object_description` (
  `obj_id` int(11) NOT NULL default '0',
  `description` text NOT NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `object_description`
--


/*!40000 ALTER TABLE `object_description` DISABLE KEYS */;
LOCK TABLES `object_description` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `object_description` ENABLE KEYS */;

--
-- Table structure for table `object_reference`
--

DROP TABLE IF EXISTS `object_reference`;
CREATE TABLE `object_reference` (
  `ref_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  `deleted` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ref_id`),
  KEY `obj_id` (`obj_id`),
  KEY `obj_del` (`deleted`)
) TYPE=MyISAM;

--
-- Dumping data for table `object_reference`
--


/*!40000 ALTER TABLE `object_reference` DISABLE KEYS */;
LOCK TABLES `object_reference` WRITE;
INSERT INTO `object_reference` VALUES (1,1,'0000-00-00 00:00:00'),(7,7,'0000-00-00 00:00:00'),(8,8,'0000-00-00 00:00:00'),(9,9,'0000-00-00 00:00:00'),(10,10,'0000-00-00 00:00:00'),(11,11,'0000-00-00 00:00:00'),(12,12,'0000-00-00 00:00:00'),(19,114,'0000-00-00 00:00:00'),(14,98,'0000-00-00 00:00:00'),(15,100,'0000-00-00 00:00:00'),(16,107,'0000-00-00 00:00:00'),(17,109,'0000-00-00 00:00:00'),(18,86,'0000-00-00 00:00:00'),(20,116,'0000-00-00 00:00:00'),(21,118,'0000-00-00 00:00:00'),(22,124,'0000-00-00 00:00:00'),(23,128,'0000-00-00 00:00:00'),(24,132,'0000-00-00 00:00:00'),(25,133,'0000-00-00 00:00:00');
UNLOCK TABLES;
/*!40000 ALTER TABLE `object_reference` ENABLE KEYS */;

--
-- Table structure for table `object_translation`
--

DROP TABLE IF EXISTS `object_translation`;
CREATE TABLE `object_translation` (
  `obj_id` int(11) NOT NULL default '0',
  `title` varchar(70) NOT NULL default '',
  `description` text,
  `lang_code` varchar(2) NOT NULL default '',
  `lang_default` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`,`lang_code`)
) TYPE=MyISAM;

--
-- Dumping data for table `object_translation`
--


/*!40000 ALTER TABLE `object_translation` DISABLE KEYS */;
LOCK TABLES `object_translation` WRITE;
INSERT INTO `object_translation` VALUES (9,'Open Source eLearning','','en',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `object_translation` ENABLE KEYS */;

--
-- Table structure for table `page_object`
--

DROP TABLE IF EXISTS `page_object`;
CREATE TABLE `page_object` (
  `page_id` int(11) NOT NULL default '0',
  `parent_id` int(11) default NULL,
  `content` mediumtext,
  `parent_type` varchar(4) NOT NULL default 'lm',
  PRIMARY KEY  (`page_id`,`parent_type`),
  FULLTEXT KEY `content` (`content`)
) TYPE=MyISAM;

--
-- Dumping data for table `page_object`
--


/*!40000 ALTER TABLE `page_object` DISABLE KEYS */;
LOCK TABLES `page_object` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `page_object` ENABLE KEYS */;

--
-- Table structure for table `payment_bill_vendor`
--

DROP TABLE IF EXISTS `payment_bill_vendor`;
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

--
-- Dumping data for table `payment_bill_vendor`
--


/*!40000 ALTER TABLE `payment_bill_vendor` DISABLE KEYS */;
LOCK TABLES `payment_bill_vendor` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_bill_vendor` ENABLE KEYS */;

--
-- Table structure for table `payment_currencies`
--

DROP TABLE IF EXISTS `payment_currencies`;
CREATE TABLE `payment_currencies` (
  `currency_id` int(3) NOT NULL default '0',
  `unit` char(32) NOT NULL default '',
  `subunit` char(32) NOT NULL default '',
  PRIMARY KEY  (`currency_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `payment_currencies`
--


/*!40000 ALTER TABLE `payment_currencies` DISABLE KEYS */;
LOCK TABLES `payment_currencies` WRITE;
INSERT INTO `payment_currencies` VALUES (1,'euro','cent');
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_currencies` ENABLE KEYS */;

--
-- Table structure for table `payment_objects`
--

DROP TABLE IF EXISTS `payment_objects`;
CREATE TABLE `payment_objects` (
  `pobject_id` int(11) NOT NULL auto_increment,
  `ref_id` int(11) NOT NULL default '0',
  `status` int(2) NOT NULL default '0',
  `pay_method` int(2) NOT NULL default '0',
  `vendor_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`pobject_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `payment_objects`
--


/*!40000 ALTER TABLE `payment_objects` DISABLE KEYS */;
LOCK TABLES `payment_objects` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_objects` ENABLE KEYS */;

--
-- Table structure for table `payment_prices`
--

DROP TABLE IF EXISTS `payment_prices`;
CREATE TABLE `payment_prices` (
  `price_id` int(11) NOT NULL auto_increment,
  `pobject_id` int(11) NOT NULL default '0',
  `duration` int(4) NOT NULL default '0',
  `currency` int(4) NOT NULL default '0',
  `unit_value` char(6) default '0',
  `sub_unit_value` char(3) default '00',
  PRIMARY KEY  (`price_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `payment_prices`
--


/*!40000 ALTER TABLE `payment_prices` DISABLE KEYS */;
LOCK TABLES `payment_prices` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_prices` ENABLE KEYS */;

--
-- Table structure for table `payment_settings`
--

DROP TABLE IF EXISTS `payment_settings`;
CREATE TABLE `payment_settings` (
  `settings_id` bigint(20) NOT NULL auto_increment,
  `currency_unit` varchar(255) NOT NULL default '',
  `currency_subunit` varchar(255) NOT NULL default '',
  `address` text NOT NULL,
  `bank_data` text NOT NULL,
  `add_info` text NOT NULL,
  `vat_rate` varchar(255) NOT NULL default '',
  `pdf_path` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`settings_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `payment_settings`
--


/*!40000 ALTER TABLE `payment_settings` DISABLE KEYS */;
LOCK TABLES `payment_settings` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_settings` ENABLE KEYS */;

--
-- Table structure for table `payment_shopping_cart`
--

DROP TABLE IF EXISTS `payment_shopping_cart`;
CREATE TABLE `payment_shopping_cart` (
  `psc_id` int(11) NOT NULL auto_increment,
  `customer_id` int(11) NOT NULL default '0',
  `pobject_id` int(11) NOT NULL default '0',
  `price_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`psc_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `payment_shopping_cart`
--


/*!40000 ALTER TABLE `payment_shopping_cart` DISABLE KEYS */;
LOCK TABLES `payment_shopping_cart` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_shopping_cart` ENABLE KEYS */;

--
-- Table structure for table `payment_statistic`
--

DROP TABLE IF EXISTS `payment_statistic`;
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
  `voucher` char(64) default NULL,
  `transaction_extern` char(64) default NULL,
  PRIMARY KEY  (`booking_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `payment_statistic`
--


/*!40000 ALTER TABLE `payment_statistic` DISABLE KEYS */;
LOCK TABLES `payment_statistic` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_statistic` ENABLE KEYS */;

--
-- Table structure for table `payment_trustees`
--

DROP TABLE IF EXISTS `payment_trustees`;
CREATE TABLE `payment_trustees` (
  `vendor_id` int(11) NOT NULL default '0',
  `trustee_id` int(11) NOT NULL default '0',
  `perm_stat` int(1) default NULL,
  `perm_obj` int(1) NOT NULL default '0',
  PRIMARY KEY  (`vendor_id`,`trustee_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `payment_trustees`
--


/*!40000 ALTER TABLE `payment_trustees` DISABLE KEYS */;
LOCK TABLES `payment_trustees` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_trustees` ENABLE KEYS */;

--
-- Table structure for table `payment_vendors`
--

DROP TABLE IF EXISTS `payment_vendors`;
CREATE TABLE `payment_vendors` (
  `vendor_id` int(11) NOT NULL default '0',
  `cost_center` varchar(16) NOT NULL default '',
  PRIMARY KEY  (`vendor_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `payment_vendors`
--


/*!40000 ALTER TABLE `payment_vendors` DISABLE KEYS */;
LOCK TABLES `payment_vendors` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `payment_vendors` ENABLE KEYS */;

--
-- Table structure for table `personal_clipboard`
--

DROP TABLE IF EXISTS `personal_clipboard`;
CREATE TABLE `personal_clipboard` (
  `user_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  `type` char(4) NOT NULL default '',
  `title` char(70) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`item_id`,`type`)
) TYPE=MyISAM;

--
-- Dumping data for table `personal_clipboard`
--


/*!40000 ALTER TABLE `personal_clipboard` DISABLE KEYS */;
LOCK TABLES `personal_clipboard` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `personal_clipboard` ENABLE KEYS */;

--
-- Table structure for table `qpl_answer_enhanced`
--

DROP TABLE IF EXISTS `qpl_answer_enhanced`;
CREATE TABLE `qpl_answer_enhanced` (
  `answer_enhanced_id` int(11) NOT NULL auto_increment,
  `answerblock_fi` int(11) NOT NULL default '0',
  `value1` int(11) NOT NULL default '0',
  `value2` int(11) NOT NULL default '0',
  `answer_boolean_prefix` enum('0','1') NOT NULL default '0',
  `enhanced_order` tinyint(4) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`answer_enhanced_id`),
  KEY `answerblock_fi` (`answerblock_fi`,`value1`)
) TYPE=MyISAM COMMENT='saves combinations of test question answers which are combin';

--
-- Dumping data for table `qpl_answer_enhanced`
--


/*!40000 ALTER TABLE `qpl_answer_enhanced` DISABLE KEYS */;
LOCK TABLES `qpl_answer_enhanced` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `qpl_answer_enhanced` ENABLE KEYS */;

--
-- Table structure for table `qpl_answerblock`
--

DROP TABLE IF EXISTS `qpl_answerblock`;
CREATE TABLE `qpl_answerblock` (
  `answerblock_id` int(11) NOT NULL auto_increment,
  `answerblock_index` tinyint(4) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `subquestion_index` tinyint(4) NOT NULL default '0',
  `points` double NOT NULL default '0',
  `feedback` varchar(30) default NULL,
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`answerblock_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM COMMENT='defines an answerblock, a combination of given answers of a ';

--
-- Dumping data for table `qpl_answerblock`
--


/*!40000 ALTER TABLE `qpl_answerblock` DISABLE KEYS */;
LOCK TABLES `qpl_answerblock` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `qpl_answerblock` ENABLE KEYS */;

--
-- Table structure for table `qpl_answers`
--

DROP TABLE IF EXISTS `qpl_answers`;
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
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`answer_id`),
  UNIQUE KEY `answer_id` (`answer_id`),
  KEY `answer_id_2` (`answer_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `qpl_answers`
--


/*!40000 ALTER TABLE `qpl_answers` DISABLE KEYS */;
LOCK TABLES `qpl_answers` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `qpl_answers` ENABLE KEYS */;

--
-- Table structure for table `qpl_question_type`
--

DROP TABLE IF EXISTS `qpl_question_type`;
CREATE TABLE `qpl_question_type` (
  `question_type_id` int(3) unsigned NOT NULL auto_increment,
  `type_tag` char(25) NOT NULL default '',
  PRIMARY KEY  (`question_type_id`),
  UNIQUE KEY `question_type_id` (`question_type_id`),
  KEY `question_type_id_2` (`question_type_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `qpl_question_type`
--


/*!40000 ALTER TABLE `qpl_question_type` DISABLE KEYS */;
LOCK TABLES `qpl_question_type` WRITE;
INSERT INTO `qpl_question_type` VALUES (1,'qt_multiple_choice_sr'),(2,'qt_multiple_choice_mr'),(3,'qt_cloze'),(4,'qt_matching'),(5,'qt_ordering'),(6,'qt_imagemap'),(7,'qt_javaapplet'),(8,'qt_text');
UNLOCK TABLES;
/*!40000 ALTER TABLE `qpl_question_type` ENABLE KEYS */;

--
-- Table structure for table `qpl_questionpool`
--

DROP TABLE IF EXISTS `qpl_questionpool`;
CREATE TABLE `qpl_questionpool` (
  `id_questionpool` int(11) NOT NULL auto_increment,
  `obj_fi` int(11) NOT NULL default '0',
  `online` enum('0','1') NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`id_questionpool`),
  KEY `obj_fi` (`obj_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `qpl_questionpool`
--


/*!40000 ALTER TABLE `qpl_questionpool` DISABLE KEYS */;
LOCK TABLES `qpl_questionpool` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `qpl_questionpool` ENABLE KEYS */;

--
-- Table structure for table `qpl_questions`
--

DROP TABLE IF EXISTS `qpl_questions`;
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
  `image_file` varchar(100) default NULL,
  `params` text,
  `maxNumOfChars` int(11) NOT NULL default '0',
  `textgap_rating` enum('ci','cs','l1','l2','l3','l4','l5') default NULL,
  `complete` enum('0','1') NOT NULL default '1',
  `created` varchar(14) NOT NULL default '',
  `original_id` int(11) default NULL,
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`question_id`),
  KEY `question_type_fi` (`question_type_fi`),
  FULLTEXT KEY `title_desc` (`title`,`comment`)
) TYPE=MyISAM;

--
-- Dumping data for table `qpl_questions`
--


/*!40000 ALTER TABLE `qpl_questions` DISABLE KEYS */;
LOCK TABLES `qpl_questions` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `qpl_questions` ENABLE KEYS */;

--
-- Table structure for table `qpl_suggested_solutions`
--

DROP TABLE IF EXISTS `qpl_suggested_solutions`;
CREATE TABLE `qpl_suggested_solutions` (
  `suggested_solution_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL default '0',
  `internal_link` varchar(50) default '',
  `import_id` varchar(50) default '',
  `subquestion_index` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`suggested_solution_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `qpl_suggested_solutions`
--


/*!40000 ALTER TABLE `qpl_suggested_solutions` DISABLE KEYS */;
LOCK TABLES `qpl_suggested_solutions` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `qpl_suggested_solutions` ENABLE KEYS */;

--
-- Table structure for table `rbac_fa`
--

DROP TABLE IF EXISTS `rbac_fa`;
CREATE TABLE `rbac_fa` (
  `rol_id` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `assign` enum('y','n') default NULL,
  `protected` enum('y','n') default 'n',
  PRIMARY KEY  (`rol_id`,`parent`),
  KEY `jmp_parent` (`parent`)
) TYPE=MyISAM;

--
-- Dumping data for table `rbac_fa`
--


/*!40000 ALTER TABLE `rbac_fa` DISABLE KEYS */;
LOCK TABLES `rbac_fa` WRITE;
INSERT INTO `rbac_fa` VALUES (2,8,'y','y'),(3,8,'n','n'),(4,8,'y','n'),(5,8,'y','n'),(83,8,'n','n'),(82,8,'n','n'),(80,8,'n','y'),(81,8,'n','n'),(14,8,'y','n'),(110,8,'n','y'),(111,8,'n','n'),(112,8,'n','n'),(125,8,'n','y'),(126,8,'n','n'),(129,8,'n','y'),(130,8,'n','n'),(131,8,'n','n'),(134,25,'y','n');
UNLOCK TABLES;
/*!40000 ALTER TABLE `rbac_fa` ENABLE KEYS */;

--
-- Table structure for table `rbac_operations`
--

DROP TABLE IF EXISTS `rbac_operations`;
CREATE TABLE `rbac_operations` (
  `ops_id` int(11) NOT NULL auto_increment,
  `operation` char(32) NOT NULL default '',
  `description` char(255) default NULL,
  `class` enum('create','general','object','rbac','admin','notused') NOT NULL default 'notused',
  `op_order` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`ops_id`),
  UNIQUE KEY `operation` (`operation`)
) TYPE=MyISAM;

--
-- Dumping data for table `rbac_operations`
--


/*!40000 ALTER TABLE `rbac_operations` DISABLE KEYS */;
LOCK TABLES `rbac_operations` WRITE;
INSERT INTO `rbac_operations` VALUES (1,'edit_permission','edit permissions','rbac',NULL),(2,'visible','view object','general',100),(3,'read','access object','general',110),(4,'write','modify object','general',120),(6,'delete','remove object','general',130),(7,'join','join/subscribe','object',NULL),(8,'leave','leave/unsubscribe','object',NULL),(9,'edit_post','edit forum articles','object',NULL),(10,'delete_post','delete forum articles','object',NULL),(11,'smtp_mail','send external mail','object',NULL),(12,'system_message','allow to send system messages','object',NULL),(13,'create_user','create new user account','create',NULL),(14,'create_role','create new role definition','create',NULL),(15,'create_rolt','create new role definition template','create',NULL),(16,'create_cat','create new category','create',NULL),(17,'create_grp','create new group','create',NULL),(18,'create_frm','create new forum','create',NULL),(19,'create_crs','create new course','create',NULL),(20,'create_lm','create new learning module','create',NULL),(21,'create_sahs','create new SCORM/AICC learning module','create',NULL),(22,'create_glo','create new glossary','create',NULL),(23,'create_dbk','create new digibook','create',NULL),(24,'create_exc','create new exercise','create',NULL),(25,'create_file','upload new file','create',NULL),(26,'create_fold','create new folder','create',NULL),(40,'edit_userassignment','change userassignment of roles','object',NULL),(41,'edit_roleassignment','change roleassignments of user accounts','object',NULL),(27,'create_tst','create new test','create',NULL),(28,'create_qpl','create new question pool','create',NULL),(29,'create_chat','create chat object','create',NULL),(30,'mail_visible','users can use mail system','object',NULL),(31,'create_mep','create new media pool','create',NULL),(32,'create_htlm','create new html learning module','create',NULL),(42,'create_svy','create new survey','create',NULL),(43,'create_spl','create new question pool (Survey)','create',NULL),(44,'create_tax','create taxonomy object','create',NULL),(45,'invite','invite','object',NULL),(46,'participate','participate','object',NULL),(47,'cat_administrate_users','Administrate local user','object',NULL),(48,'read_users','read local users','object',NULL),(49,'push_desktop_items','Allow pushing desktop items','object',NULL),(50,'create_webr','create web resource','create',NULL),(51,'search','Allow using search','object',NULL),(52,'moderate','Moderate objects','object',NULL),(53,'create_icrs','create LearnLink Seminar','create',NULL),(54,'create_icla','create LearnLink Seminar room','create',NULL),(55,'edit_learning_progress','edit learning progress','object',NULL);
UNLOCK TABLES;
/*!40000 ALTER TABLE `rbac_operations` ENABLE KEYS */;

--
-- Table structure for table `rbac_pa`
--

DROP TABLE IF EXISTS `rbac_pa`;
CREATE TABLE `rbac_pa` (
  `rol_id` int(11) NOT NULL default '0',
  `ops_id` text NOT NULL,
  `ref_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rol_id`,`ref_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `rbac_pa`
--


/*!40000 ALTER TABLE `rbac_pa` DISABLE KEYS */;
LOCK TABLES `rbac_pa` WRITE;
INSERT INTO `rbac_pa` VALUES (5,'a:2:{i:0;i:3;i:1;i:2;}',1),(14,'a:2:{i:0;i:3;i:1;i:2;}',1),(4,'a:2:{i:0;i:3;i:1;i:2;}',1),(2,'a:1:{i:0;s:2:\"51\";}',22),(3,'a:1:{i:0;s:2:\"51\";}',22),(4,'a:1:{i:0;s:2:\"51\";}',22),(5,'a:1:{i:0;s:2:\"51\";}',22),(14,'a:1:{i:0;s:2:\"51\";}',22),(80,'a:1:{i:0;s:2:\"51\";}',22),(81,'a:1:{i:0;s:2:\"51\";}',22),(82,'a:1:{i:0;s:2:\"51\";}',22),(83,'a:1:{i:0;s:2:\"51\";}',22),(110,'a:1:{i:0;s:2:\"51\";}',22),(111,'a:1:{i:0;s:2:\"51\";}',22),(112,'a:1:{i:0;s:2:\"51\";}',22),(134,'a:3:{i:0;i:52;i:1;i:3;i:2;i:2;}',24),(5,'a:1:{i:0;i:30;}',12),(4,'a:1:{i:0;i:30;}',12);
UNLOCK TABLES;
/*!40000 ALTER TABLE `rbac_pa` ENABLE KEYS */;

--
-- Table structure for table `rbac_ta`
--

DROP TABLE IF EXISTS `rbac_ta`;
CREATE TABLE `rbac_ta` (
  `typ_id` int(11) NOT NULL default '0',
  `ops_id` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`typ_id`,`ops_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `rbac_ta`
--


/*!40000 ALTER TABLE `rbac_ta` DISABLE KEYS */;
LOCK TABLES `rbac_ta` WRITE;
INSERT INTO `rbac_ta` VALUES (15,1),(15,2),(15,3),(15,4),(15,6),(15,7),(15,8),(15,18),(15,20),(15,21),(15,22),(15,23),(15,24),(15,25),(15,26),(15,27),(15,28),(15,29),(15,31),(15,32),(15,42),(15,43),(15,50),(15,53),(16,1),(16,2),(16,3),(16,4),(16,6),(16,16),(16,17),(16,18),(16,19),(16,20),(16,21),(16,22),(16,23),(16,24),(16,25),(16,27),(16,28),(16,29),(16,31),(16,32),(16,42),(16,43),(16,47),(16,48),(16,50),(16,53),(17,1),(17,2),(17,3),(17,4),(17,6),(17,7),(17,8),(17,17),(17,18),(17,20),(17,21),(17,22),(17,23),(17,24),(17,25),(17,26),(17,27),(17,28),(17,29),(17,31),(17,32),(17,42),(17,43),(17,50),(17,53),(17,55),(19,1),(19,2),(19,3),(19,4),(19,11),(19,12),(19,30),(20,1),(20,2),(20,3),(20,4),(20,6),(20,7),(20,8),(20,55),(21,1),(21,2),(21,3),(21,4),(21,44),(22,1),(22,2),(22,3),(22,4),(22,6),(22,13),(22,41),(22,48),(22,49),(23,1),(23,2),(23,3),(23,4),(23,6),(23,14),(23,15),(23,40),(24,1),(24,2),(24,3),(24,4),(28,1),(28,2),(28,3),(31,1),(31,2),(31,3),(31,4),(31,6),(31,55),(32,1),(32,2),(32,3),(32,4),(32,6),(32,7),(32,8),(33,1),(33,2),(33,3),(33,4),(33,16),(34,1),(34,2),(34,3),(34,4),(34,6),(34,7),(34,8),(34,55),(37,1),(37,2),(37,3),(37,4),(37,6),(37,9),(37,10),(84,1),(84,2),(84,3),(84,4),(84,6),(85,1),(85,2),(85,3),(85,4),(87,1),(87,2),(87,3),(87,4),(87,6),(87,18),(87,20),(87,21),(87,22),(87,23),(87,24),(87,25),(87,26),(87,27),(87,28),(87,29),(87,31),(87,32),(87,42),(87,43),(87,50),(87,53),(88,1),(88,2),(88,3),(88,4),(88,6),(94,1),(94,2),(94,3),(94,4),(94,6),(94,55),(95,1),(95,2),(95,3),(95,4),(95,6),(96,1),(96,2),(96,3),(96,4),(96,6),(96,52),(97,1),(97,2),(97,3),(97,4),(101,1),(101,2),(101,3),(101,4),(101,6),(102,1),(102,2),(102,3),(102,4),(102,6),(102,55),(103,1),(103,2),(103,3),(103,4),(103,6),(103,45),(103,46),(104,1),(104,2),(104,3),(104,4),(104,6),(105,1),(105,2),(105,3),(105,4),(105,6),(106,1),(106,2),(106,3),(106,4),(108,1),(108,2),(108,3),(108,6),(113,1),(113,2),(113,3),(113,4),(115,1),(115,2),(115,3),(115,4),(117,1),(117,2),(117,3),(117,4),(119,1),(119,2),(119,3),(119,4),(119,6),(119,7),(119,8),(119,54),(122,1),(122,2),(122,3),(122,4),(122,6),(122,7),(122,8),(123,1),(123,2),(123,3),(123,4),(123,51),(127,1),(127,2),(127,3),(127,4);
UNLOCK TABLES;
/*!40000 ALTER TABLE `rbac_ta` ENABLE KEYS */;

--
-- Table structure for table `rbac_templates`
--

DROP TABLE IF EXISTS `rbac_templates`;
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

--
-- Dumping data for table `rbac_templates`
--


/*!40000 ALTER TABLE `rbac_templates` DISABLE KEYS */;
LOCK TABLES `rbac_templates` WRITE;
INSERT INTO `rbac_templates` VALUES (3,'grp',32,8),(3,'grp',31,8),(5,'webr',2,8),(5,'tst',2,8),(5,'svy',2,8),(5,'seas',51,8),(3,'lm',2,8),(3,'lm',1,8),(3,'htlm',6,8),(3,'grp',29,8),(3,'grp',28,8),(3,'grp',27,8),(3,'htlm',1,8),(3,'grp',50,8),(3,'grp',43,8),(5,'sahs',2,8),(5,'lm',2,8),(5,'htlm',2,8),(5,'root',3,8),(5,'root',2,8),(3,'grp',26,8),(3,'grp',25,8),(3,'grp',24,8),(3,'grp',23,8),(3,'grp',22,8),(3,'grp',42,8),(3,'grp',21,8),(3,'grp',20,8),(3,'grp',18,8),(3,'grp',7,8),(3,'grp',6,8),(3,'grp',4,8),(4,'webr',3,8),(3,'grp',3,8),(3,'grp',2,8),(3,'grp',1,8),(3,'glo',6,8),(3,'glo',4,8),(3,'glo',3,8),(3,'glo',2,8),(4,'webr',2,8),(4,'tst',3,8),(4,'tst',2,8),(4,'svy',46,8),(4,'svy',3,8),(4,'svy',2,8),(4,'seas',51,8),(4,'mail',30,8),(4,'sahs',3,8),(4,'sahs',2,8),(4,'lm',3,8),(4,'lm',2,8),(4,'htlm',3,8),(3,'glo',1,8),(3,'frm',10,8),(3,'frm',9,8),(3,'frm',6,8),(3,'frm',4,8),(3,'frm',3,8),(4,'htlm',2,8),(5,'grp',2,8),(5,'glo',2,8),(5,'frm',2,8),(3,'frm',2,8),(3,'frm',1,8),(3,'fold',50,8),(3,'fold',43,8),(3,'fold',42,8),(3,'fold',32,8),(4,'root',3,8),(4,'root',2,8),(4,'grp',7,8),(4,'grp',2,8),(4,'glo',3,8),(5,'fold',2,8),(4,'glo',2,8),(83,'grp',7,8),(3,'fold',31,8),(3,'fold',29,8),(3,'fold',28,8),(3,'fold',27,8),(3,'fold',26,8),(3,'fold',25,8),(3,'fold',24,8),(4,'frm',9,8),(4,'frm',3,8),(4,'frm',2,8),(4,'fold',3,8),(5,'file',2,8),(4,'fold',2,8),(4,'file',3,8),(80,'mep',1,8),(80,'sahs',8,8),(80,'sahs',7,8),(80,'sahs',6,8),(80,'sahs',4,8),(80,'sahs',3,8),(5,'exc',2,8),(4,'file',2,8),(80,'sahs',2,8),(4,'exc',3,8),(80,'sahs',1,8),(80,'lm',6,8),(80,'lm',4,8),(80,'lm',3,8),(80,'lm',2,8),(80,'lm',1,8),(80,'htlm',6,8),(80,'htlm',4,8),(80,'htlm',3,8),(80,'htlm',2,8),(80,'htlm',1,8),(81,'webr',3,8),(80,'grp',50,8),(80,'grp',43,8),(80,'grp',42,8),(4,'exc',2,8),(4,'dbk',3,8),(4,'dbk',2,8),(4,'crs',7,8),(80,'grp',32,8),(80,'grp',31,8),(80,'grp',29,8),(81,'webr',2,8),(80,'grp',28,8),(80,'grp',27,8),(4,'crs',2,8),(3,'fold',22,8),(3,'fold',21,8),(3,'fold',20,8),(3,'fold',18,8),(80,'grp',26,8),(81,'tst',3,8),(81,'tst',2,8),(81,'svy',46,8),(3,'fold',6,8),(81,'svy',3,8),(81,'svy',2,8),(81,'sahs',3,8),(81,'sahs',2,8),(81,'lm',3,8),(81,'lm',2,8),(81,'htlm',3,8),(81,'htlm',2,8),(81,'grp',8,8),(80,'grp',25,8),(80,'grp',24,8),(81,'grp',7,8),(81,'grp',3,8),(5,'dbk',2,8),(14,'seas',51,8),(14,'root',3,8),(14,'root',2,8),(80,'grp',22,8),(81,'grp',2,8),(83,'grp',2,8),(3,'fold',4,8),(3,'fold',3,8),(14,'cat',2,8),(4,'chat',3,8),(80,'grp',21,8),(80,'grp',20,8),(80,'grp',18,8),(80,'grp',8,8),(3,'fold',2,8),(3,'fold',1,8),(3,'file',6,8),(3,'file',4,8),(3,'file',3,8),(3,'file',2,8),(3,'file',1,8),(3,'exc',6,8),(3,'exc',4,8),(3,'exc',3,8),(3,'exc',2,8),(3,'exc',1,8),(3,'dbk',3,8),(3,'dbk',2,8),(3,'crs',50,8),(80,'grp',7,8),(80,'grp',6,8),(80,'grp',4,8),(80,'grp',3,8),(80,'grp',2,8),(81,'glo',3,8),(81,'glo',2,8),(81,'frm',9,8),(81,'frm',3,8),(81,'frm',2,8),(81,'fold',3,8),(81,'fold',2,8),(81,'file',3,8),(81,'file',2,8),(81,'exc',3,8),(81,'exc',2,8),(81,'dbk',3,8),(81,'dbk',2,8),(81,'chat',3,8),(81,'chat',2,8),(80,'grp',1,8),(80,'glo',6,8),(80,'glo',4,8),(80,'glo',3,8),(80,'glo',2,8),(80,'glo',1,8),(80,'frm',10,8),(80,'frm',9,8),(80,'frm',6,8),(80,'frm',4,8),(80,'frm',3,8),(80,'frm',2,8),(80,'frm',1,8),(80,'fold',50,8),(80,'fold',43,8),(80,'fold',42,8),(80,'fold',32,8),(80,'fold',31,8),(80,'fold',29,8),(80,'fold',28,8),(80,'fold',27,8),(80,'fold',26,8),(80,'fold',25,8),(80,'fold',24,8),(80,'fold',22,8),(80,'fold',21,8),(80,'fold',20,8),(80,'fold',18,8),(80,'fold',6,8),(80,'fold',4,8),(80,'fold',3,8),(80,'fold',2,8),(80,'fold',1,8),(80,'file',6,8),(80,'file',4,8),(80,'file',3,8),(80,'file',2,8),(80,'file',1,8),(80,'exc',6,8),(80,'exc',4,8),(80,'exc',3,8),(80,'exc',2,8),(80,'exc',1,8),(80,'dbk',6,8),(80,'dbk',4,8),(80,'dbk',3,8),(80,'dbk',2,8),(80,'dbk',1,8),(80,'chat',52,8),(80,'chat',6,8),(80,'chat',4,8),(80,'chat',3,8),(80,'chat',2,8),(80,'chat',1,8),(4,'chat',2,8),(5,'crs',2,8),(4,'cat',3,8),(5,'chat',2,8),(4,'cat',2,8),(5,'cat',2,8),(110,'htlm',3,8),(110,'htlm',2,8),(110,'htlm',1,8),(110,'grp',50,8),(110,'grp',43,8),(110,'grp',42,8),(110,'grp',32,8),(110,'grp',31,8),(110,'grp',29,8),(110,'grp',28,8),(110,'grp',27,8),(110,'grp',26,8),(110,'grp',25,8),(110,'grp',24,8),(110,'grp',23,8),(110,'grp',22,8),(110,'grp',21,8),(110,'grp',20,8),(110,'grp',18,8),(110,'grp',8,8),(110,'grp',7,8),(110,'grp',6,8),(110,'grp',4,8),(110,'grp',3,8),(110,'grp',2,8),(110,'grp',1,8),(110,'glo',6,8),(110,'glo',4,8),(110,'glo',3,8),(110,'glo',2,8),(110,'glo',1,8),(110,'frm',10,8),(110,'frm',9,8),(110,'frm',6,8),(110,'frm',4,8),(110,'frm',3,8),(110,'frm',2,8),(110,'frm',1,8),(110,'fold',50,8),(110,'fold',43,8),(110,'fold',42,8),(110,'fold',32,8),(110,'fold',31,8),(110,'fold',29,8),(110,'fold',28,8),(110,'fold',27,8),(110,'fold',26,8),(110,'fold',25,8),(110,'fold',24,8),(110,'fold',23,8),(110,'fold',22,8),(110,'fold',21,8),(110,'fold',20,8),(110,'fold',18,8),(110,'fold',6,8),(110,'fold',4,8),(110,'fold',3,8),(110,'fold',2,8),(110,'fold',1,8),(110,'file',6,8),(110,'file',4,8),(110,'file',3,8),(110,'file',2,8),(110,'file',1,8),(110,'exc',6,8),(110,'exc',4,8),(110,'exc',3,8),(110,'exc',2,8),(110,'exc',1,8),(110,'dbk',6,8),(110,'dbk',4,8),(110,'dbk',3,8),(110,'dbk',2,8),(110,'dbk',1,8),(110,'crs',50,8),(110,'crs',43,8),(110,'crs',42,8),(110,'crs',32,8),(110,'crs',31,8),(110,'crs',29,8),(110,'crs',28,8),(110,'crs',27,8),(110,'crs',26,8),(110,'crs',25,8),(110,'crs',24,8),(110,'crs',22,8),(110,'crs',21,8),(110,'crs',20,8),(110,'crs',18,8),(110,'crs',17,8),(110,'crs',8,8),(110,'crs',7,8),(110,'crs',6,8),(110,'crs',4,8),(110,'crs',3,8),(110,'crs',2,8),(110,'crs',1,8),(110,'chat',52,8),(110,'chat',6,8),(110,'chat',4,8),(110,'chat',3,8),(110,'chat',2,8),(110,'chat',1,8),(111,'glo',3,8),(111,'glo',2,8),(111,'frm',10,8),(111,'frm',9,8),(111,'frm',4,8),(111,'frm',3,8),(111,'frm',2,8),(111,'fold',50,8),(111,'fold',43,8),(111,'fold',42,8),(111,'fold',29,8),(111,'fold',26,8),(111,'fold',25,8),(111,'fold',24,8),(111,'fold',18,8),(111,'fold',4,8),(111,'fold',3,8),(111,'fold',2,8),(111,'file',4,8),(111,'file',3,8),(111,'file',2,8),(111,'exc',4,8),(111,'exc',3,8),(111,'exc',2,8),(111,'dbk',4,8),(111,'dbk',3,8),(111,'dbk',2,8),(111,'crs',50,8),(111,'crs',43,8),(111,'crs',42,8),(111,'crs',29,8),(111,'crs',26,8),(111,'crs',25,8),(111,'crs',24,8),(111,'crs',18,8),(111,'crs',17,8),(111,'crs',8,8),(111,'crs',7,8),(111,'crs',4,8),(111,'crs',3,8),(111,'crs',2,8),(111,'chat',52,8),(111,'chat',4,8),(111,'chat',3,8),(111,'chat',2,8),(112,'tst',2,8),(112,'svy',46,8),(112,'svy',3,8),(112,'svy',2,8),(112,'sahs',3,8),(112,'sahs',2,8),(112,'lm',3,8),(112,'lm',2,8),(112,'htlm',3,8),(112,'htlm',2,8),(112,'grp',8,8),(112,'grp',7,8),(112,'grp',3,8),(112,'grp',2,8),(112,'glo',3,8),(112,'glo',2,8),(112,'frm',9,8),(112,'frm',3,8),(112,'frm',2,8),(112,'fold',3,8),(112,'fold',2,8),(112,'file',3,8),(112,'file',2,8),(112,'exc',3,8),(112,'exc',2,8),(112,'dbk',3,8),(112,'dbk',2,8),(112,'crs',8,8),(112,'crs',3,8),(112,'crs',2,8),(112,'chat',3,8),(112,'chat',2,8),(3,'crs',43,8),(3,'htlm',4,8),(3,'htlm',3,8),(3,'htlm',2,8),(125,'webr',6,8),(125,'webr',2,8),(125,'webr',1,8),(125,'tst',6,8),(125,'tst',2,8),(125,'tst',1,8),(125,'svy',6,8),(125,'svy',1,8),(125,'qpl',6,8),(125,'qpl',2,8),(125,'qpl',1,8),(125,'spl',6,8),(125,'spl',2,8),(125,'spl',1,8),(125,'mep',6,8),(125,'mep',2,8),(125,'mep',1,8),(125,'sahs',6,8),(125,'sahs',2,8),(125,'sahs',1,8),(125,'lm',6,8),(125,'lm',2,8),(125,'lm',1,8),(125,'htlm',6,8),(125,'htlm',2,8),(125,'htlm',1,8),(125,'grp',6,8),(125,'grp',2,8),(125,'grp',1,8),(125,'glo',6,8),(125,'glo',2,8),(125,'glo',1,8),(125,'frm',6,8),(125,'frm',2,8),(125,'frm',1,8),(125,'fold',6,8),(125,'fold',3,8),(125,'fold',2,8),(125,'fold',1,8),(125,'file',6,8),(125,'file',2,8),(125,'file',1,8),(125,'exc',6,8),(125,'exc',2,8),(125,'exc',1,8),(125,'dbk',6,8),(125,'dbk',2,8),(125,'dbk',1,8),(125,'crs',6,8),(125,'crs',3,8),(125,'crs',2,8),(125,'crs',1,8),(125,'chat',6,8),(125,'chat',2,8),(125,'chat',1,8),(125,'cat',50,8),(125,'cat',48,8),(125,'cat',47,8),(125,'cat',43,8),(125,'cat',42,8),(125,'cat',32,8),(125,'cat',31,8),(125,'cat',29,8),(125,'cat',28,8),(125,'cat',27,8),(125,'cat',25,8),(125,'cat',24,8),(125,'cat',23,8),(125,'cat',22,8),(125,'cat',21,8),(125,'cat',20,8),(125,'cat',19,8),(125,'cat',18,8),(125,'cat',17,8),(125,'cat',16,8),(125,'cat',6,8),(125,'cat',4,8),(125,'cat',3,8),(125,'cat',2,8),(125,'cat',1,8),(3,'crs',42,8),(3,'crs',32,8),(3,'crs',31,8),(3,'crs',29,8),(3,'crs',28,8),(3,'crs',27,8),(3,'crs',26,8),(3,'crs',25,8),(3,'crs',24,8),(3,'crs',22,8),(3,'crs',21,8),(3,'crs',20,8),(3,'crs',18,8),(3,'crs',17,8),(3,'crs',8,8),(3,'crs',7,8),(3,'crs',6,8),(3,'crs',4,8),(3,'crs',3,8),(3,'crs',2,8),(3,'crs',1,8),(3,'chat',52,8),(3,'chat',6,8),(3,'chat',4,8),(3,'chat',3,8),(3,'chat',2,8),(3,'chat',1,8),(3,'cat',50,8),(3,'cat',43,8),(3,'cat',42,8),(3,'cat',32,8),(3,'cat',31,8),(3,'cat',29,8),(3,'cat',28,8),(3,'cat',27,8),(3,'cat',25,8),(3,'cat',24,8),(3,'cat',22,8),(3,'cat',21,8),(3,'cat',20,8),(3,'cat',19,8),(3,'cat',18,8),(3,'cat',17,8),(3,'cat',16,8),(3,'cat',3,8),(3,'cat',2,8),(3,'lm',3,8),(3,'lm',4,8),(3,'lm',6,8),(3,'sahs',1,8),(3,'sahs',2,8),(3,'sahs',3,8),(3,'sahs',4,8),(3,'sahs',6,8),(3,'mep',1,8),(3,'mep',2,8),(3,'mep',3,8),(3,'mep',4,8),(3,'mep',6,8),(3,'spl',1,8),(3,'spl',2,8),(3,'spl',3,8),(3,'spl',4,8),(3,'spl',6,8),(3,'qpl',1,8),(3,'qpl',2,8),(3,'qpl',3,8),(3,'qpl',4,8),(3,'qpl',6,8),(3,'svy',1,8),(3,'svy',2,8),(3,'svy',3,8),(3,'svy',4,8),(3,'svy',6,8),(3,'svy',45,8),(3,'svy',46,8),(3,'tst',1,8),(3,'tst',2,8),(3,'tst',3,8),(3,'tst',4,8),(3,'tst',6,8),(3,'webr',1,8),(3,'webr',2,8),(3,'webr',3,8),(3,'webr',4,8),(3,'webr',6,8),(126,'cat',2,8),(126,'cat',3,8),(126,'cat',16,8),(126,'cat',17,8),(126,'cat',18,8),(126,'cat',19,8),(126,'cat',20,8),(126,'cat',21,8),(126,'cat',22,8),(126,'cat',24,8),(126,'cat',25,8),(126,'cat',27,8),(126,'cat',28,8),(126,'cat',29,8),(126,'cat',31,8),(126,'cat',32,8),(126,'cat',42,8),(126,'cat',43,8),(126,'cat',50,8),(126,'chat',2,8),(126,'chat',3,8),(126,'chat',4,8),(126,'chat',52,8),(126,'crs',2,8),(126,'crs',3,8),(126,'crs',7,8),(126,'exc',2,8),(126,'exc',3,8),(126,'exc',4,8),(126,'file',2,8),(126,'file',3,8),(126,'file',4,8),(126,'fold',2,8),(126,'fold',3,8),(126,'frm',2,8),(126,'frm',3,8),(126,'frm',4,8),(126,'frm',9,8),(126,'frm',10,8),(126,'glo',2,8),(126,'glo',3,8),(126,'glo',4,8),(126,'grp',2,8),(126,'grp',3,8),(126,'grp',7,8),(126,'htlm',2,8),(126,'htlm',3,8),(126,'htlm',4,8),(126,'lm',2,8),(126,'lm',3,8),(126,'lm',4,8),(126,'sahs',2,8),(126,'sahs',3,8),(126,'sahs',4,8),(126,'mep',2,8),(126,'mep',3,8),(126,'mep',4,8),(126,'spl',2,8),(126,'spl',3,8),(126,'spl',4,8),(126,'qpl',2,8),(126,'qpl',3,8),(126,'qpl',4,8),(126,'svy',2,8),(126,'svy',3,8),(126,'svy',4,8),(126,'svy',45,8),(126,'svy',46,8),(126,'tst',2,8),(126,'tst',3,8),(126,'tst',4,8),(126,'webr',2,8),(126,'webr',3,8),(126,'webr',4,8),(110,'htlm',4,8),(110,'htlm',6,8),(110,'lm',1,8),(110,'lm',2,8),(110,'lm',3,8),(110,'lm',4,8),(110,'lm',6,8),(110,'sahs',1,8),(110,'sahs',2,8),(110,'sahs',3,8),(110,'sahs',4,8),(110,'sahs',6,8),(110,'mep',1,8),(110,'mep',2,8),(110,'mep',3,8),(110,'mep',4,8),(110,'mep',6,8),(110,'spl',1,8),(110,'spl',2,8),(110,'spl',3,8),(110,'spl',4,8),(110,'spl',6,8),(110,'qpl',1,8),(110,'qpl',2,8),(110,'qpl',3,8),(110,'qpl',4,8),(110,'qpl',6,8),(110,'svy',1,8),(110,'svy',2,8),(110,'svy',3,8),(110,'svy',4,8),(110,'svy',6,8),(110,'svy',45,8),(110,'svy',46,8),(110,'tst',1,8),(110,'tst',2,8),(110,'tst',3,8),(110,'tst',4,8),(110,'tst',6,8),(110,'webr',1,8),(110,'webr',2,8),(110,'webr',3,8),(110,'webr',4,8),(110,'webr',6,8),(111,'glo',4,8),(111,'grp',2,8),(111,'grp',3,8),(111,'grp',7,8),(111,'grp',8,8),(111,'htlm',2,8),(111,'htlm',3,8),(111,'lm',2,8),(111,'lm',3,8),(111,'sahs',2,8),(111,'sahs',3,8),(111,'mep',2,8),(111,'mep',3,8),(111,'mep',4,8),(111,'spl',2,8),(111,'spl',3,8),(111,'spl',4,8),(111,'qpl',2,8),(111,'qpl',3,8),(111,'qpl',4,8),(111,'svy',2,8),(111,'svy',3,8),(111,'svy',4,8),(111,'svy',45,8),(111,'svy',46,8),(111,'tst',2,8),(111,'tst',3,8),(111,'tst',4,8),(111,'webr',2,8),(111,'webr',3,8),(111,'webr',4,8),(112,'tst',3,8),(112,'webr',2,8),(112,'webr',3,8),(80,'mep',2,8),(80,'mep',3,8),(80,'mep',4,8),(80,'mep',6,8),(80,'spl',1,8),(80,'spl',2,8),(80,'spl',3,8),(80,'spl',4,8),(80,'spl',6,8),(80,'qpl',1,8),(80,'qpl',2,8),(80,'qpl',3,8),(80,'qpl',4,8),(80,'qpl',6,8),(80,'svy',1,8),(80,'svy',2,8),(80,'svy',3,8),(80,'svy',4,8),(80,'svy',6,8),(80,'svy',45,8),(80,'svy',46,8),(80,'tst',1,8),(80,'tst',2,8),(80,'tst',3,8),(80,'tst',4,8),(80,'tst',6,8),(80,'webr',1,8),(80,'webr',2,8),(80,'webr',3,8),(80,'webr',4,8),(80,'webr',6,8),(129,'icrs',1,8),(129,'icrs',2,8),(129,'icrs',3,8),(129,'icrs',4,8),(129,'icrs',6,8),(129,'icrs',7,8),(129,'icrs',8,8),(129,'icrs',54,8),(129,'rolf',1,8),(129,'rolf',2,8),(129,'rolf',3,8),(129,'rolf',4,8),(129,'rolf',6,8),(129,'rolf',14,8),(130,'icrs',2,8),(130,'icrs',3,8),(130,'icrs',7,8),(130,'icrs',8,8),(131,'crs',2,8),(131,'crs',7,8),(131,'crs',8,8),(3,'lm',55,8),(3,'tst',55,8),(3,'dbk',55,8),(3,'sahs',55,8),(3,'htlm',55,8),(3,'crs',55,8),(110,'lm',55,8),(110,'tst',55,8),(110,'dbk',55,8),(110,'sahs',55,8),(110,'htlm',55,8),(110,'crs',55,8),(125,'lm',55,8),(125,'tst',55,8),(125,'dbk',55,8),(125,'sahs',55,8),(125,'htlm',55,8),(125,'crs',55,8);
UNLOCK TABLES;
/*!40000 ALTER TABLE `rbac_templates` ENABLE KEYS */;

--
-- Table structure for table `rbac_ua`
--

DROP TABLE IF EXISTS `rbac_ua`;
CREATE TABLE `rbac_ua` (
  `usr_id` int(11) NOT NULL default '0',
  `rol_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`rol_id`),
  KEY `usr_id` (`usr_id`),
  KEY `rol_id` (`rol_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `rbac_ua`
--


/*!40000 ALTER TABLE `rbac_ua` DISABLE KEYS */;
LOCK TABLES `rbac_ua` WRITE;
INSERT INTO `rbac_ua` VALUES (6,2),(13,14);
UNLOCK TABLES;
/*!40000 ALTER TABLE `rbac_ua` ENABLE KEYS */;

--
-- Table structure for table `role_data`
--

DROP TABLE IF EXISTS `role_data`;
CREATE TABLE `role_data` (
  `role_id` int(11) NOT NULL default '0',
  `allow_register` tinyint(1) unsigned NOT NULL default '0',
  `assign_users` char(2) default '0',
  `auth_mode` enum('default','local','ldap','radius','shibboleth','script') NOT NULL default 'default',
  PRIMARY KEY  (`role_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `role_data`
--


/*!40000 ALTER TABLE `role_data` DISABLE KEYS */;
LOCK TABLES `role_data` WRITE;
INSERT INTO `role_data` VALUES (2,0,'0','default'),(3,0,'0','default'),(4,0,'0','default'),(5,1,'0','default'),(14,0,'0','default'),(134,0,'0','default');
UNLOCK TABLES;
/*!40000 ALTER TABLE `role_data` ENABLE KEYS */;

--
-- Table structure for table `role_desktop_items`
--

DROP TABLE IF EXISTS `role_desktop_items`;
CREATE TABLE `role_desktop_items` (
  `role_item_id` int(11) NOT NULL auto_increment,
  `role_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  `item_type` char(16) NOT NULL default '',
  KEY `role_item_id` (`role_item_id`,`role_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `role_desktop_items`
--


/*!40000 ALTER TABLE `role_desktop_items` DISABLE KEYS */;
LOCK TABLES `role_desktop_items` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `role_desktop_items` ENABLE KEYS */;

--
-- Table structure for table `sahs_lm`
--

DROP TABLE IF EXISTS `sahs_lm`;
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

--
-- Dumping data for table `sahs_lm`
--


/*!40000 ALTER TABLE `sahs_lm` DISABLE KEYS */;
LOCK TABLES `sahs_lm` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sahs_lm` ENABLE KEYS */;

--
-- Table structure for table `sc_item`
--

DROP TABLE IF EXISTS `sc_item`;
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

--
-- Dumping data for table `sc_item`
--


/*!40000 ALTER TABLE `sc_item` DISABLE KEYS */;
LOCK TABLES `sc_item` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sc_item` ENABLE KEYS */;

--
-- Table structure for table `sc_manifest`
--

DROP TABLE IF EXISTS `sc_manifest`;
CREATE TABLE `sc_manifest` (
  `obj_id` int(11) NOT NULL default '0',
  `import_id` varchar(200) default NULL,
  `version` varchar(200) default NULL,
  `xml_base` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `sc_manifest`
--


/*!40000 ALTER TABLE `sc_manifest` DISABLE KEYS */;
LOCK TABLES `sc_manifest` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sc_manifest` ENABLE KEYS */;

--
-- Table structure for table `sc_organization`
--

DROP TABLE IF EXISTS `sc_organization`;
CREATE TABLE `sc_organization` (
  `obj_id` int(11) NOT NULL default '0',
  `import_id` varchar(200) default NULL,
  `structure` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `sc_organization`
--


/*!40000 ALTER TABLE `sc_organization` DISABLE KEYS */;
LOCK TABLES `sc_organization` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sc_organization` ENABLE KEYS */;

--
-- Table structure for table `sc_organizations`
--

DROP TABLE IF EXISTS `sc_organizations`;
CREATE TABLE `sc_organizations` (
  `obj_id` int(11) NOT NULL default '0',
  `default_organization` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `sc_organizations`
--


/*!40000 ALTER TABLE `sc_organizations` DISABLE KEYS */;
LOCK TABLES `sc_organizations` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sc_organizations` ENABLE KEYS */;

--
-- Table structure for table `sc_resource`
--

DROP TABLE IF EXISTS `sc_resource`;
CREATE TABLE `sc_resource` (
  `obj_id` int(11) NOT NULL default '0',
  `import_id` varchar(200) default NULL,
  `resourcetype` varchar(30) default NULL,
  `scormtype` enum('sco','asset') default NULL,
  `href` varchar(250) default NULL,
  `xml_base` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `sc_resource`
--


/*!40000 ALTER TABLE `sc_resource` DISABLE KEYS */;
LOCK TABLES `sc_resource` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sc_resource` ENABLE KEYS */;

--
-- Table structure for table `sc_resource_dependency`
--

DROP TABLE IF EXISTS `sc_resource_dependency`;
CREATE TABLE `sc_resource_dependency` (
  `id` int(11) NOT NULL auto_increment,
  `res_id` int(11) default NULL,
  `identifierref` varchar(200) default NULL,
  `nr` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `sc_resource_dependency`
--


/*!40000 ALTER TABLE `sc_resource_dependency` DISABLE KEYS */;
LOCK TABLES `sc_resource_dependency` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sc_resource_dependency` ENABLE KEYS */;

--
-- Table structure for table `sc_resource_file`
--

DROP TABLE IF EXISTS `sc_resource_file`;
CREATE TABLE `sc_resource_file` (
  `id` int(11) NOT NULL auto_increment,
  `res_id` int(11) default NULL,
  `href` text,
  `nr` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `sc_resource_file`
--


/*!40000 ALTER TABLE `sc_resource_file` DISABLE KEYS */;
LOCK TABLES `sc_resource_file` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sc_resource_file` ENABLE KEYS */;

--
-- Table structure for table `sc_resources`
--

DROP TABLE IF EXISTS `sc_resources`;
CREATE TABLE `sc_resources` (
  `obj_id` int(11) NOT NULL default '0',
  `xml_base` varchar(200) default NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `sc_resources`
--


/*!40000 ALTER TABLE `sc_resources` DISABLE KEYS */;
LOCK TABLES `sc_resources` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `sc_resources` ENABLE KEYS */;

--
-- Table structure for table `scorm_object`
--

DROP TABLE IF EXISTS `scorm_object`;
CREATE TABLE `scorm_object` (
  `obj_id` int(11) NOT NULL auto_increment,
  `title` varchar(200) default NULL,
  `type` varchar(3) default NULL,
  `slm_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `scorm_object`
--


/*!40000 ALTER TABLE `scorm_object` DISABLE KEYS */;
LOCK TABLES `scorm_object` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `scorm_object` ENABLE KEYS */;

--
-- Table structure for table `scorm_tracking`
--

DROP TABLE IF EXISTS `scorm_tracking`;
CREATE TABLE `scorm_tracking` (
  `user_id` int(11) NOT NULL default '0',
  `sco_id` int(11) NOT NULL default '0',
  `lvalue` varchar(64) NOT NULL default '',
  `rvalue` text,
  `obj_id` int(11) default NULL,
  PRIMARY KEY  (`user_id`,`sco_id`,`lvalue`)
) TYPE=MyISAM;

--
-- Dumping data for table `scorm_tracking`
--


/*!40000 ALTER TABLE `scorm_tracking` DISABLE KEYS */;
LOCK TABLES `scorm_tracking` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `scorm_tracking` ENABLE KEYS */;

--
-- Table structure for table `scorm_tree`
--

DROP TABLE IF EXISTS `scorm_tree`;
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

--
-- Dumping data for table `scorm_tree`
--


/*!40000 ALTER TABLE `scorm_tree` DISABLE KEYS */;
LOCK TABLES `scorm_tree` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `scorm_tree` ENABLE KEYS */;

--
-- Table structure for table `search_data`
--

DROP TABLE IF EXISTS `search_data`;
CREATE TABLE `search_data` (
  `obj_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `target` text NOT NULL,
  `type` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`user_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `search_data`
--


/*!40000 ALTER TABLE `search_data` DISABLE KEYS */;
LOCK TABLES `search_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `search_data` ENABLE KEYS */;

--
-- Table structure for table `search_tree`
--

DROP TABLE IF EXISTS `search_tree`;
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

--
-- Dumping data for table `search_tree`
--


/*!40000 ALTER TABLE `search_tree` DISABLE KEYS */;
LOCK TABLES `search_tree` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `search_tree` ENABLE KEYS */;

--
-- Table structure for table `service`
--

DROP TABLE IF EXISTS `service`;
CREATE TABLE `service` (
  `name` varchar(100) NOT NULL default '',
  `dir` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`name`)
) TYPE=MyISAM COMMENT='ILIAS Modules';

--
-- Dumping data for table `service`
--


/*!40000 ALTER TABLE `service` DISABLE KEYS */;
LOCK TABLES `service` WRITE;
INSERT INTO `service` VALUES ('Help','Services/Help'),('PersonalDesktop','.'),('Administration','.');
UNLOCK TABLES;
/*!40000 ALTER TABLE `service` ENABLE KEYS */;

--
-- Table structure for table `service_class`
--

DROP TABLE IF EXISTS `service_class`;
CREATE TABLE `service_class` (
  `class` varchar(100) NOT NULL default '',
  `service` varchar(100) NOT NULL default '',
  `dir` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`class`)
) TYPE=MyISAM COMMENT='Class information of ILIAS Modules';

--
-- Dumping data for table `service_class`
--


/*!40000 ALTER TABLE `service_class` DISABLE KEYS */;
LOCK TABLES `service_class` WRITE;
INSERT INTO `service_class` VALUES ('ilHelpGUI','Help','classes'),('ilPersonalDesktopGUI','PersonalDesktop','classes'),('ilAdministrationGUI','Administration','classes');
UNLOCK TABLES;
/*!40000 ALTER TABLE `service_class` ENABLE KEYS */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `keyword` char(50) NOT NULL default '',
  `value` char(50) NOT NULL default '',
  PRIMARY KEY  (`keyword`)
) TYPE=MyISAM;

--
-- Dumping data for table `settings`
--


/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
LOCK TABLES `settings` WRITE;
INSERT INTO `settings` VALUES ('convert_path',''),('db_version','656'),('ilias_version','3.2.3 2004-11-22'),('inst_info',''),('inst_name',''),('java_path',''),('language','en'),('ldap_basedn',''),('ldap_port',''),('ldap_server',''),('system_user_id','6'),('anonymous_role_id','14'),('error_recipient',''),('pub_section',''),('feedback_recipient',''),('unzip_path',''),('anonymous_user_id','13'),('zip_path',''),('enable_registration','1'),('system_role_id','2'),('recovery_folder_id','15'),('bench_max_records','10000'),('enable_bench','0'),('sys_user_tracking_id','17'),('enable_tracking','0'),('save_user_related_data','0'),('auth_mode','1'),('auto_registration','1'),('approve_recipient',''),('require_login','1'),('require_passwd','1'),('require_passwd2','1'),('require_firstname','1'),('require_gender','1'),('require_lastname','1'),('require_institution',''),('require_department',''),('require_street',''),('require_city',''),('require_zipcode',''),('require_country',''),('require_phone_office',''),('require_phone_home',''),('require_phone_mobile',''),('require_fax',''),('require_email','1'),('require_hobby',''),('require_default_role','1'),('require_referral_comment',''),('enable_js_edit','1'),('sys_assessment_folder_id','20'),('default_repository_view','flat'),('enable_calendar','1'),('custom_icon_big_width','32'),('custom_icon_big_height','32'),('custom_icon_small_width','22'),('custom_icon_small_height','22'),('icon_position_in_lists','header');
UNLOCK TABLES;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;

--
-- Table structure for table `settings_deactivated_styles`
--

DROP TABLE IF EXISTS `settings_deactivated_styles`;
CREATE TABLE `settings_deactivated_styles` (
  `skin` varchar(100) NOT NULL default '',
  `style` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`skin`,`style`)
) TYPE=MyISAM;

--
-- Dumping data for table `settings_deactivated_styles`
--


/*!40000 ALTER TABLE `settings_deactivated_styles` DISABLE KEYS */;
LOCK TABLES `settings_deactivated_styles` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `settings_deactivated_styles` ENABLE KEYS */;

--
-- Table structure for table `style_data`
--

DROP TABLE IF EXISTS `style_data`;
CREATE TABLE `style_data` (
  `id` int(11) NOT NULL default '0',
  `uptodate` tinyint(2) default '0'
) TYPE=MyISAM;

--
-- Dumping data for table `style_data`
--


/*!40000 ALTER TABLE `style_data` DISABLE KEYS */;
LOCK TABLES `style_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `style_data` ENABLE KEYS */;

--
-- Table structure for table `style_folder_styles`
--

DROP TABLE IF EXISTS `style_folder_styles`;
CREATE TABLE `style_folder_styles` (
  `folder_id` int(11) NOT NULL default '0',
  `style_id` int(11) NOT NULL default '0',
  KEY `f_id` (`folder_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `style_folder_styles`
--


/*!40000 ALTER TABLE `style_folder_styles` DISABLE KEYS */;
LOCK TABLES `style_folder_styles` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `style_folder_styles` ENABLE KEYS */;

--
-- Table structure for table `style_parameter`
--

DROP TABLE IF EXISTS `style_parameter`;
CREATE TABLE `style_parameter` (
  `id` int(11) NOT NULL auto_increment,
  `style_id` int(11) NOT NULL default '0',
  `tag` varchar(100) default NULL,
  `class` varchar(100) default NULL,
  `parameter` varchar(100) default NULL,
  `value` varchar(100) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `style_parameter`
--


/*!40000 ALTER TABLE `style_parameter` DISABLE KEYS */;
LOCK TABLES `style_parameter` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `style_parameter` ENABLE KEYS */;

--
-- Table structure for table `survey_anonymous`
--

DROP TABLE IF EXISTS `survey_anonymous`;
CREATE TABLE `survey_anonymous` (
  `anonymous_id` int(11) NOT NULL auto_increment,
  `survey_key` varchar(32) NOT NULL default '',
  `survey_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`anonymous_id`),
  KEY `survey_key` (`survey_key`,`survey_fi`),
  KEY `survey_fi` (`survey_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_anonymous`
--


/*!40000 ALTER TABLE `survey_anonymous` DISABLE KEYS */;
LOCK TABLES `survey_anonymous` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_anonymous` ENABLE KEYS */;

--
-- Table structure for table `survey_answer`
--

DROP TABLE IF EXISTS `survey_answer`;
CREATE TABLE `survey_answer` (
  `answer_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `anonymous_id` varchar(32) NOT NULL default '',
  `value` double default NULL,
  `textanswer` text,
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`answer_id`),
  KEY `survey_fi` (`survey_fi`),
  KEY `question_fi` (`question_fi`),
  KEY `user_fi` (`user_fi`),
  KEY `anonymous_id` (`anonymous_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_answer`
--


/*!40000 ALTER TABLE `survey_answer` DISABLE KEYS */;
LOCK TABLES `survey_answer` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_answer` ENABLE KEYS */;

--
-- Table structure for table `survey_category`
--

DROP TABLE IF EXISTS `survey_category`;
CREATE TABLE `survey_category` (
  `category_id` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `defaultvalue` enum('0','1') NOT NULL default '0',
  `owner_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`category_id`),
  KEY `owner_fi` (`owner_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_category`
--


/*!40000 ALTER TABLE `survey_category` DISABLE KEYS */;
LOCK TABLES `survey_category` WRITE;
INSERT INTO `survey_category` VALUES (1,'dc_desired','1',0,'2004-05-22 13:43:01'),(2,'dc_undesired','1',0,'2004-05-22 13:43:01'),(3,'dc_agree','1',0,'2004-05-22 13:43:01'),(4,'dc_disagree','1',0,'2004-05-22 13:43:01'),(5,'dc_good','1',0,'2004-05-22 13:43:01'),(6,'dc_notacceptable','1',0,'2004-05-22 13:43:01'),(7,'dc_should','1',0,'2004-05-22 13:43:01'),(8,'dc_shouldnot','1',0,'2004-05-22 13:43:01'),(9,'dc_true','1',0,'2004-05-22 13:43:01'),(10,'dc_false','1',0,'2004-05-22 13:43:01'),(11,'dc_always','1',0,'2004-05-22 13:43:01'),(12,'dc_never','1',0,'2004-05-22 13:43:01'),(13,'dc_yes','1',0,'2004-05-22 13:43:01'),(14,'dc_no','1',0,'2004-05-22 13:43:01'),(15,'dc_neutral','1',0,'2004-05-22 13:43:01'),(16,'dc_undecided','1',0,'2004-05-22 13:43:01'),(17,'dc_fair','1',0,'2004-05-22 13:43:01'),(18,'dc_sometimes','1',0,'2004-05-22 13:43:01'),(19,'dc_stronglydesired','1',0,'2004-05-22 13:43:01'),(20,'dc_stronglyundesired','1',0,'2004-05-22 13:43:01'),(21,'dc_stronglyagree','1',0,'2004-05-22 13:43:01'),(22,'dc_stronglydisagree','1',0,'2004-05-22 13:43:01'),(23,'dc_verygood','1',0,'2004-05-22 13:43:01'),(24,'dc_poor','1',0,'2004-05-22 13:43:01'),(25,'dc_must','1',0,'2004-05-22 13:43:01'),(26,'dc_mustnot','1',0,'2004-05-22 13:43:01'),(27,'dc_definitelytrue','1',0,'2004-05-22 13:43:01'),(28,'dc_definitelyfalse','1',0,'2004-05-22 13:43:01'),(29,'dc_manytimes','1',0,'2004-05-22 13:43:01'),(30,'dc_varying','1',0,'2004-05-22 13:43:01'),(31,'dc_rarely','1',0,'2004-05-22 13:43:01'),(32,'dc_mostcertainly','1',0,'2004-05-22 13:43:01'),(33,'dc_morepositive','1',0,'2004-05-22 13:43:01'),(34,'dc_morenegative','1',0,'2004-05-22 13:43:01'),(35,'dc_mostcertainlynot','1',0,'2004-05-22 13:43:01');
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_category` ENABLE KEYS */;

--
-- Table structure for table `survey_constraint`
--

DROP TABLE IF EXISTS `survey_constraint`;
CREATE TABLE `survey_constraint` (
  `constraint_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL default '0',
  `relation_fi` int(11) NOT NULL default '0',
  `value` double NOT NULL default '0',
  PRIMARY KEY  (`constraint_id`),
  KEY `question_fi` (`question_fi`),
  KEY `relation_fi` (`relation_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_constraint`
--


/*!40000 ALTER TABLE `survey_constraint` DISABLE KEYS */;
LOCK TABLES `survey_constraint` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_constraint` ENABLE KEYS */;

--
-- Table structure for table `survey_finished`
--

DROP TABLE IF EXISTS `survey_finished`;
CREATE TABLE `survey_finished` (
  `finished_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `anonymous_id` varchar(32) default NULL,
  `state` enum('0','1') NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`finished_id`),
  KEY `survey_fi` (`survey_fi`),
  KEY `user_fi` (`user_fi`),
  KEY `anonymous_id` (`anonymous_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_finished`
--


/*!40000 ALTER TABLE `survey_finished` DISABLE KEYS */;
LOCK TABLES `survey_finished` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_finished` ENABLE KEYS */;

--
-- Table structure for table `survey_invited_group`
--

DROP TABLE IF EXISTS `survey_invited_group`;
CREATE TABLE `survey_invited_group` (
  `invited_group_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `group_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`invited_group_id`),
  KEY `survey_fi` (`survey_fi`),
  KEY `group_fi` (`group_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_invited_group`
--


/*!40000 ALTER TABLE `survey_invited_group` DISABLE KEYS */;
LOCK TABLES `survey_invited_group` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_invited_group` ENABLE KEYS */;

--
-- Table structure for table `survey_invited_user`
--

DROP TABLE IF EXISTS `survey_invited_user`;
CREATE TABLE `survey_invited_user` (
  `invited_user_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`invited_user_id`),
  KEY `survey_fi` (`survey_fi`),
  KEY `user_fi` (`user_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_invited_user`
--


/*!40000 ALTER TABLE `survey_invited_user` DISABLE KEYS */;
LOCK TABLES `survey_invited_user` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_invited_user` ENABLE KEYS */;

--
-- Table structure for table `survey_material`
--

DROP TABLE IF EXISTS `survey_material`;
CREATE TABLE `survey_material` (
  `material_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL default '0',
  `internal_link` varchar(50) default NULL,
  `import_id` varchar(50) default NULL,
  `material_title` varchar(255) default NULL,
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`material_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_material`
--


/*!40000 ALTER TABLE `survey_material` DISABLE KEYS */;
LOCK TABLES `survey_material` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_material` ENABLE KEYS */;

--
-- Table structure for table `survey_phrase`
--

DROP TABLE IF EXISTS `survey_phrase`;
CREATE TABLE `survey_phrase` (
  `phrase_id` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `defaultvalue` enum('0','1','2') NOT NULL default '0',
  `owner_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`phrase_id`),
  KEY `owner_fi` (`owner_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_phrase`
--


/*!40000 ALTER TABLE `survey_phrase` DISABLE KEYS */;
LOCK TABLES `survey_phrase` WRITE;
INSERT INTO `survey_phrase` VALUES (1,'dp_standard_attitude_desired_undesired','1',0,'2004-05-22 13:54:31'),(2,'dp_standard_attitude_agree_disagree','1',0,'2004-05-22 13:54:58'),(3,'dp_standard_attitude_good_notacceptable','1',0,'2004-05-22 13:55:18'),(4,'dp_standard_attitude_shold_shouldnot','1',0,'2004-05-22 13:55:46'),(5,'dp_standard_beliefs_true_false','1',0,'2004-05-22 13:56:13'),(6,'dp_standard_beliefs_always_never','1',0,'2004-05-22 14:05:47'),(7,'dp_standard_behaviour_yes_no','1',0,'2004-05-22 14:05:47'),(8,'dp_standard_attitude_desired_neutral_undesired','1',0,'2004-05-22 14:05:47'),(9,'dp_standard_attitude_agree_undecided_disagree','1',0,'2004-05-22 14:05:47'),(10,'dp_standard_attitude_good_fair_notacceptable','1',0,'2004-05-22 14:05:47'),(11,'dp_standard_attitude_should_undecided_shouldnot','1',0,'2004-05-22 14:05:47'),(12,'dp_standard_beliefs_true_undecided_false','1',0,'2004-05-22 14:05:47'),(13,'dp_standard_beliefs_always_sometimes_never','1',0,'2004-05-22 14:05:47'),(14,'dp_standard_behaviour_yes_undecided_no','1',0,'2004-05-22 14:05:47'),(15,'dp_standard_attitude_desired5','1',0,'2004-05-22 15:07:02'),(16,'dp_standard_attitude_agree5','1',0,'2004-05-22 15:07:17'),(17,'dp_standard_attitude_good5','1',0,'2004-05-22 15:07:29'),(18,'dp_standard_attitude_must5','1',0,'2004-05-22 15:07:44'),(19,'dp_standard_beliefs_true5','1',0,'2004-05-22 15:07:54'),(20,'dp_standard_beliefs_always5','1',0,'2004-05-22 15:08:12'),(21,'dp_standard_behaviour_certainly5','1',0,'2004-05-22 15:08:28'),(22,'dp_standard_numbers','1',0,'2004-06-21 01:27:19');
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_phrase` ENABLE KEYS */;

--
-- Table structure for table `survey_phrase_category`
--

DROP TABLE IF EXISTS `survey_phrase_category`;
CREATE TABLE `survey_phrase_category` (
  `phrase_category_id` int(11) NOT NULL auto_increment,
  `phrase_fi` int(11) NOT NULL default '0',
  `category_fi` int(11) NOT NULL default '0',
  `sequence` int(11) NOT NULL default '0',
  PRIMARY KEY  (`phrase_category_id`),
  KEY `phrase_fi` (`phrase_fi`),
  KEY `category_fi` (`category_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_phrase_category`
--


/*!40000 ALTER TABLE `survey_phrase_category` DISABLE KEYS */;
LOCK TABLES `survey_phrase_category` WRITE;
INSERT INTO `survey_phrase_category` VALUES (1,1,1,1),(2,1,2,2),(3,2,3,1),(4,2,4,2),(5,3,5,1),(6,3,6,2),(7,4,7,1),(8,4,8,2),(9,5,9,1),(10,5,10,2),(11,6,11,1),(12,6,12,2),(13,7,13,1),(14,7,14,2),(15,8,1,1),(16,8,15,2),(17,8,2,3),(18,9,3,1),(19,9,16,2),(20,9,4,3),(21,10,5,1),(22,10,17,2),(23,10,6,3),(24,11,7,1),(25,11,16,2),(26,11,8,3),(27,12,9,1),(28,12,16,2),(29,12,10,3),(30,13,11,1),(31,13,18,2),(32,13,12,3),(33,14,13,1),(34,14,16,2),(35,14,14,3),(36,15,19,1),(37,15,1,2),(38,15,15,3),(39,15,2,4),(40,15,20,5),(41,16,21,1),(42,16,3,2),(43,16,16,3),(44,16,4,4),(45,16,22,5),(46,17,23,1),(47,17,5,2),(48,17,17,3),(49,17,24,4),(50,17,6,5),(51,18,25,1),(52,18,7,2),(53,18,16,3),(54,18,8,4),(55,18,26,5),(56,19,27,1),(57,19,9,2),(58,19,16,3),(59,19,10,4),(60,19,28,5),(61,20,11,1),(62,20,29,2),(63,20,30,3),(64,20,31,4),(65,20,12,5),(66,21,32,1),(67,21,33,2),(68,21,16,3),(69,21,34,4),(70,21,35,5);
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_phrase_category` ENABLE KEYS */;

--
-- Table structure for table `survey_question`
--

DROP TABLE IF EXISTS `survey_question`;
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
  `orientation` enum('0','1','2') default '0',
  `maxchars` int(11) default NULL,
  `complete` enum('0','1') NOT NULL default '0',
  `created` varchar(14) NOT NULL default '',
  `original_id` int(11) default NULL,
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`question_id`),
  KEY `obj_fi` (`obj_fi`),
  KEY `owner_fi` (`owner_fi`),
  FULLTEXT KEY `title_desc` (`title`,`description`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_question`
--


/*!40000 ALTER TABLE `survey_question` DISABLE KEYS */;
LOCK TABLES `survey_question` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_question` ENABLE KEYS */;

--
-- Table structure for table `survey_question_constraint`
--

DROP TABLE IF EXISTS `survey_question_constraint`;
CREATE TABLE `survey_question_constraint` (
  `question_constraint_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `constraint_fi` int(11) NOT NULL default '0',
  PRIMARY KEY  (`question_constraint_id`),
  KEY `survey_fi` (`survey_fi`),
  KEY `question_fi` (`question_fi`),
  KEY `constraint_fi` (`constraint_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_question_constraint`
--


/*!40000 ALTER TABLE `survey_question_constraint` DISABLE KEYS */;
LOCK TABLES `survey_question_constraint` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_question_constraint` ENABLE KEYS */;

--
-- Table structure for table `survey_question_material`
--

DROP TABLE IF EXISTS `survey_question_material`;
CREATE TABLE `survey_question_material` (
  `material_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL default '0',
  `materials` text,
  `materials_file` varchar(200) NOT NULL default '',
  UNIQUE KEY `material_id` (`material_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_question_material`
--


/*!40000 ALTER TABLE `survey_question_material` DISABLE KEYS */;
LOCK TABLES `survey_question_material` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_question_material` ENABLE KEYS */;

--
-- Table structure for table `survey_question_obligatory`
--

DROP TABLE IF EXISTS `survey_question_obligatory`;
CREATE TABLE `survey_question_obligatory` (
  `question_obligatory_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `obligatory` enum('0','1') NOT NULL default '1',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`question_obligatory_id`),
  KEY `survey_fi` (`survey_fi`,`question_fi`)
) TYPE=MyISAM COMMENT='Contains the obligatory state of questions in a survey';

--
-- Dumping data for table `survey_question_obligatory`
--


/*!40000 ALTER TABLE `survey_question_obligatory` DISABLE KEYS */;
LOCK TABLES `survey_question_obligatory` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_question_obligatory` ENABLE KEYS */;

--
-- Table structure for table `survey_questionblock`
--

DROP TABLE IF EXISTS `survey_questionblock`;
CREATE TABLE `survey_questionblock` (
  `questionblock_id` int(11) NOT NULL auto_increment,
  `title` text,
  `owner_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`questionblock_id`),
  KEY `owner_fi` (`owner_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_questionblock`
--


/*!40000 ALTER TABLE `survey_questionblock` DISABLE KEYS */;
LOCK TABLES `survey_questionblock` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_questionblock` ENABLE KEYS */;

--
-- Table structure for table `survey_questionblock_question`
--

DROP TABLE IF EXISTS `survey_questionblock_question`;
CREATE TABLE `survey_questionblock_question` (
  `questionblock_question_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `questionblock_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  PRIMARY KEY  (`questionblock_question_id`),
  KEY `survey_fi` (`survey_fi`),
  KEY `questionblock_fi` (`questionblock_fi`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_questionblock_question`
--


/*!40000 ALTER TABLE `survey_questionblock_question` DISABLE KEYS */;
LOCK TABLES `survey_questionblock_question` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_questionblock_question` ENABLE KEYS */;

--
-- Table structure for table `survey_questionpool`
--

DROP TABLE IF EXISTS `survey_questionpool`;
CREATE TABLE `survey_questionpool` (
  `id_questionpool` int(11) NOT NULL auto_increment,
  `obj_fi` int(11) NOT NULL default '0',
  `online` enum('0','1') NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`id_questionpool`),
  KEY `obj_fi` (`obj_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_questionpool`
--


/*!40000 ALTER TABLE `survey_questionpool` DISABLE KEYS */;
LOCK TABLES `survey_questionpool` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_questionpool` ENABLE KEYS */;

--
-- Table structure for table `survey_questiontype`
--

DROP TABLE IF EXISTS `survey_questiontype`;
CREATE TABLE `survey_questiontype` (
  `questiontype_id` int(11) NOT NULL auto_increment,
  `type_tag` varchar(30) NOT NULL default '',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`questiontype_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_questiontype`
--


/*!40000 ALTER TABLE `survey_questiontype` DISABLE KEYS */;
LOCK TABLES `survey_questiontype` WRITE;
INSERT INTO `survey_questiontype` VALUES (1,'qt_nominal','2004-05-18 22:28:41'),(2,'qt_ordinal','2004-05-18 22:28:48'),(3,'qt_metric','2004-05-18 22:28:59'),(4,'qt_text','2004-05-18 22:29:04');
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_questiontype` ENABLE KEYS */;

--
-- Table structure for table `survey_relation`
--

DROP TABLE IF EXISTS `survey_relation`;
CREATE TABLE `survey_relation` (
  `relation_id` int(11) NOT NULL auto_increment,
  `longname` varchar(20) NOT NULL default '',
  `shortname` varchar(2) NOT NULL default '',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`relation_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_relation`
--


/*!40000 ALTER TABLE `survey_relation` DISABLE KEYS */;
LOCK TABLES `survey_relation` WRITE;
INSERT INTO `survey_relation` VALUES (1,'less','<','2004-05-18 19:57:53'),(2,'less_or_equal','<=','2004-05-18 19:58:08'),(3,'equal','=','2004-05-18 19:58:16'),(4,'not_equal','<>','2004-05-18 19:58:39'),(5,'more_or_equal','>=','2004-05-18 19:58:52'),(6,'more','>','2004-05-18 19:59:03');
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_relation` ENABLE KEYS */;

--
-- Table structure for table `survey_survey`
--

DROP TABLE IF EXISTS `survey_survey`;
CREATE TABLE `survey_survey` (
  `survey_id` int(11) NOT NULL auto_increment,
  `obj_fi` int(11) NOT NULL default '0',
  `author` varchar(50) NOT NULL default '',
  `introduction` text,
  `status` enum('0','1') NOT NULL default '1',
  `startdate` date default NULL,
  `enddate` date default NULL,
  `evaluation_access` enum('0','1','2') NOT NULL default '0',
  `invitation` enum('0','1') NOT NULL default '0',
  `invitation_mode` enum('0','1') NOT NULL default '1',
  `complete` enum('0','1') NOT NULL default '0',
  `created` varchar(14) NOT NULL default '',
  `anonymize` enum('0','1') NOT NULL default '0',
  `show_question_titles` enum('0','1') NOT NULL default '1',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`survey_id`),
  KEY `obj_fi` (`obj_fi`),
  FULLTEXT KEY `introduction` (`introduction`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_survey`
--


/*!40000 ALTER TABLE `survey_survey` DISABLE KEYS */;
LOCK TABLES `survey_survey` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_survey` ENABLE KEYS */;

--
-- Table structure for table `survey_survey_question`
--

DROP TABLE IF EXISTS `survey_survey_question`;
CREATE TABLE `survey_survey_question` (
  `survey_question_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `sequence` int(11) NOT NULL default '0',
  `heading` varchar(255) default NULL,
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`survey_question_id`),
  KEY `survey_fi` (`survey_fi`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_survey_question`
--


/*!40000 ALTER TABLE `survey_survey_question` DISABLE KEYS */;
LOCK TABLES `survey_survey_question` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_survey_question` ENABLE KEYS */;

--
-- Table structure for table `survey_variable`
--

DROP TABLE IF EXISTS `survey_variable`;
CREATE TABLE `survey_variable` (
  `variable_id` int(11) NOT NULL auto_increment,
  `category_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `value1` double default NULL,
  `value2` double default NULL,
  `sequence` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`variable_id`),
  KEY `category_fi` (`category_fi`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `survey_variable`
--


/*!40000 ALTER TABLE `survey_variable` DISABLE KEYS */;
LOCK TABLES `survey_variable` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `survey_variable` ENABLE KEYS */;

--
-- Table structure for table `tree`
--

DROP TABLE IF EXISTS `tree`;
CREATE TABLE `tree` (
  `tree` int(10) NOT NULL default '0',
  `child` int(10) unsigned NOT NULL default '0',
  `parent` int(10) unsigned default NULL,
  `lft` int(10) unsigned NOT NULL default '0',
  `rgt` int(10) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  KEY `child` (`child`),
  KEY `parent` (`parent`),
  KEY `jmp_tree` (`tree`)
) TYPE=MyISAM;

--
-- Dumping data for table `tree`
--


/*!40000 ALTER TABLE `tree` DISABLE KEYS */;
LOCK TABLES `tree` WRITE;
INSERT INTO `tree` VALUES (1,1,0,1,38,1),(1,7,9,5,6,3),(1,8,9,7,8,3),(1,9,1,2,37,2),(1,10,9,9,10,3),(1,11,9,11,12,3),(1,12,9,3,4,3),(1,14,9,13,18,3),(1,15,9,19,20,3),(1,16,9,21,22,3),(1,17,9,23,24,3),(1,18,9,25,26,3),(1,19,9,27,28,3),(1,20,9,29,30,3),(1,21,9,31,32,3),(1,22,9,33,34,3),(1,23,9,35,36,3),(1,24,14,14,17,4),(1,25,24,15,16,5);
UNLOCK TABLES;
/*!40000 ALTER TABLE `tree` ENABLE KEYS */;

--
-- Table structure for table `tst_active`
--

DROP TABLE IF EXISTS `tst_active`;
CREATE TABLE `tst_active` (
  `active_id` int(10) unsigned NOT NULL auto_increment,
  `user_fi` int(10) unsigned NOT NULL default '0',
  `test_fi` int(10) unsigned NOT NULL default '0',
  `sequence` text NOT NULL,
  `postponed` text,
  `lastindex` tinyint(4) NOT NULL default '1',
  `tries` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  `submitted` tinyint(3) unsigned default '0',
  `submittimestamp` datetime default NULL,
  PRIMARY KEY  (`active_id`),
  UNIQUE KEY `active_id` (`active_id`),
  KEY `active_id_2` (`active_id`),
  KEY `user_fi` (`user_fi`),
  KEY `test_fi` (`test_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `tst_active`
--


/*!40000 ALTER TABLE `tst_active` DISABLE KEYS */;
LOCK TABLES `tst_active` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_active` ENABLE KEYS */;

--
-- Table structure for table `tst_active_qst_sol_settings`
--

DROP TABLE IF EXISTS `tst_active_qst_sol_settings`;
CREATE TABLE `tst_active_qst_sol_settings` (
  `test_fi` int(10) unsigned NOT NULL default '0',
  `user_fi` int(10) unsigned NOT NULL default '0',
  `question_fi` int(10) unsigned NOT NULL default '0',
  `solved` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`test_fi`,`user_fi`,`question_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `tst_active_qst_sol_settings`
--


/*!40000 ALTER TABLE `tst_active_qst_sol_settings` DISABLE KEYS */;
LOCK TABLES `tst_active_qst_sol_settings` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_active_qst_sol_settings` ENABLE KEYS */;

--
-- Table structure for table `tst_eval_settings`
--

DROP TABLE IF EXISTS `tst_eval_settings`;
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
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`eval_settings_id`),
  KEY `user_fi` (`user_fi`)
) TYPE=MyISAM COMMENT='User settings for statistical evaluation tool';

--
-- Dumping data for table `tst_eval_settings`
--


/*!40000 ALTER TABLE `tst_eval_settings` DISABLE KEYS */;
LOCK TABLES `tst_eval_settings` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_eval_settings` ENABLE KEYS */;

--
-- Table structure for table `tst_eval_users`
--

DROP TABLE IF EXISTS `tst_eval_users`;
CREATE TABLE `tst_eval_users` (
  `test_fi` int(11) NOT NULL default '0',
  `evaluator_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  KEY `test_fi` (`test_fi`,`evaluator_fi`,`user_fi`)
) TYPE=MyISAM COMMENT='Contains the users someone has chosen for a statistical eval';

--
-- Dumping data for table `tst_eval_users`
--


/*!40000 ALTER TABLE `tst_eval_users` DISABLE KEYS */;
LOCK TABLES `tst_eval_users` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_eval_users` ENABLE KEYS */;

--
-- Table structure for table `tst_invited_user`
--

DROP TABLE IF EXISTS `tst_invited_user`;
CREATE TABLE `tst_invited_user` (
  `test_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  `clientip` varchar(15) default NULL,
  PRIMARY KEY  (`test_fi`,`user_fi`)
) TYPE=MyISAM;

--
-- Dumping data for table `tst_invited_user`
--


/*!40000 ALTER TABLE `tst_invited_user` DISABLE KEYS */;
LOCK TABLES `tst_invited_user` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_invited_user` ENABLE KEYS */;

--
-- Table structure for table `tst_mark`
--

DROP TABLE IF EXISTS `tst_mark`;
CREATE TABLE `tst_mark` (
  `mark_id` int(10) unsigned NOT NULL auto_increment,
  `test_fi` int(10) unsigned NOT NULL default '0',
  `short_name` varchar(15) NOT NULL default '',
  `official_name` varchar(50) NOT NULL default '',
  `minimum_level` double NOT NULL default '0',
  `passed` enum('0','1') NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`mark_id`),
  UNIQUE KEY `mark_id` (`mark_id`),
  KEY `mark_id_2` (`mark_id`),
  KEY `test_fi` (`test_fi`)
) TYPE=MyISAM COMMENT='Mark steps of mark schemas';

--
-- Dumping data for table `tst_mark`
--


/*!40000 ALTER TABLE `tst_mark` DISABLE KEYS */;
LOCK TABLES `tst_mark` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_mark` ENABLE KEYS */;

--
-- Table structure for table `tst_solutions`
--

DROP TABLE IF EXISTS `tst_solutions`;
CREATE TABLE `tst_solutions` (
  `solution_id` int(10) unsigned NOT NULL auto_increment,
  `user_fi` int(10) unsigned NOT NULL default '0',
  `test_fi` int(10) unsigned NOT NULL default '0',
  `question_fi` int(10) unsigned NOT NULL default '0',
  `value1` text,
  `value2` varchar(50) default NULL,
  `points` double default NULL,
  `pass` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`solution_id`),
  UNIQUE KEY `solution_id` (`solution_id`),
  KEY `solution_id_2` (`solution_id`),
  KEY `user_fi` (`user_fi`),
  KEY `test_fi` (`test_fi`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM COMMENT='Test and Assessment solutions';

--
-- Dumping data for table `tst_solutions`
--


/*!40000 ALTER TABLE `tst_solutions` DISABLE KEYS */;
LOCK TABLES `tst_solutions` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_solutions` ENABLE KEYS */;

--
-- Table structure for table `tst_test_question`
--

DROP TABLE IF EXISTS `tst_test_question`;
CREATE TABLE `tst_test_question` (
  `test_question_id` int(11) NOT NULL auto_increment,
  `test_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `sequence` int(10) unsigned NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`test_question_id`),
  KEY `test_fi` (`test_fi`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM COMMENT='Relation table for questions in tests';

--
-- Dumping data for table `tst_test_question`
--


/*!40000 ALTER TABLE `tst_test_question` DISABLE KEYS */;
LOCK TABLES `tst_test_question` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_test_question` ENABLE KEYS */;

--
-- Table structure for table `tst_test_random`
--

DROP TABLE IF EXISTS `tst_test_random`;
CREATE TABLE `tst_test_random` (
  `test_random_id` int(11) NOT NULL auto_increment,
  `test_fi` int(11) NOT NULL default '0',
  `questionpool_fi` int(11) NOT NULL default '0',
  `num_of_q` int(10) unsigned NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`test_random_id`),
  KEY `test_fi` (`test_fi`),
  KEY `questionpool_fi` (`questionpool_fi`)
) TYPE=MyISAM COMMENT='Questionpools taken for a random test';

--
-- Dumping data for table `tst_test_random`
--


/*!40000 ALTER TABLE `tst_test_random` DISABLE KEYS */;
LOCK TABLES `tst_test_random` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_test_random` ENABLE KEYS */;

--
-- Table structure for table `tst_test_random_question`
--

DROP TABLE IF EXISTS `tst_test_random_question`;
CREATE TABLE `tst_test_random_question` (
  `test_random_question_id` int(11) NOT NULL auto_increment,
  `test_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `sequence` int(10) unsigned NOT NULL default '0',
  `pass` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`test_random_question_id`),
  KEY `test_fi` (`test_fi`),
  KEY `user_fi` (`user_fi`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM COMMENT='Relation table for random questions in tests';

--
-- Dumping data for table `tst_test_random_question`
--


/*!40000 ALTER TABLE `tst_test_random_question` DISABLE KEYS */;
LOCK TABLES `tst_test_random_question` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_test_random_question` ENABLE KEYS */;

--
-- Table structure for table `tst_test_result`
--

DROP TABLE IF EXISTS `tst_test_result`;
CREATE TABLE `tst_test_result` (
  `test_result_id` int(10) unsigned NOT NULL auto_increment,
  `user_fi` int(10) unsigned NOT NULL default '0',
  `test_fi` int(10) unsigned NOT NULL default '0',
  `question_fi` int(10) unsigned NOT NULL default '0',
  `points` double NOT NULL default '0',
  `pass` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`test_result_id`),
  UNIQUE KEY `user_fi` (`user_fi`,`test_fi`,`question_fi`,`pass`),
  KEY `test_fi` (`test_fi`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM COMMENT='Test and Assessment user results for test questions';

--
-- Dumping data for table `tst_test_result`
--


/*!40000 ALTER TABLE `tst_test_result` DISABLE KEYS */;
LOCK TABLES `tst_test_result` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_test_result` ENABLE KEYS */;

--
-- Table structure for table `tst_test_type`
--

DROP TABLE IF EXISTS `tst_test_type`;
CREATE TABLE `tst_test_type` (
  `test_type_id` int(10) unsigned NOT NULL auto_increment,
  `type_tag` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`test_type_id`),
  UNIQUE KEY `test_type_id` (`test_type_id`),
  KEY `test_type_id_2` (`test_type_id`)
) TYPE=MyISAM COMMENT='ILIAS 3 Assessment Test types';

--
-- Dumping data for table `tst_test_type`
--


/*!40000 ALTER TABLE `tst_test_type` DISABLE KEYS */;
LOCK TABLES `tst_test_type` WRITE;
INSERT INTO `tst_test_type` VALUES (1,'tt_assessment'),(2,'tt_self_assessment'),(4,'tt_online_exam'),(5,'tt_varying_randomtest');
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_test_type` ENABLE KEYS */;

--
-- Table structure for table `tst_tests`
--

DROP TABLE IF EXISTS `tst_tests`;
CREATE TABLE `tst_tests` (
  `test_id` int(10) unsigned NOT NULL auto_increment,
  `obj_fi` int(11) NOT NULL default '0',
  `author` varchar(50) NOT NULL default '',
  `test_type_fi` int(10) unsigned NOT NULL default '0',
  `introduction` text,
  `sequence_settings` tinyint(3) unsigned NOT NULL default '0',
  `score_reporting` tinyint(3) unsigned NOT NULL default '0',
  `nr_of_tries` tinyint(3) unsigned NOT NULL default '0',
  `hide_previous_results` enum('0','1') NOT NULL default '0',
  `hide_title_points` enum('0','1') NOT NULL default '0',
  `processing_time` time default NULL,
  `enable_processing_time` enum('0','1') NOT NULL default '0',
  `reporting_date` varchar(14) default NULL,
  `starting_time` varchar(14) default NULL,
  `ending_time` varchar(14) default NULL,
  `ects_output` enum('0','1') NOT NULL default '0',
  `ects_fx` float default NULL,
  `complete` enum('0','1') NOT NULL default '1',
  `created` varchar(14) default NULL,
  `TIMESTAMP` timestamp NOT NULL,
  `ects_a` double NOT NULL default '90',
  `ects_b` double NOT NULL default '65',
  `ects_c` double NOT NULL default '35',
  `ects_d` double NOT NULL default '10',
  `ects_e` double NOT NULL default '0',
  `random_test` enum('0','1') NOT NULL default '0',
  `random_question_count` int(11) default NULL,
  `count_system` enum('0','1') NOT NULL default '0',
  `mc_scoring` enum('0','1') NOT NULL default '0',
  `pass_scoring` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`test_id`),
  UNIQUE KEY `test_id` (`test_id`),
  KEY `test_id_2` (`test_id`),
  KEY `obj_fi` (`obj_fi`),
  KEY `test_type_fi` (`test_type_fi`),
  FULLTEXT KEY `introduction` (`introduction`)
) TYPE=MyISAM COMMENT='Tests in ILIAS Assessment';

--
-- Dumping data for table `tst_tests`
--


/*!40000 ALTER TABLE `tst_tests` DISABLE KEYS */;
LOCK TABLES `tst_tests` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_tests` ENABLE KEYS */;

--
-- Table structure for table `tst_times`
--

DROP TABLE IF EXISTS `tst_times`;
CREATE TABLE `tst_times` (
  `times_id` int(11) NOT NULL auto_increment,
  `active_fi` int(11) NOT NULL default '0',
  `started` datetime NOT NULL default '0000-00-00 00:00:00',
  `finished` datetime NOT NULL default '0000-00-00 00:00:00',
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`times_id`),
  KEY `active_fi` (`active_fi`)
) TYPE=MyISAM COMMENT='Editing times of an assessment test';

--
-- Dumping data for table `tst_times`
--


/*!40000 ALTER TABLE `tst_times` DISABLE KEYS */;
LOCK TABLES `tst_times` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `tst_times` ENABLE KEYS */;

--
-- Table structure for table `user_defined_field_definition`
--

DROP TABLE IF EXISTS `user_defined_field_definition`;
CREATE TABLE `user_defined_field_definition` (
  `field_id` int(3) NOT NULL auto_increment,
  `field_name` tinytext NOT NULL,
  `field_type` tinyint(1) NOT NULL default '0',
  `field_values` text NOT NULL,
  `visible` tinyint(1) NOT NULL default '0',
  `changeable` tinyint(1) NOT NULL default '0',
  `required` tinyint(1) NOT NULL default '0',
  `searchable` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`field_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `user_defined_field_definition`
--


/*!40000 ALTER TABLE `user_defined_field_definition` DISABLE KEYS */;
LOCK TABLES `user_defined_field_definition` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `user_defined_field_definition` ENABLE KEYS */;

--
-- Table structure for table `usr_data`
--

DROP TABLE IF EXISTS `usr_data`;
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
  `agree_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `ilinc_id` int(10) unsigned default NULL,
  `ilinc_login` varchar(40) default NULL,
  `ilinc_passwd` varchar(40) default NULL,
  `client_ip` varchar(15) default NULL,
  `auth_mode` enum('default','local','ldap','radius','shibboleth','script') NOT NULL default 'default',
  PRIMARY KEY  (`usr_id`),
  KEY `login` (`login`,`passwd`)
) TYPE=MyISAM;

--
-- Dumping data for table `usr_data`
--


/*!40000 ALTER TABLE `usr_data` DISABLE KEYS */;
LOCK TABLES `usr_data` WRITE;
INSERT INTO `usr_data` VALUES (6,'root','dfa8327f5bfa4c672a04f9b38e348a70','root','user','','m','ilias@yourserver.com','','','','','','','2005-07-20 15:11:40','2003-09-30 19:50:01','0000-00-00 00:00:00','','','','','','',7,1,0,0,0,'',NULL,1,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,NULL,'default'),(13,'anonymous','294de3557d9d00b3d2d8a1e6aab028cf','anonymous','anonymous','','m','nomail',NULL,NULL,NULL,NULL,NULL,'','2003-08-15 11:03:36','2003-08-15 10:07:30','2003-08-15 10:07:30','','','','','','',7,1,0,0,0,'',NULL,1,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,NULL,NULL,NULL,'local');
UNLOCK TABLES;
/*!40000 ALTER TABLE `usr_data` ENABLE KEYS */;

--
-- Table structure for table `usr_defined_data`
--

DROP TABLE IF EXISTS `usr_defined_data`;
CREATE TABLE `usr_defined_data` (
  `usr_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `usr_defined_data`
--


/*!40000 ALTER TABLE `usr_defined_data` DISABLE KEYS */;
LOCK TABLES `usr_defined_data` WRITE;
INSERT INTO `usr_defined_data` VALUES (6),(13);
UNLOCK TABLES;
/*!40000 ALTER TABLE `usr_defined_data` ENABLE KEYS */;

--
-- Table structure for table `usr_pref`
--

DROP TABLE IF EXISTS `usr_pref`;
CREATE TABLE `usr_pref` (
  `usr_id` int(10) unsigned NOT NULL default '0',
  `keyword` char(40) NOT NULL default '',
  `value` char(40) default NULL,
  PRIMARY KEY  (`usr_id`,`keyword`)
) TYPE=MyISAM;

--
-- Dumping data for table `usr_pref`
--


/*!40000 ALTER TABLE `usr_pref` DISABLE KEYS */;
LOCK TABLES `usr_pref` WRITE;
INSERT INTO `usr_pref` VALUES (6,'style','delos'),(6,'skin','default'),(6,'public_zip','n'),(6,'public_upload','n'),(6,'public_street','n'),(6,'public_profile','n'),(6,'public_phone','n'),(6,'public_institution','n'),(6,'public_hobby','n'),(6,'public_email','n'),(6,'public_country','n'),(6,'public_city','n'),(6,'language','en'),(6,'show_users_online','y'),(13,'show_users_online','y');
UNLOCK TABLES;
/*!40000 ALTER TABLE `usr_pref` ENABLE KEYS */;

--
-- Table structure for table `usr_pwassist`
--

DROP TABLE IF EXISTS `usr_pwassist`;
CREATE TABLE `usr_pwassist` (
  `pwassist_id` varchar(32) NOT NULL default '',
  `expires` int(11) NOT NULL default '0',
  `ctime` int(11) NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`pwassist_id`),
  UNIQUE KEY `user_id` (`user_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `usr_pwassist`
--


/*!40000 ALTER TABLE `usr_pwassist` DISABLE KEYS */;
LOCK TABLES `usr_pwassist` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `usr_pwassist` ENABLE KEYS */;

--
-- Table structure for table `usr_search`
--

DROP TABLE IF EXISTS `usr_search`;
CREATE TABLE `usr_search` (
  `usr_id` int(11) NOT NULL default '0',
  `search_result` text,
  `search_type` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`search_type`)
) TYPE=MyISAM;

--
-- Dumping data for table `usr_search`
--


/*!40000 ALTER TABLE `usr_search` DISABLE KEYS */;
LOCK TABLES `usr_search` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `usr_search` ENABLE KEYS */;

--
-- Table structure for table `usr_session`
--

DROP TABLE IF EXISTS `usr_session`;
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

--
-- Dumping data for table `usr_session`
--


/*!40000 ALTER TABLE `usr_session` DISABLE KEYS */;
LOCK TABLES `usr_session` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `usr_session` ENABLE KEYS */;

--
-- Table structure for table `ut_access`
--

DROP TABLE IF EXISTS `ut_access`;
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

--
-- Dumping data for table `ut_access`
--


/*!40000 ALTER TABLE `ut_access` DISABLE KEYS */;
LOCK TABLES `ut_access` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ut_access` ENABLE KEYS */;

--
-- Table structure for table `ut_learning_progress`
--

DROP TABLE IF EXISTS `ut_learning_progress`;
CREATE TABLE `ut_learning_progress` (
  `lp_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `obj_type` char(4) NOT NULL default '',
  `obj_id` int(11) NOT NULL default '0',
  `spent_time` int(10) NOT NULL default '0',
  `access_time` int(10) NOT NULL default '0',
  `visits` int(4) NOT NULL default '0',
  PRIMARY KEY  (`lp_id`),
  KEY `user_obj` (`user_id`,`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `ut_learning_progress`
--


/*!40000 ALTER TABLE `ut_learning_progress` DISABLE KEYS */;
LOCK TABLES `ut_learning_progress` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ut_learning_progress` ENABLE KEYS */;

--
-- Table structure for table `ut_lp_collections`
--

DROP TABLE IF EXISTS `ut_lp_collections`;
CREATE TABLE `ut_lp_collections` (
  `obj_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  KEY `obj_id` (`obj_id`,`item_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `ut_lp_collections`
--


/*!40000 ALTER TABLE `ut_lp_collections` DISABLE KEYS */;
LOCK TABLES `ut_lp_collections` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ut_lp_collections` ENABLE KEYS */;

--
-- Table structure for table `ut_lp_filter`
--

DROP TABLE IF EXISTS `ut_lp_filter`;
CREATE TABLE `ut_lp_filter` (
  `usr_id` int(11) NOT NULL default '0',
  `filter_type` varchar(4) NOT NULL default '',
  `root_node` int(11) NOT NULL default '0',
  `hidden` text NOT NULL,
  `query_string` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `ut_lp_filter`
--


/*!40000 ALTER TABLE `ut_lp_filter` DISABLE KEYS */;
LOCK TABLES `ut_lp_filter` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ut_lp_filter` ENABLE KEYS */;

--
-- Table structure for table `ut_lp_marks`
--

DROP TABLE IF EXISTS `ut_lp_marks`;
CREATE TABLE `ut_lp_marks` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `completed` int(1) NOT NULL default '0',
  `mark` char(32) NOT NULL default '',
  `comment` char(255) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`usr_id`),
  KEY `obj_usr` (`obj_id`,`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `ut_lp_marks`
--


/*!40000 ALTER TABLE `ut_lp_marks` DISABLE KEYS */;
LOCK TABLES `ut_lp_marks` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ut_lp_marks` ENABLE KEYS */;

--
-- Table structure for table `ut_lp_settings`
--

DROP TABLE IF EXISTS `ut_lp_settings`;
CREATE TABLE `ut_lp_settings` (
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` char(4) NOT NULL default '',
  `mode` tinyint(1) NOT NULL default '0',
  `visits` int(4) default '0',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `ut_lp_settings`
--


/*!40000 ALTER TABLE `ut_lp_settings` DISABLE KEYS */;
LOCK TABLES `ut_lp_settings` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ut_lp_settings` ENABLE KEYS */;

--
-- Table structure for table `ut_online`
--

DROP TABLE IF EXISTS `ut_online`;
CREATE TABLE `ut_online` (
  `usr_id` int(11) NOT NULL default '0',
  `online_time` int(11) NOT NULL default '0',
  `access_time` int(10) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `ut_online`
--


/*!40000 ALTER TABLE `ut_online` DISABLE KEYS */;
LOCK TABLES `ut_online` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `ut_online` ENABLE KEYS */;

--
-- Table structure for table `webr_items`
--

DROP TABLE IF EXISTS `webr_items`;
CREATE TABLE `webr_items` (
  `link_id` int(11) NOT NULL auto_increment,
  `webr_id` int(11) NOT NULL default '0',
  `title` varchar(127) default NULL,
  `description` text NOT NULL,
  `target` text,
  `active` tinyint(1) default NULL,
  `disable_check` tinyint(1) default NULL,
  `create_date` int(11) NOT NULL default '0',
  `last_update` int(11) NOT NULL default '0',
  `last_check` int(11) default NULL,
  `valid` tinyint(1) NOT NULL default '0',
  KEY `link_id` (`link_id`,`webr_id`),
  FULLTEXT KEY `title` (`title`)
) TYPE=MyISAM;

--
-- Dumping data for table `webr_items`
--


/*!40000 ALTER TABLE `webr_items` DISABLE KEYS */;
LOCK TABLES `webr_items` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `webr_items` ENABLE KEYS */;

--
-- Table structure for table `webr_params`
--

DROP TABLE IF EXISTS `webr_params`;
CREATE TABLE `webr_params` (
  `param_id` int(11) NOT NULL auto_increment,
  `webr_id` int(11) NOT NULL default '0',
  `link_id` int(11) NOT NULL default '0',
  `name` char(128) NOT NULL default '',
  `value` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`param_id`),
  KEY `link_id` (`link_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `webr_params`
--


/*!40000 ALTER TABLE `webr_params` DISABLE KEYS */;
LOCK TABLES `webr_params` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `webr_params` ENABLE KEYS */;

--
-- Table structure for table `xml_attribute_idx`
--

DROP TABLE IF EXISTS `xml_attribute_idx`;
CREATE TABLE `xml_attribute_idx` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `value_id` smallint(5) unsigned NOT NULL default '0',
  KEY `node_id` (`node_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_attribute_idx`
--


/*!40000 ALTER TABLE `xml_attribute_idx` DISABLE KEYS */;
LOCK TABLES `xml_attribute_idx` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_attribute_idx` ENABLE KEYS */;

--
-- Table structure for table `xml_attribute_name`
--

DROP TABLE IF EXISTS `xml_attribute_name`;
CREATE TABLE `xml_attribute_name` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `attribute` char(32) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`),
  UNIQUE KEY `attribute` (`attribute`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_attribute_name`
--


/*!40000 ALTER TABLE `xml_attribute_name` DISABLE KEYS */;
LOCK TABLES `xml_attribute_name` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_attribute_name` ENABLE KEYS */;

--
-- Table structure for table `xml_attribute_namespace`
--

DROP TABLE IF EXISTS `xml_attribute_namespace`;
CREATE TABLE `xml_attribute_namespace` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `namespace` char(64) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_attribute_namespace`
--


/*!40000 ALTER TABLE `xml_attribute_namespace` DISABLE KEYS */;
LOCK TABLES `xml_attribute_namespace` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_attribute_namespace` ENABLE KEYS */;

--
-- Table structure for table `xml_attribute_value`
--

DROP TABLE IF EXISTS `xml_attribute_value`;
CREATE TABLE `xml_attribute_value` (
  `value_id` smallint(5) unsigned NOT NULL auto_increment,
  `value` char(32) NOT NULL default '0',
  PRIMARY KEY  (`value_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_attribute_value`
--


/*!40000 ALTER TABLE `xml_attribute_value` DISABLE KEYS */;
LOCK TABLES `xml_attribute_value` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_attribute_value` ENABLE KEYS */;

--
-- Table structure for table `xml_cdata`
--

DROP TABLE IF EXISTS `xml_cdata`;
CREATE TABLE `xml_cdata` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `cdata` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_cdata`
--


/*!40000 ALTER TABLE `xml_cdata` DISABLE KEYS */;
LOCK TABLES `xml_cdata` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_cdata` ENABLE KEYS */;

--
-- Table structure for table `xml_comment`
--

DROP TABLE IF EXISTS `xml_comment`;
CREATE TABLE `xml_comment` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `comment` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_comment`
--


/*!40000 ALTER TABLE `xml_comment` DISABLE KEYS */;
LOCK TABLES `xml_comment` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_comment` ENABLE KEYS */;

--
-- Table structure for table `xml_element_idx`
--

DROP TABLE IF EXISTS `xml_element_idx`;
CREATE TABLE `xml_element_idx` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `element_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_element_idx`
--


/*!40000 ALTER TABLE `xml_element_idx` DISABLE KEYS */;
LOCK TABLES `xml_element_idx` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_element_idx` ENABLE KEYS */;

--
-- Table structure for table `xml_element_name`
--

DROP TABLE IF EXISTS `xml_element_name`;
CREATE TABLE `xml_element_name` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `element` char(32) NOT NULL default '',
  PRIMARY KEY  (`element_id`),
  UNIQUE KEY `element` (`element`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_element_name`
--


/*!40000 ALTER TABLE `xml_element_name` DISABLE KEYS */;
LOCK TABLES `xml_element_name` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_element_name` ENABLE KEYS */;

--
-- Table structure for table `xml_element_namespace`
--

DROP TABLE IF EXISTS `xml_element_namespace`;
CREATE TABLE `xml_element_namespace` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `namespace` char(64) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_element_namespace`
--


/*!40000 ALTER TABLE `xml_element_namespace` DISABLE KEYS */;
LOCK TABLES `xml_element_namespace` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_element_namespace` ENABLE KEYS */;

--
-- Table structure for table `xml_entity_reference`
--

DROP TABLE IF EXISTS `xml_entity_reference`;
CREATE TABLE `xml_entity_reference` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `entity_reference` char(128) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_entity_reference`
--


/*!40000 ALTER TABLE `xml_entity_reference` DISABLE KEYS */;
LOCK TABLES `xml_entity_reference` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_entity_reference` ENABLE KEYS */;

--
-- Table structure for table `xml_node_type`
--

DROP TABLE IF EXISTS `xml_node_type`;
CREATE TABLE `xml_node_type` (
  `node_type_id` int(11) NOT NULL auto_increment,
  `description` varchar(50) default NULL,
  `lft_delimiter` varchar(10) default NULL,
  `rgt_delimiter` varchar(10) default NULL,
  PRIMARY KEY  (`node_type_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_node_type`
--


/*!40000 ALTER TABLE `xml_node_type` DISABLE KEYS */;
LOCK TABLES `xml_node_type` WRITE;
INSERT INTO `xml_node_type` VALUES (1,'ELEMENT_NODE','<','>'),(2,'ATTRIBUTE_NODE(not used)','\"','\"'),(3,'TEXT_NODE',NULL,NULL),(5,'ENTITY_REF_NODE','&',';'),(4,'CDATA_SECTION_NODE','<![CDATA[',']]>'),(8,'COMMENT_NODE','<!--','-->'),(9,'DOCUMENT_NODE',NULL,NULL),(10,'DOCUMENT_TYPE_NODE',NULL,NULL),(6,'ENTITY_NODE','&',';');
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_node_type` ENABLE KEYS */;

--
-- Table structure for table `xml_object`
--

DROP TABLE IF EXISTS `xml_object`;
CREATE TABLE `xml_object` (
  `ID` int(11) NOT NULL auto_increment,
  `version` varchar(5) NOT NULL default '',
  `encoding` varchar(40) default NULL,
  `charset` varchar(40) default NULL,
  `TIMESTAMP` timestamp NOT NULL,
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Master Table for XML objects';

--
-- Dumping data for table `xml_object`
--


/*!40000 ALTER TABLE `xml_object` DISABLE KEYS */;
LOCK TABLES `xml_object` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_object` ENABLE KEYS */;

--
-- Table structure for table `xml_pi_data`
--

DROP TABLE IF EXISTS `xml_pi_data`;
CREATE TABLE `xml_pi_data` (
  `leaf_id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `leaf_text` text NOT NULL,
  PRIMARY KEY  (`leaf_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_pi_data`
--


/*!40000 ALTER TABLE `xml_pi_data` DISABLE KEYS */;
LOCK TABLES `xml_pi_data` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_pi_data` ENABLE KEYS */;

--
-- Table structure for table `xml_pi_target`
--

DROP TABLE IF EXISTS `xml_pi_target`;
CREATE TABLE `xml_pi_target` (
  `leaf_id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `leaf_text` text NOT NULL,
  PRIMARY KEY  (`leaf_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_pi_target`
--


/*!40000 ALTER TABLE `xml_pi_target` DISABLE KEYS */;
LOCK TABLES `xml_pi_target` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_pi_target` ENABLE KEYS */;

--
-- Table structure for table `xml_text`
--

DROP TABLE IF EXISTS `xml_text`;
CREATE TABLE `xml_text` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `textnode` text NOT NULL,
  PRIMARY KEY  (`node_id`),
  FULLTEXT KEY `textnode` (`textnode`)
) TYPE=MyISAM;

--
-- Dumping data for table `xml_text`
--


/*!40000 ALTER TABLE `xml_text` DISABLE KEYS */;
LOCK TABLES `xml_text` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_text` ENABLE KEYS */;

--
-- Table structure for table `xml_tree`
--

DROP TABLE IF EXISTS `xml_tree`;
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

--
-- Dumping data for table `xml_tree`
--


/*!40000 ALTER TABLE `xml_tree` DISABLE KEYS */;
LOCK TABLES `xml_tree` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xml_tree` ENABLE KEYS */;

--
-- Table structure for table `xmlnestedset`
--

DROP TABLE IF EXISTS `xmlnestedset`;
CREATE TABLE `xmlnestedset` (
  `ns_book_fk` int(11) NOT NULL default '0',
  `ns_type` char(50) NOT NULL default '',
  `ns_tag_fk` int(11) default NULL,
  `ns_l` int(11) default NULL,
  `ns_r` int(11) default NULL,
  KEY `ns_tag_fk` (`ns_tag_fk`),
  KEY `ns_book_fk` (`ns_book_fk`)
) TYPE=MyISAM;

--
-- Dumping data for table `xmlnestedset`
--


/*!40000 ALTER TABLE `xmlnestedset` DISABLE KEYS */;
LOCK TABLES `xmlnestedset` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xmlnestedset` ENABLE KEYS */;

--
-- Table structure for table `xmlparam`
--

DROP TABLE IF EXISTS `xmlparam`;
CREATE TABLE `xmlparam` (
  `tag_fk` int(11) NOT NULL default '0',
  `param_name` char(50) NOT NULL default '',
  `param_value` char(255) NOT NULL default '',
  KEY `tag_fk` (`tag_fk`)
) TYPE=MyISAM;

--
-- Dumping data for table `xmlparam`
--


/*!40000 ALTER TABLE `xmlparam` DISABLE KEYS */;
LOCK TABLES `xmlparam` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xmlparam` ENABLE KEYS */;

--
-- Table structure for table `xmltags`
--

DROP TABLE IF EXISTS `xmltags`;
CREATE TABLE `xmltags` (
  `tag_pk` int(11) NOT NULL auto_increment,
  `tag_depth` int(11) NOT NULL default '0',
  `tag_name` char(50) NOT NULL default '',
  PRIMARY KEY  (`tag_pk`)
) TYPE=MyISAM;

--
-- Dumping data for table `xmltags`
--


/*!40000 ALTER TABLE `xmltags` DISABLE KEYS */;
LOCK TABLES `xmltags` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xmltags` ENABLE KEYS */;

--
-- Table structure for table `xmlvalue`
--

DROP TABLE IF EXISTS `xmlvalue`;
CREATE TABLE `xmlvalue` (
  `tag_value_pk` int(11) NOT NULL auto_increment,
  `tag_fk` int(11) NOT NULL default '0',
  `tag_value` text NOT NULL,
  PRIMARY KEY  (`tag_value_pk`),
  KEY `tag_fk` (`tag_fk`),
  FULLTEXT KEY `tag_value` (`tag_value`)
) TYPE=MyISAM;

--
-- Dumping data for table `xmlvalue`
--


/*!40000 ALTER TABLE `xmlvalue` DISABLE KEYS */;
LOCK TABLES `xmlvalue` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `xmlvalue` ENABLE KEYS */;

