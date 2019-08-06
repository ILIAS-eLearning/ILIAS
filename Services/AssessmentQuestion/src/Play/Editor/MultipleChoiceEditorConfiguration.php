<?php

namespace ILIAS\AssessmentQuestion\Play\Editor;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event\AbstractConfiguration;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;
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
class MultipleChoiceEditorConfiguration extends AbstractConfiguration {

	/**
	 * @var bool
	 */
	protected $shuffle_answers;
	/**
	 * @var int
	 */
	protected $max_answers;
	/**
	 * @var int
	 */
	protected $thumbnail_size;

	/**
	 * @param bool $shuffle_answers
	 * @param int  $max_answers
	 * @param int  $thumbnail_size
	 *
	 * @return MultipleChoiceEditorConfiguration
	 */
	static function create(bool $shuffle_answers = false, int $max_answers = 1, int $thumbnail_size = 0) : MultipleChoiceEditorConfiguration {
		$object = new MultipleChoiceEditorConfiguration();
		$object->shuffle_answers = $shuffle_answers;
		$object->max_answers = $max_answers;
		$object->thumbnail_size = $thumbnail_size;
		return $object;
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
}
