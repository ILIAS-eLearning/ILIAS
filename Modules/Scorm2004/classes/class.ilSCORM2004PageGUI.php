<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Page.php");
require_once './Modules/Scorm2004/classes/class.ilQuestionExporter.php';

/**
* Class ilSCORM2004Page GUI class
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @ilCtrl_Calls ilSCORM2004PageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
* @ilCtrl_Calls ilSCORM2004PageGUI: ilRatingGUI, ilPublicUserProfileGUI, ilPageObjectGUI, ilNoteGUI
* @ilCtrl_Calls ilSCORM2004PageGUI: ilMDEditorGUI
*
* @ingroup ModulesScormAicc
*/
class ilSCORM2004PageGUI extends ilPageObjectGUI
{
	/**
	* Constructor
	*/
	function __construct($a_parent_type, $a_id = 0, $a_old_nr = 0, $a_slm_id = 0)
	{
		global $tpl;
		
		parent::__construct($a_parent_type, $a_id, $a_old_nr);
		
		$this->setEnabledMaps(false);
		$this->setPreventHTMLUnmasking(false);
		$this->setEnabledInternalLinks(false);
		$this->setEnabledSelfAssessment(true);
		$this->setEnabledPCTabs(true);
		
		$this->slm_id = $a_slm_id;
		$this->enableNotes(true, $this->slm_id);
	}
	
	function initPageObject($a_parent_type, $a_id, $a_old_nr)
	{
		$page = new ilSCORM2004Page($a_id, $a_old_nr);
		$this->setPageObject($page);
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilmdeditorgui':
				return parent::executeCommand();
				break;

			case "ilpageobjectgui":
				$page_gui = new ilPageObjectGUI("sahs",
					$this->getPageObject()->getId(), $this->getPageObject()->old_nr);
				$page_gui->setEnabledPCTabs(true);
				$html = $ilCtrl->forwardCommand($page_gui);
				return $html;
				
			default:
				$html = parent::executeCommand();
				return $html;
		}
	}

	/**
	* Set SCORM2004 Page Object.
	*
	* @param	object	$a_scpage	Page Object
	*/
	function setSCORM2004Page($a_scpage)
	{
		$this->setPageObject($a_scpage);
	}

	/**
	* Get SCORM2004 Page Object.
	*
	* @return	object		Page Object
	*/
	function getSCORM2004Page()
	{
		return $this->getPageObject();
	}

	/*function preview()
	{
		global $ilCtrl;
		
		$wtpl = new ilTemplate("tpl....html",
			true, true, "Modules/Scorm2004");
		
		$wtpl->setVariable("PAGE", parent::preview());
		return $wtpl->get();
	}*/
	
	/**
	* Get question html for page
	*/
	function getQuestionHtmlOfPage()
	{
		$q_ids = $this->getPageObject()->getQuestionIds();

		$html = array();
		if (count($q_ids) > 0)
		{
			foreach ($q_ids as $q_id)
			{
				include_once("./Modules/TestQuestionPool/classes/class.assQuestionGUI.php");
				$q_gui =& assQuestionGUI::_getQuestionGUI("", $q_id);
				$q_gui->outAdditionalOutput();				
				$html[$q_id] = $q_gui->getPreview(TRUE);
			}
		}
		return $html;
	
	}
	

	function getQuestionJsOfPage($a_no_interaction = false)
	{
		$q_ids = $this->getPageObject()->getQuestionIds();
		$js = array();
		if (count($q_ids) > 0)
		{
			foreach ($q_ids as $q_id)
			{
				$q_exporter = new ilQuestionExporter($a_no_interaction);
				$js[$q_id] = $q_exporter->exportQuestion($q_id);
			}
		}
		return $js;
	}
	
	/**
	* Show the page
	*/
	function showPage($a_mode = "preview")
	{
		global $tpl, $ilCtrl;
		
		if ($a_mode == "preview") { 
			$qhtml = $this->getQuestionJsOfPage(($this->getOutputMode()=="edit") ? true : false);
			$this->setQuestionHTML($qhtml);
			//include JQuery Libraries before Prototpye
			$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery.js");
			$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery-ui-min.js");
			$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/pure.js");
			$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/question_handling.js");
		}
				
		$this->setTemplateOutput(false);
		
		
		$output = parent::showPage();
		
		if ($a_mode == "preview") { 
			$output = "<script>var ScormApi=null;".ilQuestionExporter::questionsJS()."</script>".$output;
		}
		
		return $output;
	}
}
?>
