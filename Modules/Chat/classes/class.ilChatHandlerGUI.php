<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @ilCtrl_Calls ilChatHandlerGUI: ilObjChatGUI
*
* 
* @ingroup Modules/Chat 
*/
class ilChatHandlerGUI
{
	public function __construct()
	{
		global $ilCtrl;

		$this->ctrl = $ilCtrl;
	}

	/**
	 * Execute command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		global $lng, $ilAccess, $tpl, $ilNavigationHistory;
		
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		if ($next_class == "")
		{
			$this->ctrl->setCmdClass("ilobjchatgui");
			$next_class = $this->ctrl->getNextClass($this);
		}

		switch ($next_class)
		{
			case 'ilobjchatgui':
				require_once "./Modules/Chat/classes/class.ilObjChatGUI.php";
				$chat_gui =& new ilObjChatGUI("", (int) $_GET["ref_id"], true, false);
				$this->ctrl->forwardCommand($chat_gui);
				break;
		}

		$tpl->show();
	 	
	}
}
?>