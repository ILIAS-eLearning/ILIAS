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
require_once("content/classes/class.ilLearningModule.php");
require_once("classes/class.ilMetaData.php");
require_once("content/classes/class.ilParagraph.php");
require_once("content/classes/class.ilLMTable.php");

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
	var $lm_object;
	var $keyword_language;


	/**
	* Constructor
	*
	* @param	object		$a_lm_object	must be of type ilObjLearningModule
	* @param	string		$a_xml_file		xml file
	* @access	public
	*/
	function ilLMParser(&$a_lm_object, $a_xml_file)
	{
		parent::ilSaxParser($a_xml_file);
		$this->cnt = array();
		$this->current_element = array();
		$this->structure_objects = array();
		$this->lm_object =& $a_lm_object;
		//$this->lm_id = $a_lm_id;
		$this->st_into_tree = array();
		$this->pg_into_tree = array();

		// Todo: The following has to go to other places
		/*
		$query = "DELETE FROM lm_tree WHERE lm_id ='".$a_lm_id."'";
		$this->ilias->db->query($query);
		$query = "DELETE FROM lm_data WHERE lm_id ='".$a_lm_id."'";
		$this->ilias->db->query($query);
		$query = "DELETE FROM lm_page_object WHERE lm_id ='".$a_lm_id."'";
		$this->ilias->db->query($query);*/
		/*
		$query = "DELETE FROM meta_data";
		$this->ilias->db->query($query);*/

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
	}

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
//echo "BEGIN_TAG:".$a_name.":<br>";
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

			case "MetaData":
				$this->in_meta_data = true;
				$this->meta_data =& new ilMetaData();
				$this->current_object->assignMetaData($this->meta_data);
				if(get_class($this->current_object) == "ilobjlearningmodule")
				{
//echo "starting new meta data for lm<br>";
					$this->meta_data->setId($this->lm_object->getId());
					$this->meta_data->setType("lm");
				}
				break;

			case "Identifier":
				$this->meta_data->setImportIdentifierEntryID($a_attribs["Entry"]);
				$this->meta_data->setImportIdentifierCatalog($a_attribs["Catalog"]);
				break;

			case "Keyword":
				$this->keyword_language = $a_attribs["Language"];
				break;

		}
		$this->beginElement($a_name);
//echo "Begin Tag: $a_name<br>";

		// append content to page xml content
		if($this->in_page_object && !$this->in_meta_data)
		{
			$this->page_object->appendXMLContent($this->buildTag("start", $a_name, $a_attribs));
		}
	}

	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{

		// append content to page xml content
		if($this->in_page_object && !$this->in_meta_data)
		{
			$this->page_object->appendXMLContent($this->buildTag("end", $a_name));
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
//echo "PageObject ".$this->page_object->getImportId().":<br>";
					/*
					$content = $this->page_object->getContent();
					foreach($content as $co_object)
					{
						if (get_class($co_object) == "ilparagraph")
						{
							echo nl2br($co_object->getText())."<br><br>";
						}
					}*/
					$this->page_object->createFromXML();
					$this->pg_mapping[$this->page_object->getImportId()]
						= $this->page_object->getId();

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

			case "MetaData":
				$this->in_meta_data = false;
				// save structure object at the end of its meta block
				if(get_class($this->current_object) == "ilstructureobject")
				{
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
				}
				if(get_class($this->current_object) == "ilobjlearningmodule")
				{
					$this->current_object->update();
				}
				break;

			case "Paragraph":
				// can't unset paragraph object, because PageObject is still processing
				break;

			case "Table":
				unset ($this->container[count($this->container) - 1]);
				break;

		}
		$this->endElement($a_name);

	}

	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);
		if(!empty($a_data))
		{
			if($this->in_page_object && !$this->in_meta_data)
			{
				$this->page_object->appendXMLContent($a_data);
			}


			switch($this->getCurrentElement())
			{
				case "Paragraph":
					//$this->paragraph->appendText($a_data);
//echo "setText(".htmlentities($a_data)."), strlen:".strlen($a_data)."<br>";
					break;

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

			}
		}

	}

}
?>
