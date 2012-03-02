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
	function __construct($a_target, $a_tax_tree)
	{
		parent::__construct($a_target);
		
		//$this->setFilterMode(IL_FM_POSITIVE);
		//$this->addFilter("tax");
		
		$this->tree = $a_tax_tree;
		$this->root_id = $this->tree->readRootId();
		
		$this->setSessionExpandVariable("txexpand");
		$this->checkPermissions(false);
		$this->setPostSort(false);
		
//		$this->setOrderColumn("order_nr");
//		$this->textwidth = 200;

		$this->force_open_path = array();
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

		$ilCtrl->setParameterByClass("ilobjtaxonomygui", "tax_node",
			$this->tree->readRootId());
		
		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_scat_s.gif"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("tax_node"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", $lng->txt("tax_taxonomy"));
		if ($this->highlighted == $this->tree->readRootId())
		{
			$tpl->setVariable("A_CLASS", "class='il_HighlightedNode'");
		}
		$tpl->setVariable("LINK_TARGET",
			$ilCtrl->getLinkTargetByClass("ilobjtaxonomygui", "listItems"));
		$tpl->parseCurrentBlock();
		
		$tpl->touchBlock("element");

		$ilCtrl->setParameterByClass("ilobjtaxonomygui", "tax_node",
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
		$tpl->setVariable("ICON_IMAGE" , $this->getImage("icon_".$a_option["type"]."_s.gif", $a_option["type"], $a_obj_id));
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
				$ilCtrl->setParameterByClass("ilobjtaxonomygui", "tax_node", $a_node_id);
				$ret = $ilCtrl->getLinkTargetByClass("ilobjtaxonomygui", "listItems");
				$ilCtrl->setParameterByClass("ilobjtaxonomygui", "tax_node", $_GET["tax_node"]);
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

}
?>
