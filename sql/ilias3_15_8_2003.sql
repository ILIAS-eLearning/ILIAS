# phpMyAdmin MySQL-Dump
# version 2.3.3-rc1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Jan 06, 2003 at 04:34 PM
# Server version: 4.00.05
# PHP Version: 4.3.0
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

INSERT INTO fav_data VALUES (6, 1, 0, 'www.ilias.uni-koeln.de', 'ILIAS Uni-Köln', 'top', 20020813174241);
INSERT INTO fav_data VALUES (6, 2, 0, 'www.databay.de', 'Databay AG', 'top', 20020813174351);
# --------------------------------------------------------

#
# Table structure for table `frm_data`
#

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
  PRIMARY KEY  (top_pk)
) TYPE=MyISAM;

#
# Dumping data for table `frm_data`
#

INSERT INTO frm_data VALUES (1, 172, 'frm1 a', '', 0, 0, '', '6', '2002-12-24 01:01:08');
INSERT INTO frm_data VALUES (3, 178, 'frm2', '', 0, 0, '', '6', '2002-12-24 01:05:57');
# --------------------------------------------------------

#
# Table structure for table `frm_posts`
#

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

#
# Dumping data for table `frm_posts`
#

# --------------------------------------------------------

#
# Table structure for table `frm_posts_tree`
#

CREATE TABLE frm_posts_tree (
  node_id int(11) NOT NULL auto_increment,
  lo_id int(11) NOT NULL default '0',
  parent_node_id int(11) NOT NULL default '0',
  lft int(11) NOT NULL default '0',
  rgt int(11) NOT NULL default '0',
  node_type_id int(11) NOT NULL default '0',
  depth int(11) NOT NULL default '0',
  prev_sibling_node_id int(11) NOT NULL default '0',
  next_sibling_node_id int(11) NOT NULL default '0',
  first_child_node_id int(11) NOT NULL default '0',
  PRIMARY KEY  (node_id,lo_id)
) TYPE=MyISAM;

#
# Dumping data for table `frm_posts_tree`
#

# --------------------------------------------------------

#
# Table structure for table `frm_threads`
#

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
  PRIMARY KEY  (thr_pk)
) TYPE=MyISAM;

#
# Dumping data for table `frm_threads`
#

# --------------------------------------------------------

#
# Table structure for table `lng_data`
#

CREATE TABLE lng_data (
  module varchar(30) NOT NULL default '',
  identifier varchar(50) binary NOT NULL default '',
  lang_key char(2) NOT NULL default '',
  value blob NOT NULL,
  PRIMARY KEY  (module,identifier,lang_key),
  KEY module (module),
  KEY lang_key (lang_key)
) TYPE=MyISAM;

#
# Dumping data for table `lng_data`
#

