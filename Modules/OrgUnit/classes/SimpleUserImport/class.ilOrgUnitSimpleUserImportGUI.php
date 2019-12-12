<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOrgUnitSimpleUserImportGUI
 *
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitSimpleUserImportGUI
{

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var ilObjOrgUnit|ilObjCategory
     */
    protected $parent_object;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilAccessHandler
     */
    protected $ilAccess;


    /**
     * @param $parent_gui
     */
    public function __construct($parent_gui)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilToolbar = $DIC['ilToolbar'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $log = $DIC['log'];
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->parent_gui = $parent_gui;
        $this->parent_object = $parent_gui->object;
        $this->tabs_gui = $this->parent_gui->tabs_gui;
        $this->toolbar = $ilToolbar;
        $this->lng = $lng;
        $this->ilLog = $log;
        $this->ilAccess = $ilAccess;
        $this->lng->loadLanguageModule('user');
        if (!$this->ilAccess->checkaccess('write', '', $this->parent_gui->object->getRefId())) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
        }
    }


    /**
     * @return bool
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass('ilOrgUnitSimpleImportGUI', 'chooseImport'));

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


    public function userImportScreen()
    {
        $form = $this->initForm();
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * @description FSX Can be deleted; Just for a single Test of a UserImport
     */
    protected function testImport()
    {
        return false;
        $importer = new ilOrgUnitSimpleUserImport();
        $string = '<Assignment action=\'add\'>
						<User id_type=\'ilias_login\'>root</User>
                        <OrgUnit id_type=\'external_id\'>imported_001</OrgUnit>
						<Role>superior</Role>
                    </Assignment>';

        $xml = new SimpleXMLElement($string);
        $importer->simpleUserImportElement($xml);
        ilUtil::sendInfo('<pre>' . print_r($importer->getErrors(), 1) . '</pre>');
    }


    protected function initForm()
    {
        $form = new ilPropertyFormGUI();
        $input = new ilFileInputGUI($this->lng->txt('import_xml_file'), 'import_file');
        $input->setRequired(true);
        $form->addItem($input);
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton('startImport', $this->lng->txt('import'));

        return $form;
    }


    public function startImport()
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
                ilUtil::sendFailure($this->lng->txt('import_failed'), true);
                $this->ctrl->redirect($this, 'render');
            }
            $this->displayImportResults($importer);
        }
    }


    /**
     * @param $importer ilOrgUnitImporter
     */
    public function displayImportResults($importer)
    {
        if (!$importer->hasErrors() and !$importer->hasWarnings()) {
            $stats = $importer->getStats();
            ilUtil::sendSuccess(sprintf($this->lng->txt('user_import_successful'), $stats['created'], $stats['removed']), true);
        }
        if ($importer->hasWarnings()) {
            $msg = $this->lng->txt('import_terminated_with_warnings') . '<br>';
            foreach ($importer->getWarnings() as $warning) {
                $msg .= '-' . $this->lng->txt($warning['lang_var']) . ' (Import ID: ' . $warning['import_id'] . ')<br>';
            }
            ilUtil::sendInfo($msg, true);
        }
        if ($importer->hasErrors()) {
            $msg = $this->lng->txt('import_terminated_with_errors') . '<br>';
            foreach ($importer->getErrors() as $warning) {
                $msg .= '- ' . $this->lng->txt($warning['lang_var']) . ' (Import ID: ' . $warning['import_id'] . ')<br>';
            }
            ilUtil::sendFailure($msg, true);
        }
    }
}
