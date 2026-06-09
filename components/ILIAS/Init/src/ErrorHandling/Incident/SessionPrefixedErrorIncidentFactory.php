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
 * Creates identifiers from a session prefix and a random suffix.
 */
final readonly class SessionPrefixedErrorIncidentFactory implements ErrorIncidentFactory
{
    public function __construct(
        private \Random\Randomizer $randomizer = new \Random\Randomizer()
    ) {
    }

    public function create(string $session_id): ErrorIncident
    {
        $session_prefix = substr($session_id, 0, 5);
        $error_number = $this->randomizer->getInt(1, 9999);

        return new ErrorIncident(new ErrorIncidentId($session_prefix . '_' . $error_number));
    }
}
