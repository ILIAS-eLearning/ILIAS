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
