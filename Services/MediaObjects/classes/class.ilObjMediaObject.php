<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define ("IL_MODE_ALIAS", 1);
define ("IL_MODE_OUTPUT", 2);
define ("IL_MODE_FULL", 3);

require_once("./Services/MediaObjects/classes/class.ilMediaItem.php");
include_once "classes/class.ilObject.php";

/** @defgroup ServicesMediaObjects Services/MediaObjects
 */

/**
* Class ilObjMediaObject
*
* Todo: this class must be integrated with group/folder handling
*
* ILIAS Media Object
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
*/
class ilObjMediaObject extends ilObject
{
	var $is_alias;
	var $origin_id;
	var $id;
	var $media_items;
	var $contains_int_link;

	/**
	* Constructor
	* @access	public
	*/
	function ilObjMediaObject($a_id = 0)
	{
		$this->is_alias = false;
		$this->media_items = array();
		$this->contains_int_link = false;
		$this->type = "mob";
		parent::ilObject($a_id, false);
	}

	function setRefId()
	{
		$this->ilias->raiseError("Operation ilObjMedia::setRefId() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function getRefId()
	{
		return false;
	}

	function putInTree()
	{
		$this->ilias->raiseError("Operation ilObjMedia::putInTree() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function createReference()
	{
		$this->ilias->raiseError("Operation ilObjMedia::createReference() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function setTitle($a_title)
	{
		parent::setTitle($a_title);
	}

	function getTitle()
	{
		return parent::getTitle();
	}

	/**
	* checks wether a lm content object with specified id exists or not
	*
	* @param	int		$id		id
	*
	* @return	boolean		true, if lm content object exists
	*/
	function _exists($a_id)
	{
		global $ilDB;
		
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		if (is_int(strpos($a_id, "_")))
		{
			$a_id = ilInternalLink::_extractObjIdOfTarget($a_id);
		}
		
		return parent::_exists($a_id, false);
	}

	/**
	* delete media object
	*/
	function delete()
	{
		if (!($this->getId() > 0))
		{
			return;
		}

		$usages = $this->getUsages();

		if (count($usages) == 0)
		{
			// remove directory
			ilUtil::delDir(ilObjMediaObject::_getDirectory($this->getId()));

			// remove thumbnail directory
			ilUtil::delDir(ilObjMediaObject::_getThumbnailDirectory($this->getId()));

			// delete meta data of mob
			$this->deleteMetaData();

			// delete media items
			ilMediaItem::deleteAllItemsOfMob($this->getId());

			// delete object
			parent::delete();
		}
	}

	/**
	* get description of media object
	*
	* @return	string		description
	*/
	function getDescription()
	{
		return parent::getDescription();
	}

	/**
	* set description of media object
	*/
	function setDescription($a_description)
	{
		parent::setDescription($a_description);
	}

	/**
	* Meta data update listener
	*
	* Important note: Do never call create() or update()
	* method of ilObject here. It would result in an
	* endless loop: update object -> update meta -> update
	* object -> ...
	* Use static _writeTitle() ... methods instead.
	*
	* @param	string		$a_element
	*/
	function MDUpdateListener($a_element)
	{
		include_once 'Services/MetaData/classes/class.ilMD.php';

		switch($a_element)
		{
			case 'General':

				// Update Title and description
				$md = new ilMD(0, $this->getId(), $this->getType());
				$md_gen = $md->getGeneral();

				if (is_object($md_gen))
				{
					ilObject::_writeTitle($this->getId(),$md_gen->getTitle());
					$this->setTitle($md_gen->getTitle());
	
					foreach($md_gen->getDescriptionIds() as $id)
					{
						$md_des = $md_gen->getDescription($id);
						ilObject::_writeDescription($this->getId(),$md_des->getDescription());
						$this->setDescription($md_des->getDescription());
						break;
					}
				}

				break;

			default:
		}
		return true;
	}

	/**
	* create meta data entry
	*/
	function createMetaData()
	{
		include_once 'Services/MetaData/classes/class.ilMDCreator.php';

		global $ilUser;

		$md_creator = new ilMDCreator(0, $this->getId(), $this->getType());
		$md_creator->setTitle($this->getTitle());
		$md_creator->setTitleLanguage($ilUser->getPref('language'));
		$md_creator->setDescription($this->getDescription());
		$md_creator->setDescriptionLanguage($ilUser->getPref('language'));
		$md_creator->setKeywordLanguage($ilUser->getPref('language'));
		$md_creator->setLanguage($ilUser->getPref('language'));
		$md_creator->create();

		return true;
	}

	/**
	* update meta data entry
	*/
	function updateMetaData()
	{
		include_once("Services/MetaData/classes/class.ilMD.php");
		include_once("Services/MetaData/classes/class.ilMDGeneral.php");
		include_once("Services/MetaData/classes/class.ilMDDescription.php");

		$md =& new ilMD(0, $this->getId(), $this->getType());
		$md_gen =& $md->getGeneral();
		$md_gen->setTitle($this->getTitle());

		// sets first description (maybe not appropriate)
		$md_des_ids =& $md_gen->getDescriptionIds();
		if (count($md_des_ids) > 0)
		{
			$md_des =& $md_gen->getDescription($md_des_ids[0]);
			$md_des->setDescription($this->getDescription());
			$md_des->update();
		}
		$md_gen->update();

	}

	/**
	* delete meta data entry
	*/
	function deleteMetaData()
	{
		// Delete meta data
		include_once('Services/MetaData/classes/class.ilMD.php');
		$md = new ilMD(0, $this->getId(), $this->getType());
		$md->deleteAll();
	}


	/**
	* add media item to media object
	*
	* @param	object		$a_item		media item object
	*/
	function addMediaItem(&$a_item)
	{
		$this->media_items[] =& $a_item;
	}


	/**
	* get all media items
	*
	* @return	array		array of media item objects
	*/
	function &getMediaItems()
	{
		return $this->media_items;
	}

	/**
	 * get item for media purpose
	 *
	 * @param string $a_purpose
	 * @return ilMediaItem
	 */
	function &getMediaItem($a_purpose)
	{
	    foreach ($this->media_items as $media_item)
		{
		    if($media_item->getPurpose() == $a_purpose)
			{
				return $media_item;
			}
		}
		return false;
	}


	/**
	*
	*/
	function removeMediaItem($a_purpose)
	{
	    foreach ($this->media_items as $key => $media_item)
	    {
			if($media_item->getPurpose() == $a_purpose)
			{
				unset($this->media_items[$key]);
			}
		}
		// update numbers and keys
		$i = 1;
		$media_items = array();
		foreach ($this->media_items as $media_item)
	    {
			$media_items [$i] = $media_item;
	        $media_item->setMobId($this->getId());
			$media_item->setNr($i);
			$i++;			
		}
		$this->media_items = $media_items;
	}
	
	/**
	* remove all media items
	*/
	function removeAllMediaItems()
	{
		$this->media_items = array();
	}


	function getMediaItemNr($a_purpose)
	{
		for($i=0; $i<count($this->media_items); $i++)
		{
			if($this->media_items[$i]->getPurpose() == $a_purpose)
			{
				return $i + 1;
			}
		}
		return false;
	}

	
	function hasFullscreenItem()
	{
		return $this->hasPurposeItem("Fullscreen");
	}
	
	/**
	 * returns wether object has media item with specific purpose
	 *
	 * @param string $purpose
	 * @return boolean
	 */
	function hasPurposeItem($purpose)
	{
		if(is_object($this->getMediaItem($purpose)))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	

	/**
	* read media object data from db
	*/
	function read()
	{
//echo "<br>ilObjMediaObject:read";
		parent::read();

		// get media items
		ilMediaItem::_getMediaItemsOfMOb($this);
	}

	/**
	* set id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getId()
	{
		return $this->id;
	}

	/**
	* set wether page object is an alias
	*/
	function setAlias($a_is_alias)
	{
		$this->is_alias = $a_is_alias;
	}

	function isAlias()
	{
		return $this->is_alias;
	}

	function setOriginID($a_id)
	{
		return $this->origin_id = $a_id;
	}

	function getOriginID()
	{
		return $this->origin_id;
	}

	/*
	function getimportId()
	{
		return $this->meta_data->getImportIdentifierEntryID();
	}*/


	/**
	* get import id
	*/
	function getImportId()
	{
		return $this->import_id;
	}

	function setImportId($a_id)
	{
		$this->import_id = $a_id;
	}

	/**
	* create media object in db
	*/
	function create($a_upload = false, $a_save_media_items = true)
	{
		parent::create();

		if (!$a_upload)
		{
			$this->createMetaData();
		}

		if ($a_save_media_items)
		{
			$media_items =& $this->getMediaItems();
			for($i=0; $i<count($media_items); $i++)
			{
				$item =& $media_items[$i];
				$item->setMobId($this->getId());
				$item->setNr($i+1);
				$item->create();
			}
		}

	}


	/**
	* update media object in db
	*/
	function update()
	{
		$this->updateMetaData();
		parent::update();

		ilMediaItem::deleteAllItemsOfMob($this->getId());

		// iterate all items
		$media_items =& $this->getMediaItems();
		$j = 1;
		foreach($media_items as $key => $val)
		{
		    $item =& $media_items[$key];
			if (is_object($item))
			{
				$item->setMobId($this->getId());
				$item->setNr($j);
				if ($item->getLocationType() == "Reference")
				{
					$item->extractUrlParameters();
				}
				$item->create();
				$j++;
			}
		}
	}

	/**
	* get directory for files of media object (static)
	*
	* @param	int		$a_mob_id		media object id
	*/
	function _getDirectory($a_mob_id)
	{
		return ilUtil::getWebspaceDir()."/mobs/mm_".$a_mob_id;
	}
	
/**
	* get directory for files of media object (static)
	*
	* @param	int		$a_mob_id		media object id
	*/
	function _getURL($a_mob_id)
	{
		return ilUtil::getHtmlPath(ilUtil::getWebspaceDir()."/mobs/mm_".$a_mob_id);
	}

	/**
	* get directory for files of media object (static)
	*
	* @param	int		$a_mob_id		media object id
	*/
	function _getThumbnailDirectory($a_mob_id, $a_mode = "filesystem")
	{
		return ilUtil::getWebspaceDir($a_mode)."/thumbs/mm_".$a_mob_id;
	}
	
	/**
	* Get path for standard item.
	*
	* @param	int		$a_mob_id		media object id
	*/
	static function _lookupStandardItemPath($a_mob_id, $a_url_encode = false,
		$a_web = true)
	{
		return ilObjMediaObject::_lookupItemPath($a_mob_id, $a_url_encode, $a_web, "Standard");
	}
	
	/**
	* Get path for item with specific purpose.
	*
	* @param	int		$a_mob_id		media object id
	*/
	static function _lookupItemPath($a_mob_id, $a_url_encode = false,
		$a_web = true, $a_purpose = "")
	{
		if ($a_purpose == "")
		{
			$a_purpose = "Standard";
		}
		$location = ilMediaItem::_lookupLocationForMobId($a_mob_id, $a_purpose);
		if (preg_match("/https?\:/i",$location))
		    return $location;
		    
		if ($a_url_encode)
		    $location = rawurlencode($location);

		$path = ($a_web)
			? ILIAS_HTTP_PATH
			: ".";
			
		return $path."/data/".CLIENT_ID."/mobs/mm_".$a_mob_id."/".$location;
	}

	/**
	* create file directory of media object
	*/
	function createDirectory()
	{
		ilUtil::createDirectory(ilObjMediaObject::_getDirectory($this->getId()));
	}

	/**
	* create thumbnail directory
	*/
	function _createThumbnailDirectory($a_obj_id)
	{
		ilUtil::createDirectory(ilUtil::getWebspaceDir()."/thumbs");
		ilUtil::createDirectory(ilUtil::getWebspaceDir()."/thumbs/mm_".$a_obj_id);
	}

	/**
	* get MediaObject XLM Tag
	*  @param	int		$a_mode		IL_MODE_ALIAS | IL_MODE_OUTPUT | IL_MODE_FULL
	*/
	function getXML($a_mode = IL_MODE_FULL, $a_inst = 0)
	{
		// TODO: full implementation of all parameters
//echo "-".$a_mode."-";
		switch ($a_mode)
		{
			case IL_MODE_ALIAS:
				$xml = "<MediaObject>";
				$xml .= "<MediaAlias OriginId=\"il__mob_".$this->getId()."\"/>";
				$media_items =& $this->getMediaItems();
				for($i=0; $i<count($media_items); $i++)
				{
					$item =& $media_items[$i];
					$xml .= "<MediaAliasItem Purpose=\"".$item->getPurpose()."\">";

					// Layout
					$width = ($item->getWidth() != "")
						? "Width=\"".$item->getWidth()."\""
						: "";
					$height = ($item->getHeight() != "")
						? "Height=\"".$item->getHeight()."\""
						: "";
					$halign = ($item->getHAlign() != "")
						? "HorizontalAlign=\"".$item->getHAlign()."\""
						: "";
					$xml .= "<Layout $width $height $halign />";

					// Caption
					if ($item->getCaption() != "")
					{
						$xml .= "<Caption Align=\"bottom\">".
							str_replace("&", "&amp;", $item->getCaption())."</Caption>";
					}

					// Text Representation
					if ($item->getTextRepresentation() != "")
					{
						$xml .= "<TextRepresentation>".
							str_replace("&", "&amp;", $item->getTextRepresentation())."</TextRepresentation>";
					}

					// Parameter
					$parameters = $item->getParameters();
					foreach ($parameters as $name => $value)
					{
						$xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>";
					}
					$xml .= "</MediaAliasItem>";
				}
				break;

			// for output we need technical sections of meta data
			case IL_MODE_OUTPUT:

				// get first technical section
//				$meta =& $this->getMetaData();
				$xml = "<MediaObject Id=\"il__mob_".$this->getId()."\">";

				$media_items =& $this->getMediaItems();
				for($i=0; $i<count($media_items); $i++)
				{
					$item =& $media_items[$i];
					$xml .= "<MediaItem Purpose=\"".$item->getPurpose()."\">";

					// Location
					$xml.= "<Location Type=\"".$item->getLocationType()."\">".
						$this->handleAmps($item->getLocation())."</Location>";

					// Format
					$xml.= "<Format>".$item->getFormat()."</Format>";

					// Layout
					$width = ($item->getWidth() != "")
						? "Width=\"".$item->getWidth()."\""
						: "";
					$height = ($item->getHeight() != "")
						? "Height=\"".$item->getHeight()."\""
						: "";
					$halign = ($item->getHAlign() != "")
						? "HorizontalAlign=\"".$item->getHAlign()."\""
						: "";
					$xml .= "<Layout $width $height $halign />";

					// Caption
					if ($item->getCaption() != "")
					{
						$xml .= "<Caption Align=\"bottom\">".
							str_replace("&", "&amp;", $item->getCaption())."</Caption>";
					}
					
					// Text Representation
					if ($item->getTextRepresentation() != "")
					{
						$xml .= "<TextRepresentation>".
							str_replace("&", "&amp;", $item->getTextRepresentation())."</TextRepresentation>";
					}

					// Parameter
					$parameters = $item->getParameters();
					foreach ($parameters as $name => $value)
					{
						$xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>";
					}
					$xml .= $item->getMapAreasXML();
					$xml .= "</MediaItem>";
				}
				break;

			// full xml for export
			case IL_MODE_FULL:

//				$meta =& $this->getMetaData();
				$xml = "<MediaObject>";

				// meta data
				include_once("Services/MetaData/classes/class.ilMD2XML.php");
				$md2xml = new ilMD2XML(0, $this->getId(), $this->getType());
				$md2xml->setExportMode(true);
				$md2xml->startExport();
				$xml.= $md2xml->getXML();

				$media_items =& $this->getMediaItems();
				for($i=0; $i<count($media_items); $i++)
				{
					$item =& $media_items[$i];
					$xml .= "<MediaItem Purpose=\"".$item->getPurpose()."\">";

					// Location
					$xml.= "<Location Type=\"".$item->getLocationType()."\">".
						$item->getLocation()."</Location>";

					// Format
					$xml.= "<Format>".$item->getFormat()."</Format>";

					// Layout
					$width = ($item->getWidth() != "")
						? "Width=\"".$item->getWidth()."\""
						: "";
					$height = ($item->getHeight() != "")
						? "Height=\"".$item->getHeight()."\""
						: "";
					$halign = ($item->getHAlign() != "")
						? "HorizontalAlign=\"".$item->getHAlign()."\""
						: "";
					$xml .= "<Layout $width $height $halign />";

					// Caption
					if ($item->getCaption() != "")
					{
						$xml .= "<Caption Align=\"bottom\">".
							str_replace("&", "&amp;", $item->getCaption())."</Caption>";
					}
					
					// Text Representation
					if ($item->getTextRepresentation() != "")
					{
						$xml .= "<TextRepresentation>".
							str_replace("&", "&amp;", $item->getTextRepresentation())."</TextRepresentation>";
					}

					// Parameter
					$parameters = $item->getParameters();
					foreach ($parameters as $name => $value)
					{
						$xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>";
					}
					$xml .= $item->getMapAreasXML(true, $a_inst);
					$xml .= "</MediaItem>";
				}
				break;
		}
		$xml .= "</MediaObject>";
		return $xml;
	}

	/**
	* Replace "&" (if not an "&amp;") with "&amp;"
	*/
	function handleAmps($a_str)
	{
		$a_str = str_replace("&amp;", "&", $a_str);
		$a_str = str_replace("&", "&amp;", $a_str);
		return $a_str;
	}
	
	/**
	* export XML
	*/
	function exportXML(&$a_xml_writer, $a_inst = 0)
	{
		$a_xml_writer->appendXML($this->getXML(IL_MODE_FULL, $a_inst));
	}


	/**
	* export all media files of object to target directory
	* note: target directory must be the export target directory,
	* "/objects/il_<inst>_mob_<mob_id>/..." will be appended to this directory
	*
	* @param	string		$a_target_dir		target directory
	*/
	function exportFiles($a_target_dir)
	{
		$subdir = "il_".IL_INST_ID."_mob_".$this->getId();
		ilUtil::makeDir($a_target_dir."/objects/".$subdir);

		$mobdir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->getId();
		ilUtil::rCopy($mobdir, $a_target_dir."/objects/".$subdir);
//echo "from:$mobdir:to:".$a_target_dir."/objects/".$subdir.":<br>";
	}


	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
			$a_value = ilUtil::insertInstIntoID($a_value);
		}

		return $a_value;
	}


	//////
	// EDIT METHODS: these methods act on the media alias in the dom
	//////

	/**
	* content parser set this flag to true, if the media object contains internal links
	* (this method should only be called by the import parser)
	*
	* @param	boolean		$a_contains_link		true, if page contains intern link tag(s)
	*/
	function setContainsIntLink($a_contains_link)
	{
		$this->contains_int_link = $a_contains_link;
	}

	/**
	* returns true, if mob was marked as containing an intern link (via setContainsIntLink)
	* (this method should only be called by the import parser)
	*/
	function containsIntLink()
	{
		return $this->contains_int_link;
	}

	/**
	* static
	*/
	function _deleteAllUsages($a_type, $a_id, $a_usage_hist_nr = 0)
	{
		global $ilDB;
		
		$q = "DELETE FROM mob_usage WHERE usage_type = ".
			$ilDB->quote($a_type, "text").
			" AND usage_id= ".$ilDB->quote($a_id, "integer").
			" AND usage_hist_nr = ".$ilDB->quote($a_usage_hist_nr, "integer");
		$ilDB->manipulate($q);
	}

	/**
	* get mobs of object
	*/
	function _getMobsOfObject($a_type, $a_id, $a_usage_hist_nr = 0)
	{
		global $ilDB;

		$q = "SELECT * FROM mob_usage WHERE ".
			"usage_type = ".$ilDB->quote($a_type, "text")." AND ".
			"usage_id = ".$ilDB->quote($a_id, "integer")." AND ".
			"usage_hist_nr = ".$ilDB->quote($a_usage_hist_nr, "integer");
		$mobs = array();
		$mob_set = $ilDB->query($q);
		while($mob_rec = $ilDB->fetchAssoc($mob_set))
		{
			$mobs[$mob_rec["id"]] = $mob_rec["id"];
		}
		return $mobs;
	}

	/**
	* Save usage of mob within another container (e.g. page)
	*/
	function _saveUsage($a_mob_id, $a_type, $a_id, $a_usage_hist_nr = 0)
	{
		global $ilDB;
		
		$q = "DELETE FROM mob_usage WHERE ".
			" id = ".$ilDB->quote((int) $a_mob_id, "integer")." AND ".
			" usage_type = ".$ilDB->quote($a_type, "text")." AND ".
			" usage_id = ".$ilDB->quote((int) $a_id, "integer")." AND ".
			" usage_hist_nr = ".$ilDB->quote((int) $a_usage_hist_nr, "integer");
		$ilDB->manipulate($q);
		$q = "INSERT INTO mob_usage (id, usage_type, usage_id, usage_hist_nr) VALUES".
			" (".$ilDB->quote((int) $a_mob_id, "integer").",".
			$ilDB->quote($a_type, "text").",".
			$ilDB->quote((int) $a_id, "integer").",".
			$ilDB->quote((int) $a_usage_hist_nr, "integer").")";
		$ilDB->manipulate($q);
	}

	/**
	* Remove usage of mob in another container
	*/
	function _removeUsage($a_mob_id, $a_type, $a_id, $a_usage_hist_nr = 0)
	{
		global $ilDB;
		
		$q = "DELETE FROM mob_usage WHERE ".
			" id = ".$ilDB->quote((int) $a_mob_id, "integer")." AND ".
			" usage_type = ".$ilDB->quote($a_type, "text")." AND ".
			" usage_id = ".$ilDB->quote((int) $a_id, "integer")." AND ".
			" usage_hist_nr = ".$ilDB->quote((int) $a_usage_hist_nr, "integer");
		$ilDB->manipulate($q);
	}

	/**
	* get all usages of current media object
	*/
	function getUsages()
	{
		return $this->lookupUsages($this->getId());
	}
	
	/**
	* Lookup usages of media object
	*
	* @todo: This should be all in one context -> mob id table
	*/
	function lookupUsages($a_id)
	{
		global $ilDB;

		// get usages in learning modules
		$q = "SELECT * FROM mob_usage WHERE id = ".
			$ilDB->quote($a_id, "integer");
		$us_set = $ilDB->query($q);
		$ret = array();
		while($us_rec = $ilDB->fetchAssoc($us_set))
		{
			$ret[] = array("type" => $us_rec["usage_type"],
				"id" => $us_rec["usage_id"],
				"hist_nr" => $us_rec["usage_hist_nr"]);
		}

		// get usages in media pools
		$q = "SELECT DISTINCT mep_id FROM mep_tree JOIN mep_item ON (child = obj_id) WHERE mep_item.foreign_id = ".
			$ilDB->quote($a_id, "integer")." AND mep_item.type = ".$ilDB->quote("mob", "text");
		$us_set = $ilDB->query($q);
		while($us_rec = $ilDB->fetchAssoc($us_set))
		{
			$ret[] = array("type" => "mep",
				"id" => $us_rec["mep_id"]);
		}
		
		// get usages in news items (media casts)
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$news_usages = ilNewsItem::_lookupMediaObjectUsages($a_id);
		foreach($news_usages as $nu)
		{
			$ret[] = $nu;
		}
		

		// get usages in map areas
		$q = "SELECT DISTINCT mob_id FROM media_item it, map_area area ".
			" WHERE area.item_id = it.id ".
			" AND area.link_type = ".$ilDB->quote("int", "text")." ".
			" AND area.target = ".$ilDB->quote("il__mob_".$a_id, "text");
		$us_set = $ilDB->query($q);
		while($us_rec = $ilDB->fetchAssoc($us_set))
		{
			$ret[] = array("type" => "map",
				"id" => $us_rec["mob_id"]);
		}

		// get usages in personal clipboards
		$users = ilObjUser::_getUsersForClipboadObject("mob", $a_id);
		foreach ($users as $user)
		{
			$ret[] = array("type" => "clip",
				"id" => $user);
		}

		return $ret;
	}

	/**
	* Get's the repository object ID of a parent object, if possible
	*/
	function getParentObjectIdForUsage($a_usage, $a_include_all_access_obj_ids = false)
	{
		if(is_int(strpos($a_usage["type"], ":")))
		{
			$us_arr = explode(":", $a_usage["type"]);
			$type = $us_arr[1];
			$cont_type = $us_arr[0];
		}
		else
		{
			$type = $a_usage["type"];
		}
		
		$id = $a_usage["id"];
		$obj_id = false;

		switch($type)
		{
			case "html":					// "old" category pages
				if ($cont_type == "cat")
				{
					$obj_id = $id;
				}
				// Test InfoScreen Text
				if ($cont_type == "tst")
				{
					$obj_id = $id;
					//var_dump($qinfo);
				}
				// Question Pool *Question* Text
				if ($cont_type == "qpl")
				{
					include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
					$qinfo = assQuestion::_getQuestionInfo($id);
					if ($qinfo["original_id"] > 0)
					{
						include_once("./Modules/Test/classes/class.ilObjTest.php");
						$obj_id = ilObjTest::_lookupTestObjIdForQuestionId($id);	// usage in test
					}
					else
					{
						$obj_id = $qinfo["obj_fi"];		// usage in pool
					}
				}
				break;
				
			case "pg":
			
				// Question Pool Question Pages
				if ($cont_type == "qpl")
				{
					include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
					$qinfo = assQuestion::_getQuestionInfo($id);
					if ($qinfo["original_id"] > 0)
					{
						include_once("./Modules/Test/classes/class.ilObjTest.php");
						$obj_id = ilObjTest::_lookupTestObjIdForQuestionId($id);	// usage in test
					}
					else
					{
						$obj_id = $qinfo["obj_fi"];		// usage in pool
					}
				}
				
				// learning modules
				if ($cont_type == "lm" || $cont_type == "dbk")
				{
					include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
					$obj_id = ilLMObject::_lookupContObjID($id);
				}
				
				// glossary definition
				if ($cont_type == "gdf")
				{
					include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
					include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
					$term_id = ilGlossaryDefinition::_lookupTermId($id);
					$obj_id = ilGlossaryTerm::_lookGlossaryID($term_id);
				}
				
				break;
				
			// Media Pool
			case "mep":
				$obj_id = $id;
				break;

			// News Context Object (e.g. MediaCast)
			case "news":
				include_once("./Services/News/classes/class.ilNewsItem.php");
				$obj_id = ilNewsItem::_lookupContextObjId($id);
				break;
		}
		
		return $obj_id;
	}
	
	/**
	* resize image and return new image file ("_width_height" string appended)
	*
	* @param	string		$a_file		full file name
	* @param	int			$a_width	width
	* @param	int			$a_height	height
	*/
	function _resizeImage($a_file, $a_width, $a_height, $a_constrain_prop = false)
	{
		$file_path = pathinfo($a_file);
		$location = substr($file_path["basename"],0,strlen($file_path["basename"]) -
			strlen($file_path["extension"]) - 1)."_".
			$a_width."_".
			$a_height.".".$file_path["extension"];
		$target_file = $file_path["dirname"]."/".
			$location;
		ilUtil::resizeImage($a_file, $target_file,
			(int) $a_width, (int) $a_height, $a_constrain_prop);

		return $location;
	}

	/**
	* get mime type for file
	*
	* @param	string		$a_file		file name
	* @return	string					mime type
	* static
	*/
	static function getMimeType ($a_file)
	{
		// check if mimetype detection enabled in php.ini
		$set = ini_get("mime_magic.magicfile");
		// get mimetype
		if ($set <> "")
		{
			$mime = @mime_content_type($a_file);
		}

		// some php installations return always
		// text/plain, so we make our own detection in this case, too
		if (empty($mime) || $mime == "text/plain")
		{
			$path = pathinfo($a_file);
			$ext = ".".strtolower($path["extension"]);

			/**
			* map of mimetypes.py from python.org (there was no author mentioned in the file)
			*/
			$types_map = ilObjMediaObject::getExt2MimeMap();
			$mime = $types_map[$ext];
		}

		// set default if mimetype detection failed or not possible (e.g. remote file)
		if (empty($mime))
		{
			$mime = "application/octet-stream";
		}

		return $mime;
	}

	/**
	* Determine width and height
	*/
	static function _determineWidthHeight($a_def_width, $a_def_height, $a_format, $a_type,
		$a_file, $a_reference, $a_constrain_proportions, $a_use_original,
		$a_user_width, $a_user_height)
	{
		// determine width and height of known image types
		$width = $a_def_width;
		$height = $a_def_height;
		
		if ($a_format == "audio/mpeg")
		{
			$width = 300;
			$height = 20;
		}
		
		if (ilUtil::deducibleSize($a_format))
		{
			if ($a_type == "File")
			{
				$size = @getimagesize($a_file);
			}
			else
			{
				$size = @getimagesize($a_reference);
			}
		}
		if ($a_use_original)
		{
			if ($size[0] > 0 && $size[1] > 0)
			{
				$width = $size[0];
				$height = $size[1];
			}
		}
		else
		{
			$w = (int) $a_user_width;
			$h = (int) $a_user_height;
			$width = $w;
			$height = $h;
//echo "<br>C-$width-$height-";
			if (ilUtil::deducibleSize($a_format) && $a_constrain_proportions)
			{
				if ($size[0] > 0 && $size[1] > 0)
				{
						$wr = $size[0] / $w;
						$hr = $size[1] / $h;
//echo "<br>+".$wr."+".$size[0]."+".$w."+";
//echo "<br>+".$hr."+".$size[1]."+".$h."+";
						$r = max($wr, $hr);
						$width = (int) ($size[0]/$r);
						$height = (int) ($size[1]/$r);
				}
			}
//echo "<br>D-$width-$height-";
		}
//echo "<br>E-$width-$height-";
		return array("width" => $width, "height" => $height);
	}
	
	/**
	* get file extension to mime type map
	*/
	function getExt2MimeMap()
	{
		$types_map = array (
			'.a'      => 'application/octet-stream',
			'.ai'     => 'application/postscript',
			'.aif'    => 'audio/x-aiff',
			'.aifc'   => 'audio/x-aiff',
			'.aiff'   => 'audio/x-aiff',
			'.asd'    => 'application/astound',
			'.asf'    => 'video/x-ms-asf',
			'.asn'    => 'application/astound',
			'.asx'    => 'video/x-ms-asf',
			'.au'     => 'audio/basic',
			'.avi'    => 'video/x-msvideo',
			'.bat'    => 'text/plain',
			'.bcpio'  => 'application/x-bcpio',
			'.bin'    => 'application/octet-stream',
			'.bmp'    => 'image/x-ms-bmp',
			'.c'      => 'text/plain',
			'.cdf'    => 'application/x-cdf',
			'.class'  => 'application/x-java-applet',
			'.com'    => 'application/octet-stream',
			'.cpio'   => 'application/x-cpio',
			'.csh'    => 'application/x-csh',
			'.css'    => 'text/css',
			'.csv'    => 'text/comma-separated-values',
			'.dcr'    => 'application/x-director',
			'.dir'    => 'application/x-director',
			'.dll'    => 'application/octet-stream',
			'.doc'    => 'application/msword',
			'.dot'    => 'application/msword',
			'.dvi'    => 'application/x-dvi',
			'.dwg'    => 'application/acad',
			'.dxf'    => 'application/dxf',
			'.dxr'    => 'application/x-director',
			'.eml'    => 'message/rfc822',
			'.eps'    => 'application/postscript',
			'.etx'    => 'text/x-setext',
			'.exe'    => 'application/octet-stream',
			'.flv'    => 'video/x-flv',
			'.gif'    => 'image/gif',
			'.gtar'   => 'application/x-gtar',
			'.gz'     => 'application/gzip',
			'.h'      => 'text/plain',
			'.hdf'    => 'application/x-hdf',
			'.htm'    => 'text/html',
			'.html'   => 'text/html',
			'.ief'    => 'image/ief',
			'.iff'    => 'image/iff',
			'.jar'    => 'application/x-java-applet',
			'.jpe'    => 'image/jpeg',
			'.jpeg'   => 'image/jpeg',
			'.jpg'    => 'image/jpeg',
			'.js'     => 'application/x-javascript',
			'.ksh'    => 'text/plain',
			'.latex'  => 'application/x-latex',
			'.m1v'    => 'video/mpeg',
			'.man'    => 'application/x-troff-man',
			'.me'     => 'application/x-troff-me',
			'.mht'    => 'message/rfc822',
			'.mhtml'  => 'message/rfc822',
			'.mid'    => 'audio/x-midi',
			'.midi'   => 'audio/x-midi',
			'.mif'    => 'application/x-mif',
			'.mov'    => 'video/quicktime',
			'.movie'  => 'video/x-sgi-movie',
			'.mp2'    => 'audio/mpeg',
			'.mp3'    => 'audio/mpeg',
			'.mpa'    => 'video/mpeg',
			'.mpe'    => 'video/mpeg',
			'.mpeg'   => 'video/mpeg',
			'.mpg'    => 'video/mpeg',
			'.mp4'    => 'video/mp4',
			'.mv4'    => 'video/mp4',
			'.ms'     => 'application/x-troff-ms',
			'.nc'     => 'application/x-netcdf',
			'.nws'    => 'message/rfc822',
			'.o'      => 'application/octet-stream',
			'.ogg'    => 'application/ogg',
			'.obj'    => 'application/octet-stream',
			'.oda'    => 'application/oda',
			'.p12'    => 'application/x-pkcs12',
			'.p7c'    => 'application/pkcs7-mime',
			'.pbm'    => 'image/x-portable-bitmap',
			'.pdf'    => 'application/pdf',
			'.pfx'    => 'application/x-pkcs12',
			'.pgm'    => 'image/x-portable-graymap',
			'.php'    => 'application/x-httpd-php',
			'.phtml'  => 'application/x-httpd-php',
			'.pl'     => 'text/plain',
			'.png'    => 'image/png',
			'.pnm'    => 'image/x-portable-anymap',
			'.pot'    => 'application/vnd.ms-powerpoint',
			'.ppa'    => 'application/vnd.ms-powerpoint',
			'.ppm'    => 'image/x-portable-pixmap',
			'.pps'    => 'application/vnd.ms-powerpoint',
			'.ppt'    => 'application/vnd.ms-powerpoint',
			'.ps'     => 'application/postscript',
			'.psd'    => 'image/psd',
			'.pwz'    => 'application/vnd.ms-powerpoint',
			'.py'     => 'text/x-python',
			'.pyc'    => 'application/x-python-code',
			'.pyo'    => 'application/x-python-code',
			'.qt'     => 'video/quicktime',
			'.ra'     => 'audio/x-pn-realaudio',
			'.ram'    => 'application/x-pn-realaudio',
			'.ras'    => 'image/x-cmu-raster',
			'.rdf'    => 'application/xml',
			'.rgb'    => 'image/x-rgb',
			'.roff'   => 'application/x-troff',
			'.rpm'    => 'audio/x-pn-realaudio-plugin',
			'.rtf'    => 'application/rtf',
			'.rtx'    => 'text/richtext',
			'.sgm'    => 'text/x-sgml',
			'.sgml'   => 'text/x-sgml',
			'.sh'     => 'application/x-sh',
			'.shar'   => 'application/x-shar',
			'.sit'    => 'application/x-stuffit',
			'.snd'    => 'audio/basic',
			'.so'     => 'application/octet-stream',
			'.spc'    => 'text/x-speech',
			'.src'    => 'application/x-wais-source',
			'.sv4cpio'=> 'application/x-sv4cpio',
			'.sv4crc' => 'application/x-sv4crc',
			'.svg'    => 'image/svg+xml',
			'.swf'    => 'application/x-shockwave-flash',
			'.t'      => 'application/x-troff',
			'.tar'    => 'application/x-tar',
			'.talk'   => 'text/x-speech',
			'.tbk'    => 'application/toolbook',
			'.tcl'    => 'application/x-tcl',
			'.tex'    => 'application/x-tex',
			'.texi'   => 'application/x-texinfo',
			'.texinfo'=> 'application/x-texinfo',
			'.tif'    => 'image/tiff',
			'.tiff'   => 'image/tiff',
			'.tr'     => 'application/x-troff',
			'.tsv'    => 'text/tab-separated-values',
			'.tsp'    => 'application/dsptype',
			'.txt'    => 'text/plain',
			'.ustar'  => 'application',
			'.vcf'    => 'text/x-vcard',
			'.vox'    => 'audio/voxware',
			'.wav'    => 'audio/x-wav',
			'.wax'    => 'audio/x-ms-wax',
			'.wiz'    => 'application/msword',
			'.wm'     => 'video/x-ms-wm',
			'.wma'    => 'audio/x-ms-wma',
			'.wmd'    => 'video/x-ms-wmd',
			'.wml'    => 'text/vnd.wap.wml',
			'.wmlc'   => 'application/vnd.wap.wmlc',
			'.wmls'   => 'text/vnd.wap.wmlscript',
			'.wmlsc'  => 'application/vnd.wap.wmlscriptc',
			'.wmv'    => 'video/x-ms-wmv',
			'.wmx'    => 'video/x-ms-wmx',
			'.wmz'    => 'video/x-ms-wmz',
			'.wvx'    => 'video/x-ms-wvx',
			'.wrl'    => 'x-world/x-vrml',
			'.xbm'    => 'image/x-xbitmap',
			'.xla'    => 'application/msexcel',
			'.xlb'    => 'application/vnd.ms-excel',
			'.xls'    => 'application/msexcel',
			'.xml'    => 'text/xml',
			'.xpm'    => 'image/x-xpixmap',
			'.xsl'    => 'application/xml',
			'.xwd'    => 'image/x-xwindowdump',
			'.zip'    => 'application/zip');

		return $types_map;
	}

	/**
	* Get simple mime types that deactivate parameter property
	* files tab in ILIAS
	*/
	static function _getSimpleMimeTypes()
	{
		return array("image/x-ms-bmp", "image/gif", "image/jpeg", "image/x-portable-bitmap",
			"image/png", "image/psd", "image/tiff", "application/pdf");
	}
	
	function getDataDirectory()
	{
		return ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
	}

	/**
	* Check whether only autostart parameter should be supported (instead
	* of parameters input field.
	*
	* This should be the same behaviour as mp3/flv in page.xsl
	*/
	static function _useAutoStartParameterOnly($a_loc, $a_format)
	{
		$lpath = pathinfo($a_loc);
		if ($lpath["extension"] == "mp3" && $a_format == "audio/mpeg")
		{
			return true;
		}
		if ($lpath["extension"] == "flv")
		{
			return true;
		}
		return false;
	}

	/**
	* create new media object and update page in db and return new media object
	*/
	function &_saveTempFileAsMediaObject($name, $tmp_name, $upload = TRUE)
	{
		// create dummy object in db (we need an id)
		$media_object = new ilObjMediaObject();
		$media_object->setTitle($name);
		$media_object->setDescription("");
		$media_object->create();

		// determine and create mob directory, move uploaded file to directory
		$media_object->createDirectory();
		$mob_dir = ilObjMediaObject::_getDirectory($media_object->getId());

		$media_item =& new ilMediaItem();
		$media_object->addMediaItem($media_item);
		$media_item->setPurpose("Standard");

		$file = $mob_dir."/".$name;
		if ($upload)
		{
			ilUtil::moveUploadedFile($tmp_name,$name, $file);
		}
		else
		{
			copy($tmp_name, $file);
		}
		// get mime type
		$format = ilObjMediaObject::getMimeType($file);
		$location = $name;
		// set real meta and object data
		$media_item->setFormat($format);
		$media_item->setLocation($location);
		$media_item->setLocationType("LocalFile");
		$media_object->setTitle($name);
		$media_object->setDescription($format);

		if (ilUtil::deducibleSize($format))
		{
			$size = getimagesize($file);
			$media_item->setWidth($size[0]);
			$media_item->setHeight($size[1]);
		}
		$media_item->setHAlign("Left");

		ilUtil::renameExecutables($mob_dir);
		$media_object->update();

		return $media_object;
	}
	
	/**
	* Get all media objects linked in map areas of this media object
	*/
	function getLinkedMediaObjects($a_ignore = "")
	{
		$linked = array();
		
		if (!is_array($a_ignore))
		{
			$a_ignore = array();
		}
		
		// get linked media objects (map areas)
		$med_items = $this->getMediaItems();

		foreach($med_items as $med_item)
		{
			$int_links = ilMapArea::_getIntLinks($med_item->getId());
			foreach ($int_links as $k => $int_link)
			{
				if ($int_link["Type"] == "MediaObject")
				{
					include_once("./Services/COPage/classes/class.ilInternalLink.php");
					$l_id = ilInternalLink::_extractObjIdOfTarget($int_link["Target"]);
					if (ilObject::_exists($l_id))
					{
						if (!in_array($l_id, $linked) && 
							!in_array($l_id, $a_ignore))
						{
							$linked[] = $l_id;
						}
					}
				}
			}
		}
//var_dump($linked);
		return $linked;
	}
}
?>
