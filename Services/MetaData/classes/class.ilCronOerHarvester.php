<?php

declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron job for definition for oer harvesting
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCronOerHarvester extends ilCronJob
{
    public const CRON_JOB_IDENTIFIER = 'meta_oer_harvester';
    public const DEFAULT_SCHEDULE_VALUE = 1;

    private ilLogger $logger;
    private ilLanguage $lng;

    private ilOerHarvesterSettings $settings;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->meta();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');

        $this->settings = ilOerHarvesterSettings::getInstance();
    }

    public function getTitle(): string
    {
        return $this->lng->txt('meta_oer_harvester');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('meta_oer_harvester_desc');
    }

    public function getId(): string
    {
        return self::CRON_JOB_IDENTIFIER;
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return self::DEFAULT_SCHEDULE_VALUE;
    }

    public function hasCustomSettings(): bool
    {
        return true;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form): void
    {
        // target selection
        $target = new ilRepositorySelector2InputGUI(
            $this->lng->txt('meta_oer_target'),
            'target',
            false,
            $a_form
        );

        $explorer = $target->getExplorerGUI();
        $explorer->setSelectMode('target', false);
        $explorer->setRootId(ROOT_FOLDER_ID);
        $explorer->setTypeWhiteList(['cat']);

        if ($this->settings->getTarget()) {
            $explorer->setPathOpen($this->settings->getTarget());
            $target->setValue($this->settings->getTarget());
        }

        $target->setRequired(true);
        $a_form->addItem($target);

        // copyright selection
        $checkbox_group = new ilCheckboxGroupInputGUI(
            $this->lng->txt('meta_oer_copyright_selection'),
            'copyright'
        );
        $checkbox_group->setRequired(true);
        $checkbox_group->setValue($this->settings->getCopyrightTemplates());
        $checkbox_group->setInfo(
            $this->lng->txt('meta_oer_copyright_selection_info')
        );

        foreach (ilMDCopyrightSelectionEntry::_getEntries() as $copyright_entry) {
            $copyright_checkox = new ilCheckboxOption(
                $copyright_entry->getTitle(),
                (string) $copyright_entry->getEntryId(),
                $copyright_entry->getDescription()
            );
            $checkbox_group->addOption($copyright_checkox);
        }
        $a_form->addItem($checkbox_group);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form): bool
    {
        $this->settings->setTarget((int) $a_form->getInput('target'));
        $this->settings->setCopyrightTemplates($a_form->getInput('copyright'));
        $this->settings->save();

        return true;
    }

    public function run(): ilCronJobResult
    {
        $this->logger->info('Started cron oer harvester.');
        $harvester = new ilOerHarvester(new ilCronJobResult());
        $res = $harvester->run();
        $this->logger->info('cron oer harvester finished');

        return $res;
    }

    public function addToExternalSettingsForm(int $a_form_id, array &$a_fields, bool $a_is_active): void
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_META_COPYRIGHT:

                $a_fields['meta_oer_harvester'] =
                    (
                        $a_is_active ?
                        $this->lng->txt('enabled') :
                        $this->lng->txt('disabled')
                    );
                break;
        }
    }
}
