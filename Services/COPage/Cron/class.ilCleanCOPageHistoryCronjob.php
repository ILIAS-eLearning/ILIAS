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

use ILIAS\COPage\History\HistoryManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCleanCOPageHistoryCronjob extends ilCronJob
{
    protected HistoryManager $history_manager;
    protected ilSetting $settings;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->history_manager = $DIC
            ->copage()
            ->internal()
            ->domain()
            ->history();
    }

    public function getId() : string
    {
        return "copg_history_cleanup";
    }

    public function getTitle() : string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("copg");
        return $lng->txt("copg_history_cleanup_cron");
    }

    public function getDescription() : string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("copg");
        return $lng->txt("copg_history_cleanup_cron_info");
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
        global $DIC;

        $log = ilLoggerFactory::getLogger("copg");
        $log->debug("----- Delete old page history entries, Start -----");

        $status = ilCronJobResult::STATUS_NO_ACTION;
        $result = new ilCronJobResult();

        $x_days = $this->getCronDays();
        $keep_entries = $this->getKeepEntries();
        $log->debug("... $x_days days, keep $keep_entries");

        if ($this->history_manager->deleteOldHistoryEntries($x_days, $keep_entries)) {
            $status = ilCronJobResult::STATUS_OK;
        }

        $log->debug("----- Delete old page history entries, End -----");

        $result->setStatus($status);
        return $result;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form) : void
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("copg");

        $ti = new ilNumberInputGUI(
            $lng->txt("copg_cron_days"),
            "copg_cron_days"
        );
        $ti->setSize(6);
        $ti->setSuffix($lng->txt("copg_days"));
        $ti->setInfo($lng->txt("copg_cron_days_info"));
        $ti->setValue((string) $this->getCronDays());
        $a_form->addItem($ti);

        $ti = new ilNumberInputGUI($lng->txt("copg_cron_keep_entries"), "copg_cron_keep_entries");
        $ti->setSize(6);
        $ti->setSuffix($lng->txt("copg_entries"));
        $ti->setInfo($lng->txt("copg_cron_keep_entries_info"));
        $ti->setValue((string) $this->getKeepEntries());
        $a_form->addItem($ti);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form) : bool
    {
        $this->setCronDays((int) $a_form->getInput("copg_cron_days"));
        $this->setKeepEntries((int) $a_form->getInput("copg_cron_keep_entries"));

        return true;
    }

    protected function getCronDays() : int
    {
        $settings = $this->settings;
        return (int) $settings->get("copg_cron_days", "3600");
    }

    protected function setCronDays(int $days) : void
    {
        $settings = $this->settings;
        $settings->set("copg_cron_days", (string) $days);
    }

    protected function getKeepEntries() : int
    {
        $settings = $this->settings;
        return (int) $settings->get("copg_cron_keep_entries", "1000");
    }

    protected function setKeepEntries(int $entries) : void
    {
        $settings = $this->settings;
        $settings->set("copg_cron_keep_entries", (string) $entries);
    }
}
