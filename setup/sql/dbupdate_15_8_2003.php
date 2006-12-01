<#1>
#intial release of database
<#2>
# adding forum tables
DROP TABLE IF EXISTS frm_posts_tree;
CREATE TABLE frm_posts_tree (
  fpt_pk bigint(20) NOT NULL auto_increment,
  thr_fk bigint(20) NOT NULL default '0',
  pos_fk bigint(20) NOT NULL default '0',
  parent_pos bigint(20) NOT NULL default '0',
  lft int(11) NOT NULL default '0',
  rgt int(11) NOT NULL default '0',
  depth int(11) NOT NULL default '0',
  date datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (fpt_pk)
) TYPE=MyISAM;

DROP TABLE IF EXISTS frm_data;
CREATE TABLE frm_data (
  top_pk bigint(20) NOT NULL auto_increment,
  top_frm_fk bigint(20) NOT NULL default '0',
  top_name varchar(255) NOT NULL default '',
  top_description varchar(255) NOT NULL default '',
  top_num_posts int(11) NOT NULL default '0',
  top_num_threads int(11) NOT NULL default '0',
  top_last_post varchar(50) NOT NULL default '',
  top_mods varchar(100) NOT NULL default '',
  top_last_modified datetime NOT NULL default '0000-00-00 00:00:00',
  visits int(11) NOT NULL default '0',
  PRIMARY KEY  (top_pk)
) TYPE=MyISAM;

DROP TABLE IF EXISTS frm_posts;
CREATE TABLE frm_posts (
  pos_pk bigint(20) NOT NULL auto_increment,
  pos_top_fk bigint(20) NOT NULL default '0',
  pos_thr_fk bigint(20) NOT NULL default '0',
  pos_usr_id bigint(20) NOT NULL default '0',
  pos_message text NOT NULL,
  pos_date datetime NOT NULL default '0000-00-00 00:00:00',
  pos_update datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (pos_pk)
) TYPE=MyISAM;

DROP TABLE IF EXISTS frm_threads;
CREATE TABLE frm_threads (
  thr_pk bigint(20) NOT NULL auto_increment,
  thr_top_fk bigint(20) NOT NULL default '0',
  thr_subject varchar(255) NOT NULL default '',
  thr_usr_id bigint(20) NOT NULL default '0',
  thr_num_posts int(11) NOT NULL default '0',
  thr_last_post varchar(50) NOT NULL default '',
  thr_date datetime NOT NULL default '0000-00-00 00:00:00',
  thr_update datetime NOT NULL default '0000-00-00 00:00:00',
  thr_last_modified datetime NOT NULL default '0000-00-00 00:00:00',
  visits int(11) NOT NULL default '0',
  PRIMARY KEY  (thr_pk)
) TYPE=MyISAM;

<#3>
# set system adminstrator login to root/homer
UPDATE usr_data SET 
login='root',
passwd='dfa8327f5bfa4c672a04f9b38e348a70' 
WHERE usr_id='6';

<#4>
# change column in `frm_data`
ALTER TABLE `frm_data` CHANGE `top_last_modified` `top_date` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL;

# new column in `frm_data`
ALTER TABLE `frm_data` ADD `top_update` DATETIME NOT NULL;

# new column in `frm_data`
ALTER TABLE `frm_data` ADD `update_user` INT NOT NULL ;

# new column in `frm_data`
ALTER TABLE `frm_data` ADD `top_usr_id` BIGINT( 20 ) NOT NULL ;

# delete column in `frm_threads`
ALTER TABLE `frm_threads` DROP `thr_last_modified`;

<#5>
# There are some old wrong entries in rbac_templates => delete them
DELETE FROM rbac_templates
WHERE parent='152';

<#6>
# new forum operation in `rbac_operations`
INSERT INTO `rbac_operations` ( `ops_id` , `operation` , `description` ) 
VALUES (
'9', 'edit post', 'edit forum articles'
);

# new operation link in `rbac_ta`
INSERT INTO `rbac_ta` ( `typ_id` , `ops_id` )
VALUES (
'14', '9'
);

<#7>
# change data type in `frm_data`
ALTER TABLE `frm_data` CHANGE `top_mods` `top_mods` INT NOT NULL ;

# new forum operation in `rbac_operations`
INSERT INTO `rbac_operations` ( `ops_id` , `operation` , `description` ) 
VALUES (
'10', 'delete post', 'delete forum articles'
);

# new operation link in `rbac_ta`
INSERT INTO `rbac_ta` ( `typ_id` , `ops_id` ) 
VALUES (
'14', '10'
);

<#8>
# new column in `frm_posts`
ALTER TABLE `frm_posts` ADD `update_user` INT NOT NULL ;

<#9>
#  delete operation create of root folder and add operation delete
UPDATE rbac_ta SET ops_id='6' WHERE typ_id='33' AND ops_id='5';

<#10>
#  set missing primary key an auto increment flag for some lo_tables
ALTER TABLE `lo_attribute_name_leaf` DROP PRIMARY KEY , ADD PRIMARY KEY ( `leaf_id` );
ALTER TABLE `lo_attribute_name_leaf` CHANGE `leaf_id` `leaf_id` INT( 11 ) DEFAULT '0' NOT NULL AUTO_INCREMENT;

ALTER TABLE `lo_attribute` DROP PRIMARY KEY ,ADD PRIMARY KEY ( `attribute_id` );
ALTER TABLE `lo_attribute` CHANGE `attribute_id` `attribute_id` INT( 11 ) DEFAULT '0' NOT NULL AUTO_INCREMENT;

ALTER TABLE `lo_attribute_namespace_leaf` DROP PRIMARY KEY ,ADD PRIMARY KEY ( `leaf_id` );
ALTER TABLE `lo_attribute_namespace_leaf` CHANGE `leaf_id` `leaf_id` INT( 11 ) DEFAULT '0' NOT NULL AUTO_INCREMENT;

<#11>
# new object-types: note folder object, note object
INSERT INTO object_data (type,title,description,owner,create_date,last_update) VALUES ('typ', 'notf', 'Note Folder Object', -1, '2002-12-21 00:04:00', '2002-12-21 00:04:00');
INSERT INTO object_data (type,title,description,owner,create_date,last_update) VALUES ('typ', 'note', 'Note Object', -1, '2002-12-21 00:04:00', '2002-12-21 00:04:00');

# new table note_data
DROP TABLE IF EXISTS note_data;
CREATE TABLE note_data (
  note_id int(11) NOT NULL default '0',
  lo_id int(11) NOT NULL default '0',
  text text,
  create_date datetime NOT NULL default '0000-00-00 00:00:00',
  last_update datetime NOT NULL default '0000-00-00 00:00:00',
  important enum('y','n') NOT NULL default 'n',
  good enum('y','n') NOT NULL default 'n',
  question enum('y','n') NOT NULL default 'n',
  bad enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (note_id)
) TYPE=MyISAM;

<#12>
# many changes in LO-repository

# remove old tables
 DROP TABLE IF EXISTS `lo_attribute` ,
`lo_attribute_name_leaf` ,
`lo_attribute_namespace_leaf` ,
`lo_attribute_value_leaf` ,
`lo_cdata_leaf` ,
`lo_comment_leaf` ,
`lo_element_name_leaf` ,
`lo_element_namespace_leaf` ,
`lo_entity_reference_leaf` ,
`lo_pi_data_leaf` ,
`lo_pi_target_leaf` ,
`lo_text_leaf` ,
`lo_tree` ;

