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
if (!$ilDB->tableColumnExists("survey_questionblock", "show_questiontext"))
{
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'ps', 'Privacy security settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('ps', '__PrivacySecurity', 'Privacy and Security', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'ps'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// new permission
$query = "INSERT INTO rbac_operations SET operation = 'export_member_data', ".
	"description = 'Export member data', ".
	"class = 'object'";

$res = $ilDB->query($query);
$new_ops_id = $ilDB->getLastInsertId();


// add rbac operations to assessment folder
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
$this->db->query($query);

$query = "INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('".$typ_id."','".$new_ops_id."')";
$this->db->query($query);
?>
<#878>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#879>
<?php
// add layout attribute for matrix questions
$attribute_visibility = FALSE;
$query = "SHOW COLUMNS FROM survey_question_matrix";
$res = $ilDB->query($query);
if ($res->numRows())
{
	while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		if (strcmp($data["Field"], "layout") == 0)
		{
			$attribute_visibility = TRUE;
		}
	}
}
if ($attribute_visibility == FALSE)
{
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$file_ids[] = $row->obj_id;
}

foreach($file_ids as $file_id)
{
	// Check if done
	$query = "SELECT * FROM tmp_migration WHERE obj_id = ".$file_id." AND passed = 1";
	$res = $ilDB->query($query);
	if($res->numRows())
	{
		continue;
	}

	if(!@file_exists(ilUpdateUtils::getDataDir().'/files/file_'.$file_id) or !@is_dir(ilUpdateUtils::getDataDir().'/files/file_'.$file_id))
	{
		$ilLog->write('DB Migration 905: Failed: No data found for file_'.$file_id);
		continue;
	}

	// Rename
	$fss = new ilFSStorageFile($file_id);
	$fss->create();


	if($fss->rename(ilUpdateUtils::getDataDir().'/files/file_'.$file_id,$fss->getAbsolutePath()))
	{
		$ilLog->write('DB Migration 905: Success renaming file_'.$file_id);
	}
	else
	{
		$ilLog->write('DB Migration 905: Failed renaming '.ilUpdateUtils::getDataDir().'/files/file_'.$file_id.' -> '.$fss->getAbsolutePath());
		continue;
	}

	// Save success
	$query = "REPLACE INTO tmp_migration SET obj_id = '".$file_id."',passed = '1'";
	$ilDB->query($query);

	// Update file size
	$size = ilObjFileAccess::_lookupFileSize($file_id);
	$query = "UPDATE file_data SET file_size = '".$size."' ".
		"WHERE file_id = ".$file_id;
	$ilDB->query($query);
	$ilLog->write('DB Migration 905: File size is '.$size.' Bytes');
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'nwss', 'News settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('nwss', '__NewsSettings', 'News Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'nwss'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'feed', 'External Feed', -1, now(), now())";
$this->db->query($query);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'feed'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations for feed object
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$event_ids[] = $row->event_ids;
}

foreach($event_ids as $event_id)
{
	// Check if done
	$query = "SELECT * FROM tmp_migration WHERE obj_id = ".$event_id." AND passed = 1";
	$res = $ilDB->query($query);
	if($res->numRows())
	{
		continue;
	}

	if(!@file_exists(ilUpdateUtils::getDataDir().'/events/event_'.$event_id))
	{
		$ilLog->write('DB Migration 905: Failed: No data found for event id '.$event_id);
		continue;
	}

	// Rename
	$fss = new ilFSStorageEvent($event_id);
	$fss->create();


	if($fss->rename(ilUpdateUtils::getDataDir().'/events/event_'.$event_id,$fss->getAbsolutePath()))
	{
		$ilLog->write('DB Migration 905: Success renaming event_'.$event_id);
	}
	else
	{
		$ilLog->write('DB Migration 905: Failed renaming '.ilUpdateUtils::getDataDir().'/events/event_'.$event_id.' -> '.$fss->getAbsolutePath());
		continue;
	}

	// Save success
	$query = "REPLACE INTO tmp_migration SET obj_id = '".$event_id."',passed = '1'";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$archive_ids[$row->archive_ids]['id'] = $row->archive_ids;
	$archive_ids[$row->archive_ids]['name'] = $row->archive_name;
	$archive_ids[$row->archive_ids]['course_id'] = $row->course_id;

}

