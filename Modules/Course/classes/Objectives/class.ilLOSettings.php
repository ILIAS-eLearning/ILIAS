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
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOSettings
{
    // new settings 5.1
    public const QST_PASSED_FLAG = 1;
    public const QST_PASSED_HIDE = 2;

    public const TYPE_INITIAL_PLACEMENT_ALL = 1;
    public const TYPE_INITIAL_PLACEMENT_SELECTED = 2;
    public const TYPE_INITIAL_QUALIFYING_ALL = 3;
    public const TYPE_INITIAL_QUALIFYING_SELECTED = 4;
    public const TYPE_INITIAL_NONE = 5;

    public const TYPE_QUALIFYING_ALL = 1;
    public const TYPE_QUALIFYING_SELECTED = 2;

    // end new settings

    public const TYPE_TEST_UNDEFINED = 0;
    public const TYPE_TEST_INITIAL = 1;
    public const TYPE_TEST_QUALIFIED = 2;

    public const QT_VISIBLE_ALL = 0;
    public const QT_VISIBLE_OBJECTIVE = 1;

    public const LOC_INITIAL_ALL = 1;
    public const LOC_INITIAL_SEL = 2;
    public const LOC_QUALIFIED = 3;
    public const LOC_PRACTISE = 4;

    public const HIDE_PASSED_OBJECTIVE_QST = 1;
    public const MARK_PASSED_OBJECTIVE_QST = 2;

    private static array $instances = [];

    private int $it_type = self::TYPE_INITIAL_PLACEMENT_ALL;
    private int $qt_type = self::TYPE_QUALIFYING_ALL;

    private bool $it_start = false;
    private bool $qt_start = false;
    private int $container_id = 0;
    private int $type = 0;
    private int $initial_test = 0;
    private int $qualified_test = 0;
    private bool $reset_results = true;
    private int $passed_objective_mode = self::HIDE_PASSED_OBJECTIVE_QST;

    private bool $entry_exists = false;

    private ilLogger $logger;
    protected ilDBInterface $db;
    protected ilTree $tree;

    protected function __construct(int $a_cont_id)
    {
        global $DIC;

        $this->logger = $DIC->logger()->crs();
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->container_id = $a_cont_id;
        $this->read();
    }

    public static function getInstanceByObjId(int $a_obj_id) : self
    {
        if (isset(self::$instances[$a_obj_id])) {
            return self::$instances[$a_obj_id];
        }
        return self::$instances[$a_obj_id] = new ilLOSettings($a_obj_id);
    }

    public function setInitialTestType(int $a_type) : void
    {
        $this->it_type = $a_type;
    }

    public function getInitialTestType() : int
    {
        return $this->it_type;
    }

    public function getQualifyingTestType() : int
    {
        return $this->qt_type;
    }

    public function setQualifyingTestType(int $a_type) : void
    {
        $this->qt_type = $a_type;
    }

    public function setInitialTestAsStart(bool $a_type) : void
    {
        $this->it_start = $a_type;
    }

    public function isInitialTestStart() : bool
    {
        return $this->it_start;
    }

    public function setQualifyingTestAsStart(bool $a_type) : void
    {
        $this->qt_start = $a_type;
    }

    public function isQualifyingTestStart() : bool
    {
        return $this->qt_start;
    }

    /**
     * Check if separate initial test are configured
     */
    public function hasSeparateInitialTests() : bool
    {
        return $this->getInitialTestType() == self::TYPE_INITIAL_PLACEMENT_SELECTED || $this->getInitialTestType() == self::TYPE_INITIAL_QUALIFYING_SELECTED;
    }

    /**
     * Check if separate qualified tests are configured
     */
    public function hasSeparateQualifiedTests() : bool
    {
        return $this->getQualifyingTestType() == self::TYPE_QUALIFYING_SELECTED;
    }

    /**
     *  Check if initial test is qualifying*
     */
    public function isInitialTestQualifying() : bool
    {
        return $this->getInitialTestType() == self::TYPE_INITIAL_QUALIFYING_ALL || $this->getInitialTestType() == self::TYPE_INITIAL_QUALIFYING_SELECTED;
    }

    /**
     * Check if test ref_id is used in an objective course
     */
    public static function isObjectiveTest(int $a_trst_ref_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        // Check for direct assignment
        $query = 'SELECT obj_id FROM loc_settings ' .
            'WHERE itest = ' . $ilDB->quote($a_trst_ref_id, 'integer') . ' ' .
            'OR qtest = ' . $ilDB->quote($a_trst_ref_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->obj_id;
        }
        return (bool) ilLOTestAssignments::lookupContainerForTest($a_trst_ref_id);
    }

    public static function cloneSettings(int $a_copy_id, int $a_container_id, int $a_new_container_id) : void
    {
        $options = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $options->getMappings();

        $settings = self::getInstanceByObjId($a_container_id);
        $new_settings = self::getInstanceByObjId($a_new_container_id);

        $new_settings->setType($settings->getType());
        $new_settings->setInitialTestType($settings->getInitialTestType());
        $new_settings->setQualifyingTestType($settings->getQualifyingTestType());
        $new_settings->resetResults($settings->isResetResultsEnabled());
        $new_settings->setPassedObjectiveMode($settings->getPassedObjectiveMode());

        if ($settings->getInitialTest() && array_key_exists($settings->getInitialTest(), $mappings)) {
            $new_settings->setInitialTest($mappings[$settings->getInitialTest()]);
            $new_settings->setInitialTestAsStart($new_settings->isInitialTestStart());
        }

        if ($settings->getQualifiedTest() && array_key_exists($settings->getQualifiedTest(), $mappings)) {
            $new_settings->setQualifiedTest($mappings[$settings->getQualifiedTest()]);
            $new_settings->setQualifyingTestAsStart($settings->isQualifyingTestStart());
        }

        // update calls create in case of no entry exists.
        $new_settings->update();
    }

    /**
     * Check if start objects are enabled
     */
    public function worksWithStartObjects() : bool
    {
        return $this->isInitialTestStart() || $this->isQualifyingTestStart();
    }

    /**
     * Check if the loc is configured for initial tests
     */
    public function worksWithInitialTest() : bool
    {
        return $this->getInitialTestType() !== self::TYPE_INITIAL_NONE;
    }

    public function isGeneralQualifiedTestVisible() : bool
    {
        return $this->getQualifyingTestType() === self::TYPE_QUALIFYING_ALL;
    }

    public function getPassedObjectiveMode() : int
    {
        return $this->passed_objective_mode;
    }

    public function setPassedObjectiveMode(int $a_mode) : void
    {
        $this->passed_objective_mode = $a_mode;
    }

    public function isGeneralInitialTestVisible() : bool
    {
        return
            $this->getInitialTestType() === self::TYPE_INITIAL_PLACEMENT_ALL ||
            $this->getInitialTestType() === self::TYPE_INITIAL_QUALIFYING_ALL;
    }

    public function settingsExist() : bool
    {
        return $this->entry_exists;
    }

    public function getObjId() : int
    {
        return $this->container_id;
    }

    public function setType(int $a_type) : void
    {
        $this->type = $a_type;
    }

    public function getType() : int
    {
        return $this->type;
    }

    public function getTestByType(int $a_type) : int
    {
        switch ($a_type) {
            case self::TYPE_TEST_INITIAL:
                return $this->getInitialTest();

            case self::TYPE_TEST_QUALIFIED:
                return $this->getQualifiedTest();
        }
        return 0;
    }

    public function getTests() : array
    {
        $tests = array();
        if ($this->getInitialTest()) {
            $tests[] = $this->getInitialTest();
        }
        if ($this->getQualifiedTest()) {
            $tests[] = $this->getQualifiedTest();
        }
        return $tests;
    }

    public function isRandomTestType(int $a_type) : bool
    {
        $tst = $this->getTestByType($a_type);
        return ilObjTest::_lookupRandomTest(ilObject::_lookupObjId($tst));
    }

    public function setInitialTest(int $a_id) : void
    {
        $this->initial_test = $a_id;
    }

    public function getInitialTest() : int
    {
        return $this->initial_test;
    }

    public function setQualifiedTest(int $a_id) : void
    {
        $this->qualified_test = $a_id;
    }

    public function getQualifiedTest() : int
    {
        return $this->qualified_test;
    }

    public function resetResults(bool $a_status) : void
    {
        $this->reset_results = $a_status;
    }

    public function isResetResultsEnabled() : bool
    {
        return $this->reset_results;
    }

    public function create() : void
    {
        $query = 'INSERT INTO loc_settings ' .
            '(obj_id, it_type,itest,qtest,it_start,qt_type,qt_start,reset_results,passed_obj_mode) VALUES ( ' .
            $this->db->quote($this->getObjId(), 'integer') . ', ' .
            $this->db->quote($this->getInitialTestType(), 'integer') . ', ' .
            $this->db->quote($this->getInitialTest(), 'integer') . ', ' .
            $this->db->quote($this->getQualifiedTest(), 'integer') . ', ' .
            $this->db->quote($this->isInitialTestStart(), 'integer') . ', ' .
            $this->db->quote($this->getQualifyingTestType(), 'integer') . ', ' .
            $this->db->quote($this->isQualifyingTestStart(), 'integer') . ', ' .
            $this->db->quote($this->isResetResultsEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getPassedObjectiveMode(), 'integer') . ' ' .
            ') ';
        $this->db->manipulate($query);
        $this->entry_exists = true;
    }

    public function update() : void
    {
        if (!$this->entry_exists) {
            $this->create();
            return;
        }

        $query = 'UPDATE loc_settings ' . ' ' .
            'SET it_type = ' . $this->db->quote($this->getInitialTestType(), 'integer') . ', ' .
            'itest = ' . $this->db->quote($this->getInitialTest(), 'integer') . ', ' .
            'qtest = ' . $this->db->quote($this->getQualifiedTest(), 'integer') . ', ' .
            'it_start = ' . $this->db->quote($this->isInitialTestStart(), 'integer') . ', ' .
            'qt_type = ' . $this->db->quote($this->getQualifyingTestType(), 'integer') . ', ' .
            'qt_start = ' . $this->db->quote($this->isQualifyingTestStart(), 'integer') . ', ' .
            'reset_results = ' . $this->db->quote($this->isResetResultsEnabled(), 'integer') . ', ' .
            'passed_obj_mode = ' . $this->db->quote($this->getPassedObjectiveMode(), 'integer') . ' ' .
            'WHERE obj_id = ' . $this->db->quote($this->getObjId(), 'integer');

        $this->db->manipulate($query);
    }

    /**
     * Update start objects
     * Depends on course objective settings
     * @param ilContainerStartObjects
     */
    public function updateStartObjects(ilContainerStartObjects $start) : void
    {
        if ($this->getInitialTestType() != self::TYPE_INITIAL_NONE) {
            if ($start->exists($this->getQualifiedTest())) {
                $start->deleteItem($this->getQualifiedTest());
            }
        }

        switch ($this->getInitialTestType()) {
            case self::TYPE_INITIAL_PLACEMENT_ALL:
            case self::TYPE_INITIAL_QUALIFYING_ALL:

                if ($this->isInitialTestStart()) {
                    if (!$start->exists($this->getInitialTest())) {
                        $start->add($this->getInitialTest());
                    }
                } else {
                    if ($start->exists($this->getInitialTest())) {
                        $start->deleteItem($this->getInitialTest());
                    }
                }
                break;

            case self::TYPE_INITIAL_NONE:

                if ($start->exists($this->getInitialTest())) {
                    $start->deleteItem($this->getInitialTest());
                }
                break;

            default:

                $this->logger->debug('Type initial default');
                if ($start->exists($this->getInitialTest())) {
                    $this->logger->debug('Old start object exists. Trying to delete');
                    $start->deleteItem($this->getInitialTest());
                }
                break;
        }

        switch ($this->getQualifyingTestType()) {
            case self::TYPE_QUALIFYING_ALL:

                if ($this->isQualifyingTestStart()) {
                    if (!$start->exists($this->getQualifiedTest())) {
                        $start->add($this->getQualifiedTest());
                    }
                }
                break;

            default:
                if ($start->exists($this->getQualifiedTest())) {
                    $start->deleteItem($this->getQualifiedTest());
                }
                break;
        }
    }

    /**
     * Read
     */
    protected function read() : void
    {
        $query = 'SELECT * FROM loc_settings ' .
            'WHERE obj_id = ' . $this->db->quote($this->getObjId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->entry_exists = true;

            $this->setInitialTestType((int) $row->it_type);
            $this->setInitialTestAsStart((bool) $row->it_start);
            $this->setQualifyingTestType((int) $row->qt_type);
            $this->setQualifyingTestAsStart((bool) $row->qt_start);
            $this->setInitialTest((int) $row->itest);
            $this->setQualifiedTest((int) $row->qtest);
            $this->resetResults((bool) $row->reset_results);
            $this->setPassedObjectiveMode((int) $row->passed_obj_mode);
        }

        if ($this->tree->isDeleted($this->getInitialTest())) {
            $this->setInitialTest(0);
        }
        if ($this->tree->isDeleted($this->getQualifiedTest())) {
            $this->setQualifiedTest(0);
        }
    }

    public function toXml(ilXmlWriter $writer) : void
    {
        $writer->xmlElement(
            'Settings',
            array(
                'initialTestType' => $this->getInitialTestType(),
                'initialTestStart' => (int) $this->isInitialTestStart(),
                'qualifyingTestType' => $this->getQualifyingTestType(),
                'qualifyingTestStart' => (int) $this->isQualifyingTestStart(),
                'resetResults' => (int) $this->isResetResultsEnabled(),
                'passedObjectivesMode' => $this->getPassedObjectiveMode(),
                'iTest' => $this->getInitialTest(),
                'qTest' => $this->getQualifiedTest()
            )
        );
    }
}
