<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Setup Languages table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSetupLanguageTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_client)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($lng->getInstallableLanguages());
		//$this->setTitle($lng->txt(""));
		$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt("language"));
		$this->addColumn($this->lng->txt("installed"));
		$this->addColumn($this->lng->txt("include_local"));
		$this->addColumn($this->lng->txt("default"));
		
		$this->setEnableHeader(true);
		$this->setFormAction("setup.php?cmd=gateway");
		$this->setRowTemplate("tpl.setup_lang_table_row.html", "setup");
		$this->disable("footer");
		$this->setEnableTitle(true);
		//$this->setWidth("");
		
		$this->client = $a_client;
		$this->installed_langs = $lng->getInstalledLanguages();
		$this->installed_local_langs = $lng->getInstalledLocalLanguages();
		$this->local_langs = $lng->getLocalLanguages();
		$this->default_lang = $this->client->getDefaultLanguage();


		$this->addCommandButton("saveLanguages", $lng->txt("save"));
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($lang_key)
	{

		global $lng;
		$this->tpl->setVariable("LANG_KEY", $lang_key);
		$this->tpl->setVariable("TXT_LANG", $this->lng->txt("lang_".$lang_key));
		
		if (in_array($lang_key,$this->installed_langs))
		{
			$this->tpl->setVariable("CHECKED", ("checked=\"checked\""));
		}

		if (!in_array($lang_key,$this->local_langs))
		{
			$this->tpl->setVariable("LOCAL", ("disabled=\"disabled\""));        
		}
		else if (in_array($lang_key,$this->installed_local_langs))
		{
			$this->tpl->setVariable("LOCAL", ("checked=\"checked\""));
		}

		if ($lang_key == $this->default_lang)
		{
			$this->tpl->setVariable("DEFAULT", ("checked=\"checked\""));
		}
	}

}
?>
