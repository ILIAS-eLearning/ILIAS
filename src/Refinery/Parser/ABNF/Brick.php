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
use ILIAS\Refinery\Custom\Transformation as Custom;

/**
 * @phpstan-type Continuation Closure(Result<Intermediate>): Result<Intermediate>
 * @phpstan-type Parser Closure(Intermediate, Continuation): Result<Intermediate>
 */
class Brick
{
    private Transform $transform;
    private Primitives $primitives;

    public function __construct()
    {
        $this->transform = new Transform();
        $this->primitives = new Primitives();
    }

    public function apply(Closure $parse, string $input): Result
    {
        return $this->consume($parse, new Intermediate($input))->then(static fn (Intermediate $x): Result => (
            $x->transform(static fn ($value): Result => (
                new Ok(is_array($value) && !is_string(key($value)) ? current($value) : $value)
            ))
        ));
    }

    /**
     * @param Parser $parse
     * @return Transformation
     */
    public function toTransformation(Closure $parse): Transformation
    {
        return new Custom(fn ($input) => $this->apply($parse, $input)->value());
    }

    /**
     * @param int $start
     * @param int $end
     * @return Parser
     */
    public function range(int $start, int $end): Closure
    {
        return static fn (Intermediate $x, Closure $cc): Result => $cc(
            $x->value() >= $start && $x->value() <= $end ? $x->accept() : $x->reject()
        );
    }

    /**
     * @return Parser
     */
    public function alpha(): Closure
    {
        return $this->primitives->simpleEither([
            $this->range(0x41, 0x5A),
            $this->range(0x61, 0x7A),
        ]);
    }

    /**
     * @return Parser
     */
    public function digit(): Closure
    {
        return $this->range(0x30, 0x39);
    }

    public function either(array $parsers): Closure
    {
        return $this->primitives->simpleEither($this->namesFromKeys($parsers));
    }

    /**
     * @param (Parser|string)[] $parse
     * @return Parser
     */
    public function sequence(array $parsers): Closure
    {
        return $this->primitives->simpleSequence($this->namesFromKeys($parsers));
    }

    /**
     * @param int $min
     * @param null|int $max
     * @param Parser|string $parse
     * @return Parser
     */
    public function repeat(int $min, ?int $max, $parse): Closure
    {
        $min = max(0, $min);
        $max = null === $max ? null : max($min, $max) - $min;

        return $this->primitives->simpleSequence([
            ...array_fill(0, $min, $parse),
            $this->primitives->until($max, $parse)
        ]);
    }

    /**
     * @param Transformation $transformation
     * @param Parser $parser
     * @return Parser
     */
    public function transformation(Transformation $transformation, Closure $parse): Closure
    {
        return $this->transform->to($transformation, $parse);
    }

    /**
     * @param array<(string|int), (string|Parser)> $array
     * @return Parser[]
     */
    private function namesFromKeys(array $array): array
    {
        return array_map(
            function ($key) use ($array): Closure {
                $current = $this->primitives->parserFrom($array[$key]);
                return is_string($key) ? $this->transform->as($key, $current) : $current;
            },
            array_keys($array)
        );
    }

    /**
     * It just checks if the given input is fully consumed after parsing and rejects the input otherwise.
     *
     * @param Parser $parse
     * @param Intermediate $intermediate
     * @return Result<Intermediate>
     */
    private function consume(Closure $parse, Intermediate $intermediate): Result
    {
        return $parse($intermediate, static fn (Result $x): Result => $x->then(
            static fn (Intermediate $x): Result => $x->done() ? new Ok($x) : new Error('EOF not reached.')
        ));
    }
}
