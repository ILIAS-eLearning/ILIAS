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
* @package ilias-tracking
*
*/

define("LP_MODE_PERSONAL_DESKTOP",1);
define("LP_MODE_ADMINISTRATION",2);
define("LP_MODE_REPOSITORY",3);

/* Base class for all Learning progress gui classes.
 * Defines modes for presentation according to the context in which it was called
 * E.g: mode LP_MODE_PERSONAL_DESKTOP displays only listOfObjects.
 */

class ilLearningProgressBaseGUI 
{
	var $tpl = null;
	var $ctrl = null;

	var $mode = 0;

	function ilLearningProgressBaseGUI($a_mode)
	{
		global $tpl,$ilCtrl;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;

		$this->mode = $a_mode;
	}
	
	function getMode()
	{
		return $this->mode;
	}

	// Protected
	function __getDefaultCommand()
	{
		if(strlen($cmd = $this->ctrl->getCmd()))
		{
			return $cmd;
		}
		return 'show';
	}

}
?>