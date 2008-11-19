<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
* This class represents a location property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilLocationInputGUI extends ilFormPropertyGUI
{
	protected $latitude;
	protected $longitude;
	protected $zoom;
	protected $address;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("location");
	}

	/**
	* Set Latitude.
	*
	* @param	real	$a_latitude	Latitude
	*/
	function setLatitude($a_latitude)
	{
		$this->latitude = $a_latitude;
	}

	/**
	* Get Latitude.
	*
	* @return	real	Latitude
	*/
	function getLatitude()
	{
		return $this->latitude;
	}

	/**
	* Set Longitude.
	*
	* @param	real	$a_longitude	Longitude
	*/
	function setLongitude($a_longitude)
	{
		$this->longitude = $a_longitude;
	}

	/**
	* Get Longitude.
	*
	* @return	real	Longitude
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
	* Set Address.
	*
	* @param        string  $a_Address      Address
	*/
	function setAddress($a_address)
	{
		$this->address = $a_address;
	}
	
	/**
	* Get Address.
	*
	* @return       string  Address
	*/
	function getAddress()
	{
		return $this->address;
	}

	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setLatitude($a_values[$this->getPostVar()]["latitude"]);
		$this->setLongitude($a_values[$this->getPostVar()]["longitude"]);
		$this->setZoom($a_values[$this->getPostVar()]["zoom"]);
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$_POST[$this->getPostVar()]["latitude"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["latitude"]);
		$_POST[$this->getPostVar()]["longitude"] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]["longitude"]);
		if ($this->getRequired() &&
			(trim($_POST[$this->getPostVar()]) == "" || trim($_POST[$this->getPostVar()]) == ""))
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		return true;
	}

	/**
	* Insert property html
	*
	*/
	function insert(&$a_tpl)
	{
		global $tpl, $lng;
		
		$lng->loadLanguageModule("gmaps");
		$a_tpl->setCurrentBlock("prop_location");
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->setVariable("TXT_ZOOM", $lng->txt("gmaps_zoom_level"));
		$a_tpl->setVariable("LOC_DESCRIPTION", $lng->txt("gmaps_std_location_desc"));
		
		$lat = is_numeric($this->getLatitude())
			? $this->getLatitude()
			: 0;
		$long = is_numeric($this->getLongitude())
			? $this->getLongitude()
			: 0;
		$a_tpl->setVariable("PROPERTY_VALUE_LAT", $lat);
		$a_tpl->setVariable("PROPERTY_VALUE_LONG", $long);
		for($i = 0; $i <= 18; $i++)
		{
			$levels[$i] = $i;
		}
		$a_tpl->setVariable("ZOOM_SELECT",
			ilUtil::formSelect($this->getZoom(), $this->getPostVar()."[zoom]",
			$levels, false, true, 0, "", array("id" => "map_".$this->getPostVar()."_zoom",
				"onchange" => "ilUpdateMap('"."map_".$this->getPostVar()."');")));
		$a_tpl->setVariable("MAP_ID", "map_".$this->getPostVar());
		$a_tpl->setVariable("TXT_LOOKUP", $lng->txt("gmaps_lookup_address"));
		$a_tpl->setVariable("TXT_ADDRESS", $this->getAddress());
		
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapGUI.php");
		$map_gui = new ilGoogleMapGUI();
		$map_gui->setMapId("map_".$this->getPostVar());
		$map_gui->setLatitude($lat);
		$map_gui->setLongitude($long);
		$map_gui->setZoom($this->getZoom());
		$map_gui->setEnableTypeControl(true);
		$map_gui->setEnableLargeMapControl(true);
		$map_gui->setEnableUpdateListener(true);
		$map_gui->setEnableCentralMarker(true);
		
		$a_tpl->setVariable("MAP", $map_gui->getHtml());
		
		$a_tpl->parseCurrentBlock();
	}

}
