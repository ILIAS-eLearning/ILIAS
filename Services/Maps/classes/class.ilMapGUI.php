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
* User interface class for maps
*
* @author Richard Klees <richard.klees@concepts-and-training.de>
* @version $Id$
*
* @ingroup ServicesMaps
*/

abstract class ilMapGUI
{
	protected $mapid;		// string
	protected $width = "500px";		// string
	protected $height = "300px";		// string
	protected $latitude;	// string
	protected $longitude;
	protected $zoom;
	protected $enabletypecontrol = false;
	protected $enableupdatelistener = false;
	protected $enablenavigationcontrol = false;
	protected $enablelargemapcontrol = false;
	protected $user_marker = array();
	
	function __construct()
	{
		global $lng, $tpl;
			
		$lng->loadLanguageModule("maps");
	}

	/**
	* Set Map ID.
	*
	* @param	string	$a_mapid	Map ID
	*/
	function setMapId($a_mapid)
	{
		$this->mapid = $a_mapid;
		return $this;
	}
	
	/**
	* Get Map ID.
	*
	* @return	string	Map ID
	*/
	function getMapId()
	{
		return $this->mapid;
	}

	/**
	* Set Width.
	*
	* @param	string	$a_width	Width
	*/
	function setWidth($a_width)
	{
		$this->width = $a_width;
		return $this;
	}

	/**
	* Get Width.
	*
	* @return	string	Width
	*/
	function getWidth()
	{
		return $this->width;
	}

	/**
	* Set Height.
	*
	* @param	string	$a_height	Height
	*/
	function setHeight($a_height)
	{
		$this->height = $a_height;
		return $this;
	}

	/**
	* Get Height.
	*
	* @return	string	Height
	*/
	function getHeight()
	{
		return $this->height;
	}

	/**
	* Set Latitude.
	*
	* @param	string	$a_latitude	Latitude
	*/
	function setLatitude($a_latitude)
	{
		$this->latitude = $a_latitude;
		return $this;
	}

	/**
	* Get Latitude.
	*
	* @return	string	Latitude
	*/
	function getLatitude()
	{
		return $this->latitude;
	}

	/**
	* Set Longitude.
	*
	* @param	string	$a_longitude	Longitude
	*/
	function setLongitude($a_longitude)
	{
		$this->longitude = $a_longitude;
		return $this;
	}

	/**
	* Get Longitude.
	*
	* @return	string	Longitude
	*/
	function getLongitude()
	{
		return $this->longitude;
	}

	/**
	* Set Zoom.
	*
	* @param	int	$a_zoom	Zoom
	*/
	function setZoom($a_zoom)
	{
		$this->zoom = $a_zoom;
		return $this;
	}

	/**
	* Get Zoom.
	*
	* @return	int	Zoom
	*/
	function getZoom()
	{
		return $this->zoom;
	}

	/**
	* Set Use Map Type Control.
	*
	* @param	boolean	$a_enabletypecontrol	Use Map Type Control
	*/
	function setEnableTypeControl($a_enabletypecontrol)
	{
		$this->enabletypecontrol = $a_enabletypecontrol;
		return $this;
	}

	/**
	* Get Use Map Type Control.
	*
	* @return	boolean	Use Map Type Control
	*/
	function getEnableTypeControl()
	{
		return $this->enabletypecontrol;
	}

	/**
	* Set Use Navigation Control.
	*
	* @param	boolean	$a_enablenavigationcontrol	Use Navigation Control
	*/
	function setEnableNavigationControl($a_enablenavigationcontrol)
	{
		$this->enablenavigationcontrol = $a_enablenavigationcontrol;
		return $this;
	}

	/**
	* Get Use Navigation Control.
	*
	* @return	boolean	Use Navigation Control
	*/
	function getEnableNavigationControl()
	{
		return $this->enablenavigationcontrol;
	}

	/**
	* Set Activate Update Listener.
	*
	* @param	boolean	$a_enableupdatelistener	Activate Update Listener
	*/
	function setEnableUpdateListener($a_enableupdatelistener)
	{
		$this->enableupdatelistener = $a_enableupdatelistener;
		return $this;
	}

	/**
	* Get Activate Update Listener.
	*
	* @return	boolean	Activate Update Listener
	*/
	function getEnableUpdateListener()
	{
		return $this->enableupdatelistener;
	}

	/**
	* Set Large Map Control.
	*
	* @param	boolean	$a_largemapcontrol	Large Map Control
	*/
	function setEnableLargeMapControl($a_largemapcontrol)
	{
		$this->largemapcontrol = $a_largemapcontrol;
		return $this;
	}

	/**
	* Get Large Map Control.
	*
	* @return	boolean	Large Map Control
	*/
	function getEnableLargeMapControl()
	{
		return $this->largemapcontrol;
	}

	/**
	* Enable Central Marker.
	*
	* @param	boolean	$a_centralmarker	Central Marker
	*/
	function setEnableCentralMarker($a_centralmarker)
	{
		$this->centralmarker = $a_centralmarker;
		return $this;
	}

	/**
	* Get Enable Central Marker.
	*
	* @return	boolean	Central Marker
	*/
	function getEnableCentralMarker()
	{
		return $this->centralmarker;
	}

	/**
	* Add user marker
	*
	* @param	int		$a_user_id		User ID
	*/
	function addUserMarker($a_user_id)
	{
		return $this->user_marker[] = $a_user_id;
	}

	/**
	* Get HTML
	*/
	abstract function getHtml();
	
	/**
	* Get User List HTML (to be displayed besides the map)
	*/
	abstract function getUserListHtml();
}
?>
