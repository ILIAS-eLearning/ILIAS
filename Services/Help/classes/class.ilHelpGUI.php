<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ("Services/Help/classes/class.ilHelp.php");

/**
* Help GUI class.
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*/
class ilHelpGUI
{
	var $help_sections = array();
	
	/**
	* constructor
	*/
	function ilHelpGUI()
	{
		global $ilCtrl;
				
		$this->ctrl =& $ilCtrl;
	}
	

	
	/**
	 * Add help section
	 *
	 * @param
	 * @return
	 */
	function addHelpSection($a_help_id, $a_level = 1)
	{
		$this->help_sections[] = array("help_id" => $a_help_id, $a_level);
	}
	
	/**
	 * Has sections?
	 *
	 * @param
	 * @return
	 */
	function hasSections()
	{
		return (count($this->help_sections) > 0);
	}
	
	/**
	 * Get help sections
	 *
	 * @param
	 * @return
	 */
	function getHelpSections()
	{
		return $this->help_sections;
	}
	
	/**
	 * Get help section url parameter
	 *
	 * @param
	 * @return
	 */
	function setCtrlPar()
	{
		global $ilCtrl;
		
		$h_ids = $sep = "";
		foreach ($this->getHelpSections() as $hs)
		{
			$h_ids.= $sep.$hs["help_id"];
			$sep = ",";
		}
		$ilCtrl->setParameterByClass("ilhelpgui", "help_ids", $h_ids);
	}
	

	/**
	* execute command
	*/
	function executeCommand()
	{
		$cmd = $this->ctrl->getCmd("showHelp");
		$next_class = $this->ctrl->getNextClass($this);
		
		switch($next_class)
		{
			default:
				return $this->$cmd();
				break;
		}
	}
	
	/**
	 * Show online help
	 */
	function showHelp()
	{
		global $ilHelp, $lng;
		
		if ($_GET["help_ids"] != "")
		{
			ilSession::set("help_ids", $_GET["help_ids"]);
			$help_ids = $_GET["help_ids"];
		}
		else
		{
			$help_ids = ilSession::get("help_ids");
		}
		
		$help_arr = explode(",", $help_ids);
		
		if (OH_REF_ID > 0 && count($help_arr) > 0)
		{
			$oh_lm_id = ilObject::_lookupObjId(OH_REF_ID);
			
			include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
			$acc = new ilAccordionGUI();
			$acc->setId("oh_acc");
			$acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);
			
			foreach ($help_arr as $h_id)
			{
				include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
				$data = ilLMObject::getExportIDInfo(ilObject::_lookupObjId(OH_REF_ID),
					$h_id, "st");
				$st_id = $data[0]["obj_id"];
				
				$pages = ilLMObject::getPagesOfChapter($oh_lm_id, $st_id);
				include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
				$grp_list = new ilGroupedListGUI();
				foreach ($pages as $pg)
				{
					$grp_list->addEntry(ilLMObject::_lookupTitle($pg["child"]), "#", "",
						"return ilHelp.showPage(".$pg["child"].");");
				}
				
				$acc->addItem(ilLMObject::_lookupTitle($st_id), $grp_list->getHTML());
			}
			$h_tpl = new ilTemplate("tpl.help.html", true, true, "Services/Help");
			$h_tpl->setVariable("HEAD", $lng->txt("help"));
			$h_tpl->setVariable("CONTENT", $acc->getHTML());
			$h_tpl->setVariable("CLOSE_IMG", ilUtil::img(ilUtil::getImagePath("icon_close2_s.gif")));
			echo $h_tpl->get();
		}
		exit;
	}
	
	/**
	 * Show page
	 *
	 * @param
	 * @return
	 */
	function showPage()
	{
		global $lng;
		
		$page_id = (int) $_GET["help_page"];
		
		$h_tpl = new ilTemplate("tpl.help.html", true, true, "Services/Help");
		include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
		
		$h_tpl->setCurrentBlock("backlink");
		$h_tpl->setVariable("TXT_BACK", $lng->txt("back"));
		$h_tpl->setVariable("ONCLICK_BACK",
			"return ilHelp.listHelp(event);");
		$h_tpl->parseCurrentBlock();
		
		
		$h_tpl->setVariable("HEAD", $lng->txt("help")." - ".
			ilLMObject::_lookupTitle($page_id));
		
		include_once("./Services/COPage/classes/class.ilPageUtil.php");
		if (!ilPageUtil::_existsAndNotEmpty("lm", $page_id))
		{
			exit;
		}
		include_once("./Services/COPage/classes/class.ilPageObject.php");
		include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");

		// get page object
		include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
		$page_gui =& new ilPageObjectGUI("lm", $page_id);
		$page_gui->setPresentationTitle("");
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader("");
		$page_gui->setEnabledFileLists(false);
		$page_gui->setEnabledPCTabs(false);
		$page_gui->setFileDownloadLink(".");
		$page_gui->setFullscreenLink(".");
		$page_gui->setSourcecodeDownloadScript(".");
		$page_gui->setRawPageContent(true);
		$page_gui->setEnabledMaps(false);
		$ret = $page_gui->showPage();

		$h_tpl->setVariable("CONTENT", $ret);
		$h_tpl->setVariable("CLOSE_IMG", ilUtil::img(ilUtil::getImagePath("icon_close2_s.gif")));
		
		
		echo $h_tpl->get();
		exit;
	}
	

}
?>