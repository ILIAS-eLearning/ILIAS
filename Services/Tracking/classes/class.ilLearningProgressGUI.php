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
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLearningProgressGUI: ilLPListOfObjectsGUI
*
* @extends ilObjectGUI
* @package ilias-core
*
*/

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';

class ilLearningProgressGUI extends ilLearningProgressBaseGUI
{
	function ilLearningProgressGUI($a_mode)
	{
		parent::ilLearningProgressBaseGUI($a_mode);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$this->ctrl->setReturn($this, "");

		switch($this->__getNextClass())
		{
			case 'illplistofobjectsgui':

				include_once 'Services/Tracking/classes/class.ilLPListOfObjectsGUI.php';

				$loo_gui = new ilLPListOfObjectsGUI($this->getMode());
				$this->ctrl->forwardCommand($loo_gui);
				break;

			default:
				die("No mode given");
		}
		return true;
	}

	function __getNextClass()
	{
		if(strlen($next_class = $this->ctrl->getNextClass()))
		{
			return $next_class;
		}
		switch($this->getMode())
		{
			case LP_MODE_ADMINISTRATION:
				return 'illplistofobjectsgui';
		}
	}
}
?>