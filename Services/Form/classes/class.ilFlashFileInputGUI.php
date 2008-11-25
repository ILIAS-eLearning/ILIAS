<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
* This class represents an image file property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilFlashFileInputGUI extends ilFileInputGUI
{
	protected $applet;
	protected $width;
	protected $height;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("flash_file");
		$this->setSuffixes(array("swf"));
		$this->width = 550;
		$this->height = 400;
	}

	/**
	* Set applet.
	*
	* @param	string	$a_applet	Applet
	*/
	function setApplet($a_applet)
	{
		$this->applet = $a_applet;
	}

	/**
	* Get applet.
	*
	* @return	string	Applet
	*/
	function getApplet()
	{
		return $this->applet;
	}

	/**
	* Get width.
	*
	* @return	integer	width
	*/
	function getWidth()
	{
		return $this->width;
	}

	/**
	* Set width.
	*
	* @param	integer	$a_width	width
	*/
	function setWidth($a_width)
	{
		$this->width = $a_width;
	}

	/**
	* Get height.
	*
	* @return	integer	height
	*/
	function getHeight()
	{
		return $this->height;
	}

	/**
	* Set height.
	*
	* @param	integer	$a_height	height
	*/
	function setHeight($a_height)
	{
		$this->height = $a_height;
	}

	/**
	* Insert property html
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		if ($this->getApplet() != "")
		{
			$a_tpl->setCurrentBlock("applet");
			$a_tpl->setVariable("APPLET_WIDTH", $this->getWidth());
			$a_tpl->setVariable("APPLET_HEIGHT", $this->getHeight());
			$a_tpl->setVariable("POST_VAR_D", $this->getPostVar());
			$a_tpl->setVariable("TEXT_WIDTH", $lng->txt("width"));
			$a_tpl->setVariable("TEXT_HEIGHT", $lng->txt("height"));
			$a_tpl->setVariable("APPLET_FILE", basename($this->getApplet()));
			$a_tpl->setVariable("APPLET_PATH", str_replace(ILIAS_ABSOLUTE_PATH, ILIAS_HTTP_PATH, $this->getApplet()));
			if ($this->getWidth()) $a_tpl->setVariable("VALUE_WIDTH", "value=\"" . $this->getWidth() . "\"");
			if ($this->getHeight()) $a_tpl->setVariable("VALUE_HEIGHT", "value=\"" . $this->getHeight() . "\"");
			$a_tpl->setVariable("ID", $this->getFieldId());
			$a_tpl->setVariable("TXT_DELETE_EXISTING",
				$lng->txt("delete_existing_file"));
			$a_tpl->parseCurrentBlock();
		}
		
		$a_tpl->setCurrentBlock("prop_flash_file");
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->setVariable("ID", $this->getFieldId());
		$a_tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice")." ".
			$this->getMaxFileSizeString());
		$a_tpl->parseCurrentBlock();
	}

	/**
	* Get deletion flag
	*/
	function getDeletionFlag()
	{
		if ($_POST[$this->getPostVar()."_delete"])
		{
			return true;
		}
		return false;
	}

}
