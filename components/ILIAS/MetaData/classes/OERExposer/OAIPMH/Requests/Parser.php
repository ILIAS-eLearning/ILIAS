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

namespace ILIAS\MetaData\OERExposer\OAIPMH\Requests;

use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;
use ILIAS\MetaData\OERExposer\OAIPMH\HTTP\WrapperInterface as HTTPWrapper;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Request;
use ILIAS\Data\URI;

class Parser implements ParserInterface
{
    protected HTTPWrapper $http;

    public function __construct(HTTPWrapper $http)
    {
        $this->http = $http;
    }

    public function parseFromHTTP(URI $base_url): RequestInterface
    {
        $verb = Verb::NULL;
        if ($this->http->requestHasArgument(Argument::VERB)) {
            $verb = Verb::tryFrom($this->http->retrieveArgumentFromRequest(Argument::VERB)) ?? Verb::NULL;
        }

        $request = $this->getEmptyRequest($verb, $base_url);

        foreach (Argument::cases() as $argument) {
            if (
                $argument === Argument::VERB ||
                !$this->http->requestHasArgument($argument)
            ) {
                continue;
            }
            $request = $request->withArgument(
                $argument,
                rawurldecode($this->http->retrieveArgumentFromRequest($argument))
            );
        }

        return $request;
    }

    protected function getEmptyRequest(Verb $verb, URI $base_url): RequestInterface
    {
        return new Request(
            $base_url,
            $verb
        );
    }
}
