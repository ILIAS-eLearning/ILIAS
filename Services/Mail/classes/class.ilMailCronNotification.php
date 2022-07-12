<?php declare(strict_types=1);

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

use ILIAS\HTTP\GlobalHttpState;

/**
 * Mail notifications
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilMailCronNotification extends ilCronJob
{
    private GlobalHttpState $http;
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected bool $initDone = false;

    protected function init() : void
    {
        global $DIC;

        if (!$this->initDone) {
            $this->settings = $DIC->settings();
            $this->lng = $DIC->language();
            $this->http = $DIC->http();

            $this->initDone = true;
        }
    }

    public function getId() : string
    {
        return 'mail_notification';
    }

    public function getTitle() : string
    {
        $this->init();

        return $this->lng->txt('cron_mail_notification');
    }

    public function getDescription() : string
    {
        $this->init();

        $this->lng->loadLanguageModule('mail');

        return  sprintf(
            $this->lng->txt('cron_mail_notification_desc'),
            $this->lng->txt('mail_allow_external')
        );
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return null;
    }
    
    public function hasAutoActivation() : bool
    {
        return false;
    }

    public function hasFlexibleSchedule() : bool
    {
        return false;
    }

    public function hasCustomSettings() : bool
    {
        return true;
    }

    public function run() : ilCronJobResult
    {
        $msn = new ilMailSummaryNotification();
        $msn->send();

        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form) : void
    {
        $this->init();
        $cb = new ilCheckboxInputGUI(
            $this->lng->txt('cron_mail_notification_message'),
            'mail_notification_message'
        );
        $cb->setInfo($this->lng->txt('cron_mail_notification_message_info'));
        $cb->setChecked((bool) $this->settings->get('mail_notification_message', '0'));
        $a_form->addItem($cb);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form) : bool
    {
        $this->init();
        $this->settings->set(
            'mail_notification_message',
            (string) ($this->http->wrapper()->post()->has('mail_notification_message') ? 1 : 0)
        );
        return true;
    }

    public function activationWasToggled(ilDBInterface $db, ilSetting $setting, bool $a_currently_active) : void
    {
        $setting->set('mail_notification', (string) ((int) $a_currently_active));
    }
}
