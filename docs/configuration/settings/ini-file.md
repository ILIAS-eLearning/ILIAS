# ILIAS INI-Files

ILIAS uses two types of ini-files to write some installation-depending settings
to the webserver. These files are located here:

- `/ilias.ini.php`
- `/data/YOUR_CLIENT/client.ini.php`

## ilias.ini.php

| Key/Group                       | Example Values                     | Description |
|:--------------------------------|:-----------------------------------|:------------|
| **[server]**                    |                                    |             |
| http_path                       | "http://trunk.local"               |             |
| absolute_path                   | "/var/www/ilias"                   |             |
| presetting                      | ""                                 |             |
| timezone                        | "Europe/Berlin"                    |             |
| **[clients]**                   |                                    |             |
| path                            | "data"                             |             |
| inifile                         | "client.ini.php"                   |             |
| datadir                         | "/var/iliasdata"                   |             |
| default                         | "trunk"                            |             |
| list                            | "0"                                |             |
| **[setup]**                     |                                    |             |
| pass                            | "63a9f0ea7bb98050796b649e85481845" |             |
| **[tools]**                     |                                    |             |
| convert                         | "/usr/bin/convert"                 |             |
| zip                             | "/usr/bin/zip"                     |             |
| unzip                           | "/usr/bin/unzip"                   |             |
| java                            | ""                                 |             |
| ffmpeg                          | ""                                 |             |
| ghostscript                     | "/usr/bin/gs"                      |             |
| latex                           | ""                                 |             |
| vscantype                       | "none"                             |             |
| scancommand                     | ""                                 |             |
| cleancommand                    | ""                                 |             |
| enable_system_styles_management | ""                                 |             |
| lessc                           | ""                                 |             |
| **[log]**                       |                                    |             |
| path                            | "/var/iliasdata"                   |             |
| file                            | "ilias.log"                        |             |
| enabled                         | "1"                                |             |
| level                           | "WARNING"                          |             |
| error_path                      | "/var/iliasdata"                   |             |
| [debian]                        |                                    |             |
| data_dir                        | "/var/opt/ilias"                   |             |
| log                             | "/var/log/ilias/ilias.log"         |             |
| convert                         | "/usr/bin/convert"                 |             |
| zip                             | "/usr/bin/zip"                     |             |
| unzip                           | "/usr/bin/unzip"                   |             |
| java                            | ""                                 |             |
| ffmpeg                          | "/usr/bin/ffmpeg"                  |             |
| [redhat]                        |                                    |             |
| data_dir                        | ""                                 |             |
| log                             | ""                                 |             |
| convert                         | ""                                 |             |
| zip                             | ""                                 |             |
| unzip                           | ""                                 |             |
| java                            | ""                                 |             |
| [suse]                          |                                    |             |
| data_dir                        | ""                                 |             |
| log                             | ""                                 |             |
| convert                         | ""                                 |             |
| zip                             | ""                                 |             |
| unzip                           | ""                                 |             |
| java                            | ""                                 |             |
| **[https]**                     |                                    |             |
| auto_https_detect_enabled       | "0"                                |             |
| auto_https_detect_header_name   | ""                                 |             |
| auto_https_detect_header_value  | ""                                 |             |

## client.ini.php

