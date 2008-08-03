<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./Services/COPage/classes/class.ilInternalLink.php");

define("IL_AREA_RECT", "Rect");
define("IL_AREA_CIRCLE", "Circle");
define("IL_AREA_POLY", "Poly");

define("IL_INT_LINK", "int");
define("IL_EXT_LINK", "ext");

define("IL_LT_STRUCTURE", "StructureObject");
define("IL_LT_PAGE", "PageObject");
define("IL_LT_MEDIA", "MediaObject");
define("IL_LT_GLITEM", "GlossaryItem");

define("IL_TF_MEDIA", "Media");
define("IL_TF_FAQ", "FAQ");
define("IL_TF_GLOSSARY", "Glossary");
define("IL_TF_NEW", "New");


/**
* Class ilMapArea
*
* Map Area of an Image Map, subobject of Media Item
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
*/
class ilMapArea
{
	var $ilias;
	var $item_id;
	var $nr;
	var $shape;
	var $coords;
	var $title;
	var $linktype;
	var $xl_title;
	var $xl_href;
	var $il_target;
	var $il_type;
	var $il_target_frame;


	/**
	* map area
	*
	* @param	int		$a_item_id		parent media item id
	* @param	int		$a_nr			map area number within media item
	*/
	function ilMapArea($a_item_id = 0, $a_nr = 0)
	{
		global $ilias;

		$this->ilias =& $ilias;
		$this->title = "";

		if ($a_item_id !=0 && $a_nr != 0)
		{
			$this->setItemId($a_item_id);
			$this->setNr($a_nr);
			$this->read();
		}
	}

	/**
	* create persistent map area object in db
	*/
	function create()
	{
		global $ilDB;

		$q = "INSERT INTO map_area (item_id, nr, shape, ".
			"coords, link_type, title, href, target, type, target_frame) ".
			" VALUES (".
			$ilDB->quote($this->getItemId()).",".
			$ilDB->quote($this->getNr()).",".
			$ilDB->quote($this->getShape()).",".
			$ilDB->quote($this->getCoords()).",".
			$ilDB->quote($this->getLinkType()).",".
			$ilDB->quote($this->getTitle()).",".
			$ilDB->quote($this->getHref()).",".
			$ilDB->quote($this->getTarget()).",".
			$ilDB->quote($this->getType()).",".
			$ilDB->quote($this->getTargetFrame()).")";
		$ilDB->query($q);
	}

	/**
	* get maximum nr of media item (static)
	*
	* @param	int		$a_item_id	 item id
	*
	* @return	int		maximum nr
	*/
	function _getMaxNr($a_item_id)
	{
		global $ilDB;
		
		$q = "SELECT max(nr) AS max_nr FROM map_area WHERE item_id=".
			$ilDB->quote($a_item_id);
		$max_set = $ilDB->query($q);
		$max_rec = $max_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $max_rec["max_nr"];
	}

	/**
	* read map area data into object (item id and nr must be set)
	*/
	function read()
	{
		global $ilDB;
		
		$q = "SELECT * FROM map_area WHERE item_id=".
			$ilDB->quote($this->getItemId()).
			" AND nr=".$ilDB->quote($this->getNr());
		$area_set = $ilDB->query($q);
		$area_rec = $area_set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->setShape($area_rec["shape"]);
//echo $area_rec["Shape"];
		$this->setNr($area_rec["nr"]);
		$this->setCoords($area_rec["coords"]);
		$this->setLinkType($area_rec["link_type"]);
		$this->setTitle($area_rec["title"]);
		$this->setHref($area_rec["href"]);
		$this->setTarget($area_rec["target"]);
		$this->setType($area_rec["type"]);
		$this->setTargetFrame($area_rec["target_frame"]);
	}

	/**
	* update map area
	*/
	function update()
	{
		global $ilDB;

		$q = "UPDATE map_area SET shape = ".$ilDB->quote($this->getShape()).
			", coords = ".$ilDB->quote($this->getCoords()).
			", link_type = ".$ilDB->quote($this->getLinkType()).
			", title = ".$ilDB->quote($this->getTitle()).
			", href = ".$ilDB->quote($this->getHref()).
			", target = ".$ilDB->quote($this->getTarget()).
			", type = ".$ilDB->quote($this->getType()).
			", target_frame = ".$ilDB->quote($this->getTargetFrame()).
			" WHERE item_id = ".$ilDB->quote($this->getItemId())." AND nr = ".
				$ilDB->quote($this->getNr());
		$ilDB->query($q);
//echo "<br>$q<br>";
	}

