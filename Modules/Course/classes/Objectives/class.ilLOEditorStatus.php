<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Implementation\Component\Listing\Workflow\Step;

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

    /** @var self|null  */
    protected static $instance = null;
    
    /** @var int  */
    protected $section = null;

    /** @var string[]  */
    protected $failures_by_section = array();
    /** @var int[]  */
    protected $error_by_section = array();

    /** @var array  */
    protected $objectives = array();

    /** @var ilLOSettings|null  */
    protected $settings = null;
    /** @var ilLOTestAssignments|null  */
    protected $assignments = null;
    /** @var ilObject|null  */
    protected $parent_obj = null;
    /** @var null|object  */
    protected $cmd_class = null;
    /** @var string  */
    protected $html = '';

    /** @var ilTemplate  */
    protected $tpl = null;
    /** @var ilCtrl|null  */
    protected $ctrl = null;
    /** @var ilLanguage|null  */
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
     * @return ilLOEditorStatus
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
     * @param int $a_section
     */
    public function setSection(int $a_section)
    {
        $this->section = $a_section;
    }

    /**
     * @return int
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Get failures by section
     * @param int $a_section
     * @return array
     */
    public function getFailures($a_section)
    {
        return (array) $this->failures_by_section[$a_section];
    }

    /**
     * Append failure
     * @param int $a_section
     * @param string $a_failure_msg_key
     * @param bool $is_error
     */
    protected function appendFailure(int $a_section, string $a_failure_msg_key, bool $is_error = false)
    {
        $this->failures_by_section[$a_section][] = $a_failure_msg_key;
        if ($is_error) {
            $this->error_by_section[$a_section] = $a_section;
        }
    }
    
    /**
     * Command class
     * @param object $a_cmd_class
     */
    public function setCmdClass($a_cmd_class)
    {
        $this->cmd_class = $a_cmd_class;
    }
    
    /**
     * Get cmd class
     * @return object
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
    
    /**
     * @return ilLOSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Get first failed step
     *
     * @return string
     */
    public function getFirstFailedStep() : string
    {
        if (!$this->getSettingsStatus()) {
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
     *
     * @ret string
     */
    public function getHTML() : string
    {
        global $DIC;
        $steps = [];
        $workflow = $DIC->ui()->factory()->listing()->workflow();
        // Step 1
        // course settings
        $done = $this->getSettingsStatus();

        $steps[] = $workflow->step(
            $this->lng->txt('crs_objective_status_settings'),
            implode(" ", $this->getFailureMessages(self::SECTION_SETTINGS)),
            $this->ctrl->getLinkTarget($this->getCmdClass(), 'settings')
        )->withStatus($this->determineStatus($done, self::SECTION_SETTINGS));


        // Step 1.1
        $done = $this->getObjectivesAvailableStatus(true);

        $steps[] = $workflow->step(
            $this->lng->txt('crs_objective_status_objective_creation'),
            implode(" ", $this->getFailureMessages(self::SECTION_OBJECTIVES_NEW)),
            $done
                ? $this->ctrl->getLinkTarget($this->getCmdClass(), 'listObjectives')
                : $this->ctrl->getLinkTarget($this->getCmdClass(), 'showObjectiveCreation')
        )->withStatus($this->determineStatus($done, self::SECTION_OBJECTIVES_NEW));

        // Step 2
        // course material
        $done = $this->getMaterialsStatus(true);
        $this->ctrl->setParameterByClass('ilobjcoursegui', 'cmd', 'enableAdministrationPanel');

        $steps[] = $workflow->step(
            $this->lng->txt('crs_objective_status_materials'),
            implode(" ", $this->getFailureMessages(self::SECTION_MATERIALS)),
            $this->ctrl->getLinkTargetByClass('ilobjcoursegui', '')
        )->withStatus($this->determineStatus($done, self::SECTION_MATERIALS));

        // Step 3
        // course itest
        if (ilLOSettings::getInstanceByObjId($this->getParentObject()->getId())->worksWithInitialTest()) {
            $done = $this->getInitialTestStatus();
            $command = $this->getSettings()->hasSeparateInitialTests() ?
                    'testsOverview' :
                    'testOverview';
            $this->ctrl->setParameter($this->getCmdClass(), 'tt', ilLOSettings::TYPE_TEST_INITIAL);

            $steps[] = $workflow->step(
                $this->lng->txt('crs_objective_status_itest'),
                implode(" ", $this->getFailureMessages(self::SECTION_ITES)),
                $this->ctrl->getLinkTarget($this->getCmdClass(), $command)
            )->withStatus($this->determineStatus($done, self::SECTION_ITES));
        }

        // Step 4
        // course qtest
        $done = $this->getQualifiedTestStatus();
        $command = $this->getSettings()->hasSeparateQualifiedTests() ?
                'testsOverview' :
                'testOverview';
        $this->ctrl->setParameter($this->getCmdClass(), 'tt', ilLOSettings::TYPE_TEST_QUALIFIED);

        $steps[] = $workflow->step(
            $this->lng->txt('crs_objective_status_qtest'),
            implode(" ", $this->getFailureMessages(self::SECTION_QTEST)),
            $this->ctrl->getLinkTarget($this->getCmdClass(), $command)
        )->withStatus($this->determineStatus($done, self::SECTION_QTEST));

        // Step 5
        // course qtest
        $done = $this->getObjectivesStatus();
        $this->ctrl->setParameter($this->getCmdClass(), 'tt', $_GET["tt"]);

        $steps[] = $workflow->step(
            $this->lng->txt('crs_objective_status_objectives'),
            implode(" ", $this->getFailureMessages(self::SECTION_OBJECTIVES)),
            $this->ctrl->getLinkTarget($this->getCmdClass(), 'listObjectives')
        )->withStatus($this->determineStatus($done, self::SECTION_OBJECTIVES));

        $list = $workflow->linear(
            $this->lng->txt('crs_objective_status_configure'),
            $steps
        )
            ->withActive($this->determineActiveSection());

        $renderer = $DIC->ui()->renderer();
        return $renderer->render($list);
    }


    /**
     * Get error messages
     *
     * @param
     * @return array
     */
    public function getFailureMessages($a_section)
    {
        $mess = array();
        foreach ($this->getFailures($a_section) as $failure_code) {
            $mess[] = $this->lng->txt($failure_code);
        }
        return $mess;
    }

    /**
     * Determines workflow status of section
     *
     * @param bool $done
     * @param $section
     * @return int
     */
    public function determineStatus(bool $done, int $section) : int
    {
        if ($done) {
            return Step::SUCCESSFULLY;
        } elseif ($this->hasSectionErrors($section)) {
            return Step::UNSUCCESSFULLY;
        } else {
            if ($this->section == $section) {
                return Step::IN_PROGRESS;
            } else {
                return Step::NOT_STARTED;
            }
        }
    }

    /**
     * Determines active section position of workflow
     *
     * @return int
     */
    public function determineActiveSection() : int
    {
        $itest_enabled = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId())->worksWithInitialTest();
        $active_map = array(
            self::SECTION_SETTINGS => 0,
            self::SECTION_OBJECTIVES_NEW => 1,
            self::SECTION_MATERIALS => 2,
            self::SECTION_ITES => 3,
            self::SECTION_QTEST => $itest_enabled ? 4 : 3,
            self::SECTION_OBJECTIVES => $itest_enabled ? 5 : 4
        );

        return $active_map[$this->section];
    }

    /**
     * @param $a_section
     * @return bool
     */
    public function hasSectionErrors($a_section) : bool
    {
        return isset($this->error_by_section[$a_section]);
    }

    /**
     * Check if course is lo confgured
     * @return bool
     */
    protected function getSettingsStatus() : bool
    {
        return $this->getSettings()->settingsExist();
    }

    /**
     * Get objectives
     * @var bool $a_set_errors
     *
     * @return int
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
     * @param bool $a_set_errors
     * @return bool
     */
    protected function getMaterialsStatus($a_set_errors = true) : bool
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
     * @param bool $a_set_errors
     * @return boolean
     */
    protected function getInitialTestStatus($a_set_errors = true) : bool
    {
        if ($this->getSettings()->hasSeparateInitialTests()) {
            if (count($this->objectives) <= 0) {
                return false;
            }

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
                        $this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_tst_offline', true);
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
                $this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_tst_offline', true);
            }
            return false;
        }
        return true;
    }

    /**
     * Check status of qualified test
     * @param bool $a_set_errors
     * @return boolean
     */
    protected function getQualifiedTestStatus($a_set_errors = true) : bool
    {
        if ($this->getSettings()->hasSeparateQualifiedTests()) {
            if (count($this->objectives) <= 0) {
                return false;
            }

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
                        $this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_tst_offline', true);
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
                $this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_tst_offline', true);
            }
            return false;
        }
        return true;
    }

    /**
     * Check if questions are assigned
     * @param int $a_test_ref_id
     * @return bool
     */
    protected function lookupQuestionsAssigned($a_test_ref_id) : bool
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

    /**
     * @param bool $a_set_errors
     * @return bool
     */
    protected function getObjectivesStatus($a_set_errors = true) : bool
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
     * @return bool
     */
    protected function checkTestOnline($a_ref_id) : bool
    {
        include_once './Modules/Test/classes/class.ilObjTestAccess.php';
        return !ilObjTestAccess::_isOffline(ilObject::_lookupObjId($a_ref_id));
    }
}
