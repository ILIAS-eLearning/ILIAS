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

namespace ILIAS\UI\Component\Listing\Entity;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Entity\EntityRetrieval;

/**
 * This is what an Entity Listing looks like.
 */
interface Entity extends Component
{
    /**
     * Get an Entity Listing like this, but provide it with a subset of entities.
     * If no subset is provided, the whole set will be rendered instead.
     *
     * This will invoke {@see EntityRetrieval::getEntitiesByIds()} instead of
     * {@see EntityRetrieval::getEntities()}.
     *
     * @param array<string|int> $entity_ids
     */
    public function withSubset(array $entity_ids): static;
}
