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
* Class ilPCMediaObject
*
* Media content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCMediaObject extends ilPageContent
{
	var $dom;
	var $mob_node;
	
	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("media");
	}

	/**
	* Read/get Media Object
	*
	* @param	int		media object ID
	*/
	function readMediaObject($a_mob_id = 0)
	{
		if ($a_mob_id > 0)
		{
			$mob = new ilObjMediaObject($a_mob_id);
			$this->setMediaObject($mob);
		}
	}
	
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->mob_node =& $a_node->first_child();
	}

	/**
	* set dom object
	*/
	function setDom(&$a_dom)
	{
		$this->dom =& $a_dom;
	}

	/**
	* set hierarchical edit id
	*/
	function setHierId($a_hier_id)
	{
		$this->hier_id = $a_hier_id;
	}

	/**
	* Set Media Object.
	*
	* @param	object	$a_mediaobject	Media Object
	*/
	function setMediaObject($a_mediaobject)
	{
		$this->mediaobject = $a_mediaobject;
	}

	/**
	* Get Media Object.
	*
	* @return	object	Media Object
	*/
	function getMediaObject()
	{
		return $this->mediaobject;
	}
	
	function createMediaObject()
	{
		$this->setMediaObject(new ilObjMediaObject());
	}

	function create(&$a_pg_obj, $a_hier_id)
	{
//echo "::".is_object($this->dom).":";
		$this->node = $this->createPageContentNode();
		
		//$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		//$this->list_node =& $this->dom->create_element("List");
		//$this->list_node =& $this->node->append_child($this->list_node);
	}
	
	/**
	* Create an media alias in page
	*
	* @param	object	$a_pg_obj		page object
	* @param	string	$a_hier_id		hierarchical ID
	*/
	function createAlias(&$a_pg_obj, $a_hier_id)
	{
		$this->node =& $this->dom->create_element("PageContent");
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->mob_node =& $this->dom->create_element("MediaObject");
		$this->mob_node =& $this->node->append_child($this->mob_node);
		$this->mal_node =& $this->dom->create_element("MediaAlias");
		$this->mal_node =& $this->mob_node->append_child($this->mal_node);
		$this->mal_node->set_attribute("OriginId", "il__mob_".$this->getMediaObject()->getId());

		// standard view
		$item_node =& $this->dom->create_element("MediaAliasItem");
		$item_node =& $this->mob_node->append_child($item_node);
		$item_node->set_attribute("Purpose", "Standard");
		$media_item =& $this->getMediaObject()->getMediaItem("Standard");

		$layout_node =& $this->dom->create_element("Layout");
		$layout_node =& $item_node->append_child($layout_node);
		if ($media_item->getWidth() > 0)
		{
			$layout_node->set_attribute("Width", $media_item->getWidth());
		}
		if ($media_item->getHeight() > 0)
		{
			$layout_node->set_attribute("Height", $media_item->getHeight());
		}
		$layout_node->set_attribute("HorizontalAlign", "Left");

		// caption
		if ($media_item->getCaption() != "")
		{
			$cap_node =& $this->dom->create_element("Caption");
			$cap_node =& $item_node->append_child($cap_node);
			$cap_node->set_attribute("Align", "bottom");
			$cap_node->set_content($media_item->getCaption());
		}

		$pars = $media_item->getParameters();
		foreach($pars as $par => $val)
		{
			$par_node =& $this->dom->create_element("Parameter");
			$par_node =& $item_node->append_child($par_node);
			$par_node->set_attribute("Name", $par);
			$par_node->set_attribute("Value", $val);
		}

		// fullscreen view
		$fullscreen_item =& $this->getMediaObject()->getMediaItem("Fullscreen");
		if (is_object($fullscreen_item))
		{
			$item_node =& $this->dom->create_element("MediaAliasItem");
			$item_node =& $this->mob_node->append_child($item_node);
			$item_node->set_attribute("Purpose", "Fullscreen");

			// width and height
			$layout_node =& $this->dom->create_element("Layout");
			$layout_node =& $item_node->append_child($layout_node);
			if ($fullscreen_item->getWidth() > 0)
			{
				$layout_node->set_attribute("Width", $fullscreen_item->getWidth());
			}
			if ($fullscreen_item->getHeight() > 0)
			{
				$layout_node->set_attribute("Height", $fullscreen_item->getHeight());
			}

			// caption
			if ($fullscreen_item->getCaption() != "")
			{
				$cap_node =& $this->dom->create_element("Caption");
				$cap_node =& $item_node->append_child($cap_node);
				$cap_node->set_attribute("Align", "bottom");
				$cap_node->set_content($fullscreen_item->getCaption());
			}

			$pars = $fullscreen_item->getParameters();
			foreach($pars as $par => $val)
			{
				$par_node =& $this->dom->create_element("Parameter");
				$par_node =& $item_node->append_child($par_node);
				$par_node->set_attribute("Name", $par);
				$par_node->set_attribute("Value", $val);
			}
		}
	}
	
	/**
	* Dump node xml
	*/
	function dumpXML()
	{
		$xml = $this->dom->dump_node($this->node);
		return $xml;
	}
}
?>
