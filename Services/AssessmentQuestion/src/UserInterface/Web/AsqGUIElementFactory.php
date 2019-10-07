<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web;

use Exception;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\CreateQuestionFormGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFormGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFeedbackFormGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageFactory;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ilPropertyFormGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\SingleChoiceQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\MultipleChoiceQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\KprimChoiceQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\ErrorTextQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\NumericQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\ImageMapQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\FormulaQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\TextSubsetQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\OrderingQuestionGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\FileUploadQuestionGUI;

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
    const TYPE_GENERIC = 0;
    const TYPE_SINGLE_CHOICE = 1;
    const TYPE_MULTIPLE_CHOICE = 2;
    const TYPE_ORDERING = 5;
    const TYPE_IMAGE_MAP = 6;
    const TYPE_NUMERIC = 9;
    const TYPE_TEXT_SUBSET = 10;
    const TYPE_FILE_UPLOAD = 13;
    const TYPE_ERROR_TEXT = 14;
    const TYPE_FORMULA = 15;
    const TYPE_KPRIM_CHOICE = 16;
    
    /**
     * @return CreateQuestionFormGUI
     */
	public static function CreateQuestionCreationForm(): CreateQuestionFormGUI {
		//CreateQuestion.png
		return new CreateQuestionFormGUI();
	}

    /**
     * @param array $questions
     */
	public static function CreateQuestionListControl(array $questions) {
		//returns question list object
		//GetQuestionlist.png
	}

    /**
     * @param string $question_id
     */
	public static function CreatePrintViewControl(string $question_id) {
		//returns print view
		//GetPrintView.png
	}

    /**
     * @param string $question_id
     */
	public static function CreatePresentationForm(string $question_id) {
		//returns presentation form
		//EditQuestionPresentation.png
	}

	public static function getQuestionTypes() : array {
	    global $DIC;
	    
	    $question_types = [];
	    $question_types[self::TYPE_GENERIC] = $DIC->language()->txt('asq_question_generic');
	    $question_types[self::TYPE_SINGLE_CHOICE] = $DIC->language()->txt('asq_question_single_answer');
	    $question_types[self::TYPE_MULTIPLE_CHOICE] = $DIC->language()->txt('asq_question_multiple_answer');
	    $question_types[self::TYPE_KPRIM_CHOICE] = $DIC->language()->txt('asq_question_kprim_answer');
	    $question_types[self::TYPE_ERROR_TEXT] = $DIC->language()->txt('asq_question_error_text');
	    $question_types[self::TYPE_IMAGE_MAP] = $DIC->language()->txt('asq_question_image_map');
	    $question_types[self::TYPE_NUMERIC] = $DIC->language()->txt('asq_question_numeric');
	    $question_types[self::TYPE_FORMULA] = $DIC->language()->txt('asq_question_formula');
	    $question_types[self::TYPE_TEXT_SUBSET] = $DIC->language()->txt('asq_question_text_subset');
	    $question_types[self::TYPE_ORDERING] = $DIC->language()->txt('asq_question_ordering');
	    $question_types[self::TYPE_FILE_UPLOAD] = $DIC->language()->txt('asq_question_file_upload');
	    /*$question_types[3] = 'Cloze Test ';
	     $question_types[4] = 'Matching Question ';
	     $question_types[7] = 'Java Applet ';
	     $question_types[8] = 'Text Question ';
	     $question_types[11] = 'Flash Question ';
	     $question_types[12] = 'Ordering Horizontal ';
	     $question_types[17] = 'Long Menu ';*/
	    return $question_types;
	}
	
    /**
     * @param QuestionDto $question
     *
     * @return ilPropertyFormGUI
     * @throws Exception
     */
	public static function CreateQuestionForm(QuestionDto $question):ilPropertyFormGUI {
		if (is_null($question->getLegacyData() ||
		    is_null($question->getLegacyData()->getAnswerTypeId()))) {
			return new QuestionFormGUI($question);
		} else {
			return self::createLegacyForm($question);
		}

	}
	
	/**
	 * @param QuestionDto $question
	 *
	 * @return ilPropertyFormGUI
	 * @throws Exception
	 */
	private static function createLegacyForm(QuestionDto $question): ilPropertyFormGUI {
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
	        default:
	            throw new Exception("Implement missing case please");
	    }
	}

    /**
     * @param AuthoringContextContainer $contextContainer
     * @param QuestionDto               $question
     * @param bool                      $preventRteUsage
     *
     * @return QuestionFeedbackFormGUI
     */
	public static function CreateQuestionFeedbackForm(
        QuestionDto $question,
        bool $preventRteUsage
    ): QuestionFeedbackFormGUI
    {
        $page_factory = new PageFactory($question->getContainerObjId(),$question->getQuestionIntId());
        return new QuestionFeedbackFormGUI($page_factory->getFeedbackPage(),$question, $preventRteUsage);
    }
}