INSERT INTO lng_data VALUES ('common', 'setup_welcome', 'en', 'Welcome to the setup of ILIAS.<br>To make Ilias operatable please fill out the following fields.<br>ILIAS will install the database with the given parameters after pressing <submit>.');
INSERT INTO lng_data VALUES ('common', 'setup_ready', 'en', 'setup is ready');
INSERT INTO lng_data VALUES ('common', 'setup', 'en', 'setup');
INSERT INTO lng_data VALUES ('common', 'set_online', 'en', 'set online');
INSERT INTO lng_data VALUES ('common', 'set_offline', 'en', 'set offline');
INSERT INTO lng_data VALUES ('common', 'set', 'en', 'set');
INSERT INTO lng_data VALUES ('common', 'server_software', 'en', 'server software');
INSERT INTO lng_data VALUES ('common', 'server', 'en', 'server');
INSERT INTO lng_data VALUES ('common', 'sequences', 'en', 'sequences');
INSERT INTO lng_data VALUES ('common', 'sequence', 'en', 'sequence');
INSERT INTO lng_data VALUES ('common', 'sent', 'en', 'sent');
INSERT INTO lng_data VALUES ('common', 'sender', 'en', 'sender');
INSERT INTO lng_data VALUES ('common', 'send', 'en', 'send');
INSERT INTO lng_data VALUES ('common', 'selected', 'en', 'selected');
INSERT INTO lng_data VALUES ('common', 'select_all', 'en', 'select all');
INSERT INTO lng_data VALUES ('common', 'sections', 'en', 'sections');
INSERT INTO lng_data VALUES ('common', 'search_recipient', 'en', 'search recipient');
INSERT INTO lng_data VALUES ('common', 'search_in', 'en', 'search in');
INSERT INTO lng_data VALUES ('common', 'search_cc_recipient', 'en', 'search cc recipient');
INSERT INTO lng_data VALUES ('common', 'search_bc_recipient', 'en', 'search bc recipient');
INSERT INTO lng_data VALUES ('common', 'search', 'en', 'search');
INSERT INTO lng_data VALUES ('common', 'saved_successfully', 'en', 'saved successfully');
INSERT INTO lng_data VALUES ('common', 'saved', 'en', 'saved');
INSERT INTO lng_data VALUES ('common', 'save_message', 'en', 'save message');
INSERT INTO lng_data VALUES ('common', 'save_and_back', 'en', 'save and back');
INSERT INTO lng_data VALUES ('common', 'save', 'en', 'save');
INSERT INTO lng_data VALUES ('common', 'salutation_m', 'en', 'Mr.');
INSERT INTO lng_data VALUES ('common', 'salutation_f', 'en', 'Ms./Mrs.');
INSERT INTO lng_data VALUES ('common', 'salutation', 'en', 'salutation');
INSERT INTO lng_data VALUES ('common', 'rights', 'en', 'rights');
INSERT INTO lng_data VALUES ('common', 'right', 'en', 'right');
INSERT INTO lng_data VALUES ('common', 'retype_password', 'en', 'retype password');
INSERT INTO lng_data VALUES ('common', 'reset', 'en', 'reset');
INSERT INTO lng_data VALUES ('common', 'reply', 'en', 'reply');
INSERT INTO lng_data VALUES ('common', 'refresh_list', 'en', 'refresh list');
INSERT INTO lng_data VALUES ('common', 'refresh', 'en', 'refresh');
INSERT INTO lng_data VALUES ('common', 'recipient', 'en', 'recipient');
INSERT INTO lng_data VALUES ('common', 'read', 'en', 'read');
INSERT INTO lng_data VALUES ('common', 'quit', 'en', 'quit');
INSERT INTO lng_data VALUES ('common', 'question', 'en', 'question');
INSERT INTO lng_data VALUES ('common', 'publishing_organisation', 'en', 'publishing organisation');
INSERT INTO lng_data VALUES ('common', 'published', 'en', 'published');
INSERT INTO lng_data VALUES ('common', 'publication_date', 'en', 'publication date');
INSERT INTO lng_data VALUES ('common', 'publication', 'en', 'publication');
INSERT INTO lng_data VALUES ('common', 'pub_section', 'en', 'public section');
INSERT INTO lng_data VALUES ('common', 'profile', 'en', 'profile');
INSERT INTO lng_data VALUES ('common', 'print', 'en', 'print');
INSERT INTO lng_data VALUES ('common', 'presentation_options', 'en', 'presentation options');
INSERT INTO lng_data VALUES ('common', 'position', 'en', 'position');
INSERT INTO lng_data VALUES ('common', 'port', 'en', 'port');
INSERT INTO lng_data VALUES ('common', 'phrase', 'en', 'phrase');
INSERT INTO lng_data VALUES ('common', 'phone', 'en', 'phone');
INSERT INTO lng_data VALUES ('common', 'personal_profile', 'en', 'personal profile');
INSERT INTO lng_data VALUES ('common', 'personal_desktop', 'en', 'personal desktop');
INSERT INTO lng_data VALUES ('common', 'perm_settings', 'en', 'permissions');
INSERT INTO lng_data VALUES ('common', 'payment_system', 'en', 'payment system');
INSERT INTO lng_data VALUES ('common', 'path_to_zip', 'en', 'path to zip');
INSERT INTO lng_data VALUES ('common', 'path_to_unzip', 'en', 'path to unzip');
INSERT INTO lng_data VALUES ('common', 'path_to_java', 'en', 'path to java');
INSERT INTO lng_data VALUES ('common', 'path_to_convert', 'en', 'path to convert');
INSERT INTO lng_data VALUES ('common', 'path_to_babylon', 'en', 'path to babylon');
INSERT INTO lng_data VALUES ('common', 'path', 'en', 'path');
INSERT INTO lng_data VALUES ('common', 'password', 'en', 'password');
INSERT INTO lng_data VALUES ('common', 'page_edit', 'en', 'edit page');
INSERT INTO lng_data VALUES ('common', 'page', 'en', 'page');
INSERT INTO lng_data VALUES ('common', 'owner', 'en', 'owner');
INSERT INTO lng_data VALUES ('common', 'overview', 'en', 'overview');
INSERT INTO lng_data VALUES ('common', 'other', 'en', 'other');
INSERT INTO lng_data VALUES ('common', 'options', 'en', 'options');
INSERT INTO lng_data VALUES ('common', 'optimize', 'en', 'optimize');
INSERT INTO lng_data VALUES ('common', 'online_version', 'en', 'online version');
INSERT INTO lng_data VALUES ('common', 'online_chapter', 'en', 'online chapter');
INSERT INTO lng_data VALUES ('common', 'old', 'en', 'old');
INSERT INTO lng_data VALUES ('common', 'offline_version', 'en', 'offline version');
INSERT INTO lng_data VALUES ('common', 'objects', 'en', 'objects');
INSERT INTO lng_data VALUES ('common', 'not_installed', 'en', 'not installed');
INSERT INTO lng_data VALUES ('common', 'normal', 'en', 'normal');
INSERT INTO lng_data VALUES ('common', 'none', 'en', 'none');
INSERT INTO lng_data VALUES ('common', 'no_title', 'en', 'no title');
INSERT INTO lng_data VALUES ('common', 'no_objects', 'en', 'no objects');
INSERT INTO lng_data VALUES ('common', 'no', 'en', 'no');
INSERT INTO lng_data VALUES ('common', 'nickname', 'en', 'nickname');
INSERT INTO lng_data VALUES ('common', 'news', 'en', 'news');
INSERT INTO lng_data VALUES ('common', 'new_group', 'en', 'new group');
INSERT INTO lng_data VALUES ('common', 'new_folder', 'en', 'new folder');
INSERT INTO lng_data VALUES ('common', 'new', 'en', 'new');
INSERT INTO lng_data VALUES ('common', 'name', 'en', 'name');
INSERT INTO lng_data VALUES ('common', 'must_fill_in', 'en', 'must fill in');
INSERT INTO lng_data VALUES ('common', 'multimedia', 'en', 'multimedia');
INSERT INTO lng_data VALUES ('common', 'msg_nothing_found', 'en', 'msg_nothing_found');
INSERT INTO lng_data VALUES ('common', 'msg_failed', 'en', 'sorry, action failed');
INSERT INTO lng_data VALUES ('common', 'msg_changes_ok', 'en', 'the changes were ok');
INSERT INTO lng_data VALUES ('common', 'move_to', 'en', 'move to');
INSERT INTO lng_data VALUES ('common', 'migrate', 'en', 'migrate');
INSERT INTO lng_data VALUES ('common', 'message_to', 'en', 'message to:');
INSERT INTO lng_data VALUES ('common', 'message', 'en', 'message');
INSERT INTO lng_data VALUES ('common', 'message_content', 'en', 'message content');
INSERT INTO lng_data VALUES ('common', 'members', 'en', 'members');
INSERT INTO lng_data VALUES ('common', 'mark_all_unread', 'en', 'mark all as unread');
INSERT INTO lng_data VALUES ('common', 'mark_all_read', 'en', 'mark all as read');
INSERT INTO lng_data VALUES ('common', 'mails', 'en', 'mails');
INSERT INTO lng_data VALUES ('common', 'mail_s_unread', 'en', 'unread mail(s)');
INSERT INTO lng_data VALUES ('common', 'mail_s', 'en', 'mail(s)');
INSERT INTO lng_data VALUES ('common', 'mail', 'en', 'mail');
INSERT INTO lng_data VALUES ('common', 'los_last_visited', 'en', 'last visited learning objects');
INSERT INTO lng_data VALUES ('common', 'los', 'en', 'learning objects');
INSERT INTO lng_data VALUES ('common', 'logout', 'en', 'logout');
INSERT INTO lng_data VALUES ('common', 'login_to_ilias', 'en', 'login to ILIAS');
INSERT INTO lng_data VALUES ('common', 'login', 'en', 'login');
INSERT INTO lng_data VALUES ('common', 'lo_overview', 'en', 'lo overview');
INSERT INTO lng_data VALUES ('common', 'lo_other_langs', 'en', 'lo\'s in other langauges');
INSERT INTO lng_data VALUES ('common', 'lo_new', 'en', 'new learning object');
INSERT INTO lng_data VALUES ('common', 'lo_edit', 'en', 'edit learning object');
INSERT INTO lng_data VALUES ('common', 'lo_categories', 'en', 'lo categories');
INSERT INTO lng_data VALUES ('common', 'lo_available', 'en', 'available learning objects');
INSERT INTO lng_data VALUES ('common', 'lo', 'en', 'learning object');
INSERT INTO lng_data VALUES ('common', 'literature_bookmarks', 'en', 'literature bookmarks');
INSERT INTO lng_data VALUES ('common', 'literature', 'en', 'literature');
INSERT INTO lng_data VALUES ('common', 'list_of_questions', 'en', 'question list');
INSERT INTO lng_data VALUES ('common', 'list_of_pages', 'en', 'pages list');
INSERT INTO lng_data VALUES ('common', 'link', 'en', 'link');
INSERT INTO lng_data VALUES ('common', 'linked_pages', 'en', 'linked pages');
INSERT INTO lng_data VALUES ('common', 'linebreak', 'en', 'linebreak');
INSERT INTO lng_data VALUES ('common', 'level', 'en', 'level');
INSERT INTO lng_data VALUES ('common', 'ldap', 'en', 'LDAP');
INSERT INTO lng_data VALUES ('common', 'lastname', 'en', 'lastname');
INSERT INTO lng_data VALUES ('common', 'last_change', 'en', 'last change');
INSERT INTO lng_data VALUES ('common', 'languages_generate_from_upload', 'en', 'generate languages from uploaded file');
INSERT INTO lng_data VALUES ('common', 'languages_generate_from_file', 'en', 'generate languages from file');
INSERT INTO lng_data VALUES ('common', 'languages', 'en', 'languages');
INSERT INTO lng_data VALUES ('common', 'language', 'en', 'language');
INSERT INTO lng_data VALUES ('common', 'lang_path', 'en', 'language path');
INSERT INTO lng_data VALUES ('common', 'keywords', 'en', 'keywords');
INSERT INTO lng_data VALUES ('common', 'item', 'en', 'item');
INSERT INTO lng_data VALUES ('common', 'ip_address', 'en', 'IP address');
INSERT INTO lng_data VALUES ('common', 'institution', 'en', 'institution');
INSERT INTO lng_data VALUES ('common', 'installed', 'en', 'installed');
INSERT INTO lng_data VALUES ('common', 'install', 'en', 'install');
INSERT INTO lng_data VALUES ('common', 'inst_name', 'en', 'installation name');
INSERT INTO lng_data VALUES ('common', 'inst_info', 'en', 'installation info');
INSERT INTO lng_data VALUES ('common', 'inst_id', 'en', 'installation ID');
INSERT INTO lng_data VALUES ('common', 'inifile', 'en', 'ini-file');
INSERT INTO lng_data VALUES ('common', 'information_abbr', 'en', 'info');
INSERT INTO lng_data VALUES ('common', 'inbox', 'en', 'inbox');
INSERT INTO lng_data VALUES ('common', 'ilias_version', 'en', 'ILIAS version');
INSERT INTO lng_data VALUES ('common', 'id', 'en', 'ID');
INSERT INTO lng_data VALUES ('common', 'http_path', 'en', 'http path');
INSERT INTO lng_data VALUES ('common', 'host', 'en', 'host');
INSERT INTO lng_data VALUES ('common', 'help', 'en', 'help');
INSERT INTO lng_data VALUES ('common', 'guest', 'en', 'guest');
INSERT INTO lng_data VALUES ('common', 'groupscope', 'en', 'groupscope');
INSERT INTO lng_data VALUES ('common', 'groups', 'en', 'groups');
INSERT INTO lng_data VALUES ('common', 'groupname', 'en', 'groupname');
INSERT INTO lng_data VALUES ('common', 'group_filesharing', 'en', 'group file sharing');
INSERT INTO lng_data VALUES ('common', 'glossary', 'en', 'glossary');
INSERT INTO lng_data VALUES ('common', 'generate', 'en', 'generate');
INSERT INTO lng_data VALUES ('common', 'gender', 'en', 'gender');
INSERT INTO lng_data VALUES ('common', 'functions', 'en', 'functions');
INSERT INTO lng_data VALUES ('common', 'from', 'en', 'from:');
INSERT INTO lng_data VALUES ('common', 'forward', 'en', 'forward');
INSERT INTO lng_data VALUES ('common', 'forums_of_your_groups', 'en', 'forums of your groups');
INSERT INTO lng_data VALUES ('common', 'forums_available', 'en', 'available forums');
INSERT INTO lng_data VALUES ('common', 'forums', 'en', 'forums');
INSERT INTO lng_data VALUES ('common', 'forum_new', 'en', 'new forum');
INSERT INTO lng_data VALUES ('common', 'forum', 'en', 'forum');
INSERT INTO lng_data VALUES ('common', 'forename', 'en', 'forename');
INSERT INTO lng_data VALUES ('common', 'folders', 'en', 'folders');
INSERT INTO lng_data VALUES ('common', 'folder', 'en', 'folder');
INSERT INTO lng_data VALUES ('common', 'firstname', 'en', 'firstname');
INSERT INTO lng_data VALUES ('common', 'fill_out_all_required_fields', 'en', 'please fill out all required fields');
INSERT INTO lng_data VALUES ('common', 'files_location', 'en', 'files location');
INSERT INTO lng_data VALUES ('common', 'file_version', 'en', 'version provided in file');
INSERT INTO lng_data VALUES ('common', 'feedback_recipient', 'en', 'feedback recipient');
INSERT INTO lng_data VALUES ('common', 'feedback', 'en', 'feedback');
INSERT INTO lng_data VALUES ('common', 'faq_exercise', 'en', 'faq exercise');
INSERT INTO lng_data VALUES ('common', 'execute', 'en', 'execute');
INSERT INTO lng_data VALUES ('common', 'error_recipient', 'en', 'error recipient');
INSERT INTO lng_data VALUES ('common', 'err_wrong_login', 'en', 'wrong login');
INSERT INTO lng_data VALUES ('common', 'err_create_database_failed', 'en', 'creation of database failed');
INSERT INTO lng_data VALUES ('common', 'enumerate', 'en', 'enumerate');
INSERT INTO lng_data VALUES ('common', 'enable', 'en', 'enable');
INSERT INTO lng_data VALUES ('common', 'employee', 'en', 'employee');
INSERT INTO lng_data VALUES ('common', 'email', 'en', 'email');
INSERT INTO lng_data VALUES ('common', 'editor', 'en', 'editor');
INSERT INTO lng_data VALUES ('common', 'edit_properties', 'en', 'edit properties');
INSERT INTO lng_data VALUES ('common', 'edit_data', 'en', 'edit data');
INSERT INTO lng_data VALUES ('common', 'edit', 'en', 'edit');
INSERT INTO lng_data VALUES ('common', 'drafts', 'en', 'drafts');
INSERT INTO lng_data VALUES ('common', 'desired_password', 'en', 'desired password');
INSERT INTO lng_data VALUES ('common', 'description', 'en', 'description');
INSERT INTO lng_data VALUES ('common', 'deleted', 'en', 'deleted');
INSERT INTO lng_data VALUES ('common', 'delete_selected', 'en', 'delete selected');
INSERT INTO lng_data VALUES ('common', 'delete_all', 'en', 'delete all');
INSERT INTO lng_data VALUES ('common', 'delete', 'en', 'delete');
INSERT INTO lng_data VALUES ('common', 'default', 'en', 'default');
INSERT INTO lng_data VALUES ('common', 'db_version', 'en', 'database version');
INSERT INTO lng_data VALUES ('common', 'db_user', 'en', 'database user');
INSERT INTO lng_data VALUES ('common', 'db_type', 'en', 'database type');
INSERT INTO lng_data VALUES ('common', 'db_pass', 'en', 'database password');
INSERT INTO lng_data VALUES ('common', 'db_name', 'en', 'database name');
INSERT INTO lng_data VALUES ('common', 'db_host', 'en', 'database host');
INSERT INTO lng_data VALUES ('common', 'date', 'en', 'date');
INSERT INTO lng_data VALUES ('common', 'database_version', 'en', 'current database version');
INSERT INTO lng_data VALUES ('common', 'database_update', 'en', 'update database');
INSERT INTO lng_data VALUES ('common', 'database_needs_update', 'en', 'database needs an update');
INSERT INTO lng_data VALUES ('common', 'database_is_uptodate', 'en', 'the database is up-to-date');
INSERT INTO lng_data VALUES ('common', 'database_exists', 'en', 'database exists');
INSERT INTO lng_data VALUES ('common', 'database', 'en', 'database');
INSERT INTO lng_data VALUES ('common', 'current_password', 'en', 'current password');
INSERT INTO lng_data VALUES ('common', 'crs_management_system', 'en', 'crs management system');
INSERT INTO lng_data VALUES ('common', 'crs_available', 'en', 'available courses');
INSERT INTO lng_data VALUES ('common', 'create_in', 'en', 'create in');
INSERT INTO lng_data VALUES ('common', 'create', 'en', 'create');
INSERT INTO lng_data VALUES ('common', 'courses', 'en', 'courses');
INSERT INTO lng_data VALUES ('common', 'course', 'en', 'course');
INSERT INTO lng_data VALUES ('common', 'country', 'en', 'country');
INSERT INTO lng_data VALUES ('common', 'contact_information', 'en', 'contact information');
INSERT INTO lng_data VALUES ('common', 'compose', 'en', 'compose');
INSERT INTO lng_data VALUES ('common', 'comma_separated', 'en', 'comma separated');
INSERT INTO lng_data VALUES ('common', 'city', 'en', 'city');
INSERT INTO lng_data VALUES ('common', 'choose_language', 'en', 'choose your language');
INSERT INTO lng_data VALUES ('common', 'chg_password', 'en', 'change password');
INSERT INTO lng_data VALUES ('common', 'chg_language', 'en', 'change language');
INSERT INTO lng_data VALUES ('common', 'check', 'en', 'check');
INSERT INTO lng_data VALUES ('common', 'chapter_title', 'en', 'chapter title');
INSERT INTO lng_data VALUES ('common', 'chapter_number', 'en', 'chapter number');
INSERT INTO lng_data VALUES ('common', 'chapter', 'en', 'chapter');
INSERT INTO lng_data VALUES ('common', 'change_metadata', 'en', 'change metadata');
INSERT INTO lng_data VALUES ('common', 'change_lo_info', 'en', 'change lo info');
INSERT INTO lng_data VALUES ('common', 'change', 'en', 'change');
INSERT INTO lng_data VALUES ('common', 'cc', 'en', 'cc');
INSERT INTO lng_data VALUES ('common', 'categories', 'en', 'categories');
INSERT INTO lng_data VALUES ('common', 'bookmarks', 'en', 'bookmarks');
INSERT INTO lng_data VALUES ('common', 'bookmark_new', 'en', 'new bookmark');
INSERT INTO lng_data VALUES ('common', 'bc', 'en', 'bc');
INSERT INTO lng_data VALUES ('common', 'basic_data', 'en', 'basic data');
INSERT INTO lng_data VALUES ('common', 'basedn', 'en', 'BaseDN');
INSERT INTO lng_data VALUES ('common', 'back', 'en', 'back');
INSERT INTO lng_data VALUES ('common', 'available_languages', 'en', 'available languages');
INSERT INTO lng_data VALUES ('common', 'authors', 'en', 'authors');
INSERT INTO lng_data VALUES ('common', 'assign_lo_forum', 'en', 'assign lo forum');
INSERT INTO lng_data VALUES ('common', 'are_you_sure', 'en', 'Are you sure?');
INSERT INTO lng_data VALUES ('common', 'archive', 'en', 'archive');
INSERT INTO lng_data VALUES ('common', 'announce_changes', 'en', 'announce changes');
INSERT INTO lng_data VALUES ('common', 'announce', 'en', 'announce');
INSERT INTO lng_data VALUES ('common', 'also_as_email', 'en', 'also as email');
INSERT INTO lng_data VALUES ('common', 'administrator', 'en', 'administrator');
INSERT INTO lng_data VALUES ('common', 'administration', 'en', 'administration');
INSERT INTO lng_data VALUES ('common', 'administrate', 'en', 'administrate');
INSERT INTO lng_data VALUES ('common', 'add_member', 'en', 'add member');
INSERT INTO lng_data VALUES ('common', 'add_author', 'en', 'add author');
INSERT INTO lng_data VALUES ('common', 'add', 'en', 'add');
INSERT INTO lng_data VALUES ('common', 'actions', 'en', 'actions');
INSERT INTO lng_data VALUES ('common', 'access', 'en', 'access');
INSERT INTO lng_data VALUES ('common', 'accept_usr_agreement', 'en', 'accept user agreement?');
INSERT INTO lng_data VALUES ('common', 'absolute_path', 'en', 'absolute path');
INSERT INTO lng_data VALUES ('common', 'sep_decimal', 'en', '.');
INSERT INTO lng_data VALUES ('common', 'sep_thousand', 'en', ',');
INSERT INTO lng_data VALUES ('common', 'lang_dateformat', 'en', 'MM/DD/YYYY');
INSERT INTO lng_data VALUES ('common', 'lang_version', 'en', '1');
INSERT INTO lng_data VALUES ('common', 'show_owner', 'en', 'show owner');
INSERT INTO lng_data VALUES ('common', 'signature', 'en', 'signature');
INSERT INTO lng_data VALUES ('common', 'startpage', 'en', 'startpage');
INSERT INTO lng_data VALUES ('common', 'status', 'en', 'status');
INSERT INTO lng_data VALUES ('common', 'step', 'en', 'step');
INSERT INTO lng_data VALUES ('common', 'street', 'en', 'street');
INSERT INTO lng_data VALUES ('common', 'structure', 'en', 'structure');
INSERT INTO lng_data VALUES ('common', 'student', 'en', 'student');
INSERT INTO lng_data VALUES ('common', 'subcat_name', 'en', 'subcategory name');
INSERT INTO lng_data VALUES ('common', 'subchapter_new', 'en', 'new subchapter');
INSERT INTO lng_data VALUES ('common', 'subject', 'en', 'subject');
INSERT INTO lng_data VALUES ('common', 'submit', 'en', 'submit');
INSERT INTO lng_data VALUES ('common', 'subscription', 'en', 'subscription');
INSERT INTO lng_data VALUES ('common', 'summary', 'en', 'summary');
INSERT INTO lng_data VALUES ('common', 'system', 'en', 'system');
INSERT INTO lng_data VALUES ('common', 'system_groups', 'en', 'system groups');
INSERT INTO lng_data VALUES ('common', 'system_grp', 'en', 'system group');
INSERT INTO lng_data VALUES ('common', 'system_message', 'en', 'system message');
INSERT INTO lng_data VALUES ('common', 'system_language', 'en', 'system language');
INSERT INTO lng_data VALUES ('common', 'test_intern', 'en', 'test intern');
INSERT INTO lng_data VALUES ('common', 'time', 'en', 'time');
INSERT INTO lng_data VALUES ('common', 'title', 'en', 'title');
INSERT INTO lng_data VALUES ('common', 'to', 'en', 'to:');
INSERT INTO lng_data VALUES ('common', 'total', 'en', 'total');
INSERT INTO lng_data VALUES ('common', 'tpl_path', 'en', 'template path');
INSERT INTO lng_data VALUES ('common', 'trash', 'en', 'trash');
INSERT INTO lng_data VALUES ('common', 'type', 'en', 'type');
INSERT INTO lng_data VALUES ('common', 'type_your_message_here', 'en', 'type your message here');
INSERT INTO lng_data VALUES ('common', 'uid', 'en', 'uid');
INSERT INTO lng_data VALUES ('common', 'unknown', 'en', 'UNKNOWN');
INSERT INTO lng_data VALUES ('common', 'unread', 'en', 'unread');
INSERT INTO lng_data VALUES ('common', 'update_applied', 'en', 'update applied');
INSERT INTO lng_data VALUES ('common', 'url', 'en', 'url');
INSERT INTO lng_data VALUES ('common', 'url_description', 'en', 'url description');
INSERT INTO lng_data VALUES ('common', 'userdata', 'en', 'userdata');
INSERT INTO lng_data VALUES ('common', 'username', 'en', 'username');
INSERT INTO lng_data VALUES ('common', 'users', 'en', 'users');
INSERT INTO lng_data VALUES ('common', 'usr_agreement', 'en', 'user agreement');
INSERT INTO lng_data VALUES ('common', 'usr_skin', 'en', 'user skin');
INSERT INTO lng_data VALUES ('common', 'usr_style', 'en', 'usr style');
INSERT INTO lng_data VALUES ('common', 'version', 'en', 'version');
INSERT INTO lng_data VALUES ('common', 'view_content', 'en', 'view content');
INSERT INTO lng_data VALUES ('common', 'visible_layers', 'en', 'visible layers');
INSERT INTO lng_data VALUES ('common', 'write', 'en', 'write');
INSERT INTO lng_data VALUES ('common', 'yes', 'en', 'yes');
INSERT INTO lng_data VALUES ('common', 'your_message', 'en', 'your message');
INSERT INTO lng_data VALUES ('common', 'zip', 'en', 'zip code');
INSERT INTO lng_data VALUES ('common', 'obj_not_found', 'en', 'object not found');
INSERT INTO lng_data VALUES ('common', 'cut', 'en', 'cut');
INSERT INTO lng_data VALUES ('common', 'copy', 'en', 'copy');
INSERT INTO lng_data VALUES ('common', 'paste', 'en', 'paste');
INSERT INTO lng_data VALUES ('common', 'clear', 'en', 'clear');
INSERT INTO lng_data VALUES ('common', 'required_field', 'en', 'required field');
INSERT INTO lng_data VALUES ('common', 'you_may_add_local_roles', 'en', 'you may add local roles');
INSERT INTO lng_data VALUES ('common', 'obj_owner', 'en', 'This Object is owned by');
INSERT INTO lng_data VALUES ('common', 'permission', 'en', 'permission');
INSERT INTO lng_data VALUES ('common', 'roles', 'en', 'roles');
INSERT INTO lng_data VALUES ('common', 'uninstall', 'en', 'uninstall');
INSERT INTO lng_data VALUES ('common', 'frm', 'en', 'forum');
INSERT INTO lng_data VALUES ('common', 'obj', 'en', 'object');
INSERT INTO lng_data VALUES ('common', 'cat', 'en', 'category');
INSERT INTO lng_data VALUES ('common', 'le', 'en', 'learning module');
INSERT INTO lng_data VALUES ('common', 'grp', 'en', 'group');
INSERT INTO lng_data VALUES ('common', 'crs', 'en', 'course');
INSERT INTO lng_data VALUES ('common', 'file', 'en', 'file');
INSERT INTO lng_data VALUES ('common', 'rolf', 'en', 'role folder');
INSERT INTO lng_data VALUES ('common', 'rolt', 'en', 'role template');
INSERT INTO lng_data VALUES ('common', 'lngf', 'en', 'language folder');
INSERT INTO lng_data VALUES ('common', 'lng', 'en', 'language');
INSERT INTO lng_data VALUES ('common', 'change_language', 'en', 'set user language');
INSERT INTO lng_data VALUES ('common', 'update_language', 'en', 'update language');
INSERT INTO lng_data VALUES ('common', 'check_language', 'en', 'check language');
INSERT INTO lng_data VALUES ('common', 'set_system_language', 'en', 'set system language');
INSERT INTO lng_data VALUES ('common', 'system_choose_language', 'en', 'system choose language');
INSERT INTO lng_data VALUES ('common', 'usr', 'en', 'user');
INSERT INTO lng_data VALUES ('common', 'user', 'en', 'user');
INSERT INTO lng_data VALUES ('common', 'usrf', 'en', 'user folder');
INSERT INTO lng_data VALUES ('common', 'objf', 'en', 'object folder');
INSERT INTO lng_data VALUES ('common', 'role', 'en', 'role');
INSERT INTO lng_data VALUES ('common', 'passwd', 'en', 'password');
INSERT INTO lng_data VALUES ('common', 'desc', 'en', 'description');
INSERT INTO lng_data VALUES ('common', 'default_role', 'en', 'default role');
INSERT INTO lng_data VALUES ('common', 'enabled', 'en', 'enabled');
INSERT INTO lng_data VALUES ('common', 'disabled', 'en', 'disabled');
INSERT INTO lng_data VALUES ('common', 'import', 'en', 'import');
INSERT INTO lng_data VALUES ('common', 'in_use', 'en', 'user language');
INSERT INTO lng_data VALUES ('common', 'lang_de', 'en', 'German');
INSERT INTO lng_data VALUES ('common', 'lang_dk', 'en', 'Danish');
INSERT INTO lng_data VALUES ('common', 'lang_en', 'en', 'English');
INSERT INTO lng_data VALUES ('common', 'lang_es', 'en', 'Spanish');
INSERT INTO lng_data VALUES ('common', 'lang_fr', 'en', 'French');
INSERT INTO lng_data VALUES ('common', 'lang_id', 'en', 'Indonesian');
INSERT INTO lng_data VALUES ('common', 'lang_it', 'en', 'Italian');
INSERT INTO lng_data VALUES ('common', 'lang_no', 'en', 'Norwegisch');
INSERT INTO lng_data VALUES ('common', 'lang_pl', 'en', 'Polish');
INSERT INTO lng_data VALUES ('common', 'lang_se', 'en', 'Swedish');
INSERT INTO lng_data VALUES ('common', 'lang_xx', 'en', 'Custom');
INSERT INTO lng_data VALUES ('common', 'forums_not_available', 'en', 'forums not available');
INSERT INTO lng_data VALUES ('common', 'forums_last_post', 'en', 'last article');
INSERT INTO lng_data VALUES ('common', 'forums_moderators', 'en', 'moderators');
INSERT INTO lng_data VALUES ('common', 'forums_new_thread', 'en', 'new topic');
INSERT INTO lng_data VALUES ('common', 'permission_denied', 'en', 'permission denied');
INSERT INTO lng_data VALUES ('common', 'forums_thread_new_entry', 'en', 'new topic has been inscribed');
INSERT INTO lng_data VALUES ('common', 'forums_post_new_entry', 'en', 'new article has been inscribed');
INSERT INTO lng_data VALUES ('common', 'form_empty_fields', 'en', 'please complete these fields:');
INSERT INTO lng_data VALUES ('common', 'today', 'en', 'today');
INSERT INTO lng_data VALUES ('common', 'forums_threads', 'en', 'topics');
INSERT INTO lng_data VALUES ('common', 'forums_posts', 'en', 'articles');
INSERT INTO lng_data VALUES ('common', 'forums_the_post', 'en', 'article');
INSERT INTO lng_data VALUES ('common', 'forums_thread_create', 'en', 'created from');
INSERT INTO lng_data VALUES ('common', 'operation', 'en', 'Operation');
INSERT INTO lng_data VALUES ('common', 'edit_operations', 'en', 'edit operations');
INSERT INTO lng_data VALUES ('common', 'file_not_found', 'en', 'File not found');
INSERT INTO lng_data VALUES ('common', 'new_language', 'en', 'New language');
INSERT INTO lng_data VALUES ('common', 'forums_threads_not_available', 'en', 'topics not available');
INSERT INTO lng_data VALUES ('common', 'forums_posts_not_available', 'en', 'articles not available');
INSERT INTO lng_data VALUES ('common', 'author', 'en', 'author');
INSERT INTO lng_data VALUES ('common', 'registered_since', 'en', 'registered since');
INSERT INTO lng_data VALUES ('common', 'context', 'en', 'context');
INSERT INTO lng_data VALUES ('common', 'forums_respond', 'en', 'respond to an article');
INSERT INTO lng_data VALUES ('common', 'forums_your_reply', 'en', 'your reply');
# --------------------------------------------------------

