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
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaAliasItem[@Purpose='$a_purpose']";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) > 0)
		{
			$this->item_node =& $res->nodeset[0];
		}
	}

	function setWidth($a_width)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom, $this->item_node, "Layout",
			array("Caption", "Parameter"),
			"", array("Width" => $a_width), false);
	}


	function getWidth()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaAliasItem[@Purpose='".$this->purpose."']/Layout";
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
			array("Caption", "Parameter"),
			"", array("Height" => $a_height), false);
	}


	function getHeight()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaAliasItem[@Purpose='".$this->purpose."']/Layout";
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
			array("Parameter"),
			$a_caption, array("Align" => "bottom"));
	}


	function getCaption()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaAliasItem[@Purpose='".$this->purpose."']/Caption";
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
			array("Caption", "Parameter"),
			"", array("HorizontalAlign" => $a_halign), false);
	}


	function getHorizontalAlign()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaAliasItem[@Purpose='".$this->purpose."']/Layout";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$layout_node =& $res->nodeset[0];
			return $layout_node->get_attribute("HorizontalAlign");
		}
	}


	/**
	* set parameter
	*
	* note: parameter tags are simply appended to the item node, so if
	* current element definition (MediaAliasItem (Layout?, Caption?, Parameter*)>)
	* changes, adoptions may be necessary
	*/
	function setParameters($a_par_array)
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaAliasItem[@Purpose='".$this->purpose."']/Parameter";
		$res =& xpath_eval($xpc, $path);
		$par_arr = array();
		for($i=0; $i < count($res->nodeset); $i++)
		{
			$par_node =& $res->nodeset[$i];
			$par_node->unlink_node($par_node);
		}

		if (is_array($a_par_array))
		{
			foreach($a_par_array as $par => $val)
			{
				$par_node =& $this->dom->create_element("Parameter");
				$par_node =& $this->item_node->append_child($par_node);
				$par_node->set_attribute("Name", $par);
				$par_node->set_attribute("Value", $val);
			}
		}
	}


	/**
	* get all parameters
	*/
	function getParameterString()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId = '".$this->hier_id."']/MediaObject/MediaAliasItem[@Purpose='".$this->purpose."']/Parameter";
		$res =& xpath_eval($xpc, $path);
		$par_arr = array();
		for($i=0; $i < count($res->nodeset); $i++)
		{
			$par_node =& $res->nodeset[$i];
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

}
?>
