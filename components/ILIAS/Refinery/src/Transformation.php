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

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use Exception;
use ilLanguage;
use Closure;

/**
 * @template A
 * @template B
 * @implements Constraint<A>
 * @implements Transformable<A, B>
 */
class Transformation implements Transformable, Constraint
{
    /**
     * @var Closure(Closure(string): string, A, string): string
     */
    private readonly ?Closure $builder;

    /**
     * @param Transformable<A, B> $intern
     */
    public function __construct(
        private readonly Transformable $intern,
        private readonly ilLanguage $language,
        ?callable $builder = null
    ) {
        $this->builder = $builder === null ? null : Closure::fromCallable($builder);
    }

    /**
     * @param A $from
     * @return B
     */
    public function transform($from)
    {
        return $this->applyTo(new Ok($from))->value();
    }

    /**
     * @param Result<A> $result
     * @return Result<B>
     */
    public function applyTo(Result $result): Result
    {
        return $result->then(function ($value): Result {
            try {
                return new Ok($this->intern->transform($value));
            } catch (ConstraintViolationException $e) {
                return $this->build($value, $e, fn() => $e->getTranslatedMessage($this->language->txt(...)));
            } catch (Exception $exception) {
                return $this->build($value, $e, fn() => $e);
            }
        });
    }

    /**
     * @param A $from
     * @return B
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }

    /**
     * @param callable(Closure(string): string, A, string): string $builder
     * @return Constraint<A>
     */
    public function withProblemBuilder(callable $builder): self
    {
        return new self($this->intern, $this->language, $builder);
    }

    /**
     * @param A $value
     * @throws Exception
     */
    public function check($value): void
    {
        $this->intern->transform($value);
    }

    /**
     * @param A $value
     */
    public function accepts($value): bool
    {
        return $this->applyTo(new Ok($value))->isOK();
    }

    /**
     * @param A $value
     * @return null|Exception|string
     */
    public function problemWith($value)
    {
        return $this->applyTo(new Ok($value))
                    ->map(fn() => null)
                    ->except(fn($error) => new Ok($error))->value();
    }

    private function translate(string $lang_var, ...$args): string
    {
        $message = $this->language->txt($lang_var);
        if ($args === []) {
            return $message;
        }
        // @Todo: Check if this can be removed as this feature is currently used nowhere.
        return sprintf($message, ...array_map(function ($v) {
            if (is_array($v)) {
                return "array";
            } elseif (is_null($v)) {
                return "null";
            } elseif (is_object($v) && !method_exists($v, "__toString")) {
                return get_class($v);
            }
            return $v;
        }, $args));
    }

    private function build($value, Exception $exception, Closure $default): Result
    {
        $builder = $this->builder ?? $default;
        return new Error($builder($this->translate(...), $value, $exception));
    }
}
