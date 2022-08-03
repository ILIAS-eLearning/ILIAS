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

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTICronOutcomeService extends ilCronJob
{
    private ilLanguage $lng;
    private ilCronJobRepository $cronRepo;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("lti");
        $this->cronRepo = $DIC->cron()->repository();
    }

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
        return $this->lng->txt('lti_cron_title');
    }

    public function getDescription() : string
    {
        return $this->lng->txt('lti_cron_title_desc');
    }

    public function run() : ilCronJobResult
    {
        $status = \ilCronJobResult::STATUS_NO_ACTION;

        $info = $this->cronRepo->getCronJobData($this->getId());
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
