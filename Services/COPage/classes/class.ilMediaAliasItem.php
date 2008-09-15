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


/**
* Class ilMediaAliasItem
*
* Media Alias Item, component of a media object (file or reference)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilMediaAliasItem
{
	var $dom;
	var $hier_id;
	var $purpose;
	var $item_node;

	function ilMediaAliasItem(&$a_dom, $a_hier_id, $a_purpose, $a_pc_id = "")
	{
		$this->dom =& $a_dom;
		$this->hier_id = $a_hier_id;
		$this->purpose = $a_purpose;
		$this->setPcId($a_pc_id);
//echo "+$a_pc_id+";
		$this->item_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId());
//var_dump($this->item_node);
	}

	function getMAItemNode($a_hier_id, $a_purpose, $a_pc_id = "", $a_sub_element = "")
	{
		if ($a_pc_id != "")
		{
			$xpc = xpath_new_context($this->dom);
			$path = "//PageContent[@PCID = '".$a_pc_id."']/MediaObject/MediaAliasItem[@Purpose='$a_purpose']".$a_sub_element;
			$res =& xpath_eval($xpc, $path);
			if (count($res->nodeset) == 1)
			{
				return $res->nodeset[0];
			}
		}
		
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$a_hier_id."']/MediaObject/MediaAliasItem[@Purpose='$a_purpose']".$a_sub_element;
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) > 0)
		{
			return $res->nodeset[0];
		}
	}
	
	function getParameterNodes($a_hier_id, $a_purpose, $a_pc_id = "")
	{
		if ($a_pc_id != "")
		{
			$xpc = xpath_new_context($this->dom);
			$path = "//PageContent[@PCID = '".$a_pc_id."']/MediaObject/MediaAliasItem[@Purpose='$a_purpose']/Parameter";
			$res =& xpath_eval($xpc, $path);
			if (count($res->nodeset) > 0)
			{
				return $res->nodeset;
			}
			return array();
		}
		
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$a_hier_id."']/MediaObject/MediaAliasItem[@Purpose='$a_purpose']/Parameter";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) > 0)
		{
			return $res->nodeset;
		}
	}

	function getMapAreaNodes($a_hier_id, $a_purpose, $a_pc_id = "")
	{
		if ($a_pc_id != "")
		{
			$xpc = xpath_new_context($this->dom);
			$path = "//PageContent[@PCID = '".$a_pc_id."']/MediaObject/MediaAliasItem[@Purpose='$a_purpose']/MapArea";
			$res =& xpath_eval($xpc, $path);
			if (count($res->nodeset) > 0)
			{
				return $res->nodeset;
			}
			return array();
		}
		
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$a_hier_id."']/MediaObject/MediaAliasItem[@Purpose='$a_purpose']/MapArea";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) > 0)
		{
			return $res->nodeset;
		}
	}

	/**
	* Set PC Id.
	*
	* @param	string	$a_pcid	PC Id
	*/
	function setPcId($a_pcid)
	{
		$this->pcid = $a_pcid;
	}

	/**
	* Get PC Id.
	*
	* @return	string	PC Id
	*/
	function getPcId()
	{
		return $this->pcid;
	}

	/**
	* check if item node exists
	*
	* @return	 boolean		returns true if item node exists
	*/
	function exists()
	{
		if (is_object($this->item_node))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* inserts new node in dom
	*/
	function insert()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) > 0)
		{
			$obj_node =& $res->nodeset[0];
			$item_node =& $this->dom->create_element("MediaAliasItem");
			$item_node =& $obj_node->append_child($item_node);
			$item_node->set_attribute("Purpose", $this->purpose);
			$this->item_node =& $item_node;
		}
	}

	/**
	* Set width
	*/
	function setWidth($a_width)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "Layout",
			array("Caption", "Parameter", "MapArea"),
			"", array("Width" => $a_width), false);
	}


	/**
	* Get width
	*/
	function getWidth()
	{
		$layout_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/Layout");
		if (is_object($layout_node))
		{
			return $layout_node->get_attribute("Width");
		}
	}

	/**
	* check if alias item defines own size or derives size from object
	*
	* @return	boolean		returns true if size is not derived from object
	*/
	function definesSize()
	{
		$layout_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/Layout");
		if (is_object($layout_node))
		{
			return $layout_node->has_attribute("Width");
		}
		return false;
	}

	/**
	* derive size from object (-> width and height attributes are removed from layout element)
	*/
	function deriveSize()
	{
		$layout_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/Layout");
		if (is_object($layout_node))
		{
			if ($layout_node->has_attribute("Width"))
			{
				$layout_node->remove_attribute("Width");
			}
			if ($layout_node->has_attribute("Height"))
			{
				$layout_node->remove_attribute("Height");
			}
		}
	}

	/**
	* Set Height
	*/
	function setHeight($a_height)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "Layout",
			array("Caption", "Parameter", "MapArea"),
			"", array("Height" => $a_height), false);
	}


	/**
	* Get Height
	*/
	function getHeight()
	{
		$layout_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/Layout");
		if (is_object($layout_node))
		{
			return $layout_node->get_attribute("Height");
		}
	}


	/**
	* Set Caption
	*/
	function setCaption($a_caption)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "Caption",
			array("Parameter", "MapArea"),
			$a_caption, array("Align" => "bottom"));
	}


	/**
	* Get Caption
	*/
	function getCaption()
	{
		$caption_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/Caption");
		if (is_object($caption_node))
		{
			return $caption_node->get_content();
		}
	}

	/**
	* check if alias item defines own caption or derives caption from object
	*
	* @return	boolean		returns true if caption is not derived from object
	*/
	function definesCaption()
	{
		$caption_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/Caption");
		if (is_object($caption_node))
		{
			return true;
		}
		return false;
	}

	/**
	* derive caption from object (-> caption element is removed from media alias item)
	*/
	function deriveCaption()
	{
		$caption_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/Caption");
		if (is_object($caption_node))
		{
			$caption_node->unlink_node($caption_node);
		}
	}

	function setHorizontalAlign($a_halign)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "Layout",
			array("Caption", "Parameter", "MapArea"),
			"", array("HorizontalAlign" => $a_halign), false);
	}


	function getHorizontalAlign()
	{
		$layout_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/Layout");
		if (is_object($layout_node))
		{
			return $layout_node->get_attribute("HorizontalAlign");
		}
	}

	/**
	* set parameter
	*/
	function setParameters($a_par_array)
	{
		$par_nodes = $this->getParameterNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		$par_arr = array();
		for($i=0; $i < count($par_nodes); $i++)
		{
			$par_node =& $par_nodes[$i];
			$par_node->unlink_node($par_node);
		}

		if (is_array($a_par_array))
		{
			foreach($a_par_array as $par => $val)
			{
				$attributes = array ("Name" => $par, "Value" => $val);
				ilDOMUtil::addElementToList($this->dom, $this->item_node,
					"Parameter", array("MapArea"), "", $attributes);
				/* $par_node =& $this->dom->create_element("Parameter");
				$par_node =& $this->item_node->append_child($par_node);
				$par_node->set_attribute("Name", $par);
				$par_node->set_attribute("Value", $val); */
			}
		}
	}

	/**
	* get all parameters
	*/
	function getParameterString()
	{
		$par_nodes = $this->getParameterNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		$par_arr = array();
		for($i=0; $i < count($par_nodes); $i++)
		{
			$par_node =& $par_nodes[$i];
			$par_arr[] = $par_node->get_attribute("Name")."=\"".$par_node->get_attribute("Value")."\"";
		}
		return implode($par_arr, ", ");
	}


	/**
	* get a single parameter
	*/
	function getParameter($a_name)
	{
	}

	/**
	* check if alias item defines own parameters or derives parameters from object
	*
	* @return	boolean		returns true if parameters are not derived from object
	*/
	function definesParameters()
	{
		$par_nodes = $this->getParameterNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (count($par_nodes) > 0)
		{
			return true;
		}
		return false;
	}

	/**
	* derive parameters from object (-> all parameter elements are removed from media alias item)
	*/
	function deriveParameters()
	{
		$par_nodes = $this->getParameterNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (count($par_nodes) > 0)
		{
			for($i=0; $i < count($par_nodes); $i++)
			{
				$par_node =& $par_nodes[$i];
				$par_node->unlink_node($par_node);
			}
		}
	}

	
	/**
	* Get all map areas
	*/
	function getMapAreas()
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		$maparea_arr = array();
		for($i=0; $i < count($ma_nodes); $i++)
		{
			$maparea_node = $ma_nodes[$i];
			$childs = $maparea_node->child_nodes();
			$link = array();
			if ($childs[0]->node_name() == "ExtLink")
			{
				$link = array("LinkType" => "ExtLink",
					"Href" => $childs[0]->get_attribute("Href"),
					"Title" => $childs[0]->get_content());
			}
			if ($childs[0]->node_name() == "IntLink")
			{
				$link = array("LinkType" => "IntLink",
					"Target" => $childs[0]->get_attribute("Target"),
					"Type" => $childs[0]->get_attribute("Type"),
					"TargetFrame" => $childs[0]->get_attribute("TargetFame"),
					"Title" => $childs[0]->get_content());
			}
			$maparea_arr[] = array(
				"Nr" => $i + 1,
				"Shape" => $maparea_node->get_attribute("Shape"),
				"Coords" => $maparea_node->get_attribute("Coords"),
				"Link" => $link);
		}
		
		return $maparea_arr;
	}
	
	/**
	* Set title of area
	*/
	function setAreaTitle($a_nr, $a_title)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$childs = $ma_nodes[$a_nr - 1]->child_nodes();
			if (is_object($childs[0]) &&
				($childs[0]->node_name() == "IntLink" || $childs[0]->node_name() == "ExtLink"))
			{
				$childs[0]->set_content($a_title);
			}
		}
	}
	
	/**
	* Set link of area to an internal one
	*/
	function setAreaIntLink($a_nr, $a_type, $a_target, $a_target_frame)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$title = $this->getTitleOfArea($a_nr);
			ilDOMUtil::deleteAllChildsByName($ma_nodes[$a_nr - 1], array("IntLink", "ExtLink"));
			$attributes = array("Type" => $a_type, "Target" => $a_target,
				"TargetFrame" => $a_target_frame);
			ilDOMUtil::setFirstOptionalElement($this->dom, $ma_nodes[$a_nr - 1], "IntLink",
				array(""), $title, $attributes);
		}
	}
	
	/**
	* Set link of area to an external one
	*/
	function setAreaExtLink($a_nr, $a_href)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$title = $this->getTitleOfArea($a_nr);
			ilDOMUtil::deleteAllChildsByName($ma_nodes[$a_nr - 1], array("IntLink", "ExtLink"));
			$attributes = array("Href" => $a_href);
			ilDOMUtil::setFirstOptionalElement($this->dom, $ma_nodes[$a_nr - 1], "ExtLink",
				array(""), $title, $attributes);
		}
	}

	/**
	* Set shape and coords of single area
	*/
	function setShape($a_nr, $a_shape_type, $a_coords)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$ma_nodes[$a_nr - 1]->set_attribute("Shape", $a_shape_type);
			$ma_nodes[$a_nr - 1]->set_attribute("Coords", $a_coords);
		}
	}

	/**
	* Add a new area to the map
	*/
	function addMapArea($a_shape_type, $a_coords, $a_title,
		$a_link)
	{
		$attributes = array("Shape" => $a_shape_type,
			"Coords" => $a_coords);
		$ma_node = ilDOMUtil::addElementToList($this->dom, $this->item_node,
			"MapArea", array(), "", $attributes);

		if ($a_link["LinkType"] == "int" || $a_link["LinkType"] == "IntLink")
		{
			$attributes = array("Type" => $a_link["Type"],
				"TargetFrame" => $a_link["TargetFrame"],
				"Target" => $a_link["Target"]);
			ilDOMUtil::setFirstOptionalElement($this->dom, $ma_node, "IntLink",
				array(""), $a_title, $attributes);
		}
		if ($a_link["LinkType"] == "ext" || $a_link["LinkType"] == "ExtLink")
		{
			$attributes = array("Href" => $a_link["Href"]);
			ilDOMUtil::setFirstOptionalElement($this->dom, $ma_node, "ExtLink",
				array(""), $a_title, $attributes);
		}
	}
	
	/**
	* Delete a sinlge map area
	*/
	function deleteMapArea($a_nr)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$ma_nodes[$a_nr - 1]->unlink_node($ma_nodes[$a_nr - 1]);
		}
	}
	
	/**
	* Delete all map areas
	*/
	function deleteAllMapAreas()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaAliasItem[@Purpose='".$this->purpose."']/MapArea";
		$res =& xpath_eval($xpc, $path);
		for ($i = 0; $i < count($res->nodeset); $i++)
		{
			$res->nodeset[$i]->unlink_node($res->nodeset[$i]);
		}
	}

	/**
	* Get link type
	*/
	function getLinkTypeOfArea($a_nr)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$childs = $ma_nodes[$a_nr - 1]->child_nodes();
			if ($childs[0]->node_name() == "IntLink")
			{
				return "int";
			}
			if ($childs[0]->node_name() == "ExtLink")
			{
				return "ext";
			}
		}
	}

	/**
	* Get type (only interna link
	*/
	function getTypeOfArea($a_nr)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$childs = $ma_nodes[$a_nr - 1]->child_nodes();
			return $childs[0]->get_attribute("Type");
		}
	}

	/**
	* Get target (only interna link
	*/
	function getTargetOfArea($a_nr)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$childs = $ma_nodes[$a_nr - 1]->child_nodes();
			return $childs[0]->get_attribute("Target");
		}
	}

	/**
	* Get target frame (only interna link
	*/
	function getTargetFrameOfArea($a_nr)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$childs = $ma_nodes[$a_nr - 1]->child_nodes();
			return $childs[0]->get_attribute("TargetFrame");
		}
	}

	/**
	* Get href (only external link)
	*/
	function getHrefOfArea($a_nr)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$childs = $ma_nodes[$a_nr - 1]->child_nodes();
			return $childs[0]->get_attribute("Href");
		}
	}

	/**
	* Get title
	*/
	function getTitleOfArea($a_nr)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$childs = $ma_nodes[$a_nr - 1]->child_nodes();
			return $childs[0]->get_content();
		}
	}

	/**
	* delete full item node from dom
	*/
	function delete()
	{
		if (is_object($this->item_node))
		{
			$this->item_node->unlink_node($this->item_node);
		}
	}
	
	/**
	* make map work copy of image
	*
	* @param	int			$a_area_nr		draw area $a_area_nr only
	* @param	boolean		$a_exclude		true: draw all areas but area $a_area_nr
	*/
	function makeMapWorkCopy($a_st_item, $a_area_nr = 0, $a_exclude = false,
		$a_output_new_area, $a_area_type, $a_coords)
	{
		global $lng;
		
		if (!$a_st_item->copyOriginal())
		{
			return false;
		}
		$a_st_item->buildMapWorkImage();
		
		// determine ratios
		$size = @getimagesize($a_st_item->getMapWorkCopyName());
		$x_ratio = 1;
		if ($size[0] > 0 && $this->getWidth() > 0)
		{
			$x_ratio = $this->getWidth() / $size[0];
		}
		$y_ratio = 1;
		if ($size[1] > 0 && $this->getHeight() > 0)
		{
			$y_ratio = $this->getHeight() / $size[1];
		}

		// draw map areas
		$areas = $this->getMapAreas();
		for ($i=0; $i < count($areas); $i++)
		{
			if (	((($i+1) == $a_area_nr) && !$a_exclude) ||
					((($i+1) != $a_area_nr) && $a_exclude) ||
					($a_area_nr == 0)
				)
			{
				$area = new ilMapArea();
				$area->setShape($areas[$i]["Shape"]);
				$area->setCoords($areas[$i]["Coords"]);
				$area->draw($a_st_item->getMapWorkImage(), $a_st_item->color1, $a_st_item->color2, true,
					$x_ratio, $y_ratio);
			}
		}
		
		if ($a_output_new_area)
		{
			$area = new ilMapArea();
			$area->setShape($a_area_type);
			$area->setCoords($a_coords);
			$area->draw($a_st_item->getMapWorkImage(), $a_st_item->color1, $a_st_item->color2, false,
				$x_ratio, $y_ratio);
		}

		$a_st_item->saveMapWorkImage();
		
		return true;
	}

}
?>
