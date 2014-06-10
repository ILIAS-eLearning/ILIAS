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
	protected $_title = null;
	
	public function __construct($a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
		parent::setEnableTitle(false);
		
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
	
	public function render() {
		//print_r($this);
		//die();
		if ($this->_title_enabled) {
			return $this->_title->render().parent::render();
		}
		return parent::render();
	}
}

?>