#
# Table structure for table `lo_attribute`
#

CREATE TABLE lo_attribute (
  attribute_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0'
) TYPE=MyISAM;

#
# Dumping data for table `lo_attribute`
#

# --------------------------------------------------------

#
# Table structure for table `lo_attribute_name_leaf`
#

CREATE TABLE lo_attribute_name_leaf (
  leaf_id int(11) NOT NULL default '0',
  attribute_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  leaf_text varchar(128) NOT NULL default ''
) TYPE=MyISAM;

#
# Dumping data for table `lo_attribute_name_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_attribute_namespace_leaf`
#

CREATE TABLE lo_attribute_namespace_leaf (
  leaf_id int(11) NOT NULL default '0',
  attribute_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL
) TYPE=MyISAM;

#
# Dumping data for table `lo_attribute_namespace_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_attribute_value_leaf`
#

CREATE TABLE lo_attribute_value_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  attribute_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  leaf_text varchar(255) NOT NULL default '',
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_attribute_value_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_cdata_leaf`
#

CREATE TABLE lo_cdata_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_cdata_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_comment_leaf`
#

CREATE TABLE lo_comment_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_comment_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_element_name_leaf`
#

CREATE TABLE lo_element_name_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text varchar(128) NOT NULL default '',
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_element_name_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_element_namespace_leaf`
#

