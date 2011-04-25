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
	var $iim_node;
	
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
	function setNode($a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->iim_node = $a_node->first_child();
		$this->med_alias_node = $this->iim_node->first_child();
		if (is_object($this->med_alias_node) && $this->med_alias_node->myDOMNode != null)
		{
			$id = $this->med_alias_node->get_attribute("OriginId");
			$mob_id = ilInternalLink::_extractObjIdOfTarget($id);
			if (ilObject::_lookupType($mob_id) == "mob")
			{
				$this->setMediaObject(new ilObjMediaObject($mob_id));
			}
		}
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
		$this->iim_node =& $this->dom->create_element("InteractiveImage");
		$this->iim_node =& $this->node->append_child($this->iim_node);
		$this->mal_node =& $this->dom->create_element("MediaAlias");
		$this->mal_node =& $this->iim_node->append_child($this->mal_node);
		$this->mal_node->set_attribute("OriginId", "il__mob_".$this->getMediaObject()->getId());

		// standard view
		$item_node =& $this->dom->create_element("MediaAliasItem");
		$item_node =& $this->iim_node->append_child($item_node);
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
		if (is_object($this->iim_node))
		{
			$mal_node = $this->iim_node->first_child();
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
		if (is_object($this->iim_node))
		{
			$mal_node = $this->iim_node->first_child();
			if (is_object($mal_node))
			{
				$class =  $mal_node->get_attribute("Class");
				return $class;
			}
		}
	}

	
	////
	//// Content popups
	////


	/**
	 * Add a tab
	 */
	function addContentPopup()
	{
		global $lng;
		
		$new_item = $this->dom->create_element("ContentPopup");
		$new_item->set_attribute("Title", $lng->txt("cont_new_popup"));
		$new_item = $this->iim_node->append_child($new_item);
	}
	
	/**
	 * Get popup captions
	 */
	function getPopups()
	{
		$captions = array();
		$childs = $this->iim_node->child_nodes();
		$k = 0;
		for($i = 0; $i < count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "ContentPopup")
			{
				$pc_id = $childs[$i]->get_attribute("PCID");
				$hier_id = $childs[$i]->get_attribute("HierId");
				$title = $childs[$i]->get_attribute("Title");
				
				$titles[] = array("title" => $title,
					"pc_id" => $pc_id, "hier_id" => $hier_id);
				$k++;
			}
		}
		return $titles;
	}

	/**
	 * Save popups
	 */
	function savePopups($a_popups)
	{
		$childs = $this->iim_node->child_nodes();
		for($i = 0; $i < count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "ContentPopup")
			{
				$pc_id = $childs[$i]->get_attribute("PCID");
				$hier_id = $childs[$i]->get_attribute("HierId");
				$k = $hier_id.":".$pc_id;
				$childs[$i]->set_attribute("Title", $a_popups[$k]);
			}
		}
	}

	/**
	 * Get caption
	 */
/*
	function getCaption($a_hier_id, $a_pc_id)
	{
		$captions = array();
		$tab_nodes = $this->tabs_node->child_nodes();
		$k = 0;
		for($i = 0; $i < count($tab_nodes); $i++)
		{
			if ($tab_nodes[$i]->node_name() == "Tab")
			{
				if ($a_pc_id == $tab_nodes[$i]->get_attribute("PCID") &&
					($a_hier_id == $tab_nodes[$i]->get_attribute("HierId")))
				{
					$tab_node_childs = $tab_nodes[$i]->child_nodes();
					for($j = 0; $j < count($tab_node_childs); $j++)
					{
						if ($tab_node_childs[$j]->node_name() == "TabCaption")
						{
							return $tab_node_childs[$j]->get_content();
						}
					}
				}
			}
		}
		
		return "";
	}
*/

	/**
	 * Save positions of tabs
	 */
/*
	function savePositions($a_pos)
	{
		asort($a_pos);
		
		// File Item
		$childs = $this->tabs_node->child_nodes();
		$nodes = array();
		for ($i=0; $i<count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "Tab")
			{
				$pc_id = $childs[$i]->get_attribute("PCID");
				$hier_id = $childs[$i]->get_attribute("HierId");
				$nodes[$hier_id.":".$pc_id] = $childs[$i];
				$childs[$i]->unlink($childs[$i]);
			}
		}
		
		foreach($a_pos as $k => $v)
		{
			if (is_object($nodes[$k]))
			{
				$nodes[$k] = $this->tabs_node->append_child($nodes[$k]);
			}
		}
	}
*/

	/**
	* Save positions of tabs
	*/
/*
	function deleteTab($a_hier_id, $a_pc_id)
	{
		// File Item
		$childs = $this->tabs_node->child_nodes();
		$nodes = array();
		for ($i=0; $i<count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "Tab")
			{
				if ($a_pc_id == $childs[$i]->get_attribute("PCID") &&
					$a_hier_id == $childs[$i]->get_attribute("HierId"))
				{
					$childs[$i]->unlink($childs[$i]);
				}
			}
		}
	}
*/

}
?>
