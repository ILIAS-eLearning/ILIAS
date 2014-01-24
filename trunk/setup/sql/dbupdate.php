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
) TYPE=MyISAM COMMENT='Tabelle f�r Anzeige von Geänderten Termindaten';
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

$rbacsystem = ilRbacSystem::getInstance();
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
	$rbacadmin->copyRolePermissions($mem_role_id,ROLE_FOLDER_ID,$dest_rolf_id,$dest_role_id);

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
	$rbacadmin->copyRoleTemplatePermissions($adm_role_id,ROLE_FOLDER_ID,$dest_rolf_id,$dest_role_id);

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
<#407>
DROP TABLE IF EXISTS `settings_deactivated_styles`;
CREATE TABLE `settings_deactivated_styles` (
	`skin` VARCHAR(100) NOT NULL,
	`style` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`skin`,`style`)
) TYPE=MyISAM;
<#408>
<?php
// change style folder to style settings
$query = "INSERT INTO settings (keyword, value) VALUES ('default_repository_view','flat')";
$this->db->query($query);
?>
<#409>
<?php
// ADD new permission push desktop items
$query = "SELECT * FROM object_data WHERE type = 'typ' AND title = 'usrf'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$type_id = $row->obj_id;
}

// INSERT new operation push_sesktop_items
$query = "INSERT INTO rbac_operations ".
	"SET operation = 'push_desktop_items', description = 'Allow pushing desktop items'";

$this->db->query($query);
// GET new ops_id
$query = "SELECT LAST_INSERT_ID() as ops_id FROM rbac_operations ";
$res = $this->db->getRow($query);
$ops_id = $res->ops_id;

// INSERT in rbac_ta
$query = "INSERT INTO rbac_ta SET typ_id = '".$type_id."', ops_id = '".$ops_id."'";
$this->db->query($query);
?>
<#410>
<?php
$query = "SELECT * FROM object_data ".
	"WHERE type = 'typ' AND title = 'crsg'";

$res = $this->db->getRow($query);
$ops_id = $res->obj_id;

$query = "DELETE FROM rbac_ta WHERE typ_id = '".$ops_id."'";
$this->db->query($query);
?>
<#411>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#412>
CREATE TABLE IF NOT EXISTS `role_desktop_items` (
  `role_item_id` int(11) NOT NULL auto_increment,
  `role_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  `item_type` char(16) NOT NULL default '',
  KEY `role_item_id` (`role_item_id`,`role_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

<#413>
<?php
$ilDB->query("DELETE FROM conditions WHERE operator = 'not_member'");
$ilDB->query('DELETE FROM crs_groupings');
?>

<#414>
ALTER TABLE `crs_groupings` ADD `crs_ref_id` INT( 11 ) NOT NULL AFTER `crs_grp_id` ;


<#415>
ALTER TABLE media_item ADD COLUMN tried_thumb ENUM('y','n') DEFAULT 'n';

<#416>
<?php
// insert link definition in object_data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'webr', 'Link resource object', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];


// add operation assignment to link object definition
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete, 7: subscribe, 8:unsubscribe
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
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','7')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','8')";
$this->db->query($query);

// add create operation
$query = "INSERT INTO rbac_operations ".
	"SET operation = 'create_webr', description = 'create web resource'";
$this->db->query($query);

// get new ops_id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$ops_id = $row[0];

// add create for crs,cat,fold and grp
// get category type id
$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='cat'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$this->db->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='crs'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$this->db->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='grp'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$this->db->query($query);

$query = "SELECT obj_id FROM object_data WHERE type='typ' and title='fold'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','".$ops_id."')";
$this->db->query($query);
?>
<#417>
CREATE TABLE `webr_items` (
  `link_id` int(11) NOT NULL auto_increment,
  `webr_id` int(11) NOT NULL default '0',
  `title` varchar(127) default NULL,
  `target` text,
  `active` tinyint(1) default NULL,
  `disable_check` tinyint(1) default NULL,
  `create_date` int(11) NOT NULL default '0',
  `last_update` int(11) NOT NULL default '0',
  `last_check` int(11) default NULL,
  `valid` tinyint(1) NOT NULL default '0',
  KEY `link_id` (`link_id`,`webr_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

<#418>
ALTER TABLE benchmark MODIFY module VARCHAR(150);
ALTER TABLE benchmark MODIFY benchmark VARCHAR(150);

<#419>
ALTER TABLE usr_data ADD COLUMN `client_ip` VARCHAR(15) AFTER `ilinc_id`;

<#420>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#421>
DROP TABLE IF EXISTS il_meta_annotation;
CREATE TABLE il_meta_annotation (
  meta_annotation_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  entity TEXT NULL,
  date TEXT NULL,
  description TEXT NULL,
  description_language CHAR(2) NULL,
  PRIMARY KEY(meta_annotation_id)
);
DROP TABLE IF EXISTS il_meta_classification;
CREATE TABLE il_meta_classification (
  meta_classification_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR NULL,
  purpose CHAR(32) NULL,
  description TEXT NULL,
  description_language CHAR(2) NULL,
  PRIMARY KEY(meta_classification_id)
);
DROP TABLE IF EXISTS il_meta_contribute;
CREATE TABLE il_meta_contribute (
  meta_contribute_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id int(11) NULL,
  role CHAR(32) NULL,
  date TEXT NULL,
  PRIMARY KEY(meta_contribute_id)
);

DROP TABLE IF EXISTS il_meta_description;
CREATE TABLE il_meta_description (
  meta_description_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id int(11) NULL,
  description TEXT NULL,
  description_language CHAR(2) NULL,
  PRIMARY KEY(meta_description_id)
);

DROP TABLE IF EXISTS il_meta_educational;
CREATE TABLE il_meta_educational (
  meta_educational_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  interactivity_type CHAR(16) NULL,
  learning_resource_type CHAR(32) NULL,
  interactivity_level CHAR(16) NULL,
  semantic_density CHAR(16) NULL,
  intended_end_user_row CHAR(16) NULL,
  context CHAR(16) NULL,
  difficulty CHAR(16) NULL,
  typical_learning_time TEXT NULL,
  PRIMARY KEY(meta_educational_id)
);

DROP TABLE IF EXISTS il_meta_entity;
CREATE TABLE il_meta_entity (
  meta_entity_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id int(11) NULL,
  entity TEXT NULL,
  PRIMARY KEY(meta_entity_id)
);

DROP TABLE IF EXISTS il_meta_format;
CREATE TABLE il_meta_format (
  meta_format_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id int(11) NULL,
  format TEXT NULL,
  PRIMARY KEY(meta_format_id)
);

DROP TABLE IF EXISTS il_meta_general;
CREATE TABLE il_meta_general (
  meta_general_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  general_structure CHAR(16) NULL,
  title TEXT NOT NULL,
  title_language CHAR(2) NULL,
  coverage TEXT NULL,
  coverage_language CHAR(2) NULL,
  PRIMARY KEY(meta_general_id)
);

DROP TABLE IF EXISTS il_meta_identifier;
CREATE TABLE il_meta_identifier (
  meta_identifier_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id int(11) NULL,
  catalog TEXT NULL,
  entry_id TEXT NULL,
  PRIMARY KEY(meta_identifier_id)
);

DROP TABLE IF EXISTS il_meta_identifier_;
CREATE TABLE il_meta_identifier_ (
  meta_identifier__id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id int(11) NULL,
  catalog TEXT NULL,
  entry TEXT NULL,
  PRIMARY KEY(meta_identifier__id)
);

DROP TABLE IF EXISTS il_meta_keyword;
CREATE TABLE il_meta_keyword (
  meta_keyword_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id CHAR NULL,
  keyword TEXT NULL,
  keyword_language CHAR(2) NULL,
  PRIMARY KEY(meta_keyword_id)
);

DROP TABLE IF EXISTS il_meta_language;
CREATE TABLE il_meta_language (
  meta_language_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id int(11) NULL,
  language CHAR(2) NULL,
  PRIMARY KEY(meta_language_id)
);

DROP TABLE IF EXISTS il_meta_lifecycle;
CREATE TABLE il_meta_lifecycle (
  meta_lifecycle_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  lifecycle_status CHAR(16) NULL,
  meta_version TEXT NULL,
  version_language CHAR(2) BINARY NULL,
  PRIMARY KEY(meta_lifecycle_id)
);

DROP TABLE IF EXISTS il_meta_location;
CREATE TABLE il_meta_location (
  meta_location_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NOT NULL,
  parent_id int(11) NULL,
  location TEXT NULL,
  location_type CHAR(16) NULL,
  PRIMARY KEY(meta_location_id)
);

DROP TABLE IF EXISTS il_meta_meta_data;
CREATE TABLE il_meta_meta_data (
  meta_meta_data_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  meta_data_scheme CHAR(16) NULL,
  language CHAR(2) NULL,
  PRIMARY KEY(meta_meta_data_id)
);

DROP TABLE IF EXISTS il_meta_relation;
CREATE TABLE il_meta_relation (
  meta_relation_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  kind CHAR(16) NULL,
  PRIMARY KEY(meta_relation_id)
);

DROP TABLE IF EXISTS il_meta_requirement;
CREATE TABLE il_meta_requirement (
  meta_requirement_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id int(11) NULL,
  operating_system_name CHAR(16) NULL,
  operating_system_minimum_version TEXT NULL,
  operating_system_maximum_version TEXT NULL,
  browser_name CHAR(32) NULL,
  browser_minimum_version TEXT NULL,
  browser_maximum_version TEXT NULL,
  PRIMARY KEY(meta_requirement_id)
);

DROP TABLE IF EXISTS il_meta_rights;
CREATE TABLE il_meta_rights (
  meta_rights_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  costs CHAR(3) NULL,
  copyright_and_other_restrictions CHAR(3) NULL,
  description TEXT NULL,
  description_language CHAR(2) NULL,
  PRIMARY KEY(meta_rights_id)
);

DROP TABLE IF EXISTS il_meta_taxon;
CREATE TABLE il_meta_taxon (
  meta_taxon_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR NULL,
  parent_id int(11) NULL,
  taxon TEXT NULL,
  taxon_language CHAR(2) NULL,
  taxon_id TEXT NULL,
  PRIMARY KEY(meta_taxon_id)
);

DROP TABLE IF EXISTS il_meta_taxon_path;
CREATE TABLE il_meta_taxon_path (
  meta_taxon_path_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id int(11) NULL,
  source TEXT NULL,
  source_language CHAR(2) NULL,
  PRIMARY KEY(meta_taxon_path_id)
);

DROP TABLE IF EXISTS il_meta_technical;
CREATE TABLE il_meta_technical (
  meta_technical_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  size TEXT NULL,
  installation_remarks TEXT NULL,
  installation_remarks_language CHAR(2) NULL,
  other_platform_requirements TEXT NULL,
  other_platform_requirements_language CHAR(2) NULL,
  duration TEXT NULL,
  PRIMARY KEY(meta_technical_id)
);

DROP TABLE IF EXISTS il_meta_typical_age_range;
CREATE TABLE il_meta_typical_age_range (
  meta_typical_age_range_id int(11) NOT NULL AUTO_INCREMENT,
  rbac_id int(11) NULL,
  obj_id int(11) NULL,
  obj_type CHAR(6) NULL,
  parent_type CHAR(16) NULL,
  parent_id int(11) NULL,
  typical_age_range TEXT NULL,
  typical_age_range_language CHAR(2) NULL,
  PRIMARY KEY(meta_typical_age_range_id)
);

<#422>

ALTER TABLE tst_active 
ADD COLUMN `submitted` TINYINT UNSIGNED DEFAULT 0 AFTER `TIMESTAMP`,
ADD COLUMN `submittimestamp` DATETIME AFTER `submitted`;

CREATE TABLE `tst_active_qst_sol_settings` (
  `test_fi` int(10) unsigned NOT NULL default '0',
  `user_fi` int(10) unsigned NOT NULL default '0',
  `question_fi` int(10) unsigned NOT NULL default '0',
  `solved` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`test_fi`,`user_fi`,`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `tst_invited_user` (
  `test_fi` int(11) NOT NULL default '0',
  `user_fi` int(11) NOT NULL default '0',
  `TIMESTAMP` timestamp(14) NOT NULL,
  `clientip` varchar(15) default NULL,
  PRIMARY KEY  (`test_fi`,`user_fi`)
) TYPE=MyISAM;

INSERT INTO tst_test_type SET test_type_id=4, type_tag='tt_online_exam';


<#423>
ALTER TABLE `il_meta_classification` CHANGE `obj_type` `obj_type` VARCHAR( 6 ) NULL DEFAULT NULL;
ALTER TABLE `il_meta_contribute` CHANGE `parent_type` `parent_type` VARCHAR( 32 ) NULL DEFAULT NULL;
ALTER TABLE `il_meta_educational` CHANGE `intended_end_user_row` `intended_end_user_role` VARCHAR( 16 ) NULL DEFAULT NULL;
ALTER TABLE `il_meta_identifier` CHANGE `entry_id` `entry` TEXT NULL DEFAULT NULL;
ALTER TABLE `il_meta_keyword` CHANGE `parent_id` `parent_id` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE `il_meta_taxon` CHANGE `parent_type` `parent_type` CHAR( 32 ) NULL DEFAULT NULL;
ALTER TABLE `il_meta_taxon_path` CHANGE `parent_type` `parent_type` VARCHAR( 32 ) NULL DEFAULT NULL ;

<#424>
ALTER TABLE `il_meta_requirement` ADD `or_composite_id` INT( 11 ) NOT NULL ;

<#425>
ALTER TABLE `il_meta_keyword` CHANGE `parent_type` `parent_type` VARCHAR( 32 ) NULL DEFAULT NULL;

<#426>
<?php
$ilCtrlStructureReader->getStructure();
?>
<?php
$wd = getcwd();

include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDCreator.php';


$webr_ids = array();
$query = "SELECT * FROM object_data WHERE type = 'webr'";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$webr_ids[$row->obj_id]['title'] = $row->title;
	$webr_ids[$row->obj_id]['desc'] = $row->description;

}

foreach($webr_ids as $id => $data)
{
	$query = "SELECT ref_id FROM object_reference WHERE obj_id = '".$id."'";

	$res = $ilDB->query($query);
	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$md_creator = new ilMDCreator($row->ref_id,$id,'webr');
		$md_creator->setTitle($data['title']);
		$md_creator->setTitleLanguage('en');
		$md_creator->setDescription($data['desc']);
		$md_creator->setDescriptionLanguage('en');
		$md_creator->setKeywordLanguage('en');
		$md_creator->setLanguage('en');

		$md_creator->create();
	}
}

?>
<#427>
ALTER  TABLE  `tst_tests`  ADD  `count_system` ENUM(  '0',  '1'  ) DEFAULT  '0' NOT  NULL  AFTER  `random_question_count` ;

<#428>
ALTER TABLE `glossary` ADD `virtual` ENUM('none','fixed','level','subtree') DEFAULT 'none' NOT NULL;

<#429>
<?php

// insert style folder type to object data
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'seas', 'Search settings', -1, now(), now())";
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

// CREATE SEarch settings object
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('seas', 'Search settings', 'Search settings', -1, now(), now())";
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
<#430>
<?php

$query = "INSERT INTO rbac_operations SET operation = 'search', description = 'Allow using search'";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as ops_id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

$ops_id = $row->ops_id;

  // Add permission tu use search
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'seas'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

$obj_id = $row->obj_id;


$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$obj_id."','".$ops_id."')";
$this->db->query($query);
?>
<#431>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#432>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#433>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#434>
<?php
$this->db->query('DELETE FROM usr_search');
?>
<#435>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#436>
CREATE TABLE `tst_test_result` (
  `test_result_id` int(10) unsigned NOT NULL auto_increment,
  `user_fi` int(10) unsigned NOT NULL default '0',
  `test_fi` int(10) unsigned NOT NULL default '0',
  `question_fi` int(10) unsigned NOT NULL default '0',
  `points` double NOT NULL default '0',
  `TIMESTAMP` timestamp,
  PRIMARY KEY  (`test_result_id`),
  UNIQUE KEY `test_result_id` (`test_result_id`),
  KEY `test_result_id_2` (`test_result_id`),
  KEY `user_fi` (`user_fi`),
  KEY `test_fi` (`test_fi`),
  KEY `question_fi` (`question_fi`)
) COMMENT='Test and Assessment user results for test questions';
<#437>
ALTER TABLE  `tst_test_result` DROP INDEX  `test_result_id`;
ALTER TABLE  `tst_test_result` DROP INDEX  `test_result_id_2`;
ALTER TABLE  `tst_test_result` DROP INDEX  `user_fi`;
ALTER TABLE  `tst_test_result` ADD UNIQUE (`user_fi` ,`test_fi` ,`question_fi`);
<#438>
UPDATE ut_access SET language='uk' WHERE language='ua';
UPDATE usr_pref SET value='uk' WHERE value='ua' and keyword='language';
UPDATE settings SET value='uk' WHERE value='ua' and keyword='language';
UPDATE object_translation SET lang_code='uk' WHERE lang_code='ua';
UPDATE lng_data SET lang_key='uk' WHERE lang_key='ua';
UPDATE glossary_term SET language='uk' WHERE language='ua';
<#439>
<?php
$wd = getcwd();

include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDCreator.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMD.php';

ilMD::_deleteAllByType('webr');

$webr_ids = array();
$query = "SELECT * FROM object_data WHERE type = 'webr'";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$webr_ids[$row->obj_id]['title'] = $row->title;
	$webr_ids[$row->obj_id]['desc'] = $row->description;

}

foreach($webr_ids as $id => $data)
{
	$md_creator = new ilMDCreator($id,$id,'webr');
	$md_creator->setTitle($data['title']);
	$md_creator->setTitleLanguage('en');
	$md_creator->setDescription($data['desc']);
	$md_creator->setDescriptionLanguage('en');
	$md_creator->setKeywordLanguage('en');
	$md_creator->setLanguage('en');

	$md_creator->create();
}

?>
<#440>
<?php
	global $log;
	$log->write("test&assessment (update step 440): starting with conversion. creating database entries with maximum available points for every question");
	$idx = 1;
	$query = "SELECT * FROM qpl_questions";
	$result = $ilDB->query($query);
	$maxidx = $result->numRows() + 1;
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$maxidx--;
		$log->write("processing question $maxidx");
		$query_answer = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s",
			$ilDB->quote($row["question_id"] . "")
		);
		$result_answer = $ilDB->query($query_answer);
		$answers = array();
		while ($row_answer = $result_answer->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($answers, $row_answer);
		}
		$maxpoints = 0;
		switch ($row["question_type_fi"])
		{
			case 1: // multiple choice single response
				$points = array("set" => 0, "unset" => 0);
				foreach ($answers as $key => $value) 
				{
					if ($value["points"] > $points["set"])
					{
						$points["set"] = $value["points"];
					}
				}
				$maxpoints = $points["set"];
				break;
			case 2: // multiple choice multiple response
				$points = array("set" => 0, "unset" => 0);
				$allpoints = 0;
				foreach ($answers as $key => $value) 
				{
					$allpoints += $value["points"];
				}
				$maxpoints = $allpoints;
				break;
			case 3: // close question
				$gaps = array();
				foreach ($answers as $key => $value)
				{
					if (!array_key_exists($value["gap_id"], $gaps))
					{
						$gaps[$value["gap_id"]] = array();
					}
					array_push($gaps[$value["gap_id"]], $value);
				}
				$points = 0;
				foreach ($gaps as $key => $value) {
					if ($value[0]["cloze_type"] == 0) {
						$points += $value[0]["points"];
					} else {
						$points_arr = array("set" => 0, "unset" => 0);
						foreach ($value as $key2 => $value2) {
							if ($value2["points"] > $points_arr["set"])
							{
								$points_arr["set"] = $value2["points"];
							}
						}
						$points += $points_arr["set"];
					}
				}
				$maxpoints = $points;
				break;
			case 4: // matching question
				$points = 0;
				foreach ($answers as $key => $value)
				{
					if ($value["points"] > 0)
					{
						$points += $value["points"];
					}
				}
				$maxpoints = $points;
				break;
			case 5: // ordering question
				$points = 0;
				foreach ($answers as $key => $value)
				{
					$points += $value["points"];
				}
				$maxpoints = $points;
				break;
			case 6: // imagemap question
				$points = array("set" => 0, "unset" => 0);
				foreach ($answers as $key => $value) {
					if ($value["points"] > $points["set"])
					{
						$points["set"] = $value["points"];
					}
				}
				$maxpoints = $points["set"];
				break;
			case 7: // java applet question
			case 8: // text question
				break;
		}
		if ($row["question_type_fi"] < 7)
		{
			$updatequery = sprintf("UPDATE qpl_questions SET points = %s WHERE question_id = %s",
				$ilDB->quote($maxpoints . ""),
				$ilDB->quote($row["question_id"] . "")
			);
			$resultupdate = $ilDB->query($updatequery);
			$log->write("  $idx. creating maximum question points: $updatequery");
			$idx++;
		}
	}
	$log->write("test&assessment: conversion completed. maximum available points for every question");

	
	$log->write("test&assessment: starting with conversion. creating database entry for reached points of every user for every processed question");
	// update code
	$idx = 1;
	$query = "SELECT question_id, question_type_fi FROM qpl_questions";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$queryanswers = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY gap_id, aorder ASC",
			$ilDB->quote($row["question_id"] . "")
		);
		$resultanswers = $ilDB->query($queryanswers);
		$answers = array();
		while ($rowanswer = $resultanswers->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($answers, $rowanswer);
		}
		$querytests = sprintf("SELECT DISTINCT test_fi FROM tst_solutions WHERE question_fi = %s",
			$ilDB->quote($row["question_id"] . "")
		);
		$resulttests = $ilDB->query($querytests);
		$tests = array();
		while ($rowtest = $resulttests->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($tests, $rowtest["test_fi"]);
		}
		foreach ($tests as $test_id)
		{
			$queryusers = sprintf("SELECT DISTINCT user_fi FROM tst_solutions WHERE test_fi = %s AND question_fi = %s",
				$ilDB->quote($test_id . ""),
				$ilDB->quote($row["question_id"])
			);
			$resultusers = $ilDB->query($queryusers);
			$users = array();
			while ($rowuser = $resultusers->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($users, $rowuser["user_fi"]);
			}
			// now begin the conversion
			foreach ($users as $user_id)
			{
				$querysolutions = sprintf("SELECT * FROM tst_solutions WHERE test_fi = %s AND user_fi = %s AND question_fi = %s",
					$ilDB->quote($test_id . ""),
					$ilDB->quote($user_id . ""),
					$ilDB->quote($row["question_id"] . "")
				);
				$resultsolutions = $ilDB->query($querysolutions);
				switch ($row["question_type_fi"])
				{
					case 1:
					case 2:
						// multiple choice questions
						$found_values = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if (strcmp($data["value1"], "") != 0)
							{
								array_push($found_values, $data["value1"]);
							}
						}
						$points = 0;
						if (count($found_values) > 0)
						{
							foreach ($answers as $key => $answer)
							{
								if ($answer["correctness"])
								{
									if (in_array($key, $found_values))
									{
										$points += $answer["points"];
									}
								}
								else
								{
									if (!in_array($key, $found_values))
									{
										$points += $answer["points"];
									}
								}
							}
						}
						// save $points
						break;
					case 3:
						// close questions
						$found_value1 = array();
						$found_value2 = array();
						$user_result = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if (strcmp($data["value2"], "") != 0)
							{
								$user_result[$data["value1"]] = array(
									"gap_id" => $data["value1"],
									"value" => $data["value2"]
								);
							}
						}
						$points = 0;
						$counter = 0;
						$gaps = array();
						foreach ($answers as $key => $value)
						{
							if (!array_key_exists($value["gap_id"], $gaps))
							{
								$gaps[$value["gap_id"]] = array();
							}
							array_push($gaps[$value["gap_id"]], $value);
						}
						foreach ($user_result as $gap_id => $value) 
						{
							if ($gaps[$gap_id][0]["cloze_type"] == 0) 
							{
								$foundsolution = 0;
								foreach ($gaps[$gap_id] as $k => $v) 
								{
									if ((strcmp(strtolower($v["answertext"]), strtolower($value["value"])) == 0) && (!$foundsolution)) 
									{
										$points += $v["points"];
										$foundsolution = 1;
									}
								}
							} 
							else 
							{
								if ($value["value"] >= 0)
								{
									foreach ($gaps[$gap_id] as $answerkey => $answer)
									{
										if ($value["value"] == $answerkey)
										{
											$points += $answer["points"];
										}
									}
								}
							}
						}
						// save $points;
						break;
					case 4:
						// matching questions
						$found_value1 = array();
						$found_value2 = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if (strcmp($data["value1"], "") != 0)
							{
								array_push($found_value1, $data["value1"]);
								array_push($found_value2, $data["value2"]);
							}
						}
						$points = 0;
						foreach ($found_value2 as $key => $value)
						{
							foreach ($answers as $answer_value)
							{
								if (($answer_value["matching_order"] == $value) and ($answer_value["aorder"] == $found_value1[$key]))
								{
									$points += $answer_value["points"];
								}
							}
						}
						// save $points;
						break;
					case 5:
						// ordering questions
						$found_value1 = array();
						$found_value2 = array();
						$user_order = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if ((strcmp($data["value1"], "") != 0) && (strcmp($data["value2"], "") != 0))
							{
								$user_order[$data["value2"]] = $data["value1"];
							}
						}
						ksort($user_order);
						$user_order = array_values($user_order);
						$answer_order = array();
						foreach ($answers as $key => $answer)
						{
							$answer_order[$answer["solution_order"]] = $key;
						}
						ksort($answer_order);
						$answer_order = array_values($answer_order);
						$points = 0;
						foreach ($answer_order as $index => $answer_id)
						{
							if (strcmp($user_order[$index], "") != 0)
							{
								if ($answer_id == $user_order[$index])
								{
									$points += $answers[$answer_id]["points"];
								}
							}
						}
						// save $points;
						break;
					case 6:
						// imagemap questions
						$found_values = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if (strcmp($data["value1"], "") != 0)
							{
								array_push($found_values, $data["value1"]);
							}
						}
						$points = 0;
						if (count($found_values) > 0)
						{
							foreach ($answers as $key => $answer)
							{
								if ($answer["correctness"])
								{
									if (in_array($key, $found_values))
									{
										$points += $answer["points"];
									}
								}
							}
						}
						// save $points;
						break;
					case 7:
						// java applet questions
						$found_values = array();
						$points = 0;
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							$points += $data["points"];
						}
						// save $points;
						break;
					case 8:
						// text questions
						$points = 0;
						if ($resultsolutions->numRows() == 1)
						{
							$data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC);
							if ($data["points"])
							{
								$points = $data["points"];
							}
						}
						// save $points;
						break;
				}
				$insertquery = sprintf("REPLACE tst_test_result (user_fi, test_fi, question_fi, points) VALUES (%s, %s, %s, %s)",
					$ilDB->quote($user_id . ""),
					$ilDB->quote($test_id . ""),
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($points . "")
				);
				$ilDB->query($insertquery);
				$log->write("  $idx. creating user result: $insertquery");
				$idx++;
			}
		}
	}
	$log->write("test&assessment: conversion finished. creating database entry for reached points of every user for every processed question");
