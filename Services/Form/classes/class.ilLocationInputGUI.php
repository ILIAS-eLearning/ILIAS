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
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setLatitude($a_values[$this->getPostVar()]["latitude"]);
		$this->setLongitude($a_values[$this->getPostVar()]["longitude"]);
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
		global $tpl;
		
		$gm_set = new ilSetting("google_maps");
		
		$tpl->addJavaScript("http://maps.google.com/maps?file=api&amp;v=2&amp;key=".
			$gm_set->get("api_key"));
		$tpl->addJavaScript("Services/JavaScript/js/Basic.js");
		$tpl->addJavaScript("Services/GoogleMaps/js/ServiceGoogleMaps.js");
		$a_tpl->setCurrentBlock("prop_location");
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->setVariable("MAP_ID", "map_".$this->getPostVar());
		$a_tpl->setVariable("PROPERTY_VALUE_LAT", $this->getLatitude());
		$a_tpl->setVariable("PROPERTY_VALUE_LONG", $this->getLongitude());
		$a_tpl->parseCurrentBlock();
	}

}

