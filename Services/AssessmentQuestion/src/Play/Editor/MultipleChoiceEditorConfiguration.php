<?php

namespace ILIAS\AssessmentQuestion\Play\Editor;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;

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
class MultipleChoiceEditorConfiguration extends AbstractValueObject {

	/**
	 * @var bool
	 */
	private $shuffle_answers;
	/**
	 * @var int
	 */
	private $max_answers;
	/**
	 * @var int
	 */
	private $thumbnail_size;

	/**
	 * MultipleChoiceEditor constructor.
	 *
	 * @param bool $shuffle_answers
	 * @param int  $max_answers
	 * @param int  $thumbnail_size
	 */
	public function __construct(bool $shuffle_answers = false, int $max_answers = 1, int $thumbnail_size = 0) {
		$this->shuffle_answers = $shuffle_answers;
		$this->max_answers = $max_answers;
		$this->thumbnail_size = $thumbnail_size;
	}


	/**
	 * @return bool
	 */
	public function isShuffleAnswers(): bool {
		return $this->shuffle_answers;
	}


	/**
	 * @return int
	 */
	public function getMaxAnswers(): int {
		return $this->max_answers;
	}


	/**
	 * @return int
	 */
	public function getThumbnailSize(): int {
		return $this->thumbnail_size;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::equals()
	 */
    public function equals(AbstractValueObject $other): bool
    {
        return $this->isShuffleAnswers() === $other->isShuffleAnswers() &&
               $this->getMaxAnswers() === $other->getMaxAnswers() &&
               $this->getThumbnailSize() === $other->getThumbnailSize();
    }
    
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::jsonSerialize()
     */
    public function jsonSerialize() {
        return get_object_vars($this);
    }
}
