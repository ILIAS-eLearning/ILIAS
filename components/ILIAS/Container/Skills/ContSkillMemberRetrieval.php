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

class ContSkillMemberRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected ContainerSkillManager $cont_skill_manager,
        protected \ilContainer $container
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $participants = \ilCourseParticipants::getInstanceByObjId($this->container->getId());
        $members = $participants->getMembers();

        $member_data = [];
        foreach ($members as $member_id) {
            $name = \ilObjUser::_lookupName($member_id);
            $login = \ilObjUser::_lookupLogin($member_id);

            $member_data[] = [
                "id" => $member_id,
                "name" => $name["lastname"] . ", " . $name["firstname"],
                "login" => $login,
                "published" => $this->cont_skill_manager->getPublished($member_id),
                "skills" => $this->cont_skill_manager->getMemberSkillLevelsForContainerOrdered($member_id)
            ];
        }

        // Apply ordering and range
        $member_data = $this->applyOrder($member_data, $order);
        $member_data = $this->applyRange($member_data, $range);

        foreach ($member_data as $member) {
            yield $member;
        }
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        $participants = \ilCourseParticipants::getInstanceByObjId($this->container->getId());
        return count($participants->getMembers());
    }

    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ["id"]);
    }
}
