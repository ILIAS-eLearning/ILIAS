<#1>
CREATE TABLE glossary_term
(
	id			INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	glo_id		INT NOT NULL,
	term		VARCHAR(200),
	language	CHAR(2),
	INDEX glo_id (glo_id)
);

<#2>
CREATE TABLE glossary_definition
(
	id			INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	term_id		INT NOT NULL,
	page_id		INT NOT NULL
);

<#3>
CREATE TABLE desktop_item
(
	user_id		INT NOT NULL,
	item_id		INT NOT NULL,
	type		CHAR(4) NOT NULL,
	INDEX user_id (user_id)
);

<#4>
UPDATE object_data SET title = 'ILIAS' WHERE title = 'ILIAS open source';

<#5>
REPLACE INTO lm_data (obj_id, title, type, lm_id) VALUES (1, 'dummy', 'du', 0);

<#6>
UPDATE role_data SET allow_register = 1 WHERE role_id = 5;

<#7>
REPLACE INTO settings (keyword, value) VALUES ('enable_registration', 1);

<#8>
REPLACE INTO settings (keyword, value) VALUES ('system_role_id', '2');

<#9>
DELETE FROM rbac_pa WHERE rol_id = 2;
DELETE FROM rbac_templates WHERE rol_id = 2;
DELETE FROM rbac_fa WHERE rol_id = 2 AND parent != 8;

<#10>
RENAME TABLE lm_page_object TO page_object;
ALTER TABLE page_object DROP PRIMARY KEY;
ALTER TABLE page_object MODIFY parent_type VARCHAR(4) NOT NULL DEFAULT 'lm';
ALTER TABLE page_object ADD PRIMARY KEY (page_id, parent_type);

<#11>
ALTER TABLE glossary_definition DROP COLUMN page_id;
ALTER TABLE glossary_definition ADD COLUMN short_text VARCHAR(200) NOT NULL DEFAULT '';

<#12>
UPDATE settings SET value = '3.0.0_alpha5' WHERE keyword = 'ilias_version' LIMIT 1;

<#13>
DROP TABLE IF EXISTS object_translation;
CREATE TABLE object_translation (
 obj_id int(11) NOT NULL default '0',
 title char(70) NOT NULL default '',
 description char(128) default NULL,
 lang_code char(2) NOT NULL default '',
 lang_default tinyint(1) NOT NULL default '0',
 PRIMARY KEY  (obj_id,lang_code)
) TYPE=MyISAM;

<#14>
CREATE TABLE cal_appointment (
  appointmentId int(11) NOT NULL auto_increment,
  appointmentUnionId int(11) NOT NULL default '0',
  categoryId int(11) NOT NULL default '0',
  priorityId int(11) NOT NULL default '0',
  access varchar(15) NOT NULL default '',
  description text,
  duration int(11) NOT NULL default '0',
  startTimestamp bigint(14) NOT NULL default '0',
  term varchar(128) NOT NULL default '',
  location varchar(80) default NULL,
  serial tinyint(1) unsigned NOT NULL default '0',
  ownerId int(11) unsigned NOT NULL default '0',
  userId int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (appointmentId)
) TYPE=MyISAM;

<#15>
CREATE TABLE cal_appointmentrepeats (
  appointmentRepeatsId int(11) NOT NULL auto_increment,
  appointmentId int(11) NOT NULL default '0',
  endTimestamp int(14) default NULL,
  type varchar(15) NOT NULL default '',
  weekdays varchar(7) NOT NULL default 'nnnnnnn',
  PRIMARY KEY  (appointmentRepeatsId)
) TYPE=MyISAM;


<#16>
CREATE TABLE cal_appointmentrepeatsnot (
  appointmentRepeatsNotId int(11) NOT NULL auto_increment,
  appointmentRepeatsId int(11) NOT NULL default '0',
  leaveOutTimestamp int(14) default NULL,
  PRIMARY KEY  (appointmentRepeatsNotId)
) TYPE=MyISAM;

<#17>
CREATE TABLE cal_category (
  categoryId int(11) NOT NULL auto_increment,
  description text,
  term varchar(20) NOT NULL default '',
  PRIMARY KEY  (categoryId)
) TYPE=MyISAM;


<#18>
CREATE TABLE cal_priority (
  priorityId int(11) NOT NULL auto_increment,
  description text,
  term varchar(20) NOT NULL default '',
  PRIMARY KEY  (priorityId)
) TYPE=MyISAM;


<#19>
CREATE TABLE cal_user_group (
  groupId int(11) NOT NULL default '0',
  userId int(11) NOT NULL default '0',
  description text,
  PRIMARY KEY  (groupId,userId)
) TYPE=MyISAM;

<#20>
CREATE TABLE dummy_groups (
  groupId int(11) NOT NULL auto_increment,
  description text,
  owner varchar(20) NOT NULL default '',
  term varchar(20) NOT NULL default '',
  PRIMARY KEY  (groupId)
) TYPE=MyISAM;

<#21>
DELETE FROM glossary_definition;
DELETE FROM meta_data WHERE obj_type='gdf';
DELETE FROM page_object WHERE parent_type='gdf';
ALTER TABLE glossary_definition ADD COLUMN nr INT NOT NULL;

<#22>
CREATE TABLE usr_search (
usr_id INT NOT NULL ,
search_result TEXT,
PRIMARY KEY ( usr_id )
);

<#23>
ALTER TABLE lm_data ADD COLUMN import_id CHAR(50) NOT NULL DEFAULT '';

<#24>
ALTER TABLE object_data ADD COLUMN import_id CHAR(50) NOT NULL DEFAULT '';

<#25>
ALTER TABLE glossary_term ADD COLUMN import_id CHAR(50) NOT NULL DEFAULT '';

<#26>
ALTER TABLE desktop_item ADD COLUMN parameters VARCHAR(200);

<#27>
CREATE TABLE personal_clipboard
(
	user_id		INT NOT NULL,
	item_id		INT NOT NULL,
	type		CHAR(4) NOT NULL,
	title		CHAR(70) NOT NULL DEFAULT '',
	PRIMARY KEY (user_id, item_id, type)
);

<#28>
INSERT INTO cal_priority (priorityId, description, term) VALUES ('1','high','high');
INSERT INTO cal_priority (priorityId, description, term) VALUES ('2','middle','middle');
INSERT INTO cal_priority (priorityId, description, term) VALUES ('3','low','low');
INSERT INTO cal_category (categoryId, description, term) VALUES ('1','test','test');

<#29>
ALTER TABLE page_object CHANGE content content TEXT NOT NULL;
ALTER TABLE page_object ADD FULLTEXT ( content );

<#30>
CREATE TABLE dbk_translations
(
	id			int(11) NOT NULL,
	tr_id		int(11) NOT NULL,
	PRIMARY KEY (id,tr_id)
);

<#31>
<?php
// 1. replace all '-1' values in ref_id
$q = "SELECT * FROM grp_tree WHERE ref_id = '-1'";
$res = $this->db->query($q);

$grp_data = array();

while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$grp_data[] = $row;
}

if (count($grp_data) > 0)
{
	foreach ($grp_data as $entry)
	{
		$q = "INSERT INTO object_reference ".
			 "(ref_id,obj_id) VALUES (0,'".$entry["child"]."')";
		$this->db->query($q);

		$q = "SELECT LAST_INSERT_ID()";
		$res = $this->db->query($q);
		$row = $res->fetchRow();
		$entry["ref_id"] = $row[0];
	
		$q = "UPDATE grp_data SET ref_id='".$entry["ref_id"]."' WHERE child='".$entry["child"]."'";
		$this->db->query($q);
	}
}

unset($grp_data);
$grp_data = array();

// 2. replace child and parent (both are obj_ids) with ref_ids
$q = "SELECT * FROM grp_tree";
$res = $this->db->query($q);

while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$grp_data[] = $row;
}

$q = "DELETE FROM grp_tree";
$this->db->query($q);

if (count($grp_data) > 0)
{
	foreach ($grp_data as $key => $entry)
	{
		$q = "SELECT ref_id FROM object_reference WHERE obj_id='".$entry["parent"]."'";
		$res = $this->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC); 
		$entry["parent"] = $row["ref_id"];

		$q = "INSERT INTO grp_tree (tree,child,parent,lft,rgt,depth,perm,ref_id) VALUES ".
			 "('".$entry["tree"]."',".
			 "'".$entry["ref_id"]."',".
			 "'".$entry["parent"]."',".
			 "'".$entry["lft"]."',".
			 "'".$entry["rgt"]."',".
			 "'".$entry["depth"]."',".
			 "'".$entry["perm"]."',".
			 "'".$entry["child"]."')";
		$this->db->query($q);
	}
}

unset($grp_data);
?>

<#32>
ALTER TABLE grp_tree CHANGE ref_id obj_id INT(11);

<#33>
CREATE TABLE file_data
(
	file_id INT NOT NULL,
	file_type CHAR(64) NOT NULL,
	PRIMARY KEY (file_id)
);

<#34>
CREATE TABLE lo_access
(
	timestamp DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
	usr_id INT(11) DEFAULT '0' NOT NULL,
	lm_id  INT(11) DEFAULT '0' NOT NULL,
	obj_id INT(11) DEFAULT '0' NOT NULL,
	lm_title VARCHAR(200) DEFAULT '' NOT NULL
);

<#35>
ALTER TABLE file_data ADD file_name CHAR(128) NOT NULL AFTER file_id;

<#36>
DELETE FROM rbac_templates WHERE rol_id='83' AND type='grp' AND ops_id=3 AND parent=8;

<#37>
ALTER TABLE media_item ADD COLUMN param TEXT;

<#38>
INSERT INTO object_data (type, title, description, owner, create_date, last_update)
VALUES ('typ', 'exc', 'Exercise object', -1, now(), now());

<#39>
CREATE TABLE exc_data
(
	obj_id INT NOT NULL,
	instruction TEXT,
	time_stamp INT(10),
	PRIMARY KEY (obj_id)
	);
CREATE TABLE exc_members
(
	obj_id INT(11) NOT NULL,
	usr_id INT(11) NOT NULL,
	solved TINYINT(1) NULL,
	sent TINYINT(1) NULL,
	PRIMARY KEY(obj_id,usr_id)
	);

<#40>
INSERT INTO rbac_templates VALUES(81,"frm",4,8);
INSERT INTO rbac_templates VALUES(80,"frm",9,8);
INSERT INTO rbac_templates VALUES(80,"frm",10,8);

<#41>
ALTER TABLE learning_module RENAME content_object;

<#42>
ALTER TABLE content_object ADD COLUMN online ENUM('y','n') DEFAULT 'n';

<#43>
DELETE FROM rbac_templates where rol_id=82;

CREATE TABLE grp_registration (
`grp_id` INT NOT NULL ,
`user_id` INT NOT NULL ,
`subject` VARCHAR( 255 ) NOT NULL ,
`application_date` DATETIME NOT NULL
);

<#44>
DELETE FROM settings WHERE keyword='crs_enable';
DELETE FROM settings WHERE keyword='group_file_sharing';
DELETE FROM settings WHERE keyword='babylon_path';
DELETE FROM settings WHERE keyword='news';
DELETE FROM settings WHERE keyword='payment_system';

<#45>
ALTER TABLE grp_data CHANGE status register INTEGER DEFAULT '1';

<#46>
<?php
// adding new object type LDAP

// INSERT LDAP TYPE DEFINITION in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'ldap', 'LDAP settings object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// ADD OPERATION assignment to ldap object definition
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
$this->db->query($query);

// INSERT LDAP OBJECT in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('ldap','LDAP settings','Folder contains all LDAP settings','-1',now(),now())";
$this->db->query($query);

// fetch obj id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$obj_id = $row[0];

// CREATE OBJECT REFERENCE ENTRY for ldap object
$query = "INSERT INTO object_reference (obj_id) VALUES ('".$obj_id."')";
$this->db->query($query);

// fetch ref id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$ref_id = $row[0];

// INSERT LDAP OBJECT IN TREE (UNDER SYSTEMSETTINGS FOLDER)
$query = "SELECT * FROM tree".
     "WHERE child = '9' ".
     "AND tree = '1'";
$res = $this->db->getRow($query);

$left = $res->lft;
$lft = $left + 1;
$rgt = $left + 2;

// SPREAD TREE
$query = "UPDATE tree SET ".
     "lft = CASE ".
	 "WHEN lft > ".$left." ".
	 "THEN lft + 2 ".
	 "ELSE lft ".
	 "END, ".
	 "rgt = CASE ".
	 "WHEN rgt > ".$left." ".
	 "THEN rgt + 2 ".
	 "ELSE rgt ".
	 "END ".
	 "WHERE tree = '1'";
$this->db->query($query);

// INSERT NODE
$query = "INSERT INTO tree (tree,child,parent,lft,rgt,depth) ".
     "VALUES ".
	 "('1','".$ref_id."','9','".$lft."','".$rgt."','2')";
$this->db->query($query);
?>

<#47>
INSERT INTO settings (keyword, value) VALUES ('anonymous_role_id','14');

<#48>
ALTER TABLE grp_data ADD COLUMN password VARCHAR(255) DEFAULT NULL;
ALTER TABLE grp_data ADD COLUMN expiration DATETIME DEFAULT '0000-00-00 00:00:00';

<#49>
ALTER TABLE usr_data ADD COLUMN department VARCHAR(80) NOT NULL DEFAULT '';
ALTER TABLE usr_data CHANGE phone phone_office VARCHAR(40) NOT NULL DEFAULT '';
ALTER TABLE usr_data ADD COLUMN phone_home VARCHAR(40) NOT NULL DEFAULT '';
ALTER TABLE usr_data ADD COLUMN phone_mobile VARCHAR(40) NOT NULL DEFAULT '';
ALTER TABLE usr_data ADD COLUMN fax VARCHAR(40) NOT NULL DEFAULT '';

<#50>
<?php
// correct tree entry for LDAP object

// fetch ref_id of ldap object entry
$query = "SELECT ref_id FROM object_reference ".
		 "LEFT JOIN object_data ON object_reference.obj_id=object_data.obj_id ".
		 "WHERE object_data.type='ldap'";
$res = $this->db->query($query);

while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ref_id = $row->ref_id;
}

// remove false tree entry
$query = "DELETE FROM tree WHERE child = '".$ref_id."'";
$this->db->query($query);

// INSERT LDAP OBJECT IN TREE (UNDER SYSTEMSETTINGS FOLDER)
$query = "SELECT * FROM tree ".
     "WHERE child = '9' ".
     "AND tree = '1'";
$res = $this->db->query($query);

while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$left = $row->lft;
	$lft = $left + 1;
	$rgt = $left + 2;
}

// SPREAD TREE
$query = "UPDATE tree SET ".
     "lft = CASE ".
	 "WHEN lft > ".$left." ".
	 "THEN lft + 2 ".
	 "ELSE lft ".
	 "END, ".
	 "rgt = CASE ".
	 "WHEN rgt > ".$left." ".
	 "THEN rgt + 2 ".
	 "ELSE rgt ".
	 "END ".
	 "WHERE tree = '1'";
$this->db->query($query);

// INSERT NODE
$query = "INSERT INTO tree (tree,child,parent,lft,rgt,depth) ".
     "VALUES ".
	 "('1','".$ref_id."','9','".$lft."','".$rgt."','2')";
$this->db->query($query);
?>

<#51>
ALTER TABLE rbac_ua DROP COLUMN default_role;

<#52>
<?php
// change author role to template
// first move all assigned users from author to guest role
$query = "SELECT usr_id FROM rbac_ua WHERE rol_id='3'";
$res = $this->db->query($query);

$users = array();

while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$users[] = $row->usr_id;
}

foreach ($users as $key => $id)
{
	$query = "SELECT * FROM rbac_ua WHERE usr_id='".$id."' AND rol_id='5'";
	$res = $this->db->query($query);

	if (!$res->numRows())
	{
		$query = "INSERT INTO rbac_ua (usr_id,rol_id) ".
    			 "VALUES ".
				 "('".$id."','5')";
		$this->db->query($query);
	}
}

// change object type of author from role to rolt
$query = "UPDATE object_data SET type='rolt', description='Role template for authors with write & create permissions.' WHERE obj_id='3'";
$this->db->query($query);

// change assign status
$query = "UPDATE rbac_fa SET assign='n' WHERE rol_id='3' AND parent='8'";
$this->db->query($query);

// remove invalid datas
$query = "DELETE FROM rbac_fa WHERE rol_id='3' AND parent!='8'";
$this->db->query($query);

$query = "DELETE FROM rbac_templates WHERE rol_id='3' AND parent!='8'";
$this->db->query($query);

$query = "DELETE FROM rbac_ua WHERE rol_id='3'";
$this->db->query($query);
?>

<#53>
UPDATE object_data SET title='User', description='Standard role for registered users. Grants read access to most objects.' WHERE obj_id='4';

<#54>
CREATE TABLE mob_usage
(
	id INT NOT NULL,
	usage_type CHAR(4) NOT NULL,
	usage_id INT NOT NULL,
	PRIMARY KEY (id, usage_type, usage_id)
);

<#55>
INSERT INTO settings (keyword,value) VALUES ('system_user_id','6');

<#56>
ALTER TABLE `exc_members` ADD `notice` TEXT AFTER `usr_id` ,
ADD `returned` TINYINT( 1 ) AFTER `notice` ;

<#57>
<?php

  // GET DEFAULT OPERATIONS FOR EXC-OBJECT
$query = "SELECT DISTINCT(ops_id) AS id FROM rbac_operations ".
	"WHERE operation IN('visible','read','write','create','delete','edit permission')";

$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ops_ids[] = $row->id;
}
// GET type_id of exc object
$query = "SELECT obj_id FROM object_data ".
	"WHERE type = 'typ' ".
	"AND title = 'exc'";

$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$type_id = $row->obj_id;
}
// INSERT OPERATIONS
foreach($ops_ids as $id)
{
	$query = "INSERT INTO rbac_ta ".
		"SET typ_id = '".$type_id."', ".
		"ops_id = '".$id."'";
	$this->db->query($query);
}
?>

<#58>
<?php

//GET ID OF THE IL_GRP_MEMBER TEMPLATE
$query1 = "SELECT obj_id FROM object_data WHERE title = 'il_grp_member' ";
$res = $this->db->query($query1);
$tpl = $res->fetchRow(DB_FETCHMODE_ASSOC);

//GET PROPER PARENT_ID
$query2 = "SELECT parent FROM rbac_templates WHERE rol_id = ".$tpl["obj_id"];
$res = $this->db->query($query2);
$rol_fold = $res->fetchRow(DB_FETCHMODE_ASSOC);


//DELETE RIGHTS FOR COURSE OBJECT FROM THE TEMPLATE
$query3 = "DELETE FROM rbac_templates WHERE rol_id = '".$tpl["obj_id"]."' AND type = 'crs'";
$this->db->query($query3);

//CHANGE RIGHTS OF THE FORUM OBJECT IN THE TEMPLATE
$query4 = "DELETE FROM rbac_templates WHERE rol_id = '".$tpl["obj_id"]."' AND type = 'frm' AND ops_id = 5 ";
$this->db->query($query4);
$query5 = "DELETE FROM rbac_templates WHERE rol_id = '".$tpl["obj_id"]."' AND type = 'frm' AND ops_id = 6 ";
$this->db->query($query5);
$query6 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','frm','9','".$rol_fold["parent"]."')";
$this->db->query($query6);

//CHANGE RIGHTS OF THE FORUM OBJECT IN THE TEMPLATE
$query7 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','glo','2','".$rol_fold["parent"]."')";
$this->db->query($query7);
$query8 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','glo','3','".$rol_fold["parent"]."')";
$this->db->query($query8);

//CHANGE RIGHTS OF THE GROUP OBJECT IN THE TEMPLATE
$query9 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','grp','5','".$rol_fold["parent"]."')";
$this->db->query($query9);
$query10 = "DELETE FROM rbac_templates WHERE rol_id = '".$tpl["obj_id"]."' AND type = 'grp' AND ops_id = 4 ";
$this->db->query($query10);

//CHANGE RIGHTS OF THE LEARNING MODUL OBJECT IN THE TEMPLATE
$query11 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','lm','2','".$rol_fold["parent"]."')";
$this->db->query($query11);
$query12 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','lm','3','".$rol_fold["parent"]."')";
$this->db->query($query12);

//CHANGE RIGHTS OF THE SCORM LEARNING MODUL OBJECT IN THE TEMPLATE
$query13 = "DELETE FROM rbac_templates WHERE rol_id = '".$tpl["obj_id"]."' AND type = 'slm' AND ops_id = 5 ";
$this->db->query($query13);
$query14 = "DELETE FROM rbac_templates WHERE rol_id = '".$tpl["obj_id"]."' AND type = 'slm' AND ops_id = 6 ";
$this->db->query($query14);
?>

<#59>
<?php

//GET ID OF THE IL_GRP_ADMIN TEMPLATE
$query1 = "SELECT obj_id FROM object_data WHERE title = 'il_grp_admin' ";
$res = $this->db->query($query1);
$tpl = $res->fetchRow(DB_FETCHMODE_ASSOC);

//GET PROPER PARENT_ID
$query2 = "SELECT parent FROM rbac_templates WHERE rol_id = ".$tpl["obj_id"];
$res = $this->db->query($query2);
$rol_fold = $res->fetchRow(DB_FETCHMODE_ASSOC);

