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

require_once("content/classes/class.ilPageObject.php");
require_once("content/classes/class.ilStructureObject.php");
require_once("content/classes/class.ilObjLearningModule.php");
require_once("classes/class.ilMetaData.php");
require_once("content/classes/class.ilParagraph.php");
require_once("content/classes/class.ilLMTable.php");
require_once("content/classes/class.ilMediaObject.php");
require_once("content/classes/class.ilMediaItem.php");
require_once("content/classes/class.ilBibItem.php");

/**
* Learning Module Parser
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilSaxParser
* @package content
*/
class ilLMParser extends ilSaxParser
{
	var $cnt;				// counts open elements
	var $current_element;	// store current element type
	var $learning_module;	// current learning module
	var $page_object;		// current page object
	var $structure_objects;	// array of current structure objects
	var $media_object;
	var $current_object;	// at the time a LearningModule, PageObject or StructureObject
	var $meta_data;			// current meta data object
	var $paragraph;
	var $table;
	var $lm_tree;
	var $pg_into_tree;
	var $st_into_tree;
	var $container;
	var $in_page_object;	// are we currently within a PageObject? true/false
	var $in_meta_data;		// are we currently within MetaData? true/false
	var $in_media_object;
	var $lm_object;
	var $keyword_language;
	var $pages_with_int_links;
	var $mob_mapping;
	var $subdir;
	var $media_item;		// current media item
	var $loc_type;			// current location type

	var $bib_item;			// current bib item object
	var $in_bib_item;		// are we currently within BibItem? true/false

	/**
	* Constructor
	*
	* @param	object		$a_lm_object	must be of type ilObjLearningModule
	* @param	string		$a_xml_file		xml file
	* @param	string		$a_subdir		subdirectory in import directory
	* @access	public
	*/
	function ilLMParser(&$a_lm_object, $a_xml_file, $a_subdir)
	{
		parent::ilSaxParser($a_xml_file);
		$this->cnt = array();
		$this->current_element = array();
		$this->structure_objects = array();
		$this->lm_object =& $a_lm_object;
		//$this->lm_id = $a_lm_id;
		$this->st_into_tree = array();
		$this->pg_into_tree = array();
		$this->pages_with_int_links = array();
		$this->mob_mapping = array();
		$this->subdir = $a_subdir;

		$this->lm_tree = new ilTree($this->lm_object->getId());
		$this->lm_tree->setTreeTablePK("lm_id");
		$this->lm_tree->setTableNames('lm_tree','lm_data');
		//$this->lm_tree->addTree($a_lm_id, 1); happens in ilObjLearningModuleGUI

	}

	/**
	* set event handler
	* should be overwritten by inherited class
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	function startParsing()
	{
		parent::startParsing();
		$this->storeTree();
		$this->processIntLinks();
		$this->copyMobFiles();
	}

	/**
	* insert StructureObjects and PageObjects into tree
	*/
	function storeTree()
	{
		foreach($this->st_into_tree as $st)
		{
			$this->lm_tree->insertNode($st["id"], $st["parent"]);
			if (is_array($this->pg_into_tree[$st["id"]]))
			{
				foreach($this->pg_into_tree[$st["id"]] as $pg)
				{
					switch ($pg["type"])
					{
						case "pg_alias":
//echo "storeTree.pg_alias:".$this->pg_mapping[$pg["id"]].":".$st["id"].":<br>";
							$this->lm_tree->insertNode($this->pg_mapping[$pg["id"]], $st["id"]);
							break;

						case "pg":
//echo "storeTree.pg:".$pg["id"].":".$st["id"].":<br>";
							$this->lm_tree->insertNode($pg["id"], $st["id"]);
							break;
					}
				}
			}
		}
//echo "6";
	}


	/**
	* updates all internal links
	*/
	function processIntLinks()
	{
		$pg_mapping = array();
		foreach($this->pg_mapping as $key => $value)
		{
			$pg_mapping[$key] = "pg_".$value;
		}
		foreach($this->pages_with_int_links as $page_id)
		{
			$page_obj =& new ilPageObject($page_id);
			$page_obj->buildDom();
			$page_obj->mapIntLinks($pg_mapping);
			$page_obj->update(false);
			unset($page_obj);
		}
	}


