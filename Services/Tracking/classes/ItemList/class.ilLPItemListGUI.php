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
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*
*/

include_once 'Services/Tracking/classes/class.ilLPStatus.php';
include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';

class ilLPItemListGUI
{
	var $db = null;

	function ilLPItemListGUI($a_id,$a_type)
	{
		global $ilDB,$lng,$ilErr,$tree,$ilObjDataCache;

		$this->db =& $ilDB;
		$this->lng =& $lng;
		$this->err =& $ilErr;
		$this->tree =& $tree;
		$this->obj_cache = $ilObjDataCache;

		$this->id = $a_id;
		$this->type = $a_type;
	}

	function getId()
	{
		return $this->id;
	}
	function getType()
	{
		return $this->type;
	}

	function setCurrentUser($a_user)
	{
		$this->user = $a_user;
	}
	function getCurrentUser()
	{
		return $this->user;
	}

	function getMode()
	{
		return $this->mode;
	}

	function getTitle()
	{
		return $this->title;
	}
	function getDescription()
	{
		return $this->description;
	}

	function getMark()
	{
		return $this->mark;
	}
	function getComment()
	{
		return $this->comment;
	}
	function getTypicalLearningTime()
	{
		return $this->tlt ? $this->tlt : 0;
	}
	function hasDetails()
	{
		return true;
	}
	function enabled($a_key)
	{
		return $this->enabled[$key];
	}

	function enable($a_key)
	{
		$this->enabled[$key] = true;
	}
	function disable($a_key)
	{
		$this->enabled[$key] = false;
	}

	function setIndentLevel($a_level)
	{
		$this->level = $a_level;
	}

	function getUserStatus()
	{
		return $this->status;
	}

	function addCheckBox($a_check)
	{
		$this->checkbox = $a_check;
	}

	function __readMode()
	{
		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
		$this->mode = ilLPObjSettings::_lookupMode($this->getId());
	}

	function __readStatusInfo()
	{
	}
	function __readUserStatus()
	{
	}
	function __readTypicalLearningTime()
	{
	}		
	
	function getHTML()
	{
		return $this->html;
	}

	/**
	* Read all necassary data for output
	*
	* @access public
	*/
	function read()
	{
		$this->__readMode();
		$this->__readStatusInfo();
		$this->__readTypicalLearningTime();
		$this->__readTitle();
		$this->__readDescription();
	}

	function readUserInfo()
	{
		if($this->getCurrentUser())
		{
			$this->__readMark();
			$this->__readComment();
			$this->__readUserStatus();
			$this->__readUserStatusInfo();
		}
	}

	function renderTypeImage()
	{
		$this->tpl->setCurrentBlock("row_type_image");
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_'.$this->getType().'.gif'));
		$this->tpl->setVariable("TYPE_ALT_IMG",$this->lng->txt('obj_'.$this->getType()));
		$this->tpl->parseCurrentBlock();
	}


	function renderContainerProgress()
	{
		$this->tpl = new ilTemplate('tpl.lp_item_list_row.html',true,true,'Services/Tracking');

		$this->renderTypeImage();

		$this->tpl->setVariable("TXT_TITLE",$this->getTitle());
		if(strlen($this->getDescription()))
		{
			$this->tpl->setVariable("TXT_DESC",$this->getDescription());
		}
		// Status info
		if($this->user_status_info)
		{
			$this->tpl->setCurrentBlock("status_info");
			$this->tpl->setVariable("STATUS_PROP",$this->user_status_info[0]);
			$this->tpl->setVariable("STATUS_VAL",$this->user_status_info[1]);
			$this->tpl->parseCurrentBlock();
		}

		// Status
		$this->tpl->setCurrentBlock("item_property");
		$this->tpl->setVariable("TXT_PROP",$this->lng->txt('trac_status'));
		$this->tpl->setVariable("VAL_PROP",$this->lng->txt($this->getUserStatus()));
		$this->tpl->parseCurrentBlock();

		for($i = 0;$i < $this->level;$i++)
		{
			$this->tpl->touchBlock('start_indent');
			$this->tpl->touchBlock('end_indent');
		}

		$this->html = $this->tpl->get();
	}

