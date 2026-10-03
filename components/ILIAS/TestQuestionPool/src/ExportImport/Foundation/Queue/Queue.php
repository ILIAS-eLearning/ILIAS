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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Queue;

/** @implements \IteratorAggregate<int, Processor> */
final class Queue implements \IteratorAggregate
{
    /** @var list<Processor> */
    private readonly array $processors;

    public function __construct(Processor ...$processors)
    {
        $this->processors = $processors;
    }

    public function process(object $carry): object
    {
        foreach ($this->processors as $processor) {
            $processor->process($carry);
        }

        return $carry;
    }

    public function get(string $class): Processor
    {
        $matches = array_values(array_filter(
            $this->processors,
            static fn(Processor $processor): bool => $processor instanceof $class
        ));

        if (count($matches) !== 1) {
            throw new \InvalidArgumentException(
                count($matches) === 0
                    ? "Processor {$class} not found"
                    : "Processor {$class} is ambiguous"
            );
        }

        return $matches[0];
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->processors);
    }
}
