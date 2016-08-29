<?php
use CaT\Plugins\TalentAssessment;
use CaT\Plugins\TalentAssessment\Observator as Observator;

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/class.ilTalentAssessmentSettingsGUI.php");
require_once(__DIR__."/class.ilTalentAssessmentObservatorGUI.php");

/**
 * User Interface class for career goal repository object.
 *
 * @ilCtrl_isCalledBy ilObjTalentAssessmentGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTalentAssessmentGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjTalentAssessmentGUI: ilTalentAssessmentSettingsGUI, ilTalentAssessmentObservatorGUI, ilRepositorySearchGUI
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de> 
 */
class ilObjTalentAssessmentGUI extends ilObjectPluginGUI {
	use TalentAssessment\Settings\ilFormHelper;

	const CMD_PROPERTIES = "editProperties";
	const CMD_SHOWCONTENT = "showContent";
	const CMD_SUMMARY = "showSummary";
	const CMD_OBSERVATIONS = "showObservations";
	const CMD_OBSERVATOR = "showObservator";

	const TAB_SETTINGS = "tab_settings";
	const TAB_OBSERVATIONS = "tab_observations";
	const TAB_OBSERVATOR = "tab_observator";

	/**
	 * Initialisation
	 */
	protected function afterConstructor() {
		global $ilAccess, $ilTabs, $ilCtrl, $ilToolbar;

		$this->gAccess = $ilAccess;
		$this->gTabs = $ilTabs;
		$this->gCtrl = $ilCtrl;
	}

	/**
	 * Get type.
	 */
	final function getType() {
		return "xtas";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	function performCommand($cmd) {
		$next_class = $this->gCtrl->getNextClass($this);

		switch($next_class) {
			case "ilrepositorysearchgui":
				include_once "./Services/Search/classes/class.ilRepositorySearchGUI.php";
				$rep_search = new ilRepositorySearchGUI();
				$obj = new ilTalentAssessmentObservatorGUI($this, $this->object->getActions(), $this->plugin->txtClosure(), $this->object->getId());
				$rep_search->setCallback($obj,
					"addObservator",
					$status
				);
				$rep_search->disableRoleSearch(true);

				$this->gCtrl->setReturn($this, "showObservator");
				$this->gCtrl->forwardCommand($rep_search);
				break;
			default:
				switch ($cmd) {
					case ilTalentAssessmentSettingsGUI::CMD_SHOW:
					case ilTalentAssessmentSettingsGUI::CMD_SAVE:
						$this->forwardSettings();
						break;
					case ilTalentAssessmentObservatorGUI::CMD_SHOW:
						$this->showObservator();
						break;
					case ilTalentAssessmentObservatorGUI::CMD_ADD:
					case ilTalentAssessmentObservatorGUI::CMD_DELETE:
					case ilTalentAssessmentObservatorGUI::CMD_DELETE_SELECTED:
					case ilTalentAssessmentObservatorGUI::CMD_CONFIRMED_DELETE:
						$this->forwardObservator();
						break;
					case self::CMD_PROPERTIES:
					case self::CMD_SHOWCONTENT:
						$this->showContent();
						break;
					case self::CMD_OBSERVATIONS:
					case self::CMD_OBSERVATOR:
						$this->$cmd();
						break;
				}
		}
	}

	/**
	 * After object has been created -> jump to this command
	 */
	function getAfterCreationCmd() {
		return "editProperties";
	}

	/**
	 * Get standard command
	 */
	function getStandardCmd() {
		return "showContent";
	}

	public function initCreateForm($a_new_type) {
		$form = parent::initCreateForm($a_new_type);

		$db = $this->plugin->getSettingsDB();
		$career_goal_options = $db->getCareerGoalsOptions();
		$venue_options = $db->getVenueOptions();
		$org_unit_options = $db->getOrgUnitOptions();
		$this->addSettingsFormItems($form, $career_goal_options, $venue_options, $org_unit_options);

		return $form;
	}

	public function afterSave(\ilObject $newObj) {
		$post = $_POST;
		$db = $this->plugin->getSettingsDB();
		$settings = $db->create((int)$newObj->getId(), \CaT\Plugins\TalentAssessment\Settings\TalentAssessment::IN_PROGRESS, 0
								, "text", "text", "text", "text", new \ilDateTime(date("Y-m-d H:i:s"), IL_CAL_DATETIME)
								, new \ilDateTime(date("Y-m-d H:i:s"), IL_CAL_DATETIME), 0, 0);
		$newObj->setSettings($settings);
		$actions = $newObj->getActions();
		$actions->update($post);

		$actions->createLocalRole($newObj);

		parent::afterSave($newObj);
	}

	/**
	 * Set tabs
	 */
	protected function setTabs() {
		$this->addInfoTab();

		if ($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			$this->gTabs->addTab(self::TAB_SETTINGS, $this->txt("properties")
				,$this->gCtrl->getLinkTarget($this, self::CMD_PROPERTIES));

			$this->gTabs->addTab(self::TAB_OBSERVATIONS, $this->txt("observations")
				,$this->gCtrl->getLinkTarget($this, self::CMD_OBSERVATIONS));

			$this->gTabs->addTab(self::TAB_OBSERVATOR, $this->txt("observator")
				,$this->gCtrl->getLinkTarget($this, self::CMD_OBSERVATOR));
		}

		$this->addPermissionTab();
	}

	protected function forwardSettings() {
		if(!$this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->gCtrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->gTabs->setTabActive(self::TAB_SETTINGS);
			$actions = $this->object->getActions();
			$gui = new ilTalentAssessmentSettingsGUI($actions, $this->plugin->txtClosure());
			$this->gCtrl->forwardCommand($gui);
		}
	}

	protected function showContent() {
		$_GET["cmd"] = ilTalentAssessmentSettingsGUI::CMD_SHOW;
		$this->forwardSettings();
	}

	protected function showObservations() {
		if(!$this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->gCtrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->gTabs->setTabActive(self::TAB_OBSERVATIONS);
			$actions = $this->object->getActions();
			// $gui = new ilTalentAssessmentSettingsGUI($actions, $this->plugin->txtClosure());
			// $this->gCtrl->forwardCommand($gui);
		}
	}

	protected function showObservator() {
		$this->renderUserSearch();
		$this->forwardObservator();
	}

	protected function renderUserSearch() {
		include_once "./Services/Search/classes/class.ilRepositorySearchGUI.php";
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$this->gToolbar,
				array(
					"auto_complete_name"	=> $this->txt("user"),
					"user_type"				=> $types,
					"submit_name"			=> $this->txt("add"),
					"add_search"			=> false
				)
			);
	}

	protected function forwardObservator() {
		if(!$this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->gCtrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->gTabs->setTabActive(self::TAB_OBSERVATOR);
			$actions = $this->object->getActions();
			$gui = new ilTalentAssessmentObservatorGUI($this, $actions, $this->plugin->txtClosure(), $this->object->getId());
			$this->gCtrl->forwardCommand($gui);
		}
	}
}