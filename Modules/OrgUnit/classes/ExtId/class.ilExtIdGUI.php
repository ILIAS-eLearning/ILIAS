<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExtIdGUI
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilExtIdGUI
{
    protected ilTabsGUI $tabs_gui;
    protected ilPropertyFormGUI $form;
    protected ilCtrl $ctrl;
    protected ilTemplate $tpl;
    protected ilObjOrgUnit|ilObjCategory $object;
    protected ilLanguage $lng;
    protected ilAccessHandler $ilAccess;

    public function __construct(ilObjectGUI $parent_gui)
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $ilToolbar = $DIC['ilToolbar'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->parent_gui = $parent_gui;
        $this->parent_object = $parent_gui->getObject();
        $this->tabs_gui = $DIC->tabs();
        $this->toolbar = $ilToolbar;
        $this->lng = $lng;
        $this->ilAccess = $ilAccess;
        $this->lng->loadLanguageModule('user');
        if (!$this->ilAccess->checkaccess("write", "", $this->parent_gui->getObject()->getRefId())) {
            $main_tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
        }
    }

    public function executeCommand() : bool
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case 'edit':
                $this->edit();
                break;
            case 'update':
                $this->update();
                break;
        }

        return true;
    }

    public function edit() : void
    {
        $form = $this->initForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $input = new ilTextInputGUI($this->lng->txt("ext_id"), "ext_id");
        $input->setValue($this->parent_object->getImportId());
        $form->addItem($input);
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton("update", $this->lng->txt("save"));

        return $form;
    }

    public function update() : void
    {
        $form = $this->initForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $this->parent_object->setImportId($form->getItemByPostVar("ext_id")->getValue());
            $this->parent_object->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("ext_id_updated"), true);
            $this->ctrl->redirect($this, "edit");
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }
}
