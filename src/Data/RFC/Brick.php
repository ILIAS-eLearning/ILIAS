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

namespace ILIAS\Data\RFC;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use Closure;

/**
 * @phpstan-type Continuation Closure(Result<Intermediate>): Result<Intermediate>
 * @phpstan-type Parser Closure(Intermediate, Continuation): Result<Intermediate>
 *
 * If you want to use this class, please read the documentation above each (public) method.
 *
 * If you want to edit or add code in this class read below:
 * Type of parser: (Intermediate, (Result): Result): Result
 * Type of continuation: (Result): Result
 * Only immutable values are used.
 * $cc stands for "current continuation". https://en.wikipedia.org/wiki/Call-with-current-continuation
 * This is used to be able to capture the next computation and run it again with different input in case of an error.
 * The $cc is used for example in the `either` method to re run the computation chain with the next branch if the current failed.
 * $x holds the "state". Everything that is parsed, the current value and the data that still needs to be parsed (Either Result<Intermediate> or Intermediate).
 *
 * To restart the whole computation (on error):
 * $parse($x, $cc)->except(function () use ($x, $cc) {
 *     return $parse_something_different($x, $cc);
 * });
 *
 * To get the result of the parsers "children" (in case of `either` these would be the branches):
 * $parse($x, function ($x) use ($cc) {
 *    $x->then(function($x) use ($cc) {
 *        // $x is the successful value.
 *        return $cc(new Ok($x));
 *    })->except(function ($error) use ($cc) {
 *        return $cc(new Error($error)); // This is necessary because the $cc MUST always be called when returning a value.
 *    });
 * });
 *
 * To consume a value:
 * return $cc(somePredicate($x->value()) ? $x->accept() : $x->reject());
 */
class Brick
{
    /**
     * Used to invoke the created parser.
     *
     * It just checks if the given input is fully consumed after parsing and rejects the input otherwise.
     *
     * Example:
     * $this->apply($this->either(['a', 'b']), new Intermediate('a')) => new Ok('a');
     * $this->apply($this->either(['a', 'b']), new Intermediate('b')) => new Ok('b');
     * $this->apply($this->either(['a', 'b']), new Intermediate('c')) => new Error();
     * $this->apply($this->either(['a', 'b']), new Intermediate('ab')) => new Error();
     *
     * @param Parser $parse
     * @param Intermediate $intermediate
     * @return Result<Intermediate>
     */
    public function apply(Closure $parse, Intermediate $intermediate) : Result
    {
        return $parse($intermediate, static function (Result $x) : Result {
            return $x->then(static function (Intermediate $x) : Result {
                return $x->done() ? new Ok($x) : new Error('EOF not reached.');
            });
        });
    }
    
    // Consumer methods.

    /**
     * ABNF equivalent to value ranges:
     * fu = %x30-37 is equivalent to $fu = $this->range(0x30, 0x37).
     *
     * @param int $start
     * @param int $end
     * @return Parser
     */
    public function range(int $start, int $end) : Closure
    {
        return static function (Intermediate $x, Closure $cc) use ($start, $end) {
            return $cc($x->value() >= $start && $x->value() <= $end ? $x->accept() : $x->reject());
        };
    }

    /**
     * ABNF equivalent to ALPHA:
     * fu = ALPHA is equivalent to $fu = $this->alpha();
     *
     * @return Parser
     */
    public function alpha() : Closure
    {
        return $this->either([
            $this->range(0x41, 0x5A),
            $this->range(0x61, 0x7A),
        ]);
    }

    /**
     * ABNF equivalent to DIGIT:
     * fu = DIGIT is equivalent to $fu = $this->digit();
     *
     * @return Parser
     */
    public function digit() : Closure
    {
        return $this->range(0x30, 0x39);
    }

    // Composition methods.

    /**
     * ABNF equivalent to alternative:
     * fu = a / b is equivalent to $fu = $this->either([a, b]);
     *
     * @param (Parser|string)[] $parse
     * @return Parser
     */
    public function either(array $parse) : Closure
    {
        return $this->tooMany([$this, 'or2'], $parse);
    }

    /**
     * ABNF equivalent to concatenation:
     * fu = a b is equivalent to $fu = $this->sequence([a, b]).
     *
     * @param (Parser|string)[] $parse
     * @return Parser
     */
    public function sequence(array $parse) : Closure
    {
        return $this->tooMany([$this, 'seq'], $parse);
    }

