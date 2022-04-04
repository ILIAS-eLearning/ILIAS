<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\Data\Clock;

use DateTimeZone;

class ClockFactoryImpl implements ClockFactory
{
    /**
     * @inheritDoc
     */
    public function system() : ClockInterface
    {
        return new SystemClock();
    }

    /**
     * @inheritDoc
     */
    public function utc() : ClockInterface
    {
        return new UtcClock();
    }

    /**
     * @inheritDoc
     */
    public function local(DateTimeZone $time_zone) : ClockInterface
    {
        return new LocalClock($time_zone);
    }
}
