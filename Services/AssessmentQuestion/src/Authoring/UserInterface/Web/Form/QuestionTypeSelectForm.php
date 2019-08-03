<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionLegacyData;
use \ilPropertyFormGUI;
use ilSelectInputGUI;

class QuestionTypeSelectForm extends ilPropertyFormGUI {
	const VAR_QUESTION_TYPE = "question_type";

	public function __construct( ) {
		$this->initForm();

		parent::__construct();
	}

	/**
	 * Init settings property form
	 *
	 * @access private
	 */
	private function initForm() {
		$select = new ilSelectInputGUI("type", self::VAR_QUESTION_TYPE);
		$select->setOptions(QuestionLegacyData::getQuestionTypes());
		$this->addItem($select);

		$this->addCommandButton('create', 'Create');
	}

	public function getQuestionType() : ?int {
		$val =  intval($_POST[self::VAR_QUESTION_TYPE]);
		//return null for type new so that no legacy object will be generated
		return $val === 0 ? null : $val;
	}
}
