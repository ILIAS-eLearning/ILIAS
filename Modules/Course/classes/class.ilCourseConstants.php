<?php
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


// @deprecated
define('IL_CRS_ACTIVATION_OFFLINE',0);
define('IL_CRS_ACTIVATION_UNLIMITED',1);
define('IL_CRS_ACTIVATION_LIMITED',2);

define('IL_CRS_SUBSCRIPTION_DEACTIVATED',0);
define('IL_CRS_SUBSCRIPTION_UNLIMITED',1);
define('IL_CRS_SUBSCRIPTION_LIMITED',2);

define('IL_CRS_SUBSCRIPTION_CONFIRMATION',2);
define('IL_CRS_SUBSCRIPTION_DIRECT',3);
define('IL_CRS_SUBSCRIPTION_PASSWORD',4);

define('IL_CRS_VIEW_SESSIONS', 0);
define('IL_CRS_VIEW_OBJECTIVE', 1);
define('IL_CRS_VIEW_TIMING', 2);
define('IL_CRS_VIEW_ARCHIVE', 3);
define('IL_CRS_VIEW_SIMPLE', 4);
define('IL_CRS_VIEW_BY_TYPE', 5);

define('IL_CRS_ARCHIVE_DOWNLOAD',3);
define('IL_CRS_ARCHIVE_NONE',0);


class ilCourseConstants
{
	const CRS_ADMIN = 1;
	const CRS_MEMBER = 2;
	const CRS_TUTOR = 3;
	
	const SUBSCRIPTION_DEACTIVATED = 0;
	const SUBSCRIPTION_UNLIMITED = 1;
	const SUBSCRIPTION_LIMITED = 2;
	
	const MAIL_ALLOWED_ALL = 1;
	const MAIL_ALLOWED_TUTORS = 2;
}

?>