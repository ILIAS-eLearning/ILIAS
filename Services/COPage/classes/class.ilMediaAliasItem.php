<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Utilities/classes/class.ilDOMUtil.php");

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
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	var $dom;
	var $hier_id;
	var $purpose;
	var $item_node;

	function __construct(&$a_dom, $a_hier_id, $a_purpose, $a_pc_id = "",
		$a_parent_node_name = "MediaObject")
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->dom = $a_dom;
		$this->parent_node_name = $a_parent_node_name;
		$this->hier_id = $a_hier_id;
		$this->purpose = $a_purpose;
		$this->setPcId($a_pc_id);
		$this->item_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId());
	}

	function getMAItemNode($a_hier_id, $a_purpose, $a_pc_id = "", $a_sub_element = "")
	{
		if ($a_pc_id != "")
		{
			$xpc = xpath_new_context($this->dom);
			$path = "//PageContent[@PCID = '".$a_pc_id."']/".$this->parent_node_name."/MediaAliasItem[@Purpose='$a_purpose']".$a_sub_element;
			$res = xpath_eval($xpc, $path);
			if (count($res->nodeset) == 1)
			{
				return $res->nodeset[0];
			}
		}
		
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$a_hier_id."']/".$this->parent_node_name."/MediaAliasItem[@Purpose='$a_purpose']".$a_sub_element;
		$res = xpath_eval($xpc, $path);
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
			$path = "//PageContent[@PCID = '".$a_pc_id."']/".$this->parent_node_name."/MediaAliasItem[@Purpose='$a_purpose']/Parameter";
			$res = xpath_eval($xpc, $path);
			if (count($res->nodeset) > 0)
			{
				return $res->nodeset;
			}
			return array();
		}
		
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$a_hier_id."']/".$this->parent_node_name."/MediaAliasItem[@Purpose='$a_purpose']/Parameter";
		$res = xpath_eval($xpc, $path);
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
			$path = "//PageContent[@PCID = '".$a_pc_id."']/".$this->parent_node_name."/MediaAliasItem[@Purpose='$a_purpose']/MapArea";
			$res = xpath_eval($xpc, $path);
			if (count($res->nodeset) > 0)
			{
				return $res->nodeset;
			}
			return array();
		}
		
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$a_hier_id."']/".$this->parent_node_name."/MediaAliasItem[@Purpose='$a_purpose']/MapArea";
		$res = xpath_eval($xpc, $path);
		if (count($res->nodeset) > 0)
		{
			return $res->nodeset;
		}
		return array();
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
		$path = "//PageContent[@HierId = '".$this->hier_id."']/".$this->parent_node_name;
		$res = xpath_eval($xpc, $path);
		if (count($res->nodeset) > 0)
		{
			$obj_node = $res->nodeset[0];
			$item_node = $this->dom->create_element("MediaAliasItem");
			$item_node = $obj_node->append_child($item_node);
			$item_node->set_attribute("Purpose", $this->purpose);
			$this->item_node = $item_node;
		}
	}

	/**
	* Set width
	*/
	function setWidth($a_width)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "Layout",
			array("Caption", "TextRepresentation", "Parameter", "MapArea"),
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
			array("Caption", "TextRepresentation", "Parameter", "MapArea"),
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
			array("TextRepresentation", "Parameter", "MapArea"),
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

	/**
	* Set TextRepresentation
	*/
	function setTextRepresentation($a_text_representation)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "TextRepresentation",
			array("Parameter", "MapArea"),
			$a_text_representation, array());
	}

	/**
	* Get TextRepresentation
	*/
	function getTextRepresentation()
	{
		$text_representation_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/TextRepresentation");
		if (is_object($text_representation_node))
		{
			return $text_representation_node->get_content();
		}
	}

	/**
	* check if alias item defines own TextRepresentation or derives TextRepresentation from object
	*
	* @return	boolean		returns true if TextRepresentation is not derived from object
	*/
	function definesTextRepresentation()
	{
		$text_representation_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/TextRepresentation");
		if (is_object($text_representation_node))
		{
			return true;
		}
		return false;
	}

	/**
	* derive TextRepresentation from object (-> TextRepresentation element is removed from media alias item)
	*/
	function deriveTextRepresentation()
	{
		$text_representation_node = $this->getMAItemNode($this->hier_id, $this->purpose,
			$this->getPcId(), "/TextRepresentation");
		if (is_object($text_representation_node))
		{
			$text_representation_node->unlink_node($text_representation_node);
		}
	}

	function setHorizontalAlign($a_halign)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "Layout",
			array("Caption", "TextRepresentation", "Parameter", "MapArea"),
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
		for($i=0; $i < count($par_nodes); $i++)
		{
			$par_node = $par_nodes[$i];
			$par_node->unlink_node($par_node);
		}

		include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");
		if (is_array($a_par_array))
		{
			foreach($a_par_array as $par => $val)
			{
				if (ilMediaItem::checkParameter($par, $val))
				{
					$attributes = array("Name" => $par, "Value" => $val);
					ilDOMUtil::addElementToList($this->dom, $this->item_node,
						"Parameter", array("MapArea"), "", $attributes);
				}
			}
		}
	}

	/**
	* Get all parameters as string
	*/
	function getParameterString()
	{
		$par_nodes = $this->getParameterNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		$par_arr = array();
		for($i=0; $i < count($par_nodes); $i++)
		{
			$par_node = $par_nodes[$i];
			$par_arr[] = $par_node->get_attribute("Name")."=\"".$par_node->get_attribute("Value")."\"";
		}
		return implode($par_arr, ", ");
	}

	/**
	* Get all parameters as array
	*/
	function getParameters()
	{
		$par_nodes = $this->getParameterNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		$par_arr = array();
		for($i=0; $i < count($par_nodes); $i++)
		{
			$par_node = $par_nodes[$i];
			$par_arr[$par_node->get_attribute("Name")] =
				$par_node->get_attribute("Value");
		}
		return $par_arr;
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
				$par_node = $par_nodes[$i];
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
				"HighlightMode" => $maparea_node->get_attribute("HighlightMode"),
				"HighlightClass" => $maparea_node->get_attribute("HighlightClass"),
				"Id" => $maparea_node->get_attribute("Id"),
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
	 * Set highlight mode single area
	 */
	function setAreaHighlightMode($a_nr, $a_mode)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$ma_nodes[$a_nr - 1]->set_attribute("HighlightMode", $a_mode);
		}
	}

	/**
	 * Set highlight class single area
	 */
	function setAreaHighlightClass($a_nr, $a_class)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		if (is_object($ma_nodes[$a_nr - 1]))
		{
			$ma_nodes[$a_nr - 1]->set_attribute("HighlightClass", $a_class);
		}
	}

	/**
	* Add a new area to the map
	*/
	function addMapArea($a_shape_type, $a_coords, $a_title,
		$a_link, $a_id = "")
	{
		$attributes = array("Shape" => $a_shape_type,
			"Coords" => $a_coords, "Id" => $a_id);

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
	 * Delete map areas by id
	 */
	function deleteMapAreaById($a_id)
	{
		$ma_nodes = $this->getMapAreaNodes($this->hier_id, $this->purpose,
			$this->getPcId());
		foreach ($ma_nodes as $node)
		{
			if ($node->get_attribute("Id") == $a_id)
			{
				$node->unlink_node($node);
			}
		}
	}

	/**
	* Delete all map areas
	*/
	function deleteAllMapAreas()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/".$this->parent_node_name."/MediaAliasItem[@Purpose='".$this->purpose."']/MapArea";
		$res = xpath_eval($xpc, $path);
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
		$lng = $this->lng;
		
		if (!$a_st_item->copyOriginal())
		{
			return false;
		}
		$a_st_item->buildMapWorkImage();
		
		// determine ratios (first see whether the instance has w/h defined)
		$width = $this->getWidth();
		$height = $this->getHeight();

		// if instance has no size, use object w/h
		if ($width == 0 && $height == 0)
		{
			$width = $a_st_item->getWidth();
			$height = $a_st_item->getHeight();
		}
		$size = @getimagesize($a_st_item->getMapWorkCopyName());
		$x_ratio = 1;
		if ($size[0] > 0 && $width > 0)
		{
			$x_ratio = $width / $size[0];
		}
		$y_ratio = 1;
		if ($size[1] > 0 && $height > 0)
		{
			$y_ratio = $height / $size[1];
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
