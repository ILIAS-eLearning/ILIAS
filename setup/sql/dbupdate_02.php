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
chdir('..');

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

chdir($wd);
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
chdir('..');

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

chdir($wd);
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
chdir('..');

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

chdir($wd);
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
chdir('..');

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
chdir($wd);
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
chdir('..');

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

chdir($wd);
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
chdir('..');

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

chdir($wd);
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
chdir('..');

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

chdir($wd);
?>
<#1031>
DROP TABLE IF EXISTS tmp_migration;

<#1032>
<?php
$wd = getcwd();
chdir('..');


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
chdir($wd);
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
		for ($i = 0; $i < $row["tries"]; $i++)
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
if ($ilDB->isError($r) || $ilDB->isError($r->result))
{
	return 'could\'nt create table "dav_lock": '.
	(($ilDB->isError($r->result)) ? $r->result->getMessage() : $r->getMessage());
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
if ($ilDB->isError($r) || $ilDB->isError($r->result))
{
	return 'could\'nt create table "dav_property": '.
	(($ilDB->isError($r->result)) ? $r->result->getMessage() : $r->getMessage());
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
CREATE TABLE IF NOT EXISTS `cal_entries` (
  `cal_id` int(11) NOT NULL auto_increment,
  `title` text collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  `location` text collate utf8_unicode_ci NOT NULL,
  `fullday` tinyint(1) NOT NULL default '0',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `informations` text collate utf8_unicode_ci NOT NULL,
  `public` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`cal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

<#1173>
CREATE TABLE IF NOT EXISTS `cal_recurrence_rules` (
  `rule_id` int(11) NOT NULL auto_increment,
  `cal_id` int(11) NOT NULL default '0',
  `cal_recurrence` int(1) NOT NULL default '0',
  `freq_type` varchar(20) collate utf8_unicode_ci NOT NULL default '',
  `freq_until_date` date NOT NULL default '0000-00-00',
  `freq_until_time` time NOT NULL default '00:00:00',
  `freq_until_count` int(4) NOT NULL default '0',
  `intervall` int(4) NOT NULL default '0',
  `byday` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `byweekno` int(3) NOT NULL default '0',
  `bymonth` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `bymonthday` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `byyearday` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `bysetpos` int(3) NOT NULL default '0',
  `weekstart` varchar(2) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`rule_id`),
  KEY `cal_id` (`cal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cal_recurrence_rules`
  ADD CONSTRAINT `cal_recurrence_rules_ibfk_1` FOREIGN KEY (`cal_id`) REFERENCES `cal_entries` (`cal_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

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
VALUES ('file_access', 'inline_file_extensions', 'gif jpg jpeg mp3 mp4 m4a m4v pdf png swf<');

<#1178>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#1179>
<?php
$ilCtrlStructureReader->getStructure();
?>
