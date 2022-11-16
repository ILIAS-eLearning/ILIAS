<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Mail\Autoresponder\AutoresponderDatabaseRepository;
use ILIAS\Mail\Autoresponder\AutoresponderRepository;
use ILIAS\Mail\Autoresponder\AutoresponderService;

/**
 * Class ilMailOptionsFormGUI
 */
class ilMailOptionsFormGUI extends ilPropertyFormGUI
{
    /** @var ilLanguage */
    protected $lng;

    /** @var ilSetting */
    protected $settings;

    /** @var ilObjUser */
    protected $user;

    /** @var ilCtrl */
    protected $ctrl;

    /** @var object */
    protected $parentGui;

    /** @var string */
    protected $positiveCmd = '';

    /** @var ilMailOptions */
    protected $options;

    /** @var AutoresponderRepository $auto_responder_repository*/
    protected $autoResponderRepository;

    /** @var int $default_auto_responder_absence_end_ts */
    private $default_auto_responder_absence_end_ts;

    /**
     * ilMailOptionsFormGUI constructor.
     * @param ilMailOptions $options
     * @param $parentGui
     * @param string $positiveCmd
     * @throws InvalidArgumentException
     */
    public function __construct(ilMailOptions $options, $parentGui, string $positiveCmd, AutoresponderRepository $autoResponderRepository = null)
    {
        global $DIC;

        if (!method_exists($parentGui, 'executeCommand')) {
            throw new InvalidArgumentException(sprintf(
                'Parameter $parentGui must be ilCtrl enabled by implementing executeCommand(), %s given.',
                is_object($parentGui) ? get_class($parentGui) : var_export($parentGui, 1)
            ));
        }

        parent::__construct();

        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC->settings();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();

        $this->options = $options;
        $this->parentGui = $parentGui;
        $this->positiveCmd = $positiveCmd;
        $this->autoResponderRepository = $autoResponderRepository ?? new AutoresponderDatabaseRepository($DIC->database());
        $this->default_auto_responder_absence_end_ts = time() + 8640;

        $this->init();
    }

    /**
     *
     */
    protected function init() : void
    {
        $this->setTitle($this->lng->txt('mail_settings'));
        $this->setFormAction($this->ctrl->getFormAction($this->parentGui, $this->positiveCmd));

        if ($this->settings->get('usr_settings_hide_mail_incoming_mail') != '1') {
            $incoming_mail_gui = new ilIncomingMailInputGUI($this->lng->txt('mail_incoming'), 'incoming_type', false);
            $this->addItem($incoming_mail_gui);
        }

        $options = array();
        for ($i = 50; $i <= 80; $i++) {
            $options[$i] = $i;
        }
        $si = new ilSelectInputGUI($this->lng->txt('linebreak'), 'linebreak');
        $si->setOptions($options);
        $this->addItem($si);

        $absence = new ilCheckboxInputGUI($this->lng->txt('mail_absence_status'), 'absence_status');
        $absence->setInfo($this->lng->txt('mail_absence_status_info'));
        $absence->setValue("1");
        $this->lng->loadLanguageModule('dateplaner');
        $duration = new ilDateDurationInputGUI($this->lng->txt('mail_absence_duration'), 'absence_duration');
        $duration->setRequired(true);
        $duration->setStartText($this->lng->txt('mail_absent_from'));
        $duration->setEndText($this->lng->txt('mail_absent_until'));
        $duration->setShowTime(true);
        $auto_responder_subject = new ilTextInputGUI($this->lng->txt('mail_absence_auto_responder_subject'), 'absence_auto_responder_subject');
        $auto_responder_subject->setMaxLength(200);
        $auto_responder_subject->setRequired(true);
        $auto_responder_body = new ilTextAreaInputGUI($this->lng->txt('mail_absence_auto_responder_body'), 'absence_auto_responder_body');
        $idle_time = (int) $this->settings->get('mail_auto_responder_idle_time', (string) AutoresponderService::AUTO_RESPONDER_DEFAULT_IDLE_TIME);
        if ($idle_time === 1) {
            $auto_responder_body->setInfo($this->lng->txt('mail_absence_auto_responder_body_info_single_day'));
        } else {
            $auto_responder_body->setInfo(sprintf($this->lng->txt('mail_absence_auto_responder_body_info'), $idle_time));
        }
        $auto_responder_body->setRequired(true);
        $auto_responder_body->setCols(60);
        $auto_responder_body->setRows(10);
        $absence->addSubItem($duration);
        $absence->addSubItem($auto_responder_subject);
        $absence->addSubItem($auto_responder_body);
        $this->addItem($absence);

        $ta = new ilTextAreaInputGUI($this->lng->txt('signature'), 'signature');
        $ta->setRows(10);
        $ta->setCols(60);
        $this->addItem($ta);

        if ($this->settings->get('mail_notification')) {
            $cb = new ilCheckboxInputGUI($this->lng->txt('cron_mail_notification'), 'cronjob_notification');
            $cb->setInfo($this->lng->txt('mail_cronjob_notification_info'));
            $cb->setValue(1);
            $this->addItem($cb);
        }

        $this->addCommandButton($this->positiveCmd, $this->lng->txt('save'));
    }

