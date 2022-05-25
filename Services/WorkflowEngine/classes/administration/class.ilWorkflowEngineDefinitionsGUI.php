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
 *********************************************************************/

/**
 * Class ilWorkflowEngineDefinitionsGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineDefinitionsGUI
{
    private \ILIAS\WorkflowEngine\Service $service;
    private ilObjWorkflowEngineGUI $parent_gui;
    private \ILIAS\DI\Container $dic;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct(ilObjWorkflowEngineGUI $parent_gui, \ILIAS\DI\Container $dic = null)
    {
        if ($dic === null) {
            global $DIC;
            $dic = $DIC;
        }
        $this->service = $dic->workflowEngine();
        $this->main_tpl = $dic->ui()->mainTemplate();
        $this->parent_gui = $parent_gui;
        $this->dic = $dic;
    }

    /**
     * Handle the command given.
     * @param string $command
     * @return string HTML
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public function handle(string $command) : ?string
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
                $this->deleteDefinition();
                break;

            case 'confirmdelete':
                return $this->confirmDeleteDefinition();
                break;

            case 'startlistening':
                return $this->startListening();
                break;

            case'stoplistening':
                $this->stopListening();
                break;

            case 'view':
            default:
                return $this->showDefinitionsTable();
        }
    }

    public function showDefinitionsTable() : string
    {
        if ($this->dic->rbac()->system()->checkAccess('write', $this->service->internal()->request()->getRefId())) {
            $this->initToolbar();
        }
        $table_gui = new ilWorkflowEngineDefinitionsTableGUI($this->parent_gui, 'definitions.view');
        $table_gui->setFilterCommand("definitions.applyfilter");
        $table_gui->setResetCommand("definitions.resetFilter");
        $table_gui->setDisableFilterHiding(false);

        return $table_gui->getHTML();
    }

    public function applyFilter() : string
    {
        $table_gui = new ilWorkflowEngineDefinitionsTableGUI($this->parent_gui, 'definitions.view');
        $table_gui->writeFilterToSession();
        $table_gui->resetOffset();

        return $this->showDefinitionsTable();
    }

    public function resetFilter() : string
    {
        $table_gui = new ilWorkflowEngineDefinitionsTableGUI($this->parent_gui, 'definitions.view');
        $table_gui->resetOffset();
        $table_gui->resetFilter();

        return $this->showDefinitionsTable();
    }

    public function showUploadForm() : string
    {
        $form_definition = new ilUploadDefinitionForm();
        $form = $form_definition->getForm(
            $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.upload')
        );

        return $form->getHTML();
    }

    /**
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public function handleUploadSubmit()// TODO PHP8-REVIEW Missing return type or PHPDoc comment
    {
        $this->processUploadFormCancellation();

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

        /** @var \ILIAS\FileUpload\DTO\UploadResult|null $uploadResult */
        $uploadResult = array_values($upload->getResults())[0] ?? null;
        if (!$uploadResult || !$uploadResult->isOK()) {
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
                substr($fileBaseName, -4) === '.php' &&
                stripos($fileBaseName, strtolower($wf_base_name)) === 0
            ) {
                $number = substr($fileBaseName, strlen($wf_base_name), -4);
                if ($number > $version) {
                    $version = $number;
                }
            }
        }
        $version++;

        $repo_name = $repo_base_name . '_v' . $version . '.php';

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
        if (is_file($sourceFile)) {
            rename($sourceFile, $targetFile);
        }

        $this->main_tpl->setOnScreenMessage('success', $this->parent_gui->lng->txt('upload_parse_success'), true);
        ilUtil::redirect(
            html_entity_decode($this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view'))
        );
    }

    public function initToolbar() : void
    {
        $upload_wizard_button = ilLinkButton::getInstance();
        $upload_wizard_button->setCaption($this->parent_gui->lng->txt('upload_process'), false);
        $upload_wizard_button->setUrl(
            $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.uploadform')
        );
        $this->parent_gui->ilToolbar->addButtonInstance($upload_wizard_button);
    }

    protected function processUploadFormCancellation() : void
    {
        if (isset($_POST['cmd']['cancel'])) {
            $this->main_tpl->setOnScreenMessage('info', $this->parent_gui->lng->txt('action_aborted'), true);
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
        $identifier = basename(current($this->service->internal()->request()->getProcessId()));

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
        $this->parent_gui->ilCtrl->saveParameter($this->parent_gui, 'process_id');
        $action = $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.start');
        $armer = new ilWorkflowArmerGUI($action);

        $form = $armer->getForm($workflow_instance->getInputVars(), $workflow_instance->getStartEventInfo());

        if ($show_armer_form) {
            return $form->getHTML();
        }

        $event_data = [
            'type' => stripslashes($_POST['se_type']),
            'content' => stripslashes($_POST['se_content']),
            'subject_type' => stripslashes($_POST['se_subject_type']),
            'subject_id' => (int) $_POST['se_subject_id'],
            'context_type' => stripslashes($_POST['se_context_type']),
            'context_id' => (int) $_POST['se_context_id']
        ];
        $process_id = stripslashes($_POST['process_id']);

        $event_id = ilWorkflowDbHelper::writeStartEventData($event_data, $process_id);

        foreach ($workflow_instance->getInputVars() as $input_var) {
            ilWorkflowDbHelper::writeStaticInput($input_var['name'], stripslashes($_POST[$input_var['name']]), $event_id);
        }

        $this->main_tpl->setOnScreenMessage('success', $this->parent_gui->lng->txt('wfe_started_listening'), true);
        ilUtil::redirect(
            html_entity_decode(
                $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
            )
        );
    }

    public function stopListening() : void
    {
        $process_id = ilUtil::stripSlashes(current($this->service->internal()->request()->getProcessId()));

        ilWorkflowDbHelper::deleteStartEventData($process_id);

        $this->main_tpl->setOnScreenMessage('success', $this->parent_gui->lng->txt('wfe_stopped_listening'), true);
        ilUtil::redirect(
            html_entity_decode(
                $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
            )
        );
    }

    /**
     * @return string|void
     * @throws Exception
     */
    public function startProcess()
    {
        if (isset($_POST['cmd']['cancel'])) {
            $this->main_tpl->setOnScreenMessage('info', $this->parent_gui->lng->txt('action_aborted'), true);
            ilUtil::redirect(
                html_entity_decode(
                    $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
                )
            );
        }

        $identifier = basename(current($this->service->internal()->request()->getProcessId()));

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

            $this->parent_gui->ilCtrl->saveParameter($this->parent_gui, 'process_id');
            $action = $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.start');
            $launcher = new ilWorkflowLauncherGUI($action);
            $form = $launcher->getForm($workflow_instance->getInputVars());

            if ($show_launcher_form || $form->checkInput() === false) {
                $form->setValuesByPost();
                return $form->getHTML();
            }
        }

        ilWorkflowDbHelper::writeWorkflow($workflow_instance);

        $workflow_instance->startWorkflow();
        $workflow_instance->handleEvent(
            [
                        'time_passed',
                        'time_passed',
                        'none',
                        0,
                        'none',
                        0
            ]
        );

        ilWorkflowDbHelper::writeWorkflow($workflow_instance);

        $this->main_tpl->setOnScreenMessage('success', $this->parent_gui->lng->txt('process_started'), true);
        ilUtil::redirect(
            html_entity_decode(
                $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
            )
        );
    }

    private function ensureProcessIdInRequest() : void
    {
        if (!isset($this->dic->http()->request()->getQueryParams()['process_id'])) {
            $this->main_tpl->setOnScreenMessage('info', $this->parent_gui->lng->txt('wfe_request_missing_process_id'));
            $this->parent_gui->ilCtrl->redirect($this->parent_gui, 'definitions.view');
        }
    }

    private function getProcessIdFromRequest() : string
    {
        $processId = str_replace(['\\', '/'], '', stripslashes($this->dic->http()->request()->getQueryParams()['process_id']));

        return basename($processId);
    }

    public function deleteDefinition() : void
    {
        $this->ensureProcessIdInRequest();

        $processId = $this->getProcessIdFromRequest();

        $pathToProcessPhpFile = ilObjWorkflowEngine::getRepositoryDir() . '/' . $processId . '.php';
        $pathToProcessBpmn2File = ilObjWorkflowEngine::getRepositoryDir() . '/' . $processId . '.bpmn2';

        if (is_file($pathToProcessPhpFile)) {
            unlink($pathToProcessPhpFile);
        }
        if (is_file($pathToProcessBpmn2File)) {
            unlink($pathToProcessBpmn2File);
        }

        $this->main_tpl->setOnScreenMessage('success', $this->parent_gui->lng->txt('definition_deleted'), true);
        ilUtil::redirect(
            html_entity_decode(
                $this->parent_gui->ilCtrl->getLinkTarget($this->parent_gui, 'definitions.view')
            )
        );
    }

    public function confirmDeleteDefinition() : string
    {
        $this->ensureProcessIdInRequest();

        $processId = $this->getProcessIdFromRequest();

        $repository = new ilWorkflowDefinitionRepository(
            $this->dic->database(),
            $this->dic->filesystem(),
            ilObjWorkflowEngine::getRepositoryDir(true)
        );
        $processDefinition = $repository->getById($processId);

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
