<?php

declare(strict_types=1);

use ILIAS\KioskMode\LocatorBuilder;
use ILIAS\KioskMode\ControlBuilder;

/**
 * Class LSTOCBuilder
 */
class LSLocatorBuilder implements LocatorBuilder
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var ControlBuilder
     */
    protected $control_builder;

    public function __construct(string $command, ControlBuilder $control_builder)
    {
        $this->command = $command;
        $this->control_builder = $control_builder;
    }

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
    public function item(string $label, int $parameter, $state = null) : LocatorBuilder
    {
        $this->items[] = [
            'label' => $label,
            'command' => $this->command,
            'parameter' => $parameter
        ];
        return $this;
    }
}