	function renderSimpleProgress()
	{
		$this->tpl = new ilTemplate('tpl.lp_item_list_row.html',true,true,'Services/Tracking');

		if(is_array($this->checkbox))
		{
			$this->tpl->setVariable("CHECK_NAME",$this->checkbox[0]);
			$this->tpl->setVariable("CHECK_VALUE",$this->checkbox[1]);
			if($this->checkbox[2])
			{
				$this->tpl->setVariable("CHECK_CHECKED",'checked="checked"');
			}
		}
		$this->tpl->setVariable("TXT_TITLE",$this->getTitle());
		if(strlen($this->getDescription()))
		{
			$this->tpl->setVariable("TXT_DESC",$this->getDescription());
		}

		// Status info
		if($this->user_status_info)
		{
			$this->tpl->setCurrentBlock("status_info");
			$this->tpl->setVariable("STATUS_PROP",$this->user_status_info[0]);
			$this->tpl->setVariable("STATUS_VAL",$this->user_status_info[1]);
			$this->tpl->parseCurrentBlock();
		}

		// Status
		$this->tpl->setCurrentBlock("item_property");
		$this->tpl->setVariable("TXT_PROP",$this->lng->txt('trac_status'));
		$this->tpl->setVariable("VAL_PROP",$this->lng->txt($this->getUserStatus()));
		$this->tpl->parseCurrentBlock();

		// Path
		if($this->enabled('path'))
		{
			$this->renderPath();
		}

		
		$this->html = $this->tpl->get();
	}

	function renderObjectList()
	{
		$this->tpl = new ilTemplate('tpl.lp_item_list_row.html',true,true,'Services/Tracking');

		if(is_array($this->checkbox))
		{
			$this->tpl->setVariable("CHECK_NAME",$this->checkbox[0]);
			$this->tpl->setVariable("CHECK_VALUE",$this->checkbox[1]);
			if($this->checkbox[2])
			{
				$this->tpl->setVariable("CHECK_CHECKED",'checked="checked"');
			}
		}
		$this->tpl->setVariable("TXT_TITLE",$this->getTitle());
		if(strlen($this->getDescription()))
		{
			$this->tpl->setVariable("TXT_DESC",$this->getDescription());
		}

		// Status info
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
		if($num_na = ilLPStatusWrapper::_getCountNotAttempted($this->getId()))
		{
			$this->tpl->setCurrentBlock("item_property");
			$this->tpl->setVariable("TXT_PROP",$this->lng->txt('trac_not_attempted'));
			$this->tpl->setVariable("VAL_PROP",$num_na);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock('newline_prop');

		}
		
		$this->tpl->setCurrentBlock("item_property");
		$this->tpl->setVariable("TXT_PROP",$this->lng->txt('trac_in_progress'));
		$this->tpl->setVariable("VAL_PROP",ilLPStatusWrapper::_getCountInProgress($this->getId()));
		$this->tpl->parseCurrentBlock();
		$this->tpl->touchBlock('newline_prop');

		$this->tpl->setCurrentBlock("item_property");
		$this->tpl->setVariable("TXT_PROP",$this->lng->txt('trac_completed'));
		$this->tpl->setVariable("VAL_PROP",ilLPStatusWrapper::_getCountCompleted($this->getId()));
		$this->tpl->parseCurrentBlock();

		// Path
		if($this->enabled('path'))
		{
			$this->renderPath();
		}

		
		$this->html = $this->tpl->get();
	}

	function renderObjectDetails()
	{
		$this->tpl = new ilTemplate('tpl.lp_item_list_row.html',true,true,'Services/Tracking');

		$this->renderTypeImage();

		if(is_array($this->checkbox))
		{
			$this->tpl->setVariable("CHECK_NAME",$this->checkbox[0]);
			$this->tpl->setVariable("CHECK_VALUE",$this->checkbox[1]);
			if($this->checkbox[2])
			{
				$this->tpl->setVariable("CHECK_CHECKED",'checked="checked"');
			}
		}

		$this->tpl->setVariable("TXT_TITLE",$this->getTitle());
		if(strlen($this->getDescription()))
		{
			$this->tpl->setVariable("TXT_DESC",$this->getDescription());
		}

		// Status info
		if($this->user_status_info)
		{
			$this->tpl->setCurrentBlock("status_info");
			$this->tpl->setVariable("STATUS_PROP",$this->user_status_info[0]);
			$this->tpl->setVariable("STATUS_VAL",$this->user_status_info[1]);
			$this->tpl->parseCurrentBlock();
		}

		// Status
		$this->tpl->setCurrentBlock("item_property");
		$this->tpl->setVariable("TXT_PROP",$this->lng->txt('trac_status'));
		$this->tpl->setVariable("VAL_PROP",$this->lng->txt($this->getUserStatus()));
		$this->tpl->parseCurrentBlock();

		// Comment
		if(strlen($this->getComment()))
		{
			$this->tpl->setCurrentBlock("info_property");
			$this->tpl->setVariable("INFO_TXT_PROP",$this->lng->txt('trac_comment'));
			$this->tpl->setVariable("INFO_VAL_PROP",$this->getComment());
			$this->tpl->parseCurrentBlock();
		}

		for($i = 0;$i < $this->level;$i++)
		{
			$this->tpl->touchBlock('start_indent');
			$this->tpl->touchBlock('end_indent');
		}

		$this->html = $this->tpl->get();
	}
	// Private
	function __getPercent($max,$reached)
	{
		if(!$max)
		{
			return "0%";
		}

		return sprintf("%.2f%%",$reached / $max * 100);
	}

}
?>