<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Panel\Secondary;

use ILIAS\UI\Component;

/**
 * Interface Factory
 * @package ILIAS\UI\Component\Panel\Secondary
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       Secondary Listing Panel present lists of items with similar presentation.
     *       All items are passed by using Item Groups.
     *   composition: >
     *      This Listing is composed of title and a set of Item Groups. Additionally an
     *      optional dropdown to select the number/types of items
     *      to be shown at the top of the Listing.
     * ---
     * @param string $title
     * @param \ILIAS\UI\Component\Item\Group[] $item_groups Item groups
     * @return \ILIAS\UI\Component\Panel\Secondary\Listing
     */
    public function listing(string $title, array $item_groups): Component\Panel\Secondary\Listing;

    /**
     * ---
     * description:
     *   purpose: >
     *      Secondary Legacy Panel present content from a Legacy component.
     *   composition: >
     *      The Secondary Legacy Panel is composed of title and a Legacy component. Additionally, it
     *      may have an optional footer area containing a Shy Button.
     *
     * context:
     *   - Marginal Grid Calendar.
     *   - Marginal Blog section.
     *   - Marginal Poll section.
     *
     * ---
     * @param string $title
     * @param \ILIAS\UI\Component\Legacy\Legacy $legacy
     * @return \ILIAS\UI\Component\Panel\Secondary\Legacy
     */
    public function legacy(string $title, Component\Legacy\Legacy $legacy): Component\Panel\Secondary\Legacy;
}
