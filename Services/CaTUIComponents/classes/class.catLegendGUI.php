<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Legend for tables,
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/UICore/classes/class.ilTemplate.php");
require_once("Services/Utilities/classes/class.ilUtil.php");

class catLegendGUI {
	protected $items = array();
	
	public function __construct() {
		global $lng;
		$this->lng = &$lng;
	}
	
	static public function create() {
		return new catLegendGUI();
	}
	
	public function clearItems() {
		$this->items = array();
	}
	
	public function addItem($a_icon_html, $a_lng_var) {
		$this->items[] = array($a_icon_html, $a_lng_var);
		return $this;
	}
	
	public function item($a_icon_html, $a_lng_var) {
		return $this->addItem($a_icon_html, $a_lng_var);
	}
	
	public function render() {
		$tpl = new ilTemplate("tpl.cat_legend.html", true, true, "Services/CaTUIComponents");
		
		foreach ($this->items as $item) {
			$tpl->setCurrentBlock("item");
			$tpl->setVariable("ICON_HTML", $item[0]);
			$tpl->setVariable("TEXT", $this->lng->txt($item[1]));
			$tpl->parseCurrentBlock();
		}
		
		return $tpl->get();
	}
}

?>