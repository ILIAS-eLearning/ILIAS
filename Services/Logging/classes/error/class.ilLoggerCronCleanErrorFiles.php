<?php

require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Logging/classes/error/class.ilLoggingErrorSettings.php");
require_once("Services/Administration/classes/class.ilSetting.php");
require_once("Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
require_once("Services/Form/classes/class.ilTextInputGUI.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");


class ilLoggerCronCleanErrorFiles extends ilCronJob
{
    public function __construct()
    {
        global $DIC;

        $lng = $DIC['lng'];

        $this->lng = $lng;
        $this->lng->loadLanguageModule("logging");
        $this->settings = new ilSetting('log');
        $this->error_settings = ilLoggingErrorSettings::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return "log_error_file_cleanup";
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->lng->txt("log_error_file_cleanup_title");
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->lng->txt("log_error_file_cleanup_info");
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_IN_DAYS;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScheduleValue()
    {
        return 10;
    }

    /**
     * @inheritdoc
     */
    public function hasAutoActivation()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function hasCustomSettings()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $result = new ilCronJobResult();
        $folder = $this->error_settings->folder();
        if (!is_dir($folder)) {
            $result->setStatus(ilCronJobResult::STATUS_OK);
            $result->setMessage($this->lng->txt("log_error_path_not_configured_or_wrong"));
            return $result;
        }

        $files = $this->readLogDir($folder);
        $delete_date = new ilDateTime(date("Y-m-d"), IL_CAL_DATE);
        $delete_date->increment(ilDateTime::DAY, (-1 * $this->settings->get('clear_older_then')));

        foreach ($files as $file) {
            $file_date = date("Y-m-d", filemtime($this->error_settings->folder() . "/" . $file));

            if ($file_date <= $delete_date->get(IL_CAL_DATE)) {
                $this->deleteFile($this->error_settings->folder() . "/" . $file);
            }
        }

        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }

    protected function readLogDir($path)
    {
        $ret = array();

        $folder = dir($path);
        while ($file_name = $folder->read()) {
            if (filetype($path . "/" . $file_name) != "dir") {
                $ret[] = $file_name;
            }
        }
        $folder->close();

        return $ret;
    }

    protected function deleteFile($path)
    {
        unlink($path);
    }

    /**
     * @inheritdoc
     */
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
    {
        $clear_older_then = new ilTextInputGUI($this->lng->txt('frm_clear_older_then'), 'clear_older_then');
        $clear_older_then->setValue($this->settings->get('clear_older_then'));
        $clear_older_then->setInfo($this->lng->txt('frm_clear_older_then_info'));

        $a_form->addItem($clear_older_then);
    }

    /**
     * @param ilPropertyFormGUI $a_form
     * @return bool
     */
    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        $this->settings->set('clear_older_then', $a_form->getInput('clear_older_then'));
        return true;
    }
}
