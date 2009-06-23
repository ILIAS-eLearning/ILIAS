<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Select media pool for adding objects into pages
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/

include_once("./classes/class.ilExplorer.php");
class ilPoolSelectorGUI extends ilExplorer
{
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function __construct($a_target)
	{
		global $tree,$ilCtrl;

		$this->ctrl =& $ilCtrl;
		parent::ilExplorer($a_target);
		$this->setFrameTarget("");
	}

	function setSelectableTypes($a_types)
	{
		$this->selectable_types  = $a_types;
	}
	
	function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass("ilpcmediaobjectgui", "subCmd", "selectPool");
		$ilCtrl->setParameterByClass("ilpcmediaobjectgui", "pool_ref_id", $a_node_id);
		$link = $ilCtrl->getLinkTargetByClass("ilpcmediaobjectgui", "insert");

		return $link;
	}
	

	/**
	* Item clickable?
	*/
	function isClickable($a_type, $a_ref_id)
	{
		global $ilUser, $ilAccess;
		
		if ($a_type == "mep" &&
			$ilAccess->checkAccess("write", "", $a_ref_id))
		{
			return true;
		}
		false;
	}

	/**
	* Show childs y/n?
	*/
	function showChilds($a_ref_id)
	{
		global $ilAccess;

		if ($a_ref_id == 0)
		{
			return true;
		}

		if ($ilAccess->checkAccess("read", "", $a_ref_id))
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