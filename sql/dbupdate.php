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