?>
<#441>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

<#442>
<?php
$tables = array('il_meta_annotation',
				'il_meta_classification',
				'il_meta_contribute',
				'il_meta_description',
				'il_meta_educational',
				'il_meta_entity',
				'il_meta_format',
				'il_meta_general',
				'il_meta_identifier',
				'il_meta_identifier_',
				'il_meta_keyword',
				'il_meta_language',
				'il_meta_lifecycle',
				'il_meta_location',
				'il_meta_meta_data',
				'il_meta_relation',
				'il_meta_requirement',
				'il_meta_rights',
				'il_meta_taxon',
				'il_meta_taxon_path',
				'il_meta_technical',
				'il_meta_typical_age_range');

foreach($tables as $table)
{
	$ilDB->query("ALTER TABLE ".$table." ADD INDEX ('obj_id','rbac_id','obj_type')");
}

<#443>
<?php
$wd = getcwd();

include_once 'Services/Migration/DBUpdate_439/classes/class.ilNestedSetXML.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDCreator.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDXMLParser.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMD.php';

global $log;

$log->write("MetaData (Migration type 'mob'): Start");

$nested = new ilNestedSetXML();

// Get last processes mob object
$res = $ilDB->query("SELECT MAX(obj_id) as max_id FROM tmp_migration ");
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$max_id = $row->max_id;
}
$max_id = $max_id ? $max_id : 0;

// MetaData migration of mobs
$res = $ilDB->query("SELECT * FROM object_data WHERE type = 'mob' AND obj_id >= '".$max_id."' ORDER BY obj_id");

$log->write("MetaData (Migration type 'mob'): Number of objects: ".$res->numRows());

$counter = 0;
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if(!(++$counter%100))
	{
		$log->write("MetaData (Migration type 'mob'): Processing obj number: ".$row->obj_id);
	}

	// Check if already processed
	$done_res = $ilDB->query("SELECT * FROM tmp_migration WHERE obj_id = '".$row->obj_id."' AND passed = '1'");
	if($done_res->numRows())
	{
		continue;
	}
	// Delete old entries
	$md = new ilMD($row->obj_id,$row->obj_id,'mob');
	$md->deleteAll();

	// Get xml data
	if($xml = $nested->export($row->obj_id,'mob'))
	{
		$parser = new ilMDXMLParser($xml,$row->obj_id,$row->obj_id,'mob');
		$parser->startParsing();
	}
	else
	{
		// Create new entry
		$md_creator = new ilMDCreator($row->obj_id,$row->obj_id,'mob');
		$md_creator->setTitle($row->title);
		$md_creator->setTitleLanguage('en');
		$md_creator->setDescription($row->desc);
		$md_creator->setDescriptionLanguage('en');
		$md_creator->setKeywordLanguage('en');
		$md_creator->setLanguage('en');

		$md_creator->create();
	}
	// Set passed
	$ilDB->query("INSERT INTO tmp_migration SET obj_id = '".$row->obj_id."', passed = 1");
}
$log->write("MetaData (Migration type 'mob'): Finished migration");

?>

<#444>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#445>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#446>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

<#447>
<?php
$tables = array('il_meta_annotation',
                                'il_meta_classification',
                                'il_meta_contribute',
                                'il_meta_description',
                                'il_meta_educational',
                                'il_meta_entity',
                                'il_meta_format',
                                'il_meta_general',
                                'il_meta_identifier',
                                'il_meta_identifier_',
                                'il_meta_keyword',
                                'il_meta_language',
                                'il_meta_lifecycle',
                                'il_meta_location',
                                'il_meta_meta_data',
                                'il_meta_relation',
                                'il_meta_requirement',
                                'il_meta_rights',
                                'il_meta_taxon',
                                'il_meta_taxon_path',
                                'il_meta_technical',
                                'il_meta_typical_age_range');

foreach($tables as $table)
{
        $ilDB->query("ALTER TABLE ".$table." ADD INDEX rbac_obj(rbac_id,obj_id)");
}
$ilDB->query("ALTER TABLE tmp_migration ADD INDEX (obj_id)");
?>

<#448>
<?php
$wd = getcwd();

include_once 'Services/Migration/DBUpdate_439/classes/class.ilNestedSetXML.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDCreator.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDXMLParser.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMD.php';

global $log;

$log->write("MetaData (Migration type 'lm'): Start");

$nested = new ilNestedSetXML();

// Get last processed lm object
$res = $ilDB->query("SELECT MAX(obj_id) as max_id FROM tmp_migration ");
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
        $max_id = $row->max_id;
}
$max_id = $max_id ? $max_id : 0;

// MetaData migration of lm's
$res = $ilDB->query("SELECT * FROM object_data WHERE type = 'lm' AND obj_id >= '".$max_id."' ORDER BY obj_id");

$log->write("MetaData (Migration type 'lm,st,pg'): Number of lm's: ".$res->numRows());

$counter = 0;
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$log->write("MetaData (Migration type 'lm'): Processing obj number: ".$row->obj_id);

	// Check if already processed
	$done_res = $ilDB->query("SELECT * FROM tmp_migration WHERE obj_id = '".$row->obj_id."' AND passed = '1'");
	if($done_res->numRows())
	{
		continue;
	}
	// Delete old entries
	$md = new ilMD($row->obj_id,$row->obj_id,'lm');
	$md->deleteAll();
	unset($md);

	// Get xml data
	$xml = $nested->export($row->obj_id,'lm');
	if($xml)
	{
		$parser = new ilMDXMLParser($xml,$row->obj_id,$row->obj_id,'lm');
		$parser->startParsing();
	}
	else
	{
		// Create new entry
		$md_creator = new ilMDCreator($row->obj_id,$row->obj_id,'lm');
		$md_creator->setTitle($row->title);
		$md_creator->setTitleLanguage('en');
		$md_creator->setDescription($row->description);
		$md_creator->setDescriptionLanguage('en');
		$md_creator->setKeywordLanguage('en');
		$md_creator->setLanguage('en');

		$md_creator->create();
	}

	// Now migrate all pages and chapters
	$res_pg = $ilDB->query("SELECT * FROM lm_data WHERE lm_id = '".$row->obj_id."' AND (type = 'pg' OR type = 'st')");
	while($row_pg = $res_pg->fetchRow(DB_FETCHMODE_OBJECT))
	{
		if(function_exists('memory_get_usage'))
		{
			$memory_usage = " Memory usage: ".memory_get_usage();
		}

		$log->write("-- MetaData (Migration type '".$row_pg->type."'): Processing obj number: ".$row_pg->obj_id.$memory_usage);

		// Delete old entries
		$md = new ilMD($row_pg->lm_id,$row_pg->obj_id,$row_pg->type);
		$md->deleteAll();
		unset($md);
			   
		// Get xml data
		if($xml = $nested->export($row_pg->obj_id,$row_pg->type))
		{
			$parser = new ilMDXMLParser($xml,$row_pg->lm_id,$row_pg->obj_id,$row_pg->type);
			$parser->startParsing();
		}
		else
		{
			if(function_exists('memory_get_usage'))
			{
				$memory_usage = " Memory usage: ".memory_get_usage();
			}
			$log->write("-- --  MetaData (Migration type '".$row_pg->type."'): (Creating new entry!) Processing obj number: ".
						$row_pg->obj_id.$memory_usage);

			// Create new entry
			$md_creator = new ilMDCreator($row_pg->lm_id,$row_pg->obj_id,$row_pg->type);
			$md_creator->setTitle($row_pg->title);
			$md_creator->setTitleLanguage('en');
			$md_creator->setDescription('');
			$md_creator->setDescriptionLanguage('en');
			$md_creator->setKeywordLanguage('en');
			$md_creator->setLanguage('en');

			$md_creator->create();
		}
	}
	// Finally set lm passed
	$ilDB->query("INSERT INTO tmp_migration SET obj_id = '".$row->obj_id."', passed = 1");
}
$log->write("MetaData (Migration type 'lm'): Finished migration");

?>
<#449>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

<#450>
<?php
// Migration of glossaries
$wd = getcwd();

include_once 'Services/Migration/DBUpdate_439/classes/class.ilNestedSetXML.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDCreator.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDXMLParser.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMD.php';

global $log;

$log->write("MetaData (Migration type 'glo'): Start");

$nested = new ilNestedSetXML();

// Get last processed lm object
$res = $ilDB->query("SELECT MAX(obj_id) as max_id FROM tmp_migration ");
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
        $max_id = $row->max_id;
}
$max_id = $max_id ? $max_id : 0;

// MetaData migration of glossaries
$res = $ilDB->query("SELECT * FROM object_data WHERE type = 'glo' AND obj_id >= '".$max_id."' ORDER BY obj_id");

$log->write("MetaData (Migration type 'glo'): Number of glossaries: ".$res->numRows());

$counter = 0;
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$log->write("MetaData (Migration type 'glo'): Processing obj number: ".$row->obj_id);

	// Check if already processed
	$done_res = $ilDB->query("SELECT * FROM tmp_migration WHERE obj_id = '".$row->obj_id."' AND passed = '1'");
	if($done_res->numRows())
	{
		continue;
	}
	// Delete old entries
	$md = new ilMD($row->obj_id,$row->obj_id,'glo');
	$md->deleteAll();
	unset($md);

	// Get xml data
	$xml = $nested->export($row->obj_id,'glo');
	if($xml)
	{
		$parser = new ilMDXMLParser($xml,$row->obj_id,$row->obj_id,'glo');
		$parser->startParsing();
	}
	else
	{
		// Create new entry
		$md_creator = new ilMDCreator($row->obj_id,$row->obj_id,'glo');
		$md_creator->setTitle($row->title);
		$md_creator->setTitleLanguage('en');
		$md_creator->setDescription($row->description);
		$md_creator->setDescriptionLanguage('en');
		$md_creator->setKeywordLanguage('en');
		$md_creator->setLanguage('en');

		$md_creator->create();
	}

	// Now migrate all glossary definitions
	$res_gdf = $ilDB->query("select gd.id as gdid,term from glossary_definition as gd, glossary_term as gt where gd.term_id = gt.id".
							" and glo_id = '".$row->obj_id."'");
	while($row_gdf = $res_gdf->fetchRow(DB_FETCHMODE_OBJECT))
	{
		if(function_exists('memory_get_usage'))
		{
			$memory_usage = " Memory usage: ".memory_get_usage();
		}

		$log->write("-- MetaData (Migration type 'gdf'): Processing definition nr.: ".$row_gdf->gdid.$memory_usage);

		// Delete old entries
		$md = new ilMD($row->obj_id,$row_gdf->gdid,'gdf');
		$md->deleteAll();
		unset($md);
			   
		// Get xml data
		if($xml = $nested->export($row_gdf->gdid,'gdf'))
		{
			$log->write('xml: '.$xml);
			$parser = new ilMDXMLParser($xml,$row->obj_id,$row_gdf->gdid,'gdf');
			$parser->startParsing();
		}
		else
		{
			if(function_exists('memory_get_usage'))
			{
				$memory_usage = " Memory usage: ".memory_get_usage();
			}
			$log->write("-- --  MetaData (Migration type 'gdf'): (Creating new entry!) Processing glossary definition number: ".
						$row_gdf->gdid.$memory_usage);

			// Create new entry
			$md_creator = new ilMDCreator($row->obj_id,$row_gdf->gdid,'gdf');
			$md_creator->setTitle($row_gdf->term);
			$md_creator->setTitleLanguage('en');
			$md_creator->setDescription('');
			$md_creator->setDescriptionLanguage('en');
			$md_creator->setKeywordLanguage('en');
			$md_creator->setLanguage('en');

			$md_creator->create();
		}
	}
	// Finally set gdf passed
	$ilDB->query("INSERT INTO tmp_migration SET obj_id = '".$row->obj_id."', passed = 1");
}
$log->write("MetaData (Migration type 'glo'): Finished migration");

?>
<#451>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
  `obj_id` int(11) NOT NULL default '0',
  `passed` tinyint(4) NOT NULL default '0');

<#452>
<?php
  // Migration of type tst,svy,crs,sahs,icla,icrs
$wd = getcwd();

include_once 'Services/Migration/DBUpdate_439/classes/class.ilNestedSetXML.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDCreator.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDXMLParser.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMD.php';

global $log;

$log->write("MetaData (Migration type tst,svy,crs,sahs,icla,icrs): Start");

$nested = new ilNestedSetXML();

// Get last processed object
$res = $ilDB->query("SELECT MAX(obj_id) as max_id FROM tmp_migration ");
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
        $max_id = $row->max_id;
}
$max_id = $max_id ? $max_id : 0;

// MetaData migration of glossaries
$res = $ilDB->query("SELECT * FROM object_data WHERE type IN ('tst','svy','qpl','spl','crs','icla','icrs','sahs','htlm') AND obj_id >= '".
					$max_id."' ORDER BY obj_id");

$log->write("MetaData (Migration): Number of objects: ".$res->numRows());

$counter = 0;
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$log->write("MetaData (Migration type '".$row->type."'): Processing obj number: ".$row->obj_id);

	// Check if already processed
	$done_res = $ilDB->query("SELECT * FROM tmp_migration WHERE obj_id = '".$row->obj_id."' AND passed = '1'");
	if($done_res->numRows())
	{
		continue;
	}
	// Delete old entries
	$md = new ilMD($row->obj_id,$row->obj_id,$row->type);
	$md->deleteAll();
	unset($md);

	// Get xml data
	$xml = $nested->export($row->obj_id,$row->type);
	if($xml)
	{
		$parser = new ilMDXMLParser($xml,$row->obj_id,$row->obj_id,$row->type);
		$parser->startParsing();
	}
	else
	{
		// Create new entry
		$md_creator = new ilMDCreator($row->obj_id,$row->obj_id,$row->type);
		$md_creator->setTitle($row->title);
		$md_creator->setTitleLanguage('en');
		$md_creator->setDescription($row->description);
		$md_creator->setDescriptionLanguage('en');
		$md_creator->setKeywordLanguage('en');
		$md_creator->setLanguage('en');

		$md_creator->create();
	}
}
$log->write("MetaData (Migration): Finished migration");

?>
<#453>
DROP TABLE IF EXISTS tmp_migration;
<#454>
ALTER TABLE `object_data` ADD FULLTEXT (
	`title`);
ALTER TABLE `object_data` ADD FULLTEXT (
	`description`);
<#455>
ALTER TABLE `object_data` DROP INDEX `title`;
ALTER TABLE `object_data` DROP INDEX `description`;

ALTER TABLE `object_data` ADD FULLTEXT `title_desc` (`title` ,`description`	);
<#456>
ALTER TABLE `il_meta_keyword` ADD FULLTEXT `keyword` (`keyword`);
ALTER TABLE `il_meta_entity` ADD FULLTEXT `entity` (`entity`);

<#457>
ALTER TABLE `il_meta_general` ADD FULLTEXT `title_coverage` (`title`,`coverage`);
<#458>
ALTER TABLE `il_meta_description` ADD FULLTEXT `description` (`description`);
<#459>
ALTER TABLE `frm_posts` ADD FULLTEXT `message_subject` (`pos_message`,`pos_subject`);
<#460>
ALTER TABLE `frm_threads` ADD FULLTEXT `thr_subject` (`thr_subject`);
<#461>
ALTER TABLE `glossary_term` ADD FULLTEXT `term` (`term`);
<#462>
ALTER TABLE `exc_data` ADD FULLTEXT `instruction` (`instruction`);
<#463>
ALTER TABLE `survey_question` ADD FULLTEXT `title_desc` (`title` ,`description`);
<#464>
ALTER TABLE `qpl_questions` ADD FULLTEXT `title_desc` (`title` ,`comment`);
<#465>
ALTER TABLE `tst_tests` ADD FULLTEXT `introduction` (`introduction`);
<#466>
ALTER TABLE `survey_survey` ADD FULLTEXT `introduction` (`introduction`);
<#467>
ALTER  TABLE  `tst_tests`  ADD  `mc_scoring` ENUM(  '0',  '1'  ) DEFAULT  '0' NOT  NULL  AFTER  `count_system` ;
<#468>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#469>
CREATE TABLE `module` (
	`name` VARCHAR(100) NOT NULL PRIMARY KEY,
	`dir` VARCHAR(200) NOT NULL
) COMMENT = 'ILIAS Modules';

CREATE TABLE `module_class` (
	`class` VARCHAR(100) NOT NULL PRIMARY KEY,
	`module` VARCHAR(100) NOT NULL,
	`dir` VARCHAR(200) NOT NULL
) COMMENT = 'Class information of ILIAS Modules';

<#470>
<?php

$ilCtrlStructureReader->getStructure();

?>

<#471>
<?php

//$ilModuleReader->getModules();

