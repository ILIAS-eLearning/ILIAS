# phpMyAdmin MySQL-Dump
# version 2.3.0
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Oct 16, 2002 at 08:42 AM
# Server version: 3.23.44
# PHP Version: 4.2.1
# Database : `ilias3`
# --------------------------------------------------------

#
# Table structure for table `fav_data`
#

CREATE TABLE fav_data (
  usr_fk int(11) NOT NULL default '0',
  id int(11) NOT NULL default '0',
  pos int(11) NOT NULL default '0',
  url varchar(255) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  folder varchar(255) NOT NULL default 'top',
  timest timestamp(14) NOT NULL,
  KEY usr_fk (usr_fk),
  KEY id (id),
  KEY pos (pos)
) TYPE=MyISAM;

#
# Dumping data for table `fav_data`
#

INSERT INTO fav_data (usr_fk, id, pos, url, name, folder, timest) VALUES (6, 1, 0, 'www.ilias.uni-koeln.de', 'ILIAS Uni-Köln', 'top', 20020813174241);
INSERT INTO fav_data (usr_fk, id, pos, url, name, folder, timest) VALUES (6, 2, 0, 'www.databay.de', 'Databay AG', 'top', 20020813174351);
# --------------------------------------------------------

#
# Table structure for table `lng_data`
#

CREATE TABLE lng_data (
  identifier varchar(30) NOT NULL default '',
  lang_key char(2) NOT NULL default '',
  value mediumblob NOT NULL,
  PRIMARY KEY  (identifier,lang_key,value(3))
) TYPE=MyISAM;

#
# Dumping data for table `lng_data`
#

# --------------------------------------------------------

#
# Table structure for table `mail`
#

CREATE TABLE mail (
  id int(11) NOT NULL auto_increment,
  snd int(11) NOT NULL default '0',
  rcp int(11) NOT NULL default '0',
  snd_flag tinyint(1) NOT NULL default '0',
  rcp_flag tinyint(1) NOT NULL default '0',
  rcp_folder varchar(50) NOT NULL default 'inbox',
  subject varchar(255) NOT NULL default '',
  body text NOT NULL,
  as_email tinyint(1) NOT NULL default '0',
  date_send datetime NOT NULL default '0000-00-00 00:00:00',
  timest timestamp(14) NOT NULL,
  UNIQUE KEY id (id)
) TYPE=MyISAM;

#
# Dumping data for table `mail`
#

# --------------------------------------------------------

#
# Table structure for table `object_data`
#

CREATE TABLE object_data (
  obj_id int(11) NOT NULL auto_increment,
  type char(4) NOT NULL default 'none',
  title char(70) NOT NULL default '',
  description char(128) default NULL,
  owner int(11) NOT NULL default '0',
  create_date datetime NOT NULL default '0000-00-00 00:00:00',
  last_update datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (obj_id)
) TYPE=MyISAM;

#
# Dumping data for table `object_data`
#

INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (2, 'role', 'Adminstrator', 'Role for systemadministrators. This role grants access to everything!', -1, '2002-01-16 15:31:45', '2002-01-16 15:32:49');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (3, 'role', 'Author', 'Role for teachers with many write & some create permissions.', -1, '2002-01-16 15:32:50', '2002-01-16 15:33:54');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (4, 'role', 'Learner', 'Typical role for students. Grants write access to some objects.', -1, '2002-01-16 15:34:00', '2002-01-16 15:34:35');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (5, 'role', 'Guest', 'Role grants only a few visible & read permissions.', -1, '2002-01-16 15:34:46', '2002-01-16 15:35:19');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (6, 'usr', 'The System Administrator', 'admin@yourserver.com', -1, '2002-01-16 16:09:22', '2002-08-30 13:54:04');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (7, 'usrf', 'Users', 'Folder contains all users', -1, '2002-06-27 09:24:06', '2002-06-27 09:24:06');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (8, 'rolf', 'Roles', 'Folder contains all roles', -1, '2002-06-27 09:24:06', '2002-06-27 09:24:06');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (1, 'cat', 'ILIAS open source', 'This is the root node of the system!!!', -1, '2002-06-24 15:15:03', '2002-06-24 15:15:03');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (10, 'objf', 'Objects', 'Folder contains list of known object types', -1, '2002-07-15 12:36:56', '2002-07-15 12:36:56');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (9, 'adm', 'System Settings', 'Folder contains the systems settings', -1, '2002-07-15 12:37:33', '2002-07-15 12:37:33');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (11, 'lngf', 'Languages', 'Folder contains all available languages', -1, '2002-07-15 15:52:51', '2002-07-15 15:52:51');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (12, 'typ', 'usr', 'User object', -1, '2002-07-15 15:53:37', '2002-07-15 15:53:37');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (13, 'typ', 'le', 'Learning module Object', -1, '2002-07-15 15:54:04', '2002-07-15 15:54:04');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (14, 'typ', 'frm', 'Forum object', -1, '2002-07-15 15:54:22', '2002-07-15 15:54:22');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (15, 'typ', 'grp', 'Group object', -1, '2002-07-15 15:54:37', '2002-07-15 15:54:37');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (16, 'typ', 'cat', 'Category object', -1, '2002-07-15 15:54:54', '2002-07-15 15:54:54');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (17, 'typ', 'crs', 'Course object', -1, '2002-07-15 15:55:08', '2002-07-15 15:55:08');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (18, 'typ', 'file', 'FileSharing object', -1, '2002-07-15 15:55:31', '2002-07-15 15:55:31');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (19, 'typ', 'mail', 'Mailmodule object', -1, '2002-07-15 15:55:49', '2002-07-15 15:55:49');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (21, 'typ', 'adm', 'Administration Panel object', -1, '2002-07-15 15:56:38', '2002-07-15 15:56:38');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (22, 'typ', 'usrf', 'User Folder object', -1, '2002-07-15 15:56:52', '2002-07-15 15:56:52');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (23, 'typ', 'rolf', 'Role Folder object', -1, '2002-07-15 15:57:06', '2002-07-15 15:57:06');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (24, 'typ', 'objf', 'Object-Type Folder object', -1, '2002-07-15 15:57:17', '2002-07-15 15:57:17');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (30, 'typ', 'role', 'Role Object', -1, '2002-08-30 10:21:37', '2002-08-30 10:21:37');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (26, 'typ', 'typ', 'Object Type Definition object', -1, '2002-07-15 15:58:16', '2002-07-15 15:58:16');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (27, 'typ', 'rolt', 'Role template object', -1, '2002-07-15 15:58:16', '2002-07-15 15:58:16');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (156, 'usr', 'T. Eacher', 'teacher@yourserver.com', 6, '2002-08-30 14:04:26', '2002-08-30 14:04:26');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (158, 'usr', 'N. Iemand', 'gast@yourserver.com', 6, '2002-08-30 14:05:39', '2002-08-30 14:05:39');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (157, 'usr', 'St. Udent', 'student@yourserver.com', 6, '2002-08-30 14:05:05', '2002-08-30 14:05:05');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (28, 'typ', 'lngf', 'Language Folder object', -1, '2002-08-28 14:22:01', '2002-08-28 14:22:01');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (31, 'typ', 'lo', 'Learning Object', -1, '2002-08-30 10:21:37', '2002-08-30 10:21:37');
INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) VALUES (29, 'typ', 'lng', 'Language object', -1, '2002-08-30 10:18:29', '2002-08-30 10:18:29');
# --------------------------------------------------------

#
# Table structure for table `rbac_fa`
#

