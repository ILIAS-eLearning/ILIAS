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


/**
* parses the template.xml that defines all styles of the current template
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilSaxParser
*/
require_once("classes/class.ilSaxParser.php");

class ilStyleDefinition extends ilSaxParser
{

	/**
	* Constructor
	*
	* parse
	*
	* @access	public
	*/
	function ilStyleDefinition($a_template_id = "")
	{
		global $ilias;

		if ($a_template_id == "")
		{
			$a_template_id = $ilias->account->skin;
		}

		parent::ilSaxParser("./templates/".$a_template_id."/template.xml");
	}


	// PUBLIC METHODS

	/**
	* get translation type (sys, db or 0)s
	*
	* @param	string	object type
	* @access	public
	*/
	function getStyles()
	{
//echo ":".count($this->styles).":";
		if (is_array($this->styles))
		{
			return $this->styles;
		}
		else
		{
			return array();
		}
	}

	function getTemplateName()
	{
		return $this->template_name;
	}


	function getStyle($a_id)
	{
		return $this->styles[$a_id];
	}


	function getStyleName($a_id)
	{
		return $this->styles[$a_id]["name"];
	}


	function getImageDirectory($a_id)
	{
		return $this->styles[$a_id]["image_directory"];
	}

	function getAllTemplates()
	{
		global $ilias;

		$skins = array();

		if ($dp = @opendir($ilias->tplPath))
		{
			while (($file = readdir($dp)) != false)
			{
				//is the file a directory?
				if (is_dir($ilias->tplPath.$file) && $file != "." && $file != ".." && $file != "CVS")
				{
					if (is_file($ilias->tplPath.$file."/template.xml"))
					{
						$skins[] = array(
							"id" => $file
						);
					}
				}
			} // while
		}
		else
		{
			return false;
		}
		return $skins;
	}

	// PRIVATE METHODS

	/**
	* set event handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
		xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
	}

	/**
	* start tag handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @param	string		element tag name
	* @param	array		element attributes
	* @access	private
	*/
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		switch($a_name)
		{
			case "template" :
				$this->template_name = $a_attribs["name"];
				break;

			case "style" :
				$this->styles[$a_attribs["id"]] =
					array(	"id" => $a_attribs["id"],
							"name" => $a_attribs["name"],
							"css_file" => $a_attribs["id"].".css",
							"image_directory" => $a_attribs["image_directory"]
					);
				$browsers =
					explode(",", $a_attribs["browsers"]);
				foreach ($browsers as $val)
				{
					$this->styles[$a_attribs["id"]]["browsers"][] = trim($val);
				}
				break;
		}
	}
	
	function styleExists($skin, $style)
	{
		if (is_file("templates/".$skin."/template.xml") &&
			is_file("templates/".$skin."/".$style.".css")
			)
		{
			return true;
		}
		return false;
	}

	/**
	* end tag handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @param	string		data
	* @access	private
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);

		if(!empty($a_data))
		{
			switch($this->current_tag)
			{
				default:
					break;
			}
		}
	}

	/**
	* end tag handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @param	string		element tag name
	* @access	private
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
	}
}
?>
