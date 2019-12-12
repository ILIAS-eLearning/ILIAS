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

    /**
     * @var \ilOerHarvesterSettings
     */
    private static $instance = null;

    /**
     * @var \ilSetting|null
     */
    private $storage = null;

    /**
     * @var int
     */
    private $target = 0;


    /**
     * @var string[]
     */
    private $copyright_templates = [];

    /**
     * @var ilCronOerHarvester
     */
    private $cronjob = null;



    /**
     * ilOerHarvesterSettings constructor.
     * @throws \LogicException
     */
    protected function __construct()
    {
        $this->storage = new ilSetting(self::STORAGE_IDENTIFIER);
        /*
        $this->cronjob = ilCronManager::getJobInstanceById(self::CRON_JOB_IDENTIFIER);
        if(!$this->cronjob instanceof ilCronJob) {

            throw new \LogicException(
                'Cannot create cron job instance'
            );
        }
        */
        $this->read();
    }



    /**
     * @return \ilOerHarvesterSettings
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof ilOerHarvesterSettings) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param string $a_type
     * @return bool
     */
    public function supportsHarvesting($a_type)
    {
        return in_array($a_type, self::COLLECTED_TYPES);
    }

    /**
     * Get obj types that support harvesing
     */
    public function getHarvestingTypes()
    {
        return self::COLLECTED_TYPES;
    }

    /**
     * @param int $a_target
     */
    public function setTarget($a_target)
    {
        $this->target = $a_target;
    }

    /**
     * Get target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param array $a_template_ids
     */
    public function setCopyrightTemplates(array $a_template_ids)
    {
        $this->copyright_templates = $a_template_ids;
    }

    /**
     * @return string[]
     */
    public function getCopyrightTemplates()
    {
        return $this->copyright_templates;
    }

    /**
     * @param $a_id
     * @return bool
     */
    public function isActiveCopyrightTemplate($a_id)
    {
        return in_array($a_id, $this->getCopyrightTemplates());
    }

    /**
     * Get copyright entries in LOM format: "il_copyright_entry_INST_ID_ID"
     * return string[]
     */
    public function getCopyRightTemplatesInLomFormat()
    {
        global $DIC;

        $settings = $DIC->settings();

        $lom_entries = [];
        foreach ($this->getCopyrightTemplates() as $copyright_id) {
            $lom_entries[] = 'il_copyright_entry__' . $settings->get('inst_id', 0) . '__' . $copyright_id;
        }
        return $lom_entries;
    }


    /**
     * Save settings
     */
    public function save()
    {
        $this->storage->set('target', $this->getTarget());
        $this->storage->set('templates', serialize($this->copyright_templates));
    }

    /**
     * Read settings
     */
    public function read()
    {
        $this->setTarget($this->storage->get('target', 0));
        $this->setCopyrightTemplates(unserialize($this->storage->get('templates', serialize([]))));
    }
}
