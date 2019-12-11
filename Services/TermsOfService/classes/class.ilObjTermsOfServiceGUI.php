<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject2GUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableDataProviderFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAgreementByLanguageTableGUI.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceHistoryTableGUI.php';

/**
 * @author            Michael Jansen <mjansen@databay.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjTermsOfServiceGUI: ilAdministrationGUI
 */
class ilObjTermsOfServiceGUI extends ilObject2GUI
{
    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    /**
     * @var ilLanguage
     */
    public $lng;

    /**
     * @var ilCtrl
     */
    public $ctrl;

    /**
     * @var ilObjTermsOfService
     */
    public $object;

    /**
     * @var ilTabsGUI
     */
    public $tabs_gui;

    /**
     * @var ilTermsOfServiceTableDataProviderFactory
     */
    public $factory;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;
    
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLog
     */
    protected $log;

    /**
     * @param int $a_id
     * @param int $a_id_type
     * @param int $a_parent_node_id
     */
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        /**
         * @var $lng  ilLanguage
         * @var $ilDB ilDBInterface
         */
        global $DIC;

        $this->lng        = $DIC['lng'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->error      = $DIC['ilErr'];
        $this->log        = $DIC['ilLog'];
        $this->toolbar    = $DIC['ilToolbar'];
        $this->user       = $DIC['ilUser'];

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule('tos');

        $this->factory = new ilTermsOfServiceTableDataProviderFactory();
        $this->factory->setLanguageAdapter($this->lng);
        $this->factory->setDatabaseAdapter($DIC['ilDB']);
    }

    /**
     * Functions that must be overwritten
     */
    public function getType()
    {
        return 'tos';
    }

    /**
     *
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd        = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == '' || $cmd == 'view') {
                    $cmd = 'settings';
                }
                $this->$cmd();
                break;
        }
    }

    /**
     *
     */
    public function getAdminTabs()
    {
        if ($this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTarget($this, 'settings'), array('saveSettings', 'settings', '', 'view'), '', '');
        }

        if ($this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('tos_agreement_by_lng', $this->ctrl->getLinkTarget($this, 'showAgreementByLanguage'), array('reset', 'confirmReset', 'showAgreementByLanguage', 'resetAgreementByLanguageFilter', 'applyAgreementByLanguageFilter'), '', '');
        }

