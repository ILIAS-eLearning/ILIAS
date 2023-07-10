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

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Legacy\Legacy as ILegacy;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Counter\Factory as CounterFactory;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Symbol\Factory as SymbolFactory;
use ILIAS\UI\Component\Menu\Drilldown as IDrilldownMenu;

class Factory implements ISlate\Factory
{
    protected SignalGeneratorInterface $signal_generator;
    protected CounterFactory $counter_factory;
    protected SymbolFactory $symbol_factory;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        CounterFactory $counter_factory,
        SymbolFactory $symbol_factory
    ) {
        $this->signal_generator = $signal_generator;
        $this->counter_factory = $counter_factory;
        $this->symbol_factory = $symbol_factory;
    }

    /**
     * @inheritdoc
     */
    public function legacy(string $name, Symbol $symbol, ILegacy $content): ISlate\Legacy
    {
        return new Legacy($this->signal_generator, $name, $symbol, $content);
    }

    /**
     * @inheritdoc
     */
    public function combined(string $name, Symbol $symbol): ISlate\Combined
    {
        return new Combined($this->signal_generator, $name, $symbol);
    }

    /**
     * @inheritdoc
     */
    public function notification(string $name, array $notification_items): ISlate\Notification
    {
        $notification_symbol = $this->symbol_factory->glyph()->notification();
        return new Notification($this->signal_generator, $name, $notification_items, $notification_symbol);
    }

    /**
     * @inheritdoc
     */
    public function drilldown(string $name, Symbol $symbol, IDrilldownMenu $drilldown): ISlate\Drilldown
    {
        return new Drilldown($this->signal_generator, $name, $symbol, $drilldown);
    }
}
