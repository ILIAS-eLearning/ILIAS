<?php declare(strict_types=1);

namespace ILIAS\MyStaff;

use ILIAS\DI\Container;
use ilOrgUnitOperation;

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
 ********************************************************************
 */
class ilMyStaffCachedAccessDecorator extends ilMyStaffAccess
{
    private Container $dic;
    private ilMyStaffAccess $origin;

    public function __construct(Container $dic, ilMyStaffAccess $origin)
    {
        $this->dic = $dic;
        $this->origin = $origin;
    }

    final public function hasCurrentUserAccessToMyStaff() : bool
    {
        static $cache = null;

        if (null === $cache) {
            $cache = (
                (!$this->dic->user()->isAnonymous() && $this->dic->user()->getId() > 0) &&
                $this->origin->hasCurrentUserAccessToMyStaff()
            );
        }

        return $cache;
    }

    final public function hasCurrentUserAccessToCertificates() : bool
    {
        static $cache = null;

        if (null === $cache) {
            $cache = $this->origin->hasCurrentUserAccessToCertificates();
        }

        return $cache;
    }

    final public function hasCurrentUserAccessToCompetences() : bool
    {
        static $cache = null;

        if (null === $cache) {
            $cache = $this->origin->hasCurrentUserAccessToCompetences();
        }

        return $cache;
    }

    final public function hasCurrentUserAccessToUser($usr_id = 0) : bool
    {
        static $cache = [];

        if (!isset($cache[$usr_id])) {
            $cache[$usr_id] = $this->origin->hasCurrentUserAccessToUser($usr_id);
        }

        return $cache[$usr_id];
    }

    final public function hasCurrentUserAccessToLearningProgressInObject($ref_id = 0) : bool
    {
        static $cache = [];

        if (!isset($cache[$ref_id])) {
            $cache[$ref_id] = $this->origin->hasCurrentUserAccessToLearningProgressInObject($ref_id);
        }

        return $cache[$ref_id];
    }

    final  public function hasCurrentUserAccessToCourseLearningProgressForAtLeastOneUser() : bool
    {
        static $cache = null;

        if (null === $cache) {
            $cache = $this->origin->hasCurrentUserAccessToCourseLearningProgressForAtLeastOneUser();
        }

        return $cache;
    }

    public function hasPositionDefaultPermissionForOperationInContext(
        int $position_id,
        int $operation_id,
        int $context_id
    ) : bool {
        static $cache = [];

        $cache_key = implode('#', [$position_id, $operation_id, $context_id]);

        if (!isset($cache[$cache_key])) {
            $cache[$cache_key] = $this->origin->hasPositionDefaultPermissionForOperationInContext(
                $position_id,
                $operation_id,
                $context_id
            );
        }

        return $cache[$cache_key];
    }

    final public function countOrgusOfUserWithAtLeastOneOperation($user_id) : int
    {
        return $this->origin->countOrgusOfUserWithAtLeastOneOperation($user_id);
    }

    final public function countOrgusOfUserWithOperationAndContext(
        $user_id,
        $org_unit_operation_string = self::DEFAULT_ORG_UNIT_OPERATION,
        $context = self::DEFAULT_CONTEXT
    ) : int {
        return $this->origin->countOrgusOfUserWithOperationAndContext(
            $user_id,
            $org_unit_operation_string,
            $context
        );
    }

    final public function getUsersForUserOperationAndContext(
        $user_id,
        $org_unit_operation_string = self::DEFAULT_ORG_UNIT_OPERATION,
        $context = self::DEFAULT_CONTEXT,
        $tmp_table_name_prefix = self::TMP_DEFAULT_TABLE_NAME_PREFIX_IL_OBJ_USER_MATRIX
    ) : array {
        return $this->origin->getUsersForUserOperationAndContext(
            $user_id,
            $org_unit_operation_string,
            $context,
            $tmp_table_name_prefix
        );
    }

    final  public function getUsersForUserPerPosition($user_id) : array
    {
        return $this->origin->getUsersForUserPerPosition($user_id);
    }

    final public function getUsersForUser($user_id, ?int $position_id = null) : array
    {
        return $this->origin->getUsersForUser($user_id, $position_id);
    }

    final public function getIdsForUserAndOperation(int $user_id, string $operation, bool $return_ref_id = false) : array
    {
        return $this->origin->getIdsForUserAndOperation(
            $user_id,
            $operation,
            $return_ref_id
        );
    }