//CHANGE RIGHTS OF THE GROUP OBJECT IN THE TEMPLATE
$query3 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','grp','5','".$rol_fold["parent"]."')";
$this->db->query($query3);

//DELETE RIGHTS FOR COURSE OBJECT FROM THE TEMPLATE
$query4 = "DELETE FROM rbac_templates WHERE rol_id = '".$tpl["obj_id"]."' AND type = 'crs'";
$this->db->query($query4);

//CHANGE RIGHTS OF THE FORUM OBJECT IN THE TEMPLATE
$query5 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','frm','1','".$rol_fold["parent"]."')";
$this->db->query($query5);
$query6 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','frm','4','".$rol_fold["parent"]."')";
$this->db->query($query6);

//CHANGE RIGHTS OF THE GLOSSARY OBJECT IN THE TEMPLATE
$query7 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','glo','1','".$rol_fold["parent"]."')";
$this->db->query($query7);
$query8 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','glo','4','".$rol_fold["parent"]."')";
$this->db->query($query8);
$query9 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','glo','7','".$rol_fold["parent"]."')";
$this->db->query($query9);
$query10 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','glo','8','".$rol_fold["parent"]."')";
$this->db->query($query10);

//CHANGE RIGHTS OF THE GLOSSARY OBJECT IN THE TEMPLATE
$query11 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','rolf','1','".$rol_fold["parent"]."')";
$this->db->query($query11);
$query12 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','rolf','5','".$rol_fold["parent"]."')";
$this->db->query($query12);
$query13 = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','rolf','6','".$rol_fold["parent"]."')";
$this->db->query($query13);
?>

<#60>
<?php
$query1 = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'fold', 'Folder object', -1, now(), now())";
$this->db->query($query1);
$query2 = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'file', 'File object', -1, now(), now())";
$this->db->query($query2);
?>
<#61>
<?php
// check if setup migration is required
$ini = new ilIniFile(ILIAS_ABSOLUTE_PATH."/ilias.ini.php");
$res = $ini->read();

$migrate = true;

if ($res)
{
	if ($ini->readVariable("clients","path") !== false)
	{
		$migrate = false;
	}
}

unset($ini);

if ($migrate)
{
	$query = "SELECT * FROM settings";
	$res = $this->db->query($query);

	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$settings[$row->keyword] = $row->value;
	}

	$client_id = "default";

	rename(ILIAS_ABSOLUTE_PATH."/ilias.ini.php",ILIAS_ABSOLUTE_PATH."/ilias.ini_copied.php");

	$ini_old = new ilIniFile(ILIAS_ABSOLUTE_PATH."/ilias.ini_copied.php");
	$res = $ini_old->read();

	$datadir = $ini_old->readVariable("server","data_dir");
	$datadir_client = $datadir."/".$client_id;
	$webdir = $ini_old->readVariable("server","absolute_path")."/data";
	$webdir_client = $webdir."/".$client_id;

	ilUtil::makeDir($datadir_client);
	ilUtil::makeDir($datadir_client."/forum");
	ilUtil::makeDir($datadir_client."/files");
	ilUtil::makeDir($datadir_client."/lm_data");
	ilUtil::makeDir($datadir_client."/mail");

	ilUtil::makeDir($webdir_client);
	ilUtil::makeDir($webdir_client."/css");
	ilUtil::makeDir($webdir_client."/mobs");
	ilUtil::makeDir($webdir_client."/lm_data");
	ilUtil::makeDir($webdir_client."/usr_images");

	//copy data dir
	ilUtil::rcopy($datadir."/forum",$datadir_client."/forum");
	ilUtil::rcopy($datadir."/files",$datadir_client."/files");
	ilUtil::rcopy($datadir."/lm_data",$datadir_client."/lm_data");
	ilUtil::rcopy($datadir."/mail",$datadir_client."/mail");
	// copy web dir
	ilUtil::rcopy($webdir."/css",$webdir_client."/css");
	ilUtil::rcopy($webdir."/mobs",$webdir_client."/mobs");
	ilUtil::rcopy($webdir."/lm_data",$webdir_client."/lm_data");
	ilUtil::rcopy($webdir."/usr_images",$webdir_client."/usr_images");

	$client_master = ILIAS_ABSOLUTE_PATH."/setup/client.master.ini.php";
	$ini_new = new ilIniFile($webdir_client."/client.ini.php");
	$ini_new->GROUPS = parse_ini_file($client_master,true);

	$ini_new->setVariable("client","name",$settings["inst_name"]);
	$ini_new->setVariable("client","description",$settings["inst_info"]);
	$ini_new->setVariable("client","access",1);
	$ini_new->setVariable("db","host",$ini_old->readVariable("db","host"));
	$ini_new->setVariable("db","user",$ini_old->readVariable("db","user"));
	$ini_new->setVariable("db","pass",$ini_old->readVariable("db","pass"));
	$ini_new->setVariable("db","name",$ini_old->readVariable("db","name"));
	$ini_new->setVariable("language","default",$ini_old->readVariable("language","default"));
	$ini_new->setVariable("layout","skin",$ini_old->readVariable("layout","skin"));
	$ini_new->setVariable("layout","style",$ini_old->readVariable("layout","style"));

	$ilias_master = ILIAS_ABSOLUTE_PATH."/setup/ilias.master.ini.php";
	$ini_il = new ilIniFile($ini_old->readVariable("server","absolute_path")."/ilias.ini.php");
	$ini_il->GROUPS = parse_ini_file($ilias_master,true);

	$ini_il->setVariable("server","http_path",$ini_old->readVariable("server","http_path"));
	$ini_il->setVariable("server","absolute_path",$ini_old->readVariable("server","absolute_path"));
	$ini_il->setVariable("clients","datadir",$ini_old->readVariable("server","data_dir"));
	$ini_il->setVariable("tools", "convert", $settings["convert_path"]);
	$ini_il->setVariable("tools", "zip", $settings["zip_path"]);
	$ini_il->setVariable("tools", "unzip", $settings["unzip_path"]);
	$ini_il->setVariable("tools", "java", $settings["java_path"]);
	$ini_il->setVariable("tools", "htmldoc", $settings["htmldoc"]);

	$setup_pass = ($settings["setup_passwd"]) ? $settings["setup_passwd"] : md5("homer");
	$ini_il->setVariable("setup", "pass", $setup_pass);
	$ini_il->setVariable("clients","default",$client_id);

	$ini_new->write();
	$ini_il->write();

	if (!$settings["setup_ok"])
	{
		$query = "INSERT INTO settings VALUES ('setup_ok','1')";
		$this->db->query($query);
	}

	if (!isset($settings["nic_enabled"]))
	{
		$query = "INSERT INTO settings VALUES ('nic_enabled','0')";
		$this->db->query($query);
	}
}
?>

<#62>

CREATE TABLE int_link
(
	source_type CHAR(4) NOT NULL DEFAULT '',
	source_id INT NOT NULL,
	target_type CHAR(4) NOT NULL DEFAULT '',
	target_id INT NOT NULL,
	target_inst INT NOT NULL DEFAULT '0',
	PRIMARY KEY (source_type, source_id, target_type, target_id, target_inst)
);

<#63>
<?php
// CODE IS BROKEN! DON'T USE IT
// remove LDAP node temporary
// get LDAP node data
//$query = "SELECT * FROM tree WHERE child=13 AND tree=1";
//$res = $this->db->query($query);
//$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// take out node in main tree
//$query = "update tree SET tree='-13' WHERE child=13";
//$this->db->query($query);

// close gaps
//$diff = $row->rgt - $row->lft + 1;

//$query = "UPDATE tree SET ".
//		 "lft = CASE ".
//		 "WHEN lft > '".$row->lft." '".
//		 "THEN lft - '".$diff." '".
//		 "ELSE lft ".
//		 "END, ".
//		 "rgt = CASE ".
//		 "WHEN rgt > '".$row->lft." '".
//		 "THEN rgt - '".$diff." '".
//		 "ELSE rgt ".
//		 "END ".
//		 "WHERE tree = 1";
//$this->db->query($query);
?>

<#64>
ALTER TABLE page_object MODIFY content MEDIUMTEXT;

<#65>
ALTER TABLE mob_usage MODIFY usage_type VARCHAR(10) NOT NULL;


<#66>
UPDATE settings SET value = '3.0.0_beta1' WHERE keyword = 'ilias_version' LIMIT 1;

<#67>
ALTER TABLE int_link MODIFY source_type VARCHAR(10) NOT NULL;

<#68>
#
# Tabellenstruktur für Tabelle `search_data`
#

CREATE TABLE search_data (
  obj_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  title varchar(200) NOT NULL default '',
  target text NOT NULL default '',
  type varchar(4) NOT NULL default '',
  PRIMARY KEY  (obj_id,user_id)
) TYPE=MyISAM;

#
# Tabellenstruktur für Tabelle `search_tree`
#

CREATE TABLE search_tree (
  tree int(11) NOT NULL default '0',
  child int(11) unsigned NOT NULL default '0',
  parent int(11) unsigned default NULL,
  lft int(11) unsigned NOT NULL default '0',
  rgt int(11) unsigned NOT NULL default '0',
  depth smallint(5) unsigned NOT NULL default '0',
  KEY child (child),
  KEY parent (parent)
) TYPE=MyISAM;

  DELETE FROM usr_search;

<#69>
  DELETE FROM usr_search;
  DELETE FROM search_data;
  DELETE FROM search_tree;

<#70>
UPDATE settings SET value = '3.0.0_beta2' WHERE keyword = 'ilias_version' LIMIT 1;

<#71>
#
# Tabellenstruktur für Tabelle `dp_changed_dates`
#

CREATE TABLE dp_changed_dates (
  ID int(15) NOT NULL auto_increment,
  user_ID int(15) NOT NULL default '0',
  date_ID int(15) NOT NULL default '0',
  status int(15) NOT NULL default '0',
  timestamp int(10) NOT NULL default '0',
  PRIMARY KEY  (ID)
) TYPE=MyISAM COMMENT='Tabelle für Anzeige von Geänderten Termindaten';
# --------------------------------------------------------

<#72>
#
# Tabellenstruktur für Tabelle `dp_dates`
#

CREATE TABLE dp_dates (
  ID int(15) NOT NULL auto_increment,
  begin int(10) NOT NULL default '0',
  end int(10) NOT NULL default '0',
  group_ID int(15) NOT NULL default '0',
  user_ID int(15) NOT NULL default '0',
  created int(10) NOT NULL default '0',
  changed int(10) NOT NULL default '0',
  rotation int(15) NOT NULL default '0',
  shorttext varchar(50) NOT NULL default '',
  text text,
  end_rotation int(10) NOT NULL default '0',
  PRIMARY KEY  (ID)
) TYPE=MyISAM COMMENT='Termin Tabelle';
# --------------------------------------------------------

<#73>
#
# Tabellenstruktur für Tabelle `dp_keyword`
#

CREATE TABLE dp_keyword (
  ID int(15) NOT NULL auto_increment,
  user_ID int(15) NOT NULL default '0',
  keyword varchar(20) NOT NULL default '',
  PRIMARY KEY  (ID)
) TYPE=MyISAM COMMENT='Tabelle für Schlagwörter';
# --------------------------------------------------------

<#74>
#
# Tabellenstruktur für Tabelle `dp_keywords`
#

CREATE TABLE dp_keywords (
  ID int(15) NOT NULL auto_increment,
  date_ID int(15) NOT NULL default '0',
  keyword_ID int(15) NOT NULL default '0',
  PRIMARY KEY  (ID)
) TYPE=MyISAM COMMENT='Tabelle für die Zuordnung der Schlagwörter';
# --------------------------------------------------------

<#75>
#
# Tabellenstruktur für Tabelle `dp_neg_dates`
#

CREATE TABLE dp_neg_dates (
  ID int(15) NOT NULL auto_increment,
  date_ID int(15) NOT NULL default '0',
  user_ID int(15) NOT NULL default '0',
  timestamp int(14) default NULL,
  PRIMARY KEY  (ID)
) TYPE=MyISAM COMMENT='Tabelle für die negativen Termine';
# --------------------------------------------------------

<#76>
#
# Tabellenstruktur für Tabelle `dp_properties`
#

CREATE TABLE dp_properties (
  ID int(15) NOT NULL auto_increment,
  user_ID int(15) NOT NULL default '0',
  dv_starttime time NOT NULL default '00:00:00',
  dv_endtime time NOT NULL default '00:00:00',
  PRIMARY KEY  (ID)
) TYPE=MyISAM COMMENT='Tabelle für UserEinstellungen';

<#77>
ALTER TABLE xmlvalue ADD FULLTEXT ( tag_value );

<#78>
UPDATE usr_pref SET value='default' WHERE keyword='skin';
UPDATE usr_pref SET value='blueshadow' WHERE keyword='style';

<#79>
<?php
//
$q = "SELECT * FROM page_object WHERE content LIKE '%flit%'";
$page_set = $this->db->query($q);

while ($page_rec = $page_set->fetchRow(DB_FETCHMODE_ASSOC))
{
	$content = $page_rec["content"];

	while (ereg("flit\_([0-9]*)", $content, $found))
	{
		$new = "il__file_".$found[1];
		$content = ereg_replace($found[0], $new, $content);
//echo "replace ".$found[0]." with $new<br>";
	}
	$q = "UPDATE page_object SET content = '".addslashes($content)."'".
		" WHERE page_id = '".$page_rec["page_id"]."'".
		" AND parent_type='".$page_rec["parent_type"]."'";
	$this->db->query($q);
}

?>

<#80>
ALTER TABLE glossary_term ADD COLUMN
  create_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE glossary_term ADD COLUMN
  last_update DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';

<#81>
ALTER TABLE lm_data ADD COLUMN
  create_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE lm_data ADD COLUMN
  last_update DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';

<#82>
UPDATE rbac_operations SET operation='edit_permission' WHERE ops_id='1';
UPDATE rbac_operations SET operation='edit_post' WHERE ops_id='9';
UPDATE rbac_operations SET operation='delete_post' WHERE ops_id='10';
UPDATE rbac_operations SET operation='smtp_mail' WHERE ops_id='11';
UPDATE rbac_operations SET operation='system_message' WHERE ops_id='12';

INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('13', 'create_user', 'create new user account');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('22', '13');
DELETE FROM rbac_ta WHERE typ_id='25' AND ops_id='5';

# remove useless write-operation for lngf-object
DELETE FROM rbac_ta WHERE typ_id='28' AND ops_id='4';

# remove mob-object completely from RBAC system
DELETE FROM rbac_ta WHERE typ_id='18';

# remove useless create-operation for adm-object
DELETE FROM rbac_ta WHERE typ_id='21' AND ops_id='5';

# add operations 'create_role' and 'create_rolt'
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('14', 'create_role', 'create new role definition');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('15', 'create_rolt', 'create new role definition template');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('23', '14');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('23', '15');
DELETE FROM rbac_ta WHERE typ_id='27';
DELETE FROM rbac_ta WHERE typ_id='30';
DELETE FROM rbac_ta WHERE typ_id='23' AND ops_id='5';

<#83>
# add create operations for most object types
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('16', 'create_cat', 'create new category');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('17', 'create_grp', 'create new group');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('18', 'create_frm', 'create new forum');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('19', 'create_crs', 'create new course');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('20', 'create_lm', 'create new learning module');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('21', 'create_slm', 'create new SCORM learning module');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('22', 'create_glo', 'create new glossary');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('23', 'create_dbk', 'create new digibook');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('24', 'create_exc', 'create new exercise');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('25', 'create_file', 'upload new file');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('26', 'create_fold', 'create new folder');

# assign create-operations to different object types according to defined rules in objects.xml
# create_cat
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('33', '16');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('16', '16');
# create grp
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('16', '17');
# create_frm
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('16', '18');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('15', '18');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('17', '18');
# create_crs
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('16', '19');
# create_lm
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('16', '20');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('17', '20');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('15', '20');
# create_slm
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('16', '21');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('17', '21');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('15', '21');
# create_glo
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('16', '22');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('17', '22');
# create_dbk
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('16', '23');
# create_exc
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('16', '24');
# create_file
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('15', '25');
# create_fold
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('15', '26');

<#84>
CREATE TABLE map_area (
	item_id int(11) NOT NULL default '0',
	nr int(11) NOT NULL default '0',
	shape VARCHAR(20),
	coords VARCHAR(200),
	link_type CHAR(3),
	title VARCHAR(200),
	href VARCHAR(200),
	target VARCHAR(50),
	type VARCHAR(20),
	target_frame VARCHAR(50),
	PRIMARY KEY (item_id, nr)
) TYPE=MyISAM;

<#85>
DROP TABLE IF EXISTS lo_attribute_idx;
DROP TABLE IF EXISTS lo_attribute_name;
DROP TABLE IF EXISTS lo_attribute_namespace;
DROP TABLE IF EXISTS lo_attribute_value;
DROP TABLE IF EXISTS lo_cdata;
DROP TABLE IF EXISTS lo_comment;
DROP TABLE IF EXISTS lo_element_idx;
DROP TABLE IF EXISTS lo_element_namespace;
DROP TABLE IF EXISTS lo_element_name;
DROP TABLE IF EXISTS lo_entity_reference;
DROP TABLE IF EXISTS lo_node_type;
DROP TABLE IF EXISTS lo_pi_data;
DROP TABLE IF EXISTS lo_pi_target;
DROP TABLE IF EXISTS lo_text;
DROP TABLE IF EXISTS lo_tree;

<#86>
<?php
// remove LDAP node temporary

// get LDAP node data
$query = "SELECT ref_id FROM object_reference ".
		 "LEFT JOIN object_data ON object_reference.obj_id=object_data.obj_id ".
		 "WHERE object_data.type = 'ldap'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// check if ldap node was found
if ($row->ref_id > 0)
{
	// init tree
	$tree = new ilTree(ROOT_FOLDER_ID);

	$ldap_node = $tree->getNodeData($row->ref_id);
	
	// check if ldap is already deactivated
	if ($ldap_node["tree"] > 0)
	{
		// remove ldap node from tree
		$tree->deleteTree($ldap_node);
	}
}
?>

<#87>
<?php
// remove create operation for file object
$query = "SELECT obj_id FROM object_data ".
		 "WHERE type='typ' AND title='file'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

$query = "DELETE FROM rbac_ta WHERE typ_id='".$row->obj_id."'";
$this->db->query($query);

// init rbac
$rbacadmin = new ilRbacAdmin();
$rbacreview = new ilRbacReview();
// init tree
$tree = new ilTree(ROOT_FOLDER_ID);
// init object definition
$ilObjDef = new ilObjectDefinition();
$ilObjDef->startParsing();

// migration of rbac_pa

// first clean up rbac_pa. remove empty entries
$query = "DELETE FROM rbac_pa WHERE ops_id='a:0:{}'";
$this->db->query($query);

// set new object create permissions
$query = "SELECT rbac_pa.ops_id, rbac_pa.rol_id, rbac_pa.obj_id as ref_id, object_data.type FROM rbac_pa ".
		 "LEFT JOIN object_reference ON rbac_pa.obj_id=object_reference.ref_id ".
		 "LEFT JOIN object_data ON object_reference.obj_id=object_data.obj_id";
$res = $this->db->query($query);

while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$arr_pa_entries[] = array(
								"ref_id"	=>	$row->ref_id,
								"rol_id"	=>	$row->rol_id,
								"type"		=>	$row->type,
								"operations"=>	unserialize($row->ops_id)
							);
}

foreach ($arr_pa_entries as $key => $pa_entry)
{
	// detect create permission
	$pa_entry["create"] = array_search("5",$pa_entry["operations"]);

	// remove create permission and remember pa_entries with create permission
	if ($pa_entry["create"] !== false)
	{
		unset($pa_entry["operations"][$pa_entry["create"]]);
	}

	switch ($pa_entry)
	{
		case "usrf":
			if (in_array("4",$pa_entry["operations"]))
			{
				$pa_entry["operations"][] = "13";
			}
			break;

		case "grp":
			if ($pa_entry["create"] !== false)
			{
				$pa_entry["operations"][] = "18";
				$pa_entry["operations"][] = "20";
				$pa_entry["operations"][] = "21";
			}

			if (in_array("2",$pa_entry["operations"]) and in_array("3",$pa_entry["operations"]))
			{
				$pa_entry["operations"][] = "25";
				$pa_entry["operations"][] = "26";
			}
			break;

		case "cat":
			if ($pa_entry["create"] !== false)
			{
				$pa_entry["operations"][] = "16";
				$pa_entry["operations"][] = "17";
				$pa_entry["operations"][] = "18";
				$pa_entry["operations"][] = "19";
				$pa_entry["operations"][] = "20";
				$pa_entry["operations"][] = "21";
				$pa_entry["operations"][] = "22";
				$pa_entry["operations"][] = "23";
				$pa_entry["operations"][] = "24";
			}
			break;

		case "crs":
			if ($pa_entry["create"] !== false)
			{
				$pa_entry["operations"][] = "18";
				$pa_entry["operations"][] = "20";
				$pa_entry["operations"][] = "21";
				$pa_entry["operations"][] = "22";
			}
			break;
	}

	// remove multiple values
	$pa_entry["operations"] = array_unique($pa_entry["operations"]);

	$rbacadmin->revokePermission($pa_entry["ref_id"],$pa_entry["rol_id"]);
	$rbacadmin->grantPermission($pa_entry["rol_id"],$pa_entry["operations"],$pa_entry["ref_id"]);
}

