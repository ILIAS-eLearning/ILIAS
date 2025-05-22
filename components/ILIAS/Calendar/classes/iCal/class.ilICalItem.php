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
/**
 * Abstract base class for all ical items (Component, Parameter and Value)
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCalendar
 */
abstract class ilICalItem
{
    protected string $name = '';
    protected string $value = '';
    protected array $items = [];

    public function __construct(string $a_name, string $a_value = '')
    {
        $this->name = $a_name;
        $this->value = $a_value;
    }

    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return trim($this->value);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getItemsByName(string $a_name, bool $a_recursive = true): array
    {
        return [];
    }

    public function addItem(ilICalItem $a_item): void
    {
        $this->items[] = $a_item;
    }
}
