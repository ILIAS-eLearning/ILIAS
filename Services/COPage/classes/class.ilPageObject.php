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
require_once("./Services/COPage/classes/class.ilPCParagraph.php");
require_once("./Services/COPage/syntax_highlight/php/Beautifier/Init.php");
require_once("./Services/COPage/syntax_highlight/php/Output/Output_css.php");


define("IL_INSERT_BEFORE", 0);
define("IL_INSERT_AFTER", 1);
define("IL_INSERT_CHILD", 2);

define ("IL_CHAPTER_TITLE", "st_title");
define ("IL_PAGE_TITLE", "pg_title");
define ("IL_NO_HEADER", "none");

/** @defgroup ServicesCOPage Services/COPage
 */

/**
* Class ilPageObject
*
* Handles PageObjects of ILIAS Learning Modules (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPageObject
{
	var $id;
	var $ilias;
	var $dom;
	var $xml;
	var $encoding;
	var $node;
	var $cur_dtd = "ilias_pg_3_10.dtd";
	var $contains_int_link;
	var $needs_parsing;
	var $parent_type;
	var $parent_id;
	var $update_listeners;
	var $update_listener_cnt;
	var $offline_handler;
	var $dom_builded;
	var $history_saved;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageObject($a_parent_type, $a_id = 0, $a_old_nr = 0, $a_halt = true)
	{
		global $ilias;

		$this->parent_type = $a_parent_type;
		$this->id = $a_id;
		$this->ilias =& $ilias;

		$this->contains_int_link = false;
		$this->needs_parsing = false;
		$this->update_listeners = array();
		$this->update_listener_cnt = 0;
		$this->dom_builded = false;
		$this->halt_on_error = $a_halt;
		$this->page_not_found = false;
		$this->old_nr = $a_old_nr;
		$this->encoding = "UTF-8";
		$this->id_elements =
			array("PageContent", "TableRow", "TableData", "ListItem", "FileItem",
				"Section", "Tab");
		
		if($a_id != 0)
		{
			$this->read();
		}
	}

	function haltOnError($a_halt)
	{
		$this->halt_on_error = $a_halt;
	}

		/**
	* Set Render MD5.
	*
	* @param	string	$a_rendermd5	Render MD5
	*/
	function setRenderMd5($a_rendermd5)
	{
		$this->rendermd5 = $a_rendermd5;
	}

	/**
	* Get Render MD5.
	*
	* @return	string	Render MD5
	*/
	function getRenderMd5()
	{
		return $this->rendermd5;
	}

	/**
	* Set Rendered Content.
	*
	* @param	string	$a_renderedcontent	Rendered Content
	*/
	function setRenderedContent($a_renderedcontent)
	{
		$this->renderedcontent = $a_renderedcontent;
	}

	/**
	* Get Rendered Content.
	*
	* @return	string	Rendered Content
	*/
	function getRenderedContent()
	{
		return $this->renderedcontent;
	}

	/**
	* Set Rendered Time.
	*
	* @param	string	$a_renderedtime	Rendered Time
	*/
	function setRenderedTime($a_renderedtime)
	{
		$this->renderedtime = $a_renderedtime;
	}

	/**
	* Get Rendered Time.
	*
	* @return	string	Rendered Time
	*/
	function getRenderedTime()
	{
		return $this->renderedtime;
	}

	/**
	* Set Last Change.
	*
	* @param	string	$a_lastchange	Last Change
	*/
	function setLastChange($a_lastchange)
	{
		$this->lastchange = $a_lastchange;
	}

	/**
	* Get Last Change.
	*
	* @return	string	Last Change
	*/
	function getLastChange()
	{
		return $this->lastchange;
	}

	/**
	* read page data
	*/
	function read()
	{
		global $ilBench, $ilDB;

		$ilBench->start("ContentPresentation", "ilPageObject_read");
		if ($this->old_nr == 0)
		{
			$query = "SELECT * FROM page_object WHERE page_id = ".$ilDB->quote($this->id)." ".
				"AND parent_type=".$ilDB->quote($this->getParentType());
			$pg_set = $this->ilias->db->query($query);
			$this->page_record = $pg_set->fetchRow(DB_FETCHMODE_ASSOC);
		}
		else
		{
			$query = "SELECT * FROM page_history WHERE page_id = ".$ilDB->quote($this->id)." ".
				"AND parent_type=".$ilDB->quote($this->getParentType()).
				" AND nr = ".$ilDB->quote($this->old_nr);
			$pg_set = $this->ilias->db->query($query);
			$this->page_record = $pg_set->fetchRow(DB_FETCHMODE_ASSOC);
		}
		if (!$this->page_record)
		{
			if ($this->halt_on_error)
			{
				echo "Error: Page ".$this->id." is not in database".
					" (parent type ".$this->getParentType().")."; exit;
			}
			else
			{
				$this->page_not_found = true;
				return;
			}
		}
		$this->xml = $this->page_record["content"];
		$this->setParentId($this->page_record["parent_id"]);
		$this->user = $this->page_record["user"];
		$this->setRenderedContent($this->page_record["rendered_content"]);
		$this->setRenderMd5($this->page_record["render_md5"]);
		$this->setRenderedTime($this->page_record["rendered_time"]);
		$this->setLastChange($this->page_record["last_change"]);

		$ilBench->stop("ContentPresentation", "ilPageObject_read");
	}
	
	/**
	* checks whether page exists
	*
	* @param	string		$a_parent_type	parent type
	* @param	int			$a_id			page id
	*/
	function _exists($a_parent_type, $a_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM page_object WHERE page_id = ".$ilDB->quote($a_id)." ".
			"AND parent_type= ".$ilDB->quote($a_parent_type);

		$set = $ilDB->query($query);
		if ($row = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	function buildDom()
	{
		global $ilBench;

		if ($this->dom_builded)
		{
			return;
		}

//echo "\n<br>buildDomWith:".$this->getId().":xml:".$this->getXMLContent(true).":<br>";

		$ilBench->start("ContentPresentation", "ilPageObject_buildDom");
		$this->dom = @domxml_open_mem($this->getXMLContent(true), DOMXML_LOAD_VALIDATING, $error);
		$ilBench->stop("ContentPresentation", "ilPageObject_buildDom");

		$xpc = xpath_new_context($this->dom);
		$path = "//PageObject";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			$this->node =& $res->nodeset[0];
		}

		if (empty($error))
		{
			$this->dom_builded = true;
			return true;
		}
		else
		{
			return $error;
		}
	}

	function freeDom()
	{
		//$this->dom->free();
		unset($this->dom);
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

	function &getContentObject($a_hier_id, $a_pc_id = "")
	{
//echo ":".$a_hier_id.":";
//echo "Content:".htmlentities($this->getXMLFromDOM()).":<br>";
//echo "ilPageObject::getContentObject:hierid:".$a_hier_id.":<br>";
		$cont_node =& $this->getContentNode($a_hier_id, $a_pc_id);
//echo "ilPageObject::getContentObject:nodename:".$cont_node->node_name().":<br>";
		if (!is_object($cont_node))
		{
			return false;
		}
		switch($cont_node->node_name())
		{
			case "PageContent":
				$child_node =& $cont_node->first_child();
//echo "<br>nodename:".$child_node->node_name();
				switch($child_node->node_name())
				{
					case "Paragraph":
						require_once("./Services/COPage/classes/class.ilPCParagraph.php");
						$par =& new ilPCParagraph($this->dom);
						$par->setNode($cont_node);
						$par->setHierId($a_hier_id);
						$par->setPcId($a_pc_id);
						return $par;

					case "Table":
						if ($child_node->get_attribute("DataTable") == "y")
						{
							require_once("./Services/COPage/classes/class.ilPCDataTable.php");
							$tab =& new ilPCDataTable($this->dom);
							$tab->setNode($cont_node);
							$tab->setHierId($a_hier_id);
						}
						else
						{
							require_once("./Services/COPage/classes/class.ilPCTable.php");
							$tab =& new ilPCTable($this->dom);
							$tab->setNode($cont_node);
							$tab->setHierId($a_hier_id);
						}
						$tab->setPcId($a_pc_id);
						return $tab;

					case "MediaObject":
if ($_GET["pgEdMediaMode"] != "") {echo "ilPageObject::error media"; exit;}

						//require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
						require_once("./Services/COPage/classes/class.ilPCMediaObject.php");
						
						$mal_node =& $child_node->first_child();
//echo "ilPageObject::getContentObject:nodename:".$mal_node->node_name().":<br>";
						$id_arr = explode("_", $mal_node->get_attribute("OriginId"));
						$mob_id = $id_arr[count($id_arr) - 1];
						
						// allow deletion of non-existing media objects
						if (!ilObject::_exists($mob_id) && in_array("delete", $_POST))
						{
							$mob_id = 0;
						}

						//$mob =& new ilObjMediaObject($mob_id);
						$mob = new ilPCMediaObject($this->dom);
						$mob->readMediaObject($mob_id);
						
						//$mob->setDom($this->dom);
						$mob->setNode($cont_node);
						$mob->setHierId($a_hier_id);
						$mob->setPcId($a_pc_id);
						return $mob;

					case "List":
						require_once("./Services/COPage/classes/class.ilPCList.php");
						$list = new ilPCList($this->dom);
						$list->setNode($cont_node);
						$list->setHierId($a_hier_id);
						$list->setPcId($a_pc_id);
						return $list;

					case "FileList":
						require_once("./Services/COPage/classes/class.ilPCFileList.php");
						$file_list = new ilPCFileList($this->dom);
						$file_list->setNode($cont_node);
						$file_list->setHierId($a_hier_id);
						$file_list->setPcId($a_pc_id);
						return $file_list;

					// note: assessment handling is forwarded to assessment gui classes
					case "Question":
						require_once("./Services/COPage/classes/class.ilPCQuestion.php");
						$pc_question = new ilPCQuestion($this->dom);
						$pc_question->setNode($cont_node);
						$pc_question->setHierId($a_hier_id);
						$pc_question->setPcId($a_pc_id);
						return $pc_question;

					case "Section":
						require_once("./Services/COPage/classes/class.ilPCSection.php");
						$sec = new ilPCSection($this->dom);
						$sec->setNode($cont_node);
						$sec->setHierId($a_hier_id);
						$sec->setPcId($a_pc_id);
						return $sec;
						
					case "Resources":
						require_once("./Services/COPage/classes/class.ilPCResources.php");
						$res = new ilPCResources($this->dom);
						$res->setNode($cont_node);
						$res->setHierId($a_hier_id);
						$res->setPcId($a_pc_id);
						return $res;
						
					case "Map":
						require_once("./Services/COPage/classes/class.ilPCMap.php");
						$map = new ilPCMap($this->dom);
						$map->setNode($cont_node);
						$map->setHierId($a_hier_id);
						$map->setPcId($a_pc_id);
						return $map;

					case "Tabs":
						require_once("./Services/COPage/classes/class.ilPCTabs.php");
						$map = new ilPCTabs($this->dom);
						$map->setNode($cont_node);
						$map->setHierId($a_hier_id);
						$map->setPcId($a_pc_id);
						return $map;

					case "Plugged":
						require_once("./Services/COPage/classes/class.ilPCPlugged.php");
						$plugged = new ilPCPlugged($this->dom);
						$plugged->setNode($cont_node);
						$plugged->setHierId($a_hier_id);
						$plugged->setPcId($a_pc_id);
						return $plugged;

				}
				break;

			case "TableData":
				require_once("./Services/COPage/classes/class.ilPCTableData.php");
				$td =& new ilPCTableData($this->dom);
				$td->setNode($cont_node);
				$td->setHierId($a_hier_id);
				return $td;

			case "ListItem":
				require_once("./Services/COPage/classes/class.ilPCListItem.php");
				$td =& new ilPCListItem($this->dom);
				$td->setNode($cont_node);
				$td->setHierId($a_hier_id);
				return $td;

			case "FileItem":
				require_once("./Services/COPage/classes/class.ilPCFileItem.php");
				$file_item =& new ilPCFileItem($this->dom);
				$file_item->setNode($cont_node);
				$file_item->setHierId($a_hier_id);
				return $file_item;

			case "Tab":
				require_once("./Services/COPage/classes/class.ilPCTab.php");
				$tab =& new ilPCTab($this->dom);
				$tab->setNode($cont_node);
				$tab->setHierId($a_hier_id);
				return $file_item;

		}
	}

	function &getContentNode($a_hier_id, $a_pc_id = "")
	{
		$xpc = xpath_new_context($this->dom);
		if($a_hier_id == "pg")
		{
			return $this->node;
		}
		else
		{
			// get per pc id
			if ($a_pc_id != "")
			{
				$path = "//*[@PCID = '$a_pc_id']";
				$res =& xpath_eval($xpc, $path);
				if (count($res->nodeset) == 1)
				{
					$cont_node =& $res->nodeset[0];
					return $cont_node;
				}
			}
			
			// fall back to hier id
			$path = "//*[@HierId = '$a_hier_id']";
			$res =& xpath_eval($xpc, $path);
			if (count($res->nodeset) == 1)
			{
				$cont_node =& $res->nodeset[0];
				return $cont_node;
			}
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
		if($a_incl_head)
		{
//echo "+".$this->encoding."+";
			$enc_str = (!empty($this->encoding))
				? "encoding=\"".$this->encoding."\""
				: "";
			return "<?xml version=\"1.0\" $enc_str ?>".
                "<!DOCTYPE PageObject SYSTEM \"".ILIAS_ABSOLUTE_PATH."/xml/".$this->cur_dtd."\">".
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
	function getXMLFromDom($a_incl_head = false, $a_append_mobs = false, $a_append_bib = false,
		$a_append_str = "", $a_omit_pageobject_tag = false)
	{
		if ($a_incl_head)
		{
//echo "\n<br>#".$this->encoding."#";
			return $this->dom->dump_mem(0, $this->encoding);
		}
		else
		{
			// append multimedia object elements
			if ($a_append_mobs || $a_append_bib || $a_append_link_info)
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
				$trans =& $this->getLanguageVariablesXML();
				return "<dummy>".$this->dom->dump_node($this->node).$mobs.$bibs.$trans.$a_append_str."</dummy>";
			}
			else
			{
				if (is_object($this->dom))
				{
					if ($a_omit_pageobject_tag)
					{
						$xml = "";
						$childs =& $this->node->child_nodes();
						for($i = 0; $i < count($childs); $i++)
						{
							$xml.= $this->dom->dump_node($childs[$i]);
						}
						return $xml;
					}
					else
					{
						$xml = $this->dom->dump_mem(0, $this->encoding);
						$xml = eregi_replace("<\?xml[^>]*>","",$xml);
						$xml = eregi_replace("<!DOCTYPE[^>]*>","",$xml);

						return $xml;

						// don't use dump_node. This gives always entities.
						//return $this->dom->dump_node($this->node);
					}
				}
				else
				{
					return "";
				}
			}
		}
	}

	/**
	* get language variables as XML
	*/
	function getLanguageVariablesXML()
	{
		global $lng;

		$xml = "<LVs>";
		$lang_vars = array("ed_insert_par", "ed_insert_code",
			"ed_insert_dtable", "ed_insert_atable", "ed_insert_media", "ed_insert_list",
			"ed_insert_filelist", "ed_paste_clip", "ed_edit", "ed_insert_section",
			"ed_edit_prop","ed_edit_data", "ed_delete", "ed_moveafter", "ed_movebefore",
			"ed_go", "ed_new_row_after", "ed_new_row_before",
			"ed_new_col_after", "ed_new_col_before", "ed_delete_col",
			"ed_delete_row", "ed_class", "ed_width", "ed_align_left",
			"ed_align_right", "ed_align_center", "ed_align_left_float",
			"ed_align_right_float", "ed_delete_item", "ed_new_item_before",
			"ed_new_item_after", "ed_copy_clip", "please_select", "ed_split_page",
			"ed_item_up", "ed_item_down", "ed_row_up", "ed_row_down",
			"ed_col_left", "ed_col_right", "ed_split_page_next","ed_enable",
			"de_activate", "ed_insert_repobj", "ed_insert_map", "ed_insert_tabs");

		foreach ($lang_vars as $lang_var)
		{
			$this->appendLangVarXML($xml, $lang_var);
		}

		$xml.= "</LVs>";

		return $xml;
	}

	function appendLangVarXML(&$xml, $var)
	{
		global $lng;

		$xml.= "<LV name=\"$var\" value=\"".$lng->txt("cont_".$var)."\"/>";
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
	* Set content of paragraph
	*
	* @param	string	$a_hier_id		Hier ID
	* @param	string	$a_content		Content
	*/
	function setParagraphContent($a_hier_id, $a_content)
	{
		$node = $this->getContentNode($a_hier_id);
		if (is_object($node))
		{
			$node->set_content($a_content);
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

	function needsImportParsing($a_parse = "")
	{

		if ($a_parse === true)
		{
			$this->needs_parsing = true;
		}
		if ($a_parse === false)
		{
			$this->needs_parsing = false;
		}
		return $this->needs_parsing;
	}

	/**
	* get a xml string that contains all Bibliography elements, that
	* are referenced by any bibitem alias in the page
	*/
    function getBibliographyXML()
	{
        global $ilias, $ilDB;

		// todo: access to $_GET and $_POST variables is not
		// allowed in non GUI classes!
		//
		// access to db table object_reference is not allowed here!
        $r = $ilias->db->query("SELECT * FROM object_reference WHERE ref_id=".
			$ilDB->quote($_GET["ref_id"]));
        $row = $r->fetchRow(DB_FETCHMODE_ASSOC);

        include_once("./classes/class.ilNestedSetXML.php");
        $nested = new ilNestedSetXML();
        $bibs_xml = $nested->export($row["obj_id"], "bib");

        return $bibs_xml;
    }


	/**
	* get all media objects, that are referenced and used within
	* the page
	*/
	function collectMediaObjects($a_inline_only = true)
	{
//echo htmlentities($this->getXMLFromDom());
		// determine all media aliases of the page
		$xpc = xpath_new_context($this->dom);
		$path = "//MediaObject/MediaAlias";
		$res =& xpath_eval($xpc, $path);
		$mob_ids = array();
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$id_arr = explode("_", $res->nodeset[$i]->get_attribute("OriginId"));
			$mob_id = $id_arr[count($id_arr) - 1];
			$mob_ids[$mob_id] = $mob_id;
		}

		// determine all inline internal media links
		$xpc = xpath_new_context($this->dom);
		$path = "//IntLink[@Type = 'MediaObject']";
		$res =& xpath_eval($xpc, $path);

		for($i = 0; $i < count($res->nodeset); $i++)
		{
			if (($res->nodeset[$i]->get_attribute("TargetFrame") == "") ||
				(!$a_inline_only))
			{
				$target = $res->nodeset[$i]->get_attribute("Target");
				$id_arr = explode("_", $target);
				if (($id_arr[1] == IL_INST_ID) ||
					(substr($target, 0, 4) == "il__"))
				{
					$mob_id = $id_arr[count($id_arr) - 1];
					if (ilObject::_exists($mob_id))
					{
						$mob_ids[$mob_id] = $mob_id;
					}
				}
			}
		}

		return $mob_ids;
	}


	/**
	* get all internal links that are used within the page
	*/
	function getInternalLinks()
	{
		// get all internal links of the page
		$xpc = xpath_new_context($this->dom);
		$path = "//IntLink";
		$res =& xpath_eval($xpc, $path);

		$links = array();
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$target = $res->nodeset[$i]->get_attribute("Target");
			$type = $res->nodeset[$i]->get_attribute("Type");
			$targetframe = $res->nodeset[$i]->get_attribute("TargetFrame");
			$links[$target.":".$type.":".$targetframe] =
				array("Target" => $target, "Type" => $type,
					"TargetFrame" => $targetframe);
					
			// get links (image map areas) for inline media objects
			if ($type == "MediaObject" && $targetframe == "")
			{
				if (substr($target, 0, 4) =="il__")
				{
					$id_arr = explode("_", $target);
					$id = $id_arr[count($id_arr) - 1];
	
					$med_links = ilMediaItem::_getMapAreasIntLinks($id);
					foreach($med_links as $key => $med_link)
					{
						$links[$key] = $med_link;
					}
				}
				
			}
//echo "<br>-:".$target.":".$type.":".$targetframe.":-";
		}
		unset($xpc);

		// get all media aliases
		$xpc = xpath_new_context($this->dom);
		$path = "//MediaAlias";
		$res =& xpath_eval($xpc, $path);

		require_once("Services/MediaObjects/classes/class.ilMediaItem.php");
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$oid = $res->nodeset[$i]->get_attribute("OriginId");
			if (substr($oid, 0, 4) =="il__")
			{
				$id_arr = explode("_", $oid);
				$id = $id_arr[count($id_arr) - 1];

				$med_links = ilMediaItem::_getMapAreasIntLinks($id);
				foreach($med_links as $key => $med_link)
				{
					$links[$key] = $med_link;
				}
			}
		}
		unset($xpc);

		return $links;
	}

	/**
	* get all file items that are used within the page
	*/
	function collectFileItems()
	{
//echo "<br>PageObject::collectFileItems[".$this->getId()."]";
		// determine all media aliases of the page
		$xpc = xpath_new_context($this->dom);
		$path = "//FileItem/Identifier";
		$res =& xpath_eval($xpc, $path);
		$file_ids = array();
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$id_arr = explode("_", $res->nodeset[$i]->get_attribute("Entry"));
			$file_id = $id_arr[count($id_arr) - 1];
			$file_ids[$file_id] = $file_id;
		}

		return $file_ids;
	}

	/**
	* get a xml string that contains all media object elements, that
	* are referenced by any media alias in the page
	*/
	function getMultimediaXML()
	{
		$mob_ids = $this->collectMediaObjects();

		// get xml of corresponding media objects
		$mobs_xml = "";
		require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		foreach($mob_ids as $mob_id => $dummy)
		{
			$mob_obj =& new ilObjMediaObject($mob_id);
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
		$path = "//MediaObject/MediaAlias[@OriginId='il__mob_$a_mob_id']";
		$res =& xpath_eval($xpc, $path);
		$mal_node =& $res->nodeset[$a_nr - 1];
		$mob_node =& $mal_node->parent_node();

		return $this->dom->dump_node($mob_node);
	}

	/**
	* Validate the page content agains page DTD
	*
	* @return	array		Error array.
	*/
	function validateDom()
	{
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
		$this->hier_ids = array();
		$this->first_row_ids = array();
		$this->first_col_ids = array();
		$this->list_item_ids = array();
		$this->file_item_ids = array();

		// set hierarchical ids for Paragraphs, Tables, TableRows and TableData elements
		$xpc = xpath_new_context($this->dom);
		//$path = "//Paragraph | //Table | //TableRow | //TableData";
		
		$sep = $path = "";
		foreach ($this->id_elements as $el)
		{
			$path.= $sep."//".$el;
			$sep = " | ";
		}

		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$cnode = $res->nodeset[$i];
			$ctag = $cnode->node_name();

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
				$this->hier_ids[] = $node_hier_id;
				if ($ctag == "TableData")
				{
					if (substr($par_hier_id,strlen($par_hier_id)-2) == "_1")
					{
						$this->first_row_ids[] = $node_hier_id;
					}
				}
				if ($ctag == "ListItem")
				{
					$this->list_item_ids[] = $node_hier_id;
				}
				if ($ctag == "FileItem")
				{
					$this->file_item_ids[] = $node_hier_id;
				}
			}
			else						// no sibling -> node is first child
			{
				// get hierarchical id of next parent
				$cnode = $res->nodeset[$i];
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
//echo "<br>par:".$par_hier_id." ($ctag)";
				if (($par_hier_id != "") && ($par_hier_id != "pg"))		// set id to parent_id."_1"
				{
					$node_hier_id = $par_hier_id."_1";
					$res->nodeset[$i]->set_attribute("HierId", $node_hier_id);
					$this->hier_ids[] = $node_hier_id;
					if ($ctag == "TableData")
					{
						$this->first_col_ids[] = $node_hier_id;
						if (substr($par_hier_id,strlen($par_hier_id)-2) == "_1")
						{
							$this->first_row_ids[] = $node_hier_id;
						}
					}
					if ($ctag == "ListItem")
					{
						$this->list_item_ids[] = $node_hier_id;
					}
					if ($ctag == "FileItem")
					{
						$this->file_item_ids[] = $node_hier_id;
					}

				}
				else		// no sibling, no parent -> first node
				{
					$node_hier_id = "1";
					$res->nodeset[$i]->set_attribute("HierId", $node_hier_id);
					$this->hier_ids[] = $node_hier_id;
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
			$this->hier_ids[] = "pg";
		}
		unset($xpc);
	}

	/**
	* get all hierarchical ids
	*/
	function getHierIds()
	{
		return $this->hier_ids;
	}

	/**
	* get ids of all first table rows
	*/
	function getFirstRowIds()
	{
		return $this->first_row_ids;
	}
	
	/**
	* get ids of all first table columns
	*/
	function getFirstColumnIds()
	{
		return $this->first_col_ids;
	}
	
	/**
	* get ids of all list items
	*/
	function getListItemIds()
	{
		return $this->list_item_ids;
	}
	
	/**
	* get ids of all file items
	*/
	function getFileItemIds()
	{
		return $this->file_item_ids;
	}
	
	/**
	* strip all hierarchical id attributes out of the dom tree
	*/
	function stripHierIDs()
	{
		if(is_object($this->dom))
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
	}

	/**
	* add file sizes
	*/
	function addFileSizes()
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//FileItem";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$cnode =& $res->nodeset[$i];
			$size_node =& $this->dom->create_element("Size");
			$size_node =& $cnode->append_child($size_node);

			$childs =& $cnode->child_nodes();
			$size = "";
			for($j = 0; $j < count($childs); $j++)
			{
				if ($childs[$j]->node_name() == "Identifier")
				{
					if ($childs[$j]->has_attribute("Entry"))
					{
						$entry = $childs[$j]->get_attribute("Entry");
						$entry_arr = explode("_", $entry);
						$id = $entry_arr[count($entry_arr) - 1];
						require_once("./Modules/File/classes/class.ilObjFile.php");
						$size = ilObjFile::_lookupFileSize($id);
					}
				}
			}
			$size_node->set_content($size);
		}

		unset($xpc);
	}

	/**
	* Resolves all internal link targets of the page, if targets are available
	* (after import)
	*/
	function resolveIntLinks()
	{
		// resolve normal internal links
		$xpc = xpath_new_context($this->dom);
		$path = "//IntLink";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$target = $res->nodeset[$i]->get_attribute("Target");
			$type = $res->nodeset[$i]->get_attribute("Type");
			
			$new_target = ilInternalLink::_getIdForImportId($type, $target);
			if ($new_target !== false)
			{
				$res->nodeset[$i]->set_attribute("Target", $new_target);
			}
			else		// check wether link target is same installation
			{
				if (ilInternalLink::_extractInstOfTarget($target) == IL_INST_ID &&
					IL_INST_ID > 0 && $type != "RepositoryItem")
				{
					$new_target = ilInternalLink::_removeInstFromTarget($target);
					if (ilInternalLink::_exists($type, $new_target))
					{
						$res->nodeset[$i]->set_attribute("Target", $new_target);	
					}
				}
			}

		}
		unset($xpc);

		// resolve internal links in map areas
		$xpc = xpath_new_context($this->dom);
		$path = "//MediaAlias";
		$res =& xpath_eval($xpc, $path);
//echo "<br><b>page::resolve</b><br>";
//echo "Content:".htmlentities($this->getXMLFromDOM()).":<br>";
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$orig_id = $res->nodeset[$i]->get_attribute("OriginId");
			$id_arr = explode("_", $orig_id);
			$mob_id = $id_arr[count($id_arr) - 1];
			ilMediaItem::_resolveMapAreaLinks($mob_id);
		}
	}

	/**
	* Move internal links from one destination to another. This is used
	* for pages and structure links. Just use IDs in "from" and "to".
	*
	* @param	array	keys are the old targets, values are the new targets
	*/
	function moveIntLinks($a_from_to)
	{
		$this->buildDom();
		
		// resolve normal internal links
		$xpc = xpath_new_context($this->dom);
		$path = "//IntLink";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$target = $res->nodeset[$i]->get_attribute("Target");
			$type = $res->nodeset[$i]->get_attribute("Type");
			$obj_id = ilInternalLink::_extractObjIdOfTarget($target);
			if ($a_from_to[$obj_id] > 0 && is_int(strpos($target, "__")))
			{
				if ($type == "PageObject" && ilLMObject::_lookupType($a_from_to[$obj_id]) == "pg")
				{
					$res->nodeset[$i]->set_attribute("Target", "il__pg_".$a_from_to[$obj_id]);
				}
				if ($type == "StructureObject" && ilLMObject::_lookupType($a_from_to[$obj_id]) == "st")
				{
					$res->nodeset[$i]->set_attribute("Target", "il__st_".$a_from_to[$obj_id]);
				}
			}
		}
		unset($xpc);
	}

	/**
	* Change targest of repository links. Use full targets in "from" and "to"!!!
	*
	* @param	array	keys are the old targets, values are the new targets
	*/
	static function _handleImportRepositoryLinks($a_rep_import_id, $a_rep_type, $a_rep_ref_id)
	{
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		
//echo "-".$a_rep_import_id."-".$a_rep_ref_id."-";
		$sources = ilInternalLink::_getSourcesOfTarget("obj",
			ilInternalLink::_extractObjIdOfTarget($a_rep_import_id),
			ilInternalLink::_extractInstOfTarget($a_rep_import_id));
//var_dump($sources);
		foreach($sources as $source)
		{
//echo "A";
			if ($source["type"] == "lm:pg")
			{
//echo "B";
				$page_obj = new ilPageObject("lm", $source["id"], false);
				if  (!$page_obj->page_not_found)
				{
//echo "C";
					$page_obj->handleImportRepositoryLink($a_rep_import_id,
						$a_rep_type, $a_rep_ref_id);
				}
				$page_obj->update();
			}
		}
	}
		
	function handleImportRepositoryLink($a_rep_import_id, $a_rep_type, $a_rep_ref_id)
	{
		$this->buildDom();
		
		// resolve normal internal links
		$xpc = xpath_new_context($this->dom);
		$path = "//IntLink";
		$res =& xpath_eval($xpc, $path);
//echo "1";
		for($i = 0; $i < count($res->nodeset); $i++)
		{
//echo "2";
			$target = $res->nodeset[$i]->get_attribute("Target");
			$type = $res->nodeset[$i]->get_attribute("Type");
			if ($target == $a_rep_import_id && $type == "RepositoryItem")
			{
//echo "setting:"."il__".$a_rep_type."_".$a_rep_ref_id;
				$res->nodeset[$i]->set_attribute("Target",
					"il__".$a_rep_type."_".$a_rep_ref_id);
			}
		}
		unset($xpc);
	}

	/**
	* create new page object with current xml content
	*/
	function createFromXML()
	{
		global $lng, $ilDB, $ilUser;

//echo "<br>PageObject::createFromXML[".$this->getId()."]";

		if($this->getXMLContent() == "")
		{
			$this->setXMLContent("<PageObject></PageObject>");
		}
		// create object
		$query = "INSERT INTO page_object (page_id, parent_id, content, parent_type, create_user, last_change_user, created) VALUES ".
			"(".$ilDB->quote($this->getId()).",".
			$ilDB->quote($this->getParentId()).",".
			$ilDB->quote($this->getXMLContent()).
			", ".$ilDB->quote($this->getParentType()).
			", ".$ilDB->quote($ilUser->getId()).
			", ".$ilDB->quote($ilUser->getId()).
			", now())";

		if(!$this->ilias->db->checkQuerySize($query))
		{
			$this->ilias->raiseError($lng->txt("check_max_allowed_packet_size"),$this->ilias->error_obj->MESSAGE);
			return false;
		}

		$this->ilias->db->query($query);
//echo "created page:".htmlentities($this->getXMLContent())."<br>";
	}

	/*
	function &copy()
	{
		$page_object =& new ilPageObject($this->getParentType());
		$page_object->setParentId($this->getParentId());
		$page_object->setXMLXContent($this->getXMLContent());
	}*/


	/**
	* updates page object with current xml content
	*/
	function updateFromXML()
	{
		global $lng, $ilDB, $ilUser;

//echo "<br>PageObject::updateFromXML[".$this->getId()."]";
//echo "update:".ilUtil::prepareDBString(($this->getXMLContent())).":<br>";
//echo "update:".htmlentities(ilUtil::prepareDBString(($this->getXMLContent()))).":<br>";

		$query = "UPDATE page_object ".
			"SET content = ".$ilDB->quote($this->getXMLContent())." ".
			", parent_id = ".$ilDB->quote($this->getParentId())." ".
			", last_change_user = ".$ilDB->quote($ilUser->getId())." ".
			", last_change = now() ".
			"WHERE page_id = ".$ilDB->quote($this->getId())." AND parent_type=".
			$ilDB->quote($this->getParentType());

		if(!$this->ilias->db->checkQuerySize($query))
		{
			$this->ilias->raiseError($lng->txt("check_max_allowed_packet_size"),$this->ilias->error_obj->MESSAGE);
			return false;
		}
		$this->ilias->db->query($query);

		return true;
	}

	/**
	* update complete page content in db (dom xml content is used)
	*/
	function update($a_validate = true, $a_no_history = false)
	{
		global $lng, $ilDB, $ilUser, $ilLog, $ilCtrl;
//echo "<br>PageObject::update[".$this->getId()."],validate($a_validate)";
//echo "\n<br>dump_all2:".$this->dom->dump_mem(0, "UTF-8").":";
//echo "\n<br>PageObject::update:".$this->getXMLFromDom().":";
//echo "<br>PageObject::update:".htmlentities($this->getXMLFromDom()).":$a_no_history:"; nk();
		// test validating
		if($a_validate)
		{
			$errors = $this->validateDom();
		}

//echo "-".htmlentities($this->getXMLFromDom())."-"; exit;
		if(empty($errors))
		{
			$content = $this->getXMLFromDom();

			// this needs to be locked

			// write history entry
			$old_set = $ilDB->query("SELECT * FROM page_object WHERE ".
				"page_id = ".$ilDB->quote($this->getId())." AND ".
				"parent_type = ".$ilDB->quote($this->getParentType()));
			$last_nr_set = $ilDB->query("SELECT max(nr) as mnr FROM page_history WHERE ".
				"page_id = ".$ilDB->quote($this->getId())." AND ".
				"parent_type = ".$ilDB->quote($this->getParentType()));
			$last_nr = $last_nr_set->fetchRow(DB_FETCHMODE_ASSOC);
			if ($old_rec = $old_set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				// only save, if something has changed
				if (($content != $old_rec["content"]) && !$a_no_history &&
					!$this->history_saved)
				{
					if ($old_rec["content"] != "<PageObject></PageObject>")
					{
						$h_query = "REPLACE INTO page_history ".
							"(page_id, parent_type, hdate, parent_id, content, user, nr) VALUES (".
							$ilDB->quote($old_rec["page_id"]).",".
							$ilDB->quote($old_rec["parent_type"]).",".
							"now(),".
							$ilDB->quote($old_rec["parent_id"]).",".
							$ilDB->quote($old_rec["content"]).",".
							$ilDB->quote($old_rec["last_change_user"]).",".
							$ilDB->quote($last_nr["mnr"] + 1).")";
//echo "<br><br>+$a_no_history+$h_query";
						$ilDB->query($h_query);
						$this->history_saved = true;		// only save one time
					}
					else
					{
						$this->history_saved = true;		// do not save on first change
					}
				}
			}

			$query = "UPDATE page_object ".
				"SET content = ".$ilDB->quote($content)." ".
				", parent_id= ".$ilDB->quote($this->getParentId())." ".
				", last_change_user= ".$ilDB->quote($ilUser->getId())." ".
				", last_change = now() ".
				" WHERE page_id = ".$ilDB->quote($this->getId()).
				" AND parent_type= ".$ilDB->quote($this->getParentType());
			if(!$this->ilias->db->checkQuerySize($query))
			{
				$this->ilias->raiseError($lng->txt("check_max_allowed_packet_size"),$this->ilias->error_obj->MESSAGE);
				return false;
			}

			$this->ilias->db->query($query);
			
			// handle media object usage
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			$mob_ids = ilObjMediaObject::_getMobsOfObject(
				$this->getParentType().":pg", $this->getId());
			$this->saveMobUsage($this->getXMLFromDom());
			foreach($mob_ids as $mob)	// check, whether media object can be deleted
			{
				if (ilObject::_exists($mob))
				{
					$mob_obj = new ilObjMediaObject($mob);
					$usages = $mob_obj->getUsages();
					if (count($usages) == 0)	// delete, if no usage exists
					{
						$mob_obj->delete();
					}
				}
			}
			
			// handle file usages
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$file_ids = ilObjFile::_getFilesOfObject(
				$this->getParentType().":pg", $this->getId());
			$this->saveFileUsage();
			foreach($file_ids as $file)	// check, whether file object can be deleted
			{
				if (ilObject::_exists($file))
				{
					$file_obj = new ilObjFile($file, false);
					$usages = $file_obj->getUsages();
					if (count($usages) == 0)	// delete, if no usage exists
					{
						$file_obj->delete();
					}
				}
			}
			
			// save internal link information
			$this->saveInternalLinks($this->getXMLFromDom());
			$this->callUpdateListeners();
//echo "<br>PageObject::update:".htmlentities($this->getXMLContent()).":";
			return true;
		}
		else
		{
			return $errors;
		}
	}


	/**
	* delete page object
	*/
	function delete()
	{
		global $ilDB;
		
		$mobs = array();
		$files = array();
		
		if (!$this->page_not_found)
		{
			$this->buildDom();
			$mobs = $this->collectMediaObjects(false);
			$files = $this->collectFileItems();
		}

		// delete mob usages
		$this->saveMobUsage("<dummy></dummy>");

		// delete internal links
		$this->saveInternalLinks("<dummy></dummy>");

		// delete all file usages
		include_once("./Modules/File/classes/class.ilObjFile.php");
		ilObjFile::_deleteAllUsages($this->getParentType().":pg", $this->getId());

		// delete page_object entry
		$query = "DELETE FROM page_object ".
			"WHERE page_id = ".$ilDB->quote($this->getId()).
			" AND parent_type= ".$ilDB->quote($this->getParentType());
		$this->ilias->db->query($query);

		// delete media objects
		foreach ($mobs as $mob_id)
		{
			if (ilObject::_exists($mob_id))
			{
				$mob_obj =& new ilObjMediaObject($mob_id);
				$mob_obj->delete();
			}
		}

		include_once("./Modules/File/classes/class.ilObjFile.php");
		foreach ($files as $file_id)
		{
			if (ilObject::_exists($file_id))
			{
				$file_obj =& new ilObjFile($file_id, false);
				$file_obj->delete();
			}
		}

	}


	/**
	* save all usages of media objects (media aliases, media objects, internal links)
	*
	* @param	string		$a_xml		xml data of page
	*/
	function saveMobUsage($a_xml)
	{
//echo "<br>PageObject::saveMobUsage[".$this->getId()."]";

		$doc = domxml_open_mem($a_xml);

		// media aliases
		$xpc = xpath_new_context($doc);
		$path = "//MediaAlias";
		$res =& xpath_eval($xpc, $path);
		$usages = array();
		for ($i=0; $i < count($res->nodeset); $i++)
		{
			$id_arr = explode("_", $res->nodeset[$i]->get_attribute("OriginId"));
			$mob_id = $id_arr[count($id_arr) - 1];
			if ($mob_id > 0)
			{
				$usages[$mob_id] = true;
			}
		}

		// media objects
		$xpc = xpath_new_context($doc);
		$path = "//MediaObject/MetaData/General/Identifier";
		$res =& xpath_eval($xpc, $path);
		for ($i=0; $i < count($res->nodeset); $i++)
		{
			$mob_entry = $res->nodeset[$i]->get_attribute("Entry");
			$mob_arr = explode("_", $mob_entry);
			$mob_id = $mob_arr[count($mob_arr) - 1];
			if ($mob_id > 0)
			{
				$usages[$mob_id] = true;
			}
		}

		// internal links
		$xpc = xpath_new_context($doc);
		$path = "//IntLink[@Type='MediaObject']";
		$res =& xpath_eval($xpc, $path);
		for ($i=0; $i < count($res->nodeset); $i++)
		{
			$mob_target = $res->nodeset[$i]->get_attribute("Target");
			$mob_arr = explode("_", $mob_target);
			$mob_id = $mob_arr[count($mob_arr) - 1];
			if ($mob_id > 0)
			{
				$usages[$mob_id] = true;
			}
		}

		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		ilObjMediaObject::_deleteAllUsages($this->getParentType().":pg", $this->getId());
		foreach($usages as $mob_id => $val)
		{
			ilObjMediaObject::_saveUsage($mob_id, $this->getParentType().":pg", $this->getId());
		}
	}

	/**
	* save file usages
	*/
	function saveFileUsage()
	{
//echo "<br>PageObject::saveFileUsage[".$this->getId()."]";
		$file_ids = $this->collectFileItems();
		include_once("./Modules/File/classes/class.ilObjFile.php");
		ilObjFile::_deleteAllUsages($this->getParentType().":pg", $this->getId());
		foreach($file_ids as $file_id)
		{
			ilObjFile::_saveUsage($file_id, $this->getParentType().":pg", $this->getId());
		}
	}


	/**
	* save internal links of page
	*
	* @param	string		xml page code
	*/
	function saveInternalLinks($a_xml)
	{
//echo "<br>PageObject::saveInternalLinks[".$this->getId()."]";
		$doc = domxml_open_mem($a_xml);


		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		ilInternalLink::_deleteAllLinksOfSource($this->getParentType().":pg", $this->getId());

		// get all internal links
		$xpc = xpath_new_context($doc);
		$path = "//IntLink";
		$res =& xpath_eval($xpc, $path);
		for ($i=0; $i < count($res->nodeset); $i++)
		{
			$link_type = $res->nodeset[$i]->get_attribute("Type");

			switch ($link_type)
			{
				case "StructureObject":
					$t_type = "st";
					break;

				case "PageObject":
					$t_type = "pg";
					break;

				case "GlossaryItem":
					$t_type = "git";
					break;

				case "MediaObject":
					$t_type = "mob";
					break;

				case "RepositoryItem":
					$t_type = "obj";
					break;
			}

			$target = $res->nodeset[$i]->get_attribute("Target");
			$target_arr = explode("_", $target);
			$t_id = $target_arr[count($target_arr) - 1];

			// link to other internal object
			if (is_int(strpos($target, "__")))
			{
				$t_inst = 0;
			}
			else	// link to unresolved object in other installation
			{
				$t_inst = $target_arr[1];
			}

			if ($t_id > 0)
			{
				ilInternalLink::_saveLink($this->getParentType().":pg", $this->getId(), $t_type,
					$t_id, $t_inst);
			}
		}

	}


	/**
	* create new page (with current xml data)
	*/
	function create()
	{
		$this->createFromXML();
	}

	/**
	* delete content object with hierarchical id $a_hid
	*
	* @param	string		$a_hid		hierarchical id of content object
	* @param	boolean		$a_update	update page in db (note: update deletes all
	*									hierarchical ids in DOM!)
	*/
	function deleteContent($a_hid, $a_update = true, $a_pcid = "")
	{
		$curr_node =& $this->getContentNode($a_hid, $a_pcid);
		$curr_node->unlink_node($curr_node);
		if ($a_update)
		{
			return $this->update();
		}
	}
	
	/**
	* delete multiple content objects
	*
	* @param	string		$a_hids		array of hierarchical ids of content objects
	* @param	boolean		$a_update	update page in db (note: update deletes all
	*									hierarchical ids in DOM!)
	*/
	function deleteContents($a_hids, $a_update = true)
	{
		if (!is_array($a_hids))
		{
			return;
		}
		foreach($a_hids as $a_hid)
		{
			$a_hid = explode(":", $a_hid);
//echo "-".$a_hid[0]."-".$a_hid[1]."-";
			$curr_node =& $this->getContentNode($a_hid[0], $a_hid[1]);
			if (is_object($curr_node))
			{
				$parent_node = $curr_node->parent_node();
				if ($parent_node->node_name() != "TableRow")
				{
					$curr_node->unlink_node($curr_node);
				}
			}
		}
		if ($a_update)
		{
			return $this->update();
		}
	}
	
	/**
	* gui function
	* set enabled if is not enabled and vice versa
	*/
	function switchEnableMultiple($a_hids, $a_update = true) 
	{		
		if (!is_array($a_hids))
		{
			return;
		}
		$obj = & $this->content_obj;
		
		foreach($a_hids as $a_hid)
		{
			$a_hid = explode(":", $a_hid);
//echo "-".$a_hid[0]."-".$a_hid[1]."-";
			$curr_node =& $this->getContentNode($a_hid[0], $a_hid[1]);
			if (is_object($curr_node))
			{
				if ($curr_node->node_name() == "PageContent")
				{
					$cont_obj =& $this->getContentObject($a_hid[0], $a_hid[1]);
					if ($cont_obj->isEnabled ()) 
						$cont_obj->disable ();
					else
						$cont_obj->enable ();
				}
			}
		}
	 	
		if ($a_update)
		{
			return $this->update();
		}
	}


	/**
	* delete content object with hierarchical id >= $a_hid
	*
	* @param	string		$a_hid		hierarchical id of content object
	* @param	boolean		$a_update	update page in db (note: update deletes all
	*									hierarchical ids in DOM!)
	*/
	function deleteContentFromHierId($a_hid, $a_update = true)
	{
		$hier_ids = $this->getHierIds();
		
		// iterate all hierarchical ids
		foreach ($hier_ids as $hier_id)
		{
			// delete top level nodes only
			if (!is_int(strpos($hier_id, "_")))
			{
				if ($hier_id != "pg" && $hier_id >= $a_hid)
				{
					$curr_node =& $this->getContentNode($hier_id);
					$curr_node->unlink_node($curr_node);
				}
			}
		}
		if ($a_update)
		{
			return $this->update();
		}
	}

	/**
	* delete content object with hierarchical id < $a_hid
	*
	* @param	string		$a_hid		hierarchical id of content object
	* @param	boolean		$a_update	update page in db (note: update deletes all
	*									hierarchical ids in DOM!)
	*/
	function deleteContentBeforeHierId($a_hid, $a_update = true)
	{
		$hier_ids = $this->getHierIds();
		
		// iterate all hierarchical ids
		foreach ($hier_ids as $hier_id)
		{
			// delete top level nodes only
			if (!is_int(strpos($hier_id, "_")))
			{
				if ($hier_id != "pg" && $hier_id < $a_hid)
				{
					$curr_node =& $this->getContentNode($hier_id);
					$curr_node->unlink_node($curr_node);
				}
			}
		}
		if ($a_update)
		{
			return $this->update();
		}
	}
	
	
	/**
	* move content of hierarchical id >= $a_hid to other page
	*
	* @param	string		$a_hid		hierarchical id of content object
	* @param	boolean		$a_update	update page in db (note: update deletes all
	*									hierarchical ids in DOM!)
	*/
	function _moveContentAfterHierId(&$a_source_page, &$a_target_page, $a_hid)
	{
		$hier_ids = $a_source_page->getHierIds();

		$copy_ids = array();

		// iterate all hierarchical ids
		foreach ($hier_ids as $hier_id)
		{
			// move top level nodes only
			if (!is_int(strpos($hier_id, "_")))
			{
				if ($hier_id != "pg" && $hier_id >= $a_hid)
				{
					$copy_ids[] = $hier_id;
				}
			}
		}
		asort($copy_ids);

		$parent_node =& $a_target_page->getContentNode("pg");
		$target_dom =& $a_target_page->getDom();
		$parent_childs =& $parent_node->child_nodes();
		$cnt_parent_childs = count($parent_childs);
//echo "-$cnt_parent_childs-";
		$first_child =& $parent_childs[0];
		foreach($copy_ids as $copy_id)
		{
			$source_node =& $a_source_page->getContentNode($copy_id);

			$new_node =& $source_node->clone_node(true);
			$new_node->unlink_node($new_node);

			$source_node->unlink_node($source_node);

			if($cnt_parent_childs == 0)
			{
				$new_node =& $parent_node->append_child($new_node);
			}
			else
			{
				//$target_dom->import_node($new_node);
				$new_node =& $first_child->insert_before($new_node, $first_child);
			}
			$parent_childs =& $parent_node->child_nodes();

			//$cnt_parent_childs++;
		}

		$a_target_page->update();
		$a_source_page->update();

	}

	/**
	* insert a content node before/after a sibling or as first child of a parent
	*/
	function insertContent(&$a_cont_obj, $a_pos, $a_mode = IL_INSERT_AFTER, $a_pcid = "")
	{
		// move mode into container elements is always INSERT_CHILD
		$curr_node = $this->getContentNode($a_pos, $a_pcid);
		$curr_name = $curr_node->node_name();
		if (($curr_name == "TableData") || ($curr_name == "PageObject") ||
			($curr_name == "ListItem") || ($curr_name == "Section")
			|| ($curr_name == "Tab"))
		{
			$a_mode = IL_INSERT_CHILD;
		}

		$hid = $curr_node->get_attribute("HierId");
		if ($hid != "")
		{
//echo "-".$a_pos."-".$hid."-";
			$a_pos = $hid;
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
//echo "ZZ$a_mode";
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
//echo "INSERT_BEF";
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
//echo "PP";
				break;
		}

	}


	/**
	* move content object from position $a_source before position $a_target
	* (both hierarchical content ids)
	*/
	function moveContentBefore($a_source, $a_target, $a_spcid = "", $a_tpcid = "")
	{
		if($a_source == $a_target)
		{
			return;
		}

		// clone the node
		$content =& $this->getContentObject($a_source, $a_spcid);
		$source_node =& $content->getNode();
		$clone_node =& $source_node->clone_node(true);

		// delete source node
		$this->deleteContent($a_source, false, $a_spcid);

		// insert cloned node at target
		$content->setNode($clone_node);
		$this->insertContent($content, $a_target, IL_INSERT_BEFORE, $a_tpcid);
		return $this->update();

	}

	/**
	* move content object from position $a_source before position $a_target
	* (both hierarchical content ids)
	*/
	function moveContentAfter($a_source, $a_target, $a_spcid = "", $a_tpcid = "")
	{
		if($a_source == $a_target)
		{
			return;
		}

		// clone the node
		$content =& $this->getContentObject($a_source, $a_spcid);
		$source_node =& $content->getNode();
		$clone_node =& $source_node->clone_node(true);

		// delete source node
		$this->deleteContent($a_source, false, $a_spcid);

		// insert cloned node at target
		$content->setNode($clone_node);
		$this->insertContent($content, $a_target, IL_INSERT_AFTER, $a_tpcid);
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
	* inserts installation id into ids (e.g. il__pg_4 -> il_23_pg_4)
	* this is needed for xml export of page
	*/
	function insertInstIntoIDs($a_inst, $a_res_ref_to_obj_id = true)
	{
		// insert inst id into internal links
		$xpc = xpath_new_context($this->dom);
		$path = "//IntLink";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$target = $res->nodeset[$i]->get_attribute("Target");
			$type = $res->nodeset[$i]->get_attribute("Type");

			if (substr($target, 0, 4) == "il__")
			{
				$id = substr($target, 4, strlen($target) - 4);
				
				// convert repository links obj_<ref_id> to <type>_<obj_id>
				if ($a_res_ref_to_obj_id && $type == "RepositoryItem")
				{
					$id_arr = explode("_", $id);
					$obj_id = ilObject::_lookupObjId($id_arr[1]);
					$otype = ilObject::_lookupType($obj_id);
					if ($obj_id > 0)
					{
						$id = $otype."_".$obj_id;
					}
				}
				$new_target = "il_".$a_inst."_".$id;
				$res->nodeset[$i]->set_attribute("Target", $new_target);
			}
		}
		unset($xpc);

		// insert inst id into media aliases
		$xpc = xpath_new_context($this->dom);
		$path = "//MediaAlias";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$origin_id = $res->nodeset[$i]->get_attribute("OriginId");
			if (substr($origin_id, 0, 4) == "il__")
			{
				$new_id = "il_".$a_inst."_".substr($origin_id, 4, strlen($origin_id) - 4);
				$res->nodeset[$i]->set_attribute("OriginId", $new_id);
			}
		}
		unset($xpc);

		// insert inst id file item identifier entries
		$xpc = xpath_new_context($this->dom);
		$path = "//FileItem/Identifier";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$origin_id = $res->nodeset[$i]->get_attribute("Entry");
			if (substr($origin_id, 0, 4) == "il__")
			{
				$new_id = "il_".$a_inst."_".substr($origin_id, 4, strlen($origin_id) - 4);
				$res->nodeset[$i]->set_attribute("Entry", $new_id);
			}
		}
		unset($xpc);
		
		// insert inst id into 
		$xpc = xpath_new_context($this->dom);
		$path = "//Question";
		$res =& xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$qref = $res->nodeset[$i]->get_attribute("QRef");
