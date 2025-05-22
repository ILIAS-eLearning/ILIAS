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
 * Represents a ical property.
 * E.g DTSTART;VALUE=DATE;TZID=Europe/Berlin:20080214
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilICalProperty extends ilICalItem
{
    /**
     * @inheritDoc
     */
    public function __construct(string $a_name, string $a_value = '')
    {
        parent::__construct($a_name, $a_value);
    }

    /**
     * @inheritDoc
     */
    public function getItemsByName(string $a_name, bool $a_recursive = true): array
    {
        $found = [];
        foreach ($this->getItems() as $item) {
            if ($item->getName() == $a_name) {
                $found[] = $item;
            }
        }
        return $found;
    }
}
