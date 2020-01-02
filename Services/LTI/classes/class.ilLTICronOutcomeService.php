<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTICronOutcomeService extends ilCronJob
{
    /**
     * @inheritDoc
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
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return 'lti_outcome';
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
     * @return string
     */
    public function getTitle()
    {
        global $DIC;
        $DIC->language()->loadLanguageModule('lti');
        return $DIC->language()->txt('lti_cron_title');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        global $DIC;
        $DIC->language()->loadLanguageModule('lti');
        return $DIC->language()->txt('lti_cron_title_desc');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        global $DIC;

        $status = \ilCronJobResult::STATUS_NO_ACTION;

        $info = ilCronManager::getCronJobData($this->getId());
        $last_ts = $info['job_status_ts'];
        if (!$last_ts) {
            $last_ts = time() - 24 * 7 * 3600;
        }
        $since = new ilDateTime($last_ts, IL_CAL_UNIX);


        $result = new \ilCronJobResult();
        $result->setStatus($status);
        ilLTIAppEventListener::handleCronUpdate($since);
        $result->setStatus(ilCronJobResult::STATUS_OK);

        return $result;
    }
}
