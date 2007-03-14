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
* Class ilLPObjectiveItemListGUI
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*
*/

include_once 'Services/Tracking/classes/ItemList/class.ilLPObjectItemListGUI.php';

class ilLPObjectiveItemListGUI extends ilLPObjectItemListGUI
{
	var $child_id = null;

	function ilLPObjectiveItemListGUI($a_obj_id)
	{
		parent::ilLPObjectItemListGUI($a_obj_id,'objective');
	}

	function hasDetails()
	{
		return false;
	}
	function setChildId($a_obj_id)
	{
		$this->child_id = $a_obj_id;
	}
	function getChildId()
	{
		return $this->child_id;
	}

	function __readTitle()
	{
		return $this->title = $this->status_info['objective_title'][$this->getChildId()];
	}
	function __readDescription()
	{
		return $this->description = $this->status_info['objective_description'][$this->getChildId()];
	}

	function renderTypeImage()
	{
		$this->tpl->setCurrentBlock("row_type_image");
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_'.'lobj'.'.gif'));
		$this->tpl->setVariable("TYPE_ALT_IMG",$this->lng->txt('obj_crs'));
		$this->tpl->parseCurrentBlock();
	}

	function __readUserStatus()
	{
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';

		if(!is_array($this->status_info['completed'][$this->getChildId()]))
		{
			return $this->status = LP_STATUS_NOT_ATTEMPTED;
		}		
		if(in_array($this->getCurrentUser(),$this->status_info['completed'][$this->getChildId()]))
		{
			return $this->status = LP_STATUS_COMPLETED;
		}
		else
		{
#if(in_array($this->getCurrentUser(),ilLPStatusWrapper::_getInProgress($this->getId())))
#		{
			return $this->status = LP_STATUS_NOT_ATTEMPTED;
		}
	}

	function __readUserStatusInfo()
	{
		return true;
	}
	function __readMark()
	{
		return true;
	}
	function __readComment()
	{
		return true;
	}
}
?>