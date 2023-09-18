; <?php exit; ?>
[server]
start = "./login.php"

[client]
name = "ilias"
description = "ILIAS"
access = "1"

[db]
type = "innodb"
host = "localhost"
user = "ilias"
pass = "ca44c5a63cbfb162cca43982c9aa5581"
name = "ilias"
port = "3306"

[auth]
table = "usr_data"
usercol = "login"
passcol = "passwd"

[language]
default = "en"
path = "./lang"

[layout]
skin = "default"
style = "delos"

[session]
expire = "1800"

[system]
USE_WHOOPS = "1"
DEVMODE = "1"
ROOT_FOLDER_ID = "1"
SYSTEM_FOLDER_ID = "9"
ROLE_FOLDER_ID = "8"
MAIL_SETTINGS_ID = "12"
MAXLENGTH_OBJ_TITLE = "65"
MAXLENGTH_OBJ_DESC = "123"

[cache]
activate_global_cache = "0"
global_cache_service_type = "static"

[log]
error_recipient = ""

[cache_activated_components]
