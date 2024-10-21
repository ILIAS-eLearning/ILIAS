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

namespace ILIAS\GlobalScreen\Scope;

use Closure;
use LogicException;
use ReflectionFunction;
use ReflectionType;
use Throwable;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait CheckClosureTrait
{
    private function checkClosureForSignature(Closure $c, string $signature): void
    {
        $error_message = 'first argument and return type of closure must be type-hinted to ' . $signature;
        try {
            $r = new ReflectionFunction($c);
            if (count($r->getParameters()) !== 1) {
                throw new LogicException($error_message);
            }
            $first_param_type = $r->getParameters()[0]->getType();
            if ($first_param_type instanceof ReflectionType && $first_param_type->getName() !== $signature) {
                throw new LogicException($error_message);
            }
            $return_type = $r->getReturnType();
            if ($return_type === null) {
                throw new LogicException($error_message);
            }
            if ($return_type->getName() !== $signature) {
                throw new LogicException($error_message);
            }
        } catch (Throwable) {
            throw new LogicException($error_message);
        }
    }
}
