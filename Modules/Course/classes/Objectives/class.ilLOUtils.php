<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings for LO courses
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOUtils
{
    
    /**
     * Check if objective is completed
     */
    public static function isCompleted($a_cont_oid, $a_test_rid, $a_objective_id, $max_points, $reached, $limit_perc)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        $settings = ilLOSettings::getInstanceByObjId($a_cont_oid);
        
        if (self::lookupRandomTest(ilObject::_lookupObjId($a_test_rid))) {
            if (!$max_points) {
                return true;
            } else {
                return ($reached / $max_points * 100) >= $limit_perc;
            }
        } else {
            $required_perc = self::lookupObjectiveRequiredPercentage($a_cont_oid, $a_objective_id, $a_test_rid, $max_points);
            
            if (!$max_points) {
                return true;
            } else {
                return ($reached / $max_points * 100) >= $required_perc;
            }
        }
    }

    /**
     *
     * @param type $a_container_id
     * @param type $a_objective_id
     * @param type $a_test_type
     */
    public static function lookupObjectiveRequiredPercentage($a_container_id, $a_objective_id, $a_test_ref_id, $a_max_points)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        $settings = ilLOSettings::getInstanceByObjId($a_container_id);
        
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
        $assignments = ilLOTestAssignments::getInstance($a_container_id);
        $a_test_type = $assignments->getTypeByTest($a_test_ref_id);
        
        if ($assignments->isSeparateTest($a_test_ref_id)) {
            include_once './Services/Object/classes/class.ilObjectFactory.php';
            $factory = new ilObjectFactory();
            $tst = $factory->getInstanceByRefId($a_test_ref_id, false);
            if ($tst instanceof ilObjTest) {
                $schema = $tst->getMarkSchema();
                foreach ($schema->getMarkSteps() as $mark) {
                    if ($mark->getPassed()) {
                        return (int) $mark->getMinimumLevel();
                    }
                }
            }
        }
        
        
        
        $tst_ref_id = $a_test_ref_id;
        if (self::lookupRandomTest(ilObject::_lookupObjId($tst_ref_id))) {
            include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
            return (int) ilLORandomTestQuestionPools::lookupLimit($a_container_id, $a_objective_id, $a_test_type);
        } else {
            include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
            $limit = ilCourseObjectiveQuestion::loookupTestLimit(ilObject::_lookupObjId($tst_ref_id), $a_objective_id);
            return $limit;
        }
    }
    
    /**
     *
     * @param int $a_container_id
     * @param int $a_objective_id
     * @param int $a_ref_id
     * @return  int $a_passes
     */
    public static function lookupMaxAttempts($a_container_id, $a_objective_id, $a_test_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
        /**
         * @var ilLOTestAssignments
         */
        $assignments = ilLOTestAssignments::getInstance($a_container_id);
        if (!$assignments->isSeparateTest($a_test_ref_id)) {
            // no limit of tries for tests assigned to multiple objectives.
            return 0;
        }
        
        $query = 'SELECT nr_of_tries FROM tst_tests ' .
            'WHERE obj_fi = ' . $ilDB->quote(ilObject::_lookupObjId($a_test_ref_id), 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->nr_of_tries;
        }
        return 0;
    }
    
    
    /**
     * Check if test is a random test
     * @param type $a_test_obj_id
     * @return bool
     */
    public static function lookupRandomTest($a_test_obj_id)
    {
        include_once './Modules/Test/classes/class.ilObjTest.php';
        return ilObjTest::_lookupRandomTest($a_test_obj_id);
    }
    
    /**
     * Lookup assigned qpl name (including taxonomy) by sequence
     * @param type $a_test_ref_id
     * @param type $a_sequence_id
     * @return string
     */
    public static function lookupQplBySequence($a_test_ref_id, $a_sequence_id)
    {
        if (!$a_sequence_id) {
            return '';
        }
        $tst = ilObjectFactory::getInstanceByRefId($a_test_ref_id, false);
        if (!$tst instanceof ilObjTest) {
            return '';
        }
        include_once './Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
        include_once './Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
        $list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $GLOBALS['DIC']['ilDB'],
            $tst,
            new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
                $GLOBALS['DIC']['ilDB'],
                $tst
            )
        );
                
        $list->loadDefinitions();

        include_once './Modules/Test/classes/class.ilTestTaxonomyFilterLabelTranslater.php';
        $translator = new ilTestTaxonomyFilterLabelTranslater($GLOBALS['DIC']['ilDB']);
        $translator->loadLabels($list);
        
        $title = '';
        foreach ($list as $definition) {
            if ($definition->getId() != $a_sequence_id) {
                continue;
            }
            $title = self::buildQplTitleByDefinition($definition, $translator);
        }
        return $title;
    }
    
    /**
     * build title by definition
     * @param ilTestRandomQuestionSetSourcePoolDefinition $def
     */
    protected static function buildQplTitleByDefinition(ilTestRandomQuestionSetSourcePoolDefinition $def, ilTestTaxonomyFilterLabelTranslater $trans)
    {
        $title = $def->getPoolTitle();
        // fau: taxFilter/typeFilter - get title for extended filter conditions
        $filterTitle = array();
        $filterTitle[] = $trans->getTaxonomyFilterLabel($def->getMappedTaxonomyFilter());
        $filterTitle[] = $trans->getTypeFilterLabel($def->getTypeFilter());
        if (!empty($filterTitle)) {
            $title .= ' -> ' . implode(' / ', $filterTitle);
        }
        #$tax_id = $def->getMappedFilterTaxId();
        #if($tax_id)
        #{
        #	$title .= (' -> '. $trans->getTaxonomyTreeLabel($tax_id));
        #}
        #$tax_node = $def->getMappedFilterTaxNodeId();
        #if($tax_node)
        #{
        #	$title .= (' -> ' .$trans->getTaxonomyNodeLabel($tax_node));
        #}
        // fau.
        return $title;
    }
    
    public static function hasActiveRun($a_container_id, $a_test_ref_id, $a_objective_id)
    {
        return false;
        
        // check if pass exists
        include_once './Modules/Test/classes/class.ilObjTest.php';
        if (
            !ilObjTest::isParticipantsLastPassActive(
                $a_test_ref_id,
                $GLOBALS['DIC']['ilUser']->getId()
            )
        ) {
            return false;
        }

        // check if multiple pass exists
        include_once './Modules/Course/classes/Objectives/class.ilLOTestRun.php';
        $last_objectives = ilLOTestRun::lookupObjectives(
            $a_container_id,
            $GLOBALS['DIC']['ilUser']->getId(),
            ilObject::_lookupObjId($a_test_ref_id)
        );
        
        if (count((array) $last_objectives) and in_array((int) $a_objective_id, (array) $last_objectives)) {
            return true;
        }
        return false;
    }
    
    public static function getTestResultLinkForUser($a_test_ref_id, $a_user_id)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        
        if ($ilUser->getId() == ANONYMOUS_USER_ID) {
            return;
        }
                
        $valid = $tutor = false;
        if ($a_user_id == $ilUser->getId()) {
            $valid = $ilAccess->checkAccess('read', '', $a_test_ref_id);
        }
        if (!$valid) {
            $valid = $ilAccess->checkAccess('write', '', $a_test_ref_id);
            $tutor = true;
        }
        if ($valid) {
            $testObjId = ilObject::_lookupObjId($a_test_ref_id);
            if (!$tutor) {
                require_once 'Modules/Test/classes/class.ilObjTestAccess.php';
                if (ilObjTestAccess::visibleUserResultExists($testObjId, $a_user_id)) {
                    $ilCtrl->setParameterByClass('ilObjTestGUI', 'ref_id', $a_test_ref_id);
                    $ctrlClasses = array('ilRepositoryGUI', 'ilObjTestGUI', 'ilTestResultsGUI');
                    $link = $ilCtrl->getLinkTargetByClass($ctrlClasses);
                    $ilCtrl->setParameterByClass('ilObjTestGUI', 'ref_id', '');
                    return $link;
                }
            } else {
                include_once 'Modules/Test/classes/class.ilObjTest.php';
                $testId = ilObjTest::_getTestIDFromObjectID($testObjId);
                if ($testId) {
                    $userActiveId = ilObjTest::_getActiveIdOfUser($a_user_id, $testId);
                    if ($userActiveId) {
                        $ilCtrl->setParameterByClass('ilTestEvaluationGUI', 'ref_id', $a_test_ref_id);
                        $ilCtrl->setParameterByClass('ilTestEvaluationGUI', 'active_id', $userActiveId);
                        $link = $ilCtrl->getLinkTargetByClass(array('ilRepositoryGUI', 'ilObjTestGUI', 'ilTestEvaluationGUI'), 'outParticipantsResultsOverview');
                        $ilCtrl->setParameterByClass('ilTestEvaluationGUI', 'ref_id', '');
                        $ilCtrl->setParameterByClass('ilTestEvaluationGUI', 'active_id', '');
                        return $link;
                    }
                }
            }
        }
    }
}
