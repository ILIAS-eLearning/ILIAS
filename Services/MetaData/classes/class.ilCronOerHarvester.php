<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron job for definition for oer harvesting
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilCronOerHarvester extends ilCronJob
{
    /**
     * @param string
     */
    const CRON_JOB_IDENTIFIER = 'meta_oer_harvester';

    /**
     * @param int
     */
    const DEFAULT_SCHEDULE_VALUE = 1;

    /**
     * @var \ilLogger
     */
    private $logger = null;

    /**
     * @var \ilLanguage
     */
    private $lng = null;

    /**
     * @var null
     */
    private $settings = null;


    /**
     * ilOerHarvester constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->meta();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');

        $this->settings = ilOerHarvesterSettings::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->lng->txt('meta_oer_harvester');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->lng->txt('meta_oer_harvester_desc');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return self::CRON_JOB_IDENTIFIER;
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
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScheduleValue()
    {
        return self::DEFAULT_SCHEDULE_VALUE;
    }


    /**
     * @inheritdoc
     */
    public function hasCustomSettings()
    {
        return true;
    }

    /**
     * @param ilPropertyFormGUI $a_form
     */
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
    {
        // target selection
        $target = new ilRepositorySelector2InputGUI(
            $this->lng->txt('meta_oer_target'),
            'target',
            false
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
                $copyright_entry->getEntryId(),
                $copyright_entry->getDescription()
            );
            $checkbox_group->addOption($copyright_checkox);
        }
        $a_form->addItem($checkbox_group);
        return $a_form;
    }


    /**
     * @param \ilPropertyFormGUI $a_form
     * @return bool|void
     */
    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        $this->settings->setTarget($a_form->getInput('target'));
        $this->settings->setCopyrightTemplates($a_form->getInput('copyright'));
        $this->settings->save();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->logger->info('Started cron oer harvester.');
        $harvester = new ilOerHarvester(new ilCronJobResult());
        $res = $harvester->run();
        $this->logger->info('cron oer harvester finished');

        return $res;
    }

    /**
     * Provide external settings for presentation in MD settings
     *
     * @param int $a_form_id
     * @param array $a_fields
     * @param bool $a_is_active
     */
    public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
    {
        #23901
        global $DIC;
        $lng = $DIC->language();

        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_META_COPYRIGHT:

                $a_fields['meta_oer_harvester'] =
                    (
                        $a_is_active ?
                        $lng->txt('enabled') :
                        $lng->txt('disabled')
                    );
                break;
        }
    }
}
