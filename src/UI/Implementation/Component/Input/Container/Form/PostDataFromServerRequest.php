<?php

declare(strict_types=1);

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
