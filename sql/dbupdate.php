<#1>
#intial release of database
<#2>
# add table for user preferences
CREATE TABLE user_pref (
usr_id INT NOT NULL,
keyword CHAR(40) NOT NULL,
value_str CHAR(40),
value_int BIGINT,
PRIMARY KEY (usr_id,keyword)
);
<#3>
# change data type in object_data.type from ENUM to CHAR
ALTER TABLE object_data CHANGE type type CHAR(4) DEFAULT 'none' NOT NULL;
<#4>
# remove obsolete table object_types
DROP TABLE object_types;
<#5>
INSERT INTO settings (keyword, value_str) VALUES ('ilias_version', '3.0a');
<#6>
DROP TABLE settings;
CREATE TABLE `settings` (
`keyword` CHAR(50) NOT NULL, 
`value` CHAR(50) NOT NULL
); 
INSERT INTO settings (keyword, value) VALUES ('db_version', '6');
INSERT INTO settings (keyword, value) VALUES ('ilias_version', '3.0a');
INSERT INTO settings (keyword, value) VALUES ('inst_name', '');
INSERT INTO settings (keyword, value) VALUES ('inst_info', '');
INSERT INTO settings (keyword, value) VALUES ('convert_path', '');
INSERT INTO settings (keyword, value) VALUES ('zip_path', '');
INSERT INTO settings (keyword, value) VALUES ('unzip_path', '');
INSERT INTO settings (keyword, value) VALUES ('java_path', '');
INSERT INTO settings (keyword, value) VALUES ('babylon_path', '');
INSERT INTO settings (keyword, value) VALUES ('feedback', '');
INSERT INTO settings (keyword, value) VALUES ('errors', '');
INSERT INTO settings (keyword, value) VALUES ('pub_section','');
INSERT INTO settings (keyword, value) VALUES ('news','');
INSERT INTO settings (keyword, value) VALUES ('payment_system','');
INSERT INTO settings (keyword, value) VALUES ('group_file_sharing','');
INSERT INTO settings (keyword, value) VALUES ('crs_enable','');
INSERT INTO settings (keyword, value) VALUES ('ldap_enable','');
INSERT INTO settings (keyword, value) VALUES ('ldap_server','');
INSERT INTO settings (keyword, value) VALUES ('ldap_port','');
INSERT INTO settings (keyword, value) VALUES ('ldap_basedn','');
INSERT INTO settings (keyword, value) VALUES ('admin_firstname','');
INSERT INTO settings (keyword, value) VALUES ('admin_lastname','');
INSERT INTO settings (keyword, value) VALUES ('admin_title','');
INSERT INTO settings (keyword, value) VALUES ('admin_position','');
INSERT INTO settings (keyword, value) VALUES ('institution','');
INSERT INTO settings (keyword, value) VALUES ('street','');
INSERT INTO settings (keyword, value) VALUES ('zipcode','');
INSERT INTO settings (keyword, value) VALUES ('city','');
INSERT INTO settings (keyword, value) VALUES ('country','');
INSERT INTO settings (keyword, value) VALUES ('phone','');
INSERT INTO settings (keyword, value) VALUES ('email','');
