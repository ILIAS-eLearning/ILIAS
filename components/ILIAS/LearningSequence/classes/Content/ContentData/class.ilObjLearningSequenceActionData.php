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

/**
 * Data transfer object for an action in the learning sequence content table.
 */
final readonly class ilObjLearningSequenceActionData
{
    /**
     * Creates action data.
     *
     * @param string $label Action label.
     * @param string $link Action target.
     * @param bool $is_divider Whether the action is a divider.
     */
    public function __construct(
        /** Action label. */
        public string $label,
        /** Action target. */
        public string $link,
        /** Whether the action is a divider. */
        public bool $is_divider = false
    ) {
    }

    /**
     * Creates a divider action.
     */
    public static function divider(): self
    {
        return new self('', '', true);
    }
}
