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
	protected $cache;
	
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
		$this->setAllowDeletion(true);
		$this->setSuffixes(array("jpg", "jpeg", "png", "gif"));
		$this->setHiddenTitle("(".$lng->txt("form_image_file_input").")");
		$this->cache = true;
	}

	/**
	 * Set allow deletion
	 *
	 * @param boolean $a_val allow deletion	
	 */
	function setALlowDeletion($a_val)
	{
		$this->allow_deletion = $a_val;
	}
	
	/**
	 * Get allow deletion
	 *
	 * @return boolean allow deletion
	 */
	function getALlowDeletion()
	{
		return $this->allow_deletion;
	}
	/**
	* Set cache
	*
	* @param	boolean	$a_cache	If false, the image will be forced to reload in the browser
	* by adding an URL parameter with the actual timestamp
	*/
	public function setUseCache($a_cache)
	{
		$this->cache = ($a_cache) ? true : false;
	}
	
	/**
	* Get cache
	*
	* @return boolean
	*/
	public function getUseCache()
	{
		return $this->cache;
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
		
		$quota_exceeded = $quota_legend = false;
		if(self::$check_wsp_quota)
		{
			include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
			if(!ilDiskQuotaHandler::isUploadPossible())
			{				
				$lng->loadLanguageModule("file");		
				$quota_exceeded = $lng->txt("personal_workspace_quota_exceeded_warning");				
			}
			else
			{							
				$quota_legend = ilDiskQuotaHandler::getStatusLegend();
				if($quota_legend)
				{
					$quota_legend = "<br />".$quota_legend;
				}
			}
		}
		
		$i_tpl = new ilTemplate("tpl.prop_image_file.html", true, true, "Services/Form");				
		
		if ($this->getImage() != "")
		{
			if (!$this->getDisabled() && $this->getALlowDeletion())
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
			if (!$this->getUseCache())
			{
				$pos = strpos($this->getImage(), '?');
				if ($pos !== false)
				{
					$i_tpl->setVariable("SRC_IMAGE", $this->getImage() . "&amp;time=" . time());
				}
				else
				{
					$i_tpl->setVariable("SRC_IMAGE", $this->getImage() . "?time=" . time());
				}
			}
			else
			{
				$i_tpl->setVariable("SRC_IMAGE", $this->getImage());
			}
			$i_tpl->setVariable("ALT_IMAGE", $this->getAlt());
			$i_tpl->parseCurrentBlock();
		}
		
		$pending = $this->getPending();
		if($pending)
		{
			$i_tpl->setCurrentBlock("pending");
			$i_tpl->setVariable("TXT_PENDING", $lng->txt("file_upload_pending").
				": ".$pending);
			$i_tpl->parseCurrentBlock();
		}
		
		$i_tpl->setVariable("POST_VAR", $this->getPostVar());
		$i_tpl->setVariable("ID", $this->getFieldId());
		
		if(!$quota_exceeded)
		{
			$i_tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice")." ".
				$this->getMaxFileSizeString().$quota_legend);
			
			$this->outputSuffixes($i_tpl, "allowed_image_suffixes");
		}
		else
		{
			$i_tpl->setVariable("TXT_MAX_SIZE", $quota_exceeded);
		}
			
		if ($this->getDisabled() || $quota_exceeded)
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
