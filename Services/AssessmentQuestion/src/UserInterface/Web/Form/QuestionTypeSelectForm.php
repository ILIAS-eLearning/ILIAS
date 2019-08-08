<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;

use ILIAS\AssessmentQuestion\DomainModel\QuestionLegacyData;
use ilPropertyFormGUI;
use ilSelectInputGUI;

/**
 * Class QuestionTypeSelectForm
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionTypeSelectForm extends ilPropertyFormGUI {
	const VAR_QUESTION_TYPE = "question_type";

    /**
     * QuestionTypeSelectForm constructor.
     */
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

    /**
     * @return int|null
     */
	public function getQuestionType() : ?int {
		$val =  intval($_POST[self::VAR_QUESTION_TYPE]);
		//return null for type new so that no legacy object will be generated
		return $val === 0 ? null : $val;
	}
}
