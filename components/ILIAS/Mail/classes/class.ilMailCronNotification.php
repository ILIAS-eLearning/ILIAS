<?php

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

declare(strict_types=1);

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\CronJob;

class ilMailCronNotification extends CronJob
{
    private GlobalHttpState $http;
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected bool $init_done = false;

    protected function init(): void
    {
        global $DIC;

        if (!$this->init_done) {
            $this->settings = $DIC->settings();
            $this->lng = $DIC->language();
            $this->http = $DIC->http();

            $this->init_done = true;
        }
    }

    public function getId(): string
    {
        return 'mail_notification';
    }

    public function getTitle(): string
    {
        $this->init();

        return $this->lng->txt('cron_mail_notification');
    }

    public function getDescription(): string
    {
        $this->init();

        $this->lng->loadLanguageModule('mail');

        return  sprintf(
            $this->lng->txt('cron_mail_notification_desc'),
            $this->lng->txt('mail_allow_external')
        );
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return null;
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return false;
    }

    public function hasCustomSettings(): bool
    {
        return true;
    }

    public function usesLegacyForms(): bool
    {
        return false;
    }

    public function getCustomConfigurationInput(
        \ILIAS\UI\Factory $ui_factory,
        \ILIAS\Refinery\Factory $factory,
        ilLanguage $lng
    ): \ILIAS\UI\Component\Input\Container\Form\FormInput {
        $status = $ui_factory
            ->input()
            ->field()
            ->checkbox($this->lng->txt('cron_mail_notification_message'))
            ->withByline($this->lng->txt('cron_mail_notification_message_info'))
            ->withValue((bool) $this->settings->get('mail_notification_message', '0'))
            ->withDedicatedName('mail_notification_message');

        return $status;
    }

    public function saveCustomConfiguration(mixed $form_data): void
    {
        $this->init();
        $this->settings->set(
            'mail_notification_message',
            (string) ((int) $form_data)
        );
    }

    public function run(): JobResult
    {
        $msn = new ilMailSummaryNotification();
        $msn->send();

        $result = new JobResult();
        $result->setStatus(JobResult::STATUS_OK);
        return $result;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form): bool
    {
        throw new RuntimeException('Not implemented');
    }

    public function activationWasToggled(ilDBInterface $db, ilSetting $setting, bool $a_currently_active): void
    {
        $setting->set('mail_notification', (string) ((int) $a_currently_active));
    }
}
