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
	var $text;
	var $language;
	var $characteristic;

	/**
	* Constructor
	* @access	public
	*/
	function ilParagraph()
	{
		parent::ilPageContent();
		$this->setType("par");

		$this->text = "";
		$this->characteristic = "";
		$this->language = "";
	}

	/**
	*
	*/
	function appendText($a_text)
	{
		$this->text.= $a_text;
	}

	/**
	*
	*/
	function setText($a_text)
	{
		$this->text = $a_text;
	}

	/**
	*
	*/
	function getText($a_short_mode = false)
	{
		if (!$a_short_mode)
		{
			return $this->text;
		}
		else
		{
			return ilUtil::shortenText($this->xml2output($this->text), 100);
		}
	}

	/**
	*
	*/
	function setCharacteristic($a_char)
	{
		$this->characteristic = $a_char;
	}

	/**
	*
	*/
	function getCharacteristic()
	{
		return $this->characteristic;
	}

	function setLanguage($a_lang)
	{
		$this->language = $a_lang;
	}

	function getLanguage()
	{
		return $this->language;
	}

	function getXML($a_utf8_encoded = false, $a_short_mode = false, $a_incl_ed_ids = false)
	{
		$ed_id = ($a_incl_ed_ids)
			? "ed_id=\"".$this->getEdId()."\""
			: "";
//echo "in par ed_id:".$ed_id.":<br>";
		if ($a_utf8_encoded)
		{
			return "<Paragraph $ed_id Language=\"".$this->getLanguage().
				"\">".utf8_encode($this->getText($a_short_mode))."</Paragraph>";
		}
		else
		{
			return "<Paragraph $ed_id Language=\"".$this->getLanguage().
				"\">".$this->getText($a_short_mode)."</Paragraph>";
		}

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
		$a_text = eregi_replace("\[com\]","<Comment>",$a_text);
		$a_text = eregi_replace("\[\/com\]","</Comment>",$a_text);
		$a_text = eregi_replace("\[emp]","<Emph>",$a_text);
		$a_text = eregi_replace("\[\/emp\]","</Emph>",$a_text);
		$a_text = eregi_replace("\[str]","<Strong>",$a_text);
		$a_text = eregi_replace("\[\/str\]","</Strong>",$a_text);
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
		$a_text = eregi_replace("<Comment>","[com]",$a_text);
		$a_text = eregi_replace("</Comment>","[/com]",$a_text);
		$a_text = eregi_replace("<Emph>","[emp]",$a_text);
		$a_text = eregi_replace("</Emph>","[/emp]",$a_text);
		$a_text = eregi_replace("<Strong>","[str]",$a_text);
		$a_text = eregi_replace("</Strong>","[/str]",$a_text);

		// br to linefeed
		$a_text = str_replace("<br />", "\n", $a_text);

		// unmask html
		$a_text = str_replace("&lt;", "<", $a_text);
		$a_text = str_replace("&gt;", ">",$a_text);
		return $a_text;
		//return str_replace("<br />", chr(13).chr(10), $a_text);
	}


}
?>