// migration of rbac_templates and rbac_ta

// build array with all rbac object types
$query = "SELECT ta.typ_id,obj.title,ops.ops_id,ops.operation FROM rbac_ta AS ta ".
		 "LEFT JOIN object_data AS obj ON obj.obj_id=ta.typ_id ".
		 "LEFT JOIN rbac_operations AS ops ON ops.ops_id=ta.ops_id";
$res = $this->db->query($query);

while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$rbac_objects[$row->typ_id] = array("obj_id"	=> $row->typ_id,
									    "type"		=> $row->title
										);

	$rbac_operations[$row->typ_id][$row->ops_id] = $row->ops_id;
}

foreach ($rbac_objects as $key => $obj_data)
{
	$rbac_objects[$key]["ops"] = $rbac_operations[$key];
}

// get all roles
$query = "SELECT * FROM rbac_fa";
$res = $this->db->query($query);

while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$arr_roles[] = array(
						"rol_id"	=>	$row->rol_id,
						"rolf_id"	=>	$row->parent,
						"assign"	=>	$row->assign
						);
}

foreach ($arr_roles as $role)
{
	// work on a copy of rbac_objects
	$rbac_objects_temp = $rbac_objects;

	// for local roles display only the permissions settings for allowed subobjects
	if ($role["rolf_id"] != ROLE_FOLDER_ID)
	{
		// first get object in question (parent of role folder object)
		$parent_data = $tree->getParentNodeData($role["rolf_id"]);
		// get allowed subobject of object
		$subobj_data = $ilObjDef->getSubObjects($parent_data["type"]);

		// remove not allowed object types from array but keep the type definition of object itself
		foreach ($rbac_objects_temp as $key => $obj_data)
		{
			if (!$subobj_data[$obj_data["type"]] and $parent_data["type"] != $obj_data["type"])
			{
				unset($rbac_objects_temp[$key]);
			}
		}
	} // end if local roles
	
	foreach ($rbac_objects_temp as $key => $obj_data)
	{
		$arr_selected = $rbacreview->getOperationsOfRole($role["rol_id"], $obj_data["type"], $role["rolf_id"]);

		// detect create permission
		$obj_data["create"] = array_search("5",$arr_selected);

		// remove create permission and remember pa_entries with create permission
		if ($obj_data["create"] !== false)
		{
			unset($arr_selected[$obj_data["create"]]);
		}

		if ($obj_data["create"] !== false)
		{
			switch ($obj_data["type"])
			{
				case "usrf":
					$arr_selected[] = "13";
					break;

				case "grp":
					$arr_selected[] = "18";
					$arr_selected[] = "20";
					$arr_selected[] = "21";
					$arr_selected[] = "25";
					$arr_selected[] = "26";

					break;
		
				case "cat":
					$arr_selected[] = "16";
					$arr_selected[] = "17";
					$arr_selected[] = "18";
					$arr_selected[] = "19";
					$arr_selected[] = "20";
					$arr_selected[] = "21";
					$arr_selected[] = "22";
					$arr_selected[] = "23";
					$arr_selected[] = "24";
					break;

				case "crs":
					$arr_selected[] = "18";
					$arr_selected[] = "20";
					$arr_selected[] = "21";
					$arr_selected[] = "22";
					break;
			}
		}

		// remove multiple values
		$arr_selected = array_unique($arr_selected);

		// sets new template permissions
		if (!empty($arr_selected))
		{
			// delete all template entries for each role
			$rbacadmin->deleteRolePermission($role["rol_id"], $role["rolf_id"],$obj_data["type"]);
			$rbacadmin->setRolePermission($role["rol_id"], $obj_data["type"], $arr_selected, $role["rolf_id"]);
		}
	}
}

// remove old create operation
$query = "DELETE FROM rbac_ta WHERE ops_id=5";
$this->db->query($query);
$query = "DELETE FROM rbac_operations WHERE ops_id=5";
$this->db->query($query);
// clean up tree
$query = "DELETE FROM tree WHERE parent=0 AND tree <> 1";
$this->db->query($query);
?>

<#88>
DELETE FROM usr_session;

<#89>
ALTER TABLE rbac_pa CHANGE obj_id ref_id INT(11) DEFAULT '0' NOT NULL;
DELETE FROM usr_session;

<#90>
UPDATE settings SET value = '3.0.0_beta3' WHERE keyword = 'ilias_version' LIMIT 1;

<#91>
DROP TABLE IF EXISTS cal_appointment;
DROP TABLE IF EXISTS cal_appointmentrepeats;
DROP TABLE IF EXISTS cal_appointmentrepeatsnot;
DROP TABLE IF EXISTS cal_category;
DROP TABLE IF EXISTS cal_priority;
DROP TABLE IF EXISTS cal_user_group;
DROP TABLE IF EXISTS dummy_groups;

<#92>
# add operations 'assign_user' and 'assign_role'
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('40', 'edit_userassignment', 'change userassignment of roles');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('41', 'edit_roleassignment', 'change roleassignments of user accounts');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('23', '40');
INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('22', '41');

<#93>
ALTER TABLE mail_options ADD incoming_type TINYINT( 3 );

<#94>
# add operations 'create_tst' and 'create_qpl' for test objects and question pools
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('27', 'create_tst', 'create new test');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('28', 'create_qpl', 'create new question pool');

<#95>
<?php

// insert test definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'tst', 'Test object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operation assignment to test object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
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
?>

<#96>
<?php

// insert question pool definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'qpl', 'Question pool object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operation assignment to question pool object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
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
?>

<#97>
<?php

// get category type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='cat'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add create_tst and create_qpl operations to category type
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','27')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','28')";
$this->db->query($query);
?>

<#98>

#
# Table structure for table 'qpl_answers'
#

DROP TABLE IF EXISTS `qpl_answers`;
CREATE TABLE `qpl_answers` (
  `answer_id` int(10) unsigned NOT NULL auto_increment,
  `question_fi` int(10) unsigned NOT NULL default '0',
  `answertext` char(100) NOT NULL default '',
  `points` double NOT NULL default '0',
  `order` int(10) unsigned NOT NULL default '0',
  `correctness` enum('0','1') NOT NULL default '0',
  `solution_order` int(10) unsigned NOT NULL default '0',
  `matchingtext` char(100) default NULL,
  `matching_order` int(10) unsigned default NULL,
  `gap_id` int(10) unsigned NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`answer_id`),
  UNIQUE KEY `answer_id` (`answer_id`),
  KEY `answer_id_2` (`answer_id`)
) TYPE=MyISAM;

<#99>

#
# Table structure for table 'qpl_question_type'
#

DROP TABLE IF EXISTS `qpl_question_type`;
CREATE TABLE `qpl_question_type` (
  `question_type_id` int(3) unsigned NOT NULL auto_increment,
  `type_tag` char(25) NOT NULL default '',
  PRIMARY KEY  (`question_type_id`),
  UNIQUE KEY `question_type_id` (`question_type_id`),
  KEY `question_type_id_2` (`question_type_id`)
) TYPE=MyISAM;

INSERT INTO `qpl_question_type` (`question_type_id`, `type_tag`) VALUES("1", "qt_multiple_choice_sr");
INSERT INTO `qpl_question_type` (`question_type_id`, `type_tag`) VALUES("2", "qt_multiple_choice_mr");
INSERT INTO `qpl_question_type` (`question_type_id`, `type_tag`) VALUES("3", "qt_cloze");
INSERT INTO `qpl_question_type` (`question_type_id`, `type_tag`) VALUES("4", "qt_matching");
INSERT INTO `qpl_question_type` (`question_type_id`, `type_tag`) VALUES("5", "qt_ordering");

<#100>

#
# Table structure for table 'qpl_questions'
#

DROP TABLE IF EXISTS `qpl_questions`;
CREATE TABLE `qpl_questions` (
  `question_id` int(11) NOT NULL default '0',
  `question_type_fi` int(10) unsigned NOT NULL default '0',
  `ref_fi` int(10) unsigned NOT NULL default '0',
  `title` varchar(100) NOT NULL default '',
  `comment` text,
  `author` varchar(50) NOT NULL default '',
  `owner` int(11) NOT NULL default '0',
  `question_text` text NOT NULL,
  `points` double default NULL,
  `start_tag` varchar(5) default NULL,
  `end_tag` varchar(5) default NULL,
  `cloze_type` enum('0','1') default NULL,
  `choice_response` enum('0','1') default NULL,
  `materials` text,
  `created` varchar(14) NOT NULL default '',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`question_id`)
) TYPE=MyISAM;

<#101>
<?php

$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'chat', 'Chat object', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// INSERT NEW OPERATIONS
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','4')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','6')";
$this->db->query($query);


// ADD NEW OPERATION create_chat
$query = "INSERT INTO rbac_operations VALUES('29','create_chat','create chat object')";
$this->db->query($query);

// ADD CREATE PERMISSION FOR TYPE 'cat','grp'
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND ( title = 'cat' OR title = 'grp') ";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO rbac_ta VALUES('".$row->obj_id."','29')";
	$this->db->query($query);
}
?>
<#102>
<?php

$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'chac', 'Chat server config object', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// INSERT NEW OPERATIONS
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','4')";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('chac', 'Chat server settings', 'Configure chat server settings here', -1, now(), now())";
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
?>

<#103>
CREATE TABLE chat_rooms (
room_id INT( 11 ) NOT NULL AUTO_INCREMENT ,
chat_id INT( 11 ) NOT NULL ,
title VARCHAR( 64 ) ,
owner INT( 11 ) NOT NULL ,
PRIMARY KEY ( room_id )
);
CREATE TABLE chat_invitations (
room_id INT( 11 ) NOT NULL ,
guest_id INT( 11 ) NOT NULL ,
PRIMARY KEY ( room_id , guest_id )
);
CREATE TABLE chat_user (
usr_id INT( 11 ) NOT NULL ,
room_id INT( 11 ) NOT NULL ,
last_conn_timestamp TIMESTAMP NOT NULL ,
PRIMARY KEY ( usr_id , room_id )
);
<#104>
ALTER TABLE frm_posts ADD notify TINYINT( 1 ) NOT NULL ;
<#105>
UPDATE settings SET value = '3.0.0_beta4 2004-02-18' WHERE keyword = 'ilias_version' LIMIT 1;
<#106>
CREATE TABLE `tst_mark` (
  `mark_id` int(10) unsigned NOT NULL auto_increment,
  `test_fi` int(10) unsigned NOT NULL default '0',
  `short_name` varchar(15) NOT NULL default '',
  `official_name` varchar(50) NOT NULL default '',
  `minimum_level` double NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`mark_id`),
  UNIQUE KEY `mark_id` (`mark_id`),
  KEY `mark_id_2` (`mark_id`)
) TYPE=MyISAM COMMENT='Mark steps of mark schemas';
<#107>
CREATE TABLE `tst_test_question` (
  `test_question_id` int(11) NOT NULL auto_increment,
  `test_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `sequence` int(10) unsigned NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`test_question_id`)
) TYPE=MyISAM COMMENT='Relation table for questions in tests';
<#108>
CREATE TABLE `tst_test_type` (
  `test_type_id` int(10) unsigned NOT NULL auto_increment,
  `type_tag` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`test_type_id`),
  UNIQUE KEY `test_type_id` (`test_type_id`),
  KEY `test_type_id_2` (`test_type_id`)
) TYPE=MyISAM COMMENT='ILIAS 3 Assessment Test types';

INSERT INTO `tst_test_type` (`test_type_id`, `type_tag`) VALUES (1, 'tt_assessment');
INSERT INTO `tst_test_type` (`test_type_id`, `type_tag`) VALUES (2, 'tt_self_assessment');
INSERT INTO `tst_test_type` (`test_type_id`, `type_tag`) VALUES (3, 'tt_navigation_controlling');
<#109>
CREATE TABLE `tst_tests` (
  `test_id` int(10) unsigned NOT NULL auto_increment,
  `ref_fi` int(11) NOT NULL default '0',
  `author` varchar(50) NOT NULL default '',
  `test_type_fi` int(10) unsigned NOT NULL default '0',
  `introduction` text,
  `sequence_settings` tinyint(3) unsigned NOT NULL default '0',
  `score_reporting` tinyint(3) unsigned NOT NULL default '0',
  `nr_of_tries` tinyint(3) unsigned NOT NULL default '0',
  `processing_time` int(10) unsigned NOT NULL default '0',
  `starting_time` varchar(14) default NULL,
  `created` varchar(14) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`test_id`),
  UNIQUE KEY `test_id` (`test_id`),
  KEY `test_id_2` (`test_id`)
) TYPE=MyISAM COMMENT='Tests in ILIAS Assessment';

<#110>
ALTER  TABLE  `tst_mark`  ADD  `passed` ENUM(  '0',  '1'  ) DEFAULT  '0' NOT  NULL  AFTER  `minimum_level` ;

<#111>
ALTER  TABLE  `qpl_questions`  ADD  `matching_type` ENUM(  '0',  '1'  )  AFTER  `end_tag` ;

<#112>
INSERT INTO `qpl_question_type` (`question_type_id`, `type_tag`) VALUES("6", "qt_imagemap");

<#113>
ALTER  TABLE  `qpl_questions`  ADD  `imagemap_file` VARCHAR( 100  )  AFTER  `materials` , ADD  `image_file` VARCHAR( 100  )  AFTER  `imagemap_file` ;

<#114>

<#115>
#
# Table structure for table 'qpl_answers'
#

DROP TABLE IF EXISTS `qpl_answers`;
CREATE TABLE `qpl_answers` (
  `answer_id` int(10) unsigned NOT NULL auto_increment,
  `question_fi` int(10) unsigned NOT NULL default '0',
  `answertext` char(100) NOT NULL default '',
  `points` double NOT NULL default '0',
  `aorder` int(10) unsigned NOT NULL default '0',
  `correctness` enum('0','1') NOT NULL default '0',
  `solution_order` int(10) unsigned NOT NULL default '0',
  `matchingtext` char(100) default NULL,
  `matching_order` int(10) unsigned default NULL,
  `gap_id` int(10) unsigned NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`answer_id`),
  UNIQUE KEY `answer_id` (`answer_id`),
  KEY `answer_id_2` (`answer_id`)
) TYPE=MyISAM;

<#116>
ALTER  TABLE  `qpl_answers`  ADD  `coords` TEXT AFTER  `gap_id` , ADD  `area` VARCHAR( 20  )  AFTER  `coords` ;

<#117>
CREATE TABLE `chat_room_messages` (
`entry_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`chat_id` INT( 11 ) NOT NULL ,
`room_id` INT( 11 ) NOT NULL ,
`message` TEXT,
`commit_timestamp` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `entry_id` )
);
<#118>
ALTER TABLE chat_user ADD chat_id INT( 11 ) NOT NULL AFTER usr_id ;

<#119>
ALTER TABLE usr_data ADD i2passwd VARCHAR(32) NOT NULL DEFAULT '';

<#120>
CREATE TABLE `xml_attribute_idx` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `value_id` smallint(5) unsigned NOT NULL default '0',
  KEY `node_id` (`node_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

CREATE TABLE `xml_attribute_name` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `attribute` char(32) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`),
  UNIQUE KEY `attribute` (`attribute`)
) TYPE=MyISAM ;

# --------------------------------------------------------

CREATE TABLE `xml_attribute_namespace` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `namespace` char(64) NOT NULL default '',
  PRIMARY KEY  (`attribute_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

CREATE TABLE `xml_attribute_value` (
  `value_id` smallint(5) unsigned NOT NULL auto_increment,
  `value` char(32) NOT NULL default '0',
  PRIMARY KEY  (`value_id`)
) TYPE=MyISAM ;

# --------------------------------------------------------

CREATE TABLE `xml_cdata` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `cdata` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM ;

# --------------------------------------------------------

CREATE TABLE `xml_comment` (
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `comment` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM ;

# --------------------------------------------------------

CREATE TABLE `xml_element_idx` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `element_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`node_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

CREATE TABLE `xml_element_name` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `element` char(32) NOT NULL default '',
  PRIMARY KEY  (`element_id`),
  UNIQUE KEY `element` (`element`)
) TYPE=MyISAM ;

# --------------------------------------------------------

CREATE TABLE `xml_element_namespace` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `namespace` char(64) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM ;

# --------------------------------------------------------

CREATE TABLE `xml_entity_reference` (
  `element_id` smallint(5) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `entity_reference` char(128) NOT NULL default '',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

CREATE TABLE `xml_node_type` (
  `node_type_id` int(11) NOT NULL auto_increment,
  `description` varchar(50) default NULL,
  `lft_delimiter` varchar(10) default NULL,
  `rgt_delimiter` varchar(10) default NULL,
  PRIMARY KEY  (`node_type_id`)
) TYPE=MyISAM;

INSERT INTO `xml_node_type` (`node_type_id`, `description`, `lft_delimiter`, `rgt_delimiter`) VALUES (1, 'ELEMENT_NODE', '<', '>'),
(2, 'ATTRIBUTE_NODE(not used)', '"', '"'),
(3, 'TEXT_NODE', NULL, NULL),
(5, 'ENTITY_REF_NODE', '&', ';'),
(4, 'CDATA_SECTION_NODE', '<![CDATA[', ']]>'),
(8, 'COMMENT_NODE', '<!--', '-->'),
(9, 'DOCUMENT_NODE', NULL, NULL),
(10, 'DOCUMENT_TYPE_NODE', NULL, NULL),
(6, 'ENTITY_NODE', '&', ';');

# --------------------------------------------------------

CREATE TABLE `xml_object` (
  `ID` int(11) NOT NULL auto_increment,
  `version` varchar(5) NOT NULL default '',
  `encoding` varchar(40) default NULL,
  `charset` varchar(40) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM COMMENT='Master Table for XML objects';

# --------------------------------------------------------

CREATE TABLE `xml_pi_data` (
  `leaf_id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `leaf_text` text NOT NULL,
  PRIMARY KEY  (`leaf_id`)
) TYPE=MyISAM ;

# --------------------------------------------------------

CREATE TABLE `xml_pi_target` (
  `leaf_id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `leaf_text` text NOT NULL,
  PRIMARY KEY  (`leaf_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

CREATE TABLE `xml_text` (
  `node_id` int(10) unsigned NOT NULL default '0',
  `textnode` text NOT NULL,
  PRIMARY KEY  (`node_id`),
  FULLTEXT KEY `textnode` (`textnode`)
) TYPE=MyISAM;

# --------------------------------------------------------

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

<#121>
ALTER TABLE chat_user DROP PRIMARY KEY;
ALTER TABLE chat_user ADD PRIMARY KEY(usr_id,chat_id,room_id);

<#122>
<?php
// register new object type 'recf' for RecoveryFolder
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'recf', 'RecoveryFolder object', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('recf', '__Restored Objects', 'Contains objects restored by recovery tool', -1, now(), now())";
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

// register RECOVERY_FOLDER_ID in table settings
$query = "INSERT INTO settings (keyword,value) VALUES('recovery_folder_id','".$row->id."')";
$res = $this->db->query($query);
?>

<#123>
CREATE TABLE `tst_solutions` (
  `solution_id` int(10) unsigned NOT NULL auto_increment,
  `user_fi` int(10) unsigned NOT NULL default '0',
  `test_fi` int(10) unsigned NOT NULL default '0',
  `question_fi` int(10) unsigned NOT NULL default '0',
  `value1` varchar(50) default NULL,
  `value2` varchar(50) default NULL,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`solution_id`),
  UNIQUE KEY `solution_id` (`solution_id`),
  KEY `solution_id_2` (`solution_id`)
) TYPE=MyISAM COMMENT='Test and Assessment solutions';

<#124>
ALTER  TABLE  `tst_solutions`  ADD  `postponed` ENUM(  '0',  '1'  ) DEFAULT  '0' NOT  NULL  AFTER  `value2` ;

<#125>
CREATE TABLE `tst_active` (
  `active_id` int(10) unsigned NOT NULL auto_increment,
  `user_fi` int(10) unsigned NOT NULL default '0',
  `test_fi` int(10) unsigned NOT NULL default '0',
  `sequence` text NOT NULL,
  `lastindex` tinyint(4) NOT NULL default '1',
  `tries` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`active_id`),
  UNIQUE KEY `active_id` (`active_id`),
  KEY `active_id_2` (`active_id`)
) TYPE=MyISAM ;

<#126>
ALTER  TABLE  `tst_solutions`  DROP  `postponed` ;

<#127>
ALTER  TABLE  `tst_active`  ADD  `postponed` text AFTER  `sequence` ;

<#128>
UPDATE settings SET value = '3.0.0_beta5 2004-03-09' WHERE keyword = 'ilias_version' LIMIT 1;

<#129>
CREATE TABLE `qpl_question_material` (
  `material_id` int(11) NOT NULL auto_increment,
  `question_id` int(11) NOT NULL default '0',
  `materials` text,
  UNIQUE KEY `material_id` (`material_id`)
) TYPE=MyISAM;

<#130>
CREATE  TABLE  `tst_times` (
`times_id` INT NOT  NULL  AUTO_INCREMENT ,
`active_fi` INT NOT  NULL ,
`started` DATETIME NOT  NULL ,
`finished` DATETIME NOT  NULL ,
`TIMESTAMP` TIMESTAMP NOT  NULL ,
PRIMARY  KEY (  `times_id`  )
) COMMENT  =  'Editing times of an assessment test';

