<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjSearchController
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
* @ilCtrl_Calls ilSearchController: ilSearchGUI, ilAdvancedSearchGUI
* @ilCtrl_Calls ilSearchController: ilLuceneSearchGUI, ilLuceneAdvancedSearchGUI
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
		include_once './Services/Search/classes/class.ilSearchSettings.php';
		if(ilSearchSettings::getInstance()->enabledLucene())
		{
			$default = 'illucenesearchgui';
		}
		else
		{
			$default = 'ilsearchgui'; 
		}
		
		return $_SESSION['search_last_class'] ? $_SESSION['search_last_class'] : $default;
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

		// check whether password of user have to be changed
		// due to first login or password of user is expired
		if( $ilUser->isPasswordChangeDemanded() || $ilUser->isPasswordExpired() )
		{
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
			case 'illucenesearchgui':
				$this->setLastClass('illucenesearchgui');
				include_once './Services/Search/classes/Lucene/class.ilLuceneSearchGUI.php';
				$this->ctrl->forwardCommand(new ilLuceneSearchGUI());
				break;
				
			case 'illuceneadvancedsearchgui':
				$this->setLastClass('illuceneadvancedsearchgui');
				include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchGUI.php';
				$this->ctrl->forwardCommand(new ilLuceneAdvancedSearchGUI());
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