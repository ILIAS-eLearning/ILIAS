<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* displays a user profile
*
* @author	Jens Conze <jc@databay.de>
* @version	$Id$
*
*/

chdir("..");

define('ILIAS_MODULE','Modules/Chat');

require_once './include/inc.header.php';
require_once './Services/User/classes/class.ilObjUserGUI.php';

$tpl->addBlockFile('CONTENT', 'content', 'tpl.chat_profile_view.html','Modules/Chat');

$user = new ilObjUserGUI('',$_GET['user'], false, false);
$user->insertPublicProfile('USR_PROFILE','usr_profile');

$tpl->setVariable('TXT_CLOSE_WINDOW', $user->lng->txt('close_window'));
$tpl->show();
?>