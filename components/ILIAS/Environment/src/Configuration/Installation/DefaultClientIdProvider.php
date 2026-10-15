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

namespace ILIAS\Environment\Configuration\Installation;

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Data\ClientId;

/**
 * Determines the current client ID from the HTTP request context.
 *
 * Priority: GET client_id → ilias.ini clients.default
 *
 * The ilClientId cookie is not considered, since ilInitialisation::determineClient()
 * does not consider it either and both have to resolve the same client.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
readonly class DefaultClientIdProvider implements ClientIdProvider
{
    public function __construct(
        private IliasIni $ilias_ini,
        private GlobalHttpState $http,
    ) {
    }

    public function getClientId(): ClientId
    {
        $from_query = (string) ($this->http->request()->getQueryParams()['client_id'] ?? '');

        $raw = $from_query !== ''
            ? $from_query
            : $this->ilias_ini->getDefaultClientId();

        // No sanitising here: ClientId only accepts [A-Za-z0-9#_.-] and rejects
        // anything else (including markup), so it is the single validation point.
        return new ClientId((string) $raw);
    }
}
