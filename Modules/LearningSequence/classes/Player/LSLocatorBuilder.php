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
     * @return int[][]|string[][]
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
