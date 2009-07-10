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

include_once("classes/class.ilObjectAccess.php");

/**
* Class ilObjMailAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilObjMailAccess extends ilObjectAccess
{
	/**
	 * Returns the number of attachments and the number of bytes used on the
	 * harddisk for mail attachments, by the user with the specified user id.
	 * @param int user id.
	 * @return array('count'=>integer,'size'=>integer),...)
	 *                            // an associative array with the disk
	 *                            // usage in bytes for each object type
	 */
	function _lookupDiskUsageOfUser($user_id)
	{
		require_once "classes/class.ilFileDataMail.php";
		return ilFileDataMail::_lookupDiskUsageOfUser($user_id);
	}
}

?>
