<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Legacy\Legacy as ILegacy;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Counter\Factory as CounterFactory;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Symbol\Factory as SymbolFactory;

class Factory implements ISlate\Factory
{
    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;

    /**
     * @var CounterFactory
     */
    protected $counter_factory;

    /**
     * @var SymbolFactory
     */
    protected $symbol_factory;

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
     * @inheritdocs
     */
    public function legacy(string $name, Symbol $symbol, ILegacy $content) : ISlate\Legacy
    {
        return new Legacy($this->signal_generator, $name, $symbol, $content);
    }

    /**
     * @inheritdocs
     */
    public function combined(string $name, Symbol $symbol) : ISlate\Combined
    {
        return new Combined($this->signal_generator, $name, $symbol);
    }

    /**
     * @inheritdocs
     */
    public function notification(string $name, array $notification_items) : ISlate\Notification
    {
        $notification_symbol = $this->symbol_factory->glyph()->notification();
        return new Notification($this->signal_generator, $name, $notification_items,$notification_symbol);
    }
}