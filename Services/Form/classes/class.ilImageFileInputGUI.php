<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
		global $lng;

		parent::__construct($a_title, $a_postvar);
		$this->setType("image_file");
		$this->setSuffixes(array("jpg", "jpeg", "png", "gif"));
		$this->setHiddenTitle("(".$lng->txt("form_image_file_input").")");
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
		
		$i_tpl = new ilTemplate("tpl.prop_image_file.html", true, true, "Services/Form");
		
		$this->outputSuffixes($i_tpl, "allowed_image_suffixes");
		
		if ($this->getImage() != "")
		{
			if (!$this->getDisabled())
			{
				$i_tpl->setCurrentBlock("delete_bl");
				$i_tpl->setVariable("POST_VAR_D", $this->getPostVar());
				$i_tpl->setVariable("TXT_DELETE_EXISTING",
					$lng->txt("delete_existing_file"));
				$i_tpl->parseCurrentBlock();
			}
			
			if (strlen($this->getValue()))
			{
				$i_tpl->setCurrentBlock("has_value");
				$i_tpl->setVariable("TEXT_IMAGE_NAME", $this->getValue());
				$i_tpl->parseCurrentBlock();
			}
			$i_tpl->setCurrentBlock("image");
			$i_tpl->setVariable("SRC_IMAGE", $this->getImage());
			$i_tpl->setVariable("ALT_IMAGE", $this->getAlt());
			$i_tpl->parseCurrentBlock();
		}
		
		$i_tpl->setVariable("POST_VAR", $this->getPostVar());
		$i_tpl->setVariable("ID", $this->getFieldId());
		$i_tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice")." ".
			$this->getMaxFileSizeString());
			
		if ($this->getDisabled())
		{
			$i_tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
		}
			
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $i_tpl->get());
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
