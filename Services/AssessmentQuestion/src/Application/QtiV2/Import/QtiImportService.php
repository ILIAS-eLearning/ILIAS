<?php

namespace ILIAS\AssessmentQuestion\Application\QtiV2\Import;

use http\Exception;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\ContentEditingMode;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionLegacyData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\MultipleChoiceScoringConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\MultipleChoiceScoringDefinition;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\TextSubsetScoringConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\TextSubsetScoringDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\AsqGUIElementFactory;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EmptyDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\MultipleChoiceEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\TextSubsetEditorConfiguration;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;
use SimpleXMLElement;

/**
 * Class AuthoringApplicationService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QtiImportService
{

    public function __construct(int $container_obj_id)
    {
        $this->container_obj_id = $container_obj_id;
    }


    public function getQuestionDtoFromXml(string $qti_item_xml):?QuestionDto
    {
        global $DIC;

        $simple_xml_element = new SimpleXMLElement($qti_item_xml);

        $question_dto = new QuestionDto();

        $question_title = $this->getQuestionTitleFromXmlElement($simple_xml_element);
        $question_text = $this->getQuestionTextFromXmlElement($simple_xml_element);
        $question_author = $DIC->user()->getLogin();

        $correct_response_identifier = $this->getCorrectResponseFromXmlElement($simple_xml_element);
        $max_score = $this->getMaxScoreFromXmlElement($simple_xml_element);


        //Todo -> the followings should be transfered in a factory
        if (isset($simple_xml_element->itemBody->choiceInteraction)) {
            $answer_options = $this->getSingleChoiceAnswerOptionsFromXmlElement($simple_xml_element, $correct_response_identifier, $max_score);

            $legacy_data = QuestionLegacyData::create(AsqGUIElementFactory::TYPE_SINGLE_CHOICE, ContentEditingMode::RTE_TEXTAREA, 'tst');

            $shuffle = $this->getShuffleFromXmlElement($simple_xml_element);
            $choices = $this->getChoicesFromXmlElement($simple_xml_element);
            $editor = MultipleChoiceEditorConfiguration::create($shuffle, $choices, null, true);
            $scoring = new MultipleChoiceScoringConfiguration();
        }

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

        return $question_dto;
    }


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