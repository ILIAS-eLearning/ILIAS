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
UPDATE settings SET value = '3.0.0_beta4 2004/02/18' WHERE keyword = 'ilias_version' LIMIT 1;
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
UPDATE settings SET value = '3.0.0_beta5 2004/03/09' WHERE keyword = 'ilias_version' LIMIT 1;

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
UPDATE settings SET value = '3.0.0RC1 2004/04/18' WHERE keyword = 'ilias_version' LIMIT 1;

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
UPDATE settings SET value = '3.0.0 2004/05/15' WHERE keyword = 'ilias_version' LIMIT 1;
<#190>
INSERT  INTO  `qpl_question_type` (  `question_type_id` ,  `type_tag`  ) VALUES ('7',  'qt_javaapplet');
<#191>
ALTER  TABLE  `tst_solutions`  ADD  `points` DOUBLE AFTER  `value2` ;
