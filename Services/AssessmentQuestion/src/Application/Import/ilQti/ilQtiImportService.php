<?php

namespace ILIAS\AssessmentQuestion\Application\Import\ilQti;

use Exception;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\ContentEditingMode;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionLegacyData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\MultipleChoiceScoringConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\MultipleChoiceScoringDefinition;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\OrderingScoringConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\TextSubsetScoringConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\TextSubsetScoringDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\AsqGUIElementFactory;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EmptyDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageMapEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\MatchingEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\MultipleChoiceEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\OrderingEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\TextSubsetEditorConfiguration;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;
use ilQTIItem;
use ilQTIMaterial;
use ilQTIMattext;
use ilQTIPresentation;
use ilQTIRenderChoice;
use ilQTIRespcondition;
use ilQTIResponse;
use ilQTIResponseLabel;
use ilQTIResprocessing;
use ilQTISetvar;
use SimpleXMLElement;


/**
 * Class ilQtiImportService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilQtiImportService
{
    const SINGLE_CHOICE_QUESTION = "SINGLE CHOICE QUESTION";
    const MULTIPLE_CHOICE_QUESTION = "MULTIPLE CHOICE QUESTION";
    const CLOZE_QUESTION = "CLOZE QUESTION";
    const ORDERING_HORIZONTAL = "assOrderingHorizontal";
    const ORDERING_QUESTION = "ORDERING QUESTION";
    const MATCHING_QUESTION = "MATCHING QUESTION";
    const IMAGE_MAP_QUESTION = "IMAGE MAP QUESTION";
    const TEXTSUBSET_QUESTION = "TEXTSUBSET QUESTION";
    const TEXT_QUESTION = "TEXT QUESTION";
    const FILE_UPLOAD_QUESTION = "assFileUpload";
    const NUMERIC_QUESTION = "NUMERIC QUESTION";
    const ERROR_TEXT = "assErrorText";


    /**
     * @var int
     */
    protected $container_obj_id;

    public function __construct(int $container_obj_id)
    {
        $this->obj_id = $container_obj_id;
    }


    public function getQuestionDtoFromIlQtiItem(ilQTIItem $il_qti_item):?QuestionDto
    {
        global $DIC;

        $question_dto = new QuestionDto();
        $question_title = $il_qti_item->getTitle();

        /** @var ilQTIMaterial $qti_mat_question_text */
        $qti_mat_question_text = $il_qti_item->getQuestiontext();
        $arr_materials = $qti_mat_question_text->materials;
        /** @var ilQTIMattext $mattext */
        $mattext = $arr_materials[0]['material'];
        $question_text = $mattext->getContent();
        $question_author = $il_qti_item->getAuthor();
        $question_duration = $il_qti_item->getDuration();

        //$correct_response_identifier = $this->getCorrectResponseFromXmlElement($simple_xml_element);
        //$max_score = $this->getMaxScoreFromXmlElement($simple_xml_element);

        $editor_configuration_is_single_line = boolval($il_qti_item->getMetadataEntry('singleline'));

        $arr_resprocessing = $il_qti_item->resprocessing;
        /** @var ilQTIResprocessing $resprocessing */
        $resprocessing = $arr_resprocessing[0];

        switch($il_qti_item->getQuestiontype()) {
            case self::SINGLE_CHOICE_QUESTION:
            case self::MULTIPLE_CHOICE_QUESTION:
            case self::CLOZE_QUESTION:
                $response = $this->getQtiResponse($il_qti_item);
                /** @var ilQTIRenderChoice $render_type */
                $render_type = $response->getRenderType();
                $answer_options = new AnswerOptions();
                $i = 1;
                foreach($render_type->response_labels as $key => $response_label) {
                    $answer_options = $this->addAnswerOption($response_label, $resprocessing, $key, $answer_options, $i);
                    $i++;
                }
                $max_answers = $i;
                break;
            case self::ORDERING_HORIZONTAL:
            case self::ORDERING_QUESTION:
                $answer_options = new AnswerOptions(); //TODO
                break;
            case self::IMAGE_MAP_QUESTION:
                $answer_options = new AnswerOptions(); //TODO
                break;
            case self::TEXTSUBSET_QUESTION:
                $answer_options = new AnswerOptions(); //TODO
                break;
            case self::TEXT_QUESTION:
                $answer_options = new AnswerOptions(); //TODO
                break;
        }

        $thumbnail_size = Null; //TODO;


        switch($il_qti_item->getQuestiontype()) {
            case self::SINGLE_CHOICE_QUESTION:
                $shuffle = $render_type->getShuffle();

                $legacy_data = $this->getQuestionLegacyData(AsqGUIElementFactory::TYPE_SINGLE_CHOICE, ContentEditingMode::RTE_TEXTAREA, 'tst');
                $editor = MultipleChoiceEditorConfiguration::create($shuffle, 1, $thumbnail_size, $editor_configuration_is_single_line);
                $scoring = new MultipleChoiceScoringConfiguration();
                break;
            case self::MULTIPLE_CHOICE_QUESTION:
                $shuffle = $render_type->getShuffle();

                $legacy_data = $this->getQuestionLegacyData(AsqGUIElementFactory::TYPE_MULTIPLE_CHOICE, ContentEditingMode::RTE_TEXTAREA, 'tst');
                $editor = MultipleChoiceEditorConfiguration::create($shuffle, $max_answers, $thumbnail_size, $editor_configuration_is_single_line);
                $scoring = new MultipleChoiceScoringConfiguration();
                break;
            case self::CLOZE_QUESTION:

                $legacy_data = $this->getQuestionLegacyData(AsqGUIElementFactory::TYPE_TEXT_SUBSET, ContentEditingMode::RTE_TEXTAREA, 'tst');
                $editor = TextSubsetEditorConfiguration::create(3); //TODO
                $scoring = new TextSubsetScoringConfiguration();
                break;
            case self::ORDERING_HORIZONTAL:
                $legacy_data = $this->getQuestionLegacyData(AsqGUIElementFactory::TYPE_ORDERING, ContentEditingMode::RTE_TEXTAREA, 'tst');
                $editor = OrderingEditorConfiguration::create(false, 2); //TODO
                $scoring = new OrderingScoringConfiguration();
                break;
            case self::ORDERING_QUESTION:
                $legacy_data = $this->getQuestionLegacyData(AsqGUIElementFactory::TYPE_ORDERING, ContentEditingMode::RTE_TEXTAREA, 'tst');
                $editor = OrderingEditorConfiguration::create(true, 2); //TODO
                $scoring = new OrderingScoringConfiguration();
                break;
            case self::MATCHING_QUESTION:
                $legacy_data = $this->getQuestionLegacyData(AsqGUIElementFactory::TYPE_MATCHING, ContentEditingMode::RTE_TEXTAREA, 'tst');
                $editor = MatchingEditorConfiguration::create(); //TODO
                $scoring = new OrderingScoringConfiguration();
                break;
            case self::IMAGE_MAP_QUESTION:
                $legacy_data = $this->getQuestionLegacyData(AsqGUIElementFactory::TYPE_IMAGE_MAP, ContentEditingMode::RTE_TEXTAREA, 'tst');
                $editor = ImageMapEditorConfiguration::create("", true); //TODO
                $scoring = new MultipleChoiceScoringConfiguration();
                break;
            case self::TEXTSUBSET_QUESTION:
                $legacy_data = $this->getQuestionLegacyData(AsqGUIElementFactory::TYPE_TEXT_SUBSET, ContentEditingMode::RTE_TEXTAREA, 'tst');
                $editor = TextSubsetEditorConfiguration::create(3); //TODO
                $scoring = new TextSubsetScoringConfiguration();
                break;
            case self::TEXT_QUESTION:
              //TODO
                $scoring = new TextSubsetScoringConfiguration();
                break;
        }



        $question_data = QuestionData::create($question_title, $question_text, $question_author);
        $play_configuration = QuestionPlayConfiguration::create($editor, $scoring);

        $feedback = Feedback::create($DIC->language()->txt('asq_label_right'), $DIC->language()->txt('asq_label_wrong'),Feedback::OPT_ANSWER_OPTION_FEEDBACK_MODE_ALL,[]);

        $question_dto->setData($question_data);

        if(is_null($legacy_data)) {
            print_r($il_qti_item);exit;
        }

        $question_dto->setLegacyData($legacy_data);

        if(!is_object($answer_options)) {
            print_r($il_qti_item);exit;
        }

        $question_dto->setAnswerOptions($answer_options);
        $question_dto->setPlayConfiguration($play_configuration);
        $question_dto->setFeedback($feedback);

        $question_dto->setContainerObjId($this->obj_id);

        return $question_dto;
    }


