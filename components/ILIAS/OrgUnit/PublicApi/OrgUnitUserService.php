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

namespace OrgUnit\PublicApi;

use OrgUnit\User\ilOrgUnitUser;
use OrgUnit\User\ilOrgUnitUserRepository;

class OrgUnitUserService
{
    public function __construct()
    {
    }

    /**
     * @param int[] $user_ids
     * @return ilOrgUnitUser[]
     */
    public function getUsers(array $user_ids, bool $with_superios = false, bool $with_positions = false): array
    {
        $org_unit_user_repository = new ilOrgUnitUserRepository();

        if ($with_superios) {
            $org_unit_user_repository->withSuperiors();
        }
        if ($with_positions) {
            $org_unit_user_repository->withPositions();
        }

        return $org_unit_user_repository->getOrgUnitUsers($user_ids);
    }

    /**
     * @param int[] $user_ids
     * @return string[]
     */
    public function getEmailAdressesOfSuperiors(array $user_ids): array
    {
        $org_unit_user_repository = new ilOrgUnitUserRepository();
        $org_unit_user_repository->withSuperiors();

        return $org_unit_user_repository->getEmailAdressesOfSuperiors($user_ids);
    }
}
