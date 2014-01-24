<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * Taxonomy explorer GUI class 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesTaxonomy
 */
class ilTaxonomyExplorerGUI extends ilTreeExplorerGUI
{
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_tax_id,
		$a_target_gui, $a_target_cmd, $a_id = "")
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
		$this->tax_tree = new ilTaxonomyTree($a_tax_id);
		if ($a_id != "")
		{
			$this->id = $a_id;
		}
		else
		{
			$this->id = "tax_expl_".$this->tax_tree->getTreeId();
		}
		$this->target_gui = $a_target_gui; 
		$this->target_cmd = $a_target_cmd; 
		parent::__construct($this->id, $a_parent_obj, $a_parent_cmd, $this->tax_tree);
	}
	
	
	/**
	 * Get content of node
	 *
	 * @param
	 * @return
	 */
	function getNodeContent($a_node)
	{
		$rn = $this->getRootNode();
		if ($rn["child"] == $a_node["child"])
		{
			return ilObject::_lookupTitle($this->tax_tree->getTreeId());
		}
		else
		{
			return $a_node["title"];
		}
	}
	
	/**
	 * Get node href
	 *
	 * @param
	 * @return
	 */
	function getNodeHref($a_node)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass($this->target_gui, "tax_node", $a_node["child"]);
		$href = $ilCtrl->getLinkTargetByClass($this->target_gui, $this->target_cmd);
		$ilCtrl->setParameterByClass($this->target_gui, "tax_node", $_GET["tax_node"]);
		return $href;
	}
	
	/**
	 * Get node icon
	 *
	 * @param
	 * @return
	 */
	function getNodeIcon($a_node)
	{
		return ilUtil::getImagePath("icon_taxn_s.png");
	}
	
	/**
	 * 
	 *
	 * @param
	 * @return
	 */
	function isNodeHighlighted($a_node)
	{
		if ($a_node["child"] == $_GET["tax_node"])
		{
			return true;
		}
		return false;
	}
	
}

?>
