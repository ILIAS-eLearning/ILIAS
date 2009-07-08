<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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

	/**
	* Create pc media object
	*/
	function create(&$a_pg_obj, $a_hier_id)
	{
		$this->node = $this->createPageContentNode();
	}
	
	/**
	* Create an media alias in page
	*
	* @param	object	$a_pg_obj		page object
	* @param	string	$a_hier_id		hierarchical ID
	w*/
	function createAlias(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node =& $this->dom->create_element("PageContent");
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
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
			//$layout_node->set_attribute("Width", $media_item->getWidth());
		}
		if ($media_item->getHeight() > 0)
		{
			//$layout_node->set_attribute("Height", $media_item->getHeight());
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

		// text representation
		if ($media_item->getTextRepresentation() != "")
		{
			$tr_node =& $this->dom->create_element("TextRepresentation");
			$tr_node =& $item_node->append_child($tr_node);
			$tr_node->set_content($media_item->getTextRepresentation());
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

			// text representation
			if ($fullscreen_item->getTextRepresentation() != "")
			{
				$tr_node =& $this->dom->create_element("TextRepresentation");
				$tr_node =& $item_node->append_child($tr_node);
				$tr_node->set_content($fullscreen_item->getTextRepresentation());
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
	
	/**
	* Set Style Class of table
	*
	* @param	string	$a_class		class
	*/
	function setClass($a_class)
	{
		if (is_object($this->mob_node))
		{
			$mal_node = $this->mob_node->first_child();
			if (is_object($mal_node))
			{
				if (!empty($a_class))
				{
					$mal_node->set_attribute("Class", $a_class);
				}
				else
				{
					if ($mal_node->has_attribute("Class"))
					{
						$mal_node->remove_attribute("Class");
					}
				}
			}
		}
	}

	/**
	* Get characteristic of section.
	*
	* @return	string		characteristic
	*/
	function getClass()
	{
		if (is_object($this->mob_node))
		{
			$mal_node = $this->mob_node->first_child();
			if (is_object($mal_node))
			{
				$class =  $mal_node->get_attribute("Class");
				return $class;
			}
		}
	}

}
?>
