<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomAdminViewGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomAdminViewGUI extends ilChatroomGUIHandler
{
    private const CHATROOM_README_PATH = '/Modules/Chatroom/README.md';

    protected ilSetting $commonSettings;

    public function __construct(ilChatroomObjectGUI $gui)
    {
        global $DIC;

        parent::__construct($gui);
        $this->commonSettings = new ilSetting('common');
    }

    public function executeDefault(string $requestedMethod) : void
    {
        $this->ilCtrl->redirect($this->gui, 'view-clientsettings');
    }

    private function defaultActions() : void
    {
        $chatSettings = new ilSetting('chatroom');
        if ($chatSettings->get('chat_enabled', '0')) {
            $this->forcePublicRoom();
        }
    }

    public function forcePublicRoom() : void
    {
        $ref_id = ilObjChatroom::_getPublicRefId();
        if (!$ref_id) {
            $this->createPublicRoom();
            return;
        }

        $instance = ilObjectFactory::getInstanceByRefId($ref_id, false);
        if (!($instance instanceof ilObjChatroom)) {
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

        ilChatroomInstaller::ensureCorrectPublicChatroomTreeLocation($ref_id);
    }

    public function createPublicRoom() : void
    {
        ilChatroomInstaller::createDefaultPublicRoom(true);
        ilUtil::sendSuccess($this->ilLng->txt('public_chat_created'), true);
    }

    protected function checkServerConnection(array $serverSettings) : void
    {
        if (
            isset($serverSettings['port'], $serverSettings['address']) &&
            !ilChatroomServerConnector::checkServerConnection(false)
        ) {
            ilUtil::sendInfo($this->ilLng->txt('chat_cannot_connect_to_server'));
        }
    }

    protected function createSettingTemplate(ilPropertyFormGUI $form) : ilTemplate
    {
        $furtherInformation = sprintf($this->ilLng->txt('server_further_information'), $this->getReadmePath());
        $serverTpl = new ilTemplate('tpl.chatroom_serversettings.html', true, true, 'Modules/Chatroom');
        $serverTpl->setVariable('VAL_SERVERSETTINGS_FORM', $form->getHTML());
        $serverTpl->setVariable('LBL_SERVERSETTINGS_FURTHER_INFORMATION', $furtherInformation);

        return $serverTpl;
    }

    protected function getReadmePath() : string
    {
        return ilUtil::_getHttpPath() . self::CHATROOM_README_PATH;
    }

    public function saveClientSettings() : void
    {
        $this->redirectIfNoPermission('write');

        $factory = new ilChatroomFormFactory();
        $form = $factory->getClientSettingsForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->clientsettings($form);
            return;
        }

        $convIdleStateTime = max(1, (int) $form->getInput('conversation_idle_state_in_minutes'));

        $settings = array(
            'name' => (string) $form->getInput('client_name'),
            'enable_osd' => (bool) $form->getInput('enable_osd'),
            'enable_osc' => (bool) $form->getInput('enable_osc'),
            'enable_browser_notifications' => (bool) $form->getInput('enable_browser_notifications'),
            'conversation_idle_state_in_minutes' => $convIdleStateTime,
            'osd_intervall' => (int) $form->getInput('osd_intervall'),
            'chat_enabled' => (bool) $form->getInput('chat_enabled'),
            'enable_smilies' => (bool) $form->getInput('enable_smilies'),
            'play_invitation_sound' => (bool) $form->getInput('play_invitation_sound'),
            'auth' => $form->getInput('auth')
        );

        if (!$settings['chat_enabled']) {
            $settings['enable_osc'] = false;
        }

        $notificationSettings = new ilSetting('notifications');
        $notificationSettings->set('osd_polling_intervall', (string) ((int) $form->getInput('osd_intervall')));
        $notificationSettings->set('enable_osd', (string) ((bool) $form->getInput('enable_osd')));

        $chatSettings = new ilSetting('chatroom');
        $chatSettings->set('chat_enabled', (string) $settings['chat_enabled']);
        $chatSettings->set('enable_browser_notifications', (string) $settings['enable_browser_notifications']);
        $chatSettings->set('conversation_idle_state_in_minutes', (string) $convIdleStateTime);
        $chatSettings->set('enable_osc', (string) $settings['enable_osc']);
        $chatSettings->set('play_invitation_sound', (string) ((bool) $form->getInput('play_invitation_sound')));

        $adminSettings = new ilChatroomAdmin($this->gui->object->getId());
        $adminSettings->saveClientSettings((object) $settings);

        $fileHandler = new ilChatroomConfigFileHandler();
        $fileHandler->createClientConfigFile($settings);

        ilUtil::sendSuccess($this->ilLng->txt('settings_has_been_saved'), true);
        $this->ilCtrl->redirect($this->gui, 'view-clientsettings');
    }

    public function clientsettings(ilPropertyFormGUI $form = null) : void
    {
        $this->redirectIfNoPermission(['visible','read']);

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
        } else {
            $form->getItemByPostVar('auth')->setIsReadOnly(true);
        }
        $form->setFormAction($this->ilCtrl->getFormAction($this->gui, 'view-saveClientSettings'));

        $settingsTpl = $this->createSettingTemplate($form);
        $this->mainTpl->setVariable('ADM_CONTENT', $settingsTpl->get());
    }
}