	/**
	* resolve internal links of an item id
	*/
	function _resolveIntLinks($a_item_id)
	{
		global $ilDB;

//echo "maparea::resolve<br>";
		$q = "SELECT * FROM map_area WHERE item_id=".$ilDB->quote($a_item_id);
		$area_set = $ilDB->query($q);
		while ($area_rec = $area_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$target = $area_rec["target"];
			$type = $area_rec["type"];
			$item_id = $area_rec["item_id"];
			$nr = $area_rec["nr"];

			if (($area_rec["link_type"] == IL_INT_LINK) && (!is_int(strpos($target, "__"))))
			{
				$new_target = ilInternalLink::_getIdForImportId($type, $target);
				if ($new_target !== false)
				{
					$query = "UPDATE map_area SET target= ".$ilDB->quote($new_target)." ".
						"WHERE item_id= ".$ilDB->quote($item_id)." AND nr=".$ilDB->quote($nr);
					$ilDB->query($query);
				}
			}
		}
	}

	/**
	* get all internal links of a media items map areas
	*
	* @param	int		$a_item_id		media item id
	*/
	function _getIntLinks($a_item_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM map_area WHERE item_id=".$ilDB->quote($a_item_id);
		$area_set = $ilDB->query($q);

		$links = array();

		while ($area_rec = $area_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$target = $area_rec["target"];
			$type = $area_rec["type"];
			$targetframe = $area_rec["target_frame"];

			if (($area_rec["link_type"] == IL_INT_LINK) && (is_int(strpos($target, "__"))))
			{
				$links[$target.":".$type.":".$targetframe] =
					array("Target" => $target, "Type" => $type,
						"TargetFrame" => $targetframe);
			}
		}
		return $links;
	}

	/**
	* set media item id
	*
	* @param	int		$a_item_id		media item id
	*/
	function setItemId($a_item_id)
	{
		$this->item_id = $a_item_id;
	}

	/**
	* get item id
	*
	* @return	int		media item id
	*/
	function getItemId()
	{
		return $this->item_id;
	}

	/**
	* set area number
	*
	* @param	int		$a_nr		number (of area within parent media object)
	*/
	function setNr($a_nr)
	{
		$this->nr = $a_nr;
	}

	/**
	* get area number
	*
	* @return	int		number (of area within parent media object)
	*/
	function getNr()
	{
		return $this->nr;
	}

	/**
	* set shape (IL_AREA_RECT, IL_AREA_CIRCLE, IL_AREA_POLY)
	*
	* @param	string		$a_shape	shape of map area
	*/
	function setShape($a_shape)
	{
		$this->shape = $a_shape;
	}

	/**
	* get shape
	*
	* @return	string		(IL_AREA_RECT, IL_AREA_CIRCLE, IL_AREA_POLY)
	*/
	function getShape()
	{
		return $this->shape;
	}

	/**
	* set coords of area
	*
	* @param	string		$a_coords		coords (comma separated integers)
	*/
	function setCoords($a_coords)
	{
		$this->coords = $a_coords;
	}

	/**
	* get coords
	*
	* @return	string		coords (comma separated integers)
	*/
	function getCoords()
	{
		return $this->coords;
	}

	/**
	* set (tooltip)title of area
	*
	* @param	string		$a_title		title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* append string to (tooltip) title of area
	*
	* @param	string		$a_title_str		title string
	*/
	function appendTitle($a_title_str)
	{
		$this->title.= $a_title_str;
	}

	/**
	* get (tooltip) title
	*
	* @return	string		title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* set link type
	*
	* @param	string		$a_linktype		link type (IL_INT_LINK, IL_EXT_LINK)
	*/
	function setLinkType($a_link_type)
	{
		$this->linktype = $a_link_type;
	}

	/**
	* get link type
	*
	* @return	int		link type (IL_INT_LINK, IL_EXT_LINK)
	*/
	function getLinkType()
	{
		return $this->linktype;
	}

