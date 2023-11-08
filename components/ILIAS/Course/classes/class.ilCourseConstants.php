<?php

declare(strict_types=0);

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

class ilCourseConstants
{
    public const CRS_ADMIN = 1;
    public const CRS_MEMBER = 2;
    public const CRS_TUTOR = 3;

    public const SUBSCRIPTION_DEACTIVATED = 0;
    public const SUBSCRIPTION_UNLIMITED = 1;
    public const SUBSCRIPTION_LIMITED = 2;

    public const MAIL_ALLOWED_ALL = 1;
    public const MAIL_ALLOWED_TUTORS = 2;

    public const IL_CRS_VIEW_TIMING_ABSOLUTE = 0;
    public const IL_CRS_VIEW_TIMING_RELATIVE = 1;

    public const IL_CRS_VIEW_SESSIONS = 0;
    public const IL_CRS_VIEW_OBJECTIVE = 1;
    public const IL_CRS_VIEW_TIMING = 2;
    public const IL_CRS_VIEW_SIMPLE = 4;
    public const IL_CRS_VIEW_BY_TYPE = 5;

    public const CRON_TIMINGS_STARTED_TABLE = 'crs_timings_started';
    public const CRON_TIMINGS_EXCEEDED_TABLE = 'crs_timings_exceeded';

    public const IL_CRS_ACTIVATION_OFFLINE = 0;
    public const IL_CRS_ACTIVATION_UNLIMITED = 1;
    public const IL_CRS_ACTIVATION_LIMITED = 2;
    public const IL_CRS_SUBSCRIPTION_DEACTIVATED = 0;
    public const IL_CRS_SUBSCRIPTION_UNLIMITED = 1;
    public const IL_CRS_SUBSCRIPTION_LIMITED = 2;
    public const IL_CRS_SUBSCRIPTION_CONFIRMATION = 2;
    public const IL_CRS_SUBSCRIPTION_DIRECT = 3;
    public const IL_CRS_SUBSCRIPTION_PASSWORD = 4;
    public const IL_CRS_ARCHIVE_DOWNLOAD = 3;
    public const IL_CRS_ARCHIVE_NONE = 0;
}
