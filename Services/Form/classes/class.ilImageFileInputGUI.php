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
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilImageFileInputGUI extends ilFileInputGUI
{
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("image_file");
		$this->setSuffixes(array("jpg", "jpeg", "png", "gif"));
	}

	/**
	* Set Image.
	*
	* @param	string	$a_image	Image
	*/
	function setImage($a_image)
	{
		$this->image = $a_image;
	}

	/**
	* Get Image.
	*
	* @return	string	Image
	*/
	function getImage()
	{
		return $this->image;
	}

	/**
	* Set Alternative Text.
	*
	* @param	string	$a_alt	Alternative Text
	*/
	function setAlt($a_alt)
	{
		$this->alt = $a_alt;
	}

	/**
	* Get Alternative Text.
	*
	* @return	string	Alternative Text
	*/
	function getAlt()
	{
		return $this->alt;
	}

	/**
	* Insert property html
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		$this->outputSuffixes($a_tpl, "allowed_image_suffixes");
		
		if ($this->getImage() != "")
		{
			$a_tpl->setCurrentBlock("image");
			$a_tpl->setVariable("SRC_IMAGE", $this->getImage());
			$a_tpl->setVariable("ALT_IMAGE", $this->getAlt());
			$a_tpl->setVariable("POST_VAR_D", $this->getPostVar());
			$a_tpl->setVariable("TXT_DELETE_EXISTING",
				$lng->txt("delete_existing_file"));
			$a_tpl->parseCurrentBlock();
		}
		
		$a_tpl->setCurrentBlock("prop_image_file");
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
