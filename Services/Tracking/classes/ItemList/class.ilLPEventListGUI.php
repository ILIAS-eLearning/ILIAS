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

class ilLPEventListGUI extends ilLPObjectItemListGUI
{
	var $child_id = null;

	function ilLPEventListGUI($a_obj_id)
	{
		parent::ilLPObjectItemListGUI($a_obj_id,'objective');
	}

	function hasDetails()
	{
		return false;
	}

	function __readMode()
	{
		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
		$this->mode = LP_MODE_EVENT;
	}

	function __readStatusInfo()
	{
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
		$this->status_info = ilLPStatusWrapper::_getStatusInfoByType($this->getId(),'event');
	}

	function __readTypicalLearningTime()
	{
		$this->tlt = 0;
	}
	
	function __readTitle()
	{
		return $this->title = $this->status_info['title'];
	}
	function __readDescription()
	{
		return $this->description = $this->status_info['description'];
	}

	function renderTypeImage()
	{
		$this->tpl->setCurrentBlock("row_type_image");
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_'.'event'.'.gif'));
		$this->tpl->setVariable("TYPE_ALT_IMG",$this->lng->txt('event'));
		$this->tpl->parseCurrentBlock();
	}

	function __readMark()
	{
		include_once './Modules/Session/classes/class.ilEventParticipants.php';
		$this->mark = ilEventParticipants::_lookupMark($this->getId(),$this->getCurrentUser());
	}
	function __readComment()
	{
		include_once './Modules/Session/classes/class.ilEventParticipants.php';
		$this->comment = ilEventParticipants::_lookupComment($this->getId(),$this->getCurrentUser());
	}

	function __readUserStatus()
	{
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
		if($this->status_info['starting_time'] < time())
		{
			if(in_array($this->getCurrentUser(),ilLPStatusWrapper::_getCompleted($this->getId())))
			{
				return $this->status = LP_STATUS_PARTICIPATED;
			}
			else
			{
				return $this->status = LP_STATUS_NOT_PARTICIPATED;
			}
		}
		if(in_array($this->getCurrentUser(),ilLPStatusWrapper::_getCompleted($this->getId())))
		{
			return $this->status = LP_STATUS_PARTICIPATED;
		}
		if($this->status_info['registration'])
		{
			if(in_array($this->getCurrentUser(),ilLPStatusWrapper::_getInProgress($this->getId())))
			{
				return $this->status = LP_STATUS_REGISTERED;
			}
			else
			{
				return $this->status = LP_STATUS_NOT_REGISTERED;
			}
		}
		else
		{
			return $this->status = LP_STATUS_NOT_ATTEMPTED;
		}
	}
}
?>