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

namespace ILIAS\UI\Implementation\Component\Entity;

use ILIAS\UI\Component\Entity as I;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Button\Tag;
use ILIAS\UI\Component\Link\Standard as ShyLink;

use ILIAS\UI\Implementation\Component\ComponentHelper;

abstract class Entity implements I\Entity
{
    use ComponentHelper;

    protected $featured_props;
    protected $main_details;
    protected $blocking_conditions;
    protected $prio_reactions = [];
    protected $personal_status;
    protected $availability;
    protected $details;
    protected $reactions;
    protected $actions = [];

    public function __construct(
        protected Symbol|Image|Shy|ShyLink|string $primary_identifier,
        protected Symbol|Image|Shy|ShyLink|string $secondary_identifier
    ) {
    }

    public function withPrimaryIdentifier(Symbol|Image|Shy|ShyLink|string $primary_identifier): self
    {
        $clone = clone $this;
        $clone->primary_identifier = $primary_identifier;
        return $clone;
    }
    public function getPrimaryIdentifier(): Symbol|Image|Shy|ShyLink|string
    {
        return $this->primary_identifier;
    }

    public function withSecondaryIdentifier(Symbol|Image|Shy|ShyLink|string $secondary_identifier): self
    {
        $clone = clone $this;
        $clone->secondary_identifier = $secondary_identifier;
        return $clone;
    }
    public function getSecondaryIdentifier(): Symbol|Image|Shy|ShyLink|string
    {
        return $this->secondary_identifier;
    }

    public function withFeaturedProperties($featured_props): self
    {
        $clone = clone $this;
        $clone->featured_props = $featured_props;
        return $clone;
    }
    public function getFeaturedProperties()
    {
        return $this->featured_props;
    }

    public function withMainDetails($main_details): self
    {
        $clone = clone $this;
        $clone->main_details = $main_details;
        return $clone;
    }
    public function getMainDetails()
    {
        return $this->main_details;
    }

    public function withBlockingAvailabilityConditions($blocking_conditions): self
    {
        $clone = clone $this;
        $clone->blocking_conditions = $blocking_conditions;
        return $clone;
    }
    public function getBlockingAvailabilityConditions()
    {
        return $this->blocking_conditions;
    }

    public function withPrioritizedReactions(array $prio_reactions): self
    {
        $this->checkArgListElements(
            "Entity Prioritized Reactions",
            $prio_reactions,
            [Glyph::class, Tag::class]
        );
        $clone = clone $this;
        $clone->prio_reactions = $prio_reactions;
        return $clone;
    }
    public function getPrioritizedReactions()
    {
        return $this->prio_reactions;
    }

    public function withPersonalStatus($personal_status): self
    {
        $clone = clone $this;
        $clone->personal_status = $personal_status;
        return $clone;
    }
    public function getPersonalStatus()
    {
        return $this->personal_status;
    }

    public function withAvailability($availability): self
    {
        $clone = clone $this;
        $clone->availability = $availability;
        return $clone;
    }
    public function getAvailability()
    {
        return $this->availability;
    }

    public function withDetails($details): self
    {
        $clone = clone $this;
        $clone->details = $details;
        return $clone;
    }
    public function getDetails()
    {
        return $this->details;
    }

    public function withReactions(array $reactions): self
    {
        $this->checkArgListElements(
            "Entity Reactions",
            $reactions,
            [Glyph::class, Tag::class]
        );

        $clone = clone $this;
        $clone->reactions = $reactions;
        return $clone;
    }
    public function getReactions()
    {
        return $this->reactions;
    }

    public function withActions(array $actions): self
    {
        $this->checkArgListElements(
            "Entity Actions",
            $actions,
            [Shy::class]
        );

        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }
    public function getActions()
    {
        return $this->actions;
    }
}
