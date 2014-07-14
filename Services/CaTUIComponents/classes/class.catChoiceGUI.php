<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* A gui to get a choice from a user.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/UICore/classes/class.ilTemplate.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");

class catChoiceGUI {
	protected $abort = null;
	protected $choices = array();
	protected $_title = null;
	protected $_title_enabled;
	protected $question = "";
	
	public function __construct() {
		$this->_title = new catTitleGUI();
	}

	public function setEnableTitle($a_enable) {
		$this->_title_enabled = $a_enable;
		return $this;
	}

	public function getEnableTitle() {
		return $this->_title_enabled;
	}

	public function setTitle($a_title) {
		$this->_title->setTitle($a_title);
		return $this;
	}

	public function getTitle() {
		return $this->_title->getTitle;
	}

	public function setSubtitle($a_subtitle) {
		$this->_title->setSubtitle($a_subtitle);
		return $this;
	}

	public function getSubtitle() {
		return $this->_title->getSubtitle();
	}

	public function setImage($a_img) {
		$this->_title->setImage($a_img);
		return $this;
	}
	
	public function getImage() {
		return $this->_title->getImage();
	}
	
	public function setQuestion($a_question) {
		$this->question = $a_question;
	}
	
	public function setAbort($a_title, $a_link) {
		$this->abort = array($a_title, $a_link);
	}
	
	public function addChoice($a_title, $a_link) {
		$this->choices[] = array($a_title, $a_link);
	}
	
	
	public function render() {
		$tpl = new ilTemplate("tpl.cat_choice.html", true, true, "Services/CaTUIComponents");

		if ($this->_title_enabled) {
			$tpl->setCurrentBlock("title");
			$tpl->setVariable("TITLE", $this->_title->render());
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable("QUESTION", $this->question);
		
		if ($this->abort) {
			$tpl->setCurrentBlock("abort");
			$tpl->setVariable("ABORT_LINK", $this->abort[1]);
			$tpl->setVariable("ABORT_TITLE", $this->abort[0]);
			$tpl->parseCurrentBlock();
		}

		foreach ($this->choices as $choice) {
						$tpl->setCurrentBlock("abort");
			$tpl->setVariable("CHOICE_LINK", $choice[1]);
			$tpl->setVariable("CHOICE_TITLE", $choice[0]);
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}
}

?>