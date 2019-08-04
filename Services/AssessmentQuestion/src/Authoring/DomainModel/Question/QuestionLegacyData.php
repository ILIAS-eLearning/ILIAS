<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use Exception;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Legacy\SingleChoiceQuestionGUI;
use ilPropertyFormGUI;
use JsonSerializable;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;

/**
 * Class QuestionPlayConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question
 *
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class QuestionLegacyData extends AbstractValueObject {
	const TYPE_GENERIC = 0;
	const TYPE_SINGLE_CHOICE = 1;
	const TYPE_MULTIPLE_CHOICE = 2;

	/**
	 * @var int;
	 */
	private $container_obj_id;
	/**
	 * @var int
	 */
	private $answer_type_id;

	/**
	 * QuestionLegacyData constructor.
	 *
	 * @param int $answer_type_id
	 * @param int $container_obj_id
	 */
	public function __construct(int $answer_type_id, int $container_obj_id = null) {
		$this->answer_type_id = $answer_type_id;
		$this->container_obj_id = $container_obj_id;
	}


	public static function getQuestionTypes() : array {
		$question_types = [];
		$question_types[self::TYPE_GENERIC] = 'GenericQuestion ';
		$question_types[self::TYPE_SINGLE_CHOICE] = 'Single Choice ';
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
		$question_types[17] = 'Long Menu ';
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
	 * {@inheritDoc}
	 * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::equals()
	 */
    public function equals(AbstractValueObject $other): bool
    {
        return $this->getAnswerTypeId() === $other->getAnswerTypeId() &&
               $this->getContainerObjId() === $other->getContainerObjId();
    }
    
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::jsonSerialize()
     */
    public function jsonSerialize() {
        return get_object_vars($this);
    }
}