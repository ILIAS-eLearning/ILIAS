<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Titles for the CaT-GUI.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");

class catTableGUI extends ilTable2GUI {
	protected $_title_enabled = false;
	protected $_enable_advice = false;
	protected $_title = null;

	public function __construct($a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
		parent::setEnableTitle(false);

		$this->_title = new catTitleGUI();
		$this->advice = "";
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

	public function setAdvice($a_advice) {
		$this->advice = $a_advice;
	}

	public function getAdvice() {
		return $this->advice;
	}

	public function setImage($a_img) {
		$this->_title->setImage($a_img);
		return $this;
	}
	
	public function getImage() {
		return $this->_title->getImage();
	}
	
	public function setLegend(catLegendGUI $a_legend) {
		$this->_title->setLegend($a_legend);
		return $this;
	}

	public function getLegend() {
		return $this->_title->getLegend();
	}

	public function setCommand($a_lng_var, $a_target) {
		$this->_title->setCommand($a_lng_var, $a_target);
		return $this;
	}

	public function removeCommand() {
		$this->_title->removeCommand();
		return $this;
	}

	public function setEnabaleAdvice($a_enable_advice) {
		$this->_enable_advice = $a_enable_advice;
	}

	private function renderAdvice() {
		$tpl = new ilTemplate("tpl.gev_my_advice.html", true, true, "Services/GEV/Desktop");

		$tpl->setCurrentBlock("advice");
		$tpl->setVariable("ADVICE", $this->lng->txt($this->getAdvice()));
			$tpl->parseCurrentBlock();

		return $tpl->get();
	}

	public function render() {
		$ret = "";

		if ($this->_title_enabled) {
			$ret .= $this->_title->render()."<br />";
		}

		if($this->_enable_advice) {
			$ret .= $this->renderAdvice()."<br />";
		}

		$ret .= parent::render();

		return $ret;
	}
	
	protected function fillRow($a_set)
	{
		foreach ($a_set as $key => $value) {
			$this->tpl->setVariable("VAL_".strtoupper($key), $value);
		}
	}
}

?>