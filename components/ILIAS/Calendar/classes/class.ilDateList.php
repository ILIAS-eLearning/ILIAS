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
 * List of dates
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 * @implements Iterator<string, ilDateTime>
 */
class ilDateList implements Iterator, Countable
{
    public const TYPE_DATE = 1;
    public const TYPE_DATETIME = 2;

    /** @var array<string, ilDateTime> */
    protected array $list_item = [];

    protected int $type;

    public function __construct(int $a_type)
    {
        $this->type = $a_type;
        $this->list_item = [];
    }

    public function rewind(): void
    {
        reset($this->list_item);
    }

    public function current(): ilDateTime
    {
        return current($this->list_item);
    }

    public function key(): string
    {
        return key($this->list_item);
    }

    public function next(): void
    {
        next($this->list_item);
    }

    public function valid(): bool
    {
        return key($this->list_item) !== null;
    }

    public function count(): int
    {
        return count($this->list_item);
    }

    /** @return array<string, ilDateTime> */
    public function get(): array
    {
        return $this->list_item;
    }

    public function getAtPosition(int $a_pos): ?ilDateTime
    {
        $counter = 1;
        foreach ($this->get() as $item) {
            if ($counter++ == $a_pos) {
                return $item;
            }
        }

        return null;
    }

    public function add(ilDateTime $date): void
    {
        $this->list_item[(string) $date->get(IL_CAL_UNIX)] = clone $date;
    }

    public function merge(ilDateList $other_list): void
    {
        foreach ($other_list->get() as $new_date) {
            $this->add($new_date);
        }
    }

    public function remove(ilDateTime $remove): void
    {
        $unix_remove = (string) $remove->get(IL_CAL_UNIX);
        if (isset($this->list_item[$unix_remove])) {
            unset($this->list_item[$unix_remove]);
        }
    }

    public function removeByDAY(ilDateTime $remove): void
    {
        foreach ($this->list_item as $key => $dt) {
            if (ilDateTime::_equals($remove, $dt, IL_CAL_DAY, ilTimeZone::UTC)) {
                unset($this->list_item[$key]);
            }
        }
    }

    public function sort(): void
    {
        ksort($this->list_item, SORT_NUMERIC);
    }

    public function __toString(): string
    {
        $out = '<br />';
        foreach ($this->get() as $date) {
            $out .= $date->get(IL_CAL_DATETIME, '', 'Europe/Berlin') . '<br/>';
        }

        return $out;
    }
}
