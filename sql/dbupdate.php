<#1>
#intial release of database
<#2>
#setup lo_xml_database
CREATE TABLE lo_attribute (
  attribute_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0'
) TYPE=MyISAM;

CREATE TABLE lo_attribute_name_leaf (
  leaf_id int(11) NOT NULL default '0',
  attribute_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  leaf_text varchar(128) NOT NULL default ''
) TYPE=MyISAM;

CREATE TABLE lo_attribute_namespace_leaf (
  leaf_id int(11) NOT NULL default '0',
  attribute_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL
) TYPE=MyISAM;

CREATE TABLE lo_attribute_value_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  attribute_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  leaf_text varchar(255) NOT NULL default '',
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

CREATE TABLE lo_cdata_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

CREATE TABLE lo_comment_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

CREATE TABLE lo_element_name_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text varchar(128) NOT NULL default '',
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

CREATE TABLE lo_element_namespace_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

CREATE TABLE lo_entity_reference_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

CREATE TABLE lo_node_type (
  node_type_id int(11) NOT NULL auto_increment,
  description varchar(50) default NULL,
  lft_delimiter varchar(10) default NULL,
  rgt_delimiter varchar(10) default NULL,
  PRIMARY KEY  (node_type_id)
) TYPE=MyISAM;

INSERT INTO lo_node_type VALUES (1, 'ELEMENT_NODE', '<', '>');
INSERT INTO lo_node_type VALUES (2, 'ATTRIBUTE_NODE(not used)', '"', '"');
INSERT INTO lo_node_type VALUES (3, 'TEXT_NODE', NULL, NULL);
INSERT INTO lo_node_type VALUES (5, 'ENTITY_REF_NODE', '&', ';');
INSERT INTO lo_node_type VALUES (4, 'CDATA_SECTION_NODE', '<![CDATA[', ']]>');
INSERT INTO lo_node_type VALUES (7, 'PI_NODE', '<?', '?>');
INSERT INTO lo_node_type VALUES (8, 'COMMENT_NODE', '<!--', '-->');
INSERT INTO lo_node_type VALUES (9, 'DOCUMENT_NODE', NULL, NULL);
INSERT INTO lo_node_type VALUES (10, 'DOCUMENT_TYPE_NODE', NULL, NULL);
INSERT INTO lo_node_type VALUES (6, 'ENTITY_NODE', '&', ';');

CREATE TABLE lo_pi_data_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

CREATE TABLE lo_pi_target_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

CREATE TABLE lo_text_leaf (
  leaf_id int(11) NOT NULL auto_increment,
  node_id int(11) NOT NULL default '0',
  leaf_text text NOT NULL,
  PRIMARY KEY  (leaf_id)
) TYPE=MyISAM;

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
<#3>
#create language table and pre-install english language support
DROP TABLE IF EXISTS languages;
CREATE TABLE languages (
  lang_key char(2) NOT NULL default '',
  installed enum('y','n') NOT NULL default 'n',
  last_update timestamp(14) NOT NULL,
  PRIMARY KEY  (lang_key)
) TYPE=MyISAM;

INSERT INTO languages VALUES ('es', 'n', 20021210150133);
INSERT INTO languages VALUES ('de', 'n', 20021210150137);
INSERT INTO languages VALUES ('it', 'n', 20021210150136);
INSERT INTO languages VALUES ('en', 'y', 20021210143417);

