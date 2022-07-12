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
 * Class ilOrgUnitSimpleUserImportGUI
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitSimpleUserImportGUI
{
    protected ilTabsGUI $tabs_gui;
    protected ilToolbarGUI $toolbar;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected object $parent_object;
    protected object $parent_gui;
    protected ilLanguage $lng;
    protected ilAccessHandler $ilAccess;
    protected \ILIAS\DI\LoggingServices $ilLog;


    public function __construct(object $parent_gui)
    {
        global $DIC;
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->parent_gui = $parent_gui;
        $this->parent_object = $parent_gui->getObject();
        $this->tabs_gui = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->ilLog = $DIC->logger();
        $this->ilAccess = $DIC->access();
        $this->lng->loadLanguageModule('user');
        if (!$this->ilAccess->checkaccess('write', '', $this->parent_gui->getObject()->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
        }
    }

    public function executeCommand(): bool
    {
        $cmd = $this->ctrl->getCmd();

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt("back"),
            $this->ctrl->getLinkTargetByClass('ilOrgUnitSimpleImportGUI', 'chooseImport'));

        switch ($cmd) {
            case 'userImportScreen':
                $this->userImportScreen();
                break;
            case 'startImport':
                $this->startImport();
                break;
        }

        return true;
    }

    public function userImportScreen(): void
    {
        $form = $this->initForm();
        $this->tpl->setContent($form->getHTML());
    }

    private function initForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $input = new ilFileInputGUI($this->lng->txt('import_xml_file'), 'import_file');
        $input->setRequired(true);
        $form->addItem($input);
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton('startImport', $this->lng->txt('import'));

        return $form;
    }

    public function startImport(): void
    {
        $form = $this->initForm();
        if (!$form->checkInput()) {
            $this->tpl->setContent($form->getHTML());
        } else {
            $file = $form->getInput('import_file');
            $importer = new ilOrgUnitSimpleUserImport();
            try {
                $importer->simpleUserImport($file['tmp_name']);
            } catch (Exception $e) {
                $this->ilLog->write($e->getMessage() . ' - ' . $e->getTraceAsString());
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('import_failed'), true);
                $this->ctrl->redirect($this, 'render');
            }
            $this->displayImportResults($importer);
        }
    }

    public function displayImportResults(ilOrgUnitImporter $importer): void
    {
        if (!$importer->hasErrors() and !$importer->hasWarnings()) {
            $stats = $importer->getStats();
            $this->tpl->setOnScreenMessage('success',
                sprintf($this->lng->txt('user_import_successful'), $stats['created'], $stats['removed']), true);
        }
        if ($importer->hasWarnings()) {
            $msg = $this->lng->txt('import_terminated_with_warnings') . '<br>';
            foreach ($importer->getWarnings() as $warning) {
                $msg .= '-' . $this->lng->txt($warning['lang_var']) . ' (Import ID: ' . $warning['import_id'] . ')<br>';
            }
            $this->tpl->setOnScreenMessage('info', $msg, true);
        }
        if ($importer->hasErrors()) {
            $msg = $this->lng->txt('import_terminated_with_errors') . '<br>';
            foreach ($importer->getErrors() as $warning) {
                $msg .= '- ' . $this->lng->txt($warning['lang_var']) . ' (Import ID: ' . $warning['import_id'] . ')<br>';
            }
            $this->tpl->setOnScreenMessage('failure', $msg, true);
        }
    }
}
