<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Component\Input\InputData;
use Psr\Http\Message\ServerRequestInterface;
use LogicException;

/**
 * Implements interaction of input element with post data from
 * psr-7 server request.
 */
class PostDataFromServerRequest implements InputData
{
    protected array $parsed_body;

    public function __construct(ServerRequestInterface $request)
    {
        $this->parsed_body = $request->getParsedBody();
    }

    /**
     * @inheritdocs
     */
    public function get(string $name)
    {
        if (!isset($this->parsed_body[$name])) {
            throw new LogicException("'$name' is not contained in posted data.");
        }

        return $this->parsed_body[$name];
    }


    /**
     * @inheritdocs
     */
    public function getOr(string $name, $default)
    {
        if (!isset($this->parsed_body[$name])) {
            return $default;
        }

        return $this->parsed_body[$name];
    }
}
