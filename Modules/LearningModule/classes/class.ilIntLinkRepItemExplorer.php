<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Internal Link: Repository Item Selector Explorer
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/

include_once("classes/class.ilExplorer.php");

class ilIntLinkRepItemExplorer extends ilExplorer
{
	var $mode = "text";
	
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilLMMenuObjectSelector($a_target)
	{
		global $tree,$ilCtrl;

		$this->ctrl =& $ilCtrl;
		parent::ilExplorer($a_target);
	}

	function setSelectableTypes($a_types)
	{
		$this->selectable_types  = $a_types;
	}

	function setMode($a_mode)
	{
		$this->mode  = $a_mode;
	}
	
	function setSetLinkTargetScript($a_script)
	{
		$this->link_target_script = $a_script;
	}
	
	function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	
	function buildLinkTarget($a_node_id, $a_type)
	{
		if ($this->mode != "link")
		{
			return "#";
		}
		else
		{
			//$tpl->setVariable("LINK_TARGET", "content");
			$link =
				ilUtil::appendUrlParameterString($this->link_target_script,
				"linktype=RepositoryItem".
				"&linktarget=il__".$a_type."_".$a_node_id);

			return ($link);
		}
	}
	

	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		if ($this->mode == "link")
		{
			return "content";
		}
		else
		{
			return '';
		}
	}

	/**
	* return if item is clickable
	*/
	function isClickable($a_type, $a_ref_id)
	{
		global $ilUser;
		
		if ($ilUser->getPref("ilPageEditor_JavaScript") != "enable"
			&& $this->mode != "link")
		{
			return false;
		}
		else
		{
			return in_array($a_type,$this->selectable_types);
		}
	}
	
	
	/**
	* build item title
	*/
	function buildTitle($a_title, $a_id, $a_type)
	{
		return $a_title;
	}

	/**
	* standard implementation for description, may be overwritten by derived classes
	*/
	function buildDescription($a_desc, $a_id, $a_type)
	{
		global $ilUser;
		
		if ($ilUser->getPref("ilPageEditor_JavaScript") != "enable")
		{
			if (in_array($a_type,$this->selectable_types))
			{
				if ($this->mode != "link")
				{
					return "[iln ".$a_type."=\"$a_id\"] [/iln]";
				}
			}
		}
		return "";
	}
	
	/**
	* get onclick event handling
	*/
	function buildOnClick($a_node_id, $a_type, $a_title)
	{
		return "parent.content.addInternalLink('[iln ".$a_type."=&quot;".$a_node_id."&quot;] [/iln]','".$a_title."');setTimeout('window.close()',300);return(false);";
	}



	function showChilds($a_ref_id)
	{
		global $rbacsystem;

		if ($a_ref_id == 0)
		{
			return true;
		}

		if ($rbacsystem->checkAccess("read", $a_ref_id))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng, $ilias;
		
		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_root_s.gif"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("text");
		$tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		//$tpl->setCurrentBlock("row");
		//$tpl->parseCurrentBlock();
		
		$tpl->touchBlock("element");
		
	}
} // END class ilLMMenuObjectSelector
?>