DROP TABLE IF EXISTS lng_data;
CREATE TABLE lng_data (
  module varchar(30) NOT NULL default '',
  identifier varchar(50) binary NOT NULL default '',
  lang_key char(2) NOT NULL default '',
  value blob NOT NULL,
  PRIMARY KEY  (module,identifier,lang_key),
  KEY module (module),
  KEY lang_key (lang_key)
) TYPE=MyISAM;
INSERT INTO lng_data VALUES ('common', 'update_language', 'en', 'update language');
INSERT INTO lng_data VALUES ('common', 'check_language', 'en', 'check language');
INSERT INTO lng_data VALUES ('common', 'change_language', 'en', 'change language');
INSERT INTO lng_data VALUES ('common', 'lng', 'en', 'language');
INSERT INTO lng_data VALUES ('common', 'rolt', 'en', 'role template');
INSERT INTO lng_data VALUES ('common', 'lngf', 'en', 'language folder');
INSERT INTO lng_data VALUES ('common', 'rolf', 'en', 'role folder');
INSERT INTO lng_data VALUES ('common', 'file', 'en', 'file');
INSERT INTO lng_data VALUES ('common', 'crs', 'en', 'course');
INSERT INTO lng_data VALUES ('common', 'grp', 'en', 'group');
INSERT INTO lng_data VALUES ('common', 'le', 'en', 'learning module');
INSERT INTO lng_data VALUES ('common', 'cat', 'en', 'category');
INSERT INTO lng_data VALUES ('common', 'obj', 'en', 'object');
INSERT INTO lng_data VALUES ('common', 'frm', 'en', 'forum');
INSERT INTO lng_data VALUES ('common', 'roles', 'en', 'roles');
INSERT INTO lng_data VALUES ('common', 'uninstall', 'en', 'uninstall');
INSERT INTO lng_data VALUES ('common', 'permission', 'en', 'permission');
INSERT INTO lng_data VALUES ('common', 'obj_owner', 'en', 'This Object is owned by');
INSERT INTO lng_data VALUES ('common', 'you_may_add_local_roles', 'en', 'you may add local roles');
INSERT INTO lng_data VALUES ('common', 'required_field', 'en', 'required field');
INSERT INTO lng_data VALUES ('common', 'clear', 'en', 'clear');
INSERT INTO lng_data VALUES ('common', 'copy', 'en', 'copy');
INSERT INTO lng_data VALUES ('common', 'paste', 'en', 'paste');
INSERT INTO lng_data VALUES ('common', 'cut', 'en', 'cut');
INSERT INTO lng_data VALUES ('common', 'obj_not_found', 'en', 'object not found');
INSERT INTO lng_data VALUES ('common', 'zip', 'en', 'zip code');
INSERT INTO lng_data VALUES ('common', 'your_message', 'en', 'your message');
INSERT INTO lng_data VALUES ('common', 'yes', 'en', 'yes');
INSERT INTO lng_data VALUES ('common', 'write', 'en', 'write');
INSERT INTO lng_data VALUES ('common', 'visible_layers', 'en', 'visible layers');
INSERT INTO lng_data VALUES ('common', 'username', 'en', 'username');
INSERT INTO lng_data VALUES ('common', 'users', 'en', 'users');
INSERT INTO lng_data VALUES ('common', 'usr_agreement', 'en', 'user agreement');
INSERT INTO lng_data VALUES ('common', 'usr_skin', 'en', 'user skin');
INSERT INTO lng_data VALUES ('common', 'usr_style', 'en', 'usr style');
INSERT INTO lng_data VALUES ('common', 'version', 'en', 'version');
INSERT INTO lng_data VALUES ('common', 'view_content', 'en', 'view content');
INSERT INTO lng_data VALUES ('common', 'userdata', 'en', 'userdata');
INSERT INTO lng_data VALUES ('common', 'url_description', 'en', 'url description');
INSERT INTO lng_data VALUES ('common', 'url', 'en', 'url');
INSERT INTO lng_data VALUES ('common', 'update_applied', 'en', 'update applied');
INSERT INTO lng_data VALUES ('common', 'unread', 'en', 'unread');
INSERT INTO lng_data VALUES ('common', 'unknown', 'en', 'UNKNOWN');
INSERT INTO lng_data VALUES ('common', 'uid', 'en', 'uid');
INSERT INTO lng_data VALUES ('common', 'type_your_message_here', 'en', 'type your message here');
INSERT INTO lng_data VALUES ('common', 'type', 'en', 'type');
INSERT INTO lng_data VALUES ('common', 'trash', 'en', 'trash');
INSERT INTO lng_data VALUES ('common', 'tpl_path', 'en', 'template path');
INSERT INTO lng_data VALUES ('common', 'total', 'en', 'total');
INSERT INTO lng_data VALUES ('common', 'to', 'en', 'to:');
INSERT INTO lng_data VALUES ('common', 'title', 'en', 'title');
INSERT INTO lng_data VALUES ('common', 'time', 'en', 'time');
INSERT INTO lng_data VALUES ('common', 'test_intern', 'en', 'test intern');
INSERT INTO lng_data VALUES ('common', 'system_message', 'en', 'system message');
INSERT INTO lng_data VALUES ('common', 'system_language', 'en', 'system language');
INSERT INTO lng_data VALUES ('common', 'system_groups', 'en', 'system groups');
INSERT INTO lng_data VALUES ('common', 'system_grp', 'en', 'system group');
INSERT INTO lng_data VALUES ('common', 'system', 'en', 'system');
INSERT INTO lng_data VALUES ('common', 'summary', 'en', 'summary');
INSERT INTO lng_data VALUES ('common', 'subscription', 'en', 'subscription');
INSERT INTO lng_data VALUES ('common', 'submit', 'en', 'submit');
INSERT INTO lng_data VALUES ('common', 'subject', 'en', 'subject');
INSERT INTO lng_data VALUES ('common', 'subchapter_new', 'en', 'new subchapter');
INSERT INTO lng_data VALUES ('common', 'subcat_name', 'en', 'subcategory name');
INSERT INTO lng_data VALUES ('common', 'student', 'en', 'student');
INSERT INTO lng_data VALUES ('common', 'street', 'en', 'street');
INSERT INTO lng_data VALUES ('common', 'structure', 'en', 'structure');
INSERT INTO lng_data VALUES ('common', 'step', 'en', 'step');
INSERT INTO lng_data VALUES ('common', 'status', 'en', 'status');
INSERT INTO lng_data VALUES ('common', 'startpage', 'en', 'startpage');
INSERT INTO lng_data VALUES ('common', 'signature', 'en', 'signature');
INSERT INTO lng_data VALUES ('common', 'show_owner', 'en', 'show owner');
INSERT INTO lng_data VALUES ('common', 'setup_welcome', 'en', 'Welcome to the setup of ILIAS.<br>To make Ilias operatable please fill out the following fields.<br>ILIAS will install the database with the given parameters after pressing <submit>.');
INSERT INTO lng_data VALUES ('common', 'setup', 'en', 'setup');
INSERT INTO lng_data VALUES ('common', 'setup_ready', 'en', 'setup is ready');
INSERT INTO lng_data VALUES ('common', 'set_online', 'en', 'set online');
INSERT INTO lng_data VALUES ('common', 'set_offline', 'en', 'set offline');
INSERT INTO lng_data VALUES ('common', 'set', 'en', 'set');
INSERT INTO lng_data VALUES ('common', 'server', 'en', 'server');
INSERT INTO lng_data VALUES ('common', 'server_software', 'en', 'server software');
INSERT INTO lng_data VALUES ('common', 'sequences', 'en', 'sequences');
INSERT INTO lng_data VALUES ('common', 'sequence', 'en', 'sequence');
INSERT INTO lng_data VALUES ('common', 'sent', 'en', 'sent');
INSERT INTO lng_data VALUES ('common', 'send', 'en', 'send');
INSERT INTO lng_data VALUES ('common', 'sender', 'en', 'sender');
INSERT INTO lng_data VALUES ('common', 'select_all', 'en', 'select all');
INSERT INTO lng_data VALUES ('common', 'selected', 'en', 'selected');
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
INSERT INTO lng_data VALUES ('common', 'reset', 'en', 'reset');
INSERT INTO lng_data VALUES ('common', 'retype_password', 'en', 'retype password');
INSERT INTO lng_data VALUES ('common', 'right', 'en', 'right');
INSERT INTO lng_data VALUES ('common', 'rights', 'en', 'rights');
INSERT INTO lng_data VALUES ('common', 'reply', 'en', 'reply');
INSERT INTO lng_data VALUES ('common', 'refresh', 'en', 'refresh');
INSERT INTO lng_data VALUES ('common', 'refresh_list', 'en', 'refresh list');
INSERT INTO lng_data VALUES ('common', 'read', 'en', 'read');
INSERT INTO lng_data VALUES ('common', 'recipient', 'en', 'recipient');
INSERT INTO lng_data VALUES ('common', 'quit', 'en', 'quit');
INSERT INTO lng_data VALUES ('common', 'question', 'en', 'question');
INSERT INTO lng_data VALUES ('common', 'publishing_organisation', 'en', 'publishing organisation');
INSERT INTO lng_data VALUES ('common', 'published', 'en', 'published');
INSERT INTO lng_data VALUES ('common', 'publication_date', 'en', 'publication date');
INSERT INTO lng_data VALUES ('common', 'pub_section', 'en', 'public section');
INSERT INTO lng_data VALUES ('common', 'publication', 'en', 'publication');
INSERT INTO lng_data VALUES ('common', 'profile', 'en', 'profile');
INSERT INTO lng_data VALUES ('common', 'print', 'en', 'print');
INSERT INTO lng_data VALUES ('common', 'presentation_options', 'en', 'presentation options');
INSERT INTO lng_data VALUES ('common', 'position', 'en', 'position');
INSERT INTO lng_data VALUES ('common', 'phrase', 'en', 'phrase');
INSERT INTO lng_data VALUES ('common', 'port', 'en', 'port');
INSERT INTO lng_data VALUES ('common', 'phone', 'en', 'phone');
INSERT INTO lng_data VALUES ('common', 'personal_profile', 'en', 'personal profile');
INSERT INTO lng_data VALUES ('common', 'personal_desktop', 'en', 'personal desktop');
INSERT INTO lng_data VALUES ('common', 'perm_settings', 'en', 'permissions');
INSERT INTO lng_data VALUES ('common', 'payment_system', 'en', 'payment system');
INSERT INTO lng_data VALUES ('common', 'path_to_zip', 'en', 'path to zip');
INSERT INTO lng_data VALUES ('common', 'path_to_unzip', 'en', 'path to unzip');
INSERT INTO lng_data VALUES ('common', 'path_to_convert', 'en', 'path to convert');
INSERT INTO lng_data VALUES ('common', 'path_to_java', 'en', 'path to java');
INSERT INTO lng_data VALUES ('common', 'path_to_babylon', 'en', 'path to babylon');
INSERT INTO lng_data VALUES ('common', 'path', 'en', 'path');
INSERT INTO lng_data VALUES ('common', 'password', 'en', 'password');
INSERT INTO lng_data VALUES ('common', 'page_edit', 'en', 'edit page');
INSERT INTO lng_data VALUES ('common', 'owner', 'en', 'owner');
INSERT INTO lng_data VALUES ('common', 'page', 'en', 'page');
INSERT INTO lng_data VALUES ('common', 'overview', 'en', 'overview');
INSERT INTO lng_data VALUES ('common', 'options', 'en', 'options');
INSERT INTO lng_data VALUES ('common', 'other', 'en', 'other');
INSERT INTO lng_data VALUES ('common', 'optimize', 'en', 'optimize');
INSERT INTO lng_data VALUES ('common', 'online_chapter', 'en', 'online chapter');
INSERT INTO lng_data VALUES ('common', 'online_version', 'en', 'online version');
INSERT INTO lng_data VALUES ('common', 'old', 'en', 'old');
INSERT INTO lng_data VALUES ('common', 'objects', 'en', 'objects');
INSERT INTO lng_data VALUES ('common', 'offline_version', 'en', 'offline version');
INSERT INTO lng_data VALUES ('common', 'not_installed', 'en', 'not installed');
INSERT INTO lng_data VALUES ('common', 'normal', 'en', 'normal');
INSERT INTO lng_data VALUES ('common', 'none', 'en', 'none');
INSERT INTO lng_data VALUES ('common', 'no_objects', 'en', 'no objects');
INSERT INTO lng_data VALUES ('common', 'no_title', 'en', 'no title');
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
INSERT INTO lng_data VALUES ('common', 'msg_changes_ok', 'en', 'the changes were ok');
INSERT INTO lng_data VALUES ('common', 'msg_failed', 'en', 'sorry, action failed');
INSERT INTO lng_data VALUES ('common', 'move_to', 'en', 'move to');
INSERT INTO lng_data VALUES ('common', 'migrate', 'en', 'migrate');
INSERT INTO lng_data VALUES ('common', 'message_to', 'en', 'message to:');
INSERT INTO lng_data VALUES ('common', 'message_content', 'en', 'message content');
INSERT INTO lng_data VALUES ('common', 'message', 'en', 'message');
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
INSERT INTO lng_data VALUES ('common', 'lo_edit', 'en', 'edit learning object');
INSERT INTO lng_data VALUES ('common', 'lo_new', 'en', 'new learning object');
INSERT INTO lng_data VALUES ('common', 'lo_categories', 'en', 'lo categories');
INSERT INTO lng_data VALUES ('common', 'lo_available', 'en', 'available learning objects');
INSERT INTO lng_data VALUES ('common', 'literature_bookmarks', 'en', 'literature bookmarks');
INSERT INTO lng_data VALUES ('common', 'lo', 'en', 'learning object');
INSERT INTO lng_data VALUES ('common', 'literature', 'en', 'literature');
INSERT INTO lng_data VALUES ('common', 'list_of_questions', 'en', 'question list');
INSERT INTO lng_data VALUES ('common', 'linked_pages', 'en', 'linked pages');
INSERT INTO lng_data VALUES ('common', 'list_of_pages', 'en', 'pages list');
INSERT INTO lng_data VALUES ('common', 'link', 'en', 'link');
INSERT INTO lng_data VALUES ('common', 'linebreak', 'en', 'linebreak');
INSERT INTO lng_data VALUES ('common', 'level', 'en', 'level');
INSERT INTO lng_data VALUES ('common', 'ldap', 'en', 'LDAP');
INSERT INTO lng_data VALUES ('common', 'lastname', 'en', 'lastname');
INSERT INTO lng_data VALUES ('common', 'languages_generate_from_upload', 'en', 'generate languages from uploaded file');
INSERT INTO lng_data VALUES ('common', 'last_change', 'en', 'last change');
INSERT INTO lng_data VALUES ('common', 'languages', 'en', 'languages');
INSERT INTO lng_data VALUES ('common', 'languages_generate_from_file', 'en', 'generate languages from file');
INSERT INTO lng_data VALUES ('common', 'language', 'en', 'language');
INSERT INTO lng_data VALUES ('common', 'keywords', 'en', 'keywords');
INSERT INTO lng_data VALUES ('common', 'lang_path', 'en', 'language path');
INSERT INTO lng_data VALUES ('common', 'item', 'en', 'item');
INSERT INTO lng_data VALUES ('common', 'ip_address', 'en', 'IP address');
INSERT INTO lng_data VALUES ('common', 'institution', 'en', 'institution');
INSERT INTO lng_data VALUES ('common', 'installed', 'en', 'installed');
INSERT INTO lng_data VALUES ('common', 'inst_name', 'en', 'installation name');
INSERT INTO lng_data VALUES ('common', 'install', 'en', 'install');
INSERT INTO lng_data VALUES ('common', 'inst_info', 'en', 'installation info');
INSERT INTO lng_data VALUES ('common', 'inst_id', 'en', 'installation ID');
INSERT INTO lng_data VALUES ('common', 'inifile', 'en', 'INI-file');
INSERT INTO lng_data VALUES ('common', 'information_abbr', 'en', 'info');
INSERT INTO lng_data VALUES ('common', 'inbox', 'en', 'inbox');
INSERT INTO lng_data VALUES ('common', 'ilias_version', 'en', 'ILIAS version');
INSERT INTO lng_data VALUES ('common', 'id', 'en', 'ID');
INSERT INTO lng_data VALUES ('common', 'http_path', 'en', 'http path');
INSERT INTO lng_data VALUES ('common', 'host', 'en', 'host');
INSERT INTO lng_data VALUES ('common', 'guest', 'en', 'guest');
INSERT INTO lng_data VALUES ('common', 'help', 'en', 'help');
INSERT INTO lng_data VALUES ('common', 'groupscope', 'en', 'groupscope');
INSERT INTO lng_data VALUES ('common', 'groups', 'en', 'groups');
INSERT INTO lng_data VALUES ('common', 'groupname', 'en', 'groupname');
INSERT INTO lng_data VALUES ('common', 'group_filesharing', 'en', 'group file sharing');
INSERT INTO lng_data VALUES ('common', 'glossary', 'en', 'glossary');
INSERT INTO lng_data VALUES ('common', 'generate', 'en', 'generate');
INSERT INTO lng_data VALUES ('common', 'gender', 'en', 'gender');
INSERT INTO lng_data VALUES ('common', 'from', 'en', 'from:');
INSERT INTO lng_data VALUES ('common', 'functions', 'en', 'functions');
INSERT INTO lng_data VALUES ('common', 'forward', 'en', 'forward');
INSERT INTO lng_data VALUES ('common', 'forums_of_your_groups', 'en', 'forums of your groups');
INSERT INTO lng_data VALUES ('common', 'forums_available', 'en', 'available forums');
INSERT INTO lng_data VALUES ('common', 'folder', 'en', 'folder');
INSERT INTO lng_data VALUES ('common', 'folders', 'en', 'folders');
INSERT INTO lng_data VALUES ('common', 'forename', 'en', 'forename');
INSERT INTO lng_data VALUES ('common', 'forum', 'en', 'forum');
INSERT INTO lng_data VALUES ('common', 'forum_new', 'en', 'new forum');
INSERT INTO lng_data VALUES ('common', 'forums', 'en', 'forums');
INSERT INTO lng_data VALUES ('common', 'firstname', 'en', 'firstname');
INSERT INTO lng_data VALUES ('common', 'fill_out_all_required_fields', 'en', 'please fill out all required fields');
INSERT INTO lng_data VALUES ('common', 'file_version', 'en', 'version provided in file');
INSERT INTO lng_data VALUES ('common', 'files_location', 'en', 'files location');
INSERT INTO lng_data VALUES ('common', 'feedback', 'en', 'feedback');
INSERT INTO lng_data VALUES ('common', 'feedback_recipient', 'en', 'feedback recipient');
INSERT INTO lng_data VALUES ('common', 'faq_exercise', 'en', 'faq exercise');
INSERT INTO lng_data VALUES ('common', 'execute', 'en', 'execute');
INSERT INTO lng_data VALUES ('common', 'error_recipient', 'en', 'error recipient');
INSERT INTO lng_data VALUES ('common', 'err_wrong_login', 'en', 'wrong login');
INSERT INTO lng_data VALUES ('common', 'enumerate', 'en', 'enumerate');
INSERT INTO lng_data VALUES ('common', 'err_create_database_failed', 'en', 'creation of database failed');
INSERT INTO lng_data VALUES ('common', 'employee', 'en', 'employee');
INSERT INTO lng_data VALUES ('common', 'enable', 'en', 'enable');
INSERT INTO lng_data VALUES ('common', 'email', 'en', 'email');
INSERT INTO lng_data VALUES ('common', 'editor', 'en', 'editor');
INSERT INTO lng_data VALUES ('common', 'edit_properties', 'en', 'edit properties');
INSERT INTO lng_data VALUES ('common', 'edit_data', 'en', 'edit data');
INSERT INTO lng_data VALUES ('common', 'drafts', 'en', 'drafts');
INSERT INTO lng_data VALUES ('common', 'edit', 'en', 'edit');
INSERT INTO lng_data VALUES ('common', 'desired_password', 'en', 'desired password');
INSERT INTO lng_data VALUES ('common', 'description', 'en', 'description');
INSERT INTO lng_data VALUES ('common', 'delete_selected', 'en', 'delete selected');
INSERT INTO lng_data VALUES ('common', 'deleted', 'en', 'deleted');
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
INSERT INTO lng_data VALUES ('common', 'database', 'en', 'database');
INSERT INTO lng_data VALUES ('common', 'database_exists', 'en', 'database exists');
INSERT INTO lng_data VALUES ('common', 'current_password', 'en', 'current password');
INSERT INTO lng_data VALUES ('common', 'crs_management_system', 'en', 'crs management system');
INSERT INTO lng_data VALUES ('common', 'crs_available', 'en', 'available courses');
INSERT INTO lng_data VALUES ('common', 'create_in', 'en', 'create in');
INSERT INTO lng_data VALUES ('common', 'create', 'en', 'create');
INSERT INTO lng_data VALUES ('common', 'courses', 'en', 'courses');
INSERT INTO lng_data VALUES ('common', 'country', 'en', 'country');
INSERT INTO lng_data VALUES ('common', 'course', 'en', 'course');
INSERT INTO lng_data VALUES ('common', 'contact_information', 'en', 'contact information');
INSERT INTO lng_data VALUES ('common', 'compose', 'en', 'compose');
INSERT INTO lng_data VALUES ('common', 'city', 'en', 'city');
INSERT INTO lng_data VALUES ('common', 'comma_separated', 'en', 'comma separated');
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
INSERT INTO lng_data VALUES ('common', 'lang_en', 'en', 'English');
INSERT INTO lng_data VALUES ('common', 'lang_dk', 'en', 'D&auml;nish');
INSERT INTO lng_data VALUES ('common', 'lang_pl', 'en', 'Polnish');
INSERT INTO lng_data VALUES ('common', 'lang_se', 'en', 'Schwedish');
INSERT INTO lng_data VALUES ('common', 'lang_no', 'en', 'Norwegish');
INSERT INTO lng_data VALUES ('common', 'lang_es', 'en', 'Spanish');
INSERT INTO lng_data VALUES ('common', 'lang_fr', 'en', 'Franz&ouml;sish');
INSERT INTO lng_data VALUES ('common', 'lang_it', 'en', 'Italienish');
INSERT INTO lng_data VALUES ('common', 'lang_id', 'en', 'Indonesish');
INSERT INTO lng_data VALUES ('common', 'lang_xx', 'en', 'benutzerdefiniert');
<#4>
# CREATE NEW ROOT FOLDER OBJECT
INSERT INTO object_data VALUES ('33', 'typ', 'root', 'Root Folder Object', '0', now(), now());
UPDATE object_data SET type = 'root' WHERE obj_id = '1' AND type = 'cat';
INSERT INTO rbac_ta VALUES ('33','1');
INSERT INTO rbac_ta VALUES ('33','2');
INSERT INTO rbac_ta VALUES ('33','3');
INSERT INTO rbac_ta VALUES ('33','4');
