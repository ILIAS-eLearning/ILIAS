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
	* Updates the media object referenced by the media alias.
	* This makes only sense, after the media object has changed.
	* (-> change object reference function)
	*/
	function updateObjectReference()
	{
		if (is_object($this->mob_node))
		{
			$this->mal_node =& $this->mob_node->first_child();
			if (is_object($this->mal_node) && $this->mal_node->node_name() == "MediaAlias")
			{
				$this->mal_node->set_attribute("OriginId", "il__mob_".$this->getMediaObject()->getId());
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
	
	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("pc_mob");
	}

	/**
	 * After page has been updated (or created)
	 *
	 * @param object $a_page page object
	 * @param DOMDocument $a_domdoc dom document
	 * @param string $a_xml xml
	 * @param bool $a_creation true on creation, otherwise false
	 */
	static function afterPageUpdate($a_page, DOMDocument $a_domdoc, $a_xml, $a_creation)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mob_ids = ilObjMediaObject::_getMobsOfObject(
			$a_page->getParentType().":pg", $a_page->getId(), 0, $a_page->getLanguage());
		self::saveMobUsage($a_page, $a_domdoc);
		foreach($mob_ids as $mob)	// check, whether media object can be deleted
		{
			if (ilObject::_exists($mob) && ilObject::_lookupType($mob) == "mob")
			{
				$mob_obj = new ilObjMediaObject($mob);
				$usages = $mob_obj->getUsages(false);
				if (count($usages) == 0)	// delete, if no usage exists
				{
					$mob_obj->delete();
				}
			}
		}
	}
	
	/**
	 * Before page is being deleted
	 *
	 * @param object $a_page page object
	 */
	static function beforePageDelete($a_page)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mob_ids = ilObjMediaObject::_getMobsOfObject(
			$a_page->getParentType().":pg", $a_page->getId(), 0, $a_page->getLanguage());

		ilObjMediaObject::_deleteAllUsages($a_page->getParentType().":pg", $a_page->getId(), false,
			$a_page->getLanguage());

		foreach($mob_ids as $mob)	// check, whether media object can be deleted
		{
			if (ilObject::_exists($mob) && ilObject::_lookupType($mob) == "mob")
			{
				$mob_obj = new ilObjMediaObject($mob);
				$usages = $mob_obj->getUsages(false);
				if (count($usages) == 0)	// delete, if no usage exists
				{
					$mob_obj->delete();
				}
			}
		}
	}

	/**
	 * After page history entry has been created
	 *
	 * @param object $a_page page object
	 * @param DOMDocument $a_old_domdoc old dom document
	 * @param string $a_old_xml old xml
	 * @param integer $a_old_nr history number
	 */
	static function afterPageHistoryEntry($a_page, DOMDocument $a_old_domdoc, $a_old_xml, $a_old_nr)
	{
		self::saveMobUsage($a_page, $a_old_domdoc, $a_old_nr);
	}

	/**
	 * Save all usages of media objects (media aliases, media objects, internal links)
	 *
	 * @param	string		$a_xml		xml data of page
	 */
	static function saveMobUsage($a_page, $a_domdoc, $a_old_nr = 0)
	{
		$usages = array();
		
		// media aliases
		$xpath = new DOMXPath($a_domdoc);
		$nodes = $xpath->query('//MediaAlias');	
		foreach($nodes as $node)
		{
			$id_arr = explode("_", $node->getAttribute("OriginId"));
			$mob_id = $id_arr[count($id_arr) - 1];
			if ($mob_id > 0 && $id_arr[1] == "")
			{
				$usages[$mob_id] = true;
			}
		}

		// media objects
		$xpath = new DOMXPath($a_domdoc);
		$nodes = $xpath->query('//MediaObject/MetaData/General/Identifier');	
		foreach($nodes as $node)
		{
			$mob_entry = $node->getAttribute("Entry");
			$mob_arr = explode("_", $mob_entry);
			$mob_id = $mob_arr[count($mob_arr) - 1];
			if ($mob_id > 0 && $mob_arr[1] == "")
			{
				$usages[$mob_id] = true;
			}
		}

		// internal links
		$xpath = new DOMXPath($a_domdoc);
		$nodes = $xpath->query("//IntLink[@Type='MediaObject']");	
		foreach($nodes as $node)
		{
			$mob_target = $node->getAttribute("Target");
			$mob_arr = explode("_", $mob_target);
//echo "<br>3<br>";
//echo $mob_target."<br>";
//var_dump($mob_arr);
			$mob_id = $mob_arr[count($mob_arr) - 1];
			if ($mob_id > 0 && $mob_arr[1] == "")
			{
				$usages[$mob_id] = true;
			}
		}

		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		ilObjMediaObject::_deleteAllUsages($a_page->getParentType().":pg", $a_page->getId(), $a_old_nr,
			$a_page->getLanguage());
		foreach($usages as $mob_id => $val)
		{
			ilObjMediaObject::_saveUsage($mob_id, $a_page->getParentType().":pg", $a_page->getId(), $a_old_nr,
				$a_page->getLanguage());
		}
		
		return $usages;
	}

}
?>
