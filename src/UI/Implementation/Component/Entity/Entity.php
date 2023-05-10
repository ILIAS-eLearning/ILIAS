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
     * @var null|string|PropertyListing|StandardLink|Legacy|array<StandardLink|Legacy>
     */
    protected null|string|array|PropertyListing|StandardLink|Legacy $blocking_conditions = null;
    /**
     * @var null|string|PropertyListing|StandardLink|Legacy|array<string|PropertyListing|StandardLink|Legacy>
     */
    protected null|string|array|PropertyListing|StandardLink|Legacy $featured_props = null;
    /**
     * @var null|string|PropertyListing|Legacy|array<PropertyListing|Legacy>
     */
    protected null|string|array|PropertyListing|Legacy $main_details = null;
    /**
     * @var array<Glyph|Tag>
     */
    protected array $prio_reactions = [];
    /**
     * @var array<Glyph|Tag>
     */
    protected array $reactions = [];
    /**
     * @var null|string|PropertyListing|StandardLink|Legacy|array<StandardLink|Legacy>
     */
    protected null|string|array|PropertyListing|StandardLink|Legacy $availability = null;
    /**
     * @var null|string|PropertyListing|Legacy|array<PropertyListing|Legacy>
     */
    protected null|string|array|PropertyListing|Legacy $details = null;
    /**
     * @var Shy[]
     */
    protected array $actions = [];
    /**
     * @var null|string|PropertyListing|Legacy|array<PropertyListing|Legacy>
     */
    protected null|string|array|PropertyListing|Legacy $personal_status = null;

    public function __construct(
        protected Symbol|Image|Shy|StandardLink|string $primary_identifier,
        protected Symbol|Image|Shy|StandardLink|string $secondary_identifier
    ) {
    }

    public function withPrimaryIdentifier(Symbol|Image|Shy|StandardLink|string $primary_identifier): self
    {
        $clone = clone $this;
        $clone->primary_identifier = $primary_identifier;
        return $clone;
    }
    public function getPrimaryIdentifier(): Symbol|Image|Shy|StandardLink|string
    {
        return $this->primary_identifier;
    }

    public function withSecondaryIdentifier(Symbol|Image|Shy|StandardLink|string $secondary_identifier): self
    {
        $clone = clone $this;
        $clone->secondary_identifier = $secondary_identifier;
        return $clone;
    }
    public function getSecondaryIdentifier(): Symbol|Image|Shy|StandardLink|string
    {
        return $this->secondary_identifier;
    }

    /**
     * @inheritdoc
     */
    public function withBlockingAvailabilityConditions(
        string|array|PropertyListing|StandardLink|Legacy $blocking_conditions
    ): self {
        $clone = clone $this;
        $clone->blocking_conditions = $blocking_conditions;
        return $clone;
    }
    /**
     * @return null|string|PropertyListing|StandardLink|Legacy|array<PropertyListing|StandardLink|Legacy>
     */
    public function getBlockingAvailabilityConditions(): null|string|array|PropertyListing|StandardLink|Legacy
    {
        return $this->blocking_conditions;
    }

    /**
     * @inheritdoc
     */
    public function withFeaturedProperties(
        string|array|PropertyListing|StandardLink|Legacy $featured_props
    ): self {
        $clone = clone $this;
        $clone->featured_props = $featured_props;
        return $clone;
    }
    /**
     * @return null|string|PropertyListing|Legacy|array<PropertyListing|Legacy>
     */
    public function getFeaturedProperties(): null|string|array|PropertyListing|StandardLink|Legacy
    {
        return $this->featured_props;
    }

    /**
     * @inheritdoc
     */
    public function withMainDetails(
        string|array|PropertyListing|Legacy $main_details
    ): self {
        $clone = clone $this;
        $clone->main_details = $main_details;
        return $clone;
    }
    /**
     * @return null|string|PropertyListing|Legacy|array<PropertyListing|Legacy>
     */
    public function getMainDetails(): null|string|array|PropertyListing|Legacy
    {
        return $this->main_details;
    }

    /**
     * @inheritdoc
     */
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
    /**
     * @return array<Glyph|Tag>
     */
    public function getPrioritizedReactions(): array
    {
        return $this->prio_reactions;
    }

    /**
     * @inheritdoc
     */
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
    /**
     * @return array<Glyph|Tag>
     */
    public function getReactions(): array
    {
        return $this->reactions;
    }

    /**
     * @inheritdoc
     */
    public function withAvailability(
        string|array|PropertyListing|StandardLink|Legacy $availability
    ): self {
        $clone = clone $this;
        $clone->availability = $availability;
        return $clone;
    }
    /**
     * @return null|string|PropertyListing|StandardLink|Legacy|array<PropertyListing|StandardLink|Legacy>
     */
    public function getAvailability(): null|string|array|PropertyListing|StandardLink|Legacy
    {
        return $this->availability;
    }

    /**
     * @inheritdoc
     */
    public function withDetails(
        string|array|PropertyListing|Legacy $details
    ): self {
        $clone = clone $this;
        $clone->details = $details;
        return $clone;
    }
    /**
     * @return null|string|PropertyListing|Legacy|array<PropertyListing|Legacy>
     */
    public function getDetails(): null|string|array|PropertyListing|Legacy
    {
        return $this->details;
    }

    /**
     * @inheritdoc
     */
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
        string|array|PropertyListing|Legacy $personal_status
    ): self {
        $clone = clone $this;
        $clone->personal_status = $personal_status;
        return $clone;
    }
    /**
     * @return null|string|PropertyListing|Legacy|array<PropertyListing|Legacy>
     */
    public function getPersonalStatus(): null|string|array|PropertyListing|Legacy
    {
        return $this->personal_status;
    }
}
