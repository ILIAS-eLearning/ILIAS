<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilPageMultiLangTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $lng;

		$lng->loadLanguageModule("meta");

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($lng->txt("cont_languages"));
		
		$this->addColumn("", "", "1");
		$this->addColumn($this->lng->txt("cont_language"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.page_ml_row.html", "Services/COPage");

		//if (count($this->getData()) > 1)
		//{
			$this->addMultiCommand("confirmRemoveLanguages", $lng->txt("remove"));
		//}
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		if (!$a_set["master"])
		{
			$this->tpl->setCurrentBlock("cb");
			$this->tpl->setVariable("CB_LANG", $a_set["lang"]);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setVariable("ML", "(".$lng->txt("cont_master_lang").")");
		}
		$this->tpl->setVariable("LANG", $lng->txt("meta_l_".$a_set["lang"]));
	}

}
?>