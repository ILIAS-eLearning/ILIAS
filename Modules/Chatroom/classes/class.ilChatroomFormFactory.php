<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Class ilChatroomFormFactory
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomFormFactory
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
    }

    /**
     * Applies given values to field in given form.
     * @param ilPropertyFormGUI $form
     * @param array             $values
     */
    public static function applyValues(ilPropertyFormGUI $form, array $values)
    {
        $form->setValuesByArray($values);
    }

    /**
     * Instantiates and returns ilPropertyFormGUI containing ilTextInputGUI
     * and ilTextAreaInputGUI
     * @deprecated replaced by default creation screens
     * @return ilPropertyFormGUI
     */
    public function getCreationForm()
    {
        $form = new ilPropertyFormGUI();
        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setRequired(true);
        $form->addItem($title);

        $description = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $form->addItem($description);

        return $this->addDefaultBehaviour($form);
    }

    /**
     * Adds 'create-save' and 'cancel' button to given $form and returns it.
     * @param ilPropertyFormGUI $form
     * @return ilPropertyFormGUI
     */
    private function addDefaultBehaviour(ilPropertyFormGUI $form)
    {
        $form->addCommandButton('create-save', $this->lng->txt('create'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    /**
     * @return ilPropertyFormGUI
     */
    public function getSettingsForm()
    {
        $this->lng->loadLanguageModule('rep');

        $form = new ilPropertyFormGUI();
        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setRequired(true);
        $form->addItem($title);

        $description = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $form->addItem($description);

        $cb = new ilCheckboxInputGUI($this->lng->txt('allow_anonymous'), 'allow_anonymous');
        $cb->setInfo($this->lng->txt('anonymous_hint'));

        $txt = new ilTextInputGUI($this->lng->txt('autogen_usernames'), 'autogen_usernames');
        $txt->setRequired(true);
        $txt->setInfo($this->lng->txt('autogen_usernames_info'));
        $cb->addSubItem($txt);
        $form->addItem($cb);

        $cb = new ilCheckboxInputGUI($this->lng->txt('allow_custom_usernames'), 'allow_custom_usernames');
        $form->addItem($cb);

        $cb_history = new ilCheckboxInputGUI($this->lng->txt('enable_history'), 'enable_history');
        $form->addItem($cb_history);

        $num_msg_history = new ilNumberInputGUI($this->lng->txt('display_past_msgs'), 'display_past_msgs');
        $num_msg_history->setInfo($this->lng->txt('hint_display_past_msgs'));
        $num_msg_history->setMinValue(0);
        $num_msg_history->setMaxValue(100);
        $form->addItem($num_msg_history);

        $cb = new ilCheckboxInputGUI($this->lng->txt('private_rooms_enabled'), 'private_rooms_enabled');
        $cb->setInfo($this->lng->txt('private_rooms_enabled_info'));
        $form->addItem($cb);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $form->addItem($section);

        $online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'online_status');
        $online->setInfo($this->lng->txt('chtr_activation_online_info'));
        $form->addItem($online);

        require_once 'Services/Form/classes/class.ilDateDurationInputGUI.php';
        $dur = new ilDateDurationInputGUI($this->lng->txt('rep_time_period'), 'access_period');
        $dur->setShowTime(true);
        $form->addItem($dur);

        $visible = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'access_visibility');
        $visible->setValue(1);
        $visible->setInfo($this->lng->txt('chtr_activation_limited_visibility_info'));
        $dur->addSubItem($visible);

        return $form;
    }

    /**
     * Prepares Fileupload form and returns it.
     * @return ilPropertyFormGUI
     */
    public function getFileUploadForm()
    {
        $form = new ilPropertyFormGUI();
        $file_input = new ilFileInputGUI();

        $file_input->setPostVar('file_to_upload');
        $file_input->setTitle($this->lng->txt('upload'));
        $form->addItem($file_input);
        $form->addCommandButton('UploadFile-uploadFile', $this->lng->txt('submit'));

        $form->setTarget('_blank');

        return $form;
    }

    /**
     * Returns period form.
     * @return ilPropertyFormGUI
     */
    public function getPeriodForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setPreventDoubleSubmission(false);

        require_once 'Services/Form/classes/class.ilDateDurationInputGUI.php';
        $duration = new ilDateDurationInputGUI($this->lng->txt('period'), 'timeperiod');

        $duration->setStartText($this->lng->txt('duration_from'));
        $duration->setEndText($this->lng->txt('duration_to'));
        $duration->setShowTime(true);
        $duration->setRequired(true);
        $form->addItem($duration);

        return $form;
    }

    /**
     * Returns chatname selection form.
     * @param array $name_options
     * @return ilPropertyFormGUI
     */
    public function getUserChatNameSelectionForm(array $name_options)
    {
        $form = new ilPropertyFormGUI();

        $radio = new ilRadioGroupInputGUI($this->lng->txt('select_custom_username'), 'custom_username_radio');

        foreach ($name_options as $key => $option) {
            $opt = new ilRadioOption($option, $key);
            $radio->addOption($opt);
        }

        $custom_opt = new ilRadioOption($this->lng->txt('custom_username'), 'custom_username');
        $radio->addOption($custom_opt);

        $txt = new ilTextInputGUI($this->lng->txt('custom_username'), 'custom_username_text');
        $custom_opt->addSubItem($txt);
        $form->addItem($radio);

        if ($this->user->isAnonymous()) {
            $radio->setValue('anonymousName');
        } else {
            $radio->setValue('fullname');
        }

        return $form;
    }

    /**
     * Returns session form with period set by given $sessions.
     * @param array $sessions
     * @return ilPropertyFormGUI
     */
    public function getSessionForm(array $sessions)
    {
        $form = new ilPropertyFormGUI();
        $form->setPreventDoubleSubmission(false);
        $list = new ilSelectInputGUI($this->lng->txt('session'), 'session');

        $options = array();

        foreach ($sessions as $session) {
            $start = new ilDateTime($session['connected'], IL_CAL_UNIX);
            $end = new ilDateTime($session['disconnected'], IL_CAL_UNIX);

            $options[$session['connected'] . ',' .
            $session['disconnected']] = ilDatePresentation::formatPeriod($start, $end);
        }

        $list->setOptions($options);
        $list->setRequired(true);

        $form->addItem($list);

        return $form;
    }

    /**
     * Returns general settings form.
     * @return ilPropertyFormGUI
     */
    public function getGeneralSettingsForm()
    {
        $form = new ilPropertyFormGUI();

        $address = new ilTextInputGUI($this->lng->txt('chatserver_address'), 'address');
        $address->setRequired(true);
        $form->addItem($address);

        $port = new ilNumberInputGUI($this->lng->txt('chatserver_port'), 'port');
        $port->setMinValue(1);
        $port->setMaxValue(65535);
        $port->setRequired(true);
        $port->setInfo($this->lng->txt('port_info'));
        $port->setSize(6);
        $form->addItem($port);

        $subDirectory = new ilTextInputGUI($this->lng->txt('chat_osc_no_sub_directory'), 'sub_directory');
        $subDirectory->setRequired(false);
        $subDirectory->setInfo($this->lng->txt('chat_osc_no_sub_directory_info'));
        $form->addItem($subDirectory);

        $protocol = new ilRadioGroupInputGUI($this->lng->txt('protocol'), 'protocol');
        $form->addItem($protocol);

        $http = new ilRadioOption($this->lng->txt('http'), 'http');
        $protocol->addOption($http);

        $https = new ilRadioOption($this->lng->txt('https'), 'https');
        $protocol->addOption($https);

        $certificate = new ilTextInputGUI($this->lng->txt('certificate'), 'cert');
        $certificate->setInfo($this->lng->txt('chat_https_cert_info'));
        $certificate->setRequired(true);
        $https->addSubItem($certificate);

        $key = new ilTextInputGUI($this->lng->txt('key'), 'key');
        $key->setInfo($this->lng->txt('chat_https_key_info'));
        $key->setRequired(true);
        $https->addSubItem($key);

        $dhparam = new ilTextInputGUI($this->lng->txt('dhparam'), 'dhparam');
        $dhparam->setInfo($this->lng->txt('chat_https_dhparam_info'));
        $dhparam->setRequired(true);
        $https->addSubItem($dhparam);

        $chatLog = new ilTextInputGUI($this->lng->txt('log'), 'log');
        $chatLog->setInfo($this->lng->txt('chat_log_info'));
        $chatLog->setRequired(false);
        $form->addItem($chatLog);

        $chatErrorLog = new ilTextInputGUI($this->lng->txt('error_log'), 'error_log');
        $chatErrorLog->setInfo($this->lng->txt('chat_error_log_info'));
        $chatErrorLog->setRequired(false);
        $form->addItem($chatErrorLog);

        $iliasSection = new ilFormSectionHeaderGUI();
        $iliasSection->setTitle($this->lng->txt('ilias_chatserver_connection'));
        $form->addItem($iliasSection);

        $iliasProxy = new ilCheckboxInputGUI($this->lng->txt('proxy'), 'ilias_proxy');
        $iliasProxy->setRequired(false);
        $iliasProxy->setInfo($this->lng->txt('ilias_proxy_info'));
        $form->addItem($iliasProxy);

        $chatServerILIASUrl = new ilTextInputGUI($this->lng->txt('url'), 'ilias_url');
        $chatServerILIASUrl->setRequired(true);
        $chatServerILIASUrl->setInfo($this->lng->txt('connection_url_info'));
        $iliasProxy->addSubItem($chatServerILIASUrl);

        $clientSection = new ilFormSectionHeaderGUI();
        $clientSection->setTitle($this->lng->txt('client_chatserver_connection'));
        $form->addItem($clientSection);

        $clientProxy = new ilCheckboxInputGUI($this->lng->txt('proxy'), 'client_proxy');
        $clientProxy->setRequired(false);
        $clientProxy->setInfo($this->lng->txt('client_proxy_info'));
        $form->addItem($clientProxy);

        $chatServerClientUrl = new ilTextInputGUI($this->lng->txt('url'), 'client_url');
        $chatServerClientUrl->setRequired(true);
        $chatServerClientUrl->setInfo($this->lng->txt('connection_url_info'));
        $clientProxy->addSubItem($chatServerClientUrl);
        
        $deletion_section = new ilFormSectionHeaderGUI();
        $deletion_section->setTitle($this->lng->txt('chat_deletion_section_head'));
        $form->addItem($deletion_section);

        $deletion_options = new ilRadioGroupInputGUI($this->lng->txt('chat_deletion_section_head'), 'deletion_mode');

        $deletion_mode_deactivated = new ilRadioOption($this->lng->txt('chat_deletion_disabled'), 0);
        $deletion_options->addOption($deletion_mode_deactivated);

        $chat_deletion_interval = new ilRadioOption($this->lng->txt('chat_deletion_interval'), 1);
        $chat_deletion_interval->setInfo($this->lng->txt('chat_deletion_interval_info'));
        $interval_unit = new ilSelectInputGUI($this->lng->txt('chat_deletion_interval_unit'), 'deletion_unit');
        $interval_unit->setRequired(true);
        $interval_unit->setOptions(array(
            'days' => $this->lng->txt('days'),
            'weeks' => $this->lng->txt('weeks'),
            'months' => $this->lng->txt('months'),
            'years' => $this->lng->txt('years'),
        ));
        $chat_deletion_interval->addSubItem($interval_unit);

        require_once 'Modules/Chatroom/classes/form/class.ilChatroomMessageDeletionThresholdInputGUI.php';
        $interval_value = new ilChatroomMessageDeletionThresholdInputGUI($this->lng->txt('chat_deletion_interval_value'), 'deletion_value', $interval_unit);
        $interval_value->allowDecimals(false);
        $interval_value->setMinValue(1);
        $interval_value->setRequired(true);
        $chat_deletion_interval->addSubItem($interval_value);

        $runAtTime = new ilTextInputGUI($this->lng->txt('chat_deletion_interval_run_at'), 'deletion_time');
        $runAtTime->setInfo($this->lng->txt('chat_deletion_interval_run_at_info'));
        $runAtTime->setRequired(true);
        $runAtTime->setValidationRegexp('/([01][0-9]|[2][0-3]):[0-5][0-9]/');
        $chat_deletion_interval->addSubItem($runAtTime);

        $deletion_options->addOption($chat_deletion_interval);

        $form->addItem($deletion_options);

        return $form;
    }

    /**
     * @return ilPropertyFormGUI
     */
    public function getClientSettingsForm()
    {
        $form = new ilPropertyFormGUI();

        $enable_chat = new ilCheckboxInputGUI($this->lng->txt('chat_enabled'), 'chat_enabled');
        $form->addItem($enable_chat);

        $enable_osc = new ilCheckboxInputGUI($this->lng->txt('chatroom_enable_osc'), 'enable_osc');
        $enable_osc->setInfo($this->lng->txt('chatroom_enable_osc_info'));
        $enable_chat->addSubItem($enable_osc);

        $osd = new ilCheckboxInputGUI($this->lng->txt('enable_osd'), 'enable_osd');
        $osd->setInfo($this->lng->txt('hint_osd'));
        $enable_chat->addSubItem($osd);

        $interval = new ilNumberInputGUI($this->lng->txt('osd_intervall'), 'osd_intervall');
        $interval->setMinValue(1);
        $interval->setRequired(true);
        $interval->setInfo($this->lng->txt('hint_osd_interval'));
        $osd->addSubItem($interval);

        $play_sound = new ilCheckboxInputGUI($this->lng->txt('play_invitation_sound'), 'play_invitation_sound');
        $play_sound->setInfo($this->lng->txt('play_invitation_sound_info'));
        $osd->addSubItem($play_sound);

        $enable_smilies = new ilCheckboxInputGUI($this->lng->txt('enable_smilies'), 'enable_smilies');
        $enable_smilies->setInfo($this->lng->txt('hint_enable_smilies'));
        $enable_chat->addSubItem($enable_smilies);

        $name = new \ilTextInputGUI($this->lng->txt('chatroom_client_name'), 'client_name');
        $name->setInfo($this->lng->txt('chatroom_client_name_info'));
        $name->setRequired(true);
        $name->setMaxLength(100);
        $enable_chat->addSubItem($name);

        require_once 'Modules/Chatroom/classes/class.ilChatroomAuthInputGUI.php';
        $auth = new ilChatroomAuthInputGUI($this->lng->txt('chatroom_auth'), 'auth');
        $auth->setInfo($this->lng->txt('chat_auth_token_info'));
        $auth->setCtrlPath(array('iladministrationgui', 'ilobjchatroomgui', 'ilpropertyformgui', 'ilformpropertydispatchgui', 'ilchatroomauthinputgui'));
        $auth->setRequired(true);
        $enable_chat->addSubItem($auth);

        return $form;
    }
}
