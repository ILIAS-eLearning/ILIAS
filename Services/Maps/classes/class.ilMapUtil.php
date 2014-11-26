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
* Map Utility Class.
*
* @author Richard Klees <richard.klees@concepts-and-training.de>
* @version $Id$
*
* @ingroup ServicesMaps
*/
class ilMapUtil
{
	static $_settings = null;

	// Settings

	static function settings() 
	{
		if (self::$_settings === null) {
			self::$_settings = new ilSetting("maps");
		}
		return self::$_settings;
	}
	
	

	/**
	* Checks whether Map feature is activated.
	* API key must be provided.
	*
	* @return	boolean		activated true/false
	*/
	static function isActivated()
	{
		return self::settings()->get("enable") == 1;
	}
	
	// RK TODO: check inputs of setters 
	
	static function setActivated($a_activated)
	{
		self::settings()->set("enable", $a_activated?"1":"0");
	}
	
	static function setType($a_type)
	{
		self::settings()->set("type", $a_type);
	}
	
	static function getType() {
		return self::settings()->get("type");
	}
	
	static function setStdLatitude($a_lat) 
	{
		self::settings()->set("std_latitude", $a_lat);
	}
	
	static function getStdLatitude() 
	{
		return self::settings()->get("std_latitude");
	}
	
	static function setStdLongitude($a_lon) 
	{
		self::settings()->set("std_longitude", $a_lon);
	}
	
	static function getStdLongitude() 
	{
		return self::settings()->get("std_longitude");
	}
	
	static function setStdZoom($a_zoom) 
	{
		self::settings()->set("std_zoom", $a_zoom);
	}
	
	static function getStdZoom() 
	{
		return self::settings()->get("std_zoom");
	}
	
	/**
	* Get default longitude, latitude and zoom.
	*
	* @return	array		array("latitude", "longitude", "zoom")
	*/
	static function getDefaultSettings()
	{
		return array(
			"longitude" => self::settings()->get("std_longitude"),
			"latitude" => self::settings()->get("std_latitude"),
			"zoom" => self::settings()->get("std_zoom"));
	}
	
	/**
	* Get an instance of the GUI class.
	*/
	static public function getMapGUI()
	{
		$type = self::getType();
		switch ($type) {
			case "googlemaps":
				require_once("Services/Maps/classes/class.ilGoogleMapGUI.php");
				return new ilGoogleMapGUI();
			case "openlayers":
				require_once("Services/Maps/classes/class.ilOpenLayersMapGUI.php");
				return new ilOpenLayersMapGUI();
			default:
				require_once("Services/Maps/classes/class.ilGoogleMapGUI.php");
				return new ilGoogleMapGUI();
		}
	}
	
	/**
	* Get a dict { $id => $name } for available maps services.
	*
	* @return array
	*/
	static public function getAvailableMapTypes()
	{
		global $lng;
		$lng->loadLanguageModule("maps");
		return array( "openlayers" 	=> $lng->txt("maps_open_layers_maps")
					, "googlemaps"	=> $lng->txt("maps_google_maps")
					);
	}
}
?>
