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