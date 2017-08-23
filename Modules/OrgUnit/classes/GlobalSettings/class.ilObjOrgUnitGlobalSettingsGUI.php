<?php
/**
 * GUI-Class ilObjOrgUnitGlobalSettingsGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilObjOrgUnitGlobalSettingsGUI: ilObjOrgUnitGUI
 */
class ilObjOrgUnitGlobalSettingsGUI {


    function __construct(ilObjOrgUnitGUI $a_parent_gui) {
        global $tpl, $ilCtrl, $ilAccess, $lng;
        /**
         * @var ilTemplate      $tpl
         * @var ilCtrl          $ilCtrl
         * @var ilAccessHandler $ilAccess
         */

        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->a_parent_gui = $a_parent_gui;
        $this->object = $a_parent_gui->object;

        $this->access = $ilAccess;
    }


    protected function checkAccess() {

        if ($this->access->checkAccess('write', '', $this->object->getRefId()) and $this->object->getRefId() == ilObjOrgUnit::getRootOrgRefId()) {
            return true;
        } else {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this->a_parent_gui, "");
        }
    }


    public function executeCommand() {
        $cmd = $this->ctrl->getCmd();

        $this->checkAccess();

        switch ($cmd) {
            case 'showForm':
            case 'cancel':
            case 'save':
                $this->$cmd();
                break;
            default:
                $this->showForm();
                break;
        }
    }


    public function showForm() {
        $this->initForm();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function save() {
        $this->initForm();
        $this->form->setValuesByPost();

        if($this->form->checkInput()) {
            $result = $this->form->storeObject();
            $this->ctrl->redirect($this);
        }
        $this->tpl->setContent($this->form->getHTML());
    }


    public function cancel() {
        $this->ctrl->redirect($this);
    }

    protected function initForm($override = false) {
        if ($override || $this->form == NULL) {
            $this->form = new ilObjOrgUnitGlobalSettingsFormGUI($this);
        }
    }
}
?>