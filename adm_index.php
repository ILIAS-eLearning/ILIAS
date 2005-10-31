<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
 * admin objects frameset
 * 
 * this file decides if a frameset is used or not
 * 
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias-core
 * @version $Id$
*/
require_once "./include/inc.header.php";


// limit access only to admins
#if (!$rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID))
include_once './classes/class.ilMainMenuGUI.php';

if(!ilMainMenuGUI::_checkAdministrationPermission())
{
	$ilias->raiseError("You are not entitled to access this page!",$ilias->error_obj->WARNING);
}

//look if there is a file tpl.adm.html (containing a frameset)
//$start_template = $ilias->tplPath.$ilias->account->getPref("skin")."/tpl.adm.html"; 

//if (file_exists($start_template))
//{
	$tpl = new ilTemplate("tpl.adm.html", false, false);
	$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
	$tpl->setVariable("REF_ID", ROOT_FOLDER_ID);
	$tpl->parseCurrentBlock();
	$tpl->show();
//}
//else
//{
//	header("location: adm_object.php?expand=1");
//	exit;
//}
?>
