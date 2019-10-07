<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionForm;
use Exception;
use ilDurationInputGUI;
use ilHiddenInputGUI;
use ilObjAdvancedEditing;
use ilPropertyFormGUI;
use ilTextAreaInputGUI;
use ilTextInputGUI;
use ilSelectInputGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\AsqGUIElementFactory;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;

/**
 * Class MultipleChoiceQuestionGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class LegacyFormGUIBase extends ilPropertyFormGUI {
	const VAR_AGGREGATE_ID = 'aggregate_id';

	const VAR_TITLE = 'title';
	const VAR_AUTHOR = 'author';
	const VAR_DESCRIPTION = 'description';
	const VAR_QUESTION = 'question';
	const VAR_WORKING_TIME = 'working_time';
	const VAR_LIFECYCLE = 'lifecycle';
	
	const VAR_LEGACY = 'legacy';

	const SECONDS_IN_MINUTE = 60;
	const SECONDS_IN_HOUR = 3600;

	const FORM_PART_LINK = 'form_part_link';

	/**
	 * @var AnswerOptionForm
	 */
	protected $option_form;
	
	/**
	 * @var \ilLanguage
	 */
	protected $lang;
	
	/**
	 * QuestionFormGUI constructor.
	 *
	 * @param QuestionDto $question
	 */
	public function __construct($question) {
	    global $DIC;
	    $this->lang = $DIC->language();
	    
	    $this->initForm($question);
	    $this->setMultipart(true);
	    $this->setTitle(AsqGUIElementFactory::getQuestionTypes()[$question->getLegacyData()->getAnswerTypeId()]);
	    
	    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	        $this->setValuesByPost();
	    }
	    
	    parent::__construct();
	}


    /**
     * @param QuestionDto $question
     */
	private function initForm(QuestionDto $question) {
	    global $DIC;
	    
	    $id = new ilHiddenInputGUI(self::VAR_AGGREGATE_ID);
	    $id->setValue($question->getId());
	    $this->addItem($id);
	    
	    $form_part_link = new \ilHiddenInputGUI(self::FORM_PART_LINK);
	    $form_part_link->setValue($DIC->ctrl()->getLinkTargetByClass('ilAsqQuestionAuthoringGUI', \ilAsqQuestionAuthoringGUI::CMD_GET_FORM_SNIPPET));
	    $this->addItem($form_part_link);
	    
	    $legacy = new ilHiddenInputGUI(self::VAR_LEGACY);
	    $legacy->setValue(json_encode($question->getLegacyData()));
	    $this->addItem($legacy);
	    
	    $this->initQuestionDataConfiguration($question);
	    
	    if (is_null($question->getPlayConfiguration())) {
	        $question->setPlayConfiguration($this->createDefaultPlayConfiguration());
	    }
	    
	    $this->initiatePlayConfiguration($question->getPlayConfiguration());
	    
        $this->option_form = new AnswerOptionForm(
            $this->lang->txt('asq_label_answer'),
            $question->getPlayConfiguration(),
            $question->getAnswerOptions(),
            $this->getAnswerOptionDefinitions($question->getPlayConfiguration()));
        
        $this->addItem($this->option_form);
	}

	protected function getAnswerOptionDefinitions(?QuestionPlayConfiguration $play) : ?array {
	    return null;
	}
	
    /**
     * @return QuestionDto
     * @throws Exception
     */
	public function getQuestion() : QuestionDto {
	    $question = new QuestionDto();
	    $question->setId($_POST[self::VAR_AGGREGATE_ID]);
	    
	    $question->setLegacyData(AbstractValueObject::deserialize($_POST[self::VAR_LEGACY]));
	    
	    $question->setData($this->readQuestionData());
	    
	    $question->setPlayConfiguration($this->readPlayConfiguration());
	    
	    if (!is_null($this->option_form)) {
	        $this->option_form->setConfiguration($question->getPlayConfiguration());
            $this->option_form->readAnswerOptions();
	        $question->setAnswerOptions($this->option_form->getAnswerOptions());
	    }
	    
	    return $question;
	}

	/**
	 * @param QuestionDto $question
	 */
	private function initQuestionDataConfiguration(QuestionDto $question): void {
	    $data = $question->getData();
	    
	    $title = new ilTextInputGUI($this->lang->txt('asq_label_title'), self::VAR_TITLE);
	    $title->setRequired(true);
	    $this->addItem($title);
	    
	    $author = new ilTextInputGUI($this->lang->txt('asq_label_author'), self::VAR_AUTHOR);
	    $author->setRequired(true);
	    $this->addItem($author);
	    
	    $description = new ilTextInputGUI($this->lang->txt('asq_label_description'), self::VAR_DESCRIPTION);
	    $this->addItem($description);
	    
	    $lifecycle = new ilSelectInputGUI($this->lang->txt('asq_label_lifecycle'), self::VAR_LIFECYCLE);
	    $lifecycle->setOptions([
	        QuestionData::LIFECYCLE_DRAFT => $this->lang->txt('asq_lifecycle_draft'),
	        QuestionData::LIFECYCLE_TO_BE_REVIEWED => $this->lang->txt('asq_lifecycle_to_be_reviewed'),
	        QuestionData::LIFECYCLE_REJECTED => $this->lang->txt('asq_lifecycle_rejected'),
	        QuestionData::LIFECYCLE_FINAL => $this->lang->txt('asq_lifecycle_final'),
	        QuestionData::LIFECYCLE_SHARABLE => $this->lang->txt('asq_lifecycle_sharable'),
	        QuestionData::LIFECYCLE_OUTDATED => $this->lang->txt('asq_lifecycle_outdated')
	    ]);
	    $this->addItem($lifecycle);
	    
	    $question_text = new ilTextAreaInputGUI($this->lang->txt('asq_label_question'), self::VAR_QUESTION);
	    $question_text->setRequired(true);
	    $question_text->setRows(10);
	    $question_text->setUseRte(true);
	    $question_text->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
	    $question_text->addPlugin("latex");
	    $question_text->addButton("latex");
	    $question_text->addButton("pastelatex");
	    $question_text->setRTESupport($question->getQuestionIntId(), $question->getIlComponentid(), "assessment");
	    $this->addItem($question_text);
	    
	    $working_time = new ilDurationInputGUI($this->lang->txt('asq_label_working_time'), self::VAR_WORKING_TIME);
	    $working_time->setShowHours(true);
	    $working_time->setShowMinutes(true);
	    $working_time->setShowSeconds(true);
	    $this->addItem($working_time);
	    
	    if ($data !== null) {
	        $title->setValue($data->getTitle());
	        $author->setValue($data->getAuthor());
	        $description->setValue($data->getDescription());
	        $lifecycle->setValue($data->getLifecycle());
	        $question_text->setValue($data->getQuestionText());
	        $working_time->setHours(floor($data->getWorkingTime() / self::SECONDS_IN_HOUR));
	        $working_time->setMinutes(floor($data->getWorkingTime() / self::SECONDS_IN_MINUTE));
	        $working_time->setSeconds($data->getWorkingTime() % self::SECONDS_IN_MINUTE);
	    } else {
	        global $DIC;
	        $author->setValue($DIC->user()->fullname);
	        $working_time->setMinutes(1);
	    }
	}


	/**
	 * @param QuestionPlayConfiguration $play
	 */
	protected abstract function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void ;

	/**
	 * @param array $fields
	 * @param string $post_var
	 * @param $value
	 * @return array
	 */
	protected function hideField(array $fields, string $post_var, $value) : array {
	    $new_fields = [];
	    
	    /** @var $field \ilFormPropertyGUI */
	    foreach ($fields as $field) {
	        if ($field->getPostVar() === $post_var) {
	            $hidden = new \ilHiddenInputGUI($post_var);
	            $hidden->setValue($value);
	            $new_fields[] = $hidden;
	        }
	        else {
	            $new_fields[] = $field;
	        }
	    }
	    
	    return $new_fields;
	}
	
	/**
	 * @param array $definitions
	 * @param string $post_var
	 * @param $value
	 * @return array
	 */
	protected function hideColumn(array $definitions, string $post_var, $value) : array {
	    $new_definitions = [];
	    
	    /** @var $definition AnswerOptionFormFieldDefinition */
	    foreach ($definitions as $definition) {
	        if ($definition->getPostVar() === $post_var) {
	            $new_definitions[] = new AnswerOptionFormFieldDefinition(
	                '',
	                AnswerOptionFormFieldDefinition::TYPE_HIDDEN,
	                $post_var,
	                [$value]);
	        }
	        else {
	            $new_definitions[] = $definition;
	        }
	    }
	    
	    return $new_definitions;
	}

	/**
	 * @param array $definitions
	 * @param string $post_var
	 * @param $value
	 * @return array
	 */
	protected function renameColumn(array $definitions, string $post_var, string $new_name) : array {
	    $new_definitions = [];
	    
	    /** @var $definition AnswerOptionFormFieldDefinition */
	    foreach ($definitions as $definition) {
	        if ($definition->getPostVar() === $post_var) {
	            $new_definitions[] = new AnswerOptionFormFieldDefinition(
	                $new_name,
	                $definition->getType(),
	                $post_var,
	                $definition->getOptions());
	        }
	        else {
	            $new_definitions[] = $definition;
	        }
	    }
	    
	    return $new_definitions;
	}
	
	/**
	 * @return QuestionData
	 * @throws Exception
	 */
	private function readQuestionData(): QuestionData {
	    return QuestionData::create(
	        ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_TITLE]),
	        ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_QUESTION]),
	        ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_AUTHOR]),
	        ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_DESCRIPTION]),
	        $this->readWorkingTime($_POST[self::VAR_WORKING_TIME]),
	        intval($_POST[self::VAR_LIFECYCLE])
	    );
	}

	/**
	 * @return QuestionPlayConfiguration
	 */
	protected abstract function readPlayConfiguration(): QuestionPlayConfiguration;

	/**
	 * @return QuestionPlayConfiguration
	 */
	protected abstract function createDefaultPlayConfiguration() : QuestionPlayConfiguration;
	
    /**
     * @param $postval
     *
     * @return int
     * @throws Exception
     */
	private function readWorkingTime($postval) : int {
		$HOURS = 'hh';
		$MINUTES = 'mm';
		$SECONDS = 'ss';

		if (
			is_array($postval) &&
			array_key_exists($HOURS, $postval) &&
			array_key_exists($MINUTES, $postval) &&
			array_key_exists($SECONDS, $postval)) {
			return $postval[$HOURS] * self::SECONDS_IN_HOUR + $postval[$MINUTES] * self::SECONDS_IN_MINUTE + $postval[$SECONDS];
		} else {
			throw new Exception("This should be impossible, please fix implementation");
		}
	}
}
