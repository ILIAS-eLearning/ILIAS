<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */
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
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjCategory $object;
    protected ilLanguage $lng;
    protected ilAccessHandler $ilAccess;
    protected ilObjectGUI $parent_gui;
    protected ilObject $parent_object;
    protected ilToolbarGUI $toolbar;

    public function __construct(ilObjectGUI $parent_gui)
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->parent_gui = $parent_gui;
        $this->parent_object = $parent_gui->getObject();
        $this->tabs_gui = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->ilAccess =  $DIC->access();
        $this->lng->loadLanguageModule('user');
        if (!$this->ilAccess->checkaccess("write", "", $this->parent_gui->getObject()->getRefId())) {
            $main_tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
        }
    }

    public function executeCommand(): bool
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

    public function edit(): void
    {
        $form = $this->initForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function initForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $input = new ilTextInputGUI($this->lng->txt("ext_id"), "ext_id");
        $input->setValue($this->parent_object->getImportId());
        $form->addItem($input);
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton("update", $this->lng->txt("save"));

        return $form;
    }

    public function update(): void
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
