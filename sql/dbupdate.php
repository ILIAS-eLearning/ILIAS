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
// remove LDAP node temporary

// get LDAP node data
$query = "SELECT * FROM tree WHERE child=13 AND tree=1";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

// take out node in main tree
$query = "update tree SET tree='-13' WHERE child=13";
$this->db->query($query);

// close gaps
$diff = $row->rgt - $row->lft + 1;

$query = "UPDATE tree SET ".
		 "lft = CASE ".
		 "WHEN lft > '".$row->lft." '".
		 "THEN lft - '".$diff." '".
		 "ELSE lft ".
		 "END, ".
		 "rgt = CASE ".
		 "WHEN rgt > '".$row->lft." '".
		 "THEN rgt - '".$diff." '".
		 "ELSE rgt ".
		 "END ".
		 "WHERE tree = 1";
$this->db->query($query);
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
