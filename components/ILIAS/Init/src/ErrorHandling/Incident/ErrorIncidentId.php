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

namespace ILIAS\Init\ErrorHandling\Incident;

/**
 * Unique identifier for a reported error incident. Used as log file name and
 * referenced in the user-facing error message.
 */
final readonly class ErrorIncidentId
{
    public function __construct(
        private string $value
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('Error incident identifier must not be empty.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
