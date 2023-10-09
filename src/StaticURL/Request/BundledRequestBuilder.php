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

namespace ILIAS\StaticURL\Request;

use ILIAS\Refinery\Factory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class BundledRequestBuilder implements RequestBuilder
{
    private LegacyRequestBuilder $legacy;
    private StaticURLRequestBuilder $static;

    public function __construct()
    {
        $this->legacy = new LegacyRequestBuilder();
        $this->static = new StaticURLRequestBuilder();
    }

    public function buildRequest(\ILIAS\HTTP\Services $http, Factory $refinery, array $handlers): ?Request
    {
        if (($request = $this->legacy->buildRequest(
            $http,
            $refinery,
            $handlers
        )) instanceof \ILIAS\StaticURL\Request\Request) {
            // we have now the situation that a new static URL is requested, but the handler is not yet registered or implemented
            // we built a legacy request using the LegacyRequestBuilder for this to let the old system handle it.
            return $request;
        }

        return $this->static->buildRequest($http, $refinery, $handlers);
    }

}
