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

require_once ("content/classes/SCORM/class.ilSCORMItem.php");
require_once ("content/classes/SCORM/class.ilSCORMResource.php");
require_once ("classes/class.ilObjSCORMLearningModule.php");

/**
* GUI class for SCORM Items
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilSCORMItemGUI
* @package content
*/
class ilSCORMItemGUI
{
	function ilSCORMItemGUI($a_id)
	{
		$this->sc_object =& new ilSCORMItem($a_id);
	}

	function view()
	{
		// get ressource identifier
		$id_ref = $this->sc_object->getIdentifierRef();
		if ($id_ref != "")
		{
			$resource =& new ilSCORMResource();
			$resource->readByIdRef($id_ref, $this->sc_object->getSLMId());

			$slm_obj =& new ilObjSCORMLearningModule($_GET["ref_id"]);

			header("Location: ../".$slm_obj->getDataDirectory()."/".$resource->getHref());
		}
	}
}
?>
