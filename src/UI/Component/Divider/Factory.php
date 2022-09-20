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

namespace ILIAS\UI\Component\Divider;

/**
 * Divider Factory
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       A Horizontal Divider is used to mark a thematic change in a sequence of
     *       elements that are stacked from top to bottom.
     *   composition: >
     *     Horizontal dividers consists of a horizontal line which may comprise a label.
     *
     * rules:
     *   usage:
     *       1: >
     *          Horizontal Dividers MUST only be used in container components that render
     *          a sequence of items from top to bottom.
     *   ordering:
     *       1: >
     *          Horizontal Dividers MUST always have a succeeding element
     *          in a sequence of elments, which MUST NOT be another Horizontal Divider.
     *       2: >
     *          Horizontal Dividers without label MUST always have a preceding
     *          element in a sequence of elments, which MUST NOT be another
     *          Horizontal Divider.
     * ---
     * @return  \ILIAS\UI\Component\Divider\Horizontal
     */
    public function horizontal(): Horizontal;

    /**
     * ---
     * description:
     *   purpose: >
     *       A Vertical Divider is used to mark a thematic or functional change in a sequence of
     *       elements that are stacked from left to right.
     *   composition: >
     *     Vertical Dividers consists of a glyph-like character.
     *
     * rules:
     *   usage:
     *       1: >
     *          Vertical Dividers MUST only be used in container components that render
     *          a sequence of items from left to right.
     *   ordering:
     *       1: >
     *          Vertical Dividers MUST always have a succeeding element
     *          in a sequence of elments, which MUST NOT be another Vertical Divider.
     * ---
     * @return  \ILIAS\UI\Component\Divider\Vertical
     */
    public function vertical(): Vertical;
}
