/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

; <?php exit; ?>

[server]
start = ./login.php

[client]
name = 
description = 
access = 0

[db]
type = "pdo-mysql-innodb"
host = localhost
user = root
pass =
name = ilias

[auth]
table = usr_data
usercol = login
passcol = passwd

[language]
default = en
path = ./lang

[layout]
skin = default
style = delos
 
[session]
expire = 1800 

[system]
ROOT_FOLDER_ID = 1
SYSTEM_FOLDER_ID = 9
ROLE_FOLDER_ID = 8
MAIL_SETTINGS_ID = 12
MAXLENGTH_OBJ_TITLE = 65
MAXLENGTH_OBJ_DESC = 123

[cache]
activate_global_cache = 0
global_cache_service_type = 0

[log]
error_recipient = 