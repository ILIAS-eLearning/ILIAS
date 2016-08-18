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
			|| 	(!$this->isCockpit() && !$this->isSearch())
		   ) {
			return parent::getHTML($a_comp, $a_part, $a_par);
		}

		$this->active = $this->getActiveItem();
		$this->items = $this->getItems();

		$current_skin = ilStyleDefinition::getCurrentSkin();

		$this->addCss($current_skin);
		$html = $this->getSubMenuHTML($current_skin);

		return array
			( "mode" => ilUIHookPluginGUI::APPEND
			, "html" => $html
			);
	}

	protected function isCockpit() {
		global $ilUser;
		return
			( $_GET["baseClass"] == "gevDesktopGUI" 
				|| ($_GET["cmdClass"] == "ilobjreportedubiogui"
					&& $_GET["target_user_id"] == $ilUser->getId())
				|| $_GET["baseClass"] == "ilTEPGUI"
			)
			&& $_GET["cmdClass"] != "gevcoursesearchgui"
			&& $_GET["cmdClass"] != "iladminsearchgui"
			;
	}

	protected function isSearch() {
		return $_GET["baseClass"] == "gevDesktopGUI"
			&& $_GET["cmdClass"] == "gevcoursesearchgui"
			;
	}

	protected function getActiveItem() {
		if ($this->isCockpit()) {
			if ($_GET["cmdClass"] == "gevmycoursesgui") {
				return "bookings";
			}
			if ($_GET["cmdClass"] == "ilobjreportedubiogui") {
				return "edubio";
			}
			if ($_GET["cmdClass"] == "gevuserprofilegui") {
				return "profile";
			}
			if ($_GET["baseClass"] == "ilTEPGUI") {
				return "tep";
			}
			if ($_GET["cmdClass"] == "gevmytrainingsapgui") {
				return "trainer_ops";
			}
			if ($_GET["cmdClass"] == "gevmytrainingsadmingui") {
				return "training_admin";
			}
		}
		if ($this->isSearch()) {
			if ($_GET["active_tab"] == "praes") {
				return "search_onside";
			}
			if ($_GET["active_tab"] == "webinar") {
				return "search_webinar";
			}
			if ($_GET["active_tab"] == "self") {
				return "search_wbt";
			}
			return "search_all";
		}

		return null;
	}

	protected function getItems() {
		if ($this->isCockpit()) {
			return $this->getCockpitItems();
		}
		else if ($this->isSearch()) {
			return array
				( "search"
					=> array("Suche", "http://www.google.de")
				, "search_all"
					=> array("Alle", "http://www.google.de")
				, "search_onside"
					=> array("Präsenz", "http://www.google.de")
				, "search_webinar"
					=> array("Webinar", "http://www.google.de")
				, "search_wbt"
					=> array("Selbstlernkurs", "http://www.google.de")
				);
		}
		else {
			throw new \LogicException("Should not get here...");
		}
	}

	protected function getCockpitItems() {
		global $ilUser;
		global $lng;

		if ($ilUser->getId() !== 0) {
			$user_utils = gevUserUtils::getInstanceByObj($ilUser);
		}
		else {
			$user_utils = null;
		}

		$items = array();

		$items["bookings"]
			= array($lng->txt("gev_bookings"), "ilias.php?baseClass=gevDesktopGUI&cmd=toMyCourses");

		if ($user_utils && ($edu_bio_link = $user_utils->getEduBioLink())) {
			$items["edubio"]
				= array($lng->txt("gev_edu_bio"), $user_utils->getEduBioLink());
		}

		$items["profile"]
			= array($lng->txt("gev_user_profile"), "ilias.php?baseClass=gevDesktopGUI&cmd=toMyProfile");


		require_once("Services/TEP/classes/class.ilTEPPermissions.php");
		if ($user_utils && ($user_utils->isAdmin() || ilTEPPermissions::getInstance($ilUser->getId())->isTutor())) {
			$lng->loadLanguageModule("tep");
			$items["tep"]
				= array($lng->txt("tep_personal_calendar_title"), "ilias.php?baseClass=ilTEPGUI");

			$items["trainer_ops"]
				= array($lng->txt("gev_mytrainingsap_title"), "ilias.php?baseClass=gevDesktopGUI&cmd=toMyTrainingsAp");
		}

		$items["training_admin"]
			= array($lng->txt("gev_my_trainings_admin"), "ilias.php?baseClass=gevDesktopGUI&cmd=toMyTrainingsAdmin");

		return $items;
	}

	protected function getSubMenuHTML($current_skin) {
		assert('is_string($current_skin)');
		$tpl = $this->getTemplate($current_skin, true, true); 
		$count = 1;
		foreach ($this->items as $id => $data) {
			list($label, $link) = $data;
			if ($this->active == $id) {
				$tpl->touchBlock("active");
			}
			$tpl->setCurrentBlock("item");
			$tpl->setVariable("ID", $id);
			$tpl->setVariable("LABEL", $label);
			$tpl->setVariable("LINK", $link);
			$tpl->parseCurrentBlock();
			$count++;
		}
		return $tpl->get();
	}

	protected function addCss($current_skin) {
		assert('is_string($current_skin)');
		global $tpl;
		$loc = $this->getStyleSheetLocation($current_skin);
		$tpl->addCss($loc);
	}

	protected function getTemplate($current_skin, $remove_unknown_vars, $remove_empty_blocks) {
		assert('is_string($current_skin)');
		$skin_folder = $this->getSkinFolder($current_skin);
		$tpl_file = "tpl.submenu.html";
		$tpl_path = $skin_folder."/Plugins/GEVCockpit/$tpl_file";
		if (is_file($tpl_path)) {
			return new ilTemplate($tpl_path, $remove_unknown_vars, $remove_empty_blocks);
		}
		else {
			return $this->plugin_object->getTemplate("tpl.submenu.html", $remove_unknown_vars, $remove_empty_blocks);
		}
	}

	protected function getStyleSheetLocation($current_skin) {
		assert('is_string($current_skin)');
		$skin_folder = $this->getSkinFolder($current_skin);
		$css_file = "submenu.css";
		$css_path = $skin_folder."/Plugins/GEVCockpit/$css_file";
		if (is_file($css_path)) {
			return $css_path;
		}
		else {
			return $this->plugin_object->getStyleSheetLocation("submenu.css");
		}
	}

	protected function getSkinFolder($current_skin) {
		assert('is_string($current_skin)');
		return "./Customizing/global/skin/$current_skin";
	}
}