foreach($archive_ids as $archive_id => $data)
{
	// Check if done
	$query = "SELECT * FROM tmp_migration WHERE obj_id = ".$archive_id." AND passed = 1";
	$res = $ilDB->query($query);
	if($res->numRows())
	{
		continue;
	}

	if(!@file_exists(ilUpdateUtils::getDataDir().'/course/'.$data['name']))
	{
		$ilLog->write('DB Migration 930: Failed: No data found for archive id '.$data['name']);
		continue;
	}

	// Rename
	$fss = new ilFSStorageCourse($data['course_id']);
	$fss->create();
	$fss->initArchiveDirectory();


	if($fss->rename(ilUpdateUtils::getDataDir().'/course/'.$data['name'],$fss->getArchiveDirectory().'/'.$data['name']))
	{
		$ilLog->write('DB Migration 905: Success renaming archive '.$data['name']);
	}
	if($fss->rename(ilUpdateUtils::getDataDir().'/course/'.$data['name'].'.zip',$fss->getArchiveDirectory().'/'.$data['name'].'.zip'))
	{
		$ilLog->write('DB Migration 905: Success renaming archive '.$data['name'].'.zip');
	}
	else
	{
		$ilLog->write('DB Migration 905: Failed renaming '.ilUpdateUtils::getDataDir().'/course/'.$data['name'] .'-> '.
			 $fss->getArchiveDirectory().'/'.$data['name']);
		continue;
	}

	// Save success
	$query = "REPLACE INTO tmp_migration SET obj_id = '".$archive_id."',passed = '1'";
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'mcst', 'Media Cast', -1, now(), now())";
$this->db->query($query);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'mcst'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations for feed object
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
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
if(!$res->numRows())
{
	// Only update if no setting is available
	# Fetch old settings from settings_table
	$query = "SELECT * FROM settings WHERE keyword LIKE('ldap_%')";
	$res = $ilDB->query($query);
	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$ldap_old[$row->keyword] = $row->value;
	}

	if($ldap_old['ldap_server'])
	{
		$ldap_new['name'] = 'Default Server';
		$ldap_new['url'] = ('ldap://'.$ldap_old['ldap_server']);
		if($ldap_old['ldap_port'])
		{
			$ldap_new['url'] .= (':'.$ldap_old['ldap_port']);
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
		$ldap_new['filter'] = ('(objectclass='.$ldap_old['ldap_objectclass'].')');

		$query = "INSERT INTO  ldap_server_settings SET ".
			"active = '".$ldap_new['active']."', ".
			"name = '".$ldap_new['name']."', ".
			"url = ".$this->db->quote($ldap_new['url']).", ".
			"version = ".$this->db->quote($ldap_new['version']).", ".
			"base_dn = '".$ldap_new['basedn']."', ".
			"referrals = '".$ldap_new['referrals']."', ".
			"tls = '".$ldap_new['tls']."', ".
			"bind_type = '".$ldap_new['bind_type']."', ".
			"bind_user = '".$ldap_new['bind_user']."', ".
			"bind_pass = '".$ldap_new['bind_pass']."', ".
			"search_base = '".$ldap_new['search_base']."', ".
			"user_scope = '".$ldap_new['user_scope']."', ".
			"user_attribute = '".$ldap_new['user_attribute']."', ".
			"filter = '".$ldap_new['filter']."' ";
			"group_dn = '', ".
			"group_scope = '', ".
			"group_filter = '', ".
			"group_member = '', ".
			"group_memberisdn = '', ".
			"group_name = '', ".
			"group_attribute = '', ".
			"sync_on_login = '0', ".
			"sync_per_cron = '0', ".
			"role_sync_active = '0', ".
			"role_bind_dn = '', ".
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

if(@is_dir($dir = ilUpdateUtils::getDataDir().'/course'))
{
	$dp = @opendir($dir);
	while(($filedir = readdir($dp)) !== false)
	{
		if($filedir == '.' or $filedir == '..')
		{
			continue;
		}
		if(preg_match('/^course_file([0-9]+)$/',$filedir,$matches))
		{
			$ilLog->write('DB Migration 944: Found file: '.$filedir.' with course_id: '.$matches[1]);

			$fss_course = new ilFSStorageCourse($matches[1]);
			$fss_course->initInfoDirectory();

			if(@is_dir($info_dir = ilUpdateUtils::getDataDir().'/course/'.$filedir))
			{
				$dp2 = @opendir($info_dir);
				while(($file = readdir($dp2)) !== false)
				{
					if($file == '.' or $file == '..')
					{
						continue;
					}
					$fss_course->rename($from = ilUpdateUtils::getDataDir().'/course/'.$filedir.'/'.$file,
						$to = $fss_course->getInfoDirectory().'/'.$file);

					$ilLog->write('DB Migration 944: Renamed: '.$from.' to: '.$to);
				}

			}
		}

	}
}
?>
<#945>
<?php
// new permission copy
$query = "INSERT INTO rbac_operations SET operation = 'copy', ".
	"description = 'Copy Object', ".
	"class = 'general', ".
	"op_order = '115'";

$res = $ilDB->query($query);
?>
<#946>
<?php

// copy permission id
$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$ops_id = $row->ops_id;

$all_types = array('cat','chat','crs','dbk','exc','file','fold','frm','glo','grp','htlm','icrs','lm','mcst','sahs','svy','tst','webr');
foreach($all_types as $type)
{
	$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = '".$type."'";
	$res = $ilDB->query($query);
	$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

	$query = "INSERT INTO rbac_ta SET typ_id = '".$row->obj_id."', ops_id = '".$ops_id."'";
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
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$ops_id = $row->ops_id;

$all_types = array('cat','chat','crs','dbk','exc','file','fold','frm','glo','grp','htlm','icrs','lm','mcst','sahs','svy','tst','webr');
$query = "SELECT * FROM rbac_templates ".
	"WHERE type IN ('".implode("','",$all_types)."') ".
	"AND ops_id = 4 ".
	"ORDER BY rol_id,parent";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	// CHECK done
	$query = "SELECT * FROM tmp_migration ".
		"WHERE obj_id = '".$row->rol_id."' ".
		"AND parent = '".$row->parent."' ".
		"AND type = '".$row->type."'";
	$res_done = $ilDB->query($query);
	if($res_done->numRows())
	{
		continue;
	}
	// INSERT new permission
	$query = "INSERT INTO rbac_templates SET ".
		"rol_id = '".$row->rol_id."', ".
		"type = '".$row->type."', ".
		"ops_id = '".$ops_id."', ".
		"parent = '".$row->parent."'";
	$ilDB->query($query);

	// Set Passed
	$query = "INSERT INTO tmp_migration SET ".
		"obj_id = '".$row->rol_id."', ".
		"parent = '".$row->parent."', ".
		"type = '".$row->type."', ".
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
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$ops_id = (int) $row->ops_id;

$all_types = array('cat','chat','crs','dbk','exc','file','fold','frm','glo','grp','htlm','icrs','lm','mcst','sahs','svy','tst','webr');

// Get all objects
$query = "SELECT rol_id,ops_id,pa.ref_id AS ref_id FROM rbac_pa AS pa ".
	"JOIN object_reference AS obr ON pa.ref_id = obr.ref_id ".
	"JOIN object_data AS obd ON obr.obj_id = obd.obj_id ".
	"WHERE obd.type IN ('".implode("','",$all_types)."') ".
	"ORDER BY rol_id,pa.ref_id ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	// CHECK done
	$query = "SELECT * FROM tmp_migration ".
		"WHERE rol_id = '".$row->rol_id."' ".
		"AND ref_id = '".$row->ref_id."' ".
		"AND passed = '1'";
	$res_done = $ilDB->query($query);
	if($res_done->numRows())
	{
		continue;
	}
	$ops_ids = unserialize(stripslashes($row->ops_id));
	// write granted ?
	if(!in_array(4,$ops_ids))
	{
		continue;
	}
	// Grant permission
	$ops_ids[] = $ops_id;
	$query = "UPDATE rbac_pa SET ".
		"ops_id = '".addslashes(serialize($ops_ids))."' ".
		"WHERE rol_id = '".$row->rol_id."' ".
		"AND ref_id = '".$row->ref_id."'";
	$ilDB->query($query);

	// Set Passed
	$query = "INSERT INTO tmp_migration SET ".
		"rol_id = '".$row->rol_id."', ".
		"ref_id = '".$row->ref_id."', ".
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
$query = "INSERT INTO rbac_operations ".
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

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='crs'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='grp'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$ilDB->query($query);

?>
<#956>
UPDATE rbac_operations SET class='create' WHERE operation='create_feed';

<#957>
<?php
// add create operation for
$query = "INSERT INTO rbac_operations ".
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

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='crs'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='grp'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='fold'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'pdts', 'Personal desktop settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('pdts', '__PersonalDesktopSettings', 'Personal Desktop Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'pdts'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->Field == 'group_optional')
	{
		$found = true;
		break;
	}
}
if(!$found)
{
	$query = 'ALTER TABLE `ldap_server_settings` ADD `group_optional` TINYINT( 1 ) DEFAULT 0 '.
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->Field == 'mapping_info')
	{
		$found = true;
		break;
	}
}
if(!$found)
{
	$query = 'ALTER TABLE `ldap_role_group_mapping` ADD `mapping_info` TEXT DEFAULT NULL ';
	$res = $ilDB->query($query);
}
?>
<#974>
<?php
// add questioncount field in qpl_questionpool table
if (!$ilDB->tableColumnExists("qpl_questionpool", "questioncount"))
{
	$query = "ALTER TABLE `qpl_questionpool` ADD `questioncount` INT NOT NULL DEFAULT 0 AFTER `online`";
	$res = $ilDB->query($query);
}
?>
<#975>
<?php
	$query = "SELECT * FROM qpl_questionpool";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		if ($row["questioncount"] == 0)
		{
			$cquery = sprintf("SELECT COUNT(question_id) AS question_count FROM qpl_questions WHERE obj_fi = %s AND ISNULL(original_id) AND complete = '1'",
				$ilDB->quote($row["obj_fi"] . "")
			);
			$cresult = $ilDB->query($cquery);
			$crow = $cresult->fetchRow(DB_FETCHMODE_ASSOC);
			$uquery = sprintf("UPDATE qpl_questionpool SET questioncount = %s WHERE obj_fi = %s",
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
	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$max_id = $row->max_id;
	}
	$max_id = $max_id ? $max_id : 0;
	$query = "SELECT * FROM survey_answer WHERE answer_id > $max_id ORDER BY answer_id";
	$result = $ilDB->query($query);
	if ($result->numRows())
	{
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$active_id = 0;
			$activequery = "";
			if (strlen($row["anonymous_id"]))
			{
				$activequery = sprintf("SELECT * FROM survey_finished WHERE survey_fi = %s AND anonymous_id = %s",
					$ilDB->quote($row["survey_fi"]),
					$ilDB->quote($row["anonymous_id"])
				);
			}
			else
			{
				if ($row["user_fi"] > 0)
				{
					$activequery = sprintf("SELECT * FROM survey_finished WHERE survey_fi = %s AND user_fi = %s",
						$ilDB->quote($row["survey_fi"]),
						$ilDB->quote($row["user_fi"])
					);
				}
			}
			if (strlen($activequery))
			{
				$activeresult = $ilDB->query($activequery);
				if ($activeresult->numRows() == 1)
				{
					$activerow = $activeresult->fetchRow(DB_FETCHMODE_ASSOC);
					$active_id = $activerow["finished_id"];
				}
			}
			if ($active_id == 0)
			{
				// found an answer dataset the could not be associated with a user in a survey
				$ilLog->write("DB Migration 999: Found unassociated dataset, deleting it: " . print_r($row, TRUE));
			}
			$updatequery = sprintf("UPDATE survey_answer SET active_fi = %s WHERE answer_id = %s",
				$ilDB->quote($active_id),
				$ilDB->quote($row["answer_id"])
			);
			$updateresult = $ilDB->query($updatequery);
			// set last position
			$updatetemp = sprintf("INSERT INTO tmp_svy_migration (answer_id) VALUES (%s)",
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
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'mcst'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add missing delete rbac operation for media casts
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','6')";
$this->db->query($query);
?>

<#1002>
<?php
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'feed'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add missing delete rbac operation for web feeds
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','6')";
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
	if ($result->numRows())
	{
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$results_presentation = 1;
			if ($row["show_solution_details"])
			{
				$results_presentation = $results_presentation | 2;
			}
			if ($row["show_solution_printview"])
			{
				$results_presentation = $results_presentation | 4;
			}
			if ($row["show_solution_feedback"])
			{
				$results_presentation = $results_presentation | 8;
			}
			$update = sprintf("UPDATE tst_tests SET results_presentation = %s WHERE test_id = %s",
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

$q = "SELECT up1.usr_id as usr_id FROM usr_pref AS up1, usr_pref AS up2 ".
	" WHERE up1.keyword= ".$ilDB->quote("style")." AND up1.value= ".$ilDB->quote("blueshadow").
	" AND up2.keyword= ".$ilDB->quote("skin")." AND up2.value= ".$ilDB->quote("default").
	" AND up1.usr_id = up2.usr_id ";

$usr_set = $ilDB->query($q);

while ($usr_rec = $usr_set->fetchRow(DB_FETCHMODE_ASSOC))
{
	$q = "UPDATE usr_pref SET value = ".$ilDB->quote("default").
		" WHERE usr_id = ".$ilDB->quote($usr_rec["usr_id"]).
		" AND keyword = ".$ilDB->quote("skin");
	$ilDB->query($q);

	$q = "UPDATE usr_pref SET value = ".$ilDB->quote("delos").
		" WHERE usr_id = ".$ilDB->quote($usr_rec["usr_id"]).
		" AND keyword = ".$ilDB->quote("style");
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->Field == 'mapping_info_type')
	{
		$found = true;
		break;
	}
}
if(!$found)
{
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

while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$recf_id = $row->obj_id;
}

$query = "INSERT INTO rbac_ta SET typ_id = '".$recf_id."', ops_id = 1";
$ilDB->query($query);
$query = "INSERT INTO rbac_ta SET typ_id = '".$recf_id."', ops_id = 2";
$ilDB->query($query);
$query = "INSERT INTO rbac_ta SET typ_id = '".$recf_id."', ops_id = 3";
$ilDB->query($query);
$query = "INSERT INTO rbac_ta SET typ_id = '".$recf_id."', ops_id = 4";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$obj_id = $row->obj_id;
	$usr_id = $row->usr_id;

	$query = "SELECT obj_id FROM object_data WHERE description = 'Member of course obj_no.".$obj_id."'";
	$res_role_id = $ilDB->query($query);
	while($row_role_id = $res_role_id->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$role_id = $row_role_id->obj_id;

		$query = "REPLACE INTO rbac_ua ".
			"SET usr_id = '".$usr_id."', ".
			"rol_id = '".$role_id."' ";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$file_ids[] = $row->obj_id;
}

foreach($file_ids as $file_id)
{
	// Check if done
	$query = "SELECT * FROM tmp_migration WHERE obj_id = ".$file_id." AND passed = 1";
	$res = $ilDB->query($query);
	if($res->numRows())
	{
		continue;
	}

	if(!@file_exists(ilUpdateUtils::getDataDir().'/files/file_'.$file_id) or !@is_dir(ilUpdateUtils::getDataDir().'/files/file_'.$file_id))
	{
		$ilLog->write('DB Migration 1024: Files already migrated. File: file_'.$file_id);
		continue;
	}

	// Rename
	$fss = new ilFSStorageFile($file_id);
	$fss->create();


	if($fss->rename(ilUpdateUtils::getDataDir().'/files/file_'.$file_id,$fss->getAbsolutePath()))
	{
		$ilLog->write('DB Migration 1024: Success renaming file_'.$file_id);
	}
	else
	{
		$ilLog->write('DB Migration 1024: Failed renaming '.ilUpdateUtils::getDataDir().'/files/file_'.$file_id.' -> '.$fss->getAbsolutePath());
		continue;
	}

	// Save success
	$query = "REPLACE INTO tmp_migration SET obj_id = '".$file_id."',passed = '1'";
	$ilDB->query($query);

	// Update file size
	$size = ilObjFileAccess::_lookupFileSize($file_id);
	$query = "UPDATE file_data SET file_size = '".$size."' ".
		"WHERE file_id = ".$file_id;
	$ilDB->query($query);
	$ilLog->write('DB Migration 905: File size is '.$size.' Bytes');
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$event_ids[] = $row->event_ids;
}

foreach($event_ids as $event_id)
{
	// Check if done
	$query = "SELECT * FROM tmp_migration WHERE obj_id = ".$event_id." AND passed = 1";
	$res = $ilDB->query($query);
	if($res->numRows())
	{
		continue;
	}

	if(!@file_exists(ilUpdateUtils::getDataDir().'/events/event_'.$event_id))
	{
		$ilLog->write('DB Migration 1028: Already migrated: Event data for event id '.$event_id);
		continue;
	}

	// Rename
	$fss = new ilFSStorageEvent($event_id);
	$fss->create();


	if($fss->rename(ilUpdateUtils::getDataDir().'/events/event_'.$event_id,$fss->getAbsolutePath()))
	{
		$ilLog->write('DB Migration 1028: Success renaming event_'.$event_id);
	}
	else
	{
		$ilLog->write('DB Migration 1028: Failed renaming '.ilUpdateUtils::getDataDir().'/events/event_'.$event_id.' -> '.$fss->getAbsolutePath());
		continue;
	}

	// Save success
	$query = "REPLACE INTO tmp_migration SET obj_id = '".$event_id."',passed = '1'";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$archive_ids[$row->archive_ids]['id'] = $row->archive_ids;
	$archive_ids[$row->archive_ids]['name'] = $row->archive_name;
	$archive_ids[$row->archive_ids]['course_id'] = $row->course_id;

}

foreach($archive_ids as $archive_id => $data)
{
	// Check if done
	$query = "SELECT * FROM tmp_migration WHERE obj_id = ".$archive_id." AND passed = 1";
	$res = $ilDB->query($query);
	if($res->numRows())
	{
		continue;
	}

	if(!@file_exists(ilUpdateUtils::getDataDir().'/course/'.$data['name']))
	{
		$ilLog->write('DB Migration 1030: Archives already migrated: No data found for archive id '.$data['name']);
		continue;
	}

	// Rename
	$fss = new ilFSStorageCourse($data['course_id']);
	$fss->create();
	$fss->initArchiveDirectory();


	if($fss->rename(ilUpdateUtils::getDataDir().'/course/'.$data['name'],$fss->getArchiveDirectory().'/'.$data['name']))
	{
		$ilLog->write('DB Migration 1030: Success renaming archive '.$data['name']);
	}
	if($fss->rename(ilUpdateUtils::getDataDir().'/course/'.$data['name'].'.zip',$fss->getArchiveDirectory().'/'.$data['name'].'.zip'))
	{
		$ilLog->write('DB Migration 1030: Success renaming archive '.$data['name'].'.zip');
	}
	else
	{
		$ilLog->write('DB Migration 1030: Failed renaming '.ilUpdateUtils::getDataDir().'/course/'.$data['name'] .'-> '.
			 $fss->getArchiveDirectory().'/'.$data['name']);
		continue;
	}

	// Save success
	$query = "REPLACE INTO tmp_migration SET obj_id = '".$archive_id."',passed = '1'";
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

if(@is_dir($dir = ilUpdateUtils::getDataDir().'/course'))
{
	$dp = @opendir($dir);
	while(($filedir = readdir($dp)) !== false)
	{
		if($filedir == '.' or $filedir == '..')
		{
			continue;
		}
		if(preg_match('/^course_file([0-9]+)$/',$filedir,$matches))
		{
			$ilLog->write('DB Migration 1032: Found file: '.$filedir.' with course_id: '.$matches[1]);

			$fss_course = new ilFSStorageCourse($matches[1]);
			$fss_course->initInfoDirectory();

			if(@is_dir($info_dir = ilUpdateUtils::getDataDir().'/course/'.$filedir))
			{
				$dp2 = @opendir($info_dir);
				while(($file = readdir($dp2)) !== false)
				{
					if($file == '.' or $file == '..')
					{
						continue;
					}
					$fss_course->rename($from = ilUpdateUtils::getDataDir().'/course/'.$filedir.'/'.$file,
						$to = $fss_course->getInfoDirectory().'/'.$file);

					$ilLog->write('DB Migration 1032: Renamed: '.$from.' to: '.$to);
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'rcrs', 'Remote Course Object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];


// add operation assignment to link object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete,
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','6')";
$this->db->query($query);

// add create operation
$query = "INSERT INTO rbac_operations ".
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

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
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
$query = "UPDATE conditions SET ref_handling = 0 ".
	"WHERE target_type = 'st' OR trigger_type = 'crsg' ";
$ilDB->query($query);
?>
<#1062>
<?php
// Insert lm reference id for all preconditions (target type 'st')
$query = "SELECT id,target_obj_id FROM conditions ".
	"WHERE target_type = 'st'";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "SELECT ref_id FROM object_reference AS obr ".
		"JOIN lm_data AS lm ON obr.obj_id = lm_id ".
		"WHERE lm.obj_id = ".$row->target_obj_id." ";
	$res_ref = $ilDB->query($query);
	$row_ref = $res_ref->fetchRow(DB_FETCHMODE_OBJECT);

	if($row_ref->ref_id)
	{
		$query = "UPDATE conditions SET ".
			"target_ref_id = ".$row_ref->ref_id.' '.
			"WHERE id = ".$row->id." ";
		$ilDB->query($query);
	}
}
?>
<#1063>
<?php
global $ilLog;

// Delete all conditions if course is not parent
$query = "SELECT DISTINCT target_ref_id AS ref FROM conditions ".
	"WHERE target_type != 'crs' AND target_type != 'st' ";
$res = $ilDB->query($query);
$ref_ids = array();
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ref_ids[] = $row->ref;
}

$tree = new ilTree(ROOT_FOLDER_ID);
foreach($ref_ids as $ref_id)
{
	if(!$tree->checkForParentType($ref_id,'crs'))
	{
		$query = "DELETE FROM conditions ".
			"WHERE target_ref_id = ".$ref_id." ".
			"AND target_type != 'st' ";
		$ilDB->query($query);
		$ilLog->write('Delete condition for ref_id = '.$ref_id.' (not inside of course)');
	}
}
?>

<#1064>
<?php
// register new object type 'mds' for meta data setttings and advanced meta data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'mds', 'Meta Data settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('mds', '__MetaDataSettings', 'Meta Data Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'mds'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
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
if ($result->numRows())
{
	$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
	if ($row["max_id"] > 0)
	{
		$startid = $row["max_id"];
		// if not completed, delete the last entries
		$remove = "DELETE FROM tst_sequence WHERE active_fi = $startid";
		$result = $ilDB->query($remove);
	}
}

// start from the last valid active id and convert the sequence settings
$query = "SELECT * FROM tst_active WHERE active_id >= $startid ORDER BY active_id ASC";
$result = $ilDB->query($query);
if ($result->numRows())
{
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$sequence_array = explode(",", $row["sequence"]);
		if ($sequence_array === FALSE) $sequence_array = array();
		foreach ($sequence_array as $key => $value) $sequence_array[$key] = intval($value);
		$postponed = "NULL";
		if (strlen($row["postponed"]))
		{
			$postponed_array = explode(",", $row["postponed"]);
			foreach ($postponed_array as $key => $value) $postponed_array[$key] = intval($value);
			if (is_array($postponed_array))
			{
				$postponed = $ilDB->quote(serialize(array_unique($postponed_array)));
			}
		}
		for ($i = 0; $i <= $row["tries"]; $i++)
		{
			if (($i < $row["tries"]) || ($i == 0))
			{
				$insert = sprintf("INSERT INTO tst_sequence (active_fi, pass, sequence, postponed, hidden) VALUES (%s, %s, %s, %s, NULL)",
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
if($res->numRows() == 1)
{
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
$query = "INSERT INTO rbac_operations SET operation = 'add_thread', ".
	"description = 'Add Threads', ".
	"class = 'object'";

$res = $ilDB->query($query);
$new_ops_id = $ilDB->getLastInsertId();

$query = "SELECT obj_id FROM object_data ".
	"WHERE type ='typ' ".
	"AND title = 'frm' ";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$typ_id = $row['obj_id'];

$query = "INSERT INTO rbac_ta ".
	"SET typ_id = ".$ilDB->quote($typ_id).", ".
	"ops_id = ".$ilDB->quote($new_ops_id)." ";
$ilDB->query($query);


// Copy template permissions from 'edit_post'
$query = "SELECT ops_id FROM rbac_operations ".
	"WHERE operation = 'edit_post' ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$add_post_id = $row['ops_id'];
}

$query = "SELECT * FROM rbac_templates ".
	"WHERE ops_id = ".$ilDB->quote($add_post_id)." ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$query = "INSERT INTO rbac_templates ".
		"SET rol_id = ".$ilDB->quote($row['rol_id']).", ".
		"type = 'frm', ".
		"ops_id = ".$ilDB->quote($new_ops_id).", ".
		"parent = ".$ilDB->quote($row['parent'])." ";
	$ilDB->query($query);
}
?>

<#1094>
<?php
// insert new permission add thread to all forum objects
// Copy template permissions from 'edit_post'
$query = "SELECT ops_id FROM rbac_operations ".
	"WHERE operation = 'edit_post' ";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$add_post_id = $row['ops_id'];

$query = "SELECT ops_id FROM rbac_operations ".
	"WHERE operation = 'add_thread' ";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$add_thread_id = $row['ops_id'];

// get all forum rbac_pa entries
$query = "SELECT rol_id,ops_id,pa.ref_id FROM object_data AS obd ".
	"JOIN object_reference as ore ON obd.obj_id = ore.obj_id ".
	"JOIN rbac_pa AS pa ON ore.ref_id = pa.ref_id ".
	"WHERE type = 'frm' ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$operations = unserialize($row->ops_id);
	if(in_array($add_post_id,$operations))
	{
		$operations[] = $add_thread_id;
		$query = "UPDATE rbac_pa SET ".
			"ops_id = ".$ilDB->quote(serialize($operations))." ".
			"WHERE rol_id = ".$ilDB->quote($row->rol_id)." ".
			"AND ref_id = ".$ilDB->quote($row->ref_id)." ";
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
  `objective_id` varchar(255) default NULL,
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
if ($result->numRows())
{
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$querysum = sprintf("SELECT SUM(points) AS reachedpoints FROM tst_test_result WHERE active_fi = %s AND pass = %s",
			$ilDB->quote($row["active_fi"] . ""),
			$ilDB->quote($row["pass"] . "")
		);
		$resultsum = $ilDB->query($querysum);
		if ($resultsum->numRows() > 0)
		{
			$rowsum = $resultsum->fetchRow(DB_FETCHMODE_ASSOC);
			$newresultquery = sprintf("REPLACE INTO tst_test_pass_result SET active_fi = %s, pass = %s, points = %s",
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
if ($result->numRows())
{
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$querynew = sprintf("UPDATE tst_tests SET results_presentation = %s WHERE test_id = %s",
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
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'lngf'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add write permission for language folder
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
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

$frm_modetator_ops = array(1, 2, 3, 4, 6, 9, 10, 58, 62);
foreach ($frm_modetator_ops as $op_id)
{
	$query = "INSERT
			  INTO rbac_templates
		 	  VALUES (".$this->db->quote($frm_modetator_tpl_id).", 'frm', ".$this->db->quote($op_id).", 8)";
	$this->db->query($query);
}

$query = "INSERT
		  INTO rbac_fa
		  VALUES (".$this->db->quote($frm_modetator_tpl_id).", 8, 'n', 'n')";
$this->db->query($query);
?>
<#1118>
<?php

$query = "SELECT * FROM rbac_operations WHERE operation = 'join' ";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$join_ops_id = $row["ops_id"];

$query = "SELECT * FROM rbac_operations WHERE operation = 'leave' ";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$leave_ops_id = $row["ops_id"];

$types = array("lm", "sahs", "glo", "webr");

foreach($types as $type)
{
	$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
		" AND title = '".$type."'";
	$res = $this->db->query($query);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	$typ_id = $row["obj_id"];
	if ($typ_id > 0)
	{
		$q = ("DELETE FROM rbac_ta WHERE typ_id = ".$ilDB->quote($typ_id)." AND ops_id = ".
			$ilDB->quote($join_ops_id));
		$ilDB->query($q);
//echo "<br>$q";
		$q = ("DELETE FROM rbac_ta WHERE typ_id = ".$ilDB->quote($typ_id)." AND ops_id = ".
			$ilDB->quote($leave_ops_id));
		$ilDB->query($q);
//echo "<br>$q";
		$q = ("DELETE FROM rbac_templates WHERE type = ".$ilDB->quote($type)." AND ops_id = ".
			$ilDB->quote($join_ops_id));
		$ilDB->query($q);
//echo "<br>$q";
		$q = ("DELETE FROM rbac_templates WHERE type = ".$ilDB->quote($type)." AND ops_id = ".
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
$query = "SELECT obj_id FROm object_data ".
	"WHERE type = 'rolt' ".
	"AND title = 'Author' ".
	"OR title = 'Co-Author' ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "DELETE FROM rbac_templates WHERE ".
		"rol_id = ".$ilDB->quote($row->obj_id)." ".
		"AND type = 'root' ".
		"AND ops_id = 2 ".
		"AND parent = 8";
	$ilDB->query($query);

	$query = "INSERT INTO rbac_templates SET ".
		"rol_id = ".$ilDB->quote($row->obj_id).", ".
		"type = 'root', ".
		"ops_id = 2, ".
		"parent = 8";
	$ilDB->query($query);

	$query = "DELETE FROM rbac_templates WHERE ".
		"rol_id = ".$ilDB->quote($row->obj_id)." ".
		"AND type = 'root' ".
		"AND ops_id = 3 ".
		"AND parent = 8";
	$ilDB->query($query);

	$query = "INSERT INTO rbac_templates SET ".
		"rol_id = ".$ilDB->quote($row->obj_id).", ".
		"type = 'root', ".
		"ops_id = 3, ".
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'cmps', 'Component settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('cmps', '__ComponentSettings', 'Component Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'cmps'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
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
		." FROM object_data AS d"
		." LEFT JOIN object_reference AS r ON r.obj_id=d.obj_id"
		." WHERE type ='facs'"
		;
$result = $ilDB->query($query);
while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	$obj_id = $row['obj_id'];
	$ref_id = $row['ref_id'];
	if ($ref_id !== null)
	{
		$ilDB->query("DELETE FROM tree WHERE child=".$ilDB->quote($ref_id));
		$ilDB->query("DELETE FROM object_reference WHERE ref_id=".$ilDB->quote($ref_id));
	}
	$ilDB->query("DELETE FROM object_data WHERE obj_id=".$ilDB->quote($obj_id));
}

// delete 'facs' object type
$query = "SELECT obj_id FROM object_data"
		." WHERE type ='typ' AND title ='facs'"
		;
$result = $ilDB->query($query);
while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	$typ_id = $row['obj_id'];
	$ilDB->query("DELETE FROM rbac_ta WHERE typ_id=".$ilDB->quote($typ_id));
	$ilDB->query("DELETE FROM object_data WHERE obj_id=".$ilDB->quote($typ_id));
}

// ----------------


// REGISTER NEW OBJECT TYPE 'facs' for File Access settings object
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'facs', 'File Access settings object', -1, now(), now())";
$ilDB->query($query);
$typ_id =  $ilDB->getLastInsertId();

// REGISTER RBAC OPERATIONS FOR OBJECT TYPE
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
		."  (".$ilDB->quote($typ_id).",'1')"
		.", (".$ilDB->quote($typ_id).",'2')"
		.", (".$ilDB->quote($typ_id).",'3')"
		.", (".$ilDB->quote($typ_id).",'4')"
		;
$ilDB->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('facs', '__File Access', 'File Access settings', -1, now(), now())";
$ilDB->query($query);
$obj_id = $ilDB->getLastInsertId();

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES(".$ilDB->quote($obj_id).")";
$res = $ilDB->query($query);
$ref_id = $ilDB->getLastInsertId();

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($ref_id,SYSTEM_FOLDER_ID);


// Create data table for WebDAV Locks
// IMPORTANT: To prevent data loss on installations which use the HSLU-patches,
//            the WebDAV tables must only be created if the do not exist yet,
//            and the tables must be created exactly like this. The tables may
//            be altered in subsequent update scripts however.
//            For performance reasons, all these tables should be InnoDB tables,
//            but they are currently created with the default table engine, in
//            order not to require configuration changes for MySQL.
$q = "CREATE TABLE IF NOT EXISTS dav_lock ( ".
" token varchar(255) NOT NULL default '', ".
" obj_id int(11) NOT NULL default 0, ".
" node_id int(11) NOT NULL default 0, ".
" ilias_owner int(11) NOT NULL default 0, ".
" dav_owner varchar(200) default null, ".
" expires int(11) NOT NULL default 0, ".
" depth int(11) NOT NULL default 0, ".
" type char(1) NOT NULL default 'w', ".
" scope char(1) NOT NULL default 's', ".
" PRIMARY KEY (token), ".
" UNIQUE KEY token (token), ".
" KEY path (obj_id,node_id), ".
" KEY path_3 (obj_id,node_id,token), ".
" KEY expires (expires) ".
")"; //") ENGINE=InnoDB;";
$r = $ilDB->db->query($q);
if (MDB2::isError($r) || MDB2::isError($r->result))
{
	return 'could\'nt create table "dav_lock": '.
	((MDB2::isError($r->result)) ? $r->result->getMessage() : $r->getMessage());
}
// Create data table for WebDAV Properties
$q = "CREATE TABLE IF NOT EXISTS dav_property ( ".
" obj_id int(11) NOT NULL default 0, ".
" node_id int(11) NOT NULL default 0, ".
" ns varchar(120) NOT NULL default 'DAV:', ".
" name varchar(120) NOT NULL default '', ".
" value text, ".
" PRIMARY KEY (obj_id,node_id,name,ns), ".
" KEY path (obj_id,node_id) ".
")"; //") ENGINE=InnoDB;";
$r = $ilDB->db->query($q);
if (MDB2::isError($r) || MDB2::isError($r->result))
{
	return 'could\'nt create table "dav_property": '.
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
$q = "CREATE TABLE IF NOT EXISTS write_event ( ".
" obj_id INT(11) NOT NULL DEFAULT 0, ".
" parent_obj_id INT(11) NOT NULL DEFAULT 0, ".
" usr_id INT(11) NOT NULL DEFAULT 0, ".
" action VARCHAR(8) NOT NULL DEFAULT '', ".
" ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ".
" PRIMARY KEY (obj_id, ts, parent_obj_id, action), ".
" KEY parent_key (parent_obj_id, ts) ".
")"; //") ENGINE=InnoDB";
$r = $ilDB->db->query($q);

$q = "CREATE TABLE IF NOT EXISTS read_event ( ".
" obj_id INT(11) NOT NULL DEFAULT 0, ".
" usr_id INT(11) NOT NULL DEFAULT 0, ".
" ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ".
" read_count int(11) NOT NULL DEFAULT 0, ".
" PRIMARY KEY (obj_id, usr_id) ".
")"; //") ENGINE=InnoDB";
$r = $ilDB->db->query($q);

$q = "CREATE TABLE IF NOT EXISTS catch_write_events ( ".
" obj_id int(11) NOT NULL default 0, ".
" usr_id int(11) NOT NULL default 0, ".
" ts timestamp NOT NULL default current_timestamp, ".
" PRIMARY KEY (obj_id, usr_id) ".
")"; //") ENGINE=InnoDB";
$r = $ilDB->db->query($q);

// Track existing write events. This MUST always be done, when change event tracking
// is activated. If it is not done, change event tracking will not work as expected.
$q = "INSERT IGNORE INTO write_event ".
	"(obj_id,parent_obj_id,usr_id,action,ts) ".
	"SELECT r1.obj_id,r2.obj_id,d.owner,'create',d.create_date ".
	"FROM object_data AS d ".
	"JOIN object_reference AS r1 ON d.obj_id=r1.obj_id ".
	"JOIN tree AS t ON t.child=r1.ref_id ".
	"JOIN object_reference as r2 on r2.ref_id=t.parent ";
$r = $ilDB->db->query($q);

// activate change event tracking:
$q = "REPLACE INTO settings ".
	"(module, keyword, value) VALUES ".
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'svyf', 'Survey Settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('svyf', '__SurveySettings', 'Survey Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'svyf'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'sess', 'Session object', -1, now(), now())";
$ilDB->query($query);
$typ_id =  $ilDB->getLastInsertId();

// Register permissions for sessions
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
		."  (".$ilDB->quote($typ_id).",'1')"
		.", (".$ilDB->quote($typ_id).",'2')"
		.", (".$ilDB->quote($typ_id).",'3')"
		.", (".$ilDB->quote($typ_id).",'4')"
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$course_obj_id = $row->obj_id;

	// check if already migrated
	$query = "SELECT type FROM object_data WHERE obj_id = ".$course_obj_id;
	$obj_res = $ilDB->query($query);
	$obj_row = $obj_res->fetchRow();

	if($obj_row[0] == 'sess')
	{
		$ilLog->write('DB Migration 1194: Session with event_id: '.$row->event_id.' already migrated.');
		continue;
	}

	// find course ref_id
	$query = "SELECT ref_id FROM object_reference WHERE obj_id = '".$course_obj_id."' ";
	$ref_res = $ilDB->query($query);
	$ref_row = $ref_res->fetchRow();

	if(!$ref_row[0])
	{
		$ilLog->write('DB Migration 1194: Found session without course ref_id. event_id: '.$row->event_id.', obj_id: '.$row->obj_id);
		continue;
	}
	$course_ref_id = $ref_row[0];

	// Create object data entry
	$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('sess', ".$ilDB->quote($row->title).", ".$ilDB->quote($row->description).", 6, now(), now())";
	$ilDB->query($query);
	$session_obj_id = $ilDB->getLastInsertId();

	// Insert long description
	$query = "INSERT INTO object_description SET obj_id = ".$session_obj_id.", description =  ".$ilDB->quote($row->description);

	// Create reference
	$query = "INSERT INTO object_reference (obj_id) VALUES('".$session_obj_id."')";
	$ilDB->query($query);
	$session_ref_id = $ilDB->getLastInsertId();

	// check if course is deleted
	// yes => insert into tree with negative tree id
	$query = "SELECT tree FROM tree WHERE child = ".$course_ref_id;
	$tree_res = $ilDB->query($query);
	$tree_row = $tree_res->fetchRow();
	$tree_id = $tree_row[0];
	if($tree_id != 1)
	{
		$current_tree = new ilTree($tree_id);
	}
	else
	{
		$current_tree = new ilTree(ROOT_FOLDER_ID);
	}

	// Insert into tree
	$current_tree->insertNode($session_ref_id,$course_ref_id);

	// Update all event related tables

	// event
	$query = "UPDATE event SET obj_id = ".$session_obj_id." ".
		"WHERE event_id = ".$row->event_id;
	$ilDB->query($query);

	// event_appointment
	$query = "UPDATE event_appointment SET event_id = ".$session_obj_id." ".
		"WHERE event_id = ".$row->event_id." ";
	$ilDB->query($query);

	// event_id
	$query = "UPDATE event_items SET event_id = ".$session_obj_id." ".
		"WHERE event_id = ".$row->event_id." ";
	$ilDB->query($query);

	// event participants
	$query = "UPDATE event_participants SET event_id = ".$session_obj_id." ".
		"WHERE event_id = ".$row->event_id." ";
	$ilDB->query($query);

	// adjust permissions
	$query = "SELECT * FROM rbac_pa WHERE ref_id = ".$course_ref_id;
	$pa_res = $ilDB->query($query);
	while($pa_row = $pa_res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$new_ops = array();
		$operations = unserialize($pa_row->ops_id);

		if(in_array(1,$operations))
		{
			$new_ops[] = 1;
		}
		if(in_array(2,$operations))
		{
			$new_ops[] = 2;
		}
		if(in_array(3,$operations))
		{
			$new_ops[] = 3;
		}
		if(in_array(4,$operations))
		{
			$new_ops[] = 4;
		}
		$query = "INSERT INTO rbac_pa SET ".
			"rol_id = ".$ilDB->quote($pa_row->rol_id).", ".
			"ops_id = ".$ilDB->quote(serialize($new_ops)).", ".
			"ref_id = ".$ilDB->quote($session_ref_id)." ";
		$ilDB->query($query);
	}
}
?>
<#1196>
<?php
// add create operation for
$query = "INSERT INTO rbac_operations ".
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

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
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
		."  (".$ilDB->quote($typ_id).",'".$ops_id."')";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$unlimited = ($row->expiration == '0000-00-00 00:00:00' ? 1 : 0);
	
	if($unlimited)
	{
		$start = '0000-00-00 00:00:00';
	}
	else
	{
		$start = '2002-01-01 00:00:00';
	}

	if($row->register == 0)
	{
		$unlimited = 1;
	}

	$query = "INSERT INTO grp_settings ".
		"SET obj_id = ".$ilDB->quote($row->grp_id).", ".
		"information = '', ".
		"grp_type = 0, ".
		"registration_type = ".$ilDB->quote($row->register).", ".
		"registration_enabled = 1, ".
		"registration_unlimited = ".$unlimited.", ".
		"registration_start = ".$ilDB->quote($start)." , ".
		"registration_end = ".$ilDB->quote($row->expiration).", ".
		"registration_password = ".$ilDB->quote($row->password).", ".
		"registration_max_members = 0, ".
		"waiting_list = 0, ".
		"latitude = ".$ilDB->quote($row->latitude).", ".
		"longitude = ".$this->db->quote($row->longitude).", ".
		"location_zoom = ".$ilDB->quote($row->location_zoom).", ".
		"enablemap = ".$ilDB->quote($row->enable_group_map)." ";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO il_subscribers ".
		"SET obj_id = ".$ilDB->quote($row->grp_id).", ".
		"usr_id = ".$ilDB->quote($row->user_id).", ".
		"sub_time = ".$ilDB->quote($row->unix).", ".
		"subject = ".$ilDB->quote($row->subject)." ";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
        $permissions_empty = false;
}
if($permissions_empty)
{
        // new object type for course sessions
        $query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
                        "VALUES ('typ', 'sess', 'Session object', -1, now(), now())";
        $ilDB->query($query);
        $typ_id =  $ilDB->getLastInsertId();

        // Register permissions for sessions
        // 1: edit_permissions, 2: visible, 3: read, 4:write
        $query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
                        ."  (".$ilDB->quote($typ_id).",'1')"
                        .", (".$ilDB->quote($typ_id).",'2')"
                        .", (".$ilDB->quote($typ_id).",'3')"
                        .", (".$ilDB->quote($typ_id).",'4')"
                        ;
        $ilDB->query($query);

        $query = "SELECT ops_id FROM rbac_operations WHERE operation = 'copy'";
        $res = $ilDB->query($query);
        $row = $res->fetchRow();
        $ops_id = $row[0];

        // Register copy permissions for sessions
        $query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
                        ."  (".$ilDB->quote($typ_id).",'".$ops_id."')";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->fulltime)
	{
		$query = "UPDATE event_appointment SET ".
			"start = ".$ilDB->quote(gmdate('Y-m-d',$row->starting_time)).", ".
			"end = ".$ilDB->quote(gmdate('Y-m-d',$row->ending_time))." ".
			"WHERE appointment_id = ".$ilDB->quote($row->appointment_id)." ";
	}
	else
	{
		$query = "UPDATE event_appointment SET ".
			"start = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->starting_time)).", ".
			"end = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->ending_time))." ".
			"WHERE appointment_id = ".$ilDB->quote($row->appointment_id)." ";
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
while($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
{
	$rows[] = $rec;
}
$ilDB->query("DELETE FROM container_sorting");
$ilDB->query("ALTER TABLE container_sorting ADD PRIMARY KEY (obj_id, child_id)");
foreach($rows as $row)
{
	$pos = unserialize($row["items"]);
	foreach($pos as $i => $p)
	{
		$ilDB->query("REPLACE INTO container_sorting (obj_id, child_id, type, position) VALUES (".
			$ilDB->quote($row["obj_id"]).",".
			$ilDB->quote($i).",".
			$ilDB->quote($row["type"]).",".
			$ilDB->quote($p).
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$insertquery = sprintf("INSERT INTO qpl_answer_matching_term (term_id, question_fi, term) VALUES (NULL, %s, %s)",
		$ilDB->quote($row->question_fi),
		$ilDB->quote($row->answertext)
	);
	$ilDB->query($insertquery);
	$newTermID = $ilDB->getLastInsertId();

	$updatequery = sprintf("UPDATE qpl_answer_matching SET answertext = '$newTermID' WHERE answer_id = " . $ilDB->quote($row->answer_id));
	$ilDB->query($updatequery);
}
?>
<#1233>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1234>
<?php
// register new object type 'mcts' for news settings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'mcts', 'Mediacast settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('mcts', '__MediacastSettings', 'Mediacast Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'mcts'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to mcst settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'wiki', 'Wiki', -1, now(), now())";
$this->db->query($query);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'wiki'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations for feed object
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
$this->db->query($query);

// new permission: edit content
$query = "INSERT INTO rbac_operations SET operation = 'edit_content', ".
	"description = 'Edit content', ".
	"class = 'object'";

$res = $ilDB->query($query);
$new_ops_id = $ilDB->getLastInsertId();

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$new_ops_id."')";
$this->db->query($query);

// add create operation for wikis
$query = "INSERT INTO rbac_operations ".
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

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='crs'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='grp'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$ilDB->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='fold'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
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
$query = "INSERT INTO rbac_operations SET operation = 'edit_event', ".
	"description = 'Edit calendar event', ".
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

$query = "INSERT INTO rbac_ta SET typ_id = ".$ilDB->quote($cals).", ops_id = ".$ilDB->quote($new_ops_id)." ";
$ilDB->query($query);

$query = "INSERT INTO rbac_ta SET typ_id = ".$ilDB->quote($crs).", ops_id = ".$ilDB->quote($new_ops_id)." ";
$ilDB->query($query);

$query = "INSERT INTO rbac_ta SET typ_id = ".$ilDB->quote($grp).", ops_id = ".$ilDB->quote($new_ops_id)." ";
$ilDB->query($query);

$query = "INSERT INTO rbac_ta SET typ_id = ".$ilDB->quote($sess).", ops_id = ".$ilDB->quote($new_ops_id)." ";
$ilDB->query($query);
?>

<#1248>
<?php

$wd = getcwd();

include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');

$query = "SELECT obd.obj_id AS obj_id ,title,od.description AS description,activation_type,activation_start,activation_end, ".
	"subscription_limitation_type,subscription_start,subscription_end FROM crs_settings AS cs  ".
	"JOIN object_data as obd ON obd.obj_id = cs.obj_id ".
	"JOIN object_description AS od ON od.obj_id = obd.obj_id ".
	"WHERE subscription_limitation_type = 2 OR ".
	"activation_type = 2 ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$color = ilCalendarAppointmentColors::_getRandomColorByType('crs');

	$query = "INSERT INTO cal_categories SET ".
		"obj_id = ".$row->obj_id.", ".
		"title = ".$ilDB->quote($row->title).", ".
		"color = '".$color."', ".
		"type = 2";
	$ilDB->query($query);

	$cat_id = $ilDB->getLastInsertId();

	if($row->subscription_limitation_type == 2)
	{
		$query = "INSERT INTO cal_entries SET ".
			"title = ".$ilDB->quote($row->title).", ".
			"subtitle = ".$ilDB->quote('crs_cal_reg_start').", ".
			"description = ".$ilDB->quote($row->description).", ".
			"start = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->subscription_start)).", ".
			"end = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->subscription_start)).", ".
			"auto_generated = 1,".
			"context_id = 1, ".
			"translation_type = 1";
		$ilDB->query($query);

		$cal_id = $ilDB->getLastInsertId();

		$query = "INSERT INTO cal_category_assignments ".
			"SET cal_id = ".$ilDB->quote($cal_id).", cat_id = ".$ilDB->quote($cat_id)." ";
		$ilDB->query($query);

		$query = "INSERT INTO cal_entries SET ".
			"title = ".$ilDB->quote($row->title).", ".
			"subtitle = ".$ilDB->quote('crs_cal_reg_end').", ".
			"description = ".$ilDB->quote($row->description).", ".
			"start = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->subscription_end)).", ".
			"end = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->subscription_end)).", ".
			"auto_generated = 1,".
			"context_id = 2, ".
			"translation_type = 1";
		$ilDB->query($query);

		$cal_id = $ilDB->getLastInsertId();

		$query = "INSERT INTO cal_category_assignments ".
			"SET cal_id = ".$ilDB->quote($cal_id).", cat_id = ".$ilDB->quote($cat_id)." ";
		$ilDB->query($query);
	}
	if($row->activation_type == 2)
	{
		$query = "INSERT INTO cal_entries SET ".
			"title = ".$ilDB->quote($row->title).", ".
			"subtitle = ".$ilDB->quote('crs_cal_activation_start').", ".
			"description = ".$ilDB->quote($row->description).", ".
			"start = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->activation_start)).", ".
			"end = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->activation_start)).", ".
			"auto_generated = 1,".
			"context_id = 3, ".
			"translation_type = 1";
		$ilDB->query($query);

		$cal_id = $ilDB->getLastInsertId();

		$query = "INSERT INTO cal_category_assignments ".
			"SET cal_id = ".$ilDB->quote($cal_id).", cat_id = ".$ilDB->quote($cat_id)." ";
		$ilDB->query($query);

		$query = "INSERT INTO cal_entries SET ".
			"title = ".$ilDB->quote($row->title).", ".
			"subtitle = ".$ilDB->quote('crs_cal_activation_end').", ".
			"description = ".$ilDB->quote($row->description).", ".
			"start = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->activation_end)).", ".
			"end = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->activation_end)).", ".
			"auto_generated = 1,".
			"context_id = 3, ".
			"translation_type = 1";
		$ilDB->query($query);

		$cal_id = $ilDB->getLastInsertId();

		$query = "INSERT INTO cal_category_assignments ".
			"SET cal_id = ".$ilDB->quote($cal_id).", cat_id = ".$ilDB->quote($cat_id)." ";
		$ilDB->query($query);
	}
}

?>

<#1249>
<?php

$wd = getcwd();

include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');

$query = "SELECT obd.obj_id AS obj_id ,title,od.description AS description, ".
	"registration_type,registration_unlimited,UNIX_TIMESTAMP(registration_start) AS registration_start, ".
	"UNIX_TIMESTAMP(registration_end) AS registration_end ".
	"FROM grp_settings AS gs ".
	"JOIN object_data as obd ON obd.obj_id = gs.obj_id ".
	"JOIN object_description AS od ON obd.obj_id = od.obj_id ".
	"WHERE registration_type != -1 AND ".
	"registration_unlimited = 0 ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$color = ilCalendarAppointmentColors::_getRandomColorByType('grp');

	$query = "INSERT INTO cal_categories SET ".
		"obj_id = ".$row->obj_id.", ".
		"title = ".$ilDB->quote($row->title).", ".
		"color = '".$color."', ".
		"type = 2";
	$ilDB->query($query);

	$cat_id = $ilDB->getLastInsertId();

	if($row->registration_start)
	{
		$query = "INSERT INTO cal_entries SET ".
				"title = ".$ilDB->quote($row->title).", ".
				"subtitle = ".$ilDB->quote('grp_cal_reg_start').", ".
				"description = ".$ilDB->quote($row->description).", ".
				"start = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->registration_start)).", ".
				"end = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->registration_start)).", ".
				"auto_generated = 1,".
				"context_id = 1, ".
				"translation_type = 1";
		$ilDB->query($query);

		$cal_id = $ilDB->getLastInsertId();

		$query = "INSERT INTO cal_category_assignments ".
			"SET cal_id = ".$ilDB->quote($cal_id).", cat_id = ".$ilDB->quote($cat_id)." ";
		$ilDB->query($query);
	}
	if($row->registration_end)
	{
		$query = "INSERT INTO cal_entries SET ".
			"title = ".$ilDB->quote($row->title).", ".
			"subtitle = ".$ilDB->quote('grp_cal_reg_end').", ".
			"description = ".$ilDB->quote($row->description).", ".
			"start = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->registration_end)).", ".
			"end = ".$ilDB->quote(gmdate('Y-m-d H:i:s',$row->registration_end)).", ".
			"auto_generated = 1,".
			"context_id = 2, ".
			"translation_type = 1";
		$ilDB->query($query);

		$cal_id = $ilDB->getLastInsertId();

		$query = "INSERT INTO cal_category_assignments ".
			"SET cal_id = ".$ilDB->quote($cal_id).", cat_id = ".$ilDB->quote($cat_id)." ";
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


$query = "SELECT * FROM rbac_templates ".
	"WHERE type = 'crs' ".
	"AND ops_id IN ('".implode("','",$ops)."') ";

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO rbac_templates ".
		"SET rol_id = ".$ilDB->quote($row->rol_id).", ".
		"type = 'sess', ".
		"ops_id = ".$ilDB->quote($row->ops_id).", ".
		"parent = ".$ilDB->quote($row->parent)." ";
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

$query = "DELETE FROM rbac_templates WHERE type = 'crs' AND ops_id = ".$ilDB->quote($ops)." ";
$ilDB->query($query);

$query = "DELETE FROM rbac_templates WHERE type = 'sess' AND ops_id = ".$ilDB->quote($ops)." ";
$ilDB->query($query);

$query = "SELECT * FROM rbac_templates WHERE type = 'crs' AND ops_id = 4";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO rbac_templates SET ".
		"rol_id = ".$ilDB->quote($row->rol_id).", ".
		"type = 'sess', ".
		"ops_id = ".$ilDB->quote($ops).", ".
		"parent = ".$ilDB->quote($row->parent)." ";
	$ilDB->query($query);

	$query = "INSERT INTO rbac_templates SET ".
		"rol_id = ".$ilDB->quote($row->rol_id).", ".
		"type = 'crs', ".
		"ops_id = ".$ilDB->quote($ops).", ".
		"parent = ".$ilDB->quote($row->parent)." ";
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

$query = "DELETE FROM rbac_templates WHERE type = 'grp' AND ops_id = ".$ilDB->quote($ops)." ";
$ilDB->query($query);

$query = "SELECT * FROM rbac_templates WHERE type = 'grp' AND ops_id = 4";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO rbac_templates SET ".
		"rol_id = ".$ilDB->quote($row->rol_id).", ".
		"type = 'grp', ".
		"ops_id = ".$ilDB->quote($ops).", ".
		"parent = ".$ilDB->quote($row->parent)." ";
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
$query = "SELECT ref_id FROM object_data AS obd JOIN object_reference AS obr ON obd.obj_id = obr.obj_id WHERE type = 'crs' ".
	"OR type = 'grp' OR type = 'sess'";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	// get rbac_pa entries
	$query = "SELECT * FROM rbac_pa WHERE ref_id = ".$ilDB->quote($row->ref_id)." ";
	$pa_res = $ilDB->query($query);
	while($pa_row = $pa_res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$current_ops = unserialize($pa_row->ops_id);
		if(in_array(4,$current_ops) and !in_array($ops,$current_ops))
		{
			$current_ops[] = (int) $ops;
			$query = "UPDATE rbac_pa SET ops_id = ".$ilDB->quote(serialize($current_ops))." ".
				"WHERE rol_id = ".$ilDB->quote($pa_row->rol_id)." ".
				"AND ref_id = ".$ilDB->quote($pa_row->ref_id)." ";
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

$query = "SELECT obd.obj_id AS obj_id ,obd.title,obd.description AS description,start,end, location,fulltime ".
	"FROM event AS e ".
	"JOIN object_data as obd ON obd.obj_id = e.obj_id ".
	"LEFT JOIN object_description AS od ON od.obj_id = obd.obj_id ".
	"JOIN event_appointment AS ea ON e.obj_id = ea.event_id ";
$ilLog->write($query);
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$color = ilCalendarAppointmentColors::_getRandomColorByType('sess');

	$query = "INSERT INTO cal_categories SET ".
		"obj_id = ".$ilDB->quote($row->obj_id).", ".
		"title = ".$ilDB->quote($row->title).", ".
		"color = '".$color."', ".
		"type = 2";
	$ilDB->query($query);

	$cat_id = $ilDB->getLastInsertId();

	$query = "INSERT INTO cal_entries SET ".
		"title = ".$ilDB->quote($row->title).", ".
		"subtitle = '', ".
		"description = ".$ilDB->quote($row->description).", ".
		"fullday = ".$ilDB->quote($row->fulltime).", ".
		"start = ".$ilDB->quote($row->start).", ".
		"end = ".$ilDB->quote($row->end).", ".
		"auto_generated = 1,".
		"context_id = 1, ".
		"translation_type = 0 ";
	$ilDB->query($query);
	$cal_id = $ilDB->getLastInsertId();

	$query = "INSERT INTO cal_category_assignments ".
		"SET cal_id = ".$ilDB->quote($cal_id).", cat_id = ".$ilDB->quote($cat_id)." ";
	$ilDB->query($query);

}
?>

<#1256>
<?php
$wd = getcwd();

global $ilLog;

include_once('Services/Calendar/classes/class.ilCalendarAppointmentColors.php');

// Create missing crs calendars

$query = "SELECT obd.obj_id,obd.title,obd.type FROM object_data AS obd ".
	"LEFT JOIN cal_categories AS cc on obd.obj_id = cc.obj_id AND cc.type = 2 ".
	"WHERE cc.obj_id IS NULL and obd.type = 'crs' ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$color = ilCalendarAppointmentColors::_getRandomColorByType('crs');

	$query = "INSERT INTO cal_categories SET ".
		"obj_id = ".$ilDB->quote($row->obj_id).", ".
		"title = ".$ilDB->quote($row->title).", ".
		"color = '".$color."', ".
		"type = 2";
	$ilDB->query($query);
}

// Create missing grp calendars
$query = "SELECT obd.obj_id,obd.title,obd.type FROM object_data AS obd ".
	"LEFT JOIN cal_categories AS cc on obd.obj_id = cc.obj_id AND cc.type = 2 ".
	"WHERE cc.obj_id IS NULL and obd.type = 'grp' ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$color = ilCalendarAppointmentColors::_getRandomColorByType('grp');

	$query = "INSERT INTO cal_categories SET ".
		"obj_id = ".$ilDB->quote($row->obj_id).", ".
		"title = ".$ilDB->quote($row->title).", ".
		"color = '".$color."', ".
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "SELECT * FROM crs_objective_qst ".
		"WHERE objective_id = ".$ilDB->quote($row->objective_id)." ".
		"AND ref_id = ".$ilDB->quote($row->ref_id)." ";
	$qst_res = $ilDB->query($query);

	$sum_points = 0;
	while($qst_row = $qst_res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		// Read possible points
		$query = "SELECT points FROM qpl_questions WHERE question_id = ".$ilDB->quote($qst_row->question_id)." ";
		$p_res = $ilDB->query($query);

		while($p_row = $p_res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$sum_points += $p_row->points;
		}
	}
	$required = $sum_points * $row->tst_limit / 100;

	$query = "UPDATE crs_objective_tst ".
		"SET tst_limit = ".$ilDB->quote($required)." ".
		"WHERE test_objective_id = ".$ilDB->quote($row->test_objective_id)." ";
	$ilDB->query($query);
}
?>

<#1260>
<?php
$objectives = array();
$query = "SELECT * FROM crs_objective_tst ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if(!isset($objectives[$row->objective_id][$row->tst_status]))
	{
		$objectives[$row->objective_id][$row->tst_status] = $row->tst_limit;
	}
	else
	{
		$objectives[$row->objective_id][$row->tst_status] += $row->tst_limit;
	}
}

foreach($objectives as $objective_id => $status)
{
	if(isset($status[0]))
	{
		$query = "UPDATE crs_objective_tst SET tst_limit = ".$ilDB->quote($status[0])." ".
			"WHERE objective_id = ".$ilDB->quote($objective_id)." ".
			"AND tst_status = 0 ";
		$ilDB->query($query);
	}
	if(isset($status[1]))
	{
		$query = "UPDATE crs_objective_tst SET tst_limit = ".$ilDB->quote($status[1])." ".
			"WHERE objective_id = ".$ilDB->quote($objective_id)." ".
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->field == 'mid' or $row->Field == 'mid')
	{
		$mid_missing = false;
		continue;
	}
}

if($mid_missing)
{
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->field == 'mid' or $row->Field == 'mid')
	{
		$mid_missing = false;
	}
	if($row->field == 'organization' or $row->Field == 'organization')
	{
		$org_missing = false;
	}
}
if($mid_missing)
{
	$query = "ALTER TABLE remote_course_settings ADD mid INT( 11 ) NOT NULL DEFAULT '0'";
	$ilDB->query($query);
}
if($org_missing)
{
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->registration_max_members)
	{
		$query = "UPDATE grp_settings SET registration_membership_limited = 1 WHERE obj_id = ".$ilDB->quote($row->obj_id)." ";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->subscription_max_members)
	{
		$query = "UPDATE crs_settings SET subscription_membership_limitation = 1 WHERE obj_id = ".$ilDB->quote($row->obj_id)." ";
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
if (!$ilDB->tableColumnExists("tst_tests", "kiosk"))
{
	$query = "ALTER TABLE tst_tests ADD COLUMN kiosk INT NOT NULL DEFAULT 0";
	$res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_tests", "resultoutput"))
{
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$event_id = $row->item_id;
	$crs_obj_id = $row->obj_id;

	// Read event ref_id
	$query = "SELECT ref_id FROM event AS e ".
		"JOIN object_data AS od ON e.obj_id = od.obj_id ".
		"JOIN object_reference AS obr ON od.obj_id = obr.obj_id ".
		"WHERE event_id = ".$ilDB->quote($row->item_id)." ";
	$ref_res = $ilDB->query($query);
	while($ref_row = $ref_res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$event_ref_id = $ref_row->ref_id;
	}

	if($event_ref_id)
	{
		$query = "DELETE FROM ut_lp_collections ".
			"WHERE item_id = ".$ilDB->quote($event_ref_id)." ";
		$ilDB->query($query);

		$query = "INSERT INTO ut_lp_collections ".
			"SET obj_id = ".$ilDB->quote($crs_obj_id).", ".
			"item_id = ".$ilDB->quote($event_ref_id)." ";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
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
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'wiki'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add missing delete rbac operation for wikis
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','6')";
$this->db->query($query);

?>

<#1296>
<?php

// copy permission for wikis
$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$ops_id = $row->ops_id;

$all_types = array('wiki');
foreach($all_types as $type)
{
	$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = '".$type."'";
	$res = $ilDB->query($query);
	$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

	$query = "INSERT INTO rbac_ta SET typ_id = '".$row->obj_id."', ops_id = '".$ops_id."'";
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
if (!$ilDB->tableColumnExists("tst_test_pass_result", "maxpoints"))
{
	$query = "ALTER TABLE tst_test_pass_result ADD COLUMN maxpoints INT NOT NULL DEFAULT 0";
	$res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_test_pass_result", "questioncount"))
{
	$query = "ALTER TABLE tst_test_pass_result ADD COLUMN questioncount INT NOT NULL DEFAULT 0";
	$res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_test_pass_result", "answeredquestions"))
{
	$query = "ALTER TABLE tst_test_pass_result ADD COLUMN answeredquestions INT NOT NULL DEFAULT 0";
	$res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_test_pass_result", "workingtime"))
{
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

	  $query = sprintf("SELECT tst_tests.random_test FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_active.test_fi = tst_tests.test_id",
			$ilDB->quote($active_id . "")
		);
	  $res = $ilDB->query($query);
	  while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
	  {
		  return $row['random_test'];
	  }
	  return 0;
	}

	function getQuestionCountAndPointsForPassOfParticipant($active_id, $pass)
	{
		global $ilDB;
		$random = lookupRandomTestFromActiveId($active_id);
		if ($random)
		{
			$query = sprintf("SELECT tst_test_random_question.pass, COUNT(tst_test_random_question.question_fi) AS qcount, " .
				"SUM(qpl_questions.points) AS qsum FROM tst_test_random_question, qpl_questions " .
				"WHERE tst_test_random_question.question_fi = qpl_questions.question_id AND " .
				"tst_test_random_question.active_fi = %s and pass = %s GROUP BY tst_test_random_question.active_fi, " .
				"tst_test_random_question.pass",
				$ilDB->quote($active_id),
				$ilDB->quote($pass)
			);
		}
		else
		{
			$query = sprintf("SELECT COUNT(tst_test_question.question_fi) AS qcount, " .
				"SUM(qpl_questions.points) AS qsum FROM tst_test_question, qpl_questions, tst_active " .
				"WHERE tst_test_question.question_fi = qpl_questions.question_id AND tst_test_question.test_fi = tst_active.test_fi AND " .
				"tst_active.active_id = %s GROUP BY tst_test_question.test_fi",
				$ilDB->quote($active_id)
			);
		}
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return array("count" => $row["qcount"], "points" => $row["qsum"]);
		}
		else
		{
			return array("count" => 0, "points" => 0);
		}
	}

	function getWorkingTimeOfParticipantForPass($active_id, $pass)
	{
		global $ilDB;

		$query = sprintf("SELECT * FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		$time = 0;
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
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
	if ($result->numRows())
	{
		while ($foundrow = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$active_id = $foundrow["active_fi"];
			$pass = $foundrow["pass"];
			$data = getQuestionCountAndPointsForPassOfParticipant($active_id, $pass);
			$time = getWorkingTimeOfParticipantForPass($active_id, $pass);
			// update test pass results
			$pointquery = sprintf("SELECT SUM(points) AS reachedpoints, COUNT(question_fi) AS answeredquestions FROM tst_test_result WHERE active_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($pass . "")
			);
			$pointresult = $ilDB->query($pointquery);
			if ($pointresult->numRows() > 0)
			{
				$pointrow = $pointresult->fetchRow(MDB2_FETCHMODE_ASSOC);
				$newresultquery = sprintf("REPLACE INTO tst_test_pass_result SET active_fi = %s, pass = %s, points = %s, maxpoints = %s, questioncount = %s, answeredquestions = %s, workingtime = %s",
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
if (!$ilDB->tableColumnExists("tst_tests", "finalstatement"))
{
	$query = "ALTER TABLE `tst_tests` ADD `finalstatement` TEXT NULL";
	$res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_tests", "showfinalstatement"))
{
	$query = "ALTER TABLE `tst_tests` ADD `showfinalstatement` INT NOT NULL DEFAULT '0'";
	$res = $ilDB->query($query);
}
if (!$ilDB->tableColumnExists("tst_tests", "showinfo"))
{
	$query = "ALTER TABLE `tst_tests` ADD `showinfo` INT NOT NULL DEFAULT '1'";
	$res = $ilDB->query($query);
}
?>
<#1305>
<?php
if (!$ilDB->tableColumnExists("tst_tests", "showinfo"))
{
	$query = "ALTER TABLE `tst_tests` ADD `forcejs` INT NOT NULL DEFAULT '0'";
	$res = $ilDB->query($query);
}
?>
<#1306>
<?php
// correct the wrong statement #1305
if (!$ilDB->tableColumnExists("tst_tests", "forcejs"))
{
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
while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	foreach ($classes as $curr_class)
	{
		foreach ($pars as $curr_par)
		{
			$query = "SELECT id FROM style_parameter WHERE style_id='".$row["style_id"]."'".
				" AND tag = 'div' AND class='".$curr_class."' AND parameter = '".$curr_par."'";
			$res2 = $ilDB->query($query);
			if ($row2 = $res2->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$q = "UPDATE style_parameter SET value='10px' WHERE id = '".$row2["id"]."'";
//echo "<br>".$q;
				$ilDB->query($q);
			}
			else
			{
				$q = "INSERT INTO style_parameter (style_id, tag, class, parameter,value) VALUES ".
					"('".$row["style_id"]."','div','".$curr_class."','".$curr_par."','10px')";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	switch($row->sortorder)
	{
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
	$query = "DELETE FROM container_sorting_settings WHERE obj_id = ".$ilDB->quote($row->obj_id)." ";
	$ilDB->query($query);
	
	$query = "INSERT INTO container_sorting_settings SET ".
		"obj_id = ".$ilDB->quote($row->obj_id).", ".
		"sort_mode = ".$ilDB->quote($sort)." ";
	$ilDB->query($query);
}
?>

<#1317>
<?php

$query = "SELECT obr.obj_id AS parent_id,ci.obj_id AS item_id,position FROM crs_items AS ci ".
	"JOIN object_reference AS obr ON ci.parent_id = obr.ref_id ";
$res = $ilDB->query($query);

while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	
	$query = "DELETE FROM container_sorting WHERE ".
		"obj_id = ".$ilDB->quote($row->parent_id)." ".
		"AND child_id = ".$ilDB->quote($row->item_id)." ";
	$ilDB->query($query);
	
	$query = "INSERT INTO container_sorting ".
		"SET obj_id = ".$ilDB->quote($row->parent_id).", ".
		"parent_type = '', ".
		"parent_id = 0, ".
		"child_id = ".$ilDB->quote($row->item_id).", ".
		"position = ".$ilDB->quote($row->position)." ";
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'crsr', 'Course Reference Object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$copy_id = $row->ops_id;


// add operation assignment to link object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete, and copy
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','6')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$copy_id."')";
$this->db->query($query);

// add create operation
$query = "INSERT INTO rbac_operations ".
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

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
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
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'catr', 'Category Reference Object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$copy_id = $row->ops_id;


// add operation assignment to link object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete, and copy
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','6')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$copy_id."')";
$this->db->query($query);

// add create operation
$query = "INSERT INTO rbac_operations ".
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

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$this->db->query($query);

// add create for root
// get root type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='root'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$this->db->query($query);
?>
<#1330>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1331>
<?php
$query = "UPDATE rbac_operations SET class='create' ".
	"WHERE operation = 'create_rcrs'  OR ".
	"operation = 'create_crsr'  OR ".
	"operation = 'create_catr' ";
$ilDB->query($query);
?>

<#1332>
<?php

// Delete deprecated read permission for container references
$query = "SELECT obj_id FROM object_data ".
	"WHERE type = 'typ' AND title = 'catr'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$cat_id = $row[0];

$query = "SELECT obj_id FROM object_data ".
	"WHERE type = 'typ' AND title = 'crsr'";
$res = $ilDB->query($query);
$row = $res->fetchRow();
$crs_id = $row[0];

$query = "DELETE FROM rbac_ta WHERE typ_id = ".$ilDB->quote($cat_id)." ".
	"AND ops_id = 3";
$ilDB->query($query);

$query = "DELETE FROM rbac_ta WHERE typ_id = ".$ilDB->quote($crs_id)." ".
	"AND ops_id = 3";
$ilDB->query($query);
?>

<#1333>
<?php

// Add template permissions to root node for Author and Co-Author template
$query = "SELECT * FROM rbac_operations WHERE operation = 'copy'";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$copy_id = $row->ops_id;

$query = "SELECT * FROM rbac_operations WHERE operation = 'create_catr'";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$create_cat_id = $row->ops_id;

$query = "SELECT * FROM rbac_operations WHERE operation = 'create_crsr'";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$create_crs_id = $row->ops_id;

// get author and co-author obj_ids
$query = "SELECT obj_id FROM object_data ".
	"WHERE type = 'rolt' ".
	"AND title = 'Author' ".
	"OR title = 'Co-Author' ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	foreach(array(1,2,4,6,$copy_id) as $ops_id)
	{
		$query = "DELETE FROM rbac_templates WHERE ".
			"rol_id = ".$ilDB->quote($row->obj_id)." ".
			"AND type = 'catr' ".
			"AND ops_id = ".$ops_id." ".
			"AND parent = 8";
		$ilDB->query($query);
	
		$query = "INSERT INTO rbac_templates SET ".
			"rol_id = ".$ilDB->quote($row->obj_id).", ".
			"type = 'catr', ".
			"ops_id = ".$ops_id.", ".
			"parent = 8";
		$ilDB->query($query);
		
		$query = "DELETE FROM rbac_templates WHERE ".
			"rol_id = ".$ilDB->quote($row->obj_id)." ".
			"AND type = 'crsr' ".
			"AND ops_id = ".$ops_id." ".
			"AND parent = 8";
		$ilDB->query($query);
	
		$query = "INSERT INTO rbac_templates SET ".
			"rol_id = ".$ilDB->quote($row->obj_id).", ".
			"type = 'crsr', ".
			"ops_id = ".$ops_id.", ".
			"parent = 8";
		$ilDB->query($query);
	}
	foreach(array($create_cat_id,$create_crs_id) as $ops_id)
	{
		$query = "DELETE FROM rbac_templates WHERE ".
			"rol_id = ".$ilDB->quote($row->obj_id)." ".
			"AND type = 'cat' ".
			"AND ops_id = ".$ops_id." ".
			"AND parent = 8";
		$ilDB->query($query);
	
		$query = "INSERT INTO rbac_templates SET ".
			"rol_id = ".$ilDB->quote($row->obj_id).", ".
			"type = 'cat', ".
			"ops_id = ".$ops_id.", ".
			"parent = 8";
		$ilDB->query($query);
	}

	$query = "DELETE FROM rbac_templates WHERE ".
		"rol_id = ".$ilDB->quote($row->obj_id)." ".
		"AND type = 'root' ".
		"AND ops_id = ".$create_cat_id." ".
		"AND parent = 8";
	$ilDB->query($query);
	
	$query = "INSERT INTO rbac_templates SET ".
		"rol_id = ".$ilDB->quote($row->obj_id).", ".
		"type = 'root', ".
		"ops_id = ".$create_cat_id.", ".
		"parent = 8";
	$ilDB->query($query);
}
?>
<#1334>
<?php

// register new object type 'tags' for tagging settings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'tags', 'Tagging settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('tags', '__TaggingSettings', 'Tagging Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'tags'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations for tagging settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
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
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$start = gmdate('Y-m-d H:i:s',$row->begin);
	$end = gmdate('Y-m-d H:i:s',$row->end);
	$changed = gmdate('Y-m-d H:i:s',$row->changed);
	
	
	$query = "INSERT INTO cal_entries ".
		"SET last_update = ".$ilDB->quote($changed).", ".
		"title = ".$ilDB->quote($row->shorttext).", ".
		"description = ".$ilDB->quote($row->text).", ".
		"start = ".$ilDB->quote($start).', '.
		"end = ".$ilDB->quote($end)." ";
	$ilDB->query($query);
	
	$cal_id = $ilDB->getLastInsertId();
	
	$until = $row->end_rotation;
	switch($row->rotation)
	{
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
	
	if($freq)
	{
		$until = gmdate('Y-m-d H:i:s',$until);
		$query = "INSERT INTO cal_recurrence_rules ".
			"SET cal_id = ".$ilDB->quote($cal_id).", ".
			"cal_recurrence = 1, ".
			"freq_type = ".$ilDB->quote($freq).", ".
			"intervall = ".$ilDB->quote($int).", ".
			"freq_until_date = ".$ilDB->quote($until).", ".
			"freq_until_count = 30";
		$ilDB->query($query);
	}
	
	if($row->group_ID)
	{
		$query = "SELECT obj_id FROM object_reference ".
			"WHERE ref_id = ".$ilDB->quote($row->group_ID);
		$ref_res = $ilDB->query($query);
		$ref_row = $ref_res->fetchRow();
		$obj_id = $ref_row[0];
		
		// SELECT category id of course/group
		$query = "SELECT * FROM cal_categories WHERE obj_id = ".$ilDB->quote($obj_id);
		$cat_res = $ilDB->query($query);
		$cat_row = $cat_res->fetchRow();
		$cat_id = $cat_row[0];
		
		// INSERT INTO course/group calendar
		$query = "INSERT INTO cal_category_assignments ".
			"SET cal_id = ".$ilDB->quote($cal_id).", ".
			"cat_id = ".$ilDB->quote($cat_id);
		$ilDB->query($query);
	}
	else
	{
		$user_id = $row->user_ID;
		
		if(isset($user_ids[$user_id]))
		{
			$cat_id = $user_ids[$user_id];
		}
		else
		{
			// This is a personal calendar
			$query = "INSERT INTO cal_categories ".
				"SET obj_id = ".$ilDB->quote($user_id).", ".
				"title = 'Personal Calendar', ".
				"color = '#DAE2FF', ".
				"type = 1";
			$ilDB->query($query);
			
			// SELECT category id
			$query = "SELECT cat_id FROM cal_categories ".
				"WHERE obj_id = ".$ilDB->quote($user_id).
				"AND title = 'Personal Calendar'";
			$cat_res = $ilDB->query($query);
			$cat_row = $cat_res->fetchRow();
			$cat_id = $cat_row[0];
			
			$user_ids[$user_id] = $cat_id;
		}
		
		// INSERT INTO personal calendar
		$query = "INSERT INTO cal_category_assignments ".
			"SET cal_id = ".$ilDB->quote($cal_id).", ".
			"cat_id = ".$ilDB->quote($cat_id);
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
		."  (".$ilDB->quote($typ_id).",'".$ops_id."')";
$ilDB->query($query);

$ops_id = 4;
$query = "SELECT rol_id,obr.ref_id FROM object_data obd ".
	"JOIN object_reference obr ON obd.obj_id = obr.obj_id ".
	"JOIN rbac_pa  ON obr.ref_id = rbac_pa.ref_id ".
	"WHERE type = 'sess' ".
	"AND (ops_id LIKE ".$ilDB->quote("%i:".$ops_id."%"). " ".
	"OR ops_id LIKE".$ilDB->quote("%:\"".$ops_id."\";%").") ";

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ref_id = $row->ref_id;
	$rol_id = $row->rol_id;
	
	$query = "SELECT * FROM rbac_pa WHERE rol_id = ".$ilDB->quote($rol_id).' '.
		"AND ref_id = ".$ilDB->quote($ref_id);
	$pa_res = $ilDB->query($query);
	while($pa_row = $pa_res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$ops = unserialize($pa_row->ops_id);
		
		if(!in_array(6,$ops))
		{
			$ops[] = 6;
			
			$query = "UPDATE rbac_pa SET ".
				"ops_id = ".$ilDB->quote($ops).' '.
				"WHERE rol_id = ".$ilDB->quote($rol_id).' '.
				"AND ref_id = ".$ilDB->quote($ref_id);
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
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$copy_id = $row->ops_id;


$ops_id = 4;
$query = "SELECT rol_id,obr.ref_id FROM object_data obd ".
	"JOIN object_reference obr ON obd.obj_id = obr.obj_id ".
	"JOIN rbac_pa  ON obr.ref_id = rbac_pa.ref_id ".
	"WHERE type = 'sess' ".
	"AND (ops_id LIKE ".$ilDB->quote("%i:".$ops_id."%"). " ".
	"OR ops_id LIKE".$ilDB->quote("%:\"".$ops_id."\";%").") ";

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ref_id = $row->ref_id;
	$rol_id = $row->rol_id;
	
	$query = "SELECT * FROM rbac_pa WHERE rol_id = ".$ilDB->quote($rol_id).' '.
		"AND ref_id = ".$ilDB->quote($ref_id);
	$pa_res = $ilDB->query($query);
	while($pa_row = $pa_res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$ops = unserialize($pa_row->ops_id);
		
		if(!in_array($copy_id,$ops))
		{
			$ops[] = $copy_id;
			
			$query = "UPDATE rbac_pa SET ".
				"ops_id = ".$ilDB->quote($ops).' '.
				"WHERE rol_id = ".$ilDB->quote($rol_id).' '.
				"AND ref_id = ".$ilDB->quote($ref_id);
			$ilDB->query($query);
		}
	}
}
?>

<#1344>
<?php
// Adjust role template permission for sessions

$query = "SELECT * FROM rbac_templates ".
	"WHERE type = 'sess' ".
	"AND ops_id = ".$ilDB->quote(4);

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "DELETE FROM rbac_templates ".
		"WHERE rol_id = ".$ilDB->quote($row->rol_id)." AND ".
		"type = 'sess' AND ".
		"ops_id = ".$ilDB->quote(6)." AND ".
		"parent = ".$ilDB->quote($row->parent)." ";
	$ilDB->query($query);
	
	$query = "INSERT INTO rbac_templates ".
		"SET rol_id = ".$ilDB->quote($row->rol_id).", ".
		"type = 'sess', ".
		"ops_id = ".$ilDB->quote(6).", ".
		"parent = ".$ilDB->quote($row->parent)." ";
	$ilDB->query($query);
}
?>

<#1345>
<?php

$query = "SELECT * FROM rbac_operations WHERE operation = 'create_sess'";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$create_id = $row->ops_id;


$query = "SELECT * FROM rbac_templates ".
	"WHERE type = 'crs' ".
	"AND ops_id = ".$ilDB->quote(4);

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "DELETE FROM rbac_templates ".
		"WHERE rol_id = ".$ilDB->quote($row->rol_id)." AND ".
		"type = 'crs' AND ".
		"ops_id = ".$ilDB->quote($create_id)." AND ".
		"parent = ".$ilDB->quote($row->parent)." ";
	$ilDB->query($query);
	
	$query = "INSERT INTO rbac_templates ".
		"SET rol_id = ".$ilDB->quote($row->rol_id).", ".
		"type = 'crs', ".
		"ops_id = ".$ilDB->quote($create_id).", ".
		"parent = ".$ilDB->quote($row->parent)." ";
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
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$sess_id = $row->ops_id;


$ops_id = 4;
$query = "SELECT rol_id,obr.ref_id FROM object_data obd ".
	"JOIN object_reference obr ON obd.obj_id = obr.obj_id ".
	"JOIN rbac_pa  ON obr.ref_id = rbac_pa.ref_id ".
	"WHERE type = 'crs' ".
	"AND (ops_id LIKE ".$ilDB->quote("%i:".$ops_id."%"). " ".
	"OR ops_id LIKE".$ilDB->quote("%:\"".$ops_id."\";%").") ";

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ref_id = $row->ref_id;
	$rol_id = $row->rol_id;
	
	$query = "SELECT * FROM rbac_pa WHERE rol_id = ".$ilDB->quote($rol_id).' '.
		"AND ref_id = ".$ilDB->quote($ref_id);
	$pa_res = $ilDB->query($query);
	while($pa_row = $pa_res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$ops = unserialize($pa_row->ops_id);
		
		if(!in_array($copy_id,$ops))
		{
			$ops[] = $sess_id;
			
			$query = "UPDATE rbac_pa SET ".
				"ops_id = ".$ilDB->quote($ops).' '.
				"WHERE rol_id = ".$ilDB->quote($rol_id).' '.
				"AND ref_id = ".$ilDB->quote($ref_id);
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
if (!$ilDB->tableColumnExists("tst_tests", "customstyle"))
{
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

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'tax'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$typ_id = $row["obj_id"];
if ($typ_id > 0)
{
	$q = "DELETE FROM rbac_ta WHERE typ_id = ".$ilDB->quote($typ_id);
	$ilDB->query($q);
	$q = "DELETE FROM object_data WHERE obj_id = ".$ilDB->quote($typ_id);
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
if (!$ilDB->tableColumnExists("file_usage", "usage_hist_nr"))
{
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
	if ($res->numRows() == 0)
	{
		$query = "INSERT INTO qpl_question_type (type_tag, plugin) VALUES ('assFlashQuestion', '0')";
		$ilDB->query($query);
	}
?>
<#1364>
<?php
$q = "CREATE TABLE IF NOT EXISTS qpl_question_flash ( ".
" `question_fi` INT NOT NULL PRIMARY KEY, ".
" `params` TEXT NULL ".
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

$query = "SELECT ta.ops_id from rbac_ta ta ".
	"LEFT JOIN rbac_operations op ON ta.ops_id = op.ops_id ".
	"WHERE op.ops_id IS NULL";

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "DELETE FROM rbac_ta WHERE ops_id = ".$ilDB->quote($row->ops_id);
	$ilDB->query($query);
}
?>
<#1368>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1369>
<?php
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'cert'";
$res = $this->db->query($query);
if ($res->numRows() == 0)
{
	// register new object type 'cert' for certificate settings
	$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
			"VALUES ('typ', 'cert', 'Certificate settings', -1, now(), now())";
	$this->db->query($query);

	// ADD NODE IN SYSTEM SETTINGS FOLDER
	// create object data entry
	$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
			"VALUES ('cert', '__CertificateSettings', 'Certificate Settings', -1, now(), now())";
	$this->db->query($query);

	$query = "SELECT LAST_INSERT_ID() as id";
	$res = $this->db->query($query);
	$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

	// create object reference entry
	$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
	$res = $this->db->query($query);

	$query = "SELECT LAST_INSERT_ID() as id";
	$res = $this->db->query($query);
	$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

	// put in tree
	$tree = new ilTree(ROOT_FOLDER_ID);
	$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

	$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
		" AND title = 'cert'";
	$res = $this->db->query($query);
	$row = $res->fetchRow();
	$typ_id = $row[0];

	// add rbac operations for certificate settings
	// 1: edit_permissions, 2: visible, 3: read, 4:write
	$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
	$this->db->query($query);
	$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
	$this->db->query($query);
	$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
	$this->db->query($query);
	$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
	$this->db->query($query);
}
?>
<#1370>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1371>
<?php
// add 'manual' field in tst_test_result table to indicate manual scoring
if (!$ilDB->tableColumnExists("tst_test_result", "manual"))
{
	$query = "ALTER TABLE `tst_test_result` ADD `manual` TINYINT NOT NULL DEFAULT '0'";
	$res = $ilDB->query($query);
}
?>
<#1372>
<?php
// register new object type 'lrss' for learning resource settings
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'lrss', 'Learning resources settings', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('lrss', '__LearningResourcesSettings', 'Learning Resources Settings', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'lrss'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add rbac operations to news settings
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
$this->db->query($query);

?>
<#1373>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1374>
<?php
	// add activation start/end fields
	$ilDB->alterTable("lm_data",
		array("add" => array(
			"activation_start" => array(
				"type" => "timestamp", "default" => "0000-00-00 00:00:00", "notnull" => true),
			"activation_end" => array(
				"type" => "timestamp", "default" => "0000-00-00 00:00:00", "notnull" => true)
			)
		));
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
	$ilDB->alterTable("lm_data",
		array("remove" => array("activation_start" => array(), "activation_end" => array())
			));
?>
<#1378>
<?php
	// add activation start/end fields
	if (!$ilDB->tableColumnExists("page_object", "activation_start"))
	{
		$ilDB->alterTable("page_object",
			array("add" => array(
				"activation_start" => array(
					"type" => "timestamp", "default" => "0000-00-00 00:00:00", "notnull" => true),
				"activation_end" => array(
					"type" => "timestamp", "default" => "0000-00-00 00:00:00", "notnull" => true)
				)
			));
	}
?>
<#1379>
<?php
	// step move to 1380
?>
<#1380>
<?php
	if (!$ilDB->tableColumnExists("page_object", "active"))
	{
		$ilDB->alterTable("page_object",
			array("add" => array(
				"active" => array(
					"type" => "boolean", "default" => true, "notnull" => true)
				)
			));
			
		$st = $ilDB->prepare("SELECT * FROM lm_data WHERE type = ?", array("text"));
		$res = $ilDB->execute($st, array("pg"));
	
		while ($rec = $ilDB->fetchAssoc($res))
		{
			$st2 = $ilDB->prepareManip("UPDATE page_object SET active = ? WHERE ".
				"page_id = ? AND (parent_type = ? OR parent_type = ?) AND parent_id = ?",
				array("boolean", "integer", "text", "text", "integer"));
			$ilDB->execute($st2, array(($rec["active"]!="n"), $rec["obj_id"], "lm", "dbk", $rec["lm_id"]));
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
	if (!$ilDB->tableExists("style_char"))
	{
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
	if (!$ilDB->tableColumnExists("style_parameter", "type"))
	{
		$q = "ALTER TABLE `style_parameter` ADD COLUMN `type` varchar(30) NOT NULL default ''";
			$ilDB->query($q);
	}
?>
<#1385>
<?php
	include_once("./Services/Migration/DBUpdate_1385/classes/class.ilStyleMigration.php");
	ilStyleMigration::addMissingStyleCharacteristics();
?>
<#1386>
<?php
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
?>
<#1387>
<?php

	// force rewriting of page container style
	$q = "DELETE FROM `style_char` WHERE type = 'page_cont'";
	$ilDB->query($q);
	$q = "DELETE FROM `style_parameter` WHERE type = 'page_cont'";
	$ilDB->query($q);

	include_once("./Services/Migration/DBUpdate_1385/classes/class.ilStyleMigration.php");
	ilStyleMigration::_addMissingStyleClassesToAllStyles();
?>
<#1388>
UPDATE `style_data` SET `uptodate` = 0;
<#1389>
<?php
	$q = "UPDATE `style_char` SET characteristic = 'TextInput' WHERE type = 'qinput'";
	$ilDB->query($q);
	$q = "UPDATE `style_parameter` SET class = 'TextInput' WHERE type = 'qinput'";
	$ilDB->query($q);

	// add LongTextInput
	$sts = $ilDB->prepare("SELECT * FROM object_data WHERE type = 'sty'");
	$sets = $ilDB->execute($sts);
	
	while ($recs = $ilDB->fetchAssoc($sets))
	{
		$id = $recs["obj_id"];
		
		$st = $ilDB->prepare("SELECT * FROM style_char WHERE type = ? AND style_id = ?",
			array("text", "integer"));
		$set = $ilDB->execute($st, array("qlinput", $id));
		if (!($rec = $ilDB->fetchAssoc($set)))
		{
			$q = "INSERT INTO `style_char` (style_id, type, characteristic) VALUES ".
				"(".$ilDB->quote($id).",".$ilDB->quote("qlinput").",".$ilDB->quote("LongTextInput").")";
			$ilDB->query($q);
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
	if (!$ilDB->tableExists("page_anchor"))
	{
		$ilDB->createTable("page_anchor",
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
if(!$ilDB->tableColumnExists("usr_data", "im_jabber"))
{
	$ilDB->alterTable("usr_data",
		array("add" => array(
			"im_jabber" => array(
				"type" => "text", "length" => 40, "fixed" => false)
			)
		));
}
if(!$ilDB->tableColumnExists("usr_data", "im_voip"))
{
	$ilDB->alterTable("usr_data",
		array("add" => array(
			"im_voip" => array(
				"type" => "text", "length" => 40, "fixed" => false)
			)
		));
}
?>
<#1394>
<?php
if(!$ilDB->tableColumnExists("qpl_suggested_solutions", "type"))
{
	$ilDB->alterTable("qpl_suggested_solutions",
		array("add" => array(
			"type" => array(
				"type" => "text", "length" => 32, "notnull" => true),
			"value" => array(
				"type" => "text", "notnull" => false)
			)
		));
}
?>
<#1395>
<?php
$statement = $ilDB->prepare("SELECT * FROM qpl_suggested_solutions");
$result = $ilDB->execute($statement);
if ($result->numRows() > 0)
{
	while ($data = $ilDB->fetchAssoc($result))
	{
		if (strlen($data["tpye"]) == 0)
		{
			if (preg_match("/il_+(\\w+)_+\\d+/", $data["internal_link"], $matches))
			{
				$updatestatement = $ilDB->prepareManip("UPDATE qpl_suggested_solutions SET type = ? WHERE suggested_solution_id = ?",
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

if(!$ilDB->tableColumnExists('usr_search','query'))
{
	$ilDB->alterTable('usr_search',array('add'	=> array('query' => array('type' => 'text',
																		'notnull'	=> false))));
}
?>
<#1397>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#1398>
<?php
if (!$ilDB->tableColumnExists("qpl_question_matching", "thumb_geometry"))
{
	$ilDB->alterTable('qpl_question_matching',array('add'	=> array('thumb_geometry' => array('type' => 'integer','notnull'	=> true, "default" => 100))));
}
?>
<#1399>
<?php
if (!$ilDB->tableColumnExists("qpl_question_matching", "element_height"))
{
	$ilDB->alterTable('qpl_question_matching',array('add'	=> array('element_height' => array('type' => 'integer','notnull'	=> false))));
}
?>
<#1400>
<?php
if(!$ilDB->tableColumnExists('usr_search','root'))
{
	$ilDB->alterTable('usr_search',array('add'	=> array('root' => array('type' => 'integer',
																		'notnull'	=> false,
																		'default'	=> ROOT_FOLDER_ID))));
}
?>
<#1401>
<?php
	if (!$ilDB->tableExists("loginname_history"))
	{
		$ilDB->createTable("loginname_history",
			array(
				"usr_id" => array("type" => "integer", "length" => 4, "notnull" => true),
				"login" => array("type" => "text", "length" => 80, "fixed" => false, "notnull" => true),
				"date" => array("type" => "integer", "length" => 4, "notnull" => true)
			)
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
if ($ilDB->tableColumnExists('qpl_answer_cloze','name')) $ilDB->query("ALTER TABLE qpl_answer_cloze DROP `name`");
?>
<#1408>
<?php
if ($ilDB->tableColumnExists('qpl_answer_cloze','correctness')) $ilDB->query("ALTER TABLE qpl_answer_cloze DROP `correctness`");
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
if ($ilDB->tableColumnExists('qpl_answer_matching','answertext'))
{
	$ilDB->query("ALTER TABLE qpl_answer_matching CHANGE `answertext` `term_fi` INT NOT NULL DEFAULT  0");
} 
?>
<#1413>
<?php
if ($ilDB->tableColumnExists('qpl_answer_matching','aorder')) $ilDB->query("ALTER TABLE qpl_answer_matching DROP `aorder`");
?>
<#1414>
<?php
$ilDB->query("ALTER TABLE qpl_answer_matching MODIFY `matching_order` SMALLINT NOT NULL DEFAULT 0");
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
if ($ilDB->tableExists('qpl_question_flashapp'))
{
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
if ($ilDB->tableExists('tst_eval_settings'))
{
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
while ($data = $ilDB->fetchAssoc($result))
{
	if (strpos($data['Key_name'], 'active_fi_') !== FALSE)
	{
		$statement = $ilDB->prepareManip("ALTER TABLE tst_test_result DROP INDEX " . $data['Key_name']);
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
	if ($res->numRows() == 0)
	{
		$query = "SELECT MAX(question_type_id) FROM qpl_question_type";
		$res = $ilDB->query($query);
		$data = $ilDB->fetchAssoc($res);
		$max = current($data) + 1;

		$statement = $ilDB->prepareManip("INSERT INTO qpl_question_type (question_type_id, type_tag, plugin) VALUES (?, ?, ?)", 
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
	if (!$ilDB->tableExists("qpl_question_orderinghorizontal"))
	{
		$ilDB->createTable("qpl_question_orderinghorizontal",
			array(
				"question_fi" => array(
					"type" => "integer", "length" => 4, "notnull" => true),
				"ordertext" => array(
					"type" => "text", "length" => 2000, "notnull" => true),
				"textsize" => array(
					"type" => "float", "notnull" => false)
				)
			);
		$ilDB->addPrimaryKey("qpl_question_orderinghorizontal", array("question_fi"));
	}
?>
<#1528>
<?php
if (!$ilDB->tableColumnExists("qpl_answer_ordering", "random_id"))
{
	$query = "ALTER TABLE qpl_answer_ordering ADD COLUMN random_id INT NOT NULL DEFAULT 0";
	$res = $ilDB->query($query);
}
?>
<#1529>
<?php
$statement = $ilDB->prepare("SELECT * FROM qpl_answer_ordering");
$result = $ilDB->execute($statement);
while ($data = $ilDB->fetchAssoc($result))
{
	$statement = $ilDB->prepareManip("UPDATE qpl_answer_ordering SET random_id = ? WHERE answer_id = ?", array("integer", "integer"));
	$random_number = mt_rand(1, 100000);
	$values = array($random_number, $data["answer_id"]);
	$ilDB->execute($statement, $values);
}
?>
<#1530>
<?php
if (!$ilDB->tableColumnExists("qpl_question_ordering", "thumb_geometry"))
{
	$ilDB->query("ALTER TABLE qpl_question_ordering ADD COLUMN thumb_geometry INT NOT NULL DEFAULT 100");
}
?>
<#1531>
<?php
if (!$ilDB->tableColumnExists("qpl_question_ordering", "element_height"))
{
	$ilDB->query("ALTER TABLE qpl_question_ordering ADD COLUMN element_height INT NULL");
}
?>
<#1532>
<?php
if (!$ilDB->tableColumnExists("qpl_answer_ordering", "points"))
{
	$ilDB->query("ALTER TABLE qpl_answer_ordering DROP points");
}
?>
<#1533>
<?php
if (!$ilDB->tableColumnExists("qpl_answer_ordering", "aorder"))
{
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
if ($res->numRows() == 0)
{
	$query = "SELECT MAX(question_type_id) FROM qpl_question_type";
	$res = $ilDB->query($query);
	$data = $ilDB->fetchAssoc($res);
	$max = current($data) + 1;
	$statement = $ilDB->prepareManip("INSERT INTO qpl_question_type (question_type_id, type_tag, plugin) VALUES (?, ?, ?)", 
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
	if (!$ilDB->tableExists("qpl_question_fileupload"))
	{
		$ilDB->createTable("qpl_question_fileupload",
			array(
				"question_fi" => array(
					"type" => "integer", "length" => 4, "notnull" => true),
				"allowedextensions" => array(
					"type" => "text", "length" => 255, "notnull" => false),
				"maxsize" => array(
					"type" => "float", "notnull" => false)
				)
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
if ($res->numRows() == 1)
{
	$data = $ilDB->fetchAssoc($res);
	if ($data['question_type_id'] == 0)
	{
		$query = "SELECT MAX(question_type_id) FROM qpl_question_type";
		$res = $ilDB->query($query);
		$data = $ilDB->fetchAssoc($res);
		$max = current($data) + 1;
		$statement = $ilDB->prepareManip("UPDATE qpl_question_type SET question_type_id = ? WHERE type_tag = ? AND plugin = ?", 
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
if ($res->numRows() == 1)
{
	$row = $ilDB->fetchAssoc($res);
	$id = $row["question_type_id"];

	$setting = new ilSetting("assessment");
	$types = $setting->get("assessment_manual_scoring");
	$manualtypes = explode(",", $types);
	if (!in_array($id, $manualtypes))
	{
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

// Convert read_event last_access to unixtime for faster updates of user entries
$query = "SELECT *,UNIX_TIMESTAMP(last_access) ut FROM read_event ";
$res = $ilDB->query($query);
$events = array();
while($row = $ilDB->fetchAssoc($res))
{
	$events[] = $row;
}

// Convert table 
$query = 'ALTER TABLE `read_event` CHANGE `last_access` `last_access` INT';
$ilDB->query($query);

// Update existing values
foreach($events as $event)
{
	$query = 'UPDATE read_event '.
		'SET last_access = '.$ilDB->quote($event['ut']).' '.
		'WHERE obj_id = '.$ilDB->quote($event['obj_id']).' '.
		'AND usr_id = '.$ilDB->quote($event['usr_id']);
	$ilDB->query($query);
}
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
	$ilMySQLAbstraction->performAbstraction('object_description');
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
	$ilDB->addPrimaryKey('role_desktop_items',array('role_item_id'));
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
	$set = $ilDB->query("SELECT DISTINCT table_name FROM abstraction_progress WHERE step = ".
		$ilDB->quote(80, "integer"));
	while ($rec = $ilDB->fetchAssoc($set))
	{
		$ilMySQLAbstraction->fixIndexNames($rec["table_name"]);
	}
?>

<#1681>
<?php
	$ilDB->renameTable('ldap_role_group_mapping','ldap_rg_mapping');
?>

<#1682>
<?php
	$ilDB->renameTable('ldap_role_group_mapping_seq','ldap_rg_mapping_seq');
?>

<#1683>
RENAME TABLE `crs_defined_field_definitions` TO `crs_f_definitions`;


<#1684>
<?php
	$ilMySQLAbstraction->performAbstraction('crs_f_definitions');
?>
	
<#1685>
<?php
	$ilDB->modifyTableColumn('usr_data','hobby', array("type" => "text", "length" => 4000));
?>
<#1686>
<?php
	$set = $ilDB->query("SELECT DISTINCT table_name FROM abstraction_progress WHERE step = ".
		$ilDB->quote(80, "integer"));
	while ($rec = $ilDB->fetchAssoc($set))
	{
		$ilMySQLAbstraction->fixClobNotNull($rec["table_name"]);
	}
?>
<#1687>
<?php
	$ilMySQLAbstraction->performAbstraction('crs_file');
?>

<#1688>
<?php
	$ilDB->modifyTableColumn('lng_data','value', array("type" => "text", "notnull" => false, "length" => 4000));
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
	$set = $ilDB->query("SELECT DISTINCT table_name FROM abstraction_progress WHERE step = ".
		$ilDB->quote(80, "integer"));
	while ($rec = $ilDB->fetchAssoc($set))
	{
		$ilMySQLAbstraction->fixDatetimeValues($rec["table_name"]);
		$ilMySQLAbstraction->replaceEmptyDatesWithNull($rec["table_name"]);
	}
?>

