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

class ContSkillRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected ContainerSkillManager $cont_skill_manager
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $skills = $this->cont_skill_manager->getSkillsForTableGUI();

        // Apply ordering and range
        $skills = $this->applyOrder($skills, $order);
        $skills = $this->applyRange($skills, $range);

        foreach ($skills as $skill) {
            $skill["id"] = $skill["base_skill_id"] . ":" . $skill["tref_id"];
            yield $skill;
        }
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        $skills = $this->cont_skill_manager->getSkillsForTableGUI();
        return count($skills);
    }

    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ["base_skill_id", "tref_id"]);
    }
}
