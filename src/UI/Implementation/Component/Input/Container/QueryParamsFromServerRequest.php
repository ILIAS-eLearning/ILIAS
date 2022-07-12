<?php declare(strict_types=1);

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
