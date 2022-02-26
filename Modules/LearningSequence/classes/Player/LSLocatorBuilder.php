<?php declare(strict_types=1);

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\KioskMode\LocatorBuilder;
use ILIAS\KioskMode\ControlBuilder;

class LSLocatorBuilder implements LocatorBuilder
{
    /**
     * @var array<array<int|string>>
     */
    protected array $items;

    protected string $command;
    protected ControlBuilder $control_builder;

    public function __construct(string $command, ControlBuilder $control_builder)
    {
        $this->command = $command;
        $this->control_builder = $control_builder;
    }

    /**
     * @var array int[]|string[]
     */
    public function getItems() : array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function end() : ControlBuilder
    {
        return $this->control_builder;
    }

    /**
     * @inheritdoc
     */
    public function item(string $label, int $parameter) : LocatorBuilder
    {
        $this->items[] = [
            'label' => $label,
            'command' => $this->command,
            'parameter' => $parameter
        ];
        return $this;
    }
}
