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

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Implementation\Component\Input\InputData;
use Psr\Http\Message\ServerRequestInterface;
use LogicException;

/**
 * Implements interaction of input element with get data from psr-7 server request.
 */
class StackedInputData implements InputData
{
    protected array $stack;

    public function __construct(InputData ...$stack)
    {
        $this->stack = $stack;
    }

    public function get(string $name)
    {
        foreach($this->stack as $input) {
            try {
                return $input->get($name);
            } catch (\LogicException $e) {
            }
        }
        throw new LogicException("'$name' is not contained in stack of input.");

    }

    public function getOr(string $name, $default)
    {
        try {
            $this->get($name);
        } catch (\LogicException $e) {
        }
        return $default;
    }
}
