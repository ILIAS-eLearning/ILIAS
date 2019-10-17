<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Component\Input\PostData;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Implements interaction of input element with post data from
 * psr-7 server request.
 */
class PostDataFromServerRequest implements PostData
{

    /**
     * @var    array
     */
    protected $parsed_body;


    public function __construct(ServerRequestInterface $request)
    {
        $this->parsed_body = $request->getParsedBody();
    }


    /**
     * @inheritdocs
     */
    public function get($name)
    {
        if (!isset($this->parsed_body[$name])) {
            throw new \LogicException("'$name' is not contained in posted data.");
        }

        return $this->parsed_body[$name];
    }


    /**
     * @inheritdocs
     */
    public function getOr($name, $default)
    {
        if (!isset($this->parsed_body[$name])) {
            return $default;
        }

        return $this->parsed_body[$name];
    }
}
