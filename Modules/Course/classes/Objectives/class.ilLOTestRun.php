<?php
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
 * Stores current objective, questions and max points
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOTestRun
{
    protected int $container_id = 0;
    protected int $user_id = 0;
    protected int $test_id = 0;
    protected int $objective_id = 0;
    protected int $max_points = 0;
    protected array $questions = array();

    protected ilDBInterface $db;

    public function __construct(int $a_crs_id, int $a_user_id, int $a_test_id, int $a_objective_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->container_id = $a_crs_id;
        $this->user_id = $a_user_id;
        $this->test_id = $a_test_id;
        $this->objective_id = $a_objective_id;

        $this->read();
    }

    public static function lookupRunExistsForObjective(int $a_test_id, int $a_objective_id, int $a_user_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT * FROM loc_tst_run ' .
            'WHERE test_id = ' . $ilDB->quote($a_test_id, 'integer') . ' ' .
            'AND objective_id = ' . $ilDB->quote($a_objective_id, 'integer') . ' ' .
            'AND user_id = ' . $ilDB->quote($a_user_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }

    public static function deleteRuns(int $a_container_id, int $a_user_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = 'DELETE FROM loc_tst_run ' .
            'WHERE container_id = ' . $ilDB->quote($a_container_id, 'integer') . ' ' .
            'AND user_id = ' . $ilDB->quote($a_user_id, 'integer') . ' ';
        $ilDB->manipulate($query);
    }

    public static function deleteRun(int $a_container_id, int $a_user_id, int $a_test_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'DELETE FROM loc_tst_run ' .
            'WHERE container_id = ' . $ilDB->quote($a_container_id, 'integer') . ' ' .
            'AND user_id = ' . $ilDB->quote($a_user_id, 'integer') . ' ' .
            'AND test_id = ' . $ilDB->quote($a_test_id, 'integer') . ' ';
        $ilDB->manipulate($query);
    }

    public static function lookupObjectives(int $a_container_id, int $a_user_id, int $a_test_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT objective_id FROM loc_tst_run ' .
            'WHERE container_id = ' . $ilDB->quote($a_container_id, 'integer') . ' ' .
            'AND user_id = ' . $ilDB->quote($a_user_id, 'integer') . ' ' .
            'AND test_id = ' . $ilDB->quote($a_test_id, 'integer');
        $res = $ilDB->query($query);
        $objectives = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $objectives[] = $row->objective_id;
        }
        return $objectives;
    }

    /**
     * @return \ilLOTestRun[]
     */
    public static function getRun(int $a_container_id, int $a_user_id, int $a_test_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT objective_id FROM loc_tst_run ' .
            'WHERE container_id = ' . $ilDB->quote($a_container_id, 'integer') . ' ' .
            'AND user_id = ' . $ilDB->quote($a_user_id, 'integer') . ' ' .
            'AND test_id = ' . $ilDB->quote($a_test_id, 'integer') . ' ';
        $res = $ilDB->query($query);

        $run = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $run[] = new ilLOTestRun($a_container_id, $a_user_id, $a_test_id, $row->objective_id);
        }
        return $run;
    }

    public function getContainerId(): int
    {
        return $this->container_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }

    public function getObjectiveId(): int
    {
        return $this->objective_id;
    }

    public function getMaxPoints(): int
    {
        return $this->max_points;
    }

    public function setMaxPoints(int $a_points): void
    {
        $this->max_points = $a_points;
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function clearQuestions(): void
    {
        $this->questions = array();
    }

    public function addQuestion(int $a_id): void
    {
        $this->questions[$a_id] = 0;
    }

    public function questionExists(int $a_question_id): bool
    {
        return array_key_exists($a_question_id, $this->questions);
    }

    public function setQuestionResult(int $a_qst_id, int $a_points): void
    {
        $this->questions[$a_qst_id] = $a_points;
    }

    public function getResult(): array
    {
        $sum_points = 0;
        foreach ($this->questions as $points) {
            $sum_points += $points;
        }

        $percentage =
            ($this->getMaxPoints() > 0) ?
                ($sum_points / $this->getMaxPoints() * 100) :
                100;

        return array(
            'max' => $this->getMaxPoints(),
            'reached' => $sum_points,
            'percentage' => $percentage
        );
    }

    public function delete(): void
    {
        $query = 'DELETE FROM loc_tst_run ' .
            'WHERE container_id = ' . $this->db->quote($this->getContainerId(), 'integer') . ' ' .
            'AND user_id = ' . $this->db->quote($this->getUserId(), 'integer') . ' ' .
            'AND test_id = ' . $this->db->quote($this->getTestId(), 'integer') . ' ' .
            'AND objective_id = ' . $this->db->quote($this->getObjectiveId(), 'integer');
        $this->db->manipulate($query);
    }

    public function create(): void
    {
        $query = 'INSERT INTO loc_tst_run ' .
            '(container_id, user_id, test_id, objective_id,max_points,questions) ' .
            'VALUES( ' .
            $this->db->quote($this->getContainerId(), 'integer') . ', ' .
            $this->db->quote($this->getUserId(), 'integer') . ', ' .
            $this->db->quote($this->getTestId(), 'integer') . ', ' .
            $this->db->quote($this->getObjectiveId(), 'integer') . ', ' .
            $this->db->quote($this->getMaxPoints(), 'integer') . ', ' .
            $this->db->quote(serialize($this->getQuestions()), 'text') . ' ' .
            ')';
        $this->db->manipulate($query);
    }

    public function update(): void
    {
        $query = 'UPDATE loc_tst_run SET ' .
            'max_points = ' . $this->db->quote($this->getMaxPoints(), 'integer') . ', ' .
            'questions = ' . $this->db->quote(serialize($this->getQuestions()), 'text') . ' ' .
            'WHERE container_id = ' . $this->db->quote($this->container_id, 'integer') . ' ' .
            'AND user_id = ' . $this->db->quote($this->getUserId(), 'integer') . ' ' .
            'AND test_id = ' . $this->db->quote($this->getTestId(), 'integer') . ' ' .
            'AND objective_id = ' . $this->db->quote($this->getObjectiveId(), 'integer') . ' ';
        $this->db->manipulate($query);
    }

    public function read(): void
    {
        $query = 'SELECT * FROM loc_tst_run ' .
            'WHERE container_id = ' . $this->db->quote($this->getContainerId(), 'integer') . ' ' .
            'AND user_id = ' . $this->db->quote($this->getUserId(), 'integer') . ' ' .
            'AND test_id = ' . $this->db->quote($this->getTestId(), 'integer') . ' ' .
            'AND objective_id = ' . $this->db->quote($this->getObjectiveId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->max_points = $row->max_points;
            if ($row->questions) {
                $this->questions = unserialize($row->questions);
            }
        }
    }
}
