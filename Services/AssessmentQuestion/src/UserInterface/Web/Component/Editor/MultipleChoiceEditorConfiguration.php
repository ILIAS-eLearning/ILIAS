<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class MultipleChoiceEditorConfiguration
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
	 * @var int?
	 */
	protected $thumbnail_size;
    /**
     * @var bool
     */
	protected $single_line;


    /**
     * @param bool $shuffle_answers
     * @param int  $max_answers
     * @param int  $thumbnail_size
     *
     * @param bool $single_line
     *
     * @return MultipleChoiceEditorConfiguration
     */
	static function create(bool $shuffle_answers = false,
                           int $max_answers = 1,
                           int $thumbnail_size = null,
                           bool $single_line = true) : MultipleChoiceEditorConfiguration
    {
		$object = new MultipleChoiceEditorConfiguration();
		$object->shuffle_answers = $shuffle_answers;
		$object->max_answers = $max_answers;
		$object->thumbnail_size = $thumbnail_size;
		$object->single_line = $single_line;
		return $object;
	}

	/**
	 * @return bool
	 */
	public function isShuffleAnswers() {
		return $this->shuffle_answers;
	}

	/**
	 * @return int
	 */
	public function getMaxAnswers() {
		return $this->max_answers;
	}

	/**
	 * @return int
	 */
	public function getThumbnailSize() {
		return $this->thumbnail_size;
	}

	/**
	 * @return boolean
	 */
	public function isSingleLine() {
	    return $this->single_line;
	}

    /**
     * @param AbstractValueObject $other
     *
     * @return bool
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var MultipleChoiceEditorConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->isShuffleAnswers() === $other->isShuffleAnswers() &&
               $this->getMaxAnswers() === $other->getMaxAnswers() &&
               $this->getThumbnailSize() === $other->getThumbnailSize() &&
               $this->isSingleLine() === $other->isSingleLine();
    }
}
