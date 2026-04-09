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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts;

/**
 * A pipeline is a sequence of pipes that are executed in order. A pipe can be a closure or an class that implements
 * the Pipe interface.
 *
 * @template TPassable
 *
 * @phpstan-type PipeParam \Closure(TPassable, PipeParam)|Pipe: TPassable
 */
interface Pipeline
{
    /**
     * Set the object being sent through the pipeline.
     *
     * @param TPassable $passable
     */
    public function send(mixed $passable): self;

    /**
     * Set the array of pipes.
     *
     * @param list<PipeParam> $pipes
     */
    public function through(array $pipes): self;

    /**
     * Push additional pipe onto the pipeline.
     *
     * @param PipeParam $pipes
     */
    public function pipe(\Closure|Pipe $pipe): self;

    /**
     * Push additional pipe onto the pipeline which will be executed when the condition is met.
     *
     * @param \Closure(TPassable): bool $condition
     * @param PipeParam $pipe
     */
    public function pipeWhen(\Closure $condition, \Closure|Pipe $pipe): self;

    /**
     * Push additional pipe onto the pipeline which will be executed when the condition is not met.
     *
     * @param \Closure(TPassable): bool $condition
     * @param PipeParam $pipe
     */
    public function pipeUnless(\Closure $condition, \Closure|Pipe $pipe): self;

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param \Closure(TPassable): TPassable $destination
     * @return TPassable|mixed
     */
    public function then(\Closure $destination): mixed;

    /**
     * Run the pipeline and return the result.
     *
     * @return TPassable
     */
    public function thenReturn(): mixed;

    /**
     * Set a final callback to be executed after the pipeline ends regardless of the outcome.
     *
     * @param \Closure(TPassable): TPassable $callback
     */
    public function finally(\Closure $callback): self;

    /**
     * Get the array of pipes.
     *
     * @return list<PipeParam>
     */
    public function pipes(): array;
}
