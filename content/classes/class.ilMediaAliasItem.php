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


/**
* Class ilMediaItem
*
* Media Item, component of a media object (file or reference)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilMediaAliasItem
{
	var $dom;
	var $hier_id;
	var $purpose;
	var $item_node;

	function ilMediaAliasItem(&$a_dom, $a_hier_id, $a_purpose)
	{
		$this->dom =& $a_dom;
		$this->hier_id = $a_hier_id;
		$this->purpose = $a_purpose;

		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaItem[@Purpose='$a_purpose']";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) > 0)
		{
			$this->item_node =& $res->nodeset[0];
		}
	}

	function setWidth($a_width)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "Layout",
			array("Caption", "Parameter", "MapArea"),
			"", array("Width" => $a_width), false);
	}


	function getWidth()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaItem[@Purpose='".$this->purpose."']/Layout";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$layout_node =& $res->nodeset[0];
			return $layout_node->get_attribute("Width");
		}
	}

	function setHeight($a_height)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "Layout",
			array("Caption", "Parameter", "MapArea"),
			"", array("Height" => $a_height), false);
	}


	function getHeight()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaItem[@Purpose='".$this->purpose."']/Layout";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$layout_node =& $res->nodeset[0];
			return $layout_node->get_attribute("Height");
		}
	}


	function setCaption($a_caption)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "Caption",
			array("Parameter", "MapArea"),
			$a_caption, array("Align" => "bottom"));
	}


	function getCaption()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaItem[@Purpose='".$this->purpose."']/Caption";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$caption_node =& $res->nodeset[0];
			return $caption_node->get_content();
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
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaItem[@Purpose='".$this->purpose."']/Layout";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$layout_node =& $res->nodeset[0];
			return $layout_node->get_attribute("HorizontalAlign");
		}
	}


	/**
	* set parameter
	*/
	function setParameter($a_name, $a_value)
	{
	}

	/**
	* get all parameters
	*/
	function getParameters()
	{
	}

	/**
	* get a single parameter
	*/
	function getParameter($a_name)
	{
	}

}
?>
