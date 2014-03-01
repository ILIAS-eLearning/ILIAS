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
	protected $single_page_mode = false;

	/**
	 * Constructur
	 *
	 * @param string $a_parent_type parent object type
	 * @param int $a_parent_id parent object id
	 * @param bool $a_single_page_mode single page mode (page includes ml managing)
	 */
	function __construct($a_parent_type, $a_parent_id, $a_single_page_mode = false)
	{
		//$this->ml = new ilPageMultiLang($a_parent_type, $a_parent_id);

		// object translation
		include_once("./Services/Object/classes/class.ilObjectTranslation.php");
		$this->ot = ilObjectTranslation::getInstance($a_parent_id);
		
//		$this->single_page_mode = $a_single_page_mode;
	}
	
	/**
	 * Execute command
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
					"saveMultilingualitySettings", "confirmDeactivateMultiLanguage", "addLanguage",
					"saveLanguages", "deactivateMultiLang", "confirmRemoveLanguages",
					"removeLanguages")))
				{
					$this->$cmd();
				}
		}
	}
	
	/**
	 * Settings
	 */
/*	function settings()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;

		$this->setTabs();
		
		if (!$this->ml->getActivated())
		{
			ilUtil::sendInfo($lng->txt("cont_multilang_currently_deactivated"));
			$ilToolbar->addButton($lng->txt("cont_activate_multi_lang"),
				$ilCtrl->getLinkTarget($this, "activateMultilinguality"));
		}
		else
		{
			$ilToolbar->addButton($lng->txt("cont_add_lang"),
				$ilCtrl->getLinkTarget($this, "addLanguage"));

			$ilToolbar->addButton($lng->txt("cont_deactivate_multi_lang"),
				$ilCtrl->getLinkTarget($this, "confirmDeactivateMultiLanguage"));

			include_once("./Services/COPage/classes/class.ilPageMultiLangTableGUI.php");
			$tab = new ilPageMultiLangTableGUI($this, "managaMultiLanguage");
			$langs[] = array("master" => true, "lang" => $this->ml->getMasterLanguage());
			foreach ($this->ml->getLanguages() as $l)
			{
				$langs[] = array("master" => false, "lang" => $l);
			}
			$tab->setData($langs);


			$tpl->setContent($tab->getHTML());
		}
	}
*/
	/**
	 * Activate multi language (-> master language selection)
	 */
/*	function activateMultilinguality()
	{
		global $tpl, $lng;

		$this->setTabs();

		ilUtil::sendInfo($lng->txt("cont_select_master_lang"));
		
		$form = $this->getMultiLangForm();
		$tpl->setContent($form->getHTML());
	}
*/
	/**
	 * Get multi language form
	 */
/*	function getMultiLangForm($a_add = false)
	{
		global $tpl, $lng, $ilCtrl, $ilUser;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		// master language
		if (!$a_add)
		{
			include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
			$options = ilMDLanguageItem::_getLanguages();
			$si = new ilSelectInputGUI($lng->txt("cont_master_lang"), "master_lang");
			$si->setOptions($options);
			$si->setValue($ilUser->getLanguage());
			$form->addItem($si);
		}
		
		// additional languages
		include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
		$options = ilMDLanguageItem::_getLanguages();
		$options = array("" => $lng->txt("please_select")) + $options;
		$si = new ilSelectInputGUI($lng->txt("cont_additional_langs"), "additional_langs");
		$si->setOptions($options);
		$si->setMulti(true);
		$form->addItem($si);

		if ($a_add)
		{
			$form->addCommandButton("saveLanguages", $lng->txt("save"));
			$form->addCommandButton("settings", $lng->txt("cancel"));
		}
		else
		{
			$form->addCommandButton("saveMultilingualitySettings", $lng->txt("save"));
			$form->addCommandButton("cancel", $lng->txt("cancel"));
		}
		$form->setTitle($lng->txt("cont_activate_multi_lang"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		return $form;
	}
*/
	/**
	 * Return to parent
	 */
