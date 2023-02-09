<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Server;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class Response
{
    /**
     * @var array|\stdClass
     */
    protected $data = [];

    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;

    /**
     * Constructor
     */
    public function __construct($data)
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->data = $data;
    }

    /**
     *
     * @param
     * @return
     */
    public function send()
    {
        $http = $this->http;

        $string = json_encode($this->data);
        $stream = \ILIAS\Filesystem\Stream\Streams::ofString($string);
        $http->saveResponse($http
            ->response()
            ->withAddedHeader('Content-Type', 'application/json')
            ->withBody($stream));
        $http->sendResponse();
        $http->close();
    }
}
