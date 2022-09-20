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

namespace ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class Response
{
    /**
     * @var array|\stdClass
     */
    protected $data = [];

    protected \ILIAS\HTTP\Services $http;

    /**
     * @param array|\stdClass $data
     */
    public function __construct($data)
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->data = $data;
    }

    public function send(): void
    {
        $http = $this->http;

        $string = json_encode($this->data);
        $stream = \ILIAS\Filesystem\Stream\Streams::ofString($string);
        $http->saveResponse($http
            ->response()
            ->withAddedHeader('Content-Type', 'application/json')
            ->withBody($stream));
        $http->sendResponse();
        exit;
    }
}
