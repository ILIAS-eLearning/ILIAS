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
	function getText()
	{
		return $this->text;
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

	function getXML($a_utf8_encoded = false)
	{
		if ($a_utf8_encoded)
		{
			return "<Paragraph Language=\"".$this->getLanguage().
				"\">".utf8_encode($this->getText())."</Paragraph>";
		}
		else
		{
			return "<Paragraph Language=\"".$this->getLanguage().
				"\">".$this->getText()."</Paragraph>";
		}

	}


}
?>