//echo "<br>setted:".$qref;
			if (substr($qref, 0, 4) == "il__")
			{
				$new_id = "il_".$a_inst."_".substr($qref, 4, strlen($qref) - 4);
//echo "<br>setting:".$new_id;
				$res->nodeset[$i]->set_attribute("QRef", $new_id);
			}
		}
		unset($xpc);

	}

	/**
	* Highligths Text with given ProgLang
	*/

	function highlightText($a_text, $proglang, $autoindent)
	{

		if (!$this->hasHighlighter($proglang)) {
			$proglang="plain";
		}
		
		require_once("./Services/COPage/syntax_highlight/php/HFile/HFile_".$proglang.".php");
		$classname =  "HFile_$proglang";
		$h_instance = new $classname();
		if ($autoindent == "n") {
			$h_instance ->notrim   = 1;
			$h_instance ->indent   = array ("");
			$h_instance ->unindent = array ("");
		}

		$highlighter = new Core($h_instance, new Output_css());
		$a_text = $highlighter->highlight_text(html_entity_decode($a_text));

		return $a_text;
	}

	function hasHighlighter ($hfile_ext) {
		return file_exists ("Services/COPage/syntax_highlight/php/HFile/HFile_".$hfile_ext.".php");
	}

	/**
	* depending on the SubCharacteristic and ShowLineNumbers
	* attribute the line numbers and html tags for the syntax
	* highlighting will be inserted using the dom xml functions
	*/
	function insertSourceCodeParagraphs($a_output, $outputmode = "presentation")
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//Paragraph"; //"[@Characteristic = 'Code']";
		$res = & xpath_eval($xpc, $path);
		for($i = 0; $i < count($res->nodeset); $i++)
		{					
			$context_node = $res->nodeset[$i];
			$char = $context_node->get_attribute('Characteristic');

			if ($char != "Code")			
				continue; 
			
			$n = $context_node->parent_node();
			$char = $context_node->get_attribute('Characteristic');
			$subchar = $context_node->get_attribute('SubCharacteristic');
			$showlinenumbers = $context_node->get_attribute('ShowLineNumbers');
			$downloadtitle = $context_node->get_attribute('DownloadTitle');
			$autoindent = $context_node->get_attribute('AutoIndent');

			$content = "";

			// get XML Content
			$childs = $context_node->child_nodes();

			for($j=0; $j<count($childs); $j++)
			{
				$content .= $this->dom->dump_node($childs[$j]);
			}

			while ($context_node->has_child_nodes ())
			{
				$node_del = $context_node->first_child ();
				$context_node->remove_child ($node_del);
			}

			$content = str_replace("<br />", "<br/>", utf8_decode($content) );
			$content = str_replace("<br/>", "\n", $content);
			$rownums = count(split ("\n",$content));

			$plain_content = html_entity_decode($content);
			$plain_content = preg_replace ("/\&#x([1-9a-f]{2});?/ise","chr (base_convert (\\1, 16, 10))",$plain_content);
			$plain_content = preg_replace ("/\&#(\d+);?/ise","chr (\\1)",$plain_content);			
			$content = utf8_encode($this->highlightText($plain_content, $subchar, $autoindent));

			$content = str_replace("&amp;lt;", "&lt;", $content);
			$content = str_replace("&amp;gt;", "&gt;", $content);
			$content = str_replace("&", "&amp;", $content);					

			$rows  	 = "<TR valign=\"top\">";
			$rownumbers = "";
			$linenumbers= "";

			//if we have to show line numbers
			if (strcmp($showlinenumbers,"y")==0)
			{
				$linenumbers = "<TD nowrap=\"nowrap\" class=\"ilc_LineNumbers\" >";
				$linenumbers .= "<PRE class=\"ilc_Code\">";

				for ($j=0; $j < $rownums; $j++)
				{
					$indentno      = strlen($rownums) - strlen($j+1) + 2;
					$rownumeration = ($j+1);
					$linenumbers   .= "<span class=\"ilc_LineNumber\">$rownumeration</span>";
					if ($j < $rownums-1)
					{
						$linenumbers .= "\n";
					}
				}
				$linenumbers .= "</PRE>";
				$linenumbers .= "</TD>";
			}
			
			$rows .= $linenumbers."<TD class=\"ilc_Sourcecode\"><PRE class=\"ilc_Code\">".$content."</PRE></TD></TR>";
			$rows .= "</TR>";

			// fix for ie explorer which is not able to produce empty line feeds with <br /><br />; 
			// workaround: add a space after each br.
			$newcontent = str_replace("\n", "<br/>",$rows);
			// fix for IE
			$newcontent = str_replace("<br/><br/>", "<br/> <br/>",$newcontent);	
			// falls drei hintereinander...
			$newcontent = str_replace("<br/><br/>", "<br/> <br/>",$newcontent);

			//$context_node->set_content($newcontent);
//var_dump($newcontent);
			$a_output = str_replace("[[[[[Code;".($i + 1)."]]]]]", $newcontent, $a_output);
			
			if ($outputmode != "presentation" && is_object($this->offline_handler)
				&& trim($downloadtitle) != "")
			{
				// call code handler for offline versions
				$this->offline_handler->handleCodeParagraph ($this->id, $i + 1, $downloadtitle, $plain_content);
			}
		}
		
		return $a_output;
	}
	

	/**
	* Check, whether (all) page content hashes are set
	*/
	function checkPCIds()
	{
		$this->builddom();
		$mydom = $this->dom;
		
		$sep = $path = "";
		foreach ($this->id_elements as $el)
		{
			$path.= $sep."//".$el."[not(@PCID)]";
			$sep = " | ";
		}
		
		$xpc = xpath_new_context($mydom);
		$res = & xpath_eval($xpc, $path);

		if (count ($res->nodeset) > 0)
		{
			return false;
		}
		return true;
	}

	/**
	* Insert Page Content IDs
	*/
	function insertPCIds()
	{
		$this->builddom();
		$mydom = $this->dom;
		
		$pcids = array();

		$sep = $path = "";
		foreach ($this->id_elements as $el)
		{
			$path.= $sep."//".$el."[@PCID]";
			$sep = " | ";
		}
		
		// get existing ids
		$xpc = xpath_new_context($mydom);
		$res = & xpath_eval($xpc, $path);

		for ($i = 0; $i < count ($res->nodeset); $i++)
		{
			$node = $res->nodeset[$i];
			$pcids[] = $node->get_attribute("PCID");
		}
		
		// add missing ones
		$sep = $path = "";
		foreach ($this->id_elements as $el)
		{
			$path.= $sep."//".$el."[not(@PCID)]";
			$sep = " | ";
		}
		$xpc = xpath_new_context($mydom);
		$res = & xpath_eval($xpc, $path);

		for ($i = 0; $i < count ($res->nodeset); $i++)
		{
			$node = $res->nodeset[$i];
			$id = ilUtil::randomHash(10, $pcids);
			$pcids[] = $id;
//echo "setting-".$id."-";
			$res->nodeset[$i]->set_attribute("PCID", $id);
		}
	}
	
	/**
	* Get page contents hashes
	*/
	function getPageContentsHashes()
	{
		$this->builddom();
		$this->addHierIds();
		$mydom = $this->dom;
		
		// get existing ids
		$path = "//PageContent";
		$xpc = xpath_new_context($mydom);
		$res = & xpath_eval($xpc, $path);

		$hashes = array();
		for ($i = 0; $i < count ($res->nodeset); $i++)
		{
			$hier_id = $res->nodeset[$i]->get_attribute("HierId");
			$pc_id = $res->nodeset[$i]->get_attribute("PCID");
			$dump = $mydom->dump_node($res->nodeset[$i]);
			if (($hpos = strpos($dump, ' HierId="'.$hier_id.'"')) > 0)
			{
				$dump = substr($dump, 0, $hpos).
					substr($dump, $hpos + strlen(' HierId="'.$hier_id.'"'));
			}
			
			$childs = $res->nodeset[$i]->child_nodes();
			$content = "";
			if ($childs[0] && $childs[0]->node_name() == "Paragraph")
			{
				$content = $mydom->dump_node($childs[0]);
				$content = substr($content, strpos($content, ">") + 1,
					strrpos($content, "<") - (strpos($content, ">") + 1));
//var_dump($content);
				$content = ilPCParagraph::xml2output($content);
//var_dump($content);
			}
			//$hashes[$hier_id] =
			//	array("PCID" => $pc_id, "hash" => md5($dump));
			$hashes[$pc_id] =
				array("hier_id" => $hier_id, "hash" => md5($dump), "content" => $content);
		}
		
		return $hashes;
	}
	
	function send_paragraph ($par_id, $filename)
	{
		$this->builddom();

		$mydom = $this->dom;

		$xpc = xpath_new_context($mydom);

		//$path = "//PageContent[position () = $par_id]/Paragraph";
		//$path = "//Paragraph[$par_id]";
		$path = "/descendant::Paragraph[position() = $par_id]";

		$res = & xpath_eval($xpc, $path);
		
		if (count ($res->nodeset) != 1)
			die ("Should not happen");

		$context_node = $res->nodeset[0];

		// get plain text

		$childs = $context_node->child_nodes();

		for($j=0; $j<count($childs); $j++)
		{
			$content .= $mydom->dump_node($childs[$j]);
		}

		$content = str_replace("<br />", "\n", $content);
		$content = str_replace("<br/>", "\n", $content);

		$plain_content = html_entity_decode($content);

		ilUtil::deliverData($plain_content, $filename);
		/*
		$file_type = "application/octet-stream";
		header("Content-type: ".$file_type);
		header("Content-disposition: attachment; filename=\"$filename\"");
		echo $plain_content;*/
		exit();
	}

	/**
	* get fo page content
	*/
	function getFO()
	{
		$xml = $this->getXMLFromDom(false, true, true);
		$xsl = file_get_contents("./Services/COPage/xsl/page_fo.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();

		$params = array ();


		$fo = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);

		// do some replacements
		$fo = str_replace("\n", "", $fo);
		$fo = str_replace("<br/>", "<br>", $fo);
		$fo = str_replace("<br>", "\n", $fo);

		xslt_free($xh);

		//
		$fo = substr($fo, strpos($fo,">") + 1);
//echo "<br><b>fo:</b><br>".htmlentities($fo); flush();
		return $fo;
	}
	
	function registerOfflineHandler ($handler) {
		$this->offline_handler = $handler;
	}
	
	/**
	* lookup whether page contains deactivated elements
	*/
	function _lookupContainsDeactivatedElements($a_id, $a_parent_type)
	{
		global $ilDB;

		$query = "SELECT * FROM page_object WHERE page_id = ".
			$ilDB->quote($a_id)." AND ".
			" parent_type = ".$ilDB->quote($a_parent_type)." AND ".
			" content LIKE '% Enabled=\"False\"%'";
		$obj_set = $ilDB->query($query);
		
		if ($obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return true;
		}

		return false;
	}

	/**
	* Get History Entries
	*/
	function getHistoryEntries()
	{
		global $ilDB;
		
		$h_query = "SELECT * FROM page_history ".
			" WHERE page_id = ".$ilDB->quote($this->getId()).
			" AND parent_type = ".$ilDB->quote($this->getParentType()).
			" ORDER BY hdate DESC";
		
		$hset = $ilDB->query($h_query);
		$hentries = array();

		while ($hrec = $hset->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$hentries[] = $hrec;
		}
		
		return $hentries;
	}
	
	/**
	* Get information about a history entry, its predecessor and
	* its successor.
	*
	* @param	int		$a_nr		Nr of history entry
	*/
	function getHistoryInfo($a_nr)
	{
		global $ilDB;
		
		$h_query = "SELECT * FROM page_history ".
			" WHERE page_id = ".$ilDB->quote($this->getId()).
			" AND parent_type = ".$ilDB->quote($this->getParentType()).
			" AND nr in (".
				$ilDB->quote($a_nr - 1).",".$ilDB->quote($a_nr).",".$ilDB->quote($a_nr + 1).")".
			" ORDER BY hdate DESC";
		
		$hset = $ilDB->query($h_query);
		$ret = array();

		while ($hrec = $hset->fetchRow(DB_FETCHMODE_ASSOC))
		{
			switch ($hrec["nr"])
			{
				case ($a_nr - 1):
					$ret["previous"] = $hrec;
					break;
					
				case ($a_nr):
					$ret["current"] = $hrec;
					break;

				case ($a_nr + 1):
					$ret["next"] = $hrec;
					break;
			}
		}

		return $ret;
	}
	
	function addChangeDivClasses($a_hashes)
	{
		$xpc = xpath_new_context($this->dom);
		$path = "/*[1]";
		$res =& xpath_eval($xpc, $path);
		$rnode = $res->nodeset[0];

//echo "A";
		foreach($a_hashes as $pc_id => $h)
		{
//echo "B";
			if ($h["change"] != "")
			{
//echo "<br>C-".$h["hier_id"]."-".$h["change"]."-";
				$dc_node = $this->dom->create_element("DivClass");
				$dc_node->set_attribute("HierId", $h["hier_id"]);
				$dc_node->set_attribute("Class", "ilEdit".$h["change"]);
				$dc_node = $rnode->append_child($dc_node);
			}
		}
	}
	
	/**
	* Compares to revisions of the page
	*
	* @param	int		$a_left		Nr of first revision
	* @param	int		$a_right	Nr of second revision
	*/
	function compareVersion($a_left, $a_right)
	{
		global $ilDB;
		
		// get page objects
		$l_page = new ilPageObject($this->getParentType(), $this->getId(), $a_left);
		$r_page = new ilPageObject($this->getParentType(), $this->getId(), $a_right);
		
		$l_hashes = $l_page->getPageContentsHashes();
		$r_hashes = $r_page->getPageContentsHashes();
		
		// determine all deleted and changed page elements
		foreach ($l_hashes as $pc_id => $h)
		{
			if (!isset($r_hashes[$pc_id]))
			{
				$l_hashes[$pc_id]["change"] = "Deleted";
			}
			else
			{
				if ($l_hashes[$pc_id]["hash"] != $r_hashes[$pc_id]["hash"])
				{
					$l_hashes[$pc_id]["change"] = "Modified";
					$r_hashes[$pc_id]["change"] = "Modified";
					
					include_once("./Services/COPage/mediawikidiff/class.WordLevelDiff.php");
					// if modified element is a paragraph, highlight changes
					if ($l_hashes[$pc_id]["content"] != "" &&
						$r_hashes[$pc_id]["content"] != "")
					{
						$new_left = str_replace("\n", "<br />", $l_hashes[$pc_id]["content"]);
						$new_right = str_replace("\n", "<br />", $r_hashes[$pc_id]["content"]);
						$wldiff = new WordLevelDiff(array($new_left),
							array($new_right));
						$new_left = $wldiff->orig();
						$new_right = $wldiff->closing();
						$l_page->setParagraphContent($l_hashes[$pc_id]["hier_id"], $new_left[0]);
						$r_page->setParagraphContent($l_hashes[$pc_id]["hier_id"], $new_right[0]);
					}
				}
			}
		}
		
		// determine all new paragraphs
		foreach ($r_hashes as $pc_id => $h)
		{
			if (!isset($l_hashes[$pc_id]))
			{
				$r_hashes[$pc_id]["change"] = "New";
			}
		}
		
		$l_page->addChangeDivClasses($l_hashes);
		$r_page->addChangeDivClasses($r_hashes);
		
		return array("l_page" => $l_page, "r_page" => $r_page,
			"l_changes" => $l_hashes, "r_changes" => $r_hashes);
	}
	
	/**
	* increase view cnt
	*/
	function increaseViewCnt()
	{
		global $ilDB;
		
		$q = "UPDATE page_object ".
			" SET view_cnt = view_cnt + 1 ".
			" WHERE page_id = ".$ilDB->quote($this->getId()).
			" AND parent_type = ".$ilDB->quote($this->getParentType());
		$ilDB->query($q);
	}
	
	/**
	* Get recent pages changes for parent object.
	*
	* @param	string	$a_parent_type	Parent Type
	* @param	int		$a_parent_id	Parent ID
	* @param	int		$a_period		Time Period
	*/
	static function getRecentChanges($a_parent_type, $a_parent_id, $a_period = 30)
	{
		global $ilDB;
		
		$page_changes = array();
		$q = "SELECT * FROM page_object ".
			" WHERE parent_id = ".$ilDB->quote($a_parent_id).
			" AND parent_type = ".$ilDB->quote($a_parent_type).
			" AND (TO_DAYS(now()) - TO_DAYS(last_change)) <= ".((int)$a_period);
		$set = $ilDB->query($q);
		while($page = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$page_changes[] = array("date" => $page["last_change"],
				"id" => $page["page_id"], "type" => "page",
				"user" => $page["last_change_user"]);
		}

		$q = "SELECT * FROM page_history ".
			" WHERE parent_id = ".$ilDB->quote($a_parent_id).
			" AND parent_type = ".$ilDB->quote($a_parent_type).
			" AND (TO_DAYS(now()) - TO_DAYS(hdate)) <= ".((int)$a_period);
		$set = $ilDB->query($q);
		while ($page = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$page_changes[] = array("date" => $page["hdate"],
				"id" => $page["page_id"], "type" => "hist", "nr" => $page["nr"],
				"user" => $page["user"]);
		}
		
		$page_changes = ilUtil::sortArray($page_changes, "date", "desc");
		
		return $page_changes;
	}
	
	/**
	* Get all pages for parent object
	*
	* @param	string	$a_parent_type	Parent Type
	* @param	int		$a_parent_id	Parent ID
	* @param	int		$a_period		Time Period
	*/
	static function getAllPages($a_parent_type, $a_parent_id)
	{
		global $ilDB;
		
		$page_changes = array();
		
		$q = "SELECT * FROM page_object ".
			" WHERE parent_id = ".$ilDB->quote($a_parent_id).
			" AND parent_type = ".$ilDB->quote($a_parent_type);
		$set = $ilDB->query($q);
		$pages = array();
		while ($page = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$pages[$page["page_id"]] = array("date" => $page["last_change"],
				"id" => $page["page_id"], "user" => $page["last_change_user"]);
		}

		return $pages;
	}

	/**
	* Get new pages.
	*
	* @param	string	$a_parent_type	Parent Type
	* @param	int		$a_parent_id	Parent ID
	*/
	static function getNewPages($a_parent_type, $a_parent_id)
	{
		global $ilDB;
		
		$pages = array();
		
		$q = "SELECT * FROM page_object ".
			" WHERE parent_id = ".$ilDB->quote($a_parent_id).
			" AND parent_type = ".$ilDB->quote($a_parent_type).
			" ORDER BY created DESC";
		$set = $ilDB->query($q);
		while($page = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($page["created"] != "0000-00-00 00:00:00")
			{
				$pages[] = array("created" => $page["created"],
					"id" => $page["page_id"],
					"user" => $page["create_user"],
					);
			}
		}

		return $pages;
	}

	/**
	* Get all contributors for parent object
	*
	* @param	string	$a_parent_type	Parent Type
	* @param	int		$a_parent_id	Parent ID
	*/
	static function getParentObjectContributors($a_parent_type, $a_parent_id)
	{
		global $ilDB;
		
		$contributors = array();
		$st = $ilDB->prepare("SELECT last_change_user FROM page_object ".
			" WHERE parent_id = ? AND parent_type = ? ".
			" AND last_change_user != ?",
			array("integer", "text", "integer"));
		$set = $ilDB->execute($st, array($a_parent_id, $a_parent_type, 0));

		while ($page = $ilDB->fetchAssoc($set))
		{
			$contributors[$page["last_change_user"]][$page["page_id"]] = 1;
		}

		$st = $ilDB->prepare("SELECT count(*) as cnt, page_id, user FROM page_history ".
			" WHERE parent_id = ? AND parent_type = ? AND user != ? ".
			" GROUP BY page_id, user ",
			array("integer", "text", "integer"));
		$set = $ilDB->execute($st, array($a_parent_id, $a_parent_type, 0));
		while ($hpage = $ilDB->fetchAssoc($set))
		{
			$contributors[$hpage["user"]][$hpage["page_id"]] =
				$contributors[$hpage["user"]][$hpage["page_id"]] + $hpage["cnt"];
		}
		
		$c = array();
		foreach ($contributors as $k => $co)
		{
			$name = ilObjUser::_lookupName($k);
			$c[] = array("user_id" => $k, "pages" => $co,
				"lastname" => $name["lastname"], "firstname" => $name["firstname"]);
		}
		
		return $c;
	}

	/**
	* Write rendered content
	*/
	function writeRenderedContent($a_content, $a_md5)
	{
		global $ilDB;
		
		$st = $ilDB->prepareManip("UPDATE page_object ".
			" SET rendered_content = ?, render_md5 = ?, rendered_time = now()".
			" WHERE page_id = ?  AND parent_type = ?",
			array("text", "text", "integer", "text"));
		$r = $ilDB->execute($st,
			array($a_content, $a_md5, $this->getId(), $this->getParentType()));
	}

}
?>
