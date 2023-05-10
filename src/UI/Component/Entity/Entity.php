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
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Button\Tag;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Listing\Property as PropertyListing;
use ILIAS\UI\Component\Link\Standard as StandardLink;

/**
 * This describes an Entity
 */
interface Entity extends Component
{
    //Priority Areas

    /**
     * Set (and maybe explain) Blocking Availability Conditions when there is
     * some access restriction for the user on this object.
     *
     * @param string|PropertyListing|StandardLink|Legacy|array<PropertyListing|StandardLink|Legacy> $blocking_conditions
     */
    public function withBlockingAvailabilityConditions(
        string|array|PropertyListing|StandardLink|Legacy $blocking_conditions
    ): self;

    /**
     * Some Properties may be of higher relevance than others, be it as very
     * significant properties of the entity itself or of greater importance
     * within the context; you may "feature" them, i.e. put them in a prominent
     * place.
     *
     * @param string|PropertyListing|StandardLink|Legacy|array<string|PropertyListing|StandardLink|Legacy> $featured_props
     */
    public function withFeaturedProperties(
        string|array|PropertyListing|StandardLink|Legacy $featured_props
    ): self;

    /**
     * Main Details should provide a quick differentiation or choice on the entity.
     * "Description" would be one of the most prominent examples.
     *
     * @param string|PropertyListing|Legacy|array<PropertyListing|Legacy> $main_details
     */
    public function withMainDetails(
        string|array|PropertyListing|Legacy $main_details
    ): self;

    /**
     * Users may directly react to the entity, e.g. comment or tag it.
     * When there are multiple possible reactions, split them e.g. into more
     * common ones (here) and less often used ones.
     * Another way of distinguishing Reactions might be the availability/significance
     * for everybody in contrast to the current user (e.g. rating vs. my favorite)
     *
     * @param array<Glyph|Tag> $prio_reactions
     */
    public function withPrioritizedReactions(array $prio_reactions): self;


    //Further Areas

    /**
     * Reactions that are less prominent than Prioritized Reactions go here.
     * @param array<Glyph|Tag> $reactions
     */
    public function withReactions(array $reactions): self;

    /**
     * Properties that could potentially limit a users access to the object
     * belong to this group. If they _are_ actually blocking access, then
     * you should place them into Blocking Availability Conditions.
     *
     * @param string|PropertyListing|StandardLink|Legacy|array<PropertyListing|StandardLink|Legacy> $availability
     */
    public function withAvailability(
        string|array|PropertyListing|StandardLink|Legacy $availability
    ): self;

    /**
     * Details provide further information about the entity - worth knowing and helpful,
     * but not as significantly important than Main Details or Featured Properties.
     *
     * @param string|PropertyListing|Legacy|array<PropertyListing|Legacy> $details
     */
    public function withDetails(
        string|array|PropertyListing|Legacy $details
    ): self;

    /**
     * Actions are the things you can actually _do_ with the entity,
     * e.g. in context of repository items: view, copy, delete, etc.
     *
     * @param Shy[] $actions
     */
    public function withActions(array $actions): self;

    /**
     * Personal Status properties indicate the status of a relation between
     * the current user and the object. A most prominent example would be
     * the learning Progress of a Course.
     *
     * @param string|PropertyListing|Legacy|array<PropertyListing|Legacy> $personal_status
     */
    public function withPersonalStatus(
        string|array|PropertyListing|Legacy $personal_status
    ): self;
}
