<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Google Map Utility Class.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesGoogleMaps
*/
class ilGoogleMapUtil
{

	/**
	* Checks whether Google Map feature is activated.
	* API key must be provided.
	*
	* @return	boolean		activated true/false
	*/
	static function isActivated()
	{
		$gm_set = new ilSetting("google_maps");
		if ($gm_set->get("enable"))
		{
			return true;
		}

		return false;
	}
	
	/**
	* Get default longitude, latitude and zoom.
	*
	* @return	array		array("latitude", "longitude", "zoom")
	*/
	static function getDefaultSettings()
	{
		$gm_set = new ilSetting("google_maps");

		return array(
			"longitude" => $gm_set->get("std_longitude"),
			"latitude" => $gm_set->get("std_latitude"),
			"zoom" => $gm_set->get("std_zoom"));
	}

}
?>
