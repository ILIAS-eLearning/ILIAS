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

namespace ILIAS\Refinery;

use Closure;

trait ProblemBuilder
{
    /**
     * @return string|callable
     */
    abstract protected function getError();

    final public function withProblemBuilder(callable $builder): self
    {
        $clone = clone $this;
        $clone->error = $builder;
        return $clone;
    }

    final public function getErrorMessage($value): string
    {
        $error = $this->getError();
        if (!is_callable($error)) {
            return $error;
        }
        $lng_closure = $this->getLngClosure();
        return $error($lng_closure, $value);
    }

    /**
     * Get the closure to be passed to the error-function that does i18n and sprintf.
     */
    final protected function getLngClosure(): Closure
    {
        return function (string $lang_var, ...$args): string {
            $error = $this->lng->txt($lang_var);

            if ($args === []) {
                return $error;
            }

            return sprintf($error, ...array_map(function ($v) {
                if (is_array($v)) {
                    return "array";
                } elseif (is_null($v)) {
                    return "null";
                } elseif (is_object($v) && !method_exists($v, "__toString")) {
                    return get_class($v);
                }
                return $v;
            }, $args));
        };
    }
}