?>
<#472>
ALTER TABLE `usr_search` ADD `search_type` TINYINT( 2 ) DEFAULT '0' NOT NULL ;
<#473>
<?php
	// update reached question points for new test scoring options
	global $log;
	$log->write("test&assessment: starting with conversion. updating database entries for reached points of every user for every processed question");
	// update code
	$idx = 1;
	$query = "SELECT question_id, question_type_fi FROM qpl_questions";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$queryanswers = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY gap_id, aorder ASC",
			$ilDB->quote($row["question_id"] . "")
		);
		$resultanswers = $ilDB->query($queryanswers);
		$answers = array();
		while ($rowanswer = $resultanswers->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($answers, $rowanswer);
		}
		$querytests = sprintf("SELECT DISTINCT test_fi FROM tst_solutions WHERE question_fi = %s",
			$ilDB->quote($row["question_id"] . "")
		);
		$resulttests = $ilDB->query($querytests);
		$tests = array();
		while ($rowtest = $resulttests->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($tests, $rowtest["test_fi"]);
		}
		foreach ($tests as $test_id)
		{
			$queryusers = sprintf("SELECT DISTINCT user_fi FROM tst_solutions WHERE test_fi = %s AND question_fi = %s",
				$ilDB->quote($test_id . ""),
				$ilDB->quote($row["question_id"])
			);
			$resultusers = $ilDB->query($queryusers);
			$users = array();
			while ($rowuser = $resultusers->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($users, $rowuser["user_fi"]);
			}
			// now begin the conversion
			foreach ($users as $user_id)
			{
				$querysolutions = sprintf("SELECT * FROM tst_solutions WHERE test_fi = %s AND user_fi = %s AND question_fi = %s",
					$ilDB->quote($test_id . ""),
					$ilDB->quote($user_id . ""),
					$ilDB->quote($row["question_id"] . "")
				);
				$resultsolutions = $ilDB->query($querysolutions);
				switch ($row["question_type_fi"])
				{
					case 1:
					case 2:
						// multiple choice questions
						$found_values = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if (strcmp($data["value1"], "") != 0)
							{
								array_push($found_values, $data["value1"]);
							}
						}
						$points = 0;
						if ((count($found_values) > 0) || ($row["question_type_fi"] == 2))
						{
							foreach ($answers as $key => $answer)
							{
								if ($answer["correctness"])
								{
									if (in_array($key, $found_values))
									{
										$points += $answer["points"];
									}
								}
								else
								{
									if (!in_array($key, $found_values))
									{
										$points += $answer["points"];
									}
								}
							}
						}
						// save $points
						break;
					case 3:
						// close questions
						$found_value1 = array();
						$found_value2 = array();
						$user_result = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if (strcmp($data["value2"], "") != 0)
							{
								$user_result[$data["value1"]] = array(
									"gap_id" => $data["value1"],
									"value" => $data["value2"]
								);
							}
						}
						$points = 0;
						$counter = 0;
						$gaps = array();
						foreach ($answers as $key => $value)
						{
							if (!array_key_exists($value["gap_id"], $gaps))
							{
								$gaps[$value["gap_id"]] = array();
							}
							array_push($gaps[$value["gap_id"]], $value);
						}
						foreach ($user_result as $gap_id => $value) 
						{
							if ($gaps[$gap_id][0]["cloze_type"] == 0) 
							{
								$foundsolution = 0;
								foreach ($gaps[$gap_id] as $k => $v) 
								{
									if ((strcmp(strtolower($v["answertext"]), strtolower($value["value"])) == 0) && (!$foundsolution)) 
									{
										$points += $v["points"];
										$foundsolution = 1;
									}
								}
							} 
							else 
							{
								if ($value["value"] >= 0)
								{
									foreach ($gaps[$gap_id] as $answerkey => $answer)
									{
										if ($value["value"] == $answerkey)
										{
											$points += $answer["points"];
										}
									}
								}
							}
						}
						// save $points;
						break;
					case 4:
						// matching questions
						$found_value1 = array();
						$found_value2 = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if (strcmp($data["value1"], "") != 0)
							{
								array_push($found_value1, $data["value1"]);
								array_push($found_value2, $data["value2"]);
							}
						}
						$points = 0;
						foreach ($found_value2 as $key => $value)
						{
							foreach ($answers as $answer_value)
							{
								if (($answer_value["matching_order"] == $value) and ($answer_value["aorder"] == $found_value1[$key]))
								{
									$points += $answer_value["points"];
								}
							}
						}
						// save $points;
						break;
					case 5:
						// ordering questions
						$found_value1 = array();
						$found_value2 = array();
						$user_order = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if ((strcmp($data["value1"], "") != 0) && (strcmp($data["value2"], "") != 0))
							{
								$user_order[$data["value2"]] = $data["value1"];
							}
						}
						ksort($user_order);
						$user_order = array_values($user_order);
						$answer_order = array();
						foreach ($answers as $key => $answer)
						{
							$answer_order[$answer["solution_order"]] = $key;
						}
						ksort($answer_order);
						$answer_order = array_values($answer_order);
						$points = 0;
						foreach ($answer_order as $index => $answer_id)
						{
							if (strcmp($user_order[$index], "") != 0)
							{
								if ($answer_id == $user_order[$index])
								{
									$points += $answers[$answer_id]["points"];
								}
							}
						}
						// save $points;
						break;
					case 6:
						// imagemap questions
						$found_values = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if (strcmp($data["value1"], "") != 0)
							{
								array_push($found_values, $data["value1"]);
							}
						}
						$points = 0;
						if (count($found_values) > 0)
						{
							foreach ($answers as $key => $answer)
							{
								if ($answer["correctness"])
								{
									if (in_array($key, $found_values))
									{
										$points += $answer["points"];
									}
								}
							}
						}
						// save $points;
						break;
					case 7:
						// java applet questions
						$found_values = array();
						$points = 0;
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							$points += $data["points"];
						}
						// save $points;
						break;
					case 8:
						// text questions
						$points = 0;
						if ($resultsolutions->numRows() == 1)
						{
							$data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC);
							if ($data["points"])
							{
								$points = $data["points"];
							}
						}
						// save $points;
						break;
				}
				// check for special scoring options in test
				$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
					$ilDB->quote($test_id)
				);
				$resulttest = $ilDB->query($query);
				if ($resulttest->numRows() == 1)
				{
					$rowtest = $resulttest->fetchRow(DB_FETCHMODE_ASSOC);
					if ($rowtest["count_system"] == 1)
					{
						$maxpoints = 0;
						$query = sprintf("SELECT points FROM qpl_questions WHERE question_id = %s",
							$ilDB->quote($row["question_id"] . "")
						);
						$resultmaxpoints = $ilDB->query($query);
						if ($resultmaxpoints->numRows() == 1)
						{
							$rowmaxpoints = $resultmaxpoints->fetchRow(DB_FETCHMODE_ASSOC);
							$maxpoints = $rowmaxpoints["points"];
						}
						if ($points != $maxpoints)
						{
							$points = 0;
						}
					}
				}
				else
				{
					$points = 0;
				}
				$insertquery = sprintf("REPLACE tst_test_result (user_fi, test_fi, question_fi, points) VALUES (%s, %s, %s, %s)",
					$ilDB->quote($user_id . ""),
					$ilDB->quote($test_id . ""),
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($points . "")
				);
				$ilDB->query($insertquery);
				$log->write("  $idx. creating user result: $insertquery");
				$idx++;
			}
		}
	}
	$log->write("test&assessment: conversion finished. creating database entry for reached points of every user for every processed question");
?>
<#474>
ALTER TABLE `il_meta_typical_age_range` ADD `typical_age_range_min` TINYINT( 3 ) DEFAULT '-1' NOT NULL ,
ADD `typical_age_range_max` TINYINT( 3 ) DEFAULT '-1' NOT NULL ;
<#475>
<?php
$query = "SELECT * FROM il_meta_typical_age_range ";
$res =& $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if(preg_match("/\s*(\d*)\s*(-?)\s*(\d*)/",$row->typical_age_range,$matches))
	{
		if(!$matches[2] and !$matches[3])
		{
			$min = $max = $matches[1];
		}
		elseif($matches[2] and !$matches[3])
		{
			$min = $matches[1];
			$max = 99;
		}
		else
		{
			$min = $matches[1];
			$max = $matches[3];
		}

		$query = "UPDATE il_meta_typical_age_range ".
			"SET typical_age_range_min = '".(int) $min."', ".
			"typical_age_range_max = '".(int) $max."' ".
			"WHERE meta_typical_age_range_id = '".$row->meta_typical_age_range_id."'";

		$ilDB->query($query);
	}
}
?>
<#476>
ALTER TABLE `usr_search` DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `usr_id` , `search_type` );

<#477>
REPLACE INTO settings (keyword, value) VALUES ('enable_calendar', '1');

<#478>
<?php
$tables = array('il_meta_annotation',
				'il_meta_classification',
				'il_meta_contribute',
				'il_meta_description',
				'il_meta_educational',
				'il_meta_entity',
				'il_meta_format',
				'il_meta_general',
				'il_meta_identifier',
				'il_meta_identifier_',
				'il_meta_keyword',
				'il_meta_language',
				'il_meta_lifecycle',
				'il_meta_location',
				'il_meta_meta_data',
				'il_meta_relation',
				'il_meta_requirement',
				'il_meta_rights',
				'il_meta_taxon',
				'il_meta_taxon_path',
				'il_meta_technical',
				'il_meta_typical_age_range');

foreach($tables as $table)
{
	$ilDB->query("UPDATE ".$table." SET rbac_id = 0 WHERE obj_type = 'mob'");
}
?>
<#479>
<?php

$query = "INSERT INTO rbac_operations SET operation = 'moderate', description = 'Moderate objects'";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as ops_id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

$ops_id = $row->ops_id;

  // Add permission tu use search
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'chat'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

$obj_id = $row->obj_id;


$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$obj_id."','".$ops_id."')";
$this->db->query($query);
?>
<#480>
<?php
 // Get all global roles
$query = "SELECT rol_id FROM rbac_fa ".
	"WHERE parent = '".ROLE_FOLDER_ID."'";

$res = $this->db->query($query);
while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$rol_ids[] = $row->rol_id;
}
// Get searchSettings ref_id
$query = "SELECT ref_id from object_data od ,object_reference as orf where od.obj_id = orf.obj_id and type = 'seas'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ref_id = $row->ref_id;
}
// Get serach operation id
$query = "SELECT * FROM rbac_operations where operation = 'search'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$search_ops = $row->ops_id;
}

foreach($rol_ids as $role_id)
{
	$query = "SELECT ops_id FROM rbac_pa where rol_id = '".$role_id."' and ref_id = '".$ref_id."'";
	$res = $this->db->query($query);
	$ops = array();
	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$ops = unserialize(stripslashes($row->ops_id));
	}
	if(!in_array($search_ops,$ops))
	{
		$query = "DELETE FROM rbac_pa WHERE rol_id = '".$role_id."' and ref_id = '".$ref_id."'";
		$this->db->query($query);
		
		$ops[] = $search_ops;
		$query = "INSERT INTO rbac_pa SET rol_id = '".$role_id."', ".
			"ops_id = '".addslashes(serialize($ops))."', ".
			"ref_id = '".$ref_id."'";
		$this->db->query($query);
	}
}
?>
<#481>
<?php
$query = "UPDATE il_meta_format SET parent_type = 'meta_technical'";
$this->db->query($query);
?>
<#482>
CREATE TABLE `crs_wating_list` (
`obj_id` INT( 11 ) NOT NULL ,
`usr_id` INT( 11 ) NOT NULL ,
`sub_time` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `obj_id` , `usr_id` )
) TYPE = MYISAM ;

<#483>
ALTER TABLE `usr_data` ADD `auth_mode` ENUM( 'default','local', 'ldap', 'radius', 'shibboleth','script' ) DEFAULT 'default' NOT NULL;
ALTER TABLE `role_data` ADD `auth_mode` ENUM( 'default', 'local', 'ldap', 'radius', 'shibboleth', 'script' ) DEFAULT 'default' NOT NULL;


<#484>
UPDATE `usr_data` SET `auth_mode` = 'local' WHERE `usr_id` =13 LIMIT 1;

<#485>
ALTER TABLE `crs_wating_list` RENAME `crs_waiting_list`;

<#486>
DROP TABLE IF EXISTS `chat_record_data`;
CREATE TABLE `chat_record_data` (
  `record_data_id` int(11) NOT NULL auto_increment,
  `record_id` int(11) NOT NULL default '0',
  `message` mediumtext NOT NULL,
  `msg_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`record_data_id`)
	) TYPE = MyISAM;


DROP TABLE IF EXISTS `chat_records`;
CREATE TABLE `chat_records` (
  `record_id` int(11) NOT NULL auto_increment,
  `moderator_id` int(11) NOT NULL default '0',
  `chat_id` int(11) NOT NULL default '0',
  `room_id` int(11) NOT NULL default '0',
  `start_time` int(11) NOT NULL default '0',
  `end_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`record_id`)
	) TYPE=MyISAM;

<#487>
ALTER TABLE `chat_records` ADD `title` VARCHAR( 255 ) NOT NULL AFTER `room_id`,
ADD `description` TEXT NOT NULL AFTER `title` ;

<#488>
ALTER TABLE glossary ADD public_html_file VARCHAR(50) DEFAULT '' NOT NULL;
ALTER TABLE glossary ADD public_xml_file VARCHAR(50) DEFAULT '' NOT NULL;

<#489>
ALTER TABLE glossary ADD COLUMN glo_menu_active ENUM('y','n') DEFAULT 'y';
ALTER TABLE glossary ADD COLUMN downloads_active ENUM('y','n') DEFAULT 'n';
<#490>
DROP TABLE IF EXISTS `tmp_migration`;
CREATE TABLE `tmp_migration` (
  `page_id` int(11) NOT NULL default '0',
  `parent_id` int(11) default '0',
  `passed` tinyint(4) NOT NULL default '0');
<#491>
<?php
$wd = getcwd();

global $log;

// php5 downward complaince to php 4 dom xml
if (version_compare(PHP_VERSION,'5','>='))
{
	include_once 'Services/Migration/DBUpdate_491/inc.xml5compliance.php';
}


//  migration of page content
$res = $ilDB->query("SELECT page_id,parent_id FROM page_object ORDER BY page_id,parent_id");

$log->write("Page object migration: Number of objects: ".$res->numRows());

$counter = 0;
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$log->write("Page object migration: Processing page_id, parent_id: ".$row->page_id.", ".$row->parent_id);

	// Check if already processed
	$done_res = $ilDB->query("SELECT * FROM tmp_migration WHERE page_id = '".$row->page_id."' AND parent_id = '".$row->parent_id."' ".
							 "AND passed = '1'");
	if($done_res->numRows())
	{
		continue;
	}

	// get content
	$query = "SELECT content FROM page_object WHERE page_id = '".$row->page_id."' ".
		"AND parent_id = '".$row->parent_id."'";

	$res2 = $ilDB->query($query);

	// Must be splitted since DBupdate.php cannot handle expressions like 'question mark greater than'.
	$prefix = "<";
	$prefix .= "?xml version=\"1.0\" encoding=\"UTF-8\" ?";
	$prefix .= ">";

	while($row2 = $res2->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$content = $row2->content;
	}
	$content = $prefix.$content;

	$error = '';
	$dom_obj = @domxml_open_mem($content, DOMXML_LOAD_PARSING, $error);

	if($error)
	{
		// Cannot handle this error. => set passed and continue
		$log->write("Error building dom from node: page_id, parent_id: ".$row->page_id.", ".$row->parent_id);
		$ilDB->query("INSERT INTO tmp_migration SET page_id = '".$row->page_id."', parent_id = '".$row->parent_id."', passed = 1");
		continue;
	}

	$new_content = $dom_obj->dump_mem(0,"UTF-8");

	//$log->write("FROM DOM: ".$new_content ."ENDE");

	$new_content = eregi_replace("<\?xml[^>]*>","",$new_content);
	$new_content = eregi_replace("<!DOCTYPE[^>]*>","",$new_content);

	// Update content
	$query = "UPDATE page_object SET content = '".addslashes($new_content)."' ".
		"WHERE page_id = '".$row->page_id ."' AND parent_id = '".$row->parent_id."'";
	$ilDB->query($query);


	// Set passed
	$ilDB->query("INSERT INTO tmp_migration SET page_id = '".$row->page_id."', parent_id = '".$row->parent_id."', passed = 1");
}
$log->write("Page object: Finished migration");
?>
<#492>
DROP TABLE IF EXISTS `tmp_migration`;

<#493>
DELETE FROM desktop_item WHERE type = 'grou';

<#494>
<?php	
// insert third party tools definition (extt)
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('typ', 'extt', 'external tools settings', -1, now(), now())";
$this->db->query($query);

// fetch type id
$query = "SELECT LAST_INSERT_ID()";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// add operation assignment for extt object
// 1: edit_permissions, 2: visible, 3: read, 4: write, 6:delete
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','1')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','2')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','3')";
$this->db->query($query);
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','4')";
$this->db->query($query);

// add extt object in administration panel
$query = "INSERT INTO object_data VALUES('','extt','External tools settings','Configuring external tools',-1,NOW(),NOW(),'')";
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

<#495>
ALTER TABLE `usr_data` ADD `ilinc_login` VARCHAR( 40 ) AFTER `ilinc_id` ,
ADD `ilinc_passwd` VARCHAR( 40 ) AFTER `ilinc_login` ;

<#496>
<?php
$query = "INSERT INTO rbac_operations SET operation = 'create_icrs', description = 'create LearnLink Seminar'";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as ops_id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

$ops_id = $row->ops_id;

// Add operation to cat,grp,fold,crs 
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title IN ('cat','grp','fold','crs')";
$res = $this->db->query($query);

while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$obj_ids[] = $row->obj_id;
}

foreach ($obj_ids as $obj_id)
{
	$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$obj_id."','".$ops_id."')";
	$this->db->query($query);
}
?>

<#497>
<?php
// Add new operation create_icla
$query = "INSERT INTO rbac_operations SET operation = 'create_icla', description = 'create LearnLink Seminar room'";
$res = $this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as ops_id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$icla_ops_id = $row->ops_id;

// Add operation to icrs 
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title IN ('icrs')";
$res = $this->db->query($query);

while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$obj_ids[] = $row->obj_id;
}

foreach ($obj_ids as $obj_id)
{
	$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$obj_id."','".$icla_ops_id."')";
	$this->db->query($query);
}


// Add join/leave operations to icrs 
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'icrs'";
$res = $this->db->query($query);

$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$obj_id = $row->obj_id;

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$obj_id."','7')";
$this->db->query($query);

$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$obj_id."','8')";
$this->db->query($query);

// ADMIN TEMPLATE for LearnLink Seminars
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('rolt', 'il_icrs_admin', 'Administrator template for LearnLink Seminars', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as obj_id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$obj_id = $row->obj_id;

$admin = array();
$admin["icrs"] = array(1,2,3,4,6,7,8,$icla_ops_id);
$admin["rolf"] = array(1,2,3,4,6,14);

$rbacadmin =& new ilRbacAdmin();

foreach($admin as $type => $ops)
{
	$rbacadmin->setRolePermission($obj_id,$type,$ops,ROLE_FOLDER_ID);
}

$rbacadmin->assignRoleToFolder($obj_id,ROLE_FOLDER_ID,"n");

// MEMBER TEMPLATE for LearnLink Seminars
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('rolt', 'il_icrs_member', 'Member template for LearnLink Seminars', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as obj_id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$obj_id = $row->obj_id;

$admin = array();
$admin["icrs"] = array(2,3,7,8);


foreach($admin as $type => $ops)
{
	$rbacadmin->setRolePermission($obj_id,$type,$ops,ROLE_FOLDER_ID);
}
$rbacadmin->assignRoleToFolder($obj_id,ROLE_FOLDER_ID,"n");
?>

<#498>
<?php
// the following code removes all existing icrs and icla objects in system
// normally most of the code won't be triggered because iLinc-support was not available to public

// init tree and rbacadmin
$tree =& new ilTree(ROOT_FOLDER_ID);
$rbacadmin =& new ilRbacAdmin();
$rbacreview =& new ilRbacReview();

$ilca_nodes = $tree->getNodeDataByType('icla');

if (count($icla_nodes) != 0)
{
	foreach ($icla_nodes as $node)
	{
		// first look up for rolefolders
		$rolf = $rbacreview->getRoleFolderOfObject($node["ref_id"]);
		
		if ($rolf)
		{
			// remove local roles
			$roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"],false);
			foreach ($roles as $role_id)
			{
				$rbacadmin->deleteRole($role_id,$rolf["ref_id"]);
			}
			
			// remove linked local roles
			$roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);
			foreach ($roles as $role_id)
			{
				$rbacadmin->deleteLocalRole($role_id,$rolf["ref_id"]);
			}

			// delete rbac_fa entry
			$query = "DELETE FROM rbac_fa WHERE parent = '".$rolf["ref_id"]."'";
			$this->db->query($query);
			
			// delete entry in object_data
			$query = "DELETE FROM object_data WHERE obj_id = '".$rolf["obj_id"]."'";
			$this->db->query($query);
			
			// delete entry in object_reference
			$query = "DELETE FROM object_reference WHERE ref_id = '".$rolf["ref_id"]."'";
			$this->db->query($query);

			// remove tree entry
			$tree->deleteTree($rolf);	
		}
		
		// delete entry in object_data
		$query = "DELETE FROM object_data WHERE obj_id = '".$node["obj_id"]."'";
		$this->db->query($query);
		
		// delete entry in object_reference
		$query = "DELETE FROM object_reference WHERE ref_id = '".$node["ref_id"]."'";
		$this->db->query($query);
		
		// remove permission settings
		$rbacadmin->revokePermission($node["ref_id"]);
		
		// remove tree entry
		$tree->deleteTree($node);	
	}
} // if count icla_nodes

$ilcrs_nodes = $tree->getNodeDataByType('icrs');

if (count($icrs_nodes) != 0)
{
	foreach ($icrs_nodes as $node)
	{
		// first look up for rolefolders
		$rolf = $rbacreview->getRoleFolderOfObject($node["ref_id"]);
		
		if ($rolf)
		{
			// remove local roles
			$roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"],false);
			foreach ($roles as $role_id)
			{
				$rbacadmin->deleteRole($role_id,$rolf["ref_id"]);
			}
			
			// remove linked local roles
			$roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);
			foreach ($roles as $role_id)
			{
				$rbacadmin->deleteLocalRole($role_id,$rolf["ref_id"]);
			}
			
			// delete rbac_fa entry
			$query = "DELETE FROM rbac_fa WHERE parent = '".$rolf["ref_id"]."'";
			$this->db->query($query);
			
			// delete entry in object_data
			$query = "DELETE FROM object_data WHERE obj_id = '".$rolf["obj_id"]."'";
			$this->db->query($query);
			
			// delete entry in object_reference
			$query = "DELETE FROM object_reference WHERE ref_id = '".$rolf["ref_id"]."'";
			$this->db->query($query);

			// remove tree entry
			$tree->deleteTree($rolf);
		}

		// delete entry in object_data
		$query = "DELETE FROM object_data WHERE obj_id = '".$node["obj_id"]."'";
		$this->db->query($query);
		
		// delete entry in object_reference
		$query = "DELETE FROM object_reference WHERE ref_id = '".$node["ref_id"]."'";
		$this->db->query($query);
		
		// remove permission settings
		$rbacadmin->revokePermission($node["ref_id"]);
		
		// remove tree entry
		$tree->deleteTree($node);	
	}
} // if count icrs_nodes
?>

<#499>
<?php
// remove icla object from rbac system
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'icla'";
$res = $this->db->query($query);

$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$obj_id = $row->obj_id;

$query = "DELETE FROM rbac_ta WHERE typ_id = '".$obj_id."'";
$this->db->query($query);

$query = "DELETE FROM rbac_templates WHERE type = 'icla'";
$this->db->query($query);

$query = "DELETE FROM object_data WHERE type = 'icla'";
$this->db->query($query);

$query = "DELETE FROM object_data WHERE type = 'icrs'";
$this->db->query($query);

// reset iLinc related datas
$query = "TRUNCATE ilinc_data";
$this->db->query($query);

