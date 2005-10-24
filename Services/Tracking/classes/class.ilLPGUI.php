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
* @extends ilObjectGUI
* @package ilias-core
*
*/

include_once "classes/class.ilObjectGUI.php";

class ilLPGUI
{
	var $tpl = null;

	function ilLPGUI()
	{
		global $tpl;

		$this->tpl =& $tpl;
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
				$cmd = $this->ctrl->getCmd("test");
				$this->$cmd();
				break;
		}
		return true;
	}

	function test()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.trac_test.html','Services/Tracking');

		$this->tpl->setVariable("HALLO",'hallo');
	}
}
?>