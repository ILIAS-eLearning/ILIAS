<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFeedbackFormGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\ErrorTextQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\EssayQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\FileUploadQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\FormulaQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\ImageMapQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\KprimChoiceQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\MatchingQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\MultipleChoiceQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\NumericQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\OrderingQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\SingleChoiceQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\TextSubsetQuestionGUI;
use Exception;
use ilPropertyFormGUI;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\OrderingTextQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions\ClozeQuestionGUI;

const MSG_SUCCESS = "success";

/**
 * Class AsqGUIElementFactory
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AsqGUIElementFactory {
    const TYPE_SINGLE_CHOICE = 1;
    const TYPE_MULTIPLE_CHOICE = 2;
    const TYPE_MATCHING = 4;
    const TYPE_ORDERING = 5;
    const TYPE_IMAGE_MAP = 6;
    const TYPE_ESSAY = 8;
    const TYPE_NUMERIC = 9;
    const TYPE_TEXT_SUBSET = 10;
    const TYPE_FILE_UPLOAD = 13;
    const TYPE_ERROR_TEXT = 14;
    const TYPE_FORMULA = 15;
    const TYPE_KPRIM_CHOICE = 16;
    const TYPE_CLOZE = 17;
    const TYPE_ORDER_TEXT = 18;
    
    /**
     * @param QuestionDto $question
     *
     * @return ilPropertyFormGUI
     * @throws Exception
     */
	public static function CreateQuestionForm(QuestionDto $question):ilPropertyFormGUI {
	    switch($question->getLegacyData()->getAnswerTypeId()) {
	        case self::TYPE_SINGLE_CHOICE:
	            return new SingleChoiceQuestionGUI($question);
	        case self::TYPE_MULTIPLE_CHOICE:
	            return new MultipleChoiceQuestionGUI($question);
	        case self::TYPE_KPRIM_CHOICE:
	            return new KprimChoiceQuestionGUI($question);
	        case self::TYPE_ERROR_TEXT:
	            return new ErrorTextQuestionGUI($question);
	        case self::TYPE_IMAGE_MAP:
	            return new ImageMapQuestionGUI($question);
	        case self::TYPE_NUMERIC:
	            return new NumericQuestionGUI($question);
	        case self::TYPE_FORMULA:
	            return new FormulaQuestionGUI($question);
	        case self::TYPE_TEXT_SUBSET:
	            return new TextSubsetQuestionGUI($question);
	        case self::TYPE_ORDERING:
	            return new OrderingQuestionGUI($question);
	        case self::TYPE_FILE_UPLOAD:
	            return new FileUploadQuestionGUI($question);
	        case self::TYPE_MATCHING:
	            return new MatchingQuestionGUI($question);
	        case self::TYPE_ESSAY:
	            return new EssayQuestionGUI($question);
	        case self::TYPE_ORDER_TEXT:
	            return new OrderingTextQuestionGUI($question);
	        case self::TYPE_CLOZE:
	            return new ClozeQuestionGUI($question);
	        default:
	            throw new Exception("Implement missing case please");
	    }
	}
	
	public static function getQuestionTypes() : array {
	    global $DIC;

	    $question_types = [];
	    $question_types[self::TYPE_SINGLE_CHOICE] = $DIC->language()->txt('asq_question_single_answer');
	    $question_types[self::TYPE_MULTIPLE_CHOICE] = $DIC->language()->txt('asq_question_multiple_answer');
	    $question_types[self::TYPE_MATCHING] = $DIC->language()->txt('asq_question_matching');
	    $question_types[self::TYPE_KPRIM_CHOICE] = $DIC->language()->txt('asq_question_kprim_answer');
	    $question_types[self::TYPE_ERROR_TEXT] = $DIC->language()->txt('asq_question_error_text');
	    $question_types[self::TYPE_ESSAY] = $DIC->language()->txt('asq_question_essay');
	    $question_types[self::TYPE_IMAGE_MAP] = $DIC->language()->txt('asq_question_image_map');
	    $question_types[self::TYPE_NUMERIC] = $DIC->language()->txt('asq_question_numeric');
	    $question_types[self::TYPE_FORMULA] = $DIC->language()->txt('asq_question_formula');
	    $question_types[self::TYPE_TEXT_SUBSET] = $DIC->language()->txt('asq_question_text_subset');
	    $question_types[self::TYPE_ORDERING] = $DIC->language()->txt('asq_question_ordering');
	    $question_types[self::TYPE_FILE_UPLOAD] = $DIC->language()->txt('asq_question_file_upload');
	    $question_types[self::TYPE_ORDER_TEXT] = $DIC->language()->txt('asq_question_ordering_text');
	    $question_types[self::TYPE_CLOZE] = $DIC->language()->txt('asq_question_cloze');
	    return $question_types;
	}

    /**
     * @param QuestionDto               $question
     *
     * @return QuestionFeedbackFormGUI
     */
	public static function CreateQuestionFeedbackForm(
        QuestionDto $question
    ): QuestionFeedbackFormGUI
    {
        return new QuestionFeedbackFormGUI($question);
    }
}