        if ($this->rbacsystem->checkAccess('read', $this->object->getRefId()) &&
            $this->rbacsystem->checkAccess('read', USER_FOLDER_ID)
        ) {
            $this->tabs_gui->addTarget('tos_acceptance_history', $this->ctrl->getLinkTarget($this, 'showAcceptanceHistory'), array('showAcceptanceHistory', 'resetAcceptanceHistoryFilter', 'applyAcceptanceHistoryFilter'), '', '');
        }

        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');
        }
    }

    /**
     *
     */
    protected function initSettingsForm()
    {
        if (null == $this->form) {
            $this->form = new ilPropertyFormGUI();
            $this->form->setTitle($this->lng->txt('tos_tos_settings'));
            $this->form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));

            $status = new ilCheckboxInputGUI($this->lng->txt('tos_status_enable'), 'tos_status');
            $status->setInfo($this->lng->txt('tos_status_desc'));
            $this->form->addItem($status);

            $this->form->addCommandButton('saveSettings', $this->lng->txt('save'));
        }
    }

    /**
     *
     */
    protected function saveSettings()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $provider = $this->factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_AGRREMENT_BY_LANGUAGE);
        $list     = $provider->getList(array(), array());

        $has_documents = false;
        foreach ($list['items'] as $item) {
            if ($item['agreement_document']) {
                $has_documents = true;
                break;
            }
        }

        $this->initSettingsForm();
        if ($this->form->checkInput()) {
            if ($has_documents || !(int) $this->form->getInput('tos_status')) {
                $this->object->saveStatus((int) $this->form->getInput('tos_status'));
                ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
            }
        }
        
        if (
            !$has_documents &&
            (int) $this->form->getInput('tos_status') &&
            !$this->object->getStatus()
        ) {
            $_POST['tos_status'] = 0;
            ilUtil::sendFailure($this->lng->txt('tos_no_documents_exist_cant_save'));
        }

        $this->settings(false);
    }

    /**
     * @param bool $init_from_database
     */
    protected function settings($init_from_database = true)
    {
        if (!$this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->showMissingDocuments();

        $this->initSettingsForm();
        if ($init_from_database) {
            $this->form->setValuesByArray(array(
                'tos_status' => $this->object->getStatus()
            ));
        } else {
            $this->form->setValuesByPost();
        }

        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     *
     */
    protected function confirmReset()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'confirmReset'));
        $confirmation->setConfirm($this->lng->txt('confirm'), 'reset');
        $confirmation->setCancel($this->lng->txt('cancel'), 'showAgreementByLanguage');
        $confirmation->setHeaderText($this->lng->txt('tos_sure_reset_tos'));

        $this->tpl->setContent($confirmation->getHTML());
    }

    /**
     *
     */
    protected function reset()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->object->resetAll();
        $this->log->write(__METHOD__ . ': Terms of service reset by ' . $this->user->getId() . ' [' . $this->user->getLogin() . ']');
        ilUtil::sendSuccess($this->lng->txt('tos_reset_successfull'));

        $this->showAgreementByLanguage();
    }

    /**
     *
     */
    protected function showAgreementByLanguage()
    {
        if (!$this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->lng->loadLanguageModule('meta');

        if ($this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'settings'));
            $this->toolbar->addFormButton($this->lng->txt('tos_reset_tos_for_all_users'), 'confirmReset');
        }

        $this->showLastResetDate();

        $table = new ilTermsOfServiceAgreementByLanguageTableGUI($this, 'showAgreementByLanguage');
        $table->setProvider($this->factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_AGRREMENT_BY_LANGUAGE));
        $table->populate();

        $this->tpl->setContent($table->getHTML());
    }

    /**
     *
     */
    protected function showLastResetDate()
    {
        if ($this->object->getLastResetDate() && $this->object->getLastResetDate()->get(IL_CAL_UNIX) != 0) {
            $status = ilDatePresentation::useRelativeDates();
            ilDatePresentation::setUseRelativeDates(false);
            $this->toolbar->addText(sprintf($this->lng->txt('tos_last_reset_date'), ilDatePresentation::formatDate($this->object->getLastResetDate())));
            ilDatePresentation::setUseRelativeDates($status);
        }
    }

    /**
     *
     */
    protected function showMissingDocuments()
    {
        if (!$this->object->getStatus()) {
            return;
        }

        $provider = $this->factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_AGRREMENT_BY_LANGUAGE);
        $list     = $provider->getList(array(), array());
        
        $has_documents = false;
        foreach ($list['items'] as $item) {
            if ($item['agreement_document']) {
                $has_documents = true;
                break;
            }
        }

        if (!$has_documents) {
            ilUtil::sendInfo($this->lng->txt('tos_no_documents_exist'));
        }
    }

    /**
     *
     */
    protected function applyAgreementByLanguageFilter()
    {
        $table = new ilTermsOfServiceAgreementByLanguageTableGUI($this, 'showAgreementByLanguage');
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showAgreementByLanguage();
    }

    /**
     *
     */
    protected function resetAgreementByLanguageFilter()
    {
        $table = new ilTermsOfServiceAgreementByLanguageTableGUI($this, 'showAgreementByLanguage');
        $table->resetOffset();
        $table->resetFilter();

        $this->showAgreementByLanguage();
    }

    /**
     *
     */
    protected function getAgreementTextByFilenameAsynch()
    {
        require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceJsonResponse.php';
        $response = new ilTermsOfServiceJsonResponse();

        if (
            !isset($_GET['agreement_document']) ||
            !strlen($_GET['agreement_document']) ||
            !$this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $response->setStatus(ilTermsOfServiceJsonResponse::STATUS_FAILURE);
            echo $response;
        }

        $file = realpath(strip_tags(rawurldecode(ilUtil::stripOnlySlashes($_GET['agreement_document']))));
        if (preg_match('/Customizing[\/\\\](global[\/\\\]agreement|clients[\/\\\]' . CLIENT_ID . '[\/\\\]agreement)[\/\\\]agreement_([a-z]{2})\.html$/', $file)) {
            $content = file_get_contents($file);
            if (strip_tags($content) === $content) {
                $content       = '';
                $lines         = file($file);
                foreach ($lines as $line) {
                    $content .= nl2br(trim($line));
                }
            }
            $response->setBody($content);
        } else {
            $response->setStatus(ilTermsOfServiceJsonResponse::STATUS_FAILURE);
        }

        echo $response;
    }

    /**
     *
     */
    protected function showAcceptanceHistory()
    {
        if (
            !$this->rbacsystem->checkAccess('read', '', $this->object->getRefId()) ||
            !$this->rbacsystem->checkAccess('read', '', USER_FOLDER_ID)
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->lng->loadLanguageModule('meta');

        $table = new ilTermsOfServiceAcceptanceHistoryTableGUI($this, 'showAcceptanceHistory');
        $table->setProvider($this->factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY));
        $table->populate();

        $this->tpl->setContent($table->getHTML());
    }

    /**
     *
     */
    protected function getAcceptedContentAsynch()
    {
        require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceJsonResponse.php';
        $response = new ilTermsOfServiceJsonResponse();

        if (!isset($_GET['tosv_id'])) {
            $response->setStatus(ilTermsOfServiceJsonResponse::STATUS_FAILURE);
            echo $response;
        }

        $entity = ilTermsOfServiceHelper::getById(ilUtil::stripSlashes($_GET['tosv_id']));
        $response->setBody($entity->getText());

        echo $response;
    }

    /**
     * Show auto complete results
     */
    protected function addUserAutoComplete()
    {
        if (
            !$this->rbacsystem->checkAccess('read', '', $this->object->getRefId()) ||
            !$this->rbacsystem->checkAccess('read', '', USER_FOLDER_ID)
        ) {
            echo json_encode(array());
            exit();
        }
        
        include_once 'Services/User/classes/class.ilUserAutoComplete.php';
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(array('login', 'firstname', 'lastname', 'email'));
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        echo $auto->getList($_REQUEST['term']);
        exit();
    }

    /**
     *
     */
    protected function applyAcceptanceHistoryFilter()
    {
        $table = new ilTermsOfServiceAcceptanceHistoryTableGUI($this, 'showAcceptanceHistory');
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showAcceptanceHistory();
    }

    /**
     *
     */
    protected function resetAcceptanceHistoryFilter()
    {
        $table = new ilTermsOfServiceAcceptanceHistoryTableGUI($this, 'showAcceptanceHistory');
        $table->resetOffset();
        $table->resetFilter();

        $this->showAcceptanceHistory();
    }
}
