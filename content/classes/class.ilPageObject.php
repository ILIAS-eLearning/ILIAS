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

require_once("content/classes/class.ilLMObject.php");
require_once("content/classes/class.ilPageParser.php");
require_once("content/classes/class.ilPageContent.php");

define("IL_INSERT_BEFORE", 0);
define("IL_INSERT_AFTER", 1);
define("IL_INSERT_CHILD", 2);

/**
* Class ilPageObject
*
* Handles PageObjects of ILIAS Learning Modules (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilPageObject extends ilLMObject
{
	var $is_alias;
	var $origin_id;
	var $content;		// array of objects (ilParagraph or ilMediaObject)
	var $id;
	var $ilias;
	var $dom;
	var $xml;
	var $encoding;
	var $node;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageObject($a_id = 0)
	{
		global $ilias;

		parent::ilLMObject();
		$this->setType("pg");
		$this->id = $a_id;
		$this->ilias =& $ilias;

		$this->is_alias = false;
		$this->content = array();

		if($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	*
	*/
	function read()
	{
		parent::read();

		$query = "SELECT * FROM lm_page_object WHERE page_id = '".$this->id."'";
		$pg_set = $this->ilias->db->query($query);
		$this->page_record = $pg_set->fetchRow(DB_FETCHMODE_ASSOC);

		// todo: make utf8 global (db content should be already utf8)
		$this->xml = $this->page_record["content"];

		// todo: this is for testing only
//echo htmlentities($this->xml);
		//$this->dom =& domxml_open_mem(utf8_encode($this->xml));

		//$page_parser = new ilPageParser($this, $this->xml_content);
		//$page_parser->startParsing();

	}

	function buildDom()
	{
		$this->dom =& domxml_open_mem($this->xml);

		$xpc = xpath_new_context($this->dom);
		$path = "//PageObject";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$this->node =& $res->nodeset[0];
		}

	}

	function &getDom()
	{
		return $this->dom;
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

	function getimportId()
	{
		return $this->meta_data->getImportIdentifierEntryID();
	}


	/*
	function appendContent(&$a_content_obj)
	{
		$this->content[] =& $a_content_obj;
	}*/

	function &getContentObject($a_hier_id)
	{
		$cont_node =& $this->getContentNode($a_hier_id);
		switch($cont_node->node_name())
		{
			case "Paragraph":

				$par = new ilParagraph($this->dom);
				$par->setNode($cont_node);
				$par->setHierId($a_hier_id);
				return $par;

			case "Table":

				$tab = new ilLMTable($this->dom);
				$tab->setNode($cont_node);
				$tab->setHierId($a_hier_id);
				return $tab;

		}
	}

	function &getContentNode($a_hier_id)
	{
 		// search for attribute "//*[@HierId = '%s']".
//echo "get node :$a_hier_id:";
		$xpc = xpath_new_context($this->dom);
		if($a_hier_id == "pg")
		{
			return $this->node;
		}
		else
		{
			$path = "//*[@HierId = '$a_hier_id']";
		}
		$res =& xpath_eval($xpc, $path);
//echo "1:count:".count($res->nodeset).":hierid:$a_hier_id:";
		if (count($res->nodeset) == 1)
		{
			$cont_node =& $res->nodeset[0];
			return $cont_node;
		}
	}



	function &getNode()
	{
		return $this->node;
	}


	/**
	* set xml content of page, start with <PageObject...>,
	* end with </PageObject>, comply with ILIAS DTD, omit MetaData, use utf-8!
	*
	* @param	string		$a_xml			xml content
	* @param	string		$a_encoding		encoding of the content (here is no conversion done!
	*										it should be already utf-8 encoded at the time)
	*/
	function setXMLContent($a_xml, $a_encoding = "UTF-8")
	{
		$this->encoding = $a_encoding;
		$this->xml = "<?xml version=\"1.0\" encoding=\"$a_encoding\" ?>".$a_xml;
	}

	/**
	* append xml content to page
	* setXMLContent must be called before and the same encoding must be used
	*
	* @param	string		$a_xml			xml content
	*/
	function appendXMLContent($a_xml)
	{
		$this->xml.= $a_xml;
	}


	/**
	* get xml content of page
	*/
	function getXMLContent()
	{
		return $this->xml;
	}

	/**
	* get xml content of page from dom
	* (use this, if any changes are made to the document)
	*/
	function getXMLFromDom()
	{
		$this->xml = $this->dom->dump_mem(0, "UTF-8");
		return $this->xml;
	}

	/**
	* Add hierarchical ID (e.g. for editing) attributes "HierId" to current dom tree.
	* This attribute will be added to the following elements:
	* PageObject, Paragraph, Table, TableRow, TableData.
	* Only elements of these types are counted as "childs" here.
	*
	* Hierarchical IDs have the format "x_y_z_...", e.g. "1_4_2" means: second
	* child of fourth child of first child of page.
	*
	* The PageObject element gets the special id "pg". The first child of the
	* page starts with id 1. The next child gets the 2 and so on.
	*
	* Another example: The first child of the page is a Paragraph -> id 1.
	* The second child is a table -> id 2. The first row gets the id 2_1, the
	*/
	function addHierIDs()
	{

		// set hierarchical ids for Paragraphs, Tables, TableRows and TableData elements
		$xpc = xpath_new_context($this->dom);
		$path = "//Paragraph | //Table | //TableRow | //TableData";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$cnode = $res->nodeset[$i];
			// get hierarchical id of previous sibling
			$sib_hier_id = "";
			while($cnode =& $cnode->previous_sibling())
			{
				if (($cnode->node_type() == XML_ELEMENT_NODE)
					&& $cnode->has_attribute("HierId"))
				{
					$sib_hier_id = $cnode->get_attribute("HierId");
					//$sib_hier_id = $id_attr->value();
					break;
				}
			}

			if ($sib_hier_id != "")		// set id to sibling id "+ 1"
			{
				$node_hier_id = ilPageContent::incEdId($sib_hier_id);
				$res->nodeset[$i]->set_attribute("HierId", $node_hier_id);
			}
			else						// no sibling -> node is first child
			{
				// get hierarchical id of next parent
				$cnode =& $res->nodeset[$i];
				$par_hier_id = "";
				while($cnode =& $cnode->parent_node())
				{
					if (($cnode->node_type() == XML_ELEMENT_NODE)
						&& $cnode->has_attribute("HierId"))
					{
						$par_hier_id = $cnode->get_attribute("HierId");
						//$par_hier_id = $id_attr->value();
						break;
					}
				}

				if (($par_hier_id != "") && ($par_hier_id != "pg"))		// set id to parent_id."_1"
				{
					$node_hier_id = $par_hier_id."_1";
					$res->nodeset[$i]->set_attribute("HierId", $node_hier_id);
				}
				else		// no sibling, no parent -> first node
				{
					$node_hier_id = "1";
					$res->nodeset[$i]->set_attribute("HierId", $node_hier_id);
				}
			}
		}

		// set special hierarchical id "pg" for pageobject
		$xpc = xpath_new_context($this->dom);
		$path = "//PageObject";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)	// should only be 1
		{
			$res->nodeset[$i]->set_attribute("HierId", "pg");
		}
	}


	/**
	* create new page object with current xml content
	*/
	function createFromXML()
	{
		// create object
		parent::create();
		$query = "INSERT INTO lm_page_object (page_id, lm_id, content) VALUES ".
			"('".$this->getId()."', '".$this->getLMId()."','".$this->getXMLContent()."')";
		$this->ilias->db->query($query);
//echo "created page:".htmlentities($this->getXMLContent())."<br>";
	}

	/**
	* update complete page content in db (dom xml content is used)
	*/
	function update()
	{
		parent::update();
		$query = "UPDATE lm_page_object ".
			"SET content = '".$this->getXMLFromDom()."' ".
			"WHERE page_id = '".$this->getId()."'";
		$this->ilias->db->query($query);
//echo "<br>PageObject::update:".htmlentities($this->getXMLContent()).":";
	}

	/**
	* delete content object with hierarchical id $a_hid
	*
	* @param	string		$a_hid		hierarchical id of content object
	*/
	function deleteContent($a_hid)
	{
		$curr_node =& $this->getContentNode($a_hid);
		$curr_node->unlink_node($curr_node);
		$this->update();
	}


	/**
	* insert a content node before/after a sibling or as first child of a parent
	*/
	function insertContent(&$a_cont_obj, $a_pos, $a_mode = IL_INSERT_AFTER)
	{
		// move mode into container elements is always INSERT_CHILD
//echo "get node at $a_pos";
		$curr_node =& $this->getContentNode($a_pos);
		$curr_name = $curr_node->node_name();
		if (($curr_name == "TableData") || ($curr_name == "PageObject"))
		{
			$a_mode = IL_INSERT_CHILD;
		}


		if($a_mode != IL_INSERT_CHILD)			// determine parent hierarchical id
		{										// of sibling at $a_pos
			$pos = explode("_", $a_pos);
			$target_pos = array_pop($pos);
			$parent_pos = implode($pos, "_");
		}
		else		// if we should insert a child, $a_pos is alreade the hierarchical id
		{			// of the parent node
			$parent_pos = $a_pos;
		}

		// get the parent node
		if($parent_pos != "")
		{
			$parent_node =& $this->getContentNode($parent_pos);
		}
		else
		{
			$parent_node =& $this->getNode();
		}

		// count the parent children
		$parent_childs =& $parent_node->child_nodes();
		$cnt_parent_childs = count($parent_childs);

		switch ($a_mode)
		{
			// insert new node after sibling at $a_pos
			case IL_INSERT_AFTER:
				$new_node =& $a_cont_obj->getNode();
				//$a_pos = ilPageContent::incEdId($a_pos);
				//$curr_node =& $this->getContentNode($a_pos);
//echo "behind $a_pos:";
				if($succ_node =& $curr_node->next_sibling())
				{
					$new_node =& $succ_node->insert_before($new_node, $succ_node);
				}
				else
				{
//echo "movin doin append_child";
					$new_node =& $parent_node->append_child($new_node);
				}
				$a_cont_obj->setNode($new_node);
				break;

			case IL_INSERT_BEFORE:
				$new_node =& $a_cont_obj->getNode();
				$succ_node =& $this->getContentNode($a_pos);
				$new_node =& $succ_node->insert_before($new_node, $succ_node);
				$a_cont_obj->setNode($new_node);
				break;

			// insert new node as first child of parent $a_pos (= $a_parent)
			case IL_INSERT_CHILD:
//echo "insert as child:parent_childs:$cnt_parent_childs:<br>";
				$new_node =& $a_cont_obj->getNode();
				if($cnt_parent_childs == 0)
				{
					$new_node =& $parent_node->append_child($new_node);
				}
				else
				{
					$new_node =& $parent_childs[0]->insert_before($new_node, $parent_childs[0]);
				}
				$a_cont_obj->setNode($new_node);
				break;
		}

	}

	/**
	* static
	*/
	function getPageList($lm_id)
	{
		return ilLMObject::getObjectList($lm_id, "pg");
	}


	/**
	* move content object from position $a_source before position $a_target
	* (both hierarchical content ids)
	*/
	function moveContentBefore($a_source, $a_target)
	{
		if($a_source == $a_target)
		{
			return;
		}

		// determine move mode
		/*
		$target_node =& $this->getContentNode($a_target);
		if ($target_node->node_name() == "TableData")
		{
			$mode = IL_INSERT_CHILD;
		}
		else
		{
			$mode = IL_INSERT_BEFORE;
		}*/

		// clone the node
		$content =& $this->getContentObject($a_source);
		$source_node =& $content->getNode();
		$clone_node =& $source_node->clone_node(true);

		// delete source node
		$this->deleteContent($a_source);

		// insert cloned node at target
		$content->setNode($clone_node);
		$this->insertContent($content, $a_target, IL_INSERT_BEFORE);
		$this->update();

	}

	/**
	* move content object from position $a_source before position $a_target
	* (both hierarchical content ids)
	*/
	function moveContentAfter($a_source, $a_target)
	{
//echo "source:$a_source:target:$a_target:<br>";
		if($a_source == $a_target)
		{
			return;
		}

//echo "move source:$a_source:to:$a_target:<br>";

		// determine move mode
		/*
		$target_node =& $this->getContentNode($a_target);
		if ($target_node->node_name() == "TableData")
		{
			$mode = IL_INSERT_CHILD;
		}
		else
		{
			$mode = IL_INSERT_AFTER;
		}*/

		// clone the node
		$content =& $this->getContentObject($a_source);
		$source_node =& $content->getNode();
		$clone_node =& $source_node->clone_node(true);

		// delete source node
		$this->deleteContent($a_source);

		// insert cloned node at target
		$content->setNode($clone_node);
		$this->insertContent($content, $a_target, IL_INSERT_AFTER);
		$this->update();
	}

}
?>