	/**
	* copy multimedia object files from import zip file to mob directory
	*/
	function copyMobFiles()
	{
		$imp_dir = $this->lm_object->getImportDirectory();
		foreach ($this->mob_mapping as $origin_id => $mob_id)
		{
			if(empty($origin_id))
			{
				continue;
			}
			$obj_dir = str_replace("_", "", $origin_id);
			$source_dir = $imp_dir."/".$this->subdir."/objects/".$obj_dir;
			$target_dir = $this->ilias->ini->readVariable("server","webspace_dir")."/mobs/mm_".$mob_id;
//echo "copy from $source_dir to $target_dir <br>";
			if (@is_dir($source_dir))
			{
				// make target directory
				@mkdir($target_dir);
				@chmod($target_dir, 0755);

				if (@is_dir($target_dir))
				{
					ilUtil::rCopy($source_dir, $target_dir);
				}
			}
		}
	}


	/*
	* update parsing status for a element begin
	*/
	function beginElement($a_name)
	{
		if(!isset($this->status["$a_name"]))
		{
			$this->cnt[$a_name] == 1;
		}
		else
		{
			$this->cnt[$a_name]++;
		}
		$this->current_element[count($this->current_element)] = $a_name;
	}

	/*
	* update parsing status for an element ending
	*/
	function endElement($a_name)
	{
		$this->cnt[$a_name]--;
		unset ($this->current_element[count($this->current_element) - 1]);
	}

	/*
	* returns current element
	*/
	function getCurrentElement()
	{
		return ($this->current_element[count($this->current_element) - 1]);
	}

	/*
	* returns number of current open elements of type $a_name
	*/
	function getOpenCount($a_name)
	{
		if (isset($this->cnt[$a_name]))
		{
			return $this->cnt[$a_name];
		}
		else
		{
			return 0;
		}

	}

	/**
	* generate a tag with given name and attributes
	*
	* @param	string		"start" | "end" for starting or ending tag
	* @param	string		element/tag name
	* @param	array		array of attributes
	*/
	function buildTag ($type, $name, $attr="")
	{
		$tag = "<";

		if ($type == "end")
			$tag.= "/";

		$tag.= $name;

		if (is_array($attr))
		{
			while (list($k,$v) = each($attr))
				$tag.= " ".$k."=\"$v\"";
		}

		$tag.= ">";

		return $tag;
	}

