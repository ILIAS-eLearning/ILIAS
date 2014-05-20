<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Titles for the CaT-GUI.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/UICore/classes/class.ilTemplate.php");
require_once("Services/Utilities/classes/class.ilUtil.php");

class catTitleGUI {
	protected $title;
	protected $subtitle;
	protected $img;
	protected $legend;
	
	public function __construct($a_title = null, $a_subtitle = null, $a_img = null) {
		global $lng, $ilCtrl;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		
		$this->title = $a_title;
		$this->subtitle = $a_subtitle;
		$this->img = $a_img;
		$this->legend = null;
	}
	
	public function setTitle($a_title) {
		$this->title = $a_title;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setSubtitle($a_subtitle) {
		$this->subtitle = $a_subtitle;
	}
	
	public function getSubtitle() {
		return $this->subtitle;
	}
	
	public function setImage($a_img) {
		$this->img = $a_img;
	}
	
	public function getImage() {
		return $this->img;
	}
	
	public function setLegend($a_legend) {
		die ("catTitleGUI::setLegend: NYI!"); // TODO: implement this!
	}
	
	public function getLegend() {
		return $this->legend;
	}
	
	public function render() {
		$tpl = new ilTemplate("tpl.cat_title.html", true, true, "Services/CaTUIComponents");
		
		if ($this->title !== null) {
			$tpl->setCurrentBlock("title");
			$tpl->setVariable("TITLE", $this->lng->txt($this->title));
			$tpl->parseCurrentBlock();
		}
		
		if ($this->subtitle !== null) {
			$tpl->setCurrentBlock("title");
			$tpl->setVariable("SUBTITLE", $this->lng->txt($this->subtitle));
			$tpl->parseCurrentBlock();
		}
		
		if ($this->img !== null) {
			$tpl->setCurrentBlock("image");
			$tpl->setVariable("IMG_PATH", ilUtil::getImagePath($this->img));
			$tpl->parseCurrentBlock();
		}

		if ($this->legend !== null) {
			$tpl->setCurrentBlock("legend");
			$this->setVariable("LEGEND", $this->legend->render());
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}
}

?>