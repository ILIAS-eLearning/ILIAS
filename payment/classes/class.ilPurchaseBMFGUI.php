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
* Class ilPurchaseBMFGUI
*
* @author Stefan Meyer
* @version $Id$
*
* @package core
*/


class ilPurchaseBMFGUI
{
	var $ctrl;
	var $tpl;

	var $user_obj;

	function ilPurchaseBMFGUI(&$user_obj)
	{
		global $ilCtrl,$tpl;

		$this->ctrl =& $ilCtrl;
		$this->tpl =& $tpl;
		
		// Get user object
		$this->user_obj =& $user_obj;
	}

	function start()
	{
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_edit.html',true);

		$this->tpl->setVariable("TXT_PATH","hallo");

		// user_id $this->user_obj->getId()
		// all 

	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tree;

		$cmd = $this->ctrl->getCmd();
		switch ($this->ctrl->getNextClass($this))
		{

			default:
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'start';
				}
				$this->$cmd();
				break;
		}
	}

}
?>