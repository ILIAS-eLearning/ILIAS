<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.ilSingleChoiceWizardInputGUI.php";

class ilEssayKeywordWizardInputGUI extends ilSingleChoiceWizardInputGUI
{
	/**
	 * Set Value.
	 *
	 * @param    string    $a_value    Value
	 */
	function setValue($a_value)
	{
		$this->values = array();
		if (is_array( $a_value ))
		{
			if (is_array( $a_value['answer'] ))
			{
				foreach ($a_value['answer'] as $index => $value)
				{
					include_once "./Modules/TestQuestionPool/classes/class.assAnswerMultipleResponseImage.php";
					$answer = new ASS_AnswerMultipleResponseImage($value, $a_value['points'][$index], $index, $a_value['points_unchecked'][$index], $a_value['imagename'][$index]);
					array_push( $this->values, $answer );
				}
			}
		}
	}

	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return    boolean        Input ok, true/false
	 */
	function checkInput()
	{
		global $lng;

		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		if (is_array( $_POST[$this->getPostVar()] ))
		{
			$_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive( $_POST[$this->getPostVar()], false,
																		 ilObjAdvancedEditing::_getUsedHTMLTagsAsString( "assessment"
																		 )
			);
		}
		$foundvalues = $_POST[$this->getPostVar()];
		if (is_array( $foundvalues ))
		{
			// check answers
			if (is_array( $foundvalues['answer'] ))
			{
				foreach ($foundvalues['answer'] as $aidx => $answervalue)
				{
					if (((strlen( $answervalue )) == 0) && (strlen( $foundvalues['imagename'][$aidx] ) == 0))
					{
						$this->setAlert( $lng->txt( "msg_input_is_required" ) );
						return FALSE;
					}
				}
			}
			// check points
			$max = 0;
			if (is_array( $foundvalues['points'] ))
			{
				foreach ($foundvalues['points'] as $points)
				{
					if ($points > $max)
					{
						$max = $points;
					}
					if (((strlen( $points )) == 0) || (!is_numeric( $points )))
					{
						$this->setAlert( $lng->txt( "form_msg_numeric_value_required" ) );
						return FALSE;
					}
				}
			}
			if ($max == 0)
			{
				$this->setAlert( $lng->txt( "enter_enough_positive_points" ) );
				return false;
			}

			if (is_array( $_FILES ) && count( $_FILES ) && $this->getSingleline())
			{
				if (!$this->hideImages)
				{
					if (is_array( $_FILES[$this->getPostVar()]['error']['image'] ))
					{
						foreach ($_FILES[$this->getPostVar()]['error']['image'] as $index => $error)
						{
							// error handling
							if ($error > 0)
							{
								switch ($error)
								{
									case UPLOAD_ERR_INI_SIZE:
										$this->setAlert( $lng->txt( "form_msg_file_size_exceeds" ) );
										return false;
										break;

									case UPLOAD_ERR_FORM_SIZE:
										$this->setAlert( $lng->txt( "form_msg_file_size_exceeds" ) );
										return false;
										break;

									case UPLOAD_ERR_PARTIAL:
										$this->setAlert( $lng->txt( "form_msg_file_partially_uploaded" ) );
										return false;
										break;

									case UPLOAD_ERR_NO_FILE:
										if ($this->getRequired())
										{
											if ((!strlen( $foundvalues['imagename'][$index]
											)) && (!strlen( $foundvalues['answer'][$index] ))
											)
											{
												$this->setAlert( $lng->txt( "form_msg_file_no_upload" ) );
												return false;
											}
										}
										break;

									case UPLOAD_ERR_NO_TMP_DIR:
										$this->setAlert( $lng->txt( "form_msg_file_missing_tmp_dir" ) );
										return false;
										break;

									case UPLOAD_ERR_CANT_WRITE:
										$this->setAlert( $lng->txt( "form_msg_file_cannot_write_to_disk" ) );
										return false;
										break;

									case UPLOAD_ERR_EXTENSION:
										$this->setAlert( $lng->txt( "form_msg_file_upload_stopped_ext" ) );
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
							$this->setAlert( $lng->txt( "form_msg_file_no_upload" ) );
							return false;
						}
					}

					if (is_array( $_FILES[$this->getPostVar()]['tmp_name']['image'] ))
					{
						foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname)
						{
							$filename     = $_FILES[$this->getPostVar()]['name']['image'][$index];
							$filename_arr = pathinfo( $filename );
							$suffix       = $filename_arr["extension"];
							$mimetype     = $_FILES[$this->getPostVar()]['type']['image'][$index];
							$size_bytes   = $_FILES[$this->getPostVar()]['size']['image'][$index];
							// check suffixes
							if (strlen( $tmpname ) && is_array( $this->getSuffixes() ))
							{
								if (!in_array( strtolower( $suffix ), $this->getSuffixes() ))
								{
									$this->setAlert( $lng->txt( "form_msg_file_wrong_file_type" ) );
									return false;
								}
							}
						}
					}

					if (is_array( $_FILES[$this->getPostVar()]['tmp_name']['image'] ))
					{
						foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname)
						{
							$filename     = $_FILES[$this->getPostVar()]['name']['image'][$index];
							$filename_arr = pathinfo( $filename );
							$suffix       = $filename_arr["extension"];
							$mimetype     = $_FILES[$this->getPostVar()]['type']['image'][$index];
							$size_bytes   = $_FILES[$this->getPostVar()]['size']['image'][$index];
							// virus handling
							if (strlen( $tmpname ))
							{
								$vir = ilUtil::virusHandling( $tmpname, $filename );
								if ($vir[0] == false)
								{
									$this->setAlert( $lng->txt( "form_msg_file_virus_found" ) . "<br />" . $vir[1] );
									return false;
								}
							}
						}
					}
				}
			}
		}
		else
		{
			$this->setAlert( $lng->txt( "msg_input_is_required" ) );
			return FALSE;
		}

		return $this->checkSubItemsInput();
	}

