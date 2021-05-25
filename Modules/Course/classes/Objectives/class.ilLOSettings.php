<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings for LO courses
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOSettings
{
    // new settings 5.1
    const QST_PASSED_FLAG = 1;
    const QST_PASSED_HIDE = 2;
    
    const TYPE_INITIAL_PLACEMENT_ALL = 1;
    const TYPE_INITIAL_PLACEMENT_SELECTED = 2;
    const TYPE_INITIAL_QUALIFYING_ALL = 3;
    const TYPE_INITIAL_QUALIFYING_SELECTED = 4;
    const TYPE_INITIAL_NONE = 5;
    
    const TYPE_QUALIFYING_ALL = 1;
    const TYPE_QUALIFYING_SELECTED = 2;
    
    // end new settings
    
    const TYPE_TEST_UNDEFINED = 0;
    const TYPE_TEST_INITIAL = 1;
    const TYPE_TEST_QUALIFIED = 2;
    
    const QT_VISIBLE_ALL = 0;
    const QT_VISIBLE_OBJECTIVE = 1;
    
    
    const LOC_INITIAL_ALL = 1;
    const LOC_INITIAL_SEL = 2;
    const LOC_QUALIFIED = 3;
    const LOC_PRACTISE = 4;
    
    const HIDE_PASSED_OBJECTIVE_QST = 1;
    const MARK_PASSED_OBJECTIVE_QST = 2;
    
    
    private static $instances = array();
    
    /**
     * @var ilLogger
     */
    private $logger = null;
    
    
    // settings 5.1
    private $it_type = self::TYPE_INITIAL_PLACEMENT_ALL;
    private $qt_type = self::TYPE_QUALIFYING_ALL;
    
    private $it_start = false;
    private $qt_start = false;
    
    // end settings 5.1
    
    private $container_id = 0;
    private $type = 0;
    private $initial_test = 0;
    private $qualified_test = 0;
    private $reset_results = true;
    private $passed_objective_mode = self::HIDE_PASSED_OBJECTIVE_QST;


    private $entry_exists = false;

    
    /**
     * Constructor
     * @param int $a_cont_id
     */
    protected function __construct($a_cont_id)
    {
        $this->logger = $GLOBALS['DIC']->logger()->crs();
        
        $this->container_id = $a_cont_id;
        $this->read();
    }
    
    /**
     * get singleton instance
     * @param int $a_obj_id
     * @return ilLOSettings
     */
    public static function getInstanceByObjId($a_obj_id)
    {
        if (self::$instances[$a_obj_id]) {
            return self::$instances[$a_obj_id];
        }
        return self::$instances[$a_obj_id] = new ilLOSettings($a_obj_id);
    }
    
    /**
     * Set Initial test type
     * @param type $a_type
     */
    public function setInitialTestType($a_type)
    {
        $this->it_type = $a_type;
    }
    
    /**
     * Get initial test type
     * @return type
     */
    public function getInitialTestType()
    {
        return $this->it_type;
    }
    
    /**
     * Get qualifying test type
     */
    public function getQualifyingTestType()
    {
        return $this->qt_type;
    }
    
    /**
     * Set qualifying test type
     * @param type $a_type
     */
    public function setQualifyingTestType($a_type)
    {
        $this->qt_type = $a_type;
    }
    
    /**
     *
     * @param type $a_type
     */
    public function setInitialTestAsStart($a_type)
    {
        $this->it_start = $a_type;
    }
    
    /**
     * Get initial test start
     * @return type
     */
    public function isInitialTestStart()
    {
        return $this->it_start;
    }
    
    /**
     * Set qt as start object
     * @param type $a_type
     */
    public function setQualifyingTestAsStart($a_type)
    {
        $this->qt_start = $a_type;
    }
    
    /**
     * Is qt start object
     * @return type
     */
    public function isQualifyingTestStart()
    {
        return $this->qt_start;
    }
    
    /**
     * Check if separate initial test are configured
     */
    public function hasSeparateInitialTests()
    {
        return $this->getInitialTestType() == self::TYPE_INITIAL_PLACEMENT_SELECTED || $this->getInitialTestType() == self::TYPE_INITIAL_QUALIFYING_SELECTED;
    }
    
    /**
     * Check if separate qualified tests are configured
     */
    public function hasSeparateQualifiedTests()
    {
        return $this->getQualifyingTestType() == self::TYPE_QUALIFYING_SELECTED;
    }

    /**
     *  Check if initial test is qualifying*
     */
    public function isInitialTestQualifying()
    {
        return $this->getInitialTestType() == self::TYPE_INITIAL_QUALIFYING_ALL || $this->getInitialTestType() == self::TYPE_INITIAL_QUALIFYING_SELECTED;
    }
    
    /**
     * Check if test ref_id is used in an objective course
     * @param int ref_id
     */
    public static function isObjectiveTest($a_trst_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        
        // Check for direct assignment
        $query = 'SELECT obj_id FROM loc_settings ' .
                'WHERE itest = ' . $ilDB->quote($a_trst_ref_id, 'integer') . ' ' .
                'OR qtest = ' . $ilDB->quote($a_trst_ref_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->obj_id;
        }
        
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
        return ilLOTestAssignments::lookupContainerForTest($a_trst_ref_id);
    }
    
    /**
     * Clone settings
     * @param type $a_copy_id
     * @param type $a_container_id
     * @param type $a_new_container_id
     */
    public static function cloneSettings($a_copy_id, $a_container_id, $a_new_container_id)
    {
        include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
        $options = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $options->getMappings();
        
        $settings = self::getInstanceByObjId($a_container_id);
        $new_settings = self::getInstanceByObjId($a_new_container_id);
        
        $new_settings->setType($settings->getType());
        $new_settings->setInitialTestType($settings->getInitialTestType());
        $new_settings->setQualifyingTestType($settings->getQualifyingTestType());
        $new_settings->resetResults($settings->isResetResultsEnabled());
        $new_settings->setPassedObjectiveMode($settings->getPassedObjectiveMode());
        
        if ($settings->getInitialTest() and array_key_exists($settings->getInitialTest(), $mappings)) {
            $new_settings->setInitialTest($mappings[$settings->getInitialTest()]);
            $new_settings->setInitialTestAsStart($new_settings->isInitialTestStart());
        }
        
        if ($settings->getQualifiedTest() and array_key_exists($settings->getQualifiedTest(), $mappings)) {
            $new_settings->setQualifiedTest($mappings[$settings->getQualifiedTest()]);
            $new_settings->setQualifyingTestAsStart($settings->isQualifyingTestStart());
        }
        
        // update calls create in case of no entry exists.
        $new_settings->update();
    }
    
    /**
     * Check if start objects are enabled
     */
    public function worksWithStartObjects()
    {
        return $this->isInitialTestStart() or $this->isQualifyingTestStart();
    }


    /**
     * Check if the loc is configured for initial tests
     */
    public function worksWithInitialTest()
    {
        return $this->getInitialTestType() != self::TYPE_INITIAL_NONE;
    }
    
    /**
     * Check if qualified test for all objectives is visible
     * @return type
     */
    public function isGeneralQualifiedTestVisible()
    {
        return $this->getQualifyingTestType() == self::TYPE_QUALIFYING_ALL;
    }

    /**
     * @return type
     */
    public function setGeneralQualifiedTestVisibility($a_stat)
    {
        $this->qt_visible_all = $a_stat;
        return true;
    }
    
    public function isQualifiedTestPerObjectiveVisible()
    {
        return $this->getQualifyingTestType() == self::TYPE_QUALIFYING_SELECTED;
    }
    
    public function setQualifiedTestPerObjectiveVisibility($a_stat)
    {
        $this->qt_visible_lo = $a_stat;
    }

    public function getPassedObjectiveMode()
    {
        return $this->passed_objective_mode;
    }
    
    public function setPassedObjectiveMode($a_mode)
    {
        $this->passed_objective_mode = $a_mode;
    }
    
    /**
     * Check if initial test for all objectives is visible
     * @return bool
     */
    public function isGeneralInitialTestVisible()
    {
        return $this->getInitialTestType() == self::TYPE_INITIAL_PLACEMENT_ALL || $this->getInitialTestType() == self::TYPE_INITIAL_QUALIFYING_ALL;
    }
    
    /**
     *
     * @return type
     */
    public function settingsExist()
    {
        return $this->entry_exists;
    }
    
    public function getObjId()
    {
        return $this->container_id;
    }
    
    public function setType($a_type)
    {
        $this->type = $a_type;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    /**
     *
     * @param type $a_type
     * @return type
     * @todo refactor
     */
    public function getTestByType($a_type)
    {
        switch ($a_type) {
            case self::TYPE_TEST_INITIAL:
                return $this->getInitialTest();
                
            case self::TYPE_TEST_QUALIFIED:
                return $this->getQualifiedTest();
        }
    }
    
    /**
     * Get assigned tests
     * @return type
     * @todo refactor
     */
    public function getTests()
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
    
    /**
     * Check if test is of type random test
     * @param type $a_type
     * @return type
     * @todo refactor
     */
    public function isRandomTestType($a_type)
    {
        $tst = $this->getTestByType($a_type);
        include_once './Modules/Test/classes/class.ilObjTest.php';
        return ilObjTest::_lookupRandomTest(ilObject::_lookupObjId($tst));
    }
    
    /**
     * set initial test id
     * @param type $a_id
     * @todo refactor
     */
    public function setInitialTest($a_id)
    {
        $this->initial_test = $a_id;
    }

    /**
     * get initial test
     * @return type
     * @todo refactor
     */
    public function getInitialTest()
    {
        return $this->initial_test;
    }

    /**
     * set qualified test
     * @param type $a_id
     * @todo refactor
     */
    public function setQualifiedTest($a_id)
    {
        $this->qualified_test = $a_id;
    }
    
    /**
     * get qualified test
     * @return type
     * @todo refactor
     */
    public function getQualifiedTest()
    {
        return $this->qualified_test;
    }

    /**
     * reset results
     * @param type $a_status
     */
    public function resetResults($a_status)
    {
        $this->reset_results = $a_status;
    }
    
    /**
     * check if reset result is enabled
     * @return type
     */
    public function isResetResultsEnabled()
    {
        return (bool) $this->reset_results;
    }
    
    /**
     * Create new entry
     */
    public function create()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'INSERT INTO loc_settings ' .
                '(obj_id, it_type,itest,qtest,it_start,qt_type,qt_start,reset_results,passed_obj_mode) VALUES ( ' .
                $ilDB->quote($this->getObjId(), 'integer') . ', ' .
                $ilDB->quote($this->getInitialTestType(), 'integer') . ', ' .
                $ilDB->quote($this->getInitialTest(), 'integer') . ', ' .
                $ilDB->quote($this->getQualifiedTest(), 'integer') . ', ' .
                $ilDB->quote($this->isInitialTestStart(), 'integer') . ', ' .
                $ilDB->quote($this->getQualifyingTestType(), 'integer') . ', ' .
                $ilDB->quote($this->isQualifyingTestStart(), 'integer') . ', ' .
                $ilDB->quote($this->isResetResultsEnabled(), 'integer') . ', ' .
                $ilDB->quote($this->getPassedObjectiveMode(), 'integer') . ' ' .
                ') ';
        $ilDB->manipulate($query);
        
        $this->entry_exists = true;
    }

    
    /**
     * update settings
     * @global type $ilDB
     */
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->entry_exists) {
            return $this->create();
        }
        
        $query = 'UPDATE loc_settings ' . ' ' .
                'SET it_type = ' . $ilDB->quote($this->getInitialTestType(), 'integer') . ', ' .
                'itest = ' . $ilDB->quote($this->getInitialTest(), 'integer') . ', ' .
                'qtest = ' . $ilDB->quote($this->getQualifiedTest(), 'integer') . ', ' .
                'it_start = ' . $ilDB->quote($this->isInitialTestStart(), 'integer') . ', ' .
                'qt_type = ' . $ilDB->quote($this->getQualifyingTestType(), 'integer') . ', ' .
                'qt_start = ' . $ilDB->quote($this->isQualifyingTestStart(), 'integer') . ', ' .
                'reset_results = ' . $ilDB->quote($this->isResetResultsEnabled(), 'integer') . ', ' .
                'passed_obj_mode = ' . $ilDB->quote($this->getPassedObjectiveMode(), 'integer') . ' ' .
                'WHERE obj_id = ' . $ilDB->quote($this->getObjId(), 'integer');
                
        $ilDB->manipulate($query);
    }

    /**
     * Update start objects
     * Depends on course objective settings
     *
     * @param ilContainerStartObjects
     */
    public function updateStartObjects(ilContainerStartObjects $start)
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
        return true;
    }
    
    
    /**
     * Read
     */
    protected function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT * FROM loc_settings ' .
                'WHERE obj_id = ' . $ilDB->quote($this->getObjId(), 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->entry_exists = true;
            
            $this->setInitialTestType($row->it_type);
            $this->setInitialTestAsStart((bool) $row->it_start);
            $this->setQualifyingTestType($row->qt_type);
            $this->setQualifyingTestAsStart($row->qt_start);
            
            #$this->setType($row->type);
            $this->setInitialTest($row->itest);
            $this->setQualifiedTest($row->qtest);
            #$this->setGeneralQualifiedTestVisibility($row->qt_vis_all);
            #$this->setQualifiedTestPerObjectiveVisibility($row->qt_vis_obj);
            $this->resetResults($row->reset_results);
            $this->setPassedObjectiveMode($row->passed_obj_mode);
        }
        
        if ($GLOBALS['DIC']['tree']->isDeleted($this->getInitialTest())) {
            $this->setInitialTest(0);
        }
        if ($GLOBALS['DIC']['tree']->isDeleted($this->getQualifiedTest())) {
            $this->setQualifiedTest(0);
        }
    }
    
    /**
     * export to xml
     * @param ilXmlWriter $writer
     */
    public function toXml(ilXmlWriter $writer)
    {
        $writer->xmlElement(
            'Settings',
            array(
                'initialTestType' => (int) $this->getInitialTestType(),
                'initialTestStart' => (int) $this->isInitialTestStart(),
                'qualifyingTestType' => (int) $this->getQualifyingTestType(),
                'qualifyingTestStart' => (int) $this->isQualifyingTestStart(),
                'resetResults' => (int) $this->isResetResultsEnabled(),
                'passedObjectivesMode' => (int) $this->getPassedObjectiveMode(),
                'iTest' => (int) $this->getInitialTest(),
                'qTest' => (int) $this->getQualifiedTest()
            )
        );
    }
}