<#131>
CREATE TABLE benchmark
(
	cdate			DATETIME,
	module			VARCHAR(200),
	benchmark		VARCHAR(200),
	duration		DOUBLE(14,5),
	INDEX (module, benchmark)
);

<#132>
REPLACE INTO settings (keyword, value) VALUES ('bench_max_records', 10000);
REPLACE INTO settings (keyword, value) VALUES ('enable_bench', 0);
<#133>
ALTER  TABLE  `tst_tests`  ADD  `reporting_date` VARCHAR( 14  )  AFTER  `processing_time` ;
<#134>
ALTER  TABLE  `qpl_questions`  ADD  `complete` ENUM(  '0',  '1'  ) DEFAULT  '1' NOT  NULL  AFTER  `image_file` ;
<#135>
ALTER  TABLE  `tst_tests`  ADD  `complete` ENUM(  '0',  '1'  ) DEFAULT  '1' NOT  NULL  AFTER  `starting_time` ;

<#136>
<?php
// ADD NEW OPERATION create_chat
$query = "INSERT INTO rbac_operations VALUES('30','mail_visible','users can use mail system')";
$this->db->query($query);

// ENABLE OPERATION FOR MAIL OBJECT
$query = "INSERT INTO rbac_ta VALUES('19','30')";
$this->db->query($query);

?>
<#137>
<?php
// MAIL REF_ID
$query = "SELECT * FROM object_data NATURAL JOIN object_reference ".
		"WHERE type = 'mail'";

$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ref_id = $row->ref_id;
}
// GET ALL ROLE_IDS
$query = "SELECT DISTINCT(rol_id) FROM rbac_pa";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$role_ids[] = $row->rol_id;
}

$rbacsystem =& new ilRbacSystem();
$rbacadmin =& new ilRbacAdmin();
$rbacreview =& new ilRbacReview();

foreach($role_ids as $id)
{
	if($rbacsystem->checkPermission($ref_id,$id,"visible") and $rbacsystem->checkPermission($ref_id,$id,"read"))
	{
		$ops = $rbacreview->getRoleOperationsOnObject($id,$ref_id);
		$ops[] = 30;
		$rbacadmin->revokePermission($ref_id,$id);
		$rbacadmin->grantPermission($id,$ops,$ref_id);
	}
}
?>
<#138>
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

<#139>
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('31', 'create_mep', 'create new media pool');

<#140>
<?php

// insert media pool definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'mep', 'Media pool object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operations to media pool
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
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
?>

<#141>
<?php

// add create media pool operation to categories
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='cat'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$typ_id = $row["obj_id"];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','31')";
$this->db->query($query);
?>

<#142>

CREATE TABLE mep_tree
(
	mep_id INT NOT NULL,
	child INT NOT NULL,
	parent INT NOT NULL,
	lft INT NOT NULL,
	rgt INT NOT NULL,
	depth SMALLINT NOT NULL
);

<#143>
ALTER  TABLE  `qpl_questions`  ADD  `ordering_type` enum('0','1') default NULL  AFTER  `matching_type` ;

<#144>
UPDATE content_object SET default_layout = '1window' WHERE default_layout='no_menu';

<#145>
ALTER TABLE mail DROP COLUMN timest;

<#146>
ALTER TABLE mail CHANGE m_type m_type VARCHAR( 255 ) DEFAULT NULL;

<#147>
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('32', 'create_htlm', 'create new html learning module');

<#148>
<?php

// insert media pool definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'htlm', 'HTML LM object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operations to html lm
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
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
?>

<#149>
<?php

// add create html lm operation to categories
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='cat'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$typ_id = $row["obj_id"];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','32')";
$this->db->query($query);
?>
<#150>
ALTER  TABLE  `qpl_questions`  ADD  `working_time` TIME DEFAULT  '00:00:00' NOT  NULL  AFTER  `question_text` ;
<#151>
ALTER  TABLE  `qpl_questions`  ADD  `shuffle` ENUM(  '0',  '1'  ) DEFAULT  '1' NOT  NULL  AFTER  `working_time` ;
<#152>
ALTER TABLE `frm_posts` ADD `pos_subject` TEXT NOT NULL AFTER `pos_message` ;
<#153>
ALTER TABLE content_object ADD COLUMN toc_active ENUM('y','n') DEFAULT 'y';
ALTER TABLE content_object ADD COLUMN lm_menu_active ENUM('y','n') DEFAULT 'y';

<#154>
CREATE TABLE file_based_lm
(
	id INT NOT NULL PRIMARY KEY,
	online ENUM('y','n') DEFAULT 'n',
	startfile VARCHAR(200)
);

<#155>
<?php

// build file_based_lm entries for each html learning module
$query = "SELECT * FROM object_data WHERE type='htlm'";
$res = $this->db->query($query);
while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$obj_id = $row["obj_id"];
	$query = "INSERT INTO file_based_lm (id, online) VALUES ('".
		$row["obj_id"]."', 'n')";
	$this->db->query($query);
}
?>
<#156>
ALTER  TABLE  `qpl_answers`  ADD  `cloze_type` ENUM(  '0',  '1'  )  AFTER  `gap_id` ;

<#157>
<?php
// save the cloze type from the qpl_questions table into the qpl_answers table
$query = "SELECT * FROM qpl_questions WHERE cloze_type >= 0";
$res = $this->db->query($query);
while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$update_query = sprintf("UPDATE qpl_answers SET cloze_type = %s WHERE question_fi = %s",
		$this->db->quote("$row->cloze_type"),
		$this->db->quote("$row->question_id")
	);
	$update_res = $this->db->query($update_query);
}
?>
<#158>
<?php
// insert files into tree
$query = "SELECT * FROM object_data AS obj ".
		 "LEFT JOIN object_reference AS ref ON obj.obj_id = ref.obj_id ".
		 "LEFT JOIN grp_tree AS grp ON grp.child = ref.ref_id ".
		 "WHERE obj.type = 'file'";
$res = $this->db->query($query);

$tree = new ilTree(ROOT_FOLDER_ID);

while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if ($row->tree > 0)
	{
		$query = "SELECT * FROM tree WHERE child='".$row->tree."' AND tree='1'";
		$res2 = $this->db->query($query);

		if ($res2->numRows() > 0)
		{
			$tree->insertNode($row->child,$row->tree);
		}
	}
}

// remove table grp_tree
$query = "DROP TABLE IF EXISTS grp_tree";
$this->db->query($query);
?>
<#159>
<?php
$query = "SELECT * FROM `qpl_questions` WHERE  NOT isnull(start_tag)";
$res = $this->db->query($query);
while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$new_text = preg_replace("/" . preg_quote($row->start_tag) . "(.*?)" . preg_quote($row->end_tag) . "/", "<gap>$1</gap>", $row->question_text);
	$update_query = sprintf("UPDATE qpl_questions SET question_text = %s WHERE question_id = %s",
		$this->db->quote($new_text),
		$this->db->quote("$row->question_id")
	);
	$update_res = $this->db->query($update_query);
}
?>
<#160>
<?php
// fetch type id of file object definition
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='file'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];
// add operation assignment to file object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
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

// fetch type id of folder object definition
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='fold'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operation assignment to file object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
// 18: create_frm, 20: create_lm, 21: create_slm, 22: create_glo, 25: create_file, 26: create_fold
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
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','18')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','20')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','21')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','22')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','25')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','26')";
$this->db->query($query);

// fetch type id of group object definition
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='grp'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add create_glo operation assignment to grp object definition
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','22')";
$this->db->query($query);

// init rbac
$rbacadmin = new ilRbacAdmin();
$rbacreview = new ilRbacReview();

// init tree
$tree = new ilTree(ROOT_FOLDER_ID);

// fetch obj id of group member role template
$query = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_member'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$mem_role_id = $row[0];

// update group role templates
// member role
$ops_data["chat"] = array(2,3);
$ops_data["file"] = array(2,3);
$ops_data["frm"] = array(2,3,4,9);
$ops_data["glo"] = array(2,3,7,8);
$ops_data["grp"] = array(2,3,7,8,18,25,26);
$ops_data["lm"] = array(2,3,7,8);
$ops_data["slm"] = array(2,3,7,8);
$ops_data["fold"] = array(2,3,18,25,26);

// sets new template permissions
foreach ($ops_data as $type => $ops)
{
	// delete all template entries for each role
	$rbacadmin->deleteRolePermission($mem_role_id, ROLE_FOLDER_ID,$type);
	$rbacadmin->setRolePermission($mem_role_id, $type, $ops, ROLE_FOLDER_ID);
}

// copy member template settings to all group member roles
$query = "SELECT obj_id FROM object_data WHERE type='role' AND title LIKE 'il_grp_member%'";
$res = $this->db->query($query);

while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$dest_role_id = $row->obj_id;
	$rolf_arr = $rbacreview->getFoldersAssignedToRole($dest_role_id,true);
	$dest_rolf_id = $rolf_arr[0];
	$rbacadmin->deleteRolePermission($dest_role_id,$dest_rolf_id,false);
	$rbacadmin->copyRolePermission($mem_role_id,ROLE_FOLDER_ID,$dest_rolf_id,$dest_role_id);

	// change existing objects
	$node_id = $tree->getParentId($dest_rolf_id);

	if (empty($node_id))
	{
		continue;
	}

	// GET ALL SUBNODES
	$node_data = $tree->getNodeData($node_id);
	$subtree_nodes = $tree->getSubTree($node_data);

	// GET ALL OBJECTS THAT CONTAIN A ROLE FOLDER
	$all_parent_obj_of_rolf = $rbacreview->getObjectsWithStopedInheritance($dest_role_id);

	// DELETE ACTUAL ROLE FOLDER FROM ARRAY
	$key = array_keys($all_parent_obj_of_rolf,$node_id);
	
	unset($all_parent_obj_of_rolf[$key[0]]);
	
	$check = false;
	
	foreach ($subtree_nodes as $node)
	{
		if (!$check)
		{
			if (in_array($node["child"],$all_parent_obj_of_rolf))
			{
				$lft = $node["lft"];
				$rgt = $node["rgt"];
				$check = true;
				continue;
			}

			$valid_nodes[] = $node;
		}
		else
		{
			if (($node["lft"] > $lft) && ($node["rgt"] < $rgt))
			{
				continue;
			}
			else
			{
				$check = false;
				$valid_nodes[] = $node;
			}
		}
	}
	
	// prepare arrays for permission settings below
	foreach ($valid_nodes as $key => $node)
	{
		$node_ids[] = $node["child"];
		$valid_nodes[$key]["perms"] = $rbacreview->getOperationsOfRole($mem_role_id,$node["type"],ROLE_FOLDER_ID);
	}

	// FIRST REVOKE PERMISSIONS FROM ALL VALID OBJECTS
	$rbacadmin->revokePermissionList($node_ids,$dest_role_id);

	// NOW SET ALL PERMISSIONS
	foreach ($valid_nodes as $node)
	{
		if (is_array($node["perms"]))
		{
			$rbacadmin->grantPermission($dest_role_id,$node["perms"],$node["child"]);
		}
	}
} // end while

// now for grp_admin roles
// fetch obj id of group member role template
$query = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_admin'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$adm_role_id = $row[0];

// update group role templates
// admin role
unset($ops_data);
unset($rolf_arr);
unset($node_data);
unset($node_ids);
unset($valid_nodes);

$ops_data["chat"] = array(1,2,3,4,6);
$ops_data["file"] = array(1,2,3,4,6);
$ops_data["frm"] = array(1,2,3,4,6,9,10);
$ops_data["glo"] = array(1,2,3,4,6,7,8);
$ops_data["grp"] = array(1,2,3,4,6,7,8,18,20,21,22,25,26,29);
$ops_data["lm"] = array(1,2,3,4,6,7,8);
$ops_data["slm"] = array(1,2,3,4,6,7,8);
$ops_data["fold"] = array(1,2,3,4,6,18,20,21,22,25,26);

// sets new template permissions
foreach ($ops_data as $type => $ops)
{
	// delete all template entries for each role
	$rbacadmin->deleteRolePermission($adm_role_id, ROLE_FOLDER_ID,$type);
	$rbacadmin->setRolePermission($adm_role_id, $type, $ops, ROLE_FOLDER_ID);
}

// copy member template settings to all group member roles
$query = "SELECT obj_id FROM object_data WHERE type='role' AND title LIKE 'il_grp_admin%'";
$res = $this->db->query($query);

while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$dest_role_id = $row->obj_id;
	$rolf_arr = $rbacreview->getFoldersAssignedToRole($dest_role_id,true);
	$dest_rolf_id = $rolf_arr[0];
	$rbacadmin->deleteRolePermission($dest_role_id,$dest_rolf_id,false);
	$rbacadmin->copyRolePermission($adm_role_id,ROLE_FOLDER_ID,$dest_rolf_id,$dest_role_id);

	// change existing objects
	$node_id = $tree->getParentId($dest_rolf_id);

	if (empty($node_id))
	{
		continue;
	}

	// GET ALL SUBNODES
	$node_data = $tree->getNodeData($node_id);
	$subtree_nodes = $tree->getSubTree($node_data);

	// GET ALL OBJECTS THAT CONTAIN A ROLE FOLDER
	$all_parent_obj_of_rolf = $rbacreview->getObjectsWithStopedInheritance($dest_role_id);

	// DELETE ACTUAL ROLE FOLDER FROM ARRAY
	$key = array_keys($all_parent_obj_of_rolf,$node_id);
	
	unset($all_parent_obj_of_rolf[$key[0]]);

	$check = false;
	
	foreach ($subtree_nodes as $node)
	{
		if (!$check)
		{
			if (in_array($node["child"],$all_parent_obj_of_rolf))
			{
				$lft = $node["lft"];
				$rgt = $node["rgt"];
				$check = true;
				continue;
			}
	
			$valid_nodes[] = $node;
		}
		else
		{
			if (($node["lft"] > $lft) && ($node["rgt"] < $rgt))
			{
				continue;
			}
			else
			{
				$check = false;
				$valid_nodes[] = $node;
			}
		}
	}

	// prepare arrays for permission settings below
	foreach ($valid_nodes as $key => $node)
	{
		$node_ids[] = $node["child"];
		$valid_nodes[$key]["perms"] = $rbacreview->getOperationsOfRole($adm_role_id,$node["type"],ROLE_FOLDER_ID);
	}

	// FIRST REVOKE PERMISSIONS FROM ALL VALID OBJECTS
	$rbacadmin->revokePermissionList($node_ids,$dest_role_id);
	
	// NOW SET ALL PERMISSIONS
	foreach ($valid_nodes as $node)
	{
		if (is_array($node["perms"]))
		{
			$rbacadmin->grantPermission($dest_role_id,$node["perms"],$node["child"]);
		}
	}
} // end while
?>
<#161>
ALTER  TABLE  `qpl_answers`  ADD  `name` VARCHAR( 50  )  NOT  NULL  AFTER  `question_fi` ;
<#162>
ALTER  TABLE  `qpl_answers`  ADD  `shuffle` ENUM('0','1')  NOT  NULL DEFAULT '1' AFTER  `name` ;

<#163>
UPDATE settings SET value = '3.0.0RC1 2004-04-18' WHERE keyword = 'ilias_version' LIMIT 1;

<#164>
ALTER TABLE `scorm_tracking` ADD `student_name` VARCHAR( 255 ) NOT NULL ;

<#165>
CREATE TABLE scorm_lm
(
	id INT NOT NULL PRIMARY KEY,
	online ENUM('y','n') DEFAULT 'n',
	api_adapter VARCHAR(80) DEFAULT 'API'
);

<#166>
ALTER TABLE scorm_lm ADD COLUMN  api_func_prefix VARCHAR(20) DEFAULT 'LMS';

<#167>
DELETE FROM style_parameter WHERE tag='div' AND class='Page' and parameter='border-spacing';
DELETE FROM style_parameter WHERE tag='div' AND class='Page' and parameter='border-style';
DELETE FROM style_parameter WHERE tag='div' AND class='Page' and parameter='border-color';
DELETE FROM style_parameter WHERE tag='div' AND class='Page' and parameter='border-width';
UPDATE style_parameter SET value='0px' WHERE tag='div' AND class='Page' and parameter='padding';
DELETE FROM style_parameter WHERE tag='div' AND class='LMNavigation' and parameter='padding';
DELETE FROM style_parameter WHERE tag='div' AND class='LMNavigation' and parameter='border-spacing';

<#168>
UPDATE usr_pref SET value='blueshadow' WHERE value='blueshadow_ie' AND keyword='style';

<#169>
ALTER TABLE frm_posts ADD import_name TEXT;
ALTER TABLE frm_threads ADD import_name TEXT;

<#170>
ALTER TABLE content_object ADD COLUMN toc_mode ENUM('chapters','pages') DEFAULT 'chapters';

<#171>
<?php

// add create media pool operation to categories
$query = "SELECT obj_id FROM object_data WHERE type='adm'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$adm_id = $row["obj_id"];

$query = "INSERT INTO object_translation (obj_id, title, description, ".
	"lang_code, lang_default) VALUES ('".$adm_id."','Open Source eLearning'".
	",'','en','1')";
$this->db->query($query);
?>

<#172>
ALTER TABLE mail ADD import_name TEXT;

<#173>
# add operations 'create_svy' and 'create_spl' for survey objects and survey question pools
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('42', 'create_svy', 'create new survey');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('43', 'create_spl', 'create new question pool (Survey)');

<#174>
<?php

// insert survey definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'svy', 'Survey object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operation assignment to survey object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
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
?>

<#175>
<?php

// insert survey question pool definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'spl', 'Question pool object (Survey)', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operation assignment to survey question pool object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
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
?>

<#176>
<?php

// get category type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='cat'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add create_tst and create_qpl operations to category type
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','42')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','43')";
$this->db->query($query);
?>
<#177>
ALTER TABLE `usr_data` CHANGE `login` `login` VARCHAR (80) NOT NULL;
ALTER TABLE `usr_data` CHANGE `email` `email` VARCHAR (80) NOT NULL;

<#178>
CREATE TABLE file_usage
(
	id INT NOT NULL,
	usage_type VARCHAR(10) NOT NULL,
	usage_id INT NOT NULL,
	PRIMARY KEY (id, usage_type, usage_id)
);

<#179>
<?php

// prepare file access to work with safe mode
umask(0117);

// get settings from ini file
$ini = new ilIniFile(CLIENT_WEB_DIR."/client.ini.php");
$ini->read();
$ini->setVariable("layout", "skin", "default");
$ini->setVariable("layout", "style", "blueshadow");
$ini->write();

?>

<#180>
UPDATE usr_pref SET value='blueshadow' WHERE value='blueshadow_ie' AND keyword='style';

<#181>
ALTER  TABLE  `tst_tests`  CHANGE  `processing_time`  `processing_time` TIME;
ALTER  TABLE  `tst_tests`  ADD  `enable_processing_time` ENUM(  '0',  '1'  ) DEFAULT  '0' NOT  NULL  AFTER  `processing_time` ;

<#182>
CREATE TABLE glossary
(
	id INT NOT NULL PRIMARY KEY,
	online ENUM('y','n') DEFAULT 'n'
);

<#183>
<?php
$query = "SELECT obj_id FROM object_data WHERE type='glo'";
$gl_set = $this->db->query($query);

while ($gl_rec = $gl_set->fetchRow(DB_FETCHMODE_ASSOC))
{
	$query = "INSERT INTO glossary (id, online) VALUES ('".$gl_rec["obj_id"]."','y')";
	$this->db->query($query);
}
?>

<#184>
CREATE TABLE scorm_tracking2
(
	user_id INT NOT NULL,
	sco_id INT NOT NULL,
	lvalue VARCHAR(64),
	rvalue TEXT,
	PRIMARY KEY(user_id, sco_id)
);

<#185>
<?php

// add create media pool, exercise, htlm and digilib operation to groups
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='grp'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$typ_id = $row["obj_id"];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','32')";
$this->db->query($query);
?>

<#186>
<?php

// add create media pool, exercise, htlm and digilib operation to groups
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='grp'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
$typ_id = $row["obj_id"];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','23')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','24')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','31')";
$this->db->query($query);
?>

<#187>
<?php

//GET ID OF THE IL_GRP_ADMIN TEMPLATE
$query1 = "SELECT obj_id FROM object_data WHERE title = 'il_grp_admin' ";
$res = $this->db->query($query1);
$tpl = $res->fetchRow(DB_FETCHMODE_ASSOC);

//GET PROPER PARENT_ID
$query2 = "SELECT parent FROM rbac_templates WHERE rol_id = ".$tpl["obj_id"];
$res = $this->db->query($query2);
$rol_fold = $res->fetchRow(DB_FETCHMODE_ASSOC);

$perms = array(		array("type" => "dbk", "ops_id" => 1),
					array("type" => "dbk", "ops_id" => 2),
					array("type" => "dbk", "ops_id" => 3),
					array("type" => "dbk", "ops_id" => 4),
					array("type" => "dbk", "ops_id" => 6));

foreach($perms as $perm)
{
	$q = "REPLACE INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','".$perm["type"]."','".$perm["ops_id"]."','".$rol_fold["parent"]."')";
	$this->db->query($q);
}

?>

<#188>
<?php