/*	function cancel()
	{
		global $ilCtrl;

		$ilCtrl->returnToParent($this);
	}
*/
	/**
	 * Save multlilinguality settings
	 */
/*	function saveMultilingualitySettings()
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
	}*/

	/**
	 * Get multi lang info
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
		$tpl->setVariable("TXT_MASTER_LANG", $lng->txt("obj_master_lang"));
		$tpl->setVariable("VAL_ML", $lng->txt("meta_l_".$this->ot->getMasterLanguage()));
		$cl = ($a_page_lang == "-")
			? $this->ot->getMasterLanguage()
			: $a_page_lang;
		$tpl->setVariable("TXT_CURRENT_LANG", $lng->txt("cont_current_lang"));
		$tpl->setVariable("VAL_CL", $lng->txt("meta_l_".$cl));
		return $tpl->get();
	}

	/**
	 * Confirm page translation creation
	 */
/*	function confirmDeactivateMultiLanguage()
	{
		global $ilCtrl, $tpl, $lng;

		$this->setTabs();

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($ilCtrl->getFormAction($this));
		$cgui->setHeaderText($lng->txt("cont_deactivate_multi_lang_conf"));
		$cgui->setCancel($lng->txt("cancel"), "settings");
		$cgui->setConfirm($lng->txt("confirm"), "deactivateMultiLang");
		$tpl->setContent($cgui->getHTML());
	}*/

	/**
	 * Deactivate multilanguage
	 */
/*	function deactivateMultiLang()
	{
		global $lng, $ilCtrl;

		$this->ml->delete();
		ilUtil::sendSuccess($lng->txt("cont_multilang_deactivated"), true);
		$ilCtrl->redirect($this, "settings");
	}*/

	/**
	 * Add language
	 */
/*	function addLanguage()
	{
		global $tpl;

		$this->setTabs();
		$form = $this->getMultiLangForm(true);
		$tpl->setContent($form->getHTML());
	}*/

	/**
	 * Save languages
	 */
/*	function saveLanguages()
	{
		global $ilCtrl, $lng;

		$form = $this->getMultiLangForm();
		if ($form->checkInput())
		{
			$ad = $form->getInput("additional_langs");
			if (is_array($ad))
			{
				$ml = $this->ml->getMasterLanguage();
				foreach ($ad as $l)
				{
					if ($l != $ml && $l != "")
					{
						$this->ml->addLanguage($l);
					}
				}
			}
		}
		$this->ml->save();
		ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "settings");
	}*/

	/**
	 * Confirm remove languages
	 */
/*	function confirmRemoveLanguages()
	{
		global $ilCtrl, $tpl, $lng;

		$this->setTabs();

		$lng->loadLanguageModule("meta");
			
		if (!is_array($_POST["lang"]) || count($_POST["lang"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "settings");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("cont_conf_delete_lang"));
			$cgui->setCancel($lng->txt("cancel"), "settings");
			$cgui->setConfirm($lng->txt("remove"), "removeLanguages");
			
			foreach ($_POST["lang"] as $i)
			{
				$cgui->addItem("lang[]", $i, $lng->txt("meta_l_".$i));
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
*/
	/**
	 * Remove languages
	 */
/*	function removeLanguages()
	{
		global $lng, $ilCtrl;

		if (is_array($_POST["lang"]))
		{
			$langs = $this->ml->getLanguages();
			foreach ($langs as $k => $l)
			{
				if (in_array($l, $_POST["lang"]))
				{
					unset($langs[$k]);
				}
			}
			$this->ml->setLanguages($langs);
			$this->ml->save();
			ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
		}
		$ilCtrl->redirect($this, "settings");
	}*/

	/**
	 * Set tab
	 */
/*	function setTabs()
	{
		global $ilTabs, $lng, $ilCtrl;

		if ($this->single_page_mode)
		{
			$ilTabs->clearTargets();
			$ilTabs->setBackTarget($lng->txt("cont_back_to_page"),
				$ilCtrl->getLinkTarget($this, "cancel"));
		}
	}*/

}

?>
