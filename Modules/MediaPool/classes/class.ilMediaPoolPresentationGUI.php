<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Top level GUI class for media pools.
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilMediaPoolPresentationGUI: ilObjMediaPoolGUI
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolPresentationGUI
{
	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $tpl;
	var $lng;
	var $objDefinition;

	/**
	* Constructor
	* @access	public
	*/
	function ilMediaPoolPresentationGUI()
	{
		global $ilias, $tpl, $lng, $objDefinition, $ilCtrl,
			$rbacsystem;
		
		$lng->loadLanguageModule("content");

		$this->ctrl =& $ilCtrl;

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tpl, $ilCtrl;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("");

		switch($next_class)
		{
			case "ilobjmediapoolgui":
				require_once ("./Modules/MediaPool/classes/class.ilObjMediaPoolGUI.php");
				$mep_gui =& new ilObjMediaPoolGUI("", $_GET["ref_id"],true, false);
				$ilCtrl->forwardCommand($mep_gui);
				break;

			default:
				$this->ctrl->setCmdClass("ilobjmediapoolgui");
				//$this->ctrl->setCmd("");
				return $this->executeCommand();
				break;
		}
	}

}
?>