$query = "UPDATE usr_data SET ilinc_id = NULL";
$this->db->query($query);
?>
<#500>
CREATE INDEX jmp_parent ON ctrl_calls(parent);
<#501>
CREATE INDEX jmp_uid ON mail_obj_data(user_id);
<#502>
CREATE INDEX jmp_uid ON mail(user_id);
<#503>
CREATE INDEX jmp_parent ON rbac_fa(parent);
<#504>
CREATE INDEX jmp_tree ON mail_tree(tree);
<#505>
CREATE INDEX jmp_tree ON tree(tree);
<#506>
CREATE INDEX jmp_lm ON lm_tree(lm_id);
<#507>
CREATE TABLE `chat_blocked` (
`chat_id` INT( 11 ) NOT NULL ,
`usr_id` INT( 11 ) NOT NULL
) TYPE = MYISAM;
<#508>
<?php

// get all glossary definition pages
$res = $ilDB->query("SELECT content, page_id, parent_type FROM page_object ".
	"WHERE content LIKE '%MediaAlias OriginId%' AND parent_type='gdf'");

while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$content = $row["content"];

	// get all media aliases
	while (eregi("MediaAlias OriginId=\"(il__mob_([0-9]*))\"", $content, $found))
	{
		// insert mob usage record
		$q = "REPLACE INTO mob_usage (id, usage_type, usage_id) VALUES".
			" ('".$found[2]."', 'gdf:pg', '".$row["page_id"]."')";
		$ilDB->query($q);

		// remove id from content string to prevent endless while loop
		$content = eregi_replace($found[1], "", $content);
	}
}

?>
<#509>
ALTER TABLE `chat_user` ADD `kicked` TINYINT DEFAULT '0' AFTER `last_conn_timestamp`;
<#510>
ALTER TABLE `webr_items` ADD FULLTEXT (
`title`
	);
<#511>
<?php

// get all question pages
$res = $ilDB->query("SELECT content, page_id, parent_type FROM page_object ".
	"WHERE content LIKE '%MediaAlias OriginId%' AND parent_type='qpl'");

while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$content = $row["content"];

	// get all media aliases
	while (eregi("MediaAlias OriginId=\"(il__mob_([0-9]*))\"", $content, $found))
	{
		// insert mob usage record
		$q = "REPLACE INTO mob_usage (id, usage_type, usage_id) VALUES".
			" ('".$found[2]."', 'qpl:pg', '".$row["page_id"]."')";
		$ilDB->query($q);

		// remove id from content string to prevent endless while loop
		$content = eregi_replace($found[1], "", $content);
	}
}

?>
<#512>
<?php
	// convert referral_comment and hobby textareas in usr_data:
	// change html entities &lt; &gt; &amp; to normal characters because
	// the form was changed
	$res = $ilDB->query("SELECT usr_id, referral_comment, hobby FROM usr_data");
	while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$newhobby = str_replace("&amp;", "&", str_replace("&gt;", ">", str_replace("&lt;", "<", $row["hobby"])));
		$newcomment = str_replace("&amp;", "&", str_replace("&gt;", ">", str_replace("&lt;", "<", $row["referral_comment"])));
		if ((strcmp($row["hobby"], $newhobby) != 0) || (strcmp($row["referral_comment"], $newcomment) != 0))
		{
			$q = sprintf("UPDATE usr_data SET hobby = %s, referral_comment = %s WHERE usr_id = %s",
				$ilDB->quote($newhobby . ""),
				$ilDB->quote($newcomment . ""),
				$ilDB->quote($row["usr_id"] . "")
			);
			$ilDB->query($q);
		}
	}
?>
<#513>
CREATE TABLE `object_description` (
  `obj_id` int(11) NOT NULL default '0',
  `description` text  NOT NULL,
  PRIMARY KEY  (`obj_id`)
) TYPE=MyISAM;
<#514>
ALTER TABLE `object_translation` CHANGE `description` `description` TEXT  NULL DEFAULT NULL;
<#515>
<?php
	// reconstruct original id's which were set wrong due to test duplication
	$res = $ilDB->query("SELECT question_id, original_id FROM qpl_questions WHERE original_id > 0");
	while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$original_id = $row["original_id"];
		$question_id = $row["question_id"];
		$last_original_id = $row["original_id"];
		$last_question_id = $row["question_id"];
		// DBUPDATE-BUGFIX
		// add a fix for a previously update step run which was not successful and
		// ended in an endless loop
		if ($original_id == $question_id)
		{
			$update_query = sprintf("UPDATE qpl_questions SET original_id = NULL WHERE question_id = %s",
				$ilDB->quote($question_id . "")
			);
			$result_update = $ilDB->query($update_query);
		}
		else
		{
			while ($last_original_id > 0)
			{
				$search_query = sprintf("SELECT question_id, original_id FROM qpl_questions WHERE question_id = %s",
					$ilDB->quote($last_original_id . "")
				);
				$result_search = $ilDB->query($search_query);
				if ($result_search->numRows() == 0)
				{
					// no original found
					$last_original_id = 0;
				}
				else
				{
					$search_row = $result_search->fetchRow(DB_FETCHMODE_ASSOC);
					$last_original_id = $search_row["original_id"];
					$last_question_id = $search_row["question_id"];
				}
			}
		}
		if ($last_question_id != $original_id)
		{
			// DBUPDATE-BUGFIX
			// added && ($last_question_id != $question_id) to prevent setting the
			// original_id equal to the question_id which could happen when the original
			// question no longer exists
			if ((($last_question_id > 0) && ($question_id > 0)) && ($last_question_id != $question_id))
			{
				$update_query = sprintf("UPDATE qpl_questions SET original_id = %s WHERE question_id = %s",
					$ilDB->quote($last_question_id . ""),
					$ilDB->quote($question_id . "")
				);
				$result_update = $ilDB->query($update_query);
			}
		}
	}
?>
<#516>
ALTER TABLE `webr_items` ADD `description` TEXT NOT NULL AFTER `title`;

<#517>
ALTER TABLE `ilinc_data` DROP `class_id`;
ALTER TABLE `ilinc_data` DROP `user_id`;

ALTER TABLE `ilinc_data` ADD `contact_name` VARCHAR( 255 ) ,
ADD `contact_responsibility` VARCHAR( 255 ) ,
ADD `contact_phone` VARCHAR( 255 ) ,
ADD `contact_email` VARCHAR( 255 ) ,
ADD `activation_unlimited` TINYINT( 2 ) ,
ADD `activation_start` INT( 11 ) ,
ADD `activation_end` INT( 11 ) ,
ADD `activation_offline` ENUM( 'y', 'n' ) ,
ADD `subscription_unlimited` TINYINT( 2 ) ,
ADD `subscription_start` INT( 11 ) ,
ADD `subscription_end` INT( 11 ) ,
ADD `subscription_type` TINYINT( 2 ) ,
ADD `subscription_password` VARCHAR( 32 ) ;

CREATE TABLE `ilinc_registration` (
`obj_id` INT UNSIGNED NOT NULL ,
`usr_id` INT UNSIGNED NOT NULL ,
`usr_text` VARCHAR( 255 ) ,
`application_date` DATETIME,
PRIMARY KEY ( `obj_id` ) ,
INDEX ( `usr_id` )
);
<#518>
DELETE FROM object_description;
<#519>
<?php
$wd = getcwd();
chdir('..');

$tree =& new ilTree(ROOT_FOLDER_ID);
$GLOBALS['tree'] = $tree;
$rbacadmin =& new ilRbacAdmin();
$rbacreview =& new ilRbacReview();

$query = "SELECT obd.obj_id as objid,obr.ref_id as refid FROM object_data as obd,object_reference as obr ".
	"WHERE obd.obj_id = obr.obj_id ".
	"AND type = 'chat'";

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($tree->isInTree($row->refid) and !count($tree->getChilds($row->refid)))
	{
		// 1. Insert in object_data
		$query = "INSERT INTO object_data ".
			"(type,title,description,owner,create_date,last_update,import_id) ".
			"VALUES ".
			"('rolf','".$row->objid."','(ref_id ".$row->refid.")',".
			"'6',now(),now(),'')";

		$ilDB->query($query);
		
		// 2. Get id of new role folder
		$rolf_id = $ilDB->getLastInsertId();

		// 3. Create reference
		$query = "INSERT INTO object_reference ".
			"(obj_id) VALUES ('".$rolf_id."')";
		$ilDB->query($query);

		$rolf_ref_id = $ilDB->getLastInsertId();

		// 4. Insert in tree
		$tree->insertNode($rolf_ref_id,$row->refid);

		// Set permissions
		foreach ($rbacreview->getParentRoleIds($row->refid) as $parRol)
		{
			$ops = $rbacreview->getOperationsOfRole($parRol["obj_id"],'rolf', $parRol["parent"]);
			$rbacadmin->grantPermission($parRol["obj_id"], $ops,$rolf_ref_id);
		}

		// Add new lokal moderator role
		$query = "INSERT INTO object_data ".
			"(type,title,description,owner,create_date,last_update,import_id) ".
			 "VALUES ".
			"('role','il_chat_moderator_".$row->refid."','"."Moderator of chat obj_no.".$row->objid."',".
			"'6',now(),now(),'')";

		$ilDB->query($query);
			
		$role_id = $ilDB->getLastInsertId();

		// Insert role_data entry
		$query = "INSERT INTO role_data ".
			"(role_id,allow_register,assign_users) ".
			"VALUES ".
			"('".$role_id."','0','1')";
		$ilDB->query($query);

		// Assign this role to role folder
		$rbacadmin->assignRoleToFolder($role_id,$rolf_ref_id);

		// Grant permissions: visible,read,write,chat_moderate
		$permissions = ilRbacReview::_getOperationIdsByName(array('visible','read','moderate'));
		$rbacadmin->grantPermission($role_id,
									$permissions,
									$row->refid);

	}


}
chdir($wd);
?>

<#520>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#521>
REPLACE INTO settings (keyword, value) VALUES ('custom_icon_big_width', 32);
REPLACE INTO settings (keyword, value) VALUES ('custom_icon_big_height', 32);
REPLACE INTO settings (keyword, value) VALUES ('custom_icon_small_width', 22);
REPLACE INTO settings (keyword, value) VALUES ('custom_icon_small_height', 22);
REPLACE INTO settings (keyword, value) VALUES ('icon_position_in_lists', 'header');
<#522>
CREATE TABLE container_settings
(
	id INT NOT NULL,
	keyword char(40) NOT NULL,
	value char(50),
	PRIMARY KEY (id, keyword)
) TYPE = MYISAM;
<#523>
CREATE TABLE note
(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	rep_obj_id INT NOT NULL,
	obj_id INT NOT NULL,
	obj_type CHAR(10),
	type INT NOT NULL,
	author INT NOT NULL,
	text MEDIUMTEXT,
	label INT NOT NULL,
	creation_date DATETIME,
	INDEX i_author (author),
	INDEX i_obj (rep_obj_id, obj_id, obj_type)
) TYPE = MYISAM;
<#524>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#525>
ALTER TABLE note ADD update_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00';
<#526>
ALTER TABLE content_object ADD COLUMN pub_notes ENUM('y','n') NOT NULL DEFAULT 'n';
<#527>
<?php
// non-member template for course object
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		 "VALUES ('rolt', 'il_crs_non_member', 'Non-member template for course object', -1, now(), now())";
$this->db->query($query);

$query = "SELECT LAST_INSERT_ID() as obj_id";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$obj_id = $row->obj_id;

$rbacadmin =& new ilRbacAdmin();

$admin = array();
$admin["crs"] = array(2,7,8);
foreach($admin as $type => $ops)
{
	$rbacadmin->setRolePermission($obj_id,$type,$ops,ROLE_FOLDER_ID);
}

$rbacadmin->assignRoleToFolder($obj_id,ROLE_FOLDER_ID,"n");
?>
<#528>
CREATE TABLE `service` (
	`name` VARCHAR(100) NOT NULL PRIMARY KEY,
	`dir` VARCHAR(200) NOT NULL
) COMMENT = 'ILIAS Modules';

CREATE TABLE `service_class` (
	`class` VARCHAR(100) NOT NULL PRIMARY KEY,
	`service` VARCHAR(100) NOT NULL,
	`dir` VARCHAR(200) NOT NULL
) COMMENT = 'Class information of ILIAS Modules';
<#529>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#530>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#531>
<?php

function _lookupObjId($a_id)
{
	global $ilDB;

	$query = "SELECT obj_id FROM object_reference ".
		"WHERE ref_id = '".$a_id."'";
	$res = $ilDB->query($query);
	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		return $row->obj_id;
	}
	return 0;
}

  // Fix chat reference bug
$query = "SELECT * FROM chat_records ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ilDB->query("UPDATE chat_records SET chat_id = '".
				 _lookupObjId($row->chat_id)."' WHERE record_id = '".$row->record_id."'");
}
$query = "SELECT * FROM chat_room_messages ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ilDB->query("UPDATE chat_room_messages SET chat_id = '".
				 _lookupObjId($row->chat_id)."' WHERE entry_id = '".$row->entry_id."'");
}
$query = "SELECT * FROM chat_rooms ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ilDB->query("UPDATE chat_rooms SET chat_id = '".
				 _lookupObjId($row->chat_id)."' WHERE room_id = '".$row->room_id."'");
}
$ilDB->query("DELETE FROM chat_user");

?>
<#532>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#533>
<?php

global $log;
$GLOBALS['ilLog'] =& $log;


  // add chat below ChatSettings for personal desktop chat

  // Get chat settings id
$query = "SELECT * FROM object_data LEFT JOIN object_reference USING(obj_id) WHERE type = 'chac'";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$chac_ref_id = $row->ref_id;
}

$query = "INSERT INTO object_data ".
	"(type,title,description,owner,create_date,last_update,import_id) ".
	"VALUES ".
	"('chat','Public chat','Public chat',".
	"'6',now(),now(),'')";

$ilDB->query($query);
$chat_id = $ilDB->getLastInsertId();

// Create reference
$query = "INSERT INTO object_reference ".
	"(obj_id) VALUES ('".$chat_id."')";
$ilDB->query($query);

$chat_ref_id = $ilDB->getLastInsertId();

// put in tree
$tree =& new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($chat_ref_id,$chac_ref_id);

// Create role folder
$query = "INSERT INTO object_data ".
	"(type,title,description,owner,create_date,last_update,import_id) ".
	"VALUES ".
	"('rolf','".$chat_id."','(ref_id ".$chat_ref_id.")',".
	"'6',now(),now(),'')";

$ilDB->query($query);
$rolf_id = $ilDB->getLastInsertId();

// Create reference
$query = "INSERT INTO object_reference ".
	"(obj_id) VALUES ('".$rolf_id."')";
$ilDB->query($query);

$rolf_ref_id = $ilDB->getLastInsertId();

// put in tree
$tree->insertNode($rolf_ref_id,$chat_ref_id);

// Create role
$query = "INSERT INTO object_data ".
	"(type,title,description,owner,create_date,last_update,import_id) ".
	"VALUES ".
	"('role','il_chat_moderator_".$chat_ref_id."','Moderator of chat obj_no.".$chat_id."',".
	"'6',now(),now(),'')";

$ilDB->query($query);
$role_id = $ilDB->getLastInsertId();

// Insert role_data
$query = "INSERT INTO role_data set role_id = '".$role_id."'";
$ilDB->query($query);


$permissions = ilRbacReview::_getOperationIdsByName(array('visible','read','moderate'));
$rbacadmin = new ilRbacAdmin();
$rbacadmin->grantPermission($role_id,
							$permissions,
							$chat_ref_id);
$rbacadmin->assignRoleToFolder($role_id,$rolf_ref_id);
?>
<#534>
ALTER TABLE `qpl_questions` ADD `textgap_rating` ENUM( 'ci', 'cs', 'l1', 'l2', 'l3', 'l4', 'l5' ) AFTER `maxNumOfChars` ;
<#535>
CREATE TABLE `qpl_questionpool` (
`id_questionpool` INT NOT NULL AUTO_INCREMENT ,
`obj_fi` INT NOT NULL ,
`online` ENUM( '0', '1' ) NOT NULL ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `id_questionpool` ) ,
INDEX ( `obj_fi` )
);
<#536>
<?php
	// set all existing test question pools online
	$query = "SELECT * FROM object_data WHERE type = 'qpl'";
	$result = $ilDB->query($query);
	if ($result->numRows() > 0)
	{
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$checkquery = sprintf("SELECT id_questionpool FROM qpl_questionpool WHERE obj_fi = %s",
				$ilDB->quote($row["obj_id"] . "")
			);
			$checkresult = $ilDB->query($checkquery);
			if ($checkresult->numRows() == 0)
			{
				$insertquery = sprintf("INSERT INTO qpl_questionpool (online, obj_fi) VALUES ('1', %s)",
					$ilDB->quote($row["obj_id"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
			}
		}
	}
?>
<#537>
CREATE TABLE `survey_questionpool` (
`id_questionpool` INT NOT NULL AUTO_INCREMENT ,
`obj_fi` INT NOT NULL ,
`online` ENUM( '0', '1' ) NOT NULL ,
`TIMESTAMP` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `id_questionpool` ) ,
INDEX ( `obj_fi` )
);
<#538>
<?php
	// set all existing test question pools online
	$query = "SELECT * FROM object_data WHERE type = 'spl'";
	$result = $ilDB->query($query);
	if ($result->numRows() > 0)
	{
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$checkquery = sprintf("SELECT id_questionpool FROM survey_questionpool WHERE obj_fi = %s",
				$ilDB->quote($row["obj_id"] . "")
			);
			$checkresult = $ilDB->query($checkquery);
			if ($checkresult->numRows() == 0)
			{
				$insertquery = sprintf("INSERT INTO survey_questionpool (online, obj_fi) VALUES ('1', %s)",
					$ilDB->quote($row["obj_id"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
			}
		}
	}
?>
<#539>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#540>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#541>
CREATE TABLE IF NOT EXISTS `webr_params` (
  `param_id` int(11) NOT NULL auto_increment,
  `webr_id` int(11) NOT NULL default '0',
  `link_id` int(11) NOT NULL default '0',
  `name` char(128)  NOT NULL default '',
  `value` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`param_id`),
  KEY `link_id` (`link_id`)
) TYPE = MyISAM;
<#542>
ALTER TABLE bookmark_data ADD description varchar(255) NOT NULL DEFAULT '' AFTER title;
<#543>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#544>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#545>
ALTER TABLE note ADD subject varchar(200) NOT NULL DEFAULT '';
<#546>
INSERT INTO `tst_test_type` ( `test_type_id` , `type_tag` ) VALUES ('5', 'tt_varying_randomtest');
ALTER TABLE `tst_solutions` ADD `pass` INT DEFAULT '0' NOT NULL AFTER `points` ;
ALTER TABLE `tst_test_random_question` ADD `pass` INT DEFAULT '0' NOT NULL AFTER `sequence` ;
ALTER TABLE `tst_test_result` ADD `pass` INT DEFAULT '0' NOT NULL AFTER `points` ;
<#547>
ALTER TABLE object_reference ADD COLUMN deleted datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
<#548>
CREATE INDEX obj_del ON object_reference(deleted);
<#549>
<?php
$query = "SELECT * FROM tree WHERE tree < 0";
$result = $ilDB->query($query);
while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	$q = "UPDATE object_reference SET deleted=now() WHERE ref_id='".
		$row["child"]."'";
	$ilDB->query($q);
}
?>
<#550>
ALTER TABLE `tst_tests` ADD `hide_previous_results` ENUM( '0', '1' ) DEFAULT '0' NOT NULL AFTER `nr_of_tries` ;

<#551>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#552>
ALTER TABLE `frm_settings` ADD `anonymized` TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER `default_view`;

<#553>
CREATE TABLE IF NOT EXISTS `ut_learning_progress` (
  `lp_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `obj_type` char(4)  NOT NULL default '',
  `obj_id` int(11) NOT NULL default '0',
  `spent_time` int(10) NOT NULL default '0',
  `access_time` int(10) NOT NULL default '0',
  `visits` int(4) NOT NULL default '0',
  PRIMARY KEY  (`lp_id`),
  KEY `user_obj` (`user_id`,`obj_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

<#554>

CREATE TABLE IF NOT EXISTS `ut_login` (
  `usr_id` int(11) NOT NULL default '0',
  `login_time` int(10) NOT NULL default '0'
) TYPE=MyISAM;

<#555>
CREATE TABLE IF NOT EXISTS `ut_lp_filter` (
  `lpf_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `shown` text  NOT NULL,
  `hidden` text  NOT NULL,
  `mode` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`lpf_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

<#556>
CREATE TABLE IF NOT EXISTS `ut_lp_settings` (
  `lps_id` int(11) NOT NULL auto_increment,
  `obj_id` int(11) NOT NULL default '0',
  `obj_type` char(4) NOT NULL default '',
  `mode` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`lps_id`),
  KEY `obj_id` (`obj_id`)
) TYPE=MyISAM  AUTO_INCREMENT=1;
<#557>
CREATE TABLE `frm_notification` (
  `notification_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `frm_id` int(11) NOT NULL default '0',
  `thread_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`notification_id`)
	) TYPE=MyISAM  AUTO_INCREMENT=1;
<#558>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#559>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#560>
ALTER TABLE `tst_tests` ADD `pass_scoring` ENUM( '0', '1' ) DEFAULT '0' NOT NULL AFTER `mc_scoring` ;
<#561>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#562>
ALTER TABLE  `tst_test_result` DROP INDEX  `user_fi` , ADD UNIQUE  `user_fi` (  `user_fi` ,  `test_fi` ,  `question_fi` ,  `pass` );
<#563>
<?php
	// set user's style to delos (if it has been blueshadow)
	$query = "SELECT u1.usr_id, u1.value as skin, u2.value as style ".
		"FROM usr_pref AS u1, usr_pref AS u2 ".
		"WHERE u1.usr_id = u2.usr_id AND u1.keyword = 'skin' AND u2.keyword = 'style' ";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		if (($row["skin"] == "default" && $row["style"] == "blueshadow") ||
			$row["skin"] == "blueshadow2" && $row["style"] == "blueshadow2")
		{
			$q = "UPDATE usr_pref SET value = 'default' WHERE ".
				" usr_id = '".$row["usr_id"]."' AND ".
				" keyword = 'skin'";
			$ilDB->query($q);
			$q = "UPDATE usr_pref SET value = 'delos' WHERE ".
				" usr_id = '".$row["usr_id"]."' AND ".
				" keyword = 'style'";
			$ilDB->query($q);
		}
	}
?>
<#564>
<?php
	// set system default style to delos (if it has been blueshadow)
	$ini = new ilIniFile(CLIENT_WEB_DIR."/client.ini.php");
	$ini->read();
	if (($ini->readVariable("layout","skin") == "default" &&
		$ini->readVariable("layout","style") == "blueshadow") ||
		($ini->readVariable("layout","skin") == "blueshadow2" &&
		$ini->readVariable("layout","style") == "blueshadow2"))
	{
		$ini->setVariable("layout", "skin", "default");
		$ini->setVariable("layout", "style", "delos");
		$ini->write();
	}
