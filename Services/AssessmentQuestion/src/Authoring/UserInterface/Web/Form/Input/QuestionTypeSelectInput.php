<?php
namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Input;

use ilSelectInputGUI;

class QuestionTypeSelectInput {

	const LNG_KEY_LABEL = 'question_type';
	const LNG_KEY_BYLINE = '';
	const POST_KEY = 'question_type';


	protected $lng_key_label;
	protected $lng_key_byline;
	protected $post_key;
	protected $answer_types;

	public function __construct(array $answer_types) {
		$this->lng_key_label = self::LNG_KEY_LABEL;
		$this->lng_key_byline = self::LNG_KEY_BYLINE;
		$this->post_key =self::POST_KEY;

		$this->answer_types = $answer_types;
	}

	public function getInput() {
		global $DIC;
		$ui = $DIC->ui()->factory();
		return $ui->input()->field()->select($this->lng_key_label, $this->answer_types,$this->lng_key_label);
	}

	public function getPostKey() {
		return $this->post_key;
	}


}
