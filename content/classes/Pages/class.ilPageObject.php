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

//require_once("content/classes/class.ilLMObject.php");
require_once("content/classes/Pages/class.ilPageContent.php");
require_once("content/classes/Pages/class.ilPCParagraph.php");

define("IL_INSERT_BEFORE", 0);
define("IL_INSERT_AFTER", 1);
define("IL_INSERT_CHILD", 2);

define ("IL_CHAPTER_TITLE", "st_title");
define ("IL_PAGE_TITLE", "pg_title");
define ("IL_NO_HEADER", "none");

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
class ilPageObject
{
	var $id;
	var $ilias;
	var $dom;
	var $xml;
	var $encoding;
	var $node;
	var $cur_dtd = "ilias_pg_0_1.dtd";
	var $contains_int_link;
	var $parent_type;
	var $parent_id;
	var $update_listeners;
	var $update_listener_cnt;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageObject($a_parent_type, $a_id = 0)
	{
		global $ilias;

		$this->parent_type = $a_parent_type;
		$this->id = $a_id;
		$this->ilias =& $ilias;

		$this->contains_int_link = false;
		$this->update_listeners = array();
		$this->update_listener_cnt = 0;

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
		$query = "SELECT * FROM page_object WHERE page_id = '".$this->id."' ".
			"AND parent_type='".$this->getParentType()."'";
		$pg_set = $this->ilias->db->query($query);
		if (!($this->page_record = $pg_set->fetchRow(DB_FETCHMODE_ASSOC)))
		{
			echo "Error: Page ".$this->id." is not in database."; exit;
		}

		// todo: make utf8 global (db content should be already utf8)
		$this->xml = $this->page_record["content"];

	}

	function buildDom()
	{
//echo ":xml:".htmlentities($this->getXMLContent(true)).":";
		$this->dom = domxml_open_mem($this->getXMLContent(true), DOMXML_LOAD_VALIDATING, $error);

		$xpc = xpath_new_context($this->dom);
		$path = "//PageObject";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$this->node =& $res->nodeset[0];
		}

