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

namespace ILIAS\Init\ErrorHandling\Logging\CleanUp;

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\CronJob;
use ilLanguage;
use ilSetting;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Init\ErrorHandling\Logging\FileHandler;
use ILIAS\Init\ErrorHandling\Logging\SettingsInterface as ErrorLogSettingsInterface;
use ILIAS\Init\ErrorHandling\Logging\Settings as ErrorLogSettings;

class CleanUpOldErrorLogsJob extends CronJob
{
    private ilLanguage $lng;
    private FileHandler $file_handler;
    private Settings $settings;
    private ErrorLogSettingsInterface $error_log_settings;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("logging");

        $this->settings = new Settings(
            new ilSetting('log')
        );
        $this->error_log_settings = new ErrorLogSettings($DIC->iliasIni());
        $this->file_handler = new FileHandler();
    }

    public function getId(): string
    {
        return "log_error_file_cleanup";
    }

    public function getTitle(): string
    {
        return $this->lng->txt("log_error_file_cleanup_title");
    }

    public function getDescription(): string
    {
        return $this->lng->txt("log_error_file_cleanup_info");
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::IN_DAYS;
    }

    public function getDefaultScheduleValue(): int
    {
        return 10;
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function hasCustomSettings(): bool
    {
        return true;
    }

    public function run(): JobResult
    {
        $result = new JobResult();

        $error_log_directory = $this->error_log_settings->directory();
        if (!$this->file_handler->doesDirectoryExist($error_log_directory)) {
            $result->setStatus(JobResult::STATUS_OK);
            $result->setMessage($this->lng->txt("log_error_path_not_configured_or_wrong"));
            return $result;
        }

        $deleted_files = $this->file_handler->deleteFilesOlderThan(
            $error_log_directory,
            $this->settings->deletionCutoffInDays()
        );

        if ($deleted_files > 0) {
            $result->setStatus(JobResult::STATUS_OK);
        } else {
            $result->setStatus(JobResult::STATUS_NO_ACTION);
        }
        return $result;
    }

    public function usesLegacyForms(): bool
    {
        return false;
    }

    public function getCustomConfigurationInput(
        UIFactory $ui_factory,
        Refinery $factory,
        ilLanguage $lng
    ): FormInput {
        $lng->loadLanguageModule("logging");

        return $ui_factory->input()->field()
            ->numeric($this->lng->txt('frm_clear_older_then'), $this->lng->txt('frm_clear_older_then_info'))
            ->withAdditionalTransformation($factory->int()->isGreaterThanOrEqual(1))
            ->withValue($this->settings->deletionCutoffInDays());
    }

    public function saveCustomConfiguration(mixed $form_data): void
    {
        $this->settings->saveDeletionCutoff((int) $form_data);
    }
}