	/**
	* handler for begin of element
	*/
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
// echo "BEGIN_TAG:".$a_name.":<br>";
		switch($a_name)
		{
			case "ContentObject":
				$this->current_object =& $this->lm_object;
				break;

			case "StructureObject":
//echo "<br><br>StructureOB-SET-".count($this->structure_objects)."<br>";
				$this->structure_objects[count($this->structure_objects)]
					=& new ilStructureObject();
				$this->current_object =& $this->structure_objects[count($this->structure_objects) - 1];
				$this->current_object->setLMId($this->lm_object->getId());
				break;

			case "PageObject":
				$this->in_page_object = true;
				$this->page_object =& new ilPageObject();
				$this->page_object->setLMId($this->lm_object->getId());
				//$this->container = array();
				//$this->container[] =& $this->page_object;
				$this->current_object =& $this->page_object;
				$this->page_object->setXMLContent("");
				break;

			case "PageAlias":
				$this->page_object->setAlias(true);
				$this->page_object->setOriginID($a_attribs["OriginId"]);
				break;

			case "MediaObject":
//echo "<br>---NEW MEDIAOBJECT---<br>";
				$this->in_media_object = true;
				$this->media_object =& new ilMediaObject();
				break;

			case "MediaAlias":
//echo "<br>---NEW MEDIAALIAS---<br>";
				$this->media_object->setAlias(true);
				$this->media_object->setOriginID($a_attribs["OriginId"]);
				break;

			case "MediaItem":
				$this->media_item =& new ilMediaItem();
				$this->media_item->setPurpose($a_attribs["Purpose"]);
				break;

			case "Layout":
				if (is_object($this->media_object) && $this->in_media_object)
				{
					$this->media_item->setWidth($a_attribs["Width"]);
					$this->media_item->setHeight($a_attribs["Height"]);
					$this->media_item->setHAlign($a_attribs["HorizontalAlign"]);
				}
				break;

			case "Parameter":
				if (is_object($this->media_object) && $this->in_media_object)
				{
					$this->media_item->setParameter($a_attribs["Name"], $a_attribs["Value"]);
				}
				break;

			////////////////////////////////////////////////
			/// Meta Data Section
			////////////////////////////////////////////////
			case "MetaData":
				$this->in_meta_data = true;
//echo "<br>---NEW METADATA---<br>";
				$this->meta_data =& new ilMetaData();
				if(!$this->in_media_object)
				{
					$this->current_object->assignMetaData($this->meta_data);
					if(get_class($this->current_object) == "ilobjlearningmodule")
					{
						$this->meta_data->setId($this->lm_object->getId());
						$this->meta_data->setType("lm");
					}
				}
				else
				{
					$this->media_object->assignMetaData($this->meta_data);
				}
				break;

			// GENERAL: Identifier
			case "Identifier":
				if ($this->in_meta_data)
				{
					$this->meta_data->setImportIdentifierEntryID($a_attribs["Entry"]);
					$this->meta_data->setImportIdentifierCatalog($a_attribs["Catalog"]);
				}
				break;

			// GENERAL: Keyword
			case "Keyword":
//echo "<b>>>".count($this->meta_data->technicals)."</b><br>";
				$this->keyword_language = $a_attribs["Language"];
				break;

			case "IntLink":
				if(is_object($this->page_object))
				{
					$this->page_object->setContainsIntLink(true);
				}
				break;

			// TECHNICAL
			case "Technical":
				$this->meta_technical =& new ilMetaTechnical($this->meta_data);
				$this->meta_data->addTechnicalSection($this->meta_technical);
//echo "<b>>>".count($this->meta_data->technicals)."</b><br>";
				break;

			// TECHNICAL: Size
			case "Size":
				$this->meta_technical->setSize($a_attribs["Size"]);
				break;

			case "Location":
				$this->loc_type = $a_attribs["Type"];
				break;

			// TECHNICAL: Requirement
			case "Requirement":
				if (!is_object($this->requirement_set))
				{
					$this->requirement_set =& new ilMetaTechnicalRequirementSet();
				}
				$this->requirement =& new ilMetaTechnicalRequirement();
				break;

			// TECHNICAL: OperatingSystem
			case "OperatingSystem":
				$this->requirement->setType("OperatingSystem");
				$this->requirement->setName($a_attribs["Name"]);
				$this->requirement->setMinVersion($a_attribs["MinimumVersion"]);
				$this->requirement->setMaxVersion($a_attribs["MaximumVersion"]);
				break;

			// TECHNICAL: Browser
			case "Browser":
				$this->requirement->setType("Browser");
				$this->requirement->setName($a_attribs["Name"]);
				$this->requirement->setMinVersion($a_attribs["MinimumVersion"]);
				$this->requirement->setMaxVersion($a_attribs["MaximumVersion"]);
				break;

			// TECHNICAL: OrComposite
			case "OrComposite":
				$this->meta_technical->addRequirementSet($this->requirement_set);
				unset($this->requirement_set);
				break;

			// TECHNICAL: InstallationRemarks
			case "InstallationRemarks":
				$this->meta_technical->setInstallationRemarksLanguage($a_attribs["Language"]);
				break;

			// TECHNICAL: InstallationRemarks
			case "OtherPlatformRequirements":
				$this->meta_technical->setOtherRequirementsLanguage($a_attribs["Language"]);
				break;

			case "Bibliography":
				$this->in_bib_item = true;
// echo "<br>---NEW BIBLIOGRAPHY---<br>";
				$this->bib_item =& new ilBibItem();
				break;

		}
		$this->beginElement($a_name);
//echo "Begin Tag: $a_name<br>";