		if (empty($error))
		{
			return true;
		}
		else
		{
			return $error;
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

	function setParentId($a_id)
	{
		$this->parent_id = $a_id;
	}

	function getParentId()
	{
		return $this->parent_id;
	}

	function setParentType($a_type)
	{
		$this->parent_type = $a_type;
	}

	function getParentType()
	{
		return $this->parent_type;
	}

	function addUpdateListener(&$a_object, $a_method, $a_parameters = "")
	{
		$cnt = $this->update_listener_cnt;
		$this->update_listeners[$cnt]["object"] =& $a_object;
		$this->update_listeners[$cnt]["method"] = $a_method;
		$this->update_listeners[$cnt]["parameters"] = $a_parameters;
		$this->update_listener_cnt++;
	}

	function callUpdateListeners()
	{
		for($i=0; $i<$this->update_listener_cnt; $i++)
		{
			$object =& $this->update_listeners[$i]["object"];
			$method = $this->update_listeners[$i]["method"];
			$parameters = $this->update_listeners[$i]["parameters"];
			$object->$method($parameters);
		}
	}

	function &getContentObject($a_hier_id)
	{
//echo "Content:".htmlentities($this->getXMLFromDOM()).":<br>";
//echo "ilPageObject::getContentObject:hierid:".$a_hier_id.":<br>";
		$cont_node =& $this->getContentNode($a_hier_id);
//echo "ilPageObject::getContentObject:nodename:".$cont_node->node_name().":<br>";
		switch($cont_node->node_name())
		{
			case "PageContent":
				$child_node =& $cont_node->first_child();
				switch($child_node->node_name())
				{
					case "Paragraph":
						require_once("content/classes/Pages/class.ilPCParagraph.php");
						$par =& new ilPCParagraph($this->dom);
						$par->setNode($cont_node);
						$par->setHierId($a_hier_id);
						return $par;

					case "Table":
						require_once("content/classes/Pages/class.ilPCTable.php");
						$tab =& new ilPCTable($this->dom);
						$tab->setNode($cont_node);
						$tab->setHierId($a_hier_id);
						return $tab;

					case "MediaObject":
						require_once("content/classes/Pages/class.ilMediaObject.php");
						$mal_node =& $child_node->first_child();
						$mob =& new ilMediaObject($mal_node->get_attribute("OriginId"));
						$mob->setDom($this->dom);
						$mob->setNode($cont_node);
						$mob->setHierId($a_hier_id);
						return $mob;

					case "List":
						require_once("content/classes/Pages/class.ilPCList.php");
						$list =& new ilPCList($this->dom);
						$list->setNode($cont_node);
						$list->setHierId($a_hier_id);
						return $list;

					case "FileList":
						require_once("content/classes/Pages/class.ilPCFileList.php");
						$file_list =& new ilPCFileList($this->dom);
						$file_list->setNode($cont_node);
						$file_list->setHierId($a_hier_id);
						return $file_list;
				}
				break;

			case "TableData":
				require_once("content/classes/Pages/class.ilPCTableData.php");
				$td =& new ilPCTableData($this->dom);
				$td->setNode($cont_node);
				$td->setHierId($a_hier_id);
				return $td;

			case "ListItem":
				require_once("content/classes/Pages/class.ilPCListItem.php");
				$td =& new ilPCListItem($this->dom);
				$td->setNode($cont_node);
				$td->setHierId($a_hier_id);
				return $td;

			case "FileItem":
				require_once("content/classes/Pages/class.ilPCFileItem.php");
				$file_item =& new ilPCFileItem($this->dom);
				$file_item->setNode($cont_node);
				$file_item->setHierId($a_hier_id);
				return $file_item;

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
//echo "count:".count($res->nodeset).":hierid:$a_hier_id:";
		if (count($res->nodeset) == 1)
		{
			$cont_node =& $res->nodeset[0];
			return $cont_node;
		}
	}

	// only for test purposes
	function lookforhier($a_hier_id)
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//*[@HierId = '$a_hier_id']";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
			return "YES";
		else
			return "NO";
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
	*										it must be already utf-8 encoded at the time)
	*/
	function setXMLContent($a_xml, $a_encoding = "UTF-8")
	{
		$this->encoding = $a_encoding;
		$this->xml = $a_xml;
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
	function getXMLContent($a_incl_head = false)
	{
		// build full http path for XML DOCTYPE header.
		// Under windows a relative path doesn't work :-(
		// unfortunately this fix doesn't work also.
		// the class is used in scripts outside the content
		// module as well.
		/*
		$split = preg_split("/\/content\//",$_SERVER["PHP_SELF"]);
		$http_path = "http://".$_SERVER["HTTP_HOST"].$split[0]."/xml/";*/

		if($a_incl_head)
		{
			$enc_str = (!empty($this->encoding))
				? "encoding=\"".$this->encoding."\""
				: "";
			return "<?xml version=\"1.0\" $ecn_str ?>".
				"<!DOCTYPE PageObject SYSTEM \"".ILIAS_HTTP_PATH."/xml/".$this->cur_dtd."\">".
				$this->xml;
		}
		else
		{
			return $this->xml;
		}
	}

	/**
	* get xml content of page from dom
	* (use this, if any changes are made to the document)
	*/
	function getXMLFromDom($a_incl_head = false, $a_append_mobs = false, $a_append_bib = false, $a_append_str = "")
	{
		if ($a_incl_head)
		{
			return $this->dom->dump_mem(0, $this->encoding);
		}
		else
		{
			// append multimedia object elements
			if ($a_append_mobs || $a_append_bib)
			{
				$mobs = "";
				$bibs = "";
				if ($a_append_mobs)
				{
					$mobs =& $this->getMultimediaXML();
				}
				if ($a_append_bib)
				{
					$bibs =& $this->getBibliographyXML();
				}
				return "<dummy>".$this->dom->dump_node($this->node).$mobs.$bibs.$a_append_str."</dummy>";
			}
			else
			{
				if (is_object($this->dom))
				{
					return $this->dom->dump_node($this->node);
				}
				else
				{
					return "";
				}
			}
		}
	}


	function getFirstParagraphText()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//Paragraph[1]";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) > 0)
		{
			$cont_node =& $res->nodeset[0]->parent_node();
			$par =& new ilPCParagraph($this->dom);
			$par->setNode($cont_node);
			return $par->getText();
		}
		else
		{
			return "";
		}
	}


	/**
	* lm parser set this flag to true, if the page contains intern links
	* (this method should only be called by the import parser)
	*
	* todo: move to ilLMPageObject !?
	*
	* @param	boolean		$a_contains_link		true, if page contains intern link tag(s)
	*/
	function setContainsIntLink($a_contains_link)
	{
		$this->contains_int_link = $a_contains_link;
	}

	/**
	* returns true, if page was marked as containing an intern link (via setContainsIntLink)
	* (this method should only be called by the import parser)
	*/
	function containsIntLink()
	{
		return $this->contains_int_link;
	}

	/**
	* get a xml string that contains all Bibliography elements, that
	* are referenced by any bibitem alias in the page
	*/
    function getBibliographyXML()
	{
        global $ilias;

		// todo: access to $_GET and $_POST variables is not
		// allowed in non GUI classes!
		//
		// access to db table object_reference is not allowed here!
        $r = $ilias->db->query("SELECT * FROM object_reference WHERE ref_id='".$_GET["ref_id"]."' ");
        $row = $r->fetchRow(DB_FETCHMODE_ASSOC);

        include_once("./classes/class.ilNestedSetXML.php");
        $nested = new ilNestedSetXML();
        $bibs_xml = $nested->export($row["obj_id"], "bib");

        return $bibs_xml;
    }

	/**
	* get a xml string that contains all media object elements, that
	* are referenced by any media alias in the page
	*/
	function getMultimediaXML()
	{
//echo htmlentities($this->getXMLFromDom());
		// determine all media aliases of the page
		$xpc = xpath_new_context($this->dom);
		$path = "//MediaObject/MediaAlias";
		$res =& xpath_eval($xpc, $path);
		$mob_ids = array();
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$mob_ids[$res->nodeset[$i]->get_attribute("OriginId")] = true;
		}

		// get xml of corresponding media objects
		$mobs_xml = "";
		require_once("content/classes/Pages/class.ilMediaObject.php");
		foreach($mob_ids as $mob_id => $dummy)
		{
			$mob_obj =& new ilMediaObject($mob_id);
			$mobs_xml .= $mob_obj->getXML(IL_MODE_OUTPUT);
		}
		return $mobs_xml;
	}

