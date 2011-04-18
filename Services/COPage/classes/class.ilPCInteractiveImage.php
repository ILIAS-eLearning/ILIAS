<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
 * Interactive image.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCInteractiveImage extends ilPageContent
{
	var $dom;
	var $mob_node;
	
	/**
	 * Init page content component.
	 */
	function init()
	{
		$this->setType("iim");
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
	
	/**
	 * Set node (and media object node)
	 */
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->mob_node =& $a_node->first_child();
	}

	/**
	 * Set dom object
	 */
	function setDom(&$a_dom)
	{
		$this->dom =& $a_dom;
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
	
	/**
 	 * Create new media object
	 */
	function createMediaObject()
	{
		$this->setMediaObject(new ilObjMediaObject());
	}

	/**
	 * Create pc media object
	 */
	function create($a_pg_obj, $a_hier_id)
	{
		$this->node = $this->createPageContentNode();
	}
	
	/**
	 * Create an media alias in page
	 *
	 * @param	object	$a_pg_obj		page object
	 * @param	string	$a_hier_id		hierarchical ID
	 */
	function createAlias(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node =& $this->dom->create_element("PageContent");
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->mob_node =& $this->dom->create_element("InteractiveImage");
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
	 * Set style class
	 *
	 * @param string $a_class style class
	 */
	function setStyleClass($a_class)
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
	 * Get style class
	 *
	 * @return string style class
	 */
	function getStyleClass()
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
