<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ilCtrl_Calls ilECSSettingsGUI: ilECSMappingSettingsGUI, ilECSParticipantSettingsGUI
 */
class ilECSSettingsGUI
{
    public const MAPPING_EXPORT = 1;
    public const MAPPING_IMPORT = 2;

    private ilLogger $log;
    private ilGlobalTemplateInterface $tpl;
    private ilLanguage $lng;
    private ilCtrl $ctrl;
    private ilTabsGUI $tabs_gui;
    private ilRbacReview $rbacreview;
    private ilTree $tree;
    private ilAccessHandler $access;
    private ilToolbarGUI $toolbar;
    private ilObjectDataCache $objDataCache;
    private ilObjUser $user;
    private \ILIAS\HTTP\Services $http;
    private ilECSSetting $settings;
    
    private ?ilPropertyFormGUI $form = null;
    private ilECSCategoryMappingRule $rule;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tabs_gui = $DIC->tabs();
        $this->rbacreview = $DIC->rbac()->review();
        $this->log = $DIC->logger()->wsrv();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->objDataCache = $DIC['ilObjDataCache'];
        $this->user = $DIC->user();
        $this->http = $DIC->http();

        $this->lng->loadLanguageModule('ecs');
        $this->initSettings();
    }
    
    /**
     * Execute command
     */
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        $this->setSubTabs();
        switch ($next_class) {
            case 'ilecsmappingsettingsgui':
                $mapset = new ilECSMappingSettingsGUI($this, (int) $_REQUEST['server_id'], (int) $_REQUEST['mid']);
                $this->ctrl->setReturn($this, 'communities');
                $this->ctrl->forwardCommand($mapset);
                break;
            
            case 'ilecsparticipantsettingsgui':
                $part = new ilECSParticipantSettingsGUI(
                    (int) $_REQUEST['server_id'],
                    (int) $_REQUEST['mid']
                );
                $this->ctrl->setReturn($this, 'communities');
                $this->ctrl->forwardCommand($part);
                break;
            
            default:

                if ($cmd !== "overview" && $cmd !== "communities" && !$this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
                    $this->ctrl->redirect($this, "overview");
                }

                if (!$cmd || $cmd === 'view') {
                    $cmd = "overview";
                }
                $this->$cmd();
                break;
        }
    }

    /**
     * List available servers
     */
    public function overview() : void
    {
        $this->tabs_gui->setSubTabActive('overview');
        if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
            $this->toolbar->addButton(
                $this->lng->txt('ecs_add_new_ecs'),
                $this->ctrl->getLinkTarget($this, 'create')
            );
        }

        $servers = ilECSServerSettings::getInstance();

        $table = new ilECSServerTableGUI($this, 'overview');
        $table->initTable();
        $table->parse($servers);
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * activate server
     */
    protected function activate() : void
    {
        $this->initSettings((int) $_REQUEST['server_id']);
        $this->settings->setEnabledStatus(true);
        $this->settings->update();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'overview');
    }
    
    /**
     * activate server
     */
    protected function deactivate() : void
    {
        $this->initSettings((int) $_REQUEST['server_id']);
        $this->settings->setEnabledStatus(false);
        $this->settings->update();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'overview');
    }
    
    /**
     * Read all importable econtent
     */
    protected function readAll() : bool
    {
        try {
            //TOOD fix somehow broken logic code of this foreach loop
            foreach (ilECSServerSettings::getInstance()->getServers(ilECSServerSettings::ACTIVE_SERVER) as $server) {
                (new ilECSEventQueueReader($server))->handleImportReset();
                (new ilECSEventQueueReader($server))->handleExportReset();

                ilECSTaskScheduler::_getInstanceByServerId($server->getServerId())->startTaskExecution();

                $this->tpl->setOnScreenMessage('info', $this->lng->txt('ecs_remote_imported'));
                $this->imported();
                return true;
            }
        } catch (ilECSConnectorException $e1) {
            $this->tpl->setOnScreenMessage('info', 'Cannot connect to ECS server: ' . $e1->getMessage());
            $this->imported();
        } catch (ilException $e2) {
            $this->tpl->setOnScreenMessage('info', 'Update failed: ' . $e2->getMessage());
            $this->imported();
        }
        return false;
    }

    /**
     * Create new settings
     */
    protected function create() : void
    {
        $this->initSettings(0);

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->clearSubTabs();
        $this->tabs_gui->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'overview'));

        $this->initSettingsForm('create');
        $this->tabs_gui->setSubTabActive('ecs_settings');

        $this->tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Edit server setting
     */
    protected function edit() : void
    {
        $this->initSettings((int) $_REQUEST['server_id']);
        $this->ctrl->saveParameter($this, 'server_id');

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->clearSubTabs();
        $this->tabs_gui->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'overview'));

        $this->initSettingsForm();
        $this->tabs_gui->setSubTabActive('ecs_settings');

        $this->tpl->setContent($this->form->getHTML());
    }

    protected function cp() : void
    {
        $this->initSettings((int) $_REQUEST['server_id']);

        $copy = clone $this->settings;
        $copy->save();

        $this->ctrl->setParameter($this, 'server_id', $copy->getServerId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('ecs_settings_cloned'), true);
        $this->ctrl->redirect($this, 'edit');
    }

    /**
     * Delete confirmation
     */
    protected function delete() : void
    {
        $this->initSettings((int) $_REQUEST['server_id']);

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->clearSubTabs();
        $this->tabs_gui->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'overview'));

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->lng->txt('delete'), 'doDelete');
        $confirm->setCancel($this->lng->txt('cancel'), 'overview');
        $confirm->setHeaderText($this->lng->txt('ecs_delete_setting'));

        $confirm->addItem('', '', $this->settings->getServer());
        $confirm->addHiddenItem('server_id', (string) $this->settings->getServerId());
        
        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * Do delete
     */
    protected function doDelete() : void
    {
        $this->initSettings($_REQUEST['server_id']);
        $this->settings->delete();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('ecs_setting_deleted'), true);
        $this->ctrl->redirect($this, 'overview');
    }


    /**
     * show settings
     */
    protected function settings() : void
    {
        $this->initSettingsForm();
        $this->tabs_gui->setSubTabActive('ecs_settings');
        
        $this->tpl->setContent($this->form->getHTML());
    }
    
    /**
     * init settings form
     */
    protected function initSettingsForm($a_mode = 'update') : void
    {
        if (isset($this->form) && is_object($this->form)) {
            return;
        }
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'settings'));
        $this->form->setTitle($this->lng->txt('ecs_connection_settings'));
        
        $ena = new ilCheckboxInputGUI($this->lng->txt('ecs_active'), 'active');
        $ena->setChecked($this->settings->isEnabled());
        $ena->setValue("1");
        $this->form->addItem($ena);
        
        $server_title = new ilTextInputGUI($this->lng->txt('ecs_server_title'), 'title');
        $server_title->setValue($this->settings->getTitle());
        $server_title->setSize(80);
        $server_title->setMaxLength(128);
        $server_title->setRequired(true);
        $this->form->addItem($server_title);

        $ser = new ilTextInputGUI($this->lng->txt('ecs_server_url'), 'server');
        $ser->setValue((string) $this->settings->getServer());
        $ser->setRequired(true);
        $this->form->addItem($ser);
        
        $pro = new ilSelectInputGUI($this->lng->txt('ecs_protocol'), 'protocol');
        // fixed to https
        #$pro->setOptions(array(ilECSSetting::PROTOCOL_HTTP => $this->lng->txt('http'),
        #		ilECSSetting::PROTOCOL_HTTPS => $this->lng->txt('https')));
        $pro->setOptions(array(ilECSSetting::PROTOCOL_HTTPS => 'HTTPS'));
        $pro->setValue($this->settings->getProtocol());
        $pro->setRequired(true);
        $this->form->addItem($pro);
        
        $por = new ilTextInputGUI($this->lng->txt('ecs_port'), 'port');
        $por->setSize(5);
        $por->setMaxLength(5);
        $por->setValue((string) $this->settings->getPort());
        $por->setRequired(true);
        $this->form->addItem($por);

        $tcer = new ilRadioGroupInputGUI($this->lng->txt('ecs_auth_type'), 'auth_type');
        $tcer->setValue((string) $this->settings->getAuthType());
        $this->form->addItem($tcer);

        // Certificate based authentication
        $cert_based = new ilRadioOption($this->lng->txt('ecs_auth_type_cert'), (string) ilECSSetting::AUTH_CERTIFICATE);
        $tcer->addOption($cert_based);

        $cli = new ilTextInputGUI($this->lng->txt('ecs_client_cert'), 'client_cert');
        $cli->setSize(60);
        $cli->setValue((string) $this->settings->getClientCertPath());
        $cli->setRequired(true);
        $cert_based->addSubItem($cli);
        
        $key = new ilTextInputGUI($this->lng->txt('ecs_cert_key'), 'key_path');
        $key->setSize(60);
        $key->setValue((string) $this->settings->getKeyPath());
        $key->setRequired(true);
        $cert_based->addSubItem($key);
        
        $cerp = new ilTextInputGUI($this->lng->txt('ecs_key_password'), 'key_password');
        $cerp->setSize(12);
        $cerp->setValue((string) $this->settings->getKeyPassword());
        $cerp->setInputType('password');
        $cerp->setRequired(true);
        $cert_based->addSubItem($cerp);

        $cer = new ilTextInputGUI($this->lng->txt('ecs_ca_cert'), 'ca_cert');
        $cer->setSize(60);
        $cer->setValue((string) $this->settings->getCACertPath());
        $cer->setRequired(true);
        $cert_based->addSubItem($cer);

        // Apache auth
        $apa_based = new ilRadioOption($this->lng->txt('ecs_auth_type_apache'), (string) ilECSSetting::AUTH_APACHE);
        $tcer->addOption($apa_based);

        $user = new ilTextInputGUI($this->lng->txt('ecs_apache_user'), 'auth_user');
        $user->setSize(32);
        $user->setValue($this->settings->getAuthUser());
        $user->setRequired(true);
        $apa_based->addSubItem($user);

        $pass = new ilPasswordInputGUI($this->lng->txt('ecs_apache_pass'), 'auth_pass');
        $pass->setRetype(false);
        $pass->setSize(32);
        $pass->setMaxLength(128);
        $pass->setValue($this->settings->getAuthPass());
        $pass->setRequired(true);
        $pass->setSkipSyntaxCheck(true);
        $apa_based->addSubItem($pass);


        $ser = new ilNonEditableValueGUI($this->lng->txt('cert_serial'));
        $ser->setValue($this->settings->getCertSerialNumber() ?: $this->lng->txt('ecs_no_value'));
        $cert_based->addSubItem($ser);

        $loc = new ilFormSectionHeaderGUI();
        $loc->setTitle($this->lng->txt('ecs_local_settings'));
        $this->form->addItem($loc);
        
        $imp = new ilCustomInputGUI($this->lng->txt('ecs_import_id'));
        $imp->setRequired(true);
        
        $tpl = new ilTemplate('tpl.ecs_import_id_form.html', true, true, 'Services/WebServices/ECS');
        $tpl->setVariable('SIZE', 5);
        $tpl->setVariable('MAXLENGTH', 11);
        $tpl->setVariable('POST_VAR', 'import_id');
        $tpl->setVariable('PROPERTY_VALUE', $this->settings->getImportId());
        
        if ($this->settings->getImportId()) {
            $path = $this->buildPath($this->settings->getImportId());
            if ($path === '') {
                $imp->setAlert($this->lng->txt('err_check_input'));
            } else {
                $tpl->setVariable('COMPLETE_PATH', $this->buildPath($this->settings->getImportId()));
            }
        }
        
        $imp->setHTML($tpl->get());
        $imp->setInfo($this->lng->txt('ecs_import_id_info'));
        $this->form->addItem($imp);
        
        $loc = new ilFormSectionHeaderGUI();
        $loc->setTitle($this->lng->txt('ecs_remote_user_settings'));
        $this->form->addItem($loc);
        
        $role = new ilSelectInputGUI($this->lng->txt('ecs_role'), 'global_role');
        $role->setOptions($this->prepareRoleSelect());
        $role->setValue($this->settings->getGlobalRole());
        $role->setInfo($this->lng->txt('ecs_global_role_info'));
        $role->setRequired(true);
        $this->form->addItem($role);
        
        $duration = new ilDurationInputGUI($this->lng->txt('ecs_account_duration'), 'duration');
        $duration->setInfo($this->lng->txt('ecs_account_duration_info'));
        $duration->setMonths($this->settings->getDuration());
        $duration->setShowSeconds(false);
        $duration->setShowMinutes(false);
        $duration->setShowHours(false);
        $duration->setShowDays(false);
        $duration->setShowMonths(true);
        $duration->setRequired(true);
        $this->form->addItem($duration);
        
        // Email recipients
        $loc = new ilFormSectionHeaderGUI();
        $loc->setTitle($this->lng->txt('ecs_notifications'));
        $this->form->addItem($loc);
        
        $rcp_user = new ilTextInputGUI($this->lng->txt('ecs_user_rcp'), 'user_recipients');
        $rcp_user->setValue($this->settings->getUserRecipientsAsString());
        $rcp_user->setInfo($this->lng->txt('ecs_user_rcp_info'));
        $this->form->addItem($rcp_user);

        $rcp_econ = new ilTextInputGUI($this->lng->txt('ecs_econ_rcp'), 'econtent_recipients');
        $rcp_econ->setValue($this->settings->getEContentRecipientsAsString());
        $rcp_econ->setInfo($this->lng->txt('ecs_econ_rcp_info'));
        $this->form->addItem($rcp_econ);

        $rcp_app = new ilTextInputGUI($this->lng->txt('ecs_approval_rcp'), 'approval_recipients');
        $rcp_app->setValue($this->settings->getApprovalRecipientsAsString());
        $rcp_app->setInfo($this->lng->txt('ecs_approval_rcp_info'));
        $this->form->addItem($rcp_app);

        if ($a_mode === 'update') {
            $this->form->addCommandButton('update', $this->lng->txt('save'));
        } else {
            $this->form->addCommandButton('save', $this->lng->txt('save'));
        }
        $this->form->addCommandButton('overview', $this->lng->txt('cancel'));
    }
    
    /**
     * save settings
     */
    protected function update() : void
    {
        $this->initSettings((int) $_REQUEST['server_id']);
        $this->loadFromPost();
        
        if (!$error = $this->settings->validate()) {
            $this->settings->update();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'), true);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt($error));
            $this->edit();
        }
        
        $this->overview();
    }

    /**
     * Save settings
     */
    protected function save() : void
    {
        $this->initSettings(0);
        $this->loadFromPost();

        if (!$error = $this->settings->validate()) {
            $this->settings->save();

            #$this->updateTitle();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'), true);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt($error));
        }
        $this->ctrl->redirect($this, 'overview');
    }

    /**
     * Update configuration title
     */
    protected function updateTitle() : void
    {
        try {
            $reader = ilECSCommunityReader::getInstanceByServerId($this->settings->getServerId());

            foreach ($reader->getCommunities() as $community) {
                foreach ($community->getParticipants() as $part) {
                    $this->log->dump($community);
                    if ($part->isSelf()) {
                        $this->settings->setTitle($part->getParticipantName());
                        $this->settings->update();
                        return;
                    }
                }
            }
        } catch (ilECSConnectorException $exc) {
            $this->tpl->setOnScreenMessage('failure', $exc->getMessage());
        }
        $this->settings->setTitle('');
        $this->settings->update();
    }

    /**
     * Load from post
     */
    protected function loadFromPost() : void
    {
        $this->settings->setEnabledStatus((bool) $_POST['active']);
        $this->settings->setTitle(ilUtil::stripSlashes($_POST['title']));
        $this->settings->setServer(ilUtil::stripSlashes($_POST['server']));
        $this->settings->setPort(ilUtil::stripSlashes($_POST['port']));
        $this->settings->setProtocol(ilUtil::stripSlashes($_POST['protocol']));
        $this->settings->setClientCertPath(ilUtil::stripSlashes($_POST['client_cert']));
        $this->settings->setCACertPath(ilUtil::stripSlashes($_POST['ca_cert']));
        $this->settings->setKeyPath(ilUtil::stripSlashes($_POST['key_path']));
        $this->settings->setKeyPassword(ilUtil::stripSlashes($_POST['key_password']));
        $this->settings->setImportId((int) ilUtil::stripSlashes($_POST['import_id']));
        $this->settings->setServer(ilUtil::stripSlashes($_POST['server']));
        $this->settings->setGlobalRole((int) $_POST['global_role']);
        $this->settings->setDuration((int) $_POST['duration']['MM']);
        $this->settings->setUserRecipients(explode(',', ilUtil::stripSlashes($_POST['user_recipients'])));
        $this->settings->setEContentRecipients(explode(',', ilUtil::stripSlashes($_POST['econtent_recipients'])));
        $this->settings->setApprovalRecipients(explode(',', ilUtil::stripSlashes($_POST['approval_recipients'])));

        $this->settings->setAuthType((int) $_POST['auth_type']);
        $this->settings->setAuthPass(ilUtil::stripSlashes($_POST['auth_pass']));
        $this->settings->setAuthUser(ilUtil::stripSlashes($_POST['auth_user']));
    }
    
    /**
     * Refresh participants
     */
    protected function refreshParticipants() : void
    {
        $servers = ilECSServerSettings::getInstance();
        foreach ($servers->getServers(ilECSServerSettings::ALL_SERVER) as $server) {

            // read community
            try {
                $creader = ilECSCommunityReader::getInstanceByServerId($server->getServerId());
                foreach (ilECSParticipantSettings::getInstanceByServerId($server->getServerId())->getAvailabeMids() as $mid) {
                    if (!$creader->getParticipantByMID($mid)) {
                        $this->log->notice('Deleting deprecated participant: ' . $server->getServerId() . ' ' . $mid);
                        $part = new ilECSParticipantSetting($server->getServerId(), $mid);
                        $part->delete();
                    }
                }
            } catch (ilECSConnectorException $e) {
                $this->tpl->setOnScreenMessage('failure', $server->getServer() . ': ' . $e->getMessage(), true);
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'communities');
    }
    
    /**
     * show communities
     */
    public function communities() : void
    {
        $tpl = new \ilTemplate(
            'tpl.ecs_communities.html',
            true,
            true,
            'Services/WebServices/ECS'
        );

        // add toolbar to refresh communities
        if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
            $this->toolbar->addButton(
                $this->lng->txt('ecs_refresh_participants'),
                $this->ctrl->getLinkTarget($this, 'refreshParticipants')
            );
        }

        
        $this->tabs_gui->setSubTabActive('ecs_communities');

        $tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'updateCommunities'));

        if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
            $tpl->setCurrentBlock("submit_buttons");
            $tpl->setVariable('TXT_SAVE', $this->lng->txt('save'));
            $tpl->setVariable('TXT_CANCEL', $this->lng->txt('cancel'));
            $tpl->parseCurrentBlock();
        }

        $settings = ilECSServerSettings::getInstance();
            
        foreach ($settings->getServers(ilECSServerSettings::ALL_SERVER) as $server) {
            // Try to read communities
            try {
                $reader = ilECSCommunityReader::getInstanceByServerId($server->getServerId());
                foreach ($reader->getCommunities() as $community) {
                    $tpl->setCurrentBlock('table_community');
                    $table_gui = new ilECSCommunityTableGUI($server, $this, 'communities', $community->getId());
                    $table_gui->setTitle($community->getTitle() . ' (' . $community->getDescription() . ')');
                    $table_gui->parse($community->getParticipants());
                    $tpl->setVariable('TABLE_COMM', $table_gui->getHTML());
                    $tpl->parseCurrentBlock();
                }
            } catch (ilECSConnectorException $exc) {
                // Maybe server is not fully configured
                continue;
            }

            // Show section for each server
            $tpl->setCurrentBlock('server');
            $tpl->setVariable('TXT_SERVER_NAME', $server->getTitle());
            $tpl->parseCurrentBlock();
        }

        $this->tpl->setContent($tpl->get());
    }

    /**
     * Validate import types
     * @param array $import_types
     */
    protected function validateImportTypes(array $import_types) : array
    {
        $num_cms = 0;
        foreach ((array) $import_types as $sid => $server) {
            foreach ((array) $server as $mid => $import_type) {
                if ((int) $import_type === ilECSParticipantSetting::IMPORT_CMS) {
                    ++$num_cms;
                }
            }
        }
        
        if ($num_cms <= 1) {
            return [];
        }
        // Change to import type "UNCHANGED"
        $new_types = [];
        foreach ((array) $import_types as $sid => $server) {
            foreach ((array) $server as $mid => $import_type) {
                if ((int) $import_type === ilECSParticipantSetting::IMPORT_CMS) {
                    $new_types[$sid][$mid] = ilECSParticipantSetting::IMPORT_UNCHANGED;
                } else {
                    $new_types[$sid][$mid] = $import_type;
                }
            }
        }
        return $new_types;
    }
    
    /**
     * update whitelist
     */
    protected function updateCommunities() : void
    {
        // @TODO: Delete deprecated communities
        $validatedImportTypes = $this->validateImportTypes($_POST['import_type']);

        $servers = ilECSServerSettings::getInstance();
        foreach ($servers->getServers(ilECSServerSettings::ACTIVE_SERVER) as $server) {
            try {
                // Read communities
                $cReader = ilECSCommunityReader::getInstanceByServerId($server->getServerId());

                // Update community cache
                foreach ($cReader->getCommunities() as $community) {
                    $cCache = ilECSCommunityCache::getInstance($server->getServerId(), $community->getId());
                    $cCache->setCommunityName($community->getTitle());
                    $cCache->setMids($community->getMids());
                    $cCache->setOwnId($community->getOwnId());
                    $cCache->update();
                }
            } catch (Exception $e) {
                $this->log->error('Cannot read ecs communities: ' . $e->getMessage());
            }
        }
        foreach ((array) $_POST['sci_mid'] as $sid => $mids) {
            $this->log->info("server id is " . print_r($sid, true));
            foreach ((array) $mids as $mid => $value) {
                $set = new ilECSParticipantSetting($sid, $mid);
                #$set->enableExport(array_key_exists($mid, (array) $_POST['export'][$sid]) ? true : false);
                #$set->enableImport(array_key_exists($mid, (array) $_POST['import'][$sid]) ? true : false);
                if ($validatedImportTypes) {
                    $set->setImportType((int) $validatedImportTypes[$sid][$mid]);
                } else {
                    $set->setImportType((int) $_POST['import_type'][$sid][$mid]);
                }

                // update title/cname
                try {
                    $part = ilECSCommunityReader::getInstanceByServerId($sid)->getParticipantByMID($mid);
                    if ($part instanceof ilECSParticipant) {
                        $set->setTitle($part->getParticipantName());
                    }
                    $com = ilECSCommunityReader::getInstanceByServerId($sid)->getCommunityByMID($mid);
                    if ($com instanceof ilECSCommunity) {
                        $set->setCommunityName($com->getTitle());
                    }
                } catch (Exception $e) {
                    $this->log->error('Cannot read ecs communities: ' . $e->getMessage());
                }

                $set->update();
            }
        }
        if ($validatedImportTypes) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('ecs_invalid_import_type_cms'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        }
        $this->ctrl->redirect($this, 'communities');
        // TODO: Do update of remote courses and ...
    }


    /**
     * Handle tabs for ECS data mapping
     */
    protected function setMappingTabs(int $a_active) : void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->clearSubTabs();

        $this->tabs_gui->setBackTarget(
            $this->lng->txt('ecs_settings'),
            $this->ctrl->getLinkTarget($this, 'overview')
        );
        if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
            $this->tabs_gui->addTab(
                'import',
                $this->lng->txt('ecs_tab_import'),
                $this->ctrl->getLinkTarget($this, 'importMappings')
            );
        }
        $this->tabs_gui->addTab(
            'export',
            $this->lng->txt('ecs_tab_export'),
            $this->ctrl->getLinkTarget($this, 'exportMappings')
        );


        switch ($a_active) {
            case self::MAPPING_IMPORT:
                $this->tabs_gui->activateTab('import');
                break;

            case self::MAPPING_EXPORT:
                $this->tabs_gui->activateTab('export');
                break;
        }
    }
    
    /**
     * Show mapping settings (EContent-Data <-> (Remote)Course
     */
    public function importMappings() : void
    {
        $this->setMappingTabs(self::MAPPING_IMPORT);

        $fields = ilAdvancedMDFieldDefinition::getInstancesByObjType('crs');
        if (!count($fields)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('ecs_no_adv_md'));
            return;
        }

        $settings = ilECSServerSettings::getInstance();
        
        $sel_srv = (int) $_REQUEST["ecs_mapping_server"];
        if (!$sel_srv) {
            $sel_srv = $_SESSION["ecs_sel_srv"];
        } else {
            $_SESSION["ecs_sel_srv"] = $sel_srv;
        }
        
        // Iterate all servers
        $options = array(0 => $this->lng->txt("please_choose"));
        foreach ($settings->getServers(ilECSServerSettings::ALL_SERVER) as $server) {
            $title = $server->getTitle();
            if (!$title) {
                $title = "ECS (" . $server->getServerId() . ")";
            }
            $options[$server->getServerId()] = $title;
        }
        
        $sel = new ilSelectInputGUI("", "ecs_mapping_server");
        $sel->setOptions($options);
        $sel->setValue($sel_srv);
        $this->toolbar->addInputItem($sel);
        
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "importMappings"));
        $this->toolbar->addFormButton($this->lng->txt("submit"), "importMappings");
        
        if ($sel_srv) {
            $form = $this->initMappingsForm($sel_srv, self::MAPPING_IMPORT);
            $this->tpl->setContent($form->getHTML());
        }
    }

    /**
     * Show mapping settings (EContent-Data <-> (Remote)Course
     */
    protected function exportMappings() : void
    {
        $this->setMappingTabs(self::MAPPING_EXPORT);

        $fields = ilAdvancedMDFieldDefinition::getInstancesByObjType('crs');
        if (!count($fields)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('ecs_no_adv_md'));
            return;
        }

        $settings = ilECSServerSettings::getInstance();
                
        $sel_srv = (int) $_REQUEST["ecs_mapping_server"];
        if (!$sel_srv) {
            $sel_srv = $_SESSION["ecs_sel_srv"];
        } else {
            $_SESSION["ecs_sel_srv"] = $sel_srv;
        }
        
        // Iterate all servers
        $options = array(0 => $this->lng->txt("please_choose"));
        foreach ($settings->getServers(ilECSServerSettings::ALL_SERVER) as $server) {
            $title = $server->getTitle();
            if (!$title) {
                $title = "ECS (" . $server->getServerId() . ")";
            }
            $options[$server->getServerId()] = $title;
        }
        
        $sel = new ilSelectInputGUI("", "ecs_mapping_server");
        $sel->setOptions($options);
        $sel->setValue($sel_srv);
        $this->toolbar->addInputItem($sel);
        
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "exportMappings"));
        $this->toolbar->addFormButton($this->lng->txt("submit"), "exportMappings");
        
        if ($sel_srv) {
            $form = $this->initMappingsForm($sel_srv, self::MAPPING_EXPORT);
            $this->tpl->setContent($form->getHTML());
        }
    }
    
    /**
     * Save mappings
     */
    protected function saveImportMappings() : void
    {
        foreach ((array) $_POST['mapping'] as $mtype => $mappings) {
            foreach ((array) $mappings as $ecs_field => $advmd_id) {
                $map = new ilECSDataMappingSetting(
                    (int) $_REQUEST['ecs_mapping_server'],
                    (int) $mtype,
                    $ecs_field
                );
                $map->setAdvMDId($advmd_id);
                $map->save();
            }
        }
        
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'), true);
        $this->ctrl->setParameter($this, "ecs_mapping_server", (int) $_POST['ecs_mapping_server']);
        $this->ctrl->redirect($this, 'importMappings');
    }

    /**
     * Save mappings
     */
    protected function saveExportMappings() : void
    {
        foreach ((array) $_POST['mapping'] as $mtype => $mappings) {
            foreach ((array) $mappings as $ecs_field => $advmd_id) {
                $map = new ilECSDataMappingSetting(
                    (int) $_POST['ecs_mapping_server'],
                    (int) $mtype,
                    $ecs_field
                );
                $map->setAdvMDId($advmd_id);
                $map->save();
            }
        }

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'), true);
        $this->ctrl->setParameter($this, "ecs_mapping_server", (int) $_POST['ecs_mapping_server']);
        $this->ctrl->redirect($this, 'exportMappings');
    }

    /**
     * init mapping form
     */
    protected function initMappingsForm(int $a_server_id, int $mapping_type) : ilPropertyFormGUI
    {
        $mapping_settings = ilECSDataMappingSettings::getInstanceByServerId($a_server_id);
            
        $form = new ilPropertyFormGUI();

        if ($mapping_type === self::MAPPING_IMPORT) {
            $form->setTitle($this->lng->txt('ecs_mapping_tbl'));
            $form->addCommandButton('saveImportMappings', $this->lng->txt('save'));
            $form->addCommandButton('importMappings', $this->lng->txt('cancel'));
        } else {
            $form->setTitle($this->lng->txt('ecs_mapping_exp_tbl'));
            $form->addCommandButton('saveExportMappings', $this->lng->txt('save'));
            $form->addCommandButton('exportMappings', $this->lng->txt('cancel'));
        }

        $form->setFormAction($this->ctrl->getFormAction($this, 'saveMappings'));

        if ($mapping_type === self::MAPPING_IMPORT) {
            $assignments = new ilCustomInputGUI($this->lng->txt('ecs_mapping_crs'));
            $form->addItem($assignments);
        }

        $fields = ilAdvancedMDFieldDefinition::getInstancesByObjType('crs');
        $options = $this->prepareFieldSelection($fields);

        // get all optional ecourse fields
        $optional = ilECSUtils::_getOptionalECourseFields();
        foreach ($optional as $field_name) {
            if ($mapping_type === self::MAPPING_IMPORT) {
                $select = new ilSelectInputGUI(
                    $this->lng->txt('ecs_field_' . $field_name),
                    'mapping' . '[' . ilECSDataMappingSetting::MAPPING_IMPORT_CRS . '][' . $field_name . ']'
                );

                $select->setValue(
                    $mapping_settings->getMappingByECSName(
                        ilECSDataMappingSetting::MAPPING_IMPORT_CRS,
                        $field_name
                    )
                );
                $select->setOptions($options);
                $assignments->addSubItem($select);
            } else {
                $select = new ilSelectInputGUI(
                    $this->lng->txt('ecs_field_' . $field_name),
                    'mapping' . '[' . ilECSDataMappingSetting::MAPPING_EXPORT . '][' . $field_name . ']'
                );
                $select->setValue(
                    $mapping_settings->getMappingByECSName(
                        ilECSDataMappingSetting::MAPPING_EXPORT,
                        $field_name
                    )
                );
                $select->setOptions($options);
                $form->addItem($select);
            }
        }

        $server = new ilHiddenInputGUI('ecs_mapping_server');
        $server->setValue((string) $a_server_id);
        $form->addItem($server);

        // Remote courses
        // no remote course settings for export
        if ($mapping_type === self::MAPPING_EXPORT) {
            return $form;
        }

        $rcrs = new ilCustomInputGUI($this->lng->txt('ecs_mapping_rcrs'));
        $form->addItem($rcrs);

        $fields = ilAdvancedMDFieldDefinition::getInstancesByObjType('rcrs');
        $options = $this->prepareFieldSelection($fields);

        // get all optional econtent fields
        $optional = ilECSUtils::_getOptionalEContentFields();
        foreach ($optional as $field_name) {
            $select = new ilSelectInputGUI(
                $this->lng->txt('ecs_field_' . $field_name),
                'mapping[' . ilECSDataMappingSetting::MAPPING_IMPORT_RCRS . '][' . $field_name . ']'
            );
            $select->setValue(
                $mapping_settings->getMappingByECSName(
                    ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,
                    $field_name
                )
            );
            $select->setOptions($options);
            $rcrs->addSubItem($select);
        }
        return $form;
    }
    
    /**
     * Category mappings
     */
    protected function categoryMapping() : void
    {
        $this->tabs_gui->setSubTabActive('ecs_category_mapping');
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.category_mapping.html', 'Services/WebServices/ECS');
        
        $this->initRule();
        $this->initCategoryMappingForm();
        
        
        $this->tpl->setVariable('NEW_RULE_TABLE', $this->form->getHTML());
        if ($html = $this->showRulesTable()) {
            $this->tpl->setVariable('RULES_TABLE', $html);
        }
    }
    
    /**
     * save category mapping
     */
    protected function addCategoryMapping() : bool
    {
        $this->initRule();
        
        $this->initCategoryMappingForm('add');
        if ($this->form->checkInput()) {
            $this->rule->setContainerId($this->form->getInput('import_id'));
            $this->rule->setFieldName($this->form->getInput('field'));
            $this->rule->setMappingType($this->form->getInput('type'));

            switch ($this->form->getInput('type')) {
                case ilECSCategoryMappingRule::TYPE_FIXED:
                    $this->rule->setMappingValue($this->form->getInput('mapping_value'));
                    break;
                
                case ilECSCategoryMappingRule::TYPE_DURATION:
                    if ($this->form->getItemByPostVar('dur_begin')) {
                        $this->rule->setDateRangeStart($this->form->getItemByPostVar('dur_begin')->getDate());
                    }
                    if ($this->form->getItemByPostVar('dur_end')) {
                        $this->rule->setDateRangeEnd($this->form->getItemByPostVar('dur_end')->getDate());
                    }
                    break;
                
                case ilECSCategoryMappingRule::TYPE_BY_TYPE:
                    $this->rule->setByType($this->form->getInput('by_type'));
                    break;
            }
            
            if ($err = $this->rule->validate()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt($err));
                $this->form->setValuesByPost();
                $this->categoryMapping();
                return false;
            }
            
            $this->rule->save();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'));
            unset($this->rule);
            $this->categoryMapping();
            return true;
        }
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('err_check_input'));
        $this->form->setValuesByPost();
        $this->categoryMapping();
        return false;
    }
    
    /**
     * Edit category mapping
     */
    protected function editCategoryMapping() : bool
    {
        if (!$_REQUEST['rule_id']) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->categoryMapping();
            return false;
        }

        $this->tabs_gui->setSubTabActive('ecs_category_mapping');
        $this->ctrl->saveParameter($this, 'rule_id');
        $this->initRule((int) $_REQUEST['rule_id']);
        
        $this->initCategoryMappingForm('edit');
        $this->tpl->setContent($this->form->getHTML());
        return true;
    }
    
    /**
     * update category mapping
     */
    protected function updateCategoryMapping() : bool
    {
        if (!$_REQUEST['rule_id']) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->categoryMapping();
            return false;
        }
        $this->ctrl->saveParameter($this, 'rule_id');
        $this->initRule((int) $_REQUEST['rule_id']);
        $this->initCategoryMappingForm('edit');
        if ($this->form->checkInput()) {
            $this->rule->setContainerId($this->form->getInput('import_id'));
            $this->rule->setFieldName($this->form->getInput('field'));
            $this->rule->setMappingType($this->form->getInput('type'));

            
            switch ($this->form->getInput('type')) {
                case ilECSCategoryMappingRule::TYPE_FIXED:
                    $this->rule->setMappingValue($this->form->getInput('mapping_value'));
                    break;
                
                case ilECSCategoryMappingRule::TYPE_DURATION:
                    if ($this->form->getItemByPostVar('dur_begin')) {
                        $this->rule->setDateRangeStart($this->form->getItemByPostVar('dur_begin')->getDate());
                    }
                    if ($this->form->getItemByPostVar('dur_end')) {
                        $this->rule->setDateRangeEnd($this->form->getItemByPostVar('dur_end')->getDate());
                    }
                    break;
                
                case ilECSCategoryMappingRule::TYPE_BY_TYPE:
                    $this->rule->setByType($this->form->getInput('by_type'));
                    break;
            }
            
            if ($err = $this->rule->validate()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt($err));
                $this->form->setValuesByPost();
                $this->editCategoryMapping();
                return false;
            }
            
            $this->rule->update();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'categoryMapping');
            return true;
        }
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('err_check_input'));
        $this->form->setValuesByPost();
        $this->editCategoryMapping();
        return false;
    }
    
    /**
     * Delete selected category mappings
     */
    protected function deleteCategoryMappings() : bool
    {
        if (!is_array($_POST['rules']) || !$_POST['rules']) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox'));
            $this->categoryMapping();
            return false;
        }
        foreach ($_POST['rules'] as $rule_id) {
            $rule = new ilECSCategoryMappingRule($rule_id);
            $rule->delete();
        }
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'));
        $this->categoryMapping();
        return true;
    }
    
    /**
     * Show rules table
     */
    protected function showRulesTable() : string
    {
        $rule_table = new ilECSCategoryMappingTableGUI($this, 'categoryMapping');
        $rule_table->parse(ilECSCategoryMapping::getActiveRules());
        return $rule_table->getHTML();
    }
    
    /**
     * Init rule
     * @param int	$rule_id	rule id
     */
    protected function initRule(int $a_rule_id = 0) : void
    {
        if (isset($this->rule) && is_object($this->rule)) {
            return;
        }
        
        $this->rule = new ilECSCategoryMappingRule($a_rule_id);
    }
    
    /**
     * Init category mapping form
     */
    protected function initCategoryMappingForm($a_mode = 'add') : void
    {
        if (is_object($this->form)) {
            return;
        }

        $this->form = new ilPropertyFormGUI();
        
        if ($a_mode === 'add') {
            $this->form->setTitle($this->lng->txt('ecs_new_category_mapping'));
            $this->form->setFormAction($this->ctrl->getFormAction($this, 'categoryMapping'));
            $this->form->addCommandButton('addCategoryMapping', $this->lng->txt('save'));
        } else {
            $this->form->setTitle($this->lng->txt('ecs_edit_category_mapping'));
            $this->form->setFormAction($this->ctrl->getFormAction($this, 'editCategoryMapping'));
            $this->form->addCommandButton('updateCategoryMapping', $this->lng->txt('save'));
        }
        $this->form->addCommandButton('categoryMapping', $this->lng->txt('cancel'));

        $imp = new ilCustomInputGUI($this->lng->txt('ecs_import_id'), 'import_id');
        $imp->setRequired(true);
        
        $tpl = new ilTemplate('tpl.ecs_import_id_form.html', true, true, 'Services/WebServices/ECS');
        $tpl->setVariable('SIZE', 5);
        $tpl->setVariable('MAXLENGTH', 11);
        $tpl->setVariable('POST_VAR', 'import_id');
        $tpl->setVariable('PROPERTY_VALUE', $this->rule->getContainerId());
        
        if ($this->settings->getImportId()) {
            $tpl->setVariable('COMPLETE_PATH', $this->buildPath($this->rule->getContainerId()));
        }
        
        $imp->setHTML($tpl->get());
        $imp->setInfo($this->lng->txt('ecs_import_id_info'));
        $this->form->addItem($imp);
        
        $select = new ilSelectInputGUI($this->lng->txt('ecs_attribute_name'), 'field');
        $select->setValue($this->rule->getFieldName());
        $select->setRequired(true);
        $select->setOptions(ilECSCategoryMapping::getPossibleFields());
        $this->form->addItem($select);

        //	Value
        $value = new ilRadioGroupInputGUI($this->lng->txt('ecs_cat_mapping_type'), 'type');
        $value->setValue((string) $this->rule->getMappingType());
        $value->setRequired(true);
        
        $fixed = new ilRadioOption($this->lng->txt('ecs_cat_mapping_fixed'), (string) ilECSCategoryMappingRule::TYPE_FIXED);
        $fixed->setInfo($this->lng->txt('ecs_cat_mapping_fixed_info'));
        
        $fixed_val = new ilTextInputGUI($this->lng->txt('ecs_cat_mapping_values'), 'mapping_value');
        $fixed_val->setValue($this->rule->getMappingValue());
        $fixed_val->setMaxLength(255);
        $fixed_val->setSize(40);
        $fixed_val->setRequired(true);
        $fixed->addSubItem($fixed_val);
        
        $value->addOption($fixed);

        $duration = new ilRadioOption($this->lng->txt('ecs_cat_mapping_duration'), (string) ilECSCategoryMappingRule::TYPE_DURATION);
        $duration->setInfo($this->lng->txt('ecs_cat_mapping_duration_info'));
        
        $dur_start = new ilDateTimeInputGUI($this->lng->txt('from'), 'dur_begin');
        $dur_start->setRequired(true);
        $dur_start->setDate($this->rule->getDateRangeStart());
        $duration->addSubItem($dur_start);
            
        $dur_end = new ilDateTimeInputGUI($this->lng->txt('to'), 'dur_end');
        $dur_end->setRequired(true);
        $dur_end->setDate($this->rule->getDateRangeEnd());
        $duration->addSubItem($dur_end);
        
        $value->addOption($duration);
        
        $type = new ilRadioOption($this->lng->txt('ecs_cat_mapping_by_type'), (string) ilECSCategoryMappingRule::TYPE_BY_TYPE);
        $type->setInfo($this->lng->txt('ecs_cat_mapping_by_type_info'));
        
        $options = ilECSUtils::getPossibleRemoteTypes(true);
        
        $types = new ilSelectInputGUI($this->lng->txt('type'), 'by_type');
        $types->setOptions($options);
        $types->setValue($this->rule->getByType());
        $types->setRequired(true);
        $type->addSubitem($types);
        
        $value->addOption($type);
        
        $this->form->addItem($value);
    }
    
    
    /**
     * Show imported materials
     */
    protected function imported() : bool
    {
        $this->tabs_gui->setSubTabActive('ecs_import');
    
        if (ilECSServerSettings::getInstance()->activeServerExists()) {
            $this->toolbar->addButton(
                $this->lng->txt('ecs_read_remote_links'),
                $this->ctrl->getLinkTarget($this, 'readAll')
            );
            
            $this->toolbar->addSeparator();
        }
        
        
        $sel_type = $_REQUEST["otype"];
        if (!$sel_type) {
            $sel_type = "rcrs";
        }
        
        $options = ilECSUtils::getPossibleRemoteTypes(true);
        
        $sel = new ilSelectInputGUI("", "otype");
        $sel->setOptions($options);
        $sel->setValue($sel_type);
        $this->toolbar->addInputItem($sel);
        
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "imported"));
        $this->toolbar->addFormButton($this->lng->txt("submit"), "imported");
                            
        $robjs = ilUtil::_getObjectsByOperations($sel_type, 'visible', $this->user->getId(), -1);
        if (count($robjs)) {
            $this->toolbar->addSeparator();
            
            $this->toolbar->addButton(
                $this->lng->txt('csv_export'),
                $this->ctrl->getLinkTarget($this, 'exportImported')
            );
        }
        $table_gui = new ilECSImportedContentTableGUI($this, 'imported');
        $table_gui->setTitle($this->lng->txt('ecs_imported_content'));
        $table_gui->parse($robjs);
        $this->tpl->setContent($table_gui->getHTML());

        return true;
    }
    
    /**
     * csv export of imported remote courses
     */
    protected function exportImported() : void
    {
        // :TODO: mind resource type and move to ilRemoteObjectBase...
        
        $rcourses = ilUtil::_getObjectsByOperations('rcrs', 'visible', $this->user->getId(), -1);
        
        // Read participants
        try {
            $reader = ilECSCommunityReader::getInstanceByServerId($this->settings->getServerId());
        } catch (ilECSConnectorException $e) {
            $reader = null;
        }
        
        // read obj_ids
        $this->objDataCache->preloadReferenceCache($rcourses);
        $obj_ids = array();
        foreach ($rcourses as $rcrs_ref_id) {
            $obj_id = $this->objDataCache->lookupObjId((int) $rcrs_ref_id);
            $obj_ids[$obj_id] = $obj_id;
        }

        $writer = new ilCSVWriter();
        
        $writer->addColumn($this->lng->txt('title'));
        $writer->addColumn($this->lng->txt('description'));
        $writer->addColumn($this->lng->txt('ecs_imported_from'));
        $writer->addColumn($this->lng->txt('ecs_field_courseID'));
        $writer->addColumn($this->lng->txt('ecs_field_term'));
        $writer->addColumn($this->lng->txt('ecs_field_lecturer'));
        $writer->addColumn($this->lng->txt('ecs_field_courseType'));
        $writer->addColumn($this->lng->txt('ecs_field_semester_hours'));
        $writer->addColumn($this->lng->txt('ecs_field_credits'));
        $writer->addColumn($this->lng->txt('ecs_field_room'));
        $writer->addColumn($this->lng->txt('ecs_field_cycle'));
        $writer->addColumn($this->lng->txt('ecs_field_begin'));
        $writer->addColumn($this->lng->txt('ecs_field_end'));
        $writer->addColumn($this->lng->txt('last_update'));
        
        // TODO fix getting proper datamappingsettings for each server
        $settings = ilECSDataMappingSettings::getInstanceByServerId(1);
        
        foreach ($obj_ids as $obj_id) {
            $rcourse = new ilObjRemoteCourse($obj_id, false);
            $values = ilECSUtils::getAdvancedMDValuesForObjId($obj_id);
            
            $writer->addRow();
            $writer->addColumn(ilObject::_lookupTitle($obj_id));
            $writer->addColumn(ilObject::_lookupDescription($obj_id));
            
            $mid = $rcourse->getMID();
            if ($reader && ($participant = $reader->getParticipantByMID($mid))) {
                $writer->addColumn($participant->getParticipantName());
            }
            $field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'courseID');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'term');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'lecturer');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'courseType');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'semester_hours');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'credits');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'room');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'cycle');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'begin');
            $dt = '';
            if (isset($values[$field])) {
                $dt = new ilDateTime($values[$field], IL_CAL_UNIX);
                $dt = $dt->get(IL_CAL_DATETIME);
            }
            $writer->addColumn($dt);
            
            $field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS, 'end');
            $dt = '';
            if (isset($values[$field])) {
                $dt = new ilDateTime($values[$field], IL_CAL_UNIX);
                $dt = $dt->get(IL_CAL_DATETIME);
            }
            $writer->addColumn($dt);
            
            $writer->addColumn($this->objDataCache->lookupLastUpdate((int) $obj_id));
        }

        $stream = \ILIAS\Filesystem\Stream\Streams::ofString($writer->getCSVString());
        $this->http->saveResponse($this->http
            ->response()
            ->withAddedHeader('Content-Type', 'text/csv')
            ->withAddedHeader('Content-Disposition', 'attachment; filename="' . date("Y_m_d") . '_ecs_import.csv' . '"')
            ->withBody($stream));
        $this->http->sendResponse();
        $this->http->close();
    }
    
    /**
     * Show released materials
     */
    protected function released() : void
    {
        $this->tabs_gui->setSubTabActive('ecs_released');
                        
        if ($this->settings->isEnabled()) {
            $this->toolbar->addButton(
                $this->lng->txt('ecs_read_remote_links'),
                $this->ctrl->getLinkTarget($this, 'readAll')
            );
            
            $this->toolbar->addSeparator();
        }
        
        $sel_type = $_REQUEST["otype"];
        if (!$sel_type) {
            $sel_type = "rcrs";
        }
        
        $options = ilECSUtils::getPossibleReleaseTypes(true);
        
        $sel = new ilSelectInputGUI("", "otype");
        $sel->setOptions($options);
        $sel->setValue($sel_type);
        $this->toolbar->addInputItem($sel);
        
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "released"));
        $this->toolbar->addFormButton($this->lng->txt("submit"), "released");
                                    
        $exported = ilECSExportManager::getInstance()->getExportedIdsByType($sel_type);
        if (count($exported)) {
            $this->toolbar->addSeparator();
            
            $this->toolbar->addButton(
                $this->lng->txt('csv_export'),
                $this->ctrl->getLinkTarget($this, 'exportReleased')
            );
        }
        
        $table_gui = new ilECSExportedContentTableGUI($this, 'released');
        $table_gui->setTitle($this->lng->txt('ecs_released_content'));
        $table_gui->parse($exported);
        $this->tpl->setContent($table_gui->getHTML());
    }
    
    /**
     * export released
     */
    protected function exportReleased() : void
    {
        $exported = ilECSExportManager::getInstance()->getExportedIds();
        $this->objDataCache->preloadObjectCache($exported);
        
        $writer = new ilCSVWriter();
        
        $writer->addColumn($this->lng->txt('title'));
        $writer->addColumn($this->lng->txt('description'));
        $writer->addColumn($this->lng->txt('ecs_field_courseID'));
        $writer->addColumn($this->lng->txt('ecs_field_term'));
        $writer->addColumn($this->lng->txt('ecs_field_lecturer'));
        $writer->addColumn($this->lng->txt('ecs_field_courseType'));
        $writer->addColumn($this->lng->txt('ecs_field_semester_hours'));
        $writer->addColumn($this->lng->txt('ecs_field_credits'));
        $writer->addColumn($this->lng->txt('ecs_field_room'));
        $writer->addColumn($this->lng->txt('ecs_field_cycle'));
        $writer->addColumn($this->lng->txt('ecs_field_begin'));
        $writer->addColumn($this->lng->txt('ecs_field_end'));
        $writer->addColumn($this->lng->txt('last_update'));
        
        $settings = ilECSDataMappingSettings::getInstanceByServerId($this->settings->getServerId());

        foreach ($exported as $obj_id) {
            $values = ilECSUtils::getAdvancedMDValuesForObjId($obj_id);
            
            $writer->addRow();
            $writer->addColumn(ilObject::_lookupTitle($obj_id));
            $writer->addColumn(ilObject::_lookupDescription($obj_id));
            
            $field = $settings->getMappingByECSName(0, 'courseID');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(0, 'term');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(0, 'lecturer');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(0, 'courseType');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(0, 'semester_hours');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(0, 'credits');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(0, 'room');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(0, 'cycle');
            $writer->addColumn($values[$field] ?? '');
            
            $field = $settings->getMappingByECSName(0, 'begin');
            $dt = '';
            if (isset($values[$field])) {
                $dt = new ilDateTime($values[$field], IL_CAL_UNIX);
                $dt = $dt->get(IL_CAL_DATETIME);
            }
            $writer->addColumn($dt);
            
            $field = $settings->getMappingByECSName(0, 'end');
            $dt = '';
            if (isset($values[$field])) {
                $dt = new ilDateTime($values[$field], IL_CAL_UNIX);
                $dt = $dt->get(IL_CAL_DATETIME);
            }
            $writer->addColumn($dt);
            
            $writer->addColumn($this->objDataCache->lookupLastUpdate((int) $obj_id));
        }

        $stream = \ILIAS\Filesystem\Stream\Streams::ofString($writer->getCSVString());
        $this->http->saveResponse($this->http
            ->response()
            ->withAddedHeader('Content-Type', 'text/csv')
            ->withAddedHeader('Content-Disposition', 'attachment; filename="' . date("Y_m_d") . '_ecs_export.csv' . '"')
            ->withBody($stream));
        $this->http->sendResponse();
        $this->http->close();
    }
    
    
    /**
     * get options for field selection
     * @param array array of field objects
     */
    protected function prepareFieldSelection($fields) : array
    {
        $options[0] = $this->lng->txt('ecs_ignore_field');
        foreach ($fields as $field) {
            $title = ilAdvancedMDRecord::_lookupTitle($field->getRecordId());
            $options[$field->getFieldId()] = $title . ': ' . $field->getTitle();
        }
        return $options;
    }
    


    /**
     * Init settings
     */
    protected function initSettings(int $a_server_id = 1) : void
    {
        $this->settings = ilECSSetting::getInstanceByServerId($a_server_id);
    }
    
    /**
     * set sub tabs
     */
    protected function setSubTabs() : void
    {
        $this->tabs_gui->clearSubTabs();
        
        $this->tabs_gui->addSubTabTarget(
            "overview",
            $this->ctrl->getLinkTarget($this, 'overview'),
            "overview",
            get_class($this)
        );

        // Disable all other tabs, if server hasn't been configured.
        if (ilECSServerSettings::getInstance()->serverExists()) {
            $this->tabs_gui->addSubTabTarget(
                "ecs_communities",
                $this->ctrl->getLinkTarget($this, 'communities'),
                "communities",
                get_class($this)
            );

            if ($this->access->checkAccess('write', '', (int) $_REQUEST["ref_id"])) {
                $this->tabs_gui->addSubTabTarget(
                    'ecs_mappings',
                    $this->ctrl->getLinkTarget($this, 'importMappings'),
                    'importMappings',
                    get_class($this)
                );

                $this->tabs_gui->addSubTabTarget(
                    'ecs_category_mapping',
                    $this->ctrl->getLinkTarget($this, 'categoryMapping')
                );

                $this->tabs_gui->addSubTabTarget(
                    'ecs_import',
                    $this->ctrl->getLinkTarget($this, 'imported')
                );

                $this->tabs_gui->addSubTabTarget(
                    'ecs_released',
                    $this->ctrl->getLinkTarget($this, 'released')
                );
            }
        }
    }
    
    /**
     * get global role array
     */
    private function prepareRoleSelect() : array
    {
        $global_roles = ilUtil::_sortIds(
            $this->rbacreview->getGlobalRoles(),
            'object_data',
            'title',
            'obj_id'
        );
        
        $select[0] = $this->lng->txt('links_select_one');
        foreach ($global_roles as $role_id) {
            $select[$role_id] = ilObject::_lookupTitle((int) $role_id);
        }
        return $select;
    }

    private function buildPath(int $a_ref_id) : string
    {
        if (!$this->tree->isInTree($a_ref_id) || $this->tree->isDeleted($a_ref_id)) {
            return '';
        }
        $loc = new ilLocatorGUI();
        $loc->setTextOnly(false);
        $loc->addContextItems($a_ref_id);
        
        return $loc->getHTML();
    }
}
