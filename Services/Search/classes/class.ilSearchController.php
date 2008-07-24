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
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.|
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilObjSearchController
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-search
*
* @ilCtrl_Calls ilSearchController: ilSearchGUI, ilAdvancedSearchGUI, ilSearchResultGUI
*
*/

class ilSearchController
{
	var $ctrl = null;
	var $ilias = null;
	var $lng = null;

	/**
	* Constructor
	* @access public
	*/
	function ilSearchController()
	{
		global $ilCtrl,$ilias,$lng,$tpl;

		$this->ilias = $ilias;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
	}

	function getLastClass()
	{
		return $_SESSION['search_last_class'] ? $_SESSION['search_last_class'] : 'ilsearchgui';
	}
	function setLastClass($a_class)
	{
		$_SESSION['search_last_class'] = $a_class;
	}

	function &executeCommand()
	{
		global $rbacsystem,$ilUser;

		// Check for incomplete profile
		if($ilUser->getProfileIncomplete())
		{
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		if( $ilUser->isPasswordExpired() )
		{
			$msg = $this->lng->txt('password_expired');
			$password_age = $ilUser->getPasswordAge();

			ilUtil::sendInfo( sprintf($msg,$password_age), true );

			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		include_once 'Services/Search/classes/class.ilSearchSettings.php';

		// Check hacks
		if(!$rbacsystem->checkAccess('search',ilSearchSettings::_getSearchSettingRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		$forward_class = $this->ctrl->getNextClass($this) ? $this->ctrl->getNextClass($this) : $this->getLastClass();

		switch($forward_class)
		{
			case 'ilsearchresultgui':
				// Remember last class
				$this->setLastClass('ilsearchresultgui');

				include_once 'Services/Search/classes/class.ilSearchResultGUI.php';

				$this->ctrl->forwardCommand(new ilSearchResultGUI());
				break;

			case 'iladvancedsearchgui':
				// Remember last class
				$this->setLastClass('iladvancedsearchgui');

				include_once 'Services/Search/classes/class.ilAdvancedSearchGUI.php';

				$this->ctrl->forwardCommand(new ilAdvancedSearchGUI());
				break;

			case 'ilsearchgui':
				// Remember last class
				$this->setLastClass('ilsearchgui');

			default:
				include_once 'Services/Search/classes/class.ilSearchGUI.php';

				$search_gui = new ilSearchGUI();
				$this->ctrl->forwardCommand($search_gui);
				break;
		}
		$this->tpl->show();

		return true;
	}
}
?>
