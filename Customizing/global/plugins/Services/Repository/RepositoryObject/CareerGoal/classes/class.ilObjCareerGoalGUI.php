<?php
use CaT\Plugins\CareerGoal;

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/class.ilCareerGoalSettingsGUI.php");

/**
 * User Interface class for career goal repository object.
 *
 * @ilCtrl_isCalledBy ilObjCareerGoalGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjCareerGoalGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCareerGoalGUI: ilCareerGoalSettingsGUI
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
	function setTabs() {
		global $ilTabs, $ilCtrl, $ilAccess;

		$this->addInfoTab();

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
			$ilTabs->addTab(self::TAB_SETTINGS, $this->txt("properties"),
			$ilCtrl->getLinkTarget($this, self::CMD_PROPERTIES));

			$ilTabs->addTab(self::TAB_REQUIREMENT, $this->txt("requirements"),
			$ilCtrl->getLinkTarget($this, self::CMD_REQUIREMENT));

			$ilTabs->addTab(self::TAB_OBSERVATIONS, $this->txt("observations"),
			$ilCtrl->getLinkTarget($this, self::CMD_OBSERVATIONS));
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