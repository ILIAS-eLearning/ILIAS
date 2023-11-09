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
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Listing\Property as PropertyListing;
use ILIAS\UI\Component\Link\Standard as StandardLink;

use ILIAS\UI\Implementation\Component\ComponentHelper;

abstract class Entity implements I\Entity
{
    use ComponentHelper;

    /**
     * @var array<PropertyListing | StandardLink | Legacy>
     */
    protected array $blocking_conditions = [];
    /**
     * @var array<PropertyListing | StandardLink | Legacy>
     */
    protected array $featured_props = [];
    /**
     * @var array<PropertyListing | Legacy>
     */
    protected array $main_details = [];
    /**
     * @var array<Glyph | Tag>
     */
    protected array $prio_reactions = [];
    /**
     * @var array<Glyph | Tag>
     */
    protected array $reactions = [];
    /**
     * @var array<PropertyListing | StandardLink | Legacy>
     */
    protected array $availability = [];
    /**
     * @var array<PropertyListing | Legacy>
     */
    protected array $details = [];
    /**
     * @var Shy[]
     */
    protected array $actions = [];
    /**
     * @var array<PropertyListing | Legacy>
     */
    protected array $personal_status = [];

    public function __construct(
        protected Symbol | Image | Shy | StandardLink | string $primary_identifier,
        protected Symbol | Image | Shy | StandardLink | string $secondary_identifier
    ) {
    }

    public function withPrimaryIdentifier(Symbol | Image | Shy | StandardLink | string $primary_identifier): self
    {
        $clone = clone $this;
        $clone->primary_identifier = $primary_identifier;
        return $clone;
    }
    public function getPrimaryIdentifier(): Symbol | Image | Shy | StandardLink | string
    {
        return $this->primary_identifier;
    }

    public function withSecondaryIdentifier(Symbol | Image | Shy | StandardLink | string $secondary_identifier): self
    {
        $clone = clone $this;
        $clone->secondary_identifier = $secondary_identifier;
        return $clone;
    }
    public function getSecondaryIdentifier(): Symbol | Image | Shy | StandardLink | string
    {
        return $this->secondary_identifier;
    }

    /**
     * @inheritdoc
     */
    public function withBlockingAvailabilityConditions(
        PropertyListing | StandardLink | Legacy ...$blocking_conditions
    ): self {
        $clone = clone $this;
        $clone->blocking_conditions = $blocking_conditions;
        return $clone;
    }
    /**
     * @return array<PropertyListing | StandardLink | Legacy>
     */
    public function getBlockingAvailabilityConditions(): array
    {
        return $this->blocking_conditions;
    }

    /**
     * @inheritdoc
     */
    public function withFeaturedProperties(
        PropertyListing | StandardLink | Legacy ...$featured_props
    ): self {
        $clone = clone $this;
        $clone->featured_props = $featured_props;
        return $clone;
    }
    /**
     * @return array<PropertyListing | StandardLink | Legacy>
     */
    public function getFeaturedProperties(): array
    {
        return $this->featured_props;
    }

    /**
     * @inheritdoc
     */
    public function withMainDetails(
        PropertyListing | Legacy ...$main_details
    ): self {
        $clone = clone $this;
        $clone->main_details = $main_details;
        return $clone;
    }
    /**
     * @return array<PropertyListing | Legacy>
     */
    public function getMainDetails(): array
    {
        return $this->main_details;
    }

    /**
     * @inheritdoc
     */
    public function withPrioritizedReactions(Glyph | Tag ...$prio_reactions): self
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
    /**
     * @return array<Glyph | Tag>
     */
    public function getPrioritizedReactions(): array
    {
        return $this->prio_reactions;
    }

    /**
     * @inheritdoc
     */
    public function withReactions(Glyph | Tag ...$reactions): self
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
    /**
     * @return array<Glyph | Tag>
     */
    public function getReactions(): array
    {
        return $this->reactions;
    }

    /**
     * @inheritdoc
     */
    public function withAvailability(
        PropertyListing | StandardLink | Legacy ...$availability
    ): self {
        $clone = clone $this;
        $clone->availability = $availability;
        return $clone;
    }
    /**
     * @return array<PropertyListing | StandardLink | Legacy>
     */
    public function getAvailability(): array
    {
        return $this->availability;
    }

    /**
     * @inheritdoc
     */
    public function withDetails(
        PropertyListing | Legacy ...$details
    ): self {
        $clone = clone $this;
        $clone->details = $details;
        return $clone;
    }
    /**
     * @return array<PropertyListing | Legacy>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @inheritdoc
     */
    public function withActions(Shy ...$actions): self
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }
    /**
     * @return Shy[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @inheritdoc
     */
    public function withPersonalStatus(
        PropertyListing | Legacy ...$personal_status
    ): self {
        $clone = clone $this;
        $clone->personal_status = $personal_status;
        return $clone;
    }
    /**
     * @return array<PropertyListing | Legacy>
     */
    public function getPersonalStatus(): array
    {
        return $this->personal_status;
    }
}
