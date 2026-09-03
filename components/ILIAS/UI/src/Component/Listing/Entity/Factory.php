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

/**
 * This is what a factory for EntityListings looks like
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      The Entity Listing yields uniform Entities according to a consumer
     *      defined concept and lists them one after the other.
     *   composition: >
     *      Entities are stacked one after the other. On very large screens the layout will have multiple columns to use
     *      the space optimally. The design of the entity is one that favors a more horizontal representation.
     *
     * ---
     * @param \ILIAS\UI\Component\Listing\Entity\RecordToEntity $entity_mapping
     * @return \ILIAS\UI\Component\Listing\Entity\Standard
     */
    public function standard(RecordToEntity $entity_mapping): Standard;

    /**
     * ---
     * description:
     *  purpose: >
     *      The Entity Listing yields uniform Entities according to a consumer
     *      defined concept and lists them in a grid.
     *  composition:
     *      Shows a grid of many entities in a card-style design. Images, Symbols and other secondary identifiers are
     *      stacked to favor a vertical representation.
     * rules:
     *  usage:
     *      1: >
     *          If you want all entity secondary identifier images to take on the same height, you must provide images
     *          with the same height.
     *
     * ---
     * @param \ILIAS\UI\Component\Listing\Entity\RecordToEntity $entity_mapping
     * @return \ILIAS\UI\Component\Listing\Entity\Grid
     */
    public function grid(RecordToEntity $entity_mapping): Grid;
}
