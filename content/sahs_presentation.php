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
* scorm learning module presentation script
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

define("ILIAS_MODULE", "content");
chdir("..");
require_once "./include/inc.header.php";
require_once "classes/class.ilObjectGUI.php";

$lng->loadLanguageModule("content");

	$ref_id=$_GET["ref_id"];
	
	//read type of cbt
	$q = "SELECT type FROM object_data od, object_reference oref WHERE oref.ref_id=$ref_id AND oref.obj_id=od.obj_id";
	$lm_set = $ilias->db->query($q);
	$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);
	$type=$lm_rec["type"];	

	switch ($type) {
		case "slm":
					//SCORM
					require_once "./content/classes/SCORM/class.ilSCORMPresentationGUI.php";
					$scorm_presentation = new ilSCORMPresentationGUI();
					break;
		case "alm":
					//AICC
					require_once "./content/classes/AICC/class.ilAICCPresentationGUI.php";
					$aicc_presentation = new ilAICCPresentationGUI();
					break;
		case "hlm":
					//HACP
					require_once "./content/classes/HACP/class.ilHACPPresentationGUI.php";
					$hacp_presentation = new ilHACPPresentationGUI();
					break;
		default:
					//unknown type
					$ilias->raiseError($lng->txt("unknown type in sahs_presentation"),$ilias->error_obj->MESSAGE);

	}


?>
