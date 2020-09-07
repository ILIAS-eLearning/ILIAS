<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilWorkflowEngineDefinitionsGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineDefinitionsGUI
{
    /** @var ilObjWorkflowEngineGUI $parent_gui */
    protected $parent_gui;

    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    /**
     * ilWorkflowEngineDefinitionsGUI constructor.
     *
     * @param ilObjWorkflowEngineGUI $parent_gui
     * @param \ILIAS\DI\Container|$dic $dic
     */
    public function __construct(ilObjWorkflowEngineGUI $parent_gui, \ILIAS\DI\Container $dic = null)
    {
        $this->parent_gui = $parent_gui;

        if ($dic === null) {
            $dic = $GLOBALS['DIC'];
        }
        $this->dic = $dic;
    }

    /**
     * Handle the command given.
     *
     * @param string $command
     *
     * @return string HTML
     */
    public function handle($command)
    {
        switch (strtolower($command)) {
            case 'uploadform':
                return $this->showUploadForm();
                break;

            case 'upload':
                return $this->handleUploadSubmit();
                break;

            case 'applyfilter':
                return $this->applyFilter();
                break;

            case 'resetfilter':
                return $this->resetFilter();
                break;

            case 'start':
                return $this->startProcess();
                break;

            case 'delete':
                return $this->deleteDefinition();
                break;

            case 'confirmdelete':
                return $this->confirmDeleteDefinition();
                break;

            case 'startlistening':
                return $this->startListening();
                break;

            case'stoplistening':
                return $this->stopListening();
                break;

            case 'view':
            default:
                return $this->showDefinitionsTable();
        }
    }

    /**
     * @return string HTML
     */
    public function showDefinitionsTable()
    {
        $this->initToolbar();
        require_once './Services/WorkflowEngine/classes/administration/class.ilWorkflowEngineDefinitionsTableGUI.php';
        $table_gui = new ilWorkflowEngineDefinitionsTableGUI($this->parent_gui, 'definitions.view');
        $table_gui->setFilterCommand("definitions.applyfilter");
        $table_gui->setResetCommand("definitions.resetFilter");
        $table_gui->setDisableFilterHiding(false);

        return $table_gui->getHTML();
    }

    /**
     * @return string HTML
     */
    public function applyFilter()
    {
        require_once './Services/WorkflowEngine/classes/administration/class.ilWorkflowEngineDefinitionsTableGUI.php';
        $table_gui = new ilWorkflowEngineDefinitionsTableGUI($this->parent_gui, 'definitions.view');
        $table_gui->writeFilterToSession();
        $table_gui->resetOffset();

        return $this->showDefinitionsTable();
    }

    /**
     * @return string HTML
     */
    public function resetFilter()
    {
        require_once './Services/WorkflowEngine/classes/administration/class.ilWorkflowEngineDefinitionsTableGUI.php';
        $table_gui = new ilWorkflowEngineDefinitionsTableGUI($this->parent_gui, 'definitions.view');
        $table_gui->resetOffset();
        $table_gui->resetFilter();

        return $this->showDefinitionsTable();
    }

    /**
     * @return string
     */
    public function showUploadForm()
    {
        require_once './Services/WorkflowEngine/classes/administration/class.ilUploadDefinitionForm.php';
        $form_definition = new ilUploadDefinitionForm();
        $form = $form_definition->getForm(
            $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.upload')
        );

        return $form->getHTML();
    }

    /**
     * @return string
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function handleUploadSubmit()
    {
        $this->processUploadFormCancellation();

        require_once './Services/WorkflowEngine/classes/administration/class.ilUploadDefinitionForm.php';
        $form_definition = new ilUploadDefinitionForm();
        $form = $form_definition->getForm(
            $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.upload')
        );

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            return $form->getHTML();
        }

        $fs = $this->dic->filesystem()->storage();
        $upload = $this->dic->upload();

        $repositoryDirectory = ilObjWorkflowEngine::getRepositoryDir(true);
        if (!$fs->hasDir($repositoryDirectory)) {
            $fs->createDir($repositoryDirectory);
        }

        $tmpDirectory = ilObjWorkflowEngine::getTempDir(true);
        if (!$fs->hasDir($tmpDirectory)) {
            $fs->createDir($tmpDirectory);
        }

        if (!$upload->hasUploads() || $upload->hasBeenProcessed()) {
            $form->setValuesByPost();
            return $form->getHTML();
        }

        $upload->process();

        /** @var \ILIAS\FileUpload\DTO\UploadResult $uploadResult */
        $uploadResult = array_values($upload->getResults())[0];
        if (!$uploadResult || $uploadResult->getStatus() != \ILIAS\FileUpload\DTO\ProcessingStatus::OK) {
            $form->setValuesByPost();
            return $form->getHTML();
        }

        $upload->moveOneFileTo(
            $uploadResult,
            $tmpDirectory,
            \ILIAS\FileUpload\Location::STORAGE,
            $uploadResult->getName(),
            true
        );

        $repo_base_name = 'il' . substr($uploadResult->getName(), 0, strpos($uploadResult->getName(), '.'));
        $wf_base_name = 'wfd.' . $repo_base_name . '_v';
        $version = 0;

        $fileList = $fs->listContents($repositoryDirectory, true);

        foreach ($fileList as $file) {
            if ($file->isDir()) {
                continue;
            }

            $fileBaseName = basename($file->getPath());

            if (
                substr(strtolower($fileBaseName), 0, strlen($wf_base_name)) == strtolower($wf_base_name) &&
                substr($fileBaseName, -4) == '.php'
            ) {
                $number = substr($fileBaseName, strlen($wf_base_name), -4);
                if ($number > $version) {
                    $version = $number;
                }
            }
        }
        $version++;

        $repo_name = $repo_base_name . '_v' . $version . '.php';

        require_once './Services/WorkflowEngine/classes/parser/class.ilBPMN2Parser.php';
        $parser = new ilBPMN2Parser();
        $bpmn = $fs->read($tmpDirectory . $uploadResult->getName());
        $code = $parser->parseBPMN2XML($bpmn, $repo_name);

        $fs->put($repositoryDirectory . 'wfd.' . $repo_name, $code);
        $fs->put($repositoryDirectory . 'wfd.' . $repo_base_name . '_v' . $version . '.bpmn2', $bpmn);
        $fs->delete($tmpDirectory . $uploadResult->getName());

        // TODO: Workaround because of file extension whitelist. You currently cannot create/put '.php' files
        $absRepositoryDirectory = ilObjWorkflowEngine::getRepositoryDir();
        $sourceFile = $absRepositoryDirectory . str_replace('.', '', 'wfd.' . $repo_name) . '.sec';
        $targetFile = $absRepositoryDirectory . 'wfd.' . $repo_name;
        if (file_exists($sourceFile)) {
            rename($sourceFile, $targetFile);
        }

        ilUtil::sendSuccess($this->parent_gui->lng->txt('upload_parse_success'), true);
        ilUtil::redirect(
            html_entity_decode($this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view'))
        );
    }

    /**
     * @return void
     */
    public function initToolbar()
    {
        require_once './Services/UIComponent/Button/classes/class.ilLinkButton.php';
        $upload_wizard_button = ilLinkButton::getInstance();
        $upload_wizard_button->setCaption($this->parent_gui->lng->txt('upload_process'), false);
        $upload_wizard_button->setUrl(
            $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.uploadform')
        );
        $this->parent_gui->ilToolbar->addButtonInstance($upload_wizard_button);
    }

    /**
     * @return void
     */
    protected function processUploadFormCancellation()
    {
        if (isset($_POST['cmd']['cancel'])) {
            ilUtil::sendInfo($this->parent_gui->lng->txt('action_aborted'), true);
            ilUtil::redirect(
                html_entity_decode(
                    $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
                )
            );
        }
    }

    /**
     * @return string|void
     */
    public function startListening()
    {
        $identifier = basename($_GET['process_id']);

        require_once ilObjWorkflowEngine::getRepositoryDir() . $identifier . '.php';
        $class = substr($identifier, 4);
        /** @var ilBaseWorkflow $workflow_instance */
        $workflow_instance = new $class;

        $workflow_instance->setWorkflowClass('wfd.' . $class . '.php');
        $workflow_instance->setWorkflowLocation(ilObjWorkflowEngine::getRepositoryDir());

        $show_armer_form = true;
        switch (true) {
            case isset($_POST['process_id']):
            case isset($_POST['se_type']):
            case isset($_POST['se_content']):
            case isset($_POST['se_subject_type']):
            case isset($_POST['se_context_type']):
                $show_armer_form = false;
                break;
            default:
                $show_armer_form = true;
        }

        // Check for Event definitions
        require_once './Services/WorkflowEngine/classes/administration/class.ilWorkflowArmerGUI.php';
        $this->parent_gui->ilCtrl->saveParameter($this->parent_gui, 'process_id', $identifier);
        $action = $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.start');
        $armer = new ilWorkflowArmerGUI($action, $identifier);

        $form = $armer->getForm($workflow_instance->getInputVars(), $workflow_instance->getStartEventInfo());

        if ($show_armer_form) {
            return $form->getHTML();
        }

        $event_data = array(
            'type' => stripslashes($_POST['se_type']),
            'content' => stripslashes($_POST['se_content']),
            'subject_type' => stripslashes($_POST['se_subject_type']),
            'subject_id' => (int) $_POST['se_subject_id'],
            'context_type' => stripslashes($_POST['se_context_type']),
            'context_id' => (int) $_POST['se_context_id']
        );
        $process_id = stripslashes($_POST['process_id']);

        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowDbHelper.php';
        $event_id = ilWorkflowDbHelper::writeStartEventData($event_data, $process_id);

        foreach ($workflow_instance->getInputVars() as $input_var) {
            ilWorkflowDbHelper::writeStaticInput($input_var['name'], stripslashes($_POST[$input_var['name']]), $event_id);
        }

        ilUtil::sendSuccess($this->parent_gui->lng->txt('wfe_started_listening'), true);
        ilUtil::redirect(
            html_entity_decode(
                $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
            )
        );
    }

    public function stopListening()
    {
        $process_id = ilUtil::stripSlashes($_GET['process_id']);

        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowDbHelper.php';
        ilWorkflowDbHelper::deleteStartEventData($process_id);

        ilUtil::sendSuccess($this->parent_gui->lng->txt('wfe_stopped_listening'), true);
        ilUtil::redirect(
            html_entity_decode(
                $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
            )
        );
    }

    /**
     * @return string|void
     *
     * @throws \Exception
     */
    public function startProcess()
    {
        if (isset($_POST['cmd']['cancel'])) {
            ilUtil::sendInfo($this->parent_gui->lng->txt('action_aborted'), true);
            ilUtil::redirect(
                html_entity_decode(
                    $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
                )
            );
        }

        $identifier = basename($_GET['process_id']);

        require_once ilObjWorkflowEngine::getRepositoryDir() . $identifier . '.php';
        $class = substr($identifier, 4);
        /** @var ilBaseWorkflow $workflow_instance */
        $workflow_instance = new $class;

        $workflow_instance->setWorkflowClass('wfd.' . $class . '.php');
        $workflow_instance->setWorkflowLocation(ilObjWorkflowEngine::getRepositoryDir());

        if (count($workflow_instance->getInputVars())) {
            $show_launcher_form = false;
            foreach ($workflow_instance->getInputVars() as $input_var) {
                if (!isset($_POST[$input_var['name']])) {
                    $show_launcher_form = true;
                } else {
                    $workflow_instance->setInstanceVarById($input_var['name'], $_POST[$input_var['name']]);
                }
            }

            require_once './Services/WorkflowEngine/classes/administration/class.ilWorkflowLauncherGUI.php';
            $this->parent_gui->ilCtrl->saveParameter($this->parent_gui, 'process_id', $identifier);
            $action = $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.start');
            $launcher = new ilWorkflowLauncherGUI($action, $identifier);
            $form = $launcher->getForm($workflow_instance->getInputVars());

            if ($show_launcher_form || $form->checkInput() == false) {
                $form->setValuesByPost();
                return $form->getHTML();
            }
        }

        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowDbHelper.php';
        ilWorkflowDbHelper::writeWorkflow($workflow_instance);

        $workflow_instance->startWorkflow();
        $workflow_instance->handleEvent(
            array(
                        'time_passed',
                        'time_passed',
                        'none',
                        0,
                        'none',
                        0
                )
        );

        ilWorkflowDbHelper::writeWorkflow($workflow_instance);

        ilUtil::sendSuccess($this->parent_gui->lng->txt('process_started'), true);
        ilUtil::redirect(
            html_entity_decode(
                $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
            )
        );
    }

    /**
     *
     */
    private function ensureProcessIdInRequest()
    {
        if (!isset($this->dic->http()->request()->getQueryParams()['process_id'])) {
            ilUtil::sendInfo($this->parent_gui->lng->txt('wfe_request_missing_process_id'));
            $this->parent_gui->ilCtrl->redirect($this->parent_gui, 'definitions.view');
        }
    }

    /**
     * @return string
     */
    private function getProcessIdFromRequest()
    {
        $processId = str_replace(['\\', '/'], '', stripslashes($this->dic->http()->request()->getQueryParams()['process_id']));

        return basename($processId);
    }

    /**
     * @return void
     */
    public function deleteDefinition()
    {
        $this->ensureProcessIdInRequest();

        $processId = $this->getProcessIdFromRequest();

        $pathToProcessPhpFile = ilObjWorkflowEngine::getRepositoryDir() . '/' . $processId . '.php';
        $pathToProcessBpmn2File = ilObjWorkflowEngine::getRepositoryDir() . '/' . $processId . '.bpmn2';

        if (file_exists($pathToProcessPhpFile)) {
            unlink($pathToProcessPhpFile);
        }
        if (file_exists($pathToProcessBpmn2File)) {
            unlink($pathToProcessBpmn2File);
        }

        ilUtil::sendSuccess($this->parent_gui->lng->txt('definition_deleted'), true);
        ilUtil::redirect(
            html_entity_decode(
                $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
            )
        );
    }

    /**
     * @return string
     */
    public function confirmDeleteDefinition()
    {
        $this->ensureProcessIdInRequest();

        $processId = $this->getProcessIdFromRequest();

        require_once 'Services/WorkflowEngine/classes/administration/class.ilWorkflowDefinitionRepository.php';
        $repository = new ilWorkflowDefinitionRepository(
            $this->dic->database(),
            $this->dic->filesystem(),
            ilObjWorkflowEngine::getRepositoryDir(true)
        );
        $processDefinition = $repository->getById($processId);

        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirmation = new ilConfirmationGUI();
        $confirmation->addItem('process_id[]', $processDefinition['id'], $processDefinition['title']);
        $this->parent_gui->ilCtrl->setParameter($this->parent_gui, 'process_id', $processDefinition['id']);
        $confirmation->setFormAction($this->parent_gui->ilCtrl->getFormAction($this->parent_gui, 'definitions.view'));
        $confirmation->setHeaderText($this->parent_gui->lng->txt('wfe_sure_to_delete_process_def'));
        $confirmation->setConfirm($this->parent_gui->lng->txt('confirm'), 'definitions.delete');
        $confirmation->setCancel($this->parent_gui->lng->txt('cancel'), 'definitions.view');

        return $confirmation->getHTML();
    }
}
