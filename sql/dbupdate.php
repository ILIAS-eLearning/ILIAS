<#2>
ALTER TABLE user_data ADD language VARCHAR(10) DEFAULT 'en' NOT NULL AFTER email; 
<#3>
#add mail table
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


