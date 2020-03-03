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

if (ini_get('session.save_handler') != 'files') {
    throw new Exception("session.save_handler in php.ini must be configured to 'files'.");
}

chdir("..");
define('IL_INITIAL_WD', getcwd());
require_once "./setup/include/inc.setup_header.php";

$setup = new ilSetupGUI();