| Key/Group                        | Example Values                                                       | Description                                                                                                                                                                                            |
|:---------------------------------|:---------------------------------------------------------------------|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **[server]**                     |                                                                      |                                                                                                                                                                                                        |
| start                            | "./login.php"                                                        | Startscript of the ILIAS-Installation                                                                                                                                                                  |
| [client]                         |                                                                      |                                                                                                                                                                                                        |
| name                             | "trunk"                                                              | Unique Client-ID of the Installation                                                                                                                                                                   |
| description                      | Client-Description                                                   |                                                                                                                                                                                                        |
| access                           | "1"                                                                  | 0: Client is offline / 1: Client is online                                                                                                                                                             |
| **[db]**                         |                                                                      |                                                                                                                                                                                                        |
| type                             | "innodb"                                                             | indicates the selected DB-type                                                                                                                                                                         |
| host                             | "localhost"                                                          | Database-Host, IP or Hostname                                                                                                                                                                          |
| user                             | "root"                                                               | Database-Username                                                                                                                                                                                      |
| pass                             | ""                                                                   | Database-Password                                                                                                                                                                                      |
| name                             | "trunk"                                                              | Database-Name                                                                                                                                                                                          |
| port                             | ""                                                                   | Specific DB-Port, "" uses standardport                                                                                                                                                                 |
| structure_reload                 | "0"                                                                  | usually 0, when switching to 1, a Controle-Structure-Reload will be performed. Only switch to 1 if you kwnoe what you are doing                                                                        |
| **[auth]**                       |                                                                      |                                                                                                                                                                                                        |
| table                            | "usr_data"                                                           | Table-name of the used table for user data                                                                                                                                                             |
| usercol                          | "login"                                                              | column with the username-values                                                                                                                                                                        |
| passcol                          | "passwd"                                                             | column with user-password                                                                                                                                                                              |
| **[language]**                   |                                                                      |                                                                                                                                                                                                        |
| default                          | "en"                                                                 | default (system-) language                                                                                                                                                                             |
| path                             | "./lang"                                                             | Path to language-files                                                                                                                                                                                 |
| **[layout]**                     |                                                                      |                                                                                                                                                                                                        |
| skin                             | "default"                                                            | ID of the standard skin                                                                                                                                                                                |
| style                            | "delos"                                                              | ID of the standrd style                                                                                                                                                                                |
| **[session]**                    |                                                                      |                                                                                                                                                                                                        |
| expire                           | "1800"                                                               | (PHP-) Session-duration                                                                                                                                                                                |
| **[system]**                     |                                                                      |                                                                                                                                                                                                        |
| ROOT_FOLDER_ID                   | "1"                                                                  | Ref-ID of the "root-folder" a.k.a Repository                                                                                                                                                           |
| SYSTEM_FOLDER_ID                 | "9"                                                                  | Ref-ID of the "system-folder" a.k.a Administration                                                                                                                                                     |
| ROLE_FOLDER_ID                   | "8"                                                                  | Ref-ID of the "role-folder" a.k.a Roles                                                                                                                                                                |
| MAIL_SETTINGS_ID                 | "12"                                                                 |                                                                                                                                                                                                        |
| MAXLENGTH_OBJ_TITLE              | "65"                                                                 |                                                                                                                                                                                                        |
| MAXLENGTH_OBJ_DESC               | "123"                                                                |                                                                                                                                                                                                        |
| DEBUG                            | "0"                                                                  |                                                                                                                                                                                                        |
| DEVMODE                          | "0"                                                                  |                                                                                                                                                                                                        |
| **[cache]**                      |                                                                      |                                                                                                                                                                                                        |
| activate_global_cache            | "1"                                                                  | activates "1" or deactivates "0" the global chaching service. when deactivated, ILIAS used direct database-access                                                                                      |
| global_cache_service_type        | "4"                                                                  | defines the used service type (if possible and installed ): 0: Static (raw PHP, no specific caching-service), 1: xCache, 2: Memcached, 3: APCu. Please configure using ilias-setup (./setup/setup.php) |
| log_level                        | ""                                                                   | cachign log-level, disabled by default. 0: None, 1: "Shy", 2: "Normal", 3: "Chatty"                                                                                                                    |
| **[log]**                        |                                                                      |                                                                                                                                                                                                        |
| error_recipient                  | ""                                                                   |                                                                                                                                                                                                        |
| **[cache_activated_components]** | Defines which components acre cached by the selected caching-service |                                                                                                                                                                                                        |
| clng                             | "1"                                                                  | language-data                                                                                                                                                                                          |
| obj_def                          | "1"                                                                  | object-definitions                                                                                                                                                                                     |
| ilctrl                           | "1"                                                                  | controle-structure                                                                                                                                                                                     |
| comp                             | "1"                                                                  | components                                                                                                                                                                                             |
| tpl                              | "1"                                                                  | template-instances                                                                                                                                                                                     |
| tpl_blocks                       | "1"                                                                  | template-blocks                                                                                                                                                                                        |
| tpl_variables                    | "1"                                                                  | temnplate-variables                                                                                                                                                                                    |
| events                           | "1"                                                                  | events                                                                                                                                                                                                 |
| **[file_access]**                |                                                                      |                                                                                                                                                                                                        |
| disable_ascii                    | "0"                                                                  | set to 1 to disable the convertion of filenames to ASCII. Setting this to 1 can lead to problems with mobile devices and is not recommended                                                            |
