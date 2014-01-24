<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerSelectInputGUI.php");
include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");

/**
 * Select taxonomy nodes input GUI
 *
 * @author Alex Killing <alex.killing@gmx.de> 
 * @version $Id$
 * @ilCtrl_IsCalledBy ilTaxSelectInputGUI: ilFormPropertyDispatchGUI
 *
 * @ingroup	ServicesTaxonomy
 */
class ilTaxSelectInputGUI extends ilExplorerSelectInputGUI
{
	/**
	 * Constructor
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	function __construct($a_taxonomy_id, $a_postvar, $a_multi = false)
	{
		global $lng, $ilCtrl;
		
		$lng->loadLanguageModule("tax");
		$this->multi_nodes = $a_multi;
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php");
		$ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $a_postvar);
		$this->explorer_gui = new ilTaxonomyExplorerGUI(array("ilformpropertydispatchgui", "iltaxselectinputgui"), $this->getExplHandleCmd(), $a_taxonomy_id, "", "",
			"tax_expl_".$a_postvar);
		$this->explorer_gui->setSelectMode($a_postvar."_sel", $this->multi_nodes);
		$this->explorer_gui->setSkipRootNode(true);

		parent::__construct(ilObject::_lookupTitle($a_taxonomy_id), $a_postvar, $this->explorer_gui, $this->multi_nodes);
		$this->setType("tax_select");
		
		if ((int) $a_taxonomy_id == 0)
		{
			throw new ilTaxonomyExceptions("No taxonomy ID passed to ilTaxSelectInputGUI.");
		}
		
		$this->setTaxonomyId((int) $a_taxonomy_id);
		$this->tax = new ilObjTaxonomy($this->getTaxonomyId());
	}
	
	/**
	 * Set taxonomy id
	 *
	 * @param int $a_val taxonomy id
	 */
	function setTaxonomyId($a_val)
	{
		$this->taxononmy_id = $a_val;
	}
	
	/**
	 * Get taxonomy id
	 *
	 * @return int taxonomy id
	 */
	function getTaxonomyId()
	{
		return $this->taxononmy_id;
	}
	
	/**
	 * Get title for node id (needs to be overwritten, if explorer is not a tree eplorer
	 *
	 * @param
	 * @return
	 */
	function getTitleForNodeId($a_id)
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
		return ilTaxonomyNode::_lookupTitle($a_id);
	}
}
