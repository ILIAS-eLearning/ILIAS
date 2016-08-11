<?php

/* Copyright (c) 2016, Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");

/**
 * Creates a submenu for the Cockpit of the GEV.
 */
class ilGEVCockpitUIHookGUI extends ilUIHookPluginGUI {
	/**
	 * @inheritdoc
	 */
	function getHTML($a_comp, $a_part, $a_par = array()) {
		if ( 	$a_part != "template_get"
			|| 	$a_par["tpl_id"] != "Services/MainMenu/tpl.main_menu.html"
			|| 	!$this->isCockpit()
		   ) {
			return parent::getHTML($a_comp, $a_part, $a_par);
		}

		$this->items = array
			( "Buchungen"
			, "Bildungsbiografie"
			, "Profil"
			, "TEP"
			, "Trainingseinsätze"
			, "Trainingsverwaltung"
			);

		$this->addCss();
		$html = $this->getSubmenuHTML();

		return array
			( "mode" => ilUIHookPluginGUI::APPEND
			, "html" => $html
			);
	}

	protected function isCockpit() {
		return $_GET["baseClass"] == "gevDesktopGUI"
			&& $_GET["cmdClass"] != "gevcoursesearchgui"
			&& $_GET["cmdClass"] != "iladminsearchgui"
			;
	}

	protected function getSubMenuHTML() {
		$tpl = $this->plugin_object->getTemplate("tpl.submenu.html", true, true);
		$count = 1;
		foreach ($this->items as $item) {
			$tpl->setCurrentBlock("item");
			$tpl->setVariable("NUM", $count);
			$tpl->setVariable("LABEL", $item);
			$tpl->parseCurrentBlock();
			$count++;
		}
		return $tpl->get();
	}

	protected function addCss() {
		global $tpl;
		$tpl->addCss($this->plugin_object->getStyleSheetLocation("submenu.css"));
	}
}
