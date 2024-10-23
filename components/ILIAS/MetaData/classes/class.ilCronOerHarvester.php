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

use ILIAS\Cron\Schedule\CronJobScheduleType;
use ILIAS\MetaData\OERHarvester\Initiator;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface;
use ILIAS\MetaData\OERHarvester\Results\Wrapper as ResultWrapper;

/**
 * Cron job for definition for oer harvesting
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCronOerHarvester extends ilCronJob
{
    protected const CRON_JOB_IDENTIFIER = 'meta_oer_harvester';
    protected const DEFAULT_SCHEDULE_VALUE = 1;

    private ilLogger $logger;
    private ilLanguage $lng;
    private Initiator $initiator;
    private SettingsInterface $settings;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->meta();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');

        $this->initiator = new Initiator($DIC);
        $this->settings = $this->initiator->settings();
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

    public function getDefaultScheduleType(): CronJobScheduleType
    {
        return CronJobScheduleType::SCHEDULE_TYPE_DAILY;
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
        $explorer->setRootId(ROOT_FOLDER_ID);
        $explorer->setTypeWhiteList(['cat']);

        $target_ref_id = $this->settings->getContainerRefIDForHarvesting();
        if ($target_ref_id) {
            $explorer->setPathOpen($target_ref_id);
            $target->setValue($target_ref_id);
        }

        $target->setRequired(true);
        $a_form->addItem($target);

        // source for exposing
        $ex_target = new ilRepositorySelector2InputGUI(
            $this->lng->txt('meta_oer_exposed_source'),
            'exposed_source',
            false,
            $a_form
        );

        $ex_explorer = $ex_target->getExplorerGUI();
        $ex_explorer->setRootId(ROOT_FOLDER_ID);
        $ex_explorer->setTypeWhiteList(['cat']);

        $ex_target_ref_id = $this->settings->getContainerRefIDForExposing();
        if ($ex_target_ref_id) {
            $ex_explorer->setPathOpen($ex_target_ref_id);
            $ex_target->setValue($ex_target_ref_id);
        }

        $ex_target->setRequired(true);
        $a_form->addItem($ex_target);

        // copyright selection
        $checkbox_group = new ilCheckboxGroupInputGUI(
            $this->lng->txt('meta_oer_copyright_selection'),
            'copyright'
        );
        $checkbox_group->setRequired(true);
        $checkbox_group->setValue($this->settings->getCopyrightEntryIDsSelectedForHarvesting());
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

        // object type selection
        $checkbox_group = new ilCheckboxGroupInputGUI(
            $this->lng->txt('meta_oer_object_type_selection'),
            'object_type'
        );
        $checkbox_group->setRequired(true);
        $checkbox_group->setValue($this->settings->getObjectTypesSelectedForHarvesting());

        foreach ($this->settings->getObjectTypesEligibleForHarvesting() as $type) {
            $copyright_checkox = new ilCheckboxOption(
                $this->lng->txt('objs_' . $type),
                $type
            );
            $checkbox_group->addOption($copyright_checkox);
        }
        $a_form->addItem($checkbox_group);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form): bool
    {
        $copyrights = [];
        foreach ($a_form->getInput('copyright') as $id) {
            $copyrights[] = (int) $id;
        }

        $this->settings->saveContainerRefIDForHarvesting((int) $a_form->getInput('target'));
        $this->settings->saveContainerRefIDForExposing((int) $a_form->getInput('exposed_source'));
        $this->settings->saveCopyrightEntryIDsSelectedForHarvesting(...$copyrights);
        $this->settings->saveObjectTypesSelectedForHarvesting(...$a_form->getInput('object_type'));
        return true;
    }

    public function run(): ilCronJobResult
    {
        $this->logger->info('Started cron oer harvester.');
        $harvester = $this->initiator->harvester();
        $res = $harvester->run(new ResultWrapper(new ilCronJobResult()));
        $this->logger->info('cron oer harvester finished');

        return $res->get();
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
