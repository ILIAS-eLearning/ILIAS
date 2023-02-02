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

namespace ILIAS\Refinery\Parser\ABNF;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use Closure;
use ILIAS\Refinery\Transformation;

class Transform
{
    /**
     * Transforms the value parsed by the given $parse parameter to the value returned by $transformation.
     * Example:
     * $brick = new Brick();
     * $int = $DIC->refinery()->kindlyTo()->int();
     * $brick->apply($this->to($int, $brick->digit()), '4') => new Ok(4);
     */
    public function to(Transformation $transformation, Closure $parse): Closure
    {
        return $this->from(static fn ($value): Result => $transformation->applyTo(new Ok($value))->map(
            static fn ($value): array => [$value]
        ), $parse);
    }

    /**
     * The value parsed by the given $parse parameter will be stored under the key $key.
     *
     * Example:
     * $brick = new Brick();
     *
     * $brick->apply($this->as('hej', $brick->alpha()), 'a') => new Ok(['hej' => 'a']);
     *
     * $brick->apply($brick->sequence([
     *     $this->as('first', $brick->alpha()),
     *     $this->as('second', $brick->alpha()),
     *     $this->as('third', $brick->alpha()),
     * ]), 'abc') => new Ok(['first' => 'a', 'second' => 'b', 'third' => 'c']);
     *
     * $brick->apply($this->as('foo', $this->as('bar', $brick->alpha())), 'a') => new Ok(['foo' => ['bar' => 'a']]);
     */
    public function as(string $key, Closure $parse): Closure
    {
        return $this->from(
            fn ($value): Result => new Ok([$key => $this->removeArrayLevel($value)]),
            $parse
        );
    }

    /**
     * self::to MUST add one array level, because it may be used in a context where more array levels are added,
     * this method removes this level when it is known that it is and will be the only element in the array.
     */
    private function removeArrayLevel($value)
    {
        if (is_array($value) && count($value) === 1 && isset($value[0])) {
            return $value[0];
        }

        return $value;
    }

    private function from(Closure $transform, Closure $parse): Closure
    {
        return fn (Intermediate $previous, Closure $cc): Result => $parse(
            $previous->onlyTodo(),
            fn (Result $child): Result => $child->then(
                fn (Intermediate $child): Result =>
                    $child->transform($transform)
                          ->then($this->combine($previous, $child, $cc))
                          ->except($this->error($cc))
            )->except($this->error($cc))
        );
    }

    private function combine(Intermediate $hasAccepted, Intermediate $hasTodos, Closure $cc): Closure
    {
        return static fn (array $values): Result => $cc(new Ok(
            $hasTodos->onlyTodo()
                     ->push($hasAccepted->accepted())
                     ->push($values)
        ));
    }

    private function error(Closure $cc): Closure
    {
        return static fn ($error): Result => $cc(new Error($error));
    }
}
