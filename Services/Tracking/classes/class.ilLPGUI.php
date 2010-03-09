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
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ilCtrl_Calls ilLPGUI:
*
* @extends ilObjectGUI
* @package ilias-core
*
*/

include_once "classes/class.ilObjectGUI.php";

define("LP_MODE_PERSONAL_DESKTOP",1);
define("LP_MODE_ADMINISTRATION",2);
define("LP_MODE_REPOSITORY",3);

/* Base class for all Learning progress gui classes.
 * Defines modes */

class ilLPGUI
{
	var $tpl = null;
	var $ctrl = null;

	var $ref_id = null;


	/**
	* Call this constructor with ref_id of the context in which the GUI has been started.
	* This is used for different output behaviour.
	* E.G 
	* ref_id = TRACKING_ID = 17: learning progress called from administration => 'listOfObjects'
	* ref_id = 0:			   : learning progress called from personal desktop => 'listOfObjects', 'listOfProgress' ...
	* ref_id = crs || lm ...   : learning progress called from repository => 'listOfProgress', 'listOfSettings'
	*
	*/
	function ilLPGUI($a_ref_id)
	{
		global $tpl,$ilCtrl;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;

		$this->ref_id = $a_ref_id;

		$this->_parseMode();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();
		$this->ctrl->setReturn($this, "show");

		switch($next_class)
		{
			default:
				$cmd = $this->ctrl->getCmd("view");
				$this->$cmd();
				break;
		}
		return true;
	}

	function __parseMode()
	{
	}
}
?>