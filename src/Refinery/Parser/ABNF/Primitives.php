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

namespace ILIAS\Refinery\Parser\ABNF;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use Closure;

/**
 * @phpstan-type Continuation Closure(Result<Intermediate>): Result<Intermediate>
 * @phpstan-type Parser Closure(Intermediate, Continuation): Result<Intermediate>
 */
class Primitives
{
    public function simpleEither(array $parse): Closure
    {
        return $this->variadic([$this, 'or2'], $parse);
    }

    public function simpleSequence(array $parse): Closure
    {
        return $this->variadic([$this, 'seq'], $parse);
    }

    /**
     * @param null|int $max
     * @param Parser|string $parse
     * @return Parser
     */
    public function until(?int $max, $parse): Closure
    {
        if (0 === $max) {
            return $this->nop();
        }

        $max = null === $max ? null : $max - 1;

        return $this->simpleEither([
            $this->nop(),
            $this->simpleSequence([
                $parse,
                $this->lazy([$this, 'until'], $max, $parse)
            ])
        ]);
    }

    /**
     * @param Parser|string $x
     * @return Parser
     */
    public function parserFrom($x): Closure
    {
        return $x instanceof Closure ? $x : $this->mustEqual($x);
    }

    private function or2(Closure $f, Closure $g): Closure
    {
        return static fn (Intermediate $x, Closure $cc): Result => (
            $f($x, $cc)->except(static fn (): Result => $g($x, $cc))
        );
    }

    /**
     * @param Parser $f
     * @param Parser $g
     * @return Parser
     */
    private function seq(Closure $f, Closure $g): Closure
    {
        return static fn (Intermediate $x, Closure $cc): Result => $f(
            $x,
            static fn (Result $x): Result => (
                $x->then(static fn (Intermediate $x): Result => $g($x, $cc))
                  ->except(static fn ($error): Result => $cc(new Error($error)))
            )
        );
    }

    /**
     * @param int $atom
     * @return Parser
     */
    private function eq(int $atom): Closure
    {
        return static fn (Intermediate $x, Closure $cc): Result => $cc(
            $atom === $x->value() ? $x->accept() : $x->reject()
        );
    }

    /**
     * Makes a variadic function from a binary one (left associative and requires at leat 1 argument):
     * f(f(a, b), c) === variadic(f, [a, b, c]);
     *
     * @param callable(Parser, Parser): Parser $call
     * @param (Parser|string)[] $parse
     * @return Parser
     */
    private function variadic(callable $call, array $parse): Closure
    {
        $parse = array_map([$this, 'parserFrom'], $parse);

        return array_reduce(array_slice($parse, 1), $call, $parse[0]);
    }

    /**
     * @param string $expected
     * @return Parser
     */
    private function mustEqual(string $expected): Closure
    {
        if (strlen($expected) === 1) {
            return $this->eq(ord($expected));
        } elseif ('' === $expected) {
            return $this->nop();
        }

        return $this->simpleSequence(str_split($expected));
    }

    /**
     * @return Parser
     */
    private function nop(): Closure
    {
        return static fn (Intermediate $x, Closure $cc): Result => (
            $cc(new Ok($x))
        );
    }

    private function lazy(callable $call, ...$arguments): Closure
    {
        return static fn (Intermediate $x, Closure $cc): Result => (
            ($call(...$arguments))($x, $cc)
        );
    }
}
