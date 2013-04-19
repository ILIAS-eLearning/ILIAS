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
* GUI class for learning progress filter functionality
* Used for object and learning progress presentation
*
*
* @ilCtrl_Calls ilUserFilterGUI:
*
* 
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @package ilias-tracking
*
*/
class ilUserFilterGUI
{
	var $usr_id = null;
	var $tpl = null;
	var $lng = null;
	var $ctrl = null;

	function ilUserFilterGUI($a_usr_id)
	{
		global $lng,$ilCtrl,$tpl;

		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('trac');
		$this->tpl =& $tpl;
		$this->usr_id = $a_usr_id;
		$this->__initFilter();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		switch($this->ctrl->getNextClass())
		{
			default:
				$cmd = $this->ctrl->getCmd() ? $this->ctrl->getCmd() : 'show';
				$this->$cmd();

		}
		return true;
	}

	
	function getUserId()
	{
		return $this->usr_id;
	}


	function getHTML()
	{
		global $ilObjDataCache;

		$tpl = new ilTemplate('tpl.search_user_filter.html',true,true,'Services/Search');

		$tpl->setVariable("FILTER_ACTION",$this->ctrl->getFormAction($this));
		$tpl->setVariable("TBL_TITLE",$this->lng->txt('trac_lp_filter'));
		$tpl->setVariable("TXT_LOGIN",$this->lng->txt('login'));
		$tpl->setVariable("TXT_FIRSTNAME",$this->lng->txt('firstname'));
		$tpl->setVariable("TXT_LASTNAME",$this->lng->txt('lastname'));
		$tpl->setVariable("BTN_REFRESH",$this->lng->txt('trac_refresh'));

		$tpl->setVariable("QUERY",ilUtil::prepareFormOutput($this->filter->getQueryString('login')));
		$tpl->setVariable("FIRSTNAME",ilUtil::prepareFormOutput($this->filter->getQueryString('firstname')));
		$tpl->setVariable("LASTNAME",ilUtil::prepareFormOutput($this->filter->getQueryString('lastname')));

		return $tpl->get();
	}

		
		
	function refresh()
	{
		$_GET['offset'] = 0;
		$this->ctrl->saveParameter($this,'offset');
		$this->filter->storeQueryStrings($_POST['filter']);
		$this->ctrl->returnToParent($this);

		return true;
	}


	// Private
	function __initFilter()
	{
		global $ilUser;

		include_once 'Services/Search/classes/class.ilUserSearchFilter.php';
		$this->filter = new ilUserSearchFilter($ilUser->getId());
		return true;
	}
}	
?>