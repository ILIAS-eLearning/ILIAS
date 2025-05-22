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

namespace ILIAS\Cron\Job\Schedule;

enum JobScheduleType: int
{
    case DAILY = 1;
    case IN_MINUTES = 2;
    case IN_HOURS = 3;
    case IN_DAYS = 4;
    case WEEKLY = 5;
    case MONTHLY = 6;
    case QUARTERLY = 7;
    case YEARLY = 8;
}