?>
<#565>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#566>
CREATE TABLE style_data
(
	id INT NOT NULL,
	uptodate TINYINT(2) DEFAULT 0
);
<#567>
<?php
	// create style data record for each style
	$query = "SELECT * FROM object_data ".
		"WHERE type='sty' ";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$q = "INSERT INTO style_data (id, uptodate) VALUES ".
			"('".$row["obj_id"]."','0')";
		$ilDB->query($q);
	}
?>
<#568>
ALTER TABLE `rbac_fa` ADD `protected` ENUM( 'y', 'n' ) DEFAULT 'n';
UPDATE rbac_fa SET protected = 'y' WHERE rol_id = '2';

<#569>
<?php
// set admin templates to protected status
$query = "SELECT * FROM object_data WHERE type='rolt' AND title IN ('il_crs_admin','il_icrs_admin','il_grp_admin','Local Administrator')";
$result = $ilDB->query($query);
while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	$q = "UPDATE rbac_fa SET protected='y' WHERE rol_id='".$row["obj_id"]."'";
	$ilDB->query($q);
}
?>
<#570>
ALTER TABLE `ut_lp_settings`
DROP `lps_id`;

<#571>
ALTER TABLE `ut_lp_settings` DROP INDEX `obj_id`,
	ADD PRIMARY KEY ( `obj_id` );

<#572>
CREATE TABLE IF NOT EXISTS `ut_lp_collections` (
  `obj_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  KEY `obj_id` (`obj_id`,`item_id`)
) TYPE=MyISAM;
<#573>
DELETE FROM tst_test_type WHERE type_tag = 'tt_navigation_controlling';
UPDATE tst_tests SET test_type_fi = 1 WHERE test_type_fi = 3;
<#574>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#575>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#576>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#577>
ALTER TABLE `ut_lp_settings` ADD `visits` INT( 4 ) DEFAULT '0' AFTER `mode`;
<#578>
ALTER TABLE `content_object` ADD `downloads_public_active` ENUM('y','n') DEFAULT 'y' NOT NULL AFTER `downloads_active`;

<#579>
ALTER TABLE `tst_tests` ADD `hide_title_points` ENUM( '0', '1' ) DEFAULT '0' NOT NULL AFTER `hide_previous_results` ;

<#580>
ALTER TABLE `ut_login` RENAME `ut_online`;

<#581>
ALTER TABLE `ut_online` ADD PRIMARY KEY ( `usr_id` );

<#582>
ALTER TABLE `ut_online` CHANGE `login_time` `online_time` INT( 11 ) NOT NULL DEFAULT '0';

<#583>
ALTER TABLE `ut_online` ADD `access_time` INT( 10 ) NOT NULL ;

<#584>
ALTER TABLE `il_meta_format`
  DROP `parent_type`,
  DROP `parent_id`;

<#585>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#586>
ALTER TABLE file_data ADD mode char(8) DEFAULT 'object';
<#587>
<?php
// set admin templates to protected status
$query = "SELECT * FROM file_data";
$result = $ilDB->query($query);
while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	$q2 = "SELECT * FROM file_usage WHERE id = ".$ilDB->quote($row["file_id"]);
	$r2 = $ilDB->query($q2);
	if ($dummy = $r2->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$q3 = "UPDATE file_data SET mode=".$ilDB->quote("filelist").
			" WHERE file_id = ".$row["file_id"];
		$ilDB->query($q3);
	}
}
?>
<#588>
<?php
$wd = getcwd();

include_once 'Services/Migration/DBUpdate_426/classes/class.ilMDCreator.php';
include_once 'Services/Migration/DBUpdate_426/classes/class.ilMD.php';

$file_ids = array();
$query = "SELECT file_type, title, description, obj_id, file_id, file_name, version".
	" FROM file_data, object_data WHERE mode = 'object'".
	" AND file_data.file_id = object_data.obj_id";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$md_creator = new ilMDCreator($row["obj_id"], $row["obj_id"], 'file');
	$md_creator->setTitle($row['title']);
	$md_creator->setTitleLanguage('');
	$md_creator->setDescription($row['description']);
	$md_creator->setDescriptionLanguage('');
	$md_creator->setLanguage('');
	$md_creator->create();
	
//echo "<br>file:".$row["obj_id"].":".$row["title"].":".$row["description"].":".$row["file_type"].":";
	
	$file = CLIENT_DATA_DIR."/files/file_".$row["obj_id"]."/".$row["file_name"];

	if (@!is_file($file))
	{
		$version_subdir = "/".sprintf("%03d", $row["version"]);
		$file = CLIENT_DATA_DIR."/files/file_".$row["obj_id"].$version_subdir."/".$row["file_name"];
	}

	if (is_file($file))
	{
		$size = filesize($file);
	}
	else
	{
		$size = 0;
	}

	// create technical section
	$md_obj =& new ilMD($row["obj_id"], $row["obj_id"], 'file');;
	$technical = $md_obj->addTechnical();
	$technical->setSize($size);
	$technical->save();
	$format = $technical->addFormat();
	$format->setFormat($row["file_type"]);
	$format->save();
	$technical->update();

}

?>
<#589>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#590>
ALTER TABLE `survey_survey` CHANGE `evaluation_access` `evaluation_access` ENUM( '0', '1', '2' ) DEFAULT '0' NOT NULL;

<#591>
ALTER TABLE `rbac_operations` ADD `class` ENUM('create','general','object','rbac','admin','notused') DEFAULT 'notused' NOT NULL, ADD `op_order` SMALLINT UNSIGNED;

<#592>
<?php
$query = "SELECT * FROM rbac_operations";
$result = $ilDB->query($query);

while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	switch($row['operation'])
	{
		case 'visible':
		case 'read':
		case 'write':
		case 'delete':
			$perm_class = 'general';
			break;
			
		case 'join':
		case 'leave':
		case 'edit_post':
		case 'delete_post':
		case 'smtp_mail':
		case 'system_message':
		case 'cat_administrate_users':
		case 'edit_roleassignment':
		case 'edit_userassignment':
		case 'invite':
		case 'mail_visible':
		case 'moderate':
		case 'participate':
		case 'push_desktop_items':
		case 'read_users':
		case 'search':
		case 'leave':
			$perm_class = 'object';
			break;
			
		case 'edit_permission':
			$perm_class = 'rbac';
			break;

		case 'create_cat':
		case 'create_chat':
		case 'create_crs':
		case 'create_dbk':
		case 'create_exc':
		case 'create_file':
		case 'create_fold':
		case 'create_frm':
		case 'create_glo':
		case 'create_grp':
		case 'create_htlm':
		case 'create_icla':
		case 'create_icrs':
		case 'create_lm':
		case 'create_mep':
		case 'create_qpl':
		case 'create_role':
		case 'create_rolt':
		case 'create_sahs':
		case 'create_spl':
		case 'create_svy':
		case 'create_tax':
		case 'create_tst':
		case 'create_user':
		case 'create_webr':
			$perm_class = 'create';
			break;

		default:
			$perm_class = 'notused';
			break;
	}

	$q2 = "UPDATE rbac_operations SET class='".$perm_class."' WHERE operation = '".$row['operation']."'";
	$ilDB->query($q2);
}

$query = "UPDATE rbac_operations SET op_order='100' WHERE operation='visible'";
$ilDB->query($query);
$query = "UPDATE rbac_operations SET op_order='110' WHERE operation='read'";
$ilDB->query($query);
$query = "UPDATE rbac_operations SET op_order='120' WHERE operation='write'";
$ilDB->query($query);
$query = "UPDATE rbac_operations SET op_order='130' WHERE operation='delete'";
$ilDB->query($query);

$ilCtrlStructureReader->getStructure();
?>
<#593>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#594>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#595>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#596>
DROP TABLE `ut_lp_filter`;
CREATE TABLE `ut_lp_filter` (
  `usr_id` int(11) NOT NULL default '0',
  `filter_type` varchar(4) NOT NULL default '',
  `root_node` int(11) NOT NULL default '0',
  `hidden` text NOT NULL,
  `query_string` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`usr_id`)
) TYPE=MyISAM;
<#597>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#598>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#599>
CREATE table ctrl_structure
(
	root_class	varchar(40) NOT NULL PRIMARY KEY,
	call_node	MEDIUMTEXT,
	forward	MEDIUMTEXT,
	parent	MEDIUMTEXT
) TYPE=MyISAM;
<#600>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#601>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#602>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#603>
<?php

  // Add operation edit_learing_progress
$query = "INSERT INTO rbac_operations SET operation = 'edit_learning_progress', ".
	"description = 'edit learning progress', ".
	"class = 'object'";

$res = $ilDB->query($query);

$new_ops_id = $ilDB->getLastInsertId();

// Get type ids of 'lm', 'dbk', 'sahs', 'htlm' ,'tst' and 'crs'
$query = "SELECT obj_id FROM object_data WHERE title IN ('lm','dbk','sahs','htlm','tst','crs') ".
	"AND type = 'typ'";

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$type_ids[] = $row->obj_id;
}
// ASSIGN new operation to object types
foreach($type_ids as $typ_id)
{
	$query = "INSERT INTO rbac_ta SET typ_id = '".$typ_id."', ".
		"ops_id = '".$new_ops_id."'";

	$ilDB->query($query);
}

// get template il_crs_admin Author and Local Administrator
$query = "SELECT obj_id FROM object_data WHERE title IN ('il_crs_admin','Author','Local Administrator') ".
	"AND type = 'rolt'";

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$rolt_ids[] = $row->obj_id;
}

// ASSIGN new operation to role templates
foreach($rolt_ids as $rolt_id)
{
	$query = "INSERT INTO rbac_templates SET rol_id = '".$rolt_id."', ".
		"type = 'lm', ".
		"ops_id = '".$new_ops_id."', ".
		"parent = '".ROLE_FOLDER_ID."'";
	$ilDB->query($query);

	$query = "INSERT INTO rbac_templates SET rol_id = '".$rolt_id."', ".
		"type = 'tst', ".
		"ops_id = '".$new_ops_id."', ".
		"parent = '".ROLE_FOLDER_ID."'";
	$ilDB->query($query);
	$query = "INSERT INTO rbac_templates SET rol_id = '".$rolt_id."', ".
		"type = 'dbk', ".
		"ops_id = '".$new_ops_id."', ".
		"parent = '".ROLE_FOLDER_ID."'";
	$ilDB->query($query);
	$query = "INSERT INTO rbac_templates SET rol_id = '".$rolt_id."', ".
		"type = 'sahs', ".
		"ops_id = '".$new_ops_id."', ".
		"parent = '".ROLE_FOLDER_ID."'";
	$ilDB->query($query);
	$query = "INSERT INTO rbac_templates SET rol_id = '".$rolt_id."', ".
		"type = 'htlm', ".
		"ops_id = '".$new_ops_id."', ".
		"parent = '".ROLE_FOLDER_ID."'";
	$ilDB->query($query);
	$query = "INSERT INTO rbac_templates SET rol_id = '".$rolt_id."', ".
		"type = 'crs', ".
		"ops_id = '".$new_ops_id."', ".
		"parent = '".ROLE_FOLDER_ID."'";
	$ilDB->query($query);
}
?>
<#604>
CREATE TABLE IF NOT EXISTS `ut_lp_marks` (
  `obj_id` int(11) NOT NULL default '0',
  `mark` char(32)  NOT NULL default '',
  `comment` char(255) NOT NULL default '',
  PRIMARY KEY  (`obj_id`)
) Type=MyISAM;

<#605>
DROP TABLE IF EXISTS `ut_lp_marks`;
CREATE TABLE `ut_lp_marks` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `completed` int(1) NOT NULL default '0',
  `mark` char(32)  NOT NULL default '',
  `comment` char(255) NOT NULL default '',
  PRIMARY KEY  (`obj_id`)
) Type=MyISAM;

<#606>
DROP TABLE `ut_lp_filter`;
CREATE TABLE `ut_lp_filter` (
  `usr_id` int(11) NOT NULL default '0',
  `filter_type` varchar(4) NOT NULL default '',
  `root_node` int(11) NOT NULL default '0',
  `hidden` text NOT NULL,
  `query_string` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`usr_id`)
) TYPE=MyISAM;

<#607>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#608>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#609>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#610>
DROP TABLE IF EXISTS `ut_lp_marks`;
CREATE TABLE `ut_lp_marks` (
  `obj_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `completed` int(1) NOT NULL default '0',
  `mark` char(32) NOT NULL default '',
  `comment` char(255) NOT NULL default '',
  PRIMARY KEY  (`obj_id`,`usr_id`),
  KEY `obj_usr` (`obj_id`,`usr_id`)
) Type=MyISAM;

<#611>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#612>
<?php

// get all ref ids in scorm tracking
$query = "SELECT DISTINCT ref_id FROM scorm_tracking";

$res = $ilDB->query($query);
while($rec = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$q2 = "SELECT * FROM object_reference WHERE ref_id = ".
		$ilDB->quote($rec["ref_id"]);
	$res2 = $ilDB->query($q2);
	$rec2 = $res2->fetchRow(DB_FETCHMODE_ASSOC);
	
	$q3 = "UPDATE scorm_tracking SET ref_id= ".
		$ilDB->quote($rec2["obj_id"]). " WHERE ".
		" ref_id = ".$ilDB->quote($rec["ref_id"]);
		
	$ilDB->query($q3);
}

?>
<#613>
ALTER TABLE scorm_tracking CHANGE ref_id obj_id INT;
<#614>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#615>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#616>
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
) TYPE=MyISAM ;
<#617>
CREATE TABLE `feedback_results` (
  `fb_id` int(11) NOT NULL default '0',
  `user_id` int(11) default NULL,
  `vote` int(11) NOT NULL default '0',
  `note` text NOT NULL,
  `votetime` int(11) NOT NULL default '0'
) TYPE=MyISAM;

<#618>
ALTER TABLE `frm_user_read` ADD INDEX `obj_usr` ( `obj_id`,`usr_id`);
<#619>
ALTER TABLE `frm_user_read` ADD INDEX `post_usr` ( `post_id` , `usr_id`);
<#620>
ALTER TABLE `frm_thread_access` ADD INDEX `usr_thread` ( `thread_id` , `usr_id`);
<#621>
ALTER TABLE `frm_posts` ADD INDEX ( `pos_thr_fk` );
<#622>
ALTER TABLE `frm_posts` ADD INDEX ( `pos_top_fk`);
<#623>
ALTER TABLE `crs_settings` ADD `waiting_list` TINYINT( 1 ) DEFAULT '1' NOT NULL ;
<#624>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#625>
ALTER TABLE `tst_eval_users` DROP INDEX `test_fi_2`;
ALTER TABLE `tst_eval_users` DROP INDEX `user_fi`;
ALTER TABLE `tst_eval_users` DROP INDEX `evaluator_fi`;
ALTER TABLE `tst_eval_users` DROP INDEX `test_fi`;
ALTER TABLE `tst_eval_users` ADD INDEX ( `test_fi` , `evaluator_fi` , `user_fi` ) ;
DROP TABLE `tst_eval_groups`;
<#626>
ALTER TABLE `tst_eval_users` DROP `eval_users_id`;
ALTER TABLE `tst_eval_users` DROP `TIMESTAMP`;
<#627>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#628>
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
) TYPE = MyISAM;

<#629>
DROP TABLE IF EXISTS `usr_defined_data`;
CREATE TABLE `usr_defined_data` (
  `usr_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`usr_id`)
) TYPE = MyISAM;

<#630>
<?php
$query = "SELECT DISTINCT usr_id FROM usr_data";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO usr_defined_data ".
		"SET usr_id = '".$row->usr_id."'";
	$ilDB->query($query);
}
?>
<#631>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#632>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#633>
ALTER TABLE `frm_settings` ADD COLUMN `statistics_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `anonymized`;
?>
<#634>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#635>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('entity','date','description');
$table = 'il_meta_annotation';
$key = 'meta_annotation_id';

ilMDConvert($table,$fields,$key);
?>
<#636>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('purpose','description');
$table = 'il_meta_classification';
$key = 'meta_classification_id';

ilMDConvert($table,$fields,$key);
?>
<#637>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('role','date');
$table = 'il_meta_contribute';
$key = 'meta_contribute_id';

ilMDConvert($table,$fields,$key);
?>
<#638>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('description');
$table = 'il_meta_description';
$key = 'meta_description_id';

ilMDConvert($table,$fields,$key);
?>
<#639>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('interactivity_type','learning_resource_type','interactivity_level','semantic_density','intended_end_user_role',
				'context','difficulty','typical_learning_time');
$table = 'il_meta_educational';
$key = 'meta_educational_id';

ilMDConvert($table,$fields,$key);
?>
<#640>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('entity');
$table = 'il_meta_entity';
$key = 'meta_entity_id';

ilMDConvert($table,$fields,$key);
?>
<#641>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('format');
$table = 'il_meta_format';
$key = 'meta_format_id';

ilMDConvert($table,$fields,$key);
?>
<#642>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('general_structure','title','coverage');
$table = 'il_meta_general';
$key = 'meta_general_id';

ilMDConvert($table,$fields,$key);
?>
<#643>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('catalog','entry');
$table = 'il_meta_identifier';
$key = 'meta_identifier_id';

ilMDConvert($table,$fields,$key);
?>
<#644>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('catalog','entry');
$table = 'il_meta_identifier_';
$key = 'meta_identifier__id';

ilMDConvert($table,$fields,$key);
?>
<#645>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('keyword');
$table = 'il_meta_keyword';
$key = 'meta_keyword_id';

ilMDConvert($table,$fields,$key);
?>
<#646>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('language');
$table = 'il_meta_language';
$key = 'meta_language_id';

ilMDConvert($table,$fields,$key);
?>
<#647>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('lifecycle_status','meta_version','version_language');
$table = 'il_meta_lifecycle';
$key = 'meta_lifecycle_id';

ilMDConvert($table,$fields,$key);
?>
<#648>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('location','location_type');
$table = 'il_meta_location';
$key = 'meta_location_id';

ilMDConvert($table,$fields,$key);
?>
<#649>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('meta_data_scheme','language');
$table = 'il_meta_meta_data';
$key = 'meta_meta_data_id';

ilMDConvert($table,$fields,$key);
?>
<#650>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('kind');
$table = 'il_meta_relation';
$key = 'meta_relation_id';

ilMDConvert($table,$fields,$key);
?>
<#651>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('operating_system_name','operating_system_minimum_version','operating_system_maximum_version',
				'browser_name','browser_minimum_version','browser_maximum_version');
$table = 'il_meta_requirement';
$key = 'meta_requirement_id';

ilMDConvert($table,$fields,$key);
?>
<#652>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('description','costs','copyright_and_other_restrictions');
$table = 'il_meta_rights';
$key = 'meta_rights_id';

ilMDConvert($table,$fields,$key);
?>
<#653>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('taxon','taxon_id');
$table = 'il_meta_taxon';
$key = 'meta_taxon_id';

ilMDConvert($table,$fields,$key);
?>
<#654>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('source','source_language');
$table = 'il_meta_taxon_path';
$key = 'meta_taxon_path_id';

ilMDConvert($table,$fields,$key);
?>
<#655>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('size','installation_remarks','other_platform_requirements','duration');
$table = 'il_meta_technical';
$key = 'meta_technical_id';

ilMDConvert($table,$fields,$key);
?>
<#656>
<?php
include_once './Services/Migration/DBUpdate_635/inc.meta_data_converter.php';

$fields = array('typical_age_range','typical_age_range_max','typical_age_range_min');
$table = 'il_meta_typical_age_range';
$key = 'meta_typical_age_range_id';

ilMDConvert($table,$fields,$key);
?>

<#657>
ALTER TABLE `il_meta_general` CHANGE `title` `title` TEXT NULL DEFAULT NULL;
ALTER TABLE `il_meta_location` CHANGE `parent_type` `parent_type` VARCHAR( 16 ) NULL;

<#658>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#659>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#660>
INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('15','17');
<#661>
<?php
// reconstruct accidently deleted tst_active datasets when "delete selected user datasets"
// in test maintenance was used

// collect all the missing tst_active datasets in $foundactive
$foundactive = array();
$query = "SELECT DISTINCT concat( tst_test_result.user_fi, '_', tst_test_result.test_fi ) ".
	", tst_test_result.user_fi, tst_test_result.test_fi, tst_active.user_fi AS active1, " .
	"tst_active.test_fi AS active2, tst_tests.random_test FROM tst_tests, tst_test_result " .
	"LEFT JOIN tst_active ON concat( tst_active.user_fi, '_', tst_active.test_fi ) = " .
	"concat( tst_test_result.user_fi, '_', tst_test_result.test_fi ) ".
	"WHERE isnull( tst_active.user_fi ) ".
	"AND tst_test_result.test_fi = tst_tests.test_id";
$result = $ilDB->query($query);
if ($result->numRows())
{
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		array_push($foundactive, array($row["test_fi"], $row["user_fi"], $row["random_test"]));
	}
}

