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

require_once("content/classes/class.ilPageContent.php");

/**
* Class ilParagraph
*
* Paragraph of ilPageObject of ILIAS Learning Module (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilParagraph extends ilPageContent
{
	var $dom;
	var $par_node;			// node of Paragraph element

	/**
	* Constructor
	* @access	public
	*/
	function ilParagraph(&$a_dom)
	{
		parent::ilPageContent();
		$this->setType("par");

		$this->dom =& $a_dom;
	}

	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->par_node =& $a_node->first_child();		//... and this the Paragraph node
	}


	function create(&$a_pg_obj, $a_hier_id)
	{
		$this->node =& $this->dom->create_element("PageContent");
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->par_node =& $this->dom->create_element("Paragraph");
		$this->par_node =& $this->node->append_child($this->par_node);
		$this->par_node->set_attribute("Language", "");
	}


	/**
	* set (xml) content of text paragraph
	*/
	function setText($a_text)
	{
		// DOMXML_LOAD_PARSING, DOMXML_LOAD_VALIDATING, DOMXML_LOAD_RECOVERING
		$temp_dom = @domxml_open_mem("<Paragraph>".$a_text."</Paragraph>",
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
			}
//echo "<br>thedump:".htmlentities($this->dom->dump_node($this->par_node)).":";
			return true;
		}
		else
		{
			return $error;
		}
	}

	/**
	* get (xml) content of paragraph
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
			//return $this->par_node->get_content();
			return $content;
		}
		else
		{
			return "";
		}
	}

	/**
	*
	*/
	function setCharacteristic($a_char)
	{
		if (!empty($a_char))
		{
			$this->par_node->set_attribute("Characteristic", $a_char);
		}
		else
		{
			$this->par_node->remove_attribute("Characteristic");
		}
	}

	/**
	*
	*/
	function getCharacteristic()
	{
		return $this->par_node->get_attribute("Characteristic");
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

	function input2xml($a_text)
	{
		// note: the order of the processing steps is crucial
		// and should be the same as in xml2output() in REVERSE order!

		$a_text = trim($a_text);

		// mask html
		$a_text = str_replace("<","&lt;",$a_text);
		$a_text = str_replace(">","&gt;",$a_text);

		// linefeed to br
		$a_text = str_replace(chr(13).chr(10),"<br />",$a_text);
		$a_text = str_replace(chr(13),"<br />", $a_text);
		$a_text = str_replace(chr(10),"<br />", $a_text);

		// bb code to xml
		$a_text = eregi_replace("\[com\]","<Comment Language=\"".$this->getLanguage()."\">",$a_text);
		$a_text = eregi_replace("\[\/com\]","</Comment>",$a_text);
		$a_text = eregi_replace("\[emp]","<Emph>",$a_text);
		$a_text = eregi_replace("\[\/emp\]","</Emph>",$a_text);
		$a_text = eregi_replace("\[str]","<Strong>",$a_text);
		$a_text = eregi_replace("\[\/str\]","</Strong>",$a_text);
		$a_text = eregi_replace("\[fn\]","<Footnote>",$a_text);
		$a_text = eregi_replace("\[\/fn\]","</Footnote>",$a_text);
		$a_text = eregi_replace("\[quot\]","<Quotation Language=\"".$this->getLanguage()."\">",$a_text);
		$a_text = eregi_replace("\[\/quot\]","</Quotation>",$a_text);
		$a_text = eregi_replace("\[code\]","<Code>",$a_text);
		$a_text = eregi_replace("\[\/code\]","</Code>",$a_text);

		/*$blob = ereg_replace("<NR><NR>","<P>",$blob);
		$blob = ereg_replace("<NR>"," ",$blob);*/

		//$a_text = nl2br($a_text);
		return $a_text;
	}

	function xml2output($a_text)
	{
		// note: the order of the processing steps is crucial
		// and should be the same as in input2xml() in REVERSE order!

		// xml to bb code
		$a_text = eregi_replace("<Comment[^>]*>","[com]",$a_text);
		$a_text = eregi_replace("</Comment>","[/com]",$a_text);
		$a_text = eregi_replace("<Emph>","[emp]",$a_text);
		$a_text = eregi_replace("</Emph>","[/emp]",$a_text);
		$a_text = eregi_replace("<Strong>","[str]",$a_text);
		$a_text = eregi_replace("</Strong>","[/str]",$a_text);
		$a_text = eregi_replace("<Footnote[^>]*>","[fn]",$a_text);
		$a_text = eregi_replace("</Footnote>","[/fn]",$a_text);
		$a_text = eregi_replace("<Quotation[^>]*>","[quot]",$a_text);
		$a_text = eregi_replace("</Quotation>","[/quot]",$a_text);
		$a_text = eregi_replace("<Code[^>]*>","[code]",$a_text);
		$a_text = eregi_replace("</Code>","[/code]",$a_text);

		// br to linefeed
		$a_text = str_replace("<br />", "\n", $a_text);
		$a_text = str_replace("<br/>", "\n", $a_text);

		// unmask html
		$a_text = str_replace("&lt;", "<", $a_text);
		$a_text = str_replace("&gt;", ">",$a_text);
		return $a_text;
		//return str_replace("<br />", chr(13).chr(10), $a_text);
	}

}
?>