CREATE TABLE rbac_fa (
  rol_id int(11) NOT NULL default '0',
  parent int(11) NOT NULL default '0',
  parent_obj int(11) NOT NULL default '0',
  assign enum('y','n') default NULL,
  PRIMARY KEY  (rol_id,parent)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_fa`
#

INSERT INTO rbac_fa (rol_id, parent, parent_obj, assign) VALUES (2, 8, 9, 'y');
INSERT INTO rbac_fa (rol_id, parent, parent_obj, assign) VALUES (3, 8, 9, 'y');
INSERT INTO rbac_fa (rol_id, parent, parent_obj, assign) VALUES (4, 8, 9, 'y');
INSERT INTO rbac_fa (rol_id, parent, parent_obj, assign) VALUES (5, 8, 9, 'y');
# --------------------------------------------------------

#
# Table structure for table `rbac_operations`
#

CREATE TABLE rbac_operations (
  ops_id int(11) NOT NULL auto_increment,
  operation char(100) NOT NULL default '',
  description char(255) default NULL,
  PRIMARY KEY  (ops_id)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_operations`
#

INSERT INTO rbac_operations (ops_id, operation, description) VALUES (1, 'edit permission', 'edit permissions');
INSERT INTO rbac_operations (ops_id, operation, description) VALUES (2, 'visible', 'view object');
INSERT INTO rbac_operations (ops_id, operation, description) VALUES (3, 'read', 'access object');
INSERT INTO rbac_operations (ops_id, operation, description) VALUES (4, 'write', 'modify object');
INSERT INTO rbac_operations (ops_id, operation, description) VALUES (5, 'create', 'add object');
INSERT INTO rbac_operations (ops_id, operation, description) VALUES (6, 'delete', 'remove object');
INSERT INTO rbac_operations (ops_id, operation, description) VALUES (7, 'join', 'join/subscribe');
INSERT INTO rbac_operations (ops_id, operation, description) VALUES (8, 'leave', 'leave/unsubscribe');
# --------------------------------------------------------

#
# Table structure for table `rbac_pa`
#

CREATE TABLE rbac_pa (
  rol_id int(11) NOT NULL default '0',
  ops_id text NOT NULL,
  obj_id int(11) NOT NULL default '0',
  set_id int(11) NOT NULL default '0',
  PRIMARY KEY  (rol_id,obj_id,set_id)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_pa`
#

INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (2, 'a:6:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"3";i:4;s:1:"2";i:5;s:1:"4";}', 1, 0);
INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (3, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 1, 0);
INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (5, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1, 0);
INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1, 0);
INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (2, 'a:4:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 7, 9);
INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 8, 9);
INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (2, 'a:4:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 11, 9);
INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (3, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 8, 9);
INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (2, 'a:5:{i:0;s:1:"5";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"2";i:4;s:1:"4";}', 9, 1);
INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (2, 'a:6:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"3";i:4;s:1:"2";i:5;s:1:"4";}', 8, 9);
INSERT INTO rbac_pa (rol_id, ops_id, obj_id, set_id) VALUES (2, 'a:3:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"2";}', 10, 9);
# --------------------------------------------------------

#
# Table structure for table `rbac_ta`
#

CREATE TABLE rbac_ta (
  typ_id smallint(6) NOT NULL default '0',
  ops_id smallint(6) NOT NULL default '0',
  PRIMARY KEY  (typ_id,ops_id)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_ta`
#

INSERT INTO rbac_ta (typ_id, ops_id) VALUES (11, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (11, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (11, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (11, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (11, 6);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (12, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (12, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (12, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (12, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (12, 6);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (13, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (13, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (13, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (13, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (13, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (13, 6);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (13, 7);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (13, 8);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (14, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (14, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (14, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (14, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (14, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (14, 6);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (15, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (15, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (15, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (15, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (15, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (15, 6);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (15, 7);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (15, 8);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (16, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (16, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (16, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (16, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (16, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (16, 6);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (17, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (17, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (17, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (17, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (17, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (17, 6);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (17, 7);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (17, 8);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (18, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (18, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (18, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (18, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (19, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (19, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (19, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (19, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (19, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (19, 6);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (20, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (20, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (20, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (21, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (21, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (21, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (21, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (21, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (22, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (22, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (22, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (22, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (23, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (23, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (23, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (23, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (23, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (23, 6);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (24, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (24, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (24, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (25, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (25, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (25, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (26, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (26, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (26, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (26, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (28, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (28, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (28, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (28, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (29, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (29, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (29, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (29, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (29, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (29, 6);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (30, 1);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (30, 2);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (30, 3);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (30, 4);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (30, 5);
INSERT INTO rbac_ta (typ_id, ops_id) VALUES (30, 6);
# --------------------------------------------------------

#
# Table structure for table `rbac_templates`
#

CREATE TABLE rbac_templates (
  rol_id int(11) NOT NULL default '0',
  type char(5) NOT NULL default '',
  ops_id int(11) NOT NULL default '0',
  parent int(11) NOT NULL default '0'
) TYPE=MyISAM;

#
# Dumping data for table `rbac_templates`
#

INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'le', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'role', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'lang', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'lang', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'set', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'set', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'objf', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'objf', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'objf', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'lngf', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'lngf', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'lngf', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'lngf', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'file', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'file', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'file', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'file', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'abo', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'cat', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'cat', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'cat', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'mail', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'mail', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'mail', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'mail', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'mail', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'le', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'le', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'le', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'le', 8, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'le', 7, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'lang', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'frm', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'frm', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'frm', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'cat', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'cat', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'le', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'le', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'le', 8, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'le', 7, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'crs', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'grp', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'grp', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'frm', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'frm', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'cat', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'cat', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'rolf', 2, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'usrf', 2, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'usrf', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'le', 2, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'le', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'grp', 2, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'grp', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'frm', 2, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'frm', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'cat', 2, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'cat', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'usrf', 2, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'usrf', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'le', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'grp', 2, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'grp', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'frm', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'cat', 2, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'cat', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'usrf', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'le', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'grp', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'frm', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'cat', 1, 152);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'abo', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'abo', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'usr', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'usr', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'usr', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'usr', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'usr', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'rolf', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'rolf', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'rolf', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'rolf', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'rolf', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'rolf', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'role', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'role', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'role', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'role', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'role', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'role', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'mail', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'mail', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'mail', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'mail', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'mail', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'mail', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'le', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'le', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'le', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'le', 8, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'le', 7, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'le', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'le', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'le', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'lang', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'lang', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'lang', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'lang', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'lang', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'lang', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'grp', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'grp', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'grp', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'grp', 8, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'grp', 7, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'grp', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'grp', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'grp', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'frm', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'frm', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'frm', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'frm', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'frm', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'frm', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'crs', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'crs', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'crs', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'crs', 8, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'crs', 7, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'crs', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'crs', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'crs', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'cat', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'cat', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'cat', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'cat', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'cat', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'cat', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'adm', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'le', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'le', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'grp', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'grp', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'grp', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'grp', 8, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'grp', 7, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'grp', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'grp', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'frm', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'frm', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'frm', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'frm', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'crs', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'crs', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'crs', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'crs', 8, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'crs', 7, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'crs', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'crs', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'adm', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'adm', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'adm', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'adm', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'grp', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'grp', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'grp', 8, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'grp', 7, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'crs', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'crs', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'crs', 8, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'crs', 7, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'mail', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'mail', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'mail', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'mail', 6, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'mail', 5, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'set', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'typ', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'typ', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'typ', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'typ', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'usrf', 1, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'usrf', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'usrf', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (2, 'usrf', 4, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'lang', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'role', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'role', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'rolf', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'rolf', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'usr', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (3, 'usr', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'mail', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'role', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (5, 'usr', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'role', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'rolf', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'rolf', 2, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'usr', 3, 8);
INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (4, 'usr', 2, 8);
# --------------------------------------------------------

#
# Table structure for table `rbac_ua`
#

CREATE TABLE rbac_ua (
  usr_id int(11) NOT NULL default '0',
  rol_id int(11) NOT NULL default '0',
  PRIMARY KEY  (usr_id,rol_id)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_ua`
#

INSERT INTO rbac_ua (usr_id, rol_id) VALUES (6, 2);
INSERT INTO rbac_ua (usr_id, rol_id) VALUES (156, 3);
INSERT INTO rbac_ua (usr_id, rol_id) VALUES (157, 4);
INSERT INTO rbac_ua (usr_id, rol_id) VALUES (158, 5);
# --------------------------------------------------------

#
# Table structure for table `settings`
#

CREATE TABLE settings (
  keyword char(50) NOT NULL default '',
  value char(50) NOT NULL default '',
  PRIMARY KEY  (keyword,value)
) TYPE=MyISAM;

#
# Dumping data for table `settings`
#

INSERT INTO settings (keyword, value) VALUES ('admin_firstname', 'ghjg');
INSERT INTO settings (keyword, value) VALUES ('admin_lastname', 'dfsfs');
INSERT INTO settings (keyword, value) VALUES ('admin_position', '');
INSERT INTO settings (keyword, value) VALUES ('admin_title', '');
INSERT INTO settings (keyword, value) VALUES ('babylon_path', '');
INSERT INTO settings (keyword, value) VALUES ('city', 'gjjg');
INSERT INTO settings (keyword, value) VALUES ('convert_path', '');
INSERT INTO settings (keyword, value) VALUES ('country', 'hjgh');
INSERT INTO settings (keyword, value) VALUES ('crs_enable', '');
INSERT INTO settings (keyword, value) VALUES ('db_version', '1');
INSERT INTO settings (keyword, value) VALUES ('email', 'hjgj');
INSERT INTO settings (keyword, value) VALUES ('errors', '');
INSERT INTO settings (keyword, value) VALUES ('feedback', '');
INSERT INTO settings (keyword, value) VALUES ('group_file_sharing', '');
INSERT INTO settings (keyword, value) VALUES ('ilias_version', '3.0a');
INSERT INTO settings (keyword, value) VALUES ('institution', '');
INSERT INTO settings (keyword, value) VALUES ('inst_info', '');
INSERT INTO settings (keyword, value) VALUES ('inst_name', '');
INSERT INTO settings (keyword, value) VALUES ('java_path', '');
INSERT INTO settings (keyword, value) VALUES ('ldap_basedn', '');
INSERT INTO settings (keyword, value) VALUES ('ldap_enable', '');
INSERT INTO settings (keyword, value) VALUES ('ldap_port', '');
INSERT INTO settings (keyword, value) VALUES ('ldap_server', '');
INSERT INTO settings (keyword, value) VALUES ('news', '');
INSERT INTO settings (keyword, value) VALUES ('payment_system', '');
INSERT INTO settings (keyword, value) VALUES ('phone', 'hg');
INSERT INTO settings (keyword, value) VALUES ('pub_section', '');
INSERT INTO settings (keyword, value) VALUES ('street', 'gjhghj');
INSERT INTO settings (keyword, value) VALUES ('unzip_path', '');
INSERT INTO settings (keyword, value) VALUES ('zipcode', 'ghj');
INSERT INTO settings (keyword, value) VALUES ('zip_path', '');
# --------------------------------------------------------

#
# Table structure for table `tree`
#

CREATE TABLE tree (
  tree smallint(6) NOT NULL default '0',
  child int(11) NOT NULL default '0',
  parent int(11) default NULL,
  lft int(11) NOT NULL default '0',
  rgt int(11) NOT NULL default '0',
  depth int(11) NOT NULL default '0'
) TYPE=MyISAM;

#
# Dumping data for table `tree`
#

INSERT INTO tree (tree, child, parent, lft, rgt, depth) VALUES (1, 1, 0, 1, 12, 1);
INSERT INTO tree (tree, child, parent, lft, rgt, depth) VALUES (1, 7, 9, 3, 4, 3);
INSERT INTO tree (tree, child, parent, lft, rgt, depth) VALUES (1, 8, 9, 5, 6, 3);
INSERT INTO tree (tree, child, parent, lft, rgt, depth) VALUES (1, 9, 1, 2, 11, 2);
INSERT INTO tree (tree, child, parent, lft, rgt, depth) VALUES (1, 10, 9, 7, 8, 3);
INSERT INTO tree (tree, child, parent, lft, rgt, depth) VALUES (1, 11, 9, 9, 10, 3);
# --------------------------------------------------------

#
# Table structure for table `usr_data`
#

CREATE TABLE usr_data (
  usr_id int(11) NOT NULL default '0',
  login char(11) NOT NULL default '',
  passwd char(32) NOT NULL default '',
  firstname char(20) NOT NULL default '',
  surname char(30) NOT NULL default '',
  title char(20) default NULL,
  gender enum('m','f') NOT NULL default 'm',
  email char(40) NOT NULL default 'here your email',
  last_login datetime NOT NULL default '0000-00-00 00:00:00',
  last_update datetime NOT NULL default '0000-00-00 00:00:00',
  create_date datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (usr_id),
  KEY login (login,passwd)
) TYPE=MyISAM;

#
# Dumping data for table `usr_data`
#

INSERT INTO usr_data (usr_id, login, passwd, firstname, surname, title, gender, email, last_login, last_update, create_date) VALUES (6, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'System', 'Administrator', 'The', 'm', 'ilias@yourserver.com', '2002-05-15 14:56:41', '2002-05-22 13:08:18', '0000-00-00 00:00:00');
INSERT INTO usr_data (usr_id, login, passwd, firstname, surname, title, gender, email, last_login, last_update, create_date) VALUES (158, 'gast', 'd4061b1486fe2da19dd578e8d970f7eb', 'N.', 'Iemand', '', 'm', 'gast@yourserver.com', '0000-00-00 00:00:00', '2002-08-30 14:05:39', '2002-08-30 14:05:39');
INSERT INTO usr_data (usr_id, login, passwd, firstname, surname, title, gender, email, last_login, last_update, create_date) VALUES (157, 'lerner', '3c1c7de8baffc419327b6439bba34217', 'St.', 'Udent', '', 'm', 'student@yourserver.com', '0000-00-00 00:00:00', '2002-08-30 14:05:05', '2002-08-30 14:05:05');
INSERT INTO usr_data (usr_id, login, passwd, firstname, surname, title, gender, email, last_login, last_update, create_date) VALUES (156, 'autor', '7a25cefdc710b155828e91df70fe7478', 'T.', 'Eacher', '', 'm', 'teacher@yourserver.com', '0000-00-00 00:00:00', '2002-08-30 14:04:26', '2002-08-30 14:04:26');
# --------------------------------------------------------

#
# Table structure for table `usr_pref`
#

CREATE TABLE usr_pref (
  usr_id int(11) NOT NULL default '0',
  keyword char(40) NOT NULL default '',
  value char(40) default NULL,
  PRIMARY KEY  (usr_id,keyword)
) TYPE=MyISAM;

#
# Dumping data for table `usr_pref`
#

INSERT INTO usr_pref (usr_id, keyword, value) VALUES (6, 'language', 'de');
INSERT INTO usr_pref (usr_id, keyword, value) VALUES (6, 'skin', 'default');
INSERT INTO usr_pref (usr_id, keyword, value) VALUES (6, 'style_default', 'style');
INSERT INTO usr_pref (usr_id, keyword, value) VALUES (156, 'language', 'en');
INSERT INTO usr_pref (usr_id, keyword, value) VALUES (156, 'skin', 'default');
INSERT INTO usr_pref (usr_id, keyword, value) VALUES (156, 'style_default', 'style');
# --------------------------------------------------------

#
# Table structure for table `usr_session`
#

CREATE TABLE usr_session (
  sesskey varchar(32) NOT NULL default '',
  expiry int(11) NOT NULL default '0',
  value text NOT NULL,
  PRIMARY KEY  (sesskey)
) TYPE=MyISAM;

#
# Dumping data for table `usr_session`
#