// reconstruct the missing datasets
foreach ($foundactive as $missingarray)
{
	$test_id = $missingarray[0];
	$user_id = $missingarray[1];
	$is_random = $missingarray[2];
	// begin reconstruction
	$found = 0;
	if ($is_random)
	{
		// get number of questions in the test
		$query = sprintf("SELECT test_random_question_id FROM tst_test_random_question WHERE test_fi = %s AND user_fi = %s AND pass = 0",
			$ilDB->quote($test_id . ""),
			$ilDB->quote($user_id . "")
		);
		$result = $ilDB->query($query);
		$found = $result->numRows();
	}
	else
	{
		// get number of questions in the test
		$query = sprintf("SELECT test_question_id FROM tst_test_question WHERE test_fi = %s",
			$ilDB->quote($test_id . "")
		);
		$result = $ilDB->query($query);
		$found = $result->numRows();
	}
	if ($is_random)
	{
		// get maximum pass
		$query = sprintf("SELECT MAX(pass) AS maxpass FROM tst_test_random_question WHERE user_fi = %s AND test_fi = %s",
			$ilDB->quote($user_id . ""),
			$ilDB->quote($test_id . "")
		);
	}
	else
	{
		// get maximum pass
		$query = sprintf("SELECT MAX(pass) AS maxpass FROM tst_test_result WHERE user_fi = %s AND test_fi = %s",
			$ilDB->quote($user_id . ""),
			$ilDB->quote($test_id . "")
		);
	}
	$result = $ilDB->query($query);
	if ($result->numRows())
	{
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$pass = $row["maxpass"];
		
		$sequencearray = array();
		for ($i = 1; $i <= $found; $i++) array_push($sequencearray, $i);
		// re-add tst_active
		$query = sprintf("INSERT INTO tst_active (user_fi, test_fi, sequence, lastindex, tries) VALUES (%s, %s, %s, %s, %s)",
			$ilDB->quote($user_id . ""),
			$ilDB->quote($test_id . ""),
			$ilDB->quote(join(",",$sequencearray)),
			$ilDB->quote("1"),
			$ilDB->quote($pass . "")
		);
		$ilDB->query($query);
	}
}
?>
<#662>
<?php
	// correct accidently increased tries (when doubleclicking or reloading in test submission)

	function getNrOfResultsForPass($test_id, $user_id, $pass)
	{
		global $ilDB;
		$query = sprintf("SELECT test_result_id FROM tst_test_result WHERE test_fi = %s AND user_fi = %s AND pass = %s",
			$ilDB->quote($test_id . ""),
			$ilDB->quote($user_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		return $result->numRows();
	}
	
	global $log;
	$query = "SELECT * FROM tst_active WHERE tries > 1";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$tries = $row["tries"];
		while ((getNrOfResultsForPass($row["test_fi"], $row["user_fi"], $tries-1) == 0) && ($tries > 0))
		{
			$tries--;
		}
		if ($tries < $row["tries"])
		{
			$updatequery = sprintf("UPDATE tst_active SET tries = %s WHERE active_id = %s",
				$ilDB->quote($tries . ""),
				$ilDB->quote($row["active_id"] . "")
			);
			$ilDB->query($updatequery);
			$log->write("Update step #662: set tst_active.tries from ".$row["tries"]." to $tries for tst_active.active_id = " . $row["active_id"]);
		}
	}
	

?>
<#663>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#664>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#665>
<?php
	// update badly calculated text gaps in cloze questions due to missing checks for text gap rating

	function getTextgapPoints($gaprating, $a_original, $a_entered, $max_points)
	{
		$result = 0;
		switch ($gaprating)
		{
			case "ci":
				if (strcmp(strtolower($a_original), strtolower($a_entered)) == 0) $result = $max_points;
				break;
			case "cs":
				if (strcmp($a_original, $a_entered) == 0) $result = $max_points;
				break;
			case "l1":
				if (levenshtein($a_original, $a_entered) <= 1) $result = $max_points;
				break;
			case "l2":
				if (levenshtein($a_original, $a_entered) <= 2) $result = $max_points;
				break;
			case "l3":
				if (levenshtein($a_original, $a_entered) <= 3) $result = $max_points;
				break;
			case "l4":
				if (levenshtein($a_original, $a_entered) <= 4) $result = $max_points;
				break;
			case "l5":
				if (levenshtein($a_original, $a_entered) <= 5) $result = $max_points;
				break;
		}
		return $result;
	}

	global $log;
	$log->write("test&assessment text grap rating: starting with conversion. updating database entries for reached points of every user for every processed question");
	// update code
	$idx = 1;
	$query = "SELECT question_id, question_type_fi, textgap_rating FROM qpl_questions WHERE question_type_fi = 3 AND textgap_rating <> 'ci'";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$queryanswers = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY gap_id, aorder ASC",
			$ilDB->quote($row["question_id"] . "")
		);
		$resultanswers = $ilDB->query($queryanswers);
		$answers = array();
		while ($rowanswer = $resultanswers->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($answers, $rowanswer);
		}
		$querytests = sprintf("SELECT DISTINCT test_fi FROM tst_solutions WHERE question_fi = %s",
			$ilDB->quote($row["question_id"] . "")
		);
		$resulttests = $ilDB->query($querytests);
		$tests = array();
		while ($rowtest = $resulttests->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($tests, $rowtest["test_fi"]);
		}
		foreach ($tests as $test_id)
		{
			$queryusers = sprintf("SELECT DISTINCT user_fi FROM tst_solutions WHERE test_fi = %s AND question_fi = %s",
				$ilDB->quote($test_id . ""),
				$ilDB->quote($row["question_id"])
			);
			$resultusers = $ilDB->query($queryusers);
			$users = array();
			while ($rowuser = $resultusers->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($users, $rowuser["user_fi"]);
			}
			// now begin the conversion
			foreach ($users as $user_id)
			{
				$querysolutions = sprintf("SELECT * FROM tst_solutions WHERE test_fi = %s AND user_fi = %s AND question_fi = %s",
					$ilDB->quote($test_id . ""),
					$ilDB->quote($user_id . ""),
					$ilDB->quote($row["question_id"] . "")
				);
				$resultsolutions = $ilDB->query($querysolutions);
				switch ($row["question_type_fi"])
				{
					case 3:
						// close questions
						$found_value1 = array();
						$found_value2 = array();
						$user_result = array();
						while ($data = $resultsolutions->fetchRow(DB_FETCHMODE_ASSOC))
						{
							if (strcmp($data["value2"], "") != 0)
							{
								$user_result[$data["value1"]] = array(
									"gap_id" => $data["value1"],
									"value" => $data["value2"]
								);
							}
						}
						$points = 0;
						$counter = 0;
						$gaps = array();
						foreach ($answers as $key => $value)
						{
							if (!array_key_exists($value["gap_id"], $gaps))
							{
								$gaps[$value["gap_id"]] = array();
							}
							array_push($gaps[$value["gap_id"]], $value);
						}
						foreach ($user_result as $gap_id => $value) 
						{
							if ($gaps[$gap_id][0]["cloze_type"] == 0) 
							{
								$gappoints = 0;
								foreach ($gaps[$gap_id] as $k => $v) 
								{
									$gotpoints = getTextgapPoints($row["textgap_rating"], $v["answertext"], $value["value"], $v["points"]);
									if ($gotpoints > $gappoints) $gappoints = $gotpoints;
								}
								$points += $gappoints;
							} 
							else 
							{
								if ($value["value"] >= 0)
								{
									foreach ($gaps[$gap_id] as $answerkey => $answer)
									{
										if ($value["value"] == $answerkey)
										{
											$points += $answer["points"];
										}
									}
								}
							}
						}
						// save $points;
						break;
				}
				// check for special scoring options in test
				$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
					$ilDB->quote($test_id)
				);
				$resulttest = $ilDB->query($query);
				if ($resulttest->numRows() == 1)
				{
					$rowtest = $resulttest->fetchRow(DB_FETCHMODE_ASSOC);
					if ($rowtest["count_system"] == 1)
					{
						$maxpoints = 0;
						$query = sprintf("SELECT points FROM qpl_questions WHERE question_id = %s",
							$ilDB->quote($row["question_id"] . "")
						);
						$resultmaxpoints = $ilDB->query($query);
						if ($resultmaxpoints->numRows() == 1)
						{
							$rowmaxpoints = $resultmaxpoints->fetchRow(DB_FETCHMODE_ASSOC);
							$maxpoints = $rowmaxpoints["points"];
						}
						if ($points != $maxpoints)
						{
							$points = 0;
						}
					}
				}
				else
				{
					$points = 0;
				}
				$insertquery = sprintf("REPLACE tst_test_result (user_fi, test_fi, question_fi, points) VALUES (%s, %s, %s, %s)",
					$ilDB->quote($user_id . ""),
					$ilDB->quote($test_id . ""),
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($points . "")
				);
				$ilDB->query($insertquery);
				$log->write("  $idx. creating user result: $insertquery");
				$idx++;
			}
		}
	}
	$log->write("test&assessment: conversion finished. creating database entry for reached points of every user for every processed question");
