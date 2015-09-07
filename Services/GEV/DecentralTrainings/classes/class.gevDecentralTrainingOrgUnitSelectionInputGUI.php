<?php
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingOrgUnitExplorerGUI.php");
require_once("Services/TEP/classes/class.ilTEPOrgUnitSelectionInputGUI.php");
class gevDecentralTrainingOrgUnitSelectionInputGUI extends ilTEPOrgUnitSelectionInputGUI {

	 /**
	 * Constructor
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	// gev-patch start
	function __construct(array $a_org_units, $a_postvar, $a_multi = false, $a_show_recursive = true, $a_root_node_ref_id = null)
	// gev-patch end
	{
		global $lng, $ilCtrl, $tree;
				
		$lng->loadLanguageModule("orgu");
		
		$this->multi_nodes = $a_multi;
		$this->org_unit_map = $a_org_units;
		// gev-patch start
		$this->show_rcrsv = (bool)$a_show_recursive; 
		//gev-patch end
		
		$ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $a_postvar);
		
		$id = "ousel".md5($a_postvar);
		
		$this->explorer_gui = new gevDecentralTrainingOrgUnitExplorerGUI($id, array("ilformpropertydispatchgui", "ilteporgunitselectioninputgui"), $this->getExplHandleCmd(), $tree, $a_root_node_ref_id);
		$this->explorer_gui->setSelectMode($a_postvar."_sel", $this->multi_nodes);
		$this->explorer_gui->setSkipRootNode(true);
		$this->explorer_gui->setSelectableOrgUnitIds($a_org_units);

		ilExplorerSelectInputGUI::__construct($lng->txt("objs_orgu"), $a_postvar, $this->explorer_gui, $this->multi_nodes);
	}
}