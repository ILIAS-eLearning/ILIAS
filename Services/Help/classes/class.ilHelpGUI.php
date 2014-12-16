<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ("Services/Help/classes/class.ilHelp.php");

/**
* Help GUI class.
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*
* @ilCtrl_Calls ilHelpGUI: ilLMPageGUI
*
*/
class ilHelpGUI
{
	var $help_sections = array();
	const ID_PART_SCREEN = "screen";
	const ID_PART_SUB_SCREEN = "sub_screen";
	const ID_PART_COMPONENT = "component";
	var $def_screen_id = array();
	var $screen_id = array();
	
	/**
	* constructor
	*/
	function ilHelpGUI()
	{
		global $ilCtrl;
				
		$this->ctrl =& $ilCtrl;
	}
	
	/**
	 * Set default screen id
	 *
	 * @param
	 * @return
	 */
	function setDefaultScreenId($a_part, $a_id)
	{
		$this->def_screen_id[$a_part] = $a_id;
	}

	/**
	 * Set screen id
	 *
	 * @param
	 */
	function setScreenId($a_id)
	{
		$this->screen_id[self::ID_PART_SCREEN] = $a_id;
	}

	/**
	 * Set sub screen id
	 *
	 * @param
	 */
	function setSubScreenId($a_id)
	{
		$this->screen_id[self::ID_PART_SUB_SCREEN] = $a_id;
	}

	/**
	 * Set screen id component
	 *
	 * @param
	 * @return
	 */
	function setScreenIdComponent($a_comp)
	{
		$this->screen_id_component = $a_comp;
	}
	
	
	/**
	 * Get screen id
	 *
	 * @param
	 * @return
	 */
	function getScreenId()
	{
		$comp = ($this->screen_id_component != "")
			? $this->screen_id_component
			: $this->def_screen_id[self::ID_PART_COMPONENT];
		
		if ($comp == "")
		{
			return "";
		}
		
		$scr_id = ($this->screen_id[self::ID_PART_SCREEN] != "")
			? $this->screen_id[self::ID_PART_SCREEN]
			: $this->def_screen_id[self::ID_PART_SCREEN];
		
		$sub_scr_id = ($this->screen_id[self::ID_PART_SUB_SCREEN] != "")
			? $this->screen_id[self::ID_PART_SUB_SCREEN]
			: $this->def_screen_id[self::ID_PART_SUB_SCREEN];
		
		$screen_id = $comp."/".
			$scr_id."/".
			$sub_scr_id;
			
		return $screen_id;
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
		global $ilSetting;
		
		include_once("./Services/Help/classes/class.ilHelpMapping.php");
		return ilHelpMapping::hasScreenIdSections($this->getScreenId());
	}
	
	/**
	 * Get help sections
	 *
	 * @param
	 * @return
	 */
	function getHelpSections()
	{
		include_once("./Services/Help/classes/class.ilHelpMapping.php");
		return ilHelpMapping::getHelpSectionsForId($this->getScreenId(), (int) $_GET["ref_id"]);
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
		
		/*$h_ids = $sep = "";
		foreach ($this->getHelpSections() as $hs)
		{
			$h_ids.= $sep.$hs;
			$sep = ",";
		}*/
		$ilCtrl->setParameterByClass("ilhelpgui", "help_screen_id", $this->getScreenId().".".$_GET["ref_id"]);
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
		global $ilHelp, $lng, $ilSetting;

		if ($_GET["help_screen_id"] != "")
		{
			ilSession::set("help_screen_id", $_GET["help_screen_id"]);
			$help_screen_id = $_GET["help_screen_id"];
		}
		else
		{
			$help_screen_id = ilSession::get("help_screen_id");
		}
		
		$this->resetCurrentPage();
		
		$id_arr = explode(".", $help_screen_id);
		include_once("./Services/Help/classes/class.ilHelpMapping.php");
		$help_arr = ilHelpMapping::getHelpSectionsForId($id_arr[0], $id_arr[1]);
		
		$hm = (int) $ilSetting->get("help_module");
		
		if ((OH_REF_ID > 0 || $hm > 0) && count($help_arr) > 0)
		{
			if (OH_REF_ID > 0)
			{
				$oh_lm_id = ilObject::_lookupObjId(OH_REF_ID);
			}
			else
			{
				include_once("./Services/Help/classes/class.ilObjHelpSettings.php");
				$oh_lm_id = ilObjHelpSettings::lookupModuleLmId($hm);
			}
			
			include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
			$acc = new ilAccordionGUI();
			$acc->setId("oh_acc_".$h_id);
			$acc->setUseSessionStorage(true);
			$acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);
			
			foreach ($help_arr as $h_id)
			{
				include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
				$st_id = $h_id;
				
				if (!ilLMObject::_exists($st_id))
				{
					continue;
				}

				$pages = ilLMObject::getPagesOfChapter($oh_lm_id, $st_id);
				include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
				$grp_list = new ilGroupedListGUI();
				foreach ($pages as $pg)
				{ 
					$grp_list->addEntry(ilLMObject::_lookupTitle($pg["child"]), "#", "",
						"return il.Help.showPage(".$pg["child"].");");
				}
				
				$acc->addItem(ilLMObject::_lookupTitle($st_id), $grp_list->getHTML());
			}
			$h_tpl = new ilTemplate("tpl.help.html", true, true, "Services/Help");
			$h_tpl->setVariable("HEAD", $lng->txt("help"));
			$h_tpl->setVariable("CONTENT", $acc->getHTML());
			include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
			$h_tpl->setVariable("CLOSE_IMG", ilGlyphGUI::get(ilGlyphGUI::CLOSE));
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
			"return il.Help.listHelp(event, true);");
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
		include_once("./Modules/LearningModule/classes/class.ilLMPageGUI.php");
		$page_gui = new ilLMPageGUI($page_id);
		$cfg = $page_gui->getPageConfig();
		$page_gui->setPresentationTitle("");
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader("");
		$page_gui->setRawPageContent(true);
		$cfg->setEnablePCType("Map", false);
		$cfg->setEnablePCType("Tabs", false);
		$cfg->setEnablePCType("FileList", false);
		