?>
<#666>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#667>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#668>
CREATE TABLE lng_modules
(
	module varchar(30) not null,
	lang_key varchar(2) not null,
	lang_array mediumtext,
	primary key (module, lang_key)
);
<#669>
ALTER TABLE style_data ADD PRIMARY KEY (id);
ALTER TABLE style_data ADD COLUMN standard TINYINT(2) DEFAULT 0;
ALTER TABLE style_data ADD COLUMN category INT;
<#670>
ALTER TABLE style_data ADD COLUMN active TINYINT(2) DEFAULT 1;
<#671>
INSERT INTO `qpl_question_type` ( `question_type_id` , `type_tag` ) VALUES ('9', 'qt_numeric');
INSERT INTO `qpl_question_type` ( `question_type_id` , `type_tag` ) VALUES ('10', 'qt_textsubset');
<#672>
CREATE TABLE `qpl_numeric_range` (
`range_id` INT NOT NULL AUTO_INCREMENT ,
`lowerlimit` DOUBLE NOT NULL ,
`upperlimit` DOUBLE NOT NULL ,
`points` DOUBLE DEFAULT '0' NOT NULL ,
`aorder` INT DEFAULT '0' NOT NULL ,
`question_fi` INT NOT NULL ,
`lastchange` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `range_id` )
);
<#673>
UPDATE survey_questiontype SET type_tag = 'SurveyNominalQuestion' WHERE questiontype_id = 1;
UPDATE survey_questiontype SET type_tag = 'SurveyOrdinalQuestion' WHERE questiontype_id = 2;
UPDATE survey_questiontype SET type_tag = 'SurveyMetricQuestion' WHERE questiontype_id = 3;
UPDATE survey_questiontype SET type_tag = 'SurveyTextQuestion' WHERE questiontype_id = 4;
<#674>
ALTER TABLE survey_survey CHANGE anonymize anonymize ENUM('0','1','2') NOT NULL DEFAULT '0';
<#675>
ALTER TABLE `qpl_questions` ADD `correctanswers` INT NULL DEFAULT '0' AFTER `textgap_rating`;
ALTER TABLE `qpl_questions` ADD `keywords` TEXT NULL AFTER `maxNumOfChars`;
<#676>
CREATE TABLE `qpl_question_cloze` (
  `question_fi` int(11) NOT NULL default '0',
  `textgap_rating` enum('ci','cs','l1','l2','l3','l4','l5') default NULL,
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `qpl_question_essay` (
  `question_fi` int(11) NOT NULL default '0',
  `maxNumOfChars` int(11) NOT NULL default '0',
  `keywords` text,
  `textgap_rating` enum('ci','cs','l1','l2','l3','l4','l5') default NULL,
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `qpl_question_imagemap` (
  `question_fi` int(11) NOT NULL default '0',
  `image_file` varchar(100) default NULL,
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `qpl_question_javaapplet` (
  `question_fi` int(11) NOT NULL default '0',
  `image_file` varchar(100) default NULL,
  `params` text,
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `qpl_question_matching` (
  `question_fi` int(11) NOT NULL default '0',
  `shuffle` enum('0','1') NOT NULL default '1',
  `matching_type` enum('0','1') default NULL,
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `qpl_question_multiplechoice` (
  `question_fi` int(11) NOT NULL default '0',
  `shuffle` enum('0','1') NOT NULL default '1',
  `choice_response` enum('0','1') default NULL,
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `qpl_question_numeric` (
  `question_fi` int(11) NOT NULL default '0',
  `maxNumOfChars` int(11) NOT NULL default '0',
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `qpl_question_ordering` (
  `question_fi` int(11) NOT NULL default '0',
  `ordering_type` enum('0','1') default NULL,
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `qpl_question_textsubset` (
  `question_fi` int(11) NOT NULL default '0',
  `textgap_rating` enum('ci','cs','l1','l2','l3','l4','l5') default NULL,
  `correctanswers` int(11) default '0',
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;
<#677>
<?php
	$query = "SELECT * FROM qpl_questions";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		switch ($row["question_type_fi"])
		{
			case 1:
			case 2:
				$insertquery = sprintf("INSERT INTO qpl_question_multiplechoice (question_fi, shuffle, choice_response) VALUES (%s, %s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["shuffle"] . ""),
					$ilDB->quote($row["choice_response"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 3:
				$insertquery = sprintf("INSERT INTO qpl_question_cloze (question_fi, textgap_rating) VALUES (%s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["textgap_rating"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 4:
				$insertquery = sprintf("INSERT INTO qpl_question_matching (question_fi, shuffle, matching_type) VALUES (%s, %s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["shuffle"] . ""),
					$ilDB->quote($row["matching_type"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 5:
				$insertquery = sprintf("INSERT INTO qpl_question_ordering (question_fi, ordering_type) VALUES (%s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["ordering_type"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 6:
				$insertquery = sprintf("INSERT INTO qpl_question_imagemap (question_fi, image_file) VALUES (%s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["image_file"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 7:
				$insertquery = sprintf("INSERT INTO qpl_question_javaapplet (question_fi, image_file, params) VALUES (%s, %s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["image_file"] . ""),
					$ilDB->quote($row["params"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 8:
				$insertquery = sprintf("INSERT INTO qpl_question_essay (question_fi, maxNumOfChars, keywords, textgap_rating) VALUES (%s, %s, %s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["maxNumOfChars"] . ""),
					$ilDB->quote($row["keywords"] . ""),
					$ilDB->quote($row["textgap_rating"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 9:
				$insertquery = sprintf("INSERT INTO qpl_question_numeric (question_fi, maxNumOfChars) VALUES (%s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["maxNumOfChars"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 10:
				$insertquery = sprintf("INSERT INTO qpl_question_textsubset (question_fi, textgap_rating, correctanswers) VALUES (%s, %s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["textgap_rating"] . ""),
					$ilDB->quote($row["correctanswers"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
		}
	}
?>
<#678>
ALTER TABLE `qpl_questions`
  DROP `shuffle`,
  DROP `start_tag`,
  DROP `end_tag`,
  DROP `matching_type`,
  DROP `ordering_type`,
  DROP `cloze_type`,
  DROP `choice_response`,
  DROP `image_file`,
  DROP `params`,
  DROP `maxNumOfChars`,
  DROP `keywords`,
  DROP `textgap_rating`,
  DROP `correctanswers`;
<#679>
CREATE TABLE `survey_question_metric` (
  `question_fi` int(11) NOT NULL default '0',
  `subtype` enum('3','4','5') NOT NULL default '3',
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `survey_question_nominal` (
  `question_fi` int(11) NOT NULL default '0',
  `subtype` enum('1','2') NOT NULL default '1',
  `orientation` enum('0','1','2') NOT NULL default '0',
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `survey_question_ordinal` (
  `question_fi` int(11) NOT NULL default '0',
  `orientation` enum('0','1','2') NOT NULL default '0',
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `survey_question_text` (
  `question_fi` int(11) NOT NULL default '0',
  `maxchars` int(11) default NULL,
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;
<#680>
<?php
	$query = "SELECT * FROM survey_question";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		switch ($row["questiontype_fi"])
		{
			case 1:
				$subtype = $row["subtype"];
				if ($subtype < 1) $subtype = 1;
				$orientation = $row["orientation"];
				if ($orientation < 1) $orientation = 1;
				$insertquery = sprintf("INSERT INTO survey_question_nominal (question_fi, subtype, orientation) VALUES (%s, %s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["subtype"] . ""),
					$ilDB->quote($orientation . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 2:
				$orientation = $row["orientation"];
				if (!strlen($orientation)) $orientation = 0;
				$insertquery = sprintf("INSERT INTO survey_question_ordinal (question_fi, orientation) VALUES (%s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($orientation . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 3:
				$subtype = $row["subtype"];
				if ($subtype < 3) $subtype = 3;
				$insertquery = sprintf("INSERT INTO survey_question_metric (question_fi, subtype) VALUES (%s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($subtype . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 4:
				$insertquery = sprintf("INSERT INTO survey_question_text (question_fi, maxchars) VALUES (%s, %s)",
					$ilDB->quote($row["question_id"] . ""),
					$ilDB->quote($row["maxchars"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
		}
	}
?>
<#681>
ALTER TABLE `survey_question`
  DROP `subtype`,
  DROP `orientation`,
  DROP `maxchars`;
<#682>
DROP TABLE `qpl_answer_enhanced`;
<#683>
CREATE TABLE `qpl_answer_cloze` (
  `answer_id` int(10) unsigned NOT NULL auto_increment,
  `question_fi` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `shuffle` enum('0','1') NOT NULL default '1',
  `answertext` text NOT NULL,
  `points` double NOT NULL default '0',
  `aorder` int(10) unsigned NOT NULL default '0',
  `correctness` enum('0','1') NOT NULL default '0',
  `gap_id` int(10) unsigned NOT NULL default '0',
  `cloze_type` enum('0','1') default NULL,
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`answer_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;
CREATE TABLE `qpl_answer_imagemap` (
  `answer_id` int(10) unsigned NOT NULL auto_increment,
  `question_fi` int(10) unsigned NOT NULL default '0',
  `answertext` text NOT NULL,
  `points` double NOT NULL default '0',
  `aorder` int(10) unsigned NOT NULL default '0',
  `correctness` enum('0','1') NOT NULL default '0',
  `coords` text,
  `area` varchar(20) default NULL,
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`answer_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;
CREATE TABLE `qpl_answer_matching` (
  `answer_id` int(10) unsigned NOT NULL auto_increment,
  `question_fi` int(10) unsigned NOT NULL default '0',
  `answertext` text NOT NULL,
  `points` double NOT NULL default '0',
  `aorder` int(10) unsigned NOT NULL default '0',
  `matchingtext` text,
  `matching_order` int(10) unsigned default NULL,
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`answer_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;
CREATE TABLE `qpl_answer_multiplechoice` (
  `answer_id` int(10) unsigned NOT NULL auto_increment,
  `question_fi` int(10) unsigned NOT NULL default '0',
  `answertext` text NOT NULL,
  `imagefile` text,
  `points` double NOT NULL default '0',
  `aorder` int(10) unsigned NOT NULL default '0',
  `correctness` enum('0','1') NOT NULL default '0',
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`answer_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;
CREATE TABLE `qpl_answer_ordering` (
  `answer_id` int(10) unsigned NOT NULL auto_increment,
  `question_fi` int(10) unsigned NOT NULL default '0',
  `answertext` text NOT NULL,
  `points` double NOT NULL default '0',
  `aorder` int(10) unsigned NOT NULL default '0',
  `solution_order` int(10) unsigned NOT NULL default '0',
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`answer_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;
CREATE TABLE `qpl_answer_textsubset` (
  `answer_id` int(10) unsigned NOT NULL auto_increment,
  `question_fi` int(10) unsigned NOT NULL default '0',
  `answertext` text NOT NULL,
  `points` double NOT NULL default '0',
  `aorder` int(10) unsigned NOT NULL default '0',
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`answer_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;
<#684>
<?php
	$query = "SELECT qpl_answers.*, qpl_questions.question_type_fi FROM qpl_answers, qpl_questions WHERE qpl_answers.question_fi = qpl_questions.question_id";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		switch ($row["question_type_fi"])
		{
			case 1:
			case 2:
				$insertquery = sprintf("INSERT INTO qpl_answer_multiplechoice (question_fi, answertext, points, aorder, correctness) VALUES (%s, %s, %s, %s, %s)",
					$ilDB->quote($row["question_fi"] . ""),
					$ilDB->quote($row["answertext"] . ""),
					$ilDB->quote($row["points"] . ""),
					$ilDB->quote($row["aorder"] . ""),
					$ilDB->quote($row["correctness"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 3:
				$insertquery = sprintf("INSERT INTO qpl_answer_cloze (question_fi, name, shuffle, answertext, points, aorder, correctness, gap_id, cloze_type) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
					$ilDB->quote($row["question_fi"] . ""),
					$ilDB->quote($row["name"] . ""),
					$ilDB->quote($row["shuffle"] . ""),
					$ilDB->quote($row["answertext"] . ""),
					$ilDB->quote($row["points"] . ""),
					$ilDB->quote($row["aorder"] . ""),
					$ilDB->quote($row["correctness"] . ""),
					$ilDB->quote($row["gap_id"] . ""),
					$ilDB->quote($row["cloze_type"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 4:
				$insertquery = sprintf("INSERT INTO qpl_answer_matching (question_fi, answertext, points, aorder, matchingtext, matching_order) VALUES (%s, %s, %s, %s, %s, %s)",
					$ilDB->quote($row["question_fi"] . ""),
					$ilDB->quote($row["answertext"] . ""),
					$ilDB->quote($row["points"] . ""),
					$ilDB->quote($row["aorder"] . ""),
					$ilDB->quote($row["matchingtext"] . ""),
					$ilDB->quote($row["matching_order"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 5:
				$insertquery = sprintf("INSERT INTO qpl_answer_ordering (question_fi, answertext, points, aorder, solution_order) VALUES (%s, %s, %s, %s, %s)",
					$ilDB->quote($row["question_fi"] . ""),
					$ilDB->quote($row["answertext"] . ""),
					$ilDB->quote($row["points"] . ""),
					$ilDB->quote($row["aorder"] . ""),
					$ilDB->quote($row["solution_order"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 6:
				$insertquery = sprintf("INSERT INTO qpl_answer_imagemap (question_fi, answertext, points, aorder, correctness, coords, area) VALUES (%s, %s, %s, %s, %s, %s, %s)",
					$ilDB->quote($row["question_fi"] . ""),
					$ilDB->quote($row["answertext"] . ""),
					$ilDB->quote($row["points"] . ""),
					$ilDB->quote($row["aorder"] . ""),
					$ilDB->quote($row["correctness"] . ""),
					$ilDB->quote($row["coords"] . ""),
					$ilDB->quote($row["area"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
			case 7:
				break;
			case 8:
				break;
			case 9:
				break;
			case 10:
				$insertquery = sprintf("INSERT INTO qpl_answer_textsubset (question_fi, answertext, points, aorder) VALUES (%s, %s, %s, %s)",
					$ilDB->quote($row["question_fi"] . ""),
					$ilDB->quote($row["answertext"] . ""),
					$ilDB->quote($row["points"] . ""),
					$ilDB->quote($row["aorder"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				break;
		}
	}
?>
<#685>
DROP TABLE qpl_answers;
DROP TABLE qpl_answerblock;
<#686>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#687>
ALTER TABLE survey_survey CHANGE anonymize anonymize ENUM('0','1','2') NOT NULL DEFAULT '0';

<#688>
ALTER TABLE `tst_tests` ADD `shuffle_questions` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `ending_time` ;
<#689>
ALTER TABLE `tst_tests` ADD `show_solution_details` ENUM( '0', '1' ) NOT NULL DEFAULT '1';

<#690>
ALTER TABLE `survey_question_nominal` CHANGE `orientation` `orientation` ENUM( '0', '1', '2' ) NOT NULL DEFAULT '0';

<#691>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#692>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#693>
ALTER TABLE `survey_anonymous` ADD `user_key` VARCHAR( 40 ) NULL AFTER `survey_fi` ;
<#694>
ALTER TABLE `exc_returned` CHANGE `TIMESTAMP` `timestamp` timestamp(14);
<#695>
<?php

  // Update registration settings
$query = "SELECT value FROM settings WHERE keyword='auto_registration'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->value)
	{
		$reg_mode = 2;
	}
}


$query = "SELECT value FROM settings WHERE keyword='enable_registration'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if(!$row->value)
	{
		$reg_mode = 1;
	}
}
if(!$reg_mode)
{
	$reg_mode = 3;
}
$query = "INSERT INTO settings SET keyword = 'new_registration_type',value = '".$reg_mode."'";
$ilDB->query($query);
?>
<#696>
CREATE TABLE `reg_email_role_assignments` (
`assignment_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`domain` VARCHAR( 128 ) NOT NULL ,
`role` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `assignment_id` )
) TYPE = MYISAM ;
<#697>
<?php
$query = "INSERT INTO reg_email_role_assignments SET domain = '', role = ''";
$ilDB->query($query);
?>
<#698>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#699>
ALTER TABLE `exc_members` ADD `solved_time` timestamp(14) DEFAULT '00000000000000' AFTER solved;
<#700>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#701>
ALTER TABLE `qpl_answer_multiplechoice` ADD `points_unchecked` DOUBLE NOT NULL DEFAULT '0' AFTER `points`;
<#702>
<?php
	$query = "SELECT qpl_answer_multiplechoice.*, qpl_question_multiplechoice.choice_response FROM qpl_answer_multiplechoice, qpl_question_multiplechoice WHERE qpl_answer_multiplechoice.question_fi = qpl_question_multiplechoice.question_fi";
	$result = $ilDB->query($query);
	if ($result->numRows())
	{
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// only multiple response questions
			if ($row["choice_response"] == 1)
			{
				if ($row["correctness"] == 0)
				{
					$query = sprintf("UPDATE qpl_answer_multiplechoice SET points = %s, points_unchecked = %s WHERE answer_id = %s",
						$ilDB->quote("0"),
						$ilDB->quote($row["points"]),
						$ilDB->quote($row["answer_id"])
					);
				}
				else
				{
					$query = sprintf("UPDATE qpl_answer_multiplechoice SET points = %s, points_unchecked = %s WHERE answer_id = %s",
						$ilDB->quote($row["points"]),
						$ilDB->quote("0"),
						$ilDB->quote($row["answer_id"])
					);
				}
				$updateres = $ilDB->query($query);
			}
		}
	}
?>
<#703>
ALTER TABLE `qpl_answer_multiplechoice` DROP `correctness`;
<#704>
ALTER TABLE `tst_tests` ADD `score_cutting` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `mc_scoring`;
<#705>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#706>
CREATE TABLE `qpl_question_singlechoice` (
  `question_fi` int(11) NOT NULL default '0',
  `shuffle` enum('0','1') NOT NULL default '1',
  PRIMARY KEY  (`question_fi`)
) TYPE=MyISAM;

CREATE TABLE `qpl_answer_singlechoice` (
  `answer_id` int(10) unsigned NOT NULL auto_increment,
  `question_fi` int(10) unsigned NOT NULL default '0',
  `answertext` text NOT NULL,
  `imagefile` text,
  `points` double NOT NULL default '0',
  `aorder` int(10) unsigned NOT NULL default '0',
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`answer_id`),
  KEY `question_fi` (`question_fi`)
) TYPE=MyISAM;
<#707>
<?php
	// move multiple choice sr -> singlechoice
	$query = "SELECT qpl_question_multiplechoice.*, qpl_questions.question_type_fi FROM qpl_question_multiplechoice, qpl_questions WHERE qpl_question_multiplechoice.question_fi = qpl_questions.question_id";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		switch ($row["question_type_fi"])
		{
			case 1:
				// single response
				$insertquery = sprintf("INSERT INTO qpl_question_singlechoice (question_fi, shuffle) VALUES (%s, %s)",
					$ilDB->quote($row["question_fi"] . ""),
					$ilDB->quote($row["shuffle"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				$deletequery = sprintf("DELETE FROM qpl_question_multiplechoice WHERE question_fi = %s",
					$ilDB->quote($row["question_fi"] . "")
				);
				$deleteresult = $ilDB->query($deletequery);
				break;
		}
	}
	// move multiple choice sr answers -> singlechoice answers
	$query = "SELECT qpl_answer_multiplechoice.*, qpl_questions.question_type_fi FROM qpl_answer_multiplechoice, qpl_questions WHERE qpl_answer_multiplechoice.question_fi = qpl_questions.question_id";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		switch ($row["question_type_fi"])
		{
			case 1:
				// single response
				$insertquery = sprintf("INSERT INTO qpl_answer_singlechoice (question_fi, answertext, imagefile, points, aorder) VALUES (%s, %s, %s, %s, %s)",
					$ilDB->quote($row["question_fi"] . ""),
					$ilDB->quote($row["answertext"] . ""),
					$ilDB->quote($row["imagefile"] . ""),
					$ilDB->quote($row["points"] . ""),
					$ilDB->quote($row["aorder"] . "")
				);
				$insertresult = $ilDB->query($insertquery);
				$deletequery = sprintf("DELETE FROM qpl_answer_multiplechoice WHERE answer_id = %s",
					$ilDB->quote($row["answer_id"] . "")
				);
				$deleteresult = $ilDB->query($deletequery);
				break;
		}
	}
?>
<#708>
ALTER TABLE qpl_question_multiplechoice DROP choice_response;

<#709>
ALTER TABLE `tst_tests` ADD `password` VARCHAR( 20 ) NULL AFTER `pass_scoring`;

<#710>
ALTER TABLE `crs_items` ADD `changeable` TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER `activation_end` ,
ADD `visible` TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER `changeable`;

<#711>
ALTER TABLE `crs_items` ADD `timing_min` INT DEFAULT '0' NOT NULL AFTER `obj_id` ,
ADD `timing_max` INT DEFAULT '0' NOT NULL AFTER `timing_min`;

<#712>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#713>
ALTER TABLE `settings` ADD `module` VARCHAR( 50 ) NOT NULL DEFAULT 'common' FIRST ;
ALTER TABLE `settings` DROP PRIMARY KEY ;
ALTER TABLE `settings` ADD PRIMARY KEY ( `module` , `keyword` ) ;
ALTER TABLE `settings` CHANGE `value` `value` TEXT NOT NULL; 
<#714>
UPDATE `settings` SET `module` = 'assessment' WHERE `module` = 'common' AND `keyword` LIKE 'assessment_%';
<#715>
ALTER TABLE `tst_tests` ADD `show_solution_printview` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `show_solution_details`;

<#716>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#717>
ALTER TABLE content_object ADD column header_page int NOT NULL DEFAULT 0;
ALTER TABLE content_object ADD column footer_page int NOT NULL DEFAULT 0;
<#718>
DROP TABLE `qpl_question_type`;
CREATE TABLE `qpl_question_type` (
  `question_type_id` int(3) unsigned NOT NULL auto_increment,
  `type_tag` char(35) NOT NULL default '',
  PRIMARY KEY  (`question_type_id`)
);

INSERT INTO `qpl_question_type` (`question_type_id`, `type_tag`) VALUES (1, 'assSingleChoice'),
(2, 'assMultipleChoice'),
(3, 'assClozeTest'),
(4, 'assMatchingQuestion'),
(5, 'assOrderingQuestion'),
(6, 'assImagemapQuestion'),
(7, 'assJavaApplet'),
(8, 'assTextQuestion'),
(9, 'assNumeric'),
(10, 'assTextSubset');
<#719>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#720>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#721>
ALTER TABLE `tst_tests` ADD `show_summary` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `show_solution_printview` ;
<#722>
ALTER TABLE `tst_active` ADD `anonymous_id` VARCHAR( 5 ) NULL AFTER `user_fi` ;
ALTER TABLE `tst_active` ADD INDEX ( `anonymous_id` ) ;
ALTER TABLE `tst_active_qst_sol_settings` ADD `anonymous_id` VARCHAR( 5 ) NULL AFTER `user_fi` ;
ALTER TABLE `tst_active_qst_sol_settings` ADD INDEX ( `anonymous_id` ) ;
ALTER TABLE `tst_solutions` ADD `anonymous_id` VARCHAR( 5 ) NULL AFTER `user_fi` ;
ALTER TABLE `tst_solutions` ADD INDEX ( `anonymous_id` ) ;
ALTER TABLE `tst_test_random_question` ADD `anonymous_id` VARCHAR( 5 ) NULL AFTER `user_fi` ;
ALTER TABLE `tst_test_random_question` ADD INDEX ( `anonymous_id` ) ;
ALTER TABLE `tst_test_result` ADD `anonymous_id` VARCHAR( 5 ) NULL AFTER `user_fi` ;
ALTER TABLE `tst_test_result` ADD INDEX ( `anonymous_id` ) ;
<#723>
ALTER TABLE `tst_active_qst_sol_settings` DROP `anonymous_id` ;
ALTER TABLE `tst_solutions` DROP `anonymous_id` ;
ALTER TABLE `tst_test_random_question` DROP `anonymous_id` ;
ALTER TABLE `tst_test_result` DROP `anonymous_id` ;
<#724>
ALTER TABLE `tst_active_qst_sol_settings` ADD `active_fi` INT NOT NULL AFTER `user_fi` ;
<#725>
<?php
$query = "SELECT tst_active_qst_sol_settings.*, tst_active.active_id FROM tst_active_qst_sol_settings, tst_active WHERE tst_active_qst_sol_settings.test_fi = tst_active.test_fi AND tst_active_qst_sol_settings.user_fi = tst_active.user_fi";
$result = $ilDB->query($query);
if ($result->numRows())
{
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$update = sprintf("UPDATE tst_active_qst_sol_settings SET active_fi = %s WHERE test_fi = %s AND user_fi = %s",
			$ilDB->quote($row["active_id"] . ""),
			$ilDB->quote($row["test_fi"] . ""),
			$ilDB->quote($row["user_fi"] . "")
		);
		$updateresult = $ilDB->query($update);
	}
}
?>
<#726>
ALTER TABLE `tst_active_qst_sol_settings` DROP PRIMARY KEY;
ALTER TABLE `tst_active_qst_sol_settings` ADD PRIMARY KEY ( `active_fi` , `question_fi` );
ALTER TABLE `tst_active_qst_sol_settings` DROP `test_fi`;
ALTER TABLE `tst_active_qst_sol_settings` DROP `user_fi`;
<#727>
ALTER TABLE `tst_solutions` DROP INDEX `solution_id_2`;
ALTER TABLE `tst_solutions` DROP INDEX `solution_id`;
ALTER TABLE `tst_solutions` ADD `active_fi` INT NOT NULL AFTER `user_fi` ;
<#728>
<?php
global $ilLog;
$query = "SELECT tst_solutions.*, tst_active.active_id FROM tst_solutions, tst_active WHERE tst_solutions.test_fi = tst_active.test_fi AND tst_solutions.user_fi = tst_active.user_fi";
$result = $ilDB->query($query);
if ($result->numRows())
{
	if(function_exists('memory_get_usage'))
  {
		$memory_usage = " Memory usage: ".memory_get_usage();
  }
	$ilLog->write("-- MetaData (Migration type '".$row_pg->type."'): Processing obj number: ".$row_pg->obj_id.$memory_usage);
	$counter = 0;
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		if(function_exists('memory_get_usage'))
		{
			$memory_usage = " Memory usage: ".memory_get_usage();
		}
		if(!(++$counter % 100))
		{
			$ilLog->write("test_result number: $counter".$memory_usage);
		}
		$update = sprintf("UPDATE tst_solutions SET active_fi = %s WHERE test_fi = %s AND user_fi = %s",
			$ilDB->quote($row["active_id"] . ""),
			$ilDB->quote($row["test_fi"] . ""),
			$ilDB->quote($row["user_fi"] . "")
		);
		$updateresult = $ilDB->query($update);
	}
}
?>
<#729>
ALTER TABLE `tst_solutions` DROP `test_fi`;
ALTER TABLE `tst_solutions` DROP `user_fi`;
ALTER TABLE `tst_solutions` ADD INDEX ( `active_fi` ) ;
<#730>
ALTER TABLE `tst_test_random_question` ADD `active_fi` INT NOT NULL AFTER `user_fi` ;
<#731>
<?php
$query = "SELECT tst_test_random_question.*, tst_active.active_id FROM tst_test_random_question, tst_active WHERE tst_test_random_question.test_fi = tst_active.test_fi AND tst_test_random_question.user_fi = tst_active.user_fi";
$result = $ilDB->query($query);
if ($result->numRows())
{
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$update = sprintf("UPDATE tst_test_random_question SET active_fi = %s WHERE test_fi = %s AND user_fi = %s",
			$ilDB->quote($row["active_id"] . ""),
			$ilDB->quote($row["test_fi"] . ""),
			$ilDB->quote($row["user_fi"] . "")
		);
		$updateresult = $ilDB->query($update);
	}
}
?>
<#732>
ALTER TABLE `tst_test_random_question` DROP `test_fi`;
ALTER TABLE `tst_test_random_question` DROP `user_fi`;
ALTER TABLE `tst_test_random_question` ADD INDEX ( `active_fi` ) ;
<#733>
ALTER TABLE `tst_test_result` ADD `active_fi` INT NOT NULL AFTER `user_fi` ;
<#734>
<?php
$query = "SELECT tst_test_result.*, tst_active.active_id FROM tst_test_result, tst_active WHERE tst_test_result.test_fi = tst_active.test_fi AND tst_test_result.user_fi = tst_active.user_fi";
$result = $ilDB->query($query);
if ($result->numRows())
{
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$update = sprintf("UPDATE tst_test_result SET active_fi = %s, TIMESTAMP = %s WHERE test_result_id = %s",
			$ilDB->quote($row["active_id"] . ""),
			$ilDB->quote($row["TIMESTAMP"] . ""),
			$ilDB->quote($row["test_result_id"] . "")
		);
		$updateresult = $ilDB->query($update);
	}
}
?>
<#735>
ALTER TABLE `tst_test_result` DROP INDEX `user_fi`;
ALTER TABLE `tst_test_result` DROP INDEX `question_fi`;
ALTER TABLE `tst_test_result` DROP `test_fi`;
ALTER TABLE `tst_test_result` DROP `user_fi`;
ALTER TABLE `tst_test_result` ADD UNIQUE (`active_fi` ,`question_fi`, `pass`);
<#736>
ALTER TABLE `tst_tests` ADD `allowedUsers` INT NULL AFTER `password` ;
ALTER TABLE `tst_tests` ADD `allowedUsersTimeGap` INT NULL AFTER `allowedUsers` ;
<#737>
ALTER TABLE `tst_active` DROP INDEX `active_id_2`;
ALTER TABLE `tst_active` DROP INDEX `active_id`;
ALTER TABLE `tst_mark` DROP INDEX `mark_id_2`;
ALTER TABLE `tst_mark` DROP INDEX `mark_id`;
ALTER TABLE `tst_tests` DROP INDEX `test_id_2`;
ALTER TABLE `tst_tests` DROP INDEX `test_id`;
ALTER TABLE `tst_test_type` DROP INDEX `test_type_id_2`;
ALTER TABLE `tst_test_type` DROP INDEX `test_type_id`;
<#738>
ALTER TABLE `ass_log` ADD `test_only` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `original_fi` ;
ALTER TABLE `ass_log` ADD `ref_id` INT NULL AFTER `original_fi` ;
<#739>
REPLACE INTO settings (module, keyword, value) VALUES ('common', 'enable_trash', 1);
<#740>
<?php
// register new object type 'adve' for Advanced editing settings in the administration
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('typ', 'adve', 'Advanced editing object', -1, now(), now())";
$this->db->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry
$query = "INSERT INTO object_data (type, title, description, owner, create_date, last_update) ".
		"VALUES ('adve', '__AdvancedEditing', 'Advanced Editing', -1, now(), now())";
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
$query = "INSERT INTO settings (keyword,value) VALUES('sys_advanced_editing_id','".$row->id."')";
$res = $this->db->query($query);

// retrieve assessment folder definition from object_data
$query = "SELECT obj_id FROM object_data WHERE type = 'typ' ".
	" AND title = 'adve'";
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
<#741>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#742>
ALTER TABLE usr_data ADD COLUMN `profile_incomplete` int(2) default '0';
<#743>
ALTER TABLE `usr_data` MODIFY `auth_mode` ENUM( 'default','local', 'ldap', 'radius', 'shibboleth','script','cas','soap') DEFAULT 'default' NOT NULL;
ALTER TABLE `role_data` MODIFY `auth_mode` ENUM( 'default', 'local', 'ldap', 'radius', 'shibboleth', 'script','cas','soap') DEFAULT 'default' NOT NULL;
ALTER TABLE `usr_data` ADD COLUMN ext_account CHAR(50);

<#744>
CREATE TABLE xhtml_page(
  id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  content MEDIUMTEXT
);
<#745>
ALTER TABLE `crs_items` ADD `suggestion_start` INT NOT NULL AFTER `activation_end` ,
ADD `suggestion_end` INT NOT NULL AFTER `suggestion_start`;

<#746>
DROP TABLE IF EXISTS event;
CREATE TABLE event (
  event_id int(11) NOT NULL auto_increment,
  obj_id int(11) NOT NULL default '0',
  title varchar(70) NOT NULL default '',
  description text NOT NULL,
  location text NOT NULL,
  tutor_firstname varchar(127) NOT NULL default '',
  tutor_lastname varchar(127) NOT NULL default '',
  tutor_title varchar(16) NOT NULL default '',
  tutor_email varchar(127)NOT NULL default '',
  tutor_phone varchar(127) NOT NULL default '',
  details text NOT NULL,
  PRIMARY KEY  (event_id)
) TYPE=MyISAM;

<#747>
DROP TABLE IF EXISTS `event_appointment`;
CREATE TABLE `event_appointment` (
  `appointment_id` int(11) NOT NULL auto_increment,
  `event_id` int(11) NOT NULL default '0',
  `starting_time` int(11) NOT NULL default '0',
  `ending_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`appointment_id`)
) TYPE=MyISAM;

<#748>
DROP TABLE IF EXISTS `event_file`;
CREATE TABLE `event_file` (
  `file_id` int(11) NOT NULL auto_increment,
  `event_id` int(11) NOT NULL default '0',
  `file_name` char(64) NOT NULL default '',
  `file_type` char(64) NOT NULL default '',
  `file_size` int(11) NOT NULL default '0',
  PRIMARY KEY  (`file_id`)
) TYPE=MyISAM;

<#749>

DROP TABLE IF EXISTS `event_items`;
CREATE TABLE `event_items` (
  `event_id` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`event_id`,`item_id`),
  KEY `event_id` (`event_id`)
	) TYPE=MyISAM ;

<#750>
ALTER TABLE `crs_settings` ADD `important` TEXT NOT NULL ;

<#751>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#752>
ALTER TABLE `qpl_question_cloze` ADD `identical_scoring` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `textgap_rating` ;
<#753>
ALTER TABLE `qpl_answer_cloze` CHANGE `cloze_type` `cloze_type` ENUM( '0', '1', '2' ) NULL DEFAULT NULL;

<#754>
DROP TABLE IF EXISTS `crs_file`;
CREATE TABLE `crs_file` (
  `file_id` int(11) NOT NULL auto_increment,
  `course_id` int(11) NOT NULL default '0',
  `file_name` char(64) NOT NULL default '',
  `file_type` char(64) NOT NULL default '',
  `file_size` int(11) NOT NULL default '0',
  PRIMARY KEY  (`file_id`)
) TYPE=MyISAM;

<#755>
ALTER TABLE `crs_items` CHANGE `activation_unlimited` `timing_type` TINYINT( 2 ) NULL DEFAULT NULL;

<#756>
ALTER TABLE `crs_items` CHANGE `activation_start` `timing_start` INT( 8 ) NULL DEFAULT NULL;

<#757>
ALTER TABLE `crs_items` CHANGE `activation_end` `timing_end` INT( 8 ) NULL DEFAULT NULL ;

<#758>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#759>
ALTER TABLE `crs_items`
  DROP `timing_min`,
  DROP `timing_max`;
<#760>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#761>
DROP TABLE IF EXISTS `crs_objective_status`;
CREATE TABLE `crs_objective_status` (
  `objective_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`objective_id`,`user_id`)
) TYPE=MyISAM;
<#762>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#763>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#764>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#765>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#766>
ALTER TABLE `event` ADD `registration` TINYINT( 1 ) NOT NULL ,
ADD `participation` TINYINT( 1 ) NOT NULL ;
<#767>
CREATE TABLE `event_participants` (
`event_id` INT( 11 ) NOT NULL ,
`usr_id` INT( 11 ) NOT NULL ,
`registered` TINYINT( 1 ) NOT NULL ,
`participated` TINYINT( 1 ) NOT NULL ,
PRIMARY KEY ( `event_id` , `usr_id` )
) TYPE = MYISAM ;

<#768>
ALTER TABLE `lm_data` ADD `active` ENUM( 'y', 'n' ) DEFAULT 'y';

<#769>
ALTER TABLE `event_participants` ADD `mark` TEXT NOT NULL ,
ADD `comment` TEXT NOT NULL ;

<#770>
CREATE TABLE `ut_lp_event_collections` (
`obj_id` int( 11 ) NOT NULL default '0',
`item_id` int( 11 ) NOT NULL default '0',
KEY `obj_id` ( `obj_id` , `item_id` )
) TYPE = MYISAM;
<#771>
ALTER TABLE `crs_items` ADD `earliest_start` INT( 11 ) NOT NULL AFTER `changeable` ,
ADD `latest_end` INT( 11 ) NOT NULL AFTER `earliest_start`;
<#772>
CREATE TABLE `usr_new_account_mail` (
`lang` CHAR(5) NOT NULL PRIMARY KEY,
`subject` VARCHAR(200) NULL default '',
`body` MEDIUMTEXT NULL default ''
) TYPE = MYISAM;
<#773>
ALTER TABLE `ilinc_data` ADD `akclassvalue1` VARCHAR( 40 ) NULL ;
ALTER TABLE `ilinc_data` ADD `akclassvalue2` VARCHAR( 40 ) NULL ;
REPLACE INTO settings (module, keyword, value) VALUES ('common', 'ilinc_akclassvalues_required', 1);

<#774>
ALTER TABLE `payment_settings` ADD `paypal` TEXT NOT NULL ;

<#775>
CREATE TABLE `crs_timings_usr_accept` (
  `crs_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `accept` tinyint(1) NOT NULL default '0',
  `remark` text  NOT NULL,
  `visible` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`crs_id`,`usr_id`)
) TYPE=MyISAM;
<#776>
CREATE TABLE `crs_timings_planed` (
`item_id` INT( 11 ) NOT NULL ,
`usr_id` INT( 11 ) NOT NULL ,
`planed_start` INT( 11 ) NOT NULL ,
`planed_end` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `item_id` , `usr_id` )
) TYPE = MYISAM ;

<#777>
<?php
$tree = new ilTree(ROOT_FOLDER_ID);
$query = "SELECT ut.obj_id AS obj_id,type,item_id FROM ut_lp_collections AS ut INNER JOIN object_data AS od ON od.obj_id = ut.obj_id";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->type != 'crs' and
	   $row->type != 'fold' and
	   $row->type != 'grp')
	{
		continue;
	}
	// get container ref_id
	$query = "SELECT * FROM object_reference WHERE obj_id = '".$row->obj_id."'";
	$ref_res = $this->db->query($query);
	while($ref_row = $ref_res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$container_ref_id = $ref_row->ref_id;
	}
	// get item ref ids
	$query = "SELECT * FROM object_reference WHERE obj_id = '".$row->item_id."'";
	$item_res = $this->db->query($query);
	while($item_row = $item_res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		// is child node
		if($tree->isGrandChild($container_ref_id,$item_row->ref_id))
		{
			$query = "UPDATE ut_lp_collections ".
				"SET item_id = '".$item_row->ref_id."' ".
				"WHERE obj_id = '".$row->obj_id."' ".
				"AND item_id = '".$row->item_id."'";
			$this->db->query($query);
			break;
		}
	}
}
?>

<#778>

ALTER TABLE `usr_new_account_mail` ADD COLUMN `sal_f` VARCHAR(200) NOT NULL DEFAULT '';
ALTER TABLE `usr_new_account_mail` ADD COLUMN `sal_m` VARCHAR(200) NOT NULL DEFAULT '';
ALTER TABLE `usr_new_account_mail` ADD COLUMN `sal_g` VARCHAR(200) NOT NULL DEFAULT '';

<#779>
ALTER TABLE `crs_settings` ADD `activation_type` TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER `contact_consultation`;

<#780>
<?php

$query = "SELECT * FROM crs_settings ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->activation_unlimited)
	{
		$type = 1;
	}
	elseif($row->activation_offline)
	{
		$type = 0;
	}
	else
	{
		$type = 2;
	}
	$query = "UPDATE crs_settings ".
		"SET activation_type = '".$type."' ".
		"WHERE obj_id = '".$row->obj_id."'";
	$ilDB->query($query);
}
?>
<#781>
ALTER TABLE `crs_settings`
  DROP `activation_unlimited`,
  DROP `activation_offline`;

<#782>
ALTER TABLE `crs_settings` ADD `subscription_limitation_type` TINYINT( 1 ) NOT NULL AFTER `activation_end` ;

<#783>
<?php

$query = "SELECT * FROM crs_settings ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->subscription_unlimited)
	{
		$type = 1;
	}
	elseif($row->subscription_type == 1)
	{
		$type = 0;
	}
	else
	{
		$type = 2;
	}
	$query = "UPDATE crs_settings ".
		"SET subscription_limitation_type = '".$type."' ".
		"WHERE obj_id = '".$row->obj_id."'";
	$ilDB->query($query);
}
?>
<#784>
ALTER TABLE `crs_settings`
DROP `subscription_unlimited`;

<#785>
ALTER TABLE `crs_settings` ADD `view_mode` TINYINT( 1 ) NOT NULL AFTER `subscription_notify`;

<#786>
<?php

$query = "SELECT * FROM crs_settings ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	if($row->archive_type != 1)
	{
		$type = 3;
	}
	elseif($row->objective_view)
	{
		$type = 1;
	}
	else
	{
		$type = 0;
	}
	$query = "UPDATE crs_settings ".
		"SET view_mode = '".$type."' ".
		"WHERE obj_id = '".$row->obj_id."'";
	$ilDB->query($query);
}
?>
<#787>
ALTER TABLE `crs_settings`
  DROP `objective_view`;
<#788>
ALTER TABLE `survey_survey` ADD `outro` TEXT NULL AFTER `introduction` ;
UPDATE `survey_survey` SET `outro` = 'survey_finished';

<#789>
ALTER TABLE `event` DROP `tutor_firstname` ,
DROP `tutor_lastname` ,
DROP `tutor_title` ;

<#790>
ALTER TABLE `event` ADD `tutor_name` TEXT NOT NULL AFTER `location`;

<#791>
REPLACE INTO settings (keyword, value) VALUES ('shib_hos_type', 'external_wayf');
REPLACE INTO settings (keyword, value) VALUES ('shib_federation_name', 'Shibboleth');
REPLACE INTO settings (keyword, value) VALUES ('shib_idp_list', '');
<#792>
ALTER TABLE exc_members ADD sent_time TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
<#793>
CREATE TABLE exc_usr_tutor (
obj_id INT NOT NULL DEFAULT 0,
usr_id INT NOT NULL DEFAULT 0,
tutor_id INT NOT NULL DEFAULT 0,
download_time TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY (obj_id, usr_id, tutor_id));
<#794>
ALTER TABLE `tst_tests` ADD `show_question_titles` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `show_summary` ;

<#795>
CREATE TABLE IF NOT EXISTS `crs_objective_status_pretest` (
  `objective_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`objective_id`,`user_id`)
) Type=MyISAM;

<#796>
<?php

$query = "SELECT * FROM crs_objective_status WHERE status = 0";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO crs_objective_status_pretest ".
		"SET objective_id = '".$row->objective_id."', ".
		"user_id = '".$row->user_id."'";
	$ilDB->query($query);
}
?>

<#797>
<?php
$query = "DELETE FROM crs_objective_status ".
	"WHERE status = '0'";
$ilDB->query($query);
?>
<#798>
ALTER TABLE exc_members ADD feedback_time TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE exc_members ADD feedback TINYINT(1);
UPDATE exc_members SET feedback=1 WHERE sent=1;
<#799>
ALTER TABLE exc_members MODIFY feedback TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE exc_members MODIFY returned TINYINT(1) NOT NULL DEFAULT 0;
<#800>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#801>
ALTER TABLE sc_resource ADD INDEX import_id (import_id);
<#802>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#803>
ALTER TABLE `event_appointment` ADD `fulltime` TINYINT( 1 ) NOT NULL;

<#804>
ALTER TABLE `ut_lp_marks` CHANGE `comment` `comment` TEXT NOT NULL;


<#805>
<?php

$query = "SELECT * FROM rbac_operations WHERE operation = 'edit_learning_progress'";
												   $res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ops_id = $row->ops_id;
}

// Get type ids of 'exc', 'grp', 'fold'
$query = "SELECT obj_id FROM object_data WHERE title IN ('exc','grp','fold') ".
	"AND type = 'typ'";

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$type_ids[] = $row->obj_id;
}
// ASSIGN new operation to object types
foreach($type_ids as $typ_id)
{
	$query = "INSERT INTO rbac_ta SET typ_id = '".$typ_id."', ".
		"ops_id = '".$ops_id."'";

	$ilDB->query($query);
}

// get template il_crs_admin Author and Local Administrator
$query = "SELECT obj_id FROM object_data WHERE title IN ('il_grp_admin','il_crs_admin','Author','Local Administrator') ".
	"AND type = 'rolt'";

$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$rolt_ids[] = $row->obj_id;
}

// ASSIGN new operation to role templates
foreach($rolt_ids as $rolt_id)
{
	$query = "INSERT INTO rbac_templates SET rol_id = '".$rolt_id."', ".
		"type = 'grp', ".
		"ops_id = '".$ops_id."', ".
		"parent = '".ROLE_FOLDER_ID."'";
	$ilDB->query($query);

	$query = "INSERT INTO rbac_templates SET rol_id = '".$rolt_id."', ".
		"type = 'exc', ".
		"ops_id = '".$ops_id."', ".
		"parent = '".ROLE_FOLDER_ID."'";
	$ilDB->query($query);

	$query = "INSERT INTO rbac_templates SET rol_id = '".$rolt_id."', ".
		"type = 'fold', ".
		"ops_id = '".$ops_id."', ".
		"parent = '".ROLE_FOLDER_ID."'";
	$ilDB->query($query);
}
?>
<#806>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#807>
ALTER TABLE exc_members ADD status ENUM('notgraded','failed','passed') NOT NULL DEFAULT 'notgraded';
UPDATE exc_members SET status = 'passed' WHERE solved = 1;
ALTER TABLE exc_members CHANGE solved_time status_time TIMESTAMP DEFAULT '0000-00-00 00:00:00';
<#808>
ALTER TABLE frm_data ADD INDEX top_frm_fk (top_frm_fk);
<#809>
ALTER TABLE mail ADD INDEX folder_id (folder_id);
<#810>
ALTER TABLE mail ADD INDEX m_status (m_status);
<#811>
ALTER TABLE frm_posts ADD INDEX pos_date (pos_date);
<#812>
ALTER TABLE `crs_settings` ADD `show_members` TINYINT NOT NULL DEFAULT '1';

<#813>
DROP TABLE IF EXISTS tmp_migration;
CREATE TABLE `tmp_migration` (
`objective_id` int(11) NOT NULL default '0',
`passed` tinyint(4) NOT NULL default '0');

<#814>
<?php
  // Store objective results in table crs_objective_result

  // Get all objectives
$query = "SELECT objective_id FROM crs_objectives ";
$res = $ilDB->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	// check if objective is already processed
	$res_passed = $ilDB->query("SELECT objective_id FROM tmp_migration WHERE objective_id = '".$row->objective_id."' AND passed = '1'");
	if($res_passed->numRows())
	{
		continue;
	}

	// Read objective info
	$query = "SELECT * FROM crs_objective_tst as ct JOIN crs_objective_qst as cq ".
		"ON (ct.objective_id = cq.objective_id AND ct.obj_id = cq.obj_id) ".
		"WHERE tst_status = '1' AND ct.objective_id = '".$row->objective_id."'";

	$obj_info = $ilDB->query($query);
	$objective_info = array();
	while($obj_row = $obj_info->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$objective_info[$obj_row->obj_id]['questions'][] = $obj_row->question_id;
		$objective_info[$obj_row->obj_id]['limit'] = $obj_row->tst_limit;
	}
	
	// Read max reachable points
	// Read user points
	foreach($objective_info as $test_id => $data)
	{
		$query = "SELECT SUM(points) as reachable FROM qpl_questions WHERE ".
			"question_id IN('".implode("','",$data['questions'])."')";
		$reachable_res = $ilDB->query($query);
		while($reachable_row = $reachable_res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objective_info[$test_id]['reachable'] = $reachable_row->reachable;
		}
		
		$query = "SELECT user_fi, MAX(points) as reached FROM tst_test_result JOIN tst_active ON active_fi = active_id ".
			"WHERE question_fi IN('".implode("','",$data['questions'])."') ".
			"GROUP BY question_fi,user_fi";
		$user_reached_res = $ilDB->query($query);
		$objective_info[$test_id]['users'] = array();
		while($user_reached_row = $user_reached_res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objective_info[$test_id]['users'][$user_reached_row->user_fi] += $user_reached_row->reached;
		}
	}
	// Check reached
	foreach($objective_info as $test_id => $data)
	{
		if(!$data['reachable'])
		{
			continue;
		}
		foreach($data['users'] as $user_id => $reached)
		{
			if(($reached / $data['reachable'] * 100) >= $data['limit'])
			{
				$query = "REPLACE INTO crs_objective_status ".
					"SET objective_id = '".$row->objective_id."', ".
					"user_id = '".$user_id."', ".
					"status = '1'";
				$ilDB->query($query);
			}
		}
	}
	// Now set objective passed
	$query = "REPLACE INTO tmp_migration ".
		"SET objective_id = '".$row->objective_id."', ".
		"passed = '1'";
	$ilDB->query($query);
}
?>
<#815>
DROP TABLE IF EXISTS tmp_migration;
<#816>
ALTER TABLE xhtml_page ADD COLUMN save_content MEDIUMTEXT;
<#817>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#818>
<?php
// get all languages
$q = "SELECT * FROM object_data WHERE type = ".$ilDB->quote("lng");
$lang_set = $ilDB->query($q);
while($lang_rec = $lang_set->fetchRow(DB_FETCHMODE_ASSOC))
{
	// get all installed languages
	if (substr($lang_rec["description"], 0, 9) == "installed")
	{
		$q = "SELECT * FROM lng_data WHERE lang_key = ".$ilDB->quote($lang_rec["title"]);
		$var_set = $ilDB->query($q);
		$lang_array = array();
		
		// get data from lng_data table
		while($var_rec = $var_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$lang_array[$var_rec["module"]][$var_rec["identifier"]] = $var_rec["value"];
		}
		
		// put data into lng_modules table
		foreach($lang_array as $module => $lang_arr)
		{
			$query = "REPLACE INTO lng_modules (lang_key, module, lang_array) VALUES ".
				 "(".$ilDB->quote($lang_rec["title"]).", " .
				 " ".$ilDB->quote($module).", " . 
				 " ".$ilDB->quote(serialize($lang_arr)).") ";
			$ilDB->query($query);
		}

	}
}
?>
<#819>
ALTER TABLE `tst_tests` ADD `instant_verification` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `score_reporting`;
<#820>
UPDATE tst_tests SET instant_verification = '1' WHERE score_reporting = 0;
UPDATE tst_tests SET score_reporting = 1 WHERE score_reporting = 0;

<#821>
<?php
// fetch type id of folder object definition
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='fold'";
$res = $this->db->query($query);
$row = $res->fetchRow();
$typ_id = $row[0];

// 18: create_frm, 20: create_lm, 21: create_slm, 22: create_glo, 25: create_file, 26: create_grp
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','17')";
$this->db->query($query);
?>
<#822>
<?php
// add certificate_visibility field to tst_tests but only if it not exists
$certificate_visibility = FALSE;
$query = "SHOW COLUMNS FROM tst_tests";
$res = $ilDB->query($query);
if ($res->numRows())
{
	while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		if (strcmp($data["Field"], "certificate_visibility") == 0)
		{
			$certificate_visibility = TRUE;
		}
	}
}
if ($certificate_visibility == FALSE)
{
	$query = "ALTER TABLE `tst_tests` ADD `certificate_visibility` ENUM( '0', '1', '2' ) NOT NULL DEFAULT '0' AFTER `show_question_titles`";
	$res = $ilDB->query($query);
}
?>
<#823>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#824>
ALTER TABLE `tst_tests` ADD `anonymity` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `instant_verification`;
<#825>
UPDATE tst_tests SET anonymity = '1' WHERE test_type_fi = 2;
<#826>
ALTER TABLE `tst_tests` ADD `show_cancel` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `instant_verification`;
<#827>
ALTER TABLE `tst_tests` ADD `use_previous_answers` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `hide_previous_results`;
<#828>
UPDATE tst_tests SET use_previous_answers = '0' WHERE hide_previous_results = '1';
<#829>
ALTER TABLE `tst_tests` DROP `hide_previous_results`;
<#830>
UPDATE `tst_tests` SET `score_reporting` = 3 WHERE length(`reporting_date`) > 0;
<#831>
ALTER TABLE `tst_tests` ADD `fixed_participants` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `instant_verification`;
<#832>
UPDATE tst_tests SET fixed_participants = '1' WHERE test_type_fi = '4';
<#833>
ALTER TABLE `tst_tests` CHANGE `show_summary` `show_summary` ENUM( '0', '1', '2', '3', '4', '5', '6', '7' ) NOT NULL DEFAULT '0';
<#834>
CREATE table `il_news_item`
(
	`id` int not null auto_increment primary key,
	`creation_date` datetime,
	`priority` enum('0','1','2') default 1,
	`title` varchar(200),
	`content` text,
	`context_obj_id` int,
	`context_obj_type` char(10),
	`context_sub_obj_id` int,
	`context_sub_obj_type` char(10),
	`content_type` enum('text','html')
);
<#835>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#836>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#837>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#838>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#839>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#840>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#841>
UPDATE tst_tests SET show_summary = '7' WHERE test_type_fi = '4';
<#842>
UPDATE tst_tests SET show_solution_printview = '1' WHERE test_type_fi = '4';
<#843>
INSERT INTO settings (module, keyword, value) VALUES ('assessment', 'assessment_manual_scoring', '8');
<#844>
ALTER TABLE `tst_tests` ADD `title_output` ENUM( '0', '1', '2' ) NOT NULL DEFAULT '0' AFTER `hide_title_points`;
<#845>
UPDATE tst_tests SET title_output = '1' WHERE hide_title_points = '1';
<#846>
ALTER TABLE `tst_tests` DROP `hide_title_points`;
<#847>
# add operation 'tst_statistics' for tests
INSERT INTO rbac_operations (ops_id,operation,class,description) VALUES ('56', 'tst_statistics', 'object','view the statistics of a test');
<#848>
<?php
// retrieve test object data
$query = "SELECT obj_id FROM object_data WHERE type='typ' AND title='tst'";
$result = $ilDB->query($query);
$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
$typ_id = $row->obj_id;

// append operation assignment to test object definition
// 56: tst_statistics
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('".$typ_id."','56')";
$ilDB->query($query);
?>
<#849>
<?php
	$query = "SELECT rbac_pa.* FROM rbac_pa, object_data, object_reference WHERE object_data.type = 'tst' AND object_reference.obj_id = object_data.obj_id AND rbac_pa.ref_id = object_reference.ref_id";
	$result = $ilDB->query($query);
	while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$ops = unserialize(stripslashes($row["ops_id"]));
		if (in_array(4, $ops))
		{
			array_push($ops, 56);
			$ops_id = addslashes(serialize($ops));
			$query = sprintf("REPLACE INTO rbac_pa (rol_id,ops_id,ref_id) VALUES (%s, %s, %s)",
				$ilDB->quote($row["rol_id"] . ""),
				$ilDB->quote($ops_id . ""),
				$ilDB->quote($row["ref_id"] . "")
			);
			$ilDB->query($query);
		}
	}
?>
<#850>
CREATE TABLE `qpl_feedback_singlechoice` (
  `feedback_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL,
  `answer` int(11) NOT NULL,
  `feedback` text NOT NULL,
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`feedback_id`),
  KEY `question_fi` (`question_fi`)
);
<#851>
CREATE TABLE `qpl_feedback_generic` (
  `feedback_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL,
  `correctness` enum('0','1') NOT NULL default '0',
  `feedback` text NOT NULL,
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`feedback_id`),
  KEY `question_fi` (`question_fi`)
);
<#852>
ALTER TABLE `tst_tests` ADD `answer_feedback` ENUM( '0', '1') NOT NULL DEFAULT '0' AFTER `instant_verification`;
<#853>
ALTER TABLE `tst_tests` ADD `answer_feedback_points` ENUM( '0', '1') NOT NULL DEFAULT '0' AFTER `answer_feedback`;
<#854>
CREATE TABLE `qpl_feedback_multiplechoice` (
  `feedback_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL,
  `answer` int(11) NOT NULL,
  `feedback` text NOT NULL,
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`feedback_id`),
  KEY `question_fi` (`question_fi`)
);
<#855>
CREATE TABLE `qpl_feedback_imagemap` (
  `feedback_id` int(11) NOT NULL auto_increment,
  `question_fi` int(11) NOT NULL,
  `answer` int(11) NOT NULL,
  `feedback` text NOT NULL,
  `lastchange` timestamp NOT NULL,
  PRIMARY KEY  (`feedback_id`),
  KEY `question_fi` (`question_fi`)
);
<#856>
<?php
// add matrix row field to survey_answer but only if it not exists
$row_visibility = FALSE;
$query = "SHOW COLUMNS FROM survey_answer";
$res = $ilDB->query($query);
if ($res->numRows())
{
	while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		if (strcmp($data["Field"], "row") == 0)
		{
			$row_visibility = TRUE;
		}
	}
}
if ($row_visibility == FALSE)
{
	$query = "ALTER TABLE `survey_answer` ADD `row` INT NOT NULL DEFAULT '0' AFTER `textanswer`";
	$res = $ilDB->query($query);
}
?>
<#857>
CREATE TABLE IF NOT EXISTS `survey_question_matrix` (
`question_fi` INT NOT NULL ,
`subtype` INT NOT NULL DEFAULT '0',
`column_separators` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
`row_separators` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
`neutral_column_separator` ENUM( '0', '1' ) NOT NULL DEFAULT '1',
`column_placeholders` INT NOT NULL DEFAULT '0',
`legend` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
`singleline_row_caption` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
`repeat_column_header` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
`column_header_position` ENUM( '0', '1', '2', '3' ) NOT NULL DEFAULT '0',
`random_rows` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
`column_order` ENUM( '0', '1', '2' ) NOT NULL DEFAULT '0',
`column_images` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
`row_images` ENUM( '0', '1' ) NOT NULL ,
`lastchange` TIMESTAMP NOT NULL ,
PRIMARY KEY ( `question_fi` )
);
<#858>
<?php
// add neutral field to survey_category but only if it not exists
$neutral_visibility = FALSE;
$query = "SHOW COLUMNS FROM survey_category";
$res = $ilDB->query($query);
if ($res->numRows())
{
	while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		if (strcmp($data["Field"], "neutral") == 0)
		{
			$neutral_visibility = TRUE;
		}
	}
}
if ($neutral_visibility == FALSE)
{
	$query = "ALTER TABLE `survey_category` ADD `neutral` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `owner_fi`";
	$res = $ilDB->query($query);
}
?>
<#859>
<?php
// add matrix question as question type if it does not exist
$query = "SELECT questiontype_id FROM survey_questiontype WHERE type_tag = 'SurveyMatrixQuestion'";
$result = $ilDB->query($query);
if ($result->numRows() == 0)
{
	$query = "INSERT INTO `survey_questiontype` ( `questiontype_id` , `type_tag` , `TIMESTAMP` ) VALUES ( '5', 'SurveyMatrixQuestion', NOW( ) )";
	$result = $ilDB->query($query);
}
?>
<#860>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#861>
CREATE TABLE IF NOT EXISTS `survey_question_matrix_rows` (
`id_survey_question_matrix_rows` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`title` VARCHAR( 255 ) NOT NULL ,
`sequence` INT NOT NULL DEFAULT 0,
`question_fi` INT NOT NULL ,
INDEX ( `question_fi` )
);
<#862>
<?php
// add bipolar adjectives fields to survey_question_matrix but only if it not exists
$bipolar_visibility = FALSE;
$query = "SHOW COLUMNS FROM survey_question_matrix";
$res = $ilDB->query($query);
if ($res->numRows())
{
	while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		if (strcmp($data["Field"], "bipolar_adjective1") == 0)
		{
			$bipolar_visibility = TRUE;
		}
	}
}
if ($bipolar_visibility == FALSE)
{
	$query = "ALTER TABLE `survey_question_matrix` ADD `bipolar_adjective1` VARCHAR( 255 ) NULL AFTER `row_images` , ADD `bipolar_adjective2` VARCHAR( 255 ) NULL AFTER `bipolar_adjective1`";
	$res = $ilDB->query($query);
}
?>
<#863>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#864>
<?php
$ilCtrlStructureReader->getStructure();




				// FILE ENDS HERE, DO NOT ADD ANY ADDITIONAL STEPS
				//
				//         USE dbupdate_02.php INSTEAD




?>
