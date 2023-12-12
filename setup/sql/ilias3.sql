-- MariaDB dump 10.19  Distrib 10.6.12-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ilias_release
-- ------------------------------------------------------
-- Server version	10.6.12-MariaDB-0ubuntu0.22.04.1

--
-- Table structure for table `acc_access_key`
--

CREATE TABLE `acc_access_key` (
  `lang_key` char(2) NOT NULL DEFAULT '',
  `function_id` int(11) NOT NULL DEFAULT 0,
  `access_key` char(1) DEFAULT NULL,
  PRIMARY KEY (`lang_key`,`function_id`)
) ;

--
-- Dumping data for table `acc_access_key`
--


--
-- Table structure for table `acc_cache`
--

CREATE TABLE `acc_cache` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL DEFAULT 0,
  `result` longtext DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ;

--
-- Dumping data for table `acc_cache`
--


--
-- Table structure for table `acc_criterion_to_doc`
--

CREATE TABLE `acc_criterion_to_doc` (
  `id` int(11) NOT NULL DEFAULT 0,
  `doc_id` int(11) NOT NULL DEFAULT 0,
  `criterion_id` varchar(50) NOT NULL,
  `criterion_value` varchar(255) DEFAULT NULL,
  `assigned_ts` int(11) NOT NULL DEFAULT 0,
  `modification_ts` int(11) NOT NULL DEFAULT 0,
  `owner_usr_id` int(11) NOT NULL DEFAULT 0,
  `last_modified_usr_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `acc_criterion_to_doc`
--


--
-- Table structure for table `acc_criterion_to_doc_seq`
--

CREATE TABLE `acc_criterion_to_doc_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `acc_criterion_to_doc_seq`
--


--
-- Table structure for table `acc_documents`
--

CREATE TABLE `acc_documents` (
  `id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `creation_ts` int(11) NOT NULL DEFAULT 0,
  `modification_ts` int(11) NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `owner_usr_id` int(11) NOT NULL DEFAULT 0,
  `last_modified_usr_id` int(11) NOT NULL DEFAULT 0,
  `text` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `acc_documents`
--


--
-- Table structure for table `acc_documents_seq`
--

CREATE TABLE `acc_documents_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `acc_documents_seq`
--


--
-- Table structure for table `acc_user_access_key`
--

CREATE TABLE `acc_user_access_key` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `function_id` smallint(6) NOT NULL DEFAULT 0,
  `access_key` char(1) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`function_id`)
) ;

--
-- Dumping data for table `acc_user_access_key`
--


--
-- Table structure for table `acl_ws`
--

CREATE TABLE `acl_ws` (
  `node_id` int(11) NOT NULL DEFAULT 0,
  `object_id` int(11) NOT NULL DEFAULT 0,
  `extended_data` varchar(200) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`node_id`,`object_id`)
) ;

--
-- Dumping data for table `acl_ws`
--


--
-- Table structure for table `addressbook_mlist`
--

CREATE TABLE `addressbook_mlist` (
  `ml_id` bigint(20) NOT NULL DEFAULT 0,
  `user_id` bigint(20) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `createdate` datetime DEFAULT NULL,
  `changedate` datetime DEFAULT NULL,
  `lmode` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ml_id`),
  KEY `i1_idx` (`user_id`)
) ;

--
-- Dumping data for table `addressbook_mlist`
--


--
-- Table structure for table `addressbook_mlist_ass`
--

CREATE TABLE `addressbook_mlist_ass` (
  `a_id` bigint(20) NOT NULL DEFAULT 0,
  `ml_id` bigint(20) NOT NULL DEFAULT 0,
  `usr_id` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`a_id`),
  KEY `i1_idx` (`ml_id`),
  KEY `i2_idx` (`usr_id`)
) ;

--
-- Dumping data for table `addressbook_mlist_ass`
--


--
-- Table structure for table `addressbook_mlist_ass_seq`
--

CREATE TABLE `addressbook_mlist_ass_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `addressbook_mlist_ass_seq`
--


--
-- Table structure for table `addressbook_mlist_seq`
--

CREATE TABLE `addressbook_mlist_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `addressbook_mlist_seq`
--


--
-- Table structure for table `adl_shared_data`
--

CREATE TABLE `adl_shared_data` (
  `slm_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `target_id` varchar(4000) NOT NULL DEFAULT '',
  `store` longtext DEFAULT NULL,
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cp_node_id`,`user_id`)
) ;

--
-- Dumping data for table `adl_shared_data`
--


--
-- Table structure for table `adm_set_templ_hide_tab`
--

CREATE TABLE `adm_set_templ_hide_tab` (
  `template_id` int(11) NOT NULL DEFAULT 0,
  `tab_id` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`template_id`,`tab_id`)
) ;

--
-- Dumping data for table `adm_set_templ_hide_tab`
--


--
-- Table structure for table `adm_set_templ_value`
--

CREATE TABLE `adm_set_templ_value` (
  `template_id` int(11) NOT NULL DEFAULT 0,
  `setting` varchar(40) NOT NULL DEFAULT '',
  `value` varchar(4000) DEFAULT NULL,
  `hide` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`template_id`,`setting`)
) ;

--
-- Dumping data for table `adm_set_templ_value`
--

INSERT INTO `adm_set_templ_value` VALUES (1,'pass_scoring','0',1);
INSERT INTO `adm_set_templ_value` VALUES (2,'pass_scoring','0',1);

--
-- Table structure for table `adm_settings_template`
--

CREATE TABLE `adm_settings_template` (
  `id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(5) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` longtext DEFAULT NULL,
  `auto_generated` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `adm_settings_template`
--

INSERT INTO `adm_settings_template` VALUES (1,'tst','il_astpl_loc_initial','il_astpl_loc_initial_desc',1);
INSERT INTO `adm_settings_template` VALUES (2,'tst','il_astpl_loc_qualified','il_astpl_loc_qualified_desc',1);

--
-- Table structure for table `adm_settings_template_seq`
--

CREATE TABLE `adm_settings_template_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=3;

--
-- Dumping data for table `adm_settings_template_seq`
--

INSERT INTO `adm_settings_template_seq` VALUES (2);

--
-- Table structure for table `adv_md_field_int`
--

CREATE TABLE `adv_md_field_int` (
  `field_id` int(11) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `lang_code` varchar(5) NOT NULL,
  PRIMARY KEY (`field_id`,`lang_code`)
) ;

--
-- Dumping data for table `adv_md_field_int`
--


--
-- Table structure for table `adv_md_obj_rec_select`
--

CREATE TABLE `adv_md_obj_rec_select` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `rec_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`sub_type`,`rec_id`)
) ;

--
-- Dumping data for table `adv_md_obj_rec_select`
--


--
-- Table structure for table `adv_md_record`
--

CREATE TABLE `adv_md_record` (
  `record_id` int(11) NOT NULL DEFAULT 0,
  `import_id` varchar(64) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 0,
  `title` varchar(128) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `parent_obj` int(11) DEFAULT NULL,
  `gpos` int(11) DEFAULT NULL,
  `lang_default` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`record_id`)
) ;

--
-- Dumping data for table `adv_md_record`
--


--
-- Table structure for table `adv_md_record_int`
--

CREATE TABLE `adv_md_record_int` (
  `record_id` int(11) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `lang_code` varchar(5) NOT NULL,
  PRIMARY KEY (`record_id`,`lang_code`)
) ;

--
-- Dumping data for table `adv_md_record_int`
--


--
-- Table structure for table `adv_md_record_obj_ord`
--

CREATE TABLE `adv_md_record_obj_ord` (
  `record_id` int(11) NOT NULL,
  `obj_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`record_id`,`obj_id`)
) ;

--
-- Dumping data for table `adv_md_record_obj_ord`
--


--
-- Table structure for table `adv_md_record_objs`
--

CREATE TABLE `adv_md_record_objs` (
  `record_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` char(6) NOT NULL DEFAULT '',
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `optional` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`record_id`,`obj_type`,`sub_type`)
) ;

--
-- Dumping data for table `adv_md_record_objs`
--


--
-- Table structure for table `adv_md_record_scope`
--

CREATE TABLE `adv_md_record_scope` (
  `scope_id` int(11) NOT NULL DEFAULT 0,
  `record_id` int(11) NOT NULL,
  `ref_id` int(11) NOT NULL,
  PRIMARY KEY (`scope_id`)
) ;

--
-- Dumping data for table `adv_md_record_scope`
--


--
-- Table structure for table `adv_md_record_scope_seq`
--

CREATE TABLE `adv_md_record_scope_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `adv_md_record_scope_seq`
--


--
-- Table structure for table `adv_md_record_seq`
--

CREATE TABLE `adv_md_record_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `adv_md_record_seq`
--


--
-- Table structure for table `adv_md_substitutions`
--

CREATE TABLE `adv_md_substitutions` (
  `obj_type` varchar(4) NOT NULL DEFAULT ' ',
  `substitution` longtext DEFAULT NULL,
  `hide_description` tinyint(4) NOT NULL DEFAULT 0,
  `hide_field_names` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_type`)
) ;

--
-- Dumping data for table `adv_md_substitutions`
--


--
-- Table structure for table `adv_md_values_date`
--

CREATE TABLE `adv_md_values_date` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value` date DEFAULT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`sub_type`,`sub_id`,`field_id`)
) ;

--
-- Dumping data for table `adv_md_values_date`
--


--
-- Table structure for table `adv_md_values_datetime`
--

CREATE TABLE `adv_md_values_datetime` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value` datetime DEFAULT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`sub_type`,`sub_id`,`field_id`)
) ;

--
-- Dumping data for table `adv_md_values_datetime`
--


--
-- Table structure for table `adv_md_values_enum`
--

CREATE TABLE `adv_md_values_enum` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  `value_index` varchar(16) NOT NULL,
  PRIMARY KEY (`obj_id`,`sub_type`,`sub_id`,`field_id`,`value_index`)
) ;

--
-- Dumping data for table `adv_md_values_enum`
--


--
-- Table structure for table `adv_md_values_extlink`
--

CREATE TABLE `adv_md_values_extlink` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value` varchar(500) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`sub_type`,`sub_id`,`field_id`)
) ;

--
-- Dumping data for table `adv_md_values_extlink`
--


--
-- Table structure for table `adv_md_values_float`
--

CREATE TABLE `adv_md_values_float` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value` double DEFAULT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`sub_type`,`sub_id`,`field_id`)
) ;

--
-- Dumping data for table `adv_md_values_float`
--


--
-- Table structure for table `adv_md_values_int`
--

CREATE TABLE `adv_md_values_int` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value` int(11) DEFAULT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`sub_type`,`sub_id`,`field_id`)
) ;

--
-- Dumping data for table `adv_md_values_int`
--


--
-- Table structure for table `adv_md_values_intlink`
--

CREATE TABLE `adv_md_values_intlink` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value` int(11) NOT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`sub_type`,`sub_id`,`field_id`)
) ;

--
-- Dumping data for table `adv_md_values_intlink`
--


--
-- Table structure for table `adv_md_values_location`
--

CREATE TABLE `adv_md_values_location` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `loc_lat` double DEFAULT NULL,
  `loc_long` double DEFAULT NULL,
  `loc_zoom` tinyint(4) DEFAULT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`sub_type`,`sub_id`,`field_id`)
) ;

--
-- Dumping data for table `adv_md_values_location`
--


--
-- Table structure for table `adv_md_values_ltext`
--

CREATE TABLE `adv_md_values_ltext` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value_index` varchar(16) NOT NULL,
  `value` varchar(4000) DEFAULT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`sub_type`,`sub_id`,`field_id`,`value_index`)
) ;

--
-- Dumping data for table `adv_md_values_ltext`
--


--
-- Table structure for table `adv_md_values_text`
--

CREATE TABLE `adv_md_values_text` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value` varchar(4000) DEFAULT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  `sub_type` varchar(10) NOT NULL DEFAULT '-',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`field_id`,`sub_type`,`sub_id`),
  KEY `i1_idx` (`obj_id`)
) ;

--
-- Dumping data for table `adv_md_values_text`
--


--
-- Table structure for table `adv_mdf_definition`
--

CREATE TABLE `adv_mdf_definition` (
  `field_id` int(11) NOT NULL DEFAULT 0,
  `record_id` int(11) NOT NULL DEFAULT 0,
  `import_id` varchar(32) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `field_type` tinyint(4) NOT NULL DEFAULT 0,
  `field_values` text DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `searchable` tinyint(4) NOT NULL DEFAULT 0,
  `required` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`field_id`)
) ;

--
-- Dumping data for table `adv_mdf_definition`
--


--
-- Table structure for table `adv_mdf_definition_seq`
--

CREATE TABLE `adv_mdf_definition_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `adv_mdf_definition_seq`
--


--
-- Table structure for table `adv_mdf_enum`
--

CREATE TABLE `adv_mdf_enum` (
  `field_id` int(11) NOT NULL,
  `lang_code` varchar(5) NOT NULL,
  `idx` int(11) NOT NULL,
  `value` varchar(4000) NOT NULL,
  PRIMARY KEY (`field_id`,`lang_code`,`idx`)
) ;

--
-- Dumping data for table `adv_mdf_enum`
--


--
-- Table structure for table `aicc_course`
--

CREATE TABLE `aicc_course` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `course_creator` varchar(255) DEFAULT NULL,
  `course_id` varchar(50) DEFAULT NULL,
  `course_system` varchar(50) DEFAULT NULL,
  `course_title` varchar(255) DEFAULT NULL,
  `c_level` varchar(5) DEFAULT NULL,
  `max_fields_cst` int(11) DEFAULT 0,
  `max_fields_ort` int(11) DEFAULT 0,
  `total_aus` int(11) NOT NULL DEFAULT 0,
  `total_blocks` int(11) NOT NULL DEFAULT 0,
  `total_complex_obj` int(11) DEFAULT 0,
  `total_objectives` int(11) DEFAULT 0,
  `version` varchar(10) DEFAULT NULL,
  `max_normal` tinyint(4) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `aicc_course`
--


--
-- Table structure for table `aicc_object`
--

CREATE TABLE `aicc_object` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `slm_id` int(11) NOT NULL DEFAULT 0,
  `system_id` varchar(50) DEFAULT NULL,
  `title` varchar(4000) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `developer_id` varchar(50) DEFAULT NULL,
  `c_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`obj_id`),
  KEY `i1_idx` (`slm_id`)
) ;

--
-- Dumping data for table `aicc_object`
--


--
-- Table structure for table `aicc_object_seq`
--

CREATE TABLE `aicc_object_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `aicc_object_seq`
--


--
-- Table structure for table `aicc_units`
--

CREATE TABLE `aicc_units` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `c_type` varchar(50) DEFAULT NULL,
  `command_line` varchar(255) DEFAULT NULL,
  `max_time_allowed` time DEFAULT NULL,
  `time_limit_action` varchar(50) DEFAULT NULL,
  `max_score` double DEFAULT NULL,
  `core_vendor` varchar(4000) DEFAULT NULL,
  `system_vendor` varchar(4000) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `mastery_score` int(11) NOT NULL DEFAULT 0,
  `web_launch` varchar(255) DEFAULT NULL,
  `au_password` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `aicc_units`
--


--
-- Table structure for table `aicc_units_seq`
--

CREATE TABLE `aicc_units_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `aicc_units_seq`
--


--
-- Table structure for table `ass_log`
--

CREATE TABLE `ass_log` (
  `ass_log_id` int(11) NOT NULL DEFAULT 0,
  `user_fi` int(11) NOT NULL DEFAULT 0,
  `obj_fi` int(11) NOT NULL DEFAULT 0,
  `logtext` varchar(4000) DEFAULT NULL,
  `question_fi` int(11) DEFAULT NULL,
  `original_fi` int(11) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `test_only` varchar(1) DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ass_log_id`),
  KEY `i1_idx` (`user_fi`,`obj_fi`),
  KEY `i2_idx` (`obj_fi`)
) ;

--
-- Dumping data for table `ass_log`
--


--
-- Table structure for table `ass_log_seq`
--

CREATE TABLE `ass_log_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ass_log_seq`
--


--
-- Table structure for table `auth_ext_attr_mapping`
--

CREATE TABLE `auth_ext_attr_mapping` (
  `auth_src_id` int(11) NOT NULL DEFAULT 0,
  `attribute` varchar(75) NOT NULL,
  `ext_attribute` varchar(1000) DEFAULT NULL,
  `update_automatically` tinyint(4) NOT NULL DEFAULT 0,
  `auth_mode` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`auth_mode`,`auth_src_id`,`attribute`)
) ;

--
-- Dumping data for table `auth_ext_attr_mapping`
--


--
-- Table structure for table `background_task`
--

CREATE TABLE `background_task` (
  `id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `handler` varchar(1000) DEFAULT NULL,
  `steps` mediumint(9) NOT NULL DEFAULT 0,
  `cstep` mediumint(9) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `params` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `background_task`
--


--
-- Table structure for table `background_task_seq`
--

CREATE TABLE `background_task_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `background_task_seq`
--


--
-- Table structure for table `badge_badge`
--

CREATE TABLE `badge_badge` (
  `id` int(11) NOT NULL DEFAULT 0,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `type_id` varchar(255) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `descr` varchar(4000) DEFAULT NULL,
  `conf` varchar(4000) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `valid` varchar(255) DEFAULT NULL,
  `crit` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `badge_badge`
--


--
-- Table structure for table `badge_badge_seq`
--

CREATE TABLE `badge_badge_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `badge_badge_seq`
--


--
-- Table structure for table `badge_image_templ_type`
--

CREATE TABLE `badge_image_templ_type` (
  `tmpl_id` int(11) NOT NULL DEFAULT 0,
  `type_id` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tmpl_id`,`type_id`)
) ;

--
-- Dumping data for table `badge_image_templ_type`
--


--
-- Table structure for table `badge_image_template`
--

CREATE TABLE `badge_image_template` (
  `id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `badge_image_template`
--


--
-- Table structure for table `badge_image_template_seq`
--

CREATE TABLE `badge_image_template_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `badge_image_template_seq`
--


--
-- Table structure for table `badge_user_badge`
--

CREATE TABLE `badge_user_badge` (
  `badge_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `awarded_by` int(11) DEFAULT NULL,
  `pos` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`badge_id`,`user_id`)
) ;

--
-- Dumping data for table `badge_user_badge`
--


--
-- Table structure for table `benchmark`
--

CREATE TABLE `benchmark` (
  `id` int(11) NOT NULL DEFAULT 0,
  `cdate` datetime DEFAULT NULL,
  `module` varchar(150) DEFAULT NULL,
  `benchmark` varchar(150) DEFAULT NULL,
  `duration` double DEFAULT NULL,
  `sql_stmt` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`module`,`benchmark`)
) ;

--
-- Dumping data for table `benchmark`
--


--
-- Table structure for table `benchmark_seq`
--

CREATE TABLE `benchmark_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `benchmark_seq`
--


--
-- Table structure for table `book_obj_use_book`
--

CREATE TABLE `book_obj_use_book` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `book_ref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`book_ref_id`)
) ;

--
-- Dumping data for table `book_obj_use_book`
--


--
-- Table structure for table `booking_entry`
--

CREATE TABLE `booking_entry` (
  `booking_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `deadline` int(11) NOT NULL DEFAULT 0,
  `num_bookings` int(11) NOT NULL DEFAULT 0,
  `target_obj_id` int(11) DEFAULT NULL,
  `booking_group` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`booking_id`)
) ;

--
-- Dumping data for table `booking_entry`
--


--
-- Table structure for table `booking_entry_seq`
--

CREATE TABLE `booking_entry_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `booking_entry_seq`
--


--
-- Table structure for table `booking_member`
--

CREATE TABLE `booking_member` (
  `participant_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `booking_pool_id` varchar(255) NOT NULL,
  `assigner_user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`participant_id`,`user_id`,`booking_pool_id`)
) ;

--
-- Dumping data for table `booking_member`
--


--
-- Table structure for table `booking_member_seq`
--

CREATE TABLE `booking_member_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `booking_member_seq`
--


--
-- Table structure for table `booking_obj_assignment`
--

CREATE TABLE `booking_obj_assignment` (
  `booking_id` int(11) NOT NULL DEFAULT 0,
  `target_obj_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`booking_id`,`target_obj_id`)
) ;

--
-- Dumping data for table `booking_obj_assignment`
--


--
-- Table structure for table `booking_object`
--

CREATE TABLE `booking_object` (
  `booking_object_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `schedule_id` int(11) DEFAULT NULL,
  `pool_id` int(11) DEFAULT 0,
  `description` varchar(1000) DEFAULT NULL,
  `nr_items` smallint(6) NOT NULL DEFAULT 1,
  `info_file` varchar(500) DEFAULT NULL,
  `post_text` varchar(4000) DEFAULT NULL,
  `post_file` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`booking_object_id`),
  KEY `i1_idx` (`pool_id`),
  KEY `i2_idx` (`schedule_id`)
) ;

--
-- Dumping data for table `booking_object`
--


--
-- Table structure for table `booking_object_seq`
--

CREATE TABLE `booking_object_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `booking_object_seq`
--


--
-- Table structure for table `booking_preferences`
--

CREATE TABLE `booking_preferences` (
  `book_pool_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `book_obj_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`book_pool_id`,`user_id`,`book_obj_id`)
) ;

--
-- Dumping data for table `booking_preferences`
--


--
-- Table structure for table `booking_reservation`
--

CREATE TABLE `booking_reservation` (
  `booking_reservation_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `object_id` int(11) NOT NULL DEFAULT 0,
  `date_from` int(11) NOT NULL DEFAULT 0,
  `date_to` int(11) NOT NULL DEFAULT 0,
  `status` smallint(6) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `assigner_id` int(11) NOT NULL DEFAULT 0,
  `context_obj_id` int(11) DEFAULT 0,
  PRIMARY KEY (`booking_reservation_id`),
  KEY `i1_idx` (`user_id`),
  KEY `i2_idx` (`object_id`),
  KEY `i3_idx` (`date_from`),
  KEY `i4_idx` (`date_to`),
  KEY `i5_idx` (`context_obj_id`)
) ;

--
-- Dumping data for table `booking_reservation`
--


--
-- Table structure for table `booking_reservation_group_seq`
--

CREATE TABLE `booking_reservation_group_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `booking_reservation_group_seq`
--


--
-- Table structure for table `booking_reservation_seq`
--

CREATE TABLE `booking_reservation_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `booking_reservation_seq`
--


--
-- Table structure for table `booking_schedule`
--

CREATE TABLE `booking_schedule` (
  `booking_schedule_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `pool_id` int(11) NOT NULL DEFAULT 0,
  `deadline` int(11) DEFAULT NULL,
  `rent_min` int(11) DEFAULT NULL,
  `rent_max` int(11) DEFAULT NULL,
  `raster` int(11) DEFAULT NULL,
  `auto_break` int(11) DEFAULT NULL,
  `av_from` int(11) DEFAULT NULL,
  `av_to` int(11) DEFAULT NULL,
  PRIMARY KEY (`booking_schedule_id`),
  KEY `i1_idx` (`pool_id`)
) ;

--
-- Dumping data for table `booking_schedule`
--


--
-- Table structure for table `booking_schedule_seq`
--

CREATE TABLE `booking_schedule_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `booking_schedule_seq`
--


--
-- Table structure for table `booking_schedule_slot`
--

CREATE TABLE `booking_schedule_slot` (
  `booking_schedule_id` int(11) NOT NULL DEFAULT 0,
  `day_id` varchar(2) NOT NULL DEFAULT '',
  `slot_id` tinyint(4) NOT NULL DEFAULT 0,
  `times` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`booking_schedule_id`,`day_id`,`slot_id`)
) ;

--
-- Dumping data for table `booking_schedule_slot`
--


--
-- Table structure for table `booking_settings`
--

CREATE TABLE `booking_settings` (
  `booking_pool_id` int(11) NOT NULL DEFAULT 0,
  `public_log` tinyint(4) DEFAULT NULL,
  `pool_offline` tinyint(4) DEFAULT NULL,
  `slots_no` smallint(6) DEFAULT 0,
  `schedule_type` tinyint(4) NOT NULL DEFAULT 1,
  `ovlimit` tinyint(4) DEFAULT NULL,
  `rsv_filter_period` smallint(6) DEFAULT NULL,
  `reminder_status` tinyint(4) NOT NULL DEFAULT 0,
  `reminder_day` int(11) NOT NULL DEFAULT 0,
  `last_remind_ts` int(11) NOT NULL DEFAULT 0,
  `preference_nr` int(11) NOT NULL DEFAULT 0,
  `pref_deadline` int(11) NOT NULL DEFAULT 0,
  `pref_booking_hash` varchar(23) NOT NULL DEFAULT '0',
  PRIMARY KEY (`booking_pool_id`)
) ;

--
-- Dumping data for table `booking_settings`
--


--
-- Table structure for table `booking_user`
--

CREATE TABLE `booking_user` (
  `entry_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `booking_message` varchar(1024) DEFAULT NULL,
  `notification_sent` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`entry_id`,`user_id`)
) ;

--
-- Dumping data for table `booking_user`
--


--
-- Table structure for table `bookmark_data`
--

CREATE TABLE `bookmark_data` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `target` varchar(200) DEFAULT NULL,
  `type` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`user_id`)
) ;

--
-- Dumping data for table `bookmark_data`
--

INSERT INTO `bookmark_data` VALUES (1,0,'dummy_folder','','','bmf');

--
-- Table structure for table `bookmark_data_seq`
--

CREATE TABLE `bookmark_data_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=2;

--
-- Dumping data for table `bookmark_data_seq`
--

INSERT INTO `bookmark_data_seq` VALUES (1);

--
-- Table structure for table `bookmark_tree`
--

CREATE TABLE `bookmark_tree` (
  `tree` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tree`,`child`),
  KEY `i1_idx` (`child`),
  KEY `i2_idx` (`parent`),
  KEY `i3_idx` (`child`,`tree`)
) ;

--
-- Dumping data for table `bookmark_tree`
--

INSERT INTO `bookmark_tree` VALUES (6,1,0,1,2,1);

--
-- Table structure for table `buddylist`
--

CREATE TABLE `buddylist` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `buddy_usr_id` int(11) NOT NULL DEFAULT 0,
  `ts` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`,`buddy_usr_id`)
) ;

--
-- Dumping data for table `buddylist`
--


--
-- Table structure for table `buddylist_requests`
--

CREATE TABLE `buddylist_requests` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `buddy_usr_id` int(11) NOT NULL DEFAULT 0,
  `ignored` tinyint(4) NOT NULL DEFAULT 0,
  `ts` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`,`buddy_usr_id`),
  KEY `i1_idx` (`buddy_usr_id`,`ignored`)
) ;

--
-- Dumping data for table `buddylist_requests`
--


--
-- Table structure for table `cache_clob`
--

CREATE TABLE `cache_clob` (
  `component` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `entry_id` varchar(50) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  `expire_time` int(11) NOT NULL DEFAULT 0,
  `ilias_version` varchar(10) DEFAULT NULL,
  `int_key_1` int(11) DEFAULT NULL,
  `int_key_2` int(11) DEFAULT NULL,
  `text_key_1` varchar(20) DEFAULT NULL,
  `text_key_2` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`component`,`name`,`entry_id`),
  KEY `et_idx` (`expire_time`),
  KEY `iv_idx` (`ilias_version`)
) ;

--
-- Dumping data for table `cache_clob`
--


--
-- Table structure for table `cache_text`
--

CREATE TABLE `cache_text` (
  `component` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `entry_id` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(4000) DEFAULT NULL,
  `expire_time` int(11) NOT NULL DEFAULT 0,
  `ilias_version` varchar(10) DEFAULT NULL,
  `int_key_1` int(11) DEFAULT NULL,
  `int_key_2` int(11) DEFAULT NULL,
  `text_key_1` varchar(20) DEFAULT NULL,
  `text_key_2` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`component`,`name`,`entry_id`),
  KEY `et_idx` (`expire_time`),
  KEY `iv_idx` (`ilias_version`)
) ;

--
-- Dumping data for table `cache_text`
--


--
-- Table structure for table `cal_auth_token`
--

CREATE TABLE `cal_auth_token` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `selection` int(11) NOT NULL DEFAULT 0,
  `calendar` int(11) NOT NULL DEFAULT 0,
  `ical` longtext DEFAULT NULL,
  `c_time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`hash`),
  KEY `i1_idx` (`hash`)
) ;

--
-- Dumping data for table `cal_auth_token`
--


--
-- Table structure for table `cal_cat_assignments`
--

CREATE TABLE `cal_cat_assignments` (
  `cal_id` int(11) NOT NULL DEFAULT 0,
  `cat_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cal_id`,`cat_id`),
  KEY `i2_idx` (`cat_id`)
) ;

--
-- Dumping data for table `cal_cat_assignments`
--


--
-- Table structure for table `cal_cat_visibility`
--

CREATE TABLE `cal_cat_visibility` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `cat_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `visible` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`cat_id`,`obj_id`),
  KEY `i1_idx` (`cat_id`)
) ;

--
-- Dumping data for table `cal_cat_visibility`
--


--
-- Table structure for table `cal_categories`
--

CREATE TABLE `cal_categories` (
  `cat_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `title` char(128) DEFAULT NULL,
  `color` char(8) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `loc_type` tinyint(4) NOT NULL DEFAULT 1,
  `remote_url` varchar(500) DEFAULT NULL,
  `remote_user` varchar(50) DEFAULT NULL,
  `remote_pass` varchar(50) DEFAULT NULL,
  `remote_sync` datetime DEFAULT NULL,
  PRIMARY KEY (`cat_id`),
  KEY `i2_idx` (`obj_id`),
  KEY `i3_idx` (`type`)
) ;

--
-- Dumping data for table `cal_categories`
--


--
-- Table structure for table `cal_categories_seq`
--

CREATE TABLE `cal_categories_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cal_categories_seq`
--


--
-- Table structure for table `cal_ch_group`
--

CREATE TABLE `cal_ch_group` (
  `grp_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `multiple_assignments` tinyint(4) NOT NULL DEFAULT 0,
  `title` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`grp_id`)
) ;

--
-- Dumping data for table `cal_ch_group`
--


--
-- Table structure for table `cal_ch_group_seq`
--

CREATE TABLE `cal_ch_group_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cal_ch_group_seq`
--


--
-- Table structure for table `cal_ch_settings`
--

CREATE TABLE `cal_ch_settings` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `admin_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`admin_id`)
) ;

--
-- Dumping data for table `cal_ch_settings`
--


--
-- Table structure for table `cal_entries`
--

CREATE TABLE `cal_entries` (
  `cal_id` int(11) NOT NULL DEFAULT 0,
  `last_update` datetime DEFAULT NULL,
  `title` char(128) DEFAULT NULL,
  `subtitle` char(64) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `location` varchar(4000) DEFAULT NULL,
  `fullday` tinyint(4) NOT NULL DEFAULT 0,
  `starta` datetime DEFAULT NULL,
  `enda` datetime DEFAULT NULL,
  `informations` varchar(4000) DEFAULT NULL,
  `auto_generated` tinyint(4) NOT NULL DEFAULT 0,
  `context_id` int(11) NOT NULL DEFAULT 0,
  `translation_type` tinyint(4) NOT NULL DEFAULT 0,
  `is_milestone` tinyint(4) NOT NULL DEFAULT 0,
  `completion` int(11) NOT NULL DEFAULT 0,
  `notification` tinyint(4) NOT NULL DEFAULT 0,
  `context_info` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cal_id`),
  KEY `i1_idx` (`last_update`),
  KEY `i2_idx` (`context_id`),
  KEY `i3_idx` (`starta`),
  KEY `i4_idx` (`enda`)
) ;

--
-- Dumping data for table `cal_entries`
--


--
-- Table structure for table `cal_entries_seq`
--

CREATE TABLE `cal_entries_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cal_entries_seq`
--


--
-- Table structure for table `cal_entry_responsible`
--

CREATE TABLE `cal_entry_responsible` (
  `cal_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cal_id`,`user_id`)
) ;

--
-- Dumping data for table `cal_entry_responsible`
--


--
-- Table structure for table `cal_notification`
--

CREATE TABLE `cal_notification` (
  `notification_id` int(11) NOT NULL DEFAULT 0,
  `cal_id` int(11) NOT NULL DEFAULT 0,
  `user_type` tinyint(4) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `email` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `i1_idx` (`cal_id`)
) ;

--
-- Dumping data for table `cal_notification`
--


--
-- Table structure for table `cal_notification_seq`
--

CREATE TABLE `cal_notification_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cal_notification_seq`
--


--
-- Table structure for table `cal_rec_exclusion`
--

CREATE TABLE `cal_rec_exclusion` (
  `excl_id` int(11) NOT NULL DEFAULT 0,
  `cal_id` int(11) NOT NULL DEFAULT 0,
  `excl_date` date DEFAULT NULL,
  PRIMARY KEY (`excl_id`),
  KEY `i1_idx` (`cal_id`)
) ;

--
-- Dumping data for table `cal_rec_exclusion`
--


--
-- Table structure for table `cal_rec_exclusion_seq`
--

CREATE TABLE `cal_rec_exclusion_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cal_rec_exclusion_seq`
--


--
-- Table structure for table `cal_recurrence_rules`
--

CREATE TABLE `cal_recurrence_rules` (
  `rule_id` int(11) NOT NULL DEFAULT 0,
  `cal_id` int(11) NOT NULL DEFAULT 0,
  `cal_recurrence` int(11) NOT NULL DEFAULT 0,
  `freq_type` char(20) DEFAULT NULL,
  `freq_until_date` datetime DEFAULT NULL,
  `freq_until_count` int(11) NOT NULL DEFAULT 0,
  `intervall` int(11) NOT NULL DEFAULT 0,
  `byday` char(64) DEFAULT NULL,
  `byweekno` char(64) DEFAULT '0',
  `bymonth` char(64) DEFAULT NULL,
  `bymonthday` char(64) DEFAULT NULL,
  `byyearday` char(64) DEFAULT NULL,
  `bysetpos` char(64) DEFAULT '0',
  `weekstart` char(2) DEFAULT NULL,
  PRIMARY KEY (`rule_id`),
  KEY `i1_idx` (`cal_id`)
) ;

--
-- Dumping data for table `cal_recurrence_rules`
--


--
-- Table structure for table `cal_recurrence_rules_seq`
--

CREATE TABLE `cal_recurrence_rules_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cal_recurrence_rules_seq`
--


--
-- Table structure for table `cal_registrations`
--

CREATE TABLE `cal_registrations` (
  `cal_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `dstart` int(11) NOT NULL DEFAULT 0,
  `dend` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cal_id`,`usr_id`,`dstart`,`dend`)
) ;

--
-- Dumping data for table `cal_registrations`
--


--
-- Table structure for table `cal_shared`
--

CREATE TABLE `cal_shared` (
  `cal_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` int(11) NOT NULL DEFAULT 0,
  `create_date` datetime DEFAULT NULL,
  `writable` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`cal_id`,`obj_id`),
  KEY `i1_idx` (`obj_id`,`obj_type`)
) ;

--
-- Dumping data for table `cal_shared`
--


--
-- Table structure for table `cal_shared_status`
--

CREATE TABLE `cal_shared_status` (
  `cal_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cal_id`,`usr_id`)
) ;

--
-- Dumping data for table `cal_shared_status`
--


--
-- Table structure for table `catch_write_events`
--

CREATE TABLE `catch_write_events` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `ts` datetime DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`usr_id`)
) ;

--
-- Dumping data for table `catch_write_events`
--


--
-- Table structure for table `chatroom_admconfig`
--

CREATE TABLE `chatroom_admconfig` (
  `instance_id` int(11) NOT NULL DEFAULT 0,
  `server_settings` varchar(2000) NOT NULL DEFAULT '',
  `default_config` tinyint(4) NOT NULL DEFAULT 0,
  `client_settings` varchar(1000) NOT NULL DEFAULT '',
  PRIMARY KEY (`instance_id`)
) ;

--
-- Dumping data for table `chatroom_admconfig`
--


--
-- Table structure for table `chatroom_admconfig_seq`
--

CREATE TABLE `chatroom_admconfig_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `chatroom_admconfig_seq`
--


--
-- Table structure for table `chatroom_bans`
--

CREATE TABLE `chatroom_bans` (
  `room_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `timestamp` int(11) NOT NULL DEFAULT 0,
  `remark` varchar(1000) DEFAULT NULL,
  `actor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`room_id`,`user_id`)
) ;

--
-- Dumping data for table `chatroom_bans`
--


--
-- Table structure for table `chatroom_history`
--

CREATE TABLE `chatroom_history` (
  `hist_id` bigint(20) NOT NULL DEFAULT 0,
  `room_id` int(11) NOT NULL DEFAULT 0,
  `message` varchar(4000) DEFAULT NULL,
  `timestamp` int(11) NOT NULL DEFAULT 0,
  `sub_room` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`hist_id`),
  KEY `i1_idx` (`room_id`,`sub_room`)
) ;

--
-- Dumping data for table `chatroom_history`
--


--
-- Table structure for table `chatroom_history_seq`
--

CREATE TABLE `chatroom_history_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `chatroom_history_seq`
--


--
-- Table structure for table `chatroom_proomaccess`
--

CREATE TABLE `chatroom_proomaccess` (
  `proom_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`proom_id`,`user_id`)
) ;

--
-- Dumping data for table `chatroom_proomaccess`
--


--
-- Table structure for table `chatroom_prooms`
--

CREATE TABLE `chatroom_prooms` (
  `proom_id` int(11) NOT NULL DEFAULT 0,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) NOT NULL DEFAULT '0',
  `owner` int(11) NOT NULL DEFAULT 0,
  `created` int(11) NOT NULL DEFAULT 0,
  `closed` int(11) DEFAULT 0,
  `is_public` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`proom_id`),
  KEY `i1_idx` (`parent_id`),
  KEY `i2_idx` (`owner`)
) ;

--
-- Dumping data for table `chatroom_prooms`
--


--
-- Table structure for table `chatroom_prooms_seq`
--

CREATE TABLE `chatroom_prooms_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `chatroom_prooms_seq`
--


--
-- Table structure for table `chatroom_psessions`
--

CREATE TABLE `chatroom_psessions` (
  `psess_id` bigint(20) NOT NULL DEFAULT 0,
  `proom_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `connected` int(11) NOT NULL DEFAULT 0,
  `disconnected` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`psess_id`),
  KEY `i1_idx` (`proom_id`,`user_id`),
  KEY `i2_idx` (`disconnected`)
) ;

--
-- Dumping data for table `chatroom_psessions`
--


--
-- Table structure for table `chatroom_psessions_seq`
--

CREATE TABLE `chatroom_psessions_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `chatroom_psessions_seq`
--


--
-- Table structure for table `chatroom_sessions`
--

CREATE TABLE `chatroom_sessions` (
  `sess_id` bigint(20) NOT NULL DEFAULT 0,
  `room_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `userdata` varchar(4000) DEFAULT NULL,
  `connected` int(11) NOT NULL DEFAULT 0,
  `disconnected` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`sess_id`),
  KEY `i1_idx` (`room_id`,`user_id`),
  KEY `i2_idx` (`disconnected`),
  KEY `i3_idx` (`user_id`)
) ;

--
-- Dumping data for table `chatroom_sessions`
--


--
-- Table structure for table `chatroom_sessions_seq`
--

CREATE TABLE `chatroom_sessions_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `chatroom_sessions_seq`
--


--
-- Table structure for table `chatroom_settings`
--

CREATE TABLE `chatroom_settings` (
  `room_id` int(11) NOT NULL DEFAULT 0,
  `object_id` int(11) DEFAULT 0,
  `room_type` varchar(20) NOT NULL DEFAULT '',
  `allow_anonymous` tinyint(4) DEFAULT 0,
  `allow_custom_usernames` tinyint(4) DEFAULT 0,
  `enable_history` tinyint(4) DEFAULT 0,
  `restrict_history` tinyint(4) DEFAULT 0,
  `autogen_usernames` varchar(50) DEFAULT 'Anonymous #',
  `allow_private_rooms` tinyint(4) DEFAULT 0,
  `display_past_msgs` int(11) NOT NULL DEFAULT 0,
  `private_rooms_enabled` int(11) NOT NULL DEFAULT 0,
  `online_status` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`room_id`)
) ;

--
-- Dumping data for table `chatroom_settings`
--

INSERT INTO `chatroom_settings` VALUES (2,185,'default',0,0,0,0,'Anonymous #',1,0,1,1);

--
-- Table structure for table `chatroom_settings_seq`
--

CREATE TABLE `chatroom_settings_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=3;

--
-- Dumping data for table `chatroom_settings_seq`
--

INSERT INTO `chatroom_settings_seq` VALUES (2);

--
-- Table structure for table `chatroom_smilies`
--

CREATE TABLE `chatroom_smilies` (
  `smiley_id` int(11) NOT NULL DEFAULT 0,
  `smiley_keywords` varchar(100) DEFAULT NULL,
  `smiley_path` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`smiley_id`)
) ;

--
-- Dumping data for table `chatroom_smilies`
--


--
-- Table structure for table `chatroom_smilies_seq`
--

CREATE TABLE `chatroom_smilies_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `chatroom_smilies_seq`
--


--
-- Table structure for table `chatroom_uploads`
--

CREATE TABLE `chatroom_uploads` (
  `upload_id` int(11) NOT NULL DEFAULT 0,
  `room_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `filename` varchar(200) NOT NULL DEFAULT '',
  `filetype` varchar(200) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`upload_id`)
) ;

--
-- Dumping data for table `chatroom_uploads`
--


--
-- Table structure for table `chatroom_uploads_seq`
--

CREATE TABLE `chatroom_uploads_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `chatroom_uploads_seq`
--


--
-- Table structure for table `chatroom_users`
--

CREATE TABLE `chatroom_users` (
  `room_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `userdata` varchar(4000) NOT NULL DEFAULT '',
  `connected` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`room_id`,`user_id`)
) ;

--
-- Dumping data for table `chatroom_users`
--


--
-- Table structure for table `cmi_comment`
--

CREATE TABLE `cmi_comment` (
  `cmi_comment_id` int(11) NOT NULL DEFAULT 0,
  `cmi_node_id` int(11) DEFAULT NULL,
  `c_comment` longtext DEFAULT NULL,
  `c_timestamp` datetime DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `sourceislms` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`cmi_comment_id`),
  KEY `i2_idx` (`cmi_node_id`)
) ;

--
-- Dumping data for table `cmi_comment`
--


--
-- Table structure for table `cmi_comment_seq`
--

CREATE TABLE `cmi_comment_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cmi_comment_seq`
--


--
-- Table structure for table `cmi_correct_response`
--

CREATE TABLE `cmi_correct_response` (
  `cmi_correct_resp_id` int(11) NOT NULL DEFAULT 0,
  `cmi_interaction_id` int(11) DEFAULT NULL,
  `pattern` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cmi_correct_resp_id`),
  KEY `i1_idx` (`cmi_interaction_id`)
) ;

--
-- Dumping data for table `cmi_correct_response`
--


--
-- Table structure for table `cmi_correct_response_seq`
--

CREATE TABLE `cmi_correct_response_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cmi_correct_response_seq`
--


--
-- Table structure for table `cmi_custom`
--

CREATE TABLE `cmi_custom` (
  `sco_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `lvalue` varchar(64) NOT NULL DEFAULT ' ',
  `rvalue` varchar(255) DEFAULT NULL,
  `c_timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`,`lvalue`,`obj_id`,`sco_id`)
) ;

--
-- Dumping data for table `cmi_custom`
--


--
-- Table structure for table `cmi_gobjective`
--

CREATE TABLE `cmi_gobjective` (
  `user_id` int(11) NOT NULL,
  `satisfied` varchar(50) DEFAULT NULL,
  `measure` varchar(50) DEFAULT NULL,
  `scope_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `objective_id` varchar(253) NOT NULL,
  `score_raw` varchar(50) DEFAULT NULL,
  `score_min` varchar(50) DEFAULT NULL,
  `score_max` varchar(50) DEFAULT NULL,
  `progress_measure` varchar(50) DEFAULT NULL,
  `completion_status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`scope_id`,`objective_id`),
  KEY `i2_idx` (`scope_id`,`objective_id`)
) ;

--
-- Dumping data for table `cmi_gobjective`
--


--
-- Table structure for table `cmi_interaction`
--

CREATE TABLE `cmi_interaction` (
  `cmi_interaction_id` int(11) NOT NULL DEFAULT 0,
  `cmi_node_id` int(11) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `id` varchar(255) DEFAULT NULL,
  `latency` varchar(20) DEFAULT NULL,
  `result` varchar(4000) DEFAULT NULL,
  `c_timestamp` varchar(20) DEFAULT NULL,
  `c_type` varchar(32) DEFAULT NULL,
  `weighting` double DEFAULT NULL,
  `learner_response` longtext DEFAULT NULL,
  PRIMARY KEY (`cmi_interaction_id`),
  KEY `i2_idx` (`id`),
  KEY `i3_idx` (`c_type`),
  KEY `i4_idx` (`cmi_node_id`)
) ;

--
-- Dumping data for table `cmi_interaction`
--


--
-- Table structure for table `cmi_interaction_seq`
--

CREATE TABLE `cmi_interaction_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cmi_interaction_seq`
--


--
-- Table structure for table `cmi_node`
--

CREATE TABLE `cmi_node` (
  `accesscount` int(11) DEFAULT NULL,
  `accessduration` varchar(20) DEFAULT NULL,
  `accessed` varchar(20) DEFAULT NULL,
  `activityabsduration` varchar(20) DEFAULT NULL,
  `activityattemptcount` int(11) DEFAULT NULL,
  `activityexpduration` varchar(20) DEFAULT NULL,
  `activityprogstatus` tinyint(4) DEFAULT NULL,
  `attemptabsduration` varchar(20) DEFAULT NULL,
  `attemptcomplamount` double DEFAULT NULL,
  `attemptcomplstatus` tinyint(4) DEFAULT NULL,
  `attemptexpduration` varchar(20) DEFAULT NULL,
  `attemptprogstatus` tinyint(4) DEFAULT NULL,
  `audio_captioning` int(11) DEFAULT NULL,
  `audio_level` double DEFAULT NULL,
  `availablechildren` varchar(255) DEFAULT NULL,
  `cmi_node_id` int(11) NOT NULL DEFAULT 0,
  `completion` double DEFAULT NULL,
  `completion_status` varchar(32) DEFAULT NULL,
  `completion_threshold` varchar(32) DEFAULT NULL,
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `created` varchar(20) DEFAULT NULL,
  `credit` varchar(32) DEFAULT NULL,
  `delivery_speed` double DEFAULT NULL,
  `c_entry` varchar(255) DEFAULT NULL,
  `c_exit` varchar(255) DEFAULT NULL,
  `c_language` varchar(5) DEFAULT NULL,
  `launch_data` longtext DEFAULT NULL,
  `learner_name` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `c_max` double DEFAULT NULL,
  `c_min` double DEFAULT NULL,
  `c_mode` varchar(20) DEFAULT NULL,
  `modified` varchar(20) DEFAULT NULL,
  `progress_measure` double DEFAULT NULL,
  `c_raw` double DEFAULT NULL,
  `scaled` double DEFAULT NULL,
  `scaled_passing_score` double DEFAULT NULL,
  `session_time` varchar(20) DEFAULT NULL,
  `success_status` varchar(255) DEFAULT NULL,
  `suspend_data` longtext DEFAULT NULL,
  `total_time` varchar(20) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `c_timestamp` datetime DEFAULT NULL,
  `additional_tables` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cmi_node_id`),
  KEY `i1_idx` (`cp_node_id`),
  KEY `i2_idx` (`completion_status`),
  KEY `i3_idx` (`credit`),
  KEY `i5_idx` (`user_id`)
) ;

--
-- Dumping data for table `cmi_node`
--


--
-- Table structure for table `cmi_node_seq`
--

CREATE TABLE `cmi_node_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cmi_node_seq`
--


--
-- Table structure for table `cmi_objective`
--

CREATE TABLE `cmi_objective` (
  `cmi_interaction_id` int(11) DEFAULT NULL,
  `cmi_node_id` int(11) DEFAULT NULL,
  `cmi_objective_id` int(11) NOT NULL DEFAULT 0,
  `description` longtext DEFAULT NULL,
  `id` varchar(4000) DEFAULT NULL,
  `c_max` double DEFAULT NULL,
  `c_min` double DEFAULT NULL,
  `c_raw` double DEFAULT NULL,
  `scaled` double DEFAULT NULL,
  `progress_measure` double DEFAULT NULL,
  `success_status` varchar(32) DEFAULT NULL,
  `scope` varchar(16) DEFAULT NULL,
  `completion_status` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`cmi_objective_id`),
  KEY `i2_idx` (`cmi_interaction_id`),
  KEY `i4_idx` (`success_status`)
) ;

--
-- Dumping data for table `cmi_objective`
--


--
-- Table structure for table `cmi_objective_seq`
--

CREATE TABLE `cmi_objective_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cmi_objective_seq`
--


--
-- Table structure for table `cmix_lrs_types`
--

CREATE TABLE `cmix_lrs_types` (
  `type_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `availability` int(11) NOT NULL DEFAULT 1,
  `remarks` varchar(4000) DEFAULT NULL,
  `time_to_delete` int(11) DEFAULT NULL,
  `lrs_endpoint` varchar(255) NOT NULL,
  `lrs_key` varchar(128) NOT NULL,
  `lrs_secret` varchar(128) NOT NULL,
  `privacy_comment_default` varchar(2000) NOT NULL,
  `external_lrs` tinyint(4) NOT NULL DEFAULT 0,
  `force_privacy_settings` tinyint(4) NOT NULL DEFAULT 0,
  `bypass_proxy` tinyint(4) NOT NULL DEFAULT 0,
  `only_moveon` tinyint(4) NOT NULL DEFAULT 0,
  `achieved` tinyint(4) NOT NULL DEFAULT 1,
  `answered` tinyint(4) NOT NULL DEFAULT 1,
  `completed` tinyint(4) NOT NULL DEFAULT 1,
  `failed` tinyint(4) NOT NULL DEFAULT 1,
  `initialized` tinyint(4) NOT NULL DEFAULT 1,
  `passed` tinyint(4) NOT NULL DEFAULT 1,
  `progressed` tinyint(4) NOT NULL DEFAULT 1,
  `satisfied` tinyint(4) NOT NULL DEFAULT 1,
  `c_terminated` tinyint(4) NOT NULL DEFAULT 1,
  `hide_data` tinyint(4) NOT NULL DEFAULT 0,
  `c_timestamp` tinyint(4) NOT NULL DEFAULT 0,
  `duration` tinyint(4) NOT NULL DEFAULT 1,
  `no_substatements` tinyint(4) NOT NULL DEFAULT 0,
  `privacy_ident` smallint(6) NOT NULL DEFAULT 0,
  `privacy_name` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`type_id`)
) ;

--
-- Dumping data for table `cmix_lrs_types`
--


--
-- Table structure for table `cmix_lrs_types_seq`
--

CREATE TABLE `cmix_lrs_types_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cmix_lrs_types_seq`
--


--
-- Table structure for table `cmix_results`
--

CREATE TABLE `cmix_results` (
  `id` int(11) NOT NULL,
  `obj_id` int(11) NOT NULL,
  `usr_id` int(11) NOT NULL,
  `version` smallint(6) NOT NULL DEFAULT 1,
  `score` double DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT '0',
  `last_update` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`obj_id`,`usr_id`)
) ;

--
-- Dumping data for table `cmix_results`
--


--
-- Table structure for table `cmix_results_seq`
--

CREATE TABLE `cmix_results_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cmix_results_seq`
--


--
-- Table structure for table `cmix_settings`
--

CREATE TABLE `cmix_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `lrs_type_id` int(11) NOT NULL DEFAULT 0,
  `content_type` varchar(32) DEFAULT NULL,
  `source_type` varchar(32) DEFAULT NULL,
  `activity_id` varchar(255) DEFAULT NULL,
  `instructions` varchar(4000) DEFAULT NULL,
  `offline_status` tinyint(4) NOT NULL DEFAULT 1,
  `launch_url` varchar(255) DEFAULT NULL,
  `auth_fetch_url` tinyint(4) NOT NULL DEFAULT 0,
  `launch_method` varchar(32) DEFAULT NULL,
  `launch_mode` varchar(32) DEFAULT NULL,
  `mastery_score` double NOT NULL DEFAULT 0,
  `keep_lp` tinyint(4) NOT NULL DEFAULT 0,
  `usr_privacy_comment` varchar(4000) DEFAULT NULL,
  `show_statements` tinyint(4) NOT NULL DEFAULT 0,
  `xml_manifest` longtext DEFAULT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `highscore_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_achieved_ts` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_percentage` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_wtime` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_own_table` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_top_table` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_top_num` int(11) NOT NULL DEFAULT 0,
  `bypass_proxy` tinyint(4) NOT NULL DEFAULT 0,
  `only_moveon` tinyint(4) NOT NULL DEFAULT 0,
  `achieved` tinyint(4) NOT NULL DEFAULT 1,
  `answered` tinyint(4) NOT NULL DEFAULT 1,
  `completed` tinyint(4) NOT NULL DEFAULT 1,
  `failed` tinyint(4) NOT NULL DEFAULT 1,
  `initialized` tinyint(4) NOT NULL DEFAULT 1,
  `passed` tinyint(4) NOT NULL DEFAULT 1,
  `progressed` tinyint(4) NOT NULL DEFAULT 1,
  `satisfied` tinyint(4) NOT NULL DEFAULT 1,
  `c_terminated` tinyint(4) NOT NULL DEFAULT 1,
  `hide_data` tinyint(4) NOT NULL DEFAULT 0,
  `c_timestamp` tinyint(4) NOT NULL DEFAULT 0,
  `duration` tinyint(4) NOT NULL DEFAULT 1,
  `no_substatements` tinyint(4) NOT NULL DEFAULT 0,
  `privacy_ident` smallint(6) NOT NULL DEFAULT 0,
  `privacy_name` smallint(6) NOT NULL DEFAULT 0,
  `publisher_id` varchar(255) NOT NULL DEFAULT '',
  `anonymous_homepage` tinyint(4) NOT NULL DEFAULT 1,
  `moveon` varchar(32) NOT NULL DEFAULT '',
  `launch_parameters` varchar(255) NOT NULL DEFAULT '',
  `entitlement_key` varchar(255) NOT NULL DEFAULT '',
  `switch_to_review` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `cmix_settings`
--


--
-- Table structure for table `cmix_token`
--

CREATE TABLE `cmix_token` (
  `token` varchar(255) NOT NULL DEFAULT '0',
  `valid_until` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `lrs_type_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `cmi5_session` varchar(255) NOT NULL DEFAULT '',
  `returned_for_cmi5_session` varchar(255) NOT NULL DEFAULT '',
  `cmi5_session_data` longtext DEFAULT NULL,
  PRIMARY KEY (`token`),
  UNIQUE KEY `c1_idx` (`obj_id`,`usr_id`,`ref_id`),
  KEY `i1_idx` (`token`,`valid_until`)
) ;

--
-- Dumping data for table `cmix_token`
--


--
-- Table structure for table `cmix_users`
--

CREATE TABLE `cmix_users` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `proxy_success` tinyint(4) NOT NULL DEFAULT 0,
  `fetched_until` datetime DEFAULT NULL,
  `usr_ident` varchar(255) DEFAULT NULL,
  `privacy_ident` smallint(6) NOT NULL DEFAULT 0,
  `registration` varchar(255) NOT NULL DEFAULT '',
  `satisfied` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`usr_id`,`privacy_ident`)
) ;

--
-- Dumping data for table `cmix_users`
--


--
-- Table structure for table `conditions`
--

CREATE TABLE `conditions` (
  `condition_id` int(11) NOT NULL DEFAULT 0,
  `target_ref_id` int(11) NOT NULL DEFAULT 0,
  `target_obj_id` int(11) NOT NULL DEFAULT 0,
  `target_type` varchar(8) DEFAULT NULL,
  `trigger_ref_id` int(11) NOT NULL DEFAULT 0,
  `trigger_obj_id` int(11) NOT NULL DEFAULT 0,
  `trigger_type` varchar(8) DEFAULT NULL,
  `operator` varchar(64) DEFAULT NULL,
  `value` varchar(64) DEFAULT NULL,
  `ref_handling` tinyint(4) NOT NULL DEFAULT 1,
  `obligatory` tinyint(4) NOT NULL DEFAULT 1,
  `num_obligatory` tinyint(4) NOT NULL DEFAULT 0,
  `hidden_status` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`condition_id`),
  KEY `tot_idx` (`target_obj_id`,`target_type`),
  KEY `i1_idx` (`target_obj_id`)
) ;

--
-- Dumping data for table `conditions`
--


--
-- Table structure for table `conditions_seq`
--

CREATE TABLE `conditions_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `conditions_seq`
--


--
-- Table structure for table `cont_filter_field`
--

CREATE TABLE `cont_filter_field` (
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `record_set_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0
) ;

--
-- Dumping data for table `cont_filter_field`
--


--
-- Table structure for table `cont_member_skills`
--

CREATE TABLE `cont_member_skills` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  `skill_id` int(11) NOT NULL DEFAULT 0,
  `level_id` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`user_id`,`skill_id`,`tref_id`)
) ;

--
-- Dumping data for table `cont_member_skills`
--


--
-- Table structure for table `cont_skills`
--

CREATE TABLE `cont_skills` (
  `id` int(11) NOT NULL DEFAULT 0,
  `skill_id` int(11) NOT NULL DEFAULT 0,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`,`skill_id`,`tref_id`)
) ;

--
-- Dumping data for table `cont_skills`
--


--
-- Table structure for table `container_reference`
--

CREATE TABLE `container_reference` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `target_obj_id` int(11) NOT NULL DEFAULT 0,
  `title_type` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`obj_id`,`target_obj_id`),
  KEY `i1_idx` (`obj_id`)
) ;

--
-- Dumping data for table `container_reference`
--


--
-- Table structure for table `container_settings`
--

CREATE TABLE `container_settings` (
  `id` int(11) NOT NULL DEFAULT 0,
  `keyword` char(40) NOT NULL DEFAULT '',
  `value` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`,`keyword`)
) ;

--
-- Dumping data for table `container_settings`
--


--
-- Table structure for table `container_sorting`
--

CREATE TABLE `container_sorting` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `child_id` int(11) NOT NULL DEFAULT 0,
  `position` int(11) NOT NULL DEFAULT 0,
  `parent_type` varchar(5) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`child_id`,`parent_id`)
) ;

--
-- Dumping data for table `container_sorting`
--


--
-- Table structure for table `container_sorting_bl`
--

CREATE TABLE `container_sorting_bl` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `block_ids` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `container_sorting_bl`
--


--
-- Table structure for table `container_sorting_set`
--

CREATE TABLE `container_sorting_set` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `sort_mode` tinyint(4) NOT NULL DEFAULT 0,
  `sort_direction` tinyint(4) NOT NULL DEFAULT 0,
  `new_items_position` tinyint(4) NOT NULL DEFAULT 1,
  `new_items_order` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `container_sorting_set`
--


--
-- Table structure for table `content_object`
--

CREATE TABLE `content_object` (
  `id` int(11) NOT NULL DEFAULT 0,
  `default_layout` varchar(100) DEFAULT 'toc2win',
  `stylesheet` int(11) NOT NULL DEFAULT 0,
  `page_header` char(8) DEFAULT 'st_title',
  `is_online` char(1) DEFAULT 'n',
  `toc_active` char(1) DEFAULT 'y',
  `lm_menu_active` char(1) DEFAULT 'y',
  `toc_mode` char(8) DEFAULT 'chapters',
  `clean_frames` char(1) DEFAULT 'n',
  `print_view_active` char(1) DEFAULT 'y',
  `numbering` char(1) DEFAULT 'n',
  `hist_user_comments` char(1) DEFAULT 'n',
  `public_access_mode` char(8) DEFAULT 'complete',
  `public_html_file` varchar(50) DEFAULT NULL,
  `public_xml_file` varchar(50) DEFAULT NULL,
  `downloads_active` char(1) DEFAULT 'n',
  `downloads_public_active` char(1) DEFAULT 'y',
  `header_page` int(11) NOT NULL DEFAULT 0,
  `footer_page` int(11) NOT NULL DEFAULT 0,
  `no_glo_appendix` char(1) DEFAULT 'n',
  `layout_per_page` tinyint(4) DEFAULT NULL,
  `public_scorm_file` varchar(50) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 0,
  `hide_head_foot_print` tinyint(4) NOT NULL DEFAULT 0,
  `disable_def_feedback` int(11) NOT NULL DEFAULT 0,
  `rating_pages` tinyint(4) DEFAULT 0,
  `progr_icons` tinyint(4) NOT NULL DEFAULT 0,
  `store_tries` tinyint(4) NOT NULL DEFAULT 0,
  `restrict_forw_nav` tinyint(4) NOT NULL DEFAULT 0,
  `for_translation` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `content_object`
--


--
-- Table structure for table `content_page_data`
--

CREATE TABLE `content_page_data` (
  `content_page_id` int(11) NOT NULL DEFAULT 0,
  `stylesheet` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`content_page_id`)
) ;

--
-- Dumping data for table `content_page_data`
--


--
-- Table structure for table `content_page_metrics`
--

CREATE TABLE `content_page_metrics` (
  `content_page_id` int(11) NOT NULL DEFAULT 0,
  `page_id` int(11) NOT NULL DEFAULT 0,
  `lang` varchar(2) NOT NULL DEFAULT '-',
  `reading_time` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`content_page_id`,`page_id`,`lang`)
) ;

--
-- Dumping data for table `content_page_metrics`
--


--
-- Table structure for table `copg_multilang`
--

CREATE TABLE `copg_multilang` (
  `parent_type` varchar(10) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `master_lang` varchar(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`parent_type`,`parent_id`)
) ;

--
-- Dumping data for table `copg_multilang`
--


--
-- Table structure for table `copg_multilang_lang`
--

CREATE TABLE `copg_multilang_lang` (
  `parent_type` varchar(10) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `lang` varchar(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`parent_type`,`parent_id`,`lang`)
) ;

--
-- Dumping data for table `copg_multilang_lang`
--


--
-- Table structure for table `copg_pc_def`
--

CREATE TABLE `copg_pc_def` (
  `pc_type` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(40) NOT NULL DEFAULT '',
  `directory` varchar(40) DEFAULT NULL,
  `int_links` tinyint(4) NOT NULL DEFAULT 0,
  `style_classes` tinyint(4) NOT NULL DEFAULT 0,
  `xsl` tinyint(4) NOT NULL DEFAULT 0,
  `component` varchar(40) DEFAULT NULL,
  `def_enabled` tinyint(4) DEFAULT 0,
  `top_item` tinyint(4) NOT NULL DEFAULT 0,
  `order_nr` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`pc_type`)
) ;

--
-- Dumping data for table `copg_pc_def`
--

INSERT INTO `copg_pc_def` VALUES ('amdpl','AMDPageList','classes',0,0,0,'Modules/Wiki',0,1,127);
INSERT INTO `copg_pc_def` VALUES ('blog','Blog','classes',0,0,0,'Services/COPage',0,1,100);
INSERT INTO `copg_pc_def` VALUES ('cach','ConsultationHours','classes',0,0,0,'Modules/Portfolio',0,1,127);
INSERT INTO `copg_pc_def` VALUES ('dtab','DataTable','classes',0,1,0,'Services/COPage',1,1,50);
INSERT INTO `copg_pc_def` VALUES ('flit','FileItem','classes',0,1,0,'Services/COPage',1,0,0);
INSERT INTO `copg_pc_def` VALUES ('flst','FileList','classes',0,1,0,'Services/COPage',1,1,40);
INSERT INTO `copg_pc_def` VALUES ('gcell','GridCell','classes',0,0,0,'Services/COPage',1,0,0);
INSERT INTO `copg_pc_def` VALUES ('grid','Grid','classes',0,0,0,'Services/COPage',1,1,127);
INSERT INTO `copg_pc_def` VALUES ('iim','InteractiveImage','classes',1,1,0,'Services/COPage',1,1,100);
INSERT INTO `copg_pc_def` VALUES ('incl','ContentInclude','classes',1,1,0,'Services/COPage',0,1,110);
INSERT INTO `copg_pc_def` VALUES ('lhist','LearningHistory','classes',0,0,0,'Services/LearningHistory',0,1,127);
INSERT INTO `copg_pc_def` VALUES ('li','ListItem','classes',0,0,0,'Services/COPage',1,0,0);
INSERT INTO `copg_pc_def` VALUES ('list','List','classes',0,1,0,'Services/COPage',1,1,127);
INSERT INTO `copg_pc_def` VALUES ('lpe','LoginPageElement','classes',0,0,0,'Services/COPage',0,1,120);
INSERT INTO `copg_pc_def` VALUES ('map','Map','classes',0,0,0,'Services/COPage',0,1,127);
INSERT INTO `copg_pc_def` VALUES ('mcrs','MyCourses','classes',0,0,0,'Modules/Portfolio',0,1,127);
INSERT INTO `copg_pc_def` VALUES ('media','MediaObject','classes',0,1,0,'Services/COPage',1,1,20);
INSERT INTO `copg_pc_def` VALUES ('par','Paragraph','classes',1,1,0,'Services/COPage',1,1,10);
INSERT INTO `copg_pc_def` VALUES ('pcqst','Question','classes',0,0,0,'Services/COPage',0,1,70);
INSERT INTO `copg_pc_def` VALUES ('plach','PlaceHolder','classes',0,1,0,'Services/COPage',0,1,90);
INSERT INTO `copg_pc_def` VALUES ('plug','Plugged','classes',0,0,0,'Services/COPage',0,1,127);
INSERT INTO `copg_pc_def` VALUES ('prof','Profile','classes',0,0,0,'Services/COPage',0,1,127);
INSERT INTO `copg_pc_def` VALUES ('qover','QuestionOverview','classes',0,0,0,'Services/COPage',0,1,80);
INSERT INTO `copg_pc_def` VALUES ('repobj','Resources','classes',0,0,0,'Services/COPage',0,1,127);
INSERT INTO `copg_pc_def` VALUES ('sec','Section','classes',0,1,0,'Services/COPage',1,1,60);
INSERT INTO `copg_pc_def` VALUES ('skills','Skills','classes',0,0,0,'Services/COPage',0,1,127);
INSERT INTO `copg_pc_def` VALUES ('src','SourceCode','classes',0,0,0,'Services/COPage',1,1,127);
INSERT INTO `copg_pc_def` VALUES ('tab','Table','classes',0,1,0,'Services/COPage',1,1,127);
INSERT INTO `copg_pc_def` VALUES ('tabs','Tabs','classes',0,1,0,'Services/COPage',1,1,127);
INSERT INTO `copg_pc_def` VALUES ('tabstab','Tab','classes',0,0,0,'Services/COPage',1,0,0);
INSERT INTO `copg_pc_def` VALUES ('td','TableData','classes',0,0,0,'Services/COPage',1,0,0);
INSERT INTO `copg_pc_def` VALUES ('templ','ContentTemplate','classes',0,0,0,'Services/COPage',0,1,127);
INSERT INTO `copg_pc_def` VALUES ('vrfc','Verification','classes',0,0,0,'Services/COPage',0,1,127);

--
-- Table structure for table `copg_pobj_def`
--

CREATE TABLE `copg_pobj_def` (
  `parent_type` varchar(20) NOT NULL DEFAULT '',
  `class_name` varchar(80) NOT NULL DEFAULT '',
  `directory` varchar(40) DEFAULT NULL,
  `component` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`parent_type`)
) ;

--
-- Dumping data for table `copg_pobj_def`
--

INSERT INTO `copg_pobj_def` VALUES ('auth','ilLoginPage','classes','Services/Authentication');
INSERT INTO `copg_pobj_def` VALUES ('blp','ilBlogPosting','classes','Modules/Blog');
INSERT INTO `copg_pobj_def` VALUES ('cont','ilContainerPage','classes','Services/Container');
INSERT INTO `copg_pobj_def` VALUES ('copa','ilContentPagePage','classes','Modules/ContentPage');
INSERT INTO `copg_pobj_def` VALUES ('cstr','ilContainerStartObjectsPage','classes','Services/Container');
INSERT INTO `copg_pobj_def` VALUES ('dclf','ilDclDetailedViewDefinition','classes/DetailedView','Modules/DataCollection');
INSERT INTO `copg_pobj_def` VALUES ('gdf','ilGlossaryDefPage','classes','Modules/Glossary');
INSERT INTO `copg_pobj_def` VALUES ('impr','ilImprint','classes','Services/Imprint');
INSERT INTO `copg_pobj_def` VALUES ('lm','ilLMPage','classes','Modules/LearningModule');
INSERT INTO `copg_pobj_def` VALUES ('lobj','ilLOPage','classes/Objectives','Modules/Course');
INSERT INTO `copg_pobj_def` VALUES ('mep','ilMediaPoolPage','classes','Modules/MediaPool');
INSERT INTO `copg_pobj_def` VALUES ('prtf','ilPortfolioPage','classes','Modules/Portfolio');
INSERT INTO `copg_pobj_def` VALUES ('prtt','ilPortfolioTemplatePage','classes','Modules/Portfolio');
INSERT INTO `copg_pobj_def` VALUES ('qfbg','ilAssGenFeedbackPage','classes/feedback','Modules/TestQuestionPool');
INSERT INTO `copg_pobj_def` VALUES ('qfbs','ilAssSpecFeedbackPage','classes/feedback','Modules/TestQuestionPool');
INSERT INTO `copg_pobj_def` VALUES ('qht','ilAssHintPage','classes','Modules/TestQuestionPool');
INSERT INTO `copg_pobj_def` VALUES ('qpl','ilAssQuestionPage','classes','Modules/TestQuestionPool');
INSERT INTO `copg_pobj_def` VALUES ('sahs','ilSCORM2004Page','classes','Modules/Scorm2004');
INSERT INTO `copg_pobj_def` VALUES ('stys','ilPageLayoutPage','Layout/classes','Services/COPage');
INSERT INTO `copg_pobj_def` VALUES ('wpg','ilWikiPage','classes','Modules/Wiki');

--
-- Table structure for table `copg_section_timings`
--

CREATE TABLE `copg_section_timings` (
  `page_id` int(11) NOT NULL DEFAULT 0,
  `parent_type` varchar(10) NOT NULL DEFAULT '',
  `unix_ts` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`page_id`,`parent_type`,`unix_ts`)
) ;

--
-- Dumping data for table `copg_section_timings`
--


--
-- Table structure for table `copy_wizard_options`
--

CREATE TABLE `copy_wizard_options` (
  `copy_id` int(11) NOT NULL DEFAULT 0,
  `source_id` int(11) NOT NULL DEFAULT 0,
  `options` longtext DEFAULT NULL,
  PRIMARY KEY (`copy_id`,`source_id`)
) ;

--
-- Dumping data for table `copy_wizard_options`
--


--
-- Table structure for table `cp_auxilaryresource`
--

CREATE TABLE `cp_auxilaryresource` (
  `auxiliaryresourceid` varchar(255) DEFAULT NULL,
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `purpose` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`)
) ;

--
-- Dumping data for table `cp_auxilaryresource`
--


--
-- Table structure for table `cp_condition`
--

CREATE TABLE `cp_condition` (
  `c_condition` varchar(50) DEFAULT NULL,
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `measurethreshold` varchar(50) DEFAULT NULL,
  `c_operator` varchar(50) DEFAULT NULL,
  `referencedobjective` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`)
) ;

--
-- Dumping data for table `cp_condition`
--


--
-- Table structure for table `cp_datamap`
--

CREATE TABLE `cp_datamap` (
  `sco_node_id` int(11) NOT NULL DEFAULT 0,
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `slm_id` int(11) NOT NULL DEFAULT 0,
  `target_id` varchar(4000) NOT NULL DEFAULT '',
  `read_shared_data` tinyint(4) DEFAULT 1,
  `write_shared_data` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`cp_node_id`)
) ;

--
-- Dumping data for table `cp_datamap`
--


--
-- Table structure for table `cp_dependency`
--

CREATE TABLE `cp_dependency` (
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `resourceid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`),
  KEY `i2_idx` (`resourceid`)
) ;

--
-- Dumping data for table `cp_dependency`
--


--
-- Table structure for table `cp_file`
--

CREATE TABLE `cp_file` (
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `href` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`)
) ;

--
-- Dumping data for table `cp_file`
--


--
-- Table structure for table `cp_hidelmsui`
--

CREATE TABLE `cp_hidelmsui` (
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `value` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`),
  KEY `i1_idx` (`value`)
) ;

--
-- Dumping data for table `cp_hidelmsui`
--


--
-- Table structure for table `cp_item`
--

CREATE TABLE `cp_item` (
  `completionthreshold` varchar(50) DEFAULT '1.0',
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `datafromlms` varchar(4000) DEFAULT NULL,
  `id` varchar(200) DEFAULT NULL,
  `isvisible` varchar(32) DEFAULT NULL,
  `parameters` varchar(255) DEFAULT NULL,
  `resourceid` varchar(200) DEFAULT NULL,
  `sequencingid` varchar(50) DEFAULT NULL,
  `timelimitaction` varchar(30) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `progressweight` varchar(50) DEFAULT '1.0',
  `completedbymeasure` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`cp_node_id`),
  KEY `i1_idx` (`id`),
  KEY `i2_idx` (`sequencingid`)
) ;

--
-- Dumping data for table `cp_item`
--


--
-- Table structure for table `cp_manifest`
--

CREATE TABLE `cp_manifest` (
  `base` varchar(200) DEFAULT NULL,
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `defaultorganization` varchar(50) DEFAULT NULL,
  `id` varchar(200) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `uri` varchar(255) DEFAULT NULL,
  `version` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`),
  KEY `i1_idx` (`id`)
) ;

--
-- Dumping data for table `cp_manifest`
--


--
-- Table structure for table `cp_mapinfo`
--

CREATE TABLE `cp_mapinfo` (
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `readnormalmeasure` tinyint(4) DEFAULT NULL,
  `readsatisfiedstatus` tinyint(4) DEFAULT NULL,
  `targetobjectiveid` varchar(255) DEFAULT NULL,
  `writenormalmeasure` tinyint(4) DEFAULT NULL,
  `writesatisfiedstatus` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`),
  KEY `i1_idx` (`targetobjectiveid`)
) ;

--
-- Dumping data for table `cp_mapinfo`
--


--
-- Table structure for table `cp_node`
--

CREATE TABLE `cp_node` (
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `nodename` varchar(50) DEFAULT NULL,
  `slm_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`),
  KEY `i2_idx` (`nodename`),
  KEY `i3_idx` (`slm_id`)
) ;

--
-- Dumping data for table `cp_node`
--


--
-- Table structure for table `cp_node_seq`
--

CREATE TABLE `cp_node_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `cp_node_seq`
--


--
-- Table structure for table `cp_objective`
--

CREATE TABLE `cp_objective` (
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `minnormalmeasure` varchar(50) DEFAULT NULL,
  `objectiveid` varchar(200) DEFAULT NULL,
  `c_primary` tinyint(4) DEFAULT NULL,
  `satisfiedbymeasure` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`)
) ;

--
-- Dumping data for table `cp_objective`
--


--
-- Table structure for table `cp_organization`
--

CREATE TABLE `cp_organization` (
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `id` varchar(200) DEFAULT NULL,
  `objectivesglobtosys` tinyint(4) DEFAULT NULL,
  `sequencingid` varchar(50) DEFAULT NULL,
  `structure` varchar(200) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`),
  KEY `i1_idx` (`id`),
  KEY `i2_idx` (`sequencingid`)
) ;

--
-- Dumping data for table `cp_organization`
--


--
-- Table structure for table `cp_package`
--

CREATE TABLE `cp_package` (
  `created` varchar(20) DEFAULT NULL,
  `c_identifier` varchar(255) DEFAULT NULL,
  `jsdata` longtext DEFAULT NULL,
  `modified` varchar(20) DEFAULT NULL,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `persistprevattempts` int(11) DEFAULT NULL,
  `c_settings` varchar(255) DEFAULT NULL,
  `xmldata` longtext DEFAULT NULL,
  `activitytree` longtext DEFAULT NULL,
  `global_to_system` tinyint(4) NOT NULL DEFAULT 1,
  `shared_data_global_to_system` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`obj_id`),
  KEY `i1_idx` (`c_identifier`)
) ;

--
-- Dumping data for table `cp_package`
--


--
-- Table structure for table `cp_resource`
--

CREATE TABLE `cp_resource` (
  `base` varchar(4000) DEFAULT NULL,
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `href` varchar(4000) DEFAULT NULL,
  `id` varchar(200) DEFAULT NULL,
  `scormtype` varchar(32) DEFAULT NULL,
  `c_type` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`),
  KEY `i1_idx` (`id`)
) ;

--
-- Dumping data for table `cp_resource`
--


--
-- Table structure for table `cp_rule`
--

CREATE TABLE `cp_rule` (
  `action` varchar(50) DEFAULT NULL,
  `childactivityset` varchar(50) DEFAULT NULL,
  `conditioncombination` varchar(50) DEFAULT NULL,
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `minimumcount` int(11) DEFAULT NULL,
  `minimumpercent` varchar(50) DEFAULT NULL,
  `c_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`)
) ;

--
-- Dumping data for table `cp_rule`
--


--
-- Table structure for table `cp_sequencing`
--

CREATE TABLE `cp_sequencing` (
  `activityabsdurlimit` varchar(20) DEFAULT NULL,
  `activityexpdurlimit` varchar(20) DEFAULT NULL,
  `attemptabsdurlimit` varchar(20) DEFAULT NULL,
  `attemptexpdurlimit` varchar(20) DEFAULT NULL,
  `attemptlimit` int(11) DEFAULT NULL,
  `begintimelimit` varchar(20) DEFAULT NULL,
  `choice` tinyint(4) DEFAULT NULL,
  `choiceexit` tinyint(4) DEFAULT NULL,
  `completionbycontent` tinyint(4) DEFAULT NULL,
  `constrainchoice` tinyint(4) DEFAULT NULL,
  `cp_node_id` int(11) NOT NULL DEFAULT 0,
  `endtimelimit` varchar(20) DEFAULT NULL,
  `flow` tinyint(4) DEFAULT NULL,
  `forwardonly` tinyint(4) DEFAULT NULL,
  `id` varchar(200) DEFAULT NULL,
  `measuresatisfactive` tinyint(4) DEFAULT NULL,
  `objectivemeasweight` double DEFAULT NULL,
  `objectivebycontent` tinyint(4) DEFAULT NULL,
  `preventactivation` tinyint(4) DEFAULT NULL,
  `randomizationtiming` varchar(50) DEFAULT NULL,
  `reorderchildren` tinyint(4) DEFAULT NULL,
  `requiredcompleted` varchar(50) DEFAULT NULL,
  `requiredincomplete` varchar(50) DEFAULT NULL,
  `requirednotsatisfied` varchar(50) DEFAULT NULL,
  `requiredforsatisfied` varchar(50) DEFAULT NULL,
  `rollupobjectivesatis` tinyint(4) DEFAULT NULL,
  `rollupprogcompletion` tinyint(4) DEFAULT NULL,
  `selectcount` int(11) DEFAULT NULL,
  `selectiontiming` varchar(50) DEFAULT NULL,
  `sequencingid` varchar(50) DEFAULT NULL,
  `tracked` tinyint(4) DEFAULT NULL,
  `usecurattemptobjinfo` tinyint(4) DEFAULT NULL,
  `usecurattemptproginfo` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`cp_node_id`),
  KEY `i1_idx` (`id`)
) ;

--
-- Dumping data for table `cp_sequencing`
--


--
-- Table structure for table `cp_suspend`
--

CREATE TABLE `cp_suspend` (
  `data` longtext DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `obj_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`obj_id`)
) ;

--
-- Dumping data for table `cp_suspend`
--


--
-- Table structure for table `cp_tree`
--

CREATE TABLE `cp_tree` (
  `child` int(11) NOT NULL DEFAULT 0,
  `depth` int(11) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) DEFAULT NULL,
  `rgt` int(11) DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`child`),
  KEY `i1_idx` (`child`),
  KEY `i2_idx` (`obj_id`),
  KEY `i3_idx` (`parent`)
) ;

--
-- Dumping data for table `cp_tree`
--


--
-- Table structure for table `cron_job`
--

CREATE TABLE `cron_job` (
  `job_id` varchar(50) NOT NULL DEFAULT '',
  `component` varchar(200) DEFAULT NULL,
  `schedule_type` tinyint(4) DEFAULT NULL,
  `schedule_value` int(11) DEFAULT NULL,
  `job_status` tinyint(4) DEFAULT NULL,
  `job_status_user_id` int(11) DEFAULT NULL,
  `job_status_type` tinyint(4) DEFAULT NULL,
  `job_status_ts` int(11) DEFAULT NULL,
  `job_result_status` tinyint(4) DEFAULT NULL,
  `job_result_user_id` int(11) DEFAULT NULL,
  `job_result_code` varchar(64) DEFAULT NULL,
  `job_result_message` varchar(400) DEFAULT NULL,
  `job_result_type` tinyint(4) DEFAULT NULL,
  `job_result_ts` int(11) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL,
  `path` varchar(400) DEFAULT NULL,
  `running_ts` int(11) DEFAULT NULL,
  `job_result_dur` int(11) DEFAULT NULL,
  `alive_ts` int(11) DEFAULT NULL,
  PRIMARY KEY (`job_id`)
) ;

--
-- Dumping data for table `cron_job`
--

INSERT INTO `cron_job` VALUES ('book_notification','Modules/BookingManager',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ilBookCronNotification',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('book_pref_book','Modules/BookingManager',1,NULL,1,0,0,1605888798,NULL,NULL,NULL,NULL,NULL,NULL,'ilBookingPrefBookCron',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('cal_consultation','Services/Calendar',0,0,0,0,0,0,0,0,'','',0,0,'ilConsultationHourCron','Services/Calendar/classes/ConsultationHours/',0,0,0);
INSERT INTO `cron_job` VALUES ('certificate','Services/Certificate',2,1,1,0,0,1605888798,NULL,NULL,NULL,NULL,NULL,NULL,'ilCertificateCron',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('crs_timings_reminder','Modules/Course',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ilTimingsCronReminder',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('ecs_task_handler','Services/WebServices',3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ilCronEcsTaskScheduler',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('exc_feedback_notification','Modules/Exercise',0,0,1,0,0,1381511097,0,0,'','',0,0,'ilExcCronFeedbackNotification','',0,0,0);
INSERT INTO `cron_job` VALUES ('exc_reminders','Modules/Exercise',1,NULL,1,0,0,1605888798,NULL,NULL,NULL,NULL,NULL,NULL,'ilExcCronReminders',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('finish_unfinished_passes','Modules/Test',1,0,0,0,0,0,0,0,'','',0,0,'ilCronFinishUnfinishedTestPasses','',0,0,0);
INSERT INTO `cron_job` VALUES ('frm_notification','Modules/Forum',3,1,0,0,0,0,0,0,'','',0,0,'ilForumCronNotification','',0,0,0);
INSERT INTO `cron_job` VALUES ('ldap_sync','Services/LDAP',0,0,0,0,0,0,0,0,'','',0,0,'ilLDAPCronSynchronization','',0,0,0);
INSERT INTO `cron_job` VALUES ('lm_link_check','Modules/LearningModule',0,0,0,0,0,0,0,0,'','',0,0,'ilLearningModuleCronLinkCheck','',0,0,0);
INSERT INTO `cron_job` VALUES ('log_error_file_cleanup','Services/Logging',4,10,0,0,0,0,0,0,'','',0,0,'ilLoggerCronCleanErrorFiles','Services/Logging/classes/error/',0,0,0);
INSERT INTO `cron_job` VALUES ('lp_object_statistics','Services/Tracking',0,0,1,0,0,1381511103,0,0,'','',0,0,'ilLPCronObjectStatistics','',0,0,0);
INSERT INTO `cron_job` VALUES ('lti_outcome','Services/LTI',2,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ilLTICronOutcomeService',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('mail_notification','Services/Mail',0,0,1,6,1,1481898063,NULL,6,'job_manual_reset','Cron job re-activated by admin',1,0,'ilMailCronNotification','',0,0,0);
INSERT INTO `cron_job` VALUES ('mail_orphaned_mails','Services/Mail',1,1,0,0,0,0,0,0,'','',0,0,'ilMailCronOrphanedMails','',0,0,0);
INSERT INTO `cron_job` VALUES ('mem_min_members','Services/Membership',0,0,1,0,0,1443610661,0,0,'','',0,0,'ilMembershipCronMinMembers','',0,0,0);
INSERT INTO `cron_job` VALUES ('mem_notification','Services/Membership',1,0,0,0,0,0,0,0,'','',0,0,'ilMembershipCronNotifications','',0,0,0);
INSERT INTO `cron_job` VALUES ('meta_oer_harvester','Services/MetaData',1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ilCronOerHarvester',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('orgunit_paths','Modules/OrgUnit',1,0,1,0,0,1472816001,0,0,'','',0,0,'ilCronUpdateOrgUnitPaths','',0,0,0);
INSERT INTO `cron_job` VALUES ('prg_invalidate_expired_progresses','Modules/StudyProgramme',4,1,1,0,0,1605888798,NULL,NULL,NULL,NULL,NULL,NULL,'ilPrgInvalidateExpiredProgressesCronJob',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('prg_restart_assignments_temporal_progress','Modules/StudyProgramme',4,1,1,0,0,1605888798,NULL,NULL,NULL,NULL,NULL,NULL,'ilPrgRestartAssignmentsCronJob',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('prg_update_progress','Modules/StudyProgramme',4,1,1,0,0,1605888798,NULL,NULL,NULL,NULL,NULL,NULL,'ilPrgUpdateProgressCronJob',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('prg_user_not_restarted','Modules/StudyProgramme',4,1,1,0,0,1605888798,NULL,NULL,NULL,NULL,NULL,NULL,'ilPrgUserNotRestartedCronJob',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('prg_user_risky_to_fail','Modules/StudyProgramme',4,1,1,0,0,1605888798,NULL,NULL,NULL,NULL,NULL,NULL,'ilPrgUserRiskyToFailCronJob',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('skll_notification','Services/Skill',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ilSkillNotifications',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('src_lucene_indexer','Services/Search',1,0,0,0,0,0,0,0,'','',0,0,'ilLuceneIndexer','Services/Search/classes/Lucene/',0,0,0);
INSERT INTO `cron_job` VALUES ('survey_notification','Modules/Survey',0,0,1,0,0,1381511099,0,0,'','',0,0,'ilSurveyCronNotification','',0,0,0);
INSERT INTO `cron_job` VALUES ('sysc_trash','Services/SystemCheck',5,1,0,0,0,0,0,0,'','',0,0,'ilSCCronTrash','',0,0,0);
INSERT INTO `cron_job` VALUES ('user_check_accounts','Services/User',0,0,0,0,0,0,0,0,'','',0,0,'ilUserCronCheckAccounts','',0,0,0);
INSERT INTO `cron_job` VALUES ('user_inactivated','Services/User',1,0,0,0,0,0,0,0,'','',0,0,'ilCronDeleteInactivatedUserAccounts','',0,0,0);
INSERT INTO `cron_job` VALUES ('user_inactive','Services/User',1,0,0,0,0,0,0,0,'','',0,0,'ilCronDeleteInactiveUserAccounts','',0,0,0);
INSERT INTO `cron_job` VALUES ('user_never_logged_in','Services/User',1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ilCronDeleteNeverLoggedInUserAccounts',NULL,NULL,NULL,NULL);
INSERT INTO `cron_job` VALUES ('webr_link_check','Modules/WebResource',1,0,0,0,0,0,0,0,'','',0,0,'ilWebResourceCronLinkCheck','',0,0,0);
INSERT INTO `cron_job` VALUES ('xapi_results_evaluation','Modules/CmiXapi',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ilXapiResultsCronjob',NULL,NULL,NULL,NULL);

--
-- Table structure for table `crs_archives`
--

CREATE TABLE `crs_archives` (
  `archive_id` int(11) NOT NULL DEFAULT 0,
  `course_id` int(11) NOT NULL DEFAULT 0,
  `archive_name` varchar(255) DEFAULT NULL,
  `archive_type` tinyint(4) NOT NULL DEFAULT 0,
  `archive_date` int(11) DEFAULT NULL,
  `archive_size` int(11) DEFAULT NULL,
  `archive_lang` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`archive_id`)
) ;

--
-- Dumping data for table `crs_archives`
--


--
-- Table structure for table `crs_archives_seq`
--

CREATE TABLE `crs_archives_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `crs_archives_seq`
--


--
-- Table structure for table `crs_f_definitions`
--

CREATE TABLE `crs_f_definitions` (
  `field_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `field_name` varchar(255) DEFAULT NULL,
  `field_type` tinyint(4) NOT NULL DEFAULT 0,
  `field_values` longtext DEFAULT NULL,
  `field_required` tinyint(4) NOT NULL DEFAULT 0,
  `field_values_opt` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`field_id`)
) ;

--
-- Dumping data for table `crs_f_definitions`
--


--
-- Table structure for table `crs_f_definitions_seq`
--

CREATE TABLE `crs_f_definitions_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `crs_f_definitions_seq`
--


--
-- Table structure for table `crs_file`
--

CREATE TABLE `crs_file` (
  `file_id` int(11) NOT NULL DEFAULT 0,
  `course_id` int(11) NOT NULL DEFAULT 0,
  `file_name` char(64) DEFAULT NULL,
  `file_type` char(64) DEFAULT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`file_id`)
) ;

--
-- Dumping data for table `crs_file`
--


--
-- Table structure for table `crs_file_seq`
--

CREATE TABLE `crs_file_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `crs_file_seq`
--


--
-- Table structure for table `crs_groupings`
--

CREATE TABLE `crs_groupings` (
  `crs_grp_id` int(11) NOT NULL DEFAULT 0,
  `crs_ref_id` int(11) NOT NULL DEFAULT 0,
  `crs_id` int(11) NOT NULL DEFAULT 0,
  `unique_field` char(32) DEFAULT NULL,
  PRIMARY KEY (`crs_grp_id`),
  KEY `i1_idx` (`crs_id`)
) ;

--
-- Dumping data for table `crs_groupings`
--


--
-- Table structure for table `crs_items`
--

CREATE TABLE `crs_items` (
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `timing_type` tinyint(4) DEFAULT NULL,
  `timing_start` int(11) NOT NULL DEFAULT 0,
  `timing_end` int(11) NOT NULL DEFAULT 0,
  `suggestion_start` int(11) NOT NULL DEFAULT 0,
  `suggestion_end` int(11) NOT NULL DEFAULT 0,
  `changeable` tinyint(4) NOT NULL DEFAULT 0,
  `visible` tinyint(4) NOT NULL DEFAULT 0,
  `position` int(11) DEFAULT NULL,
  `suggestion_start_rel` int(11) DEFAULT 0,
  `suggestion_end_rel` int(11) DEFAULT 0,
  PRIMARY KEY (`parent_id`,`obj_id`),
  KEY `ob_idx` (`obj_id`)
) ;

--
-- Dumping data for table `crs_items`
--

INSERT INTO `crs_items` VALUES (1,9,1,1450792127,1450792127,1450792127,1450792127,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,8,1,1450792127,1450792127,1450792127,1450792127,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,12,1,1481898045,1481898045,1481898045,1481898045,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,16,1,1450795974,1450795974,1450795974,1450795974,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,17,1,1481897710,1481897710,1481897710,1481897710,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,18,1,1450795859,1450795859,1450795859,1450795859,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,22,1,1450796001,1450796001,1450796001,1450796001,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,23,1,1450795847,1450795847,1450795847,1450795847,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,29,1,1450795894,1450795894,1450795894,1450795894,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,30,1,1450796011,1450796011,1450796011,1450796011,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,32,1,1481899353,1481899353,1481899353,1481899353,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,34,1,1482324927,1482324927,1482324927,1482324927,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,36,1,1481898367,1481898367,1481898367,1481898367,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,37,1,1450796232,1450796232,1450796232,1450796232,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,39,1,1481899398,1481899398,1481899398,1481899398,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,52,1,1450795488,1450795488,1450795488,1450795488,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,53,1,1450796052,1450796052,1450796052,1450796052,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,55,1,1481899451,1481899451,1481899451,1481899451,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,57,1,1450796335,1450796335,1450796335,1450796335,0,0,0,0,0);
INSERT INTO `crs_items` VALUES (9,64,1,1450795969,1450795969,1450795969,1450795969,0,0,0,0,0);

--
-- Table structure for table `crs_lm_history`
--

CREATE TABLE `crs_lm_history` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `crs_ref_id` int(11) NOT NULL DEFAULT 0,
  `lm_ref_id` int(11) NOT NULL DEFAULT 0,
  `lm_page_id` int(11) NOT NULL DEFAULT 0,
  `last_access` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`,`crs_ref_id`,`lm_ref_id`)
) ;

--
-- Dumping data for table `crs_lm_history`
--


--
-- Table structure for table `crs_objective_lm`
--

CREATE TABLE `crs_objective_lm` (
  `lm_ass_id` int(11) NOT NULL DEFAULT 0,
  `objective_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `type` char(6) DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  PRIMARY KEY (`lm_ass_id`)
) ;

--
-- Dumping data for table `crs_objective_lm`
--


--
-- Table structure for table `crs_objective_lm_seq`
--

CREATE TABLE `crs_objective_lm_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `crs_objective_lm_seq`
--


--
-- Table structure for table `crs_objective_qst`
--

CREATE TABLE `crs_objective_qst` (
  `qst_ass_id` int(11) NOT NULL DEFAULT 0,
  `objective_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `question_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`qst_ass_id`)
) ;

--
-- Dumping data for table `crs_objective_qst`
--


--
-- Table structure for table `crs_objective_qst_seq`
--

CREATE TABLE `crs_objective_qst_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `crs_objective_qst_seq`
--


--
-- Table structure for table `crs_objective_status`
--

CREATE TABLE `crs_objective_status` (
  `objective_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`objective_id`,`user_id`)
) ;

--
-- Dumping data for table `crs_objective_status`
--


--
-- Table structure for table `crs_objective_status_p`
--

CREATE TABLE `crs_objective_status_p` (
  `objective_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`objective_id`,`user_id`)
) ;

--
-- Dumping data for table `crs_objective_status_p`
--


--
-- Table structure for table `crs_objective_tst`
--

CREATE TABLE `crs_objective_tst` (
  `test_objective_id` int(11) NOT NULL DEFAULT 0,
  `objective_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `tst_status` tinyint(4) DEFAULT NULL,
  `tst_limit` tinyint(4) DEFAULT NULL,
  `tst_limit_p` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`test_objective_id`)
) ;

--
-- Dumping data for table `crs_objective_tst`
--


--
-- Table structure for table `crs_objective_tst_seq`
--

CREATE TABLE `crs_objective_tst_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `crs_objective_tst_seq`
--


--
-- Table structure for table `crs_objectives`
--

CREATE TABLE `crs_objectives` (
  `crs_id` int(11) NOT NULL DEFAULT 0,
  `objective_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(70) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(4) DEFAULT 1,
  `passes` smallint(6) DEFAULT 0,
  PRIMARY KEY (`objective_id`),
  KEY `i1_idx` (`crs_id`)
) ;

--
-- Dumping data for table `crs_objectives`
--


--
-- Table structure for table `crs_objectives_seq`
--

CREATE TABLE `crs_objectives_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `crs_objectives_seq`
--


--
-- Table structure for table `crs_reference_settings`
--

CREATE TABLE `crs_reference_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `member_update` tinyint(4) NOT NULL DEFAULT 0
) ;

--
-- Dumping data for table `crs_reference_settings`
--


--
-- Table structure for table `crs_settings`
--

CREATE TABLE `crs_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `syllabus` varchar(4000) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_responsibility` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_consultation` varchar(4000) DEFAULT NULL,
  `activation_type` tinyint(4) NOT NULL DEFAULT 0,
  `activation_start` int(11) DEFAULT NULL,
  `activation_end` int(11) DEFAULT NULL,
  `sub_limitation_type` tinyint(4) NOT NULL DEFAULT 0,
  `sub_start` int(11) DEFAULT NULL,
  `sub_end` int(11) DEFAULT NULL,
  `sub_type` int(11) DEFAULT NULL,
  `sub_password` varchar(32) DEFAULT NULL,
  `sub_mem_limit` tinyint(4) NOT NULL DEFAULT 0,
  `sub_max_members` int(11) DEFAULT NULL,
  `sub_notify` int(11) DEFAULT NULL,
  `view_mode` tinyint(4) NOT NULL DEFAULT 0,
  `sortorder` int(11) DEFAULT NULL,
  `archive_start` int(11) DEFAULT NULL,
  `archive_end` int(11) DEFAULT NULL,
  `archive_type` int(11) DEFAULT NULL,
  `abo` tinyint(4) DEFAULT 1,
  `waiting_list` tinyint(4) NOT NULL DEFAULT 1,
  `important` varchar(4000) DEFAULT NULL,
  `show_members` tinyint(4) NOT NULL DEFAULT 1,
  `latitude` varchar(30) DEFAULT NULL,
  `longitude` varchar(30) DEFAULT NULL,
  `location_zoom` int(11) NOT NULL DEFAULT 0,
  `enable_course_map` tinyint(4) NOT NULL DEFAULT 0,
  `session_limit` tinyint(4) NOT NULL DEFAULT 0,
  `session_prev` bigint(20) NOT NULL DEFAULT -1,
  `session_next` bigint(20) NOT NULL DEFAULT -1,
  `reg_ac_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `reg_ac` varchar(32) DEFAULT NULL,
  `status_dt` tinyint(4) DEFAULT 2,
  `auto_notification` tinyint(4) NOT NULL DEFAULT 1,
  `mail_members_type` tinyint(4) DEFAULT 1,
  `crs_start` int(11) DEFAULT NULL,
  `crs_end` int(11) DEFAULT NULL,
  `leave_end` int(11) DEFAULT NULL,
  `auto_wait` tinyint(4) NOT NULL DEFAULT 0,
  `min_members` smallint(6) DEFAULT NULL,
  `show_members_export` int(11) DEFAULT NULL,
  `timing_mode` tinyint(4) DEFAULT 0,
  `period_start` datetime DEFAULT NULL,
  `period_end` datetime DEFAULT NULL,
  `period_time_indication` int(11) NOT NULL DEFAULT 0,
  `target_group` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `crs_settings`
--


--
-- Table structure for table `crs_start`
--

CREATE TABLE `crs_start` (
  `crs_start_id` int(11) NOT NULL DEFAULT 0,
  `crs_id` int(11) NOT NULL DEFAULT 0,
  `item_ref_id` int(11) NOT NULL DEFAULT 0,
  `pos` int(11) DEFAULT NULL,
  PRIMARY KEY (`crs_start_id`),
  KEY `i1_idx` (`crs_id`)
) ;

--
-- Dumping data for table `crs_start`
--


--
-- Table structure for table `crs_start_seq`
--

CREATE TABLE `crs_start_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `crs_start_seq`
--


--
-- Table structure for table `crs_timings_exceeded`
--

CREATE TABLE `crs_timings_exceeded` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `sent` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`ref_id`)
) ;

--
-- Dumping data for table `crs_timings_exceeded`
--


--
-- Table structure for table `crs_timings_planed`
--

CREATE TABLE `crs_timings_planed` (
  `item_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `planed_start` int(11) NOT NULL DEFAULT 0,
  `planed_end` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`item_id`,`usr_id`)
) ;

--
-- Dumping data for table `crs_timings_planed`
--


--
-- Table structure for table `crs_timings_started`
--

CREATE TABLE `crs_timings_started` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `sent` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`ref_id`)
) ;

--
-- Dumping data for table `crs_timings_started`
--


--
-- Table structure for table `crs_timings_user`
--

CREATE TABLE `crs_timings_user` (
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `sstart` int(11) NOT NULL DEFAULT 0,
  `ssend` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ref_id`,`usr_id`)
) ;

--
-- Dumping data for table `crs_timings_user`
--


--
-- Table structure for table `crs_timings_usr_accept`
--

CREATE TABLE `crs_timings_usr_accept` (
  `crs_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `accept` tinyint(4) NOT NULL DEFAULT 0,
  `remark` varchar(4000) DEFAULT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`crs_id`,`usr_id`)
) ;

--
-- Dumping data for table `crs_timings_usr_accept`
--


--
-- Table structure for table `crs_user_data`
--

CREATE TABLE `crs_user_data` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`usr_id`,`field_id`)
) ;

--
-- Dumping data for table `crs_user_data`
--


--
-- Table structure for table `crs_waiting_list`
--

CREATE TABLE `crs_waiting_list` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `sub_time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`usr_id`)
) ;

--
-- Dumping data for table `crs_waiting_list`
--


--
-- Table structure for table `ctrl_calls`
--

CREATE TABLE `ctrl_calls` (
  `parent` varchar(100) NOT NULL DEFAULT '',
  `child` varchar(100) NOT NULL DEFAULT '',
  `comp_prefix` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`parent`,`child`)
) ;

--
-- Dumping data for table `ctrl_calls`
--


--
-- Table structure for table `ctrl_classfile`
--

CREATE TABLE `ctrl_classfile` (
  `class` varchar(100) NOT NULL DEFAULT ' ',
  `filename` varchar(250) DEFAULT NULL,
  `comp_prefix` varchar(50) DEFAULT NULL,
  `plugin_path` varchar(250) DEFAULT NULL,
  `cid` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`class`),
  KEY `i1_idx` (`cid`)
) ;

--
-- Dumping data for table `ctrl_classfile`
--


--
-- Table structure for table `ctrl_structure`
--

CREATE TABLE `ctrl_structure` (
  `root_class` varchar(40) NOT NULL DEFAULT ' ',
  `call_node` longtext DEFAULT NULL,
  `forward` longtext DEFAULT NULL,
  `parent` longtext DEFAULT NULL,
  PRIMARY KEY (`root_class`)
) ;

--
-- Dumping data for table `ctrl_structure`
--


--
-- Table structure for table `data_cache`
--

CREATE TABLE `data_cache` (
  `module` varchar(50) NOT NULL DEFAULT 'common',
  `keyword` varchar(50) NOT NULL DEFAULT ' ',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`module`,`keyword`)
) ;

--
-- Dumping data for table `data_cache`
--


--
-- Table structure for table `dav_lock`
--

CREATE TABLE `dav_lock` (
  `token` varchar(255) NOT NULL DEFAULT ' ',
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `node_id` int(11) NOT NULL DEFAULT 0,
  `ilias_owner` int(11) NOT NULL DEFAULT 0,
  `dav_owner` varchar(200) DEFAULT NULL,
  `expires` int(11) NOT NULL DEFAULT 0,
  `depth` int(11) NOT NULL DEFAULT 0,
  `type` char(1) DEFAULT 'w',
  `scope` char(1) DEFAULT 's',
  PRIMARY KEY (`token`),
  UNIQUE KEY `c1_idx` (`token`),
  KEY `i1_idx` (`obj_id`,`node_id`),
  KEY `i2_idx` (`obj_id`,`node_id`,`token`),
  KEY `i3_idx` (`expires`)
) ;

--
-- Dumping data for table `dav_lock`
--


--
-- Table structure for table `dav_property`
--

CREATE TABLE `dav_property` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `node_id` int(11) NOT NULL DEFAULT 0,
  `ns` varchar(120) NOT NULL DEFAULT 'DAV:',
  `name` varchar(120) NOT NULL DEFAULT ' ',
  `value` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`node_id`,`name`,`ns`),
  KEY `i1_idx` (`obj_id`,`node_id`)
) ;

--
-- Dumping data for table `dav_property`
--


--
-- Table structure for table `dbk_translations`
--

CREATE TABLE `dbk_translations` (
  `id` int(11) NOT NULL DEFAULT 0,
  `tr_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`,`tr_id`)
) ;

--
-- Dumping data for table `dbk_translations`
--


--
-- Table structure for table `desktop_item`
--

CREATE TABLE `desktop_item` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `item_id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(4) DEFAULT NULL,
  `parameters` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`item_id`)
) ;

--
-- Dumping data for table `desktop_item`
--


--
-- Table structure for table `didactic_tpl_a`
--

CREATE TABLE `didactic_tpl_a` (
  `id` int(11) NOT NULL DEFAULT 0,
  `tpl_id` int(11) NOT NULL DEFAULT 0,
  `type_id` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `didactic_tpl_a`
--

INSERT INTO `didactic_tpl_a` VALUES (1,1,1);
INSERT INTO `didactic_tpl_a` VALUES (2,2,1);

--
-- Table structure for table `didactic_tpl_a_seq`
--

CREATE TABLE `didactic_tpl_a_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=3;

--
-- Dumping data for table `didactic_tpl_a_seq`
--

INSERT INTO `didactic_tpl_a_seq` VALUES (2);

--
-- Table structure for table `didactic_tpl_abr`
--

CREATE TABLE `didactic_tpl_abr` (
  `action_id` int(11) NOT NULL DEFAULT 0,
  `filter_type` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`action_id`)
) ;

--
-- Dumping data for table `didactic_tpl_abr`
--


--
-- Table structure for table `didactic_tpl_alp`
--

CREATE TABLE `didactic_tpl_alp` (
  `action_id` int(11) NOT NULL DEFAULT 0,
  `filter_type` tinyint(4) NOT NULL DEFAULT 0,
  `template_type` tinyint(4) NOT NULL DEFAULT 0,
  `template_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`action_id`)
) ;

--
-- Dumping data for table `didactic_tpl_alp`
--

INSERT INTO `didactic_tpl_alp` VALUES (1,3,2,82);
INSERT INTO `didactic_tpl_alp` VALUES (2,3,2,269);

--
-- Table structure for table `didactic_tpl_alr`
--

CREATE TABLE `didactic_tpl_alr` (
  `action_id` int(11) NOT NULL DEFAULT 0,
  `role_template_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`action_id`)
) ;

--
-- Dumping data for table `didactic_tpl_alr`
--


--
-- Table structure for table `didactic_tpl_en`
--

CREATE TABLE `didactic_tpl_en` (
  `id` int(11) NOT NULL DEFAULT 0,
  `node` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`,`node`)
) ;

--
-- Dumping data for table `didactic_tpl_en`
--


--
-- Table structure for table `didactic_tpl_fp`
--

CREATE TABLE `didactic_tpl_fp` (
  `pattern_id` int(11) NOT NULL DEFAULT 0,
  `pattern_type` tinyint(4) NOT NULL DEFAULT 0,
  `pattern_sub_type` tinyint(4) NOT NULL DEFAULT 0,
  `pattern` varchar(64) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `parent_type` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`pattern_id`)
) ;

--
-- Dumping data for table `didactic_tpl_fp`
--

INSERT INTO `didactic_tpl_fp` VALUES (1,1,1,'.*',1,'action');
INSERT INTO `didactic_tpl_fp` VALUES (2,1,1,'.*',2,'action');

--
-- Table structure for table `didactic_tpl_fp_seq`
--

CREATE TABLE `didactic_tpl_fp_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=3;

--
-- Dumping data for table `didactic_tpl_fp_seq`
--

INSERT INTO `didactic_tpl_fp_seq` VALUES (2);

--
-- Table structure for table `didactic_tpl_objs`
--

CREATE TABLE `didactic_tpl_objs` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `tpl_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ref_id`,`tpl_id`)
) ;

--
-- Dumping data for table `didactic_tpl_objs`
--


--
-- Table structure for table `didactic_tpl_sa`
--

CREATE TABLE `didactic_tpl_sa` (
  `id` int(11) NOT NULL DEFAULT 0,
  `obj_type` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`obj_type`)
) ;

--
-- Dumping data for table `didactic_tpl_sa`
--

INSERT INTO `didactic_tpl_sa` VALUES (1,'grp');
INSERT INTO `didactic_tpl_sa` VALUES (2,'sess');

--
-- Table structure for table `didactic_tpl_settings`
--

CREATE TABLE `didactic_tpl_settings` (
  `id` int(11) NOT NULL DEFAULT 0,
  `enabled` tinyint(4) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `title` varchar(64) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  `info` varchar(4000) DEFAULT NULL,
  `auto_generated` tinyint(4) NOT NULL DEFAULT 0,
  `exclusive_tpl` tinyint(4) NOT NULL DEFAULT 0,
  `icon_ide` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `didactic_tpl_settings`
--

INSERT INTO `didactic_tpl_settings` VALUES (1,1,1,'grp_closed','grp_closed_info','',1,0,NULL);
INSERT INTO `didactic_tpl_settings` VALUES (2,1,1,'sess_closed','sess_closed_info','',1,0,NULL);

--
-- Table structure for table `didactic_tpl_settings_seq`
--

CREATE TABLE `didactic_tpl_settings_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=3;

--
-- Dumping data for table `didactic_tpl_settings_seq`
--

INSERT INTO `didactic_tpl_settings_seq` VALUES (2);

--
-- Table structure for table `ecs_cmap_rule`
--

CREATE TABLE `ecs_cmap_rule` (
  `rid` int(11) NOT NULL DEFAULT 0,
  `sid` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `attribute` varchar(64) DEFAULT NULL,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `is_filter` tinyint(4) NOT NULL DEFAULT 0,
  `filter` varchar(512) DEFAULT NULL,
  `create_subdir` tinyint(4) NOT NULL DEFAULT 0,
  `subdir_type` tinyint(4) NOT NULL DEFAULT 0,
  `directory` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`rid`)
) ;

--
-- Dumping data for table `ecs_cmap_rule`
--


--
-- Table structure for table `ecs_cmap_rule_seq`
--

CREATE TABLE `ecs_cmap_rule_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ecs_cmap_rule_seq`
--


--
-- Table structure for table `ecs_cms_data`
--

CREATE TABLE `ecs_cms_data` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `server_id` int(11) DEFAULT NULL,
  `mid` int(11) DEFAULT NULL,
  `tree_id` int(11) DEFAULT NULL,
  `title` varchar(512) DEFAULT NULL,
  `term` varchar(255) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1,
  `deleted` tinyint(4) NOT NULL DEFAULT 0,
  `cms_id` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `ecs_cms_data`
--


--
-- Table structure for table `ecs_cms_data_seq`
--

CREATE TABLE `ecs_cms_data_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ecs_cms_data_seq`
--


--
-- Table structure for table `ecs_cms_tree`
--

CREATE TABLE `ecs_cms_tree` (
  `tree` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `rgt` int(11) DEFAULT NULL,
  `depth` int(11) DEFAULT NULL,
  PRIMARY KEY (`tree`,`child`)
) ;

--
-- Dumping data for table `ecs_cms_tree`
--


--
-- Table structure for table `ecs_community`
--

CREATE TABLE `ecs_community` (
  `sid` int(11) NOT NULL DEFAULT 0,
  `cid` int(11) NOT NULL DEFAULT 0,
  `own_id` int(11) NOT NULL DEFAULT 0,
  `cname` varchar(255) DEFAULT NULL,
  `mids` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`sid`,`cid`)
) ;

--
-- Dumping data for table `ecs_community`
--


--
-- Table structure for table `ecs_container_mapping`
--

CREATE TABLE `ecs_container_mapping` (
  `mapping_id` int(11) NOT NULL DEFAULT 0,
  `container_id` int(11) NOT NULL DEFAULT 0,
  `field_name` varchar(255) DEFAULT NULL,
  `mapping_type` tinyint(4) NOT NULL DEFAULT 0,
  `mapping_value` varchar(255) DEFAULT NULL,
  `date_range_start` int(11) NOT NULL DEFAULT 0,
  `date_range_end` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`mapping_id`)
) ;

--
-- Dumping data for table `ecs_container_mapping`
--


--
-- Table structure for table `ecs_container_mapping_seq`
--

CREATE TABLE `ecs_container_mapping_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ecs_container_mapping_seq`
--


--
-- Table structure for table `ecs_course_assignments`
--

CREATE TABLE `ecs_course_assignments` (
  `id` int(11) NOT NULL DEFAULT 0,
  `sid` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `cms_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` varchar(64) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `cms_sub_id` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `ecs_course_assignments`
--


--
-- Table structure for table `ecs_course_assignments_seq`
--

CREATE TABLE `ecs_course_assignments_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ecs_course_assignments_seq`
--


--
-- Table structure for table `ecs_crs_mapping_atts`
--

CREATE TABLE `ecs_crs_mapping_atts` (
  `id` int(11) NOT NULL DEFAULT 0,
  `sid` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `ecs_crs_mapping_atts`
--


--
-- Table structure for table `ecs_crs_mapping_atts_seq`
--

CREATE TABLE `ecs_crs_mapping_atts_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ecs_crs_mapping_atts_seq`
--


--
-- Table structure for table `ecs_data_mapping`
--

CREATE TABLE `ecs_data_mapping` (
  `sid` int(11) NOT NULL DEFAULT 0,
  `mapping_type` tinyint(4) NOT NULL DEFAULT 0,
  `ecs_field` varchar(32) NOT NULL DEFAULT '',
  `advmd_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`sid`,`mapping_type`,`ecs_field`)
) ;

--
-- Dumping data for table `ecs_data_mapping`
--


--
-- Table structure for table `ecs_events`
--

CREATE TABLE `ecs_events` (
  `event_id` int(11) NOT NULL DEFAULT 0,
  `type` char(32) DEFAULT NULL,
  `id` int(11) NOT NULL DEFAULT 0,
  `op` char(32) DEFAULT NULL,
  `server_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`event_id`)
) ;

--
-- Dumping data for table `ecs_events`
--


--
-- Table structure for table `ecs_events_seq`
--

CREATE TABLE `ecs_events_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ecs_events_seq`
--


--
-- Table structure for table `ecs_export`
--

CREATE TABLE `ecs_export` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `econtent_id` int(11) NOT NULL DEFAULT 0,
  `server_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`server_id`,`obj_id`)
) ;

--
-- Dumping data for table `ecs_export`
--


--
-- Table structure for table `ecs_import`
--

CREATE TABLE `ecs_import` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `server_id` int(11) NOT NULL DEFAULT 0,
  `sub_id` varchar(64) DEFAULT NULL,
  `ecs_id` int(11) DEFAULT 0,
  `content_id` varchar(255) DEFAULT NULL,
  `econtent_id` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`server_id`,`obj_id`)
) ;

--
-- Dumping data for table `ecs_import`
--


--
-- Table structure for table `ecs_node_mapping_a`
--

CREATE TABLE `ecs_node_mapping_a` (
  `server_id` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `cs_root` int(11) NOT NULL DEFAULT 0,
  `cs_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `title_update` tinyint(4) DEFAULT NULL,
  `position_update` tinyint(4) DEFAULT NULL,
  `tree_update` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`server_id`,`mid`,`cs_root`,`cs_id`)
) ;

--
-- Dumping data for table `ecs_node_mapping_a`
--


--
-- Table structure for table `ecs_part_settings`
--

CREATE TABLE `ecs_part_settings` (
  `sid` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `export` tinyint(4) NOT NULL DEFAULT 0,
  `import` tinyint(4) NOT NULL DEFAULT 0,
  `import_type` tinyint(4) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `token` tinyint(4) DEFAULT 1,
  `export_types` varchar(4000) DEFAULT NULL,
  `import_types` varchar(4000) DEFAULT NULL,
  `dtoken` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`sid`,`mid`)
) ;

--
-- Dumping data for table `ecs_part_settings`
--


--
-- Table structure for table `ecs_remote_user`
--

CREATE TABLE `ecs_remote_user` (
  `eru_id` int(11) NOT NULL DEFAULT 0,
  `sid` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `remote_usr_id` char(50) DEFAULT NULL,
  PRIMARY KEY (`eru_id`)
) ;

--
-- Dumping data for table `ecs_remote_user`
--


--
-- Table structure for table `ecs_remote_user_seq`
--

CREATE TABLE `ecs_remote_user_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ecs_remote_user_seq`
--


--
-- Table structure for table `ecs_server`
--

CREATE TABLE `ecs_server` (
  `server_id` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(4) DEFAULT 0,
  `protocol` tinyint(4) DEFAULT 1,
  `server` varchar(255) DEFAULT NULL,
  `port` smallint(6) DEFAULT 1,
  `auth_type` tinyint(4) DEFAULT 1,
  `client_cert_path` varchar(512) DEFAULT NULL,
  `ca_cert_path` varchar(512) DEFAULT NULL,
  `key_path` varchar(512) DEFAULT NULL,
  `key_password` varchar(32) DEFAULT NULL,
  `cert_serial` varchar(32) DEFAULT NULL,
  `polling_time` int(11) DEFAULT 0,
  `import_id` int(11) DEFAULT 0,
  `global_role` int(11) DEFAULT 0,
  `econtent_rcp` varchar(512) DEFAULT NULL,
  `user_rcp` varchar(512) DEFAULT NULL,
  `approval_rcp` varchar(512) DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `title` varchar(128) DEFAULT NULL,
  `auth_user` varchar(32) DEFAULT NULL,
  `auth_pass` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`server_id`)
) ;

--
-- Dumping data for table `ecs_server`
--


--
-- Table structure for table `ecs_server_seq`
--

CREATE TABLE `ecs_server_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ecs_server_seq`
--


--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `event_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(70) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `location` varchar(4000) DEFAULT NULL,
  `tutor_name` varchar(4000) DEFAULT NULL,
  `tutor_email` varchar(127) DEFAULT NULL,
  `tutor_phone` varchar(127) DEFAULT NULL,
  `details` varchar(4000) DEFAULT NULL,
  `registration` tinyint(4) NOT NULL DEFAULT 0,
  `participation` tinyint(4) NOT NULL DEFAULT 0,
  `reg_type` smallint(6) DEFAULT 0,
  `reg_limit_users` int(11) DEFAULT 0,
  `reg_waiting_list` tinyint(4) DEFAULT 0,
  `reg_limited` tinyint(4) DEFAULT 0,
  `reg_min_users` smallint(6) DEFAULT NULL,
  `reg_auto_wait` tinyint(4) NOT NULL DEFAULT 0,
  `show_members` tinyint(4) NOT NULL DEFAULT 0,
  `mail_members` tinyint(4) NOT NULL DEFAULT 0,
  `reg_notification` int(11) NOT NULL DEFAULT 0,
  `notification_opt` varchar(50) DEFAULT 'notification_option_manual',
  `show_cannot_part` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`event_id`),
  KEY `i1_idx` (`obj_id`)
) ;

--
-- Dumping data for table `event`
--


--
-- Table structure for table `event_appointment`
--

CREATE TABLE `event_appointment` (
  `appointment_id` int(11) NOT NULL DEFAULT 0,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `e_start` datetime DEFAULT NULL,
  `e_end` datetime DEFAULT NULL,
  `starting_time` int(11) NOT NULL DEFAULT 0,
  `ending_time` int(11) NOT NULL DEFAULT 0,
  `fulltime` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`appointment_id`),
  KEY `i1_idx` (`event_id`)
) ;

--
-- Dumping data for table `event_appointment`
--


--
-- Table structure for table `event_appointment_seq`
--

CREATE TABLE `event_appointment_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `event_appointment_seq`
--


--
-- Table structure for table `event_file`
--

CREATE TABLE `event_file` (
  `file_id` int(11) NOT NULL DEFAULT 0,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `file_name` char(64) DEFAULT NULL,
  `file_type` char(64) DEFAULT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`file_id`)
) ;

--
-- Dumping data for table `event_file`
--


--
-- Table structure for table `event_file_seq`
--

CREATE TABLE `event_file_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `event_file_seq`
--


--
-- Table structure for table `event_items`
--

CREATE TABLE `event_items` (
  `event_id` int(11) NOT NULL DEFAULT 0,
  `item_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`event_id`,`item_id`),
  KEY `i1_idx` (`event_id`)
) ;

--
-- Dumping data for table `event_items`
--


--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `event_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `registered` tinyint(4) NOT NULL DEFAULT 0,
  `participated` tinyint(4) NOT NULL DEFAULT 0,
  `mark` longtext DEFAULT NULL,
  `e_comment` longtext DEFAULT NULL,
  `contact` tinyint(4) NOT NULL DEFAULT 0,
  `notification_enabled` int(11) NOT NULL DEFAULT 0,
  `excused` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`event_id`,`usr_id`)
) ;

--
-- Dumping data for table `event_participants`
--


--
-- Table structure for table `event_seq`
--

CREATE TABLE `event_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `event_seq`
--


--
-- Table structure for table `exc_ass_file_order`
--

CREATE TABLE `exc_ass_file_order` (
  `id` int(11) NOT NULL DEFAULT 0,
  `assignment_id` int(11) NOT NULL DEFAULT 0,
  `filename` varchar(150) NOT NULL,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`assignment_id`)
) ;

--
-- Dumping data for table `exc_ass_file_order`
--


--
-- Table structure for table `exc_ass_file_order_seq`
--

CREATE TABLE `exc_ass_file_order_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `exc_ass_file_order_seq`
--


--
-- Table structure for table `exc_ass_reminders`
--

CREATE TABLE `exc_ass_reminders` (
  `type` varchar(32) NOT NULL,
  `ass_id` int(11) NOT NULL,
  `exc_id` int(11) NOT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `start` int(11) DEFAULT NULL,
  `end` int(11) DEFAULT NULL,
  `freq` int(11) DEFAULT NULL,
  `last_send` int(11) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `last_send_day` date DEFAULT NULL,
  PRIMARY KEY (`ass_id`,`exc_id`,`type`)
) ;

--
-- Dumping data for table `exc_ass_reminders`
--


--
-- Table structure for table `exc_ass_wiki_team`
--

CREATE TABLE `exc_ass_wiki_team` (
  `id` int(11) NOT NULL DEFAULT 0,
  `container_ref_id` int(11) NOT NULL DEFAULT 0,
  `template_ref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `exc_ass_wiki_team`
--


--
-- Table structure for table `exc_assignment`
--

CREATE TABLE `exc_assignment` (
  `id` int(11) NOT NULL DEFAULT 0,
  `exc_id` int(11) NOT NULL DEFAULT 0,
  `time_stamp` int(11) DEFAULT NULL,
  `instruction` longtext DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `start_time` int(11) DEFAULT NULL,
  `mandatory` tinyint(4) DEFAULT 1,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 1,
  `peer` tinyint(4) NOT NULL DEFAULT 0,
  `peer_min` smallint(6) NOT NULL DEFAULT 0,
  `fb_file` varchar(1000) DEFAULT NULL,
  `fb_cron` tinyint(4) NOT NULL DEFAULT 0,
  `fb_cron_done` tinyint(4) NOT NULL DEFAULT 0,
  `peer_dl` int(11) DEFAULT 0,
  `peer_file` tinyint(4) DEFAULT 0,
  `peer_prsl` tinyint(4) DEFAULT 0,
  `fb_date` tinyint(4) NOT NULL DEFAULT 1,
  `peer_char` smallint(6) DEFAULT NULL,
  `peer_unlock` tinyint(4) NOT NULL DEFAULT 0,
  `peer_valid` tinyint(4) NOT NULL DEFAULT 1,
  `team_tutor` tinyint(4) NOT NULL DEFAULT 0,
  `max_file` tinyint(4) DEFAULT NULL,
  `deadline2` int(11) DEFAULT NULL,
  `peer_text` tinyint(4) NOT NULL DEFAULT 1,
  `peer_rating` tinyint(4) NOT NULL DEFAULT 1,
  `peer_crit_cat` int(11) DEFAULT NULL,
  `portfolio_template` int(11) DEFAULT NULL,
  `min_char_limit` int(11) DEFAULT NULL,
  `max_char_limit` int(11) DEFAULT NULL,
  `fb_date_custom` int(11) DEFAULT NULL,
  `rmd_submit_status` tinyint(4) DEFAULT NULL,
  `rmd_submit_start` int(11) DEFAULT NULL,
  `rmd_submit_end` int(11) DEFAULT NULL,
  `rmd_submit_freq` int(11) DEFAULT NULL,
  `rmd_grade_status` tinyint(4) DEFAULT NULL,
  `rmd_grade_start` int(11) DEFAULT NULL,
  `rmd_grade_end` int(11) DEFAULT NULL,
  `rmd_grade_freq` int(11) DEFAULT NULL,
  `peer_rmd_status` tinyint(4) DEFAULT NULL,
  `peer_rmd_start` int(11) DEFAULT NULL,
  `peer_rmd_end` int(11) DEFAULT NULL,
  `peer_rmd_freq` int(11) DEFAULT NULL,
  `deadline_mode` tinyint(4) DEFAULT 0,
  `relative_deadline` int(11) DEFAULT 0,
  `rel_deadline_last_subm` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`exc_id`),
  KEY `i2_idx` (`deadline_mode`,`exc_id`)
) ;

--
-- Dumping data for table `exc_assignment`
--


--
-- Table structure for table `exc_assignment_peer`
--

CREATE TABLE `exc_assignment_peer` (
  `ass_id` int(11) NOT NULL DEFAULT 0,
  `giver_id` int(11) NOT NULL DEFAULT 0,
  `peer_id` int(11) NOT NULL DEFAULT 0,
  `tstamp` datetime DEFAULT NULL,
  `pcomment` longtext DEFAULT NULL,
  `is_valid` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ass_id`,`giver_id`,`peer_id`)
) ;

--
-- Dumping data for table `exc_assignment_peer`
--


--
-- Table structure for table `exc_assignment_seq`
--

CREATE TABLE `exc_assignment_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `exc_assignment_seq`
--


--
-- Table structure for table `exc_crit`
--

CREATE TABLE `exc_crit` (
  `id` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) NOT NULL DEFAULT 0,
  `type` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `descr` varchar(1000) DEFAULT NULL,
  `pos` int(11) NOT NULL DEFAULT 0,
  `required` tinyint(4) NOT NULL DEFAULT 0,
  `def` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `exc_crit`
--


--
-- Table structure for table `exc_crit_cat`
--

CREATE TABLE `exc_crit_cat` (
  `id` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `pos` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `exc_crit_cat`
--


--
-- Table structure for table `exc_crit_cat_seq`
--

CREATE TABLE `exc_crit_cat_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `exc_crit_cat_seq`
--


--
-- Table structure for table `exc_crit_seq`
--

CREATE TABLE `exc_crit_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `exc_crit_seq`
--


--
-- Table structure for table `exc_data`
--

CREATE TABLE `exc_data` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `instruction` longtext DEFAULT NULL,
  `time_stamp` int(11) DEFAULT NULL,
  `pass_mode` varchar(8) NOT NULL DEFAULT 'all',
  `pass_nr` int(11) DEFAULT NULL,
  `show_submissions` tinyint(4) NOT NULL DEFAULT 0,
  `compl_by_submission` tinyint(4) NOT NULL DEFAULT 0,
  `certificate_visibility` tinyint(4) NOT NULL DEFAULT 0,
  `tfeedback` tinyint(4) NOT NULL DEFAULT 7,
  `nr_mandatory_random` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `exc_data`
--


--
-- Table structure for table `exc_idl`
--

CREATE TABLE `exc_idl` (
  `ass_id` int(11) NOT NULL DEFAULT 0,
  `member_id` int(11) NOT NULL DEFAULT 0,
  `is_team` tinyint(4) NOT NULL DEFAULT 0,
  `tstamp` int(11) DEFAULT 0,
  `starting_ts` int(11) DEFAULT 0,
  PRIMARY KEY (`ass_id`,`member_id`,`is_team`)
) ;

--
-- Dumping data for table `exc_idl`
--


--
-- Table structure for table `exc_mandatory_random`
--

CREATE TABLE `exc_mandatory_random` (
  `exc_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `ass_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`exc_id`,`usr_id`,`ass_id`)
) ;

--
-- Dumping data for table `exc_mandatory_random`
--


--
-- Table structure for table `exc_mem_ass_status`
--

CREATE TABLE `exc_mem_ass_status` (
  `ass_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `notice` varchar(4000) DEFAULT NULL,
  `returned` tinyint(4) NOT NULL DEFAULT 0,
  `solved` tinyint(4) DEFAULT NULL,
  `status_time` datetime DEFAULT NULL,
  `sent` tinyint(4) DEFAULT NULL,
  `sent_time` datetime DEFAULT NULL,
  `feedback_time` datetime DEFAULT NULL,
  `feedback` tinyint(4) NOT NULL DEFAULT 0,
  `status` char(9) DEFAULT 'notgraded',
  `mark` varchar(32) DEFAULT NULL,
  `u_comment` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`ass_id`,`usr_id`)
) ;

--
-- Dumping data for table `exc_mem_ass_status`
--


--
-- Table structure for table `exc_members`
--

CREATE TABLE `exc_members` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `notice` varchar(4000) DEFAULT NULL,
  `returned` tinyint(4) NOT NULL DEFAULT 0,
  `solved` tinyint(4) DEFAULT NULL,
  `status_time` datetime DEFAULT NULL,
  `sent` tinyint(4) DEFAULT NULL,
  `sent_time` datetime DEFAULT NULL,
  `feedback_time` datetime DEFAULT NULL,
  `feedback` tinyint(4) NOT NULL DEFAULT 0,
  `status` char(9) DEFAULT 'notgraded',
  PRIMARY KEY (`obj_id`,`usr_id`),
  KEY `ob_idx` (`obj_id`),
  KEY `i1_idx` (`usr_id`)
) ;

--
-- Dumping data for table `exc_members`
--


--
-- Table structure for table `exc_returned`
--

CREATE TABLE `exc_returned` (
  `returned_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `filename` varchar(1000) DEFAULT NULL,
  `filetitle` varchar(1000) DEFAULT NULL,
  `mimetype` varchar(150) DEFAULT NULL,
  `ts` datetime DEFAULT NULL,
  `ass_id` int(11) DEFAULT NULL,
  `atext` longtext DEFAULT NULL,
  `late` tinyint(4) NOT NULL DEFAULT 0,
  `team_id` int(11) NOT NULL DEFAULT 0,
  `web_dir_access_time` datetime DEFAULT NULL,
  PRIMARY KEY (`returned_id`),
  KEY `i1_idx` (`obj_id`),
  KEY `i2_idx` (`user_id`),
  KEY `i3_idx` (`filetitle`(333))
) ;

--
-- Dumping data for table `exc_returned`
--


--
-- Table structure for table `exc_returned_seq`
--

CREATE TABLE `exc_returned_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `exc_returned_seq`
--


--
-- Table structure for table `exc_usr_tutor`
--

CREATE TABLE `exc_usr_tutor` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `tutor_id` int(11) NOT NULL DEFAULT 0,
  `download_time` datetime DEFAULT NULL,
  `ass_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ass_id`,`usr_id`,`tutor_id`),
  KEY `ob_idx` (`obj_id`)
) ;

--
-- Dumping data for table `exc_usr_tutor`
--


--
-- Table structure for table `export_file_info`
--

CREATE TABLE `export_file_info` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `export_type` varchar(32) NOT NULL DEFAULT '',
  `file_name` varchar(64) NOT NULL DEFAULT '',
  `version` varchar(16) DEFAULT NULL,
  `create_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `filename` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`obj_id`,`export_type`,`filename`),
  KEY `i1_idx` (`create_date`)
) ;

--
-- Dumping data for table `export_file_info`
--


--
-- Table structure for table `export_options`
--

CREATE TABLE `export_options` (
  `export_id` smallint(6) NOT NULL DEFAULT 0,
  `keyword` smallint(6) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `value` varchar(32) DEFAULT NULL,
  `pos` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`export_id`,`keyword`,`ref_id`)
) ;

--
-- Dumping data for table `export_options`
--


--
-- Table structure for table `feedback_items_seq`
--

CREATE TABLE `feedback_items_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `feedback_items_seq`
--


--
-- Table structure for table `file_based_lm`
--

CREATE TABLE `file_based_lm` (
  `id` int(11) NOT NULL DEFAULT 0,
  `is_online` char(1) DEFAULT 'n',
  `startfile` varchar(200) DEFAULT NULL,
  `show_lic` tinyint(4) DEFAULT NULL,
  `show_bib` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `file_based_lm`
--


--
-- Table structure for table `file_data`
--

CREATE TABLE `file_data` (
  `file_id` int(11) NOT NULL DEFAULT 0,
  `file_name` char(250) DEFAULT NULL,
  `file_type` char(250) DEFAULT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `version` int(11) DEFAULT NULL,
  `f_mode` char(8) DEFAULT 'object',
  `rating` tinyint(4) NOT NULL DEFAULT 0,
  `page_count` bigint(20) DEFAULT NULL,
  `max_version` int(11) DEFAULT NULL,
  `rid` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`file_id`),
  KEY `i1_idx` (`rid`)
) ;

--
-- Dumping data for table `file_data`
--


--
-- Table structure for table `file_usage`
--

CREATE TABLE `file_usage` (
  `id` int(11) NOT NULL DEFAULT 0,
  `usage_type` varchar(10) NOT NULL DEFAULT ' ',
  `usage_id` int(11) NOT NULL DEFAULT 0,
  `usage_hist_nr` int(11) NOT NULL DEFAULT 0,
  `usage_lang` varchar(2) NOT NULL DEFAULT '-',
  PRIMARY KEY (`id`,`usage_type`,`usage_id`,`usage_hist_nr`,`usage_lang`)
) ;

--
-- Dumping data for table `file_usage`
--


--
-- Table structure for table `frm_data`
--

CREATE TABLE `frm_data` (
  `top_pk` bigint(20) NOT NULL DEFAULT 0,
  `top_frm_fk` bigint(20) NOT NULL DEFAULT 0,
  `top_name` varchar(255) DEFAULT NULL,
  `top_description` varchar(255) DEFAULT NULL,
  `top_num_posts` int(11) NOT NULL DEFAULT 0,
  `top_num_threads` int(11) NOT NULL DEFAULT 0,
  `top_last_post` varchar(50) DEFAULT NULL,
  `top_mods` int(11) NOT NULL DEFAULT 0,
  `top_date` datetime DEFAULT NULL,
  `visits` int(11) NOT NULL DEFAULT 0,
  `top_update` datetime DEFAULT NULL,
  `update_user` int(11) NOT NULL DEFAULT 0,
  `top_usr_id` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`top_pk`),
  KEY `i1_idx` (`top_frm_fk`)
) ;

--
-- Dumping data for table `frm_data`
--


--
-- Table structure for table `frm_data_seq`
--

CREATE TABLE `frm_data_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `frm_data_seq`
--


--
-- Table structure for table `frm_drafts_history`
--

CREATE TABLE `frm_drafts_history` (
  `history_id` int(11) NOT NULL DEFAULT 0,
  `draft_id` int(11) NOT NULL DEFAULT 0,
  `post_subject` varchar(4000) NOT NULL DEFAULT '',
  `post_message` longtext NOT NULL,
  `draft_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`history_id`),
  KEY `i1_idx` (`draft_id`)
) ;

--
-- Dumping data for table `frm_drafts_history`
--


--
-- Table structure for table `frm_drafts_history_seq`
--

CREATE TABLE `frm_drafts_history_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `frm_drafts_history_seq`
--


--
-- Table structure for table `frm_notification`
--

CREATE TABLE `frm_notification` (
  `notification_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `frm_id` bigint(20) NOT NULL DEFAULT 0,
  `thread_id` bigint(20) NOT NULL DEFAULT 0,
  `admin_force_noti` tinyint(4) NOT NULL DEFAULT 0,
  `user_toggle_noti` tinyint(4) NOT NULL DEFAULT 0,
  `user_id_noti` int(11) DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `i1_idx` (`user_id`,`thread_id`)
) ;

--
-- Dumping data for table `frm_notification`
--


--
-- Table structure for table `frm_notification_seq`
--

CREATE TABLE `frm_notification_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `frm_notification_seq`
--


--
-- Table structure for table `frm_posts`
--

CREATE TABLE `frm_posts` (
  `pos_pk` bigint(20) NOT NULL DEFAULT 0,
  `pos_top_fk` bigint(20) NOT NULL DEFAULT 0,
  `pos_thr_fk` bigint(20) NOT NULL DEFAULT 0,
  `pos_usr_alias` varchar(255) DEFAULT NULL,
  `pos_subject` varchar(4000) DEFAULT NULL,
  `pos_date` datetime DEFAULT NULL,
  `pos_update` datetime DEFAULT NULL,
  `update_user` int(11) NOT NULL DEFAULT 0,
  `pos_cens` tinyint(4) NOT NULL DEFAULT 0,
  `pos_cens_com` varchar(4000) DEFAULT NULL,
  `notify` tinyint(4) NOT NULL DEFAULT 0,
  `import_name` varchar(4000) DEFAULT NULL,
  `pos_status` tinyint(4) NOT NULL DEFAULT 1,
  `pos_message` longtext DEFAULT NULL,
  `pos_author_id` int(11) NOT NULL DEFAULT 0,
  `pos_display_user_id` int(11) NOT NULL DEFAULT 0,
  `is_author_moderator` tinyint(4) DEFAULT NULL,
  `pos_cens_date` datetime DEFAULT NULL,
  `pos_activation_date` datetime DEFAULT NULL,
  PRIMARY KEY (`pos_pk`),
  KEY `i1_idx` (`pos_thr_fk`),
  KEY `i2_idx` (`pos_top_fk`),
  KEY `i3_idx` (`pos_date`),
  KEY `i5_idx` (`pos_thr_fk`,`pos_date`)
) ;

--
-- Dumping data for table `frm_posts`
--


--
-- Table structure for table `frm_posts_deleted`
--

CREATE TABLE `frm_posts_deleted` (
  `deleted_id` int(11) NOT NULL DEFAULT 0,
  `deleted_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `deleted_by` varchar(255) NOT NULL DEFAULT '',
  `forum_title` varchar(255) NOT NULL DEFAULT '',
  `thread_title` varchar(255) NOT NULL DEFAULT '',
  `post_title` varchar(255) NOT NULL DEFAULT '',
  `post_message` longtext NOT NULL,
  `post_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `thread_id` int(11) NOT NULL DEFAULT 0,
  `forum_id` int(11) NOT NULL DEFAULT 0,
  `pos_display_user_id` int(11) NOT NULL DEFAULT 0,
  `pos_usr_alias` varchar(255) DEFAULT NULL,
  `is_thread_deleted` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`deleted_id`)
) ;

--
-- Dumping data for table `frm_posts_deleted`
--


--
-- Table structure for table `frm_posts_deleted_seq`
--

CREATE TABLE `frm_posts_deleted_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `frm_posts_deleted_seq`
--


--
-- Table structure for table `frm_posts_drafts`
--

CREATE TABLE `frm_posts_drafts` (
  `draft_id` int(11) NOT NULL DEFAULT 0,
  `post_id` bigint(20) NOT NULL DEFAULT 0,
  `thread_id` bigint(20) NOT NULL DEFAULT 0,
  `forum_id` bigint(20) NOT NULL DEFAULT 0,
  `post_author_id` int(11) NOT NULL DEFAULT 0,
  `post_subject` varchar(4000) NOT NULL DEFAULT '',
  `post_message` longtext NOT NULL,
  `post_notify` tinyint(4) NOT NULL DEFAULT 0,
  `post_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `post_update` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `update_user_id` int(11) NOT NULL DEFAULT 0,
  `post_user_alias` varchar(255) DEFAULT NULL,
  `pos_display_usr_id` int(11) NOT NULL DEFAULT 0,
  `notify` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`draft_id`),
  KEY `i1_idx` (`post_id`),
  KEY `i2_idx` (`thread_id`),
  KEY `i3_idx` (`forum_id`)
) ;

--
-- Dumping data for table `frm_posts_drafts`
--


--
-- Table structure for table `frm_posts_drafts_seq`
--

CREATE TABLE `frm_posts_drafts_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `frm_posts_drafts_seq`
--


--
-- Table structure for table `frm_posts_seq`
--

CREATE TABLE `frm_posts_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `frm_posts_seq`
--


--
-- Table structure for table `frm_posts_tree`
--

CREATE TABLE `frm_posts_tree` (
  `fpt_pk` bigint(20) NOT NULL DEFAULT 0,
  `thr_fk` bigint(20) NOT NULL DEFAULT 0,
  `pos_fk` bigint(20) NOT NULL DEFAULT 0,
  `parent_pos` bigint(20) NOT NULL DEFAULT 0,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` int(11) NOT NULL DEFAULT 0,
  `fpt_date` datetime DEFAULT NULL,
  PRIMARY KEY (`fpt_pk`),
  KEY `i1_idx` (`thr_fk`),
  KEY `i2_idx` (`pos_fk`),
  KEY `i3_idx` (`parent_pos`)
) ;

--
-- Dumping data for table `frm_posts_tree`
--


--
-- Table structure for table `frm_posts_tree_seq`
--

CREATE TABLE `frm_posts_tree_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `frm_posts_tree_seq`
--


--
-- Table structure for table `frm_settings`
--

CREATE TABLE `frm_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `default_view` int(11) NOT NULL DEFAULT 0,
  `anonymized` tinyint(4) NOT NULL DEFAULT 0,
  `statistics_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `post_activation` tinyint(4) NOT NULL DEFAULT 0,
  `admin_force_noti` tinyint(4) NOT NULL DEFAULT 0,
  `user_toggle_noti` tinyint(4) NOT NULL DEFAULT 0,
  `preset_subject` tinyint(4) NOT NULL DEFAULT 1,
  `notification_type` varchar(10) DEFAULT NULL,
  `add_re_subject` tinyint(4) NOT NULL DEFAULT 0,
  `mark_mod_posts` tinyint(4) NOT NULL DEFAULT 0,
  `thread_sorting` int(11) NOT NULL DEFAULT 0,
  `thread_rating` tinyint(4) NOT NULL DEFAULT 0,
  `file_upload_allowed` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `frm_settings`
--


--
-- Table structure for table `frm_thread_access`
--

CREATE TABLE `frm_thread_access` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `thread_id` int(11) NOT NULL DEFAULT 0,
  `access_old` int(11) NOT NULL DEFAULT 0,
  `access_last` int(11) NOT NULL DEFAULT 0,
  `access_old_ts` datetime DEFAULT NULL,
  PRIMARY KEY (`usr_id`,`obj_id`,`thread_id`),
  KEY `i1_idx` (`access_last`)
) ;

--
-- Dumping data for table `frm_thread_access`
--


--
-- Table structure for table `frm_threads`
--

CREATE TABLE `frm_threads` (
  `thr_pk` bigint(20) NOT NULL DEFAULT 0,
  `thr_top_fk` bigint(20) NOT NULL DEFAULT 0,
  `thr_subject` varchar(255) DEFAULT NULL,
  `thr_usr_alias` varchar(255) DEFAULT NULL,
  `thr_num_posts` int(11) NOT NULL DEFAULT 0,
  `thr_last_post` varchar(50) DEFAULT NULL,
  `thr_date` datetime DEFAULT NULL,
  `thr_update` datetime DEFAULT NULL,
  `visits` int(11) NOT NULL DEFAULT 0,
  `import_name` varchar(4000) DEFAULT NULL,
  `is_sticky` tinyint(4) NOT NULL DEFAULT 0,
  `is_closed` tinyint(4) NOT NULL DEFAULT 0,
  `thread_sorting` int(11) NOT NULL DEFAULT 0,
  `avg_rating` double NOT NULL DEFAULT 0,
  `thr_author_id` int(11) NOT NULL DEFAULT 0,
  `thr_display_user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`thr_pk`),
  KEY `i2_idx` (`thr_top_fk`)
) ;

--
-- Dumping data for table `frm_threads`
--


--
-- Table structure for table `frm_threads_seq`
--

CREATE TABLE `frm_threads_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `frm_threads_seq`
--


--
-- Table structure for table `frm_user_read`
--

CREATE TABLE `frm_user_read` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `thread_id` int(11) NOT NULL DEFAULT 0,
  `post_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`,`obj_id`,`thread_id`,`post_id`),
  KEY `i1_idx` (`usr_id`,`post_id`)
) ;

--
-- Dumping data for table `frm_user_read`
--


--
-- Table structure for table `glo_advmd_col_order`
--

CREATE TABLE `glo_advmd_col_order` (
  `glo_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`glo_id`,`field_id`)
) ;

--
-- Dumping data for table `glo_advmd_col_order`
--


--
-- Table structure for table `glo_glossaries`
--

CREATE TABLE `glo_glossaries` (
  `id` int(11) NOT NULL DEFAULT 0,
  `glo_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`,`glo_id`)
) ;

--
-- Dumping data for table `glo_glossaries`
--


--
-- Table structure for table `glo_term_reference`
--

CREATE TABLE `glo_term_reference` (
  `glo_id` int(11) NOT NULL DEFAULT 0,
  `term_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`glo_id`,`term_id`)
) ;

--
-- Dumping data for table `glo_term_reference`
--


--
-- Table structure for table `glossary`
--

CREATE TABLE `glossary` (
  `id` int(11) NOT NULL DEFAULT 0,
  `is_online` char(1) DEFAULT 'n',
  `virtual` char(7) DEFAULT 'none',
  `public_html_file` varchar(50) DEFAULT NULL,
  `public_xml_file` varchar(50) DEFAULT NULL,
  `glo_menu_active` char(1) DEFAULT 'y',
  `downloads_active` char(1) DEFAULT 'n',
  `pres_mode` varchar(10) NOT NULL DEFAULT 'table',
  `snippet_length` int(11) NOT NULL DEFAULT 200,
  `show_tax` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `glossary`
--


--
-- Table structure for table `glossary_definition`
--

CREATE TABLE `glossary_definition` (
  `id` int(11) NOT NULL DEFAULT 0,
  `term_id` int(11) NOT NULL DEFAULT 0,
  `short_text` varchar(4000) DEFAULT NULL,
  `nr` int(11) NOT NULL DEFAULT 0,
  `short_text_dirty` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `glossary_definition`
--


--
-- Table structure for table `glossary_definition_seq`
--

CREATE TABLE `glossary_definition_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `glossary_definition_seq`
--


--
-- Table structure for table `glossary_term`
--

CREATE TABLE `glossary_term` (
  `id` int(11) NOT NULL DEFAULT 0,
  `glo_id` int(11) NOT NULL DEFAULT 0,
  `term` varchar(200) DEFAULT NULL,
  `language` char(2) DEFAULT NULL,
  `import_id` varchar(50) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`glo_id`)
) ;

--
-- Dumping data for table `glossary_term`
--


--
-- Table structure for table `glossary_term_seq`
--

CREATE TABLE `glossary_term_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `glossary_term_seq`
--


--
-- Table structure for table `grp_settings`
--

CREATE TABLE `grp_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `information` varchar(4000) DEFAULT NULL,
  `grp_type` tinyint(4) NOT NULL DEFAULT 0,
  `registration_type` tinyint(4) NOT NULL DEFAULT 0,
  `registration_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `registration_unlimited` tinyint(4) NOT NULL DEFAULT 0,
  `registration_start` datetime DEFAULT NULL,
  `registration_end` datetime DEFAULT NULL,
  `registration_password` char(32) DEFAULT NULL,
  `registration_mem_limit` tinyint(4) NOT NULL DEFAULT 0,
  `registration_max_members` int(11) NOT NULL DEFAULT 0,
  `waiting_list` tinyint(4) NOT NULL DEFAULT 0,
  `latitude` varchar(30) DEFAULT NULL,
  `longitude` varchar(30) DEFAULT NULL,
  `location_zoom` int(11) NOT NULL DEFAULT 0,
  `enablemap` tinyint(4) NOT NULL DEFAULT 0,
  `reg_ac_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `reg_ac` varchar(32) DEFAULT NULL,
  `view_mode` tinyint(4) NOT NULL DEFAULT 6,
  `mail_members_type` tinyint(4) DEFAULT 1,
  `registration_min_members` smallint(6) DEFAULT NULL,
  `leave_end` int(11) DEFAULT NULL,
  `auto_wait` tinyint(4) NOT NULL DEFAULT 0,
  `show_members` tinyint(4) NOT NULL DEFAULT 1,
  `grp_start` int(11) DEFAULT NULL,
  `grp_end` int(11) DEFAULT NULL,
  `period_start` datetime DEFAULT NULL,
  `period_end` datetime DEFAULT NULL,
  `period_time_indication` int(11) NOT NULL DEFAULT 0,
  `auto_notification` int(11) NOT NULL DEFAULT 1,
  `session_limit` tinyint(4) NOT NULL DEFAULT 0,
  `session_prev` bigint(20) NOT NULL DEFAULT -1,
  `session_next` bigint(20) NOT NULL DEFAULT -1,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `grp_settings`
--


--
-- Table structure for table `help_map`
--

CREATE TABLE `help_map` (
  `chap` int(11) NOT NULL DEFAULT 0,
  `component` varchar(10) NOT NULL DEFAULT '',
  `screen_id` varchar(100) NOT NULL DEFAULT '',
  `screen_sub_id` varchar(100) NOT NULL DEFAULT '',
  `perm` varchar(20) NOT NULL DEFAULT '',
  `module_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`component`,`screen_id`,`screen_sub_id`,`chap`,`perm`,`module_id`),
  KEY `sc_idx` (`screen_id`),
  KEY `ch_idx` (`chap`)
) ;

--
-- Dumping data for table `help_map`
--


--
-- Table structure for table `help_module`
--

CREATE TABLE `help_module` (
  `id` int(11) NOT NULL DEFAULT 0,
  `lm_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `help_module`
--


--
-- Table structure for table `help_module_seq`
--

CREATE TABLE `help_module_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `help_module_seq`
--


--
-- Table structure for table `help_tooltip`
--

CREATE TABLE `help_tooltip` (
  `id` int(11) NOT NULL DEFAULT 0,
  `tt_text` varchar(4000) DEFAULT NULL,
  `tt_id` varchar(200) NOT NULL DEFAULT '',
  `comp` varchar(10) NOT NULL DEFAULT '',
  `lang` char(2) NOT NULL DEFAULT 'de',
  `module_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`tt_id`,`module_id`)
) ;

--
-- Dumping data for table `help_tooltip`
--


--
-- Table structure for table `help_tooltip_seq`
--

CREATE TABLE `help_tooltip_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `help_tooltip_seq`
--


--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` varchar(8) DEFAULT NULL,
  `action` varchar(20) DEFAULT NULL,
  `hdate` datetime DEFAULT NULL,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `info_params` varchar(4000) DEFAULT NULL,
  `user_comment` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`obj_id`,`obj_type`)
) ;

--
-- Dumping data for table `history`
--


--
-- Table structure for table `history_seq`
--

CREATE TABLE `history_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `history_seq`
--


--
-- Table structure for table `iass_info_settings`
--

CREATE TABLE `iass_info_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `contact` varchar(100) DEFAULT NULL,
  `responsibility` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `mails` text DEFAULT NULL,
  `consultation_hours` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `iass_info_settings`
--


--
-- Table structure for table `iass_members`
--

CREATE TABLE `iass_members` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `examiner_id` int(11) DEFAULT 0,
  `record` text DEFAULT NULL,
  `internal_note` text DEFAULT NULL,
  `notify` tinyint(4) NOT NULL DEFAULT 0,
  `notification_ts` int(11) NOT NULL DEFAULT -1,
  `learning_progress` tinyint(4) DEFAULT 0,
  `finalized` tinyint(4) NOT NULL DEFAULT 0,
  `place` varchar(255) DEFAULT NULL,
  `event_time` bigint(20) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `user_view_file` tinyint(4) DEFAULT NULL,
  `changer_id` int(11) DEFAULT NULL,
  `change_time` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`usr_id`)
) ;

--
-- Dumping data for table `iass_members`
--


--
-- Table structure for table `iass_settings`
--

CREATE TABLE `iass_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `content` text DEFAULT NULL,
  `record_template` text DEFAULT NULL,
  `event_time_place_required` tinyint(4) NOT NULL DEFAULT 0,
  `file_required` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `iass_settings`
--


--
-- Table structure for table `il_adn_dismiss`
--

CREATE TABLE `il_adn_dismiss` (
  `id` bigint(20) NOT NULL,
  `usr_id` bigint(20) DEFAULT NULL,
  `notification_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_adn_dismiss`
--


--
-- Table structure for table `il_adn_dismiss_seq`
--

CREATE TABLE `il_adn_dismiss_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_adn_dismiss_seq`
--


--
-- Table structure for table `il_adn_notifications`
--

CREATE TABLE `il_adn_notifications` (
  `id` bigint(20) NOT NULL,
  `title` varchar(256) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `type_during_event` tinyint(4) DEFAULT NULL,
  `dismissable` tinyint(4) DEFAULT NULL,
  `permanent` tinyint(4) DEFAULT NULL,
  `allowed_users` varchar(256) DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `last_update_by` bigint(20) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  `limited_to_role_ids` varchar(256) DEFAULT NULL,
  `limit_to_roles` tinyint(4) DEFAULT NULL,
  `interruptive` tinyint(4) DEFAULT NULL,
  `link` varchar(256) DEFAULT NULL,
  `link_type` tinyint(4) DEFAULT NULL,
  `link_target` varchar(256) DEFAULT NULL,
  `event_start` bigint(20) DEFAULT 0,
  `event_end` bigint(20) DEFAULT 0,
  `display_start` bigint(20) DEFAULT 0,
  `display_end` bigint(20) DEFAULT 0,
  `create_date` bigint(20) DEFAULT 0,
  `last_update` bigint(20) DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_adn_notifications`
--


--
-- Table structure for table `il_adn_notifications_seq`
--

CREATE TABLE `il_adn_notifications_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_adn_notifications_seq`
--


--
-- Table structure for table `il_bibl_attribute`
--

CREATE TABLE `il_bibl_attribute` (
  `entry_id` int(11) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `value` varchar(4000) DEFAULT NULL,
  `id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_bibl_attribute`
--


--
-- Table structure for table `il_bibl_attribute_seq`
--

CREATE TABLE `il_bibl_attribute_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_bibl_attribute_seq`
--


--
-- Table structure for table `il_bibl_data`
--

CREATE TABLE `il_bibl_data` (
  `id` int(11) NOT NULL DEFAULT 0,
  `filename` varchar(256) DEFAULT NULL,
  `is_online` tinyint(4) DEFAULT NULL,
  `file_type` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_bibl_data`
--


--
-- Table structure for table `il_bibl_entry`
--

CREATE TABLE `il_bibl_entry` (
  `data_id` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_bibl_entry`
--


--
-- Table structure for table `il_bibl_entry_seq`
--

CREATE TABLE `il_bibl_entry_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_bibl_entry_seq`
--


--
-- Table structure for table `il_bibl_field`
--

CREATE TABLE `il_bibl_field` (
  `id` int(11) NOT NULL,
  `identifier` varchar(50) NOT NULL,
  `data_type` tinyint(4) NOT NULL,
  `position` mediumint(9) DEFAULT NULL,
  `is_standard_field` tinyint(4) NOT NULL,
  `object_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_bibl_field`
--


--
-- Table structure for table `il_bibl_field_seq`
--

CREATE TABLE `il_bibl_field_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_bibl_field_seq`
--


--
-- Table structure for table `il_bibl_filter`
--

CREATE TABLE `il_bibl_filter` (
  `id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `filter_type` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_bibl_filter`
--


--
-- Table structure for table `il_bibl_filter_seq`
--

CREATE TABLE `il_bibl_filter_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_bibl_filter_seq`
--


--
-- Table structure for table `il_bibl_overview_model`
--

CREATE TABLE `il_bibl_overview_model` (
  `ovm_id` int(11) NOT NULL DEFAULT 0,
  `literature_type` varchar(32) DEFAULT NULL,
  `pattern` varchar(512) DEFAULT NULL,
  `file_type_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`ovm_id`)
) ;

--
-- Dumping data for table `il_bibl_overview_model`
--

INSERT INTO `il_bibl_overview_model` VALUES (1,'default','[<strong>|bib_default_author|</strong> ][|bib_default_title|]: <Emph>[|bib_default_publisher| ][|bib_default_year| ][|bib_default_address|]</Emph>',2);
INSERT INTO `il_bibl_overview_model` VALUES (2,'default','[<strong>|ris_default_a1|</strong> ][<strong>|ris_default_au|</strong> ][|ris_default_t1|][ |ris_default_ti|]: <Emph>[|ris_default_pb| ][|ris_default_y1| ][|ris_default_py| ][|ris_default_cy|]</Emph>',1);

--
-- Table structure for table `il_bibl_settings`
--

CREATE TABLE `il_bibl_settings` (
  `id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) NOT NULL DEFAULT '-',
  `url` varchar(128) NOT NULL DEFAULT '-',
  `img` varchar(128) DEFAULT NULL,
  `show_in_list` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_bibl_settings`
--


--
-- Table structure for table `il_bibl_settings_seq`
--

CREATE TABLE `il_bibl_settings_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_bibl_settings_seq`
--


--
-- Table structure for table `il_bibl_translation`
--

CREATE TABLE `il_bibl_translation` (
  `id` int(11) NOT NULL,
  `field_id` bigint(20) NOT NULL,
  `language_key` varchar(2) NOT NULL,
  `translation` varchar(256) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_bibl_translation`
--


--
-- Table structure for table `il_bibl_translation_seq`
--

CREATE TABLE `il_bibl_translation_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_bibl_translation_seq`
--


--
-- Table structure for table `il_block_setting`
--

CREATE TABLE `il_block_setting` (
  `type` varchar(20) NOT NULL DEFAULT ' ',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `block_id` int(11) NOT NULL DEFAULT 0,
  `setting` varchar(40) NOT NULL DEFAULT ' ',
  `value` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`type`,`user_id`,`block_id`,`setting`)
) ;

--
-- Dumping data for table `il_block_setting`
--


--
-- Table structure for table `il_blog`
--

CREATE TABLE `il_blog` (
  `id` int(11) NOT NULL DEFAULT 0,
  `bg_color` char(6) DEFAULT NULL,
  `font_color` char(6) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `ppic` tinyint(4) DEFAULT NULL,
  `rss_active` tinyint(4) DEFAULT 0,
  `approval` tinyint(4) DEFAULT 0,
  `abs_shorten` tinyint(4) DEFAULT 0,
  `abs_shorten_len` smallint(6) DEFAULT 0,
  `abs_image` tinyint(4) DEFAULT 0,
  `abs_img_width` smallint(6) DEFAULT 0,
  `abs_img_height` smallint(6) DEFAULT 0,
  `keywords` tinyint(4) NOT NULL DEFAULT 1,
  `authors` tinyint(4) NOT NULL DEFAULT 1,
  `nav_mode` tinyint(4) NOT NULL DEFAULT 1,
  `nav_list_post` smallint(6) NOT NULL DEFAULT 10,
  `nav_list_mon` smallint(6) DEFAULT 0,
  `ov_post` smallint(6) DEFAULT 0,
  `nav_order` varchar(255) DEFAULT NULL,
  `nav_list_mon_with_post` int(11) DEFAULT 3,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_blog`
--


--
-- Table structure for table `il_blog_posting`
--

CREATE TABLE `il_blog_posting` (
  `id` int(11) NOT NULL DEFAULT 0,
  `blog_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(400) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `author` int(11) NOT NULL DEFAULT 0,
  `approved` tinyint(4) DEFAULT 0,
  `last_withdrawn` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`created`)
) ;

--
-- Dumping data for table `il_blog_posting`
--


--
-- Table structure for table `il_blog_posting_seq`
--

CREATE TABLE `il_blog_posting_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_blog_posting_seq`
--


--
-- Table structure for table `il_bt_bucket`
--

CREATE TABLE `il_bt_bucket` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `user_id` bigint(20) DEFAULT NULL,
  `root_task_id` bigint(20) DEFAULT NULL,
  `current_task_id` bigint(20) DEFAULT NULL,
  `state` smallint(6) DEFAULT NULL,
  `total_number_of_tasks` int(11) DEFAULT NULL,
  `percentage` smallint(6) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `last_heartbeat` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`user_id`)
) ;

--
-- Dumping data for table `il_bt_bucket`
--


--
-- Table structure for table `il_bt_bucket_seq`
--

CREATE TABLE `il_bt_bucket_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_bt_bucket_seq`
--


--
-- Table structure for table `il_bt_task`
--

CREATE TABLE `il_bt_task` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `type` varchar(256) DEFAULT NULL,
  `class_path` varchar(256) DEFAULT NULL,
  `class_name` varchar(256) DEFAULT NULL,
  `bucket_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_bt_task`
--


--
-- Table structure for table `il_bt_task_seq`
--

CREATE TABLE `il_bt_task_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_bt_task_seq`
--


--
-- Table structure for table `il_bt_value`
--

CREATE TABLE `il_bt_value` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `has_parent_task` tinyint(4) DEFAULT NULL,
  `parent_task_id` bigint(20) DEFAULT NULL,
  `hash` varchar(256) DEFAULT NULL,
  `type` varchar(256) DEFAULT NULL,
  `class_path` varchar(256) DEFAULT NULL,
  `class_name` varchar(256) DEFAULT NULL,
  `serialized` longtext DEFAULT NULL,
  `bucket_id` bigint(20) DEFAULT NULL,
  `position` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`bucket_id`)
) ;

--
-- Dumping data for table `il_bt_value`
--


--
-- Table structure for table `il_bt_value_seq`
--

CREATE TABLE `il_bt_value_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_bt_value_seq`
--


--
-- Table structure for table `il_bt_value_to_task`
--

CREATE TABLE `il_bt_value_to_task` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `task_id` bigint(20) DEFAULT NULL,
  `value_id` bigint(20) DEFAULT NULL,
  `bucket_id` bigint(20) DEFAULT NULL,
  `position` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`task_id`),
  KEY `i2_idx` (`value_id`)
) ;

--
-- Dumping data for table `il_bt_value_to_task`
--


--
-- Table structure for table `il_bt_value_to_task_seq`
--

CREATE TABLE `il_bt_value_to_task_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_bt_value_to_task_seq`
--


--
-- Table structure for table `il_cert_bgtask_migr`
--

CREATE TABLE `il_cert_bgtask_migr` (
  `id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `lock` int(11) NOT NULL DEFAULT 0,
  `found_items` int(11) NOT NULL DEFAULT 0,
  `processed_items` int(11) NOT NULL DEFAULT 0,
  `migrated_items` int(11) NOT NULL DEFAULT 0,
  `progress` int(11) NOT NULL DEFAULT 0,
  `state` varchar(255) NOT NULL,
  `started_ts` int(11) DEFAULT 0,
  `finished_ts` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `con_idx` (`id`,`usr_id`)
) ;

--
-- Dumping data for table `il_cert_bgtask_migr`
--


--
-- Table structure for table `il_cert_bgtask_migr_seq`
--

CREATE TABLE `il_cert_bgtask_migr_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_cert_bgtask_migr_seq`
--


--
-- Table structure for table `il_cert_cron_queue`
--

CREATE TABLE `il_cert_cron_queue` (
  `id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `adapter_class` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `started_timestamp` int(11) NOT NULL DEFAULT 0,
  `template_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`obj_id`,`usr_id`)
) ;

--
-- Dumping data for table `il_cert_cron_queue`
--


--
-- Table structure for table `il_cert_cron_queue_seq`
--

CREATE TABLE `il_cert_cron_queue_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_cert_cron_queue_seq`
--


--
-- Table structure for table `il_cert_template`
--

CREATE TABLE `il_cert_template` (
  `id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` varchar(255) NOT NULL DEFAULT '',
  `certificate_content` longtext NOT NULL,
  `certificate_hash` varchar(255) NOT NULL,
  `template_values` longtext NOT NULL,
  `background_image_path` varchar(255) DEFAULT NULL,
  `version` bigint(20) NOT NULL DEFAULT 0,
  `ilias_version` varchar(255) NOT NULL DEFAULT 'v5.4.0',
  `created_timestamp` int(11) NOT NULL DEFAULT 0,
  `currently_active` tinyint(4) NOT NULL DEFAULT 0,
  `deleted` tinyint(4) NOT NULL DEFAULT 0,
  `thumbnail_image_path` varchar(255) DEFAULT NULL,
  `certificate_content_bu` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`obj_id`),
  KEY `i2_idx` (`obj_id`,`deleted`),
  KEY `i3_idx` (`obj_id`,`currently_active`,`deleted`),
  KEY `i4_idx` (`obj_type`),
  KEY `i5_idx` (`background_image_path`,`currently_active`)
) ;

--
-- Dumping data for table `il_cert_template`
--


--
-- Table structure for table `il_cert_template_seq`
--

CREATE TABLE `il_cert_template_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_cert_template_seq`
--


--
-- Table structure for table `il_cert_user_cert`
--

CREATE TABLE `il_cert_user_cert` (
  `id` int(11) NOT NULL DEFAULT 0,
  `pattern_certificate_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` varchar(255) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `user_name` varchar(255) NOT NULL DEFAULT '0',
  `acquired_timestamp` int(11) NOT NULL DEFAULT 0,
  `certificate_content` longtext NOT NULL,
  `template_values` longtext NOT NULL,
  `valid_until` int(11) DEFAULT NULL,
  `background_image_path` varchar(255) DEFAULT NULL,
  `version` varchar(255) NOT NULL DEFAULT '1',
  `ilias_version` varchar(255) NOT NULL DEFAULT 'v5.4.0',
  `currently_active` tinyint(4) NOT NULL DEFAULT 0,
  `thumbnail_image_path` varchar(255) DEFAULT NULL,
  `certificate_content_bu` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`obj_id`,`pattern_certificate_id`),
  KEY `i2_idx` (`user_id`,`currently_active`),
  KEY `i3_idx` (`user_id`,`currently_active`,`acquired_timestamp`),
  KEY `i4_idx` (`user_id`,`obj_type`,`currently_active`),
  KEY `i5_idx` (`obj_id`,`currently_active`),
  KEY `i6_idx` (`user_id`,`obj_id`,`currently_active`),
  KEY `i7_idx` (`background_image_path`,`currently_active`)
) ;

--
-- Dumping data for table `il_cert_user_cert`
--


--
-- Table structure for table `il_cert_user_cert_seq`
--

CREATE TABLE `il_cert_user_cert_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_cert_user_cert_seq`
--


--
-- Table structure for table `il_certificate`
--

CREATE TABLE `il_certificate` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `il_certificate`
--


--
-- Table structure for table `il_cld_data`
--

CREATE TABLE `il_cld_data` (
  `id` int(11) NOT NULL DEFAULT 0,
  `is_online` tinyint(4) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `root_folder` varchar(255) DEFAULT NULL,
  `root_id` varchar(255) DEFAULT NULL,
  `owner_id` bigint(20) NOT NULL DEFAULT 0,
  `auth_complete` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_cld_data`
--


--
-- Table structure for table `il_component`
--

CREATE TABLE `il_component` (
  `type` char(10) NOT NULL DEFAULT '',
  `name` varchar(200) DEFAULT NULL,
  `id` char(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`type`,`id`)
) ;

--
-- Dumping data for table `il_component`
--

INSERT INTO `il_component` VALUES ('Modules','SystemFolder','adm');
INSERT INTO `il_component` VALUES ('Modules','Bibliographic','bibl');
INSERT INTO `il_component` VALUES ('Modules','Blog','blog');
INSERT INTO `il_component` VALUES ('Modules','BookingManager','book');
INSERT INTO `il_component` VALUES ('Modules','Category','cat');
INSERT INTO `il_component` VALUES ('Modules','CategoryReference','catr');
INSERT INTO `il_component` VALUES ('Modules','Chatroom','chtr');
INSERT INTO `il_component` VALUES ('Modules','Cloud','cld');
INSERT INTO `il_component` VALUES ('Modules','CmiXapi','cmix');
INSERT INTO `il_component` VALUES ('Modules','ContentPage','copa');
INSERT INTO `il_component` VALUES ('Modules','Course','crs');
INSERT INTO `il_component` VALUES ('Modules','CourseReference','crsr');
INSERT INTO `il_component` VALUES ('Modules','DataCollection','dcl');
INSERT INTO `il_component` VALUES ('Modules','Exercise','exc');
INSERT INTO `il_component` VALUES ('Modules','ExternalFeed','feed');
INSERT INTO `il_component` VALUES ('Modules','File','file');
INSERT INTO `il_component` VALUES ('Modules','Folder','fold');
INSERT INTO `il_component` VALUES ('Modules','Forum','frm');
INSERT INTO `il_component` VALUES ('Modules','Glossary','glo');
INSERT INTO `il_component` VALUES ('Modules','Group','grp');
INSERT INTO `il_component` VALUES ('Modules','GroupReference','grpr');
INSERT INTO `il_component` VALUES ('Modules','HTMLLearningModule','htlm');
INSERT INTO `il_component` VALUES ('Modules','IndividualAssessment','iass');
INSERT INTO `il_component` VALUES ('Modules','ItemGroup','itgr');
INSERT INTO `il_component` VALUES ('Modules','LearningModule','lm');
INSERT INTO `il_component` VALUES ('Modules','LearningSequence','lso');
INSERT INTO `il_component` VALUES ('Modules','LTIConsumer','lti');
INSERT INTO `il_component` VALUES ('Modules','MediaCast','mcst');
INSERT INTO `il_component` VALUES ('Modules','MediaPool','mep');
INSERT INTO `il_component` VALUES ('Modules','OrgUnit','orgu');
INSERT INTO `il_component` VALUES ('Modules','Poll','poll');
INSERT INTO `il_component` VALUES ('Modules','StudyProgramme','prg');
INSERT INTO `il_component` VALUES ('Modules','StudyProgrammeReference','prgr');
INSERT INTO `il_component` VALUES ('Modules','Portfolio','prtf');
INSERT INTO `il_component` VALUES ('Modules','TestQuestionPool','qpl');
INSERT INTO `il_component` VALUES ('Modules','RemoteCategory','rcat');
INSERT INTO `il_component` VALUES ('Modules','RemoteCourse','rcrs');
INSERT INTO `il_component` VALUES ('Modules','RemoteFile','rfil');
INSERT INTO `il_component` VALUES ('Modules','RemoteGlossary','rglo');
INSERT INTO `il_component` VALUES ('Modules','RemoteGroup','rgrp');
INSERT INTO `il_component` VALUES ('Modules','RemoteLearningModule','rlm');
INSERT INTO `il_component` VALUES ('Modules','RootFolder','root');
INSERT INTO `il_component` VALUES ('Modules','RemoteTest','rtst');
INSERT INTO `il_component` VALUES ('Modules','RemoteWiki','rwik');
INSERT INTO `il_component` VALUES ('Modules','ScormAicc','sahs');
INSERT INTO `il_component` VALUES ('Modules','Scorm2004','sc13');
INSERT INTO `il_component` VALUES ('Modules','Session','sess');
INSERT INTO `il_component` VALUES ('Modules','SurveyQuestionPool','spl');
INSERT INTO `il_component` VALUES ('Modules','Survey','svy');
INSERT INTO `il_component` VALUES ('Modules','Test','tst');
INSERT INTO `il_component` VALUES ('Modules','WebResource','webr');
INSERT INTO `il_component` VALUES ('Modules','WorkspaceFolder','wfld');
INSERT INTO `il_component` VALUES ('Modules','Wiki','wiki');
INSERT INTO `il_component` VALUES ('Modules','WorkspaceRootFolder','wsrt');
INSERT INTO `il_component` VALUES ('Services','AccessControl','ac');
INSERT INTO `il_component` VALUES ('Services','Accessibility','acc');
INSERT INTO `il_component` VALUES ('Services','Accordion','accrdn');
INSERT INTO `il_component` VALUES ('Services','Administration','adm');
INSERT INTO `il_component` VALUES ('Services','AdministrativeNotification','adn');
INSERT INTO `il_component` VALUES ('Services','AdvancedEditing','adve');
INSERT INTO `il_component` VALUES ('Services','AdvancedMetaData','amet');
INSERT INTO `il_component` VALUES ('Services','Authentication','auth');
INSERT INTO `il_component` VALUES ('Services','Awareness','awrn');
INSERT INTO `il_component` VALUES ('Services','Badge','badge');
INSERT INTO `il_component` VALUES ('Services','BackgroundTasks','bgtk');
INSERT INTO `il_component` VALUES ('Services','Calendar','cal');
INSERT INTO `il_component` VALUES ('Services','Certificate','cert');
INSERT INTO `il_component` VALUES ('Services','ContainerReference','cntr');
INSERT INTO `il_component` VALUES ('Services','Component','comp');
INSERT INTO `il_component` VALUES ('Services','Container','cont');
INSERT INTO `il_component` VALUES ('Services','Contact','contact');
INSERT INTO `il_component` VALUES ('Services','COPage','copg');
INSERT INTO `il_component` VALUES ('Services','Cron','cron');
INSERT INTO `il_component` VALUES ('Services','Dashboard','dash');
INSERT INTO `il_component` VALUES ('Services','Database','db');
INSERT INTO `il_component` VALUES ('Services','DataSet','ds');
INSERT INTO `il_component` VALUES ('Services','EventHandling','evnt');
INSERT INTO `il_component` VALUES ('Services','Export','exp');
INSERT INTO `il_component` VALUES ('Services','FileServices','fils');
INSERT INTO `il_component` VALUES ('Services','Help','help');
INSERT INTO `il_component` VALUES ('Services','Imprint','impr');
INSERT INTO `il_component` VALUES ('Services','Init','init');
INSERT INTO `il_component` VALUES ('Services','Language','lang');
INSERT INTO `il_component` VALUES ('Services','LinkChecker','lchk');
INSERT INTO `il_component` VALUES ('Services','LDAP','ldap');
INSERT INTO `il_component` VALUES ('Services','LearningHistory','lhist');
INSERT INTO `il_component` VALUES ('Services','Link','link');
INSERT INTO `il_component` VALUES ('Services','Logging','log');
INSERT INTO `il_component` VALUES ('Services','LTI','lti');
INSERT INTO `il_component` VALUES ('Services','Mail','mail');
INSERT INTO `il_component` VALUES ('Services','MetaData','meta');
INSERT INTO `il_component` VALUES ('Services','Membership','mmbr');
INSERT INTO `il_component` VALUES ('Services','MainMenu','mme');
INSERT INTO `il_component` VALUES ('Services','MediaObjects','mob');
INSERT INTO `il_component` VALUES ('Services','MyStaff','msta');
INSERT INTO `il_component` VALUES ('Services','Navigation','navh');
INSERT INTO `il_component` VALUES ('Services','News','news');
INSERT INTO `il_component` VALUES ('Services','Notifications','nota');
INSERT INTO `il_component` VALUES ('Services','Notes','note');
INSERT INTO `il_component` VALUES ('Services','Notification','noti');
INSERT INTO `il_component` VALUES ('Services','Object','obj');
INSERT INTO `il_component` VALUES ('Services','OpenIdConnect','oidc');
INSERT INTO `il_component` VALUES ('Services','OnScreenChat','osch');
INSERT INTO `il_component` VALUES ('Services','DidacticTemplate','otpl');
INSERT INTO `il_component` VALUES ('Services','PDFGeneration','pdfg');
INSERT INTO `il_component` VALUES ('Services','Preview','prvw');
INSERT INTO `il_component` VALUES ('Services','PrivacySecurity','ps');
INSERT INTO `il_component` VALUES ('Services','PersonalWorkspace','pwsp');
INSERT INTO `il_component` VALUES ('Services','Repository','rep');
INSERT INTO `il_component` VALUES ('Services','Randomization','rnd');
INSERT INTO `il_component` VALUES ('Services','AuthShibboleth','shiba');
INSERT INTO `il_component` VALUES ('Services','Skill','skll');
INSERT INTO `il_component` VALUES ('Services','Search','src');
INSERT INTO `il_component` VALUES ('Services','Style','styl');
INSERT INTO `il_component` VALUES ('Services','SystemCheck','sysc');
INSERT INTO `il_component` VALUES ('Services','Table','table');
INSERT INTO `il_component` VALUES ('Services','Tagging','tag');
INSERT INTO `il_component` VALUES ('Services','Tasks','task');
INSERT INTO `il_component` VALUES ('Services','Taxonomy','tax');
INSERT INTO `il_component` VALUES ('Services','TermsOfService','tos');
INSERT INTO `il_component` VALUES ('Services','Tracking','trac');
INSERT INTO `il_component` VALUES ('Services','Tree','tree');
INSERT INTO `il_component` VALUES ('Services','UIComponent','ui');
INSERT INTO `il_component` VALUES ('Services','User','user');
INSERT INTO `il_component` VALUES ('Services','WebDAV','wbdv');
INSERT INTO `il_component` VALUES ('Services','WorkflowEngine','wfe');
INSERT INTO `il_component` VALUES ('Services','WebServices','wsrv');

--
-- Table structure for table `il_custom_block`
--

CREATE TABLE `il_custom_block` (
  `id` int(11) NOT NULL DEFAULT 0,
  `context_obj_id` int(11) DEFAULT NULL,
  `context_obj_type` varchar(10) DEFAULT NULL,
  `context_sub_obj_id` int(11) DEFAULT NULL,
  `context_sub_obj_type` varchar(10) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_custom_block`
--


--
-- Table structure for table `il_custom_block_seq`
--

CREATE TABLE `il_custom_block_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_custom_block_seq`
--


--
-- Table structure for table `il_dcl_data`
--

CREATE TABLE `il_dcl_data` (
  `id` int(11) NOT NULL DEFAULT 0,
  `is_online` tinyint(4) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `public_notes` tinyint(4) DEFAULT NULL,
  `approval` tinyint(4) DEFAULT NULL,
  `notification` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_dcl_data`
--


--
-- Table structure for table `il_dcl_data_seq`
--

CREATE TABLE `il_dcl_data_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_data_seq`
--


--
-- Table structure for table `il_dcl_datatype`
--

CREATE TABLE `il_dcl_datatype` (
  `id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(256) DEFAULT NULL,
  `ildb_type` varchar(256) NOT NULL DEFAULT '',
  `storage_location` int(11) NOT NULL DEFAULT 0,
  `sort` smallint(6) DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_dcl_datatype`
--

INSERT INTO `il_dcl_datatype` VALUES (1,'number','integer',2,20);
INSERT INTO `il_dcl_datatype` VALUES (2,'text','text',1,0);
INSERT INTO `il_dcl_datatype` VALUES (3,'reference','text',1,80);
INSERT INTO `il_dcl_datatype` VALUES (4,'boolean','integer',2,30);
INSERT INTO `il_dcl_datatype` VALUES (5,'datetime','date',3,40);
INSERT INTO `il_dcl_datatype` VALUES (6,'fileupload','integer',2,70);
INSERT INTO `il_dcl_datatype` VALUES (7,'rating','integer',0,100);
INSERT INTO `il_dcl_datatype` VALUES (8,'ilias_reference','integer',2,90);
INSERT INTO `il_dcl_datatype` VALUES (9,'mob','integer',2,60);
INSERT INTO `il_dcl_datatype` VALUES (11,'formula','text',0,110);
INSERT INTO `il_dcl_datatype` VALUES (12,'plugin','text',0,120);
INSERT INTO `il_dcl_datatype` VALUES (14,'text_selection','text',1,10);
INSERT INTO `il_dcl_datatype` VALUES (15,'date_selection','text',1,50);

--
-- Table structure for table `il_dcl_datatype_prop`
--

CREATE TABLE `il_dcl_datatype_prop` (
  `id` int(11) NOT NULL DEFAULT 0,
  `datatype_id` int(11) DEFAULT NULL,
  `title` varchar(256) DEFAULT NULL,
  `inputformat` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_dcl_datatype_prop`
--

INSERT INTO `il_dcl_datatype_prop` VALUES (1,2,'length',1);
INSERT INTO `il_dcl_datatype_prop` VALUES (2,2,'regex',2);
INSERT INTO `il_dcl_datatype_prop` VALUES (3,3,'table_id',1);
INSERT INTO `il_dcl_datatype_prop` VALUES (4,2,'url',4);
INSERT INTO `il_dcl_datatype_prop` VALUES (5,2,'text_area',4);
INSERT INTO `il_dcl_datatype_prop` VALUES (6,3,'reference_link',4);
INSERT INTO `il_dcl_datatype_prop` VALUES (7,9,'width',1);
INSERT INTO `il_dcl_datatype_prop` VALUES (8,9,'height',1);
INSERT INTO `il_dcl_datatype_prop` VALUES (9,8,'learning_progress',4);
INSERT INTO `il_dcl_datatype_prop` VALUES (10,8,'ILIAS_reference_link',4);
INSERT INTO `il_dcl_datatype_prop` VALUES (11,3,'multiple_selection',4);
INSERT INTO `il_dcl_datatype_prop` VALUES (12,11,'expression',2);
INSERT INTO `il_dcl_datatype_prop` VALUES (13,8,'display_action_menu',4);
INSERT INTO `il_dcl_datatype_prop` VALUES (14,2,'link_detail_page',4);
INSERT INTO `il_dcl_datatype_prop` VALUES (15,9,'link_detail_page',4);

--
-- Table structure for table `il_dcl_field`
--

CREATE TABLE `il_dcl_field` (
  `id` int(11) NOT NULL DEFAULT 0,
  `table_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(256) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `datatype_id` int(11) NOT NULL DEFAULT 0,
  `is_unique` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`datatype_id`),
  KEY `i2_idx` (`table_id`)
) ;

--
-- Dumping data for table `il_dcl_field`
--


--
-- Table structure for table `il_dcl_field_prop`
--

CREATE TABLE `il_dcl_field_prop` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `field_id` bigint(20) NOT NULL DEFAULT 0,
  `name` varchar(4000) NOT NULL DEFAULT '',
  `value` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`id`,`field_id`)
) ;

--
-- Dumping data for table `il_dcl_field_prop`
--


--
-- Table structure for table `il_dcl_field_prop_b`
--

CREATE TABLE `il_dcl_field_prop_b` (
  `id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `datatype_prop_id` int(11) NOT NULL DEFAULT 0,
  `value` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`field_id`),
  KEY `i2_idx` (`datatype_prop_id`)
) ;

--
-- Dumping data for table `il_dcl_field_prop_b`
--


--
-- Table structure for table `il_dcl_field_prop_s_b`
--

CREATE TABLE `il_dcl_field_prop_s_b` (
  `sequence` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_field_prop_s_b`
--


--
-- Table structure for table `il_dcl_field_prop_seq`
--

CREATE TABLE `il_dcl_field_prop_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_field_prop_seq`
--


--
-- Table structure for table `il_dcl_field_seq`
--

CREATE TABLE `il_dcl_field_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_field_seq`
--


--
-- Table structure for table `il_dcl_record`
--

CREATE TABLE `il_dcl_record` (
  `id` int(11) NOT NULL DEFAULT 0,
  `table_id` int(11) NOT NULL DEFAULT 0,
  `create_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT 0,
  `last_edit_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`table_id`)
) ;

--
-- Dumping data for table `il_dcl_record`
--


--
-- Table structure for table `il_dcl_record_field`
--

CREATE TABLE `il_dcl_record_field` (
  `id` int(11) NOT NULL DEFAULT 0,
  `record_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`record_id`),
  KEY `i2_idx` (`field_id`)
) ;

--
-- Dumping data for table `il_dcl_record_field`
--


--
-- Table structure for table `il_dcl_record_field_seq`
--

CREATE TABLE `il_dcl_record_field_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_record_field_seq`
--


--
-- Table structure for table `il_dcl_record_seq`
--

CREATE TABLE `il_dcl_record_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_record_seq`
--


--
-- Table structure for table `il_dcl_sel_opts`
--

CREATE TABLE `il_dcl_sel_opts` (
  `id` bigint(20) NOT NULL,
  `field_id` bigint(20) NOT NULL,
  `opt_id` bigint(20) NOT NULL,
  `sorting` bigint(20) NOT NULL,
  `value` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_dcl_sel_opts`
--


--
-- Table structure for table `il_dcl_sel_opts_seq`
--

CREATE TABLE `il_dcl_sel_opts_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_sel_opts_seq`
--


--
-- Table structure for table `il_dcl_stloc1_default`
--

CREATE TABLE `il_dcl_stloc1_default` (
  `id` int(11) NOT NULL,
  `tview_set_id` int(11) DEFAULT NULL,
  `value` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_dcl_stloc1_default`
--


--
-- Table structure for table `il_dcl_stloc1_default_seq`
--

CREATE TABLE `il_dcl_stloc1_default_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_stloc1_default_seq`
--


--
-- Table structure for table `il_dcl_stloc1_value`
--

CREATE TABLE `il_dcl_stloc1_value` (
  `id` int(11) NOT NULL DEFAULT 0,
  `record_field_id` int(11) NOT NULL DEFAULT 0,
  `value` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`record_field_id`)
) ;

--
-- Dumping data for table `il_dcl_stloc1_value`
--


--
-- Table structure for table `il_dcl_stloc1_value_seq`
--

CREATE TABLE `il_dcl_stloc1_value_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_stloc1_value_seq`
--


--
-- Table structure for table `il_dcl_stloc2_default`
--

CREATE TABLE `il_dcl_stloc2_default` (
  `id` int(11) NOT NULL,
  `tview_set_id` int(11) DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_dcl_stloc2_default`
--


--
-- Table structure for table `il_dcl_stloc2_default_seq`
--

CREATE TABLE `il_dcl_stloc2_default_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_stloc2_default_seq`
--


--
-- Table structure for table `il_dcl_stloc2_value`
--

CREATE TABLE `il_dcl_stloc2_value` (
  `id` int(11) NOT NULL DEFAULT 0,
  `record_field_id` int(11) NOT NULL DEFAULT 0,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`record_field_id`)
) ;

--
-- Dumping data for table `il_dcl_stloc2_value`
--


--
-- Table structure for table `il_dcl_stloc2_value_seq`
--

CREATE TABLE `il_dcl_stloc2_value_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_stloc2_value_seq`
--


--
-- Table structure for table `il_dcl_stloc3_default`
--

CREATE TABLE `il_dcl_stloc3_default` (
  `id` int(11) NOT NULL,
  `tview_set_id` int(11) NOT NULL,
  `value` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_dcl_stloc3_default`
--


--
-- Table structure for table `il_dcl_stloc3_default_seq`
--

CREATE TABLE `il_dcl_stloc3_default_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_stloc3_default_seq`
--


--
-- Table structure for table `il_dcl_stloc3_value`
--

CREATE TABLE `il_dcl_stloc3_value` (
  `id` int(11) NOT NULL DEFAULT 0,
  `record_field_id` int(11) NOT NULL DEFAULT 0,
  `value` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`record_field_id`)
) ;

--
-- Dumping data for table `il_dcl_stloc3_value`
--


--
-- Table structure for table `il_dcl_stloc3_value_seq`
--

CREATE TABLE `il_dcl_stloc3_value_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_stloc3_value_seq`
--


--
-- Table structure for table `il_dcl_table`
--

CREATE TABLE `il_dcl_table` (
  `id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(256) DEFAULT NULL,
  `add_perm` tinyint(4) NOT NULL DEFAULT 1,
  `edit_perm` tinyint(4) NOT NULL DEFAULT 1,
  `delete_perm` tinyint(4) NOT NULL DEFAULT 1,
  `edit_by_owner` tinyint(4) NOT NULL DEFAULT 1,
  `limited` tinyint(4) NOT NULL DEFAULT 0,
  `limit_start` datetime DEFAULT NULL,
  `limit_end` datetime DEFAULT NULL,
  `is_visible` tinyint(4) NOT NULL DEFAULT 1,
  `export_enabled` tinyint(4) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `default_sort_field_id` varchar(16) NOT NULL DEFAULT '0',
  `default_sort_field_order` varchar(4) NOT NULL DEFAULT 'asc',
  `public_comments` int(11) NOT NULL DEFAULT 0,
  `view_own_records_perm` int(11) NOT NULL DEFAULT 0,
  `delete_by_owner` tinyint(4) NOT NULL DEFAULT 0,
  `save_confirmation` tinyint(4) NOT NULL DEFAULT 0,
  `import_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `table_order` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`obj_id`)
) ;

--
-- Dumping data for table `il_dcl_table`
--


--
-- Table structure for table `il_dcl_table_seq`
--

CREATE TABLE `il_dcl_table_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_table_seq`
--


--
-- Table structure for table `il_dcl_tableview`
--

CREATE TABLE `il_dcl_tableview` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `table_id` bigint(20) NOT NULL DEFAULT 0,
  `title` varchar(128) NOT NULL DEFAULT '',
  `roles` longtext DEFAULT NULL,
  `description` varchar(128) DEFAULT NULL,
  `tableview_order` bigint(20) DEFAULT NULL,
  `step_vs` tinyint(4) NOT NULL DEFAULT 1,
  `step_c` tinyint(4) NOT NULL DEFAULT 0,
  `step_e` tinyint(4) NOT NULL DEFAULT 0,
  `step_o` tinyint(4) NOT NULL DEFAULT 0,
  `step_s` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `t1_idx` (`table_id`)
) ;

--
-- Dumping data for table `il_dcl_tableview`
--


--
-- Table structure for table `il_dcl_tableview_seq`
--

CREATE TABLE `il_dcl_tableview_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_tableview_seq`
--


--
-- Table structure for table `il_dcl_tfield_set`
--

CREATE TABLE `il_dcl_tfield_set` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `table_id` bigint(20) NOT NULL DEFAULT 0,
  `field` varchar(128) NOT NULL DEFAULT '',
  `field_order` bigint(20) DEFAULT NULL,
  `exportable` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t2_idx` (`table_id`,`field`)
) ;

--
-- Dumping data for table `il_dcl_tfield_set`
--


--
-- Table structure for table `il_dcl_tfield_set_seq`
--

CREATE TABLE `il_dcl_tfield_set_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_tfield_set_seq`
--


--
-- Table structure for table `il_dcl_tview_set`
--

CREATE TABLE `il_dcl_tview_set` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `tableview_id` bigint(20) NOT NULL DEFAULT 0,
  `field` varchar(128) NOT NULL DEFAULT '',
  `visible` tinyint(4) DEFAULT NULL,
  `in_filter` tinyint(4) DEFAULT NULL,
  `filter_value` longtext DEFAULT NULL,
  `filter_changeable` tinyint(4) DEFAULT NULL,
  `default_value` varchar(255) DEFAULT NULL,
  `required_create` tinyint(4) NOT NULL DEFAULT 0,
  `locked_create` tinyint(4) NOT NULL DEFAULT 0,
  `visible_create` tinyint(4) NOT NULL DEFAULT 1,
  `visible_edit` tinyint(4) NOT NULL DEFAULT 1,
  `required_edit` tinyint(4) NOT NULL DEFAULT 0,
  `locked_edit` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`tableview_id`)
) ;

--
-- Dumping data for table `il_dcl_tview_set`
--


--
-- Table structure for table `il_dcl_tview_set_seq`
--

CREATE TABLE `il_dcl_tview_set_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_tview_set_seq`
--


--
-- Table structure for table `il_dcl_view_seq`
--

CREATE TABLE `il_dcl_view_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_dcl_view_seq`
--


--
-- Table structure for table `il_event_handling`
--

CREATE TABLE `il_event_handling` (
  `component` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(10) NOT NULL DEFAULT '',
  `id` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`component`,`type`,`id`)
) ;

--
-- Dumping data for table `il_event_handling`
--

INSERT INTO `il_event_handling` VALUES ('Modules/Category','raise','delete');
INSERT INTO `il_event_handling` VALUES ('Modules/Chatroom','raise','chatSettingsChanged');
INSERT INTO `il_event_handling` VALUES ('Modules/Course','listen','Modules/Course');
INSERT INTO `il_event_handling` VALUES ('Modules/Course','listen','Services/AccessControl');
INSERT INTO `il_event_handling` VALUES ('Modules/Course','listen','Services/Tracking');
INSERT INTO `il_event_handling` VALUES ('Modules/Course','raise','addParticipant');
INSERT INTO `il_event_handling` VALUES ('Modules/Course','raise','addSubscriber');
INSERT INTO `il_event_handling` VALUES ('Modules/Course','raise','addToWaitingList');
INSERT INTO `il_event_handling` VALUES ('Modules/Course','raise','create');
INSERT INTO `il_event_handling` VALUES ('Modules/Course','raise','delete');
INSERT INTO `il_event_handling` VALUES ('Modules/Course','raise','deleteParticipant');
INSERT INTO `il_event_handling` VALUES ('Modules/Course','raise','update');
INSERT INTO `il_event_handling` VALUES ('Modules/CourseReference','listen','Services/AccessControl');
INSERT INTO `il_event_handling` VALUES ('Modules/Exercise','raise','createAssignment');
INSERT INTO `il_event_handling` VALUES ('Modules/Exercise','raise','delete');
INSERT INTO `il_event_handling` VALUES ('Modules/Exercise','raise','deleteAssignment');
INSERT INTO `il_event_handling` VALUES ('Modules/Exercise','raise','updateAssignment');
INSERT INTO `il_event_handling` VALUES ('Modules/Forum','listen','Modules/Course');
INSERT INTO `il_event_handling` VALUES ('Modules/Forum','listen','Modules/Forum');
INSERT INTO `il_event_handling` VALUES ('Modules/Forum','listen','Modules/Group');
INSERT INTO `il_event_handling` VALUES ('Modules/Forum','listen','Services/News');
INSERT INTO `il_event_handling` VALUES ('Modules/Forum','raise','censoredPost');
INSERT INTO `il_event_handling` VALUES ('Modules/Forum','raise','createdPost');
INSERT INTO `il_event_handling` VALUES ('Modules/Forum','raise','deletedPost');
INSERT INTO `il_event_handling` VALUES ('Modules/Forum','raise','updatedPost');
INSERT INTO `il_event_handling` VALUES ('Modules/Group','listen','Services/AccessControl');
INSERT INTO `il_event_handling` VALUES ('Modules/Group','raise','addParticipant');
INSERT INTO `il_event_handling` VALUES ('Modules/Group','raise','addSubscriber');
INSERT INTO `il_event_handling` VALUES ('Modules/Group','raise','addToWaitingList');
INSERT INTO `il_event_handling` VALUES ('Modules/Group','raise','create');
INSERT INTO `il_event_handling` VALUES ('Modules/Group','raise','delete');
INSERT INTO `il_event_handling` VALUES ('Modules/Group','raise','deleteParticipant');
INSERT INTO `il_event_handling` VALUES ('Modules/Group','raise','update');
INSERT INTO `il_event_handling` VALUES ('Modules/LearningSequence','listen','Modules/LearningSequence');
INSERT INTO `il_event_handling` VALUES ('Modules/LearningSequence','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Modules/LearningSequence','listen','Services/Tracking');
INSERT INTO `il_event_handling` VALUES ('Modules/LearningSequence','raise','addParticipant');
INSERT INTO `il_event_handling` VALUES ('Modules/LearningSequence','raise','addSubscriber');
INSERT INTO `il_event_handling` VALUES ('Modules/LearningSequence','raise','addToWaitingList');
INSERT INTO `il_event_handling` VALUES ('Modules/LearningSequence','raise','create');
INSERT INTO `il_event_handling` VALUES ('Modules/LearningSequence','raise','delete');
INSERT INTO `il_event_handling` VALUES ('Modules/LearningSequence','raise','deleteParticipant');
INSERT INTO `il_event_handling` VALUES ('Modules/LearningSequence','raise','update');
INSERT INTO `il_event_handling` VALUES ('Modules/MediaPool','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Modules/OrgUnit','listen','Services/Tree');
INSERT INTO `il_event_handling` VALUES ('Modules/OrgUnit','raise','delete');
INSERT INTO `il_event_handling` VALUES ('Modules/Portfolio','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Modules/Session','listen','Modules/Session');
INSERT INTO `il_event_handling` VALUES ('Modules/Session','raise','addSubscriber');
INSERT INTO `il_event_handling` VALUES ('Modules/Session','raise','addToWaitingList');
INSERT INTO `il_event_handling` VALUES ('Modules/Session','raise','create');
INSERT INTO `il_event_handling` VALUES ('Modules/Session','raise','delete');
INSERT INTO `il_event_handling` VALUES ('Modules/Session','raise','enter');
INSERT INTO `il_event_handling` VALUES ('Modules/Session','raise','register');
INSERT INTO `il_event_handling` VALUES ('Modules/Session','raise','update');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','listen','Modules/Course');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','listen','Modules/Group');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','listen','Modules/OrgUnit');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','listen','Modules/StudyProgramme');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','listen','Services/AccessControl');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','listen','Services/ContainerReference');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','listen','Services/Tracking');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','listen','Services/Tree');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','listen','Services/User');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','raise','informUserToRestart');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','raise','userAssigned');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','raise','userDeassigned');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','raise','userReAssigned');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','raise','userRiskyToFail');
INSERT INTO `il_event_handling` VALUES ('Modules/StudyProgramme','raise','userSuccessful');
INSERT INTO `il_event_handling` VALUES ('Services/AccessControl','raise','assignUser');
INSERT INTO `il_event_handling` VALUES ('Services/AccessControl','raise','deassignUser');
INSERT INTO `il_event_handling` VALUES ('Services/Authentication','raise','afterLogin');
INSERT INTO `il_event_handling` VALUES ('Services/Authentication','raise','afterLogout');
INSERT INTO `il_event_handling` VALUES ('Services/Authentication','raise','beforeLogout');
INSERT INTO `il_event_handling` VALUES ('Services/Authentication','raise','expiredSessionDetected');
INSERT INTO `il_event_handling` VALUES ('Services/Authentication','raise','reachedSessionPoolLimit');
INSERT INTO `il_event_handling` VALUES ('Services/Badge','listen','Services/Tracking');
INSERT INTO `il_event_handling` VALUES ('Services/Badge','listen','Services/User');
INSERT INTO `il_event_handling` VALUES ('Services/Calendar','listen','Modules/Course');
INSERT INTO `il_event_handling` VALUES ('Services/Calendar','listen','Modules/Exercise');
INSERT INTO `il_event_handling` VALUES ('Services/Calendar','listen','Modules/Group');
INSERT INTO `il_event_handling` VALUES ('Services/Calendar','listen','Modules/Session');
INSERT INTO `il_event_handling` VALUES ('Services/Certificate','listen','Modules/StudyProgramme');
INSERT INTO `il_event_handling` VALUES ('Services/Certificate','listen','Services/Certificate');
INSERT INTO `il_event_handling` VALUES ('Services/Certificate','listen','Services/Tracking');
INSERT INTO `il_event_handling` VALUES ('Services/Certificate','listen','Services/User');
INSERT INTO `il_event_handling` VALUES ('Services/Certificate','raise','certificateIssued');
INSERT INTO `il_event_handling` VALUES ('Services/Contact','listen','Services/Contact');
INSERT INTO `il_event_handling` VALUES ('Services/Contact','listen','Services/User');
INSERT INTO `il_event_handling` VALUES ('Services/Contact','raise','contactRequested');
INSERT INTO `il_event_handling` VALUES ('Services/ContainerReference','listen','Modules/Category');
INSERT INTO `il_event_handling` VALUES ('Services/ContainerReference','listen','Modules/Course');
INSERT INTO `il_event_handling` VALUES ('Services/ContainerReference','listen','Modules/StudyProgramme');
INSERT INTO `il_event_handling` VALUES ('Services/ContainerReference','raise','deleteReference');
INSERT INTO `il_event_handling` VALUES ('Services/LTI','listen','Services/Tracking');
INSERT INTO `il_event_handling` VALUES ('Services/LTI','listen','Services/User');
INSERT INTO `il_event_handling` VALUES ('Services/Mail','raise','externalEmailDelegated');
INSERT INTO `il_event_handling` VALUES ('Services/Mail','raise','sentInternalMail');
INSERT INTO `il_event_handling` VALUES ('Services/News','raise','readNews');
INSERT INTO `il_event_handling` VALUES ('Services/News','raise','unreadNews');
INSERT INTO `il_event_handling` VALUES ('Services/Notification','listen','Service/Object');
INSERT INTO `il_event_handling` VALUES ('Services/Object','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Services/Object','raise','create');
INSERT INTO `il_event_handling` VALUES ('Services/Object','raise','delete');
INSERT INTO `il_event_handling` VALUES ('Services/Object','raise','toTrash');
INSERT INTO `il_event_handling` VALUES ('Services/Object','raise','undelete');
INSERT INTO `il_event_handling` VALUES ('Services/Object','raise','update');
INSERT INTO `il_event_handling` VALUES ('Services/OnScreenChat','listen','Modules/Chatroom');
INSERT INTO `il_event_handling` VALUES ('Services/OpenIdConnect','listen','Services/Authentication');
INSERT INTO `il_event_handling` VALUES ('Services/Repository','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Services/Search','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Services/Skill','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Services/Skill','listen','Services/Tracking');
INSERT INTO `il_event_handling` VALUES ('Services/Tagging','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Services/TermsOfService','listen','Services/User');
INSERT INTO `il_event_handling` VALUES ('Services/TermsOfService','raise','ilTermsOfServiceEventWithdrawn');
INSERT INTO `il_event_handling` VALUES ('Services/Tracking','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Services/Tracking','listen','Services/Tree');
INSERT INTO `il_event_handling` VALUES ('Services/Tracking','raise','updateStatus');
INSERT INTO `il_event_handling` VALUES ('Services/Tree','raise','deleteNode');
INSERT INTO `il_event_handling` VALUES ('Services/User','listen','Services/Object');
INSERT INTO `il_event_handling` VALUES ('Services/User','listen','Services/TermsOfService');
INSERT INTO `il_event_handling` VALUES ('Services/User','raise','afterCreate');
INSERT INTO `il_event_handling` VALUES ('Services/User','raise','afterUpdate');
INSERT INTO `il_event_handling` VALUES ('Services/User','raise','deleteUser');
INSERT INTO `il_event_handling` VALUES ('Services/WebServices','raise','newEcsEvent');
INSERT INTO `il_event_handling` VALUES ('Services/WebServices/ECS','listen','Modules/Course');
INSERT INTO `il_event_handling` VALUES ('Services/WebServices/ECS','listen','Modules/Group');
INSERT INTO `il_event_handling` VALUES ('Services/WebServices/ECS','listen','Services/User');

--
-- Table structure for table `il_exc_team`
--

CREATE TABLE `il_exc_team` (
  `id` int(11) NOT NULL DEFAULT 0,
  `ass_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ass_id`,`user_id`),
  KEY `i1_idx` (`id`)
) ;

--
-- Dumping data for table `il_exc_team`
--


--
-- Table structure for table `il_exc_team_log`
--

CREATE TABLE `il_exc_team_log` (
  `log_id` int(11) NOT NULL DEFAULT 0,
  `team_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `details` varchar(500) DEFAULT NULL,
  `action` tinyint(4) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`log_id`),
  KEY `i1_idx` (`team_id`)
) ;

--
-- Dumping data for table `il_exc_team_log`
--


--
-- Table structure for table `il_exc_team_log_seq`
--

CREATE TABLE `il_exc_team_log_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_exc_team_log_seq`
--


--
-- Table structure for table `il_exc_team_seq`
--

CREATE TABLE `il_exc_team_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_exc_team_seq`
--


--
-- Table structure for table `il_external_feed_block`
--

CREATE TABLE `il_external_feed_block` (
  `id` int(11) NOT NULL DEFAULT 0,
  `feed_url` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_external_feed_block`
--


--
-- Table structure for table `il_external_feed_block_seq`
--

CREATE TABLE `il_external_feed_block_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_external_feed_block_seq`
--


--
-- Table structure for table `il_gc_memcache_server`
--

CREATE TABLE `il_gc_memcache_server` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `status` tinyint(4) DEFAULT NULL,
  `host` varchar(256) DEFAULT NULL,
  `port` bigint(20) DEFAULT NULL,
  `weight` smallint(6) DEFAULT NULL,
  `flush_needed` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_gc_memcache_server`
--


--
-- Table structure for table `il_gc_memcache_server_seq`
--

CREATE TABLE `il_gc_memcache_server_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_gc_memcache_server_seq`
--


--
-- Table structure for table `il_html_block`
--

CREATE TABLE `il_html_block` (
  `id` int(11) NOT NULL DEFAULT 0,
  `content` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_html_block`
--


--
-- Table structure for table `il_md_cpr_selections`
--

CREATE TABLE `il_md_cpr_selections` (
  `entry_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(128) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `copyright` longtext DEFAULT NULL,
  `language` char(2) DEFAULT NULL,
  `costs` tinyint(4) NOT NULL DEFAULT 0,
  `cpr_restrictions` tinyint(4) NOT NULL DEFAULT 1,
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  `outdated` tinyint(4) NOT NULL DEFAULT 0,
  `position` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`entry_id`)
) ;

--
-- Dumping data for table `il_md_cpr_selections`
--

INSERT INTO `il_md_cpr_selections` VALUES (1,'Attribution Non-commercial No Derivatives (by-nc-nd)','Creative Commons License','<a rel=\"license\" href=\"http://creativecommons.org/licenses/by-nc-nd/4.0/\"><img alt=\"Creative Commons License\" style=\"border-width:0\" src=\"https://i.creativecommons.org/l/by-nc-nd/4.0/88x31.png\" /></a><br />This work is licensed under a <a rel=\"license\" href=\"http://creativecommons.org/licenses/by-nc-nd/4.0/\">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>.','en',0,1,0,0,0);
INSERT INTO `il_md_cpr_selections` VALUES (2,'Attribution Non-commercial Share Alike (by-nc-sa)','Creative Commons License','<a rel=\"license\" href=\"http://creativecommons.org/licenses/by-nc-sa/4.0/\"><img alt=\"Creative Commons License\" style=\"border-width:0\" src=\"https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png\" /></a><br />This work is licensed under a <a rel=\"license\" href=\"http://creativecommons.org/licenses/by-nc-sa/4.0/\">Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License</a>.','en',0,1,0,0,0);
INSERT INTO `il_md_cpr_selections` VALUES (3,'Attribution Non-commercial (by-nc)','Creative Commons License','<a rel=\"license\" href=\"http://creativecommons.org/licenses/by-nc/4.0/\"><img alt=\"Creative Commons License\" style=\"border-width:0\" src=\"https://i.creativecommons.org/l/by-nc/4.0/88x31.png\" /></a><br />This work is licensed under a <a rel=\"license\" href=\"http://creativecommons.org/licenses/by-nc/4.0/\">Creative Commons Attribution-NonCommercial 4.0 International License</a>.','en',0,1,0,0,0);
INSERT INTO `il_md_cpr_selections` VALUES (4,'Attribution No Derivatives (by-nd)','Creative Commons License','<a rel=\"license\" href=\"http://creativecommons.org/licenses/by-nd/4.0/\"><img alt=\"Creative Commons License\" style=\"border-width:0\" src=\"https://i.creativecommons.org/l/by-nd/4.0/88x31.png\" /></a><br />This work is licensed under a <a rel=\"license\" href=\"http://creativecommons.org/licenses/by-nd/4.0/\">Creative Commons Attribution-NoDerivatives 4.0 International License</a>.','en',0,1,0,0,0);
INSERT INTO `il_md_cpr_selections` VALUES (5,'Attribution Share Alike (by-sa)','Creative Commons License','<a rel=\"license\" href=\"http://creativecommons.org/licenses/by-sa/4.0/\"><img alt=\"Creative Commons License\" style=\"border-width:0\" src=\"https://i.creativecommons.org/l/by-sa/4.0/88x31.png\" /></a><br />This work is licensed under a <a rel=\"license\" href=\"http://creativecommons.org/licenses/by-sa/4.0/\">Creative Commons Attribution-ShareAlike 4.0 International License</a>.','en',0,1,0,0,0);
INSERT INTO `il_md_cpr_selections` VALUES (6,'Attribution (by)','Creative Commons License','<a rel=\"license\" href=\"http://creativecommons.org/licenses/by/4.0/\"><img alt=\"Creative Commons License\" style=\"border-width:0\" src=\"https://i.creativecommons.org/l/by/4.0/88x31.png\" /></a><br />This work is licensed under a <a rel=\"license\" href=\"http://creativecommons.org/licenses/by/4.0/\">Creative Commons Attribution 4.0 International License</a>.','en',0,1,0,0,0);
INSERT INTO `il_md_cpr_selections` VALUES (7,'All rights reserved','','This work has all rights reserved by the owner.','en',0,1,1,0,0);

--
-- Table structure for table `il_md_cpr_selections_seq`
--

CREATE TABLE `il_md_cpr_selections_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=8;

--
-- Dumping data for table `il_md_cpr_selections_seq`
--

INSERT INTO `il_md_cpr_selections_seq` VALUES (7);

--
-- Table structure for table `il_media_cast_data`
--

CREATE TABLE `il_media_cast_data` (
  `id` int(11) NOT NULL DEFAULT 0,
  `is_online` tinyint(4) DEFAULT 0,
  `public_files` tinyint(4) DEFAULT 0,
  `downloadable` tinyint(4) DEFAULT 0,
  `def_access` tinyint(4) DEFAULT 0,
  `sortmode` tinyint(4) DEFAULT 3,
  `viewmode` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_media_cast_data`
--


--
-- Table structure for table `il_media_cast_data_ord`
--

CREATE TABLE `il_media_cast_data_ord` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `item_id` int(11) NOT NULL DEFAULT 0,
  `pos` mediumint(9) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`item_id`)
) ;

--
-- Dumping data for table `il_media_cast_data_ord`
--


--
-- Table structure for table `il_meta_annotation`
--

CREATE TABLE `il_meta_annotation` (
  `meta_annotation_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `entity` longtext DEFAULT NULL,
  `a_date` longtext DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `description_language` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_annotation_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_annotation`
--


--
-- Table structure for table `il_meta_annotation_seq`
--

CREATE TABLE `il_meta_annotation_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_annotation_seq`
--


--
-- Table structure for table `il_meta_classification`
--

CREATE TABLE `il_meta_classification` (
  `meta_classification_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `purpose` varchar(32) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `description_language` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_classification_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_classification`
--


--
-- Table structure for table `il_meta_classification_seq`
--

CREATE TABLE `il_meta_classification_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_classification_seq`
--


--
-- Table structure for table `il_meta_contribute`
--

CREATE TABLE `il_meta_contribute` (
  `meta_contribute_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(32) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `role` varchar(32) DEFAULT NULL,
  `c_date` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`meta_contribute_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_contribute`
--


--
-- Table structure for table `il_meta_contribute_seq`
--

CREATE TABLE `il_meta_contribute_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_contribute_seq`
--


--
-- Table structure for table `il_meta_description`
--

CREATE TABLE `il_meta_description` (
  `meta_description_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(16) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `description_language` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_description_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_description`
--


--
-- Table structure for table `il_meta_description_seq`
--

CREATE TABLE `il_meta_description_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_description_seq`
--


--
-- Table structure for table `il_meta_educational`
--

CREATE TABLE `il_meta_educational` (
  `meta_educational_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `interactivity_type` varchar(16) DEFAULT NULL,
  `learning_resource_type` varchar(32) DEFAULT NULL,
  `interactivity_level` varchar(16) DEFAULT NULL,
  `semantic_density` varchar(16) DEFAULT NULL,
  `intended_end_user_role` varchar(16) DEFAULT NULL,
  `context` varchar(16) DEFAULT NULL,
  `difficulty` varchar(16) DEFAULT NULL,
  `typical_learning_time` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`meta_educational_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_educational`
--


--
-- Table structure for table `il_meta_educational_seq`
--

CREATE TABLE `il_meta_educational_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_educational_seq`
--


--
-- Table structure for table `il_meta_entity`
--

CREATE TABLE `il_meta_entity` (
  `meta_entity_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(16) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `entity` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`meta_entity_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_entity`
--


--
-- Table structure for table `il_meta_entity_seq`
--

CREATE TABLE `il_meta_entity_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_entity_seq`
--


--
-- Table structure for table `il_meta_format`
--

CREATE TABLE `il_meta_format` (
  `meta_format_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `format` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`meta_format_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`),
  KEY `i2_idx` (`format`)
) ;

--
-- Dumping data for table `il_meta_format`
--


--
-- Table structure for table `il_meta_format_seq`
--

CREATE TABLE `il_meta_format_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_format_seq`
--


--
-- Table structure for table `il_meta_general`
--

CREATE TABLE `il_meta_general` (
  `meta_general_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `general_structure` varchar(16) DEFAULT NULL,
  `title` varchar(4000) DEFAULT NULL,
  `title_language` char(2) DEFAULT NULL,
  `coverage` varchar(4000) DEFAULT NULL,
  `coverage_language` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_general_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_general`
--


--
-- Table structure for table `il_meta_general_seq`
--

CREATE TABLE `il_meta_general_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_general_seq`
--


--
-- Table structure for table `il_meta_identifier`
--

CREATE TABLE `il_meta_identifier` (
  `meta_identifier_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(16) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `catalog` varchar(4000) DEFAULT NULL,
  `entry` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`meta_identifier_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_identifier`
--


--
-- Table structure for table `il_meta_identifier_`
--

CREATE TABLE `il_meta_identifier_` (
  `meta_identifier__id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(16) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `catalog` varchar(4000) DEFAULT NULL,
  `entry` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`meta_identifier__id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_identifier_`
--


--
-- Table structure for table `il_meta_identifier__seq`
--

CREATE TABLE `il_meta_identifier__seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_identifier__seq`
--


--
-- Table structure for table `il_meta_identifier_seq`
--

CREATE TABLE `il_meta_identifier_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_identifier_seq`
--


--
-- Table structure for table `il_meta_keyword`
--

CREATE TABLE `il_meta_keyword` (
  `meta_keyword_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(32) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `keyword` varchar(4000) DEFAULT NULL,
  `keyword_language` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_keyword_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_keyword`
--


--
-- Table structure for table `il_meta_keyword_seq`
--

CREATE TABLE `il_meta_keyword_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_keyword_seq`
--


--
-- Table structure for table `il_meta_language`
--

CREATE TABLE `il_meta_language` (
  `meta_language_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` char(6) DEFAULT NULL,
  `parent_type` char(16) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `language` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_language_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_language`
--


--
-- Table structure for table `il_meta_language_seq`
--

CREATE TABLE `il_meta_language_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_language_seq`
--


--
-- Table structure for table `il_meta_lifecycle`
--

CREATE TABLE `il_meta_lifecycle` (
  `meta_lifecycle_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `lifecycle_status` varchar(16) DEFAULT NULL,
  `meta_version` varchar(4000) DEFAULT NULL,
  `version_language` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_lifecycle_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_lifecycle`
--


--
-- Table structure for table `il_meta_lifecycle_seq`
--

CREATE TABLE `il_meta_lifecycle_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_lifecycle_seq`
--


--
-- Table structure for table `il_meta_location`
--

CREATE TABLE `il_meta_location` (
  `meta_location_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(16) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `location` varchar(4000) DEFAULT NULL,
  `location_type` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`meta_location_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_location`
--


--
-- Table structure for table `il_meta_location_seq`
--

CREATE TABLE `il_meta_location_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_location_seq`
--


--
-- Table structure for table `il_meta_meta_data`
--

CREATE TABLE `il_meta_meta_data` (
  `meta_meta_data_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` char(6) DEFAULT NULL,
  `meta_data_scheme` char(16) DEFAULT NULL,
  `language` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_meta_data_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_meta_data`
--


--
-- Table structure for table `il_meta_meta_data_seq`
--

CREATE TABLE `il_meta_meta_data_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_meta_data_seq`
--


--
-- Table structure for table `il_meta_oer_stat`
--

CREATE TABLE `il_meta_oer_stat` (
  `obj_id` int(11) NOT NULL,
  `href_id` int(11) NOT NULL,
  `blocked` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `il_meta_oer_stat`
--


--
-- Table structure for table `il_meta_relation`
--

CREATE TABLE `il_meta_relation` (
  `meta_relation_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` char(6) DEFAULT NULL,
  `kind` char(16) DEFAULT NULL,
  PRIMARY KEY (`meta_relation_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_relation`
--


--
-- Table structure for table `il_meta_relation_seq`
--

CREATE TABLE `il_meta_relation_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_relation_seq`
--


--
-- Table structure for table `il_meta_requirement`
--

CREATE TABLE `il_meta_requirement` (
  `meta_requirement_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(16) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `operating_system_name` varchar(16) DEFAULT NULL,
  `os_min_version` char(255) DEFAULT NULL,
  `os_max_version` char(255) DEFAULT NULL,
  `browser_name` varchar(32) DEFAULT NULL,
  `browser_minimum_version` char(255) DEFAULT NULL,
  `browser_maximum_version` char(255) DEFAULT NULL,
  `or_composite_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`meta_requirement_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_requirement`
--


--
-- Table structure for table `il_meta_requirement_seq`
--

CREATE TABLE `il_meta_requirement_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_requirement_seq`
--


--
-- Table structure for table `il_meta_rights`
--

CREATE TABLE `il_meta_rights` (
  `meta_rights_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `costs` char(3) DEFAULT NULL,
  `cpr_and_or` char(3) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `description_language` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_rights_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_rights`
--


--
-- Table structure for table `il_meta_rights_seq`
--

CREATE TABLE `il_meta_rights_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_rights_seq`
--


--
-- Table structure for table `il_meta_tar`
--

CREATE TABLE `il_meta_tar` (
  `meta_tar_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(16) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `typical_age_range` varchar(4000) DEFAULT NULL,
  `tar_language` char(2) DEFAULT NULL,
  `tar_min` char(2) DEFAULT NULL,
  `tar_max` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_tar_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_tar`
--


--
-- Table structure for table `il_meta_tar_seq`
--

CREATE TABLE `il_meta_tar_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_tar_seq`
--


--
-- Table structure for table `il_meta_taxon`
--

CREATE TABLE `il_meta_taxon` (
  `meta_taxon_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(32) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `taxon` varchar(4000) DEFAULT NULL,
  `taxon_language` char(2) DEFAULT NULL,
  `taxon_id` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`meta_taxon_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_taxon`
--


--
-- Table structure for table `il_meta_taxon_path`
--

CREATE TABLE `il_meta_taxon_path` (
  `meta_taxon_path_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `parent_type` varchar(32) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `source` varchar(4000) DEFAULT NULL,
  `source_language` char(2) DEFAULT NULL,
  PRIMARY KEY (`meta_taxon_path_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_taxon_path`
--


--
-- Table structure for table `il_meta_taxon_path_seq`
--

CREATE TABLE `il_meta_taxon_path_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_taxon_path_seq`
--


--
-- Table structure for table `il_meta_taxon_seq`
--

CREATE TABLE `il_meta_taxon_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_taxon_seq`
--


--
-- Table structure for table `il_meta_technical`
--

CREATE TABLE `il_meta_technical` (
  `meta_technical_id` int(11) NOT NULL DEFAULT 0,
  `rbac_id` int(11) DEFAULT NULL,
  `obj_id` int(11) DEFAULT NULL,
  `obj_type` varchar(6) DEFAULT NULL,
  `t_size` varchar(4000) DEFAULT NULL,
  `ir` varchar(4000) DEFAULT NULL,
  `ir_language` char(2) DEFAULT NULL,
  `opr` varchar(4000) DEFAULT NULL,
  `opr_language` char(2) DEFAULT NULL,
  `duration` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`meta_technical_id`),
  KEY `i1_idx` (`rbac_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_meta_technical`
--


--
-- Table structure for table `il_meta_technical_seq`
--

CREATE TABLE `il_meta_technical_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_meta_technical_seq`
--


--
-- Table structure for table `il_mm_actions`
--

CREATE TABLE `il_mm_actions` (
  `identification` varchar(255) NOT NULL,
  `action` varchar(4000) DEFAULT NULL,
  `external` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`identification`)
) ;

--
-- Dumping data for table `il_mm_actions`
--


--
-- Table structure for table `il_mm_custom_items`
--

CREATE TABLE `il_mm_custom_items` (
  `identifier` varchar(255) NOT NULL,
  `type` varchar(128) DEFAULT NULL,
  `action` varchar(4000) DEFAULT NULL,
  `top_item` tinyint(4) DEFAULT NULL,
  `default_title` varchar(4000) DEFAULT NULL,
  `role_based_visibility` tinyint(4) DEFAULT 0,
  `global_role_ids` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`identifier`)
) ;

--
-- Dumping data for table `il_mm_custom_items`
--


--
-- Table structure for table `il_mm_items`
--

CREATE TABLE `il_mm_items` (
  `identification` varchar(255) NOT NULL DEFAULT '',
  `active` tinyint(4) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `parent_identification` varchar(255) DEFAULT NULL,
  `icon_id` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`identification`)
) ;

--
-- Dumping data for table `il_mm_items`
--


--
-- Table structure for table `il_mm_translation`
--

CREATE TABLE `il_mm_translation` (
  `id` varchar(255) NOT NULL,
  `identification` varchar(255) DEFAULT NULL,
  `translation` varchar(4000) DEFAULT NULL,
  `language_key` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_mm_translation`
--


--
-- Table structure for table `il_new_item_grp`
--

CREATE TABLE `il_new_item_grp` (
  `id` int(11) NOT NULL DEFAULT 0,
  `titles` varchar(1000) DEFAULT NULL,
  `pos` smallint(6) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_new_item_grp`
--


--
-- Table structure for table `il_new_item_grp_seq`
--

CREATE TABLE `il_new_item_grp_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_new_item_grp_seq`
--


--
-- Table structure for table `il_news_item`
--

CREATE TABLE `il_news_item` (
  `id` int(11) NOT NULL DEFAULT 0,
  `priority` int(11) DEFAULT 1,
  `title` varchar(200) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `context_obj_id` int(11) DEFAULT NULL,
  `context_obj_type` varchar(10) DEFAULT NULL,
  `context_sub_obj_id` int(11) DEFAULT NULL,
  `context_sub_obj_type` varchar(10) DEFAULT NULL,
  `content_type` char(5) DEFAULT 'text',
  `creation_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `visibility` char(6) DEFAULT 'users',
  `content_long` longtext DEFAULT NULL,
  `content_is_lang_var` tinyint(4) DEFAULT 0,
  `mob_id` int(11) DEFAULT NULL,
  `playtime` varchar(8) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `content_text_is_lang_var` tinyint(4) NOT NULL DEFAULT 0,
  `mob_cnt_download` int(11) NOT NULL DEFAULT 0,
  `mob_cnt_play` int(11) NOT NULL DEFAULT 0,
  `content_html` tinyint(4) NOT NULL DEFAULT 0,
  `update_user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`context_obj_id`),
  KEY `i2_idx` (`creation_date`),
  KEY `mo_idx` (`mob_id`)
) ;

--
-- Dumping data for table `il_news_item`
--


--
-- Table structure for table `il_news_item_seq`
--

CREATE TABLE `il_news_item_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_news_item_seq`
--


--
-- Table structure for table `il_news_read`
--

CREATE TABLE `il_news_read` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `news_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`news_id`),
  KEY `i1_idx` (`user_id`),
  KEY `i2_idx` (`news_id`)
) ;

--
-- Dumping data for table `il_news_read`
--


--
-- Table structure for table `il_news_subscription`
--

CREATE TABLE `il_news_subscription` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`ref_id`)
) ;

--
-- Dumping data for table `il_news_subscription`
--


--
-- Table structure for table `il_object_def`
--

CREATE TABLE `il_object_def` (
  `id` char(10) NOT NULL DEFAULT '',
  `class_name` varchar(200) DEFAULT NULL,
  `component` varchar(200) DEFAULT NULL,
  `location` varchar(250) DEFAULT NULL,
  `checkbox` tinyint(4) NOT NULL DEFAULT 0,
  `inherit` tinyint(4) NOT NULL DEFAULT 0,
  `translate` char(5) DEFAULT NULL,
  `devmode` tinyint(4) NOT NULL DEFAULT 0,
  `allow_link` tinyint(4) NOT NULL DEFAULT 0,
  `allow_copy` tinyint(4) NOT NULL DEFAULT 0,
  `rbac` tinyint(4) NOT NULL DEFAULT 0,
  `system` tinyint(4) NOT NULL DEFAULT 0,
  `sideblock` tinyint(4) NOT NULL DEFAULT 0,
  `default_pos` int(11) NOT NULL DEFAULT 0,
  `grp` char(10) DEFAULT NULL,
  `default_pres_pos` int(11) NOT NULL DEFAULT 0,
  `export` tinyint(4) NOT NULL DEFAULT 0,
  `repository` tinyint(4) NOT NULL DEFAULT 1,
  `workspace` tinyint(4) NOT NULL DEFAULT 0,
  `administration` tinyint(4) NOT NULL DEFAULT 0,
  `amet` tinyint(4) NOT NULL DEFAULT 0,
  `orgunit_permissions` tinyint(4) NOT NULL DEFAULT 0,
  `lti_provider` tinyint(4) NOT NULL DEFAULT 0,
  `offline_handling` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_object_def`
--

INSERT INTO `il_object_def` VALUES ('accs','AccessibilitySettings','Services/Accessibility','Services/Accessibility/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('adm','SystemFolder','Modules/SystemFolder','Modules/SystemFolder/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('adn','AdministrativeNotification','Services/AdministrativeNotification','Services/AdministrativeNotification/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('adve','AdvancedEditing','Services/AdvancedEditing','Services/AdvancedEditing/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('assf','AssessmentFolder','Modules/Test','Modules/Test/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('auth','AuthSettings','Services/Authentication','Services/Authentication/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('awra','AwarenessAdministration','Services/Awareness','Services/Awareness/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('bdga','BadgeAdministration','Services/Badge','Services/Badge/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('bibl','Bibliographic','Modules/Bibliographic','Modules/Bibliographic/classes',1,1,'0',0,1,1,1,0,0,360,NULL,360,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('bibs','BibliographicAdmin','Modules/Bibliographic','Modules/Bibliographic/classes/Admin',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('blga','BlogAdministration','Modules/Blog','Modules/Blog/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('blog','Blog','Modules/Blog','Modules/Blog/classes',1,1,'0',0,1,1,1,0,0,330,NULL,330,1,1,1,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('book','BookingPool','Modules/BookingManager','Modules/BookingManager/classes',1,1,NULL,0,1,1,1,0,0,250,NULL,250,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('cadm','ContactAdministration','Services/Contact','Services/Contact/classes',0,1,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('cals','CalendarSettings','Services/Calendar','Services/Calendar/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('cat','Category','Modules/Category','Modules/Category/classes',1,1,'db',0,0,1,1,0,0,10,'cat',10,1,1,0,0,1,0,0,0);
INSERT INTO `il_object_def` VALUES ('catr','CategoryReference','Modules/CategoryReference','Modules/CategoryReference/classes',1,0,'0',0,0,1,1,0,0,15,'cat',10,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('cert','CertificateSettings','Services/Certificate','Services/Certificate/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('chta','ChatroomAdmin','Modules/Chatroom','Modules/Chatroom/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('chtr','Chatroom','Modules/Chatroom','Modules/Chatroom/classes',1,0,'0',0,1,1,1,0,0,90,NULL,150,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('cld','Cloud','Modules/Cloud','Modules/Cloud/classes',1,1,'0',0,1,0,1,0,0,440,NULL,440,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('cmis','CmiXapiAdministration','Modules/CmiXapi','Modules/CmiXapi/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('cmix','CmiXapi','Modules/CmiXapi','Modules/CmiXapi/classes',1,1,'db',0,1,1,1,0,0,120,'lres',0,1,1,0,0,0,0,0,1);
INSERT INTO `il_object_def` VALUES ('cmps','ComponentSettings','Services/Component','Services/Component/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('cmxv','CmiXapiVerification','Modules/CmiXapi','Modules/CmiXapi/classes/Verification',0,0,'0',0,0,0,0,0,0,90,NULL,150,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('coms','CommentsSettings','Services/Notes','Services/Notes/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('copa','ContentPage','Modules/ContentPage','Modules/ContentPage/classes',1,1,'db',0,1,1,1,0,0,340,NULL,340,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('cpad','ContentPageAdministration','Modules/ContentPage','Modules/ContentPage/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('crs','Course','Modules/Course','Modules/Course/classes',1,1,'db',0,0,1,1,0,0,20,'crs',30,1,1,0,0,1,1,1,1);
INSERT INTO `il_object_def` VALUES ('crsr','CourseReference','Modules/CourseReference','Modules/CourseReference/classes',1,0,'0',0,0,1,1,0,0,25,'crs',20,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('crss','CourseAdministration','Modules/Course','Modules/Course/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('crsv','CourseVerification','Modules/Course','Modules/Course/classes/Verification',0,0,'0',0,0,0,0,0,0,90,NULL,150,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('dcl','DataCollection','Modules/DataCollection','Modules/DataCollection/classes',1,1,'0',0,1,1,1,0,0,340,NULL,340,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('dshs','DashboardSettings','Services/Dashboard','Services/Dashboard/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('ecss','ECSSettings','Services/WebServices','Services/WebServices/ECS/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('exc','Exercise','Modules/Exercise','Modules/Exercise/classes',1,1,NULL,0,1,1,1,0,0,170,NULL,160,1,1,0,0,1,1,0,0);
INSERT INTO `il_object_def` VALUES ('excs','ExerciseAdministration','Modules/Exercise','Modules/Exercise/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('excv','ExerciseVerification','Modules/Exercise','Modules/Exercise/classes',0,0,'0',0,0,0,0,0,0,100,NULL,160,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('extt','ExternalToolsSettings','Services/Administration','Services/Administration/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('facs','FileAccessSettings','Modules/File','Modules/File/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('feed','ExternalFeed','Modules/ExternalFeed','Modules/ExternalFeed/classes',1,1,NULL,0,1,1,1,0,1,80,NULL,0,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('file','File','Modules/File','Modules/File/classes',1,0,'0',0,1,1,1,0,0,90,NULL,150,1,1,1,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('fils','FileServices','Services/FileServices','Services/FileServices/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('fold','Folder','Modules/Folder','Modules/Folder/classes',1,1,'db',0,0,1,1,0,0,40,NULL,20,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('frm','Forum','Modules/Forum','Modules/Forum/classes',1,1,'0',0,1,1,1,0,0,70,NULL,90,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('frma','ForumAdministration','Modules/Forum','Modules/Forum/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('glo','Glossary','Modules/Glossary','Modules/Glossary/classes',1,1,'0',0,1,1,1,0,0,160,NULL,110,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('grp','Group','Modules/Group','Modules/Group/classes',1,1,'db',0,0,1,1,0,0,50,NULL,70,1,1,0,0,1,1,1,0);
INSERT INTO `il_object_def` VALUES ('grpr','GroupReference','Modules/GroupReference','Modules/GroupReference/classes',1,0,'0',0,0,1,1,0,0,51,'grp',20,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('grps','GroupAdministration','Modules/Group','Modules/Group/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('hlps','HelpSettings','Services/Help','Services/Help/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('htlm','FileBasedLM','Modules/HTMLLearningModule','Modules/HTMLLearningModule/classes',1,1,'0',0,1,1,1,0,0,130,'lres',0,1,1,0,0,0,0,0,1);
INSERT INTO `il_object_def` VALUES ('iass','IndividualAssessment','Modules/IndividualAssessment','Modules/IndividualAssessment/classes',1,1,'0',0,1,1,1,0,0,190,NULL,190,1,1,0,0,1,1,0,0);
INSERT INTO `il_object_def` VALUES ('itgr','ItemGroup','Modules/ItemGroup','Modules/ItemGroup/classes',1,0,'0',0,0,1,1,0,0,45,NULL,5,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('lhts','LearningHistorySettings','Services/LearningHistory','Services/LearningHistory/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('lm','LearningModule','Modules/LearningModule','Modules/LearningModule/classes',1,1,'db',0,1,1,1,0,0,120,'lres',0,1,1,0,0,0,0,1,1);
INSERT INTO `il_object_def` VALUES ('lng','Language','Services/Language','Services/Language/classes',1,0,'0',0,0,0,0,0,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('lngf','LanguageFolder','Services/Language','Services/Language/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('logs','LoggingSettings','Services/Logging','Services/Logging/classes',0,1,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('lrss','LearningResourcesSettings','Modules/LearningModule','Modules/LearningModule/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('lso','LearningSequence','Modules/LearningSequence','Modules/LearningSequence/classes',1,1,'0',0,0,1,1,0,0,30,'lso',300,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('lsos','LearningSequenceAdmin','Modules/LearningSequence','Modules/LearningSequence/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('lti','LTIConsumer','Modules/LTIConsumer','Modules/LTIConsumer/classes',1,1,'db',0,1,1,1,0,0,120,'lres',0,0,1,0,0,0,0,0,1);
INSERT INTO `il_object_def` VALUES ('ltis','LTIAdministration','Services/LTI','Services/LTI/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('ltiv','LTIConsumerVerification','Modules/LTIConsumer','Modules/LTIConsumer/classes/Verification',0,0,'0',0,0,0,0,0,0,90,NULL,150,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('mail','Mail','Services/Mail','Services/Mail/classes',0,0,'sys',0,0,0,1,0,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('mcst','MediaCast','Modules/MediaCast','Modules/MediaCast/classes',1,1,NULL,0,1,1,1,0,0,110,NULL,130,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('mcts','MediaCastSettings','Modules/MediaCast','Modules/MediaCast/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('mds','MDSettings','Services/MetaData','Services/MetaData/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('mep','MediaPool','Modules/MediaPool','Modules/MediaPool/classes',1,1,'db',0,1,1,1,0,0,200,NULL,190,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('mme','MainMenu','Services/MainMenu','Services/MainMenu/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('mobs','MediaObjectsSettings','Services/MediaObjects','Services/MediaObjects/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('nota','NotificationAdmin','Services/Notifications','Services/Notifications/classes',0,0,'sys',0,0,0,0,1,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('nots','NotesSettings','Services/Notes','Services/Notes/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('nwss','NewsSettings','Services/News','Services/News/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('objf','ObjectFolder','Services/Object','Services/Object/classes',0,0,'sys',1,0,0,1,1,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('orgu','OrgUnit','Modules/OrgUnit','Modules/OrgUnit/classes',1,1,'db',0,0,0,1,0,0,10,'orgu',10,1,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('otpl','ObjectTemplateAdministration','Services/DidacticTemplate','Services/DidacticTemplate/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('pdfg','PDFGeneration','Services/PDFGeneration','Services/PDFGeneration/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('poll','Poll','Modules/Poll','Modules/Poll/classes',1,1,'0',0,1,1,1,0,1,350,NULL,350,1,1,1,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('prfa','PortfolioAdministration','Modules/Portfolio','Modules/Portfolio/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('prg','StudyProgramme','Modules/StudyProgramme','Modules/StudyProgramme/classes',1,1,'db',0,0,1,1,0,0,30,NULL,30,0,1,0,0,0,1,0,0);
INSERT INTO `il_object_def` VALUES ('prgr','StudyProgrammeReference','Modules/StudyProgrammeReference','Modules/StudyProgrammeReference/classes',1,0,'0',0,1,1,1,0,0,25,'prg',20,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('prgs','StudyProgrammeAdmin','Modules/StudyProgramme','Modules/StudyProgramme/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('prss','PersonalWorkspaceSettings','Services/PersonalWorkspace','Services/PersonalWorkspace/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('prtf','Portfolio','Modules/Portfolio','Modules/Portfolio/classes',0,0,'0',0,0,0,0,0,0,0,NULL,0,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('prtt','PortfolioTemplate','Modules/Portfolio','Modules/Portfolio/classes',0,0,'0',0,1,1,1,0,0,500,NULL,500,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('ps','PrivacySecurity','Services/PrivacySecurity','Services/PrivacySecurity/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('qpl','QuestionPool','Modules/TestQuestionPool','Modules/TestQuestionPool/classes',1,1,'0',0,1,1,1,0,0,210,NULL,200,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('rcat','RemoteCategory','Modules/RemoteCategory','Modules/RemoteCategory/classes',1,0,'0',0,1,1,1,0,0,30,'cat',40,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('rcrs','RemoteCourse','Modules/RemoteCourse','Modules/RemoteCourse/classes',1,0,'0',0,1,1,1,0,0,30,'crs',40,0,0,0,0,1,0,0,0);
INSERT INTO `il_object_def` VALUES ('recf','RecoveryFolder','Services/Administration','Services/Administration/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('reps','RepositorySettings','Services/Repository','Services/Repository/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('rfil','RemoteFile','Modules/RemoteFile','Modules/RemoteFile/classes',1,0,'0',0,1,1,1,0,0,30,'file',40,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('rglo','RemoteGlossary','Modules/RemoteGlossary','Modules/RemoteGlossary/classes',1,0,'0',0,1,1,1,0,0,30,'glo',40,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('rgrp','RemoteGroup','Modules/RemoteGroup','Modules/RemoteGroup/classes',1,0,'0',0,1,1,1,0,0,30,'grp',40,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('rlm','RemoteLearningModule','Modules/RemoteLearningModule','Modules/RemoteLearningModule/classes',1,0,'0',0,1,1,1,0,0,30,'lres',40,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('role','Role','Services/AccessControl','Services/AccessControl/classes',1,0,'0',0,0,0,0,0,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('rolf','RoleFolder','Services/AccessControl','Services/AccessControl/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('rolt','RoleTemplate','Services/AccessControl','Services/AccessControl/classes',1,0,'0',0,0,0,0,0,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('root','RootFolder','Modules/RootFolder','Modules/RootFolder/classes',0,0,'0',0,0,0,1,1,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('rtst','RemoteTest','Modules/RemoteTest','Modules/RemoteTest/classes',1,0,'0',0,1,1,1,0,0,30,'tst',40,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('rwik','RemoteWiki','Modules/RemoteWiki','Modules/RemoteWiki/classes',1,0,'0',0,1,1,1,0,0,30,'wiki',40,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('sahs','SAHSLearningModule','Modules/ScormAicc','Modules/ScormAicc/classes',1,1,'0',0,1,1,1,0,0,140,'lres',0,1,1,0,0,0,0,1,1);
INSERT INTO `il_object_def` VALUES ('scov','SCORMVerification','Modules/ScormAicc','Modules/ScormAicc/classes/Verification',0,0,'0',0,0,0,0,0,0,90,NULL,150,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('seas','SearchSettings','Services/Search','Services/Search/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('sess','Session','Modules/Session','Modules/Session/classes',1,0,'0',0,0,1,1,0,0,15,NULL,15,1,1,0,0,1,0,0,0);
INSERT INTO `il_object_def` VALUES ('skmg','SkillManagement','Services/Skill','Services/Skill/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('spl','SurveyQuestionPool','Modules/SurveyQuestionPool','Modules/SurveyQuestionPool/classes',1,1,'0',0,1,1,1,0,0,220,NULL,210,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('sty','StyleSheet','Services/Style','Services/Style/Content/classes',1,0,'0',0,0,0,0,0,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('stys','StyleSettings','Services/Style','Services/Style/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('svy','Survey','Modules/Survey','Modules/Survey/classes',1,1,'0',0,1,1,1,0,0,190,NULL,180,1,1,0,0,0,1,1,1);
INSERT INTO `il_object_def` VALUES ('svyf','SurveyAdministration','Modules/Survey','Modules/Survey/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('sysc','SystemCheck','Services/SystemCheck','Services/SystemCheck/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('tags','TaggingSettings','Services/Tagging','Services/Tagging/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('tax','Taxonomy','Services/Taxonomy','Services/Taxonomy/classes',1,0,'0',0,0,0,0,0,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('taxs','TaxonomyAdministration','Services/Taxonomy','Services/Taxonomy/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('tos','TermsOfService','Services/TermsOfService','Services/TermsOfService/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('trac','UserTracking','Services/Tracking','Services/Tracking/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('tst','Test','Modules/Test','Modules/Test/classes',1,1,'0',0,1,1,1,0,0,180,NULL,170,1,1,0,0,0,1,1,1);
INSERT INTO `il_object_def` VALUES ('tstv','TestVerification','Modules/Test','Modules/Test/classes',0,0,'0',0,0,0,0,0,0,90,NULL,150,0,0,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('usr','User','Services/User','Services/User/classes',1,0,'0',0,0,0,0,0,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('usrf','UserFolder','Services/User','Services/User/classes',0,1,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,1,0,0);
INSERT INTO `il_object_def` VALUES ('wbdv','WebDAV','Services/WebDAV','Services/WebDAV/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('wbrs','WebResourceAdministration','Modules/WebResource','Modules/WebResource/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('webr','LinkResource','Modules/WebResource','Modules/WebResource/classes',1,0,'0',0,1,1,1,0,0,100,NULL,120,1,1,1,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('wfe','WorkflowEngine','Services/WorkflowEngine','Services/WorkflowEngine/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('wfld','WorkspaceFolder','Modules/WorkspaceFolder','Modules/WorkspaceFolder/classes',1,1,'0',0,0,0,0,0,0,300,NULL,300,0,0,1,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('wiki','Wiki','Modules/Wiki','Modules/Wiki/classes',1,1,NULL,0,1,1,1,0,0,115,NULL,140,1,1,0,0,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('wiks','WikiSettings','Modules/Wiki','Modules/Wiki/classes',0,0,'sys',0,0,0,1,1,0,0,NULL,0,0,1,0,1,0,0,0,0);
INSERT INTO `il_object_def` VALUES ('wsrt','WorkspaceRootFolder','Modules/WorkspaceRootFolder','Modules/WorkspaceRootFolder/classes',0,0,'0',0,0,0,0,1,0,0,NULL,0,0,0,1,0,0,0,0,0);

--
-- Table structure for table `il_object_group`
--

CREATE TABLE `il_object_group` (
  `id` char(10) NOT NULL DEFAULT '',
  `name` varchar(200) DEFAULT NULL,
  `default_pres_pos` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_object_group`
--

INSERT INTO `il_object_group` VALUES ('cat','Categories',10);
INSERT INTO `il_object_group` VALUES ('crs','Courses',30);
INSERT INTO `il_object_group` VALUES ('file','Files',150);
INSERT INTO `il_object_group` VALUES ('glo','Glossaries',110);
INSERT INTO `il_object_group` VALUES ('grp','Groups',70);
INSERT INTO `il_object_group` VALUES ('iass','IndividualAssessment',170);
INSERT INTO `il_object_group` VALUES ('lres','LearningResources',100);
INSERT INTO `il_object_group` VALUES ('lso','LearningSequence',300);
INSERT INTO `il_object_group` VALUES ('orgu','Organisational Unit',10);
INSERT INTO `il_object_group` VALUES ('prg','StudyProgramme',30);
INSERT INTO `il_object_group` VALUES ('tst','Tests',170);
INSERT INTO `il_object_group` VALUES ('wiki','Wikis',140);

--
-- Table structure for table `il_object_sub_type`
--

CREATE TABLE `il_object_sub_type` (
  `obj_type` varchar(10) NOT NULL DEFAULT '',
  `sub_type` varchar(10) NOT NULL DEFAULT '',
  `amet` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_type`,`sub_type`)
) ;

--
-- Dumping data for table `il_object_sub_type`
--

INSERT INTO `il_object_sub_type` VALUES ('book','bobj',1);
INSERT INTO `il_object_sub_type` VALUES ('glo','term',1);
INSERT INTO `il_object_sub_type` VALUES ('mep','mob',1);
INSERT INTO `il_object_sub_type` VALUES ('orgu','orgu_type',1);
INSERT INTO `il_object_sub_type` VALUES ('prg','prg_type',1);
INSERT INTO `il_object_sub_type` VALUES ('wiki','wpg',1);

--
-- Table structure for table `il_object_subobj`
--

CREATE TABLE `il_object_subobj` (
  `parent` char(10) NOT NULL DEFAULT '',
  `subobj` char(10) NOT NULL DEFAULT '',
  `mmax` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`parent`,`subobj`),
  KEY `i1_idx` (`subobj`)
) ;

--
-- Dumping data for table `il_object_subobj`
--

INSERT INTO `il_object_subobj` VALUES ('adm','accs',1);
INSERT INTO `il_object_subobj` VALUES ('adm','adn',1);
INSERT INTO `il_object_subobj` VALUES ('adm','adve',1);
INSERT INTO `il_object_subobj` VALUES ('adm','assf',1);
INSERT INTO `il_object_subobj` VALUES ('adm','auth',1);
INSERT INTO `il_object_subobj` VALUES ('adm','awra',1);
INSERT INTO `il_object_subobj` VALUES ('adm','bdga',1);
INSERT INTO `il_object_subobj` VALUES ('adm','bibs',1);
INSERT INTO `il_object_subobj` VALUES ('adm','blga',1);
INSERT INTO `il_object_subobj` VALUES ('adm','cadm',1);
INSERT INTO `il_object_subobj` VALUES ('adm','cals',1);
INSERT INTO `il_object_subobj` VALUES ('adm','cert',1);
INSERT INTO `il_object_subobj` VALUES ('adm','chta',1);
INSERT INTO `il_object_subobj` VALUES ('adm','cmis',1);
INSERT INTO `il_object_subobj` VALUES ('adm','cmps',1);
INSERT INTO `il_object_subobj` VALUES ('adm','coms',1);
INSERT INTO `il_object_subobj` VALUES ('adm','cpad',1);
INSERT INTO `il_object_subobj` VALUES ('adm','crss',1);
INSERT INTO `il_object_subobj` VALUES ('adm','dshs',1);
INSERT INTO `il_object_subobj` VALUES ('adm','ecss',1);
INSERT INTO `il_object_subobj` VALUES ('adm','excs',1);
INSERT INTO `il_object_subobj` VALUES ('adm','extt',1);
INSERT INTO `il_object_subobj` VALUES ('adm','facs',1);
INSERT INTO `il_object_subobj` VALUES ('adm','fils',1);
INSERT INTO `il_object_subobj` VALUES ('adm','frma',1);
INSERT INTO `il_object_subobj` VALUES ('adm','grps',1);
INSERT INTO `il_object_subobj` VALUES ('adm','hlps',1);
INSERT INTO `il_object_subobj` VALUES ('adm','lhts',1);
INSERT INTO `il_object_subobj` VALUES ('adm','lngf',1);
INSERT INTO `il_object_subobj` VALUES ('adm','logs',1);
INSERT INTO `il_object_subobj` VALUES ('adm','lrss',1);
INSERT INTO `il_object_subobj` VALUES ('adm','lsos',1);
INSERT INTO `il_object_subobj` VALUES ('adm','ltis',1);
INSERT INTO `il_object_subobj` VALUES ('adm','mail',1);
INSERT INTO `il_object_subobj` VALUES ('adm','mcts',1);
INSERT INTO `il_object_subobj` VALUES ('adm','mds',1);
INSERT INTO `il_object_subobj` VALUES ('adm','mme',1);
INSERT INTO `il_object_subobj` VALUES ('adm','mobs',1);
INSERT INTO `il_object_subobj` VALUES ('adm','nota',1);
INSERT INTO `il_object_subobj` VALUES ('adm','nots',1);
INSERT INTO `il_object_subobj` VALUES ('adm','nwss',1);
INSERT INTO `il_object_subobj` VALUES ('adm','objf',1);
INSERT INTO `il_object_subobj` VALUES ('adm','orgu',1);
INSERT INTO `il_object_subobj` VALUES ('adm','otpl',1);
INSERT INTO `il_object_subobj` VALUES ('adm','pdfg',1);
INSERT INTO `il_object_subobj` VALUES ('adm','prfa',1);
INSERT INTO `il_object_subobj` VALUES ('adm','prgs',1);
INSERT INTO `il_object_subobj` VALUES ('adm','prss',1);
INSERT INTO `il_object_subobj` VALUES ('adm','ps',1);
INSERT INTO `il_object_subobj` VALUES ('adm','recf',1);
INSERT INTO `il_object_subobj` VALUES ('adm','reps',1);
INSERT INTO `il_object_subobj` VALUES ('adm','rolf',0);
INSERT INTO `il_object_subobj` VALUES ('adm','seas',1);
INSERT INTO `il_object_subobj` VALUES ('adm','skmg',1);
INSERT INTO `il_object_subobj` VALUES ('adm','stys',1);
INSERT INTO `il_object_subobj` VALUES ('adm','svyf',1);
INSERT INTO `il_object_subobj` VALUES ('adm','sysc',1);
INSERT INTO `il_object_subobj` VALUES ('adm','tags',1);
INSERT INTO `il_object_subobj` VALUES ('adm','taxs',1);
INSERT INTO `il_object_subobj` VALUES ('adm','tos',1);
INSERT INTO `il_object_subobj` VALUES ('adm','trac',1);
INSERT INTO `il_object_subobj` VALUES ('adm','usrf',1);
INSERT INTO `il_object_subobj` VALUES ('adm','wbdv',1);
INSERT INTO `il_object_subobj` VALUES ('adm','wbrs',1);
INSERT INTO `il_object_subobj` VALUES ('adm','wfe',1);
INSERT INTO `il_object_subobj` VALUES ('adm','wiks',1);
INSERT INTO `il_object_subobj` VALUES ('cat','bibl',0);
INSERT INTO `il_object_subobj` VALUES ('cat','blog',0);
INSERT INTO `il_object_subobj` VALUES ('cat','book',0);
INSERT INTO `il_object_subobj` VALUES ('cat','cat',0);
INSERT INTO `il_object_subobj` VALUES ('cat','catr',0);
INSERT INTO `il_object_subobj` VALUES ('cat','chtr',0);
INSERT INTO `il_object_subobj` VALUES ('cat','cld',0);
INSERT INTO `il_object_subobj` VALUES ('cat','cmix',0);
INSERT INTO `il_object_subobj` VALUES ('cat','copa',0);
INSERT INTO `il_object_subobj` VALUES ('cat','crs',0);
INSERT INTO `il_object_subobj` VALUES ('cat','crsr',0);
INSERT INTO `il_object_subobj` VALUES ('cat','dcl',0);
INSERT INTO `il_object_subobj` VALUES ('cat','exc',0);
INSERT INTO `il_object_subobj` VALUES ('cat','feed',0);
INSERT INTO `il_object_subobj` VALUES ('cat','file',0);
INSERT INTO `il_object_subobj` VALUES ('cat','frm',0);
INSERT INTO `il_object_subobj` VALUES ('cat','glo',0);
INSERT INTO `il_object_subobj` VALUES ('cat','grp',0);
INSERT INTO `il_object_subobj` VALUES ('cat','grpr',0);
INSERT INTO `il_object_subobj` VALUES ('cat','htlm',0);
INSERT INTO `il_object_subobj` VALUES ('cat','iass',0);
INSERT INTO `il_object_subobj` VALUES ('cat','itgr',0);
INSERT INTO `il_object_subobj` VALUES ('cat','lm',0);
INSERT INTO `il_object_subobj` VALUES ('cat','lso',0);
INSERT INTO `il_object_subobj` VALUES ('cat','lti',0);
INSERT INTO `il_object_subobj` VALUES ('cat','mcst',0);
INSERT INTO `il_object_subobj` VALUES ('cat','mep',0);
INSERT INTO `il_object_subobj` VALUES ('cat','poll',0);
INSERT INTO `il_object_subobj` VALUES ('cat','prg',0);
INSERT INTO `il_object_subobj` VALUES ('cat','prgr',0);
INSERT INTO `il_object_subobj` VALUES ('cat','prtt',0);
INSERT INTO `il_object_subobj` VALUES ('cat','qpl',0);
INSERT INTO `il_object_subobj` VALUES ('cat','rcat',0);
INSERT INTO `il_object_subobj` VALUES ('cat','rcrs',0);
INSERT INTO `il_object_subobj` VALUES ('cat','rfil',0);
INSERT INTO `il_object_subobj` VALUES ('cat','rglo',0);
INSERT INTO `il_object_subobj` VALUES ('cat','rgrp',0);
INSERT INTO `il_object_subobj` VALUES ('cat','rlm',0);
INSERT INTO `il_object_subobj` VALUES ('cat','rtst',0);
INSERT INTO `il_object_subobj` VALUES ('cat','rwik',0);
INSERT INTO `il_object_subobj` VALUES ('cat','sahs',0);
INSERT INTO `il_object_subobj` VALUES ('cat','spl',0);
INSERT INTO `il_object_subobj` VALUES ('cat','svy',0);
INSERT INTO `il_object_subobj` VALUES ('cat','tst',0);
INSERT INTO `il_object_subobj` VALUES ('cat','webr',0);
INSERT INTO `il_object_subobj` VALUES ('cat','wiki',0);
INSERT INTO `il_object_subobj` VALUES ('crs','bibl',0);
INSERT INTO `il_object_subobj` VALUES ('crs','blog',0);
INSERT INTO `il_object_subobj` VALUES ('crs','book',0);
INSERT INTO `il_object_subobj` VALUES ('crs','catr',0);
INSERT INTO `il_object_subobj` VALUES ('crs','chtr',0);
INSERT INTO `il_object_subobj` VALUES ('crs','cld',0);
INSERT INTO `il_object_subobj` VALUES ('crs','cmix',0);
INSERT INTO `il_object_subobj` VALUES ('crs','copa',0);
INSERT INTO `il_object_subobj` VALUES ('crs','crsr',0);
INSERT INTO `il_object_subobj` VALUES ('crs','dcl',0);
INSERT INTO `il_object_subobj` VALUES ('crs','exc',0);
INSERT INTO `il_object_subobj` VALUES ('crs','feed',0);
INSERT INTO `il_object_subobj` VALUES ('crs','file',0);
INSERT INTO `il_object_subobj` VALUES ('crs','fold',0);
INSERT INTO `il_object_subobj` VALUES ('crs','frm',0);
INSERT INTO `il_object_subobj` VALUES ('crs','glo',0);
INSERT INTO `il_object_subobj` VALUES ('crs','grp',0);
INSERT INTO `il_object_subobj` VALUES ('crs','grpr',0);
INSERT INTO `il_object_subobj` VALUES ('crs','htlm',0);
INSERT INTO `il_object_subobj` VALUES ('crs','iass',0);
INSERT INTO `il_object_subobj` VALUES ('crs','itgr',0);
INSERT INTO `il_object_subobj` VALUES ('crs','lm',0);
INSERT INTO `il_object_subobj` VALUES ('crs','lso',0);
INSERT INTO `il_object_subobj` VALUES ('crs','lti',0);
INSERT INTO `il_object_subobj` VALUES ('crs','mcst',0);
INSERT INTO `il_object_subobj` VALUES ('crs','mep',0);
INSERT INTO `il_object_subobj` VALUES ('crs','poll',0);
INSERT INTO `il_object_subobj` VALUES ('crs','prtt',0);
INSERT INTO `il_object_subobj` VALUES ('crs','qpl',0);
INSERT INTO `il_object_subobj` VALUES ('crs','rcat',0);
INSERT INTO `il_object_subobj` VALUES ('crs','rcrs',0);
INSERT INTO `il_object_subobj` VALUES ('crs','rgrp',0);
INSERT INTO `il_object_subobj` VALUES ('crs','sahs',0);
INSERT INTO `il_object_subobj` VALUES ('crs','sess',0);
INSERT INTO `il_object_subobj` VALUES ('crs','spl',0);
INSERT INTO `il_object_subobj` VALUES ('crs','svy',0);
INSERT INTO `il_object_subobj` VALUES ('crs','tst',0);
INSERT INTO `il_object_subobj` VALUES ('crs','webr',0);
INSERT INTO `il_object_subobj` VALUES ('crs','wiki',0);
INSERT INTO `il_object_subobj` VALUES ('fold','bibl',0);
INSERT INTO `il_object_subobj` VALUES ('fold','blog',0);
INSERT INTO `il_object_subobj` VALUES ('fold','book',0);
INSERT INTO `il_object_subobj` VALUES ('fold','chtr',0);
INSERT INTO `il_object_subobj` VALUES ('fold','cld',0);
INSERT INTO `il_object_subobj` VALUES ('fold','cmix',0);
INSERT INTO `il_object_subobj` VALUES ('fold','copa',0);
INSERT INTO `il_object_subobj` VALUES ('fold','dcl',0);
INSERT INTO `il_object_subobj` VALUES ('fold','exc',0);
INSERT INTO `il_object_subobj` VALUES ('fold','file',0);
INSERT INTO `il_object_subobj` VALUES ('fold','fold',0);
INSERT INTO `il_object_subobj` VALUES ('fold','frm',0);
INSERT INTO `il_object_subobj` VALUES ('fold','glo',0);
INSERT INTO `il_object_subobj` VALUES ('fold','grp',0);
INSERT INTO `il_object_subobj` VALUES ('fold','htlm',0);
INSERT INTO `il_object_subobj` VALUES ('fold','iass',0);
INSERT INTO `il_object_subobj` VALUES ('fold','itgr',0);
INSERT INTO `il_object_subobj` VALUES ('fold','lm',0);
INSERT INTO `il_object_subobj` VALUES ('fold','lso',0);
INSERT INTO `il_object_subobj` VALUES ('fold','lti',0);
INSERT INTO `il_object_subobj` VALUES ('fold','mcst',0);
INSERT INTO `il_object_subobj` VALUES ('fold','mep',0);
INSERT INTO `il_object_subobj` VALUES ('fold','poll',0);
INSERT INTO `il_object_subobj` VALUES ('fold','prtt',0);
INSERT INTO `il_object_subobj` VALUES ('fold','qpl',0);
INSERT INTO `il_object_subobj` VALUES ('fold','sahs',0);
INSERT INTO `il_object_subobj` VALUES ('fold','sess',0);
INSERT INTO `il_object_subobj` VALUES ('fold','spl',0);
INSERT INTO `il_object_subobj` VALUES ('fold','svy',0);
INSERT INTO `il_object_subobj` VALUES ('fold','tst',0);
INSERT INTO `il_object_subobj` VALUES ('fold','webr',0);
INSERT INTO `il_object_subobj` VALUES ('fold','wiki',0);
INSERT INTO `il_object_subobj` VALUES ('grp','bibl',0);
INSERT INTO `il_object_subobj` VALUES ('grp','blog',0);
INSERT INTO `il_object_subobj` VALUES ('grp','book',0);
INSERT INTO `il_object_subobj` VALUES ('grp','catr',0);
INSERT INTO `il_object_subobj` VALUES ('grp','chtr',0);
INSERT INTO `il_object_subobj` VALUES ('grp','cld',0);
INSERT INTO `il_object_subobj` VALUES ('grp','cmix',0);
INSERT INTO `il_object_subobj` VALUES ('grp','copa',0);
INSERT INTO `il_object_subobj` VALUES ('grp','crsr',0);
INSERT INTO `il_object_subobj` VALUES ('grp','dcl',0);
INSERT INTO `il_object_subobj` VALUES ('grp','exc',0);
INSERT INTO `il_object_subobj` VALUES ('grp','feed',0);
INSERT INTO `il_object_subobj` VALUES ('grp','file',0);
INSERT INTO `il_object_subobj` VALUES ('grp','fold',0);
INSERT INTO `il_object_subobj` VALUES ('grp','frm',0);
INSERT INTO `il_object_subobj` VALUES ('grp','glo',0);
INSERT INTO `il_object_subobj` VALUES ('grp','grp',0);
INSERT INTO `il_object_subobj` VALUES ('grp','grpr',0);
INSERT INTO `il_object_subobj` VALUES ('grp','htlm',0);
INSERT INTO `il_object_subobj` VALUES ('grp','iass',0);
INSERT INTO `il_object_subobj` VALUES ('grp','itgr',0);
INSERT INTO `il_object_subobj` VALUES ('grp','lm',0);
INSERT INTO `il_object_subobj` VALUES ('grp','lso',0);
INSERT INTO `il_object_subobj` VALUES ('grp','lti',0);
INSERT INTO `il_object_subobj` VALUES ('grp','mcst',0);
INSERT INTO `il_object_subobj` VALUES ('grp','mep',0);
INSERT INTO `il_object_subobj` VALUES ('grp','poll',0);
INSERT INTO `il_object_subobj` VALUES ('grp','prtt',0);
INSERT INTO `il_object_subobj` VALUES ('grp','qpl',0);
INSERT INTO `il_object_subobj` VALUES ('grp','rcat',0);
INSERT INTO `il_object_subobj` VALUES ('grp','rcrs',0);
INSERT INTO `il_object_subobj` VALUES ('grp','rgrp',0);
INSERT INTO `il_object_subobj` VALUES ('grp','sahs',0);
INSERT INTO `il_object_subobj` VALUES ('grp','sess',0);
INSERT INTO `il_object_subobj` VALUES ('grp','spl',0);
INSERT INTO `il_object_subobj` VALUES ('grp','svy',0);
INSERT INTO `il_object_subobj` VALUES ('grp','tst',0);
INSERT INTO `il_object_subobj` VALUES ('grp','webr',0);
INSERT INTO `il_object_subobj` VALUES ('grp','wiki',0);
INSERT INTO `il_object_subobj` VALUES ('lngf','lng',0);
INSERT INTO `il_object_subobj` VALUES ('lso','copa',0);
INSERT INTO `il_object_subobj` VALUES ('lso','exc',0);
INSERT INTO `il_object_subobj` VALUES ('lso','file',0);
INSERT INTO `il_object_subobj` VALUES ('lso','htlm',0);
INSERT INTO `il_object_subobj` VALUES ('lso','iass',0);
INSERT INTO `il_object_subobj` VALUES ('lso','lm',0);
INSERT INTO `il_object_subobj` VALUES ('lso','rolf',1);
INSERT INTO `il_object_subobj` VALUES ('lso','sahs',0);
INSERT INTO `il_object_subobj` VALUES ('lso','svy',0);
INSERT INTO `il_object_subobj` VALUES ('lso','tst',0);
INSERT INTO `il_object_subobj` VALUES ('orgu','orgu',0);
INSERT INTO `il_object_subobj` VALUES ('prg','crsr',1);
INSERT INTO `il_object_subobj` VALUES ('prg','prg',0);
INSERT INTO `il_object_subobj` VALUES ('prg','prgr',0);
INSERT INTO `il_object_subobj` VALUES ('prg','rolf',1);
INSERT INTO `il_object_subobj` VALUES ('recf','bibl',0);
INSERT INTO `il_object_subobj` VALUES ('recf','blog',0);
INSERT INTO `il_object_subobj` VALUES ('recf','cat',0);
INSERT INTO `il_object_subobj` VALUES ('recf','catr',0);
INSERT INTO `il_object_subobj` VALUES ('recf','chtr',0);
INSERT INTO `il_object_subobj` VALUES ('recf','cld',0);
INSERT INTO `il_object_subobj` VALUES ('recf','cmix',0);
INSERT INTO `il_object_subobj` VALUES ('recf','copa',0);
INSERT INTO `il_object_subobj` VALUES ('recf','crs',0);
INSERT INTO `il_object_subobj` VALUES ('recf','crsr',0);
INSERT INTO `il_object_subobj` VALUES ('recf','dcl',0);
INSERT INTO `il_object_subobj` VALUES ('recf','exc',0);
INSERT INTO `il_object_subobj` VALUES ('recf','file',0);
INSERT INTO `il_object_subobj` VALUES ('recf','fold',0);
INSERT INTO `il_object_subobj` VALUES ('recf','frm',0);
INSERT INTO `il_object_subobj` VALUES ('recf','glo',0);
INSERT INTO `il_object_subobj` VALUES ('recf','grp',0);
INSERT INTO `il_object_subobj` VALUES ('recf','grpr',0);
INSERT INTO `il_object_subobj` VALUES ('recf','htlm',0);
INSERT INTO `il_object_subobj` VALUES ('recf','itgr',0);
INSERT INTO `il_object_subobj` VALUES ('recf','lm',0);
INSERT INTO `il_object_subobj` VALUES ('recf','lti',0);
INSERT INTO `il_object_subobj` VALUES ('recf','mcst',0);
INSERT INTO `il_object_subobj` VALUES ('recf','mep',0);
INSERT INTO `il_object_subobj` VALUES ('recf','poll',0);
INSERT INTO `il_object_subobj` VALUES ('recf','prg',0);
INSERT INTO `il_object_subobj` VALUES ('recf','prgr',0);
INSERT INTO `il_object_subobj` VALUES ('recf','prtt',0);
INSERT INTO `il_object_subobj` VALUES ('recf','qpl',0);
INSERT INTO `il_object_subobj` VALUES ('recf','sahs',0);
INSERT INTO `il_object_subobj` VALUES ('recf','sess',0);
INSERT INTO `il_object_subobj` VALUES ('recf','spl',0);
INSERT INTO `il_object_subobj` VALUES ('recf','svy',0);
INSERT INTO `il_object_subobj` VALUES ('recf','tst',0);
INSERT INTO `il_object_subobj` VALUES ('recf','webr',0);
INSERT INTO `il_object_subobj` VALUES ('recf','wiki',0);
INSERT INTO `il_object_subobj` VALUES ('rolf','role',0);
INSERT INTO `il_object_subobj` VALUES ('rolf','rolt',0);
INSERT INTO `il_object_subobj` VALUES ('root','adm',1);
INSERT INTO `il_object_subobj` VALUES ('root','bibl',0);
INSERT INTO `il_object_subobj` VALUES ('root','blog',0);
INSERT INTO `il_object_subobj` VALUES ('root','book',0);
INSERT INTO `il_object_subobj` VALUES ('root','cat',0);
INSERT INTO `il_object_subobj` VALUES ('root','catr',0);
INSERT INTO `il_object_subobj` VALUES ('root','chtr',0);
INSERT INTO `il_object_subobj` VALUES ('root','cld',0);
INSERT INTO `il_object_subobj` VALUES ('root','cmix',0);
INSERT INTO `il_object_subobj` VALUES ('root','copa',0);
INSERT INTO `il_object_subobj` VALUES ('root','crs',0);
INSERT INTO `il_object_subobj` VALUES ('root','crsr',0);
INSERT INTO `il_object_subobj` VALUES ('root','dcl',0);
INSERT INTO `il_object_subobj` VALUES ('root','exc',0);
INSERT INTO `il_object_subobj` VALUES ('root','feed',0);
INSERT INTO `il_object_subobj` VALUES ('root','file',0);
INSERT INTO `il_object_subobj` VALUES ('root','frm',0);
INSERT INTO `il_object_subobj` VALUES ('root','glo',0);
INSERT INTO `il_object_subobj` VALUES ('root','grp',0);
INSERT INTO `il_object_subobj` VALUES ('root','grpr',0);
INSERT INTO `il_object_subobj` VALUES ('root','htlm',0);
INSERT INTO `il_object_subobj` VALUES ('root','iass',0);
INSERT INTO `il_object_subobj` VALUES ('root','itgr',0);
INSERT INTO `il_object_subobj` VALUES ('root','lm',0);
INSERT INTO `il_object_subobj` VALUES ('root','lso',0);
INSERT INTO `il_object_subobj` VALUES ('root','lti',0);
INSERT INTO `il_object_subobj` VALUES ('root','mcst',0);
INSERT INTO `il_object_subobj` VALUES ('root','mep',0);
INSERT INTO `il_object_subobj` VALUES ('root','poll',0);
INSERT INTO `il_object_subobj` VALUES ('root','prg',0);
INSERT INTO `il_object_subobj` VALUES ('root','prgr',0);
INSERT INTO `il_object_subobj` VALUES ('root','prtt',0);
INSERT INTO `il_object_subobj` VALUES ('root','qpl',0);
INSERT INTO `il_object_subobj` VALUES ('root','rcat',0);
INSERT INTO `il_object_subobj` VALUES ('root','rcrs',0);
INSERT INTO `il_object_subobj` VALUES ('root','rfil',0);
INSERT INTO `il_object_subobj` VALUES ('root','rglo',0);
INSERT INTO `il_object_subobj` VALUES ('root','rgrp',0);
INSERT INTO `il_object_subobj` VALUES ('root','rlm',0);
INSERT INTO `il_object_subobj` VALUES ('root','rtst',0);
INSERT INTO `il_object_subobj` VALUES ('root','rwik',0);
INSERT INTO `il_object_subobj` VALUES ('root','sahs',0);
INSERT INTO `il_object_subobj` VALUES ('root','spl',0);
INSERT INTO `il_object_subobj` VALUES ('root','svy',0);
INSERT INTO `il_object_subobj` VALUES ('root','tst',0);
INSERT INTO `il_object_subobj` VALUES ('root','webr',0);
INSERT INTO `il_object_subobj` VALUES ('root','wiki',0);
INSERT INTO `il_object_subobj` VALUES ('stys','sty',0);
INSERT INTO `il_object_subobj` VALUES ('usrf','usr',0);
INSERT INTO `il_object_subobj` VALUES ('wfld','blog',0);
INSERT INTO `il_object_subobj` VALUES ('wfld','cmxv',0);
INSERT INTO `il_object_subobj` VALUES ('wfld','crsv',0);
INSERT INTO `il_object_subobj` VALUES ('wfld','excv',0);
INSERT INTO `il_object_subobj` VALUES ('wfld','file',0);
INSERT INTO `il_object_subobj` VALUES ('wfld','ltiv',0);
INSERT INTO `il_object_subobj` VALUES ('wfld','scov',0);
INSERT INTO `il_object_subobj` VALUES ('wfld','tstv',0);
INSERT INTO `il_object_subobj` VALUES ('wfld','webr',0);
INSERT INTO `il_object_subobj` VALUES ('wfld','wfld',0);
INSERT INTO `il_object_subobj` VALUES ('wsrt','blog',0);
INSERT INTO `il_object_subobj` VALUES ('wsrt','cmxv',0);
INSERT INTO `il_object_subobj` VALUES ('wsrt','crsv',0);
INSERT INTO `il_object_subobj` VALUES ('wsrt','excv',0);
INSERT INTO `il_object_subobj` VALUES ('wsrt','file',0);
INSERT INTO `il_object_subobj` VALUES ('wsrt','ltiv',0);
INSERT INTO `il_object_subobj` VALUES ('wsrt','scov',0);
INSERT INTO `il_object_subobj` VALUES ('wsrt','tstv',0);
INSERT INTO `il_object_subobj` VALUES ('wsrt','webr',0);
INSERT INTO `il_object_subobj` VALUES ('wsrt','wfld',0);

--
-- Table structure for table `il_orgu_authority`
--

CREATE TABLE `il_orgu_authority` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `over` tinyint(4) DEFAULT NULL,
  `scope` tinyint(4) DEFAULT NULL,
  `position_id` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_orgu_authority`
--

INSERT INTO `il_orgu_authority` VALUES (1,1,1,2);

--
-- Table structure for table `il_orgu_authority_seq`
--

CREATE TABLE `il_orgu_authority_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=2;

--
-- Dumping data for table `il_orgu_authority_seq`
--

INSERT INTO `il_orgu_authority_seq` VALUES (1);

--
-- Table structure for table `il_orgu_op_contexts`
--

CREATE TABLE `il_orgu_op_contexts` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `context` varchar(16) DEFAULT NULL,
  `parent_context_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_orgu_op_contexts`
--

INSERT INTO `il_orgu_op_contexts` VALUES (1,'object',0);
INSERT INTO `il_orgu_op_contexts` VALUES (2,'iass',1);
INSERT INTO `il_orgu_op_contexts` VALUES (3,'crs',1);
INSERT INTO `il_orgu_op_contexts` VALUES (4,'grp',1);
INSERT INTO `il_orgu_op_contexts` VALUES (5,'tst',1);
INSERT INTO `il_orgu_op_contexts` VALUES (6,'exc',1);
INSERT INTO `il_orgu_op_contexts` VALUES (7,'svy',1);
INSERT INTO `il_orgu_op_contexts` VALUES (8,'prg',1);
INSERT INTO `il_orgu_op_contexts` VALUES (9,'usrf',1);

--
-- Table structure for table `il_orgu_op_contexts_seq`
--

CREATE TABLE `il_orgu_op_contexts_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=10;

--
-- Dumping data for table `il_orgu_op_contexts_seq`
--

INSERT INTO `il_orgu_op_contexts_seq` VALUES (9);

--
-- Table structure for table `il_orgu_operations`
--

CREATE TABLE `il_orgu_operations` (
  `operation_id` bigint(20) NOT NULL DEFAULT 0,
  `operation_string` varchar(127) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  `list_order` bigint(20) DEFAULT NULL,
  `context_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`operation_id`),
  KEY `i1_idx` (`operation_string`),
  KEY `i3_idx` (`list_order`),
  KEY `i4_idx` (`context_id`)
) ;

--
-- Dumping data for table `il_orgu_operations`
--

INSERT INTO `il_orgu_operations` VALUES (1,'read_learning_progress','Read the learning Progress of a User',0,3);
INSERT INTO `il_orgu_operations` VALUES (2,'read_learning_progress','Read the learning Progress of a User',0,4);
INSERT INTO `il_orgu_operations` VALUES (3,'read_learning_progress','Read the learning Progress of a User',0,2);
INSERT INTO `il_orgu_operations` VALUES (4,'read_learning_progress','Read the learning Progress of a User',0,6);
INSERT INTO `il_orgu_operations` VALUES (5,'read_learning_progress','Read the learning Progress of a User',0,7);
INSERT INTO `il_orgu_operations` VALUES (6,'manage_members','Edit Members in a course',0,3);
INSERT INTO `il_orgu_operations` VALUES (7,'manage_members','Edit Members in a group',0,4);
INSERT INTO `il_orgu_operations` VALUES (8,'edit_submissions_grades','',0,6);
INSERT INTO `il_orgu_operations` VALUES (9,'access_results','',0,7);
INSERT INTO `il_orgu_operations` VALUES (10,'write_learning_progress','Write the learning Progress of a User',0,2);
INSERT INTO `il_orgu_operations` VALUES (11,'access_enrolments','Access Enrolments in a course',0,3);
INSERT INTO `il_orgu_operations` VALUES (12,'read_learning_progress','Read Test Participants Learning Progress',0,5);
INSERT INTO `il_orgu_operations` VALUES (13,'access_results','Access Test Participants Results',0,5);
INSERT INTO `il_orgu_operations` VALUES (14,'manage_participants','Manage Test Participants',0,5);
INSERT INTO `il_orgu_operations` VALUES (15,'score_participants','Score Test Participants',0,5);
INSERT INTO `il_orgu_operations` VALUES (16,'view_members','View Memberships of other users',0,8);
INSERT INTO `il_orgu_operations` VALUES (17,'read_learning_progress','View learning progress of other users',0,8);
INSERT INTO `il_orgu_operations` VALUES (18,'view_individual_plan','View Individual Plans of other users',0,8);
INSERT INTO `il_orgu_operations` VALUES (19,'edit_individual_plan','Edit Individual Plans of other users',0,8);
INSERT INTO `il_orgu_operations` VALUES (20,'manage_members','Manage Memberships of other users',0,8);
INSERT INTO `il_orgu_operations` VALUES (21,'edit_user_accounts','Edit User in User Administration',0,9);
INSERT INTO `il_orgu_operations` VALUES (22,'view_certificates','Read the certificates of a User',0,5);
INSERT INTO `il_orgu_operations` VALUES (23,'view_certificates','Read the certificates of a User',0,6);
INSERT INTO `il_orgu_operations` VALUES (24,'view_certificates','Read the certificates of a User',0,3);
INSERT INTO `il_orgu_operations` VALUES (25,'view_competences','Read the competences of a User',0,5);
INSERT INTO `il_orgu_operations` VALUES (26,'view_competences','Read the competences of a User',0,4);
INSERT INTO `il_orgu_operations` VALUES (27,'view_competences','Read the competences of a User',0,3);
INSERT INTO `il_orgu_operations` VALUES (28,'view_competences','Read the competences of a User',0,7);

--
-- Table structure for table `il_orgu_operations_seq`
--

CREATE TABLE `il_orgu_operations_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=29;

--
-- Dumping data for table `il_orgu_operations_seq`
--

INSERT INTO `il_orgu_operations_seq` VALUES (28);

--
-- Table structure for table `il_orgu_permissions`
--

CREATE TABLE `il_orgu_permissions` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `context_id` bigint(20) DEFAULT NULL,
  `operations` varchar(256) DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `position_id` bigint(20) DEFAULT NULL,
  `protected` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `co_idx` (`context_id`),
  KEY `po_idx` (`position_id`)
) ;

--
-- Dumping data for table `il_orgu_permissions`
--


--
-- Table structure for table `il_orgu_permissions_seq`
--

CREATE TABLE `il_orgu_permissions_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_orgu_permissions_seq`
--


--
-- Table structure for table `il_orgu_positions`
--

CREATE TABLE `il_orgu_positions` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `title` varchar(512) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `core_position` tinyint(4) DEFAULT NULL,
  `core_identifier` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_orgu_positions`
--

INSERT INTO `il_orgu_positions` VALUES (1,'Employees','Employees of a OrgUnit',1,1);
INSERT INTO `il_orgu_positions` VALUES (2,'Superiors','Superiors of a OrgUnit',1,2);

--
-- Table structure for table `il_orgu_positions_seq`
--

CREATE TABLE `il_orgu_positions_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=3;

--
-- Dumping data for table `il_orgu_positions_seq`
--

INSERT INTO `il_orgu_positions_seq` VALUES (2);

--
-- Table structure for table `il_orgu_ua`
--

CREATE TABLE `il_orgu_ua` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `user_id` bigint(20) DEFAULT NULL,
  `position_id` bigint(20) DEFAULT NULL,
  `orgu_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pi_idx` (`position_id`),
  KEY `ui_idx` (`user_id`),
  KEY `oi_idx` (`orgu_id`),
  KEY `po_idx` (`position_id`,`orgu_id`),
  KEY `pu_idx` (`position_id`,`user_id`)
) ;

--
-- Dumping data for table `il_orgu_ua`
--


--
-- Table structure for table `il_orgu_ua_seq`
--

CREATE TABLE `il_orgu_ua_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_orgu_ua_seq`
--


--
-- Table structure for table `il_plugin`
--

CREATE TABLE `il_plugin` (
  `component_type` char(10) NOT NULL DEFAULT '',
  `component_name` varchar(90) NOT NULL DEFAULT ' ',
  `slot_id` char(10) NOT NULL DEFAULT '',
  `name` varchar(40) NOT NULL DEFAULT ' ',
  `last_update_version` char(10) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  `db_version` int(11) NOT NULL DEFAULT 0,
  `plugin_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`component_type`,`component_name`,`slot_id`,`name`)
) ;

--
-- Dumping data for table `il_plugin`
--


--
-- Table structure for table `il_pluginslot`
--

CREATE TABLE `il_pluginslot` (
  `component` varchar(200) NOT NULL DEFAULT ' ',
  `id` char(10) NOT NULL DEFAULT '',
  `name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`component`,`id`)
) ;

--
-- Dumping data for table `il_pluginslot`
--

INSERT INTO `il_pluginslot` VALUES ('Modules/Cloud','cldh','CloudHook');
INSERT INTO `il_pluginslot` VALUES ('Modules/DataCollection','dclfth','FieldTypeHook');
INSERT INTO `il_pluginslot` VALUES ('Modules/OrgUnit','orguext','OrgUnitExtension');
INSERT INTO `il_pluginslot` VALUES ('Modules/OrgUnit','orgutypehk','OrgUnitTypeHook');
INSERT INTO `il_pluginslot` VALUES ('Modules/SurveyQuestionPool','svyq','SurveyQuestions');
INSERT INTO `il_pluginslot` VALUES ('Modules/Test','texp','Export');
INSERT INTO `il_pluginslot` VALUES ('Modules/Test','tsig','Signature');
INSERT INTO `il_pluginslot` VALUES ('Modules/TestQuestionPool','qst','Questions');
INSERT INTO `il_pluginslot` VALUES ('Services/AdvancedMetaData','amdc','AdvancedMDClaiming');
INSERT INTO `il_pluginslot` VALUES ('Services/Authentication','authhk','AuthenticationHook');
INSERT INTO `il_pluginslot` VALUES ('Services/AuthShibboleth','shibhk','ShibbolethAuthenticationHook');
INSERT INTO `il_pluginslot` VALUES ('Services/Calendar','capg','AppointmentCustomGrid');
INSERT INTO `il_pluginslot` VALUES ('Services/Calendar','capm','AppointmentCustomModal');
INSERT INTO `il_pluginslot` VALUES ('Services/COPage','pgcp','PageComponent');
INSERT INTO `il_pluginslot` VALUES ('Services/Cron','crnhk','CronHook');
INSERT INTO `il_pluginslot` VALUES ('Services/EventHandling','evhk','EventHook');
INSERT INTO `il_pluginslot` VALUES ('Services/LDAP','ldaphk','LDAPHook');
INSERT INTO `il_pluginslot` VALUES ('Services/PDFGeneration','renderer','Renderer');
INSERT INTO `il_pluginslot` VALUES ('Services/Preview','pvre','PreviewRenderer');
INSERT INTO `il_pluginslot` VALUES ('Services/Repository','robj','RepositoryObject');
INSERT INTO `il_pluginslot` VALUES ('Services/UIComponent','uihk','UserInterfaceHook');
INSERT INTO `il_pluginslot` VALUES ('Services/User','udfc','UDFClaiming');
INSERT INTO `il_pluginslot` VALUES ('Services/User','udfd','UDFDefinition');
INSERT INTO `il_pluginslot` VALUES ('Services/WebServices','soaphk','SoapHook');
INSERT INTO `il_pluginslot` VALUES ('Services/WorkflowEngine','wfeCG','ComplexGateway');

--
-- Table structure for table `il_poll`
--

CREATE TABLE `il_poll` (
  `id` int(11) NOT NULL DEFAULT 0,
  `question` varchar(1000) DEFAULT NULL,
  `image` varchar(1000) DEFAULT NULL,
  `online_status` tinyint(4) NOT NULL DEFAULT 0,
  `view_results` tinyint(4) NOT NULL DEFAULT 3,
  `period` tinyint(4) NOT NULL DEFAULT 0,
  `period_begin` int(11) DEFAULT 0,
  `period_end` int(11) DEFAULT 0,
  `max_answers` tinyint(4) NOT NULL DEFAULT 1,
  `result_sort` tinyint(4) NOT NULL DEFAULT 0,
  `non_anon` tinyint(4) NOT NULL DEFAULT 0,
  `show_results_as` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_poll`
--


--
-- Table structure for table `il_poll_answer`
--

CREATE TABLE `il_poll_answer` (
  `id` int(11) NOT NULL DEFAULT 0,
  `poll_id` int(11) NOT NULL DEFAULT 0,
  `answer` varchar(1000) DEFAULT NULL,
  `pos` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_poll_answer`
--


--
-- Table structure for table `il_poll_answer_seq`
--

CREATE TABLE `il_poll_answer_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_poll_answer_seq`
--


--
-- Table structure for table `il_poll_vote`
--

CREATE TABLE `il_poll_vote` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `poll_id` int(11) NOT NULL DEFAULT 0,
  `answer_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`poll_id`,`answer_id`)
) ;

--
-- Dumping data for table `il_poll_vote`
--


--
-- Table structure for table `il_qpl_qst_fq_res`
--

CREATE TABLE `il_qpl_qst_fq_res` (
  `result_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `result` varchar(255) DEFAULT NULL,
  `range_min` double NOT NULL DEFAULT 0,
  `range_max` double NOT NULL DEFAULT 0,
  `tolerance` double NOT NULL DEFAULT 0,
  `unit_fi` int(11) NOT NULL DEFAULT 0,
  `formula` longtext DEFAULT NULL,
  `rating_simple` int(11) NOT NULL DEFAULT 1,
  `rating_sign` double NOT NULL DEFAULT 0.25,
  `rating_value` double NOT NULL DEFAULT 0.25,
  `rating_unit` double NOT NULL DEFAULT 0.25,
  `points` double NOT NULL DEFAULT 0,
  `resprecision` int(11) NOT NULL DEFAULT 0,
  `result_type` int(11) NOT NULL DEFAULT 0,
  `range_min_txt` varchar(4000) DEFAULT NULL,
  `range_max_txt` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`result_id`)
) ;

--
-- Dumping data for table `il_qpl_qst_fq_res`
--


--
-- Table structure for table `il_qpl_qst_fq_res_seq`
--

CREATE TABLE `il_qpl_qst_fq_res_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_qpl_qst_fq_res_seq`
--


--
-- Table structure for table `il_qpl_qst_fq_res_unit`
--

CREATE TABLE `il_qpl_qst_fq_res_unit` (
  `result_unit_id` int(11) NOT NULL DEFAULT 0,
  `result` varchar(255) DEFAULT NULL,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `unit_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`result_unit_id`),
  KEY `i1_idx` (`question_fi`,`unit_fi`)
) ;

--
-- Dumping data for table `il_qpl_qst_fq_res_unit`
--


--
-- Table structure for table `il_qpl_qst_fq_res_unit_seq`
--

CREATE TABLE `il_qpl_qst_fq_res_unit_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_qpl_qst_fq_res_unit_seq`
--


--
-- Table structure for table `il_qpl_qst_fq_ucat`
--

CREATE TABLE `il_qpl_qst_fq_ucat` (
  `category_id` int(11) NOT NULL DEFAULT 0,
  `category` varchar(255) DEFAULT NULL,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`category_id`)
) ;

--
-- Dumping data for table `il_qpl_qst_fq_ucat`
--


--
-- Table structure for table `il_qpl_qst_fq_ucat_seq`
--

CREATE TABLE `il_qpl_qst_fq_ucat_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_qpl_qst_fq_ucat_seq`
--


--
-- Table structure for table `il_qpl_qst_fq_unit`
--

CREATE TABLE `il_qpl_qst_fq_unit` (
  `unit_id` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(255) DEFAULT NULL,
  `factor` double NOT NULL DEFAULT 0,
  `baseunit_fi` int(11) NOT NULL DEFAULT 0,
  `category_fi` int(11) NOT NULL DEFAULT 0,
  `sequence` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`unit_id`),
  KEY `i2_idx` (`question_fi`)
) ;

--
-- Dumping data for table `il_qpl_qst_fq_unit`
--


--
-- Table structure for table `il_qpl_qst_fq_unit_seq`
--

CREATE TABLE `il_qpl_qst_fq_unit_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_qpl_qst_fq_unit_seq`
--


--
-- Table structure for table `il_qpl_qst_fq_var`
--

CREATE TABLE `il_qpl_qst_fq_var` (
  `variable_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `variable` varchar(255) DEFAULT NULL,
  `range_min` double NOT NULL DEFAULT 0,
  `range_max` double NOT NULL DEFAULT 0,
  `unit_fi` int(11) NOT NULL DEFAULT 0,
  `step_dim_min` int(11) NOT NULL DEFAULT 0,
  `step_dim_max` int(11) NOT NULL DEFAULT 0,
  `varprecision` int(11) NOT NULL DEFAULT 0,
  `intprecision` int(11) NOT NULL DEFAULT 1,
  `range_min_txt` varchar(4000) DEFAULT NULL,
  `range_max_txt` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`variable_id`)
) ;

--
-- Dumping data for table `il_qpl_qst_fq_var`
--


--
-- Table structure for table `il_qpl_qst_fq_var_seq`
--

CREATE TABLE `il_qpl_qst_fq_var_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_qpl_qst_fq_var_seq`
--


--
-- Table structure for table `il_rating`
--

CREATE TABLE `il_rating` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` char(10) NOT NULL DEFAULT '',
  `sub_obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_obj_type` char(10) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `rating` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`obj_type`,`sub_obj_id`,`sub_obj_type`,`user_id`,`category_id`),
  KEY `obj_idx` (`obj_id`,`obj_type`,`sub_obj_id`,`sub_obj_type`)
) ;

--
-- Dumping data for table `il_rating`
--


--
-- Table structure for table `il_rating_cat`
--

CREATE TABLE `il_rating_cat` (
  `id` int(11) NOT NULL DEFAULT 0,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `pos` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_rating_cat`
--


--
-- Table structure for table `il_rating_cat_seq`
--

CREATE TABLE `il_rating_cat_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_rating_cat_seq`
--


--
-- Table structure for table `il_request_token`
--

CREATE TABLE `il_request_token` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `token` char(64) NOT NULL DEFAULT '',
  `stamp` datetime DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`token`),
  KEY `i1_idx` (`user_id`,`session_id`),
  KEY `i2_idx` (`user_id`,`stamp`)
) ;

--
-- Dumping data for table `il_request_token`
--

INSERT INTO `il_request_token` VALUES (6,'a0d2d5b51feec3dcf1842e6fd80092bc','2016-12-21 13:55:17','ssev5rsqvp335hermt971ieuj6');
INSERT INTO `il_request_token` VALUES (6,'e94abe3044958d2cf4bebff6e68f6a52','2016-12-16 14:39:53','2tfi6g36pme1ivd1tu7nencp41');

--
-- Table structure for table `il_resource`
--

CREATE TABLE `il_resource` (
  `rid` varchar(64) NOT NULL DEFAULT '',
  `storage_id` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`rid`),
  KEY `i1_idx` (`storage_id`)
) ;

--
-- Dumping data for table `il_resource`
--


--
-- Table structure for table `il_resource_info`
--

CREATE TABLE `il_resource_info` (
  `rid` varchar(64) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `suffix` varchar(64) DEFAULT NULL,
  `mime_type` varchar(250) DEFAULT NULL,
  `size` bigint(20) NOT NULL DEFAULT 0,
  `creation_date` bigint(20) NOT NULL DEFAULT 0,
  `version_number` bigint(20) NOT NULL,
  PRIMARY KEY (`rid`,`version_number`),
  KEY `i1_idx` (`rid`)
) ;

--
-- Dumping data for table `il_resource_info`
--


--
-- Table structure for table `il_resource_revision`
--

CREATE TABLE `il_resource_revision` (
  `rid` varchar(64) NOT NULL DEFAULT '',
  `available` tinyint(4) DEFAULT 1,
  `version_number` bigint(20) NOT NULL,
  `owner_id` bigint(20) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`rid`,`version_number`),
  KEY `i1_idx` (`rid`)
) ;

--
-- Dumping data for table `il_resource_revision`
--


--
-- Table structure for table `il_resource_stkh`
--

CREATE TABLE `il_resource_stkh` (
  `id` varchar(64) NOT NULL DEFAULT '',
  `class_name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_resource_stkh`
--


--
-- Table structure for table `il_resource_stkh_u`
--

CREATE TABLE `il_resource_stkh_u` (
  `rid` varchar(64) NOT NULL DEFAULT '',
  `stakeholder_id` varchar(64) DEFAULT NULL,
  KEY `i1_idx` (`rid`),
  KEY `i2_idx` (`stakeholder_id`)
) ;

--
-- Dumping data for table `il_resource_stkh_u`
--


--
-- Table structure for table `il_subscribers`
--

CREATE TABLE `il_subscribers` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `subject` varchar(4000) DEFAULT NULL,
  `sub_time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`,`obj_id`)
) ;

--
-- Dumping data for table `il_subscribers`
--


--
-- Table structure for table `il_tag`
--

CREATE TABLE `il_tag` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` char(10) NOT NULL DEFAULT '',
  `sub_obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_obj_type` char(10) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `tag` varchar(100) NOT NULL DEFAULT ' ',
  `is_offline` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`obj_type`,`sub_obj_id`,`sub_obj_type`,`user_id`,`tag`),
  KEY `i1_idx` (`obj_id`,`obj_type`,`sub_obj_id`,`sub_obj_type`),
  KEY `i2_idx` (`tag`),
  KEY `i3_idx` (`user_id`)
) ;

--
-- Dumping data for table `il_tag`
--


--
-- Table structure for table `il_translations`
--

CREATE TABLE `il_translations` (
  `id` int(11) NOT NULL DEFAULT 0,
  `id_type` varchar(50) NOT NULL DEFAULT '',
  `lang_code` varchar(2) NOT NULL DEFAULT '',
  `title` varchar(256) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  `lang_default` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`,`id_type`,`lang_code`)
) ;

--
-- Dumping data for table `il_translations`
--


--
-- Table structure for table `il_verification`
--

CREATE TABLE `il_verification` (
  `id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(100) NOT NULL DEFAULT '',
  `parameters` varchar(1000) DEFAULT NULL,
  `raw_data` longtext DEFAULT NULL,
  PRIMARY KEY (`id`,`type`)
) ;

--
-- Dumping data for table `il_verification`
--


--
-- Table structure for table `il_wac_secure_path`
--

CREATE TABLE `il_wac_secure_path` (
  `path` varchar(64) NOT NULL DEFAULT ' ',
  `component_directory` varchar(256) DEFAULT NULL,
  `checking_class` varchar(256) DEFAULT NULL,
  `in_sec_folder` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`path`)
) ;

--
-- Dumping data for table `il_wac_secure_path`
--


--
-- Table structure for table `il_wiki_contributor`
--

CREATE TABLE `il_wiki_contributor` (
  `wiki_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status` int(11) DEFAULT NULL,
  `status_time` datetime DEFAULT NULL,
  PRIMARY KEY (`wiki_id`,`user_id`)
) ;

--
-- Dumping data for table `il_wiki_contributor`
--


--
-- Table structure for table `il_wiki_data`
--

CREATE TABLE `il_wiki_data` (
  `id` int(11) NOT NULL DEFAULT 0,
  `startpage` varchar(200) DEFAULT NULL,
  `short` varchar(20) DEFAULT NULL,
  `is_online` tinyint(4) DEFAULT 0,
  `rating` tinyint(4) DEFAULT 0,
  `introduction` longtext DEFAULT NULL,
  `public_notes` tinyint(4) DEFAULT 1,
  `imp_pages` tinyint(4) DEFAULT NULL,
  `page_toc` tinyint(4) DEFAULT NULL,
  `rating_side` tinyint(4) NOT NULL DEFAULT 0,
  `rating_new` tinyint(4) NOT NULL DEFAULT 0,
  `rating_ext` tinyint(4) NOT NULL DEFAULT 0,
  `rating_overall` tinyint(4) DEFAULT 0,
  `empty_page_templ` tinyint(4) NOT NULL DEFAULT 1,
  `link_md_values` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_wiki_data`
--


--
-- Table structure for table `il_wiki_imp_pages`
--

CREATE TABLE `il_wiki_imp_pages` (
  `wiki_id` int(11) NOT NULL DEFAULT 0,
  `ord` int(11) NOT NULL DEFAULT 0,
  `indent` tinyint(4) NOT NULL DEFAULT 0,
  `page_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`wiki_id`,`page_id`)
) ;

--
-- Dumping data for table `il_wiki_imp_pages`
--


--
-- Table structure for table `il_wiki_missing_page`
--

CREATE TABLE `il_wiki_missing_page` (
  `wiki_id` int(11) NOT NULL DEFAULT 0,
  `source_id` int(11) NOT NULL DEFAULT 0,
  `target_name` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`wiki_id`,`source_id`,`target_name`),
  KEY `i1_idx` (`wiki_id`,`target_name`)
) ;

--
-- Dumping data for table `il_wiki_missing_page`
--


--
-- Table structure for table `il_wiki_page`
--

CREATE TABLE `il_wiki_page` (
  `id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  `wiki_id` int(11) NOT NULL DEFAULT 0,
  `blocked` tinyint(4) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 0,
  `hide_adv_md` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `il_wiki_page`
--


--
-- Table structure for table `il_wiki_page_seq`
--

CREATE TABLE `il_wiki_page_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `il_wiki_page_seq`
--


--
-- Table structure for table `int_link`
--

CREATE TABLE `int_link` (
  `source_type` varchar(10) NOT NULL DEFAULT ' ',
  `source_id` int(11) NOT NULL DEFAULT 0,
  `target_type` varchar(4) NOT NULL DEFAULT ' ',
  `target_id` int(11) NOT NULL DEFAULT 0,
  `target_inst` int(11) NOT NULL DEFAULT 0,
  `source_lang` varchar(2) NOT NULL DEFAULT '-',
  PRIMARY KEY (`source_type`,`source_id`,`source_lang`,`target_type`,`target_id`,`target_inst`),
  KEY `ta_idx` (`target_type`,`target_id`,`target_inst`),
  KEY `so_idx` (`source_type`,`source_id`)
) ;

--
-- Dumping data for table `int_link`
--


--
-- Table structure for table `item_group_item`
--

CREATE TABLE `item_group_item` (
  `item_group_id` int(11) NOT NULL DEFAULT 0,
  `item_ref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`item_group_id`,`item_ref_id`)
) ;

--
-- Dumping data for table `item_group_item`
--


--
-- Table structure for table `itgr_data`
--

CREATE TABLE `itgr_data` (
  `id` int(11) NOT NULL DEFAULT 0,
  `hide_title` tinyint(4) NOT NULL DEFAULT 0,
  `behaviour` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `itgr_data`
--


--
-- Table structure for table `last_visited`
--

CREATE TABLE `last_visited` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `nr` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(10) NOT NULL DEFAULT '',
  `sub_obj_id` varchar(40) DEFAULT NULL,
  `goto_link` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`nr`)
) ;

--
-- Dumping data for table `last_visited`
--


--
-- Table structure for table `ldap_attribute_mapping`
--

CREATE TABLE `ldap_attribute_mapping` (
  `server_id` int(11) NOT NULL DEFAULT 0,
  `keyword` varchar(32) NOT NULL DEFAULT ' ',
  `value` varchar(255) DEFAULT NULL,
  `perform_update` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`server_id`,`keyword`),
  KEY `i1_idx` (`server_id`)
) ;

--
-- Dumping data for table `ldap_attribute_mapping`
--


--
-- Table structure for table `ldap_rg_mapping`
--

CREATE TABLE `ldap_rg_mapping` (
  `mapping_id` int(11) NOT NULL DEFAULT 0,
  `server_id` int(11) NOT NULL DEFAULT 0,
  `url` varchar(255) DEFAULT NULL,
  `dn` varchar(255) DEFAULT NULL,
  `member_attribute` varchar(64) DEFAULT NULL,
  `member_isdn` tinyint(4) NOT NULL DEFAULT 0,
  `role` int(11) NOT NULL DEFAULT 0,
  `mapping_info` varchar(4000) DEFAULT NULL,
  `mapping_info_type` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`mapping_id`)
) ;

--
-- Dumping data for table `ldap_rg_mapping`
--


--
-- Table structure for table `ldap_rg_mapping_seq`
--

CREATE TABLE `ldap_rg_mapping_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ldap_rg_mapping_seq`
--


--
-- Table structure for table `ldap_role_assignments`
--

CREATE TABLE `ldap_role_assignments` (
  `server_id` int(11) NOT NULL DEFAULT 0,
  `rule_id` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `dn` varchar(1000) DEFAULT NULL,
  `attribute` varchar(32) DEFAULT NULL,
  `isdn` tinyint(4) NOT NULL DEFAULT 0,
  `att_name` varchar(255) DEFAULT NULL,
  `att_value` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL DEFAULT 0,
  `add_on_update` tinyint(4) DEFAULT NULL,
  `remove_on_update` tinyint(4) DEFAULT NULL,
  `plugin_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`server_id`,`rule_id`)
) ;

--
-- Dumping data for table `ldap_role_assignments`
--


--
-- Table structure for table `ldap_role_assignments_seq`
--

CREATE TABLE `ldap_role_assignments_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ldap_role_assignments_seq`
--


--
-- Table structure for table `ldap_server_settings`
--

CREATE TABLE `ldap_server_settings` (
  `server_id` int(11) NOT NULL DEFAULT 0,
  `active` int(11) NOT NULL DEFAULT 0,
  `name` varchar(32) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `version` int(11) NOT NULL DEFAULT 0,
  `base_dn` varchar(255) DEFAULT NULL,
  `referrals` int(11) NOT NULL DEFAULT 0,
  `tls` int(11) NOT NULL DEFAULT 0,
  `bind_type` int(11) NOT NULL DEFAULT 0,
  `bind_user` varchar(255) DEFAULT NULL,
  `bind_pass` varchar(32) DEFAULT NULL,
  `search_base` varchar(255) DEFAULT NULL,
  `user_scope` tinyint(4) NOT NULL DEFAULT 0,
  `user_attribute` varchar(255) DEFAULT NULL,
  `filter` varchar(512) DEFAULT NULL,
  `group_dn` varchar(255) DEFAULT NULL,
  `group_scope` tinyint(4) NOT NULL DEFAULT 0,
  `group_filter` varchar(255) DEFAULT NULL,
  `group_member` varchar(255) DEFAULT NULL,
  `group_memberisdn` tinyint(4) NOT NULL DEFAULT 0,
  `group_name` varchar(255) DEFAULT NULL,
  `group_attribute` varchar(64) DEFAULT NULL,
  `group_optional` tinyint(4) NOT NULL DEFAULT 0,
  `group_user_filter` varchar(255) DEFAULT NULL,
  `sync_on_login` tinyint(4) NOT NULL DEFAULT 0,
  `sync_per_cron` tinyint(4) NOT NULL DEFAULT 0,
  `role_sync_active` tinyint(4) NOT NULL DEFAULT 0,
  `role_bind_dn` varchar(255) DEFAULT NULL,
  `role_bind_pass` varchar(32) DEFAULT NULL,
  `migration` tinyint(4) NOT NULL DEFAULT 0,
  `authentication` tinyint(4) NOT NULL DEFAULT 1,
  `authentication_type` tinyint(4) NOT NULL DEFAULT 0,
  `username_filter` varchar(255) DEFAULT NULL,
  `escape_dn` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`server_id`)
) ;

--
-- Dumping data for table `ldap_server_settings`
--


--
-- Table structure for table `ldap_server_settings_seq`
--

CREATE TABLE `ldap_server_settings_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `ldap_server_settings_seq`
--


--
-- Table structure for table `like_data`
--

CREATE TABLE `like_data` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` varchar(40) NOT NULL,
  `sub_obj_id` int(11) NOT NULL DEFAULT 0,
  `sub_obj_type` varchar(40) NOT NULL,
  `news_id` int(11) NOT NULL DEFAULT 0,
  `like_type` int(11) NOT NULL DEFAULT 0,
  `exp_ts` datetime NOT NULL,
  PRIMARY KEY (`user_id`,`obj_id`,`obj_type`,`sub_obj_id`,`sub_obj_type`,`news_id`,`like_type`),
  KEY `i1_idx` (`obj_id`)
) ;

--
-- Dumping data for table `like_data`
--


--
-- Table structure for table `link_check`
--

CREATE TABLE `link_check` (
  `id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `page_id` int(11) NOT NULL DEFAULT 0,
  `url` varchar(255) DEFAULT NULL,
  `parent_type` varchar(8) DEFAULT NULL,
  `http_status_code` int(11) NOT NULL DEFAULT 0,
  `last_check` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`obj_id`)
) ;

--
-- Dumping data for table `link_check`
--


--
-- Table structure for table `link_check_report`
--

CREATE TABLE `link_check_report` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`usr_id`)
) ;

--
-- Dumping data for table `link_check_report`
--


--
-- Table structure for table `link_check_seq`
--

CREATE TABLE `link_check_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `link_check_seq`
--


--
-- Table structure for table `lm_data`
--

CREATE TABLE `lm_data` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  `type` char(2) DEFAULT NULL,
  `lm_id` int(11) NOT NULL DEFAULT 0,
  `import_id` varchar(50) DEFAULT NULL,
  `public_access` char(1) DEFAULT 'n',
  `create_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `active` char(1) DEFAULT 'y',
  `layout` varchar(100) DEFAULT NULL,
  `short_title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`obj_id`),
  KEY `i1_idx` (`lm_id`),
  KEY `i2_idx` (`type`),
  KEY `im_idx` (`import_id`)
) ;

--
-- Dumping data for table `lm_data`
--

INSERT INTO `lm_data` VALUES (1,'dummy','du',0,'','n',NULL,NULL,'y','',NULL);

--
-- Table structure for table `lm_data_seq`
--

CREATE TABLE `lm_data_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=2;

--
-- Dumping data for table `lm_data_seq`
--

INSERT INTO `lm_data_seq` VALUES (1);

--
-- Table structure for table `lm_data_transl`
--

CREATE TABLE `lm_data_transl` (
  `id` int(11) NOT NULL DEFAULT 0,
  `lang` varchar(2) NOT NULL DEFAULT '',
  `title` varchar(200) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `short_title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`,`lang`)
) ;

--
-- Dumping data for table `lm_data_transl`
--


--
-- Table structure for table `lm_glossaries`
--

CREATE TABLE `lm_glossaries` (
  `lm_id` int(11) NOT NULL DEFAULT 0,
  `glo_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`lm_id`,`glo_id`)
) ;

--
-- Dumping data for table `lm_glossaries`
--


--
-- Table structure for table `lm_menu`
--

CREATE TABLE `lm_menu` (
  `id` int(11) NOT NULL DEFAULT 0,
  `lm_id` int(11) NOT NULL DEFAULT 0,
  `link_type` char(6) DEFAULT 'extern',
  `title` varchar(200) DEFAULT NULL,
  `target` varchar(200) DEFAULT NULL,
  `link_ref_id` int(11) DEFAULT NULL,
  `active` char(1) DEFAULT 'n',
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`link_type`),
  KEY `i2_idx` (`lm_id`),
  KEY `i3_idx` (`active`)
) ;

--
-- Dumping data for table `lm_menu`
--


--
-- Table structure for table `lm_menu_seq`
--

CREATE TABLE `lm_menu_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `lm_menu_seq`
--


--
-- Table structure for table `lm_read_event`
--

CREATE TABLE `lm_read_event` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `read_count` int(11) NOT NULL DEFAULT 0,
  `spent_seconds` int(11) NOT NULL DEFAULT 0,
  `last_access` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`usr_id`)
) ;

--
-- Dumping data for table `lm_read_event`
--


--
-- Table structure for table `lm_tree`
--

CREATE TABLE `lm_tree` (
  `lm_id` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`lm_id`,`child`),
  KEY `i1_idx` (`child`),
  KEY `i2_idx` (`parent`),
  KEY `i3_idx` (`lm_id`)
) ;

--
-- Dumping data for table `lm_tree`
--


--
-- Table structure for table `lng_data`
--

CREATE TABLE `lng_data` (
  `module` varchar(30) NOT NULL DEFAULT ' ',
  `identifier` varchar(200) NOT NULL DEFAULT ' ',
  `lang_key` char(2) NOT NULL DEFAULT '',
  `value` varchar(4000) DEFAULT NULL,
  `local_change` datetime DEFAULT NULL,
  `remarks` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`module`,`identifier`,`lang_key`),
  KEY `i1_idx` (`module`),
  KEY `i2_idx` (`lang_key`),
  KEY `i3_idx` (`local_change`)
) ;

--
-- Dumping data for table `lng_data`
--


--
-- Table structure for table `lng_log`
--

CREATE TABLE `lng_log` (
  `module` varchar(30) NOT NULL DEFAULT '',
  `identifier` varchar(200) NOT NULL DEFAULT ' ',
  PRIMARY KEY (`module`,`identifier`)
) ;

--
-- Dumping data for table `lng_log`
--


--
-- Table structure for table `lng_modules`
--

CREATE TABLE `lng_modules` (
  `module` varchar(30) NOT NULL DEFAULT ' ',
  `lang_key` char(2) NOT NULL DEFAULT '',
  `lang_array` longtext DEFAULT NULL,
  PRIMARY KEY (`module`,`lang_key`)
) ;

--
-- Dumping data for table `lng_modules`
--


--
-- Table structure for table `lo_access`
--

CREATE TABLE `lo_access` (
  `timestamp` datetime DEFAULT NULL,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `lm_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `lm_title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`usr_id`,`lm_id`)
) ;

--
-- Dumping data for table `lo_access`
--


--
-- Table structure for table `loc_rnd_qpl`
--

CREATE TABLE `loc_rnd_qpl` (
  `container_id` int(11) NOT NULL DEFAULT 0,
  `objective_id` int(11) NOT NULL DEFAULT 0,
  `tst_type` tinyint(4) NOT NULL DEFAULT 0,
  `tst_id` int(11) NOT NULL DEFAULT 0,
  `qp_seq` int(11) NOT NULL DEFAULT 0,
  `percentage` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`container_id`,`objective_id`,`tst_type`,`tst_id`,`qp_seq`)
) ;

--
-- Dumping data for table `loc_rnd_qpl`
--


--
-- Table structure for table `loc_settings`
--

CREATE TABLE `loc_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `itest` int(11) DEFAULT NULL,
  `qtest` int(11) DEFAULT NULL,
  `qt_vis_all` tinyint(4) DEFAULT 1,
  `qt_vis_obj` tinyint(4) DEFAULT 0,
  `reset_results` tinyint(4) DEFAULT 0,
  `it_type` tinyint(4) DEFAULT 5,
  `qt_type` tinyint(4) DEFAULT 1,
  `it_start` tinyint(4) DEFAULT 1,
  `qt_start` tinyint(4) DEFAULT 1,
  `passed_obj_mode` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`obj_id`),
  KEY `i1_idx` (`itest`),
  KEY `i2_idx` (`qtest`)
) ;

--
-- Dumping data for table `loc_settings`
--


--
-- Table structure for table `loc_tst_assignments`
--

CREATE TABLE `loc_tst_assignments` (
  `assignment_id` int(11) NOT NULL DEFAULT 0,
  `container_id` int(11) NOT NULL DEFAULT 0,
  `assignment_type` tinyint(4) NOT NULL DEFAULT 0,
  `objective_id` int(11) NOT NULL DEFAULT 0,
  `tst_ref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`assignment_id`)
) ;

--
-- Dumping data for table `loc_tst_assignments`
--


--
-- Table structure for table `loc_tst_assignments_seq`
--

CREATE TABLE `loc_tst_assignments_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `loc_tst_assignments_seq`
--


--
-- Table structure for table `loc_tst_run`
--

CREATE TABLE `loc_tst_run` (
  `container_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `test_id` int(11) NOT NULL DEFAULT 0,
  `objective_id` int(11) NOT NULL DEFAULT 0,
  `max_points` int(11) DEFAULT 0,
  `questions` varchar(1000) DEFAULT '0',
  PRIMARY KEY (`container_id`,`user_id`,`test_id`,`objective_id`)
) ;

--
-- Dumping data for table `loc_tst_run`
--


--
-- Table structure for table `loc_user_results`
--

CREATE TABLE `loc_user_results` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `course_id` int(11) NOT NULL DEFAULT 0,
  `objective_id` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `status` tinyint(4) DEFAULT 0,
  `result_perc` tinyint(4) DEFAULT 0,
  `limit_perc` tinyint(4) DEFAULT 0,
  `tries` tinyint(4) DEFAULT 0,
  `is_final` tinyint(4) DEFAULT 0,
  `tstamp` int(11) DEFAULT 0,
  PRIMARY KEY (`user_id`,`course_id`,`objective_id`,`type`)
) ;

--
-- Dumping data for table `loc_user_results`
--


--
-- Table structure for table `log_components`
--

CREATE TABLE `log_components` (
  `component_id` varchar(20) NOT NULL DEFAULT '',
  `log_level` int(11) DEFAULT NULL,
  PRIMARY KEY (`component_id`)
) ;

--
-- Dumping data for table `log_components`
--

INSERT INTO `log_components` VALUES ('ac',0);
INSERT INTO `log_components` VALUES ('adm',NULL);
INSERT INTO `log_components` VALUES ('adn',NULL);
INSERT INTO `log_components` VALUES ('amet',NULL);
INSERT INTO `log_components` VALUES ('auth',0);
INSERT INTO `log_components` VALUES ('awrn',0);
INSERT INTO `log_components` VALUES ('bgtk',NULL);
INSERT INTO `log_components` VALUES ('book',NULL);
INSERT INTO `log_components` VALUES ('cal',NULL);
INSERT INTO `log_components` VALUES ('cat',NULL);
INSERT INTO `log_components` VALUES ('cert',NULL);
INSERT INTO `log_components` VALUES ('chtr',0);
INSERT INTO `log_components` VALUES ('cmix',NULL);
INSERT INTO `log_components` VALUES ('cont',NULL);
INSERT INTO `log_components` VALUES ('contact',0);
INSERT INTO `log_components` VALUES ('copg',0);
INSERT INTO `log_components` VALUES ('crs',0);
INSERT INTO `log_components` VALUES ('crsr',NULL);
INSERT INTO `log_components` VALUES ('db',0);
INSERT INTO `log_components` VALUES ('ds',NULL);
INSERT INTO `log_components` VALUES ('evnt',NULL);
INSERT INTO `log_components` VALUES ('exc',NULL);
INSERT INTO `log_components` VALUES ('exp',0);
INSERT INTO `log_components` VALUES ('file',0);
INSERT INTO `log_components` VALUES ('fils',NULL);
INSERT INTO `log_components` VALUES ('frm',NULL);
INSERT INTO `log_components` VALUES ('glo',0);
INSERT INTO `log_components` VALUES ('grp',0);
INSERT INTO `log_components` VALUES ('init',0);
INSERT INTO `log_components` VALUES ('lang',NULL);
INSERT INTO `log_components` VALUES ('lchk',0);
INSERT INTO `log_components` VALUES ('lhist',NULL);
INSERT INTO `log_components` VALUES ('lm',0);
INSERT INTO `log_components` VALUES ('log',0);
INSERT INTO `log_components` VALUES ('log_root',0);
INSERT INTO `log_components` VALUES ('lti',NULL);
INSERT INTO `log_components` VALUES ('mail',0);
INSERT INTO `log_components` VALUES ('mep',NULL);
INSERT INTO `log_components` VALUES ('meta',NULL);
INSERT INTO `log_components` VALUES ('mmbr',NULL);
INSERT INTO `log_components` VALUES ('mme',NULL);
INSERT INTO `log_components` VALUES ('mob',0);
INSERT INTO `log_components` VALUES ('obj',0);
INSERT INTO `log_components` VALUES ('osch',NULL);
INSERT INTO `log_components` VALUES ('otpl',0);
INSERT INTO `log_components` VALUES ('pdfg',NULL);
INSERT INTO `log_components` VALUES ('pwsp',NULL);
INSERT INTO `log_components` VALUES ('rep',0);
INSERT INTO `log_components` VALUES ('rnd',NULL);
INSERT INTO `log_components` VALUES ('sc13',NULL);
INSERT INTO `log_components` VALUES ('skll',0);
INSERT INTO `log_components` VALUES ('spl',NULL);
INSERT INTO `log_components` VALUES ('src',0);
INSERT INTO `log_components` VALUES ('styl',NULL);
INSERT INTO `log_components` VALUES ('svy',NULL);
INSERT INTO `log_components` VALUES ('sysc',NULL);
INSERT INTO `log_components` VALUES ('tos',NULL);
INSERT INTO `log_components` VALUES ('trac',NULL);
INSERT INTO `log_components` VALUES ('tree',0);
INSERT INTO `log_components` VALUES ('user',0);
INSERT INTO `log_components` VALUES ('wbdv',NULL);
INSERT INTO `log_components` VALUES ('webr',0);
INSERT INTO `log_components` VALUES ('wiki',NULL);
INSERT INTO `log_components` VALUES ('wsrv',0);

--
-- Table structure for table `loginname_history`
--

CREATE TABLE `loginname_history` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `login` varchar(80) NOT NULL DEFAULT '',
  `history_date` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`,`login`,`history_date`)
) ;

--
-- Dumping data for table `loginname_history`
--


--
-- Table structure for table `lso_activation`
--

CREATE TABLE `lso_activation` (
  `ref_id` int(11) NOT NULL,
  `online` tinyint(4) NOT NULL DEFAULT 0,
  `effective_online` tinyint(4) NOT NULL DEFAULT 0,
  `activation_start_ts` int(11) DEFAULT NULL,
  `activation_end_ts` int(11) DEFAULT NULL,
  PRIMARY KEY (`ref_id`)
) ;

--
-- Dumping data for table `lso_activation`
--


--
-- Table structure for table `lso_settings`
--

CREATE TABLE `lso_settings` (
  `obj_id` int(11) NOT NULL,
  `abstract` longtext DEFAULT NULL,
  `extro` longtext DEFAULT NULL,
  `abstract_image` varchar(128) DEFAULT NULL,
  `extro_image` varchar(128) DEFAULT NULL,
  `gallery` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `lso_settings`
--


--
-- Table structure for table `lso_states`
--

CREATE TABLE `lso_states` (
  `lso_ref_id` int(11) NOT NULL,
  `usr_id` int(11) NOT NULL,
  `current_item` int(11) DEFAULT NULL,
  `states` longtext DEFAULT NULL,
  `first_access` varchar(32) DEFAULT NULL,
  `last_access` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`lso_ref_id`,`usr_id`)
) ;

--
-- Dumping data for table `lso_states`
--


--
-- Table structure for table `lti2_consumer`
--

CREATE TABLE `lti2_consumer` (
  `consumer_pk` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `consumer_key256` varchar(256) NOT NULL,
  `consumer_key` longblob DEFAULT NULL,
  `secret` varchar(1024) NOT NULL,
  `lti_version` varchar(10) DEFAULT NULL,
  `consumer_name` varchar(255) DEFAULT NULL,
  `consumer_version` varchar(255) DEFAULT NULL,
  `consumer_guid` varchar(1024) DEFAULT NULL,
  `profile` longblob DEFAULT NULL,
  `tool_proxy` longblob DEFAULT NULL,
  `settings` longblob DEFAULT NULL,
  `protected` tinyint(4) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  `enable_from` datetime DEFAULT NULL,
  `enable_until` datetime DEFAULT NULL,
  `last_access` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `ext_consumer_id` int(11) NOT NULL,
  `ref_id` int(11) NOT NULL,
  `signature_method` varchar(15) NOT NULL DEFAULT 'HMAC-SHA1',
  PRIMARY KEY (`consumer_pk`)
) ;

--
-- Dumping data for table `lti2_consumer`
--


--
-- Table structure for table `lti2_consumer_seq`
--

CREATE TABLE `lti2_consumer_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `lti2_consumer_seq`
--


--
-- Table structure for table `lti2_context`
--

CREATE TABLE `lti2_context` (
  `context_pk` int(11) NOT NULL,
  `consumer_pk` int(11) NOT NULL,
  `lti_context_id` varchar(255) NOT NULL,
  `settings` longblob DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`context_pk`),
  KEY `i1_idx` (`consumer_pk`)
) ;

--
-- Dumping data for table `lti2_context`
--


--
-- Table structure for table `lti2_context_seq`
--

CREATE TABLE `lti2_context_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `lti2_context_seq`
--


--
-- Table structure for table `lti2_nonce`
--

CREATE TABLE `lti2_nonce` (
  `consumer_pk` int(11) NOT NULL,
  `value` varchar(50) NOT NULL DEFAULT '',
  `expires` datetime NOT NULL,
  PRIMARY KEY (`consumer_pk`,`value`)
) ;

--
-- Dumping data for table `lti2_nonce`
--


--
-- Table structure for table `lti2_resource_link`
--

CREATE TABLE `lti2_resource_link` (
  `resource_link_pk` int(11) NOT NULL DEFAULT 0,
  `context_pk` int(11) DEFAULT NULL,
  `consumer_pk` int(11) DEFAULT NULL,
  `lti_resource_link_id` varchar(255) NOT NULL,
  `settings` longblob DEFAULT NULL,
  `primary_resource_link_pk` int(11) DEFAULT NULL,
  `share_approved` tinyint(4) DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`resource_link_pk`),
  KEY `i1_idx` (`consumer_pk`),
  KEY `i2_idx` (`context_pk`)
) ;

--
-- Dumping data for table `lti2_resource_link`
--


--
-- Table structure for table `lti2_resource_link_seq`
--

CREATE TABLE `lti2_resource_link_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `lti2_resource_link_seq`
--


--
-- Table structure for table `lti2_share_key`
--

CREATE TABLE `lti2_share_key` (
  `share_key_id` varchar(32) NOT NULL,
  `resource_link_pk` int(11) NOT NULL,
  `auto_approve` tinyint(4) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`share_key_id`),
  KEY `i1_idx` (`resource_link_pk`)
) ;

--
-- Dumping data for table `lti2_share_key`
--


--
-- Table structure for table `lti2_tool_proxy`
--

CREATE TABLE `lti2_tool_proxy` (
  `tool_proxy_pk` int(11) NOT NULL,
  `tool_proxy_id` varchar(32) NOT NULL,
  `consumer_pk` int(11) NOT NULL,
  `tool_proxy` longblob NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`tool_proxy_pk`),
  UNIQUE KEY `u1_idx` (`tool_proxy_id`),
  KEY `i1_idx` (`consumer_pk`)
) ;

--
-- Dumping data for table `lti2_tool_proxy`
--


--
-- Table structure for table `lti2_tool_proxy_seq`
--

CREATE TABLE `lti2_tool_proxy_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `lti2_tool_proxy_seq`
--


--
-- Table structure for table `lti2_user_result`
--

CREATE TABLE `lti2_user_result` (
  `user_pk` int(11) NOT NULL DEFAULT 0,
  `resource_link_pk` int(11) NOT NULL,
  `lti_user_id` varchar(255) NOT NULL,
  `lti_result_sourcedid` varchar(1024) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`user_pk`),
  KEY `i1_idx` (`resource_link_pk`)
) ;

--
-- Dumping data for table `lti2_user_result`
--


--
-- Table structure for table `lti2_user_result_seq`
--

CREATE TABLE `lti2_user_result_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `lti2_user_result_seq`
--


--
-- Table structure for table `lti_consumer_results`
--

CREATE TABLE `lti_consumer_results` (
  `id` int(11) NOT NULL,
  `obj_id` int(11) NOT NULL,
  `usr_id` int(11) NOT NULL,
  `result` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`obj_id`,`usr_id`)
) ;

--
-- Dumping data for table `lti_consumer_results`
--


--
-- Table structure for table `lti_consumer_results_seq`
--

CREATE TABLE `lti_consumer_results_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `lti_consumer_results_seq`
--


--
-- Table structure for table `lti_consumer_settings`
--

CREATE TABLE `lti_consumer_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `provider_id` int(11) NOT NULL DEFAULT 0,
  `launch_method` varchar(16) NOT NULL DEFAULT '',
  `offline_status` tinyint(4) NOT NULL DEFAULT 1,
  `show_statements` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_achieved_ts` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_percentage` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_wtime` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_own_table` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_top_table` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_top_num` int(11) NOT NULL DEFAULT 0,
  `mastery_score` double NOT NULL DEFAULT 0.5,
  `keep_lp` tinyint(4) NOT NULL DEFAULT 0,
  `use_xapi` tinyint(4) NOT NULL DEFAULT 0,
  `activity_id` varchar(128) DEFAULT NULL,
  `launch_key` varchar(255) DEFAULT NULL,
  `launch_secret` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `lti_consumer_settings`
--


--
-- Table structure for table `lti_ext_consumer`
--

CREATE TABLE `lti_ext_consumer` (
  `id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `prefix` varchar(255) NOT NULL,
  `user_language` varchar(255) NOT NULL,
  `role` int(11) NOT NULL DEFAULT 0,
  `local_role_always_member` tinyint(4) NOT NULL DEFAULT 0,
  `default_skin` varchar(50) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `lti_ext_consumer`
--


--
-- Table structure for table `lti_ext_consumer_otype`
--

CREATE TABLE `lti_ext_consumer_otype` (
  `consumer_id` int(11) NOT NULL DEFAULT 0,
  `object_type` varchar(255) NOT NULL,
  PRIMARY KEY (`consumer_id`,`object_type`)
) ;

--
-- Dumping data for table `lti_ext_consumer_otype`
--


--
-- Table structure for table `lti_ext_consumer_seq`
--

CREATE TABLE `lti_ext_consumer_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `lti_ext_consumer_seq`
--


--
-- Table structure for table `lti_ext_provider`
--

CREATE TABLE `lti_ext_provider` (
  `id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `availability` smallint(6) NOT NULL DEFAULT 1,
  `remarks` varchar(4000) DEFAULT NULL,
  `time_to_delete` int(11) DEFAULT NULL,
  `provider_url` varchar(255) NOT NULL,
  `provider_key` varchar(128) NOT NULL,
  `provider_secret` varchar(128) NOT NULL,
  `provider_key_customizable` tinyint(4) NOT NULL DEFAULT 0,
  `provider_icon` varchar(255) DEFAULT NULL,
  `provider_xml` longtext DEFAULT NULL,
  `external_provider` tinyint(4) NOT NULL DEFAULT 0,
  `launch_method` varchar(32) DEFAULT NULL,
  `has_outcome` tinyint(4) NOT NULL DEFAULT 0,
  `mastery_score` double DEFAULT NULL,
  `keep_lp` tinyint(4) NOT NULL DEFAULT 0,
  `privacy_comment_default` varchar(2000) NOT NULL,
  `creator` int(11) DEFAULT NULL,
  `accepted_by` int(11) DEFAULT NULL,
  `global` tinyint(4) NOT NULL DEFAULT 0,
  `use_xapi` tinyint(4) NOT NULL DEFAULT 0,
  `xapi_launch_key` varchar(64) DEFAULT NULL,
  `xapi_launch_secret` varchar(64) DEFAULT NULL,
  `xapi_launch_url` varchar(255) DEFAULT NULL,
  `custom_params` varchar(1020) DEFAULT NULL,
  `use_provider_id` tinyint(4) NOT NULL DEFAULT 0,
  `always_learner` tinyint(4) NOT NULL DEFAULT 0,
  `xapi_activity_id` varchar(128) DEFAULT NULL,
  `keywords` varchar(1000) DEFAULT NULL,
  `inc_usr_pic` tinyint(4) NOT NULL DEFAULT 0,
  `category` varchar(16) NOT NULL DEFAULT '',
  `privacy_ident` smallint(6) NOT NULL DEFAULT 0,
  `privacy_name` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `lti_ext_provider`
--


--
-- Table structure for table `lti_ext_provider_seq`
--

CREATE TABLE `lti_ext_provider_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `lti_ext_provider_seq`
--


--
-- Table structure for table `lti_int_provider_obj`
--

CREATE TABLE `lti_int_provider_obj` (
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `ext_consumer_id` int(11) NOT NULL DEFAULT 0,
  `admin` int(11) DEFAULT NULL,
  `tutor` int(11) DEFAULT NULL,
  `member` int(11) DEFAULT NULL,
  PRIMARY KEY (`ref_id`,`ext_consumer_id`)
) ;

--
-- Dumping data for table `lti_int_provider_obj`
--


--
-- Table structure for table `mail`
--

CREATE TABLE `mail` (
  `mail_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `folder_id` int(11) NOT NULL DEFAULT 0,
  `sender_id` int(11) DEFAULT NULL,
  `send_time` datetime DEFAULT NULL,
  `m_status` varchar(16) DEFAULT NULL,
  `m_email` tinyint(4) DEFAULT NULL,
  `m_subject` varchar(255) DEFAULT NULL,
  `import_name` varchar(4000) DEFAULT NULL,
  `use_placeholders` tinyint(4) NOT NULL DEFAULT 0,
  `m_message` longtext DEFAULT NULL,
  `rcp_to` longtext DEFAULT NULL,
  `rcp_cc` longtext DEFAULT NULL,
  `rcp_bcc` longtext DEFAULT NULL,
  `attachments` longtext DEFAULT NULL,
  `tpl_ctx_id` varchar(100) DEFAULT NULL,
  `tpl_ctx_params` longblob DEFAULT NULL,
  PRIMARY KEY (`mail_id`),
  KEY `i1_idx` (`user_id`),
  KEY `i2_idx` (`folder_id`),
  KEY `i3_idx` (`m_status`),
  KEY `i4_idx` (`sender_id`,`user_id`)
) ;

--
-- Dumping data for table `mail`
--


--
-- Table structure for table `mail_attachment`
--

CREATE TABLE `mail_attachment` (
  `mail_id` int(11) NOT NULL DEFAULT 0,
  `path` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`mail_id`),
  KEY `i1_idx` (`path`(255))
) ;

--
-- Dumping data for table `mail_attachment`
--


--
-- Table structure for table `mail_cron_orphaned`
--

CREATE TABLE `mail_cron_orphaned` (
  `mail_id` int(11) NOT NULL DEFAULT 0,
  `folder_id` int(11) NOT NULL DEFAULT 0,
  `ts_do_delete` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`mail_id`,`folder_id`)
) ;

--
-- Dumping data for table `mail_cron_orphaned`
--


--
-- Table structure for table `mail_man_tpl`
--

CREATE TABLE `mail_man_tpl` (
  `tpl_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `context` varchar(100) NOT NULL DEFAULT '',
  `lang` varchar(2) NOT NULL DEFAULT '',
  `m_subject` varchar(255) DEFAULT NULL,
  `m_message` longtext DEFAULT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tpl_id`),
  KEY `i1_idx` (`context`)
) ;

--
-- Dumping data for table `mail_man_tpl`
--


--
-- Table structure for table `mail_man_tpl_seq`
--

CREATE TABLE `mail_man_tpl_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `mail_man_tpl_seq`
--


--
-- Table structure for table `mail_obj_data`
--

CREATE TABLE `mail_obj_data` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `title` char(70) DEFAULT NULL,
  `m_type` char(16) DEFAULT NULL,
  PRIMARY KEY (`obj_id`),
  KEY `i1_idx` (`user_id`,`m_type`),
  KEY `i2_idx` (`obj_id`,`user_id`)
) ;

--
-- Dumping data for table `mail_obj_data`
--

INSERT INTO `mail_obj_data` VALUES (2,6,'a_root','root');
INSERT INTO `mail_obj_data` VALUES (3,6,'b_inbox','inbox');
INSERT INTO `mail_obj_data` VALUES (4,6,'c_trash','trash');
INSERT INTO `mail_obj_data` VALUES (5,6,'d_drafts','drafts');
INSERT INTO `mail_obj_data` VALUES (6,6,'e_sent','sent');
INSERT INTO `mail_obj_data` VALUES (7,6,'z_local','local');

--
-- Table structure for table `mail_obj_data_seq`
--

CREATE TABLE `mail_obj_data_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=9;

--
-- Dumping data for table `mail_obj_data_seq`
--

INSERT INTO `mail_obj_data_seq` VALUES (8);

--
-- Table structure for table `mail_options`
--

CREATE TABLE `mail_options` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `linebreak` tinyint(4) NOT NULL DEFAULT 0,
  `signature` varchar(4000) DEFAULT NULL,
  `incoming_type` tinyint(4) DEFAULT NULL,
  `cronjob_notification` tinyint(4) NOT NULL DEFAULT 0,
  `mail_address_option` tinyint(4) NOT NULL DEFAULT 3,
  PRIMARY KEY (`user_id`),
  KEY `i1_idx` (`user_id`,`linebreak`)
) ;

--
-- Dumping data for table `mail_options`
--

INSERT INTO `mail_options` VALUES (6,60,'',0,0,3);

--
-- Table structure for table `mail_saved`
--

CREATE TABLE `mail_saved` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `m_email` tinyint(4) DEFAULT NULL,
  `m_subject` varchar(255) DEFAULT NULL,
  `use_placeholders` tinyint(4) NOT NULL DEFAULT 0,
  `m_message` longtext DEFAULT NULL,
  `rcp_to` longtext DEFAULT NULL,
  `rcp_cc` longtext DEFAULT NULL,
  `rcp_bcc` longtext DEFAULT NULL,
  `attachments` longtext DEFAULT NULL,
  `tpl_ctx_id` varchar(100) DEFAULT NULL,
  `tpl_ctx_params` longblob DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ;

--
-- Dumping data for table `mail_saved`
--


--
-- Table structure for table `mail_seq`
--

CREATE TABLE `mail_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `mail_seq`
--


--
-- Table structure for table `mail_template`
--

CREATE TABLE `mail_template` (
  `lang` varchar(5) NOT NULL DEFAULT ' ',
  `subject` varchar(200) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `sal_f` varchar(200) DEFAULT NULL,
  `sal_m` varchar(200) DEFAULT NULL,
  `sal_g` varchar(200) DEFAULT NULL,
  `type` varchar(4) NOT NULL DEFAULT ' ',
  `att_file` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`type`,`lang`)
) ;

--
-- Dumping data for table `mail_template`
--


--
-- Table structure for table `mail_tpl_ctx`
--

CREATE TABLE `mail_tpl_ctx` (
  `id` varchar(100) NOT NULL DEFAULT '',
  `component` varchar(100) NOT NULL DEFAULT '',
  `class` varchar(100) NOT NULL DEFAULT '',
  `path` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `mail_tpl_ctx`
--

INSERT INTO `mail_tpl_ctx` VALUES ('crs_context_member_manual','Modules/Course','ilCourseMailTemplateMemberContext',NULL);
INSERT INTO `mail_tpl_ctx` VALUES ('crs_context_tutor_manual','Modules/Course','ilCourseMailTemplateTutorContext',NULL);
INSERT INTO `mail_tpl_ctx` VALUES ('exc_context_grade_rmd','Modules/Exercise','ilExcMailTemplateGradeReminderContext',NULL);
INSERT INTO `mail_tpl_ctx` VALUES ('exc_context_peer_rmd','Modules/Exercise','ilExcMailTemplatePeerReminderContext',NULL);
INSERT INTO `mail_tpl_ctx` VALUES ('exc_context_submit_rmd','Modules/Exercise','ilExcMailTemplateSubmitReminderContext',NULL);
INSERT INTO `mail_tpl_ctx` VALUES ('mail_template_generic','Services/Mail','ilMailTemplateGenericContext',NULL);
INSERT INTO `mail_tpl_ctx` VALUES ('prg_context_manual','Modules/StudyProgramme','ilStudyProgrammeMailTemplateContext',NULL);
INSERT INTO `mail_tpl_ctx` VALUES ('sahs_context_lp','Modules/ScormAicc','ilScormMailTemplateLPContext',NULL);
INSERT INTO `mail_tpl_ctx` VALUES ('sess_context_participant_manual','Modules/Session','ilSessionMailTemplateParticipantContext',NULL);
INSERT INTO `mail_tpl_ctx` VALUES ('svy_context_rmd','Modules/Survey','ilSurveyMailTemplateReminderContext',NULL);

--
-- Table structure for table `mail_tree`
--

CREATE TABLE `mail_tree` (
  `tree` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`child`),
  KEY `i2_idx` (`parent`),
  KEY `i3_idx` (`tree`)
) ;

--
-- Dumping data for table `mail_tree`
--

INSERT INTO `mail_tree` VALUES (6,2,0,1,12,1);
INSERT INTO `mail_tree` VALUES (6,3,2,2,3,2);
INSERT INTO `mail_tree` VALUES (6,4,2,4,5,2);
INSERT INTO `mail_tree` VALUES (6,5,2,6,7,2);
INSERT INTO `mail_tree` VALUES (6,6,2,8,9,2);
INSERT INTO `mail_tree` VALUES (6,7,2,10,11,2);

--
-- Table structure for table `map_area`
--

CREATE TABLE `map_area` (
  `item_id` int(11) NOT NULL DEFAULT 0,
  `nr` int(11) NOT NULL DEFAULT 0,
  `shape` varchar(20) DEFAULT NULL,
  `coords` varchar(200) DEFAULT NULL,
  `link_type` char(3) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `href` varchar(800) DEFAULT NULL,
  `target` varchar(50) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `target_frame` varchar(50) DEFAULT NULL,
  `highlight_mode` varchar(8) DEFAULT NULL,
  `highlight_class` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`item_id`,`nr`),
  KEY `lt_idx` (`link_type`,`target`)
) ;

--
-- Dumping data for table `map_area`
--


--
-- Table structure for table `media_item`
--

CREATE TABLE `media_item` (
  `id` int(11) NOT NULL DEFAULT 0,
  `width` varchar(10) DEFAULT NULL,
  `height` varchar(10) DEFAULT NULL,
  `halign` char(10) DEFAULT 'Left',
  `caption` varchar(3000) DEFAULT NULL,
  `nr` int(11) NOT NULL DEFAULT 0,
  `purpose` char(20) DEFAULT 'Standard',
  `mob_id` int(11) NOT NULL DEFAULT 0,
  `location` varchar(200) DEFAULT NULL,
  `location_type` char(10) DEFAULT 'LocalFile',
  `format` varchar(200) DEFAULT NULL,
  `param` varchar(2000) DEFAULT NULL,
  `tried_thumb` char(1) DEFAULT 'n',
  `text_representation` varchar(4000) DEFAULT NULL,
  `upload_hash` varchar(100) DEFAULT NULL,
  `duration` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `media_item`
--


--
-- Table structure for table `media_item_seq`
--

CREATE TABLE `media_item_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `media_item_seq`
--


--
-- Table structure for table `member_agreement`
--

CREATE TABLE `member_agreement` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `accepted` tinyint(4) NOT NULL DEFAULT 0,
  `acceptance_time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`,`obj_id`)
) ;

--
-- Dumping data for table `member_agreement`
--


--
-- Table structure for table `member_noti`
--

CREATE TABLE `member_noti` (
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `nmode` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ref_id`)
) ;

--
-- Dumping data for table `member_noti`
--


--
-- Table structure for table `member_noti_user`
--

CREATE TABLE `member_noti_user` (
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ref_id`,`user_id`)
) ;

--
-- Dumping data for table `member_noti_user`
--


--
-- Table structure for table `mep_data`
--

CREATE TABLE `mep_data` (
  `id` int(11) NOT NULL DEFAULT 0,
  `default_width` int(11) DEFAULT NULL,
  `default_height` int(11) DEFAULT NULL,
  `for_translation` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `mep_data`
--


--
-- Table structure for table `mep_item`
--

CREATE TABLE `mep_item` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(10) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `foreign_id` int(11) DEFAULT NULL,
  `import_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`obj_id`),
  KEY `ft_idx` (`foreign_id`,`type`)
) ;

--
-- Dumping data for table `mep_item`
--

INSERT INTO `mep_item` VALUES (1,'dummy','Dummy',1,'');

--
-- Table structure for table `mep_item_seq`
--

CREATE TABLE `mep_item_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=2;

--
-- Dumping data for table `mep_item_seq`
--

INSERT INTO `mep_item_seq` VALUES (1);

--
-- Table structure for table `mep_tree`
--

CREATE TABLE `mep_tree` (
  `mep_id` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) NOT NULL DEFAULT 0,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`mep_id`,`child`),
  KEY `ch_idx` (`child`),
  KEY `pa_idx` (`parent`),
  KEY `me_idx` (`mep_id`)
) ;

--
-- Dumping data for table `mep_tree`
--


--
-- Table structure for table `mob_parameter`
--

CREATE TABLE `mob_parameter` (
  `med_item_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`med_item_id`,`name`)
) ;

--
-- Dumping data for table `mob_parameter`
--


--
-- Table structure for table `mob_usage`
--

CREATE TABLE `mob_usage` (
  `id` int(11) NOT NULL DEFAULT 0,
  `usage_type` varchar(10) NOT NULL DEFAULT ' ',
  `usage_id` int(11) NOT NULL DEFAULT 0,
  `usage_hist_nr` int(11) NOT NULL DEFAULT 0,
  `usage_lang` varchar(2) NOT NULL DEFAULT '-',
  PRIMARY KEY (`id`,`usage_type`,`usage_id`,`usage_hist_nr`,`usage_lang`),
  KEY `mi_idx` (`id`),
  KEY `i1_idx` (`usage_id`)
) ;

--
-- Dumping data for table `mob_usage`
--


--
-- Table structure for table `module_class`
--

CREATE TABLE `module_class` (
  `class` varchar(100) NOT NULL DEFAULT ' ',
  `module` varchar(100) DEFAULT NULL,
  `dir` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`class`)
) ;

--
-- Dumping data for table `module_class`
--

INSERT INTO `module_class` VALUES ('CmiXapiGUI','CmiXapi','classes');
INSERT INTO `module_class` VALUES ('ilAccessibilitySupportContactsGUI','SystemFolder','classes');
INSERT INTO `module_class` VALUES ('ilExerciseHandlerGUI','Exercise','classes');
INSERT INTO `module_class` VALUES ('ilGlossaryEditorGUI','Glossary','classes');
INSERT INTO `module_class` VALUES ('ilGlossaryPresentationGUI','Glossary','classes');
INSERT INTO `module_class` VALUES ('ilHTLMEditorGUI','HTMLLearningModule','classes');
INSERT INTO `module_class` VALUES ('ilHTLMPresentationGUI','HTMLLearningModule','classes');
INSERT INTO `module_class` VALUES ('ilLinkResourceHandlerGUI','WebResource','classes');
INSERT INTO `module_class` VALUES ('ilLMEditorGUI','LearningModule','Editing/classes');
INSERT INTO `module_class` VALUES ('ilLMPresentationGUI','LearningModule','Presentation/classes');
INSERT INTO `module_class` VALUES ('ilMediaCastHandlerGUI','MediaCast','classes');
INSERT INTO `module_class` VALUES ('ilMediaPoolPresentationGUI','MediaPool','classes');
INSERT INTO `module_class` VALUES ('ilObjCategoryReferenceGUI','CategoryReference','classes');
INSERT INTO `module_class` VALUES ('ilObjChatroomAdminGUI','Chatroom','classes');
INSERT INTO `module_class` VALUES ('ilObjChatroomGUI','Chatroom','classes');
INSERT INTO `module_class` VALUES ('ilObjCourseReferenceGUI','CourseReference','classes');
INSERT INTO `module_class` VALUES ('ilObjGroupReferenceGUI','GroupReference','classes');
INSERT INTO `module_class` VALUES ('ilObjIndividualAssessmentGUI','IndividualAssessment','classes');
INSERT INTO `module_class` VALUES ('ilObjLearningSequenceGUI','LearningSequence','classes');
INSERT INTO `module_class` VALUES ('ilObjPortfolioGUI','Portfolio','classes');
INSERT INTO `module_class` VALUES ('ilObjQuestionPoolGUI','TestQuestionPool','classes');
INSERT INTO `module_class` VALUES ('ilObjRemoteCategoryGUI','RemoteCategory','classes');
INSERT INTO `module_class` VALUES ('ilObjRemoteCourseGUI','RemoteCourse','classes');
INSERT INTO `module_class` VALUES ('ilObjRemoteFileGUI','RemoteFile','classes');
INSERT INTO `module_class` VALUES ('ilObjRemoteGlossaryGUI','RemoteGlossary','classes');
INSERT INTO `module_class` VALUES ('ilObjRemoteGroupGUI','RemoteGroup','classes');
INSERT INTO `module_class` VALUES ('ilObjRemoteLearningModuleGUI','RemoteLearningModule','classes');
INSERT INTO `module_class` VALUES ('ilObjRemoteTestGUI','RemoteTest','classes');
INSERT INTO `module_class` VALUES ('ilObjRemoteWikiGUI','RemoteWiki','classes');
INSERT INTO `module_class` VALUES ('ilObjStudyProgrammeReferenceGUI','StudyProgrammeReference','classes');
INSERT INTO `module_class` VALUES ('ilObjSurveyGUI','Survey','classes');
INSERT INTO `module_class` VALUES ('ilObjSurveyQuestionPoolGUI','SurveyQuestionPool','classes');
INSERT INTO `module_class` VALUES ('ilObjTestGUI','Test','classes');
INSERT INTO `module_class` VALUES ('ilPortfolioRepositoryGUI','Portfolio','classes');
INSERT INTO `module_class` VALUES ('ilSAHSEditGUI','ScormAicc','Editing/classes');
INSERT INTO `module_class` VALUES ('ilSAHSPresentationGUI','ScormAicc','classes');
INSERT INTO `module_class` VALUES ('ilSystemSupportContactsGUI','SystemFolder','classes');
INSERT INTO `module_class` VALUES ('ilWikiHandlerGUI','Wiki','classes');
INSERT INTO `module_class` VALUES ('LTIConsumerGUI','LTIConsumer','classes');

--
-- Table structure for table `note`
--

CREATE TABLE `note` (
  `id` int(11) NOT NULL DEFAULT 0,
  `rep_obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` varchar(10) DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT 0,
  `author` int(11) NOT NULL DEFAULT 0,
  `note_text` longtext DEFAULT NULL,
  `label` int(11) NOT NULL DEFAULT 0,
  `creation_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `no_repository` tinyint(4) DEFAULT 0,
  `news_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`author`),
  KEY `i2_idx` (`rep_obj_id`,`obj_id`,`obj_type`)
) ;

--
-- Dumping data for table `note`
--


--
-- Table structure for table `note_seq`
--

CREATE TABLE `note_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `note_seq`
--


--
-- Table structure for table `note_settings`
--

CREATE TABLE `note_settings` (
  `rep_obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` varchar(10) NOT NULL DEFAULT '-',
  `activated` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rep_obj_id`,`obj_id`,`obj_type`)
) ;

--
-- Dumping data for table `note_settings`
--


--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `last_mail` datetime DEFAULT NULL,
  `page_id` int(11) DEFAULT 0,
  `activated` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`type`,`id`,`user_id`)
) ;

--
-- Dumping data for table `notification`
--


--
-- Table structure for table `notification_channels`
--

CREATE TABLE `notification_channels` (
  `channel_name` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(4000) NOT NULL DEFAULT '',
  `class` varchar(100) NOT NULL DEFAULT '',
  `include` varchar(100) NOT NULL DEFAULT '',
  `config_type` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`channel_name`)
) ;

--
-- Dumping data for table `notification_channels`
--

INSERT INTO `notification_channels` VALUES ('mail','mail','mail_desc','ilNotificationMailHandler','Services/Notifications/classes/class.ilNotificationMailHandler.php','set_by_admin');
INSERT INTO `notification_channels` VALUES ('osd','osd','osd_desc','ilNotificationOSDHandler','Services/Notifications/classes/class.ilNotificationOSDHandler.php','set_by_admin');

--
-- Table structure for table `notification_data`
--

CREATE TABLE `notification_data` (
  `notification_id` int(11) NOT NULL DEFAULT 0,
  `serialized` varchar(4000) NOT NULL DEFAULT '',
  PRIMARY KEY (`notification_id`)
) ;

--
-- Dumping data for table `notification_data`
--


--
-- Table structure for table `notification_data_seq`
--

CREATE TABLE `notification_data_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `notification_data_seq`
--


--
-- Table structure for table `notification_listener`
--

CREATE TABLE `notification_listener` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(100) NOT NULL DEFAULT '',
  `sender_id` int(11) NOT NULL DEFAULT 0,
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`,`module`,`sender_id`)
) ;

--
-- Dumping data for table `notification_listener`
--


--
-- Table structure for table `notification_osd`
--

CREATE TABLE `notification_osd` (
  `notification_osd_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `serialized` varchar(4000) NOT NULL DEFAULT '',
  `valid_until` int(11) NOT NULL DEFAULT 0,
  `time_added` int(11) NOT NULL DEFAULT 0,
  `type` varchar(100) NOT NULL DEFAULT '',
  `visible_for` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`notification_osd_id`)
) ;

--
-- Dumping data for table `notification_osd`
--


--
-- Table structure for table `notification_osd_seq`
--

CREATE TABLE `notification_osd_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `notification_osd_seq`
--


--
-- Table structure for table `notification_queue`
--

CREATE TABLE `notification_queue` (
  `notification_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `valid_until` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`notification_id`,`usr_id`)
) ;

--
-- Dumping data for table `notification_queue`
--


--
-- Table structure for table `notification_types`
--

CREATE TABLE `notification_types` (
  `type_name` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `notification_group` varchar(100) NOT NULL DEFAULT '',
  `config_type` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`type_name`)
) ;

--
-- Dumping data for table `notification_types`
--

INSERT INTO `notification_types` VALUES ('chat_invitation','chat_invitation','chat_invitation_description','chat','set_by_admin');
INSERT INTO `notification_types` VALUES ('osd_maint','osd_maint','osd_maint_description','osd_notification','set_by_admin');

--
-- Table structure for table `notification_usercfg`
--

CREATE TABLE `notification_usercfg` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(100) NOT NULL DEFAULT '',
  `channel` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`usr_id`,`module`,`channel`)
) ;

--
-- Dumping data for table `notification_usercfg`
--

INSERT INTO `notification_usercfg` VALUES (-1,'buddysystem_request','mail');
INSERT INTO `notification_usercfg` VALUES (-1,'buddysystem_request','osd');
INSERT INTO `notification_usercfg` VALUES (-1,'chat_invitation','mail');
INSERT INTO `notification_usercfg` VALUES (-1,'chat_invitation','osd');
INSERT INTO `notification_usercfg` VALUES (-1,'osd_main','osd');

--
-- Table structure for table `obj_content_master_lng`
--

CREATE TABLE `obj_content_master_lng` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `master_lang` varchar(2) NOT NULL DEFAULT '',
  `fallback_lang` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `obj_content_master_lng`
--


--
-- Table structure for table `obj_lp_stat`
--

CREATE TABLE `obj_lp_stat` (
  `type` varchar(4) NOT NULL DEFAULT '',
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `yyyy` smallint(6) NOT NULL DEFAULT 0,
  `mm` tinyint(4) NOT NULL DEFAULT 0,
  `dd` tinyint(4) NOT NULL DEFAULT 0,
  `fulldate` int(11) NOT NULL DEFAULT 0,
  `mem_cnt` int(11) DEFAULT NULL,
  `in_progress` int(11) DEFAULT NULL,
  `completed` int(11) DEFAULT NULL,
  `failed` int(11) DEFAULT NULL,
  `not_attempted` int(11) DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`fulldate`)
) ;

--
-- Dumping data for table `obj_lp_stat`
--


--
-- Table structure for table `obj_members`
--

CREATE TABLE `obj_members` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `blocked` tinyint(4) NOT NULL DEFAULT 0,
  `notification` tinyint(4) NOT NULL DEFAULT 0,
  `passed` tinyint(4) DEFAULT NULL,
  `origin` int(11) DEFAULT 0,
  `origin_ts` int(11) DEFAULT 0,
  `contact` tinyint(4) DEFAULT 0,
  `admin` tinyint(4) DEFAULT 0,
  `tutor` tinyint(4) DEFAULT 0,
  `member` smallint(6) DEFAULT 0,
  PRIMARY KEY (`obj_id`,`usr_id`),
  KEY `i1_idx` (`usr_id`)
) ;

--
-- Dumping data for table `obj_members`
--


--
-- Table structure for table `obj_noti_settings`
--

CREATE TABLE `obj_noti_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `noti_mode` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `obj_noti_settings`
--


--
-- Table structure for table `obj_stat`
--

CREATE TABLE `obj_stat` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` varchar(10) NOT NULL DEFAULT '',
  `yyyy` smallint(6) NOT NULL DEFAULT 0,
  `mm` tinyint(4) NOT NULL DEFAULT 0,
  `dd` tinyint(4) NOT NULL DEFAULT 0,
  `hh` tinyint(4) NOT NULL DEFAULT 0,
  `read_count` int(11) DEFAULT NULL,
  `childs_read_count` int(11) DEFAULT NULL,
  `spent_seconds` int(11) DEFAULT NULL,
  `childs_spent_seconds` int(11) DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`yyyy`,`mm`,`dd`,`hh`)
) ;

--
-- Dumping data for table `obj_stat`
--


--
-- Table structure for table `obj_stat_log`
--

CREATE TABLE `obj_stat_log` (
  `log_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` varchar(10) NOT NULL DEFAULT '',
  `tstamp` int(11) DEFAULT NULL,
  `yyyy` smallint(6) DEFAULT NULL,
  `mm` tinyint(4) DEFAULT NULL,
  `dd` tinyint(4) DEFAULT NULL,
  `hh` tinyint(4) DEFAULT NULL,
  `read_count` int(11) DEFAULT NULL,
  `childs_read_count` int(11) DEFAULT NULL,
  `spent_seconds` int(11) DEFAULT NULL,
  `childs_spent_seconds` int(11) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `i1_idx` (`tstamp`)
) ;

--
-- Dumping data for table `obj_stat_log`
--


--
-- Table structure for table `obj_stat_log_seq`
--

CREATE TABLE `obj_stat_log_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `obj_stat_log_seq`
--


--
-- Table structure for table `obj_stat_tmp`
--

CREATE TABLE `obj_stat_tmp` (
  `log_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` varchar(10) NOT NULL DEFAULT '',
  `tstamp` int(11) DEFAULT NULL,
  `yyyy` smallint(6) DEFAULT NULL,
  `mm` tinyint(4) DEFAULT NULL,
  `dd` tinyint(4) DEFAULT NULL,
  `hh` tinyint(4) DEFAULT NULL,
  `read_count` int(11) DEFAULT NULL,
  `childs_read_count` int(11) DEFAULT NULL,
  `spent_seconds` int(11) DEFAULT NULL,
  `childs_spent_seconds` int(11) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `i1_idx` (`obj_id`,`obj_type`,`yyyy`,`mm`,`dd`,`hh`)
) ;

--
-- Dumping data for table `obj_stat_tmp`
--


--
-- Table structure for table `obj_type_stat`
--

CREATE TABLE `obj_type_stat` (
  `type` varchar(4) NOT NULL DEFAULT '',
  `yyyy` smallint(6) NOT NULL DEFAULT 0,
  `mm` tinyint(4) NOT NULL DEFAULT 0,
  `dd` tinyint(4) NOT NULL DEFAULT 0,
  `fulldate` int(11) NOT NULL DEFAULT 0,
  `cnt_references` int(11) DEFAULT NULL,
  `cnt_objects` int(11) DEFAULT NULL,
  `cnt_deleted` int(11) DEFAULT NULL,
  PRIMARY KEY (`type`,`fulldate`)
) ;

--
-- Dumping data for table `obj_type_stat`
--


--
-- Table structure for table `obj_user_data_hist`
--

CREATE TABLE `obj_user_data_hist` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `update_user` int(11) NOT NULL DEFAULT 0,
  `editing_time` datetime DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`usr_id`)
) ;

--
-- Dumping data for table `obj_user_data_hist`
--


--
-- Table structure for table `obj_user_stat`
--

CREATE TABLE `obj_user_stat` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `yyyy` smallint(6) NOT NULL DEFAULT 0,
  `mm` tinyint(4) NOT NULL DEFAULT 0,
  `dd` tinyint(4) NOT NULL DEFAULT 0,
  `fulldate` int(11) NOT NULL DEFAULT 0,
  `counter` int(11) DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`fulldate`)
) ;

--
-- Dumping data for table `obj_user_stat`
--


--
-- Table structure for table `object_data`
--

CREATE TABLE `object_data` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `type` char(4) DEFAULT 'none',
  `title` char(255) DEFAULT NULL,
  `description` char(128) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT 0,
  `create_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `import_id` char(50) DEFAULT NULL,
  `offline` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`obj_id`),
  KEY `i1_idx` (`type`),
  KEY `i2_idx` (`title`),
  KEY `i4_idx` (`import_id`),
  KEY `i5_idx` (`owner`)
) ;

--
-- Dumping data for table `object_data`
--

INSERT INTO `object_data` VALUES (1,'root','ILIAS','This is the root node of the system!!!',-1,'2002-06-24 15:15:03','2004-01-20 12:24:12','',NULL);
INSERT INTO `object_data` VALUES (2,'role','Administrator','Role for systemadministrators. This role grants access to everything!',-1,'2002-01-16 15:31:45','2003-08-15 13:18:57','',NULL);
INSERT INTO `object_data` VALUES (3,'rolt','Author','Role template for authors with write & create permissions.',-1,'2002-01-16 15:32:50','2016-12-16 14:58:27','',NULL);
INSERT INTO `object_data` VALUES (4,'role','User','Standard role for registered users. Grants read access to most objects.',-1,'2002-01-16 15:34:00','2016-12-16 14:56:25','',NULL);
INSERT INTO `object_data` VALUES (5,'role','Guest','Role grants only a few visible & read permissions.',-1,'2002-01-16 15:34:46','2016-12-16 14:54:14','',NULL);
INSERT INTO `object_data` VALUES (6,'usr','root user','ilias@yourserver.com',-1,'2002-01-16 16:09:22','2016-12-14 14:44:49','',NULL);
INSERT INTO `object_data` VALUES (7,'usrf','User accounts','Manage user accounts here.',-1,'2002-06-27 09:24:06','2004-01-20 12:23:47','',NULL);
INSERT INTO `object_data` VALUES (8,'rolf','Roles','Manage your roles here.',-1,'2002-06-27 09:24:06','2004-01-20 12:23:40','',NULL);
INSERT INTO `object_data` VALUES (9,'adm','System Settings','Folder contains the systems settings',-1,'2002-07-15 12:37:33','2002-07-15 12:37:33','',NULL);
INSERT INTO `object_data` VALUES (10,'objf','Objectdefinitions','Manage ILIAS object types and object permissions. (only for experts!)',-1,'2002-07-15 12:36:56','2004-01-20 12:23:53','',NULL);
INSERT INTO `object_data` VALUES (11,'lngf','Languages','Manage your system languages here.',-1,'2002-07-15 15:52:51','2004-01-20 12:24:06','',NULL);
INSERT INTO `object_data` VALUES (12,'mail','Mail Settings','Configure global mail settings here.',-1,'2003-08-15 10:07:28','2004-01-20 12:24:00','',NULL);
INSERT INTO `object_data` VALUES (13,'usr','Anonymous','Anonymous user account. DO NOT DELETE!',-1,'2003-08-15 10:07:30','2003-08-15 10:07:30','',NULL);
INSERT INTO `object_data` VALUES (14,'role','Anonymous','Default role for anonymous users (with no account)',-1,'2003-08-15 12:06:19','2005-07-20 15:15:06','',NULL);
INSERT INTO `object_data` VALUES (15,'typ','grp','Group object',-1,'2002-07-15 15:54:37','2002-07-15 15:54:37','',NULL);
INSERT INTO `object_data` VALUES (16,'typ','cat','Category object',-1,'2002-07-15 15:54:54','2002-07-15 15:54:54','',NULL);
INSERT INTO `object_data` VALUES (17,'typ','crs','Course object',-1,'2002-07-15 15:55:08','2002-07-15 15:55:08','',NULL);
INSERT INTO `object_data` VALUES (18,'typ','mob','Multimedia object',-1,NULL,'2003-08-15 12:03:20','',NULL);
INSERT INTO `object_data` VALUES (19,'typ','mail','Mailmodule object',-1,'2002-07-15 15:55:49','2002-07-15 15:55:49','',NULL);
INSERT INTO `object_data` VALUES (20,'typ','sahs','SCORM/AICC Learning Module',-1,'2003-08-15 10:07:28','2003-08-15 12:23:10','',NULL);
INSERT INTO `object_data` VALUES (21,'typ','adm','Administration Panel object',-1,'2002-07-15 15:56:38','2002-07-15 15:56:38','',NULL);
INSERT INTO `object_data` VALUES (22,'typ','usrf','User Folder object',-1,'2002-07-15 15:56:52','2002-07-15 15:56:52','',NULL);
INSERT INTO `object_data` VALUES (23,'typ','rolf','Role Folder object',-1,'2002-07-15 15:57:06','2002-07-15 15:57:06','',NULL);
INSERT INTO `object_data` VALUES (24,'typ','objf','Object-Type Folder object',-1,'2002-07-15 15:57:17','2002-07-15 15:57:17','',NULL);
INSERT INTO `object_data` VALUES (25,'typ','usr','User object',-1,'2002-07-15 15:53:37','2003-08-15 12:30:56','',NULL);
INSERT INTO `object_data` VALUES (26,'typ','typ','Object Type Definition object',-1,'2002-07-15 15:58:16','2002-07-15 15:58:16','',NULL);
INSERT INTO `object_data` VALUES (27,'typ','rolt','Role template object',-1,'2002-07-15 15:58:16','2002-07-15 15:58:16','',NULL);
INSERT INTO `object_data` VALUES (28,'typ','lngf','Language Folder object',-1,'2002-08-28 14:22:01','2002-08-28 14:22:01','',NULL);
INSERT INTO `object_data` VALUES (29,'typ','lng','Language object',-1,'2002-08-30 10:18:29','2002-08-30 10:18:29','',NULL);
INSERT INTO `object_data` VALUES (30,'typ','role','Role Object',-1,'2002-08-30 10:21:37','2002-08-30 10:21:37','',NULL);
INSERT INTO `object_data` VALUES (31,'typ','dbk','Digilib Book',-1,'2003-08-15 10:07:29','2003-08-15 12:30:19','',NULL);
INSERT INTO `object_data` VALUES (32,'typ','glo','Glossary',-1,'2003-08-15 10:07:30','2003-08-15 12:29:54','',NULL);
INSERT INTO `object_data` VALUES (33,'typ','root','Root Folder Object',-1,'2002-12-21 00:04:00','2003-08-15 12:04:20','',NULL);
INSERT INTO `object_data` VALUES (34,'typ','lm','Learning module Object',-1,'2002-07-15 15:54:04','2003-08-15 12:33:04','',NULL);
INSERT INTO `object_data` VALUES (35,'typ','notf','Note Folder Object',-1,'2002-12-21 00:04:00','2002-12-21 00:04:00','',NULL);
INSERT INTO `object_data` VALUES (36,'typ','note','Note Object',-1,'2002-12-21 00:04:00','2002-12-21 00:04:00','',NULL);
INSERT INTO `object_data` VALUES (37,'typ','frm','Forum object',-1,'2002-07-15 15:54:22','2003-08-15 12:36:40','',NULL);
INSERT INTO `object_data` VALUES (70,'lng','en','installed_local',-1,NULL,'2020-11-20 17:13:24','',NULL);
INSERT INTO `object_data` VALUES (71,'lng','de','not_installed',6,'2003-08-15 10:25:19','2015-12-22 16:29:24','',NULL);
INSERT INTO `object_data` VALUES (72,'lng','es','not_installed',6,'2003-08-15 10:25:19','2003-08-15 10:25:19','',NULL);
INSERT INTO `object_data` VALUES (73,'lng','it','not_installed',6,'2003-08-15 10:25:19','2003-08-15 10:25:19','',NULL);
INSERT INTO `object_data` VALUES (80,'rolt','il_grp_admin','Administrator role template of groups',-1,'2003-08-15 10:07:28','2016-12-16 15:05:30','',NULL);
INSERT INTO `object_data` VALUES (81,'rolt','il_grp_member','Member role template of groups',-1,'2003-08-15 10:07:28','2016-12-16 15:06:38','',NULL);
INSERT INTO `object_data` VALUES (82,'rolt','il_grp_status_closed','Group role template',-1,'2003-08-15 10:07:29','2003-08-15 13:21:38','',NULL);
INSERT INTO `object_data` VALUES (83,'rolt','il_grp_status_open','Group role template',-1,'2003-08-15 10:07:29','2003-08-15 13:21:25','',NULL);
INSERT INTO `object_data` VALUES (84,'typ','exc','Exercise object',-1,'2003-11-30 21:22:49','2003-11-30 21:22:49','',NULL);
INSERT INTO `object_data` VALUES (85,'typ','auth','Authentication settings',-1,'2003-11-30 21:22:49','2003-11-30 21:22:49','',NULL);
INSERT INTO `object_data` VALUES (86,'auth','Authentication settings','Select and configure authentication mode for all user accounts',-1,'2003-11-30 21:22:49','2003-11-30 21:22:49','',NULL);
INSERT INTO `object_data` VALUES (87,'typ','fold','Folder object',-1,'2003-11-30 21:22:50','2003-11-30 21:22:50','',NULL);
INSERT INTO `object_data` VALUES (88,'typ','file','File object',-1,'2003-11-30 21:22:50','2003-11-30 21:22:50','',NULL);
INSERT INTO `object_data` VALUES (89,'lng','fr','not_installed',6,'2004-01-20 12:22:17','2004-01-20 12:22:17','',NULL);
INSERT INTO `object_data` VALUES (90,'lng','nl','not_installed',6,'2004-01-20 12:22:17','2004-01-20 12:22:17','',NULL);
INSERT INTO `object_data` VALUES (91,'lng','pl','not_installed',6,'2004-01-20 12:22:17','2004-01-20 12:22:17','',NULL);
INSERT INTO `object_data` VALUES (93,'lng','zh','not_installed',6,'2004-01-20 12:22:17','2004-01-20 12:22:17','',NULL);
INSERT INTO `object_data` VALUES (94,'typ','tst','Test object',-1,'2004-02-18 21:17:40','2004-02-18 21:17:40','',NULL);
INSERT INTO `object_data` VALUES (95,'typ','qpl','Question pool object',-1,'2004-02-18 21:17:40','2004-02-18 21:17:40','',NULL);
INSERT INTO `object_data` VALUES (99,'typ','recf','RecoveryFolder object',-1,'2004-03-09 18:13:16','2004-03-09 18:13:16','',NULL);
INSERT INTO `object_data` VALUES (100,'recf','__Restored Objects','Contains objects restored by recovery tool',-1,'2004-03-09 18:13:16','2004-03-09 18:13:16','',NULL);
INSERT INTO `object_data` VALUES (101,'typ','mep','Media pool object',-1,'2004-04-19 00:09:14','2004-04-19 00:09:14','',NULL);
INSERT INTO `object_data` VALUES (102,'typ','htlm','HTML LM object',-1,'2004-04-19 00:09:15','2004-04-19 00:09:15','',NULL);
INSERT INTO `object_data` VALUES (103,'typ','svy','Survey object',-1,'2004-05-15 01:18:59','2004-05-15 01:18:59','',NULL);
INSERT INTO `object_data` VALUES (104,'typ','spl','Question pool object (Survey)',-1,'2004-05-15 01:18:59','2004-05-15 01:18:59','',NULL);
INSERT INTO `object_data` VALUES (106,'typ','cals','Calendar Settings',-1,'2004-06-21 01:27:18','2004-06-21 01:27:18','',NULL);
INSERT INTO `object_data` VALUES (107,'cals','Calendar Settings','Configure Calendar Settings here',-1,'2004-06-21 01:27:18','2004-06-21 01:27:18','',NULL);
INSERT INTO `object_data` VALUES (108,'typ','trac','UserTracking object',-1,'2004-07-11 01:03:12','2004-07-11 01:03:12','',NULL);
INSERT INTO `object_data` VALUES (109,'trac','__User Tracking','System user tracking',-1,'2004-07-11 01:03:12','2004-07-11 01:03:12','',NULL);
INSERT INTO `object_data` VALUES (110,'rolt','il_crs_admin','Administrator template for course admins',-1,'2004-09-02 09:49:43','2016-12-16 15:00:10','',NULL);
INSERT INTO `object_data` VALUES (111,'rolt','il_crs_tutor','Tutor template for course tutors',-1,'2004-09-02 09:49:43','2016-12-16 15:03:56','',NULL);
INSERT INTO `object_data` VALUES (112,'rolt','il_crs_member','Member template for course members',-1,'2004-09-02 09:49:43','2016-12-16 15:00:53','',NULL);
INSERT INTO `object_data` VALUES (115,'typ','assf','AssessmentFolder object',-1,'2005-01-07 17:21:15','2005-01-07 17:21:15','',NULL);
INSERT INTO `object_data` VALUES (116,'assf','__Test&Assessment','Test&Assessment Administration',-1,'2005-01-07 17:21:15','2005-01-07 17:21:15','',NULL);
INSERT INTO `object_data` VALUES (117,'typ','stys','Style Settings',-1,'2005-03-02 08:59:01','2005-03-02 08:59:01','',NULL);
INSERT INTO `object_data` VALUES (118,'stys','System Style Settings','Manage system skin and style settings here',-1,'2005-03-02 08:59:01','2005-03-02 08:59:01','',NULL);
INSERT INTO `object_data` VALUES (121,'typ','crsg','Course grouping object',-1,'2005-03-02 08:59:02','2005-03-02 08:59:02','',NULL);
INSERT INTO `object_data` VALUES (122,'typ','webr','Link resource object',-1,'2005-03-13 22:41:38','2005-03-13 22:41:38','',NULL);
INSERT INTO `object_data` VALUES (123,'typ','seas','Search settings',-1,'2005-06-20 09:50:00','2005-06-20 09:50:00','',NULL);
INSERT INTO `object_data` VALUES (124,'seas','Search settings','Search settings',-1,'2005-06-20 09:50:00','2005-06-20 09:50:00','',NULL);
INSERT INTO `object_data` VALUES (125,'rolt','Local Administrator','Role template for local administrators.',6,'2005-07-20 15:33:13','2016-12-16 15:09:46','',NULL);
INSERT INTO `object_data` VALUES (127,'typ','extt','external tools settings',-1,'2005-07-20 18:10:04','2005-07-20 18:10:04','',NULL);
INSERT INTO `object_data` VALUES (128,'extt','External tools settings','Configuring external tools',-1,'2005-07-20 18:10:04','2005-07-20 18:10:04','',NULL);
INSERT INTO `object_data` VALUES (131,'rolt','il_crs_non_member','Non-member template for course object',-1,'2005-11-07 12:41:21','2015-12-22 15:35:30','',NULL);
INSERT INTO `object_data` VALUES (135,'typ','adve','Advanced editing object',-1,'2006-07-11 18:43:23','2006-07-11 18:43:23','',NULL);
INSERT INTO `object_data` VALUES (136,'adve','__AdvancedEditing','Advanced Editing',-1,'2006-07-11 18:43:23','2006-07-11 18:43:23','',NULL);
INSERT INTO `object_data` VALUES (137,'typ','ps','Privacy security settings',-1,'2007-02-26 17:58:49','2007-02-26 17:58:49','',NULL);
INSERT INTO `object_data` VALUES (138,'ps','__PrivacySecurity','Privacy and Security',-1,'2007-02-26 17:58:49','2007-02-26 17:58:49','',NULL);
INSERT INTO `object_data` VALUES (139,'typ','nwss','News settings',-1,'2007-02-26 17:58:50','2007-02-26 17:58:50','',NULL);
INSERT INTO `object_data` VALUES (140,'nwss','__NewsSettings','News Settings',-1,'2007-02-26 17:58:50','2007-02-26 17:58:50','',NULL);
INSERT INTO `object_data` VALUES (141,'typ','feed','External Feed',-1,'2007-02-26 17:58:50','2007-02-26 17:58:50','',NULL);
INSERT INTO `object_data` VALUES (142,'typ','mcst','Media Cast',-1,'2007-04-03 13:43:46','2007-04-03 13:43:46','',NULL);
INSERT INTO `object_data` VALUES (143,'typ','dshs','Dashboard Settings',-1,'2007-04-03 13:43:47','2007-04-03 13:43:47','',NULL);
INSERT INTO `object_data` VALUES (144,'dshs','__DashboardSettings','Dashboard Settings',-1,'2007-04-03 13:43:47','2007-04-03 13:43:47','',NULL);
INSERT INTO `object_data` VALUES (145,'typ','rcrs','Remote Course Object',-1,'2007-09-25 19:47:53','2007-09-25 19:47:53','',NULL);
INSERT INTO `object_data` VALUES (146,'typ','mds','Meta Data settings',-1,'2007-09-25 19:47:53','2007-09-25 19:47:53','',NULL);
INSERT INTO `object_data` VALUES (147,'mds','__MetaDataSettings','Meta Data Settings',-1,'2007-09-25 19:47:53','2007-09-25 19:47:53','',NULL);
INSERT INTO `object_data` VALUES (148,'rolt','il_frm_moderator','Moderator template for forum moderators',-1,'2007-11-27 14:43:12','2007-11-27 14:43:12','',NULL);
INSERT INTO `object_data` VALUES (149,'typ','cmps','Component settings',-1,'2008-06-02 16:08:54','2008-06-02 16:08:54','',NULL);
INSERT INTO `object_data` VALUES (150,'cmps','__ComponentSettings','Component Settings',-1,'2008-06-02 16:08:54','2008-06-02 16:08:54','',NULL);
INSERT INTO `object_data` VALUES (151,'typ','facs','File Access settings object',-1,'2008-06-02 16:08:55','2008-06-02 16:08:55','',NULL);
INSERT INTO `object_data` VALUES (152,'facs','Files','Settings for files and file handling',-1,'2008-06-02 16:08:55','2016-12-16 15:43:54','',NULL);
INSERT INTO `object_data` VALUES (153,'typ','svyf','Survey Settings',-1,'2008-06-02 16:08:55','2008-06-02 16:08:55','',NULL);
INSERT INTO `object_data` VALUES (154,'svyf','__SurveySettings','Survey Settings',-1,'2008-06-02 16:08:55','2008-06-02 16:08:55','',NULL);
INSERT INTO `object_data` VALUES (155,'typ','sess','Session object',-1,'2008-06-02 16:08:55','2008-06-02 16:08:55','',NULL);
INSERT INTO `object_data` VALUES (156,'typ','mcts','Mediacast settings',-1,'2008-06-02 16:08:56','2008-06-02 16:08:56','',NULL);
INSERT INTO `object_data` VALUES (157,'mcts','__MediacastSettings','Mediacast Settings',-1,'2008-06-02 16:08:56','2008-06-02 16:08:56','',NULL);
INSERT INTO `object_data` VALUES (158,'typ','wiki','Wiki',-1,'2008-06-02 16:08:57','2008-06-02 16:08:57','',NULL);
INSERT INTO `object_data` VALUES (159,'typ','crsr','Course Reference Object',-1,'2008-09-23 19:24:09','2008-09-23 19:24:09','',NULL);
INSERT INTO `object_data` VALUES (160,'typ','catr','Category Reference Object',-1,'2008-09-23 19:24:09','2008-09-23 19:24:09','',NULL);
INSERT INTO `object_data` VALUES (161,'typ','tags','Tagging settings',-1,'2008-09-23 19:24:09','2008-09-23 19:24:09','',NULL);
INSERT INTO `object_data` VALUES (162,'tags','__TaggingSettings','Tagging Settings',-1,'2008-09-23 19:24:09','2008-09-23 19:24:09','',NULL);
INSERT INTO `object_data` VALUES (163,'typ','cert','Certificate settings',-1,'2009-07-20 13:03:21','2009-07-20 13:03:21','',NULL);
INSERT INTO `object_data` VALUES (164,'cert','__CertificateSettings','Certificate Settings',-1,'2009-07-20 13:03:21','2009-07-20 13:03:21','',NULL);
INSERT INTO `object_data` VALUES (165,'typ','lrss','Learning resources settings',-1,'2009-07-20 13:03:21','2009-07-20 13:03:21','',NULL);
INSERT INTO `object_data` VALUES (166,'lrss','__LearningResourcesSettings','Learning Resources Settings',-1,'2009-07-20 13:03:21','2009-07-20 13:03:21','',NULL);
INSERT INTO `object_data` VALUES (167,'typ','accs','Accessibility settings',-1,'2009-07-20 13:07:28','2009-07-20 13:07:28','',NULL);
INSERT INTO `object_data` VALUES (168,'accs','__AccessibilitySettings','Accessibility Settings',-1,'2009-07-20 13:07:28','2009-07-20 13:07:28','',NULL);
INSERT INTO `object_data` VALUES (169,'typ','mobs','Media Object/Pool settings',-1,'2009-07-20 13:08:42','2009-07-20 13:08:42','',NULL);
INSERT INTO `object_data` VALUES (170,'mobs','__MediaObjectSettings','Media Object/Pool Settings',-1,'2009-07-20 13:08:42','2009-07-20 13:08:42','',NULL);
INSERT INTO `object_data` VALUES (171,'typ','frma','Forum administration',-1,'2010-07-19 16:42:55','2010-07-19 16:42:55','',NULL);
INSERT INTO `object_data` VALUES (172,'frma','__ForumAdministration','Forum Administration',-1,'2010-07-19 16:42:55','2010-07-19 16:42:55','',NULL);
INSERT INTO `object_data` VALUES (173,'typ','book','Booking Manager',-1,'2010-07-19 16:43:10','2010-07-19 16:43:10','',NULL);
INSERT INTO `object_data` VALUES (174,'typ','skmg','Skill Management',-1,'2011-08-07 11:39:00','2011-08-07 11:39:00','',NULL);
INSERT INTO `object_data` VALUES (175,'skmg','__SkillManagement','Skill Management',-1,'2011-08-07 11:39:00','2011-08-07 11:39:00','',NULL);
INSERT INTO `object_data` VALUES (176,'typ','blga','Blog administration',-1,'2011-08-07 11:39:04','2011-08-07 11:39:04','',NULL);
INSERT INTO `object_data` VALUES (177,'blga','__BlogAdministration','Blog Administration',-1,'2011-08-07 11:39:04','2011-08-07 11:39:04','',NULL);
INSERT INTO `object_data` VALUES (178,'typ','prfa','Portfolio administration',-1,'2011-08-07 11:39:04','2011-08-07 11:39:04','',NULL);
INSERT INTO `object_data` VALUES (179,'prfa','__PortfolioAdministration','Portfolio Administration',-1,'2011-08-07 11:39:04','2011-08-07 11:39:04','',NULL);
INSERT INTO `object_data` VALUES (180,'typ','chtr','Chatroom Object',-1,'2011-08-07 11:39:06','2011-08-07 11:39:06','',NULL);
INSERT INTO `object_data` VALUES (181,'chta','Chatroom Admin','Chatroom General Settings',-1,'2011-08-07 11:39:06','2011-08-07 11:39:06','',NULL);
INSERT INTO `object_data` VALUES (182,'typ','chta','Chatroom Administration Type',-1,'2011-08-07 11:39:08','2011-08-07 11:39:08','',NULL);
INSERT INTO `object_data` VALUES (183,'typ','otpl','Object Template administration',-1,'2011-10-04 16:53:24','2011-10-04 16:53:24','',NULL);
INSERT INTO `object_data` VALUES (184,'otpl','__ObjectTemplateAdministration','Object Template Administration',-1,'2011-10-04 16:53:24','2011-10-04 16:53:24','',NULL);
INSERT INTO `object_data` VALUES (185,'chtr','Public Chatroom','Public Chatroom',-1,'2011-10-04 16:53:25','2011-10-04 16:53:25','',NULL);
INSERT INTO `object_data` VALUES (186,'rolf','185','(ref_id )',-1,'2011-10-04 16:53:25','2011-10-04 16:53:25','',NULL);
INSERT INTO `object_data` VALUES (187,'role','il_chat_moderator_46','Moderator of chat obj_no.185',-1,'2011-10-04 16:53:25','2011-10-04 16:53:25','',NULL);
INSERT INTO `object_data` VALUES (188,'rolt','il_chat_moderator','Moderator template for chat moderators',-1,'2011-10-04 16:53:25','2011-10-04 16:53:25','',NULL);
INSERT INTO `object_data` VALUES (189,'typ','blog','Blog Object',-1,'2012-09-04 14:25:29','2012-09-04 14:25:29','',NULL);
INSERT INTO `object_data` VALUES (190,'typ','dcl','Data Collection Object',-1,'2012-09-04 14:25:30','2012-09-04 14:25:30','',NULL);
INSERT INTO `object_data` VALUES (191,'typ','poll','Poll Object',-1,'2012-09-04 14:25:41','2012-09-04 14:25:41','',NULL);
INSERT INTO `object_data` VALUES (192,'typ','hlps','Help Settings',-1,'2012-09-04 14:25:48','2012-09-04 14:25:48','',NULL);
INSERT INTO `object_data` VALUES (193,'hlps','HelpSettings','Help Settings',-1,'2012-09-04 14:25:48','2012-09-04 14:25:48','',NULL);
INSERT INTO `object_data` VALUES (194,'typ','itgr','Item Group',-1,'2012-09-04 14:25:52','2012-09-04 14:25:52','',NULL);
INSERT INTO `object_data` VALUES (195,'typ','rcat','Remote Category Object',-1,'2012-09-04 14:26:14','2012-09-04 14:26:14','',NULL);
INSERT INTO `object_data` VALUES (196,'typ','rwik','Remote Wiki Object',-1,'2012-09-04 14:26:14','2012-09-04 14:26:14','',NULL);
INSERT INTO `object_data` VALUES (197,'typ','rlm','Remote Learning Module Object',-1,'2012-09-04 14:26:15','2012-09-04 14:26:15','',NULL);
INSERT INTO `object_data` VALUES (198,'typ','rglo','Remote Glossary Object',-1,'2012-09-04 14:26:15','2012-09-04 14:26:15','',NULL);
INSERT INTO `object_data` VALUES (199,'typ','rfil','Remote File Object',-1,'2012-09-04 14:26:16','2012-09-04 14:26:16','',NULL);
INSERT INTO `object_data` VALUES (200,'typ','rgrp','Remote Group Object',-1,'2012-09-04 14:26:16','2012-09-04 14:26:16','',NULL);
INSERT INTO `object_data` VALUES (201,'typ','rtst','Remote Test Object',-1,'2012-09-04 14:26:17','2012-09-04 14:26:17','',NULL);
INSERT INTO `object_data` VALUES (202,'rolt','il_blog_contributor','Contributor template for blogs',-1,'2012-10-27 19:30:37','2012-10-27 19:30:37','',NULL);
INSERT INTO `object_data` VALUES (203,'typ','ecss','ECS Administration',-1,'2012-11-23 17:12:57','2012-11-23 17:12:57','',NULL);
INSERT INTO `object_data` VALUES (204,'ecss','__ECSSettings','ECS Administration',-1,'2012-11-23 17:12:57','2012-11-23 17:12:57','',NULL);
INSERT INTO `object_data` VALUES (205,'typ','tos','Terms of Service',-1,'2013-10-11 18:59:35','2013-10-11 18:59:35','',NULL);
INSERT INTO `object_data` VALUES (206,'tos','Terms of Service','Terms of Service: Settings',-1,'2013-10-11 18:59:35','2013-10-11 18:59:35','',NULL);
INSERT INTO `object_data` VALUES (207,'typ','bibl','Bibliographic Object',-1,'2013-10-11 18:59:37','2013-10-11 18:59:37','',NULL);
INSERT INTO `object_data` VALUES (208,'typ','sysc','System Check',-1,'2013-10-11 18:59:40','2013-10-11 18:59:40','',NULL);
INSERT INTO `object_data` VALUES (209,'sysc','System Check','System Check',-1,'2013-10-11 18:59:40','2013-10-11 18:59:40','',NULL);
INSERT INTO `object_data` VALUES (210,'typ','cld','Cloud Folder',-1,'2013-10-11 18:59:40','2013-10-11 18:59:40','',NULL);
INSERT INTO `object_data` VALUES (211,'typ','reps','Repository Settings',-1,'2013-10-11 18:59:42','2013-10-11 18:59:42','',NULL);
INSERT INTO `object_data` VALUES (212,'reps','Repository Settings','Repository Settings',-1,'2013-10-11 18:59:42','2013-10-11 18:59:42','',NULL);
INSERT INTO `object_data` VALUES (213,'typ','crss','Course Settings',-1,'2013-10-11 18:59:42','2013-10-11 18:59:42','',NULL);
INSERT INTO `object_data` VALUES (214,'crss','Course Settings','Course Settings',-1,'2013-10-11 18:59:42','2013-10-11 18:59:42','',NULL);
INSERT INTO `object_data` VALUES (215,'typ','grps','Group Settings',-1,'2013-10-11 18:59:42','2013-10-11 18:59:42','',NULL);
INSERT INTO `object_data` VALUES (216,'grps','Group Settings','Group Settings',-1,'2013-10-11 18:59:42','2013-10-11 18:59:42','',NULL);
INSERT INTO `object_data` VALUES (217,'typ','wbrs','WebResource Settings',-1,'2013-10-11 18:59:42','2013-10-11 18:59:42','',NULL);
INSERT INTO `object_data` VALUES (218,'wbrs','WebResource Settings','WebResource Settings',-1,'2013-10-11 18:59:42','2013-10-11 18:59:42','',NULL);
INSERT INTO `object_data` VALUES (219,'typ','prtt','Portfolio Template Object',-1,'2013-10-11 19:04:51','2013-10-11 19:04:51','',NULL);
INSERT INTO `object_data` VALUES (220,'typ','orgu','Organisational Unit',-1,'2013-10-11 19:04:52','2013-10-11 19:04:52','',NULL);
INSERT INTO `object_data` VALUES (221,'orgu','__OrgUnitAdministration','Organisationsal Units',-1,'2013-10-11 19:04:52','2013-10-11 19:04:52','',NULL);
INSERT INTO `object_data` VALUES (222,'rolt','il_orgu_superior','OrgUnit Superior Role Template',-1,'2013-10-11 19:04:52','2013-10-11 19:04:52','',NULL);
INSERT INTO `object_data` VALUES (223,'typ','wiks','Wiki Settings',-1,'2013-11-06 17:13:57','2013-11-06 17:13:57','',NULL);
INSERT INTO `object_data` VALUES (224,'wiks','Wiki Settings','Wiki Settings',-1,'2013-11-06 17:13:57','2013-11-06 17:13:57','',NULL);
INSERT INTO `object_data` VALUES (225,'typ','excs','Exercise Settings',-1,'2014-09-23 21:48:42','2014-09-23 21:48:42','',NULL);
INSERT INTO `object_data` VALUES (226,'excs','Exercise Settings','Exercise Settings',-1,'2014-09-23 21:48:42','2014-09-23 21:48:42','',NULL);
INSERT INTO `object_data` VALUES (227,'typ','taxs','Taxonomy Settings',-1,'2014-09-23 21:48:42','2014-09-23 21:48:42','',NULL);
INSERT INTO `object_data` VALUES (228,'taxs','Taxonomy Settings','Taxonomy Settings',-1,'2014-09-23 21:48:42','2014-09-23 21:48:42','',NULL);
INSERT INTO `object_data` VALUES (229,'typ','bibs','BibliographicAdmin',-1,'2014-09-23 21:48:55','2014-09-23 21:48:55','',NULL);
INSERT INTO `object_data` VALUES (230,'bibs','BibliographicAdmin','BibliographicAdmin',-1,'2014-09-23 21:48:55','2014-09-23 21:48:55','',NULL);
INSERT INTO `object_data` VALUES (231,'rolt','il_blog_editor','Editor template for blogs',-1,'2015-09-30 12:57:25','2015-12-22 15:28:30','',NULL);
INSERT INTO `object_data` VALUES (232,'typ','awra','Awareness Tool Administration',-1,'2015-09-30 12:57:29','2015-09-30 12:57:29','',NULL);
INSERT INTO `object_data` VALUES (233,'awra','__AwarenessToolAdministration','Awareness Tool Administration',-1,'2015-09-30 12:57:29','2015-09-30 12:57:29','',NULL);
INSERT INTO `object_data` VALUES (234,'typ','logs','Logging Administration',-1,'2015-09-30 12:57:31','2015-09-30 12:57:31','',NULL);
INSERT INTO `object_data` VALUES (235,'logs','__LoggingSettings','Logging Administration',-1,'2015-09-30 12:57:31','2015-09-30 12:57:31','',NULL);
INSERT INTO `object_data` VALUES (236,'typ','prg','StudyProgramme',-1,'2015-09-30 12:57:34','2015-09-30 12:57:34','',NULL);
INSERT INTO `object_data` VALUES (237,'typ','prgs','StudyProgrammeAdmin',-1,'2015-09-30 12:57:35','2015-09-30 12:57:35','',NULL);
INSERT INTO `object_data` VALUES (238,'prgs','StudyProgrammeAdmin','StudyProgrammeAdmin',-1,'2015-09-30 12:57:35','2015-09-30 12:57:35','',NULL);
INSERT INTO `object_data` VALUES (239,'typ','cadm','Contact',-1,'2015-11-17 15:20:05','2015-11-17 15:20:05','',NULL);
INSERT INTO `object_data` VALUES (240,'cadm','Contact','Contact',-1,'2015-11-17 15:20:05','2015-11-17 15:20:05','',NULL);
INSERT INTO `object_data` VALUES (241,'lng','ka','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (242,'lng','ar','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (243,'lng','bg','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (244,'lng','sq','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (245,'lng','ro','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (246,'lng','sk','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (247,'lng','da','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (248,'lng','hu','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (249,'lng','uk','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (250,'lng','fa','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (251,'lng','sr','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (252,'lng','pt','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (253,'lng','ja','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (254,'lng','vi','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (255,'lng','ru','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (256,'lng','et','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (257,'lng','lt','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (258,'lng','cs','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (259,'lng','tr','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (260,'lng','el','not_installed',6,'2015-12-22 14:32:40','2015-12-22 14:32:40','',NULL);
INSERT INTO `object_data` VALUES (261,'typ','grpr','Group Reference Object',-1,'2016-09-02 13:26:19','2016-09-02 13:26:19','',NULL);
INSERT INTO `object_data` VALUES (262,'typ','bdga','Badge Settings',-1,'2016-09-02 13:26:21','2016-09-02 13:26:21','',NULL);
INSERT INTO `object_data` VALUES (263,'bdga','Badge Settings','Badge Settings',-1,'2016-09-02 13:26:21','2016-09-02 13:26:21','',NULL);
INSERT INTO `object_data` VALUES (264,'typ','wfe','WorkflowEngine',-1,'2016-09-02 13:33:13','2016-09-02 13:33:13','',NULL);
INSERT INTO `object_data` VALUES (265,'wfe','WorkflowEngine','WorkflowEngine',-1,'2016-09-02 13:33:13','2016-09-02 13:33:13','',NULL);
INSERT INTO `object_data` VALUES (266,'typ','iass','Individual Assessment',-1,'2016-09-02 13:33:17','2016-09-02 13:33:17','',NULL);
INSERT INTO `object_data` VALUES (267,'rolt','il_iass_member','Member of a manual assessment object',-1,'2016-09-02 13:33:17','2016-09-02 13:33:17','',NULL);
INSERT INTO `object_data` VALUES (268,'rolt','il_sess_participant','Session participant template',-1,'2018-01-25 08:42:46','2018-01-25 08:42:46',NULL,NULL);
INSERT INTO `object_data` VALUES (269,'rolt','il_sess_status_closed','Closed session template',0,'2018-01-25 08:42:46','2018-01-25 08:42:46',NULL,NULL);
INSERT INTO `object_data` VALUES (270,'typ','pdfg','PDFGeneration',-1,'2018-01-25 08:42:49','2018-01-25 08:42:49',NULL,NULL);
INSERT INTO `object_data` VALUES (271,'pdfg','PDFGeneration','PDFGeneration',-1,'2018-01-25 08:42:49','2018-01-25 08:42:49',NULL,NULL);
INSERT INTO `object_data` VALUES (272,'typ','ltis','LTI Settings',-1,'2018-01-25 08:42:57','2018-01-25 08:42:57',NULL,NULL);
INSERT INTO `object_data` VALUES (273,'ltis','LTI Settings','LTI Settings',-1,'2018-01-25 08:42:57','2018-01-25 08:42:57',NULL,NULL);
INSERT INTO `object_data` VALUES (274,'typ','copa','Content Page Object',-1,'2020-11-20 17:12:55','2020-11-20 17:12:55',NULL,NULL);
INSERT INTO `object_data` VALUES (275,'typ','mme','Main Menu',-1,'2020-11-20 17:13:00','2020-11-20 17:13:00',NULL,NULL);
INSERT INTO `object_data` VALUES (276,'mme','Main Menu','Main Menu',-1,'2020-11-20 17:13:00','2020-11-20 17:13:00',NULL,NULL);
INSERT INTO `object_data` VALUES (277,'typ','lso','Learning Sequence',-1,'2020-11-20 17:13:02','2020-11-20 17:13:02',NULL,NULL);
INSERT INTO `object_data` VALUES (278,'rolt','il_lso_admin','Admin template for learning sequences',-1,'2020-11-20 17:13:02','2020-11-20 17:13:02',NULL,NULL);
INSERT INTO `object_data` VALUES (279,'rolt','il_lso_member','Member template for learning sequences',-1,'2020-11-20 17:13:02','2020-11-20 17:13:02',NULL,NULL);
INSERT INTO `object_data` VALUES (280,'typ','lti','LTI Consumer Object',-1,'2020-11-20 17:13:08','2020-11-20 17:13:08',NULL,NULL);
INSERT INTO `object_data` VALUES (281,'typ','cmix','cmi5/xAPI Object',-1,'2020-11-20 17:13:08','2020-11-20 17:13:08',NULL,NULL);
INSERT INTO `object_data` VALUES (282,'typ','cmis','cmi5/xAPI Administration',-1,'2020-11-20 17:13:08','2020-11-20 17:13:08',NULL,NULL);
INSERT INTO `object_data` VALUES (283,'cmis','cmi5/xAPI Administration','cmi5/xAPI Administration',-1,'2020-11-20 17:13:08','2020-11-20 17:13:08',NULL,NULL);
INSERT INTO `object_data` VALUES (284,'typ','nots','Notes Settings',-1,'2020-11-20 17:13:10','2020-11-20 17:13:10',NULL,NULL);
INSERT INTO `object_data` VALUES (285,'nots','Notes Settings','Notes Settings',-1,'2020-11-20 17:13:10','2020-11-20 17:13:10',NULL,NULL);
INSERT INTO `object_data` VALUES (286,'typ','coms','Comments Settings',-1,'2020-11-20 17:13:10','2020-11-20 17:13:10',NULL,NULL);
INSERT INTO `object_data` VALUES (287,'coms','Comments Settings','Comments Settings',-1,'2020-11-20 17:13:10','2020-11-20 17:13:10',NULL,NULL);
INSERT INTO `object_data` VALUES (288,'typ','lhts','Learning History Settings',-1,'2020-11-20 17:13:10','2020-11-20 17:13:10',NULL,NULL);
INSERT INTO `object_data` VALUES (289,'lhts','Learning History Settings','Learning History Settings',-1,'2020-11-20 17:13:10','2020-11-20 17:13:10',NULL,NULL);
INSERT INTO `object_data` VALUES (290,'typ','prss','Personal Resources Settings',-1,'2020-11-20 17:13:10','2020-11-20 17:13:10',NULL,NULL);
INSERT INTO `object_data` VALUES (291,'prss','Personal Resources Settings','Personal Resources Settings',-1,'2020-11-20 17:13:10','2020-11-20 17:13:10',NULL,NULL);
INSERT INTO `object_data` VALUES (292,'typ','prgr','Study Programme Reference',-1,'2020-11-20 17:13:12','2020-11-20 17:13:12',NULL,NULL);
INSERT INTO `object_data` VALUES (293,'typ','lsos','LearningSequenceAdmin',-1,'2020-11-20 17:13:14','2020-11-20 17:13:14',NULL,NULL);
INSERT INTO `object_data` VALUES (294,'lsos','LearningSequenceAdmin','LearningSequenceAdmin',-1,'2020-11-20 17:13:14','2020-11-20 17:13:14',NULL,NULL);
INSERT INTO `object_data` VALUES (295,'typ','cpad','ContentPageAdministration',-1,'2020-11-20 17:13:15','2020-11-20 17:13:15',NULL,NULL);
INSERT INTO `object_data` VALUES (296,'cpad','ContentPageAdministration','ContentPageAdministration',-1,'2020-11-20 17:13:15','2020-11-20 17:13:15',NULL,NULL);
INSERT INTO `object_data` VALUES (297,'typ','fils','File Services',-1,'2020-11-20 17:13:17','2020-11-20 17:13:17',NULL,NULL);
INSERT INTO `object_data` VALUES (298,'fils','File Services','File Services',-1,'2020-11-20 17:13:17','2020-11-20 17:13:17',NULL,NULL);
INSERT INTO `object_data` VALUES (299,'typ','wbdv','WebDAV',-1,'2020-11-20 17:13:17','2020-11-20 17:13:17',NULL,NULL);
INSERT INTO `object_data` VALUES (300,'wbdv','WebDAV','WebDAV',-1,'2020-11-20 17:13:17','2020-11-20 17:13:17',NULL,NULL);
INSERT INTO `object_data` VALUES (301,'typ','adn','Administrative Notifications',-1,'2020-11-20 17:13:17','2020-11-20 17:13:17',NULL,NULL);
INSERT INTO `object_data` VALUES (302,'adn','Administrative Notifications','Administrative Notifications',-1,'2020-11-20 17:13:17','2020-11-20 17:13:17',NULL,NULL);

--
-- Table structure for table `object_data_del`
--

CREATE TABLE `object_data_del` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `type` char(4) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `object_data_del`
--


--
-- Table structure for table `object_data_seq`
--

CREATE TABLE `object_data_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=303;

--
-- Dumping data for table `object_data_seq`
--

INSERT INTO `object_data_seq` VALUES (302);

--
-- Table structure for table `object_description`
--

CREATE TABLE `object_description` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `description` longtext DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `object_description`
--

INSERT INTO `object_description` VALUES (152,'Settings for files and file handling');

--
-- Table structure for table `object_reference`
--

CREATE TABLE `object_reference` (
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `deleted` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT 0,
  PRIMARY KEY (`ref_id`),
  KEY `i1_idx` (`obj_id`),
  KEY `i2_idx` (`deleted`)
) ;

--
-- Dumping data for table `object_reference`
--

INSERT INTO `object_reference` VALUES (1,1,NULL,0);
INSERT INTO `object_reference` VALUES (7,7,NULL,0);
INSERT INTO `object_reference` VALUES (8,8,NULL,0);
INSERT INTO `object_reference` VALUES (9,9,NULL,0);
INSERT INTO `object_reference` VALUES (10,10,NULL,0);
INSERT INTO `object_reference` VALUES (11,11,NULL,0);
INSERT INTO `object_reference` VALUES (12,12,NULL,0);
INSERT INTO `object_reference` VALUES (14,98,NULL,0);
INSERT INTO `object_reference` VALUES (15,100,NULL,0);
INSERT INTO `object_reference` VALUES (16,107,NULL,0);
INSERT INTO `object_reference` VALUES (17,109,NULL,0);
INSERT INTO `object_reference` VALUES (18,86,NULL,0);
INSERT INTO `object_reference` VALUES (20,116,NULL,0);
INSERT INTO `object_reference` VALUES (21,118,NULL,0);
INSERT INTO `object_reference` VALUES (22,124,NULL,0);
INSERT INTO `object_reference` VALUES (23,128,NULL,0);
INSERT INTO `object_reference` VALUES (26,136,NULL,0);
INSERT INTO `object_reference` VALUES (27,138,NULL,0);
INSERT INTO `object_reference` VALUES (28,140,NULL,0);
INSERT INTO `object_reference` VALUES (29,144,NULL,0);
INSERT INTO `object_reference` VALUES (30,147,NULL,0);
INSERT INTO `object_reference` VALUES (31,150,NULL,0);
INSERT INTO `object_reference` VALUES (32,152,NULL,0);
INSERT INTO `object_reference` VALUES (33,154,NULL,0);
INSERT INTO `object_reference` VALUES (34,157,NULL,0);
INSERT INTO `object_reference` VALUES (35,162,NULL,0);
INSERT INTO `object_reference` VALUES (36,164,NULL,0);
INSERT INTO `object_reference` VALUES (37,166,NULL,0);
INSERT INTO `object_reference` VALUES (38,168,NULL,0);
INSERT INTO `object_reference` VALUES (39,170,NULL,0);
INSERT INTO `object_reference` VALUES (40,172,NULL,0);
INSERT INTO `object_reference` VALUES (41,175,NULL,0);
INSERT INTO `object_reference` VALUES (42,177,NULL,0);
INSERT INTO `object_reference` VALUES (43,179,NULL,0);
INSERT INTO `object_reference` VALUES (44,181,NULL,0);
INSERT INTO `object_reference` VALUES (45,184,NULL,0);
INSERT INTO `object_reference` VALUES (46,185,NULL,0);
INSERT INTO `object_reference` VALUES (47,186,NULL,0);
INSERT INTO `object_reference` VALUES (48,193,NULL,0);
INSERT INTO `object_reference` VALUES (49,204,NULL,0);
INSERT INTO `object_reference` VALUES (50,206,NULL,0);
INSERT INTO `object_reference` VALUES (51,209,NULL,0);
INSERT INTO `object_reference` VALUES (52,212,NULL,0);
INSERT INTO `object_reference` VALUES (53,214,NULL,0);
INSERT INTO `object_reference` VALUES (54,216,NULL,0);
INSERT INTO `object_reference` VALUES (55,218,NULL,0);
INSERT INTO `object_reference` VALUES (56,221,NULL,0);
INSERT INTO `object_reference` VALUES (57,224,NULL,0);
INSERT INTO `object_reference` VALUES (58,226,NULL,0);
INSERT INTO `object_reference` VALUES (59,228,NULL,0);
INSERT INTO `object_reference` VALUES (60,230,NULL,0);
INSERT INTO `object_reference` VALUES (61,233,NULL,0);
INSERT INTO `object_reference` VALUES (62,235,NULL,0);
INSERT INTO `object_reference` VALUES (63,238,NULL,0);
INSERT INTO `object_reference` VALUES (64,240,NULL,0);
INSERT INTO `object_reference` VALUES (65,263,NULL,0);
INSERT INTO `object_reference` VALUES (66,265,NULL,0);
INSERT INTO `object_reference` VALUES (67,271,NULL,0);
INSERT INTO `object_reference` VALUES (68,273,NULL,0);
INSERT INTO `object_reference` VALUES (69,276,NULL,0);
INSERT INTO `object_reference` VALUES (70,283,NULL,0);
INSERT INTO `object_reference` VALUES (71,285,NULL,0);
INSERT INTO `object_reference` VALUES (72,287,NULL,0);
INSERT INTO `object_reference` VALUES (73,289,NULL,0);
INSERT INTO `object_reference` VALUES (74,291,NULL,0);
INSERT INTO `object_reference` VALUES (75,294,NULL,0);
INSERT INTO `object_reference` VALUES (76,296,NULL,0);
INSERT INTO `object_reference` VALUES (77,298,NULL,0);
INSERT INTO `object_reference` VALUES (78,300,NULL,0);
INSERT INTO `object_reference` VALUES (79,302,NULL,0);

--
-- Table structure for table `object_reference_seq`
--

CREATE TABLE `object_reference_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=80;

--
-- Dumping data for table `object_reference_seq`
--

INSERT INTO `object_reference_seq` VALUES (79);

--
-- Table structure for table `object_reference_ws`
--

CREATE TABLE `object_reference_ws` (
  `wsp_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`wsp_id`),
  KEY `i1_idx` (`obj_id`),
  KEY `i2_idx` (`deleted`)
) ;

--
-- Dumping data for table `object_reference_ws`
--


--
-- Table structure for table `object_reference_ws_seq`
--

CREATE TABLE `object_reference_ws_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `object_reference_ws_seq`
--


--
-- Table structure for table `object_translation`
--

CREATE TABLE `object_translation` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `lang_code` char(2) NOT NULL DEFAULT '',
  `lang_default` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`lang_code`)
) ;

--
-- Dumping data for table `object_translation`
--

INSERT INTO `object_translation` VALUES (9,'Open Source eLearning','','en',1);

--
-- Table structure for table `openid_provider`
--

CREATE TABLE `openid_provider` (
  `provider_id` int(11) NOT NULL DEFAULT 0,
  `enabled` tinyint(4) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `url` varchar(512) DEFAULT NULL,
  `image` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`provider_id`)
) ;

--
-- Dumping data for table `openid_provider`
--

INSERT INTO `openid_provider` VALUES (1,1,'MyOpenID','http://%s.myopenid.com',1);

--
-- Table structure for table `openid_provider_seq`
--

CREATE TABLE `openid_provider_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=2;

--
-- Dumping data for table `openid_provider_seq`
--

INSERT INTO `openid_provider_seq` VALUES (1);

--
-- Table structure for table `orgu_data`
--

CREATE TABLE `orgu_data` (
  `orgu_id` int(11) NOT NULL DEFAULT 0,
  `orgu_type_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`orgu_id`)
) ;

--
-- Dumping data for table `orgu_data`
--


--
-- Table structure for table `orgu_obj_pos_settings`
--

CREATE TABLE `orgu_obj_pos_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `orgu_obj_pos_settings`
--


--
-- Table structure for table `orgu_obj_type_settings`
--

CREATE TABLE `orgu_obj_type_settings` (
  `obj_type` varchar(10) NOT NULL,
  `active` tinyint(4) DEFAULT 0,
  `activation_default` tinyint(4) DEFAULT 0,
  `changeable` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`obj_type`)
) ;

--
-- Dumping data for table `orgu_obj_type_settings`
--


--
-- Table structure for table `orgu_path_storage`
--

CREATE TABLE `orgu_path_storage` (
  `ref_id` bigint(20) NOT NULL DEFAULT 0,
  `obj_id` bigint(20) DEFAULT NULL,
  `path` longtext DEFAULT NULL,
  PRIMARY KEY (`ref_id`)
) ;

--
-- Dumping data for table `orgu_path_storage`
--


--
-- Table structure for table `orgu_types`
--

CREATE TABLE `orgu_types` (
  `id` int(11) NOT NULL DEFAULT 0,
  `default_lang` varchar(4) NOT NULL DEFAULT '',
  `icon` varchar(256) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT 0,
  `create_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `orgu_types`
--


--
-- Table structure for table `orgu_types_adv_md_rec`
--

CREATE TABLE `orgu_types_adv_md_rec` (
  `type_id` int(11) NOT NULL DEFAULT 0,
  `rec_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`type_id`,`rec_id`)
) ;

--
-- Dumping data for table `orgu_types_adv_md_rec`
--


--
-- Table structure for table `orgu_types_seq`
--

CREATE TABLE `orgu_types_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `orgu_types_seq`
--


--
-- Table structure for table `orgu_types_trans`
--

CREATE TABLE `orgu_types_trans` (
  `orgu_type_id` int(11) NOT NULL DEFAULT 0,
  `lang` varchar(4) NOT NULL DEFAULT '',
  `member` varchar(32) NOT NULL DEFAULT '',
  `value` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`orgu_type_id`,`lang`,`member`)
) ;

--
-- Dumping data for table `orgu_types_trans`
--


--
-- Table structure for table `osc_activity`
--

CREATE TABLE `osc_activity` (
  `conversation_id` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `timestamp` bigint(20) NOT NULL DEFAULT 0,
  `is_closed` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`conversation_id`,`user_id`)
) ;

--
-- Dumping data for table `osc_activity`
--


--
-- Table structure for table `osc_conversation`
--

CREATE TABLE `osc_conversation` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `is_group` tinyint(4) NOT NULL DEFAULT 0,
  `participants` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `osc_conversation`
--


--
-- Table structure for table `osc_messages`
--

CREATE TABLE `osc_messages` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `conversation_id` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `message` longtext DEFAULT NULL,
  `timestamp` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`user_id`),
  KEY `i2_idx` (`conversation_id`),
  KEY `i3_idx` (`conversation_id`,`user_id`,`timestamp`)
) ;

--
-- Dumping data for table `osc_messages`
--


--
-- Table structure for table `page_anchor`
--

CREATE TABLE `page_anchor` (
  `page_parent_type` varchar(10) NOT NULL DEFAULT ' ',
  `page_id` int(11) NOT NULL DEFAULT 0,
  `anchor_name` varchar(120) NOT NULL DEFAULT ' ',
  `page_lang` varchar(2) NOT NULL DEFAULT '-',
  PRIMARY KEY (`page_parent_type`,`page_id`,`page_lang`,`anchor_name`)
) ;

--
-- Dumping data for table `page_anchor`
--


--
-- Table structure for table `page_editor_settings`
--

CREATE TABLE `page_editor_settings` (
  `settings_grp` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '',
  `value` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`settings_grp`,`name`)
) ;

--
-- Dumping data for table `page_editor_settings`
--

INSERT INTO `page_editor_settings` VALUES ('rep','active_acc','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_code','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_com','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_emp','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_fn','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_imp','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_quot','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_str','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_sub','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_sup','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_tex','1');
INSERT INTO `page_editor_settings` VALUES ('rep','active_xln','1');

--
-- Table structure for table `page_history`
--

CREATE TABLE `page_history` (
  `page_id` int(11) NOT NULL DEFAULT 0,
  `parent_type` varchar(4) NOT NULL DEFAULT ' ',
  `hdate` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `parent_id` int(11) DEFAULT NULL,
  `nr` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `ilias_version` varchar(20) DEFAULT NULL,
  `lang` varchar(2) NOT NULL DEFAULT '-',
  PRIMARY KEY (`page_id`,`parent_type`,`hdate`,`lang`),
  KEY `i1_idx` (`page_id`),
  KEY `i2_idx` (`parent_id`,`parent_type`,`hdate`)
) ;

--
-- Dumping data for table `page_history`
--


--
-- Table structure for table `page_layout`
--

CREATE TABLE `page_layout` (
  `layout_id` int(11) NOT NULL DEFAULT 0,
  `content` longtext DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(4) DEFAULT 0,
  `style_id` int(11) DEFAULT 0,
  `special_page` tinyint(4) DEFAULT 0,
  `mod_scorm` tinyint(4) DEFAULT 1,
  `mod_portfolio` tinyint(4) DEFAULT NULL,
  `mod_lm` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`layout_id`)
) ;

--
-- Dumping data for table `page_layout`
--

INSERT INTO `page_layout` VALUES (1,'','1A Simple text page with accompanying media','Example description',1,0,0,1,0,NULL);
INSERT INTO `page_layout` VALUES (2,'','1C Text page with accompanying media and test','',1,0,0,1,0,NULL);
INSERT INTO `page_layout` VALUES (3,'','1E Text page with accompanying media followed by test and text','',1,0,0,1,0,NULL);
INSERT INTO `page_layout` VALUES (4,'','2C Simple media page with accompanying text and test','',1,0,0,1,0,NULL);
INSERT INTO `page_layout` VALUES (5,'','7C Vertical component navigation page with media and text','',1,0,0,1,0,NULL);

--
-- Table structure for table `page_layout_seq`
--

CREATE TABLE `page_layout_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=6;

--
-- Dumping data for table `page_layout_seq`
--

INSERT INTO `page_layout_seq` VALUES (5);

--
-- Table structure for table `page_object`
--

CREATE TABLE `page_object` (
  `page_id` int(11) NOT NULL DEFAULT 0,
  `parent_id` int(11) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `parent_type` varchar(4) NOT NULL DEFAULT 'lm',
  `last_change_user` int(11) DEFAULT NULL,
  `view_cnt` int(11) DEFAULT 0,
  `last_change` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `create_user` int(11) DEFAULT NULL,
  `render_md5` varchar(32) DEFAULT NULL,
  `rendered_content` longtext DEFAULT NULL,
  `rendered_time` datetime DEFAULT NULL,
  `activation_start` datetime DEFAULT NULL,
  `activation_end` datetime DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `is_empty` tinyint(4) NOT NULL DEFAULT 0,
  `inactive_elements` tinyint(4) DEFAULT 0,
  `int_links` tinyint(4) DEFAULT 0,
  `show_activation_info` tinyint(4) NOT NULL DEFAULT 0,
  `lang` varchar(2) NOT NULL DEFAULT '-',
  `edit_lock_user` int(11) DEFAULT NULL,
  `edit_lock_ts` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`page_id`,`parent_type`,`lang`),
  KEY `i3_idx` (`parent_id`,`parent_type`,`last_change`)
) ;

--
-- Dumping data for table `page_object`
--

INSERT INTO `page_object` VALUES (1,NULL,'<PageObject></PageObject>','impr',6,0,'2016-12-16 15:25:17','2016-12-16 15:25:17',6,'52bee1212f8a154aac268a0c20b77437','<a class=\"small\" id=\"ilPageShowAdvContent\" style=\"display:none; text-align:right;\" href=\"#\"><span>{{{{{LV_show_adv}}}}}</span><span>{{{{{LV_hide_adv}}}}}</span></a><h1 class=\"ilc_page_title_PageTitle\">Legal Notice</h1><!--COPage-PageTop-->\n<div xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" style=\"clear:both;\"><!--Break--></div>\n','2016-12-16 15:25:17',NULL,NULL,1,0,0,0,0,'-',NULL,0);
INSERT INTO `page_object` VALUES (1,0,'<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"500px\"/></PageContent></TableData><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData> </TableRow></Table></PageContent></PageObject>','stys',0,0,NULL,NULL,0,'','',NULL,NULL,NULL,1,0,0,0,0,'-',0,0);
INSERT INTO `page_object` VALUES (2,0,'<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent><PageContent PCID=\"3f77eb1d8a478493d69b99d438fda8f\"><PlaceHolder ContentClass=\"Question\" Height=\"200px\"/></PageContent></TableData><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData> </TableRow></Table></PageContent></PageObject>','stys',0,0,NULL,NULL,0,'','',NULL,NULL,NULL,1,0,0,0,0,'-',0,0);
INSERT INTO `page_object` VALUES (3,0,'<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent><PageContent PCID=\"3f77eb1d8a478493d69b99d438fda8f\"><PlaceHolder ContentClass=\"Question\" Height=\"200px\"/></PageContent><PageContent PCID=\"9b77eb1d8a478197d69b99d938fea8f\"><PlaceHolder ContentClass=\"Text\" Height=\"200px\"/></PageContent></TableData><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData> </TableRow></Table></PageContent></PageObject>','stys',0,0,NULL,NULL,0,'','',NULL,NULL,NULL,1,0,0,0,0,'-',0,0);
INSERT INTO `page_object` VALUES (4,0,'<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent><PageContent PCID=\"3f77eb1d8a478493d69b99d438fda8f\"><PlaceHolder ContentClass=\"Question\" Height=\"200px\"/></PageContent></TableData></TableRow></Table></PageContent></PageObject>','stys',0,0,NULL,NULL,0,'','',NULL,NULL,NULL,1,0,0,0,0,'-',0,0);
INSERT INTO `page_object` VALUES (5,0,'<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"100%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent></TableData> </TableRow><TableRow PCID=\"efade08caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"100%\"><PageContent PCID=\"124d24457cbc90ea1bf1a1323d7c3b89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"dfade09caf9fd13e8c7012f29c9510be\"><TableData PCID=\"e4e417c08feebeafb1487e60a2e245a5\" Width=\"33%\"><PageContent PCID=\"3e77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Media\" Height=\"150px\"/></PageContent><PageContent PCID=\"4e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Text\" Height=\"250px\"/></PageContent></TableData><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a5\" Width=\"33%\"><PageContent PCID=\"3a77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Media\" Height=\"150px\"/></PageContent><PageContent PCID=\"4ea7eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Text\" Height=\"250px\"/></PageContent></TableData><TableData PCID=\"b4e417c08feebeafb1487e60a2e245a5\" Width=\"33%\"><PageContent PCID=\"3b77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Media\" Height=\"150px\"/></PageContent><PageContent PCID=\"4b77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Text\" Height=\"250px\"/></PageContent></TableData></TableRow></Table></PageContent></TableData></TableRow></Table></PageContent></PageObject>','stys',0,0,NULL,NULL,0,'','',NULL,NULL,NULL,1,0,0,0,0,'-',0,0);

--
-- Table structure for table `page_pc_usage`
--

CREATE TABLE `page_pc_usage` (
  `pc_type` varchar(30) NOT NULL DEFAULT '',
  `pc_id` int(11) NOT NULL DEFAULT 0,
  `usage_type` varchar(30) NOT NULL DEFAULT '',
  `usage_id` int(11) NOT NULL DEFAULT 0,
  `usage_hist_nr` int(11) NOT NULL DEFAULT 0,
  `usage_lang` varchar(2) NOT NULL DEFAULT '-',
  PRIMARY KEY (`pc_type`,`pc_id`,`usage_type`,`usage_id`,`usage_hist_nr`,`usage_lang`)
) ;

--
-- Dumping data for table `page_pc_usage`
--


--
-- Table structure for table `page_qst_answer`
--

CREATE TABLE `page_qst_answer` (
  `qst_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `try` tinyint(4) NOT NULL DEFAULT 0,
  `passed` tinyint(4) NOT NULL DEFAULT 0,
  `points` double NOT NULL DEFAULT 0,
  `unlocked` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`qst_id`,`user_id`)
) ;

--
-- Dumping data for table `page_qst_answer`
--


--
-- Table structure for table `page_question`
--

CREATE TABLE `page_question` (
  `page_parent_type` varchar(4) NOT NULL DEFAULT '',
  `page_id` int(11) NOT NULL DEFAULT 0,
  `question_id` int(11) NOT NULL DEFAULT 0,
  `page_lang` varchar(2) NOT NULL DEFAULT '-',
  PRIMARY KEY (`page_parent_type`,`page_id`,`question_id`,`page_lang`),
  KEY `i1_idx` (`page_parent_type`,`page_id`,`page_lang`),
  KEY `i2_idx` (`question_id`)
) ;

--
-- Dumping data for table `page_question`
--


--
-- Table structure for table `page_style_usage`
--

CREATE TABLE `page_style_usage` (
  `id` int(11) NOT NULL DEFAULT 0,
  `page_id` int(11) NOT NULL DEFAULT 0,
  `page_type` char(10) NOT NULL DEFAULT '',
  `page_nr` int(11) NOT NULL DEFAULT 0,
  `template` tinyint(4) NOT NULL DEFAULT 0,
  `stype` varchar(30) DEFAULT NULL,
  `sname` char(30) DEFAULT NULL,
  `page_lang` varchar(2) NOT NULL DEFAULT '-',
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`page_id`,`page_type`,`page_lang`,`page_nr`)
) ;

--
-- Dumping data for table `page_style_usage`
--


--
-- Table structure for table `page_style_usage_seq`
--

CREATE TABLE `page_style_usage_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `page_style_usage_seq`
--


--
-- Table structure for table `pdfgen_conf`
--

CREATE TABLE `pdfgen_conf` (
  `conf_id` int(11) NOT NULL,
  `renderer` varchar(255) NOT NULL,
  `service` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `config` longtext DEFAULT NULL,
  PRIMARY KEY (`conf_id`)
) ;

--
-- Dumping data for table `pdfgen_conf`
--


--
-- Table structure for table `pdfgen_conf_seq`
--

CREATE TABLE `pdfgen_conf_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `pdfgen_conf_seq`
--


--
-- Table structure for table `pdfgen_map`
--

CREATE TABLE `pdfgen_map` (
  `map_id` int(11) NOT NULL,
  `service` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `preferred` varchar(255) NOT NULL,
  `selected` varchar(255) NOT NULL,
  PRIMARY KEY (`map_id`)
) ;

--
-- Dumping data for table `pdfgen_map`
--

INSERT INTO `pdfgen_map` VALUES (1,'Test','PrintViewOfQuestions','WkhtmlToPdf','WkhtmlToPdf');
INSERT INTO `pdfgen_map` VALUES (2,'Test','UserResult','WkhtmlToPdf','WkhtmlToPdf');
INSERT INTO `pdfgen_map` VALUES (3,'Wiki','ContentExport','WkhtmlToPdf','WkhtmlToPdf');
INSERT INTO `pdfgen_map` VALUES (4,'Portfolio','ContentExport','WkhtmlToPdf','WkhtmlToPdf');
INSERT INTO `pdfgen_map` VALUES (5,'Survey','Results','WkhtmlToPdf','WkhtmlToPdf');

--
-- Table structure for table `pdfgen_map_seq`
--

CREATE TABLE `pdfgen_map_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=6;

--
-- Dumping data for table `pdfgen_map_seq`
--

INSERT INTO `pdfgen_map_seq` VALUES (5);

--
-- Table structure for table `pdfgen_purposes`
--

CREATE TABLE `pdfgen_purposes` (
  `purpose_id` int(11) NOT NULL,
  `service` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  PRIMARY KEY (`purpose_id`)
) ;

--
-- Dumping data for table `pdfgen_purposes`
--

INSERT INTO `pdfgen_purposes` VALUES (1,'Test','PrintViewOfQuestions');
INSERT INTO `pdfgen_purposes` VALUES (2,'Test','UserResult');
INSERT INTO `pdfgen_purposes` VALUES (3,'Wiki','ContentExport');
INSERT INTO `pdfgen_purposes` VALUES (4,'Portfolio','ContentExport');
INSERT INTO `pdfgen_purposes` VALUES (5,'Survey','Results');

--
-- Table structure for table `pdfgen_purposes_seq`
--

CREATE TABLE `pdfgen_purposes_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=6;

--
-- Dumping data for table `pdfgen_purposes_seq`
--

INSERT INTO `pdfgen_purposes_seq` VALUES (5);

--
-- Table structure for table `pdfgen_renderer`
--

CREATE TABLE `pdfgen_renderer` (
  `renderer_id` int(11) NOT NULL,
  `renderer` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`renderer_id`)
) ;

--
-- Dumping data for table `pdfgen_renderer`
--

INSERT INTO `pdfgen_renderer` VALUES (1,'TCPDF','Services/PDFGeneration/classes/renderer/tcpdf/class.ilTCPDFRenderer.php');
INSERT INTO `pdfgen_renderer` VALUES (4,'WkhtmlToPdf','Services/PDFGeneration/classes/renderer/wkhtmltopdf/class.ilWkhtmlToPdfRenderer.php');

--
-- Table structure for table `pdfgen_renderer_avail`
--

CREATE TABLE `pdfgen_renderer_avail` (
  `availability_id` int(11) NOT NULL,
  `service` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `renderer` varchar(255) NOT NULL,
  PRIMARY KEY (`availability_id`)
) ;

--
-- Dumping data for table `pdfgen_renderer_avail`
--

INSERT INTO `pdfgen_renderer_avail` VALUES (3,'Test','PrintViewOfQuestions','TCPDF');
INSERT INTO `pdfgen_renderer_avail` VALUES (4,'Test','UserResult','TCPDF');
INSERT INTO `pdfgen_renderer_avail` VALUES (5,'Wiki','ContentExport','WkhtmlToPdf');
INSERT INTO `pdfgen_renderer_avail` VALUES (6,'Portfolio','ContentExport','WkhtmlToPdf');
INSERT INTO `pdfgen_renderer_avail` VALUES (9,'Test','UserResult','WkhtmlToPdf');
INSERT INTO `pdfgen_renderer_avail` VALUES (10,'Test','PrintViewOfQuestions','WkhtmlToPdf');
INSERT INTO `pdfgen_renderer_avail` VALUES (12,'Survey','Results','WkhtmlToPdf');

--
-- Table structure for table `pdfgen_renderer_avail_seq`
--

CREATE TABLE `pdfgen_renderer_avail_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=13;

--
-- Dumping data for table `pdfgen_renderer_avail_seq`
--

INSERT INTO `pdfgen_renderer_avail_seq` VALUES (12);

--
-- Table structure for table `pdfgen_renderer_seq`
--

CREATE TABLE `pdfgen_renderer_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=5;

--
-- Dumping data for table `pdfgen_renderer_seq`
--

INSERT INTO `pdfgen_renderer_seq` VALUES (4);

--
-- Table structure for table `personal_clipboard`
--

CREATE TABLE `personal_clipboard` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `item_id` int(11) NOT NULL DEFAULT 0,
  `type` char(4) NOT NULL DEFAULT '',
  `title` char(70) DEFAULT NULL,
  `insert_time` datetime DEFAULT NULL,
  `parent` int(11) NOT NULL DEFAULT 0,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`item_id`,`type`),
  KEY `it_idx` (`item_id`,`type`)
) ;

--
-- Dumping data for table `personal_clipboard`
--


--
-- Table structure for table `personal_pc_clipboard`
--

CREATE TABLE `personal_pc_clipboard` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `content` longtext DEFAULT NULL,
  `insert_time` datetime NOT NULL,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`insert_time`,`order_nr`),
  KEY `i1_idx` (`user_id`)
) ;

--
-- Dumping data for table `personal_pc_clipboard`
--


--
-- Table structure for table `pg_amd_page_list`
--

CREATE TABLE `pg_amd_page_list` (
  `id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `data` varchar(4000) DEFAULT NULL,
  `sdata` longtext DEFAULT NULL,
  PRIMARY KEY (`id`,`field_id`)
) ;

--
-- Dumping data for table `pg_amd_page_list`
--


--
-- Table structure for table `pg_amd_page_list_seq`
--

CREATE TABLE `pg_amd_page_list_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `pg_amd_page_list_seq`
--


--
-- Table structure for table `post_conditions`
--

CREATE TABLE `post_conditions` (
  `ref_id` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `condition_operator` varchar(32) NOT NULL,
  PRIMARY KEY (`ref_id`,`condition_operator`,`value`)
) ;

--
-- Dumping data for table `post_conditions`
--


--
-- Table structure for table `preview_data`
--

CREATE TABLE `preview_data` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `render_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `render_status` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `preview_data`
--


--
-- Table structure for table `prg_auto_content`
--

CREATE TABLE `prg_auto_content` (
  `prg_obj_id` int(11) NOT NULL,
  `cat_ref_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `last_usr_id` int(11) NOT NULL,
  `last_edited` datetime DEFAULT NULL,
  PRIMARY KEY (`prg_obj_id`,`cat_ref_id`)
) ;

--
-- Dumping data for table `prg_auto_content`
--


--
-- Table structure for table `prg_auto_membership`
--

CREATE TABLE `prg_auto_membership` (
  `prg_obj_id` int(11) NOT NULL,
  `source_type` varchar(8) NOT NULL,
  `source_id` int(11) NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT 0,
  `last_usr_id` int(11) NOT NULL,
  `last_edited` datetime DEFAULT NULL,
  PRIMARY KEY (`prg_obj_id`,`source_type`,`source_id`)
) ;

--
-- Dumping data for table `prg_auto_membership`
--


--
-- Table structure for table `prg_settings`
--

CREATE TABLE `prg_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `last_change` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `subtype_id` int(11) NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 0,
  `lp_mode` tinyint(4) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `deadline_period` int(11) NOT NULL DEFAULT 0,
  `deadline_date` datetime DEFAULT NULL,
  `vq_period` int(11) NOT NULL DEFAULT -1,
  `vq_date` datetime DEFAULT NULL,
  `vq_restart_period` int(11) NOT NULL DEFAULT -1,
  `rm_nr_by_usr_days` int(11) DEFAULT NULL,
  `proc_end_no_success` int(11) DEFAULT NULL,
  `send_re_assigned_mail` tinyint(4) DEFAULT 0,
  `send_info_to_re_assign_mail` tinyint(4) DEFAULT 0,
  `send_risky_to_fail_mail` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `prg_settings`
--


--
-- Table structure for table `prg_settings_seq`
--

CREATE TABLE `prg_settings_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `prg_settings_seq`
--


--
-- Table structure for table `prg_translations`
--

CREATE TABLE `prg_translations` (
  `id` int(11) NOT NULL DEFAULT 0,
  `prg_type_id` int(11) DEFAULT NULL,
  `lang` varchar(4) DEFAULT NULL,
  `member` varchar(32) DEFAULT NULL,
  `value` varchar(3500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `prg_translations`
--


--
-- Table structure for table `prg_translations_seq`
--

CREATE TABLE `prg_translations_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `prg_translations_seq`
--


--
-- Table structure for table `prg_type`
--

CREATE TABLE `prg_type` (
  `id` int(11) NOT NULL DEFAULT 0,
  `default_lang` varchar(4) DEFAULT NULL,
  `owner` int(11) DEFAULT NULL,
  `create_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `last_update` datetime DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `prg_type`
--


--
-- Table structure for table `prg_type_adv_md_rec`
--

CREATE TABLE `prg_type_adv_md_rec` (
  `id` int(11) NOT NULL DEFAULT 0,
  `type_id` int(11) DEFAULT NULL,
  `rec_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `prg_type_adv_md_rec`
--


--
-- Table structure for table `prg_type_adv_md_rec_seq`
--

CREATE TABLE `prg_type_adv_md_rec_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `prg_type_adv_md_rec_seq`
--


--
-- Table structure for table `prg_type_seq`
--

CREATE TABLE `prg_type_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `prg_type_seq`
--


--
-- Table structure for table `prg_usr_assignments`
--

CREATE TABLE `prg_usr_assignments` (
  `id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `root_prg_id` int(11) NOT NULL DEFAULT 0,
  `last_change` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `last_change_by` int(11) NOT NULL DEFAULT 0,
  `restart_date` datetime DEFAULT NULL,
  `restarted_assignment_id` int(11) NOT NULL DEFAULT -1,
  `restart_mail_send` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `prg_usr_assignments`
--


--
-- Table structure for table `prg_usr_assignments_seq`
--

CREATE TABLE `prg_usr_assignments_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `prg_usr_assignments_seq`
--


--
-- Table structure for table `prg_usr_progress`
--

CREATE TABLE `prg_usr_progress` (
  `id` int(11) NOT NULL DEFAULT 0,
  `assignment_id` int(11) NOT NULL DEFAULT 0,
  `prg_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 0,
  `points_cur` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `completion_by` int(11) DEFAULT NULL,
  `last_change` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `last_change_by` int(11) DEFAULT NULL,
  `deadline` varchar(15) DEFAULT NULL,
  `assignment_date` datetime DEFAULT NULL,
  `completion_date` datetime DEFAULT NULL,
  `vq_date` datetime DEFAULT NULL,
  `invalidated` tinyint(4) DEFAULT NULL,
  `sent_mail_risky_to_fail` datetime DEFAULT NULL,
  `individual` tinyint(4) NOT NULL DEFAULT 0,
  `sent_mail_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `con_idx` (`assignment_id`,`prg_id`,`usr_id`)
) ;

--
-- Dumping data for table `prg_usr_progress`
--


--
-- Table structure for table `prg_usr_progress_seq`
--

CREATE TABLE `prg_usr_progress_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `prg_usr_progress_seq`
--


--
-- Table structure for table `qpl_a_cloze`
--

CREATE TABLE `qpl_a_cloze` (
  `answer_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `shuffle` varchar(1) DEFAULT '1',
  `answertext` varchar(1000) DEFAULT NULL,
  `points` double NOT NULL DEFAULT 0,
  `aorder` smallint(6) NOT NULL DEFAULT 0,
  `gap_id` smallint(6) NOT NULL DEFAULT 0,
  `cloze_type` varchar(1) DEFAULT '0',
  `lowerlimit` varchar(20) DEFAULT '0',
  `upperlimit` varchar(20) DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `gap_size` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`answer_id`),
  KEY `i1_idx` (`question_fi`),
  KEY `i2_idx` (`gap_id`)
) ;

--
-- Dumping data for table `qpl_a_cloze`
--


--
-- Table structure for table `qpl_a_cloze_combi_res`
--

CREATE TABLE `qpl_a_cloze_combi_res` (
  `combination_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `gap_fi` int(11) NOT NULL DEFAULT 0,
  `answer` varchar(1000) DEFAULT NULL,
  `points` double DEFAULT NULL,
  `best_solution` tinyint(4) DEFAULT NULL,
  `row_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`combination_id`,`question_fi`,`gap_fi`,`row_id`),
  KEY `i1_idx` (`gap_fi`,`question_fi`)
) ;

--
-- Dumping data for table `qpl_a_cloze_combi_res`
--


--
-- Table structure for table `qpl_a_cloze_seq`
--

CREATE TABLE `qpl_a_cloze_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_cloze_seq`
--


--
-- Table structure for table `qpl_a_errortext`
--

CREATE TABLE `qpl_a_errortext` (
  `answer_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `text_wrong` varchar(150) NOT NULL DEFAULT '',
  `text_correct` varchar(150) DEFAULT NULL,
  `points` double NOT NULL DEFAULT 0,
  `sequence` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`answer_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_a_errortext`
--


--
-- Table structure for table `qpl_a_errortext_seq`
--

CREATE TABLE `qpl_a_errortext_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_errortext_seq`
--


--
-- Table structure for table `qpl_a_essay`
--

CREATE TABLE `qpl_a_essay` (
  `answer_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) DEFAULT NULL,
  `answertext` varchar(1000) DEFAULT NULL,
  `points` double DEFAULT NULL,
  PRIMARY KEY (`answer_id`)
) ;

--
-- Dumping data for table `qpl_a_essay`
--


--
-- Table structure for table `qpl_a_essay_seq`
--

CREATE TABLE `qpl_a_essay_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_essay_seq`
--


--
-- Table structure for table `qpl_a_imagemap`
--

CREATE TABLE `qpl_a_imagemap` (
  `answer_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `answertext` varchar(4000) DEFAULT NULL,
  `points` double NOT NULL DEFAULT 0,
  `aorder` smallint(6) NOT NULL DEFAULT 0,
  `coords` varchar(4000) DEFAULT NULL,
  `area` varchar(20) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `points_unchecked` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`answer_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_a_imagemap`
--


--
-- Table structure for table `qpl_a_imagemap_seq`
--

CREATE TABLE `qpl_a_imagemap_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_imagemap_seq`
--


--
-- Table structure for table `qpl_a_kprim`
--

CREATE TABLE `qpl_a_kprim` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `position` int(11) NOT NULL DEFAULT 0,
  `answertext` varchar(1000) DEFAULT NULL,
  `imagefile` varchar(255) DEFAULT NULL,
  `correctness` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_fi`,`position`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_a_kprim`
--


--
-- Table structure for table `qpl_a_lome`
--

CREATE TABLE `qpl_a_lome` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `gap_number` int(11) NOT NULL DEFAULT 0,
  `position` int(11) NOT NULL DEFAULT 0,
  `answer_text` varchar(1000) DEFAULT NULL,
  `points` double DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`question_fi`,`gap_number`,`position`)
) ;

--
-- Dumping data for table `qpl_a_lome`
--


--
-- Table structure for table `qpl_a_matching`
--

CREATE TABLE `qpl_a_matching` (
  `answer_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `term_fi` int(11) NOT NULL DEFAULT 0,
  `points` double NOT NULL DEFAULT 0,
  `definition_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`answer_id`),
  KEY `i1_idx` (`question_fi`),
  KEY `i2_idx` (`term_fi`)
) ;

--
-- Dumping data for table `qpl_a_matching`
--


--
-- Table structure for table `qpl_a_matching_seq`
--

CREATE TABLE `qpl_a_matching_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_matching_seq`
--


--
-- Table structure for table `qpl_a_mc`
--

CREATE TABLE `qpl_a_mc` (
  `answer_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `answertext` varchar(1000) DEFAULT NULL,
  `imagefile` varchar(1000) DEFAULT NULL,
  `points` double NOT NULL DEFAULT 0,
  `points_unchecked` double NOT NULL DEFAULT 0,
  `aorder` smallint(6) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`answer_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_a_mc`
--


--
-- Table structure for table `qpl_a_mc_seq`
--

CREATE TABLE `qpl_a_mc_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_mc_seq`
--


--
-- Table structure for table `qpl_a_mdef`
--

CREATE TABLE `qpl_a_mdef` (
  `def_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `definition` varchar(1000) DEFAULT NULL,
  `ident` int(11) NOT NULL DEFAULT 0,
  `picture` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`def_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_a_mdef`
--


--
-- Table structure for table `qpl_a_mdef_seq`
--

CREATE TABLE `qpl_a_mdef_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_mdef_seq`
--


--
-- Table structure for table `qpl_a_mterm`
--

CREATE TABLE `qpl_a_mterm` (
  `term_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `term` varchar(1000) DEFAULT NULL,
  `picture` varchar(1000) DEFAULT NULL,
  `ident` int(11) DEFAULT NULL,
  PRIMARY KEY (`term_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_a_mterm`
--


--
-- Table structure for table `qpl_a_mterm_seq`
--

CREATE TABLE `qpl_a_mterm_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_mterm_seq`
--


--
-- Table structure for table `qpl_a_ordering`
--

CREATE TABLE `qpl_a_ordering` (
  `answer_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `answertext` varchar(1000) DEFAULT NULL,
  `solution_key` smallint(6) NOT NULL DEFAULT 0,
  `random_id` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `depth` int(11) NOT NULL DEFAULT 0,
  `position` mediumint(9) DEFAULT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_a_ordering`
--


--
-- Table structure for table `qpl_a_ordering_seq`
--

CREATE TABLE `qpl_a_ordering_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_ordering_seq`
--


--
-- Table structure for table `qpl_a_sc`
--

CREATE TABLE `qpl_a_sc` (
  `answer_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `answertext` varchar(1000) DEFAULT NULL,
  `imagefile` varchar(1000) DEFAULT NULL,
  `points` double NOT NULL DEFAULT 0,
  `aorder` smallint(6) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`answer_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_a_sc`
--


--
-- Table structure for table `qpl_a_sc_seq`
--

CREATE TABLE `qpl_a_sc_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_sc_seq`
--


--
-- Table structure for table `qpl_a_textsubset`
--

CREATE TABLE `qpl_a_textsubset` (
  `answer_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `answertext` varchar(1000) DEFAULT NULL,
  `points` double NOT NULL DEFAULT 0,
  `aorder` smallint(6) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`answer_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_a_textsubset`
--


--
-- Table structure for table `qpl_a_textsubset_seq`
--

CREATE TABLE `qpl_a_textsubset_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_a_textsubset_seq`
--


--
-- Table structure for table `qpl_fb_generic`
--

CREATE TABLE `qpl_fb_generic` (
  `feedback_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `correctness` varchar(1) DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `feedback` longtext DEFAULT NULL,
  PRIMARY KEY (`feedback_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_fb_generic`
--


--
-- Table structure for table `qpl_fb_generic_seq`
--

CREATE TABLE `qpl_fb_generic_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_fb_generic_seq`
--


--
-- Table structure for table `qpl_fb_specific`
--

CREATE TABLE `qpl_fb_specific` (
  `feedback_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `answer` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `feedback` longtext DEFAULT NULL,
  `question` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`feedback_id`),
  UNIQUE KEY `con_idx` (`question_fi`,`question`,`answer`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_fb_specific`
--


--
-- Table structure for table `qpl_fb_specific_seq`
--

CREATE TABLE `qpl_fb_specific_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_fb_specific_seq`
--


--
-- Table structure for table `qpl_hint_tracking`
--

CREATE TABLE `qpl_hint_tracking` (
  `qhtr_track_id` int(11) NOT NULL DEFAULT 0,
  `qhtr_active_fi` int(11) NOT NULL DEFAULT 0,
  `qhtr_pass` int(11) NOT NULL DEFAULT 0,
  `qhtr_question_fi` int(11) NOT NULL DEFAULT 0,
  `qhtr_hint_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`qhtr_track_id`)
) ;

--
-- Dumping data for table `qpl_hint_tracking`
--


--
-- Table structure for table `qpl_hint_tracking_seq`
--

CREATE TABLE `qpl_hint_tracking_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_hint_tracking_seq`
--


--
-- Table structure for table `qpl_hints`
--

CREATE TABLE `qpl_hints` (
  `qht_hint_id` int(11) NOT NULL DEFAULT 0,
  `qht_question_fi` int(11) NOT NULL DEFAULT 0,
  `qht_hint_index` int(11) NOT NULL DEFAULT 0,
  `qht_hint_points` double NOT NULL DEFAULT 0,
  `qht_hint_text` longtext DEFAULT NULL,
  PRIMARY KEY (`qht_hint_id`)
) ;

--
-- Dumping data for table `qpl_hints`
--


--
-- Table structure for table `qpl_hints_seq`
--

CREATE TABLE `qpl_hints_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_hints_seq`
--


--
-- Table structure for table `qpl_num_range`
--

CREATE TABLE `qpl_num_range` (
  `range_id` int(11) NOT NULL DEFAULT 0,
  `lowerlimit` varchar(20) DEFAULT '0',
  `upperlimit` varchar(20) DEFAULT '0',
  `points` double NOT NULL DEFAULT 0,
  `aorder` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`range_id`),
  KEY `i6_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_num_range`
--


--
-- Table structure for table `qpl_num_range_seq`
--

CREATE TABLE `qpl_num_range_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_num_range_seq`
--


--
-- Table structure for table `qpl_qst_cloze`
--

CREATE TABLE `qpl_qst_cloze` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `textgap_rating` varchar(2) DEFAULT NULL,
  `identical_scoring` varchar(1) DEFAULT '1',
  `fixed_textlen` int(11) DEFAULT NULL,
  `cloze_text` longtext DEFAULT NULL,
  `feedback_mode` varchar(16) NOT NULL DEFAULT 'gapQuestion',
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_cloze`
--


--
-- Table structure for table `qpl_qst_errortext`
--

CREATE TABLE `qpl_qst_errortext` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `errortext` varchar(4000) NOT NULL DEFAULT '',
  `textsize` double NOT NULL DEFAULT 100,
  `points_wrong` double NOT NULL DEFAULT -1,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_errortext`
--


--
-- Table structure for table `qpl_qst_essay`
--

CREATE TABLE `qpl_qst_essay` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `maxnumofchars` int(11) NOT NULL DEFAULT 0,
  `keywords` varchar(4000) DEFAULT NULL,
  `textgap_rating` varchar(2) DEFAULT NULL,
  `matchcondition` smallint(6) NOT NULL DEFAULT 0,
  `keyword_relation` varchar(3) NOT NULL DEFAULT 'any',
  `word_cnt_enabled` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_essay`
--


--
-- Table structure for table `qpl_qst_fileupload`
--

CREATE TABLE `qpl_qst_fileupload` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `allowedextensions` varchar(255) DEFAULT NULL,
  `maxsize` double DEFAULT NULL,
  `compl_by_submission` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_fileupload`
--


--
-- Table structure for table `qpl_qst_flash`
--

CREATE TABLE `qpl_qst_flash` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `params` varchar(4000) DEFAULT NULL,
  `applet` varchar(150) DEFAULT NULL,
  `width` int(11) NOT NULL DEFAULT 550,
  `height` int(11) NOT NULL DEFAULT 400,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_flash`
--


--
-- Table structure for table `qpl_qst_horder`
--

CREATE TABLE `qpl_qst_horder` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `ordertext` varchar(2000) DEFAULT NULL,
  `textsize` double DEFAULT NULL,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_horder`
--


--
-- Table structure for table `qpl_qst_imagemap`
--

CREATE TABLE `qpl_qst_imagemap` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `image_file` varchar(100) DEFAULT NULL,
  `is_multiple_choice` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_imagemap`
--


--
-- Table structure for table `qpl_qst_javaapplet`
--

CREATE TABLE `qpl_qst_javaapplet` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `image_file` varchar(100) DEFAULT NULL,
  `params` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_javaapplet`
--


--
-- Table structure for table `qpl_qst_kprim`
--

CREATE TABLE `qpl_qst_kprim` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `shuffle_answers` tinyint(4) NOT NULL DEFAULT 0,
  `answer_type` varchar(16) NOT NULL DEFAULT 'singleLine',
  `thumb_size` int(11) DEFAULT NULL,
  `opt_label` varchar(32) NOT NULL DEFAULT 'right/wrong',
  `custom_true` varchar(255) DEFAULT NULL,
  `custom_false` varchar(255) DEFAULT NULL,
  `score_partsol` tinyint(4) NOT NULL DEFAULT 0,
  `feedback_setting` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_kprim`
--


--
-- Table structure for table `qpl_qst_lome`
--

CREATE TABLE `qpl_qst_lome` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `shuffle_answers` tinyint(4) NOT NULL DEFAULT 0,
  `answer_type` varchar(16) NOT NULL DEFAULT 'singleLine',
  `feedback_setting` int(11) NOT NULL DEFAULT 1,
  `long_menu_text` longtext DEFAULT NULL,
  `min_auto_complete` tinyint(4) DEFAULT 3,
  `identical_scoring` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_lome`
--


--
-- Table structure for table `qpl_qst_matching`
--

CREATE TABLE `qpl_qst_matching` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `shuffle` varchar(1) DEFAULT '1',
  `matching_type` varchar(1) DEFAULT '1',
  `thumb_geometry` int(11) NOT NULL DEFAULT 100,
  `matching_mode` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_matching`
--


--
-- Table structure for table `qpl_qst_mc`
--

CREATE TABLE `qpl_qst_mc` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `shuffle` varchar(1) DEFAULT '1',
  `allow_images` varchar(1) DEFAULT '0',
  `resize_images` varchar(1) DEFAULT '0',
  `thumb_size` smallint(6) DEFAULT NULL,
  `feedback_setting` tinyint(4) NOT NULL DEFAULT 1,
  `selection_limit` int(11) DEFAULT NULL,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_mc`
--


--
-- Table structure for table `qpl_qst_numeric`
--

CREATE TABLE `qpl_qst_numeric` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `maxnumofchars` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_numeric`
--


--
-- Table structure for table `qpl_qst_ordering`
--

CREATE TABLE `qpl_qst_ordering` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `ordering_type` varchar(1) DEFAULT '1',
  `thumb_geometry` int(11) NOT NULL DEFAULT 100,
  `element_height` int(11) DEFAULT NULL,
  `scoring_type` mediumint(9) NOT NULL DEFAULT 0,
  `reduced_points` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_ordering`
--


--
-- Table structure for table `qpl_qst_sc`
--

CREATE TABLE `qpl_qst_sc` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `shuffle` varchar(1) DEFAULT '1',
  `allow_images` varchar(1) DEFAULT '0',
  `resize_images` varchar(1) DEFAULT '0',
  `thumb_size` smallint(6) DEFAULT NULL,
  `feedback_setting` tinyint(4) DEFAULT 2,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_sc`
--


--
-- Table structure for table `qpl_qst_skl_assigns`
--

CREATE TABLE `qpl_qst_skl_assigns` (
  `obj_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `skill_base_fi` int(11) NOT NULL DEFAULT 0,
  `skill_tref_fi` int(11) NOT NULL DEFAULT 0,
  `skill_points` int(11) NOT NULL DEFAULT 0,
  `eval_mode` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`obj_fi`,`question_fi`,`skill_base_fi`,`skill_tref_fi`)
) ;

--
-- Dumping data for table `qpl_qst_skl_assigns`
--


--
-- Table structure for table `qpl_qst_skl_sol_expr`
--

CREATE TABLE `qpl_qst_skl_sol_expr` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `skill_base_fi` int(11) NOT NULL DEFAULT 0,
  `skill_tref_fi` int(11) NOT NULL DEFAULT 0,
  `order_index` int(11) NOT NULL DEFAULT 0,
  `expression` varchar(255) NOT NULL DEFAULT ' ',
  `points` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_fi`,`skill_base_fi`,`skill_tref_fi`,`order_index`)
) ;

--
-- Dumping data for table `qpl_qst_skl_sol_expr`
--


--
-- Table structure for table `qpl_qst_textsubset`
--

CREATE TABLE `qpl_qst_textsubset` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `textgap_rating` varchar(2) DEFAULT NULL,
  `correctanswers` int(11) DEFAULT 0,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `qpl_qst_textsubset`
--


--
-- Table structure for table `qpl_qst_type`
--

CREATE TABLE `qpl_qst_type` (
  `question_type_id` int(11) NOT NULL DEFAULT 0,
  `type_tag` varchar(35) DEFAULT NULL,
  `plugin` tinyint(4) NOT NULL DEFAULT 0,
  `plugin_name` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`question_type_id`)
) ;

--
-- Dumping data for table `qpl_qst_type`
--

INSERT INTO `qpl_qst_type` VALUES (1,'assSingleChoice',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (2,'assMultipleChoice',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (3,'assClozeTest',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (4,'assMatchingQuestion',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (5,'assOrderingQuestion',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (6,'assImagemapQuestion',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (7,'assJavaApplet',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (8,'assTextQuestion',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (9,'assNumeric',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (10,'assTextSubset',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (12,'assFlashQuestion',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (13,'assOrderingHorizontal',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (14,'assFileUpload',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (15,'assErrorText',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (16,'assFormulaQuestion',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (17,'assKprimChoice',0,NULL);
INSERT INTO `qpl_qst_type` VALUES (18,'assLongMenu',0,NULL);

--
-- Table structure for table `qpl_questionpool`
--

CREATE TABLE `qpl_questionpool` (
  `id_questionpool` int(11) NOT NULL DEFAULT 0,
  `obj_fi` int(11) NOT NULL DEFAULT 0,
  `isonline` varchar(1) DEFAULT '0',
  `questioncount` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `show_taxonomies` tinyint(4) NOT NULL DEFAULT 0,
  `nav_taxonomy` int(11) DEFAULT NULL,
  `skill_service` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id_questionpool`),
  KEY `i1_idx` (`obj_fi`)
) ;

--
-- Dumping data for table `qpl_questionpool`
--


--
-- Table structure for table `qpl_questionpool_seq`
--

CREATE TABLE `qpl_questionpool_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_questionpool_seq`
--


--
-- Table structure for table `qpl_questions`
--

CREATE TABLE `qpl_questions` (
  `question_id` int(11) NOT NULL DEFAULT 0,
  `question_type_fi` int(11) NOT NULL DEFAULT 0,
  `obj_fi` int(11) NOT NULL DEFAULT 0,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `author` varchar(50) DEFAULT NULL,
  `owner` int(11) NOT NULL DEFAULT 0,
  `working_time` varchar(8) DEFAULT '00:00:00',
  `points` double DEFAULT NULL,
  `complete` varchar(1) DEFAULT '1',
  `original_id` int(11) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `created` int(11) NOT NULL DEFAULT 0,
  `nr_of_tries` int(11) NOT NULL DEFAULT 0,
  `question_text` longtext DEFAULT NULL,
  `add_cont_edit_mode` varchar(16) DEFAULT NULL,
  `external_id` varchar(255) DEFAULT NULL,
  `lifecycle` varchar(16) DEFAULT 'draft',
  PRIMARY KEY (`question_id`),
  KEY `i1_idx` (`question_type_fi`),
  KEY `i2_idx` (`original_id`),
  KEY `i3_idx` (`obj_fi`),
  KEY `i4_idx` (`title`),
  KEY `i5_idx` (`owner`)
) ;

--
-- Dumping data for table `qpl_questions`
--


--
-- Table structure for table `qpl_questions_seq`
--

CREATE TABLE `qpl_questions_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_questions_seq`
--


--
-- Table structure for table `qpl_sol_sug`
--

CREATE TABLE `qpl_sol_sug` (
  `suggested_solution_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `internal_link` varchar(50) DEFAULT NULL,
  `import_id` varchar(50) DEFAULT NULL,
  `subquestion_index` int(11) NOT NULL DEFAULT 0,
  `type` varchar(32) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`suggested_solution_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `qpl_sol_sug`
--


--
-- Table structure for table `qpl_sol_sug_seq`
--

CREATE TABLE `qpl_sol_sug_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `qpl_sol_sug_seq`
--


--
-- Table structure for table `rbac_fa`
--

CREATE TABLE `rbac_fa` (
  `rol_id` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) NOT NULL DEFAULT 0,
  `assign` char(1) DEFAULT NULL,
  `protected` char(1) DEFAULT 'n',
  `blocked` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rol_id`,`parent`),
  KEY `i1_idx` (`parent`),
  KEY `i2_idx` (`assign`,`rol_id`),
  KEY `i3_idx` (`assign`,`parent`)
) ;

--
-- Dumping data for table `rbac_fa`
--

INSERT INTO `rbac_fa` VALUES (2,8,'y','y',0);
INSERT INTO `rbac_fa` VALUES (3,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (4,8,'y','n',0);
INSERT INTO `rbac_fa` VALUES (5,8,'y','n',0);
INSERT INTO `rbac_fa` VALUES (14,8,'y','n',0);
INSERT INTO `rbac_fa` VALUES (80,8,'n','y',0);
INSERT INTO `rbac_fa` VALUES (81,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (82,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (83,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (110,8,'n','y',0);
INSERT INTO `rbac_fa` VALUES (111,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (112,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (125,8,'n','y',0);
INSERT INTO `rbac_fa` VALUES (131,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (148,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (187,46,'y','n',0);
INSERT INTO `rbac_fa` VALUES (188,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (202,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (222,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (231,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (267,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (268,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (269,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (278,8,'n','n',0);
INSERT INTO `rbac_fa` VALUES (279,8,'n','n',0);

--
-- Table structure for table `rbac_log`
--

CREATE TABLE `rbac_log` (
  `log_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `created` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `action` tinyint(4) NOT NULL DEFAULT 0,
  `data` longtext DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `i1_idx` (`ref_id`),
  KEY `i2_idx` (`created`)
) ;

--
-- Dumping data for table `rbac_log`
--


--
-- Table structure for table `rbac_log_seq`
--

CREATE TABLE `rbac_log_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `rbac_log_seq`
--


--
-- Table structure for table `rbac_operations`
--

CREATE TABLE `rbac_operations` (
  `ops_id` int(11) NOT NULL DEFAULT 0,
  `operation` char(32) DEFAULT NULL,
  `description` char(255) DEFAULT NULL,
  `class` char(16) DEFAULT NULL,
  `op_order` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ops_id`),
  KEY `i1_idx` (`operation`)
) ;

--
-- Dumping data for table `rbac_operations`
--

INSERT INTO `rbac_operations` VALUES (1,'edit_permission','edit permissions','rbac',9000);
INSERT INTO `rbac_operations` VALUES (2,'visible','view object','general',1000);
INSERT INTO `rbac_operations` VALUES (3,'read','access object','general',2000);
INSERT INTO `rbac_operations` VALUES (4,'write','modify object','general',6000);
INSERT INTO `rbac_operations` VALUES (6,'delete','remove object','general',8000);
INSERT INTO `rbac_operations` VALUES (7,'join','join/subscribe','object',1200);
INSERT INTO `rbac_operations` VALUES (8,'leave','leave/unsubscribe','object',1400);
INSERT INTO `rbac_operations` VALUES (9,'add_reply','Reply to forum articles','object',3050);
INSERT INTO `rbac_operations` VALUES (10,'moderate_frm','delete forum articles','object',3750);
INSERT INTO `rbac_operations` VALUES (11,'smtp_mail','send external mail','object',210);
INSERT INTO `rbac_operations` VALUES (13,'create_usr','create new user account','create',9999);
INSERT INTO `rbac_operations` VALUES (14,'create_role','create new role definition','create',9999);
INSERT INTO `rbac_operations` VALUES (15,'create_rolt','create new role definition template','create',9999);
INSERT INTO `rbac_operations` VALUES (16,'create_cat','create new category','create',9999);
INSERT INTO `rbac_operations` VALUES (17,'create_grp','create new group','create',9999);
INSERT INTO `rbac_operations` VALUES (18,'create_frm','create new forum','create',9999);
INSERT INTO `rbac_operations` VALUES (19,'create_crs','create new course','create',9999);
INSERT INTO `rbac_operations` VALUES (20,'create_lm','create new learning module','create',9999);
INSERT INTO `rbac_operations` VALUES (21,'create_sahs','create new SCORM/AICC learning module','create',9999);
INSERT INTO `rbac_operations` VALUES (22,'create_glo','create new glossary','create',9999);
INSERT INTO `rbac_operations` VALUES (24,'create_exc','create new exercise','create',9999);
INSERT INTO `rbac_operations` VALUES (25,'create_file','upload new file','create',9999);
INSERT INTO `rbac_operations` VALUES (26,'create_fold','create new folder','create',9999);
INSERT INTO `rbac_operations` VALUES (27,'create_tst','create new test','create',9999);
INSERT INTO `rbac_operations` VALUES (28,'create_qpl','create new question pool','create',9999);
INSERT INTO `rbac_operations` VALUES (30,'internal_mail','users can use mail system','object',200);
INSERT INTO `rbac_operations` VALUES (31,'create_mep','create new media pool','create',9999);
INSERT INTO `rbac_operations` VALUES (32,'create_htlm','create new html learning module','create',9999);
INSERT INTO `rbac_operations` VALUES (40,'edit_userassignment','change userassignment of roles','object',2500);
INSERT INTO `rbac_operations` VALUES (41,'edit_roleassignment','change roleassignments of user accounts','object',2500);
INSERT INTO `rbac_operations` VALUES (42,'create_svy','create new survey','create',9999);
INSERT INTO `rbac_operations` VALUES (43,'create_spl','create new question pool (Survey)','create',9999);
INSERT INTO `rbac_operations` VALUES (45,'invite','invite','object',2600);
INSERT INTO `rbac_operations` VALUES (47,'cat_administrate_users','Administrate local user','object',7050);
INSERT INTO `rbac_operations` VALUES (48,'read_users','read local users','object',7000);
INSERT INTO `rbac_operations` VALUES (49,'push_desktop_items','Allow pushing desktop items','object',2400);
INSERT INTO `rbac_operations` VALUES (50,'create_webr','create web resource','create',9999);
INSERT INTO `rbac_operations` VALUES (51,'search','Allow using search','object',300);
INSERT INTO `rbac_operations` VALUES (52,'moderate','Moderate objects','object',3700);
INSERT INTO `rbac_operations` VALUES (55,'edit_learning_progress','edit learning progress','object',3600);
INSERT INTO `rbac_operations` VALUES (56,'tst_statistics','view the statistics of a test','object',7100);
INSERT INTO `rbac_operations` VALUES (57,'export_member_data','Export member data','object',0);
INSERT INTO `rbac_operations` VALUES (58,'copy','Copy Object','general',4000);
INSERT INTO `rbac_operations` VALUES (59,'create_feed','create external feed','create',9999);
INSERT INTO `rbac_operations` VALUES (60,'create_mcst','create media cast','create',9999);
INSERT INTO `rbac_operations` VALUES (61,'create_rcrs','create remote course','create',9999);
INSERT INTO `rbac_operations` VALUES (62,'add_thread','Add Threads','object',3100);
INSERT INTO `rbac_operations` VALUES (63,'create_sess','create session','create',9999);
INSERT INTO `rbac_operations` VALUES (64,'edit_content','Edit content','object',3000);
INSERT INTO `rbac_operations` VALUES (65,'create_wiki','create wiki','create',9999);
INSERT INTO `rbac_operations` VALUES (66,'edit_event','Edit calendar event','object',3600);
INSERT INTO `rbac_operations` VALUES (67,'create_crsr','create course reference','create',9999);
INSERT INTO `rbac_operations` VALUES (68,'create_catr','create category reference','create',9999);
INSERT INTO `rbac_operations` VALUES (69,'mail_to_global_roles','User may send mails to global roles','object',230);
INSERT INTO `rbac_operations` VALUES (71,'create_book','create booking pool','create',9999);
INSERT INTO `rbac_operations` VALUES (72,'add_consultation_hours','Add Consultation Hours Calendar','object',300);
INSERT INTO `rbac_operations` VALUES (73,'create_chtr','create chatroom','create',9999);
INSERT INTO `rbac_operations` VALUES (74,'create_blog','Create Blog','create',9999);
INSERT INTO `rbac_operations` VALUES (75,'create_dcl','Create Data Collection','create',9999);
INSERT INTO `rbac_operations` VALUES (76,'create_poll','Create Poll','create',9999);
INSERT INTO `rbac_operations` VALUES (77,'add_entry','Add Entry','object',3200);
INSERT INTO `rbac_operations` VALUES (78,'create_itgr','Create Item Group','create',9999);
INSERT INTO `rbac_operations` VALUES (79,'contribute','Contribute','object',3205);
INSERT INTO `rbac_operations` VALUES (80,'lp_other_users','See LP Data Of Other Users','object',250);
INSERT INTO `rbac_operations` VALUES (81,'create_bibl','Create Bibliographic','create',9999);
INSERT INTO `rbac_operations` VALUES (82,'create_cld','Create Cloud Folder','create',9999);
INSERT INTO `rbac_operations` VALUES (83,'upload','Upload Items','object',3240);
INSERT INTO `rbac_operations` VALUES (84,'delete_files','Delete Files','object',3260);
INSERT INTO `rbac_operations` VALUES (85,'delete_folders','Delete Folders','object',3270);
INSERT INTO `rbac_operations` VALUES (86,'download','Download Items','object',3230);
INSERT INTO `rbac_operations` VALUES (87,'files_visible','Files are visible','object',3210);
INSERT INTO `rbac_operations` VALUES (88,'folders_visible','Folders are visible','object',3220);
INSERT INTO `rbac_operations` VALUES (89,'folders_create','Folders may be created','object',3250);
INSERT INTO `rbac_operations` VALUES (90,'create_prtt','Create Portfolio Template','create',9999);
INSERT INTO `rbac_operations` VALUES (91,'create_orgu','Create OrgUnit','create',9999);
INSERT INTO `rbac_operations` VALUES (92,'view_learning_progress','View learning progress from users in this orgu.','object',270);
INSERT INTO `rbac_operations` VALUES (93,'view_learning_progress_rec','View learning progress from users in this orgu and subsequent orgus.','object',280);
INSERT INTO `rbac_operations` VALUES (94,'statistics_read','Read Statistics','object',2200);
INSERT INTO `rbac_operations` VALUES (95,'read_learning_progress','Read Learning Progress','object',2300);
INSERT INTO `rbac_operations` VALUES (96,'redact','Redact','object',3900);
INSERT INTO `rbac_operations` VALUES (97,'edit_wiki_navigation','Edit Wiki Navigation','object',3220);
INSERT INTO `rbac_operations` VALUES (98,'delete_wiki_pages','Delete Wiki Pages','object',3300);
INSERT INTO `rbac_operations` VALUES (99,'activate_wiki_protection','Set Read-Only','object',3240);
INSERT INTO `rbac_operations` VALUES (100,'wiki_html_export','Wiki HTML Export','object',3242);
INSERT INTO `rbac_operations` VALUES (101,'create_prg','Create Study Programme','create',9999);
INSERT INTO `rbac_operations` VALUES (102,'manage_members','Manage Members','object',2400);
INSERT INTO `rbac_operations` VALUES (103,'sty_write_content','Edit Content Styles','object',6101);
INSERT INTO `rbac_operations` VALUES (104,'sty_write_system','Edit System Styles','object',6100);
INSERT INTO `rbac_operations` VALUES (105,'sty_write_page_layout','Edit Page Layouts','object',6102);
INSERT INTO `rbac_operations` VALUES (106,'create_grpr','Create Group Reference','create',9999);
INSERT INTO `rbac_operations` VALUES (107,'news_add_news','Add News','object',2100);
INSERT INTO `rbac_operations` VALUES (108,'create_iass','Create Individual Assessment','create',9999);
INSERT INTO `rbac_operations` VALUES (109,'edit_members','Manage members','object',2400);
INSERT INTO `rbac_operations` VALUES (110,'amend_grading','Amend grading','object',8200);
INSERT INTO `rbac_operations` VALUES (111,'grade','Grade','object',2410);
INSERT INTO `rbac_operations` VALUES (112,'edit_page_meta','Edit Page Metadata','object',3050);
INSERT INTO `rbac_operations` VALUES (113,'release_objects','Release objects','object',500);
INSERT INTO `rbac_operations` VALUES (114,'edit_submissions_grades','Edit Submissions Grades','object',3800);
INSERT INTO `rbac_operations` VALUES (115,'tst_results','view the results of test participants','object',7050);
INSERT INTO `rbac_operations` VALUES (116,'create_copa','Create Content Page Object','create',9999);
INSERT INTO `rbac_operations` VALUES (117,'manage_materials','Manage Materials','object',6500);
INSERT INTO `rbac_operations` VALUES (118,'edit_metadata','Edit Metadata','object',5800);
INSERT INTO `rbac_operations` VALUES (119,'create_lso','Create Learning Sequence','create',9999);
INSERT INTO `rbac_operations` VALUES (120,'participate','Participate to Learning Sequence','object',1010);
INSERT INTO `rbac_operations` VALUES (121,'unparticipate','Unparticipate from Learning Sequence','object',1020);
INSERT INTO `rbac_operations` VALUES (122,'read_results','Access Results','object',2500);
INSERT INTO `rbac_operations` VALUES (123,'change_presentation','change presentation of a view','object',200);
INSERT INTO `rbac_operations` VALUES (124,'upload_blacklisted_files','Upload Blacklisted Files','object',1);
INSERT INTO `rbac_operations` VALUES (125,'read_outcomes','Access Outcomes','object',2250);
INSERT INTO `rbac_operations` VALUES (126,'create_lti','Create LTI Consumer Object','create',9999);
INSERT INTO `rbac_operations` VALUES (127,'create_cmix','Create cmi5/xAPI Object','create',9999);
INSERT INTO `rbac_operations` VALUES (128,'add_consume_provider','Allow Add Own Provider','object',3510);
INSERT INTO `rbac_operations` VALUES (129,'create_prgr','Create Study Programme Reference','create',9999);

--
-- Table structure for table `rbac_operations_seq`
--

CREATE TABLE `rbac_operations_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=130;

--
-- Dumping data for table `rbac_operations_seq`
--

INSERT INTO `rbac_operations_seq` VALUES (129);

--
-- Table structure for table `rbac_pa`
--

CREATE TABLE `rbac_pa` (
  `rol_id` int(11) NOT NULL DEFAULT 0,
  `ops_id` varchar(4000) DEFAULT NULL,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rol_id`,`ref_id`),
  KEY `i1_idx` (`ref_id`)
) ;

--
-- Dumping data for table `rbac_pa`
--

INSERT INTO `rbac_pa` VALUES (2,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (3,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (4,'a:2:{i:0;i:3;i:1;i:2;}',1);
INSERT INTO `rbac_pa` VALUES (4,'a:1:{i:0;i:30;}',12);
INSERT INTO `rbac_pa` VALUES (4,'a:1:{i:0;i:51;}',22);
INSERT INTO `rbac_pa` VALUES (4,'a:2:{i:0;i:2;i:1;i:3;}',24);
INSERT INTO `rbac_pa` VALUES (4,'a:2:{i:0;i:2;i:1;i:3;}',46);
INSERT INTO `rbac_pa` VALUES (5,'a:2:{i:0;i:3;i:1;i:2;}',1);
INSERT INTO `rbac_pa` VALUES (5,'a:2:{i:0;i:30;i:1;i:69;}',12);
INSERT INTO `rbac_pa` VALUES (5,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (14,'a:2:{i:0;i:3;i:1;i:2;}',1);
INSERT INTO `rbac_pa` VALUES (14,'a:1:{i:0;i:69;}',12);
INSERT INTO `rbac_pa` VALUES (14,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (80,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (81,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (82,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (83,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (110,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (111,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (112,'a:1:{i:0;s:2:\"51\";}',22);
INSERT INTO `rbac_pa` VALUES (187,'a:3:{i:0;i:52;i:1;i:3;i:2;i:2;}',46);

--
-- Table structure for table `rbac_ta`
--

CREATE TABLE `rbac_ta` (
  `typ_id` int(11) NOT NULL DEFAULT 0,
  `ops_id` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`typ_id`,`ops_id`)
) ;

--
-- Dumping data for table `rbac_ta`
--

INSERT INTO `rbac_ta` VALUES (15,1);
INSERT INTO `rbac_ta` VALUES (15,2);
INSERT INTO `rbac_ta` VALUES (15,3);
INSERT INTO `rbac_ta` VALUES (15,4);
INSERT INTO `rbac_ta` VALUES (15,6);
INSERT INTO `rbac_ta` VALUES (15,7);
INSERT INTO `rbac_ta` VALUES (15,8);
INSERT INTO `rbac_ta` VALUES (15,17);
INSERT INTO `rbac_ta` VALUES (15,18);
INSERT INTO `rbac_ta` VALUES (15,20);
INSERT INTO `rbac_ta` VALUES (15,21);
INSERT INTO `rbac_ta` VALUES (15,22);
INSERT INTO `rbac_ta` VALUES (15,23);
INSERT INTO `rbac_ta` VALUES (15,24);
INSERT INTO `rbac_ta` VALUES (15,25);
INSERT INTO `rbac_ta` VALUES (15,26);
INSERT INTO `rbac_ta` VALUES (15,27);
INSERT INTO `rbac_ta` VALUES (15,28);
INSERT INTO `rbac_ta` VALUES (15,31);
INSERT INTO `rbac_ta` VALUES (15,32);
INSERT INTO `rbac_ta` VALUES (15,42);
INSERT INTO `rbac_ta` VALUES (15,43);
INSERT INTO `rbac_ta` VALUES (15,50);
INSERT INTO `rbac_ta` VALUES (15,55);
INSERT INTO `rbac_ta` VALUES (15,58);
INSERT INTO `rbac_ta` VALUES (15,59);
INSERT INTO `rbac_ta` VALUES (15,60);
INSERT INTO `rbac_ta` VALUES (15,63);
INSERT INTO `rbac_ta` VALUES (15,65);
INSERT INTO `rbac_ta` VALUES (15,66);
INSERT INTO `rbac_ta` VALUES (15,67);
INSERT INTO `rbac_ta` VALUES (15,68);
INSERT INTO `rbac_ta` VALUES (15,71);
INSERT INTO `rbac_ta` VALUES (15,73);
INSERT INTO `rbac_ta` VALUES (15,74);
INSERT INTO `rbac_ta` VALUES (15,75);
INSERT INTO `rbac_ta` VALUES (15,76);
INSERT INTO `rbac_ta` VALUES (15,78);
INSERT INTO `rbac_ta` VALUES (15,81);
INSERT INTO `rbac_ta` VALUES (15,82);
INSERT INTO `rbac_ta` VALUES (15,90);
INSERT INTO `rbac_ta` VALUES (15,95);
INSERT INTO `rbac_ta` VALUES (15,102);
INSERT INTO `rbac_ta` VALUES (15,106);
INSERT INTO `rbac_ta` VALUES (15,107);
INSERT INTO `rbac_ta` VALUES (15,111);
INSERT INTO `rbac_ta` VALUES (15,116);
INSERT INTO `rbac_ta` VALUES (15,119);
INSERT INTO `rbac_ta` VALUES (15,126);
INSERT INTO `rbac_ta` VALUES (15,127);
INSERT INTO `rbac_ta` VALUES (16,1);
INSERT INTO `rbac_ta` VALUES (16,2);
INSERT INTO `rbac_ta` VALUES (16,3);
INSERT INTO `rbac_ta` VALUES (16,4);
INSERT INTO `rbac_ta` VALUES (16,6);
INSERT INTO `rbac_ta` VALUES (16,16);
INSERT INTO `rbac_ta` VALUES (16,17);
INSERT INTO `rbac_ta` VALUES (16,18);
INSERT INTO `rbac_ta` VALUES (16,19);
INSERT INTO `rbac_ta` VALUES (16,20);
INSERT INTO `rbac_ta` VALUES (16,21);
INSERT INTO `rbac_ta` VALUES (16,22);
INSERT INTO `rbac_ta` VALUES (16,23);
INSERT INTO `rbac_ta` VALUES (16,24);
INSERT INTO `rbac_ta` VALUES (16,25);
INSERT INTO `rbac_ta` VALUES (16,27);
INSERT INTO `rbac_ta` VALUES (16,28);
INSERT INTO `rbac_ta` VALUES (16,31);
INSERT INTO `rbac_ta` VALUES (16,32);
INSERT INTO `rbac_ta` VALUES (16,42);
INSERT INTO `rbac_ta` VALUES (16,43);
INSERT INTO `rbac_ta` VALUES (16,47);
INSERT INTO `rbac_ta` VALUES (16,48);
INSERT INTO `rbac_ta` VALUES (16,50);
INSERT INTO `rbac_ta` VALUES (16,58);
INSERT INTO `rbac_ta` VALUES (16,59);
INSERT INTO `rbac_ta` VALUES (16,60);
INSERT INTO `rbac_ta` VALUES (16,61);
INSERT INTO `rbac_ta` VALUES (16,65);
INSERT INTO `rbac_ta` VALUES (16,67);
INSERT INTO `rbac_ta` VALUES (16,68);
INSERT INTO `rbac_ta` VALUES (16,71);
INSERT INTO `rbac_ta` VALUES (16,73);
INSERT INTO `rbac_ta` VALUES (16,74);
INSERT INTO `rbac_ta` VALUES (16,75);
INSERT INTO `rbac_ta` VALUES (16,76);
INSERT INTO `rbac_ta` VALUES (16,78);
INSERT INTO `rbac_ta` VALUES (16,81);
INSERT INTO `rbac_ta` VALUES (16,82);
INSERT INTO `rbac_ta` VALUES (16,90);
INSERT INTO `rbac_ta` VALUES (16,101);
INSERT INTO `rbac_ta` VALUES (16,106);
INSERT INTO `rbac_ta` VALUES (16,108);
INSERT INTO `rbac_ta` VALUES (16,116);
INSERT INTO `rbac_ta` VALUES (16,119);
INSERT INTO `rbac_ta` VALUES (16,126);
INSERT INTO `rbac_ta` VALUES (16,127);
INSERT INTO `rbac_ta` VALUES (17,1);
INSERT INTO `rbac_ta` VALUES (17,2);
INSERT INTO `rbac_ta` VALUES (17,3);
INSERT INTO `rbac_ta` VALUES (17,4);
INSERT INTO `rbac_ta` VALUES (17,6);
INSERT INTO `rbac_ta` VALUES (17,7);
INSERT INTO `rbac_ta` VALUES (17,8);
INSERT INTO `rbac_ta` VALUES (17,17);
INSERT INTO `rbac_ta` VALUES (17,18);
INSERT INTO `rbac_ta` VALUES (17,20);
INSERT INTO `rbac_ta` VALUES (17,21);
INSERT INTO `rbac_ta` VALUES (17,22);
INSERT INTO `rbac_ta` VALUES (17,23);
INSERT INTO `rbac_ta` VALUES (17,24);
INSERT INTO `rbac_ta` VALUES (17,25);
INSERT INTO `rbac_ta` VALUES (17,26);
INSERT INTO `rbac_ta` VALUES (17,27);
INSERT INTO `rbac_ta` VALUES (17,28);
INSERT INTO `rbac_ta` VALUES (17,31);
INSERT INTO `rbac_ta` VALUES (17,32);
INSERT INTO `rbac_ta` VALUES (17,42);
INSERT INTO `rbac_ta` VALUES (17,43);
INSERT INTO `rbac_ta` VALUES (17,50);
INSERT INTO `rbac_ta` VALUES (17,55);
INSERT INTO `rbac_ta` VALUES (17,58);
INSERT INTO `rbac_ta` VALUES (17,59);
INSERT INTO `rbac_ta` VALUES (17,60);
INSERT INTO `rbac_ta` VALUES (17,63);
INSERT INTO `rbac_ta` VALUES (17,65);
INSERT INTO `rbac_ta` VALUES (17,66);
INSERT INTO `rbac_ta` VALUES (17,67);
INSERT INTO `rbac_ta` VALUES (17,68);
INSERT INTO `rbac_ta` VALUES (17,71);
INSERT INTO `rbac_ta` VALUES (17,73);
INSERT INTO `rbac_ta` VALUES (17,74);
INSERT INTO `rbac_ta` VALUES (17,75);
INSERT INTO `rbac_ta` VALUES (17,76);
INSERT INTO `rbac_ta` VALUES (17,78);
INSERT INTO `rbac_ta` VALUES (17,81);
INSERT INTO `rbac_ta` VALUES (17,82);
INSERT INTO `rbac_ta` VALUES (17,90);
INSERT INTO `rbac_ta` VALUES (17,95);
INSERT INTO `rbac_ta` VALUES (17,102);
INSERT INTO `rbac_ta` VALUES (17,106);
INSERT INTO `rbac_ta` VALUES (17,107);
INSERT INTO `rbac_ta` VALUES (17,108);
INSERT INTO `rbac_ta` VALUES (17,111);
INSERT INTO `rbac_ta` VALUES (17,116);
INSERT INTO `rbac_ta` VALUES (17,119);
INSERT INTO `rbac_ta` VALUES (17,126);
INSERT INTO `rbac_ta` VALUES (17,127);
INSERT INTO `rbac_ta` VALUES (19,1);
INSERT INTO `rbac_ta` VALUES (19,2);
INSERT INTO `rbac_ta` VALUES (19,3);
INSERT INTO `rbac_ta` VALUES (19,4);
INSERT INTO `rbac_ta` VALUES (19,11);
INSERT INTO `rbac_ta` VALUES (19,30);
INSERT INTO `rbac_ta` VALUES (19,69);
INSERT INTO `rbac_ta` VALUES (20,1);
INSERT INTO `rbac_ta` VALUES (20,2);
INSERT INTO `rbac_ta` VALUES (20,3);
INSERT INTO `rbac_ta` VALUES (20,4);
INSERT INTO `rbac_ta` VALUES (20,6);
INSERT INTO `rbac_ta` VALUES (20,55);
INSERT INTO `rbac_ta` VALUES (20,58);
INSERT INTO `rbac_ta` VALUES (20,95);
INSERT INTO `rbac_ta` VALUES (21,1);
INSERT INTO `rbac_ta` VALUES (21,2);
INSERT INTO `rbac_ta` VALUES (21,3);
INSERT INTO `rbac_ta` VALUES (21,4);
INSERT INTO `rbac_ta` VALUES (22,1);
INSERT INTO `rbac_ta` VALUES (22,2);
INSERT INTO `rbac_ta` VALUES (22,3);
INSERT INTO `rbac_ta` VALUES (22,4);
INSERT INTO `rbac_ta` VALUES (22,6);
INSERT INTO `rbac_ta` VALUES (22,13);
INSERT INTO `rbac_ta` VALUES (22,41);
INSERT INTO `rbac_ta` VALUES (22,48);
INSERT INTO `rbac_ta` VALUES (22,49);
INSERT INTO `rbac_ta` VALUES (23,1);
INSERT INTO `rbac_ta` VALUES (23,2);
INSERT INTO `rbac_ta` VALUES (23,3);
INSERT INTO `rbac_ta` VALUES (23,4);
INSERT INTO `rbac_ta` VALUES (23,6);
INSERT INTO `rbac_ta` VALUES (23,14);
INSERT INTO `rbac_ta` VALUES (23,15);
INSERT INTO `rbac_ta` VALUES (23,40);
INSERT INTO `rbac_ta` VALUES (24,1);
INSERT INTO `rbac_ta` VALUES (24,2);
INSERT INTO `rbac_ta` VALUES (24,3);
INSERT INTO `rbac_ta` VALUES (24,4);
INSERT INTO `rbac_ta` VALUES (28,1);
INSERT INTO `rbac_ta` VALUES (28,2);
INSERT INTO `rbac_ta` VALUES (28,3);
INSERT INTO `rbac_ta` VALUES (28,4);
INSERT INTO `rbac_ta` VALUES (31,1);
INSERT INTO `rbac_ta` VALUES (31,2);
INSERT INTO `rbac_ta` VALUES (31,3);
INSERT INTO `rbac_ta` VALUES (31,4);
INSERT INTO `rbac_ta` VALUES (31,6);
INSERT INTO `rbac_ta` VALUES (31,55);
INSERT INTO `rbac_ta` VALUES (31,58);
INSERT INTO `rbac_ta` VALUES (32,1);
INSERT INTO `rbac_ta` VALUES (32,2);
INSERT INTO `rbac_ta` VALUES (32,3);
INSERT INTO `rbac_ta` VALUES (32,4);
INSERT INTO `rbac_ta` VALUES (32,6);
INSERT INTO `rbac_ta` VALUES (32,58);
INSERT INTO `rbac_ta` VALUES (32,64);
INSERT INTO `rbac_ta` VALUES (33,1);
INSERT INTO `rbac_ta` VALUES (33,2);
INSERT INTO `rbac_ta` VALUES (33,3);
INSERT INTO `rbac_ta` VALUES (33,4);
INSERT INTO `rbac_ta` VALUES (33,16);
INSERT INTO `rbac_ta` VALUES (33,17);
INSERT INTO `rbac_ta` VALUES (33,18);
INSERT INTO `rbac_ta` VALUES (33,19);
INSERT INTO `rbac_ta` VALUES (33,20);
INSERT INTO `rbac_ta` VALUES (33,21);
INSERT INTO `rbac_ta` VALUES (33,22);
INSERT INTO `rbac_ta` VALUES (33,23);
INSERT INTO `rbac_ta` VALUES (33,24);
INSERT INTO `rbac_ta` VALUES (33,25);
INSERT INTO `rbac_ta` VALUES (33,27);
INSERT INTO `rbac_ta` VALUES (33,28);
INSERT INTO `rbac_ta` VALUES (33,31);
INSERT INTO `rbac_ta` VALUES (33,32);
INSERT INTO `rbac_ta` VALUES (33,42);
INSERT INTO `rbac_ta` VALUES (33,43);
INSERT INTO `rbac_ta` VALUES (33,50);
INSERT INTO `rbac_ta` VALUES (33,59);
INSERT INTO `rbac_ta` VALUES (33,60);
INSERT INTO `rbac_ta` VALUES (33,61);
INSERT INTO `rbac_ta` VALUES (33,65);
INSERT INTO `rbac_ta` VALUES (33,67);
INSERT INTO `rbac_ta` VALUES (33,68);
INSERT INTO `rbac_ta` VALUES (33,71);
INSERT INTO `rbac_ta` VALUES (33,73);
INSERT INTO `rbac_ta` VALUES (33,74);
INSERT INTO `rbac_ta` VALUES (33,75);
INSERT INTO `rbac_ta` VALUES (33,76);
INSERT INTO `rbac_ta` VALUES (33,78);
INSERT INTO `rbac_ta` VALUES (33,81);
INSERT INTO `rbac_ta` VALUES (33,82);
INSERT INTO `rbac_ta` VALUES (33,90);
INSERT INTO `rbac_ta` VALUES (33,91);
INSERT INTO `rbac_ta` VALUES (33,101);
INSERT INTO `rbac_ta` VALUES (33,106);
INSERT INTO `rbac_ta` VALUES (33,108);
INSERT INTO `rbac_ta` VALUES (33,116);
INSERT INTO `rbac_ta` VALUES (33,119);
INSERT INTO `rbac_ta` VALUES (33,126);
INSERT INTO `rbac_ta` VALUES (33,127);
INSERT INTO `rbac_ta` VALUES (34,1);
INSERT INTO `rbac_ta` VALUES (34,2);
INSERT INTO `rbac_ta` VALUES (34,3);
INSERT INTO `rbac_ta` VALUES (34,4);
INSERT INTO `rbac_ta` VALUES (34,6);
INSERT INTO `rbac_ta` VALUES (34,55);
INSERT INTO `rbac_ta` VALUES (34,58);
INSERT INTO `rbac_ta` VALUES (34,95);
INSERT INTO `rbac_ta` VALUES (37,1);
INSERT INTO `rbac_ta` VALUES (37,2);
INSERT INTO `rbac_ta` VALUES (37,3);
INSERT INTO `rbac_ta` VALUES (37,4);
INSERT INTO `rbac_ta` VALUES (37,6);
INSERT INTO `rbac_ta` VALUES (37,9);
INSERT INTO `rbac_ta` VALUES (37,10);
INSERT INTO `rbac_ta` VALUES (37,58);
INSERT INTO `rbac_ta` VALUES (37,62);
INSERT INTO `rbac_ta` VALUES (84,1);
INSERT INTO `rbac_ta` VALUES (84,2);
INSERT INTO `rbac_ta` VALUES (84,3);
INSERT INTO `rbac_ta` VALUES (84,4);
INSERT INTO `rbac_ta` VALUES (84,6);
INSERT INTO `rbac_ta` VALUES (84,55);
INSERT INTO `rbac_ta` VALUES (84,58);
INSERT INTO `rbac_ta` VALUES (84,95);
INSERT INTO `rbac_ta` VALUES (84,114);
INSERT INTO `rbac_ta` VALUES (85,1);
INSERT INTO `rbac_ta` VALUES (85,2);
INSERT INTO `rbac_ta` VALUES (85,3);
INSERT INTO `rbac_ta` VALUES (85,4);
INSERT INTO `rbac_ta` VALUES (87,1);
INSERT INTO `rbac_ta` VALUES (87,2);
INSERT INTO `rbac_ta` VALUES (87,3);
INSERT INTO `rbac_ta` VALUES (87,4);
INSERT INTO `rbac_ta` VALUES (87,6);
INSERT INTO `rbac_ta` VALUES (87,17);
INSERT INTO `rbac_ta` VALUES (87,18);
INSERT INTO `rbac_ta` VALUES (87,20);
INSERT INTO `rbac_ta` VALUES (87,21);
INSERT INTO `rbac_ta` VALUES (87,22);
INSERT INTO `rbac_ta` VALUES (87,23);
INSERT INTO `rbac_ta` VALUES (87,24);
INSERT INTO `rbac_ta` VALUES (87,25);
INSERT INTO `rbac_ta` VALUES (87,26);
INSERT INTO `rbac_ta` VALUES (87,27);
INSERT INTO `rbac_ta` VALUES (87,28);
INSERT INTO `rbac_ta` VALUES (87,31);
INSERT INTO `rbac_ta` VALUES (87,32);
INSERT INTO `rbac_ta` VALUES (87,42);
INSERT INTO `rbac_ta` VALUES (87,43);
INSERT INTO `rbac_ta` VALUES (87,50);
INSERT INTO `rbac_ta` VALUES (87,55);
INSERT INTO `rbac_ta` VALUES (87,58);
INSERT INTO `rbac_ta` VALUES (87,60);
INSERT INTO `rbac_ta` VALUES (87,63);
INSERT INTO `rbac_ta` VALUES (87,65);
INSERT INTO `rbac_ta` VALUES (87,71);
INSERT INTO `rbac_ta` VALUES (87,73);
INSERT INTO `rbac_ta` VALUES (87,74);
INSERT INTO `rbac_ta` VALUES (87,75);
INSERT INTO `rbac_ta` VALUES (87,76);
INSERT INTO `rbac_ta` VALUES (87,78);
INSERT INTO `rbac_ta` VALUES (87,81);
INSERT INTO `rbac_ta` VALUES (87,82);
INSERT INTO `rbac_ta` VALUES (87,90);
INSERT INTO `rbac_ta` VALUES (87,95);
INSERT INTO `rbac_ta` VALUES (87,106);
INSERT INTO `rbac_ta` VALUES (87,116);
INSERT INTO `rbac_ta` VALUES (87,119);
INSERT INTO `rbac_ta` VALUES (87,126);
INSERT INTO `rbac_ta` VALUES (87,127);
INSERT INTO `rbac_ta` VALUES (88,1);
INSERT INTO `rbac_ta` VALUES (88,2);
INSERT INTO `rbac_ta` VALUES (88,3);
INSERT INTO `rbac_ta` VALUES (88,4);
INSERT INTO `rbac_ta` VALUES (88,6);
INSERT INTO `rbac_ta` VALUES (88,55);
INSERT INTO `rbac_ta` VALUES (88,58);
INSERT INTO `rbac_ta` VALUES (88,95);
INSERT INTO `rbac_ta` VALUES (94,1);
INSERT INTO `rbac_ta` VALUES (94,2);
INSERT INTO `rbac_ta` VALUES (94,3);
INSERT INTO `rbac_ta` VALUES (94,4);
INSERT INTO `rbac_ta` VALUES (94,6);
INSERT INTO `rbac_ta` VALUES (94,55);
INSERT INTO `rbac_ta` VALUES (94,56);
INSERT INTO `rbac_ta` VALUES (94,58);
INSERT INTO `rbac_ta` VALUES (94,95);
INSERT INTO `rbac_ta` VALUES (94,115);
INSERT INTO `rbac_ta` VALUES (95,1);
INSERT INTO `rbac_ta` VALUES (95,2);
INSERT INTO `rbac_ta` VALUES (95,3);
INSERT INTO `rbac_ta` VALUES (95,4);
INSERT INTO `rbac_ta` VALUES (95,6);
INSERT INTO `rbac_ta` VALUES (95,58);
INSERT INTO `rbac_ta` VALUES (96,1);
INSERT INTO `rbac_ta` VALUES (96,2);
INSERT INTO `rbac_ta` VALUES (96,3);
INSERT INTO `rbac_ta` VALUES (96,4);
INSERT INTO `rbac_ta` VALUES (96,6);
INSERT INTO `rbac_ta` VALUES (96,52);
INSERT INTO `rbac_ta` VALUES (96,58);
INSERT INTO `rbac_ta` VALUES (97,1);
INSERT INTO `rbac_ta` VALUES (97,2);
INSERT INTO `rbac_ta` VALUES (97,3);
INSERT INTO `rbac_ta` VALUES (97,4);
INSERT INTO `rbac_ta` VALUES (99,1);
INSERT INTO `rbac_ta` VALUES (99,2);
INSERT INTO `rbac_ta` VALUES (99,3);
INSERT INTO `rbac_ta` VALUES (99,4);
INSERT INTO `rbac_ta` VALUES (101,1);
INSERT INTO `rbac_ta` VALUES (101,2);
INSERT INTO `rbac_ta` VALUES (101,3);
INSERT INTO `rbac_ta` VALUES (101,4);
INSERT INTO `rbac_ta` VALUES (101,6);
INSERT INTO `rbac_ta` VALUES (101,58);
INSERT INTO `rbac_ta` VALUES (102,1);
INSERT INTO `rbac_ta` VALUES (102,2);
INSERT INTO `rbac_ta` VALUES (102,3);
INSERT INTO `rbac_ta` VALUES (102,4);
INSERT INTO `rbac_ta` VALUES (102,6);
INSERT INTO `rbac_ta` VALUES (102,55);
INSERT INTO `rbac_ta` VALUES (102,58);
INSERT INTO `rbac_ta` VALUES (102,95);
INSERT INTO `rbac_ta` VALUES (103,1);
INSERT INTO `rbac_ta` VALUES (103,2);
INSERT INTO `rbac_ta` VALUES (103,3);
INSERT INTO `rbac_ta` VALUES (103,4);
INSERT INTO `rbac_ta` VALUES (103,6);
INSERT INTO `rbac_ta` VALUES (103,45);
INSERT INTO `rbac_ta` VALUES (103,55);
INSERT INTO `rbac_ta` VALUES (103,58);
INSERT INTO `rbac_ta` VALUES (103,95);
INSERT INTO `rbac_ta` VALUES (103,122);
INSERT INTO `rbac_ta` VALUES (104,1);
INSERT INTO `rbac_ta` VALUES (104,2);
INSERT INTO `rbac_ta` VALUES (104,3);
INSERT INTO `rbac_ta` VALUES (104,4);
INSERT INTO `rbac_ta` VALUES (104,6);
INSERT INTO `rbac_ta` VALUES (104,58);
INSERT INTO `rbac_ta` VALUES (106,1);
INSERT INTO `rbac_ta` VALUES (106,2);
INSERT INTO `rbac_ta` VALUES (106,3);
INSERT INTO `rbac_ta` VALUES (106,4);
INSERT INTO `rbac_ta` VALUES (106,66);
INSERT INTO `rbac_ta` VALUES (106,70);
INSERT INTO `rbac_ta` VALUES (106,72);
INSERT INTO `rbac_ta` VALUES (108,1);
INSERT INTO `rbac_ta` VALUES (108,2);
INSERT INTO `rbac_ta` VALUES (108,3);
INSERT INTO `rbac_ta` VALUES (108,4);
INSERT INTO `rbac_ta` VALUES (108,6);
INSERT INTO `rbac_ta` VALUES (108,80);
INSERT INTO `rbac_ta` VALUES (115,1);
INSERT INTO `rbac_ta` VALUES (115,2);
INSERT INTO `rbac_ta` VALUES (115,3);
INSERT INTO `rbac_ta` VALUES (115,4);
INSERT INTO `rbac_ta` VALUES (117,1);
INSERT INTO `rbac_ta` VALUES (117,2);
INSERT INTO `rbac_ta` VALUES (117,3);
INSERT INTO `rbac_ta` VALUES (117,103);
INSERT INTO `rbac_ta` VALUES (117,104);
INSERT INTO `rbac_ta` VALUES (117,105);
INSERT INTO `rbac_ta` VALUES (122,1);
INSERT INTO `rbac_ta` VALUES (122,2);
INSERT INTO `rbac_ta` VALUES (122,3);
INSERT INTO `rbac_ta` VALUES (122,4);
INSERT INTO `rbac_ta` VALUES (122,6);
INSERT INTO `rbac_ta` VALUES (122,58);
INSERT INTO `rbac_ta` VALUES (123,1);
INSERT INTO `rbac_ta` VALUES (123,2);
INSERT INTO `rbac_ta` VALUES (123,3);
INSERT INTO `rbac_ta` VALUES (123,4);
INSERT INTO `rbac_ta` VALUES (123,51);
INSERT INTO `rbac_ta` VALUES (127,1);
INSERT INTO `rbac_ta` VALUES (127,2);
INSERT INTO `rbac_ta` VALUES (127,3);
INSERT INTO `rbac_ta` VALUES (127,4);
INSERT INTO `rbac_ta` VALUES (135,1);
INSERT INTO `rbac_ta` VALUES (135,2);
INSERT INTO `rbac_ta` VALUES (135,3);
INSERT INTO `rbac_ta` VALUES (135,4);
INSERT INTO `rbac_ta` VALUES (137,1);
INSERT INTO `rbac_ta` VALUES (137,2);
INSERT INTO `rbac_ta` VALUES (137,3);
INSERT INTO `rbac_ta` VALUES (137,4);
INSERT INTO `rbac_ta` VALUES (137,57);
INSERT INTO `rbac_ta` VALUES (139,1);
INSERT INTO `rbac_ta` VALUES (139,2);
INSERT INTO `rbac_ta` VALUES (139,3);
INSERT INTO `rbac_ta` VALUES (139,4);
INSERT INTO `rbac_ta` VALUES (141,1);
INSERT INTO `rbac_ta` VALUES (141,3);
INSERT INTO `rbac_ta` VALUES (141,4);
INSERT INTO `rbac_ta` VALUES (141,6);
INSERT INTO `rbac_ta` VALUES (141,58);
INSERT INTO `rbac_ta` VALUES (142,1);
INSERT INTO `rbac_ta` VALUES (142,2);
INSERT INTO `rbac_ta` VALUES (142,3);
INSERT INTO `rbac_ta` VALUES (142,4);
INSERT INTO `rbac_ta` VALUES (142,6);
INSERT INTO `rbac_ta` VALUES (142,55);
INSERT INTO `rbac_ta` VALUES (142,58);
INSERT INTO `rbac_ta` VALUES (142,95);
INSERT INTO `rbac_ta` VALUES (143,1);
INSERT INTO `rbac_ta` VALUES (143,2);
INSERT INTO `rbac_ta` VALUES (143,3);
INSERT INTO `rbac_ta` VALUES (143,4);
INSERT INTO `rbac_ta` VALUES (143,123);
INSERT INTO `rbac_ta` VALUES (145,1);
INSERT INTO `rbac_ta` VALUES (145,2);
INSERT INTO `rbac_ta` VALUES (145,3);
INSERT INTO `rbac_ta` VALUES (145,4);
INSERT INTO `rbac_ta` VALUES (145,6);
INSERT INTO `rbac_ta` VALUES (146,1);
INSERT INTO `rbac_ta` VALUES (146,2);
INSERT INTO `rbac_ta` VALUES (146,3);
INSERT INTO `rbac_ta` VALUES (146,4);
INSERT INTO `rbac_ta` VALUES (149,1);
INSERT INTO `rbac_ta` VALUES (149,2);
INSERT INTO `rbac_ta` VALUES (149,3);
INSERT INTO `rbac_ta` VALUES (149,4);
INSERT INTO `rbac_ta` VALUES (151,1);
INSERT INTO `rbac_ta` VALUES (151,2);
INSERT INTO `rbac_ta` VALUES (151,3);
INSERT INTO `rbac_ta` VALUES (151,4);
INSERT INTO `rbac_ta` VALUES (151,124);
INSERT INTO `rbac_ta` VALUES (153,1);
INSERT INTO `rbac_ta` VALUES (153,2);
INSERT INTO `rbac_ta` VALUES (153,3);
INSERT INTO `rbac_ta` VALUES (153,4);
INSERT INTO `rbac_ta` VALUES (155,1);
INSERT INTO `rbac_ta` VALUES (155,2);
INSERT INTO `rbac_ta` VALUES (155,3);
INSERT INTO `rbac_ta` VALUES (155,4);
INSERT INTO `rbac_ta` VALUES (155,6);
INSERT INTO `rbac_ta` VALUES (155,55);
INSERT INTO `rbac_ta` VALUES (155,58);
INSERT INTO `rbac_ta` VALUES (155,95);
INSERT INTO `rbac_ta` VALUES (155,102);
INSERT INTO `rbac_ta` VALUES (155,117);
INSERT INTO `rbac_ta` VALUES (155,118);
INSERT INTO `rbac_ta` VALUES (156,1);
INSERT INTO `rbac_ta` VALUES (156,2);
INSERT INTO `rbac_ta` VALUES (156,3);
INSERT INTO `rbac_ta` VALUES (156,4);
INSERT INTO `rbac_ta` VALUES (158,1);
INSERT INTO `rbac_ta` VALUES (158,2);
INSERT INTO `rbac_ta` VALUES (158,3);
INSERT INTO `rbac_ta` VALUES (158,4);
INSERT INTO `rbac_ta` VALUES (158,6);
INSERT INTO `rbac_ta` VALUES (158,58);
INSERT INTO `rbac_ta` VALUES (158,64);
INSERT INTO `rbac_ta` VALUES (158,94);
INSERT INTO `rbac_ta` VALUES (158,97);
INSERT INTO `rbac_ta` VALUES (158,98);
INSERT INTO `rbac_ta` VALUES (158,99);
INSERT INTO `rbac_ta` VALUES (158,100);
INSERT INTO `rbac_ta` VALUES (158,112);
INSERT INTO `rbac_ta` VALUES (159,1);
INSERT INTO `rbac_ta` VALUES (159,2);
INSERT INTO `rbac_ta` VALUES (159,4);
INSERT INTO `rbac_ta` VALUES (159,6);
INSERT INTO `rbac_ta` VALUES (159,55);
INSERT INTO `rbac_ta` VALUES (159,58);
INSERT INTO `rbac_ta` VALUES (159,95);
INSERT INTO `rbac_ta` VALUES (160,1);
INSERT INTO `rbac_ta` VALUES (160,2);
INSERT INTO `rbac_ta` VALUES (160,4);
INSERT INTO `rbac_ta` VALUES (160,6);
INSERT INTO `rbac_ta` VALUES (160,58);
INSERT INTO `rbac_ta` VALUES (161,1);
INSERT INTO `rbac_ta` VALUES (161,2);
INSERT INTO `rbac_ta` VALUES (161,3);
INSERT INTO `rbac_ta` VALUES (161,4);
INSERT INTO `rbac_ta` VALUES (163,1);
INSERT INTO `rbac_ta` VALUES (163,2);
INSERT INTO `rbac_ta` VALUES (163,3);
INSERT INTO `rbac_ta` VALUES (163,4);
INSERT INTO `rbac_ta` VALUES (165,1);
INSERT INTO `rbac_ta` VALUES (165,2);
INSERT INTO `rbac_ta` VALUES (165,3);
INSERT INTO `rbac_ta` VALUES (165,4);
INSERT INTO `rbac_ta` VALUES (167,1);
INSERT INTO `rbac_ta` VALUES (167,2);
INSERT INTO `rbac_ta` VALUES (167,3);
INSERT INTO `rbac_ta` VALUES (167,4);
INSERT INTO `rbac_ta` VALUES (169,1);
INSERT INTO `rbac_ta` VALUES (169,2);
INSERT INTO `rbac_ta` VALUES (169,3);
INSERT INTO `rbac_ta` VALUES (169,4);
INSERT INTO `rbac_ta` VALUES (171,1);
INSERT INTO `rbac_ta` VALUES (171,2);
INSERT INTO `rbac_ta` VALUES (171,3);
INSERT INTO `rbac_ta` VALUES (171,4);
INSERT INTO `rbac_ta` VALUES (173,1);
INSERT INTO `rbac_ta` VALUES (173,2);
INSERT INTO `rbac_ta` VALUES (173,3);
INSERT INTO `rbac_ta` VALUES (173,4);
INSERT INTO `rbac_ta` VALUES (173,6);
INSERT INTO `rbac_ta` VALUES (173,58);
INSERT INTO `rbac_ta` VALUES (174,1);
INSERT INTO `rbac_ta` VALUES (174,2);
INSERT INTO `rbac_ta` VALUES (174,3);
INSERT INTO `rbac_ta` VALUES (174,4);
INSERT INTO `rbac_ta` VALUES (176,1);
INSERT INTO `rbac_ta` VALUES (176,2);
INSERT INTO `rbac_ta` VALUES (176,3);
INSERT INTO `rbac_ta` VALUES (176,4);
INSERT INTO `rbac_ta` VALUES (178,1);
INSERT INTO `rbac_ta` VALUES (178,2);
INSERT INTO `rbac_ta` VALUES (178,3);
INSERT INTO `rbac_ta` VALUES (178,4);
INSERT INTO `rbac_ta` VALUES (180,1);
INSERT INTO `rbac_ta` VALUES (180,2);
INSERT INTO `rbac_ta` VALUES (180,3);
INSERT INTO `rbac_ta` VALUES (180,4);
INSERT INTO `rbac_ta` VALUES (180,6);
INSERT INTO `rbac_ta` VALUES (180,52);
INSERT INTO `rbac_ta` VALUES (180,58);
INSERT INTO `rbac_ta` VALUES (182,1);
INSERT INTO `rbac_ta` VALUES (182,2);
INSERT INTO `rbac_ta` VALUES (182,3);
INSERT INTO `rbac_ta` VALUES (182,4);
INSERT INTO `rbac_ta` VALUES (183,1);
INSERT INTO `rbac_ta` VALUES (183,2);
INSERT INTO `rbac_ta` VALUES (183,3);
INSERT INTO `rbac_ta` VALUES (183,4);
INSERT INTO `rbac_ta` VALUES (189,1);
INSERT INTO `rbac_ta` VALUES (189,2);
INSERT INTO `rbac_ta` VALUES (189,3);
INSERT INTO `rbac_ta` VALUES (189,4);
INSERT INTO `rbac_ta` VALUES (189,6);
INSERT INTO `rbac_ta` VALUES (189,58);
INSERT INTO `rbac_ta` VALUES (189,79);
INSERT INTO `rbac_ta` VALUES (189,96);
INSERT INTO `rbac_ta` VALUES (190,1);
INSERT INTO `rbac_ta` VALUES (190,2);
INSERT INTO `rbac_ta` VALUES (190,3);
INSERT INTO `rbac_ta` VALUES (190,4);
INSERT INTO `rbac_ta` VALUES (190,6);
INSERT INTO `rbac_ta` VALUES (190,58);
INSERT INTO `rbac_ta` VALUES (190,64);
INSERT INTO `rbac_ta` VALUES (190,77);
INSERT INTO `rbac_ta` VALUES (191,1);
INSERT INTO `rbac_ta` VALUES (191,3);
INSERT INTO `rbac_ta` VALUES (191,4);
INSERT INTO `rbac_ta` VALUES (191,6);
INSERT INTO `rbac_ta` VALUES (191,58);
INSERT INTO `rbac_ta` VALUES (192,1);
INSERT INTO `rbac_ta` VALUES (192,2);
INSERT INTO `rbac_ta` VALUES (192,3);
INSERT INTO `rbac_ta` VALUES (192,4);
INSERT INTO `rbac_ta` VALUES (194,1);
INSERT INTO `rbac_ta` VALUES (194,2);
INSERT INTO `rbac_ta` VALUES (194,3);
INSERT INTO `rbac_ta` VALUES (194,4);
INSERT INTO `rbac_ta` VALUES (194,6);
INSERT INTO `rbac_ta` VALUES (194,58);
INSERT INTO `rbac_ta` VALUES (195,1);
INSERT INTO `rbac_ta` VALUES (195,2);
INSERT INTO `rbac_ta` VALUES (195,3);
INSERT INTO `rbac_ta` VALUES (195,4);
INSERT INTO `rbac_ta` VALUES (195,6);
INSERT INTO `rbac_ta` VALUES (196,1);
INSERT INTO `rbac_ta` VALUES (196,2);
INSERT INTO `rbac_ta` VALUES (196,3);
INSERT INTO `rbac_ta` VALUES (196,4);
INSERT INTO `rbac_ta` VALUES (196,6);
INSERT INTO `rbac_ta` VALUES (197,1);
INSERT INTO `rbac_ta` VALUES (197,2);
INSERT INTO `rbac_ta` VALUES (197,3);
INSERT INTO `rbac_ta` VALUES (197,4);
INSERT INTO `rbac_ta` VALUES (197,6);
INSERT INTO `rbac_ta` VALUES (198,1);
INSERT INTO `rbac_ta` VALUES (198,2);
INSERT INTO `rbac_ta` VALUES (198,3);
INSERT INTO `rbac_ta` VALUES (198,4);
INSERT INTO `rbac_ta` VALUES (198,6);
INSERT INTO `rbac_ta` VALUES (199,1);
INSERT INTO `rbac_ta` VALUES (199,2);
INSERT INTO `rbac_ta` VALUES (199,3);
INSERT INTO `rbac_ta` VALUES (199,4);
INSERT INTO `rbac_ta` VALUES (199,6);
INSERT INTO `rbac_ta` VALUES (200,1);
INSERT INTO `rbac_ta` VALUES (200,2);
INSERT INTO `rbac_ta` VALUES (200,3);
INSERT INTO `rbac_ta` VALUES (200,4);
INSERT INTO `rbac_ta` VALUES (200,6);
INSERT INTO `rbac_ta` VALUES (201,1);
INSERT INTO `rbac_ta` VALUES (201,2);
INSERT INTO `rbac_ta` VALUES (201,3);
INSERT INTO `rbac_ta` VALUES (201,4);
INSERT INTO `rbac_ta` VALUES (201,6);
INSERT INTO `rbac_ta` VALUES (203,1);
INSERT INTO `rbac_ta` VALUES (203,2);
INSERT INTO `rbac_ta` VALUES (203,3);
INSERT INTO `rbac_ta` VALUES (203,4);
INSERT INTO `rbac_ta` VALUES (205,1);
INSERT INTO `rbac_ta` VALUES (205,2);
INSERT INTO `rbac_ta` VALUES (205,3);
INSERT INTO `rbac_ta` VALUES (205,4);
INSERT INTO `rbac_ta` VALUES (207,1);
INSERT INTO `rbac_ta` VALUES (207,2);
INSERT INTO `rbac_ta` VALUES (207,3);
INSERT INTO `rbac_ta` VALUES (207,4);
INSERT INTO `rbac_ta` VALUES (207,6);
INSERT INTO `rbac_ta` VALUES (207,58);
INSERT INTO `rbac_ta` VALUES (208,1);
INSERT INTO `rbac_ta` VALUES (208,2);
INSERT INTO `rbac_ta` VALUES (208,3);
INSERT INTO `rbac_ta` VALUES (208,4);
INSERT INTO `rbac_ta` VALUES (210,1);
INSERT INTO `rbac_ta` VALUES (210,2);
INSERT INTO `rbac_ta` VALUES (210,3);
INSERT INTO `rbac_ta` VALUES (210,4);
INSERT INTO `rbac_ta` VALUES (210,6);
INSERT INTO `rbac_ta` VALUES (210,83);
INSERT INTO `rbac_ta` VALUES (210,84);
INSERT INTO `rbac_ta` VALUES (210,85);
INSERT INTO `rbac_ta` VALUES (210,86);
INSERT INTO `rbac_ta` VALUES (210,87);
INSERT INTO `rbac_ta` VALUES (210,88);
INSERT INTO `rbac_ta` VALUES (210,89);
INSERT INTO `rbac_ta` VALUES (211,1);
INSERT INTO `rbac_ta` VALUES (211,2);
INSERT INTO `rbac_ta` VALUES (211,3);
INSERT INTO `rbac_ta` VALUES (211,4);
INSERT INTO `rbac_ta` VALUES (213,1);
INSERT INTO `rbac_ta` VALUES (213,2);
INSERT INTO `rbac_ta` VALUES (213,3);
INSERT INTO `rbac_ta` VALUES (213,4);
INSERT INTO `rbac_ta` VALUES (215,1);
INSERT INTO `rbac_ta` VALUES (215,2);
INSERT INTO `rbac_ta` VALUES (215,3);
INSERT INTO `rbac_ta` VALUES (215,4);
INSERT INTO `rbac_ta` VALUES (217,1);
INSERT INTO `rbac_ta` VALUES (217,2);
INSERT INTO `rbac_ta` VALUES (217,3);
INSERT INTO `rbac_ta` VALUES (217,4);
INSERT INTO `rbac_ta` VALUES (219,1);
INSERT INTO `rbac_ta` VALUES (219,2);
INSERT INTO `rbac_ta` VALUES (219,3);
INSERT INTO `rbac_ta` VALUES (219,4);
INSERT INTO `rbac_ta` VALUES (219,6);
INSERT INTO `rbac_ta` VALUES (219,58);
INSERT INTO `rbac_ta` VALUES (220,1);
INSERT INTO `rbac_ta` VALUES (220,2);
INSERT INTO `rbac_ta` VALUES (220,3);
INSERT INTO `rbac_ta` VALUES (220,4);
INSERT INTO `rbac_ta` VALUES (220,6);
INSERT INTO `rbac_ta` VALUES (220,47);
INSERT INTO `rbac_ta` VALUES (220,48);
INSERT INTO `rbac_ta` VALUES (220,58);
INSERT INTO `rbac_ta` VALUES (220,91);
INSERT INTO `rbac_ta` VALUES (220,92);
INSERT INTO `rbac_ta` VALUES (220,93);
INSERT INTO `rbac_ta` VALUES (223,1);
INSERT INTO `rbac_ta` VALUES (223,2);
INSERT INTO `rbac_ta` VALUES (223,3);
INSERT INTO `rbac_ta` VALUES (223,4);
INSERT INTO `rbac_ta` VALUES (225,1);
INSERT INTO `rbac_ta` VALUES (225,2);
INSERT INTO `rbac_ta` VALUES (225,3);
INSERT INTO `rbac_ta` VALUES (225,4);
INSERT INTO `rbac_ta` VALUES (227,1);
INSERT INTO `rbac_ta` VALUES (227,2);
INSERT INTO `rbac_ta` VALUES (227,3);
INSERT INTO `rbac_ta` VALUES (227,4);
INSERT INTO `rbac_ta` VALUES (229,1);
INSERT INTO `rbac_ta` VALUES (229,2);
INSERT INTO `rbac_ta` VALUES (229,3);
INSERT INTO `rbac_ta` VALUES (229,4);
INSERT INTO `rbac_ta` VALUES (232,1);
INSERT INTO `rbac_ta` VALUES (232,2);
INSERT INTO `rbac_ta` VALUES (232,3);
INSERT INTO `rbac_ta` VALUES (232,4);
INSERT INTO `rbac_ta` VALUES (234,1);
INSERT INTO `rbac_ta` VALUES (234,2);
INSERT INTO `rbac_ta` VALUES (234,3);
INSERT INTO `rbac_ta` VALUES (234,4);
INSERT INTO `rbac_ta` VALUES (236,1);
INSERT INTO `rbac_ta` VALUES (236,2);
INSERT INTO `rbac_ta` VALUES (236,3);
INSERT INTO `rbac_ta` VALUES (236,4);
INSERT INTO `rbac_ta` VALUES (236,6);
INSERT INTO `rbac_ta` VALUES (236,58);
INSERT INTO `rbac_ta` VALUES (236,101);
INSERT INTO `rbac_ta` VALUES (236,102);
INSERT INTO `rbac_ta` VALUES (236,129);
INSERT INTO `rbac_ta` VALUES (237,1);
INSERT INTO `rbac_ta` VALUES (237,2);
INSERT INTO `rbac_ta` VALUES (237,3);
INSERT INTO `rbac_ta` VALUES (237,4);
INSERT INTO `rbac_ta` VALUES (239,1);
INSERT INTO `rbac_ta` VALUES (239,2);
INSERT INTO `rbac_ta` VALUES (239,3);
INSERT INTO `rbac_ta` VALUES (239,4);
INSERT INTO `rbac_ta` VALUES (261,1);
INSERT INTO `rbac_ta` VALUES (261,2);
INSERT INTO `rbac_ta` VALUES (261,4);
INSERT INTO `rbac_ta` VALUES (261,6);
INSERT INTO `rbac_ta` VALUES (261,58);
INSERT INTO `rbac_ta` VALUES (262,1);
INSERT INTO `rbac_ta` VALUES (262,2);
INSERT INTO `rbac_ta` VALUES (262,3);
INSERT INTO `rbac_ta` VALUES (262,4);
INSERT INTO `rbac_ta` VALUES (264,1);
INSERT INTO `rbac_ta` VALUES (264,2);
INSERT INTO `rbac_ta` VALUES (264,3);
INSERT INTO `rbac_ta` VALUES (264,4);
INSERT INTO `rbac_ta` VALUES (266,1);
INSERT INTO `rbac_ta` VALUES (266,2);
INSERT INTO `rbac_ta` VALUES (266,3);
INSERT INTO `rbac_ta` VALUES (266,4);
INSERT INTO `rbac_ta` VALUES (266,6);
INSERT INTO `rbac_ta` VALUES (266,55);
INSERT INTO `rbac_ta` VALUES (266,58);
INSERT INTO `rbac_ta` VALUES (266,95);
INSERT INTO `rbac_ta` VALUES (266,109);
INSERT INTO `rbac_ta` VALUES (266,110);
INSERT INTO `rbac_ta` VALUES (270,1);
INSERT INTO `rbac_ta` VALUES (270,2);
INSERT INTO `rbac_ta` VALUES (270,3);
INSERT INTO `rbac_ta` VALUES (270,4);
INSERT INTO `rbac_ta` VALUES (272,1);
INSERT INTO `rbac_ta` VALUES (272,2);
INSERT INTO `rbac_ta` VALUES (272,3);
INSERT INTO `rbac_ta` VALUES (272,4);
INSERT INTO `rbac_ta` VALUES (272,113);
INSERT INTO `rbac_ta` VALUES (272,128);
INSERT INTO `rbac_ta` VALUES (274,1);
INSERT INTO `rbac_ta` VALUES (274,2);
INSERT INTO `rbac_ta` VALUES (274,3);
INSERT INTO `rbac_ta` VALUES (274,4);
INSERT INTO `rbac_ta` VALUES (274,6);
INSERT INTO `rbac_ta` VALUES (274,55);
INSERT INTO `rbac_ta` VALUES (274,58);
INSERT INTO `rbac_ta` VALUES (274,95);
INSERT INTO `rbac_ta` VALUES (275,1);
INSERT INTO `rbac_ta` VALUES (275,2);
INSERT INTO `rbac_ta` VALUES (275,3);
INSERT INTO `rbac_ta` VALUES (275,4);
INSERT INTO `rbac_ta` VALUES (277,1);
INSERT INTO `rbac_ta` VALUES (277,2);
INSERT INTO `rbac_ta` VALUES (277,3);
INSERT INTO `rbac_ta` VALUES (277,4);
INSERT INTO `rbac_ta` VALUES (277,6);
INSERT INTO `rbac_ta` VALUES (277,55);
INSERT INTO `rbac_ta` VALUES (277,58);
INSERT INTO `rbac_ta` VALUES (277,95);
INSERT INTO `rbac_ta` VALUES (277,102);
INSERT INTO `rbac_ta` VALUES (277,120);
INSERT INTO `rbac_ta` VALUES (277,121);
INSERT INTO `rbac_ta` VALUES (280,1);
INSERT INTO `rbac_ta` VALUES (280,2);
INSERT INTO `rbac_ta` VALUES (280,3);
INSERT INTO `rbac_ta` VALUES (280,4);
INSERT INTO `rbac_ta` VALUES (280,6);
INSERT INTO `rbac_ta` VALUES (280,55);
INSERT INTO `rbac_ta` VALUES (280,58);
INSERT INTO `rbac_ta` VALUES (280,95);
INSERT INTO `rbac_ta` VALUES (280,125);
INSERT INTO `rbac_ta` VALUES (281,1);
INSERT INTO `rbac_ta` VALUES (281,2);
INSERT INTO `rbac_ta` VALUES (281,3);
INSERT INTO `rbac_ta` VALUES (281,4);
INSERT INTO `rbac_ta` VALUES (281,6);
INSERT INTO `rbac_ta` VALUES (281,55);
INSERT INTO `rbac_ta` VALUES (281,58);
INSERT INTO `rbac_ta` VALUES (281,95);
INSERT INTO `rbac_ta` VALUES (281,125);
INSERT INTO `rbac_ta` VALUES (282,1);
INSERT INTO `rbac_ta` VALUES (282,2);
INSERT INTO `rbac_ta` VALUES (282,3);
INSERT INTO `rbac_ta` VALUES (282,4);
INSERT INTO `rbac_ta` VALUES (284,1);
INSERT INTO `rbac_ta` VALUES (284,2);
INSERT INTO `rbac_ta` VALUES (284,3);
INSERT INTO `rbac_ta` VALUES (284,4);
INSERT INTO `rbac_ta` VALUES (286,1);
INSERT INTO `rbac_ta` VALUES (286,2);
INSERT INTO `rbac_ta` VALUES (286,3);
INSERT INTO `rbac_ta` VALUES (286,4);
INSERT INTO `rbac_ta` VALUES (288,1);
INSERT INTO `rbac_ta` VALUES (288,2);
INSERT INTO `rbac_ta` VALUES (288,3);
INSERT INTO `rbac_ta` VALUES (288,4);
INSERT INTO `rbac_ta` VALUES (290,1);
INSERT INTO `rbac_ta` VALUES (290,2);
INSERT INTO `rbac_ta` VALUES (290,3);
INSERT INTO `rbac_ta` VALUES (290,4);
INSERT INTO `rbac_ta` VALUES (292,1);
INSERT INTO `rbac_ta` VALUES (292,2);
INSERT INTO `rbac_ta` VALUES (292,4);
INSERT INTO `rbac_ta` VALUES (292,6);
INSERT INTO `rbac_ta` VALUES (292,58);
INSERT INTO `rbac_ta` VALUES (293,1);
INSERT INTO `rbac_ta` VALUES (293,2);
INSERT INTO `rbac_ta` VALUES (293,3);
INSERT INTO `rbac_ta` VALUES (293,4);
INSERT INTO `rbac_ta` VALUES (295,1);
INSERT INTO `rbac_ta` VALUES (295,2);
INSERT INTO `rbac_ta` VALUES (295,3);
INSERT INTO `rbac_ta` VALUES (295,4);
INSERT INTO `rbac_ta` VALUES (297,1);
INSERT INTO `rbac_ta` VALUES (297,2);
INSERT INTO `rbac_ta` VALUES (297,3);
INSERT INTO `rbac_ta` VALUES (297,4);
INSERT INTO `rbac_ta` VALUES (299,1);
INSERT INTO `rbac_ta` VALUES (299,2);
INSERT INTO `rbac_ta` VALUES (299,3);
INSERT INTO `rbac_ta` VALUES (299,4);
INSERT INTO `rbac_ta` VALUES (301,1);
INSERT INTO `rbac_ta` VALUES (301,2);
INSERT INTO `rbac_ta` VALUES (301,3);
INSERT INTO `rbac_ta` VALUES (301,4);

--
-- Table structure for table `rbac_templates`
--

CREATE TABLE `rbac_templates` (
  `rol_id` int(11) NOT NULL DEFAULT 0,
  `type` char(5) NOT NULL DEFAULT '',
  `ops_id` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rol_id`,`parent`,`type`,`ops_id`)
) ;

--
-- Dumping data for table `rbac_templates`
--

INSERT INTO `rbac_templates` VALUES (3,'bibl',1,8);
INSERT INTO `rbac_templates` VALUES (3,'bibl',2,8);
INSERT INTO `rbac_templates` VALUES (3,'bibl',3,8);
INSERT INTO `rbac_templates` VALUES (3,'bibl',4,8);
INSERT INTO `rbac_templates` VALUES (3,'bibl',6,8);
INSERT INTO `rbac_templates` VALUES (3,'bibl',58,8);
INSERT INTO `rbac_templates` VALUES (3,'blog',1,8);
INSERT INTO `rbac_templates` VALUES (3,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (3,'blog',3,8);
INSERT INTO `rbac_templates` VALUES (3,'blog',4,8);
INSERT INTO `rbac_templates` VALUES (3,'blog',6,8);
INSERT INTO `rbac_templates` VALUES (3,'blog',58,8);
INSERT INTO `rbac_templates` VALUES (3,'blog',79,8);
INSERT INTO `rbac_templates` VALUES (3,'blog',96,8);
INSERT INTO `rbac_templates` VALUES (3,'book',1,8);
INSERT INTO `rbac_templates` VALUES (3,'book',2,8);
INSERT INTO `rbac_templates` VALUES (3,'book',3,8);
INSERT INTO `rbac_templates` VALUES (3,'book',4,8);
INSERT INTO `rbac_templates` VALUES (3,'book',6,8);
INSERT INTO `rbac_templates` VALUES (3,'book',58,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',2,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',3,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',4,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',6,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',16,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',17,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',18,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',19,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',20,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',21,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',22,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',24,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',25,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',27,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',28,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',31,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',32,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',42,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',43,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',50,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',58,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',59,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',60,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',65,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',67,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',68,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',71,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',73,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',74,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',75,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',76,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',78,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',81,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',82,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',90,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',101,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',106,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',108,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',116,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',119,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',126,8);
INSERT INTO `rbac_templates` VALUES (3,'cat',127,8);
INSERT INTO `rbac_templates` VALUES (3,'catr',1,8);
INSERT INTO `rbac_templates` VALUES (3,'catr',2,8);
INSERT INTO `rbac_templates` VALUES (3,'catr',4,8);
INSERT INTO `rbac_templates` VALUES (3,'catr',6,8);
INSERT INTO `rbac_templates` VALUES (3,'catr',58,8);
INSERT INTO `rbac_templates` VALUES (3,'chtr',1,8);
INSERT INTO `rbac_templates` VALUES (3,'chtr',2,8);
INSERT INTO `rbac_templates` VALUES (3,'chtr',3,8);
INSERT INTO `rbac_templates` VALUES (3,'chtr',4,8);
INSERT INTO `rbac_templates` VALUES (3,'chtr',6,8);
INSERT INTO `rbac_templates` VALUES (3,'chtr',52,8);
INSERT INTO `rbac_templates` VALUES (3,'chtr',58,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',1,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',2,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',3,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',4,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',6,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',83,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',84,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',85,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',86,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',87,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',88,8);
INSERT INTO `rbac_templates` VALUES (3,'cld',89,8);
INSERT INTO `rbac_templates` VALUES (3,'cmix',1,8);
INSERT INTO `rbac_templates` VALUES (3,'cmix',2,8);
INSERT INTO `rbac_templates` VALUES (3,'cmix',3,8);
INSERT INTO `rbac_templates` VALUES (3,'cmix',4,8);
INSERT INTO `rbac_templates` VALUES (3,'cmix',6,8);
INSERT INTO `rbac_templates` VALUES (3,'cmix',55,8);
INSERT INTO `rbac_templates` VALUES (3,'cmix',58,8);
INSERT INTO `rbac_templates` VALUES (3,'cmix',95,8);
INSERT INTO `rbac_templates` VALUES (3,'cmix',125,8);
INSERT INTO `rbac_templates` VALUES (3,'copa',1,8);
INSERT INTO `rbac_templates` VALUES (3,'copa',2,8);
INSERT INTO `rbac_templates` VALUES (3,'copa',3,8);
INSERT INTO `rbac_templates` VALUES (3,'copa',4,8);
INSERT INTO `rbac_templates` VALUES (3,'copa',6,8);
INSERT INTO `rbac_templates` VALUES (3,'copa',55,8);
INSERT INTO `rbac_templates` VALUES (3,'copa',58,8);
INSERT INTO `rbac_templates` VALUES (3,'copa',95,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',1,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',2,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',3,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',4,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',6,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',7,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',8,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',17,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',18,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',20,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',21,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',22,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',24,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',25,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',26,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',27,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',28,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',31,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',32,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',42,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',43,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',50,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',55,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',58,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',59,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',60,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',63,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',65,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',66,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',67,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',68,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',71,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',73,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',74,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',75,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',76,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',78,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',81,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',82,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',90,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',95,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',102,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',106,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',107,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',108,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',111,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',116,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',119,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',126,8);
INSERT INTO `rbac_templates` VALUES (3,'crs',127,8);
INSERT INTO `rbac_templates` VALUES (3,'crsr',1,8);
INSERT INTO `rbac_templates` VALUES (3,'crsr',2,8);
INSERT INTO `rbac_templates` VALUES (3,'crsr',4,8);
INSERT INTO `rbac_templates` VALUES (3,'crsr',6,8);
INSERT INTO `rbac_templates` VALUES (3,'crsr',55,8);
INSERT INTO `rbac_templates` VALUES (3,'crsr',58,8);
INSERT INTO `rbac_templates` VALUES (3,'crsr',95,8);
INSERT INTO `rbac_templates` VALUES (3,'dcl',1,8);
INSERT INTO `rbac_templates` VALUES (3,'dcl',2,8);
INSERT INTO `rbac_templates` VALUES (3,'dcl',3,8);
INSERT INTO `rbac_templates` VALUES (3,'dcl',4,8);
INSERT INTO `rbac_templates` VALUES (3,'dcl',6,8);
INSERT INTO `rbac_templates` VALUES (3,'dcl',58,8);
INSERT INTO `rbac_templates` VALUES (3,'dcl',64,8);
INSERT INTO `rbac_templates` VALUES (3,'dcl',77,8);
INSERT INTO `rbac_templates` VALUES (3,'exc',1,8);
INSERT INTO `rbac_templates` VALUES (3,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (3,'exc',3,8);
INSERT INTO `rbac_templates` VALUES (3,'exc',4,8);
INSERT INTO `rbac_templates` VALUES (3,'exc',6,8);
INSERT INTO `rbac_templates` VALUES (3,'exc',55,8);
INSERT INTO `rbac_templates` VALUES (3,'exc',58,8);
INSERT INTO `rbac_templates` VALUES (3,'exc',95,8);
INSERT INTO `rbac_templates` VALUES (3,'exc',114,8);
INSERT INTO `rbac_templates` VALUES (3,'feed',1,8);
INSERT INTO `rbac_templates` VALUES (3,'feed',3,8);
INSERT INTO `rbac_templates` VALUES (3,'feed',4,8);
INSERT INTO `rbac_templates` VALUES (3,'feed',6,8);
INSERT INTO `rbac_templates` VALUES (3,'feed',58,8);
INSERT INTO `rbac_templates` VALUES (3,'file',1,8);
INSERT INTO `rbac_templates` VALUES (3,'file',2,8);
INSERT INTO `rbac_templates` VALUES (3,'file',3,8);
INSERT INTO `rbac_templates` VALUES (3,'file',4,8);
INSERT INTO `rbac_templates` VALUES (3,'file',6,8);
INSERT INTO `rbac_templates` VALUES (3,'file',55,8);
INSERT INTO `rbac_templates` VALUES (3,'file',58,8);
INSERT INTO `rbac_templates` VALUES (3,'file',95,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',1,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',2,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',3,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',4,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',6,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',17,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',18,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',20,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',21,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',22,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',24,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',25,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',26,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',27,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',28,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',31,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',32,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',42,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',43,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',50,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',55,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',58,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',60,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',63,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',65,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',71,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',73,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',74,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',75,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',76,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',78,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',81,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',82,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',90,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',95,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',106,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',108,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',116,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',119,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',126,8);
INSERT INTO `rbac_templates` VALUES (3,'fold',127,8);
INSERT INTO `rbac_templates` VALUES (3,'frm',1,8);
INSERT INTO `rbac_templates` VALUES (3,'frm',2,8);
INSERT INTO `rbac_templates` VALUES (3,'frm',3,8);
INSERT INTO `rbac_templates` VALUES (3,'frm',4,8);
INSERT INTO `rbac_templates` VALUES (3,'frm',6,8);
INSERT INTO `rbac_templates` VALUES (3,'frm',9,8);
INSERT INTO `rbac_templates` VALUES (3,'frm',10,8);
INSERT INTO `rbac_templates` VALUES (3,'frm',58,8);
INSERT INTO `rbac_templates` VALUES (3,'frm',62,8);
INSERT INTO `rbac_templates` VALUES (3,'glo',1,8);
INSERT INTO `rbac_templates` VALUES (3,'glo',2,8);
INSERT INTO `rbac_templates` VALUES (3,'glo',3,8);
INSERT INTO `rbac_templates` VALUES (3,'glo',4,8);
INSERT INTO `rbac_templates` VALUES (3,'glo',6,8);
INSERT INTO `rbac_templates` VALUES (3,'glo',58,8);
INSERT INTO `rbac_templates` VALUES (3,'glo',64,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',1,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',2,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',3,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',4,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',6,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',7,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',17,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',18,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',20,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',21,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',22,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',23,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',24,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',25,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',26,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',27,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',28,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',31,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',32,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',42,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',43,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',50,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',55,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',58,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',59,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',60,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',63,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',65,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',66,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',67,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',68,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',71,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',73,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',74,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',75,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',76,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',78,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',81,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',82,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',90,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',95,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',102,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',106,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',107,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',108,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',111,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',116,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',119,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',126,8);
INSERT INTO `rbac_templates` VALUES (3,'grp',127,8);
INSERT INTO `rbac_templates` VALUES (3,'grpr',1,8);
INSERT INTO `rbac_templates` VALUES (3,'grpr',2,8);
INSERT INTO `rbac_templates` VALUES (3,'grpr',4,8);
INSERT INTO `rbac_templates` VALUES (3,'grpr',6,8);
INSERT INTO `rbac_templates` VALUES (3,'grpr',58,8);
INSERT INTO `rbac_templates` VALUES (3,'htlm',1,8);
INSERT INTO `rbac_templates` VALUES (3,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (3,'htlm',3,8);
INSERT INTO `rbac_templates` VALUES (3,'htlm',4,8);
INSERT INTO `rbac_templates` VALUES (3,'htlm',6,8);
INSERT INTO `rbac_templates` VALUES (3,'htlm',55,8);
INSERT INTO `rbac_templates` VALUES (3,'htlm',58,8);
INSERT INTO `rbac_templates` VALUES (3,'htlm',95,8);
INSERT INTO `rbac_templates` VALUES (3,'iass',1,8);
INSERT INTO `rbac_templates` VALUES (3,'iass',2,8);
INSERT INTO `rbac_templates` VALUES (3,'iass',3,8);
INSERT INTO `rbac_templates` VALUES (3,'iass',4,8);
INSERT INTO `rbac_templates` VALUES (3,'iass',6,8);
INSERT INTO `rbac_templates` VALUES (3,'iass',55,8);
INSERT INTO `rbac_templates` VALUES (3,'iass',58,8);
INSERT INTO `rbac_templates` VALUES (3,'iass',95,8);
INSERT INTO `rbac_templates` VALUES (3,'iass',109,8);
INSERT INTO `rbac_templates` VALUES (3,'itgr',1,8);
INSERT INTO `rbac_templates` VALUES (3,'itgr',2,8);
INSERT INTO `rbac_templates` VALUES (3,'itgr',3,8);
INSERT INTO `rbac_templates` VALUES (3,'itgr',4,8);
INSERT INTO `rbac_templates` VALUES (3,'itgr',6,8);
INSERT INTO `rbac_templates` VALUES (3,'itgr',58,8);
INSERT INTO `rbac_templates` VALUES (3,'lm',1,8);
INSERT INTO `rbac_templates` VALUES (3,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (3,'lm',3,8);
INSERT INTO `rbac_templates` VALUES (3,'lm',4,8);
INSERT INTO `rbac_templates` VALUES (3,'lm',6,8);
INSERT INTO `rbac_templates` VALUES (3,'lm',55,8);
INSERT INTO `rbac_templates` VALUES (3,'lm',58,8);
INSERT INTO `rbac_templates` VALUES (3,'lm',95,8);
INSERT INTO `rbac_templates` VALUES (3,'lso',1,8);
INSERT INTO `rbac_templates` VALUES (3,'lso',2,8);
INSERT INTO `rbac_templates` VALUES (3,'lso',3,8);
INSERT INTO `rbac_templates` VALUES (3,'lso',4,8);
INSERT INTO `rbac_templates` VALUES (3,'lso',6,8);
INSERT INTO `rbac_templates` VALUES (3,'lso',55,8);
INSERT INTO `rbac_templates` VALUES (3,'lso',58,8);
INSERT INTO `rbac_templates` VALUES (3,'lso',95,8);
INSERT INTO `rbac_templates` VALUES (3,'lti',1,8);
INSERT INTO `rbac_templates` VALUES (3,'lti',2,8);
INSERT INTO `rbac_templates` VALUES (3,'lti',3,8);
INSERT INTO `rbac_templates` VALUES (3,'lti',4,8);
INSERT INTO `rbac_templates` VALUES (3,'lti',6,8);
INSERT INTO `rbac_templates` VALUES (3,'lti',55,8);
INSERT INTO `rbac_templates` VALUES (3,'lti',58,8);
INSERT INTO `rbac_templates` VALUES (3,'lti',95,8);
INSERT INTO `rbac_templates` VALUES (3,'lti',125,8);
INSERT INTO `rbac_templates` VALUES (3,'mcst',1,8);
INSERT INTO `rbac_templates` VALUES (3,'mcst',2,8);
INSERT INTO `rbac_templates` VALUES (3,'mcst',3,8);
INSERT INTO `rbac_templates` VALUES (3,'mcst',4,8);
INSERT INTO `rbac_templates` VALUES (3,'mcst',6,8);
INSERT INTO `rbac_templates` VALUES (3,'mcst',55,8);
INSERT INTO `rbac_templates` VALUES (3,'mcst',58,8);
INSERT INTO `rbac_templates` VALUES (3,'mcst',95,8);
INSERT INTO `rbac_templates` VALUES (3,'mep',1,8);
INSERT INTO `rbac_templates` VALUES (3,'mep',2,8);
INSERT INTO `rbac_templates` VALUES (3,'mep',3,8);
INSERT INTO `rbac_templates` VALUES (3,'mep',4,8);
INSERT INTO `rbac_templates` VALUES (3,'mep',6,8);
INSERT INTO `rbac_templates` VALUES (3,'mep',58,8);
INSERT INTO `rbac_templates` VALUES (3,'poll',1,8);
INSERT INTO `rbac_templates` VALUES (3,'poll',3,8);
INSERT INTO `rbac_templates` VALUES (3,'poll',4,8);
INSERT INTO `rbac_templates` VALUES (3,'poll',6,8);
INSERT INTO `rbac_templates` VALUES (3,'poll',58,8);
INSERT INTO `rbac_templates` VALUES (3,'prg',1,8);
INSERT INTO `rbac_templates` VALUES (3,'prg',2,8);
INSERT INTO `rbac_templates` VALUES (3,'prg',3,8);
INSERT INTO `rbac_templates` VALUES (3,'prg',4,8);
INSERT INTO `rbac_templates` VALUES (3,'prg',6,8);
INSERT INTO `rbac_templates` VALUES (3,'prg',58,8);
INSERT INTO `rbac_templates` VALUES (3,'prg',101,8);
INSERT INTO `rbac_templates` VALUES (3,'prg',102,8);
INSERT INTO `rbac_templates` VALUES (3,'prtt',1,8);
INSERT INTO `rbac_templates` VALUES (3,'prtt',2,8);
INSERT INTO `rbac_templates` VALUES (3,'prtt',3,8);
INSERT INTO `rbac_templates` VALUES (3,'prtt',4,8);
INSERT INTO `rbac_templates` VALUES (3,'prtt',6,8);
INSERT INTO `rbac_templates` VALUES (3,'prtt',58,8);
INSERT INTO `rbac_templates` VALUES (3,'qpl',1,8);
INSERT INTO `rbac_templates` VALUES (3,'qpl',2,8);
INSERT INTO `rbac_templates` VALUES (3,'qpl',3,8);
INSERT INTO `rbac_templates` VALUES (3,'qpl',4,8);
INSERT INTO `rbac_templates` VALUES (3,'qpl',6,8);
INSERT INTO `rbac_templates` VALUES (3,'qpl',58,8);
INSERT INTO `rbac_templates` VALUES (3,'root',2,8);
INSERT INTO `rbac_templates` VALUES (3,'root',3,8);
INSERT INTO `rbac_templates` VALUES (3,'sahs',1,8);
INSERT INTO `rbac_templates` VALUES (3,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (3,'sahs',3,8);
INSERT INTO `rbac_templates` VALUES (3,'sahs',4,8);
INSERT INTO `rbac_templates` VALUES (3,'sahs',6,8);
INSERT INTO `rbac_templates` VALUES (3,'sahs',55,8);
INSERT INTO `rbac_templates` VALUES (3,'sahs',58,8);
INSERT INTO `rbac_templates` VALUES (3,'sahs',95,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',1,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',2,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',3,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',4,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',6,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',55,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',58,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',95,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',102,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',117,8);
INSERT INTO `rbac_templates` VALUES (3,'sess',118,8);
INSERT INTO `rbac_templates` VALUES (3,'spl',1,8);
INSERT INTO `rbac_templates` VALUES (3,'spl',2,8);
INSERT INTO `rbac_templates` VALUES (3,'spl',3,8);
INSERT INTO `rbac_templates` VALUES (3,'spl',4,8);
INSERT INTO `rbac_templates` VALUES (3,'spl',6,8);
INSERT INTO `rbac_templates` VALUES (3,'spl',58,8);
INSERT INTO `rbac_templates` VALUES (3,'svy',1,8);
INSERT INTO `rbac_templates` VALUES (3,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (3,'svy',3,8);
INSERT INTO `rbac_templates` VALUES (3,'svy',4,8);
INSERT INTO `rbac_templates` VALUES (3,'svy',6,8);
INSERT INTO `rbac_templates` VALUES (3,'svy',45,8);
INSERT INTO `rbac_templates` VALUES (3,'svy',55,8);
INSERT INTO `rbac_templates` VALUES (3,'svy',58,8);
INSERT INTO `rbac_templates` VALUES (3,'svy',95,8);
INSERT INTO `rbac_templates` VALUES (3,'svy',122,8);
INSERT INTO `rbac_templates` VALUES (3,'tst',1,8);
INSERT INTO `rbac_templates` VALUES (3,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (3,'tst',3,8);
INSERT INTO `rbac_templates` VALUES (3,'tst',4,8);
INSERT INTO `rbac_templates` VALUES (3,'tst',6,8);
INSERT INTO `rbac_templates` VALUES (3,'tst',55,8);
INSERT INTO `rbac_templates` VALUES (3,'tst',56,8);
INSERT INTO `rbac_templates` VALUES (3,'tst',58,8);
INSERT INTO `rbac_templates` VALUES (3,'tst',95,8);
INSERT INTO `rbac_templates` VALUES (3,'tst',115,8);
INSERT INTO `rbac_templates` VALUES (3,'webr',1,8);
INSERT INTO `rbac_templates` VALUES (3,'webr',2,8);
INSERT INTO `rbac_templates` VALUES (3,'webr',3,8);
INSERT INTO `rbac_templates` VALUES (3,'webr',4,8);
INSERT INTO `rbac_templates` VALUES (3,'webr',6,8);
INSERT INTO `rbac_templates` VALUES (3,'webr',58,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',1,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',2,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',3,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',4,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',6,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',58,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',64,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',94,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',97,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',98,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',99,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',100,8);
INSERT INTO `rbac_templates` VALUES (3,'wiki',112,8);
INSERT INTO `rbac_templates` VALUES (4,'bibl',2,8);
INSERT INTO `rbac_templates` VALUES (4,'bibl',3,8);
INSERT INTO `rbac_templates` VALUES (4,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (4,'blog',3,8);
INSERT INTO `rbac_templates` VALUES (4,'book',2,8);
INSERT INTO `rbac_templates` VALUES (4,'book',3,8);
INSERT INTO `rbac_templates` VALUES (4,'cat',2,8);
INSERT INTO `rbac_templates` VALUES (4,'cat',3,8);
INSERT INTO `rbac_templates` VALUES (4,'catr',2,8);
INSERT INTO `rbac_templates` VALUES (4,'chtr',2,8);
INSERT INTO `rbac_templates` VALUES (4,'chtr',3,8);
INSERT INTO `rbac_templates` VALUES (4,'cmix',2,8);
INSERT INTO `rbac_templates` VALUES (4,'cmix',3,8);
INSERT INTO `rbac_templates` VALUES (4,'copa',2,8);
INSERT INTO `rbac_templates` VALUES (4,'copa',3,8);
INSERT INTO `rbac_templates` VALUES (4,'crs',2,8);
INSERT INTO `rbac_templates` VALUES (4,'crs',7,8);
INSERT INTO `rbac_templates` VALUES (4,'crsr',2,8);
INSERT INTO `rbac_templates` VALUES (4,'dbk',2,8);
INSERT INTO `rbac_templates` VALUES (4,'dbk',3,8);
INSERT INTO `rbac_templates` VALUES (4,'dcl',2,8);
INSERT INTO `rbac_templates` VALUES (4,'dcl',3,8);
INSERT INTO `rbac_templates` VALUES (4,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (4,'exc',3,8);
INSERT INTO `rbac_templates` VALUES (4,'feed',3,8);
INSERT INTO `rbac_templates` VALUES (4,'file',2,8);
INSERT INTO `rbac_templates` VALUES (4,'file',3,8);
INSERT INTO `rbac_templates` VALUES (4,'fold',2,8);
INSERT INTO `rbac_templates` VALUES (4,'fold',3,8);
INSERT INTO `rbac_templates` VALUES (4,'frm',2,8);
INSERT INTO `rbac_templates` VALUES (4,'frm',3,8);
INSERT INTO `rbac_templates` VALUES (4,'frm',9,8);
INSERT INTO `rbac_templates` VALUES (4,'frm',62,8);
INSERT INTO `rbac_templates` VALUES (4,'glo',2,8);
INSERT INTO `rbac_templates` VALUES (4,'glo',3,8);
INSERT INTO `rbac_templates` VALUES (4,'grp',2,8);
INSERT INTO `rbac_templates` VALUES (4,'grp',7,8);
INSERT INTO `rbac_templates` VALUES (4,'grpr',2,8);
INSERT INTO `rbac_templates` VALUES (4,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (4,'htlm',3,8);
INSERT INTO `rbac_templates` VALUES (4,'iass',2,8);
INSERT INTO `rbac_templates` VALUES (4,'iass',3,8);
INSERT INTO `rbac_templates` VALUES (4,'itgr',2,8);
INSERT INTO `rbac_templates` VALUES (4,'itgr',3,8);
INSERT INTO `rbac_templates` VALUES (4,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (4,'lm',3,8);
INSERT INTO `rbac_templates` VALUES (4,'lso',2,8);
INSERT INTO `rbac_templates` VALUES (4,'lso',3,8);
INSERT INTO `rbac_templates` VALUES (4,'lti',2,8);
INSERT INTO `rbac_templates` VALUES (4,'lti',3,8);
INSERT INTO `rbac_templates` VALUES (4,'mail',30,8);
INSERT INTO `rbac_templates` VALUES (4,'mcst',2,8);
INSERT INTO `rbac_templates` VALUES (4,'mcst',3,8);
INSERT INTO `rbac_templates` VALUES (4,'poll',3,8);
INSERT INTO `rbac_templates` VALUES (4,'prg',2,8);
INSERT INTO `rbac_templates` VALUES (4,'prg',3,8);
INSERT INTO `rbac_templates` VALUES (4,'root',2,8);
INSERT INTO `rbac_templates` VALUES (4,'root',3,8);
INSERT INTO `rbac_templates` VALUES (4,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (4,'sahs',3,8);
INSERT INTO `rbac_templates` VALUES (4,'seas',51,8);
INSERT INTO `rbac_templates` VALUES (4,'sess',2,8);
INSERT INTO `rbac_templates` VALUES (4,'sess',3,8);
INSERT INTO `rbac_templates` VALUES (4,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (4,'svy',3,8);
INSERT INTO `rbac_templates` VALUES (4,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (4,'tst',3,8);
INSERT INTO `rbac_templates` VALUES (4,'webr',2,8);
INSERT INTO `rbac_templates` VALUES (4,'webr',3,8);
INSERT INTO `rbac_templates` VALUES (4,'wiki',2,8);
INSERT INTO `rbac_templates` VALUES (4,'wiki',3,8);
INSERT INTO `rbac_templates` VALUES (4,'wiki',64,8);
INSERT INTO `rbac_templates` VALUES (5,'bibl',2,8);
INSERT INTO `rbac_templates` VALUES (5,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (5,'book',2,8);
INSERT INTO `rbac_templates` VALUES (5,'cat',2,8);
INSERT INTO `rbac_templates` VALUES (5,'catr',2,8);
INSERT INTO `rbac_templates` VALUES (5,'chtr',2,8);
INSERT INTO `rbac_templates` VALUES (5,'crs',2,8);
INSERT INTO `rbac_templates` VALUES (5,'crsr',2,8);
INSERT INTO `rbac_templates` VALUES (5,'dbk',2,8);
INSERT INTO `rbac_templates` VALUES (5,'dcl',2,8);
INSERT INTO `rbac_templates` VALUES (5,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (5,'feed',3,8);
INSERT INTO `rbac_templates` VALUES (5,'file',2,8);
INSERT INTO `rbac_templates` VALUES (5,'frm',2,8);
INSERT INTO `rbac_templates` VALUES (5,'glo',2,8);
INSERT INTO `rbac_templates` VALUES (5,'grp',2,8);
INSERT INTO `rbac_templates` VALUES (5,'grpr',2,8);
INSERT INTO `rbac_templates` VALUES (5,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (5,'itgr',2,8);
INSERT INTO `rbac_templates` VALUES (5,'itgr',3,8);
INSERT INTO `rbac_templates` VALUES (5,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (5,'mcst',2,8);
INSERT INTO `rbac_templates` VALUES (5,'prg',2,8);
INSERT INTO `rbac_templates` VALUES (5,'root',2,8);
INSERT INTO `rbac_templates` VALUES (5,'root',3,8);
INSERT INTO `rbac_templates` VALUES (5,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (5,'seas',51,8);
INSERT INTO `rbac_templates` VALUES (5,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (5,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (5,'webr',2,8);
INSERT INTO `rbac_templates` VALUES (5,'wiki',2,8);
INSERT INTO `rbac_templates` VALUES (14,'cat',2,8);
INSERT INTO `rbac_templates` VALUES (14,'root',2,8);
INSERT INTO `rbac_templates` VALUES (14,'root',3,8);
INSERT INTO `rbac_templates` VALUES (14,'seas',51,8);
INSERT INTO `rbac_templates` VALUES (80,'bibl',1,8);
INSERT INTO `rbac_templates` VALUES (80,'bibl',2,8);
INSERT INTO `rbac_templates` VALUES (80,'bibl',3,8);
INSERT INTO `rbac_templates` VALUES (80,'bibl',4,8);
INSERT INTO `rbac_templates` VALUES (80,'bibl',6,8);
INSERT INTO `rbac_templates` VALUES (80,'bibl',58,8);
INSERT INTO `rbac_templates` VALUES (80,'blog',1,8);
INSERT INTO `rbac_templates` VALUES (80,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (80,'blog',3,8);
INSERT INTO `rbac_templates` VALUES (80,'blog',4,8);
INSERT INTO `rbac_templates` VALUES (80,'blog',6,8);
INSERT INTO `rbac_templates` VALUES (80,'blog',58,8);
INSERT INTO `rbac_templates` VALUES (80,'blog',79,8);
INSERT INTO `rbac_templates` VALUES (80,'book',1,8);
INSERT INTO `rbac_templates` VALUES (80,'book',2,8);
INSERT INTO `rbac_templates` VALUES (80,'book',3,8);
INSERT INTO `rbac_templates` VALUES (80,'book',4,8);
INSERT INTO `rbac_templates` VALUES (80,'book',6,8);
INSERT INTO `rbac_templates` VALUES (80,'book',58,8);
INSERT INTO `rbac_templates` VALUES (80,'catr',1,8);
INSERT INTO `rbac_templates` VALUES (80,'catr',2,8);
INSERT INTO `rbac_templates` VALUES (80,'catr',4,8);
INSERT INTO `rbac_templates` VALUES (80,'catr',6,8);
INSERT INTO `rbac_templates` VALUES (80,'catr',58,8);
INSERT INTO `rbac_templates` VALUES (80,'chtr',1,8);
INSERT INTO `rbac_templates` VALUES (80,'chtr',2,8);
INSERT INTO `rbac_templates` VALUES (80,'chtr',3,8);
INSERT INTO `rbac_templates` VALUES (80,'chtr',4,8);
INSERT INTO `rbac_templates` VALUES (80,'chtr',6,8);
INSERT INTO `rbac_templates` VALUES (80,'chtr',52,8);
INSERT INTO `rbac_templates` VALUES (80,'chtr',58,8);
INSERT INTO `rbac_templates` VALUES (80,'cmix',1,8);
INSERT INTO `rbac_templates` VALUES (80,'cmix',2,8);
INSERT INTO `rbac_templates` VALUES (80,'cmix',3,8);
INSERT INTO `rbac_templates` VALUES (80,'cmix',4,8);
INSERT INTO `rbac_templates` VALUES (80,'cmix',6,8);
INSERT INTO `rbac_templates` VALUES (80,'cmix',55,8);
INSERT INTO `rbac_templates` VALUES (80,'cmix',58,8);
INSERT INTO `rbac_templates` VALUES (80,'cmix',95,8);
INSERT INTO `rbac_templates` VALUES (80,'cmix',125,8);
INSERT INTO `rbac_templates` VALUES (80,'copa',1,8);
INSERT INTO `rbac_templates` VALUES (80,'copa',2,8);
INSERT INTO `rbac_templates` VALUES (80,'copa',3,8);
INSERT INTO `rbac_templates` VALUES (80,'copa',4,8);
INSERT INTO `rbac_templates` VALUES (80,'copa',6,8);
INSERT INTO `rbac_templates` VALUES (80,'copa',55,8);
INSERT INTO `rbac_templates` VALUES (80,'copa',58,8);
INSERT INTO `rbac_templates` VALUES (80,'copa',95,8);
INSERT INTO `rbac_templates` VALUES (80,'crsr',1,8);
INSERT INTO `rbac_templates` VALUES (80,'crsr',2,8);
INSERT INTO `rbac_templates` VALUES (80,'crsr',4,8);
INSERT INTO `rbac_templates` VALUES (80,'crsr',6,8);
INSERT INTO `rbac_templates` VALUES (80,'crsr',55,8);
INSERT INTO `rbac_templates` VALUES (80,'crsr',58,8);
INSERT INTO `rbac_templates` VALUES (80,'crsr',95,8);
INSERT INTO `rbac_templates` VALUES (80,'dcl',1,8);
INSERT INTO `rbac_templates` VALUES (80,'dcl',2,8);
INSERT INTO `rbac_templates` VALUES (80,'dcl',3,8);
INSERT INTO `rbac_templates` VALUES (80,'dcl',4,8);
INSERT INTO `rbac_templates` VALUES (80,'dcl',6,8);
INSERT INTO `rbac_templates` VALUES (80,'dcl',58,8);
INSERT INTO `rbac_templates` VALUES (80,'dcl',64,8);
INSERT INTO `rbac_templates` VALUES (80,'dcl',77,8);
INSERT INTO `rbac_templates` VALUES (80,'exc',1,8);
INSERT INTO `rbac_templates` VALUES (80,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (80,'exc',3,8);
INSERT INTO `rbac_templates` VALUES (80,'exc',4,8);
INSERT INTO `rbac_templates` VALUES (80,'exc',6,8);
INSERT INTO `rbac_templates` VALUES (80,'exc',55,8);
INSERT INTO `rbac_templates` VALUES (80,'exc',58,8);
INSERT INTO `rbac_templates` VALUES (80,'exc',95,8);
INSERT INTO `rbac_templates` VALUES (80,'exc',114,8);
INSERT INTO `rbac_templates` VALUES (80,'feed',1,8);
INSERT INTO `rbac_templates` VALUES (80,'feed',3,8);
INSERT INTO `rbac_templates` VALUES (80,'feed',4,8);
INSERT INTO `rbac_templates` VALUES (80,'feed',6,8);
INSERT INTO `rbac_templates` VALUES (80,'feed',58,8);
INSERT INTO `rbac_templates` VALUES (80,'file',1,8);
INSERT INTO `rbac_templates` VALUES (80,'file',2,8);
INSERT INTO `rbac_templates` VALUES (80,'file',3,8);
INSERT INTO `rbac_templates` VALUES (80,'file',4,8);
INSERT INTO `rbac_templates` VALUES (80,'file',6,8);
INSERT INTO `rbac_templates` VALUES (80,'file',55,8);
INSERT INTO `rbac_templates` VALUES (80,'file',58,8);
INSERT INTO `rbac_templates` VALUES (80,'file',95,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',1,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',2,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',3,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',4,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',6,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',17,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',18,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',20,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',21,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',22,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',24,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',25,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',26,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',27,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',28,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',31,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',32,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',42,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',43,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',50,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',55,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',58,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',60,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',63,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',65,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',71,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',73,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',74,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',75,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',76,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',78,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',81,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',82,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',90,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',95,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',106,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',108,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',116,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',119,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',126,8);
INSERT INTO `rbac_templates` VALUES (80,'fold',127,8);
INSERT INTO `rbac_templates` VALUES (80,'frm',1,8);
INSERT INTO `rbac_templates` VALUES (80,'frm',2,8);
INSERT INTO `rbac_templates` VALUES (80,'frm',3,8);
INSERT INTO `rbac_templates` VALUES (80,'frm',4,8);
INSERT INTO `rbac_templates` VALUES (80,'frm',6,8);
INSERT INTO `rbac_templates` VALUES (80,'frm',9,8);
INSERT INTO `rbac_templates` VALUES (80,'frm',10,8);
INSERT INTO `rbac_templates` VALUES (80,'frm',58,8);
INSERT INTO `rbac_templates` VALUES (80,'frm',62,8);
INSERT INTO `rbac_templates` VALUES (80,'glo',1,8);
INSERT INTO `rbac_templates` VALUES (80,'glo',2,8);
INSERT INTO `rbac_templates` VALUES (80,'glo',3,8);
INSERT INTO `rbac_templates` VALUES (80,'glo',4,8);
INSERT INTO `rbac_templates` VALUES (80,'glo',6,8);
INSERT INTO `rbac_templates` VALUES (80,'glo',58,8);
INSERT INTO `rbac_templates` VALUES (80,'glo',64,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',1,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',2,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',3,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',4,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',6,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',7,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',8,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',17,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',18,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',20,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',21,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',22,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',24,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',25,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',26,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',27,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',28,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',31,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',32,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',42,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',43,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',50,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',55,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',58,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',59,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',60,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',63,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',65,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',66,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',67,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',68,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',71,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',73,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',74,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',75,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',76,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',78,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',81,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',82,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',90,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',95,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',102,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',106,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',107,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',108,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',111,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',116,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',119,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',126,8);
INSERT INTO `rbac_templates` VALUES (80,'grp',127,8);
INSERT INTO `rbac_templates` VALUES (80,'grpr',1,8);
INSERT INTO `rbac_templates` VALUES (80,'grpr',2,8);
INSERT INTO `rbac_templates` VALUES (80,'grpr',4,8);
INSERT INTO `rbac_templates` VALUES (80,'grpr',6,8);
INSERT INTO `rbac_templates` VALUES (80,'grpr',58,8);
INSERT INTO `rbac_templates` VALUES (80,'htlm',1,8);
INSERT INTO `rbac_templates` VALUES (80,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (80,'htlm',3,8);
INSERT INTO `rbac_templates` VALUES (80,'htlm',4,8);
INSERT INTO `rbac_templates` VALUES (80,'htlm',6,8);
INSERT INTO `rbac_templates` VALUES (80,'htlm',55,8);
INSERT INTO `rbac_templates` VALUES (80,'htlm',58,8);
INSERT INTO `rbac_templates` VALUES (80,'htlm',95,8);
INSERT INTO `rbac_templates` VALUES (80,'iass',1,8);
INSERT INTO `rbac_templates` VALUES (80,'iass',2,8);
INSERT INTO `rbac_templates` VALUES (80,'iass',3,8);
INSERT INTO `rbac_templates` VALUES (80,'iass',4,8);
INSERT INTO `rbac_templates` VALUES (80,'iass',6,8);
INSERT INTO `rbac_templates` VALUES (80,'iass',55,8);
INSERT INTO `rbac_templates` VALUES (80,'iass',58,8);
INSERT INTO `rbac_templates` VALUES (80,'iass',95,8);
INSERT INTO `rbac_templates` VALUES (80,'iass',109,8);
INSERT INTO `rbac_templates` VALUES (80,'itgr',1,8);
INSERT INTO `rbac_templates` VALUES (80,'itgr',2,8);
INSERT INTO `rbac_templates` VALUES (80,'itgr',3,8);
INSERT INTO `rbac_templates` VALUES (80,'itgr',4,8);
INSERT INTO `rbac_templates` VALUES (80,'itgr',6,8);
INSERT INTO `rbac_templates` VALUES (80,'itgr',58,8);
INSERT INTO `rbac_templates` VALUES (80,'lm',1,8);
INSERT INTO `rbac_templates` VALUES (80,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (80,'lm',3,8);
INSERT INTO `rbac_templates` VALUES (80,'lm',4,8);
INSERT INTO `rbac_templates` VALUES (80,'lm',6,8);
INSERT INTO `rbac_templates` VALUES (80,'lm',55,8);
INSERT INTO `rbac_templates` VALUES (80,'lm',58,8);
INSERT INTO `rbac_templates` VALUES (80,'lm',95,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',1,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',2,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',3,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',4,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',6,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',20,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',21,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',24,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',25,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',27,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',32,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',42,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',55,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',58,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',95,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',102,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',108,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',116,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',120,8);
INSERT INTO `rbac_templates` VALUES (80,'lso',121,8);
INSERT INTO `rbac_templates` VALUES (80,'lti',1,8);
INSERT INTO `rbac_templates` VALUES (80,'lti',2,8);
INSERT INTO `rbac_templates` VALUES (80,'lti',3,8);
INSERT INTO `rbac_templates` VALUES (80,'lti',4,8);
INSERT INTO `rbac_templates` VALUES (80,'lti',6,8);
INSERT INTO `rbac_templates` VALUES (80,'lti',55,8);
INSERT INTO `rbac_templates` VALUES (80,'lti',58,8);
INSERT INTO `rbac_templates` VALUES (80,'lti',95,8);
INSERT INTO `rbac_templates` VALUES (80,'lti',125,8);
INSERT INTO `rbac_templates` VALUES (80,'mcst',1,8);
INSERT INTO `rbac_templates` VALUES (80,'mcst',2,8);
INSERT INTO `rbac_templates` VALUES (80,'mcst',3,8);
INSERT INTO `rbac_templates` VALUES (80,'mcst',4,8);
INSERT INTO `rbac_templates` VALUES (80,'mcst',6,8);
INSERT INTO `rbac_templates` VALUES (80,'mcst',55,8);
INSERT INTO `rbac_templates` VALUES (80,'mcst',58,8);
INSERT INTO `rbac_templates` VALUES (80,'mcst',95,8);
INSERT INTO `rbac_templates` VALUES (80,'mep',1,8);
INSERT INTO `rbac_templates` VALUES (80,'mep',2,8);
INSERT INTO `rbac_templates` VALUES (80,'mep',3,8);
INSERT INTO `rbac_templates` VALUES (80,'mep',4,8);
INSERT INTO `rbac_templates` VALUES (80,'mep',6,8);
INSERT INTO `rbac_templates` VALUES (80,'mep',58,8);
INSERT INTO `rbac_templates` VALUES (80,'poll',1,8);
INSERT INTO `rbac_templates` VALUES (80,'poll',3,8);
INSERT INTO `rbac_templates` VALUES (80,'poll',4,8);
INSERT INTO `rbac_templates` VALUES (80,'poll',6,8);
INSERT INTO `rbac_templates` VALUES (80,'poll',58,8);
INSERT INTO `rbac_templates` VALUES (80,'prtt',1,8);
INSERT INTO `rbac_templates` VALUES (80,'prtt',2,8);
INSERT INTO `rbac_templates` VALUES (80,'prtt',3,8);
INSERT INTO `rbac_templates` VALUES (80,'prtt',4,8);
INSERT INTO `rbac_templates` VALUES (80,'prtt',6,8);
INSERT INTO `rbac_templates` VALUES (80,'prtt',58,8);
INSERT INTO `rbac_templates` VALUES (80,'qpl',1,8);
INSERT INTO `rbac_templates` VALUES (80,'qpl',2,8);
INSERT INTO `rbac_templates` VALUES (80,'qpl',3,8);
INSERT INTO `rbac_templates` VALUES (80,'qpl',4,8);
INSERT INTO `rbac_templates` VALUES (80,'qpl',6,8);
INSERT INTO `rbac_templates` VALUES (80,'qpl',58,8);
INSERT INTO `rbac_templates` VALUES (80,'sahs',1,8);
INSERT INTO `rbac_templates` VALUES (80,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (80,'sahs',3,8);
INSERT INTO `rbac_templates` VALUES (80,'sahs',4,8);
INSERT INTO `rbac_templates` VALUES (80,'sahs',6,8);
INSERT INTO `rbac_templates` VALUES (80,'sahs',55,8);
INSERT INTO `rbac_templates` VALUES (80,'sahs',58,8);
INSERT INTO `rbac_templates` VALUES (80,'sahs',95,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',1,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',2,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',3,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',4,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',6,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',55,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',58,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',95,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',102,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',117,8);
INSERT INTO `rbac_templates` VALUES (80,'sess',118,8);
INSERT INTO `rbac_templates` VALUES (80,'spl',1,8);
INSERT INTO `rbac_templates` VALUES (80,'spl',2,8);
INSERT INTO `rbac_templates` VALUES (80,'spl',3,8);
INSERT INTO `rbac_templates` VALUES (80,'spl',4,8);
INSERT INTO `rbac_templates` VALUES (80,'spl',6,8);
INSERT INTO `rbac_templates` VALUES (80,'spl',58,8);
INSERT INTO `rbac_templates` VALUES (80,'svy',1,8);
INSERT INTO `rbac_templates` VALUES (80,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (80,'svy',3,8);
INSERT INTO `rbac_templates` VALUES (80,'svy',4,8);
INSERT INTO `rbac_templates` VALUES (80,'svy',6,8);
INSERT INTO `rbac_templates` VALUES (80,'svy',45,8);
INSERT INTO `rbac_templates` VALUES (80,'svy',55,8);
INSERT INTO `rbac_templates` VALUES (80,'svy',58,8);
INSERT INTO `rbac_templates` VALUES (80,'svy',95,8);
INSERT INTO `rbac_templates` VALUES (80,'svy',122,8);
INSERT INTO `rbac_templates` VALUES (80,'tst',1,8);
INSERT INTO `rbac_templates` VALUES (80,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (80,'tst',3,8);
INSERT INTO `rbac_templates` VALUES (80,'tst',4,8);
INSERT INTO `rbac_templates` VALUES (80,'tst',6,8);
INSERT INTO `rbac_templates` VALUES (80,'tst',55,8);
INSERT INTO `rbac_templates` VALUES (80,'tst',56,8);
INSERT INTO `rbac_templates` VALUES (80,'tst',58,8);
INSERT INTO `rbac_templates` VALUES (80,'tst',95,8);
INSERT INTO `rbac_templates` VALUES (80,'tst',115,8);
INSERT INTO `rbac_templates` VALUES (80,'webr',1,8);
INSERT INTO `rbac_templates` VALUES (80,'webr',2,8);
INSERT INTO `rbac_templates` VALUES (80,'webr',3,8);
INSERT INTO `rbac_templates` VALUES (80,'webr',4,8);
INSERT INTO `rbac_templates` VALUES (80,'webr',6,8);
INSERT INTO `rbac_templates` VALUES (80,'webr',58,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',1,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',2,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',3,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',4,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',6,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',58,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',64,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',94,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',97,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',98,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',99,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',100,8);
INSERT INTO `rbac_templates` VALUES (80,'wiki',112,8);
INSERT INTO `rbac_templates` VALUES (81,'bibl',2,8);
INSERT INTO `rbac_templates` VALUES (81,'bibl',3,8);
INSERT INTO `rbac_templates` VALUES (81,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (81,'blog',3,8);
INSERT INTO `rbac_templates` VALUES (81,'book',2,8);
INSERT INTO `rbac_templates` VALUES (81,'book',3,8);
INSERT INTO `rbac_templates` VALUES (81,'catr',2,8);
INSERT INTO `rbac_templates` VALUES (81,'chtr',2,8);
INSERT INTO `rbac_templates` VALUES (81,'chtr',3,8);
INSERT INTO `rbac_templates` VALUES (81,'cmix',2,8);
INSERT INTO `rbac_templates` VALUES (81,'cmix',3,8);
INSERT INTO `rbac_templates` VALUES (81,'copa',2,8);
INSERT INTO `rbac_templates` VALUES (81,'copa',3,8);
INSERT INTO `rbac_templates` VALUES (81,'crsr',2,8);
INSERT INTO `rbac_templates` VALUES (81,'dcl',2,8);
INSERT INTO `rbac_templates` VALUES (81,'dcl',3,8);
INSERT INTO `rbac_templates` VALUES (81,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (81,'exc',3,8);
INSERT INTO `rbac_templates` VALUES (81,'feed',3,8);
INSERT INTO `rbac_templates` VALUES (81,'file',2,8);
INSERT INTO `rbac_templates` VALUES (81,'file',3,8);
INSERT INTO `rbac_templates` VALUES (81,'fold',2,8);
INSERT INTO `rbac_templates` VALUES (81,'fold',3,8);
INSERT INTO `rbac_templates` VALUES (81,'frm',2,8);
INSERT INTO `rbac_templates` VALUES (81,'frm',3,8);
INSERT INTO `rbac_templates` VALUES (81,'frm',9,8);
INSERT INTO `rbac_templates` VALUES (81,'frm',62,8);
INSERT INTO `rbac_templates` VALUES (81,'glo',2,8);
INSERT INTO `rbac_templates` VALUES (81,'glo',3,8);
INSERT INTO `rbac_templates` VALUES (81,'grp',2,8);
INSERT INTO `rbac_templates` VALUES (81,'grp',3,8);
INSERT INTO `rbac_templates` VALUES (81,'grp',7,8);
INSERT INTO `rbac_templates` VALUES (81,'grp',8,8);
INSERT INTO `rbac_templates` VALUES (81,'grpr',2,8);
INSERT INTO `rbac_templates` VALUES (81,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (81,'htlm',3,8);
INSERT INTO `rbac_templates` VALUES (81,'iass',2,8);
INSERT INTO `rbac_templates` VALUES (81,'iass',3,8);
INSERT INTO `rbac_templates` VALUES (81,'itgr',2,8);
INSERT INTO `rbac_templates` VALUES (81,'itgr',3,8);
INSERT INTO `rbac_templates` VALUES (81,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (81,'lm',3,8);
INSERT INTO `rbac_templates` VALUES (81,'lso',2,8);
INSERT INTO `rbac_templates` VALUES (81,'lso',3,8);
INSERT INTO `rbac_templates` VALUES (81,'lso',120,8);
INSERT INTO `rbac_templates` VALUES (81,'lti',2,8);
INSERT INTO `rbac_templates` VALUES (81,'lti',3,8);
INSERT INTO `rbac_templates` VALUES (81,'mcst',2,8);
INSERT INTO `rbac_templates` VALUES (81,'mcst',3,8);
INSERT INTO `rbac_templates` VALUES (81,'poll',3,8);
INSERT INTO `rbac_templates` VALUES (81,'prtt',2,8);
INSERT INTO `rbac_templates` VALUES (81,'prtt',3,8);
INSERT INTO `rbac_templates` VALUES (81,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (81,'sahs',3,8);
INSERT INTO `rbac_templates` VALUES (81,'sess',2,8);
INSERT INTO `rbac_templates` VALUES (81,'sess',3,8);
INSERT INTO `rbac_templates` VALUES (81,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (81,'svy',3,8);
INSERT INTO `rbac_templates` VALUES (81,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (81,'tst',3,8);
INSERT INTO `rbac_templates` VALUES (81,'webr',2,8);
INSERT INTO `rbac_templates` VALUES (81,'webr',3,8);
INSERT INTO `rbac_templates` VALUES (81,'wiki',2,8);
INSERT INTO `rbac_templates` VALUES (81,'wiki',3,8);
INSERT INTO `rbac_templates` VALUES (81,'wiki',64,8);
INSERT INTO `rbac_templates` VALUES (83,'grp',2,8);
INSERT INTO `rbac_templates` VALUES (83,'grp',7,8);
INSERT INTO `rbac_templates` VALUES (110,'bibl',1,8);
INSERT INTO `rbac_templates` VALUES (110,'bibl',2,8);
INSERT INTO `rbac_templates` VALUES (110,'bibl',3,8);
INSERT INTO `rbac_templates` VALUES (110,'bibl',4,8);
INSERT INTO `rbac_templates` VALUES (110,'bibl',6,8);
INSERT INTO `rbac_templates` VALUES (110,'bibl',58,8);
INSERT INTO `rbac_templates` VALUES (110,'blog',1,8);
INSERT INTO `rbac_templates` VALUES (110,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (110,'blog',3,8);
INSERT INTO `rbac_templates` VALUES (110,'blog',4,8);
INSERT INTO `rbac_templates` VALUES (110,'blog',6,8);
INSERT INTO `rbac_templates` VALUES (110,'blog',58,8);
INSERT INTO `rbac_templates` VALUES (110,'blog',79,8);
INSERT INTO `rbac_templates` VALUES (110,'blog',96,8);
INSERT INTO `rbac_templates` VALUES (110,'book',1,8);
INSERT INTO `rbac_templates` VALUES (110,'book',2,8);
INSERT INTO `rbac_templates` VALUES (110,'book',3,8);
INSERT INTO `rbac_templates` VALUES (110,'book',4,8);
INSERT INTO `rbac_templates` VALUES (110,'book',6,8);
INSERT INTO `rbac_templates` VALUES (110,'book',58,8);
INSERT INTO `rbac_templates` VALUES (110,'catr',1,8);
INSERT INTO `rbac_templates` VALUES (110,'catr',2,8);
INSERT INTO `rbac_templates` VALUES (110,'catr',4,8);
INSERT INTO `rbac_templates` VALUES (110,'catr',6,8);
INSERT INTO `rbac_templates` VALUES (110,'catr',58,8);
INSERT INTO `rbac_templates` VALUES (110,'chtr',1,8);
INSERT INTO `rbac_templates` VALUES (110,'chtr',2,8);
INSERT INTO `rbac_templates` VALUES (110,'chtr',3,8);
INSERT INTO `rbac_templates` VALUES (110,'chtr',4,8);
INSERT INTO `rbac_templates` VALUES (110,'chtr',6,8);
INSERT INTO `rbac_templates` VALUES (110,'chtr',52,8);
INSERT INTO `rbac_templates` VALUES (110,'chtr',58,8);
INSERT INTO `rbac_templates` VALUES (110,'cmix',1,8);
INSERT INTO `rbac_templates` VALUES (110,'cmix',2,8);
INSERT INTO `rbac_templates` VALUES (110,'cmix',3,8);
INSERT INTO `rbac_templates` VALUES (110,'cmix',4,8);
INSERT INTO `rbac_templates` VALUES (110,'cmix',6,8);
INSERT INTO `rbac_templates` VALUES (110,'cmix',55,8);
INSERT INTO `rbac_templates` VALUES (110,'cmix',58,8);
INSERT INTO `rbac_templates` VALUES (110,'cmix',95,8);
INSERT INTO `rbac_templates` VALUES (110,'cmix',125,8);
INSERT INTO `rbac_templates` VALUES (110,'copa',1,8);
INSERT INTO `rbac_templates` VALUES (110,'copa',2,8);
INSERT INTO `rbac_templates` VALUES (110,'copa',3,8);
INSERT INTO `rbac_templates` VALUES (110,'copa',4,8);
INSERT INTO `rbac_templates` VALUES (110,'copa',6,8);
INSERT INTO `rbac_templates` VALUES (110,'copa',55,8);
INSERT INTO `rbac_templates` VALUES (110,'copa',58,8);
INSERT INTO `rbac_templates` VALUES (110,'copa',95,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',1,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',2,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',3,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',4,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',6,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',7,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',8,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',17,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',18,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',20,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',21,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',22,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',24,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',25,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',26,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',27,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',28,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',31,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',32,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',42,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',43,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',50,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',55,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',58,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',59,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',60,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',63,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',65,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',66,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',67,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',68,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',71,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',73,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',74,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',75,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',76,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',78,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',81,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',82,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',90,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',95,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',102,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',106,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',107,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',108,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',111,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',116,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',119,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',126,8);
INSERT INTO `rbac_templates` VALUES (110,'crs',127,8);
INSERT INTO `rbac_templates` VALUES (110,'crsr',1,8);
INSERT INTO `rbac_templates` VALUES (110,'crsr',2,8);
INSERT INTO `rbac_templates` VALUES (110,'crsr',4,8);
INSERT INTO `rbac_templates` VALUES (110,'crsr',6,8);
INSERT INTO `rbac_templates` VALUES (110,'crsr',55,8);
INSERT INTO `rbac_templates` VALUES (110,'crsr',58,8);
INSERT INTO `rbac_templates` VALUES (110,'crsr',95,8);
INSERT INTO `rbac_templates` VALUES (110,'dcl',1,8);
INSERT INTO `rbac_templates` VALUES (110,'dcl',2,8);
INSERT INTO `rbac_templates` VALUES (110,'dcl',3,8);
INSERT INTO `rbac_templates` VALUES (110,'dcl',4,8);
INSERT INTO `rbac_templates` VALUES (110,'dcl',6,8);
INSERT INTO `rbac_templates` VALUES (110,'dcl',58,8);
INSERT INTO `rbac_templates` VALUES (110,'dcl',64,8);
INSERT INTO `rbac_templates` VALUES (110,'dcl',77,8);
INSERT INTO `rbac_templates` VALUES (110,'exc',1,8);
INSERT INTO `rbac_templates` VALUES (110,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (110,'exc',3,8);
INSERT INTO `rbac_templates` VALUES (110,'exc',4,8);
INSERT INTO `rbac_templates` VALUES (110,'exc',6,8);
INSERT INTO `rbac_templates` VALUES (110,'exc',55,8);
INSERT INTO `rbac_templates` VALUES (110,'exc',58,8);
INSERT INTO `rbac_templates` VALUES (110,'exc',95,8);
INSERT INTO `rbac_templates` VALUES (110,'exc',114,8);
INSERT INTO `rbac_templates` VALUES (110,'feed',1,8);
INSERT INTO `rbac_templates` VALUES (110,'feed',3,8);
INSERT INTO `rbac_templates` VALUES (110,'feed',4,8);
INSERT INTO `rbac_templates` VALUES (110,'feed',6,8);
INSERT INTO `rbac_templates` VALUES (110,'feed',58,8);
INSERT INTO `rbac_templates` VALUES (110,'file',1,8);
INSERT INTO `rbac_templates` VALUES (110,'file',2,8);
INSERT INTO `rbac_templates` VALUES (110,'file',3,8);
INSERT INTO `rbac_templates` VALUES (110,'file',4,8);
INSERT INTO `rbac_templates` VALUES (110,'file',6,8);
INSERT INTO `rbac_templates` VALUES (110,'file',55,8);
INSERT INTO `rbac_templates` VALUES (110,'file',58,8);
INSERT INTO `rbac_templates` VALUES (110,'file',95,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',1,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',2,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',3,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',4,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',6,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',17,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',18,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',20,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',21,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',22,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',24,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',25,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',26,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',27,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',28,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',31,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',32,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',42,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',43,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',50,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',55,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',58,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',60,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',63,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',65,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',71,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',73,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',74,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',75,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',76,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',78,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',81,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',82,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',90,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',95,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',106,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',108,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',116,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',119,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',126,8);
INSERT INTO `rbac_templates` VALUES (110,'fold',127,8);
INSERT INTO `rbac_templates` VALUES (110,'frm',1,8);
INSERT INTO `rbac_templates` VALUES (110,'frm',2,8);
INSERT INTO `rbac_templates` VALUES (110,'frm',3,8);
INSERT INTO `rbac_templates` VALUES (110,'frm',4,8);
INSERT INTO `rbac_templates` VALUES (110,'frm',6,8);
INSERT INTO `rbac_templates` VALUES (110,'frm',9,8);
INSERT INTO `rbac_templates` VALUES (110,'frm',10,8);
INSERT INTO `rbac_templates` VALUES (110,'frm',58,8);
INSERT INTO `rbac_templates` VALUES (110,'frm',62,8);
INSERT INTO `rbac_templates` VALUES (110,'glo',1,8);
INSERT INTO `rbac_templates` VALUES (110,'glo',2,8);
INSERT INTO `rbac_templates` VALUES (110,'glo',3,8);
INSERT INTO `rbac_templates` VALUES (110,'glo',4,8);
INSERT INTO `rbac_templates` VALUES (110,'glo',6,8);
INSERT INTO `rbac_templates` VALUES (110,'glo',58,8);
INSERT INTO `rbac_templates` VALUES (110,'glo',64,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',1,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',2,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',3,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',4,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',6,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',7,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',8,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',17,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',18,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',20,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',21,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',22,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',24,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',25,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',26,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',27,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',28,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',31,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',32,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',42,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',43,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',50,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',55,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',58,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',59,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',60,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',63,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',65,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',66,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',67,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',68,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',71,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',73,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',74,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',75,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',76,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',78,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',81,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',82,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',90,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',95,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',102,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',106,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',107,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',108,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',111,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',116,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',119,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',126,8);
INSERT INTO `rbac_templates` VALUES (110,'grp',127,8);
INSERT INTO `rbac_templates` VALUES (110,'grpr',1,8);
INSERT INTO `rbac_templates` VALUES (110,'grpr',2,8);
INSERT INTO `rbac_templates` VALUES (110,'grpr',4,8);
INSERT INTO `rbac_templates` VALUES (110,'grpr',6,8);
INSERT INTO `rbac_templates` VALUES (110,'grpr',58,8);
INSERT INTO `rbac_templates` VALUES (110,'htlm',1,8);
INSERT INTO `rbac_templates` VALUES (110,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (110,'htlm',3,8);
INSERT INTO `rbac_templates` VALUES (110,'htlm',4,8);
INSERT INTO `rbac_templates` VALUES (110,'htlm',6,8);
INSERT INTO `rbac_templates` VALUES (110,'htlm',55,8);
INSERT INTO `rbac_templates` VALUES (110,'htlm',58,8);
INSERT INTO `rbac_templates` VALUES (110,'htlm',95,8);
INSERT INTO `rbac_templates` VALUES (110,'iass',1,8);
INSERT INTO `rbac_templates` VALUES (110,'iass',2,8);
INSERT INTO `rbac_templates` VALUES (110,'iass',3,8);
INSERT INTO `rbac_templates` VALUES (110,'iass',4,8);
INSERT INTO `rbac_templates` VALUES (110,'iass',6,8);
INSERT INTO `rbac_templates` VALUES (110,'iass',55,8);
INSERT INTO `rbac_templates` VALUES (110,'iass',58,8);
INSERT INTO `rbac_templates` VALUES (110,'iass',95,8);
INSERT INTO `rbac_templates` VALUES (110,'iass',109,8);
INSERT INTO `rbac_templates` VALUES (110,'itgr',1,8);
INSERT INTO `rbac_templates` VALUES (110,'itgr',2,8);
INSERT INTO `rbac_templates` VALUES (110,'itgr',3,8);
INSERT INTO `rbac_templates` VALUES (110,'itgr',4,8);
INSERT INTO `rbac_templates` VALUES (110,'itgr',6,8);
INSERT INTO `rbac_templates` VALUES (110,'itgr',58,8);
INSERT INTO `rbac_templates` VALUES (110,'lm',1,8);
INSERT INTO `rbac_templates` VALUES (110,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (110,'lm',3,8);
INSERT INTO `rbac_templates` VALUES (110,'lm',4,8);
INSERT INTO `rbac_templates` VALUES (110,'lm',6,8);
INSERT INTO `rbac_templates` VALUES (110,'lm',55,8);
INSERT INTO `rbac_templates` VALUES (110,'lm',58,8);
INSERT INTO `rbac_templates` VALUES (110,'lm',95,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',1,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',2,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',3,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',4,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',6,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',20,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',21,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',24,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',25,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',27,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',32,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',42,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',55,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',58,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',95,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',102,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',108,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',116,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',120,8);
INSERT INTO `rbac_templates` VALUES (110,'lso',121,8);
INSERT INTO `rbac_templates` VALUES (110,'lti',1,8);
INSERT INTO `rbac_templates` VALUES (110,'lti',2,8);
INSERT INTO `rbac_templates` VALUES (110,'lti',3,8);
INSERT INTO `rbac_templates` VALUES (110,'lti',4,8);
INSERT INTO `rbac_templates` VALUES (110,'lti',6,8);
INSERT INTO `rbac_templates` VALUES (110,'lti',55,8);
INSERT INTO `rbac_templates` VALUES (110,'lti',58,8);
INSERT INTO `rbac_templates` VALUES (110,'lti',95,8);
INSERT INTO `rbac_templates` VALUES (110,'lti',125,8);
INSERT INTO `rbac_templates` VALUES (110,'mcst',1,8);
INSERT INTO `rbac_templates` VALUES (110,'mcst',2,8);
INSERT INTO `rbac_templates` VALUES (110,'mcst',3,8);
INSERT INTO `rbac_templates` VALUES (110,'mcst',4,8);
INSERT INTO `rbac_templates` VALUES (110,'mcst',6,8);
INSERT INTO `rbac_templates` VALUES (110,'mcst',55,8);
INSERT INTO `rbac_templates` VALUES (110,'mcst',58,8);
INSERT INTO `rbac_templates` VALUES (110,'mcst',95,8);
INSERT INTO `rbac_templates` VALUES (110,'mep',1,8);
INSERT INTO `rbac_templates` VALUES (110,'mep',2,8);
INSERT INTO `rbac_templates` VALUES (110,'mep',3,8);
INSERT INTO `rbac_templates` VALUES (110,'mep',4,8);
INSERT INTO `rbac_templates` VALUES (110,'mep',6,8);
INSERT INTO `rbac_templates` VALUES (110,'mep',58,8);
INSERT INTO `rbac_templates` VALUES (110,'poll',1,8);
INSERT INTO `rbac_templates` VALUES (110,'poll',3,8);
INSERT INTO `rbac_templates` VALUES (110,'poll',4,8);
INSERT INTO `rbac_templates` VALUES (110,'poll',6,8);
INSERT INTO `rbac_templates` VALUES (110,'poll',58,8);
INSERT INTO `rbac_templates` VALUES (110,'prtt',1,8);
INSERT INTO `rbac_templates` VALUES (110,'prtt',2,8);
INSERT INTO `rbac_templates` VALUES (110,'prtt',3,8);
INSERT INTO `rbac_templates` VALUES (110,'prtt',4,8);
INSERT INTO `rbac_templates` VALUES (110,'prtt',6,8);
INSERT INTO `rbac_templates` VALUES (110,'prtt',58,8);
INSERT INTO `rbac_templates` VALUES (110,'qpl',1,8);
INSERT INTO `rbac_templates` VALUES (110,'qpl',2,8);
INSERT INTO `rbac_templates` VALUES (110,'qpl',3,8);
INSERT INTO `rbac_templates` VALUES (110,'qpl',4,8);
INSERT INTO `rbac_templates` VALUES (110,'qpl',6,8);
INSERT INTO `rbac_templates` VALUES (110,'qpl',58,8);
INSERT INTO `rbac_templates` VALUES (110,'sahs',1,8);
INSERT INTO `rbac_templates` VALUES (110,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (110,'sahs',3,8);
INSERT INTO `rbac_templates` VALUES (110,'sahs',4,8);
INSERT INTO `rbac_templates` VALUES (110,'sahs',6,8);
INSERT INTO `rbac_templates` VALUES (110,'sahs',55,8);
INSERT INTO `rbac_templates` VALUES (110,'sahs',58,8);
INSERT INTO `rbac_templates` VALUES (110,'sahs',95,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',1,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',2,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',3,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',4,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',6,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',55,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',58,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',95,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',102,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',117,8);
INSERT INTO `rbac_templates` VALUES (110,'sess',118,8);
INSERT INTO `rbac_templates` VALUES (110,'spl',1,8);
INSERT INTO `rbac_templates` VALUES (110,'spl',2,8);
INSERT INTO `rbac_templates` VALUES (110,'spl',3,8);
INSERT INTO `rbac_templates` VALUES (110,'spl',4,8);
INSERT INTO `rbac_templates` VALUES (110,'spl',6,8);
INSERT INTO `rbac_templates` VALUES (110,'spl',58,8);
INSERT INTO `rbac_templates` VALUES (110,'svy',1,8);
INSERT INTO `rbac_templates` VALUES (110,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (110,'svy',3,8);
INSERT INTO `rbac_templates` VALUES (110,'svy',4,8);
INSERT INTO `rbac_templates` VALUES (110,'svy',6,8);
INSERT INTO `rbac_templates` VALUES (110,'svy',45,8);
INSERT INTO `rbac_templates` VALUES (110,'svy',55,8);
INSERT INTO `rbac_templates` VALUES (110,'svy',58,8);
INSERT INTO `rbac_templates` VALUES (110,'svy',95,8);
INSERT INTO `rbac_templates` VALUES (110,'svy',122,8);
INSERT INTO `rbac_templates` VALUES (110,'tst',1,8);
INSERT INTO `rbac_templates` VALUES (110,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (110,'tst',3,8);
INSERT INTO `rbac_templates` VALUES (110,'tst',4,8);
INSERT INTO `rbac_templates` VALUES (110,'tst',6,8);
INSERT INTO `rbac_templates` VALUES (110,'tst',55,8);
INSERT INTO `rbac_templates` VALUES (110,'tst',56,8);
INSERT INTO `rbac_templates` VALUES (110,'tst',58,8);
INSERT INTO `rbac_templates` VALUES (110,'tst',95,8);
INSERT INTO `rbac_templates` VALUES (110,'tst',115,8);
INSERT INTO `rbac_templates` VALUES (110,'webr',1,8);
INSERT INTO `rbac_templates` VALUES (110,'webr',2,8);
INSERT INTO `rbac_templates` VALUES (110,'webr',3,8);
INSERT INTO `rbac_templates` VALUES (110,'webr',4,8);
INSERT INTO `rbac_templates` VALUES (110,'webr',6,8);
INSERT INTO `rbac_templates` VALUES (110,'webr',58,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',1,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',2,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',3,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',4,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',6,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',58,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',64,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',94,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',97,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',98,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',99,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',100,8);
INSERT INTO `rbac_templates` VALUES (110,'wiki',112,8);
INSERT INTO `rbac_templates` VALUES (111,'bibl',2,8);
INSERT INTO `rbac_templates` VALUES (111,'bibl',3,8);
INSERT INTO `rbac_templates` VALUES (111,'bibl',4,8);
INSERT INTO `rbac_templates` VALUES (111,'bibl',58,8);
INSERT INTO `rbac_templates` VALUES (111,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (111,'blog',3,8);
INSERT INTO `rbac_templates` VALUES (111,'blog',4,8);
INSERT INTO `rbac_templates` VALUES (111,'blog',58,8);
INSERT INTO `rbac_templates` VALUES (111,'blog',79,8);
INSERT INTO `rbac_templates` VALUES (111,'book',2,8);
INSERT INTO `rbac_templates` VALUES (111,'book',3,8);
INSERT INTO `rbac_templates` VALUES (111,'book',4,8);
INSERT INTO `rbac_templates` VALUES (111,'book',58,8);
INSERT INTO `rbac_templates` VALUES (111,'catr',2,8);
INSERT INTO `rbac_templates` VALUES (111,'catr',4,8);
INSERT INTO `rbac_templates` VALUES (111,'catr',58,8);
INSERT INTO `rbac_templates` VALUES (111,'chtr',2,8);
INSERT INTO `rbac_templates` VALUES (111,'chtr',3,8);
INSERT INTO `rbac_templates` VALUES (111,'chtr',4,8);
INSERT INTO `rbac_templates` VALUES (111,'chtr',52,8);
INSERT INTO `rbac_templates` VALUES (111,'chtr',58,8);
INSERT INTO `rbac_templates` VALUES (111,'cmix',2,8);
INSERT INTO `rbac_templates` VALUES (111,'cmix',3,8);
INSERT INTO `rbac_templates` VALUES (111,'cmix',4,8);
INSERT INTO `rbac_templates` VALUES (111,'cmix',58,8);
INSERT INTO `rbac_templates` VALUES (111,'copa',2,8);
INSERT INTO `rbac_templates` VALUES (111,'copa',3,8);
INSERT INTO `rbac_templates` VALUES (111,'copa',4,8);
INSERT INTO `rbac_templates` VALUES (111,'copa',58,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',2,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',3,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',4,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',7,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',8,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',17,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',18,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',20,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',21,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',22,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',24,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',25,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',26,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',27,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',28,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',31,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',32,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',42,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',43,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',50,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',55,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',58,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',59,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',60,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',63,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',65,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',66,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',67,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',68,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',71,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',73,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',74,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',75,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',76,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',78,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',81,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',82,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',90,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',95,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',102,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',106,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',107,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',108,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',111,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',116,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',119,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',126,8);
INSERT INTO `rbac_templates` VALUES (111,'crs',127,8);
INSERT INTO `rbac_templates` VALUES (111,'crsr',2,8);
INSERT INTO `rbac_templates` VALUES (111,'crsr',4,8);
INSERT INTO `rbac_templates` VALUES (111,'crsr',55,8);
INSERT INTO `rbac_templates` VALUES (111,'crsr',58,8);
INSERT INTO `rbac_templates` VALUES (111,'crsr',95,8);
INSERT INTO `rbac_templates` VALUES (111,'dcl',2,8);
INSERT INTO `rbac_templates` VALUES (111,'dcl',3,8);
INSERT INTO `rbac_templates` VALUES (111,'dcl',4,8);
INSERT INTO `rbac_templates` VALUES (111,'dcl',58,8);
INSERT INTO `rbac_templates` VALUES (111,'dcl',64,8);
INSERT INTO `rbac_templates` VALUES (111,'dcl',77,8);
INSERT INTO `rbac_templates` VALUES (111,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (111,'exc',3,8);
INSERT INTO `rbac_templates` VALUES (111,'exc',4,8);
INSERT INTO `rbac_templates` VALUES (111,'exc',55,8);
INSERT INTO `rbac_templates` VALUES (111,'exc',58,8);
INSERT INTO `rbac_templates` VALUES (111,'exc',95,8);
INSERT INTO `rbac_templates` VALUES (111,'exc',114,8);
INSERT INTO `rbac_templates` VALUES (111,'feed',3,8);
INSERT INTO `rbac_templates` VALUES (111,'feed',4,8);
INSERT INTO `rbac_templates` VALUES (111,'feed',58,8);
INSERT INTO `rbac_templates` VALUES (111,'file',2,8);
INSERT INTO `rbac_templates` VALUES (111,'file',3,8);
INSERT INTO `rbac_templates` VALUES (111,'file',4,8);
INSERT INTO `rbac_templates` VALUES (111,'file',55,8);
INSERT INTO `rbac_templates` VALUES (111,'file',58,8);
INSERT INTO `rbac_templates` VALUES (111,'file',95,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',2,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',3,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',4,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',17,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',18,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',20,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',21,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',22,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',24,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',25,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',26,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',27,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',28,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',31,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',32,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',42,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',43,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',50,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',55,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',58,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',60,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',63,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',65,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',71,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',73,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',74,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',75,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',76,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',78,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',81,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',82,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',90,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',95,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',106,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',108,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',116,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',119,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',126,8);
INSERT INTO `rbac_templates` VALUES (111,'fold',127,8);
INSERT INTO `rbac_templates` VALUES (111,'frm',2,8);
INSERT INTO `rbac_templates` VALUES (111,'frm',3,8);
INSERT INTO `rbac_templates` VALUES (111,'frm',4,8);
INSERT INTO `rbac_templates` VALUES (111,'frm',9,8);
INSERT INTO `rbac_templates` VALUES (111,'frm',10,8);
INSERT INTO `rbac_templates` VALUES (111,'frm',58,8);
INSERT INTO `rbac_templates` VALUES (111,'frm',62,8);
INSERT INTO `rbac_templates` VALUES (111,'glo',2,8);
INSERT INTO `rbac_templates` VALUES (111,'glo',3,8);
INSERT INTO `rbac_templates` VALUES (111,'glo',4,8);
INSERT INTO `rbac_templates` VALUES (111,'glo',58,8);
INSERT INTO `rbac_templates` VALUES (111,'glo',64,8);
INSERT INTO `rbac_templates` VALUES (111,'grp',2,8);
INSERT INTO `rbac_templates` VALUES (111,'grp',3,8);
INSERT INTO `rbac_templates` VALUES (111,'grp',7,8);
INSERT INTO `rbac_templates` VALUES (111,'grp',8,8);
INSERT INTO `rbac_templates` VALUES (111,'grpr',2,8);
INSERT INTO `rbac_templates` VALUES (111,'grpr',4,8);
INSERT INTO `rbac_templates` VALUES (111,'grpr',58,8);
INSERT INTO `rbac_templates` VALUES (111,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (111,'htlm',3,8);
INSERT INTO `rbac_templates` VALUES (111,'htlm',55,8);
INSERT INTO `rbac_templates` VALUES (111,'htlm',95,8);
INSERT INTO `rbac_templates` VALUES (111,'iass',2,8);
INSERT INTO `rbac_templates` VALUES (111,'iass',3,8);
INSERT INTO `rbac_templates` VALUES (111,'iass',4,8);
INSERT INTO `rbac_templates` VALUES (111,'iass',55,8);
INSERT INTO `rbac_templates` VALUES (111,'iass',58,8);
INSERT INTO `rbac_templates` VALUES (111,'iass',95,8);
INSERT INTO `rbac_templates` VALUES (111,'iass',109,8);
INSERT INTO `rbac_templates` VALUES (111,'itgr',2,8);
INSERT INTO `rbac_templates` VALUES (111,'itgr',3,8);
INSERT INTO `rbac_templates` VALUES (111,'itgr',4,8);
INSERT INTO `rbac_templates` VALUES (111,'itgr',58,8);
INSERT INTO `rbac_templates` VALUES (111,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (111,'lm',3,8);
INSERT INTO `rbac_templates` VALUES (111,'lm',55,8);
INSERT INTO `rbac_templates` VALUES (111,'lm',95,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',2,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',3,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',4,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',20,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',21,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',24,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',25,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',27,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',32,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',42,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',55,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',58,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',102,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',108,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',116,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',120,8);
INSERT INTO `rbac_templates` VALUES (111,'lso',121,8);
INSERT INTO `rbac_templates` VALUES (111,'lti',2,8);
INSERT INTO `rbac_templates` VALUES (111,'lti',3,8);
INSERT INTO `rbac_templates` VALUES (111,'lti',4,8);
INSERT INTO `rbac_templates` VALUES (111,'lti',58,8);
INSERT INTO `rbac_templates` VALUES (111,'mcst',2,8);
INSERT INTO `rbac_templates` VALUES (111,'mcst',3,8);
INSERT INTO `rbac_templates` VALUES (111,'mcst',4,8);
INSERT INTO `rbac_templates` VALUES (111,'mcst',55,8);
INSERT INTO `rbac_templates` VALUES (111,'mcst',58,8);
INSERT INTO `rbac_templates` VALUES (111,'mcst',95,8);
INSERT INTO `rbac_templates` VALUES (111,'mep',2,8);
INSERT INTO `rbac_templates` VALUES (111,'mep',3,8);
INSERT INTO `rbac_templates` VALUES (111,'mep',4,8);
INSERT INTO `rbac_templates` VALUES (111,'mep',58,8);
INSERT INTO `rbac_templates` VALUES (111,'poll',3,8);
INSERT INTO `rbac_templates` VALUES (111,'poll',4,8);
INSERT INTO `rbac_templates` VALUES (111,'poll',58,8);
INSERT INTO `rbac_templates` VALUES (111,'prtt',2,8);
INSERT INTO `rbac_templates` VALUES (111,'prtt',3,8);
INSERT INTO `rbac_templates` VALUES (111,'qpl',2,8);
INSERT INTO `rbac_templates` VALUES (111,'qpl',3,8);
INSERT INTO `rbac_templates` VALUES (111,'qpl',4,8);
INSERT INTO `rbac_templates` VALUES (111,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (111,'sahs',3,8);
INSERT INTO `rbac_templates` VALUES (111,'sahs',55,8);
INSERT INTO `rbac_templates` VALUES (111,'sahs',95,8);
INSERT INTO `rbac_templates` VALUES (111,'sess',2,8);
INSERT INTO `rbac_templates` VALUES (111,'sess',3,8);
INSERT INTO `rbac_templates` VALUES (111,'sess',4,8);
INSERT INTO `rbac_templates` VALUES (111,'sess',6,8);
INSERT INTO `rbac_templates` VALUES (111,'sess',55,8);
INSERT INTO `rbac_templates` VALUES (111,'sess',58,8);
INSERT INTO `rbac_templates` VALUES (111,'sess',95,8);
INSERT INTO `rbac_templates` VALUES (111,'sess',102,8);
INSERT INTO `rbac_templates` VALUES (111,'sess',117,8);
INSERT INTO `rbac_templates` VALUES (111,'sess',118,8);
INSERT INTO `rbac_templates` VALUES (111,'spl',2,8);
INSERT INTO `rbac_templates` VALUES (111,'spl',3,8);
INSERT INTO `rbac_templates` VALUES (111,'spl',4,8);
INSERT INTO `rbac_templates` VALUES (111,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (111,'svy',3,8);
INSERT INTO `rbac_templates` VALUES (111,'svy',4,8);
INSERT INTO `rbac_templates` VALUES (111,'svy',45,8);
INSERT INTO `rbac_templates` VALUES (111,'svy',55,8);
INSERT INTO `rbac_templates` VALUES (111,'svy',58,8);
INSERT INTO `rbac_templates` VALUES (111,'svy',95,8);
INSERT INTO `rbac_templates` VALUES (111,'svy',122,8);
INSERT INTO `rbac_templates` VALUES (111,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (111,'tst',3,8);
INSERT INTO `rbac_templates` VALUES (111,'tst',4,8);
INSERT INTO `rbac_templates` VALUES (111,'tst',55,8);
INSERT INTO `rbac_templates` VALUES (111,'tst',58,8);
INSERT INTO `rbac_templates` VALUES (111,'tst',95,8);
INSERT INTO `rbac_templates` VALUES (111,'tst',115,8);
INSERT INTO `rbac_templates` VALUES (111,'webr',2,8);
INSERT INTO `rbac_templates` VALUES (111,'webr',3,8);
INSERT INTO `rbac_templates` VALUES (111,'webr',4,8);
INSERT INTO `rbac_templates` VALUES (111,'webr',58,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',2,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',3,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',4,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',58,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',64,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',94,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',97,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',98,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',99,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',100,8);
INSERT INTO `rbac_templates` VALUES (111,'wiki',112,8);
INSERT INTO `rbac_templates` VALUES (112,'bibl',2,8);
INSERT INTO `rbac_templates` VALUES (112,'bibl',3,8);
INSERT INTO `rbac_templates` VALUES (112,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (112,'blog',3,8);
INSERT INTO `rbac_templates` VALUES (112,'book',2,8);
INSERT INTO `rbac_templates` VALUES (112,'book',3,8);
INSERT INTO `rbac_templates` VALUES (112,'catr',2,8);
INSERT INTO `rbac_templates` VALUES (112,'chtr',2,8);
INSERT INTO `rbac_templates` VALUES (112,'chtr',3,8);
INSERT INTO `rbac_templates` VALUES (112,'cmix',2,8);
INSERT INTO `rbac_templates` VALUES (112,'cmix',3,8);
INSERT INTO `rbac_templates` VALUES (112,'copa',2,8);
INSERT INTO `rbac_templates` VALUES (112,'copa',3,8);
INSERT INTO `rbac_templates` VALUES (112,'crs',2,8);
INSERT INTO `rbac_templates` VALUES (112,'crs',3,8);
INSERT INTO `rbac_templates` VALUES (112,'crs',8,8);
INSERT INTO `rbac_templates` VALUES (112,'crsr',2,8);
INSERT INTO `rbac_templates` VALUES (112,'dcl',2,8);
INSERT INTO `rbac_templates` VALUES (112,'dcl',3,8);
INSERT INTO `rbac_templates` VALUES (112,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (112,'exc',3,8);
INSERT INTO `rbac_templates` VALUES (112,'feed',3,8);
INSERT INTO `rbac_templates` VALUES (112,'file',2,8);
INSERT INTO `rbac_templates` VALUES (112,'file',3,8);
INSERT INTO `rbac_templates` VALUES (112,'fold',2,8);
INSERT INTO `rbac_templates` VALUES (112,'fold',3,8);
INSERT INTO `rbac_templates` VALUES (112,'frm',2,8);
INSERT INTO `rbac_templates` VALUES (112,'frm',3,8);
INSERT INTO `rbac_templates` VALUES (112,'frm',9,8);
INSERT INTO `rbac_templates` VALUES (112,'frm',62,8);
INSERT INTO `rbac_templates` VALUES (112,'glo',2,8);
INSERT INTO `rbac_templates` VALUES (112,'glo',3,8);
INSERT INTO `rbac_templates` VALUES (112,'grp',2,8);
INSERT INTO `rbac_templates` VALUES (112,'grp',3,8);
INSERT INTO `rbac_templates` VALUES (112,'grp',7,8);
INSERT INTO `rbac_templates` VALUES (112,'grp',8,8);
INSERT INTO `rbac_templates` VALUES (112,'grpr',2,8);
INSERT INTO `rbac_templates` VALUES (112,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (112,'htlm',3,8);
INSERT INTO `rbac_templates` VALUES (112,'iass',2,8);
INSERT INTO `rbac_templates` VALUES (112,'iass',3,8);
INSERT INTO `rbac_templates` VALUES (112,'itgr',2,8);
INSERT INTO `rbac_templates` VALUES (112,'itgr',3,8);
INSERT INTO `rbac_templates` VALUES (112,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (112,'lm',3,8);
INSERT INTO `rbac_templates` VALUES (112,'lso',2,8);
INSERT INTO `rbac_templates` VALUES (112,'lso',3,8);
INSERT INTO `rbac_templates` VALUES (112,'lso',120,8);
INSERT INTO `rbac_templates` VALUES (112,'lti',2,8);
INSERT INTO `rbac_templates` VALUES (112,'lti',3,8);
INSERT INTO `rbac_templates` VALUES (112,'mcst',2,8);
INSERT INTO `rbac_templates` VALUES (112,'mcst',3,8);
INSERT INTO `rbac_templates` VALUES (112,'poll',3,8);
INSERT INTO `rbac_templates` VALUES (112,'prtt',2,8);
INSERT INTO `rbac_templates` VALUES (112,'prtt',3,8);
INSERT INTO `rbac_templates` VALUES (112,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (112,'sahs',3,8);
INSERT INTO `rbac_templates` VALUES (112,'sess',2,8);
INSERT INTO `rbac_templates` VALUES (112,'sess',3,8);
INSERT INTO `rbac_templates` VALUES (112,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (112,'svy',3,8);
INSERT INTO `rbac_templates` VALUES (112,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (112,'tst',3,8);
INSERT INTO `rbac_templates` VALUES (112,'webr',2,8);
INSERT INTO `rbac_templates` VALUES (112,'webr',3,8);
INSERT INTO `rbac_templates` VALUES (112,'wiki',2,8);
INSERT INTO `rbac_templates` VALUES (112,'wiki',3,8);
INSERT INTO `rbac_templates` VALUES (112,'wiki',64,8);
INSERT INTO `rbac_templates` VALUES (125,'bibl',1,8);
INSERT INTO `rbac_templates` VALUES (125,'bibl',2,8);
INSERT INTO `rbac_templates` VALUES (125,'bibl',3,8);
INSERT INTO `rbac_templates` VALUES (125,'bibl',6,8);
INSERT INTO `rbac_templates` VALUES (125,'bibl',58,8);
INSERT INTO `rbac_templates` VALUES (125,'blog',1,8);
INSERT INTO `rbac_templates` VALUES (125,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (125,'blog',3,8);
INSERT INTO `rbac_templates` VALUES (125,'blog',6,8);
INSERT INTO `rbac_templates` VALUES (125,'book',1,8);
INSERT INTO `rbac_templates` VALUES (125,'book',2,8);
INSERT INTO `rbac_templates` VALUES (125,'book',3,8);
INSERT INTO `rbac_templates` VALUES (125,'book',6,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',1,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',2,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',3,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',4,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',6,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',16,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',17,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',18,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',19,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',20,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',21,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',22,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',24,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',25,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',27,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',28,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',31,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',32,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',42,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',43,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',47,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',48,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',50,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',58,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',59,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',60,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',65,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',67,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',68,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',71,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',73,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',74,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',75,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',76,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',78,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',81,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',82,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',90,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',101,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',106,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',108,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',116,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',119,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',126,8);
INSERT INTO `rbac_templates` VALUES (125,'cat',127,8);
INSERT INTO `rbac_templates` VALUES (125,'catr',1,8);
INSERT INTO `rbac_templates` VALUES (125,'catr',2,8);
INSERT INTO `rbac_templates` VALUES (125,'catr',6,8);
INSERT INTO `rbac_templates` VALUES (125,'catr',58,8);
INSERT INTO `rbac_templates` VALUES (125,'chtr',1,8);
INSERT INTO `rbac_templates` VALUES (125,'chtr',2,8);
INSERT INTO `rbac_templates` VALUES (125,'chtr',6,8);
INSERT INTO `rbac_templates` VALUES (125,'cld',1,8);
INSERT INTO `rbac_templates` VALUES (125,'cld',2,8);
INSERT INTO `rbac_templates` VALUES (125,'cld',3,8);
INSERT INTO `rbac_templates` VALUES (125,'cld',6,8);
INSERT INTO `rbac_templates` VALUES (125,'cmix',1,8);
INSERT INTO `rbac_templates` VALUES (125,'cmix',2,8);
INSERT INTO `rbac_templates` VALUES (125,'cmix',6,8);
INSERT INTO `rbac_templates` VALUES (125,'copa',1,8);
INSERT INTO `rbac_templates` VALUES (125,'copa',2,8);
INSERT INTO `rbac_templates` VALUES (125,'copa',6,8);
INSERT INTO `rbac_templates` VALUES (125,'crs',1,8);
INSERT INTO `rbac_templates` VALUES (125,'crs',2,8);
INSERT INTO `rbac_templates` VALUES (125,'crs',3,8);
INSERT INTO `rbac_templates` VALUES (125,'crs',6,8);
INSERT INTO `rbac_templates` VALUES (125,'crs',55,8);
INSERT INTO `rbac_templates` VALUES (125,'crs',95,8);
INSERT INTO `rbac_templates` VALUES (125,'crs',102,8);
INSERT INTO `rbac_templates` VALUES (125,'crs',111,8);
INSERT INTO `rbac_templates` VALUES (125,'crsr',1,8);
INSERT INTO `rbac_templates` VALUES (125,'crsr',2,8);
INSERT INTO `rbac_templates` VALUES (125,'crsr',6,8);
INSERT INTO `rbac_templates` VALUES (125,'dcl',1,8);
INSERT INTO `rbac_templates` VALUES (125,'dcl',2,8);
INSERT INTO `rbac_templates` VALUES (125,'dcl',3,8);
INSERT INTO `rbac_templates` VALUES (125,'dcl',6,8);
INSERT INTO `rbac_templates` VALUES (125,'exc',1,8);
INSERT INTO `rbac_templates` VALUES (125,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (125,'exc',6,8);
INSERT INTO `rbac_templates` VALUES (125,'exc',55,8);
INSERT INTO `rbac_templates` VALUES (125,'exc',95,8);
INSERT INTO `rbac_templates` VALUES (125,'feed',1,8);
INSERT INTO `rbac_templates` VALUES (125,'feed',3,8);
INSERT INTO `rbac_templates` VALUES (125,'feed',6,8);
INSERT INTO `rbac_templates` VALUES (125,'file',1,8);
INSERT INTO `rbac_templates` VALUES (125,'file',2,8);
INSERT INTO `rbac_templates` VALUES (125,'file',6,8);
INSERT INTO `rbac_templates` VALUES (125,'fold',1,8);
INSERT INTO `rbac_templates` VALUES (125,'fold',2,8);
INSERT INTO `rbac_templates` VALUES (125,'fold',3,8);
INSERT INTO `rbac_templates` VALUES (125,'fold',6,8);
INSERT INTO `rbac_templates` VALUES (125,'fold',55,8);
INSERT INTO `rbac_templates` VALUES (125,'fold',95,8);
INSERT INTO `rbac_templates` VALUES (125,'frm',1,8);
INSERT INTO `rbac_templates` VALUES (125,'frm',2,8);
INSERT INTO `rbac_templates` VALUES (125,'frm',6,8);
INSERT INTO `rbac_templates` VALUES (125,'glo',1,8);
INSERT INTO `rbac_templates` VALUES (125,'glo',2,8);
INSERT INTO `rbac_templates` VALUES (125,'glo',6,8);
INSERT INTO `rbac_templates` VALUES (125,'grp',1,8);
INSERT INTO `rbac_templates` VALUES (125,'grp',2,8);
INSERT INTO `rbac_templates` VALUES (125,'grp',6,8);
INSERT INTO `rbac_templates` VALUES (125,'grp',55,8);
INSERT INTO `rbac_templates` VALUES (125,'grp',95,8);
INSERT INTO `rbac_templates` VALUES (125,'grpr',1,8);
INSERT INTO `rbac_templates` VALUES (125,'grpr',2,8);
INSERT INTO `rbac_templates` VALUES (125,'grpr',6,8);
INSERT INTO `rbac_templates` VALUES (125,'htlm',1,8);
INSERT INTO `rbac_templates` VALUES (125,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (125,'htlm',3,8);
INSERT INTO `rbac_templates` VALUES (125,'htlm',6,8);
INSERT INTO `rbac_templates` VALUES (125,'htlm',55,8);
INSERT INTO `rbac_templates` VALUES (125,'htlm',95,8);
INSERT INTO `rbac_templates` VALUES (125,'iass',1,8);
INSERT INTO `rbac_templates` VALUES (125,'iass',2,8);
INSERT INTO `rbac_templates` VALUES (125,'iass',6,8);
INSERT INTO `rbac_templates` VALUES (125,'itgr',1,8);
INSERT INTO `rbac_templates` VALUES (125,'itgr',2,8);
INSERT INTO `rbac_templates` VALUES (125,'itgr',3,8);
INSERT INTO `rbac_templates` VALUES (125,'itgr',6,8);
INSERT INTO `rbac_templates` VALUES (125,'lm',1,8);
INSERT INTO `rbac_templates` VALUES (125,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (125,'lm',3,8);
INSERT INTO `rbac_templates` VALUES (125,'lm',6,8);
INSERT INTO `rbac_templates` VALUES (125,'lm',55,8);
INSERT INTO `rbac_templates` VALUES (125,'lm',95,8);
INSERT INTO `rbac_templates` VALUES (125,'lso',1,8);
INSERT INTO `rbac_templates` VALUES (125,'lso',2,8);
INSERT INTO `rbac_templates` VALUES (125,'lso',6,8);
INSERT INTO `rbac_templates` VALUES (125,'lti',1,8);
INSERT INTO `rbac_templates` VALUES (125,'lti',2,8);
INSERT INTO `rbac_templates` VALUES (125,'lti',6,8);
INSERT INTO `rbac_templates` VALUES (125,'mcst',1,8);
INSERT INTO `rbac_templates` VALUES (125,'mcst',2,8);
INSERT INTO `rbac_templates` VALUES (125,'mcst',3,8);
INSERT INTO `rbac_templates` VALUES (125,'mcst',6,8);
INSERT INTO `rbac_templates` VALUES (125,'mep',1,8);
INSERT INTO `rbac_templates` VALUES (125,'mep',2,8);
INSERT INTO `rbac_templates` VALUES (125,'mep',3,8);
INSERT INTO `rbac_templates` VALUES (125,'mep',6,8);
INSERT INTO `rbac_templates` VALUES (125,'poll',1,8);
INSERT INTO `rbac_templates` VALUES (125,'poll',3,8);
INSERT INTO `rbac_templates` VALUES (125,'poll',6,8);
INSERT INTO `rbac_templates` VALUES (125,'prg',1,8);
INSERT INTO `rbac_templates` VALUES (125,'prg',2,8);
INSERT INTO `rbac_templates` VALUES (125,'prg',3,8);
INSERT INTO `rbac_templates` VALUES (125,'prg',101,8);
INSERT INTO `rbac_templates` VALUES (125,'prtt',1,8);
INSERT INTO `rbac_templates` VALUES (125,'prtt',2,8);
INSERT INTO `rbac_templates` VALUES (125,'prtt',3,8);
INSERT INTO `rbac_templates` VALUES (125,'prtt',6,8);
INSERT INTO `rbac_templates` VALUES (125,'qpl',1,8);
INSERT INTO `rbac_templates` VALUES (125,'qpl',2,8);
INSERT INTO `rbac_templates` VALUES (125,'qpl',3,8);
INSERT INTO `rbac_templates` VALUES (125,'qpl',6,8);
INSERT INTO `rbac_templates` VALUES (125,'root',2,8);
INSERT INTO `rbac_templates` VALUES (125,'root',3,8);
INSERT INTO `rbac_templates` VALUES (125,'sahs',1,8);
INSERT INTO `rbac_templates` VALUES (125,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (125,'sahs',3,8);
INSERT INTO `rbac_templates` VALUES (125,'sahs',6,8);
INSERT INTO `rbac_templates` VALUES (125,'sahs',55,8);
INSERT INTO `rbac_templates` VALUES (125,'sahs',95,8);
INSERT INTO `rbac_templates` VALUES (125,'sess',1,8);
INSERT INTO `rbac_templates` VALUES (125,'sess',2,8);
INSERT INTO `rbac_templates` VALUES (125,'sess',3,8);
INSERT INTO `rbac_templates` VALUES (125,'spl',1,8);
INSERT INTO `rbac_templates` VALUES (125,'spl',2,8);
INSERT INTO `rbac_templates` VALUES (125,'spl',3,8);
INSERT INTO `rbac_templates` VALUES (125,'spl',6,8);
INSERT INTO `rbac_templates` VALUES (125,'svy',1,8);
INSERT INTO `rbac_templates` VALUES (125,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (125,'svy',6,8);
INSERT INTO `rbac_templates` VALUES (125,'tst',1,8);
INSERT INTO `rbac_templates` VALUES (125,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (125,'tst',6,8);
INSERT INTO `rbac_templates` VALUES (125,'tst',55,8);
INSERT INTO `rbac_templates` VALUES (125,'tst',95,8);
INSERT INTO `rbac_templates` VALUES (125,'webr',1,8);
INSERT INTO `rbac_templates` VALUES (125,'webr',2,8);
INSERT INTO `rbac_templates` VALUES (125,'webr',3,8);
INSERT INTO `rbac_templates` VALUES (125,'webr',6,8);
INSERT INTO `rbac_templates` VALUES (125,'wiki',1,8);
INSERT INTO `rbac_templates` VALUES (125,'wiki',2,8);
INSERT INTO `rbac_templates` VALUES (125,'wiki',3,8);
INSERT INTO `rbac_templates` VALUES (125,'wiki',6,8);
INSERT INTO `rbac_templates` VALUES (131,'crs',2,8);
INSERT INTO `rbac_templates` VALUES (131,'crs',7,8);
INSERT INTO `rbac_templates` VALUES (148,'frm',1,8);
INSERT INTO `rbac_templates` VALUES (148,'frm',2,8);
INSERT INTO `rbac_templates` VALUES (148,'frm',3,8);
INSERT INTO `rbac_templates` VALUES (148,'frm',4,8);
INSERT INTO `rbac_templates` VALUES (148,'frm',6,8);
INSERT INTO `rbac_templates` VALUES (148,'frm',9,8);
INSERT INTO `rbac_templates` VALUES (148,'frm',10,8);
INSERT INTO `rbac_templates` VALUES (148,'frm',58,8);
INSERT INTO `rbac_templates` VALUES (148,'frm',62,8);
INSERT INTO `rbac_templates` VALUES (188,'chtr',2,8);
INSERT INTO `rbac_templates` VALUES (188,'chtr',3,8);
INSERT INTO `rbac_templates` VALUES (188,'chtr',4,8);
INSERT INTO `rbac_templates` VALUES (188,'chtr',52,8);
INSERT INTO `rbac_templates` VALUES (202,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (202,'blog',3,8);
INSERT INTO `rbac_templates` VALUES (202,'blog',79,8);
INSERT INTO `rbac_templates` VALUES (222,'orgu',2,8);
INSERT INTO `rbac_templates` VALUES (222,'orgu',3,8);
INSERT INTO `rbac_templates` VALUES (222,'orgu',92,8);
INSERT INTO `rbac_templates` VALUES (231,'blog',2,8);
INSERT INTO `rbac_templates` VALUES (231,'blog',3,8);
INSERT INTO `rbac_templates` VALUES (231,'blog',4,8);
INSERT INTO `rbac_templates` VALUES (231,'blog',79,8);
INSERT INTO `rbac_templates` VALUES (231,'blog',96,8);
INSERT INTO `rbac_templates` VALUES (267,'iass',2,8);
INSERT INTO `rbac_templates` VALUES (267,'iass',3,8);
INSERT INTO `rbac_templates` VALUES (268,'sess',2,8);
INSERT INTO `rbac_templates` VALUES (268,'sess',3,8);
INSERT INTO `rbac_templates` VALUES (277,'lso',55,8);
INSERT INTO `rbac_templates` VALUES (277,'lso',102,8);
INSERT INTO `rbac_templates` VALUES (277,'lso',120,8);
INSERT INTO `rbac_templates` VALUES (277,'lso',121,8);
INSERT INTO `rbac_templates` VALUES (278,'copa',2,8);
INSERT INTO `rbac_templates` VALUES (278,'copa',3,8);
INSERT INTO `rbac_templates` VALUES (278,'copa',55,8);
INSERT INTO `rbac_templates` VALUES (278,'copa',95,8);
INSERT INTO `rbac_templates` VALUES (278,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (278,'exc',3,8);
INSERT INTO `rbac_templates` VALUES (278,'exc',55,8);
INSERT INTO `rbac_templates` VALUES (278,'exc',95,8);
INSERT INTO `rbac_templates` VALUES (278,'exc',114,8);
INSERT INTO `rbac_templates` VALUES (278,'file',2,8);
INSERT INTO `rbac_templates` VALUES (278,'file',3,8);
INSERT INTO `rbac_templates` VALUES (278,'file',55,8);
INSERT INTO `rbac_templates` VALUES (278,'file',95,8);
INSERT INTO `rbac_templates` VALUES (278,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (278,'htlm',3,8);
INSERT INTO `rbac_templates` VALUES (278,'htlm',55,8);
INSERT INTO `rbac_templates` VALUES (278,'htlm',95,8);
INSERT INTO `rbac_templates` VALUES (278,'iass',2,8);
INSERT INTO `rbac_templates` VALUES (278,'iass',3,8);
INSERT INTO `rbac_templates` VALUES (278,'iass',55,8);
INSERT INTO `rbac_templates` VALUES (278,'iass',95,8);
INSERT INTO `rbac_templates` VALUES (278,'iass',102,8);
INSERT INTO `rbac_templates` VALUES (278,'iass',109,8);
INSERT INTO `rbac_templates` VALUES (278,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (278,'lm',3,8);
INSERT INTO `rbac_templates` VALUES (278,'lm',55,8);
INSERT INTO `rbac_templates` VALUES (278,'lm',95,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',1,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',2,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',3,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',4,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',6,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',20,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',21,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',24,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',25,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',27,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',32,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',42,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',55,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',58,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',99,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',102,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',108,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',116,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',120,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',121,8);
INSERT INTO `rbac_templates` VALUES (278,'lso',277,8);
INSERT INTO `rbac_templates` VALUES (278,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (278,'sahs',3,8);
INSERT INTO `rbac_templates` VALUES (278,'sahs',55,8);
INSERT INTO `rbac_templates` VALUES (278,'sahs',95,8);
INSERT INTO `rbac_templates` VALUES (278,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (278,'svy',3,8);
INSERT INTO `rbac_templates` VALUES (278,'svy',45,8);
INSERT INTO `rbac_templates` VALUES (278,'svy',55,8);
INSERT INTO `rbac_templates` VALUES (278,'svy',95,8);
INSERT INTO `rbac_templates` VALUES (278,'svy',122,8);
INSERT INTO `rbac_templates` VALUES (278,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (278,'tst',3,8);
INSERT INTO `rbac_templates` VALUES (278,'tst',55,8);
INSERT INTO `rbac_templates` VALUES (278,'tst',56,8);
INSERT INTO `rbac_templates` VALUES (278,'tst',95,8);
INSERT INTO `rbac_templates` VALUES (278,'tst',115,8);
INSERT INTO `rbac_templates` VALUES (279,'copa',2,8);
INSERT INTO `rbac_templates` VALUES (279,'copa',3,8);
INSERT INTO `rbac_templates` VALUES (279,'exc',2,8);
INSERT INTO `rbac_templates` VALUES (279,'exc',3,8);
INSERT INTO `rbac_templates` VALUES (279,'file',2,8);
INSERT INTO `rbac_templates` VALUES (279,'file',3,8);
INSERT INTO `rbac_templates` VALUES (279,'htlm',2,8);
INSERT INTO `rbac_templates` VALUES (279,'htlm',3,8);
INSERT INTO `rbac_templates` VALUES (279,'lm',2,8);
INSERT INTO `rbac_templates` VALUES (279,'lm',3,8);
INSERT INTO `rbac_templates` VALUES (279,'lso',2,8);
INSERT INTO `rbac_templates` VALUES (279,'lso',3,8);
INSERT INTO `rbac_templates` VALUES (279,'lso',121,8);
INSERT INTO `rbac_templates` VALUES (279,'lso',277,8);
INSERT INTO `rbac_templates` VALUES (279,'sahs',2,8);
INSERT INTO `rbac_templates` VALUES (279,'sahs',3,8);
INSERT INTO `rbac_templates` VALUES (279,'svy',2,8);
INSERT INTO `rbac_templates` VALUES (279,'svy',3,8);
INSERT INTO `rbac_templates` VALUES (279,'tst',2,8);
INSERT INTO `rbac_templates` VALUES (279,'tst',3,8);

--
-- Table structure for table `rbac_ua`
--

CREATE TABLE `rbac_ua` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `rol_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`,`rol_id`),
  KEY `i1_idx` (`usr_id`),
  KEY `i2_idx` (`rol_id`)
) ;

--
-- Dumping data for table `rbac_ua`
--

INSERT INTO `rbac_ua` VALUES (6,2);
INSERT INTO `rbac_ua` VALUES (13,14);

--
-- Table structure for table `rcat_settings`
--

CREATE TABLE `rcat_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `organization` varchar(400) DEFAULT NULL,
  `local_information` varchar(4000) DEFAULT NULL,
  `remote_link` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `rcat_settings`
--


--
-- Table structure for table `read_event`
--

CREATE TABLE `read_event` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `last_access` int(11) DEFAULT NULL,
  `read_count` int(11) NOT NULL DEFAULT 0,
  `spent_seconds` int(11) NOT NULL DEFAULT 0,
  `first_access` datetime DEFAULT NULL,
  `childs_read_count` int(11) NOT NULL DEFAULT 0,
  `childs_spent_seconds` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`usr_id`),
  KEY `i1_idx` (`usr_id`)
) ;

--
-- Dumping data for table `read_event`
--


--
-- Table structure for table `reg_access_limit`
--

CREATE TABLE `reg_access_limit` (
  `role_id` int(11) NOT NULL DEFAULT 0,
  `limit_absolute` int(11) DEFAULT NULL,
  `limit_relative_d` int(11) DEFAULT NULL,
  `limit_relative_m` int(11) DEFAULT NULL,
  `limit_mode` char(16) DEFAULT 'absolute',
  PRIMARY KEY (`role_id`)
) ;

--
-- Dumping data for table `reg_access_limit`
--


--
-- Table structure for table `reg_er_assignments`
--

CREATE TABLE `reg_er_assignments` (
  `assignment_id` int(11) NOT NULL DEFAULT 0,
  `domain` varchar(128) DEFAULT NULL,
  `role` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`assignment_id`)
) ;

--
-- Dumping data for table `reg_er_assignments`
--

INSERT INTO `reg_er_assignments` VALUES (1,'',0);

--
-- Table structure for table `reg_er_assignments_seq`
--

CREATE TABLE `reg_er_assignments_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=2;

--
-- Dumping data for table `reg_er_assignments_seq`
--

INSERT INTO `reg_er_assignments_seq` VALUES (1);

--
-- Table structure for table `reg_registration_codes`
--

CREATE TABLE `reg_registration_codes` (
  `code_id` int(11) NOT NULL DEFAULT 0,
  `code` varchar(50) DEFAULT NULL,
  `role` int(11) DEFAULT 0,
  `generated_on` int(11) DEFAULT 0,
  `used` int(11) NOT NULL DEFAULT 0,
  `role_local` varchar(255) DEFAULT NULL,
  `alimit` varchar(50) DEFAULT NULL,
  `alimitdt` varchar(255) DEFAULT NULL,
  `reg_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `ext_enabled` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`code_id`),
  KEY `i1_idx` (`code`)
) ;

--
-- Dumping data for table `reg_registration_codes`
--


--
-- Table structure for table `reg_registration_codes_seq`
--

CREATE TABLE `reg_registration_codes_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `reg_registration_codes_seq`
--


--
-- Table structure for table `remote_course_settings`
--

CREATE TABLE `remote_course_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `local_information` varchar(4000) DEFAULT NULL,
  `availability_type` tinyint(4) NOT NULL DEFAULT 0,
  `r_start` int(11) NOT NULL DEFAULT 0,
  `r_end` int(11) NOT NULL DEFAULT 0,
  `remote_link` varchar(4000) DEFAULT NULL,
  `mid` int(11) NOT NULL DEFAULT 0,
  `organization` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `remote_course_settings`
--


--
-- Table structure for table `rep_rec_content_obj`
--

CREATE TABLE `rep_rec_content_obj` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `declined` tinyint(4) NOT NULL DEFAULT 0
) ;

--
-- Dumping data for table `rep_rec_content_obj`
--


--
-- Table structure for table `rep_rec_content_role`
--

CREATE TABLE `rep_rec_content_role` (
  `role_id` int(11) NOT NULL DEFAULT 0,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`role_id`,`ref_id`),
  KEY `i1_idx` (`role_id`)
) ;

--
-- Dumping data for table `rep_rec_content_role`
--


--
-- Table structure for table `rep_rec_content_role_seq`
--

CREATE TABLE `rep_rec_content_role_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `rep_rec_content_role_seq`
--


--
-- Table structure for table `rfil_settings`
--

CREATE TABLE `rfil_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `organization` varchar(400) DEFAULT NULL,
  `local_information` varchar(4000) DEFAULT NULL,
  `remote_link` varchar(400) DEFAULT NULL,
  `version` smallint(6) NOT NULL DEFAULT 1,
  `version_tstamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `rfil_settings`
--


--
-- Table structure for table `rglo_settings`
--

CREATE TABLE `rglo_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `organization` varchar(400) DEFAULT NULL,
  `local_information` varchar(4000) DEFAULT NULL,
  `remote_link` varchar(400) DEFAULT NULL,
  `availability_type` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `rglo_settings`
--


--
-- Table structure for table `rgrp_settings`
--

CREATE TABLE `rgrp_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `organization` varchar(400) DEFAULT NULL,
  `local_information` varchar(4000) DEFAULT NULL,
  `remote_link` varchar(400) DEFAULT NULL,
  `availability_type` tinyint(4) NOT NULL DEFAULT 0,
  `availability_start` int(11) DEFAULT NULL,
  `availability_end` int(11) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `rgrp_settings`
--


--
-- Table structure for table `rlm_settings`
--

CREATE TABLE `rlm_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `organization` varchar(400) DEFAULT NULL,
  `local_information` varchar(4000) DEFAULT NULL,
  `remote_link` varchar(400) DEFAULT NULL,
  `availability_type` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `rlm_settings`
--


--
-- Table structure for table `role_data`
--

CREATE TABLE `role_data` (
  `role_id` int(11) NOT NULL DEFAULT 0,
  `allow_register` tinyint(4) NOT NULL DEFAULT 0,
  `assign_users` tinyint(4) DEFAULT 0,
  `auth_mode` char(16) DEFAULT 'default',
  PRIMARY KEY (`role_id`),
  KEY `i1_idx` (`auth_mode`)
) ;

--
-- Dumping data for table `role_data`
--

INSERT INTO `role_data` VALUES (2,0,0,'default');
INSERT INTO `role_data` VALUES (3,0,0,'default');
INSERT INTO `role_data` VALUES (4,0,0,'default');
INSERT INTO `role_data` VALUES (5,1,0,'default');
INSERT INTO `role_data` VALUES (14,0,0,'default');
INSERT INTO `role_data` VALUES (187,0,0,'default');

--
-- Table structure for table `rtst_settings`
--

CREATE TABLE `rtst_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `organization` varchar(400) DEFAULT NULL,
  `local_information` varchar(4000) DEFAULT NULL,
  `remote_link` varchar(400) DEFAULT NULL,
  `availability_type` tinyint(4) NOT NULL DEFAULT 0,
  `availability_start` int(11) DEFAULT NULL,
  `availability_end` int(11) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `rtst_settings`
--


--
-- Table structure for table `rwik_settings`
--

CREATE TABLE `rwik_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `organization` varchar(400) DEFAULT NULL,
  `local_information` varchar(4000) DEFAULT NULL,
  `remote_link` varchar(400) DEFAULT NULL,
  `availability_type` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `rwik_settings`
--


--
-- Table structure for table `sahs_lm`
--

CREATE TABLE `sahs_lm` (
  `id` int(11) NOT NULL DEFAULT 0,
  `c_online` varchar(3) DEFAULT 'n',
  `api_adapter` varchar(80) DEFAULT 'API',
  `api_func_prefix` varchar(20) DEFAULT 'LMS',
  `credit` varchar(10) DEFAULT 'credit',
  `default_lesson_mode` varchar(8) DEFAULT 'normal',
  `auto_review` varchar(3) DEFAULT 'n',
  `c_type` varchar(10) DEFAULT NULL,
  `max_attempt` int(11) DEFAULT 0,
  `module_version` int(11) DEFAULT 1,
  `editable` int(11) NOT NULL DEFAULT 0,
  `stylesheet` int(11) NOT NULL DEFAULT 0,
  `glossary` int(11) NOT NULL DEFAULT 0,
  `question_tries` int(11) DEFAULT 3,
  `unlimited_session` varchar(1) NOT NULL DEFAULT 'n',
  `no_menu` varchar(1) NOT NULL DEFAULT 'n',
  `hide_navig` varchar(1) NOT NULL DEFAULT 'n',
  `debug` varchar(1) NOT NULL DEFAULT 'n',
  `debugpw` varchar(50) DEFAULT 'n',
  `entry_page` int(11) NOT NULL DEFAULT 0,
  `seq_exp_mode` tinyint(4) DEFAULT 0,
  `localization` varchar(2) DEFAULT NULL,
  `open_mode` tinyint(4) NOT NULL DEFAULT 0,
  `width` smallint(6) NOT NULL DEFAULT 950,
  `height` smallint(6) NOT NULL DEFAULT 650,
  `auto_continue` varchar(1) NOT NULL DEFAULT 'n',
  `sequencing` varchar(1) NOT NULL DEFAULT 'y',
  `interactions` varchar(1) NOT NULL DEFAULT 'y',
  `objectives` varchar(1) NOT NULL DEFAULT 'y',
  `time_from_lms` varchar(1) NOT NULL DEFAULT 'n',
  `comments` varchar(1) NOT NULL DEFAULT 'y',
  `auto_last_visited` varchar(1) NOT NULL DEFAULT 'y',
  `check_values` varchar(1) NOT NULL DEFAULT 'y',
  `offline_mode` varchar(1) NOT NULL DEFAULT 'n',
  `offline_zip_created` datetime DEFAULT NULL,
  `auto_suspend` varchar(1) NOT NULL DEFAULT 'n',
  `fourth_edition` varchar(1) NOT NULL DEFAULT 'n',
  `ie_compatibility` varchar(1) DEFAULT NULL,
  `ie_force_render` varchar(1) NOT NULL DEFAULT 'n',
  `mastery_score` tinyint(4) DEFAULT NULL,
  `id_setting` tinyint(4) NOT NULL DEFAULT 0,
  `name_setting` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `sahs_lm`
--


--
-- Table structure for table `sahs_sc13_sco`
--

CREATE TABLE `sahs_sc13_sco` (
  `id` int(11) NOT NULL DEFAULT 0,
  `hide_obj_page` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `sahs_sc13_sco`
--


--
-- Table structure for table `sahs_sc13_seq_assign`
--

CREATE TABLE `sahs_sc13_seq_assign` (
  `identifier` varchar(50) DEFAULT NULL,
  `sahs_sc13_tree_node_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`sahs_sc13_tree_node_id`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_assign`
--


--
-- Table structure for table `sahs_sc13_seq_cond`
--

CREATE TABLE `sahs_sc13_seq_cond` (
  `cond` varchar(50) DEFAULT NULL,
  `seqnodeid` int(11) NOT NULL DEFAULT 0,
  `measurethreshold` varchar(50) DEFAULT NULL,
  `operator` varchar(50) DEFAULT NULL,
  `referencedobjective` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`seqnodeid`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_cond`
--


--
-- Table structure for table `sahs_sc13_seq_course`
--

CREATE TABLE `sahs_sc13_seq_course` (
  `flow` tinyint(4) DEFAULT 0,
  `choice` tinyint(4) DEFAULT 1,
  `forwardonly` tinyint(4) DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_course`
--


--
-- Table structure for table `sahs_sc13_seq_item`
--

CREATE TABLE `sahs_sc13_seq_item` (
  `importid` varchar(32) DEFAULT NULL,
  `seqnodeid` int(11) NOT NULL DEFAULT 0,
  `sahs_sc13_tree_node_id` int(11) NOT NULL DEFAULT 0,
  `sequencingid` varchar(50) DEFAULT NULL,
  `nocopy` tinyint(4) DEFAULT NULL,
  `nodelete` tinyint(4) DEFAULT NULL,
  `nomove` tinyint(4) DEFAULT NULL,
  `seqxml` longtext DEFAULT NULL,
  `rootlevel` tinyint(4) NOT NULL DEFAULT 0,
  `importseqxml` longtext DEFAULT NULL,
  PRIMARY KEY (`sahs_sc13_tree_node_id`,`rootlevel`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_item`
--


--
-- Table structure for table `sahs_sc13_seq_mapinfo`
--

CREATE TABLE `sahs_sc13_seq_mapinfo` (
  `seqnodeid` int(11) NOT NULL DEFAULT 0,
  `readnormalizedmeasure` tinyint(4) DEFAULT NULL,
  `readsatisfiedstatus` tinyint(4) DEFAULT NULL,
  `targetobjectiveid` varchar(50) DEFAULT NULL,
  `writenormalizedmeasure` tinyint(4) DEFAULT NULL,
  `writesatisfiedstatus` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`seqnodeid`),
  KEY `i1_idx` (`targetobjectiveid`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_mapinfo`
--


--
-- Table structure for table `sahs_sc13_seq_node`
--

CREATE TABLE `sahs_sc13_seq_node` (
  `seqnodeid` int(11) NOT NULL DEFAULT 0,
  `nodename` varchar(50) DEFAULT NULL,
  `tree_node_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`seqnodeid`),
  KEY `i2_idx` (`tree_node_id`),
  KEY `i3_idx` (`nodename`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_node`
--


--
-- Table structure for table `sahs_sc13_seq_node_seq`
--

CREATE TABLE `sahs_sc13_seq_node_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_node_seq`
--


--
-- Table structure for table `sahs_sc13_seq_obj`
--

CREATE TABLE `sahs_sc13_seq_obj` (
  `seqnodeid` int(11) NOT NULL DEFAULT 0,
  `minnormalizedmeasure` varchar(50) DEFAULT NULL,
  `objectiveid` varchar(200) DEFAULT NULL,
  `primary_obj` tinyint(4) DEFAULT NULL,
  `satisfiedbymeasure` tinyint(4) DEFAULT NULL,
  `import_objective_id` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`seqnodeid`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_obj`
--


--
-- Table structure for table `sahs_sc13_seq_rule`
--

CREATE TABLE `sahs_sc13_seq_rule` (
  `action` varchar(50) DEFAULT NULL,
  `childactivityset` varchar(50) DEFAULT NULL,
  `conditioncombination` varchar(50) DEFAULT NULL,
  `seqnodeid` int(11) NOT NULL DEFAULT 0,
  `minimumcount` int(11) DEFAULT NULL,
  `minimumpercent` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`seqnodeid`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_rule`
--


--
-- Table structure for table `sahs_sc13_seq_templ`
--

CREATE TABLE `sahs_sc13_seq_templ` (
  `seqnodeid` int(11) NOT NULL DEFAULT 0,
  `id` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`seqnodeid`,`id`),
  KEY `i1_idx` (`seqnodeid`,`id`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_templ`
--


--
-- Table structure for table `sahs_sc13_seq_templts`
--

CREATE TABLE `sahs_sc13_seq_templts` (
  `identifier` varchar(50) DEFAULT NULL,
  `filename` varchar(50) DEFAULT NULL,
  `id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_templts`
--

INSERT INTO `sahs_sc13_seq_templts` VALUES ('pretestpost','pretest_posttest.xml',1);
INSERT INTO `sahs_sc13_seq_templts` VALUES ('linearpath','linear_path.xml',2);
INSERT INTO `sahs_sc13_seq_templts` VALUES ('linearpathforward','linear_path_forward.xml',3);
INSERT INTO `sahs_sc13_seq_templts` VALUES ('mandatoryoptions','mandatory_options.xml',4);

--
-- Table structure for table `sahs_sc13_seq_templts_seq`
--

CREATE TABLE `sahs_sc13_seq_templts_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=5;

--
-- Dumping data for table `sahs_sc13_seq_templts_seq`
--

INSERT INTO `sahs_sc13_seq_templts_seq` VALUES (4);

--
-- Table structure for table `sahs_sc13_seq_tree`
--

CREATE TABLE `sahs_sc13_seq_tree` (
  `child` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `importid` varchar(32) NOT NULL DEFAULT '',
  `parent` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) DEFAULT NULL,
  PRIMARY KEY (`child`,`importid`,`parent`),
  KEY `i1_idx` (`child`),
  KEY `i2_idx` (`importid`),
  KEY `i3_idx` (`parent`)
) ;

--
-- Dumping data for table `sahs_sc13_seq_tree`
--


--
-- Table structure for table `sahs_sc13_tree`
--

CREATE TABLE `sahs_sc13_tree` (
  `slm_id` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) NOT NULL DEFAULT 0,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`child`,`parent`,`slm_id`),
  KEY `i1_idx` (`child`),
  KEY `i2_idx` (`parent`),
  KEY `i3_idx` (`slm_id`)
) ;

--
-- Dumping data for table `sahs_sc13_tree`
--


--
-- Table structure for table `sahs_sc13_tree_node`
--

CREATE TABLE `sahs_sc13_tree_node` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  `type` char(4) DEFAULT NULL,
  `slm_id` int(11) NOT NULL DEFAULT 0,
  `import_id` varchar(50) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`obj_id`),
  KEY `i1_idx` (`slm_id`),
  KEY `i2_idx` (`type`)
) ;

--
-- Dumping data for table `sahs_sc13_tree_node`
--

INSERT INTO `sahs_sc13_tree_node` VALUES (1,'Dummy top node for all trees.','',0,'',NULL,NULL);

--
-- Table structure for table `sahs_sc13_tree_node_seq`
--

CREATE TABLE `sahs_sc13_tree_node_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=2;

--
-- Dumping data for table `sahs_sc13_tree_node_seq`
--

INSERT INTO `sahs_sc13_tree_node_seq` VALUES (1);

--
-- Table structure for table `sahs_user`
--

CREATE TABLE `sahs_user` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `package_attempts` smallint(6) DEFAULT NULL,
  `module_version` smallint(6) DEFAULT NULL,
  `last_visited` varchar(255) DEFAULT NULL,
  `hash` varchar(20) DEFAULT NULL,
  `hash_end` datetime DEFAULT NULL,
  `offline_mode` varchar(8) DEFAULT NULL,
  `last_access` datetime DEFAULT NULL,
  `total_time_sec` int(11) DEFAULT NULL,
  `sco_total_time_sec` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `percentage_completed` tinyint(4) DEFAULT NULL,
  `first_access` datetime DEFAULT NULL,
  `last_status_change` datetime DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`user_id`)
) ;

--
-- Dumping data for table `sahs_user`
--


--
-- Table structure for table `saml_idp_settings`
--

CREATE TABLE `saml_idp_settings` (
  `idp_id` int(11) NOT NULL,
  `is_active` tinyint(4) NOT NULL,
  `allow_local_auth` tinyint(4) NOT NULL DEFAULT 0,
  `default_role_id` int(11) NOT NULL DEFAULT 0,
  `uid_claim` varchar(1000) DEFAULT NULL,
  `login_claim` varchar(1000) DEFAULT NULL,
  `sync_status` tinyint(4) NOT NULL DEFAULT 0,
  `account_migr_status` tinyint(4) NOT NULL DEFAULT 0,
  `entity_id` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`idp_id`)
) ;

--
-- Dumping data for table `saml_idp_settings`
--


--
-- Table structure for table `saml_idp_settings_seq`
--

CREATE TABLE `saml_idp_settings_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `saml_idp_settings_seq`
--


--
-- Table structure for table `sc_item`
--

CREATE TABLE `sc_item` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `import_id` varchar(200) DEFAULT NULL,
  `identifierref` varchar(200) DEFAULT NULL,
  `isvisible` varchar(6) DEFAULT NULL,
  `parameters` varchar(4000) DEFAULT NULL,
  `prereq_type` varchar(200) DEFAULT NULL,
  `prerequisites` varchar(4000) DEFAULT NULL,
  `maxtimeallowed` varchar(30) DEFAULT NULL,
  `timelimitaction` varchar(30) DEFAULT NULL,
  `datafromlms` longtext DEFAULT NULL,
  `masteryscore` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `sc_item`
--


--
-- Table structure for table `sc_manifest`
--

CREATE TABLE `sc_manifest` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `import_id` varchar(200) DEFAULT NULL,
  `version` varchar(200) DEFAULT NULL,
  `xml_base` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `sc_manifest`
--


--
-- Table structure for table `sc_organization`
--

CREATE TABLE `sc_organization` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `import_id` varchar(200) DEFAULT NULL,
  `structure` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `sc_organization`
--


--
-- Table structure for table `sc_organizations`
--

CREATE TABLE `sc_organizations` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `default_organization` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `sc_organizations`
--


--
-- Table structure for table `sc_resource`
--

CREATE TABLE `sc_resource` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `import_id` varchar(200) DEFAULT NULL,
  `resourcetype` varchar(30) DEFAULT NULL,
  `scormtype` varchar(6) DEFAULT NULL,
  `href` varchar(250) DEFAULT NULL,
  `xml_base` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`obj_id`),
  KEY `i1_idx` (`import_id`)
) ;

--
-- Dumping data for table `sc_resource`
--


--
-- Table structure for table `sc_resource_dependen`
--

CREATE TABLE `sc_resource_dependen` (
  `id` int(11) NOT NULL DEFAULT 0,
  `res_id` int(11) DEFAULT NULL,
  `identifierref` varchar(200) DEFAULT NULL,
  `nr` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `sc_resource_dependen`
--


--
-- Table structure for table `sc_resource_dependen_seq`
--

CREATE TABLE `sc_resource_dependen_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `sc_resource_dependen_seq`
--


--
-- Table structure for table `sc_resource_file`
--

CREATE TABLE `sc_resource_file` (
  `id` int(11) NOT NULL DEFAULT 0,
  `res_id` int(11) DEFAULT NULL,
  `href` varchar(4000) DEFAULT NULL,
  `nr` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `sc_resource_file`
--


--
-- Table structure for table `sc_resource_file_seq`
--

CREATE TABLE `sc_resource_file_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `sc_resource_file_seq`
--


--
-- Table structure for table `sc_resources`
--

CREATE TABLE `sc_resources` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `xml_base` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `sc_resources`
--


--
-- Table structure for table `scorm_object`
--

CREATE TABLE `scorm_object` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  `c_type` char(3) DEFAULT NULL,
  `slm_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `scorm_object`
--


--
-- Table structure for table `scorm_object_seq`
--

CREATE TABLE `scorm_object_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `scorm_object_seq`
--


--
-- Table structure for table `scorm_tracking`
--

CREATE TABLE `scorm_tracking` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `sco_id` int(11) NOT NULL DEFAULT 0,
  `lvalue` varchar(64) NOT NULL DEFAULT ' ',
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `c_timestamp` datetime DEFAULT NULL,
  `rvalue` longtext DEFAULT NULL,
  PRIMARY KEY (`user_id`,`sco_id`,`lvalue`,`obj_id`),
  KEY `i2_idx` (`obj_id`,`sco_id`,`lvalue`)
) ;

--
-- Dumping data for table `scorm_tracking`
--


--
-- Table structure for table `scorm_tree`
--

CREATE TABLE `scorm_tree` (
  `slm_id` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`slm_id`,`child`),
  KEY `i1_idx` (`child`),
  KEY `i2_idx` (`parent`)
) ;

--
-- Dumping data for table `scorm_tree`
--


--
-- Table structure for table `search_command_queue`
--

CREATE TABLE `search_command_queue` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` char(4) NOT NULL DEFAULT '',
  `sub_id` int(11) NOT NULL DEFAULT 0,
  `sub_type` char(4) DEFAULT NULL,
  `command` char(16) DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `finished` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`obj_type`,`sub_id`)
) ;

--
-- Dumping data for table `search_command_queue`
--


--
-- Table structure for table `search_data`
--

CREATE TABLE `search_data` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  `target` varchar(4000) DEFAULT NULL,
  `type` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`user_id`)
) ;

--
-- Dumping data for table `search_data`
--


--
-- Table structure for table `search_data_seq`
--

CREATE TABLE `search_data_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `search_data_seq`
--


--
-- Table structure for table `service_class`
--

CREATE TABLE `service_class` (
  `class` varchar(100) NOT NULL DEFAULT ' ',
  `service` varchar(100) DEFAULT NULL,
  `dir` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`class`)
) ;

--
-- Dumping data for table `service_class`
--

INSERT INTO `service_class` VALUES ('ilAccessibilityControlConceptGUI','Accessibility','classes');
INSERT INTO `service_class` VALUES ('ilAccordionPropertiesStorage','Accordion','classes');
INSERT INTO `service_class` VALUES ('ilAdministrationGUI','Administration','classes');
INSERT INTO `service_class` VALUES ('ilAwarenessGUI','Awareness','classes');
INSERT INTO `service_class` VALUES ('ilBTControllerGUI','BackgroundTasks','classes');
INSERT INTO `service_class` VALUES ('ilCharSelectorGUI','UIComponent','CharSelector/classes');
INSERT INTO `service_class` VALUES ('ilContainerBlockPropertiesStorage','Container','classes');
INSERT INTO `service_class` VALUES ('ilCronManagerGUI','Cron','classes');
INSERT INTO `service_class` VALUES ('ilDashboardGUI','Dashboard','classes');
INSERT INTO `service_class` VALUES ('ilDerivedTasksGUI','Tasks','DerivedTasks/classes');
INSERT INTO `service_class` VALUES ('ilHelpGUI','Help','classes');
INSERT INTO `service_class` VALUES ('ilImprintGUI','Imprint','classes');
INSERT INTO `service_class` VALUES ('ilLTIRouterGUI','LTI','classes');
INSERT INTO `service_class` VALUES ('ilMailGUI','Mail','classes');
INSERT INTO `service_class` VALUES ('ilMembershipOverviewGUI','Membership','classes');
INSERT INTO `service_class` VALUES ('ilNavigationHistoryGUI','Navigation','classes');
INSERT INTO `service_class` VALUES ('ilNotificationGUI','Notifications','classes');
INSERT INTO `service_class` VALUES ('ilObjPluginDispatchGUI','Repository','classes');
INSERT INTO `service_class` VALUES ('ilOnScreenChatGUI','OnScreenChat','classes');
INSERT INTO `service_class` VALUES ('ilPreviewGUI','Preview','classes');
INSERT INTO `service_class` VALUES ('ilPublicUserProfileGUI','User','classes');
INSERT INTO `service_class` VALUES ('ilRepositoryGUI','Repository','classes');
INSERT INTO `service_class` VALUES ('ilSearchController','Search','classes');
INSERT INTO `service_class` VALUES ('ilSharedResourceGUI','PersonalWorkspace','classes');
INSERT INTO `service_class` VALUES ('ilStartUpGUI','Init','classes');
INSERT INTO `service_class` VALUES ('ilTablePropertiesStorage','Table','classes');
INSERT INTO `service_class` VALUES ('ilTaggingSlateContentGUI','Tagging','classes');
INSERT INTO `service_class` VALUES ('ilUIPluginRouterGUI','UIComponent','classes');

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `module` varchar(50) NOT NULL DEFAULT 'common',
  `keyword` varchar(50) NOT NULL DEFAULT ' ',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`module`,`keyword`)
) ;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` VALUES ('adve','autosave','30');
INSERT INTO `settings` VALUES ('adve','auto_url_linking','1');
INSERT INTO `settings` VALUES ('adve','block_mode_minutes','0');
INSERT INTO `settings` VALUES ('adve','use_physical','');
INSERT INTO `settings` VALUES ('assessment','assessment_manual_scoring','8,14');
INSERT INTO `settings` VALUES ('assessment','ass_process_lock_mode','db');
INSERT INTO `settings` VALUES ('assessment','quest_process_lock_mode_autoinit_done','1');
INSERT INTO `settings` VALUES ('assessment','use_javascript','1');
INSERT INTO `settings` VALUES ('awrn','awrn_enabled','1');
INSERT INTO `settings` VALUES ('awrn','caching_period','20');
INSERT INTO `settings` VALUES ('awrn','max_nr_entries','50');
INSERT INTO `settings` VALUES ('awrn','up_act_adm_contacts','2');
INSERT INTO `settings` VALUES ('awrn','up_act_contact_approved','2');
INSERT INTO `settings` VALUES ('awrn','up_act_contact_requests','1');
INSERT INTO `settings` VALUES ('awrn','up_act_crs_contacts','1');
INSERT INTO `settings` VALUES ('awrn','up_act_crs_current','0');
INSERT INTO `settings` VALUES ('awrn','up_act_mmbr_user_grpcrs','0');
INSERT INTO `settings` VALUES ('awrn','up_act_user_all','0');
INSERT INTO `settings` VALUES ('awrn','use_osd','1');
INSERT INTO `settings` VALUES ('bdga','active','1');
INSERT INTO `settings` VALUES ('bdga','components','a:2:{i:0;s:3:\"crs\";i:1;s:4:\"user\";}');
INSERT INTO `settings` VALUES ('bdga','obi_active','');
INSERT INTO `settings` VALUES ('bdga','obi_contact','');
INSERT INTO `settings` VALUES ('bdga','obi_organisation','');
INSERT INTO `settings` VALUES ('bdga','obi_salt','');
INSERT INTO `settings` VALUES ('buddysystem','enabled','1');
INSERT INTO `settings` VALUES ('calendar','cache_enabled','0');
INSERT INTO `settings` VALUES ('calendar','cache_minutes','0');
INSERT INTO `settings` VALUES ('calendar','cg_registration','1');
INSERT INTO `settings` VALUES ('calendar','consultation_hours','0');
INSERT INTO `settings` VALUES ('calendar','course_cal','1');
INSERT INTO `settings` VALUES ('calendar','default_date_format','1');
INSERT INTO `settings` VALUES ('calendar','default_day_end','19');
INSERT INTO `settings` VALUES ('calendar','default_day_start','8');
INSERT INTO `settings` VALUES ('calendar','default_timezone','Europe/Berlin');
INSERT INTO `settings` VALUES ('calendar','default_time_format','1');
INSERT INTO `settings` VALUES ('calendar','default_week_start','1');
INSERT INTO `settings` VALUES ('calendar','enabled','1');
INSERT INTO `settings` VALUES ('calendar','enable_grp_milestones','0');
INSERT INTO `settings` VALUES ('calendar','group_cal','1');
INSERT INTO `settings` VALUES ('calendar','notification','0');
INSERT INTO `settings` VALUES ('calendar','notification_user','0');
INSERT INTO `settings` VALUES ('calendar','sync_cache_enabled','0');
INSERT INTO `settings` VALUES ('calendar','sync_cache_minutes','10');
INSERT INTO `settings` VALUES ('calendar','webcal_sync','1');
INSERT INTO `settings` VALUES ('calendar','webcal_sync_hours','1');
INSERT INTO `settings` VALUES ('certificate','persisting_cers_introduced_ts','1605888782');
INSERT INTO `settings` VALUES ('chatroom','conversation_idle_state_in_minutes','1');
INSERT INTO `settings` VALUES ('chatroom','public_room_ref','46');
INSERT INTO `settings` VALUES ('cmix','ilias_uuid','b4b4f485-9c96-4593-bb0b-9674d0840834');
INSERT INTO `settings` VALUES ('common','allow_change_loginname','0');
INSERT INTO `settings` VALUES ('common','anonymous_role_id','14');
INSERT INTO `settings` VALUES ('common','anonymous_user_id','13');
INSERT INTO `settings` VALUES ('common','approve_recipient','');
INSERT INTO `settings` VALUES ('common','auth_mode','1');
INSERT INTO `settings` VALUES ('common','auto_complete_length','10');
INSERT INTO `settings` VALUES ('common','auto_registration','1');
INSERT INTO `settings` VALUES ('common','bench_max_records','10000');
INSERT INTO `settings` VALUES ('common','block_activated_pdtag','1');
INSERT INTO `settings` VALUES ('common','block_activated_pdusers','1');
INSERT INTO `settings` VALUES ('common','chat_export_period','1');
INSERT INTO `settings` VALUES ('common','chat_export_status','0');
INSERT INTO `settings` VALUES ('common','comments_del_tutor','1');
INSERT INTO `settings` VALUES ('common','comments_del_user','0');
INSERT INTO `settings` VALUES ('common','comments_noti_recip','');
INSERT INTO `settings` VALUES ('common','common','system_user_id');
INSERT INTO `settings` VALUES ('common','convert_path','');
INSERT INTO `settings` VALUES ('common','create_history_loginname','0');
INSERT INTO `settings` VALUES ('common','custom_icon_big_height','32');
INSERT INTO `settings` VALUES ('common','custom_icon_big_width','32');
INSERT INTO `settings` VALUES ('common','custom_icon_small_height','22');
INSERT INTO `settings` VALUES ('common','custom_icon_small_width','22');
INSERT INTO `settings` VALUES ('common','custom_icon_tiny_height','16');
INSERT INTO `settings` VALUES ('common','custom_icon_tiny_width','16');
INSERT INTO `settings` VALUES ('common','dbupdate_randtest_pooldef_migration_fix','2');
INSERT INTO `settings` VALUES ('common','dbupwarn_tos_migr_54x','1');
INSERT INTO `settings` VALUES ('common','dbupwarn_tstfixqstseq','1');
INSERT INTO `settings` VALUES ('common','dbup_tst_skl_thres_mig_done','1');
INSERT INTO `settings` VALUES ('common','db_hotfixes_5_3','18');
INSERT INTO `settings` VALUES ('common','db_hotfixes_7','108');
INSERT INTO `settings` VALUES ('common','db_update_running','0');
INSERT INTO `settings` VALUES ('common','db_version','5751');
INSERT INTO `settings` VALUES ('common','default_repository_view','flat');
INSERT INTO `settings` VALUES ('common','disable_bookmarks','0');
INSERT INTO `settings` VALUES ('common','disable_comments','0');
INSERT INTO `settings` VALUES ('common','disable_contacts','0');
INSERT INTO `settings` VALUES ('common','disable_contacts_require_mail','1');
INSERT INTO `settings` VALUES ('common','disable_my_memberships','0');
INSERT INTO `settings` VALUES ('common','disable_my_offers','0');
INSERT INTO `settings` VALUES ('common','disable_notes','0');
INSERT INTO `settings` VALUES ('common','enable_anonymous_fora','');
INSERT INTO `settings` VALUES ('common','enable_bench','0');
INSERT INTO `settings` VALUES ('common','enable_calendar','1');
INSERT INTO `settings` VALUES ('common','enable_cat_page_edit','1');
INSERT INTO `settings` VALUES ('common','enable_fora_statistics','');
INSERT INTO `settings` VALUES ('common','enable_js_edit','1');
INSERT INTO `settings` VALUES ('common','enable_registration','1');
INSERT INTO `settings` VALUES ('common','enable_sahs_pd','1');
INSERT INTO `settings` VALUES ('common','enable_tracking','0');
INSERT INTO `settings` VALUES ('common','enable_trash','1');
INSERT INTO `settings` VALUES ('common','error_recipient','');
INSERT INTO `settings` VALUES ('common','feedback_recipient','');
INSERT INTO `settings` VALUES ('common','forum_notification','1');
INSERT INTO `settings` VALUES ('common','hide_adv_search','0');
INSERT INTO `settings` VALUES ('common','hits_per_page','50');
INSERT INTO `settings` VALUES ('common','https','0');
INSERT INTO `settings` VALUES ('common','icon_position_in_lists','item_rows');
INSERT INTO `settings` VALUES ('common','ilchtrbacfix','1');
INSERT INTO `settings` VALUES ('common','ilchtrperms','1');
INSERT INTO `settings` VALUES ('common','ilfrmnoti1','1');
INSERT INTO `settings` VALUES ('common','ilfrmreadidx1','1');
INSERT INTO `settings` VALUES ('common','ilfrmthri2','1');
INSERT INTO `settings` VALUES ('common','ilGlobalTstPoolUsageSettingInitilisation','1');
INSERT INTO `settings` VALUES ('common','ilias_version','3.2.3 2004-11-22');
INSERT INTO `settings` VALUES ('common','ilinc_akclassvalues_required','1');
INSERT INTO `settings` VALUES ('common','ilmpathix','1');
INSERT INTO `settings` VALUES ('common','iloscmsgidx1','1');
INSERT INTO `settings` VALUES ('common','iloscmsgidx2','1');
INSERT INTO `settings` VALUES ('common','iloscmsgidx3','1');
INSERT INTO `settings` VALUES ('common','ilpghi2','1');
INSERT INTO `settings` VALUES ('common','ilpgi3','1');
INSERT INTO `settings` VALUES ('common','ilrqtix','1');
INSERT INTO `settings` VALUES ('common','iltosobjinstall','1');
INSERT INTO `settings` VALUES ('common','inst_id','0');
INSERT INTO `settings` VALUES ('common','inst_info','');
INSERT INTO `settings` VALUES ('common','inst_institution','');
INSERT INTO `settings` VALUES ('common','inst_name','DBTemplate');
INSERT INTO `settings` VALUES ('common','java_path','');
INSERT INTO `settings` VALUES ('common','language','en');
INSERT INTO `settings` VALUES ('common','ldap_basedn','');
INSERT INTO `settings` VALUES ('common','ldap_port','');
INSERT INTO `settings` VALUES ('common','ldap_server','');
INSERT INTO `settings` VALUES ('common','letter_avatars','1');
INSERT INTO `settings` VALUES ('common','lm_qst_imap_migr_run','1');
INSERT INTO `settings` VALUES ('common','loginname_change_blocking_time','3600');
INSERT INTO `settings` VALUES ('common','lp_desktop','1');
INSERT INTO `settings` VALUES ('common','lp_extended_data','');
INSERT INTO `settings` VALUES ('common','lp_learner','1');
INSERT INTO `settings` VALUES ('common','lp_list_gui','0');
INSERT INTO `settings` VALUES ('common','lucene_default_operator','1');
INSERT INTO `settings` VALUES ('common','lucene_fragment_count','3');
INSERT INTO `settings` VALUES ('common','lucene_fragment_size','50');
INSERT INTO `settings` VALUES ('common','lucene_item_filter','a:0:{}');
INSERT INTO `settings` VALUES ('common','lucene_item_filter_enabled','0');
INSERT INTO `settings` VALUES ('common','lucene_last_index_time','1230807600');
INSERT INTO `settings` VALUES ('common','lucene_max_subitems','5');
INSERT INTO `settings` VALUES ('common','lucene_mime_filter','a:0:{}');
INSERT INTO `settings` VALUES ('common','lucene_mime_filter_enabled','0');
INSERT INTO `settings` VALUES ('common','lucene_offline_filter','0');
INSERT INTO `settings` VALUES ('common','lucene_prefix_wildcard','');
INSERT INTO `settings` VALUES ('common','lucene_show_relevance','1');
INSERT INTO `settings` VALUES ('common','lucene_sub_relevance','');
INSERT INTO `settings` VALUES ('common','lucene_user_search','1');
INSERT INTO `settings` VALUES ('common','mail_allow_external','1');
INSERT INTO `settings` VALUES ('common','mail_incoming_mail','0');
INSERT INTO `settings` VALUES ('common','mail_notification','1');
INSERT INTO `settings` VALUES ('common','mail_send_html','1');
INSERT INTO `settings` VALUES ('common','mail_subject_prefix','[ILIAS]');
INSERT INTO `settings` VALUES ('common','mail_system_sys_env_from_addr','');
INSERT INTO `settings` VALUES ('common','mail_system_sys_from_addr','');
INSERT INTO `settings` VALUES ('common','mail_system_sys_from_name','');
INSERT INTO `settings` VALUES ('common','mail_system_sys_reply_to_addr','');
INSERT INTO `settings` VALUES ('common','mail_system_sys_signature','\n\n* * * * *\n[CLIENT_NAME]\n[CLIENT_DESC]\n[CLIENT_URL]\n');
INSERT INTO `settings` VALUES ('common','mail_system_usr_env_from_addr','');
INSERT INTO `settings` VALUES ('common','mail_system_usr_from_addr','');
INSERT INTO `settings` VALUES ('common','mail_system_usr_from_name','');
INSERT INTO `settings` VALUES ('common','main_tree_impl','mp');
INSERT INTO `settings` VALUES ('common','new_registration_type','4');
INSERT INTO `settings` VALUES ('common','nic_key','d9f35b4eab4947c1557ce5a533c3f0cf');
INSERT INTO `settings` VALUES ('common','object_statistics','1');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_bibl','99990170');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_blog','99990130');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_book','99990220');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_cat','99990010');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_catr','99990020');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_chtr','99990120');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_cld','99990160');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_crs','99990040');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_crsr','99990050');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_dcl','99990210');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_exc','99990280');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_feed','99990140');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_file','99990150');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_fold','99990080');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_frm','99990110');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_glo','99990260');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp','99990060');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grpr','99990070');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_bibl','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_blog','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_book','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_cat','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_catr','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_chtr','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_cld','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_crs','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_crsr','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_dcl','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_exc','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_feed','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_file','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_fold','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_frm','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_glo','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_grp','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_grpr','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_htlm','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_iass','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_itgr','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_lm','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_mcst','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_mep','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_orgu','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_poll','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_prg','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_prtt','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_qpl','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_sahs','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_sess','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_spl','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_svy','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_tst','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_webr','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_grp_wiki','0');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_htlm','99990240');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_iass','99990300');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_itgr','99990090');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_lm','99990230');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_mcst','99990190');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_mep','99990330');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_orgu','99990360');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_poll','99990320');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_prg','99990030');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_prtt','99990270');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_qpl','99990340');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_sahs','99990250');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_sess','99990100');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_spl','99990350');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_svy','99990310');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_tst','99990290');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_webr','99990180');
INSERT INTO `settings` VALUES ('common','obj_add_new_pos_wiki','99990200');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_bibl','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_blog','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_book','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_cat','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_catr','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_chtr','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_cld','1');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_crs','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_crsr','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_dbk','1');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_dcl','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_exc','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_feed','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_file','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_fold','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_frm','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_glo','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_grp','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_grpr','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_htlm','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_iass','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_itgr','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_lm','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_mcst','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_mep','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_orgu','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_poll','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_prg','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_prtt','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_qpl','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_sahs','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_sess','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_spl','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_svy','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_tst','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_webr','');
INSERT INTO `settings` VALUES ('common','obj_dis_creation_wiki','');
INSERT INTO `settings` VALUES ('common','password_assistance','1');
INSERT INTO `settings` VALUES ('common','pd_active_pres_view_0','a:2:{i:0;s:4:\"list\";i:1;s:4:\"tile\";}');
INSERT INTO `settings` VALUES ('common','pd_active_pres_view_1','a:2:{i:0;s:4:\"list\";i:1;s:4:\"tile\";}');
INSERT INTO `settings` VALUES ('common','pd_active_sort_view_0','a:2:{i:0;s:8:\"location\";i:1;s:4:\"type\";}');
INSERT INTO `settings` VALUES ('common','pd_active_sort_view_1','a:3:{i:0;s:8:\"location\";i:1;s:4:\"type\";i:2;s:10:\"start_date\";}');
INSERT INTO `settings` VALUES ('common','pd_def_pres_view_0','list');
INSERT INTO `settings` VALUES ('common','pd_def_pres_view_1','list');
INSERT INTO `settings` VALUES ('common','personal_items_default_view','0');
INSERT INTO `settings` VALUES ('common','preview_learner','1');
INSERT INTO `settings` VALUES ('common','ps_access_times','');
INSERT INTO `settings` VALUES ('common','ps_account_security_mode','2');
INSERT INTO `settings` VALUES ('common','ps_crs_access_times','');
INSERT INTO `settings` VALUES ('common','ps_export_confirm','');
INSERT INTO `settings` VALUES ('common','ps_export_confirm_group','');
INSERT INTO `settings` VALUES ('common','ps_export_course','');
INSERT INTO `settings` VALUES ('common','ps_export_group','');
INSERT INTO `settings` VALUES ('common','ps_export_scorm','1');
INSERT INTO `settings` VALUES ('common','ps_login_max_attempts','0');
INSERT INTO `settings` VALUES ('common','ps_password_change_on_first_login_enabled','1');
INSERT INTO `settings` VALUES ('common','ps_password_chars_and_numbers_enabled','');
INSERT INTO `settings` VALUES ('common','ps_password_lowercase_chars_num','0');
INSERT INTO `settings` VALUES ('common','ps_password_max_age','0');
INSERT INTO `settings` VALUES ('common','ps_password_max_length','0');
INSERT INTO `settings` VALUES ('common','ps_password_min_length','6');
INSERT INTO `settings` VALUES ('common','ps_password_must_not_contain_loginame','1');
INSERT INTO `settings` VALUES ('common','ps_password_special_chars_enabled','');
INSERT INTO `settings` VALUES ('common','ps_password_uppercase_chars_num','0');
INSERT INTO `settings` VALUES ('common','ps_prevent_simultaneous_logins','0');
INSERT INTO `settings` VALUES ('common','ps_protect_admin','1');
INSERT INTO `settings` VALUES ('common','pub_section','');
INSERT INTO `settings` VALUES ('common','rbac_log','1');
INSERT INTO `settings` VALUES ('common','rbac_log_age','6');
INSERT INTO `settings` VALUES ('common','recovery_folder_id','15');
INSERT INTO `settings` VALUES ('common','reg_hash_life_time','600');
INSERT INTO `settings` VALUES ('common','rep_favourites','1');
INSERT INTO `settings` VALUES ('common','rep_shorten_description','1');
INSERT INTO `settings` VALUES ('common','rep_shorten_description_length','128');
INSERT INTO `settings` VALUES ('common','rep_tree_limit_grp_crs','');
INSERT INTO `settings` VALUES ('common','rep_tree_synchronize','1');
INSERT INTO `settings` VALUES ('common','require_city','');
INSERT INTO `settings` VALUES ('common','require_country','');
INSERT INTO `settings` VALUES ('common','require_default_role','1');
INSERT INTO `settings` VALUES ('common','require_department','');
INSERT INTO `settings` VALUES ('common','require_email','1');
INSERT INTO `settings` VALUES ('common','require_fax','');
INSERT INTO `settings` VALUES ('common','require_firstname','1');
INSERT INTO `settings` VALUES ('common','require_gender','1');
INSERT INTO `settings` VALUES ('common','require_hobby','');
INSERT INTO `settings` VALUES ('common','require_institution','');
INSERT INTO `settings` VALUES ('common','require_lastname','1');
INSERT INTO `settings` VALUES ('common','require_login','1');
INSERT INTO `settings` VALUES ('common','require_passwd','1');
INSERT INTO `settings` VALUES ('common','require_passwd2','1');
INSERT INTO `settings` VALUES ('common','require_phone_home','');
INSERT INTO `settings` VALUES ('common','require_phone_mobile','');
INSERT INTO `settings` VALUES ('common','require_phone_office','');
INSERT INTO `settings` VALUES ('common','require_referral_comment','');
INSERT INTO `settings` VALUES ('common','require_street','');
INSERT INTO `settings` VALUES ('common','require_zipcode','');
INSERT INTO `settings` VALUES ('common','reuse_of_loginnames','1');
INSERT INTO `settings` VALUES ('common','rpc_server_host','');
INSERT INTO `settings` VALUES ('common','rpc_server_port','0');
INSERT INTO `settings` VALUES ('common','save_user_related_data','0');
INSERT INTO `settings` VALUES ('common','search_date_filter','');
INSERT INTO `settings` VALUES ('common','search_enabled_firstname','0');
INSERT INTO `settings` VALUES ('common','search_enabled_gender','0');
INSERT INTO `settings` VALUES ('common','search_enabled_hobby','0');
INSERT INTO `settings` VALUES ('common','search_enabled_institution','0');
INSERT INTO `settings` VALUES ('common','search_enabled_interests_general','0');
INSERT INTO `settings` VALUES ('common','search_enabled_interests_help_looking','0');
INSERT INTO `settings` VALUES ('common','search_enabled_interests_help_offered','0');
INSERT INTO `settings` VALUES ('common','search_enabled_lastname','0');
INSERT INTO `settings` VALUES ('common','search_enabled_matriculation','0');
INSERT INTO `settings` VALUES ('common','search_enabled_title','0');
INSERT INTO `settings` VALUES ('common','search_index','0');
INSERT INTO `settings` VALUES ('common','search_lucene','0');
INSERT INTO `settings` VALUES ('common','search_max_hits','10');
INSERT INTO `settings` VALUES ('common','search_show_inactiv_user','1');
INSERT INTO `settings` VALUES ('common','search_show_limited_user','1');
INSERT INTO `settings` VALUES ('common','session_allow_client_maintenance','1');
INSERT INTO `settings` VALUES ('common','session_handling_type','0');
INSERT INTO `settings` VALUES ('common','session_max_count','0');
INSERT INTO `settings` VALUES ('common','session_max_idle','30');
INSERT INTO `settings` VALUES ('common','session_max_idle_after_first_request','1');
INSERT INTO `settings` VALUES ('common','session_min_idle','15');
INSERT INTO `settings` VALUES ('common','session_reminder_enabled','');
INSERT INTO `settings` VALUES ('common','session_statistics','1');
INSERT INTO `settings` VALUES ('common','setup_ok','1');
INSERT INTO `settings` VALUES ('common','shib_federation_name','Shibboleth');
INSERT INTO `settings` VALUES ('common','shib_hos_type','external_wayf');
INSERT INTO `settings` VALUES ('common','shib_idp_list','');
INSERT INTO `settings` VALUES ('common','show_mail_settings','1');
INSERT INTO `settings` VALUES ('common','show_user_activity','1');
INSERT INTO `settings` VALUES ('common','soap_connect_timeout','0');
INSERT INTO `settings` VALUES ('common','soap_user_administration','0');
INSERT INTO `settings` VALUES ('common','soap_wsdl_path','');
INSERT INTO `settings` VALUES ('common','sty_media_cont_mig','1');
INSERT INTO `settings` VALUES ('common','system_role_id','2');
INSERT INTO `settings` VALUES ('common','system_user_id','6');
INSERT INTO `settings` VALUES ('common','sys_advanced_editing_id','26');
INSERT INTO `settings` VALUES ('common','sys_assessment_folder_id','20');
INSERT INTO `settings` VALUES ('common','sys_user_tracking_id','17');
INSERT INTO `settings` VALUES ('common','tos_status','0');
INSERT INTO `settings` VALUES ('common','tracking_time_span','300');
INSERT INTO `settings` VALUES ('common','tst_score_rep_consts_cleaned','1');
INSERT INTO `settings` VALUES ('common','unzip_path','');
INSERT INTO `settings` VALUES ('common','user_activity_time','5');
INSERT INTO `settings` VALUES ('common','user_adm_alpha_nav','1');
INSERT INTO `settings` VALUES ('common','user_delete_own_account','0');
INSERT INTO `settings` VALUES ('common','user_delete_own_account_email','');
INSERT INTO `settings` VALUES ('common','user_portfolios','1');
INSERT INTO `settings` VALUES ('common','user_reactivate_code','0');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_birthday','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_bs_allow_to_contact_me','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_city','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_country','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_delicious','0');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_department','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_email','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_fax','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_firstname','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_gender','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_hide_own_online_status','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_hits_per_page','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_hobby','0');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_institution','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_interests_general','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_interests_help_looking','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_interests_help_offered','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_language','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_lastname','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_mail_incoming_mail','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_matriculation','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_password','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_phone_home','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_phone_mobile','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_phone_office','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_preferences','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_referral_comment','0');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_roles','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_sel_country','0');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_skin_style','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_street','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_title','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_upload','1');
INSERT INTO `settings` VALUES ('common','usr_settings_changeable_lua_zipcode','1');
INSERT INTO `settings` VALUES ('common','usr_settings_disable_delicious','1');
INSERT INTO `settings` VALUES ('common','usr_settings_disable_hobby','1');
INSERT INTO `settings` VALUES ('common','usr_settings_disable_preferences','1');
INSERT INTO `settings` VALUES ('common','usr_settings_disable_referral_comment','1');
INSERT INTO `settings` VALUES ('common','usr_settings_disable_sel_country','1');
INSERT INTO `settings` VALUES ('common','usr_settings_hide_delicious','1');
INSERT INTO `settings` VALUES ('common','usr_settings_hide_hide_own_online_status','1');
INSERT INTO `settings` VALUES ('common','usr_settings_hide_hobby','1');
INSERT INTO `settings` VALUES ('common','usr_settings_hide_preferences','1');
INSERT INTO `settings` VALUES ('common','usr_settings_hide_sel_country','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_birthday','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_bs_allow_to_contact_me','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_city','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_country','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_delicious','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_department','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_email','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_fax','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_firstname','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_gender','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_hide_own_online_status','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_hits_per_page','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_hobby','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_instant_messengers','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_institution','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_interests_general','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_interests_help_looking','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_interests_help_offered','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_language','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_lastname','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_mail_incoming_mail','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_matriculation','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_password','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_phone_home','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_phone_mobile','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_phone_office','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_preferences','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_referral_comment','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_roles','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_sel_country','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_skin_style','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_street','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_title','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_upload','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_lua_zipcode','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_birthday','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_bs_allow_to_contact_me','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_city','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_country','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_delicious','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_department','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_email','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_firstname','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_gender','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_hits_per_page','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_hobby','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_institution','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_interests_general','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_interests_help_looking','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_interests_help_offered','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_language','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_lastname','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_mail_incoming_mail','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_matriculation','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_password','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_phone_office','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_preferences','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_referral_comment','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_roles','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_sel_country','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_skin_style','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_street','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_title','1');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_upload','0');
INSERT INTO `settings` VALUES ('common','usr_settings_visib_reg_zipcode','1');
INSERT INTO `settings` VALUES ('common','usr_starting_point','1');
INSERT INTO `settings` VALUES ('common','zip_path','');
INSERT INTO `settings` VALUES ('file_access','inline_file_extensions','gif jpg jpeg mp3 pdf png');
INSERT INTO `settings` VALUES ('fold','bgtask_download','');
INSERT INTO `settings` VALUES ('fold','enable_download_folder','');
INSERT INTO `settings` VALUES ('fold','enable_multi_download','');
INSERT INTO `settings` VALUES ('lm','cont_upload_dir','');
INSERT INTO `settings` VALUES ('lm','html_export_ids','');
INSERT INTO `settings` VALUES ('lm','page_history','1');
INSERT INTO `settings` VALUES ('lm','replace_mob_feature','');
INSERT INTO `settings` VALUES ('lm','scormdebug_disable_cache','');
INSERT INTO `settings` VALUES ('lm','scormdebug_global_activate','');
INSERT INTO `settings` VALUES ('lm','scorm_login_as_learner_id','');
INSERT INTO `settings` VALUES ('lm','scorm_lp_auto_activate','1');
INSERT INTO `settings` VALUES ('lm','scorm_without_session','1');
INSERT INTO `settings` VALUES ('lm','time_scheduled_page_activation','1');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_browser','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_content','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_context','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_contribute','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_copyright','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_costs','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_coverage','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_density','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_difficulty','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_format','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_interactivity','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_keyword','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_language','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_level','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_operating_system','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_purpose','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_resource','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_status','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_structure','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_taxon','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_user_role','0');
INSERT INTO `settings` VALUES ('lucene_adv_search','lom_version','0');
INSERT INTO `settings` VALUES ('MathJax','path_to_mathjax','https://cdn.jsdelivr.net/npm/mathjax@2.7.9/MathJax.js?config=TeX-AMS-MML_HTMLorMML,Safe');
INSERT INTO `settings` VALUES ('mobs','black_list_file_types','html');
INSERT INTO `settings` VALUES ('mobs','file_manager_always','');
INSERT INTO `settings` VALUES ('mobs','mep_activate_pages','1');
INSERT INTO `settings` VALUES ('mobs','restricted_file_types','');
INSERT INTO `settings` VALUES ('mobs','upload_dir','');
INSERT INTO `settings` VALUES ('news','acc_cache_mins','10');
INSERT INTO `settings` VALUES ('notifications','enable_mail','1');
INSERT INTO `settings` VALUES ('notifications','enable_osd','1');
INSERT INTO `settings` VALUES ('pd','user_activity_time','0');
INSERT INTO `settings` VALUES ('preview','max_previews_per_object','5');
INSERT INTO `settings` VALUES ('preview','preview_enabled','1');
INSERT INTO `settings` VALUES ('prfa','banner','1');
INSERT INTO `settings` VALUES ('prfa','banner_height','100');
INSERT INTO `settings` VALUES ('prfa','banner_width','1370');
INSERT INTO `settings` VALUES ('prfa','mask','0');
INSERT INTO `settings` VALUES ('prfa','mycrs','1');
INSERT INTO `settings` VALUES ('prfa','pd_block','1');
INSERT INTO `settings` VALUES ('survey','unlimited_invitation','1');
INSERT INTO `settings` VALUES ('tags','enable','1');
INSERT INTO `settings` VALUES ('user_account','lua_access_restricted','');
INSERT INTO `settings` VALUES ('user_account','lua_enabled','');
INSERT INTO `settings` VALUES ('webdav','custom_webfolder_instructions','');
INSERT INTO `settings` VALUES ('webdav','custom_webfolder_instructions_enabled','0');
INSERT INTO `settings` VALUES ('webdav','webdav_enabled','0');

--
-- Table structure for table `settings_deactivated_s`
--

CREATE TABLE `settings_deactivated_s` (
  `skin` varchar(100) NOT NULL DEFAULT ' ',
  `style` varchar(100) NOT NULL DEFAULT ' ',
  PRIMARY KEY (`skin`,`style`)
) ;

--
-- Dumping data for table `settings_deactivated_s`
--


--
-- Table structure for table `shib_role_assignment`
--

CREATE TABLE `shib_role_assignment` (
  `rule_id` int(11) NOT NULL DEFAULT 0,
  `role_id` int(11) NOT NULL DEFAULT 0,
  `name` char(255) DEFAULT NULL,
  `value` char(255) DEFAULT NULL,
  `plugin` tinyint(4) NOT NULL DEFAULT 0,
  `plugin_id` int(11) NOT NULL DEFAULT 0,
  `add_on_update` tinyint(4) NOT NULL DEFAULT 0,
  `remove_on_update` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rule_id`)
) ;

--
-- Dumping data for table `shib_role_assignment`
--


--
-- Table structure for table `shib_role_assignment_seq`
--

CREATE TABLE `shib_role_assignment_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `shib_role_assignment_seq`
--


--
-- Table structure for table `skl_assigned_material`
--

CREATE TABLE `skl_assigned_material` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `top_skill_id` int(11) NOT NULL DEFAULT 0,
  `skill_id` int(11) NOT NULL DEFAULT 0,
  `level_id` int(11) NOT NULL DEFAULT 0,
  `wsp_id` int(11) NOT NULL DEFAULT 0,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`top_skill_id`,`tref_id`,`skill_id`,`level_id`,`wsp_id`)
) ;

--
-- Dumping data for table `skl_assigned_material`
--


--
-- Table structure for table `skl_level`
--

CREATE TABLE `skl_level` (
  `id` int(11) NOT NULL DEFAULT 0,
  `skill_id` int(11) NOT NULL DEFAULT 0,
  `nr` smallint(6) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `trigger_ref_id` int(11) NOT NULL DEFAULT 0,
  `trigger_obj_id` int(11) NOT NULL DEFAULT 0,
  `creation_date` datetime DEFAULT NULL,
  `import_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `skl_level`
--


--
-- Table structure for table `skl_level_seq`
--

CREATE TABLE `skl_level_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `skl_level_seq`
--


--
-- Table structure for table `skl_personal_skill`
--

CREATE TABLE `skl_personal_skill` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `skill_node_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`skill_node_id`)
) ;

--
-- Dumping data for table `skl_personal_skill`
--


--
-- Table structure for table `skl_profile`
--

CREATE TABLE `skl_profile` (
  `id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `skl_profile`
--


--
-- Table structure for table `skl_profile_level`
--

CREATE TABLE `skl_profile_level` (
  `profile_id` int(11) NOT NULL DEFAULT 0,
  `base_skill_id` int(11) NOT NULL DEFAULT 0,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  `level_id` int(11) NOT NULL DEFAULT 0,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`profile_id`,`tref_id`,`base_skill_id`)
) ;

--
-- Dumping data for table `skl_profile_level`
--


--
-- Table structure for table `skl_profile_role`
--

CREATE TABLE `skl_profile_role` (
  `profile_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`profile_id`,`role_id`)
) ;

--
-- Dumping data for table `skl_profile_role`
--


--
-- Table structure for table `skl_profile_seq`
--

CREATE TABLE `skl_profile_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `skl_profile_seq`
--


--
-- Table structure for table `skl_profile_user`
--

CREATE TABLE `skl_profile_user` (
  `profile_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`profile_id`,`user_id`)
) ;

--
-- Dumping data for table `skl_profile_user`
--


--
-- Table structure for table `skl_self_eval`
--

CREATE TABLE `skl_self_eval` (
  `id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `top_skill_id` int(11) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `last_update` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `skl_self_eval`
--


--
-- Table structure for table `skl_self_eval_level`
--

CREATE TABLE `skl_self_eval_level` (
  `skill_id` int(11) NOT NULL DEFAULT 0,
  `level_id` int(11) NOT NULL DEFAULT 0,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `top_skill_id` int(11) NOT NULL DEFAULT 0,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`,`top_skill_id`,`tref_id`,`skill_id`)
) ;

--
-- Dumping data for table `skl_self_eval_level`
--


--
-- Table structure for table `skl_self_eval_seq`
--

CREATE TABLE `skl_self_eval_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `skl_self_eval_seq`
--


--
-- Table structure for table `skl_skill_resource`
--

CREATE TABLE `skl_skill_resource` (
  `base_skill_id` int(11) NOT NULL DEFAULT 0,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  `level_id` int(11) NOT NULL DEFAULT 0,
  `rep_ref_id` int(11) NOT NULL DEFAULT 0,
  `imparting` tinyint(4) NOT NULL DEFAULT 0,
  `ltrigger` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`base_skill_id`,`tref_id`,`level_id`,`rep_ref_id`)
) ;

--
-- Dumping data for table `skl_skill_resource`
--


--
-- Table structure for table `skl_templ_ref`
--

CREATE TABLE `skl_templ_ref` (
  `skl_node_id` int(11) NOT NULL DEFAULT 0,
  `templ_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`skl_node_id`)
) ;

--
-- Dumping data for table `skl_templ_ref`
--


--
-- Table structure for table `skl_tree`
--

CREATE TABLE `skl_tree` (
  `skl_tree_id` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`skl_tree_id`,`child`)
) ;

--
-- Dumping data for table `skl_tree`
--

INSERT INTO `skl_tree` VALUES (1,1,0,1,2,1);

--
-- Table structure for table `skl_tree_node`
--

CREATE TABLE `skl_tree_node` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  `type` char(4) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `self_eval` tinyint(4) NOT NULL DEFAULT 0,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `creation_date` datetime DEFAULT NULL,
  `import_id` varchar(50) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `skl_tree_node`
--

INSERT INTO `skl_tree_node` VALUES (1,'Skill Tree Root Node','skrt','2011-08-07 11:39:00',NULL,0,0,0,NULL,'',NULL);

--
-- Table structure for table `skl_tree_node_seq`
--

CREATE TABLE `skl_tree_node_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=2;

--
-- Dumping data for table `skl_tree_node_seq`
--

INSERT INTO `skl_tree_node_seq` VALUES (1);

--
-- Table structure for table `skl_usage`
--

CREATE TABLE `skl_usage` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `skill_id` int(11) NOT NULL DEFAULT 0,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`skill_id`,`tref_id`)
) ;

--
-- Dumping data for table `skl_usage`
--


--
-- Table structure for table `skl_user_has_level`
--

CREATE TABLE `skl_user_has_level` (
  `level_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `skill_id` int(11) NOT NULL DEFAULT 0,
  `trigger_ref_id` int(11) NOT NULL DEFAULT 0,
  `trigger_obj_id` int(11) NOT NULL DEFAULT 0,
  `trigger_title` varchar(200) DEFAULT NULL,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  `trigger_obj_type` varchar(4) DEFAULT 'crs',
  `self_eval` tinyint(4) NOT NULL DEFAULT 0,
  `next_level_fulfilment` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`level_id`,`user_id`,`trigger_obj_id`,`tref_id`,`self_eval`)
) ;

--
-- Dumping data for table `skl_user_has_level`
--


--
-- Table structure for table `skl_user_skill_level`
--

CREATE TABLE `skl_user_skill_level` (
  `level_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `status_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `skill_id` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `valid` tinyint(4) NOT NULL DEFAULT 0,
  `trigger_ref_id` int(11) NOT NULL DEFAULT 0,
  `trigger_obj_id` int(11) NOT NULL DEFAULT 0,
  `trigger_title` varchar(200) DEFAULT NULL,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  `trigger_obj_type` varchar(4) DEFAULT 'crs',
  `self_eval` tinyint(4) NOT NULL DEFAULT 0,
  `unique_identifier` varchar(80) DEFAULT NULL,
  `next_level_fulfilment` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`skill_id`,`tref_id`,`user_id`,`status_date`,`status`,`trigger_obj_id`,`self_eval`),
  KEY `isk_idx` (`skill_id`),
  KEY `ilv_idx` (`level_id`),
  KEY `ius_idx` (`user_id`),
  KEY `isd_idx` (`status_date`),
  KEY `ist_idx` (`status`),
  KEY `ivl_idx` (`valid`)
) ;

--
-- Dumping data for table `skl_user_skill_level`
--


--
-- Table structure for table `sty_media_query`
--

CREATE TABLE `sty_media_query` (
  `id` int(11) NOT NULL DEFAULT 0,
  `style_id` int(11) NOT NULL DEFAULT 0,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  `mquery` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `sty_media_query`
--


--
-- Table structure for table `sty_media_query_seq`
--

CREATE TABLE `sty_media_query_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `sty_media_query_seq`
--


--
-- Table structure for table `style_char`
--

CREATE TABLE `style_char` (
  `style_id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(30) NOT NULL DEFAULT ' ',
  `characteristic` varchar(30) NOT NULL DEFAULT ' ',
  `hide` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`style_id`,`type`,`characteristic`),
  KEY `i1_idx` (`style_id`)
) ;

--
-- Dumping data for table `style_char`
--


--
-- Table structure for table `style_color`
--

CREATE TABLE `style_color` (
  `style_id` int(11) NOT NULL DEFAULT 0,
  `color_name` varchar(30) NOT NULL DEFAULT '.',
  `color_code` char(10) DEFAULT NULL,
  PRIMARY KEY (`style_id`,`color_name`)
) ;

--
-- Dumping data for table `style_color`
--


--
-- Table structure for table `style_data`
--

CREATE TABLE `style_data` (
  `id` int(11) NOT NULL DEFAULT 0,
  `uptodate` tinyint(4) DEFAULT 0,
  `standard` tinyint(4) DEFAULT 0,
  `category` int(11) DEFAULT NULL,
  `active` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `style_data`
--


--
-- Table structure for table `style_folder_styles`
--

CREATE TABLE `style_folder_styles` (
  `folder_id` int(11) NOT NULL DEFAULT 0,
  `style_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`folder_id`,`style_id`)
) ;

--
-- Dumping data for table `style_folder_styles`
--


--
-- Table structure for table `style_parameter`
--

CREATE TABLE `style_parameter` (
  `id` int(11) NOT NULL DEFAULT 0,
  `style_id` int(11) NOT NULL DEFAULT 0,
  `tag` varchar(100) DEFAULT NULL,
  `class` varchar(100) DEFAULT NULL,
  `parameter` varchar(100) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `mq_id` int(11) NOT NULL DEFAULT 0,
  `custom` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`style_id`)
) ;

--
-- Dumping data for table `style_parameter`
--


--
-- Table structure for table `style_parameter_seq`
--

CREATE TABLE `style_parameter_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `style_parameter_seq`
--


--
-- Table structure for table `style_setting`
--

CREATE TABLE `style_setting` (
  `style_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(30) NOT NULL DEFAULT '',
  `value` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`style_id`,`name`)
) ;

--
-- Dumping data for table `style_setting`
--


--
-- Table structure for table `style_template`
--

CREATE TABLE `style_template` (
  `id` int(11) NOT NULL DEFAULT 0,
  `style_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(30) NOT NULL DEFAULT '',
  `preview` varchar(4000) DEFAULT NULL,
  `temp_type` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`style_id`)
) ;

--
-- Dumping data for table `style_template`
--


--
-- Table structure for table `style_template_class`
--

CREATE TABLE `style_template_class` (
  `template_id` int(11) NOT NULL DEFAULT 0,
  `class_type` char(30) NOT NULL DEFAULT '',
  `class` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`template_id`,`class_type`,`class`)
) ;

--
-- Dumping data for table `style_template_class`
--


--
-- Table structure for table `style_template_seq`
--

CREATE TABLE `style_template_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `style_template_seq`
--


--
-- Table structure for table `style_usage`
--

CREATE TABLE `style_usage` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `style_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`),
  KEY `i1_idx` (`style_id`)
) ;

--
-- Dumping data for table `style_usage`
--


--
-- Table structure for table `svy_360_appr`
--

CREATE TABLE `svy_360_appr` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `has_closed` int(11) DEFAULT 0,
  PRIMARY KEY (`obj_id`,`user_id`)
) ;

--
-- Dumping data for table `svy_360_appr`
--


--
-- Table structure for table `svy_360_rater`
--

CREATE TABLE `svy_360_rater` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `appr_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `anonymous_id` int(11) NOT NULL DEFAULT 0,
  `mail_sent` int(11) DEFAULT 0,
  PRIMARY KEY (`obj_id`,`appr_id`,`user_id`,`anonymous_id`)
) ;

--
-- Dumping data for table `svy_360_rater`
--


--
-- Table structure for table `svy_anonymous`
--

CREATE TABLE `svy_anonymous` (
  `anonymous_id` int(11) NOT NULL DEFAULT 0,
  `survey_key` varchar(32) DEFAULT NULL,
  `survey_fi` int(11) NOT NULL DEFAULT 0,
  `user_key` varchar(40) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `externaldata` varchar(4000) DEFAULT NULL,
  `sent` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`anonymous_id`),
  KEY `i1_idx` (`survey_key`,`survey_fi`),
  KEY `i2_idx` (`survey_fi`),
  KEY `i3_idx` (`sent`)
) ;

--
-- Dumping data for table `svy_anonymous`
--


--
-- Table structure for table `svy_anonymous_seq`
--

CREATE TABLE `svy_anonymous_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_anonymous_seq`
--


--
-- Table structure for table `svy_answer`
--

CREATE TABLE `svy_answer` (
  `answer_id` int(11) NOT NULL DEFAULT 0,
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `value` double DEFAULT NULL,
  `textanswer` text DEFAULT NULL,
  `rowvalue` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`answer_id`),
  KEY `i1_idx` (`question_fi`),
  KEY `i2_idx` (`active_fi`)
) ;

--
-- Dumping data for table `svy_answer`
--


--
-- Table structure for table `svy_answer_seq`
--

CREATE TABLE `svy_answer_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_answer_seq`
--


--
-- Table structure for table `svy_category`
--

CREATE TABLE `svy_category` (
  `category_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(1000) DEFAULT NULL,
  `defaultvalue` varchar(1) DEFAULT '0',
  `owner_fi` int(11) NOT NULL DEFAULT 0,
  `neutral` varchar(1) DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`category_id`),
  KEY `i1_idx` (`owner_fi`)
) ;

--
-- Dumping data for table `svy_category`
--

INSERT INTO `svy_category` VALUES (1,'dc_desired','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (2,'dc_undesired','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (3,'dc_agree','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (4,'dc_disagree','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (5,'dc_good','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (6,'dc_notacceptable','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (7,'dc_should','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (8,'dc_shouldnot','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (9,'dc_true','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (10,'dc_false','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (11,'dc_always','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (12,'dc_never','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (13,'dc_yes','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (14,'dc_no','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (15,'dc_neutral','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (16,'dc_undecided','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (17,'dc_fair','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (18,'dc_sometimes','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (19,'dc_stronglydesired','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (20,'dc_stronglyundesired','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (21,'dc_stronglyagree','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (22,'dc_stronglydisagree','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (23,'dc_verygood','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (24,'dc_poor','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (25,'dc_must','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (26,'dc_mustnot','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (27,'dc_definitelytrue','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (28,'dc_definitelyfalse','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (29,'dc_manytimes','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (30,'dc_varying','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (31,'dc_rarely','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (32,'dc_mostcertainly','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (33,'dc_morepositive','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (34,'dc_morenegative','1',0,'0',1085190181);
INSERT INTO `svy_category` VALUES (35,'dc_mostcertainlynot','1',0,'0',1085190181);

--
-- Table structure for table `svy_category_seq`
--

CREATE TABLE `svy_category_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=36;

--
-- Dumping data for table `svy_category_seq`
--

INSERT INTO `svy_category_seq` VALUES (35);

--
-- Table structure for table `svy_constraint`
--

CREATE TABLE `svy_constraint` (
  `constraint_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `relation_fi` int(11) NOT NULL DEFAULT 0,
  `value` double NOT NULL DEFAULT 0,
  `conjunction` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`constraint_id`),
  KEY `i1_idx` (`question_fi`),
  KEY `i2_idx` (`relation_fi`)
) ;

--
-- Dumping data for table `svy_constraint`
--


--
-- Table structure for table `svy_constraint_seq`
--

CREATE TABLE `svy_constraint_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_constraint_seq`
--


--
-- Table structure for table `svy_finished`
--

CREATE TABLE `svy_finished` (
  `finished_id` int(11) NOT NULL DEFAULT 0,
  `survey_fi` int(11) NOT NULL DEFAULT 0,
  `user_fi` int(11) NOT NULL DEFAULT 0,
  `anonymous_id` varchar(32) DEFAULT NULL,
  `state` varchar(1) DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `lastpage` int(11) NOT NULL DEFAULT 0,
  `appr_id` int(11) DEFAULT 0,
  PRIMARY KEY (`finished_id`),
  KEY `i1_idx` (`survey_fi`),
  KEY `i2_idx` (`user_fi`),
  KEY `i3_idx` (`anonymous_id`)
) ;

--
-- Dumping data for table `svy_finished`
--


--
-- Table structure for table `svy_finished_seq`
--

CREATE TABLE `svy_finished_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_finished_seq`
--


--
-- Table structure for table `svy_invitation`
--

CREATE TABLE `svy_invitation` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `survey_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`survey_id`)
) ;

--
-- Dumping data for table `svy_invitation`
--


--
-- Table structure for table `svy_material`
--

CREATE TABLE `svy_material` (
  `material_id` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `internal_link` varchar(50) DEFAULT NULL,
  `import_id` varchar(50) DEFAULT NULL,
  `material_title` varchar(255) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `text_material` varchar(4000) DEFAULT NULL,
  `external_link` varchar(500) DEFAULT NULL,
  `file_material` varchar(200) DEFAULT NULL,
  `material_type` int(11) DEFAULT 0,
  PRIMARY KEY (`material_id`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `svy_material`
--


--
-- Table structure for table `svy_material_seq`
--

CREATE TABLE `svy_material_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_material_seq`
--


--
-- Table structure for table `svy_phrase`
--

CREATE TABLE `svy_phrase` (
  `phrase_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(100) DEFAULT NULL,
  `defaultvalue` varchar(1) DEFAULT '0',
  `owner_fi` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`phrase_id`),
  KEY `i1_idx` (`owner_fi`)
) ;

--
-- Dumping data for table `svy_phrase`
--

INSERT INTO `svy_phrase` VALUES (1,'dp_standard_attitude_desired_undesired','1',0,1085190871);
INSERT INTO `svy_phrase` VALUES (2,'dp_standard_attitude_agree_disagree','1',0,1085190898);
INSERT INTO `svy_phrase` VALUES (3,'dp_standard_attitude_good_notacceptable','1',0,1085190918);
INSERT INTO `svy_phrase` VALUES (4,'dp_standard_attitude_shold_shouldnot','1',0,1085190946);
INSERT INTO `svy_phrase` VALUES (5,'dp_standard_beliefs_true_false','1',0,1085190973);
INSERT INTO `svy_phrase` VALUES (6,'dp_standard_beliefs_always_never','1',0,1085191547);
INSERT INTO `svy_phrase` VALUES (7,'dp_standard_behaviour_yes_no','1',0,1085191547);
INSERT INTO `svy_phrase` VALUES (8,'dp_standard_attitude_desired_neutral_undesired','1',0,1085191547);
INSERT INTO `svy_phrase` VALUES (9,'dp_standard_attitude_agree_undecided_disagree','1',0,1085191547);
INSERT INTO `svy_phrase` VALUES (10,'dp_standard_attitude_good_fair_notacceptable','1',0,1085191547);
INSERT INTO `svy_phrase` VALUES (11,'dp_standard_attitude_should_undecided_shouldnot','1',0,1085191547);
INSERT INTO `svy_phrase` VALUES (12,'dp_standard_beliefs_true_undecided_false','1',0,1085191547);
INSERT INTO `svy_phrase` VALUES (13,'dp_standard_beliefs_always_sometimes_never','1',0,1085191547);
INSERT INTO `svy_phrase` VALUES (14,'dp_standard_behaviour_yes_undecided_no','1',0,1085191547);
INSERT INTO `svy_phrase` VALUES (15,'dp_standard_attitude_desired5','1',0,1085195222);
INSERT INTO `svy_phrase` VALUES (16,'dp_standard_attitude_agree5','1',0,1085195237);
INSERT INTO `svy_phrase` VALUES (17,'dp_standard_attitude_good5','1',0,1085195249);
INSERT INTO `svy_phrase` VALUES (18,'dp_standard_attitude_must5','1',0,1085195264);
INSERT INTO `svy_phrase` VALUES (19,'dp_standard_beliefs_true5','1',0,1085195274);
INSERT INTO `svy_phrase` VALUES (20,'dp_standard_beliefs_always5','1',0,1085195292);
INSERT INTO `svy_phrase` VALUES (21,'dp_standard_behaviour_certainly5','1',0,1085195308);
INSERT INTO `svy_phrase` VALUES (22,'dp_standard_numbers','1',0,1087738039);

--
-- Table structure for table `svy_phrase_cat`
--

CREATE TABLE `svy_phrase_cat` (
  `phrase_category_id` int(11) NOT NULL DEFAULT 0,
  `phrase_fi` int(11) NOT NULL DEFAULT 0,
  `category_fi` int(11) NOT NULL DEFAULT 0,
  `sequence` int(11) NOT NULL DEFAULT 0,
  `other` smallint(6) NOT NULL DEFAULT 0,
  `scale` int(11) DEFAULT NULL,
  PRIMARY KEY (`phrase_category_id`),
  KEY `i1_idx` (`phrase_fi`),
  KEY `i2_idx` (`category_fi`)
) ;

--
-- Dumping data for table `svy_phrase_cat`
--

INSERT INTO `svy_phrase_cat` VALUES (1,1,1,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (2,1,2,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (3,2,3,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (4,2,4,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (5,3,5,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (6,3,6,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (7,4,7,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (8,4,8,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (9,5,9,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (10,5,10,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (11,6,11,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (12,6,12,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (13,7,13,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (14,7,14,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (15,8,1,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (16,8,15,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (17,8,2,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (18,9,3,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (19,9,16,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (20,9,4,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (21,10,5,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (22,10,17,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (23,10,6,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (24,11,7,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (25,11,16,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (26,11,8,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (27,12,9,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (28,12,16,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (29,12,10,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (30,13,11,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (31,13,18,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (32,13,12,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (33,14,13,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (34,14,16,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (35,14,14,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (36,15,19,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (37,15,1,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (38,15,15,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (39,15,2,4,0,0);
INSERT INTO `svy_phrase_cat` VALUES (40,15,20,5,0,0);
INSERT INTO `svy_phrase_cat` VALUES (41,16,21,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (42,16,3,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (43,16,16,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (44,16,4,4,0,0);
INSERT INTO `svy_phrase_cat` VALUES (45,16,22,5,0,0);
INSERT INTO `svy_phrase_cat` VALUES (46,17,23,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (47,17,5,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (48,17,17,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (49,17,24,4,0,0);
INSERT INTO `svy_phrase_cat` VALUES (50,17,6,5,0,0);
INSERT INTO `svy_phrase_cat` VALUES (51,18,25,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (52,18,7,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (53,18,16,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (54,18,8,4,0,0);
INSERT INTO `svy_phrase_cat` VALUES (55,18,26,5,0,0);
INSERT INTO `svy_phrase_cat` VALUES (56,19,27,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (57,19,9,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (58,19,16,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (59,19,10,4,0,0);
INSERT INTO `svy_phrase_cat` VALUES (60,19,28,5,0,0);
INSERT INTO `svy_phrase_cat` VALUES (61,20,11,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (62,20,29,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (63,20,30,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (64,20,31,4,0,0);
INSERT INTO `svy_phrase_cat` VALUES (65,20,12,5,0,0);
INSERT INTO `svy_phrase_cat` VALUES (66,21,32,1,0,0);
INSERT INTO `svy_phrase_cat` VALUES (67,21,33,2,0,0);
INSERT INTO `svy_phrase_cat` VALUES (68,21,16,3,0,0);
INSERT INTO `svy_phrase_cat` VALUES (69,21,34,4,0,0);
INSERT INTO `svy_phrase_cat` VALUES (70,21,35,5,0,0);

--
-- Table structure for table `svy_phrase_cat_seq`
--

CREATE TABLE `svy_phrase_cat_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=71;

--
-- Dumping data for table `svy_phrase_cat_seq`
--

INSERT INTO `svy_phrase_cat_seq` VALUES (70);

--
-- Table structure for table `svy_phrase_seq`
--

CREATE TABLE `svy_phrase_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=23;

--
-- Dumping data for table `svy_phrase_seq`
--

INSERT INTO `svy_phrase_seq` VALUES (22);

--
-- Table structure for table `svy_qblk`
--

CREATE TABLE `svy_qblk` (
  `questionblock_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(4000) DEFAULT NULL,
  `show_questiontext` varchar(1) DEFAULT '1',
  `owner_fi` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `show_blocktitle` varchar(1) DEFAULT NULL,
  `compress_view` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`questionblock_id`),
  KEY `i1_idx` (`owner_fi`)
) ;

--
-- Dumping data for table `svy_qblk`
--


--
-- Table structure for table `svy_qblk_qst`
--

CREATE TABLE `svy_qblk_qst` (
  `qblk_qst_id` int(11) NOT NULL DEFAULT 0,
  `survey_fi` int(11) NOT NULL DEFAULT 0,
  `questionblock_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`qblk_qst_id`),
  KEY `i1_idx` (`survey_fi`),
  KEY `i2_idx` (`questionblock_fi`),
  KEY `i3_idx` (`question_fi`)
) ;

--
-- Dumping data for table `svy_qblk_qst`
--


--
-- Table structure for table `svy_qblk_qst_seq`
--

CREATE TABLE `svy_qblk_qst_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_qblk_qst_seq`
--


--
-- Table structure for table `svy_qblk_seq`
--

CREATE TABLE `svy_qblk_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_qblk_seq`
--


--
-- Table structure for table `svy_qpl`
--

CREATE TABLE `svy_qpl` (
  `id_questionpool` int(11) NOT NULL DEFAULT 0,
  `obj_fi` int(11) NOT NULL DEFAULT 0,
  `isonline` varchar(1) DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_questionpool`),
  KEY `i1_idx` (`obj_fi`)
) ;

--
-- Dumping data for table `svy_qpl`
--


--
-- Table structure for table `svy_qpl_seq`
--

CREATE TABLE `svy_qpl_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_qpl_seq`
--


--
-- Table structure for table `svy_qst_constraint`
--

CREATE TABLE `svy_qst_constraint` (
  `question_constraint_id` int(11) NOT NULL DEFAULT 0,
  `survey_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `constraint_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_constraint_id`),
  KEY `i1_idx` (`survey_fi`),
  KEY `i2_idx` (`question_fi`),
  KEY `i3_idx` (`constraint_fi`)
) ;

--
-- Dumping data for table `svy_qst_constraint`
--


--
-- Table structure for table `svy_qst_constraint_seq`
--

CREATE TABLE `svy_qst_constraint_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_qst_constraint_seq`
--


--
-- Table structure for table `svy_qst_matrix`
--

CREATE TABLE `svy_qst_matrix` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `subtype` int(11) NOT NULL DEFAULT 0,
  `column_separators` varchar(1) DEFAULT '0',
  `row_separators` varchar(1) DEFAULT '0',
  `neutral_column_separator` varchar(1) DEFAULT '1',
  `column_placeholders` int(11) NOT NULL DEFAULT 0,
  `legend` varchar(1) DEFAULT '0',
  `singleline_row_caption` varchar(1) DEFAULT '0',
  `repeat_column_header` varchar(1) DEFAULT '0',
  `column_header_position` varchar(1) DEFAULT '0',
  `random_rows` varchar(1) DEFAULT '0',
  `column_order` varchar(1) DEFAULT '0',
  `column_images` varchar(1) DEFAULT '0',
  `row_images` varchar(1) DEFAULT '0',
  `bipolar_adjective1` varchar(255) DEFAULT NULL,
  `bipolar_adjective2` varchar(255) DEFAULT NULL,
  `layout` varchar(4000) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `svy_qst_matrix`
--


--
-- Table structure for table `svy_qst_matrixrows`
--

CREATE TABLE `svy_qst_matrixrows` (
  `id_svy_qst_matrixrows` int(11) NOT NULL DEFAULT 0,
  `title` varchar(1000) DEFAULT NULL,
  `sequence` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `other` tinyint(4) NOT NULL DEFAULT 0,
  `label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_svy_qst_matrixrows`),
  KEY `i1_idx` (`question_fi`)
) ;

--
-- Dumping data for table `svy_qst_matrixrows`
--


--
-- Table structure for table `svy_qst_matrixrows_seq`
--

CREATE TABLE `svy_qst_matrixrows_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_qst_matrixrows_seq`
--


--
-- Table structure for table `svy_qst_mc`
--

CREATE TABLE `svy_qst_mc` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `orientation` varchar(1) DEFAULT '0',
  `use_min_answers` tinyint(4) NOT NULL DEFAULT 0,
  `nr_min_answers` smallint(6) DEFAULT NULL,
  `nr_max_answers` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `svy_qst_mc`
--


--
-- Table structure for table `svy_qst_metric`
--

CREATE TABLE `svy_qst_metric` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `subtype` varchar(1) DEFAULT '3',
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `svy_qst_metric`
--


--
-- Table structure for table `svy_qst_oblig`
--

CREATE TABLE `svy_qst_oblig` (
  `question_obligatory_id` int(11) NOT NULL DEFAULT 0,
  `survey_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `obligatory` varchar(1) DEFAULT '1',
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`question_obligatory_id`),
  KEY `i1_idx` (`survey_fi`,`question_fi`)
) ;

--
-- Dumping data for table `svy_qst_oblig`
--


--
-- Table structure for table `svy_qst_oblig_seq`
--

CREATE TABLE `svy_qst_oblig_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_qst_oblig_seq`
--


--
-- Table structure for table `svy_qst_sc`
--

CREATE TABLE `svy_qst_sc` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `orientation` varchar(1) DEFAULT '0',
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `svy_qst_sc`
--


--
-- Table structure for table `svy_qst_text`
--

CREATE TABLE `svy_qst_text` (
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `maxchars` int(11) DEFAULT NULL,
  `width` int(11) NOT NULL DEFAULT 50,
  `height` int(11) NOT NULL DEFAULT 5,
  PRIMARY KEY (`question_fi`)
) ;

--
-- Dumping data for table `svy_qst_text`
--


--
-- Table structure for table `svy_qtype`
--

CREATE TABLE `svy_qtype` (
  `questiontype_id` int(11) NOT NULL DEFAULT 0,
  `type_tag` varchar(30) DEFAULT NULL,
  `plugin` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`questiontype_id`)
) ;

--
-- Dumping data for table `svy_qtype`
--

INSERT INTO `svy_qtype` VALUES (1,'SurveyMultipleChoiceQuestion',0);
INSERT INTO `svy_qtype` VALUES (2,'SurveySingleChoiceQuestion',0);
INSERT INTO `svy_qtype` VALUES (3,'SurveyMetricQuestion',0);
INSERT INTO `svy_qtype` VALUES (4,'SurveyTextQuestion',0);
INSERT INTO `svy_qtype` VALUES (5,'SurveyMatrixQuestion',0);

--
-- Table structure for table `svy_qtype_seq`
--

CREATE TABLE `svy_qtype_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=6;

--
-- Dumping data for table `svy_qtype_seq`
--

INSERT INTO `svy_qtype_seq` VALUES (5);

--
-- Table structure for table `svy_quest_skill`
--

CREATE TABLE `svy_quest_skill` (
  `q_id` int(11) NOT NULL DEFAULT 0,
  `survey_id` int(11) NOT NULL DEFAULT 0,
  `base_skill_id` int(11) NOT NULL DEFAULT 0,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`q_id`)
) ;

--
-- Dumping data for table `svy_quest_skill`
--


--
-- Table structure for table `svy_question`
--

CREATE TABLE `svy_question` (
  `question_id` int(11) NOT NULL DEFAULT 0,
  `questiontype_fi` int(11) NOT NULL DEFAULT 0,
  `obj_fi` int(11) NOT NULL DEFAULT 0,
  `owner_fi` int(11) NOT NULL DEFAULT 0,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `obligatory` varchar(1) DEFAULT '1',
  `complete` varchar(1) DEFAULT '0',
  `created` varchar(14) DEFAULT NULL,
  `original_id` int(11) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `questiontext` longtext DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`question_id`),
  KEY `i1_idx` (`obj_fi`),
  KEY `i2_idx` (`owner_fi`)
) ;

--
-- Dumping data for table `svy_question`
--


--
-- Table structure for table `svy_question_seq`
--

CREATE TABLE `svy_question_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_question_seq`
--


--
-- Table structure for table `svy_relation`
--

CREATE TABLE `svy_relation` (
  `relation_id` int(11) NOT NULL DEFAULT 0,
  `longname` varchar(20) DEFAULT NULL,
  `shortname` varchar(2) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`relation_id`)
) ;

--
-- Dumping data for table `svy_relation`
--

INSERT INTO `svy_relation` VALUES (1,'less','<',1084867073);
INSERT INTO `svy_relation` VALUES (2,'less_or_equal','<=',1084867088);
INSERT INTO `svy_relation` VALUES (3,'equal','=',1084867096);
INSERT INTO `svy_relation` VALUES (4,'not_equal','<>',1084867119);
INSERT INTO `svy_relation` VALUES (5,'more_or_equal','>=',1084867132);
INSERT INTO `svy_relation` VALUES (6,'more','>',1084867143);

--
-- Table structure for table `svy_relation_seq`
--

CREATE TABLE `svy_relation_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=7;

--
-- Dumping data for table `svy_relation_seq`
--

INSERT INTO `svy_relation_seq` VALUES (6);

--
-- Table structure for table `svy_settings`
--

CREATE TABLE `svy_settings` (
  `settings_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `keyword` varchar(40) NOT NULL DEFAULT '',
  `title` varchar(400) DEFAULT NULL,
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`settings_id`),
  KEY `i1_idx` (`usr_id`)
) ;

--
-- Dumping data for table `svy_settings`
--


--
-- Table structure for table `svy_settings_seq`
--

CREATE TABLE `svy_settings_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_settings_seq`
--


--
-- Table structure for table `svy_skill_threshold`
--

CREATE TABLE `svy_skill_threshold` (
  `survey_id` int(11) NOT NULL DEFAULT 0,
  `base_skill_id` int(11) NOT NULL DEFAULT 0,
  `tref_id` int(11) NOT NULL DEFAULT 0,
  `level_id` int(11) NOT NULL DEFAULT 0,
  `threshold` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`survey_id`,`base_skill_id`,`tref_id`,`level_id`)
) ;

--
-- Dumping data for table `svy_skill_threshold`
--


--
-- Table structure for table `svy_svy`
--

CREATE TABLE `svy_svy` (
  `survey_id` int(11) NOT NULL DEFAULT 0,
  `obj_fi` int(11) NOT NULL DEFAULT 0,
  `author` varchar(50) DEFAULT NULL,
  `introduction` longtext DEFAULT NULL,
  `outro` longtext DEFAULT NULL,
  `status` varchar(1) DEFAULT '1',
  `evaluation_access` varchar(1) DEFAULT '0',
  `invitation` varchar(1) DEFAULT '0',
  `invitation_mode` varchar(1) DEFAULT '1',
  `complete` varchar(1) DEFAULT '0',
  `anonymize` varchar(1) DEFAULT '0',
  `show_question_titles` varchar(1) DEFAULT '1',
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `created` int(11) NOT NULL DEFAULT 0,
  `mailnotification` tinyint(4) DEFAULT NULL,
  `startdate` varchar(14) DEFAULT NULL,
  `enddate` varchar(14) DEFAULT NULL,
  `mailaddresses` varchar(2000) DEFAULT NULL,
  `mailparticipantdata` varchar(4000) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `pool_usage` tinyint(4) DEFAULT NULL,
  `mode` tinyint(4) NOT NULL DEFAULT 0,
  `mode_360_self_eval` tinyint(4) NOT NULL DEFAULT 0,
  `mode_360_self_rate` tinyint(4) NOT NULL DEFAULT 0,
  `mode_360_self_appr` tinyint(4) NOT NULL DEFAULT 0,
  `mode_360_results` tinyint(4) NOT NULL DEFAULT 0,
  `mode_skill_service` tinyint(4) NOT NULL DEFAULT 0,
  `reminder_status` tinyint(4) NOT NULL DEFAULT 0,
  `reminder_start` datetime DEFAULT NULL,
  `reminder_end` datetime DEFAULT NULL,
  `reminder_frequency` smallint(6) NOT NULL DEFAULT 0,
  `reminder_target` tinyint(4) NOT NULL DEFAULT 0,
  `tutor_ntf_status` tinyint(4) NOT NULL DEFAULT 0,
  `tutor_ntf_reci` varchar(2000) DEFAULT NULL,
  `tutor_ntf_target` tinyint(4) NOT NULL DEFAULT 0,
  `reminder_last_sent` datetime DEFAULT NULL,
  `own_results_view` tinyint(4) DEFAULT 0,
  `own_results_mail` tinyint(4) DEFAULT 0,
  `confirmation_mail` tinyint(4) DEFAULT NULL,
  `anon_user_list` tinyint(4) DEFAULT 0,
  `reminder_tmpl` int(11) DEFAULT NULL,
  `mode_self_eval_results` tinyint(4) DEFAULT 0,
  `tutor_res_status` tinyint(4) DEFAULT NULL,
  `tutor_res_reci` varchar(2000) DEFAULT NULL,
  `tutor_res_cron` tinyint(4) DEFAULT NULL,
  `calculate_sum_score` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`survey_id`),
  KEY `i1_idx` (`obj_fi`)
) ;

--
-- Dumping data for table `svy_svy`
--


--
-- Table structure for table `svy_svy_qst`
--

CREATE TABLE `svy_svy_qst` (
  `survey_question_id` int(11) NOT NULL DEFAULT 0,
  `survey_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `sequence` int(11) NOT NULL DEFAULT 0,
  `heading` varchar(4000) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`survey_question_id`),
  KEY `i1_idx` (`survey_fi`),
  KEY `i2_idx` (`question_fi`)
) ;

--
-- Dumping data for table `svy_svy_qst`
--


--
-- Table structure for table `svy_svy_qst_seq`
--

CREATE TABLE `svy_svy_qst_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_svy_qst_seq`
--


--
-- Table structure for table `svy_svy_seq`
--

CREATE TABLE `svy_svy_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_svy_seq`
--


--
-- Table structure for table `svy_times`
--

CREATE TABLE `svy_times` (
  `id` int(11) NOT NULL DEFAULT 0,
  `finished_fi` int(11) NOT NULL DEFAULT 0,
  `entered_page` int(11) DEFAULT NULL,
  `left_page` int(11) DEFAULT NULL,
  `first_question` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`finished_fi`)
) ;

--
-- Dumping data for table `svy_times`
--


--
-- Table structure for table `svy_times_seq`
--

CREATE TABLE `svy_times_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_times_seq`
--


--
-- Table structure for table `svy_variable`
--

CREATE TABLE `svy_variable` (
  `variable_id` int(11) NOT NULL DEFAULT 0,
  `category_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `value1` double DEFAULT NULL,
  `value2` double DEFAULT NULL,
  `sequence` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `other` smallint(6) NOT NULL DEFAULT 0,
  `scale` mediumint(9) DEFAULT NULL,
  PRIMARY KEY (`variable_id`),
  KEY `i1_idx` (`category_fi`),
  KEY `i2_idx` (`question_fi`)
) ;

--
-- Dumping data for table `svy_variable`
--


--
-- Table structure for table `svy_variable_seq`
--

CREATE TABLE `svy_variable_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `svy_variable_seq`
--


--
-- Table structure for table `sysc_groups`
--

CREATE TABLE `sysc_groups` (
  `id` int(11) NOT NULL DEFAULT 0,
  `component` char(16) DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `sysc_groups`
--

INSERT INTO `sysc_groups` VALUES (1,'tree',NULL,0);

--
-- Table structure for table `sysc_groups_seq`
--

CREATE TABLE `sysc_groups_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=2;

--
-- Dumping data for table `sysc_groups_seq`
--

INSERT INTO `sysc_groups_seq` VALUES (1);

--
-- Table structure for table `sysc_tasks`
--

CREATE TABLE `sysc_tasks` (
  `id` int(11) NOT NULL DEFAULT 0,
  `grp_id` int(11) NOT NULL DEFAULT 0,
  `last_update` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `identifier` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `sysc_tasks`
--

INSERT INTO `sysc_tasks` VALUES (1,1,NULL,0,'dump');
INSERT INTO `sysc_tasks` VALUES (2,1,NULL,0,'structure');
INSERT INTO `sysc_tasks` VALUES (3,1,NULL,0,'missing_tree');
INSERT INTO `sysc_tasks` VALUES (4,1,NULL,0,'missing_reference');
INSERT INTO `sysc_tasks` VALUES (5,1,NULL,0,'duplicates');

--
-- Table structure for table `sysc_tasks_seq`
--

CREATE TABLE `sysc_tasks_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=6;

--
-- Dumping data for table `sysc_tasks_seq`
--

INSERT INTO `sysc_tasks_seq` VALUES (5);

--
-- Table structure for table `syst_style_cat`
--

CREATE TABLE `syst_style_cat` (
  `skin_id` varchar(50) NOT NULL DEFAULT '',
  `style_id` varchar(50) NOT NULL DEFAULT '',
  `substyle` varchar(50) NOT NULL DEFAULT '',
  `category_ref_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`skin_id`,`style_id`,`substyle`,`category_ref_id`)
) ;

--
-- Dumping data for table `syst_style_cat`
--


--
-- Table structure for table `table_properties`
--

CREATE TABLE `table_properties` (
  `table_id` varchar(30) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `property` varchar(20) NOT NULL DEFAULT '',
  `value` varchar(4000) NOT NULL DEFAULT ' ',
  PRIMARY KEY (`table_id`,`user_id`,`property`)
) ;

--
-- Dumping data for table `table_properties`
--

INSERT INTO `table_properties` VALUES ('admsettemptst',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('bibl_libraries_tbl',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('crnmng',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('crnmng',6,'order','status');
INSERT INTO `table_properties` VALUES ('objroleperm_32',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('repmodtbl',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('repnwitgrptbl',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_options__134',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_options__14',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_options__4',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_options__5',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_accs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_adm',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_adve',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_assf',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_auth',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_awra',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_bibl',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_bibs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_blga',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_blog',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_book',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_cadm',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_cals',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_cat',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_catr',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_cert',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_chta',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_chtr',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_cmps',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_crs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_crsr',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_crss',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_dcl',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_ecss',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_exc',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_excs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_extt',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_facs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_feed',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_file',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_fold',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_frm',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_frma',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_glo',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_grp',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_grpr',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_grps',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_hlps',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_htlm',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_iass',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_itgr',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_lm',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_lngf',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_logs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_lrss',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_mail',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_mcst',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_mcts',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_mds',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_mep',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_mobs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_nwss',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_orgu',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_otpl',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_pays',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_pdts',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_poll',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_prfa',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_prg',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_prgs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_prtt',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_ps',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_qpl',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_rcat',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_rcrs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_recf',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_reps',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_rfil',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_rglo',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_rgrp',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_rlm',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_rolf',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_root',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_rtst',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_rwik',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_sahs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_seas',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_sess',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_skmg',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_spl',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_stys',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_svy',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_svyf',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_sysc',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_tags',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_taxs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_tos',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_trac',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_tst',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_usrf',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_wbrs',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_webr',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_wiki',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('role_template_8_wiks',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('rolf_role_tbl',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('rolf_role_tbl',6,'order','title');
INSERT INTO `table_properties` VALUES ('rolf_role_tbl',6,'rows','50');
INSERT INTO `table_properties` VALUES ('tbl_didactic_tpl_settings',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('tbl_didactic_tpl_settings',6,'order','title');
INSERT INTO `table_properties` VALUES ('user7',6,'direction','asc');
INSERT INTO `table_properties` VALUES ('user7',6,'order','login');
INSERT INTO `table_properties` VALUES ('user7',6,'selfields','a:24:{s:9:\"firstname\";b:1;s:8:\"lastname\";b:1;s:12:\"access_until\";b:1;s:10:\"last_login\";b:1;s:11:\"create_date\";b:0;s:12:\"approve_date\";b:0;s:10:\"agree_date\";b:0;s:5:\"email\";b:1;s:5:\"title\";b:0;s:8:\"birthday\";b:0;s:6:\"gender\";b:0;s:9:\"org_units\";b:0;s:11:\"institution\";b:0;s:10:\"department\";b:0;s:6:\"street\";b:0;s:7:\"zipcode\";b:0;s:4:\"city\";b:0;s:7:\"country\";b:0;s:11:\"sel_country\";b:0;s:12:\"phone_office\";b:0;s:10:\"phone_home\";b:0;s:12:\"phone_mobile\";b:0;s:3:\"fax\";b:0;s:13:\"matriculation\";b:0;}');

--
-- Table structure for table `table_templates`
--

CREATE TABLE `table_templates` (
  `name` varchar(64) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `context` varchar(128) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`name`,`user_id`,`context`)
) ;

--
-- Dumping data for table `table_templates`
--


--
-- Table structure for table `tax_data`
--

CREATE TABLE `tax_data` (
  `id` int(11) NOT NULL DEFAULT 0,
  `sorting_mode` int(11) NOT NULL DEFAULT 0,
  `item_sorting` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `tax_data`
--


--
-- Table structure for table `tax_node`
--

CREATE TABLE `tax_node` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  `type` char(4) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `tax_id` int(11) NOT NULL DEFAULT 0,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `tax_node`
--

INSERT INTO `tax_node` VALUES (1,'Dummy top node for all tax trees.','','2012-09-04 14:25:23','2012-09-04 14:25:23',0,0);

--
-- Table structure for table `tax_node_assignment`
--

CREATE TABLE `tax_node_assignment` (
  `node_id` int(11) NOT NULL DEFAULT 0,
  `component` varchar(10) NOT NULL DEFAULT '',
  `item_type` varchar(20) NOT NULL DEFAULT '',
  `item_id` int(11) NOT NULL DEFAULT 0,
  `tax_id` int(11) NOT NULL DEFAULT 0,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`node_id`,`component`,`obj_id`,`item_type`,`item_id`),
  KEY `i1_idx` (`component`,`item_type`,`item_id`)
) ;

--
-- Dumping data for table `tax_node_assignment`
--


--
-- Table structure for table `tax_node_seq`
--

CREATE TABLE `tax_node_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=2;

--
-- Dumping data for table `tax_node_seq`
--

INSERT INTO `tax_node_seq` VALUES (1);

--
-- Table structure for table `tax_tree`
--

CREATE TABLE `tax_tree` (
  `tax_tree_id` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tax_tree_id`,`child`),
  KEY `i1_idx` (`child`)
) ;

--
-- Dumping data for table `tax_tree`
--


--
-- Table structure for table `tax_usage`
--

CREATE TABLE `tax_usage` (
  `tax_id` int(11) NOT NULL DEFAULT 0,
  `obj_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tax_id`,`obj_id`)
) ;

--
-- Dumping data for table `tax_usage`
--


--
-- Table structure for table `tos_acceptance_track`
--

CREATE TABLE `tos_acceptance_track` (
  `tosv_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `ts` int(11) NOT NULL DEFAULT 0,
  `criteria` longtext DEFAULT NULL,
  PRIMARY KEY (`tosv_id`,`usr_id`,`ts`),
  KEY `i1_idx` (`usr_id`,`ts`)
) ;

--
-- Dumping data for table `tos_acceptance_track`
--


--
-- Table structure for table `tos_acceptance_track_seq`
--

CREATE TABLE `tos_acceptance_track_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tos_acceptance_track_seq`
--


--
-- Table structure for table `tos_criterion_to_doc`
--

CREATE TABLE `tos_criterion_to_doc` (
  `id` int(11) NOT NULL DEFAULT 0,
  `doc_id` int(11) NOT NULL DEFAULT 0,
  `criterion_id` varchar(50) NOT NULL,
  `criterion_value` varchar(255) DEFAULT NULL,
  `assigned_ts` int(11) NOT NULL DEFAULT 0,
  `modification_ts` int(11) NOT NULL DEFAULT 0,
  `owner_usr_id` int(11) NOT NULL DEFAULT 0,
  `last_modified_usr_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `tos_criterion_to_doc`
--


--
-- Table structure for table `tos_criterion_to_doc_seq`
--

CREATE TABLE `tos_criterion_to_doc_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tos_criterion_to_doc_seq`
--


--
-- Table structure for table `tos_documents`
--

CREATE TABLE `tos_documents` (
  `id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `creation_ts` int(11) NOT NULL DEFAULT 0,
  `modification_ts` int(11) NOT NULL DEFAULT 0,
  `sorting` int(11) NOT NULL DEFAULT 0,
  `owner_usr_id` int(11) NOT NULL DEFAULT 0,
  `last_modified_usr_id` int(11) NOT NULL DEFAULT 0,
  `text` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `tos_documents`
--


--
-- Table structure for table `tos_documents_seq`
--

CREATE TABLE `tos_documents_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tos_documents_seq`
--


--
-- Table structure for table `tos_versions`
--

CREATE TABLE `tos_versions` (
  `id` int(11) NOT NULL DEFAULT 0,
  `text` longtext DEFAULT NULL,
  `hash` varchar(32) DEFAULT NULL,
  `ts` int(11) NOT NULL DEFAULT 0,
  `doc_id` int(11) NOT NULL DEFAULT 0,
  `title` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`hash`,`doc_id`)
) ;

--
-- Dumping data for table `tos_versions`
--


--
-- Table structure for table `tos_versions_seq`
--

CREATE TABLE `tos_versions_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tos_versions_seq`
--


--
-- Table structure for table `tree`
--

CREATE TABLE `tree` (
  `tree` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) NOT NULL DEFAULT 0,
  `path` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`child`),
  KEY `i2_idx` (`parent`),
  KEY `i3_idx` (`tree`),
  KEY `i4_idx` (`path`(255))
) ;

--
-- Dumping data for table `tree`
--

INSERT INTO `tree` VALUES (1,1,0,1,344,1,'1');
INSERT INTO `tree` VALUES (1,7,9,5,6,3,'1.9.7');
INSERT INTO `tree` VALUES (1,8,9,7,8,3,'1.9.8');
INSERT INTO `tree` VALUES (1,9,1,2,343,2,'1.9');
INSERT INTO `tree` VALUES (1,10,9,9,10,3,'1.9.10');
INSERT INTO `tree` VALUES (1,11,9,11,12,3,'1.9.11');
INSERT INTO `tree` VALUES (1,12,9,3,4,3,'1.9.12');
INSERT INTO `tree` VALUES (1,14,9,13,18,3,'1.9.14');
INSERT INTO `tree` VALUES (1,15,9,19,20,3,'1.9.15');
INSERT INTO `tree` VALUES (1,16,9,21,22,3,'1.9.16');
INSERT INTO `tree` VALUES (1,17,9,23,24,3,'1.9.17');
INSERT INTO `tree` VALUES (1,18,9,25,26,3,'1.9.18');
INSERT INTO `tree` VALUES (1,20,9,29,30,3,'1.9.20');
INSERT INTO `tree` VALUES (1,21,9,31,32,3,'1.9.21');
INSERT INTO `tree` VALUES (1,22,9,33,34,3,'1.9.22');
INSERT INTO `tree` VALUES (1,23,9,35,36,3,'1.9.23');
INSERT INTO `tree` VALUES (1,26,9,37,38,3,'1.9.26');
INSERT INTO `tree` VALUES (1,27,9,39,40,3,'1.9.27');
INSERT INTO `tree` VALUES (1,28,9,41,42,3,'1.9.28');
INSERT INTO `tree` VALUES (1,29,9,43,44,3,'1.9.29');
INSERT INTO `tree` VALUES (1,30,9,45,46,3,'1.9.30');
INSERT INTO `tree` VALUES (1,31,9,47,48,3,'1.9.31');
INSERT INTO `tree` VALUES (1,32,9,49,50,3,'1.9.32');
INSERT INTO `tree` VALUES (1,33,9,51,52,3,'1.9.33');
INSERT INTO `tree` VALUES (1,34,9,53,54,3,'1.9.34');
INSERT INTO `tree` VALUES (1,35,9,55,56,3,'1.9.35');
INSERT INTO `tree` VALUES (1,36,9,57,58,3,'1.9.36');
INSERT INTO `tree` VALUES (1,37,9,59,60,3,'1.9.37');
INSERT INTO `tree` VALUES (1,38,9,61,62,3,'1.9.38');
INSERT INTO `tree` VALUES (1,39,9,63,64,3,'1.9.39');
INSERT INTO `tree` VALUES (1,40,9,65,66,3,'1.9.40');
INSERT INTO `tree` VALUES (1,41,9,67,68,3,'1.9.41');
INSERT INTO `tree` VALUES (1,42,9,69,70,3,'1.9.42');
INSERT INTO `tree` VALUES (1,43,9,71,72,3,'1.9.43');
INSERT INTO `tree` VALUES (1,44,9,73,278,3,'1.9.44');
INSERT INTO `tree` VALUES (1,45,9,279,280,3,'1.9.45');
INSERT INTO `tree` VALUES (1,46,44,74,177,4,'1.9.44.46');
INSERT INTO `tree` VALUES (1,47,46,75,76,5,'1.9.44.46.47');
INSERT INTO `tree` VALUES (1,48,9,281,282,3,'1.9.48');
INSERT INTO `tree` VALUES (1,49,9,283,284,3,'1.9.49');
INSERT INTO `tree` VALUES (1,50,9,285,286,3,'1.9.50');
INSERT INTO `tree` VALUES (1,51,9,287,288,3,'1.9.51');
INSERT INTO `tree` VALUES (1,52,9,289,290,3,'1.9.52');
INSERT INTO `tree` VALUES (1,53,9,291,292,3,'1.9.53');
INSERT INTO `tree` VALUES (1,54,9,293,294,3,'1.9.54');
INSERT INTO `tree` VALUES (1,55,9,295,296,3,'1.9.55');
INSERT INTO `tree` VALUES (1,56,9,297,298,3,'1.9.56');
INSERT INTO `tree` VALUES (1,57,9,299,300,3,'1.9.57');
INSERT INTO `tree` VALUES (1,58,9,301,302,3,'1.9.58');
INSERT INTO `tree` VALUES (1,59,9,303,304,3,'1.9.59');
INSERT INTO `tree` VALUES (1,60,9,305,306,3,'1.9.60');
INSERT INTO `tree` VALUES (1,61,9,307,308,3,'1.9.61');
INSERT INTO `tree` VALUES (1,62,9,309,310,3,'1.9.62');
INSERT INTO `tree` VALUES (1,63,9,311,312,3,'1.9.63');
INSERT INTO `tree` VALUES (1,64,9,313,314,3,'1.9.64');
INSERT INTO `tree` VALUES (1,65,9,315,316,3,'1.9.65');
INSERT INTO `tree` VALUES (1,66,9,317,318,3,'1.9.66');
INSERT INTO `tree` VALUES (1,67,9,0,0,3,'1.9.67');
INSERT INTO `tree` VALUES (1,68,9,0,0,3,'1.9.68');
INSERT INTO `tree` VALUES (1,69,9,0,0,3,'1.9.69');
INSERT INTO `tree` VALUES (1,70,9,0,0,3,'1.9.70');
INSERT INTO `tree` VALUES (1,71,9,0,0,3,'1.9.71');
INSERT INTO `tree` VALUES (1,72,9,0,0,3,'1.9.72');
INSERT INTO `tree` VALUES (1,73,9,0,0,3,'1.9.73');
INSERT INTO `tree` VALUES (1,74,9,0,0,3,'1.9.74');
INSERT INTO `tree` VALUES (1,75,9,0,0,3,'1.9.75');
INSERT INTO `tree` VALUES (1,76,9,0,0,3,'1.9.76');
INSERT INTO `tree` VALUES (1,77,9,0,0,3,'1.9.77');
INSERT INTO `tree` VALUES (1,78,9,0,0,3,'1.9.78');
INSERT INTO `tree` VALUES (1,79,9,0,0,3,'1.9.79');

--
-- Table structure for table `tree_workspace`
--

CREATE TABLE `tree_workspace` (
  `tree` int(11) NOT NULL DEFAULT 0,
  `child` int(11) NOT NULL DEFAULT 0,
  `parent` int(11) NOT NULL DEFAULT 0,
  `lft` int(11) NOT NULL DEFAULT 0,
  `rgt` int(11) NOT NULL DEFAULT 0,
  `depth` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`child`),
  KEY `i2_idx` (`parent`),
  KEY `i3_idx` (`tree`)
) ;

--
-- Dumping data for table `tree_workspace`
--


--
-- Table structure for table `tst_active`
--

CREATE TABLE `tst_active` (
  `active_id` int(11) NOT NULL DEFAULT 0,
  `user_fi` int(11) NOT NULL DEFAULT 0,
  `anonymous_id` varchar(5) DEFAULT NULL,
  `test_fi` int(11) NOT NULL DEFAULT 0,
  `tries` int(11) NOT NULL DEFAULT 0,
  `submitted` tinyint(4) NOT NULL DEFAULT 0,
  `submittimestamp` datetime DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `importname` varchar(400) DEFAULT NULL,
  `taxfilter` varchar(1024) DEFAULT NULL,
  `lastindex` int(11) NOT NULL DEFAULT 0,
  `last_finished_pass` int(11) DEFAULT NULL,
  `answerstatusfilter` varchar(16) DEFAULT NULL,
  `objective_container` int(11) DEFAULT NULL,
  `start_lock` varchar(128) DEFAULT NULL,
  `last_pmode` varchar(16) DEFAULT NULL,
  `last_started_pass` int(11) DEFAULT NULL,
  PRIMARY KEY (`active_id`),
  UNIQUE KEY `uc1_idx` (`user_fi`,`test_fi`,`anonymous_id`),
  KEY `i1_idx` (`user_fi`),
  KEY `i2_idx` (`test_fi`),
  KEY `i3_idx` (`anonymous_id`)
) ;

--
-- Dumping data for table `tst_active`
--


--
-- Table structure for table `tst_active_seq`
--

CREATE TABLE `tst_active_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_active_seq`
--


--
-- Table structure for table `tst_addtime`
--

CREATE TABLE `tst_addtime` (
  `active_fi` bigint(20) NOT NULL DEFAULT 0,
  `additionaltime` bigint(20) NOT NULL DEFAULT 0,
  `tstamp` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`active_fi`)
) ;

--
-- Dumping data for table `tst_addtime`
--


--
-- Table structure for table `tst_dyn_quest_set_cfg`
--

CREATE TABLE `tst_dyn_quest_set_cfg` (
  `test_fi` int(11) NOT NULL DEFAULT 0,
  `source_qpl_fi` int(11) NOT NULL DEFAULT 0,
  `tax_filter_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `order_tax` int(11) DEFAULT NULL,
  `source_qpl_title` varchar(255) DEFAULT NULL,
  `answer_filter_enabled` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`test_fi`)
) ;

--
-- Dumping data for table `tst_dyn_quest_set_cfg`
--


--
-- Table structure for table `tst_invited_user`
--

CREATE TABLE `tst_invited_user` (
  `test_fi` int(11) NOT NULL DEFAULT 0,
  `user_fi` int(11) NOT NULL DEFAULT 0,
  `clientip` varchar(255) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`test_fi`,`user_fi`)
) ;

--
-- Dumping data for table `tst_invited_user`
--


--
-- Table structure for table `tst_manual_fb`
--

CREATE TABLE `tst_manual_fb` (
  `manual_feedback_id` int(11) NOT NULL DEFAULT 0,
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `feedback` longtext DEFAULT NULL,
  `finalized_tstamp` bigint(20) DEFAULT NULL,
  `finalized_evaluation` tinyint(4) DEFAULT NULL,
  `finalized_by_usr_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`manual_feedback_id`),
  KEY `i1_idx` (`active_fi`),
  KEY `i2_idx` (`question_fi`),
  KEY `i3_idx` (`pass`)
) ;

--
-- Dumping data for table `tst_manual_fb`
--


--
-- Table structure for table `tst_manual_fb_seq`
--

CREATE TABLE `tst_manual_fb_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_manual_fb_seq`
--


--
-- Table structure for table `tst_mark`
--

CREATE TABLE `tst_mark` (
  `mark_id` int(11) NOT NULL DEFAULT 0,
  `test_fi` int(11) NOT NULL DEFAULT 0,
  `short_name` varchar(15) DEFAULT NULL,
  `official_name` varchar(50) DEFAULT NULL,
  `minimum_level` double NOT NULL DEFAULT 0,
  `passed` varchar(1) DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`mark_id`),
  KEY `i1_idx` (`test_fi`)
) ;

--
-- Dumping data for table `tst_mark`
--


--
-- Table structure for table `tst_mark_seq`
--

CREATE TABLE `tst_mark_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_mark_seq`
--


--
-- Table structure for table `tst_pass_result`
--

CREATE TABLE `tst_pass_result` (
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `points` double NOT NULL DEFAULT 0,
  `maxpoints` double NOT NULL DEFAULT 0,
  `questioncount` int(11) NOT NULL DEFAULT 0,
  `answeredquestions` int(11) NOT NULL DEFAULT 0,
  `workingtime` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `hint_count` int(11) DEFAULT 0,
  `hint_points` double DEFAULT 0,
  `obligations_answered` tinyint(4) NOT NULL DEFAULT 1,
  `exam_id` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`active_fi`,`pass`)
) ;

--
-- Dumping data for table `tst_pass_result`
--


--
-- Table structure for table `tst_qst_solved`
--

CREATE TABLE `tst_qst_solved` (
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `solved` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`active_fi`,`question_fi`)
) ;

--
-- Dumping data for table `tst_qst_solved`
--


--
-- Table structure for table `tst_result_cache`
--

CREATE TABLE `tst_result_cache` (
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `max_points` double NOT NULL DEFAULT 0,
  `reached_points` double NOT NULL DEFAULT 0,
  `mark_short` varchar(256) NOT NULL DEFAULT '',
  `mark_official` varchar(256) NOT NULL DEFAULT '',
  `passed` int(11) NOT NULL DEFAULT 0,
  `failed` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `hint_count` int(11) DEFAULT 0,
  `hint_points` double DEFAULT 0,
  `obligations_answered` tinyint(4) NOT NULL DEFAULT 1,
  `passed_once` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`active_fi`)
) ;

--
-- Dumping data for table `tst_result_cache`
--


--
-- Table structure for table `tst_rnd_cpy`
--

CREATE TABLE `tst_rnd_cpy` (
  `copy_id` int(11) NOT NULL DEFAULT 0,
  `tst_fi` int(11) NOT NULL DEFAULT 0,
  `qst_fi` int(11) NOT NULL DEFAULT 0,
  `qpl_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`copy_id`),
  KEY `i1_idx` (`qst_fi`),
  KEY `i2_idx` (`qpl_fi`),
  KEY `i3_idx` (`tst_fi`)
) ;

--
-- Dumping data for table `tst_rnd_cpy`
--


--
-- Table structure for table `tst_rnd_cpy_seq`
--

CREATE TABLE `tst_rnd_cpy_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_rnd_cpy_seq`
--


--
-- Table structure for table `tst_rnd_qpl_title`
--

CREATE TABLE `tst_rnd_qpl_title` (
  `title_id` int(11) NOT NULL DEFAULT 0,
  `qpl_fi` int(11) NOT NULL DEFAULT 0,
  `tst_fi` int(11) NOT NULL DEFAULT 0,
  `qpl_title` varchar(1000) NOT NULL DEFAULT '',
  PRIMARY KEY (`title_id`),
  KEY `i1_idx` (`qpl_fi`),
  KEY `i2_idx` (`tst_fi`)
) ;

--
-- Dumping data for table `tst_rnd_qpl_title`
--


--
-- Table structure for table `tst_rnd_qpl_title_seq`
--

CREATE TABLE `tst_rnd_qpl_title_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_rnd_qpl_title_seq`
--


--
-- Table structure for table `tst_rnd_quest_set_cfg`
--

CREATE TABLE `tst_rnd_quest_set_cfg` (
  `test_fi` int(11) NOT NULL DEFAULT 0,
  `req_pools_homo_scored` tinyint(4) NOT NULL DEFAULT 0,
  `quest_amount_cfg_mode` varchar(16) DEFAULT NULL,
  `quest_amount_per_test` int(11) DEFAULT NULL,
  `quest_sync_timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`test_fi`)
) ;

--
-- Dumping data for table `tst_rnd_quest_set_cfg`
--


--
-- Table structure for table `tst_rnd_quest_set_qpls`
--

CREATE TABLE `tst_rnd_quest_set_qpls` (
  `def_id` int(11) NOT NULL DEFAULT 0,
  `test_fi` int(11) NOT NULL DEFAULT 0,
  `pool_fi` int(11) NOT NULL DEFAULT 0,
  `pool_title` varchar(128) DEFAULT NULL,
  `pool_path` varchar(512) DEFAULT NULL,
  `pool_quest_count` int(11) DEFAULT NULL,
  `origin_tax_fi` int(11) DEFAULT NULL,
  `origin_node_fi` int(11) DEFAULT NULL,
  `mapped_tax_fi` int(11) DEFAULT NULL,
  `mapped_node_fi` int(11) DEFAULT NULL,
  `quest_amount` int(11) DEFAULT NULL,
  `sequence_pos` int(11) DEFAULT NULL,
  `origin_tax_filter` varchar(4000) DEFAULT NULL,
  `mapped_tax_filter` varchar(4000) DEFAULT NULL,
  `type_filter` varchar(250) DEFAULT NULL,
  `lifecycle_filter` varchar(250) DEFAULT NULL,
  `pool_ref_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`def_id`)
) ;

--
-- Dumping data for table `tst_rnd_quest_set_qpls`
--


--
-- Table structure for table `tst_rnd_quest_set_qpls_seq`
--

CREATE TABLE `tst_rnd_quest_set_qpls_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_rnd_quest_set_qpls_seq`
--


--
-- Table structure for table `tst_seq_qst_answstatus`
--

CREATE TABLE `tst_seq_qst_answstatus` (
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `correctness` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`active_fi`,`pass`,`question_fi`),
  KEY `i1_idx` (`active_fi`,`pass`),
  KEY `i2_idx` (`active_fi`,`question_fi`)
) ;

--
-- Dumping data for table `tst_seq_qst_answstatus`
--


--
-- Table structure for table `tst_seq_qst_checked`
--

CREATE TABLE `tst_seq_qst_checked` (
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`active_fi`,`pass`,`question_fi`)
) ;

--
-- Dumping data for table `tst_seq_qst_checked`
--


--
-- Table structure for table `tst_seq_qst_optional`
--

CREATE TABLE `tst_seq_qst_optional` (
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`active_fi`,`pass`,`question_fi`)
) ;

--
-- Dumping data for table `tst_seq_qst_optional`
--


--
-- Table structure for table `tst_seq_qst_postponed`
--

CREATE TABLE `tst_seq_qst_postponed` (
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `cnt` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`active_fi`,`pass`,`question_fi`),
  KEY `i1_idx` (`active_fi`,`pass`),
  KEY `i2_idx` (`active_fi`,`question_fi`)
) ;

--
-- Dumping data for table `tst_seq_qst_postponed`
--


--
-- Table structure for table `tst_seq_qst_presented`
--

CREATE TABLE `tst_seq_qst_presented` (
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`active_fi`,`pass`,`question_fi`)
) ;

--
-- Dumping data for table `tst_seq_qst_presented`
--


--
-- Table structure for table `tst_seq_qst_tracking`
--

CREATE TABLE `tst_seq_qst_tracking` (
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `status` varchar(16) DEFAULT NULL,
  `orderindex` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`active_fi`,`pass`,`question_fi`),
  KEY `i1_idx` (`active_fi`,`pass`),
  KEY `i2_idx` (`active_fi`,`question_fi`)
) ;

--
-- Dumping data for table `tst_seq_qst_tracking`
--


--
-- Table structure for table `tst_sequence`
--

CREATE TABLE `tst_sequence` (
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `sequence` longtext DEFAULT NULL,
  `postponed` varchar(4000) DEFAULT NULL,
  `hidden` varchar(4000) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `ans_opt_confirmed` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`active_fi`,`pass`)
) ;

--
-- Dumping data for table `tst_sequence`
--


--
-- Table structure for table `tst_skl_thresholds`
--

CREATE TABLE `tst_skl_thresholds` (
  `test_fi` int(11) NOT NULL DEFAULT 0,
  `skill_base_fi` int(11) NOT NULL DEFAULT 0,
  `skill_tref_fi` int(11) NOT NULL DEFAULT 0,
  `skill_level_fi` int(11) NOT NULL DEFAULT 0,
  `threshold` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`test_fi`,`skill_base_fi`,`skill_tref_fi`,`skill_level_fi`)
) ;

--
-- Dumping data for table `tst_skl_thresholds`
--


--
-- Table structure for table `tst_solutions`
--

CREATE TABLE `tst_solutions` (
  `solution_id` int(11) NOT NULL DEFAULT 0,
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `points` double DEFAULT NULL,
  `pass` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `value1` longtext DEFAULT NULL,
  `value2` longtext DEFAULT NULL,
  `step` int(11) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`solution_id`),
  KEY `i1_idx` (`question_fi`),
  KEY `i2_idx` (`active_fi`)
) ;

--
-- Dumping data for table `tst_solutions`
--


--
-- Table structure for table `tst_solutions_seq`
--

CREATE TABLE `tst_solutions_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_solutions_seq`
--


--
-- Table structure for table `tst_test_defaults`
--

CREATE TABLE `tst_test_defaults` (
  `test_defaults_id` int(11) NOT NULL DEFAULT 0,
  `user_fi` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `marks` longtext DEFAULT NULL,
  `defaults` longtext DEFAULT NULL,
  PRIMARY KEY (`test_defaults_id`),
  KEY `i1_idx` (`user_fi`)
) ;

--
-- Dumping data for table `tst_test_defaults`
--


--
-- Table structure for table `tst_test_defaults_seq`
--

CREATE TABLE `tst_test_defaults_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_test_defaults_seq`
--


--
-- Table structure for table `tst_test_question`
--

CREATE TABLE `tst_test_question` (
  `test_question_id` int(11) NOT NULL DEFAULT 0,
  `test_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `sequence` smallint(6) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `obligatory` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`test_question_id`),
  KEY `i1_idx` (`test_fi`),
  KEY `i2_idx` (`question_fi`)
) ;

--
-- Dumping data for table `tst_test_question`
--


--
-- Table structure for table `tst_test_question_seq`
--

CREATE TABLE `tst_test_question_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_test_question_seq`
--


--
-- Table structure for table `tst_test_result`
--

CREATE TABLE `tst_test_result` (
  `test_result_id` int(11) NOT NULL DEFAULT 0,
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `points` double NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `manual` tinyint(4) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `hint_count` int(11) DEFAULT 0,
  `hint_points` double DEFAULT 0,
  `answered` tinyint(4) NOT NULL DEFAULT 1,
  `step` int(11) DEFAULT NULL,
  PRIMARY KEY (`test_result_id`),
  KEY `i1_idx` (`active_fi`),
  KEY `i2_idx` (`question_fi`),
  KEY `i3_idx` (`pass`)
) ;

--
-- Dumping data for table `tst_test_result`
--


--
-- Table structure for table `tst_test_result_seq`
--

CREATE TABLE `tst_test_result_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_test_result_seq`
--


--
-- Table structure for table `tst_test_rnd_qst`
--

CREATE TABLE `tst_test_rnd_qst` (
  `test_random_question_id` int(11) NOT NULL DEFAULT 0,
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `question_fi` int(11) NOT NULL DEFAULT 0,
  `sequence` smallint(6) NOT NULL DEFAULT 0,
  `pass` int(11) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `src_pool_def_fi` int(11) DEFAULT NULL,
  PRIMARY KEY (`test_random_question_id`),
  KEY `i1_idx` (`question_fi`),
  KEY `i2_idx` (`active_fi`),
  KEY `i3_idx` (`pass`)
) ;

--
-- Dumping data for table `tst_test_rnd_qst`
--


--
-- Table structure for table `tst_test_rnd_qst_seq`
--

CREATE TABLE `tst_test_rnd_qst_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_test_rnd_qst_seq`
--


--
-- Table structure for table `tst_tests`
--

CREATE TABLE `tst_tests` (
  `test_id` int(11) NOT NULL DEFAULT 0,
  `obj_fi` int(11) NOT NULL DEFAULT 0,
  `author` varchar(50) DEFAULT NULL,
  `introduction` varchar(4000) DEFAULT NULL,
  `sequence_settings` tinyint(4) NOT NULL DEFAULT 0,
  `score_reporting` tinyint(4) NOT NULL DEFAULT 0,
  `instant_verification` varchar(1) DEFAULT '0',
  `answer_feedback` varchar(1) DEFAULT '0',
  `answer_feedback_points` varchar(1) DEFAULT '0',
  `fixed_participants` varchar(1) DEFAULT '0',
  `show_cancel` varchar(1) DEFAULT '1',
  `anonymity` varchar(1) DEFAULT '0',
  `nr_of_tries` smallint(6) NOT NULL DEFAULT 0,
  `use_previous_answers` varchar(1) DEFAULT '1',
  `title_output` varchar(1) DEFAULT '0',
  `processing_time` varchar(8) DEFAULT NULL,
  `enable_processing_time` varchar(1) DEFAULT '0',
  `reset_processing_time` tinyint(4) NOT NULL DEFAULT 0,
  `reporting_date` varchar(14) DEFAULT NULL,
  `shuffle_questions` varchar(1) DEFAULT '0',
  `ects_output` varchar(1) DEFAULT '0',
  `ects_fx` double DEFAULT NULL,
  `complete` varchar(1) DEFAULT '1',
  `ects_a` double NOT NULL DEFAULT 90,
  `ects_b` double NOT NULL DEFAULT 65,
  `ects_c` double NOT NULL DEFAULT 35,
  `ects_d` double NOT NULL DEFAULT 10,
  `ects_e` double NOT NULL DEFAULT 0,
  `keep_questions` tinyint(4) NOT NULL DEFAULT 0,
  `count_system` varchar(1) DEFAULT '0',
  `mc_scoring` varchar(1) DEFAULT '0',
  `score_cutting` varchar(1) DEFAULT '0',
  `pass_scoring` varchar(1) DEFAULT '0',
  `password` varchar(20) DEFAULT NULL,
  `allowedusers` int(11) DEFAULT NULL,
  `alloweduserstimegap` int(11) DEFAULT NULL,
  `results_presentation` int(11) NOT NULL DEFAULT 3,
  `show_summary` int(11) NOT NULL DEFAULT 0,
  `show_question_titles` varchar(1) DEFAULT '1',
  `certificate_visibility` varchar(1) DEFAULT '0',
  `show_marker` tinyint(4) NOT NULL DEFAULT 0,
  `kiosk` int(11) NOT NULL DEFAULT 0,
  `resultoutput` int(11) NOT NULL DEFAULT 0,
  `finalstatement` varchar(4000) DEFAULT NULL,
  `showfinalstatement` int(11) NOT NULL DEFAULT 0,
  `showinfo` int(11) NOT NULL DEFAULT 1,
  `forcejs` int(11) NOT NULL DEFAULT 0,
  `customstyle` varchar(128) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `created` int(11) NOT NULL DEFAULT 0,
  `mailnotification` tinyint(4) DEFAULT 0,
  `mailnottype` smallint(6) NOT NULL DEFAULT 0,
  `exportsettings` int(11) NOT NULL DEFAULT 0,
  `enabled_view_mode` varchar(20) DEFAULT '0',
  `template_id` int(11) DEFAULT NULL,
  `pool_usage` tinyint(4) DEFAULT NULL,
  `online_status` tinyint(4) NOT NULL DEFAULT 0,
  `print_bs_with_res` tinyint(4) NOT NULL DEFAULT 1,
  `offer_question_hints` tinyint(4) NOT NULL DEFAULT 0,
  `highscore_enabled` int(11) DEFAULT 0,
  `highscore_anon` int(11) DEFAULT 0,
  `highscore_achieved_ts` int(11) DEFAULT 0,
  `highscore_score` int(11) DEFAULT 0,
  `highscore_percentage` int(11) DEFAULT 0,
  `highscore_hints` int(11) DEFAULT 0,
  `highscore_wtime` int(11) DEFAULT 0,
  `highscore_own_table` int(11) DEFAULT 0,
  `highscore_top_table` int(11) DEFAULT 0,
  `highscore_top_num` int(11) DEFAULT 0,
  `specific_feedback` int(11) DEFAULT 0,
  `obligations_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `autosave` tinyint(4) NOT NULL DEFAULT 0,
  `autosave_ival` int(11) NOT NULL DEFAULT 0,
  `pass_deletion_allowed` int(11) NOT NULL DEFAULT 0,
  `redirection_mode` int(11) NOT NULL DEFAULT 0,
  `redirection_url` varchar(128) DEFAULT NULL,
  `examid_in_test_pass` int(11) NOT NULL DEFAULT 0,
  `examid_in_test_res` int(11) NOT NULL DEFAULT 0,
  `enable_examview` tinyint(4) DEFAULT NULL,
  `show_examview_html` tinyint(4) DEFAULT NULL,
  `show_examview_pdf` tinyint(4) DEFAULT NULL,
  `enable_archiving` tinyint(4) DEFAULT NULL,
  `question_set_type` varchar(32) NOT NULL DEFAULT 'FIXED_QUEST_SET',
  `sign_submission` int(11) NOT NULL DEFAULT 0,
  `char_selector_availability` int(11) NOT NULL DEFAULT 0,
  `char_selector_definition` varchar(4000) DEFAULT NULL,
  `skill_service` tinyint(4) DEFAULT NULL,
  `result_tax_filters` varchar(255) DEFAULT NULL,
  `show_grading_status` tinyint(4) DEFAULT 0,
  `show_grading_mark` tinyint(4) DEFAULT 0,
  `inst_fb_answer_fixation` tinyint(4) DEFAULT NULL,
  `intro_enabled` tinyint(4) DEFAULT NULL,
  `starting_time_enabled` tinyint(4) DEFAULT NULL,
  `ending_time_enabled` tinyint(4) DEFAULT NULL,
  `password_enabled` tinyint(4) DEFAULT NULL,
  `limit_users_enabled` tinyint(4) DEFAULT NULL,
  `broken` tinyint(4) DEFAULT NULL,
  `force_inst_fb` tinyint(4) DEFAULT 0,
  `starting_time` int(11) NOT NULL DEFAULT 0,
  `ending_time` int(11) NOT NULL DEFAULT 0,
  `pass_waiting` varchar(15) DEFAULT NULL,
  `follow_qst_answer_fixation` tinyint(4) DEFAULT 0,
  `block_after_passed` tinyint(4) DEFAULT 0,
  `info_screen` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`test_id`),
  KEY `i1_idx` (`obj_fi`)
) ;

--
-- Dumping data for table `tst_tests`
--


--
-- Table structure for table `tst_tests_seq`
--

CREATE TABLE `tst_tests_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_tests_seq`
--


--
-- Table structure for table `tst_times`
--

CREATE TABLE `tst_times` (
  `times_id` int(11) NOT NULL DEFAULT 0,
  `active_fi` int(11) NOT NULL DEFAULT 0,
  `started` datetime DEFAULT NULL,
  `finished` datetime DEFAULT NULL,
  `pass` smallint(6) NOT NULL DEFAULT 0,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`times_id`),
  KEY `i1_idx` (`active_fi`),
  KEY `i2_idx` (`pass`)
) ;

--
-- Dumping data for table `tst_times`
--


--
-- Table structure for table `tst_times_seq`
--

CREATE TABLE `tst_times_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `tst_times_seq`
--


--
-- Table structure for table `udf_clob`
--

CREATE TABLE `udf_clob` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`usr_id`,`field_id`)
) ;

--
-- Dumping data for table `udf_clob`
--


--
-- Table structure for table `udf_data`
--

CREATE TABLE `udf_data` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`)
) ;

--
-- Dumping data for table `udf_data`
--

INSERT INTO `udf_data` VALUES (6);
INSERT INTO `udf_data` VALUES (13);

--
-- Table structure for table `udf_definition`
--

CREATE TABLE `udf_definition` (
  `field_id` int(11) NOT NULL DEFAULT 0,
  `field_name` char(255) DEFAULT NULL,
  `field_type` tinyint(4) NOT NULL DEFAULT 0,
  `field_values` longtext DEFAULT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT 0,
  `changeable` tinyint(4) NOT NULL DEFAULT 0,
  `required` tinyint(4) NOT NULL DEFAULT 0,
  `searchable` tinyint(4) NOT NULL DEFAULT 0,
  `export` tinyint(4) NOT NULL DEFAULT 0,
  `course_export` tinyint(4) NOT NULL DEFAULT 0,
  `registration_visible` tinyint(4) DEFAULT 0,
  `visible_lua` tinyint(4) NOT NULL DEFAULT 0,
  `changeable_lua` tinyint(4) NOT NULL DEFAULT 0,
  `group_export` tinyint(4) DEFAULT 0,
  `certificate` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`field_id`)
) ;

--
-- Dumping data for table `udf_definition`
--


--
-- Table structure for table `udf_definition_seq`
--

CREATE TABLE `udf_definition_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `udf_definition_seq`
--


--
-- Table structure for table `udf_text`
--

CREATE TABLE `udf_text` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `value` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`usr_id`,`field_id`)
) ;

--
-- Dumping data for table `udf_text`
--


--
-- Table structure for table `user_action_activation`
--

CREATE TABLE `user_action_activation` (
  `context_comp` varchar(30) NOT NULL DEFAULT '',
  `context_id` varchar(30) NOT NULL DEFAULT '',
  `action_comp` varchar(30) NOT NULL DEFAULT '',
  `action_type` varchar(30) NOT NULL DEFAULT '',
  `active` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`context_comp`,`context_id`,`action_comp`,`action_type`)
) ;

--
-- Dumping data for table `user_action_activation`
--

INSERT INTO `user_action_activation` VALUES ('awrn','toplist','chtr','invite',1);
INSERT INTO `user_action_activation` VALUES ('awrn','toplist','chtr','invite_osd',1);
INSERT INTO `user_action_activation` VALUES ('awrn','toplist','contact','handle_req',1);
INSERT INTO `user_action_activation` VALUES ('awrn','toplist','mail','compose',1);
INSERT INTO `user_action_activation` VALUES ('awrn','toplist','pwsp','shared_res',1);
INSERT INTO `user_action_activation` VALUES ('awrn','toplist','user','profile',1);

--
-- Table structure for table `usr_account_codes`
--

CREATE TABLE `usr_account_codes` (
  `code_id` int(11) NOT NULL DEFAULT 0,
  `code` varchar(50) DEFAULT NULL,
  `valid_until` varchar(10) DEFAULT NULL,
  `generated` int(11) DEFAULT 0,
  `used` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`code_id`),
  KEY `i1_idx` (`code`)
) ;

--
-- Dumping data for table `usr_account_codes`
--


--
-- Table structure for table `usr_account_codes_seq`
--

CREATE TABLE `usr_account_codes_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `usr_account_codes_seq`
--


--
-- Table structure for table `usr_change_email_token`
--

CREATE TABLE `usr_change_email_token` (
  `token` varchar(32) DEFAULT NULL,
  `new_email` varchar(256) DEFAULT NULL,
  `valid_until` bigint(20) DEFAULT NULL
) ;

--
-- Dumping data for table `usr_change_email_token`
--


--
-- Table structure for table `usr_cron_mail_reminder`
--

CREATE TABLE `usr_cron_mail_reminder` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `ts` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`)
) ;

--
-- Dumping data for table `usr_cron_mail_reminder`
--


--
-- Table structure for table `usr_data`
--

CREATE TABLE `usr_data` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `login` varchar(190) DEFAULT NULL,
  `passwd` varchar(80) DEFAULT NULL,
  `firstname` varchar(32) DEFAULT NULL,
  `lastname` varchar(32) DEFAULT NULL,
  `title` varchar(32) DEFAULT NULL,
  `gender` char(1) DEFAULT 'm',
  `email` varchar(80) DEFAULT NULL,
  `institution` varchar(80) DEFAULT NULL,
  `street` varchar(40) DEFAULT NULL,
  `city` varchar(40) DEFAULT NULL,
  `zipcode` varchar(10) DEFAULT NULL,
  `country` varchar(40) DEFAULT NULL,
  `phone_office` varchar(40) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `hobby` varchar(4000) DEFAULT NULL,
  `department` varchar(80) DEFAULT NULL,
  `phone_home` varchar(40) DEFAULT NULL,
  `phone_mobile` varchar(40) DEFAULT NULL,
  `fax` varchar(40) DEFAULT NULL,
  `time_limit_owner` int(11) DEFAULT 0,
  `time_limit_unlimited` int(11) DEFAULT 0,
  `time_limit_from` int(11) DEFAULT 0,
  `time_limit_until` int(11) DEFAULT 0,
  `time_limit_message` int(11) DEFAULT 0,
  `referral_comment` varchar(250) DEFAULT NULL,
  `matriculation` varchar(40) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 0,
  `approve_date` datetime DEFAULT NULL,
  `agree_date` datetime DEFAULT NULL,
  `client_ip` varchar(255) DEFAULT NULL,
  `auth_mode` char(10) DEFAULT 'default',
  `profile_incomplete` int(11) DEFAULT 0,
  `ext_account` varchar(250) DEFAULT NULL,
  `feed_hash` varchar(32) DEFAULT NULL,
  `latitude` varchar(30) DEFAULT NULL,
  `longitude` varchar(30) DEFAULT NULL,
  `loc_zoom` int(11) NOT NULL DEFAULT 0,
  `login_attempts` tinyint(4) NOT NULL DEFAULT 0,
  `last_password_change` int(11) NOT NULL DEFAULT 0,
  `reg_hash` char(32) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `sel_country` varchar(2) DEFAULT NULL,
  `last_visited` longtext DEFAULT NULL,
  `inactivation_date` datetime DEFAULT NULL,
  `is_self_registered` tinyint(4) NOT NULL DEFAULT 0,
  `passwd_enc_type` varchar(10) DEFAULT NULL,
  `passwd_salt` varchar(32) DEFAULT NULL,
  `second_email` varchar(80) DEFAULT NULL,
  `first_login` datetime DEFAULT NULL,
  `last_profile_prompt` datetime DEFAULT NULL,
  `passwd_policy_reset` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`),
  UNIQUE KEY `uc1_idx` (`login`),
  KEY `i1_idx` (`login`,`passwd`),
  KEY `i2_idx` (`ext_account`,`auth_mode`)
) ;

--
-- Dumping data for table `usr_data`
--

INSERT INTO `usr_data` VALUES (6,'root','$2y$09$uhSHx5YHS6G1zv0gdTZfx.VNK482euQm2HmPd6cBhmOn3lgPd.NSC','root','user','','m','ilias@yourserver.com','','','','','','','2016-12-21 13:55:17','2016-12-14 14:44:49',NULL,'','','','','',7,1,1450795200,1450795200,0,'','',1,NULL,NULL,'','local',0,NULL,'',NULL,NULL,0,0,1481723089,'',NULL,'','',NULL,0,'bcryptphp',NULL,NULL,'2016-12-21 13:55:17',NULL,0);
INSERT INTO `usr_data` VALUES (13,'anonymous','294de3557d9d00b3d2d8a1e6aab028cf','anonymous','anonymous','','m','nomail','','','','','','','2003-08-15 11:03:36','2003-08-15 10:07:30','2003-08-15 10:07:30','','','','','',7,1,0,0,0,'','',1,NULL,NULL,'','local',0,'','','','',0,0,1217068076,'',NULL,'','',NULL,0,'md5','',NULL,'2003-08-15 11:03:36',NULL,0);

--
-- Table structure for table `usr_data_multi`
--

CREATE TABLE `usr_data_multi` (
  `id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `field_id` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `i1_idx` (`usr_id`)
) ;

--
-- Dumping data for table `usr_data_multi`
--


--
-- Table structure for table `usr_data_multi_seq`
--

CREATE TABLE `usr_data_multi_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `usr_data_multi_seq`
--


--
-- Table structure for table `usr_ext_profile_page`
--

CREATE TABLE `usr_ext_profile_page` (
  `id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `order_nr` int(11) NOT NULL DEFAULT 0,
  `title` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `usr_ext_profile_page`
--


--
-- Table structure for table `usr_ext_profile_page_seq`
--

CREATE TABLE `usr_ext_profile_page_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `usr_ext_profile_page_seq`
--


--
-- Table structure for table `usr_form_settings`
--

CREATE TABLE `usr_form_settings` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `id` varchar(50) NOT NULL DEFAULT '',
  `settings` varchar(4000) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`,`id`)
) ;

--
-- Dumping data for table `usr_form_settings`
--


--
-- Table structure for table `usr_portf_acl`
--

CREATE TABLE `usr_portf_acl` (
  `node_id` int(11) NOT NULL DEFAULT 0,
  `object_id` int(11) NOT NULL DEFAULT 0,
  `extended_data` varchar(200) DEFAULT NULL,
  `tstamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`node_id`,`object_id`)
) ;

--
-- Dumping data for table `usr_portf_acl`
--


--
-- Table structure for table `usr_portfolio`
--

CREATE TABLE `usr_portfolio` (
  `id` int(11) NOT NULL DEFAULT 0,
  `is_online` tinyint(4) DEFAULT NULL,
  `is_default` tinyint(4) DEFAULT NULL,
  `bg_color` char(6) DEFAULT NULL,
  `font_color` char(6) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `ppic` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `usr_portfolio`
--


--
-- Table structure for table `usr_portfolio_page`
--

CREATE TABLE `usr_portfolio_page` (
  `id` int(11) NOT NULL DEFAULT 0,
  `portfolio_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(250) NOT NULL DEFAULT '',
  `order_nr` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `usr_portfolio_page`
--


--
-- Table structure for table `usr_portfolio_page_seq`
--

CREATE TABLE `usr_portfolio_page_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `usr_portfolio_page_seq`
--


--
-- Table structure for table `usr_pref`
--

CREATE TABLE `usr_pref` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `keyword` char(40) NOT NULL DEFAULT '',
  `value` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`usr_id`,`keyword`)
) ;

--
-- Dumping data for table `usr_pref`
--

INSERT INTO `usr_pref` VALUES (6,'bs_allow_to_contact_me','y');
INSERT INTO `usr_pref` VALUES (6,'calendar_selection_type','1');
INSERT INTO `usr_pref` VALUES (6,'chat_osc_accept_msg','n');
INSERT INTO `usr_pref` VALUES (6,'date_format','1');
INSERT INTO `usr_pref` VALUES (6,'day_end','19');
INSERT INTO `usr_pref` VALUES (6,'day_start','8');
INSERT INTO `usr_pref` VALUES (6,'export_tz_type','1');
INSERT INTO `usr_pref` VALUES (6,'hide_own_online_status','n');
INSERT INTO `usr_pref` VALUES (6,'hits_per_page','50');
INSERT INTO `usr_pref` VALUES (6,'language','en');
INSERT INTO `usr_pref` VALUES (6,'public_city','n');
INSERT INTO `usr_pref` VALUES (6,'public_country','n');
INSERT INTO `usr_pref` VALUES (6,'public_email','n');
INSERT INTO `usr_pref` VALUES (6,'public_hobby','n');
INSERT INTO `usr_pref` VALUES (6,'public_institution','n');
INSERT INTO `usr_pref` VALUES (6,'public_phone','n');
INSERT INTO `usr_pref` VALUES (6,'public_profile','n');
INSERT INTO `usr_pref` VALUES (6,'public_street','n');
INSERT INTO `usr_pref` VALUES (6,'public_upload','n');
INSERT INTO `usr_pref` VALUES (6,'public_zip','n');
INSERT INTO `usr_pref` VALUES (6,'screen_reader_optimization','');
INSERT INTO `usr_pref` VALUES (6,'send_info_mails','n');
INSERT INTO `usr_pref` VALUES (6,'show_users_online','y');
INSERT INTO `usr_pref` VALUES (6,'skin','default');
INSERT INTO `usr_pref` VALUES (6,'store_last_visited','0');
INSERT INTO `usr_pref` VALUES (6,'style','delos');
INSERT INTO `usr_pref` VALUES (6,'time_format','1');
INSERT INTO `usr_pref` VALUES (6,'user_tz','Europe/Berlin');
INSERT INTO `usr_pref` VALUES (6,'weekstart','1');
INSERT INTO `usr_pref` VALUES (13,'show_users_online','y');

--
-- Table structure for table `usr_pwassist`
--

CREATE TABLE `usr_pwassist` (
  `pwassist_id` char(180) NOT NULL DEFAULT '',
  `expires` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`pwassist_id`),
  UNIQUE KEY `c1_idx` (`user_id`)
) ;

--
-- Dumping data for table `usr_pwassist`
--


--
-- Table structure for table `usr_search`
--

CREATE TABLE `usr_search` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `search_result` longtext DEFAULT NULL,
  `checked` longtext DEFAULT NULL,
  `failed` longtext DEFAULT NULL,
  `page` tinyint(4) NOT NULL DEFAULT 0,
  `search_type` tinyint(4) NOT NULL DEFAULT 0,
  `query` longtext DEFAULT NULL,
  `root` int(11) DEFAULT 1,
  `item_filter` varchar(1000) DEFAULT NULL,
  `mime_filter` varchar(1000) DEFAULT NULL,
  `creation_filter` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`usr_id`,`search_type`)
) ;

--
-- Dumping data for table `usr_search`
--


--
-- Table structure for table `usr_sess_istorage`
--

CREATE TABLE `usr_sess_istorage` (
  `session_id` varchar(256) NOT NULL DEFAULT '',
  `component_id` varchar(30) NOT NULL DEFAULT '',
  `vkey` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`session_id`,`component_id`,`vkey`)
) ;

--
-- Dumping data for table `usr_sess_istorage`
--


--
-- Table structure for table `usr_session`
--

CREATE TABLE `usr_session` (
  `session_id` varchar(256) NOT NULL DEFAULT ' ',
  `expires` int(11) NOT NULL DEFAULT 0,
  `data` longtext DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `last_remind_ts` int(11) NOT NULL DEFAULT 0,
  `type` int(11) DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  `remote_addr` varchar(50) DEFAULT NULL,
  `context` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `i1_idx` (`expires`),
  KEY `i2_idx` (`user_id`)
) ;

--
-- Dumping data for table `usr_session`
--


--
-- Table structure for table `usr_session_log`
--

CREATE TABLE `usr_session_log` (
  `tstamp` int(11) NOT NULL DEFAULT 0,
  `maxval` mediumint(9) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tstamp`,`maxval`,`user_id`)
) ;

--
-- Dumping data for table `usr_session_log`
--


--
-- Table structure for table `usr_session_stats`
--

CREATE TABLE `usr_session_stats` (
  `slot_begin` int(11) NOT NULL DEFAULT 0,
  `slot_end` int(11) NOT NULL DEFAULT 0,
  `active_min` int(11) DEFAULT NULL,
  `active_max` int(11) DEFAULT NULL,
  `active_avg` int(11) DEFAULT NULL,
  `active_end` int(11) DEFAULT NULL,
  `opened` int(11) DEFAULT NULL,
  `closed_manual` int(11) DEFAULT NULL,
  `closed_expire` int(11) DEFAULT NULL,
  `closed_idle` int(11) DEFAULT NULL,
  `closed_idle_first` int(11) DEFAULT NULL,
  `closed_limit` int(11) DEFAULT NULL,
  `closed_login` int(11) DEFAULT NULL,
  `max_sessions` int(11) DEFAULT NULL,
  `closed_misc` int(11) DEFAULT 0,
  PRIMARY KEY (`slot_begin`),
  KEY `i1_idx` (`slot_end`)
) ;

--
-- Dumping data for table `usr_session_stats`
--


--
-- Table structure for table `usr_session_stats_raw`
--

CREATE TABLE `usr_session_stats_raw` (
  `session_id` varchar(256) NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT 0,
  `start_time` int(11) NOT NULL DEFAULT 0,
  `end_time` int(11) DEFAULT NULL,
  `end_context` smallint(6) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`session_id`)
) ;

--
-- Dumping data for table `usr_session_stats_raw`
--


--
-- Table structure for table `usr_starting_point`
--

CREATE TABLE `usr_starting_point` (
  `id` int(11) NOT NULL DEFAULT 0,
  `position` int(11) DEFAULT 0,
  `starting_point` int(11) DEFAULT 0,
  `starting_object` int(11) DEFAULT 0,
  `rule_type` int(11) DEFAULT 0,
  `rule_options` varchar(4000) DEFAULT NULL,
  `calendar_view` int(11) NOT NULL DEFAULT 0,
  `calendar_period` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `usr_starting_point`
--


--
-- Table structure for table `usr_starting_point_seq`
--

CREATE TABLE `usr_starting_point_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `usr_starting_point_seq`
--


--
-- Table structure for table `ut_lp_coll_manual`
--

CREATE TABLE `ut_lp_coll_manual` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `subitem_id` int(11) NOT NULL DEFAULT 0,
  `completed` tinyint(4) NOT NULL DEFAULT 0,
  `last_change` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`usr_id`,`subitem_id`)
) ;

--
-- Dumping data for table `ut_lp_coll_manual`
--


--
-- Table structure for table `ut_lp_collections`
--

CREATE TABLE `ut_lp_collections` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `item_id` int(11) NOT NULL DEFAULT 0,
  `grouping_id` int(11) NOT NULL DEFAULT 0,
  `num_obligatory` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `lpmode` tinyint(4) DEFAULT 5,
  PRIMARY KEY (`obj_id`,`item_id`)
) ;

--
-- Dumping data for table `ut_lp_collections`
--


--
-- Table structure for table `ut_lp_defaults`
--

CREATE TABLE `ut_lp_defaults` (
  `type_id` varchar(10) NOT NULL DEFAULT '',
  `lp_mode` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`type_id`)
) ;

--
-- Dumping data for table `ut_lp_defaults`
--


--
-- Table structure for table `ut_lp_marks`
--

CREATE TABLE `ut_lp_marks` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `completed` int(11) NOT NULL DEFAULT 0,
  `mark` varchar(32) DEFAULT NULL,
  `u_comment` varchar(4000) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `status_changed` datetime DEFAULT NULL,
  `status_dirty` tinyint(4) NOT NULL DEFAULT 0,
  `percentage` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`obj_id`,`usr_id`)
) ;

--
-- Dumping data for table `ut_lp_marks`
--


--
-- Table structure for table `ut_lp_settings`
--

CREATE TABLE `ut_lp_settings` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `obj_type` char(4) DEFAULT NULL,
  `u_mode` tinyint(4) NOT NULL DEFAULT 0,
  `visits` int(11) DEFAULT 0,
  PRIMARY KEY (`obj_id`)
) ;

--
-- Dumping data for table `ut_lp_settings`
--


--
-- Table structure for table `ut_lp_user_status`
--

CREATE TABLE `ut_lp_user_status` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `additional_info` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`obj_id`,`usr_id`),
  KEY `i1_idx` (`obj_id`),
  KEY `i2_idx` (`usr_id`)
) ;

--
-- Dumping data for table `ut_lp_user_status`
--


--
-- Table structure for table `ut_online`
--

CREATE TABLE `ut_online` (
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `online_time` int(11) NOT NULL DEFAULT 0,
  `access_time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`usr_id`)
) ;

--
-- Dumping data for table `ut_online`
--

INSERT INTO `ut_online` VALUES (6,7860,1450799200);

--
-- Table structure for table `webdav_instructions`
--

CREATE TABLE `webdav_instructions` (
  `id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `uploaded_instructions` longtext DEFAULT NULL,
  `processed_instructions` longtext DEFAULT NULL,
  `lng` varchar(5) DEFAULT NULL,
  `creation_ts` datetime DEFAULT NULL,
  `modification_ts` datetime DEFAULT NULL,
  `owner_usr_id` int(11) DEFAULT NULL,
  `last_modification_usr_id` int(11) DEFAULT NULL,
  `sorting` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `webdav_instructions`
--


--
-- Table structure for table `webdav_instructions_seq`
--

CREATE TABLE `webdav_instructions_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `webdav_instructions_seq`
--


--
-- Table structure for table `webr_items`
--

CREATE TABLE `webr_items` (
  `link_id` int(11) NOT NULL DEFAULT 0,
  `webr_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(127) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `target` varchar(4000) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  `disable_check` tinyint(4) DEFAULT NULL,
  `create_date` int(11) NOT NULL DEFAULT 0,
  `last_update` int(11) NOT NULL DEFAULT 0,
  `last_check` int(11) DEFAULT NULL,
  `valid` tinyint(4) NOT NULL DEFAULT 0,
  `internal` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`link_id`),
  KEY `i1_idx` (`link_id`,`webr_id`),
  KEY `i3_idx` (`webr_id`)
) ;

--
-- Dumping data for table `webr_items`
--


--
-- Table structure for table `webr_items_seq`
--

CREATE TABLE `webr_items_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `webr_items_seq`
--


--
-- Table structure for table `webr_lists`
--

CREATE TABLE `webr_lists` (
  `webr_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(127) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `create_date` int(11) NOT NULL DEFAULT 0,
  `last_update` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`webr_id`)
) ;

--
-- Dumping data for table `webr_lists`
--


--
-- Table structure for table `webr_params`
--

CREATE TABLE `webr_params` (
  `param_id` int(11) NOT NULL DEFAULT 0,
  `webr_id` int(11) NOT NULL DEFAULT 0,
  `link_id` int(11) NOT NULL DEFAULT 0,
  `name` char(128) DEFAULT NULL,
  `value` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`param_id`),
  KEY `i1_idx` (`link_id`)
) ;

--
-- Dumping data for table `webr_params`
--


--
-- Table structure for table `webr_params_seq`
--

CREATE TABLE `webr_params_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `webr_params_seq`
--


--
-- Table structure for table `wfe_det_listening`
--

CREATE TABLE `wfe_det_listening` (
  `detector_id` int(11) NOT NULL DEFAULT 0,
  `workflow_id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(255) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `subject_type` varchar(30) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `context_type` varchar(30) DEFAULT NULL,
  `context_id` int(11) DEFAULT NULL,
  `listening_start` int(11) DEFAULT NULL,
  `listening_end` int(11) DEFAULT NULL,
  PRIMARY KEY (`detector_id`)
) ;

--
-- Dumping data for table `wfe_det_listening`
--


--
-- Table structure for table `wfe_det_listening_seq`
--

CREATE TABLE `wfe_det_listening_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `wfe_det_listening_seq`
--


--
-- Table structure for table `wfe_startup_events`
--

CREATE TABLE `wfe_startup_events` (
  `event_id` int(11) NOT NULL DEFAULT 0,
  `workflow_id` varchar(60) NOT NULL DEFAULT '',
  `type` varchar(255) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `subject_type` varchar(30) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `context_type` varchar(30) DEFAULT NULL,
  `context_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`event_id`)
) ;

--
-- Dumping data for table `wfe_startup_events`
--


--
-- Table structure for table `wfe_startup_events_seq`
--

CREATE TABLE `wfe_startup_events_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `wfe_startup_events_seq`
--


--
-- Table structure for table `wfe_static_inputs`
--

CREATE TABLE `wfe_static_inputs` (
  `input_id` int(11) NOT NULL DEFAULT 0,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`input_id`)
) ;

--
-- Dumping data for table `wfe_static_inputs`
--


--
-- Table structure for table `wfe_static_inputs_seq`
--

CREATE TABLE `wfe_static_inputs_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `wfe_static_inputs_seq`
--


--
-- Table structure for table `wfe_workflows`
--

CREATE TABLE `wfe_workflows` (
  `workflow_id` int(11) NOT NULL DEFAULT 0,
  `workflow_type` varchar(255) DEFAULT NULL,
  `workflow_content` varchar(255) DEFAULT NULL,
  `workflow_class` varchar(255) DEFAULT NULL,
  `workflow_location` varchar(255) DEFAULT NULL,
  `subject_type` varchar(30) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `context_type` varchar(30) DEFAULT NULL,
  `context_id` int(11) DEFAULT NULL,
  `workflow_instance` longtext DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`workflow_id`)
) ;

--
-- Dumping data for table `wfe_workflows`
--


--
-- Table structure for table `wfe_workflows_seq`
--

CREATE TABLE `wfe_workflows_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `wfe_workflows_seq`
--


--
-- Table structure for table `wfld_user_setting`
--

CREATE TABLE `wfld_user_setting` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `wfld_id` int(11) NOT NULL DEFAULT 0,
  `sortation` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`wfld_id`)
) ;

--
-- Dumping data for table `wfld_user_setting`
--


--
-- Table structure for table `wiki_page_template`
--

CREATE TABLE `wiki_page_template` (
  `wiki_id` int(11) NOT NULL DEFAULT 0,
  `wpage_id` int(11) NOT NULL DEFAULT 0,
  `new_pages` tinyint(4) NOT NULL DEFAULT 0,
  `add_to_page` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`wiki_id`,`wpage_id`)
) ;

--
-- Dumping data for table `wiki_page_template`
--


--
-- Table structure for table `wiki_stat`
--

CREATE TABLE `wiki_stat` (
  `wiki_id` int(11) NOT NULL DEFAULT 0,
  `ts` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `num_pages` int(11) NOT NULL DEFAULT 0,
  `del_pages` int(11) NOT NULL DEFAULT 0,
  `avg_rating` int(11) NOT NULL DEFAULT 0,
  `ts_day` char(10) DEFAULT NULL,
  `ts_hour` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`wiki_id`,`ts`)
) ;

--
-- Dumping data for table `wiki_stat`
--


--
-- Table structure for table `wiki_stat_page`
--

CREATE TABLE `wiki_stat_page` (
  `wiki_id` int(11) NOT NULL DEFAULT 0,
  `page_id` int(11) NOT NULL DEFAULT 0,
  `ts` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `int_links` int(11) NOT NULL DEFAULT 0,
  `ext_links` int(11) NOT NULL DEFAULT 0,
  `footnotes` int(11) NOT NULL DEFAULT 0,
  `num_ratings` int(11) NOT NULL DEFAULT 0,
  `num_words` int(11) NOT NULL DEFAULT 0,
  `num_chars` bigint(20) NOT NULL DEFAULT 0,
  `avg_rating` int(11) NOT NULL DEFAULT 0,
  `ts_day` char(10) DEFAULT NULL,
  `ts_hour` tinyint(4) DEFAULT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`wiki_id`,`page_id`,`ts`)
) ;

--
-- Dumping data for table `wiki_stat_page`
--


--
-- Table structure for table `wiki_stat_page_user`
--

CREATE TABLE `wiki_stat_page_user` (
  `wiki_id` int(11) NOT NULL DEFAULT 0,
  `page_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ts` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `changes` int(11) NOT NULL DEFAULT 0,
  `read_events` int(11) NOT NULL DEFAULT 0,
  `ts_day` char(10) DEFAULT NULL,
  `ts_hour` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`wiki_id`,`page_id`,`ts`,`user_id`)
) ;

--
-- Dumping data for table `wiki_stat_page_user`
--


--
-- Table structure for table `wiki_stat_user`
--

CREATE TABLE `wiki_stat_user` (
  `wiki_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ts` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `new_pages` int(11) NOT NULL DEFAULT 0,
  `ts_day` char(10) DEFAULT NULL,
  `ts_hour` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`wiki_id`,`user_id`,`ts`)
) ;

--
-- Dumping data for table `wiki_stat_user`
--


--
-- Table structure for table `wiki_user_html_export`
--

CREATE TABLE `wiki_user_html_export` (
  `wiki_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `progress` int(11) NOT NULL DEFAULT 0,
  `start_ts` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `with_comments` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`wiki_id`,`with_comments`)
) ;

--
-- Dumping data for table `wiki_user_html_export`
--


--
-- Table structure for table `write_event`
--

CREATE TABLE `write_event` (
  `obj_id` int(11) NOT NULL DEFAULT 0,
  `parent_obj_id` int(11) NOT NULL DEFAULT 0,
  `usr_id` int(11) NOT NULL DEFAULT 0,
  `action` varchar(8) NOT NULL DEFAULT ' ',
  `ts` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `write_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`write_id`),
  KEY `i1_idx` (`parent_obj_id`,`ts`),
  KEY `i2_idx` (`obj_id`)
) ;

--
-- Dumping data for table `write_event`
--

INSERT INTO `write_event` VALUES (7,9,-1,'create','2002-06-26 23:24:06',1);
INSERT INTO `write_event` VALUES (8,9,-1,'create','2002-06-26 23:24:06',2);
INSERT INTO `write_event` VALUES (9,1,-1,'create','2002-07-15 02:37:33',3);
INSERT INTO `write_event` VALUES (10,9,-1,'create','2002-07-15 02:36:56',4);
INSERT INTO `write_event` VALUES (11,9,-1,'create','2002-07-15 05:52:51',5);
INSERT INTO `write_event` VALUES (12,9,-1,'create','2003-08-15 00:07:28',6);
INSERT INTO `write_event` VALUES (98,9,-1,'create','2004-02-18 16:17:40',7);
INSERT INTO `write_event` VALUES (100,9,-1,'create','2004-03-09 13:13:16',8);
INSERT INTO `write_event` VALUES (107,9,-1,'create','2004-06-20 15:27:18',9);
INSERT INTO `write_event` VALUES (109,9,-1,'create','2004-07-10 15:03:12',10);
INSERT INTO `write_event` VALUES (86,9,-1,'create','2003-11-30 16:22:49',11);
INSERT INTO `write_event` VALUES (114,9,-1,'create','2004-09-01 23:49:45',12);
INSERT INTO `write_event` VALUES (116,9,-1,'create','2005-01-07 12:21:15',13);
INSERT INTO `write_event` VALUES (118,9,-1,'create','2005-03-02 03:59:01',14);
INSERT INTO `write_event` VALUES (124,9,-1,'create','2005-06-19 23:50:00',15);
INSERT INTO `write_event` VALUES (128,9,-1,'create','2005-07-20 08:10:04',16);
INSERT INTO `write_event` VALUES (136,9,-1,'create','2006-07-11 08:43:23',19);
INSERT INTO `write_event` VALUES (138,9,-1,'create','2007-02-26 12:58:49',20);
INSERT INTO `write_event` VALUES (140,9,-1,'create','2007-02-26 12:58:50',21);
INSERT INTO `write_event` VALUES (144,9,-1,'create','2007-04-03 03:43:47',22);
INSERT INTO `write_event` VALUES (147,9,-1,'create','2007-09-25 09:47:53',23);
INSERT INTO `write_event` VALUES (150,9,-1,'create','2008-06-02 06:08:54',24);
INSERT INTO `write_event` VALUES (152,9,-1,'create','2008-06-02 06:08:55',25);

--
-- Table structure for table `write_event_seq`
--

CREATE TABLE `write_event_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
)  AUTO_INCREMENT=26;

--
-- Dumping data for table `write_event_seq`
--

INSERT INTO `write_event_seq` VALUES (25);

--
-- Table structure for table `xhtml_page`
--

CREATE TABLE `xhtml_page` (
  `id` int(11) NOT NULL DEFAULT 0,
  `content` longtext DEFAULT NULL,
  `save_content` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;

--
-- Dumping data for table `xhtml_page`
--


--
-- Table structure for table `xhtml_page_seq`
--

CREATE TABLE `xhtml_page_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `xhtml_page_seq`
--


--
-- Table structure for table `xmlnestedset`
--

CREATE TABLE `xmlnestedset` (
  `ns_id` int(11) NOT NULL DEFAULT 0,
  `ns_book_fk` int(11) NOT NULL DEFAULT 0,
  `ns_type` varchar(50) NOT NULL DEFAULT '',
  `ns_tag_fk` int(11) NOT NULL DEFAULT 0,
  `ns_l` int(11) NOT NULL DEFAULT 0,
  `ns_r` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ns_id`),
  KEY `i1_idx` (`ns_tag_fk`),
  KEY `i2_idx` (`ns_l`),
  KEY `i3_idx` (`ns_r`),
  KEY `i4_idx` (`ns_book_fk`)
) ;

--
-- Dumping data for table `xmlnestedset`
--


--
-- Table structure for table `xmlnestedset_seq`
--

CREATE TABLE `xmlnestedset_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `xmlnestedset_seq`
--


--
-- Table structure for table `xmlnestedsettmp`
--

CREATE TABLE `xmlnestedsettmp` (
  `ns_id` int(11) NOT NULL DEFAULT 0,
  `ns_unique_id` varchar(32) NOT NULL DEFAULT '',
  `ns_book_fk` int(11) NOT NULL DEFAULT 0,
  `ns_type` varchar(50) NOT NULL DEFAULT '',
  `ns_tag_fk` int(11) NOT NULL DEFAULT 0,
  `ns_l` int(11) NOT NULL DEFAULT 0,
  `ns_r` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ns_id`),
  KEY `i1_idx` (`ns_tag_fk`),
  KEY `i2_idx` (`ns_l`),
  KEY `i3_idx` (`ns_r`),
  KEY `i4_idx` (`ns_book_fk`),
  KEY `i5_idx` (`ns_unique_id`)
) ;

--
-- Dumping data for table `xmlnestedsettmp`
--


--
-- Table structure for table `xmlnestedsettmp_seq`
--

CREATE TABLE `xmlnestedsettmp_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `xmlnestedsettmp_seq`
--


--
-- Table structure for table `xmlparam`
--

CREATE TABLE `xmlparam` (
  `tag_fk` int(11) NOT NULL DEFAULT 0,
  `param_name` char(50) NOT NULL DEFAULT '',
  `param_value` char(255) DEFAULT NULL,
  PRIMARY KEY (`tag_fk`,`param_name`)
) ;

--
-- Dumping data for table `xmlparam`
--


--
-- Table structure for table `xmltags`
--

CREATE TABLE `xmltags` (
  `tag_pk` int(11) NOT NULL DEFAULT 0,
  `tag_depth` int(11) NOT NULL DEFAULT 0,
  `tag_name` char(50) DEFAULT NULL,
  PRIMARY KEY (`tag_pk`)
) ;

--
-- Dumping data for table `xmltags`
--


--
-- Table structure for table `xmltags_seq`
--

CREATE TABLE `xmltags_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `xmltags_seq`
--


--
-- Table structure for table `xmlvalue`
--

CREATE TABLE `xmlvalue` (
  `tag_value_pk` int(11) NOT NULL DEFAULT 0,
  `tag_fk` int(11) NOT NULL DEFAULT 0,
  `tag_value` longtext DEFAULT NULL,
  PRIMARY KEY (`tag_value_pk`),
  KEY `i1_idx` (`tag_fk`)
) ;

--
-- Dumping data for table `xmlvalue`
--


--
-- Table structure for table `xmlvalue_seq`
--

CREATE TABLE `xmlvalue_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ;

--
-- Dumping data for table `xmlvalue_seq`
--



-- Dump completed on 2023-12-12 18:09:02
