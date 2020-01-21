<?php

/* Copyright (c) 2019 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Implementation\Component\Input\InputData;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Implements interaction of input element with get data from
 * psr-7 server request.
 */
class QueryParamsFromServerRequest implements InputData
{

    /**
     * @var    array
     */
    protected $query_params;


    public function __construct(ServerRequestInterface $request)
    {
        $this->query_params = $request->getQueryParams();
    }


    /**
     * @inheritdocs
     */
    public function get($name)
    {
        if (!isset($this->query_params[$name])) {
            throw new \LogicException("'$name' is not contained in query parameters.");
        }

        return $this->query_params[$name];
    }


    /**
     * @inheritdocs
     */
    public function getOr($name, $default)
    {
        if (!isset($this->query_params[$name])) {
            return $default;
        }

        return $this->query_params[$name];
    }
}
