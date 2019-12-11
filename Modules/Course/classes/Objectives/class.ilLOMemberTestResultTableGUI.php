<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';

/**
* Class ilLOmemberTestResultTableGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* $Id$
*/
class ilLOMemberTestResultTableGUI extends ilTable2GUI
{
    private $settings = null;
    private $parent_container = null;
    
    private $current_user = 0;
    
    
    /**
     * Constructor
     * @param type $a_parent_obj
     * @param type $a_parent_cmd
     * @param type $a_template_context
     */
    public function __construct($a_parent_obj_gui, $a_parent_obj, $a_parent_cmd)
    {
        $this->parent_container = $a_parent_obj;
        
        $this->setId('lomemtstres_' . $a_parent_obj->getId());
        parent::__construct($a_parent_obj_gui, $a_parent_cmd);
        
        
        $this->settings = ilLOSettings::getInstanceByObjId($a_parent_obj->getId());
    }
    
    /**
     *
     * @return ilObject
     */
    public function getParentContainer()
    {
        return $this->parent_container;
    }
    
    /**
     * return ilLOSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * Set user id
     * @param type $a_id
     */
    public function setUserId($a_id)
    {
        $this->current_user = $a_id;
    }
    
    
    /**
     * Get current user id
     */
    public function getUserId()
    {
        return $this->current_user;
    }
    
    /**
     * Init Table
     */
    public function init()
    {
        $name = ilObjUser::_lookupName($this->getUserId());
        
        if (strlen($name['firstname']) and strlen($name['lastname'])) {
            $name_string = $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']';
        } elseif (strlen($name['lastname'])) {
            $name_string = $name['lastname'] . ' [' . $name['login'] . ']';
        } else {
            $name_string = $name['login'];
        }

        $this->setTitle($GLOBALS['DIC']['lng']->txt('crs_loc_test_results_of') . ' ' . $name_string);
        
        $this->addColumn($GLOBALS['DIC']['lng']->txt('crs_objectives'), 'title', '50%');
        
        if ($this->getSettings()->worksWithInitialTest()) {
            $this->addColumn($GLOBALS['DIC']['lng']->txt('crs_loc_itest_info'), 'it', '25%');
            $this->addColumn($GLOBALS['DIC']['lng']->txt('crs_loc_qtest_info'), 'qt', '25%');
        } else {
            $this->addColumn($GLOBALS['DIC']['lng']->txt('crs_loc_qtest_info'), 'qt', '25%');
        }
        
        $this->setRowTemplate('tpl.crs_objectives_usr_result_row.html', 'Modules/Course');
        
        $this->disable('sort');
        $this->disable('num_info');
    }
    

    /**
     * Fill table rows
     * @param type $set
     */
    public function fillRow($set)
    {
        $this->tpl->setVariable('VAL_TITLE', $set['title']);
        if ($this->getSettings()->worksWithInitialTest()) {
            if ($set['has_result_it']) {
                $this->tpl->setCurrentBlock('it_has_result');
                $this->tpl->setVariable('IT_LINK', $set['link_it']);
                $this->tpl->setVariable('IT_VAL', $set['res_it'] . '%');
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setVariable('IT_NO_RES', '-');
            }
        }
        
        if ($set['has_result_qt']) {
            $this->tpl->setCurrentBlock('qt_has_result');
            $this->tpl->setVariable('QT_LINK', $set['link_qt']);
            $this->tpl->setVariable('QT_VAL', $set['res_qt'] . '%');
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setVariable('QT_NO_RES', '-');
        }
    }
    
    /**
     * Parse user results for table
     * @return type
     */
    public function parse()
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
        
        include_once './Modules/Course/classes/class.ilCourseObjective.php';
        $objective_ids = ilCourseObjective::_getObjectiveIds($this->getParentContainer()->getId(), true);

        foreach ((array) $objective_ids as $objective_id) {
            $objective = array();
            $objective['id'] = $objective_id;
            $objective['title'] = ilCourseObjective::lookupObjectiveTitle($objective_id);
            
            
            if ($this->getSettings()->worksWithInitialTest()) {
                $results_it = ilLOUserResults::lookupResult(
                    $this->getParentContainer()->getId(),
                    $this->getUserId(),
                    $objective_id,
                    ilLOSettings::TYPE_TEST_INITIAL
                );
                $objective['tries_it'] = $results_it['tries'];
                $objective['res_it'] = $results_it['result_perc'];
                $objective['link_it'] = $this->createTestResultLink(ilLOSettings::TYPE_TEST_INITIAL, $objective_id);
                $objective['has_result_it'] = (bool) $results_it['has_result'];
            }
            $results_qt = ilLOUserResults::lookupResult(
                $this->getParentContainer()->getId(),
                $this->getUserId(),
                $objective_id,
                ilLOSettings::TYPE_TEST_QUALIFIED
            );
            $objective['tries_qt'] = $results_qt['tries'];
            $objective['res_qt'] = $results_qt['result_perc'];
            $objective['link_qt'] = $this->createTestResultLink(ilLOSettings::TYPE_TEST_QUALIFIED, $objective_id);
            $objective['has_result_qt'] = (bool) $results_qt['has_result'];

            $tbl_data[] = $objective;
        }
        
        return $this->setData($tbl_data);
    }
    
    /**
     * Create test result link
     * @param type $a_type
     * @param type $a_objective_id
     */
    protected function createTestResultLink($a_type, $a_objective_id)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
        $assignments = ilLOTestAssignments::getInstance($this->getParentContainer()->getId());
        
        $test_ref_id = $assignments->getTestByObjective($a_objective_id, $a_type);
        if (!$test_ref_id) {
            return '';
        }
        include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';
        return ilLOUtils::getTestResultLinkForUser($test_ref_id, $this->getUserId());
    }
}
