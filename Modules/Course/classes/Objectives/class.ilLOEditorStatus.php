<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';

/**
 * Presentation of the status of single steps during the configuration process.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOEditorStatus
{
    const SECTION_SETTINGS = 1;
    const SECTION_MATERIALS = 2;
    const SECTION_ITES = 3;
    const SECTION_QTEST = 4;
    const SECTION_OBJECTIVES = 5;
    const SECTION_OBJECTIVES_NEW = 6;
        
    protected static $instance = null;
    

    protected $section = null;

    protected $failures_by_section = array();
    
    protected $objectives = array();

    protected $settings = null;
    protected $assignments = null;
    protected $parent_obj = null;
    protected $cmd_class = null;
    protected $html = '';
    
    protected $tpl = null;
    protected $ctrl = null;
    protected $lng = null;
    
    /**
     * Constructor
     * @param ilObject
     */
    public function __construct(ilObject $a_parent)
    {
        $this->parent_obj = $a_parent;
        $this->settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        $this->assignments = ilLOTestAssignments::getInstance($this->getParentObject()->getId());
        
        $this->ctrl = $GLOBALS['DIC']['ilCtrl'];
        $this->lng = $GLOBALS['DIC']['lng'];
        
        include_once './Modules/Course/classes/class.ilCourseObjective.php';
        $this->objectives = ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId());
    }
    
    /**
     * Get instance
     * @param ilObject $a_parent
     */
    public static function getInstance(ilObject $a_parent)
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self($a_parent);
    }
    
    /**
     * @return array
     */
    public function getObjectives()
    {
        return $this->objectives;
    }
    
    /**
     *
     * @return ilLOTestAssignments
     */
    public function getAssignments()
    {
        return $this->assignments;
    }


    /**
     * Set current section
     * @param type $a_section
     */
    public function setSection($a_section)
    {
        $this->section = $a_section;
    }
    
    public function getSection()
    {
        return $this->section;
    }
    
    /**
     * Get failures by section
     * @param type $a_section
     */
    public function getFailures($a_section)
    {
        return (array) $this->failures_by_section[$a_section];
    }
    
    /**
     * Append failure
     * @param type $a_section
     * @param type $a_failure_msg_key
     */
    protected function appendFailure($a_section, $a_failure_msg_key)
    {
        $this->failures_by_section[$a_section][] = $a_failure_msg_key;
    }
    
    /**
     * Command class
     * @param type $a_cmd_class
     */
    public function setCmdClass($a_cmd_class)
    {
        $this->cmd_class = $a_cmd_class;
    }
    
    /**
     * Get cmd class
     * @return type
     */
    public function getCmdClass()
    {
        return $this->cmd_class;
    }
    
    
    /**
     * Get parent object
     * @return ilObject
     */
    public function getParentObject()
    {
        return $this->parent_obj;
    }
    
    /*
     * @return ilLOSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * Get first failed step
     */
    public function getFirstFailedStep()
    {
        if (!$this->getSettingsStatus(false)) {
            return 'settings';
        }
        #if(!$this->getMaterialsStatus(false))
        #{
        #	return 'materials';
        #}
        if (!$this->getObjectivesAvailableStatus()) {
            return 'showObjectiveCreation';
        }
        if ($this->getSettings()->worksWithInitialTest()) {
            if (!$this->getInitialTestStatus(false)) {
                $_REQUEST['tt'] = ilLOSettings::TYPE_TEST_INITIAL;
                if ($this->getSettings()->hasSeparateInitialTests()) {
                    return 'testsOverview';
                } else {
                    return 'testOverview';
                }
            }
        }
        if (!$this->getQualifiedTestStatus(false)) {
            $_REQUEST['tt'] = ilLOSettings::TYPE_TEST_QUALIFIED;
            if ($this->getSettings()->hasSeparateQualifiedTests()) {
                return 'testsOverview';
            } else {
                return 'testOverview';
            }
        }
        if (!$this->getObjectivesStatus(false)) {
            return 'listObjectives';
        }
        return 'listObjectives';
    }
    
    
    /**
     * Get html
     */
    public function getHTML()
    {
        include_once("./Services/UIComponent/Checklist/classes/class.ilChecklistGUI.php");
        $list = new ilChecklistGUI();
        $list->setHeading($this->lng->txt('crs_objective_status_configure'));


        // Step 1
        // course settings
        $done = $this->getSettingsStatus();

        $list->addEntry(
            $this->lng->txt('crs_objective_status_settings'),
            $this->ctrl->getLinkTarget($this->getCmdClass(), 'settings'),
            $done
                ? ilChecklistGUI::STATUS_OK
                : ilChecklistGUI::STATUS_NOT_OK,
            ($this->section == self::SECTION_SETTINGS),
            $this->getErrorMessages(self::SECTION_SETTINGS)
        );
        
        
        // Step 1.1
        $done = $this->getObjectivesAvailableStatus(true);

        $list->addEntry(
            $this->lng->txt('crs_objective_status_objective_creation'),
            $done
                ? $this->ctrl->getLinkTarget($this->getCmdClass(), 'listObjectives')
                : $this->ctrl->getLinkTarget($this->getCmdClass(), 'showObjectiveCreation'),
            $done
                ? ilChecklistGUI::STATUS_OK
                : ilChecklistGUI::STATUS_NOT_OK,
            ($this->section == self::SECTION_OBJECTIVES_NEW),
            $this->getErrorMessages(self::SECTION_OBJECTIVES_NEW)
        );

        // Step 2
        // course material
        $done = $this->getMaterialsStatus(true);
        
        $this->ctrl->setParameterByClass('ilobjcoursegui', 'cmd', 'enableAdministrationPanel');
        $list->addEntry(
            $this->lng->txt('crs_objective_status_materials'),
            $this->ctrl->getLinkTargetByClass('ilobjcoursegui', ''),
            $done
                ? ilChecklistGUI::STATUS_OK
                : ilChecklistGUI::STATUS_NOT_OK,
            ($this->section == self::SECTION_MATERIALS),
            $this->getErrorMessages(self::SECTION_MATERIALS)
        );


        // Step 3
        // course itest
        if (ilLOSettings::getInstanceByObjId($this->getParentObject()->getId())->worksWithInitialTest()) {
            $done = $this->getInitialTestStatus();

            $command = $this->getSettings()->hasSeparateInitialTests() ?
                    'testsOverview' :
                    'testOverview';
            
            $this->ctrl->setParameter($this->getCmdClass(), 'tt', ilLOSettings::TYPE_TEST_INITIAL);
            $list->addEntry(
                $this->lng->txt('crs_objective_status_itest'),
                $this->ctrl->getLinkTarget($this->getCmdClass(), $command),
                $done
                        ? ilChecklistGUI::STATUS_OK
                        : ilChecklistGUI::STATUS_NOT_OK,
                ($this->section == self::SECTION_ITES),
                $this->getErrorMessages(self::SECTION_ITES)
            );
        }

        // Step 4
        // course qtest
        $done = $this->getQualifiedTestStatus();
        
        $command = $this->getSettings()->hasSeparateQualifiedTests() ?
                'testsOverview' :
                'testOverview';

        $this->ctrl->setParameter($this->getCmdClass(), 'tt', ilLOSettings::TYPE_TEST_QUALIFIED);
        $list->addEntry(
            $this->lng->txt('crs_objective_status_qtest'),
            $this->ctrl->getLinkTarget($this->getCmdClass(), $command),
            $done
                ? ilChecklistGUI::STATUS_OK
                : ilChecklistGUI::STATUS_NOT_OK,
            ($this->section == self::SECTION_QTEST),
            $this->getErrorMessages(self::SECTION_QTEST)
        );

        $this->ctrl->setParameter($this->getCmdClass(), 'tt', $_GET["tt"]);

        // Step 5
        // course qtest
        $done = $this->getObjectivesStatus();

        $list->addEntry(
            $this->lng->txt('crs_objective_status_objectives'),
            $this->ctrl->getLinkTarget($this->getCmdClass(), 'listObjectives'),
            $done
                ? ilChecklistGUI::STATUS_OK
                : ilChecklistGUI::STATUS_NOT_OK,
            ($this->section == self::SECTION_OBJECTIVES),
            $this->getErrorMessages(self::SECTION_OBJECTIVES)
        );

        return $list->getHTML();
    }
    
    

    /**
     * Get error messages
     *
     * @param
     * @return
     */
    public function getErrorMessages($a_section)
    {
        $mess = array();
        foreach ($this->getFailures($a_section) as $failure_code) {
            $mess[] = $this->lng->txt($failure_code);
        }
        return $mess;
    }

    
    /**
     * Check if course is lo confgured
     * @return type
     */
    protected function getSettingsStatus()
    {
        return $this->getSettings()->settingsExist();
    }

    /**
     * Get objectives
     * @var bool $a_set_errors
     *
     * @return type
     */
    protected function getObjectivesAvailableStatus($a_set_errors = false)
    {
        $ret = count($this->getObjectives());

        if (!$ret && $a_set_errors) {
            $this->appendFailure(self::SECTION_OBJECTIVES_NEW, 'crs_no_objectives_created');
        }

        return $ret;
    }
    
    /**
     * Get status of materials
     */
    protected function getMaterialsStatus($a_set_errors = true)
    {
        $childs = $GLOBALS['DIC']['tree']->getChilds($this->getParentObject()->getRefId());
        foreach ((array) $childs as $tnode) {
            if ($tnode['type'] == 'rolf') {
                continue;
            }
            if ($tnode['child'] == $this->getSettings()->getInitialTest()) {
                continue;
            }
            if ($tnode['child'] == $this->getSettings()->getQualifiedTest()) {
                continue;
            }
            return true;
        }
        if ($a_set_errors) {
            $this->appendFailure(self::SECTION_MATERIALS, 'crs_loc_err_stat_no_materials');
        }
        return false;
    }
    
    /**
     * Get initial test status
     * @param type $a_set_errors
     * @return boolean
     */
    protected function getInitialTestStatus($a_set_errors = true)
    {
        if ($this->getSettings()->hasSeparateInitialTests()) {
            foreach ($this->getObjectives() as $objective_id) {
                $tst_ref = $this->getAssignments()->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_INITIAL);
                if (!$GLOBALS['DIC']['tree']->isInTree($tst_ref)) {
                    if ($a_set_errors) {
                        $this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_no_it');
                    }
                    return false;
                }
                if (!$this->checkTestOnline($tst_ref)) {
                    if ($a_set_errors) {
                        $this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_tst_offline');
                    }
                    return false;
                }
            }
            return true;
        }
        
        
        $tst_ref = $this->getSettings()->getInitialTest();
        if (!$GLOBALS['DIC']['tree']->isInTree($tst_ref)) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_no_it');
            }
            return false;
        }
        if (!$this->checkTestOnline($tst_ref)) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_tst_offline');
            }
            return false;
        }
        return true;
    }
    
    /**
     * Check status of qualified test
     * @param type $a_set_errors
     * @return boolean
     */
    protected function getQualifiedTestStatus($a_set_errors = true)
    {
        if ($this->getSettings()->hasSeparateQualifiedTests()) {
            foreach ($this->getObjectives() as $objective_id) {
                $tst_ref = $this->getAssignments()->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_QUALIFIED);
                if (!$GLOBALS['DIC']['tree']->isInTree($tst_ref)) {
                    if ($a_set_errors) {
                        $this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_no_qt');
                    }
                    return false;
                }
                if (!$this->checkTestOnline($tst_ref)) {
                    if ($a_set_errors) {
                        $this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_tst_offline');
                    }
                    return false;
                }
            }
            return true;
        }
        $tst_ref = $this->getSettings()->getQualifiedTest();
        if (!$GLOBALS['DIC']['tree']->isInTree($tst_ref)) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_no_qt');
            }
            return false;
        }
        if (!$this->checkTestOnline($tst_ref)) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_tst_offline');
            }
            return false;
        }
        return true;
    }
    
    /**
     * Check if questions are assigned
     * @param type $a_test_ref_id
     */
    protected function lookupQuestionsAssigned($a_test_ref_id)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';
        if (ilLOUtils::lookupRandomTest(ilObject::_lookupObjId($a_test_ref_id))) {
            foreach ($this->getObjectives() as $objective_id) {
                include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
                $seq = ilLORandomTestQuestionPools::lookupSequences(
                    $this->parent_obj->getId(),
                    $objective_id,
                    ilObject::_lookupObjId($a_test_ref_id)
                );
                if (!$seq) {
                    return false;
                }
            }
        } else {
            foreach ($this->getObjectives() as $objective_id) {
                include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
                $qsts = ilCourseObjectiveQuestion::lookupQuestionsByObjective(ilObject::_lookupObjId($a_test_ref_id), $objective_id);
                if (!count($qsts)) {
                    return false;
                }
            }
        }
        return true;
    }


    protected function getObjectivesStatus($a_set_errors = true)
    {
        if (!$this->getObjectivesAvailableStatus($a_set_errors)) {
            return false;
        }
        
        include_once './Modules/Course/classes/class.ilCourseObjective.php';
        $num_active = ilCourseObjective::_getCountObjectives($this->getParentObject()->getId(), true);
        if (!$num_active) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_lo');
            }
            return false;
        }
        foreach (ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId(), true) as $objective_id) {
            include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
            $obj = new ilCourseObjectiveMaterials($objective_id);
            if (!count($obj->getMaterials())) {
                if ($a_set_errors) {
                    $this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_mat');
                }
                return false;
            }
        }
        // check for assigned initial test questions
        if ($this->getSettings()->worksWithInitialTest() && !$this->getSettings()->hasSeparateInitialTests()) {
            // check for assigned questions
            if (!$this->lookupQuestionsAssigned($this->getSettings()->getInitialTest())) {
                if ($a_set_errors) {
                    $this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_qst');
                }
                return false;
            }
        }
        // check for assigned questions
        if (!$this->getSettings()->hasSeparateQualifiedTests() and !$this->lookupQuestionsAssigned($this->getSettings()->getQualifiedTest())) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_qst');
            }
            return false;
        }
        
        // @deprecated
        /*
        if(!$this->checkNumberOfTries())
        {
            if($a_set_errors)
            {
                $this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_nr_tries_exceeded');
            }
            return false;
        }
        */
        
        return true;
    }
    
    protected function getStartStatus()
    {
        return true;
    }
    
    protected function checkNumberOfTries()
    {
        $qt = $this->getSettings()->getQualifiedTest();
        if (!$qt) {
            return true;
        }
        
        include_once './Services/Object/classes/class.ilObjectFactory.php';
        $factory = new ilObjectFactory();
        $tst = $factory->getInstanceByRefId($qt, false);
        
        if (!$tst instanceof ilObjTest) {
            return true;
        }
        $tries = $tst->getNrOfTries();
        if (!$tries) {
            return true;
        }
        
        $obj_tries = 0;
        foreach ($this->getObjectives() as $objective) {
            include_once './Modules/Course/classes/class.ilCourseObjective.php';
            $obj_tries += ilCourseObjective::lookupMaxPasses($objective);
        }
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . $obj_tries);
        return $obj_tries <= $tries;
    }
    
    /**
     * Check if test is online
     * @param int $a_ref_id
     */
    protected function checkTestOnline($a_ref_id)
    {
        include_once './Modules/Test/classes/class.ilObjTestAccess.php';
        return !ilObjTestAccess::_isOffline(ilObject::_lookupObjId($a_ref_id));
    }
}