	/**
	* set hyper reference (external link only)
	*
	* @param	string	$a_href		hyper ref url
	*/
	function setHref($a_href)
	{
		$this->xl_href = $a_href;
	}

	/**
	* get hyper reference url (external link only)
	*
	* @param	string		hyper ref url
	*/
	function getHref()
	{
		return $this->xl_href;
	}

	/**
	* set link text (external link only)
	*
	* @param	string		$a_title		link text
	*/
	function setExtTitle($a_title)
	{
		$this->xl_title = $a_title;
	}

	/**
	* get link text (external link only)
	*
	* @return	string		link text
	*/
	function getExtTitle()
	{
		return $this->xl_title;
	}

	/**
	* set link target (internal link only)
	*
	* @param	string		$a_target	link target (e.g. "il__pg_23")
	*/
	function setTarget($a_target)
	{
		$this->il_target = $a_target;
	}

	/**
	* get link target (internal link only)
	*
	* @return	string		link target
	*/
	function getTarget($a_insert_inst = false)
	{
		$target = $this->il_target;

		if ((substr($target, 0, 4) == "il__") && $a_insert_inst)
		{
			$target = "il_".IL_INST_ID."_".substr($target, 4, strlen($target) - 4);
		}

		return $target;
	}

	/**
	* set link type (internal link only)
	*
	* @param	string		$a_type			link type
	*				(IL_LT_STRUCTURE | IL_LT_PAGE | IL_LT_MEDIA | IL_LT_GLITEM)
	*/
	function setType($a_type)
	{
		$this->il_type = $a_type;
	}

	/**
	* get link type (internal link only)
	*
	* @return	string	(IL_LT_STRUCTURE | IL_LT_PAGE | IL_LT_MEDIA | IL_LT_GLITEM)
	*/
	function getType()
	{
		return $this->il_type;
	}

	/**
	* set link target frame (internal link only)
	*
	* @param	string		$a_target_frame		target frame (IL_TF_MEDIA |
	*											IL_TF_FAQ | IL_TF_GLOSSARY | IL_TF_NEW)
	*/
	function setTargetFrame($a_target_frame)
	{
		$this->il_target_frame = $a_target_frame;
	}

	/**
	* get link target frame (internal link only)
	*
	* @return	string		link target frame	target frame (IL_TF_MEDIA |
	*											IL_TF_FAQ | IL_TF_GLOSSARY | IL_TF_NEW)
	*/
	function getTargetFrame()
	{
		return $this->il_target_frame;
	}

	/**
	* draw image to
	*
	* @param	boolean		$a_close_poly		close polygon
	*/
	function draw(&$a_image, $a_col1, $a_col2, $a_close_poly = true,
		$a_x_ratio = 1, $a_y_ratio = 1)
	{
		switch ($this->getShape())
		{
			case "Rect" :
				$this->drawRect($a_image, $this->getCoords(), $a_col1, $a_col2,
					$a_x_ratio, $a_y_ratio);
				break;

			case "Circle" :
				$this->drawCircle($a_image, $this->getCoords(), $a_col1, $a_col2,
					$a_x_ratio, $a_y_ratio);
				break;

			case "Poly" :
				$this->drawPoly($a_image, $this->getCoords(), $a_col1, $a_col2, $a_close_poly,
					$a_x_ratio, $a_y_ratio);
				break;
		}
	}

	/**
	* draws an outlined two color line in an image
	*
	* @param 	int		$im		image identifier as returned by ImageCreateFromGIF() etc.
	* @param	int		$x1		x-coordinate of starting point
	* @param	int		$y1		y-coordinate of starting point
	* @param	int		$x2		x-coordinate of ending point
	* @param	int		$y2		y-coordinate of ending point
	* @param	int		$c1		color identifier 1
	* @param	int		$c2		color identifier 2
	*/
	function drawLine(&$im, $x1, $y1, $x2, $y2, $c1, $c2)
	{
		imageline($im, $x1+1, $y1, $x2+1, $y2, $c1);
		imageline($im, $x1-1, $y1, $x2-1, $y2, $c1);
		imageline($im, $x1, $y1+1, $x2, $y2+1, $c1);
		imageline($im, $x1, $y1-1, $x2, $y2-1, $c1);
		imageline($im, $x1, $y1, $x2, $y2, $c2);
	}

