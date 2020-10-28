<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * setup file for ilias
 *
 * this file helps setting up ilias
 * main purpose is writing the ilias.ini to the filesystem
 * it can set up the database to if the settings are correct and the dbuser has the rights
 *
 * @author  Sascha Hofmann <shofmann@databay.de>
 * @version $Id$
 *
 * @package ilias-setup
 */
if (false === file_exists(__DIR__ . '/../libs/composer/vendor/autoload.php')) {
    echo 'Could not find composers "autoload.php". Try to run "composer install" in the directory ".libs/composer"';
    exit;
}

if (php_sapi_name() === "cli") {
    require_once(__DIR__ . "/cli.php");
} else {
    header("Content-Type: text/plain");
    echo <<<MSG
Dear user,

the GUI for the setup is abandoned as of ILIAS 7:

https://docu.ilias.de/goto_docu_wiki_wpage_6314_1357.html
https://docu.ilias.de/goto_docu_wiki_wpage_6391_1357.html
https://docu.ilias.de/goto_docu_wiki_wpage_6338_1357.html

It is replaced by a command line implementation of the setup:

https://docu.ilias.de/goto_docu_wiki_wpage_5890_1357.html
https://docu.ilias.de/goto_docu_wiki_wpage_6567_1357.html

while the functionality for the maintenance mode and the multi-
clients are removed completely as dicussed in the context of
the Setup Revision:

https://docu.ilias.de/goto_docu_wiki_wpage_4900_1357.html

Have a look into a detailed documentation of the setup in the
file setup/README.md or take a look into the ILIAS installation
instructions at docs/configuration/install.md.

Best regards!
MSG;
}
