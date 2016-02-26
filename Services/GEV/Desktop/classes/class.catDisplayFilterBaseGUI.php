<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Test Baseclass for cat Filter GUIS
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*
*/

class catDisplayFilterBaseGUI {
	protected $gCtrl;
	protected $gTpl;

	public function __construct() {
		global $ilCtrl, $tpl;

		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd("showFilter");

		switch($cmd) {
			case "showFilter":
			case "saveFilter":
				$this->cmd();
				break;
			default:
				throw new Exception("Command not found");
		}
	}

	protected function showFilter(array $post_values = array()) {
		$display_filter = new DisplayFilter(new FilterGUIFactory());
		$gui = $display_filter->getNextFilterGUI($sequence, $post_values);

		$form = new ilPropertyFormGUI();
		$form->setTitle("Mööp");
		$form->setFormAction($this->gCtrl->getFormAction($this));
		$form->addCommandButton("saveFilter","Weiter");

		foreach ($post_values as $key => $value) {
			$hidden = new ilHiddenInputGUI("filter[$key]");
			$hidden->setValue($value);
			$form->addItem($hidden);
		}

		$gui->fillForm($form);

		$this->gTpl->setContent($form->getHTML());
	}

	protected function saveFilter() {
		var_dump($_POST);
	}

}