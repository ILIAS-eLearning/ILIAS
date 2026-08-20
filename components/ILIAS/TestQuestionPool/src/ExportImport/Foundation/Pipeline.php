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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipeline as PipelineContract;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipe;
use Closure;
use Throwable;

/**
 * @template TPassable
 * @implements PipelineContract<TPassable>
 *
 * @phpstan-type PipeParam Closure(TPassable, PipeParam)|Pipe: TPassable
 */
class Pipeline implements PipelineContract
{
    /**
     * The object being passed through the pipeline.
     *
     * @var TPassable $passable
     */
    protected mixed $passable;

    /**
     * The array of class pipes.
     *
     * @var list<PipeParam> $pipes
     */
    protected array $pipes = [];

    /**
     * The final callback to be executed after the pipeline ends regardless of the outcome.
     */
    protected ?Closure $finally = null;


    /**
     * @inheritDoc
     */
    public function send(mixed $passable): self
    {
        $this->passable = $passable;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function through(array $pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pipe(Closure|Pipe $pipe): self
    {
        $this->pipes[] = $pipe;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pipeWhen(Closure $condition, Closure|Pipe $pipe): self
    {
        return $this->pipe(fn($passable, $next) => $condition($passable)
            ? $this->executePipe($pipe, $passable, $next)
            : $next($passable));
    }

    /**
     * @inheritDoc
     */
    public function pipeUnless(Closure $condition, Closure|Pipe $pipe): self
    {
        return $this->pipeWhen(fn($passable) => !$condition($passable), $pipe);
    }

    /**
     * @inheritDoc
     */
    public function then(Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        try {
            return $pipeline($this->passable);
        } finally {
            if ($this->finally) {
                ($this->finally)($this->passable);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function thenReturn(): mixed
    {
        return $this->then(fn(mixed $passable): mixed => $passable);
    }

    /**
     * @inheritDoc
     */
    public function finally(Closure $callback): self
    {
        $this->finally = $callback;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pipes(): array
    {
        return $this->pipes;
    }

    /**
     * Get the final piece of the Closure onion.
     */
    protected function prepareDestination(Closure $destination): Closure
    {
        return function (mixed $passable) use ($destination): mixed {
            try {
                return $destination($passable);
            } catch (Throwable $e) {
                $this->handleException($passable, $e);
            }
        };
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     */
    protected function carry(): Closure
    {
        return fn($stack, $pipe) => function ($passable) use ($stack, $pipe): mixed {
            try {
                return $this->executePipe($pipe, $passable, $stack);
            } catch (Throwable $e) {
                $this->handleException($passable, $e);
            }
        };
    }

    /**
     * Execute a single pipe, handling both Closure and Pipe instances.
     * Pipe instances are skipped when their skip() method returns true.
     */
    protected function executePipe(Closure|Pipe $pipe, mixed $passable, Closure $next): mixed
    {
        if ($pipe instanceof Pipe) {
            return $pipe->handle($passable, $next);
        }

        if (is_callable($pipe)) {
            return $pipe($passable, $next);
        }

        throw new \InvalidArgumentException('Invalid pipe');
    }

    /**
     * Handle the given exception.
     *
     * @throws \Throwable
     */
    protected function handleException(mixed $passable, Throwable $e): void
    {
        // Maybe we want to handle specific exceptions differently in the future.
        // For now, we'll just re-throw the exception.'
        throw $e;
    }
}
