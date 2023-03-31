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

namespace ILIAS\UI\Component\Entity;

use ILIAS\UI\Component\Component;

/**
 * This describes an EntityRepresentation
 */
interface Entity extends Component
{
    public function withBlockingAvailabilityConditions($blocking_conditions): self;
    public function withFeaturedProperties($featured_props): self;
    public function withMainDetails($main_details): self;
    /**
     * @param array <Component\Symbol\Glyph|Component\Button\Tag> $prio_reactions
     */
    public function withPrioritizedReactions(array $prio_reactions): self;
    /**
     * @param array<string, Component\Button\Shy> $actions
     */
    public function withActions(array $actions): self;


    public function withPersonalStatus($personal_status): self;
    public function withAvailability($availability): self;
    public function withDetails($details): self;
    /**
     * @param array <Component\Symbol\Glyph|Component\Button\Tag> $prio_reactions
     */
    public function withReactions(array $reactions): self;
}