/*
        if (isset($simple_xml_element->itemBody->div->textEntryInteraction)) {
            $answer_options = $this->getTextSubsetQuestionOptionsFromXmlElement($simple_xml_element, $correct_response_identifier, $max_score);

            $legacy_data = QuestionLegacyData::create(AsqGUIElementFactory::TYPE_TEXT_SUBSET, ContentEditingMode::RTE_TEXTAREA, 'tst');

            //TODO?
            $editor = TextSubsetEditorConfiguration::create(1);
            $scoring = TextSubsetScoringConfiguration::create(1);
        }

        if(!is_object($answer_options)) {
            return null;
        }

        $question_data = QuestionData::create($question_title, $question_text, $question_author);
        $play_configuration = QuestionPlayConfiguration::create($editor, $scoring);

        $feedback = Feedback::create($DIC->language()->txt('asq_label_right'), $DIC->language()->txt('asq_label_wrong'),Feedback::OPT_ANSWER_OPTION_FEEDBACK_MODE_ALL,[]);

        $question_dto->setData($question_data);
        $question_dto->setLegacyData($legacy_data);
        $question_dto->setAnswerOptions($answer_options);
        $question_dto->setPlayConfiguration($play_configuration);
        $question_dto->setFeedback($feedback);

        $question_dto->setContainerObjId($this->container_obj_id);

        return $question_dto;*/
    /**
     * @param ilQTIItem $il_qti_item
     *
     * @return ilQTIResponse
     */
    protected function getQtiResponse(ilQTIItem $il_qti_item) : ilQTIResponse
    {
        /**
         * @var ilQTIPresentation $presentation
         */
        $presentation = $il_qti_item->getPresentation();
        $arr_response = $presentation->response;
        /**
         * @var ilQTIResponse $response
         */
        $response = $arr_response[0];

        return $response;
    }


    /**
     * @param ilQTIResponseLabel $response_label
     *
     * @return ilQTIMattext
     */
    protected function getMattextFromResponsLabel(ilQTIResponseLabel $response_label) : ilQTIMattext
    {
        /**
         * @var ilQTIResponseLabel $response_label
         */
        $arr_material = $response_label->material;
        /**
         * @var ilQTIMaterial $material
         */
        $material = $arr_material[0];

        $arr_mattext = $material->materials;
        /**
         * @var ilQTIMattext $mattext
         */
        $mattext = $arr_mattext[0]['material'];

        return $mattext;
    }


    /**
     * @param ilQTIResprocessing $resprocessing
     * @param                    $key
     *
     * @return mixed
     */
    protected function getPointsSelectedFromResprocessing(ilQTIResprocessing $resprocessing, $key)
    {
        $arr_respcondition = $resprocessing->respcondition;
        /**
         * @var ilQTIRespcondition $respcondition
         */
        $respcondition = $arr_respcondition[$key];

        $arr_setvar = $respcondition->setvar;
        /**
         * @var ilQTISetvar $setvar
         */
        $setvar = $arr_setvar[0];
        $points_selected = $setvar->getContent();

        return $points_selected;
    }


    /**
     * @param                    $response_label
     * @param ilQTIResprocessing $resprocessing
     * @param                    $key
     * @param AnswerOptions      $answer_options
     * @param int                $i
     */
    protected function addAnswerOption($response_label, ilQTIResprocessing $resprocessing, $key, AnswerOptions $answer_options, int $i) : AnswerOptions
    {
        $mattext = $this->getMattextFromResponsLabel($response_label);
        $points_selected = $this->getPointsSelectedFromResprocessing($resprocessing, $key);

        $points_unselected = 0;

        $display_definition = new ImageAndTextDisplayDefinition(strval($mattext->getContent()), "");

        $scoring_definition = new MultipleChoiceScoringDefinition($points_selected, $points_unselected);

        $answer_options->addOption(new AnswerOption($i, $display_definition, $scoring_definition));

        return $answer_options;
    }


    /**
     * @return QuestionLegacyData
     */
    protected function getQuestionLegacyData(int $answer_type_id, string $content_editing_mode, string $content_object_type) : QuestionLegacyData
    {
        $legacy_data = QuestionLegacyData::create($answer_type_id, $content_editing_mode, $content_object_type);

        return $legacy_data;
    }



    /***
     * @param SimpleXMLElement $simple_xml_element
     *
     * @return string
     */
    /*
     * foreach ($presentation->order as $entry)
		{
			switch ($entry["type"])
			{
				case "response":
					$response = $presentation->response[$entry["index"]];
					$rendertype = $response->getRenderType();
					switch (strtolower(get_class($response->getRenderType())))
					{
						case "ilqtirenderchoice":
							$shuffle = $rendertype->getShuffle();
							if($rendertype->getMaxnumber())
							{
								$selectionLimit = $rendertype->getMaxnumber();
							}
							$answerorder = 0;
							$foundimage = FALSE;
							foreach ($rendertype->response_labels as $response_label)
							{
								$ident = $response_label->getIdent();
								$answertext = "";
								$answerimage = array();
								foreach ($response_label->material as $mat)
								{
									$embedded = false;
									for ($m = 0; $m < $mat->getMaterialCount(); $m++)
									{
										$foundmat = $mat->getMaterial($m);
										if (strcmp($foundmat["type"], "mattext") == 0)
										{
										}
										if (strcmp($foundmat["type"], "matimage") == 0)
										{
											if (strlen($foundmat["material"]->getEmbedded()))
											{
												$embedded = true;
											}
										}
									}
									if ($embedded)
									{
										for ($m = 0; $m < $mat->getMaterialCount(); $m++)
										{
											$foundmat = $mat->getMaterial($m);
											if (strcmp($foundmat["type"], "mattext") == 0)
											{
												$answertext .= $foundmat["material"]->getContent();
											}
											if (strcmp($foundmat["type"], "matimage") == 0)
											{
												$foundimage = TRUE;
												$answerimage = array(
													"imagetype" => $foundmat["material"]->getImageType(),
													"label" => $foundmat["material"]->getLabel(),
													"content" => $foundmat["material"]->getContent()
												);
											}
										}
									}
									else
									{
										$answertext = $this->object->QTIMaterialToString($mat);
									}
								}
								$answers[$ident] = array(
									"answertext" => $answertext,
									"imagefile" => $answerimage,
									"points" => 0,
									"answerorder" => $answerorder++,
									"points_unchecked" => 0,
									"action" => ""
								);
							}
							break;
					}
					break;
			}
		}
		$responses = array();
		$feedbacks = array();
		$feedbacksgeneric = array();
		foreach ($item->resprocessing as $resprocessing)
		{
			foreach ($resprocessing->respcondition as $respcondition)
			{
				$ident = "";
				$correctness = 1;
				$conditionvar = $respcondition->getConditionvar();
				foreach ($conditionvar->order as $order)
				{
					switch ($order["field"])
					{
						case "arr_not":
							$correctness = 0;
							break;
						case "varequal":
							$ident = $conditionvar->varequal[$order["index"]]->getContent();
							break;
					}
				}
				foreach ($respcondition->setvar as $setvar)
				{
					if (strcmp($ident, "") != 0)
					{
						if ($correctness)
						{
							$answers[$ident]["action"] = $setvar->getAction();
							$answers[$ident]["points"] = $setvar->getContent();
							if (count($respcondition->displayfeedback))
							{
								foreach ($respcondition->displayfeedback as $feedbackpointer)
								{
									if (strlen($feedbackpointer->getLinkrefid()))
									{
										foreach ($item->itemfeedback as $ifb)
										{
											if (strcmp($ifb->getIdent(), "response_allcorrect") == 0)
											{
												// found a feedback for the identifier
												if (count($ifb->material))
												{
													foreach ($ifb->material as $material)
													{
														$feedbacksgeneric[1] = $material;
													}
												}
												if ((count($ifb->flow_mat) > 0))
												{
													foreach ($ifb->flow_mat as $fmat)
													{
														if (count($fmat->material))
														{
															foreach ($fmat->material as $material)
															{
																$feedbacksgeneric[1] = $material;
															}
														}
													}
												}
											}
											else if (strcmp($ifb->getIdent(), "response_onenotcorrect") == 0)
											{
												// found a feedback for the identifier
												if (count($ifb->material))
												{
													foreach ($ifb->material as $material)
													{
														$feedbacksgeneric[0] = $material;
													}
												}
												if ((count($ifb->flow_mat) > 0))
												{
													foreach ($ifb->flow_mat as $fmat)
													{
														if (count($fmat->material))
														{
															foreach ($fmat->material as $material)
															{
																$feedbacksgeneric[0] = $material;
															}
														}
													}
												}
											}
											if (strcmp($ifb->getIdent(), $feedbackpointer->getLinkrefid()) == 0)
											{
												// found a feedback for the identifier
												if (count($ifb->material))
												{
													foreach ($ifb->material as $material)
													{
														$feedbacks[$ident] = $material;
													}
												}
												if ((count($ifb->flow_mat) > 0))
												{
													foreach ($ifb->flow_mat as $fmat)
													{
														if (count($fmat->material))
														{
															foreach ($fmat->material as $material)
															{
																$feedbacks[$ident] = $material;
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
						else
						{
							$answers[$ident]["action"] = $setvar->getAction();
							$answers[$ident]["points_unchecked"] = $setvar->getContent();
						}
					}
				}
			}
		}
		$this->addGeneralMetadata($item);
		$this->object->setTitle($item->getTitle());
		$this->object->setNrOfTries($item->getMaxattempts());
		$this->object->setComment($item->getComment());
		$this->object->setAuthor($item->getAuthor());
		$this->object->setOwner($ilUser->getId());
		$this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
		$this->object->setObjId($questionpool_id);
		$this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
		$this->object->setShuffle($shuffle);
		$this->object->setSelectionLimit($selectionLimit);
		$this->object->setThumbSize($item->getMetadataEntry("thumb_size"));

		foreach ($answers as $answer)
		{
			if ($item->getMetadataEntry('singleline') || (is_array($answer["imagefile"]) && count($answer["imagefile"]) > 0))
			{
				$this->object->isSingleline = true;
			}
			$this->object->addAnswer($answer["answertext"], $answer["points"], $answer["points_unchecked"], $answer["answerorder"], $answer["imagefile"]["label"]);
		}
		// additional content editing mode information
		$this->object->setAdditionalContentEditingMode(
				$this->fetchAdditionalContentEditingModeInformation($item)
		);
		$this->object->saveToDb();
		foreach ($answers as $answer)
		{
			if (is_array($answer["imagefile"]) && (count($answer["imagefile"]) > 0))
			{
				$image =& base64_decode($answer["imagefile"]["content"]);
				$imagepath = $this->object->getImagePath();
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				if (!file_exists($imagepath))
				{
					ilUtil::makeDirParents($imagepath);
				}
				$imagepath .=  $answer["imagefile"]["label"];
				$fh = fopen($imagepath, "wb");
				if ($fh == false)
				{
				}
				else
				{
					$imagefile = fwrite($fh, $image);
					fclose($fh);
				}
			}
		}

		$feedbackSetting = $item->getMetadataEntry('feedback_setting');
		if( !is_null($feedbackSetting) )
		{
			$this->object->feedbackOBJ->saveSpecificFeedbackSetting($this->object->getId(), $feedbackSetting);
		}

		// handle the import of media objects in XHTML code
		foreach ($feedbacks as $ident => $material)
		{
			$m = $this->object->QTIMaterialToString($material);
			$feedbacks[$ident] = $m;
		}
		foreach ($feedbacksgeneric as $correctness => $material)
		{
			$m = $this->object->QTIMaterialToString($material);
			$feedbacksgeneric[$correctness] = $m;
		}
		$questiontext = $this->object->getQuestion();
		$answers =& $this->object->getAnswers();
		if (is_array($_SESSION["import_mob_xhtml"]))
		{
			include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
			include_once "./Services/RTE/classes/class.ilRTE.php";
			foreach ($_SESSION["import_mob_xhtml"] as $mob)
			{
				if ($tst_id > 0)
				{
					$importfile = $this->getTstImportArchivDirectory() . '/' . $mob["uri"];
				}
				else
				{
					$importfile = $this->getQplImportArchivDirectory() . '/' . $mob["uri"];
				}

				global $DIC;
$DIC['ilLog']->write(__METHOD__.': import mob from dir: '. $importfile);

$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
$questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
foreach ($answers as $key => $value)
{
$answer_obj =& $answers[$key];
$answer_obj->setAnswertext(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $answer_obj->getAnswertext()));
}
foreach ($feedbacks as $ident => $material)
{
    $feedbacks[$ident] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
}
foreach ($feedbacksgeneric as $correctness => $material)
{
    $feedbacksgeneric[$correctness] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
}
}
}
$this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
foreach ($answers as $key => $value)
{
    $answer_obj =& $answers[$key];
    $answer_obj->setAnswertext(ilRTE::_replaceMediaObjectImageSrc($answer_obj->getAnswertext(), 1));
}
foreach ($feedbacks as $ident => $material)
{
    $this->object->feedbackOBJ->importSpecificAnswerFeedback(
        $this->object->getId(),0, $ident, ilRTE::_replaceMediaObjectImageSrc($material, 1)
    );
}
foreach ($feedbacksgeneric as $correctness => $material)
{
    $this->object->feedbackOBJ->importGenericFeedback(
        $this->object->getId(), $correctness, ilRTE::_replaceMediaObjectImageSrc($material, 1)
    );
}
$this->object->saveToDb();
if (count($item->suggested_solutions))
{
    foreach ($item->suggested_solutions as $suggested_solution)
    {
        $this->object->setSuggestedSolution($suggested_solution["solution"]->getContent(), $suggested_solution["gap_index"], true);
    }
    $this->object->saveToDb();
}
if ($tst_id > 0)
{
    $q_1_id = $this->object->getId();
    $question_id = $this->object->duplicate(true, null, null, null, $tst_id);
    $tst_object->questions[$question_counter++] = $question_id;
    $import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
}
else
{
    $import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
}
     */


    private function getQuestionTitleFromXmlElement(SimpleXMLElement $simple_xml_element) : string
    {
        return strval($simple_xml_element[0]['title']);
    }


    private function getCorrectResponseFromXmlElement(SimpleXMLElement $simple_xml_element) : string
    {
        return strval($simple_xml_element->responseDeclaration->correctResponse->value);
    }


    private function getMaxScoreFromXmlElement(SimpleXMLElement $simple_xml_element) : float
    {
        foreach ($simple_xml_element->outcomeDeclaration as $outcome_declaration) {
            if (strval($outcome_declaration[0]['identifier'] == "MAXSCORE")) {
                return floatval($outcome_declaration->defaultValue->value);
            }
        }
    }


    private function getQuestionTextFromXmlElement(SimpleXMLElement $simple_xml_element) : string
    {
        if(is_null($simple_xml_element->itemBody->div)) {
            return "";
        }
        return $simple_xml_element->itemBody->div->asXML();
    }


    private function getShuffleFromXmlElement(SimpleXMLElement $simple_xml_element) : bool
    {
        return boolval($simple_xml_element->itemBody->choiceInteraction[0]['shuffle']);
    }


    private function getChoicesFromXmlElement(SimpleXMLElement $simple_xml_element) : int
    {
        return intval($simple_xml_element->itemBody->choiceInteraction[0]['choices']);
    }


    private function getSingleChoiceAnswerOptionsFromXmlElement(SimpleXMLElement $simple_xml_element, string $correct_response_identifier, float $max_score) : AnswerOptions
    {

        $answer_options = new AnswerOptions();
        $i = 1;
        if (is_object($simple_xml_element->itemBody->choiceInteraction)) {
            foreach ($simple_xml_element->itemBody->choiceInteraction->children() as $simple_choice) {
                $points_selected = 0;
                $points_unselected = 0;

                //$choices[strval()] = strval($simple_choice);
                $display_definition = new ImageAndTextDisplayDefinition(strval($simple_choice), "");
                if ($simple_choice[0]['identifier'] == $correct_response_identifier) {
                    $points_selected = $max_score;
                }

                $scoring_definition = new MultipleChoiceScoringDefinition($points_selected, $points_unselected);

                $answer_options->addOption(new AnswerOption($i, $display_definition, $scoring_definition));
                $i++;
            }
        }

        return $answer_options;
    }


    private function getTextSubsetQuestionOptionsFromXmlElement(SimpleXMLElement $simple_xml_element, string $correct_response_identifier, float $max_score) : AnswerOptions
    {
        $answer_options = new AnswerOptions();
        $display_definition = new EmptyDisplayDefinition();
        $scoring_definition = new TextSubsetScoringDefinition($max_score, $correct_response_identifier);

        $answer_options->addOption(new AnswerOption(1, $display_definition, $scoring_definition));

        return $answer_options;
    }
}