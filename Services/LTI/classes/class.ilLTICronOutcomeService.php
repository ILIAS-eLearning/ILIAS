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
    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return 1;
    }

    public function getId() : string
    {
        return 'lti_outcome';
    }

    public function hasAutoActivation() : bool
    {
        return false;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    public function getTitle() : string
    {
        global $DIC;
        $DIC->language()->loadLanguageModule('lti');
        return $DIC->language()->txt('lti_cron_title');
    }

    public function getDescription() : string
    {
        global $DIC;
        $DIC->language()->loadLanguageModule('lti');
        return $DIC->language()->txt('lti_cron_title_desc');
    }

    public function run() : ilCronJobResult
    {
        global $DIC;

        $status = \ilCronJobResult::STATUS_NO_ACTION;

        $info = ilCronManager::getCronJobData($this->getId());
        $last_ts = $info['job_status_ts'];
        if (!$last_ts) {
            $last_ts = time() - 24 * 3600;
        }
        $since = new ilDateTime($last_ts, IL_CAL_UNIX);


        $result = new \ilCronJobResult();
        $result->setStatus($status);
        ilLTIAppEventListener::handleCronUpdate($since);
        $result->setStatus(ilCronJobResult::STATUS_OK);

        return $result;
    }
}
