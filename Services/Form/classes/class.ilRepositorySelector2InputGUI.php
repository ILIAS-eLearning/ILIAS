<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerSelectInputGUI.php");
include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");

/**
 * Select repository nodes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_IsCalledBy ilRepositorySelector2InputGUI: ilFormPropertyDispatchGUI
 *
 */
class ilRepositorySelector2InputGUI extends ilExplorerSelectInputGUI
{
	/**
	 * Constructor
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	function __construct($a_title, $a_postvar, $a_multi = false)
	{
		global $ilCtrl;

		$this->multi_nodes = $a_multi;
		$ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $a_postvar);

		include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");
		$this->explorer_gui = new ilRepositorySelectorExplorerGUI(array("ilpropertyformgui", "ilformpropertydispatchgui", "ilrepositoryselector2inputgui"), $this->getExplHandleCmd(),
			$this, "selectRepositoryItem", "root_id");
//		$this->explorer_gui->setTypeWhiteList($this->getVisibleTypes());
//		$this->explorer_gui->setClickableTypes($this->getClickableTypes());
		$this->explorer_gui->setSelectMode($a_postvar."_sel", $this->multi_nodes);

		//$this->explorer_gui = new ilTaxonomyExplorerGUI(array("ilformpropertydispatchgui", "iltaxselectinputgui"), $this->getExplHandleCmd(), $a_taxonomy_id, "", "",
		//	"tax_expl_".$a_postvar);

		parent::__construct($a_title, $a_postvar, $this->explorer_gui, $this->multi_nodes);
		$this->setType("rep_select");
	}

	/**
	 * Get title for node id (needs to be overwritten, if explorer is not a tree eplorer
	 *
	 * @param
	 * @return
	 */
	function getTitleForNodeId($a_id)
	{
		return ilObject::_lookupTitle(ilObject::_lookupObjId($a_id));
	}

	/**
	 * Handle explorer command
	 */
	function handleExplorerCommand()
	{
		if ($this->explorer_gui->handleCommand())
		{
//			exit;
		}
	}


}
