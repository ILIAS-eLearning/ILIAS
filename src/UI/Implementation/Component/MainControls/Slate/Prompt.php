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
 
namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Counter\Counter;
use ILIAS\UI\Component\Counter\Factory as CounterFactory;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Prompts are notifications from the system to the user.
 */
abstract class Prompt extends Slate implements ISlate\Prompt
{
    protected CounterFactory $counter_factory;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        CounterFactory $counter_factory,
        string $name,
        Glyph $symbol
    ) {
        $this->counter_factory = $counter_factory;
        parent::__construct($signal_generator, $name, $symbol);
    }

    protected function getCounterFactory() : CounterFactory
    {
        return $this->counter_factory;
    }

    protected function updateCounter(Counter $counter) : ISlate\Prompt
    {
        $clone = clone $this;
        $clone->symbol = $clone->symbol->withCounter($counter);
        return $clone;
    }

    public function withUpdatedStatusCounter(int $count) : ISlate\Prompt
    {
        $counter = $this->getCounterFactory()->status($count);
        return $this->updateCounter($counter);
    }

    public function withUpdatedNoveltyCounter(int $count) : ISlate\Prompt
    {
        $counter = $this->getCounterFactory()->novelty($count);
        return $this->updateCounter($counter);
    }
}
