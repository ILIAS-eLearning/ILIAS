<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCMap
*
* Map content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCMap extends ilPageContent
{
	var $dom;
	var $map_node;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("map");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->map_node =& $a_node->first_child();		// this is the Map node
	}

	/**
	* Create map node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id)
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->map_node =& $this->dom->create_element("Map");
		$this->map_node =& $this->node->append_child($this->map_node);
		$this->map_node->set_attribute("Latitude", "0");
		$this->map_node->set_attribute("Longitude", "0");
		$this->map_node->set_attribute("Zoom", "3");
	}

	/**
	* Set latitude of map
	*
	* @param	string	$a_lat		latitude
	*/
	function setLatitude($a_lat)
	{
		if (!empty($a_lat))
		{
			$this->map_node->set_attribute("Latitude", $a_lat);
		}
		else
		{
			if ($this->map_node->has_attribute("Latitude"))
			{
				$this->map_node->remove_attribute("Latitude");
			}
		}
	}

	/**
	* Get latitude of map.
	*
	* @return	string		latitude
	*/
	function getLatitude()
	{
		if (is_object($this->map_node))
		{
			return $this->map_node->get_attribute("Latitude");
		}
	}

	/**
	* Set longitude of map
	*
	* @param	string	$a_long		longitude
	*/
	function setLongitude($a_long)
	{
		if (!empty($a_long))
		{
			$this->map_node->set_attribute("Longitude", $a_long);
		}
		else
		{
			if ($this->map_node->has_attribute("Longitude"))
			{
				$this->map_node->remove_attribute("Longitude");
			}
		}
	}

	/**
	* Get longitude of map.
	*
	* @return	string		longitude
	*/
	function getLongitude()
	{
		if (is_object($this->map_node))
		{
			return $this->map_node->get_attribute("Longitude");
		}
	}

	/**
	* Set zoom of map
	*
	* @param	string	$a_zoom		zoom
	*/
	function setZoom($a_zoom)
	{
		if (!empty($a_zoom))
		{
			$this->map_node->set_attribute("Zoom", $a_zoom);
		}
		else
		{
			if ($this->map_node->has_attribute("Zoom"))
			{
				$this->map_node->remove_attribute("Zoom");
			}
		}
	}

	/**
	* Get zoom of map.
	*
	* @return	string		zoom
	*/
	function getZoom()
	{
		if (is_object($this->map_node))
		{
			return $this->map_node->get_attribute("Zoom");
		}
	}
	
	/**
	* Set Layout
	*
	* @param	integer	$a_width			Width
	* @param	integer	$a_height			Height
	* @param	integer	$a_horizonal_align	Horizontal Alignment
	*/
	function setLayout($a_width, $a_height, $a_horizontal_align)
	{
		if (is_object($this->map_node))
		{
			ilDomUtil::setFirstOptionalElement($this->dom, $this->map_node,
				"Layout", array("MapCaption"), "", array("Width" => $a_width,
					"Height" => $a_height, "HorizontalAlign" => $a_horizontal_align));
		}
	}

	/**
	* Get Width.
	*
	* @return	integer	Width
	*/
	function getWidth()
	{
		if (is_object($this->map_node))
		{
			$childs = $this->map_node->child_nodes();
			foreach($childs as $child)
			{
				if ($child->node_name() == "Layout")
				{
					return $child->get_attribute("Width");
				}
			}
		}
	}

	/**
	* Get Height.
	*
	* @return	integer	Height
	*/
	function getHeight()
	{
		if (is_object($this->map_node))
		{
			$childs = $this->map_node->child_nodes();
			foreach($childs as $child)
			{
				if ($child->node_name() == "Layout")
				{
					return $child->get_attribute("Height");
				}
			}
		}
	}

	/**
	* Get Horizontal Alignment.
	*
	* @return	string	Horizontal Alignment
	*/
	function getHorizontalAlign()
	{
		if (is_object($this->map_node))
		{
			$childs = $this->map_node->child_nodes();
			foreach($childs as $child)
			{
				if ($child->node_name() == "Layout")
				{
					return $child->get_attribute("HorizontalAlign");
				}
			}
		}
	}

	/**
	* Set Caption.
	*
	* @param	string	$a_caption	Caption
	*/
	function setCaption($a_caption)
	{
		if (is_object($this->map_node))
		{
			ilDomUtil::setFirstOptionalElement($this->dom, $this->map_node,
				"MapCaption", array(), $a_caption, array());
		}
	}

	/**
	* Get Caption.
	*
	* @return	string	Caption
	*/
	function getCaption()
	{
		if (is_object($this->map_node))
		{
			$childs = $this->map_node->child_nodes();
			foreach($childs as $child)
			{
				if ($child->node_name() == "MapCaption")
				{
					return $child->get_content();
				}
			}
		}
	}

	static function handleCaptionInput($a_text)
	{
		$a_text = str_replace(chr(13).chr(10),"<br />",$a_text);
		$a_text = str_replace(chr(13),"<br />", $a_text);
		$a_text = str_replace(chr(10),"<br />", $a_text);
		
		return $a_text;
	}
	
	static function handleCaptionFormOutput($a_text)
	{
		$a_text = str_replace("<br />", "\n", $a_text);
		$a_text = str_replace("<br/>", "\n", $a_text);
		
		return $a_text;
	}

}

?>
