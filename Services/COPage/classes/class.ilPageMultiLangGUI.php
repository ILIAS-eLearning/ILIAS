<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageMultiLang.php");

/**
 * Page multilinguality GUI class.
 * This could be generalized as an object service in the future. 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilPageMultiLangGUI
{
	/**
	 * Constructur
	 *
	 * @param
	 * @return
	 */
	function __construct($a_parent_type, $a_parent_id)
	{
		$this->ml = new ilPageMultiLang($a_parent_type, $a_parent_id);
	}
	
	/**
	 * Execute command
	 *
	 * @param
	 * @return
	 */
	function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass();
		
		switch ($next_class)
		{
			default:
				$cmd = $ilCtrl->getCmd("settings");
				if (in_array($cmd, array("settings", "activateMultilinguality", "cancel",
					"saveMultilingualitySettings")))
				{
					$this->$cmd();
				}
		}
	}
	
	/**
	 * Settings
	 *
	 * @param
	 * @return
	 */
	function settings()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;
		
		if (!$this->ml->getActivated())
		{
			ilUtil::sendInfo($lng->txt("cont_multilang_currently_deactivated"));
			$ilToolbar->addButton($lng->txt("cont_activate_multi_lang"),
				$ilCtrl->getLinkTarget($this, "activateMultilinguality"));
		}
		else
		{
			$ilToolbar->addButton($lng->txt("cont_deactivate_multi_lang"),
				$ilCtrl->getLinkTarget($this, "deactivateMultilinguality"));
		}
	}
	
	/**
	 * Activate multi language (-> master language selection)
	 */
	function activateMultilinguality()
	{
		global $tpl, $lng;
		
		ilUtil::sendInfo($lng->txt("cont_select_master_lang"));
		
		$form = $this->getMultiLangForm();
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Get multi language form
	 */
	function getMultiLangForm()
	{
		global $tpl, $lng, $ilCtrl, $ilUser;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		// master language
		include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
		$options = ilMDLanguageItem::_getLanguages();
		$si = new ilSelectInputGUI($lng->txt("cont_master_lang"), "master_lang");
		$si->setOptions($options);
		$si->setValue($ilUser->getLanguage());
		$form->addItem($si);
		
		// additional languages
		include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
		$options = ilMDLanguageItem::_getLanguages();
		$options = array("" => $lng->txt("please_select")) + $options;
		$si = new ilSelectInputGUI($lng->txt("cont_additional_langs"), "additional_langs");
		$si->setOptions($options);
		$si->setMulti(true);
		$form->addItem($si);
		
		$form->addCommandButton("saveMultilingualitySettings", $lng->txt("save"));
		$form->addCommandButton("cancel", $lng->txt("cancel"));
		$form->setTitle($lng->txt("cont_activate_multi_lang"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		return $form;
	}

	/**
	 * Return to parent
	 *
	 * @param
	 * @return
	 */
	function cancel()
	{
		global $ilCtrl;

		$ilCtrl->returnToParent($this);
	}
	
	/**
	 * Save multlilinguality settings
	 */
	function saveMultilingualitySettings()
	{
		global $ilCtrl;
		
		include_once("./Services/COPage/classes/class.ilPageMultiLang.php");
		
		$form = $this->getMultiLangForm();
		if ($form->checkInput())
		{
			$ml = $form->getInput("master_lang");
			$this->ml->setMasterLanguage($ml);
			
			$ad = $form->getInput("additional_langs");
			foreach ($ad as $l)
			{
				if ($l != $ml && $l != "")
				{
					$this->ml->addLanguage($l);
				}
			}
			$this->ml->save();
		}
		
		$ilCtrl->redirect($this, "settings");
	}

	/**
	 * Get multi lang info
	 *
	 * @param
	 * @return
	 */
	function getMultiLangInfo($a_page_lang = "-")
	{
		global $lng;
		
		if ($a_page_lang == "")
		{
			$a_page_lang = "-";
		}
		
		$lng->loadLanguageModule("meta");
		
		$tpl = new ilTemplate("tpl.page_multi_lang_info.html", true, true, "Services/COPage");
		$tpl->setVariable("TXT_MASTER_LANG", $lng->txt("cont_master_lang"));
		$tpl->setVariable("VAL_ML", $lng->txt("meta_l_".$this->ml->getMasterLanguage()));
		$cl = ($a_page_lang == "-")
			? $this->ml->getMasterLanguage()
			: $a_page_lang;
		$tpl->setVariable("TXT_CURRENT_LANG", $lng->txt("cont_current_lang"));
		$tpl->setVariable("VAL_CL", $lng->txt("meta_l_".$cl));
		return $tpl->get();
	}
	
	
}

?>
