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

namespace ILIAS\Test;

use ILIAS\Filesystem\Stream\Stream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Services as HttpService;

class ResponseHandler
{
    public function __construct(
        private readonly HttpService $http,
    ) {
    }

    /**
     * @param Stream|string|mixed $response
     */
    public function sendAsync(mixed $response): void
    {
        if (is_string($response)) {
            $response = Streams::ofString($response);
        } elseif (is_resource($response)) {
            $response = Streams::ofResource($response);
        }

        $this->http->saveResponse(
            $this->http->response()->withBody($response)
        );
        $this->http->sendResponse();
        $this->http->close();
    }
}