    /**
     * ABNF equivalent to variable repetition:
     * fu = <a>*<b>element is equivalent to $fu = $this->repeat(<a>, <b>, element).
     * fu = *<b>element is equivalent to $fu = $this->repeat(0, <b>, element).
     * fu = 1*element is equivalent to $fu = $this->repeat(1, null, element).
     * fu = *element is equivalent to $fu = $this->repeat(0, null, element).
     * fu = [element] is equivalent to 0*1element and therefore equivalent to $fu = $this->repeat(0, 1, element).
     *
     * @param int $min
     * @param null|int $max
     * @param Parser|string $parse
     * @return Parser
     */
    public function repeat(int $min, ?int $max, $parse) : Closure
    {
        $min = max(0, $min);
        $max = null === $max ? null : max($min, $max) - $min;

        return $this->sequence([
            ...array_fill(0, $min, $parse),
            $this->until($max, $parse)
        ]);
    }

    /**
     * @param null|int $max
     * @param Parser|string $parse
     * @return Parser
     */
    private function until(?int $max, $parse) : Closure
    {
        if (0 === $max) {
            return $this->nop();
        }
        $max = null === $max ? null : $max - 1;

        return ($this->either([
            $this->nop(),
            $this->sequence([
                $parse,
                $this->lazy([$this, 'until'], $max, $parse)
            ])
        ]));
    }

    /**
     * @param Parser $f
     * @param Parser $g
     * @return Parser
     */
    private function or2(Closure $f, Closure $g) : Closure
    {
        return static function (Intermediate $x, Closure $cc) use ($f, $g) : Result {
            return $f($x, $cc)->except(static function () use ($x, $g, $cc) : Result {
                return $g($x, $cc);
            });
        };
    }

    /**
     * @param int $atom
     * @return Parser
     */
    private function eq(int $atom) : Closure
    {
        return static function (Intermediate $x, Closure $cc) use ($atom) : Result {
            return $cc($atom === $x->value() ? $x->accept() : $x->reject());
        };
    }

    /**
     * @param Parser $f
     * @param Parser $g
     * @return Parser
     */
    private function seq(Closure $f, Closure $g) : Closure
    {
        return static function (Intermediate $x, Closure $cc) use ($f, $g) : Result {
            return $f($x, static function (Result $x) use ($g, $cc) : Result {
                return $x->then(static function (Intermediate $x) use ($g, $cc) : Result {
                    return $g($x, $cc);
                })->except(static function ($error) use ($cc) : Result {
                    return $cc(new Error($error));
                });
            });
        };
    }

    /**
     * Makes a variadic function from a binary one (left associative and requires at leat 1 argument):
     * f(f(a, b), c) === tooMany(f, [a, b, c]);
     *
     * @param callable(Parser, Parser): Parser $call
     * @param (Parser|string)[] $parse
     * @return Parser
     */
    private function tooMany(callable $call, array $parse) : Closure
    {
        $parse = array_map([$this, 'parserFrom'], $parse);

        return array_reduce(array_slice($parse, 1), $call, $parse[0]);
    }

    /**
     * If it is not a closure it will be transformed into a sequence of characters.
     * This is for convenience so this is possible:
     * $this->either(['ab', 'cd']) === $this->either([
     *     $this->sequence([$this->eq('a'), $this->eq('b')])
     *     $this->sequence([$this->eq('c'), $this->eq('d')])
     * ]);
     *
     * @param Parser|string $x
     * @return Parser
     */
    private function parserFrom($x) : Closure
    {
        return $x instanceof Closure ? $x : $this->mustEqual($x);
    }

    /**
     * @param string $expected
     * @return Parser
     */
    private function mustEqual(string $expected) : Closure
    {
        if (strlen($expected) === 1) {
            return $this->eq(ord($expected));
        } elseif ('' === $expected) {
            return $this->nop();
        }

        return $this->sequence(str_split($expected));
    }

    /**
     * @return Parser
     */
    private function nop() : Closure
    {
        return static function (Intermediate $x, Closure $cc) : Result {
            return $cc(new Ok($x));
        };
    }

    private function lazy(callable $call, ...$arguments) : Closure {
        return static function (Intermediate $x, Closure $cc) use ($call, $arguments) : Result {
            return ($call(...$arguments))($x, $cc);
        };
    }
}
