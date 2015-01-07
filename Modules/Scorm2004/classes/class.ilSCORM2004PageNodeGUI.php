<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");

/**
* Class ilSCORM2004PageNodeGUI
*
* User Interface for Scorm 2004 Page Nodes
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilSCORM2004PageNodeGUI: ilSCORM2004PageGUI, ilAssGenFeedbackPageGUI
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004PageNodeGUI extends ilSCORM2004NodeGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilSCORM2004PageNodeGUI($a_slm_obj, $a_node_id = 0)
	{
		global $ilCtrl;
		
		$ilCtrl->saveParameter($this, "obj_id");
		
		parent::ilSCORM2004NodeGUI($a_slm_obj, $a_node_id);
	}

	/**
	* Get Node Type
	*/
	function getType()
	{
		return "page";
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl, $tpl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
				
			case "ilscorm2004pagegui":
				$tpl->getStandardTemplate();
				$this->setContentStyle();
				$this->setLocator();
				// Determine whether the view of a learning resource should
				// be shown in the frameset of ilias, or in a separate window.
				//$showViewInFrameset = $this->ilias->ini->readVariable("layout","view_target") == "frame";
				$showViewInFrameset = true;

				$ilCtrl->setReturn($this, "edit");
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageGUI.php");
				$page_gui =& new ilSCORM2004PageGUI($this->slm_object->getType(),
					$this->node_object->getId(), 0,
					$this->getParentGUI()->object->getId(),
					$this->slm_object->getAssignedGlossary());
				$page_gui->setEditPreview(true);
				$page_gui->setPresentationTitle($this->node_object->getTitle());
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
					$this->slm_object->getStyleSheetId(), "sahs"));

				if ($this->node_object->tree->getParentId($this->node_object->getId()) > 0)
				{
					$sco = new ilSCORM2004Sco(
								$this->node_object->getSLMObject(),
								$this->node_object->tree->getParentId(
								$this->node_object->getId()));
					if (count($sco->getGlossaryTermIds()) > 1)
					{
						include_once("./Modules/Scorm2004/classes/class.ilSCORM2004ScoGUI.php");
						$page_gui->setGlossaryOverviewInfo(
							ilSCORM2004ScoGUI::getGlossaryOverviewId(), $sco);
					}
				}
				
				$ilCtrl->setParameterByClass("ilobjscorm2004learningmodulegui",
					"active_node", $_GET["obj_id"]);
				$page_gui->setExplorerUpdater("tree", "tree_div",
					$ilCtrl->getLinkTargetByClass("ilobjscorm2004learningmodulegui",
						"showTree", "", true));
				$ilCtrl->setParameterByClass("ilobjscorm2004learningmodulegui",
					"active_node", "");

				// set page view link
				$view_frame = ilFrameTargetInfo::_getFrame("MainContent");
				$page_gui->setLinkParams("ref_id=".$this->slm_object->getRefId());				
				$tpl->setTitleIcon(ilUtil::getImagePath("icon_pg.svg"));
				
				$page_gui->activateMetaDataEditor($this->slm_object->getID(),
					$this->node_object->getId(), $this->node_object->getType(),
					$this->node_object,'MDUpdateListener');

				
				$ret = $ilCtrl->forwardCommand($page_gui);
				$this->setTabs();
				$tpl->setContent($ret);
				break;

			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	* Edit -> switch to ilscorm2004pagegui
	*/
	function edit()
	{
		global $ilCtrl;
		
		$ilCtrl->setCmdClass("ilscorm2004pagegui");
		$ilCtrl->setCmd("edit");
		$this->executeCommand();
	}
	
	/**
	* output tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $tpl, $lng;

		// metadata
/*		$ilTabs->addTarget("meta_data",
			 $ilCtrl->getLinkTargetByClass("ilmdeditorgui",''),
			 "", "ilmdeditorgui");*/
			 
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_pg.svg"));
		$tpl->setTitle(
			$lng->txt("sahs_page").": ".$this->node_object->getTitle());
	}

}
?>