		$page_gui->getPageObject()->buildDom();
		$int_links = $page_gui->getPageObject()->getInternalLinks();
		$link_xml = $this->getLinkXML($int_links);
		$link_xml.= $this->getLinkTargetsXML();
//echo htmlentities($link_xml);
		$page_gui->setLinkXML($link_xml);
		
		$ret = $page_gui->showPage();

		$h_tpl->setVariable("CONTENT", $ret);
		include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
		$h_tpl->setVariable("CLOSE_IMG", ilGlyphGUI::get(ilGlyphGUI::CLOSE));

		ilSession::set("help_pg", $page_id);
		
		$page = $h_tpl->get();
		
		// replace style classes
		//$page = str_replace("ilc_text_inline_Strong", "ilHelpStrong", $page);
		
		echo $page;
		exit;
	}
	
	/**
	 * Hide help
	 *
	 * @param
	 * @return
	 */
	function resetCurrentPage()
	{
		ilSession::clear("help_pg");
	}
	
	
	/**
	 * Get tab tooltip text
	 *
	 * @param string $a_tab_id tab id
	 * @return string tooltip text
	 */
	function getTabTooltipText($a_tab_id)
	{
		global $lng;
		
		include_once("./Services/Help/classes/class.ilHelp.php");
		if ($this->screen_id_component != "")
		{
			return ilHelp::getTooltipPresentationText($this->screen_id_component."_".$a_tab_id);
			//return $lng->txt("help_tt_".$this->screen_id_component."_".$a_tab_id);
		}
		return "";
	}
	
	/**
	 * Render current help page
	 *
	 * @param
	 * @return
	 */
	function initHelp($a_tpl)
	{
		global $ilUser, $ilSetting;

		$module_id = (int) $ilSetting->get("help_module");
		
		if ((OH_REF_ID > 0 || $module_id > 0) && $ilUser->getLanguage() == "de")
		{
			if (ilSession::get("help_pg") > 0)
			{
				$a_tpl->addOnLoadCode("il.Help.showCurrentPage(".ilSession::get("help_pg").");", 3);
			}
			if ($ilUser->getPref("hide_help_tt"))
			{
				$a_tpl->addOnLoadCode("if (il && il.Help) il.Help.switchTooltips();", 3);
			}
		}
	}
	
	/**
	 * Deactivate tooltips
	 *
	 * @param
	 * @return
	 */
	function deactivateTooltips()
	{
		global $ilUser;
		
		$ilUser->writePref("hide_help_tt", "1");
	}
	
	/**
	 * Activate tooltips
	 *
	 * @param
	 * @return
	 */
	function activateTooltips()
	{
		global $ilUser;
		
		$ilUser->writePref("hide_help_tt", "0");
	}
	
	/**
	 * get xml for links
	 */
	function getLinkXML($a_int_links)
	{
		global $ilCtrl;

		$link_info = "<IntLinkInfos>";
		foreach ($a_int_links as $int_link)
		{
			$target = $int_link["Target"];
			if (substr($target, 0, 4) == "il__")
			{
				$target_arr = explode("_", $target);
				$target_id = $target_arr[count($target_arr) - 1];
				$type = $int_link["Type"];
				$targetframe = "None";
					
				// anchor
				$anc = $anc_add = "";
				if ($int_link["Anchor"] != "")
				{
					$anc = $int_link["Anchor"];
					$anc_add = "_".rawurlencode($int_link["Anchor"]);
				}

				switch($type)
				{
					case "PageObject":
					case "StructureObject":
							if ($type == "PageObject")
							{
								$href = "#pg_".$target_id;
							}
							else
							{
								$href = "#";
							}
						break;

				}
				
				$link_info.="<IntLinkInfo Target=\"$target\" Type=\"$type\" ".
					"TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"\" Anchor=\"\"/>";
			}
		}
		$link_info.= "</IntLinkInfos>";

		return $link_info;
	}

	/**
	* Get XMl for Link Targets
	*/
	function getLinkTargetsXML()
	{
		$link_info = "<LinkTargets>";
		$link_info.="<LinkTarget TargetFrame=\"None\" LinkTarget=\"\" OnClick=\"return il.Help.openLink(event);\" />";
		$link_info.= "</LinkTargets>";
		return $link_info;
	}

}
?>