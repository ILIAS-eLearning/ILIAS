<?php
use CaT\Plugins\TalentAssessment;

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/class.ilTalentAssessmentSettingsGUI.php");

/**
 * User Interface class for career goal repository object.
 *
 * @ilCtrl_isCalledBy ilObjTalentAssessmentGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTalentAssessmentGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjTalentAssessmentGUI: ilCareerGoalSettingsGUI
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de> 
 */
class ilObjTalentAssessmentGUI extends ilObjectPluginGUI {
	use TalentAssessment\Settings\ilFormHelper;

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
			case ilTalentAssessmentSettingsGUI::CMD_SHOW:
			case ilTalentAssessmentSettingsGUI::CMD_SAVE:
				$this->forwardSettings();
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
}