//GET ID OF THE IL_GRP_ADMIN TEMPLATE
$query1 = "SELECT obj_id FROM object_data WHERE title = 'il_grp_admin' ";
$res = $this->db->query($query1);
$tpl = $res->fetchRow(DB_FETCHMODE_ASSOC);

//GET PROPER PARENT_ID
$query2 = "SELECT parent FROM rbac_templates WHERE rol_id = ".$tpl["obj_id"];
$res = $this->db->query($query2);
$rol_fold = $res->fetchRow(DB_FETCHMODE_ASSOC);

$perms = array(		array("type" => "exc", "ops_id" => 1),
					array("type" => "exc", "ops_id" => 2),
					array("type" => "exc", "ops_id" => 3),
					array("type" => "exc", "ops_id" => 4),
					array("type" => "exc", "ops_id" => 6),

					array("type" => "grp", "ops_id" => 23),
					array("type" => "grp", "ops_id" => 24),
					array("type" => "grp", "ops_id" => 31),
					array("type" => "grp", "ops_id" => 32),

					array("type" => "htlm", "ops_id" => 1),
					array("type" => "htlm", "ops_id" => 2),
					array("type" => "htlm", "ops_id" => 3),
					array("type" => "htlm", "ops_id" => 4),
					array("type" => "htlm", "ops_id" => 6),

					array("type" => "mep", "ops_id" => 1),
					array("type" => "mep", "ops_id" => 2),
					array("type" => "mep", "ops_id" => 3),
					array("type" => "mep", "ops_id" => 4),
					array("type" => "mep", "ops_id" => 6),

					);

foreach($perms as $perm)
{
	$q = "REPLACE INTO rbac_templates (rol_id,type,ops_id,parent) VALUES ('".$tpl["obj_id"]."','".$perm["type"]."','".$perm["ops_id"]."','".$rol_fold["parent"]."')";
	$this->db->query($q);
}

?>

<#189>
UPDATE settings SET value = '3.0.0 2004-05-15' WHERE keyword = 'ilias_version' LIMIT 1;
<#190>
INSERT  INTO  `qpl_question_type` (  `question_type_id` ,  `type_tag`  ) VALUES ('7',  'qt_javaapplet');
<#191>
ALTER  TABLE  `tst_solutions`  ADD  `points` DOUBLE AFTER  `value2` ;
<#192>
ALTER  TABLE  `qpl_questions`  ADD  `params` TEXT AFTER  `image_file` ;
<#193>
<?php

$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'tax', 'Taxonomy object', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// INSERT NEW OPERATIONS
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','4')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','6')";
$this->db->query($query);


// ADD NEW OPERATION create_chat
$query = "INSERT INTO rbac_operations VALUES('44','create_tax','create taxonomy object')";
$this->db->query($query);

// ADD CREATE PERMISSION FOR TYPE 'adm'
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'adm' ";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO rbac_ta VALUES('".$row->obj_id."','44')";
	$this->db->query($query);
}


$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'taxf', 'Taxonomy folder object', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// INSERT NEW OPERATIONS
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','4')";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('taxf', 'Taxonomy folder', 'Configure taxonomy settings here', -1, now(), now())";
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
?>

<#194>
DROP TABLE IF EXISTS aicc_lm;
CREATE TABLE aicc_lm (
  `id` int(11) NOT NULL default '0',
  `online` enum('y','n') default 'n',
  `api_adapter` varchar(80) default 'API',
  `api_func_prefix` varchar(20) default 'LMS',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


<#195>
ALTER TABLE scorm_tracking2 DROP PRIMARY KEY;
ALTER TABLE scorm_tracking2 MODIFY lvalue VARCHAR(64) NOT NULL;
ALTER TABLE scorm_tracking2 ADD PRIMARY KEY (user_id, sco_id, lvalue);

<#196>
# add operations 'invite' and 'participate' for survey objects
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('45', 'invite', 'invite');
INSERT INTO rbac_operations (ops_id,operation,description) VALUES ('46', 'participate', 'participate');

<#197>
<?php

// retrieve survey object data
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='svy'";
$result = $this->db->query($query);
$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
$typ_id = $row->obj_id;

// append operation assignment to survey object definition
// 45: invite, 46: participate
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','45')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','46')";
$this->db->query($query);
?>

<#198>
DROP TABLE IF EXISTS aicc_lm;
CREATE TABLE aicc_lm (
  `id` int(11) NOT NULL default '0',
  `online` enum('y','n') default 'n',
  `api_adapter` varchar(80) default 'API',
  `api_func_prefix` varchar(20) default 'LMS',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


<#199>
ALTER TABLE scorm_tracking2 DROP PRIMARY KEY;
ALTER TABLE scorm_tracking2 MODIFY lvalue VARCHAR(64) NOT NULL;
ALTER TABLE scorm_tracking2 ADD PRIMARY KEY (user_id, sco_id, lvalue);

<#200>
CREATE TABLE `ut_access` (
	`obj_id` int(10) NOT NULL auto_increment,
	`user_id` int(10) unsigned NOT NULL default '0',
	`action_type` char(10) default NULL,
	`php_script` char(10) default NULL,
	`client_ip` char(15) default NULL,
	`acc_obj_type` char(10) NOT NULL default '',
	`acc_obj_id` int(10) NOT NULL default '0',
	`sub_type` char(10) NOT NULL default '',
	`sub_id` int(10) NOT NULL default '0',
	`language` char(15) default NULL,
	`browser` char(10) default NULL,
	`session_id` char(40) default NULL,
	`acctime` datetime default NULL,
	PRIMARY KEY (`obj_id`)
) TYPE=MyISAM AUTO_INCREMENT=1;

<#201>
ALTER TABLE ut_access CHANGE obj_id id int(10) NOT NULL auto_increment;
ALTER TABLE ut_access CHANGE acctime acc_time datetime default NULL;
ALTER TABLE ut_access CHANGE sub_type acc_sub_type char(10) NOT NULL default '';
ALTER TABLE ut_access CHANGE sub_id acc_sub_id int(10) NOT NULL default '0';

<#202>
ALTER TABLE ut_access CHANGE browser browser char(60) NOT NULL default '';

<#203>
ALTER TABLE ut_access CHANGE php_script php_script char(100) NOT NULL default '';

<#204>
# Create all tables for survey tool

DROP TABLE IF EXISTS `survey_answer`;
CREATE TABLE `survey_answer` (
  `answer_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `value` double default NULL,
  `textanswer` text,
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`answer_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_category`;
CREATE TABLE `survey_category` (
  `category_id` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `defaultvalue` enum('0','1') NOT NULL default '0',
  `owner_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`category_id`)
) TYPE=MyISAM AUTO_INCREMENT=36 ;

INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (1, 'dc_desired', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (2, 'dc_undesired', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (3, 'dc_agree', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (4, 'dc_disagree', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (5, 'dc_good', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (6, 'dc_notacceptable', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (7, 'dc_should', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (8, 'dc_shouldnot', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (9, 'dc_true', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (10, 'dc_false', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (11, 'dc_always', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (12, 'dc_never', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (13, 'dc_yes', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (14, 'dc_no', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (15, 'dc_neutral', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (16, 'dc_undecided', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (17, 'dc_fair', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (18, 'dc_sometimes', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (19, 'dc_stronglydesired', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (20, 'dc_stronglyundesired', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (21, 'dc_stronglyagree', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (22, 'dc_stronglydisagree', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (23, 'dc_verygood', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (24, 'dc_poor', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (25, 'dc_must', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (26, 'dc_mustnot', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (27, 'dc_definitelytrue', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (28, 'dc_definitelyfalse', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (29, 'dc_manytimes', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (30, 'dc_varying', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (31, 'dc_rarely', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (32, 'dc_mostcertainly', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (33, 'dc_morepositive', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (34, 'dc_morenegative', '1', 0, 20040522134301);
INSERT INTO `survey_category` (`category_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (35, 'dc_mostcertainlynot', '1', 0, 20040522134301);

DROP TABLE IF EXISTS `survey_constraint`;
CREATE TABLE `survey_constraint` (
  `constraint_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL default '0',
  `relation_fi` int(11) NOT NULL default '0',
  `value` double NOT NULL default '0',
  PRIMARY KEY  (`constraint_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_finished`;
CREATE TABLE `survey_finished` (
  `finished_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `state` enum('0','1') NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`finished_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_invited_group`;
CREATE TABLE `survey_invited_group` (
  `invited_group_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `group_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`invited_group_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_invited_user`;
CREATE TABLE `survey_invited_user` (
  `invited_user_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`invited_user_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_phrase`;
CREATE TABLE `survey_phrase` (
  `phrase_id` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `defaultvalue` enum('0','1','2') NOT NULL default '0',
  `owner_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`phrase_id`)
) TYPE=MyISAM AUTO_INCREMENT=22 ;

INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (1, 'dp_standard_attitude_desired_undesired', '1', 0, 20040522135431);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (2, 'dp_standard_attitude_agree_disagree', '1', 0, 20040522135458);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (3, 'dp_standard_attitude_good_notacceptable', '1', 0, 20040522135518);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (4, 'dp_standard_attitude_shold_shouldnot', '1', 0, 20040522135546);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (5, 'dp_standard_beliefs_true_false', '1', 0, 20040522135613);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (6, 'dp_standard_beliefs_always_never', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (7, 'dp_standard_behaviour_yes_no', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (8, 'dp_standard_attitude_desired_neutral_undesired', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (9, 'dp_standard_attitude_agree_undecided_disagree', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (10, 'dp_standard_attitude_good_fair_notacceptable', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (11, 'dp_standard_attitude_should_undecided_shouldnot', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (12, 'dp_standard_beliefs_true_undecided_false', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (13, 'dp_standard_beliefs_always_sometimes_never', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (14, 'dp_standard_behaviour_yes_undecided_no', '1', 0, 20040522140547);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (15, 'dp_standard_attitude_desired5', '1', 0, 20040522150702);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (16, 'dp_standard_attitude_agree5', '1', 0, 20040522150717);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (17, 'dp_standard_attitude_good5', '1', 0, 20040522150729);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (18, 'dp_standard_attitude_must5', '1', 0, 20040522150744);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (19, 'dp_standard_beliefs_true5', '1', 0, 20040522150754);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (20, 'dp_standard_beliefs_always5', '1', 0, 20040522150812);
INSERT INTO `survey_phrase` (`phrase_id`, `title`, `defaultvalue`, `owner_fi`, `TIMESTAMP`) VALUES (21, 'dp_standard_behaviour_certainly5', '1', 0, 20040522150828);

DROP TABLE IF EXISTS `survey_phrase_category`;
CREATE TABLE `survey_phrase_category` (
  `phrase_category_id` int(11) NOT NULL auto_increment,
  `phrase_fi` int(11) NOT NULL default '0',
  `category_fi` int(11) NOT NULL default '0',
  `sequence` int(11) NOT NULL default '0',
  PRIMARY KEY  (`phrase_category_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_question`;
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
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`question_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_question_constraint`;
CREATE TABLE `survey_question_constraint` (
  `question_constraint_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `constraint_fi` int(11) NOT NULL default '0',
  PRIMARY KEY  (`question_constraint_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_question_material`;
CREATE TABLE `survey_question_material` (
  `material_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL default '0',
  `materials` text,
  `materials_file` varchar(200) NOT NULL default '',
  UNIQUE KEY `material_id` (`material_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_questionblock`;
CREATE TABLE `survey_questionblock` (
  `questionblock_id` int(11) NOT NULL auto_increment,
  `title` varchar(100) default NULL,
  `obligatory` enum('0','1') NOT NULL default '0',
  `owner_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`questionblock_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_questionblock_question`;
CREATE TABLE `survey_questionblock_question` (
  `questionblock_question_id` int(11) NOT NULL auto_increment,
  `questionblock_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  PRIMARY KEY  (`questionblock_question_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_questiontype`;
CREATE TABLE `survey_questiontype` (
  `questiontype_id` int(11) NOT NULL auto_increment,
  `type_tag` varchar(30) NOT NULL default '',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`questiontype_id`)
) TYPE=MyISAM AUTO_INCREMENT=5 ;

INSERT INTO `survey_questiontype` (`questiontype_id`, `type_tag`, `TIMESTAMP`) VALUES (1, 'qt_nominal', 20040518222841);
INSERT INTO `survey_questiontype` (`questiontype_id`, `type_tag`, `TIMESTAMP`) VALUES (2, 'qt_ordinal', 20040518222848);
INSERT INTO `survey_questiontype` (`questiontype_id`, `type_tag`, `TIMESTAMP`) VALUES (3, 'qt_metric', 20040518222859);
INSERT INTO `survey_questiontype` (`questiontype_id`, `type_tag`, `TIMESTAMP`) VALUES (4, 'qt_text', 20040518222904);

DROP TABLE IF EXISTS `survey_relation`;
CREATE TABLE `survey_relation` (
  `relation_id` int(11) NOT NULL auto_increment,
  `long` varchar(20) NOT NULL default '',
  `short` char(2) NOT NULL default '',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`relation_id`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

INSERT INTO `survey_relation` (`relation_id`, `long`, `short`, `TIMESTAMP`) VALUES (1, 'less', '<', 20040518195753);
INSERT INTO `survey_relation` (`relation_id`, `long`, `short`, `TIMESTAMP`) VALUES (2, 'less or equal', '<=', 20040518195808);
INSERT INTO `survey_relation` (`relation_id`, `long`, `short`, `TIMESTAMP`) VALUES (3, 'equal', '=', 20040518195816);
INSERT INTO `survey_relation` (`relation_id`, `long`, `short`, `TIMESTAMP`) VALUES (4, 'not equal', '<>', 20040518195839);
INSERT INTO `survey_relation` (`relation_id`, `long`, `short`, `TIMESTAMP`) VALUES (5, 'more or equal', '>=', 20040518195852);
INSERT INTO `survey_relation` (`relation_id`, `long`, `short`, `TIMESTAMP`) VALUES (6, 'more', '>', 20040518195903);

DROP TABLE IF EXISTS `survey_survey`;
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
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_survey_question`;
CREATE TABLE `survey_survey_question` (
  `survey_question_id` int(11) NOT NULL auto_increment,
  `survey_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `sequence` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`survey_question_id`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS `survey_variable`;
CREATE TABLE `survey_variable` (
  `variable_id` int(11) NOT NULL auto_increment,
  `category_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `value1` double default NULL,
  `value2` double default NULL,
  `sequence` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`variable_id`)
) TYPE=MyISAM ;

<#205>

ALTER TABLE `survey_questionblock_question` ADD `survey_fi` INT NOT NULL AFTER `questionblock_question_id` ;

<#206>
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

DROP TABLE IF EXISTS `aicc_object`;
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

DROP TABLE IF EXISTS `aicc_tree`;
CREATE TABLE `aicc_tree` (
  `alm_id` int(11) NOT NULL default '0',
  `child` int(11) unsigned NOT NULL default '0',
  `parent` int(11) unsigned default NULL,
  `lft` int(11) unsigned NOT NULL default '0',
  `rgt` int(11) unsigned NOT NULL default '0',
  `depth` smallint(5) unsigned NOT NULL default '0',
  KEY `child` (`child`),
  KEY `parent` (`parent`)
) TYPE=MyISAM;

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

<#207>
INSERT  INTO  `survey_phrase` (`phrase_id` ,  `title` ,  `defaultvalue` ,  `owner_fi` ,  `TIMESTAMP`) VALUES (NULL,  'dp_standard_numbers',  '1',  '0', NOW());

<#208>
ALTER TABLE scorm_tracking2 ADD COLUMN ref_id INT NOT NULL DEFAULT '0';
<#209>
ALTER  TABLE  `survey_question`  ADD  `original_id` INT DEFAULT  '0' NOT  NULL  AFTER  `created` ;
<#210>

INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'1','1','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'1','2','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'2','3','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'2','4','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'3','5','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'3','6','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'4','7','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'4','8','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'5','9','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'5','10','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'6','11','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'6','12','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'7','13','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'7','14','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'8','1','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'8','15','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'8','2','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'9','3','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'9','16','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'9','4','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'10','5','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'10','17','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'10','6','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'11','7','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'11','16','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'11','8','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'12','9','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'12','16','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'12','10','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'13','11','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'13','18','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'13','12','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'14','13','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'14','16','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'14','14','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'15','19','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'15','1','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'15','15','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'15','2','4');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'15','20','5');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'16','21','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'16','3','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'16','16','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'16','4','4');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'16','22','5');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'17','23','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'17','5','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'17','17','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'17','24','4');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'17','6','5');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'18','25','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'18','7','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'18','16','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'18','8','4');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'18','26','5');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'19','27','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'19','9','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'19','16','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'19','10','4');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'19','28','5');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'20','11','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'20','29','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'20','30','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'20','31','4');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'20','12','5');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'21','32','1');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'21','33','2');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'21','16','3');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'21','34','4');
INSERT INTO `survey_phrase_category` (`phrase_category_id`,`phrase_fi`,`category_fi`,`sequence`) VALUES (NULL,'21','35','5');
<#211>
ALTER  TABLE  `qpl_questions`  CHANGE  `question_id`  `question_id` INT( 11  ) DEFAULT  '0' NOT  NULL  AUTO_INCREMENT;

<#212>
DROP TABLE IF EXISTS scorm_tracking;
RENAME TABLE scorm_tracking2 TO scorm_tracking;

<#213>
ALTER TABLE scorm_lm ADD COLUMN credit ENUM('credit','no_credit') NOT NULL DEFAULT 'credit';

<#214>
ALTER TABLE scorm_lm ADD COLUMN default_lesson_mode ENUM('normal','browse') NOT NULL DEFAULT 'normal';
ALTER TABLE scorm_lm ADD COLUMN auto_review ENUM('y','n') NOT NULL DEFAULT 'n';

<#215>
ALTER TABLE content_object ADD COLUMN clean_frames ENUM('y','n') NOT NULL DEFAULT 'n';

<#216>
<?php
// get all users
$q = "SELECT usr_id FROM usr_data";
$user_set = $this->db->query($q);

while ($user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC))
{
	$q = "REPLACE INTO usr_pref (usr_id, keyword, value) VALUES ".
		" ('".$user_rec["usr_id"]."','show_users_online','y')";
	$this->db->query($q);
}
?>

<#217>
ALTER  TABLE  `qpl_question_material`  ADD  `materials_file` TEXT;
ALTER  TABLE  `qpl_question_material`  ADD  `TIMESTAMP` TIMESTAMP NOT  NULL ;

<#218>
ALTER  TABLE  `qpl_questions`  DROP  `imagemap_file`;

<#219>
ALTER  TABLE  `qpl_questions`  ADD  `original_id` INT AFTER  `created` ;

<#220>
<?php
// duplicate all test questions. work with duplicates instead of references
$query = "SELECT test_question_id, question_fi, test_fi FROM tst_test_question";
$search_result = $this->db->query($query);
while ($result_row = $search_result->fetchRow(DB_FETCHMODE_OBJECT))
{
	$question_id = $result_row->question_fi;
	$query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
		$this->db->quote($question_id)
	);
	$result = $this->db->query($query);
  if (strcmp(get_class($result), db_result) == 0) {
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, ref_fi, title, comment, author, owner, question_text, working_time, shuffle, points, start_tag, end_tag, matching_type, ordering_type, cloze_type, choice_response, materials, image_file, params, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$this->db->quote("$row->question_type_fi"),
				$this->db->quote("$row->ref_fi"),
				$this->db->quote("$row->title"),
				$this->db->quote("$row->comment"),
				$this->db->quote("$row->author"),
				$this->db->quote("$row->owner"),
				$this->db->quote("$row->question_text"),
				$this->db->quote("$row->working_time"),
				$this->db->quote("$row->shuffle"),
				$this->db->quote("$row->points"),
				$this->db->quote("$row->start_tag"),
				$this->db->quote("$row->end_tag"),
				$this->db->quote("$row->matching_type"),
				$this->db->quote("$row->ordering_type"),
				$this->db->quote("$row->cloze_type"),
				$this->db->quote("$row->choice_response"),
				$this->db->quote("$row->materials"),
				$this->db->quote("$row->image_file"),
				$this->db->quote("$row->params"),
				$this->db->quote("$row->complete"),
				$this->db->quote("$row->created"),
				$this->db->quote("$row->question_id")
			);
			$result = $this->db->query($query);
			$duplicate_id = $this->db->getLastInsertId();
			$query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s",
				$this->db->quote($question_id)
			);
			$result = $this->db->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$insertquery = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, name, shuffle, answertext, points, aorder, correctness, solution_order, matchingtext, matching_order, gap_id, cloze_type, coords, area, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
					$this->db->quote("$duplicate_id"),
					$this->db->quote("$row->name"),
					$this->db->quote("$row->shuffle"),
					$this->db->quote("$row->answertext"),
					$this->db->quote("$row->points"),
					$this->db->quote("$row->aorder"),
					$this->db->quote("$row->correctness"),
					$this->db->quote("$row->solution_order"),
					$this->db->quote("$row->matchingtext"),
					$this->db->quote("$row->matching_order"),
					$this->db->quote("$row->gap_id"),
					$this->db->quote("$row->cloze_type"),
					$this->db->quote("$row->coords"),
					$this->db->quote("$row->area")
				);
				$insertresult = $this->db->query($insertquery);
			}

			// copy question materials
			$query = sprintf("SELECT * FROM qpl_question_material WHERE question_id = %s",
				$this->db->quote($question_id)
			);
			$result = $this->db->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$insertquery = sprintf("INSERT INTO qpl_question_material (material_id, question_id, materials, materials_file, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
					$this->db->quote("$duplicate_id"),
					$this->db->quote("$row->materials"),
					$this->db->quote("$row->materials_file")
				);
				$insertresult = $this->db->query($insertquery);
			}

			$query = sprintf("UPDATE tst_test_question SET question_fi = %s WHERE test_question_id = %s",
				$this->db->quote($duplicate_id),
				$this->db->quote($result_row->test_question_id)
			);
			$result = $this->db->query($query);

			$query = sprintf("UPDATE tst_solutions SET question_fi = %s WHERE test_fi = %s AND question_fi = %s",
				$this->db->quote($duplicate_id),
				$this->db->quote($result_row->test_fi),
				$this->db->quote($result_row->question_fi)
			);
			$result = $this->db->query($query);
		}
	}
}
?>

<#221>
UPDATE settings SET value = '3.0.1 2004-06-21' WHERE keyword = 'ilias_version' LIMIT 1;

<#222>
ALTER  TABLE  `tst_tests`  ADD  `ending_time` varchar(14) default NULL AFTER  `starting_time` ;
<#223>

ALTER  TABLE  `survey_question`  CHANGE  `original_id`  `original_id` INT( 11  ) ;
UPDATE `survey_question` SET `original_id` = NULL WHERE `original_id` = 0;

<#224>

CREATE TABLE ctrl_classfile
(
	class		VARCHAR(100) NOT NULL PRIMARY KEY,
	file		VARCHAR(250)
);

CREATE TABLE ctrl_calls
(
	parent		VARCHAR(100) NOT NULL,
	child		VARCHAR(100)
);

<#225>
DROP TABLE IF EXISTS `aicc_lm`;
DROP TABLE IF EXISTS `aicc_tree`;

<#226>
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

<#227>
<?php

// get visible id
$query = "SELECT * FROM rbac_operations WHERE operation='visible'";
$result = $this->db->query($query);
$visible_id = "";
if ($result->numRows() == 1)
{
	$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
	$visible_id = $row["ops_id"];
}

// get participate id
$query = "SELECT * FROM rbac_operations WHERE operation='participate'";
$result = $this->db->query($query);
$participate_id = "";
if ($result->numRows() == 1)
{
	$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
	$participate_id = $row["ops_id"];
}

// check if User role still exists
$query = "SELECT * FROM object_data WHERE type='role' AND title='User'";
$result = $this->db->query($query);
$user_id = "";
if ($result->numRows() == 1)
{
	$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
	$user_id = $row["obj_id"];
}

// check if Guest role still exists
$query = "SELECT * FROM object_data WHERE type='role' AND title='Guest'";
$result = $this->db->query($query);
$guest_id = "";
if ($result->numRows() == 1)
{
	$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
	$guest_id = $row["obj_id"];
}

// create default roles for assessment tests

if ($user_id and $visible_id)
{
	// tests visible for users
	$query = sprintf("INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (%s, %s, %s, %s)",
		$this->db->quote($user_id),
		$this->db->quote("tst"),
		$this->db->quote($visible_id),
		ROLE_FOLDER_ID
	);
	$result = $this->db->query($query);
}

if ($guest_id and $visible_id)
{
	// tests visible for guests
	$query = sprintf("INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (%s, %s, %s, %s)",
		$this->db->quote($guest_id),
		$this->db->quote("tst"),
		$this->db->quote($visible_id),
		ROLE_FOLDER_ID
	);
	$result = $this->db->query($query);
}

// create default roles for surveys

if ($user_id and $visible_id)
{
	// surveys visible for users
	$query = sprintf("INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (%s, %s, %s, %s)",
		$this->db->quote($user_id),
		$this->db->quote("svy"),
		$this->db->quote($visible_id),
		ROLE_FOLDER_ID
	);
	$result = $this->db->query($query);
}

if ($guest_id and $visible_id)
{
	// surveys visible for guests
	$query = sprintf("INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (%s, %s, %s, %s)",
		$this->db->quote($guest_id),
		$this->db->quote("svy"),
		$this->db->quote($visible_id),
		ROLE_FOLDER_ID
	);
	$result = $this->db->query($query);
}

if ($user_id and $participate_id)
{
	// users can participate surveys
	$query = sprintf("INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (%s, %s, %s, %s)",
		$this->db->quote($user_id),
		$this->db->quote("svy"),
		$this->db->quote($participate_id),
		ROLE_FOLDER_ID
	);
	$result = $this->db->query($query);
}

if ($guest_id and $participate_id)
{
	// guests can participate surveys
	$query = sprintf("INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (%s, %s, %s, %s)",
		$this->db->quote($guest_id),
		$this->db->quote("svy"),
		$this->db->quote($participate_id),
		ROLE_FOLDER_ID
	);
	$result = $this->db->query($query);
}

?>

<#228>
<?php

// register new object type 'trac' for Tracking Item
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'trac', 'UserTracking object', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('trac', '__User Tracking', 'System user tracking', -1, now(), now())";
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

// register RECOVERY_FOLDER_ID in table settings
$query = "INSERT INTO settings (keyword,value) VALUES('sys_user_tracking_id','".$row->id."')";
$res = $this->db->query($query);
?>

<#229>
ALTER TABLE `survey_questionblock` CHANGE `title` `title` TEXT DEFAULT NULL;

<#230>
REPLACE INTO settings (keyword, value) VALUES ('enable_tracking', 0);
REPLACE INTO settings (keyword, value) VALUES ('save_user_related_data', 0);

<#231>
<?php

$ilCtrlStructureReader->getStructure();

?>

<#232>
<?php

$ilCtrlStructureReader->getStructure();

?>

<#233>
<?php

// insert media pool definition in object_data
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'trac'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operations to media pool
// 1: edit_permissions, 2: visible, 3: read, 6:delete
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','6')";
$this->db->query($query);

?>

<#234>
UPDATE settings SET value = '3.1.0_beta1 2004-07-11' WHERE keyword = 'ilias_version' LIMIT 1;

<#235>
<?php

$ilCtrlStructureReader->getStructure();

?>

<#236>
ALTER TABLE `aicc_object` CHANGE `alm_id` `slm_id` INT( 11 ) DEFAULT '0' NOT NULL;

<#237>
<?php
// ADMIN TEMPLATE
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('rolt', 'il_crs_admin', 'Administrator template for course admins', -1, now(), now())";

$this->db->query($query);

// Get id of admin_template
$query = "SELECT obj_id FROM object_data WHERE type = 'rolt' AND title = 'il_crs_admin'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$admin_id = $row->obj_id;
}

$admin["lm"] = array(1,2,3,4,6,7,8);
$admin["slm"] = array(1,2,3,4,6,7,8);
$admin["dbk"] = array(1,2,3,4,6);
$admin["glo"] = array(1,2,3,4,6,7,8);
$admin["frm"] = array(1,2,3,4,6,9,10);
$admin["chat"] = array(1,2,3,4,6);
$admin["file"] = array(1,2,3,4,6);
$admin["tst"] = array(1,2,3,4,6);
$admin["grp"] = array(1,2,3,4,6,7,8,18,20,21,22,23,24,25,26,27,29,31);
$admin["exc"] = array(1,2,3,4,6);
$admin["fold"] = array(1,2,3,4,6,18,20,21,22,25,26,29);
$admin["crs"] = array(1,2,3,4,6,7,8,17,18,20,21,22,23,24,25,26,27,29,31,32);

$rbacadmin =& new ilRbacAdmin();

foreach($admin as $type => $ops)
{
	$rbacadmin->setRolePermission($admin_id,$type,$ops,ROLE_FOLDER_ID);
}
$rbacadmin->assignRoleToFolder($admin_id,ROLE_FOLDER_ID,"n");

// TUTOR TEMPLATE
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('rolt', 'il_crs_tutor', 'Tutor template for course tutors', -1, now(), now())";

$this->db->query($query);

// Get id of admin_template
$query = "SELECT obj_id FROM object_data WHERE type = 'rolt' AND title = 'il_crs_tutor'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$tutor_id = $row->obj_id;
}
$admin["lm"] = array(2,3,4);
$admin["slm"] = array(2,3,4,7,8);
$admin["dbk"] = array(2,3,4);
$admin["glo"] = array(2,3,4,7,8);
$admin["frm"] = array(2,3,4,9);
$admin["chat"] = array(2,3,4);
$admin["file"] = array(2,3,4);
$admin["tst"] = array(2,3,4);
$admin["grp"] = array(2,3,4,7,8);
$admin["exc"] = array(2,3,4);
$admin["fold"] = array(2,3,4);
$admin["crs"] = array(2,3,4,7,8);

$rbacadmin =& new ilRbacAdmin();

foreach($admin as $type => $ops)
{
	$rbacadmin->setRolePermission($tutor_id,$type,$ops,ROLE_FOLDER_ID);
}
$rbacadmin->assignRoleToFolder($tutor_id,ROLE_FOLDER_ID,"n");

// MEMBER TEMPLATE
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('rolt', 'il_crs_member', 'Member template for course members', -1, now(), now())";

$this->db->query($query);

// Get id of admin_template
$query = "SELECT obj_id FROM object_data WHERE type = 'rolt' AND title = 'il_crs_member'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$member_id = $row->obj_id;
}
$admin["lm"] = array(2,3);
$admin["slm"] = array(2,3,7,8);
$admin["dbk"] = array(2,3);
$admin["glo"] = array(2,3,7,8);
$admin["frm"] = array(2,3);
$admin["chat"] = array(2,3);
$admin["file"] = array(2,3);
$admin["tst"] = array(2,3);
$admin["grp"] = array(2,3,7,8);
$admin["exc"] = array(2,3);
$admin["fold"] = array(2,3);
$admin["crs"] = array(2,3,7,8);

$rbacadmin =& new ilRbacAdmin();

foreach($admin as $type => $ops)
{
	$rbacadmin->setRolePermission($member_id,$type,$ops,ROLE_FOLDER_ID);
}

$rbacadmin->assignRoleToFolder($member_id,ROLE_FOLDER_ID,"n");
?>
<#238>
<?php

$ilCtrlStructureReader->getStructure();

?>

<#239>
CREATE TABLE `crs_items` (
  `parent_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `activation_unlimited` tinyint(2) default NULL,
  `activation_start` int(8) default NULL,
  `activation_end` int(8) default NULL,
  `position` int(11) default NULL,
  PRIMARY KEY  (`parent_id`,`obj_id`)
) TYPE=MyISAM;
CREATE TABLE `crs_members` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `status` tinyint(2) NOT NULL default '0',
  `role` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`,`usr_id`)
) TYPE=MyISAM;
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
CREATE TABLE `crs_subscribers` (
  `usr_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `sub_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`obj_id`)
) TYPE=MyISAM;

<#240>
CREATE TABLE `crs_archives` (
  `archive_id` int(11) NOT NULL auto_increment,
  `course_id` int(11) NOT NULL default '0',
  `archive_name` varchar(255) NOT NULL default '',
  `archive_type` tinyint(2) NOT NULL default '0',
  `archive_date` int(11) default NULL,
  `archive_size` int(11) default NULL,
  PRIMARY KEY  (`archive_id`)
) TYPE=MyISAM;

<#241>
ALTER TABLE `crs_archives` ADD `archive_lang` VARCHAR( 16 ) ;

<#242>
ALTER  TABLE  `tst_tests`  CHANGE  `ref_fi`  `obj_fi` INT( 11  ) DEFAULT  '0' NOT  NULL;
ALTER  TABLE  `qpl_questions`  CHANGE  `ref_fi`  `obj_fi` INT( 10  ) UNSIGNED DEFAULT  '0' NOT  NULL;
<#243>
<?php

// convert tst_tests reference id's to object id's
$query = "SELECT object_reference.obj_id, tst_tests.obj_fi, tst_tests.test_id FROM object_reference, tst_tests WHERE tst_tests.obj_fi = object_reference.ref_id";
$result = $this->db->query($query);
while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	$query = sprintf("UPDATE `tst_tests` SET `obj_fi` = %s WHERE `test_id` = %s",
		$this->db->quote($row["obj_id"]),
		$this->db->quote($row["test_id"])
	);
	$insert_result = $this->db->query($query);
}

// convert qpl_questions reference id's to object id's
$query = "SELECT object_reference.obj_id, qpl_questions.obj_fi, qpl_questions.question_id FROM object_reference, qpl_questions WHERE qpl_questions.obj_fi = object_reference.ref_id";
$result = $this->db->query($query);
while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	$query = sprintf("UPDATE `qpl_questions` SET `obj_fi` = %s WHERE `question_id` = %s",
		$this->db->quote($row["obj_id"]),
		$this->db->quote($row["question_id"])
	);
	$insert_result = $this->db->query($query);
}

?>

<#244>
<?php
ilUtil::makeDir(CLIENT_WEB_DIR."/assessment/");
// convert material path names in web directory from CLIENT_WEB_DIR . "/assessment/ref_id/" to CLIENT_WEB_DIR . "/assessment/obj_id/";
$d = opendir(CLIENT_WEB_DIR . "/assessment/") or die($php_errormsg);
while (false !== ($f = readdir($d))) {
	if (preg_match("/\d+/", $f))
	{
		$query = sprintf("SELECT obj_id FROM object_reference WHERE ref_id = %s",
			$this->db->quote($f)
		);
		$result = $this->db->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
    	rename(CLIENT_WEB_DIR . "/assessment/$f", CLIENT_WEB_DIR . "/assessment/" . $row["obj_id"]);
		}
	}
}
closedir($d);
?>

<#245>
<?php
// add auth object by using deprecated ldap object in database
$query = "SELECT obj_id FROM object_data WHERE type = 'ldap' LIMIT 1";
$res = $this->db->query($query);
$row = $res->fetchRow();
$obj_id = $row[0];

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$obj_id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$ref_id = $row[0];

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($ref_id,SYSTEM_FOLDER_ID);
?>

<#246>
UPDATE object_data SET type = 'auth', title = 'Authentication settings', description = 'Select and configure authentication mode for all user accounts' WHERE type = 'ldap' LIMIT 1;
UPDATE object_data SET title = 'auth', description = 'Authentication settings' WHERE type = 'typ' AND title = 'ldap' AND owner = '-1' LIMIT 1;
INSERT INTO settings (keyword,value) VALUES ('auth_mode',1);

<#247>
<?php

$ilCtrlStructureReader->getStructure();

?>
<#248>
CREATE TABLE `conditions` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`target_ref_id` INT( 11 ) NOT NULL ,
`target_obj_id` INT( 11 ) NOT NULL ,
`target_type` VARCHAR( 8 ) NOT NULL ,
`trigger_ref_id` INT( 11 ) NOT NULL ,
`trigger_obj_id` INT( 11 ) NOT NULL ,
`trigger_type` VARCHAR( 8 ) NOT NULL ,
`operation` VARCHAR( 64 ) ,
`value` VARCHAR( 64 ) ,
PRIMARY KEY ( `id` )
);
<#249>
ALTER TABLE `crs_members` ADD `passed` TINYINT( 1 ) ;
<#250>
ALTER TABLE `conditions` CHANGE `operation` `operator` VARCHAR( 64 ) DEFAULT NULL;
<#251>
ALTER TABLE usr_data ADD COLUMN `time_limit_owner` int(10) default '0';
ALTER TABLE usr_data ADD COLUMN `time_limit_unlimited` int(2) default '0';
ALTER TABLE usr_data ADD COLUMN `time_limit_from` int(10) default '0';
ALTER TABLE usr_data ADD COLUMN `time_limit_until` int(10) default '0';

<#252>
UPDATE usr_data SET time_limit_unlimited = '1';

<#253>
<?php

$ilCtrlStructureReader->getStructure();

?>
<#254>
ALTER TABLE `chat_user` CHANGE `last_conn_timestamp` `last_conn_timestamp` INT( 14 ) DEFAULT NULL;
<#255>
DELETE FROM chat_user;
<#256>
<?php
// add operation create_file to categories
$query = "SELECT ops_id FROM rbac_operations WHERE operation = 'create_file'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ops_id = $row->ops_id;
}
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'cat'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$obj_id = $row->obj_id;
}
if($obj_id and $ops_id)
{
	$query = "INSERT INTO rbac_ta VALUES('".$obj_id."','".$ops_id."')";
	$res = $this->db->query($query);
}
// assign operation to global author template
$query = "SELECT obj_id FROM object_data ".
	"WHERE type = 'rolt' AND title = 'Author'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$role_id = $row->obj_id;
}
if($role_id and $ops_id)
{
	$query = "INSERT INTO rbac_templates ".
		"VALUES('".$role_id."','cat','".$ops_id."','".ROLE_FOLDER_ID."')";

	$res = $this->db->query($query);
}
?>

<#257>
# INSERT INTO `object_data` VALUES (38, 'typ', 'alm', 'AICC Learning Module', -1, '2003-08-15 10:07:28', '2003-08-15 12:23:10', '');
# INSERT INTO `object_data` VALUES (39, 'typ', 'hlm', 'HACP Learning Module', -1, '2003-08-15 10:07:28', '2003-08-15 12:23:10', '');

<?php
$query = "SELECT ops_id FROM rbac_ta WHERE typ_id = '20'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ops_id = $row->ops_id;
}
$query = "insert into rbac_ta VALUES('38', '".$ops_id."')";
//$this->db->query($query);

$query = "insert into rbac_ta VALUES('39', '".$ops_id."')";
//$this->db->query($query);
?>
<#258>
<?php
// fix: remove duplicate auth entry in tree or references table
$query = "SELECT ref_id FROM object_reference LEFT JOIN object_data ON object_data.obj_id = object_reference.obj_id WHERE object_data.type = 'auth'";
$res = $this->db->query($query);

if ($res->numRows() > 1)
{
	$tree = new ilTree(ROOT_FOLDER_ID);

	while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$query = "SELECT * FROM tree WHERE child='".$row->ref_id."' AND parent='9'";
		$res2 = $this->db->query($query);

		if ($res2->numRows() == 1)
		{
			continue;
		}
		else
		{
			$query = "DELETE FROM tree WHERE child='".$row->ref_id."'";
			$this->db->query($query);
			$query = "DELETE FROM object_reference WHERE ref_id='".$row->ref_id."'";
			$this->db->query($query);
		}
	}

	$tree->renumber();
 }
?>
<#259>
ALTER TABLE usr_data ADD COLUMN `time_limit_message` int(2) default '0';

<#260>
# <-257
# DELETE FROM rbac_ta WHERE (typ_id=38 or typ_id=39);

<?php
$query = "SELECT ops_id FROM rbac_ta WHERE typ_id = '20'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ops_id = $row->ops_id;

	$query = "insert into rbac_ta VALUES('38', '".$ops_id."')";
	//$this->db->query($query);

	$query = "insert into rbac_ta VALUES('39', '".$ops_id."')";
	//$this->db->query($query);

}
?>

<#261>
#
# Remove materials table for assessment questions
# The materials are no longer needed because of the
# filelists in the PageObjects now uses in question text
#

DROP  TABLE `qpl_question_material`;

<#262>
<?php

  // INSERT TYPE ENTRY
$query = "INSERT object_data VALUES('','typ','pays','Payment settings',-1,NOW(),NOW(),'')";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$type_id = $row[0];

$query = "INSERT INTO object_data VALUES('','pays','Payment settings','Payment settings',-1,NOW(),NOW(),'')";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$obj_id = $row[0];

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$obj_id."')";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$ref_id = $row[0];

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($ref_id,SYSTEM_FOLDER_ID);
?>
<#263>
<?php
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'pays'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// added fix for too small typ_id field
$query = "ALTER TABLE rbac_ta CHANGE typ_id typ_id INT( 11 ) DEFAULT '0' NOT NULL ";
$this->db->query($query);

$query = "INSERT INTO rbac_ta VALUES('".$typ_id."','1')";
$this->db->query($query);

$query = "INSERT INTO rbac_ta VALUES('".$typ_id."','2')";
$this->db->query($query);

$query = "INSERT INTO rbac_ta VALUES('".$typ_id."','3')";
$this->db->query($query);

$query = "INSERT INTO rbac_ta VALUES('".$typ_id."','4')";
$this->db->query($query);
?>
<#264>
ALTER  TABLE `tst_eval_settings`   CHANGE `distancemean`  `distancemedian`  ENUM( '0' , '1'   ) DEFAULT '1'  NOT  NULL;
<#265>
ALTER TABLE `tst_eval_settings` DROP `distancequintile`;
<#266>
CREATE TABLE `payment_vendors` (
`vendor_id` INT( 11 ) NOT NULL ,
`cost_center` VARCHAR( 16 ) NOT NULL ,
PRIMARY KEY ( `vendor_id` )
);

<#267>
<?php
// remove rbac operations of aicc object type
$query = "SELECT obj_id FROM object_data ".
		 "WHERE type='typ' AND title='alm'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
if ($row->obj_id > 0)
{
	$query = "DELETE FROM rbac_ta WHERE typ_id='".$row->obj_id."'";
	$this->db->query($query);
}
$query = "DELETE FROM object_data WHERE type='typ' and title='alm'";
$this->db->query($query);

// remove rbac operations of hacp object type
$query = "SELECT obj_id FROM object_data ".
		 "WHERE type='typ' AND title='hlm'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

if ($row->obj_id > 0)
{
	$query = "DELETE FROM rbac_ta WHERE typ_id='".$row->obj_id."'";
	$this->db->query($query);
}
$query = "DELETE FROM object_data WHERE type='typ' and title='hlm'";
$this->db->query($query);

?>

<#268>
ALTER  TABLE  `survey_survey`  CHANGE  `ref_fi`  `obj_fi` INT( 11  ) DEFAULT  '0' NOT  NULL;
ALTER  TABLE  `survey_question`  CHANGE  `ref_fi`  `obj_fi` INT( 11  ) UNSIGNED DEFAULT  '0' NOT  NULL;
<#269>
<?php

// convert tst_tests reference id's to object id's
$query = "SELECT object_reference.obj_id, survey_survey.obj_fi, survey_survey.survey_id FROM object_reference, survey_survey WHERE survey_survey.obj_fi = object_reference.ref_id";
$result = $this->db->query($query);
while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	$query = sprintf("UPDATE `survey_survey` SET `obj_fi` = %s WHERE `survey_id` = %s",
		$this->db->quote($row["obj_id"]),
		$this->db->quote($row["survey_id"])
	);
	$insert_result = $this->db->query($query);
}

// convert qpl_questions reference id's to object id's
$query = "SELECT object_reference.obj_id, survey_question.obj_fi, survey_question.question_id FROM object_reference, survey_question WHERE survey_question.obj_fi = object_reference.ref_id";
$result = $this->db->query($query);
while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	$query = sprintf("UPDATE `survey_question` SET `obj_fi` = %s WHERE `question_id` = %s",
		$this->db->quote($row["obj_id"]),
		$this->db->quote($row["question_id"])
	);
	$insert_result = $this->db->query($query);
}

?>

<#270>
ALTER  TABLE `qpl_answers`   CHANGE `answertext`  `answertext`  TEXT NOT  NULL;
ALTER  TABLE `qpl_answers`   CHANGE `matchingtext`  `matchingtext`  TEXT NULL;

<#271>
DELETE FROM settings WHERE keyword='ldap_enable';
<#272>
UPDATE settings SET value = '3.2.0_beta1 2004-09-02' WHERE keyword = 'ilias_version' LIMIT 1;
<#273>
ALTER TABLE content_object ADD COLUMN print_view_active ENUM('y','n') DEFAULT 'y';
<#274>
CREATE TABLE `payment_trustees` (
`vendor_id` INT( 11 ) NOT NULL ,
`trustee_id` INT( 11 ) NOT NULL ,
`perm_stat` INT( 1 ) ,
`perm_obj` INT( 1 ) NOT NULL ,
PRIMARY KEY ( `vendor_id` , `trustee_id` )
);

<#275>
ALTER TABLE content_object ADD COLUMN numbering ENUM('y','n') DEFAULT 'n';

<#276>
CREATE TABLE `payment_objects` (
`pobject_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`ref_id` INT( 11 ) NOT NULL ,
`status` INT( 2 ) NOT NULL ,
`pay_method` INT( 2 ) NOT NULL ,
`vendor_id` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `pobject_id` )
);

<#277>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#278>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#279>
ALTER TABLE `role_data` ADD `assign_users` VARCHAR( 2 ) DEFAULT '0';

<#280>
RENAME TABLE scorm_lm TO sahs_lm;
ALTER TABLE sahs_lm ADD COLUMN type ENUM('scorm','aicc','hacp') DEFAULT 'scorm';

<#281>
<?php

$query = "SELECT * FROM object_data where type = 'slm' or type = 'alm' or type = 'hlm'";
$result = $this->db->query($query);
while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	switch($row["type"])
	{
		case "slm":
			$type = "scorm";
			break;

		case "alm":
			$type = "aicc";
			break;

		case "hlm":
			$type = "hacp";
			break;
	}
	$query = "UPDATE sahs_lm SET type='$type' WHERE id ='".$row["obj_id"]."'";
	$this->db->query($query);
	$query = "UPDATE object_data SET type='sahs' WHERE obj_id ='".$row["obj_id"]."'";
	$this->db->query($query);
}
?>

<#282>
UPDATE object_data SET title='sahs', description='SCORM/AICC Learning Module' WHERE title='slm' AND type='typ';

<#283>
UPDATE rbac_operations SET operation='create_sahs', description='create new SCORM/AICC learning module' WHERE operation = 'create_slm';

<#284>
UPDATE rbac_templates SET type='sahs' WHERE type='slm';

<#285>
<?php
$query = "INSERT INTO rbac_operations ".
	"VALUES('','cat_administrate_users','Administrate local user')";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() AS ops_id";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$admin_ops = $row->ops_id;
}

$query = "INSERT INTO rbac_operations ".
	"VALUES('','read_users','read local users')";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() AS ops_id";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$read_ops = $row->ops_id;
}

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'cat'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$cat_id = $row->obj_id;
}

$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'usrf'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$usrf_id = $row->obj_id;
}

$query = "INSERT INTO rbac_ta VALUES('".$cat_id."','".$read_ops."')";
$this->db->query($query);

$query = "INSERT INTO rbac_ta VALUES('".$cat_id."','".$admin_ops."')";
$this->db->query($query);

$query = "INSERT INTO rbac_ta VALUES('".$usrf_id."','".$read_ops."')";
$this->db->query($query);
?>

<#286>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#287>
<?php
$query = "UPDATE usr_data SET time_limit_owner = '7'";
$this->db->query($query);
?>

<#288>
ALTER TABLE `usr_data` ADD COLUMN `referral_comment` varchar(250) DEFAULT '';
ALTER TABLE `usr_data` ADD COLUMN `active` int(4) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `usr_data` ADD COLUMN `approve_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

REPLACE INTO settings (keyword, value) VALUES ('auto_registration', '1');
REPLACE INTO settings (keyword, value) VALUES ('approve_recipient', '');
REPLACE INTO settings (keyword, value) VALUES ('require_login', '1');
REPLACE INTO settings (keyword, value) VALUES ('require_passwd', '1');
REPLACE INTO settings (keyword, value) VALUES ('require_passwd2', '1');
REPLACE INTO settings (keyword, value) VALUES ('require_firstname', '1');
REPLACE INTO settings (keyword, value) VALUES ('require_gender', '1');
REPLACE INTO settings (keyword, value) VALUES ('require_lastname', '1');
REPLACE INTO settings (keyword, value) VALUES ('require_institution', '');
REPLACE INTO settings (keyword, value) VALUES ('require_department', '');
REPLACE INTO settings (keyword, value) VALUES ('require_street', '');
REPLACE INTO settings (keyword, value) VALUES ('require_city', '');
REPLACE INTO settings (keyword, value) VALUES ('require_zipcode', '');
REPLACE INTO settings (keyword, value) VALUES ('require_country', '');
REPLACE INTO settings (keyword, value) VALUES ('require_phone_office', '');
REPLACE INTO settings (keyword, value) VALUES ('require_phone_home', '');
REPLACE INTO settings (keyword, value) VALUES ('require_phone_mobile', '');
REPLACE INTO settings (keyword, value) VALUES ('require_fax', '');
REPLACE INTO settings (keyword, value) VALUES ('require_email', '1');
REPLACE INTO settings (keyword, value) VALUES ('require_hobby', '');
REPLACE INTO settings (keyword, value) VALUES ('require_default_role', '1');
REPLACE INTO settings (keyword, value) VALUES ('require_referral_comment', '');
<#289>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#290>
CREATE TABLE `payment_prices` (
`price_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`pobject_id` INT( 11 ) NOT NULL ,
`duration` INT( 4 ) NOT NULL ,
`currency` INT( 4 ) NOT NULL ,
`unit_value` INT( 6 ) DEFAULT '0',
`sub_unit_value` INT( 3 ) DEFAULT '0',
PRIMARY KEY ( `price_id` )
);
<#291>
CREATE TABLE `payment_currencies` (
  `currency_id` int(3) NOT NULL default '0',
  `unit` char(32) NOT NULL default '',
  `subunit` char(32) NOT NULL default '',
  PRIMARY KEY  (`currency_id`)
) TYPE=MyISAM;
<#292>
INSERT INTO `payment_currencies` VALUES (1, 'euro', 'cent');
<#293>
UPDATE `usr_data` SET `active`='1';
<#294>
ALTER  TABLE  `tst_tests`  ADD  `ects_output` ENUM(  '0',  '1'  ) DEFAULT  '0' NOT  NULL  AFTER  `ending_time` ;
ALTER  TABLE  `tst_tests`  ADD  `ects_fx` VARCHAR(2) DEFAULT  NULL AFTER  `ects_output` ;
<#295>
ALTER TABLE `tst_tests` CHANGE `ects_fx` `ects_fx` FLOAT DEFAULT NULL;
<#296>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#297>
ALTER  TABLE  `tst_tests`  ADD `ects_a` DOUBLE DEFAULT  '90' NOT  NULL;
ALTER  TABLE  `tst_tests`  ADD `ects_b` DOUBLE DEFAULT  '65' NOT  NULL;
ALTER  TABLE  `tst_tests`  ADD `ects_c` DOUBLE DEFAULT  '35' NOT  NULL;
ALTER  TABLE  `tst_tests`  ADD `ects_d` DOUBLE DEFAULT  '10' NOT  NULL;
ALTER  TABLE  `tst_tests`  ADD `ects_e` DOUBLE DEFAULT  '0' NOT  NULL;
<#298>
DROP TABLE `payment_prices`;
<#299>
CREATE TABLE `payment_prices` (
  `price_id` int(11) NOT NULL auto_increment,
  `pobject_id` int(11) NOT NULL default '0',
  `duration` int(4) NOT NULL default '0',
  `currency` int(4) NOT NULL default '0',
  `unit_value` char(6) default '0',
  `sub_unit_value` char(3) default '00',
  PRIMARY KEY  (`price_id`)
) TYPE=MyISAM;
<#300>
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
<#301>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#302>
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
  PRIMARY KEY  (`booking_id`)
) TYPE=MyISAM ;
<#303>
DROP TABLE IF EXISTS `payment_shopping_cart`;
CREATE TABLE `payment_shopping_cart` (
  `psc_id` int(11) NOT NULL auto_increment,
  `customer_id` int(11) NOT NULL default '0',
  `pobject_id` int(11) NOT NULL default '0',
  `price_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`psc_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;
<#304>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#305>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#306>
ALTER TABLE `survey_survey` ADD `anonymize` ENUM( '0', '1' ) DEFAULT '0' NOT NULL AFTER `created` ;
<#307>
ALTER TABLE `survey_finished` ADD `anonymous_id` VARCHAR( 32 ) NOT NULL AFTER `user_fi` ;
ALTER TABLE `survey_answer` ADD `anonymous_id` VARCHAR( 32 ) NOT NULL AFTER `user_fi` ;
<#308>
ALTER TABLE `survey_finished` DROP `anonymous_id`;
<#309>
ALTER TABLE `qpl_questions` CHANGE `materials` `solution_hint` TEXT DEFAULT NULL;
<#310>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#311>
ALTER TABLE `tst_active` ADD INDEX `user_fi` (`user_fi`);
ALTER TABLE `tst_active` ADD INDEX `test_fi` (`test_fi`);
<#312>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#313>
CREATE TABLE `exc_returned` (
`returned_id` INT NOT NULL AUTO_INCREMENT ,
`obj_id` INT NOT NULL ,
`user_id` INT NOT NULL ,
`filename` MEDIUMTEXT NOT NULL ,
`filetitle` MEDIUMTEXT NOT NULL ,
`mimetype` VARCHAR( 40 ) NOT NULL ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `returned_id` ) ,
INDEX ( `obj_id` ),
INDEX ( `user_id` )
);
<#314>
ALTER TABLE `usr_data` ADD `matriculation` VARCHAR( 40 ) AFTER `referral_comment` ;
<#315>
DROP TABLE IF EXISTS `survey_relation`;

CREATE TABLE `survey_relation` (
  `relation_id` int(11) NOT NULL auto_increment,
  `longname` varchar(20) NOT NULL default '',
  `shortname` char(2) NOT NULL default '',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`relation_id`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

INSERT INTO `survey_relation` VALUES (1, 'less', '<', 20040518195753);
INSERT INTO `survey_relation` VALUES (2, 'less_or_equal', '<=', 20040518195808);
INSERT INTO `survey_relation` VALUES (3, 'equal', '=', 20040518195816);
INSERT INTO `survey_relation` VALUES (4, 'not_equal', '<>', 20040518195839);
INSERT INTO `survey_relation` VALUES (5, 'more_or_equal', '>=', 20040518195852);
INSERT INTO `survey_relation` VALUES (6, 'more', '>', 20040518195903);
<#316>
DROP TABLE IF EXISTS `exc_returned`;
CREATE TABLE `exc_returned` (
`returned_id` INT NOT NULL AUTO_INCREMENT ,
`obj_id` INT NOT NULL ,
`user_id` INT NOT NULL ,
`filename` MEDIUMTEXT NOT NULL ,
`filetitle` MEDIUMTEXT NOT NULL ,
`mimetype` VARCHAR( 40 ) NOT NULL ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `returned_id` ) ,
INDEX ( `obj_id` ),
INDEX ( `user_id` )
);
<#317>
ALTER TABLE `object_reference` ADD INDEX ( `obj_id` );
ALTER TABLE `xmlnestedset` DROP INDEX `ns_l`;
ALTER TABLE `xmlnestedset` DROP INDEX `ns_r`;
<#318>
CREATE TABLE `qpl_answer_enhanced` (
`answer_enhanced_id` INT NOT NULL AUTO_INCREMENT ,
`answerblock_fi` INT NOT NULL ,
`answer_fi` INT NOT NULL ,
`answer_boolean_prefix` ENUM( '0', '1' ) DEFAULT '0' NOT NULL ,
`answer_boolean_connection` ENUM( '0', '1' ) DEFAULT '1' NOT NULL ,
`enhanced_order` TINYINT DEFAULT '0' NOT NULL ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `answer_enhanced_id` ) ,
INDEX ( `answerblock_fi` , `answer_fi` )
) COMMENT = 'saves combinations of test question answers which are combined in an answer block';

CREATE TABLE `qpl_answerblock` (
`answerblock_id` INT NOT NULL AUTO_INCREMENT ,
`answerblock_index` TINYINT DEFAULT '0' NOT NULL ,
`question_fi` INT NOT NULL ,
`subquestion_index` TINYINT DEFAULT '0' NOT NULL ,
`points` DOUBLE DEFAULT '0' NOT NULL ,
`feedback` VARCHAR( 30 ) ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `answerblock_id` ) ,
INDEX ( `question_fi` )
) COMMENT = 'defines an answerblock, a combination of given answers of a test question';

<#319>
<?php>
// fetch type id of folder object definition
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='fold'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operation assignment to folder object definition
$query = "REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','23')";
$this->db->query($query);
$query = "REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','24')";
$this->db->query($query);
$query = "REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','27')";
$this->db->query($query);
$query = "REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','28')";
$this->db->query($query);
$query = "REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','29')";
$this->db->query($query);
$query = "REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','31')";
$this->db->query($query);
$query = "REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','32')";
$this->db->query($query);
$query = "REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','42')";
$this->db->query($query);
$query = "REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','43')";
$this->db->query($query);
?>

<#320>
# add group & course operation assignments
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,17);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,23);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,24);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,25);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,26);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,27);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,28);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,29);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,31);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,32);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,42);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (17,43);

REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (15,27);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (15,28);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (15,42);
REPLACE INTO rbac_ta (typ_id,ops_id) VALUES (15,43);

<#321>
UPDATE settings SET value = '3.2.0 2004-10-20' WHERE keyword = 'ilias_version' LIMIT 1;
<#322>
ALTER TABLE `qpl_answer_enhanced` CHANGE `answer_fi` `answer_fi` VARCHAR( 20 ) DEFAULT '0' NOT NULL;
<#323>
ALTER TABLE `qpl_answer_enhanced` CHANGE `answer_fi` `value1` INT DEFAULT '0' NOT NULL;
ALTER TABLE `qpl_answer_enhanced` ADD `value2` INT DEFAULT '0' NOT NULL AFTER `value1`;
ALTER TABLE `qpl_answer_enhanced` DROP `answer_boolean_connection`;
<#324>
CREATE TABLE IF NOT EXISTS `qpl_suggested_solutions` (
  `suggested_solution_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL default '0',
  `internal_link` varchar(50) default '',
  `import_id` varchar(50) default '',
  `subquestion_index` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`suggested_solution_id`),
  KEY `question_fi` (`question_fi`)
);
<#325>
<?php>
// convert suggested solutions from prior format to new suggested solutions table
$query = "SELECT * FROM qpl_questions WHERE solution_hint > 0";
$res = $this->db->query($query);
if ($res->numRows())
{
	while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$qinsert = sprintf("INSERT INTO qpl_suggested_solutions (suggested_solution_id, question_fi, internal_link, import_id, subquestion_index, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
			$this->db->quote($row["question_id"] . ""),
			$this->db->quote("il__lm_" . $row["solution_hint"]),
			$this->db->quote(""),
			$this->db->quote("0")
		);
		$result = $this->db->query($qinsert);
	}
}
?>
<#326>
ALTER TABLE `qpl_questions` DROP `solution_hint`;
<#327>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#328>
ALTER  TABLE  `survey_survey`  ADD  `show_question_titles` ENUM(  '0',  '1'  ) DEFAULT  '1' NOT  NULL  AFTER  `anonymize` ;
<#329>
CREATE TABLE `survey_question_obligatory` (
`question_obligatory_id` INT NOT NULL AUTO_INCREMENT ,
`survey_fi` INT NOT NULL ,
`question_fi` INT NOT NULL ,
`obligatory` ENUM( '0', '1' ) DEFAULT '1' NOT NULL ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `question_obligatory_id` ) ,
INDEX ( `survey_fi` , `question_fi` )
) COMMENT = 'Contains the obligatory state of questions in a survey';
<#330>
<?php
// convert former questionblock obligatory states into the question obligatory table
$query = "SELECT * FROM survey_questionblock, survey_questionblock_question WHERE survey_questionblock_question.questionblock_fi = survey_questionblock.questionblock_id";
$res = $this->db->query($query);
if ($res->numRows())
{
	while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$qinsert = sprintf("INSERT INTO survey_question_obligatory (question_obligatory_id, survey_fi, question_fi, obligatory, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
			$this->db->quote($row["survey_fi"] . ""),
			$this->db->quote($row["question_fi"] . ""),
			$this->db->quote($row["obligatory"] . "")
		);
		$result = $this->db->query($qinsert);
	}
}
?>
<#331>
ALTER TABLE `survey_questionblock` DROP COLUMN `obligatory`;
<#332>
ALTER  TABLE  `usr_data`  ADD  `agree_date` DATETIME DEFAULT  '0000-00-00 00:00' NOT NULL;
<#333>
UPDATE settings SET value = '3.2.1 2004-11-03' WHERE keyword = 'ilias_version' LIMIT 1;
<#334>
ALTER TABLE `survey_survey_question`  ADD `heading` VARCHAR(255) AFTER `sequence`;
<#335>
CREATE TABLE IF NOT EXISTS usr_pwassist (
  pwassist_id varchar(32) NOT NULL default '',
  expires int(11) NOT NULL default '0',
  ctime int(11) NOT NULL default '0',
  user_id int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pwassist_id),
  UNIQUE KEY user_id (user_id)
) TYPE=MyISAM;

<#336>
ALTER TABLE `survey_question`  ADD `orientation` ENUM('0','1') default '0' AFTER `obligatory`;
<#337>
ALTER TABLE `qpl_questions` ADD INDEX `question_type_fi` ( `question_type_fi` );
ALTER TABLE `qpl_answers` ADD INDEX `question_fi` ( `question_fi` );
ALTER TABLE `tst_solutions` ADD INDEX `user_fi` ( `user_fi` );
ALTER TABLE `tst_solutions` ADD INDEX `test_fi` ( `test_fi` );
<#338>
UPDATE settings SET value = '3.2.2 2004-11-19' WHERE keyword = 'ilias_version' LIMIT 1;
<#339>
UPDATE settings SET value = '3.2.3 2004-11-22' WHERE keyword = 'ilias_version' LIMIT 1;

<#340>
<?php

$tree = new ilTree(ROOT_FOLDER_ID);
$tree->renumber();
?>
<#341>
REPLACE INTO settings (keyword, value) VALUES ('enable_js_edit', 1);
<#342>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#343>
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
<#344>
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

<#345>
DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `obj_id` int(11) NOT NULL,
  `action` char(20) NOT NULL DEFAULT '',
  `hdate` DATETIME,
  `usr_id` int(11) NOT NULL,
  INDEX object_id (`obj_id`)
) TYPE=MyISAM;

<#346>
ALTER TABLE file_data ADD version INT;

<#347>
ALTER TABLE history ADD info_params TEXT;

<#348>
UPDATE file_data SET version = 1;
<#349>
CREATE TABLE `frm_settings` (
  `obj_id` int(11) NOT NULL default '0',
  `default_view` int(2) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;

<#350>
DROP TABLE IF EXISTS `frm_user_read`;
CREATE TABLE `frm_user_read` (
  `usr_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `thread_id` int(11) NOT NULL default '0',
  `post_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`obj_id`,`thread_id`,`post_id`)
) TYPE=MyISAM;

<#351>
<?php

$query = "SELECT pos_pk,pos_usr_id,pos_thr_fk,pos_top_fk FROM frm_posts";
$res = $this->db->query($query);
while($row1 = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "SELECT top_frm_fk FROM frm_data ".
		"WHERE top_pk = '".$row1->pos_top_fk."'";

	$res2 = $this->db->query($query);
	while($row2 = $res2->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$query = "INSERT INTO frm_user_read ".
			"SET usr_id = '".$row1->pos_usr_id."', ".
			"obj_id = '".$row2->top_frm_fk."', ".
			"thread_id = '".$row1->pos_thr_fk."', ".
			"post_id = '".$row1->pos_pk."'";

		$this->db->query($query);
	}
}
?>
<#352>
DROP TABLE IF EXISTS `frm_thread_access`;
CREATE TABLE `frm_thread_access` (
  `usr_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `thread_id` int(11) NOT NULL default '0',
  `access_old` int(11) NOT NULL default '0',
  `access_last` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`obj_id`,`thread_id`)
) TYPE=MyISAM;

<#353>
DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `obj_id` int(11) NOT NULL,
  `obj_type` CHAR(8) NOT NULL DEFAULT '',
  `action` char(20) NOT NULL DEFAULT '',
  `hdate` DATETIME,
  `usr_id` int(11) NOT NULL,
  `info_params` TEXT NOT NULL DEFAULT '',
  `user_comment` TEXT NOT NULL DEFAULT '',
  INDEX id_type (obj_id, obj_type)
) TYPE=MyISAM;
<#354>
ALTER TABLE content_object ADD COLUMN hist_user_comments ENUM('y','n') DEFAULT 'n';
<#355>
ALTER TABLE `tst_tests` ADD `random_test` ENUM(  '0',  '1'  ) DEFAULT  '0' NOT  NULL  AFTER  `ects_e` ;
<#356>
CREATE TABLE `tst_test_random_question` (
  `test_random_question_id` int(11) NOT NULL auto_increment,
  `test_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `question_fi` int(11) NOT NULL default '0',
  `sequence` int(10) unsigned NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`test_random_question_id`)
) TYPE=MyISAM COMMENT='Relation table for random questions in tests';

ALTER TABLE `tst_tests` ADD `random_question_count` int(11) AFTER  `random_test` ;
<#357>
CREATE TABLE `tst_test_random` (
  `test_random_id` int(11) NOT NULL auto_increment,
  `test_fi` int(11) NOT NULL default '0',
  `questionpool_fi` int(11) NOT NULL default '0',
  `num_of_q` int(10) unsigned NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  PRIMARY KEY  (`test_random_id`)
) TYPE=MyISAM COMMENT='Questionpools taken for a random test';
<#358>
ALTER TABLE `survey_finished` ADD `anonymous_id` VARCHAR(32) AFTER  `user_fi` ;
<#359>
CREATE TABLE `link_check` (
  `page_id` int(11) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `parent_type` varchar(8) NOT NULL default ''
) TYPE=MyISAM;
<#360>
<?php
// convert all single response mc questions and remove the 'when not set' option 
$query = "SELECT qpl_answers.answer_id FROM qpl_answers, qpl_questions WHERE qpl_answers.question_fi = qpl_questions.question_id AND qpl_questions.question_type_fi = 1";
$res = $this->db->query($query);
while($row1 = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = sprintf("UPDATE qpl_answers SET correctness = '1', points = 0 WHERE answer_id = %s AND correctness = '0'",
		$this->db->quote($row1->answer_id . "")
	);
	$res2 = $this->db->query($query);
}

// convert all imagemap questions and remove the 'when not set' option 
$query = "SELECT qpl_answers.answer_id FROM qpl_answers, qpl_questions WHERE qpl_answers.question_fi = qpl_questions.question_id AND qpl_questions.question_type_fi = 6";
$res = $this->db->query($query);
while($row1 = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = sprintf("UPDATE qpl_answers SET correctness = '1', points = 0 WHERE answer_id = %s AND correctness = '0'",
		$this->db->quote($row1->answer_id . "")
	);
	$res2 = $this->db->query($query);
}

// convert all close questions and remove the 'when not set' option 
$query = "SELECT qpl_answers.answer_id FROM qpl_answers, qpl_questions WHERE qpl_answers.question_fi = qpl_questions.question_id AND qpl_questions.question_type_fi = 6 AND qpl_answers.cloze_type = '1'";
$res = $this->db->query($query);
while($row1 = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = sprintf("UPDATE qpl_answers SET correctness = '1', points = 0 WHERE answer_id = %s AND correctness = '0'",
		$this->db->quote($row1->answer_id . "")
	);
	$res2 = $this->db->query($query);
}
?>
<#361>
DROP TABLE IF EXISTS `link_check_report`;
CREATE TABLE `link_check_report` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`obj_id`,`usr_id`)
) TYPE=MyISAM;
<#362>
DROP TABLE IF EXISTS `link_check`;
CREATE TABLE `link_check` (
  `obj_id` int(11) NOT NULL default '0',
  `page_id` int(11) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `parent_type` varchar(8) NOT NULL default ''
) TYPE=MyISAM;
<#363>
CREATE TABLE `tst_eval_users` (
`eval_users_id` INT NOT NULL AUTO_INCREMENT ,
`test_fi` INT NOT NULL ,
`evaluator_fi` INT NOT NULL ,
`user_fi` INT NOT NULL ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `eval_users_id` ) ,
INDEX ( `test_fi` , `evaluator_fi` , `user_fi` )
) COMMENT = 'Contains the users someone has chosen for a statistical evaluation';

