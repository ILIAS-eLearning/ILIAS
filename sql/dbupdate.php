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