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
* Class ilLPItemListGUI
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*
*/

include_once 'Services/Tracking/classes/ItemList/class.ilLPObjectItemListGUI.php';

class ilLPSAHSItemListGUI extends ilLPObjectItemListGUI
{
	var $child_id = null;

	function ilLPSAHSItemListGUI($a_obj_id)
	{
		parent::ilLPObjectItemListGUI($a_obj_id,'sahs_item');
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
		return $this->title = $this->status_info['scos_title'][$this->getChildId()];
	}
	function __readDescription()
	{
		return $this->description = '';
	}

	function renderTypeImage()
	{
		$this->tpl->setCurrentBlock("row_type_image");
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_'.'sahs'.'.gif'));
		$this->tpl->setVariable("TYPE_ALT_IMG",$this->lng->txt('obj_sahs'));
		$this->tpl->parseCurrentBlock();
	}

	function __readUserStatus()
	{
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';

		if(in_array($this->getCurrentUser(),$this->status_info['failed'][$this->getChildId()]))
		{
			return $this->status = LP_STATUS_FAILED;
		}
		if(in_array($this->getCurrentUser(),$this->status_info['completed'][$this->getChildId()]))
		{
			return $this->status = LP_STATUS_COMPLETED;
		}
		if(in_array($this->getCurrentUser(),$this->status_info['in_progress'][$this->getChildId()]))
		{
			return $this->status = LP_STATUS_IN_PROGRESS;
		}
		else
		{
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