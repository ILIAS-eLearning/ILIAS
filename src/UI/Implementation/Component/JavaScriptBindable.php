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

namespace ILIAS\UI\Implementation\Component;

use Closure;
use ReflectionFunction;
use InvalidArgumentException;

/**
 * Trait for components implementing JavaScriptBindable providing standard
 * implementation.
 */
trait JavaScriptBindable
{
    private ?Closure $on_load_code_binder = null;

    /**
     * @see \ILIAS\UI\Component\JavaScriptBindable::withOnLoadCode
     */
    public function withOnLoadCode(Closure $binder)
    {
        $this->checkBinder($binder);
        $clone = clone $this;
        $clone->on_load_code_binder = $binder;
        return $clone;
    }

    /**
     * @see \ILIAS\UI\Component\JavaScriptBindable::withAdditionalOnLoadCode
     */
    public function withAdditionalOnLoadCode(Closure $binder)
    {
        $current_binder = $this->getOnLoadCode();
        if ($current_binder === null) {
            return $this->withOnLoadCode($binder);
        }

        $this->checkBinder($binder);
        return $this->withOnLoadCode(fn ($id) => $current_binder($id) . "\n" . $binder($id));
    }

    /**
     * @see \ILIAS\UI\Component\JavaScriptBindable::getOnLoadCode
     */
    public function getOnLoadCode(): ?Closure
    {
        return $this->on_load_code_binder;
    }

    /**
     * @throw \InvalidArgumentException	if closure does not take one argument
     */
    private function checkBinder(Closure $binder): void
    {
        $refl = new ReflectionFunction($binder);
        $args = array_map(fn ($arg) => $arg->name, $refl->getParameters());
        if (array("id") !== $args) {
            throw new InvalidArgumentException('Expected closure "$binder" to have exactly one argument "$id".');
        }
    }
}
