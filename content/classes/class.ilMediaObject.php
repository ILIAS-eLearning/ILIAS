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

require_once("classes/class.ilObjMediaObject.php");

/**
* Class ilMediaObject
*
* Todo: this class must be integrated with group/folder handling
*
* ILIAS Media Object
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilMediaObject extends ilObjMediaObject
{
	var $is_alias;
	var $origin_id;
	var $id;
	var $ilias;
	var $dom;
	var $width;
	var $height;
	var $parameters;
	var $mime;
	var $file;
	var $caption;

	/**
	* Constructor
	* @access	public
	*/
	function ilMediaObject($a_id = 0)
	{
		global $ilias;

		$this->setType("mob");
		$this->id = $a_id;
		$this->ilias =& $ilias;

		$this->is_alias = false;
		$this->parameters = array();

		if($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	* todo
	*/
	function read()
	{
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

	/*
	function getimportId()
	{
		return $this->meta_data->getImportIdentifierEntryID();
	}*/

	/**
	* get width
	*/
	function getWidth()
	{
		return $this->width;
	}

	/**
	* set width
	*/
	function setWidth($a_width)
	{
		$this->width = $a_width;
	}

	/**
	* get height
	*/
	function getHeight()
	{
		return $this->height;
	}

	/**
	* set height
	*/
	function setHeight($a_height)
	{
		$this->height = $a_height;
	}

	function setMime($a_mime)
	{
		$this->mime = $a_mime;
	}

	function getMime()
	{
		return $this->mime;
	}

	function setFile($a_file)
	{
		$this->file = $a_file;
	}

	function getFile()
	{
		return $this->file;
	}

	function setCaption($a_caption)
	{
		$this->caption = $a_caption;
	}

	function getCaption()
	{
		return $this->caption;
	}


	/**
	* set parameter
	*/
	function setParameter($a_name, $a_value)
	{
		$this->parameters[$a_name] = $a_value;
	}

	/**
	* get all parameters
	*/
	function getParameters()
	{
		return $this->parameters;
	}

	/**
	* get a single parameter
	*/
	function getParameter($a_name)
	{
		return $this->parameter[$a_name];
	}

	/**
	* create
	*/
	function create()
	{
		$this->setTitle("dummy");
		$this->setDescription("dummy");
		parent::create();
		$query = "INSERT INTO media_object (id, width, height, mime, file, caption) VALUES ".
			"('".$this->getId()."','".$this->getWidth()."','".$this->getHeight().
			"','".$this->getMIME()."','".$this->getFile()."','".$this->getCaption()."')";
		$this->ilias->db->query($query);
		foreach($this->parameters as $name => $value)
		{
			$query = "REPLACE INTO mob_parameter(mob_id, name, value) VALUES ".
				"('".$this->getId()."', '$name','$value')";
			$this->ilias->db->query($query);
		}
	}

	/**
	* get MediaObject XLM Tag
	*
	* @param	boolean		$a_alias	return tag as media alias
	*/
	function getXML($a_alias = false)
	{
		$xml = "<MediaObject>\n";
		if ($a_alias)
		{
			$xml .= "<MediaAlias OriginId=\"".$this->getId()."\"/>\n";
			$xml .= "<Layout Width=\"".$this->getWidth()."\" Height=\"".$this->getHeight()."\"/>\n";
			$parameters = $this->getParameters();
			foreach ($parameters as $name => $value)
			{
				$xml .= "<Parameter Name=\"$name\" Value=\"$value\">\n";
			}
		}
		$xml .= "</MediaObject>";

		return $xml;
	}
}
?>