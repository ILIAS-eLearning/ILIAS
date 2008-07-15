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
require_once("./Services/COPage/classes/class.ilWysiwygUtil.php");

/**
* Class ilPCParagraph
*
* Paragraph of ilPageObject
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCParagraph extends ilPageContent
{
	var $dom;
	var $par_node;			// node of Paragraph element

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("par");
	}

	/**
	* Set Page Content Node
	*
	* @param	object	$a_node		Page Content DOM Node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->par_node =& $a_node->first_child();		//... and this the Paragraph node
	}


	/**
	* Create new page content (incl. paragraph) node at node
	*
	* @param	object	$node		Parent Node for Page Content
	*/
	function createAtNode(&$node)
	{
		$this->node = $this->createPageContentNode();
		$this->par_node =& $this->dom->create_element("Paragraph");
		$this->par_node =& $this->node->append_child($this->par_node);
		$this->par_node->set_attribute("Language", "");
		$node->append_child ($this->node);
	}

	/**
	* Create paragraph node (incl. page content node)
	* after given node.
	*
	* @param	object	$node		Predecessing node
	*/
	function createAfter($node)
	{
		$this->node = $this->createPageContentNode(false);
		if($succ_node = $node->next_sibling())
		{
			$this->node = $succ_node->insert_before($this->node, $succ_node);
		}
		else
		{
			$parent_node = $node->parent_node();
			$this->node = $parent_node->append_child($this->node);
		}
		$this->par_node = $this->dom->create_element("Paragraph");
		$this->par_node = $this->node->append_child($this->par_node);
		$this->par_node->set_attribute("Language", "");
	}
	
	/**
	* Create paragraph node (incl. page content node)
	* at given hierarchical ID.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id)
	{
//echo "-$a_hier_id-";
		$this->node =& $this->dom->create_element("PageContent");
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->par_node =& $this->dom->create_element("Paragraph");
		$this->par_node =& $this->node->append_child($this->par_node);
		$this->par_node->set_attribute("Language", "");
	}


	
	/**
	* Set (xml) content of text paragraph.
	*
	* @param	string		$a_text			text content
	* @param	boolean		$a_auto_split	auto split paragraph at headlines true/false
	*/
	function setText($a_text, $a_auto_split = false)
	{
		if (!is_array($a_text))
		{
			$text = array(array("level" => 0, "text" => $a_text));
		}
		else
		{
			$text = $a_text;
		}
		
		if ($a_auto_split)
		{
			$text = $this->autoSplit($a_text);
		}

		// DOMXML_LOAD_PARSING, DOMXML_LOAD_VALIDATING, DOMXML_LOAD_RECOVERING

		$temp_dom = @domxml_open_mem('<?xml version="1.0" encoding="UTF-8"?><Paragraph>'.$text[0]["text"].'</Paragraph>',
			DOMXML_LOAD_PARSING, $error);
			
		//$this->text = $a_text;
		// remove all childs
		if(empty($error))
		{
			// delete children of paragraph node
			$children = $this->par_node->child_nodes();
			for($i=0; $i<count($children); $i++)
			{
				$this->par_node->remove_child($children[$i]);
			}

			// copy new content children in paragraph node
			$xpc = xpath_new_context($temp_dom);
			$path = "//Paragraph";
			$res =& xpath_eval($xpc, $path);
			if (count($res->nodeset) == 1)
			{
				$new_par_node =& $res->nodeset[0];
				$new_childs = $new_par_node->child_nodes();
				
				for($i=0; $i<count($new_childs); $i++)
				{
					$cloned_child =& $new_childs[$i]->clone_node(true);
					$this->par_node->append_child($cloned_child);
				}
				$orig_characteristic = $this->getCharacteristic();
				if ($text[0]["level"] > 0)
				{
					$this->par_node->set_attribute("Characteristic", 'Headline'.$text[0]["level"]);
				}
			}
			
			$ok = true;
			
			$c_node = $this->node;
			// add other chunks afterwards
			for ($i=1; $i<count($text); $i++)
			{
				if ($ok)
				{
					$next_par = new ilPCParagraph($this->dom);
					$next_par->createAfter($c_node);
					$next_par->setLanguage($this->getLanguage());
					if ($text[$i]["level"] > 0)
					{
						$next_par->setCharacteristic("Headline".$text[$i]["level"]);
					}
					else
					{
						$next_par->setCharacteristic($orig_characteristic);
					}
					$ok = $next_par->setText($text[$i]["text"], false);
					$c_node = $next_par->node;
				}
			}
			
			return true;
		}
		else
		{
			return $error;
		}
	}

	/**
	* Get (xml) content of paragraph.
	*
	* @return	string		Paragraph Content.
	*/
	function getText($a_short_mode = false)
	{
		if (is_object($this->par_node))
		{
			$content = "";
			$childs = $this->par_node->child_nodes();
			for($i=0; $i<count($childs); $i++)
			{
				$content .= $this->dom->dump_node($childs[$i]);
			}
			return $content;
		}
		else
		{
			return "";
		}
	}

	/**
	* Set Characteristic of paragraph
	*
	* @param	string	$a_char		Characteristic
	*/
	function setCharacteristic($a_char)
	{
		if (!empty($a_char))
		{
			$this->par_node->set_attribute("Characteristic", $a_char);
		}
		else
		{
			if ($this->par_node->has_attribute("Characteristic"))
			{
				$this->par_node->remove_attribute("Characteristic");
			}
		}
	}

	/**
	* Get characteristic of paragraph.
	*
	* @return	string		characteristic
	*/
	function getCharacteristic()
	{
		if (is_object($this->par_node))
		{
			return $this->par_node->get_attribute("Characteristic");
		}
	}


	/**
	* set attribute subcharacteristic
	*/
	function setSubCharacteristic($a_char)
	{
		if (!empty($a_char))
		{
			$this->par_node->set_attribute("SubCharacteristic", $a_char);
		}
		else
		{
			if ($this->par_node->has_attribute("SubCharacteristic"))
			{
				$this->par_node->remove_attribute("SubCharacteristic");
			}
		}
	}

	/**
	* Get AutoIndent (Code Paragraphs)
	*
	* @param	string		Auto Indent attribute
	*/
	function getAutoIndent()
	{
		return $this->par_node->get_attribute("AutoIndent");
	}
	
	function setAutoIndent($a_char)
	{
		if (!empty($a_char))
		{
			$this->par_node->set_attribute("AutoIndent", $a_char);
		}
		else
		{
			if ($this->par_node->has_attribute("AutoIndent"))
			{
				$this->par_node->remove_attribute("AutoIndent");
			}
		}
	}

	/**
	* get attribute subcharacteristic
	*/
	function getSubCharacteristic()
	{
		return $this->par_node->get_attribute("SubCharacteristic");
	}

	/**
	* set attribute download title
	*/

	function setDownloadTitle($a_char)
	{
		if (!empty($a_char))
		{
			$this->par_node->set_attribute("DownloadTitle", $a_char);
		}
		else
		{
			if ($this->par_node->has_attribute("DownloadTitle"))
			{
				$this->par_node->remove_attribute("DownloadTitle");
			}
		}
	}

	/**
	* get attribute download title
	*/
	function getDownloadTitle()
	{
		return $this->par_node->get_attribute("DownloadTitle");
	}
	
	/**
	* set attribute showlinenumbers
	*/
	
	function setShowLineNumbers($a_char)
	{
		$a_char = empty($a_char)?"n":$a_char;
		
		$this->par_node->set_attribute("ShowLineNumbers", $a_char);
	}

	/**
	* get attribute showlinenumbers
	* 
	*/
	function getShowLineNumbers()
	{
		return $this->par_node->get_attribute("ShowLineNumbers");
	}
	
	/**
	* set language
	*/
	function setLanguage($a_lang)
	{
		$this->par_node->set_attribute("Language", $a_lang);
	}

	/**
	* get language
	*/
	function getLanguage()
	{
		return $this->par_node->get_attribute("Language");
	}

	function input2xml($a_text, $a_wysiwyg = 0)
	{
		return $this->_input2xml($a_text, $this->getLanguage(), $a_wysiwyg);
	}
	
	/**
	* converts user input to xml
	*/
	static function _input2xml($a_text, $a_lang, $a_wysiwyg = 0)
	{
		$a_text = ilUtil::stripSlashes($a_text, false);

		// note: the order of the processing steps is crucial
		// and should be the same as in xml2output() in REVERSE order!
		$a_text = trim($a_text);

//echo "<br>between:".htmlentities($a_text);

		// mask html
		$a_text = str_replace("&","&amp;",$a_text);
		$a_text = str_replace("<","&lt;",$a_text);
		$a_text = str_replace(">","&gt;",$a_text);

		// Reconvert PageTurn and BibItemIdentifier
		$a_text = preg_replace('/&lt;([\s\/]*?PageTurn.*?)&gt;/i',"<$1>",$a_text);
		$a_text = preg_replace('/&lt;([\s\/]*?BibItemIdentifier.*?)&gt;/i',"<$1>",$a_text);

//echo "<br>second:".htmlentities($a_text);

		// mask curly brackets
/*
echo htmlentities($a_text);
		$a_text = str_replace("{", "&#123;", $a_text);
		$a_text = str_replace("}", "&#125;", $a_text);
echo htmlentities($a_text);*/
		// linefeed to br
		$a_text = str_replace(chr(13).chr(10),"<br />",$a_text);
		$a_text = str_replace(chr(13),"<br />", $a_text);
		$a_text = str_replace(chr(10),"<br />", $a_text);

		$a_text = ilPCParagraph::input2xmlReplaceLists($a_text);
		
		// bb code to xml
		$a_text = eregi_replace("\[com\]","<Comment Language=\"".$a_lang."\">",$a_text);
		$a_text = eregi_replace("\[\/com\]","</Comment>",$a_text);
		$a_text = eregi_replace("\[emp\]","<Emph>",$a_text);
		$a_text = eregi_replace("\[\/emp\]","</Emph>",$a_text);
		$a_text = eregi_replace("\[str\]","<Strong>",$a_text);
		$a_text = eregi_replace("\[\/str\]","</Strong>",$a_text);
		$a_text = eregi_replace("\[fn\]","<Footnote>",$a_text);
		$a_text = eregi_replace("\[\/fn\]","</Footnote>",$a_text);
		$a_text = eregi_replace("\[quot\]","<Quotation Language=\"".$a_lang."\">",$a_text);
		$a_text = eregi_replace("\[\/quot\]","</Quotation>",$a_text);
		$a_text = eregi_replace("\[code\]","<Code>",$a_text);
		$a_text = eregi_replace("\[\/code\]","</Code>",$a_text);

		// internal links
		//$any = "[^\]]*";	// this doesn't work :-(
		$ws= "[ \t\r\f\v\n]*";

		while (eregi("\[(iln$ws((inst$ws=$ws([\"0-9])*)?$ws".
			"((page|chap|term|media|htlm|lm|dbk|glo|frm|exc|tst|svy|webr|chat|cat|crs|grp|file|fold|sahs|mcst)$ws=$ws([\"0-9])*)$ws".
			"(target$ws=$ws(\"(New|FAQ|Media)\"))?$ws))\]", $a_text, $found))
		{
			$attribs = ilUtil::attribsToArray($found[2]);
			$inst_str = $attribs["inst"];
			// pages
			if (isset($attribs["page"]))
			{
				if (!empty($found[10]))
				{
					$tframestr = " TargetFrame=\"".$found[10]."\" ";
				}
				else
				{
					$tframestr = "";
				}
				$a_text = eregi_replace("\[".$found[1]."\]",
					"<IntLink Target=\"il_".$inst_str."_pg_".$attribs[page]."\" Type=\"PageObject\"".$tframestr.">", $a_text);
			}
			// chapters
			else if (isset($attribs["chap"]))
			{
				if (!empty($found[10]))
				{
					$tframestr = " TargetFrame=\"".$found[10]."\" ";
				}
				else
				{
					$tframestr = "";
				}
				$a_text = eregi_replace("\[".$found[1]."\]",
					"<IntLink Target=\"il_".$inst_str."_st_".$attribs[chap]."\" Type=\"StructureObject\"".$tframestr.">", $a_text);
			}
			// glossary terms
			else if (isset($attribs["term"]))
			{
				switch ($found[10])
				{
					case "New":
						$tframestr = " TargetFrame=\"New\" ";
						break;

					default:
						$tframestr = " TargetFrame=\"Glossary\" ";
						break;
				}
				$a_text = eregi_replace("\[".$found[1]."\]",
					"<IntLink Target=\"il_".$inst_str."_git_".$attribs[term]."\" Type=\"GlossaryItem\" $tframestr>", $a_text);
			}
			// media object
			else if (isset($attribs["media"]))
			{
				if (!empty($found[10]))
				{
					$tframestr = " TargetFrame=\"".$found[10]."\" ";
					$a_text = eregi_replace("\[".$found[1]."\]",
						"<IntLink Target=\"il_".$inst_str."_mob_".$attribs[media]."\" Type=\"MediaObject\"".$tframestr.">", $a_text);
				}
				else
				{
					$a_text = eregi_replace("\[".$found[1]."\]",
						"<IntLink Target=\"il_".$inst_str."_mob_".$attribs[media]."\" Type=\"MediaObject\"/>", $a_text);
				}
			}
			// repository items (id is ref_id (will be used internally but will
			// be replaced by object id for export purposes)
			else if (isset($attribs["lm"]) || isset($attribs["dbk"]) || isset($attribs["glo"])
					 || isset($attribs["frm"]) || isset($attribs["exc"]) || isset($attribs["tst"])
					 || isset($attribs["svy"]) || isset($attribs["obj"]) || isset($attribs['webr'])
					 || isset($attribs["htlm"]) || isset($attribs["chat"]) || isset($attribs["grp"])
					 || isset($attribs["fold"]) || isset($attribs["sahs"]) || isset($attribs["mcst"])
					 || isset($attribs["cat"]) || isset($attribs["crs"]) || isset($attribs["file"]))
			{
				$obj_id = (isset($attribs["lm"])) ? $attribs["lm"] : $obj_id;
				$obj_id = (isset($attribs["dbk"])) ? $attribs["dbk"] : $obj_id;
				$obj_id = (isset($attribs["chat"])) ? $attribs["chat"] : $obj_id;
				$obj_id = (isset($attribs["glo"])) ? $attribs["glo"] : $obj_id;
				$obj_id = (isset($attribs["frm"])) ? $attribs["frm"] : $obj_id;
				$obj_id = (isset($attribs["exc"])) ? $attribs["exc"] : $obj_id;
				$obj_id = (isset($attribs["htlm"])) ? $attribs["htlm"] : $obj_id;
				$obj_id = (isset($attribs["tst"])) ? $attribs["tst"] : $obj_id;
				$obj_id = (isset($attribs["svy"])) ? $attribs["svy"] : $obj_id;
				$obj_id = (isset($attribs["obj"])) ? $attribs["obj"] : $obj_id;
				$obj_id = (isset($attribs["webr"])) ? $attribs["webr"] : $obj_id;
				$obj_id = (isset($attribs["fold"])) ? $attribs["fold"] : $obj_id;
				$obj_id = (isset($attribs["cat"])) ? $attribs["cat"] : $obj_id;
				$obj_id = (isset($attribs["crs"])) ? $attribs["crs"] : $obj_id;
				$obj_id = (isset($attribs["grp"])) ? $attribs["grp"] : $obj_id;
				$obj_id = (isset($attribs["file"])) ? $attribs["file"] : $obj_id;
				$obj_id = (isset($attribs["sahs"])) ? $attribs["sahs"] : $obj_id;
				$obj_id = (isset($attribs["mcst"])) ? $attribs["mcst"] : $obj_id;

				$a_text = eregi_replace("\[".$found[1]."\]",
					"<IntLink Target=\"il_".$inst_str."_obj_".$obj_id."\" Type=\"RepositoryItem\">", $a_text);
			}			
			else
			{
				$a_text = eregi_replace("\[".$found[1]."\]", "[error: iln".$found[1]."]",$a_text);
			}
		}
		while (eregi("\[(iln$ws((inst$ws=$ws([\"0-9])*)?".$ws."media$ws=$ws([\"0-9])*)$ws)/\]", $a_text, $found))
		{
			$attribs = ilUtil::attribsToArray($found[2]);
			$inst_str = $attribs["inst"];
			$a_text = eregi_replace("\[".$found[1]."/\]",
				"<IntLink Target=\"il_".$inst_str."_mob_".$attribs[media]."\" Type=\"MediaObject\"/>", $a_text);
		}
		$a_text = eregi_replace("\[\/iln\]","</IntLink>",$a_text);

		// external link
		$ws= "[ \t\r\f\v\n]*";

		//while (eregi("\[(xln$ws(url$ws=$ws([\"0-9])*)$ws)\]", $a_text, $found))
		while (eregi("\[(xln$ws(url$ws=$ws\"([^\"])*\")$ws)\]", $a_text, $found))
		{
//echo "found2:".addslashes($found[2])."<br>"; flush();;
			$attribs = ilUtil::attribsToArray($found[2]);
//echo "url:".$attribs["url"]."<br>";
			//$found[1] = str_replace("?", "\?", $found[1]);
			if (isset($attribs["url"]))
			{
//echo "3";
				$a_text = str_replace("[".$found[1]."]", "<ExtLink Href=\"".$attribs["url"]."\">", $a_text);
			}
			else
			{
				$a_text = str_replace("[".$found[1]."]", "[error: xln".$found[1]."]",$a_text);
			}
		}
		$a_text = eregi_replace("\[\/xln\]","</ExtLink>",$a_text);
		/*$blob = ereg_replace("<NR><NR>","<P>",$blob);
		$blob = ereg_replace("<NR>"," ",$blob);*/
//echo "<br>-".htmlentities($a_text)."-";
		//$a_text = nl2br($a_text);
		//$a_text = addslashes($a_text);
		return $a_text;
	}
	
	/**
	* Converts xml from DB to output in edit textarea.
	*
	* @param	string	$a_text		xml from db
	*
	* @return	string	string ready for edit textarea
	*/
	function input2xmlReplaceLists($a_text)
	{
		$rows = explode("<br />", $a_text."<br />");
		
		$old_level = 0;

		$text = "";
		
		foreach ($rows as $row)
		{
			$level = 0;
			if (str_replace("#", "*", substr($row, 0, 3)) == "***")
			{
				$level = 3;
			}
			else if (str_replace("#", "*", substr($row, 0, 2)) == "**")
			{
				$level = 2;
			}
			else if (str_replace("#", "*", substr($row, 0, 1)) == "*")
			{
				$level = 1;
			}
			
			// end previous line
			if ($level < $old_level)
			{
				for ($i = $old_level; $i > $level; $i--)
				{
					$text.= "</SimpleListItem></".$clist[$i].">";
				}
				if ($level > 0)
				{
					$text.= "</SimpleListItem>";
				}
			}
			else if ($old_level > 0 && $level > 0 && ($level == $old_level))
			{
				$text.= "</SimpleListItem>";
			}
			else if (($level == $old_level) && $text != "")
			{
				$text.= "<br />";
			}
			
			// start next line
			if ($level > $old_level)
			{
				for($i = $old_level + 1; $i <= $level; $i++)
				{
					if (substr($row, $i - 1, 1) == "*")
					{
						$clist[$i] = "SimpleBulletList";
					}
					else
					{
						$clist[$i] = "SimpleNumberedList";
					}
					$text.= "<".$clist[$i]."><SimpleListItem>";
				}
			}
			else if ($old_level > 0 && $level > 0)
			{
				$text.= "<SimpleListItem>";
			}
			$text.= substr($row, $level);
			
			$old_level = $level;
		}
		
		// remove "<br />" at the end
		if (substr($text, strlen($text) - 6) == "<br />")
		{
			$text = substr($text, 0, strlen($text) - 6);
		}
		
		return $text;
	}
	
	/**
	* Replaces <list> tags with *
	*
	* @param	string	$a_text		xml from db
	*
	* @return	string				string containing * for lists
	*/
	static function xml2outputReplaceLists($a_text)
	{
		$segments = ilPCParagraph::segmentString($a_text, array("<SimpleBulletList>", "</SimpleBulletList>",
			"</SimpleListItem>", "<SimpleListItem>", "<SimpleNumberedList>", "</SimpleNumberedList>"));
		
		$current_list = array();
		$text = "";
		for ($i=0; $i<= count($segments); $i++)
		{
			if ($segments[$i] == "<SimpleBulletList>")
			{
				if (count($current_list) == 0)
				{
					$list_start = true;
				}
				array_push($current_list, "*");
				$li = false;
			}
			else if ($segments[$i] == "<SimpleNumberedList>")
			{
				if (count($current_list) == 0)
				{
					$list_start = true;
				}
				array_push($current_list, "#");
				$li = false;
			}
			else if ($segments[$i] == "</SimpleBulletList>")
			{
				array_pop($current_list);
				$li = false;
			}
			else if ($segments[$i] == "</SimpleNumberedList>")
			{
				array_pop($current_list);
				$li = false;
			}
			else if ($segments[$i] == "<SimpleListItem>")
			{
				$li = true;
			}
			else if ($segments[$i] == "</SimpleListItem>")
			{
				$li = false;
			}
			else
			{
				if ($li)
				{
					if ($list_start)
					{
						$text.= "<br />";
						$list_start = false;
					}
					foreach($current_list as $list)
					{
						$text.= $list;
					}
				}
				$text.= $segments[$i];
				if ($li)
				{
					$text.= "<br />";
				}
				$li = false;
			}
		}
		
		return $text;
	}
	
	/**
	* Segments a string into an array at each position of a substring
	*/
	static function segmentString($a_haystack, $a_needles)
	{
		$segments = array();
		
		$nothing_found = false;
		while (!$nothing_found)
		{
			$nothing_found = true;
			$found = -1;
			foreach($a_needles as $needle)
			{
				$pos = stripos($a_haystack, $needle);
				if (is_int($pos) && ($pos < $found || $found == -1))
				{
					$found = $pos;
					$found_needle = $needle;
					$nothing_found = false;
				}
			}
			if ($found > 0)
			{
				$segments[] = substr($a_haystack, 0, $found);
				$a_haystack = substr($a_haystack, $found);
			}
			if ($found > -1)
			{
				$segments[] = substr($a_haystack, 0, strlen($found_needle));
				$a_haystack = substr($a_haystack, strlen($found_needle));
			}
		}
		if ($a_haystack != "")
		{
			$segments[] = $a_haystack;
		}
		
		return $segments;
	}

	/**
	* Converts xml from DB to output in edit textarea.
	*
	* @param	string	$a_text		xml from db
	*
	* @return	string	string ready for edit textarea
	*/
	static function xml2output($a_text)
	{
		// note: the order of the processing steps is crucial
		// and should be the same as in input2xml() in REVERSE order!

		// xml to bb code
		$any = "[^>]*";
		$a_text = eregi_replace("<Comment[^>]*>","[com]",$a_text);
		$a_text = eregi_replace("</Comment>","[/com]",$a_text);
		$a_text = eregi_replace("<Comment/>","[com][/com]",$a_text);
		$a_text = eregi_replace("<Emph>","[emp]",$a_text);
		$a_text = eregi_replace("</Emph>","[/emp]",$a_text);
		$a_text = eregi_replace("<Emph/>","[emp][/emp]",$a_text);
		$a_text = eregi_replace("<Strong>","[str]",$a_text);
		$a_text = eregi_replace("</Strong>","[/str]",$a_text);
		$a_text = eregi_replace("<Strong/>","[str][/str]",$a_text);
		$a_text = eregi_replace("<Footnote[^>]*>","[fn]",$a_text);
		$a_text = eregi_replace("</Footnote>","[/fn]",$a_text);
		$a_text = eregi_replace("<Footnote/>","[fn][/fn]",$a_text);
		$a_text = eregi_replace("<Quotation[^>]*>","[quot]",$a_text);
		$a_text = eregi_replace("</Quotation>","[/quot]",$a_text);
		$a_text = eregi_replace("<Quotation/>","[quot][/quot]",$a_text);
		$a_text = eregi_replace("<Code[^>]*>","[code]",$a_text);
		$a_text = eregi_replace("</Code>","[/code]",$a_text);
		$a_text = eregi_replace("<Code/>","[code][/code]",$a_text);

		// replace lists
		$a_text = ilPCParagraph::xml2outputReplaceLists($a_text);
		
		// internal links
		while (eregi("<IntLink($any)>", $a_text, $found))
		{
			$found[0];
			$attribs = ilUtil::attribsToArray($found[1]);
			$target = explode("_", $attribs["Target"]);
			$target_id = $target[count($target) - 1];
			$inst_str = (!is_int(strpos($attribs["Target"], "__")))
				? $inst_str = "inst=\"".$target[1]."\" "
				: $inst_str = "";
			switch($attribs["Type"])
			{
				case "PageObject":
					$tframestr = (!empty($attribs["TargetFrame"]))
						? " target=\"".$attribs["TargetFrame"]."\""
						: "";
					$a_text = eregi_replace("<IntLink".$found[1].">","[iln ".$inst_str."page=\"".$target_id."\"$tframestr]",$a_text);
					break;

				case "StructureObject":
					$tframestr = (!empty($attribs["TargetFrame"]))
						? " target=\"".$attribs["TargetFrame"]."\""
						: "";
					$a_text = eregi_replace("<IntLink".$found[1].">","[iln ".$inst_str."chap=\"".$target_id."\"$tframestr]",$a_text);
					break;

				case "GlossaryItem":
					$tframestr = (empty($attribs["TargetFrame"]) || $attribs["TargetFrame"] == "Glossary")
						? ""
						: " target=\"".$attribs["TargetFrame"]."\"";
					$a_text = eregi_replace("<IntLink".$found[1].">","[iln ".$inst_str."term=\"".$target_id."\"".$tframestr."]",$a_text);
					break;

				case "MediaObject":
					if (empty($attribs["TargetFrame"]))
					{
						$a_text = eregi_replace("<IntLink".$found[1].">","[iln ".$inst_str."media=\"".$target_id."\"/]",$a_text);
					}
					else
					{
						$a_text = eregi_replace("<IntLink".$found[1].">","[iln media=\"".$target_id."\"".
							" target=\"".$attribs["TargetFrame"]."\"]",$a_text);
					}
					break;

				case "RepositoryItem":
					if ($inst_str == "")
					{
						$target_type = ilObject::_lookupType($target_id, true);
					}
					else
					{
						$target_type = "obj";
					}
					$a_text = eregi_replace("<IntLink".$found[1].">","[iln ".$inst_str."$target_type=\"".$target_id."\"".$tframestr."]",$a_text);
					break;

				default:
					$a_text = eregi_replace("<IntLink".$found[1].">","[iln]",$a_text);
					break;
			}
		}
		$a_text = eregi_replace("</IntLink>","[/iln]",$a_text);

		// external links
		while (eregi("<ExtLink($any)>", $a_text, $found))
		{
			$found[0];
			$attribs = ilUtil::attribsToArray($found[1]);
			//$found[1] = str_replace("?", "\?", $found[1]);
			$a_text = str_replace("<ExtLink".$found[1].">","[xln url=\"".$attribs["Href"]."\"]",$a_text);
		}
		$a_text = eregi_replace("</ExtLink>","[/xln]",$a_text);


		// br to linefeed
		$a_text = str_replace("<br />", "\n", $a_text);
		$a_text = str_replace("<br/>", "\n", $a_text);

		// prevent curly brackets from being swallowed up by template engine
		$a_text = str_replace("{", "&#123;", $a_text);
		$a_text = str_replace("}", "&#125;", $a_text);

		// unmask html
		$a_text = str_replace("&lt;", "<", $a_text);
		$a_text = str_replace("&gt;", ">",$a_text);

		// this is needed to allow html like <tag attribute="value">... in paragraphs
		$a_text = str_replace("&quot;", "\"", $a_text);

		// make ampersands in (enabled) html attributes work
		// e.g. <a href="foo.php?n=4&t=5">hhh</a>
		$a_text = str_replace("&amp;", "&", $a_text);

		// make &gt; and $lt; work to allow (disabled) html descriptions
		$a_text = str_replace("&lt;", "&amp;lt;", $a_text);
		$a_text = str_replace("&gt;", "&amp;gt;", $a_text);

		return $a_text;
		//return str_replace("<br />", chr(13).chr(10), $a_text);
	}

	/**
	* This function splits a paragraph text that has been already
	* processed with input2xml at each header position =header1=,
	* ==header2== or ===header3=== and returns an array that contains
	* the single chunks.
	*/
	function autoSplit($a_text)
	{
		$a_text = "<br />".$a_text."<br />";		// add preceding and trailing br
		
		$chunks = array();
		$c_text = $a_text;
		while ($c_text != "")
		{
//var_dump($c_text); flush();
			$s1 = strpos($c_text, "<br />=");
			if (is_int($s1))
			{
				$s2 = strpos($c_text, "<br />==");
				if (is_int($s2) && $s2 <= $s1)
				{
					$s3 = strpos($c_text, "<br />===");
					if (is_int($s3) && $s3 <= $s2)		// possible level three header
					{
						$n = strpos($c_text, "<br />", $s3 + 1);
						if ($n > ($s3+9) && substr($c_text, $n-3, 9) == "===<br />")
						{
							// found level three header
							if ($s3 > 0)
							{
								$chunks[] = array("level" => 0,
									"text" => $this->removeTrailingBr($head.substr($c_text, 0, $s3)));
								$head = "";
							}
							$chunks[] = array("level" => 3,
								"text" => trim(substr($c_text, $s3+9, $n-$s3-12)));
							$c_text = $this->removePrecedingBr(substr($c_text, $n+6));
						}
						else
						{
							$head = substr($c_text, 0, $n);
							$c_text = substr($c_text, $n);
						}
					}
					else	// possible level two header
					{
						$n = strpos($c_text, "<br />", $s2 + 1);
						if ($n > ($s2+8) && substr($c_text, $n-2, 8) == "==<br />")
						{
							// found level two header
							if ($s2 > 0)
							{
								$chunks[] = array("level" => 0,
									"text" => $this->removeTrailingBr($head.substr($c_text, 0, $s2)));
								$head = "";
							}
							$chunks[] = array("level" => 2, "text" => trim(substr($c_text, $s2+8, $n-$s2-10)));
							$c_text = $this->removePrecedingBr(substr($c_text, $n+6));
						}
						else
						{
							$head = substr($c_text, 0, $n);
							$c_text = substr($c_text, $n);
						}
					}
				}
				else	// possible level one header
				{
					$n = strpos($c_text, "<br />", $s1 + 1);
					if ($n > ($s1+7) && substr($c_text, $n-1, 7) == "=<br />")
					{
						// found level one header
						if ($s1 > 0)
						{
							$chunks[] = array("level" => 0,
								"text" => $this->removeTrailingBr($head.substr($c_text, 0, $s1)));
							$head = "";
						}
						$chunks[] = array("level" => 1, "text" => trim(substr($c_text, $s1+7, $n-$s1-8)));
						$c_text = $this->removePrecedingBr(substr($c_text, $n+6));
					}
					else
					{
						$head = substr($c_text, 0, $n);
						$c_text = substr($c_text, $n);
					}
				}
			}
			else
			{
				$chunks[] = array("level" => 0, "text" => $head.$c_text);
				$head = "";
				$c_text = "";
			}
		}
		if (count($chunks) == 0)
		{
			$chunks[] = array("level" => 0, "text" => "");
		}
		
		// remove preceding br
		if (substr($chunks[0]["text"], 0, 6) == "<br />")
		{
			$chunks[0]["text"] = substr($chunks[0]["text"], 6);
		}

		// remove trailing br
		if (substr($chunks[count($chunks) - 1]["text"],
			strlen($chunks[count($chunks) - 1]["text"]) - 6, 6) == "<br />")
		{
			$chunks[count($chunks) - 1]["text"] =
				substr($chunks[0]["text"], 0, strlen($chunks[count($chunks) - 1]["text"]) - 6);
			if ($chunks[count($chunks) - 1]["text"] == "")
			{
				unset($chunks[count($chunks) - 1]);
			}
		}
		
		return $chunks;
	}
	
	/**
	* Remove preceding <br />
	*/
	function removePrecedingBr($a_str)
	{
		if (substr($a_str, 0, 6) == "<br />")
		{
			$a_str = substr($a_str, 6);
		}
		return $a_str;
	}

	/**
	* Remove trailing <br />
	*/
	function removeTrailingBr($a_str)
	{
		if (substr($a_str, strlen($a_str) - 6) == "<br />")
		{
			$a_str = substr($a_str, 0, strlen($a_str) - 6);
		}
		return $a_str;
	}
	
	/**
	* Need to override getType from ilPageContent to distinguish between Pararagraph and Source
	*/
	function getType()
	{
		return ($this->getCharacteristic() == "Code")?"src":parent::getType();
	}

}
?>
