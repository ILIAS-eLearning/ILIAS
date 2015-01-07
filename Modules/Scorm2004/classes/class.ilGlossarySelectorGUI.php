<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

/**
* Select file for being added into file lists
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilGlossarySelectorGUI extends ilExplorer
{
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function __construct($a_target, $a_par_class = "")
	{
		global $tree,$ilCtrl;

		$this->ctrl =& $ilCtrl;
		$this->parent_class = $a_par_class;
		parent::ilExplorer($a_target);
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
		
		//$ilCtrl->setParameterByClass($this->parent_class, "subCmd", "selectGlossary");
		$ilCtrl->setParameterByClass($this->parent_class, "glo_ref_id", $a_node_id);
		$link = $ilCtrl->getLinkTargetByClass($this->parent_class, "selectGlossary");

		return $link;
	}
	

	/**
	* Item clickable?
	*/
	function isClickable($a_type, $a_ref_id)
	{
		global $ilUser, $ilAccess;
		
		if ($a_type == "glo" &&
			$ilAccess->checkAccess("read", "", $a_ref_id))
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
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_root.svg"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("text");
		$tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		//$tpl->setCurrentBlock("row");
		//$tpl->parseCurrentBlock();
		
		$tpl->touchBlock("element");
		
	}
} // END class ilFileSelectorGUI
?>