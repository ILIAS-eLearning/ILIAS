<?php

namespace ILIAS\AssessmentQuestion\DomainModel;

use Exception;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy\SingleChoiceQuestionGUI;
use ilPropertyFormGUI;

/**
 * Class QuestionLegacyData
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionLegacyData extends AbstractValueObject {
	const TYPE_GENERIC = 0;
	const TYPE_SINGLE_CHOICE = 1;
	const TYPE_MULTIPLE_CHOICE = 2;

	/**
	 * @var int;
	 */
	protected $container_obj_id;
	/**
	 * @var int
	 */
	protected $answer_type_id;

	/**
	 * @param int      $answer_type_id
	 * @param int|null $container_obj_id
	 *
	 * @return QuestionLegacyData
	 */
	static function create(int $answer_type_id, int $container_obj_id = null) : QuestionLegacyData {
		$object = new QuestionLegacyData();
		$object->answer_type_id = $answer_type_id;
		$object->container_obj_id = $container_obj_id;
		return $object;
	}


	public static function getQuestionTypes() : array {
		$question_types = [];
		$question_types[self::TYPE_GENERIC] = 'GenericQuestion ';
        /*$question_types[self::TYPE_SINGLE_CHOICE] = 'Single Choice ';
        $question_types[self::TYPE_MULTIPLE_CHOICE] = 'Multiple Choice ';
        $question_types[3] = 'Cloze Test ';
        $question_types[4] = 'Matching Question ';
        $question_types[5] = 'Ordering Question ';
        $question_types[6] = 'Imagemap Question ';
        $question_types[7] = 'Java Applet ';
        $question_types[8] = 'Text Question ';
        $question_types[9] = 'Numeric ';
        $question_types[10] = 'Text Subset ';
        $question_types[11] = 'Flash Question ';
        $question_types[12] = 'Ordering Horizontal ';
        $question_types[13] = 'File Upload ';
        $question_types[14] = 'Error Text ';
        $question_types[15] = 'Formula Question ';
        $question_types[16] = 'Kprim Choice ';
        $question_types[17] = 'Long Menu ';*/
		return $question_types;
	}


	/**
	 * @return int
	 */
	public function getContainerObjId(): ?int {
		return $this->container_obj_id;
	}

	/**
	 * @return int
	 */
	public function getAnswerTypeId(): int {
		return $this->answer_type_id;
	}

    /**
     * @param QuestionDto $question
     *
     * @return ilPropertyFormGUI
     * @throws Exception
     */
	public function createLegacyForm(QuestionDto $question): ilPropertyFormGUI {
		switch($this->answer_type_id) {
			case self::TYPE_SINGLE_CHOICE:
				return new SingleChoiceQuestionGUI($question);
			case self::TYPE_MULTIPLE_CHOICE:
				return null;
			default:
				throw new Exception("Implement missing case please");
		}
	}

    /**
     * @param AbstractValueObject $other
     *
     * @return bool
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var QuestionLegacyData $other */
        return $this->getAnswerTypeId() === $other->getAnswerTypeId() &&
               $this->getContainerObjId() === $other->getContainerObjId();
    }
}