# phpMyAdmin MySQL-Dump
# version 2.2.6-rc1
# http://phpwizard.net/phpMyAdmin/
# http://phpmyadmin.sourceforge.net/ (download page)
#
# Host: localhost
# Generation Time: Jul 30, 2002 at 03:15 PM
# Server version: 3.23.44
# PHP Version: 4.2.2
# Database : `uni-koeln_ilias3f_smeyer`
# --------------------------------------------------------

#
# Table structure for table `object_data`
#

CREATE TABLE object_data (
  obj_id int(11) NOT NULL auto_increment,
  type enum('role','user','le','frm','grp','cat','kurs','file','mail','abo','set','adm','none','usrf','rolf','objf','type') NOT NULL default 'none',
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

INSERT INTO object_data VALUES (2, 'role', 'Adminstrator', 'Rolle des Systemadministrators (darf alles)', -1, '2002-01-16 15:31:45', '2002-01-16 15:32:49');
INSERT INTO object_data VALUES (3, 'role', 'Autor', 'Rolle mit umfassenden Schreibrechten', -1, '2002-01-16 15:32:50', '2002-01-16 15:33:54');
INSERT INTO object_data VALUES (4, 'role', 'Lerner', 'Rolle für Studierende (wenig Schreibrechte)', -1, '2002-01-16 15:34:00', '2002-01-16 15:34:35');
INSERT INTO object_data VALUES (5, 'role', 'Gast', 'Gastzugang mit wenig Leserechten', -1, '2002-01-16 15:34:46', '2002-01-16 15:35:19');
INSERT INTO object_data VALUES (6, 'user', 'Meister Ad Min', 'nix', -1, '2002-01-16 16:09:22', '2002-01-16 16:09:22');
INSERT INTO object_data VALUES (7, 'usrf', 'User Folder', 'Folder der alle User enthält', -1, '2002-06-27 09:24:06', '2002-06-27 09:24:06');
INSERT INTO object_data VALUES (8, 'rolf', 'Role Folder', 'Folder der alle System Rollen enthält', -1, '2002-06-27 09:24:06', '2002-06-27 09:24:06');
INSERT INTO object_data VALUES (1, 'cat', 'root', 'Root Kategorie', -1, '2002-06-24 15:15:03', '2002-06-24 15:15:03');
INSERT INTO object_data VALUES (140, 'user', 'Lerner', 'nix', 6, '2002-07-11 10:28:13', '2002-07-11 10:28:13');
INSERT INTO object_data VALUES (141, 'user', 'Autor', 'nix', 6, '2002-07-11 10:28:34', '2002-07-11 10:28:34');
INSERT INTO object_data VALUES (142, 'user', 'Gast', 'nix', 6, '2002-07-11 10:28:54', '2002-07-11 10:28:54');
INSERT INTO object_data VALUES (10, 'objf', 'Object Folder', 'Contains list of known object types', -1, '2002-07-15 12:36:56', '2002-07-15 12:36:56');
INSERT INTO object_data VALUES (9, 'adm', 'System Settings', 'Contains systems settings', -1, '2002-07-15 12:37:33', '2002-07-15 12:37:33');
INSERT INTO object_data VALUES (11, 'type', 'role', 'Role object', -1, '2002-07-15 15:52:51', '2002-07-15 15:52:51');
INSERT INTO object_data VALUES (12, 'type', 'user', 'User object', -1, '2002-07-15 15:53:37', '2002-07-15 15:53:37');
INSERT INTO object_data VALUES (13, 'type', 'le', 'Learning object', -1, '2002-07-15 15:54:04', '2002-07-15 15:54:04');
INSERT INTO object_data VALUES (14, 'type', 'frm', 'Forum object', -1, '2002-07-15 15:54:22', '2002-07-15 15:54:22');
INSERT INTO object_data VALUES (15, 'type', 'grp', 'Group object', -1, '2002-07-15 15:54:37', '2002-07-15 15:54:37');
INSERT INTO object_data VALUES (16, 'type', 'cat', 'Category object', -1, '2002-07-15 15:54:54', '2002-07-15 15:54:54');
INSERT INTO object_data VALUES (17, 'type', 'crs', 'Course object', -1, '2002-07-15 15:55:08', '2002-07-15 15:55:08');
INSERT INTO object_data VALUES (18, 'type', 'file', 'FileSharing object', -1, '2002-07-15 15:55:31', '2002-07-15 15:55:31');
INSERT INTO object_data VALUES (19, 'type', 'mail', 'Mailmodule object', -1, '2002-07-15 15:55:49', '2002-07-15 15:55:49');
INSERT INTO object_data VALUES (20, 'type', 'abo', 'Subscription/Membership object', -1, '2002-07-15 15:56:11', '2002-07-15 15:56:11');
INSERT INTO object_data VALUES (21, 'type', 'adm', 'Administration Panel object', -1, '2002-07-15 15:56:38', '2002-07-15 15:56:38');
INSERT INTO object_data VALUES (22, 'type', 'usrf', 'User Folder object', -1, '2002-07-15 15:56:52', '2002-07-15 15:56:52');
INSERT INTO object_data VALUES (23, 'type', 'rolf', 'Role Folder object', -1, '2002-07-15 15:57:06', '2002-07-15 15:57:06');
INSERT INTO object_data VALUES (24, 'type', 'objf', 'Object-Type Folder object', -1, '2002-07-15 15:57:17', '2002-07-15 15:57:17');
INSERT INTO object_data VALUES (25, 'type', 'set', 'Set object', -1, '2002-07-15 15:57:57', '2002-07-15 15:57:57');
INSERT INTO object_data VALUES (26, 'type', 'type', 'Object Type Definition object', -1, '2002-07-15 15:58:16', '2002-07-15 15:58:16');
INSERT INTO object_data VALUES (150, 'grp', 'closed', 'Closed Group', 6, '2002-07-22 16:25:54', '2002-07-22 16:25:54');
INSERT INTO object_data VALUES (149, 'grp', 'open', '', 6, '2002-07-22 16:25:37', '2002-07-22 16:25:37');
INSERT INTO object_data VALUES (148, 'cat', 'Uni Köln', '', 6, '2002-07-22 16:25:15', '2002-07-22 16:25:15');
INSERT INTO object_data VALUES (151, 'le', 'secret', '', 6, '2002-07-22 16:26:17', '2002-07-22 16:26:17');
INSERT INTO object_data VALUES (152, 'rolf', 'Role Folder', 'Automatisch genierter Role Folder', 6, '2002-07-22 16:26:51', '2002-07-22 16:26:51');
# --------------------------------------------------------

#
# Table structure for table `object_types`
#

CREATE TABLE object_types (
  typ_id tinyint(4) unsigned NOT NULL auto_increment,
  type char(4) NOT NULL default '',
  class enum('y','n') NOT NULL default 'n',
  title char(30) NOT NULL default '',
  description char(128) NOT NULL default '',
  PRIMARY KEY  (typ_id)
) TYPE=MyISAM;

#
# Dumping data for table `object_types`
#

INSERT INTO object_types VALUES (11, 'role', 'n', 'Rolle', 'Rollenobjekt');
INSERT INTO object_types VALUES (21, 'adm', 'n', 'Administration', 'Contains all system settings');
INSERT INTO object_types VALUES (13, 'le', 'y', 'Lerneinheit', 'Objekt erzeugt eine Lerneinheit');
INSERT INTO object_types VALUES (14, 'frm', 'y', 'Forum', 'Objekt erzeugt ein Forum');
INSERT INTO object_types VALUES (15, 'grp', 'y', 'Arbeitsgruppe', 'Objekt erzeugt eine Arbeitsgruppe');
INSERT INTO object_types VALUES (16, 'cat', 'y', 'Kategorie', 'Erzeugt ein Kategorienobjekt');
INSERT INTO object_types VALUES (18, 'file', 'y', 'File Sharing', 'Erzeugt ein File Sharing Objekt');
INSERT INTO object_types VALUES (20, 'abo', 'n', 'Abonnentengruppe', 'erzeugt einen Abo-Set');
INSERT INTO object_types VALUES (17, 'kurs', 'y', 'Kurs', 'erzeugt ein Kurs Objekt');
INSERT INTO object_types VALUES (25, 'set', 'n', 'Set', 'Container für alles mögliche');
INSERT INTO object_types VALUES (12, 'user', 'n', 'Benutzer', 'Ein normales Personenobjekt');
INSERT INTO object_types VALUES (22, 'usrf', 'y', 'User Folder', 'Folder der alle User enthält');
INSERT INTO object_types VALUES (23, 'rolf', 'y', 'Role Folder', 'Folder der Rollen enthält');
INSERT INTO object_types VALUES (24, 'objf', 'y', 'Type folder', 'Contains all object type definitions');
INSERT INTO object_types VALUES (26, 'type', 'n', 'Object type', 'Defines an object type');
# --------------------------------------------------------

#
# Table structure for table `rbac_fa`
#

CREATE TABLE rbac_fa (
  rol_id int(11) NOT NULL default '0',
  parent int(11) NOT NULL default '0',
  PRIMARY KEY  (rol_id,parent)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_fa`
#

INSERT INTO rbac_fa VALUES (2, 8);
INSERT INTO rbac_fa VALUES (3, 8);
INSERT INTO rbac_fa VALUES (3, 152);
INSERT INTO rbac_fa VALUES (4, 8);
INSERT INTO rbac_fa VALUES (4, 152);
INSERT INTO rbac_fa VALUES (5, 8);
INSERT INTO rbac_fa VALUES (5, 152);
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

INSERT INTO rbac_operations VALUES (1, 'edit permission', 'edit permissions');
INSERT INTO rbac_operations VALUES (2, 'visible', 'view object');
INSERT INTO rbac_operations VALUES (3, 'read', 'access object');
INSERT INTO rbac_operations VALUES (4, 'write', 'modify object');
INSERT INTO rbac_operations VALUES (5, 'create', 'add object');
INSERT INTO rbac_operations VALUES (6, 'delete', 'remove object');
INSERT INTO rbac_operations VALUES (7, 'join', 'join group');
INSERT INTO rbac_operations VALUES (8, 'leave', 'leave group');
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

INSERT INTO rbac_pa VALUES (2, 'a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";}', 1, 0);
INSERT INTO rbac_pa VALUES (3, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 1, 0);
INSERT INTO rbac_pa VALUES (5, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 1, 0);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 1, 0);
INSERT INTO rbac_pa VALUES (2, 'a:4:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";}', 7, 9);
INSERT INTO rbac_pa VALUES (3, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 7, 9);
INSERT INTO rbac_pa VALUES (5, 'a:1:{i:0;s:1:"2";}', 7, 9);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 7, 9);
INSERT INTO rbac_pa VALUES (2, 'a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";}', 8, 9);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 150, 148);
INSERT INTO rbac_pa VALUES (3, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 8, 9);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 8, 9);
INSERT INTO rbac_pa VALUES (5, 'a:1:{i:0;s:1:"2";}', 8, 9);
INSERT INTO rbac_pa VALUES (2, 'a:4:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";}', 9, 1);
INSERT INTO rbac_pa VALUES (3, 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', 9, 1);
INSERT INTO rbac_pa VALUES (4, 'a:1:{i:0;s:1:"1";}', 9, 1);
INSERT INTO rbac_pa VALUES (5, 'N;', 9, 1);
INSERT INTO rbac_pa VALUES (2, 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', 10, 9);
INSERT INTO rbac_pa VALUES (3, 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', 10, 9);
INSERT INTO rbac_pa VALUES (4, 'a:1:{i:0;s:1:"1";}', 10, 9);
INSERT INTO rbac_pa VALUES (5, 'N;', 10, 9);
INSERT INTO rbac_pa VALUES (3, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 150, 148);
INSERT INTO rbac_pa VALUES (5, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 149, 148);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 149, 148);
INSERT INTO rbac_pa VALUES (3, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 149, 148);
INSERT INTO rbac_pa VALUES (5, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 148, 1);
INSERT INTO rbac_pa VALUES (2, 'a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";}', 148, 1);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 148, 1);
INSERT INTO rbac_pa VALUES (3, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 148, 1);
INSERT INTO rbac_pa VALUES (2, 'a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";}', 151, 150);
INSERT INTO rbac_pa VALUES (3, 'a:3:{i:0;s:1:"2";i:1;s:1:"3";i:2;s:1:"4";}', 151, 150);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 151, 150);
INSERT INTO rbac_pa VALUES (5, 'a:1:{i:0;s:1:"2";}', 151, 150);
INSERT INTO rbac_pa VALUES (5, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 150, 148);
INSERT INTO rbac_pa VALUES (2, 'a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";}', 152, 150);
INSERT INTO rbac_pa VALUES (3, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 152, 150);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"2";i:1;s:1:"3";}', 152, 150);
INSERT INTO rbac_pa VALUES (5, 'a:1:{i:0;s:1:"2";}', 152, 150);
INSERT INTO rbac_pa VALUES (2, 'a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";}', 150, 148);
INSERT INTO rbac_pa VALUES (2, 'a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";}', 149, 148);
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

INSERT INTO rbac_ta VALUES (11, 2);
INSERT INTO rbac_ta VALUES (11, 3);
INSERT INTO rbac_ta VALUES (11, 4);
INSERT INTO rbac_ta VALUES (11, 5);
INSERT INTO rbac_ta VALUES (11, 6);
INSERT INTO rbac_ta VALUES (12, 2);
INSERT INTO rbac_ta VALUES (12, 3);
INSERT INTO rbac_ta VALUES (12, 4);
INSERT INTO rbac_ta VALUES (12, 5);
INSERT INTO rbac_ta VALUES (12, 6);
INSERT INTO rbac_ta VALUES (13, 1);
INSERT INTO rbac_ta VALUES (13, 2);
INSERT INTO rbac_ta VALUES (13, 3);
INSERT INTO rbac_ta VALUES (13, 4);
INSERT INTO rbac_ta VALUES (13, 5);
INSERT INTO rbac_ta VALUES (13, 6);
INSERT INTO rbac_ta VALUES (14, 1);
INSERT INTO rbac_ta VALUES (14, 2);
INSERT INTO rbac_ta VALUES (14, 3);
INSERT INTO rbac_ta VALUES (14, 4);
INSERT INTO rbac_ta VALUES (14, 5);
INSERT INTO rbac_ta VALUES (14, 6);
INSERT INTO rbac_ta VALUES (15, 1);
INSERT INTO rbac_ta VALUES (15, 2);
INSERT INTO rbac_ta VALUES (15, 3);
INSERT INTO rbac_ta VALUES (15, 4);
INSERT INTO rbac_ta VALUES (15, 5);
INSERT INTO rbac_ta VALUES (15, 6);
INSERT INTO rbac_ta VALUES (16, 1);
INSERT INTO rbac_ta VALUES (16, 2);
INSERT INTO rbac_ta VALUES (16, 3);
INSERT INTO rbac_ta VALUES (16, 4);
INSERT INTO rbac_ta VALUES (16, 5);
INSERT INTO rbac_ta VALUES (16, 6);
INSERT INTO rbac_ta VALUES (17, 1);
INSERT INTO rbac_ta VALUES (17, 2);
INSERT INTO rbac_ta VALUES (17, 3);
INSERT INTO rbac_ta VALUES (17, 4);
INSERT INTO rbac_ta VALUES (18, 1);
INSERT INTO rbac_ta VALUES (18, 2);
INSERT INTO rbac_ta VALUES (18, 3);
INSERT INTO rbac_ta VALUES (18, 4);
INSERT INTO rbac_ta VALUES (19, 1);
INSERT INTO rbac_ta VALUES (19, 2);
INSERT INTO rbac_ta VALUES (19, 3);
INSERT INTO rbac_ta VALUES (20, 1);
INSERT INTO rbac_ta VALUES (20, 2);
INSERT INTO rbac_ta VALUES (20, 3);
INSERT INTO rbac_ta VALUES (21, 1);
INSERT INTO rbac_ta VALUES (21, 2);
INSERT INTO rbac_ta VALUES (21, 3);
INSERT INTO rbac_ta VALUES (21, 4);
INSERT INTO rbac_ta VALUES (21, 5);
INSERT INTO rbac_ta VALUES (22, 1);
INSERT INTO rbac_ta VALUES (22, 2);
INSERT INTO rbac_ta VALUES (22, 3);
INSERT INTO rbac_ta VALUES (22, 4);
INSERT INTO rbac_ta VALUES (23, 1);
INSERT INTO rbac_ta VALUES (23, 2);
INSERT INTO rbac_ta VALUES (23, 3);
INSERT INTO rbac_ta VALUES (23, 4);
INSERT INTO rbac_ta VALUES (23, 5);
INSERT INTO rbac_ta VALUES (23, 6);
INSERT INTO rbac_ta VALUES (24, 1);
INSERT INTO rbac_ta VALUES (24, 2);
INSERT INTO rbac_ta VALUES (24, 3);
INSERT INTO rbac_ta VALUES (25, 1);
INSERT INTO rbac_ta VALUES (25, 2);
INSERT INTO rbac_ta VALUES (25, 3);
INSERT INTO rbac_ta VALUES (26, 1);
INSERT INTO rbac_ta VALUES (26, 2);
INSERT INTO rbac_ta VALUES (26, 3);
INSERT INTO rbac_ta VALUES (26, 4);
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

INSERT INTO rbac_templates VALUES (5, 'usrf', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'usrf', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'usrf', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'usrf', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'role', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'role', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'role', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'role', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'usrf', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'usrf', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'usrf', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'usrf', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'type', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'type', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'type', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'type', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'rolf', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'rolf', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'usrf', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'rolf', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'rolf', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'le', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'le', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'le', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'grp', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'grp', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'frm', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'frm', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'frm', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'cat', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'cat', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'rolf', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'rolf', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'le', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'le', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'grp', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'grp', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'frm', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'frm', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'cat', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'cat', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'rolf', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'le', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'grp', 3, 8);
INSERT INTO rbac_templates VALUES (5, 'grp', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'frm', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'cat', 3, 8);
INSERT INTO rbac_templates VALUES (5, 'cat', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'rolf', 2, 152);
INSERT INTO rbac_templates VALUES (3, 'usrf', 2, 152);
INSERT INTO rbac_templates VALUES (3, 'usrf', 1, 152);
INSERT INTO rbac_templates VALUES (3, 'le', 2, 152);
INSERT INTO rbac_templates VALUES (3, 'le', 1, 152);
INSERT INTO rbac_templates VALUES (3, 'grp', 2, 152);
INSERT INTO rbac_templates VALUES (3, 'grp', 1, 152);
INSERT INTO rbac_templates VALUES (3, 'frm', 2, 152);
INSERT INTO rbac_templates VALUES (3, 'frm', 1, 152);
INSERT INTO rbac_templates VALUES (3, 'cat', 2, 152);
INSERT INTO rbac_templates VALUES (3, 'cat', 1, 152);
INSERT INTO rbac_templates VALUES (4, 'usrf', 2, 152);
INSERT INTO rbac_templates VALUES (4, 'usrf', 1, 152);
INSERT INTO rbac_templates VALUES (4, 'le', 1, 152);
INSERT INTO rbac_templates VALUES (4, 'grp', 2, 152);
INSERT INTO rbac_templates VALUES (4, 'grp', 1, 152);
INSERT INTO rbac_templates VALUES (4, 'frm', 1, 152);
INSERT INTO rbac_templates VALUES (4, 'cat', 2, 152);
INSERT INTO rbac_templates VALUES (4, 'cat', 1, 152);
INSERT INTO rbac_templates VALUES (5, 'usrf', 1, 152);
INSERT INTO rbac_templates VALUES (5, 'le', 1, 152);
INSERT INTO rbac_templates VALUES (5, 'grp', 1, 152);
INSERT INTO rbac_templates VALUES (5, 'frm', 1, 152);
INSERT INTO rbac_templates VALUES (5, 'cat', 1, 152);
INSERT INTO rbac_templates VALUES (2, 'rolf', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'rolf', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'rolf', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'objf', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'objf', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'adm', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'adm', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'adm', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'adm', 1, 8);
# --------------------------------------------------------

#
# Table structure for table `rbac_ua`
#

CREATE TABLE rbac_ua (
  usr_id int(11) NOT NULL default '0',
  rol_id int(11) NOT NULL default '0'
) TYPE=MyISAM;

#
# Dumping data for table `rbac_ua`
#

INSERT INTO rbac_ua VALUES (140, 4);
INSERT INTO rbac_ua VALUES (141, 3);
INSERT INTO rbac_ua VALUES (140, 5);
INSERT INTO rbac_ua VALUES (141, 5);
INSERT INTO rbac_ua VALUES (142, 5);
INSERT INTO rbac_ua VALUES (6, 4);
INSERT INTO rbac_ua VALUES (141, 4);
INSERT INTO rbac_ua VALUES (6, 5);
INSERT INTO rbac_ua VALUES (6, 2);
INSERT INTO rbac_ua VALUES (6, 3);
# --------------------------------------------------------

#
# Table structure for table `tree`
#

CREATE TABLE tree (
  tree smallint(6) NOT NULL default '0',
  child int(11) NOT NULL default '0',
  parent int(11) default NULL,
  lft int(11) NOT NULL default '0',
  rgt int(11) NOT NULL default '0'
) TYPE=MyISAM;

#
# Dumping data for table `tree`
#

INSERT INTO tree VALUES (1, 1, 0, 1, 20);
INSERT INTO tree VALUES (1, 7, 9, 13, 14);
INSERT INTO tree VALUES (1, 8, 9, 15, 16);
INSERT INTO tree VALUES (1, 9, 1, 12, 19);
INSERT INTO tree VALUES (1, 10, 9, 17, 18);
INSERT INTO tree VALUES (1, 150, 148, 3, 8);
INSERT INTO tree VALUES (1, 149, 148, 9, 10);
INSERT INTO tree VALUES (1, 148, 1, 2, 11);
INSERT INTO tree VALUES (1, 151, 150, 6, 7);
INSERT INTO tree VALUES (1, 152, 150, 4, 5);
# --------------------------------------------------------

#
# Table structure for table `user_data`
#

CREATE TABLE user_data (
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
# Dumping data for table `user_data`
#

INSERT INTO user_data VALUES (6, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'ad', 'min2', 'fd', 'm', 'a@b', '2002-05-15 14:56:41', '2002-05-22 13:08:18', '0000-00-00 00:00:00');
INSERT INTO user_data VALUES (140, 'lerner', '3c1c7de8baffc419327b6439bba34217', 'Lerner', '', '', 'm', '', '0000-00-00 00:00:00', '2002-07-11 10:28:13', '2002-07-11 10:28:13');
INSERT INTO user_data VALUES (141, 'autor', '7a25cefdc710b155828e91df70fe7478', 'Autor', '', '', 'm', '', '0000-00-00 00:00:00', '2002-07-11 10:28:34', '2002-07-11 10:28:34');
INSERT INTO user_data VALUES (142, 'gast', 'd4061b1486fe2da19dd578e8d970f7eb', 'Gast', '', '', 'm', '', '0000-00-00 00:00:00', '2002-07-11 10:28:54', '2002-07-11 10:28:54');
# --------------------------------------------------------

#
# Table structure for table `user_session`
#

CREATE TABLE user_session (
  sesskey varchar(32) NOT NULL default '',
  expiry int(11) NOT NULL default '0',
  value text NOT NULL,
  PRIMARY KEY  (sesskey)
) TYPE=MyISAM;

#
# Dumping data for table `user_session`
#


