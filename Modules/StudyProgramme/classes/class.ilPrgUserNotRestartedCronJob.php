<?php

declare(strict_types=1);

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
 * Inform a user, that her qualification is about to expire
 */
class ilPrgUserNotRestartedCronJob extends ilCronJob
{
    private const ID = 'prg_user_not_restarted';

    protected ilComponentLogger $log;
    protected ilLanguage $lng;
    protected ilPRGAssignmentDBRepository $assignment_repo;
    protected ilPrgCronJobAdapter $adapter;

    public function __construct()
    {
        global $DIC;
        $this->log = $DIC['ilLog'];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('prg');

        $dic = ilStudyProgrammeDIC::dic();
        $this->assignment_repo = $dic['repo.assignment'];
        $this->adapter = $dic['cron.notRestarted'];
    }

    public function getTitle(): string
    {
        return $this->lng->txt('prg_user_not_restarted_title');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('prg_user_not_restarted_desc');
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function hasAutoActivation(): bool
    {
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_IN_DAYS;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return 1;
    }

    public function run(): ilCronJobResult
    {
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);

        $programmes_to_send = $this->adapter->getRelevantProgrammeIds();
        if (count($programmes_to_send) == 0) {
            return $result;
        }

        $today = $this->getNow();
        $programmes_and_due = [];
        foreach ($programmes_to_send as $programme_obj_id => $days_offset_mail) {
            $interval = new DateInterval('P' . $days_offset_mail . 'D');
            $due = $today->add($interval);
            $programmes_and_due[$programme_obj_id] = $due;
        }

        $assignments = $this->assignment_repo->getAboutToExpire($programmes_and_due, true);

        if (count($assignments) == 0) {
            return $result;
        }
        foreach ($assignments as $ass) {
            $pgs = $ass->getProgressTree();
            $this->log(
                sprintf(
                    'PRG, UserNotRestarted: user %s\'s qualification is about to expire at assignment %s (prg obj_id %s)',
                    $ass->getUserId(),
                    $ass->getId(),
                    $pgs->getNodeId()
                )
            );
            $this->adapter->actOnSingleAssignment($ass);
            $this->assignment_repo->storeExpiryInfoSentFor($ass);
        }
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }

    protected function getNow(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    protected function log(string $msg): void
    {
        $this->log->write($msg);
    }
}
