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