# add new tables
DROP TABLE IF EXISTS lo_attribute_idx;
CREATE TABLE lo_attribute_idx (
  node_id int(10) unsigned NOT NULL default '0',
  attribute_id smallint(5) unsigned NOT NULL default '0',
  value_id smallint(5) unsigned NOT NULL default '0',
  KEY node_id (node_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_attribute_name;
CREATE TABLE lo_attribute_name (
  attribute_id smallint(5) unsigned NOT NULL auto_increment,
  attribute char(32) NOT NULL default '',
  PRIMARY KEY  (attribute_id),
  UNIQUE KEY attribute (attribute)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_attribute_namespace;
CREATE TABLE lo_attribute_namespace (
  attribute_id smallint(5) unsigned NOT NULL auto_increment,
  node_id int(10) unsigned NOT NULL default '0',
  namespace char(64) NOT NULL default '',
  PRIMARY KEY  (attribute_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_attribute_value;
CREATE TABLE lo_attribute_value (
  value_id smallint(5) unsigned NOT NULL auto_increment,
  value char(32) NOT NULL default '0',
  PRIMARY KEY  (value_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_cdata;
CREATE TABLE lo_cdata (
  node_id int(10) unsigned NOT NULL auto_increment,
  cdata text NOT NULL,
  PRIMARY KEY  (node_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_comment;
CREATE TABLE lo_comment (
  node_id int(10) unsigned NOT NULL auto_increment,
  comment text NOT NULL,
  PRIMARY KEY  (node_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_element_idx;
CREATE TABLE lo_element_idx (
  node_id int(10) unsigned NOT NULL default '0',
  element_id smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (node_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_element_name;
CREATE TABLE lo_element_name (
  element_id smallint(5) unsigned NOT NULL auto_increment,
  element char(32) NOT NULL default '',
  PRIMARY KEY  (element_id),
  UNIQUE KEY element (element)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_element_namespace;
CREATE TABLE lo_element_namespace (
  element_id smallint(5) unsigned NOT NULL auto_increment,
  node_id int(10) unsigned NOT NULL default '0',
  namespace char(64) NOT NULL default '',
  PRIMARY KEY  (element_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_entity_reference;
CREATE TABLE lo_entity_reference (
  element_id smallint(5) unsigned NOT NULL auto_increment,
  node_id int(10) unsigned NOT NULL default '0',
  entity_reference char(128) NOT NULL default '',
  PRIMARY KEY  (element_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_pi_data;
CREATE TABLE lo_pi_data (
  leaf_id int(10) unsigned NOT NULL auto_increment,
  node_id int(10) unsigned NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_pi_target;
CREATE TABLE lo_pi_target (
  leaf_id int(10) unsigned NOT NULL auto_increment,
  node_id int(10) unsigned NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_text;
CREATE TABLE lo_text (
  node_id int(10) unsigned NOT NULL default '0',
  textnode text NOT NULL,
  PRIMARY KEY  (node_id),
  FULLTEXT KEY textnode (textnode)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lo_tree;
CREATE TABLE lo_tree (
  node_id int(10) unsigned NOT NULL auto_increment,
  lo_id mediumint(8) unsigned NOT NULL default '0',
  parent_node_id int(10) unsigned NOT NULL default '0',
  lft smallint(5) unsigned NOT NULL default '0',
  rgt smallint(5) unsigned NOT NULL default '0',
  node_type_id tinyint(3) unsigned NOT NULL default '0',
  depth smallint(5) unsigned NOT NULL default '0',
  prev_sibling_node_id int(10) unsigned NOT NULL default '0',
  next_sibling_node_id int(10) unsigned NOT NULL default '0',
  first_child_node_id int(10) unsigned NOT NULL default '0',
  struct tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (node_id),
  KEY lo_id (lo_id)
) TYPE=MyISAM;

# remove old LO entries
<?php
$query = "SELECT obj_id FROM object_data WHERE type = 'lo'";
$result = $this->db->query($query);

while ($data = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	$obj_id = $data["obj_id"];
	
	$query = "DELETE FROM tree WHERE child = '".$obj_id."'";
	$this->db->query($query);
}

$query = "DELETE FROM object_data WHERE type = 'lo'";
$this->db->query($query); 
?>
# remove legacy forum objects
DELETE FROM tree WHERE child = '172';
DELETE FROM tree WHERE child = '174';
DELETE FROM tree WHERE child = '178';
DELETE FROM object_data WHERE obj_id = '172';
DELETE FROM object_data WHERE obj_id = '174';
DELETE FROM object_data WHERE obj_id = '178';
DELETE FROM rbac_pa WHERE obj_id = '172';
DELETE FROM rbac_pa WHERE obj_id = '174';
DELETE FROM rbac_pa WHERE obj_id = '178';

# db performance tuning: setting several indexes and shortened some data columns
ALTER TABLE `object_data` ADD INDEX (`type`);
ALTER TABLE `rbac_fa` DROP PRIMARY KEY;
ALTER TABLE `rbac_fa` DROP PRIMARY KEY, ADD PRIMARY KEY (`rol_id`);
ALTER TABLE `rbac_operations` CHANGE `operation` `operation` CHAR(32) NOT NULL;
ALTER TABLE `rbac_operations` ADD UNIQUE (`operation`);
ALTER TABLE `rbac_templates` ADD INDEX (`rol_id`);
ALTER TABLE `rbac_templates` ADD INDEX (`type`);
ALTER TABLE `rbac_templates` ADD INDEX (`ops_id`);
ALTER TABLE `rbac_templates` ADD INDEX (`parent`);
ALTER TABLE `rbac_ua` DROP PRIMARY KEY;
ALTER TABLE `rbac_ua` ADD INDEX (`usr_id`);
ALTER TABLE `rbac_ua` ADD INDEX (`rol_id`);
ALTER TABLE `settings` DROP PRIMARY KEY;
ALTER TABLE `settings` DROP PRIMARY KEY ,
ADD PRIMARY KEY (`keyword`);
ALTER TABLE `tree` CHANGE `tree` `tree` SMALLINT(5) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `tree` CHANGE `child` `child` INT(10) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `tree` CHANGE `parent` `parent` INT(10) UNSIGNED DEFAULT NULL;
ALTER TABLE `tree` CHANGE `lft` `lft` INT(10) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `tree` CHANGE `rgt` `rgt` INT(10) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `tree` CHANGE `depth` `depth` SMALLINT(5) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `usr_data` CHANGE `usr_id` `usr_id` INT(10) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `usr_pref` CHANGE `usr_id` `usr_id` INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE `tree` ADD INDEX (`child`);
ALTER TABLE `tree` ADD INDEX (`parent`);

<#13>
#due to problems changed index back for rbac_fa
ALTER TABLE `rbac_fa` DROP PRIMARY KEY, ADD PRIMARY KEY (rol_id,parent);

<#14>
#fixed bug of update #12
#tree tree shouldn't be unsigned, otherwise trash bin doesn't work
ALTER TABLE `tree` CHANGE `tree` `tree` INT(10) DEFAULT '0' NOT NULL;

<#15>
#drop unnecessary column
<?php
$query = "DESCRIBE note_data";
$result = $this->db->query($query);

while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	$data[] = $row["Field"];
}

if (in_array("last_update",$data))
{
	$query = "ALTER TABLE note_data DROP COLUMN last_update";
	$this->db->query($query);
}
?>
<#16>
#modified usr_data
ALTER TABLE usr_data CHANGE surname lastname CHAR( 30 ) NOT NULL;
ALTER TABLE usr_data ADD institution VARCHAR( 80 ) AFTER email ,
ADD street VARCHAR( 40 ) AFTER institution ,
ADD city VARCHAR( 40 ) AFTER street ,
ADD zipcode VARCHAR( 10 ) AFTER city ,
ADD country VARCHAR( 40 ) AFTER zipcode ,
ADD phone VARCHAR( 40 ) AFTER country ;

<#17>
#introducing reference id table
DROP TABLE IF EXISTS object_reference;
CREATE TABLE object_reference (
ref_id INT NOT NULL AUTO_INCREMENT,
obj_id INT NOT NULL ,
PRIMARY KEY (ref_id)
) TYPE=MyISAM;

<#18>
#migrate existing objects
<?php
$tree_objects = array("frm","grp","cat","root","adm","lngf","objf","usrf","rolf","lm","le","lo","crs");

$query = "SELECT type,obj_id FROM object_data ORDER by obj_id";
$result = $this->db->query($query);

while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
{
	$data[$row["obj_id"]] = $row["type"];
}

foreach ($data as $_id => $_type)
{
	if (in_array($_type,$tree_objects))
	{
		$query = "INSERT INTO object_reference (ref_id,obj_id) VALUES ('".$_id."','".$_id."')";
		$this->db->query($query);
	}
}
?>
<#19>
#enlarge several user fields to 32 letters
ALTER TABLE usr_data CHANGE login login VARCHAR( 32 ) NOT NULL;
ALTER TABLE usr_data CHANGE firstname firstname VARCHAR( 32 ) NOT NULL;
ALTER TABLE usr_data CHANGE lastname lastname VARCHAR( 32 ) NOT NULL;
ALTER TABLE usr_data CHANGE title title VARCHAR( 32 ) NOT NULL;

<#20>
# init mail functions
DROP TABLE IF EXISTS mail;
CREATE TABLE mail (
	mail_id int(11) NOT NULL auto_increment,
	user_id int(11) NOT NULL default '0',
	folder_id int(11) NOT NULL default '0',
	sender_id int(11) default NULL,
	attachments varchar(255) default NULL,
	send_time datetime NOT NULL default '0000-00-00 00:00:00',
	timest timestamp(14) NOT NULL,
	rcp_to varchar(255) default NULL,
	rcp_cc varchar(255) default NULL,
	rcp_bcc varchar(255) default NULL,
	m_status varchar(16) default NULL,
	m_type varchar(16) default NULL,
	m_email tinyint(1) default NULL,
	m_subject varchar(255) default NULL,
	m_message text,
	PRIMARY KEY (mail_id)
	) TYPE=MyISAM;

DROP TABLE IF EXISTS mail_attachment;
CREATE TABLE mail_attachment (
	mail_id int(11) NOT NULL default '0',
	path text NOT NULL,
	PRIMARY KEY  (mail_id)
	) TYPE=MyISAM;

DROP TABLE IF EXISTS mail_obj_data;
CREATE TABLE mail_obj_data (
	obj_id int(11) NOT NULL auto_increment,
	user_id int(11) NOT NULL default '0',
	title char(70) NOT NULL default '',
	type char(16) NOT NULL default '',
	PRIMARY KEY  (obj_id,user_id)
) TYPE=MyISAM;
			
DROP TABLE IF EXISTS mail_options;
CREATE TABLE mail_options (
				user_id int(11) NOT NULL default '0',
  linebreak tinyint(4) NOT NULL default '0',
  signature text NOT NULL,
  KEY user_id (user_id,linebreak)
) TYPE=MyISAM;

DROP TABLE IF EXISTS mail_saved;
CREATE TABLE mail_saved (
  user_id int(11) NOT NULL default '0',
  attachments varchar(255) default NULL,
  rcp_to varchar(255) default NULL,
  rcp_cc varchar(255) default NULL,
  rcp_bcc varchar(255) default NULL,
  m_type varchar(16) default NULL,
  m_email tinyint(1) default NULL,
  m_subject varchar(255) default NULL,
  m_message text
	) TYPE=MyISAM;

DROP TABLE IF EXISTS mail_tree;
CREATE TABLE mail_tree (
  tree int(11) NOT NULL default '0',
  child int(11) unsigned NOT NULL default '0',
  parent int(11) unsigned default NULL,
  lft int(11) unsigned NOT NULL default '0',
  rgt int(11) unsigned NOT NULL default '0',
  depth smallint(5) unsigned NOT NULL default '0',
  KEY child (child),
  KEY parent (parent)
) TYPE=MyISAM;

<#21>
<?php
$query = "SELECT usr_id FROM usr_data";
$result = $this->db->query($query);
$counter = 1;
while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
{
	$parent = $counter + 1;
	$query = "INSERT INTO mail_tree ".
		"SET tree = '".$row->usr_id."',".
		"child = '".++$counter."',".
		"parent = '0',".
		"lft = '1',".
		"rgt = '12',".
		"depth = '1'";
	$res2 = $this->db->query($query);
	$query = "INSERT INTO mail_obj_data ".
		"SET obj_id = '".$counter."',".
		"user_id = '".$row->usr_id."',".
		"title = 'a_root',".
		"type = 'root'";
	$res3 = $this->db->query($query);
	$query = "INSERT INTO mail_tree ".
		"SET tree = '".$row->usr_id."',".
		"child = '".++$counter."',".
		"parent = '".$parent."',".
		"lft = '2',".
		"rgt = '3',".
		"depth = '2'";
	$res2 = $this->db->query($query);
	$query = "INSERT INTO mail_obj_data ".
		"SET obj_id = '".$counter."',".
		"user_id = '".$row->usr_id."',".
		"title = 'b_inbox',".
		"type = 'inbox'";
	$res3 = $this->db->query($query);
	$query = "INSERT INTO mail_tree ".
		"SET tree = '".$row->usr_id."',".
		"child = '".++$counter."',".
		"parent = '".$parent."',".
		"lft = '4',".
		"rgt = '5',".
		"depth = '2'";
	$res2 = $this->db->query($query);
	$query = "INSERT INTO mail_obj_data ".
		"SET obj_id = '".$counter."',".
		"user_id = '".$row->usr_id."',".
		"title = 'c_trash',".
		"type = 'trash'";
	$res3 = $this->db->query($query);
	$query = "INSERT INTO mail_tree ".
		"SET tree = '".$row->usr_id."',".
		"child = '".++$counter."',".
		"parent = '".$parent."',".
		"lft = '6',".
		"rgt = '7',".
		"depth = '2'";
	$res2 = $this->db->query($query);
	$query = "INSERT INTO mail_obj_data ".
		"SET obj_id = '".$counter."',".
		"user_id = '".$row->usr_id."',".
		"title = 'd_drafts',".
		"type = 'drafts'";
	$res3 = $this->db->query($query);
	$query = "INSERT INTO mail_tree ".
		"SET tree = '".$row->usr_id."',".
		"child = '".++$counter."',".
		"parent = '".$parent."',".
		"lft = '8',".
		"rgt = '9',".
		"depth = '2'";
	$res2 = $this->db->query($query);
	$query = "INSERT INTO mail_obj_data ".
		"SET obj_id = '".$counter."',".
		"user_id = '".$row->usr_id."',".
		"title = 'e_sent',".
		"type = 'sent'";
	$res3 = $this->db->query($query);
	$query = "INSERT INTO mail_tree ".
		"SET tree = '".$row->usr_id."',".
		"child = '".++$counter."',".
		"parent = '".$parent."',".
		"lft = '10',".
		"rgt = '11',".
		"depth = '2'";
	$res2 = $this->db->query($query);
	$query = "INSERT INTO mail_obj_data ".
		"SET obj_id = '".$counter."',".
		"user_id = '".$row->usr_id."',".
		"title = 'z_local',".
		"type = 'local'";
	$res3 = $this->db->query($query);
	$query = "INSERT INTO mail_options ".
		"SET user_id = '".$row->usr_id."',".
		"linebreak = '60'";
	$res4 = $this->db->query($query);
}
?>
<#22>
CREATE TABLE addressbook (
  addr_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  login varchar(40) default NULL,
  firstname varchar(40) default NULL,
  lastname varchar(40) default NULL,
  email varchar(40) default NULL,
  PRIMARY KEY  (addr_id)
) TYPE=MyISAM;

<#23>
UPDATE settings
SET value = 'y'
WHERE keyword = 'mail_allow_smtp';

<#24>
DROP TABLE IF EXISTS bookmark_tree;
CREATE TABLE bookmark_tree (
  tree int(11) NOT NULL default '0',
  child int(11) unsigned NOT NULL default '0',
  parent int(11) unsigned default NULL,
  lft int(11) unsigned NOT NULL default '0',
  rgt int(11) unsigned NOT NULL default '0',
  depth smallint(5) unsigned NOT NULL default '0',
  KEY child (child),
  KEY parent (parent)
) TYPE=MyISAM;

DROP TABLE IF EXISTS bookmark_data;
CREATE TABLE bookmark_data (
	obj_id int(11) NOT NULL auto_increment,
	user_id int(11) NOT NULL default '0',
	title varchar(200) NOT NULL default '',
	target varchar(200) NOT NULL default '',
	type char(4) NOT NULL default '',
	PRIMARY KEY  (obj_id,user_id)
) TYPE=MyISAM;

<#25>
<?php
$query = "SELECT usr_id FROM usr_data";
$result = $this->db->query($query);
while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO bookmark_tree ".
		"SET tree = '".$row->usr_id."',".
		"child = '1',".
		"parent = '0',".
		"lft = '1',".
		"rgt = '2',".
		"depth = '1'";
	$res2 = $this->db->query($query);
}
?>
INSERT INTO bookmark_data (obj_id, user_id, title, target, type)
VALUES (1, 0, 'dummy', '', 'dum');

<#26>
DROP TABLE IF EXISTS grp_data;
CREATE TABLE grp_data (
	grp_id int(11) NOT NULL,
	status int(11),
	PRIMARY KEY(grp_id)
) TYPE=MyISAM;
<#27>
<?php

// INSERT MAIL OBJECT IN object_data
$query = "SELECT MAX(obj_id) FROM object_data";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$max_id = $row["MAX(obj_id)"];
}
++$max_id;
$query = "INSERT INTO object_data ".
         "VALUES ('".$max_id."','mail','Mail Settings','Mail settings object','-1',now(),now())";
$this->db->query($query);

// INSERT MAIL OBJECT IN TREE (UNDER SYSTEMSETTINGS FOLDER)
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
	 "('1','".$max_id."','9','".$lft."','".$rgt."','2')";
$this->db->query($query);

// CREATE OBJECT REFERENCE ENTRY
$query = "INSERT INTO object_reference VALUES('".$max_id."','".$max_id."')";
$this->db->query($query);

// ADD NEW OPERATION smtp_mail
$query = "SELECT MAX(ops_id) FROM rbac_operations ";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$max_ops_id = $row["MAX(ops_id)"];
}
++$max_ops_id;

// INSERT NEW OPERAION smtp_mail
$query = "INSERT INTO rbac_operations ".
         "VALUES('".$max_ops_id."','smtp mail','send external mail')";
$res = $this->db->query($query);

// INSERT PERMISSIONS FOR ADMIN ROLE
$permissions = addslashes(serialize(array("1","2","3","4","$max_ops_id")));
$query = "INSERT INTO rbac_pa VALUES('2','".$permissions."','".$max_id."','".$max_id."')";
$this->db->query($query);

// DELETE create AND delete OPERATION FORM OBJERCT MAIL
$query = "DELETE FROM rbac_ta WHERE typ_id = '19' AND ops_id IN('5','6')";
$this->db->query($query);

// ADD OPERATION smtp mail FOR OBJECT MAIL
$query = "INSERT INTO rbac_ta VALUES('19','".$max_ops_id."')";
$this->db->query($query);

?>

<#28>
<?php
// ADD NEW OPERATION system messages
$query = "SELECT MAX(ops_id) FROM rbac_operations ";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$max_ops_id = $row["MAX(ops_id)"];
}
++$max_ops_id;

// INSERT NEW OPERAION system message
$query = "INSERT INTO rbac_operations ".
         "VALUES('".$max_ops_id."','system message','allow to send system messages')";
$res = $this->db->query($query);

// ADD OPERATION smtp mail FOR OBJECT MAIL
$query = "INSERT INTO rbac_ta VALUES('19','".$max_ops_id."')";
$this->db->query($query);
?>

<#29>
DROP TABLE IF EXISTS lm_structure_object;
CREATE TABLE lm_structure_object (
  lm_id int(11) NOT NULL default '0',
  child int(11) unsigned NOT NULL default '0',
  parent int(11) unsigned default NULL,
  lft int(11) unsigned NOT NULL default '0',
  rgt int(11) unsigned NOT NULL default '0',
  depth smallint(5) unsigned NOT NULL default '0',
  KEY child (child),
  KEY parent (parent)
) TYPE=MyISAM;

DROP TABLE IF EXISTS lm_page_object;
CREATE TABLE lm_page_object (
	page_id int(11) NOT NULL auto_increment,
	lm_id int(11),
	content blob,
	PRIMARY KEY  (page_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS meta_data;
CREATE TABLE meta_data (
	obj_id int(11) NOT NULL,
	obj_type char(3) NOT NULL,
	title varchar(200) NOT NULL default '',
	PRIMARY KEY  (obj_id, obj_type)
) TYPE=MyISAM;

<#30>

ALTER TABLE lm_structure_object RENAME AS lm_tree;

ALTER TABLE lm_page_object MODIFY page_id int(11) NOT NULL;

CREATE TABLE lm_data (
	obj_id int(11) NOT NULL auto_increment,
	title varchar(200) NOT NULL default '',
	type char(2) NOT NULL default '',
	PRIMARY KEY  (obj_id)
) TYPE=MyISAM;

<#31>

ALTER TABLE lm_data ADD COLUMN lm_id int(11) NOT NULL;

<#32>

DELETE FROM lm_data;
DELETE FROM lm_tree;
DELETE FROM lm_page_object;
DELETE FROM meta_data;
INSERT INTO lm_data (obj_id, title, type, lm_id) VALUES (1, 'dummy', 'du', 0);

<#33>

# new column in `frm_posts`
ALTER TABLE `frm_posts` ADD `pos_cens` tinyint(4) NOT NULL;

# new column in `frm_posts`
ALTER TABLE `frm_posts` ADD `pos_cens_com` text NOT NULL ;

<?php
// set pos_cens = 0
$query = "UPDATE frm_posts SET pos_cens = 0 WHERE pos_cens != 0";
$res = $this->db->query($query);

?>
<#34>
<?php
$query = "SELECT * FROM object_data WHERE type='mail'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$obj_id = $row->obj_id;
}
// DELETE OLD MAIL ENTRY IN TREE
$query = "DELETE FROM tree ".
         "WHERE tree = '1' ".
         "AND child = '".$obj_id."'";
$res = $this->db->query($query);

// ... AND IN object_reference
$query = "DELETE FROM object_reference ".
         "WHERE ref_id = '".$obj_id."'";
$res = $this->db->query($query);

// INSERT MAIL OBJECT IN TREE (UNDER SYSTEMSETTINGS FOLDER)
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

// CREATE object_reference ENTRY
$query = "INSERT INTO object_reference SET obj_id = '".$obj_id."'";
$res = $this->db->query($query);

// GET LAST INSERT ID
$query = "SELECT * FROM object_reference WHERE obj_id ='".$obj_id."'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ref_id = $row->ref_id;
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
	 "('1','".$ref_id."','9','".$lft."','".$rgt."','3')";
$this->db->query($query);
?>
<#35>
<?php
$query = "SELECT * FROM object_data,object_reference WHERE object_data.type='mail' AND object_data.obj_id = object_reference.obj_id";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ref_id = $row->ref_id;
}

$query = "DELETE FROM rbac_pa WHERE obj_id = '".$ref_id."'";
$res = $this->db->query($query);

// INSERT PERMISSIONS FOR MAIL OBJECT
$query = "INSERT INTO rbac_pa ".
         " VALUES('2','a:6:{i:0;s:1:\"1\";i:1;s:1:\"3\";i:2;s:2:\"11\";i:3;s:2:\"12\";i:4;s:1:\"2\";i:5;s:1:\"4\";}','".$ref_id."','".$ref_id."')";
$res = $this->db->query($query);
?>

<#36>
<?php
$query = "SELECT * FROM object_data,object_reference WHERE object_data.type='mail' AND object_data.obj_id = object_reference.obj_id";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$ref_id = $row->ref_id;
}

$query = "DELETE FROM rbac_pa WHERE obj_id = '".$ref_id."'";
$res = $this->db->query($query);

// INSERT PERMISSIONS FOR MAIL OBJECT
$query = "INSERT INTO rbac_pa ".
         " VALUES('2','a:6:{i:0;s:1:\"1\";i:1;s:1:\"3\";i:2;s:2:\"11\";i:3;s:2:\"12\";i:4;s:1:\"2\";i:5;s:1:\"4\";}','".$ref_id."','".$ref_id."')";
$res = $this->db->query($query);

?>

<#37>
ALTER TABLE meta_data ADD column language VARCHAR(200) NOT NULL DEFAULT '';
ALTER TABLE meta_data ADD column keyword BLOB NOT NULL DEFAULT '';
ALTER TABLE meta_data ADD column description BLOB NOT NULL DEFAULT '';

<#38>
ALTER TABLE meta_data DROP column keyword;
CREATE TABLE meta_keyword(
	obj_id int(11) NOT NULL,
	obj_type char(3) NOT NULL,
	language char(2) NOT NULL,
	keyword varchar(200) NOT NULL default ''
) TYPE=MyISAM;

<#39>
UPDATE object_data SET title='lm' WHERE title='le' AND type='typ';
UPDATE object_data SET type='lm' WHERE type='le';

<#40>
INSERT INTO object_data (type, title, description, owner, create_date, last_update) VALUES
	('typ', 'slm', 'SCORM Learning Module', -1, now(), now());

<#41>
<?php
$query = "SELECT * FROM object_data WHERE type='typ' AND title='slm'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$slm_id = $row->obj_id;

$query = "SELECT * FROM object_data WHERE type='typ' AND title='lm'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$lm_id = $row->obj_id;

$query = "SELECT * FROM rbac_ta WHERE typ_id='$lm_id'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('$slm_id','".$row->ops_id."')";
	$this->db->query($query);
}
?>

<#42>
UPDATE rbac_templates SET type='lm' WHERE type='le';

<#43>
<?php

$query = "SELECT * FROM rbac_templates WHERE type='lm'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO rbac_templates (rol_id, type, ops_id, parent) ".
		"VALUES ('".$row->rol_id."','slm','".$row->ops_id."','".$row->parent."')";
	$this->db->query($query);
}
?>


<#44>
# adding group tree table
DROP TABLE IF EXISTS grp_tree;

CREATE TABLE grp_tree (
  tree int(11) NOT NULL default '0',
  child int(11) unsigned NOT NULL default '0',
  parent int(11) unsigned NOT NULL default '0',
  lft int(11) unsigned NOT NULL default '0',
  rgt int(11) unsigned NOT NULL default '0',
  depth smallint(5) unsigned NOT NULL default '0',
  perm tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (tree,child,parent)
) TYPE=MyISAM;

<#45>
<?php
$q = "INSERT INTO object_data ".
	 "(type,title,description,owner,create_date,last_update) ".
	 "VALUES ".
	 "('rolt','grp_Admin_rolt','Administrator role template of groups',-1,now(),now())";
$this->db->query($q);

$q = "INSERT INTO rbac_fa (rol_id,parent,assign,parent_obj) ".
	 "VALUES (LAST_INSERT_ID(),8,'n',9)";
$this->db->query($q);


$ops = array();
$ops[0] = array("bm",5,6,3,2,4);//bm
$ops[1] = array("crs",5,6,3,2);//crs
$ops[2] = array("fold",5,6,3,2,4);//fold
$ops[3] = array("frm",5,6,3,2);//frm
$ops[4] = array("grp",6,3,2,4,1,8,7);//grp
$ops[5] = array("lm",5,6,3,2);//lm
$ops[6] = array("mob",5,6,3,2);//mob
$ops[7] = array("slm",5,6,3,2);//slm
$ops[8] = array("rolf",2,3,4);//rolf

foreach ($ops as $object)
{
	foreach ($object as $ops_id)
	{
		if(!is_string($ops_id))
		{
			$q = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent)".
				 "VALUES (LAST_INSERT_ID(),'".$object[0]."',".$ops_id.",8)";
			$this->db->query($q);

		}
	}
}

//create Member role template of groups
$q = "INSERT INTO object_data ".
	 "(type,title,description,owner,create_date,last_update) ".
	 "VALUES ".
	 "('rolt','grp_Member_rolt','Member role template of groups',-1,now(),now())";
$this->db->query($q);
/*
$q = "SELECT LAST_INSERT_ID()";
$rolt_id = $this->db->getRow($q);
*/
$q = "INSERT INTO rbac_fa (rol_id,parent,assign,parent_obj) ".
	 "VALUES (LAST_INSERT_ID(),8,'n',9)";
$this->db->query($q);

$ops = array();
$ops[0] = array("bm",5,6,3,2,4);//bm
$ops[1] = array("crs",5,6,3,2);//crs
$ops[2] = array("fold",5,6,3,2,4);//fold
$ops[3] = array("frm",5,6,3,2);//frm
$ops[4] = array("grp",3,2,4,8,7);//grp
$ops[5] = array("lm",5,6,3,2);//lm
$ops[5] = array("slm",5,6,3,2);//slm
$ops[7] = array("mob",5,6,3,2);//mob


foreach ($ops as $object)
{
	foreach ($object as $ops_id)
	{
		if(!is_string($ops_id))
		{
			$q = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent)".
				 "VALUES (LAST_INSERT_ID(),'".$object[0]."',".$ops_id.",8)";
			$this->db->query($q);

		}
	}
}
$q = "INSERT INTO object_data ".
	 "(type,title,description,owner,create_date,last_update) ".
	 "VALUES ".
	 "('rolt','grp_Status_closed','Group role template',-1,now(),now())";
$this->db->query($q);

$ops = array("grp",2);//grp
foreach ($ops as $ops_id)
{
	if(!is_string($ops_id))
	{
		$q = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent)".
			 "VALUES (LAST_INSERT_ID(),'".$ops[0]."',".$ops_id.",8)";
		$this->db->query($q);
	}
}

$q = "INSERT INTO object_data ".
	 "(type,title,description,owner,create_date,last_update) ".
	 "VALUES ".
	 "('rolt','grp_Status_open','Group role template',-1,now(),now())";
$this->db->query($q);

$ops = array("grp",2,7);//grp
foreach ($ops as $ops_id)
{
	if(!is_string($ops_id))
	{
		$q = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent)".
			 "VALUES (LAST_INSERT_ID(),'".$ops[0]."',".$ops_id.",8)";
		$this->db->query($q);
	}
}
?>

<#46>
CREATE TABLE media_object
(
	id int NOT NULL DEFAULT '0',
	mime varchar(100),
	width varchar(10),
	height varchar(10),
	file varchar(200),
	caption text,
	PRIMARY KEY (id)
);

<#47>
CREATE TABLE mob_parameter
(
	mob_id int NOT NULL,
	name varchar(50),
	value text,
	INDEX (mob_id)
);

<#48>
<?php
global $mySetup;

$db_name = $mySetup->getDbName();
$fields = mysql_list_fields($db_name, "usr_data");
$numfields = mysql_num_fields($fields);
$got_hobby = false;
for($i=0; $i < $numfields; $i++)
{
	$fname = mysql_field_name($fields, $i);
	if ($fname == "hobby")
	{
		$got_hobby = true;
	}
}
if(!$got_hobby)
{
	$q = "ALTER TABLE usr_data ADD COLUMN hobby TEXT NOT NULL DEFAULT ''";
	$this->db->query($q);
}

?>

<#49>
ALTER TABLE lm_page_object MODIFY COLUMN content LONGTEXT NOT NULL DEFAULT '';

<#50>
DROP TABLE IF EXISTS meta_technical;
CREATE TABLE meta_technical (
	tech_id int NOT NULL auto_increment,
	obj_id int(11) NOT NULL,
	obj_type char(3) NOT NULL,
	size varchar(50) NOT NULL default '',
	install_remarks TEXT NOT NULL default '',
	install_remarks_lang char(2) NOT NULL default '',
	other_requirements TEXT NOT NULL default '',
	other_requirements_lang char(2) NOT NULL default '',
	duration varchar(50) NOT NULL default '',
	PRIMARY KEY (tech_id),
	INDEX (obj_id, obj_type)
) TYPE=MyISAM;

<#51>
DROP TABLE IF EXISTS meta_techn_loc;
CREATE TABLE meta_techn_loc (
	tech_id int NOT NULL,
	location varchar(150),
	INDEX (tech_id)
);

<#52>
DROP TABLE IF EXISTS learning_module;
CREATE TABLE learning_module(
	id					int				NOT NULL	PRIMARY KEY,
	default_layout		varchar(100)	NOT NULL	DEFAULT 'toc2win'
);

<#53>
ALTER TABLE media_object DROP COLUMN mime;
ALTER TABLE media_object DROP COLUMN file;
ALTER TABLE media_object DROP COLUMN caption;
ALTER TABLE media_object ADD COLUMN halign ENUM('Left', 'Center', 'Right') NOT NULL DEFAULT 'Right';

<#54>
ALTER TABLE meta_technical ADD COLUMN format VARCHAR(200) NOT NULL DEFAULT '';

<#55>
ALTER TABLE learning_module ADD COLUMN stylesheet INT NOT NULL DEFAULT '0';

<#56>
DROP TABLE IF EXISTS style;
CREATE TABLE style (
	style_id	INT NOT NULL,
	tag			VARCHAR(100),
	class		VARCHAR(100),
	parameter	VARCHAR(100),
	value		VARCHAR(100)
);

<#57>
#adding permission settings for role templates
<?php
$query = "SELECT * FROM object_data WHERE type='typ' AND title='rolt'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$rolt_id = $row->obj_id;

$query = "SELECT * FROM object_data WHERE type='typ' AND title='role'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$role_id = $row->obj_id;

$query = "SELECT * FROM rbac_ta WHERE typ_id='$role_id'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('$rolt_id','".$row->ops_id."')";
	$this->db->query($query);
}

$query = "DELETE FROM rbac_templates WHERE type='rolt'";
$this->db->query($query);

$query = "SELECT * FROM rbac_templates WHERE type='role'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO rbac_templates (rol_id, type, ops_id, parent) ".
		"VALUES ('".$row->rol_id."','rolt','".$row->ops_id."','".$row->parent."')";
	$this->db->query($query);
}
?>

<#58>
DROP TABLE IF EXISTS style;
DROP TABLE IF EXISTS style_parameter;
CREATE TABLE style_parameter (
	id			INT AUTO_INCREMENT NOT NULL DEFAULT '0' PRIMARY KEY,
	style_id	INT NOT NULL,
	tag			VARCHAR(100),
	class		VARCHAR(100),
	parameter	VARCHAR(100),
	value		VARCHAR(100));

<#59>
# accommodating rbac tables. removing all obsolete entries
REPLACE INTO rbac_templates (rol_id , type, ops_id, parent) VALUES ('2','objf','4','8');
REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('24','4');
REPLACE INTO rbac_templates (rol_id , type, ops_id, parent) VALUES ('2','usrf','6','8');
REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('22','6');
DELETE FROM rbac_templates WHERE type IN ('usr','typ','rolt','lng','role','lo','uset','note','le','fold','bm');
DELETE FROM object_data WHERE type='typ' AND title='uset';
DELETE FROM rbac_ta WHERE typ_id IN ('12','26','27','29','30','31','32');
<?php
$query = "SELECT * FROM object_data WHERE type='typ' AND title='note'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$id = $row->obj_id;

$query = "DELETE FROM rbac_ta WHERE typ_id='$id'";
$this->db->query($query);
?>
# set only create permission for non rbac protected objects to admin role
REPLACE INTO rbac_templates (rol_id , type, ops_id, parent) VALUES ('2','role','5','8');
REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('30','5');
REPLACE INTO rbac_templates (rol_id , type, ops_id, parent) VALUES ('2','role','5','8');
REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('30','5');
REPLACE INTO rbac_templates (rol_id , type, ops_id, parent) VALUES ('2','rolt','5','8');
REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('27','5');
REPLACE INTO rbac_templates (rol_id , type, ops_id, parent) VALUES ('2','usr','5','8');
REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('12','5');
REPLACE INTO rbac_templates (rol_id , type, ops_id, parent) VALUES ('2','lo','5','8');
REPLACE INTO rbac_ta (typ_id, ops_id) VALUES ('31','5');
<#60>
ALTER TABLE grp_tree ADD COLUMN ref_id int(11);
<#61>
ALTER TABLE learning_module ADD COLUMN page_header
	ENUM ('st_title','pg_title','none') DEFAULT 'st_title';
<#62>
# remove obsolete columns in rbac tables
ALTER TABLE rbac_fa DROP parent_obj;
ALTER TABLE rbac_pa DROP set_id;
<#63>
# add primary key to rbac_ua (required for REPLACE statement)
ALTER TABLE rbac_ua ADD PRIMARY KEY (usr_id,rol_id);
<#64>
# add table for db-driven sessionmanagement
CREATE TABLE usr_session (
session_id VARCHAR(32) NOT NULL,
expires INT NOT NULL,
data TEXT NOT NULL,
ctime INT NOT NULL,
user_id INT(10) unsigned NOT NULL,
PRIMARY KEY (session_id),
INDEX (expires),
INDEX (user_id)
);

<#65>
ALTER TABLE meta_technical DROP COLUMN format;
ALTER TABLE meta_techn_loc ADD COLUMN nr int;

<#66>
DROP TABLE IF EXISTS meta_techn_format;
CREATE TABLE meta_techn_format (
	tech_id int NOT NULL,
	format varchar(150),
	nr int,
	INDEX (tech_id)
);

<#67>
DROP TABLE IF EXISTS media_object;
CREATE TABLE media_item
(
	id int NOT NULL DEFAULT '0',
	width VARCHAR(10),
	height VARCHAR(10),
	halign ENUM('Left', 'Center', 'Right', 'LeftFloat', 'RightFloat'),
	caption TEXT,
	PRIMARY KEY (id)
);

<#68>
ALTER TABLE mob_parameter CHANGE mob_id med_item_id INT NOT NULL;

<#69>
ALTER TABLE media_item ADD COLUMN nr INT NOT NULL;
ALTER TABLE media_item ADD COLUMN purpose ENUM ('Standard', 'Fullscreen', 'Additional');

<#70>
ALTER TABLE media_item ADD COLUMN mob_id INT NOT NULL;

<#71>
ALTER TABLE media_item MODIFY id INT NOT NULL DEFAULT '0' AUTO_INCREMENT;

<#72>
ALTER TABLE meta_techn_loc ADD COLUMN type ENUM ('LocalFile', 'Reference')
	NOT NULL DEFAULT 'LocalFile';
<#73>
<?php
$query = "SELECT value FROM settings WHERE keyword='setup_passwd'";
$res = $this->db->query($query);

if ($res->numRows())
{
	$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
	$encrypted_passwd = md5($row->value);

	$query = "DELETE FROM settings WHERE keyword='setup_passwd'";
	$this->db->query($query);

	$query = "INSERT INTO settings (keyword,value) VALUES ('setup_passwd','".$encrypted_passwd."')";
	$this->db->query($query);
}
?>
<#74>
CREATE TABLE `XmlNestedSet` (
  `ns_book_fk` int(11) NOT NULL default '0',
  `ns_type` char(50) NOT NULL default '',
  `ns_tag_fk` int(11) default NULL,
  `ns_l` int(11) default NULL,
  `ns_r` int(11) default NULL,
  KEY `ns_tag_fk` (`ns_tag_fk`),
  KEY `ns_l` (`ns_l`),
  KEY `ns_r` (`ns_r`),
  KEY `ns_book_fk` (`ns_book_fk`)
) TYPE=MyISAM;
CREATE TABLE `XmlParam` (
  `tag_fk` int(11) NOT NULL default '0',
  `param_name` char(50) NOT NULL default '',
  `param_value` char(255) NOT NULL default '',
  KEY `tag_fk` (`tag_fk`)
) TYPE=MyISAM;
CREATE TABLE `XmlTags` (
  `tag_pk` int(11) NOT NULL auto_increment,
  `tag_depth` int(11) NOT NULL default '0',
  `tag_name` char(50) NOT NULL default '',
  PRIMARY KEY  (`tag_pk`)
) TYPE=MyISAM;
CREATE TABLE `XmlValue` (
  `tag_value_pk` int(11) NOT NULL auto_increment,
  `tag_fk` int(11) NOT NULL default '0',
  `tag_value` text NOT NULL,
  PRIMARY KEY  (`tag_value_pk`),
  KEY `tag_fk` (`tag_fk`)
) TYPE=MyISAM;

INSERT INTO object_data (type,title,description,owner,create_date,last_update) VALUES ('typ','dbk','Digilib Book',-1,now(),now());
<?php
$query = "SELECT * FROM object_data WHERE type='typ' AND title='dbk'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$id = $row->obj_id;
$this->db->query("INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('$id','1') ");
$this->db->query("INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('$id','2') ");
$this->db->query("INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('$id','3') ");
$this->db->query("INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('$id','4') ");
$this->db->query("INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('$id','5') ");
$this->db->query("INSERT INTO rbac_ta (typ_id,ops_id) VALUES ('$id','6') ");
?>

<#75>
DROP TABLE IF EXISTS scorm_tree;
CREATE TABLE scorm_tree (
  slm_id int(11) NOT NULL default '0',
  child int(11) unsigned NOT NULL default '0',
  parent int(11) unsigned default NULL,
  lft int(11) unsigned NOT NULL default '0',
  rgt int(11) unsigned NOT NULL default '0',
  depth smallint(5) unsigned NOT NULL default '0',
  KEY child (child),
  KEY parent (parent)
) TYPE=MyISAM;

DROP TABLE IF EXISTS scorm_object;
CREATE TABLE scorm_object (
	obj_id int(11) NOT NULL auto_increment,
	title VARCHAR(200),
	type CHAR(3),
	slm_id INT NOT NULL,
	PRIMARY KEY  (obj_id)
) TYPE=MyISAM;

<#76>
CREATE TABLE sc_manifest (
	obj_id int(11) NOT NULL,
	import_id VARCHAR(200),
	version VARCHAR(200),
	xml_base VARCHAR(200),
	PRIMARY KEY  (obj_id)
) TYPE=MyISAM;

<#77>
CREATE TABLE sc_organizations (
	obj_id int(11) NOT NULL,
	default_organization VARCHAR(200),
	PRIMARY KEY  (obj_id)
) TYPE=MyISAM;

<#78>
CREATE TABLE sc_organization (
	obj_id int(11) NOT NULL,
	import_id VARCHAR(200),
	structure VARCHAR(200),
	PRIMARY KEY  (obj_id)
) TYPE=MyISAM;


<#79>
CREATE TABLE sc_item (
	obj_id int(11) NOT NULL,
	import_id VARCHAR(200),
	identifierref VARCHAR(200),
	isvisible ENUM('', 'true', 'false') DEFAULT '',
	parameters TEXT,
	prereq_type VARCHAR(200),
	prerequisites TEXT,
	maxtimeallowed VARCHAR(30),
	timelimitaction VARCHAR(30),
	datafromlms TEXT,
	masteryscore VARCHAR(200),
	PRIMARY KEY  (obj_id)
) TYPE=MyISAM;

<#80>
CREATE TABLE sc_resources (
	obj_id int(11) NOT NULL,
	xml_base VARCHAR(200),
	PRIMARY KEY  (obj_id)
) TYPE=MyISAM;

<#81>
CREATE TABLE sc_resource (
	obj_id int(11) NOT NULL,
	import_id VARCHAR(200),
	type VARCHAR(30),
	scormtype ENUM('sco', 'asset'),
	href VARCHAR(250),
	xml_base VARCHAR(200),
	PRIMARY KEY  (obj_id)
) TYPE=MyISAM;

<#82>
CREATE TABLE sc_resource_file (
	id INT NOT NULL AUTO_INCREMENT,
	res_id int(11),
	href TEXT,
	PRIMARY KEY  (id)
) TYPE=MyISAM;

<#83>
CREATE TABLE sc_resource_dependency (
	id INT NOT NULL AUTO_INCREMENT,
	res_id int(11),
	identifierref VARCHAR(200),
	PRIMARY KEY  (id)
) TYPE=MyISAM;

<#84>
ALTER TABLE sc_resource_file ADD COLUMN nr INT;
ALTER TABLE sc_resource_dependency ADD COLUMN nr INT;

<#85>
ALTER TABLE sc_resource CHANGE type resourcetype VARCHAR(30);

<#86>
CREATE TABLE `scorm_tracking` (
	`sc_item_id` int(11) NOT NULL default '0',
	`usr_id` int(11) NOT NULL default '0',
	`entry` enum('ab-initio','resume','') NOT NULL default 'ab-initio',
	`exit` enum('time-out','suspend','logout','') NOT NULL default 'time-out',
	`lesson_location` varchar(255) NOT NULL default '',
	`credit` enum('credit','no-credit') NOT NULL default 'credit',
	`raw` decimal(10,0) NOT NULL default '0',
	`session_time` time NOT NULL default '00:00:00',
	`total_time` time NOT NULL default '00:00:00',
	`comments` text NOT NULL,
	`lesson_status` enum('passed','completed','failed','incomplete','browsed','not attempted') NOT NULL default 'passed',
	`launch_data` text NOT NULL,
	`suspend_data` text NOT NULL,
	`mastery_score` decimal(10,0) NOT NULL default '0',
	PRIMARY KEY  (`sc_item_id`,`sc_item_id`,`usr_id`)
) TYPE=MyISAM;

<#87>
ALTER TABLE media_item ADD COLUMN location VARCHAR(200);
ALTER TABLE media_item ADD COLUMN location_type ENUM('LocalFile', 'Reference') NOT NULL DEFAULT 'LocalFile';
ALTER TABLE media_item ADD COLUMN format VARCHAR(200);

<#88>
ALTER TABLE lm_page_object CHANGE lm_id parent_id int;
ALTER TABLE lm_page_object ADD COLUMN parent_type CHAR(4) DEFAULT 'lm';

<#89>
DROP TABLE IF EXISTS role_data;
CREATE TABLE role_data (role_id int(11) NOT NULL default '0',
allow_register tinyint(1) unsigned NOT NULL default '0',
PRIMARY KEY  (role_id)) TYPE=MyISAM;
<?php
$query = "SELECT DISTINCT rol_id FROM rbac_fa WHERE assign='y'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO role_data (role_id,allow_register) VALUES ('".$row->rol_id."','0')";
	$this->db->query($query);
}
?>
<#90>
DELETE FROM settings WHERE keyword='institution';
DELETE FROM settings WHERE keyword='city';
DELETE FROM settings WHERE keyword='country';
DELETE FROM settings WHERE keyword='email';
DELETE FROM settings WHERE keyword='errors';
DELETE FROM settings WHERE keyword='feedback';
DELETE FROM settings WHERE keyword='phone';
DELETE FROM settings WHERE keyword='street';
DELETE FROM settings WHERE keyword='zipcode';

<#91>
<?php
$q = "INSERT INTO object_data ".
	 "(type,title,description,owner,create_date,last_update) ".
	 "VALUES ".
	 "('rolt','lm_Author_rolt','Author role template of learning modules',-1,now(),now())";
$this->db->query($q);

$q = "INSERT INTO rbac_fa (rol_id,parent,assign) ".
	 "VALUES (LAST_INSERT_ID(),8,'n')";
$this->db->query($q);


$ops = array();
$ops[4] = array("lm",6,3,2,4,1,8,7);//lm
$ops[8] = array("rolf",2,3,4);//rolf

foreach ($ops as $object)
{
	foreach ($object as $ops_id)
	{
		if(!is_string($ops_id))
		{
			$q = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent)".
				 "VALUES (LAST_INSERT_ID(),'".$object[0]."',".$ops_id.",8)";
			$this->db->query($q);

		}
	}
}
?>

<#92>
<?php

$q = "SELECT * FROM object_data WHERE title='lm_Author_rolt'";
$set = $this->db->query($q);
$row = $set->fetchRow(DB_FETCHMODE_ASSOC);

$q = "DELETE FROM rbac_fa WHERE rol_id ='".$row["obj_id"]."'";
$this->db->query($q);

$q = "DELETE FROM rbac_templates WHERE rol_id ='".$row["obj_id"]."'";
$this->db->query($q);

$q = "DELETE FROM object_data WHERE title='lm_Author_rolt'";
$this->db->query($q);

?>

<#93>
INSERT INTO object_data (type, title, description, owner, create_date, last_update) VALUES
	('typ', 'glo', 'Glossary', -1, now(), now());

<#94>
<?php
$query = "SELECT * FROM object_data WHERE type='typ' AND title='glo'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$glo_id = $row->obj_id;

$query = "SELECT * FROM object_data WHERE type='typ' AND title='lm'";
$res = $this->db->query($query);
$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
$lm_id = $row->obj_id;

$query = "SELECT * FROM rbac_ta WHERE typ_id='$lm_id'";
$res = $this->db->query($query);
while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ('$glo_id','".$row->ops_id."')";
	$this->db->query($query);
}
?>

<#95>
<?php
$q = "SELECT * FROM rbac_templates WHERE type = 'lm'";
$t_set = $this->db->query($q);
while($t_rec = $t_set->fetchRow(DB_FETCHMODE_ASSOC))
{
	$q = "INSERT INTO rbac_templates (rol_id,type,ops_id,parent) VALUES".
		" ('".$t_rec["rol_id"]."','glo','".$t_rec["ops_id"].
		"','".$t_rec["parent"]."')";
	$this->db->query($q);
}
?>

<#96>
<?php
$q = "SELECT usr_id FROM usr_data WHERE login='anonymous' AND passwd='".md5("anonymous")."'";
$res = $this->db->query($q);
if ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
{
	$user_id = $row->obj_id;
	
	$q = "INSERT INTO rbac_ua ".
		 "(usr_id,rol_id,default_role) ".
		 "VALUES ".
		 "(".$user_id.",".$role_id.",'y')";
	$this->db->query($q);

	$q = "INSERT INTO settings ".
		 "(keyword,value) ".
		 "VALUES ".
		 "('anonymous_user_id','".$user_id."')";
	$this->db->query($q);	
}
else
{
	$q = "INSERT INTO object_data ".
		 "(type,title,description,owner,create_date,last_update) ".
		 "VALUES ".
		 "('usr','Anonymous','Anonymous user account. DO NOT DELETE!',-1,now(),now())";
	$this->db->query($q);

	$res = $this->db->query("SELECT LAST_INSERT_ID()");
	$row = $res->fetchRow();
	$user_id = $row[0];

	$q = "INSERT INTO usr_data ".
		 "(usr_id,login,passwd,firstname,lastname,gender,email,last_update,create_date) ".
		 "VALUES ".
		 "(".$user_id.",'anonymous','".md5("anonymous")."','anonymous','anonymous','m','nomail',now(),now())";
	$this->db->query($q);

	$q = "INSERT INTO rbac_ua ".
		 "(usr_id,rol_id,default_role) ".
		 "VALUES ".
		 "(".$user_id.",".$role_id.",'y')";
	$this->db->query($q);
	
	$q = "INSERT INTO settings ".
		 "(keyword,value) ".
		 "VALUES ".
		 "('anonymous_user_id',".$user_id.")";
	$this->db->query($q);
}
?>
