<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author            Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilTermsOfServiceDocumentGUI
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilTermsOfServiceAcceptanceHistoryGUI
 * @ilCtrl_isCalledBy ilObjTermsOfServiceGUI: ilAdministrationGUI
 */
class ilObjTermsOfServiceGUI extends \ilObject2GUI
{
    /** @var ILIAS\DI\Container */
    protected $dic;

    /** @var \ilRbacSystem */
    protected $rbacsystem;

    /** @var \ilErrorHandling */
    protected $error;

    /**
     * @inheritdoc
     */
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->lng = $DIC['lng'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->error = $DIC['ilErr'];

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule('tos');
        $this->lng->loadLanguageModule('meta');
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'tos';
    }

    /**
     * @inheritdoc
     */
    public function executeCommand()
    {
        $this->prepareOutput();

        $nextClass = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $tableDataProviderFactory = new \ilTermsOfServiceTableDataProviderFactory();
        $tableDataProviderFactory->setDatabaseAdapter($this->dic->database());

        switch (strtolower($nextClass)) {
            case 'iltermsofservicedocumentgui':
                $documentGui = new \ilTermsOfServiceDocumentGUI(
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
                    new \ilTermsOfServiceTrimmedDocumentPurifier(new \ilTermsOfServiceDocumentHtmlPurifier())
                );
                $this->ctrl->forwardCommand($documentGui);
                break;

            case 'iltermsofserviceacceptancehistorygui':
                $documentGui = new \ilTermsOfServiceAcceptanceHistoryGUI(
                    $this->object,
                    $this->dic['tos.criteria.type.factory'],
                    $this->dic->ui()->mainTemplate(),
                    $this->dic->ctrl(),
                    $this->dic->language(),
                    $this->dic->rbac()->system(),
                    $this->dic['ilErr'],
                    $this->dic->http()->request(),
                    $this->dic->ui()->factory(),
                    $this->dic->ui()->renderer(),
                    $tableDataProviderFactory
                );
                $this->ctrl->forwardCommand($documentGui);
                break;

            case 'ilpermissiongui':
                $perm_gui = new \ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == '' || $cmd == 'view' || !method_exists($this, $cmd)) {
                    $cmd = 'settings';
                }
                $this->$cmd();
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function getAdminTabs()
    {
        if ($this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'settings'),
                '',
                [strtolower(get_class($this))]
            );
        }

        if ($this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'tos_agreement_documents_tab_label',
                $this->ctrl->getLinkTargetByClass('ilTermsOfServiceDocumentGUI'),
                '',
                ['iltermsofservicedocumentgui']
            );
        }

        if ($this->rbacsystem->checkAccess('read', $this->object->getRefId()) &&
            $this->rbacsystem->checkAccess('read', USER_FOLDER_ID)
        ) {
            $this->tabs_gui->addTarget(
                'tos_acceptance_history',
                $this->ctrl->getLinkTargetByClass('ilTermsOfServiceAcceptanceHistoryGUI'),
                '',
                ['iltermsofserviceacceptancehistorygui']
            );
        }

        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass([get_class($this), 'ilpermissiongui'], 'perm'),
                '',
                ['ilpermissiongui', 'ilobjectpermissionstatusgui']
            );
        }
    }

    /**
     * @return \ilTermsOfServiceSettingsFormGUI
     */
    protected function getSettingsForm() : \ilTermsOfServiceSettingsFormGUI
    {
        $form = new \ilTermsOfServiceSettingsFormGUI(
            $this->object,
            $this->ctrl->getFormAction($this, 'saveSettings'),
            'saveSettings',
            $this->rbacsystem->checkAccess('write', $this->object->getRefId())
        );

        return $form;
    }

    /**
     *
     */
    protected function saveSettings()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getSettingsForm();
        if ($form->saveObject()) {
            \ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, 'settings');
        } else {
            if ($form->hasTranslatedError()) {
                \ilUtil::sendFailure($form->getTranslatedError());
            }
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function showMissingDocuments()
    {
        if (!$this->object->getStatus()) {
            return;
        }

        if (0 === \ilTermsOfServiceDocument::where([])->count()) {
            \ilUtil::sendInfo($this->lng->txt('tos_no_documents_exist'));
        }
    }

    /**
     *
     */
    protected function settings()
    {
        if (!$this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->showMissingDocuments();

        $form = $this->getSettingsForm();
        $this->tpl->setContent($form->getHTML());
    }
}