		// append content to page xml content
		if($this->in_page_object && !$this->in_meta_data && !$this->in_media_object)
		{
			$this->page_object->appendXMLContent($this->buildTag("start", $a_name, $a_attribs));
		}
		// append content to meta data xml content
        if ($this->in_meta_data )   // && !$this->in_page_object && !$this->in_media_object
        {
            $this->meta_data->appendXMLContent("\n".$this->buildTag("start", $a_name, $a_attribs));
        }
		// append content to bibitem xml content
        if ($this->in_bib_item)   // && !$this->in_page_object && !$this->in_media_object
        {
            $this->bib_item->appendXMLContent("\n".$this->buildTag("start", $a_name, $a_attribs));
        }
	}


	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{

		// append content to page xml content
		if ($this->in_page_object && !$this->in_meta_data && !$this->in_media_object)
		{
			$this->page_object->appendXMLContent($this->buildTag("end", $a_name));
		}

		if ($this->in_meta_data)	//  && !$this->in_page_object && !$this->in_media_object

		// append content to metadataxml content
		if($a_name == "MetaData")
		{
			$this->meta_data->appendXMLContent("\n".$this->buildTag("end", $a_name));
		}
		else
		{
			$this->meta_data->appendXMLContent($this->buildTag("end", $a_name));
		}

		// append content to bibitemxml content
		if ($this->in_bib_item)	// && !$this->in_page_object && !$this->in_media_object
		{
			if($a_name == "BibItem")
			{
				$this->bib_item->appendXMLContent("\n".$this->buildTag("end", $a_name));
			}
			else
			{
				$this->bib_item->appendXMLContent($this->buildTag("end", $a_name));
			}
		
		}
        
		switch($a_name)
		{
			case "StructureObject":
				unset($this->meta_data);
				unset($this->structure_objects[count($this->structure_objects) - 1]);
				break;

			case "PageObject":

				$this->in_page_object = false;
				if (!$this->page_object->isAlias())
				{
//echo "ENDPageObject ".$this->page_object->getImportId().":<br>";
					//$this->page_object->createFromXML();
					$this->page_object->updateFromXML();
					$this->pg_mapping[$this->page_object->getImportId()]
						= $this->page_object->getId();
					if ($this->page_object->containsIntLink())
					{
						$this->pages_with_int_links[] = $this->page_object->getId();
					}
				}

				// if we are within a structure object: put page in tree
				$cnt = count($this->structure_objects);
				if ($cnt > 0)
				{
					$parent_id = $this->structure_objects[$cnt - 1]->getId();
					if ($this->page_object->isAlias())
					{
						$this->pg_into_tree[$parent_id][] = array("type" => "pg_alias", "id" => $this->page_object->getOriginId());
					}
					else
					{
						$this->pg_into_tree[$parent_id][] = array("type" => "pg", "id" => $this->page_object->getId());
					}
				}

				// if we are within a structure object: put page in tree
				unset($this->meta_data);	//!?!
				unset($this->page_object);
				unset ($this->container[count($this->container) - 1]);
				break;

			case "MediaObject":
				$this->in_media_object = false;
//echo "ENDMediaObject:ImportId:".$this->media_object->getImportId()."<br>";
				// create media object on first occurence of an OriginId
				if(empty($this->mob_mapping[$this->media_object->getImportId()]))
				{
					if ($this->media_object->isAlias())
					{
						// this data will be overwritten by the "real" mob
						// see else section below
						$dummy_meta =& new ilMetaData();
						$this->media_object->assignMetaData($dummy_meta);
						$this->media_object->setTitle("dummy");
						$this->media_object->setDescription("dummy");
					}
					$this->media_object->create();
					$this->mob_mapping[$this->media_object->getImportId()]
							= $this->media_object->getId();
//echo "create:origin:".$this->media_object->getImportId().":ID:".$this->mob_mapping[$this->media_object->getImportId()]."<br>";
				}
				else
				{
					// get the id from mapping
					$this->media_object->setId($this->mob_mapping[$this->media_object->getImportId()]);

					// update "real" (no alias) media object
					// (note: we overwrite any data from the dummy mob
					// created by an MediaAlias, only the data of the real
					// object is stored in db separately; data of the
					// MediaAliases are within the page XML
					if (!$this->media_object->isAlias())
					{
//echo "<b>REAL UPDATING STARTS HERE</b><br>";
//echo "<b>>>".count($this->meta_data->technicals)."</b><br>";
//echo "origin:".$this->media_object->getImportId().":ID:".$this->mob_mapping[$this->media_object->getImportId()]."<br>";
						$this->media_object->update();
					}
				}

				// append media alias to page, if we are in a page
				if ($this->in_page_object)
				{
					$this->page_object->appendXMLContent($this->media_object->getXML(IL_MODE_ALIAS));
//echo "Appending:".htmlentities($this->media_object->getXML(IL_MODE_ALIAS))."<br>";
				}

				break;

			case "MediaItem":
				$this->media_object->addMediaItem($this->media_item);
//echo "adding media item";
				break;

			case "MetaData":
				$this->in_meta_data = false;
                if(get_class($this->current_object) == "ilpageobject" && !$this->in_media_object)
				{
					// Metadaten eines PageObjects sichern in NestedSet
					if (is_object($this->page_object))
					{
						$this->page_object->createFromXML();

						include_once("./classes/class.ilNestedSetXML.php");
						$nested = new ilNestedSetXML();
						$nested->import($this->meta_data->getXMLContent(),$this->page_object->getId(),"pg");
					}
                }
				else if(get_class($this->current_object) == "ilstructureobject")
				{    // save structure object at the end of its meta block
					// determine parent
					$cnt = count($this->structure_objects);
					if ($cnt > 1)
					{
						$parent_id = $this->structure_objects[$cnt - 2]->getId();
					}
					else
					{
						$parent_id = $this->lm_tree->getRootId();
					}

					// create structure object and put it in tree
					$this->current_object->create();
					$this->st_into_tree[] = array ("id" => $this->current_object->getId(),
						"parent" => $parent_id);
                    
                    // Metadaten eines StructureObjects sichern in NestedSet
                    include_once("./classes/class.ilNestedSetXML.php");
                    $nested = new ilNestedSetXML();
                    $nested->import($this->meta_data->getXMLContent(),$this->current_object->getId(),"st");                    
				}
                else if(get_class($this->current_object) == "ilobjdlbook" || get_class($this->current_object) == "ilobjlearningmodule")
                {
                    // Metadaten eines ContentObjects sichern in NestedSet
                    include_once("./classes/class.ilNestedSetXML.php");
                    $nested = new ilNestedSetXML();
                    $nested->import($this->meta_data->getXMLContent(),$this->current_object->getId(),"lm");                    
                }
                
				
				if(get_class($this->current_object) == "ilobjlearningmodule" || get_class($this->current_object) == "ilobjdlbook" )
				{
					$this->current_object->update();
				}
				break;

			case "Bibliography":
                
				$this->in_bib_item = false;
				
                $nested = new ilNestedSetXML();
                $nested->import($this->bib_item->getXMLContent(),$this->lm_object->getId(),"bib");
                break;
                
			case "Paragraph":
				// can't unset paragraph object, because PageObject is still processing
				break;

			case "Table":
				unset ($this->container[count($this->container) - 1]);
				break;

			//////////////////////////////////
			/// MetaData Section
			//////////////////////////////////
			// TECHNICAL: Requirement
			case "Requirement":
				$this->requirement_set->addRequirement($this->requirement);
				break;

		}
		$this->endElement($a_name);

	}

	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		// i don't know why this is necessary, but
		// the parser seems to convert "&gt;" to ">" and "&lt;" to "<"
		// in character data, but we don't want that, because it's the
		// way we mask user html in our content, so we convert back...
		$a_data = str_replace("<","&lt;",$a_data);
		$a_data = str_replace(">","&gt;",$a_data);

		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);
		if(!empty($a_data))
		{
			// append all data to page, if we are within PageObject,
			// but not within MetaData or MediaObject
			if ($this->in_page_object && !$this->in_meta_data && !$this->in_media_object)
			{
				$this->page_object->appendXMLContent($a_data);
			}
            
			if ($this->in_meta_data  )	
			{
				$this->meta_data->appendXMLContent($a_data);
			}

			if ($this->in_bib_item  )	
			{
				$this->bib_item->appendXMLContent($a_data);
			}

			switch($this->getCurrentElement())
			{
				case "Paragraph":
					//$this->paragraph->appendText($a_data);
//echo "setText(".htmlentities($a_data)."), strlen:".strlen($a_data)."<br>";
					break;

				case "Caption":
					if ($this->in_media_object)
					{
						$this->media_item->setCaption($a_data);
					}
					break;

				///////////////////////////
				/// MetaData Section
				///////////////////////////
				case "Title":
					$this->meta_data->setTitle($a_data);
					break;

				case "Language":
					$this->meta_data->setLanguage($a_data);
					break;

				case "Description":
					$this->meta_data->setDescription($a_data);
					break;

				case "Keyword":
					$this->meta_data->addKeyword($this->keyword_language, $a_data);
//echo "KEYWORD_ADD:".$this->keyword_language.":".$a_data."::<br>";
					break;

				// TECHNICAL: Format
				case "Format":
					$this->meta_technical->addFormat($a_data);
					break;

				// TECHNICAL: Size
				case "Size":
					$this->meta_technical->setSize($a_data);
					break;

				// TECHNICAL: Location
				case "Location":
//echo "Adding a location:".$this->loc_type.":".$a_data.":<br>";
					// TODO: adapt for files in "real" subdirectories
					$this->meta_technical->addLocation($this->loc_type, $a_data);
					break;

				// TECHNICAL: InstallationRemarks
				case "InstallationRemarks":
					$this->meta_technical->setInstallationRemarks($a_data);
					break;

				// TECHNICAL: InstallationRemarks
				case "OtherPlatformRequirements":
					$this->meta_technical->setOtherRequirements($a_data);
					break;

				// TECHNICAL: Duration
				case "Duration":
					$this->meta_technical->setDuration($a_data);
					break;

			}
		}

	}

}
?>
