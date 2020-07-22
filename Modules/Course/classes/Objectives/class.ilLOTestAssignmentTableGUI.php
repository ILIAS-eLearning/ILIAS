<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Modules/Course/exceptions/class.ilLOInvalidConfiguationException.php';

/**
* Class ilLOTestAssignmentTableGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* $Id$
*
*
*/
class ilLOTestAssignmentTableGUI extends ilTable2GUI
{
    const TYPE_MULTIPLE_ASSIGNMENTS = 1;
    const TYPE_SINGLE_ASSIGNMENTS = 2;
    
    private $test_type = 0;
    private $assignment_type = self::TYPE_SINGLE_ASSIGNMENTS;
    private $settings = null;
    private $container_id = 0;
    
    
    /**
     * Constructor
     * @param ilObject $a_parent_obj
     * @param type $a_parent_cmd
     * @param type $a_test_type
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_container_id, $a_test_type, $a_assignment_type = self::TYPE_SINGLE_ASSIGNMENTS)
    {
        $this->test_type = $a_test_type;
        $this->assignment_type = $a_assignment_type;
        $this->container_id = $a_container_id;
        
        $this->setId('obj_loc_' . $a_container_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->settings = ilLOSettings::getInstanceByObjId($a_container_id);
        $this->initTitle();
        $this->setTopCommands(false);
    }
    
    public function initTitle()
    {
        switch ($this->test_type) {
            case ilLOSettings::TYPE_TEST_INITIAL:
                if ($this->getAssignmentType() == self::TYPE_SINGLE_ASSIGNMENTS) {
                    if ($this->getSettings()->isInitialTestQualifying()) {
                        $this->setTitle($this->lng->txt('crs_loc_settings_tbl_its_q_all'));
                    } else {
                        $this->setTitle($this->lng->txt('crs_loc_settings_tbl_its_nq_all'));
                    }
                } else {
                    if ($this->getSettings()->isInitialTestQualifying()) {
                        $this->setTitle($this->lng->txt('crs_loc_settings_tbl_it_q'));
                    } else {
                        $this->setTitle($this->lng->txt('crs_loc_settings_tbl_it_nq'));
                    }
                }
                break;
                
                
            case ilLOSettings::TYPE_TEST_QUALIFIED:
                if ($this->getAssignmentType() == self::TYPE_SINGLE_ASSIGNMENTS) {
                    $this->setTitle($this->lng->txt('crs_loc_settings_tbl_qts_all'));
                } else {
                    $this->setTitle($this->lng->txt('crs_loc_settings_tbl_qt'));
                }
                break;
        }
    }
    
    /**
     * Get settings
     * @return ilLOSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
    
    public function getAssignmentType()
    {
        return $this->assignment_type;
    }
    
    /**
     * Init table
     */
    public function init()
    {
        $this->addColumn('', '', '20px');
        $this->addColumn($this->lng->txt('title'), 'title');
        
        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $this->addColumn($this->lng->txt('crs_objectives'), 'objective');
        }
        
