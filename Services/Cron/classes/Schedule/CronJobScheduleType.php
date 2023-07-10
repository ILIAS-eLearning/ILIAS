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

namespace ILIAS\Cron\Schedule;

enum CronJobScheduleType: int
{
    case  SCHEDULE_TYPE_DAILY = 1;
    case  SCHEDULE_TYPE_IN_MINUTES = 2;
    case  SCHEDULE_TYPE_IN_HOURS = 3;
    case  SCHEDULE_TYPE_IN_DAYS = 4;
    case  SCHEDULE_TYPE_WEEKLY = 5;
    case  SCHEDULE_TYPE_MONTHLY = 6;
    case  SCHEDULE_TYPE_QUARTERLY = 7;
    case  SCHEDULE_TYPE_YEARLY = 8;
}
