<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Titles for the CaT-GUI.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
require_once("Services/jQuery/classes/class.iljQueryUtil.php");

class catAccordionTableGUI extends catTableGUI {
	public function __construct($a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
	
		global $tpl;
		iljQueryUtil::initjQuery();
		$tpl->addJavaScript("./Services/CaTUIComponents/js/catAccordionTable.js");
	}

	public function getAccordionButtonExpanderClass() {
		return "cat_accordion_button_exp";
	}
	
	public function getAccordionButtonDeexpanderClass() {
		return "cat_accordion_button_deexp";
	}	
	
	public function getAccordionRowClass() {
		return "cat_accordion_row";
	}
	
	public function getColspan() {
		return $this->column_count;
	}
}

?>