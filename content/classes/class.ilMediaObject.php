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

define ("IL_MODE_ALIAS", 1);
define ("IL_MODE_OUTPUT", 2);
define ("IL_MODE_FULL", 3);

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
	var $dom;
	var $width;
	var $height;
	var $parameters;
	/*
	var $mime;
	var $file;
	var $caption;*/
	var $halign;

	/**
	* Constructor
	* @access	public
	*/
	function ilMediaObject($a_id = 0)
	{

		parent::ilObjMediaObject($a_id);

		$this->is_alias = false;
		$this->parameters = array();

		if($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	* read media object data from db
	*/
	function read()
	{
		// read media_object record
		$query = "SELECT * FROM media_object WHERE id = '".$this->getId()."'";
		$mob_set = $this->ilias->db->query($query);
		$mob_rec = $mob_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setWidth($mob_rec["width"]);
		$this->setHeight($mob_rec["height"]);
		$this->setHAlign($mob_rec["halign"]);

		// read mob parameters
		$query = "SELECT * FROM mob_parameter WHERE mob_id = '".$this->getId()."'";
		$par_set = $this->ilias->db->query($query);
		while ($par_rec = $par_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->parameters[$par_rec["name"]] = $par_rec["value"];
		}

		// get meta data
		$this->meta_data =& new ilMetaData($this->getType(), $this->getId());
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

	/*
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
	}*/

	function setHAlign($a_halign)
	{
		$this->halign = $a_halign;
	}

	function getHAlign()
	{
		return $this->halign;
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
	* create media object in db
	*/
	function create()
	{
		// create mob
		parent::create();
		$query = "INSERT INTO media_object (id, width, height, halign) VALUES ".
			"('".$this->getId()."','".$this->getWidth()."','".$this->getHeight().
			"','".$this->getHAlign()."')";
		$this->ilias->db->query($query);

		// create mob parameters
		foreach($this->parameters as $name => $value)
		{
			$query = "INSERT INTO mob_parameter(mob_id, name, value) VALUES ".
				"('".$this->getId()."', '$name','$value')";
			$this->ilias->db->query($query);
		}
	}

	/**
	* update media object in db
	*/
	function update()
	{
		// update mob
		parent::update();
		$query = "UPDATE media_object SET ".
			" width = '".$this->getWidth."',".
			" height = '".$this->getHeight."',".
			" halign = '".$this->getHAlign."' ".
			" WHERE id = '".$this->getId()."'";
		$this->ilias->db->query($query);
//echo "<b>".$query."</b>";

		// update mob parameters
		$query = "DELETE FROM mob_parameter WHERE mob_id = '".$this->getId()."'";
		$this->ilias->db->query($query);
		foreach($this->parameters as $name => $value)
		{
			$query = "INSERT INTO mob_parameter(mob_id, name, value) VALUES ".
				"('".$this->getId()."', '$name','$value')";
			$this->ilias->db->query($query);
		}
	}

	/**
	* get MediaObject XLM Tag
	*  @param	int		$a_mode		IL_MODE_ALIAS | IL_MODE_OUTPUT | IL_MODE_FULL
	*/
	function getXML($a_mode = IL_MODE_FULL)
	{
		// TODO: full implementation of all parameters
		$xml = "<MediaObject>\n";
		switch ($a_mode)
		{
			case IL_MODE_ALIAS:
				$xml .= "<MediaAlias OriginId=\"".$this->getId()."\"/>\n";
				$xml .= "<Layout Width=\"".$this->getWidth()."\" Height=\"".$this->getHeight()."\"/>\n";
				$parameters = $this->getParameters();
				foreach ($parameters as $name => $value)
				{
					$xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>\n";
				}
				break;

			case IL_MODE_OUTPUT:
				$xml .= "<Layout Width=\"".$this->getWidth()."\" Height=\"".$this->getHeight()."\"/>\n";
				$parameters = $this->getParameters();
				foreach ($parameters as $name => $value)
				{
					$xml .= "<Parameter Name=\"$name\" Value=\"$value\"/>\n";
				}
				break;
		}
		$xml .= "</MediaObject>";

		return $xml;
	}
}
?>
