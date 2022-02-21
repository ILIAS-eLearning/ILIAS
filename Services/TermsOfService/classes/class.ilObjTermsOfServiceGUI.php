<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author            Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilTermsOfServiceDocumentGUI
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilTermsOfServiceAcceptanceHistoryGUI
 * @ilCtrl_isCalledBy ilObjTermsOfServiceGUI: ilAdministrationGUI
 */
class ilObjTermsOfServiceGUI extends ilObject2GUI implements ilTermsOfServiceControllerEnabled
{
    protected ILIAS\DI\Container $dic;
    protected ilErrorHandling $error;

    /**
     * @inheritdoc
     */
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC['ilErr'];

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule('tos');
        $this->lng->loadLanguageModule('meta');
    }

    public function getType() : string
    {
        return 'tos';
    }

    public function executeCommand() : void
    {
        $this->prepareOutput();

        $nextClass = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $tableDataProviderFactory = new ilTermsOfServiceTableDataProviderFactory();
        $tableDataProviderFactory->setDatabaseAdapter($this->dic->database());

        switch (strtolower($nextClass)) {
            case strtolower(ilTermsOfServiceDocumentGUI::class):
                $documentGui = new ilTermsOfServiceDocumentGUI(
                    $this->object,
                    $this->dic['tos.criteria.type.factory'],
                    $this->dic->ui()->mainTemplate(),
                    $this->dic->user(),
                    $this->dic->ctrl(),
                    $this->dic->language(),
                    $this->dic->rbac()->system(),
                    $this->dic['ilErr'],
                    $this->dic->logger()->tos(),
                    $this->dic->toolbar(),
                    $this->dic->http(),
                    $this->dic->ui()->factory(),
                    $this->dic->ui()->renderer(),
                    $this->dic->filesystem(),
                    $this->dic->upload(),
                    $tableDataProviderFactory,
                    new ilTermsOfServiceTrimmedDocumentPurifier(new ilTermsOfServiceDocumentHtmlPurifier()),
                    $this->dic->refinery()
                );
                $this->ctrl->forwardCommand($documentGui);
                break;

            case strtolower(ilTermsOfServiceAcceptanceHistoryGUI::class):
                $documentGui = new ilTermsOfServiceAcceptanceHistoryGUI(
                    $this->object,
                    $this->dic['tos.criteria.type.factory'],
                    $this->dic->ui()->mainTemplate(),
                    $this->dic->ctrl(),
                    $this->dic->language(),
                    $this->dic->rbac()->system(),
                    $this->dic['ilErr'],
                    $this->dic->http(),
                    $this->dic->refinery(),
                    $this->dic->ui()->factory(),
                    $this->dic->ui()->renderer(),
                    $tableDataProviderFactory,
                );
                $this->ctrl->forwardCommand($documentGui);
                break;

            case strtolower(ilPermissionGUI::class):
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd === '' || $cmd === 'view' || !method_exists($this, $cmd)) {
                    $cmd = 'settings';
                }
                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs() : void
    {
        if ($this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'tos_agreement_documents_tab_label',
                $this->ctrl->getLinkTargetByClass(ilTermsOfServiceDocumentGUI::class),
                '',
                [strtolower(ilTermsOfServiceDocumentGUI::class)]
            );
        }

        if ($this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'settings'),
                '',
                [strtolower(self::class)]
            );
        }

        if (
            (defined('USER_FOLDER_ID') && $this->rbacsystem->checkAccess('read', USER_FOLDER_ID)) &&
            $this->rbacsystem->checkAccess('read', $this->object->getRefId())
        ) {
            $this->tabs_gui->addTarget(
                'tos_acceptance_history',
                $this->ctrl->getLinkTargetByClass(ilTermsOfServiceAcceptanceHistoryGUI::class),
                '',
                [strtolower(ilTermsOfServiceAcceptanceHistoryGUI::class)]
            );
        }

        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], 'perm'),
                '',
                [strtolower(ilPermissionGUI::class), strtolower(ilObjectPermissionStatusGUI::class)]
            );
        }
    }

    protected function getSettingsForm() : ilTermsOfServiceSettingsFormGUI
    {
        $form = new ilTermsOfServiceSettingsFormGUI(
            $this->object,
            $this->ctrl->getFormAction($this, 'saveSettings'),
            'saveSettings',
            $this->rbacsystem->checkAccess('write', $this->object->getRefId())
        );

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_TOS,
            $form,
            $this
        );

        return $form;
    }

    protected function saveSettings() : void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getSettingsForm();
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, 'settings');
        } elseif ($form->hasTranslatedError()) {
            $this->tpl->setOnScreenMessage('failure', $form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function showMissingDocuments() : void
    {
        if ($this->object->getStatus()) {
            return;
        }

        if (0 === ilTermsOfServiceDocument::where([])->count()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tos_no_documents_exist'));
        }
    }

    protected function settings() : void
    {
        if (!$this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->showMissingDocuments();

        $form = $this->getSettingsForm();
        $this->tpl->setContent($form->getHTML());
    }
}
