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
 * Class ilOrgUnitSimpleImportGUI
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitSimpleImportGUI
{
    protected ilTabsGUI $tabs_gui;
    protected ilToolbarGUI $toolbar;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjectGUI $parent_gui;
    protected object $parent_object;
    protected ilLanguage $lng;
    protected ilAccessHandler $ilAccess;
    protected \ILIAS\DI\LoggingServices $ilLog;

    public function __construct(ilObjectGUI $parent_gui)
    {
        global $DIC;
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->parent_gui = $parent_gui;
        $this->parent_object = $parent_gui->getObject();
        $this->tabs_gui = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->ilAccess = $DIC->access();
        $this->lng->loadLanguageModule('user');
        $this->ilLog = $DIC->logger();

        $this->may_create_orgus = $this->ilAccess->checkAccess("create_orgu", "", $this->parent_gui->getRefId(), 'orgu');
        $this->is_top_level_orgu = ($this->parent_object->getRefId() == ilObjOrgUnit::getRootOrgRefId());

        if (!$this->may_create_orgus) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
        }
    }

    /**
     * @return bool
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case 'chooseImport':
                $this->chooseImport();
                break;
            case 'importScreen':
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, 'chooseImport')
                );
                $this->importScreen();
                break;
            case 'startImport':
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, 'chooseImport')
                );
                $this->startImport();
                break;
        }

        return true;
    }

    public function chooseImport()
    {
        if (!$this->may_create_orgus || !$this->is_top_level_orgu) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_perm_edit"));
            $this->ctrl->redirectByClass('ilinfoscreengui', '');
        }

        $this->tabs_gui->setTabActive('view_content');
        $this->tabs_gui->removeSubTab("page_editor");
        $this->tabs_gui->removeSubTab("ordering"); // Mantis 0014728

        if ($this->may_create_orgus && $this->is_top_level_orgu) {
            $this->toolbar->addButton(
                $this->lng->txt("simple_import"),
                $this->ctrl->getLinkTargetByClass("ilOrgUnitSimpleImportGUI", "importScreen")
            );
            $this->toolbar->addButton(
                $this->lng->txt("simple_user_import"),
                $this->ctrl->getLinkTargetByClass("ilOrgUnitSimpleUserImportGUI", "userImportScreen")
            );
        }
    }

    public function importScreen()
    {
        $form = $this->initForm("startImport");
        $this->tpl->setContent($form->getHTML());
    }

    protected function initForm($submit_action)
    {
        $form = new ilPropertyFormGUI();
        $input = new ilFileInputGUI($this->lng->txt("import_xml_file"), "import_file");
        $input->setRequired(true);
        $input->setSuffixes(array('zip', 'xml'));
        $form->addItem($input);
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton($submit_action, $this->lng->txt("import"));

        return $form;
    }

    public function startImport()
    {
        $form = $this->initForm("startImport");
        if (!$form->checkInput()) {
            $this->tpl->setContent($form->getHTML());
        } else {
            $file = $form->getInput("import_file");
            $importer = new ilOrgUnitSimpleImport();
            try {
                $file_path = $file["tmp_name"];
                $file_type = pathinfo($file["name"], PATHINFO_EXTENSION);
                $file_name = pathinfo($file["name"], PATHINFO_FILENAME);

                if ($file_type == "zip") {
                    $extract_path = $file_path . '_extracted/';
                    $extracted_file = $extract_path . $file_name . '/manifest.xml';

                    $zip = new ZipArchive();
                    $res = $zip->open($file_path);
                    if ($res === true) {
                        $zip->extractTo($extract_path);
                        $zip->close();

                        if (file_exists($extracted_file)) {
                            $file_path = $extracted_file;
                        }
                    }
                }

                $importer->simpleImport($file_path);
            } catch (Exception $e) {
                $this->ilLog->write($e->getMessage() . " - " . $e->getTraceAsString());
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("import_failed"), true);
                $this->ctrl->redirect($this, "render");
            }
            $this->displayImportResults($importer);
        }
    }

    /**
     * @param $importer ilOrgUnitImporter
     */
    public function displayImportResults($importer)
    {
        if (!$importer->hasErrors() && !$importer->hasWarnings()) {
            $stats = $importer->getStats();
            $this->tpl->setOnScreenMessage(
                'success',
                sprintf($this->lng->txt("import_successful"), $stats["created"], $stats["updated"], $stats["deleted"]),
                true
            );
        }
        if ($importer->hasWarnings()) {
            $msg = $this->lng->txt("import_terminated_with_warnings") . " <br/>";
            foreach ($importer->getWarnings() as $warning) {
                $msg .= "-" . $this->lng->txt($warning["lang_var"]) . " (Import ID: " . $warning["import_id"] . ")<br />";
            }
            $this->tpl->setOnScreenMessage('info', $msg, true);
        }
        if ($importer->hasErrors()) {
            $msg = $this->lng->txt("import_terminated_with_errors") . "<br/>";
            foreach ($importer->getErrors() as $warning) {
                $msg .= "- " . $this->lng->txt($warning["lang_var"]) . " (Import ID: " . $warning["import_id"] . ")<br />";
            }
            $this->tpl->setOnScreenMessage('failure', $msg, true);
        }
    }
}