CREATE TABLE `tst_eval_groups` (
`eval_users_id` INT NOT NULL AUTO_INCREMENT ,
`test_fi` INT NOT NULL ,
`evaluator_fi` INT NOT NULL ,
`group_fi` INT NOT NULL ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `eval_users_id` ) ,
INDEX ( `test_fi` , `evaluator_fi` , `group_fi` )
) COMMENT = 'Contains the groups someone has chosen for a statistical evaluation';
<#364>
ALTER TABLE `tst_eval_groups` CHANGE `eval_users_id` `eval_groups_id` int(11) NOT NULL DEFAULT NULL auto_increment;

<#365>
DROP TABLE IF EXISTS `link_check`;
CREATE TABLE `link_check` (
  `obj_id` int(11) NOT NULL default '0',
  `page_id` int(11) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `parent_type` varchar(8) NOT NULL default '',
  `http_status_code` int(4) NOT NULL default '0',
  `last_check` int(11) NOT NULL default '0'
) TYPE=MyISAM;
<#366>
CREATE TABLE `survey_anonymous` (
`anonymous_id` INT NOT NULL AUTO_INCREMENT ,
`survey_key` VARCHAR( 32 ) NOT NULL ,
`survey_fi` INT NOT NULL ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `anonymous_id` ) ,
INDEX ( `survey_key` , `survey_fi` )
);
<#367>
ALTER TABLE `content_object` ADD `public_access_mode` ENUM( 'complete', 'selected' ) DEFAULT 'complete' NOT NULL;
ALTER TABLE `lm_data` ADD `public_access` ENUM( 'y', 'n' ) DEFAULT 'n' NOT NULL AFTER `import_id`;
ALTER TABLE `lm_data` ADD INDEX (`lm_id`);
ALTER TABLE `lm_data` ADD INDEX (`type`);
<#368>
ALTER TABLE `survey_question` CHANGE `orientation` `orientation` ENUM( '0', '1', '2' ) DEFAULT '0';
<#369>
ALTER TABLE `survey_question` ADD `maxchars` INT DEFAULT '0' NOT NULL AFTER `orientation` ;
<#370>
ALTER TABLE content_object ADD public_html_file VARCHAR(50) DEFAULT '' NOT NULL;
ALTER TABLE content_object ADD public_xml_file VARCHAR(50) DEFAULT '' NOT NULL;
<#371>
CREATE TABLE `survey_material` (
`material_id` INT NOT NULL AUTO_INCREMENT ,
`question_fi` INT NOT NULL ,
`internal_link` VARCHAR( 50 ) ,
`import_id` VARCHAR( 50 ) ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `material_id` ) ,
INDEX ( `question_fi` )
);
<#372>
ALTER TABLE `survey_material` ADD `material_title` VARCHAR( 255 ) AFTER `import_id` ;
<#373>
ALTER TABLE content_object ADD COLUMN downloads_active ENUM('y','n') DEFAULT 'n';
<#374>
UPDATE object_data SET import_id='' WHERE import_id='Array';
UPDATE lm_data SET import_id='' WHERE import_id='Array';
<#375>
CREATE TABLE `lm_menu` (
`id` INT NOT NULL AUTO_INCREMENT ,
`lm_id` INT NOT NULL ,
`link_type` ENUM( 'extern', 'intern' ) ,
`title` VARCHAR( 200 ) ,
`target` VARCHAR( 200 ) ,
`link_ref_id` INT,
`active` ENUM( 'y', 'n' ) DEFAULT 'n' NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `link_type` ) ,
INDEX ( `lm_id` ) ,
INDEX ( `active` )
);
<#376>
<?php

