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
* lo last. Displays last viewed LearningObject (db->dom->xsl->ITx)
*
* @author Arjan Ammerlaan <a.l.ammerlaan@web.de>
* @version $Id$
*
* @package ilias-core
*/
/**
* learning module presentation script
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
//	require_once "include/inc.header.php";
//	require_once "content/classes/class.ilLMPresentationGUI.php";
require_once "include/inc.header.php";
require_once "content/classes/class.ilLMPresentationGUI.php";		// for jumping to a lesson
require_once "classes/class.ilObjUser.php";							// for getLastVisitedLessons()


global $ilias;

// get all lm visited
$result = $ilias->account->getLastVisitedLessons();
if (sizeof($result) > 0)
{
	// ### WARNING: This script (lo_last.php) may only be called with the correct target ! Otherwise you would end up with double toolbars !
	header("location: content/lm_presentation.php?ref_id=".$result[0]["lm_id"]."&obj_id=".$result[0]["obj_id"]);
	exit();
}
else
{
	// no last lo for this user found, so inform user and link back to desktop
	header("location: start.php");
	exit();
}
?>
