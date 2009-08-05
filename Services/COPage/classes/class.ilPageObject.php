<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	var $cur_dtd = "ilias_pg_3_11.dtd";
	var $contains_int_link;
	var $needs_parsing;
	var $parent_type;
	var $parent_id;
	var $update_listeners;
	var $update_listener_cnt;
	var $offline_handler;
	var $dom_builded;
	var $history_saved;
	var $layout_mode;
	
	/**
	* Constructor
	* @access	public
	*/
	function ilPageObject($a_parent_type, $a_id = 0, $a_old_nr = 0, $a_halt = true)
	{
		global $ilias;

		require_once("./Services/COPage/syntax_highlight/php/Beautifier/Init.php");
		require_once("./Services/COPage/syntax_highlight/php/Output/Output_css.php");

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
		$this->layout_mode = false;
		$this->encoding = "UTF-8";		
		$this->id_elements =
			array("PageContent", "TableRow", "TableData", "ListItem", "FileItem",
				"Section", "Tab");
		
		$this->setActive(true);
		
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
	* Set Layout Mode
	*
	* @param boolean	$a_layout_mode	SetLayoutMode for editor
	*/
	function setLayoutMode($a_layout_mode)
	{
		$this->layout_mode = $a_layout_mode;
	}

	/**
	* Get Layout Mode enabled/disabled
	*
	* @return boolean	Get Layout Mode Setting
	*/
	function getLayoutMode()
	{
		return $this->layout_mode;
	}
	

	/**
	* read page data
	*/
	function read()
	{
		global $ilBench, $ilDB;

		$ilBench->start("ContentPresentation", "ilPageObject_read");
		
		$this->setActive(true);
		if ($this->old_nr == 0)
		{
			$query = "SELECT * FROM page_object WHERE page_id = ".$ilDB->quote($this->id, "integer")." ".
				"AND parent_type=".$ilDB->quote($this->getParentType(), "text");
			$pg_set = $this->ilias->db->query($query);
			$this->page_record = $ilDB->fetchAssoc($pg_set);
			$this->setActive($this->page_record["active"]);
			$this->setActivationStart($this->page_record["activation_start"]);
			$this->setActivationEnd($this->page_record["activation_end"]);
		}
		else
		{
			$query = "SELECT * FROM page_history WHERE ".
				"page_id = ".$ilDB->quote($this->id, "integer")." ".
				"AND parent_type=".$ilDB->quote($this->getParentType(), "text").
				" AND nr = ".$ilDB->quote((int) $this->old_nr, "integer");
			$pg_set = $ilDB->query($query);
			$this->page_record = $ilDB->fetchAssoc($pg_set);
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
		$this->last_change_user = $this->page_record["last_change_user"];
		$this->create_user = $this->page_record["create_user"];
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
	static function _exists($a_parent_type, $a_id)
	{
		global $ilDB;
		
		$query = "SELECT page_id FROM page_object WHERE page_id = ".$ilDB->quote($a_id, "integer")." ".
			"AND parent_type= ".$ilDB->quote($a_parent_type, "text");

		$set = $ilDB->query($query);
		if ($row = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* checks whether page exists and is not empty (may return true on some empty pages)
	*
	* @param	string		$a_parent_type	parent type
	* @param	int			$a_id			page id
	*/
	function _existsAndNotEmpty($a_parent_type, $a_id)
	{
		global $ilDB;
		
		$query = "SELECT page_id, is_empty FROM page_object WHERE page_id = ".$ilDB->quote($a_id, "integer")." ".
			"AND parent_type= ".$ilDB->quote($a_parent_type, "text");

		$set = $ilDB->query($query);
		if ($row = $ilDB->fetchAssoc($set))
		{
			if ($row["is_empty"] != 1)
			{
				return true;
			}
		}
		return false;
	}

	function buildDom($a_force = false)
	{
		global $ilBench;

		if ($this->dom_builded && !$a_force)
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

	/**
	* set activation
	*
	* @param	boolean		$a_active	true/false for active or not
	*/
	function setActive($a_active)
	{
		$this->active = $a_active;
	}

	/**
	* get activation
	*
	* @return	boolean		true/false for active or not
	*/
	function getActive($a_check_scheduled_activation = false)
	{
		if ($a_check_scheduled_activation && !$this->active)
		{
			include_once("./Services/Calendar/classes/class.ilDateTime.php");
			$start = new ilDateTime($this->getActivationStart(), IL_CAL_DATETIME);
			$end = new ilDateTime($this->getActivationEnd(), IL_CAL_DATETIME);
			$now = new ilDateTime(time(), IL_CAL_UNIX);
			if (!ilDateTime::_before($now, $start) && !ilDateTime::_after($now, $end))
			{
				return true;
			}
		}
		return $this->active;
	}

	/**
	* lookup activation status
	*/
	function _lookupActive($a_id, $a_parent_type, $a_check_scheduled_activation = false)
	{
		global $ilDB;

		$set = $ilDB->queryF("SELECT active, activation_start, activation_end FROM page_object WHERE page_id = %s".
			" AND parent_type = %s",
				array("integer", "text"),
				array($a_id, $a_parent_type));
		$rec = $ilDB->fetchAssoc($set);
		$rec["n"] = ilUtil::now();

		if (!$rec["active"] && $a_check_scheduled_activation)
		{
			if ($rec["n"] >= $rec["activation_start"] &&
				$rec["n"] <= $rec["activation_end"])
			{
				return true;
			}
		}
		
		return $rec["active"];
	}

	/**
	* Check whether page is activated by time schedule
	*/
	static function _isScheduledActivation($a_id, $a_parent_type)
	{
		global $ilDB;
		
		$set = $ilDB->queryF("SELECT active, activation_start, activation_end FROM page_object WHERE page_id = %s".
			" AND parent_type = %s", array("integer", "text"),
			array($a_id, $a_parent_type));
		$rec = $ilDB->fetchAssoc($set);

		if (!$rec["active"] && $rec["activation_start"] != "")
		{
			return true;
		}
		
		return false;
	}

	/**
	* write activation status
	*/
	function _writeActive($a_id, $a_parent_type, $a_active, $a_reset_scheduled_activation = true)
	{
		global $ilDB;

		if ($a_reset_scheduled_activation)
		{
			$st = $ilDB->manipulateF("UPDATE page_object SET active = %s, activation_start = %s, ".
				" activation_end = %s WHERE page_id = %s".
				" AND parent_type = %s", array("boolean", "timestamp", "timestamp", "integer", "text"),
				array($a_active, null, null, $a_id, $a_parent_type));
		}
		else
		{
			$st = $ilDB->prepareManip("UPDATE page_object SET active = %s WHERE page_id = %s".
				" AND parent_type = %s", array("boolean", "integer", "text"),
				array($a_active, $a_id, $a_parent_type));
		}
	}

	/**
	* Set Activation Start.
	*
	* @param	date	$a_activationstart	Activation Start
	*/
	function setActivationStart($a_activationstart)
	{
		$this->activationstart = $a_activationstart;
	}

	/**
	* Get Activation Start.
	*
	* @return	date	Activation Start
	*/
	function getActivationStart()
	{
		return $this->activationstart;
	}

	/**
	* Set Activation End.
	*
	* @param	date	$a_activationend	Activation End
	*/
	function setActivationEnd($a_activationend)
	{
		$this->activationend = $a_activationend;
	}

	/**
	* Get Activation End.
	*
	* @return	date	Activation End
	*/
	function getActivationEnd()
	{
		return $this->activationend;
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

					//Page-Layout-Support
					case "PlaceHolder":
						require_once("./Services/COPage/classes/class.ilPCPlaceHolder.php");
						$placeholder = new ilPCPlaceHolder($this->dom);
						$placeholder->setNode($cont_node);
						$placeholder->setHierId($a_hier_id);
						$placeholder->setPcId($a_pc_id);
						return $placeholder;
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
	* Copy content of page; replace page components with copies
	* where necessary (e.g. questions)
	*/
	function copyXmlContent($a_new_question_copies = true)
	{
		$xml = $this->getXmlContent();
		$temp_dom = domxml_open_mem('<?xml version="1.0" encoding="UTF-8"?>'.$xml,
			DOMXML_LOAD_PARSING, $error);

		if(empty($error))
		{
			if ($a_new_question_copies)
			{
				$this->newQuestionCopies($temp_dom);
			}
		}
		$xml = $temp_dom->dump_mem(0, $this->encoding);
		$xml = eregi_replace("<\?xml[^>]*>","",$xml);
		$xml = eregi_replace("<!DOCTYPE[^>]*>","",$xml);

		return $xml;
	}

	/**
	* Replaces existing question content elements with
	* new copies
	*/
	function newQuestionCopies(&$temp_dom)
	{
		// Get question IDs
		$path = "//Question";
		$xpc = xpath_new_context($temp_dom);
		$res = & xpath_eval($xpc, $path);

		$q_ids = array();
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		for ($i = 0; $i < count ($res->nodeset); $i++)
		{
			$qref = $res->nodeset[$i]->get_attribute("QRef");
			
			$inst_id = ilInternalLink::_extractInstOfTarget($qref);
			$q_id = ilInternalLink::_extractObjIdOfTarget($qref);

			if (!($inst_id > 0))
			{
				if ($q_id > 0)
				{
					include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
					$question = assQuestion::_instanciateQuestion($q_id);

					// now copy this question and change reference to
					// new question id
					$duplicate_id = $question->duplicate(false);
					$res->nodeset[$i]->set_attribute("QRef", "il__qst_".$duplicate_id);
				}
			}
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
			"ed_edit_prop","ed_edit_files", "ed_edit_data", "ed_delete", "ed_moveafter", "ed_movebefore",
			"ed_go", "ed_new_row_after", "ed_new_row_before",
			"ed_new_col_after", "ed_new_col_before", "ed_delete_col",
			"ed_delete_row", "ed_class", "ed_width", "ed_align_left",
			"ed_align_right", "ed_align_center", "ed_align_left_float",
			"ed_align_right_float", "ed_delete_item", "ed_new_item_before",
			"ed_new_item_after", "ed_copy_clip", "please_select", "ed_split_page",
			"ed_item_up", "ed_item_down", "ed_row_up", "ed_row_down",
			"ed_col_left", "ed_col_right", "ed_split_page_next","ed_enable",
			"de_activate", "ed_insert_repobj", "ed_insert_map", "ed_insert_tabs",
			"ed_insert_pcqst", "empty_question", "ed_paste","question_placeh","media_placeh","text_placeh",
			"ed_insert_plach","question_placehl","media_placehl","text_placehl",
			"pc_flist", "pc_par", "pc_mob", "pc_qst", "pc_sec", "pc_dtab", "pc_tab",
			"pc_code", "pc_vacc", "pc_hacc", "pc_res", "pc_map", "pc_list", "ed_insert_incl", "pc_incl");

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
		require_once("./Services/COPage/classes/class.ilPCParagraph.php");
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
			$ilDB->quote($_GET["ref_id"],'integer'));
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
			$anchor = $res->nodeset[$i]->get_attribute("Anchor");
			$links[$target.":".$type.":".$targetframe.":".$anchor] =
				array("Target" => $target, "Type" => $type,
					"TargetFrame" => $targetframe, "Anchor" => $anchor);
					
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
	function collectFileItems($a_xml = "")
	{
//echo "<br>PageObject::collectFileItems[".$this->getId()."]";
		// determine all media aliases of the page
		if ($a_xml == "")
		{
			$xpc = xpath_new_context($this->dom);
			$path = "//FileItem/Identifier";
			$res =& xpath_eval($xpc, $path);
		}
		else
		{
			$doc = domxml_open_mem($a_xml);
			$xpc = xpath_new_context($doc);
			$path = "//FileItem/Identifier";
			$res =& xpath_eval($xpc, $path);
		}
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
			if (ilObject::_lookupType($mob_id) == "mob")
			{
				$mob_obj =& new ilObjMediaObject($mob_id);
				$mobs_xml .= $mob_obj->getXML(IL_MODE_OUTPUT);
			}
		}
//var_dump($mobs_xml);
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
		$this->dom->validate($error);
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
				require_once("./Services/COPage/classes/class.ilPageContent.php");
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
		
		// map areas
		$this->addHierIDs();
		$xpc = xpath_new_context($this->dom);
		$path = "//MediaAlias";
		$res =& xpath_eval($xpc, $path);
		
		require_once("Services/MediaObjects/classes/class.ilMediaItem.php");
		require_once("Services/COPage/classes/class.ilMediaAliasItem.php");

		for($i = 0; $i < count($res->nodeset); $i++)
		{
			$media_object_node = $res->nodeset[$i]->parent_node();
			$page_content_node = $media_object_node->parent_node();
			$c_hier_id = $page_content_node->get_attribute("HierId");

			// first check, wheter we got instance map areas -> take these
			$std_alias_item = new ilMediaAliasItem($this->dom,
				$c_hier_id, "Standard");
			$areas = $std_alias_item->getMapAreas();
			$correction_needed = false;
			if (count($areas) > 0)
			{
				// check if correction needed
				foreach($areas as $area)
				{
					if ($area["Type"] == "PageObject" ||
						$area["Type"] == "StructureObject")
					{
						$t = $area["Target"];
						$tid = _extractObjIdOfTarget($t);
						if ($a_from_to[$tid] > 0)
						{
							$correction_needed = true;
						}
					}
				}
			}
			else
			{
				$areas = array();

				// get object map areas and check whether at least one must
				// be corrected
				$oid = $res->nodeset[$i]->get_attribute("OriginId");
				if (substr($oid, 0, 4) =="il__")
				{
					$id_arr = explode("_", $oid);
					$id = $id_arr[count($id_arr) - 1];
	
					$mob = new ilObjMediaObject($id);
					$med_item = $mob->getMediaItem("Standard");
					$med_areas = $med_item->getMapAreas();

					foreach($med_areas as $area)
					{
						$link_type = ($area->getLinkType() == "int")
							? "IntLink"
							: "ExtLink";
						
						$areas[] = array(
							"Nr" => $area->getNr(),
							"Shape" => $area->getShape(),
							"Coords" => $area->getCoords(),
							"Link" => array(
								"LinkType" => $link_type,
								"Href" => $area->getHref(),
								"Title" => $area->getTitle(),
								"Target" => $area->getTarget(),
								"Type" => $area->getType(),
								"TargetFrame" => $area->getTargetFrame()
								)
							);

						if ($area->getType() == "PageObject" ||
							$area->getType() == "StructureObject")
						{
							$t = $area->getTarget();
							$tid = ilInternalLink::_extractObjIdOfTarget($t);
							if ($a_from_to[$tid] > 0)
							{
								$correction_needed = true;
							}
//var_dump($a_from_to);
						}
					}
				}
			}
			
			// correct map area links
			if ($correction_needed)
			{
				$std_alias_item->deleteAllMapAreas();
				foreach($areas as $area)
				{
					if ($area["Link"]["LinkType"] == "IntLink")
					{
						$target = $area["Link"]["Target"];
						$type = $area["Link"]["Type"];
						$obj_id = ilInternalLink::_extractObjIdOfTarget($target);
						if ($a_from_to[$obj_id] > 0)
						{
							if ($type == "PageObject" && ilLMObject::_lookupType($a_from_to[$obj_id]) == "pg")
							{
								$area["Link"]["Target"] = "il__pg_".$a_from_to[$obj_id];
							}
							if ($type == "StructureObject" && ilLMObject::_lookupType($a_from_to[$obj_id]) == "st")
							{
								$area["Link"]["Target"] = "il__st_".$a_from_to[$obj_id];
							}
						}
					}
					
					$std_alias_item->addMapArea($area["Shape"], $area["Coords"],
						$area["Link"]["Title"],
						array(	"Type" => $area["Link"]["Type"],
								"TargetFrame" => $area["Link"]["TargetFrame"],
								"Target" => $area["Link"]["Target"],
								"Href" => $area["Link"]["Href"],
								"LinkType" => $area["Link"]["LinkType"],
						));
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
		
		$iel = $this->containsDeactivatedElements($this->getXMLContent());
		$inl = $this->containsIntLinks($this->getXMLContent());
				
		// create object
		/* $query = "INSERT INTO page_object (page_id, parent_id, content, parent_type, create_user, last_change_user, inactive_elements, int_links, created, last_change) VALUES ".
			"(".$ilDB->quote($this->getId()).",".
			$ilDB->quote($this->getParentId()).",".
			$ilDB->quote($this->getXMLContent()).
			", ".$ilDB->quote($this->getParentType()).
			", ".$ilDB->quote($ilUser->getId()).
			", ".$ilDB->quote($ilUser->getId()).
			", ".$ilDB->quote($iel, "integer")." ".
			", ".$ilDB->quote($inl, "integer")." ".
			", now(), now())"; */
			
		$ilDB->insert("page_object", array(
			"page_id" => array("integer", $this->getId()),
			"parent_id" => array("integer", $this->getParentId()),
			"content" => array("clob", $this->getXMLContent()),
			"parent_type" => array("text", $this->getParentType()),
			"create_user" => array("integer", $ilUser->getId()),
			"last_change_user" => array("integer", $ilUser->getId()),
			"inactive_elements" => array("integer", $iel),
			"int_links" => array("integer", $inl),
			"created" => array("timestamp", ilUtil::now()),
			"last_change" => array("timestamp", ilUtil::now())
			));

// todo: put this into insert
/*		if(!$ilDB->checkQuerySize($query))
		{
			$this->ilias->raiseError($lng->txt("check_max_allowed_packet_size"),$this->ilias->error_obj->MESSAGE);
			return false;
		}*/

//		$ilDB->query($query);
	}


	/**
	* updates page object with current xml content
	*/
	function updateFromXML()
	{
		global $lng, $ilDB, $ilUser;

//echo "<br>PageObject::updateFromXML[".$this->getId()."]";
//echo "update:".ilUtil::prepareDBString(($this->getXMLContent())).":<br>";
//echo "update:".htmlentities($this->getXMLContent()).":<br>";

		$iel = $this->containsDeactivatedElements($this->getXMLContent());
		$inl = $this->containsIntLinks($this->getXMLContent());

		/*$query = "UPDATE page_object ".
			"SET content = ".$ilDB->quote($this->getXMLContent())." ".
			", parent_id = ".$ilDB->quote($this->getParentId())." ".
			", last_change_user = ".$ilDB->quote($ilUser->getId())." ".
			", last_change = now() ".
			", active = ".$ilDB->quote($this->getActive())." ".
			", activation_start = ".$ilDB->quote($this->getActivationStart())." ".
			", activation_end = ".$ilDB->quote($this->getActivationEnd())." ".
			", inactive_elements = ".$ilDB->quote($iel, "integer")." ".
			", int_links = ".$ilDB->quote($inl, "integer")." ".
			"WHERE page_id = ".$ilDB->quote($this->getId())." AND parent_type=".
			$ilDB->quote($this->getParentType());*/
			
		$ilDB->update("page_object", array(
			"content" => array("clob", $this->getXMLContent()),
			"parent_id" => array("integer", $this->getParentId()),
			"last_change_user" => array("integer", $ilUser->getId()),
			"last_change" => array("timestamp", ilUtil::now()),
			"active" => array("integer", $this->getActive()),
			"activation_start" => array("timestamp", $this->getActivationStart()),
			"activation_end" => array("timestamp", $this->getActivationEnd()),
			"inactive_elements" => array("integer", $iel),
			"int_links" => array("integer", $inl),
			), array(
			"page_id" => array("integer", $this->getId()),
			"parent_type" => array("text", $this->getParentType())
			));

// todo: move this to update
/*		if(!$ilDB->checkQuerySize($query))
		{
			$this->ilias->raiseError($lng->txt("check_max_allowed_packet_size"),$this->ilias->error_obj->MESSAGE);
			return false;
		}
		$ilDB->query($query);*/

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
//echo "<br>PageObject::update:".htmlentities($this->getXMLFromDom());
		// add missing pc ids
		if (!$this->checkPCIds())
		{
			$this->insertPCIds();
		}

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
				"page_id = ".$ilDB->quote($this->getId(), "integer")." AND ".
				"parent_type = ".$ilDB->quote($this->getParentType(), "text"));
			$last_nr_set = $ilDB->query("SELECT max(nr) as mnr FROM page_history WHERE ".
				"page_id = ".$ilDB->quote($this->getId(), "integer")." AND ".
				"parent_type = ".$ilDB->quote($this->getParentType(), "text"));
			$last_nr = $ilDB->fetchAssoc($last_nr_set);
			if ($old_rec = $ilDB->fetchAssoc($old_set))
			{
				// only save, if something has changed and not in layout mode
				if (($content != $old_rec["content"]) && !$a_no_history &&
					!$this->history_saved && !$this->layout_mode)
				{
					if ($old_rec["content"] != "<PageObject></PageObject>")
					{
						$ilDB->manipulateF("DELETE FROM page_history WHERE ".
							"page_id = %s AND parent_type = %s AND hdate = %s",
							array("integer", "text", "timestamp"),
							array($old_rec["page_id"], $old_rec["parent_type"], $old_rec["last_change"]));
						$ilDB->insert("page_history", array(
							"page_id" => 		array("integer", $old_rec["page_id"]),
							"parent_type" => 	array("text", $old_rec["parent_type"]),
							"hdate" => 			array("timestamp", $old_rec["last_change"]),
							"parent_id" => 		array("integer", $old_rec["parent_id"]),
							"content" => 		array("clob", $old_rec["content"]),
							"user_id" => 		array("integer", $old_rec["last_change_user"]),
							"ilias_version" => 	array("text", ILIAS_VERSION_NUMERIC),
							"nr" => 			array("integer", (int) $last_nr["mnr"] + 1)
							));
						/*$h_query = "REPLACE INTO page_history ".
							"(page_id, parent_type, hdate, parent_id, content, user_id, ilias_version, nr) VALUES (".
							$ilDB->quote($old_rec["page_id"]).",".
							$ilDB->quote($old_rec["parent_type"]).",".
							$ilDB->quote($old_rec["last_change"]).",".
							$ilDB->quote($old_rec["parent_id"]).",".
							$ilDB->quote($old_rec["content"]).",".
							$ilDB->quote($old_rec["last_change_user"]).",".
							$ilDB->quote(ILIAS_VERSION_NUMERIC).",".
							$ilDB->quote($last_nr["mnr"] + 1).")";
//echo "<br><br>+$a_no_history+$h_query";
						$ilDB->query($h_query);*/
						$this->saveMobUsage($old_rec["content"], $last_nr["mnr"] + 1);
						$this->saveStyleUsage($old_rec["content"], $last_nr["mnr"] + 1);
						$this->saveFileUsage($old_rec["content"], $last_nr["mnr"] + 1);
						$this->history_saved = true;		// only save one time
					}
					else
					{
						$this->history_saved = true;		// do not save on first change
					}
				}
			}
//echo htmlentities($content);
			$em = (trim($content) == "<PageObject/>")
				? 1
				: 0;
				
			$iel = $this->containsDeactivatedElements($content);
			$inl = $this->containsIntLinks($content);
			/*$query = "UPDATE page_object ".
				"SET content = ".$ilDB->quote($content)." ".
				", parent_id= ".$ilDB->quote($this->getParentId())." ".
				", last_change_user= ".$ilDB->quote($ilUser->getId())." ".
				", last_change = now() ".
				", is_empty = ".$ilDB->quote($em, "integer")." ".
				", active = ".$ilDB->quote($this->getActive())." ".
				", activation_start = ".$ilDB->quote($this->getActivationStart())." ".
				", activation_end = ".$ilDB->quote($this->getActivationEnd())." ".
				", inactive_elements = ".$ilDB->quote($iel, "integer")." ".
				", int_links = ".$ilDB->quote($inl, "integer")." ".
				" WHERE page_id = ".$ilDB->quote($this->getId()).
				" AND parent_type= ".$ilDB->quote($this->getParentType());*/
				
			$ilDB->update("page_object", array(
				"content" => array("clob", $content),
				"parent_id" => array("integer", $this->getParentId()),
				"last_change_user" => array("integer", $ilUser->getId()),
				"last_change" => array("timestamp", ilUtil::now()),
				"is_empty" => array("integer", $em),
				"active" => array("integer", $this->getActive()),
				"activation_start" => array("timestamp", $this->getActivationStart()),
				"activation_end" => array("timestamp", $this->getActivationEnd()),
				"inactive_elements" => array("integer", $iel),
				"int_links" => array("integer", $inl),
				), array(
				"page_id" => array("integer", $this->getId()),
				"parent_type" => array("text", $this->getParentType())
				));
				
// todo put this into update function
/*			if(!$this->ilias->db->checkQuerySize($query))
			{
				$this->ilias->raiseError($lng->txt("check_max_allowed_packet_size"),$this->ilias->error_obj->MESSAGE);
				return false;
			}*/

//			$this->ilias->db->query($query);
			
			// handle media object usage
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			$mob_ids = ilObjMediaObject::_getMobsOfObject(
				$this->getParentType().":pg", $this->getId());
			$this->saveMobUsage($this->getXMLFromDom());
			$this->saveMetaKeywords($this->getXMLFromDom());
			foreach($mob_ids as $mob)	// check, whether media object can be deleted
			{
				if (ilObject::_exists($mob) && ilObject::_lookupType($mob) == "mob")
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
						if ($file_obj->getMode() == "filelist")		// non-repository object
						{
							$file_obj->delete();
						}
					}
				}
			}
			
			// save style usage
			$this->saveStyleUsage($this->getXMLFromDom());
			
			// save internal link information
			$this->saveInternalLinks($this->getXMLFromDom());
			$this->saveAnchors($this->getXMLFromDom());
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

		// delete style usages
		$this->saveStyleUsage("<dummy></dummy>");

		// delete internal links
		$this->saveInternalLinks("<dummy></dummy>");

		// delete anchors
		$this->saveAnchors("<dummy></dummy>");

		// delete all file usages
		include_once("./Modules/File/classes/class.ilObjFile.php");
		ilObjFile::_deleteAllUsages($this->getParentType().":pg", $this->getId());

		// delete page_object entry
		$ilDB->manipulate("DELETE FROM page_object ".
			"WHERE page_id = ".$ilDB->quote($this->getId(), "integer").
			" AND parent_type= ".$ilDB->quote($this->getParentType(), "text"));
		//$this->ilias->db->query($query);

		// delete media objects
		foreach ($mobs as $mob_id)
		{
			if(ilObject::_lookupType($mob_id) != 'mob')
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Type mismatch. Ignoring mob with id: '.$mob_id);
				continue;
			}
			
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
	* save all keywords
	*
	* @param	string		$a_xml		xml data of page
	*/
	function saveMetaKeywords($a_xml)
	{
		// not nice, should be set by context per method
		if ($this->getParentType() == "gdf" ||
			$this->getParentType() == "lm" ||
			$this->getParentType() == "dbk")
		{
			$doc = domxml_open_mem($a_xml);

			// get existing keywords
			$keywords = array();
			
			// find all Keyw tags
			$xpc = xpath_new_context($doc);
			$path = "//Keyw";
			$res = xpath_eval($xpc, $path);
			for ($i=0; $i < count($res->nodeset); $i++)
			{
				$k =  trim(strip_tags($res->nodeset[$i]->get_content()));
				if (!in_array($k, $keywords))
				{
					$keywords[] = $k;
				}
			}
			
			$meta_type = ($this->getParentType() == "gdf")
				? "gdf"
				: "pg";
			$meta_rep_id = $this->getParentId();
			$meta_id = $this->getId();
			
			include_once("./Services/MetaData/classes/class.ilMD.php");
			$md_obj = new ilMD($meta_rep_id, $meta_id, $meta_type);
			$mkeywords = array();
			$lang = "";
			if(is_object($md_section = $md_obj->getGeneral()))
			{
				foreach($ids = $md_section->getKeywordIds() as $id)
				{
					$md_key = $md_section->getKeyword($id);
					$mkeywords[] = strtolower($md_key->getKeyword());
					if ($lang == "")
					{
						$lang = $md_key->getKeywordLanguageCode();
					}
				}
			}
			if ($lang == "")
			{
				foreach($ids = $md_section->getLanguageIds() as $id)
				{
					$md_lang = $md_section->getLanguage($id);
					if ($lang == "")
					{
						$lang = $md_lang->getLanguageCode();
					}
				}
			}
			foreach ($keywords as $k)
			{
				if (!in_array(strtolower($k), $mkeywords))
				{
					if (trim($k) != "" && $lang != "")
					{
						$md_key = $md_section->addKeyword();
						$md_key->setKeyword(ilUtil::stripSlashes($k));
						$md_key->setKeywordLanguage(new ilMDLanguageItem($lang));
						$md_key->save();
					}
					$mkeywords[] = strtolower($k);
				}
			}
		}
	}

	/**
	* save all usages of media objects (media aliases, media objects, internal links)
	*
	* @param	string		$a_xml		xml data of page
	*/
	function saveMobUsage($a_xml, $a_old_nr = 0)
	{
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
		ilObjMediaObject::_deleteAllUsages($this->getParentType().":pg", $this->getId(), $a_old_nr);
		foreach($usages as $mob_id => $val)
		{
			ilObjMediaObject::_saveUsage($mob_id, $this->getParentType().":pg", $this->getId(), $a_old_nr);
		}
	}

	/**
	* save file usages
	*/
	function saveFileUsage($a_xml = "", $a_old_nr = 0)
	{
		$file_ids = $this->collectFileItems($a_xml, $a_old_nr);
		include_once("./Modules/File/classes/class.ilObjFile.php");
		ilObjFile::_deleteAllUsages($this->getParentType().":pg", $this->getId(), $a_old_nr);
		foreach($file_ids as $file_id)
		{
			ilObjFile::_saveUsage($file_id, $this->getParentType().":pg", $this->getId(), $a_old_nr);
		}
	}

	/**
	* Save all style class/template usages
	*
	* @param	string		$a_xml		xml data of page
	*/
	function saveStyleUsage($a_xml, $a_old_nr = 0)
	{
		global $ilDB;

		$doc = domxml_open_mem($a_xml);

		// media aliases
		$xpc = xpath_new_context($doc);
		$path = "//Paragraph | //Section | //MediaAlias | //FileItem".
			" | //Table | //TableData | //Tabs | //List";
		$res = xpath_eval($xpc, $path);
		$usages = array();
		for ($i=0; $i < count($res->nodeset); $i++)
		{
			switch ($res->nodeset[$i]->node_name())
			{
				case "Paragraph":
					$sname = $res->nodeset[$i]->get_attribute("Characteristic");
					$stype = "text_block";
					$template = 0;
					break;

				case "Section":
					$sname = $res->nodeset[$i]->get_attribute("Characteristic");
					$stype = "section";
					$template = 0;
					break;

				case "MediaAlias":
					$sname = $res->nodeset[$i]->get_attribute("Class");
					$stype = "media_cont";
					$template = 0;
					break;

				case "FileItem":
					$sname = $res->nodeset[$i]->get_attribute("Class");
					$stype = "flist_li";
					$template = 0;
					break;

				case "Table":
					$sname = $res->nodeset[$i]->get_attribute("Template");
					if ($sname == "")
					{
						$sname = $res->nodeset[$i]->get_attribute("Class");
						$stype = "table";
						$template = 0;
					}
					else
					{
						$stype = "table";
						$template = 1;
					}
					break;

				case "TableData":
					$sname = $res->nodeset[$i]->get_attribute("Class");
					$stype = "table_cell";
					$template = 0;
					break;

				case "Tabs":
					$sname = $res->nodeset[$i]->get_attribute("Template");
					if ($sname != "")
					{
						if ($res->nodeset[$i]->get_attribute("Type") == "HorizontalAccordion")
						{
							$stype = "haccordion";
						}
						if ($res->nodeset[$i]->get_attribute("Type") == "VerticalAccordion")
						{
							$stype = "vaccordion";
						}
					}
					$template = 1;
					break;
				
				case "List":
					$sname = $res->nodeset[$i]->get_attribute("Class");
					if ($res->nodeset[$i]->get_attribute("Type") == "Ordered")
					{
						$stype = "list_o";
					}
					else
					{
						$stype = "list_u";
					}
					$template = 0;
					break;
			}
			if ($sname != "" &&  $stype != "")
			{
				$usages[$sname.":".$stype.":".$template] = array("sname" => $sname,
					"stype" => $stype, "template" => $template);
			}
		}
		
		$ilDB->manipulate("DELETE FROM page_style_usage WHERE ".
			" page_id = ".$ilDB->quote($this->getId(), "integer").
			" AND page_type = ".$ilDB->quote($this->getParentType(), "text").
			" AND page_nr = ".$ilDB->quote($a_old_nr, "integer")
			);
		
		foreach ($usages as $u)
		{
			$ilDB->manipulate("INSERT INTO page_style_usage ".
				"(page_id, page_type, page_nr, template, stype, sname) VALUES (".
				$ilDB->quote($this->getId(), "integer").",".
				$ilDB->quote($this->getParentType(), "text").",".
				$ilDB->quote($a_old_nr, "integer").",".
				$ilDB->quote($u["template"], "integer").",".
				$ilDB->quote($u["stype"], "text").",".
				$ilDB->quote($u["sname"], "text").
				")");
		}
	}

	/**
	* Get last update of included elements (media objects and files).
	* This is needed for cache logic, cache must be reloaded if anything has changed.
	*/
	function getLastUpdateOfIncludedElements()
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$mobs = ilObjMediaObject::_getMobsOfObject($this->getParentType().":pg",
			$this->getId());
		$files = ilObjFile::_getFilesOfObject($this->getParentType().":pg",
			$this->getId());
		$objs = array_merge($mobs, $files);
		return ilObject::_getLastUpdateOfObjects($objs);
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
	* save anchors
	*
	* @param	string		xml page code
	*/
	function saveAnchors($a_xml)
	{
		$doc = domxml_open_mem($a_xml);

		ilPageObject::_deleteAnchors($this->getParentType(), $this->getId());

		// get all anchors
		$xpc = xpath_new_context($doc);
		$path = "//Anchor";
		$res =& xpath_eval($xpc, $path);
		$saved = array();
		for ($i=0; $i < count($res->nodeset); $i++)
		{
			$name = $res->nodeset[$i]->get_attribute("Name");
			if (trim($name) != "" && !in_array($name, $saved))
			{
				ilPageObject::_saveAnchor($this->getParentType(), $this->getId(), $name);
				$saved[] = $name;
			}
		}

	}

	/**
	* Delete anchors of a page
	*/
	static function _deleteAnchors($a_parent_type, $a_page_id)
	{
		global $ilDB;
		
		$st = $ilDB->prepareManip("DELETE FROM page_anchor WHERE page_parent_type = ? ".
			" AND page_id = ?", array("text", "integer"));
		$ilDB->execute($st, array($a_parent_type, $a_page_id));
	}
	
	/**
	* Save an anchor
	*/
	static function _saveAnchor($a_parent_type, $a_page_id, $a_anchor_name)
	{
		global $ilDB;
		
		$st = $ilDB->prepareManip("INSERT INTO page_anchor (page_parent_type, page_id, anchor_name) ".
			" VALUES (?,?,?) ", array("text", "integer", "text"));
		$ilDB->execute($st, array($a_parent_type, $a_page_id, $a_anchor_name));
	}

	/**
	* Read anchors of a page
	*/
	static function _readAnchors($a_parent_type, $a_page_id)
	{
		global $ilDB;
		
		$st = $ilDB->prepare("SELECT * FROM page_anchor WHERE page_parent_type = ? ".
			" AND page_id = ?", array("text", "integer"));
		$set = $ilDB->execute($st, array($a_parent_type, $a_page_id));
		$anchors = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$anchors[] = $rec["anchor_name"];
		}
		return $anchors;
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
	* Copy contents to clipboard and cut them from the page
	*
	* @param	string		$a_hids		array of hierarchical ids of content objects
	*/
	function cutContents($a_hids)
	{
		$this->copyContents($a_hids);
		return $this->deleteContents($a_hids);
	}
	
	/**
	* Copy contents to clipboard
	*
	* @param	string		$a_hids		array of hierarchical ids of content objects
	*/
	function copyContents($a_hids)
	{
		global $ilUser;
//var_dump($a_hids);
		if (!is_array($a_hids))
		{
			return;
		}

		$time = date("Y-m-d H:i:s", time());
		
		$hier_ids = array();
		$skip = array();
		foreach($a_hids as $a_hid)
		{
			if ($a_hid == "")
			{
				continue;
			}
			$a_hid = explode(":", $a_hid);
			
			// check, whether new hid is child of existing one or vice versa
			reset($hier_ids);
			foreach($hier_ids as $h)
			{
				if($h."_" == substr($a_hid[0], 0, strlen($h) + 1))
				{
					$skip[] = $a_hid[0];
				}
				if($a_hid[0]."_" == substr($h, 0, strlen($a_hid[0]) + 1))
				{
					$skip[] = $h;
				}
			}
			$pc_id[$a_hid[0]] = $a_hid[1];
			if ($a_hid[0] != "")
			{
				$hier_ids[$a_hid[0]] = $a_hid[0];
			}
		}
		foreach ($skip as $s)
		{
			unset($hier_ids[$s]);
		}
		include_once("./Services/COPage/classes/class.ilPageContent.php");
		$hier_ids = ilPageContent::sortHierIds($hier_ids);
		$nr = 1;
		foreach($hier_ids as $hid)
		{
			$curr_node = $this->getContentNode($hid, $pc_id[$hid]);
			if (is_object($curr_node))
			{
				if ($curr_node->node_name() == "PageContent")
				{
					$content = $this->dom->dump_node($curr_node);
					// remove pc and hier ids
					$content = eregi_replace("PCID=\"[a-z0-9]*\"","",$content);
					$content = eregi_replace("HierId=\"[a-z0-9_]*\"","",$content);
					
					$ilUser->addToPCClipboard($content, $time, $nr);
					$nr++;
				}
			}
		}
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::setAction("copy");
	}

	/**
	* Paste contents from pc clipboard
	*/
	function pasteContents($a_hier_id)
	{
		global $ilUser;
		
		$a_hid = explode(":", $a_hier_id);
		$content = $ilUser->getPCClipboardContent();
		
		// we insert from last to first, because we insert all at the
		// same hier_id
		for ($i = count($content) - 1; $i >= 0; $i--)
		{

			$c = $content[$i];
			$temp_dom = domxml_open_mem('<?xml version="1.0" encoding="UTF-8"?>'.$c,
				DOMXML_LOAD_PARSING, $error);
			if(empty($error))
			{
				$this->newQuestionCopies($temp_dom);
				$xpc = xpath_new_context($temp_dom);
				$path = "//PageContent";
				$res = xpath_eval($xpc, $path);
				if (count($res->nodeset) > 0)
				{
					$new_pc_node = $res->nodeset[0];
					$cloned_pc_node = $new_pc_node->clone_node (true);
					$cloned_pc_node->unlink_node ($cloned_pc_node);
					$this->insertContentNode ($cloned_pc_node, $a_hid[0],
						IL_INSERT_AFTER, $a_hid[1]);
				}
			}
			else
			{
//var_dump($error);
			}
		}
		$e = $this->update();
//var_dump($e);
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
		
		//check for PlaceHolder to remove in EditMode-keep in Layout Mode
		if (!$this->getLayoutMode()) {
			$sub_nodes = $curr_node->child_nodes() ;
			foreach ( $sub_nodes as $sub_node ) {
				if ($sub_node->node_name() == "PlaceHolder") {
					$curr_node->unlink_node();
					$this->update();
				}
			}
		}	
	}

	/**
	* insert a content node before/after a sibling or as first child of a parent
	*/
	function insertContentNode(&$a_cont_node, $a_pos, $a_mode = IL_INSERT_AFTER, $a_pcid = "")
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

		switch ($a_mode)
		{
			// insert new node after sibling at $a_pos
			case IL_INSERT_AFTER:
				//$new_node =& $a_cont_obj->getNode();
				if($succ_node = $curr_node->next_sibling())
				{
					$a_cont_node = $succ_node->insert_before($a_cont_node, $succ_node);
				}
				else
				{
					$a_cont_node = $parent_node->append_child($a_cont_node);
				}
				//$a_cont_obj->setNode($new_node);
				break;

			case IL_INSERT_BEFORE:
				//$new_node =& $a_cont_obj->getNode();
				$succ_node = $this->getContentNode($a_pos);
				$a_cont_node = $succ_node->insert_before($a_cont_node, $succ_node);
				//$a_cont_obj->setNode($new_node);
				break;

			// insert new node as first child of parent $a_pos (= $a_parent)
			case IL_INSERT_CHILD:
				//$new_node =& $a_cont_obj->getNode();
				if($cnt_parent_childs == 0)
				{
					$a_cont_node = $parent_node->append_child($a_cont_node);
				}
				else
				{
					$a_cont_node = $parent_childs[0]->insert_before($a_cont_node, $parent_childs[0]);
				}
				//$a_cont_obj->setNode($new_node);
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
//			$content = str_replace("&", "&amp;", $content);					
//var_dump($content);
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
		require_once("./Services/COPage/classes/class.ilPCParagraph.php");
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
	
	/**
	* Get page contents hashes
	*/
	function getQuestionIds()
	{
		$this->builddom();
		$mydom = $this->dom;
		
		// Get question IDs
		$path = "//Question";
		$xpc = xpath_new_context($mydom);
		$res = & xpath_eval($xpc, $path);

		$q_ids = array();
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		for ($i = 0; $i < count ($res->nodeset); $i++)
		{
			$qref = $res->nodeset[$i]->get_attribute("QRef");
			
			$inst_id = ilInternalLink::_extractInstOfTarget($qref);
			$obj_id = ilInternalLink::_extractObjIdOfTarget($qref);

			if (!($inst_id > 0))
			{
				if ($obj_id > 0)
				{
					$q_ids[] = $obj_id;
				}
			}
		}

		return $q_ids;
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
		var_dump($fo);
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

		/*$query = "SELECT * FROM page_object WHERE page_id = ".
			$ilDB->quote($a_id)." AND ".
			" parent_type = ".$ilDB->quote($a_parent_type)." AND ".
			" content LIKE '% Enabled=\"False\"%'";*/
		$query = "SELECT * FROM page_object WHERE page_id = ".
			$ilDB->quote($a_id, "integer")." AND ".
			" parent_type = ".$ilDB->quote($a_parent_type, "text")." AND ".
			" inactive_elements = ".$ilDB->quote(1, "integer");
		$obj_set = $ilDB->query($query);
		
		if ($obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check whether content contains deactivated elements
	 *
	 * @param
	 * @return
	 */
	function containsDeactivatedElements($a_content)
	{
		if (strpos($a_content,  " Enabled=\"False\""))
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
			" WHERE page_id = ".$ilDB->quote($this->getId(), "integer").
			" AND parent_type = ".$ilDB->quote($this->getParentType(), "text").
			" ORDER BY hdate DESC";
		
		$hset = $ilDB->query($h_query);
		$hentries = array();

		while ($hrec = $ilDB->fetchAssoc($hset))
		{
			$hrec["sortkey"] = (int) $hrec["nr"];
			$hrec["user"] = (int) $hrec["user_id"];
			$hentries[] = $hrec;
		}
//var_dump($hentries);
		return $hentries;
	}
	
	/**
	* Get History Entry
	*/
	function getHistoryEntry($a_old_nr)
	{
		global $ilDB;
		
		$res = $ilDB->queryF("SELECT * FROM page_history ".
			" WHERE page_id = %s ".
			" AND parent_type = %s ".
			" AND nr = %s",
			array("integer", "text", "integer"),
			array($this->getId(), $this->getParentType(), $a_old_nr));
		if ($hrec = $ilDB->fetchAssoc($res))
		{
			return $hrec;
		}
		
		return false;
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
		
		// determine previous entry
		$res = $ilDB->query("SELECT MAX(nr) mnr FROM page_history ".
			" WHERE page_id = ".$ilDB->quote($this->getId(), "integer").
			" AND parent_type = ".$ilDB->quote($this->getParentType(), "text").
			" AND nr < ".$ilDB->quote((int) $a_nr, "integer"));
		$row = $ilDB->fetchAssoc($res);
		if ($row["mnr"] > 0)
		{
			$res = $ilDB->query("SELECT * FROM page_history ".
				" WHERE page_id = ".$ilDB->quote($this->getId(), "integer").
				" AND parent_type = ".$ilDB->quote($this->getParentType(), "text").
				" AND nr = ".$ilDB->quote((int) $row["mnr"], "integer"));
			$row = $ilDB->fetchAssoc($res);
			$ret["previous"] = $row;
		}
		
		// determine next entry
		$res = $ilDB->query("SELECT MIN(nr) mnr FROM page_history ".
			" WHERE page_id = ".$ilDB->quote($this->getId(), "integer").
			" AND parent_type = ".$ilDB->quote($this->getParentType(), "text").
			" AND nr > ".$ilDB->quote((int) $a_nr, "integer"));
		$row = $ilDB->fetchAssoc($res);
		if ($row["mnr"] > 0)
		{
			$res = $ilDB->query("SELECT * FROM page_history ".
				" WHERE page_id = ".$ilDB->quote($this->getId(), "integer").
				" AND parent_type = ".$ilDB->quote($this->getParentType(), "text").
				" AND nr = ".$ilDB->quote((int) $row["mnr"], "integer"));
			$row = $ilDB->fetchAssoc($res);
			$ret["next"] = $row;
		}

		// current
		$res = $ilDB->query("SELECT * FROM page_history ".
			" WHERE page_id = ".$ilDB->quote($this->getId(), "integer").
			" AND parent_type = ".$ilDB->quote($this->getParentType(), "text").
			" AND nr = ".$ilDB->quote((int) $a_nr, "integer"));
		$row = $ilDB->fetchAssoc($res);
		$ret["current"] = $row;

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
		
		$ilDB->manipulate("UPDATE page_object ".
			" SET view_cnt = view_cnt + 1 ".
			" WHERE page_id = ".$ilDB->quote($this->getId(), "integer").
			" AND parent_type = ".$ilDB->quote($this->getParentType(), "text"));
		//$ilDB->query($q);
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
		$limit_ts = date('Y-m-d H:i:s', time() - ($a_period * 24 * 60 * 60));
		$q = "SELECT * FROM page_object ".
			" WHERE parent_id = ".$ilDB->quote($a_parent_id, "integer").
			" AND parent_type = ".$ilDB->quote($a_parent_type, "text").
			" AND last_change >= ".$ilDB->quote($limit_ts, "timestamp");
		//	" AND (TO_DAYS(now()) - TO_DAYS(last_change)) <= ".((int)$a_period);
		$set = $ilDB->query($q);
		while($page = $ilDB->fetchAssoc($set))
		{
			$page_changes[] = array("date" => $page["last_change"],
				"id" => $page["page_id"], "type" => "page",
				"user" => $page["last_change_user"]);
		}

		$and_str = "";
		if ($a_period > 0)
		{
			$limit_ts = date('Y-m-d H:i:s', time() - ($a_period * 24 * 60 * 60));
			$and_str = " AND hdate >= ".$ilDB->quote($limit_ts, "timestamp")." ";
		}

		$q = "SELECT * FROM page_history ".
			" WHERE parent_id = ".$ilDB->quote($a_parent_id, "integer").
			" AND parent_type = ".$ilDB->quote($a_parent_type, "text").
			$and_str;
		$set = $ilDB->query($q);
		while ($page = $ilDB->fetchAssoc($set))
		{
			$page_changes[] = array("date" => $page["hdate"],
				"id" => $page["page_id"], "type" => "hist", "nr" => $page["nr"],
				"user" => $page["user_id"]);
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
			" WHERE parent_id = ".$ilDB->quote($a_parent_id, "integer").
			" AND parent_type = ".$ilDB->quote($a_parent_type, "text");
		$set = $ilDB->query($q);
		$pages = array();
		while ($page = $ilDB->fetchAssoc($set))
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
			" WHERE parent_id = ".$ilDB->quote($a_parent_id, "integer").
			" AND parent_type = ".$ilDB->quote($a_parent_type, "text").
			" ORDER BY created DESC";
		$set = $ilDB->query($q);
		while($page = $ilDB->fetchAssoc($set))
		{
			if ($page["created"] != "")
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
		$set = $ilDB->queryF("SELECT last_change_user FROM page_object ".
			" WHERE parent_id = %s AND parent_type = %s ".
			" AND last_change_user != %s",
			array("integer", "text", "integer"),
			array($a_parent_id, $a_parent_type, 0));

		while ($page = $ilDB->fetchAssoc($set))
		{
			$contributors[$page["last_change_user"]][$page["page_id"]] = 1;
		}

		$set = $ilDB->queryF("SELECT count(*) as cnt, page_id, user_id FROM page_history ".
			" WHERE parent_id = %s AND parent_type = %s AND user_id != %s ".
			" GROUP BY page_id, user_id ",
			array("integer", "text", "integer"),
			array($a_parent_id, $a_parent_type, 0));
		while ($hpage = $ilDB->fetchAssoc($set))
		{
			$contributors[$hpage["user_id"]][$hpage["page_id"]] =
				$contributors[$hpage["user_id"]][$hpage["page_id"]] + $hpage["cnt"];
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
	* Get all contributors for parent object
	*
	* @param	string	$a_parent_type	Parent Type
	* @param	int		$a_parent_id	Parent ID
	*/
	static function getPageContributors($a_parent_type, $a_page_id)
	{
		global $ilDB;
		
		$contributors = array();
		$set = $ilDB->queryF("SELECT last_change_user FROM page_object ".
			" WHERE page_id = %s AND parent_type = %s ".
			" AND last_change_user != %s",
			array("integer", "text", "integer"),
			array($a_page_id, $a_parent_type, 0));

		while ($page = $ilDB->fetchAssoc($set))
		{
			$contributors[$page["last_change_user"]] = 1;
		}

		$set = $ilDB->queryF("SELECT count(*) as cnt, page_id, user_id FROM page_history ".
			" WHERE page_id = %s AND parent_type = %s AND user_id != %s ".
			" GROUP BY user_id, page_id ",
			array("integer", "text", "integer"),
			array($a_page_id, $a_parent_type, 0));
		while ($hpage = $ilDB->fetchAssoc($set))
		{
			$contributors[$hpage["user_id"]] =
				$contributors[$hpage["user_id"]] + $hpage["cnt"];
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
		
		$ilDB->update("page_object", array(
			"rendered_content" => array("clob", $a_content),
			"render_md5" => array("text", $a_md5),
			"rendered_time" => array("timestamp", ilUtil::now())
			), array(
			"page_id" => array("integer", $this->getId()),
			"parent_type" => array("text", $this->getParentType())
			));
		/*$st = $ilDB->prepareManip("UPDATE page_object ".
			" SET rendered_content = ?, render_md5 = ?, rendered_time = now()".
			" WHERE page_id = ?  AND parent_type = ?",
			array("text", "text", "integer", "text"));
		$r = $ilDB->execute($st,
			array($a_content, $a_md5, $this->getId(), $this->getParentType()));*/
	}

	/**
	* Get all pages for parent object that contain internal links
	*
	* @param	string	$a_parent_type	Parent Type
	* @param	int		$a_parent_id	Parent ID
	* @param	int		$a_period		Time Period
	*/
	static function getPagesWithLinks($a_parent_type, $a_parent_id)
	{
		global $ilDB;
		
		$page_changes = array();
		
		$q = "SELECT * FROM page_object ".
			" WHERE parent_id = ".$ilDB->quote($a_parent_id, "integer").
			" AND parent_type = ".$ilDB->quote($a_parent_type, "text").
			" AND int_links = ".$ilDB->quote(1, "integer");
		$set = $ilDB->query($q);
		$pages = array();
		while ($page = $ilDB->fetchAssoc($set))
		{
			$pages[$page["page_id"]] = array("date" => $page["last_change"],
				"id" => $page["page_id"], "user" => $page["last_change_user"]);
		}

		return $pages;
	}

	/**
	 * Check whether content contains internal links
	 *
	 * @param
	 * @return
	 */
	function containsIntLinks($a_content)
	{
		if (strpos($a_content,  "IntLink"))
		{
			return true;
		}
		return false;
	}

}
?>