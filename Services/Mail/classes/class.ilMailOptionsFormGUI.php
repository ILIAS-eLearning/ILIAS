<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Mail/classes/class.ilMailOptions.php';

/**
 * Class ilMailOptionsFormGUI
 */
class ilMailOptionsFormGUI extends \ilPropertyFormGUI
{
    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilSetting
     */
    protected $settings;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var
     */
    protected $parentGui;

    /**
     * @var string
     */
    protected $positiveCmd = '';

    /**
     * @var ilMailOptions
     */
    protected $options;

    /**
     * ilMailOptionsFormGUI constructor.
     * @param ilMailOptions $options
     * @param $parentGui
     * @param string $positiveCmd
     * @throws \InvalidArgumentException
     */
    public function __construct(ilMailOptions $options, $parentGui, $positiveCmd)
    {
        global $DIC;

        if (!method_exists($parentGui, 'executeCommand')) {
            throw new \InvalidArgumentException(sprintf(
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

        $this->init();
    }

    /**
     *
     */
    protected function init()
    {
        $this->setTitle($this->lng->txt('mail_settings'));
        $this->setFormAction($this->ctrl->getFormAction($this->parentGui, $this->positiveCmd));

        if ($this->settings->get('usr_settings_hide_mail_incoming_mail') != '1') {
            require_once 'Services/Mail/classes/Form/class.ilIncomingMailInputGUI.php';
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
    public function save()
    {
        if (!$this->checkInput()) {
            return false;
        }

        if (
            $this->settings->get('usr_settings_hide_mail_incoming_mail') != '1' &&
            $this->settings->get('usr_settings_disable_mail_incoming_mail') != '1'
        ) {
            $incoming_type = (int) $this->getInput('incoming_type');

            $mail_address_option = $this->options->getMailAddressOption();
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
            $mail_address_option = $this->options->getMailAddressOption();
        }

        $this->options->setLinebreak((int) $this->getInput('linebreak'));
        $this->options->setSignature($this->getInput('signature'));
        $this->options->setCronjobNotification((int) $this->getInput('cronjob_notification'));
        $this->options->setIncomingType($incoming_type);
        $this->options->setMailAddressOption($mail_address_option);

        $this->options->updateOptions();

        return true;
    }

    /**
     *
     */
    public function populate()
    {
        $data = array(
            'linebreak' => $this->options->getLinebreak(),
            'signature' => $this->options->getSignature(),
            'cronjob_notification' => $this->options->getCronjobNotification()
        );

        if ($this->settings->get('usr_settings_hide_mail_incoming_mail') != '1') {
            $data['incoming_type'] = $this->options->getIncomingType();

            $mail_address_option = $this->options->getMailAddressOption();

            $data['mail_address_option'] = $mail_address_option;
            $data['mail_address_option_both'] = $mail_address_option;
        }

        $this->setValuesByArray($data);
    }
}