// added fix for too small typ_id field
$query = "ALTER TABLE rbac_ta CHANGE typ_id typ_id INT( 11 ) DEFAULT '0' NOT NULL ";
$this->db->query($query);

// register new object type 'assf' for Test&Assessment Administration
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'assf', 'AssessmentFolder object', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('assf', '__Test&Assessment', 'Test&Assessment Administration', -1, now(), now())";
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

// register RECOVERY_FOLDER_ID in table settings
$query = "INSERT INTO settings (keyword,value) VALUES('sys_assessment_folder_id','".$row->id."')";
$res = $this->db->query($query);

// retrieve assessment folder definition from object_data
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'assf'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

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
?>
<#377>
CREATE TABLE `ass_log` (
`ass_log_id` INT NOT NULL AUTO_INCREMENT ,
`user_fi` INT NOT NULL ,
`obj_fi` INT NOT NULL ,
`logtext` TEXT NOT NULL ,
`question_fi` INT,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `ass_log_id` ) ,
INDEX ( `user_fi` , `obj_fi` )
) COMMENT = 'Logging of Test&Assessment object changes';
<#378>
ALTER TABLE `ass_log` ADD `original_fi` INT AFTER `question_fi` ;
<#379>
INSERT INTO `qpl_question_type` ( `question_type_id` , `type_tag` ) VALUES ('8', 'qt_text') ;
ALTER TABLE `qpl_questions` ADD `maxNrOfChars` INT DEFAULT '0' NOT NULL AFTER `params` ;
ALTER TABLE `tst_solutions` CHANGE `value1` `value1` TEXT DEFAULT NULL ;
<#380>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#381>
ALTER TABLE `qpl_questions` CHANGE `maxNrOfChars` `maxNumOfChars` INT( 11 ) DEFAULT '0' NOT NULL;

<#382>
<?php

// added fix for too small typ_id field
$query = "ALTER TABLE rbac_ta CHANGE typ_id typ_id INT( 11 ) DEFAULT '0' NOT NULL ";
$this->db->query($query);
?>
<#383>
ALTER TABLE `survey_question` CHANGE `maxchars` `maxchars` INT( 11 );
<#384>
<?php
// fix misassigned pages (cut in lm x - pasted in lm y)
$q = "SELECT * FROM lm_tree WHERE child > 1";
$tree_set = $this->db->query($q);
while($tree_rec = $tree_set->fetchRow(DB_FETCHMODE_ASSOC))
{
	$q2 = "UPDATE page_object SET parent_id='".$tree_rec["lm_id"]."' WHERE page_id='".$tree_rec["child"]."' AND parent_type='lm'";
	$this->db->query($q2);
	$q3 = "UPDATE lm_data SET lm_id='".$tree_rec["lm_id"]."' WHERE obj_id='".$tree_rec["child"]."' AND type='pg'";
	$this->db->query($q3);
}

?>
<#385>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#386>
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

DROP TABLE IF EXISTS `crs_objective_lm`;
CREATE TABLE `crs_objective_lm` (
  `lm_ass_id` int(11) NOT NULL auto_increment,
  `objective_id` int(11) NOT NULL default '0',
  `ref_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `type` char(6) NOT NULL default '',
  PRIMARY KEY  (`lm_ass_id`)
) TYPE=MyISAM AUTO_INCREMENT=14 ;

<#387>
DROP TABLE IF EXISTS `style_folder_styles`;
CREATE TABLE `style_folder_styles` (
  `folder_id` int(11) NOT NULL default '0',
  `style_id` int(11) NOT NULL default '0',
  INDEX f_id (`folder_id`)
) TYPE=MyISAM;

<#388>
<?php

// insert style folder type to object data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'styf', 'Style Folder', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// insert operations for style folder type
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$row->id."','4')";
$this->db->query($query);

// CREATE SYSTEM STYLE FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('styf', 'System Style Folder', 'System Style Templates', -1, now(), now())";
$this->db->query($query);

// get style folder number
$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$sty_folder_id = $row->id;

// create object reference entry
$query = "INSERT INTO object_reference (obj_id) VALUES('".$row->id."')";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($row->id,SYSTEM_FOLDER_ID);

?>
<#389>
DROP TABLE IF EXISTS `crs_objective_qst`;
CREATE TABLE `crs_objective_qst` (
  `qst_ass_id` int(11) NOT NULL auto_increment,
  `objective_id` int(11) NOT NULL default '0',
  `ref_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `question_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`qst_ass_id`)
) TYPE=MyISAM;

<#390>

CREATE TABLE IF NOT EXISTS `crs_objective_tst` (
  `test_objective_id` int(11) NOT NULL auto_increment,
  `objective_id` int(11) NOT NULL default '0',
  `ref_id` int(11) NOT NULL default '0',
  `obj_id` int(11) NOT NULL default '0',
  `tst_status` tinyint(2) default NULL,
  `tst_limit` tinyint(3) default NULL,
  PRIMARY KEY  (`test_objective_id`)
) TYPE=MyISAM;
<#391>
ALTER TABLE `crs_settings` ADD `abo` TINYINT( 2 ) DEFAULT '1';
<#392>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#393>
ALTER TABLE `crs_settings` ADD `objective_view` TINYINT( 2 ) DEFAULT '0';
<#394>
<?php
// insert iLinc course definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'icrs', 'iLinc course object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operation assignment to iLinc course object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
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

// insert iLinc classroom definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'icla', 'iLinc class room object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operation assignment to iLinc course object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
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
?>

CREATE TABLE IF NOT EXISTS `ilinc_data` (
  `obj_id` INT(11) UNSIGNED NOT NULL,
  `type` CHAR(5) NOT NULL,
  `course_id` INT(11) UNSIGNED NOT NULL,
  `class_id` INT(11) UNSIGNED,
  `user_id` INT(11) UNSIGNED,
  INDEX (`obj_id`)
) TYPE=MyISAM;

ALTER TABLE `usr_data` ADD `ilinc_id` INT UNSIGNED;

<#395>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#396>
CREATE TABLE IF NOT EXISTS `crs_objective_results` (
  `res_id` int(11) NOT NULL auto_increment,
  `usr_id` int(11) NOT NULL default '0',
  `question_id` int(11) NOT NULL default '0',
  `points` int(11) NOT NULL default '0',
  PRIMARY KEY  (`res_id`)
) TYPE=MyISAM;
<#397>
CREATE TABLE IF NOT EXISTS `crs_lm_history` (
  `usr_id` int(11) NOT NULL default '0',
  `crs_ref_id` int(11) NOT NULL default '0',
  `lm_ref_id` int(11) NOT NULL default '0',
  `lm_page_id` int(11) NOT NULL default '0',
  `last_access` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`,`crs_ref_id`,`lm_ref_id`)
) TYPE=MyISAM;
<#398>
<#399>
<?php
// insert course grouping object in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'crsg', 'Course grouping object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operation assignment to iLinc course object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
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
?>
<#400>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#401>
ALTER TABLE `tst_eval_groups` ADD INDEX ( `test_fi` );
ALTER TABLE `tst_eval_groups` ADD INDEX ( `evaluator_fi` );
ALTER TABLE `tst_eval_groups` ADD INDEX ( `group_fi` );
ALTER TABLE `tst_eval_settings` ADD INDEX ( `user_fi` );
ALTER TABLE `tst_eval_users` ADD INDEX ( `evaluator_fi` );
ALTER TABLE `tst_eval_users` ADD INDEX ( `user_fi` );
ALTER TABLE `tst_eval_users` ADD INDEX ( `test_fi` );
ALTER TABLE `tst_mark` ADD INDEX ( `test_fi` );
ALTER TABLE `tst_solutions` ADD INDEX ( `question_fi` );
ALTER TABLE `tst_test_question` ADD INDEX ( `test_fi` );
ALTER TABLE `tst_test_question` ADD INDEX ( `question_fi` );
ALTER TABLE `tst_test_random` ADD INDEX ( `test_fi` );
ALTER TABLE `tst_test_random` ADD INDEX ( `questionpool_fi` );
ALTER TABLE `tst_test_random_question` ADD INDEX ( `test_fi` );
ALTER TABLE `tst_test_random_question` ADD INDEX ( `user_fi` );
ALTER TABLE `tst_test_random_question` ADD INDEX ( `question_fi` );
ALTER TABLE `tst_tests` ADD INDEX ( `obj_fi` );
ALTER TABLE `tst_tests` ADD INDEX ( `test_type_fi` );
ALTER TABLE `tst_times` ADD INDEX ( `active_fi` );
ALTER TABLE `survey_anonymous` ADD INDEX ( `survey_fi` );
ALTER TABLE `survey_answer` ADD INDEX ( `survey_fi` );
ALTER TABLE `survey_answer` ADD INDEX ( `question_fi` );
ALTER TABLE `survey_answer` ADD INDEX ( `user_fi` );
ALTER TABLE `survey_answer` ADD INDEX ( `anonymous_id` );
ALTER TABLE `survey_category` ADD INDEX ( `owner_fi` );
ALTER TABLE `survey_constraint` ADD INDEX ( `question_fi` );
ALTER TABLE `survey_constraint` ADD INDEX ( `relation_fi` );
ALTER TABLE `survey_finished` ADD INDEX ( `survey_fi` );
ALTER TABLE `survey_finished` ADD INDEX ( `user_fi` );
ALTER TABLE `survey_finished` ADD INDEX ( `anonymous_id` );
ALTER TABLE `survey_invited_group` ADD INDEX ( `survey_fi` );
ALTER TABLE `survey_invited_group` ADD INDEX ( `group_fi` );
ALTER TABLE `survey_invited_user` ADD INDEX ( `survey_fi` );
ALTER TABLE `survey_invited_user` ADD INDEX ( `user_fi` );
ALTER TABLE `survey_phrase` ADD INDEX ( `owner_fi` );
ALTER TABLE `survey_phrase_category` ADD INDEX ( `phrase_fi` );
ALTER TABLE `survey_phrase_category` ADD INDEX ( `category_fi` );
ALTER TABLE `survey_question` ADD INDEX ( `obj_fi` );
ALTER TABLE `survey_question` ADD INDEX ( `owner_fi` );
ALTER TABLE `survey_question_constraint` ADD INDEX ( `survey_fi` );
ALTER TABLE `survey_question_constraint` ADD INDEX ( `question_fi` );
ALTER TABLE `survey_question_constraint` ADD INDEX ( `constraint_fi` );
ALTER TABLE `survey_questionblock` ADD INDEX ( `owner_fi` );
ALTER TABLE `survey_questionblock_question` ADD INDEX ( `survey_fi` );
ALTER TABLE `survey_questionblock_question` ADD INDEX ( `questionblock_fi` );
ALTER TABLE `survey_questionblock_question` ADD INDEX ( `question_fi` );
ALTER TABLE `survey_survey` ADD INDEX ( `obj_fi` );
ALTER TABLE `survey_survey_question` ADD INDEX ( `survey_fi` );
ALTER TABLE `survey_survey_question` ADD INDEX ( `question_fi` );
ALTER TABLE `survey_variable` ADD INDEX ( `category_fi` );
ALTER TABLE `survey_variable` ADD INDEX ( `question_fi` );
<#402>
CREATE TABLE IF NOT EXISTS `crs_groupings` (
  `crs_grp_id` int(11) NOT NULL default '0',
  `crs_id` int(11) NOT NULL default '0',
  `unique_field` char(32) NOT NULL default '',
  PRIMARY KEY  (`crs_grp_id`),
  KEY `crs_id` (`crs_id`)
) TYPE=MyISAM;
<#403>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#404>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#405>
CREATE TABLE IF NOT EXISTS `crs_start` (
  `crs_start_id` int(11) NOT NULL auto_increment,
  `crs_id` int(11) NOT NULL default '0',
  `item_ref_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`crs_start_id`),
  KEY `crs_id` (`crs_id`)
) TYPE=MyISAM AUTO_INCREMENT=1;
<#406>
<?php
// change style folder to style settings
$query = "UPDATE object_data SET title='stys', description='Style Settings'".
		" WHERE title='styf' AND type='typ'";
$this->db->query($query);

// change style folder to style settings
$query = "UPDATE object_data SET type='stys', title = 'System Style Settings',".
		" description = 'Manage system skin and style settings here' ".
		" WHERE type='styf' ";
$this->db->query($query);
?>