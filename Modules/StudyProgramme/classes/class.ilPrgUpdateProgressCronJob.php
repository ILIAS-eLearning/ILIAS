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
 * This will set progresses to FAILED,
 * if they are past the deadline (and not successful, yet)
 */
class ilPrgUpdateProgressCronJob extends ilCronJob
{
    private const ID = 'prg_update_progress';

    protected Pimple\Container $dic;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('prg');
        $this->dic = ilStudyProgrammeDIC::dic();
    }

    public function getTitle() : string
    {
        return $this->lng->txt('prg_update_progress_title');
    }

    public function getDescription() : string
    {
        return $this->lng->txt('prg_update_progress_description');
    }

    public function getId() : string
    {
        return self::ID;
    }

    public function hasAutoActivation() : bool
    {
        return true;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_IN_DAYS;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return 1;
    }

    public function run() : ilCronJobResult
    {
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);
        $acting_user = $this->getActingUserId();
        foreach ($this->getProgressRepository()->getPassedDeadline() as $progress) {
            if ($progress->getStatus() === ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
                $programme = ilObjStudyProgramme::getInstanceByObjId($progress->getNodeId());
                $programme->markFailed($progress->getId(), $acting_user);
                $result->setStatus(ilCronJobResult::STATUS_OK);
            }
        }
        return $result;
    }

    protected function getProgressRepository() : ilStudyProgrammeProgressDBRepository
    {
        return $this->dic['ilStudyProgrammeUserProgressDB'];
    }

    protected function getActingUserId() : int
    {
        return $this->dic['current_user']->getId();
    }
}
