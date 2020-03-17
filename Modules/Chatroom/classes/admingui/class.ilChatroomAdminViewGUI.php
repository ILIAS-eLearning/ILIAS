<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomConfigFileHandler.php';

/**
 * Class ilChatroomAdminViewGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomAdminViewGUI extends ilChatroomGUIHandler
{
    const CHATROOM_README_PATH = '/Modules/Chatroom/README.md';

    /**
     * @var ilSetting
     */
    protected $commonSettings;

    /**
     * @var ilTemplate
     */
    protected $ilTpl;

    /**
     * Constructor
     * @param ilChatroomObjectGUI $gui
     */
    public function __construct(ilChatroomObjectGUI $gui)
    {
        global $DIC;

        parent::__construct($gui);
        $this->commonSettings = new ilSetting('common');
        $this->ilTpl = $DIC->ui()->mainTemplate();
    }

    /**
     * {@inheritdoc}
     */
    public function executeDefault($method)
    {
        $this->ilCtrl->redirect($this->gui, 'view-clientsettings');
    }

    /**
     * Saves settings fetched from $_POST
     */
    public function saveSettings()
    {
        $this->redirectIfNoPermission('write');

        $factory = new ilChatroomFormFactory();
        $form = $factory->getGeneralSettingsForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->serversettings($form);
            return;
        }

        $settings = array(
            'protocol' => $form->getInput('protocol'),
            'port' => $form->getInput('port'),
            'address' => $form->getInput('address'),
            'cert' => $form->getInput('cert'),
            'key' => $form->getInput('key'),
            'dhparam' => $form->getInput('dhparam'),
            'log' => $form->getInput('log'),
            'error_log' => $form->getInput('error_log'),
            'ilias_proxy' => $form->getInput('ilias_proxy'),
            'ilias_url' => $form->getInput('ilias_url'),
            'client_proxy' => $form->getInput('client_proxy'),
            'client_url' => $form->getInput('client_url'),
            'sub_directory' => $form->getInput('sub_directory'),
            'deletion_mode' => $form->getInput('deletion_mode'),
            'deletion_unit' => $form->getInput('deletion_unit'),
            'deletion_value' => $form->getInput('deletion_value'),
            'deletion_time' => $form->getInput('deletion_time'),
        );

        $adminSettings = new ilChatroomAdmin($this->gui->object->getId());
        $adminSettings->saveGeneralSettings((object) $settings);

        $fileHandler = new ilChatroomConfigFileHandler();
        $fileHandler->createServerConfigFile($settings);

        ilUtil::sendSuccess($this->ilLng->txt('settings_has_been_saved'), true);
        $this->ilCtrl->redirect($this->gui, 'view-serversettings');
    }

    /**
     * Prepares view form and displays it.
     * @param ilPropertyFormGUI $form
     */
    public function serversettings(ilPropertyFormGUI $form = null)
    {
        $this->redirectIfNoPermission('read');

        $this->defaultActions();
        $this->gui->switchToVisibleMode();

        $adminSettings = new ilChatroomAdmin($this->gui->object->getId());
        $serverSettings = $adminSettings->loadGeneralSettings();

        if ($form === null) {
            $factory = new ilChatroomFormFactory();
            $form = $factory->getGeneralSettingsForm();
            $form->setValuesByArray($serverSettings);
        }

        $this->checkServerConnection($serverSettings);

        $form->setTitle($this->ilLng->txt('chatserver_settings_title'));
        if (ilChatroom::checkUserPermissions('write', $this->gui->ref_id, false)) {
            $form->addCommandButton('view-saveSettings', $this->ilLng->txt('save'));
        }
        $form->setFormAction($this->ilCtrl->getFormAction($this->gui, 'view-saveSettings'));

        $settingsTpl = $this->createSettingTemplate($form);
        $this->ilTpl->setVariable('ADM_CONTENT', $settingsTpl->get());
    }

    /**
     *
     */
    private function defaultActions()
    {
        $chatSettings = new ilSetting('chatroom');
        if ($chatSettings->get('chat_enabled', false)) {
            $this->forcePublicRoom();
        }
    }

    /**
     *
     */
    public function forcePublicRoom()
    {
        $ref_id = ilObjChatroom::_getPublicRefId();
        if (!$ref_id) {
            $this->createPublicRoom();
            return;
        }

        $instance = ilObjectFactory::getInstanceByRefId($ref_id, false);
        if (!$instance) {
            $this->createPublicRoom();
            return;
        }

        $obj_id = ilObject::_lookupObjId($ref_id);
        if (!$obj_id) {
            $this->createPublicRoom();
            return;
        }

        if (!ilObject::_hasUntrashedReference($obj_id)) {
            $this->createPublicRoom();
            return;
        }

        require_once 'Modules/Chatroom/classes/class.ilChatroomInstaller.php';
        ilChatroomInstaller::ensureCorrectPublicChatroomTreeLocation($ref_id);
    }

    /**
     * Creates a public chatroom.
     */
    public function createPublicRoom()
    {
        require_once 'Modules/Chatroom/classes/class.ilChatroomInstaller.php';
        ilChatroomInstaller::createDefaultPublicRoom(true);
        ilUtil::sendSuccess($this->ilLng->txt('public_chat_created'), true);
    }

    /**
     * Checks for server connection. If no connection if possible, show info in gui
     * @param array $serverSettings
     */
    protected function checkServerConnection(array $serverSettings)
    {
        require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';

        if (
            $serverSettings['port'] &&
            $serverSettings['address'] &&
            !(boolean) @ilChatroomServerConnector::checkServerConnection(false)
        ) {
            ilUtil::sendInfo($this->ilLng->txt('chat_cannot_connect_to_server'));
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     * @return ilTemplate
     */
    protected function createSettingTemplate(ilPropertyFormGUI $form)
    {
        $furtherInformation = sprintf($this->ilLng->txt('server_further_information'), $this->getReadmePath());
        $serverTpl = new ilTemplate('tpl.chatroom_serversettings.html', true, true, 'Modules/Chatroom');
        $serverTpl->setVariable('VAL_SERVERSETTINGS_FORM', $form->getHTML());
        $serverTpl->setVariable('LBL_SERVERSETTINGS_FURTHER_INFORMATION', $furtherInformation);

        return $serverTpl;
    }

    /**
     * Get the path to the README.txt file
     * @return string
     */
    protected function getReadmePath()
    {
        return ilUtil::_getHttpPath() . self::CHATROOM_README_PATH;
    }

    /**
     * Saves client settings fetched from $_POST
     */
    public function saveClientSettings()
    {
        $this->redirectIfNoPermission('write');

        $factory = new ilChatroomFormFactory();
        $form = $factory->getClientSettingsForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->clientsettings($form);
            return;
        }

        $settings = array(
            'name' => (string) $form->getInput('client_name'),
            'enable_osd' => (boolean) $form->getInput('enable_osd'),
            'enable_osc' => (boolean) $form->getInput('enable_osc'),
            'osd_intervall' => (int) $form->getInput('osd_intervall'),
            'chat_enabled' => ((boolean) $form->getInput('chat_enabled')),
            'enable_smilies' => (boolean) $form->getInput('enable_smilies'),
            'play_invitation_sound' => (boolean) $form->getInput('play_invitation_sound'),
            'auth' => $form->getInput('auth')
        );

        if (!$settings['chat_enabled']) {
            $settings['enable_osc'] = false;
        }

        $notificationSettings = new ilSetting('notifications');
        $notificationSettings->set('osd_polling_intervall', (int) $form->getInput('osd_intervall'));
        $notificationSettings->set('enable_osd', (boolean) $form->getInput('enable_osd'));

        $chatSettings = new ilSetting('chatroom');
        $chatSettings->set('chat_enabled', $settings['chat_enabled']);
        $chatSettings->set('enable_osc', $settings['enable_osc']);
        $chatSettings->set('play_invitation_sound', (boolean) $form->getInput('play_invitation_sound'));

        $adminSettings = new ilChatroomAdmin($this->gui->object->getId());
        $adminSettings->saveClientSettings((object) $settings);

        $fileHandler = new ilChatroomConfigFileHandler();
        $fileHandler->createClientConfigFile($settings);

        ilUtil::sendSuccess($this->ilLng->txt('settings_has_been_saved'), true);
        $this->ilCtrl->redirect($this->gui, 'view-clientsettings');
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function clientsettings(ilPropertyFormGUI $form = null)
    {
        $this->redirectIfNoPermission('read');

        $this->defaultActions();
        $this->gui->switchToVisibleMode();

        $adminSettings = new ilChatroomAdmin($this->gui->object->getId());
        $serverSettings = $adminSettings->loadGeneralSettings();

        if ($form === null) {
            $clientSettings = $adminSettings->loadClientSettings();
            $factory = new ilChatroomFormFactory();
            $form = $factory->getClientSettingsForm();
            $form->setValuesByArray($clientSettings);
        }

        $this->checkServerConnection($serverSettings);

        $form->setTitle($this->ilLng->txt('general_settings_title'));
        if (ilChatroom::checkUserPermissions('write', $this->gui->ref_id, false)) {
            $form->addCommandButton('view-saveClientSettings', $this->ilLng->txt('save'));
        }
        $form->setFormAction($this->ilCtrl->getFormAction($this->gui, 'view-saveClientSettings'));

        $settingsTpl = $this->createSettingTemplate($form);
        $this->ilTpl->setVariable('ADM_CONTENT', $settingsTpl->get());
    }
}