	/**
	 * Insert property html
	 *
	 * @return    int    Size
	 */
	function insert(&$a_tpl)
	{
		global $lng;

		$tpl = new ilTemplate("tpl.prop_essaykeywordswizardinput.html", true, true, "Modules/TestQuestionPool");
		$i   = 0;
		foreach ($this->values as $value)
		{
			if ($this->getSingleline())
			{
				if (is_object( $value ))
				{
					$tpl->setCurrentBlock( "prop_text_propval" );
					$tpl->setVariable( "PROPERTY_VALUE", ilUtil::prepareFormOutput( $value->getAnswertext() ) );
					$tpl->parseCurrentBlock();
					$tpl->setCurrentBlock( "prop_points_propval" );
					$tpl->setVariable( "PROPERTY_VALUE", ilUtil::prepareFormOutput( $value->getPointsChecked() ) );
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock( 'singleline' );
				$tpl->setVariable( "SIZE", $this->getSize() );
				$tpl->setVariable( "SINGLELINE_ID", $this->getPostVar() . "[answer][$i]" );
				$tpl->setVariable( "SINGLELINE_ROW_NUMBER", $i );
				$tpl->setVariable( "SINGLELINE_POST_VAR", $this->getPostVar() );
				$tpl->setVariable( "MAXLENGTH", $this->getMaxLength() );
				if ($this->getDisabled())
				{
					$tpl->setVariable( "DISABLED_SINGLELINE", " disabled=\"disabled\"" );
				}
				$tpl->parseCurrentBlock();
			}
			else {
				if (!$this->getSingleline())
				{
					if (is_object( $value ))
					{
						$tpl->setCurrentBlock( "prop_points_propval" );
						$tpl->setVariable( "PROPERTY_VALUE", ilUtil::prepareFormOutput( $value->getPoints() ) );
						$tpl->parseCurrentBlock();
					}
				}
			}

			$tpl->setCurrentBlock( "row" );
			$tpl->setVariable( "POST_VAR", $this->getPostVar() );
			$tpl->setVariable( "ROW_NUMBER", $i );
			$tpl->setVariable( "ID", $this->getPostVar() . "[answer][$i]" );
			$tpl->setVariable( "POINTS_ID", $this->getPostVar() . "[points][$i]" );
			$tpl->setVariable( "CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]" );
			$tpl->setVariable( "CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]" );
			if ($this->getDisabled())
			{
				$tpl->setVariable( "DISABLED_POINTS", " disabled=\"disabled\"" );
			}
			$tpl->setVariable( "ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD) );
			$tpl->setVariable( "REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE) );
			$tpl->parseCurrentBlock();
			$i++;
		}

		$tpl->setVariable( "ELEMENT_ID", $this->getPostVar() );
		$tpl->setVariable( "TEXT_YES", $lng->txt( 'yes' ) );
		$tpl->setVariable( "TEXT_NO", $lng->txt( 'no' ) );
		$tpl->setVariable( "DELETE_IMAGE_HEADER", $lng->txt( 'delete_image_header' ) );
		$tpl->setVariable( "DELETE_IMAGE_QUESTION", $lng->txt( 'delete_image_question' ) );
		$tpl->setVariable( "ANSWER_TEXT", $lng->txt( 'answer_text' ) );
		$tpl->setVariable( "POINTS_TEXT", $lng->txt( 'points' ) );
		$tpl->setVariable( "COMMANDS_TEXT", $lng->txt( 'actions' ) );
		$tpl->setVariable( "POINTS_CHECKED_TEXT", $lng->txt( 'checkbox_checked' ) );

		$a_tpl->setCurrentBlock( "prop_generic" );
		$a_tpl->setVariable( "PROP_GENERIC", $tpl->get() );
		$a_tpl->parseCurrentBlock();

		global $tpl;
		$tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
		$tpl->addJavascript("./Modules/TestQuestionPool/templates/default/essaykeywordwizard.js");
	}

}