	/**
	* draws an outlined two color rectangle
	*
	* @param	int			$im			image identifier as returned by ImageCreateFromGIF() etc.
	* @param	string		$coords     coordinate string, format : "x1,y1,x2,y2" with (x1,y1) is top left
	*									and (x2,y2) is bottom right point of the rectangle
	* @param	int			$c1			color identifier 1
	* @param	int			$c2			color identifier 2
	*/
	function drawRect(&$im,$coords,$c1,$c2,$a_x_ratio = 1, $a_y_ratio = 1)
	{
		$coord=explode(",", $coords);

		$this->drawLine($im, $coord[0] / $a_x_ratio, $coord[1] / $a_y_ratio,
			$coord[0] / $a_x_ratio, $coord[3] / $a_y_ratio, $c1, $c2);
		$this->drawLine($im, $coord[0] / $a_x_ratio, $coord[3] / $a_y_ratio,
			$coord[2] / $a_x_ratio, $coord[3] / $a_y_ratio, $c1, $c2);
		$this->drawLine($im, $coord[2] / $a_x_ratio, $coord[3] / $a_y_ratio,
			$coord[2] / $a_x_ratio, $coord[1] / $a_y_ratio, $c1, $c2);
		$this->drawLine($im, $coord[2] / $a_x_ratio, $coord[1] / $a_y_ratio,
			$coord[0] / $a_x_ratio, $coord[1] / $a_y_ratio, $c1, $c2);
	}


	/**
	* draws an outlined two color polygon
	*
	* @param	int			$im			image identifier as returned by ImageCreateFromGIF() etc.
	* @param	string		$coords     coordinate string, format : "x1,y1,x2,y2,..." with every (x,y) pair is
	*									an ending point of a line of the polygon
	* @param	int			$c1			color identifier 1
	* @param	int			$c3			color identifier 2
	* @param	boolean		$closed		true: the first and the last point will be connected with a line
	*/
	function drawPoly(&$im, $coords, $c1, $c2, $closed,$a_x_ratio = 1, $a_y_ratio = 1)
	{
		if ($closed)
		{
			$p = 0;
		}
		else
		{
			$p = 1;
		}

		$anz = ilMapArea::countCoords($coords);

		if ($anz < (3 - $p))
		{
			return;
		}

		$c = explode(",", $coords);

		for($i=0; $i<$anz-$p; $i++)
		{
			$this->drawLine($im, $c[$i*2] / $a_x_ratio, $c[$i*2+1] / $a_y_ratio,
				$c[($i*2+2)%(2*$anz)] / $a_x_ratio,
				$c[($i*2+3)%(2*$anz)] / $a_y_ratio, $c1, $c2);
		}
	}


	/**
	* draws an outlined two colored circle
	*
	* @param	int			$im			image identifier as returned by ImageCreateFromGIF()
	* @param	string		$coords     coordinate string, format : "x,y,r" with (x,y) as center point
	*									and r as radius
	* @param	int			$c1			color identifier 1
	* @param	int			$c3			color identifier 2
	*/
	function drawCircle(&$im, $coords, $c1, $c2,$a_x_ratio = 1, $a_y_ratio = 1)
	{
		$c = explode(",", $coords);
		imagearc($im, $c[0] / $a_x_ratio, $c[1] / $a_y_ratio,
			($c[2]+1)*2 / $a_x_ratio, ($c[2]+1)*2 / $a_y_ratio, 1, 360, $c1);
		imagearc($im, $c[0] / $a_x_ratio, $c[1] / $a_y_ratio,
			($c[2]-1)*2 / $a_x_ratio, ($c[2]-1)*2 / $a_y_ratio, 1, 360, $c1);
		imagearc($im, $c[0] / $a_x_ratio, $c[1] / $a_y_ratio,
			$c[2]*2 / $a_x_ratio, $c[2]*2 / $a_y_ratio, 1, 360, $c2);
	}

	/**
	* count the number of coordinates (x,y) in a coordinate string (format: "x1,y1,x2,y2,x3,y3,...")
	*
	* @param	string		$c		coordinate string
	*/
	function countCoords($c)
	{
		if ($c == "")
		{
			return 0;
		}
		else
		{
			$coord_array = explode(",", $c);
			return (count($coord_array) / 2);
		}
	}

}
?>
