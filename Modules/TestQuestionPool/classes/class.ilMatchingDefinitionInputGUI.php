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
* This class represents a matching definition wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
include_once "./Services/Form/classes/class.ilPropertyFormGUI.php";

class ilMatchingDefinitionInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $values = array();
	protected $terms = array();
	protected $images = array();
	protected $subtype = 1; // terms and definitions
	protected $maxlength = 200;
	protected $size = 40;
	protected $validationRegexp;
	protected $filename;
	protected $filename_post;
	protected $imagepathweb = "";
	protected $suffixes = array();
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->validationRegexp = "";
		$this->setSuffixes(array("jpg", "jpeg", "png", "gif"));
	}

	/**
	* Set Terms
	*
	* @param	array	$a_terms	Terms
	*/
	function setTerms($a_terms)
	{
		$this->terms = $a_terms;
	}

	/**
	* Get Terms
	*
	* @return	array	Terms
	*/
	function getTerms()
	{
		return $this->terms;
	}

	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setValue($a_values);
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->values = array();
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
		$matchingterms = $_POST['matchingterms'];
		$definitions = $_POST['definition'];
		$imagefiles = $_POST['image_filename'];

		if (is_array($matchingterms))
		{
			foreach ($matchingterms as $idx => $matchingterm)
			{
				$points = $_POST['points'][$idx];
				$term_id = $matchingterms[$idx];
				if (is_array($definitions))
				{
					$picture_or_definition = $definitions[$idx];
				}
				else
				{
					$picture_or_definition = $imagefiles[$idx];
				}
				$pair = new ASS_AnswerMatching(
					$points,
					$term_id,
					$picture_or_definition,
					$picture_or_definition_id
				);
				array_push($this->values, $pair);
			}
		}	
	}

	/**
	* Set subtype
	*
	* @param	int	$a_subtype	subtype
	*/
	function setSubtype($a_subtype)
	{
		$this->subtype = $a_subtype;
	}

	/**
	* Get subtype
	*
	* @return	int	subtype
	*/
	function getSubtype()
	{
		return $this->subtype;
	}

	/**
	* Set image path web
	*
	* @param	string	$a_imagepath	image path
	*/
	function setImagepathWeb($a_imagepath)
	{
		$this->imagepathweb = $a_imagepath;
	}

	/**
	* Get image path web
	*
	* @return	string	image path
	*/
	function getImagepathWeb()
	{
		return $this->imagepathweb;
	}

	/**
	* Set Values
	*
	* @param	object	$a_value	Value
	*/
	function setValues($a_values)
	{
		$this->values = $a_values;
	}

	/**
	* Get Values
	*
	* @return	object	Values
	*/
	function getValues()
	{
		return $this->values;
	}

	/**
	* Set validation regexp.
	*
	* @param	string	$a_value	regexp
	*/
	function setValidationRegexp($a_value)
	{
		$this->validationRegexp = $a_value;
	}

	/**
	* Get validation regexp.
	*
	* @return	string	regexp
	*/
	function getValidationRegexp()
	{
		return $this->validationRegexp;
	}

	/**
	* Set Max Length.
	*
	* @param	int	$a_maxlength	Max Length
	*/
	function setMaxLength($a_maxlength)
	{
		$this->maxlength = $a_maxlength;
	}

	/**
	* Get Max Length.
	*
	* @return	int	Max Length
	*/
	function getMaxLength()
	{
		return $this->maxlength;
	}

	/**
	* Set Size.
	*
	* @param	int	$a_size	Size
	*/
	function setSize($a_size)
	{
		$this->size = $a_size;
	}

	/**
	* Get Size.
	*
	* @return	int	Size
	*/
	function getSize()
	{
		return $this->size;
	}
	
	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;

		$check = true;
		
		$definitions = $_POST['definition'];
		if (is_array($definitions))
		{
			foreach ($definitions as $idx => $definition)
			{
				if ($this->getRequired() && trim($definition) == "")
				{
					$_POST['definition'][$idx] = ilUtil::stripSlashes($definition);
					$this->setAlert($lng->txt("msg_input_is_required"));
					$check = false;
				}
			}
		}
		$points = $_POST['points'];
		if (is_array($points))
		{
			foreach ($points as $point)
			{
				if ($this->getRequired() && trim($point) == "")
				{
					$_POST['points'][$idx] = ilUtil::stripSlashes($point);
					$this->setAlert($lng->txt("msg_input_is_required"));
					$check = false;
				}
			}
		}
		$matchingterms = $_POST['matchingterms'];
		if (is_array($matchingterms))
		{
			$found_terms = array();
			foreach ($matchingterms as $matchingterm)
			{
				$found_terms[$matchingterm] = 1;
			}
			if (count($found_terms) != count($matchingterms))
			{
				$this->setAlert($lng->txt("error_duplicate_term"));
				$check = false;
			}
		}

		$pictures = $_FILES['picture'];
		if (is_array($pictures))
		{
			foreach ($pictures['name'] as $index => $name)
			{
				$uploadcheck = true;
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
								$pair = $this->values[$index];
								$picture = (is_object($pair)) ? $pair->getPicture() : "";
								if (!strlen($picture))
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
				$check = $check & $uploadcheck;
			}

		}
		if (!$check) return $check;
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
		
		$tpl = new ilTemplate("tpl.prop_matchingdefinitioninput.html", true, true, "Modules/TestQuestionPool");
		$i = 0;
		foreach ($this->values as $value)
		{
			if (is_object($value))
			{
				if ($this->getSubtype() == 1)
				{
					// Terms and Definitions
					if (strlen($value->getDefinition()))
					{
						$tpl->setCurrentBlock("prop_text_propval");
						$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getDefinition()));
						$tpl->parseCurrentBlock();
					}
					$tpl->setCurrentBlock("definitions");
					$tpl->setVariable("SIZE", $this->getSize());
					$tpl->setVariable("POST_VAR_DEFINITION", "definition[$i]");
					$tpl->setVariable("INDEX_DEFINITION", "$i");
					$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
					$tpl->parseCurrentBlock();
				}
				else
				{
					// Terms and Pictures
					$this->outputSuffixes($tpl, "allowed_image_suffixes");
					if (strlen($value->getPicture()))
					{
						$tpl->setCurrentBlock("image");
						$tpl->setVariable("SRC_IMAGE", $this->getImagepathWeb() . $value->getPicture().".thumb.jpg");
						$tpl->setVariable("NAME_IMAGE_FILENAME", "image_filename[$i]");
						$tpl->setVariable("VALUE_IMAGE_FILENAME", $value->getPicture());
						$tpl->setVariable("ALT_IMAGE", $value->getPicture());
						$tpl->setVariable("POST_VAR_PICTURE_DELETE", "picture_delete[$i]");
						$tpl->setVariable("ID_PICTURE_DELETE", "picture_delete_$i");
						$tpl->setVariable("TXT_DELETE_EXISTING", $lng->txt("delete_existing_file"));
						$tpl->parseCurrentBlock();
					}

					$tpl->setCurrentBlock("pictures");
					$tpl->setVariable("POST_VAR_PICTURE", "picture[$i]");
					$tpl->setVariable("INDEX_PICTURE", "$i");
					$tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice")." ".$this->getMaxFileSizeString());
					$tpl->parseCurrentBlock();
					
				}
				
				foreach ($this->terms as $termid => $term)
				{
					$tpl->setCurrentBlock("select_option");
					$tpl->setVariable("OPTION_VALUE", $termid);
					$tpl->setVariable("OPTION_NAME", ilUtil::prepareFormOutput($term));
					if ($value->getTermId() == $termid) $tpl->setVariable("OPTION_SELECTED", " selected=\"selected\"");
					$tpl->parseCurrentBlock();
				}
				if (strlen($value->getPoints()))
				{
					$tpl->setCurrentBlock("prop_points_propval");
					$tpl->setVariable("VALUE_POINTS", ilUtil::prepareFormOutput($value->getPoints()));
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("row");
				$class = ($i % 2 == 0) ? "even" : "odd";
				if ($i == 0) $class .= " first";
				if ($i == count($this->values)-1) $class .= " last";
				$tpl->setVariable("ROW_CLASS", $class);
				$tpl->setVariable("TEXT_MATCHES", $lng->txt('matches'));
				$tpl->setVariable("SELECT_NAME", "matchingterms[$i]");
				$tpl->setVariable("NAME_POINTS", "points[$i]");
				$tpl->setVariable("INDEX", "$i");
				$tpl->setVariable("ADD_BUTTON", ilUtil::getImagePath('edit_add.png'));
				$tpl->setVariable("REMOVE_BUTTON", ilUtil::getImagePath('edit_remove.png'));
				$tpl->parseCurrentBlock();
			}
			$i++;
		}
		$tpl->setVariable("ELEMENT_ID", $this->getFieldId());

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
		
		global $tpl;
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initDomEvent();
		$tpl->addJavascript("./Modules/TestQuestionPool/templates/default/matchingdefinition.js");
	}

	protected function outputSuffixes($a_tpl, $a_block = "allowed_suffixes")
	{
		global $lng;
		
		if (is_array($this->getSuffixes()))
		{
			$suff_str = $delim = "";
			foreach($this->getSuffixes() as $suffix)
			{
				$suff_str.= $delim.".".$suffix;
				$delim = ", ";
			}
			$a_tpl->setCurrentBlock($a_block);
			$a_tpl->setVariable("TXT_ALLOWED_SUFFIXES", $lng->txt("file_allowed_suffixes")." ".$suff_str);
			$a_tpl->parseCurrentBlock();
		}
	}
	
	protected function getMaxFileSizeString()
	{
		// get the value for the maximal uploadable filesize from the php.ini (if available)
		$umf = get_cfg_var("upload_max_filesize");
		// get the value for the maximal post data from the php.ini (if available)
		$pms = get_cfg_var("post_max_size");
		
		//convert from short-string representation to "real" bytes
		$multiplier_a=array("K"=>1024, "M"=>1024*1024, "G"=>1024*1024*1024);
		
		$umf_parts=preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
		$pms_parts=preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

		if (count($umf_parts) == 2) { $umf = $umf_parts[0]*$multiplier_a[$umf_parts[1]]; }
		if (count($pms_parts) == 2) { $pms = $pms_parts[0]*$multiplier_a[$pms_parts[1]]; }

		// use the smaller one as limit
		$max_filesize = min($umf, $pms);

		if (!$max_filesize) $max_filesize=max($umf, $pms);

		//format for display in mega-bytes
		$max_filesize = sprintf("%.1f MB",$max_filesize/1024/1024);

		return $max_filesize;
	}

	/**
	* Set Accepted Suffixes.
	*
	* @param	array	$a_suffixes	Accepted Suffixes
	*/
	function setSuffixes($a_suffixes)
	{
		$this->suffixes = $a_suffixes;
	}

	/**
	* Get Accepted Suffixes.
	*
	* @return	array	Accepted Suffixes
	*/
	function getSuffixes()
	{
		return $this->suffixes;
	}

	/**
	 * Set filename value (if filename selection is enabled)
	 *  
	 * @param string $a_val
	 */
	public function setFilename($a_val)
	{
		$this->filename = $a_val;
	}
	
	/**
	* Get filename
	*
	* @return	string	filename
	*/
	function getFilename()
	{
		return $this->filename;
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
}
