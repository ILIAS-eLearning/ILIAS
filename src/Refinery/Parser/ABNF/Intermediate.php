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

class Intermediate
{
    private string $todo;
    private array $accepted;

    public function __construct(string $todo, array $accepted = [])
    {
        $this->todo = $todo;
        $this->accepted = $accepted;
    }

    public function value(): int
    {
        return ord($this->todo);
    }

    public function accept(): Result
    {
        return new Ok((new self(
            substr($this->todo, 1),
            $this->accepted()
        ))->push([new Character(substr($this->todo, 0, 1))]));
    }

    public function push(array $values): self
    {
        return new self(
            $this->todo,
            array_merge($this->accepted, $values)
        );
    }

    public function reject(): Result
    {
        return new Error('Rejected.');
    }

    public function accepted(): array
    {
        return $this->accepted;
    }

    public function done(): bool
    {
        return '' === $this->todo;
    }

    public function onlyTodo(): self
    {
        return new self($this->todo);
    }

    /**
     * Calls the given closure with all accepted values. For convenience the $accepted values are transformed to a string when they are a Character[].
     * Unprocessed characters are wrapped with the Character class to prevent the need for a conversion from a character list (e.g. ['a', 'b'] ) to a string ("ab"),
     * because it's not nice to operate on single length string lists instead of strings.
     *
     * @template A
     * @param Closure(string|A[]): Result<A> $transform
     * @return Result<A>
     */
    public function transform(Closure $transform): Result
    {
        $string = '';
        foreach ($this->accepted as $data) {
            if (!$data instanceof Character) {
                return $transform($this->accepted);
            }
            $string .= $data->value();
        }

        return $transform($string);
    }
}
