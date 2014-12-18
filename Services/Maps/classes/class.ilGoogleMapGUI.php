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
* User interface class for google maps
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMaps
*/

require_once("Services/Maps/classes/class.ilMapGUI.php");

class ilGoogleMapGUI extends ilMapGUI
{
	function __construct()
	{
		parent::__construct();
		
		// it's in getHTML too. why is it there twice? 
		/*$tpl->addJavaScript("//maps.google.com/maps/api/js?sensor=false", false);
		$tpl->addJavaScript("Services/Maps/js/ServiceGoogleMaps.js");*/
	}

	/**
	* Get HTML
	*/
	function getHtml()
	{
		global $tpl;
		
		$this->tpl = new ilTemplate("tpl.google_map.html",
			true, true, "Services/Maps");
		
		$tpl->addJavaScript("//maps.google.com/maps/api/js?sensor=false", false);
		$tpl->addJavaScript("Services/Maps/js/ServiceGoogleMaps.js");

		// add user markers
		$cnt = 0;
		foreach($this->user_marker as $user_id)
		{
			if (ilObject::_exists($user_id))
			{
				$user = new ilObjUser($user_id);
				if ($user->getLatitude() != 0 && $user->getLongitude() != 0 &&
					$user->getPref("public_location") == "y")
				{
					$this->tpl->setCurrentBlock("user_marker");
					$this->tpl->setVariable("UMAP_ID",
						$this->getMapId());
					$this->tpl->setVariable("CNT", $cnt);

					$this->tpl->setVariable("ULAT", htmlspecialchars($user->getLatitude()));
					$this->tpl->setVariable("ULONG", htmlspecialchars($user->getLongitude()));
					$info = htmlspecialchars($user->getFirstName()." ".$user->getLastName());
					$delim = "<br \/>";
					if ($user->getPref("public_institution") == "y")
					{
						$info.= $delim.htmlspecialchars($user->getInstitution());
						$delim = ", ";
					}
					if ($user->getPref("public_department") == "y")
					{
						$info.= $delim.htmlspecialchars($user->getDepartment());
					}
					$delim = "<br \/>";
					if ($user->getPref("public_street") == "y")
					{
						$info.= $delim.htmlspecialchars($user->getStreet());
					}
					if ($user->getPref("public_zip") == "y")
					{
						$info.= $delim.htmlspecialchars($user->getZipcode());
						$delim = " ";
					}
					if ($user->getPref("public_city") == "y")
					{
						$info.= $delim.htmlspecialchars($user->getCity());
					}
					$delim = "<br \/>";
					if ($user->getPref("public_country") == "y")
					{
						$info.= $delim.htmlspecialchars($user->getCountry());
					}
					$this->tpl->setVariable("USER_INFO",
						$info);
					$this->tpl->setVariable("IMG_USER",
						$user->getPersonalPicturePath("xsmall"));
					$this->tpl->parseCurrentBlock();
					$cnt++;
				}
			}
		}

		$this->tpl->setVariable("MAP_ID", $this->getMapId());
		$this->tpl->setVariable("WIDTH", $this->getWidth());
		$this->tpl->setVariable("HEIGHT", $this->getHeight());
		$this->tpl->setVariable("LAT", $this->getLatitude());
		$this->tpl->setVariable("LONG", $this->getLongitude());
		$this->tpl->setVariable("ZOOM", (int) $this->getZoom());
		$type_control = $this->getEnableTypeControl()
			? "true"
			: "false";
		$this->tpl->setVariable("TYPE_CONTROL", $type_control);
		$nav_control = $this->getEnableNavigationControl()
			? "true"
			: "false";
		$this->tpl->setVariable("NAV_CONTROL", $nav_control);
		$update_listener = $this->getEnableUpdateListener()
			? "true"
			: "false";
		$this->tpl->setVariable("UPDATE_LISTENER", $update_listener);
		$large_map_control = $this->getEnableLargeMapControl()
			? "true"
			: "false";
		$this->tpl->setVariable("LARGE_CONTROL", $large_map_control);
		$central_marker = $this->getEnableCentralMarker()
			? "true"
			: "false";
		$this->tpl->setVariable("CENTRAL_MARKER", $central_marker);

		return $this->tpl->get();
	}
	
	/**
	* Get User List HTML (to be displayed besides the map)
	*/
	function getUserListHtml()
	{
		global $tpl;
		
		$list_tpl = new ilTemplate("tpl.google_map_user_list.html",
			true, true, "Services/Maps");
			
		$cnt = 0;
		foreach($this->user_marker as $user_id)
		{
			if (ilObject::_exists($user_id))
			{
				$user = new ilObjUser($user_id);
				$this->css_row = ($this->css_row != "tblrow1_mo")
					? "tblrow1_mo"
					: "tblrow2_mo";
				if ($user->getLatitude() != 0 && $user->getLongitude() != 0
					&& $user->getPref("public_location") == "y")
				{
					$list_tpl->setCurrentBlock("item");
					$list_tpl->setVariable("MARKER_CNT", $cnt);
					$list_tpl->setVariable("MAP_ID", $this->getMapId());
					$cnt++;
				}
				else
				{
					$list_tpl->setCurrentBlock("item_no_link");
				}
				$list_tpl->setVariable("CSS_ROW", $this->css_row);
				$list_tpl->setVariable("TXT_USER", $user->getLogin());
				$list_tpl->setVariable("IMG_USER",
					$user->getPersonalPicturePath("xxsmall"));
				$list_tpl->parseCurrentBlock();
				$list_tpl->touchBlock("row");
			}
		}
		
		return $list_tpl->get();
	}

}
?>
