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

//NIFT: NotImplementedForTesting
trait ProgressRepoMockNIFT
{
    public function __construct()
    {
    }

    public function getByIds(int $prg_id, int $assignment_id): ilPRGProgress
    {
        throw new Exception("Not implemented for testing", 1);
    }
    /*public function getByPrgIdAndAssignmentId(int $prg_id, int $assignment_id)
    {
        throw new Exception("Not implemented for testing", 1);
    }*/
    public function getByPrgIdAndUserId(int $prg_id, int $usr_id): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getByPrgId(int $prg_id): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getFirstByPrgId(int $prg_id): void
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getExpiredSuccessfull(): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getRiskyToFailInstances(): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getLatestAssignedProgress(int $prg_id, int $usr_id): ?\ilPRGProgress
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getPassedDeadline(): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function delete(ilPRGProgress $progress)
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function createFor(
        ilStudyProgrammeSettings $prg,
        ilStudyProgrammeAssignment $ass
    ): ilPRGProgress {
        throw new Exception("Not implemented for testing", 1);
    }
}

trait AssignmentRepoMockNIFT
{
    public function __construct()
    {
    }
    public function createFor(int $prg_obj_id, int $usr_id, int $assigning_usr_id): ilPRGAssignment
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getForUser(int $usr_id): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getAllForNodeIsContained(int $prg_obj_id, ?array $user_filter = null, ?ilPRGAssignmentFilter $custom_filters = null): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getAllForSpecificNode(int $prg_obj_id, array $user_filter = null): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getPassedDeadline(\DateTimeImmutable $deadline): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getDashboardInstancesforUser(int $usr_id): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getAboutToExpire(
        array $programmes_and_due,
        bool $discard_formerly_notified = true
    ): array {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getByPrgId(int $prg_id): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getDueToRestart(): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getDueToManuelRestart(int $days_before_end): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function delete(ilPRGAssignment $assignment): void
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function deleteAllAssignmentsForProgrammeId(int $prg_obj_id): void
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getExpiredAndNotInvalidated(): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
}

trait SettingsRepoMockNIFT
{
    public function __construct()
    {
    }

    public function createFor(int $obj_id): ilStudyProgrammeSettings
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function delete(ilStudyProgrammeSettings $settings): void
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function loadByType(int $type_id): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function loadIdsByType(int $type_id): array
    {
        throw new Exception("Not implemented for testing", 1);
    }
}

class AssignmentRepoMock implements PRGAssignmentRepository
{
    use AssignmentRepoMockNIFT;
    /** @var array<int, ilStudyProgrammeAssignment> */
    public array $assignments = [];

    public function get(int $id): ilPRGAssignment
    {
        return $this->assignments[$id];
    }

    public function store(ilPRGAssignment $assignment): void
    {
        $this->assignments[$assignment->getId()] = $assignment;
    }
}

class SettingsRepoMock implements ilStudyProgrammeSettingsRepository
{
    use SettingsRepoMockNIFT;
    /** @var array<int, ilStudyProgrammeSettings> */
    public array $settings = [];

    public function get(int $obj_id): ilStudyProgrammeSettings
    {
        return $this->settings[$obj_id];
    }

    public function update(ilStudyProgrammeSettings $settings): void
    {
        $this->settings[$settings->getObjId()] = $settings;
    }
}

class SettingsMock extends ilStudyProgrammeSettings
{
    public function __construct(int $id)
    {
        $this->obj_id = $id;
        $this->validity_of_qualification_settings =
            new ilStudyProgrammeValidityOfAchievedQualificationSettings(
                null,
                null,
                null,
                false
            );
    }
}

class PrgMock extends ilObjStudyProgramme
{
    public function __construct(
        int $id,
        $env
    ) {
        $this->id = $id;
        $this->env = $env;
        $this->events = new class () {
            public function userSuccessful(ilPRGProgress $a_progress): void
            {
            }
        };
    }

    protected function throwIfNotInTree(): void
    {
    }

    public function update(): bool
    {
        return $this->updateSettings();// TODO PHP8-REVIEW Required parameter missing
    }
    protected function getLoggedInUserId(): int
    {
        return 9;
    }


    protected function getProgressIdString(ilPRGAssignment $assignment, ilPRGProgress $progress): string
    {
        return (string) $progress->getId();
    }

    protected function getAssignmentRepository(): ilPRGAssignmentDBRepository
    {
        return $this->env->assignment_repo;
    }
    protected function getSettingsRepository(): ilStudyProgrammeSettingsRepository
    {
        return $this->env->settings_repo;
    }

    protected function refreshLPStatus(int $usr_id, int $node_obj_id = null): void
    {
    }


    public function getParentProgress(ilPRGAssignment $assignment, int $child_progress_node_id): ?ilPRGProgress
    {
        $parent_id = $this->env->mock_tree[$progress->getNodeId()]['parent'];
        if (is_null($parent_id)) {
            return null;
        }
        return $this->getProgressRepository()->get($parent_id);
    }

    public function getChildrenProgress(ilPRGAssignment $assignment, int $progress_node_id): array
    {
        $progresses = [];
        foreach ($this->env->mock_tree[$progress->getNodeId()]['children'] as $child_id) {
            $progresses[] = $this->getProgressRepository()->get($child_id);
        }
        return $progresses;
    }

    public function testUpdateParentProgress(ilPRGProgress $progress): ilPRGProgress
    {
        return $this->updateParentProgress($progress);
    }

    public function testApplyProgressDeadline(ilPRGProgress $progress): ilPRGProgress
    {
        return $this->applyProgressDeadline($progress);
    }

    protected function getPrgInstanceByObjId(int $obj_id): ilObjStudyProgramme
    {
        return $this->tree[$obj_id]['prg'];
    }

    public function hasChildren(bool $include_references = false): bool
    {
        if ($this->id < 12) {
            return true;
        }
        return false;
    }
}

class ProgrammeEventsMock extends ilStudyProgrammeEvents
{
    public array $raised = [];// TODO PHP8-REVIEW Maybe the shape of the array can be expressed by PHPDoc comments

    public function __construct()
    {
    }

    public function raise($event, $parameter): void// TODO PHP8-REVIEW The type hints are missing
    {
        $this->raised[] = [$event, $parameter];
    }

    protected function getRefIdFor(int $obj_id): int
    {
        return 666;
    }
}