	/**
	* get complete media object (alias) element
	*/
	function getMediaAliasElement($a_mob_id, $a_nr = 1)
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//MediaObject/MediaAlias[@OriginId='$a_mob_id']";
		$res =& xpath_eval($xpc, $path);
		$mal_node =& $res->nodeset[$a_nr - 1];
		$mob_node =& $mal_node->parent_node();

		return $this->dom->dump_node($mob_node);
	}

	function validateDom()
	{
//echo "<br>PageObject::update:".htmlentities($this->getXMLContent()).":"; exit;
		$this->stripHierIDs();
		@$this->dom->validate($error);
		return $error;
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
		//$path = "//Paragraph | //Table | //TableRow | //TableData";
		$path = "//PageContent | //TableRow | //TableData | //ListItem | //FileItem";
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
		unset($xpc);
	}

	function stripHierIDs()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//*[@HierId]";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)	// should only be 1
		{
			if ($res->nodeset[$i]->has_attribute("HierId"))
			{
				$res->nodeset[$i]->remove_attribute("HierId");
			}
		}
		unset($xpc);
	}

	/**
	* converts all internal link targets of the page
	*
	* @param	array		$a_mapping		mapping array (keys: old targets, values: new targets)
	*/
	function mapIntLinks(&$a_mapping)
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//IntLink";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$target = $res->nodeset[$i]->get_attribute("Target");
			$type = $res->nodeset[$i]->get_attribute("Type");
			switch($type)
			{
				case "PageObject":
					if(!empty($a_mapping[$target]))
					{
						$res->nodeset[$i]->set_attribute("Target", $a_mapping[$target]);
					}
					break;
			}
		}
		unset($xpc);
	}

	/**
	* create new page object with current xml content
	*/
	function createFromXML()
	{
		if($this->getXMLContent() == "")
		{
			$this->setXMLContent("<PageObject></PageObject>");
		}
		// create object
		$query = "INSERT INTO page_object (page_id, parent_id, content, parent_type) VALUES ".
			"('".$this->getId()."', '".$this->getParentId()."','".addslashes($this->getXMLContent()).
			"', '".$this->getParentType()."')";
		$this->ilias->db->query($query);
//echo "created page:".htmlentities($this->getXMLContent())."<br>";
	}

	/**
	* updates page object with current xml content
	*/
	function updateFromXML()
	{
		$query = "UPDATE page_object ".
			"SET content = '".addslashes($this->getXMLContent())."' ".
			"WHERE page_id = '".$this->getId()."' AND parent_type='".$this->getParentType()."'";
		$this->ilias->db->query($query);
	}

	/**
	* update complete page content in db (dom xml content is used)
	*/
	function update($a_validate = true)
	{
//echo "<br>PageObject::update:".htmlentities($this->getXMLFromDom()).":"; exit;
		// test validating
		if($a_validate)
		{
			$errors = $this->validateDom();
		}
		if(empty($errors))
		{
			$query = "UPDATE page_object ".
				"SET content = '".addslashes($this->getXMLFromDom())."' ".
				"WHERE page_id = '".$this->getId().
				"' AND parent_type='".$this->getParentType()."'";
			$this->ilias->db->query($query);
			$this->callUpdateListeners();
//echo "<br>PageObject::update:".htmlentities($this->getXMLContent()).":";
			return true;
		}
		else
		{
			return $errors;
		}
	}

	function delete()
	{
		$query = "DELETE FROM page_object ".
			"WHERE page_id = '".$this->getId().
			"' AND parent_type='".$this->getParentType()."'";
		$this->ilias->db->query($query);
	}

	function create()
	{
		$this->createFromXML();
		/*
		$this->setXMLContent("<PageObject></PageObject>");
		$query = "INSERT INTO page_object (page_id, parent_id, content, parent_type) VALUES ".
			"('".$this->getId()."', '".$this->getParentId()."','".$this->getXMLContent().
			"', '".$this->getParentType()."')";
		$this->ilias->db->query($query);*/
//echo "created page:".htmlentities($this->getXMLContent())."<br>";

	}

	/**
	* delete content object with hierarchical id $a_hid
	*
	* @param	string		$a_hid		hierarchical id of content object
	* @param	boolean		$a_update	update page in db (note: update deletes all
	*									hierarchical ids in DOM!)
	*/
	function deleteContent($a_hid, $a_update = true)
	{
		$curr_node =& $this->getContentNode($a_hid);
		$curr_node->unlink_node($curr_node);
		if ($a_update)
		{
			return $this->update();
		}
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
		if (($curr_name == "TableData") || ($curr_name == "PageObject") ||
			($curr_name == "ListItem"))
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
	* move content object from position $a_source before position $a_target
	* (both hierarchical content ids)
	*/
	function moveContentBefore($a_source, $a_target)
	{
		if($a_source == $a_target)
		{
			return;
		}

		// clone the node
		$content =& $this->getContentObject($a_source);
		$source_node =& $content->getNode();
		$clone_node =& $source_node->clone_node(true);

		// delete source node
		$this->deleteContent($a_source, false);

		// insert cloned node at target
		$content->setNode($clone_node);
		$this->insertContent($content, $a_target, IL_INSERT_BEFORE);
		return $this->update();

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


		// clone the node
		$content =& $this->getContentObject($a_source);
		$source_node =& $content->getNode();
		$clone_node =& $source_node->clone_node(true);

		// delete source node
		$this->deleteContent($a_source, false);

		// insert cloned node at target
		$content->setNode($clone_node);
		$this->insertContent($content, $a_target, IL_INSERT_AFTER);
		return $this->update();
	}

	/**
	* transforms bbCode to corresponding xml
	*/
	function bbCode2XML(&$a_content)
	{
		$a_content = eregi_replace("\[com\]","<Comment>",$a_content);
		$a_content = eregi_replace("\[\/com\]","</Comment>",$a_content);
		$a_content = eregi_replace("\[emp]","<Emph>",$a_content);
		$a_content = eregi_replace("\[\/emp\]","</Emph>",$a_content);
		$a_content = eregi_replace("\[str]","<Strong>",$a_content);
		$a_content = eregi_replace("\[\/str\]","</Strong>",$a_content);
	}


	/**
	* presentation title doesn't have to be page title, it may be
	* chapter title + page title or chapter title only, depending on settings
	*
	* @param	string	$a_mode		IL_CHAPTER_TITLE | IL_PAGE_TITLE | IL_NO_HEADER
	*/
	// this method is moved to class ilLMPageObject
	/*
	function getPresentationTitle($a_mode = IL_CHAPTER_TITLE)
	{
		if($a_mode == IL_NO_HEADER)
		{
			return "";
		}

		if($a_mode == IL_PAGE_TITLE)
		{
			return $this->getTitle();
		}

		$tree = new ilTree($this->getLMId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");
		if ($tree->isInTree($this->getId()))
		{
			$pred_node = $tree->fetchPredecessorNode($this->getId(), "st");
			return $pred_node["title"];
		}
		else
		{
			return $this->getTitle();
		}
	}*/

}
?>
