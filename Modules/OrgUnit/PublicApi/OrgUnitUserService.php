<?php

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
    public function getEmailAdressesOfSuperiors(array $user_ids) : array
    {
        $org_unit_user_repository = new ilOrgUnitUserRepository();
        $org_unit_user_repository->withSuperiors();

        return $org_unit_user_repository->getEmailAdressesOfSuperiors($user_ids);
    }
}
