<?php declare(strict_types=0);

/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

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
