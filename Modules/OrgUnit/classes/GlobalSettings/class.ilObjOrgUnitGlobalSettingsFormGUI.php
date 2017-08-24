<?php
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * GUI-Class ilObjOrgUnitGlobalSettingsFormGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilObjOrgUnitGlobalSettingsFormGUI extends ilPropertyFormGUI {

    public function __construct($parent_gui) {
        global $ilCtrl, $lng;

        $this->ctrl = $ilCtrl;
        $this->parent_gui = $parent_gui;
        $this->lng = $lng;

        $this->setPreventDoubleSubmission(true);
        $this->setFormAction($this->ctrl->getFormAction($parent_gui));
        $this->initForm();
    }


    protected function initForm() {
        global $ilSetting;

        // Enable My Staff
        $item = new ilCheckboxInputGUI($this->lng->txt("orgu_enable_my_staff"), "enable_my_staff");
        $item->setInfo($this->lng->txt("orgu_enable_my_staff_info"));
        $item->setValue("1");
        $item->setChecked(($ilSetting->get("enable_my_staff") ? "1" : "0"));
        $this->addItem($item);

        $this->addCommandButtons();
    }

    public function storeObject() {
        global $ilSetting;
        $ilSetting->set("enable_my_staff", (int) ($_POST["enable_my_staff"] ? 1 : 0));
    }


    protected function addCommandButtons() {
        $this->addCommandButton('save', $this->lng->txt('save'));
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }
}
?>