    /**
     * @return bool
     */
    public function save() : bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        if (
            $this->settings->get('usr_settings_hide_mail_incoming_mail') != '1' &&
            $this->settings->get('usr_settings_disable_mail_incoming_mail') != '1'
        ) {
            $incoming_type = (int) $this->getInput('incoming_type');

            $mail_address_option = $this->options->getEmailAddressMode();
            switch ($incoming_type) {
                case ilMailOptions::INCOMING_EMAIL:
                    $mail_address_option = (int) $this->getInput('mail_address_option');
                    break;

                case ilMailOptions::INCOMING_BOTH:
                    $mail_address_option = (int) $this->getInput('mail_address_option_both');
                    break;
            }
        } else {
            $incoming_type = $this->options->getIncomingType();
            $mail_address_option = $this->options->getEmailAddressMode();
        }

        $absence_duration = $this->getItemByPostVar('absence_duration');
        $absence_status = (bool) $this->getInput('absence_status');
        $old_absence_status = $this->options->getAbsenceStatus();
        if (!$absence_status && $old_absence_status) {
            $this->autoResponderRepository->deleteBySenderId($this->user->getId());
        }
        $this->options->setAbsenceStatus((bool) $this->getInput('absence_status'));
        if ($absence_duration && $absence_duration->getStart() && $absence_duration->getEnd()) {
            $this->options->setAbsentFrom($absence_duration->getStart()->get(IL_CAL_UNIX));
            $this->options->setAbsentUntil($absence_duration->getEnd()->get(IL_CAL_UNIX));
        }
        $this->options->setAbsenceAutoresponderSubject($this->getInput('absence_auto_responder_subject'));
        $this->options->setAbsenceAutoresponderBody($this->getInput('absence_auto_responder_body'));

        $this->options->setLinebreak((int) $this->getInput('linebreak'));
        $this->options->setSignature((string) $this->getInput('signature'));
        $this->options->setIsCronJobNotificationStatus((bool) $this->getInput('cronjob_notification'));
        $this->options->setIncomingType((int) $incoming_type);
        $this->options->setEmailAddressMode((int) $mail_address_option);

        $this->options->updateOptions();

        return true;
    }

    /**
     *
     */
    public function populate() : void
    {
        $data = [
            'linebreak' => $this->options->getLinebreak(),
            'signature' => $this->options->getSignature(),
            'cronjob_notification' => $this->options->isCronJobNotificationEnabled(),
            'absence_status' => $this->options->getAbsenceStatus(),
            'absence_duration' => [
                'start' => (new ilDateTime(($this->options->getAbsentFrom() ?: time()), IL_CAL_UNIX))->get(IL_CAL_DATETIME),
                'end' => (new ilDateTime(($this->options->getAbsentUntil() ?: $this->default_auto_responder_absence_end_ts), IL_CAL_UNIX))->get(IL_CAL_DATETIME),
            ],
            'absence_auto_responder_subject' => $this->options->getAbsenceAutoresponderSubject(),
            'absence_auto_responder_body' => $this->options->getAbsenceAutoresponderBody(),
        ];

        if ($this->settings->get('usr_settings_hide_mail_incoming_mail') != '1') {
            $data['incoming_type'] = $this->options->getIncomingType();

            $mail_address_option = $this->options->getEmailAddressMode();

            $data['mail_address_option'] = $mail_address_option;
            $data['mail_address_option_both'] = $mail_address_option;
        }

        $this->setValuesByArray($data);
    }
}
