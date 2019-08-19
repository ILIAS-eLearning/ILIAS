<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilSelectInputGUI;
use JsonSerializable;
use ilCheckboxInputGUI;
use ilNumberInputGUI;
use ilTemplate;
use stdClass;

/**
 * Class MultipleChoiceEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class MultipleChoiceEditor extends AbstractEditor {

	/**
	 * @var array
	 */
	private $answer_options;
	/**
	 * @var MultipleChoiceEditorConfiguration
	 */
	private $configuration;
	/**
	 * @var array
	 */
	private $selected_answers;

	const VAR_MCE_SHUFFLE = 'shuffle';
	const VAR_MCE_MAX_ANSWERS = 'max_answers';
	const VAR_MCE_THUMB_SIZE = 'thumbsize';
    const VAR_MCE_IS_SINGLELINE = 'singleline';
	
    const STR_TRUE = "true";
    const STR_FALSE = "false";
    
	const VAR_MC_POSTNAME = 'multiple_choice_post_';

	public function __construct(QuestionDto $question) {
		parent::__construct($question);

		$this->answer_options = $question->getAnswerOptions()->getOptions();
		$this->configuration = $question->getPlayConfiguration()->getEditorConfiguration();
	}


	/**
	 * @return string
	 */
	public function generateHtml(): string {
		$tpl = new ilTemplate("tpl.MultipleChoiceEditor.html", true, true, "Services/AssessmentQuestion");

		if ($this->isMultipleChoice()) {
			$tpl->setCurrentBlock('selection_limit_hint');
			$tpl->setVariable('SELECTION_LIMIT_HINT', sprintf(
				"Please select %d of %d answers!",
				$this->configuration->getMaxAnswers(),
				count($this->answer_options)
			));
			$tpl->setVariable('MAX_ANSWERS', $this->configuration->getMaxAnswers());
			$tpl->parseCurrentBlock();
		}

		/** @var AnswerOption $answer_option */
		foreach ($this->answer_options as $answer_option) {
			/** @var MultipleChoiceEditorDisplayDefinition $display_definition */
			$display_definition = $answer_option->getDisplayDefinition();

			$tpl->setCurrentBlock('answer_row');
			$tpl->setVariable('ANSWER_TEXT', $display_definition->getText());
			$tpl->setVariable('TYPE', $this->isMultipleChoice() ? "checkbox" : "radio");
			$tpl->setVariable('ANSWER_ID', $answer_option->getOptionId());
			$tpl->setVariable('POST_NAME', $this->getPostName($answer_option->getOptionId()));

			if (!is_null($this->selected_answers) &&
				in_array($answer_option->getOptionId(), $this->selected_answers)
			) {
				$tpl->setVariable('CHECKED', 'checked="checked"');
			}

			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}


	/**
	 * @return bool
	 */
	private function isMultipleChoice() : bool {
		return $this->configuration->getMaxAnswers() > 1;
	}

	private function getPostName(int $answer_id = null) {
		return $this->isMultipleChoice() ?
			self::VAR_MC_POSTNAME . $this->question->getId() . '_' . $answer_id :
			self::VAR_MC_POSTNAME . $this->question->getId();
	}


	public function readAnswer(): string {
		if ($this->isMultipleChoice()) {
			$result = [];
			/** @var AnswerOption $answer_option */
			foreach ($this->answer_options as $answer_option) {
				$poststring = $this->getPostName($answer_option->getOptionId());
				if (isset($_POST[$poststring])) {
					$result[] = $_POST[$poststring];
				}
			}
			return json_encode($result);
		} else {
			return $_POST[$this->getPostName()];
		}
	}


	/**
	 * @param string $answer
	 */
	public function setAnswer(string $answer) : void {
			$this->selected_answers = json_decode($answer, true);
	}

	public static function generateFields(?AbstractConfiguration $config): ?array {
	    /** @var MultipleChoiceEditorConfiguration $config */

		$fields = [];

		$shuffle = new ilCheckboxInputGUI('shuffle', self::VAR_MCE_SHUFFLE);
		$shuffle->setValue(1);
		$fields[] = $shuffle;

		$max_answers = new ilNumberInputGUI('max_answers', self::VAR_MCE_MAX_ANSWERS);
		$fields[] = $max_answers;

		$thumb_size = new ilNumberInputGUI('thumb size', self::VAR_MCE_THUMB_SIZE);
		$fields[] = $thumb_size;
		
		$singleline = new ilSelectInputGUI('single line', self::VAR_MCE_IS_SINGLELINE);
		$singleline->setOptions([self::STR_TRUE => 'Singleline', self::STR_FALSE => 'Multiline']);
		$fields[] = $singleline;

		if ($config !== null) {
			$shuffle->setChecked($config->isShuffleAnswers());
			$max_answers->setValue($config->getMaxAnswers());
			$thumb_size->setValue($config->getThumbnailSize());
			$singleline->setValue($config->isSingleLine() ? self::STR_TRUE : self::STR_FALSE);
		}

		return $fields;
	}

	/**
	 * @return JsonSerializable|null
	 */
	public static function readConfig() : ?AbstractConfiguration {
		return MultipleChoiceEditorConfiguration::create(
			filter_var($_POST[self::VAR_MCE_SHUFFLE], FILTER_VALIDATE_BOOLEAN),
			$_POST[self::VAR_MCE_MAX_ANSWERS],
			$_POST[self::VAR_MCE_THUMB_SIZE],
		    $_POST[self::VAR_MCE_IS_SINGLELINE] === self::STR_TRUE
		);
	}

	/**
	 * @param stdClass $input
	 *
	 * @return JsonSerializable|null
	 */
	public static function deserialize(?stdClass $input) : ?JsonSerializable {
		if (is_null($input)) {
			return null;
		}

		return MultipleChoiceEditorConfiguration::create(
			$input->shuffle_answers,
			$input->max_answers,
			$input->thumbnail_size
		);
	}
	
	/**
	 * @return string
	 */
	static function getDisplayDefinitionClass() : string {
	    return ChoiceEditorDisplayDefinition::class;
	}
}