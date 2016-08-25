<?php
use CaT\Plugins\CareerGoal;

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/class.ilCareerGoalSettingsGUI.php");
require_once(__DIR__."/class.ilCareerGoalRequirementsGUI.php");
require_once(__DIR__."/class.ilCareerGoalObservationsGUI.php");

/**
 * User Interface class for career goal repository object.
 *
 * @ilCtrl_isCalledBy ilObjCareerGoalGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjCareerGoalGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCareerGoalGUI: ilCareerGoalSettingsGUI, ilCareerGoalRequirementsGUI, ilCareerGoalObservationsGUI
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de> 
 */
class ilObjCareerGoalGUI extends ilObjectPluginGUI {
	use CareerGoal\Settings\ilFormHelper;

	const CMD_REQUIREMENT = "showRequirements";
	const CMD_OBSERVATIONS = "showObservations";
	const CMD_PROPERTIES = "editProperties";
	const CMD_SHOWCONTENT = "showContent";
	const CMD_SUMMARY = "showSummary";


	const TAB_SETTINGS = "tab_settings";
	const TAB_REQUIREMENT = "tab_requirements";
	const TAB_OBSERVATIONS = "tab_observations";

	/**
	 * Initialisation
	 */
	protected function afterConstructor() {
		global $ilAccess, $ilTabs, $ilCtrl;

		$this->gAccess = $ilAccess;
		$this->gTabs = $ilTabs;
		$this->gCtrl = $ilCtrl;
	}

	/**
	 * Get type.
	 */
	final function getType() {
		return "xcgo";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			case ilCareerGoalSettingsGUI::CMD_SHOW:
			case ilCareerGoalSettingsGUI::CMD_SAVE:
				$this->forwardSettings();
				break;
			case ilCareerGoalRequirementsGUI::CMD_SHOW:
			case ilCareerGoalRequirementsGUI::CMD_ADD:
			case ilCareerGoalRequirementsGUI::CMD_EDIT:
			case ilCareerGoalRequirementsGUI::CMD_SAVE:
			case ilCareerGoalRequirementsGUI::CMD_UPDATE:
			case ilCareerGoalRequirementsGUI::CMD_DELETE:
			case ilCareerGoalRequirementsGUI::CMD_SORT:
			case ilCareerGoalRequirementsGUI::CMD_CONFIRMED_DELETE:
			case ilCareerGoalRequirementsGUI::CMD_DELETE_SELECTED_REQUIREMENTS:
			case ilCareerGoalRequirementsGUI::CMD_CONFIRMED_DELETE_SELECTED_REQUIREMENTS:
			case ilCareerGoalRequirementsGUI::CMD_SAVE_ORDER:
				$this->forwardRequirements();
				break;
			case ilCareerGoalObservationsGUI::CMD_SHOW:
			case ilCareerGoalObservationsGUI::CMD_ADD:
			case ilCareerGoalObservationsGUI::CMD_EDIT:
			case ilCareerGoalObservationsGUI::CMD_SAVE:
			case ilCareerGoalObservationsGUI::CMD_UPDATE:
			case ilCareerGoalObservationsGUI::CMD_DELETE:
			case ilCareerGoalObservationsGUI::CMD_SORT:
			case ilCareerGoalObservationsGUI::CMD_CONFIRMED_DELETE:
			case ilCareerGoalObservationsGUI::CMD_DELETE_SELECTED_OBSERVATIONS:
			case ilCareerGoalObservationsGUI::CMD_CONFIRMED_DELETE_SELECTED_OBSERVATIONS:
			case ilCareerGoalObservationsGUI::CMD_SAVE_ORDER:
				$this->forwardObservations();
				break;
			case self::CMD_PROPERTIES:
			case self::CMD_SHOWCONTENT:
				$this->showContent();
				break;
			case self::CMD_REQUIREMENT:
			case self::CMD_OBSERVATIONS:
				$this->$cmd();
				break;
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
		$this->addSettingsFormItems($form);

		return $form;
	}

	public function afterSave(\ilObject $newObj) {
		$post = $_POST;
		$db = $this->plugin->getSettingsDB();
		$settings = $db->create((int)$newObj->getId(), 0, 0, "text", "text", "text");
		$newObj->setSettings($settings);
		$actions = $newObj->getActions();
		$actions->update($post);

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

			$this->gTabs->addTab(self::TAB_REQUIREMENT, $this->txt("requirements")
				,$this->gCtrl->getLinkTarget($this, ilCareerGoalRequirementsGUI::CMD_SHOW));

			$this->gTabs->addTab(self::TAB_OBSERVATIONS, $this->txt("observations")
				,$this->gCtrl->getLinkTarget($this, self::CMD_OBSERVATIONS));
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
			$gui = new ilCareerGoalSettingsGUI($actions, $this->plugin->txtClosure());
			$this->gCtrl->forwardCommand($gui);
		}
	}

	protected function forwardRequirements() {
		if(!$this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->gCtrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->gTabs->setTabActive(self::TAB_REQUIREMENT);
			$actions = $this->object->getActions();
			$gui = new ilCareerGoalRequirementsGUI($actions, $this->plugin->txtClosure(), $this->object->getId());
			$this->gCtrl->forwardCommand($gui);
		}
	}

	protected function forwardObservations() {
		if(!$this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->gCtrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->gTabs->setTabActive(self::TAB_REQUIREMENT);
			$actions = $this->object->getActions();
			$gui = new ilCareerGoalObservationsGUI($actions, $this->plugin->txtClosure(), $this->object->getId());
			$this->gCtrl->forwardCommand($gui);
		}
	}

	protected function showContent() {
		$_GET["cmd"] = ilCareerGoalSettingsGUI::CMD_SHOW;
		$this->forwardSettings();
	}

	protected function showRequirements() {
		$this->gTabs->setTabActive(self::TAB_REQUIREMENT);
	}

	protected function showObservations() {
		$this->gTabs->setTabActive(self::TAB_OBSERVATIONS);
	}
}