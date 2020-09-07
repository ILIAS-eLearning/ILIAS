<#865>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#866>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#867>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#868>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#869>
<?php
// add show_questiontext for question blocks
if (!$ilDB->tableColumnExists("survey_questionblock", "show_questiontext")) {
    $query = "ALTER TABLE `survey_questionblock` ADD `show_questiontext` ENUM( '0', '1' ) DEFAULT '1' AFTER `title`";
    $res = $ilDB->query($query);
}
?>
<#870>
ALTER TABLE `survey_survey_question` CHANGE `heading` `heading` TEXT NULL DEFAULT NULL;

<#871>
CREATE TABLE IF NOT EXISTS `ldap_attribute_mapping` (
  `server_id` int(11) NOT NULL default '0',
  `keyword` varchar(32)   NOT NULL default '',
  `value` varchar(255)   NOT NULL default '',
  `perform_update` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`server_id`,`keyword`),
  KEY `server_id` (`server_id`)
) TYPE=MyISAM;


CREATE TABLE IF NOT EXISTS `ldap_role_group_mapping` (
  `mapping_id` int(11) NOT NULL auto_increment,
  `server_id` int(3) NOT NULL default '0',
  `dn` varchar(255)   NOT NULL default '',
  `member_attribute` varchar(64)   NOT NULL default '',
  `member_isdn` tinyint(1) NOT NULL default '0',
  `role` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mapping_id`)
) TYPE=MyISAM;


CREATE TABLE IF NOT EXISTS `ldap_server_settings` (
  `server_id` int(2) NOT NULL auto_increment,
  `active` int(1) NOT NULL default '0',
  `name` varchar(32)   NOT NULL default '',
  `url` varchar(255)   NOT NULL default '',
  `version` int(1) NOT NULL default '0',
  `base_dn` varchar(255)   NOT NULL default '',
  `referrals` int(1) NOT NULL default '0',
  `tls` int(1) NOT NULL default '0',
  `bind_type` int(1) NOT NULL default '0',
  `bind_user` varchar(255)   NOT NULL default '',
  `bind_pass` varchar(32)   NOT NULL default '',
  `search_base` varchar(255)   NOT NULL default '',
  `user_scope` tinyint(1) NOT NULL default '0',
  `user_attribute` varchar(255)   NOT NULL default '',
  `filter` varchar(255)   NOT NULL default '',
  `group_dn` varchar(255)   NOT NULL default '',
  `group_scope` tinyint(1) NOT NULL default '0',
  `group_filter` varchar(255)   NOT NULL default '',
  `group_member` varchar(255)   NOT NULL default '',
  `group_memberisdn` tinyint(1) NOT NULL default '0',
  `group_name` varchar(255)   NOT NULL default '',
  `group_attribute` varchar(64)   NOT NULL default '',
  `sync_on_login` tinyint(1) NOT NULL default '0',
  `sync_per_cron` tinyint(1) NOT NULL default '0',
  `role_sync_active` tinyint(1) NOT NULL default '0',
  `role_bind_dn` varchar(255)   NOT NULL default '',
  `role_bind_pass` varchar(32)   NOT NULL default '',
  PRIMARY KEY  (`server_id`)
) TYPE=MyISAM;

<#872>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#873>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#874>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#875>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#876>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#877>
<?php
// register new object type 'ps' for privacy security settings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'ps', 'Privacy security settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('ps', '__PrivacySecurity', 'Privacy and Security', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('" . $row->id . "')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id, SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'ps'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// new permission
$query = "INSERT INTO rbac_operations SET operation = 'export_member_data', " .
    "description = 'Export member data', " .
    "class = 'object'";

$res = $ilDB->query($query);
$new_ops_id = $ilDB->getLastInsertId();


// add rbac operations to assessment folder
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);

$query = "INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('" . $typ_id . "','" . $new_ops_id . "')";
$this->db->query($query);
?>
<#878>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#879>
<?php
// add layout attribute for matrix questions
$attribute_visibility = false;
$query = "SHOW COLUMNS FROM survey_question_matrix";
$res = $ilDB->query($query);
if ($res->numRows()) {
    while ($data = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
        if (strcmp($data["Field"], "layout") == 0) {
            $attribute_visibility = true;
        }
    }
}
if ($attribute_visibility == false) {
    $query = "ALTER TABLE `survey_question_matrix` ADD `layout` TEXT DEFAULT NULL AFTER `bipolar_adjective2`";
    $res = $ilDB->query($query);
}
?>
<#880>
ALTER TABLE `usr_data` ADD `im_icq` VARCHAR( 40 ) NULL ,
ADD `im_yahoo` VARCHAR( 40 ) NULL ,
ADD `im_msn` VARCHAR( 40 ) NULL ,
ADD `im_aim` VARCHAR( 40 ) NULL ,
ADD `im_skype` VARCHAR( 40 ) NULL ;

INSERT INTO `settings` ( `module` , `keyword` , `value` )
VALUES ('common', 'show_user_activity', '1');
INSERT INTO `settings` ( `module` , `keyword` , `value` )
VALUES ('common', 'user_activity_time', '5');
<#881>
DROP TABLE IF EXISTS il_news_item;
CREATE table `il_news_item`
(
	`id` int not null auto_increment primary key,
	`priority` enum('0','1','2') default 1,
	`title` varchar(200),
	`content` text,
	`context_obj_id` int,
	`context_obj_type` char(10),
	`context_sub_obj_id` int,
	`context_sub_obj_type` char(10),
	`content_type` enum('text','html') default 'text',
	`creation_date` datetime,
	`update_date` datetime,
	`user_id` int,
	`visibility` enum('users','public') default 'users',
	`content_long` text
);
<#882>
CREATE TABLE `il_block_setting` (
  `type` varchar(20) NOT NULL default '',
  `user` int  NOT NULL default '0',
  `block_id` int  NOT NULL default '0',
  `setting` varchar(40) NOT NULL default '',
  `value` varchar(200)   NOT NULL default '',
  PRIMARY KEY  (`type`,`user`,`block_id`,`setting`)
) TYPE=MyISAM;
<#883>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#884>
CREATE TABLE `il_news_subscription` (
  `user_id` int  NOT NULL default '0',
  `ref_id` int  NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`ref_id`)
) TYPE=MyISAM;
<#885>
ALTER TABLE usr_data ADD COLUMN feed_hash char(32);
<#886>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#887>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#888>
DROP TABLE IF EXISTS il_custom_block;
CREATE table `il_custom_block`
(
	`id` int not null auto_increment primary key,
	`context_obj_id` int,
	`context_obj_type` char(10),
	`context_sub_obj_id` int,
	`context_sub_obj_type` char(10),
	`type` varchar(20),
	`title` varchar(200)
);
<#889>
DROP TABLE IF EXISTS il_html_block;
CREATE table `il_html_block`
(
	`id` int not null primary key,
	`content` text
);
<#890>
DROP TABLE IF EXISTS il_external_feed_block;
CREATE table `il_external_feed_block`
(
	`id` int not null auto_increment primary key,
	`feed_url` varchar(250)
);
<#891>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#892>
DROP TABLE IF EXISTS `member_export_user_settings`;
CREATE TABLE `member_export_user_settings` (
  `user_id` int(11) NOT NULL default '0',
  `settings` text NOT NULL,
  PRIMARY KEY  (`user_id`)
) Type=MyISAM;

<#893>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#894>
DROP TABLE IF EXISTS `member_agreement`;
CREATE TABLE `member_agreement` (
  `usr_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `accepted` tinyint(1) NOT NULL default '0',
  `acceptance_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`obj_id`)
) Type=MyISAM;

<#895>
ALTER TABLE `usr_data` ADD `delicious` VARCHAR(40);

<#896>
CREATE TABLE `crs_defined_field_definitions` (
`field_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`obj_id` INT( 11 ) NOT NULL ,
`field_name` VARCHAR( 255 ) NOT NULL ,
`field_type` TINYINT( 1 ) NOT NULL ,
`field_values` TEXT NOT NULL ,
`field_required` TINYINT( 1 ) NOT NULL ,
PRIMARY KEY ( `field_id` )
) TYPE = MYISAM ;

<#897>
CREATE TABLE `crs_user_data` (
`usr_id` INT( 11 ) NOT NULL ,
`field_id` INT( 11 ) NOT NULL ,
`value` TEXT NOT NULL ,
PRIMARY KEY ( `usr_id` , `field_id` )
) TYPE = MYISAM ;

<#898>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#899>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#900>
CREATE TABLE `reg_access_limitation` (
  `role_id` int(11) unsigned NOT NULL,
  `limit_absolute` int(11) unsigned default NULL,
  `limit_relative_d` int(11) unsigned default NULL,
  `limit_relative_m` int(11) unsigned default NULL,
  `limit_relative_y` int(11) unsigned default NULL,
  `limit_mode` enum('absolute','relative','unlimited') NOT NULL,
  PRIMARY KEY  (`role_id`)
) ENGINE=MyISAM;
<#901>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#902>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#903>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#904>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#905>
ALTER TABLE `chat_invitations` ADD `guest_informed` tinyint(1) NOT NULL DEFAULT '0';

<#906>
ALTER TABLE `file_data` ADD `file_size` INT( 11 ) DEFAULT '0' NOT NULL AFTER `file_type` ;

<#907>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

ALTER TABLE `tmp_migration` ADD INDEX `obj_passed` ( `obj_id` ,`passed` );

<#908>
<?php
$wd = getcwd();

global $ilLog;

include_once('Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
include_once('Services/Migration/DBUpdate_904/classes/class.ilFSStorageFile.php');
include_once('Services/Migration/DBUpdate_904/classes/class.ilObjFileAccess.php');

// Fetch obj_ids of files
$query = "SELECT obj_id FROM object_data WHERE type = 'file' ORDER BY obj_id";
$res = $ilDB->query($query);
$file_ids = array();
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $file_ids[] = $row->obj_id;
}

foreach ($file_ids as $file_id) {
    // Check if done
    $query = "SELECT * FROM tmp_migration WHERE obj_id = " . $file_id . " AND passed = 1";
    $res = $ilDB->query($query);
    if ($res->numRows()) {
        continue;
    }

    if (!@file_exists(ilUpdateUtils::getDataDir() . '/files/file_' . $file_id) or !@is_dir(ilUpdateUtils::getDataDir() . '/files/file_' . $file_id)) {
        $ilLog->write('DB Migration 905: Failed: No data found for file_' . $file_id);
        continue;
    }

    // Rename
    $fss = new ilFSStorageFile($file_id);
    $fss->create();


    if ($fss->rename(ilUpdateUtils::getDataDir() . '/files/file_' . $file_id, $fss->getAbsolutePath())) {
        $ilLog->write('DB Migration 905: Success renaming file_' . $file_id);
    } else {
        $ilLog->write('DB Migration 905: Failed renaming ' . ilUpdateUtils::getDataDir() . '/files/file_' . $file_id . ' -> ' . $fss->getAbsolutePath());
        continue;
    }

    // Save success
    $query = "REPLACE INTO tmp_migration SET obj_id = '" . $file_id . "',passed = '1'";
    $ilDB->query($query);

    // Update file size
    $size = ilObjFileAccess::_lookupFileSize($file_id);
    $query = "UPDATE file_data SET file_size = '" . $size . "' " .
        "WHERE file_id = " . $file_id;
    $ilDB->query($query);
    $ilLog->write('DB Migration 905: File size is ' . $size . ' Bytes');
}

?>
<#909>
DROP TABLE IF EXISTS tmp_migration;

<#910>
DROP TABLE IF EXISTS ldap_role_group_mapping;
CREATE TABLE `ldap_role_group_mapping` (
  `mapping_id` int(11) NOT NULL auto_increment,
  `server_id` int(3) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `dn` varchar(255) NOT NULL default '',
  `member_attribute` varchar(64) NOT NULL default '',
  `member_isdn` tinyint(1) NOT NULL default '0',
  `role` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mapping_id`)
) Type=MyISAM;

<#911>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#912>
ALTER TABLE `user_defined_field_definition` ADD `export` TINYINT( 1 ) NOT NULL ,
ADD `course_export` TINYINT( 1 ) NOT NULL ;

<#913>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#914>
ALTER TABLE `il_news_item` MODIFY `priority` int default 1;

<#915>
ALTER TABLE `il_news_item` ADD COLUMN `content_is_lang_var` tinyint default 0;

<#916>
CREATE TABLE `copy_wizard_options` (
`copy_id` INT( 11 ) NOT NULL ,
`source_id` INT( 11 ) NOT NULL ,
`options` TEXT NOT NULL ,
PRIMARY KEY ( `copy_id` , `source_id` )
) TYPE = MYISAM ;
<#917>
ALTER TABLE  `tst_test_result` ADD INDEX (  `active_fi` );
<#918>
ALTER TABLE  `qpl_answer_cloze` ADD  `lowerlimit` DOUBLE NULL DEFAULT  '0' AFTER  `cloze_type` , ADD  `upperlimit` DOUBLE NULL DEFAULT  '0' AFTER  `lowerlimit` ;
<#919>
<?php
// register new object type 'newss' for news settings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'nwss', 'News settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('nwss', '__NewsSettings', 'News Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('" . $row->id . "')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id, SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'nwss'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);

?>
<#920>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#921>
CREATE TABLE  `tst_test_defaults` (
 `test_defaults_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `user_fi` INT NOT NULL ,
 `name` VARCHAR( 255 ) NOT NULL ,
 `defaults` TEXT NOT NULL ,
 `marks` TEXT NOT NULL ,
 `lastchange` TIMESTAMP NOT NULL ,
INDEX (  `user_fi` )
);
<#922>
DELETE FROM il_custom_block;
<#923>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#924>
DELETE FROM il_html_block;
DELETE FROM il_external_feed_block;

<#925>
<?php
// register new object type 'feed'
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'feed', 'External Feed', -1, now(), now())";
$this->db->query($query);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'feed'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations for feed object
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);

?>

<#926>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

<#927>
<?php
$wd = getcwd();

global $ilLog;

include_once('Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
include_once('Services/Migration/DBUpdate_904/classes/class.ilFSStorageEvent.php');

// Fetch event_ids of files
$query = "SELECT DISTINCT(event_id) as event_ids FROM event_file";
$res = $ilDB->query($query);
$event_ids = array();
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $event_ids[] = $row->event_ids;
}

foreach ($event_ids as $event_id) {
    // Check if done
    $query = "SELECT * FROM tmp_migration WHERE obj_id = " . $event_id . " AND passed = 1";
    $res = $ilDB->query($query);
    if ($res->numRows()) {
        continue;
    }

    if (!@file_exists(ilUpdateUtils::getDataDir() . '/events/event_' . $event_id)) {
        $ilLog->write('DB Migration 905: Failed: No data found for event id ' . $event_id);
        continue;
    }

    // Rename
    $fss = new ilFSStorageEvent($event_id);
    $fss->create();


    if ($fss->rename(ilUpdateUtils::getDataDir() . '/events/event_' . $event_id, $fss->getAbsolutePath())) {
        $ilLog->write('DB Migration 905: Success renaming event_' . $event_id);
    } else {
        $ilLog->write('DB Migration 905: Failed renaming ' . ilUpdateUtils::getDataDir() . '/events/event_' . $event_id . ' -> ' . $fss->getAbsolutePath());
        continue;
    }

    // Save success
    $query = "REPLACE INTO tmp_migration SET obj_id = '" . $event_id . "',passed = '1'";
    $ilDB->query($query);
}

?>
<#928>
DROP TABLE IF EXISTS tmp_migration;

<#929>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

<#930>
<?php
$wd = getcwd();

global $ilLog;

include_once('Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
include_once('Services/Migration/DBUpdate_904/classes/class.ilFSStorageCourse.php');

// Fetch archive ids
$query = "SELECT DISTINCT(archive_id) as archive_ids,archive_name,course_id FROM crs_archives";
$res = $ilDB->query($query);
$archive_ids = array();
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $archive_ids[$row->archive_ids]['id'] = $row->archive_ids;
    $archive_ids[$row->archive_ids]['name'] = $row->archive_name;
    $archive_ids[$row->archive_ids]['course_id'] = $row->course_id;
}

foreach ($archive_ids as $archive_id => $data) {
    // Check if done
    $query = "SELECT * FROM tmp_migration WHERE obj_id = " . $archive_id . " AND passed = 1";
    $res = $ilDB->query($query);
    if ($res->numRows()) {
        continue;
    }

    if (!@file_exists(ilUpdateUtils::getDataDir() . '/course/' . $data['name'])) {
        $ilLog->write('DB Migration 930: Failed: No data found for archive id ' . $data['name']);
        continue;
    }

    // Rename
    $fss = new ilFSStorageCourse($data['course_id']);
    $fss->create();
    $fss->initArchiveDirectory();


    if ($fss->rename(ilUpdateUtils::getDataDir() . '/course/' . $data['name'], $fss->getArchiveDirectory() . '/' . $data['name'])) {
        $ilLog->write('DB Migration 905: Success renaming archive ' . $data['name']);
    }
    if ($fss->rename(ilUpdateUtils::getDataDir() . '/course/' . $data['name'] . '.zip', $fss->getArchiveDirectory() . '/' . $data['name'] . '.zip')) {
        $ilLog->write('DB Migration 905: Success renaming archive ' . $data['name'] . '.zip');
    } else {
        $ilLog->write('DB Migration 905: Failed renaming ' . ilUpdateUtils::getDataDir() . '/course/' . $data['name'] . '-> ' .
             $fss->getArchiveDirectory() . '/' . $data['name']);
        continue;
    }

    // Save success
    $query = "REPLACE INTO tmp_migration SET obj_id = '" . $archive_id . "',passed = '1'";
    $ilDB->query($query);
}

?>
<#931>
DROP TABLE IF EXISTS tmp_migration;

<#932>
DROP TABLE IF EXISTS il_media_cast_item;
CREATE TABLE `il_media_cast_item` (
  `id` int(11) NOT NULL auto_increment,
  `mcst_id` int NOT NULL default '0',
  `mob_id` int NOT NULL default '0',
  `creation_date` datetime NOT NULL,
  `update_date` datetime NOT NULL,
  `update_user` int NOT NULL default '0',
  `length` varchar(8) NOT NULL,
  PRIMARY KEY  (`id`)
) Type=MyISAM;

<#933>
ALTER table usr_data ADD column latitude varchar(30) NOT NULL DEFAULT '';
ALTER table usr_data ADD column longitude varchar(30) NOT NULL DEFAULT '';
ALTER table usr_data ADD column loc_zoom int NOT NULL DEFAULT 0;

<#934>
ALTER TABLE `il_media_cast_item` ADD COLUMN `title` varchar(200);
ALTER TABLE `il_media_cast_item` ADD COLUMN `description` text;

<#935>
ALTER TABLE `il_media_cast_item` ADD COLUMN `visibility` enum('users','public') default 'users';

<#936>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#937>
<?php
// register new object type 'mcst' for media casts
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'mcst', 'Media Cast', -1, now(), now())";
$this->db->query($query);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'mcst'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations for feed object
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);

?>

<#938>
DROP TABLE IF EXISTS il_media_cast_data;
CREATE TABLE `il_media_cast_data` (
  `id` int(11) NOT NULL auto_increment,
  `offline` TINYINT DEFAULT 0,
  `public_files` TINYINT DEFAULT 0,
  PRIMARY KEY  (`id`)
) Type=MyISAM;

<#939>
DROP TABLE IF EXISTS il_media_cast_item;
ALTER TABLE `il_news_item` ADD COLUMN `mob_id` int;
ALTER TABLE `il_news_item` ADD COLUMN `playtime` varchar(8);

<#940>
ALTER TABLE `il_news_item` MODIFY `content_type` enum('text','html','audio') default 'text';

<#941>
ALTER table grp_data ADD column latitude varchar(30) NOT NULL DEFAULT '';
ALTER table grp_data ADD column longitude varchar(30) NOT NULL DEFAULT '';
ALTER table grp_data ADD column location_zoom int NOT NULL DEFAULT 0;
ALTER table grp_data ADD column enable_group_map TINYINT NOT NULL DEFAULT 0;

<#942>
ALTER table crs_settings ADD column latitude varchar(30) NOT NULL DEFAULT '';
ALTER table crs_settings ADD column longitude varchar(30) NOT NULL DEFAULT '';
ALTER table crs_settings ADD column location_zoom int NOT NULL DEFAULT 0;
ALTER table crs_settings ADD column enable_course_map TINYINT NOT NULL DEFAULT 0;

<#943>
<?php
$query = "SELECT * FROM ldap_server_settings ";
$res = $ilDB->query($query);
if (!$res->numRows()) {
    // Only update if no setting is available
    # Fetch old settings from settings_table
    $query = "SELECT * FROM settings WHERE keyword LIKE('ldap_%')";
    $res = $ilDB->query($query);
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $ldap_old[$row->keyword] = $row->value;
    }

    if ($ldap_old['ldap_server']) {
        $ldap_new['name'] = 'Default Server';
        $ldap_new['url'] = ('ldap://' . $ldap_old['ldap_server']);
        if ($ldap_old['ldap_port']) {
            $ldap_new['url'] .= (':' . $ldap_old['ldap_port']);
        }
        $ldap_new['active'] = $ldap_old['ldap_active'] ? 1 : 0;
        $ldap_new['tls'] = (int) $ldap_old['ldap_tls'];
        $ldap_new['version'] = $ldap_old['ldap_version'] ? $ldap_old['version'] : 3;
        $ldap_new['basedn'] = $ldap_old['ldap_basedn'];
        $ldap_new['referrals'] = (int) $ldap_old['ldap_referrals'];
        $ldap_new['bind_type'] = $ldap_old['ldap_bind_pw'] ? 1 : 0;
        $ldap_new['bind_user'] = $ldap_old['ldap_bind_dn'];
        $ldap_new['bind_pass'] = $ldap_old['ldap_bind_pw'];
        $ldap_new['search_base'] = $ldap_old['ldap_search_base'];
        $ldap_new['user_scope'] = 0;
        $ldap_new['user_attribute'] = $ldap_old['ldap_login_key'];
        $ldap_new['filter'] = ('(objectclass=' . $ldap_old['ldap_objectclass'] . ')');

        $query = "INSERT INTO  ldap_server_settings SET " .
            "active = '" . $ldap_new['active'] . "', " .
            "name = '" . $ldap_new['name'] . "', " .
            "url = " . $this->db->quote($ldap_new['url']) . ", " .
            "version = " . $this->db->quote($ldap_new['version']) . ", " .
            "base_dn = '" . $ldap_new['basedn'] . "', " .
            "referrals = '" . $ldap_new['referrals'] . "', " .
            "tls = '" . $ldap_new['tls'] . "', " .
            "bind_type = '" . $ldap_new['bind_type'] . "', " .
            "bind_user = '" . $ldap_new['bind_user'] . "', " .
            "bind_pass = '" . $ldap_new['bind_pass'] . "', " .
            "search_base = '" . $ldap_new['search_base'] . "', " .
            "user_scope = '" . $ldap_new['user_scope'] . "', " .
            "user_attribute = '" . $ldap_new['user_attribute'] . "', " .
            "filter = '" . $ldap_new['filter'] . "' ";
        "group_dn = '', " .
            "group_scope = '', " .
            "group_filter = '', " .
            "group_member = '', " .
            "group_memberisdn = '', " .
            "group_name = '', " .
            "group_attribute = '', " .
            "sync_on_login = '0', " .
            "sync_per_cron = '0', " .
            "role_sync_active = '0', " .
            "role_bind_dn = '', " .
            "role_bind_pass = '', ";

        $res = $ilDB->query($query);
    }
}
?>
<#944>
<?php
$wd = getcwd();

global $ilLog;

include_once('Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
include_once('Services/Migration/DBUpdate_904/classes/class.ilFSStorageCourse.php');

$ilLog->write('DB Migration 944: Starting migration of course info files');

if (@is_dir($dir = ilUpdateUtils::getDataDir() . '/course')) {
    $dp = @opendir($dir);
    while (($filedir = readdir($dp)) !== false) {
        if ($filedir == '.' or $filedir == '..') {
            continue;
        }
        if (preg_match('/^course_file([0-9]+)$/', $filedir, $matches)) {
            $ilLog->write('DB Migration 944: Found file: ' . $filedir . ' with course_id: ' . $matches[1]);

            $fss_course = new ilFSStorageCourse($matches[1]);
            $fss_course->initInfoDirectory();

            if (@is_dir($info_dir = ilUpdateUtils::getDataDir() . '/course/' . $filedir)) {
                $dp2 = @opendir($info_dir);
                while (($file = readdir($dp2)) !== false) {
                    if ($file == '.' or $file == '..') {
                        continue;
                    }
                    $fss_course->rename(
                        $from = ilUpdateUtils::getDataDir() . '/course/' . $filedir . '/' . $file,
                        $to = $fss_course->getInfoDirectory() . '/' . $file
                    );

                    $ilLog->write('DB Migration 944: Renamed: ' . $from . ' to: ' . $to);
                }
            }
        }
    }
}
?>
<#945>
<?php
// new permission copy
$query = "INSERT INTO rbac_operations SET operation = 'copy', " .
    "description = 'Copy Object', " .
    "class = 'general', " .
    "op_order = '115'";

$res = $ilDB->query($query);
?>
<#946>
<?php

// copy permission id
$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$ops_id = $row->ops_id;

$all_types = array('cat','chat','crs','dbk','exc','file','fold','frm','glo','grp','htlm','icrs','lm','mcst','sahs','svy','tst','webr');
foreach ($all_types as $type) {
    $query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = '" . $type . "'";
    $res = $ilDB->query($query);
    $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

    $query = "INSERT INTO rbac_ta SET typ_id = '" . $row->obj_id . "', ops_id = '" . $ops_id . "'";
    $ilDB->query($query);
}
?>
<#947>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `type` varchar(4),
  `passed` tinyint(4) NOT NULL default '0');

ALTER TABLE `tmp_migration` ADD INDEX `obj_parent_type` ( `obj_id` , `parent` , `type` );

<#948>
<?php
// Adjust rbac templates

// copy permission id
$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$ops_id = $row->ops_id;

$all_types = array('cat','chat','crs','dbk','exc','file','fold','frm','glo','grp','htlm','icrs','lm','mcst','sahs','svy','tst','webr');
$query = "SELECT * FROM rbac_templates " .
    "WHERE type IN ('" . implode("','", $all_types) . "') " .
    "AND ops_id = 4 " .
    "ORDER BY rol_id,parent";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    // CHECK done
    $query = "SELECT * FROM tmp_migration " .
        "WHERE obj_id = '" . $row->rol_id . "' " .
        "AND parent = '" . $row->parent . "' " .
        "AND type = '" . $row->type . "'";
    $res_done = $ilDB->query($query);
    if ($res_done->numRows()) {
        continue;
    }
    // INSERT new permission
    $query = "INSERT INTO rbac_templates SET " .
        "rol_id = '" . $row->rol_id . "', " .
        "type = '" . $row->type . "', " .
        "ops_id = '" . $ops_id . "', " .
        "parent = '" . $row->parent . "'";
    $ilDB->query($query);

    // Set Passed
    $query = "INSERT INTO tmp_migration SET " .
        "obj_id = '" . $row->rol_id . "', " .
        "parent = '" . $row->parent . "', " .
        "type = '" . $row->type . "', " .
        "passed = '1'";
    $ilDB->query($query);
}
?>
<#949>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `rol_id` int(11) NOT NULL default '0',
  `ref_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

ALTER TABLE `tmp_migration` ADD INDEX `rol_ref_passed` ( `rol_id` , `ref_id` , `passed` );

<#950>
<?php
// Adjust rbac_pa

// copy permission id
$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$ops_id = (int) $row->ops_id;

$all_types = array('cat','chat','crs','dbk','exc','file','fold','frm','glo','grp','htlm','icrs','lm','mcst','sahs','svy','tst','webr');

// Get all objects
$query = "SELECT rol_id,ops_id,pa.ref_id AS ref_id FROM rbac_pa AS pa " .
    "JOIN object_reference AS obr ON pa.ref_id = obr.ref_id " .
    "JOIN object_data AS obd ON obr.obj_id = obd.obj_id " .
    "WHERE obd.type IN ('" . implode("','", $all_types) . "') " .
    "ORDER BY rol_id,pa.ref_id ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    // CHECK done
    $query = "SELECT * FROM tmp_migration " .
        "WHERE rol_id = '" . $row->rol_id . "' " .
        "AND ref_id = '" . $row->ref_id . "' " .
        "AND passed = '1'";
    $res_done = $ilDB->query($query);
    if ($res_done->numRows()) {
        continue;
    }
    $ops_ids = unserialize(stripslashes($row->ops_id));
    // write granted ?
    if (!in_array(4, $ops_ids)) {
        continue;
    }
    // Grant permission
    $ops_ids[] = $ops_id;
    $query = "UPDATE rbac_pa SET " .
        "ops_id = '" . addslashes(serialize($ops_ids)) . "' " .
        "WHERE rol_id = '" . $row->rol_id . "' " .
        "AND ref_id = '" . $row->ref_id . "'";
    $ilDB->query($query);

    // Set Passed
    $query = "INSERT INTO tmp_migration SET " .
        "rol_id = '" . $row->rol_id . "', " .
        "ref_id = '" . $row->ref_id . "', " .
        "passed = '1'";
    $ilDB->query($query);
}
?>
<#951>
DROP TABLE IF EXISTS tmp_migration;
<#952>
<?php
// Mail enhancements part II
// Create b-tree index for fast access to object titles. This is needed for
// efficient generation and resolution of role mailbox addresses.
$query = "CREATE INDEX title_index ON object_data (title ASC);";
$this->db->query($query);
?>
<#953>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#954>
<?php
$query = "UPDATE usr_data SET ext_account = login WHERE auth_mode = 'radius'";
$ilDB->query($query);
?>
<#955>
<?php
// add create operation for
$query = "INSERT INTO rbac_operations " .
    "SET operation = 'create_feed', description = 'create external feed'";
$ilDB->query($query);

// get new ops_id
$query = "SELECT LAST_INSERT_ID()";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$ops_id = $row[0];

// add create feed for crs,cat,fold and grp
// get category type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='cat'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='crs'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='grp'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

?>
<#956>
UPDATE rbac_operations SET class='create' WHERE operation='create_feed';

<#957>
<?php
// add create operation for
$query = "INSERT INTO rbac_operations " .
    "SET operation = 'create_mcst', class='create', description = 'create media cast'";
$ilDB->query($query);

// get new ops_id
$query = "SELECT LAST_INSERT_ID()";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$ops_id = $row[0];

// add create feed for crs,cat,fold and grp
// get category type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='cat'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='crs'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='grp'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='fold'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

?>
<#958>
ALTER TABLE `il_media_cast_data` CHANGE `offline` `online` TINYINT DEFAULT 0;

<#959>
ALTER TABLE `frm_posts` ADD `pos_usr_alias` VARCHAR( 255 ) NOT NULL AFTER `pos_usr_id` ;
ALTER TABLE `frm_threads` ADD `thr_usr_alias` VARCHAR( 255 ) NOT NULL AFTER `thr_usr_id` ;

<#960>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#961>
ALTER TABLE  `qpl_question_cloze` ADD  `fixed_textlen` INT NULL ;

<#962>
DROP TABLE IF EXISTS il_news_read;
CREATE TABLE `il_news_read` (
  `user_id` int(11) NOT NULL default '0',
  `news_id` int(11) NOT NULL default '0',
  INDEX (`user_id`));

<#963>
ALTER TABLE `il_news_read` ADD INDEX (`news_id`);

<#964>
DELETE FROM il_news_read;
ALTER TABLE `il_news_read` ADD PRIMARY KEY (`user_id`,`news_id`);

<#965>
<?php
// register new object type 'pdts' for personal desktop settings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'pdts', 'Personal desktop settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('pdts', '__PersonalDesktopSettings', 'Personal Desktop Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('" . $row->id . "')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id, SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'pdts'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);

?>

<#966>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#967>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#968>
INSERT INTO `qpl_question_type` (`question_type_id`, `type_tag`) VALUES ('11', 'assFlashApp');
<#969>
CREATE TABLE IF NOT EXISTS `qpl_question_flashapp` (
  `question_fi` int(11) NOT NULL default '0',
  `flash_file` varchar(100) default NULL,
  `params` text,
  PRIMARY KEY  (`question_fi`)
);
<#970>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#971>
<?php

$found = false;
$query = 'SHOW COLUMNS FROM `ldap_server_settings`';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    if ($row->Field == 'group_optional') {
        $found = true;
        break;
    }
}
if (!$found) {
    $query = 'ALTER TABLE `ldap_server_settings` ADD `group_optional` TINYINT( 1 ) DEFAULT 0 ' .
        ' NOT NULL AFTER `group_attribute` , ADD `group_user_filter` VARCHAR( 255 ) NOT NULL AFTER `group_optional`';
    $res = $ilDB->query($query);
}
?>
<#972>
ALTER TABLE sahs_lm MODIFY type
ENUM('scorm','aicc','hacp','scorm2004');

<#973>
<?php

$found = false;
$query = 'SHOW COLUMNS FROM `ldap_role_group_mapping`';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    if ($row->Field == 'mapping_info') {
        $found = true;
        break;
    }
}
if (!$found) {
    $query = 'ALTER TABLE `ldap_role_group_mapping` ADD `mapping_info` TEXT DEFAULT NULL ';
    $res = $ilDB->query($query);
}
?>
<#974>
<?php
// add questioncount field in qpl_questionpool table
if (!$ilDB->tableColumnExists("qpl_questionpool", "questioncount")) {
    $query = "ALTER TABLE `qpl_questionpool` ADD `questioncount` INT NOT NULL DEFAULT 0 AFTER `online`";
    $res = $ilDB->query($query);
}
?>
<#975>
<?php
    $query = "SELECT * FROM qpl_questionpool";
    $result = $ilDB->query($query);
    while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
        if ($row["questioncount"] == 0) {
            $cquery = sprintf(
                "SELECT COUNT(question_id) AS question_count FROM qpl_questions WHERE obj_fi = %s AND ISNULL(original_id) AND complete = '1'",
                $ilDB->quote($row["obj_fi"] . "")
            );
            $cresult = $ilDB->query($cquery);
            $crow = $cresult->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
            $uquery = sprintf(
                "UPDATE qpl_questionpool SET questioncount = %s WHERE obj_fi = %s",
                $ilDB->quote($crow["question_count"]),
                $ilDB->quote($row["obj_fi"])
            );
            $uresult = $ilDB->query($uquery);
        }
    }
?>
<#976>
ALTER TABLE  `ass_log` ADD INDEX (  `obj_fi` );
<#977>
ALTER TABLE `qpl_question_matching` CHANGE `shuffle` `shuffle` ENUM(  '0',  '1',  '2',  '3' ) NOT NULL DEFAULT  '1';
<#978>
ALTER TABLE  `tst_tests` CHANGE  `show_summary`  `show_summary` INT NOT NULL DEFAULT  '0';
<#979>
ALTER TABLE  `qpl_numeric_range` CHANGE  `lowerlimit`  `lowerlimit` VARCHAR( 20 ) NOT NULL DEFAULT  '0';
<#980>
ALTER TABLE  `qpl_numeric_range` CHANGE  `upperlimit`  `upperlimit` VARCHAR( 20 ) NOT NULL DEFAULT  '0';
<#981>
ALTER TABLE  `qpl_answer_cloze` CHANGE  `lowerlimit`  `lowerlimit` VARCHAR( 20 ) NOT NULL DEFAULT  '0';
<#982>
ALTER TABLE  `qpl_answer_cloze` CHANGE  `upperlimit`  `upperlimit` VARCHAR( 20 ) NOT NULL DEFAULT  '0';
<#983>
ALTER TABLE `tst_tests` ADD `show_solution_feedback` INT NOT NULL DEFAULT '0' AFTER `show_solution_printview` ;
<#984>
ALTER TABLE `tst_solutions` CHANGE `value2` `value2` TEXT NULL DEFAULT NULL;
<#985>
CREATE TABLE `tst_manual_feedback` (
`manual_feedback_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`active_fi` INT NOT NULL DEFAULT '0',
`question_fi` INT NOT NULL DEFAULT '0',
`pass` INT NOT NULL DEFAULT '0',
`feedback` TEXT NULL ,
`lastchange` TIMESTAMP NOT NULL
);
ALTER TABLE `tst_manual_feedback` ADD INDEX ( `active_fi` );
ALTER TABLE `tst_manual_feedback` ADD INDEX ( `question_fi` );
ALTER TABLE `tst_manual_feedback` ADD INDEX ( `pass` );
<#986>
DELETE FROM rbac_operations WHERE ops_id = 46;
<#987>
ALTER TABLE `tst_tests` DROP `test_type_fi`;
<#988>
DROP TABLE `tst_test_type`;
<#989>
DROP TABLE `tst_eval_users`;
<#990>
ALTER TABLE  `tst_tests` ADD  `show_marker` TINYINT NOT NULL DEFAULT  '0';
<#991>
ALTER TABLE  `tst_tests` ADD  `keep_questions` TINYINT NOT NULL DEFAULT  '0' AFTER  `random_test`;
<#992>
REPLACE INTO settings (module,keyword,value) VALUES ('common','block_activated_pdusers','1');
<#993>
ALTER TABLE `usr_search` ADD `checked` TEXT NOT NULL AFTER `search_result` ,
ADD `failed` TEXT NOT NULL AFTER `checked` ,
ADD `page` TINYINT( 2 ) NOT NULL AFTER `failed` ;

<#994>
TRUNCATE TABLE `usr_search`;
<#995>
REPLACE INTO settings (keyword, value) VALUES ('custom_icon_tiny_width', 16);
REPLACE INTO settings (keyword, value) VALUES ('custom_icon_tiny_height', 16);

<#996>
DELETE FROM rbac_ta WHERE ops_id = 46;
<#997>
ALTER TABLE  `survey_answer` ADD  `active_fi` INT NOT NULL AFTER  `answer_id` ;
ALTER TABLE  `survey_answer` ADD INDEX (  `active_fi` ) ;
<#998>
DROP TABLE IF EXISTS tmp_svy_migration;
CREATE TABLE `tmp_svy_migration` (`answer_id` int(11) NOT NULL default '0');
<#999>
<?php
    // converting survey data, this could take some time and more than one try
    global $ilLog;

    $res = $ilDB->query("SELECT MAX(answer_id) as max_id FROM tmp_svy_migration ");
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $max_id = $row->max_id;
    }
    $max_id = $max_id ? $max_id : 0;
    $query = "SELECT * FROM survey_answer WHERE answer_id > $max_id ORDER BY answer_id";
    $result = $ilDB->query($query);
    if ($result->numRows()) {
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $active_id = 0;
            $activequery = "";
            if (strlen($row["anonymous_id"])) {
                $activequery = sprintf(
                    "SELECT * FROM survey_finished WHERE survey_fi = %s AND anonymous_id = %s",
                    $ilDB->quote($row["survey_fi"]),
                    $ilDB->quote($row["anonymous_id"])
                );
            } else {
                if ($row["user_fi"] > 0) {
                    $activequery = sprintf(
                        "SELECT * FROM survey_finished WHERE survey_fi = %s AND user_fi = %s",
                        $ilDB->quote($row["survey_fi"]),
                        $ilDB->quote($row["user_fi"])
                    );
                }
            }
            if (strlen($activequery)) {
                $activeresult = $ilDB->query($activequery);
                if ($activeresult->numRows() == 1) {
                    $activerow = $activeresult->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
                    $active_id = $activerow["finished_id"];
                }
            }
            if ($active_id == 0) {
                // found an answer dataset the could not be associated with a user in a survey
                $ilLog->write("DB Migration 999: Found unassociated dataset, deleting it: " . print_r($row, true));
            }
            $updatequery = sprintf(
                "UPDATE survey_answer SET active_fi = %s WHERE answer_id = %s",
                $ilDB->quote($active_id),
                $ilDB->quote($row["answer_id"])
            );
            $updateresult = $ilDB->query($updatequery);
            // set last position
            $updatetemp = sprintf(
                "INSERT INTO tmp_svy_migration (answer_id) VALUES (%s)",
                $ilDB->quote($row["answer_id"])
            );
            $updatetempresult = $ilDB->query($updatetemp);
        }
    }
?>
<#1000>
DROP TABLE `tmp_svy_migration`;

<#1001>
<?php
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'mcst'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add missing delete rbac operation for media casts
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','6')";
$this->db->query($query);
?>

<#1002>
<?php
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'feed'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add missing delete rbac operation for web feeds
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','6')";
$this->db->query($query);

?>
<#1003>
ALTER TABLE `survey_question_text` ADD `width` INT NOT NULL DEFAULT  '50';
ALTER TABLE `survey_question_text` ADD `height` INT NOT NULL DEFAULT  '5';

<#1004>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1005>
INSERT INTO `settings` (`keyword`,`value`) VALUES ('usr_settings_hide_hide_own_online_status', '1');
<#1006>
ALTER TABLE `tst_tests` ADD `results_presentation` INT NOT NULL DEFAULT '3' AFTER `show_solution_printview` ;
<#1007>
<?php
    // combine show_solution_details, show_solution_printview, show_solution_feedback to
    // results_presentation
    $query = "SELECT * FROM tst_tests";
    $result = $ilDB->query($query);
    if ($result->numRows()) {
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $results_presentation = 1;
            if ($row["show_solution_details"]) {
                $results_presentation = $results_presentation | 2;
            }
            if ($row["show_solution_printview"]) {
                $results_presentation = $results_presentation | 4;
            }
            if ($row["show_solution_feedback"]) {
                $results_presentation = $results_presentation | 8;
            }
            $update = sprintf(
                "UPDATE tst_tests SET results_presentation = %s WHERE test_id = %s",
                $ilDB->quote($results_presentation),
                $ilDB->quote($row["test_id"])
            );
            $updateresult = $ilDB->query($update);
        }
    }
?>
<#1008>
ALTER TABLE  `tst_tests` DROP  `show_solution_details`;
ALTER TABLE  `tst_tests` DROP  `show_solution_printview`;
ALTER TABLE  `tst_tests` DROP  `show_solution_feedback`;
<#1009>
<?php

$q = "SELECT up1.usr_id as usr_id FROM usr_pref AS up1, usr_pref AS up2 " .
    " WHERE up1.keyword= " . $ilDB->quote("style") . " AND up1.value= " . $ilDB->quote("blueshadow") .
    " AND up2.keyword= " . $ilDB->quote("skin") . " AND up2.value= " . $ilDB->quote("default") .
    " AND up1.usr_id = up2.usr_id ";

$usr_set = $ilDB->query($q);

while ($usr_rec = $usr_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
    $q = "UPDATE usr_pref SET value = " . $ilDB->quote("default") .
        " WHERE usr_id = " . $ilDB->quote($usr_rec["usr_id"]) .
        " AND keyword = " . $ilDB->quote("skin");
    $ilDB->query($q);

    $q = "UPDATE usr_pref SET value = " . $ilDB->quote("delos") .
        " WHERE usr_id = " . $ilDB->quote($usr_rec["usr_id"]) .
        " AND keyword = " . $ilDB->quote("style");
    $ilDB->query($q);
}

?>
<#1010>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1011>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1012>
ALTER TABLE  `qpl_questions` ADD INDEX (  `original_id` );

<#1013>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1014>
<?php

$found = false;
$query = 'SHOW COLUMNS FROM `ldap_role_group_mapping`';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    if ($row->Field == 'mapping_info_type') {
        $found = true;
        break;
    }
}
if (!$found) {
    $query = "ALTER TABLE `ldap_role_group_mapping` ADD `mapping_info_type` TINYINT( 1 ) DEFAULT '1' NOT NULL AFTER `mapping_info`";
    $res = $ilDB->query($query);
}
?>

<#1015>
ALTER TABLE `chat_invitations` ADD `invitation_time` INT( 11 ) NOT NULL AFTER `guest_informed` ;

<#1016>
<?php

$query = "SELECT * FROM object_data WHERE type = 'typ' AND title = 'recf'";
$res = $ilDB->query($query);

while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $recf_id = $row->obj_id;
}

$query = "INSERT INTO rbac_ta SET typ_id = '" . $recf_id . "', ops_id = 1";
$ilDB->query($query);
$query = "INSERT INTO rbac_ta SET typ_id = '" . $recf_id . "', ops_id = 2";
$ilDB->query($query);
$query = "INSERT INTO rbac_ta SET typ_id = '" . $recf_id . "', ops_id = 3";
$ilDB->query($query);
$query = "INSERT INTO rbac_ta SET typ_id = '" . $recf_id . "', ops_id = 4";
$ilDB->query($query);
?>

<#1017>
ALTER TABLE `crs_members` CHANGE `role` `notification` TINYINT( 2 ) NOT NULL DEFAULT '0';

<#1018>
ALTER TABLE `crs_members` CHANGE `status` `blocked` TINYINT( 2 ) NOT NULL DEFAULT '0';

<#1019>
<?php

$query = "UPDATE crs_members SET notification = '0'";
$ilDB->query($query);

$query = "UPDATE crs_members SET notification = '1' WHERE blocked = '1'";
$ilDB->query($query);

$query = "UPDATE crs_members SET blocked = '0' WHERE (blocked = '1' OR blocked = '2' OR blocked = '4')";
$ilDB->query($query);

$query = "UPDATE crs_members SET blocked = '1' WHERE blocked = '3'";
$ilDB->query($query);

?>
<#1020>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1021>
ALTER TABLE `mail` CHANGE `rcp_to` `rcp_to` TEXT NULL DEFAULT NULL ,
CHANGE `rcp_cc` `rcp_cc` TEXT NULL DEFAULT NULL ,
CHANGE `rcp_bcc` `rcp_bcc` TEXT NULL DEFAULT NULL;

<#1022>
<?php

$query = "SELECT * FROM crs_members WHERE blocked = 1";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $obj_id = $row->obj_id;
    $usr_id = $row->usr_id;

    $query = "SELECT obj_id FROM object_data WHERE description = 'Member of course obj_no." . $obj_id . "'";
    $res_role_id = $ilDB->query($query);
    while ($row_role_id = $res_role_id->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $role_id = $row_role_id->obj_id;

        $query = "REPLACE INTO rbac_ua " .
            "SET usr_id = '" . $usr_id . "', " .
            "rol_id = '" . $role_id . "' ";
        $ilDB->query($query);
    }
}
?>

<#1023>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

ALTER TABLE `tmp_migration` ADD INDEX `obj_passed` ( `obj_id` ,`passed` );

<#1024>
<?php
$wd = getcwd();

global $ilLog;

include_once('Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
include_once('Services/Migration/DBUpdate_904/classes/class.ilFSStorageFile.php');
include_once('Services/Migration/DBUpdate_904/classes/class.ilObjFileAccess.php');

// Fetch obj_ids of files
$query = "SELECT obj_id FROM object_data WHERE type = 'file' ORDER BY obj_id";
$res = $ilDB->query($query);
$file_ids = array();
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $file_ids[] = $row->obj_id;
}

foreach ($file_ids as $file_id) {
    // Check if done
    $query = "SELECT * FROM tmp_migration WHERE obj_id = " . $file_id . " AND passed = 1";
    $res = $ilDB->query($query);
    if ($res->numRows()) {
        continue;
    }

    if (!@file_exists(ilUpdateUtils::getDataDir() . '/files/file_' . $file_id) or !@is_dir(ilUpdateUtils::getDataDir() . '/files/file_' . $file_id)) {
        $ilLog->write('DB Migration 1024: Files already migrated. File: file_' . $file_id);
        continue;
    }

    // Rename
    $fss = new ilFSStorageFile($file_id);
    $fss->create();


    if ($fss->rename(ilUpdateUtils::getDataDir() . '/files/file_' . $file_id, $fss->getAbsolutePath())) {
        $ilLog->write('DB Migration 1024: Success renaming file_' . $file_id);
    } else {
        $ilLog->write('DB Migration 1024: Failed renaming ' . ilUpdateUtils::getDataDir() . '/files/file_' . $file_id . ' -> ' . $fss->getAbsolutePath());
        continue;
    }

    // Save success
    $query = "REPLACE INTO tmp_migration SET obj_id = '" . $file_id . "',passed = '1'";
    $ilDB->query($query);

    // Update file size
    $size = ilObjFileAccess::_lookupFileSize($file_id);
    $query = "UPDATE file_data SET file_size = '" . $size . "' " .
        "WHERE file_id = " . $file_id;
    $ilDB->query($query);
    $ilLog->write('DB Migration 905: File size is ' . $size . ' Bytes');
}

?>
<#1025>
DROP TABLE IF EXISTS tmp_migration;

<#1026>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

ALTER TABLE `tmp_migration` ADD INDEX `obj_passed` ( `obj_id` ,`passed` );


<#1027>
<?php
$wd = getcwd();

global $ilLog;

include_once('Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
include_once('Services/Migration/DBUpdate_904/classes/class.ilFSStorageEvent.php');

// Fetch event_ids of files
$query = "SELECT DISTINCT(event_id) as event_ids FROM event_file";
$res = $ilDB->query($query);
$event_ids = array();
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $event_ids[] = $row->event_ids;
}

foreach ($event_ids as $event_id) {
    // Check if done
    $query = "SELECT * FROM tmp_migration WHERE obj_id = " . $event_id . " AND passed = 1";
    $res = $ilDB->query($query);
    if ($res->numRows()) {
        continue;
    }

    if (!@file_exists(ilUpdateUtils::getDataDir() . '/events/event_' . $event_id)) {
        $ilLog->write('DB Migration 1028: Already migrated: Event data for event id ' . $event_id);
        continue;
    }

    // Rename
    $fss = new ilFSStorageEvent($event_id);
    $fss->create();


    if ($fss->rename(ilUpdateUtils::getDataDir() . '/events/event_' . $event_id, $fss->getAbsolutePath())) {
        $ilLog->write('DB Migration 1028: Success renaming event_' . $event_id);
    } else {
        $ilLog->write('DB Migration 1028: Failed renaming ' . ilUpdateUtils::getDataDir() . '/events/event_' . $event_id . ' -> ' . $fss->getAbsolutePath());
        continue;
    }

    // Save success
    $query = "REPLACE INTO tmp_migration SET obj_id = '" . $event_id . "',passed = '1'";
    $ilDB->query($query);
}

?>
<#1028>
DROP TABLE IF EXISTS tmp_migration;

<#1029>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

ALTER TABLE `tmp_migration` ADD INDEX `obj_passed` ( `obj_id` ,`passed` );

<#1030>
<?php
$wd = getcwd();

global $ilLog;

include_once('Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
include_once('Services/Migration/DBUpdate_904/classes/class.ilFSStorageCourse.php');

// Fetch archive ids
$query = "SELECT DISTINCT(archive_id) as archive_ids,archive_name,course_id FROM crs_archives";
$res = $ilDB->query($query);
$archive_ids = array();
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $archive_ids[$row->archive_ids]['id'] = $row->archive_ids;
    $archive_ids[$row->archive_ids]['name'] = $row->archive_name;
    $archive_ids[$row->archive_ids]['course_id'] = $row->course_id;
}

foreach ($archive_ids as $archive_id => $data) {
    // Check if done
    $query = "SELECT * FROM tmp_migration WHERE obj_id = " . $archive_id . " AND passed = 1";
    $res = $ilDB->query($query);
    if ($res->numRows()) {
        continue;
    }

    if (!@file_exists(ilUpdateUtils::getDataDir() . '/course/' . $data['name'])) {
        $ilLog->write('DB Migration 1030: Archives already migrated: No data found for archive id ' . $data['name']);
        continue;
    }

    // Rename
    $fss = new ilFSStorageCourse($data['course_id']);
    $fss->create();
    $fss->initArchiveDirectory();


    if ($fss->rename(ilUpdateUtils::getDataDir() . '/course/' . $data['name'], $fss->getArchiveDirectory() . '/' . $data['name'])) {
        $ilLog->write('DB Migration 1030: Success renaming archive ' . $data['name']);
    }
    if ($fss->rename(ilUpdateUtils::getDataDir() . '/course/' . $data['name'] . '.zip', $fss->getArchiveDirectory() . '/' . $data['name'] . '.zip')) {
        $ilLog->write('DB Migration 1030: Success renaming archive ' . $data['name'] . '.zip');
    } else {
        $ilLog->write('DB Migration 1030: Failed renaming ' . ilUpdateUtils::getDataDir() . '/course/' . $data['name'] . '-> ' .
             $fss->getArchiveDirectory() . '/' . $data['name']);
        continue;
    }

    // Save success
    $query = "REPLACE INTO tmp_migration SET obj_id = '" . $archive_id . "',passed = '1'";
    $ilDB->query($query);
}

?>
<#1031>
DROP TABLE IF EXISTS tmp_migration;

<#1032>
<?php
$wd = getcwd();


global $ilLog;

include_once('Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
include_once('Services/Migration/DBUpdate_904/classes/class.ilFSStorageCourse.php');

$ilLog->write('DB Migration 1032: Starting migration of course info files');

if (@is_dir($dir = ilUpdateUtils::getDataDir() . '/course')) {
    $dp = @opendir($dir);
    while (($filedir = readdir($dp)) !== false) {
        if ($filedir == '.' or $filedir == '..') {
            continue;
        }
        if (preg_match('/^course_file([0-9]+)$/', $filedir, $matches)) {
            $ilLog->write('DB Migration 1032: Found file: ' . $filedir . ' with course_id: ' . $matches[1]);

            $fss_course = new ilFSStorageCourse($matches[1]);
            $fss_course->initInfoDirectory();

            if (@is_dir($info_dir = ilUpdateUtils::getDataDir() . '/course/' . $filedir)) {
                $dp2 = @opendir($info_dir);
                while (($file = readdir($dp2)) !== false) {
                    if ($file == '.' or $file == '..') {
                        continue;
                    }
                    $fss_course->rename(
                        $from = ilUpdateUtils::getDataDir() . '/course/' . $filedir . '/' . $file,
                        $to = $fss_course->getInfoDirectory() . '/' . $file
                    );

                    $ilLog->write('DB Migration 1032: Renamed: ' . $from . ' to: ' . $to);
                }
            }
        }
    }
}
?>

<#1033>
CREATE TABLE cmi_comment(`cmi_comment_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `cmi_node_id` INTEGER, `comment` TEXT, `timestamp` VARCHAR(20), `location` VARCHAR(255), `sourceIsLMS` TINYINT );
CREATE INDEX cmi_comment_id ON cmi_comment(cmi_comment_id);
CREATE INDEX cmi_id ON cmi_comment(cmi_node_id);

CREATE TABLE cmi_correct_response(`cmi_correct_response_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `cmi_interaction_id` INTEGER, `pattern` VARCHAR(255) );
CREATE INDEX cmi_correct_response_id ON cmi_correct_response(cmi_correct_response_id);
CREATE INDEX cmi_correct_responsecmi_interaction_id ON cmi_correct_response(cmi_interaction_id);

CREATE TABLE cmi_interaction(`cmi_interaction_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `cmi_node_id` INTEGER, `description` TEXT, `id` VARCHAR(255), `latency` VARCHAR(20), `learner_response` TEXT, `result` TEXT, `timestamp` VARCHAR(20), `type` VARCHAR(32), `weighting` REAL );
CREATE INDEX cmi_interaction_id ON cmi_interaction(cmi_interaction_id);
CREATE INDEX id ON cmi_interaction(id);
CREATE INDEX type ON cmi_interaction(type);

CREATE TABLE cmi_node(`accesscount` INTEGER, `accessduration` VARCHAR(20), `accessed` VARCHAR(20), `activityAbsoluteDuration` VARCHAR(20), `activityAttemptCount` INTEGER, `activityExperiencedDuration` VARCHAR(20), `activityProgressStatus` TINYINT, `attemptAbsoluteDuration` VARCHAR(20), `attemptCompletionAmount` REAL, `attemptCompletionStatus` TINYINT, `attemptExperiencedDuration` VARCHAR(20), `attemptProgressStatus` TINYINT, `audio_captioning` INTEGER, `audio_level` REAL, `availableChildren` VARCHAR(255), `cmi_node_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `completion` REAL, `completion_status` VARCHAR(32), `completion_threshold` VARCHAR(32), `cp_node_id` INTEGER, `created` VARCHAR(20), `credit` VARCHAR(32), `delivery_speed` REAL, `exit` VARCHAR(255), `language` VARCHAR(5), `launch_data` TEXT, `learner_name` VARCHAR(255), `location` VARCHAR(255), `max` REAL, `min` REAL, `mode` VARCHAR(20), `modified` VARCHAR(20), `progress_measure` REAL, `raw` REAL, `scaled` REAL, `scaled_passing_score` REAL, `session_time` VARCHAR(20), `success_status` VARCHAR(255), `suspend_data` TEXT, `total_time` VARCHAR(20), `user_id` INTEGER );
CREATE INDEX cmi_itemcp_id ON cmi_node(cp_node_id);
CREATE INDEX completion_status ON cmi_node(completion_status);
CREATE INDEX credit ON cmi_node(credit);
CREATE INDEX id ON cmi_node(cmi_node_id);
CREATE INDEX user_id ON cmi_node(user_id);

CREATE TABLE cmi_objective(`cmi_interaction_id` INTEGER, `cmi_node_id` INTEGER, `cmi_objective_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `completion_status` REAL, `description` TEXT, `id` VARCHAR(255), `max` REAL, `min` REAL, `raw` REAL, `scaled` REAL, `progress_measure` REAL, `success_status` VARCHAR(32), `scope` VARCHAR(16) );
CREATE INDEX cmi_objective_id ON cmi_objective(cmi_objective_id);
CREATE INDEX cmi_objectivecmi_interaction_id ON cmi_objective(cmi_interaction_id);
CREATE INDEX id ON cmi_objective(id);
CREATE INDEX success_status ON cmi_objective(success_status);

CREATE TABLE cp_auxilaryResource(`auxiliaryResourceID` VARCHAR(255), `cp_node_id` INTEGER, `purpose` VARCHAR(255) );
ALTER TABLE cp_auxilaryResource ADD PRIMARY KEY(cp_node_id);

CREATE TABLE cp_condition(`condition` VARCHAR(50), `cp_node_id` INTEGER, `measureThreshold` VARCHAR(50), `operator` VARCHAR(50), `referencedObjective` VARCHAR(50) );
ALTER TABLE cp_condition ADD PRIMARY KEY(cp_node_id);

CREATE TABLE cp_dependency(`cp_node_id` INTEGER, `resourceId` VARCHAR(50) );
ALTER TABLE cp_dependency ADD PRIMARY KEY(cp_node_id);
CREATE INDEX cp_id ON cp_dependency(cp_node_id);
CREATE INDEX identifierref ON cp_dependency(resourceId);

CREATE TABLE cp_file(`cp_node_id` INTEGER, `href` VARCHAR(50) );
ALTER TABLE cp_file ADD PRIMARY KEY(cp_node_id);
CREATE INDEX cp_id ON cp_file(cp_node_id);

CREATE TABLE cp_hideLMSUI(`cp_node_id` INTEGER, `value` VARCHAR(50) );
ALTER TABLE cp_hideLMSUI ADD PRIMARY KEY(cp_node_id);
CREATE INDEX ss_sequencing_id ON cp_hideLMSUI(value);

CREATE TABLE cp_item(`completionThreshold` VARCHAR(50), `cp_node_id` INTEGER, `dataFromLMS` VARCHAR(255), `id` VARCHAR(200), `isvisible` VARCHAR(32), `parameters` VARCHAR(255), `resourceId` VARCHAR(200), `sequencingId` VARCHAR(50), `timeLimitAction` VARCHAR(30), `title` VARCHAR(255) );
ALTER TABLE cp_item ADD PRIMARY KEY(cp_node_id);
CREATE INDEX cp_itemidentifier ON cp_item(id);
CREATE INDEX ss_sequencing_id ON cp_item(sequencingId);

CREATE TABLE cp_manifest(`base` VARCHAR(200), `cp_node_id` INTEGER, `defaultOrganization` VARCHAR(50), `id` VARCHAR(200), `title` VARCHAR(255), `uri` VARCHAR(255), `version` VARCHAR(200) );
ALTER TABLE cp_manifest ADD PRIMARY KEY(cp_node_id);
CREATE INDEX identifier ON cp_manifest(id);

CREATE TABLE cp_mapinfo(`cp_node_id` INTEGER, `readNormalizedMeasure` TINYINT, `readSatisfiedStatus` TINYINT, `targetObjectiveID` VARCHAR(50), `writeNormalizedMeasure` TINYINT, `writeSatisfiedStatus` TINYINT );
ALTER TABLE cp_mapinfo ADD PRIMARY KEY(cp_node_id);
CREATE INDEX targetObjectiveId ON cp_mapinfo(targetObjectiveID);

CREATE TABLE cp_node(`cp_node_id` INTEGER PRIMARY KEY AUTO_INCREMENT, `nodeName` VARCHAR(50), `slm_id` INTEGER );
CREATE INDEX cp_id ON cp_node(cp_node_id);
CREATE INDEX nodeName ON cp_node(nodeName);

CREATE TABLE cp_objective(`cp_node_id` INTEGER, `minNormalizedMeasure` VARCHAR(50), `objectiveID` VARCHAR(200), `primary` TINYINT, `satisfiedByMeasure` TINYINT );
ALTER TABLE cp_objective ADD PRIMARY KEY(cp_node_id);

CREATE TABLE cp_organization(`cp_node_id` INTEGER, `id` VARCHAR(200), `objectivesGlobalToSystem` TINYINT, `sequencingId` VARCHAR(50), `structure` VARCHAR(200), `title` VARCHAR(255) );
ALTER TABLE cp_organization ADD PRIMARY KEY(cp_node_id);
CREATE INDEX cp_organizationid ON cp_organization(id);
CREATE INDEX ss_sequencing_id ON cp_organization(sequencingId);

CREATE TABLE cp_package(`created` VARCHAR(20), `identifier` VARCHAR(255), `jsdata` TEXT, `modified` VARCHAR(20), `obj_id` INTEGER, `persistPreviousAttempts` INTEGER, `settings` VARCHAR(255), `xmldata` TEXT );
ALTER TABLE cp_package ADD PRIMARY KEY(obj_id);
CREATE INDEX identifier ON cp_package(identifier);

CREATE TABLE cp_resource(`base` VARCHAR(200), `cp_node_id` INTEGER, `href` VARCHAR(250), `id` VARCHAR(200), `scormType` VARCHAR(32), `type` VARCHAR(30) );
ALTER TABLE cp_resource ADD PRIMARY KEY(cp_node_id);
CREATE INDEX import_id ON cp_resource(id);

CREATE TABLE cp_rule(`action` VARCHAR(50), `childActivitySet` VARCHAR(50), `conditionCombination` VARCHAR(50), `cp_node_id` INTEGER, `minimumCount` INTEGER, `minimumPercent` VARCHAR(50), `type` VARCHAR(50) );
ALTER TABLE cp_rule ADD PRIMARY KEY(cp_node_id);

CREATE TABLE cp_sequencing(`activityAbsoluteDurationLimit` VARCHAR(20), `activityExperiencedDurationLimit` VARCHAR(20), `attemptAbsoluteDurationLimit` VARCHAR(20), `attemptExperiencedDurationLimit` VARCHAR(20), `attemptLimit` INTEGER, `beginTimeLimit` VARCHAR(20), `choice` TINYINT, `choiceExit` TINYINT, `completionSetByContent` TINYINT, `constrainChoice` TINYINT, `cp_node_id` INTEGER, `endTimeLimit` VARCHAR(20), `flow` TINYINT, `forwardOnly` TINYINT, `id` VARCHAR(200), `measureSatisfactionIfActive` TINYINT, `objectiveMeasureWeight` REAL, `objectiveSetByContent` TINYINT, `preventActivation` TINYINT, `randomizationTiming` VARCHAR(50), `reorderChildren` TINYINT, `requiredForCompleted` VARCHAR(50), `requiredForIncomplete` VARCHAR(50), `requiredForNotSatisfied` VARCHAR(50), `requiredForSatisfied` VARCHAR(50), `rollupObjectiveSatisfied` TINYINT, `rollupProgressCompletion` TINYINT, `selectCount` INTEGER, `selectionTiming` VARCHAR(50), `sequencingId` VARCHAR(50), `tracked` TINYINT, `useCurrentAttemptObjectiveInfo` TINYINT, `useCurrentAttemptProgressInfo` TINYINT );
ALTER TABLE cp_sequencing ADD PRIMARY KEY(cp_node_id);
CREATE INDEX cp_sequencingid ON cp_sequencing(id);
CREATE TABLE cp_tree(`child` INTEGER, `depth` INTEGER, `lft` INTEGER, `obj_id` INTEGER, `parent` INTEGER, `rgt` INTEGER );
CREATE INDEX child ON cp_tree(child);
CREATE INDEX cp_treeobj_id ON cp_tree(obj_id);
CREATE INDEX parent ON cp_tree(parent);
<#1034>
CREATE TABLE `acc_cache` (
	`user_id` int NOT NULL default '0' PRIMARY KEY,
	`time` int NOT NULL default '0',
	`result` TEXT);
<#1035>
ALTER TABLE `acc_cache` MODIFY `result` MEDIUMTEXT;
<#1036>
REPLACE INTO settings (module,keyword,value) VALUES ('news','acc_cache_mins','10');
<#1037>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1038>
DROP TABLE IF EXISTS `adv_md_field_definition`;
CREATE TABLE `adv_md_field_definition` (
  `field_id` int(11) NOT NULL auto_increment,
  `record_id` int(11) NOT NULL default '0',
  `import_id` varchar(32) NOT NULL default '',
  `position` int(3) NOT NULL default '0',
  `field_type` tinyint(1) NOT NULL default '0',
  `field_values` text NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `searchable` tinyint(1) NOT NULL default '0',
  `required` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`field_id`)
) Type=MyISAM;

<#1039>
DROP TABLE IF EXISTS `adv_md_record`;
CREATE TABLE `adv_md_record` (
  `record_id` tinyint(3) NOT NULL auto_increment,
  `import_id` varchar(64) NOT NULL default '',
  `title` varchar(128) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`record_id`)
) Type=MyISAM;

<#1040>
DROP TABLE IF EXISTS `adv_md_record_objs`;
CREATE TABLE `adv_md_record_objs` (
  `record_id` tinyint(3) NOT NULL default '0',
  `obj_type` char(6) NOT NULL default '',
  PRIMARY KEY  (`record_id`,`obj_type`)
) Type=MyISAM;

<#1041>
DROP TABLE IF EXISTS `adv_md_substitutions`;
CREATE TABLE `adv_md_substitutions` (
  `obj_type` varchar(4) NOT NULL default '',
  `substitution` text NOT NULL,
  PRIMARY KEY  (`obj_type`)
) Type=MyISAM;

<#1042>
ALTER TABLE `object_data` CHANGE `title` `title` CHAR( 128 ) NOT NULL;

<#1043>
ALTER TABLE scorm_tracking DROP PRIMARY KEY;
ALTER TABLE scorm_tracking ADD PRIMARY KEY (user_id, sco_id, lvalue, obj_id);

<#1044>
ALTER TABLE `adv_md_substitutions` ADD `hide_description` TINYINT( 1 ) NOT NULL ;

<#1045>
CREATE TABLE `adv_md_values` (
`obj_id` INT( 11 ) NOT NULL ,
`field_id` INT( 11 ) NOT NULL ,
`value` TEXT NOT NULL ,
`disabled` TINYINT( 1 ) NOT NULL ,
PRIMARY KEY ( `obj_id` , `field_id` )
) TYPE = MYISAM ;

<#1046>
ALTER TABLE `adv_md_values` ADD INDEX `obj_id` ( `obj_id` );

<#1047>
CREATE TABLE `ldap_role_assignments` (
`server_id` INT( 11 ) NOT NULL ,
`rule_id` TINYINT( 3 ) NOT NULL ,
`type` TINYINT( 1 ) NOT NULL ,
`dn` TEXT NOT NULL ,
`attribute` CHAR( 32 ) NOT NULL ,
`isdn` TINYINT( 1 ) NOT NULL ,
`att_name` CHAR( 255 ) NOT NULL ,
`att_value` CHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `server_id` , `rule_id` )
) TYPE = MYISAM ;

<#1048>
ALTER TABLE `ldap_role_assignments` ADD `role_id` INT( 11 ) NOT NULL;

<#1049>
ALTER TABLE `ldap_role_assignments` CHANGE `rule_id` `rule_id` TINYINT( 3 ) NOT NULL AUTO_INCREMENT;

<#1050>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1051>
ALTER TABLE `cp_package` ADD `activitytree` TEXT;

<#1052>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1053>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1054>
<?php
// insert link definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
         "VALUES ('typ', 'rcrs', 'Remote Course Object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];


// add operation assignment to link object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete,
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','6')";
$this->db->query($query);

// add create operation
$query = "INSERT INTO rbac_operations " .
    "SET operation = 'create_rcrs', description = 'create remote course'";
$this->db->query($query);

// get new ops_id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$ops_id = $row[0];

// add create for cat
// get category type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='cat'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$this->db->query($query);

?>
<#1055>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1056>
DROP TABLE IF EXISTS `adv_md_record`;

<#1057>
ALTER TABLE scorm_tracking DROP PRIMARY KEY;
ALTER TABLE scorm_tracking ADD PRIMARY KEY (user_id, sco_id, lvalue, obj_id);

<#1058>
CREATE TABLE IF NOT EXISTS `adv_md_record` (
  `record_id` tinyint(3) NOT NULL auto_increment,
  `import_id` varchar(64) NOT NULL default '',
  `active` tinyint(1) NOT NULL default '0',
  `title` varchar(128) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`record_id`)
) Type=MyISAM AUTO_INCREMENT=1 ;

<#1059>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1060>
ALTER TABLE `conditions` ADD `ref_handling` TINYINT( 1 ) DEFAULT '1' NOT NULL ;

<#1061>
<?php
// Shared preconditions for all target objects of type 'st'
$query = "UPDATE conditions SET ref_handling = 0 " .
    "WHERE target_type = 'st' OR trigger_type = 'crsg' ";
$ilDB->query($query);
?>
<#1062>
<?php
// Insert lm reference id for all preconditions (target type 'st')
$query = "SELECT id,target_obj_id FROM conditions " .
    "WHERE target_type = 'st'";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "SELECT ref_id FROM object_reference AS obr " .
        "JOIN lm_data AS lm ON obr.obj_id = lm_id " .
        "WHERE lm.obj_id = " . $row->target_obj_id . " ";
    $res_ref = $ilDB->query($query);
    $row_ref = $res_ref->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

    if ($row_ref->ref_id) {
        $query = "UPDATE conditions SET " .
            "target_ref_id = " . $row_ref->ref_id . ' ' .
            "WHERE id = " . $row->id . " ";
        $ilDB->query($query);
    }
}
?>
<#1063>
<?php
global $ilLog;

// Delete all conditions if course is not parent
$query = "SELECT DISTINCT target_ref_id AS ref FROM conditions " .
    "WHERE target_type != 'crs' AND target_type != 'st' ";
$res = $ilDB->query($query);
$ref_ids = array();
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $ref_ids[] = $row->ref;
}

$tree = new ilTree(ROOT_FOLDER_ID);
foreach ($ref_ids as $ref_id) {
    if (!$tree->checkForParentType($ref_id, 'crs')) {
        $query = "DELETE FROM conditions " .
            "WHERE target_ref_id = " . $ref_id . " " .
            "AND target_type != 'st' ";
        $ilDB->query($query);
        $ilLog->write('Delete condition for ref_id = ' . $ref_id . ' (not inside of course)');
    }
}
?>

<#1064>
<?php
// register new object type 'mds' for meta data setttings and advanced meta data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'mds', 'Meta Data settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('mds', '__MetaDataSettings', 'Meta Data Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('" . $row->id . "')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id, SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'mds'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);
?>
<#1065>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1066>
ALTER TABLE `cp_package` MODIFY `jsdata` MEDIUMTEXT;

<#1067>
ALTER TABLE `cp_package` MODIFY `activitytree` MEDIUMTEXT;

<#1068>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1069>
CREATE TABLE IF NOT EXISTS `il_log` (
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `level` int NOT NULL DEFAULT 0,
  `message` varchar(250) NOT NULL default '',
  `module` varchar(50) NOT NULL default '',
  INDEX `created`(`created`),
  INDEX `module`(`module`),
  INDEX `level`(`level`)
) Type=MyISAM;

<#1070>
CREATE TABLE IF NOT EXISTS `addressbook_mailing_lists` (
  `ml_id` bigint(20) NOT NULL auto_increment,
  `user_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `createdate` datetime NOT NULL,
  `changedate` datetime NOT NULL,
  PRIMARY KEY  (`ml_id`),
  KEY `user_id` (`user_id`)
) Type=MyISAM;

<#1071>
CREATE TABLE IF NOT EXISTS `addressbook_mailing_lists_assignments` (
  `a_id` bigint(20) NOT NULL auto_increment,
  `ml_id` bigint(20) NOT NULL,
  `addr_id` bigint(20) NOT NULL,
  PRIMARY KEY  (`a_id`),
  KEY `ml_id` (`ml_id`),
  KEY `addr_id` (`addr_id`)
) Type=MyISAM;

<#1072>
ALTER TABLE `mail` ADD `use_placeholders` TINYINT( 1 ) NOT NULL ;
ALTER TABLE `mail_saved` ADD `use_placeholders` TINYINT( 1 ) NOT NULL ;
ALTER TABLE `mail_options` ADD `cronjob_notification` TINYINT( 1 ) NOT NULL ;

<#1073>
CREATE TABLE IF NOT EXISTS `payment_coupons` (
  `pc_pk` bigint(20) NOT NULL auto_increment,
  `usr_id` bigint(20) NOT NULL,
  `pc_title` varchar(255) NOT NULL,
  `pc_description` text NOT NULL,
  `pc_type` enum('fix','percent') NOT NULL,
  `pc_value` decimal(10,2) NOT NULL,
  `pc_from` date NOT NULL,
  `pc_till` date NOT NULL,
  `pc_from_enabled` tinyint(1) NOT NULL default '0',
  `pc_till_enabled` tinyint(1) NOT NULL default '0',
  `pc_uses` int(11) NOT NULL,
  `pc_last_change_usr_id` bigint(20) NOT NULL,
  `pc_last_changed` datetime NOT NULL,
  PRIMARY KEY  (`pc_pk`),
  KEY `usr_id` (`usr_id`)
) Type=MyISAM;

<#1074>
CREATE TABLE IF NOT EXISTS `payment_coupons_codes` (
  `pcc_pk` bigint(20) NOT NULL auto_increment,
  `pcc_pc_fk` bigint(20) NOT NULL,
  `pcc_code` varchar(255) NOT NULL,
  PRIMARY KEY  (`pcc_pk`),
  KEY `pcc_pc_fk` (`pcc_pc_fk`),
  KEY `pcc_pc_fk_2` (`pcc_pc_fk`)
) Type=MyISAM;

<#1075>
CREATE TABLE IF NOT EXISTS `payment_coupons_objects` (
  `pco_pc_fk` bigint(20) NOT NULL,
  `ref_id` bigint(20) NOT NULL,
  PRIMARY KEY  (`pco_pc_fk`,`ref_id`)
) Type=MyISAM;

<#1076>
CREATE TABLE IF NOT EXISTS `payment_coupons_tracking` (
  `pct_pk` bigint(20) NOT NULL auto_increment,
  `pct_pcc_fk` bigint(20) NOT NULL,
  `usr_id` bigint(20) NOT NULL,
  `pct_date` datetime NOT NULL,
  PRIMARY KEY  (`pct_pk`),
  KEY `pct_pcc_fk` (`pct_pcc_fk`),
  KEY `usr_id` (`usr_id`)
) Type=MyISAM;

<#1077>
CREATE TABLE IF NOT EXISTS `payment_statistic_coupons` (
  `psc_ps_fk` bigint(20) NOT NULL,
  `psc_pc_fk` bigint(20) NOT NULL,
  `psc_pcc_fk` bigint(20) NOT NULL,
  KEY `psc_ps_fk` (`psc_ps_fk`),
  KEY `psc_pc_fk` (`psc_pc_fk`),
  KEY `psc_pcc_fk` (`psc_pcc_fk`)
) Type=MyISAM;

<#1078>
ALTER TABLE `payment_trustees` ADD `perm_coupons` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `perm_obj`;
ALTER TABLE `payment_settings` ADD `bmf` TEXT NOT NULL AFTER `paypal`;
ALTER TABLE `payment_statistic` ADD `discount` CHAR( 16 ) NOT NULL AFTER `price`;

<#1079>
ALTER TABLE `frm_settings` ADD `post_activation` TINYINT( 1 ) NOT NULL ;
ALTER TABLE `frm_posts` ADD `pos_status` TINYINT( 1 ) NOT NULL DEFAULT '1';
ALTER TABLE `frm_threads` ADD `is_sticky` TINYINT( 1 ) NOT NULL DEFAULT '0';

<#1080>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1081>
CREATE TABLE  `tst_sequence` (
 `active_fi` INT NOT NULL ,
 `pass` INT NOT NULL ,
 `sequence` TEXT NOT NULL ,
 `postponed` TEXT NULL ,
 `hidden` TEXT NULL ,
 `lastchange` TIMESTAMP NOT NULL
) ENGINE = MYISAM ;
<#1082>
ALTER TABLE  `tst_sequence` ADD UNIQUE (
`active_fi` ,
`pass`
);
<#1083>
<?php

// convert old sequence settings into new sequence settings
// this is a very performance-consuming step so maybe the script stops

// check if the step was called previous and retrieve the last active id
$query = "SELECT MAX(active_fi) AS max_id FROM tst_sequence";
$result = $ilDB->query($query);
$startid = 0;
if ($result->numRows()) {
    $row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
    if ($row["max_id"] > 0) {
        $startid = $row["max_id"];
        // if not completed, delete the last entries
        $remove = "DELETE FROM tst_sequence WHERE active_fi = $startid";
        $result = $ilDB->query($remove);
    }
}

// start from the last valid active id and convert the sequence settings
$query = "SELECT * FROM tst_active WHERE active_id >= $startid ORDER BY active_id ASC";
$result = $ilDB->query($query);
if ($result->numRows()) {
    while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
        $sequence_array = explode(",", $row["sequence"]);
        if ($sequence_array === false) {
            $sequence_array = array();
        }
        foreach ($sequence_array as $key => $value) {
            $sequence_array[$key] = intval($value);
        }
        $postponed = "NULL";
        if (strlen($row["postponed"])) {
            $postponed_array = explode(",", $row["postponed"]);
            foreach ($postponed_array as $key => $value) {
                $postponed_array[$key] = intval($value);
            }
            if (is_array($postponed_array)) {
                $postponed = $ilDB->quote(serialize(array_unique($postponed_array)));
            }
        }
        for ($i = 0; $i <= $row["tries"]; $i++) {
            if (($i < $row["tries"]) || ($i == 0)) {
                $insert = sprintf(
                    "INSERT INTO tst_sequence (active_fi, pass, sequence, postponed, hidden) VALUES (%s, %s, %s, %s, NULL)",
                    $ilDB->quote($row["active_id"] . ""),
                    $ilDB->quote($i . ""),
                    $ilDB->quote(serialize($sequence_array)),
                    $postponed
                );
                $ilDB->query($insert);
            }
        }
    }
}

?>
<#1084>
ALTER TABLE  `tst_test_random_question` ADD INDEX (  `pass` );

<#1085>
<?php
$query = "SHOW INDEXES FROM ut_access ";
$res = $ilDB->query($query);
if ($res->numRows() == 1) {
    $query = "ALTER TABLE `ut_access` ADD INDEX ( `acc_obj_id` , `acc_sub_id` , `session_id` ) ";
    $res = $ilDB->query($query);
}
?>

<#1086>
ALTER TABLE `il_news_item` ADD INDEX obj_id (`context_obj_id`);

<#1087>
ALTER TABLE `il_news_item` ADD INDEX c_date (`creation_date`);

<#1088>
DROP TABLE IF EXISTS `il_md_copyright_selections`;
CREATE TABLE `il_md_copyright_selections` (
  `entry_id` int(11) NOT NULL auto_increment,
  `title` varchar(128) NOT NULL default '',
  `description` text NOT NULL,
  `copyright` text NOT NULL,
  `language` varchar(2) NOT NULL default '',
  `costs` tinyint(1) NOT NULL default '0',
  `copyright_and_other_restrictions` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`entry_id`)
) Type=MyISAM  AUTO_INCREMENT=1 ;


<#1089>
INSERT INTO `il_md_copyright_selections` VALUES (0,'Attribution Non-commercial No Derivatives', 'Creative Commons License', '<a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/3.0/">\r\n<img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-nd/3.0/88x31.png" />\r\n</a>\r\n<br />This \r\n<span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/" rel="dc:type">work</span> is licensed under a \r\n<a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/3.0/">Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 License</a>.', 'en', 0, 1);
INSERT INTO `il_md_copyright_selections` VALUES (0,'Attribution Non-commercial Share Alike (by-nc-sa)', 'Creative Commons License', '<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">\r\n<img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-sa/3.0/88x31.png" />\r\n</a>\r\n<br />This \r\n<span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/" rel="dc:type">work</span> is licensed under a \r\n<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative Commons Attribution-Noncommercial-Share Alike 3.0 License</a>.', 'en', 0, 1);
INSERT INTO `il_md_copyright_selections` VALUES (0,'Attribution Non-commercial (by-nc)', 'Creative Commons License', '<a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/">\r\n<img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc/3.0/88x31.png" />\r\n</a>\r\n<br />This \r\n<span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/" rel="dc:type">work</span> is licensed under a \r\n<a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/">Creative Commons Attribution-Noncommercial 3.0 License</a>.', 'en', 0, 1);
INSERT INTO `il_md_copyright_selections` VALUES (0,'Attribution No Derivatives (by-nd)', 'Creative Commons License', '<a rel="license" href="http://creativecommons.org/licenses/by-nd/3.0/">\r\n<img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nd/3.0/88x31.png" />\r\n</a>\r\n<br />This \r\n<span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/" rel="dc:type">work</span> is licensed under a \r\n<a rel="license" href="http://creativecommons.org/licenses/by-nd/3.0/">Creative Commons Attribution-No Derivative Works 3.0 License</a>.', 'en', 0, 1);
INSERT INTO `il_md_copyright_selections` VALUES (0,'Attribution Share Alike (by-sa)', 'Creative Commons License', '<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/">\r\n<img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/88x31.png" />\r\n</a>\r\n<br />This \r\n<span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/" rel="dc:type">work</span> is licensed under a \r\n<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-Share Alike 3.0 License</a>.', 'en', 0, 1);
INSERT INTO `il_md_copyright_selections` VALUES (0,'Attribution (by)', 'Creative Commons License', '<a rel="license" href="http://creativecommons.org/licenses/by/3.0/">\r\n<img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/88x31.png" />\r\n</a>\r\n<br />This \r\n<span xmlns:dc="http://purl.org/dc/elements/1.1/" href=\\"http://purl.org/dc/dcmitype/" rel="dc:type">work</span> is licensed under a \r\n<a rel="license" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 License</a>.', 'en', 0, 1);

<#1090>
TRUNCATE TABLE `adv_md_substitutions`;

<#1091>
CREATE TABLE `container_sorting_settings` (
`obj_id` INT( 11 ) NOT NULL ,
`sort_mode` TINYINT( 1 ) DEFAULT '0' NOT NULL ,
PRIMARY KEY ( `obj_id` )
) TYPE = MYISAM ;

<#1092>
CREATE TABLE `container_sorting` (
  `obj_id` int(11) NOT NULL default '0',
  `type` varchar(5)  NOT NULL default '',
  `items` text NOT NULL,
  PRIMARY KEY  (`obj_id`,`type`)
) Type=MyISAM;

<#1093>
<?php
// new permission
$query = "INSERT INTO rbac_operations SET operation = 'add_thread', " .
    "description = 'Add Threads', " .
    "class = 'object'";

$res = $ilDB->query($query);
$new_ops_id = $ilDB->getLastInsertId();

$query = "SELECT obj_id FROM object_data " .
    "WHERE type ='typ' " .
    "AND title = 'frm' ";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
$typ_id = $row['obj_id'];

$query = "INSERT INTO rbac_ta " .
    "SET typ_id = " . $ilDB->quote($typ_id) . ", " .
    "ops_id = " . $ilDB->quote($new_ops_id) . " ";
$ilDB->query($query);


// Copy template permissions from 'edit_post'
$query = "SELECT ops_id FROM rbac_operations " .
    "WHERE operation = 'edit_post' ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
    $add_post_id = $row['ops_id'];
}

$query = "SELECT * FROM rbac_templates " .
    "WHERE ops_id = " . $ilDB->quote($add_post_id) . " ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
    $query = "INSERT INTO rbac_templates " .
        "SET rol_id = " . $ilDB->quote($row['rol_id']) . ", " .
        "type = 'frm', " .
        "ops_id = " . $ilDB->quote($new_ops_id) . ", " .
        "parent = " . $ilDB->quote($row['parent']) . " ";
    $ilDB->query($query);
}
?>

<#1094>
<?php
// insert new permission add thread to all forum objects
// Copy template permissions from 'edit_post'
$query = "SELECT ops_id FROM rbac_operations " .
    "WHERE operation = 'edit_post' ";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
$add_post_id = $row['ops_id'];

$query = "SELECT ops_id FROM rbac_operations " .
    "WHERE operation = 'add_thread' ";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
$add_thread_id = $row['ops_id'];

// get all forum rbac_pa entries
$query = "SELECT rol_id,ops_id,pa.ref_id FROM object_data AS obd " .
    "JOIN object_reference as ore ON obd.obj_id = ore.obj_id " .
    "JOIN rbac_pa AS pa ON ore.ref_id = pa.ref_id " .
    "WHERE type = 'frm' ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $operations = unserialize($row->ops_id);
    if (in_array($add_post_id, $operations)) {
        $operations[] = $add_thread_id;
        $query = "UPDATE rbac_pa SET " .
            "ops_id = " . $ilDB->quote(serialize($operations)) . " " .
            "WHERE rol_id = " . $ilDB->quote($row->rol_id) . " " .
            "AND ref_id = " . $ilDB->quote($row->ref_id) . " ";
        $ilDB->query($query);
    }
}
?>
<#1095>
ALTER TABLE `tst_tests` ADD `reset_processing_time` SMALLINT NOT NULL DEFAULT '0' AFTER `enable_processing_time`;
<#1096>
ALTER TABLE `tst_times` ADD `pass` TINYINT NOT NULL DEFAULT '0' AFTER `finished` ;

<#1097>
TRUNCATE TABLE `container_sorting`;

<#1098>
ALTER TABLE `cp_package` ADD `global_to_system` TINYINT NOT NULL DEFAULT '1';

<#1099>
CREATE TABLE `cmi_gobjective` (
  `user_id` int(11) default NULL,
  `jsdata` MEDIUMTEXT,
  `obj_id` int(11) default NULL,
  UNIQUE KEY `user_obj` (`user_id`,`obj_id`)
) TYPE = MYISAM;

<#1100>
CREATE TABLE `cp_suspend` (
  `data` MEDIUMTEXT,
  `user_id` int(11) default NULL,
  `obj_id` int(11) default NULL,
  UNIQUE KEY `user_obj` (`user_id`,`obj_id`)
) TYPE=MyISAM;

<#1101>
<?php
$query = "UPDATE rbac_operations SET operation = 'add_post' WHERE operation = 'edit_post' ";
$res = $ilDB->query($query);

?>

<#1102>
<?php
$query = "UPDATE rbac_operations SET operation = 'moderate_frm' WHERE operation = 'delete_post' ";
$res = $ilDB->query($query);

?>

<#1103>
ALTER TABLE `chat_invitations` ADD `chat_id` INT( 11 ) NOT NULL FIRST ;

ALTER TABLE `chat_invitations` DROP PRIMARY KEY, ADD PRIMARY KEY(`chat_id`,`room_id`,`guest_id`);

<#1104>
TRUNCATE TABLE `chat_invitations`;

<#1105>
DROP TABLE IF EXISTS `cmi_gobjective`;
CREATE TABLE `cmi_gobjective` (
  `user_id` int(11) default NULL,
  `satisfied` varchar(50) default NULL,
  `measure` varchar(50) default NULL,
  `scope_id` int(11) default NULL,
  `status` varchar(50) default NULL,
  `objective_id` varchar(253) default NULL,
  UNIQUE KEY `gobjective` (`user_id`,`objective_id`,`scope_id`)
) ENGINE=MyISAM;

<#1106>
ALTER TABLE usr_session MODIFY session_id VARCHAR(80);

<#1107>
ALTER TABLE frm_threads ADD is_closed TINYINT( 1 ) NOT NULL DEFAULT '0';

<#1108>
ALTER TABLE cmi_node ADD entry varchar(255) AFTER delivery_speed;
<#1109>
DROP TABLE IF EXISTS `tst_test_pass_result`;
CREATE TABLE `tst_test_pass_result` (
  `active_fi` int(11) NOT NULL default '0',
  `pass` int(11) NOT NULL default '0',
  `points` double NOT NULL default '0',
  `TIMESTAMP` timestamp NOT NULL,
  UNIQUE KEY `active_fi` (`active_fi`,`pass`)
);
<#1110>
<?php
$query = "SELECT DISTINCT(pass), active_fi FROM tst_test_result ORDER BY active_fi";
$result = $ilDB->query($query);
if ($result->numRows()) {
    while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
        $querysum = sprintf(
            "SELECT SUM(points) AS reachedpoints FROM tst_test_result WHERE active_fi = %s AND pass = %s",
            $ilDB->quote($row["active_fi"] . ""),
            $ilDB->quote($row["pass"] . "")
        );
        $resultsum = $ilDB->query($querysum);
        if ($resultsum->numRows() > 0) {
            $rowsum = $resultsum->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
            $newresultquery = sprintf(
                "REPLACE INTO tst_test_pass_result SET active_fi = %s, pass = %s, points = %s",
                $ilDB->quote($row["active_fi"] . ""),
                $ilDB->quote($row["pass"] . ""),
                $ilDB->quote((($rowsum["reachedpoints"]) ? $rowsum["reachedpoints"] : 0) . "")
            );
            $ilDB->query($newresultquery);
        }
    }
}
?>
<#1111>
<?php
$query = "SELECT * FROM tst_tests";
$result = $ilDB->query($query);
if ($result->numRows()) {
    while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
        $querynew = sprintf(
            "UPDATE tst_tests SET results_presentation = %s WHERE test_id = %s",
            $ilDB->quote(($row["results_presentation"] | 32) . ""),
            $ilDB->quote($row["test_id"] . "")
        );
        $querynew = $ilDB->query($querynew);
    }
}
?>

<#1112>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1113>
DELETE FROM lng_data WHERE identifier='cont_Reference';
ALTER TABLE lng_data DROP PRIMARY KEY;
ALTER TABLE lng_data MODIFY identifier VARCHAR(60);
<#1114>
ALTER TABLE lng_data ADD PRIMARY KEY (module, identifier, lang_key);

<#1115>
<?php
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'lngf'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add write permission for language folder
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);
?>

<#1116>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1117>
<?php
// FORUM MODERATOR TEMPLATE
$query = "INSERT
		  INTO object_data (type, title, description, owner, create_date, last_update)
		  VALUES ('rolt', 'il_frm_moderator', 'Moderator template for forum moderators', -1, NOW(), NOW())";
$this->db->query($query);

$frm_modetator_tpl_id = $this->db->getLastInsertId();

$frm_modetator_ops = array(2, 3, 4, 9, 10, 62);
foreach ($frm_modetator_ops as $op_id) {
    $query = "INSERT
			  INTO rbac_templates
		 	  VALUES (" . $this->db->quote($frm_modetator_tpl_id) . ", 'frm', " . $this->db->quote($op_id) . ", 8)";
    $this->db->query($query);
}

$query = "INSERT
		  INTO rbac_fa
		  VALUES (" . $this->db->quote($frm_modetator_tpl_id) . ", 8, 'n', 'n')";
$this->db->query($query);
?>
<#1118>
<?php

$query = "SELECT * FROM rbac_operations WHERE operation = 'join' ";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
$join_ops_id = $row["ops_id"];

$query = "SELECT * FROM rbac_operations WHERE operation = 'leave' ";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
$leave_ops_id = $row["ops_id"];

$types = array("lm", "sahs", "glo", "webr");

foreach ($types as $type) {
    $query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
        " AND title = '" . $type . "'";
    $res = $this->db->query($query);
    $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
    $typ_id = $row["obj_id"];
    if ($typ_id > 0) {
        $q = ("DELETE FROM rbac_ta WHERE typ_id = " . $ilDB->quote($typ_id) . " AND ops_id = " .
            $ilDB->quote($join_ops_id));
        $ilDB->query($q);
        //echo "<br>$q";
        $q = ("DELETE FROM rbac_ta WHERE typ_id = " . $ilDB->quote($typ_id) . " AND ops_id = " .
            $ilDB->quote($leave_ops_id));
        $ilDB->query($q);
        //echo "<br>$q";
        $q = ("DELETE FROM rbac_templates WHERE type = " . $ilDB->quote($type) . " AND ops_id = " .
            $ilDB->quote($join_ops_id));
        $ilDB->query($q);
        //echo "<br>$q";
        $q = ("DELETE FROM rbac_templates WHERE type = " . $ilDB->quote($type) . " AND ops_id = " .
            $ilDB->quote($leave_ops_id));
        $ilDB->query($q);
        //echo "<br>$q";
    }
}

?>

<#1119>
ALTER TABLE cmi_node ADD TIMESTAMP TIMESTAMP;

<#1120>
CREATE TABLE `ecs_export` (
`obj_id` INT( 11 ) NOT NULL ,
`econtent_id` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `obj_id` )
) TYPE = MYISAM ;
<#1121>
ALTER TABLE `survey_category` CHANGE `title` `title` VARCHAR( 255 ) NOT NULL;
<#1122>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1123>
CREATE TABLE `ecs_import` (
`obj_id` INT( 11 ) NOT NULL ,
`econtent_id` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `obj_id` )
) TYPE = MYISAM ;

<#1124>
CREATE TABLE IF NOT EXISTS `remote_course_settings` (
  `obj_id` int(11) NOT NULL default '0',
  `local_information` text NOT NULL,
  `availability_type` tinyint(1) NOT NULL default '0',
  `start` int(11) NOT NULL default '0',
  `end` int(11) NOT NULL default '0',
  `remote_link` text NOT NULL,
  PRIMARY KEY  (`obj_id`)
) Type=MyISAM;

<#1125>
DROP TABLE IF EXISTS cp_hidelmsui;
DROP TABLE IF EXISTS cp_hideLMSUI;
CREATE TABLE cp_hidelmsui(`cp_node_id` INTEGER, `value` VARCHAR(50) );
ALTER TABLE cp_hidelmsui ADD PRIMARY KEY(cp_node_id);
CREATE INDEX ss_sequencing_id ON cp_hidelmsui(value);

<#1126>
ALTER TABLE `adv_md_substitutions` ADD `hide_field_names` TINYINT NOT NULL ;

<#1127>
<?php

// Add template permissions to root node for Author and Co-Author template

// get author and co-author obj_ids
$query = "SELECT obj_id FROm object_data " .
    "WHERE type = 'rolt' " .
    "AND title = 'Author' " .
    "OR title = 'Co-Author' ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "DELETE FROM rbac_templates WHERE " .
        "rol_id = " . $ilDB->quote($row->obj_id) . " " .
        "AND type = 'root' " .
        "AND ops_id = 2 " .
        "AND parent = 8";
    $ilDB->query($query);

    $query = "INSERT INTO rbac_templates SET " .
        "rol_id = " . $ilDB->quote($row->obj_id) . ", " .
        "type = 'root', " .
        "ops_id = 2, " .
        "parent = 8";
    $ilDB->query($query);

    $query = "DELETE FROM rbac_templates WHERE " .
        "rol_id = " . $ilDB->quote($row->obj_id) . " " .
        "AND type = 'root' " .
        "AND ops_id = 3 " .
        "AND parent = 8";
    $ilDB->query($query);

    $query = "INSERT INTO rbac_templates SET " .
        "rol_id = " . $ilDB->quote($row->obj_id) . ", " .
        "type = 'root', " .
        "ops_id = 3, " .
        "parent = 8";
    $ilDB->query($query);
}
?>
<#1128>
DROP TABLE IF EXISTS il_wiki_data;
CREATE TABLE il_wiki_data
(
	id int NOT NULL PRIMARY KEY,
	startpage varchar(200) NOT NULL DEFAULT '',
	short varchar(20) NOT NULL DEFAULT '',
	online TINYINT DEFAULT 0
);
<#1129>
DROP TABLE IF EXISTS il_wiki_page;
CREATE TABLE il_wiki_page
(
	id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
	title varchar(200) NOT NULL DEFAULT '',
	wiki_id int NOT NULL
);
<#1130>
DROP TABLE IF EXISTS page_history;
CREATE TABLE page_history
(
	page_id int NOT NULL DEFAULT 0,
	parent_type varchar(4) NOT NULL DEFAULT '',
	hdate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	parent_id int,
	nr int,
	user int,
	content mediumtext,
	PRIMARY KEY (page_id, parent_type, hdate)
);
<#1131>
ALTER TABLE page_object ADD COLUMN user int DEFAULT 0;
ALTER TABLE page_object ADD COLUMN view_cnt int DEFAULT 0;
ALTER TABLE page_object ADD COLUMN last_change TIMESTAMP;
ALTER TABLE page_object ADD COLUMN created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';

<#1132>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1133>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1134>
<?php
// register new object type 'newss' for news settings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'cmps', 'Component settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('cmps', '__ComponentSettings', 'Component Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('" . $row->id . "')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id, SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'cmps'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);

?>
<#1135>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1136>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1137>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1138>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1139>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1140>
<?php

// Use deprecated taxonomy folder definition for calendar settings
$query = "UPDATE object_data SET title = 'cals', description = 'Calendar Settings' WHERE type = 'typ' AND title = 'taxf'";
$ilDB->query($query);

$query = "UPDATE object_data SET type = 'cals', title = 'Calendar Settings', description = 'Configure Calendar Settings here' WHERE type = 'taxf'";
$ilDB->query($query);
?>

<#1141>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1142>
<?php
$query = "DELETE FROM rbac_operations WHERE operation = 'create_tax'";
$ilDB->query($query);
?>
<#1143>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1144>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1145>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1146>
CREATE TABLE il_object_def (
	id	CHAR(10) NOT NULL PRIMARY KEY,
	class_name	VARCHAR(200) NOT NULL,
	component VARCHAR(200) NOT NULL,
	location VARCHAR(250) NOT NULL,
	checkbox TINYINT NOT NULL,
	inherit TINYINT NOT NULL,
	translate CHAR(5) NOT NULL,
	devmode TINYINT NOT NULL,
	allow_link TINYINT NOT NULL,
	allow_copy TINYINT NOT NULL,
	rbac TINYINT NOT NULL,
	system TINYINT NOT NULL
);
<#1147>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1148>
CREATE TABLE il_object_subobj (
	parent	CHAR(10) NOT NULL,
	subobj	CHAR(10) NOT NULL,
	PRIMARY KEY (parent, subobj)
);
<#1149>
ALTER TABLE il_object_subobj ADD COLUMN max TINYINT NOT NULL;

<#1150>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1151>
ALTER TABLE il_object_def ADD COLUMN sideblock TINYINT NOT NULL;
<#1152>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1153>
CREATE TABLE il_request_token (
	user_id		INT NOT NULL,
	token		CHAR(64),
	stamp		TIMESTAMP NOT NULL,
	INDEX uid (user_id)
);
<#1154>
ALTER TABLE il_object_def ADD COLUMN default_pos INT NOT NULL;
<#1155>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1156>
CREATE TABLE il_object_group (
	id		CHAR(10) NOT NULL PRIMARY KEY,
	name	VARCHAR(200)
);

<#1157>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1158>
ALTER TABLE il_object_def ADD COLUMN grp CHAR(10) NOT NULL;

<#1159>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1160>
ALTER TABLE il_object_def ADD COLUMN default_pres_pos INT NOT NULL;

<#1161>
ALTER TABLE il_object_group ADD COLUMN default_pres_pos INT NOT NULL;

<#1162>
CREATE TABLE il_pluginslot (
	component VARCHAR(200),
	id	CHAR(10),
	name VARCHAR (200),
	PRIMARY KEY (component, id)
);

<#1163>
CREATE TABLE il_component (
	type CHAR(10),
	name VARCHAR(200),
	id CHAR(10),
	PRIMARY KEY (type, id)
);
<#1164>
DROP TABLE module;

<#1165>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1166>
DROP TABLE service;

<#1167>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1168>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1169>
<?php
// BEGIN WebDAV

// ----------------
// BEGIN Undo previous calls to this update function

// delete 'facs'-objects
$query = "SELECT d.obj_id, r.ref_id"
        . " FROM object_data AS d"
        . " LEFT JOIN object_reference AS r ON r.obj_id=d.obj_id"
        . " WHERE type ='facs'"
        ;
$result = $ilDB->query($query);
while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $obj_id = $row['obj_id'];
    $ref_id = $row['ref_id'];
    if ($ref_id !== null) {
        $ilDB->query("DELETE FROM tree WHERE child=" . $ilDB->quote($ref_id));
        $ilDB->query("DELETE FROM object_reference WHERE ref_id=" . $ilDB->quote($ref_id));
    }
    $ilDB->query("DELETE FROM object_data WHERE obj_id=" . $ilDB->quote($obj_id));
}

// delete 'facs' object type
$query = "SELECT obj_id FROM object_data"
        . " WHERE type ='typ' AND title ='facs'"
        ;
$result = $ilDB->query($query);
while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $typ_id = $row['obj_id'];
    $ilDB->query("DELETE FROM rbac_ta WHERE typ_id=" . $ilDB->quote($typ_id));
    $ilDB->query("DELETE FROM object_data WHERE obj_id=" . $ilDB->quote($typ_id));
}

// ----------------


// REGISTER NEW OBJECT TYPE 'facs' for File Access settings object
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'facs', 'File Access settings object', -1, now(), now())";
$ilDB->query($query);
$typ_id = $ilDB->getLastInsertId();

// REGISTER RBAC OPERATIONS FOR OBJECT TYPE
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
        . "  (" . $ilDB->quote($typ_id) . ",'1')"
        . ", (" . $ilDB->quote($typ_id) . ",'2')"
        . ", (" . $ilDB->quote($typ_id) . ",'3')"
        . ", (" . $ilDB->quote($typ_id) . ",'4')"
        ;
$ilDB->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('facs', '__File Access', 'File Access settings', -1, now(), now())";
$ilDB->query($query);
$obj_id = $ilDB->getLastInsertId();

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES(" . $ilDB->quote($obj_id) . ")";
$res = $ilDB->query($query);
$ref_id = $ilDB->getLastInsertId();

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($ref_id, SYSTEM_FOLDER_ID);


// Create data table for WebDAV Locks
// IMPORTANT: To prevent data loss on installations which use the HSLU-patches,
//            the WebDAV tables must only be created if the do not exist yet,
//            and the tables must be created exactly like this. The tables may
//            be altered in subsequent update scripts however.
//            For performance reasons, all these tables should be InnoDB tables,
//            but they are currently created with the default table engine, in
//            order not to require configuration changes for MySQL.
$q = "CREATE TABLE IF NOT EXISTS dav_lock ( " .
" token varchar(255) NOT NULL default '', " .
" obj_id int(11) NOT NULL default 0, " .
" node_id int(11) NOT NULL default 0, " .
" ilias_owner int(11) NOT NULL default 0, " .
" dav_owner varchar(200) default null, " .
" expires int(11) NOT NULL default 0, " .
" depth int(11) NOT NULL default 0, " .
" type char(1) NOT NULL default 'w', " .
" scope char(1) NOT NULL default 's', " .
" PRIMARY KEY (token), " .
" UNIQUE KEY token (token), " .
" KEY path (obj_id,node_id), " .
" KEY path_3 (obj_id,node_id,token), " .
" KEY expires (expires) " .
")"; //") ENGINE=InnoDB;";
$r = $ilDB->db->query($q);
if (MDB2::isError($r) || MDB2::isError($r->result)) {
    return 'could\'nt create table "dav_lock": ' .
    ((MDB2::isError($r->result)) ? $r->result->getMessage() : $r->getMessage());
}
// Create data table for WebDAV Properties
$q = "CREATE TABLE IF NOT EXISTS dav_property ( " .
" obj_id int(11) NOT NULL default 0, " .
" node_id int(11) NOT NULL default 0, " .
" ns varchar(120) NOT NULL default 'DAV:', " .
" name varchar(120) NOT NULL default '', " .
" value text, " .
" PRIMARY KEY (obj_id,node_id,name,ns), " .
" KEY path (obj_id,node_id) " .
")"; //") ENGINE=InnoDB;";
$r = $ilDB->db->query($q);
if (MDB2::isError($r) || MDB2::isError($r->result)) {
    return 'could\'nt create table "dav_property": ' .
    ((MDB2::isError($r->result)) ? $r->result->getMessage() : $r->getMessage());
}
// END WebDAV

// BEGIN ChangeEvent
// IMPORTANT: To prevent data loss on installations which use the HSLU-patches,
//            the ChangeEvent tables must only be created if the do not exist yet,
//            and the tables must be created exactly like this. The tables may
//            be altered in subsequent update scripts however.
//            For performance reasons, all these tables should be InnoDB tables,
//            but they are currently created with the default table engine, in
//            order not to require configuration changes for MySQL.
// Create tables for events on objects
$q = "CREATE TABLE IF NOT EXISTS write_event ( " .
" obj_id INT(11) NOT NULL DEFAULT 0, " .
" parent_obj_id INT(11) NOT NULL DEFAULT 0, " .
" usr_id INT(11) NOT NULL DEFAULT 0, " .
" action VARCHAR(8) NOT NULL DEFAULT '', " .
" ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, " .
" PRIMARY KEY (obj_id, ts, parent_obj_id, action), " .
" KEY parent_key (parent_obj_id, ts) " .
")"; //") ENGINE=InnoDB";
$r = $ilDB->db->query($q);

$q = "CREATE TABLE IF NOT EXISTS read_event ( " .
" obj_id INT(11) NOT NULL DEFAULT 0, " .
" usr_id INT(11) NOT NULL DEFAULT 0, " .
" ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, " .
" read_count int(11) NOT NULL DEFAULT 0, " .
" PRIMARY KEY (obj_id, usr_id) " .
")"; //") ENGINE=InnoDB";
$r = $ilDB->db->query($q);

$q = "CREATE TABLE IF NOT EXISTS catch_write_events ( " .
" obj_id int(11) NOT NULL default 0, " .
" usr_id int(11) NOT NULL default 0, " .
" ts timestamp NOT NULL default current_timestamp, " .
" PRIMARY KEY (obj_id, usr_id) " .
")"; //") ENGINE=InnoDB";
$r = $ilDB->db->query($q);

// Track existing write events. This MUST always be done, when change event tracking
// is activated. If it is not done, change event tracking will not work as expected.
$q = "INSERT IGNORE INTO write_event " .
    "(obj_id,parent_obj_id,usr_id,action,ts) " .
    "SELECT r1.obj_id,r2.obj_id,d.owner,'create',d.create_date " .
    "FROM object_data AS d " .
    "JOIN object_reference AS r1 ON d.obj_id=r1.obj_id " .
    "JOIN tree AS t ON t.child=r1.ref_id " .
    "JOIN object_reference as r2 on r2.ref_id=t.parent ";
$r = $ilDB->db->query($q);

// activate change event tracking:
$q = "REPLACE INTO settings " .
    "(module, keyword, value) VALUES " .
    "('common', ''enable_change_event_tracking', '1')";
$r = $ilDB->db->query($q);
// END ChangeEvent

?>

<#1170>
CREATE TABLE il_plugin (
	component_type CHAR(10),
	component_name VARCHAR(90),
	slot_id CHAR(10),
	name VARCHAR(40),
	id CHAR(10),
	last_update_version CHAR(10),
	current_version CHAR(10),
	ilias_min_version CHAR(10),
	ilias_max_version CHAR(10),
	active TINYINT,
	PRIMARY KEY (component_type, component_name, slot_id, name)
);

<#1171>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1172>

<#1173>

<#1174>
ALTER TABLE il_plugin ADD COLUMN db_version INT NOT NULL DEFAULT 0;

<#1175>
ALTER TABLE il_plugin DROP COLUMN id;
ALTER TABLE il_plugin DROP COLUMN current_version;
ALTER TABLE il_plugin DROP COLUMN ilias_min_version;
ALTER TABLE il_plugin DROP COLUMN ilias_max_version;

<#1176>
ALTER TABLE ctrl_classfile ADD COLUMN comp_prefix VARCHAR(50) NOT NULL DEFAULT '';
ALTER TABLE ctrl_calls ADD COLUMN comp_prefix VARCHAR(50) NOT NULL DEFAULT '';

<#1177>
INSERT IGNORE INTO `settings` ( `module` , `keyword` , `value` )
VALUES ('file_access', 'inline_file_extensions', '');

<#1178>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1179>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1180>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1181>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1182>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1183>


<#1184>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1185>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1186>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1187>
UPDATE mail AS m
  SET folder_id=(SELECT obj_id FROM mail_obj_data AS d WHERE d.user_id=m.user_id AND type='inbox' LIMIT 1)
  WHERE folder_id=0;

<#1188>
CREATE TABLE `il_tag` (
	`obj_id` INT,
	`obj_type` CHAR(10),
	`sub_obj_id` INT,
	`sub_obj_type` CHAR(10),
	`user_id` INT,
	`tag` VARCHAR(100),
	PRIMARY KEY (obj_id, obj_type, sub_obj_id,
		sub_obj_type, user_id, tag),
	INDEX obj (obj_id, obj_type, sub_obj_id, sub_obj_type),
	INDEX tag (tag),
	INDEX user_id (user_id)
) TYPE = MYISAM;
<#1189>
<?php
// register new object type 'svyf' for survey administration setttings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'svyf', 'Survey Settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('svyf', '__SurveySettings', 'Survey Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('" . $row->id . "')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id, SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'svyf'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);
// Add unlimited invitation as default setting for surveys to be consistent with previous ILIAS installations
$query = "INSERT settings (module, keyword, value) VALUES ('survey', 'unlimited_invitation', '1')";
$this->db->query($query);
?>
<#1190>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1191>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1192>
<?php
// new object type for course sessions
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'sess', 'Session object', -1, now(), now())";
$ilDB->query($query);
$typ_id = $ilDB->getLastInsertId();

// Register permissions for sessions
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
        . "  (" . $ilDB->quote($typ_id) . ",'1')"
        . ", (" . $ilDB->quote($typ_id) . ",'2')"
        . ", (" . $ilDB->quote($typ_id) . ",'3')"
        . ", (" . $ilDB->quote($typ_id) . ",'4')"
        ;
$ilDB->query($query);
?>
<#1193>
DROP TABLE IF EXISTS `il_rating`;
CREATE TABLE `il_rating` (
	`obj_id` INT,
	`obj_type` CHAR(10),
	`sub_obj_id` INT,
	`sub_obj_type` CHAR(10),
	`user_id` INT,
	`rating` INT NOT NULL DEFAULT 0,
	PRIMARY KEY (obj_id, obj_type, sub_obj_id,
		sub_obj_type, user_id),
	INDEX obj (obj_id, obj_type, sub_obj_id, sub_obj_type)
) TYPE = MYISAM;
<#1194>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1195>
<?php
global $ilLog;

// Convert course sessions to ILIAS objects
$query = "SELECT event_id,obj_id,title,description FROM event ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $course_obj_id = $row->obj_id;

    // check if already migrated
    $query = "SELECT type FROM object_data WHERE obj_id = " . $course_obj_id;
    $obj_res = $ilDB->query($query);
    $obj_row = $obj_res->fetchRow();

    if ($obj_row[0] == 'sess') {
        $ilLog->write('DB Migration 1194: Session with event_id: ' . $row->event_id . ' already migrated.');
        continue;
    }

    // find course ref_id
    $query = "SELECT ref_id FROM object_reference WHERE obj_id = '" . $course_obj_id . "' ";
    $ref_res = $ilDB->query($query);
    $ref_row = $ref_res->fetchRow();

    if (!$ref_row[0]) {
        $ilLog->write('DB Migration 1194: Found session without course ref_id. event_id: ' . $row->event_id . ', obj_id: ' . $row->obj_id);
        continue;
    }
    $course_ref_id = $ref_row[0];

    // Create object data entry
    $query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('sess', " . $ilDB->quote($row->title) . ", " . $ilDB->quote($row->description) . ", 6, now(), now())";
    $ilDB->query($query);
    $session_obj_id = $ilDB->getLastInsertId();

    // Insert long description
    $query = "INSERT INTO object_description SET obj_id = " . $session_obj_id . ", description =  " . $ilDB->quote($row->description);

    // Create reference
    $query = "INSERT INTO object_reference (obj_id) VALUES('" . $session_obj_id . "')";
    $ilDB->query($query);
    $session_ref_id = $ilDB->getLastInsertId();

    // check if course is deleted
    // yes => insert into tree with negative tree id
    $query = "SELECT tree FROM tree WHERE child = " . $course_ref_id;
    $tree_res = $ilDB->query($query);
    $tree_row = $tree_res->fetchRow();
    $tree_id = $tree_row[0];
    if ($tree_id != 1) {
        $current_tree = new ilTree($tree_id);
    } else {
        $current_tree = new ilTree(ROOT_FOLDER_ID);
    }

    // Insert into tree
    $current_tree->insertNode($session_ref_id, $course_ref_id);

    // Update all event related tables

    // event
    $query = "UPDATE event SET obj_id = " . $session_obj_id . " " .
        "WHERE event_id = " . $row->event_id;
    $ilDB->query($query);

    // event_appointment
    $query = "UPDATE event_appointment SET event_id = " . $session_obj_id . " " .
        "WHERE event_id = " . $row->event_id . " ";
    $ilDB->query($query);

    // event_id
    $query = "UPDATE event_items SET event_id = " . $session_obj_id . " " .
        "WHERE event_id = " . $row->event_id . " ";
    $ilDB->query($query);

    // event participants
    $query = "UPDATE event_participants SET event_id = " . $session_obj_id . " " .
        "WHERE event_id = " . $row->event_id . " ";
    $ilDB->query($query);

    // adjust permissions
    $query = "SELECT * FROM rbac_pa WHERE ref_id = " . $course_ref_id;
    $pa_res = $ilDB->query($query);
    while ($pa_row = $pa_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $new_ops = array();
        $operations = unserialize($pa_row->ops_id);

        if (in_array(1, $operations)) {
            $new_ops[] = 1;
        }
        if (in_array(2, $operations)) {
            $new_ops[] = 2;
        }
        if (in_array(3, $operations)) {
            $new_ops[] = 3;
        }
        if (in_array(4, $operations)) {
            $new_ops[] = 4;
        }
        $query = "INSERT INTO rbac_pa SET " .
            "rol_id = " . $ilDB->quote($pa_row->rol_id) . ", " .
            "ops_id = " . $ilDB->quote(serialize($new_ops)) . ", " .
            "ref_id = " . $ilDB->quote($session_ref_id) . " ";
        $ilDB->query($query);
    }
}
?>
<#1196>
<?php
// add create operation for
$query = "INSERT INTO rbac_operations " .
    "SET operation = 'create_sess', description = 'create session',class = 'create'";
$ilDB->query($query);

// get new ops_id
$query = "SELECT LAST_INSERT_ID()";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$ops_id = $row[0];

// add create sess for crs
// get category type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='crs'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

?>

<#1197>
<?php

$query = "SELECT ops_id FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$ops_id = $row[0];

// new object type for course sessions
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'sess'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];



// Register copy permissions for sessions
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
        . "  (" . $ilDB->quote($typ_id) . ",'" . $ops_id . "')";
$ilDB->query($query);
?>
<#1198>
ALTER TABLE read_event ADD COLUMN spent_seconds INT NOT NULL DEFAULT 0;

INSERT INTO read_event
SELECT obj_id,user_id,FROM_UNIXTIME(access_time),visits,spent_time from ut_learning_progress
ON DUPLICATE KEY
UPDATE ts=if(FROM_UNIXTIME(access_time) > ts,FROM_UNIXTIME(access_time),ts),spent_seconds=spent_seconds+spent_time, read_count=read_count+visits;

DROP TABLE ut_learning_progress;
<#1199>
ALTER TABLE `rbac_pa` ADD INDEX ( `ref_id` );
<#1200>
ALTER TABLE  `qpl_question_type` ADD  `plugin` TINYINT NOT NULL DEFAULT  '0';

<#1201>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1202>
ALTER TABLE scorm_tracking ADD timestamp timestamp;

<#1203>
ALTER TABLE sahs_lm ADD max_attempt INT DEFAULT 0;

<#1204>
ALTER TABLE sahs_lm ADD module_version INT DEFAULT 1;

<#1205>
CREATE TABLE cmi_custom (sco_id INT DEFAULT 0, obj_id INT DEFAULT 0,user_id INT DEFAULT 0, lvalue varchar(64), rvalue text,timestamp timestamp);

<#1206>
ALTER TABLE cmi_custom ADD PRIMARY KEY (user_id, lvalue, obj_id,sco_id);

<#1207>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1208>
ALTER TABLE il_wiki_data ADD COLUMN rating TINYINT DEFAULT 0;
<#1209>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1210>
DROP TABLE IF EXISTS `grp_settings`;
CREATE TABLE `grp_settings` (
  `obj_id` int(11) NOT NULL,
  `information` text NOT NULL,
  `grp_type` tinyint(1) NOT NULL,
  `registration_type` tinyint(1) NOT NULL,
  `registration_enabled` tinyint(1) NOT NULL,
  `registration_unlimited` tinyint(1) NOT NULL,
  `registration_start` datetime NOT NULL,
  `registration_end` datetime NOT NULL,
  `registration_password` char(32)  NOT NULL,
  `registration_max_members` int(4) NOT NULL,
  `waiting_list` tinyint(1) NOT NULL,
  `latitude` varchar(30) NOT NULL,
  `longitude` varchar(30) NOT NULL,
  `location_zoom` int(11) NOT NULL,
  `enablemap` tinyint(4) NOT NULL,
  PRIMARY KEY  (`obj_id`)
) Type=MyISAM;

<#1211>
<?php
// Migrate existing groups
$query = "SELECT * FROM grp_data ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $unlimited = ($row->expiration == '0000-00-00 00:00:00' ? 1 : 0);
    
    if ($unlimited) {
        $start = '0000-00-00 00:00:00';
    } else {
        $start = '2002-01-01 00:00:00';
    }

    if ($row->register == 0) {
        $unlimited = 1;
    }

    $query = "INSERT INTO grp_settings " .
        "SET obj_id = " . $ilDB->quote($row->grp_id) . ", " .
        "information = '', " .
        "grp_type = 0, " .
        "registration_type = " . $ilDB->quote($row->register) . ", " .
        "registration_enabled = 1, " .
        "registration_unlimited = " . $unlimited . ", " .
        "registration_start = " . $ilDB->quote($start) . " , " .
        "registration_end = " . $ilDB->quote($row->expiration) . ", " .
        "registration_password = " . $ilDB->quote($row->password) . ", " .
        "registration_max_members = 0, " .
        "waiting_list = 0, " .
        "latitude = " . $ilDB->quote($row->latitude) . ", " .
        "longitude = " . $this->db->quote($row->longitude) . ", " .
        "location_zoom = " . $ilDB->quote($row->location_zoom) . ", " .
        "enablemap = " . $ilDB->quote($row->enable_group_map) . " ";
    $ilDB->query($query);
}
?>
<#1212>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1213>
ALTER TABLE il_request_token ADD COLUMN session VARCHAR(100);
<#1214>
ALTER TABLE il_request_token ADD INDEX session (session);
ALTER TABLE il_request_token ADD INDEX token (token);
<#1215>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1216>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1217>
ALTER TABLE page_object CHANGE user last_change_user INT;
ALTER TABLE page_object ADD COLUMN create_user INT;

<#1218>
ALTER TABLE `crs_subscribers` ADD `subject` VARCHAR( 255 ) NOT NULL AFTER `obj_id`;

<#1219>
RENAME TABLE `crs_subscribers`  TO `il_subscribers`;

<#1220>
<?php
$query = "SELECT *,UNIX_TIMESTAMP(application_date) AS unix FROM grp_registration ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "INSERT INTO il_subscribers " .
        "SET obj_id = " . $ilDB->quote($row->grp_id) . ", " .
        "usr_id = " . $ilDB->quote($row->user_id) . ", " .
        "sub_time = " . $ilDB->quote($row->unix) . ", " .
        "subject = " . $ilDB->quote($row->subject) . " ";
    $ilDB->query($query);
}
?>

<#1221>
DROP TABLE grp_registration;

<#1222>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1223>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1224>
ALTER TABLE `chat_invitations` ADD INDEX `invitations` ( `guest_id` , `guest_informed` , `invitation_time` );

<#1225>
<?php

$query = "SELECT * FROM object_data WHERE type = 'typ' AND title = 'sess'";
$res = $ilDB->query($query);

$permissions_empty = true;
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $permissions_empty = false;
}
if ($permissions_empty) {
    // new object type for course sessions
    $query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
                        "VALUES ('typ', 'sess', 'Session object', -1, now(), now())";
    $ilDB->query($query);
    $typ_id = $ilDB->getLastInsertId();

    // Register permissions for sessions
    // 1: edit_permissions, 2: visible, 3: read, 4:write
    $query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
                        . "  (" . $ilDB->quote($typ_id) . ",'1')"
                        . ", (" . $ilDB->quote($typ_id) . ",'2')"
                        . ", (" . $ilDB->quote($typ_id) . ",'3')"
                        . ", (" . $ilDB->quote($typ_id) . ",'4')"
                        ;
    $ilDB->query($query);

    $query = "SELECT ops_id FROM rbac_operations WHERE operation = 'copy'";
    $res = $ilDB->query($query);
    $row = $res->fetchRow();
    $ops_id = $row[0];

    // Register copy permissions for sessions
    $query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
                        . "  (" . $ilDB->quote($typ_id) . ",'" . $ops_id . "')";
    $ilDB->query($query);
}
?>
<#1226>

ALTER TABLE `event_appointment` ADD `start` DATETIME NOT NULL AFTER `event_id` ,
ADD `end` DATETIME NOT NULL AFTER `start` ;

<#1227>
<?php
// migrate DB structure of session appointments
$query = "SELECT * FROM event_appointment ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    if ($row->fulltime) {
        $query = "UPDATE event_appointment SET " .
            "start = " . $ilDB->quote(gmdate('Y-m-d', $row->starting_time)) . ", " .
            "end = " . $ilDB->quote(gmdate('Y-m-d', $row->ending_time)) . " " .
            "WHERE appointment_id = " . $ilDB->quote($row->appointment_id) . " ";
    } else {
        $query = "UPDATE event_appointment SET " .
            "start = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->starting_time)) . ", " .
            "end = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->ending_time)) . " " .
            "WHERE appointment_id = " . $ilDB->quote($row->appointment_id) . " ";
    }
    $ilDB->query($query);
}
?>
<#1228>
ALTER TABLE container_sorting ADD COLUMN child_id INT NOT NULL;
ALTER TABLE container_sorting ADD COLUMN position decimal(10,2) NOT NULL;
<#1229>
ALTER TABLE container_sorting DROP PRIMARY KEY;
<#1230>
<?php
$set = $ilDB->query("SELECT * FROM container_sorting");
$rows = array();
while ($rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
    $rows[] = $rec;
}
$ilDB->query("DELETE FROM container_sorting");
$ilDB->query("ALTER TABLE container_sorting ADD PRIMARY KEY (obj_id, child_id)");
foreach ($rows as $row) {
    $pos = unserialize($row["items"]);
    foreach ($pos as $i => $p) {
        $ilDB->query("REPLACE INTO container_sorting (obj_id, child_id, type, position) VALUES (" .
            $ilDB->quote($row["obj_id"]) . "," .
            $ilDB->quote($i) . "," .
            $ilDB->quote($row["type"]) . "," .
            $ilDB->quote($p) .
            ")");
    }
}
?>
<#1231>
CREATE TABLE `qpl_answer_matching_term` (
  `term_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL,
  `term` text NOT NULL,
  PRIMARY KEY  (`term_id`),
	INDEX (  `question_fi` )
) Type=MyISAM;
<#1232>
<?php
// migrate matching question terms
$query = "SELECT * FROM qpl_answer_matching";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $insertquery = sprintf(
        "INSERT INTO qpl_answer_matching_term (term_id, question_fi, term) VALUES (NULL, %s, %s)",
        $ilDB->quote($row->question_fi),
        $ilDB->quote($row->answertext)
    );
    $ilDB->query($insertquery);
    $newTermID = $ilDB->getLastInsertId();

    $updatequery = sprintf("UPDATE qpl_answer_matching SET answertext = '$newTermID' WHERE answer_id = " . $ilDB->quote($row->answer_id));
    $ilDB->query($updatequery);
}

// migrate matching question solutions
$query = "SELECT DISTINCT question_fi FROM qpl_answer_matching";
$res = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($res)) {
    $solquery = sprintf(
        "SELECT * FROM tst_solutions WHERE question_fi = %s",
        $ilDB->quote($row['question_fi'])
    );
    $solres = $ilDB->query($solquery);
    while ($solrow = $ilDB->fetchAssoc($solres)) {
        $check = sprintf(
            "SELECT * FROM qpl_answer_matching WHERE question_fi = %s AND aorder = %s",
            $ilDB->quote($row['question_fi']),
            $ilDB->quote($solrow['value1'])
        );
        $checkres = $ilDB->query($check);
        if ($checkres->numRows() == 1) {
            $checkrow = $ilDB->fetchAssoc($checkres);
            $update = sprintf(
                "UPDATE tst_solutions SET value1 = %s WHERE solution_id = %s",
                $ilDB->quote($checkrow['answertext']),
                $ilDB->quote($solrow['solution_id'])
            );
            $ilDB->query($update);
        }
    }
}
?>
<#1233>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1234>
<?php
// register new object type 'mcts' for news settings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'mcts', 'Mediacast settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('mcts', '__MediacastSettings', 'Mediacast Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('" . $row->id . "')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id, SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'mcts'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to mcst settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);

?>
<#1235>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1236>
<?php
# DB Update
$query = "ALTER TABLE `il_media_cast_data` ADD COLUMN `downloadable` TINYINT(4) DEFAULT 0 AFTER `public_files`";
$this->db->query($query);
$query = "ALTER TABLE `media_item` MODIFY COLUMN `purpose` ENUM('Standard','Fullscreen','Additional','AudioPortable','VideoPortable') DEFAULT NULL";
$this->db->query($query);
?>

<#1237>
<?php
// register new object type 'wiki' for wikis
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'wiki', 'Wiki', -1, now(), now())";
$this->db->query($query);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'wiki'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations for feed object
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);

// new permission: edit content
$query = "INSERT INTO rbac_operations SET operation = 'edit_content', " .
    "description = 'Edit content', " .
    "class = 'object'";

$res = $ilDB->query($query);
$new_ops_id = $ilDB->getLastInsertId();

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $new_ops_id . "')";
$this->db->query($query);

// add create operation for wikis
$query = "INSERT INTO rbac_operations " .
    "SET operation = 'create_wiki', class='create', description = 'create wiki'";
$ilDB->query($query);

// get new ops_id
$query = "SELECT LAST_INSERT_ID()";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$ops_id = $row[0];

// add create wiki for crs,cat,fold and grp
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='cat'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='crs'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='grp'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='fold'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$ilDB->query($query);

?>
<#1238>
CREATE TABLE wiki_contributor (
  wiki_id INT NOT NULL,
  user_id INT NOT NULL,
  status INT,
  PRIMARY KEY (wiki_id, user_id)
) TYPE=MyISAM;
<#1239>
ALTER TABLE wiki_contributor ADD COLUMN status_time TIMESTAMP;
<#1240>
RENAME TABLE `wiki_contributor`  TO `il_wiki_contributor`;

<#1241>
DROP TABLE IF EXISTS `cal_categories_hidden`;
<#1242>
DROP TABLE IF EXISTS `cal_category_assignments`;
<#1243>
DROP TABLE IF EXISTS `cal_recurrence_rules`;
<#1244>
DROP TABLE IF EXISTS `cal_categories`;
<#1245>
DROP TABLE IF EXISTS `cal_entries`;

<#1246>
CREATE TABLE `cal_categories` (
  `cat_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL,
  `title` char(128)  NOT NULL,
  `color` char(8) NOT NULL,
  `type` tinyint(1) NOT NULL,
  PRIMARY KEY  (`cat_id`)
) Type=MyISAM;

CREATE TABLE `cal_categories_hidden` (
  `user_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  PRIMARY KEY  (`user_id`,`cat_id`),
  KEY `cat_id` (`cat_id`)
) Type=MyISAM;

CREATE TABLE `cal_category_assignments` (
  `cal_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  KEY `cal_id` (`cal_id`,`cat_id`),
  KEY `cat_id` (`cat_id`)
) Type=MyISAM;

CREATE TABLE `cal_entries` (
  `cal_id` int(11) NOT NULL auto_increment,
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  `title` char(128) NOT NULL,
  `subtitle` char(64) NOT NULL,
  `description` text NOT NULL,
  `location` text NOT NULL,
  `fullday` tinyint(1) NOT NULL default '0',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `informations` text NOT NULL,
  `auto_generated` tinyint(1) NOT NULL default '0',
  `context_id` tinyint(2) NOT NULL default '0',
  `translation_type` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`cal_id`)
) Type=MyISAM ;

CREATE TABLE `cal_recurrence_rules` (
  `rule_id` int(11) NOT NULL auto_increment,
  `cal_id` int(11) NOT NULL default '0',
  `cal_recurrence` int(1) NOT NULL default '0',
  `freq_type`char(20) NOT NULL default '',
  `freq_until_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `freq_until_count` int(4) NOT NULL default '0',
  `intervall` int(4) NOT NULL default '0',
  `byday` char(64) NOT NULL default '',
  `byweekno`char(64) NOT NULL default '0',
  `bymonth` char(64) NOT NULL default '',
  `bymonthday` char(64) NOT NULL default '',
  `byyearday` char(64) NOT NULL default '',
  `bysetpos` char(64) NOT NULL default '0',
  `weekstart` char(2) NOT NULL default '',
  PRIMARY KEY  (`rule_id`),
  KEY `cal_id` (`cal_id`)
) Type=MyISAM;

<#1247>
<?php

// new permission
$query = "INSERT INTO rbac_operations SET operation = 'edit_event', " .
    "description = 'Edit calendar event', " .
    "class = 'object'";

$res = $ilDB->query($query);
$new_ops_id = $ilDB->getLastInsertId();

// Calendar settings
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'cals' ";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$cals = $row[0];

// Course
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'crs' ";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$crs = $row[0];

// Group
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'grp' ";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$grp = $row[0];

// Session
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'sess' ";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$sess = $row[0];

$query = "INSERT INTO rbac_ta SET typ_id = " . $ilDB->quote($cals) . ", ops_id = " . $ilDB->quote($new_ops_id) . " ";
$ilDB->query($query);

$query = "INSERT INTO rbac_ta SET typ_id = " . $ilDB->quote($crs) . ", ops_id = " . $ilDB->quote($new_ops_id) . " ";
$ilDB->query($query);

$query = "INSERT INTO rbac_ta SET typ_id = " . $ilDB->quote($grp) . ", ops_id = " . $ilDB->quote($new_ops_id) . " ";
$ilDB->query($query);

$query = "INSERT INTO rbac_ta SET typ_id = " . $ilDB->quote($sess) . ", ops_id = " . $ilDB->quote($new_ops_id) . " ";
$ilDB->query($query);
?>

<#1248>
<?php

$wd = getcwd();

include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');

$query = "SELECT obd.obj_id AS obj_id ,title,od.description AS description,activation_type,activation_start,activation_end, " .
    "subscription_limitation_type,subscription_start,subscription_end FROM crs_settings AS cs  " .
    "JOIN object_data as obd ON obd.obj_id = cs.obj_id " .
    "JOIN object_description AS od ON od.obj_id = obd.obj_id " .
    "WHERE subscription_limitation_type = 2 OR " .
    "activation_type = 2 ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $color = ilCalendarAppointmentColors::_getRandomColorByType('crs');

    $query = "INSERT INTO cal_categories SET " .
        "obj_id = " . $row->obj_id . ", " .
        "title = " . $ilDB->quote($row->title) . ", " .
        "color = '" . $color . "', " .
        "type = 2";
    $ilDB->query($query);

    $cat_id = $ilDB->getLastInsertId();

    if ($row->subscription_limitation_type == 2) {
        $query = "INSERT INTO cal_entries SET " .
            "title = " . $ilDB->quote($row->title) . ", " .
            "subtitle = " . $ilDB->quote('crs_cal_reg_start') . ", " .
            "description = " . $ilDB->quote($row->description) . ", " .
            "start = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->subscription_start)) . ", " .
            "end = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->subscription_start)) . ", " .
            "auto_generated = 1," .
            "context_id = 1, " .
            "translation_type = 1";
        $ilDB->query($query);

        $cal_id = $ilDB->getLastInsertId();

        $query = "INSERT INTO cal_category_assignments " .
            "SET cal_id = " . $ilDB->quote($cal_id) . ", cat_id = " . $ilDB->quote($cat_id) . " ";
        $ilDB->query($query);

        $query = "INSERT INTO cal_entries SET " .
            "title = " . $ilDB->quote($row->title) . ", " .
            "subtitle = " . $ilDB->quote('crs_cal_reg_end') . ", " .
            "description = " . $ilDB->quote($row->description) . ", " .
            "start = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->subscription_end)) . ", " .
            "end = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->subscription_end)) . ", " .
            "auto_generated = 1," .
            "context_id = 2, " .
            "translation_type = 1";
        $ilDB->query($query);

        $cal_id = $ilDB->getLastInsertId();

        $query = "INSERT INTO cal_category_assignments " .
            "SET cal_id = " . $ilDB->quote($cal_id) . ", cat_id = " . $ilDB->quote($cat_id) . " ";
        $ilDB->query($query);
    }
    if ($row->activation_type == 2) {
        $query = "INSERT INTO cal_entries SET " .
            "title = " . $ilDB->quote($row->title) . ", " .
            "subtitle = " . $ilDB->quote('crs_cal_activation_start') . ", " .
            "description = " . $ilDB->quote($row->description) . ", " .
            "start = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->activation_start)) . ", " .
            "end = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->activation_start)) . ", " .
            "auto_generated = 1," .
            "context_id = 3, " .
            "translation_type = 1";
        $ilDB->query($query);

        $cal_id = $ilDB->getLastInsertId();

        $query = "INSERT INTO cal_category_assignments " .
            "SET cal_id = " . $ilDB->quote($cal_id) . ", cat_id = " . $ilDB->quote($cat_id) . " ";
        $ilDB->query($query);

        $query = "INSERT INTO cal_entries SET " .
            "title = " . $ilDB->quote($row->title) . ", " .
            "subtitle = " . $ilDB->quote('crs_cal_activation_end') . ", " .
            "description = " . $ilDB->quote($row->description) . ", " .
            "start = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->activation_end)) . ", " .
            "end = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->activation_end)) . ", " .
            "auto_generated = 1," .
            "context_id = 3, " .
            "translation_type = 1";
        $ilDB->query($query);

        $cal_id = $ilDB->getLastInsertId();

        $query = "INSERT INTO cal_category_assignments " .
            "SET cal_id = " . $ilDB->quote($cal_id) . ", cat_id = " . $ilDB->quote($cat_id) . " ";
        $ilDB->query($query);
    }
}

?>

<#1249>
<?php

$wd = getcwd();

include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');

$query = "SELECT obd.obj_id AS obj_id ,title,od.description AS description, " .
    "registration_type,registration_unlimited,UNIX_TIMESTAMP(registration_start) AS registration_start, " .
    "UNIX_TIMESTAMP(registration_end) AS registration_end " .
    "FROM grp_settings AS gs " .
    "JOIN object_data as obd ON obd.obj_id = gs.obj_id " .
    "JOIN object_description AS od ON obd.obj_id = od.obj_id " .
    "WHERE registration_type != -1 AND " .
    "registration_unlimited = 0 ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $color = ilCalendarAppointmentColors::_getRandomColorByType('grp');

    $query = "INSERT INTO cal_categories SET " .
        "obj_id = " . $row->obj_id . ", " .
        "title = " . $ilDB->quote($row->title) . ", " .
        "color = '" . $color . "', " .
        "type = 2";
    $ilDB->query($query);

    $cat_id = $ilDB->getLastInsertId();

    if ($row->registration_start) {
        $query = "INSERT INTO cal_entries SET " .
                "title = " . $ilDB->quote($row->title) . ", " .
                "subtitle = " . $ilDB->quote('grp_cal_reg_start') . ", " .
                "description = " . $ilDB->quote($row->description) . ", " .
                "start = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->registration_start)) . ", " .
                "end = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->registration_start)) . ", " .
                "auto_generated = 1," .
                "context_id = 1, " .
                "translation_type = 1";
        $ilDB->query($query);

        $cal_id = $ilDB->getLastInsertId();

        $query = "INSERT INTO cal_category_assignments " .
            "SET cal_id = " . $ilDB->quote($cal_id) . ", cat_id = " . $ilDB->quote($cat_id) . " ";
        $ilDB->query($query);
    }
    if ($row->registration_end) {
        $query = "INSERT INTO cal_entries SET " .
            "title = " . $ilDB->quote($row->title) . ", " .
            "subtitle = " . $ilDB->quote('grp_cal_reg_end') . ", " .
            "description = " . $ilDB->quote($row->description) . ", " .
            "start = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->registration_end)) . ", " .
            "end = " . $ilDB->quote(gmdate('Y-m-d H:i:s', $row->registration_end)) . ", " .
            "auto_generated = 1," .
            "context_id = 2, " .
            "translation_type = 1";
        $ilDB->query($query);

        $cal_id = $ilDB->getLastInsertId();

        $query = "INSERT INTO cal_category_assignments " .
            "SET cal_id = " . $ilDB->quote($cal_id) . ", cat_id = " . $ilDB->quote($cat_id) . " ";
        $ilDB->query($query);
    }
}

?>
<#1250>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1251>
<?php

// Adjust role template permission for sessions

$ops = array(1,2,3,4);

$query = "SELECT ops_id FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$ops[] = $row[0];

$query = "DELETE FROM rbac_templates WHERE type = 'sess'";
$ilDB->query($query);


$query = "SELECT * FROM rbac_templates " .
    "WHERE type = 'crs' " .
    "AND ops_id IN ('" . implode("','", $ops) . "') ";

$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "INSERT INTO rbac_templates " .
        "SET rol_id = " . $ilDB->quote($row->rol_id) . ", " .
        "type = 'sess', " .
        "ops_id = " . $ilDB->quote($row->ops_id) . ", " .
        "parent = " . $ilDB->quote($row->parent) . " ";
    $ilDB->query($query);
}
?>

<#1252>
<?php

// Adjust role template permission 'edit_event'
$query = "SELECT ops_id FROM rbac_operations WHERE operation = 'edit_event'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$ops = $row[0];

$query = "DELETE FROM rbac_templates WHERE type = 'crs' AND ops_id = " . $ilDB->quote($ops) . " ";
$ilDB->query($query);

$query = "DELETE FROM rbac_templates WHERE type = 'sess' AND ops_id = " . $ilDB->quote($ops) . " ";
$ilDB->query($query);

$query = "SELECT * FROM rbac_templates WHERE type = 'crs' AND ops_id = 4";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "INSERT INTO rbac_templates SET " .
        "rol_id = " . $ilDB->quote($row->rol_id) . ", " .
        "type = 'sess', " .
        "ops_id = " . $ilDB->quote($ops) . ", " .
        "parent = " . $ilDB->quote($row->parent) . " ";
    $ilDB->query($query);

    $query = "INSERT INTO rbac_templates SET " .
        "rol_id = " . $ilDB->quote($row->rol_id) . ", " .
        "type = 'crs', " .
        "ops_id = " . $ilDB->quote($ops) . ", " .
        "parent = " . $ilDB->quote($row->parent) . " ";
    $ilDB->query($query);
}
?>

<#1253>
<?php
// Adjust role template permission 'edit_event' for groups
$query = "SELECT ops_id FROM rbac_operations WHERE operation = 'edit_event'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$ops = $row[0];

$query = "DELETE FROM rbac_templates WHERE type = 'grp' AND ops_id = " . $ilDB->quote($ops) . " ";
$ilDB->query($query);

$query = "SELECT * FROM rbac_templates WHERE type = 'grp' AND ops_id = 4";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "INSERT INTO rbac_templates SET " .
        "rol_id = " . $ilDB->quote($row->rol_id) . ", " .
        "type = 'grp', " .
        "ops_id = " . $ilDB->quote($ops) . ", " .
        "parent = " . $ilDB->quote($row->parent) . " ";
    $ilDB->query($query);
}
?>

<#1254>
<?php
$query = "SELECT ops_id FROM rbac_operations WHERE operation = 'edit_event'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$ops = $row[0];

// Add permission 'edit_event' to existing courses
$query = "SELECT ref_id FROM object_data AS obd JOIN object_reference AS obr ON obd.obj_id = obr.obj_id WHERE type = 'crs' " .
    "OR type = 'grp' OR type = 'sess'";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    // get rbac_pa entries
    $query = "SELECT * FROM rbac_pa WHERE ref_id = " . $ilDB->quote($row->ref_id) . " ";
    $pa_res = $ilDB->query($query);
    while ($pa_row = $pa_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $current_ops = unserialize($pa_row->ops_id);
        if (in_array(4, $current_ops) and !in_array($ops, $current_ops)) {
            $current_ops[] = (int) $ops;
            $query = "UPDATE rbac_pa SET ops_id = " . $ilDB->quote(serialize($current_ops)) . " " .
                "WHERE rol_id = " . $ilDB->quote($pa_row->rol_id) . " " .
                "AND ref_id = " . $ilDB->quote($pa_row->ref_id) . " ";
            $ilDB->query($query);
        }
    }
}
?>
<#1255>
<?php
$wd = getcwd();

global $ilLog;

include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');

$query = "SELECT obd.obj_id AS obj_id ,obd.title,obd.description AS description,start,end, location,fulltime " .
    "FROM event AS e " .
    "JOIN object_data as obd ON obd.obj_id = e.obj_id " .
    "LEFT JOIN object_description AS od ON od.obj_id = obd.obj_id " .
    "JOIN event_appointment AS ea ON e.obj_id = ea.event_id ";
$ilLog->write($query);
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $color = ilCalendarAppointmentColors::_getRandomColorByType('sess');

    $query = "INSERT INTO cal_categories SET " .
        "obj_id = " . $ilDB->quote($row->obj_id) . ", " .
        "title = " . $ilDB->quote($row->title) . ", " .
        "color = '" . $color . "', " .
        "type = 2";
    $ilDB->query($query);

    $cat_id = $ilDB->getLastInsertId();

    $query = "INSERT INTO cal_entries SET " .
        "title = " . $ilDB->quote($row->title) . ", " .
        "subtitle = '', " .
        "description = " . $ilDB->quote($row->description) . ", " .
        "fullday = " . $ilDB->quote($row->fulltime) . ", " .
        "start = " . $ilDB->quote($row->start) . ", " .
        "end = " . $ilDB->quote($row->end) . ", " .
        "auto_generated = 1," .
        "context_id = 1, " .
        "translation_type = 0 ";
    $ilDB->query($query);
    $cal_id = $ilDB->getLastInsertId();

    $query = "INSERT INTO cal_category_assignments " .
        "SET cal_id = " . $ilDB->quote($cal_id) . ", cat_id = " . $ilDB->quote($cat_id) . " ";
    $ilDB->query($query);
}
?>

<#1256>
<?php
$wd = getcwd();

global $ilLog;

include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');

// Create missing crs calendars

$query = "SELECT obd.obj_id,obd.title,obd.type FROM object_data AS obd " .
    "LEFT JOIN cal_categories AS cc on obd.obj_id = cc.obj_id AND cc.type = 2 " .
    "WHERE cc.obj_id IS NULL and obd.type = 'crs' ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $color = ilCalendarAppointmentColors::_getRandomColorByType('crs');

    $query = "INSERT INTO cal_categories SET " .
        "obj_id = " . $ilDB->quote($row->obj_id) . ", " .
        "title = " . $ilDB->quote($row->title) . ", " .
        "color = '" . $color . "', " .
        "type = 2";
    $ilDB->query($query);
}

// Create missing grp calendars
$query = "SELECT obd.obj_id,obd.title,obd.type FROM object_data AS obd " .
    "LEFT JOIN cal_categories AS cc on obd.obj_id = cc.obj_id AND cc.type = 2 " .
    "WHERE cc.obj_id IS NULL and obd.type = 'grp' ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $color = ilCalendarAppointmentColors::_getRandomColorByType('grp');

    $query = "INSERT INTO cal_categories SET " .
        "obj_id = " . $ilDB->quote($row->obj_id) . ", " .
        "title = " . $ilDB->quote($row->title) . ", " .
        "color = '" . $color . "', " .
        "type = 2";
    $ilDB->query($query);
}

?>
<#1257>
ALTER TABLE il_wiki_data ADD COLUMN introduction TEXT;
<#1258>
ALTER TABLE mail_saved
CHANGE rcp_to rcp_to TEXT NULL DEFAULT NULL ,
CHANGE rcp_cc rcp_cc TEXT NULL DEFAULT NULL ,
CHANGE rcp_bcc rcp_bcc TEXT NULL DEFAULT NULL ,
CHANGE m_type m_type VARCHAR(255) NULL DEFAULT NULL;

<#1259>
<?php
// Migrate objective percents to points
$query = "SELECT * FROM crs_objective_tst ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "SELECT * FROM crs_objective_qst " .
        "WHERE objective_id = " . $ilDB->quote($row->objective_id) . " " .
        "AND ref_id = " . $ilDB->quote($row->ref_id) . " ";
    $qst_res = $ilDB->query($query);

    $sum_points = 0;
    while ($qst_row = $qst_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        // Read possible points
        $query = "SELECT points FROM qpl_questions WHERE question_id = " . $ilDB->quote($qst_row->question_id) . " ";
        $p_res = $ilDB->query($query);

        while ($p_row = $p_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sum_points += $p_row->points;
        }
    }
    $required = $sum_points * $row->tst_limit / 100;

    $query = "UPDATE crs_objective_tst " .
        "SET tst_limit = " . $ilDB->quote($required) . " " .
        "WHERE test_objective_id = " . $ilDB->quote($row->test_objective_id) . " ";
    $ilDB->query($query);
}
?>

<#1260>
<?php
$objectives = array();
$query = "SELECT * FROM crs_objective_tst ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    if (!isset($objectives[$row->objective_id][$row->tst_status])) {
        $objectives[$row->objective_id][$row->tst_status] = $row->tst_limit;
    } else {
        $objectives[$row->objective_id][$row->tst_status] += $row->tst_limit;
    }
}

foreach ($objectives as $objective_id => $status) {
    if (isset($status[0])) {
        $query = "UPDATE crs_objective_tst SET tst_limit = " . $ilDB->quote($status[0]) . " " .
            "WHERE objective_id = " . $ilDB->quote($objective_id) . " " .
            "AND tst_status = 0 ";
        $ilDB->query($query);
    }
    if (isset($status[1])) {
        $query = "UPDATE crs_objective_tst SET tst_limit = " . $ilDB->quote($status[1]) . " " .
            "WHERE objective_id = " . $ilDB->quote($objective_id) . " " .
            "AND tst_status = 1 ";
        $ilDB->query($query);
    }
}
?>

<#1261>
CREATE TABLE IF NOT EXISTS `ecs_events` (
`event_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`type` CHAR( 32 ) NOT NULL ,
`id` INT( 11 ) NOT NULL ,
`op` CHAR( 32 ) NOT NULL ,
PRIMARY KEY ( `event_id` )
) TYPE = MYISAM ;

<#1262>
<?php

$query = "DESCRIBE ecs_import ";
$res = $ilDB->query($query);

$mid_missing = true;
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    if ($row->field == 'mid' or $row->Field == 'mid') {
        $mid_missing = false;
        continue;
    }
}

if ($mid_missing) {
    $query = "ALTER TABLE ecs_import ADD mid INT( 11 ) NOT NULL DEFAULT '0' AFTER obj_id ";
    $ilDB->query($query);
}
?>

<#1263>
ALTER TABLE `usr_data` CHANGE `auth_mode` `auth_mode` ENUM( 'default', 'local', 'ldap', 'radius', 'shibboleth', 'script', 'cas', 'soap', 'ecs' ) NOT NULL DEFAULT 'default';

<#1264>
<?php
$query = "DESCRIBE remote_course_settings ";
$res = $ilDB->query($query);

$mid_missing = true;
$org_missing = true;
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    if ($row->field == 'mid' or $row->Field == 'mid') {
        $mid_missing = false;
    }
    if ($row->field == 'organization' or $row->Field == 'organization') {
        $org_missing = false;
    }
}
if ($mid_missing) {
    $query = "ALTER TABLE remote_course_settings ADD mid INT( 11 ) NOT NULL DEFAULT '0'";
    $ilDB->query($query);
}
if ($org_missing) {
    $query = "ALTER TABLE remote_course_settings ADD organization TEXT NOT NULL";
    $ilDB->query($query);
}
?>

<#1265>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1266>
ALTER TABLE `grp_settings` ADD `registration_membership_limited` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `registration_password` ;

<#1267>
<?php

// update new group setting field registration_membership limited
$query = "SELECT obj_id,registration_max_members FROM grp_settings ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    if ($row->registration_max_members) {
        $query = "UPDATE grp_settings SET registration_membership_limited = 1 WHERE obj_id = " . $ilDB->quote($row->obj_id) . " ";
        $ilDB->query($query);
    }
}
?>

<#1268>
ALTER TABLE `crs_settings` ADD `subscription_membership_limitation` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `subscription_password`;

<#1269>
<?php

// update new group setting field registration_membership limited
$query = "SELECT obj_id,subscription_max_members FROM crs_settings ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    if ($row->subscription_max_members) {
        $query = "UPDATE crs_settings SET subscription_membership_limitation = 1 WHERE obj_id = " . $ilDB->quote($row->obj_id) . " ";
        $ilDB->query($query);
    }
}
?>
<#1270>
ALTER TABLE `usr_data` CHANGE `client_ip` `client_ip` VARCHAR( 255 );
<#1271>
ALTER TABLE `tst_invited_user` CHANGE `clientip` `clientip` VARCHAR( 255 );

<#1272>
DROP TABLE IF EXISTS il_wiki_missing_page;
CREATE TABLE il_wiki_missing_page
(
	wiki_id INT NOT NULL,
	source_id INT NOT NULL,
	target_name varchar(200) NOT NULL DEFAULT '',
	INDEX wiki_id (wiki_id)
);

<#1273>
ALTER TABLE personal_clipboard ADD COLUMN insert_time TIMESTAMP;
ALTER TABLE personal_clipboard ADD COLUMN parent INT NOT NULL DEFAULT 0;
<#1274>
ALTER TABLE personal_clipboard ADD COLUMN order_nr INT NOT NULL DEFAULT 0;

<#1275>
DROP TABLE IF EXISTS cal_shared;
CREATE TABLE `cal_shared` (
  `cal_id` int(11) NOT NULL,
  `obj_id` int(11) NOT NULL,
  `obj_type` int(1) NOT NULL,
  `create_date` TIMESTAMP NOT NULL,
  PRIMARY KEY  (`cal_id`,`obj_id`)
) Type=MyISAM;

<#1276>
DROP TABLE IF EXISTS cal_shared_status;
CREATE TABLE `cal_shared_status` (
`cal_id` INT( 11 ) NOT NULL ,
`usr_id` INT( 11 ) NOT NULL ,
`status` INT( 1 ) NOT NULL ,
PRIMARY KEY ( `cal_id` , `usr_id` )
) Type = MYISAM ;

<#1277>
<?php
if (!$ilDB->tableColumnExists("tst_tests", "kiosk")) {
    $query = "ALTER TABLE tst_tests ADD COLUMN kiosk INT NOT NULL DEFAULT 0";
    $res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_tests", "resultoutput")) {
    $query = "ALTER TABLE tst_tests ADD COLUMN resultoutput INT NOT NULL DEFAULT 0";
    $res = $ilDB->query($query);
}
?>
<#1278>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1279>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1280>
<?php

$query = "SELECT * FROM ut_lp_event_collections ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $event_id = $row->item_id;
    $crs_obj_id = $row->obj_id;

    // Read event ref_id
    $query = "SELECT ref_id FROM event AS e " .
        "JOIN object_data AS od ON e.obj_id = od.obj_id " .
        "JOIN object_reference AS obr ON od.obj_id = obr.obj_id " .
        "WHERE event_id = " . $ilDB->quote($row->item_id) . " ";
    $ref_res = $ilDB->query($query);
    while ($ref_row = $ref_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $event_ref_id = $ref_row->ref_id;
    }

    if ($event_ref_id) {
        $query = "DELETE FROM ut_lp_collections " .
            "WHERE item_id = " . $ilDB->quote($event_ref_id) . " ";
        $ilDB->query($query);

        $query = "INSERT INTO ut_lp_collections " .
            "SET obj_id = " . $ilDB->quote($crs_obj_id) . ", " .
            "item_id = " . $ilDB->quote($event_ref_id) . " ";
        $ilDB->query($query);
    }
}
?>

<#1281>
DROP TABLE ut_lp_event_collections;

<#1282>
ALTER TABLE `grp_settings` CHANGE `registration_start` `registration_start` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
CHANGE `registration_end` `registration_end` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00';

<#1283>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1284>
ALTER TABLE `il_media_cast_data` ADD COLUMN `access` TINYINT(4) DEFAULT 0 AFTER `downloadable`;
<#1285>
<#1286>
CREATE TABLE `data_cache` (
  `module` varchar(50) NOT NULL DEFAULT 'common',
  `keyword` varchar(50) NOT NULL DEFAULT '',
  `value` mediumtext NULL DEFAULT NULL,
  PRIMARY KEY  (`module`,`keyword`)
);
<#1287>
CREATE TABLE `payment_topics` (
  `pt_topic_pk` bigint(20) NOT NULL auto_increment,
  `pt_topic_title` varchar(255) NOT NULL,
  `pt_topic_sort` int(11) NOT NULL,
  `pt_topic_created` int(11) NOT NULL,
  `pt_topic_changed` int(11) NOT NULL,
  PRIMARY KEY  (`pt_topic_pk`)
);

<#1288>
ALTER TABLE `payment_objects` ADD `pt_topic_fk` INT(11) NOT NULL AFTER `vendor_id`;
ALTER TABLE `payment_objects` ADD `image` VARCHAR(255) NOT NULL AFTER `pt_topic_fk`;

<#1289>
ALTER TABLE `payment_settings` ADD `topics_allow_custom_sorting` TINYINT(1) NOT NULL ,
ADD `topics_sorting_type` TINYINT(1) NOT NULL ,
ADD `topics_sorting_direction` VARCHAR(4) NOT NULL,
ADD `shop_enabled` TINYINT( 1 ) NOT NULL DEFAULT '0',
ADD `max_hits` INT NOT NULL;

UPDATE payment_settings SET max_hits = 20;
<#1290>
<?php
$query = "SELECT * FROM payment_objects";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $ilDB->query("UPDATE payment_settings SET shop_enabled = 1");
    break;
}
?>

<#1291>
CREATE TABLE `payment_topics_user_sorting` (
  `ptus_pt_topic_fk` int(11) NOT NULL,
  `ptus_usr_id` int(11) NOT NULL,
  `ptus_sorting` int(11) NOT NULL,
  PRIMARY KEY  (`ptus_pt_topic_fk`,`ptus_usr_id`)
);

<#1292>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1293>
ALTER TABLE `usr_data` ADD `login_attempts` TINYINT( 2 ) NOT NULL ,
ADD `last_password_change` INT( 11 ) NOT NULL ;
UPDATE usr_data SET last_password_change = UNIX_TIMESTAMP();

<#1294>
INSERT INTO `settings` ( `module`, `keyword`, `value` )
VALUES ( 'common', 'ps_account_security_mode', '1' );

<#1295>
<?php
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'wiki'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add missing delete rbac operation for wikis
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','6')";
$this->db->query($query);

?>

<#1296>
<?php

// copy permission for wikis
$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$ops_id = $row->ops_id;

$all_types = array('wiki');
foreach ($all_types as $type) {
    $query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = '" . $type . "'";
    $res = $ilDB->query($query);
    $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

    $query = "INSERT INTO rbac_ta SET typ_id = '" . $row->obj_id . "', ops_id = '" . $ops_id . "'";
    $ilDB->query($query);
}
?>
<#1297>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1298>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1299>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1300>
ALTER TABLE `tst_times` ADD INDEX ( `pass` );
<#1301>
<?php
if (!$ilDB->tableColumnExists("tst_test_pass_result", "maxpoints")) {
    $query = "ALTER TABLE tst_test_pass_result ADD COLUMN maxpoints INT NOT NULL DEFAULT 0";
    $res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_test_pass_result", "questioncount")) {
    $query = "ALTER TABLE tst_test_pass_result ADD COLUMN questioncount INT NOT NULL DEFAULT 0";
    $res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_test_pass_result", "answeredquestions")) {
    $query = "ALTER TABLE tst_test_pass_result ADD COLUMN answeredquestions INT NOT NULL DEFAULT 0";
    $res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_test_pass_result", "workingtime")) {
    $query = "ALTER TABLE tst_test_pass_result ADD COLUMN workingtime INT NOT NULL DEFAULT 0";
    $res = $ilDB->query($query);
}
?>
<#1302>
<?php
// update tst_test_pass_result and add cached data
    function lookupRandomTestFromActiveId($active_id)
    {
        global $ilDB;

        $query = sprintf(
            "SELECT tst_tests.random_test FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_active.test_fi = tst_tests.test_id",
            $ilDB->quote($active_id . "")
        );
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            return $row['random_test'];
        }
        return 0;
    }

    function getQuestionCountAndPointsForPassOfParticipant($active_id, $pass)
    {
        global $ilDB;
        $random = lookupRandomTestFromActiveId($active_id);
        if ($random) {
            $query = sprintf(
                "SELECT tst_test_random_question.pass, COUNT(tst_test_random_question.question_fi) AS qcount, " .
                "SUM(qpl_questions.points) AS qsum FROM tst_test_random_question, qpl_questions " .
                "WHERE tst_test_random_question.question_fi = qpl_questions.question_id AND " .
                "tst_test_random_question.active_fi = %s and pass = %s GROUP BY tst_test_random_question.active_fi, " .
                "tst_test_random_question.pass",
                $ilDB->quote($active_id),
                $ilDB->quote($pass)
            );
        } else {
            $query = sprintf(
                "SELECT COUNT(tst_test_question.question_fi) AS qcount, " .
                "SUM(qpl_questions.points) AS qsum FROM tst_test_question, qpl_questions, tst_active " .
                "WHERE tst_test_question.question_fi = qpl_questions.question_id AND tst_test_question.test_fi = tst_active.test_fi AND " .
                "tst_active.active_id = %s GROUP BY tst_test_question.test_fi",
                $ilDB->quote($active_id)
            );
        }
        $result = $ilDB->query($query);
        if ($result->numRows()) {
            $row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
            return array("count" => $row["qcount"], "points" => $row["qsum"]);
        } else {
            return array("count" => 0, "points" => 0);
        }
    }

    function getWorkingTimeOfParticipantForPass($active_id, $pass)
    {
        global $ilDB;

        $query = sprintf(
            "SELECT * FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started",
            $ilDB->quote($active_id . ""),
            $ilDB->quote($pass . "")
        );
        $result = $ilDB->query($query);
        $time = 0;
        while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
            $epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
            $epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
            $time += ($epoch_2 - $epoch_1);
        }
        return $time;
    }

    $query = "SELECT * FROM tst_test_pass_result WHERE maxpoints = 0 AND questioncount = 0 AND answeredquestions = 0 AND workingtime = 0 ORDER BY active_fi, pass";
    $result = $ilDB->query($query);
    if ($result->numRows()) {
        while ($foundrow = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $active_id = $foundrow["active_fi"];
            $pass = $foundrow["pass"];
            $data = getQuestionCountAndPointsForPassOfParticipant($active_id, $pass);
            $time = getWorkingTimeOfParticipantForPass($active_id, $pass);
            // update test pass results
            $pointquery = sprintf(
                "SELECT SUM(points) AS reachedpoints, COUNT(question_fi) AS answeredquestions FROM tst_test_result WHERE active_fi = %s AND pass = %s",
                $ilDB->quote($active_id . ""),
                $ilDB->quote($pass . "")
            );
            $pointresult = $ilDB->query($pointquery);
            if ($pointresult->numRows() > 0) {
                $pointrow = $pointresult->fetchRow(MDB2_FETCHMODE_ASSOC);
                $newresultquery = sprintf(
                    "REPLACE INTO tst_test_pass_result SET active_fi = %s, pass = %s, points = %s, maxpoints = %s, questioncount = %s, answeredquestions = %s, workingtime = %s",
                    $ilDB->quote($active_id . ""),
                    $ilDB->quote($pass . ""),
                    $ilDB->quote((($pointrow["reachedpoints"]) ? $pointrow["reachedpoints"] : 0) . ""),
                    $ilDB->quote($data["points"]),
                    $ilDB->quote($data["count"]),
                    $ilDB->quote($pointrow["answeredquestions"]),
                    $ilDB->quote($time)
                );
                $ilDB->query($newresultquery);
            }
        }
    }
?>

<#1303>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1304>
<?php
if (!$ilDB->tableColumnExists("tst_tests", "finalstatement")) {
    $query = "ALTER TABLE `tst_tests` ADD `finalstatement` TEXT NULL";
    $res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_tests", "showfinalstatement")) {
    $query = "ALTER TABLE `tst_tests` ADD `showfinalstatement` INT NOT NULL DEFAULT '0'";
    $res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_tests", "showinfo")) {
    $query = "ALTER TABLE `tst_tests` ADD `showinfo` INT NOT NULL DEFAULT '1'";
    $res = $ilDB->query($query);
}
?>
<#1305>
<?php
if (!$ilDB->tableColumnExists("tst_tests", "showinfo")) {
    $query = "ALTER TABLE `tst_tests` ADD `forcejs` INT NOT NULL DEFAULT '0'";
    $res = $ilDB->query($query);
}
?>
<#1306>
<?php
// correct the wrong statement #1305
if (!$ilDB->tableColumnExists("tst_tests", "forcejs")) {
    $query = "ALTER TABLE `tst_tests` ADD `forcejs` INT NOT NULL DEFAULT '0'";
    $res = $ilDB->query($query);
}
?>
<#1307>
ALTER TABLE page_object MODIFY last_change DATETIME DEFAULT '0000-00-00 00:00:00';
<#1308>
ALTER TABLE page_object ADD COLUMN render_md5 VARCHAR(32) DEFAULT '';
ALTER TABLE page_object ADD COLUMN rendered_content MEDIUMTEXT DEFAULT '';
<#1309>
ALTER TABLE page_object ADD COLUMN rendered_time DATETIME DEFAULT '0000-00-00 00:00:00';
<#1310>
UPDATE style_parameter SET tag='div' WHERE tag='p';
<#1311>
UPDATE style_data SET uptodate = 0;
<#1312>
<?php

$classes = array("Example", "Additional", "Citation", "Mnemonic", "Remark");
$pars = array("margin-top", "margin-bottom");

$query = "SELECT DISTINCT `style_id` FROM style_parameter";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
    foreach ($classes as $curr_class) {
        foreach ($pars as $curr_par) {
            $query = "SELECT id FROM style_parameter WHERE style_id='" . $row["style_id"] . "'" .
                " AND tag = 'div' AND class='" . $curr_class . "' AND parameter = '" . $curr_par . "'";
            $res2 = $ilDB->query($query);
            if ($row2 = $res2->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
                $q = "UPDATE style_parameter SET value='10px' WHERE id = '" . $row2["id"] . "'";
                //echo "<br>".$q;
                $ilDB->query($q);
            } else {
                $q = "INSERT INTO style_parameter (style_id, tag, class, parameter,value) VALUES " .
                    "('" . $row["style_id"] . "','div','" . $curr_class . "','" . $curr_par . "','10px')";
                //echo "<br>".$q;
                $ilDB->query($q);
            }
        }
    }
}
?>

<#1313>
ALTER TABLE `container_sorting` CHANGE `type` `parent_type` VARCHAR( 5 ) NOT NULL,
CHANGE `items` `parent_id` INT( 11 ) NOT NULL;

<#1314>
<?php

$query = "UPDATE container_sorting SET parent_type = '',parent_id = 0";
$ilDB->query($query);

?>

<#1315>
ALTER TABLE `container_sorting`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `obj_id`,
     `parent_type`,
     `parent_id`,
     `child_id`);

<#1316>
<?php

$query = "SELECT * FROM crs_settings ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    switch ($row->sortorder) {
        case 1:
            $sort = 1;
            break;
        
        case 3:
            $sort = 2;
            break;
            
        case 2:
        default:
            $sort = 0;
    }
    $query = "DELETE FROM container_sorting_settings WHERE obj_id = " . $ilDB->quote($row->obj_id) . " ";
    $ilDB->query($query);
    
    $query = "INSERT INTO container_sorting_settings SET " .
        "obj_id = " . $ilDB->quote($row->obj_id) . ", " .
        "sort_mode = " . $ilDB->quote($sort) . " ";
    $ilDB->query($query);
}
?>

<#1317>
<?php

$query = "SELECT obr.obj_id AS parent_id,ci.obj_id AS item_id,position FROM crs_items AS ci " .
    "JOIN object_reference AS obr ON ci.parent_id = obr.ref_id ";
$res = $ilDB->query($query);

while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "DELETE FROM container_sorting WHERE " .
        "obj_id = " . $ilDB->quote($row->parent_id) . " " .
        "AND child_id = " . $ilDB->quote($row->item_id) . " ";
    $ilDB->query($query);
    
    $query = "INSERT INTO container_sorting " .
        "SET obj_id = " . $ilDB->quote($row->parent_id) . ", " .
        "parent_type = '', " .
        "parent_id = 0, " .
        "child_id = " . $ilDB->quote($row->item_id) . ", " .
        "position = " . $ilDB->quote($row->position) . " ";
    $ilDB->query($query);
}
?>
<#1318>
ALTER TABLE il_tag ADD COLUMN offline TINYINT NOT NULL DEFAULT 0;
<#1319>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1320>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1321>
ALTER TABLE lng_data ADD COLUMN local_change DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
<#1322>
ALTER TABLE content_object ADD COLUMN prevent_glossary_appendix_active ENUM ('y','n') DEFAULT 'n';
<#1323>
ALTER TABLE `survey_questiontype` DROP `TIMESTAMP`;
ALTER TABLE `survey_questiontype` ADD `plugin` TINYINT NOT NULL DEFAULT '0';
<#1324>
ALTER TABLE `page_history` ADD COLUMN ilias_version VARCHAR(20);
UPDATE page_history SET ilias_version = '3.10.0';
<#1325>
# Add missing file name extensions to object titles of file objects
# Don't add the extension, if the file name starts with a '.'.
UPDATE object_data AS o, file_data AS f 
SET o.title = CONCAT(o.title, REVERSE(LEFT(REVERSE(f.file_name), INSTR(REVERSE(f.file_name), '.'))))
WHERE f.file_id=o.obj_id 
AND o.type='file'
AND INSTR(f.file_name,'.') > 1
AND LEFT(REVERSE(file_name),INSTR(REVERSE(file_name),'.')) <> LEFT(REVERSE(title),INSTR(REVERSE(title),'.'));

<#1326>
<?php
// insert link definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
         "VALUES ('typ', 'crsr', 'Course Reference Object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$copy_id = $row->ops_id;


// add operation assignment to link object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete, and copy
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','6')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $copy_id . "')";
$this->db->query($query);

// add create operation
$query = "INSERT INTO rbac_operations " .
    "SET operation = 'create_crsr', description = 'create course reference'";
$this->db->query($query);

// get new ops_id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$ops_id = $row[0];

// add create for cat
// get category type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='cat'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$this->db->query($query);

?>
<#1327>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1328>
CREATE TABLE IF NOT EXISTS `container_reference` (
  `obj_id` int(11) NOT NULL,
  `target_obj_id` int(11) NOT NULL,
  PRIMARY KEY  (`obj_id`,`target_obj_id`),
  KEY `obj_id` (`obj_id`)
) Type=MyISAM;

<#1329>
<?php
// insert link definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
         "VALUES ('typ', 'catr', 'Category Reference Object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$copy_id = $row->ops_id;


// add operation assignment to link object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete, and copy
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','6')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $copy_id . "')";
$this->db->query($query);

// add create operation
$query = "INSERT INTO rbac_operations " .
    "SET operation = 'create_catr', description = 'create category reference'";
$this->db->query($query);

// get new ops_id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$ops_id = $row[0];

// add create for cat
// get category type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='cat'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$this->db->query($query);

// add create for root
// get root type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='root'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','" . $ops_id . "')";
$this->db->query($query);
?>
<#1330>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1331>
<?php
$query = "UPDATE rbac_operations SET class='create' " .
    "WHERE operation = 'create_rcrs'  OR " .
    "operation = 'create_crsr'  OR " .
    "operation = 'create_catr' ";
$ilDB->query($query);
?>

<#1332>
<?php

// Delete deprecated read permission for container references
$query = "SELECT obj_id FROM object_data " .
    "WHERE type = 'typ' AND title = 'catr'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$cat_id = $row[0];

$query = "SELECT obj_id FROM object_data " .
    "WHERE type = 'typ' AND title = 'crsr'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$crs_id = $row[0];

$query = "DELETE FROM rbac_ta WHERE typ_id = " . $ilDB->quote($cat_id) . " " .
    "AND ops_id = 3";
$ilDB->query($query);

$query = "DELETE FROM rbac_ta WHERE typ_id = " . $ilDB->quote($crs_id) . " " .
    "AND ops_id = 3";
$ilDB->query($query);
?>

<#1333>
<?php

// Add template permissions to root node for Author and Co-Author template
$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$copy_id = $row->ops_id;

$query = "SELECT * FROM rbac_operations WHERE operation = 'create_catr'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$create_cat_id = $row->ops_id;

$query = "SELECT * FROM rbac_operations WHERE operation = 'create_crsr'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$create_crs_id = $row->ops_id;

// get author and co-author obj_ids
$query = "SELECT obj_id FROM object_data " .
    "WHERE type = 'rolt' " .
    "AND title = 'Author' " .
    "OR title = 'Co-Author' ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    foreach (array(1,2,4,6,$copy_id) as $ops_id) {
        $query = "DELETE FROM rbac_templates WHERE " .
            "rol_id = " . $ilDB->quote($row->obj_id) . " " .
            "AND type = 'catr' " .
            "AND ops_id = " . $ops_id . " " .
            "AND parent = 8";
        $ilDB->query($query);
    
        $query = "INSERT INTO rbac_templates SET " .
            "rol_id = " . $ilDB->quote($row->obj_id) . ", " .
            "type = 'catr', " .
            "ops_id = " . $ops_id . ", " .
            "parent = 8";
        $ilDB->query($query);
        
        $query = "DELETE FROM rbac_templates WHERE " .
            "rol_id = " . $ilDB->quote($row->obj_id) . " " .
            "AND type = 'crsr' " .
            "AND ops_id = " . $ops_id . " " .
            "AND parent = 8";
        $ilDB->query($query);
    
        $query = "INSERT INTO rbac_templates SET " .
            "rol_id = " . $ilDB->quote($row->obj_id) . ", " .
            "type = 'crsr', " .
            "ops_id = " . $ops_id . ", " .
            "parent = 8";
        $ilDB->query($query);
    }
    foreach (array($create_cat_id,$create_crs_id) as $ops_id) {
        $query = "DELETE FROM rbac_templates WHERE " .
            "rol_id = " . $ilDB->quote($row->obj_id) . " " .
            "AND type = 'cat' " .
            "AND ops_id = " . $ops_id . " " .
            "AND parent = 8";
        $ilDB->query($query);
    
        $query = "INSERT INTO rbac_templates SET " .
            "rol_id = " . $ilDB->quote($row->obj_id) . ", " .
            "type = 'cat', " .
            "ops_id = " . $ops_id . ", " .
            "parent = 8";
        $ilDB->query($query);
    }

    $query = "DELETE FROM rbac_templates WHERE " .
        "rol_id = " . $ilDB->quote($row->obj_id) . " " .
        "AND type = 'root' " .
        "AND ops_id = " . $create_cat_id . " " .
        "AND parent = 8";
    $ilDB->query($query);
    
    $query = "INSERT INTO rbac_templates SET " .
        "rol_id = " . $ilDB->quote($row->obj_id) . ", " .
        "type = 'root', " .
        "ops_id = " . $create_cat_id . ", " .
        "parent = 8";
    $ilDB->query($query);
}
?>
<#1334>
<?php

// register new object type 'tags' for tagging settings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'tags', 'Tagging settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('tags', '__TaggingSettings', 'Tagging Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('" . $row->id . "')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id, SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'tags'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations for tagging settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);

?>
<#1335>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1336>
<?php
// Migration of calendar data
$query = "SELECT * FROM dp_dates ";
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $start = gmdate('Y-m-d H:i:s', $row->begin);
    $end = gmdate('Y-m-d H:i:s', $row->end);
    $changed = gmdate('Y-m-d H:i:s', $row->changed);
    
    
    $query = "INSERT INTO cal_entries " .
        "SET last_update = " . $ilDB->quote($changed) . ", " .
        "title = " . $ilDB->quote($row->shorttext) . ", " .
        "description = " . $ilDB->quote($row->text) . ", " .
        "start = " . $ilDB->quote($start) . ', ' .
        "end = " . $ilDB->quote($end) . " ";
    $ilDB->query($query);
    
    $cal_id = $ilDB->getLastInsertId();
    
    $until = $row->end_rotation;
    switch ($row->rotation) {
        case 1:
            $freq = 'DAILY';
            $int = 1;
            break;
        case 2:
            $freq = 'WEEKLY';
            $int = 1;
            break;
        case 3:
            $freq = 'WEEKLY';
            $int = 2;
            break;
        case 4:
            $freq = 'WEEKLY';
            $int = 4;
            break;
        case 5:
            $freq = 'MONTHLY';
            $int = 1;
            break;
        case 6:
            $freq = 'MONTHLY';
            $int = 6;
            break;
        case 7:
            $freq = 'YEARLY';
            $int = 1;
            break;

        default:
        case 0:
            $freq = '';
            break;

    }
    
    if ($freq) {
        $until = gmdate('Y-m-d H:i:s', $until);
        $query = "INSERT INTO cal_recurrence_rules " .
            "SET cal_id = " . $ilDB->quote($cal_id) . ", " .
            "cal_recurrence = 1, " .
            "freq_type = " . $ilDB->quote($freq) . ", " .
            "intervall = " . $ilDB->quote($int) . ", " .
            "freq_until_date = " . $ilDB->quote($until) . ", " .
            "freq_until_count = 30";
        $ilDB->query($query);
    }
    
    if ($row->group_ID) {
        $query = "SELECT obj_id FROM object_reference " .
            "WHERE ref_id = " . $ilDB->quote($row->group_ID);
        $ref_res = $ilDB->query($query);
        $ref_row = $ref_res->fetchRow();
        $obj_id = $ref_row[0];
        
        // SELECT category id of course/group
        $query = "SELECT * FROM cal_categories WHERE obj_id = " . $ilDB->quote($obj_id);
        $cat_res = $ilDB->query($query);
        $cat_row = $cat_res->fetchRow();
        $cat_id = $cat_row[0];
        
        // INSERT INTO course/group calendar
        $query = "INSERT INTO cal_category_assignments " .
            "SET cal_id = " . $ilDB->quote($cal_id) . ", " .
            "cat_id = " . $ilDB->quote($cat_id);
        $ilDB->query($query);
    } else {
        $user_id = $row->user_ID;
        
        if (isset($user_ids[$user_id])) {
            $cat_id = $user_ids[$user_id];
        } else {
            // This is a personal calendar
            $query = "INSERT INTO cal_categories " .
                "SET obj_id = " . $ilDB->quote($user_id) . ", " .
                "title = 'Personal Calendar', " .
                "color = '#DAE2FF', " .
                "type = 1";
            $ilDB->query($query);
            
            // SELECT category id
            $query = "SELECT cat_id FROM cal_categories " .
                "WHERE obj_id = " . $ilDB->quote($user_id) .
                "AND title = 'Personal Calendar'";
            $cat_res = $ilDB->query($query);
            $cat_row = $cat_res->fetchRow();
            $cat_id = $cat_row[0];
            
            $user_ids[$user_id] = $cat_id;
        }
        
        // INSERT INTO personal calendar
        $query = "INSERT INTO cal_category_assignments " .
            "SET cal_id = " . $ilDB->quote($cal_id) . ", " .
            "cat_id = " . $ilDB->quote($cat_id);
        $ilDB->query($query);
    }
}
?>
<#1337>
DELETE FROM `qpl_question_type` WHERE `type_tag` = 'assFlashApp';

<#1338>
ALTER TABLE `copy_wizard_options` CHANGE `options` `options` LONGTEXT NOT NULL;

<#1339>
<?php

// Adjust delete permission for sessions
global $ilDB;

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'sess'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];
$ops_id = 6;

// Register delete permission for sessions
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
        . "  (" . $ilDB->quote($typ_id) . ",'" . $ops_id . "')";
$ilDB->query($query);

$ops_id = 4;
$query = "SELECT rol_id,obr.ref_id FROM object_data obd " .
    "JOIN object_reference obr ON obd.obj_id = obr.obj_id " .
    "JOIN rbac_pa  ON obr.ref_id = rbac_pa.ref_id " .
    "WHERE type = 'sess' " .
    "AND (ops_id LIKE " . $ilDB->quote("%i:" . $ops_id . "%") . " " .
    "OR ops_id LIKE" . $ilDB->quote("%:\"" . $ops_id . "\";%") . ") ";

$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $ref_id = $row->ref_id;
    $rol_id = $row->rol_id;
    
    $query = "SELECT * FROM rbac_pa WHERE rol_id = " . $ilDB->quote($rol_id) . ' ' .
        "AND ref_id = " . $ilDB->quote($ref_id);
    $pa_res = $ilDB->query($query);
    while ($pa_row = $pa_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $ops = unserialize($pa_row->ops_id);
        
        if (!in_array(6, $ops)) {
            $ops[] = 6;
            
            $query = "UPDATE rbac_pa SET " .
                "ops_id = " . $ilDB->quote($ops) . ' ' .
                "WHERE rol_id = " . $ilDB->quote($rol_id) . ' ' .
                "AND ref_id = " . $ilDB->quote($ref_id);
            $ilDB->query($query);
        }
    }
}
?>

<#1340>
ALTER TABLE `read_event`
CHANGE `ts` `last_access` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD `first_access` TIMESTAMP NOT NULL;

<#1341>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1342>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1343>
<?php

// Adjust copy permission for sessions
global $ilDB;

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'sess'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$copy_id = $row->ops_id;


$ops_id = 4;
$query = "SELECT rol_id,obr.ref_id FROM object_data obd " .
    "JOIN object_reference obr ON obd.obj_id = obr.obj_id " .
    "JOIN rbac_pa  ON obr.ref_id = rbac_pa.ref_id " .
    "WHERE type = 'sess' " .
    "AND (ops_id LIKE " . $ilDB->quote("%i:" . $ops_id . "%") . " " .
    "OR ops_id LIKE" . $ilDB->quote("%:\"" . $ops_id . "\";%") . ") ";

$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $ref_id = $row->ref_id;
    $rol_id = $row->rol_id;
    
    $query = "SELECT * FROM rbac_pa WHERE rol_id = " . $ilDB->quote($rol_id) . ' ' .
        "AND ref_id = " . $ilDB->quote($ref_id);
    $pa_res = $ilDB->query($query);
    while ($pa_row = $pa_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $ops = unserialize($pa_row->ops_id);
        
        if (!in_array($copy_id, $ops)) {
            $ops[] = $copy_id;
            
            $query = "UPDATE rbac_pa SET " .
                "ops_id = " . $ilDB->quote($ops) . ' ' .
                "WHERE rol_id = " . $ilDB->quote($rol_id) . ' ' .
                "AND ref_id = " . $ilDB->quote($ref_id);
            $ilDB->query($query);
        }
    }
}
?>

<#1344>
<?php
// Adjust role template permission for sessions

$query = "SELECT * FROM rbac_templates " .
    "WHERE type = 'sess' " .
    "AND ops_id = " . $ilDB->quote(4);

$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "DELETE FROM rbac_templates " .
        "WHERE rol_id = " . $ilDB->quote($row->rol_id) . " AND " .
        "type = 'sess' AND " .
        "ops_id = " . $ilDB->quote(6) . " AND " .
        "parent = " . $ilDB->quote($row->parent) . " ";
    $ilDB->query($query);
    
    $query = "INSERT INTO rbac_templates " .
        "SET rol_id = " . $ilDB->quote($row->rol_id) . ", " .
        "type = 'sess', " .
        "ops_id = " . $ilDB->quote(6) . ", " .
        "parent = " . $ilDB->quote($row->parent) . " ";
    $ilDB->query($query);
}
?>

<#1345>
<?php

$query = "SELECT * FROM rbac_operations WHERE operation = 'create_sess'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$create_id = $row->ops_id;


$query = "SELECT * FROM rbac_templates " .
    "WHERE type = 'crs' " .
    "AND ops_id = " . $ilDB->quote(4);

$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "DELETE FROM rbac_templates " .
        "WHERE rol_id = " . $ilDB->quote($row->rol_id) . " AND " .
        "type = 'crs' AND " .
        "ops_id = " . $ilDB->quote($create_id) . " AND " .
        "parent = " . $ilDB->quote($row->parent) . " ";
    $ilDB->query($query);
    
    $query = "INSERT INTO rbac_templates " .
        "SET rol_id = " . $ilDB->quote($row->rol_id) . ", " .
        "type = 'crs', " .
        "ops_id = " . $ilDB->quote($create_id) . ", " .
        "parent = " . $ilDB->quote($row->parent) . " ";
    $ilDB->query($query);
}
?>

<#1346>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1347>
<?php

// Adjust create session permission for courses
global $ilDB;


$query = "SELECT * FROM rbac_operations WHERE operation = 'create_sess'";
$res = $ilDB->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
$sess_id = $row->ops_id;


$ops_id = 4;
$query = "SELECT rol_id,obr.ref_id FROM object_data obd " .
    "JOIN object_reference obr ON obd.obj_id = obr.obj_id " .
    "JOIN rbac_pa  ON obr.ref_id = rbac_pa.ref_id " .
    "WHERE type = 'crs' " .
    "AND (ops_id LIKE " . $ilDB->quote("%i:" . $ops_id . "%") . " " .
    "OR ops_id LIKE" . $ilDB->quote("%:\"" . $ops_id . "\";%") . ") ";

$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $ref_id = $row->ref_id;
    $rol_id = $row->rol_id;
    
    $query = "SELECT * FROM rbac_pa WHERE rol_id = " . $ilDB->quote($rol_id) . ' ' .
        "AND ref_id = " . $ilDB->quote($ref_id);
    $pa_res = $ilDB->query($query);
    while ($pa_row = $pa_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $ops = unserialize($pa_row->ops_id);
        
        if (!in_array($copy_id, $ops)) {
            $ops[] = $sess_id;
            
            $query = "UPDATE rbac_pa SET " .
                "ops_id = " . $ilDB->quote($ops) . ' ' .
                "WHERE rol_id = " . $ilDB->quote($rol_id) . ' ' .
                "AND ref_id = " . $ilDB->quote($ref_id);
            $ilDB->query($query);
        }
    }
}
?>
<#1348>
UPDATE int_link SET source_type = 'wpg:pg' WHERE source_type = 'wpg';
<#1349>
DELETE FROM il_request_token WHERE user_id = 0;
<#1350>
ALTER TABLE mob_usage ADD COLUMN usage_hist_nr INT NOT NULL DEFAULT 0;
<#1351>
ALTER TABLE mob_usage DROP PRIMARY KEY;
<#1352>
ALTER TABLE mob_usage ADD PRIMARY KEY (id, usage_type, usage_id, usage_hist_nr);
<#1353>
<?php
if (!$ilDB->tableColumnExists("tst_tests", "customstyle")) {
    $query = "ALTER TABLE tst_tests ADD COLUMN customstyle VARCHAR(128) NULL";
    $res = $ilDB->query($query);
}
?>
<#1354>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1355>
<?php

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'tax'";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
$typ_id = $row["obj_id"];
if ($typ_id > 0) {
    $q = "DELETE FROM rbac_ta WHERE typ_id = " . $ilDB->quote($typ_id);
    $ilDB->query($q);
    $q = "DELETE FROM object_data WHERE obj_id = " . $ilDB->quote($typ_id);
    $ilDB->query($q);
}

?>
<#1356>
ALTER TABLE `qpl_answer_imagemap` DROP `correctness`;
<#1357>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1358>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1359>
<?php
if (!$ilDB->tableColumnExists("file_usage", "usage_hist_nr")) {
    $query = "ALTER TABLE file_usage ADD COLUMN usage_hist_nr INT NOT NULL DEFAULT 0";
    $res = $ilDB->query($query);
}
?>
<#1360>
ALTER TABLE file_usage DROP PRIMARY KEY;
<#1361>
ALTER TABLE file_usage ADD PRIMARY KEY (id, usage_type, usage_id, usage_hist_nr);
<#1362>
ALTER TABLE `object_translation` CHANGE `title` `title` VARCHAR( 128 ) NOT NULL;
<#1363>
<?php
    $query = "SELECT * FROM qpl_question_type WHERE type_tag = 'assFlashQuestion'";
    $res = $ilDB->query($query);
    if ($res->numRows() == 0) {
        $query = "INSERT INTO qpl_question_type (type_tag, plugin) VALUES ('assFlashQuestion', '0')";
        $ilDB->query($query);
    }
?>
<#1364>
<?php
$q = "CREATE TABLE IF NOT EXISTS qpl_question_flash ( " .
" `question_fi` INT NOT NULL PRIMARY KEY, " .
" `params` TEXT NULL " .
")";
$r = $ilDB->db->query($q);
?>
<#1365>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1366>
<?php
$q = "ALTER TABLE `qpl_question_flash` " .
" ADD `applet` VARCHAR( 150 ) NOT NULL ," .
" ADD `width` INT NOT NULL DEFAULT '550'," .
" ADD `height` INT NOT NULL DEFAULT '400'";
$r = $ilDB->db->query($q);
?>
<#1367>
<?php

$query = "SELECT ta.ops_id from rbac_ta ta " .
    "LEFT JOIN rbac_operations op ON ta.ops_id = op.ops_id " .
    "WHERE op.ops_id IS NULL";

$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = "DELETE FROM rbac_ta WHERE ops_id = " . $ilDB->quote($row->ops_id);
    $ilDB->query($query);
}
?>
<#1368>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1369>
<?php
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'cert'";
$res = $this->db->query($query);
if ($res->numRows() == 0) {
    // register new object type 'cert' for certificate settings
    $query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
            "VALUES ('typ', 'cert', 'Certificate settings', -1, now(), now())";
    $this->db->query($query);

    // ADD NODE IN SYSTEM SETTINGS FOLDER
    // create object data entry
    $query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
            "VALUES ('cert', '__CertificateSettings', 'Certificate Settings', -1, now(), now())";
    $this->db->query($query);

    $query = "SELECT LAST_INSERT_ID() as id";
    $res = $this->db->query($query);
    $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

    // create object reference entry
    $query = "INSERT INTO object_reference (obj_id) VALUES('" . $row->id . "')";
    $res = $this->db->query($query);

    $query = "SELECT LAST_INSERT_ID() as id";
    $res = $this->db->query($query);
    $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

    // put in tree
    $tree = new ilTree(ROOT_FOLDER_ID);
    $tree->insertNode($row->id, SYSTEM_FOLDER_ID);

    $query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
        " AND title = 'cert'";
    $res = $this->db->query($query);
    $row = $res->fetchRow();
    $typ_id = $row[0];

    // add rbac operations for certificate settings
    // 1: edit_permissions, 2: visible, 3: read, 4:write
    $query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
    $this->db->query($query);
    $query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
    $this->db->query($query);
    $query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
    $this->db->query($query);
    $query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
    $this->db->query($query);
}

// migrate matching question solutions (if not migrated from 3.9 -> 3.10)
$query = "SELECT DISTINCT question_fi FROM qpl_answer_matching";
$res = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($res)) {
    $solquery = sprintf(
        "SELECT * FROM tst_solutions WHERE question_fi = %s",
        $ilDB->quote($row['question_fi'])
    );
    $solres = $ilDB->query($solquery);
    while ($solrow = $ilDB->fetchAssoc($solres)) {
        $check = sprintf(
            "SELECT * FROM qpl_answer_matching WHERE question_fi = %s AND aorder = %s",
            $ilDB->quote($row['question_fi']),
            $ilDB->quote($solrow['value1'])
        );
        $checkres = $ilDB->query($check);
        if ($checkres->numRows() == 1) {
            $checkrow = $ilDB->fetchAssoc($checkres);
            $update = sprintf(
                "UPDATE tst_solutions SET value1 = %s WHERE solution_id = %s",
                $ilDB->quote($checkrow['answertext']),
                $ilDB->quote($solrow['solution_id'])
            );
            $ilDB->query($update);
        }
    }
}
?>
<#1370>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1371>
<?php
// add 'manual' field in tst_test_result table to indicate manual scoring
if (!$ilDB->tableColumnExists("tst_test_result", "manual")) {
    $query = "ALTER TABLE `tst_test_result` ADD `manual` TINYINT NOT NULL DEFAULT '0'";
    $res = $ilDB->query($query);
}
?>
<#1372>
<?php
// register new object type 'lrss' for learning resource settings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('typ', 'lrss', 'Learning resources settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) " .
        "VALUES ('lrss', '__LearningResourcesSettings', 'Learning Resources Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('" . $row->id . "')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id, SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' " .
    " AND title = 'lrss'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('" . $typ_id . "','4')";
$this->db->query($query);

?>
<#1373>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1374>
<?php
    // add activation start/end fields
    $ilDB->alterTable(
        "lm_data",
        array("add" => array(
            "activation_start" => array(
                "type" => "timestamp", "default" => "0000-00-00 00:00:00", "notnull" => true),
            "activation_end" => array(
                "type" => "timestamp", "default" => "0000-00-00 00:00:00", "notnull" => true)
            )
        )
    );
?>
<#1375>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1376>
 CREATE TABLE shib_role_assignment (
rule_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
role_id INT( 11 ) NOT NULL ,
name CHAR( 255 ) NOT NULL ,
value CHAR( 255 ) NOT NULL ,
plugin TINYINT( 1 ) NOT NULL ,
add_on_update TINYINT( 1 ) NOT NULL ,
remove_on_update TINYINT( 1 ) NOT NULL
) Type = MYISAM;
<#1377>
<?php
    // add activation start/end fields
    $ilDB->alterTable(
        "lm_data",
        array("remove" => array("activation_start" => array(), "activation_end" => array())
            )
    );
?>
<#1378>
<?php
    // add activation start/end fields
    if (!$ilDB->tableColumnExists("page_object", "activation_start")) {
        $ilDB->alterTable(
            "page_object",
            array("add" => array(
                "activation_start" => array(
                    "type" => "timestamp", "default" => "0000-00-00 00:00:00", "notnull" => true),
                "activation_end" => array(
                    "type" => "timestamp", "default" => "0000-00-00 00:00:00", "notnull" => true)
                )
            )
        );
    }
?>
<#1379>
<?php
    // step move to 1380
?>
<#1380>
<?php
    if (!$ilDB->tableColumnExists("page_object", "active")) {
        $ilDB->alterTable(
            "page_object",
            array("add" => array(
                "active" => array(
                    "type" => "boolean", "default" => true, "notnull" => true)
                )
            )
        );
            
        $st = $ilDB->prepare("SELECT * FROM lm_data WHERE type = ?", array("text"));
        $res = $ilDB->execute($st, array("pg"));
    
        while ($rec = $ilDB->fetchAssoc($res)) {
            $st2 = $ilDB->prepareManip(
                "UPDATE page_object SET active = ? WHERE " .
                "page_id = ? AND (parent_type = ? OR parent_type = ?) AND parent_id = ?",
                array("boolean", "integer", "text", "text", "integer")
            );
            $ilDB->execute($st2, array(($rec["active"] != "n"), $rec["obj_id"], "lm", "dbk", $rec["lm_id"]));
        }
    }
?>
<#1381>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1382>
ALTER TABLE `shib_role_assignment` ADD `plugin_id` INT( 3 ) NOT NULL AFTER `plugin` ;

<#1383>
<?php
    if (!$ilDB->tableExists("style_char")) {
        $q = "CREATE TABLE `style_char` (
			 `style_id` int(11) NOT NULL,
			 `type` varchar(30) NOT NULL default '',
			 `characteristic` varchar(30) NOT NULL default '',
			  INDEX style_id (style_id),
			  PRIMARY KEY  (`style_id`, `type`, `characteristic`)
			 ) ENGINE=MyISAM;";
        $ilDB->query($q);
    }
?>
<#1384>
<?php
    if (!$ilDB->tableColumnExists("style_parameter", "type")) {
        $q = "ALTER TABLE `style_parameter` ADD COLUMN `type` varchar(30) NOT NULL default ''";
        $ilDB->query($q);
    }
    
    // bugfix for bug 5361
    $set = $ilDB->query("SELECT * FROM style_parameter WHERE class = 'LMNavigation' AND tag = 'td'");
    while ($rec = $ilDB->fetchAssoc($set)) {
        $set2 = $ilDB->query("SELECT * FROM style_parameter WHERE class = 'LMNavigation' AND tag = 'div' " .
            "AND parameter = '" . $rec["parameter"] . "'");
        if ($rec2 = $ilDB->fetchAssoc($set2)) {
            $ilDB->query("DELETE FROM style_parameter WHERE id = " . $rec["id"]);
        } else {
            $ilDB->query("UPDATE style_parameter SET tag = 'div' WHERE id = " . $rec["id"]);
        }
    }
    
?>
<#1385>
<?php
    $setting = new ilSetting();
    $se_db = (int) $setting->get("se_db");

    if ($se_db <= 26) {
        include_once("./Services/Migration/DBUpdate_1385/classes/class.ilStyleMigration.php");
        ilStyleMigration::addMissingStyleCharacteristics();
    }
?>
<#1386>
<?php
    $setting = new ilSetting();
    $unirstep = (int) $setting->get('unir_db');
    
    if ($unirstep <= 6) {
        $setting = new ilSetting();
        $se_db = (int) $setting->get("se_db");

        if ($se_db <= 31) {
            $q = "UPDATE `style_char` SET type = 'media' WHERE `characteristic` = 'Media' OR `characteristic` = 'MediaCaption'";
            $ilDB->query($q);

            $q = "UPDATE `style_parameter` SET type = 'media' WHERE `class` = 'Media' OR `class` = 'MediaCaption'";
            $ilDB->query($q);

            $q = "UPDATE `style_parameter` SET type = 'media_caption' WHERE `class` = 'MediaCaption'";
            $ilDB->query($q);
            $q = "UPDATE `style_char` SET type = 'media_caption' WHERE `characteristic` = 'MediaCaption'";
            $ilDB->query($q);

            $q = "UPDATE `style_parameter` SET tag = 'div' WHERE `class` = 'MediaCaption'";
            $ilDB->query($q);
            $q = "UPDATE `style_char` SET type = 'media_caption' WHERE `characteristic` = 'MediaCaption'";
            $ilDB->query($q);
            $q = "UPDATE `style_char` SET type = 'media_cont', characteristic = 'MediaContainer' WHERE `characteristic` = 'Media'";
            $ilDB->query($q);
            $q = "UPDATE `style_parameter` SET type = 'media_cont', class = 'MediaContainer' WHERE `class` = 'Media'";
            $ilDB->query($q);

            $q = "UPDATE `style_char` SET type = 'page_fn' WHERE `characteristic` = 'Footnote'";
            $ilDB->query($q);
            $q = "UPDATE `style_char` SET type = 'page_nav' WHERE `characteristic` = 'LMNavigation'";
            $ilDB->query($q);
            $q = "UPDATE `style_char` SET type = 'page_title' WHERE `characteristic` = 'PageTitle'";
            $ilDB->query($q);
            $q = "UPDATE `style_parameter` SET type = 'page_fn' WHERE `class` = 'Footnote'";
            $ilDB->query($q);
            $q = "UPDATE `style_parameter` SET type = 'page_nav' WHERE `class` = 'LMNavigation'";
            $ilDB->query($q);
            $q = "UPDATE `style_parameter` SET type = 'page_title' WHERE `class` = 'PageTitle'";
            $ilDB->query($q);

            $q = "UPDATE `style_char` SET type = 'page_cont', characteristic = 'PageContainer' WHERE `characteristic` = 'Page'";
            $ilDB->query($q);
            $q = "UPDATE `style_parameter` SET tag = 'table', type = 'page_cont', class = 'PageContainer' WHERE `class` = 'Page'";
            $ilDB->query($q);

            $q = "UPDATE `style_char` SET type = 'sco_title' WHERE `characteristic` = 'Title' AND type = 'sco'";
            $ilDB->query($q);
            $q = "UPDATE `style_parameter` SET type = 'sco_title' WHERE `class` = 'Title' AND type = 'sco'";
            $ilDB->query($q);
            $q = "UPDATE `style_char` SET type = 'sco_desc' WHERE `characteristic` = 'Description' AND type = 'sco'";
            $ilDB->query($q);
            $q = "UPDATE `style_parameter` SET type = 'sco_desc' WHERE `class` = 'Description' AND type = 'sco'";
            $ilDB->query($q);
            $q = "UPDATE `style_char` SET type = 'sco_keyw' WHERE `characteristic` = 'Keywords' AND type = 'sco'";
            $ilDB->query($q);
            $q = "UPDATE `style_parameter` SET type = 'sco_keyw' WHERE `class` = 'Keywords' AND type = 'sco'";
            $ilDB->query($q);
            $q = "UPDATE `style_char` SET type = 'sco_obj' WHERE `characteristic` = 'Objective' AND type = 'sco'";
            $ilDB->query($q);
            $q = "UPDATE `style_parameter` SET type = 'sco_obj' WHERE `class` = 'Objective' AND type = 'sco'";
            $ilDB->query($q);
        }
    }
?>
<#1387>
<?php
$setting = new ilSetting();
$se_db = (int) $setting->get("se_db");

if ($se_db <= 38) {

    // force rewriting of page container style
    $q = "DELETE FROM `style_char` WHERE type = 'page_cont'";
    $ilDB->query($q);
    $q = "DELETE FROM `style_parameter` WHERE type = 'page_cont'";
    $ilDB->query($q);

    include_once("./Services/Migration/DBUpdate_1385/classes/class.ilStyleMigration.php");
    ilStyleMigration::_addMissingStyleClassesToAllStyles();
}
?>
<#1388>
UPDATE `style_data` SET `uptodate` = 0;
<#1389>
<?php
    $setting = new ilSetting();
    $unirstep = (int) $setting->get('unir_db');
    
    if ($unirstep <= 8) {
        $setting = new ilSetting();
        $se_db = (int) $setting->get("se_db");

        if ($se_db <= 39) {
            $q = "UPDATE `style_char` SET characteristic = 'TextInput' WHERE type = 'qinput'";
            $ilDB->query($q);
            $q = "UPDATE `style_parameter` SET class = 'TextInput' WHERE type = 'qinput'";
            $ilDB->query($q);

            // add LongTextInput
            $sts = $ilDB->prepare("SELECT * FROM object_data WHERE type = 'sty'");
            $sets = $ilDB->execute($sts);

            while ($recs = $ilDB->fetchAssoc($sets)) {
                $id = $recs["obj_id"];

                $st = $ilDB->prepare(
                    "SELECT * FROM style_char WHERE type = ? AND style_id = ?",
                    array("text", "integer")
                );
                $set = $ilDB->execute($st, array("qlinput", $id));
                if (!($rec = $ilDB->fetchAssoc($set))) {
                    $q = "INSERT INTO `style_char` (style_id, type, characteristic) VALUES " .
                        "(" . $ilDB->quote($id) . "," . $ilDB->quote("qlinput") . "," . $ilDB->quote("LongTextInput") . ")";
                    $ilDB->query($q);
                }
            }
        }
    }
?>
<#1390>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1391>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1392>
<?php
    if (!$ilDB->tableExists("page_anchor")) {
        $ilDB->createTable(
            "page_anchor",
            array(
                "page_parent_type" => array(
                    "type" => "text", "length" => 10, "default" => "", "notnull" => true),
                "page_id" => array(
                    "type" => "integer", "length" => 4, "default" => 0, "notnull" => true),
                "anchor_name" => array(
                    "type" => "text", "length" => 120, "default" => "", "notnull" => true)
                )
        );
        $ilDB->addPrimaryKey("page_anchor", array("page_parent_type", "page_id", "anchor_name"));
    }
?>
<#1393>
<?php
if (!$ilDB->tableColumnExists("usr_data", "im_jabber")) {
    $ilDB->alterTable(
        "usr_data",
        array("add" => array(
            "im_jabber" => array(
                "type" => "text", "length" => 40, "fixed" => false)
            )
        )
    );
}
if (!$ilDB->tableColumnExists("usr_data", "im_voip")) {
    $ilDB->alterTable(
        "usr_data",
        array("add" => array(
            "im_voip" => array(
                "type" => "text", "length" => 40, "fixed" => false)
            )
        )
    );
}
?>
<#1394>
<?php
if (!$ilDB->tableColumnExists("qpl_suggested_solutions", "type")) {
    $ilDB->alterTable(
        "qpl_suggested_solutions",
        array("add" => array(
            "type" => array(
                "type" => "text", "length" => 32, "notnull" => true),
            "value" => array(
                "type" => "text", "notnull" => false)
            )
        )
    );
}
?>
<#1395>
<?php
$statement = $ilDB->prepare("SELECT * FROM qpl_suggested_solutions");
$result = $ilDB->execute($statement);
if ($result->numRows() > 0) {
    while ($data = $ilDB->fetchAssoc($result)) {
        if (strlen($data["tpye"]) == 0) {
            if (preg_match("/il_+(\\w+)_+\\d+/", $data["internal_link"], $matches)) {
                $updatestatement = $ilDB->prepareManip(
                    "UPDATE qpl_suggested_solutions SET type = ? WHERE suggested_solution_id = ?",
                    array("text", "text")
                );
                $affectedRows = $ilDB->execute($updatestatement, array($matches[1], $data["suggested_solution_id"]));
            }
        }
    }
}
?>
<#1396>
<?php

if (!$ilDB->tableColumnExists('usr_search', 'query')) {
    $ilDB->alterTable('usr_search', array('add' => array('query' => array('type' => 'text',
                                                                        'notnull' => false))));
}
?>
<#1397>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1398>
<?php
if (!$ilDB->tableColumnExists("qpl_question_matching", "thumb_geometry")) {
    $ilDB->alterTable('qpl_question_matching', array('add' => array('thumb_geometry' => array('type' => 'integer','notnull' => true, "default" => 100))));
}
?>
<#1399>
<?php
if (!$ilDB->tableColumnExists("qpl_question_matching", "element_height")) {
    $ilDB->alterTable('qpl_question_matching', array('add' => array('element_height' => array('type' => 'integer','notnull' => false))));
}
?>
<#1400>
<?php
if (!$ilDB->tableColumnExists('usr_search', 'root')) {
    $ilDB->alterTable('usr_search', array('add' => array('root' => array('type' => 'integer',
                                                                        'notnull' => false,
                                                                        'default' => ROOT_FOLDER_ID))));
}
?>
<#1401>
<?php
    if (!$ilDB->tableExists("loginname_history")) {
        $ilDB->createTable(
            "loginname_history",
            array(
                "usr_id" => array("type" => "integer", "length" => 4, "notnull" => true),
                "login" => array("type" => "text", "length" => 80, "fixed" => false, "notnull" => true),
                "date" => array("type" => "integer", "length" => 4, "notnull" => true)
            ),
            false,
            true
        );
        $ilDB->addPrimaryKey("loginname_history", array("usr_id", "login", "date"));
    }
?>
<#1402>
<?php
$ilDB->query("ALTER TABLE qpl_answer_cloze MODIFY `shuffle` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1403>
<?php
$ilDB->query("ALTER TABLE qpl_answer_cloze MODIFY `cloze_type` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1404>
<?php
$ilDB->query("ALTER TABLE qpl_answer_cloze MODIFY `gap_id` SMALLINT NOT NULL DEFAULT 0");
?>
<#1405>
<?php
$ilDB->query("ALTER TABLE qpl_answer_cloze MODIFY `aorder` SMALLINT NOT NULL DEFAULT 0");
?>
<#1406>
<?php
$ilDB->query("ALTER TABLE qpl_answer_cloze MODIFY `question_fi` INT NOT NULL DEFAULT 0");
?>
<#1407>
<?php
if ($ilDB->tableColumnExists('qpl_answer_cloze', 'name')) {
    $ilDB->query("ALTER TABLE qpl_answer_cloze DROP `name`");
}
?>
<#1408>
<?php
if ($ilDB->tableColumnExists('qpl_answer_cloze', 'correctness')) {
    $ilDB->query("ALTER TABLE qpl_answer_cloze DROP `correctness`");
}
?>
<#1409>
<?php
$ilDB->query("ALTER TABLE qpl_answer_imagemap MODIFY `question_fi` INT NOT NULL DEFAULT 0");
?>
<#1410>
<?php
$ilDB->query("ALTER TABLE qpl_answer_imagemap MODIFY `aorder` SMALLINT NOT NULL DEFAULT 0");
?>
<#1411>
<?php
$ilDB->query("ALTER TABLE qpl_answer_matching MODIFY `question_fi` INT NOT NULL DEFAULT 0");
?>
<#1412>
<?php
if ($ilDB->tableColumnExists('qpl_answer_matching', 'answertext')) {
    $ilDB->query("ALTER TABLE qpl_answer_matching CHANGE `answertext` `term_fi` INT NOT NULL DEFAULT  0");
}
?>
<#1413>
<?php
if ($ilDB->tableColumnExists('qpl_answer_matching', 'aorder')) {
    $ilDB->query("ALTER TABLE qpl_answer_matching DROP `aorder`");
}
?>
<#1414>
<?php
if ($ilDB->tableColumnExists('qpl_answer_matching', 'matching_order')) {
    $ilDB->query("ALTER TABLE qpl_answer_matching MODIFY `matching_order` INT NOT NULL DEFAULT 0");
}
?>
<#1415>
<?php
$ilDB->query("ALTER TABLE qpl_answer_matching ADD INDEX `term_fi` ( `term_fi` )");
?>
<#1416>
<?php
$ilDB->query("ALTER TABLE qpl_answer_multiplechoice MODIFY `question_fi` INT NOT NULL DEFAULT 0");
?>
<#1417>
<?php
$ilDB->query("ALTER TABLE qpl_answer_multiplechoice MODIFY `aorder` SMALLINT NOT NULL DEFAULT 0");
?>
<#1418>
<?php
$ilDB->query("ALTER TABLE qpl_answer_ordering MODIFY `question_fi` INT NOT NULL DEFAULT 0");
?>
<#1419>
<?php
$ilDB->query("ALTER TABLE qpl_answer_ordering MODIFY `aorder` SMALLINT NOT NULL DEFAULT 0");
?>
<#1420>
<?php
$ilDB->query("ALTER TABLE qpl_answer_ordering MODIFY `solution_order` SMALLINT NOT NULL DEFAULT 0");
?>
<#1421>
<?php
$ilDB->query("ALTER TABLE qpl_answer_singlechoice MODIFY `question_fi` INT NOT NULL DEFAULT 0");
?>
<#1422>
<?php
$ilDB->query("ALTER TABLE qpl_answer_singlechoice MODIFY `aorder` SMALLINT NOT NULL DEFAULT 0");
?>
<#1423>
<?php
$ilDB->query("ALTER TABLE qpl_answer_textsubset MODIFY `question_fi` INT NOT NULL DEFAULT 0");
?>
<#1424>
<?php
$ilDB->query("ALTER TABLE qpl_answer_textsubset MODIFY `aorder` SMALLINT NOT NULL DEFAULT 0");
?>
<#1425>
<?php
$ilDB->query("ALTER TABLE qpl_feedback_generic MODIFY `correctness` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1426>
<?php
$ilDB->query("ALTER TABLE qpl_questionpool MODIFY `online` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1427>
<?php
$ilDB->query("ALTER TABLE qpl_questions MODIFY `complete` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1428>
<?php
$ilDB->query("ALTER TABLE qpl_questions MODIFY `question_type_fi` INT NOT NULL DEFAULT 0");
?>
<#1429>
<?php
$ilDB->query("ALTER TABLE qpl_questions MODIFY `obj_fi` INT NOT NULL DEFAULT 0");
?>
<#1430>
<?php
$ilDB->query("ALTER TABLE qpl_questions ADD INDEX `obj_fi` ( `obj_fi` )");
?>
<#1431>
<?php
$ilDB->query("ALTER TABLE qpl_questions DROP INDEX `title_desc`");
?>
<#1432>
<?php
$ilDB->query("ALTER TABLE qpl_questions ADD INDEX `title` ( `title` )");
?>
<#1433>
<?php
$ilDB->query("ALTER TABLE qpl_question_cloze MODIFY `identical_scoring` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1434>
<?php
$ilDB->query("ALTER TABLE qpl_question_cloze MODIFY `textgap_rating` VARCHAR(2) NULL");
?>
<#1435>
<?php
$ilDB->query("ALTER TABLE qpl_question_essay MODIFY `textgap_rating` VARCHAR(2) NULL");
?>
<#1436>
<?php
if ($ilDB->tableExists('qpl_question_flashapp')) {
    $ilDB->dropTable('qpl_question_flashapp');
}
?>
<#1437>
<?php
$ilDB->query("ALTER TABLE qpl_question_matching MODIFY `shuffle` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1438>
<?php
$ilDB->query("ALTER TABLE qpl_question_matching MODIFY `matching_type` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1439>
<?php
$ilDB->query("ALTER TABLE qpl_question_multiplechoice MODIFY `shuffle` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1440>
<?php
$ilDB->query("ALTER TABLE qpl_question_ordering MODIFY `ordering_type` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1441>
<?php
$ilDB->query("ALTER TABLE qpl_question_singlechoice MODIFY `shuffle` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1442>
<?php
$ilDB->query("ALTER TABLE qpl_question_textsubset MODIFY `textgap_rating` VARCHAR(2) NULL");
?>
<#1443>
<?php
$ilDB->query("ALTER TABLE qpl_question_type MODIFY `type_tag` VARCHAR(35) NOT NULL");
?>
<#1444>
<?php
$ilDB->query("ALTER TABLE qpl_question_type MODIFY `question_type_id` INT NOT NULL");
?>
<#1445>
<?php
$ilDB->query("ALTER TABLE tst_active MODIFY `user_fi` INT NOT NULL DEFAULT 0");
?>
<#1446>
<?php
$ilDB->query("ALTER TABLE tst_active MODIFY `test_fi` INT NOT NULL DEFAULT 0");
?>
<#1447>
<?php
$ilDB->query("ALTER TABLE tst_active MODIFY `submitted` TINYINT NOT NULL DEFAULT 0");
?>
<#1448>
<?php
$ilDB->query("ALTER TABLE tst_active_qst_sol_settings MODIFY `question_fi` INT NOT NULL DEFAULT 0");
?>
<#1449>
<?php
$ilDB->query("ALTER TABLE tst_active_qst_sol_settings MODIFY `solved` TINYINT NOT NULL DEFAULT 0");
?>
<#1450>
<?php
if ($ilDB->tableExists('tst_eval_settings')) {
    $ilDB->dropTable('tst_eval_settings');
}
?>
<#1451>
<?php
$ilDB->query("ALTER TABLE tst_mark MODIFY `passed` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1452>
<?php
$ilDB->query("ALTER TABLE tst_mark MODIFY `test_fi` INT NOT NULL DEFAULT 0");
?>
<#1453>
<?php
$ilDB->query("ALTER TABLE tst_solutions MODIFY `question_fi` INT NOT NULL DEFAULT 0");
?>
<#1454>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `instant_verification` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1455>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `answer_feedback` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1456>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `answer_feedback_points` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1457>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `fixed_participants` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1458>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `show_cancel` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1459>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `anonymity` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1460>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `use_previous_answers` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1461>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `title_output` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1462>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `enable_processing_time` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1463>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `shuffle_questions` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1464>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `ects_output` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1465>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `complete` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1466>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `random_test` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1467>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `count_system` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1468>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `mc_scoring` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1469>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `score_cutting` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1470>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `pass_scoring` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1471>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `show_question_titles` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1472>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `certificate_visibility` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1473>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `sequence_settings` TINYINT NOT NULL DEFAULT 0");
?>
<#1474>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `score_reporting` TINYINT NOT NULL DEFAULT 0");
?>
<#1475>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `nr_of_tries` SMALLINT NOT NULL DEFAULT 0");
?>
<#1476>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `reset_processing_time` TINYINT NOT NULL DEFAULT 0");
?>
<#1477>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `keep_questions` TINYINT NOT NULL DEFAULT 0");
?>
<#1478>
<?php
$ilDB->query("ALTER TABLE tst_tests MODIFY `show_marker` TINYINT NOT NULL DEFAULT 0");
?>
<#1479>
<?php
$ilDB->query("ALTER TABLE tst_test_pass_result MODIFY `maxpoints` DOUBLE NOT NULL DEFAULT 0");
?>
<#1480>
<?php
$ilDB->query("ALTER TABLE tst_test_question MODIFY `sequence` SMALLINT NOT NULL DEFAULT 0");
?>
<#1481>
<?php
$ilDB->query("ALTER TABLE tst_test_random MODIFY `num_of_q` INT NOT NULL DEFAULT 0");
?>
<#1482>
<?php
$ilDB->query("ALTER TABLE tst_test_random_question MODIFY `sequence` SMALLINT NOT NULL DEFAULT 0");
?>
<#1483>
<?php
$ilDB->query("ALTER TABLE tst_test_result MODIFY `question_fi` INT NOT NULL DEFAULT 0");
?>
<#1484>
<?php
$ilDB->query("ALTER TABLE tst_test_result DROP INDEX `active_fi`");
?>
<#1485>
<?php
$statement = $ilDB->prepare("SHOW INDEXES FROM tst_test_result");
$result = $ilDB->execute($statement);
while ($data = $ilDB->fetchAssoc($result)) {
    if ((strpos($data['Key_name'], 'active_fi_') !== false) || (strpos($data['key_name'], 'active_fi_') !== false)) {
        $statement = $ilDB->prepareManip("ALTER TABLE tst_test_result DROP INDEX " . $data['key_name']);
        $ilDB->execute($statement);
    }
}
?>
<#1486>
<?php
$ilDB->query("ALTER TABLE tst_test_result ADD INDEX `active_fi` ( `active_fi` )");
?>
<#1487>
<?php
$ilDB->query("ALTER TABLE tst_test_result ADD INDEX `question_fi` ( `question_fi` )");
?>
<#1488>
<?php
$ilDB->query("ALTER TABLE tst_test_result ADD INDEX `pass` ( `pass` )");
?>
<#1489>
<?php
$ilDB->query("ALTER TABLE tst_times MODIFY `pass` SMALLINT NOT NULL DEFAULT 0");
?>
<#1490>
<?php
$ilDB->query("ALTER TABLE ass_log MODIFY `test_only` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1491>
<?php
$ilDB->query("ALTER TABLE survey_category MODIFY `defaultvalue` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1492>
<?php
$ilDB->query("ALTER TABLE survey_category MODIFY `neutral` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1493>
<?php
$ilDB->query("ALTER TABLE survey_finished MODIFY `state` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1494>
<?php
$ilDB->query("ALTER TABLE survey_phrase MODIFY `defaultvalue` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1495>
<?php
$ilDB->query("ALTER TABLE survey_question MODIFY `obligatory` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1496>
<?php
$ilDB->query("ALTER TABLE survey_question MODIFY `complete` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1497>
<?php
$ilDB->query("ALTER TABLE survey_question MODIFY `obj_fi` INT NOT NULL DEFAULT 0");
?>
<#1498>
<?php
$ilDB->query("ALTER TABLE survey_questionblock MODIFY `show_questiontext` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1499>
<?php
$ilDB->query("ALTER TABLE survey_questionpool MODIFY `online` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1500>
<?php
$ilDB->query("ALTER TABLE survey_questiontype MODIFY `plugin` TINYINT NOT NULL DEFAULT 0");
?>
<#1501>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `column_separators` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1502>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `row_separators` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1503>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `neutral_column_separator` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1504>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `legend` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1505>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `singleline_row_caption` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1506>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `repeat_column_header` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1507>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `column_header_position` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1508>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `random_rows` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1509>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `column_order` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1510>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `column_images` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1511>
<?php
$ilDB->query("ALTER TABLE survey_question_matrix MODIFY `row_images` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1512>
<?php
$ilDB->query("ALTER TABLE survey_question_metric MODIFY `subtype` VARCHAR(1) NOT NULL DEFAULT '3'");
?>
<#1513>
<?php
$ilDB->query("ALTER TABLE survey_question_nominal MODIFY `subtype` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1514>
<?php
$ilDB->query("ALTER TABLE survey_question_nominal MODIFY `orientation` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1515>
<?php
$ilDB->query("ALTER TABLE survey_question_obligatory MODIFY `obligatory` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1516>
<?php
$ilDB->query("ALTER TABLE survey_question_ordinal MODIFY `orientation` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1517>
<?php
$ilDB->query("ALTER TABLE survey_relation MODIFY `shortname` VARCHAR(2) NOT NULL");
?>
<#1518>
<?php
$ilDB->query("ALTER TABLE survey_survey MODIFY `status` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1519>
<?php
$ilDB->query("ALTER TABLE survey_survey MODIFY `evaluation_access` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1520>
<?php
$ilDB->query("ALTER TABLE survey_survey MODIFY `invitation` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1521>
<?php
$ilDB->query("ALTER TABLE survey_survey MODIFY `invitation_mode` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1522>
<?php
$ilDB->query("ALTER TABLE survey_survey MODIFY `complete` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1523>
<?php
$ilDB->query("ALTER TABLE survey_survey MODIFY `anonymize` VARCHAR(1) NOT NULL DEFAULT '0'");
?>
<#1524>
<?php
$ilDB->query("ALTER TABLE survey_survey MODIFY `show_question_titles` VARCHAR(1) NOT NULL DEFAULT '1'");
?>
<#1525>
<?php
    $query = "SELECT * FROM qpl_question_type WHERE type_tag = 'assOrderingHorizontal'";
    $res = $ilDB->query($query);
    if ($res->numRows() == 0) {
        $query = "SELECT MAX(question_type_id) maxid FROM qpl_question_type";
        $res = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($res);
        $max = $data["maxid"] + 1;

        $statement = $ilDB->prepareManip(
            "INSERT INTO qpl_question_type (question_type_id, type_tag, plugin) VALUES (?, ?, ?)",
            array("integer", "text", "integer")
        );
        $data = array(
            $max,
            'assOrderingHorizontal',
            0
        );
        $affectedRows = $ilDB->execute($statement, $data);
    }
?>
<#1526>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1527>
<?php
    if (!$ilDB->tableExists("qpl_question_orderinghorizontal")) {
        $ilDB->createTable(
            "qpl_question_orderinghorizontal",
            array(
                "question_fi" => array(
                    "type" => "integer", "length" => 4, "notnull" => true),
                "ordertext" => array(
                    "type" => "text", "length" => 2000, "notnull" => true),
                "textsize" => array(
                    "type" => "float", "notnull" => false)
                ),
            false,
            true
        );
        $ilDB->addPrimaryKey("qpl_question_orderinghorizontal", array("question_fi"));
    }
?>
<#1528>
<?php
if (!$ilDB->tableColumnExists("qpl_answer_ordering", "random_id")) {
    $query = "ALTER TABLE qpl_answer_ordering ADD COLUMN random_id INT NOT NULL DEFAULT 0";
    $res = $ilDB->query($query);
}
?>
<#1529>
<?php
$statement = $ilDB->prepare("SELECT * FROM qpl_answer_ordering");
$result = $ilDB->execute($statement);
while ($data = $ilDB->fetchAssoc($result)) {
    $statement = $ilDB->prepareManip("UPDATE qpl_answer_ordering SET random_id = ? WHERE answer_id = ?", array("integer", "integer"));
    $random_number = mt_rand(1, 100000);
    $values = array($random_number, $data["answer_id"]);
    $ilDB->execute($statement, $values);
}
?>
<#1530>
<?php
if (!$ilDB->tableColumnExists("qpl_question_ordering", "thumb_geometry")) {
    $ilDB->query("ALTER TABLE qpl_question_ordering ADD COLUMN thumb_geometry INT NOT NULL DEFAULT 100");
}
?>
<#1531>
<?php
if (!$ilDB->tableColumnExists("qpl_question_ordering", "element_height")) {
    $ilDB->query("ALTER TABLE qpl_question_ordering ADD COLUMN element_height INT NULL");
}
?>
<#1532>
<?php
if (!$ilDB->tableColumnExists("qpl_answer_ordering", "points")) {
    $ilDB->query("ALTER TABLE qpl_answer_ordering DROP points");
}
?>
<#1533>
<?php
if (!$ilDB->tableColumnExists("qpl_answer_ordering", "aorder")) {
    $ilDB->query("ALTER TABLE qpl_answer_ordering DROP aorder");
}
?>
<#1534>
 CREATE TABLE IF NOT EXISTS `ecs_container_mapping` (
`mapping_id` INT NOT NULL ,
`container_id` INT NOT NULL ,
`field_name` VARCHAR( 255 ) NOT NULL ,
`mapping_type` TINYINT NOT NULL ,
`mapping_value` VARCHAR( 255 ) NOT NULL ,
`date_range_start` INT NOT NULL ,
`date_range_end` INT NOT NULL ,
PRIMARY KEY ( `mapping_id` )
) Type = MYISAM;

<#1535>
CREATE TABLE IF NOT EXISTS `search_command_queue` (
  `obj_id` INT NOT NULL,
  `obj_type` char(4) NOT NULL,
  `sub_id` INT NOT NULL,
  `sub_type` char(4) NOT NULL,
  `command` char(16) NOT NULL,
  `last_update` datetime NOT NULL,
  `finished` tinyint NOT NULL,
  PRIMARY KEY  (`obj_id`,`obj_type`,`sub_id`)
) Type=MyISAM;
<#1536>
<?php
    $ilDB->query("CREATE TABLE `abstraction_progress` (
		`table_name` VARCHAR(100),
		`step` INT NOT NULL,
		PRIMARY KEY  (`table_name`,`step`),
		INDEX t(table_name)
		) Type=MyISAM;");
    $ilMySQLAbstraction->performAbstraction("settings");
?>
<#1537>
CREATE TABLE IF NOT EXISTS `abstraction_progress` (
  `table_name` VARCHAR(100),
  `step` INT NOT NULL,
  PRIMARY KEY  (`table_name`,`step`),
  INDEX t(table_name)
) Type=MyISAM;
<#1538>
<?php
    $ilMySQLAbstraction->performAbstraction("usr_session");
?>

<#1539>
DROP TABLE `dp_changed_dates`;

<#1540>
DROP TABLE `dp_dates`;

<#1541>
DROP TABLE `dp_keyword`;

<#1542>
DROP TABLE `dp_keywords`;

<#1543>
DROP TABLE `dp_neg_dates`;

<#1544>
DROP TABLE `dp_properties`;

<#1545>
DROP TABLE `meta_data`;

<#1546>
DROP TABLE `meta_keyword`;

<#1547>
DROP TABLE `meta_technical`;

<#1548>
DROP TABLE `meta_techn_format`;

<#1549>
DROP TABLE `meta_techn_loc`;

<#1550>
<?php
    $ilMySQLAbstraction->performAbstraction("il_object_def");
?>
<#1551>
ALTER TABLE `il_object_subobj` CHANGE `max` `mmax` TINYINT NOT NULL;
<#1552>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1553>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1554>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1555>
<?php
    $ilMySQLAbstraction->performAbstraction("il_object_subobj");
?>
<#1556>
<?php
    $ilMySQLAbstraction->performAbstraction("il_object_group");
?>
<#1557>
<?php
    $ilMySQLAbstraction->performAbstraction("lng_modules");
?>
<#1558>
<?php
    $ilMySQLAbstraction->performAbstraction("lng_data");
?>
<#1559>
<?php
    $ilMySQLAbstraction->performAbstraction("module_class");
?>
<#1560>
<?php
    $ilMySQLAbstraction->performAbstraction("service_class");
?>
<#1561>
<?php
    $ilMySQLAbstraction->performAbstraction("il_component");
?>
<#1562>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1563>
<?php
    $ilMySQLAbstraction->performAbstraction("ctrl_calls");
?>
<#1564>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1565>
<?php
    $ilMySQLAbstraction->performAbstraction("ctrl_classfile");
?>
<#1566>
<?php
    $ilMySQLAbstraction->performAbstraction("ctrl_structure");
?>
<#1567>
<?php
    $ilMySQLAbstraction->performAbstraction("ut_online");
?>
<#1568>
<?php
$query = "SELECT * FROM qpl_question_type WHERE type_tag = 'assFileUpload'";
$res = $ilDB->query($query);
if ($res->numRows() == 0) {
    $query = "SELECT MAX(question_type_id) maxid FROM qpl_question_type";
    $res = $ilDB->query($query);
    $data = $ilDB->fetchAssoc($res);
    $max = $data["maxid"] + 1;
    $statement = $ilDB->prepareManip(
        "INSERT INTO qpl_question_type (question_type_id, type_tag, plugin) VALUES (?, ?, ?)",
        array("integer", "text", "integer")
    );
    $data = array(
        $max,
        'assFileUpload',
        0
    );
    $affectedRows = $ilDB->execute($statement, $data);
}
?>
<#1569>
<?php
    if (!$ilDB->tableExists("qpl_question_fileupload")) {
        $ilDB->createTable(
            "qpl_question_fileupload",
            array(
                "question_fi" => array(
                    "type" => "integer", "length" => 4, "notnull" => true),
                "allowedextensions" => array(
                    "type" => "text", "length" => 255, "notnull" => false),
                "maxsize" => array(
                    "type" => "float", "notnull" => false)
                ),
            false,
            true
        );
        $ilDB->addPrimaryKey("qpl_question_fileupload", array("question_fi"));
    }
?>
<#1570>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1571>
<?php
$query = "SELECT * FROM qpl_question_type WHERE type_tag = 'assOrderingHorizontal'";
$res = $ilDB->query($query);
if ($res->numRows() == 1) {
    $data = $ilDB->fetchAssoc($res);
    if ($data['question_type_id'] == 0) {
        $query = "SELECT MAX(question_type_id) maxid FROM qpl_question_type";
        $res = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($res);
        $max = $data["maxid"] + 1;
        $statement = $ilDB->prepareManip(
            "UPDATE qpl_question_type SET question_type_id = ? WHERE type_tag = ? AND plugin = ?",
            array("integer", "text", "integer")
        );
        $data = array(
            $max,
            'assOrderingHorizontal',
            0
        );
        $affectedRows = $ilDB->execute($statement, $data);
    }
}
?>
<#1572>
<?php
    include_once("./Services/Database/classes/class.ilDBAnalyzer.php");
    $analyzer = new ilDBAnalyzer();
    $pk = $analyzer->getPrimaryKeyInformation("il_object_def");
    $fields = $analyzer->getFieldInformation("il_object_def");
    $ilMySQLAbstraction->performAbstraction("il_object_def", $fields, true, $pk);
?>
<#1573>
<?php
    $ilMySQLAbstraction->replaceEmptyStringsWithNull("il_object_def");
?>
<#1574>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1575>
<?php
    $ilMySQLAbstraction->performAbstraction("il_block_setting");
?>
<#1576>
ALTER TABLE usr_data MODIFY gender CHAR(1) DEFAULT 'm';
<#1577>
ALTER TABLE usr_data MODIFY auth_mode CHAR(10) DEFAULT 'default';
<#1578>
ALTER TABLE `payment_statistic` ADD `street` VARCHAR( 40 ) NULL;
<#1579>
ALTER TABLE `payment_statistic` ADD `po_box` VARCHAR( 40 ) NULL;
<#1580>
ALTER TABLE `payment_statistic` ADD `zipcode` VARCHAR( 40 ) NULL;
<#1581>
ALTER TABLE `payment_statistic` ADD `city` VARCHAR( 40 ) NULL;
<#1582>
ALTER TABLE `payment_statistic` ADD `country` VARCHAR( 40 ) NULL;
<#1583>
<?php
    $ilMySQLAbstraction->performAbstraction("tree");
?>
<#1584>
<?php
$query = "SELECT * FROM qpl_question_type WHERE type_tag = 'assFileUpload'";
$res = $ilDB->query($query);
if ($res->numRows() == 1) {
    $row = $ilDB->fetchAssoc($res);
    $id = $row["question_type_id"];

    $setting = new ilSetting("assessment");
    $types = $setting->get("assessment_manual_scoring");
    $manualtypes = explode(",", $types);
    if (!in_array($id, $manualtypes)) {
        array_push($manualtypes, $id);
        $setting->set("assessment_manual_scoring", implode($manualtypes, ","));
    }
}
?>
<#1585>
<?php
    $ilMySQLAbstraction->performAbstraction('write_event');
?>
<#1586>
<?php
    $ilMySQLAbstraction->performAbstraction("usr_data");
?>
<#1587>
<?php
    $ilMySQLAbstraction->performAbstraction("desktop_item");
?>

<#1588>
<?php

// Create temporary field
$query = "ALTER TABLE `read_event` ADD `last_access2` INT NOT NULL AFTER `last_access` ";
$ilDB->query($query);

// Migrate last_access
$query = "UPDATE read_event SET last_access2 = UNIX_TIMESTAMP(last_access)";
$ilDB->query($query);

// Drop last_access
$query = "ALTER TABLE read_event DROP `last_access`";
$ilDB->query($query);

// Rename last_access2
$query = "ALTER TABLE `read_event` CHANGE `last_access2` `last_access` INT( 11 ) NOT NULL";
$ilDB->query($query);

?> 

<#1589>
<?php
    $ilMySQLAbstraction->performAbstraction('read_event');
?>
<#1590>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1591>
ALTER TABLE rbac_operations MODIFY `class` CHAR(16);

<#1592>
ALTER TABLE `rbac_operations` DROP INDEX `operation`;

<#1593>
ALTER TABLE `rbac_operations` ADD INDEX `operation` ( `operation` );

<#1594>
<?php
    $ilMySQLAbstraction->performAbstraction('rbac_operations');
?>

<#1595>
ALTER TABLE rbac_fa MODIFY `assign` CHAR(1);

<#1596>
ALTER TABLE `rbac_fa` CHANGE `protected` `protected` CHAR( 1 ) NULL DEFAULT 'n'; 

<#1597>
<?php
    $ilMySQLAbstraction->performAbstraction('rbac_fa');
?>
<#1598>
<?php
    $ilMySQLAbstraction->performAbstraction("object_data");
?>
<#1599>
<?php
    $ilMySQLAbstraction->performAbstraction('rbac_ua');
?>
<#1600>
<?php
    $ilMySQLAbstraction->performAbstraction('usr_pref');
?>
<#1601>
<?php
    $ilMySQLAbstraction->performAbstraction('il_custom_block');
?>
<#1602>
<?php
    $ilMySQLAbstraction->performAbstraction('il_request_token');
?>

<#1603>
DROP TABLE IF EXISTS note_data;
<#1604>
ALTER TABLE `note` CHANGE `text` `note_text` MEDIUMTEXT;
<#1605>
<?php
    $ilMySQLAbstraction->performAbstraction('note');
?>
<#1606>
<?php
    $ilMySQLAbstraction->performAbstraction('benchmark');
?>
<#1607>
<?php
    $ilMySQLAbstraction->performAbstraction('bookmark_data');
?>
<#1608>
<?php
    $ilMySQLAbstraction->performAbstraction('bookmark_tree');
?>
<#1609>
<?php
    $ilMySQLAbstraction->performAbstraction('feedback_items');
?>
<#1610>
<?php
    $ilMySQLAbstraction->performAbstraction('feedback_results');
?>
<#1611>
<?php
    $ilMySQLAbstraction->performAbstraction('rbac_ta');
?>
<#1612>
<?php
    $ilMySQLAbstraction->performAbstraction('rbac_templates');
?>
<#1613>
<?php
    $ilMySQLAbstraction->performAbstraction('rbac_pa');
?>
<#1614>
<?php
    $ilMySQLAbstraction->performAbstraction('il_log');
?>
<#1615>
<?php
    $ilMySQLAbstraction->performAbstraction('il_plugin');
?>
<#1616>
<?php
    $ilMySQLAbstraction->performAbstraction('il_pluginslot');
?>
<#1617>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1618>
ALTER TABLE `role_data` CHANGE `auth_mode` `auth_mode` CHAR( 16 ) NOT NULL DEFAULT 'default';

<#1619>
ALTER TABLE `role_data` CHANGE `assign_users` `assign_users` TINYINT( 1 ) NULL DEFAULT '0';

<#1620>
<?php
    $ilMySQLAbstraction->performAbstraction('role_data');
?>

<#1621>
ALTER TABLE content_object MODIFY page_header CHAR(8) DEFAULT 'st_title';
ALTER TABLE content_object MODIFY online CHAR(1) DEFAULT 'n';
ALTER TABLE content_object MODIFY toc_active CHAR(1) DEFAULT 'y';
ALTER TABLE content_object MODIFY lm_menu_active CHAR(1) DEFAULT 'y';
ALTER TABLE content_object MODIFY toc_mode CHAR(8) DEFAULT 'chapters';
ALTER TABLE content_object MODIFY clean_frames CHAR(1) DEFAULT 'n';
ALTER TABLE content_object MODIFY print_view_active CHAR(1) DEFAULT 'y';
ALTER TABLE content_object MODIFY numbering CHAR(1) DEFAULT 'n';
ALTER TABLE content_object MODIFY hist_user_comments CHAR(1) DEFAULT 'n';
ALTER TABLE content_object MODIFY public_access_mode CHAR(8) DEFAULT 'complete';
ALTER TABLE content_object MODIFY downloads_active CHAR(1) DEFAULT 'n';
ALTER TABLE content_object MODIFY downloads_public_active CHAR(1) DEFAULT 'y';
ALTER TABLE content_object MODIFY pub_notes CHAR(1) DEFAULT 'y';
<#1622>
ALTER TABLE content_object MODIFY prevent_glossary_appendix_active CHAR(1) DEFAULT 'n';

<#1623>
<?php
    $ilMySQLAbstraction->performAbstraction('ldap_server_settings');
?>

<#1624>
<?php
    $ilMySQLAbstraction->performAbstraction('content_object');
?>
<#1625>
<?php
    $ilMySQLAbstraction->performAbstraction('dbk_translations');
?>
<#1626>
<?php
    $ilMySQLAbstraction->performAbstraction('object_reference');
?>
<#1627>
<?php
    $ilMySQLAbstraction->performAbstraction('object_description');
?>
<#1628>
<?php
    #$ilMySQLAbstraction->performAbstraction('object_description');
?>
<#1629>
<?php
    $ilMySQLAbstraction->performAbstraction('ldap_attribute_mapping');
?>
<#1630>
<?php
    $ilMySQLAbstraction->performAbstraction('ldap_role_assignments');
?>
<#1631>
<?php
    $ilMySQLAbstraction->performAbstraction('ldap_role_group_mapping');
?>
<#1632>
<?php
    $ilMySQLAbstraction->performAbstraction('conditions');
?>
<#1633>
ALTER TABLE `conditions` CHANGE `id` `condition_id` INT( 11 ) NOT NULL DEFAULT '0';

<#1634>
<?php
    $ilMySQLAbstraction->performAbstraction('link_check');
?>

<#1635>
<?php
    $ilMySQLAbstraction->performAbstraction('link_check_report');
?>

<#1636>
<?php
    $ilMySQLAbstraction->performAbstraction('role_desktop_items');
?>

<#1637>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_archives');
?>

<#1638>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1639>
<?php
$ilDB->query("ALTER TABLE  `survey_questionpool` CHANGE  `online`  `isonline` VARCHAR( 1 ) NOT NULL DEFAULT  '0'");
?>
<#1640>
<?php
$ilDB->query("ALTER TABLE  `survey_answer` CHANGE  `row`  `rowvalue` INT NOT NULL DEFAULT  0");
?>
<#1641>
<?php
$ilDB->query("ALTER TABLE  `qpl_questions` CHANGE  `comment`  `description` TEXT NULL");
?>
<#1642>
<?php
$ilDB->query("ALTER TABLE  `qpl_questionpool` CHANGE  `online`  `isonline` VARCHAR( 1 ) NOT NULL DEFAULT  '0'");
?>
<#1643>
<?php
    $ilDB->renameTableColumn("content_object", "online", "is_online");
?>

<#1644>
<?php
    $ilDB->renameTableColumn("ctrl_classfile", "file", "filename");
?>
<#1645>
ALTER TABLE file_based_lm CHANGE `online` `is_online` CHAR(1) DEFAULT 'n';
<#1646>
ALTER TABLE glossary CHANGE `online` `is_online` CHAR(1) DEFAULT 'n';
<#1647>
<?php
    $ilDB->renameTableColumn("il_block_setting", "user", "user_id");
?>
<#1648>
<?php
    $ilDB->renameTableColumn("il_log", "level", "log_level");
?>
<#1649>
ALTER TABLE il_media_cast_data CHANGE `online` `is_online` TINYINT DEFAULT 0;
<#1650>
ALTER TABLE il_media_cast_data CHANGE `access` `def_access` TINYINT DEFAULT 0;
<#1651>
<?php
    $ilDB->renameTableColumn("il_request_token", "session", "session_id");
?>
<#1652>
ALTER TABLE il_tag CHANGE `offline` `is_offline` TINYINT NOT NULL DEFAULT 0;
<#1653>
ALTER TABLE il_wiki_data CHANGE `online` `is_online` TINYINT DEFAULT 0;
<#1654>
ALTER TABLE page_history CHANGE `user` `user_id` INT;
<#1655>
DROP TABLE IF EXISTS xml_tree;
<#1656>
DROP TABLE IF EXISTS xml_text;
<#1657>
DROP TABLE IF EXISTS xml_pi_target;
<#1658>
DROP TABLE IF EXISTS xml_pi_data;
<#1659>
DROP TABLE IF EXISTS xml_object;
<#1660>
DROP TABLE IF EXISTS xml_node_type;
<#1661>
DROP TABLE IF EXISTS xml_entity_reference;
<#1662>
DROP TABLE IF EXISTS xml_element_namespace;
<#1663>
DROP TABLE IF EXISTS xml_element_name;
<#1664>
DROP TABLE IF EXISTS xml_element_idx;
<#1665>
DROP TABLE IF EXISTS xml_comment;
<#1666>
DROP TABLE IF EXISTS xml_cdata;
<#1667>
DROP TABLE IF EXISTS xml_attribute_value;
<#1668>
DROP TABLE IF EXISTS xml_attribute_namespace;
<#1669>
DROP TABLE IF EXISTS xml_attribute_name;
<#1670>
DROP TABLE IF EXISTS xml_attribute_idx;

<#1671>
<?php
    $ilDB->addPrimaryKey('role_desktop_items', array('role_item_id'));
?>
<#1672>
<?php
    $ilDB->renameTableColumn("content_object", "prevent_glossary_appendix_active", "no_glo_appendix");
?>
<#1673>
ALTER TABLE `payment_settings` ADD `save_customer_address_enabled` TINYINT( 1 ) NOT NULL DEFAULT '0';
<#1674>
RENAME TABLE `addressbook_mailing_lists` TO `addressbook_mlist` ;
<#1675>
RENAME TABLE `addressbook_mailing_lists_assignments` TO `addressbook_mlist_ass` ;
<#1676>
RENAME TABLE `payment_coupons_objects` TO `payment_coupons_obj` ;
<#1677>
RENAME TABLE `payment_coupons_tracking` TO `payment_coupons_track` ;
<#1678>
RENAME TABLE `payment_statistic_coupons` TO `payment_statistic_coup` ;
<#1679>
RENAME TABLE `payment_topics_user_sorting` TO `payment_topic_usr_sort` ;

<#1680>
<?php
    $set = $ilDB->query("SELECT DISTINCT table_name FROM abstraction_progress WHERE step = " .
        $ilDB->quote(80, "integer"));
    while ($rec = $ilDB->fetchAssoc($set)) {
        $ilMySQLAbstraction->fixIndexNames($rec["table_name"]);
    }
?>

<#1681>
<?php
    $ilDB->renameTable('ldap_role_group_mapping', 'ldap_rg_mapping');
?>

<#1682>
<?php
    $ilDB->renameTable('ldap_role_group_mapping_seq', 'ldap_rg_mapping_seq');
?>

<#1683>
RENAME TABLE `crs_defined_field_definitions` TO `crs_f_definitions`;


<#1684>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_f_definitions');
?>
	
<#1685>
<?php
    $ilDB->modifyTableColumn('usr_data', 'hobby', array("type" => "text", "length" => 4000));
?>
<#1686>
<?php
    $set = $ilDB->query("SELECT DISTINCT table_name FROM abstraction_progress WHERE step = " .
        $ilDB->quote(80, "integer"));
    while ($rec = $ilDB->fetchAssoc($set)) {
        $ilMySQLAbstraction->fixClobNotNull($rec["table_name"]);
    }
?>
<#1687>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_file');
?>

<#1688>
<?php
    $ilDB->modifyTableColumn('lng_data', 'value', array("type" => "text", "notnull" => false, "length" => 4000));
?>

<#1689>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_groupings');
?>

<#1690>
ALTER TABLE `crs_items` CHANGE `timing_start` `timing_start` INT( 11 ) NOT NULL DEFAULT 0;

<#1691>
ALTER TABLE `crs_items` CHANGE `timing_end` `timing_end` INT( 11 ) NOT NULL DEFAULT 0;

<#1692>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_items');
?>

<#1693>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_lm_history');
?>
  
<#1694>
<?php
    $set = $ilDB->query("SELECT DISTINCT table_name FROM abstraction_progress WHERE step = " .
        $ilDB->quote(80, "integer"));
    while ($rec = $ilDB->fetchAssoc($set)) {
        $ilMySQLAbstraction->fixDatetimeValues($rec["table_name"]);
        $ilMySQLAbstraction->replaceEmptyDatesWithNull($rec["table_name"]);
    }
?>
<#1695>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_members');
?>
<#1696>
<?php
    $ilDB->modifyTableColumn('usr_data', 'hobby', array("type" => "text", "length" => 4000, "notnull" => false));
?>
<#1697>
<?php
    //$ilDB->modifyTableColumn('write_event','ts', array("type" => "timestamp", "default" => null, "notnull" => false));
?>

<#1698>
RENAME TABLE `user_defined_field_definition` TO `udf_definition`;

<#1699>
ALTER TABLE `udf_definition` CHANGE `field_name` `field_name` CHAR( 255 ) NULL;

<#1700>
<?php
    $ilMySQLAbstraction->performAbstraction('udf_definition');
?>

<#1701>
<?php
    $ilMySQLAbstraction->performAbstraction('exc_data');
?>

<#1702>
<?php
    $ilDB->modifyTableColumn('ldap_rg_mapping', 'mapping_info', array("type" => "text", "length" => 4000, "notnull" => false));
?>

<#1703>
<?php
    $ilDB->modifyTableColumn('ldap_role_assignments', 'dn', array("type" => "text", "length" => 1000, "notnull" => false));
?>
	
<#1704>
<?php
    $ilDB->modifyTableColumn('rbac_pa', 'ops_id', array("type" => "text", "length" => 4000, "notnull" => false));
?>

<#1705>
RENAME TABLE `usr_defined_data` TO `udf_data`;

<#1706>
<?php
    // rename auto generated udf data fields
    $query = "SELECT DISTINCT(field_id) fid FROM udf_definition";
    $res = $ilDB->query($query);
    while ($row = $ilDB->fetchObject($res)) {
        $query = "ALTER TABLE udf_data CHANGE `" . $row->fid . "` `f_" . $row->fid . "` TEXT NULL";
        $ilDB->query($query);
    }
?>

<#1707>
<?php
    $ilMySQLAbstraction->performAbstraction('udf_data');
?>

<#1708>
<?php
    // rename auto generated udf data fields
    $query = "SELECT DISTINCT(field_id) fid FROM udf_definition";
    $res = $ilDB->query($query);
    while ($row = $ilDB->fetchObject($res)) {
        $ilDB->modifyTableColumn('udf_data', 'f_' . $row->fid, array("type" => "clob", "notnull" => false));
    }
?>
<#1709>
<?php
    $ilMySQLAbstraction->performAbstraction('usr_new_account_mail');
?>

<#1710>
<?php
    $ilMySQLAbstraction->performAbstraction('ecs_events');
?>

<#1711>
ALTER TABLE exc_members MODIFY status CHAR(9) DEFAULT 'notgraded';
<#1712>
ALTER TABLE exc_members MODIFY notice VARCHAR(4000);

<#1713>
<?php
    $ilMySQLAbstraction->performAbstraction('exc_members');
?>
<#1714>
<?php
    $ilMySQLAbstraction->performAbstraction('ecs_import');
?>

<#1715>
<?php
    $ilMySQLAbstraction->performAbstraction('ecs_export');
?>
<#1716>
ALTER TABLE exc_returned MODIFY filename VARCHAR(1000);
ALTER TABLE exc_returned MODIFY filetitle VARCHAR(1000);
<#1717>
ALTER TABLE exc_returned CHANGE `timestamp` ts TIMESTAMP;
<#1718>
<?php
    $ilMySQLAbstraction->performAbstraction('exc_returned');
?>
<#1719>
<?php
    $ilMySQLAbstraction->performAbstraction('exc_usr_tutor');
?>
<#1720>
<?php
    $ilMySQLAbstraction->performAbstraction('file_based_lm');
?>
<#1721>
<?php
    $ilMySQLAbstraction->performAbstraction('file_based_lm');
?>
<#1722>
ALTER TABLE glossary MODIFY virtual CHAR(7) DEFAULT 'none';
ALTER TABLE glossary MODIFY glo_menu_active CHAR(1) DEFAULT 'y';
ALTER TABLE glossary MODIFY downloads_active CHAR(1) DEFAULT 'n';
<#1723>
<?php
    $ilMySQLAbstraction->performAbstraction('glossary');
?>

<#1724>
RENAME TABLE `adv_md_field_definition` TO `adv_mdf_definition`;

<#1725>
ALTER TABLE `adv_mdf_definition` CHANGE `title` `title` VARCHAR( 255 ) NULL;
 
<#1726>
ALTER TABLE `adv_mdf_definition` CHANGE `description` `description` VARCHAR( 2000 ) NULL;

<#1727>
ALTER TABLE `adv_mdf_definition` CHANGE `field_values` `field_values` VARCHAR( 4000 ) NULL;

<#1728>
<?php
    $ilMySQLAbstraction->performAbstraction('adv_mdf_definition');
?>

<#1729>
ALTER TABLE `adv_md_record` CHANGE `description` `description` VARCHAR( 4000 ) NULL;
  
<#1730>
<?php
    $ilMySQLAbstraction->performAbstraction('adv_md_record');
?>
  
<#1731>
<?php
    $ilMySQLAbstraction->performAbstraction('adv_md_record_objs');
?>

<#1732>
<?php
    $ilMySQLAbstraction->performAbstraction('adv_md_substitutions');
?>

<#1733>
ALTER TABLE `adv_md_values` CHANGE `value` `value` VARCHAR( 4000 ) NULL;

<#1734>
<?php
    $ilMySQLAbstraction->performAbstraction('adv_md_values');
?>

<#1735>
<?php
    $ilMySQLAbstraction->performAbstraction('glossary_definition');
?>
<#1736>
<?php
    $ilMySQLAbstraction->performAbstraction('glossary_term');
?>
<#1737>
ALTER TABLE `history` MODIFY `info_params` VARCHAR(4000);
<#1738>
<?php
    $ilMySQLAbstraction->performAbstraction('history');
?>

<#1739>
<?php
    $ilDB->modifyTableColumn('write_event', 'ts', array("type" => "timestamp", "notnull" => true, "default" => '1970-01-01 00:00:00'));
?>

<#1740>
<?php
    $ilMySQLAbstraction->performAbstraction('il_external_feed_block');
?>
<#1741>
ALTER TABLE `il_html_block` MODIFY `content` VARCHAR(4000);
<#1742>
<?php
    $ilMySQLAbstraction->performAbstraction('il_html_block');
?>
<#1743>
ALTER TABLE `il_media_cast_data` MODIFY `id` INT NOT NULL;
<#1744>
<?php
    $ilMySQLAbstraction->performAbstraction('il_media_cast_data');
?>

<#1745>
ALTER TABLE `object_translation` CHANGE `description` `description` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1746>
<?php
    $ilMySQLAbstraction->performAbstraction('object_translation');
?>

<#1747>
ALTER TABLE il_news_item MODIFY content_type CHAR(5) DEFAULT 'text';
ALTER TABLE il_news_item MODIFY visibility CHAR(6) DEFAULT 'users';
<#1748>
<?php
    $ilMySQLAbstraction->performAbstraction('il_news_item');
?>
<#1749>
<?php
    $ilMySQLAbstraction->performAbstraction('il_news_read');
?>
<#1750>
<?php
    $ilMySQLAbstraction->performAbstraction('il_news_subscription');
?>
<#1751>
<?php
    $ilMySQLAbstraction->performAbstraction('il_rating');
?>
<#1752>
<?php
    $ilMySQLAbstraction->performAbstraction('il_tag');
?>
<#1753>
<?php
    $ilMySQLAbstraction->performAbstraction('il_wiki_contributor');
?>
<#1754>
<?php
    $ilMySQLAbstraction->performAbstraction('il_wiki_data');
?>

<#1755>
<?php
    $ilMySQLAbstraction->performAbstraction('data_cache');
?>

<#1756>
<?php
    $ilMySQLAbstraction->performAbstraction('cal_categories');
?>

<#1757>
<?php
    $ilMySQLAbstraction->performAbstraction('cal_categories_hidden');
?>

<#1758>
RENAME TABLE `cal_category_assignments` TO `cal_cat_assignments`;

<#1759>
<?php
    $ilMySQLAbstraction->performAbstraction('cal_cat_assignments');
?>

<#1760>
ALTER TABLE `cal_entries` CHANGE `informations` `informations` VARCHAR( 4000 ) NULL;

<#1761>
ALTER TABLE `cal_entries` CHANGE `end` `end` DATETIME NULL;

<#1762>
ALTER TABLE `cal_entries` CHANGE `start` `start` DATETIME NULL;

<#1763>
ALTER TABLE `cal_entries` CHANGE `last_update` `last_update` DATETIME NULL;

<#1764>
ALTER TABLE `cal_entries` CHANGE `start` `starta` DATETIME NULL DEFAULT NULL;

<#1765>
ALTER TABLE `cal_entries` CHANGE `end` `enda` DATETIME NULL DEFAULT NULL;

<#1766>
ALTER TABLE `cal_entries` CHANGE `description` `description` VARCHAR( 4000 ) NULL;

<#1767>
ALTER TABLE `cal_entries` CHANGE `location` `location` VARCHAR( 4000 ) NULL;
  
<#1768>
<?php
    $ilMySQLAbstraction->performAbstraction('cal_entries');
?>

<#1769>
ALTER TABLE `cal_recurrence_rules` CHANGE `freq_until_date` `freq_until_date` DATETIME NULL;

<#1770>
<?php
    $ilMySQLAbstraction->performAbstraction('cal_recurrence_rules');
?>
<#1771>
<?php
    $ilMySQLAbstraction->performAbstraction('il_wiki_missing_page');
?>
<#1772>
<?php
    $ilMySQLAbstraction->performAbstraction('il_wiki_page');
?>

<#1773>
ALTER TABLE `cal_shared` CHANGE `create_date` `create_date` DATETIME NULL;

<#1774>
<?php
    $ilMySQLAbstraction->performAbstraction('cal_shared');
?>
  
<#1775>
<?php
    $ilMySQLAbstraction->performAbstraction('cal_shared_status');
?>

<#1776>
<?php
    $ilMySQLAbstraction->performAbstraction('container_settings');
?>
<#1777>
<?php
    $ilMySQLAbstraction->performAbstraction('int_link');
?>
<#1778>
ALTER TABLE lm_data MODIFY public_access CHAR(1) DEFAULT 'n';
ALTER TABLE lm_data MODIFY active CHAR(1) DEFAULT 'y';
<#1779>
<?php
    $ilMySQLAbstraction->performAbstraction('lm_data');
?>
<#1780>
ALTER TABLE lm_menu MODIFY link_type CHAR(6) DEFAULT 'extern';
ALTER TABLE lm_menu MODIFY active CHAR(1) DEFAULT 'n';
<#1781>
<?php
    $ilMySQLAbstraction->performAbstraction('lm_menu');
?>
<#1782>
<?php
    $ilMySQLAbstraction->performAbstraction('lm_tree');
?>
<#1783>
<?php
    $ilMySQLAbstraction->performAbstraction('lo_access');
?>
<#1784>
<?php
    $ilMySQLAbstraction->performAbstraction('map_area');
?>
<#1785>
ALTER TABLE media_item MODIFY halign CHAR(10) DEFAULT 'Left';
ALTER TABLE media_item MODIFY purpose CHAR(20) DEFAULT 'Standard';
ALTER TABLE media_item MODIFY location_type CHAR(10) DEFAULT 'LocalFile';
ALTER TABLE media_item MODIFY tried_thumb CHAR(1) DEFAULT 'n';
<#1786>
ALTER TABLE media_item MODIFY param VARCHAR(2000);
<#1787>
ALTER TABLE media_item MODIFY caption VARCHAR(3000);
<#1788>
<?php
    $ilMySQLAbstraction->performAbstraction('media_item');
?>
<#1789>
<?php
    $ilMySQLAbstraction->performAbstraction('mep_tree');
?>
<#1790>
ALTER TABLE mob_parameter MODIFY value VARCHAR(2000);
<#1791>
<?php
    $ilMySQLAbstraction->performAbstraction('mob_parameter');
?>
<#1792>
<?php
    $ilMySQLAbstraction->performAbstraction('mob_usage');
?>
<#1793>
<?php
    $ilMySQLAbstraction->performAbstraction('personal_clipboard');
?>
<#1794>
<?php
    $ilMySQLAbstraction->performAbstraction('settings_deactivated_styles');
?>
<#1795>
<?php
    $ilMySQLAbstraction->performAbstraction('style_data');
?>
<#1796>
<?php
    $ilMySQLAbstraction->performAbstraction('style_char');
?>
<#1797>
<?php
    $ilMySQLAbstraction->performAbstraction('style_folder_styles');
?>

<#1798>
ALTER TABLE `container_sorting` ADD `pos` INT NOT NULL ;

<#1799>
<?php

$query = "UPDATE container_sorting SET pos = 100 * position";
$ilDB->query($query);

?>

<#1800>
ALTER TABLE `container_sorting` DROP position;

<#1801>
ALTER TABLE `container_sorting` CHANGE `pos` `position` INT( 11 ) NOT NULL;

<#1802>

<#1803>

<#1804>
<?php
    $ilMySQLAbstraction->performAbstraction('container_sorting');
?>

<#1805>
<?php
    $ilMySQLAbstraction->performAbstraction('container_sorting_settings');
?>

<#1806>
<?php
    $ilMySQLAbstraction->performAbstraction('style_parameter');
?>

<#1807>
<?php
    $ilMySQLAbstraction->performAbstraction('copy_wizard_options');
?>

<#1808>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_waiting_list');
?>

<#1809>
<?php
    $ilMySQLAbstraction->performAbstraction('il_subscribers');
?>


<#1810>
RENAME TABLE `il_md_copyright_selections`  TO `il_md_cpr_selections`;

<#1811>
ALTER TABLE `il_md_cpr_selections` CHANGE `copyright_and_other_restrictions` `cpr_restrictions` TINYINT( 1 ) NOT NULL DEFAULT '1';

<#1812>
<?php
    $ilMySQLAbstraction->performAbstraction('il_md_cpr_selections');
?>
<#1813>
<?php
    $ilMySQLAbstraction->performAbstraction('usr_pwassist');
?>

<#1814>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_annotation');
?>

<#1815>
ALTER TABLE `il_meta_classification` CHANGE `description` `description` VARCHAR( 4000 ) NULL DEFAULT NULL;  

<#1816>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_classification');
?>

<#1817>
ALTER TABLE `il_meta_contribute` CHANGE `date` `date` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1818>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_contribute');
?>
  
<#1819>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_description');
?>

<#1820>
ALTER TABLE `il_meta_educational` CHANGE `typical_learning_time` `typical_learning_time` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1821>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_educational');
?>

<#1822>
ALTER TABLE `il_meta_entity` CHANGE `entity` `entity` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1823>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_entity');
?>

<#1824>
<?php
    $ilMySQLAbstraction->performAbstraction('xhtml_page');
?>

<#1825>
ALTER TABLE `il_meta_format` CHANGE `format` `format` VARCHAR( 4000 ) NULL DEFAULT NULL;  
  
<#1826>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_format');
?>
<#1827>
<?php
    $ilMySQLAbstraction->performAbstraction('page_history');
?>

<#1828>
ALTER TABLE `il_meta_general` CHANGE `title` `title` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1829>
ALTER TABLE `il_meta_general` CHANGE `coverage` `coverage` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1830>  
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_general');
?>

<#1831>
ALTER TABLE `il_meta_identifier` CHANGE `catalog` `catalog` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1832>
ALTER TABLE `il_meta_identifier` CHANGE `entry` `entry` VARCHAR( 4000 ) NULL DEFAULT NULL; 

<#1833>  
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_identifier');
?>

<#1834>
ALTER TABLE `il_meta_identifier_` CHANGE `catalog` `catalog` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1835>
ALTER TABLE `il_meta_identifier_` CHANGE `entry` `entry` VARCHAR( 4000 ) NULL DEFAULT NULL; 

<#1836>  
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_identifier_');
?>

<#1837>
ALTER TABLE `il_meta_keyword` CHANGE `keyword` `keyword` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1838>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_keyword');
?>
  
<#1839>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_language');
?>

<#1840>
ALTER TABLE `il_meta_lifecycle` CHANGE `meta_version` `meta_version` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1841>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_lifecycle');
?>

<#1842>
ALTER TABLE `il_meta_location` CHANGE `location` `location` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1843>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_location');
?>

<#1844>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_meta_data');
?>

<#1845>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_relation');
?>

<#1846>
ALTER TABLE `il_meta_requirement` CHANGE `operating_system_minimum_version` `operating_system_minimum_version` CHAR( 255 ) NULL DEFAULT NULL;

<#1847>
ALTER TABLE `il_meta_requirement` CHANGE `operating_system_maximum_version` `operating_system_maximum_version` CHAR( 255 ) NULL DEFAULT NULL;  
  
<#1848>
ALTER TABLE `il_meta_requirement` CHANGE `browser_minimum_version` `browser_minimum_version` CHAR( 255 ) NULL DEFAULT NULL;

<#1849>
ALTER TABLE `il_meta_requirement` CHANGE `browser_maximum_version` `browser_maximum_version` CHAR( 255 ) NULL DEFAULT NULL;

<#1850>
ALTER TABLE `il_meta_requirement` CHANGE `operating_system_minimum_version` `os_min_version` CHAR( 255 ) NULL DEFAULT NULL;  

<#1851>
ALTER TABLE `il_meta_requirement` CHANGE `operating_system_maximum_version` `os_max_version` CHAR( 255 ) NULL DEFAULT NULL;  

<#1852>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_requirement');
?>
<#1853>
<?php
    if (!$ilDB->tableExists("payment_news")) {
        $ilDB->createTable(
            "payment_news",
            array(
                "news_id" => array(
                    "type" => "integer", "length" => 4, "notnull" => true),
                "news_title" => array(
                    "type" => "text", "length" => 200, "notnull" => false, "default" => null),
                "news_content" => array(
                    "type" => "clob", "notnull" => false, "default" => null),
                "creation_date" => array(
                    "type" => "timestamp", "notnull" => false, "default" => null),
                "update_date" => array(
                    "type" => "timestamp", "notnull" => false, "default" => null),
                "user_id" => array(
                    "type" => "integer", "length" => 4, "notnull" => true),
                "visibility" => array(
                    "type" => "text", "length" => 8, "notnull" => true, "default" => "users")
            )
        );
        $ilDB->addPrimaryKey("payment_news", array("news_id"));
        $ilDB->query('ALTER TABLE payment_news ADD INDEX c_date( creation_date)') ;
    }
?>
<#1854>
<?php
    if (!$ilDB->tableExists("payment_news_seq")) { // works only with mysql
        $res = $ilDB->query("SELECT MAX(news_id) ma FROM payment_news");
        $rec = $ilDB->fetchAssoc($res);
        $next = $rec["ma"] + 1;
    
        $ilDB->createSequence('payment_news', $next);
    }
?>
<#1855> 
ALTER TABLE `mail_options` MODIFY `signature` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#1856> 
<?php
    $ilMySQLAbstraction->performAbstraction('mail_options');
?>

<#1857> 
<?php
// ALTER TABLE `mail` MODIFY `rcp_to` VARCHAR( 4000 ) NULL DEFAULT NULL;
?>
<#1858>
<?php
// ALTER TABLE `mail` MODIFY `rcp_cc` VARCHAR( 4000 ) NULL DEFAULT NULL;
?>
<#1859>
<?php
// ALTER TABLE `mail` MODIFY `rcp_bcc` VARCHAR( 4000 ) NULL DEFAULT NULL;
?>
<#1860>
<?php
$ilDB->manipulate("ALTER TABLE `mail` CHANGE `m_message` `m_message` LONGTEXT NULL default NULL");
?>
<#1861>
ALTER TABLE `mail` MODIFY `import_name` VARCHAR( 4000 ) NULL DEFAULT NULL;
<#1862>
<?php
    $ilMySQLAbstraction->performAbstraction('mail');
?>

<#1863>
<?php
    $ilMySQLAbstraction->performAbstraction('mail_obj_data');
?>

<#1864>
<?php
    $ilMySQLAbstraction->performAbstraction('chat_invitations');
?>

<#1865>
<?php
    $ilMySQLAbstraction->performAbstraction('frm_thread_access');
?> 
<#1866>
ALTER TABLE `payment_settings` MODIFY `address` VARCHAR( 4000 ) NULL DEFAULT NULL;
<#1867>
ALTER TABLE `payment_settings` MODIFY `bank_data` VARCHAR( 4000 ) NULL DEFAULT NULL;
<#1868>
ALTER TABLE `payment_settings` MODIFY `add_info` VARCHAR( 4000 ) NULL DEFAULT NULL;
<#1869>
ALTER TABLE `payment_settings` MODIFY `paypal` VARCHAR( 4000 ) NULL DEFAULT NULL;
<#1870>
ALTER TABLE `payment_settings` MODIFY `bmf` VARCHAR( 4000 ) NULL DEFAULT NULL;
<#1871>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_settings');
?>
<#1872>
<?php
if (!$ilDB->tableColumnExists("qpl_questionpool", "tstamp")) {
    $query = "ALTER TABLE `qpl_questionpool` ADD `tstamp` INT NOT NULL DEFAULT '0'";
    $res = $ilDB->manipulate($query);
    $res = $ilDB->query("SELECT id_questionpool, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM qpl_questionpool");
    if ($res->numRows()) {
        while ($row = $ilDB->fetchAssoc($res)) {
            preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
            $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
            $ilDB->manipulateF(
                "UPDATE qpl_questionpool SET tstamp = %s WHERE id_questionpool = %s",
                array("integer", "integer"),
                array($tstamp, $row['id_questionpool'])
            );
        }
    }
    $ilDB->manipulate("ALTER TABLE `qpl_questionpool` DROP `TIMESTAMP`");
}
?>
<#1873>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_questionpool');
?>
<#1874>
<?php
if (!$ilDB->tableColumnExists("qpl_numeric_range", "tstamp")) {
    $query = "ALTER TABLE `qpl_numeric_range` ADD `tstamp` INT NOT NULL DEFAULT '0'";
    $res = $ilDB->manipulate($query);
    $res = $ilDB->query("SELECT range_id, lastchange + 0 timestamp14 FROM qpl_numeric_range");
    if ($res->numRows()) {
        while ($row = $ilDB->fetchAssoc($res)) {
            preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
            $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
            $ilDB->manipulateF(
                "UPDATE qpl_numeric_range SET tstamp = %s WHERE range_id = %s",
                array("integer", "integer"),
                array($tstamp, $row['range_id'])
            );
        }
    }
    $ilDB->manipulate("ALTER TABLE `qpl_numeric_range` DROP lastchange");
}
?>
<#1875>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_numeric_range');
?>
<#1876>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_questions` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1877>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_questions` ADD `tstampcreated` INT NOT NULL DEFAULT '0'");
?>
<#1878>
<?php
$res = $ilDB->query("SELECT question_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14, created FROM qpl_questions");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['created'], $matchescreated);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $created = mktime((int) $matchescreated[4], (int) $matchescreated[5], (int) $matchescreated[6], (int) $matchescreated[2], (int) $matchescreated[3], (int) $matchescreated[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_questions SET tstamp = %s, tstampcreated = %s WHERE question_id = %s",
            array("integer", "integer", "integer"),
            array($tstamp, $created, $row['question_id'])
        );
    }
}
?>
<#1879>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_questions` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#1880>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_questions` DROP created");
?>
<#1881>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_questions` CHANGE `tstampcreated` `created` INT NOT NULL default '0'");
?>
<#1882>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_questions` CHANGE `description` `description` VARCHAR(1000) NULL default NULL");
?>
<#1883>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_questions` CHANGE `question_text` `question_text` LONGTEXT NULL default NULL");
?>
<#1884>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_questions');
?>
<#1885>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_cloze');
?>
<#1886>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_question_essay` CHANGE `keywords` `keywords` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#1887>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_essay');
?>
<#1888>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_fileupload');
?>
<#1889>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_question_flash` CHANGE `params` `params` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#1890>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_flash');
?>
<#1891>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_imagemap');
?>
<#1892>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_question_javaapplet` CHANGE `params` `params` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#1893>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_javaapplet');
?>
<#1894>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_matching');
?>
<#1895>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_multiplechoice');
?>
<#1896>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_numeric');
?>
<#1897>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_ordering');
?>
<#1898>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_orderinghorizontal');
?>
<#1899>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_singlechoice');
?>
<#1900>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_textsubset');
?>
<#1901>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_question_type');
?>
<#1902>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_suggested_solutions` CHANGE `value` `value` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#1903>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_suggested_solutions` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1904>
<?php
$res = $ilDB->query("SELECT suggested_solution_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM qpl_suggested_solutions");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_suggested_solutions SET tstamp = %s WHERE suggested_solution_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['suggested_solution_id'])
        );
    }
}
?>
<#1905>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_suggested_solutions` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#1906>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_suggested_solutions');
?>
<#1907>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_answer_cloze` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1908>
<?php
$res = $ilDB->query("SELECT answer_id, lastchange + 0 timestamp14 FROM qpl_answer_cloze");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_answer_cloze SET tstamp = %s WHERE answer_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['answer_id'])
        );
    }
}
?>
<#1909>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_cloze` DROP lastchange");
?>
<#1910>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_cloze` CHANGE `answer_id` `answer_id` INT NOT NULL AUTO_INCREMENT");
?>
<#1911>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_cloze` CHANGE `answertext` `answertext` VARCHAR(1000) NULL DEFAULT NULL");
?>
<#1912>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_answer_cloze');
?>
<#1913>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_answer_imagemap` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1914>
<?php
$res = $ilDB->query("SELECT answer_id, lastchange + 0 timestamp14 FROM qpl_answer_imagemap");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_answer_imagemap SET tstamp = %s WHERE answer_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['answer_id'])
        );
    }
}
?>
<#1915>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_imagemap` DROP lastchange");
?>
<#1916>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_imagemap` CHANGE `answer_id` `answer_id` INT NOT NULL AUTO_INCREMENT");
?>
<#1917>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_imagemap` CHANGE `answertext` `answertext` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#1918>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_imagemap` CHANGE `coords` `coords` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#1919>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_answer_imagemap');
?>
<#1920>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_answer_matching` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1921>
<?php
$res = $ilDB->query("SELECT answer_id, lastchange + 0 timestamp14 FROM qpl_answer_matching");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_answer_matching SET tstamp = %s WHERE answer_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['answer_id'])
        );
    }
}
?>
<#1922>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_matching` DROP lastchange");
?>
<#1923>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_matching` CHANGE `answer_id` `answer_id` INT NOT NULL AUTO_INCREMENT");
?>
<#1924>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_matching` CHANGE `matchingtext` `matchingtext` VARCHAR(1000) NULL DEFAULT NULL");
?>
<#1925>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_answer_matching');
?>
<#1926>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_matching_term` CHANGE `term` `term` VARCHAR(1000) NULL DEFAULT NULL");
?>
<#1927>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_matching_term` TO `qpl_a_mterm`");
?>
<#1928>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_a_mterm');
?>
<#1929>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_cloze` TO `qpl_qst_cloze`");
?>
<#1930>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_essay` TO `qpl_qst_essay`");
?>
<#1931>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_fileupload` TO `qpl_qst_fileupload`");
?>
<#1932>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_flash` TO `qpl_qst_flash`");
?>
<#1933>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_imagemap` TO `qpl_qst_imagemap`");
?>
<#1934>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_javaapplet` TO `qpl_qst_javaapplet`");
?>
<#1935>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_matching` TO `qpl_qst_matching`");
?>
<#1936>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_multiplechoice` TO `qpl_qst_mc`");
?>
<#1937>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_numeric` TO `qpl_qst_numeric`");
?>
<#1938>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_ordering` TO `qpl_qst_ordering`");
?>
<#1939>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_orderinghorizontal` TO `qpl_qst_horder`");
?>
<#1940>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_singlechoice` TO `qpl_qst_sc`");
?>
<#1941>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_textsubset` TO `qpl_qst_textsubset`");
?>
<#1942>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_suggested_solutions` TO `qpl_sol_sug`");
?>
<#1943>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_suggested_solutions_seq` TO `qpl_sol_sug_seq`");
?>
<#1944>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_question_type` TO `qpl_qst_type`");
?>
<#1945>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_numeric_range` TO `qpl_num_range`");
?>
<#1946>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_numeric_range_seq` TO `qpl_num_range_seq`");
?>
<#1947>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_cloze` TO `qpl_a_cloze`");
?>
<#1948>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_cloze_seq` TO `qpl_a_cloze_seq`");
?>
<#1949>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_imagemap` TO `qpl_a_imagemap`");
?>
<#1950>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_imagemap_seq` TO `qpl_a_imagemap_seq`");
?>
<#1951>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_matching` TO `qpl_a_matching`");
?>
<#1952>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_matching_seq` TO `qpl_a_matching_seq`");
?>
<#1953>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_answer_multiplechoice` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1954>
<?php
$res = $ilDB->query("SELECT answer_id, lastchange + 0 timestamp14 FROM qpl_answer_multiplechoice");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_answer_multiplechoice SET tstamp = %s WHERE answer_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['answer_id'])
        );
    }
}
?>
<#1955>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_multiplechoice` DROP lastchange");
?>
<#1956>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_multiplechoice` CHANGE `answer_id` `answer_id` INT NOT NULL AUTO_INCREMENT");
?>
<#1957>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_multiplechoice` CHANGE `answertext` `answertext` VARCHAR(1000) NULL DEFAULT NULL");
?>
<#1958>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_multiplechoice` CHANGE `imagefile` `imagefile` VARCHAR(1000) NULL DEFAULT NULL");
?>
<#1959>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_multiplechoice` TO `qpl_a_mc`");
?>
<#1960>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_a_mc');
?>
<#1961>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_answer_ordering` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1962>
<?php
$res = $ilDB->query("SELECT answer_id, lastchange + 0 timestamp14 FROM qpl_answer_ordering");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_answer_ordering SET tstamp = %s WHERE answer_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['answer_id'])
        );
    }
}
?>
<#1963>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_ordering` DROP lastchange");
?>
<#1964>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_ordering` CHANGE `answer_id` `answer_id` INT NOT NULL AUTO_INCREMENT");
?>
<#1965>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_ordering` CHANGE `answertext` `answertext` VARCHAR(1000) NULL DEFAULT NULL");
?>
<#1966>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_ordering` TO `qpl_a_ordering`");
?>
<#1967>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_a_ordering');
?>
<#1968>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_answer_singlechoice` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1969>
<?php
$res = $ilDB->query("SELECT answer_id, lastchange + 0 timestamp14 FROM qpl_answer_singlechoice");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_answer_singlechoice SET tstamp = %s WHERE answer_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['answer_id'])
        );
    }
}
?>
<#1970>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_singlechoice` DROP lastchange");
?>
<#1971>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_singlechoice` CHANGE `answer_id` `answer_id` INT NOT NULL AUTO_INCREMENT");
?>
<#1972>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_singlechoice` CHANGE `answertext` `answertext` VARCHAR(1000) NULL DEFAULT NULL");
?>
<#1973>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_singlechoice` CHANGE `imagefile` `imagefile` VARCHAR(1000) NULL DEFAULT NULL");
?>
<#1974>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_singlechoice` TO `qpl_a_sc`");
?>
<#1975>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_a_sc');
?>
<#1976>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_answer_textsubset` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1977>
<?php
$res = $ilDB->query("SELECT answer_id, lastchange + 0 timestamp14 FROM qpl_answer_textsubset");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_answer_textsubset SET tstamp = %s WHERE answer_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['answer_id'])
        );
    }
}
?>
<#1978>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_textsubset` DROP lastchange");
?>
<#1979>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_textsubset` CHANGE `answer_id` `answer_id` INT NOT NULL AUTO_INCREMENT");
?>
<#1980>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_answer_textsubset` CHANGE `answertext` `answertext` VARCHAR(1000) NULL DEFAULT NULL");
?>
<#1981>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_answer_textsubset` TO `qpl_a_textsubset`");
?>
<#1982>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_a_textsubset');
?>
<#1983>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_feedback_generic` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1984>
<?php
$res = $ilDB->query("SELECT feedback_id, lastchange + 0 timestamp14 FROM qpl_feedback_generic");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_feedback_generic SET tstamp = %s WHERE feedback_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['feedback_id'])
        );
    }
}
?>
<#1985>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_feedback_generic` DROP lastchange");
?>
<#1986>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_feedback_generic` CHANGE `feedback` `feedback` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#1987>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_feedback_generic` TO `qpl_fb_generic`");
?>
<#1988>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_fb_generic');
?>
<#1989>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_feedback_imagemap` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1990>
<?php
$res = $ilDB->query("SELECT feedback_id, lastchange + 0 timestamp14 FROM qpl_feedback_imagemap");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_feedback_imagemap SET tstamp = %s WHERE feedback_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['feedback_id'])
        );
    }
}
?>
<#1991>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_feedback_imagemap` DROP lastchange");
?>
<#1992>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_feedback_imagemap` CHANGE `feedback` `feedback` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#1993>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_feedback_imagemap` TO `qpl_fb_imap`");
?>
<#1994>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_fb_imap');
?>
<#1995>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_feedback_multiplechoice` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#1996>
<?php
$res = $ilDB->query("SELECT feedback_id, lastchange + 0 timestamp14 FROM qpl_feedback_multiplechoice");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_feedback_multiplechoice SET tstamp = %s WHERE feedback_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['feedback_id'])
        );
    }
}
?>
<#1997>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_feedback_multiplechoice` DROP lastchange");
?>
<#1998>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_feedback_multiplechoice` CHANGE `feedback` `feedback` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#1999>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_feedback_multiplechoice` TO `qpl_fb_mc`");
?>
<#2000>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_fb_mc');
?>
<#2001>
<?php
$res = $ilDB->manipulate("ALTER TABLE `qpl_feedback_singlechoice` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2002>
<?php
$res = $ilDB->query("SELECT feedback_id, lastchange + 0 timestamp14 FROM qpl_feedback_singlechoice");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE qpl_feedback_singlechoice SET tstamp = %s WHERE feedback_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['feedback_id'])
        );
    }
}
?>
<#2003>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_feedback_singlechoice` DROP lastchange");
?>
<#2004>
<?php
$ilDB->manipulate("ALTER TABLE `qpl_feedback_singlechoice` CHANGE `feedback` `feedback` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2005>
<?php
$ilDB->manipulate("RENAME TABLE `qpl_feedback_singlechoice` TO `qpl_fb_sc`");
?>
<#2006>
<?php
$ilMySQLAbstraction->performAbstraction('qpl_fb_sc');
?>
<#2007>
<?php
$ilDB->manipulate("ALTER TABLE `tst_active` CHANGE `active_id` `active_id` INT NOT NULL AUTO_INCREMENT");
?>
<#2008>
<?php
$ilDB->manipulate("ALTER TABLE `tst_active` CHANGE `sequence` `sequence` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2009>
<?php
$ilDB->manipulate("ALTER TABLE `tst_active` CHANGE `postponed` `postponed` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2010>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_active` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2011>
<?php
$res = $ilDB->query("SELECT active_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_active");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_active SET tstamp = %s WHERE active_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['active_id'])
        );
    }
}
?>
<#2012>
<?php
$ilDB->manipulate("ALTER TABLE `tst_active` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2013>
<?php
$ilDB->manipulate("RENAME TABLE `tst_active_qst_sol_settings` TO `tst_qst_solved`");
?>
<#2014>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_invited_user` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2015>
<?php
$res = $ilDB->query("SELECT test_fi, user_fi, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_invited_user");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_invited_user SET tstamp = %s WHERE test_fi = %s AND user_fi = %s",
            array("integer", "integer", "integer"),
            array($tstamp, $row['test_fi'], $row['user_fi'])
        );
    }
}
?>
<#2016>
<?php
$ilDB->manipulate("ALTER TABLE `tst_invited_user` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2017>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_manual_feedback` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2018>
<?php
$res = $ilDB->query("SELECT manual_feedback_id, lastchange + 0 timestamp14 FROM tst_manual_feedback");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_manual_feedback SET tstamp = %s WHERE manual_feedback_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['manual_feedback_id'])
        );
    }
}
?>
<#2019>
<?php
$ilDB->manipulate("ALTER TABLE `tst_manual_feedback` DROP lastchange");
?>
<#2020>
<?php
$ilDB->manipulate("ALTER TABLE `tst_manual_feedback` CHANGE `feedback` `feedback` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2021>
<?php
$ilDB->manipulate("RENAME TABLE `tst_manual_feedback` TO `tst_manual_fb`");
?>
<#2022>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_mark` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2023>
<?php
$res = $ilDB->query("SELECT mark_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_mark");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_mark SET tstamp = %s WHERE mark_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['mark_id'])
        );
    }
}
?>
<#2024>
<?php
$ilDB->manipulate("ALTER TABLE `tst_mark` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2025>
<?php
$ilDB->manipulate("ALTER TABLE `tst_mark` CHANGE `mark_id` `mark_id` INT NOT NULL AUTO_INCREMENT");
?>
<#2026>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_sequence` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2027>
<?php
$res = $ilDB->query("SELECT active_fi, pass, lastchange + 0 timestamp14 FROM tst_sequence");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_sequence SET tstamp = %s WHERE active_fi = %s AND pass = %s",
            array("integer", "integer", "integer"),
            array($tstamp, $row['active_fi'], $row['pass'])
        );
    }
}
?>
<#2028>
<?php
$ilDB->manipulate("ALTER TABLE `tst_sequence` DROP lastchange");
?>
<#2029>
<?php
$ilDB->manipulate("ALTER TABLE `tst_sequence` CHANGE `sequence` `sequence` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2030>
<?php
$ilDB->manipulate("ALTER TABLE `tst_sequence` CHANGE `postponed` `postponed` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2031>
<?php
$ilDB->manipulate("ALTER TABLE `tst_sequence` CHANGE `hidden` `hidden` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2032>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_solutions` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2033>
<?php
$res = $ilDB->query("SELECT solution_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_solutions");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_solutions SET tstamp = %s WHERE solution_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['solution_id'])
        );
    }
}
?>
<#2034>
<?php
$ilDB->manipulate("ALTER TABLE `tst_solutions` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2035>
<?php
$ilDB->manipulate("ALTER TABLE `tst_solutions` CHANGE `solution_id` `solution_id` INT NOT NULL AUTO_INCREMENT");
?>
<#2036>
<?php
$ilDB->manipulate("ALTER TABLE `tst_solutions` CHANGE `value1` `value1` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2037>
<?php
$ilDB->manipulate("ALTER TABLE `tst_solutions` CHANGE `value2` `value2` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2038>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_tests` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2039>
<?php
$res = $ilDB->query("SELECT test_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_tests");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_tests SET tstamp = %s WHERE test_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['test_id'])
        );
    }
}
?>
<#2040>
<?php
$ilDB->manipulate("ALTER TABLE `tst_tests` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2041>
<?php
$ilDB->manipulate("ALTER TABLE `tst_tests` CHANGE `test_id` `test_id` INT NOT NULL AUTO_INCREMENT");
?>
<#2042>
<?php
$ilDB->manipulate("ALTER TABLE `tst_tests` CHANGE `introduction` `introduction` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2043>
<?php
$ilDB->manipulate("ALTER TABLE `tst_tests` CHANGE `finalstatement` `finalstatement` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2044>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_test_defaults` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2045>
<?php
$res = $ilDB->query("SELECT test_defaults_id, lastchange + 0 timestamp14 FROM tst_test_defaults");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_test_defaults SET tstamp = %s WHERE test_defaults_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['test_defaults_id'])
        );
    }
}
?>
<#2046>
<?php
$ilDB->manipulate("ALTER TABLE `tst_test_defaults` DROP lastchange");
?>
<#2047>
<?php
$ilDB->manipulate("ALTER TABLE `tst_test_defaults` CHANGE `defaults` `defaults` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2048>
<?php
$ilDB->manipulate("ALTER TABLE `tst_test_defaults` CHANGE `marks` `marks` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2049>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_test_pass_result` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2050>
<?php
$res = $ilDB->query("SELECT active_fi, pass, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_test_pass_result");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_test_pass_result SET tstamp = %s WHERE active_fi = %s AND pass = %s",
            array("integer", "integer", "integer"),
            array($tstamp, $row['active_fi'], $row["pass"])
        );
    }
}
?>
<#2051>
<?php
$ilDB->manipulate("ALTER TABLE `tst_test_pass_result` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2052>
<?php
$ilDB->manipulate("RENAME TABLE `tst_test_pass_result` TO `tst_pass_result`");
?>
<#2053>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_test_question` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2054>
<?php
$res = $ilDB->query("SELECT test_question_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_test_question");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_test_question SET tstamp = %s WHERE test_question_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['test_question_id'])
        );
    }
}
?>
<#2055>
<?php
$ilDB->manipulate("ALTER TABLE `tst_test_question` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2056>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_test_random` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2057>
<?php
$res = $ilDB->query("SELECT test_random_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_test_random");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_test_random SET tstamp = %s WHERE test_random_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['test_random_id'])
        );
    }
}
?>
<#2058>
<?php
$ilDB->manipulate("ALTER TABLE `tst_test_random` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2059>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_test_random_question` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2060>
<?php
$res = $ilDB->query("SELECT test_random_question_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_test_random_question");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_test_random_question SET tstamp = %s WHERE test_random_question_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['test_random_question_id'])
        );
    }
}
?>
<#2061>
<?php
$ilDB->manipulate("ALTER TABLE `tst_test_random_question` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2062>
<?php
$ilDB->manipulate("RENAME TABLE `tst_test_random_question` TO `tst_test_rnd_qst`");
?>
<#2063>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_test_result` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2064>
<?php
$res = $ilDB->query("SELECT test_result_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_test_result");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_test_result SET tstamp = %s WHERE test_result_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['test_result_id'])
        );
    }
}
?>
<#2065>
<?php
$ilDB->manipulate("ALTER TABLE `tst_test_result` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2066>
<?php
$ilDB->manipulate("ALTER TABLE `tst_test_result` CHANGE `test_result_id` `test_result_id` INT NOT NULL AUTO_INCREMENT");
?>
<#2067>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_times` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2068>
<?php
$res = $ilDB->query("SELECT times_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM tst_times");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_times SET tstamp = %s WHERE times_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['times_id'])
        );
    }
}
?>
<#2069>
<?php
$ilDB->manipulate("ALTER TABLE `tst_times` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2070>
<?php
$res = $ilDB->manipulate("ALTER TABLE `tst_tests` ADD `tstampcreated` INT NOT NULL DEFAULT '0'");
?>
<#2071>
<?php
$res = $ilDB->query("SELECT test_id, created FROM tst_tests");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['created'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE tst_tests SET tstampcreated = %s WHERE test_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['test_id'])
        );
    }
}
?>
<#2072>
<?php
$ilDB->manipulate("ALTER TABLE `tst_tests` DROP `created`");
?>
<#2073>
<?php
$ilDB->manipulate("ALTER TABLE `tst_tests` CHANGE `tstampcreated` `created` INT NOT NULL DEFAULT '0'");
?>
<#2074>
<?php
$ilMySQLAbstraction->performAbstraction('tst_test_question');
?>
<#2075>
<?php
$ilMySQLAbstraction->performAbstraction('tst_tests');
?>
<#2076>
<?php
$res = $ilDB->manipulate("ALTER TABLE `ass_log` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2077>
<?php
$res = $ilDB->query("SELECT ass_log_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM ass_log");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE ass_log SET tstamp = %s WHERE ass_log_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['ass_log_id'])
        );
    }
}
?>
<#2078>
<?php
$ilDB->manipulate("ALTER TABLE `ass_log` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2079>
<?php
$ilDB->manipulate("ALTER TABLE `ass_log` CHANGE `logtext` `logtext` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2080>
<?php
$ilMySQLAbstraction->performAbstraction('ass_log');
?>
<#2081>
<?php
$ilMySQLAbstraction->performAbstraction('tst_active');
?>
<#2082>
<?php
$ilMySQLAbstraction->performAbstraction('tst_invited_user');
?>
<#2083>
<?php
$ilMySQLAbstraction->performAbstraction('tst_manual_fb');
?>
<#2084>
<?php
$ilMySQLAbstraction->performAbstraction('tst_mark');
?>
<#2085>
<?php
$ilMySQLAbstraction->performAbstraction('tst_pass_result');
?>
<#2086>
<?php
$ilMySQLAbstraction->performAbstraction('tst_qst_solved');
?>
<#2087>
<?php
$ilMySQLAbstraction->performAbstraction('tst_sequence');
?>
<#2088>
<?php
$ilMySQLAbstraction->performAbstraction('tst_solutions');
?>
<#2089>
<?php
$ilMySQLAbstraction->performAbstraction('tst_test_defaults');
?>
<#2090>
<?php
$ilMySQLAbstraction->performAbstraction('tst_test_random');
?>
<#2091>
<?php
$ilMySQLAbstraction->performAbstraction('tst_test_result');
?>
<#2092>
<?php
$ilMySQLAbstraction->performAbstraction('tst_test_rnd_qst');
?>
<#2093>
<?php
$ilMySQLAbstraction->performAbstraction('tst_times');
?>
<#2094>
<?php
$ilDB->manipulate("ALTER TABLE `survey_answer` DROP `survey_fi`");
?>
<#2095>
<?php
$ilDB->manipulate("ALTER TABLE `survey_answer` DROP `user_fi`");
?>
<#2096>
<?php
$ilDB->manipulate("ALTER TABLE `survey_answer` DROP `anonymous_id`");
?>
<#2097>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_anonymous` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2098>
<?php
$res = $ilDB->query("SELECT anonymous_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_anonymous");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_anonymous SET tstamp = %s WHERE anonymous_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['anonymous_id'])
        );
    }
}
?>
<#2099>
<?php
$ilDB->manipulate("ALTER TABLE `survey_anonymous` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2100>
<?php
$ilDB->manipulate("RENAME TABLE `survey_anonymous` TO `svy_anonymous`");
?>
<#2101>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_answer` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2102>
<?php
$res = $ilDB->query("SELECT answer_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_answer");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_answer SET tstamp = %s WHERE answer_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['answer_id'])
        );
    }
}
?>
<#2103>
<?php
$ilDB->manipulate("ALTER TABLE `survey_answer` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2104>
<?php
$ilDB->manipulate("ALTER TABLE `survey_answer` CHANGE `textanswer` `textanswer` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2105>
<?php
$ilDB->manipulate("RENAME TABLE `survey_answer` TO `svy_answer`");
?>
<#2106>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_category` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2107>
<?php
$res = $ilDB->query("SELECT category_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_category");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_category SET tstamp = %s WHERE category_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['category_id'])
        );
    }
}
?>
<#2108>
<?php
$ilDB->manipulate("ALTER TABLE `survey_category` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2109>
<?php
$ilDB->manipulate("RENAME TABLE `survey_category` TO `svy_category`");
?>
<#2110>
<?php
$ilDB->manipulate("RENAME TABLE `survey_constraint` TO `svy_constraint`");
?>
<#2111>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_finished` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2112>
<?php
$res = $ilDB->query("SELECT finished_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_finished");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_finished SET tstamp = %s WHERE finished_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['finished_id'])
        );
    }
}
?>
<#2113>
<?php
$ilDB->manipulate("ALTER TABLE `survey_finished` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2114>
<?php
$ilDB->manipulate("RENAME TABLE `survey_finished` TO `svy_finished`");
?>
<#2115>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_invited_group` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2116>
<?php
$res = $ilDB->query("SELECT invited_group_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_invited_group");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_invited_group SET tstamp = %s WHERE invited_group_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['invited_group_id'])
        );
    }
}
?>
<#2117>
<?php
$ilDB->manipulate("ALTER TABLE `survey_invited_group` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2118>
<?php
$ilDB->manipulate("RENAME TABLE `survey_invited_group` TO `svy_inv_grp`");
?>
<#2119>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_invited_user` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2120>
<?php
$res = $ilDB->query("SELECT invited_user_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_invited_user");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_invited_user SET tstamp = %s WHERE invited_user_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['invited_user_id'])
        );
    }
}
?>
<#2121>
<?php
$ilDB->manipulate("ALTER TABLE `survey_invited_user` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2122>
<?php
$ilDB->manipulate("RENAME TABLE `survey_invited_user` TO `svy_inv_usr`");
?>
<#2123>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_material` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2124>
<?php
$res = $ilDB->query("SELECT material_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_material");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_material SET tstamp = %s WHERE material_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['material_id'])
        );
    }
}
?>
<#2125>
<?php
$ilDB->manipulate("ALTER TABLE `survey_material` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2126>
<?php
$ilDB->manipulate("RENAME TABLE `survey_material` TO `svy_material`");
?>
<#2127>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_phrase` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2128>
<?php
$res = $ilDB->query("SELECT phrase_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_phrase");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_phrase SET tstamp = %s WHERE phrase_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['phrase_id'])
        );
    }
}
?>
<#2129>
<?php
$ilDB->manipulate("ALTER TABLE `survey_phrase` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2130>
<?php
$ilDB->manipulate("RENAME TABLE `survey_phrase` TO `svy_phrase`");
?>
<#2131>
<?php
$ilDB->manipulate("RENAME TABLE `survey_phrase_category` TO `svy_phrase_cat`");
?>
<#2132>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_question` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2133>
<?php
$res = $ilDB->query("SELECT question_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_question");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_question SET tstamp = %s WHERE question_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['question_id'])
        );
    }
}
?>
<#2134>
<?php
$ilDB->manipulate("ALTER TABLE `survey_question` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2135>
<?php
$ilDB->manipulate("ALTER TABLE `survey_question` CHANGE `questiontext` `questiontext` LONGTEXT NULL DEFAULT NULL");
?>
<#2136>
<?php
$ilDB->manipulate("RENAME TABLE `survey_question` TO `svy_question`");
?>
<#2137>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_questionblock` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2138>
<?php
$res = $ilDB->query("SELECT questionblock_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_questionblock");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_questionblock SET tstamp = %s WHERE questionblock_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['questionblock_id'])
        );
    }
}
?>
<#2139>
<?php
$ilDB->manipulate("ALTER TABLE `survey_questionblock` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2140>
<?php
$ilDB->manipulate("ALTER TABLE `survey_questionblock` CHANGE `title` `title` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2141>
<?php
$ilDB->manipulate("RENAME TABLE `survey_questionblock` TO `svy_qblk`");
?>
<#2142>
<?php
$ilDB->manipulate("ALTER TABLE `survey_questionblock_question` CHANGE `questionblock_question_id` `qblk_qst_id` INT NOT NULL AUTO_INCREMENT");
?>
<#2143>
<?php
$ilDB->manipulate("RENAME TABLE `survey_questionblock_question` TO `svy_qblk_qst`");
?>
<#2144>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_questionpool` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2145>
<?php
$res = $ilDB->query("SELECT id_questionpool, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_questionpool");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_questionpool SET tstamp = %s WHERE id_questionpool = %s",
            array("integer", "integer"),
            array($tstamp, $row['id_questionpool'])
        );
    }
}
?>
<#2146>
<?php
$ilDB->manipulate("ALTER TABLE `survey_questionpool` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2147>
<?php
$ilDB->manipulate("RENAME TABLE `survey_questionpool` TO `svy_qpl`");
?>
<#2148>
<?php
$ilDB->manipulate("RENAME TABLE `survey_questiontype` TO `svy_qtype`");
?>
<#2149>
<?php
$ilDB->manipulate("RENAME TABLE `survey_question_constraint` TO `svy_qst_constraint`");
?>
<#2150>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_question_material` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2151>
<?php
$ilDB->manipulate("ALTER TABLE `survey_question_material` CHANGE `materials` `materials` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2152>
<?php
$ilDB->manipulate("RENAME TABLE `survey_question_material` TO `svy_qst_mat`");
?>
<#2153>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_question_matrix` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2154>
<?php
$res = $ilDB->query("SELECT question_fi, lastchange + 0 timestamp14 FROM survey_question_matrix");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_question_matrix SET tstamp = %s WHERE question_fi = %s",
            array("integer", "integer"),
            array($tstamp, $row['question_fi'])
        );
    }
}
?>
<#2155>
<?php
$ilDB->manipulate("ALTER TABLE `survey_question_matrix` DROP lastchange");
?>
<#2156>
<?php
$ilDB->manipulate("ALTER TABLE `survey_question_matrix` CHANGE `layout` `layout` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2157>
<?php
$ilDB->manipulate("RENAME TABLE `survey_question_matrix` TO `svy_qst_matrix`");
?>
<#2158>
<?php
$ilDB->manipulate("ALTER TABLE `survey_question_matrix_rows` CHANGE `id_survey_question_matrix_rows` `id_svy_qst_matrixrows` INT NOT NULL AUTO_INCREMENT");
?>
<#2159>
<?php
$ilDB->manipulate("RENAME TABLE `survey_question_matrix_rows` TO `svy_qst_matrixrows`");
?>
<#2160>
<?php
$ilDB->manipulate("RENAME TABLE `survey_question_metric` TO `svy_qst_metric`");
?>
<#2161>
<?php
$ilDB->manipulate("RENAME TABLE `survey_question_nominal` TO `svy_qst_nominal`");
?>
<#2162>
<?php
$ilDB->manipulate("RENAME TABLE `survey_question_ordinal` TO `svy_qst_ordinal`");
?>
<#2163>
<?php
$ilDB->manipulate("RENAME TABLE `survey_question_text` TO `svy_qst_text`");
?>
<#2164>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_question_obligatory` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2165>
<?php
$res = $ilDB->query("SELECT question_obligatory_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_question_obligatory");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_question_obligatory SET tstamp = %s WHERE question_obligatory_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['question_obligatory_id'])
        );
    }
}
?>
<#2166>
<?php
$ilDB->manipulate("ALTER TABLE `survey_question_obligatory` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2167>
<?php
$ilDB->manipulate("RENAME TABLE `survey_question_obligatory` TO `svy_qst_oblig`");
?>
<#2168>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_relation` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2169>
<?php
$res = $ilDB->query("SELECT relation_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_relation");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_relation SET tstamp = %s WHERE relation_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['relation_id'])
        );
    }
}
?>
<#2170>
<?php
$ilDB->manipulate("ALTER TABLE `survey_relation` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2171>
<?php
$ilDB->manipulate("RENAME TABLE `survey_relation` TO `svy_relation`");
?>
<#2172>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_survey_question` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2173>
<?php
$res = $ilDB->query("SELECT survey_question_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_survey_question");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_survey_question SET tstamp = %s WHERE survey_question_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['survey_question_id'])
        );
    }
}
?>
<#2174>
<?php
$ilDB->manipulate("ALTER TABLE `survey_survey_question` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2175>
<?php
$ilDB->manipulate("ALTER TABLE `survey_survey_question` CHANGE `heading` `heading` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2176>
<?php
$ilDB->manipulate("RENAME TABLE `survey_survey_question` TO `svy_svy_qst`");
?>
<#2177>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_survey` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2178>
<?php
$res = $ilDB->query("SELECT survey_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_survey");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_survey SET tstamp = %s WHERE survey_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['survey_id'])
        );
    }
}
?>
<#2179>
<?php
$ilDB->manipulate("ALTER TABLE `survey_survey` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2180>
<?php
$ilDB->manipulate("RENAME TABLE `survey_survey` TO `svy_svy`");
?>
<#2181>
<?php
$res = $ilDB->manipulate("ALTER TABLE `survey_variable` ADD `tstamp` INT NOT NULL DEFAULT '0'");
?>
<#2182>
<?php
$res = $ilDB->query("SELECT variable_id, " . $ilDB->quoteIdentifier("TIMESTAMP") . " + 0 timestamp14 FROM survey_variable");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['timestamp14'], $matches);
        $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        $ilDB->manipulateF(
            "UPDATE survey_variable SET tstamp = %s WHERE variable_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['variable_id'])
        );
    }
}
?>
<#2183>
<?php
$ilDB->manipulate("ALTER TABLE `survey_variable` DROP " . $ilDB->quoteIdentifier("TIMESTAMP"));
?>
<#2184>
<?php
$ilDB->manipulate("RENAME TABLE `survey_variable` TO `svy_variable`");
?>
<#2185>
<?php
$res = $ilDB->manipulate("ALTER TABLE `svy_svy` ADD `tstampcreated` INT NOT NULL DEFAULT '0'");
?>
<#2186>
<?php
$res = $ilDB->query("SELECT survey_id, created FROM svy_svy");
if ($res->numRows()) {
    while ($row = $ilDB->fetchAssoc($res)) {
        if (preg_match("/(\\d{4})(\\d{2})(\\d{2})(\\d{2})(\\d{2})(\\d{2})/", $row['created'], $matches)) {
            $tstamp = mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        } else {
            $tstamp = time();
        }
        $ilDB->manipulateF(
            "UPDATE svy_svy SET tstampcreated = %s WHERE survey_id = %s",
            array("integer", "integer"),
            array($tstamp, $row['survey_id'])
        );
    }
}
?>
<#2187>
<?php
$ilDB->manipulate("ALTER TABLE `svy_svy` DROP `created`");
?>
<#2188>
<?php
$ilDB->manipulate("ALTER TABLE `svy_svy` CHANGE `tstampcreated` `created` INT NOT NULL DEFAULT '0'");
?>
<#2189>
<?php
$ilMySQLAbstraction->performAbstraction('svy_anonymous');
?>
<#2190>
<?php
$ilMySQLAbstraction->performAbstraction('svy_answer');
?>
<#2191>
<?php
$ilMySQLAbstraction->performAbstraction('svy_category');
?>
<#2192>
<?php
$ilMySQLAbstraction->performAbstraction('svy_constraint');
?>
<#2193>
<?php
$ilMySQLAbstraction->performAbstraction('svy_finished');
?>
<#2194>
<?php
$ilMySQLAbstraction->performAbstraction('svy_inv_grp');
?>
<#2195>
<?php
$ilMySQLAbstraction->performAbstraction('svy_inv_usr');
?>
<#2196>
<?php
$ilMySQLAbstraction->performAbstraction('svy_material');
?>
<#2197>
<?php
$ilMySQLAbstraction->performAbstraction('svy_phrase');
?>
<#2198>
<?php
$ilMySQLAbstraction->performAbstraction('svy_phrase_cat');
?>
<#2199>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qblk');
?>
<#2200>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qblk_qst');
?>
<#2201>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qpl');
?>
<#2202>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qst_constraint');
?>
<#2203>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qst_mat');
?>
<#2204>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qst_matrix');
?>
<#2205>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qst_matrixrows');
?>
<#2206>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qst_metric');
?>
<#2207>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qst_nominal');
?>
<#2208>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qst_oblig');
?>
<#2209>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qst_ordinal');
?>
<#2210>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qst_text');
?>
<#2211>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qtype');
?>
<#2212>
<?php
$ilMySQLAbstraction->performAbstraction('svy_question');
?>
<#2213>
<?php
$ilMySQLAbstraction->performAbstraction('svy_relation');
?>
<#2214>
<?php
$ilMySQLAbstraction->performAbstraction('svy_svy');
?>
<#2215>
<?php
$ilMySQLAbstraction->performAbstraction('svy_svy_qst');
?>
<#2216>
<?php
$ilMySQLAbstraction->performAbstraction('svy_variable');
?>
<#2217>
<?php
$ilDB->dropTableColumn('tst_active', 'sequence');
?>
<#2218>
<?php
$ilDB->dropTableColumn('tst_active', 'postponed');
?>
<#2219>
<?php
$ilDB->query("ALTER TABLE qpl_a_matching MODIFY `matching_order` INT NOT NULL DEFAULT 0");
?>
<#2220>
<?php
$ilDB->dropTableColumn('qpl_a_ordering', 'points');
?>
<#2221>
<?php
$ilDB->dropTableColumn('qpl_a_ordering', 'aorder');
?>
 
<#2222>  
ALTER TABLE `il_meta_rights` CHANGE `copyright_and_other_restrictions` `cpr_and_or` CHAR( 3 ) NULL DEFAULT NULL;

<#2223>
ALTER TABLE `il_meta_rights` CHANGE `description` `description` VARCHAR( 4000 ) NULL DEFAULT NULL;  

<#2224>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_rights');
?>

<#2225>
ALTER TABLE `il_meta_taxon` CHANGE `taxon` `taxon` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#2226>
ALTER TABLE `il_meta_taxon` CHANGE `taxon_id` `taxon_id` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#2227>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_taxon');
?>

<#2228>
ALTER TABLE `il_meta_taxon_path` CHANGE `source` `source` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#2229>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_taxon_path');
?>

<#2230>
ALTER TABLE `il_meta_technical` CHANGE `installation_remarks` `ir` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#2231>
ALTER TABLE `il_meta_technical` CHANGE `installation_remarks_language` `ir_language` CHAR( 2 ) NULL DEFAULT NULL;

<#2232>
ALTER TABLE `il_meta_technical` CHANGE `other_platform_requirements` `opr` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#2233>
ALTER TABLE `il_meta_technical` CHANGE `other_platform_requirements_language` `opr_language` CHAR( 2 ) NULL DEFAULT NULL;

<#2234>
ALTER TABLE `il_meta_technical` CHANGE `duration` `duration` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#2235>
ALTER TABLE `il_meta_technical` CHANGE `size` `size` VARCHAR( 4000 ) NULL DEFAULT NULL;
  
<#2236>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_technical');
?>

<#2237>
RENAME TABLE `il_meta_typical_age_range`  TO `il_meta_tar`;

<#2238>
ALTER TABLE `il_meta_tar` CHANGE `meta_typical_age_range_id` `meta_tar_id` INT( 11 ) NOT NULL AUTO_INCREMENT;

<#2239>
ALTER TABLE `il_meta_tar` CHANGE `typical_age_range` `typical_age_range` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#2240>
ALTER TABLE `il_meta_tar` CHANGE `typical_age_range_language` `tar_language` CHAR( 2 ) NULL DEFAULT NULL;

<#2241>
ALTER TABLE `il_meta_tar` CHANGE `typical_age_range_min` `tar_min` CHAR( 2 ) NULL DEFAULT NULL;

<#2242>
ALTER TABLE `il_meta_tar` CHANGE `typical_age_range_max` `tar_max` CHAR( 2 ) NULL DEFAULT NULL;

<#2243>
<?php
    $ilMySQLAbstraction->performAbstraction('il_meta_tar');
?>

<#2244>
ALTER TABLE `reg_access_limitation` CHANGE `limit_mode` `limit_mode` CHAR( 16 ) NOT NULL DEFAULT 'absolute';
 
<#2245>
RENAME TABLE `reg_access_limitation`  TO `reg_access_limit`;

<#2246>
<?php
    $ilMySQLAbstraction->performAbstraction('reg_access_limit');
?>

<#2247>
RENAME TABLE `reg_email_role_assignments`  TO `reg_er_assignments`;

<#2248>
<?php
    $ilMySQLAbstraction->performAbstraction('reg_er_assignments');
?>

<#2249>
<?php
    $ilMySQLAbstraction->performAbstraction('ut_lp_settings');
?>

<#2250>
ALTER TABLE `ut_lp_marks` CHANGE `comment` `comment` VARCHAR( 4000 ) NOT NULL;
  
<#2251>
<?php
    $ilMySQLAbstraction->performAbstraction('ut_lp_marks');
?>

<#2252>
ALTER TABLE `ut_lp_filter` CHANGE `hidden` `hidden` VARCHAR( 4000 ) NOT NULL;

<#2253>
<?php
    $ilMySQLAbstraction->performAbstraction('ut_lp_filter');
?>
  
<#2254>
<?php
    $ilMySQLAbstraction->performAbstraction('search_tree');
?>

<#2255>
ALTER TABLE `search_data` CHANGE `target` `target` VARCHAR( 4000 ) NOT NULL;
  

<#2256>
<?php
    $ilMySQLAbstraction->performAbstraction('search_data');
?>

<#2257>
<?php
    $ilMySQLAbstraction->performAbstraction('ut_lp_collections');
?>

<#2258>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_objective_lm');
?>

<#2259>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_objective_qst');
?>

<#2260>
DROP TABLE IF EXISTS `crs_objective_results`;

<#2261>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_objective_status');
?>

<#2262>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_objective_status_pretest');
?>

<#2263>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_objective_tst');
?>

<#2264>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_objectives');
?>
<#2265>
<?php
    $ilDB->modifyTableColumn("settings", "value", array("type" => "clob", "notnull" => false));
?>

<#2266>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2267>
<?php
    if (!$ilDB->tableColumnExists("payment_prices", "price")) {
        $ilDB->manipulate("ALTER TABLE `payment_prices` ADD `price` FLOAT NOT NULL DEFAULT '0'");
    }
?>
<#2268>
<?php
    $res = $ilDB->query("SELECT * FROM payment_prices");
    
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
        $price_id = $row['price_id'];
        $unit_value = $row['unit_value'];
        $sub_unit_value = $row['sub_unit_value'];
        $price = $unit_value . '.' . $sub_unit_value;
        
        $ilDB->manipulateF(
            'UPDATE payment_prices
			SET price = %s
			WHERE price_id = %s',
            array('float', 'integer'),
            array($price, $price_id)
        );
    }
?>
<#2269>
<?php
    if ($ilDB->tableColumnExists("payment_prices", "unit_value")) {
        $ilDB->manipulate("ALTER TABLE `payment_prices` DROP `unit_value` ");
    }
?>
<#2270>
<?php
    if ($ilDB->tableColumnExists("payment_prices", "sub_unit_value")) {
        $ilDB->manipulate("ALTER TABLE `payment_prices` DROP `sub_unit_value` ");
    }
?>
<#2271>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_prices');
?>
<#2272>
<?php
    if (!$ilDB->tableExists("payment_vats")) {
        $ilDB->createTable(
            "payment_vats",
            array(
                "vat_id" => array(
                    "type" => "integer", "length" => 4, "notnull" => true),
                "vat_title" => array(
                    "type" => "text", "length" => 255, "notnull" => true),
                "vat_rate" => array(
                    "type" => "float", "notnull" => true, "default" => 0)
                ),
            false,
            true
        );
        $ilDB->addPrimaryKey("payment_vats", array("vat_id"));
        $ilDB->createSequence("payment_vats", 1);
    }

?>
<#2273>
<?php
    if ($ilDB->tableColumnExists("payment_settings", "vat_rate")) {
        $ilDB->manipulate("ALTER TABLE `payment_settings` DROP `vat_rate` ");
    }
?>
<#2274>
<?php
    if (!$ilDB->tableColumnExists("payment_objects", "vat_rate")) {
        $ilDB->manipulate("ALTER TABLE `payment_objects` ADD `vat_rate` FLOAT NOT NULL DEFAULT '0'");
    }
    $ilMySQLAbstraction->performAbstraction('payment_objects');
?>
<#2275>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_statistic');
?>

<#2276>
ALTER TABLE `acc_cache` CHANGE `result` `result` TEXT NULL DEFAULT NULL;  

<#2277>
<?php
    $ilMySQLAbstraction->performAbstraction('acc_cache');
?>

<#2278>
<?php
    $ilMySQLAbstraction->performAbstraction('webr_params');
?>
<#2279>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_coupons_codes');
?>
<#2280>
<?php
    $ilDB->manipulate("ALTER TABLE payment_coupons MODIFY pc_type CHAR(8) NOT NULL DEFAULT 'fix'");
?>
<#2281>
<?php
    $ilDB->manipulate("ALTER TABLE payment_coupons MODIFY pc_VALUE FLOAT NOT NULL DEFAULT '0'");
?>
<#2282>
<?php
    $ilDB->manipulate("ALTER TABLE payment_coupons MODIFY usr_id INT NOT NULL DEFAULT '0'");
?>
<#2283>
<?php
    $ilDB->manipulate("ALTER TABLE payment_coupons MODIFY pc_last_change_usr_id INT NOT NULL DEFAULT '0'");
?>
<#2284>
<?php
    $ilDB->manipulate("ALTER TABLE payment_coupons MODIFY pc_description VARCHAR(4000) NOT NULL");
?>
<#2285>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_coupons');
?>
<#2286>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_coupons_obj');
?>
<#2287>
<?php
    $ilDB->manipulate("ALTER TABLE payment_coupons_track MODIFY usr_id INT NOT NULL DEFAULT '0'");
?>
<#2288>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_coupons_track');
?>
<#2289>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_shopping_cart');
?>
<#2290>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_statistic_coup');
?>
<#2291>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_topics');
?>
<#2292>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_trustees');
?>
<#2293>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_vendors');
?>
<#2294>
<?php
    $ilMySQLAbstraction->performAbstraction('payment_currencies');
?>

<#2295>
ALTER TABLE `webr_items` CHANGE `description` `description` VARCHAR( 4000 ) NOT NULL;  

<#2296>
ALTER TABLE `webr_items` CHANGE `target` `target` VARCHAR( 4000 ) NULL DEFAULT NULL;
  
<#2297>
<?php
    $ilMySQLAbstraction->performAbstraction('webr_items');
?>
<#2298>
<?php
$ilMySQLAbstraction->performAbstraction('chat_blocked');
?>
<#2299>
<?php

$ilDB->manipulate("ALTER TABLE `chat_records` CHANGE `description` `description` VARCHAR(4000) NULL DEFAULT NULL");
$ilMySQLAbstraction->performAbstraction('chat_records');
?>
<#2300>
<?php
    $ilDB->manipulate("ALTER TABLE `chat_record_data` CHANGE `message` `message` VARCHAR(4000) NULL DEFAULT NULL");
    $ilMySQLAbstraction->performAbstraction('chat_record_data');
?>

<#2301>
<?php
    $ilMySQLAbstraction->performAbstraction('chat_rooms');
?>
<#2302>
<?php
    $ilDB->manipulate("ALTER TABLE `chat_room_messages` CHANGE `message` `message` VARCHAR(4000) NULL DEFAULT NULL");
    $ilDB->manipulate("ALTER TABLE `chat_room_messages` CHANGE `commit_timestamp` `commit_timestamp` INT NOT NULL DEFAULT '0'");
    $ilMySQLAbstraction->performAbstraction('chat_room_messages');
?>

<#2303>
<?php
    $ilMySQLAbstraction->performAbstraction('chat_user');
?>
<#2304>
<?php
    $fields = array(
        'smiley_id' => array(
            'type' => 'integer',
            'length' => 4,
        ),
        'smiley_keywords' => array(
            'type' => 'text',
            'length' => 100,
        ),
        'smiley_path' => array(
            'type' => 'text',
            'length' => 200,
        )
    );
    
    $ilDB->createTable('chat_smilies', $fields);
    $ilDB->addPrimaryKey('chat_smilies', array('smiley_id'));
    $ilDB->createSequence('chat_smilies');
?>

<#2305>
<?php
    $ilDB->addIndex('il_meta_format', array('format'), 'i2');
?>

<#2306>
<?php
    $ilDB->modifyTableColumn('il_meta_format', 'format', array('type' => 'text','length' => 255, 'notnull' => false));
?>
<#2307>
<?php

// accessibility settings

// register new object type 'accs' for accessibility settings
$id = $ilDB->nextId("object_data");
$ilDB->manipulateF(
    "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) " .
        "VALUES (%s, %s, %s, %s, %s, %s, %s)",
    array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
    array($id, "typ", "accs", "Accessibility settings", -1, ilUtil::now(), ilUtil::now())
);
$typ_id = $id;

// create object data entry
$id = $ilDB->nextId("object_data");
$ilDB->manipulateF(
    "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) " .
        "VALUES (%s, %s, %s, %s, %s, %s, %s)",
    array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
    array($id, "accs", "__AccessibilitySettings", "Accessibility Settings", -1, ilUtil::now(), ilUtil::now())
);

// create object reference entry
$ref_id = $ilDB->nextId('object_reference');
$res = $ilDB->manipulateF(
    "INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
    array("integer", "integer"),
    array($ref_id, $id)
);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

// add rbac operations
// 1: edit_permissions, 2: visible, 3: read, 4:write
$ilDB->manipulateF(
    "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
    array("integer", "integer"),
    array($typ_id, 1)
);
$ilDB->manipulateF(
    "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
    array("integer", "integer"),
    array($typ_id, 2)
);
$ilDB->manipulateF(
    "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
    array("integer", "integer"),
    array($typ_id, 3)
);
$ilDB->manipulateF(
    "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
    array("integer", "integer"),
    array($typ_id, 4)
);
?>
<#2308>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#2309>
<?php
    $fields = array(
        'lang_key' => array(
            'type' => 'text',
            'fixed' => true,
            'length' => 2,
            'notnull' => true
        ),
        'function_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'access_key' => array(
            'type' => 'text',
            'fixed' => true,
            'length' => 1
        )
    );
    
    $ilDB->createTable('acc_access_key', $fields);
    $ilDB->addPrimaryKey('acc_access_key', array('lang_key','function_id'));
?>

<#2310>
<?php
    $fields = array(
        'user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'function_id' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true
        ),
        'access_key' => array(
            'type' => 'text',
            'fixed' => true,
            'length' => 1
        )
    );
    
    $ilDB->createTable('acc_user_access_key', $fields);
    $ilDB->addPrimaryKey('acc_user_access_key', array('user_id','function_id'));
?>
<#2311>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#2312>
ALTER TABLE `crs_settings` CHANGE `syllabus` `syllabus` VARCHAR( 4000 ) NULL DEFAULT NULL;

<#2313>
ALTER TABLE `crs_settings` CHANGE `contact_consultation` `contact_consultation` VARCHAR( 4000 ) NULL DEFAULT NULL;
  
<#2314>
ALTER TABLE `crs_settings` CHANGE `important` `important` VARCHAR( 4000 ) NOT NULL;

<#2315>
ALTER TABLE `crs_settings` CHANGE `subscription_limitation_type` `sub_limitation_type` TINYINT( 1 ) NOT NULL DEFAULT '0' ;

<#2316>
ALTER TABLE `crs_settings` CHANGE `subscription_start` `sub_start` INT( 11 ) NULL DEFAULT NULL;

<#2317>
ALTER TABLE `crs_settings` CHANGE `subscription_end` `sub_end` INT( 11 ) NULL DEFAULT NULL;

<#2318>
ALTER TABLE `crs_settings` CHANGE `subscription_type` `sub_type` INT( 2 ) NULL DEFAULT NULL;

<#2319>
ALTER TABLE `crs_settings` CHANGE `subscription_password` `sub_password` VARCHAR( 32 ) NULL DEFAULT NULL;

<#2320>
ALTER TABLE `crs_settings` CHANGE `subscription_membership_limitation` `sub_mem_limit` TINYINT( 1 ) NOT NULL DEFAULT '0';

<#2321>
ALTER TABLE `crs_settings` CHANGE `subscription_max_members` `sub_max_members` INT( 4 ) NULL DEFAULT NULL;

<#2322>
ALTER TABLE `crs_settings` CHANGE `subscription_notify` `sub_notify` INT( 2 ) NULL DEFAULT NULL;  

<#2323>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_settings');
?>

<#2324>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_start');
?>

<#2325>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_timings_planed');
?>

<#2326>
ALTER TABLE `crs_timings_usr_accept` CHANGE `remark` `remark` VARCHAR( 4000 ) NOT NULL;

<#2327>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_timings_usr_accept');
?>

<#2328>
ALTER TABLE `crs_user_data` CHANGE `value` `value` VARCHAR( 4000 ) NOT NULL;  

<#2329>
<?php
    $ilMySQLAbstraction->performAbstraction('crs_user_data');
?>

<#2330>
<?php
    $ilMySQLAbstraction->performAbstraction('member_agreement');
?>

<#2331>
ALTER TABLE `member_export_user_settings` CHANGE `settings` `settings` VARCHAR( 4000 ) NOT NULL;

<#2332>
RENAME TABLE `member_export_user_settings`  TO `member_usr_settings`;

<#2333>
<?php
    $ilMySQLAbstraction->performAbstraction('member_usr_settings');
?>

<#2334>
<?php
    $ilMySQLAbstraction->performAbstraction('file_data');
?>

<#2335>
DROP TABLE `grp_data`;

<#2336>
ALTER TABLE `grp_settings` CHANGE `information` `information` VARCHAR( 4000 ) NOT NULL;

<#2337>
ALTER TABLE `grp_settings` CHANGE `registration_membership_limited` `registration_mem_limit` TINYINT( 4 ) NOT NULL DEFAULT '0'; 

<#2338>
<?php
    $ilMySQLAbstraction->performAbstraction('grp_settings');
?>

<#2339>
ALTER TABLE `remote_course_settings` CHANGE `local_information` `local_information` VARCHAR( 4000 ) NOT NULL;

<#2340>
ALTER TABLE `remote_course_settings` CHANGE `remote_link` `remote_link` VARCHAR( 4000 ) NOT NULL;

<#2341>  
ALTER TABLE `remote_course_settings` CHANGE `organization` `organization` VARCHAR( 4000 ) NOT NULL;

<#2342>
<?php
    $ilMySQLAbstraction->performAbstraction('remote_course_settings');
?>

<#2343>
ALTER TABLE `event` CHANGE `description` `description` VARCHAR( 4000 ) NOT NULL;
 
<#2344>
ALTER TABLE `event` CHANGE `location` `location` VARCHAR( 4000 ) NOT NULL;

<#2345>
ALTER TABLE `event` CHANGE `tutor_name` `tutor_name` VARCHAR( 4000 ) NOT NULL;

<#2346>
ALTER TABLE `event` CHANGE `details` `details` VARCHAR( 4000 ) NOT NULL;

<#2347>
<?php
    $ilMySQLAbstraction->performAbstraction('event');
?>

<#2348>
<?php
    $ilMySQLAbstraction->performAbstraction('event_appointment');
?>

<#2349>
<?php
    $ilMySQLAbstraction->performAbstraction('event_file');
?>

<#2350>
<?php
    $ilMySQLAbstraction->performAbstraction('event_items');
?>

<#2351>
<?php
    $ilMySQLAbstraction->performAbstraction('event_participants');
?>

<#2352>
<?php
    $ilDB->addPrimaryKey('webr_items', array('link_id'));
?>

<#2353>
<?php
    $ilDB->renameTable('container_sorting_settings', 'container_sorting_set');
?>

<#2354>
<?php
    $ilDB->renameTable('crs_objective_status_pretest', 'crs_objective_status_p');
?>

<#2355>
<?php
    $ilDB->renameTableColumn('event_appointment', 'start', 'e_start');
?>

<#2356>
<?php
    $ilDB->renameTableColumn('event_appointment', 'end', 'e_end');
?>

<#2357>
<?php
    $ilDB->renameTableColumn('event_participants', 'comment', 'e_comment');
?>

<#2358>
<?php
    $ilDB->renameTableColumn('file_data', 'mode', 'f_mode');
?>

<#2359>
<?php
    $ilDB->renameTableColumn('il_meta_annotation', 'date', 'a_date');
?>

<#2360>
<?php
    $ilDB->renameTableColumn('il_meta_contribute', 'date', 'c_date');
?>

<#2361>
<?php
    $ilDB->renameTableColumn('il_meta_technical', 'size', 't_size');
?>

<#2362>
<?php
    $ilDB->renameTableColumn('remote_course_settings', 'start', 'r_start');
?>

<#2363>
<?php
    $ilDB->renameTableColumn('remote_course_settings', 'end', 'r_end');
?>

<#2364>
<?php
    $ilDB->renameTableColumn('ut_lp_marks', 'comment', 'u_comment');
?>

<#2365>
<?php
    $ilDB->renameTableColumn('ut_lp_settings', 'mode', 'u_mode');
?>

<#2366>
<?php
    $ilDB->renameTable('settings_deactivated_styles', 'settings_deactivated_s');
?>

<#2367>
<?php
    $ilDB->dropIndex('ut_lp_marks', 'i1');
?>
<#2368>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#2369>
<?php
    $ilMySQLAbstraction->performAbstraction('usr_search');
?>

<#2370>
ALTER TABLE `svy_qst_mat` DROP INDEX `c1_idx`;

<#2371>
<?php
    $ilDB->addPrimaryKey("svy_qst_mat", array("material_id"));
?>

<#2372>
<?php
$ilMySQLAbstraction->performAbstraction('svy_qst_mat');
?>

<#2373>
<?php
$ilDB->addIndex('svy_qst_mat', array('question_fi'), 'i1');
?>

<#2374>
<?php
    $ilMySQLAbstraction->performAbstraction('catch_write_events');
?>

<#2375>
<?php
    $ilMySQLAbstraction->performAbstraction('search_command_queue');
?>

<#2376>
<?php
    $ilMySQLAbstraction->performAbstraction('frm_data');
?>

<#2377>
<?php

    $ilMySQLAbstraction->performAbstraction('frm_notification');
?>

<#2378>
<?php
$ilDB->manipulate("ALTER TABLE `frm_posts` CHANGE `pos_message` `pos_message` LONGTEXT NULL default NULL");
?>

<#2379>
ALTER TABLE `frm_posts` CHANGE `pos_subject` `pos_subject` VARCHAR( 4000 ) NULL DEFAULT NULL;  

<#2380>
ALTER TABLE `frm_posts` CHANGE `pos_cens_com` `pos_cens_com` VARCHAR( 4000 ) NULL DEFAULT NULL;  

<#2381>
ALTER TABLE `frm_posts` CHANGE `import_name` `import_name` VARCHAR( 4000 ) NULL DEFAULT NULL;  


<#2382>
<?php
    $ilMySQLAbstraction->performAbstraction('frm_posts');
?>

<#2383>
<?php
    $ilMySQLAbstraction->performAbstraction('frm_posts_tree');
?>

<#2384>
ALTER TABLE `frm_threads` CHANGE `import_name` `import_name` VARCHAR( 4000 ) NULL DEFAULT NULL;  	

<#2385>
<?php
    $ilMySQLAbstraction->performAbstraction('frm_threads');
?>

<#2386>
<?php
    $ilMySQLAbstraction->performAbstraction('frm_user_read');
?>

<#2387>
<?php
    $ilMySQLAbstraction->performAbstraction('frm_settings');
?>
<#2388>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#2389>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#2390>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#2391>
ALTER TABLE `payment_coupons_codes` DROP INDEX `i2_idx`; 

<#2392>
ALTER TABLE `payment_statistic` CHANGE `access` `access_granted` INT( 11 ) NOT NULL DEFAULT '0';
<#2393>
<?php
    if (!$ilDB->tableExists("style_color")) {
        $fields = array(
            'style_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ),
            'color_name' => array(
                'type' => 'text',
                'length' => 30,
                'fixed' => false,
                'default' => ".",
                'notnull' => true
            ),
            'color_code' => array(
                'type' => 'text',
                'fixed' => true,
                'length' => 10,
                'notnull' => false
            )
        );
        
        $ilDB->createTable('style_color', $fields);
        $ilDB->addPrimaryKey('style_color', array('style_id','color_name'));
    }
?>

<#2394>
ALTER TABLE `loginname_history` CHANGE `date` `history_date` INT( 11 ) NOT NULL; 

<#2395>
ALTER TABLE `frm_posts_tree` CHANGE `date` `fpt_date` DATETIME NULL DEFAULT NULL;

<#2396>
<?php
$setting = new ilSetting();
$se_db = (int) $setting->get("se_db");

if ($se_db <= 47) {
    $set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
    while ($rec = $ilDB->fetchAssoc($set)) {
        $set2 = $ilDB->query("SELECT * FROM style_char WHERE " .
            "style_id = " . $ilDB->quote($rec["obj_id"], "integer") . " AND " .
            "characteristic = " . $ilDB->quote("FileListItemLink", "text") . " AND " .
            "type = " . $ilDB->quote("flist_a", "text"));
        if (!$ilDB->fetchAssoc($set2)) {
            $ilDB->manipulate("INSERT INTO style_char (style_id, type, characteristic)" .
                " VALUES (" .
                $ilDB->quote($rec["obj_id"], "integer") . "," .
                $ilDB->quote("flist_a", "text") . "," .
                $ilDB->quote("FileListItemLink", "text") . ")");
        }
    }
}
?>

<#2397>
<?php
if (!$ilDB->tableColumnExists("style_char", "hide")) {
    $ilDB->query("ALTER TABLE style_char ADD COLUMN hide TINYINT NOT NULL DEFAULT 0");
}
?>

<#2398>
<?php
$setting = new ilSetting();
$se_db = (int) $setting->get("se_db");

if ($se_db <= 49) {
    $set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
    while ($rec = $ilDB->fetchAssoc($set)) {
        $set2 = $ilDB->query("SELECT * FROM style_char WHERE " .
            "style_id = " . $ilDB->quote($rec["obj_id"], "integer") . " AND " .
            "characteristic = " . $ilDB->quote("Important", "text") . " AND " .
            "type = " . $ilDB->quote("text_inline", "text"));
        if (!$ilDB->fetchAssoc($set2)) {
            $ilDB->manipulate("INSERT INTO style_char (style_id, type, characteristic)" .
                " VALUES (" .
                $ilDB->quote($rec["obj_id"], "integer") . "," .
                $ilDB->quote("text_inline", "text") . "," .
                $ilDB->quote("Important", "text") . ")");
            $nid = $ilDB->nextId("style_parameter");
            $ilDB->manipulate("INSERT INTO style_parameter (id, style_id, type, class, tag, parameter, value)" .
                " VALUES (" .
                $ilDB->quote($nid, "integer") . "," .
                $ilDB->quote($rec["obj_id"], "integer") . "," .
                $ilDB->quote("text_inline", "text") . "," .
                $ilDB->quote("Important", "text") . "," .
                $ilDB->quote("span", "text") . "," .
                $ilDB->quote("text-decoration", "text") . "," .
                $ilDB->quote("underline", "text") .
                ")");
        }
        $set2 = $ilDB->query("SELECT * FROM style_char WHERE " .
            "style_id = " . $ilDB->quote($rec["obj_id"], "integer") . " AND " .
            "characteristic = " . $ilDB->quote("Accent", "text") . " AND " .
            "type = " . $ilDB->quote("text_inline", "text"));
        if (!$ilDB->fetchAssoc($set2)) {
            $ilDB->manipulate("INSERT INTO style_char (style_id, type, characteristic)" .
                " VALUES (" .
                $ilDB->quote($rec["obj_id"], "integer") . "," .
                $ilDB->quote("text_inline", "text") . "," .
                $ilDB->quote("Accent", "text") . ")");
            $nid = $ilDB->nextId("style_parameter");
            $ilDB->manipulate("INSERT INTO style_parameter (id, style_id, type, class, tag, parameter, value)" .
                " VALUES (" .
                $ilDB->quote($nid, "integer") . "," .
                $ilDB->quote($rec["obj_id"], "integer") . "," .
                $ilDB->quote("text_inline", "text") . "," .
                $ilDB->quote("Accent", "text") . "," .
                $ilDB->quote("span", "text") . "," .
                $ilDB->quote("color", "text") . "," .
                $ilDB->quote("#E000E0", "text") .
                ")");
        }
    }
}
?>

<#2399>
<?php
if (!$ilDB->tableExists("style_template_class")) {
    $fields = array(
        'template_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'class_type' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => true,
            'notnull' => false
        ),
        'class' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => true,
            'notnull' => false
        )
    );
    
    $ilDB->createTable('style_template_class', $fields);
}
?>

<#2400>
<?php
if (!$ilDB->tableExists("style_template")) {
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'style_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'name' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => false,
            'notnull' => true
        ),
        'preview' => array(
            'type' => 'text',
            'length' => 4000,
            'fixed' => false,
            'notnull' => false
        ),
        'temp_type' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => false,
            'notnull' => false
        )
    );
    
    $ilDB->createTable('style_template', $fields);
    $ilDB->addPrimaryKey('style_template', array('id'));
}
$ilDB->createSequence('style_template');
?>

<#2401>
<?php
if (!$ilDB->tableExists("page_style_usage")) {
    $fields = array(
        'page_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'page_type' => array(
            'type' => 'text',
            'length' => 10,
            'fixed' => true,
            'notnull' => true
        ),
        'page_nr' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'template' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'stype' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => false,
            'notnull' => false
        ),
        'sname' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => true,
            'notnull' => false
        )
    );
    
    $ilDB->createTable('page_style_usage', $fields);
}
?>

<#2402>
<?php
$setting = new ilSetting();
$se_db = (int) $setting->get("se_db");

if ($se_db <= 56) {
    $set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
    while ($rec = $ilDB->fetchAssoc($set)) {	// all styles
        $ast = array(
            array("tag" => "div", "type" => "va_cntr", "class" => "VAccordCntr",
                "par" => array(
                    array("name" => "margin-top", "value" => "5px")
                    )),
            array("tag" => "div", "type" => "va_icntr", "class" => "VAccordICntr",
                "par" => array(
                    array("name" => "background-color", "value" => "#FFFFFF"),
                    array("name" => "margin-bottom", "value" => "5px"),
                    array("name" => "border-width", "value" => "1px"),
                    array("name" => "border-color", "value" => "#9EADBA"),
                    array("name" => "border-style", "value" => "solid")
                    )),
            array("tag" => "div", "type" => "va_ihead", "class" => "VAccordIHead",
                "par" => array(
                    array("name" => "padding-left", "value" => "24px"),
                    array("name" => "padding-right", "value" => "3px"),
                    array("name" => "padding-bottom", "value" => "3px"),
                    array("name" => "padding-top", "value" => "3px"),
                    array("name" => "background-color", "value" => "#E2EAF4"),
                    array("name" => "text-align", "value" => "left"),
                    array("name" => "cursor", "value" => "pointer"),
                    array("name" => "background-image", "value" => "accordion_arrow.gif"),
                    array("name" => "background-repeat", "value" => "no-repeat"),
                    array("name" => "background-position", "value" => "3px 4px"),
                    )),
            array("tag" => "div", "type" => "va_ihead", "class" => "VAccordIHead:hover",
                "par" => array(
                    array("name" => "background-color", "value" => "#D2D8E2")
                    )),
            array("tag" => "div", "type" => "va_icont", "class" => "VAccordICont",
                "par" => array(
                    array("name" => "background-color", "value" => "#FFFFFF"),
                    array("name" => "padding", "value" => "3px")
                    )),

            array("tag" => "div", "type" => "ha_cntr", "class" => "HAccordCntr",
                "par" => array(
                    )),
            array("tag" => "div", "type" => "ha_icntr", "class" => "HAccordICntr",
                "par" => array(
                    array("name" => "background-color", "value" => "#FFFFFF"),
                    array("name" => "margin-right", "value" => "5px"),
                    array("name" => "border-width", "value" => "1px"),
                    array("name" => "border-color", "value" => "#9EADBA"),
                    array("name" => "border-style", "value" => "solid")
                    )),
            array("tag" => "div", "type" => "ha_ihead", "class" => "HAccordIHead",
                "par" => array(
                    array("name" => "padding-left", "value" => "20px"),
                    array("name" => "padding-right", "value" => "10px"),
                    array("name" => "padding-bottom", "value" => "3px"),
                    array("name" => "padding-top", "value" => "3px"),
                    array("name" => "background-color", "value" => "#E2EAF4"),
                    array("name" => "text-align", "value" => "left"),
                    array("name" => "cursor", "value" => "pointer"),
                    array("name" => "background-image", "value" => "haccordion_arrow.gif"),
                    array("name" => "background-repeat", "value" => "no-repeat"),
                    array("name" => "background-position", "value" => "3px 4px"),
                    )),
            array("tag" => "div", "type" => "ha_ihead", "class" => "HAccordIHead:hover",
                "par" => array(
                    array("name" => "background-color", "value" => "#D2D8E2")
                    )),
            array("tag" => "div", "type" => "ha_icont", "class" => "HAccordICont",
                "par" => array(
                    array("name" => "background-color", "value" => "#FFFFFF"),
                    array("name" => "padding", "value" => "3px")
                    )),
                    );

        foreach ($ast as $st) {
            $set2 = $ilDB->query("SELECT * FROM style_char WHERE " .
                "style_id = " . $ilDB->quote($rec["obj_id"], "integer") . " AND " .
                "characteristic = " . $ilDB->quote($st["class"], "text") . " AND " .
                "type = " . $ilDB->quote($st["type"], "text"));
            if (!$ilDB->fetchAssoc($set2)) {
                $q = "INSERT INTO style_char (style_id, type, characteristic)" .
                    " VALUES (" .
                    $ilDB->quote($rec["obj_id"], "integer") . "," .
                    $ilDB->quote($st["type"], "text") . "," .
                    $ilDB->quote($st["class"], "text") . ")";

                $ilDB->manipulate($q);
                foreach ($st["par"] as $par) {
                    $nid = $ilDB->nextId("style_parameter");
                    $q = "INSERT INTO style_parameter (id, style_id, type, class, tag, parameter, value)" .
                        " VALUES (" .
                        $ilDB->quote($nid, "integer") . "," .
                        $ilDB->quote($rec["obj_id"], "integer") . "," .
                        $ilDB->quote($st["type"], "text") . "," .
                        $ilDB->quote($st["class"], "text") . "," .
                        $ilDB->quote($st["tag"], "text") . "," .
                        $ilDB->quote($par["name"], "text") . "," .
                        $ilDB->quote($par["value"], "text") .
                        ")";

                    $ilDB->manipulate($q);
                }
            }
        }
    }
}
?>

<#2403>
<?php
$setting = new ilSetting();
$se_db = (int) $setting->get("se_db");

if ($se_db <= 57) {
    $set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
    while ($rec = $ilDB->fetchAssoc($set)) {	// all styles
        $ast = array(
            array("type" => "vaccordion", "name" => "VerticalAccordion",
                "class" => array(
                    array("class_type" => "va_cntr", "class" => "VAccordCntr"),
                    array("class_type" => "va_icntr", "class" => "VAccordICntr"),
                    array("class_type" => "va_ihead", "class" => "VAccordIHead"),
                    array("class_type" => "va_icont", "class" => "VAccordICont")
                    )),
            array("type" => "haccordion", "name" => "HorizontalAccordion",
                "class" => array(
                    array("class_type" => "ha_cntr", "class" => "HAccordCntr"),
                    array("class_type" => "ha_icntr", "class" => "HAccordICntr"),
                    array("class_type" => "ha_ihead", "class" => "HAccordIHead"),
                    array("class_type" => "ha_icont", "class" => "HAccordICont")
                    ))
                    );

        foreach ($ast as $st) {
            $set2 = $ilDB->query("SELECT * FROM style_template WHERE " .
                "style_id = " . $ilDB->quote($rec["obj_id"], "integer") . " AND " .
                "temp_type = " . $ilDB->quote($st["type"], "text") . " AND " .
                "name = " . $ilDB->quote($st["name"], "text"));
            if (!$ilDB->fetchAssoc($set2)) {
                $nid = $ilDB->nextId("style_template");
                $q = "INSERT INTO style_template (id, style_id, name, temp_type)" .
                    " VALUES (" .
                    $ilDB->quote($nid, "integer") . "," .
                    $ilDB->quote($rec["obj_id"], "integer") . "," .
                    $ilDB->quote($st["name"], "text") . "," .
                    $ilDB->quote($st["type"], "text") . ")";
                $ilDB->manipulate($q);
                $tid = $ilDB->getLastInsertId();

                foreach ($st["class"] as $c) {
                    $q = "INSERT INTO style_template_class (template_id, class_type, class)" .
                        " VALUES (" .
                        $ilDB->quote($tid, "integer") . "," .
                        $ilDB->quote($c["class_type"], "text") . "," .
                        $ilDB->quote($c["class"], "text") .
                        ")";
                    $ilDB->manipulate($q);
                }
            }
        }
    }
}
?>

<#2404>
<?php
$setting = new ilSetting();
$se_db = (int) $setting->get("se_db");

if ($se_db <= 58) {
    $set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");

    while ($rec = $ilDB->fetchAssoc($set)) {	// all styles
        $imgs = array("accordion_arrow.gif", "haccordion_arrow.gif");

        $a_style_id = $rec["obj_id"];

        $sty_data_dir = CLIENT_WEB_DIR . "/sty";
        ilUtil::makeDir($sty_data_dir);

        $style_dir = $sty_data_dir . "/sty_" . $a_style_id;
        ilUtil::makeDir($style_dir);

        // create images subdirectory
        $im_dir = $style_dir . "/images";
        ilUtil::makeDir($im_dir);

        // create thumbnails directory
        $thumb_dir = $style_dir . "/images/thumbnails";
        ilUtil::makeDir($thumb_dir);

        //	ilObjStyleSheet::_createImagesDirectory($rec["obj_id"]);
        $imdir = CLIENT_WEB_DIR . "/sty/sty_" . $a_style_id .
                "/images";
        foreach ($imgs as $cim) {
            if (!is_file($imdir . "/" . $cim)) {
                copy("./Services/Style/basic_style/images/" . $cim, $imdir . "/" . $cim);
            }
        }
    }
}
?>

<#2405>
<?php
if (!$ilDB->tableExists("style_setting")) {
    $fields = array(
        'style_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'name' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => false,
            'notnull' => true
        ),
        'value' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => false,
            'notnull' => false
        )
    );
    
    $ilDB->createTable('style_setting', $fields);
    $ilDB->addPrimaryKey('style_setting', array('style_id', 'name'));
}
?>

<#2406>
<?php
$setting = new ilSetting();
$se_db = (int) $setting->get("se_db");

if ($se_db <= 60) {
    $set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
    while ($rec = $ilDB->fetchAssoc($set)) {	// all styles
        $ast = array(
            array("tag" => "div", "type" => "sco_desct", "class" => "DescriptionTop",
                "par" => array()),
            array("tag" => "div", "type" => "sco_objt", "class" => "ObjectiveTop",
                "par" => array())
                    );

        foreach ($ast as $st) {
            $set2 = $ilDB->query("SELECT * FROM style_char WHERE " .
                "style_id = " . $ilDB->quote($rec["obj_id"], "integer") . " AND " .
                "characteristic = " . $ilDB->quote($st["class"], "text") . " AND " .
                "type = " . $ilDB->quote($st["type"], "text"));
            if (!$ilDB->fetchAssoc($set2)) {
                $q = "INSERT INTO style_char (style_id, type, characteristic)" .
                    " VALUES (" .
                    $ilDB->quote($rec["obj_id"], "integer") . "," .
                    $ilDB->quote($st["type"], "text") . "," .
                    $ilDB->quote($st["class"], "text") . ")";
                $ilDB->manipulate($q);
                foreach ($st["par"] as $par) {
                    $nid = $ilDB->nextId("style_parameter");
                    $q = "INSERT INTO style_parameter (id, style_id, type, class, tag, parameter, value)" .
                        " VALUES (" .
                        $ilDB->quote($nid, "integer") . "," .
                        $ilDB->quote($rec["obj_id"], "integer") . "," .
                        $ilDB->quote($st["type"], "text") . "," .
                        $ilDB->quote($st["class"], "text") . "," .
                        $ilDB->quote($st["tag"], "text") . "," .
                        $ilDB->quote($par["name"], "text") . "," .
                        $ilDB->quote($par["value"], "text") .
                        ")";
                    $ilDB->manipulate($q);
                }
            }
        }
    }
}
?>

<#2407>
<?php
if (!$ilDB->tableExists("page_editor_settings")) {
    $fields = array(
        'settings_grp' => array(
            'type' => 'text',
            'length' => 10,
            'fixed' => false,
            'notnull' => true
        ),
        'name' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => false,
            'notnull' => true
        ),
        'value' => array(
            'type' => 'text',
            'length' => 30,
            'fixed' => false,
            'notnull' => false
        )
    );
    
    $ilDB->createTable('page_editor_settings', $fields);
    $ilDB->addPrimaryKey('page_editor_settings', array('settings_grp', 'name'));
}
?>
<#2408>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2409>
<?php
    $ilMySQLAbstraction->performAbstraction('addressbook');
?>

<#2410>
ALTER TABLE `addressbook_mlist` CHANGE `description` `description` VARCHAR( 4000 ) NOT NULL;

<#2411>
<?php
    $ilMySQLAbstraction->performAbstraction('addressbook_mlist');
?>

<#2412>
<?php
    $ilMySQLAbstraction->performAbstraction('addressbook_mlist_ass');
?>


<#2413>
ALTER TABLE `mail_attachment` CHANGE `path` `path` VARCHAR( 4000 ) NOT NULL;

<#2414>
<?php
    $ilMySQLAbstraction->performAbstraction('mail_attachment');
?>

<#2415>
<?php
// ALTER TABLE `mail_saved` CHANGE `rcp_to` `rcp_to` VARCHAR( 4000 ) NULL DEFAULT NULL;
?>
<#2416>
<?php
// ALTER TABLE `mail_saved` CHANGE `rcp_cc` `rcp_cc` VARCHAR( 4000 ) NULL DEFAULT NULL;
?>
<#2417>
<?php
// ALTER TABLE `mail_saved` CHANGE `rcp_bcc` `rcp_bcc` VARCHAR( 4000 ) NULL DEFAULT NULL;
?>
<#2418>
<?php
$ilDB->manipulate("ALTER TABLE `mail_saved` CHANGE `m_message` `m_message` LONGTEXT NULL default NULL");
?>
<#2419>
<?php
    $ilMySQLAbstraction->performAbstraction('mail_saved');
?>
<#2420>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2421>
<?php
if (!$ilDB->tableExists("mep_data")) {
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'default_width' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ),
        'default_height' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        )
    );
    
    $ilDB->createTable('mep_data', $fields);
    $ilDB->addPrimaryKey('mep_data', array('id'));
}
?>
<#2422>
<?php

// create a mep_data entry for all media pools
$set = $ilDB->query("SELECT * FROM object_data WHERE type = " . $ilDB->quote("mep", "text"));
while ($rec = $ilDB->fetchAssoc($set)) {
    $set = $ilDB->query(
        "SELECT * FROM mep_data " .
        " WHERE id = " . $ilDB->quote($rec["obj_id"], "integer")
    );
    if (!$ilDB->fetchAssoc($set)) {
        $ilDB->manipulate("INSERT INTO mep_data " .
            "(id) VALUES (" .
            $ilDB->quote($rec["obj_id"], "integer") .
            ")");
    }
}

?>
<#2423>
<?php
$atts = array(
    'type' => 'text',
    'length' => 100,
    'notnull' => false,
    'fixed' => false
);
$ilDB->addTableColumn("lm_data", "layout", $atts);
?>
<#2424>
<?php
$atts = array(
    'type' => 'integer',
    'length' => 1,
    'notnull' => false
);
$ilDB->addTableColumn("content_object", "layout_per_page", $atts);
?>
<#2425>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2426>
<?php
$atts = array(
    'type' => 'text',
    'length' => 20,
    'notnull' => false,
    'fixed' => false
);
$ilDB->addTableColumn("il_plugin", "plugin_id", $atts);
?>
<#2427>
<?php
$atts = array(
    'type' => 'text',
    'length' => 250,
    'notnull' => false,
    'fixed' => false
);
$ilDB->addTableColumn("ctrl_classfile", "plugin_path", $atts);
?>
<#2428>
<?php
    $res = $ilDB->manipulate("ALTER TABLE `payment_prices` ADD `unlimited_duration` tinyint(4) NOT NULL default '0'");
?>
<#2429>
ALTER TABLE `payment_objects` CHANGE `vat_rate` `vat_id` INT NOT NULL DEFAULT '0';
<#2430>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2431>
<?php
    $ilMySQLAbstraction->performAbstraction('mail_tree');
?>
<#2432>
<?php
$atts = array(
    'type' => 'text',
    'length' => 4,
    'fixed' => false,
    'notnull' => false
);
$ilDB->addTableColumn("ctrl_classfile", "cid", $atts);
?>
<#2433>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2434>
<?php
$atts = array(
    'type' => 'integer',
    'length' => 1,
    'notnull' => true,
    'default' => 0
);
$ilDB->addTableColumn("page_object", "is_empty", $atts);
?>
<#2435>
<?php
    $ilDB->addIndex('ctrl_classfile', array('cid'), 'i1');
?>
<#2436>
<?php
$atts = array(
    'type' => 'timestamp'
);
$ilDB->addTableColumn("frm_thread_access", "access_old_ts", $atts);
?>
<#2437>
<?php
$set = $ilDB->query(
    "SELECT * FROM frm_thread_access "
);
while ($rec = $ilDB->fetchAssoc($set)) {
    $ilDB->manipulate(
        "UPDATE frm_thread_access SET " .
        " access_old_ts = " . $ilDB->quote(date('Y-m-d H:i:s', $rec["access_old"]), "timestamp") .
        " WHERE usr_id = " . $ilDB->quote($rec["usr_id"], "integer") .
        " AND obj_id = " . $ilDB->quote($rec["obj_id"], "integer") .
        " AND thread_id = " . $ilDB->quote($rec["thread_id"], "integer")
    );
}
?>
<#2438>
ALTER TABLE write_event
DROP PRIMARY KEY,
ADD COLUMN write_id int(11) NOT NULL AUTO_INCREMENT,
ADD PRIMARY KEY (write_id);

<#2439>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#2440>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

ALTER TABLE `tmp_migration` ADD INDEX `obj_passed` ( `obj_id` ,`passed` );



<#2441>
<?php

    global $ilLog;
    $ilLog->write($wd);

    include_once('./Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
    include_once('./Services/Migration/DBUpdate_904/classes/class.ilFSStorageFile.php');
    include_once('./Services/Migration/DBUpdate_904/classes/class.ilFSStorageEvent.php');

    
    $query = "SELECT * FROM event_file";
    $res = $ilDB->query($query);
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        // Check if done
        $query = "SELECT * FROM tmp_migration WHERE obj_id = " . $row->file_id . " AND passed = 1";
        $tmp = $ilDB->query($query);
        if ($tmp->numRows()) {
            continue;
        }
        
        // Find course ref_id
        $query = "SELECT ref_id,event.obj_id obj_id FROM event JOIN event_file ON event.event_id = event_file.event_id " .
            "JOIN object_reference ore ON event.obj_id = ore.obj_id " .
            "WHERE event.event_id = " . $row->event_id . " ";
        $sess = $ilDB->query($query);
        while ($sess_row = $sess->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sess_ref_id = $sess_row->ref_id;
            $sess_obj_id = $sess_row->obj_id;
            
            $query = "SELECT parent FROM tree WHERE child = " . $sess_row->ref_id;
            $crs = $ilDB->query($query);
            $crs_row = $crs->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
            $crs_ref_id = $crs_row->parent;
            break;
        }
        
        // Select owner of session
        $query = "SELECT owner FROM object_data WHERE obj_id = " . $ilDB->quote($sess_obj_id, 'integer');
        $owner_res = $ilDB->query($query);
        $owner_row = $owner_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        $owner = $owner_row->owner ? $owner_row->owner : 6;
        
        if (!$crs_ref_id) {
            $ilLog->write('DB Migration 2441: Found session without course ref_id. event_id: ' . $row->event_id);
            continue;
        }
        // Create object data entry
        $file_obj_id = $ilDB->nextId('object_data');
        $query = "INSERT INTO object_data (obj_id,type, title, description, owner, create_date, last_update) " .
            "VALUES (" . $file_obj_id . ",'file', " . $ilDB->quote($row->file_name) . ",'', " . $owner . "," . $ilDB->now() . " ," . $ilDB->now() . ")";
        $ilDB->query($query);
        
        // Insert long description
        $query = "INSERT INTO object_description SET obj_id = " . $file_obj_id . ", description = ''";
        $ilDB->query($query);
        
        // Create reference
        $file_ref_id = $ilDB->nextId('object_reference');
        $query = "INSERT INTO object_reference (ref_id,obj_id) VALUES('" . $file_ref_id . "','" . $file_obj_id . "')";
        $ilDB->query($query);
            
        
        // check if course is deleted
        // yes => insert into tree with negative tree id
        $query = "SELECT tree FROM tree WHERE child = " . $crs_ref_id;
        $tree_res = $ilDB->query($query);
        $tree_row = $tree_res->fetchRow();
        $tree_id = $tree_row[0];
        if ($tree_id != 1) {
            $current_tree = new ilTree($tree_id);
        } else {
            $current_tree = new ilTree(ROOT_FOLDER_ID);
        }
        // Insert into tree
        $current_tree->insertNode($file_ref_id, $crs_ref_id);
        
        // ops_id copy
        $query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
        $copy_res = $ilDB->query($query);
        $copy_row = $copy_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        $copy_id = $copy_row->ops_id;
        
        // adjust permissions
        $query = "SELECT * FROM rbac_pa WHERE ref_id = " . $sess_ref_id;
        $pa_res = $ilDB->query($query);
        while ($pa_row = $pa_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $new_ops = array();
            $operations = unserialize($pa_row->ops_id);
    
            if (in_array(1, $operations)) {
                $new_ops[] = 1;
            }
            if (in_array(2, $operations)) {
                $new_ops[] = 2;
            }
            if (in_array(3, $operations)) {
                $new_ops[] = 3;
            }
            if (in_array(4, $operations)) {
                $new_ops[] = 4;
            }
            if (in_array($copy_id, $operations)) {
                $new_ops[] = $copy_id;
            }
            if (in_array(6, $operations)) {
                $new_ops[] = 6;
            }
            $query = "INSERT INTO rbac_pa SET " .
                "rol_id = " . $ilDB->quote($pa_row->rol_id) . ", " .
                "ops_id = " . $ilDB->quote(serialize($new_ops)) . ", " .
                "ref_id = " . $ilDB->quote($file_ref_id) . " ";
            $ilDB->query($query);
        }
        
        // INSERT file_data
        $query = "INSERT INTO file_data (file_id,file_name,file_type,file_size,version,f_mode) " .
            "VALUES (" .
            $ilDB->quote($file_obj_id) . ", " .
            $ilDB->quote($row->file_name) . ", " .
            $ilDB->quote($row->file_type) . ", " .
            $ilDB->quote($row->file_size) . ", " .
            $ilDB->quote(1) . ", " .
            $ilDB->quote('object') .
            ")";
        $ilDB->query($query);
        
        // Move File
        $fss = new ilFSStorageFile($file_obj_id);
        ilUpdateUtils::makeDirParents($fss->getAbsolutePath() . '/001');
        $ess = new ilFSStorageEvent($row->event_id);
        if ($fss->rename($ess->getAbsolutePath() . '/' . $row->file_id, $fss->getAbsolutePath() . '/001/' . $row->file_name)) {
            $ilLog->write('Success renaming file: ' . $ess->getAbsolutePath() . '/' . $row->file_id . " to " . $fss->getAbsolutePath() . '/001/' . $row->file_name);
        } else {
            $ilLog->write('Error renaming file: ' . $ess->getAbsolutePath() . '/' . $row->file_id . " to " . $fss->getAbsolutePath() . '/001/' . $row->file_name);
        }
        
        // Meta data
        $next_id = $ilDB->nextId("il_meta_general");
        $query = "INSERT INTO il_meta_general (meta_general_id,rbac_id,obj_id,obj_type, " .
            "general_structure,title,title_language,coverage,coverage_language) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($file_obj_id, 'integer') . ", " .
            $ilDB->quote($file_obj_id, 'integer') . ", " .
            $ilDB->quote('file', 'text') . ", " .
            $ilDB->quote('Hierarchical', 'text') . ", " .
            $ilDB->quote($row->file_name, 'text') . ", " .
            $ilDB->quote('en', 'text') . ", " .
            $ilDB->quote('', 'text') . ", " .
            $ilDB->quote('en', 'text') . " " .
            ")";
        $ilDB->query($query);
                
        // MD technical
        $next_id = $ilDB->nextId('il_meta_technical');
        $query = "INSERT INTO il_meta_technical (meta_technical_id,rbac_id,obj_id,obj_type, " .
            "t_size,ir,ir_language,opr,opr_language,duration) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($file_obj_id, 'integer') . ", " .
            $ilDB->quote($file_obj_id, 'integer') . ", " .
            $ilDB->quote('file', 'text') . ", " .
            $ilDB->quote($row->file_size, 'text') . ", " .
            $ilDB->quote('', 'text') . ", " .
            $ilDB->quote('en', 'text') . ", " .
            $ilDB->quote('', 'text') . ", " .
            $ilDB->quote('en', 'text') . ", " .
            $ilDB->quote('', 'text') . " " .
            ")";
        $ilDB->query($query);
        
        // MD Format
        $next_id = $ilDB->nextId('il_meta_format');
        $query = "INSERT INTO il_meta_format (meta_format_id,rbac_id,obj_id,obj_type,format) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($file_obj_id, 'integer') . ", " .
            $ilDB->quote($file_obj_id, 'integer') . ", " .
            $ilDB->quote('file', 'text') . ", " .
            $ilDB->quote($row->file_type, 'text') . " " .
            ")";
        $ilDB->query($query);
        
        // Assign file to Session
        $query = "INSERT INTO event_items (event_id,item_id) " .
            "VALUES ( " .
            $ilDB->quote($sess_obj_id, 'integer') . ", " .
            $ilDB->quote($file_ref_id, 'integer') . " " .
            ")";
        $ilDB->query($query);
        
        // Create history entry
        $next_id = $ilDB->nextId("history");
        $query = "INSERT INTO history (id,obj_id,obj_type,action,hdate,usr_id,info_params) " .
            "VALUES ( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($file_obj_id, 'integer') . ", " .
            $ilDB->quote('file', 'text') . ", " .
            $ilDB->quote('create', 'text') . ", " .
            $ilDB->quote(ilUtil::now(), 'timestamp') . ", " .
            $ilDB->quote($owner, 'integer') . ", " .
            $ilDB->quote($row->file_name . ',1', 'text') .
            ")";
        $ilDB->query($query);
        
        // Remove old event_file entry
        $query = "DELETE FROM event_file " .
            "WHERE file_id = " . $ilDB->quote($row->file_id, 'integer');
        $ilDB->query($query);
        
        // Save success
        $query = "REPLACE INTO tmp_migration SET obj_id = '" . $row->file_id . "',passed = '1'";
        $ilDB->query($query);
    }

?>

<#2442>
DROP TABLE IF EXISTS tmp_migration;

<#2443>
<?php
if (!$ilDB->tableColumnExists("sahs_lm", "editable")) {
    $ilDB->query("ALTER TABLE sahs_lm add editable INT NOT NULL DEFAULT 0");
}
?>

<#2444>
<?php
if (!$ilDB->tableExists("sahs_sc13_tree_node")) {
    $ilDB->query("CREATE TABLE `sahs_sc13_tree_node` (
				`obj_id` int(11) NOT NULL auto_increment,
				`title` varchar(200) NOT NULL default '',
				`type` char(4) NOT NULL default '',
				`slm_id` int(11) NOT NULL default '0',
				`import_id` varchar(50) NOT NULL default '',
				`create_date` datetime NOT NULL default '0000-00-00 00:00:00',
				`last_update` datetime NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY  (`obj_id`),
				KEY `slm_id` (`slm_id`),
				KEY `type` (`type`)
				) ENGINE=MyISAM;");
    $ilDB->query("INSERT INTO sahs_sc13_tree_node (obj_id, title, type, slm_id) VALUES (1, 'Dummy top node for all trees.', '', 0)");
}
?>
<#2445>
<?php
if (!$ilDB->tableExists("sahs_sc13_tree")) {
    $ilDB->query("CREATE TABLE `sahs_sc13_tree` (
				`slm_id` int(11) NOT NULL default '0',
				`child` int(11) NOT NULL default '0',
				`parent` int(11) default NULL,
				`lft` int(11) NOT NULL default '0',
				`rgt` int(11) NOT NULL default '0',
				`depth` smallint(5) NOT NULL default '0',
				KEY `child` (`child`),
				KEY `parent` (`parent`),
				KEY `jmp_lm` (`slm_id`)
				) ENGINE=MyISAM;");
}
?>
<#2446>
<?php
if (!$ilDB->tableColumnExists("sahs_lm", "stylesheet")) {
    $ilDB->query("ALTER TABLE sahs_lm ADD stylesheet INT NOT NULL DEFAULT 0");
}
if (!$ilDB->tableColumnExists("sahs_lm", "glossary")) {
    $ilDB->query("ALTER TABLE sahs_lm ADD glossary INT NOT NULL DEFAULT 0");
}
if (!$ilDB->tableColumnExists("sahs_lm", "question_tries")) {
    $ilDB->query("ALTER TABLE sahs_lm ADD question_tries INT DEFAULT 3");
}
?>
<#2447>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_condition")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_condition(`condition` varchar(50), `seqNodeId` int(11), `measureThreshold` varchar(50), `operator` varchar(50), `referencedObjective` varchar(50) );");
    $ilDB->query("ALTER TABLE sahs_sc13_seq_condition ADD PRIMARY KEY(seqNodeId);");
}

?>
<#2448>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_course")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_course(`flow` tinyint DEFAULT 0, `choice` tinyint DEFAULT 1,  `forwardonly` tinyint DEFAULT 0,obj_id int(11));");
    $ilDB->query("ALTER TABLE sahs_sc13_seq_course ADD PRIMARY KEY(obj_id);");
}
?>
<#2449>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_mapinfo")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_mapinfo(`seqNodeId` int(11), `readNormalizedMeasure` tinyint, `readSatisfiedStatus` tinyint, `targetObjectiveID` varchar(50), `writeNormalizedMeasure` tinyint, `writeSatisfiedStatus` tinyint );");
    $ilDB->query("ALTER TABLE sahs_sc13_seq_mapinfo ADD PRIMARY KEY(seqNodeId);");
    $ilDB->query("CREATE INDEX targetObjectiveId ON sahs_sc13_seq_mapinfo(targetObjectiveID)");
}
?>
<#2450>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_node")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_node(`seqNodeId` int(11) PRIMARY KEY AUTO_INCREMENT, `nodeName` varchar(50), `tree_node_id` int(11) );");
    $ilDB->query("CREATE INDEX seq_id ON sahs_sc13_seq_node(seqNodeId);");
    $ilDB->query("CREATE INDEX tree_node_id ON sahs_sc13_seq_node(tree_node_id);");
    $ilDB->query("CREATE INDEX nodeName ON sahs_sc13_seq_node(nodeName);");
}
?>
<#2451>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_seqtemplate")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_seqtemplate(`seqNodeId` int(11), `id` varchar(50));");
    $ilDB->query("CREATE INDEX sahs_sc13_seq_template_node_id ON sahs_sc13_seq_seqtemplate(seqNodeId,id);");
}
?>
<#2452>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_objective")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_objective(`seqNodeId` int(11), `minNormalizedMeasure` varchar(50), `objectiveID` varchar(200), `primary` tinyint, `satisfiedByMeasure` tinyint );");
    $ilDB->query("ALTER TABLE sahs_sc13_seq_objective ADD PRIMARY KEY(seqNodeId);");
}
?>
<#2453>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_item")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_item(`importId` varchar(32), `seqNodeId` int(11), `sahs_sc13_tree_node_id` int, `sequencingId` varchar(50),`nocopy` tinyint,`nodelete` tinyint,`nomove` tinyint);");
    $ilDB->query("ALTER TABLE sahs_sc13_seq_item ADD PRIMARY KEY(seqNodeId);");
    $ilDB->query("CREATE INDEX sahs_sc13_tree_nodeid ON sahs_sc13_seq_item(sahs_sc13_tree_node_id);");
}
?>
<#2454>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_assignment")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_assignment(`identifier` varchar(50), `sahs_sc13_tree_node_id` int);");
    $ilDB->query("ALTER TABLE sahs_sc13_seq_assignment ADD PRIMARY KEY(sahs_sc13_tree_node_id);");
}
?>
<#2455>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_templates")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_templates(`identifier` varchar(50),`fileName` varchar(50),`id` int PRIMARY KEY AUTO_INCREMENT);");
    $ilDB->query("INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('pretestpost','pretest_posttest.xml');");
    $ilDB->query("INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('linearpath','linear_path.xml');");
    $ilDB->query("INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('linearpathforward','linear_path_forward.xml');");
}
?>
<#2456>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_rule")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_rule(`action` varchar(50), `childActivitySet` varchar(50), `conditionCombination` varchar(50), `seqNodeId` int(11), `minimumCount` int(11), `minimumPercent` varchar(50), `type` varchar(50) );");
    $ilDB->query("ALTER TABLE sahs_sc13_seq_rule ADD PRIMARY KEY(seqNodeId);");
}
?>
<#2457>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_sequencing")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_sequencing(`importId` varchar(32), `activityAbsoluteDurationLimit` varchar(20), `activityExperiencedDurationLimit` varchar(20), `attemptAbsoluteDurationLimit` varchar(20), `attemptExperiencedDurationLimit` varchar(20), `attemptLimit` int(11), `beginTimeLimit` varchar(20), `choice` tinyint, `choiceExit` tinyint, `completionSetByContent` tinyint, `constrainChoice` tinyint, `seqNodeId` int(11), `endTimeLimit` varchar(20), `flow` tinyint, `forwardOnly` tinyint, `id` varchar(200), `measureSatisfactionIfActive` tinyint, `objectiveMeasureWeight` REAL, `objectiveSetByContent` tinyint, `preventActivation` tinyint, `randomizationTiming` varchar(50), `reorderChildren` tinyint, `requiredForCompleted` varchar(50), `requiredForIncomplete` varchar(50), `requiredForNotSatisfied` varchar(50), `requiredForSatisfied` varchar(50), `rollupObjectiveSatisfied` tinyint, `rollupProgressCompletion` tinyint, `selectCount` int(11), `selectionTiming` varchar(50), `sequencingId` varchar(50), `tracked` tinyint, `useCurrentAttemptObjectiveInfo` tinyint, `useCurrentAttemptProgressInfo` tinyint);");
    $ilDB->query("ALTER TABLE sahs_sc13_seq_sequencing ADD PRIMARY KEY(seqNodeId);");
    $ilDB->query("CREATE INDEX seq_sequencingid ON sahs_sc13_seq_sequencing(id);");
}
?>
<#2458>
<?php
if (!$ilDB->tableExists("sahs_sc13_seq_tree")) {
    $ilDB->query("CREATE TABLE sahs_sc13_seq_tree(`child` int(11), `depth` smallint(5), `lft` int(11), `importid` varchar(32), `parent` int(11), `rgt` int(11) );");
    $ilDB->query("CREATE INDEX child ON sahs_sc13_seq_tree(child);");
    $ilDB->query("CREATE INDEX seq_importid_id ON sahs_sc13_seq_tree(importid);");
    $ilDB->query("CREATE INDEX parent ON sahs_sc13_seq_tree(parent);");
}
?>
<#2459>
<?php
if (!$ilDB->tableColumnExists("sahs_sc13_seq_objective", "import_objective_id")) {
    $ilDB->query("ALTER TABLE sahs_sc13_seq_objective ADD import_objective_id varchar(200)");
}
?>

<#2460>
<?php
$set = $ilDB->query(
    "SELECT * FROM sahs_sc13_seq_templates " .
    " WHERE identifier = " . $ilDB->quote(mandatoryoptions, "text")
);
if (!$ilDB->fetchAssoc($set)) {
    $ilDB->query("INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('mandatoryoptions','mandatory_options.xml');");
}
?>

<#2461>
<?php
if (!$ilDB->tableExists("page_question")) {
    $ilDB->query("CREATE TABLE `page_question` (
				`page_parent_type` VARCHAR(4) NOT NULL,
				`page_id` INT(11) NOT NULL,
				`question_id` int(11) NOT NULL
				) ENGINE=MyISAM;");
}
?>
<#2462>
<?php
if (!$ilDB->tableColumnExists("cal_entries", "is_milestone")) {
    $atts = array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 0
        );
    $ilDB->addTableColumn("cal_entries", "is_milestone", $atts);
}
?>

<#2463>
<?php
if (!$ilDB->tableColumnExists("cal_entries", "completion")) {
    $atts = array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
        );
    $ilDB->addTableColumn("cal_entries", "completion", $atts);
}
?>

<#2464>
<?php
if (!$ilDB->tableExists("cal_entry_responsible")) {
    $ilDB->query("CREATE TABLE `cal_entry_responsible` (
		`cal_id` INT(11) NOT NULL,
		`user_id` INT(11) NOT NULL,
		INDEX `cal_id` (`cal_id`),
		INDEX `user_id` (`user_id`)
		) ENGINE=MyISAM;");
}
?>

<#2465>
<?php
if (!$ilDB->tableExists("personal_pc_clipboard")) {
    $ilDB->query("CREATE TABLE `personal_pc_clipboard` (
				`user_id` INT(11) NOT NULL,
				`content` MEDIUMTEXT,
				`insert_time` DATETIME,
				`order_nr` INT(11),
				INDEX user_id (user_id)
				) ENGINE=MyISAM;");
}
?>

<#2466>
<?php
if (!$ilDB->tableExists("page_layout")) {
    $ilDB->query("CREATE TABLE `page_layout` (
		 `layout_id` int(11) NOT NULL auto_increment,
		 `content` mediumtext,
		 `title` varchar(128) default NULL,
		 `description` varchar(255) default NULL,
		 `active` tinyint(4) default '0',
		  PRIMARY KEY  (`layout_id`)
		 ) ENGINE=MyISAM;");
}
?>

<#2467>
<?php
$setting = new ilSetting();
$se_db = (int) $setting->get("se_db");

if ($se_db <= 20) {
    $ilDB->query("DELETE FROM page_layout");

    $ilDB->query("DELETE FROM page_object WHERE(parent_type='stys');");


    $ilDB->query("INSERT INTO page_layout(layout_id,title,description,active) values (1,'1A Simple text page with accompanying media','Example description',1);");

    $ilDB->query("INSERT INTO page_object(page_id,parent_type,content)
	values (1,'stys','<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"500px\"/></PageContent></TableData><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData> </TableRow></Table></PageContent></PageObject>');");

    //second

    $ilDB->query("INSERT INTO page_layout(layout_id,title,active) values (2,'1C Text page with accompanying media and test',1);");

    $ilDB->query("INSERT INTO page_object(page_id,parent_type,content)
	values (2,'stys','<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent><PageContent PCID=\"3f77eb1d8a478493d69b99d438fda8f\"><PlaceHolder ContentClass=\"Question\" Height=\"200px\"/></PageContent></TableData><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData> </TableRow></Table></PageContent></PageObject>');");

    //third

    $ilDB->query("INSERT INTO page_layout(layout_id,title,active) values (3,'1E Text page with accompanying media followed by test and text',1);");

    $query = "SELECT LAST_INSERT_ID() as id";
    $res = $ilDB->query($query);
    $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

    $ilDB->query("INSERT INTO page_object(page_id,parent_type,content)
	values (3,'stys','<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent><PageContent PCID=\"3f77eb1d8a478493d69b99d438fda8f\"><PlaceHolder ContentClass=\"Question\" Height=\"200px\"/></PageContent><PageContent PCID=\"9b77eb1d8a478197d69b99d938fea8f\"><PlaceHolder ContentClass=\"Text\" Height=\"200px\"/></PageContent></TableData><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData> </TableRow></Table></PageContent></PageObject>');");


    //fourth

    $ilDB->query("INSERT INTO page_layout(layout_id,title,active) values (4,'2C Simple media page with accompanying text and test',1);");

    $query = "SELECT LAST_INSERT_ID() as id";
    $res = $ilDB->query($query);
    $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

    $ilDB->query("INSERT INTO page_object(page_id,parent_type,content)
	values (4,'stys','<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent><PageContent PCID=\"3f77eb1d8a478493d69b99d438fda8f\"><PlaceHolder ContentClass=\"Question\" Height=\"200px\"/></PageContent></TableData></TableRow></Table></PageContent></PageObject>');");

    //fifth


    $ilDB->query("INSERT INTO page_layout(layout_id,title,active) values (5,'7C Vertical component navigation page with media and text',1);");

    $query = "SELECT LAST_INSERT_ID() as id";
    $res = $ilDB->query($query);
    $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

    $ilDB->query("INSERT INTO page_object(page_id,parent_type,content)
	values (5,'stys','<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"100%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent></TableData> </TableRow><TableRow PCID=\"efade08caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"100%\"><PageContent PCID=\"124d24457cbc90ea1bf1a1323d7c3b89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"dfade09caf9fd13e8c7012f29c9510be\"><TableData PCID=\"e4e417c08feebeafb1487e60a2e245a5\" Width=\"33%\"><PageContent PCID=\"3e77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Media\" Height=\"150px\"/></PageContent><PageContent PCID=\"4e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Text\" Height=\"250px\"/></PageContent></TableData><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a5\" Width=\"33%\"><PageContent PCID=\"3a77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Media\" Height=\"150px\"/></PageContent><PageContent PCID=\"4ea7eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Text\" Height=\"250px\"/></PageContent></TableData><TableData PCID=\"b4e417c08feebeafb1487e60a2e245a5\" Width=\"33%\"><PageContent PCID=\"3b77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Media\" Height=\"150px\"/></PageContent><PageContent PCID=\"4b77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Text\" Height=\"250px\"/></PageContent></TableData></TableRow></Table></PageContent></TableData></TableRow></Table></PageContent></PageObject>');");
}
?>

<#2468>
<?php
if (!$ilDB->tableColumnExists("qpl_questions", "nr_of_tries")) {
    $atts = array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
        );
    $ilDB->addTableColumn("qpl_questions", "nr_of_tries", $atts);
}
?>

<#2469>
<?php
if (!$ilDB->tableColumnExists("sahs_sc13_seq_item", "seqxml")) {
    $ilDB->query("ALTER TABLE sahs_sc13_seq_item ADD COLUMN `seqxml` mediumtext");
}
$q = "ALTER TABLE `sahs_sc13_seq_item` DROP PRIMARY KEY";
$ilDB->query($q);

$q = "ALTER TABLE `sahs_sc13_seq_item` ADD PRIMARY KEY (`sahs_sc13_tree_node_id`)";
$ilDB->query($q);
?>
<#2470>
<?php
if (!$ilDB->tableColumnExists("sahs_sc13_seq_item", "rootlevel")) {
    $ilDB->query("ALTER TABLE `sahs_sc13_seq_item` ADD COLUMN `rootlevel` tinyint(4) NOT NULL default '0'");
}
?>
<#2471>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_item');
?>
<#2472>
RENAME TABLE `sahs_sc13_seq_assignment` TO `sahs_sc13_seq_assign` ;
<#2473>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_assign');
?>
<#2474>
RENAME TABLE `sahs_sc13_seq_condition` TO `sahs_sc13_seq_cond` ;
<#2475>
ALTER TABLE `sahs_sc13_seq_cond` CHANGE `condition` `cond` VARCHAR(50);
<#2476>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_cond');
?>
<#2477>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_course');
?>
<#2478>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_mapinfo');
?>
<#2479>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_node');
?>
<#2480>
RENAME TABLE `sahs_sc13_seq_objective` TO `sahs_sc13_seq_obj`;
<#2481>
ALTER TABLE `sahs_sc13_seq_obj` CHANGE `primary` `primary_obj` TINYINT;
<#2482>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_obj');
?>
<#2483>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_rule');
?>
<#2484>
RENAME TABLE `sahs_sc13_seq_seqtemplate` TO `sahs_sc13_seq_templ`;
<#2485>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_templ');
?>
<#2486>
RENAME TABLE `sahs_sc13_seq_sequencing` TO `sahs_sc13_seq_seq`;
<#2487>
ALTER TABLE `sahs_sc13_seq_seq` CHANGE `activityExperiencedDurationLimit` `activityExperiencedDurLimit` VARCHAR(20);
ALTER TABLE `sahs_sc13_seq_seq` CHANGE `attemptExperiencedDurationLimit` `attemptExperiencedDurLimit` VARCHAR(20);
<#2488>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_seq');
?>
<#2489>
RENAME TABLE `sahs_sc13_seq_templates` TO `sahs_sc13_seq_templts`;
<#2490>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_templts');
?>
<#2491>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_seq_tree');
?>
<#2492>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_tree');
?>
<#2493>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_sc13_tree_node');
?>
<#2494>
<?php
    $setting = new ilSetting();
    $setting->set('chat_export_status', 0);
    $setting->set('chat_export_period', 1);
?>
<#2495>
<?php
if (!$ilDB->tableColumnExists('usr_data', 'reg_hash')) {
    $ilDB->alterTable('usr_data', array('add' =>
        array('reg_hash' => array('type' => 'text', 'length' => 32, 'notnull' => false, 'fixed' => true))));
}
?>

<#2496>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#2497>
<?php
if (!$ilDB->tableColumnExists('usr_session', 'last_remind_ts')) {
    $atts = array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
    );
    $ilDB->addTableColumn('usr_session', 'last_remind_ts', $atts);
}
?>
<#2498>
<?php
if ($ilDB->tableExists('tmp_mail_migration')) {
    $ilDB->dropTable('tmp_mail_migration');
}

if (!$ilDB->tableExists('tmp_mail_migration')) {
    $fields = array(
        'mail_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'mail_path' => array(
            'type' => 'text',
            'length' => 50,
            'notnull' => true
        ),
        'mail_passed' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        )
    );
    $ilDB->createTable('tmp_mail_migration', $fields);
    $ilDB->addPrimaryKey('tmp_mail_migration', array('mail_id'));
    $ilDB->addIndex('tmp_mail_migration', array('mail_path', 'mail_passed'), 'tmm');
}
?>
<#2499>
<?php
$update_step = 2498;

include_once('Services/Migration/DBUpdate_' . $update_step . '/classes/class.ilUpdateUtilsMailMigration.php');
include_once('Services/Migration/DBUpdate_' . $update_step . '/classes/class.ilFileSystemStorageMailMigration.php');
include_once('Services/Migration/DBUpdate_' . $update_step . '/classes/class.ilFSStorageMailMailMigration.php');

global $ilLog;

// Fetch all messages with attachment(s)
$res = $ilDB->queryf(
    "SELECT * FROM mail_attachment",
    array(),
    array()
);

// iterate over result
while ($record = $ilDB->fetchObject($res)) {
    // Check whether migration is already done
    $continue = false;
    $done_res = $ilDB->queryf(
        "SELECT * FROM tmp_mail_migration WHERE mail_id = %s",
        array('integer'),
        array($record->mail_id)
    );
    while ($done_record = $ilDB->fetchAssoc($done_res)) {
        $continue = true;
        break;
    }
    if ($continue) {
        continue;
    }
    
    // Checking stored path
    $path_parts = explode('_', $record->path);
    $path_parts[0] = trim($path_parts[0]);
    $path_parts[1] = trim($path_parts[1]);
    if (count($path_parts) != 2 || !strlen($path_parts[0]) || !strlen($path_parts[1])) {
        $ilLog->write('DB Migration ' . $update_step . ': Failed: No attachments found for mail ' . $record->mail_id);
        continue;
    }
    
    // Create new storage folder (if it does not exist yet)
    $oFSStorageMail = new ilFSStorageMailMailMigration($path_parts[1], $path_parts[0]);
    
    // Check if folder has to be created
    $path_record = null;
    $done_path_res = $ilDB->queryf(
        "SELECT mail_id FROM tmp_mail_migration WHERE mail_path = %s",
        array('text'),
        array($record->path)
    );
    while ($done_path_record = $ilDB->fetchAssoc($done_path_res)) {
        $path_record = $done_path_record;
        break;
    }
    if (!is_array($path_record)) {
        // check old path
        $path = CLIENT_DATA_DIR . DIRECTORY_SEPARATOR . 'mail' . DIRECTORY_SEPARATOR . $record->path;
        if (!@file_exists($path) || !@is_dir($path)) {
            $ilLog->write('DB Migration ' . $update_step . ': Failed: No attachments found for mail ' . $record->mail_id . ' (path "' . $path . '")');
            continue;
        } elseif (!@is_readable($path)) {
            $ilLog->write('DB Migration ' . $update_step . ': Failed: Path "' . $path . '" is not readable for mail ' . $record->mail_id);
            continue;
        }
        
        $tmp_path = ilUpdateUtilsMailMigration::ilTempnam();
        ilUpdateUtilsMailMigration::makeDir($tmp_path);

        ilUpdateUtilsMailMigration::rename($path, $tmp_path);
                
        $oFSStorageMail->create();
        $new_path = $oFSStorageMail->getAbsolutePath();
        if (!@file_exists($new_path) || !@is_dir($new_path)) {
            $ilLog->write('DB Migration ' . $update_step . ': Failed: Target folder "' . $new_path . '" does not exist for mail ' . $record->mail_id);
            continue;
        } elseif (!@is_writeable($new_path)) {
            $ilLog->write('DB Migration ' . $update_step . ': Failed: Target folder "' . $new_path . '" is not writeable for mail ' . $record->mail_id);
            continue;
        }
        
        ilUpdateUtilsMailMigration::rename($tmp_path, $new_path);
    }
    
    // Modifie existing attachment assigment
    $ilDB->manipulateF(
        "UPDATE mail_attachment SET path = %s WHERE mail_id = %s",
        array('text', 'integer'),
        array($oFSStorageMail->getRelativePathExMailDirectory(), $record->mail_id)
    );
    
    // Save success
    $ilDB->manipulateF(
        "INSERT INTO tmp_mail_migration (mail_id, mail_passed, mail_path) VALUES (%s, %s, %s)",
        array('integer', 'integer', 'text'),
        array($record->mail_id, 1, $record->path)
    );
}
?>
<#2500>
<?php
if ($ilDB->tableExists('tmp_mail_migration')) {
    $ilDB->dropTable('tmp_mail_migration');
}
?>

<#2501>
<?php
    $ilMySQLAbstraction->performAbstraction("shib_role_assignment");
?>
<#2502>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#2503>
<?php

$atts = array(
    'type' => 'integer',
    'length' => 1
);
$ilDB->addTableColumn("ldap_role_assignments", "add_on_update", $atts);
$ilDB->addTableColumn("ldap_role_assignments", "remove_on_update", $atts);

$atts = array(
    'type' => 'integer',
    'length' => 4,
);
$ilDB->addTableColumn("ldap_role_assignments", "plugin_id", $atts);
?>

<#2504>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#2505>
<?php
    $ilDB->modifyTableColumn("aicc_course", "description", array("type" => "clob", "notnull" => false));
?>
<#2506>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_course` CHANGE `max_fields_cst` `max_fields_cst` INT( 11 ) NULL DEFAULT '0'");
?>
<#2507>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_course` CHANGE `max_fields_ort` `max_fields_ort` INT( 11 ) NULL DEFAULT '0'");
?>
<#2508>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_course` CHANGE `total_aus` `total_aus` INT( 11 ) NOT NULL DEFAULT '0'");
?>
<#2509>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_course` CHANGE `total_blocks` `total_blocks` INT( 11 ) NOT NULL DEFAULT '0'");
?>
<#2510>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_course` CHANGE `total_complex_obj` `total_complex_obj` INT( 11 ) NULL DEFAULT '0'");
?>
<#2511>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_course` CHANGE `total_objectives` `total_objectives` INT( 11 ) NULL DEFAULT '0'");
?>
<#2512>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_object` CHANGE `type` `c_type` VARCHAR(50) NULL DEFAULT NULL");
?>
<#2513>
<?php
    $ilDB->modifyTableColumn("aicc_object", "description", array("type" => "clob", "notnull" => false));
?>
<#2514>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_object` CHANGE `title` `title` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2515>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_units` CHANGE `type` `c_type` VARCHAR(50) NULL DEFAULT NULL");
?>
<#2516>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_units` CHANGE `core_vendor` `core_vendor` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2517>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_units` CHANGE `system_vendor` `system_vendor` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2518>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_units` CHANGE `mastery_score` `mastery_score`INT( 11 ) NOT NULL DEFAULT '0'");
?>
<#2519>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_units` CHANGE `max_score` `max_score` FLOAT NULL DEFAULT NULL");
?>
<#2520>
<?php
    $ilDB->renameTableColumn('cmi_comment', 'timestamp', 'c_timestamp');
?>
<#2521>
<?php
    $ilDB->renameTableColumn('cmi_comment', 'comment', 'c_comment');
?>
<#2522>
<?php
    $ilDB->modifyTableColumn("cmi_comment", "c_comment", array("type" => "clob", "notnull" => false));
?>
<#2523>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_comment` CHANGE `c_timestamp` `c_timestamp` DATETIME NULL");
?>
<#2524>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_correct_response` DROP INDEX `cmi_correct_response_id`");
?>
<#2525>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_correct_response` CHANGE `cmi_correct_response_id` `cmi_correct_resp_id` INT( 11 ) NOT NULL AUTO_INCREMENT ");
?>
<#2526>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_custom` CHANGE `rvalue` `rvalue` VARCHAR(255) NULL DEFAULT NULL");
?>
<#2527>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_custom` CHANGE `timestamp` `c_timestamp` DATETIME NULL");
?>
<#2528>
<?php
    $ilMySQLAbstraction->performAbstraction('cmi_gobjective');
?>
<#2529>
<?php
    $ilDB->modifyTableColumn("cmi_interaction", "description", array("type" => "clob", "notnull" => false));
?>
<#2530>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_interaction` CHANGE `learner_response` `learner_response` LONGTEXT NULL DEFAULT NULL");
?>
<#2531>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_interaction` CHANGE `result` `result` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2532>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_interaction` CHANGE `weighting` `weighting` FLOAT NULL DEFAULT NULL");
?>
<#2533>
<?php
    $ilDB->renameTableColumn('cmi_interaction', 'timestamp', 'c_timestamp');
?>
<#2534>
<?php
    $ilDB->renameTableColumn('cmi_interaction', 'type', 'c_type');
?>
<#2535>
<?php
    $ilMySQLAbstraction->performAbstraction('cmi_interaction');
?>
<#2536>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `attemptCompletionAmount` `attemptcomplamount` FLOAT NULL DEFAULT NULL");
?>
<#2537>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `audio_level` `audio_level` FLOAT NULL DEFAULT NULL");
?>
<#2538>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `completion` `completion` FLOAT NULL DEFAULT NULL");
?>
<#2539>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `delivery_speed` `delivery_speed` FLOAT NULL DEFAULT NULL");
?>
<#2540>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `max` `c_max` FLOAT NULL DEFAULT NULL");
?>
<#2541>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `min` `c_min` FLOAT NULL DEFAULT NULL");
?>
<#2542>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `progress_measure` `progress_measure` FLOAT NULL DEFAULT NULL");
?>
<#2543>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `raw` `c_raw` FLOAT NULL DEFAULT NULL");
?>
<#2544>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `scaled` `scaled` FLOAT NULL DEFAULT NULL");
?>
<#2545>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `scaled_passing_score` `scaled_passing_score` FLOAT NULL DEFAULT NULL");
?>
<#2546>
<?php
    $ilDB->modifyTableColumn("cmi_node", "launch_data", array("type" => "clob", "notnull" => false));
?>
<#2547>
<?php
    $ilDB->modifyTableColumn("cmi_node", "suspend_data", array("type" => "clob", "notnull" => false));
?>
<#2548>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `TIMESTAMP` `c_timestamp` DATETIME NULL");
?>
<#2549>
<?php
    $ilDB->renameTableColumn('cmi_node', 'activityexperiencedduration', 'activityexpduration');
?>
<#2550>
<?php
    $ilDB->renameTableColumn('cmi_node', 'attemptexperiencedduration', 'attemptexpduration');
?>
<#2551>
<?php
    $ilDB->renameTableColumn('cmi_node', 'attemptabsoluteduration', 'attemptabsduration');
?>
<#2552>
<?php
    $ilDB->renameTableColumn('cmi_node', 'activityabsoluteduration', 'activityabsduration');
?>
<#2553>
<?php
    $ilDB->renameTableColumn('cmi_node', 'activityprogressstatus', 'activityprogstatus');
?>
<#2554>
<?php
    $ilDB->renameTableColumn('cmi_node', 'attemptcompletionstatus', 'attemptcomplstatus');
?>
<#2555>
<?php
    $ilDB->renameTableColumn('cmi_node', 'attemptprogressstatus', 'attemptprogstatus');
?>
<#2556>
<?php
    $ilDB->renameTableColumn('cmi_node', 'entry', 'c_entry');
?>
<#2557>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` CHANGE `exit` `c_exit` VARCHAR(255) NULL DEFAULT NULL");
?>
<#2558>
<?php
    $ilMySQLAbstraction->performAbstraction('cmi_node');
?>
<#2559>
<?php
    $ilMySQLAbstraction->performAbstraction('cmi_correct_response');
?>
<#2560>
<?php
    $ilMySQLAbstraction->performAbstraction('cmi_custom');
?>
<#2561>
	ALTER TABLE `cp_condition` CHANGE `measureThreshold` `measurethreshold` VARCHAR( 50 ) NULL DEFAULT NULL; 

<#2562>
	ALTER TABLE `cp_condition` CHANGE `referencedObjective` `referencedobjective` VARCHAR( 50 ) NULL DEFAULT NULL;

<#2563>
<?php
    $ilDB->modifyTableColumn("cmi_objective", "description", array("type" => "clob", "notnull" => false));
?>

<#2564>
<?php
    $ilDB->renameTableColumn('cmi_objective', 'min', 'c_min');;
?>
<#2565>
<?php
    $ilDB->renameTableColumn('cmi_objective', 'max', 'c_max');;
?>
<#2566>
<?php
    $ilDB->renameTableColumn('cmi_objective', 'raw', 'c_raw');;
?>
<#2567>
<?php
    $ilMySQLAbstraction->performAbstraction('cmi_objective');
?>
<#2568>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_auxilaryResource');
?>
<#2569>
<?php
    $ilDB->renameTableColumn('cp_condition', 'operator', 'c_operator');
?>
<#2570>
	ALTER TABLE `cp_condition` CHANGE `condition` `c_condition` VARCHAR( 50 ) NULL DEFAULT NULL;
<#2571>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_condition');
?>
<#2572>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_file');
?>
<#2573>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_hidelmsui');
?>
<#2574>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_item` CHANGE `completionThreshold` `completionthreshold` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2575>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_item` CHANGE `dataFromLMS` `datafromlms` VARCHAR( 255 ) NULL DEFAULT NULL");
?>
<#2576>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_item` CHANGE `resourceId` `resourceid` VARCHAR( 200 ) NULL DEFAULT NULL");
?>
<#2577>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_item` CHANGE `sequencingId` `sequencingid` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2578>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_item` CHANGE `timeLimitAction` `timelimitaction` VARCHAR( 30 ) NULL DEFAULT NULL"); ?>
<#2579>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_item');
?>
<#2580>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_manifest` CHANGE `defaultOrganization` `defaultorganization` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2581>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_manifest');
?>
<#2582>
<?php
    $ilDB->renameTableColumn('cp_mapinfo', 'readnormalizedmeasure', 'readnormalmeasure');
?>
<#2583>
<?php
    $ilDB->renameTableColumn('cp_mapinfo', 'writenormalizedmeasure', 'writenormalmeasure');
?>
<#2584>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_mapinfo` CHANGE `readSatisfiedStatus` `readsatisfiedstatus` TINYINT( 4 ) NULL DEFAULT NULL ");
?>
<#2585>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_mapinfo` CHANGE `targetObjectiveID` `targetobjectiveid` VARCHAR( 50 ) NULL DEFAULT NULL ");
?>
<#2586>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_mapinfo` CHANGE `writeSatisfiedStatus` `writesatisfiedstatus` TINYINT( 4 ) NULL DEFAULT NULL ");
?>
<#2587>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_mapinfo');
?>
<#2588>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_node` CHANGE `nodeName` `nodename` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2589>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_node');
?>
<#2590>
<?php
    $ilDB->renameTableColumn('cp_objective', 'minnormalizedmeasure', 'minnormalmeasure');
?>
<#2591>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_objective` CHANGE `satisfiedByMeasure` `satisfiedbymeasure` TINYINT( 4 ) NULL DEFAULT NULL ");
?>
<#2592>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_objective` CHANGE `objectiveID` `objectiveid` VARCHAR( 200 ) NULL DEFAULT NULL");
?>
<#2593>
	ALTER TABLE `cp_objective` CHANGE `primary` `c_primary` TINYINT( 4 ) NULL DEFAULT NULL ;
<#2594>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_organization` CHANGE `sequencingId` `sequencingid` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2595>
<?php
    $ilDB->renameTableColumn('cp_organization', 'objectivesglobaltosystem', 'objectivesglobtosys');
?>
<#2596>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_organization');
?>
<#2597>
<?php
    $ilDB->renameTableColumn('cp_package', 'persistpreviousattempts', 'persistprevattempts');
?>
<#2598>
<?php
    $ilDB->modifyTableColumn("cp_package", "xmldata", array("type" => "clob", "notnull" => false));
?>
<#2599>
<?php
    $ilDB->modifyTableColumn("cp_package", "activitytree", array("type" => "clob", "notnull" => false));
?>
<#2600>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_package` CHANGE `identifier` `c_identifier` VARCHAR(255) NULL DEFAULT NULL");
?>
<#2601>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_package` CHANGE `settings` `c_settings` VARCHAR(255) NULL DEFAULT NULL");
?>
<#2602>
<?php
$ilMySQLAbstraction->performAbstraction('cp_package');
?>
<#2603>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_rule` CHANGE `childActivitySet` `childactivityset` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2604>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_rule` CHANGE `conditionCombination` `conditioncombination` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2605>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_rule` CHANGE `minimumCount` `minimumcount` INT( 11 ) NULL DEFAULT NULL");
?>
<#2606>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_rule` CHANGE `minimumPercent` `minimumpercent` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2607>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_rule');
?>
<#2608>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'activityabsolutedurationlimit', 'activityabsdurlimit');
?>
<#2609>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'activityexperienceddurationlimit', 'activityexpdurlimit');
?>
<#2610>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'attemptabsolutedurationlimit', 'attemptabsdurlimit');
?>
<#2611>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'completionsetbycontent', 'completionbycontent');
?>
<#2612>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'measuresatisfactionifactive', 'measuresatisfactive');
?>
<#2613>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'objectivemeasureweight', 'objectivemeasweight');
?>
<#2614>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'objectivesetbycontent', 'objectivebycontent');
?>
<#2615>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'requiredforcompleted', 'requiredcompleted');
?>
<#2616>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'requiredforincomplete', 'requiredincomplete');
?>
<#2617>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'requiredfornotsatisfied', 'requirednotsatisfied');
?>
<#2618>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'requiredforsatisfied', 'requiredforsatisfied');
?>
<#2619>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'rollupobjectivesatisfied', 'rollupobjectivesatis');
?>
<#2620>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'rollupprogresscompletion', 'rollupprogcompletion');
?>
<#2621>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'usecurrentattemptobjectiveinfo', 'usecurattemptobjinfo');
?>
<#2622>
<?php
    $ilDB->renameTableColumn('cp_sequencing', 'usecurrentattemptprogressinfo', 'usecurattemptproginfo');
?>
<#2623>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `attemptLimit` `attemptlimit` INT( 11 ) NULL DEFAULT NULL ");
?>
<#2624>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `beginTimeLimit` `begintimelimit` VARCHAR( 20 ) NULL DEFAULT NULL");
?>
<#2625>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `choiceExit` `choiceexit` TINYINT( 4 ) NULL DEFAULT NULL ");
?>
<#2626>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `constrainChoice` `constrainchoice` TINYINT( 4 ) NULL DEFAULT NULL ");
?>
<#2627>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `endTimeLimit` `endtimelimit` VARCHAR( 20 ) NULL DEFAULT NULL");
?>
<#2628>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `forwardOnly` `forwardonly` TINYINT( 4 ) NULL DEFAULT NULL ");
?>
<#2629>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `preventActivation` `preventactivation` TINYINT( 4 ) NULL DEFAULT NULL ");
?>
<#2630>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `randomizationTiming` `randomizationtiming` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2631>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `reorderChildren` `reorderchildren` TINYINT( 4 ) NULL DEFAULT NULL ");
?>
<#2632>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `selectCount` `selectcount` INT( 11 ) NULL DEFAULT NULL ");
?>
<#2633>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `selectionTiming` `selectiontiming` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2634>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `sequencingId` `sequencingid` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2635>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_sequencing');
?>
<#2636>
<?php
    $ilDB->modifyTableColumn("cp_suspend", "data", array("type" => "clob", "notnull" => false));
?>
<#2637>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_suspend');
?>
<#2638>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_tree');
?>
<#2639>
<?php
    $ilDB->manipulate("ALTER TABLE `sahs_lm` CHANGE `online` `online` VARCHAR(3) NULL DEFAULT 'n'");
?>
<#2640>
<?php
    $ilDB->manipulate("ALTER TABLE `sahs_lm` CHANGE `credit` `credit` VARCHAR(10) NOT NULL DEFAULT 'credit'");
?>
<#2641>
<?php
    $ilDB->manipulate("ALTER TABLE `sahs_lm` CHANGE `default_lesson_mode` `default_lesson_mode` VARCHAR(8) NOT NULL DEFAULT 'normal'");
?>
<#2642>
<?php
    $ilDB->manipulate("ALTER TABLE `sahs_lm` CHANGE `auto_review` `auto_review` VARCHAR(3) NOT NULL DEFAULT 'n'");
?>
<#2643>
<?php
    $ilDB->manipulate("ALTER TABLE `sahs_lm` CHANGE `type` `c_type` VARCHAR(10) NULL DEFAULT NULL");
?>
<#2644>
<?php
    $ilMySQLAbstraction->performAbstraction('sahs_lm');
?>
<#2645>
<?php
    $ilMySQLAbstraction->performAbstraction('scorm_object');
?>
<#2646>
<?php
    $ilDB->manipulate("ALTER TABLE `scorm_tracking` CHANGE `rvalue` `rvalue` LONGTEXT NULL DEFAULT NULL");
?>
<#2647>
<?php
    $ilDB->manipulate("ALTER TABLE `scorm_tracking` CHANGE `timestamp` `c_timestamp`  DATETIME NULL");
?>
<#2648>
<?php
    $ilMySQLAbstraction->performAbstraction('scorm_tracking');
?>
<#2649>
<?php
    $ilMySQLAbstraction->performAbstraction('scorm_tree');
?>
<#2650>
<?php
    $ilDB->manipulate("ALTER TABLE `sc_item` CHANGE `isvisible` `isvisible` VARCHAR(6) NULL DEFAULT NULL");
?>
<#2651>
<?php
    $ilDB->manipulate("ALTER TABLE `sc_item` CHANGE `parameters` `parameters` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2652>
<?php
    $ilDB->manipulate("ALTER TABLE `sc_item` CHANGE `prerequisites` `prerequisites` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2653>
<?php
    $ilDB->modifyTableColumn("sc_item", "datafromlms", array("type" => "clob", "notnull" => false));
?>
<#2654>
<?php
    $ilMySQLAbstraction->performAbstraction('sc_item');
?>
<#2655>
<?php
    $ilMySQLAbstraction->performAbstraction('sc_manifest');
?>
<#2656>
<?php
    $ilMySQLAbstraction->performAbstraction('sc_organization');
?>
<#2657>
<?php
    $ilMySQLAbstraction->performAbstraction('sc_organizations');
?>
<#2658>
<?php
    $ilDB->manipulate("ALTER TABLE `sc_resource` CHANGE `scormtype` `scormtype` VARCHAR(6) NULL DEFAULT NULL");
?>
<#2659>
<?php
    $ilMySQLAbstraction->performAbstraction('sc_resource');
?>
<#2660>
<?php
    $ilMySQLAbstraction->performAbstraction('sc_resources');
?>
<#2661>
<?php
    $ilDB->renameTable('sc_resource_dependency', 'sc_resource_dependen');
?>
<#2662>
<?php
    $ilMySQLAbstraction->performAbstraction('sc_resource_dependen');
?>
<#2663>
<?php
    $ilDB->manipulate("ALTER TABLE `sc_resource_file` CHANGE `href` `href` VARCHAR(4000) NULL DEFAULT NULL");
?>
<#2664>
<?php
    $ilMySQLAbstraction->performAbstraction('sc_resource_file');
?>
<#2665>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_dependency` CHANGE `resourceId` `resourceId` VARCHAR( 50 ) NULL DEFAULT NULL");
?>
<#2666>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_dependency');
?>
<#2667>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_objective');
?>
<#2668>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_resource` CHANGE `scormType` `scormtype` VARCHAR( 32 ) NULL DEFAULT NULL");
?>
<#2669>
<?php
    $ilMySQLAbstraction->performAbstraction('cp_resource');
?>
<#2670>
<?php
    $ilMySQLAbstraction->performAbstraction('cmi_comment');
?>
<#2671>
<?php
    $ilMySQLAbstraction->performAbstraction('aicc_units');
?>
<#2672>
<?php
    $ilMySQLAbstraction->performAbstraction('aicc_course');
?>
<#2673>
<?php
    $ilMySQLAbstraction->performAbstraction('aicc_object');
?>
<#2674>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#2675>
ALTER TABLE page_object ADD COLUMN inactive_elements TINYINT DEFAULT 0;
<#2676>
<?php
    $query = "SELECT * FROM page_object WHERE" .
        " content LIKE '% Enabled=\"False\"%'";
    $obj_set = $ilDB->query($query);

    while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
        $ilDB->manipulate("UPDATE page_object SET inactive_elements = 1 WHERE" .
            " page_id = " . $ilDB->quote($obj_rec["page_id"], "integer") .
            " AND parent_type = " . $ilDB->quote($obj_rec["parent_type"], "text"));
    }
?>
<#2677>
ALTER TABLE page_object ADD COLUMN int_links TINYINT DEFAULT 0;
<#2678>
<?php
    $query = "SELECT * FROM page_object WHERE" .
        " content LIKE '%IntLink%'";
    $obj_set = $ilDB->query($query);

    while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
        $ilDB->manipulate("UPDATE page_object SET int_links = 1 WHERE" .
            " page_id = " . $ilDB->quote($obj_rec["page_id"], "integer") .
            " AND parent_type = " . $ilDB->quote($obj_rec["parent_type"], "text"));
    }
?>
<#2679>
<?php
$atts = array(
    'type' => 'integer',
    'length' => 8,
    'default' => 0,
    'notnull' => true
);
$r = $ilDB->addTableColumn('role_data', 'disk_quota', $atts);

// Assign largest possible numeric value to system role
$query = "UPDATE role_data SET disk_quota = 9223372036854775807 " .
        "WHERE role_id = " . SYSTEM_ROLE_ID;
$ilDB->query($query);

?>
<#2680>
<?php
    $ilDB->manipulate("ALTER TABLE `aicc_object` CHANGE `c_type` `type` VARCHAR(50) NULL DEFAULT NULL");
?>
<#2681>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_sequencing` CHANGE `attemptexperienceddurationlimit` `attemptexpdurlimit` VARCHAR(20) NULL DEFAULT NULL");
?>
<#2682>
<?php
    $ilMySQLAbstraction->performAbstraction('ilinc_registration');
?>
<#2683>
<?php
    $ilDB->renameTableColumn('ilinc_data', 'contact_responsibility', 'contact_responsibili');
?>
<#2684>
<?php
    $ilDB->renameTableColumn('ilinc_data', 'subscription_unlimited', 'subscription_unlimit');
?>
<#2685>
<?php
    $ilDB->renameTableColumn('ilinc_data', 'subscription_password', 'subscription_passwd');
?>
<#2686>
<?php
    $ilDB->manipulate("ALTER TABLE `ilinc_data` CHANGE `activation_offline` `activation_offline` VARCHAR(3) NULL DEFAULT NULL");
?>
<#2687>
<?php
    $ilMySQLAbstraction->performAbstraction('ilinc_data');
?>
<#2688>
<?php
    $ilMySQLAbstraction->performAbstraction("page_object");
?>
<#2689>
<?php
    $ilMySQLAbstraction->performAbstraction('dav_property');
?>
<#2690>
<?php
    $ilDB->renameTableColumn('aicc_course', 'level', 'c_level');
?>
<#2691>
<?php
    $ilDB->modifyTableColumn('dav_property', 'value', array("type" => "text", "length" => 4000));
?>
<#2692>
<?php
    $ilMySQLAbstraction->performAbstraction('dav_lock');
?>
<#2693>
<?php
    $ilDB->manipulateF(
    "UPDATE style_char SET type = %s WHERE type = %s AND characteristic = %s",
    array("text", "text", "text"),
    array("heading1", "text_block", "Headline1")
);
    $ilDB->manipulateF(
        "UPDATE style_char SET type = %s WHERE type = %s AND characteristic = %s",
        array("text", "text", "text"),
        array("heading2", "text_block", "Headline2")
    );
    $ilDB->manipulateF(
        "UPDATE style_char SET type = %s WHERE type = %s AND characteristic = %s",
        array("text", "text", "text"),
        array("heading3", "text_block", "Headline3")
    );
?>
<#2694>
<?php
    $ilDB->manipulateF(
    "UPDATE style_parameter SET tag = %s, type = %s WHERE tag = %s AND class = %s",
    array("text", "text", "text", "text"),
    array("h1", "heading1", "div", "Headline1")
);
    $ilDB->manipulateF(
        "UPDATE style_parameter SET tag = %s, type = %s WHERE tag = %s AND class = %s",
        array("text", "text", "text", "text"),
        array("h2", "heading2", "div", "Headline2")
    );
    $ilDB->manipulateF(
        "UPDATE style_parameter SET tag = %s, type = %s WHERE tag = %s AND class = %s",
        array("text", "text", "text", "text"),
        array("h3", "heading3", "div", "Headline3")
    );
    $ilDB->manipulateF(
        "UPDATE style_parameter SET tag = %s, type = %s WHERE tag = %s AND class = %s",
        array("text", "text", "text", "text"),
        array("h1", "page_title", "div", "PageTitle")
    );
?>
<#2695>
<?php
    // add standard values due to move from div to h1, h2, h3
    $sets = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
    
    $classes = array(
        array("tag" => "h1", "class" => "Headline1", "type" => "heading1"),
        array("tag" => "h2", "class" => "Headline2", "type" => "heading2"),
        array("tag" => "h3", "class" => "Headline3", "type" => "heading3"),
        array("tag" => "h1", "class" => "PageTitle", "type" => "page_title")
        );
        
    $pars = array(
        array("par" => "font-size", "value" => "100%"),
        array("par" => "margin-top", "value" => "0px"),
        array("par" => "margin-bottom", "value" => "0px"),
        array("par" => "font-weight", "value" => "normal")
        );
    
    while ($recs = $ilDB->fetchAssoc($sets)) {
        $id = $recs["obj_id"];
        foreach ($classes as $c) {
            foreach ($pars as $p) {
                $add = ($p["par"] == "margin-bottom" || $p["par"] == "margin-top")
                    ? " OR parameter = " . $ilDB->quote("margin", "text")
                    : "";
                $set2 = $ilDB->queryF(
                    "SELECT * FROM style_parameter WHERE style_id = %s" .
                    " AND tag = %s AND class = %s AND (parameter = %s " . $add . ")",
                    array("integer", "text", "text", "text"),
                    array($id, $c["tag"], $c["class"], $p["par"])
                );
                if ($rec2 = $ilDB->fetchAssoc($set2)) {
                    // do nothin
                } else {
                    // insert standard value
                    $pid = $ilDB->nextId("style_parameter");
                    $q = "INSERT INTO style_parameter (id, style_id, tag, class, parameter, value, type) VALUES (" .
                        $ilDB->quote($pid, "integer") . ", " .
                        $ilDB->quote($id, "integer") . ", " .
                        $ilDB->quote($c["tag"], "text") . ", " .
                        $ilDB->quote($c["class"], "text") . ", " .
                        $ilDB->quote($p["par"], "text") . ", " .
                        $ilDB->quote($p["value"], "text") . ", " .
                        $ilDB->quote($c["type"], "text") . ")";
                    //echo "<br>$q";
                    $ilDB->manipulate($q);
                }
            }
        }
    }
?>
<#2696>
<?php
    $sets = $ilDB->query("UPDATE style_data SET uptodate = " . $ilDB->quote(0, "integer"));
?>
<#2697>
<?php
$fields = array(
    'sbm_id' => array(
        'type' => 'integer',
        'length' => 4,
    ),
    'sbm_title' => array(
        'type' => 'text',
        'length' => 50,
    ),
    'sbm_link' => array(
        'type' => 'text',
        'length' => 200,
    ),
    'sbm_icon' => array(
        'type' => 'text',
        'length' => 200,
    ),
    'sbm_active' => array(
        'type' => 'integer',
        'length' => 1,
    ),
);

$ilDB->createTable('bookmark_social_bm', $fields);
$ilDB->addPrimaryKey('bookmark_social_bm', array('sbm_id'));
$ilDB->createSequence('bookmark_social_bm');

$id = $ilDB->nextId("bookmark_social_bm");
$ilDB->manipulateF(
    "INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) " .
        "VALUES (%s, %s, %s, %s, %s)",
    array("integer", "text", "text", "text", "integer"),
    array($id, 'Del.icio.us', 'http://del.icio.us/post?url={LINK}&title={TITLE}', 'delicious_10x10.gif',0)
);

$id = $ilDB->nextId("bookmark_social_bm");
$ilDB->manipulateF(
    "INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) " .
        "VALUES (%s, %s, %s, %s, %s)",
    array("integer", "text", "text", "text", "integer"),
    array($id, 'Digg', 'http://digg.com/submit?phase=2&url={LINK}', 'digg_10x10.gif',0)
);
?>
<#2698>
<?php
    $ilDB->addTableColumn("media_item", "text_representation", array("type" => "text", "length" => 4000, "notnull" => false));
?>
<#2699>
<?php
    $ilDB->renameTableColumn('aicc_object', 'type', 'c_type');
?>
<#2700>
<?php
    $ilDB->renameTableColumn('cmi_node', 'language', 'c_language');
?>
<#2701>
<?php
    $ilDB->renameTableColumn('cmi_node', 'mode', 'c_mode');
?>
<#2702>
<?php
    $ilDB->renameTableColumn('cp_resource', 'type', 'c_type');
?>
<#2703>
<?php
    $ilDB->renameTableColumn('cp_rule', 'type', 'c_type');
?>
<#2704>
<?php
    $ilDB->renameTableColumn('ilinc_data ', 'type', 'i_type');
?>
<#2705>
<?php
    $ilDB->renameTableColumn('mail_obj_data  ', 'type', 'm_type');
?>
<#2706>
<?php
    $ilDB->renameTableColumn('sahs_lm ', 'online', 'c_online');
?>
<#2707>
<?php
    $ilDB->renameTableColumn('scorm_object ', 'type', 'c_type');
?>
<#2708>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_comment` DROP INDEX `i1_idx`");
?>
<#2709>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_interaction` DROP INDEX `i1_idx`");
?>
<#2710>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_node` DROP INDEX `i4_idx`");
?>
<#2711>
<?php
    $ilDB->manipulate("ALTER TABLE `cmi_objective` DROP INDEX `i1_idx`");
?>
<#2712>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_dependency` DROP INDEX `i1_idx`");
?>
<#2713>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_file` DROP INDEX `i1_idx`");
?>
<#2714>
<?php
    $ilDB->manipulate("ALTER TABLE `cp_node` DROP INDEX `i1_idx`");
?>
<#2715>
<?php
    $ilDB->manipulate("ALTER TABLE `frm_thread_access` DROP INDEX `i1_idx`");
?>
<#2716>
<?php
    $ilDB->manipulate("ALTER TABLE `frm_user_read` DROP INDEX `i1_idx`");
?>
<#2717>
<?php
    $ilDB->manipulate("ALTER TABLE `frm_user_read` DROP INDEX `i2_idx`");
?>
<#2718>
<?php
    $ilDB->manipulate("ALTER TABLE `mail_obj_data` DROP INDEX `i1_idx`");
?>
<#2719>
<?php
    $ilMySQLAbstraction->performAbstraction('bookmark_social_bm');
?>
<#2720>
<?php
    $ilDB->addTableColumn("qpl_qst_sc", "allow_images", array("type" => "text", "length" => 1, "notnull" => false, "default" => "0"));
?>
<#2721>
<?php

$result = $ilDB->query(
    "SELECT DISTINCT question_fi FROM qpl_a_sc WHERE NOT ISNULL(imagefile)"
);
while ($data = $ilDB->fetchAssoc($result)) {
    $ilDB->manipulateF(
        "UPDATE qpl_qst_sc SET allow_images = %s WHERE question_fi = %s",
        array('text', 'integer'),
        array('1', $data['question_fi'])
    );
}

?>
<#2722>
<?php
    $ilDB->addTableColumn("qpl_qst_sc", "resize_images", array("type" => "text", "length" => 1, "notnull" => false, "default" => "0"));
?>
<#2723>
<?php
    $ilDB->addTableColumn("qpl_qst_sc", "thumb_size", array("type" => "integer", "length" => 2, "notnull" => false));
?>
<#2724>
<?php
    $ilDB->addTableColumn("qpl_qst_mc", "allow_images", array("type" => "text", "length" => 1, "notnull" => false, "default" => "0"));
?>
<#2725>
<?php

$result = $ilDB->query(
    "SELECT DISTINCT question_fi FROM qpl_a_mc WHERE NOT ISNULL(imagefile)"
);
while ($data = $ilDB->fetchAssoc($result)) {
    $ilDB->manipulateF(
        "UPDATE qpl_qst_mc SET allow_images = %s WHERE question_fi = %s",
        array('text', 'integer'),
        array('1', $data['question_fi'])
    );
}

?>
<#2726>
<?php
    $ilDB->addTableColumn("qpl_qst_mc", "resize_images", array("type" => "text", "length" => 1, "notnull" => false, "default" => "0"));
?>
<#2727>
<?php
    $ilDB->addTableColumn("qpl_qst_mc", "thumb_size", array("type" => "integer", "length" => 2, "notnull" => false));
?>
<#2728>
<?php
    $ilDB->manipulateF('UPDATE frm_data SET top_date = NULL WHERE  top_date = %s', array('timestamp'), array('0000-00-00 00:00:00'));
    $ilDB->manipulateF('UPDATE frm_data SET top_update = NULL WHERE  top_update = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2729>
<?php
    $ilDB->manipulateF('UPDATE frm_posts SET pos_date = NULL WHERE  pos_date = %s', array('timestamp'), array('0000-00-00 00:00:00'));
    $ilDB->manipulateF('UPDATE frm_posts SET pos_update = NULL WHERE  pos_update = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2730>
<?php
    $ilDB->manipulateF('UPDATE frm_posts_tree SET fpt_date= NULL WHERE  fpt_date= %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2731>
<?php
    $ilDB->manipulateF('UPDATE frm_threads SET thr_date = NULL WHERE  thr_date = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2732>
<?php
    $ilDB->manipulateF('UPDATE frm_thread_access SET access_old_ts = NULL WHERE  access_old_ts = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2733>
<?php
    $ilDB->manipulateF('UPDATE addressbook_mlist SET createdate = NULL WHERE  createdate = %s', array('timestamp'), array('0000-00-00 00:00:00'));
    $ilDB->manipulateF('UPDATE addressbook_mlist SET changedate = NULL WHERE  changedate = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2734>
<?php
    $ilDB->manipulateF('UPDATE cmi_comment SET c_timestamp = NULL WHERE  c_timestamp = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2735>
<?php
    $ilDB->manipulateF('UPDATE cmi_custom SET c_timestamp = NULL WHERE  c_timestamp = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2736>
<?php
    $ilDB->manipulateF('UPDATE cmi_node SET c_timestamp = NULL WHERE  c_timestamp = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2737>
<?php
    $ilDB->manipulateF('UPDATE ilinc_registration SET application_date = NULL WHERE  application_date = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2738>
<?php
    $ilDB->manipulateF('UPDATE mail SET send_time = NULL WHERE  send_time = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2739>
<?php
    $ilDB->manipulateF('UPDATE payment_coupons SET pc_from = NULL WHERE  pc_from = %s', array('date'), array('0000-00-00'));
    $ilDB->manipulateF('UPDATE payment_coupons SET pc_till = NULL WHERE  pc_till = %s', array('date'), array('0000-00-00'));
    $ilDB->manipulateF('UPDATE payment_coupons SET pc_last_changed = NULL WHERE  pc_last_changed =%s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2740>
<?php
    $ilDB->manipulateF('UPDATE payment_coupons_track SET pct_date = NULL WHERE  pct_date = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2741>
<?php

    $ilDB->manipulateF('UPDATE payment_news SET creation_date = NULL WHERE  creation_date = %s', array('timestamp'), array('0000-00-00 00:00:00'));
    $ilDB->manipulateF('UPDATE payment_news SET update_date = NULL WHERE  update_date = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2742>
<?php
    $ilDB->manipulateF('UPDATE scorm_tracking SET c_timestamp = NULL WHERE  c_timestamp = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2743>
<?php
    $ilMySQLAbstraction->performAbstraction('container_reference');
?>
<#2744>
<?php
    $ilDB->renameTable('usr_new_account_mail', 'mail_template');
    $ilDB->manipulate('ALTER TABLE mail_template DROP PRIMARY KEY');
    $ilDB->addTableColumn("mail_template", "type", array("type" => "text", "length" => 4, "notnull" => true, 'default' => ''));
    $ilDB->addPrimaryKey("mail_template", array("type", "lang"));
    $ilDB->manipulate("UPDATE mail_template SET type = 'nacc'");
?>
<#2745>
<?php
// media object and media pool settings

// register new object type 'mobs' for media pool and media object settings
$id = $ilDB->nextId("object_data");
$ilDB->manipulateF(
    "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) " .
        "VALUES (%s, %s, %s, %s, %s, %s, %s)",
    array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
    array($id, "typ", "mobs", "Media Object/Pool settings", -1, ilUtil::now(), ilUtil::now())
);
$typ_id = $id;

// create object data entry
$id = $ilDB->nextId("object_data");
$ilDB->manipulateF(
    "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) " .
        "VALUES (%s, %s, %s, %s, %s, %s, %s)",
    array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
    array($id, "mobs", "__MediaObjectSettings", "Media Object/Pool Settings", -1, ilUtil::now(), ilUtil::now())
);

// create object reference entry
$ref_id = $ilDB->nextId('object_reference');
$res = $ilDB->manipulateF(
    "INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
    array("integer", "integer"),
    array($ref_id, $id)
);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

// add rbac operations
// 1: edit_permissions, 2: visible, 3: read, 4:write
$ilDB->manipulateF(
    "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
    array("integer", "integer"),
    array($typ_id, 1)
);
$ilDB->manipulateF(
    "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
    array("integer", "integer"),
    array($typ_id, 2)
);
$ilDB->manipulateF(
    "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
    array("integer", "integer"),
    array($typ_id, 3)
);
$ilDB->manipulateF(
    "INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
    array("integer", "integer"),
    array($typ_id, 4)
);

?>
<#2746>
<?php
  $ilCtrlStructureReader->getStructure();
?>
<#2747>
<?php
$ilDB->createTable("mep_item", array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'type' => array(
        'type' => 'text',
        'length' => 10,
        'notnull' => false
    ),
    'title' => array(
        'type' => 'text',
        'length' => 128,
        'notnull' => false
    ),
    'foreign_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => false
    )
));
    
?>
<#2748>
<?php
$ilDB->createSequence("mep_item");
?>
<#2749>
<?php
    $ilDB->addPrimaryKey("mep_item", array("id"));

    $nid = $ilDB->nextId("mep_item");
    $q = "INSERT INTO mep_item " .
        "(id, type, title, foreign_id) VALUES (" .
        $ilDB->quote($nid, "integer") . "," .
        $ilDB->quote("dummy", "text") . "," .
        $ilDB->quote("Dummy", "text") . "," .
        $ilDB->quote(1, "integer") .
        ")";
    $ilDB->manipulate($q);
    $set = $ilDB->query("SELECT * FROM mep_tree LEFT JOIN object_data ON child = obj_id");
    while ($rec = $ilDB->fetchAssoc($set)) {
        if ($rec["child"] > 0) {
            if ($rec["child"] > 1) {
                $nid = $ilDB->nextId("mep_item");
                $q = "INSERT INTO mep_item " .
                    "(id, type, title, foreign_id) VALUES (" .
                    $ilDB->quote($nid, "integer") . "," .
                    $ilDB->quote($rec["type"], "text") . "," .
                    $ilDB->quote($rec["title"], "text") . "," .
                    $ilDB->quote($rec["child"], "integer") .
                    ")";
                $ilDB->manipulate($q);

                $q = "UPDATE mep_tree SET " .
                    " child = " . $ilDB->quote($nid, "integer") .
                    " WHERE mep_id = " . $ilDB->quote($rec["mep_id"], "integer") .
                    " AND child = " . $ilDB->quote($rec["child"], "integer");
                $ilDB->manipulate($q);
                $q = "UPDATE mep_tree SET " .
                    " parent = " . $ilDB->quote($nid, "integer") .
                    " WHERE mep_id = " . $ilDB->quote($rec["mep_id"], "integer") .
                    " AND parent = " . $ilDB->quote($rec["child"], "integer");
                $ilDB->manipulate($q);
            }
        }
    }
?>
<#2750>
<?php
$ilDB->renameTableColumn("mep_item", "id", "obj_id");
?>
<#2751>
<?php
$ilDB->addTableColumn("svy_material", "text_material", array("type" => "text", "length" => 4000, "notnull" => false, 'default' => null));
?>
<#2752>
<?php
$ilDB->addTableColumn("svy_material", "external_link", array("type" => "text", "length" => 500, "notnull" => false, 'default' => null));
?>
<#2753>
<?php
$ilDB->addTableColumn("svy_material", "file_material", array("type" => "text", "length" => 200, "notnull" => false, 'default' => null));
?>
<#2754>
<?php
$ilDB->addTableColumn("svy_material", "material_type", array("type" => "integer", "length" => 4, "notnull" => false, 'default' => 0));
?>
<#2755>
<?php
if ($ilDB->tableExists('svy_qst_mat')) {
    $ilDB->dropTable('svy_qst_mat');
}
?>
<#2756>
<?php
$affectedrows = $ilDB->manipulateF(
    'UPDATE svy_question SET questiontype_fi = %s WHERE question_id IN (SELECT question_fi FROM svy_qst_nominal WHERE subtype = %s)',
    array('integer', 'text'),
    array(2, "1")
);
if (count($affectedrows)) {
    $result = $ilDB->queryF(
        'SELECT * FROM svy_qst_nominal WHERE subtype = %s',
        array('text'),
        array("1")
    );
    while ($row = $ilDB->fetchAssoc($result)) {
        $affectedrows = $ilDB->manipulateF(
            'INSERT INTO svy_qst_ordinal (question_fi, orientation) VALUES (%s, %s)',
            array('integer', 'text'),
            array($row['question_fi'], $row['orientation'])
        );
    }
    $affectedrows = $ilDB->manipulateF(
        'DELETE FROM svy_qst_nominal WHERE subtype = %s',
        array('text'),
        array("1")
    );
}
?>
<#2757>
<?php
$ilDB->dropTableColumn('svy_qst_nominal', 'subtype');
?>
<#2758>
<?php
    $ilDB->renameTable('svy_qst_nominal', 'svy_qst_mc');
?>
<#2759>
<?php
    $ilDB->renameTable('svy_qst_ordinal', 'svy_qst_sc');
?>
<#2760>
<?php
    $ilDB->manipulateF(
    'UPDATE svy_qtype SET type_tag = %s WHERE type_tag = %s',
    array('text', 'text'),
    array('SurveyMultipleChoiceQuestion', 'SurveyNominalQuestion')
);
?>
<#2761>
<?php
    $ilDB->manipulateF(
    'UPDATE svy_qtype SET type_tag = %s WHERE type_tag = %s',
    array('text', 'text'),
    array('SurveySingleChoiceQuestion', 'SurveyOrdinalQuestion')
);
?>
<#2762>
<?php
  $ilCtrlStructureReader->getStructure();
?>
<#2763>
<?php
$ilDB->addTableColumn("udf_definition", "registration_visible", array("type" => "integer", "length" => 1, "notnull" => false, 'default' => 0));
?>
<#2764>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2765>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2766>
<?php
    $ilDB->createTable("udf_text", array(
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'field_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'value' => array(
            'type' => 'text',
            'length' => 4000,
            'fixed' => false,
            'notnull' => false
        )
    ));
    $ilDB->addPrimaryKey("udf_text", array("usr_id", "field_id"));
?>
<#2767>
<?php
    $ilDB->createTable("udf_clob", array(
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'field_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'value' => array(
            'type' => 'clob'
        )
    ));
    $ilDB->addPrimaryKey("udf_clob", array("usr_id", "field_id"));
?>
<#2768>
<?php
    $set = $ilDB->query("SELECT * FROM udf_data");
    while ($rec = $ilDB->fetchAssoc($set)) {
        $user_id = $rec["usr_id"];
        foreach ($rec as $f => $v) {
            if ($f != "usr_id") {
                $fid = (int) substr($f, 2);
                $q = "INSERT INTO udf_text (usr_id, field_id, value) VALUES (" .
                    $ilDB->quote($user_id, "integer") . "," .
                    $ilDB->quote($fid, "integer") . "," .
                    $ilDB->quote($v, "text") . ")";
                $ilDB->query($q);
            }
        }
    }
?>
<#2769>
<?php
    $ilDB->createTable("license_data", array(
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'licenses' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'used' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'remarks' => array(
            'type' => 'clob'
        )
    ));
    $ilDB->addPrimaryKey("license_data", array("obj_id"));
?>
<#2770>
<?php
if ($ilDB->tableExists('svy_inv_grp')) {
    $ilDB->dropTable('svy_inv_grp');
}
?>
<#2771>
<?php
if ($ilDB->tableExists('svy_inv_grp_seq')) {
    $ilDB->dropTable('svy_inv_grp_seq');
}
?>
<#2772>
<?php
$ilDB->addTableColumn("tst_tests", "mailnotification", array("type" => "integer", "length" => 1, "notnull" => false, 'default' => 0));
?>
?>
<#2773>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2774>
<?php

$ilDB->manipulateF(
    "UPDATE bookmark_social_bm " .
        "SET sbm_icon=%s WHERE sbm_title=%s",
    array("text", "text"),
    array('digg_15x15.gif','Digg')
);

$ilDB->manipulateF(
    "UPDATE bookmark_social_bm " .
        "SET sbm_icon=%s WHERE sbm_title=%s",
    array("text", "text"),
    array('delicious_15x15.gif','Del.icio.us')
);

$id = $ilDB->nextId("bookmark_social_bm");
$ilDB->manipulateF(
    "INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) " .
        "VALUES (%s, %s, %s, %s, %s)",
    array("integer", "text", "text", "text", "integer"),
    array($id, 'Mister Wong', 'http://www.mister-wong.com/index.php?action=addurl&bm_url={LINK}&bm_description={TITLE}', 'mister_wong_15x15.jpg',0)
);

$id = $ilDB->nextId("bookmark_social_bm");
$ilDB->manipulateF(
    "INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) " .
        "VALUES (%s, %s, %s, %s, %s)",
    array("integer", "text", "text", "text", "integer"),
    array($id, 'StumbleUpon', 'http://www.stumbleupon.com/submit?url={LINK}&title={TITLE}', 'stumbleupon_15x15.jpg',0)
);

?>
<#2775>
<?php
// must use mysql_query here, since $ilDB makes '' a null
mysql_query("UPDATE il_tag SET sub_obj_type = '-' " .
        " WHERE sub_obj_type = '' OR sub_obj_type IS NULL");

?>
<#2776>
<?php
$ilDB->addTableColumn("usr_data", "birthday", array("type" => "date", "notnull" => false, 'default' => null));
?>
<#2777>
<?php
    $ilDB->addTableColumn("ut_access", "ut_month", array(
        "type" => "text",
        "notnull" => false,
        "length" => 10,
        "fixed" => false
        ));
    $ilDB->manipulate("UPDATE ut_access SET ut_month = substr(acc_time, 1, 7)");
?>
<#2778>
<?php
    $ilMySQLAbstraction->performAbstraction("page_layout");
?>
<#2779>
<?php

    $ilMySQLAbstraction->performAbstraction("xmlnestedset");
?>
<#2780>
<?php

    $ilMySQLAbstraction->performAbstraction("xmlparam");
?>
<#2781>
<?php

    $ilMySQLAbstraction->performAbstraction("xmltags");
?>
<#2782>
<?php
    $ilMySQLAbstraction->performAbstraction("xmlvalue");
?>
<#2783>
<?php
    $ilMySQLAbstraction->performAbstraction("cal_entry_responsible");
?>
<#2784>
<?php
    $ilMySQLAbstraction->performAbstraction("file_usage");
?>
<#2785>
<?php
    $ilMySQLAbstraction->performAbstraction("page_question");
?>
<#2786>
<?php
    $ilMySQLAbstraction->performAbstraction("personal_pc_clipboard");
?>
<#2787>
<?php
    $ilDB->dropIndex('sahs_sc13_seq_item', 'i1');
?>
<#2788>
<?php
    $ilDB->dropIndex('sahs_sc13_seq_node', 'i1');
?>
<#2789>
<?php
    $ilDB->modifyTableColumn("cmi_node", "audio_level", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_node", "completion", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("aicc_units", "max_score", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_interaction", "weighting", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_node", "attemptcomplamount", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_node", "delivery_speed", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_node", "c_max", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_node", "c_min", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_node", "progress_measure", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_node", "c_raw", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_node", "scaled", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_node", "scaled_passing_score", array("type" => "float", "notnull" => false, "default" => null ));
    $ilDB->modifyTableColumn("cmi_node", "launch_data", array("type" => "clob", "notnull" => false));
    $ilDB->modifyTableColumn("cmi_node", "suspend_data", array("type" => "clob", "notnull" => false));
    $ilDB->modifyTableColumn("cp_suspend", "data", array("type" => "clob", "notnull" => false));
    $ilDB->modifyTableColumn("sc_item", "datafromlms", array("type" => "clob", "notnull" => false));
?>
<#2790>
<?php
    $ilMySQLAbstraction->performAbstraction("payment_bill_vendor");
?>
<#2791>
<?php
    $ilMySQLAbstraction->performAbstraction("payment_topic_usr_sort");
?>
<#2792>
<?php
    $ilMySQLAbstraction->performAbstraction("payment_news");
?>
<#2793>
<?php
    $res = $ilDB->queryF(
    "SELECT * FROM qpl_qst_type WHERE type_tag = %s",
    array('text'),
    array('assErrorText')
);
    if ($res->numRows() == 0) {
        $res = $ilDB->query("SELECT MAX(question_type_id) maxid FROM qpl_qst_type");
        $data = $ilDB->fetchAssoc($res);
        $max = $data["maxid"] + 1;

        $affectedRows = $ilDB->manipulateF(
            "INSERT INTO qpl_qst_type (question_type_id, type_tag, plugin) VALUES (%s, %s, %s)",
            array("integer", "text", "integer"),
            array($max, 'assErrorText', 0)
        );
    }
?>
<#2794>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2795>
<?php
    if (!$ilDB->tableExists("qpl_qst_errortext")) {
        $ilDB->createTable(
            "qpl_qst_errortext",
            array(
                "question_fi" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "errortext" => array(
                    "type" => "text", "length" => 4000, "notnull" => true
                ),
                "textsize" => array(
                    "type" => "float", "notnull" => true, "default" => 100
                )
            )
        );
        $ilDB->addPrimaryKey("qpl_qst_errortext", array("question_fi"));
    }
?>
<#2796>
<?php
    if (!$ilDB->tableExists("qpl_a_errortext")) {
        $ilDB->createTable(
            "qpl_a_errortext",
            array(
                "answer_id" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "question_fi" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "text_wrong" => array(
                    "type" => "text", "length" => 150, "notnull" => true
                ),
                "text_correct" => array(
                    "type" => "text", "length" => 150, "notnull" => false, "default" => null
                ),
                "points" => array(
                    "type" => "float", "notnull" => true, "default" => 0
                ),
                "sequence" => array(
                    "type" => "integer", "length" => 2, "notnull" => true, "default" => 0
                )
            )
        );
        $ilDB->addPrimaryKey("qpl_a_errortext", array("answer_id"));
        $ilDB->addIndex('qpl_a_errortext', array('question_fi'), 'i1');
    }
?>
<#2797>
<?php
$ilDB->createSequence("qpl_a_errortext");
?>
<#2798>
<?php
    $ilMySQLAbstraction->performAbstraction("ecs_container_mapping");
?>
<#2799>
<?php
    $ilDB->addTableColumn("qpl_qst_errortext", "points_wrong", array(
        "type" => "float",
        "notnull" => true,
        "default" => -1.0
    ));
?>
<#2800>
<?php
if (!$ilDB->tableExists("xmlnestedsettmp")) {
    $ilDB->createTable(
        "xmlnestedsettmp",
        array(
            "ns_unique_id" => array(// text because maybe we have to store a session_id in future e.g.
                "type" => "text", "length" => 32, "notnull" => true),
            "ns_book_fk" => array(
                "type" => "integer", "length" => 4, "notnull" => true),
            "ns_type" => array(
                "type" => "text", "length" => 50, "notnull" => true),
            "ns_tag_fk" => array(
                "type" => "integer", "length" => 4, "notnull" => true),
            "ns_l" => array(
                "type" => "integer", "length" => 4, "notnull" => true),
            "ns_r" => array(
                "type" => "integer", "length" => 4, "notnull" => true)
                
        )
    );
    $ilDB->addIndex("xmlnestedsettmp", array("ns_tag_fk"), 'i1');
    $ilDB->addIndex("xmlnestedsettmp", array("ns_l"), 'i2');
    $ilDB->addIndex("xmlnestedsettmp", array("ns_r"), 'i3');
    $ilDB->addIndex("xmlnestedsettmp", array("ns_book_fk"), 'i4');
    $ilDB->addIndex("xmlnestedsettmp", array("ns_unique_id"), 'i5');
}
?>
<#2801>
<?php
    $ilDB->addTableColumn("crs_settings", "session_limit", array(
        "type" => "integer",
        'length' => 1,
        "notnull" => true,
        "default" => 0
    ));
?>
<#2802>
<?php
    $ilDB->addTableColumn("crs_settings", "session_prev", array(
        "type" => "integer",
        'length' => 8,
        "notnull" => true,
        "default" => -1
    ));
?>
<#2803>
<?php
    $ilDB->addTableColumn("crs_settings", "session_next", array(
        "type" => "integer",
        'length' => 8,
        "notnull" => true,
        "default" => -1
    ));
?>
<#2804>
<?php
    $ilMySQLAbstraction->performAbstraction('ut_access');
?>
<#2805>
<?php
    $ilDB->manipulateF('UPDATE frm_data SET top_date = NULL WHERE  top_date = %s', array('timestamp'), array('0000-00-00 00:00:00'));
    $ilDB->manipulateF('UPDATE frm_data SET top_update = NULL WHERE  top_update = %s', array('timestamp'), array('0000-00-00 00:00:00'));
    $ilDB->manipulateF('UPDATE frm_posts SET pos_date = NULL WHERE  pos_date = %s', array('timestamp'), array('0000-00-00 00:00:00'));
    $ilDB->manipulateF('UPDATE frm_posts SET pos_update = NULL WHERE  pos_update = %s', array('timestamp'), array('0000-00-00 00:00:00'));
    $ilDB->manipulateF('UPDATE frm_posts_tree SET fpt_date= NULL WHERE  fpt_date= %s', array('timestamp'), array('0000-00-00 00:00:00'));
    $ilDB->manipulateF('UPDATE frm_threads SET thr_date = NULL WHERE  thr_date = %s', array('timestamp'), array('0000-00-00 00:00:00'));
    $ilDB->manipulateF('UPDATE frm_thread_access SET access_old_ts = NULL WHERE  access_old_ts = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>
<#2806>
<?php
    $ilDB->manipulateF('UPDATE frm_threads SET thr_update = NULL WHERE  thr_update = %s', array('timestamp'), array('0000-00-00 00:00:00'));
?>

<#2807>
<?php
    $query = "UPDATE object_reference SET deleted = NULL WHERE deleted = '0000-00-00 00:00:00'";
    $ilDB->query($query);
?>

<#2808>
<?php
    if (!$ilDB->tableColumnExists("ldap_server_settings", "migration")) {
        $ilDB->addTableColumn("ldap_server_settings", "migration", array(
            "type" => "integer",
            'length' => 1,
            "notnull" => true,
            "default" => 0
        ));
    }
?>
<#2809>
<?php
    if (!$ilDB->tableExists("tst_rnd_cpy")) {
        $ilDB->createTable(
            "tst_rnd_cpy",
            array(
                "copy_id" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "tst_fi" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "qst_fi" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "qpl_fi" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                )
            )
        );
        $ilDB->addPrimaryKey("tst_rnd_cpy", array("copy_id"));
        $ilDB->addIndex('tst_rnd_cpy', array('qst_fi'), 'i1');
        $ilDB->addIndex('tst_rnd_cpy', array('qpl_fi'), 'i2');
        $ilDB->addIndex('tst_rnd_cpy', array('tst_fi'), 'i3');
        $ilDB->createSequence("tst_rnd_cpy");
    }
?>
<#2810>
<?php
    $ilDB->modifyTableColumn('ut_access', 'browser', array("type" => "text", "length" => 255, 'notnull' => false));
?>
<#2811>
<?php
$ilDB->createTable(
    "cache_text",
    array(
        "component" => array(
            "type" => "text", "length" => 50, "fixed" => false, "notnull" => true
        ),
        "name" => array(
            "type" => "text", "length" => 50, "fixed" => false, "notnull" => true
        ),
        "entry_id" => array(
            "type" => "text", "length" => 50, "fixed" => false, "notnull" => true
        ),
        "value" => array(
            "type" => "text", "length" => 4000, "fixed" => false, "notnull" => false
        ),
        "expire_time" => array(
            "type" => "integer", "length" => 4, "notnull" => true
        ),
        "ilias_version" => array(
            "type" => "text", "length" => 10, "notnull" => false
        )
    )
);
$ilDB->addPrimaryKey("cache_text", array("component", "name", "entry_id"));
?>
<#2812>
<?php
$ilDB->createTable(
    "cache_clob",
    array(
        "component" => array(
            "type" => "text", "length" => 50, "fixed" => false, "notnull" => true
        ),
        "name" => array(
            "type" => "text", "length" => 50, "fixed" => false, "notnull" => true
        ),
        "entry_id" => array(
            "type" => "text", "length" => 50, "fixed" => false, "notnull" => true
        ),
        "value" => array(
            "type" => "clob"
        ),
        "expire_time" => array(
            "type" => "integer", "length" => 4, "notnull" => true
        ),
        "ilias_version" => array(
            "type" => "text", "length" => 10, "notnull" => false
        )
    )
);
$ilDB->addPrimaryKey("cache_clob", array("component", "name", "entry_id"));
?>
<#2813>
<?php
    $ilDB->addIndex('cache_clob', array('expire_time'), 'et');
    $ilDB->addIndex('cache_text', array('expire_time'), 'et');
    $ilDB->addIndex('cache_clob', array('ilias_version'), 'iv');
    $ilDB->addIndex('cache_text', array('ilias_version'), 'iv');
?>
<#2814>
<?php
    if (!$ilDB->tableExists("tst_rnd_qpl_title")) {
        $ilDB->createTable(
            "tst_rnd_qpl_title",
            array(
                "title_id" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "qpl_fi" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "tst_fi" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "qpl_title" => array(
                    "type" => "text", "length" => 1000, "notnull" => true
                )
            )
        );
        $ilDB->addPrimaryKey("tst_rnd_qpl_title", array("title_id"));
        $ilDB->addIndex('tst_rnd_qpl_title', array('qpl_fi'), 'i1');
        $ilDB->addIndex('tst_rnd_qpl_title', array('tst_fi'), 'i2');
        $ilDB->createSequence("tst_rnd_qpl_title");
    }
?>
<#2815>
<?php
    $ilDB->modifyTableColumn('tst_tests', 'processing_time', array("type" => "text", "length" => 8, 'notnull' => false));
?>
<#2816>
<?php
    $ilDB->modifyTableColumn('qpl_questions', 'working_time', array("type" => "text", "length" => 8, 'notnull' => false));
?>
<#2817>
<?php
    if (!$ilDB->tableColumnExists("qpl_a_mterm", "picture")) {
        $ilDB->addTableColumn("qpl_a_mterm", "picture", array(
            "type" => "text",
            'length' => 1000,
            "notnull" => false
        ));
    }
?>
<#2818>
<?php
    if (!$ilDB->tableExists("qpl_a_mdef")) {
        $ilDB->createTable(
            "qpl_a_mdef",
            array(
                "def_id" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "question_fi" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "definition" => array(
                    "type" => "text", "length" => 1000, "notnull" => false
                ),
                "morder" => array(
                    "type" => "integer", "length" => 4, "notnull" => true
                ),
                "picture" => array(
                    "type" => "text", "length" => 1000, "notnull" => false
                )
            )
        );
        $ilDB->addPrimaryKey("qpl_a_mdef", array("def_id"));
        $ilDB->addIndex('qpl_a_mdef', array('question_fi'), 'i1');
        $ilDB->createSequence("qpl_a_mdef");
    }
?>
<#2819>
<?php
    if (!$ilDB->tableColumnExists("qpl_a_matching", "definition_fi")) {
        $ilDB->addTableColumn("qpl_a_matching", "definition_fi", array(
            "type" => "integer",
            'length' => 4,
            "notnull" => true,
            "default" => 0
        ));
    }
?>
<#2820>
<?php
    $result = $ilDB->query('SELECT qpl_a_matching.*, qpl_questions.obj_fi FROM qpl_a_matching, qpl_questions WHERE qpl_a_matching.question_fi = qpl_questions.question_id');
    while ($row = $ilDB->fetchAssoc($result)) {
        $next_id = $ilDB->nextId("qpl_a_mdef");
        if (@file_exists(CLIENT_WEB_DIR . "/assessment/" . $row['obj_fi'] . "/" . $row['question_fi'] . "/images/" . $row['matchingtext'])) {
            $affectedRows = $ilDB->manipulateF(
                'INSERT INTO qpl_a_mdef (def_id, question_fi, definition, morder, picture) VALUES (%s, %s, %s, %s, %s)',
                array('integer', 'integer', 'text', 'integer', 'text'),
                array($next_id, $row['question_fi'], null, $row['matching_order'], $row['matchingtext'])
            );
        } else {
            $affectedRows = $ilDB->manipulateF(
                'INSERT INTO qpl_a_mdef (def_id, question_fi, definition, morder, picture) VALUES (%s, %s, %s, %s, %s)',
                array('integer', 'integer', 'text', 'integer', 'text'),
                array($next_id, $row['question_fi'], $row['matchingtext'], $row['matching_order'], null)
            );
        }
        $affectedRows = $ilDB->manipulateF(
            'UPDATE qpl_a_matching SET definition_fi = %s WHERE answer_id = %s',
            array('integer', 'integer'),
            array($next_id, $row['answer_id'])
        );
    }
?>
<#2821>
<?php
    $ilDB->dropTableColumn("qpl_a_matching", "matchingtext");
    $ilDB->dropTableColumn("qpl_a_matching", "matching_order");
    $ilDB->dropTableColumn("qpl_a_matching", "tstamp");
?>
<#2822>
<?php
    $d_set = new ilSetting("delicious");
    if ($d_set->get("user_profile") != "1") {
        $set = new ilSetting();
        $set->set("usr_settings_hide_delicious", 1);
    }
?>
<#2823>
<?php
    $ilDB->addTableColumn(
    "cache_text",
    "int_key_1",
    array("type" => "integer", "length" => 4, "notnull" => false)
);
    $ilDB->addTableColumn(
        "cache_text",
        "int_key_2",
        array("type" => "integer", "length" => 4, "notnull" => false)
    );
    $ilDB->addTableColumn(
        "cache_text",
        "text_key_1",
        array("type" => "text", "length" => 20, "fixed" => false, "notnull" => false)
    );
    $ilDB->addTableColumn(
        "cache_text",
        "text_key_2",
        array("type" => "text", "length" => 20, "fixed" => false, "notnull" => false)
    );
?>
<#2824>
<?php
    $ilDB->addTableColumn(
    "cache_clob",
    "int_key_1",
    array("type" => "integer", "length" => 4, "notnull" => false)
);
    $ilDB->addTableColumn(
        "cache_clob",
        "int_key_2",
        array("type" => "integer", "length" => 4, "notnull" => false)
    );
    $ilDB->addTableColumn(
        "cache_clob",
        "text_key_1",
        array("type" => "text", "length" => 20, "fixed" => false, "notnull" => false)
    );
    $ilDB->addTableColumn(
        "cache_clob",
        "text_key_2",
        array("type" => "text", "length" => 20, "fixed" => false, "notnull" => false)
    );
?>
<#2825>
<?php
    $ilDB->modifyTableColumn("write_event", "write_id", array("type" => "integer", "length" => 4, "notnull" => true));
    $set = $ilDB->query("SELECT MAX(write_id) ma FROM write_event");
    $rec = $ilDB->fetchAssoc($set);
    $next = $rec["ma"] + 1;
    $ilDB->createSequence("write_event", $next);
?>
<#2826>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2827>
<?php
    $setting = new ilSetting();
    $setting->set("icon_position_in_lists", "item_rows");
?>

<#2828>
<?php
    $query = "SELECT value FROM settings WHERE module = 'common' AND keyword = 'search_max_hits'";
    $max = 10;
    $res = $ilDB->query($query);
    $has_entry = $res->numRows() ? true : false;
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        if (in_array($row->value, array(5,10,15))) {
            $max = $row->value;
        }
    }
    
    if ($has_entry) {
        $ilDB->update(
            'settings',
            array(
                'value' => array('clob', $max)
            ),
            array(
                'module' => array('text', 'common'),
                'keyword' => array('text', 'search_max_hits')
            )
        );
    } else {
        $ilDB->insert(
            'settings',
            array(
                'value' => array('clob',$max),
                'module' => array('text','common'),
                'keyword' => array('text','search_max_hits')
            )
        );
    }
?>
<#2829>
<?php
    $setting = new ilSetting();
    $setting->set('reg_hash_life_time', 600);
?>
<#2830>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2831>
<?php
    $ilDB->addTableColumn("sahs_lm", "unlimited_session", array(
            "type" => "text",
            'length' => 1,
            "notnull" => true,
            "default" => 'n'
        ));
?>
<#2832>
<?php
    $ilDB->addTableColumn("sahs_lm", "no_menu", array(
            "type" => "text",
            'length' => 1,
            "notnull" => true,
            "default" => 'n'
        ));
?>
<#2833>
<?php
    $ilDB->addTableColumn("sahs_lm", "hide_navig", array(
            "type" => "text",
            'length' => 1,
            "notnull" => true,
            "default" => 'n'
        ));
?>
<#2834>
<?php
    $ilDB->addTableColumn("sahs_lm", "debug", array(
            "type" => "text",
            'length' => 1,
            "notnull" => true,
            "default" => 'n'
        ));
?>
<#2835>
<?php
    $ilDB->addTableColumn("sahs_lm", "debugpw", array(
            "type" => "text",
            'length' => 50,
            "notnull" => false,
            "default" => 'n'
        ));
?>
<#2836>
<?php
    $ilDB->modifyTableColumn("cp_manifest", "defaultorganization", array("type" => "text", "length" => 50, "notnull" => false, 'default' => null));
?>

<#2837>
<?php

if (!$ilDB->tableColumnExists('container_sorting', 'parent_type')) {
    $ilDB->addTableColumn("container_sorting", "parent_type", array(
            "type" => "text",
            "fixed" => false,
            'length' => 5,
            "notnull" => false,
            "default" => ''
        ));
}
?>
	
<#2838>
<?php

if (!$ilDB->tableColumnExists('container_sorting', 'parent_id')) {
    $ilDB->addTableColumn("container_sorting", "parent_id", array(
            "type" => "integer",
            'length' => 4,
            "notnull" => false,
            "default" => 0
        ));
}
?>

<#2839>
<?php

$ilDB->dropPrimaryKey('container_sorting');

?>

<#2840>
<?php

$ilDB->addPrimaryKey('container_sorting', array('obj_id','child_id','parent_id'));

?>
<#2841>
<?php
    $ilDB->addTableColumn("mail", "m_message_tmp", array(
        "type" => "clob",
        "notnull" => false,
        "default" => null
    ));
    
    $ilDB->manipulate('UPDATE mail SET m_message_tmp = m_message');
    
    $ilDB->dropTableColumn('mail', 'm_message');
    $ilDB->renameTableColumn("mail", "m_message_tmp", "m_message");
?>
<#2842>
<?php
    $ilDB->addTableColumn("mail_saved", "m_message_tmp", array(
        "type" => "clob",
        "notnull" => false,
        "default" => null
    ));
    
    $ilDB->manipulate('UPDATE mail_saved SET m_message_tmp = m_message');
    
    $ilDB->dropTableColumn('mail_saved', 'm_message');
    $ilDB->renameTableColumn("mail_saved", "m_message_tmp", "m_message");
?>
<#2843>
<?php
    $ilDB->addTableColumn("frm_posts", "pos_message_tmp", array(
        "type" => "clob",
        "notnull" => false,
        "default" => null
    ));
    
    $ilDB->manipulate('UPDATE frm_posts SET pos_message_tmp = pos_message');
    
    $ilDB->dropTableColumn('frm_posts', 'pos_message');
    $ilDB->renameTableColumn("frm_posts", "pos_message_tmp", "pos_message");
?>
<#2844>
<?php
  if (!$ilDB->tableColumnExists('payment_settings', 'hide_advanced_search')) {
      $ilDB->addTableColumn("payment_settings", "hide_advanced_search", array("type" => "integer", "length" => 4, "notnull" => false));
  }
?>
<#2845>
<?php
  if (!$ilDB->tableColumnExists('payment_settings', 'hide_filtering')) {
      $ilDB->addTableColumn("payment_settings", "hide_filtering", array("type" => "integer", "length" => 4, "notnull" => false));
  }
?>
<#2846>
<?php
  if (!$ilDB->tableColumnExists('payment_settings', 'hide_coupons')) {
      $ilDB->addTableColumn("payment_settings", "hide_coupons", array("type" => "integer", "length" => 4, "notnull" => false));
  }
?>

<#2847>
<?php
  $ilDB->modifyTableColumn("payment_settings", "address", array("type" => "text", "notnull" => false, "default" => null, "length" => 1600));
?>
<#2848>
<?php
  $ilDB->modifyTableColumn("payment_settings", "paypal", array("type" => "text", "notnull" => false, "default" => null, "length" => 1600));
?>
<#2849>
<?php
  $ilDB->modifyTableColumn("payment_settings", "bank_data", array("type" => "text", "notnull" => false, "default" => null, "length" => 1600));
?>
<#2850>
<?php
  if (!$ilDB->tableColumnExists('payment_settings', 'epay')) {
      $ilDB->addTableColumn("payment_settings", "epay", array( "type" => "text", "notnull" => false, "default" => null, "length" => 1600));
  }
?>
<#2851>
<?php
  $ilDB->modifyTableColumn("cp_mapinfo", "targetobjectiveid", array("type" => "text",  "length" => 50, "notnull" => false, "default" => null));
?>
<#2852>
<?php
  $ilDB->modifyTableColumn("cp_rule", "minimumcount", array("type" => "integer",  "length" => 4, "notnull" => false, "default" => null));
?>

<#2853>
<?php

    if ($ilDB->getDBType() == 'oracle') {
        $query = "SELECT column_name FROM user_tab_columns " .
            "WHERE table_name = 'MAIL' " .
            "AND column_name = 'm_message' ";
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $ilDB->query('ALTER TABLE mail RENAME COLUMN "m_message" TO m_message');
        }
    }
?>
<#2854>
<?php
    if ($ilDB->getDBType() == 'oracle') {
        $query = "SELECT column_name FROM user_tab_columns " .
            "WHERE table_name = 'MAIL_SAVED' " .
            "AND column_name = 'm_message' ";
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $ilDB->query('ALTER TABLE mail_saved RENAME COLUMN "m_message" TO m_message');
        }
    }
?>
<#2855>
<?php
    if ($ilDB->getDBType() == 'oracle') {
        $query = "SELECT column_name FROM user_tab_columns " .
            "WHERE table_name = 'FRM_POSTS' " .
            "AND column_name = 'pos_message' ";
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $ilDB->query('ALTER TABLE mail_saved RENAME COLUMN "m_message" TO m_message');
        }
    }
?>
<#2856>
<?php
  if (!$ilDB->tableColumnExists('payment_settings', 'hide_news')) {
      $ilDB->addTableColumn("payment_settings", "hide_news", array("type" => "integer", "length" => 4, "notnull" => false));
  }
?>
<#2857>
<?php
    $ilDB->addTableColumn("scorm_tracking", "rvalue_tmp", array(
        "type" => "clob",
        "notnull" => false,
        "default" => null
    ));
    
    $ilDB->manipulate('UPDATE scorm_tracking SET rvalue_tmp = rvalue');
    
    $ilDB->dropTableColumn('scorm_tracking', 'rvalue');
    $ilDB->renameTableColumn("scorm_tracking", "rvalue_tmp", "rvalue");
?>
<#2858>
<?php
    $ilDB->addTableColumn("cmi_interaction", "learner_response_tmp", array(
        "type" => "clob",
        "notnull" => false,
        "default" => null
    ));
    
    $ilDB->manipulate('UPDATE cmi_interaction SET learner_response_tmp = learner_response');
    
    $ilDB->dropTableColumn('cmi_interaction', 'learner_response');
    $ilDB->renameTableColumn("cmi_interaction", "learner_response_tmp", "learner_response");
?>
<#2859>
<?php
    // just for development versions
    $ilDB->addTableColumn("qpl_questions", "question_text_tmp", array(
        "type" => "clob",
        "notnull" => false,
        "default" => null
    ));

    $ilDB->manipulate('UPDATE qpl_questions SET question_text_tmp = question_text');

    $ilDB->dropTableColumn('qpl_questions', 'question_text');
    $ilDB->renameTableColumn("qpl_questions", "question_text_tmp", "question_text");
?>
<#2860>
<?php
    // just for development versions
    $ilDB->addTableColumn("svy_question", "questiontext_tmp", array(
        "type" => "clob",
        "notnull" => false,
        "default" => null
    ));

    $ilDB->manipulate('UPDATE svy_question SET questiontext_tmp = questiontext');

    $ilDB->dropTableColumn('svy_question', 'questiontext');
    $ilDB->renameTableColumn("svy_question", "questiontext_tmp", "questiontext");
?>
<#2861>
<?php
    $ilDB->modifyTableColumn('cp_resource', 'href', array("type" => "text", "length" => 4000, "notnull" => false, "default" => null));
?>
<#2862>
<?php
    $ilDB->modifyTableColumn('cp_resource', 'base', array("type" => "text", "length" => 4000, "notnull" => false, "default" => null));
?>
<#2863>
<?php
    $ilDB->modifyTableColumn('cp_file', 'href', array("type" => "text", "length" => 4000, "notnull" => false, "default" => null));
?>
<#2864>
<?php
$fields = array(
    'table_id' => array(
        'type' => 'text',
        'length' => 30,
        'notnull' => true
    ),
    'user_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'property' => array(
        'type' => 'text',
        'length' => 20,
        'notnull' => true
    ),
    'value' => array(
        'type' => 'text',
        'length' => 100,
        'notnull' => true
    )
);
$ilDB->createTable('table_properties', $fields);
$ilDB->addPrimaryKey('table_properties', array('table_id', 'user_id', 'property'));

?>
<#2865>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#2866>
<?php
    if ($ilDB->getDBType() == 'mysql') {
        $query = "UPDATE catch_write_events SET ts = null WHERE ts = '0000-00-00 00:00:00'";
        $ilDB->query($query);
    }
?>

<#2867>
<?php
    if ($ilDB->getDBType() == 'mysql') {
        $query = "UPDATE usr_data SET approve_date = null WHERE approve_date = '0000-00-00 00:00:00'";
        $ilDB->query($query);
    }
?>
<#2868>
<?php
    if ($ilDB->getDBType() == 'mysql') {
        $query = "UPDATE usr_data SET agree_date = null WHERE agree_date = '0000-00-00 00:00:00'";
        $ilDB->query($query);
    }
?>
<#2869>
<?php
    $ilDB->modifyTableColumn(
    'usr_data',
    'reg_hash',
    array('type' => 'text', 'length' => 32, 'notnull' => false, 'fixed' => true)
);
?>
<#2870>
<?php
    $setting = new ilSetting();
    $setting->set("disable_contacts", "0");
    $setting->set("disable_contacts_require_mail", "1");
?>
<#2871>
<?php
    $setting = new ilSetting();
    $setting->set("loginname_change_blocking_time", 3600);
?>
<#2872>
<?php
$fields = array(
    'pc_type' => array(
        'type' => 'text',
        'length' => 30,
        'notnull' => true
    ),
    'pc_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'usage_type' => array(
        'type' => 'text',
        'length' => 30,
        'notnull' => true
    ),
    'usage_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'usage_hist_nr' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    )
);
$ilDB->createTable('page_pc_usage', $fields);
$ilDB->addPrimaryKey('page_pc_usage', array('pc_type', 'pc_id', 'usage_type', 'usage_id', 'usage_hist_nr'));

?>
<#2873>
<?php
$ilDB->addTableColumn("il_news_item", "start_date", array(
    "type" => "timestamp",
    "notnull" => false,
    "default" => null
));
$ilDB->addTableColumn("il_news_item", "end_date", array(
    "type" => "timestamp",
    "notnull" => false,
    "default" => null
));
?>

<#2874>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#2875>
<?php
    $ilDB->modifyTableColumn("cp_item", "datafromlms", array("type" => "text", "notnull" => false, "default" => null, "length" => 4000));
?>
<#2876>
<?php
    $all_types = array('chat','crs', 'dbk','exc','file',
        'fold','frm','glo','grp','htlm','icrs','lm',
        'mcst','sahs','svy','tst','webr','qpl','mep','spl','feed','crsr','wiki','rcrs');
    
    $set = $ilDB->query(
        "SELECT * FROM object_data " .
        " WHERE type = " . $ilDB->quote("typ", "text") .
        " AND title = " . $ilDB->quote("root", "text")
    );
    if ($rec = $ilDB->fetchAssoc($set)) {
        $root_type_id = $rec["obj_id"];
    
        foreach ($all_types as $t) {
            $set = $ilDB->query("SELECT * FROM rbac_operations WHERE operation = " .
                $ilDB->quote("create_" . $t));
            if ($rec = $ilDB->fetchAssoc($set)) {
                $ops_id = $rec["ops_id"];
        
                $q = "INSERT INTO rbac_ta " .
                    "(typ_id, ops_id) VALUES (" .
                    $ilDB->quote($root_type_id, "integer") . "," .
                    $ilDB->quote($ops_id, "integer") .
                    ")";
                $ilDB->manipulate($q);
                //echo "<br><br>$q";
            }
        }
    }
?>
<#2877>
<?php
    $set = $ilDB->query(
    "SELECT * FROM object_data " .
        " WHERE type = " . $ilDB->quote("typ", "text") .
        " AND title = " . $ilDB->quote("root", "text")
);
    if ($rec = $ilDB->fetchAssoc($set)) {
        $root_type_id = $rec["obj_id"];
    
        $set = $ilDB->query("SELECT * FROM rbac_operations WHERE operation = " .
            $ilDB->quote("create_fold"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            $ops_id = $rec["ops_id"];
    
            $q = "DELETE FROM rbac_ta " .
                " WHERE typ_id = " . $ilDB->quote($root_type_id, "integer") .
                " AND ops_id = " . $ilDB->quote($ops_id, "integer");
            $ilDB->manipulate($q);
        }
    }
?>

<#2878>
<?php
    $ilDB->modifyTableColumn('container_sorting', 'parent_type', array("type" => "text", "length" => 5, "notnull" => false, "default" => null));
?>

<#2879>
<?php
    $ilDB->addTableColumn("payment_news", "news_content_tmp", array(
        "type" => "clob",
        "notnull" => false,
        "default" => null
    ));
    
    $ilDB->manipulate('UPDATE payment_news SET news_content_tmp = news_content');
    
    $ilDB->dropTableColumn('payment_news', 'news_content');
    $ilDB->renameTableColumn("payment_news", "news_content_tmp", "news_content");
?>
<#2880>
<?php
    //
?>
<#2881>
<?php
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'alltagz', 'http://www.alltagz.de/bookmarks/?action=add&popup=1&address={LINK}&title={TITLE}&description=', 'alltagz.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Ask.com', 'http://myjeeves.ask.com/mysearch/BookmarkIt?v=1.2&t=webpages&url={LINK}&title={TITLE}', 'ask.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'BlinkBits', 'http://www.blinkbits.com/bookmarklets/save.php?v=1&title={TITLE}&source_url={LINK}&source_image_url=&rss_feed_url=&rss_feed_url=&rss2member=&body=', 'blinkbits.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'BlinkList', 'http://www.blinklist.com/index.php?Action=Blink/addblink.php&Description=&Tag=&Url={LINK}&Title={TITLE}', 'blinklist.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Blogmarks', 'http://blogmarks.net/my/new.php?mini=1&simple=1&url={LINK}&content=&public-tags=&title={TITLE}', 'blogmarks.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Blog Memes', 'http://www.blogmemes.net/post.php?url={LINK}&title={TITLE}', 'blogmemes.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Bluedot', 'http://bluedot.us/Authoring.aspx?u={LINK}&t={TITLE}', 'bluedot.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'BoniTrust', 'http://www.bonitrust.de/account/bookmark/?bookmark_url={LINK}', 'bonitrust.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'book.mark.hu', 'http://book.mark.hu/bookmarks.php/?action=add&address={LINK}&title={TITLE}', 'bookmarkhu.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'BUMPzee!', 'http://www.bumpzee.com/bump.php?u={LINK}', 'bumpzee.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'co.mments', 'http://co.mments.com/track?url={LINK}&title={TITLE}', 'co.mments.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Connotea', 'http://www.connotea.org/addpopup?continue=confirm&uri={LINK}&title={TITLE}', 'connotea.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Diigo', 'http://www.diigo.com/post?url={LINK}&title={TITLE}&tag=&comments=', 'diigo.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'DotNetKicks.com', 'http://www.dotnetkicks.com/kick/?url={LINK}&title={TITLE}', 'dotnetkicks.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'DZone', 'http://www.dzone.com/links/add.html?url={LINK}&title={TITLE}', 'dzone.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'edelight', 'http://www.edelight.de/geschenk/neu?purl={LINK}', 'edelight.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Facebook', 'http://www.facebook.com/share.php?u={LINK}&t={TITLE}', 'facebook.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Fark', 'http://cgi.fark.com/cgi/fark/submit.pl?new_url={LINK}&title={TITLE}', 'fark.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Favoriten.de', 'http://www.favoriten.de/url-hinzufuegen.html?bm_url={LINK}&bm_title={TITLE}', 'favoriten.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Feed Me Links', 'http://feedmelinks.com/login?bounceToPage=/categorize%3F&loggedIn=wasnt&from=toolbar&op=submit&url={LINK}&name={TITLE}', 'feedmelinks.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Fleck.com', 'http://extension.fleck.com/?v=b.0.804&url={LINK}', 'fleck.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Furl', 'http://www.furl.net/storeIt.jsp?u={LINK}&keywords=&t={TITLE}', 'furl.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Google', 'http://www.google.com/bookmarks/mark?op=add&hl=de&bkmk={LINK}&annotation=&labels=&title={TITLE}', 'google.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Gwar', 'http://www.gwar.pl/DodajGwar.html?u={LINK}', 'gwar.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Hao Hao Report', 'http://www.haohaoreport.com/submit.php?url={LINK}&title={TITLE}', 'haohao.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'HEMiDEMi', 'http://www.hemidemi.com/user_bookmark/new?title={TITLE}&url={LINK}', 'hemidemi.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'icio.de', 'http://www.icio.de/add.php?url={LINK}', 'icio.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'InterNetMedia', 'http://internetmedia.hu/submit.php?url={LINK}', 'im.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'IndianPad', 'http://www.indianpad.com/submit.php?url={LINK}', 'indianpad.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Infopirat', 'http://infopirat.com/node/add/userlink?edit[url]={LINK}&edit[title]={TITLE}', 'infopirat.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Kick.ie', 'http://kick.ie/submit/?url={LINK}&title={TITLE}', 'kickit.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'linkaGoGo', 'http://www.linkagogo.com/go/AddNoPopup?url={LINK}&title={TITLE}', 'linkagogo.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Linkarena', 'http://linkarena.com/bookmarks/addlink/?url={LINK}&title={TITLE}&desc=&tags=', 'linkarena.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Linksilo.de', 'http://www.linksilo.de/index.php?area=bookmarks&func=bookmark_new&addurl={LINK}&addtitle={TITLE}', 'linksilo.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Live', 'http://favorites.live.com/quickadd.aspx?marklet=1&mkt=en-us&top=0&url={LINK}&title={TITLE}', 'live.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Lufee', 'http://www.lufee.de/submit.php?link={LINK}&ititle=', 'lufee.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Ma.Gnolia', 'http://ma.gnolia.com/bookmarklet/add?url={LINK}&title={TITLE}&description=&tags=', 'magnolia.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'mi:nota', 'http://www.minota.de/account/bookmark/?bookmark_url={LINK}', 'minota.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'MyTagz.de', 'http://www.mytagz.de/bookmarks/?action=add&address={LINK}&title={TITLE}', 'mytagz.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Netscape', 'http://www.netscape.com/submit/?U={LINK}&T={TITLE}', 'netscape.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Netvouz', 'http://www.netvouz.com/action/submitBookmark?url={LINK}&description=&tags=&title={TITLE}&popup=yes', 'netvouz.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Newsvine', 'http://www.newsvine.com/_wine/save?popoff=1&u={LINK}&tags=&blurb={TITLE}', 'newsvine.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Oknotizie', 'http://oknotizie.com/post?url={LINK}&title={TITLE}', 'oknotizie.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Oneview', 'http://www.oneview.de/quickadd/neu/addBookmark.jsf?URL={LINK}&title={TITLE}', 'oneview.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'PlugIM', 'http://www.plugim.com/submit?url={LINK}&title={TITLE}', 'plugim.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'ppnow', 'http://www.ppnow.net/submit.php?url={LINK}', 'ppnow.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'RawSugar', 'http://www.rawsugar.com/tagger/?turl={LINK}&title={TITLE}', 'rawsugar.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Rec6', 'http://rec6.via6.com/link.php?url={LINK}&={TITLE}', 'rec6.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Reddit', 'http://reddit.com/submit?url={LINK}&title={TITLE}', 'reddit.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Rojo', 'http://www.rojo.com/add-subscription/?resource={LINK}', 'rojo.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Scoopeo', 'http://www.scoopeo.com/scoop/new?newurl={LINK}&title={TITLE}', 'scoopeo.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'SeekXL', 'http://social-bookmarking.seekxl.de/?add_url={LINK}&title={TITLE}', 'seekxl.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Segnalo', 'http://segnalo.alice.it/login.html.php?uri=/post.html.php%3Furl={LINK}&title={TITLE}', 'segnalo.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'SEOigg', 'http://www.seoigg.de/node/add/storylink?edit[url]={LINK}&edit[title]={TITLE}', 'seoigg.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Shadows', 'http://www.shadows.com/bookmark/saveLink.rails?page={LINK}&title={TITLE}', 'shadows.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Simpy', 'http://www.simpy.com/simpy/LinkAdd.do?title={TITLE}&tags=&note=&href={LINK}', 'simpy.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Slashdot', 'http://slashdot.org/bookmark.pl?url={LINK}&title={TITLE}', 'slashdot.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'SocialDust', 'http://www.socialdust.com/blogaggregator/addblog.php?urlpost={LINK}&title={TITLE}', 'socialdust.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Socializer', 'http://ekstreme.com/socializer/?url={LINK}&title={TITLE}', 'socializer.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'SOFTigg', 'http://www.softigg.de/artikel/schreiben/news?url={LINK}&title={TITLE}', 'softigg16.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Sphere', 'http://www.sphere.com/search?q=sphereit:{LINK}&title={TITLE}', 'sphere.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Spurl', 'http://www.spurl.net/spurl.php?v=3&tags=&title={TITLE}&url={LINK}', 'spurl.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Squidoo', 'http://www.squidoo.com/lensmaster/bookmark?{LINK}&title={TITLE}', 'squidoo.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Tailrank', 'http://tailrank.com/share/?text=&link_href={LINK}&title={TITLE}', 'tailrank.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Technorati', 'http://technorati.com/faves/seoportal?add={LINK}&tag=', 'technorati.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'ThisNext', 'http://www.thisnext.com/pick/new/submit/sociable/?url={LINK}&name={TITLE}', 'thisnext.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'travel.vote-for.it', 'http://travel.vote-for.it/node/add/weblink?edit[links_weblink_url]={LINK}&edit[title]={TITLE}', 'voteforit.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Weblinkr', 'http://weblinkr.com/weblinks/?action=add&popup=1&address={LINK}&title={TITLE}', 'weblinkr.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Webnews', 'http://www.webnews.de/einstellen?url={LINK}&title={TITLE}', 'webnews.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Webride', 'http://webride.org/discuss/split.php?uri={LINK}&title={TITLE}', 'webride.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Wink', 'http://wink.com/_/tag?url={LINK}&doctitle={TITLE}', 'wink.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Wists', 'http://wists.com/r.php?r={LINK}&title={TITLE}', 'wists.png',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Wykop.pl', 'http://www.wykop.pl/dodaj?url={LINK}', 'wykop.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Yahoo', 'http://myweb2.search.yahoo.com/myresults/bookmarklet?t={TITLE}&d=&tag=&u={LINK}', 'yahoo.gif',0));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Yigg', 'http://yigg.de/neu?exturl={LINK}', 'yigg.png',0));
?>
<#2882>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#2883>
<?php
$fields = array(
    'obj_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'style_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    )
);
$ilDB->createTable('style_usage', $fields);
$ilDB->addPrimaryKey('style_usage', array('obj_id'));
?>
<#2884>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#2885>
<?php
if (!$ilDB->tableExists("payment_erp")) {
    $fields = array(
    'erp_id' => array(
      'type' => 'integer',
      'length' => 4,
      'notnull' => true
    ),
    'erp_short' => array(
      'type' => 'text',
      'length' => 12,
      'notnull' => true
    ),
    'name' => array(
      'type' => 'text',
      'length' => 32,
      'notnull' => true
    ),
    'description' => array(
      'type' => 'text',
      'length' => 2000
    ),
    'url' => array(
      'type' => 'text',
      'length' => 255
    )
  );
    $ilDB->createTable('payment_erp', $fields);
    $ilDB->addPrimaryKey('payment_erp', array('erp_id'));
    $ilDB->manipulateF(
        "INSERT INTO payment_erp (erp_id, erp_short, name, description, url) VALUES (%s, %s, %s, %s, %s)",
        array("integer", "text", "text", "text", "text"),
        array(0, 'none', 'none', 'No ERP system', '')
    );
    $ilDB->manipulateF(
        "INSERT INTO payment_erp (erp_id, erp_short, name, description, url) VALUES (%s, %s, %s, %s, %s)",
        array("integer", "text", "text", "text", "text"),
        array(1, "eco", "E-conomic", "E-conomic online accounting system. Available in Danish, Deutch, Spanish, Norwegian, Swedish and English.", "http://www.e-conomic.dk")
    );
}
?>
<#2886>
<?php
if (!$ilDB->tableExists("payment_erps")) {
    $fields = array(
    'erps_id' => array(
      'type' => 'integer',
      'length' => 4
    ),
    'erp_id' => array(
      'type' => 'integer',
      'length' => 4
    ),
    'active' => array(
      'type' => 'integer',
      'length' => 4
    ),
    'settings' => array(
      'type' => 'text',
      'length' => 4000,
      'notnull' => true
    )
  );
    $ilDB->createTable('payment_erps', $fields);
    $ilDB->addPrimaryKey('payment_erps', array('erps_id'));
    $ilDB->manipulateF(
        "INSERT INTO payment_erps (erps_id, erp_id, active, settings) VALUES (%s, %s, %s, %s)",
        array("integer", "integer", "integer", "text"),
        array(0, 0, 1, ' ')
    );
}
?>
<#2887>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#2888>
<?php>
	$ilDB->addTableColumn("lng_data", "remarks", array("type" => "text", "length" => 250, "notnull" => false, "default" => null));
?>
<#2889>
<?php
  $ilDB->manipulateF(
    "INSERT INTO payment_erps (erps_id, erp_id, active, settings) VALUES (%s, %s, %s, %s)",
    array("integer", "integer", "integer", "text"),
    array(1,0,0,' ')
);
?>
<#2890>
<?php
  $ilDB->modifyTableColumn(
    "payment_erps",
    "settings",
    array("type" => "text", "notnull" => false, "default" => null, "length" => 4000)
);
?>
<#2891>
<?php
    $ilDB->manipulate('DELETE FROM bookmark_social_bm');
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Facebook', 'http://www.facebook.com/share.php?u={LINK}&t={TITLE}', 'templates/default/images/socialbookmarks/facebook.png',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Google', 'http://www.google.com/bookmarks/mark?op=add&hl=de&bkmk={LINK}&annotation=&labels=&title={TITLE}', 'templates/default/images/socialbookmarks/google.gif',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Linkarena', 'http://linkarena.com/bookmarks/addlink/?url={LINK}&title={TITLE}&desc=&tags=', 'templates/default/images/socialbookmarks/linkarena.gif',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Live', 'http://favorites.live.com/quickadd.aspx?marklet=1&mkt=en-us&top=0&url={LINK}&title={TITLE}', 'templates/default/images/socialbookmarks/live.png',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Newsvine', 'http://www.newsvine.com/_wine/save?popoff=1&u={LINK}&tags=&blurb={TITLE}', 'templates/default/images/socialbookmarks/newsvine.gif',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Reddit', 'http://reddit.com/submit?url={LINK}&title={TITLE}', 'templates/default/images/socialbookmarks/reddit.gif',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Yahoo', 'http://myweb2.search.yahoo.com/myresults/bookmarklet?t={TITLE}&d=&tag=&u={LINK}', 'templates/default/images/socialbookmarks/yahoo.gif',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Yigg', 'http://yigg.de/neu?exturl={LINK}', 'templates/default/images/socialbookmarks/yigg.png',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'studiVZ meinVZ schülerVZ', 'http://www.studivz.net/Link/Selection/Url/?u={LINK}&desc={TITLE}', 'templates/default/images/socialbookmarks/studiVZ.gif',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'MySpace', 'http://www.myspace.com/index.cfm?fuseaction=postto&u={LINK}&t={TITLE}', 'templates/default/images/socialbookmarks/myspace.gif',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Del.icio.us', 'http://del.icio.us/post?url={LINK}&title={TITLE}', 'templates/default/images/socialbookmarks/delicious.gif',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Digg', 'http://digg.com/submit?phase=2&url={LINK}&title={TITLE}', 'templates/default/images/socialbookmarks/digg.gif',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Folkd', 'http://www.folkd.com/submit/{LINK}', 'templates/default/images/socialbookmarks/folkd.gif',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'Mister Wong', 'http://www.mister-wong.de/index.php?action=addurl&bm_url={LINK}&title={TITLE}', 'templates/default/images/socialbookmarks/wong.jpg',1));
    $ilDB->manipulateF("INSERT INTO bookmark_social_bm (sbm_id, sbm_title, sbm_link, sbm_icon, sbm_active) VALUES (%s, %s, %s, %s, %s)", array("integer", "text", "text", "text", "integer"), array($ilDB->nextId("bookmark_social_bm"), 'StumbleUpon', 'http://www.stumbleupon.com/submit?url={LINK}&title={TITLE}', 'templates/default/images/socialbookmarks/stumbleupon.jpg',1));

?>
<#2892>
<?php
  $ilDB->modifyTableColumn(
    "style_parameter",
    "value",
    array("type" => "text", "notnull" => false, "default" => null, "length" => 200, "fixed" => false)
);
?>
<#2893>
<?php
  $ilDB->dropTable('payment_erps');
?>
<#2894>
<?php
if (!$ilDB->tableExists("payment_erps")) {
    $fields = array(
    'erp_id' => array('type' => 'integer', 'length' => 4),
    'erps_id' => array('type' => 'integer', 'length' => 4),
    'active' => array('type' => 'integer', 'length' => 4),
    'settings' => array('type' => 'text', 'length' => 4000, 'notnull' => false, 'fixed' => false, "default" => null)
  );
    $ilDB->createTable('payment_erps', $fields);
    $ilDB->addPrimaryKey('payment_erps', array('erp_id', 'erps_id'));
}
?>
<#2895>
<?php
  $ilDB->manipulateF(
    "INSERT INTO payment_erps (erp_id, erps_id, active, settings) VALUES (%s, %s, %s, %s)",
    array("integer", "integer", "integer", "text"),
    array(0, 0, 1, null)
);
?>
<#2896>
<?php
  $ilDB->manipulateF(
    "INSERT INTO payment_erps (erp_id, erps_id, active, settings) VALUES (%s, %s, %s, %s)",
    array("integer", "integer", "integer", "text"),
    array(1, 0, 0, null)
);
?>
<#2897>
<?php
  /* keep empty db step, because it was in svn for some revisions */
?>

<#2898>
<?php
    $ilDB->addIndex('cal_categories', array('obj_id'), 'i2');
?>
<#2899>
<?php
  $ilDB->addTableColumn("payment_erp", "save_copy", array("type" => "integer", "length" => 4, "default" => 0));
?>
<#2900>
<?php
  $ilDB->addTableColumn("payment_erp", "use_ean", array("type" => "integer", "length" => 4, "default" => 0));
?>
<#2901>
<?php
  $ilDB->manipulate("UPDATE payment_erp SET save_copy=1, use_ean=1 WHERE erp_id=1");
?>

<#2902>
<?php
    $ilDB->insert("settings", array(
        "module" => array("text", 'common'),
        "keyword" => array("text", 'mail_subject_prefix'),
        "value" => array("clob", '[ILIAS]')));
?>
<#2903>
<?php
// remove copy operation from icrs object
// first get the type object id
$query = 'SELECT obj_id FROM object_data WHERE type = %s AND title = %s';
$res = $ilDB->queryF($query, array('text', 'text'), array('typ', 'icrs'));
$rowType = $ilDB->fetchAssoc($res);

// afterwards determine the operation id
$query = 'SELECT ops_id FROM rbac_operations WHERE operation = %s';
$res = $ilDB->queryF($query, array('text'), array('copy'));
$rowOperation = $ilDB->fetchAssoc($res);

if ((int) $rowType['obj_id'] && (int) $rowOperation['ops_id']) {
    // delete data
    $query = 'DELETE FROM rbac_ta WHERE typ_id = %s AND ops_id = %s';
    $ilDB->manipulateF($query, array('integer', 'integer'), array((int) $rowType['obj_id'], (int) $rowOperation['ops_id']));
    
    $query = 'DELETE FROM rbac_templates WHERE type = %s AND ops_id = %s';
    $ilDB->manipulateF($query, array('text', 'integer'), array('icrs', (int) $rowOperation['ops_id']));
}
?>
<#2904>
<?php
    $set = $ilDB->query('SELECT * FROM payment_settings');
    if ($rec = $ilDB->fetchAssoc($set)) {
        $save_user_adr = $rec["save_customer_address_enabled"];
    }
    if ($save_user_adr == '1') {
        $ilDB->insert("settings", array(
        "module" => array("text", 'common'),
        "keyword" => array("text", 'save_user_adr_bill'),
        "value" => array("clob", '1')));
    }
    
    $ilDB->dropTableColumn('payment_settings', 'save_customer_address_enabled');

?>
<#2905>
<?php
    $ilDB->addTableColumn("payment_statistic", "vat_rate", array(
        "type" => "float",
        "notnull" => true,
        "default" => 0
    ));
    
    $ilDB->addTableColumn("payment_statistic", "vat_unit", array(
        "type" => "float",
        "notnull" => true,
        "default" => 0
    ));

    $ilDB->addTableColumn("payment_statistic", "object_title", array(
        "type" => "text",
        "notnull" => false,
        "length" => 128,
        "fixed" => false
    ));
?>
<#2906>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#2907>
<?php
    $ilDB->addTableColumn("frm_notification", "admin_force_noti", array(
        "type" => "integer",
        "length" => 1,
        "notnull" => true,
        "default" => 0
    ));
    
    $ilDB->addTableColumn("frm_notification", "user_toggle_noti", array(
        "type" => "integer",
        "length" => 1,
        "notnull" => true,
        "default" => 0
    ));

    $ilDB->addTableColumn("frm_notification", "user_id_noti", array(
        "type" => "integer",
        "length" => 4,
        "notnull" => false,
        "default" => null
    ));
?>
<#2908>
<?php
    $ilDB->addTableColumn("frm_settings", "admin_force_noti", array(
        "type" => "integer",
        "length" => 1,
        "notnull" => true,
        "default" => 0
    ));
    
    $ilDB->addTableColumn("frm_settings", "user_toggle_noti", array(
        "type" => "integer",
        "length" => 1,
        "notnull" => true,
        "default" => 0
    ));
?>

<#2909>
<?php
if (!$ilDB->tableExists("cal_auth_token")) {
    $ilDB->createTable(
        "cal_auth_token",
        array(
            "user_id" => array(
                "type" => "integer", "length" => 4, "notnull" => true
            ),
            "hash" => array(
                "type" => "text", "length" => 32, "fixed" => false, "notnull" => true
            ),
            "selection" => array(
                "type" => "integer", "length" => 4, "notnull" => true
            ),
            "calendar" => array(
                "type" => "integer", "length" => 4, "notnull" => true
            )
        )
    );
    $ilDB->addPrimaryKey("cal_auth_token", array("user_id", "hash"));
    $ilDB->addIndex('cal_auth_token', array('hash'), 'i1');
    $ilDB->addIndex('cal_auth_token', array('user_id'), 'i2');
}
?>
<#2910>
<?php
    $ilDB->addIndex('mep_tree', array('child'), 'ch');
    $ilDB->addIndex('mep_tree', array('parent'), 'pa');
    $ilDB->addIndex('mep_tree', array('mep_id'), 'me');
?>
<#2911>
<?php
    $ilDB->addIndex('rbac_templates', array('rol_id', 'parent'), 'rp');
?>
<#2912>
<?php
    $ilDB->addIndex('desktop_item', array('item_id'), 'it');
?>
<#2913>
<?php
    $ilDB->addIndex('crs_items', array('obj_id'), 'ob');
?>
<#2914>
<?php
  $ilDB->modifyTableColumn("cp_mapinfo", "targetobjectiveid", array("type" => "text",  "length" => 255, "notnull" => false, "default" => null));
?>

<#2915>
<?php
    $ilDB->addTableColumn(
    'cal_auth_token',
    'ical',
    array(
            'type' => 'clob',
            'notnull' => false
        )
);
?>
<#2916>
<?php
    $ilDB->addTableColumn(
    'cal_auth_token',
    'c_time',
    array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
);
?>
<#2917>
<?php
    $ilDB->addTableColumn("tst_solutions", "value1_tmp", array(
        "type" => "clob",
        "notnull" => false,
        "default" => null
    ));
        $ilDB->manipulate('UPDATE tst_solutions SET value1_tmp = value1');
    $ilDB->dropTableColumn('tst_solutions', 'value1');
    $ilDB->renameTableColumn("tst_solutions", "value1_tmp", "value1");
?>
<#2918>
<?php
$ilDB->addTableColumn("tst_solutions", "value2_tmp", array(
    "type" => "clob",
    "notnull" => false,
    "default" => null
));
$ilDB->manipulate('UPDATE tst_solutions SET value2_tmp = value2');
$ilDB->dropTableColumn('tst_solutions', 'value2');
$ilDB->renameTableColumn("tst_solutions", "value2_tmp", "value2");
?>
<#2919>
<?php
$ilDB->modifyTableColumn("addressbook", "login", array(
    "type" => "text",
    "length" => 80,
    "notnull" => false,
    "default" => null
));
?>
<#2920>
<?php
$ilDB->modifyTableColumn("addressbook", "email", array(
    "type" => "text",
    "length" => 80,
    "notnull" => false,
    "default" => null
));
?>

<#2921>
<?php
if (!$ilDB->tableExists("tst_result_cache")) {
    $ilDB->createTable(
        "tst_result_cache",
        array(
            "active_fi" => array(
                "type" => "integer", "length" => 4, "notnull" => true
            ),
            "pass" => array(
                "type" => "integer", "length" => 4, "notnull" => true
            ),
            "max_points" => array(
                "type" => "float", "notnull" => true, "default" => 0
            ),
            "reached_points" => array(
                "type" => "float", "notnull" => true, "default" => 0
            ),
            "mark_short" => array(
                "type" => "text", "length" => 256, "fixed" => false, "notnull" => true
            ),
            "mark_official" => array(
                "type" => "text", "length" => 256, "fixed" => false, "notnull" => true
            ),
            "passed" => array(
                "type" => "integer", "length" => 4, "notnull" => true
            ),
            "failed" => array(
                "type" => "integer", "length" => 4, "notnull" => true
            ),
            "tstamp" => array(
                "type" => "integer", "length" => 4, "notnull" => true, "default" => 0
            )
        )
    );
    $ilDB->addIndex('tst_result_cache', array('active_fi'), 'i1');
}
?>

<#2922>
<?php
$ilDB->createTable(
    'ut_lp_user_status',
    array(
        'obj_id' =>
            array(
                'type' => 'integer',
                'length' => 4,
                'default' => 0,
                'notnull' => true
            ),
        'usr_id' =>
            array(
                'type' => 'integer',
                'length' => 4,
                'default' => 0,
                'notnull' => true
            ),
        'status' =>
            array(
                'type' => 'integer',
                'length' => 1,
                'default' => 0,
                'notnull' => true
            ),
        'additional_info' =>
            array(
                'type' => 'text',
                'length' => 4000,
                'notnull' => false
            )
        ),
    true
);

$ilDB->addPrimaryKey('ut_lp_user_status', array('obj_id','usr_id'));
$ilDB->addIndex('ut_lp_user_status', array('obj_id'), 'i1');
$ilDB->addIndex('ut_lp_user_status', array('usr_id'), 'i2');
?>

<#2923>
<?php
$ilDB->modifyTableColumn("file_data", "file_name", array("type" => "text",  "length" => 250, "notnull" => false, "default" => null, "fixed" => true));
?>

<#2924>
<?php
$ilDB->modifyTableColumn("file_data", "file_type", array("type" => "text",  "length" => 250, "notnull" => false, "default" => null, "fixed" => true));
?>

<#2925>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#2926>
<?php
    $ilDB->addIndex('scorm_tracking', array('obj_id','sco_id','lvalue'), 'i2');
?>

<#2927>
<?php
    $ilDB->addIndex('cmi_gobjective', array('scope_id','objective_id'), 'i2');
?>

<#2928>
<?php
    $ilDB->addIndex('cp_node', array('slm_id'), 'i3');
?>

<#2929>
<?php
    $ilDB->addIndex('lm_data', array('import_id'), 'im');
?>

<#2930>
<?php
    $ilDB->addIndex('int_link', array('target_type', 'target_id', 'target_inst'), 'ta');
?>

<#2931>
<?php
    $ilDB->addIndex('int_link', array('source_type', 'source_id'), 'so');
?>

<#2932>
<?php
    $ilDB->addIndex('map_area', array('link_type', 'target'), 'lt');
?>

<#2933>
<?php
    $ilDB->addIndex('mob_usage', array('id'), 'mi');
?>

<#2934>
<?php
    $ilDB->addIndex('mep_item', array('foreign_id', 'type'), 'ft');
?>

<#2935>
<?php
    $ilDB->addIndex('il_news_item', array('mob_id'), 'mo');
?>

<#2936>
<?php
    $ilDB->addIndex('personal_clipboard', array('item_id', 'type'), 'it');
?>

<#2937>
<?php
    $ilDB->addIndex('write_event', array('obj_id'), 'i2');
?>
<#2938>
<?php
global $ilLog;

include_once('Services/Migration/DBUpdate_2498/classes/class.ilUpdateUtilsMailMigration.php');
include_once('Services/Migration/DBUpdate_2498/classes/class.ilFileSystemStorageMailMigration.php');
include_once('Services/Migration/DBUpdate_2498/classes/class.ilFSStorageMailMailMigration.php');

// Fetch all messages with attachment(s)
$res = $ilDB->query("SELECT * FROM mail_attachment WHERE " . str_replace('LIKE', 'NOT LIKE', $ilDB->like('path', 'text', '%\_%\_%')));
$ilLog->write('DB Migration 2938: Fetched all mails with a deprecated path value ...');
// iterate over result
$counter = 0;
while ($record = $ilDB->fetchObject($res)) {
    $path_parts = explode('_', $record->path);
    if (count($path_parts) == 2) {
        $path_parts[0] = trim($path_parts[0]);
        $path_parts[1] = trim($path_parts[1]);
        
        ++$counter;
        
        // Create new storage folder (if it does not exist yet)
        $oFSStorageMail = new ilFSStorageMailMailMigration($path_parts[1], $path_parts[0]);
        if (file_exists($oFSStorageMail->getAbsolutePath()) && is_dir($oFSStorageMail->getAbsolutePath())) {
            $new_rel_path = $oFSStorageMail->getRelativePathExMailDirectory();
            if (strlen($new_rel_path)) {
                // Modify existing attachment assigment
                $ilDB->manipulateF(
                    "UPDATE mail_attachment SET path = %s WHERE mail_id = %s",
                    array('text', 'integer'),
                    array($new_rel_path, $record->mail_id)
                );
                $ilLog->write("DB Migration 2938: Updated database table 'mail_attachment' -> Set path to '" . $new_rel_path . "' for mail '" . $record->mail_id . "' ...");
            } else {
                $ilLog->write('DB Migration 2938: Migration not possible -> Could not determine relative path (mail \'' . $record->mail_id . '\') ...');
                continue;
            }
        } else {
            $ilLog->write('DB Migration 2938: Migration not possible -> Target folder \'' . $oFSStorageMail->getAbsolutePath() . '\' does not exist or is not a directory (mail \'' . $record->mail_id . '\') ...');
            continue;
        }
    }
}
?>
<#2939>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2940>
<?php
if (!$ilDB->tableColumnExists('svy_qst_mc', 'use_other_answer')) {
    $ilDB->addTableColumn("svy_qst_mc", "use_other_answer", array("type" => "integer", "length" => 2, "notnull" => false));
    $ilDB->addTableColumn("svy_qst_mc", "other_answer_label", array("type" => "text", "length" => 255, "notnull" => false, "default" => null));
}
?>
<#2941>
<?php
if (!$ilDB->tableColumnExists('svy_qst_sc', 'use_other_answer')) {
    $ilDB->addTableColumn("svy_qst_sc", "use_other_answer", array("type" => "integer", "length" => 2, "notnull" => false));
    $ilDB->addTableColumn("svy_qst_sc", "other_answer_label", array("type" => "text", "length" => 255, "notnull" => false, "default" => null));
}
?>
<#2942>
<?php
if (!$ilDB->tableColumnExists('svy_category', 'scale')) {
    $ilDB->addTableColumn("svy_category", "scale", array("type" => "integer", "length" => 4, "notnull" => false, "default" => null));
}
?>
<#2943>
<?php
if (!$ilDB->tableColumnExists('svy_category', 'other')) {
    $ilDB->addTableColumn("svy_category", "other", array("type" => "integer", "length" => 2, "notnull" => true, "default" => 0));
}
?>
<#2944>
<?php
if ($ilDB->tableColumnExists('svy_qst_mc', 'other_answer_label')) {
    $ilDB->dropTableColumn('svy_qst_mc', 'other_answer_label');
}
?>
<#2945>
<?php
if ($ilDB->tableColumnExists('svy_qst_sc', 'other_answer_label')) {
    $ilDB->dropTableColumn('svy_qst_sc', 'other_answer_label');
}
?>
<#2946>
<?php
    $ilDB->addIndex('svy_category', array('other'), 'i2');
?>
<#2947>
<?php

    $feed_set = new ilSetting('feed');
    $pxy_host = $feed_set->get('proxy');
    $pxy_port = $feed_set->get('proxy_port');
    
    $setting = new ilSetting();
    if (strlen($pxy_host) && strlen($pxy_port)) {
        $setting->set('proxy_host', $pxy_host);
        $setting->set('proxy_port', $pxy_port);
        $setting->set('proxy_status', 1);
    } else {
        $setting->set('proxy_host', '');
        $setting->set('proxy_port', '');
        $setting->set('proxy_status', 0);
    }
    
    $feed_set->delete('proxy');
    $feed_set->delete('proxy_port');
?>
<#2948>
<?php
        

                // FILE ENDS HERE, DO NOT ADD ANY ADDITIONAL STEPS
                //
                //         USE dbupdate_03.php INSTEAD


?>