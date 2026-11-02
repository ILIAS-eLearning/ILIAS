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

class PoolReservations
{
    /**
     * @param list<Reservation> $reservations
     */
    public function __construct(
        protected string $title,
        protected string $goto,
        protected array $reservations
    ) {
        if ($this->goto === '') {
            throw new \InvalidArgumentException('A pool reminder needs a goto link.');
        }
        foreach ($this->reservations as $reservation) {
            if (!$reservation instanceof Reservation) {
                throw new \InvalidArgumentException('A pool holds reservation lines.');
            }
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getGoto(): string
    {
        return $this->goto;
    }

    /**
     * @return list<Reservation>
     */
    public function getReservations(): array
    {
        return $this->reservations;
    }
}
