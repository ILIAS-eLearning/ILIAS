<?php

declare(strict_types=1);

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

class ilLoggerCronCleanErrorFiles extends ilCronJob
{
    protected const DEFAULT_VALUE_OLDER_THAN = 31;

    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected ilLoggingErrorSettings $error_settings;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("logging");
        $this->settings = new ilSetting('log');
        $this->error_settings = ilLoggingErrorSettings::getInstance();
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

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_IN_DAYS;
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

    public function run(): ilCronJobResult
    {
        $result = new ilCronJobResult();
        $folder = $this->error_settings->folder();
        if (!is_dir($folder)) {
            $result->setStatus(ilCronJobResult::STATUS_OK);
            $result->setMessage($this->lng->txt("log_error_path_not_configured_or_wrong"));
            return $result;
        }

        $offset = $this->settings->get('clear_older_then', '');
        if ($offset) {
            $offset = (int) $offset;
        } else {
            $offset = self::DEFAULT_VALUE_OLDER_THAN;
        }

        $files = $this->readLogDir($folder);
        $delete_date = new ilDateTime(date("Y-m-d"), IL_CAL_DATE);
        $delete_date->increment(ilDateTime::DAY, (-1 * $offset));

        foreach ($files as $file) {
            $file_date = date("Y-m-d", filemtime($this->error_settings->folder() . "/" . $file));

            if ($file_date <= $delete_date->get(IL_CAL_DATE)) {
                $this->deleteFile($this->error_settings->folder() . "/" . $file);
            }
        }

        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }

    protected function readLogDir(string $path): array
    {
        $ret = [];

        $folder = dir($path);
        while ($file_name = $folder->read()) {
            if (filetype($path . "/" . $file_name) != "dir") {
                $ret[] = $file_name;
            }
        }
        $folder->close();

        return $ret;
    }

    protected function deleteFile(string $path): void
    {
        unlink($path);
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form): void
    {
        $offset = $this->settings->get('clear_older_then', '');
        if (!$offset) {
            $offset = (string) self::DEFAULT_VALUE_OLDER_THAN;
        }

        $clear_older_then = new ilNumberInputGUI($this->lng->txt('frm_clear_older_then'), 'clear_older_then');
        $clear_older_then->allowDecimals(false);
        $clear_older_then->setMinValue(1, true);
        $clear_older_then->setValue($offset);
        $clear_older_then->setInfo($this->lng->txt('frm_clear_older_then_info'));

        $a_form->addItem($clear_older_then);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form): bool
    {
        $threshold = $a_form->getInput('clear_older_then');
        if ((string) $threshold === '') {
            $this->settings->delete('clear_older_then');
        } else {
            $this->settings->set('clear_older_then', (string) ((int) $a_form->getInput('clear_older_then')));
        }

        return true;
    }
}
