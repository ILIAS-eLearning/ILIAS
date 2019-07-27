<?php

namespace ILIAS\AssessmentQuestion\Play\Editor;

use ilCheckboxInputGUI;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Answer;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionDto;
use ilNumberInputGUI;
use ilTemplate;
use JsonSerializable;
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

			if (in_array($answer_option->getOptionId(), $this->selected_answers)) {
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
			return implode(",",$result);
		} else {
			return $_POST[$this->getPostName()];
		}
	}


	/**
	 * @param string $answer
	 */
	public function setAnswer(string $answer) : void {
		$str_answers = explode(',', $answer);

		foreach ($str_answers as $str_answer) {
			$this->selected_answers[] = intval($str_answer);
		}
	}

	public static function generateFields(?JsonSerializable $config): ?array {
		$fields = [];

		$shuffle = new ilCheckboxInputGUI('shuffle', self::VAR_MCE_SHUFFLE);
		$shuffle->setValue(1);
		$fields[] = $shuffle;

		$max_answers = new ilNumberInputGUI('max_answers', self::VAR_MCE_MAX_ANSWERS);
		$fields[] = $max_answers;

		$thumb_size = new ilNumberInputGUI('thumb size', self::VAR_MCE_THUMB_SIZE);
		$fields[] = $thumb_size;

		if ($config !== null) {
			$shuffle->setChecked($config->isShuffleAnswers());
			$max_answers->setValue($config->getMaxAnswers());
			$thumb_size->setValue($config->getThumbnailSize());
		}

		return $fields;
	}

	/**
	 * @return JsonSerializable|null
	 */
	public static function readConfig() : ?JsonSerializable {
		return new MultipleChoiceEditorConfiguration(
			filter_var($_POST[self::VAR_MCE_SHUFFLE], FILTER_VALIDATE_BOOLEAN),
			$_POST[self::VAR_MCE_MAX_ANSWERS],
			$_POST[self::VAR_MCE_THUMB_SIZE]
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

		return new MultipleChoiceEditorConfiguration(
			$input->shuffle_answers,
			$input->max_answers,
			$input->thumbnail_size
		);
	}
}