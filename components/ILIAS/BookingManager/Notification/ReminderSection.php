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

class ReminderSection
{
    /**
     * @param list<PoolReservations> $pools
     */
    public function __construct(
        protected string $heading,
        protected array $pools
    ) {
    }

    public function getHeading(): string
    {
        return $this->heading;
    }

    /**
     * @return list<PoolReservations>
     */
    public function getPools(): array
    {
        return $this->pools;
    }
}