    final public function getIdsForPositionAndOperation(int $position_id, string $operation, bool $return_ref_id) : array
    {
        return $this->origin->getIdsForPositionAndOperation(
            $position_id,
            $operation,
            $return_ref_id
        );
    }

    final public function getIdsForPositionAndOperationAndContext(
        int $position_id,
        string $operation,
        string $context,
        bool $return_ref_id
    ) : array {
        return $this->origin->getIdsForPositionAndOperationAndContext(
            $position_id,
            $operation,
            $context,
            $return_ref_id
        );
    }

    final public function getIlobjectsAndUsersForUserOperationAndContext(
        $user_id,
        $org_unit_operation_string = self::DEFAULT_ORG_UNIT_OPERATION,
        $context = self::DEFAULT_CONTEXT
    ) : array {
        return $this->origin->getIlobjectsAndUsersForUserOperationAndContext(
            $user_id,
            $org_unit_operation_string,
            $context
        );
    }

    final  public function buildTempTableIlobjectsUserMatrixForUserOperationAndContext(
        $user_id,
        $org_unit_operation_string = self::DEFAULT_ORG_UNIT_OPERATION,
        $context = self::DEFAULT_CONTEXT,
        $temporary_table_name_prefix = self::TMP_DEFAULT_TABLE_NAME_PREFIX_IL_OBJ_USER_MATRIX
    ) : string {
        return $this->origin->buildTempTableIlobjectsUserMatrixForUserOperationAndContext(
            $user_id,
            $org_unit_operation_string,
            $context,
            $temporary_table_name_prefix
        );
    }

    final  public function buildTempTableIlobjectsSpecificPermissionSetForOperationAndContext(
        $org_unit_operation_string = self::DEFAULT_ORG_UNIT_OPERATION,
        $context = self::DEFAULT_CONTEXT,
        $temporary_table_name_prefix = self::TMP_DEFAULT_TABLE_NAME_PREFIX_IL_OBJ_SPEC_PERMISSIONS
    ) : string {
        return $this->origin->buildTempTableIlobjectsSpecificPermissionSetForOperationAndContext(
            $org_unit_operation_string,
            $context,
            $temporary_table_name_prefix
        );
    }

    final  public function buildTempTableIlobjectsDefaultPermissionSetForOperationAndContext(
        $org_unit_operation_string = ilOrgUnitOperation::OP_ACCESS_ENROLMENTS,
        $context = self::DEFAULT_CONTEXT,
        $temporary_table_name_prefix = self::TMP_DEFAULT_TABLE_NAME_PREFIX_IL_OBJ_DEFAULT_PERMISSIONS
    ) : string {
        return $this->origin->buildTempTableIlobjectsDefaultPermissionSetForOperationAndContext(
            $org_unit_operation_string,
            $context,
            $temporary_table_name_prefix
        );
    }

    final  public function buildTempTableIlorgunitDefaultPermissionSetForOperationAndContext(
        $org_unit_operation_string = self::DEFAULT_ORG_UNIT_OPERATION,
        $context = self::DEFAULT_CONTEXT,
        $temporary_table_name_prefix = self::TMP_DEFAULT_TABLE_NAME_PREFIX_IL_ORGU_DEFAULT_PERMISSIONS
    ) : string {
        return $this->origin->buildTempTableIlorgunitDefaultPermissionSetForOperationAndContext(
            $org_unit_operation_string,
            $context,
            $temporary_table_name_prefix
        );
    }

    final  public function buildTempTableCourseMemberships(
        $temporary_table_name_prefix = self::TMP_DEFAULT_TABLE_NAME_PREFIX_CRS_MEMBERS,
        array $only_courses_of_user_ids = []
    ) : string {
        return $this->origin->buildTempTableCourseMemberships(
            $temporary_table_name_prefix,
            $only_courses_of_user_ids
        );
    }

    public function buildTempTableOrguMemberships(
        $temporary_table_name_prefix = self::TMP_DEFAULT_TABLE_NAME_PREFIX_ORGU_MEMBERS,
        array $only_orgus_of_user_ids = []
    ) : string {
        return $this->origin->buildTempTableOrguMemberships(
            $temporary_table_name_prefix,
            $only_orgus_of_user_ids
        );
    }

    final public function dropTempTable($temporary_table_name) : void
    {
        $this->origin->dropTempTable($temporary_table_name);
    }
}
