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
* Class ilContainerLinkListGUI
*
* @author Stefan Meyer <alex.killing@gmx.de>

* @version $Id$
*
* @ilCtrl_Calls ilContainerLinkListGUI:
* 
* @package ilias-core
*/

class ilContainerLinkListGUI
{
	var $ctrl;

	/**
	* Constructor
	* @access public
	*/
	function ilContainerLinkListGUI()
	{
		global $ilCtrl;
		
		$this->ctrl =& $ilCtrl;
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		//$this->prepareOutput();

		switch($next_class)
		{			
			default:
				$this->$cmd();

				break;
		}
		return true;
	}

} // END class.ilContainerLinkListGUI
?>
