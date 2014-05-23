<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* CaT select input gui, supports submit on change.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/UICore/classes/class.ilTemplate.php");
require_once("Services/Form/classes/class.ilSelectInputGUI.php");

class catSelectInputGUI extends ilSelectInputGUI {
	protected $submit_on_change = false;
	protected $action = "";
	
	public function setSubmitOnChange($a_doit) {
		$this->submit_on_change = $a_doit;
		return $this;
	}
	
	public function setAction($a_action) {
		$this->action = $a_action;
		return $this;
	}

	public function render($a_mode = "") {
		$res = parent::render($a_mode);
		
		$tpl = new ilTemplate("tpl.cat_select_input.html", true, true, "Services/CaTUIComponents");
		
		if (!$this->getParentForm()) {
			if (!$this->action) {
				throw new Exception("You need to define an action on a catSelectInputGUI if it is not part of a form.");
			}
			
			$tpl->setCurrentBlock("form");
			$tpl->setVariable("ACTION", $this->action);
			$tpl->setVariable("SELECT_FORM", $res);
			$tpl->parseCurrentBlock();
		}
		else {
			$tpl->setCurrentBlock("bare");
			$tpl->setVariable("SELECT_BARE", $res);
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable("VAR_NAME", $this->getPostVar());
		
		return $tpl->get();
	}
}

?>