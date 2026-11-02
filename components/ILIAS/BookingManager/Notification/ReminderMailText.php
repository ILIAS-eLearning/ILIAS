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

namespace ILIAS\BookingManager\Notification;

class ReminderMailText implements \Stringable
{
    public function __construct(
        protected ?ReminderSection $personal = null,
        protected ?ReminderSection $admin = null
    ) {
    }

    public function __toString(): string
    {
        return $this->sectionText($this->personal) . $this->sectionText($this->admin);
    }

    private function sectionText(?ReminderSection $section): string
    {
        if ($section === null) {
            return '';
        }

        $text = "\n" . $section->getHeading() . "\n";
        $text .= $this->divider() . "\n";
        foreach ($section->getPools() as $pool) {
            $text .= $this->headerLine($pool) . "\n";
            foreach ($pool->getReservations() as $reservation) {
                $text .= '- ' . $reservation . "\n";
            }
        }

        return $text;
    }

    private function headerLine(PoolReservations $pool): string
    {
        return $pool->getTitle() . ' (' . $pool->getGoto() . '):';
    }

    private function divider(): string
    {
        return str_repeat('-', 41);
    }
}
