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
* Class ilPaymentObjectGUI
*
* @author Stefan Meyer
* @version $Id$
*
* @package core
*/
include_once './payment/classes/class.ilPaymentObject.php';

class ilPaymentObjectGUI extends ilPaymentBaseGUI
{
	var $ctrl;
	var $lng;
	var $user_obj;

	function ilPaymentObjectGUI(&$user_obj)
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;
		$this->ilPaymentBaseGUI();
		$this->user_obj =& $user_obj;



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
					$cmd = 'showObjects';
				}
				$this->$cmd();
				break;
		}
	}

	function showObjects()
	{
		$this->showButton('showObjectSelector',$this->lng->txt('paya_sell_object'));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_objects.html',true);

		if(!count($objects = ilPaymentObject::_getObjectsData($this->user_obj->getId())))
		{
			sendInfo($this->lng->txt('paya_no_objects_assigned'));
			
			return true;
		}
		
		foreach($objects as $data)
		{
			;
		}
		return true;
	}

	function showObjectSelector()
	{
		global $tree;

		include_once './payment/classes/class.ilPaymentObjectSelector.php';

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paya_object_selector.html",true);
		$this->showButton('showObjects',$this->lng->txt('back'));


		sendInfo($this->lng->txt("paya_select_object_to_sell"));

		$exp = new ilPaymentObjectSelector($this->ctrl->getLinkTarget($this,'showObjectSelector'));
		$exp->setExpand($_GET["paya_link_expand"] ? $_GET["paya_link_expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'showObjectSelector'));
		
		$exp->setOutput(0);

		$this->tpl->setVariable("EXPLORER",$exp->getOutput());

		return true;
	}

	function showSelectedObject()
	{
		if(!$_GET['sell_id'])
		{
			sendInfo($this->lng->txt('paya_no_object_selected'));
			
			$this->showObjectSelector();
			return true;
		}
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.paya_selected_object.html',true);
		$this->showButton('showObjectSelector',$this->lng->txt('back'));

		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays.gif',false));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('details'));

		$this->ctrl->setParameter($this,'sell_id',$_GET['sell_id']);
		$this->tpl->setVariable("SO_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESCRIPTION",$this->lng->txt('description'));
		$this->tpl->setVariable("TXT_OWNER",$this->lng->txt('owner'));
		$this->tpl->setVariable("TXT_PATH",$this->lng->txt('path'));
		$this->tpl->setVariable("TXT_VENDOR",$this->lng->txt('pays_vendor'));
		$this->tpl->setVariable("BTN1_NAME",'showObjects');
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt('cancel'));
		$this->tpl->setVariable("BTN2_NAME",'addObject');
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt('next'));

		// fill values
		$this->tpl->setVariable("DETAILS",$this->lng->txt('details'));
		
		if($tmp_obj =& ilObjectFactory::getInstanceByRefId($_GET['sell_id']))
		{
			$this->tpl->setVariable("TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("DESCRIPTION",$tmp_obj->getDescription());
			$this->tpl->setVariable("OWNER",$tmp_obj->getOwnerName());
			$this->tpl->setVariable("PATH","hallo");
			$this->tpl->setVariable("VENDOR","Vendor");
		}
		return true;
	}
	
	// PRIVATE
}
?>