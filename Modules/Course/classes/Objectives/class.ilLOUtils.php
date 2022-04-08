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
class ilLOUtils
{

    /**
     * Check if objective is completed
     */
    public static function isCompleted(
        int $a_cont_oid,
        int $a_test_rid,
        int $a_objective_id,
        int $max_points,
        int $reached,
        int $limit_perc
    ) : bool {
        $settings = ilLOSettings::getInstanceByObjId($a_cont_oid);

        if (self::lookupRandomTest(ilObject::_lookupObjId($a_test_rid))) {
            if ($max_points === 0) {
                return true;
            } else {
                return ($reached / $max_points * 100) >= $limit_perc;
            }
        } else {
            $required_perc = self::lookupObjectiveRequiredPercentage(
                $a_cont_oid,
                $a_objective_id,
                $a_test_rid,
                $max_points
            );

            if ($max_points === 0) {
                return true;
            } else {
                return ($reached / $max_points * 100) >= $required_perc;
            }
        }
    }

    public static function lookupObjectiveRequiredPercentage(
        int $a_container_id,
        int $a_objective_id,
        int $a_test_ref_id,
        int $a_max_points
    ) : int {
        $settings = ilLOSettings::getInstanceByObjId($a_container_id);
        $assignments = ilLOTestAssignments::getInstance($a_container_id);
        $a_test_type = $assignments->getTypeByTest($a_test_ref_id);

        if ($assignments->isSeparateTest($a_test_ref_id)) {
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
            return ilLORandomTestQuestionPools::lookupLimit($a_container_id, $a_objective_id, $a_test_type);
        } else {
            return ilCourseObjectiveQuestion::loookupTestLimit(ilObject::_lookupObjId($tst_ref_id), $a_objective_id);
        }
    }

    public static function lookupMaxAttempts(int $a_container_id, int $a_objective_id, int $a_test_ref_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

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

    public static function lookupRandomTest(int $a_test_obj_id) : bool
    {
        return ilObjTest::_lookupRandomTest($a_test_obj_id);
    }

    /**
     * Lookup assigned qpl name (including taxonomy) by sequence
     */
    public static function lookupQplBySequence(int $a_test_ref_id, int $a_sequence_id) : string
    {
        global $DIC;

        if (!$a_sequence_id) {
            return '';
        }
        $tst = ilObjectFactory::getInstanceByRefId($a_test_ref_id, false);
        if (!$tst instanceof ilObjTest) {
            return '';
        }
        $list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $DIC->database(),
            $tst,
            new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
                $DIC->database(),
                $tst
            )
        );

        $list->loadDefinitions();

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

    protected static function buildQplTitleByDefinition(
        ilTestRandomQuestionSetSourcePoolDefinition $def,
        ilTestTaxonomyFilterLabelTranslater $trans
    ) : string {
        $title = $def->getPoolTitle();
        $filterTitle = array();
        $filterTitle[] = $trans->getTaxonomyFilterLabel($def->getMappedTaxonomyFilter());
        $filterTitle[] = $trans->getTypeFilterLabel($def->getTypeFilter());
        if (!empty($filterTitle)) {
            $title .= ' -> ' . implode(' / ', $filterTitle);
        }
        return $title;
    }

    public static function hasActiveRun(int $a_container_id, int $a_test_ref_id, int $a_objective_id) : bool
    {
        return false;
    }

    public static function getTestResultLinkForUser(int $a_test_ref_id, int $a_user_id) : string
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();
        $ilAccess = $DIC->access();

        if ($ilUser->getId() == ANONYMOUS_USER_ID) {
            return '';
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
                if (ilObjTestAccess::visibleUserResultExists($testObjId, $a_user_id)) {
                    $ilCtrl->setParameterByClass('ilObjTestGUI', 'ref_id', $a_test_ref_id);
                    $ctrlClasses = array('ilRepositoryGUI', 'ilObjTestGUI', 'ilTestResultsGUI');
                    $link = $ilCtrl->getLinkTargetByClass($ctrlClasses);
                    $ilCtrl->setParameterByClass('ilObjTestGUI', 'ref_id', '');
                    return $link;
                }
            } else {
                $testId = ilObjTest::_getTestIDFromObjectID($testObjId);
                if ($testId) {
                    $userActiveId = ilObjTest::_getActiveIdOfUser($a_user_id, $testId);
                    if ($userActiveId) {
                        $ilCtrl->setParameterByClass('ilTestEvaluationGUI', 'ref_id', $a_test_ref_id);
                        $ilCtrl->setParameterByClass('ilTestEvaluationGUI', 'active_id', $userActiveId);
                        $link = $ilCtrl->getLinkTargetByClass(array('ilRepositoryGUI',
                                                                    'ilObjTestGUI',
                                                                    'ilTestEvaluationGUI'
                        ), 'outParticipantsResultsOverview');
                        $ilCtrl->setParameterByClass('ilTestEvaluationGUI', 'ref_id', '');
                        $ilCtrl->setParameterByClass('ilTestEvaluationGUI', 'active_id', '');
                        return $link;
                    }
                }
            }
        }
        return '';
    }
}