        $this->addColumn($this->lng->txt('crs_loc_tbl_tst_type'), 'ttype');
        $this->addColumn($this->lng->txt('crs_loc_tbl_tst_qst_qpl'), 'qstqpl');
        
             
        $this->setRowTemplate("tpl.crs_loc_tst_row.html", "Modules/Course");
        $this->setFormAction($GLOBALS['DIC']['ilCtrl']->getFormAction($this->getParentObject()));
        
        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $this->addMultiCommand('confirmDeleteTests', $this->lng->txt('crs_loc_delete_assignment'));
            $this->setDefaultOrderField('objective');
            $this->setDefaultOrderDirection('asc');
        } else {
            $this->addMultiCommand('confirmDeleteTest', $this->lng->txt('crs_loc_delete_assignment'));
            $this->setDefaultOrderField('title');
            $this->setDefaultOrderDirection('asc');
        }
    }
    
    /**
     *
     * @param type $set
     */
    public function fillRow($set)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $this->tpl->setVariable('VAL_ID', $set['assignment_id']);
        } else {
            $this->tpl->setVariable('VAL_ID', $set['ref_id']);
        }
        $this->tpl->setVariable('VAL_TITLE', $set['title']);
        include_once './Services/Link/classes/class.ilLink.php';
        
        $ilCtrl->setParameterByClass('ilobjtestgui', 'ref_id', $set['ref_id']);
        $ilCtrl->setParameterByClass('ilobjtestgui', 'cmd', 'questionsTabGateway');
        $this->tpl->setVariable(
            'TITLE_LINK',
            $ilCtrl->getLinkTargetByClass('ilobjtestgui')
        );
        
        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            $this->tpl->setCurrentBlock('objectives');
            $this->tpl->setVariable('VAL_OBJECTIVE', (string) $set['objective']);
            $this->tpl->parseCurrentBlock();
        }
                
        
        
        #$this->tpl->setVariable('TITLE_LINK',ilLink::_getLink($set['ref_id']));
        if (strlen($set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $set['description']);
        }

        switch ($set['ttype']) {
            case ilObjTest::QUESTION_SET_TYPE_FIXED:
                $type = $this->lng->txt('tst_question_set_type_fixed');
                break;
            
            case ilObjTest::QUESTION_SET_TYPE_RANDOM:
                $type = $this->lng->txt('tst_question_set_type_random');
                break;
        }
        
        $this->tpl->setVariable('VAL_TTYPE', $type);
        $this->tpl->setVariable('VAL_QST_QPL', $set['qst_info']);
        
        if (isset($set['qpls']) && is_array($set['qpls']) && count($set['qpls']) > 0) {
            foreach ($set['qpls'] as $title) {
                $this->tpl->setCurrentBlock('qpl');
                $this->tpl->setVariable('MAT_TITLE', $title);
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->touchBlock('ul_begin');
            $this->tpl->touchBlock('ul_end');
        }
    }
    
    public function parseMultipleAssignments()
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
        $assignments = ilLOTestAssignments::getInstance($this->container_id);
        
        $available = $assignments->getAssignmentsByType($this->test_type);
        $data = array();
        foreach ($available as $assignment) {
            try {
                $tmp = $this->doParse($assignment->getTestRefId(), $assignment->getObjectiveId());
            } catch (ilLOInvalidConfigurationException $e) {
                $assignment->delete();
                continue;
            }
            if ($tmp) {
                // add assignment id
                $tmp['assignment_id'] = $assignment->getAssignmentId();
                $data[] = $tmp;
            }
        }
        
        $this->setData($data);
    }
    
    /**
     * Parse single test assignment
     * @param type $a_tst_ref_id
     * @return boolean
     */
    public function parse($a_tst_ref_id)
    {
        $this->setData(array($this->doParse($a_tst_ref_id)));
        return true;
    }
    
    /**
     * Parse test
     * throws ilLOInvalidConfigurationException in case assigned test cannot be found.
     */
    protected function doParse($a_tst_ref_id, $a_objective_id = 0)
    {
        include_once './Modules/Test/classes/class.ilObjTest.php';
        $tst = ilObjectFactory::getInstanceByRefId($a_tst_ref_id, false);
        
        if (!$tst instanceof ilObjTest) {
            throw new ilLOInvalidConfigurationException('No valid test given');
        }
        $tst_data['ref_id'] = $tst->getRefId();
        $tst_data['title'] = $tst->getTitle();
        $tst_data['description'] = $tst->getLongDescription();
        $tst_data['ttype'] = $tst->getQuestionSetType();

        
        if ($this->getAssignmentType() == self::TYPE_MULTIPLE_ASSIGNMENTS) {
            include_once './Modules/Course/classes/class.ilCourseObjective.php';
            $tst_data['objective'] = ilCourseObjective::lookupObjectiveTitle($a_objective_id);
        }
        
        switch ($tst->getQuestionSetType()) {
            case ilObjTest::QUESTION_SET_TYPE_FIXED:
                $tst_data['qst_info'] = $this->lng->txt('crs_loc_tst_num_qst');
                $tst_data['qst_info'] .= (' ' . count($tst->getAllQuestions()));
                break;
            
            default:
                // get available assiged question pools
                include_once './Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
                include_once './Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
                
                $list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
                    $GLOBALS['DIC']['ilDB'],
                    $tst,
                    new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
                        $GLOBALS['DIC']['ilDB'],
                        $tst
                    )
                );
                
                $list->loadDefinitions();
                
                // tax translations
                include_once './Modules/Test/classes/class.ilTestTaxonomyFilterLabelTranslater.php';
                $translater = new ilTestTaxonomyFilterLabelTranslater($GLOBALS['DIC']['ilDB']);
                $translater->loadLabels($list);
                
                $tst_data['qst_info'] = $this->lng->txt('crs_loc_tst_qpls');
                $num = 0;
                foreach ($list as $definition) {
                    /** @var ilTestRandomQuestionSetSourcePoolDefinition[] $definition */
                    $title = $definition->getPoolTitle();
                    // fau: taxFilter/typeFilter - get title for extended filter conditions
                    $filterTitle = array();
                    $filterTitle[] = $translater->getTaxonomyFilterLabel($definition->getMappedTaxonomyFilter());
                    $filterTitle[] = $translater->getTypeFilterLabel($definition->getTypeFilter());
                    if (!empty($filterTitle)) {
                        $title .= ' -> ' . implode(' / ', $filterTitle);
                    }
                    #$tax_id = $definition->getMappedFilterTaxId();
                    #if($tax_id)
                    #{
                    #	$title .= (' -> '. $translater->getTaxonomyTreeLabel($tax_id));
                    #}
                    #$tax_node = $definition->getMappedFilterTaxNodeId();
                    #if($tax_node)
                    #{
                    #	$title .= (' -> ' .$translater->getTaxonomyNodeLabel($tax_node));
                    #}
                    // fau.
                    $tst_data['qpls'][] = $title;
                    ++$num;
                }
                if (!$num) {
                    $tst_data['qst_info'] .= (' ' . (int) 0);
                }
                break;
        }
        return $tst_data;
    }
}
