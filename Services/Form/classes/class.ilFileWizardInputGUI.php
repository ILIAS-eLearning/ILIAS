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
* This class represents a file wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id: class.ilFileWizardInputGUI.php 18834 2009-02-03 10:10:29Z hschottm $
* @ingroup	ServicesForm
*/
class ilFileWizardInputGUI extends ilFileInputGUI
{
	protected $filenames = array();
	protected $allowMove = false;
	protected $imagepath_web = "";
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
	}

	/**
	* Set the web image path
	*
	* @param string $a_path Path
	*/
	public function setImagePathWeb($a_path)
	{
		$this->imagepath_web = $a_path;
	}
	
	/**
	* Get the web image path
	*
	* @return string Path
	*/
	public function getImagePathWeb()
	{
		return $this->imagepath_web;
	}

	/**
	* Set filenames
	*
	* @param	array	$a_value	Value
	*/
	function setFilenames($a_filenames)
	{
		$this->filenames = $a_filenames;
	}

	/**
	* Get filenames
	*
	* @return	array	filenames
	*/
	function getFilenames()
	{
		return $this->filenames;
	}

	/**
	* Set allow move
	*
	* @param	boolean	$a_allow_move Allow move
	*/
	function setAllowMove($a_allow_move)
	{
		$this->allowMove = $a_allow_move;
	}

	/**
	* Get allow move
	*
	* @return	boolean	Allow move
	*/
	function getAllowMove()
	{
		return $this->allowMove;
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$pictures = $_FILES[$this->getPostVar()];
		$uploadcheck = true;
		if (is_array($pictures))
		{
			foreach ($pictures['name'] as $index => $name)
			{
				// remove trailing '/'
				while (substr($name, -1) == '/')
				{
					$name = substr($name, 0, -1);
				}

				$filename = $name;
				$filename_arr = pathinfo($name);
				$suffix = $filename_arr["extension"];
				$mimetype = $pictures["type"][$index];
				$size_bytes = $pictures["size"][$index];
				$temp_name = $pictures["tmp_name"][$index];
				$error = $pictures["error"][$index];
				// error handling
				if ($error > 0)
				{
					switch ($error)
					{
						case UPLOAD_ERR_INI_SIZE:
							$this->setAlert($lng->txt("form_msg_file_size_exceeds"));
							$uploadcheck = false;
							break;

						case UPLOAD_ERR_FORM_SIZE:
							$this->setAlert($lng->txt("form_msg_file_size_exceeds"));
							$uploadcheck = false;
							break;

						case UPLOAD_ERR_PARTIAL:
							$this->setAlert($lng->txt("form_msg_file_partially_uploaded"));
							$uploadcheck = false;
							break;

						case UPLOAD_ERR_NO_FILE:
							if ($this->getRequired())
							{
								$filename = $this->filenames[$index];
								if (!strlen($filename))
								{
									$this->setAlert($lng->txt("form_msg_file_no_upload"));
									$uploadcheck = false;
								}
							}
							break;

						case UPLOAD_ERR_NO_TMP_DIR:
							$this->setAlert($lng->txt("form_msg_file_missing_tmp_dir"));
							$uploadcheck = false;
							break;

						case UPLOAD_ERR_CANT_WRITE:
							$this->setAlert($lng->txt("form_msg_file_cannot_write_to_disk"));
							$uploadcheck = false;
							break;

						case UPLOAD_ERR_EXTENSION:
							$this->setAlert($lng->txt("form_msg_file_upload_stopped_ext"));
							$uploadcheck = false;
							break;
					}
				}

				// check suffixes
				if ($pictures["tmp_name"][$index] != "" && is_array($this->getSuffixes()))
				{
					if (!in_array(strtolower($suffix), $this->getSuffixes()))
					{
						$this->setAlert($lng->txt("form_msg_file_wrong_file_type"));
						$uploadcheck = false;
					}
				}

				// virus handling
				if ($pictures["tmp_name"][$index] != "")
				{
					$vir = ilUtil::virusHandling($temp_name, $filename);
					if ($vir[0] == false)
					{
						$this->setAlert($lng->txt("form_msg_file_virus_found")."<br />".$vir[1]);
						$uploadcheck = false;
					}
				}
			}

		}

		if (!$uploadcheck)
		{
			return FALSE;
		}
		
		return $this->checkSubItemsInput();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.prop_filewizardinput.html", true, true, "Services/Form");

		$i = 0;
		foreach ($this->filenames as $value)
		{
			if (strlen($value))
			{
				$tpl->setCurrentBlock("image");
				$tpl->setVariable("SRC_IMAGE", $this->getImagePathWeb() . ilUtil::prepareFormOutput($value));
				$tpl->setVariable("PICTURE_FILE", ilUtil::prepareFormOutput($value));
				$tpl->setVariable("ID", $this->getFieldId() . "[$i]");
				$tpl->setVariable("ALT_IMAGE", ilUtil::prepareFormOutput($value));
				$tpl->parseCurrentBlock();
			}
			if ($this->getAllowMove())
			{
				$tpl->setCurrentBlock("move");
				$tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
				$tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
				$tpl->setVariable("ID", $this->getFieldId() . "[$i]");
				include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
				$tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
				$tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));							
				$tpl->parseCurrentBlock();
			}

			$this->outputSuffixes($tpl, "allowed_image_suffixes");
			
			$tpl->setCurrentBlock("row");			
			$tpl->setVariable("POST_VAR", $this->getPostVar() . "[$i]");
			$tpl->setVariable("ID", $this->getFieldId() . "[$i]");
			$tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
			$tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
			$tpl->setVariable("ALT_ADD", $lng->txt("add"));
			$tpl->setVariable("ALT_REMOVE", $lng->txt("remove"));
			if ($this->getDisabled())
			{
				$tpl->setVariable("DISABLED",
					" disabled=\"disabled\"");
			}
			
			include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
			$tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
			$tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));			
			$tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice") . " " . $this->getMaxFileSizeString());
			$tpl->parseCurrentBlock();
			$i++;
		}
		$tpl->setVariable("ELEMENT_ID", $this->getFieldId());

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
		
		global $tpl;		
		$tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
		$tpl->addJavascript("./Services/Form/templates/default/filewizard.js");
	}
}
