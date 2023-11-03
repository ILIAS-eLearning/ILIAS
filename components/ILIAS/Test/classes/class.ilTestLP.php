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

declare(strict_types=1);

use ILIAS\Test\InternalRequestService;

/**
 * Test to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package components\ILIASTest
 */
class ilTestLP extends ilObjectLP
{
    private InternalRequestService $request;
    protected ?ilObjTest $test_object = null;

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
        if ($this->test_object === null) {
            $this->setTestObject(ilObjectFactory::getInstanceByObjId($this->obj_id));
        }
        return (bool) $this->test_object->getAnonymity();
    }

    /**
     * @param ilObjTest $test
     */
    public function setTestObject(\ilObjTest $test)
    {
        $this->test_object = $test;
    }

    protected function resetCustomLPDataForUserIds(array $a_user_ids, bool $a_recursive = true): void
    {
        /* @var ilObjTest $testOBJ */
        if ($this->test_object) {
            // #19247
            $testOBJ = $this->test_object;
        } else {
            $testOBJ = ilObjectFactory::getInstanceByObjId($this->obj_id);
        }
        $testOBJ->removeTestResultsByUserIds($a_user_ids);

        // :TODO: there has to be a better way
        $test_ref_id = (int) $this->request->raw("ref_id");
        if ($this->test_object && $this->test_object->getRefId()) {
            $test_ref_id = $this->test_object->getRefId();
        }

        if ($test_ref_id) {
            $course_obj_id = ilLOTestAssignments::lookupContainerForTest($test_ref_id);
            if ($course_obj_id) {
                // remove objective results data
                $lo_assignments = ilLOTestAssignments::getInstance($course_obj_id);
                ilLOUserResults::deleteResultsFromLP(
                    $course_obj_id,
                    $a_user_ids,
                    $lo_assignments->getTypeByTest($test_ref_id) === ilLOSettings::TYPE_TEST_INITIAL,
                    $lo_assignments->getTypeByTest($test_ref_id) === ilLOSettings::TYPE_TEST_QUALIFIED,
                    ilLOTestAssignments::lookupObjectivesForTest($test_ref_id)
                );
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
