<?php

namespace ILIAS\AssessmentQuestion\DomainModel;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;

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
	/**
	 * @var ?int
	 */
	protected $answer_type_id;
	
	/**
	 * @var ?string
	 */
	protected $content_editing_mode;
	
	/**
	 * @param int      $answer_type_id
	 *
	 * @return QuestionLegacyData
	 */
	static function create(?int $answer_type_id, ?string $content_editing_mode) : QuestionLegacyData {
		$object = new QuestionLegacyData();
		$object->answer_type_id = $answer_type_id;
		$object->content_editing_mode = $content_editing_mode;
		return $object;
	}

	/**
	 * @return int
	 */
	public function getAnswerTypeId(): ?int {
		return $this->answer_type_id;
	}
	
	public function getContentEditingMode(): ?string {
	    return $this->content_editing_mode;
	}

    /**
     * @param AbstractValueObject $other
     *
     * @return bool
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var QuestionLegacyData $other */
        return get_class($this) === get_class($other) &&
               $this->getAnswerTypeId() === $other->getAnswerTypeId() &&
               $this->getContentEditingMode() === $other->getContentEditingMode();
    }
}