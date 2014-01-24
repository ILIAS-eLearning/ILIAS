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

include_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");
class ilPoolSelectorGUI extends ilExplorer
{
	var $content_gui = "ilpcmediaobjectgui";
	
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

		$this->force_open_path = array();
		if ($_GET["ref_id"] > 0)
		{
			$this->force_open_path = $tree->getPathId($_GET["ref_id"]);
		}

		$this->setSelectableTypes(array('mep'));
	}

	/**
	 * Set content GUI class
	 *
	 * @param	string	content GUI class
	 */
	function setContentGUIClass($a_val)
	{
		$this->content_gui = $a_val;
	}
	
	/**
	 * Get content GUI class
	 *
	 * @return	string	content GUI class
	 */
	function getContentGUIClass()
	{
		return $this->content_gui;
	}

	function setSelectableTypes($a_types)
	{
		$this->selectable_types  = $a_types;
	}
	
	function getSelectableTypes()
	{
		return $this->selectable_types;
	}
	
	function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass($this->getContentGUIClass(), "subCmd", "selectPool");
		$ilCtrl->setParameterByClass($this->getContentGUIClass(), "pool_ref_id", $a_node_id);
		$link = $ilCtrl->getLinkTargetByClass($this->getContentGUIClass(), $_GET["cmd"]);

		return $link;
	}
	

	/**
	* Item clickable?
	*/
	function isClickable($a_type, $a_ref_id)
	{
		global $ilUser, $ilAccess;
		
		if (in_array($a_type, $this->getSelectableTypes()) &&
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
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_root_s.png"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("text");
		$tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		//$tpl->setCurrentBlock("row");
		//$tpl->parseCurrentBlock();
		
		$tpl->touchBlock("element");
		
	}

	/**
	 * force expansion of node
	 */
	function forceExpanded($a_obj_id)
	{
		if (in_array($a_obj_id, $this->force_open_path))
		{
			return true;
		}
		return false;
	}

} // END class ilLMMenuObjectSelector
?>