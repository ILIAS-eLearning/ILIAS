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

//NIFT: NotImplementedForTesting
trait ProgressRepoMockNIFT
{
    public function __construct()
    {
    }

    public function getByIds(int $prg_id, int $assignment_id) : ilStudyProgrammeProgress
    {
        throw new Exception("Not implemented for testing", 1);
    }
    /*public function getByPrgIdAndAssignmentId(int $prg_id, int $assignment_id)
    {
        throw new Exception("Not implemented for testing", 1);
    }*/
    public function getByPrgIdAndUserId(int $prg_id, int $usr_id) : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getByPrgId(int $prg_id) : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getFirstByPrgId(int $prg_id) : void
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getExpiredSuccessfull() : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getRiskyToFailInstances() : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getPassedDeadline() : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function delete(ilStudyProgrammeProgress $progress) : void
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function createFor(
        ilStudyProgrammeSettings $prg,
        ilStudyProgrammeAssignment $ass
    ) : ilStudyProgrammeProgress {
        throw new Exception("Not implemented for testing", 1);
    }
}

trait AssignmentRepoMockNIFT
{
    public function __construct()
    {
    }

    public function createFor(int $prg_id, int $usr_id, int $assigning_usr_id) : ilStudyProgrammeAssignment
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getByUsrId(int $usr_id) : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getByPrgId(int $prg_id) : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getDueToRestart() : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function getDueToManuelRestart(int $days_before_end) : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function delete(ilStudyProgrammeAssignment $assignment) : void
    {
        throw new Exception("Not implemented for testing", 1);
    }
}

trait SettingsRepoMockNIFT
{
    public function __construct()
    {
    }

    public function createFor(int $obj_id) : ilStudyProgrammeSettings
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function delete(ilStudyProgrammeSettings $settings) : void
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function loadByType(int $type_id) : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
    public function loadIdsByType(int $type_id) : array
    {
        throw new Exception("Not implemented for testing", 1);
    }
}


class ProgressRepoMock implements ilStudyProgrammeProgressRepository
{
    /** @var array<int, ilStudyProgrammeProgress> */
    public array $progresses = [];

    use ProgressRepoMockNIFT;

    public function get(int $id) : ilStudyProgrammeProgress
    {
        return $this->progresses[$id];
    }

    public function update(ilStudyProgrammeProgress $progress) : void
    {
        $this->progresses[$progress->getNodeId()] = $progress;
    }

    public function getByPrgIdAndAssignmentId(int $prg_id, int $assignment_id)
    {
        return $this->progresses[$prg_id];
    }

    public function getByAssignmentId(int $assignment_id) : array
    {
        $ret = [];
        foreach ($this->progresses as $progress_id => $progress) {
            if ($progress->getAssignmentId() === $assignment_id) {
                $ret[] = $progress;
            }
        }
        return $ret;
    }
}

class AssignmentRepoMock implements ilStudyProgrammeAssignmentRepository
{
    /** @var array<int, ilStudyProgrammeAssignment> */
    public array $assignments = [];

    use AssignmentRepoMockNIFT;

    public function get(int $id) : ?ilStudyProgrammeAssignment
    {
        return $this->assignments[$id];
    }
    public function update(ilStudyProgrammeAssignment $assignment) : void
    {
        $this->assignments[$assignment->getId()] = $assignment;
    }
}

class SettingsRepoMock implements ilStudyProgrammeSettingsRepository
{
    /** @var array<int, ilStudyProgrammeSettings> */
    public array $settings = [];

    use SettingsRepoMockNIFT;

    public function get(int $obj_id) : ilStudyProgrammeSettings
    {
        return $this->settings[$obj_id];
    }
    
    public function update(ilStudyProgrammeSettings $settings) : void
    {
        $this->settings[$settings->getObjId()] = $settings;
    }
}

class SettingsMock extends ilStudyProgrammeSettings
{
    public function __construct(int $id)
    {
        $this->obj_id = $id;
    }
}

class PrgMock extends ilObjStudyProgramme
{
    protected ProgressRepoMock $progress_repo;
    protected AssignmentRepoMock $assignment_repo;
    protected SettingsRepoMock $settings_repo;
    public array $mock_tree;

    public function __construct(
        int $id,
        ProgressRepoMock $progress_repo,
        AssignmentRepoMock $assignment_repo,
        SettingsRepoMock $settings_repo,
        array &$mock_tree
    ) {
        $this->id = $id;
        $this->progress_repo = $progress_repo;
        $this->assignment_repo = $assignment_repo;
        $this->settings_repo = $settings_repo;
        $this->mock_tree = &$mock_tree;
        $this->events = new class() extends ilStudyProgrammeEvents {
            public function __construct()
            {
            }
            public function userSuccessful(ilStudyProgrammeProgress $a_progress) : void
            {
            }
        };
    }
    
    protected function throwIfNotInTree() : void
    {
    }

    public function update() : bool
    {
        return $this->updateSettings();// TODO PHP8-REVIEW Required parameter missing
    }
    protected function getLoggedInUserId() : int
    {
        return 9;
    }
    
    protected function getProgressIdString(ilStudyProgrammeProgress $progress) : string
    {
        return (string) $progress->getId();
    }

    protected function getProgressRepository() : ilStudyProgrammeProgressRepository
    {
        return $this->progress_repo;
    }
    protected function getAssignmentRepository() : ilStudyProgrammeAssignmentRepository
    {
        return $this->assignment_repo;
    }
    protected function getSettingsRepository() : ilStudyProgrammeSettingsRepository
    {
        return $this->settings_repo;
    }

    protected function refreshLPStatus(int $usr_id, int $node_obj_id = null) : void
    {
    }

    public function getParentProgress(ilStudyProgrammeProgress $progress) : ?ilStudyProgrammeProgress
    {
        $parent_id = $this->mock_tree[$progress->getNodeId()]['parent'];
        if (is_null($parent_id)) {
            return null;
        }
        return $this->getProgressRepository()->get($parent_id);
    }

    public function getChildrenProgress($progress) : array
    {
        $progresses = [];
        foreach ($this->mock_tree[$progress->getNodeId()]['children'] as $child_id) {
            $progresses[] = $this->getProgressRepository()->get($child_id);
        }
        return $progresses;
    }

    public function testUpdateParentProgress(ilStudyProgrammeProgress $progress) : ilStudyProgrammeProgress
    {
        return $this->updateParentProgress($progress);
    }

    public function testApplyProgressDeadline(ilStudyProgrammeProgress $progress) : ilStudyProgrammeProgress
    {
        return $this->applyProgressDeadline($progress);
    }
    
    public function hasChildren(bool $include_references = false) : bool
    {
        return $this->id < 12;
    }

    protected function getPrgInstanceByObjId(int $obj_id) : ilObjStudyProgramme
    {
        return $this->mock_tree[$obj_id]['prg'];
    }
}

class ProgrammeEventsMock extends ilStudyProgrammeEvents
{
    public array $raised;// TODO PHP8-REVIEW Maybe the shape of the array can be expressed by PHPDoc comments
    
    public function __construct()
    {
        $this->raised = [];
    }

    public function raise($event, $parameter) : void// TODO PHP8-REVIEW The type hints are missing
    {
        $this->raised[] = [$event, $parameter];
    }
}
