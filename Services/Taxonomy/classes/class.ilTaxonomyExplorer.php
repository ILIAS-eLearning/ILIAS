<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Taxononmy tree explorer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesTaxonomy
 */
require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

class ilTaxonomyExplorer extends ilExplorer
{
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function __construct($a_target, $a_tax_tree, $a_gui_class = "ilobjtaxonomygui",
		$a_gui_cmd = "listItems")
	{
		global $lng;
		
		parent::__construct($a_target);
		
		include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
		$sort_mode = ilObjTaxonomy::lookupSortingMode($a_tax_tree->getTreeId());
		$this->setPostSort(false);
		if ($sort_mode == ilObjTaxonomy::SORT_MANUAL)
		{
			$this->setPostSort(true);
		}
		
		//$this->setFilterMode(IL_FM_POSITIVE);
		//$this->addFilter("tax");
		
		$this->gui_class = $a_gui_class;
		$this->gui_cmd = $a_gui_cmd;
		$this->tree = $a_tax_tree;
		$this->root_id = $this->tree->readRootId();
		
		$this->setSessionExpandVariable("txexpand");
		$this->checkPermissions(false);
		
//		$this->setOrderColumn("order_nr");
//		$this->textwidth = 200;

		$this->force_open_path = array();
		
		$this->setRootNodeTitle($lng->txt("tax_taxonomy"));
	}

	/**
	 * Set root node titl
	 *
	 * @param string $a_val root node title	
	 */
	function setRootNodeTitle($a_val)
	{
		$this->root_node_title = $a_val;
	}
	
	/**
	 * Get root node titl
	 *
	 * @return string root node title
	 */
	function getRootNodeTitle()
	{
		return $this->root_node_title;
	}
	
	/**
	* set force open path
	*/
	function setForceOpenPath($a_path)
	{
		$this->force_open_path = $a_path;
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
		global $lng, $ilias, $ilCtrl;

		$ilCtrl->setParameterByClass($this->gui_class, "tax_node",
			$this->tree->readRootId());
		
		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_scat_s.png"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("tax_node"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", $this->getRootNodeTitle());
		if ($this->highlighted == $this->tree->readRootId())
		{
			$tpl->setVariable("A_CLASS", "class='il_HighlightedNode'");
		}
		$tpl->setVariable("LINK_TARGET",
			$ilCtrl->getLinkTargetByClass($this->gui_class, $this->gui_cmd));
		$tpl->parseCurrentBlock();
		
		$tpl->touchBlock("element");
		$ilCtrl->setParameterByClass($this->gui_class, "tax_node",
			$_GET["tax_node"]);

	}

	/**
	* overwritten method from base class
	* @access	private
	* @param	integer
	* @param	array
	* @return	string
	*/
	function formatObject(&$tpl, $a_node_id,$a_option,$a_obj_id = 0)
	{
		global $lng;
		
/*		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , $this->getImage("icon_".$a_option["type"]."_s.png", $a_option["type"], $a_obj_id));
		$tpl->setVariable("TARGET_ID" , "iconid_".$a_node_id);
		$this->iconList[] = "iconid_".$a_node_id;
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
		$tpl->parseCurrentBlock();
		
		$this->outputIcons(false);*/
		parent::formatObject($tpl, $a_node_id,$a_option,$a_obj_id);
	}
	
	/**
	* check if links for certain object type are activated
	*
	* @param	string		$a_type			object type
	*
	* @return	boolean		true if linking is activated
	*/
	function isClickable($a_type, $a_obj_id = 0)
	{
		global $ilUser;
		return true;
	}
	
	/**
	* build link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;
		
		switch($a_type)
		{
			// taxonomy node
			case "taxn":
				$ilCtrl->setParameterByClass($this->gui_class, "tax_node", $a_node_id);
				$ret = $ilCtrl->getLinkTargetByClass($this->gui_class, $this->gui_cmd);
				$ilCtrl->setParameterByClass($this->gui_class, "tax_node", $_GET["tax_node"]);
				return $ret;
				break;
		}
	}

	/**
	 * standard implementation for title, may be overwritten by derived classes
	 */
	function buildTitle($a_title, $a_id, $a_type)
	{
		return $a_title;
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

	/**
	 * Get frame target
	 */
	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return "";
	}
	
	/**
	 * get image path (may be overwritten by derived classes)
	 */
	function getImage($a_name, $a_type = "", $a_obj_id = "")
	{
		return ilUtil::getImagePath($a_name);
	}

	/**
	 * Sort nodes
	 *
	 * @param	array	node list as returned by iltree::getChilds();
	 * @return	array	sorted nodes
	 */
	function sortNodes($a_nodes,$a_parent_obj_id)
	{
		$a_nodes = ilUtil::sortArray($a_nodes, "order_nr", "asc", true);
		
		
		return $a_nodes;
	}

}
?>
