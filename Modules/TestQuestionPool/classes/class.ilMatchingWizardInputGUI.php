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

include_once "./Modules/TestQuestionPool/classes/class.ilSingleChoiceWizardInputGUI.php";

/**
* This class represents a single choice wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilMatchingWizardInputGUI extends ilTextInputGUI
{
	protected $text_name = '';
	protected $image_name = '';
	protected $values = array();
	protected $allowMove = false;
	protected $qstObject = null;
	protected $suffixes = array();
	protected $hideImages = false;

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
		$this->setSuffixes(array("jpg", "jpeg", "png", "gif"));
		$this->setSize('25');
		$this->text_name = $lng->txt('answer_text');
		$this->image_name = $lng->txt('answer_image');
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
	* Set hide images.
	*
	* @param	array	$a_hide	Hide images
	*/
	function setHideImages($a_hide)
	{
		$this->hideImages = $a_hide;
	}

	/**
	* Set Values
	*
	* @param	array	$a_value	Value
	*/
	function setValues($a_values)
	{
		$this->values = $a_values;
	}

	/**
	* Get Values
	*
	* @return	array	Values
	*/
	function getValues()
	{
		return $this->values;
	}

	function setTextName($a_value)
	{
		$this->text_name = $a_value;
	}
	
	function setImageName($a_value)
	{
		$this->image_name = $a_value;
	}
	
	/**
	* Set question object
	*
	* @param	object	$a_value	test object
	*/
	function setQuestionObject($a_value)
	{
		$this->qstObject =& $a_value;
	}

	/**
	* Get question object
	*
	* @return	object	Value
	*/
	function getQuestionObject()
	{
		return $this->qstObject;
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
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->values = array();
		if (is_array($a_value))
		{
			if (is_array($a_value['answer']))
			{
				foreach ($a_value['answer'] as $index => $value)
				{
					include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
					$answer = new assAnswerMatchingTerm($value, $a_value['imagename'][$index], $a_value['identifier'][$index]);
					array_push($this->values, $answer);
				}
			}
		}
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$foundvalues = $_POST[$this->getPostVar()];
		if (is_array($foundvalues))
		{
			// check answers
			if (is_array($foundvalues['answer']))
			{
				foreach ($foundvalues['answer'] as $aidx => $answervalue)
				{
					if (((strlen($answervalue)) == 0) && (strlen($foundvalues['imagename'][$aidx]) == 0))
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
				}
			}

			if (is_array($_FILES) && (!$this->hideImages))
			{
				if (is_array($_FILES[$this->getPostVar()]['error']['image']))
				{
					foreach ($_FILES[$this->getPostVar()]['error']['image'] as $index => $error)
					{
						// error handling
						if ($error > 0)
						{
							switch ($error)
							{
								case UPLOAD_ERR_INI_SIZE:
									$this->setAlert($lng->txt("form_msg_file_size_exceeds"));
									return false;
									break;

								case UPLOAD_ERR_FORM_SIZE:
									$this->setAlert($lng->txt("form_msg_file_size_exceeds"));
									return false;
									break;

								case UPLOAD_ERR_PARTIAL:
									$this->setAlert($lng->txt("form_msg_file_partially_uploaded"));
									return false;
									break;

								case UPLOAD_ERR_NO_FILE:
									if ($this->getRequired())
									{
										if ((!strlen($foundvalues['imagename'][$index])) && (!strlen($foundvalues['answer'][$index])))
										{
											$this->setAlert($lng->txt("form_msg_file_no_upload"));
											return false;
										}
									}
									break;

								case UPLOAD_ERR_NO_TMP_DIR:
									$this->setAlert($lng->txt("form_msg_file_missing_tmp_dir"));
									return false;
									break;

								case UPLOAD_ERR_CANT_WRITE:
									$this->setAlert($lng->txt("form_msg_file_cannot_write_to_disk"));
									return false;
									break;

								case UPLOAD_ERR_EXTENSION:
									$this->setAlert($lng->txt("form_msg_file_upload_stopped_ext"));
									return false;
									break;
							}
						}
					}
				}
				else
				{
					if ($this->getRequired())
					{
						$this->setAlert($lng->txt("form_msg_file_no_upload"));
						return false;
					}
				}

				if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image']))
				{
					foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname)
					{
						$filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
						$filename_arr = pathinfo($filename);
						$suffix = $filename_arr["extension"];
						$mimetype = $_FILES[$this->getPostVar()]['type']['image'][$index];
						$size_bytes = $_FILES[$this->getPostVar()]['size']['image'][$index];
						// check suffixes
						if (strlen($tmpname) && is_array($this->getSuffixes()))
						{
							if (!in_array(strtolower($suffix), $this->getSuffixes()))
							{
								$this->setAlert($lng->txt("form_msg_file_wrong_file_type"));
								return false;
							}
						}
					}
				}

				if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image']))
				{
					foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname)
					{
						$filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
						$filename_arr = pathinfo($filename);
						$suffix = $filename_arr["extension"];
						$mimetype = $_FILES[$this->getPostVar()]['type']['image'][$index];
						$size_bytes = $_FILES[$this->getPostVar()]['size']['image'][$index];
						// virus handling
						if (strlen($tmpname))
						{
							$vir = ilUtil::virusHandling($tmpname, $filename);
							if ($vir[0] == false)
							{
								$this->setAlert($lng->txt("form_msg_file_virus_found")."<br />".$vir[1]);
								return false;
							}
						}
					}
				}
			}
		}
		else
		{
			$this->setAlert($lng->txt("msg_input_is_required"));
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
		
		$tpl = new ilTemplate("tpl.prop_matchingwizardinput.html", true, true, "Modules/TestQuestionPool");
		$i = 0;
		foreach ($this->values as $value)
		{
			if (!$this->hideImages)
			{
				if (strlen($value->picture))
				{
					$imagename = $this->qstObject->getImagePathWeb() . $value->picture;
					if ($this->qstObject->getThumbSize())
					{
						if (@file_exists($this->qstObject->getImagePath() . $this->qstObject->getThumbPrefix() . $value->picture))
						{
							$imagename = $this->qstObject->getImagePathWeb() . $this->qstObject->getThumbPrefix() . $value->picture;
						}
					}
					$tpl->setCurrentBlock('image');
					$tpl->setVariable('SRC_IMAGE', $imagename);
					$tpl->setVariable('IMAGE_NAME', $value->picture);
					$tpl->setVariable('ALT_IMAGE', ilUtil::prepareFormOutput($value->text));
					$tpl->setVariable("TXT_DELETE_EXISTING", $lng->txt("delete_existing_file"));
					$tpl->setVariable("IMAGE_ROW_NUMBER", $i);
					$tpl->setVariable("IMAGE_POST_VAR", $this->getPostVar());
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock('addimage');
				$tpl->setVariable("IMAGE_ID", $this->getPostVar() . "[image][$i]");
				$tpl->setVariable("IMAGE_SUBMIT", $lng->txt("upload"));
				$tpl->setVariable("IMAGE_ROW_NUMBER", $i);
				$tpl->setVariable("IMAGE_POST_VAR", $this->getPostVar());
				$tpl->parseCurrentBlock();
			}

			if (is_object($value))
			{
				$tpl->setCurrentBlock("prop_text_propval");
				$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->text));
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock('singleline');
			$tpl->setVariable("SIZE", $this->getSize());
			$tpl->setVariable("SINGLELINE_ID", $this->getPostVar() . "[answer][$i]");
			$tpl->setVariable("SINGLELINE_ROW_NUMBER", $i);
			$tpl->setVariable("SINGLELINE_POST_VAR", $this->getPostVar());
			$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
			if ($this->getDisabled())
			{
				$tpl->setVariable("DISABLED_SINGLELINE", " disabled=\"disabled\"");
			}
			$tpl->parseCurrentBlock();
			if ($this->getAllowMove())
			{
				$tpl->setCurrentBlock("move");
				$tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
				$tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
				$tpl->setVariable("ID", $this->getPostVar() . "[$i]");
				$tpl->setVariable("UP_BUTTON", ilUtil::getImagePath('a_up.gif'));
				$tpl->setVariable("DOWN_BUTTON", ilUtil::getImagePath('a_down.gif'));
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("row");
			$class = ($i % 2 == 0) ? "even" : "odd";
			if ($i == 0) $class .= " first";
			if ($i == count($this->values)-1) $class .= " last";
			$tpl->setVariable("ROW_CLASS", $class);
			$tpl->setVariable("POST_VAR", $this->getPostVar());
			$tpl->setVariable("ROW_NUMBER", $i+1);
			$tpl->setVariable("ROW_IDENTIFIER", $value->identifier);
			$tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
			$tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
			$tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
			$tpl->setVariable("ADD_BUTTON", ilUtil::getImagePath('edit_add.png'));
			$tpl->setVariable("REMOVE_BUTTON", ilUtil::getImagePath('edit_remove.png'));
			$tpl->parseCurrentBlock();
			$i++;
		}

		if (!$this->hideImages)
		{
			if (is_array($this->getSuffixes()))
			{
				$suff_str = $delim = "";
				foreach($this->getSuffixes() as $suffix)
				{
					$suff_str.= $delim.".".$suffix;
					$delim = ", ";
				}
				$tpl->setCurrentBlock('allowed_image_suffixes');
				$tpl->setVariable("TXT_ALLOWED_SUFFIXES", $lng->txt("file_allowed_suffixes")." ".$suff_str);
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("image_heading");
			$tpl->setVariable("ANSWER_IMAGE", $this->image_name);
			$tpl->setVariable("TXT_MAX_SIZE", ilUtil::getFileSizeInfo());
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("TEXT_YES", $lng->txt('yes'));
		$tpl->setVariable("TEXT_NO", $lng->txt('no'));
		$tpl->setVariable("DELETE_IMAGE_HEADER", $lng->txt('delete_image_header'));
		$tpl->setVariable("DELETE_IMAGE_QUESTION", $lng->txt('delete_image_question'));
		$tpl->setVariable("ANSWER_TEXT", $this->text_name);
		$tpl->setVariable("NUMBER_TEXT", $lng->txt('row'));
		$tpl->setVariable("COMMANDS_TEXT", $lng->txt('actions'));

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
		/*
		global $tpl;
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initDomEvent();
		$tpl->addJavascript("./Modules/TestQuestionPool/templates/default/matchingwizard.js");*/
	}
}
