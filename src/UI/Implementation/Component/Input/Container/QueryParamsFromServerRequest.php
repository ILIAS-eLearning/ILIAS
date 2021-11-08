<?php declare(strict_types=1);

/* Copyright (c) 2019 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container;

use ILIAS\UI\Implementation\Component\Input\InputData;
use Psr\Http\Message\ServerRequestInterface;
use LogicException;

/**
 * Implements interaction of input element with get data from psr-7 server request.
 */
class QueryParamsFromServerRequest implements InputData
{
    protected array $query_params;

    public function __construct(ServerRequestInterface $request)
    {
        $this->query_params = $request->getQueryParams();
    }

    /**
     * @inheritdocs
     */
    public function get(string $name)
    {
        if (!isset($this->query_params[$name])) {
            throw new LogicException("'$name' is not contained in query parameters.");
        }

        return $this->query_params[$name];
    }

    /**
     * @inheritdocs
     */
    public function getOr(string $name, $default)
    {
        if (!isset($this->query_params[$name])) {
            return $default;
        }

        return $this->query_params[$name];
    }
}
