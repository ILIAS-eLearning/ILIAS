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
 * Data transfer object for a learning sequence condition.
 */
final readonly class ilObjLearningSequenceConditionData
{
    /**
     * Creates condition data.
     *
     * @param string $title Condition title.
     * @param string $value Condition value.
     * @param string $internal_name Internal condition name.
     */
    public function __construct(
        /** Condition title. */
        public string $title,
        /** Condition value. */
        public string $value,
        /** Internal condition name. */
        public string $internal_name = '',
    ) {
    }
}
