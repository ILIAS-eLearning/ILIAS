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
 * Test to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesTest
 */
class ilTestLP extends ilObjectLP
{
    private \ILIAS\Test\InternalRequestService $request;

    /**
     * @var \ilObjTest
     */
    protected $testObj;

    public function __construct(int $obj_id)
    {
        global $DIC;
        $this->request = $DIC->test()->internal()->request();

        parent::__construct($obj_id);
    }

    public static function getDefaultModes(bool $a_lp_active): array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_TEST_FINISHED,
            ilLPObjSettings::LP_MODE_TEST_PASSED
        );
    }

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_TEST_PASSED;
    }

    public function getValidModes(): array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_TEST_FINISHED,
            ilLPObjSettings::LP_MODE_TEST_PASSED
        );
    }

    public function isAnonymized(): bool
    {
        include_once './Modules/Test/classes/class.ilObjTest.php';
        return (bool) ilObjTest::_lookupAnonymity($this->obj_id);
    }

    /**
     * @param ilObjTest $test
     */
    public function setTestObject(\ilObjTest $test)
    {
        $this->testObj = $test;
    }

    protected function resetCustomLPDataForUserIds(array $a_user_ids, bool $a_recursive = true): void
    {
        /* @var ilObjTest $testOBJ */
        if ($this->testObj) {
            // #19247
            $testOBJ = $this->testObj;
        } else {
            require_once 'Services/Object/classes/class.ilObjectFactory.php';
            $testOBJ = ilObjectFactory::getInstanceByObjId($this->obj_id);
        }
        $testOBJ->removeTestResultsByUserIds($a_user_ids);

        // :TODO: there has to be a better way
        $test_ref_id = (int) $this->request->raw("ref_id");
        if ($this->testObj && $this->testObj->getRefId()) {
            $test_ref_id = $this->testObj->getRefId();
        }

        if ($test_ref_id) {
            require_once "Modules/Course/classes/Objectives/class.ilLOSettings.php";
            $course_obj_id = ilLOSettings::isObjectiveTest($test_ref_id);
            if ($course_obj_id) {
                // remove objective results data
                $lo_settings = ilLOSettings::getInstanceByObjId($course_obj_id);

                require_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";
                include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
                ilLOUserResults::deleteResultsFromLP(
                    $course_obj_id,
                    $a_user_ids,
                    ($lo_settings->getInitialTest() == $test_ref_id),
                    ($lo_settings->getQualifiedTest() == $test_ref_id),
                    ilLOTestAssignments::lookupObjectivesForTest($test_ref_id)
                );

                // refresh LP - see ilLPStatusWrapper::_updateStatus()
                require_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
                $lp_status = ilLPStatusFactory::_getInstance($course_obj_id);
                if (strtolower(get_class($lp_status)) != "illpstatus") {
                    foreach ($a_user_ids as $user_id) {
                        $lp_status->_updateStatus($course_obj_id, $user_id);
                    }
                }
            }
        }
    }

    protected static function isLPMember(array &$a_res, int $a_usr_id, array $a_obj_ids): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // if active id
        $set = $ilDB->query("SELECT tt.obj_fi" .
            " FROM tst_active ta" .
            " JOIN tst_tests tt ON (ta.test_fi = tt.test_id)" .
            " WHERE " . $ilDB->in("tt.obj_fi", (array) $a_obj_ids, false, "integer") .
            " AND ta.user_fi = " . $ilDB->quote($a_usr_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $a_res[$row["obj_fi"]] = true;
        }

        return true;
    }
}
