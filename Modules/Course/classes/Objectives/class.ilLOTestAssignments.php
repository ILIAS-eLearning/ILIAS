<?php declare(strict_types=0);
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
 * Settings for LO courses
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilLOTestAssignments
{
    private static array $instances = [];

    private int $container_id = 0;
    /**
     * @var ilLOTestAssignment[]
     */
    private array $assignments = [];
    private ilLOSettings $settings;

    protected ilDBInterface $db;

    public function __construct(int $a_container_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->container_id = $a_container_id;
        $this->settings = ilLOSettings::getInstanceByObjId($a_container_id);
        $this->readTestAssignments();
    }

    public static function getInstance(int $a_container_id) : self
    {
        if (isset(self::$instances[$a_container_id])) {
            return self::$instances[$a_container_id];
        }
        return self::$instances[$a_container_id] = new self($a_container_id);
    }

    public static function lookupContainerForTest(int $a_test_ref_id) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT container_id FROM loc_tst_assignments ' .
            'WHERE tst_ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->container_id;
        }
        return 0;
    }

    public function getContainerId() : int
    {
        return $this->container_id;
    }

    public function getSettings() : ilLOSettings
    {
        return $this->settings;
    }

    /**
     * Get assignments
     * @return ilLOTestAssignment[]
     */
    public function getAssignments() : array
    {
        return $this->assignments;
    }

    public static function deleteByContainer(int $a_container_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'DELETE FROM loc_tst_assignments ' .
            'WHERE container_id = ' . $ilDB->quote($a_container_id, 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * Get assignments by type
     * @return ilLOTestAssignment[]
     */
    public function getAssignmentsByType(int $a_type) : array
    {
        $by_type = array();
        foreach ($this->assignments as $assignment) {
            if ($assignment->getAssignmentType() === $a_type) {
                $by_type[] = $assignment;
            }
        }
        return $by_type;
    }

    /**
     * @return int[]
     */
    public function getTests() : array
    {
        $tests = array();
        if ($this->getSettings()->getInitialTest()) {
            $tests[] = $this->getSettings()->getInitialTest();
        }
        if ($this->getSettings()->getQualifiedTest()) {
            $tests[] = $this->getSettings()->getQualifiedTest();
        }
        foreach ($this->assignments as $assignment) {
            $tests[] = $assignment->getTestRefId();
        }
        return $tests;
    }

    public function getTestByObjective(int $a_objective_id, int $a_type) : int
    {
        switch ($a_type) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                if (!$this->getSettings()->hasSeparateInitialTests()) {
                    return $this->getSettings()->getInitialTest();
                }
                break;

            case ilLOSettings::TYPE_TEST_QUALIFIED:
                if (!$this->getSettings()->hasSeparateQualifiedTests()) {
                    return $this->getSettings()->getQualifiedTest();
                }
                break;
        }

        $assignment = $this->getAssignmentByObjective($a_objective_id, $a_type);
        if ($assignment) {
            return $assignment->getTestRefId();
        }
        return 0;
    }

    public function isSeparateTest(int $a_test_ref_id) : bool
    {
        if (!$this->getSettings()->hasSeparateInitialTests()) {
            if ($this->getSettings()->getInitialTest() == $a_test_ref_id) {
                return false;
            }
        }
        if (!$this->getSettings()->hasSeparateQualifiedTests()) {
            if ($this->getSettings()->getQualifiedTest() == $a_test_ref_id) {
                return false;
            }
        }
        return true;
    }

    public function getTypeByTest(int $a_test_ref_id) : int
    {
        if ($this->getSettings()->worksWithInitialTest() && !$this->getSettings()->hasSeparateInitialTests()) {
            if ($this->getSettings()->getInitialTest() == $a_test_ref_id) {
                return ilLOSettings::TYPE_TEST_INITIAL;
            }
        } elseif ($this->getSettings()->worksWithInitialTest()) {
            foreach ($this->assignments as $assignment) {
                if ($assignment->getTestRefId() == $a_test_ref_id) {
                    return ilLOSettings::TYPE_TEST_INITIAL;
                }
            }
        }
        if (!$this->getSettings()->hasSeparateQualifiedTests()) {
            if ($this->getSettings()->getQualifiedTest() == $a_test_ref_id) {
                return ilLOSettings::TYPE_TEST_QUALIFIED;
            }
        } else {
            foreach ($this->assignments as $assignment) {
                if ($assignment->getTestRefId() == $a_test_ref_id) {
                    return ilLOSettings::TYPE_TEST_QUALIFIED;
                }
            }
        }
        return ilLOSettings::TYPE_TEST_UNDEFINED;
    }

    public function getAssignmentByObjective(int $a_objective_id, int $a_type) : ?ilLOTestAssignment
    {
        foreach ($this->assignments as $assignment) {
            if (
                ($assignment->getObjectiveId() === $a_objective_id) &&
                ($assignment->getAssignmentType() === $a_type)
            ) {
                return $assignment;
            }
        }
        return null;
    }

    protected function readTestAssignments() : void
    {
        $query = 'SELECT assignment_id FROM loc_tst_assignments ' .
            'WHERE container_id = ' . $this->db->quote($this->getContainerId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $assignment = new ilLOTestAssignment($row->assignment_id);

            $this->assignments[] = $assignment;
        }
    }

    public function toXml(ilXmlWriter $writer, int $a_objective_id) : void
    {
        foreach ($this->getAssignments() as $assignment) {
            if ($assignment->getObjectiveId() != $a_objective_id) {
                continue;
            }
            $writer->xmlElement(
                'Test',
                array(
                    'type' => ilLOXmlWriter::TYPE_TST_PO,
                    'refId' => $assignment->getTestRefId(),
                    'testType' => $assignment->getAssignmentType()
                )
            );
        }
    }

    public static function lookupObjectivesForTest(int $a_test_ref_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $objectives = [];
        $query = 'SELECT objective_id FROM loc_tst_assignments ' .
            'WHERE tst_ref_id = ' . $ilDB->quote($a_test_ref_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $objectives[] = $row->objective_id;
        }
        return $objectives;
    }
}
