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

use ilException;

/**
 * Thrown if a condition is stored with a type which is not contributed (anymore).
 *
 * The setup keeps such conditions in the database, so the code reading conditions
 * has to cope with them: skip them where they are evaluated and offer to delete
 * them where they are managed.
 */
final class UnknownConditionTypeException extends ilException
{
    public function __construct(
        private readonly string $condition_name
    ) {
        parent::__construct("Condition class for '{$condition_name}' not found.");
    }

    /**
     * The name of the condition type as stored in lso_condition_types.
     */
    public function getConditionName(): string
    {
        return $this->condition_name;
    }
}
