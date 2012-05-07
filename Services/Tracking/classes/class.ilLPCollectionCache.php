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


/**
* class ilTimingCache
*
* @author Stefan Meyer <meyer@leifos.com> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
include_once 'Services/Tracking/classes/class.ilLPCollections.php';

class ilLPCollectionCache
{
	function &_getItems($a_obj_id, $a_use_subtree_by_id = false)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id]))
		{
			return $cache[$a_obj_id];
		}
		$cache[$a_obj_id] =& ilLPCollections::_getItems($a_obj_id, $a_use_subtree_by_id);
		return $cache[$a_obj_id];
	}

	public static function getGroupedItems($a_obj_id)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id]))
		{
			return $cache[$a_obj_id];
		}
		return $cache[$a_obj_id] = ilLPCollections::getGroupedItems($a_obj_id);
	}
		
}
?>