<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;

/**
 * Class QuestionPlayConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question
 *
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class QuestionPlayConfiguration extends AbstractValueObject {

	/**
	 * @var string
	 */
	private $presenter_class;
	/**
	 * @var AbstractValueObject
	 */
	private $presenter_configuration;
	/**
	 * @var string
	 */
	private $editor_class;
	/**
	 * @var AbstractValueObject
	 */
	private $editor_configuration;
	/**
	 * @var string
	 */
	private $scoring_class;
	/**
	 * @var AbstractValueObject
	 */
	private $scoring_configuration;
	/**
	 * @var int Working time in seconds
	 */
	private $working_time;


	/**
	 * QuestionPlayConfiguration constructor.
	 *
	 * @param string                $presenter_class
	 * @param string                $editor_class
	 * @param string                $scoring_class
	 * @param int                   $working_time
	 * @param AbstractValueObject|null $editor_configuration
	 * @param AbstractValueObject|null $presenter_configuration
	 * @param AbstractValueObject|null $scoring_configuration
	 */
	public function __construct(
		string $presenter_class,
		string $editor_class,
		string $scoring_class,
		int $working_time,
	    AbstractValueObject $editor_configuration = null,
	    AbstractValueObject $presenter_configuration = null,
	    AbstractValueObject $scoring_configuration = null
	) {
		$this->presenter_class = $presenter_class;
		$this->editor_class = $editor_class;
		$this->scoring_class = $scoring_class;
		$this->working_time = $working_time;
		$this->editor_configuration = $editor_configuration;
		$this->presenter_configuration = $presenter_configuration;
		$this->scoring_configuration = $scoring_configuration;
	}

	/**
	 * @return string
	 */
	public function getPresenterClass(): string {
		return $this->presenter_class;
	}

	/**
	 * @return string
	 */
	public function getEditorClass(): string {
		return $this->editor_class;
	}

	/**
	 * @return string
	 */
	public function getScoringClass(): string {
		return $this->scoring_class;
	}

	/**
	 * @return int
	 */
	public function getWorkingTime(): int {
		return $this->working_time;
	}

	/**
	 * @return AbstractValueObject
	 */
	public function getEditorConfiguration(): ?AbstractValueObject {
		return $this->editor_configuration;
	}


	/**
	 * @return AbstractValueObject
	 */
	public function getPresenterConfiguration(): ?AbstractValueObject {
		return $this->presenter_configuration;
	}


	/**
	 * @return AbstractValueObject
	 */
	public function getScoringConfiguration(): ?AbstractValueObject {
		return $this->scoring_configuration;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::equals()
	 */
    public function equals(AbstractValueObject $other): bool
    {
        return $this->getEditorClass() === $other->getEditorClass() &&
               $this->getPresenterClass() === $other->getPresenterClass() &&
               $this->getScoringClass() === $other->getScoringClass() &&
               AbstractValueObject::isNullableEqual($this->getEditorConfiguration(), $other->getEditorConfiguration()) &&
               AbstractValueObject::isNullableEqual($this->getPresenterConfiguration(), $other->getPresenterConfiguration()) &&
               AbstractValueObject::isNullableEqual($this->getScoringConfiguration(), $other->getScoringConfiguration());
    }
    
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::jsonSerialize()
     */
    public function jsonSerialize() {
        return get_object_vars($this);
    }
}