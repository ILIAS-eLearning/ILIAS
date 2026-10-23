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

namespace ILIAS\LearningSequence\Content\Condition;

use ILIAS\UI\Component\Link\Bulky;

interface SubtypeAwareInterface
{
    /**
     * Gets the currently active subtype.
     */
    public function getSubtype(): string;

    /**
     * Returns a list of all supported subtype identifiers.
     *
     * @return string[]
     */
    public function getSupportedSubtypes(): array;

    /**
     * Returns the human-readable label for a given subtype.
     */
    public function getSubtypeLabel(string $subtype): string;

    /**
     * Builds the UI step/link element for a specific subtype.
     *
     * Used as a helper to generate individual menu items (Bulky links)
     * for the Sub menu returned in setupSteps().
     *
     * @param string $subtype Identifier of the subtype (e.g., self::SUBTYPE_AND)
     */
    public function buildSubtypeStep(string $subtype): Bulky;
}