CREATE TABLE lo_element_namespace_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_element_namespace_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_entity_reference_leaf`
#

CREATE TABLE lo_entity_reference_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_entity_reference_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_node_type`
#

CREATE TABLE lo_node_type (
  node_type_id int(11) NOT NULL auto_increment,
  description varchar(50) default NULL,
  lft_delimiter varchar(10) default NULL,
  rgt_delimiter varchar(10) default NULL,
  PRIMARY KEY  (node_type_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_node_type`
#

INSERT INTO lo_node_type VALUES (1, 'ELEMENT_NODE', '<', '>');
INSERT INTO lo_node_type VALUES (2, 'ATTRIBUTE_NODE(not used)', '"', '"');
INSERT INTO lo_node_type VALUES (3, 'TEXT_NODE', NULL, NULL);
INSERT INTO lo_node_type VALUES (5, 'ENTITY_REF_NODE', '&', ';');
INSERT INTO lo_node_type VALUES (4, 'CDATA_SECTION_NODE', '<![CDATA[', ']]>');
INSERT INTO lo_node_type VALUES (8, 'COMMENT_NODE', '<!--', '-->');
INSERT INTO lo_node_type VALUES (9, 'DOCUMENT_NODE', NULL, NULL);
INSERT INTO lo_node_type VALUES (10, 'DOCUMENT_TYPE_NODE', NULL, NULL);
INSERT INTO lo_node_type VALUES (6, 'ENTITY_NODE', '&', ';');
# --------------------------------------------------------

#
# Table structure for table `lo_pi_data_leaf`
#

CREATE TABLE lo_pi_data_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_pi_data_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_pi_target_leaf`
#

CREATE TABLE lo_pi_target_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_pi_target_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_text_leaf`
#

CREATE TABLE lo_text_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_text_leaf`
#

# --------------------------------------------------------

#
# Table structure for table `lo_tree`
#

CREATE TABLE lo_tree (
  node_id int(11) NOT NULL auto_increment,
  lo_id int(11) NOT NULL default '0',
  parent_node_id int(11) NOT NULL default '0',
  lft int(11) NOT NULL default '0',
  rgt int(11) NOT NULL default '0',
  node_type_id int(11) NOT NULL default '0',
  depth int(11) NOT NULL default '0',
  prev_sibling_node_id int(11) NOT NULL default '0',
  next_sibling_node_id int(11) NOT NULL default '0',
  first_child_node_id int(11) NOT NULL default '0',
  PRIMARY KEY  (node_id,lo_id)
) TYPE=MyISAM;

#
# Dumping data for table `lo_tree`
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

INSERT INTO object_data VALUES (2, 'role', 'Adminstrator', 'Role for systemadministrators. This role grants access to everything!', -1, '2002-01-16 15:31:45', '2002-01-16 15:32:49');
INSERT INTO object_data VALUES (3, 'role', 'Author', 'Role for teachers with many write & some create permissions.', -1, '2002-01-16 15:32:50', '2002-01-16 15:33:54');
INSERT INTO object_data VALUES (4, 'role', 'Learner', 'Typical role for students. Grants write access to some objects.', -1, '2002-01-16 15:34:00', '2002-01-16 15:34:35');
INSERT INTO object_data VALUES (5, 'role', 'Guest', 'Role grants only a few visible & read permissions.', -1, '2002-01-16 15:34:46', '2002-01-16 15:35:19');
INSERT INTO object_data VALUES (6, 'usr', 'The System Administrator', 'admin@yourserver.com', -1, '2002-01-16 16:09:22', '2002-08-30 13:54:04');
INSERT INTO object_data VALUES (7, 'usrf', 'Users', 'Folder contains all users', -1, '2002-06-27 09:24:06', '2002-06-27 09:24:06');
INSERT INTO object_data VALUES (8, 'rolf', 'Roles', 'Folder contains all roles', -1, '2002-06-27 09:24:06', '2002-06-27 09:24:06');
INSERT INTO object_data VALUES (1, 'root', 'ILIAS open source', 'This is the root node of the system!!!', -1, '2002-06-24 15:15:03', '2002-06-24 15:15:03');
INSERT INTO object_data VALUES (9, 'adm', 'System Settings', 'Folder contains the systems settings', -1, '2002-07-15 12:37:33', '2002-07-15 12:37:33');
INSERT INTO object_data VALUES (10, 'objf', 'Objects', 'Folder contains list of known object types', -1, '2002-07-15 12:36:56', '2002-07-15 12:36:56');
INSERT INTO object_data VALUES (11, 'lngf', 'Languages', 'Folder contains all available languages', -1, '2002-07-15 15:52:51', '2002-07-15 15:52:51');
INSERT INTO object_data VALUES (12, 'typ', 'usr', 'User object', -1, '2002-07-15 15:53:37', '2002-07-15 15:53:37');
INSERT INTO object_data VALUES (13, 'typ', 'le', 'Learning module Object', -1, '2002-07-15 15:54:04', '2002-07-15 15:54:04');
INSERT INTO object_data VALUES (14, 'typ', 'frm', 'Forum object', -1, '2002-07-15 15:54:22', '2002-07-15 15:54:22');
INSERT INTO object_data VALUES (15, 'typ', 'grp', 'Group object', -1, '2002-07-15 15:54:37', '2002-07-15 15:54:37');
INSERT INTO object_data VALUES (16, 'typ', 'cat', 'Category object', -1, '2002-07-15 15:54:54', '2002-07-15 15:54:54');
INSERT INTO object_data VALUES (17, 'typ', 'crs', 'Course object', -1, '2002-07-15 15:55:08', '2002-07-15 15:55:08');
INSERT INTO object_data VALUES (18, 'typ', 'file', 'FileSharing object', -1, '2002-07-15 15:55:31', '2002-07-15 15:55:31');
INSERT INTO object_data VALUES (19, 'typ', 'mail', 'Mailmodule object', -1, '2002-07-15 15:55:49', '2002-07-15 15:55:49');
INSERT INTO object_data VALUES (21, 'typ', 'adm', 'Administration Panel object', -1, '2002-07-15 15:56:38', '2002-07-15 15:56:38');
INSERT INTO object_data VALUES (22, 'typ', 'usrf', 'User Folder object', -1, '2002-07-15 15:56:52', '2002-07-15 15:56:52');
INSERT INTO object_data VALUES (23, 'typ', 'rolf', 'Role Folder object', -1, '2002-07-15 15:57:06', '2002-07-15 15:57:06');
INSERT INTO object_data VALUES (24, 'typ', 'objf', 'Object-Type Folder object', -1, '2002-07-15 15:57:17', '2002-07-15 15:57:17');
INSERT INTO object_data VALUES (26, 'typ', 'typ', 'Object Type Definition object', -1, '2002-07-15 15:58:16', '2002-07-15 15:58:16');
INSERT INTO object_data VALUES (27, 'typ', 'rolt', 'Role template object', -1, '2002-07-15 15:58:16', '2002-07-15 15:58:16');
INSERT INTO object_data VALUES (28, 'typ', 'lngf', 'Language Folder object', -1, '2002-08-28 14:22:01', '2002-08-28 14:22:01');
INSERT INTO object_data VALUES (29, 'typ', 'lng', 'Language object', -1, '2002-08-30 10:18:29', '2002-08-30 10:18:29');
INSERT INTO object_data VALUES (30, 'typ', 'role', 'Role Object', -1, '2002-08-30 10:21:37', '2002-08-30 10:21:37');
INSERT INTO object_data VALUES (31, 'typ', 'lo', 'Learning Object', -1, '2002-08-30 10:21:37', '2002-08-30 10:21:37');
INSERT INTO object_data VALUES (32, 'typ', 'uset', 'User Setting Object', -1, '2002-08-30 10:21:37', '2002-08-30 10:21:37');
INSERT INTO object_data VALUES (33, 'typ', 'root', 'Root Folder Object', -1, '2002-12-21 00:04:00', '2002-12-21 00:04:00');
INSERT INTO object_data VALUES (34, 'lng', 'en', 'installed', -1, '0000-00-00 00:00:00', '2002-12-22 13:24:30');
INSERT INTO object_data VALUES (156, 'usr', 'Bill Teacher', '', 6, '2002-08-30 14:04:26', '2002-12-24 12:44:34');
INSERT INTO object_data VALUES (158, 'usr', 'Guest', '', 6, '2002-08-30 14:05:39', '2002-12-24 12:43:34');
INSERT INTO object_data VALUES (157, 'usr', 'Jim Student', '', 6, '2002-08-30 14:05:05', '2002-12-24 12:44:21');
INSERT INTO object_data VALUES (162, 'role', 'learner category A', '', 6, '2002-12-24 00:47:11', '2002-12-24 00:47:11');
INSERT INTO object_data VALUES (163, 'role', 'learner category B', '', 6, '2002-12-24 00:47:28', '2002-12-24 00:47:28');
INSERT INTO object_data VALUES (164, 'cat', 'Category A', '', 6, '2002-12-24 00:47:41', '2002-12-24 00:47:41');
INSERT INTO object_data VALUES (165, 'cat', 'Category B', '', 6, '2002-12-24 00:47:48', '2002-12-24 00:47:48');
INSERT INTO object_data VALUES (166, 'le', 'Learningmodule1 A', '', 6, '2002-12-24 00:48:00', '2002-12-24 00:48:00');
INSERT INTO object_data VALUES (167, 'le', 'Learningmodule 4 B', '', 6, '2002-12-24 00:48:11', '2002-12-24 00:48:11');
INSERT INTO object_data VALUES (168, 'usr', 'Learner A', '', 6, '2002-12-24 00:49:38', '2002-12-24 12:42:46');
INSERT INTO object_data VALUES (169, 'uset', 'lerner a', 'User Setting Folder', 6, '2002-12-24 00:49:38', '2002-12-24 00:49:38');
INSERT INTO object_data VALUES (170, 'usr', 'Learner B', '', 6, '2002-12-24 00:49:56', '2002-12-24 12:43:03');
INSERT INTO object_data VALUES (171, 'uset', 'lerner b', 'User Setting Folder', 6, '2002-12-24 00:49:56', '2002-12-24 00:49:56');
INSERT INTO object_data VALUES (172, 'frm', 'Forum1 A', ' (learner can write)', 6, '2002-12-24 01:01:08', '2002-12-24 01:01:08');
INSERT INTO object_data VALUES (176, 'crs', 'Course 1 A', '', 6, '2002-12-24 01:03:16', '2002-12-24 01:03:16');
INSERT INTO object_data VALUES (177, 'le', 'Learningmodule3', 'in course1 A', 6, '2002-12-24 01:03:30', '2002-12-24 01:03:30');
INSERT INTO object_data VALUES (178, 'frm', 'Forum3 B', '', 6, '2002-12-24 01:05:57', '2002-12-24 01:05:57');
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

INSERT INTO rbac_fa VALUES (2, 8, 9, 'y');
INSERT INTO rbac_fa VALUES (3, 8, 9, 'y');
INSERT INTO rbac_fa VALUES (4, 8, 9, 'y');
INSERT INTO rbac_fa VALUES (5, 8, 9, 'y');
INSERT INTO rbac_fa VALUES (162, 8, 9, 'y');
INSERT INTO rbac_fa VALUES (163, 8, 9, 'y');
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
INSERT INTO rbac_operations VALUES (7, 'join', 'join/subscribe');
INSERT INTO rbac_operations VALUES (8, 'leave', 'leave/unsubscribe');
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

INSERT INTO rbac_pa VALUES (5, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1, 0);
INSERT INTO rbac_pa VALUES (2, 'a:5:{i:0;s:1:"5";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"2";i:4;s:1:"4";}', 1, 0);
INSERT INTO rbac_pa VALUES (2, 'a:4:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 7, 9);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 8, 9);
INSERT INTO rbac_pa VALUES (2, 'a:4:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 11, 9);
INSERT INTO rbac_pa VALUES (3, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 8, 9);
INSERT INTO rbac_pa VALUES (2, 'a:5:{i:0;s:1:"5";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"2";i:4;s:1:"4";}', 9, 1);
INSERT INTO rbac_pa VALUES (2, 'a:6:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"3";i:4;s:1:"2";i:5;s:1:"4";}', 8, 9);
INSERT INTO rbac_pa VALUES (2, 'a:3:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"2";}', 10, 9);
INSERT INTO rbac_pa VALUES (3, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 7, 9);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 7, 9);
INSERT INTO rbac_pa VALUES (5, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 164, 1);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 164, 1);
INSERT INTO rbac_pa VALUES (3, 'a:4:{i:0;s:1:"5";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 164, 1);
INSERT INTO rbac_pa VALUES (2, 'a:6:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"3";i:4;s:1:"2";i:5;s:1:"4";}', 164, 1);
INSERT INTO rbac_pa VALUES (5, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 165, 1);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 165, 1);
INSERT INTO rbac_pa VALUES (3, 'a:4:{i:0;s:1:"5";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 165, 1);
INSERT INTO rbac_pa VALUES (2, 'a:6:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"3";i:4;s:1:"2";i:5;s:1:"4";}', 165, 1);
INSERT INTO rbac_pa VALUES (4, 'a:4:{i:0;s:1:"7";i:1;s:1:"8";i:2;s:1:"3";i:3;s:1:"2";}', 166, 164);
INSERT INTO rbac_pa VALUES (162, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 166, 164);
INSERT INTO rbac_pa VALUES (3, 'a:7:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"7";i:3;s:1:"8";i:4;s:1:"3";i:5;s:1:"2";i:6;s:1:"4";}', 166, 164);
INSERT INTO rbac_pa VALUES (2, 'a:8:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"7";i:4;s:1:"8";i:5;s:1:"3";i:6;s:1:"2";i:7;s:1:"4";}', 166, 164);
INSERT INTO rbac_pa VALUES (4, 'a:4:{i:0;s:1:"7";i:1;s:1:"8";i:2;s:1:"3";i:3;s:1:"2";}', 167, 165);
INSERT INTO rbac_pa VALUES (163, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 167, 165);
INSERT INTO rbac_pa VALUES (3, 'a:7:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"7";i:3;s:1:"8";i:4;s:1:"3";i:5;s:1:"2";i:6;s:1:"4";}', 167, 165);
INSERT INTO rbac_pa VALUES (2, 'a:8:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"7";i:4;s:1:"8";i:5;s:1:"3";i:6;s:1:"2";i:7;s:1:"4";}', 167, 165);
INSERT INTO rbac_pa VALUES (162, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 164, 1);
INSERT INTO rbac_pa VALUES (5, 'a:1:{i:0;s:1:"2";}', 166, 164);
INSERT INTO rbac_pa VALUES (163, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 165, 1);
INSERT INTO rbac_pa VALUES (5, 'a:1:{i:0;s:1:"2";}', 167, 165);
INSERT INTO rbac_pa VALUES (4, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 1, 0);
INSERT INTO rbac_pa VALUES (162, 'a:1:{i:0;s:1:"2";}', 1, 0);
INSERT INTO rbac_pa VALUES (163, 'a:1:{i:0;s:1:"2";}', 1, 0);
INSERT INTO rbac_pa VALUES (3, 'a:1:{i:0;s:1:"4";}', 1, 0);
INSERT INTO rbac_pa VALUES (5, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 172, 164);
INSERT INTO rbac_pa VALUES (4, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 172, 164);
INSERT INTO rbac_pa VALUES (3, 'a:4:{i:0;s:1:"5";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 172, 164);
INSERT INTO rbac_pa VALUES (2, 'a:6:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"3";i:4;s:1:"2";i:5;s:1:"4";}', 172, 164);
INSERT INTO rbac_pa VALUES (162, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 172, 164);
INSERT INTO rbac_pa VALUES (4, 'a:4:{i:0;s:1:"7";i:1;s:1:"8";i:2;s:1:"3";i:3;s:1:"2";}', 176, 164);
INSERT INTO rbac_pa VALUES (162, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 176, 164);
INSERT INTO rbac_pa VALUES (3, 'a:7:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"7";i:3;s:1:"8";i:4;s:1:"3";i:5;s:1:"2";i:6;s:1:"4";}', 176, 164);
INSERT INTO rbac_pa VALUES (2, 'a:8:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"7";i:4;s:1:"8";i:5;s:1:"3";i:6;s:1:"2";i:7;s:1:"4";}', 176, 164);
INSERT INTO rbac_pa VALUES (4, 'a:4:{i:0;s:1:"7";i:1;s:1:"8";i:2;s:1:"3";i:3;s:1:"2";}', 177, 176);
INSERT INTO rbac_pa VALUES (162, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 177, 176);
INSERT INTO rbac_pa VALUES (3, 'a:7:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"7";i:3;s:1:"8";i:4;s:1:"3";i:5;s:1:"2";i:6;s:1:"4";}', 177, 176);
INSERT INTO rbac_pa VALUES (2, 'a:8:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"7";i:4;s:1:"8";i:5;s:1:"3";i:6;s:1:"2";i:7;s:1:"4";}', 177, 176);
INSERT INTO rbac_pa VALUES (5, 'a:1:{i:0;s:1:"2";}', 176, 164);
INSERT INTO rbac_pa VALUES (5, 'a:1:{i:0;s:1:"2";}', 177, 176);
INSERT INTO rbac_pa VALUES (163, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 178, 165);
INSERT INTO rbac_pa VALUES (5, 'a:2:{i:0;s:1:"3";i:1;s:1:"2";}', 178, 165);
INSERT INTO rbac_pa VALUES (3, 'a:4:{i:0;s:1:"5";i:1;s:1:"3";i:2;s:1:"2";i:3;s:1:"4";}', 178, 165);
INSERT INTO rbac_pa VALUES (4, 'a:3:{i:0;s:1:"3";i:1;s:1:"2";i:2;s:1:"4";}', 178, 165);
INSERT INTO rbac_pa VALUES (2, 'a:6:{i:0;s:1:"5";i:1;s:1:"6";i:2;s:1:"1";i:3;s:1:"3";i:4;s:1:"2";i:5;s:1:"4";}', 178, 165);
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
INSERT INTO rbac_ta VALUES (13, 7);
INSERT INTO rbac_ta VALUES (13, 8);
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
INSERT INTO rbac_ta VALUES (15, 7);
INSERT INTO rbac_ta VALUES (15, 8);
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
INSERT INTO rbac_ta VALUES (17, 5);
INSERT INTO rbac_ta VALUES (17, 6);
INSERT INTO rbac_ta VALUES (17, 7);
INSERT INTO rbac_ta VALUES (17, 8);
INSERT INTO rbac_ta VALUES (18, 1);
INSERT INTO rbac_ta VALUES (18, 2);
INSERT INTO rbac_ta VALUES (18, 3);
INSERT INTO rbac_ta VALUES (18, 4);
INSERT INTO rbac_ta VALUES (19, 1);
INSERT INTO rbac_ta VALUES (19, 2);
INSERT INTO rbac_ta VALUES (19, 3);
INSERT INTO rbac_ta VALUES (19, 4);
INSERT INTO rbac_ta VALUES (19, 5);
INSERT INTO rbac_ta VALUES (19, 6);
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
INSERT INTO rbac_ta VALUES (28, 1);
INSERT INTO rbac_ta VALUES (28, 2);
INSERT INTO rbac_ta VALUES (28, 3);
INSERT INTO rbac_ta VALUES (28, 4);
INSERT INTO rbac_ta VALUES (29, 1);
INSERT INTO rbac_ta VALUES (29, 2);
INSERT INTO rbac_ta VALUES (29, 3);
INSERT INTO rbac_ta VALUES (29, 4);
INSERT INTO rbac_ta VALUES (29, 5);
INSERT INTO rbac_ta VALUES (29, 6);
INSERT INTO rbac_ta VALUES (30, 1);
INSERT INTO rbac_ta VALUES (30, 2);
INSERT INTO rbac_ta VALUES (30, 3);
INSERT INTO rbac_ta VALUES (30, 4);
INSERT INTO rbac_ta VALUES (30, 5);
INSERT INTO rbac_ta VALUES (30, 6);
INSERT INTO rbac_ta VALUES (31, 1);
INSERT INTO rbac_ta VALUES (31, 2);
INSERT INTO rbac_ta VALUES (31, 3);
INSERT INTO rbac_ta VALUES (31, 4);
INSERT INTO rbac_ta VALUES (31, 5);
INSERT INTO rbac_ta VALUES (31, 6);
INSERT INTO rbac_ta VALUES (32, 1);
INSERT INTO rbac_ta VALUES (32, 2);
INSERT INTO rbac_ta VALUES (32, 3);
INSERT INTO rbac_ta VALUES (32, 4);
INSERT INTO rbac_ta VALUES (32, 5);
INSERT INTO rbac_ta VALUES (32, 6);
INSERT INTO rbac_ta VALUES (33, 1);
INSERT INTO rbac_ta VALUES (33, 2);
INSERT INTO rbac_ta VALUES (33, 3);
INSERT INTO rbac_ta VALUES (33, 4);
INSERT INTO rbac_ta VALUES (33, 5);
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

INSERT INTO rbac_templates VALUES (5, 'crs', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'rolf', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'rolf', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'file', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'typ', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'typ', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'objf', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'objf', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'objf', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'lngf', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'lngf', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'lngf', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'lngf', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'file', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'file', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'file', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'file', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'usr', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'file', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'mail', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'mail', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'mail', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'mail', 6, 8);
INSERT INTO rbac_templates VALUES (3, 'mail', 5, 8);
INSERT INTO rbac_templates VALUES (3, 'lo', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'lo', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'lo', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'lo', 6, 8);
INSERT INTO rbac_templates VALUES (3, 'lo', 5, 8);
INSERT INTO rbac_templates VALUES (3, 'le', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'le', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'role', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'role', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'lo', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'lo', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'lng', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'lng', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'frm', 4, 8);
INSERT INTO rbac_templates VALUES (4, 'frm', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'frm', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'cat', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'root', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'root', 3, 8);
INSERT INTO rbac_templates VALUES (5, 'lng', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'lng', 3, 8);
INSERT INTO rbac_templates VALUES (5, 'grp', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'grp', 3, 8);
INSERT INTO rbac_templates VALUES (5, 'frm', 2, 8);
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
INSERT INTO rbac_templates VALUES (2, 'usr', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'usr', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'usr', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'usr', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'uset', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'uset', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'uset', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'uset', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'uset', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'uset', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'root', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'root', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'root', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'root', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'root', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'rolf', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'rolf', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'rolf', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'rolf', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'rolf', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'rolf', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'role', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'role', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'role', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'role', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'role', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'role', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'mail', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'mail', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'mail', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'mail', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'mail', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'mail', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'lo', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'lo', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'lo', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'lo', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'lo', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'lo', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'lng', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'lng', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'lng', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'lng', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'lng', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'lng', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 8, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 7, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'le', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 8, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 7, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'grp', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'frm', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'crs', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'le', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'le', 8, 8);
INSERT INTO rbac_templates VALUES (3, 'le', 7, 8);
INSERT INTO rbac_templates VALUES (3, 'le', 6, 8);
INSERT INTO rbac_templates VALUES (3, 'le', 5, 8);
INSERT INTO rbac_templates VALUES (3, 'grp', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'grp', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'grp', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'grp', 8, 8);
INSERT INTO rbac_templates VALUES (3, 'grp', 7, 8);
INSERT INTO rbac_templates VALUES (3, 'grp', 6, 8);
INSERT INTO rbac_templates VALUES (3, 'grp', 5, 8);
INSERT INTO rbac_templates VALUES (3, 'frm', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'frm', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'frm', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'frm', 5, 8);
INSERT INTO rbac_templates VALUES (3, 'crs', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'crs', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'crs', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'crs', 8, 8);
INSERT INTO rbac_templates VALUES (2, 'crs', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'crs', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'crs', 8, 8);
INSERT INTO rbac_templates VALUES (2, 'crs', 7, 8);
INSERT INTO rbac_templates VALUES (4, 'cat', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'le', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'le', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'le', 8, 8);
INSERT INTO rbac_templates VALUES (4, 'le', 7, 8);
INSERT INTO rbac_templates VALUES (4, 'grp', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'grp', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'grp', 8, 8);
INSERT INTO rbac_templates VALUES (4, 'grp', 7, 8);
INSERT INTO rbac_templates VALUES (4, 'crs', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'crs', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'crs', 8, 8);
INSERT INTO rbac_templates VALUES (4, 'crs', 7, 8);
INSERT INTO rbac_templates VALUES (2, 'crs', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'crs', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'crs', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 6, 8);
INSERT INTO rbac_templates VALUES (2, 'cat', 5, 8);
INSERT INTO rbac_templates VALUES (3, 'crs', 7, 8);
INSERT INTO rbac_templates VALUES (3, 'crs', 6, 8);
INSERT INTO rbac_templates VALUES (3, 'crs', 5, 8);
INSERT INTO rbac_templates VALUES (3, 'cat', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'cat', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'cat', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'cat', 5, 8);
INSERT INTO rbac_templates VALUES (5, 'frm', 3, 8);
INSERT INTO rbac_templates VALUES (5, 'cat', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'cat', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'mail', 4, 8);
INSERT INTO rbac_templates VALUES (4, 'mail', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'mail', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'mail', 6, 8);
INSERT INTO rbac_templates VALUES (4, 'mail', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'adm', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'adm', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'adm', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'adm', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'adm', 5, 8);
INSERT INTO rbac_templates VALUES (2, 'typ', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'typ', 4, 8);
INSERT INTO rbac_templates VALUES (2, 'usrf', 1, 8);
INSERT INTO rbac_templates VALUES (2, 'usrf', 3, 8);
INSERT INTO rbac_templates VALUES (2, 'usrf', 2, 8);
INSERT INTO rbac_templates VALUES (2, 'usrf', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'file', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'lng', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'lng', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'lng', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'role', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'role', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'rolf', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'rolf', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'uset', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'uset', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'uset', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'usr', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'usr', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'usrf', 3, 8);
INSERT INTO rbac_templates VALUES (3, 'usrf', 2, 8);
INSERT INTO rbac_templates VALUES (3, 'usrf', 4, 8);
INSERT INTO rbac_templates VALUES (3, 'root', 4, 8);
INSERT INTO rbac_templates VALUES (4, 'root', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'root', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'uset', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'uset', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'usr', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'usr', 2, 8);
INSERT INTO rbac_templates VALUES (4, 'usrf', 3, 8);
INSERT INTO rbac_templates VALUES (4, 'usrf', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'le', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'lo', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'mail', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'role', 2, 8);
INSERT INTO rbac_templates VALUES (5, 'usr', 2, 8);
# --------------------------------------------------------

#
# Table structure for table `rbac_ua`
#

CREATE TABLE rbac_ua (
  usr_id int(11) NOT NULL default '0',
  rol_id int(11) NOT NULL default '0',
  default_role varchar(100) default NULL,
  PRIMARY KEY  (usr_id,rol_id)
) TYPE=MyISAM;

#
# Dumping data for table `rbac_ua`
#

INSERT INTO rbac_ua VALUES (6, 2, 'n');
INSERT INTO rbac_ua VALUES (156, 3, 'y');
INSERT INTO rbac_ua VALUES (157, 4, 'y');
INSERT INTO rbac_ua VALUES (158, 5, 'y');
INSERT INTO rbac_ua VALUES (168, 162, 'y');
INSERT INTO rbac_ua VALUES (170, 163, 'y');
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

INSERT INTO settings VALUES ('admin_firstname', 'System');
INSERT INTO settings VALUES ('admin_lastname', 'Administrator');
INSERT INTO settings VALUES ('admin_position', '');
INSERT INTO settings VALUES ('admin_title', '');
INSERT INTO settings VALUES ('babylon_path', '');
INSERT INTO settings VALUES ('city', '-your city-');
INSERT INTO settings VALUES ('convert_path', '');
INSERT INTO settings VALUES ('country', '-your country-');
INSERT INTO settings VALUES ('crs_enable', '');
INSERT INTO settings VALUES ('db_version', '1');
INSERT INTO settings VALUES ('email', '-your email-');
INSERT INTO settings VALUES ('errors', '');
INSERT INTO settings VALUES ('feedback', '');
INSERT INTO settings VALUES ('group_file_sharing', '');
INSERT INTO settings VALUES ('ilias_version', '3.0a');
INSERT INTO settings VALUES ('institution', '');
INSERT INTO settings VALUES ('inst_info', '');
INSERT INTO settings VALUES ('inst_name', '');
INSERT INTO settings VALUES ('java_path', '');
INSERT INTO settings VALUES ('language', 'en');
INSERT INTO settings VALUES ('ldap_basedn', '');
INSERT INTO settings VALUES ('ldap_enable', '');
INSERT INTO settings VALUES ('ldap_port', '');
INSERT INTO settings VALUES ('ldap_server', '');
INSERT INTO settings VALUES ('news', '');
INSERT INTO settings VALUES ('payment_system', '');
INSERT INTO settings VALUES ('phone', '');
INSERT INTO settings VALUES ('pub_section', '');
INSERT INTO settings VALUES ('street', '');
INSERT INTO settings VALUES ('unzip_path', '');
INSERT INTO settings VALUES ('zipcode', 'ghj');
INSERT INTO settings VALUES ('zip_path', '');
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

INSERT INTO tree VALUES (1, 1, 0, 1, 36, 1);
INSERT INTO tree VALUES (1, 7, 9, 27, 28, 3);
INSERT INTO tree VALUES (1, 8, 9, 29, 30, 3);
INSERT INTO tree VALUES (1, 9, 1, 26, 35, 2);
INSERT INTO tree VALUES (1, 10, 9, 31, 32, 3);
INSERT INTO tree VALUES (1, 11, 9, 33, 34, 3);
INSERT INTO tree VALUES (1, 164, 1, 10, 25, 1);
INSERT INTO tree VALUES (1, 165, 1, 2, 9, 1);
INSERT INTO tree VALUES (1, 166, 164, 23, 24, 2);
INSERT INTO tree VALUES (166, 166, 0, 1, 2, 1);
INSERT INTO tree VALUES (1, 167, 165, 7, 8, 2);
INSERT INTO tree VALUES (167, 167, 0, 1, 2, 1);
INSERT INTO tree VALUES (168, 168, 0, 1, 2, 1);
INSERT INTO tree VALUES (170, 170, 0, 1, 2, 1);
INSERT INTO tree VALUES (1, 172, 164, 21, 22, 2);
INSERT INTO tree VALUES (172, 172, 0, 1, 2, 1);
INSERT INTO tree VALUES (1, 176, 164, 11, 14, 2);
INSERT INTO tree VALUES (1, 177, 176, 12, 13, 3);
INSERT INTO tree VALUES (177, 177, 0, 1, 2, 1);
INSERT INTO tree VALUES (1, 178, 165, 5, 6, 2);
INSERT INTO tree VALUES (178, 178, 0, 1, 2, 1);
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

INSERT INTO usr_data VALUES (6, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'System', 'Administrator', 'The', 'm', 'ilias@yourserver.com', '2002-05-15 14:56:41', '2002-05-22 13:08:18', '0000-00-00 00:00:00');
INSERT INTO usr_data VALUES (158, 'gast', 'd4061b1486fe2da19dd578e8d970f7eb', 'Guest', '', '', '', 'gast@yourserver.com', '0000-00-00 00:00:00', '2002-08-30 14:05:39', '2002-08-30 14:05:39');
INSERT INTO usr_data VALUES (157, 'lerner', '3c1c7de8baffc419327b6439bba34217', 'Jim', 'Student', '', '', 'student@yourserver.com', '0000-00-00 00:00:00', '2002-08-30 14:05:05', '2002-08-30 14:05:05');
INSERT INTO usr_data VALUES (156, 'autor', '7a25cefdc710b155828e91df70fe7478', 'Bill', 'Teacher', '', '', 'teacher@yourserver.com', '0000-00-00 00:00:00', '2002-08-30 14:04:26', '2002-08-30 14:04:26');
INSERT INTO usr_data VALUES (168, 'lernera', '49379e5812c43fd6b53d80f104e0a1ca', 'Learner', 'A', '', '', 'a@ilias.net', '0000-00-00 00:00:00', '2002-12-24 00:49:38', '2002-12-24 00:49:38');
INSERT INTO usr_data VALUES (170, 'lernerb', 'f8e7f52e46ea0efbce0219225b526061', 'Learner', 'B', '', '', 'b@ilias.net', '0000-00-00 00:00:00', '2002-12-24 00:49:56', '2002-12-24 00:49:56');
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

INSERT INTO usr_pref VALUES (6, 'style', 'default');
INSERT INTO usr_pref VALUES (6, 'skin', 'default');
INSERT INTO usr_pref VALUES (6, 'language', 'en');
INSERT INTO usr_pref VALUES (156, 'language', 'en');
INSERT INTO usr_pref VALUES (156, 'skin', 'default');
INSERT INTO usr_pref VALUES (156, 'style', 'default');
INSERT INTO usr_pref VALUES (157, 'language', 'en');
INSERT INTO usr_pref VALUES (157, 'skin', 'default');
INSERT INTO usr_pref VALUES (158, 'language', 'en');
INSERT INTO usr_pref VALUES (157, 'style', 'default');
INSERT INTO usr_pref VALUES (158, 'skin', 'default');
INSERT INTO usr_pref VALUES (158, 'style', 'default');

