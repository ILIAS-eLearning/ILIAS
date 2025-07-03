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

namespace ILIAS\Container\Skills;

use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Skill\Service\SkillProfileService;

class ContProfileRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected SkillProfileService $profile_service,
        protected \ilSkillManagementSettings $skmg_settings,
        protected int $cont_member_role_id
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $profiles = [];

        if ($this->skmg_settings->getLocalAssignmentOfProfiles()) {
            foreach ($this->profile_service->getGlobalProfilesOfRole($this->cont_member_role_id) as $gp) {
                $profiles[] = $gp;
            }
        }
        if ($this->skmg_settings->getAllowLocalProfiles()) {
            foreach ($this->profile_service->getLocalProfilesOfRole($this->cont_member_role_id) as $lp) {
                $profiles[] = $lp;
            }
        }

        // convert profiles to array structure, because tables can only handle arrays
        $profiles_array = [];
        foreach ($profiles as $profile) {
            $profiles_array[$profile->getId()] = [
                "id" => $profile->getId(),
                "profile_id" => $profile->getId(),
                "title" => $profile->getTitle(),
                "profile_ref_id" => $profile->getRefId()
            ];
        }
        ksort($profiles_array);

        $profiles_array = $this->applyOrder($profiles_array, $order);
        $profiles_array = $this->applyRange($profiles_array, $range);

        foreach ($profiles_array as $profile) {
            yield $profile;
        }
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        $count = 0;

        if ($this->skmg_settings->getLocalAssignmentOfProfiles()) {
            $count += count($this->profile_service->getGlobalProfilesOfRole($this->cont_member_role_id));
        }
        if ($this->skmg_settings->getAllowLocalProfiles()) {
            $count += count($this->profile_service->getLocalProfilesOfRole($this->cont_member_role_id));
        }

        return $count;
    }

    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ["profile_id", "id"]);
    }
}
