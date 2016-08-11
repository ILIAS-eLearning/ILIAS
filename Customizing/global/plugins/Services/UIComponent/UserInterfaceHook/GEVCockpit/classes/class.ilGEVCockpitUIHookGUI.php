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
		if ($a_part != "template_get" || $a_par["tpl_id"] != "Services/MainMenu/tpl.main_menu.html") {
			return parent::getHTML($a_comp, $a_part, $a_par);
		}

		$html = $this->getSubmenuHTML();

		return array
			( "mode" => ilUIHookPluginGUI::APPEND
			, "html" => $html
			);
	}

	protected function getSubMenuHTML() {
		global $tpl;

		$my_tpl = $this->plugin_object->getTemplate("tpl.submenu.html", false, false);
		$tpl->addCss($this->plugin_object->getStyleSheetLocation("submenu.css"));
		return $my_tpl->get();
	}
}
