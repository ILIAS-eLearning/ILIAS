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
    
    public function __construct()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
            
        $lng->loadLanguageModule("maps");
    }

    /**
    * Set Map ID.
    *
    * @param	string	$a_mapid	Map ID
    */
    public function setMapId($a_mapid)
    {
        $this->mapid = $a_mapid;
        return $this;
    }
    
    /**
    * Get Map ID.
    *
    * @return	string	Map ID
    */
    public function getMapId()
    {
        return $this->mapid;
    }

    /**
    * Set Width.
    *
    * @param	string	$a_width	Width
    */
    public function setWidth($a_width)
    {
        $this->width = $a_width;
        return $this;
    }

    /**
    * Get Width.
    *
    * @return	string	Width
    */
    public function getWidth()
    {
        return $this->width;
    }

    /**
    * Set Height.
    *
    * @param	string	$a_height	Height
    */
    public function setHeight($a_height)
    {
        $this->height = $a_height;
        return $this;
    }

    /**
    * Get Height.
    *
    * @return	string	Height
    */
    public function getHeight()
    {
        return $this->height;
    }

    /**
    * Set Latitude.
    *
    * @param	string	$a_latitude	Latitude
    */
    public function setLatitude($a_latitude)
    {
        $this->latitude = $a_latitude;
        return $this;
    }

    /**
    * Get Latitude.
    *
    * @return	string	Latitude
    */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
    * Set Longitude.
    *
    * @param	string	$a_longitude	Longitude
    */
    public function setLongitude($a_longitude)
    {
        $this->longitude = $a_longitude;
        return $this;
    }

    /**
    * Get Longitude.
    *
    * @return	string	Longitude
    */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
    * Set Zoom.
    *
    * @param	int	$a_zoom	Zoom
    */
    public function setZoom($a_zoom)
    {
        $this->zoom = $a_zoom;
        return $this;
    }

    /**
    * Get Zoom.
    *
    * @return	int	Zoom
    */
    public function getZoom()
    {
        return $this->zoom;
    }

    /**
    * Set Use Map Type Control.
    *
    * @param	boolean	$a_enabletypecontrol	Use Map Type Control
    */
    public function setEnableTypeControl($a_enabletypecontrol)
    {
        $this->enabletypecontrol = $a_enabletypecontrol;
        return $this;
    }

    /**
    * Get Use Map Type Control.
    *
    * @return	boolean	Use Map Type Control
    */
    public function getEnableTypeControl()
    {
        return $this->enabletypecontrol;
    }

    /**
    * Set Use Navigation Control.
    *
    * @param	boolean	$a_enablenavigationcontrol	Use Navigation Control
    */
    public function setEnableNavigationControl($a_enablenavigationcontrol)
    {
        $this->enablenavigationcontrol = $a_enablenavigationcontrol;
        return $this;
    }

    /**
    * Get Use Navigation Control.
    *
    * @return	boolean	Use Navigation Control
    */
    public function getEnableNavigationControl()
    {
        return $this->enablenavigationcontrol;
    }

    /**
    * Set Activate Update Listener.
    *
    * @param	boolean	$a_enableupdatelistener	Activate Update Listener
    */
    public function setEnableUpdateListener($a_enableupdatelistener)
    {
        $this->enableupdatelistener = $a_enableupdatelistener;
        return $this;
    }

    /**
    * Get Activate Update Listener.
    *
    * @return	boolean	Activate Update Listener
    */
    public function getEnableUpdateListener()
    {
        return $this->enableupdatelistener;
    }

    /**
    * Set Large Map Control.
    *
    * @param	boolean	$a_largemapcontrol	Large Map Control
    */
    public function setEnableLargeMapControl($a_largemapcontrol)
    {
        $this->largemapcontrol = $a_largemapcontrol;
        return $this;
    }

    /**
    * Get Large Map Control.
    *
    * @return	boolean	Large Map Control
    */
    public function getEnableLargeMapControl()
    {
        return $this->largemapcontrol;
    }

    /**
    * Enable Central Marker.
    *
    * @param	boolean	$a_centralmarker	Central Marker
    */
    public function setEnableCentralMarker($a_centralmarker)
    {
        $this->centralmarker = $a_centralmarker;
        return $this;
    }

    /**
    * Get Enable Central Marker.
    *
    * @return	boolean	Central Marker
    */
    public function getEnableCentralMarker()
    {
        return $this->centralmarker;
    }

    /**
    * Add user marker
    *
    * @param	int		$a_user_id		User ID
    */
    public function addUserMarker($a_user_id)
    {
        return $this->user_marker[] = $a_user_id;
    }

    /**
    * Get HTML
    */
    abstract public function getHtml();
    
    /**
    * Get User List HTML (to be displayed besides the map)
    */
    abstract public function getUserListHtml();
}
