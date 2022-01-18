<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron job for definition for oer harvesting
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilOerHarvesterSettings
{
    const CRON_JOB_IDENTIFIER = 'meta_oer_harvester';

    const STORAGE_IDENTIFIER = 'meta_oer';

    const COLLECTED_TYPES = [
        'file'
    ];

    private static ?ilOerHarvesterSettings $instance = null;

    protected ilSetting $storage;
    protected ilSetting $settings;


    private int $target = 0;


    /**
     * @var string[]
     */
    private array $copyright_templates = [];

    private ?ilCronOerHarvester $cronjob = null;




    protected function __construct()
    {
        global $DIC;

        $this->storage = new ilSetting(self::STORAGE_IDENTIFIER);
        $this->settings = $DIC->settings();

        $this->read();
    }




    public static function getInstance() : ilOerHarvesterSettings
    {
        if (!self::$instance instanceof ilOerHarvesterSettings) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function supportsHarvesting(string $a_type) : bool
    {
        return in_array($a_type, self::COLLECTED_TYPES);
    }

    /**
     * @return string[]
     */
    public function getHarvestingTypes() : array
    {
        return self::COLLECTED_TYPES;
    }


    public function setTarget(int $a_target) : void
    {
        $this->target = $a_target;
    }


    public function getTarget() : int
    {
        return $this->target;
    }

    /**
     * @param string[] $a_template_ids
     */
    public function setCopyrightTemplates(array $a_template_ids) : void
    {
        $this->copyright_templates = $a_template_ids;
    }

    /**
     * @return string[]
     */
    public function getCopyrightTemplates() : array
    {
        return $this->copyright_templates;
    }


    public function isActiveCopyrightTemplate(int $a_id) : bool
    {
        return in_array($a_id, $this->getCopyrightTemplates());
    }

    /**
     * Get copyright entries in LOM format: "il_copyright_entry_INST_ID_ID"
     * @return string[]
     */
    public function getCopyRightTemplatesInLomFormat() : array
    {

        $lom_entries = [];
        foreach ($this->getCopyrightTemplates() as $copyright_id) {
            $lom_entries[] = 'il_copyright_entry__' . $this->settings->get('inst_id', 0) . '__' . $copyright_id;
        }
        return $lom_entries;
    }



    public function save() : void
    {
        $this->storage->set('target', $this->getTarget());
        $this->storage->set('templates', serialize($this->copyright_templates));
    }


    public function read() : void
    {
        $this->setTarget($this->storage->get('target', 0));
        $this->setCopyrightTemplates(unserialize($this->storage->get('templates', serialize([]))));
    }
}
