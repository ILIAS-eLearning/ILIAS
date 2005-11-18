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
* Class ilLPListOfProgress
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLPListOfProgressGUI:
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';

class ilLPListOfProgressGUI extends ilLearningProgressBaseGUI
{
	var $tracked_user = null;
	var $show_active = true;

	function ilLPListOfProgressGUI($a_mode,$a_ref_id)
	{
		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);
		$this->__initUser();
		
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$this->ctrl->setReturn($this, "");

		switch($this->ctrl->getNextClass())
		{
			default:
				$cmd = $this->__getDefaultCommand();
				$this->$cmd();

		}
		return true;
	}

	function show()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_list_progress.html','Services/Tracking');

		$this->__showUserInfo();
		
	}

	function __showUserInfo()
	{
		include_once("classes/class.ilInfoScreenGUI.php");
		
		$info = new ilInfoScreenGUI($this);

		$info->addSection($this->lng->txt("trac_user_data"));
		$info->addProperty($this->lng->txt('username'),$this->tracked_user->getLogin());
		$info->addProperty($this->lng->txt('name'),$this->tracked_user->getFullname());

		if($this->show_active)
		{
			$info->addProperty($this->lng->txt('last_login'),ilFormat::formatDate($this->tracked_user->getLastLogin()));
		}
		$info->addProperty($this->lng->txt('trac_total_online'),
						   ilFormat::_secondsToString(ilOnlineTracking::_getOnlineTime($this->tracked_user->getId())));

		// Finally set template variable
		$this->tpl->setVariable("USER_INFO",$info->getHTML());
		
	}


	function __initUser()
	{
		global $ilUser;

		if($_GET['usr_id'])
		{
			$this->tracked_user =& ilObjectFactory::getInstanceByObjId((int) $_GET['usr_id']);
			$this->show_active = false;
		}
		else
		{
			$this->tracked_user =& $ilUser;
			$this->show_active = true;
		}
		return true;
	}
}
?>